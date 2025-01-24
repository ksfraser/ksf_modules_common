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
class fa_payment_terms extends table_interface
{
	//fa_crm_persons
	protected $terms_indicator;	
	protected $terms;
	protected $inactive;
	protected $days_before_due;
	protected $day_in_following_month;
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
		$this->table_details['tablename'] = TB_PREF . 'payment_terms';
		$this->fields_array[] = array('name' => 'terms_indicator', 'label' => 'Payment Terms Name', 'type' => $descl, 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'default' => '0' );
		$this->fields_array[] = array('name' => 'terms', 'label' => 'Paymnt Term Index', 'type' => 'int(11)', 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'default' => '0' );
		$this->fields_array[] = array('name' => 'inactive', 'label' => 'Record is Inactive', 'type' => 'bool', 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'default' => '0' );
		$this->fields_array[] = array('name' => 'day_in_following_month', 'label' => 'Due Day Next Month', 'type' => 'int(11)', 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'default' => '0' );
		$this->fields_array[] = array('name' => 'days_before_due', 'label' => 'Days until Due', 'type' => 'int(11)', 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'default' => '0' );
		$this->table_details['primarykey'] = "terms_indicator";
	}
	function insert()
	{
		$this->insert_table();
	}
	function update()
	{
		$this->update_table();
	}
	/*@bool@*/function getByName()
	{
		$fields = "*";	//comma separated list
		$where = array('terms');
		$orderby = array();
		$limit = null;	//int
		return $this->select_table( $fields, $where, $orderby, $limit );
	}
	function getById()
	{
		return $this->getByPrimaryKey();
	}
}


?>
