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
 * I've added some fields to the normal 0_bank_accounts to handle
 * bank statement imports such as FITID, etc.
 *
 *	This class uses FA specific routines (display_notification etc)
 *
 *	+--------------------------+--------------+------+-----+---------------------+----------------+
 *	| Field                    'label' => ''Type         | Null | Key | Default             | Extra          |
 *	+--------------------------+--------------+------+-----+---------------------+----------------+
 *	| account_code             'label' => ''varchar(15)  | NO   | MUL |                     |                |
 *	| account_type             'label' => ''smallint(6)  | NO   |     | 0                   |                |
 *	| bank_account_name        'label' => ''varchar(60)  | NO   | MUL |                     |                |
 *	| intu_bid                 'label' => ''varchar(16)  | NO   |     | NULL                |                |
 *	| BANKID                   'label' => ''varchar(16)  | NO   |     | NULL                |                |
 *	| ACCTID                   'label' => ''varchar(32)  | NO   |     | NULL                |                |
 *	| ACCTTYPE                 'label' => ''varchar(32)  | NO   |     | NULL                |                |
 *	| CURDEF                   'label' => ''varchar(3)   | NO   |     | NULL                |                |
 *	| bank_account_number      'label' => ''varchar(100) | NO   | MUL |                     |                |
 *	| bank_name                'label' => ''varchar(60)  | NO   |     |                     |                |
 *	| bank_address             'label' => ''tinytext     | YES  |     | NULL                |                |
 *	| bank_curr_code           'label' => ''char(3)      | NO   |     |                     |                |
 *	| dflt_curr_act            'label' => ''tinyint(1)   | NO   |     | 0                   |                |
 *	| id                       'label' => ''smallint(6)  | NO   | PRI | NULL                | auto_increment |
 *	| bank_charge_act          'label' => ''varchar(15)  | NO   |     |                     |                |
 *	| last_reconciled_date     'label' => ''timestamp    | NO   |     | 0000-00-00 00:00:00 |                |
 *	| ending_reconcile_balance 'label' => ''double       | NO   |     | 0                   |                |
 *	| inactive                 'label' => ''tinyint(1)   | NO   |     | 0                   |                |
 *	+--------------------------+--------------+------+-----+---------------------+----------------+
 *	
 *
 * **********************************************************/
class fa_bank_accounts extends table_interface
{
	//fa_crm_persons
	protected $id;	
	var $min_cid;
	var $max_cid;
	var $errors = array();
	var $warnings = array();
	protected $account_code;		//!< varchar(15)  'label' => ''NO| MUL |		  |		|
	protected $account_type;		//!< smallint(6)  'label' => ''NO|  | 0		|		|
	protected $bank_account_name;		//!< varchar(60)  'label' => ''NO| MUL |		  |		|
	protected $intu_bid;			//!< varchar(16)  'label' => ''NO|  | NULL		|		|
	protected $BANKID;			//!< varchar(16)  'label' => ''NO|  | NULL		|		|
	protected $ACCTID;			//!< varchar(32)  'label' => ''NO|  | NULL		|		|
	protected $ACCTTYPE;			//!< varchar(32)  'label' => ''NO|  | NULL		|		|
	protected $CURDEF;			//!< varchar(3)| NO|  'label' => ''NULL		|		|
	protected $bank_account_number;		//!< varchar(100) 'label' => ''NO| MUL |		  |		|
	protected $bank_name;			//!< varchar(60)  'label' => ''NO|  |		  |		|
	protected $bank_address;		//!< tinytext  'label' => ''YES  |  | NULL		|		|
	protected $bank_curr_code;		//!< char(3)| NO|  |		  |		|
	protected $dflt_curr_act;		//!< tinyint(1)| NO|  'label' => ''0		|		|
	protected $id;				//!< smallint(6)  'label' => ''NO| PRI | NULL		| auto_increment |
	protected $bank_charge_act;		//!< varchar(15)  'label' => ''NO|  |		  |		|
	protected $last_reconciled_date;	//!< timestamp 'label' => ''NO|  | 0000-00-00 00:00:00 |		|
	protected $ending_reconcile_balance;	//!< double 'label' => ''NO|  | 0		|		|
	protected $inactive;			//!< tinyint(1)| NO|  'label' => ''0		|		|


