<?php

require_once( 'class.origin.php' );
require_once( 'defines.inc.php' );
require_once( 'class.kfLog.php' );

$path_to_faroot = dirname ( realpath ( __FILE__ ) ) . "/../../";
//global $path_to_root;
//require_once( $path_to_faroot . '/includes/db_pager.inc' );
/*****************************************************************************//**
 * A table to deal with talking to a table in a database
 *
 * A lot of the code inherited from generictable class framework, migrated
 * out of table_interface and woo_interface.
 *
 * Inherits:
 * Provides:
 * *****************************************************************************/
class MODEL extends origin
{
	var $client;
	var $caller;
	var $view;

	var $db_column_name = array();
	var $db_result;			//MYSQL Result pointer
	var $db_table_pager; 		// = & new_db_pager( $this->table_name, $this->sql, $this->col_array );
	var $table_width;
	var $db_insert_id;	//!< int set by $this->insert_table();

	var $table_details;	//!< array definition details about the table
	var $properties_array;	//!< array
	var $fields_array;	//!< array
	/***GENERICTABLE***/
	var $select_array;
	var $from_array;
	var $where_array;
	var $groupby_array;
	var $having_array;
	var $orderby_array;
	var $sort_dir;			//!< ASC or DESC
	var $limit;
	var $limit_startrow;		//!< int to build a LIMIT statement - which row to start at (multi-page)
	var $limit_numberrows;		//!< int to build a LIMIT statement - how many rows to return
	var $number_rows_affected;
	var $querytime;
	var $join_array;
	protected $select_clause;	//!< string SQL sub-string (clause)
	protected $from_clause;		//!< string SQL sub-string (clause)
	protected $where_clause;	//!< string SQL sub-string (clause)
	protected $groupby_clause;	//!< string SQL sub-string (clause)
	protected $having_clause;	//!< string SQL sub-string (clause)
	protected $orderby_clause;	//!< string SQL sub-string (clause)
	protected $limit_clause;	//!< string SQL sub-string (clause)
	protected $join_clause;
	protected $query_result;
	/***!GENERICTABLE***/
	protected $objLog;


	function __construct( $client = null )
	{
		if( isset( $client ) )
			$this->set( "client", $client, false );
		if( isset( $client->view ) )
			$this->view = $client->view;

		$this->db_insert_id = null;
		if( !isset( $this->table_details ) )
			$this->table_details = array();
		if( !isset( $this->properties_array ) )
			$this->properties_array = array();
		if( !isset( $this->fields_array ) )
                       $this->fields_array = array();
		$this->objLog = new kfLog();
		$this->define_table();

	}
	function __destruct()
	{
		parent::__destruct();
	}
	function run( $action )
	{
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Entering ");
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Exiting ");
	}
	function backtrace()
	{
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Entering ");
		echo "<br />";
		array_walk(debug_backtrace(),create_function('$a,$b','print "{$a[\'function\']}()(".basename($a[\'file\']).":{$a[\'line\']});<br /> ";'));
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Exiting ");
	}
	/***************************************************************//**
         *build_interestedin
         *
         *      This function builds the table of events that we
         *      want to react to and what handlers we are passing the
         *      data to so we can react.
         * ******************************************************************/
        function build_interestedin()
        {
       		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Entering ");
         	$this->interestedin['NOTIFY_INIT_TABLES']['function'] = "create_table";
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Exiting ");
        }
	function db_pager( $model )
	{
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Entering ");
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Exiting ");
	}
	//This is VIEW functionality
	function db_result2rows()
	{
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Entering ");
		if( isset( $this->db_result ) )
		{
			$k = 0;
			while ($myrow = db_fetch($result))
			{
			        alt_table_row_color($k);
				foreach( $this->header_row as $col )
				{
					if( $this->col_type[$col] == "amount" )
					{
						amount_cell( $myrow[$col] );
					}
					else if( $this->col_type[$col] == "date" )
					{
						label_cell( sql2date( $myrow[$col] ) );
					}
					else if( $this->col_type[$col] == "edit" )
					{
			        		edit_button_cell("Edit" . $myrow['_id'], _("Edit"));
					}
					else if( $this->col_type[$col] == "delete" )
					{
			        		delete_button_cell("Delete" . $myrow['_id'], _("Delete"));
					}
					else if( $this->col_type[$col] == "inactive" )
					{
			        		inactive_control_cell($myrow["_id"], $myrow["inactive"], 'assets', '_id');
					}
					else
					{
						label_cell( $myrow[$col] );
					}
				}
			        end_row();
			}

		}
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Exiting ");
	}
	/*********************************************//**
	 * Set a variable.  Throws exceptions on sanity checks
	 *
	 * 
	 * @param field string Variable to be set
	 * @param value ... value for variable to be set
 	 * @param native... bool enforce only the variables of the class itself.  default TRUE, which will break code.
	 * @return bool Did we set the value
	 * **********************************************/
	/*@bool@*/function set( $field, $value = null, $enforce_only_native_vars = true )//:bool
	{
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Entering ");
		if( !isset( $field )  )
		{
			$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Exiting ");
			throw new Exception( "Fcn VAR Field not set", KSF_VAR_NOT_SET );
		}
		$valid = -1;
		try 
		{
			$row = $this->get_fields_array_row( $field );
			$valid = $this->validate( $value, $row['type'] );
		} catch( Exception $e )
		{
			$this->tell_eventloop( $this, 'NOTIFY_LOG_ERROR', $e->getMessage() );
			//If we caught an exception the field wasn't found
			if( true == $enforce_only_native_vars )
			{
				$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Exiting ");
				return false;
			}
			else
			{
				//If not enforcing carry on.
			}
		}
		try 
		{
			if( $valid <> 0  )
			{
				//Having looped through fields_array validates the field belongs so setting FALSE on parent call
				//OR we aren't enforcing anyway
				$ret = parent::set( $field, $value, false );
				$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Exiting ");
				return $ret;
			}
			else
			{
				$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Value wasn't of valid data type for the field");
				$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Exiting ");
				return false;
			}
		} catch( Exception $e )
		{
			$this->tell_eventloop( $this, 'NOTIFY_LOG_ERROR', $e->getMessage() );
		}
		//We should never reach this point.
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Exiting ");
		return false;
	}
	/*******************************************************//**
	 * Search for the fields_array row that matches the field
	 *
	 * Throws an exception on not finding the answer
	 *
	 * @param field string name of field to seek in definition
	 * @returns row array of definition data
	 * ********************************************************/
	function get_fields_array_row( $field )
	{
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Entering ");
		if( ! isset( $this->fields_array ) )
		{
			$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Exiting via EXCEPTION");
			throw new Exception( "Class field fields_array not set", KSF_FIELD_NOT_SET );
		}
		foreach( $this->fields_array as $row )
		{
			if( $field == $row['name'] )
			{
				$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Exiting");
				return $row;
			}
		}
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Exiting via EXCEPTION");
		throw new Exception( "Searched for VAR not found", KSF_SEARCHED_VALUE_NOT_FOUND );
	}
	/*******************************************************************************************//**
	 * Sanity check on passed in data versus table definition (data dictionary)
	 *
	 * @param mixed the field value
	 * @param mixed the definition we used to create the DB table
	 * @returns bool is it of reasonable values...
	 * **********************************************************************************************/
	/*@bool@*/function validate( $data_value, $data_type )
	{
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Entering ");
		if( strncasecmp( $data_type, "int(1)", 6 ) )
			$data_type = 'digit';
		if( strncasecmp( $data_type, "int", 3 ) )
			$data_type = 'int';
		if( strncasecmp( $data_type, "varc", 4 ) )
			$data_type = 'string';
		switch($data_type)
		{
			case 'bool':
			case 'boolean':
					if( $data_value === true )
					{
						$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Exiting ");
						return true;
					}
					if( $data_value === false )
					{
						$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Exiting ");
						return true;
					}
					if( $data_value == 0 )
					{
						$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Exiting ");
						return true;
					}
					if( $data_value == 1 ) 
					{
						$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Exiting ");
						return true;
					}
					throw new InvalidArgumentException("Expected Boolean.  Received " . $data_value);
					break;	//fall out of switch and return false
			case 'string':
				$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Exiting ");
				return is_string( $data_value );
				break;
			case 'digit':
				if( $data_value >= 0 AND $data_value <= 9 )
				{
					$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Exiting ");
					return true;
				}
				$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Exiting ");
				return false;
				break;	//fall out of switch and return false
			case 'int':
				if( is_int( $data_value ) ) 
				{
					return true;
				}
				else 
					throw new InvalidArgumentException("Expected INT.  Received " . $data_value);
				break;
			default:
				$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Exiting ");
				return true;	//data type not found
		}
		//throw new InvalidArgumentException();
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Exiting ");
		return false;
		/*
			BadFunctionCallException
			BadMethodCallException
			DomainException
			InvalidArgumentException
			LengthException
			LogicException
			OutOfBoundsException
			OutOfRangeException
			OverflowException
			RangeException
			RuntimeException
			UnderflowException
			UnexpectedValueException
		 * 
		 * */
	}
	/***************************************************************************************************************//**
	 * Select a row from the table.  Requires that the prikey is set.
	 *
	 * Doesn't consider foreign keys (recursive)
	 * Throws exceptions
	 * ***************************************************************************************************************/
	/*none*/function select_row( $unused )
	{
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Entering ");
		if( isset( $this->table_details['primarykey'] ) )
			$key = $this->table_details['primarykey'];
		else
		{
			$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Exiting via EXCEPTION");
			throw new Exception( "Primary Key not defined.  This function uses that field in the query", KSF_PRIKEY_NOT_DEFINED );
		}
		if( ! isset( $this->$key ) )
		{
			$this->tell_eventloop( $this, 'NOTIFY_LOG_DEBUG', print_r( $this->table_details, true ) );
			throw new Exception( "Primary Key not set.  Required Field for this function", KSF_PRIKEY_NOT_SET );
		}
		$sql = "SELECT * from `" . $this->table_details['tablename'] . "` WHERE $key='" . $this->$key . "'";
		$res = db_query( $sql, "Couldn't select from " . $this->table_details['tablename'] );
		$row = db_fetch( $res );
		foreach( $this->fields_array as $def )
		{
			$name = $def['name'];
			if( isset( $row[$name] ) )
			{
				$this->set( $name, $row[$name] );
			}
		}
		$this->tell_eventloop( $this, 'NOTIFY_LOG_DEBUG', __METHOD__ . ":" . __LINE__ . " Exiting " );
	}
	
