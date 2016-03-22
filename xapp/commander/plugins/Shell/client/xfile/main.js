/***
 *
 *  Plugin main entry file.
 *  Also used at compile time to include all the plugin's depenedencies. *
 *  Remarks : please add all your includes here instead of defining them in submodules.
 */
define([
    'dojo/_base/lang',
    'dojo/_base/declare',
    './ShellManager',
    'xide/factory',
    'xide/types',
    'xide/model/Component'], function (lang,declare, ShellManager, factory, types, Component) {
    /**
     * @class xfile.component
     * @inheritDoc
     */
    return declare([Component], {
        run: function () {
            var _res = this.inherited(arguments);
            var _init = function (eventData) {
                var ctrArgs = {};
                if (eventData.name !== 'Shell') {//not for us
                    return;
                }
                lang.mixin(ctrArgs, eventData);
                if (!ctrArgs.fileManager) {
                    ctrArgs.fileManager = xfile.getContext().getFileManager();
                }
                new ShellManager(ctrArgs);
            };
            factory.subscribe(types.EVENTS.ON_PLUGIN_READY, _init, this);
            factory.publish(types.EVENTS.ON_PLUGIN_LOADED, {
                name: 'Shell'
            }, this);
            return _res;
        }
    });
});