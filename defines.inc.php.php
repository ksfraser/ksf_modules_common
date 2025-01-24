<?php

//$path_to_faroot= dirname ( realpath ( __FILE__ ) ) . "/../..";
global $path_to_faroot= __DIR__ . "/../../";
global $path_to_ksfcommon= __DIR__ . "/";

//require_once( $path_to_faroot . '/includes/db/connect_db.inc' ); //db_query, ...
//require_once( $path_to_faroot . '/includes/errors.inc' ); //check_db_error, ...

//table stock_master
define( 'STOCK_ID_LENGTH_ORIG', 20 );
define( 'STOCK_ID_LENGTH', 64 );
define( 'DESCRIPTION_LENGTH', 32 );
define( 'GL_ACCOUNT_NAME_LENGTH', 32 );

//table stock_category
define( 'CAT_DESCRIPTION_LENGTH', 20 );

//table suppliers
define( 'SUPP_NAME_LENGTH', 60 );
define( 'SUPP_WEBSITE_LENGTH', 100 );
define( 'SUPP_REF_LENGTH', 30 );
define( 'SUPP_ACCOUNT_NO_LENGTH', 40 );
?>

