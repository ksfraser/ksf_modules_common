<?php

require_once( 'class.table_interface.php' );
/********************************************************//**
 * This class is a wrapper to make all data destructive functions in table_interface non-destructive.
 *
 * Does this by making the functions not do anything.
 * No:
 * 	Inserts
 * 	Updates
 * 	Deletes
 *
 *
 * *********************************************************/
class fa_table_wrapper extends table_interface
{
	function __construct()
	{
		parent::__construct();
	}
	/****************************
	 * We should not be messing directly in this table!
	 *
	 * *************************/
	function insert() {}
	function update() {}
	function insert_table() {}
	function update_table() {}
	function alter_table() {}
	function create_table() {}
	function delete_table() {}
	/******************!MESSING*************************/
}

