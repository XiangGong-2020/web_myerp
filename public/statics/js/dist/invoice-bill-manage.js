var api = frameElement.api;
var defaultPage = Public.getDefaultPage();
var transTypeObj = {
    '101101': '采购订单',
    '101102': '采购直发订单',
    '101111': '特殊采购单',
    '101201': '销售订单',
    '101211': '特殊销售单',
    '103103': '其他收入单',
    '103104': '其他支出单',
    '109101': '快递月结单'
};
var labelArr = [
    '原料与货物',
    '自用易耗品',
    '固定资产',
    '研发费用',
    '经营费用',
    '管理费用',
    '其他费用'
];
var inoutType = $.inArray(parseInt(api.data.transType), [101101,101102,101111,103104,109101]) != -1 ? 1 : 0;
var companyAll = {};
var company = [];
var typeCombo = $('#type').combo({
    data: [
        {id: 0, name: '普通发票'},
        {id: 1, name: '增值税专用发票'},
        {id: 2, name: '火车票'}
    ],
    value: 'id',
    text: 'name',
    defaultSelected: 0
}).getCombo();
var usCombo = $('#us').combo({
    data: '/Base/Company/queryAll',
    ajaxOptions: {
        formatData: function(a) {
            a.data.rows.forEach(element => {
                if (element.id > 0) {
                    var id = '' + element.id;
                    companyAll[id] = element.name;
                    if (element.isDisable == 0) {
                        company.push(element);
                    }
                }
            });
            THISPAGE.initInvoices();
            return company;
        }
    },
    value: 'id',
    text: 'name',
    defaultSelected: api.data.us ? ['id', api.data.us] : ['id', 17]
}).getCombo();
var labelCombo = $('#label').combo({
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
    defaultSelected: $.inArray(parseInt(api.data.transType), [101101, 101102, 101111, 101201, 101211]) != -1 ? 0 : ($.inArray(parseInt(api.data.transType), [109101]) != -1 ? 4 : -1)
}).getCombo();
var inoutTypeCombo = $('#inoutType').combo({
    data: [{id: 0, name: '我方开出'}, {id: 1, name: '我方收取'}],
    value: 'id',
    text: 'name',
    defaultSelected: inoutType,
    disabled: !0
}).getCombo();
var THISPAGE = {
    init: function() {
        THISPAGE.initBtns();
        THISPAGE.initDom();
        THISPAGE.initValidator();
        THISPAGE.initEvent();
    },
    initBtns: function() {
        api.button({
            id: 'cancel',
            name: '关闭',
            callback: function() {
                if (api.data.callback && 'function' == typeof api.data.callback) {
                    api.data.callback();
                }
            }
        });
    },
    initDom: function() {
        $('#date').val((new Date).format("yyyy-MM-dd"));
        $('#billNo').val(api.data.billNo);
        $('#transTypeName').val(transTypeObj[api.data.transType]);
        $('#uName').val(defaultPage.SYSTEM.uName);
        $('#type').find('input').attr('name', 'typeInput');
        $('#us').find('input').attr('name', 'usInput');
        $('#inoutType').find('input').attr('name', 'inoutTypeInput');
        $('#label').find('input').attr('name', 'labelInput');
        if ($.inArray('FINANCE_MANAGER', defaultPage.SYSTEM.rights) === -1) {
            typeCombo.disable();
            $('#code').attr('readonly', true);
            $('#no').attr('readonly', true);
            $('#date').attr('disabled', true);
            $('#amount').attr('readonly', true);
            usCombo.disable();
            $('#otherSide').attr('readonly', true);
        }
        if ($.inArray(parseInt(api.data.transType), [101101, 101102, 101111, 101201, 101211]) != -1) {
            labelCombo.disable();
        }
    },
    initInvoices: function() {
        Public.ajaxPost('/Base/Invoice/queryAll', {
            billNo: api.data.billNo
        }, function(res) {
            if (200 === res.status) {
                var invoicesTotalHtml = '本单已收发票金额：0 元';
                if (res.data.records > 0) {
                    var rows = res.data.rows;
                    var invoicesAmount = 0;
                    var invoicesHtml = '';
                    for (var i = 0; i < rows.length; i++) {
                        invoicesAmount += parseFloat(rows[i].billNo[api.data.billNo]);
                        invoicesHtml += (
                            '<div class="invoice' + (rows[i].uName == defaultPage.SYSTEM.uName ? ' my-invoice' : '') + '" id="invoice' + rows[i].id + '" data-id="' + rows[i].id + '">' +
                            '<div class="invoice-con">' +
                            '<div class="invoice-info">发票类别：' + (rows[i].type == 0 ? '普通发票' : '增值税专用发票') + '</div>' +
                            '<div class="invoice-info">发票代码：' + rows[i].code + '</div>' +
                            '<div class="invoice-info">发票号码：' + rows[i].no + '</div>' +
                            '<div class="invoice-info">开票日期：' + rows[i].date + '</div>' +
                            '<div class="invoice-info">价税合计：' + (rows[i].amount || '待验证') + '</div>' +
                            '<div class="invoice-info">我方主体：' + companyAll[rows[i].us] + '</div>' +
                            '<div class="invoice-info" style="width:950px">对方主体：' + (rows[i].otherSide || '待验证') + '</div>' +
                            '<div class="invoice-info">往来区分：' + (rows[i].inoutType == 0 ? '我方开出' : '我方收取') + '</div>' +
                            '<div class="invoice-info">性质标签：' + labelArr[rows[i].label] + '</div>' +
                            '<div class="invoice-info">业务类别：' + transTypeObj[rows[i].transType] + '</div>' +
                            '<div class="invoice-info">录入人：' + rows[i].uName + '</div>' +
                            '<div class="invoice-info">录入时间：' + rows[i].createTime + '</div>' +
                            '</div>' +
                            '<div class="invoice-bottom">' +
                            '<div class="invoice-billno" title=\'' + JSON.stringify(rows[i].billNo) + '\'>关联单据：' + JSON.stringify(rows[i].billNo) + '</div>' +
                            '</div>' +
                            (rows[i].uName == defaultPage.SYSTEM.uName ? (
                            '<div class="invoice-btns ui-btn-group">' +
                            '<div class="ui-btn invoice-edit" data-id="' + rows[i].id + '" data-uname="' + rows[i].uName + '"><i class="iconfont icon-bianji"></i><span>编辑</span></div>' +
                            '<div class="ui-btn bg-red invoice-del" data-id="' + rows[i].id + '" data-uname="' + rows[i].uName + '"><i class="iconfont icon-shanchu"></i><span>删除</span></div>' +
                            '</div>') : '') +
                            '</div>'
                        );
                    };
                    $('#invoices').html(invoicesHtml);
                    invoicesTotalHtml = '本单已收发票金额：' + Number(invoicesAmount).toFixed(2) + ' 元';
                }
                $('#invoicesAmount').html(invoicesTotalHtml);
                if (res.data.userAmountData) {
                    var userAmountHtml = '';
                    for (var uName in res.data.userAmountData) {
                        if (res.data.userAmountData.hasOwnProperty(uName)) {
                            var data = res.data.userAmountData[uName];
                            userAmountHtml += '<div class="rel fl w300 h30 mt5 ml10 lh30 ti10 white bg-gray b-r-3 ovh"><div class="abs h30 ' + (Number(data.invoiceAmount) > Number(data.paidAmount) ? 'bg-purple' : (Number(data.invoiceAmount) == Number(data.paidAmount) ? 'bg-green' : 'bg-red')) + '" style="width:' + Math.min(100, Math.max(1, Number(Number(data.invoiceAmount) / Number(data.paidAmount) * 100).toFixed(1)))  + '%"></div><div class="abs" style="left:0; top:0; right:0;" title="' + (Number(data.invoiceAmount) > Number(data.paidAmount) ? '已超额收票' : (Number(data.invoiceAmount) == Number(data.paidAmount) ? '已足额收票' : '未足额收票')) + '">' + uName + ' 应收：' + Number(data.paidAmount).toFixed(2) + ' 元，已收：' + Number(data.invoiceAmount).toFixed(2) + ' 元</div></div>';
                        }
                    }
                    $('#invoices').after(userAmountHtml);
                }
            } else {
                $.dialog.tips(res.msg, 2);
            }
        });
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
                typeInput: {required: !0},
                code: {checkInvoiceCode: !0},
                no: {required: !0, checkInvoiceNo: !0},
                date: {required: !0},
                amount: {required: !0, number: !0},
                usInput: {required: !0},
                otherSide: {required: !0},
                inoutTypeInput: {required: !0},
                labelInput: {required: !0}
            }
        });
    },
    initEvent: function() {
        laydate.render({
            elem: "#date",
        });
        $('.des').on('click', function() {
            if ($('.des-con').hasClass('dn')) {
                $('.des-con').removeClass('dn');
            } else {
                $('.des-con').addClass('dn');
            }
        });
        $('.des-close').on('click', function() {
            $('.des-con').addClass('dn');
        });
        $('#upfile').uploader({
            action: '/Base/Invoice/uploadInvoice',
            mode: 'html5',
            name: 'file',
            formData: {inoutType: inoutType, billNo: $.trim($('#billNo').val())},
            multiple: false,
            auto: true,
            debug: false,
            preview: false,
            showQueue: '#queue',
            fileSizeLimit: '10M',
            fileTypeDesc: '选择文件',
            fileTypeExts: 'pdf,ofd,png,jpg,jpeg,bmp',
            onStart: function(e) {
                $('.manage-wrapper').append('<div id="div-lock"><div class="loading">上传中...</div></div>');
                $('#code').val('');
                $('#no').val('');
                $('#date').val('');
                $('#amount').val('');
                $('#otherSide').val('');
            },
            onError: function(e) {
                $.dialog.tips('上传错误！', 2);
            },
            onSuccess: function(e) {
                var data = JSON.parse(e.data);
                if (data.status == 200) {
                    typeCombo._setDefaultSelected(['id', data.data.type]);
                    usCombo._setDefaultSelected(['id', data.data.us]);
                    $('#code').val(data.data.code);
                    $('#no').val(data.data.no);
                    $('#date').val(data.data.date);
                    $('#amount').val(data.data.amount);
                    $('#otherSide').val(data.data.otherSide);
                    $('.manage-wrapper > #div-lock > .loading').css({
                        'background': '#07c160',
                        'border': '5px solid #fff',
                        'border-radius': '5px',
                        'padding': "10px 20px",
                        'top': '105px'
                    }).html('<span class="white">识别成功，即将自动提交！</span>');
                    setTimeout(() => {
                        $('#subInvoice').trigger('click');
                    }, 500);
                } else {
                    $.dialog.tips(data.msg, 2);
                }
            },
            onAllComplete: function() {
                setTimeout(() => {
                    $('.manage-wrapper > #div-lock').remove();
                }, 1500);
            }
        });
        $('.upload-el').on({
            click: function(a) {
                !Business.verifyRight('INVOICE_ADD') && a.preventDefault();
            },
            mouseover: function() {
                if ($(this).prev().hasClass('ui-btn')) {
                    $(this).prev().addClass('ui-btn-hover');
                } else {
                    $(this).prev().addClass('black');
                }
            },
            mouseleave: function() {
                if ($(this).prev().hasClass('ui-btn')) {
                    $(this).prev().removeClass('ui-btn-hover');
                } else {
                    $(this).prev().removeClass('black');
                }
            }
        });
        $('#subInvoice').on('click', function() {
            THISPAGE.postData();
        });
        $('#invoices').on('click', '.invoice-edit', function(a) {
            a.preventDefault();
            if (Business.verifyRight('INVOICE_UPDATE')) {
                var id = $(this).data('id');
                Public.ajaxPost('/Base/Invoice/query', {
                    id: id
                }, function(res) {
                    if (res && 200 == res.status) {
                        $.dialog({
                            title: '编辑发票记录',
                            content: 'url:/Uri/invoice_manage',
                            data: {
                                oper: 'edit',
                                rowData: res.data,
                                callback: THISPAGE.callback
                            },
                            width: 1100,
                            height: 500,
                            min: !1,
                            max: !1,
                            cache: !1,
                            lock: !0
                        });
                    } else {
                        $.dialog.tips('获取数据失败！', 2);
                    }
                });
            }
        });
        $('#invoices').on('click', '.invoice-del', function(a) {
            a.stopPropagation();
            if (Business.verifyRight('INVOICE_DELETE')) {
                var id = $(this).data('id');
                id && $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                    Public.ajaxPost('/Base/Invoice/delete', {
                        id: id
                    }, function(res) {
                        if (res && 200 == res.status) {
                            $.dialog.tips('删除成功！');
                            $('#invoice' + id).remove();
                        } else {
                            $.dialog.tips('删除失败！' + res.msg, 2);
                        }
                    });
                });
            }
        });
    },
    postData: function(a, b) {
        if (Business.verifyRight('INVOICE_ADD')) {
            if (!$('#manage-form').validate().form()) {
                return void $('#manage-form').find('input.valid-error').eq(0).focus();
            }
            var billNoObj = {};
            billNoObj[$.trim($('#billNo').val())] = Number($.trim($('#amount').val()).replace(/,/g, '')).toFixed(2);
            Public.ajaxPost('/Base/Invoice/add', {
                type: typeCombo.getValue(),
                code: $.trim($('#code').val()),
                no: $.trim($('#no').val()),
                date: $.trim($('#date').val()),
                amount: $.trim($('#amount').val()).replace(/,/g, ''),
                us: usCombo.getValue(),
                otherSide: $.trim($('#otherSide').val()),
                inoutType: inoutTypeCombo.getValue(),
                label: labelCombo.getValue(),
                transType: api.data.transType,
                billNo: billNoObj,
                uName: $.trim($('#uName').val())
            }, function(res) {
                if (res && 200 == res.status) {
                    $.dialog.tips('添加成功！');
                    THISPAGE.initDom();
                    THISPAGE.initInvoices();
                } else {
                    $.dialog.tips('添加失败！' + res.msg, 2);
                }
            });
        }
    },
    callback: function(a, b, c) {
        THISPAGE.initInvoices(), c && c.api.close();
    }
};
$(function() {
    THISPAGE.init();
});
