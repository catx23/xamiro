<?php
/**
 * @author     Guenter Baumgart
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 * @license : http://opensource.org/licenses/BSD-3-Clause
 * @package XApp\xide\Models
 */
xapp_import('xapp.Commons.Entity');
xapp_import('xapp.Security.IResource');
/**
 * Class XApp_User
 */
class XApp_Resource extends XApp_Entity implements XApp_Security_IResource {

    /**
     *  Default fields: Name, Password, Role, Permissions
     */

    const RESOURCE_NAME = "Name";
    const RESOURCE_PARENT = "Parent";
    const RESOURCE_PERMISSIONS = "Permissions";

    static $entity_default_fields = Array(
        Array(
            self::ENTITY_FIELD_NAME => self::RESOURCE_NAME,
            self::ENTITY_FIELD_DESCRIPTION => "Resource Name",
            self::ENTITY_FIELD_TYPE => XAPP_TYPE_STRING
        ),
        Array(
            self::ENTITY_FIELD_NAME => self::RESOURCE_PARENT,
            self::ENTITY_FIELD_DESCRIPTION => "Resource Parent",
            self::ENTITY_FIELD_TYPE => XAPP_TYPE_STRING
        ),
        Array(
            self::ENTITY_FIELD_NAME => self::RESOURCE_PERMISSIONS,
            self::ENTITY_FIELD_DESCRIPTION => "Resource Permissions",
            self::ENTITY_FIELD_TYPE => XAPP_TYPE_STRING
        )
    );

    /**
     *  Initialize all default fields
     */
    public function __construct()
    {
        self::_createDefaultFields(self::$entity_default_fields);
    }

    /**
     * XApp_Security_IResource impl.
     * Returns a string identifier of the Resource.
     * @return string
     */
    function getResourceId(){
        return $this->getName();
    }
}
