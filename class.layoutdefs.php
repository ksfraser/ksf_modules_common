<?php

/*
$layout_defs["Accounts"]["subpanel_setup"]['iichk_ksfii_planning_areas_accounts'] = array (
  'order' => 100,
  'module' => 'iichk_KSFII_Planning_Areas',
  'subpanel_name' => 'default',
  'sort_order' => 'asc',
  'sort_by' => 'id',
  'title_key' => 'LBL_IICHK_KSFII_PLANNING_AREAS_ACCOUNTS_FROM_IICHK_KSFII_PLANNING_AREAS_TITLE',
  'get_subpanel_data' => 'iichk_ksfii_planning_areas_accounts',
  'top_buttons' => 
  array (
    0 => 
    array (
      'widget_class' => 'SubPanelTopButtonQuickCreate',
    ),
    1 => 
    array (
      'widget_class' => 'SubPanelTopSelectButton',
      'mode' => 'MultiSelect',
    ),
  ),
*/

require_once( 'class.origin.php' );

class layoutdefs extends origin
{
	protected $main_module;	// Module on whose screen this subpanel is displayed
	protected $order;	// => 100,
	protected $module;	// => 'iichk_KSFII_Planning_Areas',
	protected $subpanel_name;	// => 'default',
	protected $sort_order;	// => 'asc',
	protected $sort_by;	// => 'id',
	protected $title_key;	// => 'LBL_IICHK_KSFII_PLANNING_AREAS_ACCOUNTS_FROM_IICHK_KSFII_PLANNING_AREAS_TITLE',
	protected $get_subpanel_data;	// => 'iichk_ksfii_planning_areas_accounts',
	protected $top_buttons;	// => array()

}
