<?php
/**
 * @version 0.1.0
 * @package Xapp\String
 *
 * @author https://github.com/mc007
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */

function xapp_remove_DoubleSlash($in) {
    return preg_replace('%([^:])([/]{2,})%', '\\1/', $in);
}

function safe_string_escape($str)
{
    $len=strlen($str);
    $escapeCount=0;
    $targetString='';
    for($offset=0;$offset<$len;$offset++) {
        switch($c=$str{$offset}) {
            case "'":
                // Escapes this quote only if its not preceded by an unescaped backslash
                if($escapeCount % 2 == 0) $targetString.="\\";
                $escapeCount=0;
                $targetString.=$c;
                break;
            case '"':
                // Escapes this quote only if its not preceded by an unescaped backslash
                if($escapeCount % 2 == 0) $targetString.="\\";
                $escapeCount=0;
                $targetString.=$c;
                break;
            case '\\':
                $escapeCount++;
                $targetString.=$c;
                break;
            default:
                $escapeCount=0;
                $targetString.=$c;
        }
    }
    return $targetString;
}
function diffStr($old, $new)
{
    $a1 = str_split($old);
    $a2 = str_split($new);

    return join('', array_diff($a1, $a2));
}
function startsWith($haystack, $needle)
{
    return !strncmp($haystack, $needle, strlen($needle));
}

function endsWith($haystack, $needle)
{
    $length = strlen($needle);
    if ($length == 0) {
        return true;
    }

    return (substr($haystack, -$length) === $needle);
}