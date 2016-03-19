<?php
/**
 * @version 0.1.0
 *
 * @author https://github.com/mc007
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */

/**
 * The class manages XApp connect plugins
 *
 *  Remarks :
 *
 * @package XApp-Commander\Manager
 * @class XApp_PluginManager
 * @error 153
 * @author mc007
 */
class XApp_Commander_PluginManager implements Xapp_Singleton_Interface {


    /**
     * contains the absolute path to storage file or dir
     *
     * @var null|string
     */
    protected $_storage = null;

    /**
     * contains the storage type which can by json string or serialized array/object
     *
     * @var string
     */
    protected $_storageType = '';

    /**
     * contains all allowed storage types
     *
     * @var array
     */
    protected $_storageTypes = array('json', 'php');

    var $logger=null;

    /***
     * service configuration shortcut.
     */
    var $serviceConfig=null;

    /***
     * internal cache instance
     */
    var $cache=null;

    /**
     * option to specify a cache config
     *
     * @const CACHE_CONF
     */
    const CACHE_CONF         = 'XAPP_CACHE_CONF';

    /**
     * option to specify logging config
     *
     * @const LOGGING_CONF
     */
    const LOGGING_CONF         = 'XAPP_LOGGING_CONF';

    /**
     * option to specify service config
     *
     * @const SERVICE_CONF
     */
    const SERVICE_CONF         = 'XAPP_SERVICE_CONF';



    /**
     * options dictionary for this class containing all data type values
     *
     * @var array
     */
    public static $optionsDict = array
    (
        self::CACHE_CONF       => XAPP_TYPE_ARRAY,
        self::LOGGING_CONF     => XAPP_TYPE_ARRAY,
        self::SERVICE_CONF     => XAPP_TYPE_ARRAY
    );

    /**
     * options mandatory map for this class contains all mandatory values
     *
     * @var array
     */
    public static $optionsRule = array
    (
        self::CACHE_CONF         => 0,
        self::LOGGING_CONF       => 0,
        self::SERVICE_CONF       => 0
    );

    /**
     * options default value array containing all class option default values
     *
     * @var array
     */
    public $options = array
    (
        self::CACHE_CONF            => null,
        self::LOGGING_CONF          => null,
        self::SERVICE_CONF          => null

    );

    /**
     * contains the singleton instance for this class
     *
     * @var null|Xapp_Commander_Plugin
     */
    protected static $_instance = null;

