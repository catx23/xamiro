<?php
/**
 * @version 0.1.0
 *
 * @author https://github.com/mc007
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */
class XIDE_CIStore_Delegate extends XApp_Store_Base implements Xapp_Store_Interface
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
        xapp_set_options($options, $this);
    }


    public function set($section,$path='.',$searchQuery=null,$value=null,$decodeValue=true){

        $dataAll = $this->read();

        $storeData = json_encode($dataAll);
        $store = new Xapp_Util_Json_Query($storeData);
        $value = is_string($value) ? json_decode($value) : $value;

        $stdQuery = null;
        $success=false;
        if($searchQuery){

            //1. convert string query into an query map
            $stdQuery=$this->toStdQuery(is_string($searchQuery) ? json_decode($searchQuery,true) : $searchQuery);

            //2. prepare sanity test
            $queryResult = $store->query($path,$stdQuery)->get();

            //3. did find something ?
            if($queryResult!=null&& count($queryResult)==1){

                //4. now construct the real store
                $store = Xapp_Util_Json_Store::create($storeData);
                //5. re-run the query
                $a = $store->query($path, $stdQuery);
                //merge new value into query result
                $newValue = $this->_merge($queryResult[0],$value);
                //update store
                $a->set(null,$newValue);

                $success=true;
            }else{
                //xapp_clog($value,'cant find ci ' . ' ' . ' ');
                return false;
            }
        }

        if($success){
            $result = $store->object();
            $this->write(json_encode($result));
            return $result;
        }

        return null;
    }

    /***
     * @return mixed|null|object|stdClass
     */
    public function read(){
        $path = xapp_get_option(self::CONF_FILE,$this);
        $data = null;
        if(file_exists($path)){
            $data  = (object)XApp_Utils_JSONUtils::read_json($path,'json',false,true,null,false);
            if($data==null){
                $data = new stdClass();
            }
        }
        return $data;
    }

    public  function write($data){


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

}