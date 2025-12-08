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
                'applicableCountry' => '',
                'returnPolicyCategory' => 'https://schema.org/MerchantReturnNotPermitted',
            ];
        } else {
            $return_window = edd_get_option( 'refund_window', 0 ); // needs to be 0 here

            if ( empty( $return_window ) ) {
                $organization_piece['hasMerchantReturnPolicy'] = [
                    '@type' => 'MerchantReturnPolicy',
                    'applicableCountry' => '',
                    'returnPolicyCategory' => 'https://schema.org/MerchantReturnUnlimitedWindow',
                ];
            } else {                
                $organization_piece['hasMerchantReturnPolicy'] = [
                    '@type' => 'MerchantReturnPolicy',
                    'applicableCountry' => '',
                    'returnPolicyCategory' => 'https://schema.org/MerchantReturnFiniteReturnWindow',
                    'merchantReturnDays' => absint( $return_window ),
                ];
            }
        }
        
        return $organization_piece;
    }
}