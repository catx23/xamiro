define([
    "dojo/_base/declare",
    "xide/widgets/TemplatedWidgetBase",
    "xide/widgets/_CSSMixin",
    "xide/widgets/_StyleMixin",
    "xide/widgets/_InsertionMixin",
    "dojo/dom-class",
    'xide/factory',
    '../KeyboardHandler',
    '../History'
],
    function (declare, TemplatedWidgetBase,CSSMixin,_StyleMixin,_InsertionMixin,domClass, factory,KeyboardHandler,History)
    {
        return declare("Shell.xfile.widgets.Console", [TemplatedWidgetBase,CSSMixin,_StyleMixin,_InsertionMixin,KeyboardHandler,History],
            {
                delegate:null,
                value:null,
                editNode:null,
                labelTextNode:null,
                labelNode:null,
                type:null,
                linkToggle:null,

                templateString:"<div><div class='console' data-dojo-attach-point='root'>" +
                    '<table class="" border="0" cellpadding="0px" width="100%" height="100%" >'+

                    "<tbody data-dojo-attach-point='tBody' align='left'>"+

                    "<tr class='' >"+

                    "<td class='labelNode' data-dojo-attach-point='labelNode' class='' valign='middle' width='30px'>" +
                        "<span data-dojo-attach-point='labelTextNode'>#</span>" +
                    "</td>"+

                    "<td class='linkNode' data-dojo-attach-point='linkNode' class='' valign='middle' width='30px'>" +
                        "<button data-dojo-attach-point='linkToggle' data-dojo-type='dijit/form/ToggleButton' data-dojo-props=\"iconClass:'el-icon-link', checked: true\">"+
                    "</button>"+
                    "</td>"+

                    "<td  class='editNode' data-dojo-attach-point='editNode' class='' valign='middle' width='100%'>" +
                    "</td>"+

                    "</tr>"+
                    "</tbody>"+
                    "</table>" +


                    "</div></div>",
                isLinked:function(){
                    return this.linkToggle.get('checked');
                },
                createWidgets:function(){

                    this.editBox =factory.createEditBox(this.editNode,"",null,'', null, this);
                    var thiz=this;

                    this.editBox.on("keydown", function(evt){
                        thiz.onKey(evt);
                    });
                    this.editBox.on("keyup", function(evt){
                        thiz.onKey(evt);
                    });
                    domClass.remove(this.linkToggle.iconNode,'dijitReset');

                },
                getValue:function(){
                    return this.editBox.get('value');
                },
                startup:function()
                {
                    this.inherited(arguments);
                    this.createWidgets();
                },
                /***
                 * Keyboard impl.
                 */
                onUp:function(){
                    this.editBox.set('value',this.getPrev());
                },
                onDown:function(){
                    this.editBox.set('value',this.getNext());
                },
                onEnter:function(){
                    var value = this.editBox.get('value');
                    this.delegate.onEnter(value);
                    this.editBox.set('value','');
                    this.push(value);
                }
            });
    });