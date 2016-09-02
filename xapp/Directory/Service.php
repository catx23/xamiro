<?php
/**
 * @version 0.1.0
 *
 * @author https://github.com/mc007
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 * @package XApp\Directory
 */
xapp_import('xapp.VFS.Interface.Access');
xapp_import('xapp.File.Utils');
/***
 * Class XApp_Directory_Service lists files
 */
class XApp_Directory_Service extends XApp_Service{

    /***
     * Do auto rename for upload & uncompress
     */
    const AUTO_RENAME               = "XAPP_FILE_AUTO_RENAME";

    /***
     * Comma separated list of allowed file extensions
     */
    const INCLUDES_FILE_EXTENSIONS="includeList";

    /***
     * Comma separated list of forbidden file extensions
     */
    const EXCLUDED_FILE_EXTENSION="excludeList";

    /***
     * Local path on disc
     */
    const REPOSITORY_ROOT               = "XAPP_FILE_REPOSITORY_ROOT";

    /***
     * Maximum node length
     */
    const NODENAME_MAX_LENGTH           = "XAPP_FILE_NODENAME_MAX_LENGTH";

    /***
     * A standard temp directory for zipping
     */
    const FILE_TEMP_DIRECTORY           = "XAPP_FILE_TEMP_DIRECTORY";

    /***
     * Node creation mask
     */
    const CREATION_MASK                 = "XAPP_FILE_CREATION_MASK";

    /***
     * The file system, instance or class name
     */
    const FILE_SYSTEM                   = "XAPP_FILE_SYSTEM";

    /***
     * The file system's config
     */
    const FILE_SYSTEM_CONF              = "XAPP_FILE_SYSTEM_CONF";

    /***
     * The path to the mappings for the VFS
     */
    const VFS_CONFIG_PATH               = "XAPP_VFS_CONF_PATH";

	/***
	 * The path to the mappings for the VFS
	 */
	const VFS_CONFIG_PASSWORD           = "XAPP_VFS_CONF_PASSWORD";

    /***
     * The fields per node
     */
    const DEFAULT_NODE_FIELDS           = "XAPP_FILE_FIELDS";

    /***
     * Allowed upload extensions
     */
    const UPLOAD_EXTENSIONS             = "XAPP_FILE_UPLOAD_EXTENSIONS";

	/***
	 * Auth Delegate
	 */
	const AUTH_DELEGATE                 = "XAPP_AUTH_DELEGATE";

    /*******************************************************************************/
    /*  XApp options
    /*******************************************************************************/

    /**
     * options dictionary for this class containing all data type values
     *
     * @var array
     */
    public static $optionsDict = array
    (
        self::REPOSITORY_ROOT           => XAPP_TYPE_STRING,
        self::FILE_TEMP_DIRECTORY       => XAPP_TYPE_STRING,
        self::CREATION_MASK             => XAPP_TYPE_STRING,
        self::NODENAME_MAX_LENGTH       => XAPP_TYPE_INT,
        self::FILE_SYSTEM               => array(XAPP_TYPE_OBJECT, XAPP_TYPE_STRING),
        self::FILE_SYSTEM_CONF          => XAPP_TYPE_ARRAY,
        self::VFS_CONFIG_PATH           => XAPP_TYPE_STRING,
        self::DEFAULT_NODE_FIELDS       => XAPP_TYPE_INT,
        self::AUTO_RENAME               => XAPP_TYPE_BOOL,
        self::UPLOAD_EXTENSIONS         => XAPP_TYPE_STRING,
	    self::VFS_CONFIG_PASSWORD       => XAPP_TYPE_STRING,
	    self::AUTH_DELEGATE             => XAPP_TYPE_OBJECT

    );
    /**
     * options mandatory map for this class contains all mandatory values
     *
     * @var array
     */
    public static $optionsRule = array
    (
        self::REPOSITORY_ROOT           => 1,
        self::FILE_TEMP_DIRECTORY       => 0,
        self::CREATION_MASK             => 0,
        self::FILE_SYSTEM               => 1,
        self::FILE_SYSTEM_CONF          => 0,
        self::VFS_CONFIG_PATH           => 0,
        self::DEFAULT_NODE_FIELDS       => 0,
        self::AUTO_RENAME               => 0,
        self::UPLOAD_EXTENSIONS         => 0,
	    self::VFS_CONFIG_PASSWORD       => 0,
	    self::AUTH_DELEGATE             => 0,
    );
    /**
     * options default value array containing all class option default values
     *
     * @var array
     */
    public $options = array
    (
        self::REPOSITORY_ROOT           => null,
        self::FILE_TEMP_DIRECTORY       => '/tmp',
        self::CREATION_MASK             => '0755',
        self::NODENAME_MAX_LENGTH       => 255,
        self::FILE_SYSTEM               => null,
        self::FILE_SYSTEM_CONF          => null,
        self::VFS_CONFIG_PATH           => null,
        self::DEFAULT_NODE_FIELDS       => null,
        self::AUTO_RENAME               => true,
	    self::VFS_CONFIG_PASSWORD       => null,
        self::UPLOAD_EXTENSIONS          => 'js,css,less,bmp,csv,doc,gif,ico,jpg,jpeg,odg,odp,ods,odt,pdf,png,ppt,swf,txt,xcf,xls,json,js,md,xblox,tar',
	    self::AUTH_DELEGATE             => null
    );

