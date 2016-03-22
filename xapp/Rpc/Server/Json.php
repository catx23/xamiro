<?php

defined('XAPP') || require_once(dirname(__FILE__) . '/../../Core/core.php');

xapp_import('xapp.Rpc.Server.Exception');
xapp_import('xapp.Rpc.Fault');
xapp_import('xapp.Rpc.Server');
xapp_import('xapp.Rpc.Smd.Json');
xapp_import('xapp.Rpc.Request.Json');
xapp_import('xapp.Rpc.Response.Json');

/**
 * Rpc server json class
 *
 * @package Rpc
 * @subpackage Rpc_Server
 * @class Xapp_Rpc_Server_Json
 * @error 146
 * @author Frank Mueller <set@cooki.me>
 */
class Xapp_Rpc_Server_Json extends Xapp_Rpc_Server
{
	/**
	 * defines what rpc json server version to use
	 *
	 * @const VERSION
	 */
	const VERSION = 'RPC_SERVER_JSON_VERSION';

	/**
	 * defines if json server will output dojo compatible response at all times
	 *
	 * @const DOJO_COMPATIBLE
	 */
	const DOJO_COMPATIBLE = 'RPC_SERVER_JSON_DOJO_COMPATIBLE';


	/**
	 * options dictionary for this class containing all data type values
	 *
	 * @var array
	 */
	public static $optionsDict = array
	(
		self::VERSION => XAPP_TYPE_STRING,
		self::DOJO_COMPATIBLE => XAPP_TYPE_BOOL
	);

	/**
	 * options mandatory map for this class contains all mandatory values
	 *
	 * @var array
	 */
	public static $optionsRule = array
	(
		self::VERSION => 1,
		self::DOJO_COMPATIBLE => 1
	);

	/**
	 * options default value array containing all class option default values
	 *
	 * @var array
	 */
	public $options = array
	(
		self::DEBUG => false,
		self::ALLOW_FUNCTIONS => false,
		self::VERSION => '2.0',
		self::DOJO_COMPATIBLE => false,
		self::OMIT_ERROR => false,
		self::SERVICE_OVER_GET => false,
		self::APPLICATION_ERROR => false,
		self::NAMESPACE_IDENTIFIER => '_',
		self::CLASS_METHOD_SEPARATOR => '.',
		self::TRANSACTION_ID_REGEX => false,
		self::CACHE => null,
		self::CACHE_SERVICES => '.*',
		self::CACHE_BY_TRANSACTION_ID => false,
		self::PARAMS_AS_ARRAY => false,
		self::COMPLY_TO_JSONRPC_1_2 => false,
		self::ALLOW_BATCHED_REQUESTS => false
	);

	/**
	 * contains singleton instance of this class
	 *
	 * @var null|Xapp_Rpc_Server
	 */
	protected static $_instance = null;

	/**
	 * fault map defaults to common error faults common to json and xml
	 * can be extended and remapped in concrete server to match fault
	 * numbers defined in specifications. all faults go through getFault method
	 * which will check for fault map and code mapping will otherwise return code
	 * unmapped
	 *
	 * @var array
	 */
	public $faultMap = array
	(
		-32500 => -32000,
		-32600 => -32600,
		-32601 => -32601,
		-32602 => -32602,
		-32603 => -32603,
		-32700 => -32700
	);

	/**
	 * json rpc error code to http (error) status code default mapping according to json rpc 1.2 specs
	 *
	 * @var array
	 */
	public $codeMap = array
	(
		-32700 => 500,
		-32600 => 400,
		-32601 => 404,
		-32602 => 500,
		-32603 => 500
	);


	/**
	 * class constructor will set missing instances of smd, request and response and
	 * call parent constructor for class initialization
	 *
	 * @error 14601
	 * @param null|array|object $options expects optional options
	 */
	public function __construct($options = null)
	{
		xapp_set_options($options, $this);
		if (!xapp_is_option(self::SMD, $this)) {
			xapp_set_option(self::SMD, Xapp_Rpc_Smd_Json::instance(), $this);
		}
		if (!xapp_is_option(self::REQUEST, $this)) {
			xapp_set_option(self::REQUEST, new Xapp_Rpc_Request_Json(), $this);
		}
		if (!xapp_is_option(self::RESPONSE, $this)) {
			xapp_set_option(self::RESPONSE, new Xapp_Rpc_Response_Json(), $this);
		}
		parent::__construct();
	}

