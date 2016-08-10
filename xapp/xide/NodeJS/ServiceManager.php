<?php
/**
 * @version 0.1.0
 *
 * @author Luis Ramos
 * @author Guenter Baumgart
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 * @package xapp\xide\NodeJS
 */
xapp_import("xapp.Commons.Entity");
xapp_import('xapp.Utils.Strings');
xapp_import('xapp.Utils.Shell');
xapp_import('xapp.xide.Base.Manager');

/***
 * Class XIDE_NodeJS_Service_Manager provides a useful set of NodeJS related functions like :
 * -start, stop, kill and also debug (uses and starts a 'node-inspector', needs Chrome on the clients side)!
 * -enumerate running services, as well its child processes ('spawned')
 */
class XIDE_NodeJS_Service_Manager extends XIDE_Manager
{

	/////////////////////////////////////////////////////////////////////////////////////////
	//
	//  Hook/Event Keys
	//
	////////////////////////////////////////////////////////////////////////////////////////

	const EVENT_ON_NODE_META_CREATED = "NODEJS_EVENT_ON_NODE_META_CREATED";  //hook when service meta data has been created : best place to add/change
	const EVENT_ON_NODE_ADD = "NODEJS_EVENT_ON_NODE_ADD";           //hook to remove a certain node in the service list
	const EVENT_ON_NODE_ADDED = "NODEJS_EVENT_ON_NODE_ADDED";         //event when a node has beed added to final service list

	/////////////////////////////////////////////////////////////////////////////////////////
	//
	//  Constants
	//
	////////////////////////////////////////////////////////////////////////////////////////

	//a set of keys to describe a NodeJS service status
	const SERVICE_STATUS_OFFLINE = "offline";
	const SERVICE_STATUS_ONLINE = "online";
	const SERVICE_STATUS_TIMEOUT = "timeout";
	const SERVICE_STATUS_UNKNOWN = "unknown";
	//@TODO : determine other conditions like memory warnings,...

	//a set of keys to be used when gathering the NodeJS service's
	const FIELD_STATUS = 'status';
	const FIELD_INFO = 'info';
	const FIELD_CLIENTS = 'clients';
	const FIELD_CHILD_PROCESSES = 'childs'; //list child processes
	const FIELD_OPTIONS = 'options'; //get command line options of a service


	/////////////////////////////////////////////////////////////////////////////////////////
	//
	//  Options
	//
	////////////////////////////////////////////////////////////////////////////////////////

	//Debug options
	const DEBUG_TCP_PORT = "XAPP_NODEJS_DEBUG_TCP_PORT";
	const DEBUG_TCP_HOST = "XAPP_NODEJS_DEBUG_TCP_HOST";
	const DEBUG_PORT = "XAPP_NODEJS_DEBUG_PORT";
	const DEBUGGER_PATH = "XAPP_NODEJS_DEBUGGER_PATH";

	//Standard options
	const WORKING_PATH = "XAPP_NODEJS_WORKING_PATH";
	const EMITS = "XAPP_EMITS"; //disable or enable hooks
	const REWRITE_HOST = "XAPP_NODEJS_HOST_REWRITE"; //rewrite server host to this server IP
	const FORCE_HOST = "XAPP_NODEJS_FORCE_HOST"; //rewrite server host to this server IP


