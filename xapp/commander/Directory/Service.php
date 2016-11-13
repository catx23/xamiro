<?php
/**
 * @version 0.1.0
 *
 * @author https://github.com/mc007
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 * @package XApp\xide\Directory
 */

xapp_import('xapp.VFS.Interface.Access');
xapp_import('xapp.xide.Directory.Service');
xapp_import('xapp.Path.Utils');

/***
 * Class XIDE_Directory_Service extends the standard directory service
 * @link : ls : http://192.168.1.37:81/x4mm/Code/trunk/xide-php/xapp/xcf/index.php?debug=true&view=smdCall&service=XCF_Directory_Service.ls&path=.&callback=asd
 */
class XCOM_Directory_Service extends XIDE_Directory_Service
{
    protected $_currentItems = array();

    public static function getResourceName()
    {
        return "xfile";
    }

    /**
     * @param $mount
     * @param $searchConf
     * @return mixed
     */
    public function find($mount, $searchConf)
    {
        $vfs = $this->getFileSystem($mount);
        return $vfs->find($mount, $searchConf);

    }


    /**
     * @param $mount
     * @param $selection
     * @param string $type
     * @return mixed
     */
    public function compress($mount, $selection, $type = 'zip')
    {
        $error = array();
        xapp_import('xapp.Path.Utils');
        xapp_import('xapp.Utils.SystemTextEncoding');
        $mount = XApp_Path_Utils::getMount($mount);
        $vfs = $this->getFileSystem($mount);
        return $vfs->compress($mount, $selection, $type, $error);
    }

    /**
     * @param $mount
     * @param $what
     * @return mixed
     */
    public function extract($mount, $what)
    {
        $error = array();
        xapp_import('xapp.Path.Utils');
        xapp_import('xapp.Utils.SystemTextEncoding');
        $mount = XApp_Path_Utils::getMount($mount);
        $vfs = $this->getFileSystem($mount);
        return $vfs->extract($mount, $what, $error);
    }

    /**
     * @param $path
     * @param $newFileName
     * @return array
     */
    public function rename($mount, $path, $newFileName)
    {

        $error = array();
        xapp_import('xapp.Path.Utils');
        xapp_import('xapp.Utils.SystemTextEncoding');

        $file = XApp_Path_Utils::decodeSecureMagic($path);
        $filename_new = XApp_Path_Utils::decodeSecureMagic($newFileName);
        $mount = XApp_Path_Utils::getMount($mount);
        $vfs = $this->getFileSystem($mount);


        if ($this->isLocal($mount, $this->getFSResources())) {

            if (!@is_dir($vfs->toRealPath($mount . DIRECTORY_SEPARATOR . $filename_new))) {
                $ext = pathinfo(strtolower($filename_new), PATHINFO_EXTENSION);
                $allowable = explode(',', xapp_get_option(self::UPLOAD_EXTENSIONS, $this));
                if ($ext == '' || $ext == false || (!in_array($ext, $allowable))) {
                    $errors[] = XAPP_TEXT_FORMATTED('UPLOAD_EXTENSIONS_NOT_ALLOWED', array($filename_new, $ext));
                }
            }
            $vfs->rename($mount, $file, $filename_new, null, $error);
        } else {
            $old = $mount . '://' . $file;
            $old = $vfs->cleanSlashesRemote($old);
            $new = dirname($file) . "/" . $filename_new;
            $new = $vfs->cleanSlashesRemote($new);
            $mountManager = $vfs->getMountManager();
            $mountManager->rename($old, $new);
        }
        return self::toRPCError(count($error) > 0 ? 1 : 0, $error);
    }

