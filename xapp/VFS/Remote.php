<?php
/**
 * @version 0.1.0
 *
 * @author https://github.com/mc007
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */

xapp_import('xapp.VFS.Base');
xapp_import('xapp.Utils.Strings');
xapp_import('xapp.Utils.SystemTextEncoding');
/***
 * Class XApp_VFS_Local implements the access interface for a local file system
 */
class XApp_VFS_Remote extends XApp_VFS_Base implements Xapp_VFS_Interface_Access
{

    //////////////////////////////////////////////////////////////////
    //
    //  PHP League additions
    //
    //////////////////////////////////////////////////////////////////
    /**
     * @var League\Flysystem\MountManager
     */
    protected $mountManager=null;
    protected $config=null;
    protected $pathPrefix='';
	protected $adapter=null;



    /**
     * @return League\Flysystem\MountManager
     */
    public  function getMountManager(){
        return $this->mountManager;
    }

    public  function getPathPrefix(){
        return $this->pathPrefix;
    }
	public  function getAdapter(){
		return $this->adapter;
	}


    /**
     * @param $mount
     * @return League\Flysystem\Filesystem
     */
    public function getFilesystem($mount){
        return $this->mountManager->getFilesystem($mount);
    }
    ////////////////////////////////////////////////////////////////////////////
    //
    //  Xapp_VFS_Interface_Access implementation
    //
    ////////////////////////////////////////////////////////////////////////////

    /***
     * @param $filePath
     * @param $filename_new
     * @param null $dest
     * @param $errors
     */

    public function rename($mount,$filePath, $filename_new, $dest = null,&$errors)
    {
        $filename_new=XApp_Path_Utils::sanitizeEx(XApp_SystemTextEncoding::magicDequote($filename_new), XApp_Path_Utils::SANITIZE_HTML_STRICT);
        $filename_new= substr($filename_new, 0, xapp_get_option(self::NODENAME_MAX_LENGTH,$this));
        $old=$this->toRealPath($mount . DIRECTORY_SEPARATOR . $filePath);

        if(!is_writable($old))
        {
            $errors[]=XAPP_TEXT_FORMATTED('FILE_NOT_WRITEABLE',array($old),55100);
            return;
        }
        if($dest == null)
            $new=dirname($old)."/".$filename_new;
        else{
            $new = $this->toRealPath($mount . DIRECTORY_SEPARATOR . $dest);
        }



        if($filename_new=="" && $dest == null)
        {
            $errors[]=XAPP_TEXT_FORMATTED('DIRECTORY_NOT_WRITEABLE',array($old),55100);
            return;
        }
        if(file_exists($new))
        {
            $errors[]=XAPP_TEXT_FORMATTED('FILE_EXISTS',array($filename_new),55100);
        }
        if(!file_exists($old))
        {
            $errors[]=XAPP_TEXT_FORMATTED('CAN_NOT_FIND_FILE',array(basename($filePath)),55100);
            return;
        }
        rename($old,$new);
    }
    /**
     * Test for mount point and return mount meta data
     *
     * @return mount point data
     */
    public function hasMount($name,$enabledOnly=true){
        return $this->getResource($name,$enabledOnly);
    }

    /**
     * method set will write content into a file
     * @param $mount
     * @param $relativePath
     * @param $content
     * @return mixed|void
     */
    public function set($mount,$relativePath,$content){

        $relativePath = XApp_Path_Utils::sanitizeEx($relativePath);
        $mount = XApp_Path_Utils::normalizePath(XApp_Path_Utils::sanitizeEx($mount),true,true);
        $mount = XApp_Path_Utils::getMount($mount);
        $fsPath = XApp_Path_Utils::sanitizeEx($mount . '://' .$relativePath);
        return $this->getMountManager()->update($fsPath,$content);
    }

    /***
     * @param $mount
     * @param $relativePath
     * @return null|string
     */
    function mkFile($mount, $relativePath){

        return XApp_File_Utils::createEmptyFile(XApp_Path_Utils::securePath(self::toAbsolutePath($mount) . DIRECTORY_SEPARATOR . $relativePath));
    }

    /***
     * @param $mount
     * @param $relativePath
     * @return null|string
     */
    function mkDir($mount, $relativePath){

	    $mount = XApp_Path_Utils::getMount(XApp_Path_Utils::normalizePath($mount,true,false));

	    $relativePath = XApp_Path_Utils::normalizePath($relativePath,true,false);

	    $remotePath = $mount . '://'.$relativePath;
	    $remotePath = $this->cleanSlashesRemote($remotePath);

	    $result = true;
	    if($mount==='dropbox'){
		    $result = $this->getAdapter()->client->createFolder($relativePath);
	    }else{
		    $result= $this->getMountManager()->createDir($remotePath);
	    }
	    return $result;
    }

    /***
     * Method get returns the file's content, or sends it via echo
     * @param $mount
     * @param $relativePath
     * @param bool $attachment
     * @param array $options
     * @link http://192.168.1.37:81/xapp-commander-standalone/docroot/index.php?debug=true&view=smdCall&service=XCOM_Directory_Service.get&mount=ftp&path=./xcom/web.config.txt&callback=asd&send=true&attachment=false
     * @return mixed|string
     */
    public function get($mount,$relativePath,$attachment=false,$options=Array()){

        $relativePath = XApp_Path_Utils::sanitizeEx($relativePath);
        $mount = XApp_Path_Utils::normalizePath(XApp_Path_Utils::sanitizeEx($mount),true,true);
        $mount = str_replace('/','',$mount);
        $fsPath = XApp_Path_Utils::sanitizeEx($mount . '://' .$relativePath);
        $content = $this->getMountManager()->read($fsPath);
        if($content){
            $options=array_merge($options,Array(
                XApp_File_Utils::OPTION_AS_ATTACHMENT=>$attachment
            ));
            return $this->send(basename($relativePath),$content,$options);
        }

    }