	/**
	 * options dictionary for this class containing all data type values
	 *
	 * @var array
	 */
	public static $optionsDict = array
	(
		self::DEBUG_TCP_PORT => XAPP_TYPE_INTEGER,
		self::DEBUG_TCP_HOST => XAPP_TYPE_STRING,
		self::DEBUG_PORT => XAPP_TYPE_INTEGER,
		self::DEBUGGER_PATH => XAPP_TYPE_STRING,
		self::WORKING_PATH => XAPP_TYPE_STRING,
		self::EMITS => XAPP_TYPE_BOOL,
		self::REWRITE_HOST => XAPP_TYPE_BOOL,
		self::FORCE_HOST => XAPP_TYPE_STRING,

		self::RESOURCES_TYPE                => XAPP_TYPE_STRING,
		self::RESOURCES_DATA                => array(XAPP_TYPE_OBJECT,XAPP_TYPE_ARRAY,XAPP_TYPE_STRING),
		self::RELATIVE_VARIABLES            => XAPP_TYPE_ARRAY,
		self::ABSOLUTE_VARIABLES            => XAPP_TYPE_ARRAY,
		self::RELATIVE_REGISTRY_NAMESPACE   => XAPP_TYPE_STRING,
		self::ABSOLUTE_REGISTRY_NAMESPACE   => XAPP_TYPE_STRING,
		self::RESOURCE_VARIABLE_DELIMITER   => XAPP_TYPE_STRING,
	);

	/**
	 * options mandatory map for this class contains all mandatory values
	 *
	 * @var array
	 */
	public static $optionsRule = array
	(
		self::EMITS => 0,
		self::DEBUG_TCP_PORT => 0,
		self::DEBUG_TCP_HOST => 0,
		self::DEBUG_PORT => 0,
		self::DEBUG_TCP_PORT => 0,
		self::WORKING_PATH => 0,
		self::REWRITE_HOST => 0,
		self::FORCE_HOST => 0,
		self::RESOURCES_DATA                => 1,
		self::RESOURCES_TYPE                => 1,
		self::RELATIVE_VARIABLES            => 0,
		self::ABSOLUTE_VARIABLES            => 0,
		self::RELATIVE_REGISTRY_NAMESPACE   => 0,
		self::ABSOLUTE_REGISTRY_NAMESPACE   => 0,
		self::RESOURCE_VARIABLE_DELIMITER   => 0
	);

	/**
	 * options default value array containing all class option default values
	 * @var array
	 */
	public $options = array
	(
		self::DEBUG_TCP_PORT => 9090,
		self::DEBUG_TCP_HOST => '0.0.0.0',
		self::DEBUG_PORT => 5858,
		self::DEBUGGER_PATH => 'nxappmain/debugger.js',
		self::WORKING_PATH => 'Utils/nodejs/',
		self::EMITS => true,
		self::REWRITE_HOST => true,
		self::FORCE_HOST => null,
		self::RESOURCES_DATA                => null,
		self::RESOURCES_TYPE                => 'unknown',
		self::RELATIVE_VARIABLES            => null,
		self::ABSOLUTE_VARIABLES            => null,
		self::RELATIVE_REGISTRY_NAMESPACE   => 'xapp_relative_ns',
		self::ABSOLUTE_REGISTRY_NAMESPACE   => 'xapp_absolute_ns',
		self::RESOURCE_VARIABLE_DELIMITER   => '%'

	);

	/////////////////////////////////////////////////////////////////////////////////////////
	//
	//  Public API & main entries, expects this class has fully valid options.
	//  This class is being mixed also with the XApp_VariableMixin which comes
	//  with its own set of options, needed to have a full service description map
	//  as well a set of resolvable variables within the service description map.
	//
	////////////////////////////////////////////////////////////////////////////////////////

	private function _serviceByName($services, $name)
	{
		foreach ($services as $service) {
			//xapp_cdump('s',$name);
			if ($service->name === $name) {
				return $service;
			}
		}
		return null;
	}

	protected function stopService($serviceResource)
	{

		if (property_exists($serviceResource, 'ps_tree') &&
			count($serviceResource->ps_tree) > 0
		) {
			$firstProcess = $serviceResource->ps_tree[0];
			$pid = $firstProcess['pid'];
			$args = array();
			$cmd = "kill " . $pid;

			XApp_Shell_Utils::run(
				$cmd,
				$args,
				null,
				Array(
					XApp_Shell_Utils::OPTION_BACKGROUND => false
				)
			);
			error_log('stopped server via ' . $cmd);
			return true;
		}else{

			error_log('couldnt stop server via ' . json_encode($serviceResource));

		}

		return false;

	}

