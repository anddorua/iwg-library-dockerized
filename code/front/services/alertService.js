/**
 * Created by andrey on 14.10.16.
 */
angular.module("iwg.lib.alert", [])
    .factory('alertService', ['$filter', '$sce', function($filter, $sce){
        var alertMessage = null;
        return {
            msg: alertMessage,
            setMsg: function(msgToSet) {
                if (typeof(msgToSet) == 'object') {
                    if (msgToSet.data.type == "Exception\\EValidation") {
                        var msgToShow = msgToSet.data.message + '<br>';
                        msgToSet.data.errors.forEach(function(eMsg){
                            msgToShow += eMsg.propertyPath + ": " + eMsg.message + '<br>';
                        });
                        this.msg = $sce.trustAsHtml(msgToShow);
                    } else if (msgToSet.data.type == "Exception\\EOperationDeny") {
                        this.msg = $sce.trustAsHtml(msgToSet.data.message);
                    } else {
                        this.msg = $sce.trustAsHtml('<pre>' + $filter('json')(JSON.stringify(msgToSet)) + '</pre>');
                    }
                } else {
                    this.msg = msgToSet;
                }
            }
        };
    }])

    .factory('terminalService', [function(){
        var lines = [];
        return {
            lines: lines,
            out: function (line) {
                this.lines.unshift({ date: new Date(), text: line});
            },
            clear: function () {
                this.lines.splice(0, this.lines.length);
            }
        };
    }]);