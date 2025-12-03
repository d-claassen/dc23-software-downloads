<?php

namespace DC23\SoftwareDownloads;

final class Schema_Integration {

	public function register(): void {
		\add_filter( 'edd_generate_download_structured_data', [ $this, 'filter_download_schema' ] );
		\add_filter( 'wpseo_schema_organization', [ $this, 'filter_organization_schema' ], 10, 2 );
	}

	/**
	 * Support person.
		*
		* @param array<string, mixed> $organization_piece
		* @param WPSEO_Schema_Context $context
		*
		* @return array<string, mixed>
		*/
	public function filter_organization_schema( $organization_piece, $context ) {
		if ( $context->site_represents === 'person' ) {
			$organization_piece['@type'][] = 'Person';
		}
		
		return $organization_piece;
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
		if ( $context->site_represents !== 'person' ) {
			return $schema;
		}

		$person_reference = [
			'@id'  => YoastSEO()->helpers->schema->id->get_user_schema_id( $context->site_user_id, $context ),
		];
		
		$schema['brand']['@id'] = $person_reference['@id'];

		return $schema;
	}
}
