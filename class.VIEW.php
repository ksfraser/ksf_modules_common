<?php

require_once( 'class.origin.php' );

/**//*********************************************
* A class to display pure HTML
*
*************************************************/
class HTML_VIEW extends origin
{
	protected $label;

	function __construct()
	{
		parent::__construct();
	}
	/**//***************************************************************
        *
        *
        * @param
        * @return
        *******************************************************************/
        function row_label()
        {
                $this->tr();
                $this->label_cell();
        }
        /**//***************************************************************
        *
        *
        * @param
        * @return
        *******************************************************************/
        function label_cell()
        {
                echo "<td class='label'>$this->label</td>";
        }
        /**//***************************************************************
        *
        *
        * @param
        * @return
        *******************************************************************/
        function tr()
        {
                echo "<tr>";
        }
        /**//***************************************************************
        *
        *
        * @param
        * @return
        *******************************************************************/
        function td()
        {
                echo "<td>";
        }
        /**//***************************************************************
        *
        *
        * @param
        * @return
        *******************************************************************/
        function close_td()
        {
                echo "</td>";
        }
        /**//***************************************************************
        *
        *
        * @param
        * @return
        *******************************************************************/
        function close_tr()
        {
                echo "</tr>";
        }
        /**//***************************************************************
        *
        *
        * @param
        * @return
        *******************************************************************/
        function newline()
        {
                echo "\n";
        }


}