    /**
     * Xapp_Singleton interface impl.
     *
     * static singleton method to create static instance of driver with optional third parameter
     * xapp options array or object
     *
     * @error 15501
     * @param null|mixed $options expects optional xapp option array or object
     * @return Xapp_Commander_Plugin
     */
    public static function instance($options = null)
    {
        if(self::$_instance === null)
        {
            self::$_instance = new self($options);
            self::$_instance->setup();
        }
        return self::$_instance;
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
     * Finds a plugin configuration by Class name
     * @param $searchPath
     * @param $name
     * @param string $type
     * @return array|null
     */
    public function hasPluginConfiguration($searchPath,$name,$type=''){

        $result = null;
        //scan for plugin configs
        $foundPlugins = $this->getEnabledPluginsConfigs($searchPath);

        if($foundPlugins!=null && count($foundPlugins)>0){

            $res = (array)xapp_object_find($foundPlugins,'.',array('type='.$type,'name='.$name));
            if($res && count($res)>0){
                return $res[0];
            }
        }
        return $result;
    }
    /***
     * Finds a plugin configuration by Class name
     * @param $searchPath
     * @param $name
     * @param string $type
     * @return array|null
     */
    public function loadPluginWithConfiguration($pluginPrefix,$pluginConfig){

        if($pluginConfig==null){
            return false;
        }
        $pluginPath = true;
        if(!is_array($pluginConfig)){
            //skip if class already exists
            if(property_exists($pluginConfig,'name') && class_exists($pluginConfig->name)){

                return true;
            }
            //determine load path
            $pluginPath = $pluginPrefix . DIRECTORY_SEPARATOR . $pluginConfig->location . DIRECTORY_SEPARATOR . $pluginConfig->name . '.php';
        }else{
            if(class_exists($pluginConfig['name'])){
                return true;
            }
            $pluginPath = $pluginPrefix . DIRECTORY_SEPARATOR . $pluginConfig['location'] . DIRECTORY_SEPARATOR . $pluginConfig['name'] . '.php';
        }
        if(file_exists($pluginPath)){
            include $pluginPath;
        }

        return true;
    }

    /***
     * @param $searchPath
     * @param string $pluginPrefix
     * @param string $type
     * @return array
     *
     */
    public function getPluginResources($searchPath,$pluginPrefix='',$type='XCOM',$runTimeConfiguration='release'){

        $result = array();
        //scan for plugin configs
        $foundPlugins = $this->getEnabledPluginsConfigs($searchPath);
	    if($foundPlugins!=null && count($foundPlugins)>0){

            $res = (array)xapp_object_find($foundPlugins,'.',array('type='.$type));
            if($res && count($res)>0){

                //now iterate over plugin configs
                foreach($res as $pluginConfig)
                {
                    //skip if class already exists
                    if(class_exists($pluginConfig->name)){
                        array_push($result,$pluginConfig);
                        continue;
                    }
                    //determine load path
                    array_push($result,$pluginConfig);
                }
            }else{
                error_log('have no plugins at ' . $searchPath );
            }
        }
	    return $this->filterPluginResources($result,$runTimeConfiguration);
    }

    /***
     * @param $searchPath
     * @param string $pluginPrefix
     * @param string $type
     * @return array
     *
     */
    public function getPlugins($searchPath,$type='XCOM'){

        $result = null;

        //scan for plugin configs
        $foundPlugins = $this->getEnabledPluginsConfigs($searchPath);


        if($foundPlugins!=null && count($foundPlugins)>0){

            $res = (array)xapp_object_find($foundPlugins,'.',array('type='.$type));
            if($res && count($res)>0){
                return $res;

            }else{
                //error_log('have no plugins at ' . $searchPath );
            }
        }else{

        }
        return $result;
    }
    /***
     * @param $searchPath
     * @param string $pluginPrefix
     * @param string $type
     * @return array
     *
     */
    public function loadPlugins($searchPath,$pluginPrefix='',$type='XCOM'){

        $result = array();
        //scan for plugin configs
        $foundPlugins = $this->getEnabledPluginsConfigs($searchPath);

        if($foundPlugins!=null && count($foundPlugins)>0){

            $res = (array)xapp_object_find($foundPlugins,'.',array('type='.$type));
            if($res && count($res)>0){

                //now iterate over plugin configs
                foreach($res as $pluginConfig)
                {
                    //skip if class already exists
                    if(class_exists($pluginConfig->name)){
                        array_push($result,$pluginConfig);
                        continue;
                    }
                    //determine load path
                    $pluginPath = $pluginPrefix . DIRECTORY_SEPARATOR . $pluginConfig->location . DIRECTORY_SEPARATOR . $pluginConfig->name . '.php';
                    if(file_exists($pluginPath)){
                        include $pluginPath;
                        array_push($result,$pluginConfig);
                    }else{
                        //error_log($pluginPath. ' : doesnt exists');
                    }
                }
            }else{
                //error_log('have no plugins at ' . $searchPath );
            }
        }else{
            //error_log('couldnt find any plugins!');
        }
        return $result;
    }
    /***
     * @param $plugins
     * @param $runtTimeConfiguration
     * @return array
     */
    public function filterPluginResources($plugins,$runtTimeConfiguration){
        $result = array();
        foreach($plugins as $plugin){
            if($plugin->resources && $plugin->resources->{$runtTimeConfiguration}){
                $result[]=$plugin->resources->{$runtTimeConfiguration}->items;
            }
        }
        return $result;
    }
    /***
     * @param $searchPath
     * @param string $pluginPrefix
     * @param string $type
     * @return array
     *
     */
    public function getPluginInfos($searchPath,$type){

        $result = array();
        //scan for plugin configs
        $foundPlugins = $this->getEnabledPluginsConfigs($searchPath);
        if($foundPlugins!=null && count($foundPlugins)>0){

            $res = (array)xapp_object_find($foundPlugins,'.',array('type='.$type));
            if($res && count($res)>0){

                //now iterate over plugin configs
                foreach($res as $pluginConfig)
                {
                    array_push($result,$pluginConfig);
                }
            }
        }
        return $result;
    }

    /***
     * Method to load all plugin configs
     * @param $searchPath
     * @param string $type
     * @return array
     */
    public function getEnabledPluginsConfigs($searchPath){
        $_pathToSearch = '' . $searchPath;
        if(!file_exists($searchPath)){
            return array();
        }
        $root = scandir($_pathToSearch);
        $result = array();
        foreach($root as $value)
        {
            //skip non files
            if($value === '.' || $value === '..') {
                continue;
            }
            if(is_file($_pathToSearch . DIRECTORY_SEPARATOR . $value) && strpos($value,'json')!==false)
            {
                $confStore=$this->readJSON($_pathToSearch . DIRECTORY_SEPARATOR . $value,'json');
                if($confStore){
                    array_push($result,$confStore);
                }
            }
        }
        return $result;
    }
    /**
     * contains all singleton instances defined by a names space string identifier create
     * by instance method
     *
     * @var array
     */
    protected static $_instances = array();


    public function getPluginInstances(){
        return self::$_instances;
    }


    /***
     * Used to tell all plugins that we're going to search. That will
     * usually init their own Lucene indexer.
     */
    public function onSearchBegin(){

        $plgInstances = self::$_instances;

        foreach($plgInstances as $plg){
            if(method_exists($plg,'onBeforeSearch')){
                $plg->onBeforeSearch();
            }else{
            }
        }
    }

    var $_plugins=null;


    public function dumpObject($obj,$prefix=''){
        $d = print_r($obj,true);
        error_log(' dump : ' .$prefix . ' : ' . $d);
        return $d;
    }

    /***
     * @param $prefix
     * @param $name
     */
    public function loadPlugin($prefix,$name){

        if(file_exists($prefix.$name.".php")){
            include($prefix.$name.".php");
        }

        if(class_exists($name)){

        }else{
            error_log('couldn`t include class : ' .$name);
        }
    }

    /***
     * @param $name
     * @param bool $loadPlugin
     * @param null $serviceConf
     * @param null $loggingConf
     * @return XApp_FakePlugin
     */
    public function createFakePluginInstance($name,
                                         $loadPlugin=true,
                                         $serviceConf=null,
                                         $loggingConf=null){

        if(array_key_exists($name, self::$_instances))
        {
            return self::$_instances[trim((string)$name)];
        }

        $plgServiceConf =  $serviceConf !=null ? $serviceConf : xapp_get_option(self::SERVICE_CONF,$this);
        $plgLoggingConf =  $loggingConf !=null ? $loggingConf : xapp_get_option(self::LOGGING_CONF,$this);

        //build plugin options
        $pluginOptions=array(

            //cache configuration
            Xapp_Commander_Plugin::CACHE_CONF=>array(
                Xapp_Cache_Driver_File::PATH=>xapp_get_option(XC_CONF_CACHE_PATH,$plgServiceConf),
                Xapp_Cache_Driver_File::CACHE_EXTENSION=>$name,
                Xapp_Cache_Driver_File::DEFAULT_EXPIRATION=>1000),

            //logging configuration
            Xapp_Commander_Plugin::LOGGING_CONF=>$plgLoggingConf,
            //service configuration
            Xapp_Commander_Plugin::SERVICE_CONF=>$plgServiceConf
        );

        //create the instance
        $plugin  = new XApp_FakePlugin($pluginOptions);
        $plugin->CACHE_NS = $name;


        //fire standard interface
        $plugin->setup();

        if($loadPlugin){
            $plugin->load();
        }
        //track instance
        self::$_instances[trim((string)$name)]=$plugin;


        return $plugin;

    }
    /***
     * @param $prefix
     * @param $name
     */
    public function createPluginInstance($name,
                                         $loadPlugin=true,
                                         $serviceConf=null,
                                         $loggingConf=null,
                                         $bootstrapConf=null,
                                         $pluginDescription=null){

        if(!class_exists($name)){
            return null;
        }

        if(array_key_exists($name, self::$_instances))
        {
            return self::$_instances[trim((string)$name)];
        }

        $plgServiceConf =  $serviceConf !=null ? $serviceConf : xapp_get_option(self::SERVICE_CONF,$this);
        $plgLoggingConf =  $loggingConf !=null ? $loggingConf : xapp_get_option(self::LOGGING_CONF,$this);

        //build plugin options
        if(class_exists('Xapp_Cache_Driver_File')){
            $pluginOptions=array(

                //cache configuration
                Xapp_Commander_Plugin::CACHE_CONF=>array(
                    Xapp_Cache_Driver_File::PATH=>xapp_get_option(XC_CONF_CACHE_PATH,$plgServiceConf),
                    Xapp_Cache_Driver_File::CACHE_EXTENSION=>$name,
                    Xapp_Cache_Driver_File::DEFAULT_EXPIRATION=>1000),

                //logging configuration
                Xapp_Commander_Plugin::LOGGING_CONF=>$plgLoggingConf,
                //service configuration
                Xapp_Commander_Plugin::SERVICE_CONF=>$plgServiceConf
            );
        }else{
            $pluginOptions=array(
                //logging configuration
                Xapp_Commander_Plugin::LOGGING_CONF=>$plgLoggingConf !==null ? $plgLoggingConf : array() ,
                //service configuration
                Xapp_Commander_Plugin::SERVICE_CONF=>$plgServiceConf !==null ? $plgServiceConf : array(),
                //bootstrap configuration
                Xapp_Commander_Plugin::BOOTSTRAP_CONF=>$bootstrapConf!==null ? $bootstrapConf : array()
            );
        }

        //create the instance
        $plugin  = new $name($pluginOptions);

        //fire standard interface
        $plgErr = $plugin->setup();

        if($loadPlugin){
            $plgErr = $plugin->load();
        }

        //track instance
        self::$_instances[trim((string)$name)]=$plugin;


        return $plugin;

    }
    /***
     * Standard setup for rich XApp classes
     */
    private function setup(){

        $this->serviceConfig = xapp_get_option(self::SERVICE_CONF,$this);

        //cache
        if(xapp_is_option(self::CACHE_CONF, $this) && $this->serviceConfig){
            $cacheConfig = xapp_get_option(self::CACHE_CONF);
            if($cacheConfig){
                $this->cache = Xapp_Cache::instance("PluginManager","file",array(
                    Xapp_Cache_Driver_File::PATH=>xapp_get_option(XC_CONF_CACHE_PATH,$this->serviceConfig),
                    Xapp_Cache_Driver_File::CACHE_EXTENSION=>"pmmanager",
                    Xapp_Cache_Driver_File::DEFAULT_EXPIRATION=>200
                ));
            }
        }
    }
    /**
     * class constructor expects an absolute file/dir pointer and an optional input
     * array or object to initialize function. if a directory is passed will auto create
     * global storage file with the name ".storage"
     *
     * @error 16401
     * @param string $storage expects the absolute path to storage file or directory
     * @param string $type expects storage data type which defaults to php
     * @param array|object|null $input expects optional storage data
     * @throws Xapp_Util_Exception_Storage
     */
    private function readJSON($storage, $type = 'php', $input = array())
    {
        $storage = preg_replace("~\\\\+([\"\'\\x00\\\\])~", "$1", $storage);
        $this->_storage = $storage;
        $this->_storageType = strtolower(trim((string)$type));
        if(in_array($this->_storageType, $this->_storageTypes))
        {
            if(is_dir($this->_storage))
            {
                if(is_writeable($this->_storage))
                {
                    $this->_storage = rtrim($this->_storage, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '.storage';
                }else{
                    /*throw new Xapp_Util_Exception_Storage(vsprintf('storage directory: %s is not writable', array($this->_storage)), 1640101);*/
                }
            }else{
                if(file_exists($this->_storage))
                {
                    if(($container = file_get_contents($this->_storage)) !== false)
                    {
                        if(!empty($container))
                        {
                            switch($this->_storageType)
                            {
                                case 'json':{

                                    $container = Xapp_Util_Json::decode($container, true);
                                    return $container;
                                }
                                case 'php':
                                    $container = @unserialize($container);
                                    break;
                                default:
                                    //nothing
                            }
                            if($container === null || $container === false)
                            {
                                throw new Xapp_Util_Exception_Storage('PluginManager : unable to decode stored data', 1640103);
                            }
                        }
                        if(!empty($container))
                        {
                            $this->data = $container;
                        }else{

                        }
                    }else{
                        throw new Xapp_Util_Exception_Storage('PluginManaager unable to read storage from file', 1640104);
                    }
                }else{
                    /*throw new Xapp_Util_Exception_Storage('unable to read storage from file', 1640104);*/
                }
            }
        }else{
            throw new Xapp_Util_Exception_Storage("storage type: $type is not supported", 1640105);
        }
        return null;
    }

}