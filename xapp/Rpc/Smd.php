<?php

defined('XAPP') || require_once(dirname(__FILE__) . '/../Core/core.php');

xapp_import('xapp.Rpc.Smd.Exception');
xapp_import('xapp.Rpc.Request');

/**
 * Rpc smd class
 *
 * @package Rpc
 * @class Xapp_Rpc_Smd
 * @error 141
 * @author Frank Mueller <set@cooki.me>
 */
abstract class Xapp_Rpc_Smd implements Xapp_Singleton_Interface
{
    /**
     * defines the version of smd schema
     *
     * @const VERSION
     */
    const VERSION                   = 'RPC_SMD_VERSION';

    /**
     * defines the description of smd schema
     *
     * @const DESCRIPTION
     */
    const DESCRIPTION               = 'RPC_SMD_DESCRIPTION';

    /**
     * defines the content type of the services response
     *
     * @const CONTENT_TYPE
     */
    const CONTENT_TYPE              = 'RPC_SMD_CONTENT_TYPE';

    /**
     * defines array of method or function prefixes to exclude from service mapping.
     * defaults to "__" excluding all magic methods from classes
     *
     * @const IGNORE_PREFIXES
     */
    const IGNORE_PREFIXES           = 'RPC_SMD_IGNORE_PREFIXES';

    /**
     * defines array of method names  to ignore when mapping classes
     *
     * @const IGNORE_METHODS
     */
    const IGNORE_METHODS            = 'RPC_SMD_IGNORE_METHODS';

    /**
     * defines whether to include parent class methods in rpc service when service
     * is extended from another class
     * 
     * @const IGNORE_PARENTS
     */
    const IGNORE_PARENTS            = 'RPC_IGNORE_PARENTS';

    /**
     * defines additional parameters which are not required in request
     * parameters for service but needed globally to validate valid request,
     * e.g. session id, signature parameter, version number, etc.
     *
     * @const ADDITIONAL_PARAMETERS
     */
    const ADDITIONAL_PARAMETERS     = 'RPC_SMD_ADDITIONAL_PARAMETERS';

    /**
     * define whether to use service over get where function/method and or class is passed in url
     * as GET parameter "service" or as url path when using htaccess rewriting. if this
     * option is set to false expects requesting methods of class in dot notation in
     * service parameter e.g. "class.method". when set to true rpc class will look
     * for class in "service" parameter in $_GET array. make sure the rewriting will
     * always translate a path like "http://foo.test/gateway/class" to "http://foo.test/gateway/?service=class.method"
     *
     * @const SERVICE_OVER_GET
     */
    const SERVICE_OVER_GET          = 'RPC_SMD_SERVICE_OVER_GET';

    /**
     * option to define smd target relative or absolute style
     *
     * @const RELATIVE_TARGETS
     */
    const RELATIVE_TARGETS          = 'RPC_SMD_RELATIVE_TARGETS';

    /**
     * option to define whether htaccess rewrite is used or not. this value can have a boolean
     * value if set to true will resolve smd targets to standard rewrite url which defaults to
     * url path + service, e.g. "/path/rpc/" to /path/rpc/{$class}/{$function} or define custom
     * url pattern with placeholder for class and function/class method. see getTarget function
     * for more explanation
     *
     * @see Xapp_Rpc_Smd::getTarget
     * @const REWRITE_URL
     */
    const REWRITE_URL               = 'RPC_SMD_REWRITE_URL';

    /*
     * option to define whether to include service description as found in function/method name
     * doc comment block in smd schema or not. Use this feature only in developer mode not on
     * productive server since function signatures may include sensitive text
     *
     * @const SERVICE_DESCRIPTION
     */
    const SERVICE_DESCRIPTION       = 'RPC_SMD_SERVICE_DESCRIPTION';

    /**
     * option to define the class/method separator to distinguish the method to be called in
     * class. the separator defaults to "." according to the rpc server separator. class methods
     * should always be called either as request parameter "method" like {$class}{$separtor}{$method}
     * or url get parameter service the same style. the smd separator will inherit the rpc server separator
     * if the smd instance is passed to rpc server class.
     *
     * @const CLASS_METHOD_SEPARATOR
     */
    const CLASS_METHOD_SEPARATOR    = 'RPC_SMD_CLASS_METHOD_SEPARATOR';


