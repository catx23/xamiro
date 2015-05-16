define([
    'dojo/_base/declare',
    'dojo/_base/lang',
    'xide/utils',
    'xide/types',
    'xide/factory',
    'xide/manager/ManagerBase',
    'xide/layout/ContentPane',
    'xide/views/SplitEditor',
    './JSONEditor'

], function (declare, lang, utils, types, factory, ManagerBase, ContentPane, SplitEditor, JSONEditor) {
    return declare("JSONEditor.JSONEditorManager", [ManagerBase], {
        mainView: null,
        ctx: null,
        config: null,
        panelManager: null,
        fileManager: null,
        didRegister: false,
        registerEditor: function () {

            if (this.didRegister) {
                return;
            }
            var thiz = this;

            this.didRegister = true;

            this.ctx.registerEditorExtension('JSON Editor', 'json', 'fa-code', this, false, null, SplitEditor, {
                updateOnSelection: false,
                leftLayoutContainer: this.ctx.mainView.leftLayoutContainer,
                ctx: this.ctx,
                rightEditorArgs:null,
                leftEditorArgs:null
            }, {
                subscribers:[

                ],
                /**
                 * this goes into _designCP
                 * @param where
                 * @param who
                 * @param item
                 * @returns {widgetProto|*|*}
                 */
                createLeftView: function (where, who, item) {


                    var pane = factory.createPane('', '', where, {}, null);

                    var editor = pane.editor = utils.addWidget(JSONEditor, this.leftEditorArgs, this, pane.containerNode, true);

                    pane.getItemActions = function () {
                        return editor.getItemActions();
                    };
                    pane.setValue = function (value) {
                        var _json = dojo.fromJson(value);
                        editor.setData(_json);
                    };
                    pane.getValue = function () {
                        return JSON.stringify(editor.getData(), null, 2);
                    };

                    return pane;

                },
                /**
                 * this goes into _srcCP
                 * @param where
                 * @param who
                 * @param item
                 * @returns {widgetProto|*|*}
                 */
                createRightView: function (where, who, item) {


                    var editorArgs = {
                        region: 'bottom',
                        splitter: true,
                        style: "height:40%;padding:0px;overflow:hidden;",
                        emits: {
                            'onViewShow': false,
                            'onItemSelected': false
                        },
                        autoSelect: false
                    };


                    if (this.rightEditorArgs) {
                        lang.mixin(editorArgs, this.rightEditorArgs);
                    }

                    var editor = this.ctx.getScriptManager().onFileClicked(item, where, editorArgs, false);


                    var thiz = this;

                    editor._on('setFirstContent', function (evt) {
                        thiz.setLeftValue(evt.value);
                    });

                    return editor;

                },
                startup: function () {
                    this.inherited(arguments);
                }

            });


        },
        _registerListeners: function () {
            this.inherited(arguments);
            this.subscribe([types.EVENTS.ITEM_SELECTED, types.EVENTS.ON_MAIN_VIEW_READY]);
        },
        _constructor: function () {
            this.id = utils.createUUID();
            this._registerListeners();

        },
        init: function () {

            try {
                this.registerEditor();
            } catch (e) {
                console.error('error in json-editor : ' + e, e);
            }
        }
    });
});