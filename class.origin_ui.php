<?php

//!< WARNING this class has some FrontAccounting specific code

$path_to_root="../..";
require_once( 'defines.inc.php' );
require_once( 'class.origin.php' );

/*
	# 0 PEAR_LOG_EMERG emerg() System is unusable
	# 1 PEAR_LOG_ALERT alert() Immediate action required
	# 2 PEAR_LOG_CRIT crit() Critical conditions
	# 3 PEAR_LOG_ERR err() Error conditions
	# 4 PEAR_LOG_WARNING warning() Warning conditions
	# 5 PEAR_LOG_NOTICE notice() Normal but significant
	# 6 PEAR_LOG_INFO info() Informational
	# 7 PEAR_LOG_DEBUG debug() Debug-level messages 
*/
/**********************************************************************//**
 * class origin_ui
 *
 * Common processes for UI classes
 *
 * Functions:
 * 	fields_array2var
 * 	in_table_display
 * 	call_table
 * 	display_edit_form
 * 	display_table_with_edit
 * 	display_master_form
 *
 * Inherited: (not guaranteed to be complete due to changes in inherited class(es)
 * 	set_var
 * 	get_var
 * 	var2data
 * 	fields2data
 * 	LogError
 * 	LogMsg
 * *************************************************************************/
class origin_ui extends origin
{
	var $client;
	/************************************************************************//**
	 *constructor
	 *
	 *@param $loglevel int PEAR log levels
	 *@param object client object needing values set
	 * ***************************************************************************/
	function __construct( $loglevel = PEAR_LOG_DEBUG, $client = null )
	{
		parent::__construct( $loglevel );
		if( null != $client )
			$this->client = $client;
	}

