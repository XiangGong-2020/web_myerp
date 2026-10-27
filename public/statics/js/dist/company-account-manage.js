var api = frameElement.api;
var defaultPage = Public.getDefaultPage();
var oper = api.data.oper;
var cRowId = api.data.rowId;
var callback = api.data.callback;
var rowData = {};
var categoryCombo;
var companyCommbo;
var THISPAGE = {
    init: function() {
        if (void 0 !== cRowId) {
            Public.ajaxPost('/Base/CompanyAccount/query', {
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
            $('#name').val(rowData.name);
            $('#amount').val(Public.numToCurrency(rowData.amount));
            $('#date').val(rowData.date);
            $('#description').val(rowData.description);
        } else {
            $('#name').val('');
            $('#amount').val('');
            $('#description').val('');
        }
        categoryCombo = $('#category').combo({
            data: [{id: 1, name: '人民币'}, {id: 2, name: '美元'}],
            value: 'id',
            text: 'name',
            defaultSelected: rowData.type > 0 ? ['id', rowData.type] : -1
        }).getCombo();
        companyCombo = $('#company').combo({
            data: '/Base/Company/queryAll',
            ajaxOptions: {
                formatData: function(a) {
                    return a.data.rows;
                }
            },
            value: 'id',
            text: 'name',
            defaultSelected: rowData.company ? ['name', rowData.company] : -1
        }).getCombo();
        $('#category').find('input').attr('name', 'categoryInput');
        $('#company').find('input').attr('name', 'companyInput');
    },
    initEvent: function() {
        laydate.render({
            elem: "#date",
        });
        $('#amount').on({
            keypress: Public.numerical,
            focus: function() {
                this.value = Public.currencyToNum(this.value);
                $(this).select();
            },
            blur: function() {
                this.value = Public.numToCurrency(this.value);
            }
        });
        Public.limitLength($('#name'), 30);
    },
    initValidator: function() {
        $('#manage-form').validate({
            rules: {
                name: {required: !0},
                date: {required: !0},
                categoryInput: {required: !0},
                companyInput: {required: !0}
            }
        });
    },
    postData: function(callbackType = 0) {
        if (Business.verifyRight('COMPANYACCOUNT_UPDATE')) {
            if (!$('#manage-form').validate().form()) {
                return void $('#manage-form').find('input.valid-error').eq(0).focus();
            }
            var data = THISPAGE.getData();
            if ('add' == oper) {
                Public.ajaxPost('/Base/CompanyAccount/add', data, function(res) {
                    if (res && 200 == res.status) {
                        parent.parent.$.dialog.tips('新增结算账户成功！');
                        if (callback && 'function' == typeof callback) {
                            callback(res.data, oper, window, callbackType);
                        }
                    } else {
                        parent.parent.$.dialog.tips('新增结算账户失败！' + res.msg, 2);
                    }
                });
            } else {
                if (rowData.name != data.name) {
                    $.dialog.confirm('注意：名称被改变！将导致使用过旧名称的所有地方也被改变，确定强行保存？', function() {
                        data && Public.ajaxPost('/Base/CompanyAccount/update', data, function(res) {
                            if (res && 200 == res.status) {
                                parent.parent.$.dialog.tips('编辑结算账户成功！');
                                if (callback && 'function' == typeof callback) {
                                    callback(res.data, oper, window, callbackType);
                                }
                            } else {
                                $.dialog.tips('编辑结算账户失败！' + res.msg, 2);
                            }
                        });
                    });
                } else {
                    data && Public.ajaxPost('/Base/CompanyAccount/update', data, function(res) {
                        if (res && 200 == res.status) {
                            parent.parent.$.dialog.tips('编辑结算账户成功！');
                            if (callback && 'function' == typeof callback) {
                                callback(res.data, oper, window, callbackType);
                            }
                        } else {
                            $.dialog.tips('编辑结算账户失败！' + res.msg, 2);
                        }
                    });
                }
            }
        }
    },
    getData: function() {
        var data = {
            id: cRowId || -1,
            name: $.trim($('#name').val()),
            date: $.trim($('#date').val()),
            type: categoryCombo.getValue(),
            company: companyCombo.getText(),
            amount: $.trim($('#amount').val()).replace(/,/g, ''),
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
        $('#name').val('');
        $('#date').val(defaultPage.SYSTEM.endDate);
        categoryCombo._setDefaultSelected(-1);
        companyCombo._setDefaultSelected(-1);
        $('#amount').val('');
        $('#description').val('');
    }
};
$(function() {
    THISPAGE.init();
});
