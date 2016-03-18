<?php

/**
 * Class Xapp_Rpc_Gateway_Options holds only constants
 */
class Xapp_Rpc_Gateway_Options{
	/**
	 * defines whether readable error messages are omitted in error object or not
	 *
	 * @const OMIT_ERROR
	 */
	const OMIT_ERROR = 'RPC_GATEWAY_OMIT_ERROR';

	/**
	 * array of ip´s that are allowed to request denying all others
	 *
	 * @const ALLOW_IP
	 */
	const ALLOW_IP = 'RPC_GATEWAY_ALLOW_IP';

	/**
	 * array of ip´s that are always blocked from service
	 *
	 * @const DENY_IP
	 */
	const DENY_IP = 'RPC_GATEWAY_DENY_IP';

	/**
	 * array of username and password set as array key 0 and 1 to activate basic auth
	 *
	 * @const BASIC_AUTH
	 */
	const BASIC_AUTH = 'RPC_GATEWAY_BASIC_AUTH';

	/**
	 * disable gateway itself not servicing any requests
	 *
	 * @const DISABLE
	 */
	const DISABLE = 'RPC_GATEWAY_DISABLE';

	/**
	 * array of services to disable. array must contain either full service name
	 * or valid preg regex pattern without pattern delimiters. the regex string will
	 * be placed inside the pattern like: '=^(' . implode('|', $services) . ')=i';
	 *
	 * @const DISABLE_SERVICE
	 */
	const DISABLE_SERVICE = 'RPC_GATEWAY_DISABLE_SERVICE';

	/**
	 * array of host names, without scheme, to allow and block all others. the host
	 * name must be the same that will be found in request headers like foo.com
	 *
	 * @const ALLOW_HOST
	 */
	const ALLOW_HOST = 'RPC_GATEWAY_ALLOW_HOST';

	/**
	 * array of host names to always block from service. host name must be without
	 * scheme, e.g. foo.com
	 *
	 * @const DENY_HOST
	 */
	const DENY_HOST = 'RPC_GATEWAY_DENY_HOST';

	/**
	 * boolean value to define whether to deny service when not called through HTTPS
	 *
	 * @const FORCE_HTTPS
	 */
	const FORCE_HTTPS = 'RPC_GATEWAY_FORCE_HTTPS';

	/**
	 * array of user agents to allow service to an block all others. values must be
	 * regex conform expressions or simple values. if you want to make sure you want
	 * to block exact name pass as ^name$ or use wildcard patterns .* or value as it
	 * is which will equal to a /value/i regex expression. NOTE: if you use plain
	 * value like google for example all other agent names like googlebot,
	 * googlesearch, .. are also blocked
	 *
	 * @const ALLOW_USER_AGENT
	 */
	const ALLOW_USER_AGENT = 'RPC_GATEWAY_ALLOW_USER_AGENT';

	/**
	 * array of user agents to always deny service of - see explanations for
	 * ALLOW_USER_AGENT
	 *
	 * @const DENY_USER_AGENT
	 */
	const DENY_USER_AGENT = 'RPC_GATEWAY_DENY_USER_AGENT';

	/**
	 * array of refereres to allow and deny all others. values must be
	 * regex conform expressions or simple values. if you want to make sure you want
	 * to block exact name pass as ^name$ or use wildcard patterns .* or value as it
	 * is which will equal to a /value/i regex expression.
	 *
	 * @const ALLOW_REFERER
	 */
	const ALLOW_REFERER = 'RPC_GATEWAY_ALLOW_REFERER';

	/**
	 * activate signed requests expecting signature hash in request which. the signature parameter must always
	 * be set/placed in first level of request parameter array
	 *
	 * @const SIGNED_REQUEST
	 */
	const SIGNED_REQUEST = 'RPC_GATEWAY_SIGNED_REQUEST';

