<?php
/**
 * @version 0.1.0
 *
 * @author Luis Ramos
 * @author Guenter Baumgart
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 * @package XApp\Security\Store
 */
xapp_import("xapp.Store.StoreBase");
xapp_import("xapp.Store.Interface.*");
xapp_import('xapp.Utils.JSONUtils');
if(!class_exists('XApp_Store_JSON2')){

    class XApp_Store_JSON2 extends XApp_Store_Base implements Xapp_Store_Interface {

    public function get($section,$path,$query=null) {}
    public function set($section,$path='.',$searchQuery=null,$value=null,$decodeValue=true){}
    public function read() {
        return XApp_Utils_JSONUtils::read_json( xo_get(self::CONF_FILE,$this),'json',false,true);
    }
    public function write($data) {
        return XApp_Utils_JSONUtils::write_json(xo_get(self::CONF_FILE,$this),$data,'json',true);
    }
}
}

?>