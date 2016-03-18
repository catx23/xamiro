<?php

/**
 * Paths and sh*
 */
$FULL_ZIP                   = 'https://github.com/gbaumgart/xamiro/archive/master.zip';
//$FULL_ZIP                   = 'http://192.168.1.37/projects/xbox-app/DIST/xbox.zip';
$ROOT_DIR                   = realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR);
$DESTINATION_DIRECTORY_NAME = 'xamiro';
$DESTINATION_DIRECTORY      = $ROOT_DIR . DIRECTORY_SEPARATOR . $DESTINATION_DIRECTORY_NAME . DIRECTORY_SEPARATOR;
$AUTO_OPEN                  = true;

define('ROOT_PATH', $ROOT_DIR);

$OPEN_URL = str_replace( getThisScriptFileName(), $DESTINATION_DIRECTORY_NAME . '/index.php' ,getUrl());

/**
 * All private now!
 */
if ( ! defined('FS_CHMOD_DIR') )
	define('FS_CHMOD_DIR', ( fileperms( $ROOT_DIR ) & 0777 | 0755 ) );

if ( ! defined('FS_CHMOD_FILE') ) {
	define('FS_CHMOD_FILE', (fileperms($ROOT_DIR . DIRECTORY_SEPARATOR . 'index.php') & 0777 | 0644));
}

/**
 * Return this script file name
 * @return mixed
 */
function getThisScriptFileName()
{
	$scriptParts = pathinfo($_SERVER['SCRIPT_FILENAME']);

	if(strpos($_SERVER['REQUEST_URI'],$scriptParts['basename'])==false){
		$newRequestUri = parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
		$newRequestUri.=$scriptParts['basename'];
		$newRequestUri.='?' . parse_url($_SERVER['REQUEST_URI'],PHP_URL_QUERY);
		$_SERVER['REQUEST_URI']=$newRequestUri;
	}
	return $scriptParts['basename'];
}

/**
 * Return this request url
 * @return string
 */
function getUrl(){
	$pageURL = 'http';
	if (array_key_exists("HTTPS",$_SERVER) && $_SERVER["HTTPS"] == "on") {
		$pageURL .= "s";
	}
	$pageURL .= "://";
	if ($_SERVER["SERVER_PORT"] != "80") {
		$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
	} else {
		$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	}
	return $pageURL;
}




class XApp_Error {
	/**
	 * Stores the list of errors.
	 *
	 * @since 2.1.0
	 * @var array
	 * @access private
	 */
	private $errors = array();

	/**
	 * Stores the list of data for error codes.
	 *
	 * @since 2.1.0
	 * @var array
	 * @access private
	 */
	private $error_data = array();

	/**
	 * Initialize the error.
	 *
	 * If `$code` is empty, the other parameters will be ignored.
	 * When `$code` is not empty, `$message` will be used even if
	 * it is empty. The `$data` parameter will be used only if it
	 * is not empty.
	 *
	 * Though the class is constructed with a single error code and
	 * message, multiple codes can be added using the `add()` method.
	 *
	 * @since 2.1.0
	 *
	 * @param string|int $code Error code
	 * @param string $message Error message
	 * @param mixed $data Optional. Error data.
	 * @return XApp_Error
	 */
	public function __construct( $code = '', $message = '', $data = '' ) {
		if ( empty($code) )
			return;

		$this->errors[$code][] = $message;

		if ( ! empty($data) )
			$this->error_data[$code] = $data;
	}

	/**
	 * Make private properties readable for backwards compatibility.
	 *
	 * @since 4.0.0
	 * @access public
	 *
	 * @param string $name Property to get.
	 * @return mixed Property.
	 */
	public function __get( $name ) {
		return $this->$name;
	}

	/**
	 * Make private properties settable for backwards compatibility.
	 *
	 * @since 4.0.0
	 * @access public
	 *
	 * @param string $name  Property to set.
	 * @param mixed  $value Property value.
	 * @return mixed Newly-set property.
	 */
	public function __set( $name, $value ) {
		return $this->$name = $value;
	}

	/**
	 * Make private properties checkable for backwards compatibility.
	 *
	 * @since 4.0.0
	 * @access public
	 *
	 * @param string $name Property to check if set.
	 * @return bool Whether the property is set.
	 */
	public function __isset( $name ) {
		return isset( $this->$name );
	}

	/**
	 * Make private properties un-settable for backwards compatibility.
	 *
	 * @since 4.0.0
	 * @access public
	 *
	 * @param string $name Property to unset.
	 */
	public function __unset( $name ) {
		unset( $this->$name );
	}

	/**
	 * Retrieve all error codes.
	 *
	 * @since 2.1.0
	 * @access public
	 *
	 * @return array List of error codes, if available.
	 */
	public function get_error_codes() {
		if ( empty($this->errors) )
			return array();

		return array_keys($this->errors);
	}

	/**
	 * Retrieve first error code available.
	 *
	 * @since 2.1.0
	 * @access public
	 *
	 * @return string|int Empty string, if no error codes.
	 */
	public function get_error_code() {
		$codes = $this->get_error_codes();

		if ( empty($codes) )
			return '';

		return $codes[0];
	}

	/**
	 * Retrieve all error messages or error messages matching code.
	 *
	 * @since 2.1.0
	 *
	 * @param string|int $code Optional. Retrieve messages matching code, if exists.
	 * @return array Error strings on success, or empty array on failure (if using code parameter).
	 */
	public function get_error_messages($code = '') {
		// Return all messages if no code specified.
		if ( empty($code) ) {
			$all_messages = array();
			foreach ( (array) $this->errors as $code => $messages )
				$all_messages = array_merge($all_messages, $messages);

			return $all_messages;
		}

		if ( isset($this->errors[$code]) )
			return $this->errors[$code];
		else
			return array();
	}

	/**
	 * Get single error message.
	 *
	 * This will get the first message available for the code. If no code is
	 * given then the first code available will be used.
	 *
	 * @since 2.1.0
	 *
	 * @param string|int $code Optional. Error code to retrieve message.
	 * @return string
	 */
	public function get_error_message($code = '') {
		if ( empty($code) )
			$code = $this->get_error_code();
		$messages = $this->get_error_messages($code);
		if ( empty($messages) )
			return '';
		return $messages[0];
	}

	/**
	 * Retrieve error data for error code.
	 *
	 * @since 2.1.0
	 *
	 * @param string|int $code Optional. Error code.
	 * @return mixed Null, if no errors.
	 */
	public function get_error_data($code = '') {
		if ( empty($code) )
			$code = $this->get_error_code();

		if ( isset($this->error_data[$code]) )
			return $this->error_data[$code];
		return null;
	}

	/**
	 * Add an error or append additional message to an existing error.
	 *
	 * @since 2.1.0
	 * @access public
	 *
	 * @param string|int $code Error code.
	 * @param string $message Error message.
	 * @param mixed $data Optional. Error data.
	 */
	public function add($code, $message, $data = '') {
		$this->errors[$code][] = $message;
		if ( ! empty($data) )
			$this->error_data[$code] = $data;
	}

	/**
	 * Add data for error code.
	 *
	 * The error code can only contain one error data.
	 *
	 * @since 2.1.0
	 *
	 * @param mixed $data Error data.
	 * @param string|int $code Error code.
	 */
	public function add_data($data, $code = '') {
		if ( empty($code) )
			$code = $this->get_error_code();

		$this->error_data[$code] = $data;
	}
}
function curl_progress($resource,$download_size, $downloaded, $upload_size, $uploaded)
{
	if($download_size > 0)
		echo $downloaded / $download_size  * 100;

	ob_flush();
	flush();
	sleep(1); // just to see effect
}

