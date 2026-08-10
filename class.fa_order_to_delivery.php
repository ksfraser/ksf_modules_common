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
class fa_order_to_delivery extends table_interface
{
	//protected $id;	
	protected $stock_id;
	protected $supplier;
	protected $days;
	var $min_cid;
	var $max_cid;
	var $errors = array();
	var $warnings = array();
	private $query = "select d.item_code as stock_id, s.supp_name as supplier, abs(datediff(d.delivery_date, o.ord_date) ) as days from 1_purch_order_details d, 1_purch_orders o, 1_suppliers s  where o.order_no=d.order_no   and o.supplier_id=s.supplier_id  order by d.item_code, s.supp_name";


	//function __construct( /*$prefs_db*/ )
	function __construct( $caller = null )
	{
		//parent::__construct( $prefs_db );
		parent::__construct( $caller );
/*
		$descl = 'varchar(' . DESCRIPTION_LENGTH . ')';
		$this->table_details['tablename'] = TB_PREF . 'bank_accounts';
		$this->fields_array[] = array('name' => 'bank_account_name', 'label' => 'Bank Account Name', 'type' => $descl, 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'default' => '0' );
		$this->fields_array[] = array('name' => 'id', 'label' => 'Bank Account', 'type' => 'int(11)', 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'default' => '0' );
		$this->fields_array[] = array('name' => 'bank_curr_code', 'label' => 'Bank Currency Code', 'type' => $descl, 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'default' => '0' );
		$this->fields_array[] = array('name' => 'inactive', 'label' => 'Record is Inactive', 'type' => 'bool', 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'default' => '0' );
		$this->table_details['primarykey'] = "id";
*/
	}
	function insert()
	{
		return null;
		//$this->insert_table();
	}
	function update()
	{
		return null;
		//$this->update_table();
	}
	/*@bool@*/function getByName()
	{
		/*
		$fields = "*";	//comma separated list
		$where = array('bank_account_name');
		$orderby = array();
		$limit = null;	//int
		return $this->select_table( $fields, $where, $orderby, $limit );
		*/
		return false;
	}
	function getById()
	{
		//return $this->getByPrimaryKey();
		return null;
	}
	private function get_items_order_to_delivery()
	{
		$this->fields = array( 'd.item_code as stock_id', 's.supp_name as supplier', 'abs(datediff(d.delivery_date, o.ord_date) ) as days' );
		$this->from = array( TB_PREF . 'purch_order_details d', TB_PREF . 'purch_orders o', TB_PREF . 'suppliers s' ); 
		$this->where = array( 'o.order_no=d.order_no', 'o.supplier_id=s.supplier_id');
		$this->orderby = array( 'd.item_code', 's.supp_name');
		$this->limit = null;
	}
	private function get_suppliers_order_to_delivery()
	{
		$this->fields = array( 'd.order_no as order_number', 's.supp_name as supplier', 'abs(datediff(d.delivery_date, o.ord_date) ) as days' );
		$this->from = array( TB_PREF . 'purch_order_details d', TB_PREF . 'purch_orders o', TB_PREF . 'suppliers s' ); 
		$this->where = array( 'o.order_no=d.order_no', 'o.supplier_id=s.supplier_id');
		$this->orderby = array( 'd.item_code', 's.supp_name');
		$this->groupby = array( 'd.order_no' );
		$this->limit = null;
	}
	private function get_orders_to_delivery_details()
	{

		$this->fields = array( 'd.order_no as order_number', 's.supp_name as supplier', 'abs(datediff(d.delivery_date, o.ord_date) ) as days',
				'o.ord_date as order_date', 'd.delivery_date as delivery_date',  'd.item_code as stock_id', 
				'd.quantity_ordered as quantity_ordered', 'd.quantity_received as quantity_received' );
		$this->from = array( TB_PREF . 'purch_order_details d', TB_PREF . 'purch_orders o', TB_PREF . 'suppliers s' ); 
		$this->where = array( 'o.order_no=d.order_no', 'o.supplier_id=s.supplier_id');
		$this->orderby = array( 'd.item_code', 's.supp_name');
		$this->groupby = array( 'd.order_no' );
		$this->limit = null;
	}
	function get_delay( $type, $key )
	{
		switch( $type )
		{
			case 'items':
				$this->get_items_to_delivery_details();
				break;
			case 'suppliers':
				$this->get_suppliers_to_delivery_details();
				break;
			case 'orders':
				$this->get_orders_to_delivery_details();
				break;
			case 'item':
				$this->get_items_to_delivery_details();
				$this->where[] = ""='$key';
				break;
			case 'supplier':
				$this->get_suppliers_to_delivery_details();
				$this->where[] = ""='$key';
				break;
			case 'order':
				$this->get_orders_to_delivery_details();
				$this->where[] = "d.order_no='$key'";
				break;
			default:
				//Not a valid case so exit
				return;
				break;
		}
		$this->select_tables();
		return $this->result;

	}
}


?>
