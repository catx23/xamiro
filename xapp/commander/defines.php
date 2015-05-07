<?php
/**
 * @version 0.1.0
 *
 * @author https://github.com/mc007
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 * @package XApp-Commander
 */

/**
 * data type constant for type mixed
 * @const XAPP_TYPE_MIXED
 */
define('XC_OPERATION_NONE','0');
define('XC_OPERATION_UNKOWN','0');
define('XC_OPERATION_EDIT','1');
define('XC_OPERATION_COPY','2');
define('XC_OPERATION_MOVE','3');
define('XC_OPERATION_INFO','4');
define('XC_OPERATION_DOWNLOAD','5');
define('XC_OPERATION_COMPRESS','6');
define('XC_OPERATION_DELETE','7');
define('XC_OPERATION_RENAME','8');
define('XC_OPERATION_DND','9');
define('XC_OPERATION_COPY_PASTE','10');
define('XC_OPERATION_OPEN','11');
define('XC_OPERATION_RELOAD','12');
define('XC_OPERATION_PREVIEW','13');
define('XC_OPERATION_INSERT_IMAGE','15');
define('XC_OPERATION_NEW_FILE','16');
define('XC_OPERATION_NEW_DIRECTORY','17');
define('XC_OPERATION_UPLOAD','18');
define('XC_OPERATION_READ','19');
define('XC_OPERATION_WRITE','20');

define('XC_OPERATION_PLUGINS','21');
define('XC_OPERATION_CUSTOM','22');
define('XC_OPERATION_FIND','23');

define('XC_OPERATION_ADD_MOUNT','25');
define('XC_OPERATION_REMOVE_MOUNT','26');
define('XC_OPERATION_EDIT_MOUNT','27');
define('XC_OPERATION_PERSPECTIVE','28');
define('XC_OPERATION_EXTRACT','32');


define('XC_OPERATION_NONE_STR','none');
define('XC_OPERATION_EDIT_STR','edit');

define('XC_OPERATION_FIND_STR','find');

define('XC_OPERATION_COPY_STR','copy');
define('XC_OPERATION_MOVE_STR','move');
define('XC_OPERATION_INFO_STR','info');
define('XC_OPERATION_DOWNLOAD_STR','get');
define('XC_OPERATION_COMPRESS_STR','compress');
define('XC_OPERATION_DELETE_STR','delete');
define('XC_OPERATION_RENAME_STR','rename');
define('XC_OPERATION_DND_STR','dnd');
define('XC_OPERATION_COPY_PASTE_STR','copypaste');
define('XC_OPERATION_RELOAD_STR','reload');
define('XC_OPERATION_NEW_FILE_STR','mkfile');
define('XC_OPERATION_NEW_DIRECTORY_STR','mkdir');
define('XC_OPERATION_UPLOAD_STR','put');
define('XC_OPERATION_READ_STR','get');
define('XC_OPERATION_WRITE_STR','set');
define('XC_OPERATION_FILE_UPDATE_STR','putRemote');
define('XC_OPERATION_PLUGINS_STR','plugins');
define('XC_OPERATION_CUSTOM_STR','custom');
define('XC_OPERATION_ADD_MOUNT_STR','ADD_MOUNT');
define('XC_OPERATION_REMOVE_MOUNT_STR','REMOVE_MOUNT');
define('XC_OPERATION_EDIT_MOUNT_STR','EDIT_MOUNT');
define('XC_OPERATION_PERSPECTIVE_STR','PERSPECTIVE');
define('XC_OPERATION_EXTRACT_STR','extract');


define('XC_OPERATION_UNKOWN_STR','unkown');


define('XC_PANEL_OPTION_ALLOW_NEW_TABS','ALLOW_NEW_TABS');
define('XC_PANEL_OPTION_ALLOW_MULTI_TABS','ALLOW_MULTI_TABS');
define('XC_PANEL_OPTION_ALLOW_INFO_VIEW','ALLOW_INFO_VIEW');
define('XC_PANEL_OPTION_ALLOW_LOG_VIEW','ALLOW_LOG_VIEW');
define('XC_PANEL_OPTION_ALLOW_BREADCRUMBS','ALLOW_BREADCRUMBS');
define('XC_PANEL_OPTION_ALLOW_CONTEXT_MENU','ALLOW_CONTEXT_MENU');
define('XC_PANEL_OPTION_ALLOW_SOURCE_SELECTOR','ALLOW_SOURCE_SELECTOR');
define('XC_PANEL_OPTION_ALLOW_LAYOUT_SELECTOR','ALLOW_LAYOUT_SELECTOR');

define('XAPP_FILE_START_PATH','XAPP_FILE_START_PATH');
define('XAPP_FILE_ROOT','XAPP_FILE_ROOT');
define('XAPP_AUTH_TOKEN','XAPP_AUTH_TOKEN');
define('XAPP_CLIENT_IP','XAPP_CLIENT_IP');

/***
 * Service types
 */
define("XAPP_SERVICE_TYPE_SMD_GET","XAPP_SERVICE_TYPE_SMD_GET");
define("XAPP_SERVICE_TYPE_SMD_CALL","XAPP_SERVICE_TYPE_SMD_CALL");
define("XAPP_SERVICE_TYPE_UPLOAD","XAPP_SERVICE_TYPE_UPLOAD");
define("XAPP_SERVICE_TYPE_DOWNLOAD","XAPP_SERVICE_TYPE_DOWNLOAD");
define("XAPP_SERVICE_TYPE_CBTREE","XAPP_SERVICE_TYPE_CBTREE");
define("XAPP_SERVICE_TYPE_UNKNOWN","XAPP_SERVICE_TYPE_UNKNOWN");

/**
 * option to specify a event key prefix
 */
define('XAPP_EVENT_PREFIX', 'xapp.xcom.');
/***
 * event content keys
 */
define('XAPP_CLIENT_EVENT', 'clientEvent');
define('XAPP_CLIENT_EVENT_FILE_CHANGED','onFileContentChanged');
define('XAPP_EVENT_KEY_PATH', 'path');
define('XAPP_EVENT_KEY_CLIENT_EVENT', 'clientEvent');
define('XAPP_EVENT_KEY_REL_PATH', 'relPath');
define('XAPP_EVENT_KEY_CONTENT', 'content');
define('XAPP_EVENT_KEY_CALLEE', 'callee');
define('XAPP_EVENT_KEY_SUBSCRIBER', 'subscriber');

if(!function_exists('xcom_event')){

    /**
     * @param $operation
     * @param string $suffix
     * @param $args
     * @param null $callee
     */
    function xcom_event($operation,$suffix='',$args,$callee=null){
        if($callee!==null && $args!==null){
            $args[XAPP_EVENT_KEY_CALLEE]=$callee;
        }
        xapp_event(XAPP_EVENT_PREFIX . $suffix . $operation,array($args));
    }
}
if(!function_exists('xcom_subscribe')){
    /**
     * @param $operation
     * @param $mixed
     * @param string $suffix
     */
    function xcom_subscribe($operation,$mixed,$suffix=''){
        Xapp_Event::listen(XAPP_EVENT_PREFIX . $operation . $suffix, $mixed);
    }
}