/**
 * HTTP request method uses Curl extension to retrieve the url.
 *
 * Requires the Curl extension to be installed.
 *
 * @package WordPress
 * @subpackage HTTP
 * @since 2.7.0
 */
class XAppHttp_Curl {




	/**
	 * Temporary header storage for during requests.
	 *
	 * @since 3.2.0
	 * @access private
	 * @var string
	 */
	private $headers = '';

	/**
	 * Temporary body storage for during requests.
	 *
	 * @since 3.6.0
	 * @access private
	 * @var string
	 */
	private $body = '';

	/**
	 * The maximum amount of data to receive from the remote server.
	 *
	 * @since 3.6.0
	 * @access private
	 * @var int
	 */
	private $max_body_length = false;

	/**
	 * The file resource used for streaming to file.
	 *
	 * @since 3.6.0
	 * @access private
	 * @var resource
	 */
	private $stream_handle = false;

	/**
	 * Send a HTTP request to a URI using cURL extension.
	 *
	 * @access public
	 * @since 2.7.0
	 *
	 * @param string $url The request URL.
	 * @param string|array $args Optional. Override the defaults.
	 * @return array|Error Array containing 'headers', 'body', 'response', 'cookies', 'filename'. A Error instance upon error
	 */
	public function request($url, $args = array()) {
		$defaults = array(
			'method' => 'GET', 'timeout' => 5,
			'redirection' => 5, 'httpversion' => '1.0',
			'blocking' => true,
			'headers' => array(), 'body' => null, 'cookies' => array()
		);

		$r = xapp_parse_args( $args, $defaults );

		if ( isset($r['headers']['User-Agent']) ) {
			$r['user-agent'] = $r['headers']['User-Agent'];
			unset($r['headers']['User-Agent']);
		} else if ( isset($r['headers']['user-agent']) ) {
			$r['user-agent'] = $r['headers']['user-agent'];
			unset($r['headers']['user-agent']);
		}

		// Construct Cookie: header if any cookies are set.
		//XAppHttp::buildCookieHeader( $r );

		$handle = curl_init();


		$is_local = isset($r['local']) && $r['local'];
		$ssl_verify = isset($r['sslverify']) && $r['sslverify'];
		if ( $is_local ) {
			/** This filter is documented in wp-includes/class-http.php */
			$ssl_verify = false;
		} elseif ( ! $is_local ) {
			/** This filter is documented in wp-includes/class-http.php */
			$ssl_verify = false;
		}

		/*
		 * CURLOPT_TIMEOUT and CURLOPT_CONNECTTIMEOUT expect integers. Have to use ceil since.
		 * a value of 0 will allow an unlimited timeout.
		 */
		$timeout = (int) ceil( $r['timeout'] );
		curl_setopt( $handle, CURLOPT_CONNECTTIMEOUT, $timeout );
		curl_setopt( $handle, CURLOPT_TIMEOUT, $timeout );

		curl_setopt( $handle, CURLOPT_URL, $url);
		curl_setopt( $handle, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $handle, CURLOPT_SSL_VERIFYHOST, ( $ssl_verify === true ) ? 2 : false );
		curl_setopt( $handle, CURLOPT_SSL_VERIFYPEER, $ssl_verify );
		curl_setopt( $handle, CURLOPT_CAINFO, $r['sslcertificates'] );
		curl_setopt( $handle, CURLOPT_USERAGENT, $r['user-agent'] );
		//curl_setopt($handle, CURLOPT_PROGRESSFUNCTION, 'curl_progress');
		//curl_setopt($handle, CURLOPT_NOPROGRESS, false); // needed to make progress function work



		/*
		 * The option doesn't work with safe mode or when open_basedir is set, and there's
		 * a bug #17490 with redirected POST requests, so handle redirections outside Curl.
		 */
		curl_setopt( $handle, CURLOPT_FOLLOWLOCATION, false );
		if ( defined( 'CURLOPT_PROTOCOLS' ) ) // PHP 5.2.10 / cURL 7.19.4
			curl_setopt( $handle, CURLOPT_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS );

		switch ( $r['method'] ) {
			case 'HEAD':
				curl_setopt( $handle, CURLOPT_NOBODY, true );
				break;
			case 'POST':
				curl_setopt( $handle, CURLOPT_POST, true );
				curl_setopt( $handle, CURLOPT_POSTFIELDS, $r['body'] );
				break;
			case 'PUT':
				curl_setopt( $handle, CURLOPT_CUSTOMREQUEST, 'PUT' );
				curl_setopt( $handle, CURLOPT_POSTFIELDS, $r['body'] );
				break;
			default:
				curl_setopt( $handle, CURLOPT_CUSTOMREQUEST, $r['method'] );
				if ( ! is_null( $r['body'] ) )
					curl_setopt( $handle, CURLOPT_POSTFIELDS, $r['body'] );
				break;
		}

		if ( true === $r['blocking'] ) {
			curl_setopt( $handle, CURLOPT_HEADERFUNCTION, array( $this, 'stream_headers' ) );
			curl_setopt( $handle, CURLOPT_WRITEFUNCTION, array( $this, 'stream_body' ) );
		}

		curl_setopt( $handle, CURLOPT_HEADER, false );

		if ( isset( $r['limit_response_size'] ) )
			$this->max_body_length = intval( $r['limit_response_size'] );
		else
			$this->max_body_length = false;

		// If streaming to a file open a file handle, and setup our curl streaming handler.
		if ( $r['stream'] ) {
				$this->stream_handle = @fopen( $r['filename'], 'w+' );

			if ( ! $this->stream_handle )
				return new XApp_Error( 'http_request_failed', sprintf( ( 'Could not open handle for fopen() to %s' ), $r['filename'] ) );
		} else {
			$this->stream_handle = false;
		}

		if ( !empty( $r['headers'] ) ) {
			// cURL expects full header strings in each element.
			$headers = array();
			foreach ( $r['headers'] as $name => $value ) {
				$headers[] = "{$name}: $value";
			}
			curl_setopt( $handle, CURLOPT_HTTPHEADER, $headers );
		}

		if ( $r['httpversion'] == '1.0' )
			curl_setopt( $handle, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0 );
		else
			curl_setopt( $handle, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1 );

		/**
		 * Fires before the cURL request is executed.
		 *
		 * Cookies are not currently handled by the HTTP API. This action allows
		 * plugins to handle cookies themselves.
		 *
		 * @since 2.8.0
		 *
		 * @param resource &$handle The cURL handle returned by curl_init().
		 * @param array    $r       The HTTP request arguments.
		 * @param string   $url     The request URL.
		 */
		//do_action_ref_array( 'http_api_curl', array( &$handle, $r, $url ) );

		// We don't need to return the body, so don't. Just execute request and return.
		if ( ! $r['blocking'] ) {
			curl_exec( $handle );

			if ( $curl_error = curl_error( $handle ) ) {
				curl_close( $handle );
				return new XApp_Error( 'http_request_failed', $curl_error );
			}
			if ( in_array( curl_getinfo( $handle, CURLINFO_HTTP_CODE ), array( 301, 302 ) ) ) {
				curl_close( $handle );
				return new XApp_Error( 'http_request_failed', ( 'Too many redirects.' ) );
			}

			curl_close( $handle );
			return array( 'headers' => array(), 'body' => '', 'response' => array('code' => false, 'message' => false), 'cookies' => array() );
		}

		curl_exec( $handle );
		$theHeaders = XAppHttp::processHeaders( $this->headers, $url );
		$theBody = $this->body;

		$this->headers = '';
		$this->body = '';

		$curl_error = curl_errno( $handle );

		// If an error occurred, or, no response.
		if ( $curl_error || ( 0 == strlen( $theBody ) && empty( $theHeaders['headers'] ) ) ) {
			if ( CURLE_WRITE_ERROR /* 23 */ == $curl_error &&  $r['stream'] ) {
				fclose( $this->stream_handle );
				return new XApp_Error( 'http_request_failed', ( 'Failed to write request to temporary file.' ) );
			}
			if ( $curl_error = curl_error( $handle ) ) {
				curl_close( $handle );
				return new XApp_Error( 'http_request_failed', $curl_error );
			}
			if ( in_array( curl_getinfo( $handle, CURLINFO_HTTP_CODE ), array( 301, 302 ) ) ) {
				curl_close( $handle );
				return new XApp_Error( 'http_request_failed', ( 'Too many redirects.' ) );
			}
		}

		$response = array();
		$response['code'] = curl_getinfo( $handle, CURLINFO_HTTP_CODE );
		$response['message'] = 'no message';// get_status_header_desc($response['code']);

		curl_close( $handle );

		if ( $r['stream'] )
			fclose( $this->stream_handle );

		$response = array(
			'headers' => $theHeaders['headers'],
			'body' => null,
			'response' => $response,
			'cookies' => $theHeaders['cookies'],
			'filename' => $r['filename']
		);

		// Handle redirects.
		//if ( false !== ( $redirect_response = XAppHTTP::handle_redirects( $url, $r, $response ) ) )
	//		return $redirect_response;

		//if ( true === $r['decompress'] && true === XAppHttp_Encoding::should_decode($theHeaders['headers']) )
		//	$theBody = XAppHttp_Encoding::decompress( $theBody );

		$response['body'] = $theBody;

		return $response;
	}

