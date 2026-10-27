var urlParam = Public.urlParam();
var defaultPage = Public.getDefaultPage();
var queryConditions = {
    status1: urlParam.uId || -1,
    status2: -1,
    status3: -1,
    status4: -1,
    status5: -1,
    status6: -1,
    status7: -1,
    status8: -1,
    status9: -1,
    beginDate: '',
    endDate: urlParam.endDate || '',
    minAmount: '',
    maxAmount: '',
    skey: urlParam.skey || '',
    advanced: 0,
    unDone: urlParam.unDone || 0
};
var statusCombo1 = $('#combo1').combo({
    data: '/Base/User/queryAll?normal=yes',
    ajaxOptions: {
        formatData: function(a) {
            return a.data.rows.unshift({
                id: -1,
                name: '所有制单人'
            }), a.data.rows;
        }
    },
    value: 'id',
    text: 'name',
    defaultSelected: urlParam.uId ? ['id', parseInt(urlParam.uId)] : 0,
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
        {id: -1, name: '所有类别'},
        {id: 101101, name: '采购'},
        {id: 101102, name: '直发'}
    ],
    value: 'id',
    text: 'name',
    defaultSelected: 0,
    width: 78,
    callback: {
        onListClick: function() {
            $('#search').trigger('click');
        }
    }
}).getCombo();
var statusCombo3 = $('#combo3').combo({
    data: [
        {id: -1, name: '所有审核'},
        {id: 0, name: '未审核'},
        {id: 1, name: '已审核'},
        {id: 2, name: '已作废'}
    ],
    value: 'id',
    text: 'name',
    defaultSelected: 0,
    width: 78,
    callback: {
        onListClick: function() {
            $('#search').trigger('click');
        }
    }
}).getCombo();
var statusCombo4 = $('#combo4').combo({
    data: [
        {id: -1, name: '所有付款'},
        {id: 0, name: '未付'},
        {id: 1, name: '部分'},
        {id: 2, name: '付完'}
    ],
    value: 'id',
    text: 'name',
    defaultSelected: 0,
    width: 78,
    callback: {
        onListClick: function() {
            $('#search').trigger('click');
        }
    }
}).getCombo();
var statusCombo5 = $('#combo5').combo({
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
    callback: {
        onListClick: function() {
            $('#search').trigger('click');
        }
    }
}).getCombo();
var statusCombo6 = $('#combo6').combo({
    data: [
        {id: -1, name: '所有收货'},
        {id: 0, name: '未收'},
        {id: 1, name: '部分'},
        {id: 2, name: '收完'}
    ],
    value: 'id',
    text: 'name',
    width: 78,
    defaultSelected: 0,
    cache: !1,
    editable: !1,
    trigger: !0,
    emptyOptions: !1,
    callback: {
        onListClick: function() {
            $('#search').trigger('click');
        }
    },
    queryDelay: 0
}).getCombo();
var statusCombo7 = $('#combo7').combo({
    data: [
        {id: -1, name: '所有退货'},
        {id: 0, name: '暂无'},
        {id: 1, name: '部分'},
        {id: 2, name: '退完'}
    ],
    value: 'id',
    text: 'name',
    defaultSelected: 0,
    width: 78,
    callback: {
        onListClick: function() {
            $('#search').trigger('click');
        }
    }
}).getCombo();
var statusCombo8 = $('#combo8').combo({
    data: [
        {id: -1, name: '所有收票'},
        {id: 0, name: '无收票'},
        {id: 1, name: '有收票'}
    ],
    value: 'id',
    text: 'name',
    defaultSelected: 0,
    width: 78,
    callback: {
        onListClick: function() {
            $('#search').trigger('click');
        }
    }
}).getCombo();
var statusCombo9 = $('#combo9').combo({
    data: [
        {id: -1, name: '匹配任意信息'},
        {id: 0, name: '匹配子产品'},
        {id: 1, name: '匹配子产品编码'}
    ],
    value: 'id',
    text: 'name',
    defaultSelected: 0,
    placeholder: '请选择关键词模式',
    width: 102,
    callback: {
        onListClick: function() {
            $('#search').trigger('click');
        }
    }
}).getCombo();
var goodsShowAll = !1;
var THISPAGE = {
    init: function() {
        THISPAGE.initDom();
        THISPAGE.initGrid();
        THISPAGE.initEvent();
    },
    initDom: function() {
        $('#beginDate').val(defaultPage.SYSTEM.beginDate);
        $('#endDate').val(urlParam.endDate || defaultPage.SYSTEM.endDate);
        if (queryConditions.skey) {
            $('#skey').val(queryConditions.skey);
        }
    },
    initGrid: function() {
        var a = Public.setGrid();
        $('#grid').jqGrid({
            url: '/Bill/InvPo/list',
            datatype: 'json',
            postData: queryConditions,
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
                label: '商家',
                width: 70,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis hover'
            }, {
                name: 'uName',
                label: '制单人',
                width: 54,
                fixed: !0,
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
                formatter: function(a, b, c) {
                    var d = (0 < a.length ? a.length : 0);
                    var e = '';
                    var priceFloatUp = !1;
                    var priceFloatDown = !1;
                    var priceFloatNone = !1;
                    if (0 < d) {
                        $.each(a, function(i, v) {
                            0 < Number(v['float']) && (priceFloatUp = !0);
                            0 > Number(v['float']) && (priceFloatDown = !0);
                            0 == Number(v['float']) && (priceFloatNone = !0);
                            var goodsText =
                                (v['no'] ? v['no'] + ' ' : '') +
                                v['name'] +
                                (v['spec'] ? ' ' + v['spec'] : '') +
                                (v['sku'] ? ' ' + v['sku'] : '') +
                                (v['brand'] ? ' ' + v['brand'] : '') +
                                (v['qty'] ? ' × ' + v['qty'] : '') +
                                (v['unit'] ? v['unit'] : '') +
                                (v['price'] ? ' × ' + v['price'] + '元' : '') +
                                (v['discount'] ? ' - ' + v['discount'] + '元' : '') +
                                (v['amount'] ? ' = ' + v['amount'] + '元' : '');
                            e += ('<div class="goods-line es" title="' + goodsText + '">' +
                                (v['float'] ? '<div class="goods-float-icon ml2"><i class="' + (0 < Number(v['float']) ? 'red iconfont icon-yuanshangjiantou" title="价格上涨 ' + Number(v['float']) : (0 > Number(v['float']) ? 'green iconfont icon-yuanxiajiantou" title="价格下降 ' + Number(v['float']) : 'blue iconfont icon-yuanyoujiantou" title="价格持平或初次采购')) + '"></i></div>' : '') + goodsText + '</div>'
                            );
                        });
                    }
                    return (
                        '<div class="goods-total">' +
                        '<span class="goods-num-1">' +
                        d +
                        ' 种</span><span class="goods-num-2">' +
                        c.totalQty +
                        ' 件</span><span class="goods-num-3">' +
                        '<div class="goods-float">' +
                        (priceFloatUp ? '<span class="goods-float-icon" title="有产品价格上涨"><i class="red iconfont icon-yuanshangjiantou"></i></span>' : '') +
                        (priceFloatDown ? '<span class="goods-float-icon" title="有产品价格下降"><i class="green iconfont icon-yuanxiajiantou"></i></span>' : '') +
                        (priceFloatNone ? '<span class="goods-float-icon" title="有产品价格持平或初次采购"><i class="blue iconfont icon-yuanyoujiantou"></i></span>' : '') +
                        '</div>' +
                        c.amount +
                        ' 元' +
                        '</span>' +
                        '<span class="goods-showinfo iconfont icon-xiajiantou"></span></div><div class="goods-info">' +
                        e +
                        '</div>'
                    );
                }
            }, {
                name: 'checkId',
                label: '审核',
                width: 42,
                fixed: !0,
                sortable: !0,
                align: 'center',
                formatter: function(a, b, c) {
                    if (c.isCancel == 1) {
                        return '<span class="db h28 lh28 b-r-3 hover-gray" title="已作废">作废</span>';
                    } else if (c.isDraft == 1) {
                        return '<span class="db h28 lh28 b-r-3 hover-gray" title="目前为草稿 无法进行更多操作">草稿</span>';
                    }
                    var d = a == 0 ? '未审' : '已审';
                    var e = a == 0 ? '点击审核' : '点击反审核';
                    var f = a == 0 ? 'hover-red' : 'hover-green';
                    return '<span class="check-btn db h28 lh28 b-r-3 ' + f + '" title="' + e + '" data-checkid="' + a + '" data-id="' + c.id + '">' + d + '</span>';
                }
            }, {
                name: 'moneyStatus1',
                label: '付款',
                width: 42,
                fixed: !0,
                sortable: !0,
                align: 'center',
                formatter: function(a, b, c) {
                    if (c.isDraft == 1) {
                        return '<span class="db h28 lh28 b-r-3 hover-gray" title="目前为草稿 无法进行更多操作">草稿</span>';
                    }
                    var d = a == 0 ? '未付' : (a == 1 ? '部分' : '付完');
                    var f = a == 0 ? 'hover-red' : (a == 1 ? 'hover-yellow' : 'hover-green');
                    var g = c.srcMoneyId1 == 0 ? '点击付款' : '点击打开采购付款单' + '\r\n' + c.srcMoneyNo1;
                    return '<span class="moneyStatus1-btn db h28 lh28 b-r-3 ' + f + '" title="' + g + '" data-id="' + c.id + '" data-checkid="' + c.checkId + '" data-srcmoneyid1="' + c.srcMoneyId1 + '">' + d + '</span>';
                }
            }, {
                name: 'goodsStatus1',
                label: '收货',
                width: 42,
                fixed: !0,
                sortable: !0,
                align: 'center',
                formatter: function(a, b, c) {
                    if (a == 3) {
                        return '<span class="db h28 lh28 b-r-3 hover-gray" title="直发订单无需收货">直发</span>';
                    } else if (c.isCancel == 1 && a == 0) {
                        return '<span class="db h28 lh28 b-r-3 hover-gray" title="已作废">作废</span>';
                    } else if (c.isDraft == 1) {
                        return '<span class="db h28 lh28 b-r-3 hover-gray" title="目前为草稿 无法进行更多操作">草稿</span>';
                    }
                    var d = a == 0 ? '未收' : (a == 1 ? '部分' : (a == 2 ? '收完' : ''));
                    var f = a == 0 ? 'hover-red' : (a == 1 ? 'hover-yellow ' : (a == 2 ? 'hover-green' : ''));
                    var g = c.srcInvId1 == 0 ? '点击收货' : ('点击打开采购收货单' + '\r\n' + c.srcInvNo1);
                    return '<span class="goodsStatus1-btn db h28 lh28 b-r-3 ' + f + '" title="' + g + '" data-id="' + c.id + '" data-checkid="' + c.checkId + '" data-srcinvid1="' + c.srcInvId1 + '" data-goodsstatus1="' + c.goodsStatus1 + '">' + d + '</span>';
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
                    var g = c.srcMoneyId2 == 0 ? '点击退款' : '点击打开采购退款单' + '\r\n' + c.srcMoneyNo2;
                    return '<span class="moneyStatus2-btn db h28 lh28 b-r-3 ' + f + '" title="' + g + '" data-id="' + c.id + '" data-checkid="' + c.checkId + '" data-srcmoneyid2="' + c.srcMoneyId2 + '">' + d + '</span>';
                }
            }, {
                name: 'goodsStatus2',
                label: '退货',
                width: 42,
                fixed: !0,
                sortable: !0,
                align: 'center',
                formatter: function(a, b, c) {
                    if (c.isCancel == 1 && a == 0 && c.goodsStatus1 == 0) {
                        return '<span class="db h28 lh28 b-r-3 hover-gray" title="已作废">作废</span>';
                    } else if (c.isDraft == 1) {
                        return '<span class="db h28 lh28 b-r-3 hover-gray" title="目前为草稿 无法进行更多操作">草稿</span>';
                    }
                    var d = a == 1 ? '部分' : (a == 2 ? '退完' : '暂无');
                    var f = a == 1 ? 'hover-yellow' : (a == 2 ? 'hover-green' : 'hover-white');
                    var g = c.srcInvId2 == 0 ? '点击退货' : '点击打开采购退货单' + '\r\n' + c.srcInvNo2;
                    return '<span class="goodsStatus2-btn db h28 lh28 b-r-3 ' + f + '" title="' + g + '" data-id="' + c.id + '" data-checkid="' + c.checkId + '" data-srcinvid1="' + c.srcInvId1 + '" data-srcinvid2="' + c.srcInvId2 + '" data-goodsstatus1="' + c.goodsStatus1 + '" data-goodsstatus2="' + c.goodsStatus2 + '">' + d + '</span>';
                }
            }, {
                name: 'invoiceNum',
                label: '发票',
                width: 112,
                fixed: !0,
                sortable: !0,
                align: 'center',
                formatter: function(a, b, c) {
                    if (c.isCancel == 1) {
                        return '<span class="db fl w30 h28 lh28 hover-gray b-r-3" title="已作废">作废</span>';
                    } else if (c.isDraft == 1) {
                        return '<span class="db fl w30 h28 lh28 hover-gray b-r-3" title="目前为草稿 无法进行更多操作">草稿</span>';
                    } else {
                        var d = c.isIgnoreInvoice == 2 ? 'hover-green' : (a > 0 ? (c.invoiceAmount > c.amount ? 'hover-purple' : (c.invoiceAmount == c.amount ? 'hover-green' : 'hover-yellow')) : 'hover-red');
                        var e = a > 0 ? (c.invoiceAmount > c.amount ? '<br>已收：' + c.invoiceAmount + '元（超额）' : (c.invoiceAmount == c.amount ? '<br>已收：' + c.invoiceAmount + '元（足额）' : '<br>已收：' + c.invoiceAmount + '元（部分）')) : '';
                        var f = '<span class="ignoreReq-btn db fl ml1 w70 tc h28 lh28 hover-white b-r-3" title="点击申请白条入账" data-id="' + c.id + '">白条入账</span>';
                        if (c.isIgnoreInvoice == 2) {
                            f = '<span class="db fl ml1 w70 tc h28 lh28 hover-green b-r-3" title="管理员已同意白条入账">已同意白条</span>';
                        } else if (c.isIgnoreInvoice == 1) {
                            f = '<span class="ignoreReq-btn db fl ml1 w70 tc h28 lh28 hover-dark b-r-3" title="管理员已拒绝白条入账<br>拒绝原因：' + c.disagreeIgnoreReason + '<br>点击可再次申请白条入账" data-id="' + c.id + '">已拒绝白条</span>';
                        } else if (c.isIgnoreInvoice == 0 && c.ignoreInvoiceReason != '') {
                            f = '<span class="ignoreEdit-btn db fl ml1 w70 tc h28 lh28 hover-red b-r-3" title="您已申请过白条入账<br>申请原因：' + c.ignoreInvoiceReason + '<br>点击可重新编辑" data-id="' + c.id + '">白条待审批</span>';
                        } else if ((a > 0 && c.invoiceAmount >= c.amount) || c.isCancel == 1) {
                            f = '';
                        }
                        return '<span class="invoice-btn db fl w30 tc h28 lh28 ' + d + ' b-r-3" title="点击管理发票' + e + '" data-id="' + c.id + '">' + a + '</span>' + f;
                    }
                }
            }, {
                name: 'srcPurReqId',
                label: '来源',
                width: 42,
                fixed: !0,
                sortable: !0,
                align: 'center',
                classes: 'silver',
                formatter: function(a, b, c) {
                    return c.srcPurReqId > 0 ? ('<span class="src-btn db h28 lh28 b-r-3 hover-white" title="来自采购申请<br>点击查看申请信息" data-srcpurreqid="' + c.srcPurReqId + '">查看</span>') : '无';
                }
            }, {
                name: 'id',
                label: 'id',
                width: 0,
                hidden: !0
            }, {
                name: 'transType',
                label: '类别ID',
                hidden: !0
            }, {
                name: 'uId',
                label: '制单人Id',
                hidden: !0
            }, {
                name: 'contactId',
                label: '商家Id',
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
                name: 'amount',
                label: '金额',
                hidden: !0
            }, {
                name: 'srcMoneyId1',
                label: '付款单ID',
                hidden: !0
            }, {
                name: 'srcMoneyNo1',
                label: '付款单号',
                hidden: !0
            }, {
                name: 'srcMoneyId2',
                label: '收款单ID',
                hidden: !0
            }, {
                name: 'srcInvId1',
                label: '收货单ID',
                hidden: !0
            }, {
                name: 'srcInvId2',
                label: '退货单ID',
                hidden: !0
            }, {
                name: 'operate',
                label: '操作',
                width: 72.8,
                fixed: !0,
                formatter: Public.operFmatter
            }],
            cmTemplate: {sortable: !1, title: !1},
            jsonReader: {root: 'data.rows', records: 'data.records', total: 'data.total', repeatitems: !1, id: 'id'},
            autowidth: !0,
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
                    if (data.totalNotClearedAmount) {
                        $('#totalNotClearedAmount').html(Number(data.totalNotClearedAmount).toFixed(2));
                    } else {
                        $('#totalNotClearedAmount').parent().parent().remove();
                        Public.resizeGrid();
                    }
                    if (queryConditions.advanced > 0 && data.advanced_info) {
                        $.dialog.tips(
                            '当前筛选条件下，采购总费用：'
                            + data.advanced_info.totalAmount
                            + ' 元，采购总单数：'
                            + data.advanced_info.totalBillNum
                            + ' 单，收到发票单数：'
                            + data.advanced_info.totalInvoiceNum
                            + ' 单，发票收取率：'
                            + (Number(data.advanced_info.totalInvoiceNum) / Number(data.advanced_info.totalBillNum) * 100).toFixed(2)
                            + ' %', 2, 999
                        );
                    }
                } else {
                    $.dialog.tips(data.msg, 2);
                }
                if (goodsShowAll) {
                    $('.grid-wrap .goods-showall').html('<i class="iconfont icon-shuangshangjiantou f12 mr5"></i>收缩全部');
                    $('.grid-wrap .goods-showinfo').removeClass('icon-xiajiantou').addClass('icon-shangjiantou');
                    $('.grid-wrap .goods-float').hide();
                    $('.grid-wrap .goods-info').show();
                } else {
                    $('.grid-wrap .goods-showall').html('<i class="iconfont icon-shuangxiajiantou f12 mr5"></i>展开全部');
                    $('.grid-wrap .goods-showinfo').removeClass('icon-shangjiantou').addClass('icon-xiajiantou');
                    $('.grid-wrap .goods-float').show();
                    $('.grid-wrap .goods-info').hide();
                }
            },
            loadError: function(a, b) {
                $.dialog.tips('Type: ' + b + '; Response: ' + a.status + ' ' + a.statusText, 2);
            },
            onCellSelect: function(a, b) {
                var rowData = $('#grid').jqGrid('getRowData', a);
                if (b == 2) {
                    $.dialog({
                        title: '查看商家',
                        content: 'url:/Uri/supplier_manage',
                        data: {
                            oper: 'edit',
                            rowId: rowData.contactId
                        },
                        width: 1250,
                        height: 520,
                        max: !1,
                        min: !1,
                        cache: !1,
                        lock: !0,
                        disEdit: !0
                    });
                }
            },
            ondblClickRow: function(c) {
                $('#' + c).find('.ui-icon-pencil').trigger('click');
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
            queryConditions.status1 = statusCombo1.getValue();
            queryConditions.status2 = statusCombo2.getValue();
            queryConditions.status3 = statusCombo3.getValue();
            queryConditions.status4 = statusCombo4.getValue();
            queryConditions.status5 = statusCombo5.getValue();
            queryConditions.status6 = statusCombo6.getValue();
            queryConditions.status7 = statusCombo7.getValue();
            queryConditions.status8 = statusCombo8.getValue();
            queryConditions.status9 = statusCombo9.getValue();
            queryConditions.beginDate = $('#beginDate').val();
            queryConditions.endDate = $('#endDate').val();
            queryConditions.minAmount = $('#minAmount').val();
            queryConditions.maxAmount = $('#maxAmount').val();
            queryConditions.skey = $.trim($('#skey').val()).split(/[ ,，]+/).toString();
            queryConditions.advanced = 0;
            $('#grid').jqGrid('setGridParam', {postData: queryConditions, page: 1}).trigger('reloadGrid');
        });
        $('#search2').on('click', function() {
            queryConditions.status1 = statusCombo1.getValue();
            queryConditions.status2 = statusCombo2.getValue();
            queryConditions.status3 = statusCombo3.getValue();
            queryConditions.status4 = statusCombo4.getValue();
            queryConditions.status5 = statusCombo5.getValue();
            queryConditions.status6 = statusCombo6.getValue();
            queryConditions.status7 = statusCombo7.getValue();
            queryConditions.status8 = statusCombo8.getValue();
            queryConditions.beginDate = $('#beginDate').val();
            queryConditions.endDate = $('#endDate').val();
            queryConditions.minAmount = $('#minAmount').val();
            queryConditions.maxAmount = $('#maxAmount').val();
            queryConditions.skey = $.trim($('#skey').val()).split(/[ ,，]+/).toString();
            queryConditions.advanced = 1;
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
            if (Business.verifyRight('PO_ADD')) {
                var newTabId = (new Date).format("yyyyMMddhhmmss");
                parent.tab.addTabItem({
                    tabid: 'purchase-purchaseOrder' + newTabId,
                    text: '<i class="iconfont icon-danye f14 mr5"></i>采购订单（新建）',
                    url: '/Uri/purchaseOrder_manage?tabId=purchase-purchaseOrder' + newTabId
                });
            }
        });
        $('.grid-wrap').on('click', '.ui-icon-pencil', function(a) {
            a.preventDefault();
            var id = $(this).parent().data('id');
            id && parent.tab.addTabItem({
                tabid: 'purchase-purchaseOrder' + id,
                text: '<i class="iconfont icon-danye f14 mr5"></i>采购订单',
                url: '/Uri/purchaseOrder_manage?id=' + id
            });
        });
        $('.grid-wrap').on('click', '.ui-icon-trash', function(b) {
            b.preventDefault();
            if (Business.verifyRight('PO_DELETE')) {
                var id = $(this).parent().data('id');
                id && $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                    Public.ajaxGet('/Bill/InvPo/delete', {
                        id: id
                    }, function(res) {
                        if (res && 200 == res.status) {
                            $.dialog.tips('删除成功！');
                            $('#grid').jqGrid('delRowData', id);
                            parent.frames['purchase-purchaseOrder' + id] && parent.tab.removeTabItem('purchase-purchaseOrder' + id);
                            parent.frames['purchase-purchaseReq'] && parent.frames['purchase-purchaseReq'].$('#search').trigger('click');
                        } else {
                            $.dialog.tips('删除失败！' + res.msg, 2);
                        }
                    });
                });
            }
        });
        $('.grid-wrap').on('click', '.src-btn', function(a) {
            a.preventDefault();
            var srcPurReqId = $(this).data('srcpurreqid');
            srcPurReqId && $.dialog({
                title: '原采购申请详情',
                content: 'url:/Uri/purchaseReq_manage',
                data: {
                    oper: 'edit',
                    rowId: srcPurReqId
                },
                width: 1250,
                height: 700,
                max: !1,
                min: !1,
                cache: !1,
                lock: !0,
                disEdit: !0,
                button: [{
                    id: 'cancel',
                    name: '关闭'
                }]
            });
        });
        $('.grid-wrap').on('click', '.check-btn', function(a) {
            var that = $(this);
            if (Business.verifyRight('PO_CHECK') || Business.verifyRight('PO_UNCHECK')) {
                var id = $(this).data('id');
                var checkId = Number($(this).data('checkid')) > 0 ? 0 : defaultPage.SYSTEM.uId;
                var checkName = Number($(this).data('checkid')) > 0 ? "" : defaultPage.SYSTEM.uName;
                var rowData = $('#grid').jqGrid('getRowData', id);
                id && Public.ajaxPost('/Bill/InvPo/check', {
                    id: id,
                    checkId: checkId,
                    checkName: checkName,
                    billNo: rowData.billNo
                }, function(res) {
                    if (res && 200 == res.status) {
                        $.dialog.tips((checkId > 0 ? '审核' : '反审核') + '成功！');
                        that.parent().next().children('.moneyStatus1-btn').data('checkid', checkId);
                        that.parent().next().next().children('.goodsStatus1-btn').data('checkid', checkId);
                        that.parent().next().next().next().children('.moneyStatus2-btn').data('checkid', checkId);
                        that.parent().next().next().next().next().children('.goodsStatus2-btn').data('checkid', checkId);
                        $('#grid').jqGrid('setCell', id, 'checkId', checkId);
                        parent.frames['purchase-purchaseOrder' + id] && parent.frames['purchase-purchaseOrder' + id].location.reload();
                    } else {
                        $.dialog.tips((checkId > 0 ? '审核' : '反审核') + '失败！' + res.msg, 2);
                    }
                });
            }
        });
        $('.grid-wrap').on('click', '.moneyStatus1-btn', function(a) {
            a.preventDefault();
            if (Business.verifyRight('PAYMENT_QUERY')) {
                var id = $(this).data('id');
                var checkid = $(this).data('checkid');
                var srcmoneyid1 = $(this).data('srcmoneyid1');
                var rowData = $('#grid').jqGrid('getRowData', id);
                if (checkid == 0) {
                    $.dialog.alert('单据未审核，请立即审核！');
                } else {
                    if (srcmoneyid1 > 0) {
                        parent.tab.addTabItem({
                            tabid: 'money-payment' + srcmoneyid1,
                            text: '<i class="iconfont icon-danye f14 mr5"></i>采购付款单',
                            url: '/Uri/payment_manage?id=' + srcmoneyid1
                        });
                    } else {
                        if (id && rowData) {
                            var newTabId = (new Date).format("yyyyMMddhhmmss");
                            parent.tab.addTabItem({
                                tabid: 'money-payment' + newTabId,
                                text: '<i class="iconfont icon-danye f14 mr5"></i>采购付款单（新建）',
                                url: '/Uri/payment_manage?turn&tabId=money-payment' + newTabId,
                                data: {
                                    id: id,
                                    no: rowData.billNo,
                                    transType: rowData.transType,
                                    amount: rowData.amount,
                                    contactId: rowData.contactId,
                                    contactName: rowData.contactName,
                                    srcUs: rowData.sName,
                                    typeId: 7,
                                    typeName: '采购款',
                                    accId: 1,
                                    accName: '无主账户(户头)',
                                    wayId: 12,
                                    wayName: '支付宝',
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
            if (Business.verifyRight('RECEIPT_QUERY')) {
                var id = $(this).data('id');
                var checkid = $(this).data('checkid');
                var srcmoneyid2 = $(this).data('srcmoneyid2');
                var rowData = $('#grid').jqGrid('getRowData', id);
                if (checkid == 0) {
                    $.dialog.alert('单据未审核，请立即审核！');
                } else {
                    if (srcmoneyid2 > 0) {
                        parent.tab.addTabItem({
                            tabid: 'money-receipt' + srcmoneyid2,
                            text: '<i class="iconfont icon-danye f14 mr5"></i>采购退款单',
                            url: '/Uri/receipt_manage?id=' + srcmoneyid2
                        });
                    } else {
                        if (id && rowData) {
                            var newTabId = (new Date).format("yyyyMMddhhmmss");
                            parent.tab.addTabItem({
                                tabid: 'money-receipt' + newTabId,
                                text: '<i class="iconfont icon-danye f14 mr5"></i>采购退款单（新建）',
                                url: '/Uri/receipt_manage?turn&tabId=money-receipt' + newTabId,
                                data: {
                                    id: id,
                                    no: rowData.billNo,
                                    transType: rowData.transType,
                                    amount: rowData.amount,
                                    contactId: rowData.contactId,
                                    contactName: rowData.contactName,
                                    typeId: 6,
                                    typeName: '采购款退款',
                                    accId: 1,
                                    accName: '无主账户(户头)',
                                    wayId: 12,
                                    wayName: '支付宝',
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
            a.preventDefault();
            var id = $(this).data('id');
            var checkid = $(this).data('checkid');
            var srcinvid1 = $(this).data('srcinvid1');
            var goodsstatus1 = $(this).data('goodsstatus1');
            if (checkid == 0) {
                $.dialog.alert('单据未审核，请立即审核！');
            } else {
                if (srcinvid1 > 0) {
                    parent.tab.addTabItem({
                        tabid: 'inventory-purchase' + srcinvid1,
                        text: '<i class="iconfont icon-danye f14 mr5"></i>采购收货单',
                        url: '/Uri/purchase_manage?id=' + srcinvid1 + '&transType=102101'
                    });
                } else {
                    if (goodsstatus1 == 3) {
                        $.dialog.tips('直发订单默认已增加当前库存，无需再收货！', 2);
                    } else {
                        if (id) {
                            var newTabId = (new Date).format("yyyyMMddhhmmss");
                            parent.tab.addTabItem({
                                tabid: 'inventory-purchase' + newTabId,
                                text: '<i class="iconfont icon-danye f14 mr5"></i>采购收货单（新建）',
                                url: '/Uri/purchase_manage?turnByPurchaseOrder&tabId=inventory-purchase' + newTabId + '&transType=102101',
                                data: {
                                    id: id,
                                    transType: 102101
                                }
                            });
                        }
                    }
                }
            }
        });
        $('.grid-wrap').on('click', '.goodsStatus2-btn', function(a) {
            a.preventDefault();
            var id = $(this).data('id');
            var checkid = $(this).data('checkid');
            var srcinvid1 = $(this).data('srcinvid1');
            var goodsstatus1 = $(this).data('goodsstatus1');
            var srcinvid2 = $(this).data('srcinvid2');
            if (checkid == 0) {
                $.dialog.alert('单据未审核，请立即审核！');
            } else {
                if (srcinvid2 > 0) {
                    parent.tab.addTabItem({
                        tabid: 'inventory-purchaseBack' + srcinvid2,
                        text: '<i class="iconfont icon-danye f14 mr5"></i>采购退货单',
                        url: '/Uri/purchase_manage?id=' + srcinvid2 + '&transType=102102'
                    });
                } else {
                    if (srcinvid1 == 0 && goodsstatus1 == 0) {
                        $.dialog.tips('未收过货，不能退货！', 2);
                    } else {
                        if (id) {
                            var newTabId = (new Date).format("yyyyMMddhhmmss");
                            parent.tab.addTabItem({
                                tabid: 'inventory-purchaseBack' + newTabId,
                                text: '<i class="iconfont icon-danye f14 mr5"></i>采购退货单（新建）',
                                url: '/Uri/purchase_manage?turnByPurchaseOrder&tabId=inventory-purchaseBack' + newTabId + '&transType=102102',
                                data: {
                                    id: id,
                                    transType: 102102
                                }
                            });
                        }
                    }
                }
            }
        });
        $('.grid-wrap').on('click', '.invoice-btn', function(a) {
            a.preventDefault();
            var id = $(this).data('id');
            var rowData = $('#grid').jqGrid('getRowData', id);
            if (Business.verifyRight('INVOICE_ADD')) {
                $.dialog({
                    title: '发票管理',
                    content: 'url:/Uri/invoice_bill_manage',
                    data: {
                        transType: rowData.transType,
                        billId: rowData.id,
                        billNo: rowData.billNo,
                        otherSide: rowData.contactName,
                        us: parseInt(rowData.sId),
                        callback: THISPAGE.callback
                    },
                    width: 1250,
                    height: 600,
                    min: !1,
                    max: !1,
                    cache: !1,
                    lock: !0
                });
            }
        });
        $('.grid-wrap').on('click', '.ignoreReq-btn', function(a) {
            a.preventDefault();
            if (Business.verifyRight('PO_UPDATE')) {
                var id = $(this).data('id');
                var rowData = $('#grid').jqGrid('getRowData', id);
                if (id) {
                    if (rowData.uName == defaultPage.SYSTEM.uName) {
                        $.dialog({
                            title: '申请白条入账',
                            content: 'url:/Uri/invoice_miss_ignore',
                            data: {
                                oper: 'add',
                                billType: 'order',
                                type: 'ignoreReq',
                                id: id,
                                callback: THISPAGE.ignoreCallback
                            },
                            width: 400,
                            height: 260,
                            max: !1,
                            min: !1,
                            cache: !1,
                            lock: !0
                        });
                    } else {
                        $.dialog.tips('不能申请别人的单据白条入账！', 2);
                    }
                }
            }
        });
        $('.grid-wrap').on('click', '.ignoreEdit-btn', function(a) {
            a.preventDefault();
            if (Business.verifyRight('PO_UPDATE')) {
                var id = $(this).data('id');
                var rowData = $('#grid').jqGrid('getRowData', id);
                if (id) {
                    if (rowData.uName == defaultPage.SYSTEM.uName) {
                        $.dialog({
                            title: '编辑白条入账',
                            content: 'url:/Uri/invoice_miss_ignore',
                            data: {
                                oper: 'edit',
                                billType: 'order',
                                type: 'ignoreReq',
                                id: id,
                                callback: THISPAGE.ignoreCallback
                            },
                            width: 400,
                            height: 260,
                            max: !1,
                            min: !1,
                            cache: !1,
                            lock: !0
                        });
                    } else {
                        $.dialog.tips('不能编辑别人的单据白条入账！', 2);
                    }
                }
            }
        });
        $('.grid-wrap').on('click', '.goods-showinfo', function() {
            if ($(this).hasClass('icon-xiajiantou')) {
                $(this).prev().children('.goods-float').hide();
                $(this).parent().next().show();
                $(this).removeClass('icon-xiajiantou').addClass('icon-shangjiantou');
            } else {
                $(this).prev().children('.goods-float').show();
                $(this).parent().next().hide();
                $(this).removeClass('icon-shangjiantou').addClass('icon-xiajiantou');
            }
        });
        $('.goods-showall').on('click', function() {
            if (goodsShowAll) {
                goodsShowAll = !1;
                $('.grid-wrap .goods-showall').html('<i class="iconfont icon-shuangxiajiantou f12 mr5"></i>展开全部');
                $('.grid-wrap .goods-showinfo').removeClass('icon-shangjiantou').addClass('icon-xiajiantou');
                $('.grid-wrap .goods-float').show();
                $('.grid-wrap .goods-info').hide();
            } else {
                goodsShowAll = !0;
                $('.grid-wrap .goods-showall').html('<i class="iconfont icon-shuangshangjiantou f12 mr5"></i>收缩全部');
                $('.grid-wrap .goods-showinfo').removeClass('icon-xiajiantou').addClass('icon-shangjiantou');
                $('.grid-wrap .goods-float').hide();
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
    },
    ignoreCallback: function(a, b, c, d) {
        $('#grid').jqGrid('setGridParam', {postData: queryConditions}).trigger('reloadGrid');
        c && c.api.close();
    }
};
$(function() {
    THISPAGE.init();
});
