<?php

$path_to_root="../..";

require_once( 'class.fa_origin.php' );

include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/data_checks.inc");

include_once($path_to_root . "/inventory/includes/inventory_db.inc");
include_once($path_to_root . "/inventory/includes/db/items_codes_db.inc");
include_once($path_to_root . "/dimensions/includes/dimensions_db.inc");

/***************************************************************//**
 * Base db class for ksf common...  throws EXCEPTIONS for try/catch loops
 *
 * ****************************************************************/
class fa_db extends fa_origin
{
	protected $sql;
	protected $result;
	protected $row0;
	/****************************************************************//**
	 * Run a db query against SQL, set result and row0 and return TRUE if results
	 *
	 * ******************************************************************/
	/*@bool@*/function db_query()
	{
		if( !isset( $this->sql )
			return FALSE;
		$this->result = db_query($this->sql, "Could not find dimension");
		if ($db_num_rows($result) == 0) 
			return FALSE;
		$row = db_fetch_row($result);
		if (!$row[0]) 
			return FALSE;
		else
			$this->row0 = $row[0];
		return TRUE
	}

}

