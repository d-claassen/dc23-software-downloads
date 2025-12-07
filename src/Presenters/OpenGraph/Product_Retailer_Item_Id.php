<?php

namespace DC23\SoftwareDownloads\Presenters\OpenGraph
/**
 * Represents the product's retailer item ID.
 */
class Product_Retailer_Item_ID extends WPSEO_WooCommerce_Abstract_Product_Presenter {

	/**
	 * The tag key name.
	 *
	 * @var string
	 */
	protected $key = 'product:retailer_item_id';

    public function __construct(
        private readonly string $sku,
    ) {}

	/**
	 * Gets the raw value of a presentation.
	 *
	 * @return string The raw value.
	 */
	public function get() {
		return (string) $this->sku;
	}
}