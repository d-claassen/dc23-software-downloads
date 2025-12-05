<?php

namespace DC23\SoftwareDownloads;

final class Checkout_Schema_Integration {

    public function register(): void {
        \add_filter( 'wpseo_schema_webpage', [ $this, 'filter_webpage_schema' ], 20, 2 );
    }

	/**
	 * Checkout schema graph.
	 *
	 * @param array<string, mixed> $webpage_piece
	 * @param WPSEO_Schema_Context $context
	 *
	 * @return array<string, mixed>
	 */
	public function filter_webpage_schema( $webpage_piece, $context ) {
		if ( ! edd_is_checkout() ) {
			return $webpage_piece;
		}

		// Ensure the download page is a CheckoutPage.
		if ( ! in_array( 'CheckoutPage', (array) $webpage_piece['@type']) ) {
			$webpage_piece['@type'] = (array) $webpage_piece['@type'];
			$webpage_piece['@type'][] = 'CheckoutPage';
		}

		return $webpage_piece;
	}
}