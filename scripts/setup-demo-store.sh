#!/usr/bin/env bash
set -euo pipefail

DEMO="${1:-}"

case "${DEMO}" in
	fashion)
		THEME="agency-commerce-fashion"
		;;
	electronics)
		THEME="agency-commerce-electronics"
		;;
	home)
		THEME="agency-commerce-home"
		;;
	*)
		printf 'Usage: %s {fashion|electronics|home}\n' "$0" >&2
		exit 1
		;;
esac

if ! command -v wp >/dev/null 2>&1; then
	printf 'WP-CLI is required. In wp-env use: npm run env:cli -- bash scripts/setup-demo-store.sh %s\n' "${DEMO}" >&2
	exit 1
fi

wp plugin activate woocommerce >/dev/null
wp plugin activate agency-commerce-core >/dev/null
wp theme activate "${THEME}" >/dev/null
wp option delete agency_commerce_core_settings >/dev/null 2>&1 || true
wp option delete agency_commerce_core_active_preset >/dev/null 2>&1 || true
wp option delete agency_commerce_core_preset_snapshot >/dev/null 2>&1 || true
wp rewrite flush --hard >/dev/null

wp eval '
$upload_dir = wp_upload_dir();
foreach ( array( "480x480", "720x720", "1024x1024" ) as $placeholder_size ) {
	$placeholder = trailingslashit( $upload_dir["basedir"] ) . "woocommerce-placeholder-" . $placeholder_size . ".webp";
	if ( ! file_exists( $placeholder ) ) {
		wp_mkdir_p( dirname( $placeholder ) );
		file_put_contents(
			$placeholder,
			base64_decode( "UklGRiIAAABXRUJQVlA4IBYAAAAwAQCdASoBAAEADsD+JaQAA3AA/vuUAAA=" )
		);
	}
}
'

printf 'Agency Commerce %s demo is active using theme %s.\n' "${DEMO}" "${THEME}"
