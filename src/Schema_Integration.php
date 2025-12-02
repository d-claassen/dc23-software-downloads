<?php

namespace DC23\SoftwareDownloads;

final class Schema_Integration {

	public function register(): void {
		\add_filter( 'edd_generate_download_structured_data', [ $this, 'filter_download_schema' ] );
	}
    
    public function filter_download_schema($schema){
        return $schema;
    }
}
