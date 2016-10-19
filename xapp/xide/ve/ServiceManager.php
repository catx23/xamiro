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

xapp_import('xapp.Directory.Service');

/***
 * Class XIDE_NodeJS_Service_Manager provides a useful set of NodeJS related functions like :
 * -start, stop, kill and also debug (uses and starts a 'node-inspector', needs Chrome on the clients side)!
 * -enumerate running services, as well its child processes ('spawned')
 */
class XIDE_VE_Manager extends XIDE_Manager
{

	public function renderContent($content,$fileStruct,$bootstrap){
		/**
		 * 1. get content
		 * 2. apply template
		 *
		 * 3. variables=
		 *
		 * baseUrl,
		 * requireUrl,
		 * jQueryUrl,
		 * requireBaseUrl,
		 * themeCssFiles,
		 * id,
		 * styles = empty
		 *
		 * <base href="{baseUrl}"/>
		 * <meta charset="utf-8"/>
		 * <script src="{requireUrl}"></script>
		 * <script src="{jQueryUrl}"></script>
		 *
		 *
		 *
		 */
		$this->initVariables();
		$vfsConfig = $bootstrap->getServiceConfiguration('XCOM_Directory_Service');
		$serviceConf = $vfsConfig[XApp_Service::XAPP_SERVICE_CONF];
		$vfsConfig = $serviceConf[XApp_Directory_Service::FILE_SYSTEM_CONF];
		$vfsVars = $vfsConfig[XApp_VFS_Base::ABSOLUTE_VARIABLES];
		$this->registerRelative('VFS_VARS',json_encode($vfsVars, true));

		$this->registerRelative('USER_DIRECTORY',str_replace('\\','\\\\',XCF_Bootstrap::$user_data));

		$this->registerRelative('VFS_GET_URL', $bootstrap->getVFSGetUrl());

		$path = $fileStruct['path'];
		$folder = dirname($path);
		$mount = $fileStruct['mount'];
		

		xapp_import('xapp.Service.Utils');
		$IBM_ROOT  = 'xibm/ibm';
		$OFF_SET = '../..';
		$XIDEVE_CLIENT_BASE = '/lib/xideve/delite/';
		$requireBaseUrl= $this->resolveRelative('%clientUrl%') . '/lib/'.$IBM_ROOT;
		
		$this->registerRelative('requireBaseUrl',$requireBaseUrl);
		$this->registerRelative('ibmRoot','xibm/ibm');
		$this->registerRelative('offset',$OFF_SET);
		//lib root
		$libRoot= $this->resolveRelative('%clientUrl%') . '/lib';
		$this->registerRelative('libRoot',$libRoot);

		//jquery
		$jQueryUrl= $this->resolveRelative('%clientUrl%') . '/lib/external/jquery-1.9.1.min.js';
		$this->registerRelative('jQueryUrl',$jQueryUrl);

		$this->resolveRelative('%lodashUrl%') . '/lib/external/lodash.min.js';
		$this->registerRelative('lodashUrl',$libRoot.'/external/lodash.min.js');

		//extra resources
		$this->registerRelative('themeCssFiles','');
		$this->registerRelative('styles','');

		//file variables
		$this->registerRelative('path',$fileStruct['path']);
		$this->registerRelative('mount',$fileStruct['mount']);
		$this->registerRelative('theme','bootstrap');


		$this->registerRelative('data',$this->resolveRelative('%data%'));
		$clientDirectory = xapp_get_option(self::CLIENT_DIRECTORY);

		$css = XApp_Service_Utils::_getKey('css','app.css');
		$this->registerRelative('css',base64_decode($css));
		$this->registerRelative('user_drivers_path',$bootstrap->getVFSGetUrl('user_drivers'));
		$this->registerRelative('workspace_user',$bootstrap->getVFSGetUrl('workspace_user'));
		$templateRoot = $clientDirectory . $XIDEVE_CLIENT_BASE;
		$template = XApp_Service_Utils::_getKey('template','view.template.html');

		$templatePath = realpath($templateRoot . $template);
		$templateContent = file_get_contents($templatePath);


		
		$templateContent = $this->resolveRelative($templateContent);
		$matches=array();
		preg_match_all('~\bbackground(-image)?\s*:(.*?)\(\s*(\'|")?(?<image>.*?)\3?\s*\)~i',$content,$matches);
		$images = $matches['image'];
		foreach ($images as $i => $imageUrl) {
			$parts = explode('://',$imageUrl);
			$mount = $parts[0];
			$path  = $parts[1];
			$url = $bootstrap->getVFSGetUrl($mount) . $path;
			$content = str_replace($imageUrl,$url,$content);

		}

		//update scripts
		$domd = new DOMDocument();
		libxml_use_internal_errors(true);
		$domd->loadHTML($content);
		libxml_use_internal_errors(false);
		$items = $domd->getElementsByTagName('script');

		foreach($items as $item) {

			$src = $item->getAttribute('src');
			if(!strpos($src,'http')) {
				$url = $bootstrap->getVFSGetUrl($mount) . $folder . '/' . $src;
				$content = str_replace($src, $url, $content);
			}

			/*
			$data[] = array(
				'src' => $item->getAttribute('src'),
				'outerHTML' => $domd->saveHTML($item),
				'innerHTML' => $domd->saveHTML($item->firstChild),
			);
			*/
		}
		$contentFinal = str_replace('<viewHeaderTemplate/>',$templateContent,$content);
		return $contentFinal;
	}

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

