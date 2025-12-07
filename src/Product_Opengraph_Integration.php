<?php

namespace DC23\SoftwareDownloads;

use Yoast\WP\SEO\Presenters\Abstract_Presenter;

final class Product_Opengraph_Integration {

	public function register(): void {
		add_filter( 'wpseo_opengraph_type', [ $this, 'download_type_product' ] );
		add_filter( 'wpseo_frontend_presenters', [ $this, 'opengraph_product_presenters' ] );
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
	 * Update OpenGraph presenters to describe downloads as a product.
	 *
	 * @param array<array-key, Abstract_Presenter> $presenters
	 * @param Meta_Tags_Context                    $context
	 * 
	 * @return array<array-key, Abstract_Presenter>
	 */
	public function opengraph_product_presenters( $presenters, $context ) {
		if ( ! is_array( $presenters ) ) {
			return $presenters;
		}

		if ( ! is_singular( 'download' ) ) {
			return $presenters;
		}
		
		// Return false if a download object could not be retrieved.
		$download = edd_get_download( $context->indexable->object_id );
		if ( ! $download instanceof \EDD_Download ) {
			return false;
		}
			
		// Remove OpenGraph article metatag presenters.
		foreach ( $presenters as $key => $presenter ) {
			if (
				$presenter instanceof \Yoast\WP\SEO\Presenters\Open_Graph\Article_Publisher_Presenter
				|| $presenter instanceof \Yoast\WP\SEO\Presenters\Open_Graph\Article_Author_Presenter
			) {
				unset( $presenters[ $key ] );
			}
		}
	
		// Replicating EDDs "Structured_Data" approach.
		$presenters[] = new Presenters\OpenGraph\Product_Brand( get_bloginfo( 'name' ) );
		$presenters[] = new Presenters\OpenGraph\Product_Retailer_Item_ID( $download->get_sku() );

		return $presenters;
	}
	
	/**
	 * Adds the WooCommerce OpenGraph presenter.
	 *
	 * @param Abstract_Indexable_Presenter[] $presenters The presenter instances.
	 * @param Meta_Tags_Context              $context    The meta tags context.
	 *
	 * @return Abstract_Indexable_Presenter[] The extended presenters.
	 */
	public function add_frontend_presenter( $presenters, $context ) {
		if ( ! is_array( $presenters ) ) {
			return $presenters;
		}

		$product = $this->get_product( $context );
		if ( ! $product instanceof WC_Product ) {
			return $presenters;
		}

		$presenters[] = new WPSEO_WooCommerce_Product_Brand_Presenter( $product );

		if ( $this->should_show_price() ) {
			$presenters[] = new WPSEO_WooCommerce_Product_Price_Amount_Presenter( $product );
			$presenters[] = new WPSEO_WooCommerce_Product_Price_Currency_Presenter( $product );
		}

		$is_on_backorder = $product->is_on_backorder();
		$is_in_stock     = ( $is_on_backorder === true ) ? false : $product->is_in_stock();
		$presenters[]    = new WPSEO_WooCommerce_Pinterest_Product_Availability_Presenter( $product, $is_on_backorder, $is_in_stock );
		$presenters[]    = new WPSEO_WooCommerce_Product_Availability_Presenter( $product, $is_on_backorder, $is_in_stock );

		$presenters[] = new WPSEO_WooCommerce_Product_Retailer_Item_ID_Presenter( $product );
		$presenters[] = new WPSEO_WooCommerce_Product_Condition_Presenter( $product );

		return $presenters;
	}
} 