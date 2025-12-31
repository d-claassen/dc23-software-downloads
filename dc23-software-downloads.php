<?php

/**
 * DC23 Software Downloads plugin.
 *
 * @author Dennis Claassen
 *
 * @wordpress-plugin
 * Plugin Name: DC23 Software Downloads
 * Version: 0.7.2
 * Description: Add structured markup related to Easy Digital Downloads in your WordPress website.
 * Requires at least: 6.2
 * Requires PHP: 8.1
 * Requires Plugins: wordpress-seo,easy-digital-downloads
 * Author: Dennis Claassen
 * Author URI: https://www.dennisclaassen.nl/
 * GitHub Plugin URI: https://github.com/d-claassen/dc23-software-downloads
 * Primary Branch: main
 * Release Asset: true
 */

declare( strict_types=1 );

require_once 'vendor/autoload.php';

if ( ! function_exists( 'dc23_software_downloads_setup' ) ) :
	/**
	 * Sets up plugin and registers support for various WordPress features.
	 */
	function dc23_software_downloads_setup(): void {
        // register_block_type( __DIR__ . '/build/software-downloads' );
        
        ( new \DC23\SoftwareDownloads\Checkout_Schema_Integration() )->register();
        ( new \DC23\SoftwareDownloads\Offer_Schema_Integration() )->register();
        ( new \DC23\SoftwareDownloads\Person_Schema_Integration() )->register();
        ( new \DC23\SoftwareDownloads\Product_Opengraph_Integration() )->register();
        ( new \DC23\SoftwareDownloads\Product_Schema_Integration() )->register();
        ( new \DC23\SoftwareDownloads\ReturnPolicy_Schema_Integration() )->register();
        ( new \DC23\SoftwareDownloads\Slack_Integration() )->register();
	}
endif;
add_action( 'init', 'dc23_software_downloads_setup' );

add_filter( 'edd_download_supports', function($supports) {
        $supports[] = 'custom-fields';
        return $supports;
}, 10, 1 );

/**
 * Load the admin script.
 */
function load_custom_wp_admin_scripts() {

	// Automatically load imported dependencies and assets version.
	$asset_file = include plugin_dir_path( __FILE__ ) . '/build/index.asset.php';

	// Load the required WordPress packages.
	foreach ( $asset_file['dependencies'] as $style ) {
		wp_enqueue_script( $style );
	}

	// Load our app.js.
	wp_register_script(
		'dc23-software-downloads',
		plugins_url( 'build/index.js', __FILE__ ),
		$asset_file['dependencies'],
		$asset_file['version'],
		true
	);
	wp_enqueue_script( 'dc23-software-downloads' );

	// Load our style.css.
	/* wp_register_style(
		'dc23-tea-extended',
		plugins_url( 'build/style-index.css', __FILE__ ),
		[],
		$asset_file['version']
	);
	wp_enqueue_style( 'dc23-tea-extended' );
    */
}

add_action( 'enqueue_block_editor_assets', 'load_custom_wp_admin_scripts' );

/**
 * Custom auth_callback for register_meta extending tribe_events.
 *
 * @param bool $allowed
 * @param string $meta_key
 * @param int $post_id
 *
 * @return bool
 */
function register_custom_download_meta_auth_callback( $allowed, $meta_key, $post_id ) {
	$post          = get_post( $post_id );
	$post_type_obj = get_post_type_object( $post->post_type );

	return current_user_can( $post_type_obj->cap->edit_post, $post_id );
}

function register_custom_download_meta() {
	register_meta(
		'post',
		'_SoftwareType',
		[
			'object_subtype' => 'download',
			'type'           => 'string',
			'single'         => true,
			'auth_callback'  => 'register_custom_download_meta_auth_callback',
			'label'          => 'Type of software',
			'show_in_rest'   => [
				'schema' => [
					'type'  => 'string',
				],
			],
		],
	);
}

add_action( 'rest_api_init', 'register_custom_download_meta' );
