<?php
/**
 * @version 0.1.0
 *
 * @author https://github.com/mc007
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 * @package XApp-Commander
 */
class XApp_Security_Utils
{
    /**
     * defines whether readable error messages are omitted in error object or not
     *
     * @const OMIT_ERROR
     */
    const OMIT_ERROR                    = 'REQUEST_OMIT_ERROR';

    /**
     * array of ip´s that are allowed to request denying all others
     *
     * @const ALLOW_IP
     */
    const ALLOW_IP                      = 'REQUEST_ALLOW_IP';

    /**
     * Enable validation
     *
     * @const VALIDATE
     */
    const VALIDATE                      = 'RPC_VALIDATE';

    /**
     * array of ip´s that are always blocked from service
     *
     * @const DENY_IP
     */
    const DENY_IP                       = 'REQUEST_DENY_IP';

    /**
     * array of username and password set as array key 0 and 1 to activate basic auth
     *
     * @const BASIC_AUTH
     */
    const BASIC_AUTH                    = 'REQUEST_BASIC_AUTH';

    /**
     * disable gateway itself not servicing any requests
     *
     * @const DISABLE
     */
    const DISABLE                       = 'REQUEST_DISABLE';
    /**
     * array of services to disable. array must contain either full service name
     * or wildcard regex conform name like user.* or *.user. it can also contain any
     * other regex valid syntax. use dot syntax to disable only certain methods of
     * a class, e.g. class.method
     *
     * @const DISABLE_SERVICE
     */
    const DISABLE_SERVICE               = 'REQUEST_DISABLE_SERVICE';

    /**
     * array of host names, without scheme, to allow and block all others. the host
     * name must be the same that will be found in request headers like foo.com
     *
     * @const ALLOW_HOST
     */
    const ALLOW_HOST                    = 'REQUEST_ALLOW_HOST';

    /**
     * array of host names to always block from service. host name must be without
     * scheme, e.g. foo.com
     *
     * @const DENY_HOST
     */
    const DENY_HOST                     = 'REQUEST_DENY_HOST';

    /**
     * boolean value to define whether to deny service when not called through HTTPS
     *
     * @const FORCE_HTTPS
     */
    const FORCE_HTTPS                   = 'REQUEST_FORCE_HTTPS';

    /**
     * array of user agents to allow service to an block all others. values must be
     * regex conform expressions or simple values. if you want to make sure you want
     * to block exact name pass as ^name$ or use wildcard patterns .* or value as it
     * is which will equal to a /value/i regex expression. NOTE: if you use plain
     * value like google for example all other agent names like googlebot,
     * googlesearch, .. are also blocked
     *
     * @const ALLOW_USER_AGENT
     */
    const ALLOW_USER_AGENT              = 'REQUEST_ALLOW_USER_AGENT';

    /**
     * array of user agents to always deny service of - see explanations for
     * ALLOW_USER_AGENT
     *
     * @const DENY_USER_AGENT
     */
    const DENY_USER_AGENT               = 'REQUEST_DENY_USER_AGENT';

    /**
     * array of refereres to allow and deny all others. values must be
     * regex conform expressions or simple values. if you want to make sure you want
     * to block exact name pass as ^name$ or use wildcard patterns .* or value as it
     * is which will equal to a /value/i regex expression.
     *
     * @const ALLOW_REFERER
     */
    const ALLOW_REFERER                 = 'REQUEST_ALLOW_REFERER';

    /**
     * activate signed requests expecting signature hash in request
     *
     * @const SIGNED_REQUEST
     */
    const SIGNED_REQUEST                = 'REQUEST_SIGNED_REQUEST';

    /**
     * defines the signed request method to get user identifier from the request object. allowed values are:
     * - host = will check for host/server name from server header object
     * - ip = will check for client ip value from server header object
     * - user = will check for user parameter value in request post object - see SIGNED_REQUEST_USER_PARAM
     *
     * @const SIGNED_REQUEST_METHOD
     */
    const SIGNED_REQUEST_METHOD         = 'REQUEST_SIGNED_REQUEST_METHOD';

