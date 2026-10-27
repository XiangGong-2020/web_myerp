var api = frameElement.api;
var oper = api.data.oper;
var cRowId = api.data.rowId;
var callback = api.data.callback;
var rowData = {};
var THISPAGE = {
    init: function() {
        if (void 0 !== cRowId) {
            Public.ajaxPost('/Base/User/userLevelQuery', {
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
        rowData.id && (
            $('#name').val(rowData.name),
            $('#minExp').val(rowData.minExp),
            $('#maxExp').val(rowData.maxExp),
            $('#hourlyWage').val(rowData.hourlyWage),
            $('#description').val(rowData.description)
        );
    },
    initValidator: function() {
        $('#manage-form').validate({
            rules: {
                name: {required: !0},
                minExp: {required: !0},
                maxExp: {required: !0},
                hourlyWage: {required: !0}
            }
        });
    },
    postData: function(callbackType = 0) {
        if (Business.verifyRight('USER_LEVEL_ADVANCE')) {
            if (!$('#manage-form').validate().form()) {
                return void $('#manage-form').find('input.valid-error').eq(0).focus();
            }
            var title = 'add' == oper ? '新增成长等级' : '编辑成长等级';
            var data = THISPAGE.getData();
            Public.ajaxPost('/Base/User/' + ('add' == oper ? 'userLevelAdd' : 'userLevelUpdate'), data, function(res) {
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
            name: $.trim($('#name').val()),
            minExp: $.trim($('#minExp').val()) || 0,
            maxExp: $.trim($('#maxExp').val()) || 0,
            hourlyWage: $.trim($('#hourlyWage').val()) || 0,
            description: $.trim($('#description').val())
        };
        return data;
    },
    resetForm: function(a) {
        var uiTitle = $(".ui_title", parent.document).text().replace('◉ 视窗', '');
        console.log(uiTitle)
        if (uiTitle.includes('编辑')) {
            $(".ui_title", parent.document).text(uiTitle.replace('编辑','新增'));
        }
        oper = 'add';
        cRowId = void 0;
        $('#name').val('');
        $('#minExp').val('');
        $('#maxExp').val('');
        $('#hourlyWage').val('');
        $('#description').val('');
    }
};
$(function() {
    THISPAGE.init();
});
