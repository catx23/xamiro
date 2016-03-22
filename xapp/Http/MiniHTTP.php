<?php
/**
 * @author     Guenter Baumgart
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 * @license : http://opensource.org/licenses/BSD-3-Clause
 * @package XApp\Http
 *
 */
/**
 * HTTP request method uses Curl extension to retrieve the url.
 * Requires the Curl extension to be installed.
 */
class XApp_Http_Curl {

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

		$r =    XApp_Utils_Array::parse_args( $args, $defaults );

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
		 * @param resource &$handle The cURL handle returned by curl_init().
		 * @param array    $r       The HTTP request arguments.
		 * @param string   $url     The request URL.
		 */

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
		$theHeaders = XApp_Http::processHeaders( $this->headers, $url );
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
 * Simple HTTP class
 */
class XApp_Http{
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
			$class = 'XApp_Http_' . $transport;

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

		//if ( is_Error( $response ) )
		//	return $response;


		return $response;
	}

	public function request($url, $args = array())
	{
		xapp_import('xapp.Utils.Strings');
		xapp_import('xapp.Utils.Arrays');
		xapp_import('xapp.Commons.Error');
		xapp_import('xapp.Directory.Utils');


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
		$args = XApp_Utils_Array::parse_args($args);

		// By default, Head requests do not cause redirections.
		if (isset($args['method']) && 'HEAD' == $args['method']) {
			$defaults['redirection'] = 0;
		}

		$r = XApp_Utils_Array::parse_args($args, $defaults);


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
			$r['filename'] = XApp_Directory_Utils::get_temp_dir() . basename($url);
		}

		/*
		 * Force some settings if we are streaming to a file and check for existence and perms
		 * of destination directory.
		 */
		if ($r['stream']) {
			$r['blocking'] = true;
			if (! XApp_Directory_Utils::is_writable(dirname($r['filename']))) {
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
		XApp_Utils_Strings::mbstring_binary_safe_encoding();

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

		XApp_Utils_Strings::reset_mbstring_encoding();

		return $response;
	}
}