	/**
	 * Parses a string into variables to be stored in an array.
	 *
	 * Uses {@link http://www.php.net/parse_str parse_str()} and stripslashes if
	 * {@link http://www.php.net/magic_quotes magic_quotes_gpc} is on.
	 *
	 * @param string $string The string to be parsed.
	 * @param array $array Variables will be stored in this array.
	 */
	public static function parse_str($string, &$array)
	{
		parse_str($string, $array);
		if (get_magic_quotes_gpc()) {
			$array = self::stripslashes_deep($array);
		}
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
	public static function stripslashes_deep($value)
	{
		if (is_array($value)) {
			$value = array_map('stripslashes_deep', $value);
		} elseif (is_object($value)) {
			$vars = get_object_vars($value);
			foreach ($vars as $key => $data) {
				$value->{$key} = self::stripslashes_deep($data);
			}
		} elseif (is_string($value)) {
			$value = stripslashes($value);
		}

		return $value;
	}

	/**
	 * creates and returns singleton instance of class
	 *
	 * @error 14602
	 * @param null|array|object $options expects optional options
	 * @return Xapp_Rpc_Server_Json
	 */
	public static function instance($options = null)
	{
		if (self::$_instance === null) {
			self::$_instance = new self($options);
		}
		return self::$_instance;
	}

	/**
	 * Case for file upload to RPC method.
	 */
	public function initWithUpload()
	{

		//re-construct std rpc batch from post url

		$urlParams = array();

		self::parse_str($_SERVER["QUERY_STRING"], $urlParams);

		if (!isset($urlParams['service'])) {
			return;
		}

		$call = explode(
			xapp_get_option(self::CLASS_METHOD_SEPARATOR, $this),
			trim($urlParams['service'], ' ' . xapp_get_option(self::CLASS_METHOD_SEPARATOR, $this))
		);

		$this->_calls[] = array(
			$this->_services[] = $call[0] . xapp_get_option(self::CLASS_METHOD_SEPARATOR, $this) . $call[1],
			$call[1],
			$call[0],
			array(
				'id' => 0,
				'method' => $call[0] . xapp_get_option(self::CLASS_METHOD_SEPARATOR, $this) . $call[1],
				'params' => $urlParams,
				'jsonrpc' => '2.0'
			),
			0
		);

		$this->_services = array($urlParams['service']);

		$response = $this->response();
		if (xapp_has_option(self::DOJO_COMPATIBLE, $this)) {
			xapp_set_option(
				Xapp_Rpc_Response_Json::DOJO_COMPATIBLE,
				xapp_get_option(self::DOJO_COMPATIBLE, $this),
				$response
			);
		}
	}

	/**
	 * init server by checking for json object and method parameter or service $_GET parameter
	 * to extract class and method or function to invoke from request. throw fault if server does
	 * not allow functions just class methods. pass class options to response and store parameters
	 * from json request object in param variable
	 *
	 * @error 14603
	 * @return void
	 * @throws Xapp_Rpc_Fault
	 */
	protected function init()
	{

		$params = $this->request()->getParams();

		$response = $this->response();

		if (!empty($this->_service)) {
			$service = trim($this->_service, ' ._/');
		} else {
			$service = null;
		}
		if (is_array($params) && !array_key_exists(0, $params)) {
			$params = array($params);
		}

		if (count($_FILES)) {
			$this->initWithUpload();
			return;
		}

		foreach ((array)$params as $p) {

			if (is_array($p) && array_key_exists('method', $p) && !empty($p['method'])) {
				$type = (array_key_exists('id', $p)) ? self::CALL : self::NOTIFICATION;
				if (strpos($p['method'], xapp_get_option(self::CLASS_METHOD_SEPARATOR, $this)) !== false) {
					$call = explode(
						xapp_get_option(self::CLASS_METHOD_SEPARATOR, $this),
						trim($p['method'], ' ' . xapp_get_option(self::CLASS_METHOD_SEPARATOR, $this))
					);
					$this->_calls[] = array
					(
						$this->_services[] = $call[0] . xapp_get_option(self::CLASS_METHOD_SEPARATOR, $this) . $call[1],
						$call[1],
						$call[0],
						$p,
						$type
					);

				} else {
					if (!is_null($service)) {

						$this->_calls[] = array
						(
							$this->_services[] = $service . xapp_get_option(
									self::CLASS_METHOD_SEPARATOR,
									$this
								) . $p['method'],
							$p['method'],
							$service,
							$p,
							$type
						);

					} else {

						$this->_calls[] = array
						(
							$this->_services[] = $p['method'],
							$p['method'],
							null,
							$p,
							$type
						);
					}
				}
			}
		}

		if (empty($this->_calls) && !is_null($service)) {
			$type = (array_key_exists('id', $params[0])) ? self::CALL : self::NOTIFICATION;
			if (strpos($service, xapp_get_option(self::CLASS_METHOD_SEPARATOR, $this)) !== false) {
				$call = explode(
					xapp_get_option(self::CLASS_METHOD_SEPARATOR, $this),
					trim($service, ' ' . xapp_get_option(self::CLASS_METHOD_SEPARATOR, $this))
				);
				$this->_calls[] = array
				(
					$this->_services[] = $call[0] . xapp_get_option(self::CLASS_METHOD_SEPARATOR, $this) . $call[1],
					$call[1],
					$call[0],
					$params[0],
					$type
				);
			} else {
				$this->_calls[] = array
				(
					$this->_services[] = $service,
					$service,
					null,
					$params[0],
					$type
				);
			}
		}
		if (xapp_has_option(self::DOJO_COMPATIBLE, $this)) {
			xapp_set_option(
				Xapp_Rpc_Response_Json::DOJO_COMPATIBLE,
				xapp_get_option(self::DOJO_COMPATIBLE, $this),
				$response
			);
		}

		$response = null;
		$params = null;
	}


	/**
	 * validate json request object testing all request object parameters for validity. also checking all additional
	 * parameters for validity and throwing fault if necessary
	 *
	 * @error 14604
	 * @param array $call expects the call to validate
	 * @return void
	 * @throws Xapp_Rpc_Fault
	 */
	protected function validate($call)
	{
		if (!xapp_get_option(self::VALIDATE, $this)) {

			return;
		}
		if ($this->request()->isPost()) {
			if ($this->request()->getRaw() === "") {
				Xapp_Rpc_Fault::t("empty or invalid request object", array(1460401, -32600));
			}
			if ($this->request()->getVersion($call[3]) != xapp_get_option(self::VERSION, $this)) {
				Xapp_Rpc_Fault::t("rpc version not set or version miss match", array(1460402, -32013));
			}
			if (!xapp_get_option(self::ALLOW_NOTIFICATIONS, $this) || array_key_exists('id', $call[3])) {
				if (!array_key_exists('id', $call[3])) {
					Xapp_Rpc_Fault::t("rpc transaction id must be set", array(1460405, -32015));
				}
				if (!is_numeric($call[3]['id']) && !is_string($call[3]['id'])) {
					Xapp_Rpc_Fault::t("rpc transaction id must be string or integer", array(1460406, -32016));
				}
				if (xapp_is_option(self::TRANSACTION_ID_REGEX, $this) && !preg_match(
						trim((string)xapp_get_option(self::TRANSACTION_ID_REGEX, $this)),
						$call[3]['id']
					)
				) {
					Xapp_Rpc_Fault::t(
						"rpc transaction id does not match transaction id regex pattern",
						array(1460411, -32017)
					);
				}
			}
			if (!xapp_get_option(self::ALLOW_FUNCTIONS, $this) && empty($call[2])) {
				Xapp_Rpc_Fault::t("php functions as service are not supported by this rpc service", 1460412, -32018);
			}
			if (!array_key_exists('method', $call[3])) {
				Xapp_Rpc_Fault::t("rpc method must be set", array(1460403, -32014));
			}
			if (!$this->smd()->has($call[2], $call[1])) {
				Xapp_Rpc_Fault::t("method or function is not registered as service", array(1460404, -32601));
			}
			if (!is_null($call[2])) {
				$service = $this->smd()->get($call[2] . '.' . $call[1]);
			} else {
				$service = $this->smd()->get($call[1]);
			}
			if (!empty($service->parameters)) {
				$params = (array_key_exists('params', $call[3])) ? $call[3]['params'] : null;
				$p = (array)$params;
				$k = (is_null($params) || (array_values($p) !== $p)) ? 'n' : 'i';
				$i = 0;
				foreach ($service->parameters as $v) {
					$n = $v->name;
					if (!$v->optional && (!array_key_exists($$k, $p) || !xapp_is_value($p[$$k]))) {
						Xapp_Rpc_Fault::t(xapp_sprintf("param: %s must be set", array($$k)), array(1460407, -32602));
					}
					if (isset($v->type) && array_key_exists($$k, $p) && !in_array(
							'mixed',
							(array)$v->type
						) && !in_array(xapp_type($p[$$k]), (array)$v->type)
					) {
						Xapp_Rpc_Fault::t(
							xapp_sprintf(
								"param: %s must be of the following types: %s",
								array($$k, implode('|', (array)$v->type))
							),
							array(1460408, -32602)
						);
					}
					$i++;
				}
			}
			if (xapp_is_option(self::ADDITIONAL_PARAMETERS, $this)) {
				foreach (xapp_get_option(self::ADDITIONAL_PARAMETERS, $this) as $k => $v) {
					$type = (isset($v[0])) ? (array)$v[0] : false;
					$optional = (isset($v[1])) ? (bool)$v[1] : true;
					if (!$optional && !$this->request()->hasParam($k)) {
						Xapp_Rpc_Fault::t(
							xapp_sprintf("additional param: %s must be set", array($k)),
							array(1460409, -32602)
						);
					}
					if ($type && !in_array('mixed', $type) && !in_array(
							xapp_type($this->request()->getParam($k)),
							$type
						)
					) {
						Xapp_Rpc_Fault::t(
							xapp_sprintf(
								"additional param: %s must be of the following types: %s",
								array($k, implode('|', $type))
							),
							array(1460410, -32602)
						);
					}
				}
			}
		}
	}


	/**
	 * executing requested service/call by validating call and invoking requested class/method or function
	 *
	 * @error 14605
	 * @param array $call expects the call to execute
	 * @return void
	 * @throws Exception
	 */
	protected function execute($call)
	{
		$data = array();
		$version = $this->request()->getVersion($call[3], true);
		if (!is_null($version)) {
			$data[$version[0]] = $version[1];
		}
		try {
			$this->validate($call);
			$data['result'] = $this->invoke(
				$call,
				((array_key_exists('params', $call[3])) ? $call[3]['params'] : null)
			);
			if (version_compare((string)xapp_get_option(self::VERSION), '2.0', '<')) {
				$data['error'] = null;
			}
		} catch (Exception $e) {
			if (xapp_get_option(self::COMPLY_TO_JSONRPC_1_2, $this)) {
				if (array_key_exists($e->getCode(), $this->codeMap)) {
					xapp_set_option(Xapp_Rpc_Response::STATUS_CODE, $this->codeMap[$e->getCode()], $response);
				} else {
					xapp_set_option(Xapp_Rpc_Response::STATUS_CODE, 500, $response);
				}
			}
			if (xapp_is_option(self::EXCEPTION_CALLBACK, $this)) {
				$e = call_user_func_array(xapp_get_option(self::EXCEPTION_CALLBACK, $this), array(&$e, $this, $call));
				if ($e instanceof Exception) {
					$data['error'] = $this->compileError($e);
				} else {
					$data['error'] = $e;
				}
			} else {
				if (sizeof($this->_calls) === 1) {
					throw $e;
				} else {
					$data['error'] = $this->compileError($e);
				}
			}
		}
		if (array_key_exists('id', $call[3])) {
			$data['id'] = $call[3]['id'];
		}
		$this->_data[] = $data;
		$data = null;
	}


	/**
	 * compile error object to be returned
	 *
	 * @error 14609
	 * @param Exception $error
	 * @return array
	 */
	final protected function compileError(Exception $error)
	{
		$e = array();
		if ($error instanceof Xapp_Rpc_Fault) {
			$e['code'] = $this->getFault($error->getCode());
		} else {
			$e['code'] = $error->getCode();
		}
		if (isset($GLOBALS['_RPC']) && isset($GLOBALS['_RPC']['OMIT_ERROR']) && (bool)$GLOBALS['_RPC']['OMIT_ERROR']) {
			$e['message'] = null;
		} else {
			$e['message'] = $error->getMessage();
			if (($error instanceof Xapp_Rpc_Fault) && $error->hasData()) {
				$e['data'] = $error->getData();
			}
		}
		return $e;
	}


	/**
	 * flush response
	 *
	 * @error 14606
	 * @return mixed|void
	 */
	protected function flush()
	{
		if (sizeof($this->_data) === 1) {
			if (array_key_exists('error', current($this->_data)) || array_key_exists('id', current($this->_data))) {
				$this->response()->data(current($this->_data));
			}
		} else {
			foreach ($this->_data as $k => $v) {
				if (array_key_exists('error', $v) || array_key_exists('id', $v)) {
					$this->response()->set($k, $v);
				}
			}
		}
		$this->response()->flush();
	}


	/**
	 * shutdown server
	 *
	 * @error 14607
	 * @return mixed|void
	 */
	protected function shutdown()
	{

	}


	/**
	 * handle exception by constructing json error object, setting it to response and instantly
	 * flushing the error to output. if omit option is found in global rpc array the error message
	 * will be omitted
	 *
	 * @error 14608
	 * @param Exception $error expects instance of Exception
	 * @return void
	 */
	public function error(Exception $error)
	{
		if (xapp_get_option(self::COMPLY_TO_JSONRPC_1_2, $this)) {
			if (array_key_exists($error->getCode(), $this->codeMap)) {
				xapp_set_option(Xapp_Rpc_Response::STATUS_CODE, $this->codeMap[$error->getCode()], $response);
			} else {
				xapp_set_option(Xapp_Rpc_Response::STATUS_CODE, 500, $response);
			}
		}
		$this->response()->setVersion(xapp_get_option(self::VERSION, $this));
		$this->response()->set('error', $this->compileError($error));
		if ($this->request()->has('id')) {
			$this->response()->set('id', $this->request()->get('id'));
		}
		$this->response()->body($this->response()->data());
		$this->response()->flush();
	}
}