/**//*****************************************************************
* A class for displaying HTML
*
*	This class uses a lot of FrontAccounting functions
*	Goal is to replace them eventually by refactoring
*
***********************************************************************/
class VIEW extends HTML_VIEW
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

	function __construct()
	{
		$this->use_js();
		$this->set_var( "page_mode", "simple" );
		$this->set_var( "use_date_picker", FALSE );
		$this->set_var( "table_width", "70%" );
	}
	function __destruct()
	{
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
	function new_form()
	{
		start_form();
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
	function use_js()
	{
		$this->js = "";
		if ($this->use_date_picker)
        		$this->js .= get_js_date_picker();

		page(_($help_context = "FA-CRM"), false, false, "", $this->js);


	}
	function build_page()
	{
		//need to take the form, tables etc for the page
		//and create them to be displayed
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
	/**//************************************************************************
	* Create a COMBO INPUT list.  
	*
	* cloned and edited from FA includes/ui/ui_lists.inc
	*  Removing the SQL function - assuming data array has already been created
	*
	******************************************************************************/
/****
	function combo_input($name, $selected_id, $sql, $valfield, $namefield, $options=null, $type=null)
	{
		global $Ajax, $path_to_root, $SysPrefs ;
		
		$opts = array(	  // default options
			        // special option parameters
			'spec_option'=>false,   // option text or false
			'spec_id' => 0,         // option id
			        // submit on select parameters
			'default' => '', // default value when $_POST is not set
			'multi' => false,       // multiple select
			'select_submit' => false, //submit on select: true/false
			'async' => true,        // select update via ajax (true) vs _page_body reload
			        // search box parameters
			'sel_hint' => null,
			'search_box' => false,  // name or true/false
			'type' => 0,    // type of extended selector:
			        // 0 - with (optional) visible search box, search by fragment inside id
			        // 1 - with hidden search box, search by option text
			        // 2 - with (optional) visible search box, search by fragment at the start of id
			        // 3 - TODO reverse: box with hidden selector available via enter; this
			        // would be convenient for optional ad hoc adding of new item
			'search_submit' => true, //search submit button: true/false
			'size' => 8,    // size and max of box tag
			'max' => 50,
			'height' => false,      // number of lines in select box
			'cells' => false,       // combo displayed as 2 <td></td> cells
			'format' => null,        // format functions for regular options
			'disabled' => false,
			'box_hint' => null, // box/selectors hints; null = std see below
			'category' => false, // category column name or false
			'show_inactive' => false, // show inactive records.
			'editable' => false, // false, or length of editable entry field
			'editlink' => false     // link to entity entry/edit page (optional)
		);
		// ------ merge options with defaults ----------
		if($options != null)
		        $opts = array_merge($opts, $options);
		$search_box = $opts['search_box']===true ? '_'.$name.'_edit' : $opts['search_box'];
		// select content filtered by search field:
		$search_submit = $opts['search_submit']===true ? '_'.$name.'_button' : $opts['search_submit'];
		// select set by select content field
		$search_button = $opts['editable'] ? '_'.$name.'_button' : ($search_box ? $search_submit : false);
	
		$select_submit =  $opts['select_submit'];
		$spec_id = $opts['spec_id'];
		$spec_option = $opts['spec_option'];
		if ($opts['type'] == 0) {
		        $by_id = true;
		        $class = 'combo';
		} elseif($opts['type'] == 1) {
		        $by_id = false;
		        $class = 'combo2';
		} else {
		        $by_id = true;
		        $class = 'combo3';
		}
	
		$disabled = $opts['disabled'] ? "disabled" : '';
		$multi = $opts['multi'];
	
		if(!count($opts['search'])) {
		        $opts['search'] = array($by_id ? $valfield : $namefield);
		}
		if ($opts['sel_hint'] === null)
		        $opts['sel_hint'] = $by_id || $search_box==false ?
		                '' : _('Press Space tab for search pattern entry');
	
		if ($opts['box_hint'] === null)
		        $opts['box_hint'] = $search_box && $search_submit != false ?
		                ($by_id ? _('Enter code fragment to search or * for all')
		                : _('Enter description fragment to search or * for all')) :'';
	
		if ($selected_id == null) {
		        $selected_id = get_post($name, (string)$opts['default']);
		}
		if(!is_array($selected_id))
		        $selected_id = array((string)$selected_id); // code is generalized for multiple selection support
	
		$txt = get_post($search_box);
		$rel = '';
		$limit = '';
		if (isset($_POST['_'.$name.'_update'])) { // select list or search box change
		        if ($by_id) $txt = $_POST[$name];
	
		        if (!$opts['async'])
		                $Ajax->activate('_page_body');
		        else
		                $Ajax->activate($name);
		}
		if (isset($_POST[$search_button])) {
		        if (!$opts['async'])
		                $Ajax->activate('_page_body');
		        else
		                $Ajax->activate($name);
		}
		if ($search_box) {
		        // search related sql modifications
	
		        $rel = "rel='$search_box'"; // set relation to list
		        if ($opts['search_submit']) {
		                if (isset($_POST[$search_button])) {
		                        $selected_id = array(); // ignore selected_id while search
		                        if (!$opts['async'])
		                                $Ajax->activate('_page_body');
		                        else
		                                $Ajax->activate($name);
		                }
		                if ($txt == '') {
		                        if ($spec_option === false && $selected_id == array())
		                                $limit = ' LIMIT 1';
		                        else
		                                $opts['where'][] = $valfield . "=". db_escape(get_post($name, $spec_id));
		                }
		                else
		                        if ($txt != '*') {
	
		                                foreach($opts['search'] as $i=> $s)
		                                        $opts['search'][$i] = $s . " LIKE "
		                                                .db_escape(($class=='combo3' ? '' : '%').$txt.'%');
		                                $opts['where'][] = '('. implode($opts['search'], ' OR ') . ')';
		                        }
		        }
		}
	
		// ------ make selector ----------
		$selector = $first_opt = '';
		$first_id = false;
		$found = false;
		$lastcat = null;
		$edit = false;

		foreach( $list_arr as $RES_ARR )
		{
			if( isset( $RES_ARR['value'] ) )
			{
		       		$value = $RES_ARR['value'];
			}
			else
			{
		       		$value = $RES_ARR[0];
			}
			if( isset( $RES_ARR['description'] ) )
			{
				$descr = $RES_ARR['description'];
			}
			else
			if( null == $opts['format'] )
			{
				$descr = $RES_ARR[1];
			}
			else
			{
				$descr = call_user_func($opts['format'], $RES_ARR);
			}
			$sel = '';
			if (get_post($search_button) && ($txt == $value)) {
				$selected_id[] = $value;
			}

			if (in_array((string)$value, $selected_id, true)) 
			{
				$sel = 'selected';
				$found = $value;
				$edit = $opts['editable'] && $RES_ARR['editable'] && (@$_POST[$search_box] == $value) ? $RES_ARR[1] : false; // get non-formatted description
				if ($edit)
					break;  // selected field is editable - abandon list construction
			}
			// show selected option even if inactive
			if (!$opts['show_inactive'] && @$RES_ARR['inactive'] && $sel==='') {
				continue;
			} else
			{
				$optclass = @$RES_ARR['inactive'] ? "class='inactive'" : '';
			}

			if ($first_id === false) {
				$first_id = $value;
				$first_opt = $descr;
			}
			$cat = $RES_ARR[$opts['category']];
			if ($opts['category'] !== false && $cat != $lastcat){
				if ($lastcat!==null)
					$selector .= "</optgroup>";
				$selector .= "<optgroup label='".$cat."'>\n";
				$lastcat = $cat;
			}
			$selector .= "<option $sel $optclass value='$value'>$descr</option>\n";
		        }
		        if ($lastcat!==null)
		                $selector .= "</optgroup>";
	
		 // Prepend special option.
		if ($spec_option !== false) 
		{ 
			// if special option used - add it
		        $first_id = $spec_id;
		        $first_opt = $spec_option;
		        $sel = $found===false ? 'selected' : '';
		        $optclass = @$RES_ARR['inactive'] ? "class='inactive'" : '';
		        $selector = "<option $sel value='$first_id'>$first_opt</option>\n"
		                . $selector;
		}
	
		if ($found===false) {
		        $selected_id = array($first_id);
		}
	
		$_POST[$name] = $multi ? $selected_id : $selected_id[0];
	
		if ($SysPrefs->use_popup_search)
		        $selector = "<select id='$name' autocomplete='off' ".($multi ? "multiple" : '')
		        . ($opts['height']!==false ? ' size="'.$opts['height'].'"' : '')
		        . "$disabled name='$name".($multi ? '[]':'')."' class='$class' title='"
		        . $opts['sel_hint']."' $rel>".$selector."</select>\n";
		else
		        $selector = "<select autocomplete='off' ".($multi ? "multiple" : '')
		        . ($opts['height']!==false ? ' size="'.$opts['height'].'"' : '')
		        . "$disabled name='$name".($multi ? '[]':'')."' class='$class' title='"
		        . $opts['sel_hint']."' $rel>".$selector."</select>\n";
		if ($by_id && ($search_box != false || $opts['editable']) ) 
		{
		        // on first display show selector list
		        if (isset($_POST[$search_box]) && $opts['editable'] && $edit) 
			{
		                $selector = "<input type='hidden' name='$name' value='".$_POST[$name]."'>"
		                ."<input type='text' $disabled name='{$name}_text' id='{$name}_text' size='".
		                        $opts['editable']."' maxlength='".$opts['max']."' $rel value='$edit'>\n";
		                        set_focus($name.'_text'); // prevent lost focus
		        } else if (get_post($search_submit ? $search_submit : "_{$name}_button"))
			{
		                set_focus($name); // prevent lost focus
			}
		        if (!$opts['editable'])
			{
		                $txt = $found;
			}
		        $Ajax->addUpdate($name, $search_box, $txt ? $txt : '');
		}
	
		$Ajax->addUpdate($name, "_{$name}_sel", $selector);
	
		// span for select list/input field update
		$selector = "<span id='_{$name}_sel'>".$selector."</span>\n";
	
		 // if selectable or editable list is used - add select button
		if ($select_submit != false || $search_button) {
		// button class selects form reload/ajax selector update
		        $selector .= sprintf(SELECT_BUTTON, $disabled, user_theme(),
		                (fallback_mode() ? '' : 'display:none;'),
		                 '_'.$name.'_update')."\n";
		}
	// ------ make combo ----------
		$edit_entry = '';
		if ($search_box != false) 
		{
		        $edit_entry = "<input $disabled type='text' name='$search_box' id='$search_box' size='".  $opts['size']."' maxlength='".$opts['max'].
		                "' value='$txt' class='$class' rel='$name' autocomplete='off' title='" .$opts['box_hint']."'" .(!fallback_mode() && !$by_id ? " style=display:none;":'') .">\n";
		        if ($search_submit != false || $opts['editable']) 
			{
		                $edit_entry .= sprintf(SEARCH_BUTTON, $disabled, user_theme(), (fallback_mode() ? '' : 'display:none;'), $search_submit ? $search_submit : "_{$name}_button")."\n";
		        }
		}
		default_focus(($search_box && $by_id) ? $search_box : $name);
	
		$img = "";
		if ($SysPrefs->use_popup_search && (!isset($opts['fixed_asset']) || !$opts['fixed_asset']))
		{
		        $img_title = "";
		        $link = "";
			$id = $name;
		}
		if ($SysPrefs->use_popup_windows) 
		{
			switch (strtolower($type)) 
			{
				case "stock":
					$link = $path_to_root . "/inventory/inquiry/stock_list.php?popup=1&type=all&client_id=" . $id;
					$img_title = _("Search items");
					break;
				case "stock_manufactured":
					$link = $path_to_root . "/inventory/inquiry/stock_list.php?popup=1&type=manufactured&client_id=" . $id;
					$img_title = _("Search items");
					break;
				case "stock_purchased":
					$link = $path_to_root . "/inventory/inquiry/stock_list.php?popup=1&type=purchasable&client_id=" . $id;
					$img_title = _("Search items");
					break;
				case "stock_sales":
					$link = $path_to_root . "/inventory/inquiry/stock_list.php?popup=1&type=sales&client_id=" . $id;
					$img_title = _("Search items");
					break;
				case "stock_costable":
					$link = $path_to_root . "/inventory/inquiry/stock_list.php?popup=1&type=costable&client_id=" . $id;
					$img_title = _("Search items");
					break;
				case "component":
					$parent = $opts['parent'];
					$link = $path_to_root . "/inventory/inquiry/stock_list.php?popup=1&type=component&parent=".$parent."&client_id=" . $id;
					$img_title = _("Search items");
					break;
				case "kits":
					$link = $path_to_root . "/inventory/inquiry/stock_list.php?popup=1&type=kits&client_id=" . $id;
					$img_title = _("Search items");
					break;
				case "customer":
					$link = $path_to_root . "/sales/inquiry/customers_list.php?popup=1&client_id=" . $id;
					$img_title = _("Search customers");
					break;
				case "branch":
					$link = $path_to_root . "/sales/inquiry/customer_branches_list.php?popup=1&client_id=" . $id . "#customer_id";
					$img_title = _("Search branches");
					break;
				case "supplier":
					$link = $path_to_root . "/purchasing/inquiry/suppliers_list.php?popup=1&client_id=" . $id;
					$img_title = _("Search suppliers");
					break;
				case "account":
					$link = $path_to_root . "/gl/inquiry/accounts_list.php?popup=1&client_id=" . $id;
					$img_title = _("Search GL accounts");
					break;
	                } //switch
               	} //if
	
                if ($link !=="") 
		{
                	$theme = user_theme();
                	$img = '<img src="'.$path_to_root.'/themes/'.$theme.'/images/'.ICON_VIEW.
                        	'" style="vertical-align:middle;width:12px;height:12px;border:0;" onclick="javascript:lookupWindow(&quot;'.
                        	$link.'&quot;, &quot;&quot;);" title="' . $img_title . '" style="cursor:pointer;" />';
                }
        }

        if ($opts['editlink'])
                $selector .= ' '.$opts['editlink'];

        if ($search_box && $opts['cells'])
                $str = ($edit_entry!='' ? "<td>$edit_entry</td>" : '')."<td>$selector$img</td>";
        else
                $str = $edit_entry.$selector.$img;
        return $str;
	} //fcn
*/
}

?>
