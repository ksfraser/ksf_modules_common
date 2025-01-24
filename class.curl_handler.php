<?php

error_reporting( E_ALL );
ini_set("display_errors", 1);
require_once( dirname(__FILE__) . '/class.base.php' );

/*********************************************************//**
 * Class to handle CURL activities for client classes
 *
 *
 * ************************************************************/
class curl_handler
{
	protected $curl_handle;	//!< Curl Handle
	protected $debug;	//!<@protected int
	protected $URL;		//!<@var string
	protected $method;	//!<@protected string Request Method i.e. POST or GET
	protected $params;	//!< @protected array request params e.g. ( 'status' => 'processing' )
	protected $headers;	//!< @protected array
	protected $body;	//!< @protected array request body data.  .  Only PUT/POST. prep sets this
	protected $data;	//!< client (write2woo_object) sets this
	protected $curlopts;	//!< array of options to be set in CURL
	protected $duration; //!< How long did the query take
	protected $response_headers;	//!< @var string the headers returned from the curl call
	protected $request_headers;	//!< @var string the headers to be sent on the request
	protected $curlinfoarray;	//!< Curl response info ARRAY
	protected $response_HTTP_code;
	protected $response_body;


	function __construct( $debug = 0, $URL = '', $method = "POST", $parameters = [], $headers = [], $data = [] )
	{
		$this->debug = $debug;
	        $this->URL        = $URL;
	        $this->method     = $method;
	        $this->params = $parameters;
	        $this->headers    = $headers;
		$this->data       = $data;
		$this->curl_handle = curl_init( $this->URL );
		//save response headers
		$this->curl_setopt( CURLOPT_HEADERFUNCTION, array( $this, 'curl_stream_headers' ) );
	}
	function __destruct()
	{
		
		if( null != $this->curl_handle )
		{
			curl_close ($this->curl_handle);
			$this->curl_handle = null;
		}
		 
	}
	function curl_setopt( $key, $value )
	{
		return curl_setopt( $this->curl_handle, $key, $value );
	}
	/**
	 * Save the cURL response headers for later processing
	 *
	 * @since 1.0
	 * @see WP_Http_Curl::stream_headers()
	 * @param object $_ the cURL resource handle (unused)
	 * @param string $headers the current response headers
	 * @return int the size of the processed headers
	 */
	/*@int@*/function save_response_headers( $_, $headers ) 
	{
		$this->response_headers .= $headers;
		return strlen( $this->response_headers );
	}
	function usecookies( $cookiefile )
	{
                curl_setopt($this->curl_handle, CURLOPT_COOKIEJAR, $cookiefile);  //initiates cookie file if needed
                curl_setopt($this->curl_handle, CURLOPT_COOKIEFILE, $cookiefile);  // Uses cookies from previous session if exist
	}
	/*@null@*/function curl_exec()
	{
		$this->response_HTTP_code = null;
		$this->response_headers = null;
		$this->response_body = null;
		// blank headers
		$this->curl_headers = '';
		$this->curl_setopt( CURLOPT_HTTPHEADER, $this->headers );
		$this->curl_setopt( CURLOPT_CUSTOMREQUEST, $this->method );

		switch ( $this->method ) {
			case 'GET':
				if( empty( $this->params ) )
					$this->params = (array) $this->data;
				$this->curl_setopt( CURLOPT_POST, FALSE ); //from write2woo
				$this->curl_setopt( CURLOPT_POSTFIELDS, http_build_query( $this->params ) );
				$paramstring = $this->param2string();
				if( strpos( $this->URL, "?" ) > 0 )
					$this->URL .= "&" . $paramstring;
				else
					$this->URL .= "?" . $paramstring;
			break;
			case 'PUT':
				$this->curl_setopt( CURLOPT_POSTFIELDS, $this->data );
				$this->curl_setopt(CURLOPT_PUT, 1); //from write2woo
			break;
			case 'POST':
				$this->curl_setopt( CURLOPT_POST, TRUE );
				$this->curl_setopt( CURLOPT_POSTFIELDS, $this->data );
			break;
			case 'DELETE':
				$this->body = null;
				$this->params = (array) $this->data;
				if( isset( $this->params['force'] ) )
					if( TRUE === $this->params['force'] )
						$this->params['force'] = 'true';	//Need the string, not the bool
				break;
			case 'OPTIONS':
				break;
			default:
				$this->params = null;
				break;
		}
		$this->curlopts2curl( $this->curlopts );
		$this->curl_setopt(CURLOPT_HEADER, FALSE);	//from write2woo

		$start_time = microtime( true );
		if( ! $ret = curl_exec($this->curl_handle) )
	      	{
			$this->response_body =  "Error returned by CURL: " . curl_error($this->curl_handle);
			$this->curlinfoarray =  curl_getinfo($this->curl_handle);
			$this->duration = round( microtime( true ) - $start_time, 5 );
	      	}
	      	else
		{
			$this->duration = round( microtime( true ) - $start_time, 5 );
			$this->curlinfoarray =  curl_getinfo($this->curl_handle);
			/*	A generic curl handler shouldn't be dealing with the data itself...
			$raw = $ret;
			$json_start = strpos( $raw, '{' );
			$json_end = strrpos( $raw, '}' ) + 1; // inclusive
			$cleanedresponse = substr( $raw, $json_start, ( $json_end - $json_start ) );
			$this->response_body = json_decode( $cleanedresponse, $this->json_decode_as_array );
			 */
			$this->response_body = $ret;
			$this->get_response_headers( $ret );
			$this->http_code = curl_getinfo( $this->curl_handle, CURLINFO_HTTP_CODE );
			$this->curlinfoarray = curl_getinfo( $this->curl_handle);
		}
		return;
	}
	/***************************************************//**
	 *Take an array of Curl Options and pass to Curl
	 *
	 * @param array array of options
	 * @return null
	 * ****************************************************/
	function curlopts2curl( $curlopts )
	{
		foreach( $curlopts as $key=>$value )
		{
			$res = $this->curl_setopt( $key, $value );
		}
		return null;
	}
	/*************************************************************//**
	 * Parse the raw response headers into an assoc array in format:
	 * {
	 *   'Header-Key' => header value
	 *   'Duplicate-Key' => array(
	 *     0 => value 1
	 *     1 = value 2
	 *   )
	 * }
	 *
	 * @since 1.0
	 * @see WP_HTTP::processHeaders
	 * @return array
	 */
	/*@array@*/function get_response_headers( $ret )
	{
		// get the raw headers
		$raw_headers = preg_replace('/\n[ \t]/', ' ', str_replace( "\r\n", "\n", $ret ) );
		// spit them
		$raw_headers = array_filter( explode( "\n", $raw_headers ), 'strlen' );
		$headers = array();
		// parse into assoc array
		foreach ( $raw_headers as $header ) {
			// skip response codes (appears as HTTP/1.1 200 OK or HTTP/1.1 100 Continue)
			if ( 'HTTP/' === substr( $header, 0, 5 ) ) {
				continue;
			}
			list( $key, $value ) = explode( ':', $header . ":", 2 );
			if ( isset( $headers[ $key ] ) ) {
				// ensure duplicate headers aren't overwritten
				$headers[ $key ] = array( $headers[ $key ] );
				$headers[ $key ][] = $value;
			} else {
				$headers[ $key ] = $value;
			}
		}
		$this->response_headers = $headers;
	}
}



/**20160925 END**/
	//All of the library we are cloning functions have the same pattern:
	//	Set method (set_request_args)
	//	Set path (can be array)
	//	set Body
	//	call do_request
	//		call make_api_call( method, path, data (body/param) )
}

?>
