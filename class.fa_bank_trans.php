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
class fa_bank_trans extends table_interface
{
	//fa_crm_persons
	protected $id;	
	protected $type;
	protected $trans_no;
	protected $bank_act;
	protected $ref;
	protected $trans_date;
	protected $bank_trans_type_id;
	protected $amount;
	protected $dimension_id;
	protected $dimension2_id;
	protected $person_type_id;
	protected $person_id;
	protected $reconciled;
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
		$this->table_details['tablename'] = TB_PREF . 'bank_trans';
		$this->fields_array[] = array('name' => 'id', 'label' => 'Bank Account', 'type' => 'int(11)', 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'default' => '0' );
		$this->fields_array[] = array( 'name' => 'type', 'label' => '', 'type' => 'int(11)', 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '0' );
		$this->fields_array[] = array( 'name' => 'trans_no', 'label' => '', 'type' => 'int(11)', 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '0' );
		$this->fields_array[] = array( 'name' => 'ref', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '0' );
		$this->fields_array[] = array( 'name' => 'trans_date', 'label' => '', 'type' => 'date', 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '0000-00-00' );
		$this->fields_array[] = array( 'name' => 'bank_trans_type_id', 'label' => '', 'type' => 'int(11)', 'null' => 'NULL', 'readwrite' => 'readwrite', 'default' => '0' );
		$this->fields_array[] = array( 'name' => 'amount', 'label' => '', 'type' => 'double', 'null' => 'NULL', 'readwrite' => 'readwrite', 'default' => '0' );
		$this->fields_array[] = array( 'name' => 'dimension_id', 'label' => '', 'type' => 'int(11)', 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '0' );
		$this->fields_array[] = array( 'name' => 'dimension2_id', 'label' => '', 'type' => 'int(11)', 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '0' );
		$this->fields_array[] = array( 'name' => 'person_type_id', 'label' => '', 'type' => 'int(11)', 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '0' );
		$this->fields_array[] = array( 'name' => 'person_id', 'label' => '', 'type' => 'tinyblob', 'null' => 'NULL', 'readwrite' => 'readwrite', 'default' => '0' );
		$this->fields_array[] = array( 'name' => 'reconciled', 'label' => '', 'type' => 'date', 'null' => 'NULL', 'readwrite' => 'readwrite', 'default' => '0' );
		$this->fields_array[] = array('name' => 'bank_act', 'label' => 'Bank Account', 'type' => $descl, 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'default' => '0' );
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
	/*@bool@*/function getByName()
	{
		$fields = "*";	//comma separated list
		$where = array('bank_account_name');
		$orderby = array();
		$limit = null;	//int
		return $this->select_table( $fields, $where, $orderby, $limit );
	}
	function getById()
	{
		return $this->getByPrimaryKey();
	}
	function add_transaction( $trans_type, $trans_id, $date, $trans_currency, $exchange_rate )
	{
		$this->account_code;
		/*
 		require_once( $path_to_faroot . '/includes/db/gl_db_bank_trans.inc' );
		require_once( $path_to_faroot . '/includes/db/gl_db/trans.inc');
		require_once( $path_to_faroot . 'includes/db/audit_trail_db.inc');
		add_bank_trans($trans_type, $trans_id, $bank_account, $reference, $date, $inclusive_amt, $person_type_id, $person_id,$trans_currency, $err_msg, $exchange_rate);
                add_gl_trans($trans_type,$trans_id, $date, $code, $dim1, $dim2 ,$memo, -$exclusive_amt, $trans_currency, $person_type_id,$person_id, $err_msg, $exchange_rate); 
                add_audit_trail($trans_type, $trans_d, $date);
		*/
	}
	/**************************************************//**
	 * Return the balance at the end of the day for an account
	 *
	 * Replaces the function from gl_db_bank_trans.inc get_balance_before_bank_account
	 * ***************************************************/
	/*float*/function getEODBalance( $date )
	{
		$sql_date = $date;
		//$sql_date = date2sql( $date );
		$this->select_array[] = "sum(amount)";
		$this->from_array[] = $this->table_details['tablename'];
		$this->where_array['bank_act'] = $this->bank_act;
		$this->where_array['trans_date'] = array( 'lte', $sql_date);
		$this->buildSelectQuery();
		$this->query( "Can't calculate EOD balance", "select" );
		$row = db_fetch_row( $this->query_result );
		return $row[0];
		/*
		 *	$from = date2sql($from);
	$sql = "SELECT SUM(amount) FROM ".TB_PREF."bank_trans WHERE bank_act="
		.db_escape($bank_account) . "
		AND trans_date < '$from'";
	$before_qty = db_query($sql, "The starting balance on hand could not be calculated");
	$bfw_row = db_fetch_row($before_qty);
	return $bfw_row[0];
		 * */
	}
}


/******************TESTING****************************/

$bank = new fa_bank_trans();
/*
	$bank->set( 'bank_account', '1060' );	//!< bank account number in bank_trans
	$bank->insert();	//how do we determine success?
	$bank->select();	//assuming insert set id;
	$bank->set( 'inactive', true );
	$bank->update();
	$bank->select();	//assuming insert set id;
 */
/*
	$res = $bank->getEODBalance( date( "Y-m-d" ) );
	if( null == $res )
		throw new Exception( "No result returned" );
	else
		echo "EOD balance for today for account " . $bank->bank_account_name . " is " . $res;
 */

/******************TESTING****************************/

?>
