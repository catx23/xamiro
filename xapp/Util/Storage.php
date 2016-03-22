<?php
/**
 * @version 0.1.0
 *
 * @author https://github.com/mc007
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */

defined('XAPP') || require_once(dirname(__FILE__) . '/../Core/core.php');

xapp_import('xapp.Util.Container');
xapp_import('xapp.Util.Exception.Storage');

/**
 * Util storage class
 *
 * @package Util
 * @class Xapp_Util_Storage
 * @error 164
 * @author Frank Mueller
 */
class Xapp_Util_Storage extends Xapp_Util_Container
{
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
    public function __construct($storage, $type = 'php', $input = array())
    {
        $this->_storage = DIRECTORY_SEPARATOR . ltrim($storage, DIRECTORY_SEPARATOR);
        $this->_storageType = strtolower(trim((string)$type));

        if(in_array($this->_storageType, $this->_storageTypes))
        {
            if(is_dir($this->_storage))
            {
                if(is_writeable($this->_storage))
                {
                    $this->_storage = rtrim($this->_storage, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '.storage';
                    parent::__construct($input);
                }else{
                    throw new Xapp_Util_Exception_Storage(xapp_sprintf(_("storage directory: %s is not writable"), $this->_storage), 1640101);
                }
            }else{
                if(file_exists($this->_storage))
                {
                    if(!is_writeable($this->_storage))
                    {
                        throw new Xapp_Util_Exception_Storage(xapp_sprintf(_("storage file: %s is not writable"), $this->_storage), 1640102);
                    }
                    if(($container = file_get_contents($this->_storage)) !== false)
                    {
                        if(!empty($container))
                        {
                            switch($this->_storageType)
                            {
                                case 'json':
                                    $container = Xapp_Util_Json::decode($container, true);
                                    break;
                                case 'php':
                                    $container = @unserialize($container);
                                    break;
                                default:
                                    //nothing
                            }
                            if($container === null || $container === false)
                            {
                                throw new Xapp_Util_Exception_Storage(_("unable to decode stored data"), 1640103);
                            }
                        }
                        if(!empty($container))
                        {
                            parent::__construct(array_merge((array)$container, (array)$input));
                        }else{
                            parent::__construct($input);
                        }
                    }else{
                        throw new Xapp_Util_Exception_Storage(_("unable to read storage from file"), 1640104);
                    }
                }else{
                    if(!is_writeable(dirname($this->_storage)))
                    {
                        throw new Xapp_Util_Exception_Storage(xapp_sprintf(_("storage directory: %s is not writable"), $this->_storage), 1640101);
                    }
                    parent::__construct($input);
                }
            }
        }else{
            throw new Xapp_Util_Exception_Storage(xapp_sprintf(_("storage type: %s is not supported"), $type), 1640105);
        }
    }


    /**
     * add key value pair to storage or use set function
     *
     * @error 16402
     * @param null|mixed $index expects optional index key
     * @param null|mixed $value expects the value to set
     * @return void
     */
    public function add($index, $value = null)
    {
        $this->set($index, $value);
    }


    /**
     * remove a value from storage by its index/key
     *
     * @error 16403
     * @param null|mixed $index expects optional index key
     * @return void
     */
    public function remove($index)
    {
        $this->offsetUnset($index);
    }


    /**
     * save the data to the storage
     *
     * @error 16404
     * @throws Xapp_Util_Exception_Storage
     * @return void
     */
    public function save()
    {
        $return = null;

        switch($this->_storageType)
        {
            case 'json':
                $return = file_put_contents($this->_storage, Xapp_Util_Json::prettify(Xapp_Util_Json::encode($this->getArrayCopy())), LOCK_EX);
                break;
            case 'serialized':
                $return = file_put_contents($this->_storage, serialize($this->getArrayCopy()), LOCK_EX);
                break;
            default:
                //nothing
        }
        if($return === false)
        {
            throw new Xapp_Util_Exception_Storage(_("unable to save to storage"), 1640401);
        }
    }


    /**
     * flush the storage in memory and file
     *
     * @error 16405
     * @throws Xapp_Util_Exception_Storage
     * @return void
     */
    public function flush()
    {
        $this->exchangeArray(array());
        if(!file_put_contents($this->_storage, "", LOCK_EX))
        {
            throw new Xapp_Util_Exception_Storage(_("unable to flush storage"), 1640501);
        }
    }


    /**
     * delete the storage file
     *
     * @error 16406
     * @throws Xapp_Util_Exception_Storage
     * @return void
     */
    public function delete()
    {
        if(!unlink($this->_storage))
        {
            throw new Xapp_Util_Exception_Storage(_("unable to delete storage file"), 1640601);
        }
    }


    /**
     * class destructor clears cache
     *
     * @error 16407
     * @return void
     */
    public function __destruct()
    {
        @clearstatcache();
    }
}