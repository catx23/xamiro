<?php
/**
 * @version 0.1.0
 * @package XApp-Connect\Utils\Debugging
 *
 * @author https://github.com/mc007
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */
/***
 * @param int $decimals
 * @return string
 */
if(!function_exists('XApp_Memory_Usage')){

    function XApp_Memory_Usage($decimals = 2)
    {
        $result = 0;
        ///return;

        if (function_exists('memory_get_usage'))
        {
            $result = memory_get_usage() / 1024;
        }

        else
        {
            if (function_exists('exec'))
            {
                $output = array();

                if (substr(strtoupper(PHP_OS), 0, 3) == 'WIN')
                {
                    exec('tasklist /FI "PID eq ' . getmypid() . '" /FO LIST', $output);

                    $result = preg_replace('/[\D]/', '', $output[5]);
                }

                else
                {
                    exec('ps -eo%mem,rss,pid | grep ' . getmypid(), $output);

                    $output = explode('  ', $output[0]);

                    $result = $output[1];
                }
            }
        }

        return number_format(intval($result) / 1024, $decimals, '.', '');
    }
}
/***
 * @param $obj
 * @param string $prefix
 * @return mixed
 */
if(!function_exists('xapp_dumpObject')){

    function xapp_dumpObject($obj,$prefix=''){
        $d = print_r($obj,true);
        error_log(' dump : ' .$prefix . ' : ' . $d);
        return $d;
    }
}
if(!function_exists('xapp_show_errors')){

    function xapp_show_errors(){
        ini_set('display_errors', '1');     # don't show any errors...
        error_reporting(E_ALL | E_STRICT);
    }
}
if(!function_exists('xapp_hide_errors')){

    function xapp_hide_errors(){
        ini_set('display_errors', '0');     # don't show any errors...
        error_reporting(E_ERROR);
    }
}

//if(!function_exists('xapp_cdump')){

    function xapp_cdump($prefix='',$obj=null,$trace=false){


        /*
	    if(is_array($prefix)||is_object($prefix)){
            $obj=$prefix;
            $prefix='DumpObject';
        }
        */
        $type = XApp_Service_Entry_Utils::getConsoleType();
        //$type ='firephp';

	    if($type==='firephp'){

            //$instance = Xapp_Console::instance('firephp');
            ////error_log('m ' . json_encode($instance));


            //$instance->log('test','asd','error');

	        /*
            xapp_console('xapp console message',$prefix,'dump', (array)$obj);
            if($trace){
                xapp_console('xapp console message',$prefix,'trace', (array)$obj);
            }
            */
        }elseif($type==='chromephp'){
            if($trace){
                Xapp_Console::instance('chromephp')->dump($obj);
            }else{
                Xapp_Console::instance('chromephp')->dump($obj);
            }

        }
        return;
    }
//}

//if(!function_exists('xapp_clog')){
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

/**
 * @param string $message
 */
    function xapp_clog($mixed = null, $label = null, $type = 'info', Array $options = array()){

        xapp_import('xapp.Service.Logger');

        $logger = XApp_Service_Logger::getLogInstance();

        $type = strtolower((string)$type);

        $_typeMap = array(
            'log'       => XApp_Service_Logger::LOG,
            'warn'      => XApp_Service_Logger::WARN,
            'info'      => XApp_Service_Logger::INFO,
            'error'     => XApp_Service_Logger::ERROR,
            'dump'      => XApp_Service_Logger::INFO,
            'trace'     => XApp_Service_Logger::INFO,
            'group'     => XApp_Service_Logger::GROUP,
            'ungroup'   => XApp_Service_Logger::GROUP_END
        );


        if(array_key_exists($type, $_typeMap)){
            if($label !== null)
            {
                $label = trim(trim($label), ':') . ':';
            }

            switch($type)
            {
                case ($type === 'ungroup' || $mixed === null):
                    $logger->groupEnd();
                    break;
                case 'group':
                    $logger->group($mixed);
                    break;
                case 'trace':
                    $logger->log((string)$label, $mixed, 'info');
                    break;
                default: {
                    $logger->$type((string)$label, $mixed, $type);
                }
            }
        }



        /*
        $type = XApp_Service_Entry_Utils::getConsoleType();

        //$type ='firephp';
        if($type==='firephp'){

            //xapp_console($message);

            //Xapp_Console::instance('chromephp')->info($message);


            $instance = Xapp_Console::instance('firephp');
            ////error_log('m ' . json_encode($instance));
            $instance->log('asfasdf','test','warn');
        }elseif($type==='chromephp'){
            Xapp_Console::instance('chromephp')->info($message);

        }
        */
        return;
    }

