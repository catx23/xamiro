define([
    "../widgets/Console",
    "dojo/_base/lang",// lang.mixin lang.hitc
    "dojo/_base/declare",// lang.mixin lang.hitch
    "dojo/dom-geometry",// domGeometry.position
    "dojo/dom-style",
    "xide/widgets/ProgressItem",
    "xide/factory",
    "xide/types",
    "xide/utils",
    "xide/widgets/_CSSMixin",
    "xide/widgets/_InsertionMixin",
    "xide/widgets/_StyleMixin",
    "xide/widgets/TemplatedWidgetBase"
],
    function (Console, lang,declare,domGeometry,domStyle,ProgressItem,factory,
              types,utils,CSSMixin,_InsertionMixin,_StyleMixin,TemplatedWidgetBase)
    {
        return declare("Shell.xfile.views.ShellRunView", [TemplatedWidgetBase,CSSMixin,_StyleMixin,_InsertionMixin],
            {
                delegate:null,
                value:null,
                editorNode:null,
                cmdNode:null,
                console:null,
                type:null,
                logView:null,
                logPane:null,
                progressItem:null,
                templateString:"<div>" +
                    "<div class='runPanel' data-dojo-attach-point='root' data-dojo-type='dijit.layout.ContentPane'>" +
                        "<div class='logPane ui-widget-content' data-dojo-attach-point='logView' data-dojo-type='dijit.layout.ContentPane'>" +
                        "</div>"+
                        "<div data-dojo-attach-point='cmdNode' class='cmdParent' style='height:25px'></div>" +

                     "</div></div>",
                createLogItem:function(message,parent){
                    var progressItem = new ProgressItem({
                        terminatorItem:message,
                        message:message,
                        autoDestroy:true
                    },dojo.doc.createElement('div'));

                    parent.appendChild(progressItem.domNode);
                    progressItem.startup();

                    parent.appendChild(dojo.create("div",{
                        className:'logEntry',
                        innerHTML:''
                    }));

                    var thiz=this;
                    setTimeout(function(){
                        thiz._scrollToEnd();
                    },200);

                    return progressItem;

                },
                onEnter:function(value){
                    this.progressItem = this.createLogItem(value,this.getLoggingContainer());

                    this.delegate.onConsoleEnter(this.console,value);
                },
                getLoggingContainer:function(){
                    return this.logView.containerNode;
                },
                log:function(msg){

                    utils.destroyWidget(this.progressItem);
                    var out = '';
                    if(lang.isString(msg)){
                        out+=msg.replace(/\n/g, '<br/>');
                    }else if(lang.isObject(msg) || lang.isArray(msg)){
                        out+=JSON.stringify(msg,null,true);
                    }

                    var dst = this.getLoggingContainer();
                    dst.appendChild(dojo.create("span",{
                        innerHTML:'# ' + this.console.getPrev() + ' '
                    }));

                    var items = out.split('<br/>');
                    for(var i =0 ; i < items.length ; i++){
                        dst.appendChild(dojo.create("div",{
                            className:'logEntry',
                            innerHTML:items[i] + ''

                        }))
                    }
                    dst.appendChild(dojo.create("hr",{}));

                },
                _fixHeight:function(){

                    var actionBox  = domGeometry.getMarginBox(this.console.domNode);
                    var box  = domGeometry.getMarginBox(this.domNode);

                    var newHeight = box.h - actionBox.h - 22;
                    domStyle.set(this.logView.domNode,{
                        height:newHeight+'px'
                    })
                },
                resize:function(){
                    this.inherited(arguments);
                    this._fixHeight();
                },
                _scrollToEnd:function(){

                    var container = $(this.getLoggingContainer());
                    /*var last = $(_container.lastChild);
                    if(last){
                        var pos = last.position();
                        container.scrollTop(pos.bottom);​
                        //container.scrollTop(pos.bottom);​
                    }
                    */

                    //container.scrollTop(last.position().bottom);​


                    //$("#container").scrollTop($("#elementToScrollTo").position().top);​

                    //var last = $(_container.children[_container.children]);
                    $(container).animate({
                        scrollTop: $(container[0].lastChild.previousSibling).position().bottom
                    }, 300);

                },
                onServerResponse:function(theConsole,data){
                    this.log(data);
                    var thiz=this;
                    this._fixHeight();
                    thiz._scrollToEnd();
                },
                createWidgets:function(){

                    this.console = utils.addWidget(Console,{
                        type:this.type,
                        owner:this
                    },this,this.domNode,true,'consoleWidget ui-state-default');
                    this._fixHeight();
                },
                getValue:function(){
                    return this.editBox.get('value');
                },
                startup:function()
                {
                    this.inherited(arguments);
                    this.createWidgets();
                    factory.subscribe(types.EVENTS.RESIZE,this.resize,this);
                }
            });
    });