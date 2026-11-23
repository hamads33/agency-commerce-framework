#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/.."

if [[ -e .wp-env.override.json ]]; then
	printf '%s\n' 'Run the backend gate from a checkout without .wp-env.override.json.' >&2
	exit 1
fi

umask 077
mkdir -p "$HOME/.cache/agency-backend"
run_dir=$(mktemp -d "$HOME/.cache/agency-backend/run.XXXXXXXX")
export COMPOSE_PROJECT_NAME="agency-backend-$(basename "$run_dir" | cut -d. -f2 | tr "[:upper:]" "[:lower:]")"
export WP_ENV_HOME="$run_dir/wp-env"
export WP_ENV_PORT="${BACKEND_TEST_PORT:-18988}"
export WP_BASE_URL="http://localhost:$WP_ENV_PORT"
unset WP_ENV_COMPOSE_FILE WP_ENV_CORE WP_ENV_PHP_VERSION WP_USERNAME WP_PASSWORD
unset COMPOSE_FILE WP_ENV_LIFECYCLE_SCRIPT_AFTER_START WP_ENV_LIFECYCLE_SCRIPT_AFTER_CLEAN
unset WP_ENV_MYSQL_PORT WP_ENV_PHPMYADMIN_PORT
printf 'Backend run artifacts: %s\n' "$run_dir"

trap 'npm run env:stop > "$run_dir/stop.log" 2>&1 || true' EXIT
npm run env:start -- --debug --no-scripts 2>&1 | tee "$run_dir/start.log"
wp() { npm run env:cli -- wp "$@"; }

wp plugin activate woocommerce agency-commerce-core
wp theme activate agency-commerce
wp core version
wp plugin get woocommerce --field=version
wp eval 'echo PHP_VERSION, PHP_EOL;'
npm run env:cli -- sh -c 'wp db export /tmp/agency-backend-before-fixtures.sql >&2 &&
	printf "AGENCY_DB_DUMP:" &&
	base64 -w 0 /tmp/agency-backend-before-fixtures.sql &&
	printf "\n"' |
	tr -d '\r' | sed -n 's/^AGENCY_DB_DUMP://p' | base64 -d > "$run_dir/before-fixtures.sql"
test -s "$run_dir/before-fixtures.sql"

wp eval 'wp_mkdir_p( WPMU_PLUGIN_DIR ); if ( false === file_put_contents( WPMU_PLUGIN_DIR . "/agency-test-safety.php", "<?php\nadd_filter( \"pre_wp_mail\", \"__return_true\" );\nadd_filter( \"woocommerce_prevent_automatic_wizard_redirect\", \"__return_true\" );\nadd_filter( \"pre_http_request\", static fn () => new WP_Error( \"test_http_disabled\" ) );\n" ) ) { WP_CLI::error( "Unable to install test safeguards." ); }'
wp eval 'WC_Install::create_pages();'
wp eval "$(sed '1d' tests/backend/catalogue.php)"
wp rewrite structure '/%postname%/' --hard

status=0
for fixture in product-settings migration-recovery page-and-catalogue-protection authorization; do
	wp eval "$(sed '1d' "tests/backend/$fixture.php")" > "$run_dir/$fixture.log" 2>&1 || status=1
	cat "$run_dir/$fixture.log"
done
node tests/backend/block-order.js > "$run_dir/block-order.log" 2>&1 || status=1
cat "$run_dir/block-order.log"
for suite in admin storefront homepage cart-flow checkout-block checkout-classic account cp5-audit; do
	if npm run "test:$suite" > "$run_dir/$suite.log" 2>&1; then
		printf 'PASS %s\n' "$suite"
	else
		printf 'FAIL %s (%s/%s.log)\n' "$suite" "$run_dir" "$suite"
		tail -n 20 "$run_dir/$suite.log"
		status=1
	fi
done
if [[ ${AGENCY_RUN_NAVIGATION:-0} == 1 ]]; then
	AGENCY_NAVIGATION_FIXTURES=1 npm run test:navigation > "$run_dir/navigation.log" 2>&1 || status=1
	cat "$run_dir/navigation.log"
fi
exit "$status"
