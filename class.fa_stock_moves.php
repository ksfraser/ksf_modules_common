<?php

require_once( 'class.table_interface.php' );

$path_to_root="../..";

/*
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/data_checks.inc");

include_once($path_to_root . "/inventory/includes/inventory_db.inc");
include_once($path_to_root . "/inventory/includes/db/items_codes_db.inc");
include_once($path_to_root . "/workcenters/includes/workcenters_db.inc");


/********************************************************//**
 * Various modules need to be able to add or get info about workcenters from FA
 *
 *	This class uses FA specific routines (display_notification etc)
 *
 * **********************************************************/
class fa_stock_moves extends table_interface
{
	//fa_stock_moves
	/*
| trans_id         | int(11)     | NO   | PRI | NULL       | auto_increment |
| trans_no         | int(11)     | NO   |     | 0          |                |
| stock_id         | varchar(64) | YES  | MUL | NULL       |                |
| type             | smallint(6) | NO   | MUL | 0          |                |
| loc_code         | char(5)     | NO   |     |            |                |
| tran_date        | date        | NO   |     | 0000-00-00 |                |
| person_id        | int(11)     | YES  |     | NULL       |                |
| price            | double      | NO   |     | 0          |                |
| reference        | char(40)    | NO   |     |            |                |
| qty              | double      | NO   |     | 1          |                |
| discount_percent | double      | NO   |     | 0          |                |
| standard_cost    | double      | NO   |     | 0          |                |
| visible          | tinyint(1)  | NO   |     | 1          |                |
 
	*/
	protected $trans_id;	
	protected $trans_no;
	protected $stock_id;
	protected $type;
	protected $loc_code;
	protected $tran_date;
	protected $person_id;
	protected $price;
	protected $reference;
	protected $qty;
	protected $discount_percent;
	protected $standard_cost;
	protected $visible;
	var $min_cid;
	var $max_cid;
	var $errors = array();
	var $warnings = array();

