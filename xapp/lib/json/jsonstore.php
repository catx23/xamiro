<?php
/**
 * @version 0.1.0
 * @package JSON
 *
 * @author https://github.com/mc007
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */

class JsonStore {
   function toString($obj) {
      $json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
      return $json->encode($obj, "  ");
   }
   function asObj($jsonstr) {
      $json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
       $res = $json->decode($jsonstr);
       return $res;
   }
   function& get(&$obj, $expr) {
      if (($expr = JsonStore::_normalizedFirst($obj, $expr)) !== false) {
		 $o =& $obj;
         $keys = preg_split("/([\"'])?\]\[([\"'])?/", preg_replace(array("/^\\$\[[\"']?/", "/[\"']?\]$/"), "", $expr));
		 for ($i=0; $i<count($keys); $i++)
			 $o =& $o[$keys[$i]];
		 return $o;
	  }
	  return null;
   }
   function set(&$obj, $expr, $value) {
	  if ($res =& JsonStore::get($obj, $expr))
	     $res = $value;
   }
   function add(&$obj, $parentexpr, $value, $name="") {
	  $parent =& JsonStore::get($obj, $parentexpr);
	  if ($name != "") $parent[$name] = $value;
	  else             $parent[] = $value;
   }
   function remove(&$obj, $expr) {
      if (($expr = JsonStore::_normalizedFirst($obj, $expr)) !== false) {
		 $o =& $obj;
         $keys = preg_split("/([\"'])?\]\[([\"'])?/", preg_replace(array("/^\\$\[[\"']?/", "/[\"']?\]$/"), "", $expr));
		 for ($i=0; $i<count($keys)-1; $i++)
			$o =& $o[$keys[$i]];
		 unset($o[$keys[$i]]);
		 return true;
	  }
	  return false;
   }
   function _normalizedFirst($o, $expr) {
	  if ($expr == "")
		  return false;
	  else if (preg_match("/^\$(\[([0-9*]+|'[-a-zA-Z0-9_ ]+')\])*$/", $expr)) {
		  print("normalized: " . $expr);
		  return $expr;
	  }
	  else {
		  $res = jsonPath($o, $expr, array("resultType" => "PATH"));
		  return $res ? $res[0] : $res;
	  }
   }
}
?>