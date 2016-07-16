<?php
/**
 * @version 0.1.0
 *
 * @author Guenter Baumgart
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 * @package XApp\xide\Logging
 */
xapp_import("xapp.Commons.Entity");
xapp_import('xapp.Utils.Strings');
xapp_import('xapp.Utils.Shell');
xapp_import('xapp.xide.Base.Manager');

/**
 * Class XIDE_Log_Manager
 */
class XIDE_Log_Manager extends XIDE_Manager{

    /////////////////////////////////////////////////////////////////////////////////////////
    //
    //  Hook/Event Keys
    //
    ////////////////////////////////////////////////////////////////////////////////////////

    /////////////////////////////////////////////////////////////////////////////////////////
    //
    //  Constants
    //
    ////////////////////////////////////////////////////////////////////////////////////////

    /////////////////////////////////////////////////////////////////////////////////////////
    //
    //  Options
    //
    ////////////////////////////////////////////////////////////////////////////////////////

    //Standard options
    const LOG_PATH                          = "XAPP_NODE_JS_LOGGING_PATH";
    const EMITS                             = "XAPP_EMITS"; //disable or enable hooks

    /**
     * options dictionary for this class containing all data type values
     *
     * @var array
     */
    public static $optionsDict = array
    (
        self::LOG_PATH              => XAPP_TYPE_STRING
    );

    /**
     * options mandatory map for this class contains all mandatory values
     *
     * @var array
     */
    public static $optionsRule = array
    (
        self::LOG_PATH              => 0
    );

    /**
     * options default value array containing all class option default values
     * @var array
     */
    public $options = array
    (
       self::LOG_PATH              => 'logs/all.log'
    );
    
    public function clear($which=null){
        $path       = realpath(xo_get(self::LOG_PATH,$this));
	    if($which){
		    $path = str_replace('all.log',$which . '.json',$path);
	    }

        if(!file_exists($path)){
            return '{}';
        }
        $result = array();
        if(file_exists($path) && @is_writeable($path)){
            file_put_contents($path,"");
        }else{
            return '{}';
        }
        return $result;
    }

    public function clearAbs($path=null){
        if(!file_exists($path)){
            return '{}';
        }
        $result = array();
        if(file_exists($path) && @is_writeable($path)){
            file_put_contents($path,"");
        }else{
            return '{}';
        }
        return $result;
    }

	/**
	 * @param null $which
	 *
	 * @return array|string
	 */
    public function ls($which=null){

	    $path       = realpath(xo_get(self::LOG_PATH,$this));
        if($which){
		    $path = str_replace('all.log',$which,$path) . '.json';
	    }

        if(!file_exists($path)){
		    //$this->log('log file path : ' . $path  .' doesnt exists');
		    return '{}';
	    }
	    $result = array();

        if(file_exists($path) && is_readable($path)){
            $handle = fopen($path, "r");
            if ($handle) {
                while (($line = fgets($handle)) !== false) {

	                $lineData = json_decode($line);

	                if($lineData) {
		                $result[] = $lineData;
		            }
                }
            } else {
                // error opening the file.
            }
            fclose($handle);
        }else{
            xapp_clog('file doesnt exists '.$path);
            return '{}';
        }
	    return $result;
    }

    /**
     * @param null $which
     *
     * @return array|string
     */
    public function lsAbs($path){

        $_path = '' . $path;
        $path       = realpath($path);
        if(!file_exists($path)){
            error_log('path ' .$_path  . ' doesnt exists');
            return '{}';
        }
        $result = array();
        if(file_exists($path) && is_readable($path)){
            $handle = fopen($path, "r");
            if ($handle) {
                while (($line = fgets($handle)) !== false) {

                    $lineData = json_decode($line);

                    if($lineData) {
                        $result[] = $lineData;
                    }
                }
            } else {
                // error opening the file.
            }
            fclose($handle);
        }else{
            xapp_clog('file doesnt exists '.$path);
            return '{}';
        }
        return $result;
    }



    /**
     * class constructor
     * call parent constructor for class initialization
     *
     * @error 14601
     * @param null|array|object $options expects optional options
     */
    function __construct($options = null)
    {
        parent::__construct($options);
        //standard constructor
        xapp_set_options($options, $this);
    }

}


?>