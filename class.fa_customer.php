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

class fa_crm_persons extends table_interface
{
	protected $id;
	protected $ref;
	protected $name;	//<@ Also in debtors_master
	protected $name2;
	protected $address; 	//<@ Also in debtors_master
				//address 1&2, city, state, postal, country in 1
	protected $phone;
	protected $phone2;
	protected $fax;
	protected $email;
	protected $lang;
	protected $notes;	//<@ Also in debtors_master, cust_branch
	protected $inactive;	//<@ Also in debtors_master

	function __construct( $caller = null )
	{
		parent::__construct( $caller );
		$descl = 'varchar(' . DESCRIPTION_LENGTH . ')';
		$this->table_details['tablename'] = TB_PREF . get_class( $this );

		$this->fields_array[] = array( 'name' => 'id', 'label' => '', 'type' => 'int(11)', 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'ref', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'name', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );	//<@ Also in debtors_master
		$this->fields_array[] = array( 'name' => 'name2', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'address', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' ); 	//<@ Also in debtors_master
				//address 1&2, city, state, postal, country in 1
		$this->fields_array[] = array( 'name' => 'phone', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'phone2', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'fax', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'email', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'lang', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'notes', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );	//<@ Also in debtors_master, cust_branch
		$this->fields_array[] = array( 'name' => 'inactive', 'label' => '', 'type' => 'bool', 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );	//<@ Also in debtors_master

		$this->table_details['primarykey'] = "id";
	}


}

class fa_crm_contacts extends table_interface
{
	var $id;
	var $person_id;
	var $type;
	var $action;
	var $entity_id

	function __construct( $caller = null )
	{
		parent::__construct( $caller );
		$descl = 'varchar(' . DESCRIPTION_LENGTH . ')';
		$this->table_details['tablename'] = TB_PREF . get_class( $this );
		$this->table_details['primarykey'] = "id";
		$this->fields_array[] = array( 'name' => 'id', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'person_id', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'type', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'action', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'entity_id', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
	}
}

class fa_debtors_master extends table_interface
{

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
		$this->table_details['tablename'] = TB_PREF . get_class( $this );
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
}

class fa_cust_branch extends table_interface
{
	protected $branch_code;
	protected $debtor_no;	//<@ Also in DEBTORS_MASTER
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

	}
}

/************************************************************************************************//**
 *
 * 	fa_customer is a class setup to interface to native FA customer routines.
 *
 *	Must Have set:
 *		CustName
 *		cust_ref
 *
 *	Uses the following native FA routines:
 *		add_customer
 *		add_branch
 *		add_crm_person
 *		get_company_currency
 *		update_customer
 *		get_customer
 *
 * **************************************************************************************************/
class fa_customer extends table_interface
{
	/*
	protected $branch;	//fa_cust_branch
	protected $debtor;	//fa_debtors_master
	protected $contact;	//fa_crm_contacts
	protected $person;	//fa_crm_persons
	 */

	protected $debtor_no;	//was debtor_id
	protected $customer_id = '';
	protected $branch_id;
	protected $CustName = "";	
	protected $cust_ref = "";	//Customer Short Name
	protected $tax_id = "";	//GST No					//fa_cust
	protected $phone = "";							//fa_cust
	protected $phone2 = "";							//fa_cust
	protected $fax = "";							//fa_cust
	protected $email = "";							//fa_cust
	protected $discount;							//fa_cust
	protected $pymt_discount = "";						//fa_cust
	protected $credit_limit = "1000";					//fa_cust
	protected $curr_code = "CAD";		//Customer Currency //fa_cust
	protected $sales_type = "1";		//3-Band.  1-Retail, 4-wholesale	//PRICE LIST	//fa_cust
	protected $salesman = "2";		//Kevin
	protected $payment_terms = "4";	//Cash					//fa_cust
	protected $credit_status = "1";	//Good					//fa_cust
	protected $dimension_id = "20";	//General Interest			//fa_cust
	protected $dimension2_id = "4";	//Individual				//fa_cust
	protected $location = "KSF";
	protected $default_ship_via = "2";	//Canada Post.  1 - Instore.	//fa_cust
	protected $area = "2";		//CANADA				//fa_cust
	protected $sales_area;		//country name
	protected $tax_group = "GST";		//GST
	protected $tax_group_id = "1";	//GST
	protected $country_code;
	protected $sales_account;						//fa_cust
	protected $sales_discount_account;					//fa_cust
	protected $receivables_account;						//fa_cust
	protected $payment_discount_account;					//fa_cust
	protected $inactive = 0;						//fa_cust

	var $fieldlist = array(
		'popup', '_focus', '_modified', '_token', 'customer_id', 'CustName',
		'cust_ref', 'tax_id', 'phone', 'phone2', 'fax', 'email',
		'discount', 'pymt_discount', 'credit_limit', 'curr_code', 'sales_type', 'salesman',
		'payment_terms', 'credit_status', 'dimension_id', 'dimension2_id', 'location', 'default_ship_via',
		'area',	'tax_group_id', 'submit',
	);
	/*AntErpFA fields (for contact) */
	var $first_name = "SetMe";
	var $last_name = "SetMe";
	var $street;
	var $city;
	var $postal_code;
	var $state;
	var $address = "SetMe";
	var $notes = "";

