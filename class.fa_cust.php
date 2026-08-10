<?php

$path_to_root="../..";

require_once( 'class.fa_customer.php' ); 


/********************************************************//**
 * Various modules need to be able to add or get info about customers from FA
 *
 *	This class uses FA specific routines (display_notification etc)
 *
 * **********************************************************/
class common_cust extends fa_customer
{
	//fa_crm_persons
	protected $id;
	protected $ref;
	protected $name;	//<@ Also in debtors_master
	protected $name2;
	//protected $address; 	//<@ Also in debtors_master
					//<@ inherited from common_customer
	//protected $phone;		//<@ inherited from common_customer
	//protected $phone2;		//<@ inherited from common_customer
	//protected $fax;		//<@ inherited from common_customer
	//protected $email;		//<@ inherited from common_customer
	protected $lang;
	//protected $notes;	//<@ Also in debtors_master, cust_branch
					//<@ inherited from common_customer
	//protected $inactive;	//<@ Also in debtors_master
					//<@ inherited from common_customer
	
	//fa_debtors_master;
	//protected $debtor_no;	//also in cust_branch
					//<@ inherited from common_customer
	//protected $name;	//<@ Also in CRM_PERSONS
	//protected $address;	//<@ Also in CRM_PERSONS
	//protected $tax_id;		//<@ inherited from common_customer
	//protected $curr_code;		//<@ inherited from common_customer
	//protected $sales_type;	//<@ inherited from common_customer
	//protected $dimension_id;	//<@ inherited from common_customer
	//protected $dimension2_id;	//<@ inherited from common_customer
	//protected $credit_status;	//<@ inherited from common_customer
	//protected $payment_terms;	//<@ inherited from common_customer
	//protected $discount;		//<@ inherited from common_customer
	//protected $pymy_discout;	//<@ inherited from common_customer
	//protected $credit_limit;	//<@ inherited from common_customer
	//protected $notes;	//<@ Also in CRM_PERSON, cust_branch
	//protected $inactive;	//<@ Also in CRM_PERSON
					//<@ inherited from common_customer
	protected $debtor_ref;
	
	//fa_cust_branch;
	protected $branch_code;
	//protected $debtor_no;	//<@ Also in DEBTORS_MASTER
	protected $br_name;
	protected $br_address;
	//protected $area;		//<@ inherited from common_customer
	protected $salesman;
	protected $contact_name;
	protected $default_location;
	//protected $tax_group_id;		//<@ inherited from common_customer
	//protected $sales_account;		//<@ inherited from common_customer
	//protected $sales_discount_account;	//<@ inherited from common_customer
	//protected $receivables_account;	//<@ inherited from common_customer
	//protected $payment_discount_account;	//<@ inherited from common_customer
	//protected $default_ship_via;		//<@ inherited from common_customer
	protected $disable_trans;
	protected $br_post_address;
	protected $group_no;
	//protected $notes;	//<@ Also in CRM_PERSON, debtors_master
	protected $inactivity;
	protected $branch_ref;

	var $selected_branch;	//A branch ID from an insert in a previous step
	var $crm_person;	//An ID from an insert in a previous step
	var $min_cid;
	var $max_cid;
	var $errors = array();
	var $warnings = array();

	//WOO_Commerce
	//var $id;	//	integer 	Unique identifier for the resource.  read-only
	var $woo_id;
	var $date_created;	//	date-time 	The date the customer was created, in the site’s timezone.  read-only
	var $date_modified;	//	date-time 	The date the customer was last modified, in the site’s timezone.  read-only
	//var $email;	//	string 	The email address for the customer.
	//var $first_name;	//	string 	Customer first name.
	//var $last_name;	//	string 	Customer last name.
	var $username;	//	string 	Customer login name. Can be generated automatically from the customer’s email address if the option woocommerce_registration_generate_username is equal to yes
	var $role;
	var $password;	//	string 	Customer password. Can be generated automatically with wp_generate_password() if the “Automatically generate customer password” option is enabled, check the index meta for generate_password write-only
	var $last_order_id;	//string
	var $last_order_date;	//datestamp
/**/	var $last_order;	//	array 	Last order data. See Customer Last Order properties.  read-only
	var $orders_count;	//	integer 	Quantity of orders made by the customer.  read-only
	var $total_spent;	//	string 	Total amount spent.  read-only
	var $avatar_url;	//	string 	Avatar URL.
	var $billing_address;	//	array 	List of billing address data. See Billing Address properties.
	var $shipping_address;	//	array 	List of shipping address data. See Shipping Address properties.

