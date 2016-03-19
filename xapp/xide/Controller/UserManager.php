<?php
/**
 * @version 0.1.0
 *
 * @author Luis Ramos
 * @author Guenter Baumgart
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 * @package XApp\xide\Controller
 */
xapp_import("xapp.Commons.Entity");
xapp_import("xapp.Commons.Exceptions");
xapp_import('xapp.xide.Models.User');
xapp_import('xapp.xide.Models.UserRole');
xapp_import('xapp.xide.Models.UserPermission');
xapp_import('xapp.xide.Models.Resource');
xapp_import('xapp.Security.IAuthenticator');
xapp_import('xapp.Utils.Strings');
xapp_import('xapp.Http.*');
/***
 * Class XApp_UserManager
 */
class XApp_UserManager implements XApp_Security_IAuthenticator {


    ///////////////////////////////////////////////////////////////////////////
    //
    //  Available Hooks
    //
    ///////////////////////////////////////////////////////////////////////////

    /***
     * Hook Data Init: triggers on initWithData
     *
     * Params: XApp_User_set | XApp_UserRole_set | XApp_UserPermission_set
    */
    const HOOK_DATA_INIT = "XAPP_USER_HOOK_DATA_INIT";
    /**
     * Hook Save: triggers on permanent storage save
     *
     * Params: container -> and object with Users, Roles and Permissions
     */
    const HOOK_SAVE = "XAPP_USER_HOOK_SAVE";
    /**
     *  Hook Login: triggers on user login
     *
     * Params: userName, instance
     */
    const HOOK_LOGIN = "XAPP_USER_HOOK_LOGIN";
    /**
     *  Hook Logout: triggers on logout
     *
     *  Params: instance
     */
    const HOOK_LOGOUT = "XAPP_USER_HOOK_LOGOUT";
    /**
     *  Hook Create User: triggers on user creation and copy
     *
     * Params: userName: new user name, instance
     */
    const HOOK_CREATE_USER = "XAPP_USER_HOOK_CREATE_USER";
    /**
     *  Hook Create Resource: triggers on resource creation and copy
     *
     * Params: userName: new user name, instance
     */
    const HOOK_CREATE_RESOURCE = "XAPP_USER_HOOK_CREATE_RESOURCE";
    /**
     *  Hook Remove Resource: triggers on user removal
     *
     * Params: resource: removed resource, instance
     */
    const HOOK_REMOVE_RESOURCE = "XAPP_USER_HOOK_REMOVE_RESOURCE";
    /**
     *  Hook Remove User: triggers on user removal
     *
     * Params: userName: removed user name, instance
     */
    const HOOK_REMOVE_USER = "XAPP_USER_HOOK_REMOVE_USER";
    /**
     *  Hook RPC before call: triggers before RPC call
     *
     * Params: instance
     */
    const HOOK_RPC_BEFORE_CALL = "XAPP_USER_HOOK_RPC_BEFORE_CALL";
    /**
     *  Hook RPC after call: triggers after RPC call
     *
     * Params: instance
     */
    const HOOK_RPC_AFTER_CALL = "XAPP_USER_HOOK_RPC_AFTER_CALL";

    ///////////////////////////////////////////////////////////////////////////
    //
    //  Available Options, keyed as constants
    //
    ///////////////////////////////////////////////////////////////////////////

    /**
     *  Name of the class to use for creating a user
     */
    const USER_CLASS                = "XAPP_USER_CLASS";

    /**
     *  Name of the class to use for creating a user role
     */
    const USER_ROLE_CLASS           = "XAPP_USER_ROLE_CLASS";

    /**
     *  Name of the class to use for creating a user permission
     */
    const USER_PERMISSION_CLASS     = "XAPP_USER_PERMISSION_CLASS";

    /**
     *  Name of the class to use for creating a resource
     */
    const RESOURCE_CLASS            = "XAPP_RESOURCE_CLASS";

    /**
     *  Name of the class for persistence, JSON only at the moment.
     */
    const STORE_CLASS               = "XAPP_USER_STORE_CLASS";

    /**
     *  Options for the store class
     */
    const STORE_CONF                = "XAPP_USER_STORE_CONF";

    /**
     *  User data, will skip creation of the store class as we have the data already
     */
    const STORE_DATA                = "XAPP_USER_STORE_DATA";