    /**
     * contains the complete mapped smd schema
     *
     * @var array
     */
    public $_map = array();

    /**
     * contains array of all registered class.methods and functions as array used
     * to check if a service exists
     *
     * @var array
     */
    protected $_services = array();

    /**
     * options dictionary for this class containing all data type values
     *
     * @var array
     */
    public static $optionsDict = array
    (
        self::IGNORE_PREFIXES           => XAPP_TYPE_ARRAY,
        self::IGNORE_METHODS            => XAPP_TYPE_ARRAY,
        self::IGNORE_PARENTS            => XAPP_TYPE_BOOL,
        self::CONTENT_TYPE              => XAPP_TYPE_STRING,
        self::VERSION                   => XAPP_TYPE_STRING,
        self::DESCRIPTION               => XAPP_TYPE_STRING,
        self::ADDITIONAL_PARAMETERS     => XAPP_TYPE_ARRAY,
        self::SERVICE_OVER_GET          => XAPP_TYPE_BOOL,
        self::REWRITE_URL               => array(XAPP_TYPE_BOOL, XAPP_TYPE_STRING),
        self::SERVICE_DESCRIPTION       => XAPP_TYPE_BOOL,
        self::CLASS_METHOD_SEPARATOR    => XAPP_TYPE_STRING
    );

    /**
     * options mandatory map for this class contains all mandatory values
     *
     * @var array
     */
    public static $optionsRule = array
    (
        self::IGNORE_PREFIXES           => 0,
        self::IGNORE_METHODS            => 0,
        self::IGNORE_PARENTS            => 0,
        self::CONTENT_TYPE              => 1,
        self::VERSION                   => 1,
        self::DESCRIPTION               => 0,
        self::ADDITIONAL_PARAMETERS     => 0,
        self::SERVICE_OVER_GET          => 0,
        self::REWRITE_URL               => 0,
        self::SERVICE_DESCRIPTION       => 1,
        self::CLASS_METHOD_SEPARATOR    => 0
    );


    /**
     * needs to be implemented in concrete implementation to compile smd map
     * to be outputted as response if the base url of the server is called
     *
     * @return mixed
     */
    abstract public function compile();


    /**
     * factory method creates singleton instance of smd class defined by driver
     * passed in first parameter, e.g. "json"
     *
     * @error 14101
     * @param string $driver expects the type of smd to create
     * @param null $options expects optional options to pass to class instance
     * @return Xapp_Rpc_Smd
     * @throws Xapp_Rpc_Smd_Exception
     */
    public static function factory($driver, $options = null)
    {
        $class = __CLASS__ . '_' . ucfirst(strtolower(trim($driver)));
        if(class_exists($class, true))
        {
            return $class::instance($options);
        }else{
            throw new Xapp_Rpc_Smd_Exception(xapp_sprintf(_("rpc smd class: %s not found"), $class), 1410101);
        }
    }


    /**
     * maps a function, a class or object or a directory of classes with all details/smd
     * properties found for the passed first parameter. this function is only called by server
     * instance since functions and classes need to be registered by server. all mapped
     * functions and classes will be stored as smd map and service map to quick check if service
     * has been mapped or not. the second parameter can contain array of methods to ignore when
     * passing only class as first parameter
     *
     * @see Xapp_Rpc_Server::register
     * @error 14102
     * @param null|string|object $mixed expects any of the above explained values
     * @param null|array $ignore expects methods to ignore as array or null for no ignores
     * @return array
     */
    final public function map($mixed = null, $ignore = null)
    {
        if($mixed !== null)
        {
            if(!is_object($mixed))
            {
                if(stristr((string)$mixed, DS) !== false)
                {
                    $mixed = $this->getClasses($mixed);
                }else{
                    $mixed = array($mixed);
                }
            }else{
                $mixed = array($mixed);
            }
            foreach($mixed as $m)
            {
                $tmp = array();
                //assume function
                if(is_string($m) && function_exists($m))
                {
                    $map = $this->mapFunction($m);
                    if(!array_key_exists($m, $this->_services))
                    {
                        $this->_services[] = $m;
                    }
                    if($map !== null)
                    {
                        xapp_array_set($tmp, $m, $map);
                    }
                    //assume class and/or method
                }else{
                    $map = $this->mapClass($m, $ignore);
                    $class = $this->getClass($m);
                    if(array_key_exists($class, $this->_services))
                    {
                        $this->_services[$class] = array();
                    }
                    foreach($map as $k => $v)
                    {
                        $this->_services[$class][] = $k;
                        xapp_array_set($tmp, "$class.$k", $v);
                    }
                }
                $this->_map = array_merge($this->_map, $tmp);
            }
        }
        return $this->_map;
    }


