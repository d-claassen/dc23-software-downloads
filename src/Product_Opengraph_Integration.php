<?php

namespace DC23\SoftwareDownloads;

use Yoast\WP\SEO\Presenters\Abstract_Presenter;

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

	/*
	 * If this is a product page, remove OpenGraph article metatag presenters.
	 *
	 * @param array<array-key, Abstract_Presenter> $presenters
	 *
	 * @return array<array-key, Abstract_Presenter>
	 */
	public function remove_unneeded_presenters( $presenters ) {
		if ( is_singular( 'download' ) ) {
			foreach ( $presenters as $key => $presenter ) {
				if (
					$presenter instanceof \Yoast\WP\SEO\Presenters\Open_Graph\Article_Publisher_Presenter
					|| $presenter instanceof \Yoast\WP\SEO\Presenters\Open_Graph\Article_Author_Presenter
				) {
					unset( $presenters[ $key ] );
				}
			}
		}

		return $presenters;
	}
}