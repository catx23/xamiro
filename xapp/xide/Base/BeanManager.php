<?php
/**
 * @version 0.1.0
 *
 * @author Luis Ramos
 * @author Guenter Baumgart
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 * @package XApp\xide\Controller
 */

xapp_import("xapp.Commons.Entity");
xapp_import('xapp.Utils.Strings');
xapp_import('xapp.xide.Base.Scoped');
xapp_import('xapp.xide.Base.Manager');
xapp_import('xapp.xide.commons.BeanException');

/***
 * Class XIDE_BeanManager
 */
class XIDE_BeanManager extends XIDE_Manager
{
    /////////////////////////////////////////////////////////////////////////////
    //
    //  Bean Manager Commons.
    //
    /////////////////////////////////////////////////////////////////////////////
    public function init(){
        xapp_import('xapp.Directory.Utils');
        xapp_import('xapp.VFS.Local');
        xapp_import('xapp.xide.Utils.FileTree');
    }
    /////////////////////////////////////////////////////////////////////////////
    //
    //  Bean impl.
    //
    /////////////////////////////////////////////////////////////////////////////
    /**
     * @param $scope
     * @param $path
     * @return array
     * @throws ErrorException
     */
    public function removeGroup($scope,$path){
        xapp_import('xapp.Directory.Utils');
        xapp_import('xapp.Path.Utils');
        xapp_import('xapp.VFS.Local');
        xapp_import('xapp.Commons.Exceptions');
        $fullPath = $this->resolvePath($scope,$path,'__ROOT__',true,false);
        $errors=array();
        $success=array();
        if(is_dir($fullPath)){
            XApp_File_Utils::deleteDirectoryEx(XApp_Path_Utils::securePath($fullPath),null,null,null,$errors,$success);
        }
        return $errors;
    }

    /**
     * @param $scope
     * @param $path
     * @return string
     * @throws ErrorException
     */
    public function createGroup($scope,$path){
        xapp_import('xapp.Directory.Utils');
        xapp_import('xapp.Path.Utils');
        xapp_import('xapp.VFS.Local');
        xapp_import('xapp.Commons.Exceptions');
        $fullPath = $this->resolvePath($scope,$path,null,true,false);
        return XApp_File_Utils::mkDir(XApp_Path_Utils::securePath($fullPath));
    }


    /**
     * @param $scope
     * @param $path
     * @return null|string
     * @throws XApp_File_Exception
     */
    public function getContent($scope,$path){
        xapp_import('xapp.Directory.Utils');
        xapp_import('xapp.Path.Utils');
        xapp_import('xapp.VFS.Local');
        xapp_import('xapp.Commons.Exceptions');

        $scope = $this->getScope($scope);
        //root
        $root = $scope->resolveAbsolute('__ROOT__') .  DIRECTORY_SEPARATOR;
        $fullPath = $root . DIRECTORY_SEPARATOR .$path;

        if(!is_readable($fullPath)){
            throw new XApp_File_Exception(XAPP_TEXT_FORMATTED('CAN_NOT_READ_FILE',array(basename($fullPath)),55100));
        }
        if(file_exists($fullPath)){
            if(($content = file_get_contents($fullPath)) !== false){
                return $content;
            }
        }else{
            throw new XApp_File_Exception(XAPP_TEXT_FORMATTED('CAN_NOT_FIND_FILE',array(basename($fullPath)),55100));
        }

        return null;
    }

    /**
     * @param $scopeName
     * @param $path
     * @param $content
     * @return bool|null
     * @throws ErrorException
     * @throws Xapp_Util_Exception_Storage
     */
    public function setContent($scopeName,$path,$content){
        xapp_import('xapp.Directory.Utils');
        xapp_import('xapp.Path.Utils');
        xapp_import('xapp.VFS.Local');
        xapp_import('xapp.Commons.Exceptions');
        $scope = $this->getScope($scopeName);
	    if($scope==null){
		    return false;
	    }
        $return =null;
        $fullPath = XApp_Path_Utils::normalizePath($this->resolvePath($scopeName,DIRECTORY_SEPARATOR . $path,null,true,false),true,false);
        if(!file_exists($fullPath)){
            XApp_File_Utils::createEmptyFile($fullPath);
        }
        $content=xapp_prettyPrint($content);
        if(file_exists($fullPath)){

            if(!is_writeable($fullPath))
            {
                throw new Xapp_Util_Exception_Storage(vsprintf('File: %s is not writable', array(basename($fullPath))), 1640102);
            }else{

                //write out
                $fp=fopen($fullPath,"w");
                fputs ($fp,$content);
                fclose($fp);
                clearstatcache(true, $fullPath);
                $return=true;
            }

        }else{
            throw new Xapp_Util_Exception_Storage('unable to write storage to file  :  ' . $path . ' at : ' . $fullPath, 1640104);
        }

        return $return;
    }
    /***
     * @param string $scopeName
     * @param string $driverPathRelative
     * @param $dataPath
     * @param $query
     * @param $value
     * @return mixed
     */
    public function updateMetaData($scopeName='system',
                                   $driverPathRelative='Denon/Denon Base.meta.json',
                                   $dataPath='/inputs',
                                   $query = Array(),
                                   $value = Array())
    {

        $this->init();
        $scope = $this->getScope($scopeName);
        xapp_import('xapp.Store.*');
        xapp_import('xapp.xide.Store.*');
        $driver = $this->getMetaData($scope,$driverPathRelative);
        return $this->updateCI($driver['path'],$dataPath,$query,$value);
    }

