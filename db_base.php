<?php

//20170608 There is something in here screwing with FAs display_notification Exception handler

require_once( 'defines.inc.php' );	//defines path_to_faroot
//global $path_to_faroot;
$path_to_faroot = __DIR__ . '/../..';
require_once( $path_to_faroot . '/includes/db/connect_db.inc' ); //db_query, ...
require_once( $path_to_faroot . '/includes/errors.inc' ); //check_db_error, ...

require_once( 'class.origin.php' );
/***************************************************************//**
 *
 * Inherits:
   	function __construct( $loglevel = PEAR_LOG_DEBUG )
        / *@NULL@* /function set_var( $var, $value )
        function get_var( $var )
        / *@array@* /function var2data()
        / *@array@* /function fields2data( $fieldlist )
        / *@NULL@* /function LogError( $message, $level = PEAR_LOG_ERR )
        / *@NULL@* /function LogMsg( $message, $level = PEAR_LOG_INFO )
 * 
 * Provides:
 	function __construct( $host, $user, $pass, $database, $prefs_tablename )
        function connect_db()
        / *bool* / function is_installed()
        function set_prefix()
        function create_prefs_tablename()
        function mysql_query( $sql, $errmsg = NULL )
        function set_pref( $pref, $value )
        / *string* / function get_pref( $pref )
        function loadprefs()
        function updateprefs()
        function create_table( $table_array, $field_array )
 * 
 *
 * ******************************************************************/
