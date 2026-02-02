<?php

//!< WARNING this class has some FrontAccounting specific code

require_once( 'defines.inc.php' );
//include_once( 'Log.php' );	//PEAR Logging - included in defines.inc

/*
	# 0 PEAR_LOG_EMERG emerg() System is unusable
	# 1 PEAR_LOG_ALERT alert() Immediate action required
	# 2 PEAR_LOG_CRIT crit() Critical conditions
	# 3 PEAR_LOG_ERR err() Error conditions
	# 4 PEAR_LOG_WARNING warning() Warning conditions
	# 5 PEAR_LOG_NOTICE notice() Normal but significant
	# 6 PEAR_LOG_INFO info() Informational
	# 7 PEAR_LOG_DEBUG debug() Debug-level messages 
*/
/***************************************************************//**
 * Base class for ksf common...  throws EXCEPTIONS for try/catch loops
 *
 * Provides:
*   	function __construct( $loglevel = PEAR_LOG_DEBUG )
*	function set_var( $var, $value )
*	function get_var( $var )
*	function var2data()
*	function fields2data( $fieldlist )
*	function LogError( $message, $level = PEAR_LOG_ERR )
*	function LogMsg( $message, $level = PEAR_LOG_INFO )
*	function Log( $message, $level = PEAR_LOG_EMERG )
*	function var_dump( $var, $level = PEAR_LOG_DEBUG )
*	public function __call($method, $arguments)
*	function __get( $prop ) {
*	function __isset( $prop ) {
*	function is_supported_php() {
*	function object_var_names()
*	function user_access( $action )
*	function set( $field, $value = null, $enforce_only_native_vars = true )
*	function set_var( $var, $value )
*	function get( $field )
*	function get_var( $var )
*
*	public static function getInstance()
*	public function getIterator()
*	function unset_var( $field )
*	function objectvars2array()
*	function match_tokens( $arr1, $arr2 )
*	function obj2obj( $obj )
*	function arr2obj( $arr )
*	function score_matches( $field, $value )
*	function isdiff( $key, $value )
 *
 * *********************************************************************************/
class origin
{
	/*refactor*/protected $config_values = array();   //!< What fields to be put on config screen.  Probably doesn't belong in this class :(
	/*refactor*/protected $tabs = array();
	/*refactor*/var $help_context;		//!< help context for screens in FA
	/*refactor*/var $tb_pref;			//!< FrontAccounting Table Prefix (i.e. 0_) 
	var $loglevel;			//!< PEAR_LOG level that must be specified to be added to log/errors
	var $errors;			//!< array of error messages
	var $log;			//!< array of log messages
	var $fields;			//!< array of fields in the class
	var $data;			//!< array of data from the fields
	private $testvar;
	var $object_fields;		//!< array of the variables in this object, under '___SOURCE_KEYS_'
	protected $matchscores; //!<array indicating how many points for matching the field
	protected $application;	 //!< string which application is the child object holding data for
	protected $module;	      //!< string which module is the child object holding data for
	protected $container_arr;       //__get/__isset uses this
	protected $obj_var_name_arr;    //Array of field names in this object that need to be translated in the NVL array
	protected $dest_var_name_arr;   //Array of field names in the DEST Object for translating.
	protected $name_value_list;
	private static $_instance;	//For IteratorAggregate

