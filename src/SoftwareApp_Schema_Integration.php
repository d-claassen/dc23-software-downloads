<?php

namespace DC23\SoftwareDownloads;

final class SoftwareApp_Schema_Integration {
    public function register(): void {
        \add_filter( 'wpseo_schema_graph_pieces', [ $this, 'add_software_app' ], 10, 2 );
   }
    /**
     * Adds a software app graph piece to the schema collector.
     *
     * @param list<Abstract_Schema_Piece> $pieces  The current graph pieces.
     * @param Meta_Tags_Context           $context The current context.
     *
     * @return list<Abstract_Schema_Piece> The graph pieces.
     */
    public function add_product_return_policy( $pieces, $context ) {
       $pieces[] = new Generators\Schema\SoftwareApp( $context );
    
       return $pieces;
    }
    
}
    