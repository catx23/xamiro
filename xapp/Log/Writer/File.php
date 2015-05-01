<?php

/**
 * @version 0.1.0
 *
 * @author https://github.com/mc007
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */

defined('XAPP') || require_once(dirname(__FILE__) . '/../../Core/core.php');

xapp_import('xapp.Log.Writer');
xapp_import('xapp.Log.Writer.Exception');

/**
 * Log Writer File class
 *
 * @package Log
 * @subpackage Log_Writer
 * @class Xapp_Log_Writer_File
 * @error 118
 * @author Frank Mueller
 */
class Xapp_Log_Writer_File extends Xapp_Log_Writer
{
    /**
     * contains the log file path
     *
     * @var null|string
     */
    protected $_file = null;

    /**
     * contains the log file name with extension
     *
     * @var null|string
     */
    protected $_dir = null;

    /**
     * contains the log file default file name strftime value
     *
     * @var null|string
     */
    protected $_format = null;

    /**
     * contains the log rotation flag
     *
     * @var null|string
     */
    protected $_rotation = null;

    /**
     * list of log rotation flags:
     * d = make year folder and day file
     * w = make year folder and week folder and day files
     * m = make year folder and month folder and day files
     *
     * @var array
     */
    protected $_rotationMap = array('d', 'w', 'm');


    /**
     * class constructor receives parameter and checks parameter for validity.
     * first parameter $file must be a writeble path or a absolute file pointer
     *
     * @error 11801
     * @param   string $file expects a writable directory or absolute file pointer
     * @param   string $rotation expects optional rotation value which can be any of the following:
     *          d = make year folder and day file
     *          w = make year folder and week folder and day files
     *          m = make year folder and month folder and day files
     *          boolean true = make default log rotation = d
     * @param   string $format expects default file name strftime value
     * @throws Xapp_Log_Writer_Exception
     */
    public function __construct($file, $rotation = '', $format = '%Y%m%d')
    {
        $this->_format = (string)$format;
        if(!is_dir($file))
        {
            $this->_file = basename($file);
            $this->_dir = rtrim(dirname($file), DS) . DS;
        }else{
            $this->_file = strftime($this->_format, time()) . '.log';
            $this->_dir = $file;
        }
        if(!is_dir($this->_dir) && !is_writable($this->_dir))
        {
            throw new Xapp_Log_Writer_Exception(_("log files dir does not exist or is not writable"), 1180101);
        }
        if(!is_dir($this->_dir) && !is_writable($this->_dir))
        {
            throw new Xapp_Log_Writer_Exception("log files dir does not exist or is not writable", 1180101);
        }
        if($rotation === true)
        {
            $this->_rotation = 'd';
        }else if($rotation === false){
            $this->_rotation = '';
        }else{
            $this->_rotation = strtolower(trim((string)$rotation));
        }
        if(!empty($this->_rotation))
        {
            if(in_array($this->_rotation , $this->_rotationMap))
            {
                $this->_file = strftime($this->_format, time()) . '.log';
            }else{
                throw new Xapp_Log_Writer_Exception(_("rotation value is not a valid rotation flag"), 1180102);
            }
        }
    }


    /**
     * write message to file by formatting incoming message object and determine
     * if log rotation is activated to create all required folders for rotations.
     * see explanation in class constructor for more.
     *
     * @see Xapp_Log_Writer::__construct
     * @error 11802
     * @param string|array|object $message expects the message object
     * @param null|mixed $params expects optional parameters
     * @return bool returns whether log write was successful or not
     */
    public function write($message, $params = null)
    {
        $message = $this->format($message);
        if(!empty($this->_rotation))
        {
            $dir = $this->_dir . date('Y', time());
            if(!is_dir($dir))
            {
                mkdir($dir, 02777);
                chmod($dir, 02777);
            }
            switch($this->_rotation)
            {
                case 'm':
                    $dir .=  DS . date('m', time());
                    if(!is_dir($dir))
                    {
                        mkdir($dir, 02777);
                        chmod($dir, 02777);
                    }
                    break;
                case 'w':
                    $dir .=  DS . date('W', time());
                    if(!is_dir($dir))
                    {
                        mkdir($dir, 02777);
                        chmod($dir, 02777);
                    }
                    break;
                default:
                    //do nothing
            }
            $file = rtrim($dir, DS) . DS . $this->_file;
        }else{
            $file = rtrim($this->_dir, DS) . DS . $this->_file;
        }
        if(is_file($file))
        {
            $return = file_put_contents($file, $message, FILE_APPEND);
        }else{
            $return = file_put_contents($file, $message);
            chmod($file, 0666);
        }
        @clearstatcache();
        return $return;
    }
}