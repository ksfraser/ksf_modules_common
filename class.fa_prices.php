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
 *	Mantis 2918 Select prices updated since a given date
 *
 *
 *	This class uses FA specific routines (display_notification etc)
 *
 * **********************************************************/
class fa_prices extends table_interface
{
	//fa_prices
	/*
| id            | int(11)     | NO   | PRI | NULL    | auto_increment |
| stock_id      | varchar(64) | YES  | MUL | NULL    |                |
| sales_type_id | int(11)     | NO   |     | 0       |                |
| curr_abrev    | char(3)     | NO   |     |         |                |
| price         | double      | NO   |     | 0       |               
| last_updated  | timestamp   | NO   |     | current_timestamp | ON UPDATE CURRENT_TIMESTAMP()
	*/
	protected $id;	
	protected $stock_id;
	protected $sales_type_id;
	protected $curr_abrev;
	protected $price;
	protected $last_updated;
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
		$this->table_details['tablename'] = TB_PREF . 'prices';
		$this->fields_array[] = array('name' => 'id', 'label' => 'Bank Account', 'type' => 'int(11)', 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'default' => '0' );
		$this->fields_array[] = array( 'name' => 'stock_id', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '0' );
		$this->fields_array[] = array( 'name' => 'sales_type_id', 'label' => '', 'type' => 'int(11)', 'null' => 'NULL', 'readwrite' => 'readwrite', 'default' => '0' );
		$this->fields_array[] = array('name' => 'curr_abrev', 'label' => 'Bank Account', 'type' => $descl, 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'default' => '0' );
		$this->fields_array[] = array( 'name' => 'price', 'label' => '', 'type' => 'double', 'null' => 'NULL', 'readwrite' => 'readwrite', 'default' => '0' );
		$this->fields_array[] = array( 'name' => 'last_updated', 'label' => 'Last Updated', 'type' => 'timestamp', 'null' => 'NULL', 'readwrite' => 'readwrite', 'default' => 'current_timestamp' );
		
