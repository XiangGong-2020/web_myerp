var api = frameElement.api;
var defaultPage = Public.getDefaultPage();
var oper = api.data.oper;
var cRowId = api.data.rowId;
var callback = api.data.callback;
var rowData = {};
var content = '';
var typeCombo;
var THISPAGE = {
    init: function() {
        if (void 0 !== cRowId) {
            Public.ajaxPost('/Base/WorkRecord/query', {
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
        typeCombo = $('#type').combo({
            data: [
                {id: 0, name: '常规记录'},
                {id: 1, name: '贡献记录'},
                {id: 2, name: '过失记录'}
            ],
            value: 'id',
            text: 'name',
            defaultSelected: rowData.type >= 0 ? ['id', rowData.type] : -1,
            width: 78,
            callback: {
                onListClick: function() {
                    var type = this.getValue();
                    if (type == 2) {
                        $('#targetAmountName').text('损失金额');
                        $('#targetAmountName').parents('li').removeClass('dn');
                    } else {
                        $('#targetAmountName').text('');
                        $('#targetAmountName').parents('li').addClass('dn');
                    }
                }
            }
        }).getCombo();
        $('#type').find('input').attr('name', 'typeInput');
        Public.ajaxGet('/Base/User/queryAll?normal=yes', {}, function(data) {
            if (data.status === 200) {
                var normalUser = disableUser = '';
                $.each(data.data.rows, function(i, v) {
                    if (v.isDisable == 0) {
                        normalUser += '<option value="' + v.id + '">' + v.name + '</option>';
                    } else {
                        disableUser += '<option value="' + v.id + '">' + v.name + '</option>';
                    }
                });
                $('#normalUser').html(normalUser);
                $('#disableUser').html(disableUser);
                $('#targetNameCombo').fSelect({
                    showSearch: !0,
                    listHeight: 216
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
                $.dialog.tips(data.msg, 2);
            }
        });
        if ('edit' == oper) {
            $('#targetAmount').val(rowData.targetAmount);
            $('#targetDate').val(rowData.targetDate);
            $('#content').val(rowData.content);
            $('#targetAmountName').text(targetAmountName);
            if (rowData.type == 2) {
                $('#targetAmountName').text('损失金额');
                $('#targetAmountName').parents('li').removeClass('dn');
            } else {
                $('#targetAmountName').text('');
                $('#targetAmountName').parents('li').addClass('dn');
            }
        } else {
            $('#targetDate').val((new Date).format('yyyy-MM-dd'));
            $('#content').val('');
        }
    },
    initEvent: function() {
        laydate.render({
            elem: "#targetDate",
        });
    },
    initValidator: function() {
        $('#manage-form').validate({
            rules: {
                typeInput: {required: !0},
                targetDate: {required: !0},
                fsInput: {required: !0},
                content: {required: !0}
            }
        });
    },
    postData: function(callbackType = 0) {
        if (Business.verifyRight('WORKRECORD_ADVANCE')) {
            if (!$('#manage-form').validate().form()) {
                return void $('#manage-form').find('input.valid-error').eq(0).focus();
            }
            var data = THISPAGE.getData();
            var title = 'add' == oper ? '新增工作记录' : '编辑工作记录';
            Public.ajaxPost('/Base/WorkRecord/' + ('add' == oper ? 'add' : 'update'), data, function(res) {
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
    },
    getData: function() {
        var data = {
            id: cRowId || 0,
            type: typeCombo.getValue() || 0,
            targetDate: $('#targetDate').val(),
            targetName: $.trim($('.fs-input').val()),
            targetAmount: Number($.trim($('#targetAmount').val())).toFixed(2),
            content: $.trim($('#content').val())
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
        $('#targetDate').val((new Date).format('yyyy-MM-dd'));
        $('#clearAllBtn').trigger('click');
        $('#targetAmount').val('');
        $('#content').val('');
    }
};
$(function() {
    THISPAGE.init();
});
