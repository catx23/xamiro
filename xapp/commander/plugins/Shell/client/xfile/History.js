define([
    'dojo/_base/declare',
    "dojo/keys",
    "dojo/has"
],
    function (declare,keys,has)
    {
        return declare("Shell.xfile.History",null,
            {
                _history :[""],
                _index:0,
                push: function(cmd) {
                    this._history.push(cmd);
                    this._index = this.length();
                },

                length: function() {
                    return this._history.length;
                },

                getNext: function() {
                    this._index += 1;
                    var cmd = this._history[this._index] || "";
                    this._index = Math.min(this.length(), this._index);
                    return cmd;
                },

                getPrev: function() {
                    this._index = Math.max(0, this._index - 1);
                    return this._history[this._index];
                }
            });
    })
;