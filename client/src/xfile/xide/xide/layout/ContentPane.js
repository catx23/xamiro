define([
    'dcl/dcl',
    "dojo/_base/array",
    "dojo/dom-style",
    "dojo/dom-geometry", // domGeometry.position
    'xide/types',
    'xide/mixins/EventedMixin'
], function (dcl, array, domStyle, registry, domGeometry, types, Container) {
    return dcl(Container, {
        declareClass: "xide.layout.ContentPane",
        cssClass: "layoutContainer",
        region: 'center',
        closable: false,
        adjustChildsToWidth: false,
        didClose: false,
        isOpen: function () {
            return this.didClose !== true;
        },
        onToggleSplitter: function (state) {
        },
        sameWidth: function () {

            var max = 0;
            var children = this.containerNode.children;
            for (var i = 0; i < children.length; i++) {
                var child = children[i];
                if (!child.style) {
                    continue;
                }
                domStyle.set(child, {
                    width: 'auto'
                });
                var box = domGeometry.getContentBox(child);
                if (box.w > max) {
                    max = box.w;
                }
            }

            max -= 15;

            for (var i = 0; i < children.length; i++) {
                var child = children[i];

                if (!child.style) {
                    continue;
                }

                domStyle.set(child, {
                    width: max + 'px'
                });
            }
        },
        onResize: function () {

            if (this.adjustChildsToWidth) {
                this.sameWidth();
            }

            array.forEach(registry.findWidgets(this.containerNode), function (child) {
                if (child.onResize) {
                    child.onResize();
                }
            });
        },
        getOnShowTarget: function () {
            return this;
        },
        onShow: function () {

            this.inherited(arguments);
            this.resize();

            if (this.adjustChildsToWidth) {
                this.sameWidth();
            }

            if (this.onResize) {
                this.onResize();
            }

            this._emit(types.EVENTS.ON_VIEW_SHOW, {
                view: this.getOnShowTarget(),
                item: this.item
            });
            _.each(registry.findWidgets(this.containerNode), function (child) {
                if (child.onShow) {
                    child.onShow();
                }
            });

        },
        _close: function () {
            var splitter = this._splitterWidget;
            if (splitter) {
                switch (splitter.state) {
                    case "collapsed":
                    case "full":
                        splitter.set("state", 'closed');
                }

                this.publish(types.EVENTS.RESIZE, {}, this);
            }
        },
        _open: function () {
            var splitter = this._splitterWidget;
            if (splitter) {
                switch (splitter.state) {
                    case "full":
                        break;
                    case "collapsed":
                    case "closed":
                    {
                        splitter.set("state", 'full');

                        this.publish(types.EVENTS.RESIZE, {}, this);

                        break;
                    }
                }
            }
        },
        startup: function () {
            if (this._started) {
                return;
            }

            this.inherited(arguments);
            var splitter = this._splitterWidget,
                self = this;

            if (splitter) {

                splitter._on('splitterMoveEnd', function (e) {
                    self._emit('splitterMoveEnd');
                });

                splitter._on('splitterMoveStart', function (e) {
                    self._emit('splitterMoveStart');
                });
            }

        }
    });
});