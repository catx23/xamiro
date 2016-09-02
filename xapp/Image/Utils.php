<?php
/**
 * Class XApp_Image_Utils
 *
 * Provides image manipulation functions
 */
xapp_import("xapp.Option.Utils");
xapp_import("xapp.Image.Handlers.*");
xapp_import('xapp.Cache.Cache');
xapp_import('xapp.Cache.Driver');
xapp_import('xapp.Cache.Driver.Image');

define("XAPP_IMAGE_CACHE_DEFAULT_PATH",XAPP_BASEDIR . DS . 'cacheDir');

class XApp_Image_Utils
{

    /***
     * Default cache directory
     * @var string
     */
    public static $cacheDir = XAPP_IMAGE_CACHE_DEFAULT_PATH;

    /**
     *  Constants
     */
    const IMAGE_OPERATION = "operation";
    const IMAGE_MEMORY_LIMIT = '128M';

    //Image operations
    const OPERATION_RESIZE = "resize";
    const OPERATION_ICONIFY = "iconify";
    const OPERATION_CROP = "crop";
    const OPERATION_OPTIONS = "options";

    /***
     * XApp options
     */
    const OPTION_WIDTH = "width";
    const OPTION_HEIGHT = "height";
    const OPTION_FORCE_IMAGE_HANDLER = "force_image_handler";

    const OPTION_HAS_CACHE = "hasCache";
    const OPTION_PREVENT_CACHE = "preventCache";
    const OPTION_CACHE_DIR = "cacheDir";


    /**
     * options dictionary for this class containing all data type values
     *
     * @var array
     */
    public static $optionsDict = array
    (
        self::OPTION_WIDTH                  => XAPP_TYPE_STRING,
        self::OPTION_HEIGHT                 => XAPP_TYPE_STRING,
        self::OPTION_FORCE_IMAGE_HANDLER    => XAPP_TYPE_STRING,
        self::OPTION_HAS_CACHE              => XAPP_TYPE_BOOL,
        self::OPTION_PREVENT_CACHE          => XAPP_TYPE_BOOL
    );

    /**
     * options mandatory map for this class contains all mandatory values
     *
     * @var array
     */
    public static $optionsRule = array
    (
        self::OPTION_WIDTH                  => 0,
        self::OPTION_HEIGHT                 => 0,
        self::OPTION_FORCE_IMAGE_HANDLER    => 0,
        self::OPTION_HAS_CACHE              => 0,
        self::OPTION_PREVENT_CACHE          => 0
    );

    /**
     * options default value array containing all class option default values
     *
     * @var array
     */
    public $options = array
    (
        self::OPTION_WIDTH                  => 0,
        self::OPTION_HEIGHT                 => 0,
        self::OPTION_FORCE_IMAGE_HANDLER    => '',
        self::OPTION_HAS_CACHE              => false,
        self::OPTION_PREVENT_CACHE          => false
    );


    /**
     * Compatible image php extensions
     *
     * @var array
     */
    public static $compatibleExtensions = array(
        "gd",
        "imagick"
    );

    /**
     * Compatible image types
     *
     * @var array
     */
    public static $compatibleImageTypes = array(
        "gif" => "gif",
        "jpg" => "jpeg",
        "jpeg" => "jpeg",
        "png" => "png"
    );

    /**
     *  Image container key values
     */
    const IMAGE_CONTAINER_SIZE = "size";
    const IMAGE_CONTAINER_TYPE = "type";
    const IMAGE_CONTAINER_SRC = "src";
    const IMAGE_CONTAINER_DATA = "data";
    const IMAGE_CONTAINER_FROM_CACHE = "from_cache";

    /**
     * Container for image information
     *
     * @var array
     */
    public static $imageContainer = array(

        self::IMAGE_CONTAINER_SIZE => null,
        self::IMAGE_CONTAINER_TYPE => "",
        self::IMAGE_CONTAINER_SRC => "",
        self::IMAGE_CONTAINER_DATA => null,
        self::IMAGE_CONTAINER_FROM_CACHE => false

    );


    /**
     * @param null $extension
     * @return XApp_ImageGD|XApp_ImageImagick
     */
    private static function getHandler( $extension = null ) {
        if (!$extension)
        {
            $extension = self::checkExtensions();
        }
	    switch ($extension)
        {
            case "imagick":
                $handler = new XApp_ImageImagick;
                break;
            case "gd":
                $handler = new XApp_ImageGD;
                break;
        }
        return $handler;
    }