	const CLIENT_DIRECTORY = "CLIENT_DIRECTORY";

	/**
	 * options dictionary for this class containing all data type values
	 *
	 * @var array
	 */
	public static $optionsDict = array
	(
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
		self::CLIENT_DIRECTORY              => XAPP_TYPE_STRING
	);

	/**
	 * options mandatory map for this class contains all mandatory values
	 *
	 * @var array
	 */
	public static $optionsRule = array
	(
		self::EMITS => 0,
		self::REWRITE_HOST => 0,
		self::FORCE_HOST => 0,
		self::RESOURCES_DATA                => 1,
		self::RESOURCES_TYPE                => 1,
		self::RELATIVE_VARIABLES            => 0,
		self::ABSOLUTE_VARIABLES            => 0,
		self::RELATIVE_REGISTRY_NAMESPACE   => 0,
		self::ABSOLUTE_REGISTRY_NAMESPACE   => 0,
		self::RESOURCE_VARIABLE_DELIMITER   => 0,
		self::CLIENT_DIRECTORY              => 0
	);

	/**
	 * options default value array containing all class option default values
	 * @var array
	 */
	public $options = array
	(
		self::EMITS => true,
		self::REWRITE_HOST => true,
		self::FORCE_HOST => null,
		self::RESOURCES_DATA                => null,
		self::RESOURCES_TYPE                => 'unknown',
		self::RELATIVE_VARIABLES            => null,
		self::ABSOLUTE_VARIABLES            => null,
		self::RELATIVE_REGISTRY_NAMESPACE   => 'xapp_relative_ns',
		self::ABSOLUTE_REGISTRY_NAMESPACE   => 'xapp_absolute_ns',
		self::RESOURCE_VARIABLE_DELIMITER   => '%',
		self::CLIENT_DIRECTORY              => null

	);

	/////////////////////////////////////////////////////////////////////////////////////////
	//
	//  Public API & main entries, expects this class has fully valid options.
	//  This class is being mixed also with the XApp_VariableMixin which comes
	//  with its own set of options, needed to have a full service description map
	//  as well a set of resolvable variables within the service description map.
	//
	////////////////////////////////////////////////////////////////////////////////////////


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
		$this->initVariables();
		$this->resolveResources();
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

	/***
	 * Register a resource variable in the relative namespace
	 * @param $key
	 * @param $value
	 * @return bool
	 */
	public function registerRelative($key,$value){
		$resourceNamespace = xapp_get_option(self::RELATIVE_REGISTRY_NAMESPACE,$this);
		$registry = $this->getRegistry($resourceNamespace);
		return $registry->set(
			$key,
			$value,
			$resourceNamespace,$this);
	}

	/***
	 * Register a resource variable in the absolute namespace
	 * @param $key
	 * @param $value
	 * @return void
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
	/***
	 * Returns all registered resource variables in a given namespace
	 * @param $namespace
	 * @return array
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
	 * @return string
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
	 * @return string
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
	 * @param string    $name
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