    ///////////////////////////////////////////////////////////////////////////
    //
    //  XApp option for this class
    //
    ///////////////////////////////////////////////////////////////////////////

    /**
     * options dictionary for this class containing all data type values
     *
     * @var array
     */
    public static $optionsDict = array
    (
        self::STORE_CLASS            => XAPP_TYPE_STRING,
        self::STORE_CONF             => XAPP_TYPE_ARRAY,
        self::STORE_DATA             => XAPP_TYPE_OBJECT,
        self::USER_CLASS             => XAPP_TYPE_STRING,
        self::USER_ROLE_CLASS        => XAPP_TYPE_STRING,
        self::USER_PERMISSION_CLASS  => XAPP_TYPE_STRING,
        self::RESOURCE_CLASS         => XAPP_TYPE_STRING
    );

    /**
     * options mandatory map for this class contains all mandatory values
     *
     * @var array
     */
    public static $optionsRule = array
    (
        self::STORE_CLASS            => 0,
        self::STORE_CONF             => 0,
        self::STORE_DATA             => 0,
        self::USER_CLASS             => 1,
        self::USER_ROLE_CLASS        => 1,
        self::USER_PERMISSION_CLASS  => 1,
        self::RESOURCE_CLASS         => 1
    );
    /**
     * options default value array containing all class option default values
     *
     * @var array
     */
    public $options = array
    (
        self::STORE_CLASS            => 'XApp_Store_JSON',
        self::STORE_CONF             => null,
        self::STORE_DATA             => null,
        self::USER_CLASS             => 'XApp_User',
        self::USER_ROLE_CLASS        => 'XApp_UserRole',
        self::USER_PERMISSION_CLASS  => 'XApp_UserPermission',
        self::RESOURCE_CLASS         => 'XApp_Resource'
    );

    const PASSWORD_MAX_LENGTH = 4096;

    ///////////////////////////////////////////////////////////////////////////
    //
    //  Class attributes/members
    //
    ///////////////////////////////////////////////////////////////////////////
    /**
     * @var stdClass | 1:n XApp_User
     */
    protected $_users;
    /***
     * @var stdClass | 1:n XApp_UserRole
     */
    protected $_roles;
    /***
     * @var stdClass | 1:n XApp_UserPermission
     */
    protected $_permissions;
    /***
     * @var stdClass | 1:n XApp_Resource
     */
    protected $_resources;
    /***
     * @var stdClass | XApp_Store_JSON
     */
    protected $_store = null;
    /**
     * @var XApp_Security_User
     */
    protected $loggedInUser = null;
    /***
     * @var XApp_Http_Session
     */
    protected $session = null;
    /**
     * @var XApp_Security_Permission
     */
    protected $acl = null;

    ///////////////////////////////////////////////////////////////////////////
    //
    //  Main entry points
    //
    ///////////////////////////////////////////////////////////////////////////

    /***
     *
     * @param null $options
     */
    public function __construct($options = null)
    {
        xapp_set_options($options, $this);

        $this->_users = new stdClass();
        $this->_roles = new stdClass();
        $this->_permissions = new stdClass();
        $this->_resources = new stdClass();
    }

    /**
     * Initialize user data
     *
     * @param $allUserData  :   object with Users, Roles and Permissions
     */
    public function initWithData($allUserData)
    {
        if(json_encode($allUserData) ==='{}'){
            error_log('have no user data ');
        }

        if (isset($allUserData->Users))
        {
            $this->_initSet($this->_users,xo_get(self::USER_CLASS),$allUserData->Users);
        }

        if (isset($allUserData->Roles))
        {
            $this->_initSet($this->_roles,xo_get(self::USER_ROLE_CLASS),$allUserData->Roles);
        }

        if (isset($allUserData->Permissions))
        {
            $this->_initSet($this->_permissions,xo_get(self::USER_PERMISSION_CLASS),$allUserData->Permissions);
        }

        if (isset($allUserData->Resources))
        {
            $this->_initSet($this->_resources,xo_get(self::RESOURCE_CLASS),$allUserData->Resources);
        }

    }
    /***
     * Initializes the store and loads the data
     */
    public function init(){
        /*if(!$this->_users) {*/
            if ($this->initStore()) {
                $data = (object)$this->_store->read();
                $this->initWithData($data);
            }
        /*}*/
    }

