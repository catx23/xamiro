<?php
/**
 * @version 0.1.0
 *
 * @author https://github.com/mc007
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 * @package XApp\Service
 */

xapp_import('xapp.Commons.Mixins');
xapp_import('xapp.Service.Logger');
xapp_import('xapp.Rpc.Interface.Callable');


/***
 * Class XApp_Service provides a minimal of functions and exposes a managed class to a public
 * RPC interface
 */
class XApp_Service extends XApp_Service_Logger implements Xapp_Rpc_Interface_Callable
{

    /**
     * Return a resource name for the ACL system
     * @return string
     */
    public static function getResourceName(){
        return "";
    }

    /***
     * The name of the class as string or an instance. An instance does not need options of course.
     */
    const MANAGED_CLASS          = 'XAPP_SERVICE_MANAGED_CLASS';

    /***
     * The name of the class as string or an instance. An instance does not need options of course.
     */
    const MANAGED_CLASS_BASE_CLASSES          = 'XAPP_SERVICE_MANAGED_CLASS_BASE_CLASSES';

    /***
     * The options of the wrapped managed class
     */
    const MANAGED_CLASS_OPTIONS  = 'XAPP_SERVICE_MANAGED_CLASS_OPTIONS';

    /***
     * In case we have managed class, publish this options to
     */
    const PUBLISH_METHODS        = 'XAPP_SERVICE_PUBLISH_METHODS';

    /***
     * A instance to a logger
     */
    const LOGGER                 = 'XAPP_SERVICE_LOGGER';

	/***
	 * A instance to the bootstrap
	 */
	const BOOTSTRAP                 = 'XAPP_BOOTSTRAP';

    /*******************************************************************************/
    /*  XApp option satisfaction
    /*******************************************************************************/

    /**
     * options dictionary for this class containing all data type values
     *
     * @var array
     */
    public static $optionsDict = array
    (
        self::MANAGED_CLASS                 => array(XAPP_TYPE_OBJECT,XAPP_TYPE_STRING),
        self::MANAGED_CLASS_OPTIONS         => XAPP_TYPE_ARRAY,
        self::MANAGED_CLASS_BASE_CLASSES    => XAPP_TYPE_ARRAY,
        self::PUBLISH_METHODS               => XAPP_TYPE_ARRAY,
        self::LOGGER                        => XAPP_TYPE_OBJECT,
        self::BOOTSTRAP                        => XAPP_TYPE_OBJECT
    );
    /**
     * options mandatory map for this class contains all mandatory values
     *
     * @var array
     */
    public static $optionsRule = array
    (
        self::MANAGED_CLASS                 => 0,
        self::MANAGED_CLASS_OPTIONS         => 0,
        self::MANAGED_CLASS_BASE_CLASSES    => 0,
        self::PUBLISH_METHODS               => 0,
        self::LOGGER                        => 0,
	    self::BOOTSTRAP                     => 0
    );
    /**
     * options default value array containing all class option default values
     *
     * @var array
     */
    public $options = array
    (
        self::MANAGED_CLASS                 => null,
        self::MANAGED_CLASS_OPTIONS         => null,
        self::MANAGED_CLASS_BASE_CLASSES    => null,
        self::PUBLISH_METHODS               => null,
        self::LOGGER                        => null,
        self::BOOTSTRAP                     => null
    );


    /**
     * class constructor
     * call parent constructor for class initialization
     *
     * @error 14601
     * @param null|array|object $options expects optional options
     */
    public function __construct($options = null)
    {
        //standard constructor
        xapp_set_options($options, $this);

        //now parse options
        $this->parseOptions(xapp_get_options($this));

    }

    /***
     * The managed class instance
     */
    protected $_object;

    /***
     * Getter for _object
     * @return mixed
     */
    public function getObject(){
        return $this->_object;
    }

    /***
     * Create and wire the managed class instance
     * @param $options
     */
    private function parseOptions($options){

        if($this->_object == null && $options && xo_has(self::MANAGED_CLASS,$options)){

            //the class  
            $_managedClass = xo_get(self::MANAGED_CLASS,$options);

            //check its an instance already :
            if(is_object($_managedClass)){
                $this->_object = $_managedClass;
            }//its a string
            elseif(is_string($_managedClass) && class_exists($_managedClass)){

                $baseClasses = xo_has(self::MANAGED_CLASS_BASE_CLASSES,$options) ? xo_get(self::MANAGED_CLASS_BASE_CLASSES,$options) : null;

                $_ctrArgs = xo_has(self::MANAGED_CLASS_OPTIONS,$options) ? xo_get(self::MANAGED_CLASS_OPTIONS,$options) : array();


                //no additional base classes :
                if($baseClasses==null || !count($baseClasses)){
                    $this->_object = new $_managedClass($_ctrArgs);
                }else{
                    //mixin new base classes
                    xapp_import('xapp.Commons.ClassMixer');
                    $newClassName = "NEW_" .$_managedClass;
                    XApp_ClassMixer::create_mixed_class($newClassName , $_managedClass, $baseClasses);
                    $this->_object = new $newClassName($_ctrArgs);
                }

            }

	        if($this->_object){
                if(method_exists($this->_object,'init')){
                    $this->_object->init();
                }
            }
        }

    }

    /*******************************************************************************/
    /*  Constants
    /*******************************************************************************/

    /***
     * Fields of a service structure.
     */
    const XAPP_SERVICE_CLASS    = 'XAPP_SERVICE_CLASS';
    const XAPP_SERVICE_CONF     = 'XAPP_SERVICE_CONF';
    const XAPP_SERVICE_INSTANCE = 'XAPP_SERVICE_INSTANCE';

    /***
     * Xapp_Rpc_Interface_Callable Impl. Before the actual call is being invoked
     */
    public function onBeforeCall(Xapp_Rpc_Server $server, Array $params){}

    /***
     *Xapp_Rpc_Interface_Callable Impl. After the actual call
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

    /***
     * @param int $code
     * @param $messages
     * @param $data
     * @return array
     */
    static function toRPCError($code=1,$messages,$data = null){
        $result = array();
        $result['error']['code']=$code;
        $result['error']['message']=$messages;
        $result['error']['data']=$data;
        return $result;
    }
    /***
     * @param array $imports
     * @param $className is in Java Import style : xapp.Directory.Service => XApp_Directory_Service
     * @param $configuration
     */
    public static function factory(
        $importName,
        $configuration=Array(),
        $baseClasses=null)
    {

        $className ='' .$importName;
        if(!class_exists($className)){
            $className = str_replace('xapp','XApp',$importName);
            $className = str_replace('.','_',$className);
            if(!class_exists($className)){
                xapp_import($importName);
            }
        }

        return array(
            self::XAPP_SERVICE_CLASS             =>$className,
            self::XAPP_SERVICE_CONF              =>$configuration,
            self::XAPP_SERVICE_INSTANCE          =>null
        );
    }
    /***
     * Another factory method to bypass xapp_import problems
     * @param $className
     * @param null $configuration
     * @return mixed
     */
    public static function factoryEx(
        $className,
        $configuration=null)
    {
        if(is_string($className)){
            $reflection = new ReflectionClass($className);
            if($reflection->implementsInterface('Xapp_Singleton_Interface')){
                $instance = $className::instance($configuration);
            }else{
                $instance = new $className($configuration);
            }
        }elseif (is_object($className)){
            $instance = $className;
        }

        return array(
            self::XAPP_SERVICE_CLASS             =>$className,
            self::XAPP_SERVICE_CONF              =>xapp_get_options($instance),
            self::XAPP_SERVICE_INSTANCE          =>$instance
        );
    }

}