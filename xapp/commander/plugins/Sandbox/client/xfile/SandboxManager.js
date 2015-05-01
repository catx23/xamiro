define([
    'dojo/_base/declare',
    "dojo/_base/lang",
    'xide/factory',
    'xide/types',
    'xide/utils',
    'xide/manager/ManagerBase',
    'xfile/views/RemoteEditor',
    'dojo/cookie',
    'dojox/encoding/digests/MD5',
    './views/UrlParameterDialog',
    './views/Sandbox'

], function (declare,lang, factory, types, utils,ManagerBase, RemoteEditor, cookie, MD5, UrlParameterDialog, Sandbox) {

    return declare("Sandbox.xfile.SandboxManager", [ManagerBase],
        {
            mainView: null,
            ctx: null,
            config: null,
            panelManager: null,
            fileManager: null,
            imageEditView: null,
            currentItem: null,
            imageEditorPane: null,
            editorParentContainer: null,
            featherEditor: null,
            browserPreview: null,
            showUrlParameterDialog: true,
            persistent: true,
            cookiePrefix: 'XSandbox',
            editorPaneTitle: null,
            ctorArgs:null,
            sandboxes:null,
            currentSandbox:null,
            onEditorClose: function (editor) {
                if(editor && editor['sandbox'] && editor['sandbox'].destroy){
                    if(editor['sandbox']==this.currentSandbox){
                        this.currentSandbox=null;
                    }
                    editor.sandbox.destroy();
                }
                //this.editorParentContainer.removeChild(editor);
                this.ctx.getPanelManager().onClosePanel(editor);
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
                url = this.ctx.getFileManager().getImageUrl(item);// utils.cleanUrl(url);
                return url;
            },
            openInCurrentSandbox: function (item) {
                if(this.currentSandbox && this.currentSandbox.addItem){
                    this.editorParentContainer.selectChild(this.currentSandbox.parentContainer);
                    this.currentSandbox.addItem(item);
                }
            },
            openSandbox: function (item) {

                var thiz = this;
                if (this.showUrlParameterDialog) {

                    //raw url
                    var itemUrl = this._getUrl(item);
                    var uriParams = '';
                    this.editorPaneTitle = item.name;

                    //get url parameters from url
                    if (this.persistent) {
                        var _cookie = this.cookiePrefix + '_urlParameter_' + MD5(itemUrl, 1);
                        var settings = cookie(_cookie);
                        if (settings) {
                            uriParams += settings;
                        }
                    }

                    //a delegate when the dialog returns onOk or onCancel
                    var delegate = {
                        delegate: this,
                        onOk: function (dlg) {
                            if (dlg && dlg.getValue) {
                                uriParams = dlg.getValue();
                            }

                            //store as bloody cookie for now
                            if (thiz.persistent && uriParams && uriParams.length > 0) {
                                cookie(
                                    thiz.cookiePrefix + '_urlParameter_' + MD5(itemUrl, 1),//unique cookie
                                    uriParams//cookie value
                                );
                            }
                            //lets rock
                            thiz._openSandbox(itemUrl + uriParams);

                        },
                        onCancel: function () {
                            thiz._openSandbox(itemUrl);
                        }

                    };
                    var result = new UrlParameterDialog({
                        title: "Add your url parameters:",
                        delegate: delegate,
                        urlParams: uriParams,
                        rootUrl: itemUrl,
                        className: "fileOperationView urlParameterDialog",
                        onHide: function () {
                            setTimeout(function () {
                                utils.destroyWidget(this);
                            }, 1000);
                        }
                    });
                    result.show();
                    result.resize();

                } else {
                    return this._openSandbox(item);
                }


            },
            openEditor: function (item) {

                var thiz = this;

                if (this.showUrlParameterDialog) {

                    //raw url
                    var itemUrl = this._getUrl(item);
                    var uriParams = '';
                    this.editorPaneTitle = item.name;

                    //get url parameters from url
                    if (this.persistent) {
                        var _cookie = this.cookiePrefix + '_urlParameter_' + MD5(itemUrl, 1);
                        var settings = cookie(_cookie);
                        if (settings) {
                            uriParams += settings;
                        }
                    }

                    //a delegate when the dialog returns onOk or onCancel
                    var delegate = {
                        delegate: this,
                        onOk: function (dlg) {
                            if (dlg && dlg.getValue) {
                                uriParams = dlg.getValue();
                            }

                            //store as bloody cookie for now
                            if (thiz.persistent && uriParams && uriParams.length > 0) {
                                cookie(
                                    thiz.cookiePrefix + '_urlParameter_' + MD5(itemUrl, 1),//unique cookie
                                    uriParams//cookie value
                                );
                            }
                            //lets rock
                            thiz._openEditor(itemUrl + uriParams);

                        },
                        onCancel: function () {

                        }

                    };
                    var result = new UrlParameterDialog({
                        title: "Add your url parameters:",
                        delegate: delegate,
                        urlParams: uriParams,
                        rootUrl: itemUrl,
                        className: "fileOperationView urlParameterDialog",
                        onHide: function () {
                            setTimeout(function () {
                                utils.destroyWidget(this);
                            }, 1000);
                        }
                    });
                    result.show();
                    result.resize();

                } else {
                    return this._openEditor(item);
                }
            },
            _openSandbox: function (item) {


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
                        0,
                        1,
                        1,
                        1,
                        1,
                        1,
                        1,
                        1,
                        1,
                        1,
                        1,
                        1,
                        1,
                        1,
                        1,
                        1,
                        1,
                        1,
                        1,
                        1
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

                var mainView = this.getMainView();
                if (!mainView) {
                    return;
                }
                var dstContainer = mainView.getNewAlternateTarget();
                if (!dstContainer) {
                    return;
                }
                this.editorParentContainer = dstContainer;

                var browserUrl = lang.isString(item) ? item : this._getUrl(item);

                var thiz = this;
                var _container = new dijit.layout.ContentPane({
                    title: this.editorPaneTitle || item.name,
                    closable: true,
                    style: 'padding:0px;margin:0px',
                    onClose: function () {
                        thiz.onEditorClose(this);
                    },
                    onShow:function(){
                        thiz.currentSandbox=this.sandbox;
                    }
                }, dojo.doc.createElement('div'));

                dstContainer.addChild(_container);
                dstContainer.selectChild(_container);
                dstContainer.resize();
                mainView.resize();

                dstContainer = _container;

                var sandboxArgs ={
                    selected: true,
                    delegate: this,
                    item:this.currentItem,
                    options: {},
                    config: config,
                    frameUrl: browserUrl,
                    editUrl: browserUrl,
                    cookiePrefix:this.cookiePrefix,
                    parentContainer: dstContainer
                };

                lang.mixin(sandboxArgs,this.ctorArgs);

                var sandbox = new Sandbox( sandboxArgs , dojo.doc.createElement('div'));
                dstContainer.containerNode.appendChild(sandbox.domNode);
                sandbox.startup();
                sandbox.initWithConfig(config);
                _container['sandbox']=sandbox;//track sandbox in container, see this.onEditorClose
                this.sandboxes.push(sandbox);
                this.currentSandbox=sandbox;
            },
            _openEditor: function (item) {

                var mainView = this.getMainView();
                if (!mainView) {
                    return;
                }

                var dstContainer = mainView.getNewAlternateTarget();
                if (!dstContainer) {
                    return;
                }
                this.editorParentContainer = dstContainer;


                var browserUrl = lang.isString(item) ? item : this._getUrl(item);

                var thiz = this;
                var _container = new dijit.layout.ContentPane({
                    title: this.editorPaneTitle || item.name,
                    closable: true,
                    tabIndex:-1,
                    style: 'padding:0px;margin:0px;overflow:hidden;',
                    onClose: function () {
                        thiz.onEditorClose(this);
                    }
                }, dojo.doc.createElement('div'));

                dstContainer.addChild(_container);
                dstContainer.selectChild(_container);
                dstContainer = _container;
                var browserPreview = new RemoteEditor({
                    selected: true,
                    delegate: this,
                    options: {},
                    config: this.config,
                    frameUrl: browserUrl,
                    editUrl: browserUrl,
                    tabIndex:-1,
                    parentContainer: dstContainer
                }, dojo.doc.createElement('div'));
                dstContainer.containerNode.appendChild(browserPreview.domNode);
                browserPreview.startup();

                dstContainer.resize();
                mainView.resize();
            },
            openWindow: function (item) {

                var itemUrl = this._getUrl(item);
                window.open(itemUrl);
            },
            onMainViewReady: function () {
                var thiz = this;
                factory.publish(types.EVENTS.REGISTER_EDITOR, {
                    name: 'Sandbox',
                    extensions: 'php|html',
                    onEdit: function () {
                        thiz.openSandbox(thiz.currentItem)
                    },
                    iconClass: 'el-icon-brush',
                    owner: thiz
                }, thiz);


                factory.publish(types.EVENTS.REGISTER_EDITOR, {
                    name: 'Current Sandbox',
                    extensions: 'js|php|html|css|less|cfhtml',
                    onEdit: function () {
                        thiz.openInCurrentSandbox(thiz.currentItem)
                    },
                    iconClass: 'el-icon-brush',
                    owner: thiz
                }, thiz);

                factory.publish(types.EVENTS.REGISTER_EDITOR, {
                    name: 'Browser',
                    extensions: 'php|html|mp4|doc|xls|pdf|zip|tar|iso|avi|gz|mp3|mkv|ogg|png|jpg|cfhtml',
                    onEdit: function () {
                        thiz.openEditor(thiz.currentItem)
                    },
                    iconClass: 'el-icon-globe',
                    owner: thiz
                }, thiz);

                factory.publish(types.EVENTS.REGISTER_EDITOR, {
                    name: 'New Window',
                    extensions: 'php|html|mp4|doc|xls|pdf|zip|tar|iso|avi|gz|mp3|mkv|ogg|png|jpg|cfhtml',
                    onEdit: function () {
                        thiz.openWindow(thiz.currentItem)
                    },
                    iconClass: 'el-icon-globe',
                    owner: thiz
                }, thiz);

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
                this.sandboxes=[];
            }
        });
});