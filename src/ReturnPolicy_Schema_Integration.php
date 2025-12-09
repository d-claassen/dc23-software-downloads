<?php

namespace DC23\SoftwareDownloads;

final class ReturnPolicy_Schema_Integration {

    public function register(): void {
        \add_filter( 'wpseo_schema_organization', [ $this, 'extend_organization_with_return_policy' ], 10, 2 );
        \add_filter( 'wpseo_schema_graph_pieces', [ $this, 'add_product_return_policy' ], 10, 2 );
        
        \add_filter( 'edd_generate_download_structured_data_offer', [ $this, 'extend_offer_with_return_policy' ], 10, 2 );
	
    }
    
    public function extend_organization_with_return_policy( $organization_piece, $context ) {
        $country = \edd_get_option( 'base_country', '' );
        if ( $country === '' ) {
            // Country is required. Bail out.
            return $organization_piece;
        }
        
        $return_policy = [
            '@type' => 'MerchantReturnPolicy',
            'applicableCountry' => $country,
        ];
        
        $refundability = edd_get_option( 'refundability', 'refundable' );
        if ( $refundability === 'nonrefundable' ) {
            $return_policy['returnPolicyCategory'] = 'https://schema.org/MerchantReturnNotPermitted';
        } else {
            $return_window = edd_get_option( 'refund_window', 0 ); // needs to be 0 here
            if ( empty( $return_window ) ) {
                $return_policy['returnPolicyCategory'] = 'https://schema.org/MerchantReturnUnlimitedWindow';
            } else {                
                $return_policy['returnPolicyCategory'] = 'https://schema.org/MerchantReturnFiniteReturnWindow';
                $return_policy['merchantReturnDays'] = absint( $return_window );
            }
        }
    
        $organization_piece['hasMerchantReturnPolicy'] = $return_policy;
        
        return $organization_piece;
    }
    
    /**
     * Adds a return policy graph piece to the schema collector.
     *
     * @param list<Abstract_Schema_Piece>  $pieces  The current graph pieces.
     * @param Meta_Tags_Context $context The current context.
     *
     * @return list<Abstract_Schema_Piece> The graph pieces.
     */
    public function add_product_return_policy( $pieces, $context ) {
       //$this->context = $context;
    
       $pieces[] = new Generators\Schema\ReturnPolicy( $context );
    
       return $pieces;
    }
		/**
		 * Filter the structured data for a single price offer.
		 *
		 * @since 3.1.4
		 * @param array        $offer   Structured data for a single price offer.
		 * @param EDD_Download $download Download object.
		 */
    public function extend_offer_with_return_policy( $offer_piece, $download ) {
        //todo: only if custom settings
        if ( ! $this->has_custom_refunds( $download ) ) {
            return $offer_piece;
        }
        
        $context = \YoastSEO()->meta->for_current_page();
        $returnPolicyId = $context->canonical . '#/schema/return-policy/' . $download->ID;
        $offer_piece['hasMerchantReturnPolicy'] = [
            '@id' => $returnPolicyId,
        ];
        
        return $offer_piece;
    }
    
    private function has_custom_refunds( \EDD_Download $download ): bool {
        $download->refundability = null;
        
        $global_refundability   = \edd_get_option('refundability', 'refundable');
        $download_refundability = $download->get_refundability();

        $function_refundability = \edd_get_download_refundability( $download->ID );

        printf(
            '<!-- %s/%s/%s -->%s',
            \var_export($global_refundability,true),
            \var_export($download_refundability,true),
            \var_export($function_refundability,true),
            PHP_EOL
        );
        // Custom refundable setting?
        if ( $global_refundability !== $download_refundability ) {
            return true;
        }

        $global_refund_window   = \edd_get_option('refund_window');
        $download_refund_window = $download->get_refund_window();

        printf(
            '<!-- %s/%s -->%s',
            \var_export($global_refund_window,true),
            \var_export($download_refund_window,true),
            PHP_EOL
        );
        // Custom refund_window setting?
        if ( $global_refund_window !== $download_refund_window ) {
            return true;
        }
        
        // No custom settings.
        return false;
    }
}
