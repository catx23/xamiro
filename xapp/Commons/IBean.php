<?php
/**
 * @author     Guenter Baumgart
 * @author     David Grudl
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */

define('XAPP_CAN_NONE','0');
define('XAPP_CAN_EDIT','1');
define('XAPP_CAN_COPY','2');
define('XAPP_CAN_MOVE','3');
define('XAPP_CAN_INFO','4');
define('XAPP_CAN_DOWNLOAD','5');
define('XAPP_CAN_COMPRESS','6');
define('XAPP_CAN_DELETE','7');
define('XAPP_CAN_RENAME','8');
define('XAPP_CAN_DND','9');
define('XAPP_CAN_COPY_PASTE','10');
define('XAPP_CAN_OPEN','11');
define('XAPP_CAN_RELOAD','12');
define('XAPP_CAN_NEW','16');
define('XAPP_CAN_NEW_GROUP','17');
define('XAPP_CAN_UPLOAD','18');
define('XAPP_CAN_READ','19');
define('XAPP_CAN_WRITE','20');
define('XAPP_CAN_PLUGINS','21');
define('XAPP_CAN_UNKOWN','22');

define('XAPP_BEAN_PERMISSIONS','permissions');


interface XApp_Bean_Interface
{
    function canDelete($refId=null,$userId=null);

    function canEdit($refId=null,$userId=null);

    function canAdd($refId=null,$userId=null);

    function getPermission($refId=null,$userId=null);

}