    /////////////////////////////////////////////////////////////////////////////
    //
    //  Store related, unused
    //
    /////////////////////////////////////////////////////////////////////////////
    /**
     * Initialize user data
     *
     * @param $allUserData  :   object with Users, Roles and Permissions
     */
    public function initWithData($allUserData)
    {

    }

    /***
     * Parse options and init the store
     * @param bool $force does force to re-create the store
     * @return bool
     */
    public function initStore($force=false){

        //skip
        if($force === false && $this->_store){
            return true;
        }

        //Try with store data first
        if(xo_has(self::STORE_DATA) && is_object(xo_get(self::STORE_DATA))){
            $this->initWithData(xo_get(self::STORE_DATA));
            return true;
        }

        //No store data provided from outside, use the store class

        if(xo_has(self::STORE_CLASS) && xo_get(self::STORE_CLASS)){

            $_storeClz = xo_get(self::STORE_CLASS);

            //check its an instance already :
            if(is_object($_storeClz)){
                $this->_store = $_storeClz;
                return true;
            }//its a class name
            elseif(is_string($_storeClz) && class_exists($_storeClz)){
                $_ctrArgs = xo_has(self::STORE_CONF) ? xo_get(self::STORE_CONF) : array();
                $this->_store = new $_storeClz($_ctrArgs);
                return true;
            }
        }
        return false;
    }

    /////////////////////////////////////////////////////////////////////////////////////////
    //
    //  Utils
    //
    ////////////////////////////////////////////////////////////////////////////////////////


    /**
     * @param $scopeName
     * @param $path
     * @param string $rootVariable
     * @param bool|true $secure
     * @param bool|true $merge
     * @return mixed|string
     * @throws ErrorException
     */
    public function resolvePath($scopeName,$path,$rootVariable='__ROOT__',$secure=true,$merge=true){

        xapp_import('xapp.Path.Utils');
        $scope = $this->getScope($scopeName);
        $rootVariable = $rootVariable ?: '__ROOT__';
        $path = XApp_Path_Utils::securePath($path);
        if($scope){
            $root = $scope->resolveAbsolute($rootVariable) .  DIRECTORY_SEPARATOR;
            if($secure===true){
                if($merge==true){
                    return XApp_Path_Utils::securePath(XApp_Path_Utils::merge($root,XApp_Path_Utils::normalizePath($path)));
                }else{
                    return XApp_Path_Utils::securePath($root . XApp_Path_Utils::normalizePath($path,false,false));
                }
            }else{
                return XApp_Path_Utils::merge($root,XApp_Path_Utils::normalizePath($path,false,false));
            }
        }else{
	        throw new ErrorException("scope:" .  $scopeName . "does not exist", 1390101);
        }
    }

    /////////////////////////////////////////////////////////////////////////////
    //
    //  CI persistence impl.
    //
    /////////////////////////////////////////////////////////////////////////////

    /**
     * @param $storePath
     * @param $dataPath
     * @param $dataQuery
     * @param $newValue
     * @return mixed
     */
    public function updateCI($storePath,
                             $dataPath,
                             $dataQuery,
                             $newValue
    ){
        xapp_import('xapp.Store.*');
        xapp_import('xapp.xide.Store.*');
        $storeOptions = array(
            XApp_Store::READER_CLASS            =>'XIDE_CIStore_Delegate',
            XApp_Store::WRITER_CLASS            =>'XIDE_CIStore_Delegate',
            XApp_Store::PRIMARY_KEY             =>'inputs',
            XApp_Store::IDENTIFIER              =>'',
            XApp_Store::CONF_FILE               =>$storePath,
	        XApp_Store::CONF_PASSWORD           =>'');

        $store = new XApp_Store($storeOptions);
        $data = $store->set('',$dataPath,$dataQuery,$newValue);
        return $data;
    }

    /**
     * @param $scope XIDE_Scope
     * @param $path String
     * @return array|null
     */
    public function getMetaData($scope,$path){

        $root = $scope->resolveAbsolute('__ROOT__') .  DIRECTORY_SEPARATOR;
        $filePath = $root . $path;
	    $metaContent = XApp_Utils_JSONUtils::read_json( $filePath ,'json',false,true,null,true,null);
        if($metaContent){
            return array(
                'path'=>$filePath,
                'content'=>$metaContent,
            );
        }
        return null;
    }
}