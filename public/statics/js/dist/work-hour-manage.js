
var defaultPage = Public.getDefaultPage();
var api = frameElement.api;
var type = api.data.type;
var oper = api.data.oper;
var cRowId = api.data.rowId;
var rowData = api.data.rowData || {};
var callback = api.data.callback;
var uNameCombo;
var THISPAGE = {
    init: function() {
        if (void 0 !== cRowId) {
            Public.ajaxPost('/Base/WorkHour/query', {
                id: cRowId
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
            $('#uName').data('defItem', ['name', rowData.uName]);
            $('#workDate').val(rowData.workDate);
            $('#workHours').val(rowData.workHours);
            $('#description').val(rowData.description);
        } else {
            $('#workDate').val(defaultPage.SYSTEM.endDate);
        }
        uNameCombo = $('#uName').combo({
            data: '/Base/User/queryAll?normal=yes',
            ajaxOptions: {
                formatData: function(a) {
                    return a.data.rows;
                }
            },
            value: 'id',
            text: 'name',
            defaultSelected: rowData.uName ? ['name', rowData.uName] : -1,
            width: 330,
            listHeight: 360
        }).getCombo();
        $('#uName').find('input').attr('name', 'uNameInput');
    },
    initEvent: function() {
        laydate.render({elem: "#workDate", type: 'date'});
    },
    initValidator: function() {
        $('#manage-form').validate({
            rules: {
                uNameInput: {required: !0},
                clockDate: {required: !0},
                workHours: {required: !0}
            }
        });
    },
    postData: function(callbackType = 0) {
        if (Business.verifyRight('WORKHOUR_ADVANCE')) {
            if (!$('#manage-form').validate().form()) {
                return void $('#manage-form').find('input.valid-error').eq(0).focus();
            }
            var data = THISPAGE.getData();
            var title = 'add' == oper ? '新增' : '编辑';
            Public.ajaxPost('/Base/WorkHour/' + ('add' == oper ? 'add' : 'update'), data, function(res) {
                if (res && 200 == res.status) {
                    parent.parent.$.dialog.tips(title + '成功！');
                    if (callback && 'function' == typeof callback) {
                        callback(res.data, oper, window, callbackType);
                    }
                } else {
                    $.dialog.tips(title + '失败！' + res.msg, 2);
                }
            });
        }
    },
    getData: function() {
        var data = {
            id: cRowId || 0,
            type: type,
            uName: uNameCombo.getText() || '',
            workDate: $.trim($('#workDate').val()),
            workHours: Number($.trim($('#workHours').val())).toFixed(1),
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
        uNameCombo._setDefaultSelected(-1);
        $('#workDate').val(defaultPage.SYSTEM.endDate);
        $('#workHours').val('');
        $('#description').val('');
    }
};
$(function() {
    THISPAGE.init();
});
