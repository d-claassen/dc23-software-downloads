<?php

namespace DC23\SoftwareDownloads;

use function YoastSEO;
use function YoastSEOPremium;
use Yoast\WP\SEO\Premium\Integrations\Third_Party\EDD;

final class Schema_Integration {

	public function register(): void {
		\add_filter( 'edd_generate_download_structured_data', [ $this, 'filter_download_schema' ] );

		return;
		try {
			$edd = YoastSEOPremium()->classes->get( EDD::class ) ?? YoastSEO()->classes->get( EDD::class );
			\remove_filter(
				'wpseo_schema_organization',
				[ $edd, 'filter_organization_schema' ]
			);
		} catch(
			\YoastSEO_Vendor\Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException $serviceNotFound
		) {
			// no yoast edd integration
		}
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
