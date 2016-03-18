define([
    "dojo/_base/declare",
    "dojo/_base/lang", // lang.mixin lang.hitch
    "xide/widgets/TemplatedWidgetBase",
    "xide/widgets/_CSSMixin",
    "xide/widgets/_StyleMixin",
    "xide/widgets/_InsertionMixin",
    "xide/mixins/EventedMixin",
    "xide/utils",
    'xide/types',
    'dojox/encoding/digests/MD5',
    'dojo/cookie',
    'dojo/json',
    'xfile/views/CKEditor',
    'xide/factory'
],
    function (declare, lang,TemplatedWidgetBase,CSSMixin,_StyleMixin,
              _InsertionMixin,EventedMixin,utils,types,MD5,cookie,json,CKEditor,factory)
    {
        return declare("HTMLEditor.xfile.views.HTMLEditor", [TemplatedWidgetBase,CSSMixin,_StyleMixin,EventedMixin,_InsertionMixin],
            {
                config:null,
                ctx:null,
                cssClass:"mainView xHTMLEditor",
                /***
                 * Widget-Instances from templateString, referenced by 'data-dojo-attach-point'
                 */
                layoutMain:null,
                layoutTop:null,
                layoutLeft:null,
                layoutCenter:null,
                layoutRight:null,
                layoutBottom:null,
                toolbar:null,
                preventCaching:true,
                panelManager:null,
                leftPanel:null,
                centerPanel:null,
                tabContainer:null,
                breadCrumb:null,
                bottomTabContainer:null,
                logPanel:null,
                /***
                 * Variables
                 */
                frameUrl:null,
                editUrl:null,
                preview:null,
                cookieName:null,
                autoReloadPreview:true,
                topics:null,
                panels:null,
                templateString:
                    "<div>"+
                        "<div data-dojo-attach-point='layoutMain' data-dojo-type='xide.layout.BorderContainer' data-dojo-props=\"design:'headline',cookieName:'${!cookieName}'\" class='layoutMain '>"+
                        "<div data-dojo-attach-point='layoutTop' data-dojo-type='dijit.layout.ContentPane' data-dojo-props=\"region:'top',splitter:'false'\" class='layoutTop ui-state-default'></div>" +
                        "<div data-dojo-attach-point='layoutLeft' data-dojo-type='dijit.layout.ContentPane' data-dojo-props=\"region:'leading',splitter:'true'\" class='layoutLeft filePanelLayoutcontainer'></div>"+
                        "<div data-dojo-attach-point='layoutCenter' style='padding:0px;' data-dojo-type='dijit.layout.ContentPane' data-dojo-props=\"region:'center',splitter:'false'\" class='layoutCenter'></div>"+
                        "<div data-dojo-attach-point='layoutRight' data-dojo-type='dijit.layout.ContentPane' data-dojo-props=\"region:'right',splitter:'true',minSize:'200',toggleSplitterState:'full',toggleSplitterFullSize:'200px' \" class='layoutRight filePropertyPanel ui-state-default'></div>"+
                        "<div data-dojo-attach-point='layoutBottom' data-dojo-type='dijit.layout.ContentPane' data-dojo-props=\"region:'bottom',splitter:'true',toggleSplitterState:'closed',toggleSplitterClosedSize:'0px',toggleSplitterFullSize:'150px'\" class='layoutBottom ui-state-default'></div>"+
                        "</div>"+
                        "</div>",
                getNewAlternateTarget:function(layoutContainer){

                    if(!this.tabContainer){


                        var tabContainer = new dijit.layout.TabContainer({
                            tabStrip:true,
                            tabPosition:"top",
                            splitter:true,
                            region:'center',
                            attachParent:true,
                            style:"width:100%;height:100%"

                        },dojo.doc.createElement('div'));
                        layoutContainer.containerNode.appendChild(tabContainer.domNode);
                        tabContainer.startup();
                        this.tabContainer=tabContainer;

                    }
                    return this.tabContainer;
                },
                prepareLayout:function(config){

                    this.layoutMain.removeChild(this.layoutRight);
                    if(this.layoutTop._splitterWidget){
                        utils.destroyWidget(this.layoutTop._splitterWidget);
                        this.layoutTop._splitterWidget=null;
                    }
                    this.layoutMain.removeChild(this.layoutBottom);

                    if(config.PANEL_OPTIONS!=null && config.PANEL_OPTIONS.ALLOW_LOG_VIEW===false){

                    }else{
                        if(this.layoutBottom._splitterWidget){
                            this.layoutBottom._splitterWidget.fullSize='150px';
                            this.layoutBottom._splitterWidget.collapsedSize=null;
                            if(!this.bottomTabContainer){

                                this.bottomTabContainer = this._createBottomTabContainer();

                                this.logPanel = new dijit.layout.ContentPane({
                                    className:"bottomTabLog",
                                    title:'Log',
                                    style:"padding:0;background-color: transparent;"
                                },dojo.doc.createElement('div'));
                                this.bottomTabContainer.addChild(this.logPanel);
                            }
                        }
                    }

                },
                getFileContent:function(filePath,ready){
                    this.ctx.getFileManager().getContent(filePath,ready);
                },
                /**
                 *
                 * @param path
                 * @param mount
                 * @returns {xfile.views.CKEditor}
                 * @private
                 */
                _createEditor:function(path,mount){

                    return new CKEditor({
                        dataRef:null,
                        sourceType:0,
                        delegate:this,
                        title:"Title",
                        titleEdit:false,
                        content:null,
                        path:path,
                        mount:mount,
                        removePlugins:''
                    },dojo.doc.createElement('div'));
                },
                onRight:function(thiz){
                    var contentLeft = thiz.leftPanel.getContent();
                    thiz.editor.setContent(contentLeft);
                    /*console.error(contentLeft);*/
                },
                onLeft:function(thiz){
                    var contentRight = thiz.editor.getContent();
                    thiz.leftPanel.setContent(contentRight);
                    /*console.error(contentLeft);*/
                },
                createWidgets:function(){

                    var btn = factory.createButton(this.layoutTop.containerNode, "el-icon-caret-left", "toolbarButton", "", "", this.onLeft, this);
                    var btn = factory.createToggleButton(this.layoutTop.containerNode, "el-icon-link", "toolbarButton", "", "", this.onSelect, this,true);
                    var btn = factory.createButton(this.layoutTop.containerNode, "el-icon-caret-right", "toolbarButton", "", "", this.onRight, this);
                },
                createPanels:function(config){

                    this.leftPanel = this.ctx.getScriptManager().openFile(this.item,this.layoutLeft);
                    var editor = this._createEditor(this.item.path,this.item.mount);
                    this.layoutCenter.containerNode.appendChild(editor.domNode);
                    editor.startup();
                    this.editor=editor;

                },
                getPanelManager:function(){
                  return this.panelManager;
                },
                initWithConfig:function(config){
                    this.prepareLayout(config);
                    this.createPanels(config);
                    this.createWidgets();
                },
                removeEmptyContainers:function(){

                    /*xutils.destroyIfEmpty(this.layoutRight);
                    xutils.destroyIfEmpty(this.layoutCenter);
                    xutils.destroyIfEmpty(this.layoutLeft);
                    xutils.destroyIfEmpty(this.layoutTop);
                    xutils.destroyIfEmpty(this.layoutBottom);
                    */
                },
                postMixInProperties:function ()
                {
                    this.inherited(arguments);
                    if(this.frameUrl){
                        this.cookieName = 'HTMLEditorCookie' + MD5(this.frameUrl, 1);
                    }
                },
                onFileContentChanged:function(eventData){

                    if(this.preview){

                        if(this.autoReloadPreview){
                            var path = '' + eventData.path;
                            path=utils.cleanUrl(path);

                            var thisUrl = '' + this.preview.path;
                            thisUrl =utils.cleanUrl(thisUrl);

                            //this might by us
                            if(thisUrl.indexOf(path)!=-1){
                                this.preview.reload();

                                //restore focus on editor
                                if(this.leftPanel && this.leftPanel.focus){
                                    this.leftPanel.focus();
                                }
                                return;
                            }



                        }
                        var remoteEditorMessageArgs = {
                            command:types.EVENTS.ON_FILE_CONTENT_CHANGED
                        };
                        lang.mixin(remoteEditorMessageArgs,eventData);
                        this.preview.sendMessage(remoteEditorMessageArgs);

                    }


                },
                _resizeContainer:function(container,name){
                    try{
                        if(container && container.domNode){
                            container.resize();
                        }else{
                        }
                    }catch(e){

                    }
                },
                _fixToolbar:function(){

                    if(this.toolbar && this.layoutBottom && this.layoutMain && this.layoutBottom.domNode){
                        dojo.place(this.toolbar.domNode,this.layoutBottom.domNode,'before');
                    }
                },
                resize:function(){
                    this.inherited(arguments);

                    if(this.layoutMain){
                        this._resizeContainer(this.layoutMain,'main');
                    }

                    if(this.layoutLeft){
                        this._resizeContainer(this.layoutLeft,'left');
                    }
                    if(this.layoutTop){
                        this._resizeContainer(this.layoutTop,'top');
                    }
                    if(this.layoutCenter){
                        this._resizeContainer(this.layoutCenter,'center');
                    }
                    if(this.layoutRight){
                        this._resizeContainer(this.layoutRight,'right');
                    }


                    if(this.layoutLeft){
                        this._resizeContainer(this.layoutLeft,'left');
                    }
                    if(this.layoutBottom){
                        this._resizeContainer(this.layoutBottom,'bottom');
                    }



                },
                onStartupPost:function(){
                    //register events
                    var thiz=this;
                    dojo.connect(null, (dojo.global.onorientationchange !== undefined) ? "onorientationchange" : "onresize", this, function () {
                        thiz.resize();
                    });
                    this.removeEmptyContainers();
                    this.resize();
                },
                postCreate:function(){
                    this.inherited(arguments);

                },
                buildRendering: function(){
                    this.inherited(arguments);
                },
                destroy: function(){
                    // summary:
                    //		Prepares the object to be garbage-collected.
                    this.buttons=null;
                    this.toolbar=null;
                    utils.destroyWidget(this.preview);
                    utils.destroyWidget(this.leftPanel);
                    this.preview=null;
                    this.inherited(arguments);
                },
                startup:function(){
                    this.inherited(arguments);
                    this.buttons=[];
                    this.panels=[];
                    if(this.toolbar){
                        this.toolbar.delegate=this;
                    }
                    this.subscribe(types.EVENTS.ON_FILE_CONTENT_CHANGED,this.onFileContentChanged,this)


                },
                /***
                 * the persistence is currently done with bloody cookies
                 */
                loadPreferences:function(){

                    var _cookie =this.cookiePrefix + '_items_';
                    var _items = cookie(_cookie);
                    _items = _items ? json.parse(_items) : [];
                    return _items;
                },
                savePreferences:function(){
                    try{
                        var _items = [];
                        for(var i= 0 ;i < this.panels.length ; i++){
                            if(this.panels[i].item && this.panels[i].item.path){
                                _items.push(this.panels[i].item.path);
                            }
                        }

                        var _cookie = this.cookiePrefix + '_items_';
                        cookie(_cookie, json.stringify(_items));
                    }catch(e){
                        debugger;
                    }
                },
                onEditorClose:function(editor){
                    this.panels.remove(editor);
                    this.savePreferences();
                },
                addItem:function(item){
                    var tabContainer = this.getNewAlternateTarget(this.layoutLeft);
                    var panel = this.ctx.getScriptManager().openFile(item,tabContainer,this);
                    this.panels.push(panel);
                    tabContainer.selectChild(panel);
                    this.savePreferences();
                }
            });
    });