    /**
     * unmap everything when first parameter is not set or remove class from map
     * if first parameter is set. class can be a object, class name as string or
     * class in "class.method" dot notation
     *
     * @error 14103
     * @param null|string|object $class expects on of the above values
     * @return void
     */
    final public function unmap($class = null)
    {
        if($class !== null)
        {
            if(!is_object($class))
            {
                $class = array(get_class($class));
            }else if(strpos($class, '.') !== false && substr_count($class, '.') === 1){
                $class = explode('.', strtolower(trim($class)));
            }else{
                $class = array(strtolower(trim($class)));
            }
            if(array_key_exists($class[0], $this->_map))
            {
                if(isset($class[1]))
                {
                    if(array_key_exists($class[1], $this->_map[$class[0]])) { unset($this->_map[$class[0]][$class[1]]); }
                }else{
                    unset($this->_map[$class[0]]);
                }
            }
        }else{
            $this->_map = array();
        }
    }


    /**
     * maps a function, a class or object or a directory of classes - see map
     * method for more explanations
     *
     * @error 14114
     * @see Xapp_Rpc_Smd::map
     * @param null|string|object $mixed expects any of the above explained values
     * @param null|array $ignore expects methods to ignore as array or null for no ignores
     * @return Xapp_Rpc_Smd
     */
    public function set($mixed = null, $ignore = null)
    {
        $this->map($mixed, $ignore);
        return $this;
    }


    /**
     * unmaps a class from smd map or resets the complete map - see Xapp_Rpc_Smd::unmap for more
     *
     * @error 14115
     * @see Xapp_Rpc_Smd::unmap
     * @param null|string|object $class expects on of the above values
     * @return Xapp_Rpc_Smd
     */
    public function reset($class = null)
    {
        $this->unmap($class);
        return $this;
    }


    /**
     * get service from smd map either as function or class or dot notation for class.method.
     * if service is not found returns default value passed in second parameter
     *
     * @error 14104
     * @param string $name expects the service to get
     * @param null $default expects the default return value
     * @return array|mixed|null
     */
    public function get($name, $default = null)
    {
        return xapp_array_get($this->map(), $name, $default);
    }


    /**
     * checks if a service exist in service map or not. expects either function or method in second
     * parameter and class and method in first and second parameter. alternative pass class and method
     * as first parameter and dot notation like "class.method". this function is called by
     * xapp server instance where class and function/method is already known.
     *
     * @error 14105
     * @param null|string $mixed expects class or class.method string
     * @param null|string $function expects function or method for class in first parameter
     * @return bool
     */
    public function has($mixed = null, $function = null)
    {
        if($mixed !== null)
        {
            if(strpos($mixed, '.') !== false)
            {
                $mixed = explode('.', $mixed);
                $function = trim($mixed[1]);
                $mixed = trim($mixed[0]);
            }
            if($function !== null)
            {
                return (array_key_exists($mixed, $this->_services) && in_array($function, $this->_services[$mixed])) ? true : false;
            }else{
                return (in_array($mixed, $this->_services)) ? true : false;
            }
        }else{
            return (in_array($function, $this->_services)) ? true : false;
        }
    }


