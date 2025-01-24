<?php

class email_file
{
	var $mailto;
	var $mailfrom;
	var $filename;
	var $tmp_dir;
	var $mailuser;
	var $mailpass;
	var $server;
	var $port;
	function __construct( $from, $to, $tmp_dir, $filename, $mailuser = "", $mailpass = "", $server = "", $port = "993" )
	{
		$this->mailto = $to;
		$this->mailfrom = $from;
		$this->tmp_dir = $tmp_dir;
		$this->filename = $filename;
		$this->mailuser = $mailuser;
		$this->mailpass = $mailpass;
		$this->server = $server;
		$this->port = $port;
	}
	function email_file( $subject )
	{
		if( isset( $this->mailto ) )
		{
			$data = file_get_contents( $this->tmp_dir . "/" . $this->filename );
			$uu_data = "begin 644 " . $this->filename . "\n" . convert_uuencode($data) . "end\n";
			$headers = 'From: ' . $this->mailfrom . "\r\n" .
				'Reply-To: ' . $this->mailfrom . "\r\n";
			//ini_set( 'smtp', 'p3plcpnl0185.prod.phx3.secureserver.net' );
			if( strlen( $this->server ) > 2 )
			{
				ini_set( 'smtp', $this->server );
				ini_set( 'smtp_port', $this->port );
				ini_set( 'sendmail_from', $this->mailfrom );
				ini_set( 'username', $this->mailuser );
				ini_set( 'password', $this->mailpass );
			}
			mail($this->mailto, $subject, $uu_data, $headers);
		}
		else
			throw new Exception( "mailto isn't set" );
	}

}


?>
