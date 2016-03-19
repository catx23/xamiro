<?php

xapp_import("xapp.Commons.Object");
xapp_import("xapp.xide.Utils.CIUtils");

/**
 * Class XApp_Entity
 */
class XApp_Entity extends  XApp_Object {


    /**
     *  Fields description array components
     *
     */
    const ENTITY_FIELD_NAME = 'name';
    const ENTITY_FIELD_DESCRIPTION = 'description';
    const ENTITY_FIELD_TYPE = 'type';
    const ENTITY_FIELD_DEFAULT = 'default';

    /**
     * Entity ID
     *
     * @var string
     */
    public $id = "";

    /**
     *  All fields from the Entity (CI fields)
     *
     * @var array
     */
    public $Fields = Array();

    /**
     *  Create all the CI objects into Fields using a descriptor array
     *
     * @param array $fields_descriptor    :   an array of fields descriptors
     *
     */
    protected  function _createDefaultFields($fields_descriptor) {
        foreach($fields_descriptor as $field)
        {
            $this->addField($field);

        }
    }

    /**
     *
     *  Create an Entity
     *
     * @param $id
     * @param $fields
     */
    public function create($id,$fields) {
        $this->id=$id;
        $this->Fields=$fields;
    }

    /**
     *  Copy constructor - clone all fields to avoid crossing references
     */
    public function __clone() {
        foreach($this->Fields as $n=>$field)
            $this->Fields[$n] = clone $field;
    }

    /**
     *
     *  Returns the entity ID
     *
     * @return string   :   entity ID
     */
    public function getID() {
        return $this->id;
    }


    /**
     * Adds a field into the entity
     *
     *
     * @param array $field    :   field descriptor: Array( ENTITY_FIELD_NAME, ENTITY_FIELD_DESCRIPTION, ENTITY_FIELD_TYPE, ENTITY_FIELD_DEFAULT)
     */
    public function addField($field) {

        $CI_Obj=XApp_CIUtils::createCI($field[self::ENTITY_FIELD_NAME] ,
            $field[self::ENTITY_FIELD_DESCRIPTION] ,
            $field[self::ENTITY_FIELD_TYPE]);

        if ( isset($field[self::ENTITY_FIELD_DEFAULT]) )
        {
            XApp_CIUtils::setValue($CI_Obj,$field[self::ENTITY_FIELD_DEFAULT]);
        }

        $this->Fields[] = $CI_Obj;
    }

    /**
     *  Adds a custom field into the entity (same functionality as addField)
     *
     *
     * @param array $field_descriptor   :   field descriptor: Array( ENTITY_FIELD_NAME, ENTITY_FIELD_DESCRIPTION, ENTITY_FIELD_TYPE, ENTITY_FIELD_DEFAULT)
     */
    public function addCustomField($field_descriptor) {
        $this->addField($field_descriptor);
    }

    /**
     * Check if a field exists
     *
     * @param $fieldName
     * @return bool
     */
    public function field_exists($fieldName) {
        return (XApp_CIUtils::getByName($this->Fields,$fieldName) !== false);
    }

    /**
     *
     *  Returns all the fields from the entity (CI array)
     *
     * @return array    :   CI array
     */
    public function getFields() {
        return $this->Fields;
    }

    /**
     * Set all the fields from CI array
     *
     *
     * @param array $fields   :   CI array
     */
    public function setFields($fields)
    {
        $this->Fields=array();

        foreach($fields as $CIfield)
        {
            if (is_object($CIfield))
            {
                $this->Fields[]=$CIfield;
            }
            elseif (is_array($CIfield))
            {
                $newSingle =  XApp_CIUtils::newDefaultCI();
                foreach($CIfield as $CIkey=>$CIvalue)
                {
                    $newSingle->{$CIkey} = $CIvalue;
                }
                $this->Fields[] = $newSingle;
            }
        }
    }

    /**
     * Returns the value from a field
     *
     * @param string $field :   field name
     * @return mixed
     */
    public function get($field) {
        $CI_Obj = XApp_CIUtils::getByName($this->Fields,$field);
        if($CI_Obj) {
            return $CI_Obj->{XApp_CIUtils::CI_FIELD_VALUE};
        }
        return null;
    }

    /**
     * Sets a field value
     *
     * @param string $field  :  field name
     * @param mixed $value   :  field value
     */
    public function set($field,$value) {
        $CI_Obj = XApp_CIUtils::getByName($this->Fields,$field);
        $CI_Obj->{XApp_CIUtils::CI_FIELD_VALUE} = $value;
    }

    /**
     *
     * automatic creation of getters/setters
     *
     * catch set[Field_name]() and get[Field_name]()
     *
     * @param $name
     * @param $args
     * @return mixed
     */
    public function __call($name, $args)
    {
        $get_or_set = substr( $name , 0, 3);
        $field = substr( $name , 3);

        switch ($get_or_set)
        {
            case "get":
                return $this->get($field);
            break;
            case "set":
                if (count($args)>0) {
                    $this->set($field,$args[0]);
                } else {
                    return parent::__call($name,$args);
                }
            break;
            default:
                return parent::__call($name,$args);
        }
    }

}

?>