	/************************************************************************//**
	 *constructor
	 *
	 *@param $loglevel int PEAR log levels
	 *@param $param_arr to allow extending classes to accept values in their constructor
	 * ***************************************************************************/
	function __construct( $loglevel = PEAR_LOG_DEBUG, $param_arr = null )
	{
		global $db_connections;
		if( isset( $_SESSION['wa_current_user'] ) )
		{
		        $cu = $_SESSION['wa_current_user'];                     //FrontAccounting specific
		        $compnum = $cu->company;                                //FrontAccounting specific
		}
		else
		{
		        $compnum = 0;
		        //$this->set( 'company_prefix', $compnum );     //db_base trying to set in test cases.
		}
		if( isset( $db_connections[$compnum]['tbpref'] ) )
		        $this->tb_pref = $db_connections[$compnum]['tbpref'];   //FrontAccounting specific
		else
		        $this->set( 'tb_pref', $compnum . "_", false ); //FrontAccounting specific
		$this->loglevel = $loglevel;
		$this->error = array();
		$this->log = array();
		//Set, with end of constructor values noted
		$this->object_var_names();
		$this->obj_var_name_arr = array();
		$this->dest_var_name_arr = array();
		$this->name_value_list = array();
		if( is_array( $param_arr ) )
		{
		        foreach( $param_arr as $key=>$val)
		        {
		                //Set those values.  But only do native ones
		                $this->set( $key, $val, true );
		        }
		}
	}
	/**//*******************************************
	*
	*
	*	https://stackoverflow.com/questions/13421661/getting-indirect-modification-of-overloaded-property-has-no-effect-notice
	*	Need to use 
	*		final class XXXX implements IteratorAggregate
	*
	* @param none
	* @returns object
	*********************************************** /
*	public static function getInstance()
*	{
*		if (self::$_instance == null) self::$_instance = new self();
*		return self::$_instance;
*	}
	/**//*******************************************
	*
	*
	*	https://stackoverflow.com/questions/13421661/getting-indirect-modification-of-overloaded-property-has-no-effect-notice
	*	Need to use 
	*		final class XXXX implements IteratorAggregate
	*
	* @param none
	* @returns object
	*********************************************** /
*	public function getIterator()
*	{
*		// The ArrayIterator() class is provided by PHP
*		return new ArrayIterator($this);
*	}
	/*********************************************************//**
	 * Magic call method example from http://php.net/manual/en/language.types.object.php
	 *
	 * @param string function name
	 * @param array array of arguments to pass to function
	 * ************************************************************/
/*
	public function __call($method, $arguments) 
	{
		$arguments = array_merge(array("stdObject" => $this), $arguments); // Note: method argument 0 will always referred to the main class ($this).
		if (isset($this->{$method}) && is_callable($this->{$method})) {
		    return call_user_func_array($this->{$method}, $arguments);
		} else {
		    throw new Exception("Fatal error: Call to undefined method stdObject::{$method}()");
		}
	    }
 */
	/**
	 * Magic getter to bypass referencing plugin.
	 *
	 * @param $prop
	 *
	 * @return mixed
	 */
	function __get( $prop ) {
		if( ! is_array( $this->container_arr ) )
		        return NULL;
		if ( array_key_exists( $prop, $this->container_arr ) ) {
		    return $this->container_arr[ $prop ];
		}

		return $this->{$prop};
	}

	/**
	 * Magic isset to bypass referencing plugin.
	 *
	 * @param $prop
	 *
	 * @return mixed
	 */
	function __isset( $prop ) {
		return isset( $this->{$prop} ) || isset( $this->container_arr[ $prop ] );
	}

