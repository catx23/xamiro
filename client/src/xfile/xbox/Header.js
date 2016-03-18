var sctx=null;
var ctx=null;
var rtConfig="release";
var dataHost ="";
function JSCOMPILER_PRESERVE(){}

/*var xFileConfigMixin =%XFILE_CONFIG_MIXIN%;*/
var xFileConfigMixin ={"LAYOUT_PRESET":1,"PANEL_OPTIONS":{
    "ALLOW_COLUMN_RESIZE":true,
    "ALLOW_COLUMN_REORDER":true,
    "ALLOW_COLUMN_HIDE":true,
    "ALLOW_NEW_TABS":true,
    "ALLOW_MULTI_TAB":false,
    "ALLOW_INFO_VIEW":true,
    "ALLOW_LOG_VIEW":true,
    "ALLOW_BREADCRUMBS":false,
    "ALLOW_CONTEXT_MENU":true,
    "ALLOW_LAYOUT_SELECTOR":true,
    "ALLOW_SOURCE_SELECTOR":true
},"ALLOWED_ACTIONS":[0,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1],"FILE_PANEL_OPTIONS_LEFT":{"LAYOUT":2,"AUTO_OPEN":"true"},"FILE_PANEL_OPTIONS_MAIN":{"LAYOUT":2,"AUTO_OPEN":"true"},"FILE_PANEL_OPTIONS_RIGHT":{"LAYOUT":2,"AUTO_OPEN":"true"}};

var xFileConfig={
    permissions:%permissions%,
    serviceUrl:'%RPC_URL%',
    mixins:[
        {
            declaredClass:'xide.manager.ServerActionBase',
            mixin:{
                serviceUrl:'%RPC_URL%',
                singleton:true
            }
        },
        {
            declaredClass:'xfile.manager.FileManager',
            mixin:{
                serviceUrl:'%RPC_URL%',
                singleton:true
            }
        },
        {
            declaredClass:'xide.manager.SettingsManager',
            mixin:{
                serviceUrl:'%RPC_URL%',
                singleton:true
            }
        },
        {
            declaredClass:'xide.manager.ResourceManager',
            mixin: {
                serviceUrl:'%RPC_URL%',
                singleton: true,
                resourceVariables: %RESOURCE_VARIABLES%
            }
        }
    ],
    FILES_STORE_URL:'%FILES_STORE_URL%',
    THEME_ROOT:'%APP_URL%/themes/',
    WEB_ROOT:'%APP_URL%',
    FILE_SERVICE:'%FILE_SERVICE%',
    FILE_SERVICE_FULL:'%FILE_SERVICE_FULL%',
    REPO_URL:'%REPO_URL%',
    FILES_STORE_SERVICE_CLASS:'XCOM_Directory_Service',
    RPC_PARAMS:{
        rpcUserField:'user',
        rpcUserValue:'%RPC_USER_VALUE%',
        rpcSignatureField:'sig',
        rpcSignatureToken:'%RPC_SIGNATURE_TOKEN%',
        rpcFixedParams:{

        }
    },
    ACTION_TOOLBAR_MODE:'self'
};
var xappPluginResources=%XAPP_PLUGIN_RESOURCES%;