    /**
     * @param $set
     * @param $class
     * @param $Data
     */
    private function _initSet(&$set,$class,$Data) {

        xapp_import('xapp.Xapp.Hook');
        foreach($Data as $fObj)
        {
            $Obj =  new $class;
            if (is_array($fObj))
                $Obj->setFields($fObj["Fields"]);
            else
                $Obj->setFields($fObj->Fields);

            $set->{$Obj->getName()} = $Obj;

            //error_log('did create instance of : ' .$class.'_set');
        }


        // Trigger HOOK_DATA_INIT
        $hook_ret = Xapp_Hook::trigger(self::HOOK_DATA_INIT,array($class.'_set'=>$set));
        $set = $hook_ret[$class.'_set'];
    }



    /**
     * Saves all users, roles and permissions data into the JSON storage
     */
    public function save() {

        if (isset($this->_store))
        {
            $container = new StdClass();

            $container->Users = $this->_users;
            $container->Roles = $this->_roles;
            $container->Permissions = $this->_permissions;
            $container->Resources = $this->_resources;

            // Trigger HOOK_SAVE
            $hook_ret = Xapp_Hook::trigger(self::HOOK_SAVE,array('container'=>$container));
            $container = $hook_ret['container'];

            $this->_store->write($container);
        }

    }

    private $userStorage = null;

    /////////////////////////////////////////////////////////////////////////////////////////
    //
    //  HTTP session authentication
    //
    ////////////////////////////////////////////////////////////////////////////////////////

    /**
     * @param null $ns
     */
    public function initSessionStorage($url=null) {

        xapp_import('xapp.Security.SimpleAuthorizator');
        xapp_import('xapp.Security.IIdentity');

        if(!$url){
            xapp_import('xapp.Service.Utils');
            $url = XApp_Service_Utils::getUrl();
        }

        $sessionOptions = array(
            // security
            'referer_check' => '',    // must be disabled because PHP implementation is invalid
            'use_cookies' => 1,       // must be enabled to prevent Session Hijacking and Fixation
            'use_only_cookies' => 1,  // must be enabled to prevent Session Fixation
            'use_trans_sid' => 0,     // must be disabled to prevent Session Hijacking and Fixation

            // cookies
            'cookie_lifetime' => 0,   // until the browser is closed
            'cookie_path' => '/',     // cookie is available within the entire domain
            'cookie_domain' => '',    // cookie is available on current subdomain only
            'cookie_secure' => FALSE, // cookie is available on HTTP & HTTPS
            'cookie_httponly' => TRUE,// must be enabled to prevent Session Hijacking

            // other
            'gc_maxlifetime' => XApp_Http_Session::DEFAULT_FILE_LIFETIME,// 3 hours
            'cache_limiter' => NULL,  // (default "nocache", special value "\0")
            'cache_expire' => NULL,   // (default "180")
            'hash_function' => NULL,  // (default "0", means MD5)
            'hash_bits_per_character' => NULL // (default "4")

        );

        //'name' => preg_replace('/\d+/u', '', md5(parse_url($url, PHP_URL_PATH)))

        //xapp_clog('session name : ' .parse_url($url, PHP_URL_PATH));

        //$session = XApp_Http_Session::factory($sessionOptions);
        $session = XApp_Http_Session::factory(null);

        $session->setExpiration(10000);

        $this->session = $session;
        $userStorage = new XApp_Http_UserStorage($session);
        if($url) {
            //$userStorage->setNamespace(parse_url($url, PHP_URL_PATH));
        }




        $authorizator = new XApp_Security_SimpleAuthorizator();
        $user = $this->getUser();
        $section = $session->getSection('user');
        /*
        if($section){
            //error_log('have section');
            $user2 = $section->user;
            if($user2){
                //error_log('have user2');
            }
        }else{
            //error_log('have no section');
        }

        error_log('init session storage');

        */

        //now construct the user, needs a storage, and an Authenticator(could be from CMS)
        $this->loggedInUser = new XApp_Security_User($userStorage,$this,$authorizator,$user);
        $this->loggedInUser->setExpiration(10000,false);
        if($user){

            $id = $user->get('Name');
            $pass = $user->get('Password');

            try {

                $this->loggedInUser->login($id, $pass);

            }catch(Exception $e){
                //$this->loggedInUser->logout(true);
                xapp_clog('error creating session : ' . $e->getMessage() . ' logging out');
                //error_log('error creating session : ' . $e->getMessage() . ' logging out');
            }

            if($this->loggedInUser->isLoggedIn()){
                $this->initAcl();
            }
        }else{
            xapp_clog('error creating logged on user = user is not in session!');
           // error_log('error creating logged on user = user is not in session!');
        }

        //error_log('session id ' . session_id());
    }