	function __construct( $prefs_db )
	{
		//parent::__construct( $prefs_db );
		parent::__construct();
	}
	/*@bool@*/function validate()
	{
		if( ! $this->validate_data() )
			return FALSE;

		if( !isset( $this->ship_via ) ) 
		{
			$this->warnings[] = "Ship Via Not Set";
		}

		if( !isset( $this->default_location ) ) 
		{
			$this->warnings[] = "Fulfill from  Not Set";
		}

		if( !isset( $this->tax_group_id ) ) 
		{
			$this->warnings[] = "Tax Group Not Set";
		}

		if( !isset( $this->area ) ) 
		{
			$this->warnings[] = "Area Code Not Set";
		}

		if( !isset( $this->salesman ) ) 
		{
			$this->warnings[] = "Salesman Not Set";
		}

		if( !isset( $this->payment_terms ) ) 
		{
			$this->errors[] = "Payment Terms Not Set";
		}

		if( !isset( $this->sales_type ) ) 
		{
			$this->warnings[] = "Sales Type Not Set";
		}

		if( !isset( $this->credit_limit ) ) 
		{
			$this->warnings[] = "Credit Limit Not Set";
		}

		if( !isset( $this->credit_status ) ) 
		{
			$this->warnings[] = "Credit Status Not Set";
		}

		if( !isset( $this->dimension2_id ) ) 
		{
			$this->warnings[] = "Dimension2 Not Set";
		}

		if( !isset( $this->dimension_id ) ) 
		{
			$this->warnings[] = "Dimension1 Not Set";
		}

		if( !isset( $this->curr_code ) ) 
		{
			$this->errors[] = "Curr_code Not Set";
		}

		if( !isset( $this->tax_id ) ) 
		{
			$this->errors[] = "Tax_id Not Set";
		}
		if( !isset( $this->email ) ) 
		{
			$this->errors[] = "Email Not Set";
		}

		if( !isset( $this->fax ) ) 
		{
			$this->warnings[] = "Fax Not Set";
		}

		if( !isset( $this->phone2 ) ) 
		{
			$this->warnings[] = "Phone2 Not Set";
		}

		if( !isset( $this->phone ) ) 
		{
			$this->errors[] = "Phone Not Set";
		}

		if( !isset( $this->address ) ) 
		{
			$this->errors[] = "Address Not Set";
		}

		if( !isset( $this->debtor_ref ) ) 
		{
			$this->warnings[] = "Cust_ref Not Set";
		}

		if( !isset( $this->name ) ) 
		{
			$this->errors[] = "Name Not Set";
		}

		if( count( $this->errors ) > 0 )
			return FALSE;
		else
			return TRUE;
	}
	function insert_customer()
	{
  		$sql = "SELECT debtor_no FROM ".TB_PREF."debtors_master WHERE name=" . db_escape($this->name);
                $result = db_query($sql,"customer could not be retrieved meaning it needs to be inserted.");
                $row = db_fetch_assoc($result);

                if (!$row) {
                        $result = $this->insert_all();
                        if( $result )
                        {
				return 1;	//insert succeeded
                        }
                        else
                        {
				return -1;	//insert failed
                        }
                } else {
                    return 0;       //customer ignored (duplicate)
                }

	}
	function insert_all()
	{
		/********************************************************
		 *
		 *	FA does the following:
		 *		data validation
		 *		add_customer
		 *		add_branch
		 *		add_crm_person
		 *		add_crm_contact (cust_branch)
		 *		add_crm_contact (customer)
		 *
		 ********************************************************/
		if( $this->validate() )
		{
			$this->insert_debtor();
			$this->insert_branch();
			$this->insert_crm_persons_person();
			return TRUE;
		}
		else
		{
			display_notification("Error: " . $this->errors[0]);
			return FALSE;
		}
	}
	//common_customer.add_crm_person + add_crm_contact called from add_new_customer
	function insert_crm_persons_person()
	{
		//Each individual customer will have 1 branch (themselves)
		//Each multi-branch customer has 1 branch (HQ) plus 1 branch for each branch entity
		$this->CustName = $this->name;
		$this->cust_ref = $this->debtor_ref;
		$this->add_new_customer();
	}
	//common_customer.add_customer
	function insert_debtor()
	{
		$this->add_customer();
                $this->debtor_no =  $this->customer_id;
	}
	//common_customer.add_branch
	function insert_branch()
	{
		$this->add_branch();
                $this->selected_branch = $this->branch_id;
	}
	function insert_contacts()
	{
	}
	function update_debtor()
	{
	}
	//common_customer.add_new_customer
	function create_new_customer()
	{
		$this->add_new_customer();
	}
}

