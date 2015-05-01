var isMaster = false;
var debug=true;
var device=null;
var sctx=null;
var ctx=null;
var cctx=null;
var mctx=null;
var rtConfig="release";
var returnUrl= "";
var dataHost ="";
function JSCOMPILER_PRESERVE(){}

var xFileConfigMixin =%XFILE_CONFIG_MIXIN%;
var xFileConfig={
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