<?php

namespace DC23\SoftwareDownloads;

final class Product_Opengraph_Integration {

	public function register(): void {
		add_filter( 'wpseo_opengraph_type', [ $this, 'download_type_product' ] );
		add_filter( 'wpseo_frontend_presenters', [ $this, 'remove_unneeded_presenters' ] );
	}

	/**
	 * Return 'product' when current post is an EDD download.
	 *
	 * @param string $type
	 *
	 * @return string
	 */
	public function download_type_product( $type ) {
		if ( is_singular( 'download' ) ) {
			return 'product';
		}

		return $type;
	}
	
	public function remove_unneeded_presenters( $presenters ) {
		return $presenters;
	}
}