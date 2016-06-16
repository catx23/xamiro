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
/***
 * Class XIDE_Manager
 */
class XIDE_Manager extends XIDE_Scoped
{
    public $logger=null;

    static function toRPCError($code = 1, $messages)
    {
        $result = array();
        $result['code'] = $code;
        $result['result'] = array();
        $result['result'][] = $messages;
        return $result;
    }

    static function toRPCErrorStd($code = 1, $messages)
    {
        $result = array();
        $result['error']['code'] = $code;
        $result['error']['message'] =   $messages;
        return $result;
    }
    /**
     * @param $message
     * @param string $prefix
     * @param bool $stdError
     * @return null
     */
    public function log($message,$prefix='',$stdError=true){
        if(function_exists('xp_log')){
            xp_log(__CLASS__ . ' : ' . $message);
        }
        if($stdError){
            error_log(__CLASS__ . ' : ' .$message);
        }
        return null;
    }
}
