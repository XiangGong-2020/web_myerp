var api = frameElement.api;
var oper = api.data.oper;
var cRowId = api.data.rowId;
var rowData = api.data.rowData || {};
var callback = api.data.callback;
var THISPAGE = {
    init: function() {
        if (void 0 !== cRowId) {
            Public.ajaxPost('/Base/WorkSite/query', {
                id: cRowId
            }, function(res) {
                if (200 == res.status) {
                    rowData = res.data;
                    THISPAGE.initBtns();
                    THISPAGE.initDom(rowData);
                    THISPAGE.initValidator();
                } else {
                    $.dialog.tips('获取数据失败！' + res.msg, 2);
                }
            });
        } else {
            THISPAGE.initBtns();
            THISPAGE.initDom();
            THISPAGE.initValidator();
        }
    },
    initBtns: function() {
        api.button({
            id: 'saveAndAdd',
            name: '保存并新增',
            defClass: 'ui_state_highlight',
            callback: function() {
                return THISPAGE.postData(0), !1;
            }
        }, {
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
    initDom: function(rowData) {
        if ('edit' == oper) {
            $('#urlName').val(rowData.urlName);
            $('#urlDes').val(rowData.urlDes);
            $('#urlLink').val(rowData.urlLink);
        } else {
            $('#urlName').val('');
            $('#urlDes').val('');
            $('#urlLink').val('');
        }
    },
    initValidator: function() {
        $('#manage-form').validate({
            rules: {
                urlName: {required: !0},
                urlDes: {required: !0},
                urlLink: {required: !0, url: !0}
            }
        });
    },
    postData: function(callbackType = 0) {
        if (Business.verifyRight('WORKSITE_UPDATE')) {
            if (!$('#manage-form').validate().form()) {
                return void $('#manage-form').find('input.valid-error').eq(0).focus();
            }
            var title = 'add' == oper ? '新增业务网站' : '编辑业务网站';
            var data = THISPAGE.getData();
            if (data) {
                Public.ajaxPost('/Base/WorkSite/' + ('add' == oper ? 'add' : 'update'), data, function(res) {
                    if (res && 200 == res.status) {
                        parent.parent.$.dialog.tips(title + '成功！');
                        if (callback && 'function' == typeof callback) {
                            callback(res.data, oper, window, callbackType);
                        };
                    } else {
                        $.dialog.tips(title + '失败！' + res.msg, 2);
                    }
                });
            }
        }
    },
    getData: function() {
        var data = {
            id: void 0 !== cRowId ? cRowId : -1,
            urlName: $.trim($('#urlName').val()),
            urlDes: $.trim($('#urlDes').val()),
            urlLink: $.trim($('#urlLink').val())
        };
        return data;
    },
    resetForm: function(a) {
        var uiTitle = $(".ui_title", parent.document).text().replace('◉ 视窗', '');
        if (uiTitle.includes('编辑')) {
            $(".ui_title", parent.document).text(uiTitle.replace('编辑','新增'));
        }
        oper = 'add';
        cRowId = void 0;
        $('#urlName').val('');
        $('#urlDes').val('');
        $('#urlLink').val('');
    }
};
$(function() {
    THISPAGE.init();
});
