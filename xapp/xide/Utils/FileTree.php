<?php

/***
 * Class XIDE_FileTree creates hierarchic file tree data
 */
class XIDE_FileTree
{
	/**
	 *  Match pattern to detect driver meta files
	 */
	const FILE_MATCH_PATTERN = "XIDE_FILE_MATCH_PATTERN";

	/**
	 *  File name clean pattern
	 */
	const FILE_NAME_CLEAN_PATTERN = "XIDE_FILE_NAME_CLEAN_PATTERN";

	/**
	 *  Match pattern to detect driver meta files
	 */
	const SHOW_EMPTY_FOLDERS = "XIDE_SHOW_EMPTY_FOLDERS";

	/**
	 * options dictionary for this class containing all data type values
	 *
	 * @var array
	 */
	public static $optionsDict = array
	(
		self::FILE_MATCH_PATTERN => XAPP_TYPE_STRING,
		self::FILE_NAME_CLEAN_PATTERN => XAPP_TYPE_STRING,
		self::SHOW_EMPTY_FOLDERS => XAPP_TYPE_BOOL
	);

	/**
	 * options mandatory map for this class contains all mandatory values
	 *
	 * @var array
	 */
	public static $optionsRule = array
	(
		self::FILE_NAME_CLEAN_PATTERN => 0,
		self::FILE_MATCH_PATTERN => 1,
		self::SHOW_EMPTY_FOLDERS => 0,

	);


	/**
	 * options default value array containing all class option default values
	 *
	 * @var array
	 */
	public $options = array
	(
		self::FILE_MATCH_PATTERN => '/\.js$/U',
		self::FILE_NAME_CLEAN_PATTERN => null,
		self::SHOW_EMPTY_FOLDERS => true
	);

	/**
	 * class constructor
	 * call parent constructor for class initialization
	 *
	 * @error 14601
	 * @param null|array|object $options expects optional options
	 */
	public function __construct($options = null)
	{
		//standard constructor
		xapp_set_options($options, $this);
	}


	/***
	 * @var XApp_VFS_Local
	 */
	protected $_vfs = null;

	/***
	 * @var array : current added nodes
	 */
	protected $_currentNodes = null;


	/***
	 * @param null $path
	 * @return array
	 */
	public function ls($path = null)
	{

		xapp_import('xapp.Directory.Utils');
		xapp_import('xapp.VFS.Local');

		//we listen to VFS messages
		Xapp_Hook::connect(XApp_VFS_Base::EVENT_ON_NODE_META_CREATED, $this, "_onItem");
		Xapp_Hook::connect(XApp_VFS_Base::EVENT_ON_NODE_ADD, $this, "_addNode");

		//create a single scope for now
		//prepare vfs
		$vfsItems = array();

		//mount one item at root, the system scope.
		$vfsItems[] = array(
			'name' => 'root',
			'path' => $path . DIRECTORY_SEPARATOR
		);


		//vfs ctor
		$this->_vfs = XApp_VFS_Local::factory($vfsItems);

		//vfs dir scanning options
		$directoryOptions = Array(
			XApp_Directory_Utils::OPTION_ONLY_DIRS => false,
			XApp_Directory_Utils::OPTION_ONLY_FILES => false,
			XApp_Directory_Utils::OPTION_RECURSIVE => true,
			XApp_Directory_Utils::OPTION_CLEAR_PATH => true
		);


		//prepare result
		$this->_currentNodes = array();
		$this->_currentNodes['items'] = array();

		//fire vfs
		$items = $this->_vfs->ls(
			'root/',
			true,
			Array(
				XApp_File_Utils::OPTION_DIR_LIST_FIELDS => XAPP_XFILE_SHOW_ISDIR,
				XApp_File_Utils::OPTION_DIR_LIST => $directoryOptions,
				XApp_Directory_Utils::OPTION_INCLUDE_LIST => array('*')
			)
		);

		//complete tree-store data
		$this->_currentNodes['identifier'] = 'path';
		$this->_currentNodes['label'] = 'name';


		return $this->_currentNodes;

	}

