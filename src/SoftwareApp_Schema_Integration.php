<?php

namespace DC23\SoftwareDownloads;

final class SoftwareApp_Schema_Integration {
    public function register(): void {
        \add_filter( 'edd_generate_download_structured_data', [ $this, 'software_application_schema' ], 10, 2 );
    }
    
    public function software_application_schema( $data, $download ) {
      $software_type = \get_post_meta( $download->ID, '_SoftwareType', true );
      $data_type = (array) $data[ '@type' ];
      if ( ! empty( $software_type ) && ! in_array( $software_type, $data_type, true ) ) {
         $data['@type'] = $data_type;
         $data['@type'][] = $software_type;
      }
      
        return $data;
    }
}
    