	/**
	 * Grab the headers of the cURL request
	 *
	 * Each header is sent individually to this callback, so we append to the $header property for temporary storage
	 *
	 * @since 3.2.0
	 * @access private
	 * @return int
	 */
	private function stream_headers( $handle, $headers ) {
		$this->headers .= $headers;
		return strlen( $headers );
	}

	/**
	 * Grab the body of the cURL request
	 *
	 * The contents of the document are passed in chunks, so we append to the $body property for temporary storage.
	 * Returning a length shorter than the length of $data passed in will cause cURL to abort the request as "completed"
	 *
	 * @since 3.6.0
	 * @access private
	 * @return int
	 */
	private function stream_body( $handle, $data ) {
		$data_length = strlen( $data );

		if ( $this->max_body_length && ( strlen( $this->body ) + $data_length ) > $this->max_body_length )
			$data = substr( $data, 0, ( $this->max_body_length - $data_length ) );

		if ( $this->stream_handle ) {
			$bytes_written = fwrite( $this->stream_handle, $data );
		} else {
			$this->body .= $data;
			$bytes_written = $data_length;
		}

		// Upon event of this function returning less than strlen( $data ) curl will error with CURLE_WRITE_ERROR.
		return $bytes_written;
	}

	/**
	 * Whether this class can be used for retrieving an URL.
	 *
	 * @static
	 * @since 2.7.0
	 *
	 * @return boolean False means this class can not be used, true means it can.
	 */
	public static function test( $args = array() ) {
		if ( ! function_exists( 'curl_init' ) || ! function_exists( 'curl_exec' ) )
			return false;

		$is_ssl = isset( $args['ssl'] ) && $args['ssl'];

		if ( $is_ssl ) {
			$curl_version = curl_version();
			// Check whether this cURL version support SSL requests.
			if ( ! (CURL_VERSION_SSL & $curl_version['features']) )
				return false;
		}

		/**
		 * Filter whether cURL can be used as a transport for retrieving a URL.
		 *
		 * @since 2.7.0
		 *
		 * @param bool  $use_class Whether the class can be used. Default true.
		 * @param array $args      An array of request arguments.
		 */
		return true;
	}
}
/**
 * Merge user defined arguments into defaults array.
 *
 * This function is used throughout WordPress to allow for both string or array
 * to be merged into another array.
 *
 * @since 2.2.0
 *
 * @param string|array $args     Value to merge with $defaults
 * @param array        $defaults Optional. Array that serves as the defaults. Default empty.
 * @return array Merged user defined values with defaults.
 */
function xapp_parse_args( $args, $defaults = '' ) {
	if ( is_object( $args ) )
		$r = get_object_vars( $args );
	elseif ( is_array( $args ) )
		$r =& $args;
	else
		xapp_parse_str( $args, $r );

	if ( is_array( $defaults ) )
		return array_merge( $defaults, $r );
	return $r;
}

function mbstring_binary_safe_encoding( $reset = false ) {
	static $encodings = array();
	static $overloaded = null;

	if ( is_null( $overloaded ) )
		$overloaded = function_exists( 'mb_internal_encoding' ) && ( ini_get( 'mbstring.func_overload' ) & 2 );

	if ( false === $overloaded )
		return;

	if ( ! $reset ) {
		$encoding = mb_internal_encoding();
		array_push( $encodings, $encoding );
		mb_internal_encoding( 'ISO-8859-1' );
	}

	if ( $reset && $encodings ) {
		$encoding = array_pop( $encodings );
		mb_internal_encoding( $encoding );
	}
}

/**
 * Reset the mbstring internal encoding to a users previously set encoding.
 *
 * @see mbstring_binary_safe_encoding()
 *
 * @since 3.7.0
 */
function reset_mbstring_encoding() {
	mbstring_binary_safe_encoding( true );
}

class XAppHttp
{
	/**
	 * Transform header string into an array.
	 *
	 * If an array is given then it is assumed to be raw header data with numeric keys with the
	 * headers as the values. No headers must be passed that were already processed.
	 *
	 * @access public
	 * @static
	 * @since 2.7.0
	 *
	 * @param string|array $headers
	 * @param string $url The URL that was requested
	 * @return array Processed string headers. If duplicate headers are encountered,
	 * 					Then a numbered array is returned as the value of that header-key.
	 */
	public static function processHeaders( $headers, $url = '' ) {
		// Split headers, one per array element.
		if ( is_string($headers) ) {
			// Tolerate line terminator: CRLF = LF (RFC 2616 19.3).
			$headers = str_replace("\r\n", "\n", $headers);
			/*
			 * Unfold folded header fields. LWS = [CRLF] 1*( SP | HT ) <US-ASCII SP, space (32)>,
			 * <US-ASCII HT, horizontal-tab (9)> (RFC 2616 2.2).
			 */
			$headers = preg_replace('/\n[ \t]/', ' ', $headers);
			// Create the headers array.
			$headers = explode("\n", $headers);
		}

		$response = array('code' => 0, 'message' => '');

		/*
		 * If a redirection has taken place, The headers for each page request may have been passed.
		 * In this case, determine the final HTTP header and parse from there.
		 */
		for ( $i = count($headers)-1; $i >= 0; $i-- ) {
			if ( !empty($headers[$i]) && false === strpos($headers[$i], ':') ) {
				$headers = array_splice($headers, $i);
				break;
			}
		}

		$cookies = array();
		$newheaders = array();
		foreach ( (array) $headers as $tempheader ) {
			if ( empty($tempheader) )
				continue;

			if ( false === strpos($tempheader, ':') ) {
				$stack = explode(' ', $tempheader, 3);
				$stack[] = '';
				list( , $response['code'], $response['message']) = $stack;
				continue;
			}

			list($key, $value) = explode(':', $tempheader, 2);

			$key = strtolower( $key );
			$value = trim( $value );

			if ( isset( $newheaders[ $key ] ) ) {
				if ( ! is_array( $newheaders[ $key ] ) )
					$newheaders[$key] = array( $newheaders[ $key ] );
				$newheaders[ $key ][] = $value;
			} else {
				$newheaders[ $key ] = $value;
			}
			//if ( 'set-cookie' == $key )
			//	$cookies[] = new XAppHttp_Cookie( $value, $url );
		}

		return array('response' => $response, 'headers' => $newheaders, 'cookies' => $cookies);
	}
	/**
	 * Tests which transports are capable of supporting the request.
	 *
	 * @since 3.2.0
	 * @access private
	 *
	 * @param array $args Request arguments
	 * @param string $url URL to Request
	 *
	 * @return string|bool Class name for the first transport that claims to support the request. False if no transport claims to support the request.
	 */
	public function _get_first_available_transport( $args, $url = null ) {
		/**
		 * Filter which HTTP transports are available and in what order.
		 *
		 * @since 3.7.0
		 *
		 * @param array  $value Array of HTTP transports to check. Default array contains
		 *                      'curl', and 'streams', in that order.
		 * @param array  $args  HTTP request arguments.
		 * @param string $url   The URL to request.
		 */
		$request_order = array( 'curl', 'streams' );

		// Loop over each transport on each HTTP request looking for one which will serve this request's needs.
		foreach ( $request_order as $transport ) {
			$class = 'XAppHTTP_' . $transport;

			// Check to see if this transport is a possibility, calls the transport statically.
			if ( !call_user_func( array( $class, 'test' ), $args, $url ) )
				continue;

			return $class;
		}

		return false;
	}

