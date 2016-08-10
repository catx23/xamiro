<?php
/**
 * @version 0.1.0
 *
 * @author https://github.com/mc007
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */
class XApp_Store_Delegate extends XApp_Store_Base implements Xapp_Store_Interface
{

    private $readerStore=null;
    private $writerStore=null;


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

    protected  function getData(){


        $path = xapp_get_option(self::CONF_FILE,$this);
        $data = null;
        if(file_exists($path)){
            $data  = (object)XApp_Utils_JSONUtils::read_json($path,'json',false,true,null,false,null);
            if($data==null){
                $data = new stdClass();
            }
        }
        return $data;
    }

    protected  function setData($data){
        $path = xapp_get_option(self::CONF_FILE,$this);
        if(file_exists($path)){
            $result = null;
            $pretty = true;
            if($pretty)
            {
                $result = file_put_contents($path, Xapp_Util_Json::prettify($data), LOCK_EX);
            }else{
                $result = file_put_contents($path, $data, LOCK_EX);
            }
            if($result !== false)
            {
                $result = null;
                return true;
            }else{
                throw new Xapp_Util_Json_Exception("unable to save to file: " . $path, 1690501);
            }

        }
        return $data;
    }
    /***
     * @param $storeRoot
     * @param $store
     */
    public function getReadStore(){}

    /***
     * @param $storeRoot
     * @param $store
     */
    public function getWriteStore(){}

    /**
     * @return mixed
     */
    public function read(){
	    return $this->getData();
    }

    /***
     * @return mixed
     */
    public function write($data){
	    return $this->setData($data);
    }
}