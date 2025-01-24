<?php

error_reporting( E_ALL );
ini_set("display_errors", 1);
require_once( dirname(__FILE__) . '/class.rest_client.php' );

/**
 * 	20160925 KSF
 *	Added some code inspired by WOOCOMMERCE APIs to handle
 *	OAuth processing
 *	Adding extra info on response from server
 *	Cleaning JSON responses in case plugins add extra crap in body
 * **/

/**
 * Woo example:
 * $options = array( ... );
 * $client = new WC_API_Client( $url, $key, $secret, $options );
 * try {
 * 	$client->submodule->action();
 * 	}
 * catch (WC_API_Client_Exception $e) {
 * 	//$e->getMessage();
 * 	//$e->getCode();
 * 	if( $e instanceof WC_API_Client_HTTP_Exception )
 * 	{
 * 		$e->get_request();
 * 		$e->get_response();
 * 	}
 * }
 *
 */

class request extends base
{
	var $ch;	//!< Curl Handler
	var $debug;	//!<@var int
	var $URL;	//!<@var string
	var $method;	//!<@var string Request Method i.e. POST or GET
	var $params;	//!< @var array request params e.g. ( 'status' => 'processing' )
	var $headers;	//!< @var array
	var $body;	//!< @var array request body data.  .  Only PUT/POST. prep sets this
	var $data;	//!< client (write2woo_object) sets this
	var $json_data;	//!< client (write2woo_json) sets this 
	var $curlopts;	//!< array of options to be set in CURL
	var $duration; //!< How long did the query take
	protected $endpoint;	//!< @var string resource endpoint
	protected $request_path;	//!< @var string request path e.g. /order/123
	protected $client;	//!< @var object client calling us

	/**************************************************************************************************//**
	 *	Constructor
	 *
	 *	@param string URL
	 *	@param string method (POST/PUT/GET/DELETE/OPTION
	 *	@param array parameters array
	 *	@param array headers (CURL HEADERS) array
	 *	@param array data array NOT JSON encoded.
	 *	@param object client the class calling us
	 *	@return NULL
	 * ****************************************************************************************************/
	function __construct( $URL = '', $method = "POST", $parameters = [], $headers = [], /*NOT JSON*/$data = [], $client = null )
	{
		require_once( 'class.curl_handler.php' );
	        $this->URL        = $URL;
	        $this->method     = $method;
	        $this->params = $parameters;
	        $this->headers    = $headers;
		$this->data       = $data;
		$this->json_data = null;
		$this->client = $client;
		//wc-api-client also sets endpoint, namespace, client
		$this->prep();
		$this->ch = new curl_handler( $this->debug, $URL, $method, $parameters, $headers, null );
		return;
	}
	function encode()
	{
		display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__);
		$this->json_data = json_encode( $this->data );
	}
	/**
	 * Save the cURL response headers for later processing
	 *
	 * @since 2.0
	 * @see WP_Http_Curl::stream_headers()
	 * @param object $_ the cURL resource handle (unused)
	 * @param string $headers the current response headers
	 * @return int the size of the processed headers
	 */
	function curl_stream_headers( $_, $headers ) {
		return $this->ch->save_response_headers( $_, $headers );
	}
	function param2string()
	{
		$count = 0;
		$str = '';
		foreach ($this->params as $k=>$v)
		{
			if( $count > 0 )
			{
				$str .= "&" . $k . "=" . $v;
			}
			else
			{
				$str .= $k . "=" . $v;
				$count++;
			}
		}
		return $str;
	}
	function prep()
	{
		display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__);
		$this->body = null;
		switch ( $this->method ) {
			case 'GET':
			break;
			case 'PUT':
				$this->body = json_encode( $this->data );
			break;
			case 'POST':
				//do we need to http_build_query on ->data before setting body?
				if( isset( $this->json_data ) )
					$this->body = $this->json_data;
				else
					$this->body = json_encode( $this->data );
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
		display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__);
	}
	function request_curlopts()
	{
		display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__);
		foreach( $this->curlopts as $key=>$value )
		{
			$res = $this->ch->curl_setopt( $key, $value );
			if( FALSE == $res )
				display_error( "CURL error for " . $var1 . " with value " . $var2 );
		}
		display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__);
	}
	/*@class response@*/function curl_exec()
	{
		display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__);
		$this->ch->curl_exec();
		display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__);
		return $this->ch;
	}

}

class response extends base
{
	var $ch;	//!< Curl Handler passed in by REQUEST so we can get info
	var $curlinfoarray;	//!< Curl response info ARRAY
	var $code;
	var $headers;
	var $body;
	var $debug;
	var $http_code;	//!< HTTP Code returned by the server.  Anything not 2XX is an error
	var $fullresponse;	//!< Raw response
	var $cleanedresponse;	//!< Raw response trimmed of crap that PLUGINS may have added
	var $json_decode_as_array;
	function __construct( $curl_handle, $code = 0, $headers = [], $body = [])
	{
		display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__);
		$this->curl_handle = $curl_handle;
		$this->json_decode_as_array = FALSE;
		$this->code = $code;
		$this->headers = $headers;
		$this->body = $body;
		display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__);
	}
	/*@bool@*//*TRUE*/function clean_response()
	{
		display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__);
		//WP plugins modify JSON responses which break the JSON
		$raw = $this->ch->response_body;
		$json_start = strpos( $raw, '{' );
		$json_end = strrpos( $raw, '}' ) + 1; // inclusive

		$this->cleanedresponse = substr( $raw, $json_start, ( $json_end - $json_start ) );
		display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__);
		return TRUE;
	}
	function decode()
	{
		display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__);
		$this->http_code = $this->ch->http_code;
		$this->curlinfoarray = $this->ch->curlinfoarray;
		$this->clean_response();
		if( null == $this->cleanedresponse )
		{
		}
		else
		{
			$this->body = json_decode( $this->cleanedresponse, $this->json_decode_as_array );
			$this->get_response_headers();
		}
		display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__);
	}
	/**
	 */
	protected function get_response_headers() {
		display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__);
		$this->headers = $this->ch->response_headers;
		display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__);
	}
}

