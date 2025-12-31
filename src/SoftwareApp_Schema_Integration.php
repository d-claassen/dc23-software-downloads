<?php

namespace DC23\SoftwareDownloads;

final class SoftwareApp_Schema_Integration {
    public function register(): void {
        \add_filter( 'edd_generate_download_structured_data', [ $this, 'software_application_schema' ], 10, 2 );
    }
    
    public function software_application_schema( $data, $download ) {
        return $data;
    }
}
    