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

		if ( ! empty( $context->main_entity_of_page ) ) {
			// Ensure the main entity is a list of entities.
			if ( isset( $webpage_piece['mainEntity']['@type' ] ) || isset( $webpage_piece['mainEntity']['@id'] ) ) {
				$webpage_piece['mainEntity'] = [ $webpage_piece['mainEntity'] ];
			}
			
			print '<!-- context main entity: ';
			var_dump( $context->main_entity_of_page );
			print '-->'.PHP_EOL;

			$missing_entities = array_diff( $context->main_entity_of_page, $webpage_piece[ 'mainEntity' ] );
			if ( ! empty( $missing_entities ) ) {
				array_push( $webpage_piece['mainEntity'], ...$missing_entities );
			}
		}

		// We normally add a `ReadAction` on pages, we're replacing with a `BuyAction` on product pages.
		$webpage_piece['potentialAction'] = [
			'@type'  => 'BuyAction',
			'target' => $context->canonical, // \YoastSEO()->meta->for_current_page()->canonical,
		];
		
		unset( $webpage_piece['datePublished'], $webpage_piece['dateModified'] );
		
		return $webpage_piece;
	}
}
