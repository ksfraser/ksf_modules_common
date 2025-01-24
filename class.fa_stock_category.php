<?php

require_once( 'class.fa_table_wrapper.php' );

$path_to_root="../..";

/*
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/data_checks.inc");

include_once($path_to_root . "/inventory/includes/inventory_db.inc");
include_once($path_to_root . "/inventory/includes/db/items_codes_db.inc");
include_once($path_to_root . "/workcenters/includes/workcenters_db.inc");


/********************************************************//**
 * Various modules need to be able to get info about purchase order details from FA
 *
 *	This class uses FA specific routines (display_notification etc)
 *	This is a wrapper for the FA table.
 *
 * **********************************************************/
class fa_stock_category extends fa_table_wrapper
{
	var $min_cid;
	var $max_cid;
	var $errors = array();
	var $warnings = array();

	/*
| Field               | Type        | Null | Key | Default | Extra          |
+---------------------+-------------+------+-----+---------+----------------+
| category_id         | int(11)     | NO   | PRI | NULL    | auto_increment |
| description         | varchar(60) | NO   | UNI |         |                |
| inactive            | tinyint(1)  | NO   |     | 0       |                |
| dflt_tax_type       | int(11)     | NO   |     | 1       |                |
| dflt_units          | varchar(20) | NO   |     | each    |                |
| dflt_mb_flag        | char(1)     | NO   |     | B       |                |
| dflt_sales_act      | varchar(15) | NO   |     |         |                |
| dflt_cogs_act       | varchar(15) | NO   |     |         |                |
| dflt_inventory_act  | varchar(15) | NO   |     |         |                |
| dflt_adjustment_act | varchar(15) | NO   |     |         |                |
| dflt_assembly_act   | varchar(15) | NO   |     |         |                |
| dflt_dim1           | int(11)     | YES  |     | NULL    |                |
| dflt_dim2           | int(11)     | YES  |     | NULL    |                |
| dflt_no_sale        | tinyint(1)  | NO   |     | 0       |                |
	 *
	 * */
	protected $category_id         ; // int(11)     | NO   | PRI | NULL    | auto_increment |
	protected $description         ; // varchar(60) | NO   | UNI |         |                |
	protected $inactive            ; // tinyint(1)  | NO   |     | 0       |                |
	protected $dflt_tax_type       ; // int(11)     | NO   |     | 1       |                |
	protected $dflt_units          ; // varchar(20) | NO   |     | each    |                |
	protected $dflt_mb_flag        ; // char(1)     | NO   |     | B       |                |
	protected $dflt_sales_act      ; // varchar(15) | NO   |     |         |                |
	protected $dflt_cogs_act       ; // varchar(15) | NO   |     |         |                |
	protected $dflt_inventory_act  ; // varchar(15) | NO   |     |         |                |
	protected $dflt_adjustment_act ; // varchar(15) | NO   |     |         |                |
	protected $dflt_assembly_act   ; // varchar(15) | NO   |     |         |                |
	protected $dflt_dim1           ; // int(11)     | YES  |     | NULL    |                |
	protected $dflt_dim2           ; // int(11)     | YES  |     | NULL    |                |
	protected $dflt_no_sale        ; // tinyint(1)  | NO   |     | 0       |                |
	protected $dimension_array;	//Array of all dimensions (name, type, index)
	protected $matched_dimensions_array;	//1-D Array of dimensions (id) that match a substring
	protected $matched_dimension_string;	//substring that was matched


