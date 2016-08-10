<?php
/**
 * @version 0.1.0
 *
 * @author Guenter Baumgart
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 * @package XApp\Store\JSON
 */
xapp_import("xapp.Store.StoreBase");
xapp_import("xapp.Store.Interface.*");
xapp_import('xapp.Utils.JSONUtils');

class XApp_Store_Json extends XApp_Store_Base implements Xapp_Store_Interface {

    /***
     * @param $section
     * @param string $path     *
     * @param query, a Json path query
     * @return string
     */
    public function get($section,$path,$query=null) {

    }
    public function set($section,$path='.',$searchQuery=null,$value=null,$decodeValue=true){}

    public function read() {
	    $pass = null;
	    if(xapp_has_option(self::CONF_PASSWORD)){
		    $pass = xapp_get_option(self::CONF_PASSWORD);
	    }
	    return XApp_Utils_JSONUtils::read_json( xo_get(self::CONF_FILE,$this),'json',false,true,null,true,$pass);
    }
    public function write($data) {
		$pass = null;
	    if(xapp_has_option(self::CONF_PASSWORD)){
		    $pass = xapp_get_option(self::CONF_PASSWORD);
	    }
        return XApp_Utils_JSONUtils::write_json(xo_get(self::CONF_FILE,$this),$data,'json',true,$pass);
    }

}

?>