	//function __construct( /*$prefs_db*/ )
	function __construct( $caller = null )
	{
		//parent::__construct( $prefs_db );
		parent::__construct( $caller );
		$descl = 'varchar(' . DESCRIPTION_LENGTH . ')';
		$this->table_details['tablename'] = TB_PREF . 'bank_accounts';
		$this->fields_array[] = array('name' => 'bank_account_name', 'label' => 'Bank Account Name', 'type' => $descl, 'default' => 'NOT NULL',  'readwrite' => 'readwrite', 'default' => '0' );
		$this->fields_array[] = array('name' => 'id', 'label' => 'Bank Account', 'type' => 'int(11)', 'default' => 'NOT NULL',  'readwrite' => 'readwrite', 'default' => '0' );
		$this->fields_array[] = array('name' => 'bank_curr_code', 'label' => 'Bank Currency Code', 'type' => $descl, 'default' => 'NOT NULL',  'readwrite' => 'readwrite', 'default' => '0' );
		$this->fields_array[] = array('name' => 'inactive', 'label' => 'Record is Inactive', 'type' => 'bool', 'default' => 'NOT NULL',  'readwrite' => 'readwrite', 'default' => '0' );
		$this->table_details['primarykey'] = "id";


		 $this->fields_array[] = array('name' => 'account_code', 'type' => 'varchar(15)  ',  'readwrite' => 'readwrite',  'label' => '' );	//||
		 $this->fields_array[] = array('name' => 'account_type', 'type' => 'smallint(6)  ',  'readwrite' => 'readwrite',  'label' => '', 'default' => '0' );	//   |
		 $this->fields_array[] = array('name' => 'intu_bid', 'type' => 'varchar(16)  ',  'readwrite' => 'readwrite',  'label' => '', 'default' => 'NULL',   );	//   |
		 $this->fields_array[] = array('name' => 'BANKID', 'type' => 'varchar(16)  ',  'readwrite' => 'readwrite',  'label' => '', 'default' => 'NULL',   );	//   |
		 $this->fields_array[] = array('name' => 'ACCTID', 'type' => 'varchar(32)  ',  'readwrite' => 'readwrite',  'label' => '', 'default' => 'NULL',   );	//   |
		 $this->fields_array[] = array('name' => 'ACCTTYPE', 'type' => 'varchar(32)  ',  'readwrite' => 'readwrite',  'label' => '', 'default' => 'NULL',   );	//   |
		 $this->fields_array[] = array('name' => 'CURDEF', 'type' => 'varchar(3)   ',  'readwrite' => 'readwrite',  'label' => '', 'default' => 'NULL',   );	//   |
		 $this->fields_array[] = array('name' => 'bank_account_number', 'type' => 'varchar(100) ',  'readwrite' => 'readwrite',  'label' => '' );	//||
		 $this->fields_array[] = array('name' => 'bank_name', 'type' => 'varchar(60)  ',  'readwrite' => 'readwrite',  'label' => '' );	//||
		 $this->fields_array[] = array('name' => 'bank_address', 'type' => 'tinytext ',  'readwrite' => 'readwrite', 'label' => '', 'default' => 'NULL',   );	//   |
		 $this->fields_array[] = array('name' => 'dflt_curr_act', 'type' => 'tinyint(1)   ',  'readwrite' => 'readwrite',  'label' => '', 'default' => '0'  );	//   |
		 $this->fields_array[] = array('name' => 'bank_charge_act', 'type' => 'varchar(15)  ',  'readwrite' => 'readwrite',  'label' => '' );	//||
		 $this->fields_array[] = array('name' => 'last_reconciled_date', 'type' => 'timestamp',  'readwrite' => 'readwrite',  'label' => '' , 'default' => '0000-00-00 00:00:00' );	//   |
		 $this->fields_array[] = array('name' => 'ending_reconcile_balance', 'type' => 'double   ',  'readwrite' => 'readwrite',  'label' => '' , 'default' => '0'  );	//   |
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
/* 
*	Copied out of class.fa_bank_account.php
*		which extends class.fa_bank_trans.php
*
*       function get_account_currency()
*       {
*               $this->bank_curr_code = get_bank_account_currency($this->id);
*       }
*       function add_transaction( $trans_type, $trans_id, $date, $account_code, $trans_currency, $exchange_rate )
*       {
*               /*
*               require_once( $path_to_faroot . '/includes/db/gl_db_bank_trans.inc' );
*               require_once( $path_to_faroot . '/includes/db/gl_db/trans.inc');
*               require_once( $path_to_faroot . 'includes/db/audit_trail_db.inc');
*               add_bank_trans($trans_type, $trans_id, $bank_account, $reference, $date, $inclusive_amt, $person_type_id, $person_id,$trans_currency, $err_msg, $exchange_rate);
*                 add_gl_trans($trans_type,$trans_id, $date, $code, $dim1, $dim2 ,$memo, -$exclusive_amt, $trans_currency, $person_type_id,$person_id, $err_msg, $exchange_rate);
*                 add_audit_trail($trans_type, $trans_d, $date);
*               */
*       }
*       function getEODBalance( $date )
*       {
*               /*
*                *      $from = date2sql($from);
*       $sql = "SELECT SUM(amount) FROM ".TB_PREF."bank_trans WHERE bank_act="
*               .db_escape($bank_account) . "
*               AND trans_date < '$from'";
*       $before_qty = db_query($sql, "The starting balance on hand could not be calculated");
*       $bfw_row = db_fetch_row($before_qty);
*       return $bfw_row[0];
*                * */
*       }
*
*/
}


?>
