var api = frameElement.api;
var defaultPage = Public.getDefaultPage();
var oper = api.data.oper;
var cRowId = api.data.rowId;
var callback = api.data.callback;
var rowData = {};
var typeCombo;
var THISPAGE = {
    init: function() {
        if (void 0 !== cRowId) {
            Public.ajaxPost('/Base/Plan/query', {
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
        if ('edit' == oper && rowData.targetEditable == 0 && rowData.summaryEditable == 0) {
            api.button({
                id: 'cancel',
                name: '关闭'
            });
        } else {
            api.button({
                id: 'saveAndAdd',
                name: '提交并新增',
                defClass: 'ui_state_highlight',
                callback: function() {
                    return THISPAGE.postData(0), !1;
                }
            }, {
                id: 'confirm',
                name: '提交',
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
        typeCombo = $('#type').combo({
            data: [
                {id: 0, name: '日目标'},
                {id: 1, name: '周目标'},
                {id: 2, name: '月目标'},
                {id: 3, name: '年目标'}
            ],
            value: 'id',
            text: 'name',
            defaultSelected: rowData.type >= 0 ? ['id', rowData.type] : -1
        }).getCombo();
        $('#type').find('input').attr('name', 'typeInput');
        if ('edit' == oper) {
            typeCombo.disable();
            $('#target').val(rowData.target);
            $('#summary').val(rowData.summary);
            rowData.targetEditable == 0 && $('#target').attr('disabled', true).addClass('ui-input-dis');
            rowData.summaryEditable == 0 && $('#summary').attr('disabled', true).addClass('ui-input-dis');
            rowData.createTime != '0000-00-00 00:00:00' && $('#targetNotice').html('首次提交时间 ' + rowData.createTime);
            rowData.summaryTime != '0000-00-00 00:00:00' && $('#summaryNotice').html('首次提交时间 ' + rowData.summaryTime);
        }
    },
    initValidator: function() {
        $('#manage-form').validate({
            rules: {
                target: {required: !0},
                typeInput: {required: !0}
            }
        });
    },
    postData: function(callbackType = 0) {
        if (rowData.uId == defaultPage.SYSTEM.uId || (void 0 == cRowId && $.inArray('PLAN_ADD', defaultPage.SYSTEM.rights) !== -1)) {
            if (Business.verifyRight('PLAN_UPDATE')) {
                if (!$('#manage-form').validate().form()) {
                    return void $('#manage-form').find('input.valid-error').eq(0).focus();
                }
                var data = THISPAGE.getData();
                var title = 'add' == oper ? '新增工作目标' : '编辑工作目标';
                data && Public.ajaxPost('/Base/Plan/' + ('add' == oper ? 'add' : 'update'), data, function(res) {
                    if (200 == res.status) {
                        parent.parent.$.dialog.tips(title + '成功！');
                        if (callback && 'function' == typeof callback) {
                            callback(res.data, oper, window, callbackType);
                        };
                    } else {
                        $.dialog.tips(title + '失败！' + res.msg, 2);
                    }
                });
            }
        } else {
            $.dialog.tips('不能编辑别人的工作目标！', 2);
            return void 0;
        }
    },
    getData: function() {
        var data = {
            id: cRowId || 0,
            type: typeCombo.getValue() || 0,
            target: $.trim($('#target').val()),
            summary: $.trim($('#summary').val())
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
        typeCombo._setDefaultSelected(-1);
        $('#target').val('');
        $('#summary').val('');
    }
};
$(function() {
    THISPAGE.init();
});
