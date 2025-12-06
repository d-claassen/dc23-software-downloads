<?php

namespace DC23\SoftwareDownloads;

final class Product_Schema_Integration {

	public function register(): void {
		\add_filter( 'wpseo_schema_webpage', [ $this, 'filter_webpage_schema' ], 20, 2 );
	}

	/**
	 * Product schema graph.
	 *
	 * @param array<string, mixed> $webpage_piece
	 * @param WPSEO_Schema_Context $context
	 *
	 * @return array<string, mixed>
	 */
	public function filter_webpage_schema( $webpage_piece, $context ) {
		if ( ! is_singular( 'download' ) ) {
			return $webpage_piece;
		}

		// Ensure the download page is an ItemPage.
		if ( ! in_array( 'ItemPage', (array)	$webpage_piece['@type']) ) {
			$webpage_piece['@type'] = (array) $webpage_piece['@type'];
			$webpage_piece['@type'][] = 'ItemPage';
		}

		// For FAQ results, the questions need to be referred from the WebPage mainEntity prop.
		// The Yoast SEO FAQ block adds them there, but the EDD integration discards them.
		// Here we bring them back the question ids when they were stored in the context.
		if ( ! empty( $context->main_entity_of_page ) ) {
			// Ensure the main entity is a list of entities, so it can reference the product AND the questions.
			if ( isset( $webpage_piece['mainEntity']['@type' ] ) || isset( $webpage_piece['mainEntity']['@id'] ) ) {
				$webpage_piece['mainEntity'] = [ $webpage_piece['mainEntity'] ];
			}

			$missing_entities = array_filter(
				$context->main_entity_of_page,
				static function ( $entity ) use ($webpage_piece): bool {
					foreach( $webpage_piece[ 'mainEntity' ] as $assigned_entity ) {
						if( $entity === $assigned_entity ) {
						 // false: not missing.
							return false;
						}
					}
					// true: the context entity ref is missing from the webpage piece.
					return true;
				}
			);

			if ( ! empty( $missing_entities ) ) {
				array_push( $webpage_piece['mainEntity'], ...$missing_entities );
			}
		}

		// We normally add a `ReadAction` on pages, we're replacing with a `BuyAction` on product pages.
		$webpage_piece['potentialAction'] = [
			'@type'  => 'BuyAction',
			'target' => $context->canonical,
		];
		
		unset( $webpage_piece['datePublished'], $webpage_piece['dateModified'] );
		
		return $webpage_piece;
	}
}