	/**
	 * Dispatches a HTTP request to a supporting transport.
	 *
	 * Tests each transport in order to find a transport which matches the request arguments.
	 * Also caches the transport instance to be used later.
	 *
	 * The order for requests is cURL, and then PHP Streams.
	 *
	 * @since 3.2.0
	 * @access private
	 *
	 * @param string $url URL to Request
	 * @param array $args Request arguments
	 * @return array|Error Array containing 'headers', 'body', 'response', 'cookies', 'filename'. A Error instance upon error
	 */
	private function _dispatch_request( $url, $args ) {
		static $transports = array();

		$class = $this->_get_first_available_transport( $args, $url );
		if ( !$class )
			return new XApp_Error( 'http_failure', 'There are no HTTP transports available which can complete the requested request.' );

		// Transport claims to support request, instantiate it and give it a whirl.
		if ( empty( $transports[$class] ) )
			$transports[$class] = new $class;

		$response = $transports[$class]->request( $url, $args );

		/**
		 * Fires after an HTTP API response is received and before the response is returned.
		 *
		 * @since 2.8.0
		 *
		 * @param array|Error $response HTTP response or Error object.
		 * @param string         $context  Context under which the hook is fired.
		 * @param string         $class    HTTP transport used.
		 * @param array          $args     HTTP request arguments.
		 * @param string         $url      The request URL.
		 */
		//do_action( 'http_api_debug', $response, 'response', $class, $args, $url );

		//if ( is_Error( $response ) )
		//	return $response;

		/**
		 * Filter the HTTP API response immediately before the response is returned.
		 *
		 * @since 2.9.0
		 *
		 * @param array  $response HTTP response.
		 * @param array  $args     HTTP request arguments.
		 * @param string $url      The request URL.
		 */
		//return apply_filters( 'http_response', $response, $args, $url );
		return $response;
	}

	public function request($url, $args = array())
	{


		$defaults = array(
			'method' => 'GET',
			/**
			 * Filter the timeout value for an HTTP request.
			 *
			 * @since 2.7.0
			 *
			 * @param int $timeout_value Time in seconds until a request times out.
			 *                           Default 5.
			 */
			'timeout' => 5,
			/**
			 * Filter the number of redirects allowed during an HTTP request.
			 *
			 * @since 2.7.0
			 *
			 * @param int $redirect_count Number of redirects allowed. Default 5.
			 */
			'redirection' => 5,
			/**
			 * Filter the version of the HTTP protocol used in a request.
			 *
			 * @since 2.7.0
			 *
			 * @param string $version Version of HTTP used. Accepts '1.0' and '1.1'.
			 *                        Default '1.0'.
			 */
			'httpversion' => '1.0',
			/**
			 * Filter the user agent value sent with an HTTP request.
			 *
			 * @since 2.7.0
			 *
			 * @param string $user_agent WordPress user agent string.
			 */
			'user-agent' => 'no agent',
			/**
			 * Filter whether to pass URLs through xapp_http_validate_url() in an HTTP request.
			 *
			 * @since 3.6.0
			 *
			 * @param bool $pass_url Whether to pass URLs through xapp_http_validate_url().
			 *                       Default false.
			 */
			'reject_unsafe_urls' => false,
			'blocking' => true,
			'headers' => array(),
			'cookies' => array(),
			'body' => null,
			'compress' => false,
			'decompress' => true,
			'sslverify' => true,
			'sslcertificates' => null,
			'stream' => false,
			'filename' => null,
			'limit_response_size' => null,
		);

		// Pre-parse for the HEAD checks.
		$args = xapp_parse_args($args);

		// By default, Head requests do not cause redirections.
		if (isset($args['method']) && 'HEAD' == $args['method']) {
			$defaults['redirection'] = 0;
		}

		$r = xapp_parse_args($args, $defaults);


		// The transports decrement this, store a copy of the original value for loop purposes.
		if (!isset($r['_redirection'])) {
			$r['_redirection'] = $r['redirection'];
		}


		$arrURL = @parse_url($url);

		if (empty($url) || empty($arrURL['scheme'])) {
			return new XApp_Error('http_request_failed', ('A valid URL was not provided.'));
		}

		/*
		 * Determine if this is a https call and pass that on to the transport functions
		 * so that we can blacklist the transports that do not support ssl verification
		 */
		$r['ssl'] = $arrURL['scheme'] == 'https' || $arrURL['scheme'] == 'ssl';

		// Determine if this request is to OUR install of WordPress.

		$r['local'] = 'localhost' == $arrURL['host'] || (isset($homeURL['host']) && $homeURL['host'] == $arrURL['host']);

		/*
		 * If we are streaming to a file but no filename was given drop it in the WP temp dir
		 * and pick its name using the basename of the $url.
		 */
		if ($r['stream'] && empty($r['filename'])) {
			$r['filename'] = get_temp_dir() . basename($url);
		}

		/*
		 * Force some settings if we are streaming to a file and check for existence and perms
		 * of destination directory.
		 */
		if ($r['stream']) {
			$r['blocking'] = true;
			if (!xapp_is_writable(dirname($r['filename']))) {
				return new XApp_Error(
					'http_request_failed',
					('Destination directory for file streaming does not exist or is not writable.')
				);
			}
		}

		if (is_null($r['headers'])) {
			$r['headers'] = array();
		}

		if (isset($r['headers']['User-Agent'])) {
			$r['user-agent'] = $r['headers']['User-Agent'];
			unset($r['headers']['User-Agent']);
		}

		if (isset($r['headers']['user-agent'])) {
			$r['user-agent'] = $r['headers']['user-agent'];
			unset($r['headers']['user-agent']);
		}

		if ('1.1' == $r['httpversion'] && !isset($r['headers']['connection'])) {
			$r['headers']['connection'] = 'close';
		}

		// Construct Cookie: header if any cookies are set.
		//XAppHttp::buildCookieHeader( $r );

		// Avoid issues where mbstring.func_overload is enabled.
		mbstring_binary_safe_encoding();

		if (!isset($r['headers']['Accept-Encoding'])) {
			//if ( $encoding = XAppHttp_Encoding::accept_encoding( $url, $r ) )
			//	$r['headers']['Accept-Encoding'] = $encoding;
		}

		if ((!is_null($r['body']) && '' != $r['body']) || 'POST' == $r['method'] || 'PUT' == $r['method']) {
			if (is_array($r['body']) || is_object($r['body'])) {
				$r['body'] = http_build_query($r['body'], null, '&');

				if (!isset($r['headers']['Content-Type'])) {
					$r['headers']['Content-Type'] = 'application/x-www-form-urlencoded; charset=' . get_option(
							'blog_charset'
						);
				}
			}

			if ('' === $r['body']) {
				$r['body'] = null;
			}

			if (!isset($r['headers']['Content-Length']) && !isset($r['headers']['content-length'])) {
				$r['headers']['Content-Length'] = strlen($r['body']);
			}
		}

		$response = $this->_dispatch_request($url, $r);

		reset_mbstring_encoding();

		return $response;
	}
}