	//function __construct( /*$prefs_db*/ )
	function __construct( $caller = null )
	{
		//parent::__construct( $prefs_db );
		parent::__construct( $caller );
		$descl = 'varchar(' . DESCRIPTION_LENGTH . ')';
		$stockl = 'varchar(' . DESCRIPTION_LENGTH . ')';
		$refl = 'varchar(' . REFERENCE_LENGTH . ')';
		$loccdl = 'varchar(' . LOC_CODE_LENGTH . ')';
		$this->table_details['tablename'] = TB_PREF . 'stock_moves';
		$this->fields_array[] = array('name' => 'trans_id        ', 'label' => '', 'type' => 'int(11)', 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'default' => 'NULL' );
		$this->fields_array[] = array('name' => 'trans_no        ', 'label' => '', 'type' => 'int(11)', 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'default' => '0' );
		$this->fields_array[] = array('name' => 'stock_id        ', 'label' => '', 'type' => $stockl, 'null' => 'NULL',  'readwrite' => 'readwrite', 'default' => 'NULL' );
		$this->fields_array[] = array('name' => 'type            ', 'label' => '', 'type' => 'smallint(6)', 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'default' => '0' );
		$this->fields_array[] = array('name' => 'loc_code        ', 'label' => '', 'type' => $loccdl, 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array('name' => 'tran_date       ', 'label' => '', 'type' => 'date', 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'default' => '0000-00-00' );
		$this->fields_array[] = array('name' => 'person_id       ', 'label' => '', 'type' => 'int(11)', 'null' => 'NULL',  'readwrite' => 'readwrite', 'default' => 'NULL' );
		$this->fields_array[] = array('name' => 'price           ', 'label' => '', 'type' => 'double', 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'default' => '0' );
		$this->fields_array[] = array('name' => 'reference       ', 'label' => '', 'type' => $refl, 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array('name' => 'qty             ', 'label' => '', 'type' => 'double', 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'default' => '1' );
		$this->fields_array[] = array('name' => 'discount_percent', 'label' => '', 'type' => 'double', 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'default' => '0' );
		$this->fields_array[] = array('name' => 'standard_cost   ', 'label' => '', 'type' => 'double', 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'default' => '0' );
		$this->fields_array[] = array('name' => 'visible         ', 'label' => '', 'type' => 'bool', 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'default' => '1' );
		$this->table_details['primarykey'] = "trans_id";
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
	/**********************************************//**
	 * Update a set of stock_moves by a matched stock id
	 *
	 * @param int sales_type_id
	 * @param double price to set
	 * @param string partial stock_id to match against
	 * @param string ISO Currency code (from internal table)
	 * @param bool should we also run the query or are we setting sql vars for another function
	 * ************************************************/
	function update_price(/*numeric*/$sales_type,  /*double*/ $price, /*stringr*/ $stock_id, /*ISO Code*/$currency, $do_query = true)
	{
		//update 1_stock_moves set price=52.38 where stock_id like '%balm%' and sales_type_id=1;
		$this->clear_sql_vars();
		$this->where_array = array();
		$this->where_array['stock_id'] =  $stock_id;
		$this->where_array['sales_type_id'] = $sales_type;
		$this->where_array['curr_abrev'] = $currency;
		$this->update_array = array();
		$this->update_array['price'] = $price;
		$this->buildUpdateQuery();
		if( $do_query )
			$this->query("Can not update price for stock_id " . $stock_id, "update");
	}
	/**************************//**
	 *
	 *
	 * ****************************/
	function delete_item_price()
	{
		$this->clear_sql_vars();
		$this->select_array() = array( '*' );
		$this->where_array = array();
		$this->where_array['id'] =  $this->id;
		//$this->where_array['stock_id'] =  $this->stock_id;
		//$this->where_array['sales_type_id'] = $this->sales_type_id;
		//$this->where_array['curr_abrev'] = $this->curr_abrev;
		//$this->where_array['price'] = $this->price;
		$this->buildDeleteQuery();
		$res = $this->query( "Item Price could not be deleted", "delete" );
		return $this->db_fetch( $res );
	}
	/**************************//**
	 * Set the default Variables for the Quantity on Hand query
	 *
	 * Returns ALL stock_id's unless that variable is set in the class.
	 * 
	 * @param bool should we also separate out (groupby) by location
	 * @param INTERNAL: stock_id, loc_code, tran_date (all optional)
	 * @return null
	 * ****************************/
	function set_QOH_Query_var( $sort_loc_code = false )
	{
		$this->clear_sql_vars();
		$this->select_array = array( '*' );
		$this->select_array[] = "abs( sum( qty ) ) as sum";
		$this->where_array = array();
		if( isset( $this->stock_id ) )
			$this->where_array['stock_id'] = $this->stock_id;
		if( isset( $this->tran_date ) )
			$this->where_array['tran_date'] = $this->tran_date;
		$this->groupby_array = array();
		$this->groupby_array[] = "stock_id";
		if( $sort_loc_code  )
			$this->groupby_array[] = 'loc_code';
		else if( isset( $this->loc_code ) )
			$this->where_array['loc_code'] = $this->loc_code;
		return;
	}
	/**************************//**
	 * Get the Quantity on Hand for a stock_id
	 *
	 * Returns ALL stock_id's unless that variable is set in the class.
	 * 
	 * @param bool should we also separate out by location
	 * @return array results.
	 * ****************************/
	function get_QOH( $sort_loc_code = false )
	{
		$this->set_QOH_Query_var( $sort_loc_code );	
		$this->buildSelectQuery();
		$res = $this->query( "QOH could not be retrieved", "select");
		return $this->db_fetch( $res );
	}
	/*******************************//**
	 * Load the QOH for a location
	 *
	 * Replaces includes/db/manufacturing.db
	 * @param string location code
	 * @returns array
	 * *********************************/
	function load_stock_levels( $location )
	{
		$this->set( 'loc_code', $location );
		return $this->get_QOH( false );
	}
	function set_SALE_Query_var( $sort_loc_code = false )
	{
		$this->set_QOH_Query_var( $sort_loc_code );
		$this->select_array[] = "max( abs(qty) ) as max";
		$this->where_array['type'] = ST_CUSTDELIVERY;	//	includes/types.inc
	}
	function get_Lifetime_Sale( $sort_loc_code = false )
	{
		//select stock_id, abs(min(qty)) as max, abs(sum(qty)) as sum from 1_stock_moves where type=13 group by stock_id
		$this->set_SALE_Query_var( $sort_loc_code );
		$this->buildSelectQuery();
		$res = $this->query( "Lifetime Sale values could not be retrieved", "select");
		return $this->db_fetch( $res );
	}
	function get_Daterange_Sale( $startdate, $enddate, $sort_loc_code = false )
	{
		$this->set_SALE_Query_var( $sort_loc_code );
		if( null != $startdate )
			$this->where_array['tran_date'] = array( 'gt', $startdate );
		if( null != $enddate )
			$this->where_array['tran_date'] = array( 'lt', $enddate );
		$this->buildSelectQuery();
		$res = $this->query( "Sale values could not be retrieved", "select");
		return $this->db_fetch( $res );
	}
	function get_Daily_Sale( $sort_loc_code = false )
	{
		$this->set_SALE_Query_var( $sort_loc_code );
		$this->groupby_array[] = "tran_date";
		$this->buildSelectQuery();
		$res = $this->query( "Daily Sale values could not be retrieved", "select");
		return $this->db_fetch( $res );
	}
	/******************************//**
	 * Get the list of stock_ids that have
	 * any sales transactions
	 *
	 * ********************************/
	function get_stock_id_ever_sold()
	{
		$this->clear_sql_vars();
		$this->select_array = array( 'stock_id' );
		$this->where_array['type'] = ST_CUSTDELIVERY;	//	includes/types.inc
		$this->buildSelectQuery();
		$res = $this->query( "Lifetime Sale values could not be retrieved", "select");
		return $this->db_fetch( $res );

	}
}


/******************TESTING****************************/
/*
$test = new fa_stock_moves();
$test->unit_test = true;
try {
	$test->add_price_multi_stock_master( 3, 10, 'test', 'CAD' );
} catch (Exception $e )
{
	var_dump( $this->sql );
	$e->getMsg();
}
try {
$test->update_price_match( 3, 11, 'test', 'CAD' );
} catch (Exception $e )
{
	var_dump( $this->sql );
	$e->getMsg();
}
try {
$test->update_price( 3, 12, 'test', 'CAD' );
} catch (Exception $e )
{
	var_dump( $this->sql );
	$e->getMsg();
}
 */
/*
	$bank->set( 'bank_account', '1060' );	//!< bank account number in stock_moves
	$bank->insert();	//how do we determine success?
	$bank->select();	//assuming insert set id;
	$bank->set( 'inactive', true );
	$bank->update();
	$bank->select();	//assuming insert set id;
 */

/******************TESTING****************************/

?>
