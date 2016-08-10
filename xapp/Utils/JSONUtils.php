<?php
/**
 * @version 0.1.0
 * @package JSON
 *
 * @author https://github.com/mc007
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */

/**
 * Reads a JSON file.
 * Expects an absolute file/dir pointer and an optional input
 * array or object to initialize function. if a directory is passed will auto create
 * global storage file with the name ".storage"
 *
 * @error 16401
 * @param string $storage expects the absolute path to storage file or directory
 * @param string $type expects storage data type which defaults to php
 * @param array|object|null $input expects optional storage data
 * @throws Xapp_Util_Exception_Storage
 */
xapp_import('xapp.Util.Json.Json');
xapp_import('xapp.Util.Exception.Storage');

class XApp_Utils_JSONUtils{

    /**
     * contains the absolute path to storage file or dir
     *
     * @var null|string
     */
    private  static $_storage = null;

    /**
     * contains the storage type which can by json string or serialized array/object
     *
     * @var string
     */
    protected static $_storageType = '';

    /**
     * contains all allowed storage types
     *
     * @var array
     */
    protected static $_storageTypes = array('json', 'php');

	/**
	 * @param $string
	 * @param $key
	 * @return string
	 */
	protected static function encrypt($string,$key)
	{
		$encrypted = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($key), $string, MCRYPT_MODE_CBC, md5(md5($key))));

		return $encrypted;
	}

	/**
	 * Returns an encrypted & utf8-encoded
	 */
	/*
	public static function encrypt($pure_string, $encryption_key) {
		$encryption_key=md5($encryption_key);
		$iv_size = mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_ECB);
		$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
		$encrypted_string = mcrypt_encrypt(MCRYPT_BLOWFISH, $encryption_key, utf8_encode($pure_string), MCRYPT_MODE_ECB, $iv);
		return md5($encrypted_string);
	}
	*/

	/**
	 * Returns decrypted original string
	 */
	/*
	public  static function decrypt($encrypted_string, $encryption_key) {
		$encryption_key=md5($encryption_key);
		$iv_size = mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_ECB);
		$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
		$decrypted_string = mcrypt_decrypt(MCRYPT_BLOWFISH, $encryption_key, $encrypted_string, MCRYPT_MODE_ECB, $iv);
		return $decrypted_string;
	}*
	*/

	/**
	 *
	 * decrypt
	 */

	public static function decrypt($string,$key)
	{
		// clean url safe
		$decrypted = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($key), base64_decode($string), MCRYPT_MODE_CBC, md5(md5($key))), "\0");

		return $decrypted;
	}

    /***
     * @param $storage
     * @param string $type
     * @param bool $writable
     * @param bool $decode
     * @param array $input
     * @param bool $assoc
     * @return mixed|null|string
     * @throws Xapp_Util_Exception_Storage
     */
    public static  function  read_json($storage, $type = 'php',$writable=false,$decode=true,$input = array(),$assoc=true,$pass=null){

            $storage = preg_replace("~\\\\+([\"\'\\x00\\\\])~", "$1", $storage);
	        $storage = realpath($storage);
            self::$_storage = '' . $storage;
            self::$_storageType = strtolower(trim((string)$type));
            if(in_array(self::$_storageType, self::$_storageTypes))
            {
                if(is_dir(self::$_storage))
                {
                    if(is_writeable(self::$_storage) && $writable===true)
                    {
                        self::$_storage = rtrim(self::$_storage, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '.storage';
                    }else{
                        throw new Xapp_Util_Exception_Storage(vsprintf('storage directory: %s is not writable', array(self::$_storage)), 1640101);
                    }
                }else{

                    if(file_exists(self::$_storage))
                    {
                        if(($container = file_get_contents(self::$_storage)) !== false)
                        {
                            if(!empty($container))
                            {

                                switch(self::$_storageType)
                                {
                                    case 'json':
                                    {
	                                    if(isset($pass) && strlen($pass) && strpos($container,'~~~~~')!==false){
											$container = str_replace('~~~~~','',$container);
											$container = self::decrypt($container,$pass);
										}

	                                    $container = str_replace('<?php','',$container);//JSON data might be in a PHP file.


                                        if($decode==false){
                                            $container = str_replace(array("\n", "\r","\t"), array('', '',''), $container);
                                            $container = preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', '', $container);
                                            $container = preg_replace('/\r\n?/', "", $container);
                                            $container = str_replace('\/','/',$container);
                                            $container= preg_replace('/[\x00-\x1F\x7F]/', '', $container);
                                            return $container;
                                        }
                                        $container = Xapp_Util_Json::decode($container, $assoc);
                                        return $container;
                                    }
                                    case 'php':
                                        $container = @unserialize($container);
                                        return $container;
                                }
                                if($container === null || $container === false)
                                {
                                    throw new Xapp_Util_Exception_Storage('unable to decode stored data', 1640103);
                                }
                            }
                            if(!empty($container))
                            {
                                //$this->data = $container;
                            }else{

                            }
                        }else{
                            throw new Xapp_Util_Exception_Storage('unable to read storage from file', 1640104);
                        }
                    }else{
                        if($writable===true && !is_writeable(dirname(self::$_storage)))
                        {
                            throw new Xapp_Util_Exception_Storage(vsprintf('storage directory: %s is not writable', array(self::$_storage)), 1640101);
                        }
                        //throw new Xapp_Util_Exception_Storage('unable to read storage from file: ' . $storage , 1640104);
                    }
                }
            }else{
                throw new Xapp_Util_Exception_Storage("storage type: $type is not supported", 1640105);
            }
            return null;
    }


    /**
     * @param $storage
     * @param $data
     * @param string $type
     * @param bool|false $pretty
     * @param null $pass
     * @param bool|true $noPHP
     * @return null
     * @throws Xapp_Util_Exception_Storage
     * @throws Xapp_Util_Json_Exception
     */
    public static function write_json($storage,$data,$type = 'json',$pretty=false,$pass=null,$noPHP=true){

        $return = null;
        switch($type)
        {
            case 'json':{
                $_dataStr = is_string($data) ? $data : Xapp_Util_Json::encode($data);

	            if(strpos($storage,'.php')!=-1 && $noPHP!==true){
					$_dataStr = '<?php ' . PHP_EOL . $_dataStr;
				}

	            if(isset($pass) && strlen($pass)){
		            $_dataStr = '~~~~~' . self::encrypt($_dataStr,$pass);
	            }

                if($pretty===true){

                    $return = file_put_contents($storage,  Xapp_Util_Json::prettify($_dataStr), LOCK_EX);
                }else{
                    $return = file_put_contents($storage, $_dataStr , LOCK_EX);
                }
                break;
            }
            case 'serialized':
                $return = file_put_contents($storage, serialize($data), LOCK_EX);
                break;
            default:
                //nothing
        }
        if($return === false)
        {
            throw new Xapp_Util_Exception_Storage('unable to save to storage', 1640401);
        }
        return null;
    }
}
if(!function_exists('xapp_object_find')){
/***
 * Static wrapper for Xapp_Util_Json_Query to query an object|array
 * @param $object
 * @param $path
 * @param $query
 *
 * @param bool $toArray
 * @return $this|array|bool
 */
function xapp_object_find($object,$path,$query,$toArray=true){

        error_reporting(E_ERROR);
        ini_set('display_errors', '0');     # don't show any errors...

        $queryObject = null;
        if(is_string($object)){
            $queryObject = $object;
        }else{
            $queryObject = json_encode($object);
        }
        $q = new Xapp_Util_Json_Query($queryObject);
        $res = $q->query($path, $query);
        ini_set('display_errors', '1');     # revert
        error_reporting(E_ALL);
        return $res->get();
    }
}