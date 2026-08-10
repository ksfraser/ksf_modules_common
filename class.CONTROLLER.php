<?php

/**************************************************************************
*
*	CONTROLLER
*
**************************************************************************/
global $path_to_ksfcommon;
require_once( $path_to_ksfcommon . '/db_base.php' );
require_once( $path_to_ksfcommon . '/class.VIEW.php' );

class controller extends db_base
{
	var $mode;
	var $action;
	var $selected_id;
	var $mode_callbacks = array();
	var $view;
	var $model;
	function __construct( $host, $user, $pass, $database, $prefs_tablename )
	{
		parent::__construct( $host, $user, $pass, $database, $prefs_tablename );

		if( isset( $_POST['Mode'] ) )
			$this->set_var( "mode", $_POST['Mode'] );
		else
			$this->set_var( "mode", "unknown" );
		if( isset( $_POST['action'] ) )
			$this->set_var( "action", $_POST['action'] );
		else
		if( isset( $_GET['action'] ) )
			$this->set_var( "action", $_GET['action'] );

		if( isset( $_POST['selected_id'] ) )
			$this->set_var( "selected_id", $_POST['selected_id'] );
		$this->view = new VIEW();
		$this->model = NULL;
		/*********************************
		*	Need to set mode_callbacks
		*	in inheriting classes
		*********************************/
		$this->mode_callbacks["unknown"] = "config_form";
           
		$this->config_values[] = array( 'pref_name' => 'mode', 'label' => 'Mode' );

                //The forms/actions for this module
                //Hidden tabs are just action handlers, without accompying GUI elements.
                //$this->tabs[] = array( 'title' => '', 'action' => '', 'form' => '', 'hidden' => FALSE );
                $this->tabs[] = array( 'title' => 'Configuration', 'action' => 'config', 'form' => 'config_form', 'hidden' => FALSE );
       
	}
        function loadprefs( $prefarr = NULL )
        {
		if( isset( $prefarr ) )
		{
                	foreach( $prefarr as $row )
                	{
                	        $this->set_var( $row['pref_name'], $this->get_pref( $row['pref_name'] ) );
                	}
		}
		else
		{
                	// Get last oID exported
                	foreach( $this->config_values as $row )
                	{
                	        $this->set_var( $row['pref_name'], $this->get_pref( $row['pref_name'] ) );
                	}
		}
        }
        function updateprefs( $prefarr = NULL )
        {
                foreach( $this->config_values as $row )
                {
                        if( isset( $_POST[$row['pref_name']] ) )
                        {
                                $this->set_var( $row['pref_name'], $_POST[ $row['pref_name'] ] );
                                $this->set_pref( $row['pref_name'], $_POST[ $row['pref_name'] ] );
                        }
			else if( isset( $this->$row['pref_name'] ) )
			{
                                $this->set_pref( $row['pref_name'], $this->$row['pref_name'] );
			}
                }
		if( isset( $prefarr ) )
		{
			echo "updateprefs <br />";
			//var_dump( $prefarr );
                	foreach( $prefarr as $row )
                	{
				echo $row['pref_name'] . "<br />";
                	        if( isset( $_POST[$row['pref_name']] ) )
                	        {
                	                $this->set_var( $row['pref_name'], $_POST[ $row['pref_name'] ] );
                	                $this->set_pref( $row['pref_name'], $_POST[ $row['pref_name'] ] );
					echo "Field " . $row['pref_name'] . " set to " . $_POST[ $row['pref_name'] ];
					echo "<br />";
					//display_notification( "Field " . $row['pref_name'] . " set to " . $_POST[ $row['pref_name'] ] );
                	        }
				else if( isset( $this->$row['pref_name'] ) )
				{
                        	        $this->set_pref( $row['pref_name'], $this->$row['pref_name'] );
					echo "Field " . $row['pref_name'] . " set to " . $this->$row['pref_name'];
					echo "<br />";
				}
				else
				{
					//display_notification( "Post " . $row['pref_name'] . " not set <br />" );
					//echo "Neither var nor Post " . $row['pref_name'] . " not set <br />";
					//var_dump( $this->$row['pref_name'] );
				}
                	}
		}
        }
        function checkprefs()
        {
                $this->updateprefs();
        }
        function install()
        {
                $this->create_prefs_tablename();
                $this->loadprefs();
                $this->updateprefs();
                if( isset( $this->redirect_to ) )
                {
                        header("Location: " . $this->redirect_to );
                }
        }
        function config_form()
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
                //This currently only puts text boxes on the config screen!
                foreach( $this->config_values as $row )
                {
                                text_row($row['label'], $row['pref_name'], $this->$row['pref_name'], 20, 40);
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

        function related_tabs()
        {
                $action = $this->action;
                foreach( $this->tabs as $tab )
                {
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
                foreach( $this->tabs as $tab )
                {
                        if( $action == $tab['action'] )
                        {
                                //Call appropriate form
                                $form = $tab['form'];
                                $this->$form();
                        }
                }
        }
	function add_addons()
	{
                $addondir = "./addons/";
                foreach (glob("{$addondir}/config.*.php") as $filename)
                {
                        //echo "opening module config file " . $filename . "<br />\n";
                        include_once( $filename );
                }

                /*
                 * Loop through the $configArray to set loading modules in right order
                 */
                foreach( $configArray as $carray )
                {
                        $modarray[$carray['loadpriority']][] = $carray;
                }
	        /*
                 * locate Module class files to open
                 */
                foreach( $modarray as $priarray )
                {
                        foreach( $priarray as $marray )
                        {

                                $res = include_once( $addondir . "/" . $marray['loadFile'] );
                                if( TRUE == $res )
                                {
                                        $marray['objectName'] = new $marray['className'];
                                        if( isset( $marray['objectName']->observers ) )
                                        {
                                                foreach( $marray['objectName']->observers as $obs )
                                                {
                                                        $this->observers[] = $obs;
                                                }
                                        }
                                }
                                else
                                {
                                        echo "Attempt to open " . $addondir . "/" . $marray['loadFile'] . " FAILED!<br />";
                                }
                        }
                }
	}
	function valuesarray2table( $array )
	{
		foreach( $array as $row )
		{
			if( isset( $row['type'] ) )
			{
				switch( $row['type'] ) {
	
					case "bool":
							//$this->view->bool( $row, $this );
							$this->view->textrow( $row, $this );
							break;
					case "flag":
							break;
					case "addr":
					case "city":
					case "prov":
					case "country":
							$this->view->textrow( $row, $this );
							break;
					case "postal":
							break;
					case "date":
							//$this->view->date( $row, $this );
							$this->view->textrow( $row, $this );
							break;
					case "text":
					case "currency":
							break;
					case "int":
							$this->view->number( $row, $this );
							break;
					default:
							$this->view->textrow( $row, $this );
							break;
				}
			}
			else
			{
				$this->view->textrow( $row, $this );
			}
		}
		$this->view->end_table();
	}
	function run()
	{
                if ($this->found) {
                        $this->loadprefs();
                }
                else
                {
                        $this->install();
                        $this->set_var( 'action', "show" );
                }

		$result = $this->model->get_all_rows();

		$this->view->new_table();
		//These should come from the data dictionary having:
		//	Readable name, database column name, data type
		$this->view->header_row = $this->model->header_row;
		$this->view->col_type = $this->model->col_type;
		$this->view->db_column_name = $this->model->db_column_name;
		$this->view->db_result = $result;
		$this->view->db_result2rows();
		$this->view->end_table();

		if( isset($this->mode) )
		{
			if( is_callable( $this->mode_callbacks[$this->mode], $this ) )
			{
				//echo "CALLABLE::" . $this->mode . "::" . $this->mode_callbacks[$this->mode] . "<br />";
				$fcn = $this->mode_callbacks[$this->mode];
				$this->$fcn();
			}
			else
			{
				$this->view->display_notification( "error in action definition" );
			}
		}
		else
		{
			$this->view->display_notification( "mode not set!" );
		}
                $this->related_tabs();
                $this->show_form();
		$this->view->end_page();
	}
	function screen_mode_unknown()
	{
		if( isset( $this->model->db_pager_sql ) )
		{
			$this->view->db_pager( $this->model );
		}
		echo "screen_unknown";
		$this->config_form();
	}
	function go_install()
	{
/*
*/
	}
}
?>
