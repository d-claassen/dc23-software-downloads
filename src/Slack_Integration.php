<?php

namespace DC23\SoftwareDownloads;

use Yoast\WP\SEO\Presentations\Indexable_Presentation;

final class Slack_Integration {

	public function register(): void {
		add_filter( 'wpseo_enhanced_slack_data', [ $this, 'filter_enhanced_data' ], 10, 2 );
	}

	/**
	 * Replaces the default enhanced data (author, reading time) with product-related data.
	 *
	 * @param array<string,string>   $data
	 * @param Indexable_Presentation $presentation
	 *
	 * @return array<string,string>
	 */
	public function filter_enhanced_data( $data, $presentation ) {
		$object   = $presentation->model;
		$download = \edd_get_download( $object->object_id );

		if ( ! $download instanceof \EDD_Download ) {
			return $data;
		}

		// Omit the price amount for variable downloads.
		$show_price   = ! $download->has_variable_prices();
		$availability = 'In stock';

		// Override the data.
		$data         = [];
		if ( $show_price ) {
			$data[ 'Price' ] = $download->get_price();
		}
		$data[ 'Availability' ] = $availability;

		return $data;
	}
}
