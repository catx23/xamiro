<?php
/**
 * @version 0.1.0
 *
 * @author https://github.com/mc007
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */
/**
 * Defines the resource type File Proxy. This will be used to mount local folder at a given path.
 * @const XAPP_RESOURCE_TYPE_FILE_PROXY
 */
if ( ! defined( 'XAPP_RESOURCE_TYPE_FILE_PROXY' ) ) {
	define( 'XAPP_RESOURCE_TYPE_FILE_PROXY', 'FILE_PROXY' );
}
/***
 * Class XApp_Resource_Renderer
 * Singleton resource processor. It takes decoded json data in this format below and resolves
 * registered resources variables like %XASWEB% to urls and absolute local paths on disc.
 */
/*
{
    "class":"cmx.types.Resources",
    "includes":[

],
    "items":[
        {
            "class":"cmx.types.Resource",
            "type":"FILE_PROXY",
            "name":"xapp",
            "path":"%XAPP_ROOT%",
            "url":"%XASWEB%/lib/",
            "enabled":true
        }
    ]
}
*/

class XApp_VFS_Base {
	/////////////////////////////////////////////////////////////////////////////////////////
	//
	//  Hooks
	//
	////////////////////////////////////////////////////////////////////////////////////////


	const EVENT_ON_NODE_META_CREATED = "EVENT_ON_NODE_META_CREATED";
	const EVENT_ON_NODE_ADD = "EVENT_ON_NODE_ADD";
	const EVENT_ON_NODE_ADDED = "EVENT_ON_NODE_ADDED";
	/**
	 * contains the singleton instance created with create function
	 *
	 * @var null|XApp_VFS_Base
	 */
	protected static $_instance = null;

	/***
	 * Maximum node length
	 */
	const NODENAME_MAX_LENGTH = "XAPP_FILE_NODENAME_MAX_LENGTH";

	/***
	 * Relative url to the client's app doc root
	 */
	const RESOURCES_DATA = "XAPP_RESOURCE_DATA";

	/***
	 * Namespace of the relative registry
	 */
	const RELATIVE_REGISTRY_NAMESPACE = "XAPP_RESOURCE_REGISTRY_NS_RELATIVE";

	/***
	 * Namespace of the absolute registry
	 */
	const ABSOLUTE_REGISTRY_NAMESPACE = "XAPP_RESOURCE_REGISTRY_NS_ABSOLUTE";

	/***
	 * Namespace of the absolute registry
	 */
	const RESOURCE_VARIABLE_DELIMITER = "XAPP_RESOURCE_VARIABLE_DELIMITER";

	/***
	 * Relative variables
	 */
	const RELATIVE_VARIABLES = "XAPP_VFS_RELATIVE_VARS";

	/***
	 * Relative variables
	 */
	const ABSOLUTE_VARIABLES = "XAPP_VFS_ABSOLUTE_VARS";

	/**
	 * options dictionary for this class containing all data type values
	 *
	 * @var array
	 */
	public static $optionsDict = array
	(
		self::RESOURCES_DATA              => XAPP_TYPE_OBJECT,
		self::RELATIVE_VARIABLES          => XAPP_TYPE_ARRAY,
		self::ABSOLUTE_VARIABLES          => XAPP_TYPE_ARRAY,
		self::NODENAME_MAX_LENGTH         => XAPP_TYPE_INT,
		self::RELATIVE_REGISTRY_NAMESPACE => XAPP_TYPE_STRING,
		self::ABSOLUTE_REGISTRY_NAMESPACE => XAPP_TYPE_STRING,
		self::RESOURCE_VARIABLE_DELIMITER => XAPP_TYPE_STRING,
	);

	/**
	 * options mandatory map for this class contains all mandatory values
	 *
	 * @var array
	 */
	public static $optionsRule = array
	(
		self::RESOURCES_DATA              => 1,
		self::RELATIVE_VARIABLES          => 0,
		self::ABSOLUTE_VARIABLES          => 0,
		self::RELATIVE_REGISTRY_NAMESPACE => 0,
		self::ABSOLUTE_REGISTRY_NAMESPACE => 0,
		self::RESOURCE_VARIABLE_DELIMITER => 0,
		self::NODENAME_MAX_LENGTH         => 0
	);

