<?php

namespace DC23\Tests\SoftwareDownloads;

/**
 * Class Offer_Schema_IntegrationTest.
 *
 * @testdox Offer Schema for a single post "download"
 */
class Offer_Schema_IntegrationTest extends \WP_UnitTestCase {

	private $user_id;

	public function setUp(): void {
		parent::setUp();

		// Yoast user settings
		$this->user_id = self::factory()->user->create();

		\YoastSEO()->helpers->options->set( 'company_or_person', 'person' );
		\YoastSEO()->helpers->options->set( 'company_or_person_user_id', $this->user_id );
		
		\EDD\Settings\Setting::update( 'base_country', 'NL' );
	}
	
	public function tearDown(): void {
		\remove_filter( 'edd_generate_download_structured_data_offer', [ $this, 'fake_sale_price_specification' ] );
		\remove_filter( 'edd_generate_download_structured_data_variable_price_offer', [ $this, 'fake_sale_price_specification' ] );
		
		parent::tearDown();
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
	
	public function fake_sale_price_specification($offer_piece): array {
		$offer_piece['priceSpecification'] = [
			[
				'@type' => 'UnitPriceSpecification',
				'price' => '9.50'
			],
			[
				'@type' => 'StrikethroughPriceSpecification',
				'price' => '12.50'
			]
		];
		
		return $offer_piece;
	}
	
	public function test_should_ignore_upgraded_offer_price(): void {
		// Register conflicting behavior on higher priority.
		\add_filter( 'edd_generate_download_structured_data_offer', [ $this, 'fake_sale_price_specification' ], 5, 2 );
		\add_filter( 'edd_generate_download_structured_data_variable_price_offer', [ $this, 'fake_sale_price_specification' ], 5, 2 );

		$post_id = self::factory()->post->create(
			array(
				'title'        => 'Software downloads',
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
		$offer_piece   = $product_piece['offers'];

		$this->assertIsList(
			$offer_piece['priceSpecification'],
			'offer price piece should be fake discount price spec'
		);
	}

	public function test_should_upgrade_offer_price_to_pricespecification(): void {
		$post_id = self::factory()->post->create(
			array(
				'title'        => 'Software downloads',
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
        $offer_piece   = $product_piece['offers'];

		$this->assertSame(
			'UnitPriceSpecification',
			$offer_piece['priceSpecification']['@type'],
			'offer price piece should be typed UnitPriceSpecification'
		);
		$this->assertSame(
			'0.00',
			$offer_piece['priceSpecification']['price'],
			'price on price specification'
		);
		$this->assertArrayNotHasKey( 'valueAddedTaxIncluded', $offer_piece['priceSpecification'], 'price spec should not have VAT info by default' );

		$this->assertArrayNotHasKey( 'price', $offer_piece, 'offer should not have price itself' );
		$this->assertArrayNotHasKey( 'priceCurrency', $offer_piece, 'offer should not have currency itself' );
	}
	
	public function test_should_upgrade_variable_prices_to_price_specification(): void {
		$post_id = self::factory()->post->create(
			array(
				'title'        => 'Software Downloads (with tiers)',
				'post_content' => $this->get_post_content(),
				'post_type'    => 'download',
			)
		);
		
		\update_post_meta(
			$post_id,
			'edd_variable_prices',
			[
				['index'=>0,'amount'=>'9.99','name'=>'lite'],
				['index'=>1,'amount'=>'14.99','name'=>'mid'],
				['index'=>2,'amount'=>'19.99','name'=>'top'],
			] );
		\update_post_meta( $post_id, '_variable_pricing', true );
		
		// Update object to persist meta value to indexable.
		self::factory()->post->update_object( $post_id, [] );

		$this->go_to( \get_permalink( $post_id ) );

		// $yoast_schema = $this->get_yoast_schema_output();
		// $this->assertJson( $yoast_schema, 'Yoast schema should be valid JSON' );
		// $yoast_schema_data = \json_decode( $yoast_schema, JSON_OBJECT_AS_ARRAY );

		$edd_schema = $this->get_edd_schema_output();
		$this->assertJson( $edd_schema, 'EDD schema should be valid JSON' );
		$edd_schema_data = \json_decode( $edd_schema, JSON_OBJECT_AS_ARRAY );
	
		// $organization_piece  = $this->get_piece_by_type( $yoast_schema_data['@graph'], 'Organization' );
		// $returnpolicy_piece  = $this->get_piece_by_type( $yoast_schema_data['@graph'], 'MerchantReturnPolicy' );
		$product_piece = $this->get_piece_by_type( $edd_schema_data, 'Product' );

		$this->assertIsList( $product_piece['offers'] );
		$this->assertCount( 3, $product_piece['offers'] );

		$this->assertArrayHasKey(
			'priceSpecification',
			$product_piece['offers'][0],
			'PriceSpecification piece in product offer'
		);
		$this->assertSame(
			$product_piece['offers'][0]['priceSpecification']['price'],
			'9.99',
			'PriceSpecification has variant price',
		);
		$this->assertArrayNotHasKey( 'valueAddedTaxIncluded', $product_piece['offers'][0]['priceSpecification'], 'price spec should not have VAT info by default' );

		$this->assertArrayHasKey(
			'priceSpecification',
			$product_piece['offers'][1],
			'PriceSpecification piece in product offer'
		);
		$this->assertSame(
			$product_piece['offers'][1]['priceSpecification']['price'],
			'14.99',
			'PriceSpecification has variant price',
		);
		$this->assertArrayNotHasKey( 'valueAddedTaxIncluded', $product_piece['offers'][0]['priceSpecification'], 'price spec should not have VAT info by default' );

		$this->assertArrayHasKey(
			'priceSpecification',
			$product_piece['offers'][2],
			'PriceSpecification piece in product offer'
		);
		$this->assertSame(
			$product_piece['offers'][2]['priceSpecification']['price'],
			'19.99',
			'PriceSpecification has variant price',
		);
		$this->assertArrayNotHasKey( 'valueAddedTaxIncluded', $product_piece['offers'][0]['priceSpecification'], 'price spec should not have VAT info by default' );
	}

	public function test_should_add_vat_inclusion_to_pricespecification(): void {
		$post_id = self::factory()->post->create(
			array(
				'title'        => 'Software downloads',
				'post_content' => $this->get_post_content(),
				'post_type'    => 'download',
			)
		);

		\EDD\Settings\Setting::update( 'enable_taxes', 'true' );
		\EDD\Settings\Setting::update( 'prices_include_tax', 'yes' );

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
  $offer_piece   = $product_piece['offers'];

		$this->assertArrayHasKey(
			'valueAddedTaxIncluded',
			$offer_piece['priceSpecification'],
			'price spec should have VAT info'
		);
		$this->assertSame(
			'true',
			$offer_piece['priceSpecification']['valueAddedTaxIncluded'],
			'VAT from settings'
		);
	}

	public function test_should_add_vat_exclusion_to_pricespecification(): void {
		$post_id = self::factory()->post->create(
			array(
				'title'        => 'Software downloads',
				'post_content' => $this->get_post_content(),
				'post_type'    => 'download',
			)
		);

		\EDD\Settings\Setting::update( 'enable_taxes', 'true' );
		\EDD\Settings\Setting::update( 'prices_include_tax', 'no' );

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
  $offer_piece   = $product_piece['offers'];

		$this->assertArrayHasKey(
			'valueAddedTaxIncluded',
			$offer_piece['priceSpecification'],
			'price spec should have VAT info'
		);
		$this->assertSame(
			'false',
			$offer_piece['priceSpecification']['valueAddedTaxIncluded'],
			'VAT from settings'
		);
	}
	public function test_should_skip_non_taxable_for_vat_to_pricespecification(): void {
		$post_id = self::factory()->post->create(
			array(
				'title'        => 'Software downloads',
				'post_content' => $this->get_post_content(),
				'post_type'    => 'download',
			)
		);

		\EDD\Settings\Setting::update( 'enable_taxes', 'true' );
		\EDD\Settings\Setting::update( 'prices_include_tax', 'yes' );
		\update_post_meta( $post_id, '_nontaxable', true );

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
  $offer_piece   = $product_piece['offers'];

		$this->assertArrayNotHasKey(
			'valueAddedTaxIncluded',
			$offer_piece['priceSpecification'],
			'price spec should not have VAT info'
		);
	}
	private function get_yoast_schema_output(): string {
		return $this->get_schema_output( 'wpseo_head' );
	}

	private function get_edd_schema_output(): string {
		return $this->get_schema_output( 'wp_footer' );
	}

	private function get_schema_output( string $action, bool $debug_wpseo_head = true ): string {

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
