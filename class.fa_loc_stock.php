<?php

require_once( 'class.table_interface.php' );

$path_to_root="../..";

/*

/********************************************************//**
 * Various modules need to be able to add or get info about workcenters from FA
 *
 *	This class uses FA specific routines (display_notification etc)
 *
 * **********************************************************/
class fa_loc_stock extends table_interface
{
	//fa_loc_stock
	/*

+---------------+------------+------+-----+---------+-------+
| Field         | Type       | Null | Key | Default | Extra |
+---------------+------------+------+-----+---------+-------+
| loc_code      | char(5)    | NO   | PRI |         |       |
| stock_id      | char(20)   | NO   | PRI |         |       |
| reorder_level | bigint(20) | NO   |     | 0       |       |
+---------------+------------+------+-----+---------+-------+
	*/
	protected $loc_code;	
	protected $stock_id;
	protected $reorder_level;
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
		$this->table_details['tablename'] = TB_PREF . 'loc_stock';
		$this->fields_array[] = array('name' => 'loc_code', 'label' => 'Location Code', 'type' => $loccdl, 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'default' => '0' );
		$this->fields_array[] = array( 'name' => 'stock_id', 'label' => 'Stock ID', 'type' => $stockl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '0' );
		$this->fields_array[] = array( 'name' => 'reorder_level', 'label' => 'Reorder Level', 'type' => 'int(11)', 'null' => 'NULL', 'readwrite' => 'readwrite', 'default' => '0' );
		
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
	function delete_reorder()
	{
		$this->clear_sql_vars();
		$this->select_array() = array( '*' );
		$this->where_array = array();
		if( isset( $this->loc_code ) )
			$this->where_array['loc_code'] =  $this->loc_code;
		$this->where_array['stock_id'] =  $this->stock_id;
		//$this->where_array['reorder_level'] = $this->reorder_level;
		//$this->where_array['curr_abrev'] = $this->curr_abrev;
		//$this->where_array['price'] = $this->price;
		$this->buildDeleteQuery();
		$res = $this->query( "Location Reorder Level could not be deleted", "delete" );
		return $this->db_fetch( $res );
	}
	/**************************//**
	 *
	 *
	 * ****************************/
	function get_location_reorder()
	{
		$this->clear_sql_vars();
		$this->select_array() = array( '*' );
		$this->where_array = array();
		$this->where_array['stock_id'] =  $this->stock_id;
		//$this->where_array['reorder_level'] = $this->reorder_level;
		if( isset( $this->loc_code ) )
			$this->where_array['loc_code'] = $this->loc_code;
		$this->buildSelectQuery();
		$res = $this->query( "Reorder level could not be retrieved", "select" );
		return $this->db_fetch( $res );
	}
	function set_Location_reorder_level()
	{
		$this->clear_sql_vars();
		//$this->select_array() = array( '*' );
		$this->insert_array = array();
		$this->insert_array['loc_code'] =  $this->loc_code;
		$this->insert_array['stock_id'] =  $this->stock_id;
		$this->insert_array['reorder_level'] = $this->reorder_level;
		$this->buildInsertQuery();
		$ret = $this->query( "Location Reorder Level could not be set", "insert" );
		return $ret;
	}
	function update_Location_reorder_level()
	{
		$this->clear_sql_vars();
		//$this->select_array() = array( '*' );
		$this->update_array = array();
		$this->where_array = array();
		$this->where_array['loc_code'] =  $this->loc_code;
		$this->where_array['stock_id'] =  $this->stock_id;
		$this->update_array['reorder_level'] = $this->reorder_level;
		$this->buildUpdateQuery();
		$ret = $this->query( "Location Reorder Level could not be updated", "update" );
		return $ret;
	}

}


/******************TESTING****************************/
/*
$test = new fa_loc_stock();
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
	$bank->set( 'bank_account', '1060' );	//!< bank account number in loc_stock
	$bank->insert();	//how do we determine success?
	$bank->select();	//assuming insert set id;
	$bank->set( 'inactive', true );
	$bank->update();
	$bank->select();	//assuming insert set id;
 */

/******************TESTING****************************/

?>
