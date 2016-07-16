<?php
/**
 * @version 0.1.0
 *
 * @author https://github.com/mc007
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 * @package XApp\Directory
 */

xapp_import('xapp.Service');
xapp_import('xapp.xide.Models.Workbench');
xapp_import('xapp.xide.Models.WorkbenchState');
xapp_import('xapp.Utils.JSONUtils');

/***
 * Class XIDE_Directory_Service extends the standard directory service. Its not wrapping a another class at the Moment
 */
class XApp_XIDE_Workbench_Service extends XApp_Service
{

    /***
     * Path to the site's default meta data. See ./siteconfig
     * @TODO : check how to remove this data
     */
    const SITE_CONFIG_DIRECTORY         = 'XIDE_WORKBENCH_SITE_CONFIG_DIRECTORY';

    /***
     * Path to the current users workbench directory
     */
    const WORKBENCH_DIRECTORY             = 'XIDE_WORKBENCH_DIRECTORY';

    /***
     * The user instance
     */
    const WORKBENCH_USER                 = 'XAPP_USER';

    /**
     * options dictionary for this class containing all data type values
     *
     * @var array
     */
    public static $optionsDict = array
    (
        self::SITE_CONFIG_DIRECTORY     =>  XAPP_TYPE_STRING,
        self::WORKBENCH_DIRECTORY       =>  XAPP_TYPE_STRING,
        self::WORKBENCH_USER            =>  XAPP_TYPE_OBJECT
    );
    /**
     * options mandatory map for this class contains all mandatory values
     *
     * @var array
     */
    public static $optionsRule = array
    (
        self::SITE_CONFIG_DIRECTORY     => 1,
        self::WORKBENCH_DIRECTORY       => 1,
        self::WORKBENCH_USER            => 1

    );
    /**
     * options default value array containing all class option default values
     *
     * @var array
     */
    public $options = array
    (
        self::SITE_CONFIG_DIRECTORY     => null,
        self::WORKBENCH_DIRECTORY       => null,
        self::WORKBENCH_USER            => null
    );

    /***
     * the actual 'workbench'
     */
    protected $workbench=null;

    /***
     * Default load call, invoked by the bootstrapper
     */
    public function load(){}

    /***
     * Return entire local workbench instance
     */
    public function getInfo($dummy){
        $this->init();
        /***
         * get the users workbench settings
         */
        $wbSettingsPath  = xo_get(self::WORKBENCH_DIRECTORY,$this) . DIRECTORY_SEPARATOR . 'settings.json';
        if(file_exists($wbSettingsPath)){
            $this->workbench->workbenchState=$this->getWorkbenchState($wbSettingsPath);
        }else{
            error_log('wbSettings path invalid ' . $wbSettingsPath);
        }
        return $this->workbench;
    }

    /***
     * @param $data | XApp_XIDE_WorkbenchState
     */
    public function setState($data){

        $this->init();
        /***
         * get the users workbench settings
         */
        $wbSettingsPath  = xo_get(self::WORKBENCH_DIRECTORY,$this) . DIRECTORY_SEPARATOR . 'settings.json';

        XApp_Utils_JSONUtils::write_json($wbSettingsPath, $data,'json',true);


    }
    /***
     * Default init call, invoked by the bootstrapper
     */
    public function init(){

        if($this->workbench==null){

            $siteConfigPath = xo_get(self::SITE_CONFIG_DIRECTORY,$this) . DIRECTORY_SEPARATOR;
            $this->workbench =  $this->createWorkbench($siteConfigPath);
        }

        if($this->workbench->userInfo==null){

            $this->workbench->userInfo = $this->createUserInfo(xo_get(self::WORKBENCH_USER,$this));
        }
    }


    /**
     * @param $path
     * @return object|XApp_XIDE_WorkbenchState
     */
    public function getWorkbenchState($path){
        xapp_import('xapp.Commons.ObjectMixin');
        $wbstateData = (object)XApp_Utils_JSONUtils::read_json($path,'json',false,true);
        $wbStateInstance = new XApp_XIDE_WorkbenchState();
        $wbStateInstance = (object) XApp_ObjectMixin::merge($wbStateInstance,$wbstateData);
        return $wbStateInstance;
    }


    /***
     * Create user info transfers the given xapp user to Maqetta specific data
     * @param $xappUser
     * @return array
     * @TODO : XApp_User : id,email
     */
    private function createUserInfo($xappUser){

        $result = array();

        $result['email']='noemail@noemail.com';
        $result['isLocalInstall']=false;
        $result['userDisplayName']=$xappUser->getName();
        $result['userId']='A';

        return $result;
    }

    /***
     * Creates an instance with all the static data
     * @param $siteConfigPath
     * @return XApp_XIDE_Workbench
     */
    private function createWorkbench($siteConfigPath){

        $workbench = new XApp_XIDE_Workbench();

        //theme sets
        //$workbench->themeDefaultSet = XApp_Utils_JSONUtils::read_json($siteConfigPath . 'defaultThemeSet.json' ,'json',false,true);

        //widget palette
        //$workbench->widgetPalette   = XApp_Utils_JSONUtils::read_json($siteConfigPath . 'widgetPalette.json' ,'json',false,true);

        //dojo Options
        //$workbench->dojoOptions     = XApp_Utils_JSONUtils::read_json($siteConfigPath . 'dojoOptions.json' ,'json',false,true);

        return $workbench;
    }

    public function createProject($name='',$projectToClone='',$eclipseSupport=false,$projectTemplate=''){
        error_log('create project : ' . $name . ' clone from :  '  . $projectToClone . ' | is eclipse' . ' | ' . $projectTemplate);
    }


}