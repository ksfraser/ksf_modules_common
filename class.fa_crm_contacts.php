<?php

require_once( 'class.table_interface.php' );

$path_to_root = "../..";
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/banking.inc");
include_once($path_to_root."/sales/inquiry/customer_inquiry.php");

require_once( $path_to_root . '/sales/includes/db/customers_db.inc' ); //add_customer
require_once( $path_to_root . '/sales/includes/db/branches_db.inc' ); //add_branch
require_once( $path_to_root . '/includes/db/crm_contacts_db.inc' ); //add_crm_*
require_once( $path_to_root . '/includes/db/connect_db.inc' ); //db_query, ...
require_once( $path_to_root . '/includes/errors.inc' ); //check_db_error, ...


class fa_crm_contacts extends table_interface
{
	/************************************************
	 * This is the FA crm contacts table
	 *
	 * This table links the others together?
	 * *********************************************/
	var $id;		//int
	var $person_id;		//int
	var $type;		//varchar		supplier/customer/cust_branch
	var $action;		//varchar		general/delivery/order/invoice.  When to be emailed...
	var $entity_id;		//int    

	function __construct( $caller = null )
	{
		parent::__construct( $caller );
		$descl = 'varchar(' . DESCRIPTION_LENGTH . ')';
		$this->table_details['tablename'] = TB_PREF . get_class( $this );
		$this->table_details['primarykey'] = "id";
		$this->fields_array[] = array( 'name' => 'id', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'person_id', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'type', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'action', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'entity_id', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
	}
	/**//*******************************************
	* Add a CRM Contact
	*
	* @param none
	* @return int the ID of the inserted record
	***********************************************/
	function add_crm_contact()
	{
		//function add_crm_contact($type, $action, $entity_id, $person_id)
		add_crm_contact( $this->type, $this->action, $this->entity_id, $this->$person_id );
		$this->id = db_insert_id();
		return $this->id;
	}
}

