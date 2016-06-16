<?php
/**
 * @version 0.1.0
 *
 * @author https://github.com/mc007
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */

class XApp_Store_Base
{
    /***
     *
     */
    const READER_CLASS                      = "XAPP_STORE_READER_CLASS";
    /***
     *
     */
    const WRITER_CLASS                      = "XAPP_STORE_WRITER_CLASS";
    /***
     *
     */
    const PRIMARY_KEY                       = "XAPP_STORE_PRIMARY_KEY";
    /***
     *
     */
    const IDENTIFIER                        = "XAPP_STORE_IDENTIFIER";

    /***
     *  ID property
     */
    const ID_PROPERTY                       = "XAPP_STORE_ID_PROPERTY";

    /***
     *  Data property
     */
    const DATA_PROPERTY                     = "XAPP_STORE_DATA_PROPERTY";
    /***
     *  File path
     */
    const CONF_FILE                         = "XAPP_STORE_CONF_FILE";
	/***
	 *  File path
	 */
	const CONF_PASSWORD                     = "XAPP_STORE_CONF_PASSWORD";
    /**
     * options dictionary for this class containing all data type values
     *
     * @var array
     */
    public static $optionsDict = array
    (
        self::READER_CLASS                     => XAPP_TYPE_STRING,
        self::WRITER_CLASS                     => XAPP_TYPE_STRING,
        self::PRIMARY_KEY                      => XAPP_TYPE_STRING,
        self::IDENTIFIER                       => XAPP_TYPE_STRING,
        self::ID_PROPERTY                      => XAPP_TYPE_STRING,
        self::DATA_PROPERTY                    => XAPP_TYPE_STRING,
        self::CONF_FILE                        => XAPP_TYPE_STRING,
	    self::CONF_PASSWORD                    => XAPP_TYPE_STRING

    );
    /**
     * options mandatory map for this class contains all mandatory values
     *
     * @var array
     */
    public static $optionsRule = array
    (
        self::READER_CLASS                     => 0,
        self::WRITER_CLASS                     => 0,
        self::PRIMARY_KEY                      => 0,
        self::IDENTIFIER                       => 0,
        self::ID_PROPERTY                      => 0,
        self::DATA_PROPERTY                    => 0,
        self::CONF_FILE                        => 0,
	    self::CONF_PASSWORD                    => 0
    );
    /**
     * options default value array containing all class option default values
     *
     * @var array
     */
    public $options = array
    (
        self::READER_CLASS                     => null,
        self::WRITER_CLASS                     => null,
        self::PRIMARY_KEY                      => 'section',
        self::IDENTIFIER                       => 'identifier',
        self::ID_PROPERTY                      => 'id',
        self::DATA_PROPERTY                    => 'data',
        self::CONF_FILE                        => '',
	    self::CONF_PASSWORD                    => ''
    );
    /***
     *  The actual store object
     */
    protected $store=null;
    /**
     * Xapp_Singleton interface impl.
     *
     * static singleton method to create static instance of driver with optional third parameter
     * xapp options array or object
     *
     * @error 15501
     * @param null|mixed $options expects optional xapp option array or object
     * @return XApp_Store
     */
    public static function instance($options = null)
    {
        if(self::$_instance === null)
        {
            self::$_instance = new self($options);
        }
        return self::$_instance;
    }
    /**
     * contains the singleton instance for this class
     *
     * @var null|XApp_Store
     */
    protected static $_instance = null;

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
    /**
     *  $store = Xapp_Util_Json_Store::create($object)
            ->query('/firstElement', array("id=1000"))
            ->query('.', array('title=foo'))
            ->set(null, 1);
     * @param $inQuery  object  to find the item to be changed
     * @return array
     */
    protected static function toStdQuery($inQuery){
        $res = array();
        $idx=0;
        foreach($inQuery as $item => $val){

            $res[$idx]=''.('' .$item . '=' . $val);
            $idx++;
        }
        return $res;
    }

