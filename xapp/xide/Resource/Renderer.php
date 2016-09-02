<?php
/**
 * @version 0.1.0
 *
 * @author https://github.com/mc007
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */

/***
 * Class XIDE_Resource_Renderer
 * @remarks : renders resources into strings or prints out
 */
class XIDE_Resource_Renderer extends XApp_Resource_Renderer
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


	/**
	 * Renders all resource variables as script tag, this goes in xide.manager.ResourceManager
	 */
	public function renderResourceVariables(){

	}
	/**
	 * @param bool $print
	 * @return null|string|void
	 */
	public function renderCSS($print=false){

		$cssItems = $this->getResourcesByType(XAPP_RESOURCE_TYPE_CSS,true);

		if($cssItems!=null && count($cssItems)){

			$styleTags = '';
			foreach($cssItems as $resourceItem){

				if($resourceItem==null || !is_object($resourceItem)){
					continue;
				}
				if(!xapp_property_exists($resourceItem,XAPP_RESOURCE_URL_RESOLVED)){
					$resourceItem = $this->resolveResource($resourceItem);
				}

				$preventCache=xapp_property_exists($resourceItem,'preventCache');

				if(xapp_property_exists($resourceItem,XAPP_RESOURCE_URL_RESOLVED)){

					$url = xapp_property_get($resourceItem,XAPP_RESOURCE_URL_RESOLVED);

					$isFlatten = xapp_property_exists($resourceItem,XAPP_RESOURCE_FLATTEN) && xapp_property_get($resourceItem,XAPP_RESOURCE_FLATTEN)===true;

					if($isFlatten){

						if(xapp_property_exists($resourceItem,XAPP_RESOURCE_PATH_ABSOLUTE)){
							$path = xapp_property_get($resourceItem,XAPP_RESOURCE_PATH_ABSOLUTE);
							if(file_exists($path)){
								$content = file_get_contents($path);

								if(($content !== false)){

									$matches=array();
									preg_match_all('/@import "([^\"]+)"/i', $content, $matches);
									$baseName = basename($url);
									$baseUrl = '' . str_replace($baseName,'',$url);
									foreach ($matches[1] as $i => $cssUrl) {

										$newCSSUrl = $baseUrl . $cssUrl;
										if($preventCache){
											$newCSSUrl.='?time='.microtime();
										}
										$rel = 'stylesheet';

										if(self::isSecure()) {
											$url = preg_replace("/^http:/i", "https:", $url);
										}
										$styleTag = "<link rel='" . $rel . "' id='css_" . md5($newCSSUrl) . "' href='" .$newCSSUrl . "'  type='text/css' />\n";
										$styleTags.=$styleTag;

									}
								}
							}
						}
					}else{

						if($preventCache){
							$url.='?time='.microtime();
						}
						$rel = 'stylesheet';
						if(self::isSecure()) {
							$url = preg_replace("/^http:/i", "https:", $url);
						}
						$styleTag = "<link rel='" . $rel . "' id='css_" . md5($url) . "' href='" .$url . "'  type='text/css' />\n";
						$styleTags.=$styleTag;
					}

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

	/**
	 * @param bool $print
	 * @return string|void
	 */
	public function renderJavascriptHeaderTags($print=false){

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
		if($print){
			echo($javaScriptHeader);
		}
		return $javaScriptHeader;
	}
	/**
	 * @param bool $print
	 * @return string|void
	 */
	public function renderJavascriptBodyTags($print=false){

		$scriptItems = $this->getResourcesByType(XAPP_RESOURCE_TYPE_JS_INCLUDE_BODY,true);
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
				}
			}

			if($print){
				echo ($scriptTags);
			}
			return $scriptTags;
		}
		return '';
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

	public function addJSIncludes($print=false){
		$scriptItems = $this->getResourcesByType(XAPP_RESOURCE_TYPE_JS_INCLUDE,true);
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
				}
			}

			if($print){
				echo ($scriptTags);
			}
			return $scriptTags;
		}
		return '';
	}


	public function render(){
		$all='';
		$all.=$this->addJSIncludes(false);
		$all.=$this->renderCSS(false);
		$all.=$this->renderJavascriptHeaderTags(false);
		return $all;
	}

}
