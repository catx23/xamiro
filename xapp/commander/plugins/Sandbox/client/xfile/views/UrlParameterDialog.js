define([
    "dojo/_base/declare",
    'xfile/views/ActionDialog',
    'xide/factory'
],
    function (declare, ActionDialog,factory) {
        return declare("Sandbox.xfile.views.UrlParameterDialog", [ActionDialog],
            {
                rootUrl: null,
                urlParams: null,
                fileNameValid: true,
                onValidated: function (valid) {
                    if (this.okButton) {
                        this.okButton.set('disabled', !valid);
                    }
                },
                onNormalKey: function () {
                    if (this.editBox) {
                        this.editBox._refreshState(true);
                    }
                },
                validateUrl: function (value) {
                    return /^(https?|ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test(value);
                },
                fileNameValidator: function (value, constraints) {
                    var thiz = this;
                    var result = false;

                    if (value) {
                        if (this.owner.validateUrl(this.owner.rootUrl + value)) {
                            result = true;
                        } else {
                            thiz.message = thiz.promptMessage = 'This is not a valid url, you may go to jail!';
                            result = false;
                        }
                    } else {
                        thiz.promptMessage = '';
                        thiz.message = '';
                        result = true;
                    }

                    this.fileNameValid = result;

                    if (this.owner && this.owner.onValidated) {
                        this.owner.onValidated(result);
                    }
                    return result;
                },
                getValue: function () {
                    return this.editBox.getValue();
                },
                startup: function () {
                    this.inherited(arguments);
                    this.editBox = factory.createValidationTextBox(this.containerNode, "float:left;", "Parameters", this.urlParams, this.fileNameValidator, this.delegate, 'Not a real url!', 'I need some input');
                    this.editBox.owner = this;
                    this.addActionButtons();
                }
            });
    })
;