    /**
     * Transfer new value to std::query result item
     * @param $dstElement
     * @param $newElement
     * @return array
     */
    public function _merge(&$dstElement,$newElement){
        $dstElement=array_merge((array) $dstElement, (array) $newElement);
        return $dstElement;
    }

    /***
     * @param query, a Json path query
     * @return string
     */
    public function get($section,$path,$query=null){
        $dataAll = $this->read();
        $userData= null;
        if($dataAll){
            $primKey = xapp_get_option(self::PRIMARY_KEY,$this);
            if(!property_exists($dataAll,$primKey)){
                $dataAll->{$primKey}=new stdClass();
            }
            $userData=$dataAll->{$primKey};
        }

        $store = new Xapp_Util_Json_Store($userData);
        if($query) {
	        $stdQuery=$this->toStdQuery(is_string($query) ? json_decode($query,true) : $query);
	        $qRes  = $store->query($path, $stdQuery)->getResult();
	        return $qRes;
        }else{
            //error_log('have no query');
        }

        return $userData;
    }

    public function update($section,$path='.',$searchQuery=null,$value=null,$decodeValue=true){
        return $this->set($section,$path,$searchQuery,$value,$decodeValue);
    }

    public function set($section,$path='.',$searchQuery=null,$value=null,$decodeValue=true){
        $dataAll = $this->read();

	    $userData= null;
        if($dataAll){
            $primKey = xapp_get_option(self::PRIMARY_KEY,$this);
            if(!property_exists($dataAll,$primKey)){
                $dataAll->{$primKey}=new stdClass();
            }
            $userData=$dataAll->{$primKey};
        }

        if(!property_exists($userData,$section)){
            $userData->{$section}=array();
        }
        $storeData = json_encode($userData);
        $store = new Xapp_Util_Json_Query($storeData);
        $value = is_string($value) ? json_decode($value) : $value;

	    if($decodeValue===false && (is_array($value)|| is_object($value))){
            $value = json_encode($value,true);
        }
        if(!$value){
            return false;
        }

        $success=false;
        $stdQuery = null;
	    if($searchQuery){

		    $stdQuery=$this->toStdQuery(is_string($searchQuery) ? json_decode($searchQuery,true) : $searchQuery);
            $queryResult = $store->query($path,$stdQuery)->get();
            $dataProp = xapp_get_option(self::DATA_PROPERTY,$this);

            if(!$queryResult || !count($queryResult)){
                $primKey = xapp_get_option(self::PRIMARY_KEY,$this);
                $idProp = xapp_get_option(self::ID_PROPERTY,$this);
                $itemKey = $searchQuery->{$idProp};
                $object = $store->getObject();
                if($itemKey){
                    $object->{$section}[]=$searchQuery;
                }
                $queryResult = $store->query($path,$stdQuery)->get();
            }

            if($queryResult!=null && count($queryResult)==1){

                if($dataProp && strlen($dataProp)) {
                    $queryResult[0]->{xapp_get_option(self::DATA_PROPERTY, $this)} = $value;
                    $success=true;
                }else{
                    $this->_merge($queryResult[0],$value);
                    $success=true;
                }
            }else{
                return false;
            }
        }else{
            if(is_array($userData->{$section})){
                $found = null;
                $dataProp = xapp_get_option(self::DATA_PROPERTY,$this);
                foreach($userData->{$section} as $item){
                    if(property_exists($item,xapp_get_option(self::ID_PROPERTY,$this))){
                        $found=$item;
                        break;
                    }
                }


	            $value  = (object)$value;
	            if(is_string($value->{$dataProp})){
		            $value->{$dataProp}=json_decode($value->{$dataProp});
	            }
	            if($found){
		            $userData->{$section}[0]->{$dataProp}=$value->{$dataProp};
                }else{
                    $userData->{$section}[]=$value;//implicit
                }
	            $dataAll->{$primKey}= $userData;

                $this->write(json_encode($dataAll));

            }
        }
        if($success){
            $dataAll->{$primKey} = $store->getObject();
            $this->write(json_encode($dataAll));
            return true;
        }

        return false;
    }

}