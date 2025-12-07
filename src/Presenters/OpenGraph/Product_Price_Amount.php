<?php

namespace DC23\SoftwareDownloads\Presenters\OpenGraph;

use Yoast\WP\SEO\Presenters\Abstract_Indexable_Tag_Presenter;

final class Product_Price_Amount extends Abstract_Indexable_Tag_Presenter {

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
	protected $key = 'product:price:amount';

    public function __construct(
        private readonly string $price,
    ) {}

	/**
	 * Gets the raw value of a presentation.
	 *
	 * @return string The raw value.
	 */
	public function get() {
		return (string) $this->price;
	}
}
