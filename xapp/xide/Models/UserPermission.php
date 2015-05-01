<?php

xapp_import('xapp.Security.Permission');

/**
 * Class XApp_UserPermission
 */
class XApp_UserPermission extends XApp_Entity {

    /**
     *  Default fields: Name
     */
    const USER_PERMISSION_NAME = "Name";

    static $entity_default_fields = Array(
        Array(
            self::ENTITY_FIELD_NAME => self::USER_PERMISSION_NAME,
            self::ENTITY_FIELD_DESCRIPTION => "Permission name",
            self::ENTITY_FIELD_TYPE => XAPP_TYPE_STRING
        )
    );



    /**
     * @var
     */
    protected $resourceID;

    /**
     *  Initialize all default fields
     */
    public function __construct()
    {
        self::_createDefaultFields(self::$entity_default_fields);
    }

    public function setResourceID($resourceID)
    {
        $this->resourceID = $resourceID;
    }


}

?>