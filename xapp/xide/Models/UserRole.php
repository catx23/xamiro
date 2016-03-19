<?php

/**
 * Class XApp_UserRole
 */
class XApp_UserRole extends XApp_Entity {
    /**
     *  Default fields: Name, Permissions
     */

    const USER_ROLE_NAME = "Name";
    const USER_ROLE_PERMISSIONS = "Permissions";
    const USER_ROLE_RESOURCES = "Resources";
    const USER_ROLE_PARENTS = "Parents";


    static $entity_default_fields = Array(
        Array(
            self::ENTITY_FIELD_NAME => self::USER_ROLE_NAME,
            self::ENTITY_FIELD_DESCRIPTION => "Role name",
            self::ENTITY_FIELD_TYPE => XAPP_TYPE_STRING
        ),

        Array(
            self::ENTITY_FIELD_NAME => self::USER_ROLE_PERMISSIONS,
            self::ENTITY_FIELD_DESCRIPTION => "Role permissions",
            self::ENTITY_FIELD_TYPE => XAPP_TYPE_ARRAY,
            self::ENTITY_FIELD_DEFAULT => Array()
        ),
        Array(
            self::ENTITY_FIELD_NAME => self::USER_ROLE_PARENTS,
            self::ENTITY_FIELD_DESCRIPTION => "Role Parents",
            self::ENTITY_FIELD_TYPE => XAPP_TYPE_ARRAY,
            self::ENTITY_FIELD_DEFAULT => Array()
        ),
        Array(
            self::ENTITY_FIELD_NAME => self::USER_ROLE_RESOURCES,
            self::ENTITY_FIELD_DESCRIPTION => "Role resources",
            self::ENTITY_FIELD_TYPE => XAPP_TYPE_ARRAY,
            self::ENTITY_FIELD_DEFAULT => Array()
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
     * Adds a permission to the role
     *
     *
     * @param $Permission
     */
    public function addPermission($Permission)
    {
        if (is_object($Permission))
            $permissionName = $Permission->getName();
        else
            $permissionName = $Permission;

        $permissions = $this->getPermissions();
        $permissions[] = $permissionName;
        $this->setPermissions( $permissions );
    }

    /**
     *
     * Removes a permission from the role
     *
     * @param $permission
     */
    public function removePermission($permission) {
        if (is_object($permission))
            $permissionName = $permission->getName();
        else
            $permissionName = $permission;

        $permissions = $this->getPermissions();
        foreach($permissions as $n => $permission)
        {
            if ($permission == $permissionName)
            {
                unset( $permissions[$n]);
            }
        }
        $this->setPermissions( $permissions );
    }

    /**
     *  Returns true if the permission is included into the role
     *
     *
     * @param $permissionName   :   name of the permission
     * @return bool             :   the permission is included
     */
    public function isAllowed($permissionName) {
        $allowed = false;

        $permissions = $this->getPermissions();
        foreach($permissions as $permission)
        {
            $allowed = $allowed | ($permission == $permissionName );
        }

        return $allowed;
    }

}

?>