<?php
xapp_import("xapp.Commons.Entity");
xapp_import('xapp.xide.Models.User');
xapp_import('xapp.xide.Models.UserRole');
xapp_import('xapp.xide.Models.UserPermission');
xapp_import('xapp.xide.Models.Resource');
xapp_import('xapp.Security.IAuthenticator');
xapp_import('xapp.Utils.Strings');
xapp_import('xapp.Http.*');

class XApp_ResourceManager
{
	///////////////////////////////////////////////////////////////////////////
	//
	//  Available Hooks
	//
	///////////////////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////
	//
	//  Available Options, keyed as constants
	//
	///////////////////////////////////////////////////////////////////////////
	const STORE_CLASS = "XAPP_USER_STORE_CLASS";
	const STORE_CONF = "XAPP_USER_STORE_CONF";
	const STORE_DATA = "XAPP_USER_STORE_DATA";
	///////////////////////////////////////////////////////////////////////////
	//
	//  XApp option for this class
	//
	///////////////////////////////////////////////////////////////////////////

	/**
	 * options dictionary for this class containing all data type values
	 *
	 * @var array
	 */
	public static $optionsDict = array
	(
		self::STORE_CLASS => XAPP_TYPE_STRING,
		self::STORE_CONF => XAPP_TYPE_ARRAY,
		self::STORE_DATA => XAPP_TYPE_OBJECT
	);

	/**
	 * options mandatory map for this class contains all mandatory values
	 *
	 * @var array
	 */
	public static $optionsRule = array
	(
		self::STORE_CLASS => 0,
		self::STORE_CONF => 0,
		self::STORE_DATA => 0,
	);
	/**
	 * options default value array containing all class option default values
	 *
	 * @var array
	 */
	public $options = array
	(
		self::STORE_CLASS => 'XApp_Store_JSON',
		self::STORE_CONF => null,
		self::STORE_DATA => null,
	);



	///////////////////////////////////////////////////////////////////////////
	//
	//  Class attributes/members
	//
	///////////////////////////////////////////////////////////////////////////
	/***
	 * @var stdClass | XApp_Store_JSON
	 */
	protected $_store = null;

	///////////////////////////////////////////////////////////////////////////
	//
	//  Main entry points
	//
	///////////////////////////////////////////////////////////////////////////

	/***
	 *
	 * @param null $options
	 */
	public function __construct($options = null)
	{
		xapp_set_options($options, $this);
	}

	/***
	 * Initializes the store and loads the data
	 */
	public function init()
	{

		if ($this->initStore()) {
			//$this->initWithData( (object) $this->_store->read() );
		}
		$resources = (object)$this->_store->read();
	}


	public function ls(&$errors)
	{

		$resources = (object)$this->_store->read();

		if ($resources == null) {
			$errors[] = 'Have no Resources';
			return $errors;
		}

		$items = (array)$resources->items;

		$result = array();

		foreach ($items as $resource) {
			$resource = (array)$resource;
			if (!array_key_exists('name', $resource)) {
				continue;
			}

			$resourceItem = array(
				'name' => $resource['name']
			);

			if (array_key_exists('label', $resource)) {
				$resourceItem['label'] = $resource['label'];
			} else {
				$resourceItem['name'] = $resource['name'];
			}


			if (array_key_exists('type', $resource)) {
				$resourceItem['type'] = $resource['type'];
			}

			if (array_key_exists('adapter', $resource)) {
				$resourceItem['adapter'] = $resource['adapter'];
			}

			if (array_key_exists('config', $resource)) {


				$resourceItem['config'] = $resource['config'];
				if (array_key_exists('password', $resourceItem['config'])) {
					$resourceItem['config']['password'] = '';
				}
			}
			if (array_key_exists('path', $resource)) {
				$resourceItem['path'] = str_replace('://', '', $resource['name']);
			}
			$result[] = $resourceItem;
		}

		return $result;

	}

	/***
	 * Transfer new value to std::query result item
	 * @param $dstElement
	 * @param $newElement
	 */

	public function _merge(&$dstElement, $newElement)
	{
		$dstElement = array_merge((array)$dstElement, (array)$newElement);
		return $dstElement;
	}

