<?php
/**
 * @version 0.1.0
 *
 * @author https://github.com/mc007
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */

/***
 * Class XApp_App_Renderer
 *
 * A XApp application is usually a Dojo Javascript application and needs few paths and settings.
 * This class is meant to render out HTML.
 */
class XApp_App_Renderer
{

    /***
    * Relative url to the client's app doc root
    */
    const DOC_ROOT   = "XAPP_APP_DOC_ROOT";

    /***
     * Absolute path to the client's app doc root
     */
    const DOC_ROOT_PATH   = "XAPP_APP_DOC_ROOT_PATH";

    /***
     * Name of the client app.
     */
    const APP_NAME     = "XAPP_APP_NAME";

    /***
     * Path segment to the Dojo app.
     */
    const APP_FOLDER     = "XAPP_APP_FOLDER";

    /***
     * Run-time configuration. There are only 2 : debug & release
     */
    const CONFIG_NAME     = "XAPP_APP_RUNTIME_CONFIGURATION";

    /***
     * The applications relative service url.
     */
    const SERVICE_URL     = "XAPP_APP_SERVICE_URL";

    /***
     * The application's theme.
     */
    const THEME     = "XAPP_APP_THEME";

    /***
     * The application's resource renderer. This class adds client resources to the final HTML output.
     */
    const RESOURCE_RENDERER     = "XAPP_APP_RESOURCE_RENDERER";

    /**
     * options dictionary for this class containing all data type values
     *
     * @var array
     */
    public static $optionsDict = array
    (
        self::DOC_ROOT          => XAPP_TYPE_STRING,
        self::DOC_ROOT_PATH     => XAPP_TYPE_STRING,
        self::APP_NAME          => XAPP_TYPE_STRING,
        self::APP_FOLDER        => XAPP_TYPE_STRING,
        self::CONFIG_NAME       => XAPP_TYPE_STRING,
        self::SERVICE_URL       => XAPP_TYPE_STRING,
        self::THEME             => XAPP_TYPE_STRING,
        self::RESOURCE_RENDERER => XAPP_TYPE_CLASS,
    );

    /**
     * options mandatory map for this class contains all mandatory values
     *
     * @var array
     */
    public static $optionsRule = array
    (
        self::DOC_ROOT               => 0,
        self::DOC_ROOT_PATH          => 0,
        self::APP_NAME               => 0,
        self::APP_FOLDER             => 0,
        self::CONFIG_NAME            => 0,
        self::SERVICE_URL            => 0,
        self::THEME                  => 0,
        self::RESOURCE_RENDERER      => 0
    );

    /**
     * options default value array containing all class option default values
     *
     * @var array
     */
    public $options = array
    (
        self::DOC_ROOT                   => null,
        self::DOC_ROOT_PATH              => null,
        self::APP_NAME                   => null,
        self::APP_FOLDER                 => null,
        self::CONFIG_NAME                => null,
        self::SERVICE_URL                => null,
        self::THEME                      => null,
        self::RESOURCE_RENDERER          => null

    );


    public function printPaths(){
        echo('<br/> XAPP APP RENDERER - PATHS<br/>');
        echo('APP NAME : ' . xapp_get_option(self::APP_NAME,$this) . '<br/>');
        echo('APP FOLDER : ' . xapp_get_option(self::APP_FOLDER,$this) . '<br/>');
        echo('DOC ROOT : ' . xapp_get_option(self::DOC_ROOT,$this) . '<br/>');
        echo('DOC ROOT PATH : ' . xapp_get_option(self::DOC_ROOT_PATH,$this) . '<br/>');
        echo('SERVICE URL : ' . xapp_get_option(self::SERVICE_URL,$this) . '<br/>');
        echo('APP THEME : ' . xapp_get_option(self::THEME,$this) . '<br/>');
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
        xapp_set_options($options, $this);
    }

    /***
     * Resolves resources, uses system specific renderer
     * @return bool
     */
    public function resolveResources(){
        $resourceRenderer = xapp_get_option(self::RESOURCE_RENDERER,$this);
        if($resourceRenderer){
            $resourceRenderer->resolveResources();
            return true;
        }
        return false;
    }

    /***
     * Renders resources, uses system specific renderer
     * @return bool
     */
    public function renderResources(){
        $resourceRenderer = xapp_get_option(self::RESOURCE_RENDERER,$this);
        if($resourceRenderer){
            return $resourceRenderer->render();
        }
        return '';
    }

    /***
     * Renders resources, uses system specific renderer
     * @return bool
     */
    public function renderHead(){
        return $this->renderResources();
    }
    /***
     * Renders resources, uses system specific renderer
     * @return bool
     */
    public function renderBodyIncludes(){
        $resourceRenderer = xapp_get_option(self::RESOURCE_RENDERER,$this);
        if($resourceRenderer){
            return $resourceRenderer->renderJavascriptBodyTags();
        }
        return '';
    }
}