	protected function fixWindowsPath($path)
	{
		if ($this->isWindows()) {
			return str_replace('/', '\\', $path);
		}

		return $path;
	}

	protected function isWindows()
	{
		$os = PHP_OS;
		switch ($os) {
			case "WINNT": {
				return true;
			}
		}

		return false;
	}

	protected function startService($serviceResource)
	{

		$workingPath = $serviceResource->{XAPP_RESOURCE_PATH_ABSOLUTE};
		$nodeapp = $this->fixWindowsPath($serviceResource->main);

		if ($nodeapp != '') {
			$args = array();
			$cmd = "node " . $nodeapp;
			XApp_Shell_Utils::run(
				$cmd,
				$args,
				null,
				Array(
					XApp_Shell_Utils::OPTION_WORKING_PATH => $workingPath,
					XApp_Shell_Utils::OPTION_BACKGROUND => true
				)
			);
			return true;
		} else {
			return false;
		}
	}

	public function stop($services)
	{

		if (is_string($services)) {
			$services = array($services);
		}
		$allServices = $this->ls(false);

		for ($i = 0; $i < count($services); ++$i) {
			$service = $this->_serviceByName($allServices, $services[$i]);
			if ($service) {
				$this->stopService($service);
			}

		}

	}

	/**
	 * @param $services
	 * @return bool
	 */
	public function start($services)
	{

		if (is_string($services)) {
			$services = array($services);
		}
		$allServices = $this->ls(false);
		for ($i = 0; $i < count($services); ++$i) {
			$service = $this->_serviceByName($allServices, $services[$i]);
			if ($service) {
				$this->startService($service);
			}
		}
		return true;
	}
	/***
	 * Return current request url
	 * @return string
	 */
	public static function getUrl()
	{

		$pageURL = 'http';

		if (array_key_exists("HTTPS", $_SERVER) && $_SERVER["HTTPS"] == "on") {
			$pageURL .= "s";
		}

		$pageURL .= "://";

		$SERVER_NAME = $_SERVER["SERVER_NAME"];
		if(!$SERVER_NAME && $_SERVER["REMOTE_ADDR"]){
			$SERVER_NAME = $_SERVER["REMOTE_ADDR"];
		}

		if ($_SERVER["SERVER_PORT"] != "80") {

			if(isset($_SERVER["HTTP_X_FORWARDED_HOST"])){
				$pageURL .=$_SERVER["HTTP_X_FORWARDED_HOST"];
				$pageURL .=$_SERVER["REQUEST_URI"];
			}else{
				$pageURL .= $SERVER_NAME . ":" . $_SERVER["SERVER_PORT"];
				$pageURL .=$_SERVER["REQUEST_URI"];
			}
		} else {




			if(isset($_SERVER["HTTP_X_FORWARDED_HOST"])){
				$pageURL .=$_SERVER["HTTP_X_FORWARDED_HOST"];
				$pageURL .=$_SERVER["REQUEST_URI"];
			}else{
				$pageURL .= $SERVER_NAME;// . $_SERVER["REQUEST_URI"];
				$pageURL .=$_SERVER["REQUEST_URI"];
			}
		}

		return $pageURL;
	}