    /***
     * ls, directory listing, '/' means all mounted directories are visible
     *
     * output format for an item :
     *
     * item['isReadOnly]=
     * item['isDirectory]=
     * item['owner']=
     * item['mime']=
     * item['size']=
     * item['permissions']=
     *
     * Look in src/server/xfile/File or src/server/stores/cbtree/cbTreeFileStoreStandalone.
     * There are many methods about owner,permissions,.... Copy them into File/Utils
     *
     * options['fields'] = isReadOnly|mime|owner,... specifies the fields we want in the output!
     * Define those fields as string constants in File/Utils.php for now.
     *
     *
     *
     * @param string $path
     * @param $recursive
     * @param $options
     * @return array
     */
    public function ls($path='/',$recursive=false,$options=Array()) {

        xapp_import('xapp.Xapp.Hook');

        //1.extract mount name from path
        $parsed = parse_url($path);
        $path   = $parsed['path'];
        $path_parts = explode('/', $path);
        $basePath = $path_parts[1];

        //2. get the FS Adapter from the mount manager
        $fs = $this->getFilesystem($basePath);

        //3. build the internal path, it should look now like  'ftp://httpdocs'
        $fsPath = $path_parts[1] . '://';
        $fsPath .=$this->getPathPrefix() . '/';
        for($i = 2 ; $i<count($path_parts);$i++)
        {
            $fsPath.='/' .$path_parts[$i];
        }

        //error_log($fsPath);
        $fsPath=str_replace(':////','://',$fsPath);
        $fsPath=str_replace(':///','://',$fsPath);
        $fsPath=str_replace('///','/',$fsPath);

        // Default options
        if (!isset($options[XApp_File_Utils::OPTION_DIR_LIST_FIELDS])){
            $options[XApp_File_Utils::OPTION_DIR_LIST_FIELDS]=0;
        }

        // Default option : emit new node
        if (!isset($options[XApp_File_Utils::OPTION_EMIT])){
            $options[XApp_File_Utils::OPTION_EMIT]=true;
        }

        //default scan options
        $get_list_options=Array(
            XApp_Directory_Utils::OPTION_CLEAR_PATH=>true,
            XApp_Directory_Utils::OPTION_RECURSIVE=>$recursive

        );
        //overwrite from options
        if (isset($options[XApp_File_Utils::OPTION_DIR_LIST])){
            $get_list_options = $options[XApp_File_Utils::OPTION_DIR_LIST];
        }

        //default include & exclude list
        $inclusionMask=XApp_File_Utils::defaultInclusionPatterns();
        $exclusionMask=XApp_File_Utils::defaultExclusionPatterns();

        //overwrite include from options
        if (isset($options[XApp_Directory_Utils::OPTION_INCLUDE_LIST])){
            $inclusionMask = $options[XApp_Directory_Utils::OPTION_INCLUDE_LIST];
        }

        //overwrite excludes from options
        if (isset($options[XApp_Directory_Utils::OPTION_EXCLUDE_LIST])){
            $exclusionMask = $options[XApp_Directory_Utils::OPTION_EXCLUDE_LIST];
        }

        $list = $this->_lsRemote($this->getMountManager(),$fsPath);
        //$list=$this->getFilteredDirList($path,$inclusionMask,$exclusionMask,$get_list_options);
        $ret_list=Array();
        //xapp_dump($list);

        /***
         * Use 'readOnly' from the paths's resource information
         */
        $isReadOnly = null;
        /*
        if (($options[XApp_File_Utils::OPTION_DIR_LIST_FIELDS] & XAPP_XFILE_SHOW_ISREADONLY)==XAPP_XFILE_SHOW_ISREADONLY){
            error_log('is read only');
            $instance = self::instance();
            $resource = $instance->toResource($path);
            if($resource!==null && xapp_property_exists($resource,'readOnly')){
                $isReadOnly=$resource->{XAPP_NODE_FIELD_READ_ONLY};
            }
        }
        */

        foreach($list as $itemIn) {
            $item=new stdClass();

            $item->{XAPP_NODE_FIELD_NAME}=$itemIn[XAPP_NODE_FIELD_NAME];
            $item->{XAPP_NODE_FIELD_IS_DIRECTORY}=$itemIn['type']==='dir';

            if ($path!="/"){

                if(array_key_exists('size',$itemIn)){
                    $item->{XAPP_NODE_FIELD_SIZE}=XApp_File_Utils::formatSizeUnits($itemIn[XAPP_NODE_FIELD_SIZE]);
                }
                if(array_key_exists(XAPP_NODE_FIELD_TIME,$itemIn)){
                    $item->{XAPP_NODE_FIELD_TIME}=$itemIn[XAPP_NODE_FIELD_TIME];
                }

                if($itemIn['type']==='file'){
                    $item->{XAPP_NODE_FIELD_MIME}=XApp_File_Utils::guessMime($itemIn[XAPP_NODE_FIELD_NAME]);
                }

                $item->{XAPP_NODE_FIELD_PERMISSIONS}='Unknown';
                $item->{XAPP_NODE_FIELD_OWNER}=array(
                    'user'=>array('name'=>'Unknown'),
                    'group'=>array('name'=>'Unknown'),
                );

                //tell plugins, if anyone doesnt want the item, skip it
                $addItem=Xapp_Hook::trigger(self::EVENT_ON_NODE_ADD,array('item'=>$item));
                if($addItem===false){
                    continue;
                }

                //tell plugins, if anyone doesnt want the item, skip it
                if($options[XApp_File_Utils::OPTION_EMIT]===true){

                    $item=Xapp_Hook::trigger(self::EVENT_ON_NODE_META_CREATED,array('item'=>$item));
                    if($item===null){
                        continue;
                    }
                }

                //now overwrite readOnly flag
                if ($isReadOnly!=null && ($options[XApp_File_Utils::OPTION_DIR_LIST_FIELDS] & XAPP_XFILE_SHOW_ISREADONLY)==XAPP_XFILE_SHOW_ISREADONLY){
                    $item->{XAPP_NODE_FIELD_READ_ONLY}=$isReadOnly;
                }

                //tell plugins
                Xapp_Hook::trigger(self::EVENT_ON_NODE_ADDED,array('item'=>$item));
            }

            $ret_list[]=$item;
        }

        return $ret_list;
    }

