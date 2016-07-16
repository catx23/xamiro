<?php
/**
 * @version 0.1.0
 *
 * @author https://github.com/mc007
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 * @package XApp\xcf\Driver
 */

xapp_import('xapp.xide.Service.Service');

/***
 * Class XIDE_Log_Service extends the standard service
 * @link : http://192.168.1.37:81/x4mm/Code/trunk/xide-php/xapp/xcf/index.php?debug=true&view=smdCall&service=XIDE_Log_Service.ls&callback=asdf
 */
class XIDE_Log_Service extends XIDE_Service implements Xapp_Singleton_Interface, Xapp_Rpc_Interface_Callable
{
	public static $_instance=null;

    /***
     * Returns all registered services
     * @link http://192.168.1.37:81/x4mm/Code/trunk/xide-php/xapp/xcf/index.php?debug=true&view=smdCall&service=XIDE_Log_Service.ls&callback=asd
     * @param which {string}
     * @return mixed
     */
    public function ls($which=null){
        return $this->getObject()->ls($which);
    }
	/***
	 * @link http://192.168.1.37:81/x4mm/Code/trunk/xide-php/xapp/xcf/index.php?debug=true&view=smdCall&service=XIDE_Log_Service.ls&callback=asd
	 * @param path {string}
	 * @return mixed
	 */
	public function lsAbs($path=null){
		return $this->getObject()->lsAbs($path);
	}

	public function clear($which){
        return $this->getObject()->clear($which);
    }
	public function clearAbs($path){
		return $this->getObject()->clearAbs($path);
	}
    /**
     * class constructor
     * call parent constructor for class initialization
     *
     * @error 14601
     * @param null|array|object $options expects optional options
     */
    public function __construct($options = null)
    {
        parent::__construct($options);
        xapp_set_options($options, $this);
    }


    /**
     * Xapp_Singleton interface impl.
     *
     * static singleton method to create static instance of driver with optional third parameter
     * xapp options array or object
     *
     * @error 15501
     * @param null|mixed $options expects optional xapp option array or object
     * @return XCF_Driver_Service
     */
    public static function instance($options = null)
    {

        if(self::$_instance === null)
        {
            self::$_instance = new self($options);

        }
        return self::$_instance;
    }

    ////////////////////////////////////////////////////////////////////////
    //
    //  Xapp_Rpc_Interface_Callable impl.
    //
    ////////////////////////////////////////////////////////////////////////

	/**
	 * method that will be called before the actual requested method is called. the callback can return anything but unless
	 * the returned value is a boolean "false" the result is not used. in case of returning a boolean false the actual
	 * rpc request method is not called instead the "onAbort" function is called.
	 *
	 * @param Xapp_Rpc_Server $server
	 * @param array $params
	 * @return null|mixed
	 */
	public function onBeforeCall(Xapp_Rpc_Server $server, Array $params){}


	/**
	 * method that will be called after the requested method has been invoked. if the method returns anything that is not
	 * "NULL" the result from the invoked method or "onBeforeCall" is overwritten!
	 *
	 * @param Xapp_Rpc_Server $server
	 * @param array $params
	 * @return null|mixed
	 */
	public function onAfterCall(Xapp_Rpc_Server $server, Array $params){}


	/**
	 * method that will be called if onBeforeCall returns boolean false. the returned value will be send to rpc response
	 * instead of the value returned by the to be invoked method from rpc request
	 *
	 * @param Xapp_Rpc_Server $server
	 * @param array $params
	 * @return null|mixed
	 */
	public function onAbort(Xapp_Rpc_Server $server, Array $params){}


	/**
	 * method that will be called when invoking called service and service throws error while execute. catch this error/
	 * exception for logging or otherwise manipulating it and pass it back into the error handling process.
	 *
	 * @param Xapp_Rpc_Server $server
	 * @param Exception $e
	 * @return void|null|Exception
	 */
	public function onError(Xapp_Rpc_Server $server, Exception $e){}
}