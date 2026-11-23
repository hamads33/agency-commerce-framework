"""Package tracked runtime files and verify the complete staged release."""
import os
import re
import subprocess
from pathlib import Path
from tempfile import TemporaryDirectory
from zipfile import ZIP_DEFLATED, ZipFile

PACKAGES = {
    "wp-content/plugins/agency-commerce-core": (
        "agency-commerce-core.php", "readme.txt", "build/admin.css",
        "build/admin.js", "build/blocks.js", "build/checkout.js", "build/recently-viewed.js",
    ),
    "wp-content/themes/agency-commerce": (
        "style.css", "functions.php", "theme.json", "build/frontend.js",
        "build/catalog.js", "build/style-frontend.css",
    ),
    "wp-content/themes/agency-commerce-child": ("style.css", "functions.php"),
}
RUNTIME_DIRS = {
    "admin", "assets", "blocks", "build", "components", "inc", "includes",
    "integrations", "languages", "public", "template-parts", "templates", "woocommerce",
}
RUNTIME_SUFFIXES = {".php", ".css", ".js", ".json", ".svg", ".webp", ".png", ".jpg", ".jpeg", ".woff", ".woff2", ".pot", ".po", ".mo"}


def header(content, key):
    match = re.search(rf"^\s*\*?\s*{re.escape(key)}:\s*(\S+)", content, re.MULTILINE)
    if not match:
        raise ValueError(f"Missing release header {key}")
    return match[1]


def package(source, required, destination):
    root = Path(source)
    tracked = subprocess.check_output(["git", "ls-files", "-z", "--", source])
    contents = {}
    for raw in tracked.split(b"\0"):
        if not raw:
            continue
        file = Path(os.fsdecode(raw))
        relative = file.relative_to(root)
        if any(part.startswith(".") or part in {"vendor", "node_modules", "tests", "logs", "cache"} for part in relative.parts):
            continue
        if relative.name in {"composer.json", "package.json", "package-lock.json"}:
            continue
        if len(relative.parts) > 1 and relative.parts[0] not in RUNTIME_DIRS:
            continue
        if relative.suffix not in RUNTIME_SUFFIXES and relative.name != "readme.txt":
            continue
        if file.is_symlink() or not file.is_file() or not file.resolve().is_relative_to(root.resolve()):
            raise ValueError(f"Invalid tracked runtime file: {file}")
        contents[relative.as_posix()] = file

    missing = set(required) - contents.keys()
    if missing:
        raise ValueError(f"{root.name} is missing release files: {sorted(missing)}")
    for name, file in contents.items():
        if name.startswith("build/") and name.endswith(".js"):
            if name[:-3] + ".asset.php" not in contents:
                raise ValueError(f"Missing asset manifest for {file}")
    manifest = contents[required[0]].read_text()
    version = header(manifest, "Version")
    if "readme.txt" in contents and header(contents["readme.txt"].read_text(), "Stable tag") != version:
        raise ValueError("Plugin version and stable tag differ")
    if root.name.endswith("-child") and header(manifest, "Template") != "agency-commerce":
        raise ValueError("Child theme declares an unexpected parent")

    archive = destination / f"{root.name}.zip"
    with ZipFile(archive, "w", ZIP_DEFLATED) as bundle:
        for name, file in sorted(contents.items()):
            bundle.write(file, f"{root.name}/{name}")
    with ZipFile(archive) as bundle:
        if bundle.testzip() is not None:
            raise ValueError(f"Corrupt archive {archive}")
    return version


def main():
    output = Path("build/release")
    output.mkdir(parents=True, exist_ok=True)
    with TemporaryDirectory(prefix=".stage-", dir=output) as staging:
        destination = Path(staging)
        versions = {package(source, files, destination) for source, files in PACKAGES.items()}
        if len(versions) != 1:
            raise ValueError(f"Release versions differ: {sorted(versions)}")
        for archive in destination.glob("*.zip"):
            archive.replace(output / archive.name)
    print(f"Verified release ZIPs written to {output}")


if __name__ == "__main__":
    main()