	public function delete($selection,$options=Array(),$inclusionMask = Array(),$exclusionMask = Array(),&$error,&$success){

        $mountManager = $this->getMountManager();
        foreach ($selection as $selectedFile)
        {
	        $result = $mountManager->delete($this->toFlyPath($selectedFile));
	        if(!$result){
		        $error[] = XAPP_TEXT_FORMATTED('FAILED_TO_DELETE',array(XApp_SystemTextEncoding::toUTF8($selectedFile)));
	        }
        }
        return $error;
    }

    /**
     *
     * Copies $srcDir into $dstDirectory across multiple mount points
     *
     * @param $srcDir : expects sanitized absolute directory
     * @param $dstDirectory : expects sanitized absolute directory, if it doesn't exists, create it!
     * @param array $options : [recursive (true/false) default true, timeout (seconds) default 60, overwriteModus : XAPP_XFILE_OVERWRITE_NONE | XAPP_XFILE_OVERWRITE_ALL | XAPP_XFILE_OVERWRITE_IF_SIZE_DIFFERS
     * @param array|string $inclusionMask : null means all, if its a string : it must compatible to a scandir query, if its a string its a regular expression
     * @param array|string $exclusionMask : null means all, otherwise it must compatible to a scandir query,if its a string its a regular expression
     * @param $error : a pointer to an array reference, please track all errors and don't abort! Check __copyOrMoveFile below how to write the error messages right!
     * @param $success : track all copied items here
     */
    public function move($selection,$dst,$options=Array(),$inclusionMask = Array(),$exclusionMask = Array(),&$error,&$success,$mode){

        $dstDirectory=$this->toRealPath($dst);
        if(file_exists($dstDirectory) &&   !is_writable($dstDirectory))
        {
            throw new Xapp_XFile_Exception(XAPP_TEXT_FORMATTED('DIRECTORY_NOT_WRITEABLE',array($dstDirectory),55100));
        }

        foreach ($selection as $selectedFile)
        {

            $itemPath = $this->toRealPath($selectedFile);

            if(is_dir($itemPath)){

                $dstFile = $dstDirectory.DIRECTORY_SEPARATOR.basename($itemPath);

                XApp_File_Utils::moveDirectoryEx(
                    XApp_Directory_Utils::normalizePath($itemPath,false),
                    XApp_Directory_Utils::normalizePath($dstFile,false),
                    Array(XApp_File_Utils::OPTION_RECURSIVE=>true,
                        XApp_File_Utils::OPTION_CONFLICT_MODUS=>$mode),
                    $inclusionMask,
                    $exclusionMask,
                    $error,
                    $success
                );
            }else if(is_file($itemPath)){

                $destFile = $dstDirectory.DIRECTORY_SEPARATOR.basename($itemPath);
                if(!is_readable($itemPath)){
                    $error[] = XAPP_TEXT_FORMATTED('CAN_NOT_READ_FILE',array(basename($itemPath)));
                    continue;
                }

                // auto rename file
                if(file_exists($destFile)){
                    $base = basename($destFile);
                    $ext='';
                    $dotPos = strrpos($base, ".");
                    if($dotPos>-1){
                        $radic = substr($base, 0, $dotPos);
                        $ext = substr($base, $dotPos);
                    }

                    $i = 1;
                    $newName = $base;
                    while (file_exists($dstDirectory."/".$newName)) {
                        $suffix = "-$i";
                        if(isSet($radic)) $newName = $radic . $suffix . $ext;
                        else $newName = $base.$suffix;
                        $i++;
                    }
                    $destFile = $dstDirectory."/".$newName;
                }

                if(file_exists($destFile)){
                    unlink($destFile);
                }else{

                }

                $res = rename($itemPath, $destFile);

                $success[] = XAPP_TEXT('THE_FILE')." ".XApp_SystemTextEncoding::toUTF8(basename($itemPath))." ".XAPP_TEXT('HAS_BEEN_MOVED')." ".XApp_SystemTextEncoding::toUTF8($dst);
            }
        }
        return $error;
    }

