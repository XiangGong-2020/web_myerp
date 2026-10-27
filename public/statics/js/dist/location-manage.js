var api = frameElement.api;
var defaultPage = Public.getDefaultPage();
var oper = api.data.oper;
var cRowId = api.data.rowId;
var callback = api.data.callback;
var rowData = {storageId: api.data.storageId || 0, parentId: api.data.parentId || 0};
var THISPAGE = {
    init: function() {
        if (void 0 !== cRowId) {
            Public.ajaxPost('/Base/Storage/locationQuery', {
                id: cRowId
            }, function(res) {
                if (200 == res.status) {
                    rowData = res.data;
                    THISPAGE.initBtns();
                    THISPAGE.initDom();
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
        var a = ['保存并新增', '保存', '关闭'];
        api.button({
            id: 'saveAndAdd',
            name: a[0],
            defClass: 'ui_state_highlight',
            callback: function() {
                return THISPAGE.postData(0), !1;
            }
        }, {
            id: 'confirm',
            name: a[1],
            defClass: 'ui_state_highlight',
            callback: function() {
                return THISPAGE.postData(1), !1;
            }
        }, {
            id: 'cancel',
            name: a[2]
        });
    },
    initDom: function() {
        if (rowData.id) {
            $('#name').val(rowData.name);
        }
    },
    initValidator: function() {
        $('#manage-form').validate({
            rules: {
                name: {required: !0}
            },
            messages: {
                name: {required: '不能为空'}
            },
            errorClass: 'valid-error'
        });
    },
    postData: function(callbackType = 0) {
        if (Business.verifyRight('STORAGE_UPDATE')) {
            if (!$('#manage-form').validate().form()) {
                return void $('#manage-form').find('input.valid-error').eq(0).focus();
            }
            var data = THISPAGE.getData();
            var title = ('add' == oper ? '新增' : '修改') + (data.parentId == 0 ? '分区' : '库位');
            data && Public.ajaxPost('/Base/Storage/' + ('add' == oper ? 'locationAdd' : 'locationUpdate'), data, function(res) {
                if (res && 200 == res.status) {
                    parent.parent.$.dialog.tips(title + '成功！');
                    if (callback && 'function' == typeof callback) {
                        callback(res.data, oper, window, callbackType);
                    }
                } else {
                    parent.parent.$.dialog.tips(title + '失败！' + res.msg, 2);
                }
            });
        }
    },
    getData: function() {
        var data = {
            id: cRowId || 0,
            storageId: rowData.storageId,
            parentId: rowData.parentId,
            name: $.trim($('#name').val())
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
        $('#name').val('');
    }
};
$(function() {
    THISPAGE.init();
});
