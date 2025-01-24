<?php

class write_file
{
	protected $fp;	//!< @var handle File Pointer
	protected $filename;	//!< @var string name of output file
	protected $tmp_dir;	//!< @var string temporary directory name
	protected $deliminater;	//!< fputcsv control value 
	protected $enclosure; 	//!< fputcsv control value
	protected $escape_char;	//!< fputcsv control value
	function __construct($tmp_dir = "../../tmp/", $filename = "file.txt" )
	{
		$this->tmp_dir = $tmp_dir;
		$this->filename = $filename;
		$this->fp = fopen( $this->tmp_dir . "/" . $this->filename, 'w' );
		if( !isset( $this->fp ) )
			throw new Exception( "Unable to set Fileponter when trying to open " . $this->tmp_dir . "/" . $this->filename . " for writing." );
		$this->deliminater = ",";	//!< fputcsv control value 
		$this->enclosure = '"';		//!< fputcsv control value 
		$this->escape_char = "\\";	//!< fputcsv control value 
	}
	function __destruct()
	{
		if( isset( $this->fp ) )
			$this->close();
	}
	function write_chunk( $line )
	{
		if( !isset( $this->fp ) )
			throw new Exception( "Fileponter not set" );
		fwrite( $this->fp, $line );
		fflush( $this->fp );
	}
	function write_line( $line )
	{
		if( !isset( $this->fp ) )
			throw new Exception( "Fileponter not set" );
		fwrite( $this->fp, $line . "\r\n" );
		fflush( $this->fp );
	}
	function write_array_to_csv( $arr )
	{
		if( !isset( $this->fp ) )
			throw new Exception( "Fileponter not set" );
		fputcsv( $this->fp, $arr, $this->deliminater, $this->enclosure );
		//fputcsv( $this->fp, $arr, $this->deliminater, $this->enclosure, $this->escape_char );
	}
	function close()
	{
		if( !isset( $this->fp ) )
			throw new Exception( "Trying to close a Fileponter that isn't set" );
		fflush( $this->fp );
		fclose( $this->fp );
		$this->fp = null;

	}
}


?>
