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
class fa_locations extends table_interface
{
	/*
| loc_code         | varchar(5)   | NO   | PRI |         |       |
| location_name    | varchar(60)  | NO   |     |         |       |
| delivery_address | tinytext     | NO   |     | NULL    |       |
| phone            | varchar(30)  | NO   |     |         |       |
| phone2           | varchar(30)  | NO   |     |         |       |
| fax              | varchar(30)  | NO   |     |         |       |
| email            | varchar(100) | NO   |     |         |       |
| contact          | varchar(30)  | NO   |     |         |       |
| inactive         | tinyint(1)   | NO   |     | 0       |       |
	 
*/
	protected $loc_code         ;// varchar(5)   | NO   | PRI |         |       |
	protected $location_name    ;// varchar(60)  | NO   |     |         |       |
	protected $delivery_address ;// tinytext     | NO   |     | NULL    |       |
	protected $phone            ;// varchar(30)  | NO   |     |         |       |
	protected $phone2           ;// varchar(30)  | NO   |     |         |       |
	protected $fax              ;// varchar(30)  | NO   |     |         |       |
	protected $email            ;// varchar(100) | NO   |     |         |       |
	protected $contact          ;// varchar(30)  | NO   |     |         |       |
	protected $inactive         ;// tinyint(1)   | NO   |     | 0       |       |

	function __construct( $caller = null )
	{
		parent::__construct( $caller );
		$descl = 'varchar(' . DESCRIPTION_LENGTH . ')';
		$this->table_details['tablename'] = TB_PREF . get_class( $this );

	}

