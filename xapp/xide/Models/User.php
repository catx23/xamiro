<?php
xapp_import('xapp.Commons.Entity');
xapp_import('xapp.Security.IIdentity');
xapp_import('xapp.Security.User');
/**
 * Class XApp_User
 */
class XApp_User extends XApp_Entity /*extends XApp_Security_User implements XApp_Security_IIdentity*/ {

    /**
     *  Default fields: Name, Password, Role, Permissions
     */

    const USER_NAME = "Name";
    const USER_PASSWORD = "Password";
    const USER_ROLE = "Role";
    const USER_PERMISSIONS = "Permissions";

    static $entity_default_fields = Array(
        Array(
            self::ENTITY_FIELD_NAME => self::USER_NAME,
            self::ENTITY_FIELD_DESCRIPTION => "User name",
            self::ENTITY_FIELD_TYPE => XAPP_TYPE_STRING
        ),
        Array(
            self::ENTITY_FIELD_NAME => self::USER_PASSWORD,
            self::ENTITY_FIELD_DESCRIPTION => "User Password",
            self::ENTITY_FIELD_TYPE => XAPP_TYPE_STRING
        ),
        Array(
            self::ENTITY_FIELD_NAME => self::USER_ROLE,
            self::ENTITY_FIELD_DESCRIPTION => "User Role/s",
            self::ENTITY_FIELD_TYPE => XAPP_TYPE_ARRAY,
            self::ENTITY_FIELD_DEFAULT => Array()
        ),
        Array(
            self::ENTITY_FIELD_NAME => self::USER_PERMISSIONS,
            self::ENTITY_FIELD_DESCRIPTION => "User additional permissions",
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
     * XApp_Security_IIdentity impl.
     * Returns the ID of user.
     * @return mixed
     */
    function getId(){
        return $this->getName();
    }

    /**
     * XApp_Security_IIdentity impl.
     * Returns a list of roles that the user is a member of.
     * @return array
     */
    function getRoles(){
        return $this->getRole();
    }

    /**
     * Adds a role to the user
     *
     *
     * @param $role
     */
    public function addRole($role)
    {
        if (is_object($role))
            $roleName = $role->getName();
        else
            $roleName = $role;

        $roles = $this->getRole();
        $roles[] = $roleName;
        $this->setRole( $roles );
    }

    /**
     *
     * Removes a role from the user
     *
     * @param $role
     */
    public function removeRole($role) {
        if (is_object($role))
            $roleName = $role->getName();
        else
            $roleName = $role;

        $roles = $this->getRole();
        foreach($roles as $n => $role)
        {
            if ($role == $roleName)
            {
                unset( $roles[$n]);
            }
        }
        $this->setRole( $roles );
    }

    /**
     *
     * Check if user has a role
     *
     * @param $role
     * @return bool
     */
    public function hasRole($role) {
        $found = false;

        if (is_object($role))
            $roleName = $role->getName();
        else
            $roleName = $role;

        $roles = $this->getRole();
        foreach($roles as $n => $role)
        {
            $found = $found | ($role == $roleName);
        }

        return $found;
    }

    /**
     * Adds a permission to the user
     *
     *
     * @param $permission
     */
    public function addPermission($permission)
    {
        if (is_object($permission))
            $permissionName = $permission->getName();
        else
            $permissionName = $permission;

        $permissions = $this->getPermissions();
        $permissions[] = $permissionName;
        $this->setPermissions( $permissions );
    }

    /**
     *
     * Removes a permission from the user
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
}