	/***
	 * Method to enumerate all available NodeJS services. This function also
	 * checks the status of the service as well it does gather additional information about
	 * the service's child processes
	 * @return array|null
	 */
	public function ls($removePath = true){

		$this->prepareResources();
		$type = xo_get(self::RESOURCES_TYPE, $this);
		$services = $this->getVariableDelegate()->getResourcesByType($type);
		$emits = xo_get(self::EMITS, $this) === true;

		$result = array();//final list

		if ($services != null && count($services)) {
			foreach ($services as $service) {
				$this->completeServiceResource($service);
				if (xo_get(self::REWRITE_HOST, $this)) {
					if(property_exists($service, 'host')) {
						$host = gethostname();
						$resolved = gethostbyname($host);
						if (xo_get(self::FORCE_HOST, $this) && strlen(xo_get(self::FORCE_HOST, $this)) > 0) {
							$resolved = xo_get(self::FORCE_HOST, $this);
						}
						if ($resolved && strlen($resolved)) {
							$service->host = $resolved;
						}

						if($service->host ==='127.0.0.1' || $service->host ==='0.0.0.'){
							//$requestUrl = self::getUrl();
							$requestUrl  = XApp_Service_Entry_Utils::getUrl();
							$urlParts = parse_url($requestUrl);
							if($urlParts['host']!==$service->host){
								$service->host = $urlParts['host'];
							}
						}

					}
				}

				if ($emits && Xapp_Hook::trigger(
						self::EVENT_ON_NODE_ADD,
						array('item' => $service)
					) === false
				) {
					//skip if wanted
					continue;
				} else {
					$result[] = $service;
				}

				if ($emits) {//tell everyone
					Xapp_Hook::trigger(self::EVENT_ON_NODE_ADDED, array('item' => $service));
				}

				if ($removePath && property_exists($service, 'pathResolved')) {
					$service->pathResolved = '';
				}
			}
		}
		return $services;
	}

	/**
	 * class constructor
	 * call parent constructor for class initialization
	 *
	 * @error 14601
	 * @param null|array|object $options expects optional options
	 */
	function __construct($options = null)
	{
		parent::__construct($options);
		//standard constructor
		xapp_set_options($options, $this);
		$this->initVariables();
	}

	/////////////////////////////////////////////////////////////////////////////////////////
	//
	//  Internals
	//
	////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * PHPStorm warning/error display fix because this class is mixed with the generic class Variable_Mixin
	 * @return this
	 */
	private function getVariableDelegate()
	{
		return $this;
	}

	/***
	 * Prepare all service descriptors.
	 */
	private function prepareResources()
	{
		$this->getVariableDelegate()->initVariables();
		$this->getVariableDelegate()->resolveResources();
	}

	/**
	 *
	 * Runs a node application with the --jhelp argument. A standard node application
	 * should return a number of command line options on --help. This is usually bad for parsing and
	 * so the node application should return a JSON string on --jhelp.
	 *
	 * @param $serviceResource
	 * @return bool|string
	 *
	 * @TODO : what about npm --help ?
	 */
	private function getServiceOptions($serviceResource, $helpArg = '--jhelp')
	{

		$workingPath = $serviceResource->{XAPP_RESOURCE_PATH_ABSOLUTE};
		$nodeapp = $serviceResource->main;
		if ($nodeapp != '') {

			$args = array();
			$cmd = "node " . $nodeapp;
			$args[] = $helpArg;

			$result = XApp_Shell_Utils::run(
				$cmd,
				$args,
				null,
				Array(
					XApp_Shell_Utils::OPTION_WORKING_PATH => $workingPath,
					XApp_Shell_Utils::OPTION_BACKGROUND => false
				)
			);


			//try to decode
			$dSerialized = json_decode($result, true);
			if ($dSerialized !== false && is_array($dSerialized)) {
				return $dSerialized;
			}

			return $result;
		} else {
			return false;
		}
	}