    /**
     * maps function and all parameters of function using reflection. returns object map
     * as object throws error if mapping was not successful
     *
     * @error 14105
     * @param string $function expects function name
     * @return null|XO
     * @throws Xapp_Rpc_Smd_Exception
     */
    protected function mapFunction($function)
    {
        try
        {
            $tmp = array();
            $obj = new XO();

            $function = new ReflectionFunction($function);
            if($function->isInternal())
            {
                throw new Xapp_Rpc_Smd_Exception(xapp_sprintf(_("function: %s can not be mapped since it is internal"), $function), 1410502);
            }
            $obj->transport = null;
            $obj->target = null;
            $method = $this->mapMethod($function);
            if(xapp_get_option(self::SERVICE_DESCRIPTION, $this) && isset($method->description))
            {
                $obj->description = $method->description;
            }
            if(isset($method->returns))
            {
                $obj->returns = $method->returns;
            }
            $i = 0;
            foreach($function->getParameters() as $p)
            {
                $params = $this->mapParameter($p);
                if(isset($method->params) && isset($method->params['type'][$i]))
                {
                    $params->type = $method->params['type'][$i];
                }
                if(isset($method->params) && isset($method->params['description'][$i]) && xapp_get_option(self::SERVICE_DESCRIPTION, $this))
                {
                    $params->description = $method->params['description'][$i];
                }
                $tmp[] = $params;
                $i++;
            }
            $obj->parameters = $tmp;
            return $obj;
        }
        catch(ReflectionException $e)
        {
            throw new Xapp_Rpc_Smd_Exception(xapp_sprintf(_("unable to map function: %s due to reflection error: %d, %s"), $function, $e->getCode(), $e->getMessage()), 1410501);
        }
    }


    /**
     * maps class and all methods of class using reflection returning array of all mapped
     * methods and its parameters ignoring method names passed in second parameter and prefixes
     * passed in class option IGNORE_PREFIXES and methods passed in class option IGNORE_METHODS.
     *
     * @error 14106
     * @param string|object $class expects class as object or string
     * @param null|array $ignore expects optional array of methods to ignore or null for no ignores
     * @return array
     * @throws Xapp_Rpc_Smd_Exception
     */
    protected function mapClass($class, $ignore = null)
    {
        $interfaces = null;
        $interface  = null;
        $tmp        = array();
        $map        = array();
        $_class     = '';

        try
        {
            if(xapp_is_option(self::IGNORE_PARENTS, $this) && (bool)xapp_get_option(self::IGNORE_PARENTS))
            {
                $_parents = false;
            }else{
                $_parents = true;
            }
            $_prefixes = (array)xapp_get_option(self::IGNORE_PREFIXES, $this);
            $_methods = array_merge((array)xapp_get_option(self::IGNORE_METHODS, $this), (array)$ignore);
            $_class = (is_object($class)) ? get_class($class) : $class;

            if(!is_object($class) && strpos($class, '.') !== false)
            {
                $method = substr($class, strpos($class, '.') + 1);
                $class = substr($class, 0, strpos($class, '.'));
            }else{
                $method = null;
            }

            $class = new ReflectionClass($class);
            if($method !== null)
            {
                $methods = array($class->getMethod($method));
            }else{
                $methods = $class->getMethods();
            }

            $interfaces = $class->getInterfaces();
            if(!empty($interfaces))
            {
                foreach((array)$interfaces as $interface)
                {
                    if(stripos($interface->getName(), 'Xapp_Rpc_Interface') !== false)
                    {
                        foreach((array)$interface->getMethods() as $i)
                        {
                            $tmp[] = strtolower($i->name);
                        }
                    }
                }
                $interfaces = $tmp;
            }

            foreach($methods as $m)
            {
                $tmp = array();
                $obj = new XO();
                if(!$_parents && $m->getDeclaringClass()->name !== $class->name)
                {
                    continue;
                }
                if(!empty($_prefixes) && preg_match('/^('.implode('|', $_prefixes).')/', $m->getName()))
                {
                    continue;
                }
                if(!empty($_methods) && preg_match('/^('.implode('|', $_methods).')$/i', $m->getName()))
                {
                    continue;
                }
                if($m->isInternal() || $m->isPrivate() || $m->isProtected())
                {
                    continue;
                }
                if($m->isAbstract())
                {
                    continue;
                }
                if($m->isStatic())
                {
                    continue;
                }
                if(in_array(strtolower($m->getName()), $interfaces))
                {
                    continue;
                }
                $obj->transport = null;
                $obj->target = null;
                $method = $this->mapMethod($m);
                if(xapp_get_option(self::SERVICE_DESCRIPTION, $this) && isset($method->description))
                {
                    $obj->description = $method->description;
                }
                if(isset($method->returns))
                {
                    $obj->returns = $method->returns;
                }
                $i = 0;
                foreach($m->getParameters() as $p)
                {
                    $params = $this->mapParameter($p);
                    if(isset($method->params) && isset($method->params['type'][$i]))
                    {
                        $params->type = $method->params['type'][$i];
                    }
                    if(isset($method->params) && isset($method->params['description'][$i]) && xapp_get_option(self::SERVICE_DESCRIPTION, $this))
                    {
                        $params->description = $method->params['description'][$i];
                    }
                    $tmp[] = $params;
                    $i++;
                }
                $obj->parameters = $tmp;
                $map[$m->getName()] = $obj;
            };
            return $map;
        }
        catch(ReflectionException $e)
        {
            throw new Xapp_Rpc_Smd_Exception(xapp_sprintf(_("unable to map class: %s due to reflection error: %d, %s"), $_class, $e->getCode(), $e->getMessage()), 1410601);
        }
    }