/**
 * Workaround for Windows bug in is_writable() function
 *
 * PHP has issues with Windows ACL's for determine if a
 * directory is writable or not, this works around them by
 * checking the ability to open files rather than relying
 * upon PHP to interprate the OS ACL.
 *
 * @since 2.8.0
 *
 * @see http://bugs.php.net/bug.php?id=27609
 * @see http://bugs.php.net/bug.php?id=30931
 *
 * @param string $path Windows path to check for write-ability.
 * @return bool Whether the path is writable.
 */
function win_is_writable( $path ) {

	if ( $path[strlen( $path ) - 1] == '/' ) // if it looks like a directory, check a random file within the directory
		return win_is_writable( $path . uniqid( mt_rand() ) . '.tmp');
	else if ( is_dir( $path ) ) // If it's a directory (and not a file) check a random file within the directory
		return win_is_writable( $path . '/' . uniqid( mt_rand() ) . '.tmp' );

	// check tmp file for read/write capabilities
	$should_delete_tmp_file = !file_exists( $path );
	$f = @fopen( $path, 'a' );
	if ( $f === false )
		return false;
	fclose( $f );
	if ( $should_delete_tmp_file )
		unlink( $path );
	return true;
}

/**
 * Determine if a directory is writable.
 *
 * This function is used to work around certain ACL issues in PHP primarily
 * affecting Windows Servers.
 *
 * @since 3.6.0
 *
 * @see win_is_writable()
 *
 * @param string $path Path to check for write-ability.
 * @return bool Whether the path is writable.
 */
function xapp_is_writable( $path ) {
	if ( 'WIN' === strtoupper( substr( PHP_OS, 0, 3 ) ) )
		return win_is_writable( $path );
	else
		return @is_writable( $path );
}

/**
 * Removes trailing forward slashes and backslashes if they exist.
 *
 * The primary use of this is for paths and thus should be used for paths. It is
 * not restricted to paths and offers no specific path support.
 *
 * @since 2.2.0
 *
 * @param string $string What to remove the trailing slashes from.
 * @return string String without the trailing slashes.
 */
function untrailingslashit( $string ) {
	return rtrim( $string, '/\\' );
}
/**
 * Appends a trailing slash.
 *
 * Will remove trailing forward and backslashes if it exists already before adding
 * a trailing forward slash. This prevents double slashing a string or path.
 *
 * The primary use of this is for paths and thus should be used for paths. It is
 * not restricted to paths and offers no specific path support.
 *
 * @since 1.2.0
 *
 * @param string $string What to add the trailing slash to.
 * @return string String with trailing slash added.
 */
function trailingslashit( $string ) {
	return untrailingslashit( $string ) . '/';
}
/**
 * Determine a writable directory for temporary files.
 *
 * Function's preference is the return value of sys_get_temp_dir(),
 * followed by your PHP temporary upload directory, followed by XAppCONTENT_DIR,
 * before finally defaulting to /tmp/
 *
 * In the event that this function does not find a writable location,
 * It may be overridden by the XAppTEMP_DIR constant in your wp-config.php file.
 *
 * @since 2.5.0
 *
 * @return string Writable temporary directory.
 */
function get_temp_dir() {
	static $temp;
	if ( defined('XAppTEMP_DIR') )
		return trailingslashit(XAppTEMP_DIR);

	if ( $temp )
		return trailingslashit( $temp );

	if ( function_exists('sys_get_temp_dir') ) {
		$temp = sys_get_temp_dir();
		if ( @is_dir( $temp ) && xapp_is_writable( $temp ) )
			return trailingslashit( $temp );
	}

	$temp = ini_get('upload_tmp_dir');
	if ( @is_dir( $temp ) && xapp_is_writable( $temp ) )
		return trailingslashit( $temp );

	$temp = realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR);
	if ( is_dir( $temp ) && xapp_is_writable( $temp ) )
		return $temp;

	$temp = '/tmp/';
	return $temp;
}
function get_allowed_mime_types(){
	return array(
		// Image formats.
		'jpg|jpeg|jpe' => 'image/jpeg',
		'gif' => 'image/gif',
		'png' => 'image/png',
		'bmp' => 'image/bmp',
		'tif|tiff' => 'image/tiff',
		'ico' => 'image/x-icon',
		// Video formats.
		'asf|asx' => 'video/x-ms-asf',
		'wmv' => 'video/x-ms-wmv',
		'wmx' => 'video/x-ms-wmx',
		'wm' => 'video/x-ms-wm',
		'avi' => 'video/avi',
		'divx' => 'video/divx',
		'flv' => 'video/x-flv',
		'mov|qt' => 'video/quicktime',
		'mpeg|mpg|mpe' => 'video/mpeg',
		'mp4|m4v' => 'video/mp4',
		'ogv' => 'video/ogg',
		'webm' => 'video/webm',
		'mkv' => 'video/x-matroska',
		'3gp|3gpp' => 'video/3gpp', // Can also be audio
		'3g2|3gp2' => 'video/3gpp2', // Can also be audio
		// Text formats.
		'txt|asc|c|cc|h|srt' => 'text/plain',
		'csv' => 'text/csv',
		'tsv' => 'text/tab-separated-values',
		'ics' => 'text/calendar',
		'rtx' => 'text/richtext',
		'css' => 'text/css',
		'htm|html' => 'text/html',
		'vtt' => 'text/vtt',
		'dfxp' => 'application/ttaf+xml',
		// Audio formats.
		'mp3|m4a|m4b' => 'audio/mpeg',
		'ra|ram' => 'audio/x-realaudio',
		'wav' => 'audio/wav',
		'ogg|oga' => 'audio/ogg',
		'mid|midi' => 'audio/midi',
		'wma' => 'audio/x-ms-wma',
		'wax' => 'audio/x-ms-wax',
		'mka' => 'audio/x-matroska',
		// Misc application formats.
		'rtf' => 'application/rtf',
		'js' => 'application/javascript',
		'pdf' => 'application/pdf',
		'swf' => 'application/x-shockwave-flash',
		'class' => 'application/java',
		'tar' => 'application/x-tar',
		'zip' => 'application/zip',
		'gz|gzip' => 'application/x-gzip',
		'rar' => 'application/rar',
		'7z' => 'application/x-7z-compressed',
		'exe' => 'application/x-msdownload',
		// MS Office formats.
		'doc' => 'application/msword',
		'pot|pps|ppt' => 'application/vnd.ms-powerpoint',
		'wri' => 'application/vnd.ms-write',
		'xla|xls|xlt|xlw' => 'application/vnd.ms-excel',
		'mdb' => 'application/vnd.ms-access',
		'mpp' => 'application/vnd.ms-project',
		'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
		'docm' => 'application/vnd.ms-word.document.macroEnabled.12',
		'dotx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
		'dotm' => 'application/vnd.ms-word.template.macroEnabled.12',
		'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
		'xlsm' => 'application/vnd.ms-excel.sheet.macroEnabled.12',
		'xlsb' => 'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
		'xltx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
		'xltm' => 'application/vnd.ms-excel.template.macroEnabled.12',
		'xlam' => 'application/vnd.ms-excel.addin.macroEnabled.12',
		'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
		'pptm' => 'application/vnd.ms-powerpoint.presentation.macroEnabled.12',
		'ppsx' => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
		'ppsm' => 'application/vnd.ms-powerpoint.slideshow.macroEnabled.12',
		'potx' => 'application/vnd.openxmlformats-officedocument.presentationml.template',
		'potm' => 'application/vnd.ms-powerpoint.template.macroEnabled.12',
		'ppam' => 'application/vnd.ms-powerpoint.addin.macroEnabled.12',
		'sldx' => 'application/vnd.openxmlformats-officedocument.presentationml.slide',
		'sldm' => 'application/vnd.ms-powerpoint.slide.macroEnabled.12',
		'onetoc|onetoc2|onetmp|onepkg' => 'application/onenote',
		'oxps' => 'application/oxps',
		'xps' => 'application/vnd.ms-xpsdocument',
		// OpenOffice formats.
		'odt' => 'application/vnd.oasis.opendocument.text',
		'odp' => 'application/vnd.oasis.opendocument.presentation',
		'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
		'odg' => 'application/vnd.oasis.opendocument.graphics',
		'odc' => 'application/vnd.oasis.opendocument.chart',
		'odb' => 'application/vnd.oasis.opendocument.database',
		'odf' => 'application/vnd.oasis.opendocument.formula',
		// WordPerfect formats.
		'wp|wpd' => 'application/wordperfect',
		// iWork formats.
		'key' => 'application/vnd.apple.keynote',
		'numbers' => 'application/vnd.apple.numbers',
		'pages' => 'application/vnd.apple.pages',
	);
}
/**
 * Sanitizes a filename, replacing whitespace with dashes.
 *
 * Removes special characters that are illegal in filenames on certain
 * operating systems and special characters requiring special escaping
 * to manipulate at the command line. Replaces spaces and consecutive
 * dashes with a single dash. Trims period, dash and underscore from beginning
 * and end of filename.
 *
 * @since 2.1.0
 *
 * @param string $filename The filename to be sanitized
 * @return string The sanitized filename
 */
