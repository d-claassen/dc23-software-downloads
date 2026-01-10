const baseConfig = require( '@wordpress/scripts/config/webpack.config' );
const { getWebpackEntryPoints } = require( '@wordpress/scripts/utils' );

const scriptConfig = {
	...baseConfig,

	entry: () => ( {
		index: './src/index.js',
		...getWebpackEntryPoints( 'script' )(),
	} ),
};

module.exports = scriptConfig;
