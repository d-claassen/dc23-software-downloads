<?php

namespace DC23\SoftwareDownloads\Presenters\OpenGraph;

use Yoast\WP\SEO\Presenters\Abstract_Indexable_Tag_Presenter;

final class Product_Retailer_Item_ID extends Abstract_Indexable_Tag_Presenter {

	/**
	 * The tag format including placeholders.
	 *
	 * @var string
	 */
	protected $tag_format = self::META_PROPERTY_CONTENT;


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