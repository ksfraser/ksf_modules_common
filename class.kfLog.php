<?php

//!< Dependant upon PEAR LOG

$path_to_root="../..";

require_once( 'class.origin.php' );
@include_once ( 'Log/file.php' );

/**//************************************************************************************************
* A class to provide FILE logging
*
******************************************************************************************************/
class kfLog extends origin
{
	var $logobject;

	function __construct( $filename = __FILENAME__, $level = PEAR_LOG_DEBUG )
	{
		parent::__construct();
		$conf = array();
		$this->logobject = new Log_file( $filename . "_debug_log.txt", "", $conf, $level );
		return;	
	}
	/**//**********************************
	* Write a log message   
	*
	* @since 20240905
	*
	* @param string message
	* @param int Log Level
	* @returns none
	***************************************/
	function Log( $msg, $level )
	{
		$this->logobject->log( $message, $level );
		return;	
	}
	/**//**********************************
	* Timestamp a msg string
	*
	* @since 20240905
	*
	* @param string message
	* @param int Log Level
	* @returns none
	***************************************/
	function stampLog( $msg, $level )
	{
		return $this->Log( date( "Y-M-D H:i:s" ) . $msg, $level );
	}
}

/** From import_paypal
*	function log_message($msg) {
*	    global $path_to_root;
*	    $fp = fopen($path_to_root."/tmp/paypal.log", "a+");
*	    fwrite($fp, "[".date("d-M-Y H:i:s")."] ".$msg."\r\n");
*	    fclose($fp);
*	}
**/
?>
