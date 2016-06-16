<?php
/**
 * @version 0.1.0
 *
 * @author https://github.com/mc007
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */

xapp_import('xapp.Store.StoreBase');
xapp_import('xapp.Store.Interface.Interface');

class XApp_Store extends XApp_Store_Base
{

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


    protected $_reader=null;
    protected $_writer=null;

    /***
     * @return null
     */
    public function getReader(){
        if($this->_reader){
            return $this->_reader;
        }
        $readerClass = xapp_get_option(self::READER_CLASS,$this);
        if(class_exists($readerClass)){
            $this->_reader = new $readerClass(xapp_get_options());
        }else{
            echo('reader class ' . $readerClass . ' doesnt exists');
        }
        return $this->_reader;

    }

    /***
     * @return null
     */
    public function getWriter(){
        if($this->_writer){
            return $this->_writer;
        }

        $writerClass = xapp_get_option(self::WRITER_CLASS,$this);
        if(class_exists($writerClass)){
            $this->_writer= new $writerClass(xapp_get_options());
        }
        return $this->_writer;

    }

    /***
     * @param query, a Json path query
     * @return string
     */
    public function get($section,$path,$query=null){
        $reader = $this->getReader();
        $data = null;
        if($reader){
            $data = $reader->get($section,$path,$query);
            return $data;
        }else{

        }
        return null;
    }
    public function set($section,$path='.',$query=null,$value=null,$decodeValue=true){
	    $writer = $this->getWriter();
        if($writer){
            return $writer->set($section,$path,$query,$value,$decodeValue);
        }
        return null;
    }

}