    /**
     * map a class method using reflection doc comment to get all comments to map
     * parameter type and method return value
     *
     * @error 14107
     * @param ReflectionFunctionAbstract $method expects the method to map
     * @return XO
     */
    protected function mapMethod(ReflectionFunctionAbstract $method)
    {
        $obj = new XO();
        $tmp = array();

        if(($comment = $method->getDocComment()) !== false)
        {
            $lines = preg_split('/\n/', trim($comment));

            if(sizeof($lines) > 1)
            {
                $obj->description = array();
                $obj->params = array();
                $i = -1;
                foreach($lines as $line)
                {
                    $i++;
                    if($i === 0 || $i === sizeof($lines) - 1)
                    {
                        continue;
                    }
                    if(stripos($line, '@') === false || stripos($line, '@desc') !== false)
                    {
                        $line = str_replace(array("\n", "\r\n", "\t"), '', trim($line));
                        $line = preg_replace(array('/\@desc/i', '/^\*\/?\s*/'), array('', ''), $line);
                        if(!empty($line))
                        {
                            $tmp[] = $line;
                        }
                    }
                    if(stripos($line, '@param') !== false || stripos($line, '@return') !== false)
                    {
                        $line = str_replace(array("\t", "\n", "\r"), '', trim($line));
                        $line = substr($line, strpos($line, '@'));
                        $line = preg_replace(array('/[ \s]+/', '/[ \s]\|[ \s]/'), array(' ', '|'), $line);
                        $word = preg_split('/\s+/i', $line);
                        $word = strtolower($word[1]);
                        if(stripos($word, '|') !== false)
                        {
                            $word = explode('|', $word);
                        }
                        if(stripos($line, '@param') !== false)
                        {
                            if(preg_match('/\$\w+\b(.*)$/isD', $line, $m))
                            {
                                $obj->params['description'][] = trim($m[1]);
                            }else{
                                $obj->params['description'][] = null;
                            }
                            if(is_string($word) && stripos($word, '$') !== false)
                            {
                                $obj->params['type'][] = 'mixed';
                            }else{
                                $obj->params['type'][] = self::mapType($word);
                            }
                        }
                        if(stripos($line, '@return') !== false)
                        {
                            $obj->returns = self::mapType($word);
                        }
                    }
                }
                if(!empty($tmp))
                {
                    $obj->description = implode(" ", $tmp);
                }
            }
        }
        return $obj;
    }


