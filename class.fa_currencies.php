<?php

require_once( 'class.table_interface.php' );

$path_to_root = "../..";
include_once($path_to_root . "/includes/date_functions.inc");
/*
include_once($path_to_root . "/includes/banking.inc");
include_once($path_to_root."/sales/inquiry/customer_inquiry.php");

require_once( $path_to_root . '/sales/includes/db/customers_db.inc' ); //add_customer
require_once( $path_to_root . '/sales/includes/db/branches_db.inc' ); //add_branch
require_once( $path_to_root . '/includes/db/crm_contacts_db.inc' ); //add_crm_*
require_once( $path_to_root . '/includes/db/connect_db.inc' ); //db_query, ...
require_once( $path_to_root . '/includes/errors.inc' ); //check_db_error, ...
 */

class fa_currencies extends table_interface
{
	/************************************************
	 * This is the FA crm persons table
	 *
	 * This is basically a CONTACT/LEAD in CRM systems
	 * *********************************************/
	/*
| currency      | varchar(60)  | NO   |     |         |       |
| curr_abrev    | char(3)      | NO   | PRI |         |       |
| curr_symbol   | varchar(10)  | NO   |     |         |       |
| country       | varchar(100) | NO   |     |         |       |
| hundreds_name | varchar(15)  | NO   |     |         |       |
| inactive      | tinyint(1)   | NO   |     | 0       |       |
| auto_update   | tinyint(1)   | NO   |     | 1       |       |
+---------------+--------------+------+-----+---------+-------+

	 */
	protected $currency;
	protected $curr_abrev;
	protected $curr_symbol;
	protected $country;
	protected $hundreds_name;
	protected $inactive;	
	protected $auto_update;

	function __construct( $caller = null )
	{
		parent::__construct( $caller );
		$descl = 'varchar(' . DESCRIPTION_LENGTH . ')';
		$this->table_details['tablename'] = TB_PREF . get_class( $this );

		$this->fields_array[] = array( 'name' => 'id', 'label' => '', 'type' => 'int(11)', 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'ref', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'name', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );	//<@ Also in debtors_master
		$this->fields_array[] = array( 'name' => 'name2', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'address', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' ); 	//<@ Also in debtors_master
				//address 1&2, city, state, postal, country in 1
		$this->fields_array[] = array( 'name' => 'phone', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'phone2', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'fax', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'email', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'lang', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'notes', 'label' => '', 'type' => $descl, 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );	//<@ Also in debtors_master, cust_branch
		$this->fields_array[] = array( 'name' => 'inactive', 'label' => '', 'type' => 'bool', 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );	//<@ Also in debtors_master

		$this->table_details['primarykey'] = "id";
	}


}

