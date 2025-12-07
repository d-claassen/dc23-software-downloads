<?php

namespace DC23\SoftwareDownloads\Presenters\OpenGraph;

use Yoast\WP\SEO\Presenters\Abstract_Indexable_Tag_Presenter;

final class Product_Brand extends Abstract_Indexable_Tag_Presenter {

	/**
	 * The tag format including placeholders.
	 *
	 * @var string
	 */
	protected $tag_format = self::META_PROPERTY_CONTENT;


}