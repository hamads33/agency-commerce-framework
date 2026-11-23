#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/.."
if [[ -e .wp-env.override.json ]]; then
	echo 'Run this disposable demo gate without a wp-env override.' >&2
	exit 1
fi
umask 077
mkdir -p "$HOME/.cache/agency-forge"
run_dir=$(mktemp -d "$HOME/.cache/agency-forge/run.XXXXXXXX")
export WP_ENV_HOME="$run_dir/wp-env"
export WP_ENV_PORT="${FORGE_PORT:-18997}"
export WP_BASE_URL="http://localhost:$WP_ENV_PORT"
export COMPOSE_PROJECT_NAME="agency-forge-$(basename "$run_dir" | cut -d. -f2 | tr '[:upper:]' '[:lower:]')"
unset COMPOSE_FILE WP_ENV_COMPOSE_FILE WP_ENV_CORE WP_ENV_PHP_VERSION WP_ENV_MYSQL_PORT WP_ENV_PHPMYADMIN_PORT
printf 'Forge artifacts: %s\n' "$run_dir"
trap 'npm run env:stop > "$run_dir/stop.log" 2>&1 || true' EXIT
npm run env:start -- --debug --no-scripts 2>&1 | tee "$run_dir/start.log"
wp() { npm run env:cli -- wp "$@"; }
wp config set WP_HTTP_BLOCK_EXTERNAL true --raw
wp eval 'wp_mkdir_p( WPMU_PLUGIN_DIR ); if ( false === file_put_contents( WPMU_PLUGIN_DIR . "/forge-demo-mail.php", "<?php\nadd_filter( \"pre_wp_mail\", \"__return_true\" );\n" ) ) { WP_CLI::error( "Could not isolate mail." ); }'
wp plugin activate woocommerce agency-commerce-core
wp theme activate agency-commerce-forge
wp rewrite structure '/%postname%/' --hard
wp eval "\$args = array( 'seed-disposable-forge' ); $(sed '1d' scripts/forge-demo.php)"
wp eval "\$args = array( 'seed-disposable-forge' ); $(sed '1d' scripts/forge-demo.php)"
export FORGE_ARTIFACTS="$run_dir"
node tests/forge/smoke.js | tee "$run_dir/smoke.log"