	/*******************************************************************************************************************//**
	 * Select from the table with defined values.  NOT setup to handle joins or subselects
	 *
	 * @param array list of fields to select array( 'fieldname' => 'select as name' )
	 * @param array list of WHERE conditions array( 'field1' => 'equal to value' )
	 * @param array list of fields  array( 'field1', 'field2', ...)
	 * @param int limit
	 * @returns mysql_result
	*******************************************************************************************************************/
	/*@mysql_result@*/function select_table($fieldlist = "*", /*@array@*/$where = null, /*@array@*/$orderby = null, /*@int@*/$limit = null)
	{
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Entering ");
		$whereset = FALSE;
		$sql = "SELECT " . $fieldlist . " from `" . $this->table_details['tablename'] . "`";
		if( isset( $where ) )
		{
			foreach( $where as $key => $value )
			{
				//Allow a 1 dimensional array for WHERE
				if( strlen( $value ) < 1 )
				{
					if( isset( $this->$key ) )
					{
						$value = $this->$key;
					}
				}
				if( strlen( $key ) > 1 AND strlen( $value ) > 1 )
				{
					if( in_array( $key, $this->properties_array ) )
					{
						if( $whereset == FALSE )
						{
							$sql .= " WHERE ";
							$whereset = TRUE;
						}
						else
						{
							//each WHERE needs to be split by an AND
							$sql .= " AND ";
						}
						$sql .= $key .= "=" . db_encode( $value );
					}
				}
			}
		}
		if( isset( $orderby ) )
		{
			$orderset = FALSE;
			foreach( $orderby as $key )
			{
				if( strlen( $key ) > 1 )
				{
					if( in_array( $key, $this->properties_array ) )
					{
						if( $orderset == TRUE )
						{
							$sql .= ", ";
							
						}
						else
						{
							//First orderby doesn't need a comma
							$sql .= " ";
							$orderset = TRUE;
						}
						$sql .= $key;
					}
				}
			}
		}
		if( isset( $limit ) )
			$sql .= " LIMIT " . $limit;
		$res = db_query( $sql, "Couldn't select from " . $this->table_details['tablename'] );
		$this->tell_eventloop( $this, 'NOTIFY_LOG_DEBUG', __METHOD__ . ":" . __LINE__ . " Exiting " );
		return $res;
	}
	function query( $msg )
	{
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Entering ");
		$this->query_result = db_query( $this->sql, $msg );
		$this->tell_eventloop( $this, 'NOTIFY_LOG_DEBUG', __METHOD__ . ":" . __LINE__ . " Exiting " );
		return $this->query_result;
	}

