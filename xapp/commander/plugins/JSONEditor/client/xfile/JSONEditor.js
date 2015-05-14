/** @module xide/json/JSONEditor **/
define([
    'dojo/_base/declare',
    'dojo/_base/lang',
    './util',
    './TreeMode',
    'xide/mixins/EventedMixin',
    'xide/mixins/ReloadMixin',
    'xide/types',
    'xide/utils',
    'xide/bean/Action',
    'xide/widgets/_InsertionMixin',//used for render templates
    'xide/widgets/TemplatedWidgetBase',
    'xide/widgets/_CSSMixin'//used for render templates
    //'requirejs-dplugins/has',
    //"decor/Observable",   //render-template binding
    //"liaison/ObservablePath",   //render-template binding
    //"liaison/DOMBindingTarget", //render-template binding

    //'dojo/has!xexpression?xexpression/Expression',//expression stack
    //'dojo/has!filtrex?xexpression/filtrex'//filtrex expression(bison based)
], function (declare, lang, util, TreeMode, EventedMixin, ReloadMixin, types, utils, Action, _InsertionMixin, TemplatedWidgetBase,_CSSMixin,Observable, ObservablePath,DOMBindingTarget,Expression,filtrex) {
    /**
     * @TODOs
     *
     * - done: low-level API exposure
     * - done: constructor signature incompatible
     * - done: should be templated
     * - done: preserve collapse state during setData (hard reset)
     * - done: model rendering (field to Node title, etc...)
     *
     * - important: use inline ACE editor for editing fields, and share auto-completion for open
     * files!
     *
     * - take over rendering completely: important
     * - take over context menu to xide actions, adopt insertTemplates
     * - selection (multi)
     * - deep select
     * - xide editor API missing (model,...)
     * - controls incompatible
     * - search sucks
     * - bind insert templates to CI-Dialogs/Wizards
     *
     */


    /**
     *
     * Rework of the jsoneditoronline.org editor. This is being used as single widget or
     * part of a custom mime editor (split-editor with ACE as model feeder or output)
     *
     * @class module:xide/json/JSONEditor
     * @augments xide/mixin/EventedMixin
     * @augments xide/mixin/ReloadMixin
     * @augments xide/widgets/_CSSMixin
     * @augments xide/widgets/_InsertionMixin
     * @augments xide/model/Base
     */
    return declare("JSONEditor.JSONEditor", [TemplatedWidgetBase,_CSSMixin,TreeMode, _InsertionMixin, EventedMixin, ReloadMixin], {


        baseClass:"jsoneditor_widget",
        /**
         * a template for this widget
         */
        templateString: '<div style="height: inherit;width: inherit;overflow: hidden"></div>',
        /**
         * JSON-Editor-Options
         */
        options: null,

        /**
         * Default event map for module-introspection and event filtering
         */
        emits: {

            'visit': true,
            'setEditable': true,
            'render': true,
            'renderNode': true,
            'onAction':true,
            'event':true
        },
        /**
         * On bigger update actions like setData, this flag will make sure
         * that nodes which have been previously expanded get restored.
         */
        preserveExpandState:true,
        /**
         * temporary memory for expanded nodes, nodePath <=> boolean. This might go
         * public for the ctor arg?
         * @type {object}
         */
        _expanded:null,
        /**
         * Makes sure that the last nodePath gets its focus/selected state back on setData
         */
        preserveSelectedState:true,
        /**
         * last selected node, set in onEvent (???).
         * @type {HTMLElement[]}
         */
        _selected:null,
        /**
         * An array of insert templates, passed through the constructor's 'option' argument
         * @example:
         * [
         *      {
         *          label:"New Item",       // the label for the insert action
         *          value:"{test:2}",       // the value to insert, string or object
         *          newNodeTemplate:"[]'    // a parent template when the node for the insert is missing
         *          path:"blocks.0"         // a json-path like object path, xide-specific impl.
         *      }
         * ]
         * @type {Object[]}
         */
        insertTemplates: null,
        /***
         * An array of render templates to be injected when the Node's initial rendering occurred (onRenderNode)
         *
         * @example:
         *
         * [
         *  {

             //@type {string} the path within the dom structure
             nodeValuePath: 'field.innerHTML',

             //@type {RegExp|string|function|[]} a match function, string, regex or an array of these types
             match: /^variables[\s]?\.(\d+)$/g,

             // @type {string} the new value for the field specified in nodeValuePath
             replaceWith: '{nodeValue} - {title}',

             //@type {object} additional variables
             variables: null

         *   }
         * ]
         *
         * @type {object[]}
         */
        renderTemplates: null,
        /**
         * private map of visited nodes per path
         */
        _visitedNodes: {},
        /**
         * Read-only path map, passed through the constructor's 'option' argument
         */
        readOnlyNodes: {},
        /**
         * simple string object map to prevent rendering of nodes by field names
         *
         */
        hiddenFields:{},
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        //
        //  Original JSONEditor API/Impl. : to be removed/replaced
        //
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        /**
         * Configuration for all registered modes. Example:
         * {
         *     tree: {
         *         mixin: TreeEditor,
         *         data: 'json'
         *     },
         *     text: {
         *         mixin: TextEditor,
         *         data: 'text'
         *     }
         * }
         *
         * @type { Object.<String, {mixin: Object, data: String} > }
         */
        modes: {},
        /**
         *
         * @deprecated
         *
         * Create the JSONEditor
         *
         * @param {Element} container    Container element
         * @param {Object}  [options]    See description in constructor
         * @param {Object | undefined} json JSON object
         * @private
         */
        __create: function (container, options, json) {

            this.container = container;
            this.options = options || {};
            this.json = json || {};

            var mode = this.options.mode || 'tree';

            this.setMode(mode);

        },
        /**
         * @deprecated
         * Change the mode of the editor.
         * JSONEditor will be extended with all methods needed for the chosen mode.
         * @param {String} mode     Available modes: 'tree' (default), 'view', 'form',
         *                          'text', and 'code'.
         */
        setMode: function (mode) {

            var container = this.container,
                options = util.extend({}, this.options),
                data,
                name;

            options.mode = mode;

            try {
                var asText = false;//(config.data == 'text');

                name = this.getName();
                data = {};//this[asText ? 'getText' : 'getData'](); // get text or json

                this._create(container, options);

                this.setName(name);

                this[asText ? 'setText' : 'setData'](data); // set text or json

            }
            catch (err) {
                debugger;
                this._onError(err);
            }
        },
        /**
         * Throw an error. If an error callback is configured in options.error, this
         * callback will be invoked. Else, a regular error is thrown.
         * @deprecated
         * @param {Error} err
         * @private
         */
        _onError: function (err) {
            this._emit('error', err);
        },
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        //
        //  XIDE impl.
        //
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        /**
         * real-time playground
         */
        onReloaded: function () {

            this.insertTemplates = [];
            this._createInsertTemplate('New Command', 'blocks', "{test:2}", "commands",true,true);

        },
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        //
        //  private stuff for insert templates
        //
        /**
         *
         * @param label {string}
         * @param path {string}
         * @param value {object}
         * @param newNodeTemplate {string}
         * @param collapse {boolean}
         * @param select {boolean}
         * @private
         */
        _createInsertTemplate: function (label, path, value, newNodeTemplate,collapse,select) {

            this.insertTemplates.push({
                label: label,
                path: path,
                value: value,
                newNodeTemplate: newNodeTemplate,
                collapse:collapse,
                select:select
            });
        },
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        //
        //  Impl. bean action protocol
        //
        /**
         *
         * @returns {Array}
         */
        getItemActions: function () {

            var thiz = this,
                actions = [],
                VISIBILITY = types.ACTION_VISIBILITY;

            var _createAction = function (template) {

                var _insertAction = Action.create(template.label, 'fa-magic', 'Edit/Insert ' + template.label, false, null, types.ITEM_TYPE.TEXT, 'insertAction', null, false, function () {
                    thiz.insert(template.value, template.path, template.newNodeTemplate,template.collapse);
                }, {}).setVisibility(VISIBILITY.ACTION_TOOLBAR, {
                    label: '',
                    widgetArgs: {
                        style: 'float:right'
                    }
                });

                actions.push(_insertAction);
            };


            if (this.insertTemplates) {

                //head action (menu/combo-box)
                actions.push(Action.createDefault('New', 'fa-magic', 'Edit/Insert', 'insertAction', null, {
                    dummy: true
                }).setVisibility(VISIBILITY.ACTION_TOOLBAR, {label: ''}).
                    setVisibility(VISIBILITY.CONTEXT_MENU, null));


                //when created, move over to own toolbar
                actions[0]._on(VISIBILITY.ACTION_TOOLBAR + '_WIDGET_CREATED', function (evt) {
                    evt.widget.placeAt(thiz.menu, 'last');
                });

                _.each(this.insertTemplates, function (obj) {
                    _createAction(obj);
                }, this);

            }
            return actions;
        },
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        //
        //  Model API impl.
        //
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        /**
         * Insert data at a given path
         * @param what {string|object}
         * @param where {string} object path
         * @param newNodeTemplate {string|object} default object when we have no data in 'where'
         * @param collapse {boolean} collapse after insert
         * @param select {boolean} select(focus) after insert
         */
        insert: function (what, where, newNodeTemplate,collapse,select) {


            var data = this.getData() || {},
                nodeData = utils.getAt(data, where);

            //no data at this path, ensure it
            if (!nodeData) {
                var newData = utils.fromJson(newNodeTemplate) || [];
                nodeData = utils.ensureAt(data, where, newData);
            }

            //all good, we have data at this path; append per type
            if (nodeData != null) {

                if (_.isArray(nodeData)) {
                    nodeData.push(utils.fromJson(what));
                } else {
                    utils.setAt(nodeData, where, utils.fromJson(what));
                }
            }

            if(collapse && this.preserveExpandState){
                this._expanded[where]=true;
            }

            /**
             * @TODO: brutal set data for insert template, use JSONEditor::NodeAPI
             */
            this.setData(data);
        },

        /**
         * Default options for JSON editor, must be overridden carefully since this options
         * are used to keep the xide editor intact.
         * @returns {{editable: Function}}
         */
        getDefaultOptions: function () {

            var thiz = this;
            return {
                /**
                 * editable is a callback from JSONEditor, we mis-use this to track data per path, mapped to dom nodes
                 * but also this publish a local event.
                 * @param node
                 * @returns {boolean}
                 */
                editable: function (node) {

                    /*
                     // node is an object like:
                     //   {
                     //     field: 'FIELD',
                     //     value: 'VALUE',
                     //     path: ['PATH', 'TO', 'NODE']
                     //   }
                         switch (node.field) {
                             case '_id':
                                return false;
                             case 'name':
                                return {
                             field: false,
                                value: true
                             };
                             default:
                                return true;
                         }
                     */

                    //event filter
                    if (thiz._emit('setEditable', node) == false) {
                        return false;
                    }

                    //read-only filter
                    return !(node.path.join('.') in thiz.readOnlyNodes);
                }
            };
        },
        /**
         * The method onChange is called whenever a field or value is changed, created,
         * deleted, duplicated, etc.
         *
         * @param evt {object} the event data
         *
         * @param evt.action {String} Change action. Available values: "editField",
         *                         "editValue", "changeType", "appendNode",
         *                         "removeNode", "duplicateNode", "moveNode", "expand",
         *                         "collapse".
         *
         * @param evt.params {Object} Object containing parameters describing the change.
         *                         The parameters in params depend on the action (for
         *                         example for "editValue" the Node, old value, and new
         *                         value are provided). params contains all information
         *                         needed to undo or redo the action.
         * @private
         */
        onAction:function(evt){

            var node = evt.params.node,
                path = node.path().join('.'),
                action = evt.action;

            //forward
            this._emit('on' + utils.capitalize(evt.action),{node:node,params:evt.params});


            switch(evt.action){
                case "editValue":
                case "editField":
                case "removeNode":{
                    /*var a = this.hasTemplate(node);
                    var b = this.hasTemplate(node.parent);*/
                    var _node = node.parent ? node.parent : node;
                    if(this.getRenderTemplates(_node.path())){
                        this.onRenderNode(_node,true,evt.params.newValue);
                    }
                    break;
                }
                case "collapse":
                case "expand":{
                    this._expanded["" + path] = action == 'expand';
                    break;
                }
            }
        },
        _matches:function(nodePath,match){},
        /**
         *
         * @param nodePath
         * @returns {boolean}
         */
        getRenderTemplates:function(nodePath){

            /**
             *
             * @param nodePath
             * @param match
             */
            function testMatch(nodePath,match){
                return (typeof match ==='function' && match(nodePath)===true) //try function
                        || (match.test && match.test(nodePath)) //try reg-ex
                        || match === nodePath; //try string eq
            }

            var result = [],
                templates = this.renderTemplates;

            for (var i = 0; i < templates.length; i++) {

                var template = templates[i],
                    match = template.match;

                if(!_.isArray(match)){

                    if(testMatch(nodePath,match)){
                        result.push(template);
                    }

                }else{

                    for (var j = 0; j < match.length; j++) {

                        var _t = match[j].test(nodePath);
                        if(testMatch(nodePath,match[j])){
                            result.push(template);
                        }
                    }
                }
            }
            return result;

        },
        /**
         * Event callback for rendering, used to inject render templates
         * @param node {xide/json/Node}
         */
        onRenderNode: function (node,reset,forceValue) {

            if(!this.renderTemplates){
                return;
            }

            //the data path of the node
            var nodePath = node.path().join('.'),
                //hash for this process
                evtHash = '_'+this.id;

            //track
            this._visitedNodes[nodePath] = node;


            //memorize treat
            node[evtHash] = true;

            //narrow query scope to the node's dom data
            var queryObject = node.dom;

            if (queryObject) {


                var renderTemplates = this.getRenderTemplates(nodePath);
                if (renderTemplates){

                    for (var i = 0; i < renderTemplates.length; i++) {

                        var template = renderTemplates[i],

                        //pick value
                        nodeValue = utils.getAt(queryObject, template.nodeValuePath, null) || "";

                        if (reset == true) {
                            utils.setAt(queryObject, template.nodeValuePath, "");
                            nodeValue = forceValue || "";
                        }


                        //transform node value
                        if (template.nodeValueTransform) {
                            nodeValue = template.nodeValueTransform(nodeValue);
                        }

                        //pick node data
                        var nodeData = utils.getAt(this.data, nodePath, {}),

                        //build template variable map
                        variables = {
                            nodeValue: nodeValue
                        };

                        //copy node data into variables
                        for (var prop in nodeData) {
                            variables[prop] = nodeData[prop];
                        }

                        //copy extra variables
                        if (template.variables) {
                            for (var prop in template.variables) {
                                variables[prop] = template.variables[prop];
                            }
                        }

                        //build value
                        var newValue = utils.replace(template.replaceWith, null, variables, {
                                begin: "{",
                                end: "}"
                            }
                        );

                        //update
                        utils.setAt(queryObject, template.nodeValuePath, newValue);
                    }

                }
            }


            //restore collapse state
            if(this.preserveExpandState && this._expanded["" +nodePath] ===true){
                node.expand(false);
            }

            /* next: insertions & css jobs
             var inserts ={
                query: '.dijitDialogPaneContent',
                insert: '<div><span class="fileManagerDialogText">Do you really want to remove  this items' + '?</span></div>',
                place: 'first'
             };

             */


        },
        onAddNode:function(data){
            return true;
        },
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        //
        //  std.
        //
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        startup: function () {

            this.inherited(arguments);

            var options = this.options || {};

            var thiz = this;

            lang.mixin(options, this.getDefaultOptions());

            lang.mixin(this, options);

            this._on('renderNode', function (node) {
                thiz.onRenderNode(node);
            });

            this._on('onAction', function (evt) {
                thiz.onAction(evt);
            });

            this._on('event', function (evt) {
                //console.log(arguments);
            });


            /*
            this.readOnlyNodes = {
                "blocks":true
            };
            */

            /*
            this.hiddenFields = {
                "enabled":true,
                "blocks":true
            };
            */

            this._visitedNodes = {};
            this._expanded = {};

            this.renderTemplates = [
                {
                    //
                    //  This segment is used to replace something in the node's dom structure
                    //

                    /**
                     * @type {string} the path within the dom structure
                     */
                    nodeValuePath: 'field.innerHTML',
                    /**
                     * @type {RegExp|string|function|RegExp[]|string[]|function[]}
                     */
                    match: [/^variables[\s]?\.(\d+)$/,/^blocks[\s]?\.(\d+)$/],
                    /**
                     * @type {string} the new value for the field specified in nodeValuePath
                     */
                    replaceWith: '{nodeValue} - {title}',
                    /**
                     * @type {object} additional variables
                     */
                    variables: null,

                    /**
                     * @type {function} a function to transform the node's dom value into something else
                     */
                    nodeValueTransform:function(value){
                        return utils.capitalize(value);
                    },
                    //
                    //  This segment is about dom manipulation, todo!
                    //
                    /**
                     * @type (object)
                     */
                    insertIfMatch:{}
                }

            ];

            this.__create(this.domNode, options, this.data);

            this.initReload();

            /*
             this._on('setSelection',function(selection){
             console.log('change selection : ',selection);
             });
             */

            //this.renderTemplates = {};

        }
    });
});
