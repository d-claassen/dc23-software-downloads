<?php
/**
 * Integration tests for the Product main entity.
 *
 * @package DC23\SoftwareDownloads\Tests
 */

declare( strict_types=1 );

namespace DC23\SoftwareDownloads\Tests;

use DC23\SoftwareDownloads\Adapters\Product_Main_Entity;
// use DC23\ExcessiveSchema\Registries\Main_Entity_Registry;
use WP_UnitTestCase;

use function DC23\ExcessiveSchema\dc23_schema_get_main_entity;
use function DC23\ExcessiveSchema\dc23_schema_main_entity_exists;

final class Product_As_Main_Entity_Test extends WP_UnitTestCase {

	public function set_up(): void {
		parent::set_up();

		// Create test user for publisher. Needed for Article ouput from wordpress-seo below 26.7. Maybe also for Product.
		$this->user_id = self::factory()->user->create( [
			'display_name' => 'Test User',
			'user_email'   => 'test@example.com',
			'user_url'     => 'https://example.com',
		] );

		// Set Yoast user settings to use person schema
		\YoastSEO()->helpers->options->set( 'company_or_person', 'person' );
		\YoastSEO()->helpers->options->set( 'company_or_person_user_id', $this->user_id );
	}

	/**
	 * Override WordPress function that's incompatible with PHPUnit 10+.
	 */
	public function expectDeprecated(): void {
	}

	public function test_product_main_entity_registered_for_download_post_type(): void {
		// Verify Product is registered.
		$this->assertTrue(
			dc23_schema_main_entity_exists( 'download' ),
			'Product main entity should be registered for the "download" post type.'
		);

		// Verify basics.
		$main_entity = dc23_schema_get_main_entity( 'download' );
		$this->assertInstanceOf( Product_Main_Entity::class, $main_entity );
		$this->assertSame( 'Product', $main_entity->get_root_type() );
        
		// Create an indexable.
		$post_id   = self::factory()->post->create( [ 'post_status' => 'publish' ] );
		$indexable = \YoastSEO()->meta->for_post( $post_id )->context->indexable;
        
		// Verify id creation.
		$entity_id = $main_entity->get_entity_id( $indexable );
		$this->assertStringEndsWith( '#/schema/edd-product/' . $post_id, $entity_id );
		$this->assertStringStartsWith( $indexable->permalink, $entity_id );
	}

	public function test_dc23_schema_main_entity_filter_fires_on_edd_product_schema(): void {
		$captured_indexable = null;
		add_filter(
			'dc23_schema_main_entity',
			static function ( array $data, $indexable ) use ( &$captured_indexable ): array {
				$captured_indexable     = $indexable;
				$data['_test_marker'] = true;
				return $data;
			},
			10,
			2
		);

		$post_id = self::factory()->post->create( [
			'post_title'  => 'Test article',
			'post_status' => 'publish',
            'post_type'   => 'download',
		] );

		// Fetch the Yoast schema, which runs relevant filters.
		$product = $this->get_product_schema( $post_id );

		// Verify Product schema has the marker.
		$this->assertTrue(
			$product['_test_marker'] ?? false,
			'dc23_schema_main_entity should fire when an edd product schema filter is applied.'
		);
		$this->assertNotNull( $captured_indexable, 'Indexable should be passed to the filter.' );
		$this->assertSame( $post_id, $captured_indexable->object_id );
	}

	// -------------------------------------------------------------------------
	// Helpers
	// -------------------------------------------------------------------------

	private function get_schema( int $post_id, bool $debug = false, string $section = 'wpseo_head' ): array {
		$this->go_to( get_permalink( $post_id ) );

		ob_start();
		do_action( $section );
		$output = ob_get_clean();

		preg_match( '/<script type="application\/ld\+json"[^>]*>(.*?)<\/script>/s', $output, $matches );

		if ( $debug ) {
			var_dump( $matches[0] ?? 'no matches' );
		}

		return json_decode( $matches[1] ?? '{}', true );
	}

	private function get_product_schema( int $post_id, bool $debug = false ): ?array {
		$schema = $this->get_schema( $post_id, $debug, 'wp_footer' );

		foreach ( $schema ?? [] as $piece ) {
			if ( isset( $piece['@type'] ) && $piece['@type'] === 'Product' ) {
				return $piece;
			}
		}

		return null;
	}
}