<?php

error_reporting( E_ALL );
ini_set("display_errors", 1);
//require_once( dirname(__FILE__) . '/class.base.php' );

/**********************************************************************************************
 *A homegrown REST client using curl to talk to other side.
 *	Assumes OAUTH for authentication
 *	Assumes JSON for data transport
 *
 *	Equivalent to HttpClient from WooCommerce api
 *
 *	Do we need to extend base?  
 *
 * 	has FrontAccounting specific code 
 * 		display_error
 * 		display_notification
 *
 * *******************************************************************************************/

//class rest_client extends base
class rest_client 
{
	var $curl_handle;	//!< Curl Handle
	var $curl_headers;
	//var $referer_URL;
	var $URL;		//!< Store API URL
	//var $fields = array();	//fields that can be sent to the receiving app
	var $data = array();
	var $responseInfo;
	var $responseHeaders;	//!< @var String to hold response Headers
	//var $request;		//!< Class to hold request data
	//var $response;  	//!< Class to hold response data
	var $consumer_key;	//!< oAuth Consumer Key
	var $consumer_secret; 	//!< oAuth Consumer secret
	var $options;		//!< Options for setting things up.
	//var $APIversion;	//!< API version is part of the rest path
	//var $WPAPI;		//!< WPAPI is use WP built in REST - affects rest path
	var $curlopts;		//!< array of options to be sent to CURL
	var $params;
	var $oauth_client;	//!< class oauth_client object
	var $loglevel;		//!< int PEAR Log Level
	
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
	 *
	 * @input array of params
	 * @returns bool did we have all pre-req params?
	 * ********************************************************************************/
	function __construct( /*array*/ $args = null )
	{

		$this->curl_handle = curl_init();
		$this->params = array();
		//Set defaults that can be overridden in the args array
                $this->curlopts[CURLOPT_AUTOREFERER] =TRUE;
        //      $this->curlopts[CURLOPT_FOLLOWLOCATION] =TRUE;
                $this->curlopts[CURLOPT_HEADER] =TRUE;
                $this->curlopts[CURLOPT_RETURNTRANSFER] =true;	//sets response in a $result variable rather than echo
       	//      $this->curlopts[CURLOPT_CONNECTTIMEOUT] =100;
		//	$this->curlopts[CURLOPT_TIMEOUT] =100;
		//	$this->curlopts[CURLOPT_SSL_VERIFYPEER] =TRUE;
		//	$this->curlopts[CURLOPT_SSL_VERIFYHOST] =TRUE;
	/*
			CURLOPT_VERBOSE, $level);
	     		CURLOPT_URL, $url );
			CURLOPT_REFERER, $url );
			CURLOPT_HTTPHEADER, http_build_query($data) );
	 */

		//set request header
		//$this->curlopts[CURLOPT_HTTPHEADER] =$this->request->headers;
		//echo "<br /><br />" . __METHOD__ . ":" . __LINE__ . " set request curlopts REST_CLIENT<br />";
		//var_dump( $this->curlopts );
		$this->parse_args( $args );
		$this->createHeader();
		if( isset( $this->consumer_key ) )
			$this->oauth_client = new oauth_client($this->loglevel, $this->consumer_key, $this->consumer_secret);
	}
	function createHeader()
	{
		if( empty( $this->headers ) )
		{
			$this->curl_headers = array(
				'Accept: application/json',
				'Content-Type: application/json',
				'User-Agent: KSF API Client-PHP',
			);
			//can set 'Content-Length: ' . strlen($data_string)) in headers
			//but it is auto-set according to user's documentation
		}
		return null;
	}
	/********************************************************************//**
	 * Takes a URL and array of parameters and builds the final URL
	 *
	 * @param string URL
	 * @param array Parameters (key=>val)
	 * @returns string final URL
	 * *********************************************************************/
	function build_URL_Query( $url, $parameters = [])
	{
		if( !empty( $parameters ) )
			$url .= "?" . http_build_query( $parameters );
		return $url;
	}
	/**********************************************************************
	 *
	 * Options is an array of options so needs to be handled recursively
	 * CURLOPT values need to be passed to curl_setopt later
	 *
	 * *******************************************************************/
	function parse_args( /*array*/$args )
	{
		foreach( $args as $key=>$value )
		{
			if( $key == "options" )
			{
				$this->parse_args( $value );
			}
			else
			{
				if( strncmp( $key, "CURLOPT", 7 ) == 0 )
				{
					foreach( $value as $k=>$v )
						$this->curlopts[$k] = $v; 
				}
				else
					$this->$key = $value;
			}
		}
	}
	/**************************************************************************//**
	 * Take an option/value pair and set CURL options
	 *
	 * @param curl_handle depreciated
	 * @param string key
	 * @param string value
	 * @returns bool return value from CURL's setopt
	 * ***************************************************************************/
	function curl_setopt( $handle, $key, $value )
	{
		$res = $this->curl_handle->curl_setopt($key, $value);
		if( FALSE == $res )
		{
			display_error( "CURL error for " . $key . " with value " . $value ); //FrontAccounting specific
		}
		return $res;
	}
	function __destruct()
	{
		$this->curl_handle->__destruct();	 
	}
	function usecookies( $cookiefile )
	{
      		$this->curl_handle->usecookies( $cookiefile ); 
	}
	/*************************************************************************************************//**
	 *	Perform the steps to send the request and receive the response
	 *
	 *	@return bool Success or Fail
	 *
	 * ***************************************************************************************************/
	/*@bool@*/function curl_exec()
	{
		if( $this->loglevel == 1 )
		{
			display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__);
		}
		//$this->request_curlopts();
		//$this->request->prep();
		$this->oauth_client->curl_setAuth();
		$this->request->URL = $this->URL;
		$this->request->curl_setopt( CURLOPT_URL, $this->request->URL );

		$this->response = $this->request->curl_exec();
		display_notification( date('H:i:s', time()) . ":" . __METHOD__  . ":" . __LINE__);
		return TRUE;
	}
	function array2curlarray( $in_arr )
	{
		$out_array = array();
		foreach( $in_arr as $key => $val )
		{
			$out_array[] = $key . ": " . $val;
		}
		return $out_array;
	}
	
/**20160925 START**/
	/**
	 * JSON decode the response body after stripping any invalid leading or
	 * trailing characters.
	 *
	 * Plugins (looking at you WP Super Cache) or themes
	 * can add output to the returned JSON which breaks decoding.
	 *
	 * @since 2.0
	 * @param string $raw_body raw response body
	 * @return object|array JSON decoded response body
	 */
	function get_parsed_response( $raw_body ) {

		$json_start = strpos( $raw_body, '{' );
		$json_end = strrpos( $raw_body, '}' ) + 1; // inclusive

		$json = substr( $raw_body, $json_start, ( $json_end - $json_start ) );

		return json_decode( $json, $this->json_decode_as_array );
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
		if ( $this->loglevel ) {

			if ( $this->json_decode_as_array ) {

				$parsed_response['http'] = array(
					'request'  => json_decode( json_encode( $this->request ), true ),
					'response' => json_decode( json_encode( $this->response ), true ),
				);

			} else {

				$parsed_response->http = new stdClass();
				$parsed_response->loglevel = $this->loglevel;
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