    /**
     *
     * Copies $srcDir into $dstDirectory across multiple mount points
     *
     * @param $srcDir : expects sanitized absolute directory
     * @param $dstDirectory : expects sanitized absolute directory, if it doesn't exists, create it!
     * @param array $options : [recursive (true/false) default true, timeout (seconds) default 60, overwriteModus : XAPP_XFILE_OVERWRITE_NONE | XAPP_XFILE_OVERWRITE_ALL | XAPP_XFILE_OVERWRITE_IF_SIZE_DIFFERS
     * @param array|string $inclusionMask : null means all, if its a string : it must compatible to a scandir query, if its a string its a regular expression
     * @param array|string $exclusionMask : null means all, otherwise it must compatible to a scandir query,if its a string its a regular expression
     * @param $error : a pointer to an array reference, please track all errors and don't abort! Check __copyOrMoveFile below how to write the error messages right!
     * @param $success : track all copied items here
     */
    public function copy($selection,$dst,$options=Array(),$inclusionMask = Array(),$exclusionMask = Array(),&$error,&$success,$mode){

        $dstDirectory=$this->toRealPath($dst);
        if(file_exists($dstDirectory) &&   !is_writable($dstDirectory))
        {
            throw new Xapp_XFile_Exception(XAPP_TEXT_FORMATTED('DIRECTORY_NOT_WRITEABLE',array($dstDirectory),55100));
        }

        foreach ($selection as $selectedFile)
        {

            $itemPath = $this->toRealPath($selectedFile);

            if(is_dir($itemPath)){

                $dstFile = $dstDirectory.DIRECTORY_SEPARATOR.basename($itemPath);

                XApp_File_Utils::copyDirectory(
                    XApp_Directory_Utils::normalizePath($itemPath,false),
                    XApp_Directory_Utils::normalizePath($dstFile,false),
                    Array(XApp_File_Utils::OPTION_RECURSIVE=>true,
                        XApp_File_Utils::OPTION_CONFLICT_MODUS=>$mode),
                    $inclusionMask,
                    $exclusionMask,
                    $error,
                    $success
                );
            }else if(is_file($itemPath)){

                $destFile = $dstDirectory.DIRECTORY_SEPARATOR.basename($itemPath);
                if(!is_readable($itemPath)){
                    $error[] = XAPP_TEXT_FORMATTED('CAN_NOT_READ_FILE',array(basename($itemPath)));
                    continue;
                }

                // auto rename file
                if(file_exists($destFile)){
                    $base = basename($destFile);
                    $ext='';
                    $dotPos = strrpos($base, ".");
                    if($dotPos>-1){
                        $radic = substr($base, 0, $dotPos);
                        $ext = substr($base, $dotPos);
                    }

                    $i = 1;
                    $newName = $base;
                    while (file_exists($dstDirectory."/".$newName)) {
                        $suffix = "-$i";
                        if(isSet($radic)) $newName = $radic . $suffix . $ext;
                        else $newName = $base.$suffix;
                        $i++;
                    }
                    $destFile = $dstDirectory."/".$newName;
                }

                try{
                    copy($itemPath, $destFile);
                }catch (Exception $e){
                    $error[] = $e->getMessage();
                    return $error;
                }
                $success[] = XAPP_TEXT('THE_FILE')." ".XApp_SystemTextEncoding::toUTF8(basename($itemPath))." ".XAPP_TEXT('HAS_BEEN_COPIED')." ".XApp_SystemTextEncoding::toUTF8($dst);
            }
        }
        //xapp_cdump('success',$success);
        /*XApp_File_Utils::copyDirectoryEx($srcDir,$dstDirectory,$options,$inclusionMask,$exclusionMask,$error,$success);*/
        return $error;
    }

    /**
     *
     * Copies $srcDir into $dstDirectory across multiple mount points
     *
     * @param $srcDir : expects sanitized absolute directory
     * @param $dstDirectory : expects sanitized absolute directory, if it doesn't exists, create it!
     * @param array $options : [recursive (true/false) default true, timeout (seconds) default 60, overwriteModus : XAPP_XFILE_OVERWRITE_NONE | XAPP_XFILE_OVERWRITE_ALL | XAPP_XFILE_OVERWRITE_IF_SIZE_DIFFERS
     * @param array|string $inclusionMask : null means all, if its a string : it must compatible to a scandir query, if its a string its a regular expression
     * @param array|string $exclusionMask : null means all, otherwise it must compatible to a scandir query,if its a string its a regular expression
     * @param $error : a pointer to an array reference, please track all errors and don't abort! Check __copyOrMoveFile below how to write the error messages right!
     * @param $success : track all copied items here
     */
    public function copyDirectory($srcDir,$dstDirectory,$options=Array(),$inclusionMask = Array(),$exclusionMask = Array(),&$error,&$success){
        if (($srcDir=="/") || ($dstDirectory=="/")) {
            $error[]=XAPP_TEXT_FORMATTED("CAN_NOT_COPY_MOUNT_POINTS");
        } else {
            $srcDir=$this->toRealPath($srcDir);
            $dstDirectory=$this->toRealPath($dstDirectory);
            XApp_File_Utils::copyDirectoryEx($srcDir,$dstDirectory,$options,$inclusionMask,$exclusionMask,$error,$success);
        }
    }

    /**
     * @param $path
     * @param array $options
     * @param array $inclusionMask
     * @param array $exclusionMask
     * @param $error
     * @param $success
     * @return mixed|void
     */
    public function deleteDirectory($path,$options=Array(),$inclusionMask = Array(),$exclusionMask = Array(),&$error,&$success) {
        if ($path=="/") {
            $error[]=XAPP_TEXT_FORMATTED("DIRECTORY_NOT_WRITEABLE",Array($path));
        } else {
            $path=$this->toRealPath($path);
            XApp_File_Utils::deleteDirectoryEx($path,$options,$inclusionMask,$exclusionMask,$error,$success);
        }
    }