	function __construct( $caller = null )
	{
		parent::__construct( $caller );
		$descl = 'varchar(' . DESCRIPTION_LENGTH . ')';
		$this->table_details['tablename'] = TB_PREF . get_class( $this );
		$this->table_details['primarykey'] = "customer_id";

		$this->fields_array[] = array( 'name' => 'debtor_no', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );	//was debtor_id
		$this->fields_array[] = array( 'name' => 'customer_id = ''', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'branch_id', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'CustName', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );	
		$this->fields_array[] = array( 'name' => 'cust_ref', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );	//Customer Short Name
		$this->fields_array[] = array( 'name' => 'tax_id', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );	//GST No					//fa_cust
		$this->fields_array[] = array( 'name' => 'phone', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );							//fa_cust
		$this->fields_array[] = array( 'name' => 'phone2', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );							//fa_cust
		$this->fields_array[] = array( 'name' => 'fax', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );							//fa_cust
		$this->fields_array[] = array( 'name' => 'email', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );							//fa_cust
		$this->fields_array[] = array( 'name' => 'discount', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );							//fa_cust
		$this->fields_array[] = array( 'name' => 'pymt_discount', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );						//fa_cust
		$this->fields_array[] = array( 'name' => 'credit_limit', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '1000' );					//fa_cust
		$this->fields_array[] = array( 'name' => 'curr_code', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => "CAD" );		//Customer Currency //fa_cust
		$this->fields_array[] = array( 'name' => 'sales_type', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => "1" );		//3-Band.  1-Retail, 4-wholesale	//PRICE LIST	//fa_cust
		$this->fields_array[] = array( 'name' => 'salesman', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => "2" );		//Kevin
		$this->fields_array[] = array( 'name' => 'payment_terms', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '4' );	//Cash					//fa_cust
		$this->fields_array[] = array( 'name' => 'credit_status', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '1' );	//Good					//fa_cust
		$this->fields_array[] = array( 'name' => 'dimension_id', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '20' );	//General Interest			//fa_cust
		$this->fields_array[] = array( 'name' => 'dimension2_id', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '4' );	//Individual				//fa_cust
		$this->fields_array[] = array( 'name' => 'location', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => 'KSF' );
		$this->fields_array[] = array( 'name' => 'default_ship_via', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '2' );	//Canada Post.  1 - Instore.	//fa_cust
		$this->fields_array[] = array( 'name' => 'area', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '2' );		//CANADA				//fa_cust
		$this->fields_array[] = array( 'name' => 'sales_area', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );		//country name
		$this->fields_array[] = array( 'name' => 'tax_group', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => 'GST' );		//GST
		$this->fields_array[] = array( 'name' => 'tax_group_id', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '1' );	//GST
		$this->fields_array[] = array( 'name' => 'country_code', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'sales_account', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );						//fa_cust
		$this->fields_array[] = array( 'name' => 'sales_discount_account', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );					//fa_cust
		$this->fields_array[] = array( 'name' => 'receivables_account', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );						//fa_cust
		$this->fields_array[] = array( 'name' => 'payment_discount_account', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );					//fa_cust
		$this->fields_array[] = array( 'name' => 'inactive', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '0' );						//fa_cust

	}
		
	}
	/*@bool@*/function validate_data()
	{
		if (strlen( $this->CustName ) < 1 )
			return FALSE;
		if (strlen( $this->cust_ref ) < 1 )
			return FALSE;
		if( isset( $this->city ) OR isset($this->street) OR isset($this->state ) OR isset($this->postal_code ) )
			$this->address = $this->street . "\n" . $this->city 
				. ", " . $this->state . " " . $this->postal_code;
		if( !isset( $this->credit_limit ) )
			$this->credit_limit = price_format($SysPrefs->default_credit_limit());
		if( !$this->is_num( $this->credit_limit ) )
		{
			$this->credit_limit = 0;
		}
		if( !$this->is_num( $this->pymt_discount, 0, 100 ) )
		{
			$this->pymt_discount = 0;
		}
		if( !$this->is_num( $this->discount, 0, 100 ) )
		{
			$this->discount = 0;
		}
		if( !isset( $this->curr_code ) )
			$this->curr_code =  get_company_currency();

		return TRUE;
	}
	/*@bool@*/function is_num( $variable, $min = null, $max = null )
	{
		if( !isset( $variable )
			return FALSE;
		if( !is_numeric( $variable )
			return FALSE;
		if( isset( $min ) )
			if( $variable < $min )
				return FALSE;
		if( isset( $max ) )
			if( $variable > $max )
				return FALSE;
	}	
	/*****************************************************************//**
	 *	update_customer takes data for an existing customer and updates them.
	 *
	 * @return bool
	 *
	 * *******************************************************************/
	/*@bool@*/function update_customer()
	{
		if( !$this->validate_data() )
			return FALSE;
		update_customer($this->customer_id,		
				$this->CustName,
				$this->cust_ref,
				$this->address,
				$this->tax_id,
				$this->curr_code,
				$this->dimension_id,
				$this->dimension2_id,
				$this->credit_status,
				$this->payment_terms,
				$this->discount/100,
				$this->pymt_discount/100,
				$this->credit_limit,
				$this->sales_type,
				$this->notes
		);

		update_record_status($this->customer_id, $this->inactive,
			'debtors_master', 'debtor_no');
		return TRUE;

	}
	/*****************************************************************//**
	 *	add_new_customer takes data for a new customer and inserts them.
	 *
	 *	add_new_customer takes data for a new customer and inserts them.
	 *		Adds the customer
	 *		Adds the default branch
	 *		adds CRM Contact data
	 *	uses validate_data to enforce that a minimal set of data is present
	 *	and ensure that numbers are reasonable.
	 *	
	 * @return bool false indicates bad input data.  True indicates running 
	 * 						through the subroutines
	 *
	 * *******************************************************************/
	/*@bool@*/function add_new_customer()
	{
		//basically cloned from handle_submit within sales/manage/customers.php
		//Each individual customer will have 1 branch (themselves)
		//Each multi-branch customer has 1 branch (HQ) plus 1 branch for each branch entity
		if( !$this->validate_data() )
			return FALSE;
		$this->add_customer();
		$this->add_branch();
		$this->add_crm_person();
		$this->add_crm_contact( 'cust_branch', 'general', $this->branch_id );
		$this->add_crm_contact( 'customer', 'general', $this->customer_id );
		return TRUE;
	}
	private function add_customer()
	{
		add_customer(
				$this->CustName,
				$this->cust_ref,
				$this->address,
				$this->tax_id,
				$this->curr_code,
				$this->dimension_id,
				$this->dimension2_id,
				$this->credit_status,
				$this->payment_terms,
				$this->discount /100,
				$this->pymt_discount /100,
				$this->credit_limit,
				$this->sales_type,
				$this->notes
		);
 		$this->customer_id = $selected_id = $_POST['customer_id'] = db_insert_id();
	}
	private function add_branch()
	{
		//Each Customer (Individual and Multi Branch Headquarters) will have one branch record with debtor_no / debtor_ref / CustName data
    		//Each branch of the Multi Branch customers will have one branch record with branch_code / branch_ref / br_name data 
                
		add_branch(
                       	$this->customer_id,
			$this->CustName,
			$this->cust_ref,
			$this->address,
			$this->salesman,
			$this->area,
			$this->tax_group_id,
			'',
                       	get_company_pref('default_sales_discount_act'),
                       	get_company_pref('debtors_act'),
                       	get_company_pref('default_prompt_payment_act'),
                       	$this->location,
                       	$this->address,
        	        0,
                        0,
                       	$this->default_ship_via,
                       	$this->notes
                );
		$this->branch_id = $selected_branch = db_insert_id();
	}
	private function add_crm_person()
	{
		//Each individual customer will have 1 branch (themselves)
		//Each multi-branch customer has 1 branch (HQ) plus 1 branch for each branch entity

		//add_crm_person($ref, $name, $name2, $address, $phone, $phone2, $fax, $email, $lang, $notes, $cat_ids=null, $entity=null)
		add_crm_person($this->CustName, $this->cust_ref, '', $this->address, $this->phone, $this->phone2, $this->fax, $this->email, '', $this->notes);
		$this->person_id = db_insert_id();
	}
	private function add_crm_contact( $type, $action, $id )
	{
                add_crm_contact( $type, $action, $id, $this->person_id);
	}
	/*****************************************************************//**
	 *	get_customer uses the FA routine of the same name to look up a customer
	 *	id and return the associated data.
	 *
	 *
	 * @return null
	 *
	 * *******************************************************************/
	function get_customer()
	{
		$row = get_customer( $this->customer_id );
		$this->CustName = $row["name"];
		$this->cust_ref = $row["debtor_ref"];
		$this->address  = $row["address"];
		$this->tax_id  = $row["tax_id"];
		$this->dimension_id  = $row["dimension_id"];
		$this->dimension2_id  = $row["dimension2_id"];
		$this->sales_type = $row["sales_type"];
		$this->curr_code  = $row["curr_code"];
		$this->credit_status  = $row["credit_status"];
		$this->payment_terms  = $row["payment_terms"];
		$this->discount  = percent_format($row["discount"] * 100);
		$this->pymt_discount  = percent_format($row["pymt_discount"] * 100);
		$this->credit_limit	= price_format($row["credit_limit"]);
		$this->notes  = $row["notes"];
		$this->inactive = $row["inactive"];
		return;
	}
}

?>
