var api = frameElement.api;
var oper = api.data.oper;
var cRowId = api.data.rowId;
var rowData = api.data.rowData || {};
var callback = api.data.callback;
var combo1;
var THISPAGE = {
    init: function() {
        if (void 0 !== cRowId) {
            Public.ajaxPost('/Bill/Evaluate/evaluationQuery', {
                id: cRowId
            }, function(a) {
                if (200 == a.status) {
                    rowData = a.data;
                    THISPAGE.initBtns();
                    THISPAGE.initDom();
                    THISPAGE.initValidator();
                } else {
                    $.dialog.tips('获取数据失败！', 2);
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
    initDom: function() {
        if (rowData.id) {
            $('#name').val(rowData.name);
        }
        combo1 = $('#type').combo({
            data: '/Base/Category/list?typeNumber=positiontype',
            ajaxOptions: {
                formatData: function(a) {
                    return a.data.rows.unshift({
                        id: 0,
                        name: '【通用】'
                    }), a.data.rows;
                }
            },
            value: 'id',
            text: 'name',
            defaultSelected: rowData.type ? ['name', rowData.type] : -1,
            width: 114,
            listHeight: 90
        }).getCombo();
        $('#type').find('input').attr('name', 'typeInput');
    },
    initValidator: function() {
        $('#manage-form').validate({
            rules: {
                typeInput: {required: !0},
                name: {required: !0}
            }
        });
    },
    postData: function(callbackType = 0) {
        if (!$('#manage-form').validate().form()) {
            return void $('#manage-form').find('input.valid-error').eq(0).focus();
        }
        var title = 'add' == oper ? '新增' : '编辑';
        var data = THISPAGE.getData();
        data && Public.ajaxPost('/Bill/Evaluate/evaluation' + ('add' == oper ? 'Add' : 'Update'), data, function(res) {
            if (res && 200 == res.status) {
                parent.parent.$.dialog.tips(title + '成功！');
                if (callback && 'function' == typeof callback) {
                    callback(res.data, oper, window, callbackType);
                }
            } else {
                $.dialog.tips(title + '失败！' + res.msg, 2);
            }
        });
    },
    getData: function() {
        var data = {
            id: cRowId || 0,
            type: combo1.getText(),
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
        combo1._setDefaultSelected(-1);
        $('#name').val('');
    }
};
$(function() {
    THISPAGE.init();
});