    /**
     * @param $path
     * @param array $options
     * @param $error
     * @param $success
     */
    public function deleteFile($path,$options=Array(),&$error,&$success){
        if ($path=="/") {
            $error[]=XAPP_TEXT_FORMATTED("DIRECTORY_NOT_WRITEABLE",Array($path));
        } else {
            XApp_File_Utils::deleteFile($this->toRealPath($path),$options,$error,$success);
        }
    }
    ////////////////////////////////////////////////////////////////////////////
    //
    //  Utils
    //
    ////////////////////////////////////////////////////////////////////////////
    /***
     * Node meta completion, fields are evaluated on demand
     * @param $filepath
     * @param $item
     * @param $field_options
     */
    public static function add_ls_file_information($filepath,&$item,$field_options) {


        //sanity check
        if(empty($filepath)||!file_exists($filepath)){
            //throw new Exception('add_ls_file_information : failed because \'filepath=\'' . $filepath . ' doesnt exists or is not a path ');
            return;

        }
        // show permissions
        if (($field_options&XAPP_XFILE_SHOW_PERMISSIONS)==XAPP_XFILE_SHOW_PERMISSIONS){
            $item->{XAPP_NODE_FIELD_PERMISSIONS}=XApp_File_Utils::get_file_permissions($filepath);
        }

        // show owner
        if (($field_options&XAPP_XFILE_SHOW_OWNER)==XAPP_XFILE_SHOW_OWNER){
            $item->{XAPP_NODE_FIELD_OWNER}=XApp_File_Utils::get_file_ownership($filepath);
        }

        // force read only
        if (($field_options&XAPP_XFILE_SHOW_ISREADONLY)==XAPP_XFILE_SHOW_ISREADONLY){
            $item->{XAPP_NODE_FIELD_READ_ONLY}=!is_writable($filepath);
        }

        // show is directory
        if (($field_options&XAPP_XFILE_SHOW_ISDIR)==XAPP_XFILE_SHOW_ISDIR){

            $item->{XAPP_NODE_FIELD_IS_DIRECTORY}=is_dir($filepath);
        }

        // show size
        if (($field_options&XAPP_XFILE_SHOW_SIZE)==XAPP_XFILE_SHOW_SIZE) {
            $file_size=filesize($filepath);
            $item->{XAPP_NODE_FIELD_SIZE}=($file_size?number_format($file_size/1024,2)."Kb":"");
        }

        // show mime
        if (($field_options&XAPP_XFILE_SHOW_MIME)==XAPP_XFILE_SHOW_MIME){
            $item->{XAPP_NODE_FIELD_MIME}=XApp_File_Utils::getMime($filepath);
        }

        // show time
        if (($field_options & XAPP_XFILE_SHOW_TIME)==XAPP_XFILE_SHOW_TIME){
            $item->{XAPP_NODE_FIELD_TIME}=XApp_File_Utils::getFileTime($filepath);
        }
    }

    /**
     * method ls returns the directory listing, '/' means it will include the directory names
     * of all enabled and valid mounted directories.
     *
     * This is however a wrapper for Directory::Utils::getFilteredDirList.
     * You will implement this in a way that copyDirectory,moveDirectory can work with mounted
     * resources.
     */
    public function getFilteredDirList($path,$inclusionMask = Array(),$exclusionMask = Array(),$options=Array()){

        if ($path=="/") {   // Return all mounted directories
            $all_file_proxys=$this->getResourcesByType(XAPP_RESOURCE_TYPE_FILE_PROXY);
            $retlist=Array();
            $clear_path=((isset($options[XApp_Directory_Utils::OPTION_CLEAR_PATH])) && ($options[XApp_Directory_Utils::OPTION_CLEAR_PATH]));
            foreach($all_file_proxys as $f_proxy){
                $retlist[]=($clear_path?"":"/").$f_proxy->name;
            }
            return $retlist;
        } else {            // Call getFilteredDirList from XApp_Directory_Utils
            $path=$this->toRealPath($path);
            return XApp_Directory_Utils::getFilteredDirList($path,$inclusionMask,$exclusionMask,$options);
        }
    }

    /**
     *  Convert VFS path into real local filesystem path
     *
     * @param $path     :   expected path /mount_point/relative_path/...
     * @return string   :   real local filesystem path
     */
    public function toRealPath($path) {
        if (substr($path,0,1)=="/") $path=substr($path,1);
        $split_path=explode("/",$path);
        $mount=reset($split_path);
        $relative=implode(DIRECTORY_SEPARATOR,array_slice($split_path,1));
        return $this->toAbsolutePath($mount).$relative;
    }

    /**
     *  Convert VFS path into xapp resources item
     *
     * @param $path         :   expected path /mount_point/relative_path/...
     * @param $enabledOnly  :   return only a resources which is enabled
     * @return string       :   xapp-resource
     */
    public function toResource($path,$enabledOnly=true) {
        if (substr($path,0,1)=="/") $path=substr($path,1);
        $split_path=explode("/",$path);
        $mount=reset($split_path);
        return $this->hasMount($mount,$enabledOnly);
    }

    /**
     * method that will return the absolute path of a mounted resource
     * @param $name :   mount point name
     * @return mixed|null
     */
    public function toAbsolutePath($name){
        $resource = $this->getResource($name,true);
        if($resource){
            $resource = $this->resolveResource($resource);
        }else{
            return null;
        }
        if(xapp_property_exists($resource,XAPP_RESOURCE_PATH_ABSOLUTE)){
            return xapp_property_get($resource,XAPP_RESOURCE_PATH_ABSOLUTE);
        }

        return null;
    }

    /**
     * creates singleton instance for this class passing options to constructor
     *
     * @error 11102
     * @param null|mixed $options expects valid option object
     * @return null|XApp_VFS_Local
     */
    public static function instance($options = null)
    {
        if(self::$_instance === null)
        {
            self::$_instance = new self($options);
        }
        return self::$_instance;
    }

    /***
     * Factory method, expects an array of items :
     *
        $vfsItems[]=array(
            'name'=>'root',     //name of the VFS
            'path'=>$root       //resolved absolute path
        );

     * @param $items
     * @return null|XApp_VFS_Local
     */
    public static function factory($items){

        $head = array(
            "items"=>array(),
            "class"=>'cmx.types.Resources'
        );

        $absoluteVars = array();

        foreach($items as $item){

            $head['items'][]=array(

                "class"=>'cmx.types.Resource',
                'path'=>'%'.$item['name'].'%',
                'enabled'=>true,
                'type'=>'FILE_PROXY',
                'name'=>$item['name']
            );
            $absoluteVars[$item['name']]=$item['path'];
        }
        $vfsOptions = array(
            XApp_VFS_Base::ABSOLUTE_VARIABLES=>$absoluteVars,
            XApp_VFS_Base::RELATIVE_VARIABLES=>array(),
            XApp_VFS_Base::RESOURCES_DATA => (object)$head
        );

        $vfs = self::instance($vfsOptions);
        return $vfs;
    }
    /**
     * @param array
     * @param map
     * @return array array with remaped keys map(old_key_val=>new_key_val)
     */
    protected function array_remap_keys($input, $map) {
        $remaped_array = array();
        foreach($input as $key => $val) {
            if(array_key_exists($key, $map)) {
                $remaped_array[$map[$key]] = $val;
            }
        }
        return $remaped_array;
    }
    /***
     * Wrapper for array_remap_keys but works with an array of items
     * @param $input
     * @param $map
     * @return mixed
     */
    protected function array_items_remap_keys($input, $map) {
        $remaped_array = array();
        foreach($input as $item) {
            $item=$this->array_remap_keys($item,$map);
            array_push($remaped_array,$item);
        }

        return $remaped_array;
    }