    /**
     * @return array
     * @throws Xapp_XFile_Exception
     */
    public function putRemote($mount, $destination)
    {
        $vfs = $this->getFileSystem($mount);

        $mountManager = $vfs->getMountManager();
        $errors = array();

        //parse files
        $fileVars = $_FILES;
        foreach ($fileVars as $boxName => $boxData) {
            if (substr($boxName, 0, 9) != "userfile_") {
                continue;
            }
            $err = self::parseFileDataErrors($boxData);
            if ($err != null) {
                $errorCode = $err[0];
                $errorMessage = $err[1];
            }

            //basic sanitize
            $userfile_name = $boxData["name"];
            $userfile_name = XApp_Path_Utils::sanitizeEx(
                XApp_SystemTextEncoding::fromPostedFileName($userfile_name),
                XApp_Path_Utils::SANITIZE_HTML_STRICT
            );
            $userfile_name = substr($userfile_name, 0, 128);

            //rename if needed!
            $autorename = xapp_get_option(self::AUTO_RENAME);
            if ($autorename) {
                $userfile_name = self::autoRenameForDest($destination, $userfile_name);
            }

            /***
             * file extension check
             */
            $ext = pathinfo(strtolower($userfile_name), PATHINFO_EXTENSION);
            $allowable = explode(',', xapp_get_option(self::UPLOAD_EXTENSIONS, $this));
            if ($ext == '' || $ext == false || (!in_array($ext, $allowable))) {
                $errors[] = XAPP_TEXT_FORMATTED('UPLOAD_EXTENSIONS_NOT_ALLOWED', array($userfile_name, $ext));
                xapp_clog('file not allowed');
                continue;
            }

            try {
                if (file_exists($destination . "/" . $userfile_name)) {

                } else {

                }

            } catch (Exception $e) {
                $errorMessage = $e->getMessage();
                $errors[] = XAPP_TEXT_FORMATTED('UPLOAD_UNKOWN_ERROR', array($userfile_name, $errorMessage));
                break;
            }

            if (isSet($boxData["input_upload"])) {

                try {
                    $input = fopen("php://input", "r");
                    $output = fopen("$destination/" . $userfile_name, "w");
                    $sizeRead = 0;
                    while ($sizeRead < intval($boxData["size"])) {
                        $chunk = fread($input, 4096);
                        $sizeRead += strlen($chunk);
                        fwrite($output, $chunk, strlen($chunk));
                    }
                    fclose($input);
                    fclose($output);
                } catch (Exception $e) {
                    $errorMessage = $e->getMessage();
                    $errors[] = XAPP_TEXT_FORMATTED('UPLOAD_UNKOWN_ERROR', array($userfile_name, $errorMessage));
                    break;
                }
            } else {
                $result = true;
                $dstFinal = $mount . '://' . $destination . '/' . $userfile_name;
                //dummy write to ensure file
                $result = $mountManager->write($dstFinal, "");
                $src = fopen($boxData["tmp_name"], "r");
                while (!feof($src)) {
                    $result = $mountManager->updateStream($dstFinal, $src);
                }
                fclose($src);
                if (!$result) {
                    $errors[] = XAPP_TEXT_FORMATTED('UPLOAD_UNKOWN_ERROR', array($userfile_name));
                    break;
                }
                return $result;
            }
        }

    }

    /**
     * @param $size
     *
     * @return int|string
     */
    function convert_size_to_num($size)
    {
        //This function transforms the php.ini notation for numbers (like '2M') to an integer (2*1024*1024 in this case)
        $l = substr($size, -1);
        $ret = substr($size, 0, -1);
        switch (strtoupper($l)) {
            case 'P':
                $ret *= 1024;
            case 'T':
                $ret *= 1024;
            case 'G':
                $ret *= 1024;
            case 'M':
                $ret *= 1024;
            case 'K':
                $ret *= 1024;
                break;
        }
        return $ret;
    }

    function get_max_fileupload_size()
    {
        $max_upload_size = min(self::convert_size_to_num(ini_get('post_max_size')), self::convert_size_to_num(ini_get('upload_max_filesize')));

        return $max_upload_size;
    }