/*******************************************************************//**
 * This class will cross reference external CRM and FA customer
 *
 * *******************************************************************/
class crm_cust_xref 
{
	protected suitecrm_contact_id;
	protected suitecrm_account_id;
	protected common_cust;		//<@ Object
	protected suitecrm_contact;	//<@ Object
	protected suitecrm_account;	//<@ Object
	function __construct($crm_url, $crm_username, $crm_password)
	{
		$this->common_cust = new common_cust( "common_cust_prefs" );
        	$this->common_cust->set_var( "min_cid", 0);
        	$this->common_cust->set_var( "max_cid", 0);
		$this->common_cust->set_var( "payment_terms", 5 );
		$this->common_cust->set_var( "credit_status", 1 );
		$this->common_cust->set_var( "tax_group_id", 3 );
		$this->common_cust->set_var( "tax_id", "" );
		$this->suitecrm_contact = new suitecrm_contact( $crm_url, $crm_username, $crm_password);
	}
}


	protected $debtor_no;	//also in cust_branch
	protected $discount;
	protected $pymy_discout;
	protected $branch_code;
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
	
$data_dictionary = array();
$data_dictionary["id"]["fa"]["field"] = null;		//Both us ID, but I suspect we shouldn't be trying to send it...
$data_dictionary["id"]["crm"]["field"] = null;		//but use it for cross reference purposes
$data_dictionary["id"]["woo"]["field"] = null;

$data_dictionary["branch_name"]["fa"]["field"] = "br_name";
$data_dictionary["branch_name"]["crm"]["field"] = "";
$data_dictionary["branch_name"]["woo"]["field"] = null;

$data_dictionary["branch_address"]["fa"]["field"] = "br_address";
$data_dictionary["branch_address"]["crm"]["field"] = "";
$data_dictionary["branch_address"]["woo"]["field"] = null;

$data_dictionary["branch_post_address"]["fa"]["field"] = "br_post_address";
$data_dictionary["branch_post_address"]["crm"]["field"] = "";
$data_dictionary["branch_post_address"]["woo"]["field"] = null;

//name and address in FA are multiple fields in Suite
$data_dictionary["tax_rate_code"]["fa"]["field"] = "tax_id";		
$data_dictionary["tax_rate_code"]["crm"]["field"] = "";	
$data_dictionary["tax_rate_code"]["woo"]["field"] = null;

$data_dictionary["currency_code"]["fa"]["field"] = "curr_code";		
$data_dictionary["currency_code"]["crm"]["field"] = "";	
$data_dictionary["currency_code"]["woo"]["field"] = null;

$data_dictionary["credit_limit"]["fa"]["field"] = "credit_limit";		
$data_dictionary["credit_limit"]["crm"]["field"] = "";	
$data_dictionary["credit_limit"]["woo"]["field"] = null;

$data_dictionary["credit_status"]["fa"]["field"] = "credit_status";		
$data_dictionary["credit_status"]["crm"]["field"] = "";	
$data_dictionary["credit_status"]["woo"]["field"] = null;

