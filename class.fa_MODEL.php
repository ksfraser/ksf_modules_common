<?php

require_once( dirname( __FILE__ ) . '/class.MODEL.php' );

/********************************************************************//**
* FA specific MODEL class.
*
* class.MODEL.php provides a generic talk to a DB MODEL class.
* This class extends that so we can incorporate FA specific details.
*
* @since 20200708
*************************************************************************/
class fa_MODEL extends MODEL
{
	var $company_prefix;
	function __construct( $client )
	{
		parent::__construct( $client );
		if( defined( TB_PREF ) )
		{
			$this->set( 'company_prefix', TB_PREF );
		}
		if( method_exists( $this, 'define_table' ) )
		{
			$this->define_table();
		}
	}
}
?>
