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
class fa_prices extends table_interface
{
	//fa_prices
	/*
| id            | int(11)     | NO   | PRI | NULL    | auto_increment |
| stock_id      | varchar(64) | YES  | MUL | NULL    |                |
| sales_type_id | int(11)     | NO   |     | 0       |                |
| curr_abrev    | char(3)     | NO   |     |         |                |
| price         | double      | NO   |     | 0       |               
	*/
	protected $id;	
	protected $stock_id;
	protected $sales_type_id;
	protected $curr_abrev;
	protected $price;
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
		$this->tablename = $this->table_details['tablename'];
		$this->fields_array[] = array('name' => 'id', 'label' => 'Bank Account', 'type' => 'int(11)', 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'default' => '0' );
		$this->fields_array[] = array( 'name' => 'stock_id', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '0' );
		$this->fields_array[] = array( 'name' => 'sales_type_id', 'label' => '', 'type' => 'int(11)', 'null' => 'NULL', 'readwrite' => 'readwrite', 'default' => '0' );
		$this->fields_array[] = array('name' => 'curr_abrev', 'label' => 'Bank Account', 'type' => $descl, 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'default' => '0' );
		$this->fields_array[] = array( 'name' => 'price', 'label' => '', 'type' => 'double', 'null' => 'NULL', 'readwrite' => 'readwrite', 'default' => '0' );
		
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
	function add_price_multi_stock_master( /*numeric*/$sales_type, /*double*/ $price, /*stringr*/ $stock_id_match, /*ISO Code*/$currency )
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
	 * Update a set of prices by a matched stock id
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
	 *
	 * @internal stock_id, sales_type_id, curr_abrev
	 * ****************************/
	function get_stock_price()
	{
		$this->clear_sql_vars();
		$this->from_array = array( TB_PREF . 'stock_master' );
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
		$this->from_array = array( TB_PREF . 'stock_master' );
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
		$this->from_array = array( TB_PREF . 'stock_master' );
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
	/**//*************************************************
	* Insert a pricebook based upon a factor on an existing price
	*
	* @since 20241027
	*
	* @param string new pricebook
	* @param string source pricebook
	* @param float factor
	* @return mysql_res
	**************************************************************/
	function insert_pricebook_from_pricebook( $to, $from, $factor )
	{
		$sql = "INSERT IGNORE into " . $this->tablename;
		$sql .= "( stock_id, sales_type_id, curr_abrev, price ) ";
		$sql .= " SELECT stock_id, ";
		$sql .= " (select id from " . TB_PREF . "sales_types where sales_type in ( '" . $to . "' ) ), ";
		$sql .= " curr_abrev, price * $factor ";
		$sql .= " FROM " . $this->tablename;
		$sql .= " WHERE sales_type_id=(select id from " . TB_PREF . "sales_types where sales_type in ( '" . $from . "' ) )";
		display_notification( print_r( $sql, true ) );
		$res = db_query( $sql, "Couldn't update pricebook!" );
		return $res;
	}
	/**//****************************************************************
	* Get 2 pricebook prices to compare
	*
	* @since 20241027
	*
	* @param string pricebook 1
	* @param string pricebook 2
	* @param string Order By default stock_id
	* @param string stock_id (optional)
	* @param string curr_abrev default CAD.  If NULL we ensure the 2 pricebooks are using he same curr_abrev
	* @return array result
	********************************************************************/
	function compare_pricebooks( $pb1, $pb2, $orderby = "stock_id", $stock_id = null, $curr_abrev="CAD" )
	{
		//SELECT s.stock_id, s.price as disc, r.price as retail FROM `1_prices` s, 1_prices r where s.stock_id=r.stock_id and s.sales_type_id=3 and r.sales_type_id=1 order_by stock_id
		$this->clear_sql_vars();
		$this->from_array = array( $this->tablename . " t1", $this->tablename . " t2" );
		$this->select_array = array( 't1.stock_id', 't1.price', 't2.price' );
		$this->where_array = array();
		$this->where_array[] =  't1.stock_id=t2.stock_id';
		$this->where_array[] =  "t1.sales_type_id='" . $this->get_sales_type_id_from_name_SQL( $pb1 ) . "'";
		$this->where_array[] =  "t2.sales_type_id='" . $this->get_sales_type_id_from_name_SQL( $pb2 ) . "'";
		$this->where_array[] =  "t1.curr_abrev=t2.curr_abrev";
		if( null !== $curr_abrev )
		{
			$this->where_array[] =  "t1.curr_abrev='" . $curr_abrev . "'";
		}
		if( null !== $stock_id )
		{
			$this->where_array[] = "t1.stock_id ='" .  $stock_id . "'";
		}
		if( null !== $orderby )
		{
			switch( $orderby )
			{
				case 'stock_id':
				case 'price':
					$this->orderby_array[] = $orderby;
					break;
				default:
					$this->orderby_array[] = "stock_id";
					break;
			}
		}
		$this->buildSelectQuery();
		$res = $this->query( "Price could not be retrieved", "select" );
		return $this->db_fetch( $res );
	}
	/**//****************************************************************
	* Get SQL query to get sales_type_id from a string
	*
	* @since 20241027
	*
	* @param string sales_type
	* @return string SQL query
	********************************************************************/
	function get_sales_type_id_from_name_SQL( $sales_type )
	{
		$sql = "SELECT id from " . TB_PREF . "sales_types where sales_type in ( '" . $sales_type . "' )";
		return $sql;
	}
	/**//****************************************************************
	* Get sales_type_id from a string
	*
	* @since 20241027
	*
	* @param string sales_type
	* @return int sales_type_id
	********************************************************************/
	function get_sales_type_id_from_name( $sales_type )
	{
		$sql = $this->get_sales_type_id_from_name_SQL( $sales_type );
		$res = db_query( $sql, "Couldn't update pricebook!" );
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
