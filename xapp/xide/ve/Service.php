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
class XIDE_VE_Service extends XIDE_Service implements Xapp_Singleton_Interface, Xapp_Rpc_Interface_Callable
{
	public static $_instance=null;
    /***
     * Returns all registered services
     * @return mixed
     */
    public function ls(){

	    $bootstrap = xapp_get_option(self::BOOTSTRAP,$this);
	    $xcom = $bootstrap->getServiceConfiguration('XCOM_Directory_Service');
	    return $this->getObject()->ls();
    }

	private function getFileStruct($path){
		$bootstrap = xo_get(self::BOOTSTRAP);
		$xcom = $bootstrap->getServiceConfiguration('XCOM_Directory_Service');
		$xcom = $xcom[XApp_Service::XAPP_SERVICE_INSTANCE];
		if ( base64_encode(base64_decode($path, true)) === $path){
			$path = base64_decode($path);
		} else {
			echo 'data is NOT valid';
		}

		$path = urldecode ($path);
		$mount = XApp_Path_Utils::getMount($path);
		$path = XApp_Path_Utils::getRelativePart($path);
		$vfs = $xcom->getFileSystem($mount);

		return array(
			'path'=>$path,
			'mount'=>$mount,
			'vfs'=>$vfs,
		);
	}

	private function getContent($path,$attachment=false,$send=true,$width=null,$time=null){

		$bootstrap = xo_get(self::BOOTSTRAP);
		$xcom = $bootstrap->getServiceConfiguration('XCOM_Directory_Service');
		$xcom = $xcom[XApp_Service::XAPP_SERVICE_INSTANCE];
		if ( base64_encode(base64_decode($path, true)) === $path){
			$path = base64_decode($path);
		} else {
			echo 'data is NOT valid';
		}

		$path = urldecode ($path);

		$mount = XApp_Path_Utils::getMount($path);
		$path = XApp_Path_Utils::getRelativePart($path);

		$vfs = $xcom->getFileSystem($mount);


		$content = $vfs->get(
			XApp_Path_Utils::normalizePath($mount),
			XApp_Path_Utils::securePath(XApp_Path_Utils::normalizePath($path,true,false)),
			$attachment,
			array(
				XApp_File_Utils::OPTION_SEND=>$send,
				XApp_File_Utils::OPTION_RESIZE_TO=>$width,
				XApp_File_Utils::OPTION_PREVENT_CACHE=>isset($time)
			)
		);
		return $content;
	}
	public function view($file){
		$content = $this->getContent($file,false,false);
		return $this->getObject()->renderContent($content,$this->getFileStruct($file),xo_get(self::BOOTSTRAP));

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
     * @return XIDE_VE_Service
     */
    public static function instance($options = null)
    {
        if(self::$_instance === null)
        {
            self::$_instance = new self($options);
        }
        return self::$_instance;
    }



}