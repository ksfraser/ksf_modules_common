<?php

require_once( 'class.origin.php' );


class VIEW extends origin
{
	var $js;
	var $page_mode;
	var $header_row = array();
	var $column_type = array();	//Tells us the type of each header_row column.  MANDATORY
					//Valid values are "", amount, date, edit, delete, inactive
	var $db_column_name = array();
	var $db_result;			//MYSQL Result pointer
	var $use_date_picker;
	var $db_table_pager; 		// = & new_db_pager( $this->table_name, $this->sql, $this->col_array );
	var $table_width;
	var $help_context;
	var $page_elements;

	/**//****************************************************
	* Constructor
	*
	* @param string help context
	* @returns none
	*/
	function __construct( $help_context = "", $page_mode = "simple", $use_date_picker = FALSE )
	{
		$this->set_var( "help_context", $help_context );
		$this->set_var( "page_mode", $page_mode );
		$this->set_var( "use_date_picker", $use_date_picker );
		$this->set_var( "table_width", "70%" );
		//$this->use_js();
		$this->page_elements = array();
	}
	function __destruct()
	{
	}
	function addPageElements( $element )
	{
		$this->page_elements[] = $element;
	}
	function run()
	{
		$display_notification( __FILE__ . "::" . __CLASS__ . "::"  . __METHOD__ . ":" . __LINE__, "WARN" );
		$this->new_page();
		$this->build_page();
		$this->end_page();
	}
	function display_error( $error )
	{
		display_error(_( $error ) );
	}
	function display_notification( $msg )
	{
		display_notification(_( $msg ) );
	}
	function set_focus( $field )
	{	
		set_focus( $field );
	}
	function new_page()
	{
		if( $this->page_mode == "simple" )
		{
			simple_page_mode(true);
		}
	}
	function start_form($bool = true)
	{
		start_form( $bool );
	}
	function new_form($bool = true)
	{
		$this->start_form( $bool );
	}
	function new_table()
	{
		start_table(TABLESTYLE, "width=75%");
	}
	function table_header()
	{
		//$this->header_row = array(_("Asset Type"),_("Asset Name"),_("Serial Number"), _("Purchase Date"),
		//				_("Purchase Value"), _("Current Value"), "", "", _("A"));
		inactive_control_column($this->header_row);
		table_header($this->header_row);
	}
	function db_pager( $model )
	{
/*
		$table = & new_db_pager( $model->db_pager_tablename, $model->db_pager_sql, $model->db_pager_col_array );
		//$table = $this->db_table_pager;
		$table->width = $this->table_width;
		display_db_pager( $table );
*/
	}
	function db_result2rows()
	{
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
	}
	function edit_table()
	{
		$this->start_table();
		//These take values out of $_POST
		foreach( $this->header_row as $col )
		{
			if( $this->col_type[$col] == "amount" )
			{
				amount_row( _($this->header_row[$col]), $this->db_column_name[$col], null, null, null, 2);
			}
			else if( $this->col_type[$col] == "date" )
			{
				date_row(_($this->header_row[$col]), $this->db_column_name[$col], '', null, 0, 0, 0, null, true);
			}
			else if( $this->col_type[$col] == "edit" )
			{
			}
			else if( $this->col_type[$col] == "delete" )
			{
			}
			else if( $this->col_type[$col] == "inactive" )
			{
			}
			else
			{
				text_row( _($this->header_row[$col]), $this->db_column_name[$col], null, 50, 50 );
			}
		}
		$this->end_table();
	}
	function end_table()
	{
		end_table(1);
	}
	function end_form()
	{
		end_form();
	}
	function end_page()
	{
		end_page();
	}
	/**//**
	*Use JS
	*
	* @param none 
	* @returns none
	*/
	function use_js()
	{
		$this->js = "";
		if ($this->use_date_picker)
        		$this->js .= get_js_date_picker();

		page(_($this->help_context), false, false, "", $this->js);


	}
	function Page()
	{
		page(_($this->help_context), false, false, "", $this->js);
	}
	function display()
	{
		foreach( $this->page_elements as $element )
		{
			$element->display();
		}
	}
	function build_page()
	{
		//need to take the form, tables etc for the page
		//and create them to be displayed
		$this->display();
	}
	function dropdown( $label, $choices_array )
	{
		/*
		  //Compare Combo
		*               global $sel;
		*               $sel = array(_("Accumulated"), _("Period Y-1"), _("Budget"));
		*               echo "<td>"._("Compare to").":</td>\n";
		*               echo "<td>";
		*               echo array_selector('Compare', null, $sel);
		*               echo "</td>\n";
		*/
		echo "<td>" . $label . ":</td>\n<td>" . array_selector( $name, null, $choices_array ) . "</td>\n";
	}
	function bool( $row, $caller )
	{
		text_row($row['label'], $row['pref_name'], $caller->$row['pref_name'], 1, 1);
	}
	function textrow( $row, $caller )
	{
		text_row($row['label'], $row['pref_name'], $caller->$row['pref_name'], 20, 40);
	}
	function number( $row, $caller )
	{
		amount_row( _($row['label']), $row['pref_name'], null, null, null, 2);
	}
	function date( $row, $caller )
	{
		//date_row($label, $name, $title=null, $check=null, $inc_days=0, $inc_months=0, $inc_years=0, $params=null, $submit_on_change=false)

		date_row(_($row['label']), $row['pref_name'], '', null, 0, 0, 0, "param", false);
	}
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

}

?>
