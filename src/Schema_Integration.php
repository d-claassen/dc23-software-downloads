<?php

namespace DC23\SoftwareDownloads;

final class Schema_Integration {

	public function register(): void {
		\add_filter( 'edd_generate_download_structured_data', [ $this, 'filter_download_schema' ] );
        
        \remove_filter(
            'wpseo_schema_organization',
            [ \YoastSEO()->classes->get( \Yoast\WP\SEO\Premium\Integrations\Third_Party\EDD::class ), 'filter_organization_schema' ]
        );
	}

	/**
	 * Filter the EDD product schema
	 *
	 * @param array<string, string|array> $schema The product schema piece.
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