	/***************************************************************************************//**
	 * Delete a row in the table as long as the prikey has a value set
	 *
	 * Will eventually throw exceptions!
	 *
	 * *****************************************************************************************/
	function delete_table()
	{
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Entering ");
		$pri = $this->table_details['primarykey'];
		if( !isset( $this->$pri ) )
		{
			$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Exiting via EXCEPTION");
			throw new Exception( "Primary Key not set.  This function requires that key", KSF_PRIKEY_NOT_SET );
		}
		$sql = "DELETE from " . $this->table_details['tablename'] . " WHERE " . $pri . " = '" . $this->$pri . "'";
		db_query( $sql, "Couldn't update table " . $this->table_details['tablename'] . " for key " .  $pri );	
		$this->tell_eventloop( $this, 'NOTIFY_LOG_DEBUG', __METHOD__ . ":" . __LINE__ . " Exiting " );
	}
	/***************************************************************************************//**
	 * Update a row in the table as long as the prikey has a value set
	 *
	 * Will eventually throw exceptions!
	 *
	 *@return integer primary key
	 * *****************************************************************************************/
	function update_table()
	{
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Entering ");
		$pri = $this->table_details['primarykey'];
		if( !isset( $this->$pri ) )
		{
			$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Exiting via EXCEPTION");
			throw new Exception( "Primary Key field is not set.  This function requires that key", KSF_PRIKEY_NOT_SET );
			//return -1;
		}
		$sql = "UPDATE `" . $this->table_details['tablename'] . "` set" . "\n";
		$fieldcount = 0;
		foreach( $this->fields_array as $row )
		{
			if( $row['name'] != $this->table_details['primarykey'] )
			{
				if( isset( $this->$row['name'] ) )
				{
					if( $fieldcount > 0 )
						$sql .= ", ";
					$sql .= "`" . $row['name'] . "`=" . db_escape($this->$row['name']);
					$fieldcount++;
				}
			}
		}
		$sql .= " WHERE '" . $pri . "'='" . $this->$pri . "'";
		db_query( $sql, "Couldn't update table " . $this->table_details['tablename'] . " for key " .  $pri );	
		$this->tell_eventloop( $this, 'NOTIFY_LOG_DEBUG', __METHOD__ . ":" . __LINE__ . " Exiting " );
		return $pri;
	}
	/*****************************************************************************//**
	 *
	 *
	 * @returns bool did we find the key
	 * ******************************************************************************/
	/*@bool@*/function check_table_for_id()
	{
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Entering ");
		if( isset( $this->table_details['primarykey'] ) )
		{
			$prikey = $this->table_details['primarykey'];
		}
		//check to see if we have the id in a record
		$go = false;
		foreach( $this->fields_array as $row )
		{
			if( isset( $this->$row['name'] ) )
			{
				if( $this->$row['name'] == 'id' )
				{
					$go = true;
					$prikey = $this->$row['name'];
				}
			}
		}
		if( $go )
		{
			$sql = "select count('id') as count from $this->table_details['tablename']";
			$sql .= " WHERE " . $prikey . " = '" . $this->id . "'";
			$res = db_query( $sql, "Couldn't check for count in table " . $this->table_details['tablename'] . " with " .  $sql );	
			$count = db_fetch_assoc( $res );
			if( $count['count'] > 0 )
			{
				$this->tell_eventloop( $this, 'NOTIFY_LOG_DEBUG', __METHOD__ . ":" . __LINE__ . " Exiting " );
				return TRUE;
			}
			else
			{
				$this->tell_eventloop( $this, 'NOTIFY_LOG_DEBUG', __METHOD__ . ":" . __LINE__ . " Exiting " );
				return FALSE;
			}
		}
		$this->tell_eventloop( $this, 'NOTIFY_LOG_DEBUG', __METHOD__ . ":" . __LINE__ . " Exiting " );
		return FALSE;
	}
	/*******************************************************************************************************//**
	 *
	 *
	 * This function uses mysql_real_escape_string which is depreciated in 5.5 and removed in php 7.
	 * @return int Index of last insert. -1 on failure
	 * *********************************************************************************************************/
	/*int index of last insert*/
	/*@int@*/function insert_table()
	{
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Entering ");
		//display_notification( __FILE__ . "::" . __CLASS__ . "::"  . __METHOD__ . ":" . __LINE__, "WARN" );
		global $db_connection;
		$sql = "INSERT IGNORE INTO `" . $this->table_details['tablename'] . "`" . "\n";
		$fieldcount = 0;
		$fields = "(";
		$values = "values(";
		foreach( $this->fields_array as $row )
		{
			if( isset( $this->$row['name'] ) )
			{
				if( $fieldcount > 0 )
				{
					$fields .= ", ";
					$values .= ", ";
				}
				$fields .= "`" . $row['name'] . "`";
				$values .=  db_escape($this->$row['name']);
				$fieldcount++;
			}
		}
		$fields .= ")";
		$values .= ")";
		$sql .= $fields . $values;
		//var_dump( $sql );
		$this->db_insert_id = -1;
		if( $fieldcount > 0 )
			db_query( $sql, "Couldn't insert into table " . $this->table_details['tablename'] . " for " .  $sql );	
		else
			display_error( "No values set so couldn't insert" );
		$this->db_insert_id = db_insert_id();
		$this->tell_eventloop( $this, 'NOTIFY_LOG_DEBUG', __METHOD__ . ":" . __LINE__ . " Exiting " );
		return $this->db_insert_id;
	}
	function create_table()
	{
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Entering ");
		if( ! isset( $this->table_details['tablename'] ) )
		{
			if( method_exists( $this, 'define_table' ) )
				$this->define_table();
			else
			{
				$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Exiting via EXCEPTION");
				throw new Exception( "Table Definition not defined so can't create table", KSF_TABLE_NOT_DEFINED );
			}
		}
		$sql = "CREATE TABLE IF NOT EXISTS `" . $this->table_details['tablename'] . "` (" . "\n";
		$fieldcount = 0;
		foreach( $this->fields_array as $row )
		{
			if( $fieldcount > 0 )
			{
				$sql .= ",";
			}
			$sql .= "`" . $row['name'] . "` " . $row['type'];
			if( isset( $row['null'] ) )
				$sql .= " " . $row['null'];
			if( isset( $row['auto_increment'] ) )
				$sql .= " AUTO_INCREMENT";
			if( isset( $row['default'] ) )
				$sql .= " DEFAULT " . $row['default'];
			$fieldcount++;
		}
		if( isset( $this->table_details['primarykey'] ) )
		{
			if( $fieldcount > 0 )
			{
				$sql .= ",";
			}
		
			$sql .= " Primary KEY (`" . $this->table_details['primarykey'] . "`)";
		}
		else
		{
			//$sql .= " Primary KEY (`" . $fields_array[0]['name'] . "`)";
		}
		if( isset( $this->table_details['index'] ) )
		{
			foreach( $this->table_details['index'] as $index )
			{
				if( $index['type'] == "unique")
				{
					$sql .= ", UNIQUE KEY `" . $index['keyname'] . "` ( " . $index['columns'] . " )";
				}
				else
					//$sql .= ", INDEX " . $index['keyname'] . "( " . $index['columns'] . " )";
					$sql .= ", UNIQUE KEY `" . $index['keyname'] . "` ( " . $index['columns'] . " )";
			}
		}
		$sql .= " )";
		if( isset( $this->table_details['engine'] ) )
		{
			$sql .= " ENGINE=" . $this->table_details['engine'] . "";
		}
		else
		{
			$sql .= " ENGINE=MyISAM";
		}
		if( isset( $this->table_details['charset'] ) )
		{
			$sql .= " DEFAULT CHARSET=" . $this->table_details['charset'] . ";";
		}
		else
		{
			$sql .= " DEFAULT CHARSET=utf8;";
		}
		//var_dump( $sql );
		$this->tell_eventloop( $this, 'NOTIFY_LOG_DEBUG', __METHOD__ . ":" . __LINE__ . " Creating table " . $this->table_details['tablename'] );
		db_query( $sql, "Couldn't create table " . $this->table_details['tablename'] );
		$ret = $this->alter_table();
		$this->tell_eventloop( $this, 'NOTIFY_LOG_DEBUG', __METHOD__ . ":" . __LINE__ . " Exiting " );
		return $ret;
	}
	function alter_table()
	{
		$this->tell_eventloop( $this, 'NOTIFY_LOG_DEBUG', __METHOD__ . ":" . __LINE__ . " Entering" );
		//Need a function for doing updates/upgrades between versions.
		//ASSUMPTION:
		//	create_table as been run, and if not exist may
		//	or may not have triggered.  Regardless we are
		//	going to ALTER table to ensure all INDEXES are
		//	created and all COLUMNS exist.
		//
		//	ALTER TABLE tablename
		//		ADD COLUMN (colname colspec, colname2 colspec2)
		$sql = "ALTER TABLE `" . $this->table_details['tablename'] . "`" . "\n";
		$fieldcount = 0;
		$col = "ADD COLUMN (";
		$endcol = ")";
		$col_data = "";
		foreach( $this->fields_array as $row )
		{
			if( $fieldcount > 0 )
			{
				$col_data .= ",";
			}
			$col_data .= "`" . $row['name'] . "` " . $row['type'];
			if( isset( $row['null'] ) )
				$col_data .= " " . $row['null'];
			if( isset( $row['auto_increment'] ) )
				$col_data .= " AUTO_INCREMENT";
			if( isset( $row['default'] ) )
				$col_data .= " DEFAULT " . $row['default'];
			$fieldcount++;
		}
		if( $fieldcount > 0 )
			$col .= $col_data . $endcol;
		//ASSUMING the primary key was generated with the table and no changes since.
	/*
		if( isset( $this->table_details['index'] ) )
		{
			foreach( $this->table_details['index'] as $index )
			{
				if( $index['type'] == "unique")
				{
					$sql .= ", UNIQUE KEY `" . $index['keyname'] . "` ( " . $index['columns'] . " )";
				}
				else
					//$sql .= ", INDEX " . $index['keyname'] . "( " . $index['columns'] . " )";
					$sql .= ", UNIQUE KEY `" . $index['keyname'] . "` ( " . $index['columns'] . " )";
			}
		}
		$sql .= " )";
	 */
		//ASSUMING no changes to the engine nor charset
		//var_dump( $sql );
		$this->tell_eventloop( $this, 'NOTIFY_LOG_DEBUG', __METHOD__ . ":" . __LINE__ . " Altering table " . $this->table_details['tablename'] );
		$ret = db_query( $sql, "Couldn't alter table " . $this->table_details['tablename'] );
		$this->tell_eventloop( $this, 'NOTIFY_LOG_DEBUG', __METHOD__ . ":" . __LINE__ . " Exiting " );
		return $ret;
	}
	/*****************************************************************************************//**
	 * Count the number of rows in the table.
	 *
	 * @returns int number of rows
	 * ******************************************************************************************/
	/*@int@*/function count_rows()
	{
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Entering ");
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Exiting ");
		$res = db_query( "select count(*) from " . $this->table_details['tablename'], "Couldn't count rows in " . $this->table_details['tablename'] );
		$count = db_fetch_row( $res );
		$this->tell_eventloop( $this, 'NOTIFY_LOG_DEBUG', __METHOD__ . ":" . __LINE__ . " Exiting " );
		return $count[0];
	}
	/*****************************************************************************************//**
	 * Count the number of rows in the table filtered by criteria
	 *
	 * @TODO refactor this and count_rows to use array of query fields...
	 *
	 * @params string where criteria w/o leading "where"
	 * @returns int number of rows
	 * ******************************************************************************************/
	/*@int@*/function count_filtered($where = null)
	{
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Entering ");
		if( !isset( $where ) )
		{
			$ret = $this->count_rows();
			$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Exiting ");
			return $ret;
		}
		$res = db_query( "select count(*) from " . $this->table_details['tablename'] . " where " . $where, "Couldn't count rows in " . $this->table_details['tablename'] );
		$count = db_fetch_row( $res );
		$this->tell_eventloop( $this, 'NOTIFY_LOG_DEBUG', __METHOD__ . ":" . __LINE__ . " Exiting " );
		return $count[0];
	}
	/*string*/function getPrimaryKey()
	{
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Entering ");
		if( isset( $this->table_details['primarykey'] ) )
		{
			$this->tell_eventloop( $this, 'NOTIFY_LOG_DEBUG', __METHOD__ . ":" . __LINE__ . " Exiting " );
			return $this->table_details['primarykey'];
		}
		else
		{
			$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Exiting via EXCEPTION");
			throw new Exception( "Primary Key Not Set", KSF_PRIKEY_NOT_DEFINED );
		}
	}
	/*none*/function getByPrimaryKey()
	{
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Entering ");
		/*
		$fields = "*";	//comma separated list
		$prikey = $this->getPrimaryKey();
		$where = array( $prikey );
		$orderby = array();
		$limit = null;	//int
		return $this->select_table( $fields, $where, $orderby, $limit );
		*/
		$this->select_row();
		$this->tell_eventloop( $this, 'NOTIFY_LOG_DEBUG', __METHOD__ . ":" . __LINE__ . " Exiting " );
	}
	/***GENERICTABLE***/
	/*****************************************//**
	 * Build the LIMIT clause
	 *
	 * adapted from legacy GENERICTABLE
	 *
	 * @param NONE but uses internal variables
	 * @return NONE but sets limit
	 * *****************************************/
	function buildLimit()
	{
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Entering ");
		if( (strlen( $this->limit_startrow ) < 1) 
			OR ($this->limit_startrow < 0) 
		  )
		{
			$this->limit_startrow = 0;
		}
		if( strlen( $this->limit_numberrows ) > 0 )
		{
			//If no upper set, don't set a limit
			$this->limit = (int)$this->limit_startrow - 1 . "," . (int)$this->limit_numberrows;
		}
		$this->limit_clause = $this->limit;
		$this->tell_eventloop( $this, 'NOTIFY_LOG_DEBUG', __METHOD__ . ":" . __LINE__ . " Exiting " );
	}
	/*****************************************//**
	 * Build the REPLACE STATEMENT
	 *
	 * repalce is an extension in mysql that does either an insert or a delete then insert.
	 *
	 * @param bool should we check the table definition that all variables are in it.  Prevents joined replaces.
	 * @param NONE but uses internal variables
	 * *****************************************/
	function ReplaceQuery( $b_validate_in_table = false)
	{
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Entering ");
		global $db_connection;
		$sql = "REPLACE INTO `" . $this->table_details['tablename'] . "`" . "\n";
		$fieldcount = 0;
		$fields = "(";
		$values = "values(";
		foreach( $this->fields_array as $row )
		{
			if( isset( $this->$row['name'] ) )
			{
				if( $fieldcount > 0 )
				{
					$fields .= ", ";
					$values .= ", ";
				}
				$fields .= "`" . $row['name'] . "`";
				$values .=  db_escape($this->$row['name']);
				$fieldcount++;
			}
		}
		$fields .= ")";
		$values .= ")";
		$sql .= $fields . $values;
		if( $fieldcount > 0 )
			db_query( $sql, "Couldn't replace into table " . $this->table_details['tablename'] . " for " .  $sql );	
		else
			$this->tell_eventloop( $this, 'NOTIFY_LOG_DEBUG', __METHOD__ . ":" . __LINE__ . " Exiting " );
		return;
	}
	/*****************************************//**
	 * Build the SELECT clause
	 *
	 * adapted from legacy GENERICTABLE
	 *
	 * @param bool should we check the table definition that all variables are in it.  Prevents joined selects.
	 * @param NONE but uses internal variables
	 * @return NONE but sets limit
	 * *****************************************/
	function buildSelect( $b_validate_in_table = false)
	{
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Entering ");
		/**/
		if( null === $this->select_array )
		{
			$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Exiting via EXCEPTION");
			throw new Exception( "Required Field not set", KSF_FIELD_NOT_SET );
		}
		$fieldcount = 0;
		$sql = "";
		foreach( $this->select_array as $val )
		{
			if( $b_validate_in_table )
			{
				//if( $val != "*"  AND ! in_array( $tabledef, $val ) )
				//	throw new Exception( "Select variable not in table definition", KSF_FIELD_NOT_CLASS_VAR );
			}
			if( 0 < $fieldcount )
				$sql .= ", ";
			$sql .= $val;
			$fieldcount++;
		}
		$this->select_clause = "SELECT " . $sql;
 		/**/
		$this->tell_eventloop( $this, 'NOTIFY_LOG_DEBUG', __METHOD__ . ":" . __LINE__ . " Exiting " );
	}	
	/*****************************************//**
	 * Build the FROM clause
	 *
	 * adapted from legacy GENERICTABLE
	 *
	 * @param bool should we check the table definition that all variables are in it.  Prevents joined selects.
	 * @param NONE but uses internal variables
	 * @return NONE but sets limit
	 * *****************************************/
	function buildFrom()
	{
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Entering ");

		/**/
		if( null === $this->from_array )
		{
			$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Exiting via EXCEPTION");
			throw new Exception( "Required Field not set", KSF_FIELD_NOT_SET );
		}
		$fieldcount = 0;
		$sql = "";
		foreach( $this->from_array as $val )
		{
			if( 0 < $fieldcount )
				$sql .= ", ";
			$sql .= $val;
			$fieldcount++;
		}
		$this->from_clause = " FROM " . $sql;
 		/**/
		$this->tell_eventloop( $this, 'NOTIFY_LOG_DEBUG', __METHOD__ . ":" . __LINE__ . " Exiting " );
	}	
	/*****************************************//**
	 * Build the WHERE clause
	 *
	 * adapted from legacy GENERICTABLE
	 *
	 * @param bool should we check the table definition that all variables are in it.  Prevents joined selects.
	 * @param NONE but uses internal variables
	 * @return NONE but sets limit
	 * *****************************************/
	function buildWhere( $b_validate_in_table = false)
	{
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Entering ");
		/**/
		if( null === $this->where_array )
		{
			$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Exiting via EXCEPTION");
			throw new Exception( "Required Field not set", KSF_FIELD_NOT_SET );
		}
		if( count( $this->where_array ) < 1 )
		{
			$this->tell_eventloop( $this, 'NOTIFY_LOG_DEBUG', __METHOD__ . ":" . __LINE__ . " Exiting " );
			return;
		}
		$fieldcount = 0;
		$sql = "";
		if( isset( $this->where_array ) AND is_array( $this->where_array ) )
		{
			foreach( $this->where_array as $col => $val )
			{
				if( $b_validate_in_table )
				{
				//	if( $val != "*"  AND ! in_array( $tabledef, $val ) )
				//		throw new Exception( "Select variable not in table definition", KSF_FIELD_NOT_CLASS_VAR );
				}
				if( 0 < $fieldcount )
					$sql .= " and ";
				$sql .= "$col = '$val' ";
				$fieldcount++;
			}
		}
		$this->where_clause = " WHERE " . $sql;
 		/**/
		$this->tell_eventloop( $this, 'NOTIFY_LOG_DEBUG', __METHOD__ . ":" . __LINE__ . " Exiting " );
	}	
	/*****************************************//**
	 * Build the ORDERBY clause
	 *
	 * adapted from legacy GENERICTABLE
	 *
	 * @param bool should we check the table definition that all variables are in it.  Prevents joined selects.
	 * @param NONE but uses internal variables
	 * @return NONE but sets limit
	 * *****************************************/
	function buildOrderBy( $b_validate_in_table = false)
	{
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Entering ");
		/**/
		if( null === $this->orderby_array )
		{
			$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Exiting via EXCEPTION");
			throw new Exception( "Required Field not set", KSF_FIELD_NOT_SET );
		}
		if( count( $this->orderby_array ) < 1 )
		{
			$this->tell_eventloop( $this, 'NOTIFY_LOG_DEBUG', __METHOD__ . ":" . __LINE__ . " Exiting " );
			return;
		}
		$fieldcount = 0;
		$sql = "";
		if( isset( $this->orderby_array ) AND is_array( $this->orderby_array ))
		{
			foreach( $this->orderby_array as $col )
			{
				if( $b_validate_in_table )
				{
					
				//	if( $col != "*"  AND ! in_array( $tabledef, $col ) )
				//		throw new Exception( "Select variable not in table definition", KSF_FIELD_NOT_CLASS_VAR );
 				
				}
				if( 0 < $fieldcount )
					$sql .= ", ";
				$sql .= " '$col' ";
				$fieldcount++;
			}
		}
		$this->orderby_clause = " ORDER BY " . $sql;
 		/**/
		$this->tell_eventloop( $this, 'NOTIFY_LOG_DEBUG', __METHOD__ . ":" . __LINE__ . " Exiting " );
	}	
	/*****************************************//**
	 * Build the GROUPBY clause
	 *
	 * adapted from legacy GENERICTABLE
	 *
	 * @param bool should we check the table definition that all variables are in it.  Prevents joined selects.
	 * @param NONE but uses internal variables
	 * @return NONE but sets limit
	 * *****************************************/
	function buildGroupBy( $b_validate_in_table = false)
	{
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Entering ");
		/**/
		if( null === $this->groupby_array )
		{
			$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Exiting via EXCEPTION");
			throw new Exception( "Required Field not set", KSF_FIELD_NOT_SET );
		}
		if( count( $this->groupby_array ) < 1 )
		{
			$this->tell_eventloop( $this, 'NOTIFY_LOG_DEBUG', __METHOD__ . ":" . __LINE__ . " Exiting " );
			return;
		}
		$fieldcount = 0;
		$sql = "";
		if( isset( $this->groupby_array ) AND is_array( $this->groupby_array ))
		{
			foreach( $this->groupby_array as $col )
			{
				if( $b_validate_in_table )
				{
				//	if( $col != "*"  AND ! in_array( $tabledef, $col ) )
				//		throw new Exception( "Select variable not in table definition", KSF_FIELD_NOT_CLASS_VAR );
				}
				if( 0 < $fieldcount )
					$sql .= ", ";
				$sql .= " '$col' ";
				$fieldcount++;
			}
		}
		$this->groupby_clause = " GROUP BY " . $sql;
 		/**/
		$this->tell_eventloop( $this, 'NOTIFY_LOG_DEBUG', __METHOD__ . ":" . __LINE__ . " Exiting " );
	}	
	/*****************************************//**
	 * Build the HAVING clause
	 *
	 * adapted from legacy GENERICTABLE
	 *
	 * HAVING was added because WHERE conditions can't be used with aggregate functions.
	 * e.g having count(orders) > 10
	 *
	 * @param bool should we check the table definition that all variables are in it.  Prevents joined selects.
	 * @param NONE but uses internal variables
	 * @return NONE but sets limit
	 * *****************************************/
	function buildHaving( )
	{
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Entering ");
		/**/
		if( null === $this->having_array )
		{
			$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Exiting via EXCEPTION");
			throw new Exception( "Required Field not set", KSF_FIELD_NOT_SET );
		}
		if( count( $this->having_array ) < 1 )
		{
			$this->tell_eventloop( $this, 'NOTIFY_LOG_DEBUG', __METHOD__ . ":" . __LINE__ . " Exiting " );
			return;
		}
		$fieldcount = 0;
		$sql = "";
		if( isset( $this->having_array ) AND is_array( $this->having_array ))
		{
			foreach( $this->having_array as $col )
			{
				if( 0 < $fieldcount )
					$sql .= ", ";
				$sql .= " '$col' ";
				$fieldcount++;
			}
		}
		$this->having_clause = " HAVING " . $sql;
 		/**/
		$this->tell_eventloop( $this, 'NOTIFY_LOG_DEBUG', __METHOD__ . ":" . __LINE__ . " Exiting " );
	}	
	/*****************************************//**
	 * Build the JOIN clause
	 *
	 * adapted from legacy GENERICTABLE
	 *
	 * Employees INNER JOIN Orders on employees.id = orders.employee_id
	 * Expecting
	 * 	array[0] = array( 'table1' => tbname,
	 * 			'table2' => tbname,
	 * 			'field1 => fdname,
	 * 			'field2 => fdname,
	 * 			'type' => INNER_JOIN /...
	 * 			)
	 *
	 * @param NONE but uses internal variables
	 * @return NONE but sets limit
	 * *****************************************/
	function buildJoin() 
	{
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Entering ");
		/**/
		if( null === $this->join_array )
		{
			$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Exiting via EXCEPTION");
			throw new Exception( "Required Field not set", KSF_FIELD_NOT_SET );
		}
		if( count( $this->join_array ) < 1 )
		{
			$this->tell_eventloop( $this, 'NOTIFY_LOG_DEBUG', __METHOD__ . ":" . __LINE__ . " Exiting " );
			return;
		}
		$sql = "";
		$joincount = 0;
		$FIELDS = array( 'table1', 'type', 'table2', 'field1', 'field2' );
		if( isset( $this->join_array ) AND is_array( $this->join_array ))
		{
			foreach( $this->join_array as $arr )
			{
				if( 0 < $joincount )
					$sql .= ", ";
				foreach( $FIELDS as $col )
				{
					if( isset( $arr[$col] ) )
					{
						$sql .= $arr[$col] . " ";
						if( $col == 'table2' )
							$sql .= "on ";
						else if( $col == 'field1' )
							$sql .= "= ";
					}
					else
					{
						$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Exiting via EXCEPTION");
						throw new Exception( "Mandatory field $col not set" );
					}
				}
				$joincount++;
			}
		}
		$this->join_clause = $sql;
 		/**/
		$this->tell_eventloop( $this, 'NOTIFY_LOG_DEBUG', __METHOD__ . ":" . __LINE__ . " Exiting " );
	}	
	function buildSelectQuery( $b_validate_in_table = false )
	{
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Entering ");
		/**/
		try {
			$this->buildSelect($b_validate_in_table);
		} catch( Exception $e )
		{	
			$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Exiting via EXCEPTION");
			throw new Exception( "Can't select anything with invalid select criterea", $e->getCode() );
		}
		try {
			$this->buildFrom($b_validate_in_table);
		} catch( Exception $e )
		{
			$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Exiting via EXCEPTION");
			throw new Exception( "Can't select anything with invalid FROM criterea", $e->getCode() );
		}
		try {
			$this->buildWhere($b_validate_in_table);
		} catch( Exception $e )
		{
						//Is this a FIELD NOT SET error or something else?  Joins are not mandatory
			if( KSF_FIELD_NOT_SET == $e->getCode() )
			{
				//Not mandatory, continue
			}
			else
			{
				$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Exiting via EXCEPTION");
				throw new Exception( "Can't select anything with invalid WHERE criterea", $e->getCode() );
			}
		}
		try {
			$this->buildGroupby($b_validate_in_table);
		} catch( Exception $e )
		{
			//Is this a FIELD NOT SET error or something else?  Joins are not mandatory
			if( KSF_FIELD_NOT_SET == $e->getCode() )
			{
				//Not mandatory, continue
			}
			//Invalid Groupby might not result in the right data set returned but shouldn't hard fail
			//throw new Exception( "Can't select anything with invalid FROM criterea" );
		}
		try {
			$this->buildOrderBy($b_validate_in_table);
		} catch( Exception $e )
		{
			//Is this a FIELD NOT SET error or something else?  Joins are not mandatory
			if( KSF_FIELD_NOT_SET == $e->getCode() )
			{
				//Not mandatory, continue
				//echo "No Orderby set";
			}
			else
			{
				$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Exiting via EXCEPTION");
				//Invalid Orderby might not result in the right data set returned but shouldn't hard fail
				throw new Exception( "Can't select anything with invalid ORDERBY criterea" );
			}
		}
		try {
			$this->buildHaving($b_validate_in_table);
		} catch( Exception $e )
		{
			//Is this a FIELD NOT SET error or something else?  Joins are not mandatory
			if( KSF_FIELD_NOT_SET == $e->getCode() )
			{
				//Not mandatory, continue
				//echo "No HAVING set";
			}
			else
			{
				$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Exiting via EXCEPTION");
				//Invalid Having might not result in the right data set returned but shouldn't hard fail
				throw new Exception( "Can't select anything with invalid HAVING criterea" );
			}
		}
		try {
			$this->buildJoin($b_validate_in_table);
		} catch( Exception $e )
		{
			//Is this a FIELD NOT SET error or something else?  Joins are not mandatory
			if( KSF_FIELD_NOT_SET == $e->getCode() )
			{
				//Not mandatory, continue
			//	echo "No JOIN to be done!";
			}
			else
			{
			}
		}
		try {
			$this->buildLimit($b_validate_in_table);
		} catch( Exception $e )
		{
			//Is this a FIELD NOT SET error or something else?  Joins are not mandatory
			if( KSF_FIELD_NOT_SET == $e->getCode() )
			{
				//Not mandatory, continue
			//	echo "No LIMIT to set";
			}
			else
			{
			}
		}
		$this->sql = $this->select_clause 
			. $this->from_clause 
			. $this->where_clause
			. $this->groupby_clause
			. $this->having_clause
			. $this->orderby_clause
			. $this->limit_clause;
 		/**/
		$this->tell_eventloop( $this, 'NOTIFY_LOG_DEBUG', __METHOD__ . ":" . __LINE__ . " Exiting " );
	}
	function clear_sql_vars()
	{
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Entering ");
		$this->select_array = null;
		$this->where_array = null;
		$this->from_array = null;
		$this->groupby_array = null;
		$this->having_array = null;
		$this->orderby_array = null;
		$this->sortdir = "ASC";
		$this->limit = "";
		$this->limit_startrows = null; 
		$this->limit_numberrows = null;
		$this->select_clause = "";
		$this->where_clause = "";
		$this->from_clause = "";
		$this->groupby_clause = "";
		$this->having_clause = "";
		$this->orderby_clause = "";
		$this->query_results = "";
		$this->tell_eventloop( $this, 'NOTIFY_LOG_DEBUG', __METHOD__ . ":" . __LINE__ . " Exiting " );
	}
	/***!GENERICTABLE***/
	function assoc2var( $assoc )
        {
       		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Entering ");
         	foreach( $this->fields_array as $field_spec )
                {
                        $field = $field_spec['name'];
                        if( isset( $assoc[$field] ) )
                                $this->set( $field, $assoc[$field] );
                }
		$this->tell_eventloop( $this, 'NOTIFY_LOG_DEBUG', __METHOD__ . ":" . __LINE__ . " Exiting " );
	}
	/**********************************************//**
	 * Take a stdClass object and copy its fields to us
	 *
	 * @since 20200712
	 * @param stdClass
	 * @return null
	 * ********************************************/
	function stdClass2var( $stdclass )
        {
       		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Entering ");
         	foreach( $this->fields_array as $field_spec )
                {
                        $field = $field_spec['name'];
                        if( isset( $stdClass->$field ) )
                                $this->set( $field, $stdClass->$field );
                }
		$this->tell_eventloop( $this, 'NOTIFY_LOG_DEBUG', __METHOD__ . ":" . __LINE__ . " Exiting " );
        }
	function var2caller()
        {
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Entering ");
		if( ! isset( $this->caller ) )
		{
			$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Exiting via EXCEPTION ");
			throw new Exception( "Caller not set so can't pass values back", KSF_FIELD_NOT_SET );
		}
                foreach( $this->fields_array as $field_spec )
                {
                        $field = $field_spec['name'];
                        if( isset( $this->$field ) )
                                $this->caller->set( $field, $this->$field );
                }
		$this->tell_eventloop( $this, 'NOTIFY_LOG_DEBUG', __METHOD__ . ":" . __LINE__ . " Exiting " );
	}
	/*********************************************************************************
	 * *************************** FROM WOO_INTERFACE ***************************
	 * ******************************************************************************/
	function define_table()
	{
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Entering ");
		//Inheriting class MUST extend
		//20200302 KSF check for model_ in class name
		//If the class name starts with model_ we want to strip that off.
		if( ! strncasecmp( "model_", $this->iam, 5 ) )
		{
			$tablename = $this->iam;
		}
		else
		{
			$char = stripos( $this->iam, "_" ) + 1;
			$tablename = substr( $this->iam, $char );
		}
		//The following should be common to pretty well EVERY table...
		$ind = "id_" . $tablename;
		$this->fields_array[] = array('name' => $ind, 'type' => 'int(11)', 'auto_increment' => 'yes', 'readwrite' => 'read' );
		$this->fields_array[] = array('name' => 'updated_ts', 'type' => 'timestamp', 'null' => 'NOT NULL', 'default' => 'CURRENT_TIMESTAMP', 'readwrite' => 'read' );
		$this->table_details['tablename'] = $this->company_prefix . $tablename;
		$this->table_details['primarykey'] = $ind;	//We can override this in child class!
		//$this->table_details['index'][0]['type'] = 'unique';
		//$this->table_details['index'][0]['columns'] = "variablename";
		//$this->table_details['index'][0]['keyname'] = "variablename";
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Exiting ");
	}
	function build_write_properties_array()
	{
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Entering ");
		/*Took the list of properties, and removed the RO ones*/
		foreach( $this->fields_array as $row )
		{
			if( isset( $row['foreign_obj'] ) )
			{
				//$this->foreign_objects_array[] = trim( $row['name'] );
			}
			else
			if( isset( $row['readwrite'] ) )
			{
				if( strncmp( $row['readwrite'], "read", 4 ) <> 0 )
				{
					//Not READONLY
					$this->write_properties_array[] = $this->get_properties_field_name( $row );
				}
			}
			else
			{
				//Assuming NOT set therefore RW
				$this->write_properties_array[] = $this->get_properties_field_name( $row );
			}
		}
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Exiting ");
	}
	/***********************************************
	 * 20200307 What if we need a field name for WC that is different than how we store in the table?
	 * ********************************************/
	function get_properties_field_name( $row )
	{
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Entering ");
		$name = trim( $row['name'] );
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Exiting ");
		return $name;
	}
	/***********************************************
	 * 20200307 What if we need a field name for WC that is different than how we store in the table?
	 *  OR in a more generic manner what if we have multiple external programs such as WOO, SuiteCRM?
	 *  We could enhance the data dictionary module to do name conversions...
	 *  Alternatively, we inherit the MODEL class and do the conversion in inheriting class....
	 * ********************************************/
	function get_properties_external_field_name( $field )
	{
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Entering ");
		foreach( $this->fields_array as $row )
		{
			if( $row['name'] == $field )
			{
				if( isset( $row['external_name'] ) )
					$name = trim( $row['external_name'] );
				else
					$name = trim( $row['name'] );
				return $name;
			}
		}
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Exiting ");
		return $field;
	}
	function build_properties_array()
	{
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Entering ");
		/*All properties*/
		foreach( $this->fields_array as $row )
		{
			if( isset( $row['foreign_obj'] ) )
			{
				$this->foreign_objects_array[] = $row['name'];
			}
			else
				$this->properties_array[] = $this->get_properties_field_name( $row );
		}
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Exiting ");
	}
	function build_foreign_objects_array()
	{
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Entering ");
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Exiting ");
		//return;
	}
	function array2var( $data_array )
	{
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Entering ");
		$extract_count = 0;
		foreach( $this->properties_array as $property )
		{
			if( isset( $data_array[$property] ) )
			{
				$this->$property = $data_array[$property];
				$extract_count++;
			}
		}
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Exiting ");
		return $extract_count;
	}
	/*@int@*/function extract_data_array( $assoc_array )
	{
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Entering ");
		$ret = $this->array2var( $assoc_array);
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Exiting ");
		return $ret;
	}

