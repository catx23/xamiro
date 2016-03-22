/***
 *
 *  Plugin main entry file.
 *  Also used at compile time to include all the plugin's dependencies. *
 *  Remarks : please add all your includes here instead of defining them in sub modules.
 */
define([
    'dojo/_base/declare',
    'xide/types',
    'xide/utils',
    'xide/bean/Action',
    'xide/views/CIActionDialog',
    'xide/model/Component'], function (declare, types,utils,Action,CIActionDialog,Component) {

    /**
     * @class xfile.component
     * @inheritDoc
     */
    return declare([Component], {
        item:null,
        ctx:null,
        getLabel:function(){
            return 'nada';
        },
        openShare:function(item){
            var thiz = this,
            options = {
                selector: '.ssk',
                url: 'http://my-url',
                text: 'Share text default',
                twitter: {
                    url: 'http://url-for-twitter',
                    text: 'Share text for twitter',
                    via: 'twitter-screen-name'
                }
            };

            var url = this.ctx.getFileManager().getImageUrl(item);

            var actionDialog = new CIActionDialog({
                title: 'Share',
                style: 'width:300px;min-height:200px;',
                resizeable: true,
                delegate: {
                    onOk: function (dlg, data) {

                        var event = data[0].value;
                        thiz.addNewBlockGroup(event);
                    }
                },
                cis: [
                    utils.createCI('Url', types.ECIType.STRING, url, {
                        widgetArgs:{
                            readOnly:true
                        }
                    })
                ],
                viewArgs:{
                    inserts: [{
                        query: '.dijitDialogPaneContent',
                        insert: '<div style="padding: 16px;margin:auto 0"  class="ssk-group ssk-count">'+
                        '<a href="" class="ssk ssk-facebook"></a>'+
                        '<a href="" class="ssk ssk-twitter"></a>'+
                        '<a href="" class="ssk ssk-google-plus"></a>'+
                        '<a href="" class="ssk ssk-pinterest"></a>'+
                        '<a href="" class="ssk ssk-linkedin"></a>'+
                        '<a href="" class="ssk ssk-email"></a>'+
                        '</div>',
                        place: 'first'
                    }]
                }
            });
            actionDialog.show();


            var title = item.name;

            var options = {
                selector: '.ssk',
                url: url,
                text: title,
                twitter: {
                    url: url,
                    text: title
                }
            };

            if(utils.isImage(item.path)){
                options.image=url;
            }

            SocialShareKit.init(options);




        },
        onRenderActions:function(evt){

            var actions=evt.actions,
                item = evt.item,
                thiz = this;

            if(!item || !item.path || !item._S){
                return;
            }

            if(item){

                var mimes = [
                    'video/mp4',
                    'video/x-m4v',
                    'video/ogg',
                    'application/ogg',
                    'application/octet-stream',
                    'video/webm',
                    'audio/mpeg',
                    'audio/mpeg3',
                    'audio/mp3',
                    'audio/x-mpeg3',
                    'audio/x-mp3',
                    'audio/x-wav',
                    'audio/wav',
                    'audio/x-m4a',
                    'audio/aac',
                    'audio/mp4',
                    'audio/x-mp4',
                    'audio/ogg',
                    'application/x-empty',
                    'application/javascript',
                    'application/xhtml+xml',
                    'audio/x-mp3-playlist',
                    'application/x-web-config',
                    'application/docbook+xml',
                    'application/x-php',
                    'application/x-perl',
                    'application/x-awk',
                    'application/x-config',
                    'application/x-csh',
                    'application/xml',
                    'application/x-empty',
                    'text/html',
                    'text/css',
                    'text/x-c',
                    'text/x-php',
                    'text/plain',
                    'text/x-c++',
                    'text/x-lisp'
                ];

                $.each(navigator.plugins, function (i, plugins) {
                    $.each(plugins, function (i, plugin) {
                        (plugin.type.indexOf('audio/') === 0 || plugin.type.indexOf('video/') === 0) && mimes.push(plugin.type);
                    });
                });

                //image
                if (utils.isImage(item.path) || $.inArray(item.mime, mimes) !== -1) {

                    //share action:
                    var _share = Action.create('Share', 'fa-share-alt-square', 'File/Share', false, null, types.ITEM_TYPE.FILE, 'fileAction', null, true, function(){
                        thiz.openShare(item);
                    });
                    _share.accelKey = '';
                    _share.setVisibility(types.ACTION_VISIBILITY.ACTION_TOOLBAR,{label:''});
                    actions.push(_share);

                }

            }
        },

        onItemSelected:function(evt){
            if(evt.item && evt.item._S){
                this.item = evt.item;
            }
        },
        setup:function(){
            this.subscribe([types.EVENTS.ITEM_SELECTED,types.EVENTS.ON_RENDER_ACTIONS]);
        },
        run: function (ctx) {


            console.error('run share loaded');

            this.ctx = ctx;

            var _res = this.inherited(arguments);
            this.setup();
            return _res;
        }
    });
});