	/**
	 * @param $url
	 * @return string
	 */
	public function cleanSlashes($url){
        $explode = explode('://',$url);
        while(strpos($explode[1],'//'))
            $explode[1] = str_replace('//','/',$explode[1]);
        return implode('://',$explode);
    }

	/**
	 * @param $fileName
	 * @param $content
	 * @param array $options
	 * @return mixed
	 */
	private function send($fileName,$content,$options=Array()) {
        ini_set('memory_limit', '128M');

        if (!isset($options[XApp_File_Utils::OPTION_SIZE_LIMIT])) $options[XApp_File_Utils::OPTION_SIZE_LIMIT]=XApp_File_Utils::GET_FILE_SIZE_LIMIT;
        if (!isset($options[XApp_File_Utils::OPTION_CHUNK_SIZE])) $options[XApp_File_Utils::OPTION_CHUNK_SIZE]=XApp_File_Utils::GET_FILE_CHUNK_SIZE;
        if (!isset($options[XApp_File_Utils::OPTION_TEMP_PATH])) $options[XApp_File_Utils::OPTION_TEMP_PATH]=sys_get_temp_dir();
        if (!isset($options[XApp_File_Utils::OPTION_AS_ATTACHMENT])) $options[XApp_File_Utils::OPTION_AS_ATTACHMENT]=false;
        if (!isset($options[XApp_File_Utils::OPTION_TEST])) $options[XApp_File_Utils::OPTION_TEST]=false;
        if (!isset($options[XApp_File_Utils::OPTION_SEND])) $options[XApp_File_Utils::OPTION_SEND]=true;

        $mime=XApp_File_Utils::guessMime($fileName);

        if ($options[XApp_File_Utils::OPTION_SEND]===true) {

	        //XApp_File_Utils::sendHeader($mime,($options[XApp_File_Utils::OPTION_AS_ATTACHMENT] ? $fileName:''), basename($fileName));

	        if (!$options[XApp_File_Utils::OPTION_TEST]) {

                XApp_File_Utils::sendHeader($mime,($options[XApp_File_Utils::OPTION_AS_ATTACHMENT] ? $fileName:''), basename($fileName));
                if (strpos($mime,"text")!==FALSE){
                    if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start();
                }
            }
            echo $content;

        }else{
	        XApp_File_Utils::sendHeader($mime,'', basename($fileName));
            return $content;
        }
    }

    private function importFly(){

	    require_once(realpath(dirname(__FILE__)) . '/vendor/autoload.php');

    }