	/**
	 *
	 * Runs a node application with the --info argument.
	 * @param $serviceResource
	 * @return bool|string
	 *
	 * @TODO : what about npm --help ?
	 */
	private function getServiceInfo($serviceResource, $helpArg = '--info')
	{

		$workingPath = $serviceResource->{XAPP_RESOURCE_PATH_ABSOLUTE};
		if (property_exists($serviceResource, 'main')) {
			$nodeapp = $this->fixWindowsPath($serviceResource->main);
			if ($nodeapp != '') {
				$args = array();
				$cmd = "node " . $nodeapp;
				if(property_exists($serviceResource,'isNode') && $serviceResource->{'isNode'}===false){
					$cmd = $nodeapp;
				}
				$args[] = $helpArg;

				$result = XApp_Shell_Utils::run(
					$cmd,
					$args,
					null,
					Array(
						XApp_Shell_Utils::OPTION_WORKING_PATH => $workingPath,
						XApp_Shell_Utils::OPTION_BACKGROUND => false
					)
				);



				//error_log('run ' .$cmd . ' in ' . $workingPath . ' ' . $result);
				//try to decode
				$dSerialized = json_decode($result, true);
				if ($dSerialized !== false && is_array($dSerialized)) {

					if (xo_get(self::REWRITE_HOST, $this) && $dSerialized['host']) {

						$host = gethostname();
						$resolved = gethostbyname($host);
						if (xo_get(self::FORCE_HOST, $this) && strlen(xo_get(self::FORCE_HOST, $this)) > 0) {
							$resolved = xo_get(self::FORCE_HOST, $this);
						}

						if($dSerialized['host'] ==='http://127.0.0.1' || $dSerialized['host'] ==='http://0.0.0.0'){

							$requestUrl = XApp_Service_Entry_Utils::getUrl();
							$urlParts = parse_url($requestUrl);
							if($urlParts['host']!==$dSerialized['host']){
								$dSerialized['host'] = 'http://' . $urlParts['host'];
								$resolved = null;
							}

						}
						if ($resolved && strlen($resolved)) {
							$dSerialized['host'] = 'http://' . $resolved;
						}

						//error_log('set to ' .$result);
					}
					return $dSerialized;
				} else {
					xapp_clog('couldn deserialize ' . $result);
				}

				return $result;
			} else {
				return false;
			}
		}

		return false;
	}

	/***
	 * CompleteServiceResource evaluates and completes a NodeJS service configuration with
	 * additional fields as the status or available command line options.
	 * @param $resource
	 * @param $fields : evaluate fields of interest
	 */
	private function completeServiceResource($resource, $fields = array()){
		$emits = xo_get(self::EMITS, $this) === true;

		// Defaults
		if (!isset($fields[self::FIELD_STATUS])) {
			$fields[self::FIELD_STATUS] = true;
		}
		if (!isset($fields[self::FIELD_INFO])) {
			$fields[self::FIELD_INFO] = true;
		}
		if (!isset($fields[self::FIELD_CLIENTS])) {
			$fields[self::FIELD_CLIENTS] = true;
		}
		if (!isset($fields[self::FIELD_CHILD_PROCESSES])) {
			$fields[self::FIELD_CHILD_PROCESSES] = false;
		}
		if (!isset($fields[self::FIELD_OPTIONS])) {
			$fields[self::FIELD_OPTIONS] = false;
		}

		$resource->clients = 0;
		$resource->status = self::SERVICE_STATUS_UNKNOWN;//default to unknown

		//check status
		if (
			$fields[self::FIELD_STATUS] === true &&
			property_exists($resource, 'port') &&
			property_exists($resource, 'host')
		) {
			if (xo_get(self::REWRITE_HOST, $this)) {
				if(property_exists($resource, 'host')) {
					$host = gethostname();
					$resolved = gethostbyname($host);
					if (xo_get(self::FORCE_HOST, $this) && strlen(xo_get(self::FORCE_HOST, $this)) > 0) {
						$resolved = xo_get(self::FORCE_HOST, $this);
					}

					if ($resolved && strlen($resolved)) {
						$resource->host = $resolved;
					}
				}
			}

			if (self::_isTCPListening($resource->host, $resource->port)) {
				$resource->status = self::SERVICE_STATUS_ONLINE;
			} else {
				$resource->status = self::SERVICE_STATUS_OFFLINE;
			}
		}

		//check info
		if ($fields[self::FIELD_INFO] === true) {
			$info = $this->getServiceInfo($resource);
			if ($info) {
				$resource->info = $info;//can be string or object

				if (xo_get(self::REWRITE_HOST, $this)) {

				}
			}
		}

		//check options
		if (
			$fields[self::FIELD_OPTIONS] === true &&
			property_exists($resource, 'main') &&
			property_exists($resource, 'has') &&
			property_exists($resource->has, 'options')
		) {
			$options = $this->getServiceOptions($resource, $resource->has->options);
			if ($options) {
				$resource->options = $options;//can be string or object
			}
		}

		//list child processes
		if (
			$fields[self::FIELD_CHILD_PROCESSES] === true &&      //only when of interest
			$resource->status == self::SERVICE_STATUS_ONLINE && //must be online
			isset($resource->main)
		)                            //must a have valid main path
		{
			//get processes for command "node", filtered by "resource->main"
			$ps_list = XApp_Shell_Utils::getProcesses(
				"node",
				array(
					XApp_Shell_Utils::OPTION_FILTER_PROCESSES => $resource->main,
					XApp_Shell_Utils::OPTION_RETURN_PROCESSES_TREE => true
				)
			);
			$resource->clients = count($ps_list);
			$resource->ps_tree = $ps_list;
		}
		//tell everyone
		if ($emits) {
			Xapp_Hook::trigger(self::EVENT_ON_NODE_META_CREATED, array('item' => $resource));
		}
	}


