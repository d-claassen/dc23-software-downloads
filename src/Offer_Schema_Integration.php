<?php

namespace DC23\SoftwareDownloads;

final class Offer_Schema_Integration {

    public function register(): void {
        \add_filter( 'edd_generate_download_structured_data_offer', [ $this, 'upgrade_price_to_price_specification' ], 10, 2 );
        \add_filter( 'edd_generate_download_structured_data_variable_price_offer', [ $this, 'upgrade_price_to_price_specification' ], 10, 2 );
    }
    
	/**
	 * Filter the structured data for a single price offer.
     *
     * @param array<string, string|array> $offer_piece Structured data for a single price offer.
     * @param EDD_Download                $download    Download object.
     */
    public function upgrade_price_to_price_specification( $offer_piece, $download ) {
        if ( ! is_array( $offer_piece ) ) {
            return $offer_piece;
        }
        
        if ( ! isset( $offer_piece['price'] ) || isset( $offer_piece['priceSpecification'] ) ) {
            return $offer_piece;
        }
        
        if ! is_scalar( $offer_piece['price'] ) {
            return;
        }

        $offer_piece['priceSpecification'] = [
            '@type'         => 'UnitPriceSpecification',
            'price'        => $offer_piece['price'],
            'priceCurrency' => $offer_piece['priceCurrency'],
            // 'valueAddedTaxIncluded' => true,
        ];

        unset(
            $offer_piece['price'],
            $offer_piece['priceCurrency'],
        );

        return $offer_piece;
    }
}