<?php

require_once( __DIR__ . '/class.generic_fa_interface.php' );
require_once( __DIR__ . '/class.table_interface.php' );

if( ! class_exists( 'generic_fa_interface_model' ) )
{

	class generic_fa_interface_model extends generic_fa_interface
	{
		var $table_interface;
		var $controller;
		protected $table_details;       //!<array
		protected $fields_array;       //!<array
		/**//*************************************************************************
		* Construct the class
		*
		* @param string
		* @param string
		* @param string
		* @param string
		* @param string
		* @returns none
		*******************************************************************************/
	        function __construct( $host = null, $user = null, $pass = null, $database = null, $pref_tablename = null )
	        {
	                parent:: __construct( $host, $user, $pass, $database, $pref_tablename );
	                $this->table_details = array();
	                $this->table_interface = new table_interface();
	        }

		/*****************************************************************//**
		* Return the field if possible
		*
		*	Tries to return the field natively.  If not set,
		*	then goes to table_interface if it is defined and
		*	tries to return it from there.
		* @param string field to return
		* @return mixed value OR throws exceptions
		*
		**********************************************************************/
		function get( $field )
		{
			try
			{
				return parent::get( $field );
			}
			catch( Exception $e )
			{
				if( $e->getCode() == KSF_FIELD_NOT_SET )
					if( isset( $this->table_interface ) )
					{
						try
						{
							$this->set( $field, $this->table_interface->get( $field ));
						}
						catch( Exception $e )
						{
							//display_error( $e->getMessage() );	
							throw $e;
						}
						return parent::get( $field );
					}
			}
			return null;
		}
		/*****************************************************************//**
		* Set the field if possible
		*
		*	Tries to set the field in this class as well as in table_interface
		*	assumption being we are going to do something with the field in
		*	the database (else why set the model...)
		*
		* @param string field to set
		* @param mixed value to set
		* @param bool should we allow the class to only set __construct time fields
		* @return nothing. throws exceptions
		*
		**********************************************************************/
		function set( $field, $value = null, $enforce = false )
		{
			if( isset( $this->table_interface ) )
			{
				try
				{
					parent::set( $field, $value, false );
					try {
						$this->table_interface->set( $field, $value );
					} catch( Exception $e )
					{
						$code = $e->getCode();
						switch( $code )
						{
							case KSF_FIELD_NOT_CLASS_VAR:
								return;
								break;
							default:
								return;
								break;
						}
					}
				}
				catch( Exception $e )
				{
					if( $this->debug )
					{
						display_error( $e->getMessage() );	
						throw $e;
					}
					else
						//display_notification( $e->getMessage() );
						throw $e;
				}
			}
			else
			try 
			{
				parent::set( $field, $value, $enforce );
			}
			catch( Exception $e )
			{
				//display_notification( $e->getMessage() );
				throw $e;
			}
		}
		/*****************************************************************//**
		* Select a row out of the table
		*
		*	Requires that the table definition has a primary key designated
		*	Requires that the pri key field has been set.
		*
		*	table_interface will have the resulting row's values set.
		* @return nothing. throws exceptions
		*
		**********************************************************************/
		function select_row()
		{
			$this->table_interface->select_row();
		}
		/*@mysql_result@*/function getAll()
		{
			$fields = "*";	//comma separated list
			$where = array();
			$orderby = array();
			$limit = null;	//int
			return $this->table_interface->select_table( $fields, $where, $orderby, $limit );
		}
		function install()
		{
			//display_notification( __FILE__ . "::" . __CLASS__ . "::"  . __METHOD__ . ":" . __LINE__, "WARN" );
			$this->table_interface->create_table();
			parent::install();
		}
		/**//*****************************************************************
		* Insert an array of data.  Dependant upon fields_array
		*
		* @param array data to insert
		* @returns bool did we insert.
		**********************************************************************/
		function insert_data( $data_arr )
		{
			//display_notification( __FILE__ . "::" . __CLASS__ . "::"  . __METHOD__ . ":" . __LINE__, "WARN" );
			foreach( $data_arr as $key => $value )
			{
				try {
					$ret = $this->table_interface->set( $key, $value );
				} catch( Exception $e )
				{
					// because I am sending in $_POST there will be fields that can't be set.
				}
			}
			try 
			{
				$this->table_interface->insert_table();
				$this->table_interface->update_table();
				return true;
			} 
			catch( Exception $e )
			{
				return false;
			}
		}
		/*@bool@*/function validate( $data_var, $data_type )
		{
			if( strncasecmp( $data_type, "int", 3 ) )
				$data_type = 'int';
			if( strncasecmp( $data_type, "varc", 4 ) )
				$data_type = 'string';
			switch($data_type)
			{
				case 'bool':
				case 'string':
				case 'int':
					//break;
				default:
					return true;	//data type not found
			}
		}
		function getPrimaryKey()
		{
			return $this->table_interface->getPrimaryKey();
		}
		/**//****************************************************************************
		* Replace a substring ANYWHERE within a field with a new string
		*
		*       https://stackoverflow.com/questions/17365222/update-and-replace-part-of-a-string
		*	       UPDATE tablename
		*	       SET field_name = REPLACE(field_name , 'oldstring', 'newstring')
		*	       WHERE field_name LIKE ('oldstring%');
		*
		* @since 241024
		*
		* @param string field
		* @param string oldstring
		* @param string newstring
		* @returns string the query
		******************************************************************************/
		function replace_field_substring_query( $field, $oldstring, $newstring )
		{
			$sql = "UPDATE " . $this->tablename . " ";
			$sql .= "SET " . $field . " = REPLACE( " . $field . ", '" . $oldstring . "', '" . $newstring . "' ) ";
			$sql .= "WHERE " . $field . " LIKE '%" . $oldstring . "%'";
			return $sql;
		}
		/**//****************************************************************************
		* Replace a substring AT THE START OF a field with a new string
		*
		*       https://stackoverflow.com/questions/17365222/update-and-replace-part-of-a-string
		*	       UPDATE tablename
		*	       SET field_name = REPLACE(field_name , 'oldstring', 'newstring')
		*	       WHERE field_name LIKE ('oldstring%');
		*
		* @since 241024
		*
		* @param string field
		* @param string oldstring
		* @param string newstring
		* @returns string the query
		******************************************************************************/
		function replace_field_start_substring_query( $field, $oldstring, $newstring )
		{
			$sql = "UPDATE " . $this->tablename . " ";
			$sql .= "SET " . $field . " = REPLACE( " . $field . ", '" . $oldstring . "', '" . $newstring . "' ) ";
			$sql .= "WHERE " . $field . " LIKE '" . $oldstring . "%'";
			return $sql;
		}
                /**//****************************************************************************
                * Append a string onto a field
                *
                * @since 241024
                *
                * @param string field
                * @param string string to append
                * @param string complete WHERE condition including the where
		* @returns string the query
                ******************************************************************************/
                function field_append_string_query( $field, $string, $where = null )
                {
                        $sql = "UPDATE " . $this->tablename . " ";
                        $sql .= "SET " . $field . " = CONCAT( " . $field . ", ' " . $string . "' ) ";
                        if( null !== $where )
                                $sql .= $where;
			return $sql;
                }
                /**//****************************************************************************
                * Prepend a string onto a field
                *
                * @since 241024
                *
                * @param string field
                * @param string string to append
                * @param string complete WHERE condition including the where
		* @returns string the query
                ******************************************************************************/
                function field_prepend_string_query( $field, $string, $where = null )
                {
                        $sql = "UPDATE " . $this->tablename . " ";
                        $sql .= "SET " . $field . " = CONCAT( " . string . ", ' " . $field . "' ) ";
                        if( null !== $where )
                                $sql .= $where;
			return $sql;
                }


	}
}

?>