    /**
     * map parameter using reflection to get default value from method signature
     *
     * @error 14108
     * @param ReflectionParameter $parameter expects parameter to map
     * @return XO
     */
    protected function mapParameter(ReflectionParameter $parameter)
    {
        $obj = new XO();

        $obj->name = $parameter->getName();
        if($parameter->isDefaultValueAvailable())
        {
            $obj->default = $parameter->getDefaultValue();
        }
        $obj->optional = $parameter->isOptional();
        return $obj;
    }


    /**
     * map data type to common data type value used in most smd schema, e.g. int = integer
     * can receive arrays or string as input parameter
     *
     * @error 14109
     * @param string|array $type expects the data type as string to remap
     * @return array|string
     */
    public static function mapType($type)
    {
        if(!is_array($type))
        {
            $type = array($type);
        }
        foreach($type as &$t)
        {
            switch($t)
            {
                case 'int':
                    $t = 'integer';
                    break;
                case 'bool':
                    $t = 'boolean';
                    break;
                case 'double':
                    $t = 'float';
                    break;
                case 'long':
                    $t = 'integer';
                    break;
                case 'real':
                    $t = 'float';
                    break;
            }
        }
        return (sizeof($type) === 1) ? $type[0] : $type;
    }


    /**
     * recursive function to get all classes of directory by getting all files with
     * .php or .inc extension, parsing its content and trying to retrieve class name
     * from file content. this function needs process time it is not advised to pass
     * directories for classes to map
     *
     * @error 14110
     * @param string $dir expects the directory to look for classes
     * @param array $tmp contains all classes found
     * @return array
     * @throws Xapp_Rpc_Smd_Exception
     */
    protected function getClasses($dir, &$tmp = array())
    {
        $dir = rtrim($dir, DS) . DS;
        if(($d = @opendir(rtrim($dir, DS))) !== false)
        {
            while(($f = readdir($d)) !== false)
            {
                if($f !== '.' && $f !== '..')
                {
                    if(is_dir($dir . $f))
                    {
                        $this->getClasses($dir . $f, $tmp);
                    }else if(in_array(substr($f, strrpos($f, '.') + 1), array('php', 'inc'))){
                        $this->parseClass($dir . $f, $tmp);
                    }
                }
            }
        }else{
            throw new Xapp_Rpc_Smd_Exception(xapp_sprintf(_("class dir: %s is not found"), $dir), 1411001);
        }
        return $tmp;
    }


    /**
     * parse class file content to get class name. first parameter must be absolute file
     * path to class
     *
     * @error 14111
     * @param string $class absolute path to class
     * @param array $tmp array passed by reference to store class name in
     * @throws Xapp_Rpc_Smd_Exception
     */
    protected function parseClass($class, &$tmp = array())
    {
        if(($fp = fopen($class, 'r')) !== false)
        {
            $name = $buffer = '';
            $ns = '';
            while(!$name)
            {
                if(feof($fp)) break;
                $buffer .= fread($fp, 512);
                $tokens = @token_get_all($buffer);
                if(strpos($buffer, '{') === false) continue;
                for($i = 0;$i < count($tokens); $i++)
                {
                    if(is_array($tokens[$i]) && ($tokens[$i][0] === T_ABSTRACT || $tokens[$i][0] === T_PROTECTED || $tokens[$i][0] === T_PRIVATE))
                    {
                        break 2;
                    }
                    if(defined('T_NAMESPACE') && $tokens[$i][0] === T_NAMESPACE)
                    {
                        for($j = $i + 1; $j < count($tokens); $j++)
                        {
                            if(is_array($tokens[$j]))
                            {
                                $ns .= trim($tokens[$j][1]);
                            }else{
                                break;
                            }
                        }
                    }
                    if($tokens[$i][0] === T_CLASS)
                    {
                        for($j = $i + 1; $j < count($tokens); $j++)
                        {
                            if($tokens[$j] === '{')
                            {
                                $name = ((!empty($ns)) ? "\\".trim($ns, " \\") . "\\" : "") . $tokens[$i+2][1];
                            }
                        }
                    }
                }
            }
            if(!empty($name))
            {
                $tmp[] = $name;
            }
        }else{
            throw new Xapp_Rpc_Smd_Exception(xapp_sprintf(_("unable to open: %s"), $class), 1411101);
        }
    }


