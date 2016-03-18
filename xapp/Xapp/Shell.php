<?php

defined('XAPP') || require_once(dirname(__FILE__) . '/../Core/core.php');

/**
 * Shell class
 *
 * @package Xapp
 * @class Xapp_Shell
 * @error 110
 * @author Frank Mueller <set@cooki.me>
 */
class Xapp_Shell
{
    /**
     * generic shell exec function to execute shell commands and valid if necessary
     * the return value. the first parameter expecting the cmd to execute can be passed
     * containing valid escaped command with valid escaped arguments or passed with phps
     * sprintf % placeholders for the arguments which need to be passed then in second
     * parameter $args. overwrite the default timeout by passing a timeout value in the
     * forth parameter. the third parameter can be a valid array containing all expected
     * return values for termination status - see phps pclose for more infos. the method
     * will return the return value of the command or false in case the expected termination
     * status does not match the expected return values or false if any other error occurred
     *
     * @error 11001
     * @param string $cmd expects the command to execute
     * @param array $args expects optional array of arguments to be injected into command string placeholders
     * @param null|array|string $return expects expected termination status values as string or array
     * @param int $timeout expects timeout for execution time
     * @return array|bool|string
     */
    public static function exec($cmd, Array $args = array(), $return = null, $timeout = 15)
    {
        $tmp = array();
        $res = null;

        foreach($args as $k => &$v)
        {
            $v = escapeshellarg(trim($v));
        }
        array_unshift($args, escapeshellcmd(trim($cmd)));
        $cmd = call_user_func_array('sprintf', $args);
        $start = time();
        if(($f = popen($cmd, 'r')) !== false)
        {
            while(!feof($f))
            {
                $res = trim(fgets($f, 4096));
                if($res !== "")
                {
                    $tmp[] = $res;
                }
                if(time() > $start + (int)$timeout)
                {
                    pclose($f);
                    return false;
                }
            }
            if($return !== null && $return !== false)
            {
                if(in_array((int)pclose($f), (array)$return))
                {
                    return ((!empty($tmp)) ? (sizeof($tmp) === 1) ? $tmp[0] : $tmp : "");
                }else{
                    return false;
                }
            }else{
                if((int)pclose($f) === -1)
                {
                    return false;
                }else{
                    return ((!empty($tmp)) ? (sizeof($tmp) === 1) ? $tmp[0] : $tmp : "");
                }
            }
        }
        return false;
    }


    /**
     * execute a command and detach it from the bash/shell to run in background. this function
     * only works on linux servers supporting nohup. see Xapp_Shell::exec for more explanations
     * on how to pass command string and arguments. this function returns the process id PID
     * if a process was intended to be started with this method. if any other command is send to
     * background via this method will return its return value.
     * NOTE if using this method to send process to background it is advisable to check status of
     * process with the returned PID
     *
     * @error 11002
     * @param string $cmd expects the command to execute
     * @param array $args expects optional array of arguments to be injected into command string placeholders
     * @return bool|int|mixed
     */
    public static function execBackground($cmd, Array $args = array())
    {
        $res = null;

        $cmd = 'nohup ' . escapeshellcmd(trim($cmd, 'nohup')) . " > /dev/null 2>&1 & echo $!";
        foreach($args as $k => &$v)
        {
            $v = escapeshellarg(trim($v));
        }
        array_unshift($args, $cmd);
        $cmd = call_user_func_array('sprintf', $args);
        exec($cmd, $out);
        if(sizeof($out) > 0 && !empty($out[0]) && is_numeric($out[0]))
        {
            return (int)$out[0];
        }else{
            return $out;
        }
    }


    /**
     * kill a process by either passing a resource variable opened by phps proc_open function,
     * a PID id or a process name currently running. use the second parameter $force to force
     * a kill using force kill flag for linux. this function is only compatible with linux.
     * NOTE this function should only be used when knowing the exact process id PID or the exact
     * process name - otherwise you may kill unintended services. the function will try to determine
     * the success of killing and returning a boolean value according to success. the return value should
     * not be trusted!
     *
     * @error 11003
     * @param null|string|resource|int $proc expects value as described above
     * @param bool $force hard force process making sure it will die for good
     * @return bool
     */
    public static function kill($proc = null, $force = false)
    {
        if($proc !== null)
        {
            if((bool)$force)
            {
                $force = "-9";
            }else{
                $force = "";
            }
            if(is_resource($proc)){
                if(($proc = proc_get_status($proc)) !== false)
                {
                    passthru("kill $force ".(int)$proc['pid']." 2>/dev/null >&- >/dev/null", $out);
                    return ((int)$out === 0 && proc_close($proc) !== -1) ? true : false;
                }
            }else if(is_numeric($proc)){
                passthru("kill $force ".(int)$proc." 2>/dev/null >&- >/dev/null", $out);
                return ((int)$out === 0) ? true : false;
            }else{
                passthru("killall ".(string)$proc." 2>/dev/null >&- >/dev/null", $out);
                return ((int)$out === 0) ? true : false;
            }
        }
        return false;
    }


    /**
     * get status of process by PID or process name. the status will contain details as:
     * name pid, uptime, cpu, memory, etc. if the process does not exists will return false
     *
     * @error 11004
     * @param null|string|int $proc expects the process id or the process name
     * @return array|bool
     */
    public static function status($proc = null)
    {
        if($proc !== null)
        {
            if(is_numeric($proc)){
                $ps = self::ps(false);
                return (!empty($ps) && isset($ps[(int)$proc])) ? $ps[(int)$proc] : false;
            }else{
                $ps = self::ps(true);
                return (!empty($ps) && isset($ps[strtolower((string)$proc)])) ? $ps[strtolower((string)$proc)] : false;
            }
        }else{
            $ps = self::ps(true);
            return (!empty($ps)) ? $ps : false;
        }
    }


    /**
     * execute the linux ps command to return status of all currently running processes. use the
     * second parameter to either group all running instances or processes of one programm into a
     * group = array or set to false to group by PID.
     *
     * @error 11005
     * @param bool $group expects a value whether to group the processes by programm name or not
     * @return array
     */
    public static function ps($group = true)
    {
        $tmp = array();

        exec("ps auxw | sed 1d | awk '{print $1\"|\"$2\"|\"$3\"|\"$4\"|\"$10\"|\"$11}'", $out);
        if(!empty($out))
        {
            foreach($out as $k => $v)
            {
                $v = explode("|", trim($v, "|"));
                if((bool)$group)
                {
                    if(!empty($v[5]))
                    {
                        $v[5] = DS . trim($v[5], DS);
                        $n = strtolower((substr($v[5], strrpos($v[5], DS) + 1)));
                        if(!array_key_exists($n, $tmp))
                        {
                            $tmp[$n] = array();
                        }
                        $tmp[$n][] = $v;
                    }
                }else{
                    if(!empty($v[5]))
                    {
                        $tmp[(int)$v[1]] = $v;
                    }
                }
            }
        }
        return $tmp;
    }
}