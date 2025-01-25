<?php

class email_file
{
	var $mailto;
	var $mailfrom;
	var $filename;
	var $tmp_dir;
	function __construct( $from, $to, $tmp_dir, $filename )
	{
		$this->mailto = $to;
		$this->mailfrom = $from;
		$this->tmp_dir = $tmp_dir;
		$this->filename = $filename;
	}
	function email_file( $subject )
	{
		if( isset( $this->mailto ) )
		{
			$data = file_get_contents( $this->tmp_dir . "/" . $this->filename );
			$uu_data = "begin 644 " . $this->filename . "\n" . convert_uuencode($data) . "end\n";
			$headers = 'From: ' . $this->mailfrom . "\r\n" .
			    'Reply-To: ' . $this->mailfrom . "\r\n";
			mail($this->mailto, $subject, $uu_data, $headers);
		}
		else
			throw new Exception( "mailto isn't set" );
	}

}


?>