	/***
	 * VFS callback when a node will be added, return false if its not in
	 * our file match pattern
	 * @param $evt
	 */
	public function _addNode($evt)
	{

		if ($this->_vfs !== null &&   //need valid VFS
			is_array($evt) &&
			array_key_exists('item', $evt)
		) {

			$item = $evt['item'];

			$matchPattern = xo_get(self::FILE_MATCH_PATTERN);
			$path = $item->{XAPP_NODE_FIELD_NAME};

			//directory
			if ($item->{XAPP_NODE_FIELD_IS_DIRECTORY} === true) {

			} else {

				if (!preg_match($matchPattern, $path)) {
					return false;
				}
			}
		}
		return true;

	}

	/***
	 * VFS callback when a node has been found
	 * @param $evt
	 */
	public function _onItem($evt)
	{

		if ($this->_vfs !== null &&   //need valid VFS
			is_array($evt) &&
			array_key_exists('item', $evt)
		) {

			$item = $evt['item'];

			//clean meta data
			//pick ori name as path and swap with path!
			$item->{XAPP_NODE_FIELD_PATH} = '' . $item->{XAPP_NODE_FIELD_NAME};

			//track path
			$path = '' . $item->{XAPP_NODE_FIELD_NAME};

			//clean up file name
			$fileName = '' . $path;
			if (xo_has(self::FILE_NAME_CLEAN_PATTERN) && xo_get(self::FILE_NAME_CLEAN_PATTERN)) {

				$fileName = preg_replace(xo_get(self::FILE_NAME_CLEAN_PATTERN), '', $fileName);

			}
			$fileName = pathinfo($fileName, PATHINFO_FILENAME);


			//swap
			$item->{XAPP_NODE_FIELD_NAME} = $fileName;


			//file
			if ($item->{XAPP_NODE_FIELD_IS_DIRECTORY} === false) {
				$item->type = 'node';
				$this->_currentNodes['items'][] = $item;

				//dir
			} else {
				if ($item->{XAPP_NODE_FIELD_IS_DIRECTORY} === true) {

					//tree specific
					if (strpos($item->{XAPP_NODE_FIELD_PATH}, '/') !== false) {
						$item->type = 'node';
					} else {
						$item->type = 'leaf';

						$item->parentId = '';

					}

					//prepare directory scan options
					$directoryOptions = Array(
						XApp_Directory_Utils::OPTION_ONLY_FILES => false,
						XApp_Directory_Utils::OPTION_ONLY_DIRS => false,
						XApp_Directory_Utils::OPTION_RECURSIVE => false,
						XApp_Directory_Utils::OPTION_CLEAR_PATH => true
					);


					//enumerate
					$childList = $this->_vfs->ls(
						'root/' . $item->{XAPP_NODE_FIELD_PATH} . '/',
						true,
						Array(
							XApp_File_Utils::OPTION_DIR_LIST_FIELDS => XAPP_XFILE_SHOW_ISDIR,
							XApp_File_Utils::OPTION_DIR_LIST => $directoryOptions,
							XApp_File_Utils::OPTION_EMIT => false

						)
					);

					//store child references in item
					if ($childList && count($childList)) {

						//prepare array
						if (!xapp_property_exists($item, XAPP_NODE_FIELD_CHILDREN)) {
							$item->{XAPP_NODE_FIELD_CHILDREN} = array();
						}

						foreach ($childList as $child) {

							//build item unique reference key
							$key = $item->{XAPP_NODE_FIELD_PATH} . '/' . $child->name;

							array_push(
								$item->{XAPP_NODE_FIELD_CHILDREN},
								array(
									'_reference' => $key
								)
							);


							//$child->parentId = $item->{XAPP_NODE_FIELD_PATH};

						}
					} else {
						if (xo_get(self::SHOW_EMPTY_FOLDERS) === false) {
							//return null;
						} else {
							$item->{XAPP_NODE_FIELD_CHILDREN} = array();
						}
					}

					//add to our flat! list
					$this->_currentNodes['items'][] = $item;
				}
			}

			return $item;
		}
	}
}


?>