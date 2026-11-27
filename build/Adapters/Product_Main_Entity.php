<?php
/**
 * Product main entity.
 *
 * @package DC23\SoftwareDownloads
 */

declare( strict_types=1 );

namespace DC23\SoftwareDownloads\Adapters;

use DC23\ExcessiveSchema\Adapters\Main_Entity;
use Yoast\WP\SEO\Models\Indexable;

/**
 * Main entity for products distributed via EDD downloads.
 *
 * Bridges EDD's `edd_generate_download_structured_data` filter into
 * `dc23_schema_main_entity`. Unlike the Article and TEC adapters, this one
 * hooks a non-Yoast source filter — EDD owns the filter directly. The
 * signature has the post ID instead of a context object, so we resolve the
 * indexable ourselves before firing the uniform filter.
 *
 * Type identity is `Product` (universal across software, services, etc.).
 * Yoast SEO Premium adds a parallel SoftwareApplication piece with the same
 * @id; mentions reference Product as the canonical handle.
 */
final class Product_Main_Entity implements Main_Entity {

	public function get_root_type(): string {
		return 'Product';
	}

	public function get_entity_type( Indexable $indexable ): ?string {
		return $this->get_root_type();
	}

	/**
	 * Mirrors the @id format Yoast SEO Premium emits for EDD downloads:
	 *
	 *   $canonical . '#/schema/edd-product/' . $post_id
	 *
	 * Falls back to permalink when canonical is empty, matching Yoast's
	 * typical behaviour. Absolutises relative URLs so the @id is always
	 * a full URL.
	 */
	public function get_entity_id( Indexable $indexable ): string {
		$base = $indexable->canonical;
		if ( ! is_string( $base ) || $base === '' ) {
			$base = $indexable->permalink;
		}
		if ( \YoastSEO()->helpers->url->is_relative( $base ) ) {
			$base = home_url( $base );
		}
		return $base . '#/schema/edd-product/' . $indexable->object_id;
	}

	public function get_allowed_subtypes(): ?array {
		return null;
	}

	public function setup_main_entity_enrichment(): void {
		add_filter( 'edd_generate_download_structured_data', [ $this, 'enrich' ], 20, 2 );
	}

	/**
	 * Bridge EDD's filter into dc23_schema_main_entity.
	 *
	 * Hooks at priority 20, leaving room for Yoast SEO Premium (priority 10)
	 * to run its @id rewrite first. Resolves the post ID to an indexable
	 * because EDD's filter signature passes only the ID.
	 *
	 * @param array         $data     EDD's structured data.
	 * @param \EDD_Download $download The download post.
	 *
	 * @return array
	 */
	public function enrich( $data, $download ) {
		if ( ! is_array( $data ) ) {
			return $data;
		}

		$download_id = (int) $download->ID;
		$indexable = \YoastSEO()->meta->for_post( $download_id )->context->indexable;
		if ( $indexable === null ) {
			return $data;
		}

		return apply_filters( 'dc23_schema_main_entity', $data, $indexable );
	}
}