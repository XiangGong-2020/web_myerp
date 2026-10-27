var api = frameElement.api;
var year = api.data.year;
var callback = api.data.callback;
var rowData = {};
var THISPAGE = {
    init: function() {
        if (void 0 !== year) {
            Public.ajaxPost('/Base/SalesTarget/queryYear', {
                year: year
            }, function(res) {
                if (200 == res.status) {
                    rowData = res.data;
                    THISPAGE.initBtns();
                    THISPAGE.initDom();
                    THISPAGE.initEvent();
                    THISPAGE.initValidator();
                } else {
                    $.dialog.tips('获取数据失败！' + res.msg, 2);
                }
            });
        } else {
            THISPAGE.initBtns();
            THISPAGE.initDom();
            THISPAGE.initEvent();
            THISPAGE.initValidator();
        }
    },
    initBtns: function() {
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
    },
    initDom: function() {
        if (rowData) {
            $('#targetAmount').val(rowData.targetAmount);
        } else {
            $('#targetAmount').val('');
        }
    },
    initEvent: function() {
        $('#manage-form').on('submit', function(a) {
            a.preventDefault();
            THISPAGE.postData();
        });
    },
    initValidator: function() {
        $('#manage-form').validate({
            rules: {
                targetAmount: {required: !0}
            }
        });
    },
    postData: function(callbackType = 0) {
        if (!$('#manage-form').validate().form()) {
            return void $('#manage-form').find('input.valid-error').eq(0).focus();
        }
        var data = THISPAGE.getData();
        var title = '制定' + data.year + '年年度销售目标';
        Public.ajaxPost('/Base/SalesTarget/makeYear', data, function(res) {
            if (200 == res.status) {
                parent.parent.$.dialog.tips(title + '成功！');
                if (callback && 'function' == typeof callback) {
                    callback(res.data, 'add', window, callbackType);
                }
            } else {
                $.dialog.tips(title + '失败！' + res.msg, 2);
            }
        });
    },
    getData: function() {
        var data = {
            id: rowData ? rowData.id : 0,
            year: rowData ? rowData.year : year,
            targetAmount: Number($.trim($('#targetAmount').val())).toFixed(2)
        };
        return data;
    }
};
$(function() {
    THISPAGE.init();
});
