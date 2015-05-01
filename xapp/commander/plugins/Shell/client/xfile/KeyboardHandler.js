define([
    'dojo/_base/declare',
    "dojo/keys",
    "dojo/has"
],
    function (declare,keys,has)
    {
        return declare("Shell.xfile.KeyboardDelegate",null,
            {


                ctlrKeyDown:false,
                onKeyUp:function(evt){

                    switch(evt.keyCode){
                        case keys.META:
                        case keys.SHIFT:
                        case keys.ALT:
                        case keys.CTRL:{
                            this.ctlrKeyDown=false;
                            break;
                        }
                    }
                },
                onKey:function(evt){

                    var thiz=this;
                    if(evt.type && evt.type=='keyup'){
                        return this.onKeyUp(evt);
                    }

                    var vKey = 86, cKey = 67, cutKey=88;

                    if(evt.keyCode == keys.ENTER){

                        if(this.ctlrKeyDown){
                            if(this.openItemAlternate){
                                evt.preventDefault();
                                this.openItemAlternate(null);
                            }
                        }else{

                            this.onEnter();
                            evt.preventDefault();
                        }

                    }else{
                        switch(evt.keyCode){

                            case keys.UP_ARROW:{
                                this.onUp();
                                break;
                            }
                            case keys.DOWN_ARROW:{
                                this.onDown();
                                break;
                            }
                            case keys.META:
                            case keys.ALT:
                            case keys.SHIFT:
                            case keys.CTRL:{
                                this.ctlrKeyDown=true;
                                setTimeout(function(){
                                    thiz.ctlrKeyDown=false
                                },2000);
                                break;
                            }
                            case keys.TAB:{
                                if(this.onTab){
                                    this.onTab(evt);
                                    evt.preventDefault();
                                }
                                break;
                            }
                            case keys.BACKSPACE:
                            {
                                var doBack = true;
                                if(has('chrome') && this.ctlrKeyDown==false){
                                    doBack=false;
                                }
                                if(doBack){
                                    if(this.onBack){
                                        evt.preventDefault();
                                        this.onBack();
                                    }
                                }
                                break;
                            }
                            case cKey:{
                                if(this.ctlrKeyDown && this.onClipBoardCopy){
                                    evt.preventDefault();
                                    this.onClipBoardCopy();
                                }
                                break;
                            }
                            case vKey:{
                                if(this.ctlrKeyDown && this.onClipBoardPaste){
                                    evt.preventDefault();
                                    this.onClipBoardPaste();
                                }
                                break;
                            }
                            case cutKey:{
                                if(this.ctlrKeyDown && this.onClipBoardCut){
                                    evt.preventDefault();
                                    this.onClipBoardCut();
                                }
                                break;
                            }
                        }
                    }
                }

            });
    })
;