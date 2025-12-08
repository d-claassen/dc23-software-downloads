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
        
        $base_country = \edd_get_setting('base_country', '');
        if ( $base_country === '' ) {
            return false;
        }

        $download = \edd_get_download( $this->context->object_id );
        
        $global_refundable   = \edd_get_setting('refundable');
        $download_refundable = $download->get_refundable();

        // Custom refundable setting?
        if ( $global_refundable !== $download_refundable ) {
            return true;
        }

        $global_refund_window   = \edd_get_setting('refund_window');
        $download_refund_window = $download->get_refund_window();

        // Custom refund_window setting?
        if ( $global_refund_window !== $download_refund_window ) {
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
		$id = $this->context->canonical . '#/schema/return-policy/' . \esc_attr( $this->context->object_id );
                $base_country = \edd_get_setting('base_country', '');
		$data = [
			'@type'             => 'MerchantReturnPolicy',
			'@id'               => $id,
			'applicableCountry' => $base_country,
		];
                
                $download = \edd_get_download( $this->context->object_id );
                $download_refundable = $download->get_refundable();
                
                $download_refund_window = $download->get_refund_window();

		return $data;
	}
}
