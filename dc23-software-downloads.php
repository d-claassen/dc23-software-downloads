<?php

/**
 * DC23 Software Downloads plugin.
 *
 * @author Dennis Claassen
 *
 * @wordpress-plugin
 * Plugin Name: DC23 Software Downloads
 * Version: 0.7.0
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
		( new \DC23\SoftwareDownloads\Person_Schema_Integration() )->register();
        ( new \DC23\SoftwareDownloads\Product_Opengraph_Integration() )->register();
        ( new \DC23\SoftwareDownloads\Product_Schema_Integration() )->register();
        ( new \DC23\SoftwareDownloads\ReturnPolicy_Schema_Integration() )->register();
        ( new \DC23\SoftwareDownloads\Slack_Integration() )->register();
	}
endif;
add_action( 'init', 'dc23_software_downloads_setup' );
