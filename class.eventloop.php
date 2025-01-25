<?php

$path_to_root="../..";

require_once( 'class.origin.php' );

$configArray = array();

/******************************************************************************************//**
 * This class is the start of a controller, and loads config.*.php files to get sub-modules
 *
 * Routines here have been tested through my own framework, as well as add-ons to 
 * Wordpress, Zencart, Front Accounting.  Doesn't mean this is bug free though!!!
 *
 * Inherits:
        function __construct( $loglevel = PEAR_LOG_DEBUG )
        /*@NULL@* /function set_var( $var, $value )
        function get_var( $var )
        /*@array@* /function var2data()
        /*@array@* /function fields2data( $fieldlist )
        /*@NULL@* /function LogError( $message, $level = PEAR_LOG_ERR )
        /*@NULL@* /function LogMsg( $message, $level = PEAR_LOG_INFO )
	function object_var_names()
        function set( $field, $value = null, $enforce_only_native_vars = true )
        /*@NULL@* /function set_var( $var, $value )
	function get( $field )
 *
 * Provides:
        function __construct( $moduledir )
        function dumpObservers()
        function ObserverRegister( /*Class Instance* /$observer, $event )
         function ObserverDeRegister( $observer )
         function ObserverNotify( $trigger_class, $event, $msg )
         function notified( $object, $event, $message )
 * 
 *
 * ********************************************************************************************/
class eventloop extends origin
{
	var $config_values = array();   //What fields to be put on config screen
	var $tabs = array();
        var $help_context;
	var $tb_pref;

	function __construct( $moduledir )
	{
		parent::__construct();
 		/* 
		 * locate Module class files to open 
		 */
	        foreach (glob("{$moduledir}/config.*.php") as $filename)
	        {
			//echo "opening module config file " . $filename . "<br />\n";
	                include_once( $filename );
	        }
		/*
		 * Loop through the $configArray to set loading modules in right order
		 */
		//var_dump( $configArray );
		foreach( $configArray as $carray )
		{
			//var_dump( $carray );
			$modarray[$carray['loadpriority']][] = $carray;
		}

		//var_dump( $modarray );
		foreach( $modarray as $priarray )
		{
			foreach( $priarray as $marray )
			{
		
				$res = include_once( $moduledir . "/" . $marray['loadFile'] );
				if( TRUE == $res )
				{
					$this->ObserverNotify( $this, 'NOTIFY_LOG_INFO', "Module " . $marray['ModuleName'] . " being added" );
					//echo "Module " . $marray['ModuleName'] . " being added <br />";
					$marray['objectName'] = new $marray['className'];
					if( isset( $marray['objectName']->observers ) )
					{
						foreach( $marray['objectName']->observers as $obs )
						{
							$this->observers[] = $obs;
						}
					}
				}
				else
				{
					echo "Attempt to open " . $moduledir . "/" . $marray['loadFile'] . " FAILED!<br />";
				}
			}
		}
		$this->ObserverNotify( $this, 'NOTIFY_LOG_INFO', "Completed Adding Modules" );
		$this->ObserverNotify( $this, 'NOTIFY_INIT_CONTROLLER_COMPLETE', "Completed Adding Modules" );
	}
	function dumpObservers()
	{
		if( isset( $this->observers ) )
		{
			foreach( $this->observers as $key=>$val )
			{
				echo "Observer Event: " . $key . " with value " . $val;
			}
/*
			foreach( $this->observers as $obs )
			{
				var_dump( $obs );
			}
*/
		}
	
	}
	function ObserverRegister( /*Class Instance*/$observer, $event )
        {
               	$this->observers[$event][] = $observer;
               	return SUCCESS;
        	}
         function ObserverDeRegister( $observer )
         {
               	$this->observers[] = array_diff( $this->observers, array( $observer) );
               	return SUCCESS;
         }
         function ObserverNotify( $trigger_class, $event, $msg )
         {
	//	return;
               	if ( isset( $this->observers[$event] ) )
                      foreach ( $this->observers[$event] as $obs )
                      {
                              $obs->notified( $trigger_class, $event, $msg );
                      }
               	/* '**' being used as 'ALL' */
               	if ( isset( $this->observers['**'] ) )
                      	foreach ( $this->observers['**'] as $obs )
                      	{
                              	$obs->notified( $trigger_class, $event, $msg );
                      	}
               	return SUCCESS;
         }
         function notified( $object, $event, $message )
         {
               	//Called when another object we are observing sends us a notification
		//Needs to be extended by the inheriting class
               	return SUCCESS;
         }
}
?>
