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
class fa_sales_order_details extends table_interface
{
	//fa_sales_order_details
	/*

+---------------+------------+------+-----+---------+-------+
| Field         | Type       | Null | Key | Default | Extra |
+---------------+------------+------+-----+---------+-------+
| id               | int(11)     | NO   | PRI | NULL    | auto_increment |
| order_no         | int(11)     | NO   |     | 0       |                |
| trans_type       | smallint(6) | NO   | MUL | 30      |                |
| stk_code         | varchar(64) | YES  |     | NULL    |                |
| description      | tinytext    | YES  |     | NULL    |                |
| qty_sent         | double      | NO   |     | 0       |                |
| unit_price       | double      | NO   |     | 0       |                |
| quantity         | double      | NO   |     | 0       |                |
| discount_percent | double      | NO   |     | 0       |                
+---------------+------------+------+-----+---------+-------+
	*/
	protected $id;	
	protected $order_no;
	protected $trans_type
	protected $stk_code;
	protected $description;
	protected $qty_sent;
	protected $unit_price;
	protected $quantity;
	protected $discount_percent;
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
		$this->table_details['tablename'] = TB_PREF . 'sales_order_details';
			/*

+---------------+------------+------+-----+---------+-------+
| Field         | Type       | Null | Key | Default | Extra |
+---------------+------------+------+-----+---------+-------+
| id               | int(11)     | NO   | PRI | NULL    | auto_increment |
| order_no         | int(11)     | NO   |     | 0       |                |
| trans_type       | smallint(6) | NO   | MUL | 30      |                |
| stk_code         | varchar(64) | YES  |     | NULL    |                |
| description      | tinytext    | YES  |     | NULL    |                |
| qty_sent         | double      | NO   |     | 0       |                |
| unit_price       | double      | NO   |     | 0       |                |
| quantity         | double      | NO   |     | 0       |                |
| discount_percent | double      | NO   |     | 0       |                
+---------------+------------+------+-----+---------+-------+
	*/

		$this->fields_array[] = array('name' => 'id', 'label' => 'Index', 'type' => 'int(11)', 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'default' => '0' );
		$this->fields_array[] = array( 'name' => 'order_no', 'label' => 'Order Number', 'type' => 'int(11)', 'null' => 'NULL', 'readwrite' => 'readwrite', 'default' => '0' );
		$this->fields_array[] = array( 'name' => 'trans_type', 'label' => 'Reorder Level', 'type' => 'int(11)', 'null' => 'NULL', 'readwrite' => 'readwrite', 'default' => '0' );
		$this->fields_array[] = array( 'name' => 'stk_code', 'label' => 'Stock ID', 'type' => $stockl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '0' );
		$this->fields_array[] = array( 'name' => 'description', 'label' => 'Description', 'type' => $descl, 'null' => 'NULL', 'readwrite' => 'readwrite', 'default' => '0' );
		$this->fields_array[] = array( 'name' => 'qty_sent', 'label' => 'Quantity Delivered', 'type' => 'double', 'null' => 'NULL', 'readwrite' => 'readwrite', 'default' => '0' );
		$this->fields_array[] = array( 'name' => 'unit_price', 'label' => 'Unit Price', 'type' => 'double', 'null' => 'NULL', 'readwrite' => 'readwrite', 'default' => '0' );
		$this->fields_array[] = array( 'name' => 'quantity', 'label' => 'Quantity Ordered', 'type' => 'double', 'null' => 'NULL', 'readwrite' => 'readwrite', 'default' => '0' );
		$this->fields_array[] = array( 'name' => 'discount_percent', 'label' => 'Discount Percent', 'type' => 'double', 'null' => 'NULL', 'readwrite' => 'readwrite', 'default' => '0' );
		
		$this->table_details['primarykey'] = "id";
	}
	function insert()
	{
		$this->insert_table();
	}
	function update()
	{
		$this->update_table();
	}
	/**************************//**
	 *
	 *
	 * ****************************/
	function delete_lineitem()
	{
		$this->clear_sql_vars();
		$this->select_array() = array( '*' );
		$this->where_array = array();
		if( isset( $this->id ) )
			$this->where_array['id'] =  $this->id;
		//$this->where_array['stk_code'] =  $this->stk_code;
		//$this->where_array['qty_sent'] = $this->qty_sent;
		//$this->where_array['curr_abrev'] = $this->curr_abrev;
		//$this->where_array['price'] = $this->price;
		$this->buildDeleteQuery();
		$res = $this->query( "Order lineitem could not be deleted", "delete" );
		return $this->db_fetch( $res );
	}
	/**************************//**
	 *
	 *
	 * ****************************/
	function get_stock_id_orders()
	{
		$this->clear_sql_vars();
		$this->select_array() = array( '*' );
		$this->where_array = array();
		$this->where_array['stk_code'] =  $this->stk_code;
		//$this->where_array['qty_sent'] = $this->qty_sent;
		if( isset( $this->id ) )
			$this->where_array['id'] = $this->id;
		$this->buildSelectQuery();
		$res = $this->query( "Stock_ID order could not be retrieved", "select" );
		return $this->db_fetch( $res );
	}
	/***************************************************//**
	 * Get the list of items where not everything ordered by customers has been delivered
	 *
	 * Inspired by get_demand_qty in includes/db/manufacturing_db.inc
	 *
	 * @param INTERNAL stock_id (optional)
	 * @return array
	 * ****************************************************/
	function get_unfilled_items()
	{
		//select * from 1_sales_order_details where qty_sent <> quantity and trans_type=ST_SALESORDER;
		$this->clear_sql_vars();
		$this->select_array() = array( '*' );
		$this->where_array = array();
		$this->where_array['trans_type'] =  ST_SALESORDER;
		$this->where_array['qty_sent'] = array( 'ne', 'quantity' );
		if( isset( $this->stk_code ) )
			$this->where_array['stk_code'] = $this->stk_code;
		$this->buildSelectQuery();
		$res = $this->query( "Stock_ID order could not be retrieved", "select" );
		return $this->db_fetch( $res );
	}
}


/******************TESTING****************************/
/*
$test = new fa_sales_order_details();
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
	$bank->set( 'bank_account', '1060' );	//!< bank account number in sales_order_details
	$bank->insert();	//how do we determine success?
	$bank->select();	//assuming insert set id;
	$bank->set( 'inactive', true );
	$bank->update();
	$bank->select();	//assuming insert set id;
 */

/******************TESTING****************************/

?>