	/////////////////////////////////////////////////////////////////////////////////////////
	//
	//  Utils
	//  @TODO : find a better place to dance!
	//
	////////////////////////////////////////////////////////////////////////////////////////

	/***
	 * Check if it's a TCP server ready on host:port
	 *
	 * @param $host
	 * @param $port
	 * @return bool
	 *
	 * @TODO : Check avaibility of @fsockopen on platforms and hardened systems
	 */
	private static function _isTCPListening($host, $port)
	{
		$fp = @fsockopen($host, $port, $errno, $errstr, 30);

		if (!$fp) {
			return false;
		} else {
			fclose($fp);
			return true;
		}
	}

	/////////////////////////////////////////////////////////////////////////////////////////
	//
	//  Hooks
	//
	////////////////////////////////////////////////////////////////////////////////////////

	/***
	 * Absolute path to a resource file
	 */
	const RESOURCES_DATA   = "XAPP_RESOURCE_DATA";

	/***
	 * Resource type to enumerate
	 */
	const RESOURCES_TYPE   = "XAPP_RESOURCE_TYPE";

	/***
	 * Namespace of the relative registry
	 */
	const RELATIVE_REGISTRY_NAMESPACE   = "XAPP_RESOURCE_REGISTRY_NS_RELATIVE";

	/***
	 * Namespace of the absolute registry
	 */
	const ABSOLUTE_REGISTRY_NAMESPACE   = "XAPP_RESOURCE_REGISTRY_NS_ABSOLUTE";

	/***
	 * Namespace of the absolute registry
	 */
	const RESOURCE_VARIABLE_DELIMITER   = "XAPP_RESOURCE_VARIABLE_DELIMITER";


	/***
	 * Relative variables
	 */
	const RELATIVE_VARIABLES            = "XAPP_RELATIVE_VARS";

	/***
	 * Relative variables
	 */
	const ABSOLUTE_VARIABLES            = "XAPP_ABSOLUTE_VARS";


	/***
	 * Transfers all relative and absolute variables to the registry
	 */
	public function initVariables(){

		$resourceData = xo_get(self::RESOURCES_DATA,$this);

		if(is_string($resourceData)){
			xo_set(self::RESOURCES_DATA,(object)XApp_Utils_JSONUtils::read_json($resourceData,'json',false,true),$this);
		}



		if(xapp_has_option(self::RELATIVE_VARIABLES)){
			$rVariables = xapp_get_option(self::RELATIVE_VARIABLES,$this);
			if(count($rVariables)){
				foreach($rVariables as $variable => $value){
					$this->registerRelative($variable,$value);
				}
			}
		}

		if(xapp_has_option(self::ABSOLUTE_VARIABLES)){
			$variables = xapp_get_option(self::ABSOLUTE_VARIABLES,$this);
			if(count($variables)){
				foreach($variables as $variable => $value){
					$this->registerAbsolute($variable,$value);
				}
			}
		}
	}