    /**
     * Performs image manipulation functions
     *
     *
     * @param $src
     * @param null $dst
     * @param string $jobs
     * @param array $errors
     * @return bool
     */
    public static function execute($src,$dst=null,$jobs="",&$errors=Array(),$save=true,$cache=true,$print=true) {

	    ini_set('memory_limit', self::IMAGE_MEMORY_LIMIT);

        // Check if any of the required extensions is present
        $extension = self::checkExtensions();
        if (!$extension)
        {
            $errors[] = XAPP_TEXT_FORMATTED("IMAGE_EXTENSION_NOT_FOUND",Array( implode(",",self::$compatibleExtensions) ));
            return false;
        }

        // Parse jobs JSON string
        if ( ($jobsarray = json_decode($jobs)) === FALSE )
        {
            $errors[] = XAPP_TEXT_FORMATTED("BAD_JSON_STRING");
            return false;
        }

        $handler =  self::getHandler($extension);

        // Main loop
        foreach($jobsarray as $job)
        {
            $options = (array)$job->{self::OPERATION_OPTIONS};


            // Open file
            $container = self::open($src,$handler,$options,$errors);
            if (count($errors)>0)
            {
                return false;
            }

            // Perform operation (if image and options are not already in cache)
            if (!$container[self::IMAGE_CONTAINER_FROM_CACHE])
            {
                switch($job->{self::IMAGE_OPERATION})
                {
                    case self::OPERATION_RESIZE:
                        $container = $handler::resize($container,$options,$errors);
                    break;
                    case self::OPERATION_ICONIFY:
                        $container = $handler::iconify($container,$options,$errors);
                    break;
                    case self::OPERATION_CROP:
                    break;
                }
	            if($cache===true) {
		            self::addToCache($handler, $container, $options);
	            }

            }

            // Save file
            if ($dst != null && $save===true)
            {
                $handler::save($container,$dst,$errors);
            }
            else {
                if($print===true) {
	                header("Content-Type: image/" . $container[self::IMAGE_CONTAINER_TYPE]);
	                echo $handler::output($container);
                }else{
	                $handler::output($container);
                }
            }
        }

	    return true;
    }

    /**
     *
     * Returns the image cache key
     *
     * @param $src
     * @param $options
     * @return string
     */
    public static function cacheKey($src,$options) {
        // avoid "prevent cache" changes creating new cache files
        unset($options[self::OPTION_PREVENT_CACHE]);

        $ext = pathinfo($src,PATHINFO_EXTENSION);
        $option_string = json_encode($options);
        return $src . $option_string . "." . $ext;
    }

    /**
     * Stores the image into cache
     *
     * @param $handler
     * @param $container
     * @param $options
     */
    public static function addToCache($handler,$container,$options) {
        $cache = self::getCache($options);
        if($cache){
	        return $cache->set( self::cacheKey($container[self::IMAGE_CONTAINER_SRC],$options) , $handler::output($container));
        }
        return false;
    }

    /**
     * Returns the cache driver
     *
     * @param $options
     * @return Xapp_Cache_Driver_Image
     */
    private static function getCache($options) {
        if(is_writeable(self::$cacheDir)){
	        $cache = Xapp_Cache_Driver_Image::instance(Array(
                Xapp_Cache_Driver_Image::PATH => self::$cacheDir,
                Xapp_Cache_Driver_Image::DEFAULT_EXPIRATION => 60  * 60
            ));
            return $cache;
        }
        return null;
    }


    /**
     * Opens the image, or gets it from the cache if exists
     *
     *
     * @param $src
     * @param $handler
     * @param $options
     * @param $errors
     * @return array|mixed
     */
    public static function open($src,&$handler,$options,&$errors) {
        $cached = false;
        // Force an image handler if set in options
        if ( xo_has(self::OPTION_FORCE_IMAGE_HANDLER,$options) )
        {
            $handler = self::getHandler( xo_get(self::OPTION_FORCE_IMAGE_HANDLER,$options) );
        }

        // Open the cache version
        if ( xo_get(self::OPTION_PREVENT_CACHE,$options)!="true"  )
        {
            $cache = self::getCache($options);

            if ($cache->has(self::cacheKey($src,$options))) {
                if ( ($cache_file = $cache->get( self::cacheKey($src,$options) )) != null )
                {
                    $container = $handler::open($cache_file,$errors);
                    $cached = $container[self::IMAGE_CONTAINER_FROM_CACHE] = true;
                }
            }
        }

        if (!$cached) $container = $handler::open($src,$errors);

        return $container;
    }

    /**
     * Calculates proportional with or height
     *
     * @param $currentSize
     * @param $options
     * @return bool|stdClass
     */
    public static function calcImageSize($currentSize,$options) {
        $newsize = new stdClass();
        $newsize -> width = intval( xo_get(self::OPTION_WIDTH,$options) );
        $newsize -> height = intval( xo_get(self::OPTION_HEIGHT,$options) );

        // If width or height are missing, calculate proportional dimension
        if ( $newsize->width == 0)
        {
            $newsize->width = ceil( ($newsize->height/$currentSize->height) * $currentSize->width );
        }
        elseif ( $newsize->height == 0 )
        {
            $newsize->height = ceil( ($newsize->width/$currentSize->width) * $currentSize->height );
        }

        // of both are missing, return false
        elseif (( $newsize->width == 0) && ( $newsize->height == 0))
        {
            return false;
        }

        return $newsize;
    }

    /**
     * return image file extension (jpg, gif, png...)
     *
     * @param $src
     * @return mixed
     */
    public static function imageExtension($src) {
        return strtolower(pathinfo($src,PATHINFO_EXTENSION));
    }


    /**
     * Check if any of the compatible extensions are present
     *
     * @return bool
     */
    private static function checkExtensions() {
        foreach(self::$compatibleExtensions as $extension)
        {
            if (extension_loaded($extension))
            {
                return $extension;
            }
        }
        return false;
    }
}