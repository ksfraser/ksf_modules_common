<?php

require_once( 'class.fa_origin.php' );

/*
if( ! defined( 'company_path' ) )
{
	function company_path()
	{
		return "";
	}
}
*/

class ksf_file extends fa_origin
{
	protected $fp;	//!< @var handle File Pointer
	protected $filename;	//!< @var string name of output file
	protected $tmp_dir;	//!< @var string temporary directory name
	protected $path;	//!<DIR where are the images stored.  default company/X/images...
	function __construct( $filename = "file.txt" )
	{
		parent::__construct();
		$this->filename = $filename;
		$this->path = company_path() . '/images';
	}
	function __destruct()
	{
		if( isset( $this->fp ) )
			$this->close();
	}
	function open()
	{
		$this->validateVariables();
		if( strlen( $this->path ) > 1 )
			$this->fp = fopen( $this->path . '/' . $this->filename, 'r' );
		else
			$this->fp = fopen( $this->filename, 'r' );
		if( !isset( $this->fp ) )
			throw new Exception( "Unable to set Fileponter when trying to open ". $this->filename );	
	}
	function open_for_write()
	{
		$this->validateVariables();
		$this->fp = fopen( $this->path . '/' . $this->filename, 'w' );
		if( !isset( $this->fp ) )
			throw new Exception( "Unable to set Fileponter when trying to open ". $this->filename );	
	}
	function close()
	{
		if( !isset( $this->fp ) )
			throw new Exception( "Trying to close a Fileponter that isn't set" );
		fflush( $this->fp );
		fclose( $this->fp );
		$this->fp = null;
	}
	/*@bool@*/function make_path()
	{
		$this->validateVariables();
		if( !$this->pathExists() )
			mkdir( $this->path );
		//Did we succeed?
		return $this->pathExists();
	}
	/*@bool@*/function pathExists()
	{
		$this->validateVariables();	
		return is_dir( $this->path );
	}
	/***************************************************************
	 * Check for the existance of a file
	 *
	 * 
	 * @return bool
	 * *************************************************************/
	/*@bool@*/function fileExists()
	{
		$this->validateVariables();
		return file_exists( $this->path . '/' . $this->filename );
	}
	function validateVariables()
	{
		if( !isset( $this->path ) )
			throw new Exception( "Path variable not set" );
		if( !isset( $this->filename )  )									throw new Exception( "filename variable not set" );
	}

}

require_once( 'class.ksf_ui.php' );

/*******************************************************//**
 *
 *
 * Inherits the path of company/images for destination directory
 *
 * **********************************************************/