function sanitize_file_name( $filename ) {
	$filename_raw = $filename;
	$special_chars = array("?", "[", "]", "/", "\\", "=", "<", ">", ":", ";", ",", "'", "\"", "&", "$", "#", "*", "(", ")", "|", "~", "`", "!", "{", "}", chr(0));
	/**
	 * Filter the list of characters to remove from a filename.
	 *
	 * @since 2.8.0
	 *
	 * @param array  $special_chars Characters to remove.
	 * @param string $filename_raw  Filename as it was passed into sanitize_file_name().
	 */
	$filename = preg_replace( "#\x{00a0}#siu", ' ', $filename );
	$filename = str_replace($special_chars, '', $filename);
	$filename = str_replace( array( '%20', '+' ), '-', $filename );
	$filename = preg_replace('/[\s-]+/', '-', $filename);
	$filename = trim($filename, '.-_');

	// Split the filename into a base and extension[s]
	$parts = explode('.', $filename);

	// Process multiple extensions
	$filename = array_shift($parts);
	$extension = array_pop($parts);
	$mimes = get_allowed_mime_types();

	/*
	 * Loop over any intermediate extensions. Postfix them with a trailing underscore
	 * if they are a 2 - 5 character long alpha string not in the extension whitelist.
	 */
	foreach ( (array) $parts as $part) {
		$filename .= '.' . $part;

		if ( preg_match("/^[a-zA-Z]{2,5}\d?$/", $part) ) {
			$allowed = false;
			foreach ( $mimes as $ext_preg => $mime_match ) {
				$ext_preg = '!^(' . $ext_preg . ')$!i';
				if ( preg_match( $ext_preg, $part ) ) {
					$allowed = true;
					break;
				}
			}
			if ( !$allowed )
				$filename .= '_';
		}
	}
	$filename .= '.' . $extension;
	/** This filter is documented in wp-includes/formatting.php */
	return $filename;
}
/**
 * Get a filename that is sanitized and unique for the given directory.
 *
 * If the filename is not unique, then a number will be added to the filename
 * before the extension, and will continue adding numbers until the filename is
 * unique.
 *
 * The callback is passed three parameters, the first one is the directory, the
 * second is the filename, and the third is the extension.
 *
 * @since 2.5.0
 *
 * @param string   $dir                      Directory.
 * @param string   $filename                 File name.
 * @param callback $unique_filename_callback Callback. Default null.
 * @return string New filename, if given wasn't unique.
 */
function xapp_unique_filename( $dir, $filename, $unique_filename_callback = null ) {
	// Sanitize the file name before we begin processing.
	$filename = sanitize_file_name($filename);

	// Separate the filename into a name and extension.
	$info = pathinfo($filename);
	$ext = !empty($info['extension']) ? '.' . $info['extension'] : '';
	$name = basename($filename, $ext);

	// Edge case: if file is named '.ext', treat as an empty name.
	if ( $name === $ext )
		$name = '';

	/*
	 * Increment the file number until we have a unique file to save in $dir.
	 * Use callback if supplied.
	 */
	if ( $unique_filename_callback && is_callable( $unique_filename_callback ) ) {
		$filename = call_user_func( $unique_filename_callback, $dir, $name, $ext );
	} else {
		$number = '';

		// Change '.ext' to lower case.
		if ( $ext && strtolower($ext) != $ext ) {
			$ext2 = strtolower($ext);
			$filename2 = preg_replace( '|' . preg_quote($ext) . '$|', $ext2, $filename );

			// Check for both lower and upper case extension or image sub-sizes may be overwritten.
			while ( file_exists($dir . "/$filename") || file_exists($dir . "/$filename2") ) {
				$new_number = $number + 1;
				$filename = str_replace( "$number$ext", "$new_number$ext", $filename );
				$filename2 = str_replace( "$number$ext2", "$new_number$ext2", $filename2 );
				$number = $new_number;
			}
			return $filename2;
		}

		while ( file_exists( $dir . "/$filename" ) ) {
			if ( '' == "$number$ext" )
				$filename = $filename . ++$number . $ext;
			else
				$filename = str_replace( "$number$ext", ++$number . $ext, $filename );
		}
	}

	return $filename;
}
/**
 * Navigates through an array and removes slashes from the values.
 *
 * If an array is passed, the array_map() function causes a callback to pass the
 * value back to the function. The slashes from this value will removed.
 *
 * @since 2.0.0
 *
 * @param mixed $value The value to be stripped.
 * @return mixed Stripped value.
 */
function stripslashes_deep($value) {
	if ( is_array($value) ) {
		$value = array_map('stripslashes_deep', $value);
	} elseif ( is_object($value) ) {
		$vars = get_object_vars( $value );
		foreach ($vars as $key=>$data) {
			$value->{$key} = stripslashes_deep( $data );
		}
	} elseif ( is_string( $value ) ) {
		$value = stripslashes($value);
	}

	return $value;
}
/**
 * Parses a string into variables to be stored in an array.
 *
 * Uses {@link http://www.php.net/parse_str parse_str()} and stripslashes if
 * {@link http://www.php.net/magic_quotes magic_quotes_gpc} is on.
 *
 * @since 2.2.1
 *
 * @param string $string The string to be parsed.
 * @param array $array Variables will be stored in this array.
 */
function xapp_parse_str( $string, &$array ) {
	parse_str( $string, $array );
	if ( get_magic_quotes_gpc() )
		$array = stripslashes_deep( $array );
}

/**
 * Returns the initialized XAppHttp Object
 *
 * @since 2.7.0
 * @access private
 *
 * @return XAppHttp HTTP Transport object.
 */
