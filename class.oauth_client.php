<?php

//!< WARNING this class has some FrontAccounting specific code

$path_to_root="../..";
require_once( 'defines.inc.php' );
include_once( 'Log.php' );	//PEAR Logging

/*
	# 0 PEAR_LOG_EMERG emerg() System is unusable
	# 1 PEAR_LOG_ALERT alert() Immediate action required
	# 2 PEAR_LOG_CRIT crit() Critical conditions
	# 3 PEAR_LOG_ERR err() Error conditions
	# 4 PEAR_LOG_WARNING warning() Warning conditions
	# 5 PEAR_LOG_NOTICE notice() Normal but significant
	# 6 PEAR_LOG_INFO info() Informational
	# 7 PEAR_LOG_DEBUG debug() Debug-level messages 
*/

class oauth_client
{
	protected $loglevel;
	protected $error;
	protected $log;
	protected $consumer_secret;
	protected $consumer_key;
	protected $URL;
	public $curl_opts;
	/************************************************************************//**
	 *constructor
	 *
	 *@param $loglevel int PEAR log levels
	 *
	 * ***************************************************************************/
	function __construct( $loglevel = PEAR_LOG_DEBUG, $consumer_key, $consumer_secret )
	{
		$this->loglevel = $loglevel;
		$this->error = array();
		$this->log = array();
		$this->consumer_secret = $consumer_secret;
		$this->consumer_key = $consumer_key;
		$this->curl_opts = array();
	}
	/*********************************************************************************
	 *
	 *	parameters for oAuth 1.0a
	 *	REQUIRES consumer_key, consumer_key
	 *
	 * ********************************************************************************/
	function get_oauth_params( $params, $method )
	{
		$params = array_merge( $params, array(
			'oauth_consumer_key'     => $this->consumer_key,
			'oauth_timestamp'        => time(),
			'oauth_nonce'            => sha1( microtime() ),
			'oauth_signature_method' => 'HMAC-' . self::HASH_ALGORITHM,
		) );
		// the params above must be included in the signature generation
		$params['oauth_signature'] = $this->generate_oauth_signature( $params, $method );
		return $params;
	}
	/*********************************************************************************
	 *
	 *	Generate the oAuth signature
	 *	REQUIRES consumer_secret
	 *
	 * ********************************************************************************/
	function generate_oauth_signature( $params, $http_method )
	{
		$base_request_uri = rawurlencode( $this->URL );
		
		if ( isset( $params['filter'] ) ) {
			$filters = $params['filter'];
			unset( $params['filter'] );
			foreach ( $filters as $filter => $filter_value ) {
				$params['filter[' . $filter . ']'] = $filter_value;
			}
		}
		
		// normalize parameter key/values and sort them
		$params = $this->normalize_parameters( $params );
		uksort( $params, 'strcmp' );

		// form query string
		$query_params = array();
		foreach ( $params as $param_key => $param_value ) {
			$query_params[] = $param_key . '%3D' . $param_value; // join with equals sign
		}

		$query_string = implode( '%26', $query_params ); // join with ampersand

		// form string to sign (first key)
		$string_to_sign = $http_method . '&' . $base_request_uri . '&' . $query_string;

		return base64_encode( hash_hmac( self::HASH_ALGORITHM, $string_to_sign, $this->consumer_key, true ) );
	}
	/*****************************************************************************************
	 *
	 *	Are we connecting to the server via ssl
	 *	REQUIRE URL
	 *
	 * ***************************************************************************************/
	public function is_ssl() {
		return substr( $this->URL, 0, 5 ) === 'https';
	}
	/**20160925 END**/
	/**********************************************************************************************
	 *
	 *	In the code we copied from, the AUTH method was called in here as a new class,
	 *	checked for SSL, and returned the params (get_oauth_params below...).  However
	 *	it was written in such a way that ONLY oAuth was used so we refactored.
	 *
	 *	This function could be written so that a caller specified auth method was used.
	 *
	 *	REQUIRES consumer_key&consumer_secret OR username&password
	 *	REQUIRES URL
	 *	SETS curl_opts
	 *
	 * *******************************************************************************************/
	function curl_setAuth()
	{
		//To make the client able to use future authentication methods, we should set
		//it up to call depending on that method.  Either a class passed in, or at least
		//an indicator...
		//
		//	$authclass = "authenticator_class_" . $auth_type;
		//	$auth = new $authclass( $username, $password, $options, $params)
		//and let the auth class do what it must.

		//Even though the class is designed on the assumption we are using oAuth
		//in case we aren't, or something went wrong in the caller.
		if( isset( $this->consumer_key ) )
		{
			$this->username = $this->consumer_key;
			$this->password = $this->consumer_secret;
		}
		if( isset( $this->username ) && isset( $this->password ) )
		{
			if( $this->is_ssl() )
			{
				$this->params = array_merge( $this->params, array(
					'consumer_key'    => $this->username,
					'consumer_secret' => $this->password,
				) );

			} else {
				$this->params = array_merge( $this->params, 
						$this->get_oauth_params( 
							$this->request->params, 
							$this->request->method ) );
			
			}
			 $query_params = http_build_query( $this->params );

			//curl_setopt($this->curl_handle, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
			$this->curl_opts["CURLOPT_HTTPAUTH"] = CURLAUTH_BASIC;
			$this->curl_opts["CURLOPT_USERPWD"] = $this->username . ":" . $this->password;
		}
		$this->curl_opts["URL"] = $this->URL .  '?' . $query_params;
		return;
	}
	/***********************************************************************************************
	 * Normalize each parameter by assuming each parameter may have already been
	 * encoded, so attempt to decode, and then re-encode according to RFC 3986
	 *
	 * Note both the key and value is normalized so a filter param like:
	 *
	 * 'filter[period]' => 'week'
	 *
	 * is encoded to:
	 *
	 * 'filter%5Bperiod%5D' => 'week'
	 *
	 * This conforms to the OAuth 1.0a spec which indicates the entire query string
	 * should be URL encoded
	 * @param array $parameters un-normalized pararmeters
	 * @return array normalized parameters
	 */
	private function normalize_parameters( $parameters ) {

		$normalized_parameters = array();

		foreach ( $parameters as $key => $value ) {

			// percent symbols (%) must be double-encoded
			$key   = str_replace( '%', '%25', rawurlencode( rawurldecode( $key ) ) );
			$value = str_replace( '%', '%25', rawurlencode( rawurldecode( $value ) ) );

			$normalized_parameters[ $key ] = $value;
		}

		return $normalized_parameters;
	}

}
?>