class ksf_file_upload extends ksf_file
{
	protected $upload_ok;
	protected $files_array;		//!< array List of filenames of files we uploaded
	protected $filepaths_array;	//!< array List of full path filenames of files we uploaded
	protected $ui_class;		//!< class that has the UI screens required for this class to work
	protected $upload_button_name;
	protected $upload_button_label;
	protected $upload_file_field_name;
	protected $b_upload_single_file;
	protected $a_data;		//!< array data returned from file type handler
	function __construct( $filename, $ui_c = null, $upload_file_field_name = "import_files", $b_upload_single_file = true )
	{
		parent::__construct( $filename );
		$this->upload_ok = FALSE;
		$this->files_array = array();
		if( null == $ui_c )
			$this->ui_class = new ksf_ui_class();
		else
			$this->ui_class = $ui_c;
		$this->upload_file_field_name = $upload_file_field_name;
		$this->b_upload_single_file = $b_upload_single_file;
		$this->a_data = array();
	}
	function open()
	{
		$this->validateVariables();
		$this->fp = fopen( $this->path . '/' . $this->filename, 'w' );
		if( !isset( $this->fp ) )
			throw new Exception( "Unable to set Fileponter when trying to open ". $this->filename );	
	}
	function process_files()
	{
		//var_dump( $_FILES );
		/* If $this->b_upload_single_file true, following subarrays don't have 0/1/...
		 * array(1) 
		 * { 
		 * 	["import_files"]=> array(5) 
		 * 	{ 
		 * 		["name"]=> array(2) 
		 * 		{ 
		 * 			[0]=> string(37) "Gator Price list April 2017 Item2.csv" 
		 * 			[1]=> string(32) "Gator Price list April 2017.xlsx" 
		 * 		} 
		 * 		["type"]=> array(2) 
		 * 		{ 
		 * 			[0]=> string(24) "application/octet-stream" 
		 * 			[1]=> string(24) "application/octet-stream" 
		 * 		} 
		 * 		["tmp_name"]=> array(2) 
		 * 		{ 
		 * 			[0]=> string(46) "C:\Bitnami\redmine-3.0.3-0\php\tmp\phpEC9B.tmp" 
		 * 			[1]=> string(46) "C:\Bitnami\redmine-3.0.3-0\php\tmp\phpECAC.tmp" 
		 * 		} 
		 * 		["error"]=> array(2) 
		 * 		{ 
		 * 			[0]=> int(0) 
		 * 			[1]=> int(0) 
		 * 		} 
		 * 		["size"]=> array(2) 
		 * 		{ 
		 * 			[0]=> int(114550) 
		 * 			[1]=> int(32990) 
		 * 		} 
		 * 	} 
		 * }
		 *  */
		if( isset( $_POST['file_type'] ) )
			$type = $_POST['file_type'];
		else
			$type = null;
		if( isset( $_POST[ 'seperator' ] ) )
		 	$seperator = $_POST[ 'seperator' ];
		else
			$seperator = ',';
		if( $this->b_upload_single_file )
		{
			if ( isset( $_FILES[ $this->upload_file_field_name ] ) && $_FILES[ $this->upload_file_field_name ]['name'] != '')
			{
				$filename = $_FILES[ $this->upload_file_field_name ]['tmp_name'];
				$size = $_FILES[ $this->upload_file_field_name ]['size'];
				$error = $_FILES[ $this->upload_file_field_name ]['error'];
				if( !$error )
					$this->a_data[] = $this->process_single_file( $filename, $size, $seperator, $type );
			}
		}
		else
		{
			$filecount = count( $_FILES[ $this->upload_file_field_name ]['tmp_name'] ); //How many files
			for( $count = 0; $count < $filecount; $count++ )
			{
				$filename = $_FILES[ $this->upload_file_field_name ][$count]['tmp_name'];
				$size = $_FILES[ $this->upload_file_field_name ][$count]['size'];
				$error = $_FILES[ $this->upload_file_field_name ][$count]['error'];
				if( !$error )
					$this->a_data[] = $this->process_single_file( $filename, $size, $seperator, $type );
			}
		
		}
		var_dump( $this->a_data );
	}
	function process_single_file( $filename, $size, $separator=',', $type = 'csv' )
	{
		if( $type == 'csv' )
		{
			$fc =  new ksf_file_csv( $filename, $size, $separator );
			$fc->set( 'path', "", false );
			$fc->readcsv_entire();	//sets lines and linecount
			$this->data = array( 'count' => $fc->get( 'linecount' ), 'header' => $fc->get( 'headerline') , 'data' => $fc->get( 'lines' ) );
		}
		return $this->data;
	}
	function upload_form($b_multi=false, $action="", $name="") 
	{

		if( null == $this->ui_class )
			throw new Exception( "UI Class not set" );
		$this->ui_class->div_start('doc_tbl');
		$this->ui_class->form_start( $b_multi, false, $action, $name );
		$this->ui_class->instructions_table();
		$this->ui_class->table_start(TABLESTYLE);
		$this->ui_class->table_header( array(_("Select File(s)"), '') );
		if( $this->b_upload_single_file )
			$multi = "' />";
		else
			$multi = "[]' multiple />";
		label_row(_("Files"), "<input type='file' name='" . $this->upload_file_field_name . $multi);
		start_row();
		label_cell('Upload', "class='label'");
		if( !isset( $this->upload_button_name ) )
			throw new Exception( "Button Name not set", KSF_FIELD_NOT_SET );
		if( !isset( $this->upload_button_label ) )
			throw new Exception( "Button Name not set", KSF_FIELD_NOT_SET );
		submit_cells( $this->upload_button_name, _($this->upload_button_label) );
		end_row();
		$this->ui_class->table_end(1);
		$this->ui_class->form_end();
		div_end();
	}
	function file_put_contents( $content )
	{
		file_put_contents( $this->path . "/" . $this->filename, $content );
		$this->filepaths_array[] = $this->path . "/" . $this->filename;
		$this->files_array[] = $this->filename;
	}
	function copy_file()
	{
		if( isset( $this->fp ) )
			$this->close();
		foreach( $_FILES['files']['name'] as $id=>$fname) 
		{
    			echo "Processing file `$fname`\n";
			$content = file_get_contents($_FILES['files']['tmp_name'][$id]);
			$this->set( "filename", $fname );
			$this->file_put_contents( $content );
		}

	}

}

