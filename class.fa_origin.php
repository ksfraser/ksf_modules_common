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
	protected $trans_type;	//!<int	The transaction type.  ST_ PT_ etc
	protected $trans_no;	//!<int
	protected $trans_date;	//!<date
	protected $memo_;	//!<string

	function __construct( $loglevel = PEAR_LOG_DEBUG )
	{
		//display_notification( __FILE__ . "::" . __LINE__ );
		parent::__construct($loglevel);
		//display_notification( __FILE__ . "::" . __LINE__ );
	}
	function set( $field, $value = null, $enforce = true )
	{
		//	display_notification( __FILE__ . "::" . __LINE__ );
		switch( $field )
		{
			case 'trans_type':
				if( ! is_numeric( $value ) )
					throw new Exception( "Field $field is a number.  Non numeric passed in!" );
				//There is a set of valid values
				/*
					if $value !== ST_JE OR ...
				*/
			break;
			case 'trans_no':
				if( ! is_numeric( $value ) )
					throw new Exception( "Field $field is a number.  Non numeric passed in!" );
			break;
                        case "trans_date":
                                $value = sql2date( $value );
			break;
			default:
				break;
		}
		//	display_notification( __FILE__ . "::" . __LINE__ );
		$ret = parent::set( $field, $value, $enforce );
		//	display_notification( __FILE__ . "::" . __LINE__ . "::Parent (origin) Set Return: $ret" );
		return $ret;
	}
	/**//*******************************************************
	* Reset a transaction date that isn't in the fiscal year
	*
	*	Many built in functions force this.  HOWEVRE
	*	as I am doing imports of historical banking data
	*	I don't want to arbitrarily replace dates.  
	*	That would be bad.  This is the compromise.
	*
	* @param none
	* @returns date
	********************************************************/
	function trans_date_in_fiscal_year()
	{
		if ( !is_date_in_fiscalyear( $this->trans_date ) )
		{
			$this->trans_date = end_fiscalyear();
		}
		return $this->trans_date;
	}
	/*@bool@*/function validate()
	{
	}
	/**//********************************************************************************
	* Get the next Ref number
	*
	* FA is moving away from a reference table.
	* References are now stored in trans table.
	*	includes/references.inc
	*
	* @param none
	* @returns none  Sets Reference
	************************************************************************************/
	function getNextRef()
	{
		global $Refs;
		do {
							//get_next($type, $line=null, $context=null)
						//$Refs->get_next($cart->trans_type, null, $cart->tran_date);
			$this->reference = $Refs->get_next($this->trans_type);
		} while(!is_new_reference($this->reference, $this->trans_type));
	}
	/**//********************************************************************************
	* Save Ref number
	*
	* FA is moving away from a reference table.
	* References are now stored in trans table.
	*	includes/references.inc
	*
	* @param none
	* @returns none
	************************************************************************************/
	function saveRef()
	{
		//We don't currently initiate these, at least in this base class.
		if( ! isset( $this->trans_type ) )
			throw new Exception( "Trans Type not set. REQUIRED" );
		if( ! isset( $this->trans_no ) )
			throw new Exception( "Trans Number not set. REQUIRED" );
		if( ! isset( $this->reference ) )
			throw new Exception( "Reference not set. REQUIRED" );

		global $Refs;
			//function save($type, $id, $reference, $line = null)
		$Refs->save( $this->trans_type, $this->trans_no, $this->reference );
	}
	/**//*********************************************************
	* Save comments for the transaction
	*
	* @params none
	* @returns none
	**************************************************************/
	function addComments()
	{
		//We don't currently initiate these, at least in this base class.
		if( ! isset( $this->trans_type ) )
			throw new Exception( "Trans Type not set. REQUIRED" );
		if( ! isset( $this->trans_no ) )
			throw new Exception( "Trans Number not set. REQUIRED" );
		if( ! isset( $this->trans_date ) )
			throw new Exception( "Trans Date not set. REQUIRED" );
		if( ! isset( $this->memo_ ) )
			throw new Exception( "Memo_ not set. REQUIRED" );

		add_comments($this->trans_type, $this->trans_no, $this->trans_date, $this->memo_);
	}
}

