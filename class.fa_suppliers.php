<?php

$path_to_root="../..";
//require_once( 'class.fa_origin.php' );
require_once( 'class.table_interface.php' );

/********************************************************//**
 * Various modules need to be able to add or get info about customers from FA
 *
 *	This class uses FA specific routines (display_notification etc)
 *
 *	STUB file to help use stock_master
 *
 * **********************************************************/
//class fa_stock_master extends fa_origin
class fa_suppliers extends table_interface
{
	/*
| Field              | Type         | Null | Key | Default | Extra |
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
	function __construct( $prefs_db )
	{
		//parent::__construct( $prefs_db );
		//parent::__construct();
		$this->table_details['tablename'] = TB_PREF . 'stock_master';
		$this->fields_array[] = array( 'name' => 'stock_id', 'type' => 'varchar(64)', 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'category_id', 'type' => 'int(11)', 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' ); 
		$this->fields_array[] = array( 'name' => 'tax_type_id', 'type' => 'int(11)', 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' ); 
		$this->fields_array[] = array( 'name' => 'description', 'type' => 'varchar(200)', 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );   
		$this->fields_array[] = array( 'name' => 'long_description', 'type' => 'text', 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' ); 
		$this->fields_array[] = array( 'name' => 'units', 'type' => ' varchar(20)', 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' ); 
		$this->fields_array[] = array( 'name' => 'mb_flag', 'type' => 'char(1)', 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' ); 
		$this->fields_array[] = array( 'name' => 'sales_account', 'type' => 'varchar(15)', 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'cogs_account', 'type' => 'varchar(15)', 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'inventory_account', 'type' => 'varchar(15)', 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'adjustment_account', 'type' => 'varchar(15)', 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'assembly_account', 'type' => 'varchar(15)', 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' ); 
		$this->fields_array[] = array( 'name' => 'dimension_id', 'type' => 'int(11)', 'null' => 'NULL', 'readwrite' => 'readwrite', 'default' => '' ); 
		$this->fields_array[] = array( 'name' => 'dimension2_id', 'type' => 'int(11)', 'null' => 'NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'actual_cost', 'type' => 'double', 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'last_cost', 'type' => 'double', 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'material_cost', 'type' => 'double', 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'labour_cost', 'type' => 'double', 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'overhead_cost', 'type' => 'double', 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'inactive', 'type' => 'bool', 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'no_sale', 'type' => 'bool', 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'editable', 'type' => 'bool', 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->table_details['primarykey'] = "stock_id";
	}
 	function insert()
	{
		$this->insert_table();
	}
	function update()
	{
		$this->update_table();
	}
	function getById()
	{
		return $this->getByPrimaryKey();
	}

	/*@bool@*/function validate($value, $type)
	{
	}
	/****
	 *
	 * 		try {
			$this->clear_sql_vars();
			
			$this->select_array = array( 'stock_id' );
			$this->from_array = array( TB_PREF . 'stock_master' );
			$this->where_array = array( 'stock_id' => array( "like" =>  $stock_id_match ) );
			$this->insert_array = array();
			$this->insert_array['...'] = "";
			$this->buildInsertSelectQuery();
			$this->query( "Can not insert prices for matched stock ids " . $stock_id_match, "insert");
		} catch( Exception $e )
		{
			throw $e;
		}
	 *
	 * ***/
	function getAll( $active_only = true )
	{
		$sql = "select * from " . TB_PREF . "stock_master";
		if( $active_only )
			$sql .= " where inactive='0'";
		$result = db_query( $sql , "Couldn't select stock_ids" );
		while( ( $row = mysql_fetch_array( $result ) ) != null )
		{
			$this->stock_array[] = $row;
			$this->stock_id_array[] = $row["stock_id"];
		}
	}
	function getStock_IDs( $active_only = true )
	{
		$sql = "select stock_id from " . TB_PREF . "stock_master";
		if( $active_only )
			$sql .= " where inactive='0'";
		
		$result = db_query( $sql , "Couldn't select stock_ids" );
		while( ( $row = mysql_fetch_array( $result ) ) != null )
		{
			$this->stock_id_array[] = $row["stock_id"];
		}
	}
	function count_active()
	{
		$res = db_query( "select count(*) from " . TB_PREF . "stock_master where inactive='0'", "Couldn't count QOH" );
		$count = db_fetch_row( $res );
		$this->active_count = $count[0];
	}
	function display_active_count()
	{
		$this->count_active();
		display_notification( $this->active_count . " rows of active items exist in stock_master.");
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
		$sql = "select sm.stock_id, sm.description, c.description, sm.inactive, sm.editable 
				from " . TB_PREF . "stock_master sm, " . TB_PREF . "stock_category c
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


}

?>