    /**
     *  Re-init session storage (if set)
     */
    public function refreshSessionStorage() {
        //error_log('refreshSessionStorage');
        if ( $this->loggedInUser != null )
        {
            $this->initSessionStorage();
        }
    }

    /**
     * Login an user into the session storage
     *
     * @param $userName
     * @param $password
     * @param $errors   :  array of errors
     * @return bool     :  true if login is successful
     */

    public function login($userName,$password,&$errors=array()) {

        if ($this->loggedInUser == null) {
            $this->initSessionStorage();
        }

        $this->loggedInUser->logout();
        xapp_setup_language_standalone();
        $result =false;
        try {
            // we try to log the user in
            $result = $this->loggedInUser->login($userName, $password);

            // Trigger HOOK_LOGIN
            Xapp_Hook::trigger(self::HOOK_LOGIN,array(
                'userName'=>$userName,
                'instance'=>$this));
            $result = true;

        } catch (XApp_Security_AuthenticationException $e) {
            $errors[] =  XAPP_TEXT_FORMATTED('LOGIN_ERROR',array($userName , $e->getMessage() ));
            xapp_clog('crash : '.$e->getMessage());

        }

        if($result===true){

            $user = $this->getUser($userName);
            $section = $this->getSession()->getSection('user');

            $section->user = $user;
            $loggedInUser = $this->getLoggedInUser();
            $loggedInUser->setFields($user->getFields());

            //$this->initAcl();
            $section->setExpiration(10000);

        }else{
            //flush session variable
            $section = $this->getSession()->getSection('user');
            xapp_clog('flush session');
            error_log('flush');
            $section->user = null;
        }
        return $result;
    }



    /**
     * Logout user from session storage
     *
     * @return mixed
     */
    public function logout() {

        // Trigger HOOK_LOGOUT
        Xapp_Hook::trigger(self::HOOK_LOGOUT,array('instance'=>$this));
        return $this->loggedInUser->logout();
    }

    /**
     * Check if any user is logged
     * @return bool
     */
    public function isLoggedIn() {
        return $this->loggedInUser->isLoggedIn();
    }

    /**
     * returns logged user name
     *
     * @return bool|string  :   user name if success, false if not
     */
    public function loggedUserName() {
        if ($this->loggedInUser->isLoggedIn())
        {
            return $this->loggedInUser->getId();
        }
        else
        {
            return false;
        }
    }

    /**
     * @return XApp_Security_User
     */
    public function getLoggedInUser(){
        return $this->loggedInUser;
    }

    /////////////////////////////////////////////////////////////////////////////////////////
    //
    //  Model related
    //
    ////////////////////////////////////////////////////////////////////////////////////////


    /**
     * Adds a new ACL resources
     *
     * @param $name
     * @param $parent
     * @return mixed
     */
    public function createResource($name,$parent=null,$fields=Array())
    {
        $resource = xo_as_instance(self::RESOURCE_CLASS,$this,null,null);// XApp_Resource();
        $resource->setName($name);
        $resource->setParent($parent);

        foreach($fields as $field)
        {
            $resource->addCustomField($field);
        }

        $this->_resources->{$resource->getName()} = $resource;

        $this->save();

        // Trigger HOOK_CREATE_USER
        Xapp_Hook::trigger(self::HOOK_CREATE_RESOURCE,array(
                'resource'=>$resource,
                'instance'=>$this)
        );

        return $this->_resources->{$resource->getName()};
    }
    /**
     * Removes the resource from memory, permanent storage (if set) and session storage (if set)
     *
     * @param $resourceName
     */
    public function removeResource($resourceName) {
        if (isset($this->_users->{$resourceName})) {

            // Trigger HOOK_REMOVE_USER
            Xapp_Hook::trigger(self::HOOK_REMOVE_RESOURCE,array(
                'resource'=>$this->getUser($resourceName),
                'instance'=>$this));

            unset($this->_resources->{$resourceName});

            $this->save();
            // re-init session storage if initialized, removing the user from the authenticator
            $this->refreshSessionStorage();
        }
    }

