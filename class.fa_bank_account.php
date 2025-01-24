<?php

require_once( 'class.fa_bank_trans.php' );

$path_to_root="../..";



/********************************************************//**
 * Various modules need to be able to add or get info about workcenters from FA
 *
 *	This class uses FA specific routines (display_notification etc)
 *
 * **********************************************************/
class fa_bank_account extends fa_bank_trans
{
	//fa_crm_persons
	protected $id;	
	protected $bank_account_name;
	protected $bank_curr_code;
	protected $inactive;
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
		$this->table_details['tablename'] = TB_PREF . 'bank_accounts';
		$this->fields_array[] = array('name' => 'bank_account_name', 'label' => 'Bank Account Name', 'type' => $descl, 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'default' => '0' );
		$this->fields_array[] = array('name' => 'id', 'label' => 'Bank Account', 'type' => 'int(11)', 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'default' => '0' );
		$this->fields_array[] = array('name' => 'bank_curr_code', 'label' => 'Bank Currency Code', 'type' => $descl, 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'default' => '0' );
		$this->fields_array[] = array('name' => 'inactive', 'label' => 'Record is Inactive', 'type' => 'bool', 'null' => 'NOT NULL',  'readwrite' => 'readwrite', 'default' => '0' );
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
	function get_account_currency()
	{
		$this->bank_curr_code = get_bank_account_currency($this->id);
	}
	function add_transaction( $trans_type, $trans_id, $date, $account_code, $trans_currency, $exchange_rate )
	{
		/*
 		require_once( $path_to_faroot . '/includes/db/gl_db_bank_trans.inc' );
		require_once( $path_to_faroot . '/includes/db/gl_db/trans.inc');
		require_once( $path_to_faroot . 'includes/db/audit_trail_db.inc');
		add_bank_trans($trans_type, $trans_id, $bank_account, $reference, $date, $inclusive_amt, $person_type_id, $person_id,$trans_currency, $err_msg, $exchange_rate);
                add_gl_trans($trans_type,$trans_id, $date, $code, $dim1, $dim2 ,$memo, -$exclusive_amt, $trans_currency, $person_type_id,$person_id, $err_msg, $exchange_rate); 
                add_audit_trail($trans_type, $trans_d, $date);
		*/
	}
	function getEODBalance( $date )
	{
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


?>