//}

if(!function_exists('xapp_prettyPrint')){

    /***
     * @param $json
     * @return string
     */
    function xapp_prettyPrint( $json )
    {
        $result = '';
        $level = 0;
        $prev_char = '';
        $in_quotes = false;
        $ends_line_level = NULL;
        $json_length = strlen( $json );

        for( $i = 0; $i < $json_length; $i++ ) {
            $char = $json[$i];
            $new_line_level = NULL;
            $post = "";
            if( $ends_line_level !== NULL ) {
                $new_line_level = $ends_line_level;
                $ends_line_level = NULL;
            }
            if( $char === '"' && $prev_char != '\\' ) {
                $in_quotes = !$in_quotes;
            } else if( ! $in_quotes ) {
                switch( $char ) {
                    case '}': case ']':
                    $level--;
                    $ends_line_level = NULL;
                    $new_line_level = $level;
                    break;

                    case '{': case '[':
                    $level++;
                    case ',':
                        $ends_line_level = $level;
                        break;

                    case ':':
                        $post = " ";
                        break;

                    case " ": case "\t": case "\n": case "\r":
                    $char = "";
                    $ends_line_level = $new_line_level;
                    $new_line_level = NULL;
                    break;
                }
            }
            if( $new_line_level !== NULL ) {
                $result .= "\n".str_repeat( "\t", $new_line_level );
            }
            $result .= $char.$post;
            $prev_char = $char;
        }

        return $result;
    }
}


if(!function_exists('xapp_print_json')){
    function xapp_print_json($obj,$prefix=''){
        $json_string = json_encode($obj);
        return $json_string;
    }
}

if(!function_exists('xapp_print_memory_stats')){

    /***
     * @param string $section
     */
    function xapp_print_memory_stats($section=""){

        return;

        global $xapp_profile_time_last;
        $now = microtime(true);
        if($xapp_profile_time_last==null){
            $xapp_profile_time_last=$now;
        }
        $diff = $now-$xapp_profile_time_last;
        error_log($section . ' :: memory : ' . XApp_Memory_Usage(). ' :: diff : ' . $diff);
        $xapp_profile_time_last = $now;
        global $xapp_logger;
        if($xapp_logger!=null){
            $xapp_logger->log($section . ' :: memory : ' . XApp_Memory_Usage() . ' :: diff : ' . $diff);
        }
    }
}

if(!function_exists('xdump')){

    /**
     * xapp generic dump function will try to dump any input in first parameter using the passed value in second
     * parameter or if not set by default echo and print_r in cli or none cli mode. the first parameter can by
     * anything that can be printed to screen via print_r function. the second parameter can by a php function,
     * an object or class name the implements the dump method as public static or none static method. the dump
     * method of object must have its own logic for dumping objects since this function will do nothing else
     * but calling the method returning void. you can also use a php function like json_encode in second parameter
     * to encode and dump your input. overwrite like:
     *
     * <code>
     *      function xapp_dump($what, $with = null)
     *      {
     *          //your custom code here
     *      }
     * </code>
     *
     * @param mixed $what expects any type of variable
     * @param null|string|callable|object $with expects optional value with what to output first parameter
     */
    function xdump($what, $with = null)
    {

        $res = '';
        ob_start();

        if($with !== null)
        {
            if(is_callable($with) && function_exists($with))
            {
                $res .= call_user_func($with, $what);
            }else if(is_object($with) && method_exists($with, 'dump')){
                $res .= $with->dump($what);
            }else if(is_string($with)){
                if(stripos($with, 'xapp') !== false)
                {
                    if(!xapped($with))
                    {
                        xapp_import(lcfirst(str_replace('_', '.', $with)));
                    }
                }
                if(method_exists($with, 'dump'))
                {
                    $res .= call_user_func(array($with, 'dump'), $what);
                }
            }
        }
        if(strlen($o = (string)ob_get_contents()) > 0)
        {
            ob_end_clean();
            $res .=$o;
        }else{
            @ob_end_clean();
            $res .= ((strtolower(php_sapi_name()) === 'cli') ? print_r($what, true) : "<pre>".print_r($what, true)."</pre>");
        }

        return $res;
    }

}