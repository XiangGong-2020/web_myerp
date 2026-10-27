
var defaultPage = Public.getDefaultPage();
var api = frameElement.api;
var oper = api.data.oper;
var cRowId = api.data.rowId;
var rowData = api.data.rowData || {};
var callback = api.data.callback;
var typeCombo;
var THISPAGE = {
    init: function() {
        if (void 0 !== cRowId) {
            Public.ajaxPost('/Base/ProduceGroup/query', {
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
        if (rowData.id) {
            $('#name').val(rowData.name);
            $('#CoCNo').val(rowData.CoCNo);
            $('#description').val(rowData.description);
        }
        Public.ajaxGet('/Base/User/queryAll', {department: '生产部'}, function(data) {
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
                $('#membersCombo').fSelect({
                    showSearch: !0,
                    listHeight: 217
                });
                if (rowData.members) {
                    $('.fs-input').val(rowData.members);
                    var members = rowData.members.toString().split(',');
                    $(".fs-option").each(function() {
                        if (members.indexOf($(this).children('.fs-option-label').html()) != -1) {
                            $(this).addClass("selected");
                        }
                    });
                }
            }
        });
        typeCombo = $('#type').combo({
            data: [{id: 0, name: '配货组'}, {id: 1, name: '加工组'}],
            value: 'id',
            text: 'name',
            defaultSelected: rowData.type ? ['name', rowData.type] : -1
        }).getCombo();
        $('#type').find('input').attr('name', 'typeInput');
    },
    initValidator: function() {
        $('#manage-form').validate({
            rules: {
                name: {required: !0},
                typeInput: {required: !0},
            }
        });
    },
    postData: function(callbackType = 0) {
        if (Business.verifyRight('SYS_ADVANCE')) {
            if (!$('#manage-form').validate().form()) {
                return void $('#manage-form').find('input.valid-error').eq(0).focus();
            }
            var data = THISPAGE.getData();
            var title = 'add' == oper ? '新增' : '编辑';
            data && Public.ajaxPost('/Base/ProduceGroup/' + ('add' == oper ? 'add' : 'update'), data, function(res) {
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
            members: $.trim($('.fs-input').val()),
            type: typeCombo.getText() || '',
            CoCNo: $.trim($('#CoCNo').val()),
            description: $.trim($('#description').val())
        };
        return data;
    },
    resetForm: function(a) {
        var uiTitle = $(".ui_title", parent.document).text().replace('◉ 视窗', '');
        if (uiTitle.includes('编辑')) {
            $(".ui_title", parent.document).text(uiTitle.replace('编辑', '新增'));
        }
        oper = 'add';
        cRowId = void 0;
        $('#name').val('');
        $('#clearAllBtn').trigger('click');
        typeCombo._setDefaultSelected(-1);
        $('#CoCNo').val('');
        $('#description').val('');
    }
};
$(function() {
    THISPAGE.init();
});
