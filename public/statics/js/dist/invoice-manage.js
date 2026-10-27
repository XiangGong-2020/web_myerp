var api = frameElement.api;
var defaultPage = Public.getDefaultPage();
var oper = api.data.oper;
var rowData = api.data.rowData || {};
var callback = api.data.callback;
var usCombo;
var typeCombo;
var labelCombo;
var inoutTypeCombo;
var transTypeCombo;
var billNoList = {};
var THISPAGE = {
    init: function() {
        THISPAGE.initBtns();
        THISPAGE.initDom();
        THISPAGE.initValidator();
        THISPAGE.initEvent();
    },
    initBtns: function() {
        api.button({
            id: 'confirm',
            name: '保存',
            defClass: 'ui_state_highlight',
            callback: function() {
                return THISPAGE.postData(), !1;
            }
        }, {
            id: 'cancel',
            name: '关闭'
        });
    },
    initDom: function() {
        if (oper == 'edit') {
            $('#date').val(rowData.date);
            $('#code').val(rowData.code);
            $('#no').val(rowData.no);
            $('#amount').val(rowData.amount);
            $('#otherSide').val(rowData.otherSide);
            $('#uName').val(rowData.uName);
            $('#createTime').val(rowData.createTime);
            if (rowData.billNo) {
                billNoList = rowData.billNo;
                var totalAmount = 0;
                for (let [key, value] of Object.entries(rowData.billNo)) {
                    $('#billNoCon').append('<div class="billno-item" data-no="' + key + '"><input type="text" class="billno-no" value="' + key + '"/><input type="number" class="billno-amount" value="' + value + '"/><span class="billno-unit">元</span><span class="billno-delbtn" data-no="' + key + '"><i class="iconfont icon-shanchu f12" title="" diytitle="删除"></i></span></div>');
                    totalAmount += parseFloat(value);
                }
                $('#totalAmount').html('已关联：' + Number(totalAmount).toFixed(2) + ' 元');
                $('#reAmount').html('剩余：' + Number(parseFloat(rowData.amount) - totalAmount).toFixed(2) + ' 元');
            }
        } else {
            $('#uName').val(defaultPage.SYSTEM.uName);
            $('#createTime').val(new Date().format('yyyy-MM-dd hh:mm:ss'));
        }
        typeCombo = $('#type').combo({
            data: [
                {id: 0, name: '普通发票'},
                {id: 1, name: '增值税专用发票'},
                {id: 2, name: '火车票'}
            ],
            value: 'id',
            text: 'name',
            defaultSelected: rowData.type >= 0 ? ['id', rowData.type] : -1
        }).getCombo();
        usCombo = $('#us').combo({
            async: false, // 异步 否
            data: '/Base/Company/queryAll',
            ajaxOptions: {
                formatData: function(a) {
                    var company = [];
                    a.data.rows.forEach(element => {
                        if (element.id > 0) {
                            if (element.isDisable == 0) {
                                company.push(element);
                            }

                        }
                    });
                    return company;
                }
            },
            value: 'id',
            text: 'name',
            defaultSelected: rowData.us >= 0 ? ['id', rowData.us] : -1
        }).getCombo();
        labelCombo = $('#label').combo({
            data: [
                {id: 0, name: '原料与货物'},
                {id: 1, name: '自用易耗品'},
                {id: 2, name: '固定资产'},
                {id: 3, name: '研发费用'},
                {id: 4, name: '经营费用'},
                {id: 5, name: '管理费用'},
                {id: 6, name: '其他费用'}
            ],
            value: 'id',
            text: 'name',
            defaultSelected: rowData.label >= 0 ? ['id', rowData.label] : -1
        }).getCombo();
        inoutTypeCombo = $('#inoutType').combo({
            data: [
                {id: 0, name: '我方开出'},
                {id: 1, name: '我方收取'}
            ],
            value: 'id',
            text: 'name',
            defaultSelected: rowData.inoutType >= 0 ? ['id', rowData.inoutType] : -1
        }).getCombo();
        transTypeCombo = $('#transType').combo({
            data: [
                {id: 0, name: '未知'},
                {id: 101101, name: '采购订单'},
                {id: 101111, name: '特殊采购单'},
                {id: 101201, name: '销售订单'},
                {id: 101211, name: '特殊销售单'},
                {id: 103103, name: '其他收入单'},
                {id: 103104, name: '其他支出单'},
                {id: 109101, name: '快递月结单'}
            ],
            value: 'id',
            text: 'name',
            defaultSelected: rowData.transType >= 0 ? ['id', rowData.transType] : 0
        }).getCombo();
        $('#us').find('input').attr('name', 'usInput');
        $('#type').find('input').attr('name', 'typeInput');
        $('#label').find('input').attr('name', 'labelInput');
        $('#inoutType').find('input').attr('name', 'inoutTypeInput');
        $('#transType').find('input').attr('name', 'transTypeInput');
        if ($.inArray('INVOICE_ADVANCE', defaultPage.SYSTEM.rights) === -1 || (rowData && rowData.uName != defaultPage.SYSTEM.uName)) {
            typeCombo.disable();
            usCombo.disable();
            inoutTypeCombo.disable();
            $('#code').attr('disabled', true).addClass('ui-input-dis');
            $('#no').attr('disabled', true).addClass('ui-input-dis');
            $('#date').attr('disabled', true).addClass('ui-input-dis');
            $('#amount').attr('disabled', true).addClass('ui-input-dis');
            $('#otherSide').attr('disabled', true).addClass('ui-input-dis');
        }
    },
    initValidator: function() {
        $.validator.addMethod('checkInvoiceCode', function(a) {
            var no = $.trim($('#no').val());
            if ((no.length == 8 || no.length == 10) && a.length != 8 && a.length != 10 && a.length != 12) {
                return !1;
            }
            if (no.length == 20 && a.length > 0) {
                return !1;
            }
            return a ? /^[0-9]{8}$|^[0-9]{10}$|^[0-9]{12}$/.test(a) : !0;
        });
        $.validator.addMethod('checkInvoiceNo', function(a) {
            var code = $.trim($('#code').val());
            if (code.length > 0 && a.length != 8 && a.length != 10) {
                return !1;
            }
            return a ? /^[0-9]{8}$|^[0-9]{10}$|^[0-9]{20}$/.test(a) : !1;
        });
        $('#manage-form').validate({
            rules: {
                date: {required: !0},
                code: {checkInvoiceCode: !0},
                no: {required: !0, checkInvoiceNo: !0},
                amount: {required: !0, number: !0},
                typeInput: {required: !0},
                otherSide: {required: !0},
                usInput: {required: !0},
                labelInput: {required: !0},
                inoutTypeInput: {required: !0},
                transTypeInput: {required: !0}
            }
        });
    },
    initEvent: function() {
        laydate.render({
            elem: "#date",
        });
        $('#addBillNo').on('click', function() {
            var curno = $.trim($('#curno').val());
            var curamount = $.trim($('#curamount').val());
            if (curno == '' || curno.length < 15) {
                $.dialog.tips('单据编号不合规，请检查！', 2);
                return !1;
            } else if (curamount == '' || Number(curamount).toFixed(2) <= 0) {
                $.dialog.tips('金额必须大于零，请检查！', 2);
                return !1;
            } else if (billNoList[curno]) {
                $.dialog.tips('该单据编号已添加过！请勿重复添加！', 2);
                return !1;
            } else {
                billNoList[curno] = curamount;
                $('#billNoCon').append('<div class="billno-item" data-no="' + curno + '"><input type="text" class="billno-no" value="' + curno + '"/><input type="number" class="billno-amount" value="' + curamount + '"/><span class="billno-unit">元</span><span class="billno-delbtn" data-no="' + curno + '"><i class="iconfont icon-shanchu f12" title="" diytitle="删除"></i></span></div>');
                $('#curno').val('');
                $('#curamount').val('');
                var totalAmount = 0;
                for (let [key, value] of Object.entries(billNoList)) {
                    totalAmount += parseFloat(value);
                }
                $('#totalAmount').html('已关联：' + Number(totalAmount).toFixed(2) + ' 元');
                $('#reAmount').html('剩余：' + Number(parseFloat($.trim($('#amount').val()).replace(/,/g, '')) - totalAmount).toFixed(2) + ' 元');
            }
        });
        $('#billNoCon').on('click', '.billno-delbtn', function() {
            var no = $(this).data('no');
            delete billNoList[no] && $(this).parent().remove();
            var totalAmount = 0;
            for (let [key, value] of Object.entries(billNoList)) {
                totalAmount += parseFloat(value);
            }
            $('#totalAmount').html('已关联：' + Number(totalAmount).toFixed(2) + ' 元');
            $('#reAmount').html('剩余：' + Number(parseFloat($.trim($('#amount').val()).replace(/,/g, '')) - totalAmount).toFixed(2) + ' 元');
        });
        $('#amount').on('blur', function() {
            billNoList = {};
            var newBillNos = $('#billNoCon').find('.billno-item');
            var totalAmount = 0;
            var errInfo = '';
            $.each(newBillNos, function(i, v) {
                var billno = $.trim($(v).children('.billno-no').val());
                var amount = Number($.trim($(v).children('.billno-amount').val())).toFixed(2);
                if (billno && billno.length >= 15 && parseFloat(amount) > 0) {
                    billNoList[billno] = amount;
                    totalAmount += parseFloat(amount);
                } else {
                    errInfo = '第 ' + (i + 1) + ' 个单据的编号或金额不对，请检查！';
                    return !1;
                }
            });
            $('#totalAmount').html('已关联：' + Number(totalAmount).toFixed(2) + ' 元');
            $('#reAmount').html('剩余：' + Number(parseFloat($.trim($('#amount').val()).replace(/,/g, '')) - totalAmount).toFixed(2) + ' 元');
            if (errInfo) {
                $.dialog.tips(errInfo, 2);
            }
        });
        $('#billNoCon').on('blur', '.billno-amount', function() {
            billNoList = {};
            var newBillNos = $('#billNoCon').find('.billno-item');
            var totalAmount = 0;
            var errInfo = '';
            $.each(newBillNos, function(i, v) {
                var billno = $.trim($(v).children('.billno-no').val());
                var amount = Number($.trim($(v).children('.billno-amount').val())).toFixed(2);
                if (billno && billno.length >= 15 && parseFloat(amount) > 0) {
                    billNoList[billno] = amount;
                    totalAmount += parseFloat(amount);
                } else {
                    errInfo = '第 ' + (i + 1) + ' 个单据的编号或金额不对，请检查！';
                    return !1;
                }
            });
            $('#totalAmount').html('已关联：' + Number(totalAmount).toFixed(2) + ' 元');
            $('#reAmount').html('剩余：' + Number(parseFloat($.trim($('#amount').val()).replace(/,/g, '')) - totalAmount).toFixed(2) + ' 元');
            if (errInfo) {
                $.dialog.tips(errInfo, 2);
            }
        });
    },
    postData: function(a, b) {
        if (Business.verifyRight('INVOICE_UPDATE')) {
            if (!$('#manage-form').validate().form()) {
                return void $('#manage-form').find('input.valid-error').eq(0).focus();
            }
            billNoList = {};
            var newBillNos = $('#billNoCon').find('.billno-item');
            var totalAmount = 0;
            var errInfo = '';
            $.each(newBillNos, function(i, v) {
                var billno = $.trim($(v).children('.billno-no').val());
                var amount = Number($.trim($(v).children('.billno-amount').val())).toFixed(2);
                if (billno && billno.length >= 15 && parseFloat(amount) > 0) {
                    billNoList[billno] = amount;
                    totalAmount += parseFloat(amount);
                } else {
                    errInfo = '第 ' + (i + 1) + ' 个单据的编号或金额不对，请检查！';
                    return !1;
                }
            });
            $('#totalAmount').html('已关联：' + Number(totalAmount).toFixed(2) + ' 元');
            $('#reAmount').html('剩余：' + Number(parseFloat($.trim($('#amount').val()).replace(/,/g, '')) - totalAmount).toFixed(2) + ' 元');
            if (errInfo) {
                $.dialog.tips(errInfo, 2);
            } else if (Number(totalAmount).toFixed(2) != Number($.trim($('#amount').val()).replace(/,/g, '')).toFixed(2)) {
                $.dialog.tips('关联单据总金额与发票价税合计金额不符，请检查！', 2);
            } else {
                Public.ajaxPost('/Base/Invoice/' + (oper === 'edit' ? 'update' : 'add'), {
                    id: rowData.id || 0,
                    date: $.trim($('#date').val()),
                    code: $.trim($('#code').val()),
                    no: $.trim($('#no').val()),
                    amount: $.trim($('#amount').val()).replace(/,/g, ''),
                    otherSide: $.trim($('#otherSide').val()),
                    us: usCombo.getValue() || rowData.us,
                    type: typeCombo.getValue(),
                    label: labelCombo.getValue(),
                    inoutType: inoutTypeCombo.getValue(),
                    transType: transTypeCombo.getValue(),
                    billNo: billNoList,
                    uName: $.trim($('#uName').val()),
                    createTime: $.trim($('#createTime').val())
                }, function(res) {
                    if (res && 200 == res.status) {
                        parent.parent.parent.$.dialog.tips((oper === 'edit' ? '编辑' : '新增') + '成功！');
                        if (callback && 'function' == typeof callback) {
                            callback(res.data, oper, window);
                        };
                    } else {
                        $.dialog.tips((oper === 'edit' ? '编辑' : '新增') + '失败！' + res.msg, 2, 20);
                    }
                });
            }
        }
    }
};
$(function() {
    THISPAGE.init();
});
