var api = frameElement.api;
var defaultPage = Public.getDefaultPage();
var oper = api.data.oper;
var cRowId = api.data.rowId;
var callback = api.data.callback;
var rowData = {};
var categoryCombo;
var invoicewayCombo;
var businessLicense = '';
var THISPAGE = {
    init: function() {
        if (void 0 !== cRowId) {
            Public.ajaxPost('/Base/Company/query', {
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
            $('#fullName').val(rowData.fullName);
            $('#no').val(rowData.no);
            $('#address').val(rowData.address);
            $('#managerName').val(rowData.managerName);
            $('#date').val(rowData.date);
            $('#invoicelimit').val(rowData.invoicelimit);
            $('#businessScope').val(rowData.businessScope);
            $('#description').val(rowData.description);
            rowData.businessLicense && $('#img').html('<a target="_blank" href="/data/upfile/company/' + rowData.businessLicense + '"><img src="/data/upfile/company/' + rowData.businessLicense + '" /></a>');
            businessLicense = rowData.businessLicense;
        }
        categoryCombo = $('#category').combo({
            data: [{id: 0, name: '个体工商户'}, {id: 1, name: '小规模纳税人'}, {id: 2, name: '一般纳税人'}],
            value: 'id',
            text: 'name',
            defaultSelected: rowData.type >= 0 ? ['id', rowData.type] : -1
        }).getCombo();
        invoicewayCombo = $('#invoiceway').combo({
            data: [{id: 0, name: '查账征收'}, {id: 1, name: '核定征收'}, {id: 2, name: '定额征收'}],
            value: 'id',
            text: 'name',
            defaultSelected: rowData.invoiceway >= 0 ? ['id', rowData.invoiceway] : -1
        }).getCombo();
        $('#category').find('input').attr('name', 'categoryInput');
        $('#invoiceway').find('input').attr('name', 'invoicewayInput');
        Public.ajaxGet('/Base/CompanyAccount/list', {isDisable: -1}, function(data) {
            if (data.status === 200) {
                var accountNormal = accountDisabled = '';
                $.each(data.data.rows, function(i, v) {
                    if (v.isDisable == 0) {
                        accountNormal += '<option value="' + v.id + '">' + v.name + '</option>';
                    } else {
                        accountDisabled += '<option value="' + v.id + '">' + v.name + '</option>';
                    }
                });
                $('#accountNormal').html(accountNormal);
                $('#accountDisabled').html(accountDisabled);
                /* 异步获取数据成功之后再填充选项并加载选择事件 */
                $('#accountCombo').fSelect({
                    id: 'accountFselect',
                    showSearch: !1,
                    listHeight: 176,
                    disabled: api.config.disEdit ? !0 : !1

                });
                if (rowData.account) {
                    $('#accountFselect .fs-input').val(rowData.account);
                    var names = rowData.account.toString().split(',');
                    $("#accountFselect .fs-option").each(function() {
                        if (names.indexOf($(this).children('.fs-option-label').html()) != -1) {
                            $(this).addClass("selected");
                        }
                    })
                }
            } else {
                $.dialog.tips(data.msg, 2);
            }
        });
        Public.ajaxGet('/Base/Billfrom/list', {isDisable: -1}, function(data) {
            if (data.status === 200) {
                var billFromNormal = billFromDisabled = '';
                $.each(data.data.rows, function(i, v) {
                    if (v.isDisable == 0) {
                        billFromNormal += '<option value="' + v.id + '">' + v.name + '</option>';
                    } else {
                        billFromDisabled += '<option value="' + v.id + '">' + v.name + '</option>';
                    }
                });
                $('#billFromNormal').html(billFromNormal);
                $('#billFromDisabled').html(billFromDisabled);
                /* 异步获取数据成功之后再填充选项并加载选择事件 */
                $('#billFromCombo').fSelect({
                    id: 'billFromFselect',
                    showSearch: !1,
                    listHeight: 176,
                    disabled: api.config.disEdit ? !0 : !1

                });
                if (rowData.billFrom) {
                    $('#billFromFselect .fs-input').val(rowData.billFrom);
                    var names = rowData.billFrom.toString().split(',');
                    $("#billFromFselect .fs-option").each(function() {
                        if (names.indexOf($(this).children('.fs-option-label').html()) != -1) {
                            $(this).addClass("selected");
                        }
                    })
                }
            } else {
                $.dialog.tips(data.msg, 2);
            }
        });
    },
    initEvent: function() {
        laydate.render({
            elem: "#date",
        });
        Public.limitLength($('#name'), 20);
        $('#upimg').uploader({
            action: '/Base/Company/uploadImg',
            mode: 'html5',
            name: 'file',
            formData: {},
            multiple: false,
            auto: true,
            debug: false,
            preview: false,
            showQueue: '#queue',
            fileSizeLimit: '10M',
            fileTypeDesc: '选择图片文件',
            fileTypeExts: 'jpg,jpeg,gif,png',
            onStart: function(e) {
                $('#manage-wrapper').prepend('<div id="div-lock"><div class="loading">上传中...</div></div>');
            },
            onError: function(e) {
                $.dialog.tips('上传错误！' + e.msg, 2);
            },
            onSuccess: function(e) {
                var data = JSON.parse(e.data);
                if (data.data) {
                    businessLicense = data.data;
                    $('#img').html('<a target="_blank" href="/data/upfile/company/' + data.data + '"><img src="/data/upfile/company/' + data.data + '" /></a>');
                }
            },
            onAllComplete: function() {
                $('#manage-wrapper > #div-lock').remove();
            }
        });
        $('.upload-el').on({
            click: function(a) {
                !Business.verifyRight('COMPANY_UPDATE') && a.preventDefault();
            },
            mouseover: function() {
                $(this).prev().addClass('black');
            },
            mouseleave: function() {
                $(this).prev().removeClass('black');
            }
        });
    },
    initValidator: function() {
        $('#manage-form').validate({
            rules: {
                name: {required: !0},
                fullName: {required: !0},
                managerName: {required: !0},
                no: {required: !0},
                address: {required: !0},
                date: {required: !0},
                categoryInput: {required: !0},
                invoicewayInput: {required: !0},
                invoicelimit: {required: !0},
                businessScope: {required: !0}
            }
        });
    },
    postData: function(callbackType = 0) {
        if (Business.verifyRight('COMPANY_UPDATE')) {
            if (!$('#manage-form').validate().form()) {
                return void $('#manage-form').find('input.valid-error').eq(0).focus();
            }
            var data = THISPAGE.getData();
            if ('add' == oper) {
                data && Public.ajaxPost('/Base/Company/add', data, function(res) {
                    if (res && 200 == res.status) {
                        parent.parent.$.dialog.tips('新增经营主体成功！');
                        if (callback && 'function' == typeof callback) {
                            callback(res.data, oper, window, callbackType);
                        }
                    } else {
                        $.dialog.tips('新增经营主体失败！' + res.msg, 2);
                    }
                });
            } else {
                if (rowData.name != data.name) {
                    $.dialog.confirm('注意：名称被改变！将导致使用过旧名称的所有地方也被改变，确定强行保存？', function() {
                        data && Public.ajaxPost('/Base/Company/update', data, function(res) {
                            if (res && 200 == res.status) {
                                parent.parent.$.dialog.tips('编辑经营主体成功！');
                                if (callback && 'function' == typeof callback) {
                                    callback(res.data, oper, window, callbackType);
                                }
                            } else {
                                $.dialog.tips('编辑经营主体失败！' + res.msg, 2);
                            }
                        });
                    });
                } else {
                    data && Public.ajaxPost('/Base/Company/update', data, function(res) {
                        if (res && 200 == res.status) {
                            parent.parent.$.dialog.tips('编辑经营主体成功！');
                            if (callback && 'function' == typeof callback) {
                                callback(res.data, oper, window, callbackType);
                            }
                        } else {
                            $.dialog.tips('编辑经营主体失败！' + res.msg, 2);
                        }
                    });
                }
            }
        }
    },
    getData: function() {
        var data = {
            id: cRowId || 0,
            name: $.trim($('#name').val()),
            fullName: $.trim($('#fullName').val()),
            no: $.trim($('#no').val()),
            address: $.trim($('#address').val()),
            managerName: $.trim($('#managerName').val()),
            date: $.trim($('#date').val()),
            type: parseInt(categoryCombo.getValue()),
            invoiceway: parseInt(invoicewayCombo.getValue()),
            invoicelimit: $.trim($('#invoicelimit').val()),
            account: $.trim($('#accountFselect .fs-input').val()),
            billFrom: $.trim($('#billFromFselect .fs-input').val()),
            businessScope: $.trim($('#businessScope').val()),
            businessLicense: businessLicense,
            description: $.trim($('#description').val())
        };
        return data;
    }
};
$(function() {
    THISPAGE.init();
});
