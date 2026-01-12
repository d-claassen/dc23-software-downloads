/**
 * WordPress dependencies
 */
const { test, expect } = require( '@wordpress/e2e-test-utils-playwright' );

const noop = () => {};
const DEBUG_MODE = false;

test.describe( 'Block "Application category"', () => {
	let consoleLogs = [];
	
	test.beforeEach(async ({ page }) => {
		consoleLogs = [];
		page.on('console', msg => consoleLogs.push(msg.text()));
	});
	
	test.afterEach(async ({ page }) => {
		if (DEBUG_MODE && consoleLogs.length > 0) {
			console.log('Page logs:', consoleLogs);
		}
		page.removeAllListeners('console');
	});
	
	test.afterEach( async ( { requestUtils } ) => {
		await requestUtils.deleteAllPages();
	} );
	
	test.skip('it shows the default value', noop);
	test.skip('it shows a placeholder in the site editor', noop);

	test('it shows immediately when loaded', async ( {
		page,
		admin,
		editor,
	} ) => {
		// Given a saved post with the value.
		await admin.createNewPost( {
			title: 'Download',
			postType: 'download',
			status: 'publish',
		} );
		await editor.openDocumentSettingsSidebar();
		//await page.getByRole( 'button', { name: 'Software Downloads' } ).click();
		
		const sectionButton = await page.getByRole( 'button', { name: 'Software Downloads' } );
		// Open section if needed
		if ( ( await sectionButton.getAttribute( 'aria-expanded' ) ) === 'false' ) {
			await sectionButton.click();
		}
		
		await page.getByLabel( 'Software category' ).selectOption( { label: 'Health application' } );
		await editor.saveDraft();
		
		// When the block is added on a later visit.
		await page.reload();
		await editor.insertBlock({name: 'dc23-software-downloads/application-category'});

		// Then the value shows inside editor.
		await expect(
			editor.canvas.locator('[data-type="dc23-software-downloads/application-category"]')
		).toContainText( 'Health application' );
	} );
    
	test( 'it shows the set `application category`', async ( {
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
		editor.insertBlock({name: 'dc23-software-downloads/application-category'});

		// When the value is set in sidebar
		await editor.openDocumentSettingsSidebar();

			page
				.getByRole( 'region', { name: 'Editor settings' } )
				.getByRole( 'tab', { selected: false } )
				.click();
		
		//await page.getByRole( 'button', { name: 'Software Downloads' } ).click();
		const sectionButton = await page.getByRole( 'button', { name: 'Software Downloads' } );
		// Open section if needed
		if ( ( await sectionButton.getAttribute( 'aria-expanded' ) ) === 'false' ) {
			await sectionButton.click();
		}
		await page.getByLabel( 'Software category' ).selectOption( { label: 'Travel application' } );

		// Then the value shows inside editor.
		await expect(
			editor.canvas.locator('[data-type="dc23-software-downloads/application-category"]')
		).toContainText( 'Travel application' );
	} );
	
	test('renders correctly on frontend', async ({ admin, context, editor, page }) => {
		// Given a post with the block exists.
		await admin.createNewPost( {
			title: 'Download',
			postType: 'download',
			status: 'publish',
		} );
		await editor.insertBlock({ name: 'dc23-software-downloads/application-category' });
		await editor.openDocumentSettingsSidebar();

			page
				.getByRole( 'region', { name: 'Editor settings' } )
				.getByRole( 'tab', { selected: false } )
				.click();
		
		//await page.getByRole( 'button', { name: 'Software Downloads' } ).click();
		const sectionButton = await page.getByRole( 'button', { name: 'Software Downloads' } );
		// Open section if needed
		if ( ( await sectionButton.getAttribute( 'aria-expanded' ) ) === 'false' ) {
			await sectionButton.click();
		}
		await page.getByLabel( 'Software category' ).selectOption( { label: 'Finance application' } );
		
		// When the post is published on the frontend.
		await editor.publishPost();

		const [newPage] = await Promise.all([
			context.waitForEvent('page', {timeout: 1500}).catch(() => null),
			page.getByText('View Download').first().click(),
		]);
		// Fallback for pre-WP6.9
		const postPage = newPage || page;
	
		// Then the value shows on the frontend page.
		await expect(postPage.locator('body')).toContainText('Finance application');
	});
} );
