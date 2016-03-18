define([
    "xdojo/declare",
    "dcl/dcl",
    "xide/types",
    'xdocker/Docker2',
    //'xide/layout/_TabContainer',
    "xide/utils"
], function (declare,dcl,types,Docker,utils) {
    /**
     *
     * @mixin module:xide/views/_LayoutMixin
     */
    var Implementation = {
        _docker:null,
        _parent:null,
        __right:null,
        __bottom:null,
        __masterPanel:null,
        __bottomTabContainer:null,
        defaultPanelOptions:null,
        defaultPanelType:'DefaultFixed',
        reparent:true,
        getTop:function(){
          return this._parent;
        },
        resize:function(){
            if(this._docker){
                this._docker.resize();
            }
            return this.inherited(arguments);
        },
        getDockerTargetNode:function(){
            return null;
        },
        getDocker:function(container){

            var thiz = this;

            if(!this._docker){

                var _node = this._domNode || this.domNode;

                var _dst = this.getDockerTargetNode() || container || _node.parentNode;

                thiz._docker = Docker.createDefault(_dst);
                thiz._oldParent = thiz._parent;

                var defaultOptions  = this.defaultPanelOptions || {
                        w: '100%',
                        title:false
                    };

                var parent = thiz._docker.addPanel(this.defaultPanelType, types.DOCKER.TOP, null,defaultOptions);

                this.reparent && dojo.place(_node,parent.containerNode);

                this.reparent && thiz._docker.$container.css('top',0);

                thiz._parent = parent;
                thiz.__masterPanel = parent;
                !defaultOptions.title && parent._parent.showTitlebar(false);
                _node.id = this.id;
                thiz.add(thiz._docker,null, false);

            }

            return thiz._docker;
        },
        getPanelSplitPosition:function(type){

            if(type == types.DOCKER.DOCK.RIGHT && this.__right){
                return this.__right.getSplitter().pos();
            }
            return false;
        },
        setPanelSplitPosition:function(type,position){

            var right = this.__right;

            if(type == types.DOCKER.DOCK.RIGHT && right){

                var splitter = right.getSplitter();
                if(position==1) {
                    splitter._isToggledMin = true;
                    splitter._isToggledMax = true;
                }else if(position<1 && position >0){
                    splitter._isToggledMin = false;
                    splitter._isToggledMax = false;
                }
                splitter.pos(position);
            }
        },
        openRight:function(open){

            var thiz = this,
                rightSplitPosition= thiz.getPanelSplitPosition(types.DOCKER.DOCK.RIGHT);

            if(!open && rightSplitPosition<1){
                //panel is open: close it
                thiz.setPanelSplitPosition(types.DOCKER.DOCK.RIGHT,1);
            }else if(open && rightSplitPosition==1){
                //closed, open it and show properties
                thiz.setPanelSplitPosition(types.DOCKER.DOCK.RIGHT,0.6);
            }
        },
        _getRight:function(){
            return this.__right;
        },
        _getBottom:function(){
            return this.__bottom;
        },
        getBottomTabContainer:function(create){

            if(this.__bottomTabContainer){
                return this.__bottomTabContainer;
            }else if(create!==false){

                this. __bottomTabContainer = utils.addWidget(_TabContainer, {
                    direction: 'below'
                }, null,this.getBottomPanel(false, 0.2), true);

            }
            return this.__bottomTabContainer;
        },
        _addPanel:function(props,location,title,startPosition,type,target){

            var docker = this.getDocker();

            var panel = docker.addPanel(type || 'DefaultFixed', location , target ===false ? null : (target || this._parent), props || {
                w: '30%',
                h:'30%',
                title:title||false
            });

            if(!title) {
                panel._parent.showTitlebar(false);
            }

            if(startPosition){
                var splitter = panel.getSplitter();
                if(startPosition==1 || startPosition==0) {
                    splitter.pos(startPosition);

                }else {
                    splitter.pos(0.6);
                }
            }
            return panel;
        },
        getBottomPanel:function(title,startPosition,type,mixin,target){

            if(this.__bottom || this._getBottom()){
                return this.__bottom || this._getBottom();
            }

            var create = true;
            if(create!==false) {
                this.__bottom = this._addPanel(utils.mixin({
                    w: '30%',
                    title: title || '  '
                },mixin), types.DOCKER.DOCK.BOTTOM, title,startPosition,type,target);
            }

            return this.__bottom;

        },
        getRightPanel:function(title,startPosition,type,props){

            if(this.__right || this._getRight()){
                return this.__right || this._getRight();
            }
            var panel = this._addPanel(utils.mixin({
                w: '30%',
                title:title || '  '
            },props),types.DOCKER.DOCK.RIGHT,title,null,type);

            this.__right = panel;
            return panel;

        }
    };


    //package via declare
    var _class = declare("xide/views/_LayoutMixin",null,Implementation);
    _class.Implementation = Implementation;
    _class.dcl = dcl(null,Implementation);
    return _class;

});
