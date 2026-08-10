<?php

require_once( 'class.fa_table_wrapper.php' );

$path_to_root="../..";


/********************************************************//**
 * Various modules need to be able to get info about purchase order details from FA
 *
 *	This class uses FA specific routines (display_notification etc)
 *	This is a wrapper for the FA table.
 *
 * **********************************************************/
class fa_document_type extends fa_table_wrapper
{
	var $min_cid;
	var $max_cid;
	var $errors = array();
	var $warnings = array();

	/*
	 *
 id_document_type | int(11) unsigned | NO   | PRI | NULL       | auto_increment |
| document_type    | varchar(64)      | NO   |     | NULL       |                |
| name             | varchar(20)      | NO   |     | NULL       |                |
| description      | varchar(60)      | NO   |     | NULL       |                |
| effective_date   | date             | NO   | MUL | 0000-00-00 |                |
| end_date         | date             | NO   |     | 0000-00-00 |                |
| reference        | varchar(60)      | NO   |     |            |                |
	 * 
	 * */
	protected $id_document_type ;// int(11) unsigned | NO   | PRI | NULL       | auto_increment |
	protected $document_type    ;// varchar(64)      | NO   |     | NULL       |                |
	protected $name             ;// varchar(20)      | NO   |     | NULL       |                |
	protected $description      ;// varchar(60)      | NO   |     | NULL       |                |
	protected $effective_date   ;// date             | NO   | MUL | 0000-00-00 |                |
	protected $end_date         ;// date             | NO   |     | 0000-00-00 |                |
	protected $reference        ;// varchar(60)      | NO   |     |            |                |


	//function __construct( /*$prefs_db*/ )
	function __construct( $caller = null )
	{
		//parent::__construct( $prefs_db );
		parent::__construct( $caller );
		$descl = 'varchar(' . DESCRIPTION_LENGTH . ')';
		$acctl = 'varchar(' . ACCOUNTCODE_LENGTH . ')';
		$this->table_details['tablename'] = TB_PREF . 'dimensions';

		$this->fields_array[] = array('name' => 'id       ', 'type' => 'int(11)    ', 'null' => 'NOT NULL',   'readwrite' => 'readwrite', );	//PRI | NULL       | auto_increment |
		$this->fields_array[] = array('name' => 'reference', 'type' => 'varchar(60)', 'null' => 'NOT NULL',   'readwrite' => 'readwrite', );	//UNI |            |                |
		$this->fields_array[] = array('name' => 'name     ', 'type' => 'varchar(60)', 'null' => 'NOT NULL',   'readwrite' => 'readwrite', );	//    |            |                |
		$this->fields_array[] = array('name' => 'type_    ', 'type' => 'bool ', 'null' => 'NOT NULL',   'readwrite' => 'readwrite', );	//MUL | 1          |                |
		$this->fields_array[] = array('name' => 'closed   ', 'type' => 'bool ', 'null' => 'NOT NULL',   'readwrite' => 'readwrite', );	//    | 0          |                |
		$this->fields_array[] = array('name' => 'date_    ', 'type' => 'date       ', 'null' => 'NOT NULL',   'readwrite' => 'readwrite', );	//MUL | 0000-00-00 |                |
		$this->fields_array[] = array('name' => 'due_date ', 'type' => 'date       ', 'null' => 'NOT NULL',   'readwrite' => 'readwrite', );	//MUL | 0000-00-00 |                |

		$this->table_details['primarykey'] = "category_id";
	}
	function getAll()
	{
		$this->from_array[] = $this->table_details['tablename'];
		$this->buildSelectQuery();
		$this->query( __METHOD__ . " couldn't get ALL dimensions" );
		return $this->query_result;
	}
	/**/
}

