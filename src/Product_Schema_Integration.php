<?php

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
		if ( ! in_array( (array)	$webpage_piece['@type'], 'ItemPage' ) ) {
			$webpage_piece['@type'] = (array) $webpage_piece['@type'];
			$webpage_piece['@type'][] = 'ItemPage';
		}
		
		return $webpage_piece;
	}
}