    /**
     * set an optional array of services to exclude from being only signed request services
     * by passing service names like "function" or "class.method"
     *
     * @const SIGNED_REQUEST_EXCLUDES
     */
    const SIGNED_REQUEST_EXCLUDES       = 'REQUEST_SIGNED_REQUEST_EXCLUDES';



    /**
     * set parameter name for signed request signature parameter - the parameter
     * where the signature for the request is to be found
     *
     * @const SIGNED_REQUEST_SIGN_PARAM
     */
    const SIGNED_REQUEST_SIGN_PARAM     = 'REQUEST_SIGNED_REQUEST_SIGN_PARAM';


    const SIGN_KEY                      = 'REQUEST_SIGN_KEY';
    const SIGNING_TOKEN_FIELD           = 'REQUEST_SIGNING_TOKEN_FIELD';
    const SIGN_ALGORITHM                = 'REQUEST_SIGN_ALGORITHM';
    const ADD_SIGN_SALT                 = 'REQUEST_ADD_SIGN_SALT';
    const SIGN_SALT                     = 'REQUEST_SIGN_SALT';

    const DEFAULT_SIGNING_TOKEN_FIELD   = 'sig';
    const DEFAULT_SIGN_ALGORITHM        = 'sha1';

    public static $optionsDict = array
    (
        self::OMIT_ERROR                => XAPP_TYPE_BOOL,
        self::ALLOW_IP                  => XAPP_TYPE_ARRAY,
        self::DENY_IP                   => XAPP_TYPE_ARRAY,
        self::BASIC_AUTH                => XAPP_TYPE_ARRAY,
        self::DISABLE                   => XAPP_TYPE_BOOL,
        self::DISABLE_SERVICE           => XAPP_TYPE_ARRAY,
        self::ALLOW_HOST                => XAPP_TYPE_ARRAY,
        self::DENY_HOST                 => XAPP_TYPE_ARRAY,
        self::FORCE_HTTPS               => XAPP_TYPE_BOOL,
        self::ALLOW_USER_AGENT          => XAPP_TYPE_ARRAY,
        self::DENY_USER_AGENT           => XAPP_TYPE_ARRAY,
        self::ALLOW_REFERER             => XAPP_TYPE_ARRAY,
        self::SIGNED_REQUEST            => XAPP_TYPE_BOOL,
        self::SIGNED_REQUEST_METHOD     => XAPP_TYPE_STRING,
        self::SIGNED_REQUEST_EXCLUDES   => XAPP_TYPE_ARRAY,
        self::SIGNED_REQUEST_SIGN_PARAM => XAPP_TYPE_STRING,
        self::VALIDATE                  => XAPP_TYPE_BOOL,
        self::SIGN_KEY                  => XAPP_TYPE_STRING,
        self::SIGNING_TOKEN_FIELD       => XAPP_TYPE_STRING,
        self::SIGN_ALGORITHM            => XAPP_TYPE_STRING,
        self::ADD_SIGN_SALT             => XAPP_TYPE_BOOL,
        self::SIGN_SALT                 => XAPP_TYPE_STRING
    );

    /**
     *
     * Check all validation methods provided into options
     *
     *
     * @param $options
     * @param $error
     * @param $success
     */
    public static function check($options,&$error,&$success) {
        if(xapp_get_option(self::VALIDATE,$options)) {
            foreach(xapp_get_options($options) as $k => $v)
            {
                self::validate($k,$v,$options,$error,$success);
            }
        }
    }

