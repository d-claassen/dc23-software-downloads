<?php

namespace DC23\SoftwareDownloads;

final class Schema_Integration {

	public function register(): void {
		\add_filter( 'edd_generate_download_structured_data', [ $this, 'filter_download_schema' ] );
	}

	/**
	 * Filter the EDD product schema
	 *
	 * @param $schema array The product schema piece.
	 *
	 * @return array The product schema piece.
	 */
	public function filter_download_schema( $schema ) {
		if ( ! \is_array( $schema ) ) {
			return $schema;
		}

		return $schema;
	}
}