function _xapp_http_get_object() {
	static $http;

	if ( is_null($http) )
		$http = new XAppHttp();

	return $http;
}
/**
 * Retrieve the raw response from a safe HTTP request using the GET method.
 *
 * This function is ideal when the HTTP request is being made to an arbitrary
 * URL. The URL is validated to avoid redirection and request forgery attacks.
 *
 * @since 3.6.0
 *
 * @see xapp_remote_request() For more information on the response array format.
 * @see XAppHttp::request() For default arguments information.
 *
 * @param string $url  Site URL to retrieve.
 * @param array  $args Optional. Request arguments. Default empty array.
 * @return Error|array The response or Error on failure.
 */
function xapp_safe_remote_get( $url, $args = array() ) {
	$args['reject_unsafe_urls'] = true;
	$http = _xapp_http_get_object();
	//return $http->get( $url, $args );
}
/**
 * Returns a filename of a Temporary unique file.
 * Please note that the calling function must unlink() this itself.
 *
 * The filename is based off the passed parameter or defaults to the current unix timestamp,
 * while the directory can either be passed as well, or by leaving it blank, default to a writable temporary directory.
 *
 * @since 2.6.0
 *
 * @param string $filename (optional) Filename to base the Unique file off
 * @param string $dir (optional) Directory to store the file in
 * @return string a writable filename
 */
function xapp_tempnam($filename = '', $dir = '') {
	if ( empty($dir) )
		$dir = get_temp_dir();
	$filename = basename($filename);
	if ( empty($filename) )
		$filename = time();

	$filename = preg_replace('|\..*$|', '.tmp', $filename);
	$filename = $dir . xapp_unique_filename($dir, $filename);
	touch($filename);
	return $filename;
}
/**
 * Check whether variable is a WordPress Error.
 *
 * Returns true if $thing is an object of the XAppError class.
 *
 * @since 2.1.0
 *
 * @param mixed $thing Check if unknown variable is a XAppError object.
 * @return bool True, if XAppError. False, if not XAppError.
 */
function is_xapp_error($thing) {
	if ( is_object($thing) && is_a($thing, 'XAppError') )
		return true;
	return false;
}
/**
 * Retrieve only the response code from the raw response.
 *
 * Will return an empty array if incorrect parameter value is given.
 *
 * @since 2.7.0
 *
 * @param array $response HTTP response.
 * @return string the response code. Empty string on incorrect parameter given.
 */
function xapp_remote_retrieve_response_code( $response ) {
	if ( is_xapp_error($response) || ! isset($response['response']) || ! is_array($response['response']))
		return '';

	return $response['response']['code'];
}
/**
 * Retrieve only the response message from the raw response.
 *
 * Will return an empty array if incorrect parameter value is given.
 *
 * @since 2.7.0
 *
 * @param array $response HTTP response.
 * @return string The response message. Empty string on incorrect parameter given.
 */
function xapp_remote_retrieve_response_message( $response ) {
	if ( is_xapp_error($response) || ! isset($response['response']) || ! is_array($response['response']))
		return '';

	return $response['response']['message'];
}

/**
 * Retrieve a single header by name from the raw response.
 *
 * @since 2.7.0
 *
 * @param array $response
 * @param string $header Header name to retrieve value from.
 * @return string The header value. Empty string on if incorrect parameter given, or if the header doesn't exist.
 */
function xapp_remote_retrieve_header( $response, $header ) {
	if ( is_xapp_error($response) || ! isset($response['headers']) || ! is_array($response['headers']))
		return '';

	if ( array_key_exists($header, $response['headers']) )
		return $response['headers'][$header];

	return '';
}
/**
 * Calculates and compares the MD5 of a file to its expected value.
 *
 * @since 3.7.0
 *
 * @param string $filename The filename to check the MD5 of.
 * @param string $expected_md5 The expected MD5 of the file, either a base64 encoded raw md5, or a hex-encoded md5
 * @return bool|object XAppError on failure, true on success, false when the MD5 format is unknown/unexpected
 */
function verify_file_md5( $filename, $expected_md5 ) {
	if ( 32 == strlen( $expected_md5 ) )
		$expected_raw_md5 = pack( 'H*', $expected_md5 );
	elseif ( 24 == strlen( $expected_md5 ) )
		$expected_raw_md5 = base64_decode( $expected_md5 );
	else
		return false; // unknown format

	$file_md5 = md5_file( $filename, true );

	if ( $file_md5 === $expected_raw_md5 )
		return true;

	return new XApp_Error( 'md5_mismatch', sprintf( ( 'The checksum of the file (%1$s) does not match the expected checksum value (%2$s).' ), bin2hex( $file_md5 ), bin2hex( $expected_raw_md5 ) ) );
}
/**
 * Downloads a url to a local temporary file using the WordPress HTTP Class.
 * Please note, That the calling function must unlink() the file.
 *
 * @since 2.5.0
 *
 * @param string $url the URL of the file to download
 * @param int $timeout The timeout for the request to download the file default 300 seconds
 * @return mixed Error on failure, string Filename on success.
 */
function download_url( $url, $timeout = 300 ) {
	//WARNING: The file is not automatically deleted, The script must unlink() the file.
	if ( ! $url )
		return new XApp_Error('http_no_url', ('Invalid URL Provided.'));

	$tmpfname = xapp_tempnam($url);
	if ( ! $tmpfname )
		return new XApp_Error('http_no_file', ('Could not create Temporary file.'));

	ob_start();
	echo('downloading ' . $url . ' to ' . $tmpfname . '......please wait! <br/>');
	ob_flush();
	flush();
	sleep(1);

	
	$http = new XAppHttp();
	$response = $http->request( $url, array( 'timeout' => $timeout, 'stream' => true, 'filename' => $tmpfname ) );

	if ( is_xapp_error( $response ) ) {
		unlink( $tmpfname );
		return $response;
	}

	if ( 200 != xapp_remote_retrieve_response_code( $response ) ){
		unlink( $tmpfname );
		return new XApp_Error( 'http_404', trim( xapp_remote_retrieve_response_message( $response ) ) );
	}

	$content_md5 = xapp_remote_retrieve_header( $response, 'content-md5' );
	if ( $content_md5 ) {
		$md5_check = verify_file_md5( $tmpfname, $content_md5 );
		if ( is_xapp_error( $md5_check ) ) {
			unlink( $tmpfname );
			return $md5_check;
		}
	}

	//echo "Done";
	//ob_flush();
	//flush();


	return $tmpfname;
}

/**
 * This function should not be called directly, use unzip_file instead. Attempts to unzip an archive using the ZipArchive class.
 * Assumes that WP_Filesystem() has already been called and set up.
 *
 * @since 3.0.0
 * @see unzip_file
 * @access private
 *
 * @param string $file Full path and filename of zip archive
 * @param string $to Full path on the filesystem to extract archive to
 * @param array $needed_dirs A partial list of required folders needed to be created.
 * @return mixed Error on failure, True on success
 */
