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


require_once( $path_to_root . '/includes/ui/ui_lists.inc' );
require_once( $path_to_root . '/sales/create_recurrent_invoices.php' );
require_once( $path_to_root . '/sales/customer_payments.php' );
require_once( $path_to_root . '/sales/inquiry/customer_inquiry.php' );
require_once( $path_to_root . '/sales/manage/customers.php' );

class fa_debtors_master_model extends table_interface
{
	/************************************************
	 * This is the FA debtors table
	 *
	 * This is the ACCOUNT in CRM systems?
	 * *********************************************/
	/*
| debtor_no     | int(11)      | NO   | PRI | NULL    | auto_increment |
| name          | varchar(100) | NO   | MUL |         |                |
| address       | tinytext     | YES  |     | NULL    |                |
| tax_id        | varchar(55)  | NO   |     |         |                |
| curr_code     | char(3)      | NO   |     |         |                |
| sales_type    | int(11)      | NO   |     | 1       |                |
| dimension_id  | int(11)      | NO   |     | 0       |                |
| dimension2_id | int(11)      | NO   |     | 0       |                |
| credit_status | int(11)      | NO   |     | 0       |                |
| payment_terms | int(11)      | YES  |     | NULL    |                |
| discount      | double       | NO   |     | 0       |                |
| pymt_discount | double       | NO   |     | 0       |                |
| credit_limit  | float        | NO   |     | 1000    |                |
| notes         | tinytext     | YES  |     | NULL    |                |
| inactive      | tinyint(1)   | NO   |     | 0       |                |
| debtor_ref    | varchar(30)  | NO   | UNI | NULL    |                |
+---------------+--------------+------+-----+---------+----------------+
	 */

	protected $debtor_no;	//also in cust_branch
	protected $name;	//<@ Also in CRM_PERSONS
				//first + last name
	protected $address;	//<@ Also in CRM_PERSONS
				//address 1&2, city, state, postal, country in 1
	protected $tax_id;
	protected $curr_code;
	protected $sales_type;
	protected $dimension_id;
	protected $dimension2_id;
	protected $credit_status;
	protected $payment_terms;
	protected $discount;
	protected $pymy_discout;
	protected $credit_limit;
	protected $notes;	//<@ Also in CRM_PERSON, cust_branch
	protected $inactive;	//<@ Also in CRM_PERSON
	protected $debtor_ref;

	function __construct( $caller = null )
	{
		parent::__construct( $caller );
		$descl = 'varchar(' . DESCRIPTION_LENGTH . ')';
		//$this->table_details['tablename'] = TB_PREF . get_class( $this );
		$this->table_details['tablename'] = TB_PREF . "debtors_master";
		$this->table_details['primarykey'] = "debtor_no";
		$this->fields_array[] = array( 'name' => 'debtor_no', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );	//also in cust_branch
		$this->fields_array[] = array( 'name' => 'name', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );	//<@ Also in CRM_PERSONS
					//first + last name
		$this->fields_array[] = array( 'name' => 'address', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );	//<@ Also in CRM_PERSONS
					//address 1&2, city, state, postal, country in 1
		$this->fields_array[] = array( 'name' => 'tax_id', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'curr_code', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'sales_type', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'dimension_id', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'dimension2_id', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'credit_status', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'payment_terms', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'discount', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'pymy_discout', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'credit_limit', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'notes', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );	//<@ Also in CRM_PERSON, cust_branch
		$this->fields_array[] = array( 'name' => 'inactive', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );	//<@ Also in CRM_PERSON
		$this->fields_array[] = array( 'name' => 'debtor_ref', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );

	}
	/*********************************************************************/
	/***************************/includes/ui/ui_lists.inc*****************/
	/*********************************************************************/
	/** /
	function customer_list()
	{
		customer_list($name, $selected_id=null, $spec_option=false, $submit_on_change=false, $show_inactive=false, $editkey = false);
	}
	function customer_list_cells()
	{
		customer_list_cells($label, $name, $selected_id=null, $all_option=false, $submit_on_change=false, $show_inactive=false, $editkey = false);
	}
	function customer_list_row()
	{
		customer_list_row($label, $name, $selected_id=null, $all_option = false, $submit_on_change=false, $show_inactive=false, $editkey = false);
	}
	function customer_branches_list()
	{
		customer_branches_list($customer_id, $name, $selected_id=null, $spec_option = true, $enabled=true, $submit_on_change=false, $editkey = false);
	}
	function customer_branches_list_cells()
	{
	 	customer_branches_list_cells($label,$customer_id, $name, $selected_id=null,  $all_option = true, $enabled=true, $submit_on_change=false, $editkey = false);
	}
	function customer_branches_list_row()
	{
		customer_branches_list_row($label, $customer_id, $name, $selected_id=null, $all_option = true, $enabled=true, $submit_on_change=false, $editkey = false);
	}
	function cust_allocations_list_cells()
	{
	 	cust_allocations_list_cells($label, $name, $selected=null);
	}
	/**/
	/*********************************************************************/
	/***************************/sales/create_recurrent_invoices.php******/
	/*********************************************************************/
	function create_recurrent_invoices();
	{
		create_recurrent_invoices($customer_id, $branch_id, $order_no, $tmpl_no, $date, $from, $to);
	}
	/*********************************************************************/
	/***************************/sales/customer_payments.php**************/
	/*********************************************************************/
	function read_customer_data()
	{
		read_customer_data();
	}
	/*********************************************************************/
	/***************************/sales/inquiry/customer_inquiry.php*******/
	/*********************************************************************/
	function display_customer_summary()
	{
		display_customer_summary($customer_record);
	}
	/*********************************************************************/
	/***************************/sales/customer_payments.php**************/
	/*********************************************************************/
	function customer_settings()
	{
		customer_settings($selected_id);
	}
	/**//*****************************************************************
	* Search for a customer (account) by name
	*
	* Came from import_paypal but modified
	*
	* @param string|null if null use internal NAME
	* #returns int
	***********************************************************************/
	function find_customer_by_name( $name = null ) 
	{
		if( null != $name )
		{
			$this->set( "name", $name );
		}
		if( isset( $this->name ) )
		{
    			$sql = "SELECT debtor_no "
            			."FROM ".TB_PREF."debtors_master dm "
            			."WHERE name = ". db_escape( $this->name );
    			$result = db_query($sql,"Cannot customer by name");
    			$row = db_fetch_row($result);
    			return $row[0];
		}
		else
		{
			throw new Exception( "Name isn't set so we can't query by name!", KSF_FIELD_NOT_SET );
		}
	}
	/**//*****************************************************************
	* Search for a customer by email.
	*
	* Came from import_paypal but modified
	*
	* @param string
	* #returns int
	***********************************************************************/
	function find_customer_by_email( $email ) 
	{
	    $sql = "SELECT debtor_no "
        	    ."FROM ".TB_PREF."debtors_master dm "
        	    ."INNER JOIN ".TB_PREF."crm_contacts cc ON dm.debtor_no = cc.entity_id AND cc.type = 'customer' "
        	    ."INNER JOIN ".TB_PREF."crm_persons cp on cc.person_id = cp.id "
        	    ."WHERE cp.email = ".db_escape($email);

    		$result = db_query($sql,"Cannot customer by email");
    		$row = db_fetch_row($result);
    		return $row[0];
	}
}