	/*********************************************************************************//**
	 *master_form
	 *	Display 2 forms - the summary of items with edit/delete
	 *		The edit/entry form for 1 row of data
	 *	assumes entry_array has been built (constructor)
	 *	assumes table_details has been built (constructor)
	 *	assumes iam has been set (constructor)
	 *
	 *	requires client->fields_array and client->fields_array2var()	provided by table_interface
	 *	requires client->table_details['primarykey']
	 *	requires client->table_details['tablename']
	 *	requires client->reset_values()
	 *	requires client->entry_array
	 *
	 * ***********************************************************************************/
	function master_form()
	{
		global $Ajax;
		//simple_page_mode();
		div_start('form');
		$this->client->selected_id = find_submit('Edit');
		$count = $this->client->fields_array2var();
		$key = $this->client->table_details['primarykey'];
		if( isset( $this->client->$key ) )
		{
			$this->client->update_table();
		}
		else if( $count > 0 )
		{
			$this->client->insert_table();
		}
		$this->client->reset_values();
		
		$sql = "SELECT ";
		$rowcount = 0;
		foreach( $this->client->entry_array as $row )
		{
			if( $rowcount > 0 ) $sql .= ", ";
			$sql .= $row['name'];
			$rowcount++;
		}
		$sql .= " from " . $this->client->table_details['tablename'];
		if( isset( $this->client->table_details['orderby'] ) )
			$sql .= " ORDER BY " . $this->client->table_details['orderby'];

		$this->display_table_with_edit( $sql, $this->client->entry_array, $this->client->table_details['primarykey'] );
		$this->display_edit_form( $this->client->entry_array, $this->client->selected_id, "create_" . $this->client->iam . "_form" );
		div_end();
		//$Ajax->activate('form');
	}
	/*********************************************************************************//**
	 *display_table_with_edit
	 *		The edit/entry form for 1 row of data
	 *
	 * @param string sql statement to run to get table
	 * @param array headers for the table to be displayed
	 * @param int which row to edit
	 * @param string URL for returning to?
	 * ***********************************************************************************/
	function display_table_with_edit( $sql, $headers, $index, $return_to = null )
	{
		$columncount = 0;
		foreach( $headers as $row )
		{
			$th[$columncount] = $row['label'];
			$datacol[$columncount] = $row['name'];
			$columncount++;
		}
		//Edit
			$th[$columncount] = "";
			$columncount++;
		//Delete
			$th[$columncount] = "";
			//$th[$columncount] = $row[$index];
			$columncount++;
		start_form( );
		start_table(TABLESTYLE, "width=80%" );
		//inactive_control_column($th);
		table_header( $th );
		$k=0;

		$result = db_query( $sql, __METHOD__ . " Couldn't run query" );
		while( $nextrow = db_fetch( $result ) )
		{
			alt_table_row_color($k);
			for( $c = 0; $c <= $columncount - 3; $c++ )
			{
				label_cell( $nextrow[$c] );
			}
			edit_button_cell("Edit" . $nextrow[$index], _("Edit") );
			delete_button_cell("Delete" . $nextrow[$index], _("Delete") );
			//inactive_control_cell( $nextrow[$index] );
			end_row();
		}
		//inactive_control_row($th);
		hidden( 'table_with_edit', 1 );
		if( null != $return_to )
			hidden( 'return_to', $return_to );
		end_table();
		end_form();
	}
	/*********************************************************************************//**
	 *display_edit_form
	 *
	 * requires client->table_details
	 *
	 * @param array form definitions
	 * @param int optional int of which value selected
	 * @param string URL
	 * ***********************************************************************************/
	function display_edit_form( $form_def, $selected_id = -1, $return_to = null )
	{
		if( $selected_id > -1 )
		{
			//We are editing a row, so need to query for the values
			$sql = "SELECT * from " . $this->client->table_details['tablename'];
			$sql .= " WHERE " . $this->client->table_details['primarykey'] . " = '" . $selected_id . "'";
			$res = db_query( $sql, __METHOD__ . " Couldn't query selected" );
			$arr = db_fetch_assoc( $res );
			$this->array2var( $arr );
		}
		start_form(  );
		//start_form(  false, false, "woo_form_handler.php", "" );
		start_table(TABLESTYLE2 );
		foreach( $form_def as $row )
		{
			$var = $row['name'];
			if( $row['readwrite'] == "read" )
			{
				//can't edit this column as it isn't set write nor readwrite
				if( isset( $this->$var ) )
					label_row( _($row['label'] . ":"), $this->$var );
			}
			else
			{
				if( $row['type'] == "varchar" )
					text_row(_($row['label'] . ":"), $row['name'], $this->$var, $row['size'], $row['size']);
				/*
				else if( $row['type'] == "dropdown" )
				{
					$ddsql = "select * from " . $row['foreign_obj'];
					$ddsql .= " ORDER BY " . $row['foreign_column'];
					$this->combo_list_row( $ddsql, $row['foreign_column'], 
								_($row['label'] . ":"), $row['name'], 
								$selected_id, false, false ); 
				}
				 */
				else if( $row['type'] == "bool" )
					check_row(_($row['label'] . ":"), $row['name'] ); 
				else
					text_row(_($row['label'] . ":"), $row['name'], null, $row['size'], $row['size']);
			}
		}


		end_table();
		hidden( 'edit_form', 1 );
		hidden( 'my_class', get_class( $this->client ) );
		hidden( 'return_to', $return_to );
		hidden( 'action', $return_to );
		submit_center('ADD_ITEM', _("Add Item") );
//		submit_add_or_update_center($selected_id == -1, '', 'both', false);
		end_form();
	}
	/************************************************//**
	 *call_table
	 *
	 * 	Puts a table on the screen with a button
	 * 	to act as a "Are you sure" type of screen
	 * 	so that the user has to init the action.
	 *
	 *@param action routine (next screen) to call
	 *@param msg the message to be displayed on the button to push
	 *@returns NOTHING
	 * **************************************************/
	function call_table( $action, $msg )
	{
		echo "call table<br />";
                start_form(true);
                 start_table(TABLESTYLE2, "width=40%");
                 table_section_title( $msg );
                 hidden('action', $action );
                 end_table(1);
                 submit_center( $action, $msg );
                 end_form();
	}
	/****************************************************************************//**
	 *in_table_display
	 *
	 * @param array display on the screen, within a table, 1 row as specified by the array
	 *
	 * ******************************************************************************/
	function in_table_display( $field_array )
	{
		echo "in table display<br />";
		//ASSUMPTION we've already checked the readwrite attribute
		//and this is a writeable fields
		if( strncmp( $field_array['type'], "varchar", 7 ) == 0 
			OR strncmp( $field_array['type'], "int", 3 ) == 0 
		  )
		{
			label_row( $name . "(VC)", $this->$name );
		}
		else
		if( strncmp( $field_array['type'], "timestamp", 7 ) == 0 
			OR strncmp( $field_array['type'], "datetime", 7 ) == 0 
		  )
		{
			label_row( $name . "(DT)", $this->$name );
		}
		else
		if( strncmp( $field_array['type'], "boolean", 7 ) == 0 

		  )
		{
			label_row( $name . "(bool)", $this->$name );
		}

	}
	/*********************************************************************************//**
	 *fields_array2var
	 *	Take the data out of POST variables and put them into
	 *	the variables defined as table columns (fields_array)
	 *
	 * 
	 *	@returns int count of fields set
	 *
	 * ***********************************************************************************/
	/*@int@*/function fields_array2var()
	{
		$count = 0;
		$this->client->reset_values();
		foreach( $this->client->fields_array as $row )
		{
			$var = $row['name'];
			if( isset( $_POST[$var] ) )
			{
				$this->client->$var = $_POST[$var];
				$count++;
			}
		}
		return $count;
	}