$data_dictionary["payment_terms"]["fa"]["field"] = "payment_terms";		
$data_dictionary["payment_terms"]["crm"]["field"] = "";	
$data_dictionary["payment_terms"]["woo"]["field"] = null;

$data_dictionary["price_book"]["fa"]["field"] = "sales_type";		
$data_dictionary["price_book"]["crm"]["field"] = "";	
$data_dictionary["price_book"]["woo"]["field"] = null;

$data_dictionary["dimension_id"]["fa"]["field"] = "diimension_id";		
$data_dictionary["dimension_id"]["crm"]["field"] = "";	
$data_dictionary["dimension_id"]["woo"]["field"] = null;

$data_dictionary["dimension2_id"]["fa"]["field"] = "diimension2_id";		
$data_dictionary["dimension2_id"]["crm"]["field"] = "";	
$data_dictionary["dimension2_id"]["woo"]["field"] = null;

$data_dictionary["short_name"]["fa"]["field"] = "name2";		
$data_dictionary["short_name"]["crm"]["field"] = "";	
$data_dictionary["short_name"]["woo"]["field"] = null;

$data_dictionary["reference"]["fa"]["field"] = "ref";		
$data_dictionary["reference"]["crm"]["field"] = "";	
$data_dictionary["reference"]["woo"]["field"] = null;

$data_dictionary["status"]["fa"]["field"] = "inactive";		
$data_dictionary["status"]["crm"]["field"] = "";	
$data_dictionary["status"]["woo"]["field"] = null;

$data_dictionary["primary_language"]["fa"]["field"] = "lang";
$data_dictionary["primary_language"]["crm"]["field"] = "";
$data_dictionary["primary_language"]["woo"]["field"] = null;

$data_dictionary["debtor_ref"]["fa"]["field"] = "debtor_ref";
$data_dictionary["debtor_ref"]["crm"]["field"] = "";
$data_dictionary["debtor_ref"]["woo"]["field"] = null;

$data_dictionary["inactivity"]["fa"]["field"] = "inactivity";
$data_dictionary["inactivity"]["crm"]["field"] = "";
$data_dictionary["inactivity"]["woo"]["field"] = null;

$data_dictionary["group_no"]["fa"]["field"] = "group_no";
$data_dictionary["group_no"]["crm"]["field"] = "";
$data_dictionary["group_no"]["woo"]["field"] = null;

$data_dictionary["branch_ref"]["fa"]["field"] = "branch_ref";
$data_dictionary["branch_ref"]["crm"]["field"] = "";
$data_dictionary["branch_ref"]["woo"]["field"] = null;

$data_dictionary["created"]["fa"]["field"] = "";
$data_dictionary["created"]["crm"]["field"] = "date_entered";
$data_dictionary["created"]["woo"]["field"] = null;

$data_dictionary["modified"]["fa"]["field"] = "";
$data_dictionary["modified"]["crm"]["field"] = "date_modified";
$data_dictionary["modified"]["woo"]["field"] = null;

$data_dictionary["description"]["fa"]["field"] = "notes";
$data_dictionary["description"]["crm"]["field"] = "description";
$data_dictionary["description"]["woo"]["field"] = null;

$data_dictionary["salutation"]["fa"]["field"] = "";
$data_dictionary["salutation"]["crm"]["field"] = "salutation";
$data_dictionary["salutation"]["woo"]["field"] = null;

$data_dictionary["first_name"]["fa"]["field"] = "";
$data_dictionary["first_name"]["crm"]["field"] = "first_name";
$data_dictionary["first_name"]["woo"]["field"] = "first_name";

$data_dictionary["last_name"]["fa"]["field"] = "";
$data_dictionary["last_name"]["crm"]["field"] = "last_name";
$data_dictionary["last_name"]["woo"]["field"] = "last_name";

$data_dictionary["title"]["fa"]["field"] = "";
$data_dictionary["title"]["crm"]["field"] = "title";
$data_dictionary["title"]["woo"]["field"] = null;