    /**
     * Adds a new user
     *
     * @param $name
     * @param $password
     * @param array $roles
     * @param array $permissions
     * @param array $fields
     * @return mixed
     */
    public function createUser($name,$password,$roles=Array(),$permissions=Array(),$fields=Array())
    {
        $User = xo_as_instance(self::USER_CLASS,$this,null);// XApp_User();
        $User->setName($name);
        $User->setPassword($password);

        foreach ($roles as $role)
        {
            $User->addRole($role);
        }

        foreach($permissions as $permission)
        {
            $User->addPermission($permission);
        }

        foreach($fields as $field)
        {
            $User->addCustomField($field);
        }

        $this->_users->{$User->getName()} = $User;

        $this->save();

        // Trigger HOOK_CREATE_USER
        Xapp_Hook::trigger(self::HOOK_CREATE_USER,array(
            'user'=>$User,
            'userName'=>$User->getName(),
            'instance'=>$this)
        );

        return $this->_users->{$User->getName()};
    }

    /**
     * Creates a new user coping all roles and permissions from $referenceUserName
     *
     * @param $referenceUserName    :   existing user to be used as reference for roles and permissions
     * @param $newUserName          :   new user name
     * @param $newUserPassword      :   new user password
     * @return bool|object          :   false if error, User object if success
     */
    public function copyUser($referenceUserName,$newUserName,$newUserPassword) {
        if (isset($this->_users->{$referenceUserName}))
        {
            $User = clone $this->_users->{$referenceUserName};
            $User->setName($newUserName);
            $User->setPassword($newUserPassword);
            $this->_users->{$User->getName()} = $User;
            $this->save();

            // re-init session storage if initialized, adding the new user into the authenticator
            $this->refreshSessionStorage();

            // Trigger HOOK_CREATE_USER
            Xapp_Hook::trigger(self::HOOK_CREATE_USER,array(
                'user'=>$this->getUser($newUserName),
                'userName'=>$User->getName(),
                'instance'=>$this));

            return $this->_users->{$User->getName()};
        }
        else
        {
            return false;
        }
    }

    /**
     * Removes the user from memory, permanent storage (if set) and session storage (if set)
     *
     * @param $userName
     */
    public function removeUser($userName) {
        if (isset($this->_users->{$userName})) {

            // Trigger HOOK_REMOVE_USER
            Xapp_Hook::trigger(self::HOOK_REMOVE_USER,array(
                'user'=>$this->getUser($userName),
                'userName'=>$userName,
                'instance'=>$this));

            unset($this->_users->{$userName});


            $this->save();
            // re-init session storage if initialized, removing the user from the authenticator
            $this->refreshSessionStorage();
        }
    }

    /**
     * Creates a new permission
     *
     * @param $name
     * @param null $resourceID
     * @return mixed
     */
    public function createPermission($name,$resourceID = null)
    {
        $Permission = xo_as_instance(self::USER_PERMISSION_CLASS,$this,null);//XApp_UserPermission();
        $Permission->setName( $name );
        $Permission->setResourceID( $resourceID );

        $this->_permissions->{$name} = $Permission;

        $this->save();

        return $this->_permissions->{$name};
    }

    /**
     * Removes a permission from memory and permanent storage (if set)
     *
     * @param $name
     */
    public function removePermission($name) {
        if (isset($this->_permissions->{$name}))
        {
            unset($this->_permissions->{$name});
            $this->save();
        }
    }

    /**
     * Creates a new Role
     *
     * @param $name
     * @param array $permissions
     * @return mixed
     */
    public function createRole($name, $permissions = Array() )
    {
        $Role = xo_as_instance(self::USER_ROLE_CLASS,$this,null);//XApp_UserRole();
        $Role->setName( $name );
        $Role->setPermissions( $permissions );

        $this->_roles->{$name} = $Role;

        $this->save();

        return $this->_roles->{$name};
    }

