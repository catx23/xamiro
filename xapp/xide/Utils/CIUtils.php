<?php
/**
 * Class XApp_CIUtils
 *
 * Helper tools for Configurable Information fields
 *
 *
 */
class XApp_CIUtils {

    /***
    *
    *   Configurable Information properties names
    */
    const CI_FIELD_ID = "id";
    const CI_FIELD_NAME = "name";
    const CI_FIELD_DESCRIPTION = "description";
    const CI_FIELD_TYPE = "type";
    const CI_FIELD_DATAREF = "dataRef";
    const CI_FIELD_ENABLED = "enabled";
    const CI_FIELD_FLAGS = "flags";
    const CI_FIELD_GROUP = "group";
    const CI_FIELD_ORDER = "order";
    const CI_FIELD_PARAMS = "params";
    const CI_FIELD_PARENTID = "parentId";
    const CI_FIELD_PLATFORM = "platform";
    const CI_FIELD_STOREDESTINATION = "storeDestination";
    const CI_FIELD_TITLE = "title";
    const CI_FIELD_UID = "uid";
    const CI_FIELD_VALUE = "value";
    const CI_FIELD_ENUM_TYPE= "enumType";
    const CI_FIELD_VISIBLE = "visible";

    /**
     * Pattern for ID autogeneration
     */
    const CI_AUTOGEN_ID_PATTERN = "hhhh-hh-hh-hh-hhhhhh";

    /**
     * @param $cis
     * @return array
     */
    public static function fromArray($cis){

        $result = array();
	    foreach($cis as $option){
            $ci = self::createCI(
                $option['name'],
                $option['name'],
                $option['type'],
                $option['name']
            );
            self::set($ci,self::CI_FIELD_VALUE,$option['value']);
            self::set($ci,self::CI_FIELD_ENUM_TYPE,$option['enumType']);
            self::set($ci,self::CI_FIELD_VISIBLE,$option['visible']);
		    self::set($ci,self::CI_FIELD_GROUP,$option['group']);
		    self::set($ci,self::CI_FIELD_PARAMS,$option['params']);
            $result[]=$ci;
        }
        return $result;
    }

    /**
     *
     * Creates a new CI object
     *
     * @param string $name          : CI Name
     * @param string $description   : CI Description
     * @param string $type          : CI XAPP_TYPE
     * @param string $id            : (optional) CI id, if not provided, it will be generated
     * @param array $extraFields    : (optional) additional fields Array( field => value ... )
     * @return object               : Created CI object
     */
    public static function createCI($name,$description,$type,$id="",$extraFields=Array()) {
        $CI=self::newDefaultCI();
        $CI->{self::CI_FIELD_NAME} = $name;
        $CI->{self::CI_FIELD_TITLE} = $name;
        $CI->{self::CI_FIELD_DESCRIPTION} = $description;
        $CI->{self::CI_FIELD_TYPE} = $type;
        if ($id != "" ) $CI->{self::CI_FIELD_ID} = $id;

        foreach($extraFields as $field_name => $field_value)
            $CI->{$field_name} = $field_value;

        return $CI;
    }

    /**
     * Creates a new CI object with default values
     *
     *
     * @return object   : Created empty CI object
     */
    public static function newDefaultCI() {
        $returned = new stdClass();

        $returned->{self::CI_FIELD_DATAREF} = null;
        $returned->{self::CI_FIELD_DESCRIPTION} = "";
        $returned->{self::CI_FIELD_ENABLED} = true;
        $returned->{self::CI_FIELD_FLAGS} = -1;
        $returned->{self::CI_FIELD_GROUP} = -1;
        $returned->{self::CI_FIELD_ID} = self::createID();
        $returned->{self::CI_FIELD_NAME} = "";
        $returned->{self::CI_FIELD_ORDER} = -1;
        $returned->{self::CI_FIELD_PARAMS} = null;
        $returned->{self::CI_FIELD_PARENTID} = -1;
        $returned->{self::CI_FIELD_PLATFORM} = null;
        $returned->{self::CI_FIELD_STOREDESTINATION} = null;
        $returned->{self::CI_FIELD_TITLE} = "";
        $returned->{self::CI_FIELD_TYPE} = XAPP_TYPE_MIXED;
        $returned->{self::CI_FIELD_UID} = -1;
        $returned->{self::CI_FIELD_VALUE} = "";
        $returned->{self::CI_FIELD_VISIBLE} = true;

        return $returned;
    }