$data_dictionary["department"]["fa"]["field"] = "";
$data_dictionary["department"]["crm"]["field"] = "department";
$data_dictionary["department"]["woo"]["field"] = null;


$data_dictionary["home_phone"]["fa"]["field"] = "phone";
$data_dictionary["home_phone"]["crm"]["field"] = "phone_home";
$data_dictionary["id"]["woo"]["field"] = null;

$data_dictionary["work_phone"]["fa"]["field"] = "";
$data_dictionary["work_phone"]["crm"]["field"] = "phone_work";
$data_dictionary["work_phone"]["woo"]["field"] = null;

$data_dictionary["cell_phone"]["fa"]["field"] = "phone2";
$data_dictionary["cell_phone"]["crm"]["field"] = "phone_mobile";
$data_dictionary["cell_phone"]["woo"]["field"] = null;

$data_dictionary["other_phone"]["fa"]["field"] = "";
$data_dictionary["other_phone"]["crm"]["field"] = "phone_other";
$data_dictionary["other_phone"]["woo"]["field"] = null;

$data_dictionary["fax_phone"]["fa"]["field"] = "fax";
$data_dictionary["fax_phone"]["crm"]["field"] = "phone_fax";
$data_dictionary["fax_phone"]["woo"]["field"] = null;

$data_dictionary["email"]["fa"]["field"] = "email";
$data_dictionary["email"]["crm"]["field"] = "email1";
$data_dictionary["email"]["woo"]["field"] = "email";

$data_dictionary["primary_address_street"]["fa"]["field"] = "";
$data_dictionary["primary_address_street"]["crm"]["field"] = "primary_address_street";
$data_dictionary["primary_address_street"]["woo"]["field"] = null;

$data_dictionary["primary_address_city"]["fa"]["field"] = "";
$data_dictionary["primary_address_city"]["crm"]["field"] = "primary_address_city";
$data_dictionary["primary_address_city"]["woo"]["field"] = null;

$data_dictionary["primary_address_state"]["fa"]["field"] = "";
$data_dictionary["primary_address_state"]["crm"]["field"] = "primary_address_state";
$data_dictionary["primary_address_state"]["woo"]["field"] = null;

$data_dictionary["primary_address_country"]["fa"]["field"] = "";
$data_dictionary["primary_address_country"]["crm"]["field"] = "primary_address_country";
$data_dictionary["primary_address_country"]["woo"]["field"] = null;

$data_dictionary["primary_address_postalcode"]["fa"]["field"] = "";
$data_dictionary["primary_address_postalcode"]["crm"]["field"] = "primary_address_postal";
$data_dictionary["primary_address_postalcode"]["woo"]["field"] = null;

$data_dictionary["secondary_address_street"]["fa"]["field"] = "";
$data_dictionary["secondary_address_street"]["crm"]["field"] = "alt_address_street";
$data_dictionary["secondary_address_street"]["woo"]["field"] = null;

$data_dictionary["secondary_address_city"]["fa"]["field"] = "";
$data_dictionary["secondary_address_city"]["crm"]["field"] = "alt_address_city";
$data_dictionary["secondary_address_city"]["woo"]["field"] = null;

$data_dictionary["secondary_address_state"]["fa"]["field"] = "";
$data_dictionary["secondary_address_state"]["crm"]["field"] = "alt_address_state";
$data_dictionary["secondary_address_state"]["woo"]["field"] = null;

$data_dictionary["secondary_address_country"]["fa"]["field"] = "";
$data_dictionary["secondary_address_country"]["crm"]["field"] = "alt_address_country";
$data_dictionary["secondary_address_country"]["woo"]["field"] = null;

$data_dictionary["secondary_address_postalcode"]["fa"]["field"] = "";
$data_dictionary["secondary_address_postalcode"]["crm"]["field"] = "alt_address_postal";
$data_dictionary["secondary_address_postalcode"]["woo"]["field"] = null;


$data_dictionary["assistant_phone"]["fa"]["field"] = "";
$data_dictionary["assistant_phone"]["crm"]["field"] = "assistant_other";
$data_dictionary["assistant_phone"]["woo"]["field"] = null;