    /**
     * @return array
     * @throws Xapp_XFile_Exception
     */
    public function put()
    {
        xapp_import('xapp.Path.Utils');
        xapp_import('xapp.Utils.SystemTextEncoding');

        //XApp_ErrorHandler::start(E_ERROR);
        /*
        set_error_handler(function($code, $string, $file, $line){
            throw new ErrorException($string, null, $code, $file, $line);
        });

        $data = '';

        error_log('puttt');

        register_shutdown_function(function(){
            $error = error_get_last();
            if(null !== $error)
            {
                echo 'Caught at shutdown';
            }
        });

        try
        {
            while(true)
            {
                $data .= str_repeat('#', PHP_INT_MAX);
            }
        }
        catch(\Exception $exception)
        {
            echo 'Caught in try/catch';
        }
        */


        $vars = array_merge($_GET, $_POST);


        $dstIn = '/';
        $mount = '/';

        if (array_key_exists('dstDir', $vars)) {
            $dstIn = XApp_Path_Utils::decodeSecureMagic($vars['dstDir']);
        }
        if (array_key_exists('mount', $vars)) {
            $mount = preg_replace('@[/\\\]@', '', XApp_Path_Utils::decodeSecureMagic($vars['mount']));
        }
        if ($dstIn === '.') {
            $dstIn = '/';
        }
        $vfs = $this->getFileSystem($mount);

        $destination = $vfs->toRealPath(XApp_Path_Utils::normalizePath($mount . DIRECTORY_SEPARATOR . $dstIn));


        $errors = array();
        if (!$this->isLocal($mount, $this->getFSResources())) {
            return $this->putRemote($mount, $destination);
        }
        //writable check
        if (!is_writable($destination)) {
            throw new Xapp_XFile_Exception(XAPP_TEXT_FORMATTED('DIRECTORY_NOT_WRITEABLE', array($destination), 55100));
        }


        //parse files
        $fileVars = $_FILES;
        foreach ($fileVars as $boxName => $boxData) {

            if (substr($boxName, 0, 9) != "userfile_") {
                continue;
            }
            $err = self::parseFileDataErrors($boxData);

            if ($err != null) {
                $errorMessage = $err[1];
                $errors[] = XAPP_TEXT_FORMATTED('Error with upload %s', array($errorMessage));
                continue;

            }

            //basic sanitize
            $userfile_name = $boxData["name"];
            $userfile_name = XApp_Path_Utils::sanitizeEx(
                XApp_SystemTextEncoding::fromPostedFileName($userfile_name),
                XApp_Path_Utils::SANITIZE_HTML_STRICT
            );
            $userfile_name = substr($userfile_name, 0, 128);


            //rename if needed!
            $autorename = xapp_get_option(self::AUTO_RENAME);
            if ($autorename) {
                $userfile_name = self::autoRenameForDest($destination, $userfile_name);
            }

            /***
             * file extension check
             */
            $ext = pathinfo(strtolower($userfile_name), PATHINFO_EXTENSION);
            $allowable = explode(',', xapp_get_option(self::UPLOAD_EXTENSIONS, $this));
            if ($ext == '' || $ext == false || (!in_array($ext, $allowable))) {
                $errors[] = XAPP_TEXT_FORMATTED('UPLOAD_EXTENSIONS_NOT_ALLOWED', array($userfile_name, $ext));
                xapp_clog('file not allowed');
                continue;
            }

            try {
                //no need anymore
                if (file_exists($destination . "/" . $userfile_name)) {

                }

            } catch (Exception $e) {
                $errorMessage = $e->getMessage();
                $errors[] = XAPP_TEXT_FORMATTED('UPLOAD_UNKOWN_ERROR', array($userfile_name, $errorMessage));
                break;
            }


            if (isSet($boxData["input_upload"])) {

                try {
                    $input = fopen("php://input", "r");
                    $output = fopen("$destination/" . $userfile_name, "w");
                    $sizeRead = 0;
                    while ($sizeRead < intval($boxData["size"])) {
                        $chunk = fread($input, 4096);
                        $sizeRead += strlen($chunk);
                        fwrite($output, $chunk, strlen($chunk));
                    }
                    fclose($input);
                    fclose($output);
                } catch (Exception $e) {
                    $errorMessage = $e->getMessage();
                    $errors[] = XAPP_TEXT_FORMATTED('UPLOAD_UNKOWN_ERROR', array($userfile_name, $errorMessage));
                    break;
                }
            } else {
                $result = @move_uploaded_file($boxData["tmp_name"], "$destination/" . $userfile_name);
                if (!$result) {
                    $realPath = $destination . DIRECTORY_SEPARATOR . $userfile_name;
                    $result = move_uploaded_file($boxData["tmp_name"], $realPath);
                }
                if (!$result) {
                    $errors[] = XAPP_TEXT_FORMATTED('UPLOAD_UNKOWN_ERROR', array($userfile_name));
                    break;
                }
            }


        }

        //$error = XApp_ErrorHandler::stop();
        //error_log('error ' . $error);

        return $errors;
    }


