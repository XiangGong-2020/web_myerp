var api = frameElement.api;
var defaultPage = Public.getDefaultPage();
var oper = api.data.oper;
var cRowId = api.data.rowId;
var callback = api.data.callback;
var amountPlaces = Number(defaultPage.SYSTEM.amountPlaces);
var rowData = {};
var content = '';
var levelCombo;
var isDisableCombo;
var THISPAGE = {
    init: function() {
        if (void 0 !== cRowId) {
            Public.ajaxPost('/Base/Duty/query', {
                id: cRowId
            }, function(res) {
                if (200 == res.status) {
                    rowData = res.data;
                    content = rowData.content;
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
        Public.ajaxGet('/Base/User/queryAll', {isDisable: 0, normal: 'yes'}, function(data) {
            if (data && data.status === 200) {
                var normalUser = '';
                $.each(data.data.rows, function(i, v) {
                    normalUser += '<option value="' + (v.id + 100) + '">' + v.name + '</option>';
                });
                Public.ajaxGet('/Base/Category/list', {typeNumber: 'departmenttype'}, function(data1) {
                    if (data1 && data1.status === 200) {
                        var department = '';
                        $.each(data1.data.rows, function(i1, v1) {
                            department += '<option value="' + v1.id + '">' + v1.name + '</option>';
                        });
                        /* 异步获取数据成功之后再填充选项并加载选择事件 */
                        $('#department').html(department);
                        $('#normalUser').html(normalUser);
                        $('#targetNameCombo').fSelect({
                            showSearch: !0,
                            listHeight: 250,
                            disabled: ('edit' == oper && rowData.beginName != defaultPage.SYSTEM.uName) ? !0 : !1
                        });
                        if ('edit' == oper && rowData.targetName) {
                            $('.fs-input').val(rowData.targetName);
                            var names = rowData.targetName.toString().split(',');
                            $(".fs-option").each(function() {
                                if(names.indexOf($(this).children('.fs-option-label').html()) != -1) {
                                    $(this).addClass("selected");
                                }
                            });
                        }
                    } else {
                        $.dialog.tips(data1.msg, 2);
                    }
                });
            } else {
                $.dialog.tips(data.msg, 2);
            }
        });
        if ('edit' == oper) {
            $('#targetName').val(rowData.targetName);
            $('#lastDate').val(rowData.lastDate);
            $('#content').val(rowData.content);
            $('#backinfo').val(rowData.backinfo);
            if (rowData.beginName != defaultPage.SYSTEM.uName) {
                $('#lastDate').attr('disabled', true).addClass('ui-input-dis');
                $('#content').attr('disabled', true).addClass('ui-input-dis');
            }
        }
        levelCombo = $('#level').combo({
            data: [
                {id: 0, name: '不急'},
                {id: 1, name: '急'},
                {id: 2, name: '很急'}
            ],
            value: 'id',
            text: 'name',
            defaultSelected: rowData.level >= 0 ? ['id', rowData.level] : -1,
            disabled: ('edit' == oper && rowData.beginName != defaultPage.SYSTEM.uName) ? !0 : !1
        }).getCombo();
        isDisableCombo = $('#isDisable').combo({
            data: [
                {id: 0, name: '未处理'},
                {id: 1, name: '已处理'}
            ],
            value: 'id',
            text: 'name',
            defaultSelected: rowData.isDisable >= 0 ? ['id', rowData.isDisable] : -1
        }).getCombo();
        $('#level').find('input').attr('name', 'levelInput');
        $('#isDisable').find('input').attr('name', 'isDisableInput');
    },
    initEvent: function() {
        laydate.render({
            elem: "#lastDate"
        });
    },
    initValidator: function() {
        $('#manage-form').validate({
            rules: {
                fsInput: {required: !0},
                targetName: {required: !0},
                levelInput: {required: !0},
                lastDate: {required: !0},
                content: {required: !0}
            }
        });
    },
    postData: function(callbackType = 0) {
        if (Business.verifyRight('DUTY_UPDATE')) {
            if (!$('#manage-form').validate().form()) {
                return void $('#manage-form').find('input.valid-error').eq(0).focus();
            }
            var data = THISPAGE.getData();
            if (data.targetName == '') {
                return void $.dialog.tips('转交对象不能为空！', 2);
            }
            if (data.content == '') {
                return void $.dialog.tips('工作内容不能为空！', 2);
            }
            var title = 'add' == oper ? '新增工作转交' : '编辑工作转交';
            data && Public.ajaxPost('/Base/Duty/' + ('add' == oper ? 'add' : 'update'), data, function(res) {
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
    },
    getData: function() {
        var data = {
            id: cRowId || 0,
            targetName: $.trim($('.fs-input').val()),
            level: levelCombo.getValue(),
            isDisable: isDisableCombo.getValue(),
            lastDate: $.trim($('#lastDate').val()),
            content: $.trim($('#content').val()),
            backinfo: $.trim($('#backinfo').val())
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
        $('#clearAllBtn').trigger('click');
        levelCombo._setDefaultSelected(-1);
        isDisableCombo._setDefaultSelected(-1);
        $('#lastDate').val('');
        $('#content').val('');
        $('#backinfo').val('');
    }
};
$(function() {
    THISPAGE.init();
});