		$this->table_details['primarykey'] = "id";
		$this->from_array = array( TB_PREF . 'prices' );
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
	 * Insert a set of variable products from a match code into prices
	 *
	 * Future enhancement - recognize sales_type from its word
	 *
	 * @param int sales_type_id
	 * @param double price to set
	 * @param string partial stock_id to match against
	 * @param string ISO Currency code (from internal table)
	 * ************************************************/
	function add_price_multi_stock_master( /*numeric*/$sales_type=1, /*double*/ $price, /*stringr*/ $stock_id_match, /*ISO Code*/$currency='CAD' )
	{
		//insert ignore into 1_prices (stock_id, sales_type_id,curr_abrev, price) select stock_id, 1, 'CAD', 115/1.05 from 1_stock_master where stock_id like 'hd-gh%'
		try {
			$this->clear_sql_vars();
			//Select_array is a list of fields to select
			$this->select_array = array( 'stock_id', $sales_type, $currency, $price );
			$this->from_array = array( TB_PREF . 'stock_master' );
			$this->where_array = array( 'stock_id' => array( "like" =>  $stock_id_match ) );
			$this->insert_array = array();
			$this->insert_array['stock_id'] = "";
			$this->insert_array['sales_type_id'] = "";
			$this->insert_array['curr_abrev'] = "";
			$this->insert_array['price'] = "";
			$this->buildInsertSelectQuery();
			$this->query( "Can not insert prices for matched stock ids " . $stock_id_match, "insert");
		} catch( Exception $e )
		{
			throw $e;
		}
	}
	/**********************************************//**
	 * Update a set of prices by a "like" matched stock id
	 *	
	 *	This can be used to update a series of products.
	 *	Need to be careful that the match sku isn't too broad
	 *
	 * @param int sales_type_id
	 * @param double price to set
	 * @param string partial stock_id to match against
	 * @param string <optional> ISO Currency code (from internal table)
	 * ************************************************/
	function update_price_match(/*numeric*/$sales_type,  /*double*/ $price, /*stringr*/ $stock_id_match, /*ISO Code*/$currency = null)
	{
		//update 1_prices set price=52.38 where stock_id like '%balm%' and sales_type_id=1;
		$this->update_price($sales_type, $price, $stock_id_match, $currency, false);
		$this->where_array['stock_id'] = array( 'like', $stock_id_match );
		$this->buildUpdateQuery();
		$this->query( "Can not update price for stock_id " . $stock_id_match, "update");
	}
	/**********************************************//**
	 * Update a set of prices by a matched stock id
	 *
	 * @param int sales_type_id
	 * @param double price to set
	 * @param string partial stock_id to match against
	 * @param string ISO Currency code (from internal table)
	 * @param bool should we also run the query or are we setting sql vars for another function
	 * ************************************************/
	function update_price(/*numeric*/$sales_type,  /*double*/ $price, /*stringr*/ $stock_id, /*ISO Code*/$currency, $do_query = true)
	{
		//update 1_prices set price=52.38 where stock_id like '%balm%' and sales_type_id=1;
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
	/****************************/
	/****Cloned from native functions in 
 	*****inventory/includes/db/items_prices_db.inc
 	*/
	/****************************/
	/**************************//**
	 *
	 * @internal stock_id, sales_type_id, curr_abrev, price
	 * ****************************/
	function add_item_price()
	{
		$this->clear_sql_vars();
		$this->insert_array = array( );
		//$this->where_array = array();
		//$this->where_array['id'] =  $this->id;
		$this->insert_array['stock_id'] =  $this->stock_id;
		$this->insert_array['sales_type_id'] = $this->sales_type_id;
		$this->insert_array['curr_abrev'] = $this->curr_abrev;
		$this->insert_array['price'] = $this->price;
		$this->buildInsertQuery();
		$this->query( "Item Price could not be insertd", "insert" );
	}
	/**************************//**
	 *
	 * @internal sales_type_id, curr_abrev, price
	 * ****************************/
	function update_item_price()
	{
		$this->clear_sql_vars();
		$this->update_array = array( );
		$this->where_array = array();
		$this->where_array['id'] =  $this->id;
		//$this->update_array['stock_id'] =  $this->stock_id;
		$this->update_array['sales_type_id'] = $this->sales_type_id;
		$this->update_array['curr_abrev'] = $this->curr_abrev;
		$this->update_array['price'] = $this->price;
		$this->buildUpdateQuery();
		$res = $this->query( "Item Price could not be updated", "update" );
		return $this->db_fetch( $res );
	}
	/**************************//**
	 *
	 *
	 * ****************************/
	function delete_item_price()
	{
		$this->clear_sql_vars();
		$this->select_array = array( '*' );
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
	 * Get records that have updated since a date
	 *
	 *	In support of Mantis 2918
	 * 	List products that have been updated since a date
	 *
	 * @since 20240807
	 *
	 * @string date
	 * @param string Currency Abbreviation
	 * @param array list of sales_type_ids
	 * @returns array
	 * ****************************/
	function get_price_updated_since( $updated_since, $curr_abrev = null, $sales_type_ids = array( '1' ) )
	{
		$this->clear_sql_vars();
		$this->select_array = array( '*' );
		$this->from_array = array( TB_PREF . 'prices' );
		$this->where_array = array( 'last_updated' = array( ">" => $updated_since ) );
		if( null !== $curr_abrev )
		{
			$this->where_array[] = array( 'curr_abrev' = $curr_abrev );
		}
		else if( isset( $this->curr_abrev ) )
		{
			$this->where_array[] = array( 'curr_abrev' = $this->curr_abrev );
		}
		if( null !== $sales_type_ids )
		{
			$this->where_array = array( 'sales_type_id' = array( "in" => $sales_type_id ) );
		}
		else if( isset( $this->$sales_type_id ) )
		{
			$this->where_array = array( 'sales_type_id' =>  $this->sales_type_id  );
		}
		$this->buildSelectQuery();
		$res = $this->query( "Prices could not be retrieved" );
		return $this->db_fetch( $res );
	}
	/**************************//**
	 *
	 * @internal stock_id, sales_type_id, curr_abrev
	 * ****************************/
	function get_stock_price()
	{
		$this->clear_sql_vars();
		$this->from_array = array( TB_PREF . 'prices' );
		$this->select_array = array( '*' );
		$this->where_array = array();
		//$this->where_array['id'] =  $this->id;
		$this->where_array['stock_id'] =  $this->stock_id;
		$this->where_array['sales_type_id'] = $this->sales_type_id;
		$this->where_array['curr_abrev'] = $this->curr_abrev;
		//$this->where_array['price'] = $this->price;
		$this->buildSelectQuery();
		$res = $this->query( "Price could not be retrieved", "select" );
		return $this->db_fetch( $res );
	}
	/**************************//**
	 *
	 * @internal id
	 * ****************************/
	function get_prices()
	{
		$this->clear_sql_vars();
		$this->from_array = array( TB_PREF . 'prices' );
		$this->select_array = array( '*' );
		$this->where_array = array();
		$this->where_array['id'] =  $this->id;
		//$this->where_array['stock_id'] =  $this->stock_id;
		//$this->where_array['sales_type_id'] = $this->sales_type_id;
		//$this->where_array['curr_abrev'] = $this->curr_abrev;
		//$this->where_array['price'] = $this->price;
		$this->buildSelectQuery();
		$res = $this->query( "Price could not be retrieved", "select" );
		return $this->db_fetch( $res );
	}
	/**************************//**
	 *
	 * @internal stock_id, sales_type_id, curr_abrev
	 * ****************************/
	function get_stock_price_type_currency()
	{
		$this->clear_sql_vars();
		$this->from_array = array( TB_PREF . 'prices' );
		$this->select_array = array( '*' );
		$this->where_array = array();
		//$this->where_array['id'] =  $this->id;
		$this->where_array['stock_id'] =  $this->stock_id;
		$this->where_array['sales_type_id'] = $this->sales_type_id;
		$this->where_array['curr_abrev'] = $this->curr_abrev;
		//$this->where_array['price'] = $this->price;
		$this->buildSelectQuery();
		$res = $this->query( "Price could not be retrieved", "select" );
		return $this->db_fetch( $res );
	}

}


/******************TESTING****************************/
/*
$test = new fa_prices();
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
	$bank->set( 'bank_account', '1060' );	//!< bank account number in prices
	$bank->insert();	//how do we determine success?
	$bank->select();	//assuming insert set id;
	$bank->set( 'inactive', true );
	$bank->update();
	$bank->select();	//assuming insert set id;
 */

/******************TESTING****************************/

?>
