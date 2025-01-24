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
require_once( __DIR__ . '/class.fa_cust_branch.php' );


/************************************************************************************************//**
 *
 * 	fa_customer is a class setup to interface to native FA customer routines.
 *
 *	Must Have set:
 *		CustName
 *		cust_ref
 *
 *
 *	Refactoring to use the MODEL classes of
 *		debtors_master
 *		crm_persons
 *		crm_contacts
 *
 *	Some of the following native FA routines will be moved into the above classes
 *		durinig the refactor!
 *	Uses the following native FA routines:
 *		add_customer
 *		add_branch
 *		add_crm_person
 *		get_company_currency
 *		update_customer
 *		get_customer
 *
 *	TABLE_INTERFACE is not the right thing to extend.  This particular class should
 *	not be dealing with SQL.  It should be a controller calling other classes!
 *
 * **************************************************************************************************/
class fa_customer extends table_interface
{
	protected $cust_branch;		//fa_cust_branch
	protected $debtors_master;	//fa_debtors_master
	protected $crm_contacts;	//fa_crm_contacts
	protected $crm_persons;		//fa_crm_persons

	protected $debtor_no;	//was debtor_id				//fa_cust cust_branch  							//debtors_master
	protected $customer_id = '';
	protected $branch_id;						//branch_code in cust_branch??
	protected $CustName = "";	//name + name2 from crm_persons						//cust_branch as br_name	//debtors_master
	protected $name;									//crm_persons
	protected $name2;									//crm_persons
	protected $cust_ref = "";	//Customer Short Name		//crm_persons - ref			//cust_branch as branch_ref	//debtors_master as debtors_ref
	protected $tax_id = "";	//GST No					//fa_cust							//debtors_master
	protected $phone = "";							//fa_cust	//crm_person
	protected $phone2 = "";							//fa_cust	//crm_person
	protected $fax = "";							//fa_cust	//crm_person
	protected $email = "";							//fa_cust	//crm_person
	protected $discount;							//fa_cust							//debtors_master
	protected $payment_terms;	//<!int													//debtors_master
	protected $pymt_discount = "";						//fa_cust							//debtors_master
	protected $credit_limit = "1000";					//fa_cust							//debtors_master
	protected $curr_code = "CAD";		//Customer Currency //fa_cust									//debtors_master
	protected $sales_type = "1";		//3-Band.  1-Retail, 4-wholesale	//PRICE LIST	//fa_cust				//debtors_master
	protected $salesman = "2";		//Kevin								//cust_branch
	protected $area;											//cust_branch
	protected $payment_terms = "4";	//Cash					//fa_cust
	protected $credit_status = "1";	//Good					//fa_cust							//debtors_master
	protected $dimension_id = "20";	//General Interest			//fa_cust							//debtors_master
	protected $dimension2_id = "4";	//Individual				//fa_cust							//debtors_master
	protected $default_location = "KSF";									//cust_branch			
	protected $default_ship_via = "2";	//Canada Post.  1 - Instore.	//fa_cust			//cust_branch
	protected $area = "2";		//CANADA				//fa_cust
	protected $sales_area;		//country name
	protected $tax_group = "GST";		//GST
	protected $tax_group_id = "1";	//GST									//cust_branch
	protected $country_code;
	protected $sales_account;						//fa_cust			//cust_branch
	protected $sales_discount_account;					//fa_cust			//cust_branch
	protected $receivables_account;						//fa_cust			//cust_branch
	protected $payment_discount_account;					//fa_cust			//cust_branch
	protected $inactive = 0;						//fa_cust 	//crm_persons	//cust_branch			//debtors_master
	protected $person_id;	//crm_contacts
	protected $type;	//crm_contacts	//supplier or customer			(crm_categories)
	protected $action;	//crm_contacts	//general/delivery/order/invoice/... 	(crm_categories)
	protected $entity_id;	//crm_contacts	debtors_master or supplier
	protected $address;									//crm_persons	//cust_branch (as br_address)	//debtors_master
	protected $lang;									//crm_persons
	protected $notes;									//crm_persons	//cust_branch			//debtors_master
	protected $disable_trans;										//cust_branch
	protected $br_post_address;										//cust_branch
	protected $group_no;											//cust_branch
	

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
		$this->fields_array[] = array( 'name' => 'default_location', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => 'KSF' );
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