    /**
     * Generates CI ID according to CI_AUTOGEN_ID_PATTERN
     *
     * @return string   :   generated ID
     */
    public static function createID() {
        $generatedID = "";
        foreach( str_split(self::CI_AUTOGEN_ID_PATTERN) as $patt_char)
        {
            switch ($patt_char)
            {
                case "h":
                    $generatedID .= sprintf("%x%x" , rand(0,15) , rand(0,15) );
                break;
                case "-":
                    $generatedID .= $patt_char;
                break;
            }
        }
        return $generatedID;
    }

    /**
     * Set any field value
     *
     * @param $CI_Obj
     * @param $field
     * @param $value
     */
    public static function set(&$CI_Obj,$field,$value) {
        $CI_Obj->{$field} = $value;
    }

    /**
     * Get any field value
     *
     * @param $CI_Obj
     * @param $field
     * @param $value
     */
    public static function get(&$CI_Obj,$field) {
        return $CI_Obj->{$field};
    }

    /**
     * Set the value for the CI Object
     *
     *
     * @param $CI_Obj
     * @param $value
     */
    public static function setValue(&$CI_Obj,$value) {
        self::set($CI_Obj,self::CI_FIELD_VALUE,$value);
    }

    /**
     * Set the value for the CI Object
     *
     *
     * @param $CI_Obj
     * @param $value
     */
    public static function getValue($CI_Obj) {
        return self::get($CI_Obj,self::CI_FIELD_VALUE);
    }

    /**
     *  Finds a CI object from a CI objects array using the CI name
     *
     * @param $CI_Array
     * @param $name
     * @return bool/object :  CI object, or false if not found
     */
    public static function getByName($CI_Array,$name) {
        return self::getBy($CI_Array , self::CI_FIELD_NAME , $name);
    }
    /**
     *  Finds a CI object from a CI objects array using the CI title
     *
     * @param $CI_Array
     * @param $name
     * @return bool/object :  CI object, or false if not found
     */
    public static function getByTitle($CI_Array,$name) {
        return self::getBy($CI_Array , self::CI_FIELD_TITLE , $name);
    }

    /**
     *  Finds a CI object from a CI objects array using the CI ID
     *
     * @param $CI_Array
     * @param $ID
     * @return bool/object :  CI object, or false if not found
     */
    public static function getByID($CI_Array,$ID) {
        return self::getBy($CI_Array , self::CI_FIELD_ID , $ID);
    }

    /**
     *
     * Finds a CI object from a CI objects array using any field
     *
     * @param $CI_Array
     * @param $field
     * @param $value
     * @return bool/object :  CI object, or false if not found
     */
    public static function getBy($CI_Array,$field,$value) {
        foreach($CI_Array as $CI_Obj)
        {
            if ($CI_Obj->{$field} === $value) return $CI_Obj;
        }
        return false;
    }

    public static function getBy2($CI_Array,$field,$value) {


        $CI_Array = (array)$CI_Array;

        foreach($CI_Array as $CI_Obj){

            if(is_object($CI_Obj)){
                if ($CI_Obj->{$field} === $value) return $CI_Obj;
            }else if(is_array($CI_Obj)){
                if ($CI_Obj[$field] === $value){
                    return $CI_Obj;
                }
            }
        }
        return false;
    }

}
?>