    /**
     * Removes a role from memory and permanent storage (if set)
     *
     * @param $name
     */
    public function removeRole($name) {
        if (isset($this->_roles->{$name}))
        {
            unset($this->_roles->{$name});
            $this->save();
        }
    }


    /***
     * @param null $userName
     * @return bool|mixed
     */
    public function getUser($userName=null) {

        $session = $this->session;

        if(!$session){
            error_log('have no session');
            return false;
        }

        if(empty($userName)){

            $section = $session->getSection('user');
            $user = $section->user;
            return $user;
        }

        if (isset($this->_users->{$userName}))
        {
            return $this->_users->{$userName};
        }
        else
        {
            error_log('have no users');
            return false;
        }
    }

    /**
     *
     * Check if an user has a permission
     *
     * @param $userName
     * @param $permissionName
     * @return bool|int
     */
    public function UserIsAllowed($userName,$permissionName)
    {
        $allowed = false;
        if (isset($this->_users->{$userName}))
        {
            // first check User additional permissions
            foreach( $this->_users->{$userName}->getPermissions() as $permission)
            {
                $allowed = $allowed | ( $permission == $permissionName );
            }

            // if not, check roles
            if (!$allowed)
            {
                foreach( $this->_users->{$userName}->getRole() as $role)
                {
                    if (isset($this->_roles->{$role}))
                    {
                        $allowed = $allowed | $this->_roles->{$role}->isAllowed($permissionName);
                    }

                }
            }
        }

        return $allowed;
    }

    /*****************
     *   wraps for User Model functions, with permanent storage save
     */

    /**
     *
     * Adds a role to the user
     *
     * @param $userName
     * @param $role
     */
    public function UserAddRole($userName,$role) {
        if (isset($this->_users->{$userName}))
        {
            $this->_users->{$userName}->addRole($role);
            $this->save();
        }
    }

    /**
     * Removes a role from the user
     *
     * @param $userName
     * @param $role
     */
    public function UserRemoveRole($userName,$role) {
        if (isset($this->_users->{$userName}))
        {
            $this->_users->{$userName}->removeRole($role);
            $this->save();
        }
    }

    /**
     * Check if user has a role
     *
     *
     * @param $userName
     * @param $role
     * @return mixed
     */
    public function UserHasRole($userName,$role) {
        if (isset($this->_users->{$userName}))
        {
            return $this->_users->{$userName}->hasRole($role);
        }
    }

    /**
     * Adds a permission to the user
     *
     * @param $userName
     * @param $permission
     */
    public function UserAddPermission($userName,$permission) {
        if (isset($this->_users->{$userName}))
        {
            $this->_users->{$userName}->addPermission($permission);
            $this->save();
        }
    }

    /**
     * Removes a permission from the user
     *
     * @param $userName
     * @param $permission
     */
    public function UserRemovePermission($userName,$permission) {
        if (isset($this->_users->{$userName}))
        {
            $this->_users->{$userName}->removePermission($permission);
            $this->save();
        }
    }


    /**
     * Adds a new custom field to a User
     *
     * @param string $userName
     * @param string $fieldName
     * @param string $fieldDescription
     * @param string $fieldType
     * @param string $fieldDefaultValue :   (optional)
     */
    public function AddUserCustomField($userName,$fieldName,$fieldDescription,$fieldType,$fieldDefaultValue="") {
        if (isset($this->_users->{$userName}))
        {
            $descriptor = Array(
                XApp_Entity::ENTITY_FIELD_NAME => $fieldName,
                XApp_Entity::ENTITY_FIELD_DESCRIPTION=> $fieldDescription,
                XApp_Entity::ENTITY_FIELD_TYPE => $fieldType,
                XApp_Entity::ENTITY_FIELD_DEFAULT => $fieldDefaultValue
            );
            $this->_users->{$userName}->addCustomField($descriptor);
            $this->save();
        }
    }
    /*****************
     *   wraps for Role Model functions, with permanent storage save
     */

    /**
     *
     * Adds a permission to the role
     *
     * @param $roleName
     * @param $permission
     */
    public function RoleAddPermission($roleName,$permission) {
        if (isset($this->_roles->{$roleName}))
        {
            $this->_roles->{$roleName}->addPermission($permission);
            $this->save();
        }
    }

