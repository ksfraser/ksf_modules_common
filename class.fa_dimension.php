<?php

$path_to_root="../..";

require_once( 'class.fa_db.php' );
require_once( 'class.fa_references.php' );

include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/data_checks.inc");

include_once($path_to_root . "/inventory/includes/inventory_db.inc");
include_once($path_to_root . "/inventory/includes/db/items_codes_db.inc");
include_once($path_to_root . "/dimensions/includes/dimensions_db.inc");

/********************************************************//**
 * Various modules need to be able to add or get info about dimensions from FA
 *
 * 	inherited throws EXCEPTIONS for try/catch loops
 *	This class uses FA specific routines (display_notification etc)
 *	depends on fa_references
 *	sys_prefs::
 *	display_notification
 *	add_dimension
 *
 * **********************************************************/
class fa_dimension extends fa_db
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
	function insert_dimension()
	{
		$this->add_dimension();
	}
	function update_dimension()
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
		$sql = "SELECT * FROM ".TB_PREF."dimensions " . $where;
		$result = db_query($sql, "Could not find dimension");
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
	 * Add a level 1 dimension
	 *
	 * *******************************************************/
	function add_dimension()
	{
		$date = Today();
		$due = add_days($date, $this->get_sys_default_required_by() );

		//$ref = references::get_next(systypes::dimension());
		$c_ref = new fa_references();
		$ref = $c_ref->get_next( systypes::dimension() );
		
		$this-id = add_dimension($ref, $this->name, 1, $date, $due, "Added due to Item Import");
		display_notification("Added Dimension ");
	}
	function get_sys_default_required_by()
	{
		return sys_prefs::default_dimension_required_by();
	}
}

?>