	/**
	 * Quick'n dirty array remove object
	 * @param $array
	 * @param $value
	 * @return array
	 */
	public function array_remove(&$array, $value)
	{
		return array_filter(
			$array,
			function ($a) use ($value) {
				return $a !== $value;
			}
		);
	}

	/***
	 * @param $resource
	 * @return array
	 */
	public function updateResource($resource)
	{
		$errors = array();
		$resources = (object)$this->_store->read();

		//check we have this resource, lookup by name
		$resourceInRepo = & $this->getResourceByName($resource['name'], $resources);
		if ($resourceInRepo != null) {
			if (is_object($resourceInRepo)) {
				foreach ($resources->items as &$_res) {

					if ($_res['name'] === $resource['name']) {

						if(array_key_exists('config',$_res)){
							$_res['config'] = $this->_merge($_res['config'], $resource['config']);
						}

						if(array_key_exists('label',$_res)){
							$_res['label']=$resource['label'];
						}

						if(array_key_exists('path',$_res)){
							$_res['path']=$resource['path'];
						}

						break;
					}
				}
				$this->_store->write($resources);
			}
		}else{
			error_log('have no resource : ' . $resource['name']);
		}
		return $errors;
	}

	/***
	 * @param $resource
	 * @return array
	 */
	public function removeResource($resource)
	{
		$errors = array();
		$resources = (object)$this->_store->read();

		//check we have this resource, lookup by name
		$resourceInRepo = & $this->getResourceByName($resource['name'], $resources);
		if ($resourceInRepo != null) {
			if (is_object($resourceInRepo)) {
				foreach ($resources->items as &$_res) {
					if ($_res['name'] === $resource['name']) {
						$itemsNew = $this->array_remove($resources->items, $_res);
						$resources->items = array();//$this->array_remove($resources->items, $_res);
						foreach($itemsNew as $item){
							$resources->items[]=$item;
						}
						break;
					}
				}
				$this->_store->write($resources);
			}
		}
		return $errors;
	}

	/**
	 * @param $resource
	 * @return array|bool
	 */
	public function createResource($resource)
	{

		$errors = array();
		$resources = (object)$this->_store->read();

		//check we have this resource already, lookup by name
		$resourceInRepo = $this->getResourceByName($resource['name'], $resources);
		if ($resourceInRepo != null) {
			return false;
		}
		$resources->items[] = $resource;
		$this->_store->write($resources);

		return $errors;
	}
	///////////////////////////////////////////////////////////////////////////
	//
	//  Store & persistence related
	//
	///////////////////////////////////////////////////////////////////////////

	/***
	 * Parse options and init the store
	 * @param bool $force does force to re-create the store
	 * @return bool
	 */
	public function initStore($force = false)
	{

		//skip
		if ($force === false && $this->_store) {
			return true;
		}

		//Try with store data first
		if (xo_has(self::STORE_DATA) && is_object(xo_get(self::STORE_DATA))) {
			//$this->initWithData(xo_get(self::STORE_DATA));
			return true;
		}

		//No store data provided from outside, use the store class

		if (xo_has(self::STORE_CLASS)) {
			$_storeClz = xo_get(self::STORE_CLASS);

			//check its an instance already :
			if (is_object($_storeClz)) {
				$this->_store = $_storeClz;
				return true;
			} //its a class name
			elseif (is_string($_storeClz) && class_exists($_storeClz)) {
				$_ctrArgs = xo_has(self::STORE_CONF) ? xo_get(self::STORE_CONF) : array();
				$this->_store = new $_storeClz($_ctrArgs);
				return true;
			}
		}

		return false;
	}

	/***
	 *
	 * @param string $type
	 * @param bool $enabledOnly
	 * @return array|null
	 */
	public static function getResourceByName($name, $resourceData)
	{
		$nameIn = '' . str_replace('/', '', $name);
		if ($resourceData !== null) {
			$resourceItem = (array)xapp_object_find(
				$resourceData,
				'.items',
				array('class=' . 'cmx.types.Resource', 'name=' . $nameIn, 'enabled=' . true)
			);
			if (count($resourceItem) && is_object($resourceItem[0])) {
				return $resourceItem[0];
			}
		}
		return null;
	}

}

?>