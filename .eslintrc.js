const wpEslintConfig = require( '@wordpress/scripts/config/.eslintrc.js' );

const eslintConfig = {
	...wpEslintConfig,
	overrides: [
		...wpEslintConfig.overrides,
		{
			files: [ '**/specs/**/*.[jt]s?(x)', '**/?(*.)spec.[jt]s?(x)' ],
			extends: [ 'plugin:@wordpress/eslint-plugin/test-e2e' ],
		},
	],
};

module.exports = eslintConfig;