class db_base extends origin
{
	var $host;
	var $user;
	var $pass;
	var $database;
	var $action;
	var $dbHost;
	var $dbUser;
	var $dbPassword;
	var $dbName;
	var $db_connection;
	var $prefs_tablename;
	var $company_prefix;
	var $data;
	var $sql;
	var $sqlerrmsg;
	function __construct( $host, $user, $pass, $database, $prefs_tablename )
	{
		parent::__construct();
		//		echo "Base constructor prefs_tablename: $prefs_tablename";
		try {
			$this->set_var( "dbHost", $host );
			$this->set_var( "dbUser", $user );
			$this->set_var( "dbPassword", $pass );
			$this->set_var( "dbName", $database );
		}
		catch (Exception $e)
		{
		}
		$this->set_var( "prefs_tablename", $prefs_tablename );
		//some subclasses expect the internal vars to match the definition name
		try {
			$this->set_var( "host", $host );
	                $this->set_var( "user", $user );
	                $this->set_var( "pass", $pass );
			$this->set_var( "database", $database );
		}
		catch( Exception $e )
		{
		}
		
		$this->set_prefix();
		$this->connect_db();
	}
	function connect_db()
	{
        //	$this->db_connection = mysql_connect($this->dbHost, $this->dbUser, $this->dbPassword);
        	if (!$this->db_connection) 
		{
			//display_notification("Failed to connect to source of import Database");
			return FALSE;
		}
		else
		{
            		mysql_select_db($this->dbName, $this->db_connection);
			return TRUE;
		}
	}
	/*bool*/ function is_installed()
	{
		global $db_connections;
		if( !isset( $_SESSION["wa_current_user"] ) )
		{
			//chances are we are running CLI rather than web mode
			throw new Exception( "is_installed dependencies failed.  Are we in CLI mode?", KSF_FIELD_NOT_SET );
		}
        
		$cur_prefix = $db_connections[$_SESSION["wa_current_user"]->cur_con]['tbpref'];

		$sql = "SHOW TABLES LIKE '%" . $cur_prefix . $this->prefs_tablename . "%'";
		//display_notification( __FILE__ . "::" . __CLASS__ . "::"  . __METHOD__ . ":" . __LINE__ . "::" . $sql );
        	$result = db_query($sql, __FILE__ . " could not show tables in is_installed: " . $sql);

		$num = db_num_rows($result);
		//display_notification( __FILE__ . "::" . __CLASS__ . "::"  . __METHOD__ . ":" . __LINE__ . "::Result: " . $num );
		if( $num > 0 )
			return TRUE;
		else
			return FALSE;
	}
	function set_prefix()
	{
		if( !isset( $this->company_prefix ) )
		{
			if( strlen( TB_PREF ) == 2 )
			{
				$this->set_var( 'company_prefix', TB_PREF );
			}
			else
			{
        			global $db_connections;
				$this->set_var( 'company_prefix',  $db_connections[$_SESSION["wa_current_user"]->cur_con]['tbpref'] );
			}
			
		}
	}
	function create_prefs_tablename()
	{
	        $sql = "DROP TABLE IF EXISTS " . $this->company_prefix . $this->prefs_tablename;
		        db_query($sql, "Error dropping table");
		
	    	$sql = "CREATE TABLE `" . $this->company_prefix . $this->prefs_tablename ."` (
		         `name` char(32) NOT NULL default \"\",
		         `value` varchar(100) NOT NULL default \"\",
		          PRIMARY KEY  (`name`))
		          ENGINE=MyISAM";
	    	db_query($sql, "Error creating table");
		$this->set_pref('lastcid', 0);
		$this->set_pref('lastoid', 0);
		
	}
	function mysql_query( $sql = null, $errmsg = NULL )
	{
		if( null === $sql )
			$sql = $this->sql;
		if( null === $errmsg )
			$errmsg = $this->sqlerrmsg;
		if( null === $sql )
			throw new Exception( "Can't do an SQL query without the SQL statement" );
		//var_dump( $sql );
		$result = db_query( $sql, $errmsg );
		//var_dump( $result );
		$this->data = db_fetch( $result );
		//var_dump( $data );
		return $this->data;
	}
	function set_pref( $pref, $value )
	{
		if( !isset( $this->company_prefix ) )
			return null;
		if( !isset( $this->prefs_tablename ) )
			return null;
	        $sql = "REPLACE " . $this->company_prefix . $this->prefs_tablename . " (name, value) VALUES (".db_escape($pref).", ".db_escape($value).")";
		db_query($sql, "can't update ". $pref);
	}
	/*string*/ function get_pref( $pref )
	{
		if( !isset( $this->company_prefix ) )
			return null;
		if( !isset( $this->prefs_tablename ) )
			return null;
        	$pref = db_escape($pref);

    		$sql = "SELECT * FROM " . $this->company_prefix . $this->prefs_tablename . " WHERE name = $pref";
    		$result = db_query($sql, "could not get pref ".$pref);

    		if (!db_num_rows($result))
        		return null;
        	$row = db_fetch_row($result);
    		return $row[1];
	}
	function loadprefs()
	{
		foreach( $this->config_values as $row )
		{
			//check for integration with other modules
			if( isset( $row['integration_module'] ) AND strlen($row['integration_module']) > 3 )
			{
				//check to see if that module is installed and active
				//if so use the config values from it
				//
				//In mean time, use built in until we've written the integration code...
				$this->set_var( $row['pref_name'], $this->get_pref( $row['pref_name'] ) );
			}
			else
			{
				$this->set_var( $row['pref_name'], $this->get_pref( $row['pref_name'] ) );
			}
		}
	}
	function updateprefs()
	{
		foreach( $this->config_values as $row )
		{
			if( isset( $_POST[$row['pref_name']] ) )
			{	
				$this->set_var( $row['pref_name'], $_POST[ $row['pref_name'] ] );
				//check for integration with other modules
				if( isset( $row['integration_module'] ) AND strlen($row['integration_module']) > 3 )
				{
					//check to see if that module is installed and active
					//if so are we allowed to update its values?
					//
					//In mean time, use built in until we've written the integration code...
					$this->set_pref( $row['pref_name'], $_POST[ $row['pref_name'] ] );
				}
				else
				{
					$this->set_pref( $row['pref_name'], $_POST[ $row['pref_name'] ] );
				}
			}
		}
	}
	function create_table( $table_array, $field_array )
	{
		if( !isset( $table_array ) )
			return FALSE;
		if( !isset( $field_array ) )
			return FALSE;
		$sql = "CREATE TABLE IF NOT EXISTS `" . $table_array['tablename'] . "` (" . "\n";
		$fieldcount = 0;
		foreach( $field_array as $row )
		{
			$sql .= "`" . $row['name'] . "` " . $row['type'];
			if( isset( $row['null'] ) )
				$sql .= " " . $row['null'];
			if( isset( $row['auto_increment'] ) )
				$sql .= " AUTO_INCREMENT";
			if( isset( $row['default'] ) )
				$sql .= " DEFAULT " . $row['default'];
			$sql .= ",";
			$fieldcount++;
		}
		if( isset( $table_array['primarykey'] ) )
		{
			$sql .= " Primary KEY (`" . $table_array['primarykey'] . "`)";
		}
		else
		{
			$sql .= " Primary KEY (`" . $field_array[0]['name'] . "`)";
		}
		if( isset( $table_array['index'] ) )
		{
			foreach( $table_array['index'] as $index )
			{
				$sql .= ", INDEX " . $index['name'] . "( " . $index['columns'] . " )";
			}
		}
		$sql .= " )";
		if( isset( $table_array['engine'] ) )
		{
			$sql .= " ENGINE=" . $table_array['engine'] . "";
		}
		else
		{
			$sql .= " ENGINE=MyISAM";
		}
		if( isset( $table_array['charset'] ) )
		{
			$sql .= " DEFAULT CHARSET=" . $table_array['charset'] . ";";
		}
		else
		{
			$sql .= " DEFAULT CHARSET=utf8;";
		}
		var_dump( $sql );
		db_query( $sql, "Couldn't create table " . $table_array['tablename'] );
	}
	/**//****************************************************************************
	* Replace a substring ANYWHERE within a field with a new string
	*
	*	https://stackoverflow.com/questions/17365222/update-and-replace-part-of-a-string
	*		UPDATE tablename 
	*		SET field_name = REPLACE(field_name , 'oldstring', 'newstring') 
	*		WHERE field_name LIKE ('oldstring%');
	*
	* @param string table name
	* @param string field
	* @param string oldstring
	* @param string newstring
	******************************************************************************/
	function replace_field_substring( $tablename, $field, $oldstring, $newstring )
	{
		$sql = "UPDATE " . $tablename . " ";
		$sql .= "SET " . $field . " = REPLACE( " . $field . ", '" . $oldstring . "', '" . $newstring . "' ) "; 
		$sql .= "WHERE " . $field . " LIKE '%" . $oldstring . "%'"; 
	}
	/**//****************************************************************************
	* Replace a substring AT THE START OF a field with a new string
	*
	*	https://stackoverflow.com/questions/17365222/update-and-replace-part-of-a-string
	*		UPDATE tablename 
	*		SET field_name = REPLACE(field_name , 'oldstring', 'newstring') 
	*		WHERE field_name LIKE ('oldstring%');
	*
	* @param string table name
	* @param string field
	* @param string oldstring
	* @param string newstring
	******************************************************************************/
	function replace_field_start_substring( $tablename, $field, $oldstring, $newstring )
	{
		$sql = "UPDATE " . $tablename . " ";
		$sql .= "SET " . $field . " = REPLACE( " . $field . ", '" . $oldstring . "', '" . $newstring . "' ) "; 
		$sql .= "WHERE " . $field . " LIKE '" . $oldstring . "%'"; 
	}

}
?>
