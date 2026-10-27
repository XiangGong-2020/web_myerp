var api = frameElement.api;
var defaultPage = Public.getDefaultPage();
var oper = api.data.oper;
var cRowId = api.data.rowId;
var callback = api.data.callback;
var rowData = {};
var THISPAGE = {
    init: function() {
        if (void 0 !== cRowId) {
            Public.ajaxPost('/Base/DiscountReq/query', {
                id: cRowId
            }, function(res) {
                if (200 == res.status) {
                    rowData = res.data;
                    THISPAGE.initBtns();
                    THISPAGE.initDom();
                    THISPAGE.initValidator();
                    THISPAGE.initEvent();
                } else {
                    $.dialog.tips('获取数据失败！' + res.msg, 2);
                }
            });
        } else {
            THISPAGE.initBtns();
            THISPAGE.initDom();
            THISPAGE.initValidator();
            THISPAGE.initEvent();
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
        }
    },
    initDom: function() {
        if ('edit' == oper) {
            $('#title').val(rowData.title);
            $('#content').html(rowData.content);
            $('#description').html(rowData.description);
            $('#createTime').html('申请人：' + rowData.uName + '， 申请时间：' + rowData.createTime);
            if (api.config.disEdit) {
                $('#title').attr('disabled', true).addClass('ui-input-dis');
                $('#content').attr('disabled', true).addClass('ui-input-dis');
                $('#description').attr('disabled', true).addClass('ui-input-dis');
            }
        } else {
            $('#description').attr({readonly: 'readonly'});
        }
    },
    initEvent: function() {
        if (!api.config.disEdit) {
        }
    },
    initValidator: function() {
        $('#manage-form').validate({
            rules: {
                title: {required: !0}
            }
        });
    },
    postData: function(callbackType = 0) {
        if ($.inArray('SALE_MANAGER', defaultPage.SYSTEM.rights) !== -1 || rowData.uId == defaultPage.SYSTEM.uId || (void 0 == cRowId && $.inArray('SERVICE_QUERY', defaultPage.SYSTEM.rights) !== -1)) {
            if (!$('#manage-form').validate().form()) {
                return void $('#manage-form').find('input.valid-error').eq(0).focus();
            }
            var data = THISPAGE.getData();
            var title = 'add' == oper ? '新增' : '编辑';
            Public.ajaxPost('/Base/DiscountReq/' + ('add' == oper ? 'add' : 'update'), data, function(res) {
                if (res && 200 == res.status) {
                    parent.parent.$.dialog.tips(title + '成功！');
                    if (callback && 'function' == typeof callback) {
                        callback(res.data, oper, window, callbackType);
                    };
                } else {
                    $.dialog.tips(title + '失败！' + res.msg, 2);
                }
            });
        } else {
            $.dialog.tips('不能编辑别人的优惠申请！仅本人或高级权限可编辑！', 2);
            return void 0;
        }
    },
    getData: function() {
        var data = {
            id: cRowId || 0,
            title: $.trim($('#title').val()),
            content: $.trim($('#content').val()),
            description: $.trim($('#description').val())
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
        rowData = {};
        $('#title').val('');
        $('#content').val('');
        $('#description').val('');
    }
};
$(function() {
    THISPAGE.init();
});