    /***
     * @param $selection
     * @return array
     */
    public function delete($selection, $options, $secure = false)
    {

        $finalSelection = array();
        if ($selection) {
            if (is_string($selection)) {
                array_push($finalSelection, $selection);
            } elseif (is_array($selection)) {
                foreach ($selection as $selectedFile) {
                    $finalSelection[] = $selectedFile;
                }
            }
        }

        $success = array();
        $error = array();
        $inclusion = $inclusion = array('*', '.*');
        $mount = XApp_Path_Utils::getMount($finalSelection[0]);
        $vfs = $this->getFileSystem($mount);
        $vfs->delete($finalSelection, (array)$options, $inclusion, array(), $error, $success);
        return self::toRPCError(count($error) > 0 ? 1 : 0, $error);
    }


    public function move($selection, $dst, $inclusion = Array(), $exclusion = Array(), $mode = 1504)
    {

        $success = array();
        $error = array();
        if ($dst === '.') {
            $dst = '/';
        }

        if (!count($inclusion)) {
            $inclusion = array('*', '.*');
        }
        $vfs = $this->getFileSystem();
        $vfs->move($selection, $dst, null, $inclusion, $exclusion, $error, $success, $mode);
        return self::toRPCError(count($error) > 0 ? 1 : 0, $error);
    }

    /**
     * @param $selection
     * @param $dst
     * @param array $options
     * @return array
     */
    public function copy($selection, $dst, $options)
    {


        $success = array();
        $error = array();
        if ($dst === '.') {
            $dst = '/';
        }
        $options = (array)$options;
        $inclusion = isset($options['include']) ? $options['include'] : array();
        $exclusion = isset($options['exclude']) ? $options['exclude'] : array();
        $mode = $options['mode'];
        $hints = xapp_array_get($options, 'hints');
        if (!$inclusion) {
            $inclusion = Array();
        }
        if (!$hints) {
            $hints = Array();
        }
        if (!$exclusion) {
            $exclusion = Array();
        }
        if (!$mode) {
            $mode = 1504;
        }

        if (!count($inclusion)) {
            $inclusion = array('*', '.*');
        }

        /**
         * Local/Remote to Local/Remote
         */
        if ($this->isRemoteOperation($selection[0], $dst)) {


            $remoteMount = $this->isRemote(
                XApp_Path_Utils::getMount($selection[0]),
                $this->getFSResources()
            ) ? XApp_Path_Utils::getMount($selection[0]) : XApp_Path_Utils::getMount($dst);
            $localMount = $this->isLocal(
                XApp_Path_Utils::getMount($selection[0]),
                $this->getFSResources()
            ) ? XApp_Path_Utils::getMount($selection[0]) : XApp_Path_Utils::getMount($dst);

            $incoming = $this->isLocal(XApp_Path_Utils::getMount($dst), $this->getFSResources()) ? true : false;

            $remoteVFS = $this->getFileSystem($remoteMount);
            $localVFS = $this->getFileSystem($localMount);
            $localDirectory = $localVFS->toRealPath('/' . $localMount . '/');

            $adapterClass = '\\League\\Flysystem\\Adapter\\Local';
            $localFilesystem = new League\Flysystem\Filesystem(new $adapterClass($localDirectory));

            $mountManager = $remoteVFS->getMountManager();
            $mountManager->mountFilesystem($localMount, $localFilesystem);

            $relativePartDestination = XApp_Path_Utils::getRelativePart($dst);
            if ($incoming) {
                $tmp = $remoteMount;
                $remoteMount = $localMount;
                $localMount = $tmp;
            }

            foreach ($selection as $selectedFile) {
                $relativePart = XApp_Path_Utils::getRelativePart($selection[0]);
                $mountManager->put(
                    $remoteMount . '://' . $relativePartDestination . '/' . basename($selectedFile),
                    $mountManager->read($localMount . '://' . $relativePart)
                );
            }
            return true;

        } else {
            if ($this->isRemoteToRemoteOperation($selection[0], $dst)) {
                $remoteMountSrc = XApp_Path_Utils::getMount($selection[0]);
                $remoteSrcVFS = $this->getFileSystem($remoteMountSrc);
                $remoteMountDst = XApp_Path_Utils::getMount($dst);
                $mountManager = $remoteSrcVFS->getMountManager();

                //two different remote file systems:
                if ($remoteMountSrc !== $remoteMountDst) {
                    $remoteResource = $this->isRemote($remoteMountDst, $this->getFSResources());
                    if ($remoteResource) {

                        $dstAdapter = $remoteSrcVFS->createAdapter($remoteResource);
                        $dstFileSystem = new League\Flysystem\Filesystem($dstAdapter);
                        $mountManager->mountFilesystem($remoteMountDst, $dstFileSystem);
                    }
                }

                $relativePartDestination = XApp_Path_Utils::getRelativePart($dst);
                foreach ($selection as $selectedFile) {
                    $relativePart = XApp_Path_Utils::getRelativePart($selection[0]);
                    $mountManager->put(
                        $remoteMountDst . '://' . $relativePartDestination . '/' . basename($selectedFile),
                        $mountManager->read($remoteMountSrc . '://' . $relativePart)
                    );
                }
                return true;
            }
        }


        $vfs = $this->getFileSystem();
        $vfs->copy($selection, $dst, $options, $inclusion, $exclusion, $error, $success, $mode);

        return self::toRPCError(count($error) > 0 ? 1 : 0, $error, $success);

    }