    /**
     * get class name from mixed parameter which can be object of class, class name or
     * class.method dot notation all returning the class name as string
     *
     * @error 14112
     * @param object|string $class expects class as explained above
     * @return string
     */
    protected function getClass($class)
    {
        if(is_object($class))
        {
            return get_class($class);
        }else{
            if(strpos($class, '.') !== false)
            {
                $class = substr($class, 0, strpos($class, '.'));
            }
            return $class;
        }
    }


    /**
     * get the smd target path in absolute or relative mode for services or base url for smd
     * output if first parameter is not set. this function will return target in htaccess rewrite
     * style if the option REWRITE_URL is set. this option can be either boolean true let the smd
     * resolve the rewrite pattern automatically which is nothing but extending the base path of
     * the url to contain the service class and function parameter like: "/base/rpc/index.php"
     * becomes "/base/rpc/class.function" or "/base/rpc/function" because rewrite will remap this
     * rule to "/base/rpc/index.php?service=class.function". you can also pass a string with your
     * rewrite rule as "/base/rpc/foo/{$class}/{$function}" with {($|%)} $ or % placeholder values.
     * always make sure your htaccess rule reflects the rewrite rule and vice versa and that service
     * values are always rewritten to $_GET "service" parameter. the second parameter defines whether
     * to return the url as relative or absolute or if not set will look for global option RELATIVE_TARGETS
     *
     * @error 14113
     * @param null|string $service expects optional the service method/function and class in dot notation
     * @param null|bool $relative defined whether to return path absolute or relative
     * @return string
     * @throws Xapp_Rpc_Smd_Exception
     */
    protected function getTarget($service = null, $relative = null)
    {
        $class = null;
        $function = null;
        $separator = xapp_get_option(self::CLASS_METHOD_SEPARATOR, $this);

        $url = Xapp_Rpc_Request::url(null, -1);
        $host = rtrim($url['host'], '/ ');
        $path = trim($url['path'], '/ ');

        if(preg_match('/(\.([^\/]+|$))/i', $path, $m, PREG_OFFSET_CAPTURE))
        {
            if(isset($m[1]) && is_array($m[1]))
            {
                $path = substr($path, 0, $m[1][1] + strlen($m[1][0]));
            }
        }
        if(xapp_is_option(self::REWRITE_URL, $this) && strpos($path, 'index.') !== false)
        {
            $path = substr($path, 0, strpos($path, 'index.'));
        }
        if($service !== null)
        {
            if(strpos($service, $separator) !== false)
            {
                $class = substr($service, 0, strpos($service, $separator));
                $function = substr($service, strpos($service, $separator) + 1);
            }else{
                $class = null;
                $function = $service;
            }
        }
        if($service !== null)
        {
            if(xapp_is_option(self::REWRITE_URL, $this))
            {
                $_url = xapp_get_option(self::REWRITE_URL, $this);
                if(is_bool($_url) && $_url)
                {
                    $_url = rtrim($path, '/') . '/{$class}{$separator}{$function}';
                }
                $_url = trim($_url, '/ ');
                if(($_url = parse_url($_url)) !== false)
                {
                    $path = trim(preg_replace(array('/\{(?:\$|\%)([^\}]+)\}/ie', '/\/+/'), array("\$$1", '/'), $_url['path']), '/ ');
                }else{
                    throw new Xapp_Rpc_Smd_Exception(xapp_sprintf(_("smd rewrite rule: %s is not a valid url or url path value"), $_url), 1411301);
                }
            }else{
                $path = $path . "?service=$service";
            }
        }
        if($relative === null)
        {
            $relative = xapp_get_option(self::RELATIVE_TARGETS, $this);
        }
        if((bool)$relative)
        {
            return '/' . ltrim($path, '/ ');
        }else{
            return $url['scheme'] . '://' . $host . ((isset($url['port']) && !empty($url['port'])) ? ':' . $url['port'] : '') . '/' . $path;
        }
    }
}