	//function __construct( /*$prefs_db*/ )
	function __construct( $caller = null )
	{
		//parent::__construct( $prefs_db );
		parent::__construct( $caller );
		$descl = 'varchar(' . DESCRIPTION_LENGTH . ')';
		$acctl = 'varchar(' . ACCOUNTCODE_LENGTH . ')';
		$this->table_details['tablename'] = TB_PREF . 'purch_order_details';
		$this->fields_array[] = array('name' => 'po_detail_item', 'type' => 'int', 'null' => 'NOT NULL', 'readwrite' => 'readwrite',  );

		$this->fields_array[] =array('name' => 'category_id        ', 'type' => 'int    ', 'null' =>'NOT NULL', 'readwrite' => 'readwrite', );
		$this->fields_array[] =array('name' => 'description        ', 'type' => $descl, 'null' =>'NOT NULL', 'readwrite' => 'readwrite', );
		$this->fields_array[] =array('name' => 'inactive           ', 'type' => 'bool ', 'null' =>'NOT NULL', 'readwrite' => 'readwrite', );
		$this->fields_array[] =array('name' => 'dflt_tax_type      ', 'type' => 'int    ', 'null' =>'NOT NULL', 'readwrite' => 'readwrite', );
		$this->fields_array[] =array('name' => 'dflt_units         ', 'type' => 'varchar(20)', 'null' =>'NOT NULL', 'readwrite' => 'readwrite', );
		$this->fields_array[] =array('name' => 'dflt_mb_flag       ', 'type' => 'char(1)    ', 'null' =>'NOT NULL', 'readwrite' => 'readwrite', );
		$this->fields_array[] =array('name' => 'dflt_sales_act     ', 'type' => $acctl, 'null' =>'NOT NULL', 'readwrite' => 'readwrite', );
		$this->fields_array[] =array('name' => 'dflt_cogs_act      ', 'type' => $acctl, 'null' =>'NOT NULL', 'readwrite' => 'readwrite', );
		$this->fields_array[] =array('name' => 'dflt_inventory_act ', 'type' => $acctl, 'null' =>'NOT NULL', 'readwrite' => 'readwrite', );
		$this->fields_array[] =array('name' => 'dflt_adjustment_act', 'type' => $acctl, 'null' =>'NOT NULL', 'readwrite' => 'readwrite', );
		$this->fields_array[] =array('name' => 'dflt_assembly_act  ', 'type' => $acctl, 'null' =>'NOT NULL', 'readwrite' => 'readwrite', );
		$this->fields_array[] =array('name' => 'dflt_dim1          ', 'type' => 'int    ', 'null' =>'NULL', 'readwrite' => 'readwrite', );  
		$this->fields_array[] =array('name' => 'dflt_dim2          ', 'type' => 'int    ', 'null' =>'NULL', 'readwrite' => 'readwrite', );   
		$this->fields_array[] =array('name' => 'dflt_no_sale       ', 'type' => 'bool ', 'null' =>'NOT NULL', 'readwrite' => 'readwrite', );

		$this->table_details['primarykey'] = "category_id";
	}
	function active_sellable_categories()
	{
		// select category_id, description, dflt_dim1, dflt_dim2 from 1_stock_category where inactive=0 and dflt_no_sale=0 limit 10;
		$this->select_array[] = 'category_id';
		$this->select_array[] = 'description';
		$this->select_array[] = 'dflt_dim1';
		$this->select_array[] = 'dflt_dim2';
		$this->from_array[] = $this->table_details['tablename'];
		$this->where_array['inactive'] ='0';
		$this->where_array['dflt_no_sale'] = '0';
		//$this->orderby_array = array( 'd.item_code', 's.supp_name' );
		$this->buildSelectQuery();
	}
	function get_category_name()
	{
		$this->clear_sql_vars();
		$this->select_array[] = 'description';
		$this->from_array[] = $this->table_details['tablename'];
		$this->where_array['category_id'] = $this->category_id;
		$this->buildSelectQuery();
		$this->query( __METHOD__ . " couldn't get the category name" );
		return $this->query_result;
	}
	/*int*/function get_active_sellable_categories()
	{
		$this->clear_sql_vars();
		$this->active_sellable_categories();
		$this->query( __METHOD__ . " couldn't get the list of active sellable categories" );
		//Now what LOL!  ->query_result
		//returns an array of results
		return count( $this->query_result );
	}
	
	function get_active_dimensions()
	{
		require_once( 'class.fa_dimensions.php' );
		$dim = new fa_dimensions();
		$this->dimension_array = $dim->get_active_dimensions_array();
		if( count( $this->dimension_array ) == 0 )
			throw new Exception( "No dimensions returned", KSF_FIELD_NOT_SET );
	}
	/**************************************************//**
	 * Find dimensions where the name matches a criteria
	 *
	 * ***************************************************/
	function get_matching_dimensions_byname( $substring )
	{
		$this->clear_sql_vars();
		if( !isset( $this->dimension_array ) )
			try {
				$this->get_active_dimensions();
			} catch( Exception $e )
			{
				if( KSF_FIELD_NOT_SET == $e->getCode() )
					throw new Exception( "No dimensions matching substring", KSF_NO_MATCH_FOUND );
			}
		$this->matched_dimension_string = $substring;
		foreach( $this->dimension_array as $row )
		{
			if( stripos( $row['name'], $substring ) )
			{
				//substring is in array
				$this->matched_dimensions_array[] = $row['id'];
			}
		}
		if( count( $this->matched_dimensions_array ) < 1 ) 
			throw new Exception( "No dimensions matching substring", KSF_NO_MATCH_FOUND );
	}
	/**********************************************//**
	 * Find categories that have the matched dimensions
	 *
	 * @param string substring to search dimensions for match ONLY when search hasn't been done already.
	 * ************************************************/
	function get_categories_with_dimension( $dim_substring = "" )
	{
		if( !isset( $this->matched_dimensions_array ) )
			try {
				$this->get_matching_dimensions_byname( $dim_substring );
			} catch( Exception $e )
			{
				throw $e;
			}
		//We have an array of dimension IDs so can now find categories that have those dimensions
		$this->active_sellable_categories();
		$instring = $this->array_to_sql_in( $this->matched_dimensions_array );
		$this->where_array['dflt_dim1']['in'] = $instring;

		$this->buildSelectQuery();	//builds all of the substrings.
	}


	/**/
}

/**********Testing******************/
class pod_test extends fa_stock_category
{
	function __construct()
	{
		parent::__construct;
		$this->clear_sql_vars();
		$this->order2deliverydays();
			//Expect  SELECT d.item_code as stock_id, s.supp_name as supplier, abs(datediff(d.delivery_date, o.ord_date) ) as days, d.order_no as order_number FROM purch_order_details d, purch_orders o, suppliers s WHERE o.order_no = 'd.order_no'  and o.supplier_id = 's.supplier_id' order by d.item_code, s.supp_name
		var_dump( $this->sql );
		$this->clear_sql_vars();
	}
}
/**************!Testing*************/

?>