	/**
	 * Check if the PHP version is supported
	 *
	 * @return bool
	 */
	function is_supported_php() {
		if ( version_compare( PHP_VERSION, $this->min_php, '<' ) ) {
		    return false;
		}

		return true;
	}
	function object_var_names_old()
	{
		$clone = (array) $this;	    		
			//	display_notification( __FILE__ . "::" . __LINE__ . print_r( $clone, true ) );
		$rtn = array ();
		//private prefixed by class name, protected by *
    		$rtn['___SOURCE_KEYS_'] = $clone;
			//	display_notification( __FILE__ . "::" . __LINE__ . print_r( $rtn, true ) );
    		while ( list ($key, $value) = each ($clone) ) {
				//	display_notification( __FILE__ . "::" . __LINE__ . print_r( $key, true ) );
				//	display_notification( __FILE__ . "::" . __LINE__ . print_r( $value, true ) );
			$aux = explode ("\0", $key);
				//	display_notification( __FILE__ . "::" . __LINE__ . print_r( $aux, true ) );
			$newkey = $aux[count($aux)-1];
				//	display_notification( __FILE__ . "::" . __LINE__ . print_r( $newkey, true ) );
				//	display_notification( __FILE__ . "::" . __LINE__ . print_r( $rtn['___SOURCE_KEYS_'][$key], true ) );
			$rtn[$newkey] = $rtn['___SOURCE_KEYS_'][$key];
				//	display_notification( __FILE__ . "::" . __LINE__ . print_r( $rtn, true ) );
    		}
		$this->object_fields = $rtn;
	}
	/**//***************************************************************
	* Get the names of the fields within the class
	*
	* 	The _old version also included the entire object, values 
	* 	at the time of doing this.  I've left the code here above
	*	in case I actually used it anywhere.  But I don't recall
	*	ever using the initial values at the time of the clone.
	*
	* @param none
	* @returns array
	******************************************************************/
	function object_var_names()
	{
		$clone = (array) $this;	    		
			//	display_notification( __FILE__ . "::" . __LINE__ . print_r( $clone, true ) );
		$rtn = array ();
		$src = array ();
		//private prefixed by class name, protected by *
    		foreach ($clone as $key => $value) {
				//	display_notification( __FILE__ . "::" . __LINE__ . print_r( $key, true ) );
				//	display_notification( __FILE__ . "::" . __LINE__ . print_r( $value, true ) );
			$aux = explode ("\0", $key);
				//	display_notification( __FILE__ . "::" . __LINE__ . print_r( $aux, true ) );
			$newkey = $aux[count($aux)-1];
				//	display_notification( __FILE__ . "::" . __LINE__ . print_r( $newkey, true ) );
				//	display_notification( __FILE__ . "::" . __LINE__ . print_r( $clone[$key], true ) );
			if( isset( $clone[$key] ) )
			{
				$rtn[$newkey] = $clone[$key];
			}
			else
			{
				$rtn[$newkey] = null;
			}
				//	display_notification( __FILE__ . "::" . __LINE__ . print_r( $rtn, true ) );
    		}
		$this->object_fields = $rtn;
			//	display_notification( __FILE__ . "::" . __LINE__ . print_r( $rtn, true ) );
			//	display_notification( __FILE__ . "::" . __LINE__ . print_r( $this, true ) );
		return $rtn;
	}
	//STUB until I can code module and data access...
	function user_access( $action )
	{
		switch( $action )
		{
		        case KSF_DATA_ACCESS_READ:
		        case KSF_DATA_ACCESS_WRITE:
		        case KSF_DATA_ACCESS_READWRITE:
		        case KSF_MODULE_ACCESS_READ:
		        case KSF_MODULE_ACCESS_WRITE:
		        case KSF_MODULE_ACCESS_READWRITE:
		                break;
		        case KSF_DATA_ACCESS_DENIED:
		        case KSF_MODULE_ACCESS_DENIED:
		        default:
		                throw new Exception( "User doesn't have access to the field", KSF_DATA_ACCESS_DENIED );
		}
		return TRUE;
	}
	/*********************************************//**
	 * Set a variable.  Throws exceptions on sanity checks
	 *
	 * The throwing of exceptions is probably going to break a bunch of code!
	 * @param field string Variable to be set
	 * @param value ... value for variable to be set
	 * @param native... bool enforce only the variables of the class itself.  default TRUE, which will break code.
	 *
	 * **********************************************/
	function set( $field, $value = null, $enforce_only_native_vars = true )
	{
		//	display_notification( __FILE__ . "::" . __LINE__ );
		if( !isset( $field )  )
		{
			throw new Exception( "Fields not set", KSF_FIELD_NOT_SET );
		}
		try 
		{
		        $this->user_access( KSF_DATA_ACCESS_WRITE );
		}
		catch (Exception $e )
		{
		        throw new Exception( $e->getMessage, $e->getCode );
		}

		if( $enforce_only_native_vars )
		{
			if( null === $this->object_fields )
			{
				//How did we get here?
				$this->object_var_names();
			}
			if( ! isset( $this->object_fields ) )
			{
				debug_print_backtrace();
			}
/*
		        else if( ! in_array( $field, $this->object_fields ) AND ! array_key_exists( $field, $this->object_fields ) )
		                throw new Exception( "Variable to set ::" . $field . ":: is not a member of the class \n" . print_r( $this->object_fields, true ), KSF_FIELD_NOT_CLASS_VAR );
*/

			//if( ! in_array( $field, $this->object_fields ) )
			if( ! array_key_exists( $field, $this->object_fields ) )
			{
				throw new Exception( "Variable to set is not a member of the class: " . $field, KSF_FIELD_NOT_CLASS_VAR );
			}
		}
		if( isset( $value ) )
		{
		        if( is_array( $this->$field ) )
		        {
		                //echo "**********Setting an array \r\n";
		                $this->$field[] = $value;
		        }
		        else
		        {
		                //echo "**********Setting field $field \r\n";
		                $this->$field = $value;
		        }
		}
		else
		{
		        throw new Exception( "Value to be set not passed in", KSF_VALUE_NOT_SET );
		}
	}
	/*********************************************//**
	 * Set an array variable.  Throws exceptions on sanity checks
	 *
	 * The throwing of exceptions is probably going to break a bunch of code!
	 *
	 * @param field string Variable to be set
	 * @param value ... value for variable to be set
	 * @param int index array index to set
	 * @param native... bool enforce only the variables of the class itself.  default TRUE, which will break code.
	 * @param bool autoinc_index automatically increment the index if the array value is already set
	 * @param bool replace replace the value in the array if the index is already set.  Only one of autoinc and replace should be TRUE
	 *
	 * **********************************************/
	function set_array( $field, $value = null, $index = 0, $enforce_only_native_vars = true, $autoinc_index = false, $replace = false )
	{
		if( !isset( $field )  )
		        throw new Exception( "Fields not set", KSF_FIELD_NOT_SET );
		try{
		        $this->user_access( KSF_DATA_ACCESS_WRITE );
		}
		catch (Exception $e )
		{
		        throw new Exception( $e->getMessage, $e->getCode );
		}
		if( $enforce_only_native_vars )
		{
		        if( ! isset( $this->object_fields ) )
		        {
		                //debug_print_backtrace();
		                throw new Exception( "object_fields not set so can't check to enforce only_native_vars", KSF_FIELD_NOT_SET );
		        }
		        else if( ! in_array( $field, $this->object_fields ) AND ! array_key_exists( $field, $this->object_fields ) )
		                throw new Exception( "Variable to set ::" . $field . ":: is not a member of the class \n" . print_r( $this->object_fields, true ), KSF_FIELD_NOT_CLASS_VAR );
		}
		if( isset( $value ) )
		{
		        if( ! is_array( $this->field ) )
		        {
		                //Wrong func called.  We can either throw an exception, or call ->set instead.
		                $this->set( $field, $value, $enforce_only_native_vars );
		        }
		        else
		        {
		                if( isset( $this->$field[$index] ) )
		                {
		                        if( $autoinc_index )
		                        {
		                                $index++;
		                                $this->set_array( $field, $value, $index, $enforce_only_native_vars, $autoinc_index );
		                        }
		                        else if( $replace )
		                        {
		                                $this->$field[$index] = $value;
		                        }
		                        else
		                        {
		                                throw new Exception( "Field ::" . $field . ":: is already set but we weren't told to replace!", KSF_VALUE_SET_NO_REPLACE );
		                        }
		                }
		                else
		                {
		                        $this->$field[$index] = $value;
		                }
		        }
		}
		else
		        throw new Exception( "Value to be set not passed in", KSF_VALUE_NOT_SET );
	}
	 /**//*******************************************
	 * Nullify a field
	 *
	 * @param field string variable to nullify
	 */
	function unset_var( $field )
	{
		$this->$field = null;
		unset( $this->$field );
	}
	/***************************************************//**
	 * Most of our existing code does not use TRY/CATCH so we will trap here
	 *
	 * Eat any exceptions thrown by ->set
	 *
	 * *****************************************************/
	/*@NULL@*/function set_var( $var, $value )
	{
		try {
			$this->set( $var, $value );
		} catch( Exception $e )
		{
		}
/*
		if(!empty($value) && is_string($value)) {
			$this->$var = str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $value);
    		}
		else
		{
			$this->$var = $value ;
		}
 */
		return;
	}
	/***************************************************//**
	* Return the field
	*
	* @param string field name
	* @returns varies
	************************************************************/
	function get( $field )
	{
		if( isset( $this->$field ) )
			return $this->$field;
		else
			throw new Exception( __METHOD__ . "  Field not set.  Can't GET " . $field, KSF_FIELD_NOT_SET );
	}
	function get_var( $var )
	{
		return $this->get( $var );
	}
	/***************************************************//**
	* Take our variables and set into an array
	*
	* @since 20240312
	*
	* @param none
	* @returns array 
	************************************************************/
	/*@array@*/function var2data()
	{
		foreach( $this->fields as $f )
		{
			$this->data[$f] = $this->get_var( $f );
		}
		return $this->data;
	}
	/*@array@*/function fields2data( $fieldlist )
	{
		foreach( $fieldlist as $field )
		{
		        $this->data[$field] = $this->get_var( $field );
		}
		return $this->data;
	}
	
