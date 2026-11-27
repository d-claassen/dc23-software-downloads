<?php
/**
 * Integration that registers the Product main entity for EDD downloads.
 *
 * @package DC23\SoftwareDownloads
 */

declare( strict_types=1 );

namespace DC23\SoftwareDownloads\Integrations;

use DC23\SoftwareDownloads\Adapters\Product_Main_Entity;

use function DC23\ExcessiveSchema\dc23_schema_register_main_entity;

/**
 * Registers the Product main entity against EDD's download post type.
 *
 * Soft dependency on dc23-excessive-schema: hooks the registration action
 * unconditionally, but the action only fires when that plugin is active.
 * If it's not active, the registration is a silent no-op.
 *
 * Hard requirement on EDD itself is checked at registration time — without
 * the EDD post type, there's nothing to register against.
 */
final class Product_Schema_Adapter {

	public function register(): void {
		add_action( 'dc23_schema_register_main_entities', [ $this, 'register_main_entity' ] );
	}

	public function register_main_entity(): void {
		if ( ! post_type_exists( 'download' ) ) {
			return;
		}

		dc23_schema_register_main_entity( 'download', new Product_Main_Entity() );
	}
}