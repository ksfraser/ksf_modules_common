<?php

		//echo  __FILE__ . "::" . __LINE__  . '\n\r';
//TODO
//	
/* 20240918 Disabling for troubleshooting.
global $path_to_root;
require_once( $path_to_root . "/includes/db/connect_db.inc" );	//db_escape
*/

		//echo  __FILE__ . "::" . __LINE__  . '\n\r';
/*****************************************************************************************//**
 * Base class to provide basic SQL functions
 *
 * Provides:
*	function select_table($fieldlist = "*", $where = null, $orderby = null, $limit = null)
*        function update_table()
*        function check_table_for_id()
*        function insert_table()
*        function create_table()
*        function alter_table()
*        function count_rows()
*        function count_filtered($where)
*
*        function get( $field )
*        function set( $field, $value = null, $enforce = false )
*        function validate( $data_value, $data_type )
*        function select_row( $set_caller = false )
*        function delete_table()
*        function getPrimaryKey()
*        function getByPrimaryKey()
*
 * 
 *
 * *******************************************************************************************/
class table_interface
{
	var $db_insert_id;	//!< int set by $this->insert_table();
	var $table_details;	//!< array definition details about the table
	var $properties_array;	//!< array
	var $fields_array;	//!< array
	var $caller;
	function __construct( $caller = null )
	{
		//display_notification( __FILE__ . "::" . __LINE__ );
		//echo  __FILE__ . "::" . __LINE__  . '\n\r';

		$this->db_insert_id = null;
		if( !isset( $this->table_details ) )
			$this->table_details = array();
		if( !isset( $this->properties_array ) )
			$this->properties_array = array();
		if( null !== $caller )
			$this->caller = $caller;
		//display_notification( __FILE__ . "::" . __LINE__ );

	}
	/********************************************************//**
	 * Copied from origin.  Throws exceptions
	 *
	 * **********************************************************/
	function get( $field )
	{
		if( isset( $this->$field ) )
			return $this->$field;
		else
			throw new Exception( __METHOD__ . "  Field not set.  Can't GET", KSF_FIELD_NOT_SET );
	}
	/*********************************************//**
	 * Set a variable.  Throws exceptions on sanity checks
	 *
	 * 
	 * @param field string Variable to be set
	 * @param value ... value for variable to be set
	 * @param bool
	 * @return bool Did we set the value
	 * **********************************************/
	/*@bool@*/function set( $field, $value = null, $enforce = false )
	{
		//display_notification( __FILE__ . "::" . __CLASS__ . "::"  . __METHOD__ . ":" . __LINE__, "WARN" );	
		if( !isset( $field )  )
			throw new Exception( "Fields not set" );
		if( ! isset( $this->fields_array ) )
		{
			debug_print_backtrace();
		}
		else
		{
			foreach( $this->fields_array as $row )
			{
				if( $field == $row['name'] )
				{
					try
					{
						$this->validate( $value, $row['type'] );
						$this->$field = $value;
						return TRUE;
					}
					catch(InvalidArgumentException $e)
					{
						display_error( $e->getMessage() );
					}
					catch( Exception $e )
					{
						display_notification( $e->getMessage() );
					}
				}
				
			}
		}
		throw new Exception( "Variable <i><b>" . $field . "</b></i> to be set is not a member of the class", KSF_FIELD_NOT_CLASS_VAR );
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
		//display_notification( __FILE__ . "::" . __CLASS__ . "::"  . __METHOD__ . ":" . __LINE__, "WARN" );
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
					if( $data_value === true ) return true;
					if( $data_value === false ) return true;
					if( $data_value == 0 ) return true;
					if( $data_value == 1 ) return true;
					throw new InvalidArgumentException("Expected Boolean.  Received " . $data_value);
					break;	//fall out of switch and return false
			case 'string':
			case 'digit':
				if( $data_value >= 0 AND $data_value <= 9 ) return true;
				break;	//fall out of switch and return false
			case 'int':
				if( is_int( $data_value ) ) 
					return true;
				else 
					throw new InvalidArgumentException("Expected INT.  Received " . $data_value);
				break;
			default:
				return true;	//data type not found
		}
		//throw new InvalidArgumentException();
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
	/*none*/function select_row( $set_caller = false )
	{
		if( isset( $this->table_details['primarykey'] ) )
			$key = $this->table_details['primarykey'];
		else
			throw new Exception( "Primary Key not defined.  This function uses that field in the query" );
		if( ! isset( $this->$key ) )
			throw new Exception( "Primary Key not set.  Required Field for this function" );
		$sql = "SELECT * from `" . $this->table_details['tablename'] . "` WHERE $key='" . $this->$key . "'";
		$res = db_query( $sql, "Couldn't select from " . $this->table_details['tablename'] );
		$row = db_fetch( $res );
		foreach( $this->fields_array as $def )
		{
			$name = $def['name'];
			if( isset( $row[$name] ) )
			{
				$this->$name = $row[$name];
				if( $set_caller AND isset( $this->caller ) )
					try
					{
						$this->caller->set( $name, $this->$name );
					} catch( Exception $e )
					{
						//Caller may not have the same name for the variable.
						//Not going to stress over it.  They can query us for
						//the value...
						//HOWEVER if set_caller is true, they are expecting the value.
						throw $e;
					}
			}
		}
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
		return $res;
	}
	/***************************************************************************************//**
	 * Delete a row in the table as long as the prikey has a value set
	 *
	 * Will eventually throw exceptions!
	 *
	 * *****************************************************************************************/
	function delete_table()
	{
		//display_notification( __FILE__ . "::" . __CLASS__ . "::"  . __METHOD__ . ":" . __LINE__, "WARN" );
		$pri = $this->table_details['primarykey'];
		if( !isset( $this->$pri ) )
			throw new Exception( "Primary Key not set", PRIMARY_KEY_NOT_SET );
		$sql = "DELETE from " . $this->table_details['tablename'] . " WHERE " . $pri . " = '" . $this->$pri . "'";
		//var_dump( $sql );
		db_query( $sql, "Couldn't update table " . $this->table_details['tablename'] . " for key " .  $pri );	
		//throw new Exception( $sql );	//Causes FA to display_error the msg.  Useful for debugging.
	}
	/***************************************************************************************//**
	 * Update a row in the table as long as the prikey has a value set
	 *
	 * Will eventually throw exceptions!
	 *
	 * *****************************************************************************************/
	function update_table()
	{
		//display_notification( __FILE__ . "::" . __CLASS__ . "::"  . __METHOD__ . ":" . __LINE__, "WARN" );
		$pri = $this->table_details['primarykey'];
		if( !isset( $this->$pri ) )
			return;
			//throw new Exception( "Primary Key field is not set.  This function requires that key" );
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
	}
	/*****************************************************************************//**
	 *
	 *
	 * @returns bool did we find the key
	 * ******************************************************************************/
	/*@bool@*/function check_table_for_id()
	{
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
				return TRUE;
			else
				return FALSE;
		}
		return FALSE;
	}
	/*******************************************************************************************************//**
	 * Insert data from this class into a table.
	 *
	 * This function uses mysql_real_escape_string which is depreciated in 5.5 and removed in php 7.
	 * @return int Index of last insert
	 * *********************************************************************************************************/
	/*int index of last insert*/
	/*@int@*/function insert_table()
	{
		//display_notification( __FILE__ . "::" . __CLASS__ . "::"  . __METHOD__ . ":" . __LINE__, "WARN" );
		if( empty( $this->table_details['tablename'] ) )
		{
			throw new Exception( "table_details['tablename'] not set.  Can't run query" );
		}
		if( empty( $this->fields_array ) )
		{
			throw new Exception( "fields_array not set.  Can't build query" );
		}
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
		if( $fieldcount > 0 )
			db_query( $sql, "Couldn't insert into table " . $this->table_details['tablename'] . " for " .  $sql );	
		else
			display_error( "No values set so couldn't insert" );
		$this->db_insert_id = db_insert_id();
		return $this->db_insert_id;
	}
	function create_table()
	{
		if( ! isset( $this->table_details['tablename'] ) )
		{
			if( method_exists( $this->define_table() ) )
				$this->define_table();
			else
				throw new Exception( "Table Definition not defined so can't create table" );
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
		display_notification( __FILE__ . " Creating table " . $this->table_details['tablename'] );
		db_query( $sql, "Couldn't create table " . $this->table_details['tablename'] );
		return $this->alter_table();
	}
	function alter_table()
	{
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
		display_notification( __FILE__ . " Altering table " . $this->table_details['tablename'] );
		return db_query( $sql, "Couldn't alter table " . $this->table_details['tablename'] );
	}
	/*****************************************************************************************//**
	 * Count the number of rows in the table.
	 *
	 * @returns int number of rows
	 * ******************************************************************************************/
	/*@int@*/function count_rows()
	{
		$res = db_query( "select count(*) from " . $this->table_details['tablename'], "Couldn't count rows in " . $this->table_details['tablename'] );
		$count = db_fetch_row( $res );
		return $count[0];
	}
	/*****************************************************************************************//**
	 * Count the number of rows in the table filtered by criteria
	 *
	 * @params string where criteria w/o leading "where"
	 * @returns int number of rows
	 * ******************************************************************************************/
	/*@int@*/function count_filtered($where = null)
	{
		if( !isset( $where ) )
			return $this->count_rows();
		$res = db_query( "select count(*) from " . $this->table_details['tablename'] . " where " . $where, "Couldn't count rows in " . $this->table_details['tablename'] );
		$count = db_fetch_row( $res );
		return $count[0];
	}
	/*string*/function getPrimaryKey()
	{
		return $this->table_details['primarykey'];
	}
	/*none*/function getByPrimaryKey()
	{
		/*
		$fields = "*";	//comma separated list
		$prikey = $this->getPrimaryKey();
		$where = array( $prikey );
		$orderby = array();
		$limit = null;	//int
		return $this->select_table( $fields, $where, $orderby, $limit );
		*/
		$this->select_row();
	}
}

?>
