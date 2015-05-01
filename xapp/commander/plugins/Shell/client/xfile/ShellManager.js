define([
    'dojo/_base/declare',
    "dojo/_base/lang",
    'xide/factory',
    'xide/types',
    'xide/utils',
    'xide/manager/ManagerBase',
    './views/ShellView'
], function (declare, lang, factory, types,utils, ManagerBase, ShellView) {

    return declare("Shell.xfile.ShellManager", [ManagerBase],
        {
            mainView: null,
            ctx: null,
            config: null,
            panelManager: null,
            fileManager: null,
            currentItem: null,
            persistent: true,
            cookiePrefix: 'XShell',
            serverClass:'XShell',
            ctorArgs:null,
            currentShell:null,
            shellPanelContainer:null,
            shellView:null,
            autoOpen:false,
            getServer:function(){
                return this.fileManager || xfile.getContext().getFileManager();
            },
            getFileName: function () {
                var url = this.config.REPO_URL + '/' + this.currentItem.path;
                url = url.replace('./', '/');
                return url;
            },
            getMainView: function () {
                return this.mainView || this.panelManager.rootView;
            },
            onItemSelected: function (eventData) {
                if(!eventData || !eventData.item || !eventData.item._S){
                    return;
                }
                this.currentItem = eventData.item;
            },
            _getUrl: function (item) {
                var url = this.config.REPO_URL + '/' + item.path;
                url = url.replace('./', '/');
                return url;
            },

            _createShellView:function(dstContainer){
                var config = {
                    "LAYOUT_PRESET": 1,
                    "PANEL_OPTIONS": {
                        "ALLOW_NEW_TABS": false,
                        "ALLOW_MULTI_TAB": false,
                        "ALLOW_INFO_VIEW": false,
                        "ALLOW_LOG_VIEW": false,
                        "ALLOW_BREADCRUMBS": false,
                        "ALLOW_CONTEXT_MENU": false,
                        "ALLOW_LAYOUT_SELECTOR": false,
                        "ALLOW_SOURCE_SELECTOR": false
                    },
                    "ALLOWED_ACTIONS": [
                        0
                    ],
                    "FILE_PANEL_OPTIONS_LEFT": {
                        "LAYOUT": 2,
                        "AUTO_OPEN": "true"
                    },
                    "FILE_PANEL_OPTIONS_MAIN": {
                        "LAYOUT": 2,
                        "AUTO_OPEN": "true"
                    },
                    "FILE_PANEL_OPTIONS_RIGHT": {
                        "LAYOUT": 2,
                        "AUTO_OPEN": "false"
                    }
                };

                var shellViewArgs ={
                    selected: true,
                    delegate: this,
                    item:this.currentItem,
                    options: {},
                    config: config,
                    cookiePrefix:this.cookiePrefix,
                    parentContainer: dstContainer
                };

                lang.mixin(shellViewArgs,this.ctorArgs);

                var shellView = new ShellView( shellViewArgs , dojo.doc.createElement('div'));
                dstContainer.containerNode.appendChild(shellView.domNode);
                shellView.startup();
                shellView.initWithConfig(config);

                dstContainer['shellView']=shellView;

                return shellView;

            },
            _createShellPanel:function(){
                var mainView = this.getMainView();
                if(mainView.shellPanel){
                    return;
                }

                //
                if(!mainView || !mainView.bottomTabContainer){
                    return;
                }

                if(this.autoOpen){
                    var splitter = mainView.layoutBottom._splitterWidget;
                    if(splitter){

                        switch(splitter.state){
                            case "full":
                                break;
                            case "collapsed":
                            case "closed":
                                splitter.set("state", 'full');
                                break;
                        }
                    }
                }

                //place in bottom tab container for now


                var dstContainer=mainView.bottomTabContainer;

                var _container = new dijit.layout.ContentPane({
                    title:'Shell',
                    closable:false,
                    style:'padding:0px;margin:0px;overflow:hidden;'
                },dojo.doc.createElement('div'));

                dstContainer.addChild(_container);
                dstContainer.selectChild(_container);
                dstContainer.resize();
                mainView.resize();

                this.shellPanelContainer=dstContainer;

                this.shellView=this._createShellView(_container);
                mainView.shellPanel=this.shellView;


            },
            getShellView:function(){
                return this.shellView || this._createShellPanel();
            },
            onMainViewReady: function () {
                var thiz = this;
                var panel = this.getShellView();
            },
            _registerListeners: function () {
                this.inherited(arguments);
                var thiz=this;
                factory.subscribe(types.EVENTS.ITEM_SELECTED, this.onItemSelected, this);
                factory.subscribe(types.EVENTS.ON_MAIN_VIEW_READY, function () {
                    thiz.onMainViewReady();
                }, thiz);

            },
            constructor: function (ctorArgs) {
                this._registerListeners();
                this.ctorArgs=ctorArgs;
            },
            onServerResponse:function(theConsole,data){

                if(theConsole && data && theConsole.owner && theConsole.owner.onServerResponse ){
                    theConsole.owner.onServerResponse(theConsole,data);
                }

                /*
                console.error('server !');
                console.dir(data);
                */
            },
            getCurrentPath:function(){

                if(this.currentItem && this.panelManager){

                    /*if(this.currentItem.name==='..'){
                        return this.currentItem.path;
                    }*/
                    /*var pathItem = this.panelManager.getStore(this.currentItem.mount).getParents(this.currentItem,true)[0];*/
                    var item = this.currentItem;
                    if(this.currentItem.directory===true){
                        item = this.currentItem;
                    }else{
                        item = this.currentItem._S.getParent(this.currentItem);
                    }
                    if(item && item.directory===true){
                        return utils.buildPath(item.mount,item.path,false);
                    }
                }
                return null;
            },
            runBash:function(theConsole,value,cwd){
                var thiz=this;
                var _cb =function(response){

                    thiz.onServerResponse(theConsole,response);
                };
                var _value= this.base64_encode(value);
                this.getServer().callMethodEx(this.serverClass,'run',['sh',_value,cwd],_cb);
            },
            runJavascript:function(theConsole,value){

                var response = eval(value);
                if(response){
                    this.onServerResponse(theConsole,response);
                }

            },

            onConsoleCommand:function(theConsole,value){

                var thiz=this;
                if(theConsole.type==='sh'){
                    value = value.replace(/["'`]/g, "");
                    var dstPath=null
                    if(theConsole.isLinked()){
                        dstPath = this.getCurrentPath();
                    }
                    return this.runBash(theConsole,value,dstPath);
                }
                if(theConsole.type==='js'){
                    return this.runJavascript(theConsole,value);
                }
            },
            base64_encode:function(data) {
                // From: http://phpjs.org/functions
                // +   original by: Tyler Akins (http://rumkin.com)
                // +   improved by: Bayron Guevara
                // +   improved by: Thunder.m
                // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
                // +   bugfixed by: Pellentesque Malesuada
                // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
                // +   improved by: Rafał Kukawski (http://kukawski.pl)
                // *     example 1: base64_encode('Kevin van Zonneveld');
                // *     returns 1: 'S2V2aW4gdmFuIFpvbm5ldmVsZA=='
                // mozilla has this native
                // - but breaks in 2.0.0.12!
                //if (typeof this.window.btoa === 'function') {
                //    return btoa(data);
                //}
                var b64 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=';
                var o1, o2, o3, h1, h2, h3, h4, bits, i = 0,
                    ac = 0,
                    enc = '',
                    tmp_arr = [];

                if (!data) {
                    return data;
                }

                do { // pack three octets into four hexets
                    o1 = data.charCodeAt(i++);
                    o2 = data.charCodeAt(i++);
                    o3 = data.charCodeAt(i++);

                    bits = o1 << 16 | o2 << 8 | o3;

                    h1 = bits >> 18 & 0x3f;
                    h2 = bits >> 12 & 0x3f;
                    h3 = bits >> 6 & 0x3f;
                    h4 = bits & 0x3f;

                    // use hexets to index into b64, and append result to encoded string
                    tmp_arr[ac++] = b64.charAt(h1) + b64.charAt(h2) + b64.charAt(h3) + b64.charAt(h4);
                } while (i < data.length);

                enc = tmp_arr.join('');

                var r = data.length % 3;

                return (r ? enc.slice(0, r - 3) : enc) + '==='.slice(r || 3);

            }
        });
});