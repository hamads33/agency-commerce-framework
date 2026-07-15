#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
DIST_DIR="${ROOT_DIR}/build/release"

require_command() {
	if ! command -v "$1" >/dev/null 2>&1; then
		printf 'Required command not found: %s\n' "$1" >&2
		exit 1
	fi
}

package_directory() {
	local source_dir="$1"
	local zip_name="$2"
	local parent_dir
	local base_name

	parent_dir="$(dirname "${source_dir}")"
	base_name="$(basename "${source_dir}")"

	(
		cd "${parent_dir}"
		zip -X -r "${DIST_DIR}/${zip_name}" "${base_name}" \
			-x '*/node_modules/*' \
			-x '*/vendor/*' \
			-x '*/tests/*' \
			-x '*/test-results/*' \
			-x '*/playwright-report/*' \
			-x '*/screenshots/*' \
			-x '*/.cache/*' \
			-x '*/cache/*' \
			-x '*/logs/*' \
			-x '*/.git/*' \
			-x '*/.git' \
			-x '*/.idea/*' \
			-x '*/.vscode/*' \
			-x '*/.DS_Store' \
			-x '*/Thumbs.db' \
			-x '*.log' \
			-x '*.tmp' \
			-x '*~'
	)
}

require_command zip

rm -rf "${DIST_DIR}"
mkdir -p "${DIST_DIR}"

package_directory "${ROOT_DIR}/wp-content/themes/agency-commerce" "agency-commerce.zip"
package_directory "${ROOT_DIR}/wp-content/plugins/agency-commerce-core" "agency-commerce-core.zip"
package_directory "${ROOT_DIR}/wp-content/themes/agency-commerce-child" "agency-commerce-child.zip"

printf 'Release packages written to %s\n' "${DIST_DIR}"
