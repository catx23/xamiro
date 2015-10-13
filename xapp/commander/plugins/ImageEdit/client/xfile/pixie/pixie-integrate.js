(function (window, undefined) {
    'use strict';

    var document = window.document, Pixie, _pixie, instance;

    Pixie = function() {

        _pixie.start();

        instance = {
            save: function(format, quality) {
                _pixie.save(format, quality); return this;
            },
            getService: function(name) { return _pixie.getService(name); },
            //start: function(options) { _pixie.start(options); return this },
            close: function() { _pixie.close(); return this },
            open: function(options) { _pixie.open(options); return this },
            enableInteractiveMode: function(options) { _pixie.enableInteractiveMode(options); return this },
            getParams: function() { return _pixie.params },
            setOptions: function(options) { _pixie.setOptions(options); return this }
        };

        return instance;
    };

    _pixie = {

        params: {
            url: false,
            enableCORS: false,
            path: 'pixie',
            saveUrl: false,
            replaceOriginal: false,
            forceLaundering: false,
            appendTo: 'body',
            noManualClose: false,
            openDelay: 400,
            closeDelay: 400,
            onError: function(err) { console.log(err) },
            onLoad: function() {},
            onSave: function() {},
            onClose: function() {},
            onSaveButtonClick: null
        },

        errors: {
            invalidImg: "Image you passed in doesn't exist or can't be loaded"
        },

        loaded: false,

        cache: {},

        setOptions: function(options) {
            this.extend(_pixie.params, options);
        },

        save: function(format, quality) {
            debugger;
            if (_pixie.loaded) {
                _pixie.getService('saver').saveImage(format, quality);
            }
        },

        getService: function(name) {
            if ( ! _pixie.injector) {
                _pixie.injector = _pixie.frameWindow.angular.element(_pixie.frameWindow.document.body).injector();
            }

            return _pixie.injector.get(name);
        },

        start: function() {
            var data = document.currentScript.dataset;

            if (typeof data.path !== 'undefined') {
                _pixie.params.path = document.currentScript.dataset.path;
            }

            if (data.preload && ! _pixie.loaded) {
                window.onload = function() {
                    _pixie.create();
                    window.onload = null;
                };
            }
        },

        /**
         * Automatically open images in editor on click within passed in container or whole page.
         *
         * @param params
         */
        enableInteractiveMode: function(params) {
            params = params || {};

            var selector  = params.selector || 'img',
                container = params.container || '',
                preventDefault = params.preventDefault || true;

            (container || document).addEventListener('click', function(e) {
                if (e.target.nodeName !== selector && e.target.className.indexOf(selector) === -1) return;

                if (preventDefault) {
                    e.preventDefault();
                }

                var node = e.target;

                if (node.nodeName !== 'IMG') {
                    node = e.target.querySelector('img');
                }

                if (node && node.nodeName === 'IMG') {
                    _pixie.params.image = node;
                    _pixie.open({ url: _pixie.params.image.src });
                }
            });
        },

        /**
         * Launder image using php to avoid problems with
         * tainted canvas when loading images through cross domain.
         *
         * @param url
         * @returns Promise|String
         */
        launderImage: function(url) {

            //if CORS is enabled or image is at the same domain
            // no need to fetch image through domains so we can just return the original url
            if (_pixie.params.enableCORS || ( ! _pixie.params.forceLaundering && _pixie.urlInSameDomain(url))) {
                return url;
            }

            //check cache to see if we have already laundered this image
            if (_pixie.cache[url]) {
                return _pixie.cache[url];
            }

            //if CORS is not enabled we will need to use php to fetch
            //the image at given url and then return its data in base64
            var request = _pixie.ajax(_pixie.getPath()+'launderer.php', 'url='+_pixie.absoluteUrl(url));

            request.onload = function(e) {
                if (e.target.status >= 200 && e.target.status < 400) {
                    _pixie.cache[url] = e.target.responseText;
                }
            };

            return request;
        },

        close: function() {
            _pixie.fadeOut(_pixie.container, {
                duration: _pixie.params.closeDelay,
                complete: function() {
                    _pixie.container.style.display = 'none';
                }
            });

            document.body.classList.remove('noscroll');

            this.params.onClose();
        },

        open: function(options) {
            if (this.loading) return;

            this.extend(_pixie.params, options);

            document.body.classList.add('noscroll');

            if ( ! this.loaded) {
                _pixie.create(_pixie.loadImage);
            } else {
                _pixie.loadImage()
            }

            _pixie.container.style.display = 'block';

            if (_pixie.loaded) {
                _pixie.fadeIn(_pixie.container, {
                    duration: _pixie.params.openDelay
                });

            }
        },

        loadImage: function() {
            _pixie.imageExists(_pixie.params.url, function(exists) {
                if (exists) {
                    var resp = _pixie.launderImage(_pixie.params.url);

                    //load the image after ajax request is done
                    if (typeof resp === 'object') {
                        resp.onload = function(e) {
                            if (e.target.status >= 200 && e.target.status < 400 && e.target.responseText) {
                                _pixie.params.url = e.target.responseText;
                                _pixie.rootScope.canvas.fabric.clear();
                                _pixie.rootScope.canvas.resetZoom();
                                _pixie.rootScope.canvas.loadMainImage(e.target.responseText);
                                _pixie.rootScope.$apply(function() {
                                    _pixie.rootScope.started = true;
                                });
                            }
                        };
                    }

                    //no laundering was required so we can just load the image now
                    else if (typeof resp === 'string') {
                        _pixie.rootScope.canvas.fabric.clear();
                        _pixie.rootScope.canvas.resetZoom();
                        _pixie.rootScope.canvas.loadMainImage(resp);
                        _pixie.rootScope.$apply(function() {
                            _pixie.rootScope.started = true;
                        });
                    }
                } else {
                    _pixie.error('invalidImg', _pixie.params.url);
                }
            });
        },

        create: function(callback) {
            this.loading = true;

            var markup = '<section id="pixie-editor-container"><div id="pixie-frame-container"><div id="pixie-editor-header">Pixie Editor<div class="pixie-close">&times;</div></div></div></section>',
                link   = document.createElement('link'); link.rel = 'stylesheet'; link.href = this.getPath()+'assets/css/integrate.css';

            document.querySelector('head').appendChild(link);

            var wrapper = document.createElement('div');
            wrapper.innerHTML = markup;

            this.container = document.querySelector(_pixie.params.appendTo).appendChild(wrapper.firstChild);
            this.container.style.display = 'none';

            this.frame = document.createElement('iframe');

            this.container.querySelector('#pixie-frame-container').appendChild(this.frame);

            this.frame.onload = function() {
                _pixie.frameWindow = _pixie.frame.contentWindow;
                _pixie.rootScope   = _pixie.frameWindow.angular.element('#body').scope();
                _pixie.rootScope.pixie=_pixie;

                _pixie.loaded = true;
                _pixie.loading = false;

                _pixie.params.onLoad(_pixie.container, _pixie.rootScope, _pixie.frameWindow);
                callback && callback();
            };

            this.frame.src = this.getPath()+'index.html';

            if ( ! _pixie.params.noManualClose) {
                document.addEventListener('click', function(e) {
                    if (e.target.id === 'pixie-editor-container' || e.target.className === 'pixie-close') {
                        _pixie.close();
                    }
                });
            } else {
                document.querySelector('.pixie-close').style.display = 'none';
            }
        },

        imageExists: function(url, callback) {
            var img = new Image();
            img.onload = function() { callback(true); };
            img.onerror = function() { callback(false); };
            img.src = url;
            img = null;
        },

        urlInSameDomain: function(url) {
            return url.indexOf(document.domain) > -1 || url.indexOf('//') === -1;
        },

        error: function(name, args) {
            _pixie.params.onError({
                message: _pixie.errors[name],
                args: args
            });
        },

        getPath: function() {
            return this.params.path ? this.params.path+'/' : '';
        },

        absoluteUrl: function(url) {
            if (url.indexOf('//') > -1) return url;

            var a = document.createElement('a');
            a.href = url;
            return a.href;
        },

        extend: function(out) {
            out = out || {};

            for (var i = 1; i < arguments.length; i++) {
                var obj = arguments[i];

                if (!obj)
                    continue;

                for (var key in obj) {
                    if (obj.hasOwnProperty(key)) {
                        if (typeof obj[key] === 'object' && obj.constructor == Object)
                            _pixie.extend(out[key], obj[key]);
                        else
                            out[key] = obj[key];
                    }
                }
            }

            return out;
        },

        ajax: function(url, data) {
            var request = new XMLHttpRequest();
            request.open('POST', url, true);
            request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            request.send(data);

            return request;
        },

        animate: function(options) {
            var start = new Date;
            var id = setInterval(function() {
                var timePassed = new Date - start;
                var progress = timePassed / options.duration;
                if (progress > 1) {
                    progress = 1;
                }
                options.progress = progress;
                var delta = options.delta(progress);
                options.step(delta);
                if (progress == 1) {
                    clearInterval(id);
                    options.complete && options.complete();
                }
            }, options.delay || 10);
        },

        fadeOut: function(element, options) {
            var to = 1;
            _pixie.animate({
                duration: options.duration,
                delta: function(progress) {
                    return 0.5 - Math.cos(progress * Math.PI) / 2;
                },
                complete: options.complete,
                step: function(delta) {
                    element.style.opacity = to - delta;
                }
            });
        },

        fadeIn: function(element, options) {
            var to = 0;
            _pixie.animate({
                duration: options.duration,
                delta: function(progress) {
                    return 0.5 - Math.cos(progress * Math.PI) / 2;
                },
                complete: options.complete,
                step: function(delta) {
                    element.style.opacity = to + delta;
                }
            });
        }
    };

    window.Pixie = new Pixie();

}(this));