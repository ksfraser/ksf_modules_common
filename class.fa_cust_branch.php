<?php

require_once( 'class.table_interface.php' );

$path_to_root = "../..";
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/banking.inc");
include_once($path_to_root."/sales/inquiry/customer_inquiry.php");

require_once( $path_to_root . '/sales/includes/db/customers_db.inc' ); //add_customer
require_once( $path_to_root . '/sales/includes/db/branches_db.inc' ); //add_branch
require_once( $path_to_root . '/includes/db/crm_contacts_db.inc' ); //add_crm_*
require_once( $path_to_root . '/includes/db/connect_db.inc' ); //db_query, ...
require_once( $path_to_root . '/includes/errors.inc' ); //check_db_error, ...


class fa_cust_branch extends table_interface
{
	/************************************************
	 * This is the FA customer branch table
	 * *********************************************/
	/*
| branch_code              | int(11)     | NO   | PRI | NULL    | auto_increment |
| debtor_no                | int(11)     | NO   | PRI | 0       |                |
| br_name                  | varchar(60) | NO   |     |         |                |
| br_address               | tinytext    | NO   |     | NULL    |                |
| area                     | int(11)     | YES  |     | NULL    |                |
| salesman                 | int(11)     | NO   |     | 0       |                |
| contact_name             | varchar(60) | NO   |     |         |                |
| default_location         | varchar(5)  | NO   |     |         |                |
| tax_group_id             | int(11)     | YES  |     | NULL    |                |
| sales_account            | varchar(15) | NO   |     |         |                |
| sales_discount_account   | varchar(15) | NO   |     |         |                |
| receivables_account      | varchar(15) | NO   |     |         |                |
| payment_discount_account | varchar(15) | NO   |     |         |                |
| default_ship_via         | int(11)     | NO   |     | 1       |                |
| disable_trans            | tinyint(4)  | NO   |     | 0       |                |
| br_post_address          | tinytext    | NO   |     | NULL    |                |
| group_no                 | int(11)     | NO   | MUL | 0       |                |
| notes                    | tinytext    | YES  |     | NULL    |                |
| inactive                 | tinyint(1)  | NO   |     | 0       |                |
| branch_ref               | varchar(30) | NO   | MUL | NULL    |                |
	 */
	protected $branch_code;
	protected $debtor_no;		//<@ Also in DEBTORS_MASTER
	protected $br_name;
	protected $br_address;
	protected $area;
	protected $salesman;
	protected $contact_name;
	protected $default_location;
	protected $tax_group_id;
	protected $sales_account;
	protected $sales_discount_account;
	protected $receivables_account;
	protected $payment_discount_account;
	protected $default_ship_via;
	protected $disable_trans;
	protected $br_post_address;
	protected $group_no;
	protected $notes;	//<@ Also in CRM_PERSON, debtors_master
	protected $inactivity;
	protected $branch_ref;
	function __construct( $caller = null )
	{
		parent::__construct( $caller );
		$descl = 'varchar(' . DESCRIPTION_LENGTH . ')';
		$this->table_details['tablename'] = TB_PREF . get_class( $this );
		$this->table_details['primarykey'] = "branch_code";

		$this->fields_array[] = array( 'name' => 'branch_code', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'debtor_no', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );	//<@ Also in DEBTORS_MASTER
		$this->fields_array[] = array( 'name' => 'br_name', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'br_address', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'area', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'salesman', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'contact_name', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'default_location', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'tax_group_id', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'sales_account', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'sales_discount_account', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'receivables_account', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'payment_discount_account', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'default_ship_via', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'disable_trans', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'br_post_address', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'group_no', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'notes', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );	//<@ Also in CRM_PERSON, debtors_master
		$this->fields_array[] = array( 'name' => 'inactivity', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'branch_ref', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );

/*
                       	$this-set( "debtor_no", "" );
			$this-set( "br_name", "" );
			$this-set( "branch_ref", "" );
			$this-set( "br_address", "" );
			$this-set( "salesman", "" );
			$this-set( "area", "" );
			$this-set( "tax_group_id", "" );
*/
			$this-set( "sales_account", get_company_pref('default_sales_act') );
			$this-set( "receivables_account", get_company_pref('debtors_act') );
                       	$this-set( "payment_discount_act",  get_company_pref('default_prompt_payment_act') );
                       	$this-set( "sales_discount_act", get_company_pref('default_sales_discount_act') );
/*
                       	$this-set( "default_location", "" );
                       	$this-set( "br_post_address", "" );
        	        $this-set( "disable_trans", 0 );
                        $this-set( "group_no", 0 );
                       	$this-set( "default_ship_via", "" );
*/

	}

/**
*	Functions native to frontaccounting in sales/includes/db/branches_db.inc
*		function add_branch($customer_id, $br_name, $br_ref, $br_address, $salesman, $area,
*		function update_branch($customer_id, $branch_code, $br_name, $br_ref, $br_address,
*		function delete_branch($customer_id, $branch_code)
*		function branch_in_foreign_table($customer_id, $branch_code, $table)
*		function get_branch($branch_id)
*		function get_cust_branch($customer_id, $branch_code)
*		function get_branch_accounts($branch_id)
*		function get_branch_name($branch_id)
*		function get_cust_branches_from_group($group_no)
*		function get_default_info_for_branch($customer_id)
*		function get_sql_for_customer_branches()
*		function get_branch_contacts($branch_code, $action=null, $customer_id=null, $default = true)
*		function _get_branch_contacts($branch_code, $action=null, $customer_id=null, $default = false)
*/
	/**//***************************************************************
	* Add a customer branch
	*
	* Inspired by import_paypal
	*
	* @params none uses internal
	* @returns int branch_id
	**********************************************************************/
	function add_branch()
	{
		//Each Customer (Individual and Multi Branch Headquarters) will have one branch record with debtor_no / debtor_ref / CustName data
    		//Each branch of the Multi Branch customers will have one branch record with branch_code / branch_ref / br_name data 

                
		if( ! isset( $this->debtor_no ) )
		{
			throw new Exception( "debtor_no not set.  Can't add a branch!", KSF_FIELD_NOT_SET );
		}
		if( ! isset( $this->br_name ) )
		{
			throw new Exception( "br_name not set.  Can't add a branch!", KSF_FIELD_NOT_SET );
		}
		if( ! isset( $this->branch_ref ) )
		{
			throw new Exception( "branch_ref not set.  Can't add a branch!", KSF_FIELD_NOT_SET );
		}
		if( ! isset( $this->br_address ) )
		{
			throw new Exception( "br_address not set.  Can't add a branch!", KSF_FIELD_NOT_SET );
		}
		if( ! isset( $this->salesman ) )
		{
			throw new Exception( "salesman not set.  Can't add a branch!", KSF_FIELD_NOT_SET );
		}
		if( ! isset( $this->area ) )
		{
			throw new Exception( "area not set.  Can't add a branch!", KSF_FIELD_NOT_SET );
		}
		if( ! isset( $this->tax_group_id ) )
		{
			throw new Exception( "tax_group_id not set.  Can't add a branch!", KSF_FIELD_NOT_SET );
		}
		if( ! isset( $this->sales_account ) )
		{
			throw new Exception( "sales_account not set.  Can't add a branch!", KSF_FIELD_NOT_SET );
		}
		if( ! isset( $this->receivables_account ) )
		{
			throw new Exception( "receivables_account not set.  Can't add a branch!", KSF_FIELD_NOT_SET );
		}
		if( ! isset( $this->payment_discount_act ) )
		{
			throw new Exception( "payment_discount_act not set.  Can't add a branch!", KSF_FIELD_NOT_SET );
		}
		if( ! isset( $this->default_location ) )
		{
			throw new Exception( "default_location not set.  Can't add a branch!", KSF_FIELD_NOT_SET );
		}
		if( ! isset( $this->br_post_address ) )
		{
			throw new Exception( "br_post_address not set.  Can't add a branch!", KSF_FIELD_NOT_SET );
		}
		if( ! isset( $this->disable_trans ) )
		{
			throw new Exception( "disable_trans not set.  Can't add a branch!", KSF_FIELD_NOT_SET );
		}
		if( ! isset( $this->group_no ) )
		{
			throw new Exception( "group_no not set.  Can't add a branch!", KSF_FIELD_NOT_SET );
		}
		if( ! isset( $this->default_ship_via ) )
		{
			throw new Exception( "default_ship_via not set.  Can't add a branch!", KSF_FIELD_NOT_SET );
		}
		if( ! isset( $this->notes ) )
		{
			throw new Exception( "notes not set.  Can't add a branch!", KSF_FIELD_NOT_SET );
		}
		if( ! isset( $this->sales_discount_act ) )
		{
			throw new Exception( "sales_discount_act not set.  Can't add a branch!", KSF_FIELD_NOT_SET );
		}
        
		add_branch(
                       	$this->debtor_no,
			$this->br_name,
			$this->branch_ref,
			$this->br_address,
			$this->salesman,
			$this->area,
			$this->tax_group_id,
			$this->sales_account,
			$this->receivables_account,
                       	$this->payment_discount_act,
                       	$this->sales_discount_act,
                       	$this->default_location,
                       	$this->br_post_address,
        	        $this->disable_trans,
                        $this->group_no,
                       	$this->default_ship_via,
                       	$this->notes
                );
		$this->branch_id = db_insert_id();
		return $this->branch_id;
	}
	/**//***************************************************************
	* Search for a customer by ID
	*
	* Came from import_paypal but modified
	*
	* @param int|null
	* @returns int
	*******************************************************************/
	function find_customer_branch_by_debtor_no( $debtor_no = null ) 
	{
		if( null != $debtor_no )
		{
			$this->set( "debtor_no", $debtor_no );
		}
		if( isset( $this->debtor_no ) )
		{
    			$sql = "SELECT branch_code "
        		    ."FROM ".TB_PREF."cust_branch cb "
        		    ."WHERE cb.debtor_no = ".db_escape($customer_id)
        		    ." LIMIT 1 ";
	
	    		$result = db_query($sql,"Cannot find customer branch by customer id");
	    		$row = db_fetch_row($result);
	    		return $row[0];
		}		
		else
		{
			throw new Exception( "Debtor Number not set so can't search for customer", KSF_FIELD_NOT_SET );
		}
	}
	/**//***********************
	* Provide for name compatibility to import_paypal
	*
	*
	* @param int|null
	* @returns int
	*******************************************************************/
	function find_customer_branch_by_customer_id( $id )
	{
		return $this->find_customer_branch_by_debtor_no( $id );
	}


}

