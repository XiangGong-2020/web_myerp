var api = frameElement.api;
var defaultPage = Public.getDefaultPage();
var oper = api.data.oper;
var cRowId = api.data.rowId;
var callback = api.data.callback;
var rowData = {};
var empCombo;
var typeCombo;
var allowNegCombo;
var THISPAGE = {
    init: function() {
        if (void 0 !== cRowId) {
            Public.ajaxPost('/Base/Storage/query', {
                id: cRowId
            }, function(res) {
                if (200 == res.status) {
                    rowData = res.data;
                    THISPAGE.initBtns();
                    THISPAGE.initDom();
                    THISPAGE.initCombo();
                    THISPAGE.initValidator();
                } else {
                    $.dialog.tips('获取数据失败！' + res.msg, 2);
                }
            });
        } else {
            THISPAGE.initBtns();
            THISPAGE.initDom();
            THISPAGE.initCombo();
            THISPAGE.initValidator();
        }
    },
    initBtns: function() {
        api.button({
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
            $('#address').val(rowData.address);
            $('#phone').val(rowData.phone);
        }
    },
    initCombo: function() {
        empCombo = $('#empName').combo({
            data: '/Base/User/queryAll?normal=yes',
            ajaxOptions: {
                formatData: function(res) {
                    return res.data.rows;
                }
            },
            value: 'id',
            text: 'name',
            defaultSelected: rowData.empId > 0 ? ['id', rowData.empId] : -1
        }).getCombo();
        typeCombo = $('#type').combo({
            data: [{id: 0, name: '实物仓'}, {id: 1, name: '虚拟仓'}],
            value: 'id',
            text: 'name',
            defaultSelected: rowData.type >= 0 ? ['id', rowData.type] : -1
        }).getCombo();
        allowNegCombo = $('#allowNeg').combo({
            data: [{id: 0, name: '不允许'}, {id: 1, name: '允许'}],
            value: 'id',
            text: 'name',
            defaultSelected: rowData.allowNeg >= 0 ? ['id', rowData.allowNeg] : -1
        }).getCombo();
        $('#empName').find('input').attr('name', 'empNameInput');
        $('#type').find('input').attr('name', 'typeInput');
        $('#allowNeg').find('input').attr('name', 'allowNegInput');
    },
    initValidator: function() {
        $('#manage-form').validate({
            rules: {
                name: {required: !0},
                address: {required: !0},
                phone: {required: !0},
                empNameInput: {required: !0},
                typeInput: {required: !0},
                allowNegInput: {required: !0}
            }
        });
    },
    postData: function(callbackType = 0) {
        if (Business.verifyRight('STORAGE_UPDATE')) {
            if (!$('#manage-form').validate().form()) {
                return void $('#manage-form').find('input.valid-error').eq(0).focus();
            }
            var title = 'add' == oper ? '新增仓库' : '编辑仓库';
            var data = THISPAGE.getData();
            data && Public.ajaxPost('/Base/Storage/' + ('add' == oper ? 'add' : 'update'), {
                postData: JSON.stringify(data)
            }, function(res) {
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
            storageId: cRowId || 0,
            name: $.trim($('#name').val()),
            address: $.trim($('#address').val()),
            phone: $.trim($('#phone').val()),
            empId: empCombo.getValue() || 0,
            empName: empCombo.getText() || '',
            type: typeCombo.getValue() || 0,
            allowNeg: allowNegCombo.getValue() || 0
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
        $('#address').val('');
        $('#phone').val('');
        empCombo._setDefaultSelected(-1);
        typeCombo._setDefaultSelected(-1);
        allowNegCombo._setDefaultSelected(-1);
    }
};
$(function() {
    THISPAGE.init();
});
