<?php

require_once( 'class.table_interface.php' );

$path_to_root = "../..";
/*
if( ! defined( 'item_img_name' ) )
{
	function item_img_name()
	{
		return "";
	}
}
*/

/**********************************************************************
 * Helper class for handling images in various modules
 * *******************************************************************/
class fa_item_codes extends table_interface
{
	/*
| id          | int(11) unsigned     | NO   | PRI | NULL    | auto_increment |
| item_code   | varchar(64)          | YES  | MUL | NULL    |                |
| stock_id    | varchar(64)          | YES  | MUL | NULL    |                |
| description | varchar(200)         | NO   |     |         |                |
| category_id | smallint(6) unsigned | NO   |     | NULL    |                |
| quantity    | double               | NO   |     | 1       |                |
| is_foreign  | tinyint(1)           | NO   |     | 0       |                |
| inactive    | tinyint(1)           | NO   |     | 0       |                |
*/
	protected $id          ;// int(11) unsigned     | NO   | PRI | NULL    | auto_increment |
	protected $item_code   ;// varchar(64)          | YES  | MUL | NULL    |                |
	protected $stock_id    ;// varchar(64)          | YES  | MUL | NULL    |                |
	protected $description ;// varchar(200)         | NO   |     |         |                |
	protected $category_id ;// smallint(6) unsigned | NO   |     | NULL    |                |
	protected $quantity    ;// double               | NO   |     | 1       |                |
	protected $is_foreign  ;// tinyint(1)           | NO   |     | 0       |                |
	protected $inactive    ;// tinyint(1)           | NO   |     | 0       |                |
	function __construct( $caller = null )
	{
		parent::__construct( $caller );
		$descl = 'varchar(' . DESCRIPTION_LENGTH . ')';
		$this->table_details['tablename'] = TB_PREF . get_class( $this );

	}