	/***
	 * Debugging tools, print what you have
	 */
	public function printPaths(){

		echo('<br/> XAPP RESOURCE RENDERER - PATHS<br/>');

		echo('RESOURCE DATA' . json_encode(xapp_get_option(self::RESOURCES_DATA,$this)) . '<br/>');

		$keyValues = $this->registryToKeyValues(xapp_get_option(self::RELATIVE_REGISTRY_NAMESPACE,$this));

		echo('RESOURCE RELATIVE VARIABLES' . json_encode($keyValues) . '<br/>');

		$keyValues = $this->registryToKeyValues(xapp_get_option(self::ABSOLUTE_REGISTRY_NAMESPACE,$this));

		echo('RESOURCE ABSOLUTE VARIABLES' . json_encode($keyValues) . '<br/>');

	}
	/***
	 * Return an instance to a xapp-registry instance by a given namespace
	 * @param $namespace
	 * @return null|Xapp_Registry
	 */
	public function getRegistry($namespace){
		return Xapp_Registry::instance(array(Xapp_Registry::DEFAULT_NS =>  $namespace));
	}
	/**
	 * Register a resource variable in the relative namespace
	 * @param $key
	 * @param $value
	 * @return array
	 */
	public function registerRelative($key,$value){
		$resourceNamespace = xapp_get_option(self::RELATIVE_REGISTRY_NAMESPACE,$this);
		$registry = $this->getRegistry($resourceNamespace);
		$res = $registry->set(
			$key,
			$value,
			$resourceNamespace,$this);
		return $res;

	}
	/**
	 * Register a resource variable in the absolute namespace
	 * @param $key
	 * @param $value
	 */
	public function registerAbsolute($key,$value){
		$resourceNamespace = xapp_get_option(self::ABSOLUTE_REGISTRY_NAMESPACE,$this);
		$registry = $this->getRegistry($resourceNamespace);
		$registry->set(
			$key,
			$value,
			$resourceNamespace);
	}

	/***
	 * Register default resource variables
	 */
	public function registerDefault(){

		//register our doc root relative path
		/*
		$docRootRelative = xapp_get_option(self::DOC_ROOT,$this);
		if($docRootRelative){
			$this->registerRelative(
				xapp_get_option(self::DOC_ROOT_VAR_NAME,$this),
				$docRootRelative);
		}

		//register our doc root absolute path
		$docRootAbsolute = xapp_get_option(self::DOC_ROOT_PATH,$this);
		if($docRootRelative){
			$this->registerAbsolute(
				xapp_get_option(self::DOC_ROOT_VAR_NAME,$this),
				$docRootAbsolute);
		}
		*/
	}
	/***
	 * @param $key
	 * @param $relative
	 * @param $absolute
	 */
	public function registerResource($key,$relative,$absolute=null){
	}
	/**
	 * @param $namespace
	 * @return array
	 * @throws Xapp_Error
	 */
	public  function registryToKeyValues($namespace){
		$registry = $this->getRegistry($namespace);
		$result = $registry->getAll($namespace);
		return $result;
	}

	/***
	 * Resolves a string with resource variables
	 * For instance : %XASWEB%/xasthemes/claro/document.css
	 * @param $string
	 */
	public function resolveRelative($string){

		//pick up registry values
		$keyValues = $this->registryToKeyValues(xapp_get_option(self::RELATIVE_REGISTRY_NAMESPACE,$this));
		return $this->_replaceResourceVariables($string,$keyValues);

	}

	/***
	 * Resolves a string with resource variables to absolute paths
	 * For instance : %XASWEB%/xasthemes/claro/document.css
	 * @param $string
	 */
	public function resolveAbsolute($string){

		//pick up registry values
		$keyValues = $this->registryToKeyValues(xapp_get_option(self::ABSOLUTE_REGISTRY_NAMESPACE,$this));
		return $this->_replaceResourceVariables($string,$keyValues);

	}

