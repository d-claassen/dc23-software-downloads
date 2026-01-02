/**
 * WordPress dependencies
 */
const { test, expect } = require( '@wordpress/e2e-test-utils-playwright' );

test.describe( 'Sidebar panel', () => {
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

	test.describe( 'panel per post type', () => {
		test( 'it shows for `downloads`', async ( {
			page,
			admin,
			editor,
		} ) => {
			await admin.createNewPost( {
				title: 'Download',
				postType: 'download',
				status: 'publish',
			} );
			await editor.openDocumentSettingsSidebar();
			
			const panelButton = page.getByRole( 'button', { name: 'Software Downloads' } );
			await expect( panelButton ).toBeVisible();
		} );

		test( 'invisible for regular posts', async ( {
			page,
			admin,
			editor,
			requestUtils,
		} ) => {
			const newPage = await requestUtils.createPost( {
				title: 'Post',
				status: 'publish',
			} );
			await admin.editPost( newPage.id );
			await editor.openDocumentSettingsSidebar();
			await expect(
				page.getByRole( 'button', { name: 'Software Downloads' } )
			).toBeHidden();
		} );

		test.fixme(
			'invisible in site editor',
			async ( { page, editor, admin, requestUtils } ) => {
				const newPage = await requestUtils.createPage( {
					title: 'Posts Page',
					status: 'publish',
				} );
				await admin.visitSiteEditor( {
					postId: newPage.id,
					postType: 'page',
					canvas: 'edit',
				} );
				await editor.openDocumentSettingsSidebar();
				await expect(
					page.getByRole( 'button', { name: 'Software Downloads' } )
				).toBeHidden();
			}
		);
	} );
	
	test.describe('panel fields', function() {
		test( 'it shows for `software type`', async ( {
			page,
			admin,
			editor,
		} ) => {
			await admin.createNewPost( {
				title: 'Download',
				postType: 'download',
				status: 'publish',
			} );
			await editor.openDocumentSettingsSidebar();
			// open panel.
			await page.getByRole( 'button', { name: 'Software Downloads' } ).click();

			await page.getByLabel( 'Software type' ).selectOption( { label: 'Web application' } );
			
			await editor.saveDraft();
			await page.reload();
			
			await editor.openDocumentSettingsSidebar();
			// open panel.
			// await page.getByRole( 'button', { name: 'Software Downloads' } ).click();

			await expect(
				page.getByLabel( 'Software type' )
			).toHaveValue( 'WebApplication' );
		} );
		
		test( 'it shows for `application category`', async ( {
			page,
			admin,
			editor,
		} ) => {
			await admin.createNewPost( {
				title: 'Download',
				postType: 'download',
				status: 'publish',
			} );
			await editor.openDocumentSettingsSidebar();
			// open panel.
			await page.getByRole( 'button', { name: 'Software Downloads' } ).click();

			await page.getByLabel( 'Software category' ).selectOption( { label: 'Lifestyle application' } );
			
			await editor.saveDraft();
			await page.reload();
			
			await editor.openDocumentSettingsSidebar();
			// open panel.
			// await page.getByRole( 'button', { name: 'Software Downloads' } ).click();

			await expect(
				page.getByLabel( 'Software category' )
			).toHaveValue( 'LifestyleApplication' );
		} );
	});
} );
