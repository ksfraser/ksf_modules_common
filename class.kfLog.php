<?php

//!< Dependant upon PEAR LOG

$path_to_root="../..";

require_once( 'class.origin.php' );
@include_once ( 'Log/file.php' );

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
	function Log( $msg, $level )
	{
		$logobject->log( $message, $level );
		return;	
	}
}
?>
