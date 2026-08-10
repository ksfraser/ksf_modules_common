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
class fa_purch_data extends table_interface
{
	/*
| supplier_id          | int(11)      | NO   | PRI | 0       |       |
| stock_id             | varchar(64)  | NO   | PRI |         |       |
| price                | double       | NO   |     | 0       |       |
| suppliers_uom        | char(50)     | NO   |     |         |       |
| conversion_factor    | double       | NO   |     | 1       |       |
| supplier_description | varchar(200) | YES  |     | NULL    |       |
	 
*/
	protected $supplier_id          ;// int(11)      | NO   | PRI | 0       |       |
	protected $stock_id             ;// varchar(64)  | NO   | PRI |         |       |
	protected $price                ;// double       | NO   |     | 0       |       |
	protected $suppliers_uom        ;// char(50)     | NO   |     |         |       |
	protected $conversion_factor    ;// double       | NO   |     | 1       |       |
	protected $supplier_description ;// varchar(200) | YES  |     | NULL    |       |

	function __construct( $caller = null )
	{
		parent::__construct( $caller );
		$descl = 'varchar(' . DESCRIPTION_LENGTH . ')';
		$this->table_details['tablename'] = TB_PREF . get_class( $this );

	}

