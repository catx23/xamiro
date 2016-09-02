<?php
/**
 * @version 0.1.0
 *
 * @author Guenter Baumgart
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 * @package XApp\Image\Service
 */

xapp_import("xapp.Commons.Entity");
xapp_import("xapp.Service.Service");
xapp_import("xapp.Image.Utils");

/***
 * Class XApp_UserService is the RPC wrapper for UserManager
 */
class XApp_Image_Service extends XApp_Service implements Xapp_Singleton_Interface {

    const IMAGE_CACHE_DIR = 'XAPP_IMAGE_CACHE_DIR';
    /**
     * options dictionary for this class containing all data type values
     *
     * @var array
     */
    public static $optionsDict = array
    (
        self::MANAGED_CLASS             => XAPP_TYPE_STRING,
        self::IMAGE_CACHE_DIR           => XAPP_TYPE_DIR
    );

    /**
     * options mandatory map for this class contains all mandatory values
     *
     * @var array
     */
    public static $optionsRule = array
    (
        self::MANAGED_CLASS             => 0,
        self::IMAGE_CACHE_DIR           => 1
    );
    /**
     * options default value array containing all class option default values
     *
     * @var array
     */
    public $options = array
    (
        self::MANAGED_CLASS             => null,
        self::IMAGE_CACHE_DIR           => XAPP_IMAGE_CACHE_DEFAULT_PATH

    );

    /***
     * Xapp_Rpc_Interface_Callable Impl. Before the actual call is being invoked
     */
    public function onBeforeCall(Xapp_Rpc_Server $server, Array $params){
        parent::onBeforeCall($server,$params);
    }


    /**
     * contains the singleton instance for this class
     *
     * @var null|XApp_Image_Service
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
     * @return XApp_Image_Service
     */
    public static function instance($options = null)
    {
        if(self::$_instance === null)
        {
            self::$_instance = new self($options);
        }
        return self::$_instance;
    }

    /////////////////////////////////////////////////////////////////////////////////////////
    //
    //  Public RPC API
    //
    ////////////////////////////////////////////////////////////////////////////////////////
    public function resize($src,$width=null,$save=true,$cache=true,$height=null,$preventCache=false,$icon=false,$forceHandler='') {

	    XApp_Image_Utils::$cacheDir = xo_get(self::IMAGE_CACHE_DIR,$this);
	    $errors=Array();
        if ($icon)
        {
            $operation = XApp_Image_Utils::OPERATION_ICONIFY;
        }
        else
        {
            $operation = XApp_Image_Utils::OPERATION_RESIZE;

        }

        $jobs = '
            [
             {
              "'.XApp_Image_Utils::IMAGE_OPERATION.'" : "'.$operation.'",
              "'.XApp_Image_Utils::OPERATION_OPTIONS.'" : {
                    "'.XApp_Image_Utils::OPTION_WIDTH.'" : "'.$width.'",
                    "'.XApp_Image_Utils::OPTION_HEIGHT.'" : "'.$height.'",
                    "'.XApp_Image_Utils::OPTION_PREVENT_CACHE.'" : "'.$preventCache.'",
                    "'.XApp_Image_Utils::OPTION_FORCE_IMAGE_HANDLER.'" : "'.$forceHandler.'"
                }
             }
            ]
            ';

        XApp_Image_Utils::execute($src,"",$jobs,$errors,$save,$cache);

    }
}


?>