	/*********************************************
	 * Build the array of data that WC will accept
	 *
	 * @param none
	 * @return none but sets data_array
	 * *******************************************/
	function build_data_array()
	{
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Entering ");
		/***20180917 KSF Clean up old data arrays so we quit sending sales data, bad images, etc*/
		if( isset( $this->data_array ) )
		{
			unset( $this->data_array );
			$this->data_array = array();
		}
		/*!20180917 KSF Clean */
		foreach( $this->write_properties_array as $property )
		{
			if( isset( $this->$property ) )
			{
				$external_name = $this->get_external_field_name( $property );
				$this->data_array[$external_name] = $this->$property;
			}
		}
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Exiting ");
	}
	/*******************************************************************//**
	 *
	 * 	reset_values.  unset all variables listed in properties_array
	 *
	 * 	As we cycle through a database result set putting values into
	 * 	the object, we want to ensure we don't have any values left over
	 * 	from the previous row.  This unsets all values so that they
	 * 	are cleared.
	 * 
	 * **********************************************************************/
	function reset_values()
	{
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Entering ");
		foreach( $this->properties_array as $val )
		{
			unset( $this->$val );
		}
		$this->tell_eventloop( $this, 'NOTIFY_LOG_DEBUG', __METHOD__ . ":" . __LINE__ . " Reset values.  Should be nulls for class " . get_class( $this ) );
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Exiting ");
	}
	/***************************************************************
	 *
	 * Extract Data Objects
	 *
	 * Recursively extracts the data object
	 * Builds a double linked list in the process.
	***************************************************************/
	function extract_data_objects( $srvobj_array )
	{
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Entering ");
		//Woo sends an array of the objects
		$nextptr = $this;
		$objectcount = 0;
		foreach( $srvobj_array as $obj )
		{
			$newobj = new $this->iam($this->serverURL, $this->key, $this->secret, $this->options, $this );
			//Do the recursive extract.
			$newobj->extract_data_obj( $obj );
			//Add into Linked List
			$nextptr->next_ptr = $newobj;
			$newobj->prev_ptr = $nextptr;
			$nextptr = $newobj;
			$objectcount++;
			//The next time through the loop does another...
			//Not unsetting the object because it is part of the dbl linked list
		}
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Exiting ");
		return $objectcount;
	}


