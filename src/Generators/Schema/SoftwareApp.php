<?php

namespace DC23\SoftwareDownloads\Generators\Schema;

use \Yoast\WP\SEO\Generators\Schema\Abstract_Schema_Piece;

/**
 * Returns schema software application data.
 */
class SoftwareApp extends Abstract_Schema_Piece {

	/**
	 * Determines whether a piece should be added to the graph.
	 *
	 * @return bool
	 */
	public function is_needed() {
        if ( ! is_singular( 'download' ) ) {
            return false;
        }

        $download = \edd_get_download( $this->context->indexable->object_id );
        $software_type = \get_post_meta( $download->ID, '_SoftwareType', true );
        if ( ! empty( $software_type ) ) {
            return true;
        }

        // No custom settings.
        return false;
	}

	/**
	 * Render a software app of the download.
	 *
	 * @return array<list<string, mixed>>
	 */
	public function generate() {
		$graph = [];

		$graph[] = $this->generate_software_app();

		return $graph;
	}

	/**
	 * Generate a SoftwareApplication piece.
	 *
	 *
	 * @return array<sting, mixed>
	 */
        protected function generate_software_app(): array {
                $download_id = $this->context->indexable->object_id;
                $id          = $this->context->canonical . '#/schema/edd-product/' . \esc_attr( $download_id );

                $software_type = \get_post_meta( $download_id, '_SoftwareType', true );
                $data = [
                        '@type' => $software_type,
                        '@id'   => $id,
                ];
        
                return $data;
        }
}