    protected $_vfs;

    /**
     * @return XApp_VFS_Base
     */
    public function _getFileSystem(){
        return $this->_vfs;
    }

    /**
     * @return mixed
     */
    public function getFileSystem($mount=''){
        $mount=str_replace('/','',$mount);
	    $mount=str_replace('%','',$mount);
        if($this->_vfs==null){
            $this->createFileSystem($mount);
        }
        return $this->_vfs;
    }

    /**
     * class constructor
     * call parent constructor for class initialization
     *
     * @error 14601
     * @param null|array|object $options expects optional options
     */
    public function __construct($options = null)
    {
        xapp_set_options($options, $this);
    }

	/**
	 * @return array|mixed|null
	 */
	private function getFSOptions(){
		$fsOptions = xapp_has_option(self::FILE_SYSTEM_CONF) ? xapp_get_option(self::FILE_SYSTEM_CONF) : array();
		if($fsOptions && is_array($fsOptions)){
			//There is no mapping data in the VFS config, but we have a path to the mappings
			if(!xapp_has_option(XApp_VFS_Base::RESOURCES_DATA,$fsOptions) &&  xapp_has_option(self::VFS_CONFIG_PATH)){
				$vfsMappingPath = xapp_get_option(self::VFS_CONFIG_PATH);
				if(file_exists($vfsMappingPath)){
					$pass = null;
					if(xapp_has_option(self::VFS_CONFIG_PASSWORD)){
						$pass = xapp_get_option(self::VFS_CONFIG_PASSWORD);
					}
					$vfsMappingData = (object)XApp_Utils_JSONUtils::read_json($vfsMappingPath,'json',false,true,null,true,$pass);
					if($vfsMappingData){
						$fsOptions[XApp_VFS_Base::RESOURCES_DATA]=$vfsMappingData;
					}
				}
			}
		}
		return $fsOptions;
	}
	/**
	 * @return array|mixed|null
	 */
	public  function getFSResources(){
		$fsOptions = xapp_has_option(self::FILE_SYSTEM_CONF) ? xapp_get_option(self::FILE_SYSTEM_CONF) : array();
		if($fsOptions && is_array($fsOptions)){
			//There is no mapping data in the VFS config, but we have a path to the mappings
			if(!xapp_has_option(XApp_VFS_Base::RESOURCES_DATA,$fsOptions) &&  xapp_has_option(self::VFS_CONFIG_PATH)){
				$vfsMappingPath = xapp_get_option(self::VFS_CONFIG_PATH);
				if(file_exists($vfsMappingPath)){
					$pass = null;
					if(xapp_has_option(self::VFS_CONFIG_PASSWORD)){
						$pass = xapp_get_option(self::VFS_CONFIG_PASSWORD);
					}
					$vfsMappingData = (object)XApp_Utils_JSONUtils::read_json($vfsMappingPath,'json',false,true,null,true,$pass);
					if($vfsMappingData){
						return $vfsMappingData;
					}
				}
			}
		}
		return $fsOptions;
	}