	/**
	 * options default value array containing all class option default values
	 *
	 * @var array
	 */
	public $options = array
	(
		self::RESOURCES_DATA              => null,
		self::RELATIVE_VARIABLES          => null,
		self::ABSOLUTE_VARIABLES          => null,
		self::RELATIVE_REGISTRY_NAMESPACE => 'xapp_relative_vfs_ns',
		self::ABSOLUTE_REGISTRY_NAMESPACE => 'xapp_absolute_vfs_ns',
		self::RESOURCE_VARIABLE_DELIMITER => '%',
		self::NODENAME_MAX_LENGTH         => 255
	);

	/**
	 * class constructor
	 * call parent constructor for class initialization
	 *
	 * @error 14601
	 *
	 * @param null|array|object $options expects optional options
	 */
	public function __construct( $options = null ) {
		xapp_set_options( $options, $this );
		$this->initVariables();
	}

	/***
	 * Transfers all relative and absolute variables to the registry
	 */
	private function initVariables() {

		if ( xapp_has_option( self::RELATIVE_VARIABLES ) ) {
			$rVariables = xapp_get_option( self::RELATIVE_VARIABLES, $this );
			if ( count( $rVariables ) ) {
				foreach ( $rVariables as $variable => $value ) {
					$this->registerRelative( $variable, $value );
				}
			}
		}

		if ( xapp_has_option( self::ABSOLUTE_VARIABLES ) ) {
			$variables = xapp_get_option( self::ABSOLUTE_VARIABLES, $this );
			if ( count( $variables ) ) {
				foreach ( $variables as $variable => $value ) {
					if ( ! XApp_Utils_Strings::endsWith( $value, DIRECTORY_SEPARATOR ) ) {
						$value .= DIRECTORY_SEPARATOR;
					}
					$this->registerAbsolute( $variable, $value );
				}
			}
		}
	}

	/***
	 * Debugging tools, print what you have
	 */
	public function printPaths() {

		echo( '<br/> XAPP RESOURCE RENDERER - PATHS<br/>' );

		echo( 'RESOURCE DATA' . json_encode( xapp_get_option( self::RESOURCES_DATA, $this ) ) . '<br/>' );

		$keyValues = $this->registryToKeyValues( xapp_get_option( self::RELATIVE_REGISTRY_NAMESPACE, $this ) );

		echo( 'RESOURCE RELATIVE VARIABLES' . json_encode( $keyValues ) . '<br/>' );

		$keyValues = $this->registryToKeyValues( xapp_get_option( self::ABSOLUTE_REGISTRY_NAMESPACE, $this ) );

		echo( 'RESOURCE ABSOLUTE VARIABLES' . json_encode( $keyValues ) . '<br/>' );

	}

	/***
	 * Return an instance to a xapp-registry instance by a given namespace
	 *
	 * @param $namespace
	 *
	 * @return null|Xapp_Registry
	 */
	public function getRegistry( $namespace ) {
		return Xapp_Registry::instance( array( Xapp_Registry::DEFAULT_NS => $namespace ) );
	}

	/***
	 * Register a resource variable in the relative namespace
	 *
	 * @param $key
	 * @param $relative
	 * @param $absolute
	 */
	public function registerRelative( $key, $value ) {
		$resourceNamespace = xapp_get_option( self::RELATIVE_REGISTRY_NAMESPACE, $this );
		$registry          = $this->getRegistry( $resourceNamespace );
		$res               = $registry->set(
			$key,
			$value,
			$resourceNamespace,
			$this );

		return $res;

	}

	/***
	 * Register a resource variable in the absolute namespace
	 *
	 * @param $key
	 * @param $relative
	 * @param $absolute
	 */
	public function registerAbsolute( $key, $value ) {
		$resourceNamespace = xapp_get_option( self::ABSOLUTE_REGISTRY_NAMESPACE, $this );
		$registry          = $this->getRegistry( $resourceNamespace );
		$registry->set(
			$key,
			$value,
			$resourceNamespace );
	}

