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
		if ( ! \is_array( $this->context->schema_page_type ) ) {
			$this->context->schema_page_type = [ $this->context->schema_page_type ];
		}
		$this->context->schema_page_type[]  = 'ItemPage';

		/*
		if ( ! in_array( 'ItemPage', (array) $webpage_piece['@type']) ) {
			$webpage_piece['@type'] = (array) $webpage_piece['@type'];
			$webpage_piece['@type'][] = 'ItemPage';
		}
		*/

		// We normally add a `ReadAction` on pages, we're replacing with a `BuyAction` on product pages.
		$webpage_piece['potentialAction'] = [
			'@type'  => 'BuyAction',
			'target' => $context->canonical, // \YoastSEO()->meta->for_current_page()->canonical,
		];
		
		unset( $webpage_piece['datePublished'], $webpage_piece['dateModified'] );
		
		return $webpage_piece;
	}
}
