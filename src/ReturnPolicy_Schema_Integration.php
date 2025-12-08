<?php

namespace DC23\SoftwareDownloads;

final class ReturnPolicy_Schema_Integration {

    public function register(): void {
        \add_filter( 'wpseo_schema_organization', [ $this, 'extend_organization_with_return_policy' ], 10, 2 );
    }
    
    public function extend_organization_with_return_policy( $organization_piece, $context ) {
        $refundability = edd_get_option( 'refundability', 'refundable' );
        if ( $refundability === 'non-refundable' ) {
            $organization_piece['hasMerchantReturnPolicy'] = [
                '@type' => 'MerchantReturnPolicy',
            ];
        }
        
        return $organization_piece;
    }
}