	/**
	 * defines the signed request method to get user identifier from the request object. allowed values are:
	 * - host = will check for host/server name from server header object
	 * - ip = will check for client ip value from server header object
	 * - user = will check for user parameter value in request post object - see SIGNED_REQUEST_USER_PARAM
	 *
	 * @const SIGNED_REQUEST_METHOD
	 */
	const SIGNED_REQUEST_METHOD = 'RPC_GATEWAY_SIGNED_REQUEST_METHOD';

	/**
	 * set optional array of preg regex patterns services to exclude from being only signed request services.
	 * the regex pattern should be valid an not contain pattern delimiters. the regex string will be placed inside
	 * the pattern like: '=^(' . implode('|', $services) . ')=i';
	 *
	 * @const SIGNED_REQUEST_EXCLUDES
	 */
	const SIGNED_REQUEST_EXCLUDES = 'RPC_GATEWAY_SIGNED_REQUEST_EXCLUDES';

	/**
	 * set parameter name for signed request user identification - the user name or id
	 * that is necessary to retrieve api or service key for
	 *
	 * @const SIGNED_REQUEST_USER_PARAM
	 */
	const SIGNED_REQUEST_USER_PARAM = 'RPC_GATEWAY_SIGNED_REQUEST_USER_PARAM';

	/**
	 * set parameter name for signed request signature parameter - the parameter
	 * where the signature for the request is to be found
	 *
	 * @const SIGNED_REQUEST_SIGN_PARAM
	 */
	const SIGNED_REQUEST_SIGN_PARAM = 'RPC_GATEWAY_SIGNED_REQUEST_SIGN_PARAM';

	/**
	 * define you own callback to validate signed request by receiving data and api/gateway
	 * key. the callback function must return boolean value. the callback function will receive
	 * 3 parameters = 1) request object, 2) the get/post parameter merged 3) the api key if set in gateway instance, if not
	 * must be retrieved manually from where ever it is stored.
	 *
	 * <code>
	 *  function myCallback($request, $params, $key = null)
	 *  {
	 *      return true;
	 *  }
	 * </code>
	 *
	 * @const SIGNED_REQUEST_CALLBACK
	 */
	const SIGNED_REQUEST_CALLBACK = 'RPC_GATEWAY_SIGNED_REQUEST_CALLBACK';

	/**
	 * secret salt for creating api keys
	 */
	const SALT_KEY                = 'RPC_GATEWAY_SALT';
}

/**
 * Default Gateway options
 */
$XAPP_GATEWAY_OPTIONS = array(
	Xapp_Rpc_Gateway_Options::SIGNED_REQUEST                =>  true,
	Xapp_Rpc_Gateway_Options::OMIT_ERROR                    =>  true,
	Xapp_Rpc_Gateway_Options::SIGNED_REQUEST_METHOD         =>  'user',
	Xapp_Rpc_Gateway_Options::SIGNED_REQUEST_SIGN_PARAM     =>  'sig',
	Xapp_Rpc_Gateway_Options::SALT_KEY                      =>  'k?Ur$0aE#9j1+7ui'
);

/*******************************************************************************
 *
 *	Example : Load security config from Ini - File:
**/

/*
xapp_import('xapp.Config.Ini');
$iniFilePath = realpath($ROOT_DIRECTORY_ABSOLUTE .'/profiles/default/security.ini');
$_XAPP_GATEWAY_OPTIONS_INI_CONF = new Xapp_Config_Ini($iniFilePath);//will throw an error if it doesnt exists
$_XAPP_GATEWAY_OPTIONS_INI_CONF->load($iniFilePath);

//example  read a parameter in the ini file:
//$omit = $_XAPP_GATEWAY_OPTIONS_INI_CONF->get(Xapp_Rpc_Gateway_Options::OMIT_ERROR);

//example  in case the parameter is in a ini Section:
//$omit = $_XAPP_GATEWAY_OPTIONS_INI_CONF->get('Default'. Xapp_Rpc_Gateway_Options::OMIT_ERROR);

// set $XAPP_GATEWAY_OPTIONS to the Config-Instance:
$XAPP_GATEWAY_OPTIONS = $_XAPP_GATEWAY_OPTIONS_INI_CONF;

*/
