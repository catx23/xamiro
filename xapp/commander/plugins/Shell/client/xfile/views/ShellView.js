define([
    "xide/widgets/TemplatedWidgetBase",
    "xide/widgets/_CSSMixin",
    "xide/widgets/_StyleMixin",
    "xide/widgets/_InsertionMixin",
    "xide/utils",
    'xide/types',
    'xide/factory',
    'dojo/cookie',
    'dojo/json',
    './ShellRunView'
],
    function (TemplatedWidgetBase,CSSMixin,_StyleMixin,_InsertionMixin,utils,types,factory,cookie,json,ShellRunView)
    {
        return dojo.declare("Shell.xfile.views.ShellView", [TemplatedWidgetBase,CSSMixin,_StyleMixin,_InsertionMixin],
            {
                config:null,
                ctx:null,
                cssClass:"mainView XShellView",
                /***
                 * Widget-Instances from templateString, referenced by 'data-dojo-attach-point'
                 */
                layoutMain:null,
                layoutTop:null,
                layoutLeft:null,
                layoutCenter:null,
                layoutRight:null,
                layoutBottom:null,
                panelManager:null,
                leftPanel:null,
                centerPanel:null,
                tabContainer:null,
                bottomTabContainer:null,
                logPanel:null,
                /***
                 * Variables
                 */
                preview:null,
                cookieName:null,
                topics:null,
                panels:null,
                templateString:
                    "<div>"+
                        "<div data-dojo-attach-point='layoutMain' data-dojo-type='xide.layout.BorderContainer' data-dojo-props=\"design:'sidebar',cookieName:'${!cookieName}'\" class='layoutMain ui-widget-content'>"+
                        "<div data-dojo-attach-point='layoutCenter' style='padding:0px;' data-dojo-type='dijit.layout.ContentPane' data-dojo-props=\"region:'center',splitter:'false'\" class='layoutCenter'></div>"+
                        "<div data-dojo-attach-point='layoutRight' data-dojo-type='dijit.layout.ContentPane' data-dojo-props=\"region:'right',splitter:'true',minSize:'200',toggleSplitterState:'full',toggleSplitterFullSize:'200px' \" class='layoutRight filePropertyPanel ui-state-default'></div>"+
                        "<div data-dojo-attach-point='layoutBottom' data-dojo-type='dijit.layout.ContentPane' data-dojo-props=\"region:'bottom',splitter:'true',toggleSplitterState:'closed',toggleSplitterClosedSize:'0px',toggleSplitterFullSize:'150px'\" class='layoutBottom ui-state-default'></div>"+
                        "</div>"+
                        "</div>",
                /***
                 * the persistence is currently done with bloody cookies
                 */
                prepareLayout:function(config){
                    if(config.LAYOUT_PRESET==types.LAYOUT_PRESET.SINGLE){
                        this.layoutMain.removeChild(this.layoutLeft);
                    }

                    if(config.PANEL_OPTIONS!=null && config.PANEL_OPTIONS.ALLOW_INFO_VIEW===false){
                        this.layoutMain.removeChild(this.layoutRight);
                    }
                    if(this.layoutTop && config.PANEL_OPTIONS!=null && (config.PANEL_OPTIONS.ALLOW_BREADCRUMBS===false||config.PANEL_OPTIONS.ALLOW_BREADCRUMBS===0) &&
                        (config.ACTION_TOOLBAR_MODE==null||config.ACTION_TOOLBAR_MODE!='self')){
                        this.layoutMain.removeChild(this.layoutTop);
                    }else{
                        if(this.layoutTop && this.layoutTop._splitterWidget){
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
                getPanelManager:function(){
                  return this.panelManager;
                },
                createShellPanel:function(parentContainer,type,name){

                    var _container = new ShellRunView({
                        title: name,
                        closable: false,
                        delegate:this,
                        type:type,
                        style: 'padding:0px;margin:0px;height:inherit',
                        className:'runView'
                    }, dojo.doc.createElement('div'));
                    parentContainer.addChild(_container);

                },
                initWithConfig:function(config){
                    try{
                        this.prepareLayout(config);
                        var tabContainer = this.createTabContainer(this.layoutCenter);
                        this.panels.push(this.createShellPanel(tabContainer,'sh','Bash'));
                        this.panels.push(this.createShellPanel(tabContainer,'js','Javascript'));
                        /*this.panels.push(this.createShellPanel(tabContainer,'php','PHP'));*/

                    }catch(e){
                        debugger;
                    }

                    /*this.createPanels(config);*/

                    var thiz=this;
                    this.resize();
                },
                removeEmptyContainers:function(){

                    utils.destroyIfEmpty(this.layoutRight);
                    utils.destroyIfEmpty(this.layoutCenter);
                    utils.destroyIfEmpty(this.layoutLeft);
                    utils.destroyIfEmpty(this.layoutTop);
                    utils.destroyIfEmpty(this.layoutBottom);
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

                    this.toolbar=null;
                    utils.destroyWidget(this.preview);
                    utils.destroyWidget(this.leftPanel);
                    this.preview=null;
                    dojo.forEach(this.topics, dojo.unsubscribe);
                },
                startup:function(){
                    this.inherited(arguments);
                    this.panels=[];
                    if(this.toolbar){
                        this.toolbar.delegate=this;
                    }

                    this.topics = [
                        factory.subscribe(types.EVENTS.IMAGE_LOADED,this.resize,this)
                    ];

                },
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
                createTabContainer:function(layoutContainer){

                    if(!this.tabContainer){


                        var tabContainer = new dijit.layout.TabContainer({
                            tabStrip:true,
                            tabPosition:"left",
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
                onConsoleEnter:function(cmdConsole,value){
                    this.delegate.onConsoleCommand(cmdConsole,value);
                }
            });
    });