define([
    "dcl/dcl",
    "dcl/inherited",
    'xide/utils',
    "xide/_base/_Widget"
], function (dcl,inherited,utils,_Widget) {

    var TabPaneClass = dcl(_Widget,{
        templateString:'<div></div>',
        isContainer:true,
        declaredClass:'xide/layout/_TabPane',
        panelNode:null,
        selected:false,
        $toggleNode:null,
        $toggleButton:null,
        lazy:true,
        add:dcl.superCall(function(sup) {
            return function (mixed,options,parent,startup) {
                if(this.lazy && (mixed.allowLazy!==false && (options ? options.allowLazy!==false : true))){
                    startup = false;
                }

                return sup.apply(this, [mixed,options,parent,startup]);
            }
        }),
        addChild:function(what,mixed,startup){

            //console.log('_TabContainer :: addChild',arguments);
            //collect widget
            this.add(what,mixed);
            what.domNode && utils.addChild(this.containerNode,what.domNode);
            if(startup!==false && !what._started && what.startup){
                what.startup();
            }
        },
        unselect:function(){
            this.$toggleButton && this.$toggleButton.removeClass('active');
            this.$selectorNode && this.$selectorNode.removeClass('active');
            this.$containerNode && this.$containerNode.removeClass('active');
            this.selected = false;
        },
        select:function(){

            this.$toggleButton && this.$toggleButton.addClass('active');
            this.$selectorNode && this.$selectorNode.addClass('active');
            this.$containerNode && this.$containerNode.addClass('active');

            this._onShown();


            this.onSelect && this.onSelect();

        },
        destroy:function(){

            utils.destroy(this.$toggleButton[0]);
            utils.destroy(this.$containerNode[0]);

            return this.inherited(arguments);

        },
        _checkWidgets:function(){

        },
        _onShown:function(e){

            this.selected = true;

            console.log('on shown!');
            this._startWidgets();
            this.onShow();
            //this.resize();

            this.onSelect && this.onSelect();

            var thiz = this;
            setTimeout(function(){
                thiz.owner && thiz.owner.onShowTab();
            },1);


        },
        _onShow:function(e){
            this.selected = true;
            this.resize();
        },
        shouldResizeWidgets:function(){
            return this.selected;
        },
        _onHide:function(){
            this.selected = false;

            this.onHide();
        },
        show:function(){

            var container = $(this.containerRoot),
                toggleNode = $(this.toggleNode);

            toggleNode.removeClass('collapsed');
            toggleNode.attr('aria-expanded',true);


            container.removeClass('collapse');
            container.addClass('collapse in');
            container.attr('aria-expanded',true);

        },
        hide:function(){

            var container = $(this.containerRoot),
                toggleNode= $(this.toggleNode);

            toggleNode.addClass('collapsed');
            toggleNode.attr('aria-expanded',false);

            container.removeClass('collapse in');
            container.addClass('collapse');
            container.attr('aria-expanded',false);
        },
        postMixInProperties:function(){
            var closed = !this.open;
            var iconStr = this.iconClass ? '<span class="${!iconClass}"/>' : '';
            var active = this.selected ? 'active' : '';
            this.templateString = '<div attachTo="containerNode" style="height:100%;width:100%;position:relative;" class="tab-pane ' + active + '"></div>';
        },
        __init:function(){

            var self = this;

            var panel = this.$toggleNode;
            this.__addHandler(panel,'hidden.bs.tab','_onHided');
            this.__addHandler(panel,'hide.bs.tab','_onHide');
            this.__addHandler(panel,'shown.bs.tab','_onShown');
            this.__addHandler(panel,'show.bs.tab','_onShow');
        }
    });

    var TabContainer = dcl(_Widget,{
        declaredClass:'xide/layout/_TabContainer',
        tabClass:TabPaneClass,
        tabs:null,
        tabBar:null,
        tabContentNode:null,
        padding:'0px',
        containerCSSClass:'',
        direction:'above',
        startup:function(){
            if(this._started){
                return;
            }
            console.error('--tab container');
        },
        templateString:'<div class="${!containerCSSClass} tabbable tabs-${!direction}" style="height: inherit;" attachTo="containerNode">' +
        '<ul attachTo="tabBar" class="nav nav-tabs" role="tablist" />' +
        '<div attachTo="tabContentNode" style="width: inherit; padding:${!padding}; height: 100%;" class="tab-content"/>' +

        '</div>',
        getTab:function(name){
            return _.find(this._widgets,{
                title:name
            });
        },
        _unselectAll:function(){

            _.each(this._widgets,function(tab){
                tab.unselect();
            });
        },
        onShowTab:function(){
            this.resize();
        },
        selectChild:function(mixed){

            var tab = mixed;
            if(mixed!==null) {

                if (_.isString(mixed)) {
                    tab = this.getTab(mixed);
                }else if(_.isNumber(mixed)){
                    tab = this._widgets[0];
                }

                if (tab && tab.select) {
                    this._unselectAll();
                    tab.select();
                }

            }else{
                console.error('selectChild : mixed = null');
            }
        },
        addWidget:function(widgetProto, ctrArgsIn, delegate, parent, startup, cssClass,baseClasses,select,classExtension){


            var target = parent;
            if(widgetProto.isContainer){

            }else{

                target = this._createTab(this.tabClass,{
                    title:ctrArgsIn.title,
                    icon:ctrArgsIn.icon,
                    selected:ctrArgsIn.selected,
                    ignoreAddChild:true
                });
            }

            var w = target.add(widgetProto,ctrArgsIn,null,startup);
            return w;
        },
        resize:function(){


            if(this.tabBar){

                switch (this.direction){

                    case 'left':
                    case 'right':{
                        this.$tabContentNode.css('width', '');
                        break;
                    }
                    case 'above':
                    case 'below':{

                        if(this.$containerNode) {

                            var _total = this.$containerNode.height();
                            var _toolbar = this.$tabBar.height();
                            this.$tabContentNode.css('height', _total - _toolbar);
                        }
                        break;
                    }
                }
            }
            this.inherited && this.inherited(arguments);
        },
        _createTab:function(tabClass,options){


            !this.tabs && (this.tabs = []);

            var active = this.tabs.length == 0 ? 'active' : '',
                icon = options.icon || '',
                title = options.title || '',
                selected = options.selected!=null ? options.selected : this.tabs.length ==0;


            if(this.tabs.length ==0){
                //    selected=true;
            }

            var pane = utils.addWidget(tabClass || this.tabClass,{
                title:title,
                icon:icon,
                selected:selected,
                owner:this
            },null,this.tabContentNode,true);


            var tabId = pane.id,
                iconStr = icon ? ' ' +icon : '',
                toggleNodeStr =
                    '<li class="' +active + '">' +
                    '<a href="#'+tabId +'" data-toggle="tab" class="' +iconStr  +'"> ' + title +'</a></li>',
                tabButton = $(toggleNodeStr);

            $(this.tabBar).append(tabButton);


            pane.$toggleNode  = tabButton.find('a[data-toggle="tab"]');
            pane.$selectorNode  = tabButton.find('li');
            pane.$toggleButton  = tabButton;

            /*
            pane.$toggleNode.addClass('wcCreatePanel');
            pane.$toggleNode.data('panel', 'DefaultTab');
            */

            pane.__init();

            this.tabs.push({
                id:tabId,
                pane: pane,
                button:tabButton[0]
            });


            this.add(pane,null,false);

            return pane;
        },
        removeChild:function(tab,selectNew){

            tab = _.isString(tab) ? this.getTab(tab) : tab;
            if(!tab){
                console.error('invalid child !');
                return;
            }

            tab.destroy();

            this._widgets.remove(tab);

            if(selectNew!==false) {
                var newTab = this._widgets[this._widgets.length - 1];
                if (newTab) {
                    this.resize();
                    this.selectChild(newTab);
                }
            }
        },
        empty:function(){
            while(this._widgets.length){
                this.removeChild(this._widgets[0],false);
            }
        },
        postMixInProperties:function(){
            if(this.direction==='below'){
                this.templateString = '<div class="${!containerCSSClass} tabbable tabs-${!direction}" style="height: inherit;" attachTo="containerNode">' +
                    '<div attachTo="tabContentNode" style="width: inherit; padding:${!padding}; height: 100%;" class="tab-content"/>' +
                    '<ul attachTo="tabBar" class="nav nav-tabs" role="tablist" />' +
                    '</div>';
            }

            //return this.inherited(arguments);
        },
        createTab:function(title,icon,selected,tabClass,mixin){
            return this._createTab(tabClass,utils.mixin({
                icon:icon,
                selected:selected,
                title:title

            },mixin));
        }
    });

    function createTabPaneClass(baseClass){

        return dcl(baseClass||_Widget,{
            templateString:'<div></div>',
            isContainer:true,
            declaredClass:'xide/layout/_TabPane',
            panelNode:null,
            selected:false,
            $toggleNode:null,
            $toggleButton:null,
            lazy:true,
            add:dcl.superCall(function(sup) {
                return function (mixed,options,parent,startup) {
                    if(this.lazy && (mixed.allowLazy!==false && (options ? options.allowLazy!==false : true))){
                        startup = false;
                    }

                    return sup.apply(this, [mixed,options,parent,startup]);
                }
            }),
            addChild:function(what,mixed,startup){
                //collect widget
                this.add(what,mixed);

                what.domNode && utils.addChild(this.containerNode,what.domNode);
                if(startup!==false && !what._started && what.startup){
                    what.startup();
                }
            },
            unselect:function(){
                this.$toggleButton && this.$toggleButton.removeClass('active');
                this.$selectorNode && this.$selectorNode.removeClass('active');
                this.$containerNode && this.$containerNode.removeClass('active');
                this.selected = false;
            },
            select:function(){

                this.$toggleButton && this.$toggleButton.addClass('active');
                this.$selectorNode && this.$selectorNode.addClass('active');
                this.$containerNode && this.$containerNode.addClass('active');

                this._onShown();


                this.onSelect && this.onSelect();

            },
            destroy:function(){

                utils.destroy(this.$toggleButton[0]);
                utils.destroy(this.$containerNode[0]);

                return this.inherited(arguments);

            },
            _checkWidgets:function(){

            },
            _onShown:function(e){

                this.selected = true;
                this._startWidgets();
                this.onShow();
                this.onSelect && this.onSelect();
                var thiz = this;
                setTimeout(function(){
                    thiz.owner && thiz.owner.onShowTab();
                },1);


            },
            _onShow:function(e){
                this.selected = true;
                this.resize();
            },
            shouldResizeWidgets:function(){
                return this.selected;
            },
            _onHide:function(){
                this.selected = false;

                this.onHide();
            },
            show:function(){

                var container = $(this.containerRoot),
                    toggleNode = $(this.toggleNode);

                toggleNode.removeClass('collapsed');
                toggleNode.attr('aria-expanded',true);


                container.removeClass('collapse');
                container.addClass('collapse in');
                container.attr('aria-expanded',true);

            },
            hide:function(){

                var container = $(this.containerRoot),
                    toggleNode= $(this.toggleNode);

                toggleNode.addClass('collapsed');
                toggleNode.attr('aria-expanded',false);

                container.removeClass('collapse in');
                container.addClass('collapse');
                container.attr('aria-expanded',false);
            },
            postMixInProperties:function(){
                var closed = !this.open;
                var iconStr = this.iconClass ? '<span class="${!iconClass}"/>' : '';
                var active = this.selected ? 'active' : '';
                this.templateString = '<div attachTo="containerNode" style="height:100%;width:100%;position:relative;" class="tab-pane ' + active + '"></div>';
            },
            __init:function(){

                var self = this;

                var panel = this.$toggleNode;
                this.__addHandler(panel,'hidden.bs.tab','_onHided');
                this.__addHandler(panel,'hide.bs.tab','_onHide');
                this.__addHandler(panel,'shown.bs.tab','_onShown');
                this.__addHandler(panel,'show.bs.tab','_onShow');
            }
        });
    };

    function createTabContainerClass(baseClass,tabClass){

        return dcl(baseClass || _Widget,{
            tabClass:tabClass || TabPaneClass,
            declaredClass:'xide/layout/_TabContainer',
            tabs:null,
            tabBar:null,
            tabContentNode:null,
            padding:'0px',
            containerCSSClass:'',
            direction:'above',
            templateString:'<div class="${!containerCSSClass} tabbable tabs-${!direction}" style="height: inherit;" attachTo="containerNode">' +
            '<ul attachTo="tabBar" class="nav nav-tabs" role="tablist" />' +
            '<div attachTo="tabContentNode" style="width: inherit; padding:${!padding}; height: 100%;" class="tab-content"/>' +
            '</div>',
            getTab:function(name){
                return _.find(this._widgets,{
                    title:name
                });
            },
            _unselectAll:function(){

                _.each(this._widgets,function(tab){
                    tab.unselect();
                });
            },
            onShowTab:function(){
                this.resize();
            },
            selectChild:function(mixed){

                var tab = mixed;
                if(mixed!==null) {

                    if (_.isString(mixed)) {
                        tab = this.getTab(mixed);
                    }else if(_.isNumber(mixed)){
                        tab = this._widgets[0];
                    }

                    if (tab && tab.select) {
                        this._unselectAll();
                        tab.select();
                    }

                }else{
                    console.error('selectChild : mixed = null');
                }
            },
            addWidget:function(widgetProto, ctrArgsIn, delegate, parent, startup, cssClass,baseClasses,select,classExtension){


                var target = parent;
                if(widgetProto.isContainer){

                }else{

                    target = this._createTab(this.tabClass,{
                        title:ctrArgsIn.title,
                        icon:ctrArgsIn.icon,
                        selected:ctrArgsIn.selected,
                        ignoreAddChild:true
                    });
                }

                var w = target.add(widgetProto,ctrArgsIn,null,startup);
                return w;
            },
            resize:function(){
                console.log('tab container resize');
                if(this.tabBar){

                    switch (this.direction){

                        case 'left':
                        case 'right':{
                            this.$tabContentNode.css('width', '');
                            break;
                        }
                        case 'above':
                        case 'below':{

                            if(this.$containerNode) {

                                var _total = this.$containerNode.height();
                                var _toolbar = this.$tabBar.height();
                                this.$tabContentNode.css('height', _total - _toolbar);
                            }
                            break;
                        }
                    }
                }
                this.inherited(arguments);

            },
            _createTab:function(tabClass,options){


                !this.tabs && (this.tabs = []);

                var active = this.tabs.length == 0 ? 'active' : '',
                    icon = options.icon || '',
                    title = options.title || '',
                    selected = options.selected!=null ? options.selected : this.tabs.length ==0;


                if(this.tabs.length ==0){
                    //    selected=true;
                }

                var pane = utils.addWidget(tabClass || this.tabClass,{
                    title:title,
                    icon:icon,
                    selected:selected,
                    owner:this
                },null,this.tabContentNode,true);


                var tabId = pane.id,
                    iconStr = icon ? ' ' +icon : '',
                    toggleNodeStr =
                        '<li class="' +active + '">' +
                        '<a href="#'+tabId +'" data-toggle="tab" class="' +iconStr  +'"> ' + title +'</a></li>',
                    tabButton = $(toggleNodeStr);

                $(this.tabBar).append(tabButton);


                pane.$toggleNode  = tabButton.find('a[data-toggle="tab"]');
                pane.$selectorNode  = tabButton.find('li');
                pane.$toggleButton  = tabButton;

                pane.__init();

                this.tabs.push({
                    id:tabId,
                    pane: pane,
                    button:tabButton[0]
                });


                this.add(pane,null,false);

                return pane;
            },
            removeChild:function(tab,selectNew){

                tab = _.isString(tab) ? this.getTab(tab) : tab;
                if(!tab){
                    console.error('invalid child !');
                    return;
                }

                tab.destroy();

                this._widgets.remove(tab);

                if(selectNew!==false) {
                    var newTab = this._widgets[this._widgets.length - 1];
                    if (newTab) {
                        this.resize();
                        this.selectChild(newTab);
                    }
                }
            },
            empty:function(){
                while(this._widgets.length){
                    this.removeChild(this._widgets[0],false);
                }
            },
            postMixInProperties:function(){

                if(this.direction==='below'){
                    this.templateString = '<div class="${!containerCSSClass} tabbable tabs-${!direction}" style="height: inherit;" attachTo="containerNode">' +
                        '<div attachTo="tabContentNode" style="width: inherit; padding:${!padding}; height: 100%;" class="tab-content"/>' +
                        '<ul attachTo="tabBar" class="nav nav-tabs" role="tablist" />' +
                    '</div>';
                }

            },
            createTab:function(title,icon,selected,tabClass,mixin){
                return this._createTab(tabClass,utils.mixin({
                    icon:icon,
                    selected:selected,
                    title:title

                },mixin));
            }

        });
    }
    TabContainer.tabClass = TabPaneClass;
    dcl.chainAfter(TabContainer, "postMixInProperties");
    dcl.chainAfter(TabContainer, "resize");
    return TabContainer;

});