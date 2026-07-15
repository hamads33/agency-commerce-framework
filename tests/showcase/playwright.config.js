const path = require( 'path' );
const { defineConfig, devices } = require( '@playwright/test' );

const threshold = Number.parseFloat(
	process.env.SHOWCASE_VISUAL_DIFF_THRESHOLD || '0.01'
);

module.exports = defineConfig( {
	testDir: __dirname,
	fullyParallel: false,
	workers: 1,
	reporter: 'list',
	timeout: 90000,
	snapshotPathTemplate: path.join(
		__dirname,
		'..',
		'..',
		'showcase-screenshots',
		'regression',
		'{projectName}',
		'{arg}{ext}'
	),
	expect: {
		toHaveScreenshot: {
			animations: 'disabled',
			caret: 'hide',
			maxDiffPixelRatio: threshold,
		},
	},
	use: {
		baseURL: process.env.WP_BASE_URL || 'http://localhost:8888',
		trace: 'off',
	},
	projects: [
		{
			name: 'desktop',
			use: {
				...devices[ 'Desktop Chrome' ],
				viewport: { width: 1440, height: 1100 },
			},
		},
		{
			name: 'tablet',
			use: {
				...devices[ 'iPad (gen 7)' ],
				browserName: 'chromium',
				viewport: { width: 768, height: 1024 },
			},
		},
		{
			name: 'mobile',
			use: {
				...devices[ 'iPhone 12' ],
				browserName: 'chromium',
				viewport: { width: 390, height: 844 },
			},
		},
	],
} );