    public function ls($mount = 'ws', $path = '/', $options = null, $recursive = false){

        xapp_import('xapp.Xapp.Hook');
        //$recursive = false;
        //sanitize
        $basePath = XApp_Path_Utils::normalizePath($mount, false, false);
        if ($basePath === '' || $basePath === '.') {
            $basePath = 'root';
        }

        $path = XApp_Path_Utils::normalizePath($path, false, false);
        $options = (array)$options;

        $scanPath = $basePath . DIRECTORY_SEPARATOR . $path;
        $scanPath = XApp_Path_Utils::normalizePath($scanPath, false, false);

        $vfs = $this->getFileSystem($mount);

        //make a little check for local VFS, if the absolute path doesn't exists, abort here
        $rootResolved = $vfs->toAbsolutePath($basePath);
        if (!$rootResolved && $vfs->remote == false) {
            $error = array(XAPP_TEXT_FORMATTED('DIRECTORY_DOES_NOT_EXISTS', array($basePath . '://')));
            return self::toRPCError(1, $error);
        }


        //defaults
        if (!$options) {
            $options = Array(
                XApp_File_Utils::OPTION_DIR_LIST_FIELDS =>
                    XAPP_XFILE_SHOW_SIZE |
                    XAPP_XFILE_SHOW_PERMISSIONS |
                    XAPP_XFILE_SHOW_ISREADONLY |
                    XAPP_XFILE_SHOW_ISDIR |
                    XAPP_XFILE_SHOW_OWNER |
                    XAPP_XFILE_SHOW_TIME |
                    XAPP_XFILE_SHOW_MIME
            );
        }
        //hook into meta data creation for custom completion
        Xapp_Hook::connect(
            XApp_VFS_Base::EVENT_ON_NODE_META_CREATED,
            $this,
            "_onItem",
            '',
            array(
                //event user data
                'parentPath' => $path,
                'mount' => $basePath,
                'remote' => $vfs->remote,
                'options' => $options,
                'recursive' => $recursive,
                'owner' => $this
            )
        );

        //hook into 'node add' for custom node rejection
        Xapp_Hook::connect(
            XApp_VFS_Base::EVENT_ON_NODE_ADD,
            $this,
            "_addNode",
            '',
            array(
                'mount' => $basePath,
                'owner' => $this,
                'options' => $options,
                'remote' => $vfs->remote,
                'recursive' => $recursive
            )
        );
        $result = null;

        try {
            $vfs->ls(XApp_Path_Utils::normalizePath($scanPath, false), $recursive, $options);
        } catch (Exception $e) {
            return $this->toRPCError(1, $e->getMessage());
        }

        //prepare root object
        $rootObject = new stdClass();
        $fullPath = $vfs->toRealPath($basePath);

        if (!file_exists($fullPath)) {
            xapp_import('xapp.File.Utils');
            $res = XApp_File_Utils::mkDir($fullPath, 0755, true);
            if ($res == false) {
                return $this->toRPCError(1, 'Sorry, couldnt create directory');
            }
        }

        $vfs->add_ls_file_information(
            $vfs->toRealPath($basePath),
            $rootObject,
            XAPP_XFILE_SHOW_SIZE |
            XAPP_XFILE_SHOW_PERMISSIONS |
            XAPP_XFILE_SHOW_ISREADONLY |
            XAPP_XFILE_SHOW_TIME |
            XAPP_XFILE_SHOW_MIME
        );


        //corrections
        $rootName = '' . $path;
        $rootPath = '' . $path;

        if ($rootName === '' || $rootName === '/') {
            $rootName = '.';
        }
        if ($rootPath === '' || $rootPath === '/') {
            $rootPath = '.';
        } else {
            $rootPath = './' . $path;
        }

        $rootPath = str_replace('//', '/', $rootPath);
        if ($this->isWindows()) { //whatever
            $rootPath = str_replace('././', './', $rootName);
        }
        $rootName = basename($rootName);
        $rootName = str_replace('/', '', $rootName);

        //prepare Dojo store root structure
        $result = array(
            'status' => 200,
            'total' => 1,
            'items' => array()
        );

        $result['items'][] = array(
            'children' => $this->_currentItems, //created in the hook
            '_EX' => true,
            'size' => 0,
            'name' => $rootName,
            'path' => $rootPath,
            'mount' => $basePath,
            'directory' => true
        );
        return $result;
    }

