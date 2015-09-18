define([
    'dojo/_base/lang',
    'dojo/_base/declare',
    './MarkdownManager',
    'xide/types',
    'xide/model/Component'], function (lang, declare, MarkdownManager, types, Component) {
    return declare([Component], {
        run: function (ctx) {

            var _res = this.inherited(arguments);
            var mgr = new MarkdownManager({
                ctx:ctx
            });
            mgr.init();
            return _res;
        }
    });
});