<?php
/**
 * @version 0.1.0
 *
 * @author https://github.com/mc007
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */

/***
 * Class XApp_Standalone_Resource_Renderer
 * @remarks : renders resources into strings or prints out
 */
class XApp_Standalone_Resource_Renderer extends  XApp_Resource_Renderer
{
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

    /***
     * Register default resource variables
     */
    public function registerDefault(){
        parent::registerDefault();
    }

    public function renderCSS(){
        $cssItems = $this->getResourcesByType(XAPP_RESOURCE_TYPE_CSS,true);

        if($cssItems!=null && count($cssItems)){

            $styleTags = '';
            $print=true;
            foreach($cssItems as $resourceItem){

                if($resourceItem==null || !is_object($resourceItem)){
                    continue;
                }
                if(!xapp_property_exists($resourceItem,XAPP_RESOURCE_URL_RESOLVED)){
                    $resourceItem = $this->resolveResource($resourceItem);
                }
                if(xapp_property_exists($resourceItem,XAPP_RESOURCE_URL_RESOLVED)){
                    $url = xapp_property_get($resourceItem,XAPP_RESOURCE_URL_RESOLVED);

                    if(xapp_property_exists($resourceItem,'preventCache')){
                        $url.='?time='.microtime();
                    }

	                if( strpos($url,'http')!==false && isset($_SERVER['HTTPS'] ) ) {
		                $url = str_replace('http','https',$url);
	                }

                    $rel = 'stylesheet';
                    $styleTag = "<link rel='" . $rel . "' id='css_" . md5($url) . " 'href='" .$url . "'  type='text/css' />\n";
                    $styleTags.=$styleTag;

                }else{
                    error_log('adding resource failed : url not resolved');
                }
            }
            if($print){
                echo($styleTags);
            }
            return $styleTags;
        }
        return null;
    }

    public function renderJavascriptHeaderTags(){

        $javaScriptHeader = '<script type="application/javascript">';
        $contentOut = '';
        $cssItems = $this->getResourcesByType(XAPP_RESOURCE_TYPE_JS_HEADER_SCRIPT_TAG,true);
        if($cssItems!=null && count($cssItems)){

            foreach($cssItems as $resourceItem){

                if(!is_object($resourceItem)){
                    continue;
                }
                if(!xapp_property_exists($resourceItem,XAPP_RESOURCE_URL_RESOLVED)){
                    $resourceItem = $this->resolveResource($resourceItem);
                }

                if(xapp_property_exists($resourceItem,XAPP_RESOURCE_PATH_ABSOLUTE)){
                    $path = xapp_property_get($resourceItem,XAPP_RESOURCE_PATH_ABSOLUTE);
                    if(file_exists($path)){
                        $content = file_get_contents($path);
                        if(($content !== false)){
                            $content = $this->resolveRelative($content);
                            $contentOut.=$content;
                        }
                    }else{

                    }
                }
            }
        }
        $javaScriptHeader.=$contentOut;
        $javaScriptHeader.= '</script>';
        echo($javaScriptHeader);
        return $contentOut;
    }
    public function getJavascriptPlugins(){
        $plugins = array();
        $items = $this->getResourcesByType(XAPP_RESOURCE_TYPE_JS_PLUGIN,true);
        if($items!=null && count($items)){
            foreach($items as $resourceItem){
                $plugins[]=$resourceItem;
            }
        }else{
            return null;
        }
        $plugins = json_encode($plugins);
        $plugins = $this->resolveRelative($plugins);//replace user variables
        return json_decode($plugins);

    }
    public function addJSIncludes(){
        $scriptItems = $this->getResourcesByType(XAPP_RESOURCE_TYPE_JS_INCLUDE,true);
        $print=true;
        if($scriptItems!=null && count($scriptItems)){
            $scriptTags = '';
            foreach($scriptItems as $resourceItem){

                if(!is_object($resourceItem)){
                    continue;
                }
                if(!xapp_property_exists($resourceItem,XAPP_RESOURCE_URL_RESOLVED)){
                    $resourceItem = $this->resolveResource($resourceItem);
                }
                if(xapp_property_exists($resourceItem,XAPP_RESOURCE_URL_RESOLVED)){
                    $url = xapp_property_get($resourceItem,XAPP_RESOURCE_URL_RESOLVED);
                    $scriptTag = "<script type='text/javascript' src='" . $url . "'></script>\n";
                    $scriptTags.=$scriptTag;
                }else{
                    /*error_log('adding resource failed : url not resolved : ');*/
                }
            }

            if($print===true){
                echo ($scriptTags);
            }
            return $scriptTags;
        }
        return null;
    }

}