    /***
     * VFS callback when a node will be added, return false if its not in
     * our file match pattern
     * @param $evt
     * @return boolean
     */
    public function _addNode($evt)
    {

        if ($this->_vfs !== null && //need valid VFS
            is_array($evt) && //must be an array
            array_key_exists('item', $evt) && //need item
            array_key_exists('userData', $evt) && //need user data
            array_key_exists(
                'options',
                $evt['userData'] //need options
            )
        ) {
            $userData = $evt['userData'];
            $options = $userData['options'];

            //no filters? return
            if (
                !isset($options[self::INCLUDES_FILE_EXTENSIONS]) &&
                !isset($options[self::EXCLUDED_FILE_EXTENSION])
            ) {
                return true;
            }

            $item = $evt['item'];

            $isDirectory = $item->{XAPP_NODE_FIELD_IS_DIRECTORY};


            //check files
            if ($isDirectory === false) {

                $allowedFileExtensions = isset($options[self::INCLUDES_FILE_EXTENSIONS]) ? $options[self::INCLUDES_FILE_EXTENSIONS] : array();
                $forbiddenFileExtensions = isset($options[self::EXCLUDED_FILE_EXTENSION]) ? $options[self::EXCLUDED_FILE_EXTENSION] : array();

                if (is_string($allowedFileExtensions)) {
                    if (strpos($allowedFileExtensions, ',') !== false) {
                        $allowedFileExtensions = explode(',', $allowedFileExtensions);
                    } else {
                        $allowedFileExtensions = array($allowedFileExtensions);
                    }
                }

                if (is_string($forbiddenFileExtensions)) {
                    if (strpos($forbiddenFileExtensions, ',') !== false) {
                        $forbiddenFileExtensions = explode(',', $forbiddenFileExtensions);
                    } else {
                        $forbiddenFileExtensions = array($forbiddenFileExtensions);
                    }
                }

                $isAllowed = XApp_Directory_Utils::isAllowed(
                    $item->{XAPP_NODE_FIELD_NAME},
                    $allowedFileExtensions,
                    $forbiddenFileExtensions
                );

                return $isAllowed === true;
            }
        }
        return true;
    }

    /***
     * Xapp_Rpc_Interface_Callable Impl. Before the actual call is being invoked.
     * @param Xapp_Rpc_Server $server
     * @param array $params
     * @return void
     */
    public function onBeforeCall(Xapp_Rpc_Server $server, Array $params)
    {
        parent::init();
    }

