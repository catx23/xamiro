define([
    'dojo/_base/lang',
    'dojo/_base/declare',
    './JSONEditorManager',
    'xide/types',
    'xide/model/Component'], function (lang, declare, JSONEditorManager, types, Component) {
    return declare([Component], {
        run: function () {

            var _res = this.inherited(arguments);
            var _init = function (eventData) {

                if (eventData.name !== 'JSONEditor') {//not for us
                    return;
                }
                var ctrArgs = {};
                lang.mixin(ctrArgs, eventData);
                var mgr = new JSONEditorManager(ctrArgs);
                mgr.init();
            };

            this.subscribe(types.EVENTS.ON_PLUGIN_READY, _init);
            this.publish(types.EVENTS.ON_PLUGIN_LOADED, {
                name: 'JSONEditor'
            });
            return _res;
        }
    });
});