	public function createAdapter($resource){
		$config = $resource->config;

		$this->importFly();
		$adapterClass = '\\League\\Flysystem\\Adapter\\'.$resource->adapter;

		if(property_exists($config,'pathPrefix')){
			$this->pathPrefix=$config->pathPrefix;
		}
		$adapterInstance = null;

		if($resource->adapter=='Ftp'||$resource->adapter=='Sftp'){
			$adapterInstance = new $adapterClass((array)$config);
		}else if($resource->adapter=='WebDav'){

			require_once realpath(dirname(__FILE__)) .'/vendor/autoload.php';

			$client = new Sabre\DAV\Client((array)$config);
			$client->setVerifyPeer(false);
			$adapterInstance = new $adapterClass($client);

		}else if($resource->adapter=='Dropbox'){

			require_once realpath(dirname(__FILE__)) .'/vendor/dropbox/dropbox-sdk/lib/Dropbox/autoload.php';
			$client = new \Dropbox\Client($config->token,$config->appname);
			$adapterInstance = new $adapterClass($client);

		}else if($resource->adapter=='GoogleDrive'){


			$client_id = '34179804656-v7ckk0id5h1dcrpoj07c2343vbf2qto2.apps.googleusercontent.com';
			$client_secret = 'ZnWLJFaJh_c1AO8wk_d4Y7fk';


			//$auth_token = '{"access_token":"ya29.IQD9DFJHv7U1kBgAAAAJSdBBwvJq8lmEj8f9RBsXbK5uX82vXlvlQAbn_pL2Rg","token_type":"Bearer","expires_in":3600,"refresh_token":"1\/_RAFGmxs0bQBSCCI3hJYnntuGiyXq28UGCdsW8E1cb4","created":1401030654}';
			$auth_token = null;

// This URL should be the landing page after authorising the site access to your Google Drive.
// It should store your $auth_token or display it for manual entry.

			//$auth_token = '{"access_token":"4/rBfx8PY5GOGkLPdeHnJLieoQBur_.gq0ftA4x5ZATOl05ti8ZT3YPlhuxjAI","token_type":"Bearer","expires_in":3600,"refresh_token":"1\/_RAFGmxs0bQBSCCI3hJYnntuGiyXq28UGCdsW8E1cb4","created":1401030654}';

			$auth_token = '{"access_token":"ya29.KAACmqCTqVx9lBoAAABKWlyiPU-ZJiN-MOWl57hPFu1-uNjvC2vC5cQgO1IzJA","token_type":"Bearer","expires_in":3600,"created":1401645493}';


			//$redirect_url = 'http://localhost/flysystem/GoogleDriveSetup.php';
			$redirect_url = 'http://mc007ibi.dyndns.org:81/x4mm/Code/trunk/xide-php/xapp/xcf/index.php?debug=true';
			//$redirect_url = 'http://www.mc007ibi.dyndns.org:81/xapp-commander-standalone/docroot/index.php?option=com_xappcommander&service=XCOM_Directory_Service.get&path=./Determining Food Preferences of Hagfish.docx&callback=asdf&mount=google&raw=html&attachment=true&user=e741198e1842408aa660459240d430a6&sig=025d0a5326e1bb4608b7624c99c3cff49db7664f';
			//http://www.mc007ibi.dyndns.org:81/xapp-commander-standalone/docroot/index.php?option=com_xappcommander&service=XCOM_Directory_Service.get&path=./Determining Food Preferences of Hagfish.docx&callback=asdf&mount=google&raw=html&attachment=true&user=e741198e1842408aa660459240d430a6&sig=025d0a5326e1bb4608b7624c99c3cff49db7664f

			//http://mc007ibi.dyndns.org:81/x4mm/Code/trunk/xide-php/xapp/xcf/index.php?debug=true&code=4/rBfx8PY5GOGkLPdeHnJLieoQBur_.gq0ftA4x5ZATOl05ti8ZT3YPlhuxjAI

			//http://192.168.1.37:81/xapp-commander-standalone/docroot/index.php?option=com_xappcommander&service=XCOM_Directory_Service.get&path=./Cleanup.doc&callback=asdf&mount=/google&raw=html&attachment=true&user=e741198e1842408aa660459240d430a6&sig=5faf21f72a91fa70890c75b7f7f579b3fd5e03a8
			//error_log('########0');

			$client = new Google_Client();
			if($auth_token){
				$client->setAccessToken($auth_token);
			}
			$client->setClientId($client_id);
			$client->setClientSecret($client_secret);
			$client->setRedirectUri($redirect_url);
			$token = $client->getAccessToken();
			$code = null;

			if ($auth_token) {
				error_log('have auth');
				$refreshToken = json_decode($token);
				/*xapp_dump($refreshToken);*/
				/*
				$refreshToken = $refreshToken->refresh_token;
				if($client->getAuth()->isAccessTokenExpired()) {
					$client->getAuth()->refreshToken($refreshToken);
				}
				*/

			} else {

				if ($code) {
					error_log('########1 : have code ');
					$client->authenticate($code);
					echo "Your access token for Google Drive is:<br /><br />\n\n";
					echo $client->getAccessToken();
					echo "\n\n<br /><br />This is your \$auth_token value. Set it in the configuration file.";
					exit();
				} else {

					error_log('######## getting auth url : begin' );
					$client->setScopes(array('https://www.googleapis.com/auth/drive'));
					$authUrl = $client->createAuthUrl();
					error_log('######## getting auth url : ' . $authUrl);
					/*echo($authUrl);*/
					die("You must first authorise the plugin. Make sure your client ID and secret are set then <a href='{$authUrl}'>click here</a> to do so.");
				}
			}

			$adapterInstance = new $adapterClass($client);
		}
		return $adapterInstance;
	}