	/*int count of properties extracted*/
	/*@int@*/function extract_data_obj( $srvobj )
	{
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Entering ");
		$extract_count = 0;
		foreach( $this->properties_array as $property )
		{
			if( isset( $srvobj->$property ) )
			{
				$this->$property = $srvobj->$property;
				$extract_count++;
			}
		}
		foreach( $this->foreign_objects_array as $foa )
		{
			echo __FILE__ . ":" . __LINE__ . "<br />Foreign Object " . $foa . "<br />";
			if( isset( $srvobj->$foa ) )
			{
				$this->tell_eventloop( $this, 'NOTIFY_LOG_DEBUG', 'Extracting F/O ' . $foa . ' from object' );
				require_once( 'class.woo_' . $foa . '.php' );
				if( is_array( $srvobj->$foa ) )
				{
					foreach( $srvobj->$foa as $obj )
					{
						$newclassname = "woo_" . $foa;
						$newobj = new $newclassname($this->serverURL, $this->key, $this->secret, $this->options, $this);
						$ret = $newobj->extract_data_obj( $obj );
						if( $ret > 0 )
						{
							$newobj->insert_table();
							$this->$foa = $this->$foa + 1;	//Count of the numbers of foreign rows.
							if( $this->debug > 0 )
							{
								$this->tell_eventloop( $this, 'NOTIFY_LOG_DEBUG', 'Data Object: ' . $foa . ' with data::' . print_r( $newobj, true ) );
							}
						}
						unset( $newobj );	//Free the memory
					}
				}
				else
				if( is_object( $srvobj->$foa ) )
				{
					$newclassname = "woo_" . $foa;
					$newobj = new $newclassname($this->serverURL, $this->key, $this->secret, $this->options, $this);
					$ret = $newobj->extract_data_obj( $srvobj->$foa );
					if( $ret > 0 )
					{
						$newobj->insert_table();
						$this->$foa = $this->$foa + 1;	//Count of the numbers of foreign rows.
						if( $this->debug > 0 )
						{
							$this->tell_eventloop( $this, 'NOTIFY_LOG_DEBUG', 'Data Object: ' . $foa . ' with data::' . print_r( $newobj, true ) );
						}
					}
					unset( $newobj );	//Free the memory
				}
			}
		}
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Exiting ");
		return $extract_count;
	}
	function build_json_data()
	{
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Entering ");
		$this->json_data = json_encode( $this->data_array );
		$this->tell_eventloop( $this, 'NOTIFY_LOG_DEBUG', __METHOD__ . "::" . __LINE__ . " JSON data::" . print_r( $this->json_data, true ) );
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Exiting ");
	}
	/*@bool@*/function prep_json_for_send( $func = NULL )
	{
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Entering ");
		$this->build_data_array();
		$this->build_json_data();
		if( $this->json_data == FALSE )
		{
			$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Exiting ");
			return FALSE;
		}
		else
		{
			$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Exiting ");
			return TRUE;
		}
	}
	function ll_walk_insert_fa()
	{
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Entering ");
		$nextptr = $this->next_ptr;
		while( $nextptr != NULL )
		{
			//if "id" not in the table, insert else update
			if( $this->check_table_for_id() )
				$nextptr->update_table();
			else
				$nextptr->insert_table();
			$nextptr = $nextptr->next_ptr;
		}
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Exiting ");
	}
	function ll_walk_update_fa()
	{
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Entering ");
		$nextptr = $this->next_ptr;
		while( $nextptr != NULL )
		{
			$nextptr->update_table();
			$nextptr = $nextptr->next_ptr;
		}
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Exiting ");
	}
	/************************************************
	 * Import the fields from another table
	 *
	 * When we are decorating another class we may
	 * need to ensure we fill in all of the fields they
	 * have.  Instead of having to ensure changes between
	 * versions in all related tables, let the other table
	 * definition be imported.
	 *
	 * @param object
	 * @return none
	 * *********************************************/
	function import_fields_array( $obj )
	{
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Entering ");
		foreach( $obj->fields_array as $row )
		{
			//woo_interface constructor builds our list of fields
			if( ! in_array( $row['name'], $this->properties_array ) )
			{
				$this->fields_array[] = $row;
			}
		}
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Exiting ");
	}
	/******************************************************
	 * Import table details from a class we are decorating
	 *
	 * Unlike the import_fields_array fcn, we can't just
	 * merge the tables.  Primary Key will certainly be
	 * a conflict, as well as each index row we may have
	 * index[#] collisions
	 * ***************************************************/
	function import_table_details( $obj )
	{
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Entering ");
		if( isset( $obj->table_details['index'] ) )
		{
			foreach( $obj->table_details['index'] as $row )
			{
				$this->table_details['index'] = $row;
			}
		}
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Exiting ");
	}
	/**********************************************
	 * Extracted out of constructor so they can be
	 * called a second time after we use import_fields_array
	 * and import_table_details from an external class
	 * we need the fields from
	 * *******************************************/
	public function build_model_related_arrays()
	{
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Entering ");
		$this->write_properties_array = array();
		$this->properties_array = array();
		$this->foreign_objects_array = array();
		$this->build_write_properties_array();
		$this->build_properties_array();
		//$this->fields_array2entry();
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Exiting ");
		
	}
	/************************************************
	 * Copy fields from another object through the GET method
	 *
	 * Only copy the fields that are in the model's table
	 * @param object
	 * **********************************************/
	function obj_fields2me( $obj )
	{
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Entering ");
		$this->copy_obj_fieldlist2me( $obj, $this->properties_array );
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Exiting ");
	}
	/***************************************//**
	 * Take data from an external object and insert/update table
	 *
	 * 
	 * *****************************************/
	public function obj_insert_or_update( $obj )
	{
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Entering ");
		$this->reset_values();
		$this->obj_fields2me( $obj );
		$this->tell_eventloop( $this, "NOTIFY_LOG_DEBUG",  __METHOD__ . ":" . __LINE__ . " Exiting ");
	}




}

?>
