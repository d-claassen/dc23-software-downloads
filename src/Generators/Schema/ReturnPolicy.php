<?php

namespace DC23\SoftwareDownloads\Generators\Schema;

use \Yoast\WP\SEO\Generators\Schema\Abstract_Schema_Piece;

/**
 * Returns schema return policy data.
 */
class ReturnPolicy extends Abstract_Schema_Piece {

	/**
	 * Determines whether a piece should be added to the graph.
	 *
	 * @return bool
	 */
	public function is_needed() {
                if ( ! is_singular( 'download' ) ) {
                        return false;
                }
                
                $base_country = \edd_get_option('base_country', '');
                if ( $base_country === '' ) {
                    return false;
                }
                
                $download = \edd_get_download( $this->context->indexable->object_id );
                $download->refundability = null;
                
                $global_refundability   = \edd_get_option('refundability', 'refundable');
                $download_refundability = $download->get_refundability();

                // Custom refundable setting?
                if ( $download_refundability !== '' && $global_refundability !== $download_refundability ) {
                    return true;
                }

                $global_refund_window   = \edd_get_option('refund_window');
                $download_refund_window = $download->get_refund_window();

                // Custom refund_window setting?
                if ( $download_refund_window !== '' && $global_refund_window !== $download_refund_window ) {
                    return true;
                }
        
                // No custom settings.
                return false;
	}

	/**
	 * Render a return policy for all possible variations of the download.
	 *
	 * @return array<list<string, mixed>>
	 */
	public function generate() {
		$graph = [];

		$graph[] = $this->generate_return_policy();

		return $graph;
	}

	/**
	 * Generate a ReturnPolicy piece.
	 *
	 *
	 * @return array<sting, mixed>
	 */
                protected function generate_return_policy(): array {
		$id = $this->context->canonical . '#/schema/return-policy/' . \esc_attr( $this->context->indexable->object_id );
                                $base_country = \edd_get_option('base_country', '');
		$return_policy = [
			'@type'             => 'MerchantReturnPolicy',
			'@id'               => $id,
			'applicableCountry' => $base_country,
		];
                
                $download = \edd_get_download( $this->context->indexable->object_id );
                 $download->refundability = null;

                $refundability = $download->get_refundability();
                if ( $refundability === 'nonrefundable' ) {
                    $return_policy['returnPolicyCategory'] = 'https://schema.org/MerchantReturnNotPermitted';
                } else {
                    $return_window = $download->get_refund_window();
                    if ( empty( $return_window ) ) {
                        $return_policy['returnPolicyCategory'] = 'https://schema.org/MerchantReturnUnlimitedWindow';
                    } else {                
                        $return_policy['returnPolicyCategory'] = 'https://schema.org/MerchantReturnFiniteReturnWindow';
                        $return_policy['merchantReturnDays'] = absint( $return_window );
                    }
                }

		return $return_policy;
	}
}
