<?php

require_once( 'class.fa_origin.php' );

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
		$this->fp = fopen( $this->path . '/' . $this->filename, 'r' );
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
	function __construct( $filename )
	{
		parent::__construct( $filename );
		$this->upload_ok = FALSE;
		$this->files_array = array();
	}
	function open()
	{
		$this->validateVariables();
		$this->fp = fopen( $this->path . '/' . $this->filename, 'w' );
		if( !isset( $this->fp ) )
			throw new Exception( "Unable to set Fileponter when trying to open ". $this->filename );	
	}
	function upload_form() 
	{
		div_start('doc_tbl');
		start_table(TABLESTYLE);
		$th = array(_("Select File(s)"), '');
		table_header($th);
		label_row(_("Files"), "<input type='file' name='files[]' multiple />");
		start_row();
		label_cell('Upload', "class='label'");
		submit_cells('upload', _("Upload"));
		end_row();
		end_table(1);
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
	function __construct( $filename, $size, $separator )
	{
		parent::__construct( $filename );
		$this->size = $size;
		$this->separator = $separator;
		$this->linecount = 0;
	}
	/*@array@*/function readcsv_line()
	{
		if( isset( $this->fp ) AND isset( $this->size ) AND isset( $this->separator ) )
			return fgetcsv( $this->fp, $this->size, $this->separator );
		else
			throw new Exception( __CLASS__ . " required field not set" );
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
}

?>
