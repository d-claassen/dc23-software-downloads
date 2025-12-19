<?php

namespace DC23\Tests\SoftwareDownloads;

/**
 * Class ReturnPolicy_Schema_IntegrationTest.
 *
 * @testdox ReturnPolicy Schema for a single post "download"
 */
class ReturnPolicy_Schema_IntegrationTest extends \WP_UnitTestCase {

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
				Far far away, behind the word mountains, far from the countries Vokalia and Consonantia, there live 
				the blind texts. Separated they live in Bookmarksgrove right at the coast of the Semantics, a large 
				language ocean. A small river named Duden flows by their place and supplies it with the necessary
				regelialia. It is a paradisematic country, in which roasted parts of sentences fly into your mouth.
				Even the all-powerful Pointing has no control about the blind texts it is an almost unorthographic
				life One day however a small line of blind text by the name of Lorem Ipsum decided to leave for
				the far World of Grammar. The Big Oxmox advised her not to do so, because there were thousands
				of bad Commas, wild Question Marks and devious Semikoli, but the Little Blind Text didn’t
				listen. She packed her seven versalia, put her initial into the belt and made herself on the
				way. When she reached the first hills of the Italic Mountains, she had a last view back on
				the skyline of her hometown Bookmarksgrove, the headline of Alphabet Village and the subline
				of her own road, the Line Lane. Pityful a rethoric question ran over her cheek, then she
				continued her way. On her way she met a copy. The copy warned the Little Blind Text, that
				where it came from it would have been rewritten a thousand times and everything that was left
				from its origin would be the word "and".
				EOL;
	}
	
	public function test_should_have_country_for_return_policy(): void {
		$post_id = self::factory()->post->create(
			array(
				'title'        => 'WebPage with estimated reading time',
				'post_content' => $this->get_post_content(),
				'post_type'    => 'download',
			)
		);

		\EDD\Settings\Setting::update( 'base_country', '' );

		// Update object to persist meta value to indexable.
		self::factory()->post->update_object( $post_id, [] );

		$this->go_to( \get_permalink( $post_id ) );

		$yoast_schema = $this->get_yoast_schema_output();
		$this->assertJson( $yoast_schema, 'Yoast schema should be valid JSON' );
		$yoast_schema_data = \json_decode( $yoast_schema, JSON_OBJECT_AS_ARRAY );

		$edd_schema = $this->get_edd_schema_output();
		$this->assertJson( $edd_schema, 'EDD schema should be valid JSON' );
		$edd_schema_data = \json_decode( $edd_schema, JSON_OBJECT_AS_ARRAY );
	
		$organization_piece  = $this->get_piece_by_type( $yoast_schema_data['@graph'], 'Organization' );
		$product_piece = $this->get_piece_by_type( $edd_schema_data, 'Product' );

		$this->assertArrayNotHasKey(
			'hasMerchantReturnPolicy',
			$organization_piece,
			'ReturnPolicy piece in Organization'
		);
		$this->assertArrayNotHasKey(
			'hasMerchantReturnPolicy',
			$product_piece['offers'],
			'ReturnPolicy piece in product offer'
		);
	}

	public function test_should_have_infinite_return_policy(): void {
		$post_id = self::factory()->post->create(
			array(
				'title'        => 'WebPage with estimated reading time',
				'post_content' => $this->get_post_content(),
				'post_type'    => 'download',
			)
		);

		// Update object to persist meta value to indexable.
		self::factory()->post->update_object( $post_id, [] );

		$this->go_to( \get_permalink( $post_id ) );

		$yoast_schema = $this->get_yoast_schema_output();
		$this->assertJson( $yoast_schema, 'Yoast schema should be valid JSON' );
		$yoast_schema_data = \json_decode( $yoast_schema, JSON_OBJECT_AS_ARRAY );

		$edd_schema = $this->get_edd_schema_output();
		$this->assertJson( $edd_schema, 'EDD schema should be valid JSON' );
		$edd_schema_data = \json_decode( $edd_schema, JSON_OBJECT_AS_ARRAY );
	
		$organization_piece  = $this->get_piece_by_type( $yoast_schema_data['@graph'], 'Organization' );
		$product_piece = $this->get_piece_by_type( $edd_schema_data, 'Product' );

		$this->assertArrayHasKey(
			'hasMerchantReturnPolicy',
			$organization_piece,
			'ReturnPolicy piece in Organization'
		);
		$this->assertSame( 
			'https://schema.org/MerchantReturnUnlimitedWindow',
			$organization_piece['hasMerchantReturnPolicy']['returnPolicyCategory'],
			'infinite window'
		);
		$this->assertArrayNotHasKey(
			'hasMerchantReturnPolicy',
			$product_piece['offers'],
			'ReturnPolicy piece in product offer'
		);
	}
	
	public function test_should_have_limited_return_policy(): void {
		$post_id = self::factory()->post->create(
			array(
				'title'        => 'WebPage with estimated reading time',
				'post_content' => $this->get_post_content(),
				'post_type'    => 'download',
			)
		);
		
		\EDD\Settings\Setting::update( 'refund_window', 30 );

		// Update object to persist meta value to indexable.
		self::factory()->post->update_object( $post_id, [] );

		$this->go_to( \get_permalink( $post_id ) );

		$yoast_schema = $this->get_yoast_schema_output();
		$this->assertJson( $yoast_schema, 'Yoast schema should be valid JSON' );
		$yoast_schema_data = \json_decode( $yoast_schema, JSON_OBJECT_AS_ARRAY );

		$edd_schema = $this->get_edd_schema_output();
		$this->assertJson( $edd_schema, 'EDD schema should be valid JSON' );
		$edd_schema_data = \json_decode( $edd_schema, JSON_OBJECT_AS_ARRAY );
	
		$organization_piece  = $this->get_piece_by_type( $yoast_schema_data['@graph'], 'Organization' );
		$product_piece = $this->get_piece_by_type( $edd_schema_data, 'Product' );

		$this->assertArrayHasKey(
			'hasMerchantReturnPolicy',
			$organization_piece,
			'ReturnPolicy piece in Organization'
		);
		$this->assertSame( 
			'https://schema.org/MerchantReturnFiniteReturnWindow',
			$organization_piece['hasMerchantReturnPolicy']['returnPolicyCategory'],
			'finite window'
		);
		$this->assertSame( 
			30,
			$organization_piece['hasMerchantReturnPolicy']['merchantReturnDays'],
			'30 day window'
		);
		$this->assertArrayNotHasKey(
			'hasMerchantReturnPolicy',
			$product_piece['offers'],
			'ReturnPolicy piece in product offer'
		);
	}
		public function test_should_have_no_return_policy(): void {
		$post_id = self::factory()->post->create(
			array(
				'title'        => 'WebPage with estimated reading time',
				'post_content' => $this->get_post_content(),
				'post_type'    => 'download',
			)
		);
		
		\EDD\Settings\Setting::update( 'refundability', 'nonrefundable' );

		// Update object to persist meta value to indexable.
		self::factory()->post->update_object( $post_id, [] );

		$this->go_to( \get_permalink( $post_id ) );

		$yoast_schema = $this->get_yoast_schema_output();
		$this->assertJson( $yoast_schema, 'Yoast schema should be valid JSON' );
		$yoast_schema_data = \json_decode( $yoast_schema, JSON_OBJECT_AS_ARRAY );

		$edd_schema = $this->get_edd_schema_output();
		$this->assertJson( $edd_schema, 'EDD schema should be valid JSON' );
		$edd_schema_data = \json_decode( $edd_schema, JSON_OBJECT_AS_ARRAY );
	
		$organization_piece  = $this->get_piece_by_type( $yoast_schema_data['@graph'], 'Organization' );
		$product_piece = $this->get_piece_by_type( $edd_schema_data, 'Product' );

		$this->assertArrayHasKey(
			'hasMerchantReturnPolicy',
			$organization_piece,
			'ReturnPolicy piece in Organization'
		);
		$this->assertSame( 
			'https://schema.org/MerchantReturnNotPermitted',
			$organization_piece['hasMerchantReturnPolicy']['returnPolicyCategory'],
			'no returns'
		);
		$this->assertArrayNotHasKey(
			'hasMerchantReturnPolicy',
			$product_piece['offers'],
			'ReturnPolicy piece in product offer'
		);
	}
	
	public function test_should_have_custom_return_window_policy(): void {
		$post_id = self::factory()->post->create(
			array(
				'title'        => 'WebPage with estimated reading time',
				'post_content' => $this->get_post_content(),
				'post_type'    => 'download',
			)
		);
		
		\update_post_meta( $post_id, '_edd_refundability', 'refundable' );
		\update_post_meta( $post_id, '_edd_refund_window', '60' );
		
		// Update object to persist meta value to indexable.
		self::factory()->post->update_object( $post_id, [] );

		$this->go_to( \get_permalink( $post_id ) );

		$yoast_schema = $this->get_yoast_schema_output();
		$this->assertJson( $yoast_schema, 'Yoast schema should be valid JSON' );
		$yoast_schema_data = \json_decode( $yoast_schema, JSON_OBJECT_AS_ARRAY );

		$edd_schema = $this->get_edd_schema_output();
		$this->assertJson( $edd_schema, 'EDD schema should be valid JSON' );
		$edd_schema_data = \json_decode( $edd_schema, JSON_OBJECT_AS_ARRAY );
	
		$organization_piece  = $this->get_piece_by_type( $yoast_schema_data['@graph'], 'Organization' );
		$returnpolicy_piece  = $this->get_piece_by_type( $yoast_schema_data['@graph'], 'MerchantReturnPolicy' );
		$product_piece = $this->get_piece_by_type( $edd_schema_data, 'Product' );

		$this->assertArrayHasKey(
			'hasMerchantReturnPolicy',
			$product_piece['offers'],
			'ReturnPolicy piece in product offer'
		);
		$this->assertSame(
			$product_piece['offers']['hasMerchantReturnPolicy']['@id'],
			$returnpolicy_piece['@id'],
			'Product ReturnPolicy refers to custom ReturnPolicy',
		);
				
		$this->assertSame(
			'https://schema.org/MerchantReturnFiniteReturnWindow',
			$returnpolicy_piece['returnPolicyCategory'],
			'custom finite window'
		);
		$this->assertSame(
			60,
			$returnpolicy_piece['merchantReturnDays'],
			'custom 60 day window'
		);
	}
	
		public function test_should_have_custom_no_return_policy(): void {
		$post_id = self::factory()->post->create(
			array(
				'title'        => 'WebPage with estimated reading time',
				'post_content' => $this->get_post_content(),
				'post_type'    => 'download',
			)
		);
		
		\update_post_meta( $post_id, '_edd_refundability', 'nonrefundable' );
		\update_post_meta( $post_id, '_edd_refund_window', '' );
		
		// Update object to persist meta value to indexable.
		self::factory()->post->update_object( $post_id, [] );

		$this->go_to( \get_permalink( $post_id ) );

		$yoast_schema = $this->get_yoast_schema_output();
		$this->assertJson( $yoast_schema, 'Yoast schema should be valid JSON' );
		$yoast_schema_data = \json_decode( $yoast_schema, JSON_OBJECT_AS_ARRAY );

		$edd_schema = $this->get_edd_schema_output();
		$this->assertJson( $edd_schema, 'EDD schema should be valid JSON' );
		$edd_schema_data = \json_decode( $edd_schema, JSON_OBJECT_AS_ARRAY );
	
		$organization_piece  = $this->get_piece_by_type( $yoast_schema_data['@graph'], 'Organization' );
		$returnpolicy_piece  = $this->get_piece_by_type( $yoast_schema_data['@graph'], 'MerchantReturnPolicy' );
		$product_piece = $this->get_piece_by_type( $edd_schema_data, 'Product' );

		$this->assertArrayHasKey(
			'hasMerchantReturnPolicy',
			$product_piece['offers'],
			'ReturnPolicy piece in product offer',
		);
		$this->assertSame(
			$product_piece['offers']['hasMerchantReturnPolicy']['@id'],
			$returnpolicy_piece['@id'],
			'Product ReturnPolicy refers to custom ReturnPolicy',
		);
				
		$this->assertSame(
			'https://schema.org/MerchantReturnNotPermitted',
			$returnpolicy_piece['returnPolicyCategory'],
			'no returns',
		);
		$this->assertArrayNotHasKey(
			'merchantReturnDays',
			$returnpolicy_piece,
			'no return days in NotPermitted ReturnPolicy piece',
		);
	}
	
	public function test_should_have_custom_unlimited_window_policy(): void {
		$post_id = self::factory()->post->create(
			array(
				'title'        => 'WebPage with estimated reading time',
				'post_content' => $this->get_post_content(),
				'post_type'    => 'download',
			)
		);
		
		\update_post_meta( $post_id, '_edd_refundability', 'refundable' );
		\update_post_meta( $post_id, '_edd_refund_window', '0' );
		
		// Update object to persist meta value to indexable.
		self::factory()->post->update_object( $post_id, [] );

		$this->go_to( \get_permalink( $post_id ) );

		$yoast_schema = $this->get_yoast_schema_output();
		$this->assertJson( $yoast_schema, 'Yoast schema should be valid JSON' );
		$yoast_schema_data = \json_decode( $yoast_schema, JSON_OBJECT_AS_ARRAY );

		$edd_schema = $this->get_edd_schema_output();
		$this->assertJson( $edd_schema, 'EDD schema should be valid JSON' );
		$edd_schema_data = \json_decode( $edd_schema, JSON_OBJECT_AS_ARRAY );
	
		$organization_piece  = $this->get_piece_by_type( $yoast_schema_data['@graph'], 'Organization' );
		$returnpolicy_piece  = $this->get_piece_by_type( $yoast_schema_data['@graph'], 'MerchantReturnPolicy' );
		$product_piece = $this->get_piece_by_type( $edd_schema_data, 'Product' );

		$this->assertArrayHasKey(
			'hasMerchantReturnPolicy',
			$product_piece['offers'],
			'ReturnPolicy piece in product offer'
		);
		$this->assertSame(
			$product_piece['offers']['hasMerchantReturnPolicy']['@id'],
			$returnpolicy_piece['@id'],
			'Product ReturnPolicy refers to custom ReturnPolicy',
		);
				
		$this->assertSame(
			'https://schema.org/MerchantReturnUnlimitedWindow',
			$returnpolicy_piece['returnPolicyCategory'],
			'custom infinite window'
		);
		$this->assertArrayNotHasKey(
			'merchantReturnDays',
			$returnpolicy_piece,
			'no specific return days window'
		);
	}
	
		public function test_should_have_variable_custom_unlimited_window_policy(): void {
		$post_id = self::factory()->post->create(
			array(
				'title'        => 'WebPage with estimated reading time',
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
		
		\update_post_meta( $post_id, '_edd_refundability', 'refundable' );
		\update_post_meta( $post_id, '_edd_refund_window', '0' );
		
		// Update object to persist meta value to indexable.
		self::factory()->post->update_object( $post_id, [] );

		$this->go_to( \get_permalink( $post_id ) );

		$yoast_schema = $this->get_yoast_schema_output();
		$this->assertJson( $yoast_schema, 'Yoast schema should be valid JSON' );
		$yoast_schema_data = \json_decode( $yoast_schema, JSON_OBJECT_AS_ARRAY );

		$edd_schema = $this->get_edd_schema_output();
		$this->assertJson( $edd_schema, 'EDD schema should be valid JSON' );
		$edd_schema_data = \json_decode( $edd_schema, JSON_OBJECT_AS_ARRAY );
	
		$organization_piece  = $this->get_piece_by_type( $yoast_schema_data['@graph'], 'Organization' );
		$returnpolicy_piece  = $this->get_piece_by_type( $yoast_schema_data['@graph'], 'MerchantReturnPolicy' );
		$product_piece = $this->get_piece_by_type( $edd_schema_data, 'Product' );

		$this->assertIsList( $product_piece['offers'] );
		$this->assertCount( 3, $product_piece['offers'] );

		$this->assertArrayHasKey(
			'hasMerchantReturnPolicy',
			$product_piece['offers'][0],
			'ReturnPolicy piece in product offer'
		);
		$this->assertSame(
			$product_piece['offers'][0]['hasMerchantReturnPolicy']['@id'],
			$returnpolicy_piece['@id'],
			'Product ReturnPolicy refers to custom ReturnPolicy',
		);
		
		$this->assertArrayHasKey(
			'hasMerchantReturnPolicy',
			$product_piece['offers'][1],
			'ReturnPolicy piece in product offer'
		);
		$this->assertSame(
			$product_piece['offers'][1]['hasMerchantReturnPolicy']['@id'],
			$returnpolicy_piece['@id'],
			'Product ReturnPolicy refers to custom ReturnPolicy',
		);
		
				$this->assertArrayHasKey(
			'hasMerchantReturnPolicy',
			$product_piece['offers'][2],
			'ReturnPolicy piece in product offer'
		);
		$this->assertSame(
			$product_piece['offers'][2]['hasMerchantReturnPolicy']['@id'],
			$returnpolicy_piece['@id'],
			'Product ReturnPolicy refers to custom ReturnPolicy',
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
			print $wpseo_head . PHP_EOL;
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
