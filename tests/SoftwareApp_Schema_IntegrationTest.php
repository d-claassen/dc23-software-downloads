<?php

namespace DC23\Tests\SoftwareDownloads;

/**
 * Class SoftwareApp_Schema_IntegrationTest.
 *
 * @testdox SoftwareApp Schema for a single post "download"
 */
class SoftwareApp_Schema_IntegrationTest extends \WP_UnitTestCase {

	private $user_id;

	public function setUp(): void {
		parent::setUp();

		// Yoast user settings
		$this->user_id = self::factory()->user->create();

		\YoastSEO()->helpers->options->set( 'company_or_person', 'person' );
		\YoastSEO()->helpers->options->set( 'company_or_person_user_id', $this->user_id );
		
		\EDD\Settings\Setting::update( 'base_country', 'NL' );
	}

	// override wordpress function thats incompatible
	// with phpunit 10.
	public function expectDeprecated() {
	}
	
	private function get_post_content(): string {
		return <<<'EOL'
			<!-- wp:heading -->
			<h1 class="wp-block-heading">Software downloads</h3>
			<!-- /wp:heading -->
			EOL;
	}

	public function test_should_type_download_as_software_application(): void {
		$post_id = self::factory()->post->create(
			array(
				'title'        => 'Software download',
				'post_content' => $this->get_post_content(),
				'post_type'    => 'download',
			)
		);

		// Update object to persist meta value to indexable.
		self::factory()->post->update_object( $post_id, [] );

		$this->go_to( \get_permalink( $post_id ) );

		// $yoast_schema = $this->get_yoast_schema_output();
		// $this->assertJson( $yoast_schema, 'Yoast schema should be valid JSON' );
		// $yoast_schema_data = \json_decode( $yoast_schema, JSON_OBJECT_AS_ARRAY );

        $edd_schema = $this->get_edd_schema_output();
        $this->assertJson( $edd_schema, 'EDD schema should be valid JSON' );
        $edd_schema_data = \json_decode( $edd_schema, JSON_OBJECT_AS_ARRAY );

		// $webpage_piece  = $this->get_piece_by_type( $yoast_schema_data['@graph'], 'ItemPage' );
        $product_piece = $this->get_piece_by_type( $edd_schema_data, 'Product' );

		$this->assertEqualsCanonicalizing( 
			['Product', 'SoftwareApplication'],
			$product_piece['@type'],
			'product piece should be typed additionally with SoftwareApplication'
		);
	}
	
	private function get_yoast_schema_output(): string {
		return $this->get_schema_output( 'wpseo_head' );
	}

	private function get_edd_schema_output(): string {
		return $this->get_schema_output( 'wp_footer' );
	}

	private function get_schema_output( string $action, bool $debug_wpseo_head = false ): string {

		ob_start();
		do_action( $action );
		$wpseo_head = ob_get_contents();
		ob_end_clean();

		if ( $debug_wpseo_head ) {
			print $wpseo_head;
		}

		$dom = new \DOMDocument();
		$dom->loadHTML( $wpseo_head );
		$scripts = $dom->getElementsByTagName( 'script' );
		foreach ( $scripts as $script ) {
			if ( $script instanceof \DOMElement && $script->getAttribute( 'type' ) === 'application/ld+json' ) {
				return $script->textContent;
			}
		}

		throw new \LengthException( 'No schema script was found in the wpseo_head output.' );
	}

	/**
	 * Find a Schema.org piece in the root of the Graph by its type.
	 *
	 * @param array<int, array{"@type": string}> $graph Schema.org graph.
	 * @param string|array<int, string>          $type  Schema type to search for.
	 *
	 * @return array{"@type": string} The matching schema.org piece.
	 */
	private function get_piece_by_type( $graph, $type ): array {
		$nodes_of_type = array_filter( $graph, fn( $piece ) => ! empty( array_intersect( (array) $piece['@type'], (array) $type ) ) );

		if ( empty( $nodes_of_type ) ) {
			throw new \InvalidArgumentException( 'No piece found for type' );
		}

		// Return first instance.
		return reset( $nodes_of_type );
	}
}
