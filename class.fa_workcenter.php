<?php

require_once( 'class.fa_db.php' );

$path_to_root="../..";

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
class fa_workcenter extends fa_db
{
	//fa_crm_persons
	protected $name;	
	protected $type_;
	protected $closed;
	protected $date_;
	protected $due_date;
	var $min_cid;
	var $max_cid;
	var $errors = array();
	var $warnings = array();

	function __construct( /*$prefs_db*/ )
	{
		//parent::__construct( $prefs_db );
		parent::__construct();
	}
	function insert_workcenter()
	{
		$this->add_workcenter();
	}
	function update_workcenter()
	{
	}
	/*@bool@*/function getByName()
	{
		if ($this->name = '') 
			return FALSE;
		return $this->fetch( "WHERE name = '$this->name'" );
	}
	/*@bool@*/function fetch( $where = '' )
	{
		if( $where = '' )
			return FALSE;
		$sql = "SELECT * FROM ".TB_PREF."workcenters " . $where;
		$result = db_query($sql, "Could not find workcenter");
		if ($db_num_rows($result) == 0) 
			return FALSE;
		$row = db_fetch_row($result);
		if (!$row[0]) 
			return FALSE;
		foreach( $row[0] as $key => $value )
		{
			$this->$key = $value;
		}
		return TRUE;
	}
	/******************************************************//**
	 * Add a level 1 workcenter
	 *
	 * *******************************************************/
	function add_workcenter()
	{
		$date = Today();
		$due = add_days($date, $this->get_sys_default_required_by() );

		//$ref = references::get_next(systypes::workcenter());
		$c_ref = new fa_references();
		$ref = $c_ref->get_next( systypes::workcenter() );
		
		$this-id = add_workcenter($ref, $this->name, 1, $date, $due, "Added due to Item Import");
		display_notification("Added Dimension ");
	}
}


?>
