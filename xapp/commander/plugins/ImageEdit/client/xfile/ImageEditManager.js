define([
    'dojo/_base/declare',
    'xide/utils',
    'xide/types',
    'xide/manager/ManagerBase',
    'xide/layout/ContentPane',
    'xfile/views/RemoteEditor',
    'dojox/encoding/digests/SHA1'

], function (declare,
             utils,
             types,
             ManagerBase,
             ContentPane,
             RemoteEditor,
             SHA1)
{
    return declare("ImageEdit.ImageEditManager", [ManagerBase],
        {
            mainView:null,
            ctx:null,
            config:null,
            panelManager:null,
            fileManager:null,
            imageEditView:null,
            currentItem:null,
            imageEditorPane:null,
            imageEditorPaneContainer:null,
            featherEditor:null,
            didRegister:false,
            onEditorClose:function(editor){
                this.featherEditor=editor;
                if(editor && editor.parentContainer){
                    //editor.parentContainer.removeChild(editor);
                }
                //this.imageEditorPaneContainer.removeChild(this.imageEditorPane);
                //this.ctx.getPanelManager().onClosePanel(editor);
                this.ctx.getPanelManager().onClosePanel(editor);

            },
            onImageSaved:function(newUrl){
                var fileManager = this.fileManager || xfile.getContext().getFileManager();
                if(fileManager){
                    //mark as dirty
                    this.currentItem.dirty=true;
                    fileManager.downloadTo(newUrl,this.currentItem.mount,this.currentItem.path);
                    this.publish(types.EVENTS.ON_FILE_CONTENT_CHANGED,{
                        item:this.currentItem,
                        owner:this
                    });
                }
            },
            getFileName:function(){
                var imageUrl = this.config.REPO_URL + '/' +this.currentItem.path;
                imageUrl=imageUrl.replace('./','/');
                return imageUrl;
            },
            getMainView:function(){
              return this.mainView || this.panelManager.rootView;
            },
            onItemSelected:function(eventData){
                if(!eventData || !eventData.item || !eventData.item._S){
                    return;
                }
                this.currentItem = eventData.item;
            },
            /**
             * Open Aviary instance
             * @param item
             */
            openEditor:function(item){

                var mainView = this.getMainView();
                var thiz=this;
                if(!mainView){
                    return;
                }
                var dstContainer = mainView.getNewAlternateTarget();
                if(!dstContainer){
                    return;
                }
                this.imageEditorPaneContainer=dstContainer;

                var fileManager = this.ctx.getFileManager();
                var config = fileManager.config;
                var imageUrl = fileManager.getImageUrl(item);
                if(imageUrl.indexOf('http')==-1){
                    imageUrl = this.config.REPO_URL + '/' + fileManager.getImageUrl(item);
                }


                var _openEditor = function(imageUrl) {

                    var _container = utils.addWidget(ContentPane, {
                        title: item.name,
                        closable: true,
                        style: 'padding:0px;margin:0px;overflow:hidden;',
                        parentContainer: dstContainer,
                        onClose: function () {
                            thiz.onEditorClose(this);
                        }
                    }, null, dstContainer, true);

                    dstContainer.resize();
                    mainView.resize();
                    dstContainer = _container;

                    thiz.imageEditorPane = _container;

                    if (thiz.imageEditView) {
                        utils.destroyWidget(thiz.imageEditView);
                        thiz.imageEditView = null;
                    }

                    if (!thiz.imageEditView) {
                        console.log('open image editor with image url ' + imageUrl);
                        thiz.imageEditView = new RemoteEditor({
                            selected: true,
                            delegate: thiz,
                            options: {},
                            config: thiz.config,
                            frameUrl: require.toUrl("ImageEdit/xfile/templates/Aviary.html"),
                            editUrl: imageUrl,
                            parentContainer: dstContainer
                        }, dojo.doc.createElement('div'));

                        dstContainer.containerNode.appendChild(thiz.imageEditView.domNode);
                    }
                    thiz.imageEditView.startup();

                };


                if(config.NEEDS_TOKEN==1){

                    var _tokenReady = function(result){

                        imageUrl+='&xfToken='+result;
                        _openEditor(imageUrl);
                    };
                    return fileManager.callMethod('createToken','xfToken',_tokenReady,false);

                }else{
                    _openEditor(imageUrl);
                }


            },
            openPixlrEditor:function(item){

                var mainView = this.getMainView(),
                    thiz=this;

                if(!mainView){
                    return;
                }


                var fileManager = this.ctx.getFileManager();
                var config = fileManager.config;


                var imageUrl = fileManager.getImageUrl(item);
                if(imageUrl.indexOf('http')==-1){
                    imageUrl = this.config.REPO_URL + '/' + fileManager.getImageUrl(item);
                }

                var saveUrl = thiz.config.FILE_SERVICE_FULL;
                var mount = item.mount.replace('/', '');
                if (saveUrl.indexOf('?') == -1) {
                    saveUrl += '?service=XCOM_Directory_Service.fileUpdate&view=smdCall&callback=asdf&mount=' + mount + '&srcPath=' + item.path;
                } else {
                    saveUrl += '&service=XCOM_Directory_Service.fileUpdate&view=smdCall&callback=asdf&mount=' + mount + '&srcPath=' + item.path;
                }

                /**
                 * calc signature
                 * @type {{service: string, mount: *, srcPath: (item.path|*), callback: string, user: (xFileConfig.RPC_PARAMS.rpcUserValue|*|a.RPC_PARAMS.rpcUserValue)}}
                 */
                var aParams = {
                    "service": "XCOM_Directory_Service.fileUpdate",
                    "mount": mount,
                    'srcPath':item.path,
                    "callback":"asdf",
                    "title":item.name,
                    "user":this.config.RPC_PARAMS.rpcUserValue
                };

                var pStr  =  dojo.toJson(aParams);
                var signature = SHA1._hmac(pStr, this.config.RPC_PARAMS.rpcSignatureToken, 1);
                saveUrl+='&' + this.config.RPC_PARAMS.rpcUserField + '=' + this.config.RPC_PARAMS.rpcUserValue;
                saveUrl+='&' + this.config.RPC_PARAMS.rpcSignatureField + '=' + signature;


                var _openEditor = function(imageUrl,saveUrl) {

                    var dstContainer = mainView.getNewAlternateTarget();
                    if(!dstContainer){
                        return;
                    }

                    thiz.imageEditorPaneContainer=dstContainer;

                    var _container = utils.addWidget(ContentPane,{
                        title: item.name,
                        closable: true,
                        style: 'padding:0px;margin:0px;overflow:hidden;',
                        parentContainer:dstContainer,
                        onClose: function () {
                            thiz.onEditorClose(this);
                        }
                    },null, dstContainer,true);
                    dstContainer.resize();
                    mainView.resize();

                    dstContainer = _container;
                    thiz.imageEditorPane = _container;

                    if (thiz.imageEditView) {
                        utils.destroyWidget(thiz.imageEditView);
                        thiz.imageEditView = null;
                    }

                    if (!thiz.imageEditView) {
                        thiz.imageEditView = new RemoteEditor({
                            selected: true,
                            delegate: thiz,
                            options: {},
                            config: thiz.config,
                            frameUrl: require.toUrl("ImageEdit/xfile/templates/Pixlr.html"),
                            editUrl: imageUrl,
                            saveUrl: saveUrl,
                            parentContainer: dstContainer,
                            title: item.name
                        }, dojo.doc.createElement('div'));

                        dstContainer.containerNode.appendChild(thiz.imageEditView.domNode);
                    }
                    thiz.imageEditView.startup();

                };
                if(config.NEEDS_TOKEN==1){

                    var _tokenReady = function(result){

                        imageUrl+='&xfToken='+result;
                        saveUrl+='&xfToken='+result;
                        _openEditor(imageUrl,saveUrl);
                    };
                    return fileManager.callMethod('createToken','xfToken',_tokenReady,false);

                }else{
                    _openEditor(imageUrl,saveUrl);
                }


            },
            openPixie:function(item){

                var mainView = this.getMainView(),
                    thiz=this;

                if(!mainView){
                    return;
                }

                var dstContainer = mainView.getNewAlternateTarget();
                if(!dstContainer){
                    return;
                }
                this.imageEditorPaneContainer=dstContainer;

                var fileManager = this.ctx.getFileManager();
                var config = fileManager.config;


                var imageUrl = fileManager.getImageUrl(item);
                if(imageUrl.indexOf('http')==-1){
                    imageUrl = this.config.REPO_URL + '/' + fileManager.getImageUrl(item);
                }


                var _openEditor = function(imageUrl) {

                    var _container = utils.addWidget(ContentPane, {
                        title: item.name,
                        closable: true,
                        style: 'padding:0px;margin:0px;overflow:hidden;',
                        parentContainer: dstContainer,
                        iconClass:'fa-paint',
                        cssClass:'ui-widget-content',
                        onClose: function () {
                            thiz.onEditorClose(this);
                        }
                    }, null, dstContainer, true);

                    dstContainer.resize();
                    mainView.resize();
                    dstContainer = _container;

                    thiz.imageEditorPane = _container;

                    if (thiz.imageEditView) {
                        utils.destroyWidget(thiz.imageEditView);
                        thiz.imageEditView = null;
                    }

                    if (!thiz.imageEditView) {
                        console.log('open image editor with image url ' + imageUrl);
                        thiz.imageEditView = utils.addWidget(RemoteEditor,{
                            selected: true,
                            delegate: thiz,
                            cssClass:'ui-widget-content',
                            options: {},
                            config: thiz.config,
                            frameUrl: require.toUrl("ImageEdit/xfile/pixie_editor.html"),
                            editUrl: imageUrl,
                            parentContainer: dstContainer,
                            item:item,
                            onMessage:function(message){
                                _container.set('iconClass','fa-spinner fa-spin');
                                var result  = JSON.parse(message);

                                if(result && result.command && result.command==='save'){

                                    fileManager.setContent(item.mount,item.path,result.data,function(){
                                        _container.set('iconClass','fa-paint');
                                    });
                                }
                            }
                        },this,dstContainer.containerNode,true);
                    }

                };

                _openEditor(imageUrl);


            },
            onMainViewReady:function(evt){

                if(this.didRegister){
                    return;
                }
                var thiz=this;
/*
                this.publish(types.EVENTS.REGISTER_EDITOR,{
                    name:'Aviary',
                    extensions:'jpeg|jpg|gif|png',
                    onEdit:function(){thiz.openEditor(thiz.currentItem)},
                    iconClass:'el-icon-brush',
                    owner:thiz
                },thiz);

                this.publish(types.EVENTS.REGISTER_EDITOR,{
                    name:'Pixlr',
                    extensions:'jpeg|jpg|gif|png',
                    onEdit:function(){thiz.openPixlrEditor(thiz.currentItem)},
                    iconClass:'el-icon-brush',
                    owner:thiz
                },thiz);
*/
                this.publish(types.EVENTS.REGISTER_EDITOR,{
                    name:'Pixie',
                    extensions:'jpeg|jpg|png',
                    onEdit:function(){thiz.openPixie(thiz.currentItem)},
                    iconClass:'el-icon-brush',
                    owner:thiz
                },thiz);

                this.didRegister=true;

            },
            _registerListeners:function () {
                this.inherited(arguments);
                this.subscribe([types.EVENTS.ITEM_SELECTED,types.EVENTS.ON_MAIN_VIEW_READY]);
            },
            constructor:function () {
                this.id=utils.createUUID();
                this._registerListeners();
            }
        });
});