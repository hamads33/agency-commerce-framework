const { defineConfig } = require( '@playwright/test' );

module.exports = defineConfig( {
	testDir: __dirname,
	workers: 1,
	reporter: 'list',
	outputDir: '../../test-results/navigation',
	use: {
		reducedMotion: 'reduce',
	},
} );