class ksf_file_csv extends ksf_file
{
	protected $size;
	protected $separator;
	protected $lines = array();	//!<array of arrays once run
	protected $linecount;
	protected $b_header;
	protected $b_skip_header;
	private $grabbed_header;
	protected $headerline;
	protected $b_skip_empty_lines;	//!<bool
	protected $b_trim_fields;	//!<bool
	function __construct( $filename, $size, $separator, $b_header = false, $b_skip_header = false )
	{
		parent::__construct( $filename );
		$this->size = $size;
		$this->separator = $separator;
		$this->linecount = 0;
		$this->b_header = $b_header;
		$this->b_skip_header = $b_skip_header;
		$this->grabbed_header = false;
	}
	/*@array@*/function readcsv_line()
	{
		if( !isset( $this->fp )  )
			throw new Exception( __CLASS__ . " required field not set: fp" );
		if( ! isset( $this->size )  )
			throw new Exception( __CLASS__ . " required field not set: size" );
		if( ! isset( $this->separator ) )
			throw new Exception( __CLASS__ . " required field not set: separator" );
			if( $this->b_header AND !$this->grabbed_header )
			{
				$this->headerline = fgetcsv( $this->fp, $this->size, $this->separator );
				$this->grabbed_header = true;
			}
			if( ! $this->b_header )
				$this->headerline = '';
			else
			{
			}
			return fgetcsv( $this->fp, $this->size, $this->separator );
	}
	function readcsv_entire()
	{
		if( ! isset( $this->fp ) )
			try {
				$this->open();
			} catch( Exception $e )
			{
				display_notification( $e->getMessage() );
				$this->lines = array();
				return;
			}
		while( $line = $this->readcsv_line() )
		{
			$this->lines[] = $line;
			$this->linecount++;
		}
	}
	/**//******************************************************************************************
	* Read a CSV file, accounting for encapsulated line returns.
	*
	* fgetcsv and str_getcsv do not handle properly "\n" in an encapsulated field.
	*	This routine handles this.
	*
	* @since 20240902
	*
	* Originally used in square_import.  Then copied here.
	*
	* @param string the CSV data
	* @param char delimieter default comma
	* @param bool should we skip empty lines, default true
	* @param bool should we trim fields, default true
	* @returns array	Also sets ->lines and ->linecount
	************************************************************************************************/
	function parse_csv ($csv_string, $delimiter = ",", $skip_empty_lines = true, $trim_fields = true)
	{
		$enc = preg_replace('/(?<!")""/', '!!Q!!', $csv_string);
		$enc = preg_replace_callback(
			'/"(.*?)"/s',
			function ($field) {
				return urlencode(utf8_encode($field[1]));
			},
			$enc
		);
		$lines = preg_split($skip_empty_lines ? ($trim_fields ? '/( *\R)+/s' : '/\R+/s') : '/\R/s', $enc);
		$this->lines = array_map(
			function ($line) use ($delimiter, $trim_fields) {
				$fields = $trim_fields ? array_map('trim', explode($delimiter, $line)) : explode($delimiter, $line);
				return array_map(
					function ($field) {
						return str_replace('!!Q!!', '"', utf8_decode(urldecode($field)));
					},
					$fields
				);
			},
			$lines
		);
		$this->linecount = count( $this->lines );
		return $this->lines;
	}
}

?>