    protected static function isWindows()
    {
        $os = PHP_OS;
        switch ($os) {
            case "WINNT": {
                return true;
            }
        }

        return false;
    }
    /***
     * Maqetta & XIDE specific extension, we mixin 2 new attributes per node and we mark common directories
     * as read-only
     * @param $evt
     * @return object
     */
    public function _onItem($evt){

        if (is_array($evt) && isset($evt['item'])) {
            $item = $evt['item'];
            $userData = $evt['userData'];
            if (!$userData) {
                return null;
            }
            $options = isset($userData['options']) ? $userData['options'] : array();
            $recursive = $userData['recursive'];
            if (isset($userData['parentPath'])) {
                $parentPath = $userData['parentPath'];
                $item->path = './' . $parentPath . '/' . $item->name;
                $item->path = str_replace('//', '/', $item->path);
                $item->mount = $userData['mount'];
                $item->remote = $userData['remote'];
            } else {
                $item->path = './' . $item->name;
            }

            if ($item->isDir === true) {
                $item->children = array();
                $item->_EX = false;
                $item->size = property_exists($item, 'size') ? $item->size : 0;
                $item->directory = $item->isDir;
                /*
                if($recursive) {
                    $old = $this->_currentItems;
                    $this->_currentItems = Array();
                    $data = $userData['owner']->ls($userData['mount'], $item->path, $userData['options'], $userData['recursive']);
                    $this->_currentItems = $old;
                    if(is_array($data) && array_key_exists('items',$data)) {
                        $items = $data['items'];
                        $first = $items[0];
                        //$item->_EX = true;

                        $item->children = $first['children'];
                        foreach($item->children as $n=>$entry) {
                            $entry->path = $item->path . str_replace('./', '/', $entry->path);
                        }

                    }elseif ($data['error']){
                    }
                }
                */

            } else {
                if (property_exists($item, 'size')) {
                    if ($item->size === '') {
                        $item->size = '0 Kb';
                    }
                } else {
                    $item->size = '0 Kb';
                }
            }
            $item->path = str_replace('//', '/', $item->path);
            if ($this->isWindows()) {
                $item->path = str_replace('././', './', $item->path);
            }
            $this->_currentItems[] = $item;

            return $item;
        }
        return null;
    }


    /**
     * Parse the $fileVars[] PHP errors
     * @static
     * @param $boxData
     * @return array|null
     */
    static function parseFileDataErrors($boxData)
    {
        $userfile_error = $boxData["error"];
        $userfile_tmp_name = $boxData["tmp_name"];
        $userfile_size = $boxData["size"];
        if ($userfile_error != UPLOAD_ERR_OK) {
            $errorsArray = array();
            $errorsArray[UPLOAD_ERR_FORM_SIZE] = $errorsArray[UPLOAD_ERR_INI_SIZE] = array(409, "File is too big! Max is" . ini_get("upload_max_filesize"));
            $errorsArray[UPLOAD_ERR_NO_FILE] = array(410, "No file found on server!");
            $errorsArray[UPLOAD_ERR_PARTIAL] = array(410, "File is partial");
            $errorsArray[UPLOAD_ERR_INI_SIZE] = array(410, "No file found on server, maybe too big! Allowed size : " . ini_get("upload_max_filesize"));
            $errorsArray[UPLOAD_ERR_NO_TMP_DIR] = array(410, "Cannot find the temporary directory!");
            $errorsArray[UPLOAD_ERR_CANT_WRITE] = array(411, "Cannot write into the temporary directory!");
            $errorsArray[UPLOAD_ERR_EXTENSION] = array(410, "A PHP extension stopped the upload process");
            if ($userfile_error == UPLOAD_ERR_NO_FILE) {
                // OPERA HACK, do not display "no file found error"
                if (!ereg('Opera', $_SERVER['HTTP_USER_AGENT'])) {
                    return $errorsArray[$userfile_error];
                }
            } else {
                return $errorsArray[$userfile_error];
            }
        }
        if ($userfile_tmp_name == "none") {
            return array(410, 'no file name');
        }
        return null;
    }


    /**
     * @param $destination
     * @param $fileName
     * @return string
     */
    public static function autoRenameForDest($destination, $fileName)
    {
        if (!is_file($destination . "/" . $fileName)) {
            return $fileName;
        }
        $i = 1;
        $ext = "";
        $name = "";
        $split = explode(".", $fileName);
        if (count($split) > 1) {
            $ext = "." . $split[count($split) - 1];
            array_pop($split);
            $name = join("\.", $split);
        } else {
            $name = $fileName;
        }
        while (is_file($destination . "/" . $name . "-$i" . $ext)) {
            $i++; // increment i until finding a non existing file.
        }
        return $name . "-$i" . $ext;
    }

