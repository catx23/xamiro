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
xapp_import('xapp.xide.Models.User');
xapp_import('xapp.xide.Models.UserRole');
xapp_import('xapp.xide.Models.UserPermission');
xapp_import('xapp.Security.*');

/***
 * Class XApp_UserService is the RPC wrapper for UserManager
 */
class XApp_XIDE_Controller_UserService extends XApp_Service implements Xapp_Singleton_Interface {

    const USER_MANAGER_CONF             = "XAPP_USER_MANAGER_CONF";

    /**
     * options dictionary for this class containing all data type values
     *
     * @var array
     */
    public static $optionsDict = array
    (
        self::USER_MANAGER_CONF         => XAPP_TYPE_ARRAY,
        self::MANAGED_CLASS             => XAPP_TYPE_STRING
    );

    /**
     * options mandatory map for this class contains all mandatory values
     *
     * @var array
     */
    public static $optionsRule = array
    (
        self::USER_MANAGER_CONF         => 1,
        self::MANAGED_CLASS             => 1
    );
    /**
     * options default value array containing all class option default values
     *
     * @var array
     */
    public $options = array
    (
        self::USER_MANAGER_CONF         => null,
        self::MANAGED_CLASS             => 'XApp_UserManager',
        self::PUBLISH_METHODS           => array('login')
    );

    /***
     * @param null $options
     */
    /*
    public function __construct($options = null)
    {
        parent::__construct($options);
        error_log(xo_get(self::MANAGED_CLASS,$this));
    }
    */

    public function test(){

    }

    /***
     * Xapp_Rpc_Interface_Callable Impl. Before the actual call is being invoked
     */
    public function onBeforeCall(Xapp_Rpc_Server $server, Array $params){

        parent::onBeforeCall($server, $params);
    }

    /***
     * Xapp_Rpc_Interface_Callable Impl. After the actual call is being invoked
     */
    public function onAfterCall(Xapp_Rpc_Server $server, Array $params) {
        // Trigger HOOK_RPC_AFTER_CALL
        //Xapp_Hook::trigger(self::HOOK_RPC_AFTER_CALL,array('userManagerInstance'=>$this));
    }

    /**
     * contains the singleton instance for this class
     *
     * @var null|XApp_XIDE_Controller_UserService
     */
    protected static $_instance = null;
    /**
     * Xapp_Singleton interface impl.
     *
     * static singleton method to create static instance of driver with optional third parameter
     * xapp options array or object
     *
     * @error 15501
     * @param null|mixed $options expects optional xapp option array or object
     * @return XApp_XIDE_Controller_UserService
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
     * @param $userName
     * @param $password
     * @return string
     */
    public function login($userName,$password){

        $errors = array();

        $usrMgr=$this->getObject();

        $loginResult = $usrMgr->login($userName,$password,$errors);

        return $loginResult ? "OK" : "Bad";
    }

}


?>