    /**
     * Removes a permission from the role
     *
     * @param $roleName
     * @param $permission
     */
    public function RoleRemovePermission($roleName,$permission) {
        if (isset($this->_roles->{$roleName}))
        {
            $this->_roles->{$roleName}->removePermission($permission);
            $this->save();
        }
    }

    /**
     * Returns true if the permission is included into the role
     *
     * @param $roleName
     * @param $permission
     * @return mixed
     */
    public function RoleIsAllowed($roleName,$permission) {
        if (isset($this->_roles->{$roleName}))
        {
            return $this->_roles->{$roleName}->isAllowed($permission);
        }
    }
    /**
     * XApp_Security_IAuthenticator impl.
     * @param array $credentials
     * @return XApp_Security_Identity
     * @throws XApp_Security_AuthenticationException
     */
    function authenticate(array $credentials){
        xapp_import('xapp.Security.SimpleAuthorizator');
        xapp_import('xapp.Security.IIdentity');
        xapp_import('xapp.Security.Identity');
        xapp_import("xapp.Commons.Exceptions");
        xapp_import("xapp.Security.AuthenticationException");

        //prepare user table, from model
        $userList = array();

        foreach( (array) $this->_users as $User)
        {
            $userList[$User->getName()] = $User->getPassword();
        }

        //walk over users and do password match
        list($username, $password) = $credentials;

        foreach ($userList as $name => $pass) {
            if (strcasecmp($name, $username) === 0) {

                //try plain password
                if ((string) $pass === (string) $password) {
                    return new XApp_Security_Identity($name);

                }//try hashed password
                elseif(self::verifyPassword($password, $pass)){
                    return new XApp_Security_Identity($name);
                }else{
                    throw new XApp_Security_AuthenticationException("Invalid password.", self::INVALID_CREDENTIAL);
                }

            }
        }
        throw new XApp_Security_AuthenticationException("User '$username' not found.", self::IDENTITY_NOT_FOUND);

    }

    /////////////////////////////////////////////////////////////////////////////////////////
    //
    //  Utils
    //
    ////////////////////////////////////////////////////////////////////////////////////////
    /**
     * Computes salted password hash.
     * @param  string
     * @return string
     */
    protected  static function hashPassword($password, $options = NULL)
    {
        if ($password === XApp_Utils_Strings::upper($password)) { // perhaps caps lock is on
            $password = XApp_Utils_Strings::lower($password);
        }
        $password = substr($password, 0, self::PASSWORD_MAX_LENGTH);
        $options = $options ?: implode('$', array(
            'algo' => '$2a', // blowfish
            'cost' => '07',
            'salt' => XApp_Utils_Strings::random(22),
        ));
        return crypt($password, $options);
    }

    /**
     * Verifies that a password matches a hash.
     * @return bool
     */
    protected  static function verifyPassword($password, $hash)
    {
        return self::hashPassword($password, $hash) === $hash
        || (PHP_VERSION_ID >= 50307 && substr($hash, 0, 3) === '$2a' && self::hashPassword($password, $tmp = '$2x' . substr($hash, 3)) === $tmp);
    }

    ////////////////////////////////////////////////////////////////////////////////////////
    //
    //  Getters/Setters
    //
    ////////////////////////////////////////////////////////////////////////////////////////
    public function getUsers(){
        return $this->_users;
    }
    public function getPermissions(){
        return $this->_permissions;
    }
    public function getResources(){
        return $this->_resources;
    }
    public function getRoles(){
        return $this->_roles;
    }
    public function getRole($name){

        $roles = $this->_roles;
        if($roles->{$name}){
            return $roles->{$name};
        }
        return null;
    }
    /**
     * @return XApp_Security_Permission
     */
    public function getAcl(){
        if(!$this->acl){
            $this->acl = new XApp_Security_Permission();
        }
        return $this->acl;
    }
    /**
     * @return XApp_Security_User
     */
    public function getSessionStorage(){
        return $this->loggedInUser;
    }
    /***
     * @return XApp_Http_Session
     */
    public function getSession(){
        return $this->session ?:XApp_Http_Session::factory(null);
    }

    ///////////////////////////////////////////////////////////////////////////
    //
    //  Store & persistence related
    //
    ///////////////////////////////////////////////////////////////////////////

