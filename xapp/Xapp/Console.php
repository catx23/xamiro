<?php

defined('XAPP') || require_once(dirname(__FILE__) . '/../Core/core.php');

xapp_import('xapp.Xapp.Error');

/**
 * Console class
 *
 * @package Xapp
 * @class Xapp_Cli
 * @error 109
 * @author Frank Mueller <set@cooki.me>
 */
class Xapp_Console implements Xapp_Singleton_Interface
{
    /**
     * property that contains instance of third party console class instance
     * like "firephp" or "chromephp" since this class acts only as a wrapper
     * for these console implementations
     *
     * @var ChromePhp|FirePHP|null
     */
    public $console = null;

    /**
     * contains the driver string on which this class has been instantiated
     * e.g. "firephp" to load the firephp external class
     *
     * @var null|string
     */
    protected $_driver = null;

    /**
     * contains the static singleton instance of this class
     *
     * @var null|Xapp_Console
     */
    protected static $_instance = null;

    /**
     * map of valid actions or log types
     *
     * @var array
     */
    protected static $_typeMap = array
    (
        'log'       => 'log',
        'warn'      => 'warn',
        'info'      => 'info',
        'error'     => 'error',
        'dump'      => 'dump',
        'trace'     => 'trace',
        'group'     => 'group',
        'ungroup'   => 'ungroup',
        'ini'       => 'ini'
    );


    /**
     * class constructor will include the external console class, initialize it and store
     * its instance as $console property. the class can not be instantiated other
     * then using singleton method create. the loaded console class will be init and map
     * with its log types to this classed log types.
     *
     * @error 10901
     * @param string $driver expects the driver string to load
     * @param array $options expects an driver dependent option array
     * @throws Xapp_Error
     */
    protected function __construct($driver, Array $options = array())
    {
        $this->_driver = strtolower(trim($driver));
        switch($this->_driver)
        {
            case 'firephp':
                xapp_import('firephp.firephp-core.*');
                if(sizeof(ob_list_handlers()) === 0)
                {
                    ob_start();
                }
                $this->console = FirePHP::init();
                $this->console->setOptions($options);
                self::$_typeMap = array_merge(self::$_typeMap, array
                (
                    'log'       => FirePHP::LOG,
                    'warn'      => FirePHP::WARN,
                    'info'      => FirePHP::INFO,
                    'error'     => FirePHP::ERROR,
                    'dump'      => FirePHP::DUMP,
                    'trace'     => FirePHP::TRACE,
                    'group'     => FirePHP::GROUP_START,
                    'ungroup'   => FirePHP::GROUP_END,
                ));
                break;
            case 'chromephp':
                xapp_import('xapp.Ext.ChromePhp');
                $this->console = ChromePhp::getInstance();
                if(!isset($options[ChromePhp::BACKTRACE_LEVEL]))
                {
                    $options[ChromePhp::BACKTRACE_LEVEL] = 2;
                }
                $this->console->addSettings($options);
                self::$_typeMap = array_merge(self::$_typeMap, array
                (
                    'log'       => ChromePhp::LOG,
                    'warn'      => ChromePhp::WARN,
                    'info'      => ChromePhp::INFO,
                    'error'     => ChromePhp::ERROR,
                    'dump'      => ChromePhp::INFO,
                    'trace'     => ChromePhp::INFO,
                    'group'     => ChromePhp::GROUP,
                    'ungroup'   => ChromePhp::GROUP_END
                ));
                break;
            default:
                throw new Xapp_Error(xapp_sprintf(_("xapp console driver: %s not supported"), $driver), 1090101);
        }
    }


    /**
     * creates singleton instance and passes all options to class constructor
     *
     * @see Xapp_Console::__construct
     * @error 10902
     * @param null|string $driver expects the driver string to load
     * @param array $options expects an driver dependent option array
     * @return null|Xapp_Console
     */
    public static function instance($driver = null, Array $options = array())
    {
        if(self::$_instance === null)
        {
            self::$_instance = new self($driver, $options);
        }
        return self::$_instance;
    }


