<?php
xapp_import('xapp.Security.IAuthorizator');
class XApp_Security_SimpleAuthorizator implements XApp_Security_IAuthorizator
{
    function isAllowed($role, $resource, $privilege)
    {
        error_log('calling is allowed : ' . ' role : ' . $role . ' resource ' . $resource . ' = action = ' . $privilege);
        return true;
    }
}
