<?php

class ksf_exception extends Exception {
	protected $request;
	protected $response;
	//Are these declared in exception?
	//var $code;
	//var $message;

	function __construct( $message, $code = 0, $request, $response )
	{
		parent::__construct( $message, $code );
		$this->request  = $request;
		$this->response = $response;
	}
	public function get_request() {

		return $this->request;
	}
	public function get_response() {

		return $this->response;
	}

}

?>