	/***
	 * Does a multiple search and replace on a string using a key/value array.
	 * @param $str
	 * @param $vars
	 * @return string
	 */
	public function _replaceResourceVariables($str,$vars){

		$result =  '' . $str;
		$variableDelimiter = xapp_get_option(self::RESOURCE_VARIABLE_DELIMITER,$this);
		$userVars= (array)$vars;
		if($userVars){
			$_keys = array();
			$_values = array();
			foreach ($userVars as $key => $value)
			{
				array_push($_keys,$variableDelimiter.$key.$variableDelimiter);
				array_push($_values,$value);
			}
			$result = str_replace(
				$_keys,
				$_values,
				$result
			);
		}
		return $result;
	}
	/***
	 * Does a multiple search and replace on a string using a key/value array.
	 * @param $str
	 * @param $vars
	 * @return string
	 */
	public static function replaceResourceVariables($str,$vars,$variableDelimiter='%'){

		$result =  '' . $str;
		$userVars= (array)$vars;
		if($userVars){
			$_keys = array();
			$_values = array();
			foreach ($userVars as $key => $value)
			{
				array_push($_keys,$variableDelimiter.$key.$variableDelimiter);
				array_push($_values,$value);
			}
			$result = str_replace(
				$_keys,
				$_values,
				$result
			);
		}
		return $result;
	}

	/***
	 * Resolve the url of a given resource object
	 * @param $resource
	 */
	public function resolveResource(&$resource){

		if($resource!==null && is_object($resource)){

			if( xapp_property_exists($resource,'main')){
				$resource->{'main'}=$this->resolveAbsolute(xapp_property_get($resource,'main'));
			}

			if( xapp_property_exists($resource,XAPP_RESOURCE_NAME)&&
				xapp_property_exists($resource,XAPP_RESOURCE_PATH)){
				//not resolved yet ?
				if(!xapp_property_exists($resource,XAPP_RESOURCE_PATH_ABSOLUTE)){
					$resource->{XAPP_RESOURCE_PATH_ABSOLUTE}=$this->resolveAbsolute(xapp_property_get($resource,XAPP_RESOURCE_PATH));
				}
			}
		}else{
			error_log('resource/Renderer : have no resources!!!');
		}
		return $resource;
	}

	/***
	 *  Resolves web urls and absolute paths
	 */
	public function resolveResources(){
		$resourceData = xapp_get_option(self::RESOURCES_DATA,$this);
		if($resourceData!==null){

			$resourceItems = (array)xapp_object_find($resourceData,'.items',array('class='.'cmx.types.Resource'));
			if($resourceItems!=null && count($resourceItems)){
				foreach($resourceItems as $resourceItem){

					if(
						!xapp_property_exists($resourceItem,XAPP_RESOURCE_URL_RESOLVED)||
						!xapp_property_exists($resourceItem,XAPP_RESOURCE_PATH_ABSOLUTE))
					{
						$this->resolveResource($resourceItem);
					}
				}
				xo_set(self::RESOURCES_DATA,$resourceItems);
			}
		}else{
			return false;
		}
		return true;
	}
	/***
	 * @param string    $type
	 * @param bool      $enabledOnly
	 * @return array|null
	 */
	public function getResourcesByType($type,$enabledOnly=true){
		$resourceData = xapp_get_option(self::RESOURCES_DATA,$this);
		if($resourceData!==null){
			$resourceItems = (array)xapp_object_find($resourceData,'.',array('class='.'cmx.types.Resource','type='.$type,'enabled='.$enabledOnly));
			if($resourceItems!=null && count($resourceItems)){
				return $resourceItems;
			}

		}
		return null;
	}

	/***
	 *
	 * @param string    $type
	 * @param bool      $enabledOnly
	 * @return array|null
	 */
	public function getResource($name,$enabledOnly=true){
		$resourceData = xapp_get_option(self::RESOURCES_DATA,$this);
		if($resourceData!==null){
			$resourceItem = (array)xapp_object_find($resourceData,'.items',array('class='.'cmx.types.Resource','name='.$name,'type='. xo_get(self::RESOURCES_TYPE,$this),'enabled='.$enabledOnly));
			if(count($resourceItem) && is_object($resourceItem[0])){
				return $resourceItem[0];
			}
		}
		return null;
	}
}


?>