	/***
	 *
	 * @param string    $type
	 * @param bool      $enabledOnly
	 * @return array|null
	 */
	public static function isRemote($name,$resourceData){
		$nameIn = ''. str_replace('/','',$name);
		if($resourceData!==null){
			$resourceItem = (array)xapp_object_find($resourceData,'.items',array('class='.'cmx.types.Resource','type='.XAPP_RESOURCE_TYPE_REMOTE_FILE_PROXY,'name='.$nameIn,'enabled='.true));
			if(count($resourceItem) && is_object($resourceItem[0])){
				return $resourceItem[0];
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
	public static function isLocal($name,$resourceData){
		$nameIn = ''. str_replace('/','',$name);
		if($resourceData!==null){
			$resourceItem = (array)xapp_object_find($resourceData,'.items',array('class='.'cmx.types.Resource','type='.XAPP_RESOURCE_TYPE_FILE_PROXY,'name='.$nameIn,'enabled='.true));
			if(count($resourceItem) && is_object($resourceItem[0])){
				return $resourceItem[0];
			}
		}
		return null;
	}

	/**
	 * @param $mount
	 * @return bool|string
	 */
	public function getResourceType($mount){
		if($this->isLocal($mount,$this->getFSResources())){
			return XAPP_RESOURCE_TYPE_FILE_PROXY;
		}elseif($this->isRemote($mount,$this->getFSResources())){
			return XAPP_RESOURCE_TYPE_REMOTE_FILE_PROXY;
		}
		return false;
	}

	/**
	 * @param $src
	 * @param $dst
	 * @return bool
	 */
	public function isRemoteOperation($src,$dst){
		$srcType = $this->getResourceType(XApp_Path_Utils::getMount($src));
		$dstType = $this->getResourceType(XApp_Path_Utils::getMount($dst));
		return $dst && $dstType && $srcType!=$dstType;
	}

	/**
	 * @param $src
	 * @param $dst
	 * @return bool
	 */
	public function isRemoteToRemoteOperation($src,$dst){
		$srcType = $this->getResourceType(XApp_Path_Utils::getMount($src));
		$dstType = $this->getResourceType(XApp_Path_Utils::getMount($dst));
		return $srcType==XAPP_RESOURCE_TYPE_REMOTE_FILE_PROXY && $dstType==XAPP_RESOURCE_TYPE_REMOTE_FILE_PROXY;
	}

	/**
	 * @param string $mount
	 * @throws Exception
	 */
    private function createFileSystem($mount='')
    {
        $_fs  = xapp_get_option(self::FILE_SYSTEM);//file system class
        //VFS instance already included
        if(is_object($_fs)){
            $this->_vfs=$_fs;

        }elseif(is_string($_fs)){//class name of the VFS

            if(class_exists($_fs)){

	            /***
                 * prepare file system options
                 */
                $fsOptions = xapp_has_option(self::FILE_SYSTEM_CONF) ? xapp_get_option(self::FILE_SYSTEM_CONF) : array();
                if($fsOptions && is_array($fsOptions)){

                    //There is no mapping data in the VFS config, but we have a path to the mappings
                    if(!xapp_has_option(XApp_VFS_Base::RESOURCES_DATA,$fsOptions) &&  xapp_has_option(self::VFS_CONFIG_PATH)){

                        $vfsMappingPath = xapp_get_option(self::VFS_CONFIG_PATH);
                        if(file_exists($vfsMappingPath)){

	                        $pass = null;
	                        if(xapp_has_option(self::VFS_CONFIG_PASSWORD)){
		                        $pass = xapp_get_option(self::VFS_CONFIG_PASSWORD);
	                        }else{

	                        }
                            $vfsMappingData = (object)XApp_Utils_JSONUtils::read_json($vfsMappingPath,'json',false,true,null,true,$pass);
                            if($vfsMappingData){
                                $fsOptions[XApp_VFS_Base::RESOURCES_DATA]=$vfsMappingData;
                            }
                        }
                    }
                }
                $remoteConfig = null;
                //switch to remove file system if needed
                if(strlen($mount)>0){
                    $remoteConfig = XApp_VFS_Local::isRemote($mount,$fsOptions[XApp_VFS_Base::RESOURCES_DATA]);
                    if($remoteConfig!=null){
                        xapp_import('xapp.VFS.Remote');
                        $_fs='XApp_VFS_Remote';
                    }else{
                    }
                }
                $this->_vfs=$_fs::instance($fsOptions);

	            $this->_vfs->{'remote'}=$remoteConfig!=null;

                if($remoteConfig){
                    $this->_vfs->initWithResource($remoteConfig);
                }
            }else
            {
                throw new Exception('VFS Class doesnt exists : ' . $_fs);
            }
        }
    }

    /***
     * Init will create the VFS instance
     */
    public function init(){

        /***
         * no file system yet
         */

        /*if($this->_getFileSystem()==null){
            $this->createFileSystem();
        }
        */
    }



    /***
     * Xapp_Rpc_Interface_Callable Impl. Before the actual call is being invoked
     */
    public function onBeforeCall(Xapp_Rpc_Server $server, Array $params){
        $this->init();
    }

    /***
     * Possibility to extend directory/file nodes with more data
     * @param $evt
     */

    public function _onItem($evt){

        if(is_array($evt) && $evt['item']){
            //$evt['item']->{XAPP_NODE_FIELD_IS_DIRTY}=false;
            return $evt['item'];
        }
    }

    /*******************************************************************************/
    /*  XApp VFS interface implementation
    /*******************************************************************************/

    /**
     * method set will write content into a file
     */
    public function set($mount,$path,$content=""){

	    if($mount==='__direct__'){
		    $pathAbs = XApp_Path_Utils::securePath(XApp_Path_Utils::normalizePath($path,true,false));
		    if(file_exists($pathAbs)){
			    return XApp_File_Utils::set($pathAbs,$content);
		    }else{
			    return false;
		    }
	    }
	    $vfs = $this->getFileSystem($mount);

        if(strlen($content)<1){$content=' ';}//
        $vfs->set(XApp_Path_Utils::normalizePath($mount),XApp_Path_Utils::securePath(XApp_Path_Utils::normalizePath($path,true,false)),$content);
    }

    /**
     * method mkFile creates a file
     */
    public function mkfile($mount,$path){
        $vfs = $this->getFileSystem($mount);
        return $vfs->mkFile(XApp_Path_Utils::normalizePath($mount),XApp_Path_Utils::securePath(XApp_Path_Utils::normalizePath($path,true,false)));
    }

    /**
     * method mkDir creates a directory
     */
    public function mkdir($mount,$path){

	    $vfs = $this->getFileSystem($mount);
	    return $vfs->mkDir(XApp_Path_Utils::normalizePath($mount),XApp_Path_Utils::securePath(XApp_Path_Utils::normalizePath($path,true,false)));
    }


    /**
     * Directory listing
     * @link http://localhost/xide/code/php/xide-php/server/xide/service.php?service=XApp_Directory_Service.ls&callback=asdF&path=/ws/&recursive=true
     * @param string $path
     * @param bool $recursive
     * @param null $options
     * @return array
     */
    public function ls($path='/',$options=null){


	    $recursive=false;
        $vfs = $this->_getFileSystem();
        if(!$options){
/*
            $options= xapp_has_option(self::DEFAULT_NODE_FIELDS) ? xapp_get_option(self::DEFAULT_NODE_FIELDS,$this) :  Array(
                XApp_File_Utils::OPTION_DIR_LIST_FIELDS=>
                    XAPP_XFILE_SHOW_MIME|
                    XAPP_XFILE_SHOW_SIZE|
                    XAPP_XFILE_SHOW_PERMISSIONS|
                    XAPP_XFILE_SHOW_ISREADONLY|
                    XAPP_XFILE_SHOW_ISDIR);
*/
            $options= Array(
                XApp_File_Utils::OPTION_DIR_LIST_FIELDS=>
                    XAPP_XFILE_SHOW_MIME|
                    XAPP_XFILE_SHOW_SIZE|
                    XAPP_XFILE_SHOW_PERMISSIONS|
                    XAPP_XFILE_SHOW_ISREADONLY|
                    XAPP_XFILE_SHOW_ISDIR
            );
        }

        $items = $vfs->ls(XApp_Path_Utils::normalizePath($path,false),$recursive,$options);

        return $items;

    }

    public function copyDirectory($srcDir,$dstDirectory,$options=Array(),$inclusionMask = Array(),$exclusionMask = Array(),&$error,&$success){}
	/**
	 * @link @link http://192.168.1.37:81/xapp-commander-standalone/docroot/index.php?debug=true&view=smdCall&service=XCOM_Directory_Service.get&mount=root&path=./index.php&callback=asd
	 * @param $path
	 * @param bool|false $attachment
	 * @param bool|true $send
	 * @param null $width
	 * @param null $time
	 * @param string $mount
	 * @return bool|string
	 */
    public function get($path,$attachment=false,$send=true,$width=null,$time=null,$mount=""){

		if ( base64_encode(base64_decode($path, true)) === $path){
		    $path = base64_decode($path);
	    } else {
		    echo '$data is NOT valid';
	    }

	    $path = urldecode ($path);
	    $mount = XApp_Path_Utils::getMount($path);
	    if($mount==='__direct__'){
		    $pathAbs = XApp_Path_Utils::securePath(XApp_Path_Utils::normalizePath(XApp_Path_Utils::getRelativePart($path),true,false));
		    if(file_exists($pathAbs)){
			    return XApp_File_Utils::get('',$pathAbs,array(
					    XApp_File_Utils::OPTION_SEND=>$send,
					    XApp_File_Utils::OPTION_RESIZE_TO=>$width,
					    XApp_File_Utils::OPTION_PREVENT_CACHE=>isset($time)
			    ));
		    }else{
			    return false;
		    }

	    }
	    $path = XApp_Path_Utils::getRelativePart($path);
	    $vfs = $this->getFileSystem($mount);

		$content = $vfs->get(
            XApp_Path_Utils::normalizePath($mount),
            XApp_Path_Utils::securePath(XApp_Path_Utils::normalizePath($path,true,false)),
            $attachment,
            array(
                XApp_File_Utils::OPTION_SEND=>$send,
	            XApp_File_Utils::OPTION_RESIZE_TO=>$width,
	            XApp_File_Utils::OPTION_PREVENT_CACHE=>isset($time)
            )
        );
        if($attachment===true){
            exit;
        }
        return $content;
    }

	public function get2($mount="",$path,$attachment=false,$send=true,$width=null,$time=null){


		$path = urldecode($path);

		$mount =  $mount ? $mount : XApp_Path_Utils::getMount($path);
		if($mount==='__direct__'){
			$pathAbs = XApp_Path_Utils::securePath(XApp_Path_Utils::normalizePath(XApp_Path_Utils::getRelativePart($path),true,false));
			if(file_exists($pathAbs)){
				return XApp_File_Utils::get('',$pathAbs,array(
					XApp_File_Utils::OPTION_SEND=>$send,
					XApp_File_Utils::OPTION_RESIZE_TO=>$width,
					XApp_File_Utils::OPTION_PREVENT_CACHE=>isset($time)
				));
			}else{
				return false;
			}

		}
		$vfs = $this->getFileSystem($mount);
		$content = $vfs->get(
			XApp_Path_Utils::normalizePath($mount),
			XApp_Path_Utils::securePath(XApp_Path_Utils::normalizePath($path,true,false)),
			$attachment,
			array(
				XApp_File_Utils::OPTION_SEND=>$send,
				XApp_File_Utils::OPTION_RESIZE_TO=>$width,
				XApp_File_Utils::OPTION_PREVENT_CACHE=>isset($time)
			)
		);
		if($attachment===true){
			exit;
		}
		return $content;
	}

    public function deleteDirectory($path){
        $vfs = $this->_getFileSystem();
        /*$realPath = XApp_Path_Utils::normalizePath($mount) . XApp_Path_Utils::securePath(XApp_Path_Utils::normalizePath($path,true,false));*/
        //$vfs->deleteDirectory(XApp_Path_Utils::normalizePath($mount) . XApp_Path_Utils::securePath(XApp_Path_Utils::normalizePath($path,true,false)));
    }

    public function deleteFile($path){
        $vfs = $this->_getFileSystem();
        $success=array();
        $error=array();
        $vfs->deleteFile(XApp_Path_Utils::securePath(XApp_Path_Utils::normalizePath($path,true,false)),null,$error,$success);
    }

	public function createToken($what){
		$authDelegate = xapp_has_option(self::AUTH_DELEGATE) ? xapp_get_option(self::AUTH_DELEGATE) : null;
		$result = '';
		if($authDelegate){
			$result =  $authDelegate->createToken($what);
		}
		return $result;


	}

}