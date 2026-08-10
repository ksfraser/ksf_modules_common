<?php

require_once( '../ksf_modules_common/class.ksf_file.php' );

/**********************************************************************
 * Helper class for handling images in various modules
 * *******************************************************************/
class fa_image extends ksf_file_upload
{
	protected $stock_id; //!< stock_id of item we are handling images for
	var $image_width;
	var $image_height;
	var $image_type;
	var $image_attr;
	var $image_path; 	//!<DIR where are the images stored.  default company/X/images...
	var $images_count;	//!< How many images exist for this stock_id
	var $maxpics;
	function __construct( $stock_id, $maxpics = 10 )
	{
		$this->stock_id = $stock_id;
		parent::__construct( item_img_name($this->stock_id) . ".jpg" );
		$this->set( 'maxpics', $maxpics );
	}
	/*************************************************************************
	 * Is the image a JPG/GIF/PNG
	 *
	 * Uses the image_type attribute returned from get_image_attributes
	 * @return bool
	 * **********************************************************************/
	function isValidImage()
	{
		switch( $this->image_type )
		{
			case IMAGETYPE_GIF:
			case IMAGETYPE_JPEG:
			case IMAGETYPE_PNG:
					return TRUE;
					break;
			default:
				return FALSE;
				break;
		}
		/*
		if ($this->image_type != IMAGETYPE_GIF && $this->image_type != IMAGETYPE_JPEG && $this->image_type != IMAGETYPE_PNG)
			return false;
		else
			return true;
		 */
	}
	/********************************************************************
	 * Set the base directory where we store pics.
	 *
	 * @param string directory path
	 * @return bool does this path exist or can we make it.
	 * *****************************************************************/
	function set_image_path( $path )
	{
		$old_path = $this->path;
		$this->path = $path;
		if( $this->pathExists() )
			return TRUE;
		else
			if( $this->make_path() )
				return TRUE;
			else
				$this->path = $old_path;
		return FALSE;
	}
	/******************************************************************************
	 * Get the attributes of an image.  Typically used on upload.
	 *
	 * @param string filename
	 * @return bool can we get the attributes.
	 * ***************************************************************************/
	/*@bool@*/function get_image_attribs()
	{
		if ((list($this->image_width, $this->image_height, $this->image_type, $this->image_attr) = getimagesize( $this->filename ) ) !== false)
	                return true;
	        else
	                return false;
	}
	/*****************************************************************
	 * Generate the filename for the "nth" image for this item
	 *
	 * @param char count
	 * @return string filename
	 * **************************************************************/
	/*string*/ function gen_filename(/*char*/ $count )
	{
		if( 0 == $count )
			$filename = item_img_name($this->stock_id) . ".jpg";
		else
			$filename = item_img_name($this->stock_id) . $count . ".jpg";
		return $filename;
	}
	/**************************************************************
	 * Get the next available filename
	 *
	 * 
	 * @return string filename or ""
	 * ***********************************************************/
	/*string*/ function get_next_filename()
	{
		for ( $j = 0; $j <= $this->maxpics; $j++ )
		{
			$this->filename = $this->gen_filename( $j );
			if( $this->fileExists() === FALSE )
			{
				$this->images_count = $j;
				return $this->filename;
			}
		}
		return "";
	}
	/****************************************************************
	 * Check to see if there is a stock_id.jpg pic
	 *
	 * @return bool Is the pic missing?
	 * *************************************************************/
	/*@bool@*/function isMissing()
	{
		if( $this->get_next_filename( 1 ) == $this->gen_filename( 0 ) )
			return true; //gen_filename should return stock_id.jpg.  If that is the next filename there is no pic :(
		else
			return false;
	}
}

class fa_image_ui
{
	//public:
	var $page_security;
	var $help_context;
	var $maxpics;
	var $header_count_repeat;	//How many rows before repeating the header.  0 for no repeat

	function __construct()
	{
		$this->page_security = 'SA_ITEM';
		$this->help_context = "Inventory Photos";	//Is there any other place we use images?
		$this->maxpics = 10;
		$this->header_count_repeat = 0;	//How many rows before repeating the header.  0 for no repeat

	}
	function page()
	{
		page(_($help_context = "Inventory Photos"), true);
	}

}
