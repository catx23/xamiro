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
 * Class XIDE_NodeJS_Service extends the standard service
 * @link : http://192.168.1.37:81/x4mm/Code/trunk/xide-php/xapp/xcf/index.php?debug=true&view=smdCall&service=XIDE_NodeJS_Service.ls&callback=asdf
 */
class XIDE_NodeJS_Service extends XIDE_Service implements Xapp_Singleton_Interface, Xapp_Rpc_Interface_Callable
{
    const SERVER_IS_READY = "serverIsReady";
    const RUN_RESULT = "runResult";
    const SERVER_URL = "serverUrl";
    public static $_instance=null;


    /***
     * Returns all registered services
     * @return mixed
     */
    public function ls(){
        return $this->getObject()->ls();
    }

    /***
     * Stop a number services of by name
     * @return mixed
     */
    public function stop($services=array()){
        return $this->getObject()->stop($services);
    }

    /***
     * Start a number services of by name
     * @return mixed
     */
    public function start($services=array()){
        return $this->getObject()->start($services);
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
     * @return XIDE_NodeJS_Service
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
     * @param $arguments
     * @return array
     * @link xapp/xcf/index.php?debug=true&view=smdCall&service=XCF_NodeJS_Debug_Service.run&callback=asd&nodeApp=test/testme.js&workingPath=/PMaster/x4mm/Utils/nodejs/
     */
    public function run($arguments) {
        xo_merge($arguments,$this->_object);
        $result=$this->getObject()->run();

        return Array(
            self::RUN_RESULT=>$result,
            self::SERVER_URL => $this->getDebugServerURL()
        );
    }

    /***
     * Check if debug server is up.
     *
     * return json string width self::SERVER_IS_READY => (true/false)
     *
     * @param $arguments
     * @return string
     */
    public function checkServer($arguments) {
        xo_merge($arguments,$this->_object);
        $result=$this->getObject()->checkServer();
        return array(
            self::SERVER_IS_READY=>$result
        );

    }

    public function runDebugServer($arguments) {
        xo_merge($arguments,$this->_object);

        return Array(
            self::RUN_RESULT=>$this->getObject()->runDebugServer()
        );
    }

    private function getDebugServerURL() {
        $options = $this->getObject()->options;

        $url = "http://";
        $url.= $options[XCF_NodeJS_Debug_Manager::DEBUG_TCP_HOST];
        $url.= ":";
        $url.= $options[XCF_NodeJS_Debug_Manager::DEBUG_TCP_PORT];
        $url.= "/debug?port=";
        $url.= $options[XCF_NodeJS_Debug_Manager::DEBUG_PORT];

        return $url;
    }
    ////////////////////////////////////////////////////////////////////////
    //
    //  Xapp_Rpc_Interface_Callable impl.
    //
    ////////////////////////////////////////////////////////////////////////

    /**
     * Method that will be called before the actual requested method is called.
     * Before any call
     * @return boolean
     */
	public function onBeforeCall(Xapp_Rpc_Server $server, Array $params){
        $this->getObject()->initVariables();
    }


    /**
     * method that will be called after the requested method has been invoked
     *
     * @return boolean
     */
    //public function onAfterCall($function=null, $class=null, $params=null){}
}