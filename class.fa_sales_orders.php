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
class fa_sales_orders extends fa_table_wrapper
{
	var $min_cid;
	var $max_cid;
	var $errors = array();
	var $warnings = array();

	/*
| order_no         | int(11)             | NO   | PRI | NULL       |       |
| trans_type       | smallint(6)         | NO   | PRI | 30         |       |
| version          | tinyint(1) unsigned | NO   |     | 0          |       |
| type             | tinyint(1)          | NO   |     | 0          |       |
| debtor_no        | int(11)             | NO   |     | 0          |       |
| branch_code      | int(11)             | NO   |     | 0          |       |
| reference        | varchar(100)        | NO   |     |            |       |
| customer_ref     | tinytext            | NO   |     | NULL       |       |
| comments         | tinytext            | YES  |     | NULL       |       |
| ord_date         | date                | NO   |     | 0000-00-00 |       |
| order_type       | int(11)             | NO   |     | 0          |       |
| ship_via         | int(11)             | NO   |     | 0          |       |
| delivery_address | tinytext            | NO   |     | NULL       |       |
| contact_phone    | varchar(30)         | YES  |     | NULL       |       |
| contact_email    | varchar(100)        | YES  |     | NULL       |       |
| deliver_to       | tinytext            | NO   |     | NULL       |       |
| freight_cost     | double              | NO   |     | 0          |       |
| from_stk_loc     | varchar(5)          | NO   |     |            |       |
| delivery_date    | date                | NO   |     | 0000-00-00 |       |
| payment_terms    | int(11)             | YES  |     | NULL       |       |
| total            | double              | NO   |     | 0          |       |

	 *
	 * */
	protected $order_no         ;// int(11)             | NO   | PRI | NULL       |       |
	protected $trans_type       ;// smallint(6)         | NO   | PRI | 30         |       |
	protected $version          ;// tinyint(1) unsigned | NO   |     | 0          |       |
	protected $type             ;// tinyint(1)          | NO   |     | 0          |       |
	protected $debtor_no        ;// int(11)             | NO   |     | 0          |       |
	protected $branch_code      ;// int(11)             | NO   |     | 0          |       |
	protected $reference        ;// varchar(100)        | NO   |     |            |       |
	protected $customer_ref     ;// tinytext            | NO   |     | NULL       |       |
	protected $comments         ;// tinytext            | YES  |     | NULL       |       |
	protected $ord_date         ;// date                | NO   |     | 0000-00-00 |       |
	protected $order_type       ;// int(11)             | NO   |     | 0          |       |
	protected $ship_via         ;// int(11)             | NO   |     | 0          |       |
	protected $delivery_address ;// tinytext            | NO   |     | NULL       |       |
	protected $contact_phone    ;// varchar(30)         | YES  |     | NULL       |       |
	protected $contact_email    ;// varchar(100)        | YES  |     | NULL       |       |
	protected $deliver_to       ;// tinytext            | NO   |     | NULL       |       |
	protected $freight_cost     ;// double              | NO   |     | 0          |       |
	protected $from_stk_loc     ;// varchar(5)          | NO   |     |            |       |
	protected $delivery_date    ;// date                | NO   |     | 0000-00-00 |       |
	protected $payment_terms    ;// int(11)             | YES  |     | NULL       |       |
	protected $total            ;// double              | NO   |     | 0          |       |


