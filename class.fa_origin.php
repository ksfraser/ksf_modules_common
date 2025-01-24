<?php


/***************************************************************//**
 * 
 * Inherits:
   	function __construct( $loglevel = PEAR_LOG_DEBUG )
        /*@NULL@* /function set_var( $var, $value )
        function get_var( $var )
        /*@array@* /function var2data()
        /*@array@* /function fields2data( $fieldlist )
        /*@NULL@* /function LogError( $message, $level = PEAR_LOG_ERR )
	/*@NULL@* /function LogMsg( $message, $level = PEAR_LOG_INFO )
	*
 * Provides:

 * ****************************************************************/

require_once( 'class.origin.php' );
/***************************************************************//**
 * 
 *
 * Inherits:
   	function __construct( $loglevel = PEAR_LOG_DEBUG )
        /*@NULL@* /function set_var( $var, $value )
        function get_var( $var )
        /*@array@* /function var2data()
        /*@array@* /function fields2data( $fieldlist )
        /*@NULL@* /function LogError( $message, $level = PEAR_LOG_ERR )
	/*@NULL@* /function LogMsg( $message, $level = PEAR_LOG_INFO )
 * Provides:
	 / *@bool@* /function validate() //CURRENTLY DOES NOT DO ANYTHING
 *
 * *********************************************************************************/

class fa_origin extends origin
{
	protected $id;
	protected $reference;

	function __construct( $loglevel = PEAR_LOG_DEBUG )
	{
		parent::__construct($loglevel);
	}
	/*@bool@*/function validate()
	{
	}

}