    /**
     *
     *  Validate using method
     *
     * @param $method         :   validation method
     * @param array $value    :   data for the validation
     * @param $options        :   options
     * @param $error
     * @param $success
     */
    public static function validate($method, $value,&$options,&$error,&$success) {
        $user = null;
        $method = strtoupper($method);

        switch($method)
        {
            case self::ALLOW_IP:
                $client_ip=self::getClientIp();
                if($client_ip !== null && !in_array($client_ip, $value))
                {
                    $error[] = XAPP_TEXT_FORMATTED("IP_NOT_ALLOWED",Array($client_ip));
                }
                else
                {
                    $success[] = XAPP_TEXT_FORMATTED("ALLOWED_IP",Array($client_ip));
                }
            break;
            case self::DENY_IP:
                $client_ip=self::getClientIp();
                if($client_ip !== null && in_array($client_ip, $value))
                {
                    $error[] = XAPP_TEXT_FORMATTED("IP_DISALLOWED",Array($client_ip));
                }
                else
                {
                    $success[] = XAPP_TEXT_FORMATTED("IP_NOT_DISALLOWED",Array($client_ip));
                }
            break;
            case self::ALLOW_HOST:
                $host=self::getHost();
                if($host !== null  && !in_array($host, $value))
                {
                    $error[] = XAPP_TEXT_FORMATTED("HOST_DENIED",Array($host));
                }
                else
                {
                    $success[] = XAPP_TEXT_FORMATTED("HOST_ALLOWED",Array($host));
                }
            break;
            case self::DENY_HOST:
                $host=self::getHost();
                if($host !== null  && in_array($host, $value))
                {
                    $error[] = XAPP_TEXT_FORMATTED("HOST_DENIED",Array($host));
                }
                else
                {
                    $success[] = XAPP_TEXT_FORMATTED("HOST_ALLOWED",Array($host));
                }
            break;
            case self::ALLOW_REFERER:
                $referer=self::getReferer();
                $found=false;
                foreach($value as $referer_url) {
                    $parsed_url= parse_url($referer_url,PHP_URL_HOST);
                    if (($parsed_url != '') && ($parsed_url !== FALSE))
                        $referer_url = $parsed_url;

                    $found = $found || ($referer_url == $referer );
                }
                if (!$found)
                {
                    $error[] = XAPP_TEXT_FORMATTED("REFERER_NOT_ALLOWED",Array($referer));
                }
                else
                {
                    $success[] = XAPP_TEXT_FORMATTED("ALLOWED_REFERER",Array($referer));

                }

            break;
            case self::ALLOW_USER_AGENT:
                $user_agent=$_SERVER["HTTP_USER_AGENT"];
                if($user_agent != null && !preg_match('/('.implode('|',$value).')/i', $user_agent))
                {
                    $error[] = XAPP_TEXT_FORMATTED("USER_AGENT_DENIED",Array($user_agent));
                }
                else
                {
                    $success[] = XAPP_TEXT_FORMATTED("ALLOWED_USER_AGENT",Array($user_agent));
                }
            break;
            case self::DENY_USER_AGENT:
                $user_agent=$_SERVER["HTTP_USER_AGENT"];
                if($user_agent != null && preg_match('/('.implode('|',$value).')/i', $user_agent))
                {
                    $error[] = XAPP_TEXT_FORMATTED("USER_AGENT_DENIED",Array($user_agent));
                }
                else
                {
                    $success[] = XAPP_TEXT_FORMATTED("ALLOWED_USER_AGENT",Array($user_agent));
                }
            break;
            case self::SIGNED_REQUEST:
                if($value)
                {
                    if ( !isset($options[self::SIGN_KEY]) )
                    {
                        $error[] = XAPP_TEXT_FORMATTED("SIGN_KEY_NOT_PROVIDED");
                    }
                    else
                    {
                        // Gather all required options (key, algo, token_field, salt)
                        $key = $options[self::SIGN_KEY];
                        $algo= ( isset($options[self::SIGN_ALGORITHM]) ? $options[self::SIGN_ALGORITHM] : self::DEFAULT_SIGN_ALGORITHM );
                        $token_field= ( isset($options[self::SIGNING_TOKEN_FIELD]) ? $options[self::SIGNING_TOKEN_FIELD] : self::DEFAULT_SIGNING_TOKEN_FIELD );

                        $salt='';
                        if (array_key_exists(self::SIGN_SALT,$options))
                            $salt = $options[self::SIGN_SALT];

                        // Get token
                        $token = $value[$token_field];

                        if ( !isset($token))
                        {
                            $error[] = XAPP_TEXT_FORMATTED("SIGNING_TOKEN_NOT_FOUND");

                        }
                        else
                        {

                            // Remove token itself from data
                            if (array_key_exists($token_field,$value)) unset($value[$token_field]);

                            // Repare string to be hashed
                            $data = $salt . implode("",$value);

                            // Generate sign using algorithm
                            $sign = hash_hmac($algo,$data,$key);

                            if ( $sign != $token)
                            {
                                $error[] = XAPP_TEXT_FORMATTED("SIGN_VALIDATION_FAIL");
                            }
                            else
                            {
                                $success[] = XAPP_TEXT_FORMATTED("SIGN_VALIDATION_SUCCESS");

                            }

                        }

                    }
                }
                break;
        }

    }