    /**
     * shortcut function to log a warning to console
     *
     * @error 10903
     * @param null|mixed $message expects the message of any type to send to console
     * @param null|string $label expects the optional label to describe the first parameter
     * @param array $options expects optional parameters
     * @return void
     */
    public function warn($message = null, $label = null, Array $options = array())
    {
        $this->log($message, $label, 'warn', $options);
    }


    /**
     * shortcut function to log a info to console
     *
     * @error 10904
     * @param null|mixed $message expects the message of any type to send to console
     * @param null|string $label expects the optional label to describe the first parameter
     * @param array $options expects optional parameters
     * @return void
     */
    public function info($message = null, $label = null, Array $options = array())
    {
        $this->log($message, $label, 'info', $options);
    }


    /**
     * shortcut function to log a error to console
     *
     * @error 10905
     * @param null|mixed $message expects the message of any type to send to console
     * @param null|string $label expects the optional label to describe the first parameter
     * @param array $options expects optional parameters
     * @return void
     */
    public function error($message = null, $label = null, Array $options = array())
    {
        $this->log($message, $label, 'error', $options);
    }


    /**
     * shortcut function to dump an object to console
     *
     * @error 10906
     * @param null|mixed $message expects the message of any type to send to console
     * @param null|string $label expects the optional label to describe the first parameter
     * @param array $options expects optional parameters
     * @return void
     */
    public function dump($message = null, $label = null, Array $options = array())
    {
        $this->log($message, $label, 'dump', $options);
    }


    /**
     * shortcut function to dump all current ini values to console with optional option
     * to only send values for a certain extension in first parameter $message according
     * to phps ini_get_all sections see php help page.
     *
     * @error 10907
     * @param null|mixed $message expects the message of any type to send to console
     * @param null|string $label expects the optional label to describe the first parameter
     * @param array $options expects optional parameters
     * @return void
     */
    public function ini($message = null, $label = null, Array $options = array())
    {
        $tmp = array();

        if($message !== null)
        {
            $ini = @ini_get_all(strtolower((string)$message));
        }else{
            $ini = ini_get_all();
        }
        if($ini)
        {
            foreach($ini as $k => $v)
            {
                if($v['local_value'] != $v['global_value'])
                {
                    $tmp[$k] = $v['local_value'];
                }else{
                    $tmp[$k] = $v['global_value'];
                }
            }
            $this->log($tmp, 'php ini values:', 'dump');
        }
    }


    /**
     * log to console directly with this method passing only the first required parameter and to change
     * the log type the third parameter according to allowed log types. pass a lable for second parameter
     * to describe the message send to console.
     *
     * @error 10908
     * @param null|mixed $mixed expects the message of any type to send to console
     * @param null|string $label expects the optional label to describe the first parameter
     * @param string $type expects the log type - see log type array
     * @param array $options expects optional parameters
     * @return void
     * @throws Xapp_Error
     */
    public function log($mixed = null, $label = null, $type = 'info', Array $options = array())
    {
        $type = strtolower((string)$type);
        if(array_key_exists($type, self::$_typeMap))
        {
            if($type === 'ini')
            {
                $this->ini($mixed, $label, $options);
            }
            if($label !== null)
            {
                $label = trim(trim($label), ':') . ':';
            }
            switch($this->_driver)
            {
                case 'chromephp':
                    switch($type)
                    {
                        case ($type === 'ungroup' || $mixed === null):
                            $this->console->groupEnd();
                            break;
                        case 'group':
                            $this->console->group($mixed);
                            break;
                        case 'trace':
                            $this->console->log((string)$label, $mixed, 'info');
                            break;
                        default:
                            $this->console->log((string)$label, $mixed, self::$_typeMap[$type]);
                    }
                    break;
                case 'firephp':
                    switch($type)
                    {
                        case ($type === 'ungroup' || $mixed === null):
                            $this->console->groupEnd();
                            break;
                        case 'group':
                            $this->console->group($mixed, $options);
                            break;
                        case 'trace':
                            $this->console->trace($label);
                            break;
                        default:
                            $this->console->$type($mixed, (string)$label, $options);
                    }
                    break;
            }
        }else{
            throw new Xapp_Error(xapp_sprintf(_("xapp console log type: %s not supported"), $type), 1090801);
        }
    }
}