	/***********************************************************************
	 *These came out of fa_generic_interface
	 *
	 *
	 * *************************************************************/
	function action_show_form()
	{
		$this->show_config_form();
	}
	function show_config_form()
	{
		start_form(true);
	 	start_table(TABLESTYLE2, "width=40%");
		$th = array("Config Variable", "Value");
		table_header($th);
		$k = 0;
		alt_table_row_color($k);
			/* To show a labeled cell...*/
			//label_cell("Table Status");
			//if ($this->found) $table_st = "Found";
			//else $table_st = "<font color=red>Not Found</font>";
			//label_cell($table_st);
			//end_row();
		foreach( $this->config_values as $row )
		{
				text_row($row['label'], $row['pref_name'], $this->$row['pref_name'], 20, 60);
		}
		end_table(1);
		if (!$this->found) {
		    hidden('action', 'create');
		    submit_center('create', 'Create Table');
		} else {
		    hidden('action', 'update');
		    submit_center('update', 'Update Configuration');
		}
		end_form();
		
	}
	function form_export()
	{
		$selected_id = 1;
		$none_option = "";
		$submit_on_change = FALSE;
		$all = FALSE;
		$all_items = TRUE;
		$mode = 1;
		$spec_option = "";
		 start_form(true);

		 start_table(TABLESTYLE2, "width=40%");

		 table_section_title("Export Purchase Order");

		 $company_record = get_company_prefs();

		$this->get_id_range();

		//$sql = "SELECT supp_name, order_no FROM " . $this->company_prefix . "purch_orders o, " . $this->company_prefix . "suppliers s where s.supplier_id = o.supplier_id";
		//echo combo_input("SupplierPO", $selected_id, $sql, 'supplier_id', 'supp_name',
/*
		echo combo_input("order_no2", $this->order_no, $sql, 'supp_name', 'order_no',
        		array(
                		//'format' => '_format_add_curr',
            			'order' => array('order_no'),
                		//'search_box' => $mode!=0,
                		'type' => 1,
        			//'search' => array("order_no","supp_name"),
                		//'spec_option' => $spec_option === true ? _("All Suppliers") : $spec_option,
                		'spec_id' => $all_items,
                		'select_submit'=> $submit_on_change,
                		'async' => false,
                		//'sel_hint' => $mode ? _('Press Space tab to filter by name fragment') :
                		//_('Select supplier'),
                		//'show_inactive'=>$all
                	)
		);
*/

		 text_row("Export " . $this->vendor . " Purchase Order ID:", 'order_no', $this->order_no, 10, 10);

		 end_table(1);

		 hidden('action', 'c_export');
		 submit_center('cexport', "Export  " . $this->vendor . " Purchase Orders");

		 end_form();
	}
	function related_tabs()
	{
		//echo "<br /><b>show form</b><br />";
		$action = $this->action;
		//echo "<br />Action $action<br />";
		if( isset( $this->ui_class ) )
			$this->tabs = $this->ui_class->tabs;
		foreach( $this->tabs as $tab )
		{
			//echo "<br />" . $tab['action'] . "<br />";
			if( $action == $tab['action'] )
			{
				echo $tab['title'];
				echo '&nbsp;|&nbsp;';
			}
			else
			{
				if( $tab['hidden'] == FALSE )
				{
					hyperlink_params($_SERVER['PHP_SELF'], 
						_("&" .  $tab['title']), 
						"action=" . $tab['action'], 
						false);
					echo '&nbsp;|&nbsp;';
				}
			}
		}
	}
	function show_form()
	{
		$action = $this->action;
		if( isset( $this->ui_class ) )
			$this->tabs = $this->ui_class->tabs;
		foreach( $this->tabs as $tab )
		{
			if( $action == $tab['action'] )
			{
				//Call appropriate form
				$form = $tab['form'];
				echo $form . "<br />";
				if( isset( $this->ui_class ) )
					$this->ui_class->$form();
				else
					$this->$form();
			}
		}
	}
	function base_page()
	{
		if( isset( $this->ui_class ) )
			page(_($this->ui_class->help_context));
		else
			page(_($this->help_context));
		$this->related_tabs();
	}
	function display()
	{
		$this->base_page();
		$this->show_form();
		end_page();
	}
}
?>
