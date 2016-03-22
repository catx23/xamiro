<?php
/**
 * @version 0.1.0
 *
 * @author https://github.com/mc007
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 * @package XApp\xide\Directory
 */

xapp_import('xapp.VFS.Interface.Access');
xapp_import('xapp.Directory.Service');

/***
 * Class XIDE_Directory_Service extends the standard directory service
 */
class XIDE_Directory_Service extends XApp_Directory_Service
{

	/**
	 * Directory listing
	 * @link http://localhost/xide/code/php/xide-php/server/xide/service.php?service=XApp_Directory_Service.ls&callback=asdF&path=/ws/&recursive=true
	 * @param string $mount
	 * @param string $path
	 * @param null $options
	 * @return mixed
	 */
    public function ls($mount='ws',$path='/',$options=null){

	    $recursive=false;

	    $vfs = $this->getFileSystem($mount);

		/**
		 * variable normalization
		 */
		$basePath = XApp_Path_Utils::normalizePath($mount,false,false);
		if($basePath==='' || $basePath==='.'){
			$basePath='ws';
		}
		$path = XApp_Path_Utils::normalizePath($path,false,false);
		$scanPath =  $basePath . DIRECTORY_SEPARATOR .$path;
		$scanPath = XApp_Path_Utils::normalizePath($scanPath,false,false);


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
		$items = $vfs->ls(XApp_Path_Utils::normalizePath($scanPath,false),$recursive,$options);

		return $items;

	}

	/***
     * Xapp_Rpc_Interface_Callable Impl. Before the actual call is being invoked.
     *
     */
	/**
	 * @param Xapp_Rpc_Server $server
	 * @param array $params
	 */
    public function onBeforeCall(Xapp_Rpc_Server $server, Array $params){
	    parent::init();
        Xapp_Hook::connect(XApp_VFS_Base::EVENT_ON_NODE_META_CREATED,$this,"_onItem");
    }
    /***
     * Maqetta & XIDE specific extension, we mixin 2 new attributes per node and we mark common directories
     * as read-only
     * @param $evt
     */
    public function _onItem($evt){

        if(is_array($evt) && $evt['item']){

            $item = $evt['item'];
            $item->{XAPP_NODE_FIELD_IS_DIRTY}=false;
            $item->{XAPP_NODE_FIELD_IS_NEW}=false;

            //mark as readOnly
            if( $item->{XAPP_NODE_FIELD_IS_DIRECTORY}==true &&
                (   $item->{XAPP_NODE_FIELD_NAME}=='clipart' ||
                    $item->{XAPP_NODE_FIELD_NAME}=='dojo' ||
                    $item->{XAPP_NODE_FIELD_NAME}=='maqetta' ||
                    $item->{XAPP_NODE_FIELD_NAME}=='shapes' ||
                    $item->{XAPP_NODE_FIELD_NAME}=='samples'||
                    $item->{XAPP_NODE_FIELD_NAME}=='themes')

            ){
                $item->{XAPP_NODE_FIELD_READ_ONLY}=true;
            }
            return $item;
        }
        return null;
    }
}