<?php

$path_to_root="../..";
//require_once( 'class.fa_origin.php' );
require_once( 'class.table_interface.php' );

/********************************************************//**
 * Various modules need to be able to add or get info about customers from FA
 *
 *	This class uses FA specific routines (display_notification etc)
 *
 *	STUB file to help use suppliers 
 *
 * **********************************************************/
class fa_suppliers extends table_interface
{
	/*
| Field                    | Type         | Null | Key | Default | Extra |
+--------------------+--------------+------+-----+---------+-------+
| supplier_id              | int(11)      | NO   | PRI | NULL    | auto_increment |
| supp_name                | varchar(60)  | NO   |     |         |                |
| address                  | tinytext     | NO   |     | NULL    |                |
| supp_address             | tinytext     | NO   |     | NULL    |                |
| gst_no                   | varchar(25)  | NO   |     |         |                |
| contact                  | varchar(60)  | NO   |     |         |                |
| supp_account_no          | varchar(40)  | NO   |     |         |                |
| website                  | varchar(100) | NO   |     |         |                |
| bank_account             | varchar(60)  | NO   |     |         |                |
| curr_code                | char(3)      | YES  |     | NULL    |                |
| payment_terms            | int(11)      | YES  |     | NULL    |                |
| tax_included             | tinyint(1)   | NO   |     | 0       |                |
| dimension_id             | int(11)      | YES  |     | 0       |                |
| dimension2_id            | int(11)      | YES  |     | 0       |                |
| tax_group_id             | int(11)      | YES  |     | NULL    |                |
| credit_limit             | double       | NO   |     | 0       |                |
| purchase_account         | varchar(15)  | NO   |     |         |                |
| payable_account          | varchar(15)  | NO   |     |         |                |
| payment_discount_account | varchar(15)  | NO   |     |         |                |
| notes                    | tinytext     | NO   |     | NULL    |                |
| inactive                 | tinyint(1)   | NO   |     | 0       |                |
| supp_ref                 | varchar(30)  | NO   | MUL | NULL    |                |
	 *
	 * */
	protected $supplier_id              ;// int(11)      | NO   | PRI | NULL    | auto_increment |
	protected $supp_name                ;// varchar(60)  | NO   |     |         |                |
	protected $address                  ;// tinytext     | NO   |     | NULL    |                |
	protected $supp_address             ;// tinytext     | NO   |     | NULL    |                |
	protected $gst_no                   ;// varchar(25)  | NO   |     |         |                |
	protected $contact                  ;// varchar(60)  | NO   |     |         |                |
	protected $supp_account_no          ;// varchar(40)  | NO   |     |         |                |
	protected $website                  ;// varchar(100) | NO   |     |         |                |
	protected $bank_account             ;// varchar(60)  | NO   |     |         |                |
	protected $curr_code                ;// char(3)      | YES  |     | NULL    |                |
	protected $payment_terms            ;// int(11)      | YES  |     | NULL    |                |
	protected $tax_included             ;// tinyint(1)   | NO   |     | 0       |                |
	protected $dimension_id             ;// int(11)      | YES  |     | 0       |                |
	protected $dimension2_id            ;// int(11)      | YES  |     | 0       |                |
	protected $tax_group_id             ;// int(11)      | YES  |     | NULL    |                |
	protected $credit_limit             ;// double       | NO   |     | 0       |                |
	protected $purchase_account         ;// varchar(15)  | NO   |     |         |                |
	protected $payable_account          ;// varchar(15)  | NO   |     |         |                |
	protected $payment_discount_account ;// varchar(15)  | NO   |     |         |                |
	protected $notes                    ;// tinytext     | NO   |     | NULL    |                |
	protected $inactive                 ;// tinyint(1)   | NO   |     | 0       |                |
	protected $supp_ref                 ;// varchar(30)  | NO   | MUL | NULL    |                |
	protected $supplier_array;
	protected $supplier_id_array;
	function __construct( $prefs_db = null )
	{
		//parent::__construct( $prefs_db );
		parent::__construct();
		$this->table_details['tablename'] = TB_PREF . 'suppliers';
		$this->fields_array[] = array( 'name' => 'supplier_id', 'type' => 'int(11)', 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '', 'auto_increment' => 'yes' );
		$this->fields_array[] = array( 'name' => 'supp_name', 'type' => 'varchar(60)', 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'address', 'type' => 'tinytext ', 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'supp_address', 'type' => 'tinytext ', 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '');
		$this->fields_array[] = array( 'name' => 'gst_no', 'type' => 'varchar(25)', 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'contact', 'type' => 'varchar(60)', 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '');
		$this->fields_array[] = array( 'name' => 'supp_account_no', 'type' => 'varchar(40)', 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'website', 'type' => 'varchar(100) ', 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'bank_account', 'type' => 'varchar(60)', 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'curr_code', 'type' => 'char(3)', 'null' => 'NULL', 'readwrite' => 'readwrite', 'default' => '');
		$this->fields_array[] = array( 'name' => 'payment_terms', 'type' => 'int(11)', 'null' => 'NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'tax_included', 'type' => 'tinyint(1) ', 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '0' );
		$this->fields_array[] = array( 'name' => 'dimension_id', 'type' => 'int(11)', 'null' => 'NULL', 'readwrite' => 'readwrite', 'default' => '0' );
		$this->fields_array[] = array( 'name' => 'dimension2_id', 'type' => 'int(11)', 'null' => 'NULL', 'readwrite' => 'readwrite', 'default' => '0' );
		$this->fields_array[] = array( 'name' => 'tax_group_id', 'type' => 'int(11)', 'null' => 'NULL', 'readwrite' => 'readwrite', 'default' => '');
		$this->fields_array[] = array( 'name' => 'credit_limit', 'type' => 'double ', 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '0');
		$this->fields_array[] = array( 'name' => 'purchase_account ', 'type' => 'varchar(15)', 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'payable_account', 'type' => 'varchar(15)', 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'payment_discount_account ', 'type' => 'varchar(15)', 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'notes', 'type' => 'tinytext ', 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => 'NULL' );
		$this->fields_array[] = array( 'name' => 'inactive', 'type' => 'tinyint(1) ', 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '0');
		$this->fields_array[] = array( 'name' => 'supp_ref', 'type' => 'varchar(30)', 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => 'NULL');
		$this->table_details['primarykey'] = "supplier_id";
		//$this->table_details['key'] = "supp_ref";

		$this->init_values();
	}
	function init_values()
	{
           	$this->supp_name = '';
           	$this->supp_ref  = '';
           	$this->address  = '';
           	$this->supp_address  = '';
           	$this->tax_group_id = '';
           	$this->website  = '';
           	$this->supp_account_no  = '';
           	$this->notes = '';
                $this->dimension_id = 0;
                $this->dimension2_id = 0;
                $this->tax_included = 0;
                $this->sales_type = -1;
                $this->gst_no = '';
                $this->bank_account = '';
                $this->payment_terms  = '';
                $this->credit_limit = 0;

		if( function_exists( "get_company_prefs" ) && is_callable( "get_company_prefs" ) )
		{
                	$company_record = get_company_prefs();
                	$this->curr_code  = $company_record["curr_default"];
                	$this->payable_account = $company_record["creditors_act"];
                	$this->purchase_account = ''; // default/item's cogs account
                	$this->payment_discount_account = $company_record['pyt_discount_act'];
		}
		else
		{
                	$this->curr_code  = "CAD";
                	$this->payable_account = "2100";
                	$this->purchase_account = ''; // default/item's cogs account
                	$this->payment_discount_account = "5060";
		}

	}
	function set( $field, $value = null, $enforce = false )
	{
		//Should call parent::set but not setting :(
		$this->$field = $value;
		if( $value !== $this->$field )
		{
			throw new Excpetion( "Set failed miserably! : $field::$value:::$this->$field" );
		}
		else
		{
			//display_notification( __LINE__ . ":: Successfully set $field as: " . print_r( $this->$field, true ) );
		}
	}
 	function insert()
	{
		$this->insert_table();
	}
	function update()
	{
		$this->update_table();
	}
	/**//****************************************************
	 * Select the row matching our ID and set our values
	 *
	 * Updated 20240221
	 *
	 * @param none Expects ID to be set
	 * @return none
	 *******************************************************/
	function getById()
	{
		$ret = $this->getByPrimaryKey();
		return;
		//getByPrimaryKey doesn't return anything at this time.
		//return $ret;
	}
	function getAll( $active_only = true )
	{
		$sql = "select * from " . TB_PREF . "suppliers";
		if( $active_only )
			$sql .= " where inactive='0'";
		$result = db_query( $sql , "Couldn't select supplier_ids" );
		while( ( $row = mysql_fetch_array( $result ) ) != null )
		{
			$this->supplier_array[] = $row;
			$this->supplier_id_array[] = $row["stock_id"];
		}
	}
	function getSupplier_IDs( $active_only = true )
	{
		$sql = "select supplier_id from " . TB_PREF . "suppliers";
		if( $active_only )
			$sql .= " where inactive='0'";
		
		$result = db_query( $sql , "Couldn't select supplier_ids" );
		while( ( $row = mysql_fetch_array( $result ) ) != null )
		{
			$this->supplier_id_array[] = $row["supplier_id"];
		}
	}
	function count_active()
	{
		$res = db_query( "select count(*) from " . TB_PREF . "suppliers where inactive='0'", "Couldn't count QOH" );
		$count = db_fetch_row( $res );
		$this->active_count = $count[0];
	}
	function display_active_count()
	{
		$this->count_active();
		display_notification( $this->active_count . " rows of active suppliers exist in suppliers.");
	}
	/*****************************************************************************************//**
	 * Display a form with a drop down list of Stock Items that meet a sub-query criteria
	 * 
	 * @param subquery Query to select stock_ids
	 * @param stock_IN is the stock_ids to display IN (TRUE) or NOT IN (FALSE) the results from the subquery
	 * @param instruction_title is the table section label for instructions to use this form
	 * @param instruction_rows is rows in the table with instructions on how to use this form
	 *
	 * Subquery similar to "select stock_id from " . $this->table_details['tablename'] . "where image_count='0'";
	 * @return NULL.  Displays a form
	 * *******************************************************************************************/
	function display_edit_list_form( $subquery, $stock_IN = true, $instruction_title = "", $instruction_rows = null )
	{
		throw new Exception( "This Function hasn't been coded for this class." );
		$sql = "select sm.stock_id, sm.description, c.description, sm.inactive, sm.editable 
				from " . TB_PREF . "suppliers sm, " . TB_PREF . "stock_category c
				where sm.category_id = c.category_id and sm.stock_id ";
		if( $stock_IN )
			$sql .= " IN ";
		else
			$sql .= " NOT IN ";
		$sql .= "( " . $subquery . " )";
		 global $all_items;
		$selected_id = "0";
		$name = "";
		$editkey = TRUE;
		$opts = array('cells'=>true, 'show_inactive'=>'1');
		$all_option = FALSE;
		$submit_on_change = TRUE;
                set_editor('item', $name, $editkey);
		start_form();
		start_table();
		table_section_title(_( $instruction_title ));
		foreach( $instruction_rows as $row )
		{
			label_row(_( $row ), NULL);
		}
		label_row("&nbsp;", NULL);
		label_row("Press F4 to pop open a window to edit the item details", null);
		table_section(1);
	        $ret = combo_input($name, $selected_id, $sql, 'stock_id', 'sm.description',
	        array_merge(
	          array(
	                'format' => '_format_stock_items',
	                'spec_option' => $all_option===true ?  _("All Items") : $all_option,
	                'spec_id' => $all_items,
			'search_box' => true,
	        	'search' => array("sm.stock_id", "c.description","sm.description"),
	                'search_submit' => get_company_pref('no_item_list')!=0,
	                'size'=>10,
	                'select_submit'=> $submit_on_change,
	                'category' => 2,
	                'order' => array('c.description','sm.stock_id')
	          ), $opts) );
			echo $ret;
		end_table();
		end_form();
		return NULL;
	}

	/**//****************************************************************************************
	* Insert a new supplier, like purchasing/manage/suppliers.php does
	*
	* @param bool check for existance of same name
	* @returns int supplier_id|0
	********************************************************************************************/
	function add_supplier( $check_existance = false )
	{
		//supp_ref is short name

                if ( $this->supplier_id )
                {
			throw new Exception( "Supplier ID is set.  Can't insert - must update!" );
                }
                else
                {
			if( ! isset( $this->supp_name ) || strlen( $this->supp_name ) < 3 )
			{	
				throw new Exception( "Supplier Name is a mandatory field" );
			}
			if( ! isset( $this->supp_ref ) )
			{	
				throw new Exception( "Supplier Short Name (supp_ref) is a mandatory field" );
			}
			if( strlen( $this->supp_ref ) < 2 )
			{	
				throw new Exception( "Supplier Short Name (supp_ref) is TOO SHORT." );
			}
			if( $check_existance )
			{
				//TODO: write this part
				//Select to see if the vendor already exists.
				//We have a scoring routing in the Bankk Import module
			}
                	begin_transaction();
                        add_supplier($this->supp_name, $this->supp_ref, $this->address, $this->supp_address,
                                $this->gst_no, $this->website, $this->supp_account_no, $this->bank_account,
                                $this->credit_limit, $this->dimension_id, $this->dimension2_id,
                                $this->curr_code, $this->payment_terms, $this->payable_account, $this->purchase_account,
                                $this->payment_discount_account, $this->notes, $this->tax_group_id, $this->tax_included );

                        $this->supplier_id = db_insert_id();
			//	display_notification( __LINE__ . " Supplier ID: $this->supplier_id " );

			$person_id = 0;
/* * /
				display_notification( __LINE__ . " Person ID $person_id " );
			$person = new fa_crm_person();
			$person->set( "ref", $this->supp_ref );
			$person->set( "name", $this->supp_ref );
			$person->set( "name2", $this->supp_name );
			$person->set( "address", $this->address );
			$person->set( "notes", $this->notes );
				display_notification( __LINE__ . " Person ID $person_id " );
			$person_id = $person->add_crm_person();
	
/**/
/* * /
				display_notification( __LINE__ . " Person ID $person_id " );

			$contact = new fa_crm_contacts();
			$contact->set( "type", "supplier" );
			$contact->set( "action", "general" );
			$contact->set( "entity_id", $supplier_id );
			$contact->set( "person_id", $person_id );
				display_notification( __LINE__ . " Person ID $person_id " );
			$contact_id = $contact->add_crm_contact();
				display_notification( __LINE__ );

			if( $contact_id > 0 )
			{
					display_notification( __LINE__ );
                		commit_transaction();
					display_notification( __LINE__ );
				return $this->supplier_id;
			}
			else
			{
					display_notification( __LINE__ );
				//failure somewhere
				return 0;
			}
/**/
			if( $this->supplier_id > 0 )
			{
                		commit_transaction();
				return $this->supplier_id;
			}
			else
			{
				//failure somewhere
				return 0;
			}
                }
	}

	/**//****************************************************************************************
	* Update a new supplier, like purchasing/manage/suppliers.php does
	*
	********************************************************************************************/
	function update_supplier()
	{
                if ($this->supplier_id)
                {
                	begin_transaction();
                        update_supplier($this->supplier_id, $this->supp_name, $this->supp_ref, $this->address,
                                $this->supp_address, $this->gst_no,
                                $this->website, $this->supp_account_no, $this->bank_account,
                                input_num('credit_limit', 0), $this->dimension_id, $this->dimension2_id, $this->curr_code,
                                $this->payment_terms, $this->payable_account, $this->purchase_account, $this->payment_discount_account,
                                $this->notes, $this->tax_group_id, get_post('tax_included', 0));
                        update_record_status($this->supplier_id, $this->inactive,
                                'suppliers', 'supplier_id');

                        $Ajax->activate('supplier_id'); // in case of status change
                        display_notification(_("Supplier has been updated."));
                	commit_transaction();
                }
                else
                {
			//Should have called add_supplier
			throw new Exception( "Supplier ID not set.  Can't update!" );
                }
	}
	/**//***********************************************************************************
	* Retrieve a supplier by supp_ref, like purchasing/manage/suppliers.php does
	*
	* @param string supplier short name        
	* @returns array list
	****************************************************************************************/
	function get_supplier_by_shortname( $name )
	{
		//display_notification( __LINE__  );
		$sql = "SELECT supplier_id, supp_name, supp_ref, curr_code, inactive FROM ".TB_PREF."suppliers ";
			$sql .= " WHERE supp_ref = '$name'";
			//var_dump( $sql );
		$result = db_query($sql, "Can't search for vendor" );
		//var_dump( $result );
		$suppliers = array();
		$count = 0;
		$shortnames = array();
                while ($row = db_fetch($result)) 
		{
			$this->supplier_id = $suppliers[$count]['supplier_id'] = $row['supplier_id'];
			$this->supplier_shortname = $suppliers[$count]['supplier_shortname'] = $row['supp_ref'];
			$this->supplier_curr_code = $suppliers[$count]['supplier_curr_code'] = $row['curr_code'];
			$this->supplier_name = $suppliers[$count]['supplier_name'] = $row['supp_name'];
			$this->supplier_inactive = $suppliers[$count]['inactive'] = $row['inactive'];
			$this->supplier_bank_account = $suppliers[$count]['bank_account'] = $row['bank_account'];
			$count++;
			//$shortnames[] = $row['supp_ref'];
		}
		//$suppliers['shortnames'] = $shortnames;
		//display_notification( __LINE__  );
		//display_notification( print_r( $suppliers, true )  );
		return $suppliers;
	}
	/**//***********************************************************************************
	* Retrieve a supplier by name, like purchasing/manage/suppliers.php does
	*
	* @param string supplier name        
	* @returns array list
	****************************************************************************************/
	function get_supplier_by_name( $name )
	{
		//display_notification( __LINE__  );
		$sql = "SELECT supplier_id, supp_name, supp_ref, curr_code, inactive FROM ".TB_PREF."suppliers ";
			$sql .= " WHERE supp_name = '$name'";
			//var_dump( $sql );
		$result = db_query($sql, "Can't search for vendor" );
		//var_dump( $result );
		$suppliers = array();
		$count = 0;
		$shortnames = array();
                while ($row = db_fetch($result)) 
		{
			$this->supplier_id = $suppliers[$count]['supplier_id'] = $row['supplier_id'];
			$this->supplier_shortname = $suppliers[$count]['supplier_shortname'] = $row['supp_ref'];
			$this->supplier_name = $suppliers[$count]['supplier_name'] = $row['supp_name'];
			$this->supplier_curr_code = $suppliers[$count]['supplier_curr_code'] = $row['curr_code'];
			$this->supplier_inactive = $suppliers[$count]['inactive'] = $row['inactive'];
			$this->supplier_bank_account = $suppliers[$count]['bank_account'] = $row['bank_account'];
			$count++;
			//$shortnames[] = $row['supp_ref'];
		}
		//$suppliers['shortnames'] = $shortnames;
		//display_notification( __LINE__  );
		//display_notification( print_r( $suppliers, true )  );
		return $suppliers;
	}
	/**//****************************************************************************************
	* Retrieve a supplier by supplier_id, like purchasing/manage/suppliers.php does
	*
	********************************************************************************************/
	function get_supplier()
	{
		if ($this->supplier_id)
	        {
	                //SupplierID exists - either passed when calling the form or from the form itself
	                $myrow = get_supplier($this->supplier_id);
	
	                $this->supp_name = $myrow["supp_name"];
	                $this->supp_ref = $myrow["supp_ref"];
	                $this->address  = $myrow["address"];
	                $this->supp_address  = $myrow["supp_address"];
	
	                $this->gst_no  = $myrow["gst_no"];
	                $this->website  = $myrow["website"];
	                $this->supp_account_no  = $myrow["supp_account_no"];
	                $this->bank_account  = $myrow["bank_account"];
	                $this->dimension_id  = $myrow["dimension_id"];
	                $this->dimension2_id  = $myrow["dimension2_id"];
	                $this->curr_code  = $myrow["curr_code"];
	                $this->payment_terms  = $myrow["payment_terms"];
	                $this->credit_limit  = price_format($myrow["credit_limit"]);
	                $this->tax_group_id = $myrow["tax_group_id"];
	                $this->tax_included = $myrow["tax_included"];
	                $this->payable_account  = $myrow["payable_account"];
	                $this->purchase_account  = $myrow["purchase_account"];
	                $this->payment_discount_account = $myrow["payment_discount_account"];
	                $this->notes  = $myrow["notes"];
	                $this->inactive = $myrow["inactive"];
	        }
	        else
	        {
			throw new Exception( "Supplier ID not set.  Can't lookup Supplier!" );
		}
	}
	function supplier_settings(&$supplier_id)
	{
		if( $supplier_id )
		{
			$this->set( "supplier_id", $supplier_id );
			$this->get_supplier();
		}
		else
		{
			$this->init_values();
		}
	}
	//supplier_list_cells grabs a list of suppliers
	/**//***********************************************************************************
	* Grab the list of suppliers
	*
	* @param bool should we include inactive
	* @returns array list
	****************************************************************************************/
	function supplier_list( $include_inactive = false )
	{
		//display_notification( __LINE__  );
		$sql = "SELECT * FROM ".TB_PREF."suppliers ";
		//$sql = "SELECT supplier_id, supp_ref, curr_code, inactive FROM ".TB_PREF."suppliers ";
		if( $include_inactive )
		{
			//$sql .= " WHERE inactive = true ";
		}
		else
		{
			$sql .= " WHERE inactive = false ";
		}
			//SQL for debugging purposes
			//$sql2 = "SELECT supplier_id, supp_ref, curr_code, inactive, bank_account FROM ".TB_PREF."suppliers ";
			//var_dump( $sql );
			//var_dump( $sql2 );
		$result = db_query($sql, "Can't search for vendors" );
		//var_dump( $result );
		$suppliers = array();
		$count = 0;
		$shortnames = array();
                while ($row = db_fetch($result)) 
		{
			//display_notification(  __FILE__ . "::" . __LINE__ . "::" . print_r( $row, true )  );
			$suppliers[$count]['supplier_id'] = $row['supplier_id'];
			$suppliers[$count]['supplier_shortname'] = $row['supp_ref'];
			$suppliers[$count]['supp_ref'] = $row['supp_ref'];
			$suppliers[$count]['supp_name'] = $row['supp_name'];
			$suppliers[$count]['supp_address'] = $row['supp_address'];
			$suppliers[$count]['address'] = $row['address'];
			$suppliers[$count]['gst_no'] = $row['gst_no'];
			$suppliers[$count]['contact'] = $row['contact'];
			$suppliers[$count]['supplier_curr_code'] = $row['curr_code'];
			$suppliers[$count]['inactive'] = $row['inactive'];
			$suppliers[$count]['bank_account'] = $row['bank_account'];
			$suppliers[$count]['supp_account_no'] = $row['supp_account_no'];
			$suppliers[$count]['website'] = $row['website'];
			$suppliers[$count]['payment_terms'] = $row['payment_terms'];
			$suppliers[$count]['tax_included'] = $row['tax_included'];
			$suppliers[$count]['dimension_id'] = $row['dimension_id'];
			$suppliers[$count]['dimension2_id'] = $row['dimension2_id'];
			$suppliers[$count]['tax_group_id'] = $row['tax_group_id'];
			$suppliers[$count]['credit_limit'] = $row['credit_limit'];
			$suppliers[$count]['purchase_account'] = $row['purchase_account'];
			$suppliers[$count]['payable_account'] = $row['payable_account'];
			$suppliers[$count]['payment_discount_account'] = $row['payment_discount_account'];
			$suppliers[$count]['notes'] = $row['notes'];
			$suppliers[$count]['inactive'] = $row['inactive'];
			$shortnames[$count] = $row['supp_ref'];
			$count++;
		}
		$suppliers['shortnames'] = $shortnames;
		//display_notification( __LINE__  );
		//display_notification( print_r( $suppliers, true )  );
		return $suppliers;
	}
	/*@bool@*/function validate($value, $type)
	{
	}
	/**//*************************************************************
	* Score the match level between transaction and GL data
	*
	*       CURRENTLY NOT USED
	*	CURRENTLY DOESN"T WORk
	*
	*	TO BE MOVED INTO ORIGIN
	*
	* @param array GL data
	* @param array Transaction we are matching
	* @param array scoring values
	* @returns array the GL Data with a score
	******************************************************************/
	function score_match( $gldata, $trz, $scoring )
	{
		throw new Exception( "THIS FUNCTION IS INCOMPLETE." );
/*
	        $score = 0;
	//      //The query for GL Data already matches date?
	//      //We only want to score on 1 date since there is a chance both are the same depending on reporting bank
	        if( sql2date( $trz['valueTimestamp'] ) == sql2date( $arr['tran_date'] ) )
	        {
	                //var_dump( __LINE__ );
	                $score += 10;
	        } else
	        if( sql2date( $trz['entryTimestamp'] ) == sql2date( $arr['tran_date'] ) )
	        {
	                //var_dump( __LINE__ );
	                $score += 10;
	        }
	        //var_dump( $score );
	//      //Probably need to do a lookup - initial imports don't have the account number matching the GL Account.
	        if( $trz['account'] == $arr['account'] )
	        {
	                //var_dump( __LINE__ );
	                $score += 10;
	        }
	        //var_dump( $score );
	//      //trz accountName is the vendor/counterparty.
	        $exp = explode( ":", $arr['memo_'] );
	        $gl_vendor = $exp[1];
	        $gl_vendor_tokens = explode( " ", $gl_vendor );
	        $trz_vendor_tokens = explode( " ", $trz['accountName'] );
	        $count_gl = count( $gl_vendor_tokens );
	        $count_trz = count( $trz_vendor_tokens );
	        //For percentage Name Match, base upon SMALLER number of chunks.
	        if( $count_trz <= $count_gl )
	        {
	                //var_dump( __LINE__ );
	                if( $count_trz > 0 )
	                {
	                        //var_dump( __LINE__ );
	                        $percent = 100 / $count_trz;
	                }
	                else
	                {
	                        //var_dump( __LINE__ );
	                        $percent = 0;
	                }
	        }
	        else
	        {
	                //var_dump( __LINE__ );
	                if( $count_gl > 0 )
	                {
	                        //var_dump( __LINE__ );
	                        $percent = 100 / $count_gl;
	                }
	                else
	                {
	                        //var_dump( __LINE__ );
	                        $percent = 0;
	                }
	        }
	        //var_dump( $percent );
	        //var_dump( $score );
	        $matched = match_tokens( $gl_vendor_tokens, $trz_vendor_tokens );
	        //var_dump( __LINE__ );
	        //var_dump( $matched );
	        $score += $matched * $percent;
	        //var_dump( $score );
	//      //Transaction Code __might__ match depending on what was imported into Gnu way back when...
	//      //References are not guaranteed Unique between FIs.  However, if the rest here matches....
	        if( $trz['transactionCode'] == $arr['reference'] )
	        {
	                $score += 100;
	        }
	        //var_dump( __LINE__ );
	        //var_dump( $score );
	        $arr['score'] = $score;
	//      //var_dump( $arr );
	        //var_dump( __LINE__ );
	                $arr_arr[] = $arr;
	        }
	        return $arr_arr;
*/	
	}
//}

	/*@bool@* /function validate($value, $type)
	{
	/****
	 *
*	 * 		try {
*			$this->clear_sql_vars();
*			$this->select_array = array( 'stock_id' );
*			$this->from_array = array( TB_PREF . 'stock_master' );
*			$this->where_array = array( 'stock_id' => array( "like" =>  $stock_id_match ) );
*			$this->insert_array = array();
*			$this->insert_array['...'] = "";
*			$this->buildInsertSelectQuery();
*			$this->query( "Can not insert prices for matched stock ids " . $stock_id_match, "insert");
*		} catch( Exception $e )
*		{
*			throw $e;
*		}
	 *
	 * *** /
	}
	/**//******************************************************
	* Take a FULL name of a vendor and return a shortened name
	*
	* THE is a common 1st word in a name.
	* Chains often have a #1234 at the end of a name
	* City's have "-" to differentiate departments
	*
	* @param string full name
	* @param bool Should we set supp_ref with the shortened version
	* @returns string shortened name
	************************************************************/
	function shortenName( $fullname, $setSupp_ref = false )
	{
	        $tokens = explode( " ", (string) $fullname );
	        $shortname = "";
		$tcount = 0;
	        foreach( $tokens as $token )
	        {
	                switch( strtoupper( $token ) )
	                {
	                        case "THE":
					if( $tcount == 0 )
					{
						//THE city of XXX
	                              		break;
					}
					else
					{
						//Leader of THE pack
	                                	$shortname .= " " . $token;
	                                	break;
					}
				//Amazon shows up in different formats on a CC statement
	                        case "AMZN":
	                        case "AMAZON":
	                        case "AMAZON*":
	                                $shortname .= "Amazon";
	                                break 2;        //Should take us out of the foreach
	                        case "-":
	                        case "*":
	                        case (strncmp( $token, "*", 1 ) == 0):
	                        case "#":
	                        case (strncmp( $token, "#", 1 ) == 0):
	                        //case ( is_numberic( $token ) == true ):
	                                break 2;        //Should take us out of the foreach
	                        default:
	                                $shortname .= " " . $token;
	                                break;
	                }
			$tcount++;
	        }
		if( $setSupp_ref )
		{
			$this->set( "supp_ref", $shortname );
		}
	        return $shortname;
	}

/**/
}

/*
$test = new fa_suppliers();
var_dump( $test );
*/
?>
