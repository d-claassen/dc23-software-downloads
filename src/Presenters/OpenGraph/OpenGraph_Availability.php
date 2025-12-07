<?php

namespace DC23\SoftwareDownloads\Presenters\OpenGraph;

use Yoast\WP\SEO\Presenters\Abstract_Indexable_Tag_Presenter;

final class OpenGraph_Availability extends Abstract_Indexable_Tag_Presenter {

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
	protected $key = 'og:availability';

	public function __construct( 
		private readonly bool $is_on_backorder,
		private readonly bool $is_in_stock,
	) {}

	/**
	 * Gets the raw value of a presentation.
	 *
	 * @return string
	 */
	public function get() {
		if ( $this->is_on_backorder ) {
			return 'backorder';
		}

		if ( $this->is_in_stock ) {
			return 'instock';
		}

		return 'out of stock';
	}
}