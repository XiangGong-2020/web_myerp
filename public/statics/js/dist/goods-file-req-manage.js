var api = frameElement.api;
var defaultPage = Public.getDefaultPage();
var oper = api.data.oper;
var cRowId = api.data.rowId;
var type = api.data.type;
var callback = api.data.callback;
var rowData = {};
var typeObj = {
    goodsFileOne: '中文图纸',
    goodsFileTwo: '中文教程',
    goodsFileThree: '程序文件',
    goodsFileFour: 'PCB文件',
    goodsFileFive: '设计文件'
};
var THISPAGE = {
    init: function() {
        if (void 0 !== cRowId) {
            Public.ajaxPost('/Base/Goods/fileReqQuery', {
                id: cRowId
            }, function(res) {
                if (200 == res.status) {
                    rowData = res.data;
                    THISPAGE.initBtns();
                    THISPAGE.initDom();
                } else {
                    $.dialog.tips('获取数据失败！' + res.msg, 2);
                }
            });
        } else {
            THISPAGE.initBtns();
            THISPAGE.initDom();
        }
    },
    initBtns: function() {
        if (api.config.disEdit) {
            api.button({
                id: 'cancel',
                name: '关闭'
            });
        } else {
            api.button({
                id: 'confirm',
                name: '保存',
                defClass: 'ui_state_highlight',
                callback: function() {
                    return THISPAGE.postData(1), !1;
                }
            }, {
                id: 'cancel',
                name: '关闭'
            });
        }
    },
    initDom: function() {
        if ('edit' == oper) {
            $('#no').val(rowData.no);
            $('#type').val(typeObj[rowData.type]);
            $('#name').val(rowData.name);
            $('#uName').val(rowData.uName);
            $('#reqTime').val(rowData.reqTime);
            $('#description').val(rowData.description);
            if (api.config.disEdit) {
                $('#no').attr('disabled', true).addClass('ui-input-dis');
                $('#type').attr('disabled', true).addClass('ui-input-dis');
                $('#name').attr('disabled', true).addClass('ui-input-dis');
                $('#uName').attr('disabled', true).addClass('ui-input-dis');
                $('#reqTime').attr('disabled', true).addClass('ui-input-dis');
                $('#description').attr('disabled', true).addClass('ui-input-dis');
            }
        }
    },
    postData: function(callbackType = 0) {
        if (Business.verifyRight(type.toUpperCase() + '_CHECK')) {
            var data = THISPAGE.getData();
            data && Public.ajaxPost('/Base/Goods/fileReqRefuse', data, function(res) {
                if (res && 200 == res.status) {
                    parent.parent.$.dialog.tips('操作成功！');
                    if (callback && 'function' == typeof callback) {
                        callback(res.data, oper, window, callbackType);
                    }
                } else {
                    $.dialog.tips('操作失败！' + res.msg, 2);
                }
            });
        }
    },
    getData: function() {
        rowData.description = $.trim($('#description').val());
        return rowData;
    }
};
$(function() {
    THISPAGE.init();
});