    /***
     * Parse options and init the store
     * @param bool $force does force to re-create the store
     * @return bool
     */
    public function initStore($force=false){

        //skip
        if($force === false && $this->_store){
            return true;
        }

        //Try with store data first
        if(xo_has(self::STORE_DATA) && is_object(xo_get(self::STORE_DATA))){
            $this->initWithData(xo_get(self::STORE_DATA));
            return true;
        }

        //No store data provided from outside, use the store class

        if(xo_has(self::STORE_CLASS)){
            $_storeClz = xo_get(self::STORE_CLASS);

            //check its an instance already :
            if(is_object($_storeClz)){
                $this->_store = $_storeClz;
                return true;
            }//its a class name
            elseif(is_string($_storeClz) && class_exists($_storeClz)){
                $_ctrArgs = xo_has(self::STORE_CONF) ? xo_get(self::STORE_CONF) : array();
                $this->_store = new $_storeClz($_ctrArgs);
                return true;
            }
        }
        return false;
    }

    /**
     * @param $name
     * @param $role
     * @param $roles
     * @param $acl
     */
    protected function registerRole($name,$role,$roles,$acl){

        $parents = $role->getParents();
        if($parents){
            //walk over parents and ensure them
            foreach($parents as $parent){

                if($acl->hasRole($parent)){
                    //already registered
                }else{
                    if(isset($roles->{$parent})){
                        $this->registerRole($parent,$roles->{$parent},$roles,$acl);
                    }else{
                        unset($parents,$roles->{$parent});
                    }
                }
            }
            if(!$acl->hasRole($name)) {
                $acl->addRole($name, $parents);
            }
        }else{
            if(!$acl->hasRole($name)) {
                $acl->addRole($name, null);
            }
        }
    }

    /**
     * Return logged on's user permission from acl
     * @param null $resources
     * @param null $user
     * @param null $acl
     * @return array
     */
    public function getUserPermissions($resources=null,$user=null,$acl=null){

        $result = array();


        if(!$user){
            $user = $this->getUser();
        }

        if(!$user){
            return $result;
        }

        if(!$acl){
            $acl = $this->getAcl();
        }

        if(!$resources){
            $resources = $this->getResources();
        }
        $userRoles = $user->getRole();

        foreach ($resources as $resource) {

            //collect all
            $permissions = $resource->getPermissions();
            $name = $resource->getName();
            $result[$name] = new stdClass();

            if ($permissions) {
                //set all true
                foreach ($permissions as $permission) {
                    $result[$name]->{$permission} = true;
                }

                //now to false or true
                foreach ($permissions as $permission) {
                    foreach($userRoles as $role) {
                        $result[$name]->{$permission} = $acl->isAllowed($role,$name,$permission);
                    }
                }
            }
        }
        return $result;

    }

    /**
     * @throws Exception
     */
    public function initAcl(){

        $acl = $this->getAcl();
        $loggedInUser = $this->getLoggedInUser();
        if($loggedInUser){

            $roles = $this->getRoles();
            $resources = $this->getResources();

            //transfer global roles(currently raw!) - data to ACL Roles
            foreach ($roles as $roleName => $roleData) {
                $this->registerRole($roleName,$roleData,$roles,$acl);
            }

            //transfer resource - data (currently raw!)
            foreach ($resources as $resourceName => $resourceData) {
                if(!$acl->hasResource($resourceName)) {
                    $acl->addResource($resourceName);
                }
            }

            foreach ($roles as $roleName => $roleData) {
                $roleResources = $roleData->getResources();
                if ($roleResources) {
                    foreach ($roleResources as $roleResourceName => $roleResourceData) {
                        $allow = $roleResourceData['allow'];
                        $deny = isset($roleResourceData['deny']) ? $roleResourceData['deny'] : null;
                        $resourceName = $roleResourceData['name'];
                        if (isset($resources->{$resourceName})) {
                            if ($deny) {
                                foreach ($allow as $roleResourcePermissionIndex => $roleResourcePermission) {
                                    $acl->deny($roleName, $resourceName, $allow);
                                }
                            }
                            if ($allow) {
                                foreach ($allow as $roleResourcePermissionIndex => $roleResourcePermission) {
                                    $acl->allow($roleName, $resourceName, $allow);
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}