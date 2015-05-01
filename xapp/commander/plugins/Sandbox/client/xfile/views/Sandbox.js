define([
    "dojo/_base/array",
    "dojo/_base/declare",
    "dojo/_base/html",
    "dojo/on",
    "dojo/_base/lang", // lang.mixin lang.hitch
    "xide/widgets/TemplatedWidgetBase",
    "xide/widgets/_CSSMixin",
    "xide/widgets/_StyleMixin",
    "xide/widgets/_InsertionMixin",
    "dojo/dom-class", // domClass.add domClass.contains
    "dojo/dom-geometry", // domGeometry.position
    "dojo/dom-style",
    "dojo/window",
    "xide/utils",
    'xide/types',
    'xide/factory',
    'dijit/layout/ContentPane',
    'xfile/views/RemoteEditor',
    'dojox/encoding/digests/MD5',
    'dojo/cookie',
    'dojo/json'
],
    function (array, declare, html, on,lang,TemplatedWidgetBase,CSSMixin,_StyleMixin,_InsertionMixin,
              domClass, domGeometry, domStyle,win,utils,types,factory,ContentPane,RemoteEditor,MD5,cookie,json)
    {
        return declare("Sandbox.xfile.views.Sandbox", [TemplatedWidgetBase,CSSMixin,_StyleMixin,_InsertionMixin],
            {
                config:null,
                ctx:null,
                cssClass:"mainView xSandbox",
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
                        "<div data-dojo-attach-point='layoutTop' data-dojo-type='dijit.layout.ContentPane' data-dojo-props=\"region:'top',splitter:'true'\" class='layoutTop ui-state-default'></div>" +
                        "<div data-dojo-attach-point='layoutLeft' data-dojo-type='dijit.layout.ContentPane' data-dojo-props=\"region:'leading',splitter:'true'\" class='layoutLeft filePanelLayoutcontainer'></div>"+
                        "<div data-dojo-attach-point='layoutCenter' style='padding:0px;' data-dojo-type='dijit.layout.ContentPane' data-dojo-props=\"region:'center',splitter:'false'\" class='layoutCenter'></div>"+
                        "<div data-dojo-attach-point='layoutRight' data-dojo-type='dijit.layout.ContentPane' data-dojo-props=\"region:'right',splitter:'true',minSize:'200',toggleSplitterState:'full',toggleSplitterFullSize:'200px' \" class='layoutRight filePropertyPanel ui-state-default'></div>"+
                        "<div data-dojo-attach-point='layoutBottom' data-dojo-type='dijit.layout.ContentPane' data-dojo-props=\"region:'bottom',splitter:'true',toggleSplitterState:'closed',toggleSplitterClosedSize:'0px',toggleSplitterFullSize:'150px'\" class='layoutBottom ui-state-default'></div>"+
                        "</div>"+
                        "</div>",
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
                },
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
                    if(config.LAYOUT_PRESET==types.LAYOUT_PRESET.SINGLE){
                        this.layoutMain.removeChild(this.layoutLeft);
                    }

                    if(config.PANEL_OPTIONS!=null && config.PANEL_OPTIONS.ALLOW_INFO_VIEW===false){
                        this.layoutMain.removeChild(this.layoutRight);
                    }
                    if(config.PANEL_OPTIONS!=null && (config.PANEL_OPTIONS.ALLOW_BREADCRUMBS===false||config.PANEL_OPTIONS.ALLOW_BREADCRUMBS===0) &&
                        (config.ACTION_TOOLBAR_MODE==null||config.ACTION_TOOLBAR_MODE!='self')){
                        this.layoutMain.removeChild(this.layoutTop);
                    }else{

                        if(this.layoutTop._splitterWidget){
                            utils.destroyWidget(this.layoutTop._splitterWidget);
                            this.layoutTop._splitterWidget=null;
                        }
                    }
                    if(config.PANEL_OPTIONS!=null && config.PANEL_OPTIONS.ALLOW_LOG_VIEW===false){
                        this.layoutMain.removeChild(this.layoutBottom);
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
                createPanel:function(panelOptions,dstContainer,cookiePrefix,region){

                    if(panelOptions.AUTO_OPEN||panelOptions.AUTO_OPEN==null){
                        return this.getPanelManager().openUrl(this.item.path,dstContainer,this.config,cookiePrefix,region);
                    }
                },
                createPanels:function(config){

                    if(config.LAYOUT_PRESET==types.LAYOUT_PRESET.DUAL){

                        /*this.leftPanel = this.createPanel(config.FILE_PANEL_OPTIONS_LEFT,this.layoutLeft,types.LAYOUT_REGION.LEFT,types.LAYOUT_REGION.LEFT);*/

                        var tabContainer = this.getNewAlternateTarget(this.layoutLeft);

                        this.leftPanel = this.ctx.getScriptManager().openFile(this.item,tabContainer);

                        var preview =  RemoteEditor({
                            selected: true,
                            delegate: this,
                            options: {},
                            config: this.config,
                            frameUrl: this.frameUrl,
                            editUrl: this.editUrl,
                            parentContainer: this.layoutCenter,
                            style:'padding:0px;',
                            path:'' + this.item.path
                        }, dojo.doc.createElement('div'));
                        this.layoutCenter.containerNode.appendChild(preview.domNode);
                        preview.startup();

                        this.centerPanel=preview;
                        this.preview=preview;

                    }else if(config.LAYOUT_PRESET==types.LAYOUT_PRESET.SINGLE   &&
                             config.FILE_PANEL_OPTIONS_MAIN !=null)
                    {
                        this.createPanel(config.FILE_PANEL_OPTIONS_MAIN,this.layoutCenter,types.LAYOUT_REGION.CENTER);
                    }

                    if(config.PANEL_OPTIONS.ALLOW_INFO_VIEW && config.FILE_PANEL_OPTIONS_RIGHT && config.FILE_PANEL_OPTIONS_RIGHT.AUTO_OPEN){
                        /*this.getPanelManager().openProperties(null,this.layoutRight);*/
                    }
                },
                getPanelManager:function(){
                  return this.panelManager;
                },
                onShowPanel:function(data){

                    var panel = data ? data.content ? data.content.panel ? data.content.panel : null : null : null;
                    if(panel && panel.getCurrentPathItem){
                        var cPathItem = panel.getCurrentPathItem();
                        if(cPathItem &&  this.breadCrumb){
                            this.breadCrumb.setTrail(cPathItem.path);
                            this.breadCrumb.owner=panel;
                        }
                    }
                },
                _restoreLastItems:function(){

                    var thiz=this;
                    var _lastItems= this.loadPreferences();
                    if(_lastItems && this.panelManager && this.panelManager.store){
                        for(var i =0 ; i <_lastItems.length; i++){
                            var storeItem = this.panelManager.store.getItemByPath(null,_lastItems[i]);
                            if(storeItem!=null && lang.isObject(storeItem)){
                                this.addItem(storeItem);
                            }
                        }
                    }

                },
                initWithConfig:function(config){
                    this.prepareLayout(config);
                    this.createPanels(config);

                    var thiz=this;
                    setTimeout(function(){
                        thiz._restoreLastItems();
                    },1000)
                },
                removeEmptyContainers:function(){
                    utils.destroyIfEmpty(this.layoutRight);
                    utils.destroyIfEmpty(this.layoutCenter);
                    utils.destroyIfEmpty(this.layoutLeft);
                    utils.destroyIfEmpty(this.layoutTop);
                    utils.destroyIfEmpty(this.layoutBottom);
                },
                postMixInProperties:function ()
                {
                    this.inherited(arguments);
                    if(this.frameUrl){
                        this.cookieName = 'sandboxCookie' + MD5(this.frameUrl, 1);
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
                _createBottomTabContainer:function(){

                    var tabContainer = new dijit.layout.TabContainer({
                        tabStrip:true,
                        tabPosition:"bottom",
                        region:'bottom',
                        attachParent:true,
                        style:"width:100%"

                    },dojo.doc.createElement('div'));
                    this.layoutBottom.containerNode.appendChild(tabContainer.domNode);
                    tabContainer.startup();
                    return tabContainer;
                },
                destroy: function(){
                    // summary:
                    //		Prepares the object to be garbage-collected.
                    this.buttons=null;
                    this.toolbar=null;
                    utils.destroyWidget(this.preview);
                    utils.destroyWidget(this.leftPanel);
                    this.preview=null;
                    dojo.forEach(this.topics, dojo.unsubscribe);
                },
                startup:function(){
                    this.inherited(arguments);
                    this.buttons=[];
                    this.panels=[];
                    if(this.toolbar){
                        this.toolbar.delegate=this;
                    }
                    this.topics = [
                       factory.subscribe(types.EVENTS.ON_FILE_CONTENT_CHANGED,this.onFileContentChanged,this)
                    ];

                }

            });
    });