	/***
	 * Register default resource variables
	 */
	public function registerDefault() {

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
	public function registerResource( $key, $relative, $absolute = null ) {
	}

	/***
	 * Returns all registered resource variables in a given namespace
	 *
	 * @param $key
	 * @param $relative
	 * @param $absolute
	 */
	public function registryToKeyValues( $namespace ) {
		$registry = $this->getRegistry( $namespace );
		$result   = $registry->getAll( $namespace );

		return $result;
	}

	/***
	 * Resolves a string with resource variables
	 * For instance : %XASWEB%/xasthemes/claro/document.css
	 *
	 * @param $string
	 */
	public function resolveRelative( $string ) {

		//pick up registry values
		$keyValues = $this->registryToKeyValues( xapp_get_option( self::RELATIVE_REGISTRY_NAMESPACE, $this ) );

		return $this->_replaceResourceVariables( $string, $keyValues );

	}

	/***
	 * Resolves a string with resource variables to absolute paths
	 * For instance : %XASWEB%/xasthemes/claro/document.css
	 *
	 * @param $string
	 */
	public function resolveAbsolute( $string ) {

		//pick up registry values
		$keyValues = $this->registryToKeyValues( xapp_get_option( self::ABSOLUTE_REGISTRY_NAMESPACE, $this ) );

		return $this->_replaceResourceVariables( $string, $keyValues );

	}

	/***
	 * Does a multiple search and replace on a string using a key/value array.
	 *
	 * @param $str
	 * @param $vars
	 *
	 * @return string
	 */
	private function _replaceResourceVariables( $str, $vars ) {

		$result            = '' . $str;
		$variableDelimiter = xapp_get_option( self::RESOURCE_VARIABLE_DELIMITER, $this );
		$userVars          = (array) $vars;
		if ( $userVars ) {
			$_keys   = array();
			$_values = array();
			foreach ( $userVars as $key => $value ) {
				array_push( $_keys, $variableDelimiter . $key . $variableDelimiter );
				array_push( $_values, $value );
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
	 *
	 * @param $resource
	 */
	public function resolveResource( $resource ) {

		if ( $resource !== null && is_object( $resource ) ) {

			if ( xapp_property_exists( $resource, XAPP_RESOURCE_NAME ) &&
			     xapp_property_exists( $resource, XAPP_RESOURCE_PATH )
			) {

				//not resolved yet ?
				if ( ! xapp_property_exists( $resource, XAPP_RESOURCE_PATH_ABSOLUTE ) ) {
					$resource->{XAPP_RESOURCE_PATH_ABSOLUTE} = $this->resolveAbsolute( xapp_property_get( $resource,
							XAPP_RESOURCE_PATH ) );
				}
			}
		} else {
			error_log( 'resource/Renderer : have no resources!!!' );
		}

		return $resource;
	}

	/***
	 *  Resolves web urls and absolute paths
	 */
	public function resolveResources() {
		$resourceData = xapp_get_option( self::RESOURCES_DATA, $this );
		if ( $resourceData !== null ) {

			$resourceItems = (array) xapp_object_find( $resourceData,
				'.items',
				array( 'class=' . 'cmx.types.Resource' ) );
			if ( $resourceItems != null && count( $resourceItems ) ) {
				foreach ( $resourceItems as $resourceItem ) {

					if ( ! xapp_property_exists( $resourceItem, XAPP_RESOURCE_URL_RESOLVED ) ) {
						$this->resolveResource( $resourceItem );
					}
				}
			}
		} else {
			return false;
		}

		return true;
	}

	/***
	 * @param string $type
	 * @param bool $enabledOnly
	 *
	 * @return array|null
	 */
	public function getResourcesByType( $type, $enabledOnly = true ) {
		$resourceData = xapp_get_option( self::RESOURCES_DATA, $this );
		if ( $resourceData !== null ) {
			$resourceItems = (array) xapp_object_find( $resourceData,
				'.items',
				array( 'class=' . 'cmx.types.Resource', 'type=' . $type, 'enabled=' . $enabledOnly ) );
			if ( $resourceItems != null && count( $resourceItems ) ) {
				return $resourceItems;
			}

		}

		return null;
	}

	/***
	 *
	 * @param string $type
	 * @param bool $enabledOnly
	 *
	 * @return array|null
	 */
	public function getResource( $name, $enabledOnly = true ) {
		$resourceData = xapp_get_option( self::RESOURCES_DATA, $this );
		$nameIn       = '' . str_replace( '/', '', $name );

		if ( $resourceData !== null ) {
			$resourceItem = (array) xapp_object_find( $resourceData,
				'.items',
				array(
					'class=' . 'cmx.types.Resource',
					'name=' . $nameIn,
					'type=' . XAPP_RESOURCE_TYPE_FILE_PROXY,
					'enabled=' . $enabledOnly
				) );
			if ( count( $resourceItem ) && is_object( $resourceItem[0] ) ) {
				return $resourceItem[0];
			}
		}

		return null;
	}

	/***
	 *
	 * @param string $type
	 * @param bool $enabledOnly
	 *
	 * @return array|null
	 */
	public static function getResourceByName( $name, $resourceData ) {
		$nameIn = '' . str_replace( '/', '', $name );
		if ( $resourceData !== null ) {
			$resourceItem = (array) xapp_object_find( $resourceData,
				'.items',
				array( 'class=' . 'cmx.types.Resource', 'name=' . $nameIn, 'enabled=' . true ) );
			if ( count( $resourceItem ) && is_object( $resourceItem[0] ) ) {
				return $resourceItem[0];
			}
		}

		return null;
	}

	/***
	 *
	 * @param string $type
	 * @param bool $enabledOnly
	 *
	 * @return array|null
	 */
	public static function isRemote( $name, $resourceData ) {
		$nameIn = '' . str_replace( '/', '', $name );
		if ( $resourceData !== null ) {
			$resourceItem = (array) xapp_object_find( $resourceData,
				'.items',
				array(
					'class=' . 'cmx.types.Resource',
					'type=' . XAPP_RESOURCE_TYPE_REMOTE_FILE_PROXY,
					'name=' . $nameIn,
					'enabled=' . true
				) );
			if ( count( $resourceItem ) && is_object( $resourceItem[0] ) ) {
				return $resourceItem[0];
			}
		}

		return null;
	}

	/***
	 *
	 * @param string $type
	 * @param bool $enabledOnly
	 *
	 * @return array|null
	 */
	public static function isLocal( $name, $resourceData ) {
		$nameIn = '' . str_replace( '/', '', $name );
		if ( $resourceData !== null ) {
			$resourceItem = (array) xapp_object_find( $resourceData,
				'.items',
				array(
					'class=' . 'cmx.types.Resource',
					'type=' . XAPP_RESOURCE_TYPE_FILE_PROXY,
					'name=' . $nameIn,
					'enabled=' . true
				) );
			if ( count( $resourceItem ) && is_object( $resourceItem[0] ) ) {
				return $resourceItem[0];
			}
		}

		return null;
	}

	/**
	 * Test for mount point and return mount meta data
	 *
	 * @return mount point data
	 */
	public function hasMount( $name, $enabledOnly = true ) {
		return $this->getResource( $name, $enabledOnly );
	}

	/**
	 * @return mixed|null
	 */
	public function getResources() {
		return xo_get( self::RESOURCES_DATA, $this );
	}

	/**
	 * @param $mount
	 *
	 * @return bool|string
	 */
	public function getResourceType( $mount ) {
		if ( $this->isLocal( $mount, $this->getResources() ) ) {
			return XAPP_RESOURCE_TYPE_FILE_PROXY;
		} elseif ( $this->isRemote( $mount, $this->getResources() ) ) {
			return XAPP_RESOURCE_TYPE_REMOTE_FILE_PROXY;
		}

		return false;
	}

	/**
	 * @param $src
	 * @param $dst
	 *
	 * @return bool
	 */
	public function isRemoteOperation( $src, $dst ) {
		$srcType = $this->getResourceType( XApp_Path_Utils::getMount( $src ) );
		$dstType = $this->getResourceType( XApp_Path_Utils::getMount( $dst ) );

		return $dst && $dstType && $srcType != $dstType;
	}

	public function isRemoteFS() {
		return false;
	}

	/**
	 * @param $fsPath
	 *
	 * @return mixed
	 */
	public function cleanSlashesRemote( $fsPath ) {
		$fsPath = str_replace( ':////', '://', $fsPath );
		$fsPath = str_replace( ':///', '://', $fsPath );
		$fsPath = str_replace( '///', '/', $fsPath );

		return $fsPath;
	}


}
