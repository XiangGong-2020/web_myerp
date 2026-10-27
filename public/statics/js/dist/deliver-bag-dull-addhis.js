var api = frameElement.api;
var defaultPage = Public.getDefaultPage();
var rowData = api.data.rowData || {};
var callback = api.data.callback;
var THISPAGE = {
    init: function() {
        THISPAGE.initBtns();
        THISPAGE.initDom();
        THISPAGE.initValidator();
    },
    initBtns: function() {
        api.button({
            id: 'confirm',
            name: '追加',
            defClass: 'ui_state_highlight',
            callback: function() {
                return THISPAGE.postData(), !1;
            }
        }, {
            id: 'cancel',
            name: '关闭'
        });
    },
    initDom: function() {
        $('#no').val(rowData.no);
        $('#company').val(rowData.company);
        $('#sellerNick').val(rowData.sellerNick);
        $('#lastUpdator').val(rowData.lastUpdator);
        $('#dullOperHis').val(rowData.dullOperHis);
    },
    initValidator: function() {
        $('#manage-form').validate({
            rules: {
                addHis: {required: !0}
            }
        });
    },
    postData: function() {
        if (Business.verifyRight('SYS_DEFAULT')) {
            if (!$('#manage-form').validate().form()) {
                return void $('#manage-form').find('input.valid-error').eq(0).focus();
            }
            var data = THISPAGE.getData();
            data && Public.ajaxPost('/Base/DeliverBag/dull_update_dullOperHis', data, function(res) {
                if (res && 200 == res.status) {
                    $.dialog.tips('追加滞留处理记录成功！');
                    rowData = res.data;
                    THISPAGE.initDom();
                    $('#addHis').val('');
                    if (callback && 'function' == typeof callback) {
                        callback(res.data);
                    }
                } else {
                    $.dialog.tips('追加滞留处理记录失败！' + res.msg, 2);
                }
            });
        }
    },
    getData: function() {
        var data = {
            id: rowData.id,
            dullOperHis: (new Date).format('yyyy-MM-dd hh:mm:ss') + ' ' + defaultPage.SYSTEM.uName + '：' + $.trim($('#addHis').val()) + "\r\n" + rowData.dullOperHis
        };
        return data;
    }
};
$(function() {
    THISPAGE.init();
});
