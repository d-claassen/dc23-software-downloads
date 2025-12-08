<?php

namespace DC23\SoftwareDownloads;

final class ReturnPolicy_Schema_Integration {

    public function register(): void {
        \add_filter( 'wpseo_schema_organization', [ $this, 'extend_organization_with_return_policy' ], 10, 2 );
    }
    
    public function extend_organization_with_return_policy( $organization_piece, $context ) {
        $country = \edd_get_option( 'base_country', '' );
        if ( $country === '' ) {
            // Country is required. Bail out.
            return $organization_piece;
        }
        
        $return_policy = [
            '@type' => 'MerchantReturnPolicy',
            'applicableCountry' => $base_country,
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
        print '<!-- ';
        \printf( ' refundability: %s', $refundability );
        \printf( 'refund/return_window: %s', $return_window ?? 'n/a' );
        print ' -->';
    
        $organization_piece['hasMerchantReturnPolicy'] = $return_policy;
        
        return $organization_piece;
    }
}