    /**
     * @param $mount
     * @param $selection
     * @param string $type
     * @return mixed
     */
    public function downloadTo($url, $mount, $to)
    {

        xapp_import('xapp.Path.Utils');
        xapp_import('xapp.File.Utils');
        xapp_import('xapp.Commons.ErrorHandler');
        xapp_import('xapp.Utils.Download');
        xapp_import('xapp.Utils.SystemTextEncoding');
        $success = array();
        $error = array();
        $mount = XApp_Path_Utils::getMount($mount);
        $vfs = $this->getFileSystem($mount);

        $realSrcFile = '' . $url;


        XApp_ErrorHandler::start();

        if ($this->isLocal($mount, $this->getFSResources())) {

            $srcFile = '' . XApp_Path_Utils::decodeSecureMagic($to);

            $destFile = $vfs->toRealPath($mount . DIRECTORY_SEPARATOR . $srcFile);

            if (is_dir($destFile) && (!file_exists($destFile) || !is_file($destFile))) {

                $destDirectory = '' . $destFile;

                //default file name
                $fileName = 'remoteFile.download';

                //try to get a file name from the url through parse_url(pathinfo())
                $urlParts = parse_url($url);
                if ($urlParts['path']) {
                    $pathInfo = pathinfo($urlParts['path']);
                    if ($pathInfo['basename']) {
                        $fileName = $pathInfo['basename'];
                    }
                }

                $destFile = XApp_File_Utils::unique_filename($destFile, $fileName);

                $destFile = $destDirectory . DIRECTORY_SEPARATOR . $destFile;

                touch($destFile);
            }
            try {

                $srcPath = XApp_Download::download($realSrcFile);
                if (!file_exists($srcPath)) {
                    return self::toRPCError(1, 'Error downloading ' . $srcPath . ' Error : ' . $srcPath);
                }
                $src = fopen($srcPath, "r");
                $dest = fopen($destFile, "w");

                if ($dest !== false) {
                    while (!feof($src)) {
                        stream_copy_to_stream($src, $dest, 4096);
                    }
                    fclose($dest);
                }
                fclose($src);
                @unlink($srcPath);

            } catch (Exception $e) {
                return self::toRPCError(1, $e->getMessage());
            }
        } else {
            $mountManager = $vfs->getMountManager();
            $to = XApp_Path_Utils::normalizePath($to, false, false);
            $to = $mount . '://' . $to;
            $to = $vfs->cleanSlashesRemote($to);
            try {
                $src = fopen($realSrcFile, "r");
                while (!feof($src)) {
                    $mountManager->updateStream($to, $src);
                }
                fclose($src);
            } catch (Exception $e) {
                return self::toRPCError(1, $e->getMessage());
            }
        }

        /*
        $errors = XApp_ErrorHandler::stop();
        if($errors){
            xapp_clog($errors);
            $this->logger->log(json_encode($errors));
        }
        */
        /*xapp_clog($errors);*/
        return true;
    }


    /**
     * @return bool|mixed
     */
    public function fileUpdate()
    {

        $srcPath = null;
        if (array_key_exists('srcPath', $_GET)) {
            $srcPath = $_GET['srcPath'];
        }
        $remoteUrl = null;
        if (array_key_exists('image', $_GET)) {
            $remoteUrl = '' . $_GET['image'];
        }

        $title = null;
        if (array_key_exists('title', $_GET)) {
            $title = '' . $_GET['title'];
        }
        $type = null;
        if (array_key_exists('type', $_GET)) {
            $type = '' . $_GET['type'];
        }

        $mount = null;
        if (array_key_exists('mount', $_GET)) {
            $mount = '' . $_GET['mount'];
        }
        if (strpos($srcPath, 'php') !== false || strpos($srcPath, 'sh') !== false) {
            die('bye');
        }
        if ($remoteUrl && $srcPath) {
            if ($type && $title) {
                $srcPath = dirname($srcPath) . DIRECTORY_SEPARATOR . $title . '.' . $type;
            }
            return $this->downloadTo($remoteUrl, $mount, $srcPath);
        } else {
            error_log('file update failed');
        }

        return true;
    }
}