    public function initWithResource($resource){

        $config = $resource->config;

        $this->importFly();
        $adapterClass = '\\League\\Flysystem\\Adapter\\'.$resource->adapter;

        if(property_exists($config,'pathPrefix')){
            $this->pathPrefix=$config->pathPrefix;
        }

	    if($resource->adapter=='Ftp'||$resource->adapter=='Sftp'){
            $adapterInstance = new $adapterClass((array)$config);
        }else if($resource->adapter=='WebDav'){

            //require_once realpath(dirname(__FILE__)) .'/vendor/sabre/dav/lib/Sabre/autoload.php';
            require_once realpath(dirname(__FILE__)) .'/vendor/autoload.php';

            $client = new Sabre\DAV\Client((array)$config);
            $client->setVerifyPeer(false);
            //$client->
            /*CURLOPT_SSL_VERIFYPEER => 0,
CURLOPT_SSL_VERIFYHOST => 0,*/

            //xapp_dump($client);

            $adapterInstance = new $adapterClass($client);


        }else if($resource->adapter=='Dropbox'){

            require_once realpath(dirname(__FILE__)) .'/vendor/dropbox/dropbox-sdk/lib/Dropbox/autoload.php';
            $client = new \Dropbox\Client($config->token,$config->appname);
            $adapterInstance = new $adapterClass($client);

        }else if($resource->adapter=='GoogleDrive'){

            //$client_id = '914720938366-1b9t1n0d87g7r429j37kh29474n301la.apps.googleusercontent.com';
            //$client_secret = '-FqmFBTynCy6VBIYwDLeIvPm';

	        $client_id = '34179804656-v7ckk0id5h1dcrpoj07c2343vbf2qto2.apps.googleusercontent.com';
	        $client_secret = 'ZnWLJFaJh_c1AO8wk_d4Y7fk';


            //$auth_token = '{"access_token":"ya29.IQD9DFJHv7U1kBgAAAAJSdBBwvJq8lmEj8f9RBsXbK5uX82vXlvlQAbn_pL2Rg","token_type":"Bearer","expires_in":3600,"refresh_token":"1\/_RAFGmxs0bQBSCCI3hJYnntuGiyXq28UGCdsW8E1cb4","created":1401030654}';
	        $auth_token = null;
            // This URL should be the landing page after authorising the site access to your Google Drive.
            // It should store your $auth_token or display it for manual entry.

	        //$auth_token = '{"access_token":"4/rBfx8PY5GOGkLPdeHnJLieoQBur_.gq0ftA4x5ZATOl05ti8ZT3YPlhuxjAI","token_type":"Bearer","expires_in":3600,"refresh_token":"1\/_RAFGmxs0bQBSCCI3hJYnntuGiyXq28UGCdsW8E1cb4","created":1401030654}';

	        $auth_token = '{"access_token":"ya29.KAACmqCTqVx9lBoAAABKWlyiPU-ZJiN-MOWl57hPFu1-uNjvC2vC5cQgO1IzJA","token_type":"Bearer","expires_in":3600,"created":1401645493}';


            //$redirect_url = 'http://localhost/flysystem/GoogleDriveSetup.php';
	        $redirect_url = 'http://mc007ibi.dyndns.org:81/x4mm/Code/trunk/xide-php/xapp/xcf/index.php?debug=true';
	        //$redirect_url = 'http://www.mc007ibi.dyndns.org:81/xapp-commander-standalone/docroot/index.php?option=com_xappcommander&service=XCOM_Directory_Service.get&path=./Determining Food Preferences of Hagfish.docx&callback=asdf&mount=google&raw=html&attachment=true&user=e741198e1842408aa660459240d430a6&sig=025d0a5326e1bb4608b7624c99c3cff49db7664f';
	        //http://www.mc007ibi.dyndns.org:81/xapp-commander-standalone/docroot/index.php?option=com_xappcommander&service=XCOM_Directory_Service.get&path=./Determining Food Preferences of Hagfish.docx&callback=asdf&mount=google&raw=html&attachment=true&user=e741198e1842408aa660459240d430a6&sig=025d0a5326e1bb4608b7624c99c3cff49db7664f

	        //http://mc007ibi.dyndns.org:81/x4mm/Code/trunk/xide-php/xapp/xcf/index.php?debug=true&code=4/rBfx8PY5GOGkLPdeHnJLieoQBur_.gq0ftA4x5ZATOl05ti8ZT3YPlhuxjAI

	        //http://192.168.1.37:81/xapp-commander-standalone/docroot/index.php?option=com_xappcommander&service=XCOM_Directory_Service.get&path=./Cleanup.doc&callback=asdf&mount=/google&raw=html&attachment=true&user=e741198e1842408aa660459240d430a6&sig=5faf21f72a91fa70890c75b7f7f579b3fd5e03a8
	        //error_log('########0');

            $client = new Google_Client();
            if($auth_token){
	            $client->setAccessToken($auth_token);
            }
            $client->setClientId($client_id);
            $client->setClientSecret($client_secret);
            $client->setRedirectUri($redirect_url);
            $token = $client->getAccessToken();
	        $code = null;
	        //$code = '4/lnZn4gks2d2pO1ddiO488ZxPKX_o.8uYEey1YDAkROl05ti8ZT3bHlSmxjAI';
	        //{"access_token":"ya29.KAACmqCTqVx9lBoAAABKWlyiPU-ZJiN-MOWl57hPFu1-uNjvC2vC5cQgO1IzJA","token_type":"Bearer","expires_in":3600,"created":1401645493}
	        //error_log('######## : ' . $token);//
	        //www.mc007ibi.dyndns.org:81/xapp-commander-standalone/docroot/index.php?option=com_xappcommander&service=XCOM_Directory_Service.get&path=./Determining Food Preferences of Hagfish.docx&callback=asdf&mount=google&raw=html&attachment=true&user=e741198e1842408aa660459240d430a6&sig=025d0a5326e1bb4608b7624c99c3cff49db7664f

	        if ($auth_token) {
		        error_log('have auth');
		        $refreshToken = json_decode($token);
		        /*xapp_dump($refreshToken);*/
		        /*
		        $refreshToken = $refreshToken->refresh_token;
		        if($client->getAuth()->isAccessTokenExpired()) {
			        $client->getAuth()->refreshToken($refreshToken);
		        }
		        */

	        } else {

		        if ($code) {
			        error_log('########1 : have code ');
			        $client->authenticate($code);
			        echo "Your access token for Google Drive is:<br /><br />\n\n";
			        echo $client->getAccessToken();
			        echo "\n\n<br /><br />This is your \$auth_token value. Set it in the configuration file.";
			        exit();
		        } else {

			        error_log('######## getting auth url : begin' );
			        $client->setScopes(array('https://www.googleapis.com/auth/drive'));
			        $authUrl = $client->createAuthUrl();
			        error_log('######## getting auth url : ' . $authUrl);
			        /*echo($authUrl);*/
			        die("You must first authorise the plugin. Make sure your client ID and secret are set then <a href='{$authUrl}'>click here</a> to do so.");
		        }
	        }


            $adapterInstance = new $adapterClass($client);


        }

        $fs = "League\\Flysystem\\Filesystem";

        // Add them in the constructor
        $manager = new League\Flysystem\MountManager(array(
            $resource->name => new $fs($adapterInstance)
        ));
        $this->mountManager=$manager;
	    $this->adapter=$adapterInstance;

        //xapp_dump($resource);
        //$fs = $this->getFilesystem($resource->{'name'});
        // Or mount them later
        //$manager->mountFilesystem('local', $local);
        //xapp_dump($manager);
        //$filesystem = new Filesystem($adapterInstance);
        //$ls = $fs->listWith(array('mimetype', 'size', 'timestamp'),'httpdocs');
        //$ls = $fs->listWith(null,'httpdocs');
        //xapp_dump($fs->listContents('/httpdocs'));
    }

    /**
     * Function on mount manager to list directory contents
     *
     * @param $mountMgr
     * @param $path
     * @return mixed
     */
    public function _lsRemote($mountMgr,$path){


        $list = $mountMgr->listContents($path);

        $list =$this->array_items_remap_keys($list,array(
            'basename'=>XAPP_NODE_FIELD_NAME,
            'timestamp'=>XAPP_NODE_FIELD_TIME,
            'size'=>XAPP_NODE_FIELD_SIZE,
            'type'=>'type'//dont remove
        ));
        return $list;
    }
	/**
	 * @param $path
	 * @return string
	 */
	public function toFlyPath($path){
		$path = XApp_Path_Utils::normalizePath($path);
		$mount = XApp_Path_Utils::getMount($path);
		$relativePath = XApp_Path_Utils::getRelativePart($path);
		return $mount . '://'.$relativePath;
	}


	public function isRemoteFS(){
		return true;
	}

}
