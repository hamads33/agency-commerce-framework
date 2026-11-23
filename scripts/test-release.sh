#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/.."
root=$PWD
npm run package
umask 077
mkdir -p "$HOME/.cache/agency-release"
run_dir=$(mktemp -d "$HOME/.cache/agency-release/run.XXXXXXXX")
export COMPOSE_PROJECT_NAME="agency-release-$(basename "$run_dir" | cut -d. -f2 | tr '[:upper:]' '[:lower:]')"
export WP_ENV_HOME="$run_dir/wp-env"
export WP_ENV_PORT="${RELEASE_TEST_PORT:-18994}"
export WP_BASE_URL="http://localhost:$WP_ENV_PORT"
unset COMPOSE_FILE WP_ENV_COMPOSE_FILE WP_ENV_CORE WP_ENV_PHP_VERSION
unset WP_ENV_MYSQL_PORT WP_ENV_PHPMYADMIN_PORT
printf 'Release smoke artifacts: %s\n' "$run_dir"

python3 - "$root" "$run_dir" <<'PY'
import json
import sys
from pathlib import Path

root, workspace = map(Path, sys.argv[1:])
config = json.loads((root / ".wp-env.json").read_text())
config["mappings"] = {"wp-content/agency-release-fixtures": str(root / "build/release")}
config["config"]["WP_HTTP_BLOCK_EXTERNAL"] = True
(workspace / ".wp-env.json").write_text(json.dumps(config))
PY

cd "$run_dir"
env_cli="$root/node_modules/.bin/wp-env"
trap '"$env_cli" stop > "$run_dir/stop.log" 2>&1 || true' EXIT
"$env_cli" start --debug --no-scripts 2>&1 | tee "$run_dir/start.log"
wp() { "$env_cli" run cli -- wp "$@"; }

artifacts=/var/www/html/wp-content/agency-release-fixtures
wp plugin activate woocommerce
wp plugin install "$artifacts/agency-commerce-core.zip" --activate
wp theme install "$artifacts/agency-commerce.zip"
wp theme install "$artifacts/agency-commerce-child.zip" --activate
wp eval 'wp_mkdir_p( WPMU_PLUGIN_DIR ); if ( false === file_put_contents( WPMU_PLUGIN_DIR . "/agency-test-mail.php", "<?php\nadd_filter( \"pre_wp_mail\", \"__return_true\" );\n" ) ) { WP_CLI::error( "Could not isolate mail." ); }'
wp plugin deactivate agency-commerce-core
wp plugin activate agency-commerce-core
wp option update woocommerce_custom_orders_table_enabled yes
wp eval 'WC_Install::create_pages(); if ( "agency-commerce" !== get_template() || ! Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled() ) { WP_CLI::error( "Expected child inheritance and HPOS." ); }'
wp rewrite structure '/%postname%/' --hard
wp eval "$(sed '1d' "$root/tests/backend/catalogue.php")"
WP_ENV_COMPOSE_FILE=$(find "$WP_ENV_HOME" -type f -name docker-compose.yml -print -quit)
export WP_ENV_COMPOSE_FILE
test -n "$WP_ENV_COMPOSE_FILE"
(cd "$root" && node tests/backend/block-order.js) | tee "$run_dir/order.log"