	/*@NULL@*/function LogError( $message, $level = PEAR_LOG_ERR )
	{
		if( $level <= $this->loglevel )
			$this->errors[] = $message;
		return;
	}
	/*@NULL@*/function Log( $message, $level = PEAR_LOG_EMERG )
	{
		//These probably should have been put in the reverse order, but for now...
		switch( $level )
		{
		        case PEAR_LOG_EMERG:
		        case PEAR_LOG_ALERT:
		        case PEAR_LOG_CRIT:
		        case PEAR_LOG_ERR:
		                $this->LogError( $message, $level );
		        case PEAR_LOG_WARNING:
		        case PEAR_LOG_NOTICE:
		        case PEAR_LOG_INFO:
		        case PEAR_LOG_DEBUG:
		                $this->var_dump( $message, 0 );
		        default:
		                $this->LogMsg( $message, $level );
		                break;
		}
	}
	/*@NULL@*/function LogMsg( $message, $level = PEAR_LOG_INFO )
	{
		if( $level <= $this->loglevel )
			$this->log[] = $message;
		return;
	}
	/*@NULL@*/function var_dump( $var, $level = PEAR_LOG_DEBUG )
	{
		if( $level <= $this->loglevel )
		        if( is_array( $var ) )
		        {
		                var_dump( get_class( $this ) );
		                var_dump(  $var );
		        }
		        else
		        {
		                var_dump( get_class( $this ) . "::" . $var );
		        }
		return;
	}
	/***************************************************************//**
	* Create a Name-Value pair as part of an array.  Can replace KEYS
	*
	* @param none
	* @returns array this classes vars in an array
	******************************************************************/
	/*@array@*/function objectvars2array()
	{
		$val = array();
		foreach( get_object_vars( $this ) as $key => $value )
		{
		        if( count( $this->dest_var_name_arr ) > 0 )
		        {
		                //No point trying to convert key names if we don't have destination names to convert to.
		                $key = str_replace( $this->obj_var_name_arr, $this->dest_var_name_arr, $key );
		        }
		        //if( "id" != $key )    //Not used for CREATE but needed for UPDATE.
		                if( isset( $this->$key ) )
		                        $val[] = array( "name" => $key, "value" => $this->$key );
		}
		$this->name_value_list = $val;
		return $val;
	}
	/**//************************************************************
	* Find the token matches between arrays.  GOTCHA - Exact Match
	*
	*
	* @param array
	* @param array
	* @returns int
	*****************************************************************/
	function match_tokens( $arr1, $arr2 )
	{
		$result = array_intersect( $arr1, $arr2 );
		return count( $result );
	}
	/**//**********************************************************************
	* Convert Statement class to this object
	*
	* @since 20240228
	*
	* @param class
	* @returns int how many fields did we copy
	**************************************************************************/
	function obj2obj( $obj )
	{
		//	display_notification( __FILE__ . "::" . __LINE__ . print_r( $obj, true ) );
		if( is_array( $obj ) )
		        return $this->arr2obj( $obj );
		if( ! is_object( $obj ) )
		        throw new Exception( "Passed in data is neither an array nor an object.  We can't handle here!" );

		$cnt = 0;
		foreach( get_object_vars($this) as $key => $value )
		{
		        //	display_notification( __FILE__ . "::" . __LINE__ . " " . print_r( $key, true ) );
		        if( isset( $obj->$key ) )
		        {
		                //	display_notification( __FILE__ . "::" . __LINE__ . " $key $obj->$key" );
		                //$this->$key = $obj->$key;
		                $this->set( $key, $obj->$key );
		                //	display_notification( __FILE__ . "::" . __LINE__ . " " . print_r( $this->$key, true ) );
		                $cnt++;
		        }
		        else
		        {
		                //	display_notification( __FILE__ . "::" . __LINE__ . " $key not set in " . print_r( $obj, true ) );
		        }
		}
		//	display_notification( __FILE__ . "::" . __LINE__ . print_r( $this, true ) );
		return $cnt;
	}
	/**//**********************************************************************
	* Convert Transaction array to this object
	*
	* Using this class's set of variables, we set any that have
	* a value passed in via the array.  If there are fields in the array that
	* are not in this object, they are ignored
	*
	* @since 20240228
	*
	* @param array
	* @returns int how many fields did we copy
	**************************************************************************/
	function arr2obj( $arr )
	{
		//	display_notification( __FILE__ . "::" . __LINE__ . print_r( $arr, true ) );
		if( is_object( $arr ) )
		        return $this->obj2obj( $arr );
		if( ! is_array( $arr ) )
		        throw new Exception( "Passed in data is neither an array nor an object.  We can't handle here!" );

		$cnt = 0;
		foreach( get_object_vars($this) as $key => $value )
		{
		        //	display_notification( __FILE__ . "::" . __LINE__ . " " . print_r( $key, true ) );
		        if( isset( $arr[$key] ) )
		        {
		                //	display_notification( __FILE__ . "::" . __LINE__ . " $key $arr[$key]" );
		                //$this->$key = $arr[$key];
		                $this->set( $key, $arr[$key] );
		                //	display_notification( __FILE__ . "::" . __LINE__ . " " . print_r( $this->$key, true ) );
		                $cnt++;
		        }
		        else
		        {
		                //	display_notification( __FILE__ . "::" . __LINE__ . " $key not set in " . print_r( $arr, true ) );
		        }
		}
		//	display_notification( __FILE__ . "::" . __LINE__ . print_r( $this, true ) );
		return $cnt;
	}
	/**//***************************************************
	* Score a match for a field
	*
	* @param string field name
	* @param mixed value to compare
	* @returns int score modifier
	********************************************************/
	function score_matches( $field, $value )
	{
		if( isset( $this->matchscores[$field] ) )
		{
		        if( isset( $this->$field ) )
		        {
		                if( $this->$field == $value )
		                {
		                        return $this->matchscores[$field];
		                }
		        }
		}
		return 0;
	}
	/**//******************************************************
	* Check to see if our value is different than passed in
	*
	* Driven by bank_import
	*
	* @since 20240805
	*
	* @param string key
	* @param mixed value
	* @return bool is it different
	**********************************************************/
	function isdiff( $key, $value )
	{
		if( $this->$key !== $value )
		{
			return true;
		}
		return false;
	}
}

/***************DYNAMIC create setter and getter**********************
// Create dynamic method. Here i'm generating getter and setter dynimically
// Beware: Method name are case sensitive.
foreach ($obj as $func_name => $value) {
    if (!$value instanceOf Closure) {

	$obj->{"set" . ucfirst($func_name)} = function($stdObject, $value) use ($func_name) {  // Note: you can also use keyword 'use' to bind parent variables.
	    $stdObject->{$func_name} = $value;
	};

	$obj->{"get" . ucfirst($func_name)} = function($stdObject) use ($func_name) {  // Note: you can also use keyword 'use' to bind parent variables.
	    return $stdObject->{$func_name};
	};

    }
}


*************************************************************************/ 

/***********************TESTING******************************
class origin_child extends origin
{
	var $only_in_child;
}
$test = new origin_child();
var_dump( $test );
try {
	$test->set( 'only_in_child', true, true );
} catch( Exception $e )
{
	var_dump( $e );
}
try {
	$test->set( 'only_in_child', true );
} catch( Exception $e )
{
	var_dump( $e );
}
try {
	$test->set( 'only_in_child' );
} catch( Exception $e )
{
	var_dump( $e );
}
var_dump( $test );
/************!TESTING**********************/
?>
