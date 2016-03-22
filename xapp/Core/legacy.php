<?php

// *****************************************************************
// legacy functions for php version < 5.3.0. this file must be
// included when using php version lesser then 5.3.0. no need to
// include this file manually since it will be included with
// core.php file
// *****************************************************************


if(!function_exists('parse_ini_string'))
{
    /**
     * emulates php parse_ini_string function
     *
     * @see http://www.php.net/manual/en/function.parse-ini-string.php
     * @param string $string expects the ini settings as string
     * @return array
     */
    function parse_ini_string($string)
    {
        $array = array();
        $lines = explode("\n", trim($string));
        foreach($lines as $line)
        {
            $regex = preg_match("/^(?!;)(?P<key>[\w+\.\-]+?)\s*=\s*(?P<value>.+?)\s*$/", $line, $match);
            if($regex)
            {
                $key = $match['key'];
                $value = $match['value'];
                if(preg_match( "/^\".*\"$/", $value) || preg_match( "/^'.*'$/", $value))
                {
                    $value = mb_substr($value, 1, mb_strlen($value) - 2);
                }
                $array[$key] = $value;
            }
        }
        return $array;
    }
}


if(!function_exists('get_called_class'))
{
    /**
     * emulates php get_called_class. will fail on very deep nested callees
     * since implementation tries to get always the first callee which does
     * not mean its the first callee in chain. use php versions > 5.3.0 since
     * everything below that version is considered to be outdated. will throw
     * exception if callee can not be determined
     *
     * @see http://www.php.net/manual/en/function.get-called-class.php
     * @param bool|array $bt expects either debug backtrace array or false to force getting backtrace in function
     * @param int $l expects the level or steps of backtrace will look by default
     * @return string
     * @throws Exception
     */
    function get_called_class($bt = false, $l = 1)
    {
        if(!$bt) $bt = debug_backtrace();
        if(!isset($bt[$l])) throw new Exception("cannot find called class -> stack level too deep.");
        if(!isset($bt[$l]['type']))
        {
            throw new Exception('type not set');
        }else{
            switch ($bt[$l]['type'])
            {
                case '::':
                    if(($lines = file($bt[$l]['file'])) !== false)
                    {
                        $matches = array();
                        $i = 0;
                        $line = '';
                        do
                        {
                            $i++;
                            $line = $lines[$bt[$l]['line']-$i] . $line;
                        }
                        while(stripos($line, $bt[$l]['function']) === false);
                        preg_match('/([a-zA-Z0-9\_]+)\:\:'.$bt[$l]['function'].'/su', $line, $matches);
                        //fall back for static singleton instantiation with method call in same line
                        if(sizeof($matches) === 0)
                        {
                            preg_match('/([a-zA-Z0-9\_]+)\:\:(?:[^\(]{1,})\((?:[^\)]*)(?:[\)]{1,})\-\>'.$bt[$l]['function'].'/su', $line, $matches);
                        }
                        if(!isset($matches[1]))
                        {
                            throw new Exception("could not find caller class: originating method call is obscured.");
                        }
                        switch ($matches[1])
                        {
                            case 'self':
                            case 'parent':
                                return get_called_class($bt,$l+1);
                            default:
                                return $matches[1];
                        }
                    }else{
                        throw new Exception("unable to open file for introspection");
                    }
                case '->':
                    switch ($bt[$l]['function'])
                    {
                        case '__get':
                            if(!is_object($bt[$l]['object']))
                            {
                                throw new Exception("edge case fail. __get called on non object.");
                            }
                            return get_class($bt[$l]['object']);
                        default: return $bt[$l]['class'];
                    }
                default:
                    throw new Exception("unknown backtrace method type");
            }
        }
    }
}