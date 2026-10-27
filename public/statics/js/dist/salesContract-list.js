var urlParam = Public.urlParam();
var defaultPage = Public.getDefaultPage();
var qtyPlaces = Number(defaultPage.SYSTEM.qtyPlaces);
var pricePlaces = Number(defaultPage.SYSTEM.pricePlaces);
var amountPlaces = Number(defaultPage.SYSTEM.amountPlaces);
var queryConditions = {
    status0: -1,
    status1: urlParam.uName || '',
    status2: -1,
    status3: urlParam.goodsStatus1 || -1,
    status4: -1,
    status5: -1,
    beginDate: '',
    endDate: urlParam.endDate || '',
    minAmount: '',
    maxAmount: '',
    skey: '',
    unClear: urlParam.unClear || 0,
    unDeliver: urlParam.unDeliver || 0
};
var goodsShowAll = !1;
var company = [];
var statusCombo0 = $('#combo0').combo({
    data: '/Base/Company/queryAll',
    ajaxOptions: {
        formatData: function(a) {
            return a.data.rows.unshift({
                id: -1,
                name: '所有卖方'
            }), a.data.rows;
        }
    },
    value: 'id',
    text: 'name',
    defaultSelected: 0,
    width: 90,
    listHeight: 500,
    callback: {
        onListClick: function() {
            $('#search').trigger('click');
        }
    }
}).getCombo();
var statusCombo1 = $('#combo1').combo({
    data: '/Base/User/queryAll?department=销售部',
    ajaxOptions: {
        formatData: function(a) {
            return a.data.rows.unshift({
                id: -1,
                name: '所有销售人'
            }, {
                id: 1,
                name: '系统'
            }, {
                id: 7,
                name: '杨小兰'
            }, {
                id: 8,
                name: '周猷川'
            }), a.data.rows;
        }
    },
    value: 'id',
    text: 'name',
    defaultSelected: urlParam.uName ? ['name', urlParam.uName] : 0,
    width: 90,
    listHeight: 500,
    callback: {
        onListClick: function() {
            $('#search').trigger('click');
        }
    }
}).getCombo();
var statusCombo2 = $('#combo2').combo({
    data: [
        {id: -1, name: '所有收款'},
        {id: 0, name: '未收'},
        {id: 1, name: '部分'},
        {id: 2, name: '收完'}
    ],
    value: 'id',
    text: 'name',
    defaultSelected: 0,
    width: 78,
    listHeight: 500,
    callback: {
        onListClick: function() {
            $('#search').trigger('click');
        }
    }
}).getCombo();
var statusCombo3 = $('#combo3').combo({
    data: [
        {id: -1, name: '所有交货'},
        {id: 0, name: '未交货'},
        {id: 1, name: '已交货'}
    ],
    value: 'id',
    text: 'name',
    defaultSelected: urlParam.goodsStatus1 ? ['id', parseInt(urlParam.goodsStatus1)] : 0,
    width: 78,
    listHeight: 500,
    callback: {
        onListClick: function() {
            $('#search').trigger('click');
        }
    }
}).getCombo();
var statusCombo4 = $('#combo4').combo({
    data: [
        {id: -1, name: '所有退款'},
        {id: 0, name: '暂无'},
        {id: 1, name: '部分'},
        {id: 2, name: '退完'}
    ],
    value: 'id',
    text: 'name',
    defaultSelected: 0,
    width: 78,
    listHeight: 500,
    callback: {
        onListClick: function() {
            $('#search').trigger('click');
        }
    }
}).getCombo();
var statusCombo5 = $('#combo5').combo({
    data: [
        {id: -1, name: '所有退货'},
        {id: 0, name: '无退货'},
        {id: 1, name: '有退货'}
    ],
    value: 'id',
    text: 'name',
    defaultSelected: 0,
    width: 78,
    listHeight: 500,
    callback: {
        onListClick: function() {
            $('#search').trigger('click');
        }
    }
}).getCombo();
var THISPAGE = {
    init: function() {
        THISPAGE.initDom();
        THISPAGE.initGrid();
        THISPAGE.initEvent();
    },
    initDom: function() {
        $('#beginDate').val(defaultPage.SYSTEM.beginDate);
        $('#endDate').val(urlParam.endDate || defaultPage.SYSTEM.endDate);
    },
    initGrid: function() {
        var a = Public.setGrid();
        $('#grid').jqGrid({
            url: '/Bill/InvSc/list',
            postData: queryConditions,
            datatype: 'json',
            colModel: [{
                name: 'billNo',
                label: '<span class="ml10">单据编号</span>',
                width: 160,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ti10'
            }, {
                name: 'billDate',
                label: '单据日期',
                width: 70,
                fixed: !0,
                sortable: !0
            }, {
                name: 'contactName',
                label: '买方',
                width: 70,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis hover'
            }, {
                name: 'saler',
                label: '销售人',
                width: 90,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray'
            }, {
                name: 'description',
                label: '备注',
                width: 100,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray'
            }, {
                name: 'goods',
                label: '包含产品',
                nameExt: '<span class="goods-showall"><i class="iconfont icon-shuangxiajiantou f12 mr5"></i>展开全部</span>',
                width: 100,
                formatter: function(b, c, d) {
                    if (b) {
                        var e = b.length;
                        var f = '';
                        var g = 0;
                        if (0 < e) {
                            $.each(b, function(i, v) {
                                var goodsText =
                                    (v['no'] ? v['no'] + ' ' : '') +
                                    v['name'] +
                                    (v['spec'] ? ' ' + v['spec'] : '') +
                                    (v['sku'] ? ' ' + v['sku'] : '') +
                                    (v['brand'] ? ' ' + v['brand'] : '') +
                                    (v['qty'] ? ' × ' + Number(v['qty']) : '') +
                                    (v['unit'] ? v['unit'] : '') +
                                    (v['price'] ? ' × ' + Number(v['price']) + '元' : '') +
                                    (v['amount'] ? ' = ' + Number(v['amount']) + '元' : '');
                                f += ('<div class="goods-line es" title="' + goodsText + '">' + goodsText + '</div>');
                                g += parseFloat(v['qty']);
                            });
                        }
                        return (
                            '<div class="goods-total"><span class="goods-num-1">' +
                            e +
                            ' 种</span><span class="goods-num-2">' +
                            parseInt(g) +
                            ' 件</span><span class="goods-num-3-1">' +
                            d.amount +
                            ' 元</span><span class="goods-num-3-2 es" title="' + (d.zbjType == 0 ? '' : ((d.zbjType == 1 ? '另外支付保证金 ' : '从货款押保证金 ') + d.zbjAmount + ' 元')) + '">' +
                            (d.zbjType == 0 ? '' : ((d.zbjType == 1 ? '另外支付保证金 ' : '从货款押保证金 ') + d.zbjAmount + ' 元')) +
                            '</span><span class="goods-showinfo iconfont icon-xiajiantou"></span></div><div class="goods-info">' +
                            f +
                            '</div>'
                        );
                    } else {
                        return '&nbsp;';
                    }
                }
            }, {
                name: 'checkId',
                label: '审核',
                width: 42,
                fixed: !0,
                sortable: !0,
                align: 'center',
                formatter: function(a, b, c) {
                    if (c.isDraft == 1) {
                        return '<span class="db h28 lh28 b-r-3 hover-gray" title="目前为草稿 无法进行更多操作">草稿</span>';
                    }
                    var d = a == 0 ? '未审' : '已审';
                    var e = a == 0 ? '点击审核' : '点击反审核';
                    var f = a == 0 ? 'hover-red' : 'hover-green';
                    return '<span class="check-btn db h28 lh28 b-r-3 ' + f + '" title="' + e + '" data-checkid="' + a + '" data-id="' + c.id + '">' + d + '</span>';
                }
            }, {
                name: 'moneyStatus1',
                label: '收款',
                width: 42,
                fixed: !0,
                sortable: !0,
                align: 'center',
                formatter: function(a, b, c) {
                    if (c.isDraft == 1) {
                        return '<span class="db h28 lh28 b-r-3 hover-gray" title="目前为草稿 无法进行更多操作">草稿</span>';
                    }
                    var d = a == 0 ? '未收' : (a == 1 ? '部分' : '收完');
                    var f = a == 0 ? 'hover-red' : (a == 1 ? 'hover-yellow' : 'hover-green');
                    var g = c.srcMoneyId1 == 0 ? '点击收款' : '点击打开销售收款单 ' + '\r\n' + c.srcMoneyNo1;
                    if (c.redoSoId > 0 && c.redoSoType == 1) {
                        return '<span class="db h28 lh28 b-r-3 hover-gray" title="另外发货单据无需收款">无需</span>';
                    } else {
                        return '<span class="moneyStatus1-btn db h28 lh28 b-r-3 ' + f + '" title="' + g + '" data-id="' + c.id + '" data-checkid="' + c.checkId + '" data-srcmoneyid1="' + c.srcMoneyId1 + '">' + d + '</span>';
                    }
                }
            }, {
                name: 'goodsStatus1',
                label: '交货',
                width: 42,
                fixed: !0,
                sortable: !0,
                align: 'center',
                formatter: function(a, b, c) {
                    if (c.isDraft == 1) {
                        return '<span class="db h28 lh28 b-r-3 hover-gray" title="目前为草稿 无法进行更多操作">草稿</span>';
                    }
                    var d = a == 0 ? '未交' : '已交';
                    var f = a == 0 ? 'hover-red' : 'hover-green';
                    var g = a == 0 ? ('点击标记为已交货') : '点击标记为未交货';
                    return '<span class="goodsStatus1-btn db h28 lh28 b-r-3 ' + f + '" title="' + g + '" data-id="' + c.id + '" data-checkid="' + c.checkId + '" data-goodsstatus1="' + a + '">' + d + '</span>';
                }
            }, {
                name: 'moneyStatus2',
                label: '退款',
                width: 42,
                fixed: !0,
                sortable: !0,
                align: 'center',
                formatter: function(a, b, c) {
                    if (c.isDraft == 1) {
                        return '<span class="db h28 lh28 b-r-3 hover-gray" title="目前为草稿 无法进行更多操作">草稿</span>';
                    }
                    var d = a == 1 ? '部分' : (a == 2 ? '退完' : '暂无');
                    var f = a == 1 ? 'hover-yellow' : (a == 2 ? 'hover-green' : 'hover-white');
                    var g = c.srcMoneyId2 == 0 ? '点击退款' : '点击打开销售退款单 ' + '\r\n' + c.srcMoneyNo2;
                    if (c.redoSoId > 0 && c.redoSoType == 1) {
                        return '<span class="db h28 lh28 b-r-3 hover-gray" title="另外发货单据无需退款">无需</span>';
                    } else {
                        return '<span class="moneyStatus2-btn db h28 lh28 b-r-3 ' + f + '" title="' + g + '" data-id="' + c.id + '" data-checkid="' + c.checkId + '" data-srcmoneyid2="' + c.srcMoneyId2 + '">' + d + '</span>';
                    }
                }
            }, {
                name: 'goodsStatus2',
                label: '退货',
                width: 42,
                hidden: !0,
                fixed: !0,
                sortable: !0,
                align: 'center',
                formatter: function(a, b, c) {
                    if (c.isDraft == 1) {
                        return '<span class="db h28 lh28 b-r-3 hover-gray" title="目前为草稿 无法进行更多操作">草稿</span>';
                    }
                    var d = a == 0 ? '暂无' : '有退';
                    var f = a == 0 ? 'hover-white' : 'hover-green';
                    var g = a == 0 ? '点击设置为有退货' : '点击设置为无退货';
                    return '<span class="goodsStatus2-btn db h28 lh28 b-r-3 ' + f + '" title="' + g + '" data-id="' + c.id + '" data-checkid="' + c.checkId + '" data-goodsstatus2="' + a + '">' + d + '</span>';
                }
            }, {
                name: 'invoiceNum',
                label: '发票',
                width: 42,
                fixed: !0,
                sortable: !0,
                align: 'center',
                formatter: function(a, b, c) {
                    if (c.isDraft == 1) {
                        return '<span class="db h28 lh28 b-r-3 hover-gray" title="目前为草稿 无法进行更多操作">草稿</span>';
                    }
                    var d = a > 0 ? 'hover-green' : 'hover-white';
                    return '<span class="invoice-btn db h28 lh28 b-r-3 ' + d + '" title="点击管理发票" data-id="' + c.id + '">' + a + '</span>';
                }
            }, {
                name: 'isReq',
                label: '特采申请',
                width: 66,
                fixed: !0,
                sortable: !0,
                align: 'center',
                formatter: function(a, b, c) {
                    if (c.isDraft == 1) {
                        return '<span class="db h28 lh28 b-r-3 hover-gray" title="目前为草稿 无法进行更多操作">草稿</span>';
                    }
                    var d = a == 3 ? 'hover-green' : 'hover-white';
                    var e = a == 3 ? '有申请' : '暂无';
                    var f = a == 3 ? c.reqNo : '';
                    return '<span class="db h28 lh28 b-r-3 def ' + d + '" title="' + f + '">' + e + '</span>';
                }
            }, {
                name: 'id',
                label: 'id',
                width: 0,
                hidden: !0
            }, {
                name: 'amount',
                label: '单据金额',
                hidden: !0
            }, {
                name: 'paidAmount',
                label: '已收支金额',
                hidden: !0
            }, {
                name: 'transType',
                label: '类型',
                hidden: !0
            }, {
                name: 'contactId',
                label: '买方(对方) ID',
                hidden: !0
            }, {
                name: 'sId',
                label: '我方经营主体Id',
                hidden: !0
            }, {
                name: 'sName',
                label: '我方经营主体名称',
                hidden: !0
            }, {
                name: 'srcMoneyId1',
                label: '付款单ID',
                hidden: !0
            }, {
                name: 'srcMoneyId2',
                label: '收款单ID',
                hidden: !0
            }, {
                name: 'operate',
                label: '操作',
                width: 72.8,
                fixed: !0,
                align: 'center',
                formatter: Public.operFmatter
            }],
            cmTemplate: {sortable: !1, title: !1},
            jsonReader: {root: 'data.rows', records: 'data.records', total: 'data.total', repeatitems: !1, id: 'id'},
            width: a.w,
            height: a.h,
            altRows: !1,
            gridview: !0,
            multiselect: !1,
            multiboxonly: !0,
            shrinkToFit: !0,
            forceFit: !1,
            viewsortcols: [true, 'vertical', true],
            sortname: 'id',
            sortorder: 'DESC',
            pager: '#page',
            page: 1,
            rowNum: 100,
            rowList: [100, 200, 500],
            viewrecords: !0,
            loadComplete: function(data) {
                if (data && 200 == data.status) {
                    $('#totalNotClearedAmount').html(Number(data.totalNotClearedAmount).toFixed(2));
                } else {
                    $.dialog.tips(data.msg, 2);
                }
                if (goodsShowAll) {
                    $('.grid-wrap .goods-showall').html('<i class="iconfont icon-shuangshangjiantou f12 mr5"></i>收缩全部');
                    $('.grid-wrap .goods-showinfo').removeClass('icon-xiajiantou').addClass('icon-shangjiantou');
                    $('.grid-wrap .goods-info').show();
                } else {
                    $('.grid-wrap .goods-showall').html('<i class="iconfont icon-shuangxiajiantou f12 mr5"></i>展开全部');
                    $('.grid-wrap .goods-showinfo').removeClass('icon-shangjiantou').addClass('icon-xiajiantou');
                    $('.grid-wrap .goods-info').hide();
                }
            },
            loadError: function(a, b) {
                $.dialog.tips('Type: ' + b + '; Response: ' + a.status + ' ' + a.statusText, 2);
            },
            onCellSelect: function(c, d) {
                var rowData = $('#grid').jqGrid('getRowData', c);
                if (d == 2) {
                    $.dialog({
                        title: '查看客户',
                        content: 'url:/Uri/customer_manage',
                        data: {
                            oper: 'edit',
                            rowId: rowData.contactId
                        },
                        width: 1250,
                        height: 500,
                        max: !1,
                        min: !1,
                        cache: !1,
                        lock: !0,
                        disEdit: !0
                    });
                }
            },
            ondblClickRow: function(b) {
                $('#' + b).find('.ui-icon-pencil').trigger('click');
            }
        });
    },
    initEvent: function() {
        laydate.render({
            elem: "#datePicker",
            range: ['#beginDate', '#endDate'],
            done: function() {
                setTimeout(function() {
                    $('#search').trigger('click');
                }, 300);
            }
        });
        $('#search').on('click', function() {
            queryConditions.status0 = statusCombo0.getValue();
            queryConditions.status1 = statusCombo1.getValue() == -1 ? '' : statusCombo1.getText();
            queryConditions.status2 = statusCombo2.getValue();
            queryConditions.status3 = statusCombo3.getValue();
            queryConditions.status4 = statusCombo4.getValue();
            queryConditions.status5 = statusCombo5.getValue();
            queryConditions.beginDate = $('#beginDate').val();
            queryConditions.endDate = $('#endDate').val();
            queryConditions.minAmount = $('#minAmount').val();
            queryConditions.maxAmount = $('#maxAmount').val();
            queryConditions.skey = $.trim($('#skey').val()).split(/[ ,，]+/).toString();
            $('#grid').jqGrid('setGridParam', {
                postData: queryConditions,
                page: 1
            }).trigger('reloadGrid');
        });
        $('#refresh').on('click', function() {
            window.location.reload();
        });
        $('#add').on('click', function(a) {
            a.preventDefault();
            if (Business.verifyRight('SC_ADD')) {
                var newTabId = (new Date).format("yyyyMMddhhmmss");
                parent.tab.addTabItem({
                    tabid: 'sales-salesContract' + newTabId,
                    text: '<i class="iconfont icon-danye f14 mr5"></i>特殊销售单（新建）',
                    url: '/Uri/salesContract_manage?tabId=sales-salesContract' + newTabId
                });
            }
        });
        $('.grid-wrap').on('click', '.goods-showinfo', function(a) {
            if ($(this).hasClass('icon-xiajiantou')) {
                $(this).parent().next().show();
                $(this).removeClass('icon-xiajiantou').addClass('icon-shangjiantou');
            } else {
                $(this).parent().next().hide();
                $(this).removeClass('icon-shangjiantou').addClass('icon-xiajiantou');
            }
        });
        $('.grid-wrap').on('click', '.ui-icon-pencil', function(a) {
            a.preventDefault();
            if (Business.verifyRight('SC_QUERY')) {
                var id = $(this).parent().data('id');
                id && parent.tab.addTabItem({
                    tabid: 'sales-salesContract' + id,
                    text: '<i class="iconfont icon-danye f14 mr5"></i>特殊销售单',
                    url: '/Uri/salesContract_manage?id=' + id
                });
            }
        });
        $('.grid-wrap').on('click', '.ui-icon-trash', function(a) {
            a.preventDefault();
            if (Business.verifyRight('SC_DELETE')) {
                var id = $(this).parent().data('id');
                id && $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                    Public.ajaxPost('/Bill/InvSc/delete', {
                        id: id
                    }, function(res) {
                        if (res && 200 == res.status) {
                            $.dialog.tips('删除成功！');
                            $('#grid').jqGrid('delRowData', id);
                            parent.frames['sales-salesOrder'] && parent.frames['sales-salesOrder'].$('#search').trigger('click');
                            parent.frames['bid-bidQuote'] && parent.frames['bid-bidQuote'].$('#search').trigger('click');
                            parent.frames['sales-salesContract' + id] && parent.tab.removeTabItem('sales-salesContract' + id);
                        } else {
                            $.dialog.tips('删除失败！' + res.msg, 2);
                        }
                    });
                });
            }
        });
        $('.grid-wrap').on('click', '.check-btn', function(a) {
            var that = $(this);
            if (Business.verifyRight('SC_CHECK')) {
                var id = $(this).data('id');
                var checkId = Number($(this).data('checkid')) > 0 ? 0 : defaultPage.SYSTEM.uId;
                var checkName = Number($(this).data('checkid')) > 0 ? "" : defaultPage.SYSTEM.uName;
                var rowData = $('#grid').jqGrid('getRowData', id);
                id && Public.ajaxPost('/Bill/InvSc/check', {
                    id: id,
                    checkId: checkId,
                    checkName: checkName,
                    billNo: rowData.billNo,
                    transType: rowData.transType
                }, function(res) {
                    if (res && 200 == res.status) {
                        $.dialog.tips((checkId > 0 ? '审核' : '反审核') + '成功！');
                        that.parent().next().children('.moneyStatus1-btn').data('checkid', checkId);
                        that.parent().next().next().children('.goodsStatus1-btn').data('checkid', checkId);
                        that.parent().next().next().next().children('.moneyStatus2-btn').data('checkid', checkId);
                        that.parent().next().next().next().next().children('.goodsStatus2-btn').data('checkid', checkId);
                        $('#grid').jqGrid('setCell', id, 'checkId', checkId);
                        parent.frames['sales-salesContract' + id] && parent.frames['sales-salesContract' + id].location.reload();
                    } else {
                        $.dialog.tips((checkId > 0 ? '审核' : '反审核') + '失败！' + res.msg, 2);
                    }
                });
            }
        });
        $('.grid-wrap').on('click', '.moneyStatus1-btn', function(a) {
            a.preventDefault();
            if (Business.verifyRight('RECEIPT_QUERY')) {
                var id = $(this).data('id');
                var checkId = $(this).data('checkid');
                var srcmoneyid1 = $(this).data('srcmoneyid1');
                var rowData = $('#grid').jqGrid('getRowData', id);
                if (checkId == 0) {
                    $.dialog.alert('单据未审核，请立即审核！');
                } else {
                    if (srcmoneyid1 > 0) {
                        parent.tab.addTabItem({
                            tabid: 'money-receipt' + srcmoneyid1,
                            text: '<i class="iconfont icon-danye f14 mr5"></i>销售收款单',
                            url: '/Uri/receipt_manage?id=' + srcmoneyid1
                        });
                    } else {
                        var companyAccount = {id: 3, name: '优芯对公账户'};
                        if (rowData.sName) {
                            Public.ajaxPost('/Base/CompanyAccount/queryByCompanyName', {
                                async: !1,
                                company: rowData.sName
                            }, function(res) {
                                if (200 == res.status) {
                                    companyAccount = res.data;
                                }
                            });
                        }
                        if (id && rowData) {
                            var newTabId = (new Date).format("yyyyMMddhhmmss");
                            parent.tab.addTabItem({
                                tabid: 'money-receipt' + newTabId,
                                text: '<i class="iconfont icon-danye f14 mr5"></i>销售收款单（新建）',
                                url: '/Uri/receipt_manage?turn&tabId=money-receipt' + newTabId,
                                data: {
                                    id: id,
                                    no: rowData.billNo,
                                    transType: rowData.transType,
                                    amount: rowData.amount,
                                    contactId: rowData.contactId,
                                    contactName: rowData.contactName,
                                    typeId: 8,
                                    typeName: '销售款',
                                    accId: companyAccount.id,
                                    accName: companyAccount.name,
                                    wayId: 11,
                                    wayName: '银行转账',
                                    uId: defaultPage.SYSTEM.uId,
                                    uName: defaultPage.SYSTEM.uName
                                }
                            });
                        }
                    }
                }
            }
        });
        $('.grid-wrap').on('click', '.moneyStatus2-btn', function(a) {
            a.preventDefault();
            if (Business.verifyRight('PAYMENT_QUERY')) {
                var id = $(this).data('id');
                var checkId = $(this).data('checkid');
                var srcMoneyId2 = $(this).data('srcmoneyid2');
                var rowData = $('#grid').jqGrid('getRowData', id);
                if (checkId == 0) {
                    $.dialog.alert('单据未审核，请立即审核！');
                } else {
                    if (srcMoneyId2 > 0) {
                        parent.tab.addTabItem({
                            tabid: 'money-payment' + srcMoneyId2,
                            text: '<i class="iconfont icon-danye f14 mr5"></i>销售退款单',
                            url: '/Uri/payment_manage?id=' + srcMoneyId2
                        });
                    } else {
                        var companyAccount = {id: 3, name: '优芯对公账户'};
                        if (rowData.sName) {
                            Public.ajaxPost('/Base/CompanyAccount/queryByCompanyName', {
                                async: !1,
                                company: rowData.sName
                            }, function(res) {
                                if (200 == res.status) {
                                    companyAccount = res.data;
                                }
                            });
                        }
                        if (id && rowData) {
                            var newTabId = (new Date).format("yyyyMMddhhmmss");
                            parent.tab.addTabItem({
                                tabid: 'money-payment' + newTabId,
                                text: '<i class="iconfont icon-danye f14 mr5"></i>销售退款单（新建）',
                                url: '/Uri/payment_manage?turn&tabId=money-payment' + newTabId,
                                data: {
                                    id: id,
                                    no: rowData.billNo,
                                    transType: rowData.transType,
                                    amount: rowData.amount,
                                    contactId: rowData.contactId,
                                    contactName: rowData.contactName,
                                    typeId: 284,
                                    typeName: '销售款退款',
                                    accId: companyAccount.id,
                                    accName: companyAccount.name,
                                    wayId: 11,
                                    wayName: '银行转账',
                                    uId: defaultPage.SYSTEM.uId,
                                    uName: defaultPage.SYSTEM.uName
                                }
                            });
                        }
                    }
                }
            }
        });
        $('.grid-wrap').on('click', '.goodsStatus1-btn', function(a) {
            a.stopPropagation();
            a.preventDefault();
            if (Business.verifyRight('SC_UPDATE')) {
                var id = $(this).data('id');
                var checkId = $(this).data('checkid');
                var goodsStatus1 = Number($(this).data('goodsstatus1')) > 0 ? 0 : 1;
                var rowData = $('#grid').jqGrid('getRowData', id);
                if (checkId == 0) {
                    $.dialog.alert('单据未审核，请立即审核！');
                } else {
                    id && rowData && $.dialog.confirm('1、务必真实交货后，再标记为已交货，切勿随意标记！<br>2、标记为已交货后，除非特殊情况下，切勿随意取消！<br>3、在每月发工资前，自查上月是否有点了交货又取消的单子！<br>4、请重视并谨慎进行交货标记，否则将影响部分业绩统计！<br><br><div class=" f16 fb tc">确定标记为' + (goodsStatus1 == 1 ? '已交货' : '未交货') + '？</div>', function () {
                        Public.ajaxPost('/Bill/InvSc/upGoodsStatus1', {
                            id: id,
                            goodsStatus1: goodsStatus1,
                            billNo: rowData.billNo,
                            transType: rowData.transType
                        }, function(res) {
                            if (res && 200 == res.status) {
                                $.dialog.tips('标记成功！');
                                $('#grid').jqGrid('setCell', id, 'goodsStatus1', goodsStatus1);
                                parent.frames['sales-salesContract' + id] && parent.frames['sales-salesContract' + id].location.reload();
                                parent.frames['index'] && parent.frames['index'].$('#timelimited-refresh-btn').trigger('click');
                            } else {
                                $.dialog.tips('标记失败！' + res.msg, 2);
                            }
                        });
                    });
                }
            }
        });
        $('.grid-wrap').on('click', '.goodsStatus2-btn', function(a) {
            a.stopPropagation();
            a.preventDefault();
            if (Business.verifyRight('SC_UPDATE')) {
                var id = $(this).data('id');
                var checkId = $(this).data('checkid');
                var goodsStatus2 = Number($(this).data('goodsstatus2')) > 0 ? 0 : 1;
                var rowData = $('#grid').jqGrid('getRowData', id);
                if (checkId == 0) {
                    $.dialog.alert('单据未审核，请立即审核！');
                } else {
                    id && rowData && Public.ajaxPost('/Bill/InvSc/upGoodsStatus2', {
                        id: id,
                        goodsStatus2: goodsStatus2,
                        billNo: rowData.billNo,
                        transType: rowData.transType
                    }, function(res) {
                        if (res && 200 == res.status) {
                            $.dialog.tips('标记成功！');
                            $('#grid').jqGrid('setCell', id, 'goodsStatus2', goodsStatus2);
                            parent.frames['sales-salesContract' + id] && parent.frames['sales-salesContract' + id].location.reload();
                        } else {
                            $.dialog.tips('标记失败！' + res.msg, 2);
                        }
                    });
                }
            }
        });
        $('.grid-wrap').on('click', '.invoice-btn', function(a) {
            a.preventDefault();
            var id = $(this).data('id');
            var rowData = $('#grid').jqGrid('getRowData', id);
            if (Business.verifyRight('INVOICE_QUERY')) {
                var data = {
                    transType: rowData.transType,
                    billId: rowData.id,
                    billNo: rowData.billNo,
                    otherSide: rowData.contactName,
                    us: parseInt(rowData.sId),
                    callback: THISPAGE.callback
                };
                $.dialog({
                    title: '发票管理',
                    content: 'url:/Uri/invoice_bill_manage',
                    data: data,
                    width: 1250,
                    height: 600,
                    min: !1,
                    max: !1,
                    cache: !1,
                    lock: !0
                });
            }
        });
        $('.goods-showall').on('click', function() {
            if (goodsShowAll) {
                goodsShowAll = !1;
                $('.grid-wrap .goods-showall').html('<i class="iconfont icon-shuangxiajiantou f12 mr5"></i>展开全部');
                $('.grid-wrap .goods-showinfo').removeClass('icon-shangjiantou').addClass('icon-xiajiantou');
                $('.grid-wrap .goods-info').hide();
            } else {
                goodsShowAll = !0;
                $('.grid-wrap .goods-showall').html('<i class="iconfont icon-shuangshangjiantou f12 mr5"></i>收缩全部');
                $('.grid-wrap .goods-showinfo').removeClass('icon-xiajiantou').addClass('icon-shangjiantou');
                $('.grid-wrap .goods-info').show();
            }
        });
        $(window).on('keydown', function(a) {
            var curKey = a.which;
            if (curKey == 13) {
                $('#beginDate, #endDate').blur();
                $('#search').trigger('click');
            }
        });
        $(window).on('resize', function() {
            Public.resizeGrid();
        });
    },
    callback: function() {
        $('#search').trigger('click');
    }
};
$(function() {
    THISPAGE.init();
});