	//function __construct( /*$prefs_db*/ )
	function __construct( $caller = null )
	{
		//parent::__construct( $prefs_db );
		parent::__construct( $caller );
		$descl = 'varchar(' . DESCRIPTION_LENGTH . ')';
		$this->table_details['tablename'] = TB_PREF . 'purch_order_details';
		$this->fields_array[] = array('name' => 'po_detail_item', 'type' => 'int', 'null' => 'NOT NULL', 'readwrite' => 'readwrite',  );
		$this->fields_array[] = array('name' => 'order_no', 'type' => 'int', 'null' => 'NOT NULL',    'default' => '0', 'readwrite' => 'readwrite',  );
		$this->fields_array[] = array('name' => 'item_code', 'type' => 'varchar', 'null' => 'NULL',   'readwrite' => 'readwrite',  );
		$this->fields_array[] = array('name' => 'description', 'type' => 'tinytext', 'null' => 'NULL',   'readwrite' => 'readwrite',  );       
		$this->fields_array[] = array('name' => 'delivery_date', 'type' => 'date', 'null' => 'NOT NULL', 'default' => '0', 'readwrite' => 'readwrite',  );
		$this->fields_array[] = array('name' => 'qty_invoiced', 'type' => 'double', 'null' => 'NOT NULL', 'default' => '0',           'readwrite' => 'readwrite',  );
		$this->fields_array[] = array('name' => 'unit_price', 'type' => 'double', 'null' => 'NOT NULL', 'default' => '0',           'readwrite' => 'readwrite',  );
		$this->fields_array[] = array('name' => 'act_price', 'type' => 'double', 'null' => 'NOT NULL', 'default' => '0',           'readwrite' => 'readwrite',  );
		$this->fields_array[] = array('name' => 'std_cost_unit', 'type' => 'double', 'null' => 'NOT NULL', 'default' => '0',           'readwrite' => 'readwrite',  );
		$this->fields_array[] = array('name' => 'quantity_ordered', 'type' => 'double', 'null' => 'NOT NULL',  'default' => '0',           'readwrite' => 'readwrite',  );
		$this->fields_array[] = array('name' => 'quantity_received', 'type' => 'double', 'null' => 'NOT NULL', 'default' => '0',           'readwrite' => 'readwrite',  );
		$this->table_details['primarykey'] = "po_detail_item";
	}
	/*************************************************//**
	 * Retrieve Item, quantity, Supplier, Days to arrive on an order by order basis
	 *
	 * @param none
	 * @return none.  Sets internal variable
	 * ***************************************************/
	function order2deliverydays()
	{
		//select d.item_code, s.supp_name, abs(datediff(d.delivery_date, o.ord_date) ) from 1_purch_order_details d, 1_purch_orders o, 1_suppliers s  where o.order_no=d.order_no   and o.supplier_id=s.supplier_id  order by d.item_code, s.supp_name;
		$this->select_array[] = 'd.item_code as stock_id';
		$this->select_array[] = 's.supp_name as supplier';
		$this->select_array[] = 'abs(datediff(d.delivery_date, o.ord_date) ) as days';
		$this->select_array[] = 'd.order_no as order_number';
		$this->select_array[] = 'd.quantity_ordered as quantity';
		$this->from_array[] = 'purch_order_details d';
		$this->from_array[] = 'purch_orders o';
		$this->from_array[] = 'suppliers s';
		$this->where_array['o.order_no'] ='d.order_no';
		$this->where_array['o.supplier_id'] = 's.supplier_id';
		$this->orderby_array = array( 'd.item_code', 's.supp_name' );
		$this->buildSelectQuery();
	}
	function max_min_delay_item()
	{
		//select d.item_code, s.supp_name, min(abs(datediff(d.delivery_date, o.ord_date) ) ), max(abs(datediff(d.delivery_date, o.ord_date) )) from 1_purch_order_details d, 1_purch_orders o, 1_suppliers s  where o.order_no=d.order_no   and o.supplier_id=s.supplier_id group by d.item_code order by d.item_code, s.supp_name;
		$this->select_array[] = 'd.item_code as stock_id';
		$this->select_array[] = 's.supp_name as supplier';
		$this->select_array[] = 'min(abs(datediff(d.delivery_date, o.ord_date) ) ) as shortest';
		$this->select_array[] = 'max(abs(datediff(d.delivery_date, o.ord_date) ) ) as longest';
		$this->from_array[] = 'purch_order_details d';
		$this->from_array[] = 'purch_orders o';
		$this->from_array[] = 'suppliers s';
		$this->where_array['o.order_no'] ='d.order_no';
		$this->where_array['o.supplier_id'] = 's.supplier_id';
		$this->groupby_array[] = 'd.item_code';
		$this->orderby_array = array( 'd.item_code', 's.supp_name' );
		$this->buildSelectQuery();
	
	}
	/*int*/function get_order2deliverydays()
	{
		$this->order2deliverydays();
		$this->query( __METHOD__ . " couldn't get the list of items by orders and suppliers and their delay in fulfillment" );
		//Now what LOL!  ->query_result
		//returns an array of results
		return count( $this->query_result ) + 1;
	}
	/*int*/function get_max_min_delay_item()
	{
		$this->max_min_delay_item();
		$this->query( __METHOD__ . " couldn't get the list of items by orders and suppliers and their delay in fulfillment" );
		//Now what LOL!  ->query_result
		//returns an array of results
		return count( $this->query_result ) + 1;
		
	}
	/*@bool@*/function getByName()
	{
		/*
		$fields = "*";	//comma separated list
		$where = array('terms');
		$orderby = array();
		$limit = null;	//int
		return $this->select_table( $fields, $where, $orderby, $limit );
 		*/
	}
	function supplier_min_max()
	{
		//select s.supp_name, min(abs(datediff(d.delivery_date, o.ord_date) ) ), max(abs(datediff(d.delivery_date, o.ord_date) )) from 1_purch_order_details d, 1_purch_orders o, 1_suppliers s  where o.order_no=d.order_no   and o.supplier_id=s.supplier_id group by s.supp_name order by  s.supp_name;
		$this->select_array[] = 's.supp_name as supplier';
		$this->select_array[] = 'min(abs(datediff(d.delivery_date, o.ord_date) ) ) as shortest';
		$this->select_array[] = 'max(abs(datediff(d.delivery_date, o.ord_date) ) ) as longest';
		$this->from_array[] = 'purch_order_details d';
		$this->from_array[] = 'purch_orders o';
		$this->from_array[] = 'suppliers s';
		$this->where_array['o.order_no'] ='d.order_no';
		$this->where_array['o.supplier_id'] = 's.supplier_id';
		$this->groupby_array[] = 's.supp_name';
		$this->orderby_array = array( 's.supp_name' );
		if( strlen($this->supplier_id) > 1 )
			$this->where_array['o.supplier_id'] = $this->supplier_id;
		$this->buildSelectQuery();
	}
	/*int*/function get_supplier_min_max( $supplier_id = null )
	{
		if( null != $supplier_id )
			$this->supplier_id = $supplier_id;
		else
			unset($this->supplier_id);
		$this->supplier_min_max();
		$this->query( __METHOD__ . " couldn't get the list of suppliers and their delay in fulfillment" );
		//Now what LOL!  ->query_result
		//returns an array of results
		if( isset( $this->supplier_id ) )
		{
			$this->shortest = $this->query_results[0]['shortest'];
			$this->longest = $this->query_results[0]['longest'];
		}
		return count( $this->query_result ) + 1;
	
	}
	function stock_id_min_max()
	{
		//select s.supp_name, min(abs(datediff(d.delivery_date, o.ord_date) ) ), max(abs(datediff(d.delivery_date, o.ord_date) )) from 1_purch_order_details d, 1_purch_orders o, 1_suppliers s  where o.order_no=d.order_no   and o.supplier_id=s.supplier_id group by s.supp_name order by  s.supp_name;
		$this->select_array[] = 'd.item_code as stock_id';
		$this->select_array[] = 'min(abs(datediff(d.delivery_date, o.ord_date) ) ) as shortest';
		$this->select_array[] = 'max(abs(datediff(d.delivery_date, o.ord_date) ) ) as longest';
		$this->from_array[] = 'purch_order_details d';
		$this->from_array[] = 'purch_orders o';
		$this->from_array[] = 'suppliers s';
		$this->where_array['o.order_no'] ='d.order_no';
		$this->where_array['o.supplier_id'] = 's.supplier_id';
		if( isset( $this->stock_id ) )
			$this->where_array['d.item_code'] = $this->stock_id;
		$this->groupby_array[] = 'd.item_code';
		$this->orderby_array = array( 'd.item_code' );
		$this->buildSelectQuery();
	}
	/*int*/function get_stock_id_min_max( $stock_id = null )
	{
		if( null != $stock_id )
			$this->stock_id = $stock_id;
		else
			unset($this->stock_id);
		$this->stock_id_min_max();
		$this->query( __METHOD__ . " couldn't get the list of items their delay in fulfillment" );
		//Now what LOL!  ->query_result
		//returns an array of results
		if( isset( $this->stock_id ) )
		{
			$this->shortest = $this->query_results[0]['shortest'];
			$this->longest = $this->query_results[0]['longest'];
		}
		return count( $this->query_result ) + 1;
	
	}
	function getById()
	{
		return $this->getByPrimaryKey();
	}
	/**/
	/***********************************************//**
	 * Get the number on order for an item
	 *
	 * based upon get_on_porder_qty($stock_id, $location) 
	 * from includes\db\manufacturing_db.php
	 *
	 * @param loc_code optional
	 * @param internal stock_id
	 * @return array (stock_id, onorder)
	 * ***********************************************/
	function on_order_quantity( $location = null )
	{
		/*	$sql = "SELECT SUM(".TB_PREF."purch_order_details.quantity_ordered - "
		.TB_PREF."purch_order_details.quantity_received) AS qoo
		FROM ".TB_PREF."purch_order_details INNER JOIN "
			.TB_PREF."purch_orders ON ".TB_PREF."purch_order_details.order_no=".TB_PREF."purch_orders.order_no
		WHERE ".TB_PREF."purch_order_details.item_code=".db_escape($stock_id)." ";
	if ($location != "")
		$sql .= "AND ".TB_PREF."purch_orders.into_stock_location=".db_escape($location)." ";
	$sql .= "AND ".TB_PREF."purch_order_details.item_code=".db_escape($stock_id); */
		$this->select_array[] = 'd.item_code as stock_id';
		$this->select_array[] = 'sum(d.quantity_ordered - d.quantity_received) as onorder';
		$this->from_array[] = 'purch_order_details d';
		$this->from_array[] = 'purch_orders o';
		$this->where_array['o.order_no'] ='d.order_no';
		if( isset( $this->stock_id ) )
		{
			$this->where_array['d.item_code'] = $this->item_code;
		}
		if( isset( $location ) )
		{
			$this->where_array['o.into_stock_location'] = $location;
			$this->select_array[] = 'o.into_stock_location as loc_code';
		}
		$this->buildSelectQuery();
		$this->query( __METHOD__ . " couldn't get the list of items their delay in fulfillment" );
		return $this->query_results;
	}
}


?>
