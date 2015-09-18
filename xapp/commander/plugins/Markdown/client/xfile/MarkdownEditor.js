define([
    'dojo/_base/declare',
    'xide/utils',
    'xide/types',
    'xide/factory',
    'xide/layout/ContentPane',
    'xide/Document',
    'xide/views/BeanView',
    'xide/views/_EditorMixin',
    'xide/views/_ActionMixin',
    "dojo/text!./stackeditor-release/public/editorRelease.html"
], function (declare, utils, types, factory, ContentPane, Document, BeanView, _EditorMixin, _ActionMixin, editorHTML) {

    return declare("Markdown.MarkdownEditor", [BeanView, _EditorMixin, _ActionMixin], {
        _markdown: null,
        content: null,
        fileMgr: null,
        //////////////////////////////////////////////////////////////////////
        //
        //  Impl. stack-edit calls
        //
        //
        getTitle: function () {//called in stackedit
            return this.item.name;
        },
        selectFile: function (fmgr) {//called in stackedit
            this.fileMgr = fmgr;
        },
        //////////////////////////////////////////////////////////////////////
        //
        //  local privates
        //
        //
        _getMarkdownContent: function () {
            return this.fileMgr.currentFile.content
        },

        //////////////////////////////////////////////////////////////////////
        //
        //  Impl. bean interface
        //
        //
        getActions: function () {


            var actions = [],
                thiz = this,
                container = this.doc ? this.doc.getDocument() : this.containerNode,
                ACTION_TYPE = types.ACTION,
                ACTION_ICON = types.ACTION_ICON;

            //save
            actions.push(this.createActionParameters('Save', ACTION_TYPE.SAVE, 'file', ACTION_ICON.SAVE, function () {
                thiz.set('iconClass', thiz.loadingIcon);
                thiz.saveContent(thiz._getMarkdownContent(), thiz.item, function () {
                    thiz.onLoaded();
                });
            }, 'CTRL + S', 'ctrl s', types.KEYBOARD_PROFILE.SEQUENCE, container, thiz));


            return actions;

        },
        hasItemActions: function () {
            return true;
        },
        onItemClick: function () {

            this.publish(types.EVENTS.ON_ITEM_SELECTED, {
                item: this.item,
                owner: this,
                beanContextName: this.beanContextName
            })
        },



        startup: function () {

            this.inherited(arguments);
            var resourceVariables = this.ctx.getResourceManager().getResourceVariables();
            resourceVariables.baseUrl = resourceVariables.XCOM_PLUGINS_WEB_URL + 'Markdown/client/xfile/stackeditor-release/public/';
            var thiz = this;

            this.getContent(this.item, function (content) {

                thiz.content = content;

                thiz.set('iconClass', thiz.loadingIcon);

                thiz.doc = utils.addWidget(Document, {
                    template: editorHTML,
                    resourceVariables: resourceVariables
                }, thiz, thiz.domNode, true);

                var global = thiz.doc.getGlobal();
                global.delegate = thiz;

                global.onReady = function (evt) {
                    thiz._markdown = evt;
                    thiz.onLoaded();
                    thiz.resetActions();

                    //forward click
                    $(global).click(function () {
                        thiz.onItemClick();
                    });
                };
                //thiz.doc._on('ready', function (evt) {});
            });
        }
    });
});