/**********************************************************************************************
 *A homegrown REST client using curl to talk to other side.
 *	Assumes OAUTH for authentication
 *	Assumes JSON for data transport
 *
 *	Equivalent to HttpClient from WooCommerce api
 *
 *	Do we need to extend base?  
 *
 *	Each WC resource (ie product) has a series of functions that set the method,
 *	path and params.  ex GET /products ... /products/#{id}, /products/sku/{sku}
 *	They then call do_request.  PUT operations set body instead of params with 
 *	an array which I assume will be JSON encoded.  namespace is to make 1 level less
 *	of nesting for client code for JSON (set_request)args).  calling do_request calls
 *	make_api_call which as params calls get_enpoint_path and get_request_data.  get_request_data
 *	checks the method and then returns either the param or the body var.
 *
 * *******************************************************************************************/

class wp_rest_client extends rest_client
{

	/** @var string request method, e.g. GET */
	protected $request_method;

	/** @var string request path, e.g. orders/123 */
	protected $request_path;

	/** @var array request params, e.g. { 'status' => 'processing' } */
	protected $request_params;

	/** @var array request body data, only used for PUT/POST requests */
	protected $request_body;

	//var $curl_handle;	//!< Inherited Curl Handle.
	var $curl_headers;	//!< Inherited 
	var $referer_URL;
	//var $URL;		//!< Inherited Store API URL
	var $fields = array();	//fields that can be sent to the receiving app
	//var $data = array();	//Inherited 
	//var $responseInfo;	//Inherited 
	//var $responseHeaders;	//!< @var String Inherited to hold response Headers
	var $request;		//!< Class to hold request data
	var $response;  	//!< Class to hold response data
	//var $consumer_key;	//!< Inherited oAuth Consumer Key
	//var $consumer_secret; 	//!< Inherited oAuth Consumer secret
	//var $options;		//!< Inherited Options for setting things up.
	var $APIversion;	//!< API version is part of the rest path
	var $WPAPI;		//!< WPAPI is use WP built in REST - affects rest path
	//var $curlopts;	//!< array Inherited of options to be sent to CURL
	//var $params;		// Inherited 
	//var $oauth_client;	//!< class Inherited oauth_client object
	//var $loglevel;		//!< int Inherited PEAR Log Level
	var $namespace;		//!< WO_API_Client_resource_...
	var $endpoint;		//!< WO_API_Client_resource_...
	var $client;		//!< WO_API_Client_resource_...
	
	//HASH_ALGORITHM in base

	/********************************************************************************//**
	 *
	 * for oauth we should have the following passed in in the args:
	 * 	consumer_key
	 * 	consumer_secret
	 * 	URL
	 * for REST connection need the following in the args:
	 * 	method
	 * 	data
	 * 	params (get)
	 * For both pass in loglevel
	 *
	 * @returns bool can we proceed
	 * ********************************************************************************/
	function __construct( /*array*/ $args = null )
	{
		$res = parent::__construct( $args );
		if( ! $res )
		{
			//pre-req check failed.  Can't proceed
			return $res;
		}
		else
		{
			return $res;
		}
	}
	function createRequest()
	{
		$request = new request( $this->URL, $this->method, $this->params, $this->headers, $this->data );
		$request->debug = $this->debug;
		$request->curlopts = $this->curlopts;
		//echo "<br /><br />" . __METHOD__ . ":" . __LINE__ . " set request curlopts REST_CLIENT<br />";
		//var_dump( $this->curlopts );
		//echo "<br /><br />" . __METHOD__ . ":" . __LINE__ . " set request curlopts REQUEST<br />";
		//var_dump( $request->curlopts );
		return $request;
	}
	/**
	 * Build the result object/array
	 *
	 * @since 2.0.0
	 * @param object|array JSON decoded result
	 * @return object|array in format:
	 * {
	 *  <result data>
	 *  'http' =>
	 *   'request' => stdClass(
	 *     'url' => request URL
	 *     'method' => request method
	 *     'body' => JSON encoded request body entity
	 *     'headers' => array of request headers
	 *     'duration' => request duration, in seconds
	 *     'params' => optional raw params
	 *     'data' => optional raw request data
	 *     'duration' =>
	 *    )
	 *   'response' => stdClass(
	 *     'body' => raw response body
	 *     'code' => HTTP response code
	 *     'headers' => HTTP response headers in assoc array
	 *   )
	 * }
	 */
	function build_result( $parsed_response ) {

		// add cURL log, HTTP request/response object
		if ( $this->debug ) {

			if ( $this->json_decode_as_array ) {

				$parsed_response['http'] = array(
					'request'  => json_decode( json_encode( $this->request ), true ),
					'response' => json_decode( json_encode( $this->response ), true ),
				);

			} else {

				$parsed_response->http = new stdClass();
				$parsed_response->debug = $this->debug;
				$parsed_response->http->request = $this->request;
				$parsed_response->http->response = $this->response;
			}
		}

		return $parsed_response;
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
