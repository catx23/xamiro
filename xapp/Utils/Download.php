<?php
/**
 * @author     Guenter Baumgart
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 * @package xapp\Utils

 */
class XApp_Download
{
	/**
	 * Retrieve only the response code from the raw response.
	 *
	 * Will return an empty array if incorrect parameter value is given.
	 *
	 * @param array $response HTTP response.
	 * @return string the response code. Empty string on incorrect parameter given.
	 */
	private  static function remote_retrieve_response_code( $response ) {
		if ( XApp_Error_Base::is_error($response) || ! isset($response['response']) || ! is_array($response['response']))
			return '';

		return $response['response']['code'];
	}

	/**
	 * Retrieve only the response message from the raw response.
	 *
	 * Will return an empty array if incorrect parameter value is given.
	 *
	 * @param array $response HTTP response.
	 * @return string The response message. Empty string on incorrect parameter given.
	 */
	private static function remote_retrieve_response_message( $response ) {
		if ( XApp_Error_Base::is_error($response) || ! isset($response['response']) || ! is_array($response['response']))
			return '';

		return $response['response']['message'];
	}

	/**
	 * Retrieve a single header by name from the raw response.
	 *
	 * @param array $response
	 * @param string $header Header name to retrieve value from.
	 * @return string The header value. Empty string on if incorrect parameter given, or if the header doesn't exist.
	 */
	private static function remote_retrieve_header( $response, $header ) {
		if ( XApp_Error_Base::is_error($response) || ! isset($response['headers']) || ! is_array($response['headers']))
			return '';

		if ( array_key_exists($header, $response['headers']) )
			return $response['headers'][$header];

		return '';
	}

	public static function  download($url, $timeout = 3000){


		xapp_import('xapp.Commons.Error');
		xapp_import('xapp.Utils.Strings');
		xapp_import('xapp.Http.MiniHTTP');
		xapp_import('xapp.Directory.Utils');
		xapp_import('xapp.File.Utils');

		if ( ! $url )
			return new XApp_Error('http_no_url', ('Invalid URL Provided.'));

		$tmpfname = XApp_Directory_Utils::tempname($url);
		if ( ! $tmpfname )
			return new XApp_Error_Base('http_no_file', ('Could not create Temporary file.'));

		$http = new XApp_Http();

		$response = $http->request( $url, array( 'timeout' => $timeout, 'stream' => true, 'filename' => $tmpfname ) );
		/*xapp_clog($response);*/

		if ( XApp_Error_Base::is_error( $response ) ) {
			unlink( $tmpfname );
			return $response;
		}


		/*
		if ( 200 != self::remote_retrieve_response_code( $response ) ){
			unlink( $tmpfname );
			return self::remote_retrieve_response_message( $response );
		}*/

		$content_md5 = self::remote_retrieve_header( $response, 'content-md5' );
		if ( $content_md5 ) {
			$md5_check = XApp_File_Utils::verify_file_md5( $tmpfname, $content_md5 );
			if ( XApp_Error::is_error( $md5_check ) ) {
				unlink( $tmpfname );
				return $md5_check;
			}
		}

		return $tmpfname;
	}
}

