<?php

defined('XAPP') || require_once(dirname(__FILE__) . '/../Core/core.php');

/**
 * Cli class
 *
 * @package Xapp
 * @class Xapp_Cli
 * @error 108
 * @author Frank Mueller <set@cooki.me>
 */
class Xapp_Cli
{
    /**
     * stores the cli arguments also found in phps global variable argv into class cache to be used
     * after first parsing into key => value array
     *
     * @var null
     */
    protected static $_cache = null;


    /**
     * class can only be used static since all functions are accessible static only and
     * should be used like that.
     * @error 10801
     */
    protected function __construct(){}


    /**
     * init function can be used to init all command line arguments to be stored into cache before
     * reading them.
     *
     * @error 10802
     * @param null|string $prefix expects optional argument prefix e.g. --arg
     * @param string $separator expects the arg value separator value e.g. arg=value
     * @return void
     */
    public static function init($prefix = null, $separator = '=')
    {
        if(self::$_cache === null)
        {
            self::$_cache = self::arg(null, $prefix, $separator);
        }
    }


    /**
     * retrieve arguments from command line which are defined by a optional argument prefix
     * e.g. --arg and a default key => value separator which is: arg=value. this function will
     * extract all args and values into an cached array. by passing the first parameter will try
     * to retrieve the parameters value from the array. if the first parameter is empty will
     * return the whole argument array with its values
     *
     * @error 10803
     * @param null|string $arg expects the optional argument to get from command line
     * @param null|string $prefix expects optional argument prefix e.g. --arg
     * @param string $separator expects the arg value separator value e.g. arg=value
     * @return array|mixed
     */
    public static function arg($arg = null, $prefix = null, $separator = '=')
    {
        $args = array();

        $prefix = (string)$prefix;
        $separator = (string)$separator;

        if(self::$_cache !== null)
        {
            if($arg !== null && array_key_exists($arg, self::$_cache))
            {
                return self::$_cache[$arg];
            }else{
                return self::$_cache;
            }
        }

        if(isset($_SERVER['argv']))
        {
            for($i = 1; $i < sizeof($_SERVER['argv']); $i++)
            {
                $a = trim($_SERVER['argv'][$i]);
                if(!empty($prefix))
                {
                    if(substr($a, 0, strlen($prefix)) !== $prefix)
                    {
                        continue;
                    }
                }
                if(strpos($a, $separator) !== false)
                {
                    $v = explode($separator, $a, 2);
                    $args[trim(substr($v[0], strlen($separator) + 1))] = trim($v[1]);
                }else{
                    $args[] = trim($a);
                }
            }
        }

        if(self::$_cache === null)
        {
            self::$_cache = $args;
        }

        if($arg !== null && array_key_exists($arg, $args))
        {
            return $args[$arg];
        }else{
            return $args;
        }
    }


    /**
     * method to prompt an option message to command line asking the user to enter
     * one of the passed options. first argument expects your prompt option message.
     * second argument asks for the option array of values like array(1,2,3) or
     * array(a,b,c). if the user does not enter a valid option by pressing the according
     * key the user will be prompted to repeat. the function will return the valid chosen
     * option.
     *
     * @error 10804
     * @param string $msg expects the option prompt message
     * @param null|array $options expects a valid array with option values
     * @return null|string
     */
    public static function read($msg, Array $options = null)
    {
        if($options !== null && !empty($options))
        {
            $msg .= ' [ '.implode(', ', $options).' ]';
        }
        fwrite(STDOUT, trim(trim($msg), ':') . ': ');
        $input = (string)trim(fgets(STDIN));
        if(($options !== null && !empty($options)) && !in_array($input, $options))
        {
            self::write('Please enter a valid option');
            $input = (string)self::read($msg, $options);
        }
        return $input;

   	}


    /**
     * writes message to command line either as new line or by replacing the current
     * line with the passed message. the function returns a boolean value if writing to
     * command line was successful or not.
     *
     * @error 10805
     * @param string|array $msg expects the message to display
     * @param bool $replace expects boolean value on whether to replace line or write new line
     * @return bool
     */
    public static function write($msg, $replace = false)
    {
        $c = 0;

        foreach((array)$msg as $m)
        {
            if(fwrite(STDOUT, (((bool)$replace) ? "\r\033[K".$m : $m.PHP_EOL)))
            {
                $c++;
            }
        }
        return ($c === sizeof($msg)) ? true : false;
    }


    /**
     * predefined prompt message to be send to command line. currently only supported
     * prompting the user for entering a password. the function will return the value
     * of the prompt command e.g. the entered password for command "password"
     *
     * @error 10806
     * @param string $cmd expects a valid prompt cmd e.g. 'password'
     * @return null|string|mixed
     */
    public static function prompt($cmd)
    {
        $cmd = strtolower(trim($cmd));
        switch($cmd)
        {
            case 'password':
                return shell_exec('/usr/bin/env bash -c \'read -s -p "'.escapeshellcmd("password: ").'" var && echo $var\'');
                break;
        }
    }


    /**
     * method to sleep or halt command line for x seconds before next write/read command
     * can be executed with option of counting down.
     *
     * @error 10807
     * @param int $seconds expects sleep time
     * @param bool $countdown expects whether to visually count down with 1,2,3,...
     * @return void
     */
    public static function sleep($seconds = 0, $countdown = false)
    {
        $time = 0;
        $seconds = (int)$seconds;

        if((bool)$countdown === false)
        {
            sleep($seconds);
        }else{
            $time = $seconds;
            while($time > 0)
            {
                fwrite(STDOUT, "\r\033[K".$time);
                sleep(1);
                $time--;
            }
        }
    }
}