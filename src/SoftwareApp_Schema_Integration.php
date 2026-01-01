<?php

namespace DC23\SoftwareDownloads;

final class SoftwareApp_Schema_Integration {
    public function register(): void {
        \add_filter( 'edd_generate_download_structured_data', [ $this, 'software_application_schema' ], 10, 2 );
    }
    
    public function software_application_schema( $data, $download ) {
      $software_type = \get_post_meta( $download->ID, '_SoftwareType', true );
      $data_type = $data['@type'  ];
      if ( ! in_array( $software_type , (array) $data_type, true ) ( {
         $data['@type'] = (array) $data['@type' ];
         $data['@type#'][] = $software_type;
      }
      
        return $data;
    }
}
    