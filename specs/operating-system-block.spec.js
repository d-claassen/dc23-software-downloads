/**
 * WordPress dependencies
 */
const { test, expect } = require( '@wordpress/e2e-test-utils-playwright' );

test.describe( 'Block "Operating system"', () => {
	let consoleLogs = [];
	
	test.beforeEach(async ({ page }) => {
		consoleLogs = [];
		page.on('console', msg => consoleLogs.push(msg.text()));
	});
	
	test.afterEach(async ({ page }) => {
		if (consoleLogs.length > 0) {
			console.log('Page logs:', consoleLogs);
		}
		page.removeAllListeners('console');
	});
	
	test.afterEach( async ( { requestUtils } ) => {
		await requestUtils.deleteAllPages();
	} );
	
    test.todo('it shows the default value');
    test.todo('it shows immediately when loaded');
    test.todo('it shows a placeholder in the site editor');
    test.todo('it shows on the frontend');
    
	test( 'it shows the set `operating system`', async ( {
		page,
		admin,
		editor,
	} ) => {
		// Given a post with the block.
		await admin.createNewPost( {
				title: 'Download',
				postType: 'download',
				status: 'publish',
		} );
		editor.insertBlock('dc23-software-downloads/opeeatibg-system');

		// When the value is set in sidebar
			await editor.openDocumentSettingsSidebar();
			await page.getByRole( 'button', { name: 'Software Downloads' } ).click();
			await page.getByLabel( 'Operating system' ).type( 'Android' );

		// Then the value shows inside editor.
			await expect(
				editor.canvas
			).toHaveValue( 'Android' );
	} );
} );