    /**
     * get the client ip from request. returns null if not possible
     *
     * @error 14419
     * @return mixed|null
     */
    public static function getClientIp()
    {
        if(strtolower(php_sapi_name()) !== 'cli')
        {
            if(isset($_SERVER['HTTP_CLIENT_IP']) && strcasecmp($_SERVER['HTTP_CLIENT_IP'], "unknown"))
            {
                return $_SERVER['HTTP_CLIENT_IP'];
            }
            if(isset($_SERVER['HTTP_X_FORWARDED_FOR']) && strcasecmp($_SERVER['HTTP_X_FORWARDED_FOR'], "unknown"))
            {
                return $_SERVER['HTTP_X_FORWARDED_FOR'];
            }
            if(!empty($_SERVER['REMOTE_ADDR']) && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
            {
                return $_SERVER['REMOTE_ADDR'];
            }
        }
        return null;
    }

    /**
     * get host name from request. returns null if not found
     *
     * @error 14415
     * @return null|string
     */
    public static function getHost()
    {
        if(strtolower(php_sapi_name()) !== 'cli')
        {
            $host = $_SERVER['HTTP_HOST'];
            if(!empty($host))
            {
                return $host;
            }
            $host = $_SERVER['SERVER_NAME'];
            if(!empty($host))
            {
                return $host;
            }
        }
        return null;
    }
    /**
     * get the server referer from request. returns null if not found
     *
     * @error 14417
     * @return null|string
     */
    public static function getReferer()
    {
        if(strtolower(php_sapi_name()) !== 'cli')
        {
            if(getenv('HTTP_ORIGIN') && strcasecmp(getenv('HTTP_ORIGIN'), 'unknown'))
            {
                $ref = getenv('HTTP_ORIGIN');
            }
            else if(isset($_SERVER['HTTP_ORIGIN']) && $_SERVER['HTTP_ORIGIN'] && strcasecmp($_SERVER['HTTP_ORIGIN'], 'unknown'))
            {
                $ref = $_SERVER['HTTP_ORIGIN'];
            }
            else if(getenv('HTTP_REFERER') && strcasecmp(getenv('HTTP_REFERER'), 'unknown'))
            {
                $ref = getenv('HTTP_REFERER');
            }
            else if(isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] && strcasecmp($_SERVER['HTTP_REFERER'], 'unknown'))
            {
                $ref = $_SERVER['HTTP_REFERER'];
            }else{
                $ref = false;
            }
            if($ref !== false && !empty($ref))
            {
                if(($host = parse_url($ref, PHP_URL_HOST)) !== false)
                {
                    return trim($host);
                }
            }
        }
        return null;
    }


}