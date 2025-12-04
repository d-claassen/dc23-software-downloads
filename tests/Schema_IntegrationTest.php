<?php

namespace DC23\Tests\SoftwareDownloads;

/**
 * Class Schema_IntegrationTest.
 *
 * @testdox Schema for a single post "download"
 */
class Schema_IntegrationTest extends \WP_UnitTestCase {

	private $user_id;

	public function setUp(): void {
		parent::setUp();

		// Yoast user settings
		$this->user_id = self::factory()->user->create();

		\YoastSEO()->helpers->options->set( 'company_or_person', 'person' );
		\YoastSEO()->helpers->options->set( 'company_or_person_user_id', $this->user_id );
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

	public function test_should_somehow_impact_download_webpage(): void {
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

		$person_piece  = $this->get_piece_by_type( $yoast_schema_data['@graph'], 'Person' );
		$product_piece = $this->get_piece_by_type( $edd_schema_data, 'Product' );

		$this->assertSame( 
			[ '@id' => $person_piece['@id'] ],
			$product_piece['brand'],
			'product piece should ref person as brand'
		);
		$this->assertSame( 
			[ '@id' => $person_piece['@id'] ],
			$product_piece['offers'][0]['seller'],
			'product piece should ref person as seller'
		);
		$this->assertContains( 'Brand', $person_piece['@type'], 'person should be Brand' );
	}
	
	public function skip_test_should_not_impact_page_webpage(): void {
		$post_id = self::factory()->post->create(
			array(
				'title'        => 'WebPage without estimated reading time',
				'post_content' => $this->get_post_content(),
				'post_type'    => 'page',
			)
		);

		// Update object to persist meta value to indexable.
		self::factory()->post->update_object( $post_id, [] );

		$this->go_to( \get_permalink( $post_id ) );

		$schema_output = $this->get_yoast_schema_output();

		$this->assertJson( $schema_output );

		$schema_data = \json_decode( $schema_output, JSON_OBJECT_AS_ARRAY );

		$webpage_piece = $this->get_piece_by_type( $schema_data['@graph'], 'WebPage' );

		$this->markTestIncomplete('Figure out assertions ');
        
        
		$this->assertArrayNotHasKey( 'timeRequired', $webpage_piece, 'timeRequired should not exist for WebPage' );
		
		$this->assertEmpty(
			array_filter( $schema_data['@graph'], fn( $piece ) => in_array( 'Article', (array) $piece['@type'], true ) ),
			'No Article on Page page'
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