$data_dictionary["assistant"]["fa"]["field"] = "";
$data_dictionary["assistant"]["crm"]["field"] = "assistant";
$data_dictionary["assistant"]["woo"]["field"] = null;

$data_dictionary["lead_source"]["fa"]["field"] = "";
$data_dictionary["lead_source"]["crm"]["field"] = "lead_source";
$data_dictionary["lead_source"]["woo"]["field"] = null;

$data_dictionary["birthday"]["fa"]["field"] = "";
$data_dictionary["birthday"]["crm"]["field"] = "birthday";
$data_dictionary["birthday"]["woo"]["field"] = null;

$data_dictionary["portal_account"]["fa"]["field"] = "";
$data_dictionary["portal_account"]["crm"]["field"] = "joomla_account_id";
$data_dictionary["portal_account"]["woo"]["field"] = "username";

$data_dictionary["portal_account_type"]["fa"]["field"] = "";
$data_dictionary["portal_account_type"]["crm"]["field"] = "portal_user_type";
$data_dictionary["portal_account_type"]["woo"]["field"] = null;

$data_dictionary["billing_address_street"]["fa"]["field"] = "";
$data_dictionary["billing_address_street"]["crm"]["field"] = "primary_address_street";
$data_dictionary["billing_address_street"]["woo"]["field"] = "address_1";

$data_dictionary["billing_address_city"]["fa"]["field"] = "";
$data_dictionary["billing_address_city"]["crm"]["field"] = "primary_address_city";
$data_dictionary["billing_address_city"]["woo"]["field"] = "city";

$data_dictionary["billing_address_state"]["fa"]["field"] = "";
$data_dictionary["billing_address_state"]["crm"]["field"] = "primary_address_state";
$data_dictionary["billing_address_state"]["woo"]["field"] = "state";

$data_dictionary["billing_address_country"]["fa"]["field"] = "";
$data_dictionary["billing_address_country"]["crm"]["field"] = "primary_address_country";
$data_dictionary["billing_address_country"]["woo"]["field"] = "country";

$data_dictionary["billing_address_postalcode"]["fa"]["field"] = "";
$data_dictionary["billing_address_postalcode"]["crm"]["field"] = "primary_address_postal";
$data_dictionary["billing_address_postalcode"]["woo"]["field"] = "postcode";

$data_dictionary["shipping_address_street"]["fa"]["field"] = "";
$data_dictionary["shipping_address_street"]["crm"]["field"] = "primary_address_street";
$data_dictionary["shipping_address_street"]["woo"]["field"] = "address_1";

$data_dictionary["shipping_address_city"]["fa"]["field"] = "";
$data_dictionary["shipping_address_city"]["crm"]["field"] = "primary_address_city";
$data_dictionary["shipping_address_city"]["woo"]["field"] = "city";

$data_dictionary["shipping_address_state"]["fa"]["field"] = "";
$data_dictionary["shipping_address_state"]["crm"]["field"] = "primary_address_state";
$data_dictionary["shipping_address_state"]["woo"]["field"] = "state";

$data_dictionary["shipping_address_country"]["fa"]["field"] = "";
$data_dictionary["shipping_address_country"]["crm"]["field"] = "primary_address_country";
$data_dictionary["shipping_address_country"]["woo"]["field"] = "country";

$data_dictionary["shipping_address_postalcode"]["fa"]["field"] = "";
$data_dictionary["shipping_address_postalcode"]["crm"]["field"] = "primary_address_postal";
$data_dictionary["shipping_address_postalcode"]["woo"]["field"] = "postcode";

class fa_cust extends common_cust
{
}
class vtiger_cust extends common_cust
{
	function __construct()
	{
        	$this->min_cid = 0;
        	$this->max_cid = 0;
		//parent::__construct( $host, $user, $pass, $database, NULL );
		parent::__construct( "common_cust_prefs" );
		//These should now be pref variables...
		$this->set_var( "payment_terms", 5 );
		$this->set_var( "credit_status", 1 );
		$this->set_var( "tax_group_id", 3 );
		$this->set_var( "tax_id", "" );
	}

}

?>