		$this->cust_branch = new fa_cust_branch();		//fa_cust_branch
		$this->debtors_master = new fa_debtors_master();	//fa_debtors_master
		$this->crm_contacts = new fa_crm_contacts();		//fa_crm_contacts
		$this->crm_persons = new fa_crm_persons();		//fa_crm_persons

	}
	/**//*************************************************
	* Set our values.  Also set our dependant classes
	*
	* @param string variable
	* @param string value
	* @param bool enforce membership in class
	* @returns bool success or not
	*******************************************************/
	function set( $var, $value, $enforce )
	{
		switch( $var )
		{
			case ('debtor_no' ):	
				$this->cust_branch->set( $var, $value, $enforce );
				$this->debtors_master->set( $var, $value, $enforce );
				break;
			case ('customer_id' ):
				break;
			case ('branch_id' ):	
				//branch_code in cust_branch??
				//$this->cust_branch->set( $var, $value, $enforce );
				break;
			case ('CustName' ):	
				//name + name2 from crm_persons	
				//cust_branch as br_name
				$this->debtors_master->set( $var, $value, $enforce );
				break;
			case ('name' ):	
				$this->crm_persons->set( $var, $value, $enforce );
				break;
			case ('name2' ):
				$this->crm_persons->set( $var, $value, $enforce );
				break;
			case ('cust_ref' ):	
				//Customer Short Name	
				$this->crm_persons->set( $var, $value, $enforce ); - ref	
				//cust_branch as branch_ref	
				//$this->cust_branch->set( $var, $value, $enforce );
				$this->debtors_master->set( $var, $value, $enforce );// as debtors_ref
				break;
			case ('tax_id' ):	
				//GST No
				//fa_cust							
				$this->debtors_master->set( $var, $value, $enforce );
				break;
			case ('phone' ):	
				//fa_cust
				$this->crm_persons->set( $var, $value, $enforce );
				break;
			case ('phone2' ):			
				//fa_cust	
				$this->crm_persons->set( $var, $value, $enforce );
				break;
			case ('fax' ):		
				//fa_cust
				$this->crm_persons->set( $var, $value, $enforce );
				break;
			case ('email' ):	
				//fa_cust
				$this->crm_persons->set( $var, $value, $enforce );
				break;
			case ('discount' ):
				//fa_cust	
				$this->debtors_master->set( $var, $value, $enforce );
				break;
			case ('payment_terms' ):	
				//<!int													
				$this->debtors_master->set( $var, $value, $enforce );
				break;
			case ('pymt_discount' ):
				//fa_cust	
				$this->debtors_master->set( $var, $value, $enforce );
				break;
			case ('credit_limit' ):		
				//fa_cust	
				$this->debtors_master->set( $var, $value, $enforce );
				break;
			case ('curr_code' ):		
				//Customer Currency 
				//fa_cust	
				$this->debtors_master->set( $var, $value, $enforce );
				break;
			case ('sales_type' ):		
				//3-Band.  1-Retail, 4-wholesale	
				//PRICE LIST	
				//fa_cust				
				$this->debtors_master->set( $var, $value, $enforce );
				break;
			case ('salesman' ):		
				//Kevin			
				$this->cust_branch->set( $var, $value, $enforce );
				break;
			case ('area' ):	
				$this->cust_branch->set( $var, $value, $enforce );
				break;
			case ('payment_terms' ):	
				//Cash					
				//fa_cust
				break;
			case ('credit_status' ):	
				//Good					
				//fa_cust							
				$this->debtors_master->set( $var, $value, $enforce );
				break;
			case ('dimension_id' ):	
				//General Interest			
				//fa_cust							
				$this->debtors_master->set( $var, $value, $enforce );
				break;
			case ('dimension2_id' ):	
				//Individual				
				//fa_cust							
				$this->debtors_master->set( $var, $value, $enforce );
				break;
			case ('default_location' ):						
				$this->cust_branch->set( $var, $value, $enforce );
				break;
			case ('default_ship_via' ):	
				//Canada Post.  1 - Instore.	
				//fa_cust		
				$this->cust_branch->set( $var, $value, $enforce );
				break;
			case ('area' ):		
				//CANADA
				//fa_cust
				break;
			case ('sales_area' ):		
				//country name
				break;
			case ('tax_group' ):
				//GST
				break;
			case ('tax_group_id' ):	
				//GST									
				$this->cust_branch->set( $var, $value, $enforce );
				break;
			case ('country_code' ):
				break;
			case ('sales_account' ):	
				//fa_cust	
				$this->cust_branch->set( $var, $value, $enforce );
				break;
			case ('sales_discount_account' ):	
				//fa_cust		
				$this->cust_branch->set( $var, $value, $enforce );
				break;
			case ('receivables_account' ):		
				//fa_cust		
				$this->cust_branch->set( $var, $value, $enforce );
				break;
			case ('payment_discount_account' ):	
				//fa_cust		
				$this->cust_branch->set( $var, $value, $enforce );
				break;
			case ('inactive = 0' ):						
				//fa_cust 	
				$this->crm_persons->set( $var, $value, $enforce );	
				$this->cust_branch->set( $var, $value, $enforce );
				$this->debtors_master->set( $var, $value, $enforce );
				break;
			case ('person_id' ):	
				//crm_contacts
				break;
			case ('type' ):	
				//crm_contacts	
				//supplier or customer			(crm_categories)
				break;
			case ('action' ):	
				//crm_contacts
				//general/delivery/order/invoice/... 	(crm_categories)
				break;
			case ('entity_id' ):	
				//crm_contacts	debtors_master or supplier
				break;
			case ('address' ):			
				$this->crm_persons->set( $var, $value, $enforce );
				//cust_branch (as br_address)	
				//$this->cust_branch->set( $var, $value, $enforce );
				$this->debtors_master->set( $var, $value, $enforce );
				break;
			case ('lang' ):		
				$this->crm_persons->set( $var, $value, $enforce );
				break;
			case ('notes' ):	
				$this->crm_persons->set( $var, $value, $enforce );	
				$this->cust_branch->set( $var, $value, $enforce );
				$this->debtors_master->set( $var, $value, $enforce );
				break;
			case ('disable_trans' ):										
				$this->cust_branch->set( $var, $value, $enforce );
				break;
			case ('br_post_address' ):	
				$this->cust_branch->set( $var, $value, $enforce );
				break;
			case ('group_no' ):											
				$this->cust_branch->set( $var, $value, $enforce );
				break;
			default:
				return false;
		}
		parent::set( $var, $value, $enforce );
		return true;
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
	function add_customer()
	{
			// add_customer($CustName, $cust_ref, $address, $tax_id, $curr_code,
        		//	$dimension_id, $dimension2_id, $credit_status, $payment_terms, $discount, $pymt_discount,
        		//	$credit_limit, $sales_type, $notes)

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
 		$this->customer_id =  db_insert_id();
		return $this->customer_id;
	}
	/**//*************************************************
	* Call add_branch in class.fa_cust_branch.php
	*
	* @param none user internal
	* @return int the value from that class
	*******************************************************/
	function add_branch()
	{
		//Each Customer (Individual and Multi Branch Headquarters) will have one branch record with debtor_no / debtor_ref / CustName data
    		//Each branch of the Multi Branch customers will have one branch record with branch_code / branch_ref / br_name data 

		$branch = new fa_cust_branch();
       		$branch->set( "debtor_no", $this->customer_id );
       		$branch->set( "br_name", $this->CustName );
       		$branch->set( "cust_ref", $this->cust_ref );
        	$branch->set( "address", $this-> );
        	$branch->set( "area", $this->area );
        	$branch->set( "salesman", $this->salesman );
        	$branch->set( "tax_group_id", $this->tax_group_id );
        	$branch->set( "default_ship_via", $this->default_ship_via );
        	$branch->set( "notes", $this->notes );       
        	$branch->set( "location", $this->location );       

		$this->branch_id = $branch->add_branch();
     
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
		if( !isset( $this->customer_id ) )
			throw new Exception( "Customer ID must be set", KSF_FIELD_NOT_SET );
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
	/**//*******************************************************************
	* Display a list of customers (accounts) in a drop down list
	*
	*	native includes/ui/ui_lists.inc
	*	Should be calling debtors_master for the list...
	*		function customer_list($name, $selected_id=null, $spec_option=false, $submit_on_change=false,
	*		        $show_inactive=false, $editkey = false)

	*
	* @param string var label
	* @returns string HTML drop down list string
	*************************************************************************/
	function customer_list_dropdown( $varname )
	{
		require_once( $path_to_root . "/includes/ui/ui_lists.inc" );
		$html =  customer_list( $varname, null, false, true);
		return $html;
	}
	/**//*******************************************************************
	* Ask if a customer has branches
	*
	*	native  customer_list("partnerId_$tid", null, false, true);
	*	Should be calling debtors_master/_branches for the list...
	*
	* @param int the debtor_no
	* @returns bool does the cust have branch
	*************************************************************************/
	function customer_has_branches( $debtor_no )
	{
		$hasBranch = db_customer_has_branches( $debtor_no )
		return $hasBranch;
	}
	/**//*******************************************************************
	* Display a list of customer branches in a drop down list
	*
	*	native includes/ui/ui_lists.inc
	*	Should be calling debtors_master for the list...
	*		function customer_branches_list($customer_id, $name, $selected_id=null,
	*		        $spec_option = true, $enabled=true, $submit_on_change=false, $editkey = false)
	*
	* @param int debtor_no
	* @param string index name for DD
	* @returns string HTML drop down list string
	*************************************************************************/
	function customer_branches_dropdown( $debtor_no, $varname )
	{
		require_once( $path_to_root . "/includes/ui/ui_lists.inc" );
		$html = customer_branches_list($debtor_no, $varname . "_" . $debtor_no, null, false, true, true);
		return $html;
	}
	/****************************************************************************
	 ******************** Import PAYPAL *****************************************
	 ****************************************************************************
	 *function write_customer($email, $name, $company, $address, $phone, $fax, $currency) {
	 *
	 *    global $paypal_sales_type_id, $paypal_tax_group_id, $paypal_salesman, $paypal_area,
	 *        $paypal_location, $paypal_credit_status, $paypal_shipper;
	 *    global $SysPrefs;
	 *
	 *    log_message("Memory, write_customer start:".memory_get_usage());
	 *    $customer_id = find_customer_by_email($email);
	 *    if (empty($customer_id)) {
	 *        $customer_id = find_customer_by_name($company);
	 *    }
	 *    if (empty($customer_id)) {
	 *        //it is a new customer
	 *        begin_transaction();
	 *        add_customer($company, substr($company,0,30), $address,
	 *            '', $currency, 0, 0,
	 *            $paypal_credit_status, -1,
	 *            0, 0,
	 *            $SysPrefs->default_credit_limit(),
	 *            $paypal_sales_type_id, 'PayPal');
	 *
	 *        $customer_id = db_insert_id();
	 *
	 *        add_branch($customer_id, $company, substr($company,0,30),
	 *            $address, $paypal_salesman, $paypal_area, $paypal_tax_group_id, '',
	 *            get_company_pref('default_sales_discount_act'), get_company_pref('debtors_act'),
	 *            get_company_pref('default_prompt_payment_act'),
	 *            $paypal_location, $address, 0, 0,
	 *            $paypal_shipper, 'PayPal');
	 *
	 *        $selected_branch = db_insert_id();
	 *
	 *        $nameparts = explode(" ", $name);
	 *        $firstname = "";
	 *        for ($i=0; $i<(count($nameparts) - 1); $i++) {
	 *            if (!empty($firstname)) {
	 *                $firstname .= " ";
	 *            }
	 *            $firstname .= $nameparts[$i];
	 *        }
	 *        $lastname = $nameparts[count($nameparts)-1];
	 *        add_crm_person('paypal', $firstname, $lastname, $address,
	 *            $phone, '', $fax, $email, '', '');
	 *
	 *        add_crm_contact('customer', 'general', $selected_branch, db_insert_id());
	 *
	 *        commit_transaction();
	 *    }
	 *    else {
	 *        $selected_branch = 0;
	 *    }
	 *    log_message("Memory, write_customer end:".memory_get_usage());
	 *    return array($customer_id, $selected_branch);
	 *}
	 ****************************************************************************/

}

?>