function _unzip_file_ziparchive($file, $to, $needed_dirs = array() ) {

	$z = new ZipArchive();

	$zopen = $z->open( $file, ZIPARCHIVE::CHECKCONS );
	if ( true !== $zopen )
		return new XApp_Error( 'incompatible_archive', ( 'Incompatible Archive.' ), array( 'ziparchive_error' => $zopen ) );

	$uncompressed_size = 0;

	for ( $i = 0; $i < $z->numFiles; $i++ ) {
		if ( ! $info = $z->statIndex($i) )
			return new XApp_Error( 'stat_failed_ziparchive', ( 'Could not retrieve file from archive.' ) );

		if ( '__MACOSX/' === substr($info['name'], 0, 9) ) // Skip the OS X-created __MACOSX directory
			continue;

		$uncompressed_size += $info['size'];

		if ( '/' == substr($info['name'], -1) ) // directory
			$needed_dirs[] = $to . untrailingslashit($info['name']);
		else
			$needed_dirs[] = $to . untrailingslashit(dirname($info['name']));
	}

	$needed_dirs = array_unique($needed_dirs);
	foreach ( $needed_dirs as $dir ) {
		// Check the parent folders of the folders all exist within the creation array.
		if ( untrailingslashit($to) == $dir ) // Skip over the working directory, We know this exists (or will exist)
			continue;
		if ( strpos($dir, $to) === false ) // If the directory is not within the working directory, Skip it
			continue;

		$parent_folder = dirname($dir);
		while ( !empty($parent_folder) && untrailingslashit($to) != $parent_folder && !in_array($parent_folder, $needed_dirs) ) {
			$needed_dirs[] = $parent_folder;
			$parent_folder = dirname($parent_folder);
		}
	}
	asort($needed_dirs);

	//error_log('needed dirs ' . json_encode($needed_dirs));

	// Create those directories if need be:
	foreach ( $needed_dirs as $_dir ) {
		if (!file_exists($_dir) && !mkdir($_dir, FS_CHMOD_DIR) && ! is_dir($_dir) ) { // Only check to see if the Dir exists upon creation failure. Less I/O this way.
			//error_log('mkdir_failed_ziparchive', ('Could not create directory.'), substr($_dir, strlen($to)));
			//return new XApp_Error('mkdir_failed_ziparchive', ('Could not create directory.'), substr($_dir, strlen($to)));
		}
	}
	unset($needed_dirs);

	for ( $i = 0; $i < $z->numFiles; $i++ ) {
		if ( ! $info = $z->statIndex($i) )
			return new XApp_Error( 'stat_failed_ziparchive', ( 'Could not retrieve file from archive.' ) );

		if ( '/' == substr($info['name'], -1) ) // directory
			continue;

		if ( '__MACOSX/' === substr($info['name'], 0, 9) ) // Don't extract the OS X-created __MACOSX directory files
			continue;

		$contents = $z->getFromIndex($i);
		if ( false === $contents ) {
			//echo('extract_failed_ziparchive' .  ('Could not extract file from archive.') .  $info['name']);
		}

		if ( ! file_put_contents( $to . $info['name'], $contents, FS_CHMOD_FILE) ) {
			//echo('copy_failed_ziparchive' . ('Could not copy file.') .  $info['name']);
		}
	}

	$z->close();

	return true;
}

/**
 * This function should not be called directly, use unzip_file instead. Attempts to unzip an archive using the PclZip library.
 * Assumes that WP_Filesystem() has already been called and set up.
 *
 * @since 3.0.0
 * @see unzip_file
 * @access private
 *
 * @param string $file Full path and filename of zip archive
 * @param string $to Full path on the filesystem to extract archive to
 * @param array $needed_dirs A partial list of required folders needed to be created.
 * @return mixed XApp_Error on failure, True on success
 */
function _unzip_file_pclzip($file, $to, $needed_dirs = array()) {


	mbstring_binary_safe_encoding();

	require_once(ROOT_PATH . 'wp-admin/includes/class-pclzip.php');

	$archive = new PclZip($file);

	$archive_files = $archive->extract(PCLZIP_OPT_EXTRACT_AS_STRING);

	reset_mbstring_encoding();

	// Is the archive valid?
	if ( !is_array($archive_files) )
		return new XApp_Error('incompatible_archive', __('Incompatible Archive.'), $archive->errorInfo(true));

	if ( 0 == count($archive_files) )
		return new XApp_Error( 'empty_archive_pclzip', __( 'Empty archive.' ) );

	$uncompressed_size = 0;

	// Determine any children directories needed (From within the archive)
	foreach ( $archive_files as $file ) {
		if ( '__MACOSX/' === substr($file['filename'], 0, 9) ) // Skip the OS X-created __MACOSX directory
			continue;

		$uncompressed_size += $file['size'];

		$needed_dirs[] = $to . untrailingslashit( $file['folder'] ? $file['filename'] : dirname($file['filename']) );
	}

	/*
	 * disk_free_space() could return false. Assume that any falsey value is an error.
	 * A disk that has zero free bytes has bigger problems.
	 * Require we have enough space to unzip the file and copy its contents, with a 10% buffer.
	 */
	$needed_dirs = array_unique($needed_dirs);
	foreach ( $needed_dirs as $dir ) {
		// Check the parent folders of the folders all exist within the creation array.
		if ( untrailingslashit($to) == $dir ) // Skip over the working directory, We know this exists (or will exist)
			continue;
		if ( strpos($dir, $to) === false ) // If the directory is not within the working directory, Skip it
			continue;

		$parent_folder = dirname($dir);
		while ( !empty($parent_folder) && untrailingslashit($to) != $parent_folder && !in_array($parent_folder, $needed_dirs) ) {
			$needed_dirs[] = $parent_folder;
			$parent_folder = dirname($parent_folder);
		}
	}
	asort($needed_dirs);

	// Create those directories if need be:
	foreach ( $needed_dirs as $_dir ) {
		// Only check to see if the dir exists upon creation failure. Less I/O this way.
		if ( ! mkdir( $_dir, FS_CHMOD_DIR ) && ! is_dir( $_dir ) )
			return new XApp_Error( 'mkdir_failed_pclzip', __( 'Could not create directory.' ), substr( $_dir, strlen( $to ) ) );
	}
	unset($needed_dirs);

	// Extract the files from the zip
	foreach ( $archive_files as $file ) {
		if ( $file['folder'] )
			continue;

		if ( '__MACOSX/' === substr($file['filename'], 0, 9) ) // Don't extract the OS X-created __MACOSX directory files
			continue;

		if ( ! file_put_contents( $to . $file['filename'], $file['content'], FS_CHMOD_FILE) )
			return new XApp_Error( 'copy_failed_pclzip', __( 'Could not copy file.' ), $file['filename'] );
	}
	return true;
}

function unzip_file($file, $to) {

	echo('unzip ' . $file . ' to ' . $to . '<br/>');
	// Unzip can use a lot of memory, but not this much hopefully
	set_time_limit(0);
	@ini_set( 'memory_limit', '128M' );

	$needed_dirs = array();
	$to = trailingslashit($to);

	/**
	 * Filter whether to use ZipArchive to unzip archives.
	 * @param bool $ziparchive Whether to use ZipArchive. Default true.
	 */
	if ( class_exists( 'ZipArchive' )) {
		$result = _unzip_file_ziparchive($file, $to, $needed_dirs);
		if ( true === $result ) {
			echo('unzip success' . '<br/>');
			return $result;
		} elseif ( is_xapp_error($result) ) {
			if ( 'incompatible_archive' != $result->get_error_code() )
				return $result;
		}
	}
	// Fall through to PclZip if ZipArchive is not available, or encountered an error opening the file.
	$res = _unzip_file_pclzip($file, $to, $needed_dirs);

	echo('unzip success' . '<br/>');
	return $res;
}

$downloaded  = download_url($FULL_ZIP);
//$downloaded  = '/tmp/xbox.tmp';

if(file_exists($downloaded)){

	@mkdir($DESTINATION_DIRECTORY,0777,true);
	unzip_file($downloaded,$DESTINATION_DIRECTORY);

	echo('deleting ' . $downloaded .  '<br/>');
	@unlink($downloaded);
	if(file_exists($DESTINATION_DIRECTORY . DIRECTORY_SEPARATOR . 'index.php')){
		echo('install success! You can open it now '. '<br/>');
		sleep(5);
		if($AUTO_OPEN){
			ob_start();
			echo('redirect to <a href="' . $OPEN_URL .'">' . $OPEN_URL .'</a><br/>');
			sleep(2);
			ob_flush();
			flush();
			header("Location: " .$OPEN_URL);
			die();
		}
	}else{
		echo('install error! expected an index.php in ' . $DESTINATION_DIRECTORY . '<br/>');
	}
}else{
	echo('couldnt download ' . $FULL_ZIP .  '!<br/>');
}