define([
    'dojo/_base/declare',
    'xide/utils',
    'xide/types',
    'xide/factory',
    'xide/manager/ManagerBase',
    './MarkdownEditor'
], function (declare,
             utils,
             types,
             factory,
             ManagerBase,
             MarkdownEditor)
{
    return declare("Markdown.MarkdownManager", [ManagerBase],
        {
            mainView:null,
            ctx:null,
            config:null,
            panelManager:null,
            fileManager:null,
            currentItem:null,
            didRegister:false,
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

            registerEditor:function(){

                if(this.didRegister){
                    return;
                }
                this.didRegister=true;

                this.ctx.registerEditorExtension('Markdown', 'md', 'fa-code', this, false, null, MarkdownEditor, {
                    updateOnSelection: false,
                    leftLayoutContainer: this.ctx.mainView.leftLayoutContainer,
                    ctx: this.ctx
                }, {

                });

            },
            _registerListeners:function () {
                this.inherited(arguments);
                this.subscribe([types.EVENTS.ITEM_SELECTED,types.EVENTS.ON_MAIN_VIEW_READY]);
            },
            _constructor:function () {
                this.id=utils.createUUID();
                this._registerListeners();

            },
            init:function(){

                try {
                    this.registerEditor();
                }catch(e){
                    console.error('error in json-editor : ' + e , e);
                }
            }
        });
});