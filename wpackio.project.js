const pkg = require('./package.json');

module.exports = {
	// Project Identity
	appName: 'cpLive', // Unique name of your project
	type: 'plugin', // Plugin or theme
	slug: 'cp-live', // Plugin or Theme slug, basically the directory name under `wp-content/<themes|plugins>`
	// Used to generate banners on top of compiled stuff
	bannerConfig: {
		name: 'cpLive',
		author: 'Mission Lab',
		license: 'GPL-3.0',
		link: 'https://missionlab.dev',
		version: pkg.version,
		copyrightText:
			'This software is released under the GPL-3.0 License\nhttps://opensource.org/licenses/GPL-3.0',
		credit: true,
	},
	// Files we need to compile, and where to put
	files: [
		{
			name         : 'styles',
			entry        : {
				main: ['./assets/scss/main.scss'],
				admin: ['./assets/scss/admin.scss'],
			},
			webpackConfig: (config, merge, appDir, isDev) => {
				const customRules = {
					module: {
						rules: [
							{
								test: /\.(png|jpg|gif|svg)$/i,
								use : [
									{
										loader : 'url-loader',
										options: {
											limit: 8192,
										}
									}
								]
							}
						]
					}
				};

				return merge(config, customRules);
			},
		},
		{
			name : 'scripts',
			entry: {
				main: ['./assets/js/main.js'],
				admin: ['./assets/js/admin.js']
			},
		}
	],

	// Output path relative to the context directory
	// We need relative path here, else, we can not map to publicPath
	outputPath: 'dist',
	// Project specific config
	// Needs react(jsx)?
	hasReact: false,
	// Whether or not to use the new jsx runtime introduced in React 17
	// this is opt-in
	// @see {https://reactjs.org/blog/2020/09/22/introducing-the-new-jsx-transform.html}
	useReactJsxRuntime: false,
	// Disable react refresh
	disableReactRefresh: false,
	// Needs sass?
	hasSass: true,
	// Needs less?
	hasLess: false,
	// Needs flowtype?
	hasFlow: false,
	// Externals
	// <https://webpack.js.org/configuration/externals/>
	externals: {
		jquery: 'jQuery',
	},
	// Webpack Aliases
	// <https://webpack.js.org/configuration/resolve/#resolve-alias>
	alias: undefined,
	// Show overlay on development
	errorOverlay: true,
	// Auto optimization by webpack
	// Split all common chunks with default config
	// <https://webpack.js.org/plugins/split-chunks-plugin/#optimization-splitchunks>
	// Won't hurt because we use PHP to automate loading
	optimizeSplitChunks: true,
	// Usually PHP and other files to watch and reload when changed
	watch: './inc|includes|templates/**/*.php',
	// Files that you want to copy to your ultimate theme/plugin package
	// Supports glob matching from minimatch
	// @link <https://github.com/isaacs/minimatch#usage>
	packageFiles: [
		'assets/images/**',
		'includes/**',
		'vendor/**',
		'dist/**',
		'*.php',
		'*.md',
		'readme.txt',
		'languages/**',
		'layouts/**',
		'templates/**',
		'LICENSE',
		'*.css',
	],
	// Path to package directory, relative to the root
	packageDirPath: 'build',
	// whether or not to disable wordpress external scripts handling
	disableWordPressExternals: false,
};
