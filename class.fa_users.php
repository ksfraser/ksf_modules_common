<?php

$path_to_root="../..";
//require_once( 'class.fa_origin.php' );
require_once( 'class.table_interface.php' );

/********************************************************//**
 * Various modules need to be able to add or get info about customers from FA
 *
 *	This class uses FA specific routines (display_notification etc)
 *
 *	STUB file to help use stock_master
 *
 * **********************************************************/
//class fa_stock_master extends fa_origin
class fa_users extends table_interface
{
	/*
| Field              | Type         | Null | Key | Default | Extra |
+--------------------+--------------+------+-----+---------+-------+
| id              | smallint(6)  | NO   | PRI | NULL    | auto_increment |
| user_id         | varchar(60)  | NO   | UNI |         |                |
| password        | varchar(100) | NO   |     |         |                |
| real_name       | varchar(100) | NO   |     |         |                |
| role_id         | int(11)      | NO   |     | 1       |                |
| phone           | varchar(30)  | NO   |     |         |                |
| email           | varchar(100) | YES  |     | NULL    |                |
| language        | varchar(20)  | YES  |     | NULL    |                |
| date_format     | tinyint(1)   | NO   |     | 0       |                |
| date_sep        | tinyint(1)   | NO   |     | 0       |                |
| tho_sep         | tinyint(1)   | NO   |     | 0       |                |
| dec_sep         | tinyint(1)   | NO   |     | 0       |                |
| theme           | varchar(20)  | NO   |     | default |                |
| page_size       | varchar(20)  | NO   |     | A4      |                |
| prices_dec      | smallint(6)  | NO   |     | 2       |                |
| qty_dec         | smallint(6)  | NO   |     | 2       |                |
| rates_dec       | smallint(6)  | NO   |     | 4       |                |
| percent_dec     | smallint(6)  | NO   |     | 1       |                |
| show_gl         | tinyint(1)   | NO   |     | 1       |                |
| show_codes      | tinyint(1)   | NO   |     | 0       |                |
| show_hints      | tinyint(1)   | NO   |     | 0       |                |
| last_visit_date | datetime     | YES  |     | NULL    |                |
| query_size      | tinyint(1)   | YES  |     | 10      |                |
| graphic_links   | tinyint(1)   | YES  |     | 1       |                |
| pos             | smallint(6)  | YES  |     | 1       |                |
| print_profile   | varchar(30)  | NO   |     | 1       |                |
| rep_popup       | tinyint(1)   | YES  |     | 1       |                |
| sticky_doc_date | tinyint(1)   | YES  |     | 0       |                |
| startup_tab     | varchar(20)  | NO   |     | orders  |                |
| inactive        | tinyint(1)   | NO   |     | 0       |                |
	 *
	 * */
	protected $id              ;// smallint(6)  | NO   | PRI | NULL    | auto_increment |
	protected $user_id         ;// varchar(60)  | NO   | UNI |         |                |
	protected $password        ;// varchar(100) | NO   |     |         |                |
	protected $real_name       ;// varchar(100) | NO   |     |         |                |
	protected $role_id         ;// int(11)      | NO   |     | 1       |                |
	protected $phone           ;// varchar(30)  | NO   |     |         |                |
	protected $email           ;// varchar(100) | YES  |     | NULL    |                |
	protected $language        ;// varchar(20)  | YES  |     | NULL    |                |
	protected $date_format     ;// tinyint(1)   | NO   |     | 0       |                |
	protected $date_sep        ;// tinyint(1)   | NO   |     | 0       |                |
	protected $tho_sep         ;// tinyint(1)   | NO   |     | 0       |                |
	protected $dec_sep         ;// tinyint(1)   | NO   |     | 0       |                |
	protected $theme           ;// varchar(20)  | NO   |     | default |                |
	protected $page_size       ;// varchar(20)  | NO   |     | A4      |                |
	protected $prices_dec      ;// smallint(6)  | NO   |     | 2       |                |
	protected $qty_dec         ;// smallint(6)  | NO   |     | 2       |                |
	protected $rates_dec       ;// smallint(6)  | NO   |     | 4       |                |
	protected $percent_dec     ;// smallint(6)  | NO   |     | 1       |                |
	protected $show_gl         ;// tinyint(1)   | NO   |     | 1       |                |
	protected $show_codes      ;// tinyint(1)   | NO   |     | 0       |                |
	protected $show_hints      ;// tinyint(1)   | NO   |     | 0       |                |
	protected $last_visit_date ;// datetime     | YES  |     | NULL    |                |
	protected $query_size      ;// tinyint(1)   | YES  |     | 10      |                |
	protected $graphic_links   ;// tinyint(1)   | YES  |     | 1       |                |
	protected $pos             ;// smallint(6)  | YES  |     | 1       |                |
	protected $print_profile   ;// varchar(30)  | NO   |     | 1       |                |
	protected $rep_popup       ;// tinyint(1)   | YES  |     | 1       |                |
	protected $sticky_doc_date ;// tinyint(1)   | YES  |     | 0       |                |
	protected $startup_tab     ;// varchar(20)  | NO   |     | orders  |                |
	protected $inactive        ;// tinyint(1)   | NO   |     | 0       |                |
	function __construct( $prefs_db )
	{
		//parent::__construct( $prefs_db );
		//parent::__construct();
		$this->table_details['tablename'] = TB_PREF . 'stock_master';
		$this->fields_array[] = array( 'name' => 'stock_id', 'type' => 'varchar(64)', 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'category_id', 'type' => 'int(11)', 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' ); 
		$this->fields_array[] = array( 'name' => 'tax_type_id', 'type' => 'int(11)', 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' ); 
		$this->fields_array[] = array( 'name' => 'description', 'type' => 'varchar(200)', 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );   
		$this->fields_array[] = array( 'name' => 'long_description', 'type' => 'text', 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' ); 
		$this->fields_array[] = array( 'name' => 'units', 'type' => ' varchar(20)', 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' ); 
		$this->fields_array[] = array( 'name' => 'mb_flag', 'type' => 'char(1)', 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' ); 
		$this->fields_array[] = array( 'name' => 'sales_account', 'type' => 'varchar(15)', 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'cogs_account', 'type' => 'varchar(15)', 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'inventory_account', 'type' => 'varchar(15)', 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'adjustment_account', 'type' => 'varchar(15)', 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'assembly_account', 'type' => 'varchar(15)', 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' ); 
		$this->fields_array[] = array( 'name' => 'dimension_id', 'type' => 'int(11)', 'null' => 'NULL', 'readwrite' => 'readwrite', 'default' => '' ); 
		$this->fields_array[] = array( 'name' => 'dimension2_id', 'type' => 'int(11)', 'null' => 'NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'actual_cost', 'type' => 'double', 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'last_cost', 'type' => 'double', 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'material_cost', 'type' => 'double', 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'labour_cost', 'type' => 'double', 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'overhead_cost', 'type' => 'double', 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'inactive', 'type' => 'bool', 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'no_sale', 'type' => 'bool', 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->fields_array[] = array( 'name' => 'editable', 'type' => 'bool', 'null' => 'NOT NULL', 'readwrite' => 'readwrite', 'default' => '' );
		$this->table_details['primarykey'] = "stock_id";
	}
 	function insert()
	{
		$this->insert_table();
	}
	function update()
	{
		$this->update_table();
	}
	function getById()
	{
		return $this->getByPrimaryKey();
	}

	/*@bool@*/function validate($value, $type)
	{
	}
	/****
	 *
	 * 		try {
			$this->clear_sql_vars();
			
			$this->select_array = array( 'stock_id' );
			$this->from_array = array( TB_PREF . 'stock_master' );
			$this->where_array = array( 'stock_id' => array( "like" =>  $stock_id_match ) );
			$this->insert_array = array();
			$this->insert_array['...'] = "";
			$this->buildInsertSelectQuery();
			$this->query( "Can not insert prices for matched stock ids " . $stock_id_match, "insert");
		} catch( Exception $e )
		{
			throw $e;
		}
	 *
	 * ***/
	function getAll( $active_only = true )
	{
		$sql = "select * from " . TB_PREF . "stock_master";
		if( $active_only )
			$sql .= " where inactive='0'";
		$result = db_query( $sql , "Couldn't select stock_ids" );
		while( ( $row = mysql_fetch_array( $result ) ) != null )
		{
			$this->stock_array[] = $row;
			$this->stock_id_array[] = $row["stock_id"];
		}
	}
	function getStock_IDs( $active_only = true )
	{
		$sql = "select stock_id from " . TB_PREF . "stock_master";
		if( $active_only )
			$sql .= " where inactive='0'";
		
		$result = db_query( $sql , "Couldn't select stock_ids" );
		while( ( $row = mysql_fetch_array( $result ) ) != null )
		{
			$this->stock_id_array[] = $row["stock_id"];
		}
	}
	function count_active()
	{
		$res = db_query( "select count(*) from " . TB_PREF . "stock_master where inactive='0'", "Couldn't count QOH" );
		$count = db_fetch_row( $res );
		$this->active_count = $count[0];
	}
	function display_active_count()
	{
		$this->count_active();
		display_notification( $this->active_count . " rows of active items exist in stock_master.");
	}
	/*****************************************************************************************//**
	 * Display a form with a drop down list of Stock Items that meet a sub-query criteria
	 * 
	 * @param subquery Query to select stock_ids
	 * @param stock_IN is the stock_ids to display IN (TRUE) or NOT IN (FALSE) the results from the subquery
	 * @param instruction_title is the table section label for instructions to use this form
	 * @param instruction_rows is rows in the table with instructions on how to use this form
	 *
	 * Subquery similar to "select stock_id from " . $this->table_details['tablename'] . "where image_count='0'";
	 * @return NULL.  Displays a form
	 * *******************************************************************************************/
	function display_edit_list_form( $subquery, $stock_IN = true, $instruction_title = "", $instruction_rows = null )
	{
		$sql = "select sm.stock_id, sm.description, c.description, sm.inactive, sm.editable 
				from " . TB_PREF . "stock_master sm, " . TB_PREF . "stock_category c
				where sm.category_id = c.category_id and sm.stock_id ";
		if( $stock_IN )
			$sql .= " IN ";
		else
			$sql .= " NOT IN ";
		$sql .= "( " . $subquery . " )";
		 global $all_items;
		$selected_id = "0";
		$name = "";
		$editkey = TRUE;
		$opts = array('cells'=>true, 'show_inactive'=>'1');
		$all_option = FALSE;
		$submit_on_change = TRUE;
                set_editor('item', $name, $editkey);
		start_form();
		start_table();
		table_section_title(_( $instruction_title ));
		foreach( $instruction_rows as $row )
		{
			label_row(_( $row ), NULL);
		}
		label_row("&nbsp;", NULL);
		label_row("Press F4 to pop open a window to edit the item details", null);
		table_section(1);
	        $ret = combo_input($name, $selected_id, $sql, 'stock_id', 'sm.description',
	        array_merge(
	          array(
	                'format' => '_format_stock_items',
	                'spec_option' => $all_option===true ?  _("All Items") : $all_option,
	                'spec_id' => $all_items,
			'search_box' => true,
	        	'search' => array("sm.stock_id", "c.description","sm.description"),
	                'search_submit' => get_company_pref('no_item_list')!=0,
	                'size'=>10,
	                'select_submit'=> $submit_on_change,
	                'category' => 2,
	                'order' => array('c.description','sm.stock_id')
	          ), $opts) );
			echo $ret;
		end_table();
		end_form();
		return NULL;
	}


}

?>
