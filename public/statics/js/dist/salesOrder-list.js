var urlParam = Public.urlParam();
var defaultPage = Public.getDefaultPage();
var queryConditions = {
    status1: '',
    status2: urlParam.uId || -1,
    status3: -1,
    status4: -1,
    status5: -1,
    status6: -1,
    status7: -1,
    status8: '',
    beginDate: '',
    endDate: urlParam.endDate || '',
    minAmount: '',
    maxAmount: '',
    skey: '',
    unClear: urlParam.unClear || 0
};
var statusCombo1 = $('#combo1').combo({
    data: '/Base/Billfrom/queryAll?isDisable=0',
    ajaxOptions: {
        formatData: function(a) {
            return a.data.rows.unshift({
                id: -1,
                name: '所有渠道'
            }), a.data.rows;
        }
    },
    value: 'id',
    text: 'name',
    defaultSelected: 0,
    width: 78,
    minListWidth: 200,
    listHeight: 500,
    callback: {
        onListClick: function() {
            $('#search').trigger('click');
        }
    }
}).getCombo();
var statusCombo2 = $('#combo2').combo({
    data: '/Base/User/queryAll?department=销售部',
    ajaxOptions: {
        formatData: function(a) {
            return a.data.rows.unshift({
                id: -1,
                name: '所有制单人'
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
    defaultSelected: urlParam.uId ? ['id', parseInt(urlParam.uId)] : 0,
    width: 90,
    listHeight: 500,
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
    listHeight: 500,
    callback: {
        onListClick: function() {
            $('#search').trigger('click');
        }
    }
}).getCombo();
var statusCombo4 = $('#combo4').combo({
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
    listHeight: 500,
    callback: {
        onListClick: function() {
            $('#search').trigger('click');
        }
    }
}).getCombo();
var statusCombo6 = $('#combo6').combo({
    data: [
        {id: -1, name: '所有发货'},
        {id: 0, name: '未发'},
        {id: 1, name: '部分'},
        {id: 2, name: '发完'}
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
    listHeight: 500,
    callback: {
        onListClick: function() {
            $('#search').trigger('click');
        }
    }
}).getCombo();
var statusCombo8 = $('#combo8').combo({
    data: [
        {id: -1, name: '所有销往'},
        {id: 0, name: '北京'},
        {id: 1, name: '天津'},
        {id: 2, name: '河北'},
        {id: 3, name: '山西'},
        {id: 4, name: '内蒙'},
        {id: 5, name: '辽宁'},
        {id: 6, name: '吉林'},
        {id: 7, name: '黑龙江'},
        {id: 8, name: '上海'},
        {id: 9, name: '江苏'},
        {id: 10, name: '浙江'},
        {id: 11, name: '安徽'},
        {id: 12, name: '福建'},
        {id: 13, name: '江西'},
        {id: 14, name: '山东'},
        {id: 15, name: '河南'},
        {id: 16, name: '湖北'},
        {id: 17, name: '湖南'},
        {id: 18, name: '广东'},
        {id: 19, name: '广西'},
        {id: 20, name: '海南'},
        {id: 21, name: '重庆'},
        {id: 22, name: '四川'},
        {id: 23, name: '贵州'},
        {id: 24, name: '云南'},
        {id: 25, name: '西藏'},
        {id: 26, name: '陕西'},
        {id: 27, name: '甘肃'},
        {id: 28, name: '青海'},
        {id: 29, name: '宁夏'},
        {id: 30, name: '新疆'},
        {id: 31, name: '台湾'},
        {id: 32, name: '香港'},
        {id: 33, name: '澳门'},
        {id: 34, name: '国外'}
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
    },
    initGrid: function() {
        var a = Public.setGrid();
        $('#grid').jqGrid({
            url: '/Bill/InvSo/list',
            datatype: 'json',
            postData: queryConditions,
            colModel: [{
                name: 'billNo',
                label: '<span class="ml10">单据编号</span>',
                width: 164,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ti10'
            }, {
                name: 'billDate',
                label: '单据日期',
                width: 72,
                fixed: !0,
                sortable: !0
            }, {
                name: 'contactName',
                label: '客户',
                width: 70,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis hover'
            }, {
                name: 'billFrom',
                label: '销售渠道',
                width: 66,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray'
            }, {
                name: 'address',
                label: '销往',
                width: 66,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray'
            }, {
                name: 'expressCompanyName',
                label: '快递公司',
                width: 66,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray'
            }, {
                name: 'expressNo',
                label: '快递单号',
                width: 136,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray'
            }, {
                name: 'uName',
                label: '制单人',
                width: 54,
                fixed: !0,
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
                    if (0 < d) {
                        $.each(a, function(i, v) {
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
                            e += ('<div class="goods-line es" title="' + goodsText + '">' + goodsText + '</div>');
                        });
                    }
                    return (
                        '<div class="goods-total"><span class="goods-num-1">' +
                        d +
                        ' 种</span><span class="goods-num-2">' +
                        c.totalQty +
                        ' 件</span><span class="goods-num-3">' +
                        c.amount +
                        ' 元</span><span class="goods-showinfo iconfont icon-xiajiantou"></span></div><div class="goods-info">' +
                        e +
                        '</div>'
                    );
                }
            }, {
                name: 'description',
                label: '备注',
                width: 100,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray'
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
                    var g = c.srcMoneyId1 == 0 ? '点击收款' : '点击打开销售收款单' + '\r\n' + c.srcMoneyNo1;
                    return '<span class="moneyStatus1-btn db h28 lh28 b-r-3 ' + f + '" title="' + g + '" data-id="' + c.id + '" data-checkid="' + c.checkId + '" data-srcmoneyid1="' + c.srcMoneyId1 + '">' + d + '</span>';
                }
            }, {
                name: 'goodsStatus1',
                label: '发货',
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
                    var d = a == 0 ? '未发' : (a == 1 ? '部分' : (a == 2 ? '发完' : ''));
                    var f = a == 0 ? 'hover-red' : (a == 1 ? 'hover-yellow' : (a == 2 ? 'hover-green' : ''));
                    var g = c.srcInvId1 == 0 ? '点击发货' : '点击打开销售发货单' + '\r\n' + c.srcInvNo1;
                    if (c.srcOcId && c.srcOcNo) {
                        return '<span class="db h28 lh28 hover-gray b-r-3" title="已特殊另发">无需</span>';
                    } else {
                        return '<span class="goodsStatus1-btn db h28 lh28 b-r-3 ' + f + '" title="' + g + '" data-id="' + c.id + '" data-checkid="' + c.checkId + '" data-srcinvid1="' + c.srcInvId1 + '" data-goodsstatus1="' + c.goodsStatus1 + '">' + d + '</span>';
                    }
                }
            }, {
                name: 'srcOcId',
                label: '特殊另发',
                width: 66,
                fixed: !0,
                sortable: !0,
                align: 'center',
                classes: 'gray',
                formatter: function(a, b, c) {
                    if (c.isCancel == 1) {
                        return '<span class="db h28 lh28 b-r-3 hover-gray" title="已作废">作废</span>';
                    } else if (c.isDraft == 1) {
                        return '<span class="db h28 lh28 b-r-3 hover-gray" title="目前为草稿 无法进行更多操作">草稿</span>';
                    }
                    if (c.srcOcId && c.srcOcNo) {
                        return '<span class="redoanother-bill-btn db h28 lh28 hover-gray b-r-3" title="点击打开特殊销售单\r\n' + c.srcOcNo + '" data-srcocid="' + a + '">已另发</span>';
                    } else if (c.uId == 1 || c.billFrom == '官网') {
                        return '<span class="redoanother-btn db h28 lh28 hover-white b-r-3" title="点击新建特殊销售单另外发货" data-id="' + c.id + '" data-goodsstatus1="' + c.goodsStatus1 + '" data-goodsstatus2="' + c.goodsStatus2 + '">特殊另发</span>';
                    } else {
                        return '手工录入';
                    }
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
                    var g = c.srcMoneyId2 == 0 ? '点击退款' : '点击打开销售退款单' + '\r\n' + c.srcMoneyNo2;
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
                    if (c.isCancel == 1) {
                        return '<span class="db h28 lh28 b-r-3 hover-gray" title="已作废">作废</span>';
                    } else if (c.isDraft == 1) {
                        return '<span class="db h28 lh28 b-r-3 hover-gray" title="目前为草稿 无法进行更多操作">草稿</span>';
                    }
                    var d = a == 1 ? '部分' : (a == 2 ? '退完' : '暂无');
                    var f = a == 1 ? 'hover-yellow' : (a == 2 ? 'hover-green' : 'hover-white');
                    var g = c.srcInvId2 == 0 ? '点击退货' : '点击打开销售退货单' + '\r\n' + c.srcInvNo2;
                    if (c.srcOcId && c.srcOcNo) {
                        return '<span class="db h28 lh28 hover-gray b-r-3" title="已特殊另发">无需</span>';
                    } else {
                        return '<span class="goodsStatus2-btn db h28 lh28 b-r-3 ' + f + '" title="' + g + '" data-id="' + c.id + '" data-checkid="' + c.checkId + '" data-srcinvid1="' + c.srcInvId1 + '" data-srcinvid2="' + c.srcInvId2 + '" data-goodsstatus1="' + c.goodsStatus1 + '" data-goodsstatus2="' + c.goodsStatus2 + '">' + d + '</span>';
                    }
                }
            }, {
                name: 'invoiceNum',
                label: '发票',
                width: 42,
                fixed: !0,
                sortable: !0,
                align: 'center',
                formatter: function(a, b, c) {
                    if (c.isCancel == 1) {
                        return '<span class="db h28 lh28 hover-gray b-r-3" title="已作废">作废</span>';
                    } else if (c.isDraft == 1) {
                        return '<span class="db h28 lh28 hover-gray b-r-3" title="目前为草稿 无法进行更多操作">草稿</span>';
                    }
                    var d = a > 0 ? 'hover-green' : 'hover-white';
                    return '<span class="invoice-btn db h28 lh28 b-r-3 ' + d + '" title="点击管理发票" data-id="' + c.id + '">' + a + '</span>';
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
                name: 'srcMoneyId2',
                label: '收款单ID',
                hidden: !0
            }, {
                name: 'srcInvId1',
                label: '发货单ID',
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
            sortname: '',
            sortorder: '',
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
            queryConditions.status1 = statusCombo1.getValue() == -1 ? '' : statusCombo1.getText();
            queryConditions.status2 = statusCombo2.getValue();
            queryConditions.status3 = statusCombo3.getValue();
            queryConditions.status4 = statusCombo4.getValue();
            queryConditions.status5 = statusCombo5.getValue();
            queryConditions.status6 = statusCombo6.getValue();
            queryConditions.status7 = statusCombo7.getValue();
            queryConditions.status8 = statusCombo8.getValue() == -1 ? '' : statusCombo8.getText();
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
            if (Business.verifyRight('SO_ADD')) {
                var newTabId = (new Date).format("yyyyMMddhhmmss");
                parent.tab.addTabItem({
                    tabid: 'sales-salesOrder' + newTabId,
                    text: '<i class="iconfont icon-danye f14 mr5"></i>销售订单（新建）',
                    url: '/Uri/salesOrder_manage?tabId=sales-salesOrder' + newTabId
                });
            }
        });
        $('#getNotSendGoodsOrder').on('click', function(a) {
            a.preventDefault();
            if (Business.verifyRight('SO_ADD')) {
                $.dialog.prompt('请输入想要强制同步的网店未发货订单号（支持灵通打单平台、官网后台订单）', function(inputValue) {
                    var billNo = $.trim(inputValue);
                    if (billNo.length >= 19) {
                        Public.ajaxPost('/Api/SpiderOrder/notSendGoodsOrder_spider', {
                            billNo: billNo,
                            uName: defaultPage.SYSTEM.uName
                        }, function(res) {
                            if (res && 200 == res.status) {
                                $.dialog.tips('未发货订单强制同步成功（单号：' + billNo + '）！', 0, 5);
                                $('#skey').val(billNo);
                                $('#search').trigger('click');
                            } else {
                                $.dialog.tips(res.msg, 2);
                            }
                        });
                    } else {
                        $.dialog.tips('请输入有效订单号！', 2);
                    }
                });
            }
        });
        $('.grid-wrap').on('click', '.ui-icon-pencil', function(a) {
            a.preventDefault();
            var id = $(this).parent().data('id');
            id && parent.tab.addTabItem({
                tabid: 'sales-salesOrder' + id,
                text: '<i class="iconfont icon-danye f14 mr5"></i>销售订单',
                url: '/Uri/salesOrder_manage?id=' + id
            });
        });
        $('.grid-wrap').on('click', '.ui-icon-trash', function(a) {
            a.preventDefault();
            if (Business.verifyRight('SO_DELETE')) {
                var id = $(this).parent().data('id');
                id && $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                    Public.ajaxGet('/Bill/InvSo/delete', {
                        id: id
                    }, function(res) {
                        if (res && 200 == res.status) {
                            $.dialog.tips('删除成功！');
                            $('#grid').jqGrid('delRowData', id);
                            parent.frames['sales-salesOrder' + id] && parent.tab.removeTabItem('sales-salesOrder' + id);
                        } else {
                            $.dialog.tips('删除失败！' + res.msg, 2);
                        }
                    });
                });
            }
        });
        $('.grid-wrap').on('click', '.check-btn', function(a) {
            var that = $(this);
            if (Business.verifyRight('SO_CHECK')) {
                var id = $(this).data('id');
                var checkId = Number($(this).data('checkid')) > 0 ? 0 : defaultPage.SYSTEM.uId;
                var checkName = Number($(this).data('checkid')) > 0 ? "" : defaultPage.SYSTEM.uName;
                var rowData = $('#grid').jqGrid('getRowData', id);
                id && Public.ajaxPost('/Bill/InvSo/check', {
                    id: id,
                    checkId: checkId,
                    checkName: checkName,
                    billNo: rowData.billNo
                }, function(res) {
                    if (res && 200 == res.status) {
                        $.dialog.tips((checkId > 0 ? '审核' : '反审核') + '成功！');
                        that.parent().next().children('.moneyStatus1-btn').data('checkid', checkId);
                        that.parent().next().next().children('.goodsStatus1-btn').data('checkid', checkId);
                        that.parent().next().next().next().next().children('.moneyStatus2-btn').data('checkid', checkId);
                        that.parent().next().next().next().next().next().children('.goodsStatus2-btn').data('checkid', checkId);
                        $('#grid').jqGrid('setCell', id, 'checkId', checkId);
                        parent.frames['sales-salesOrder' + id] && parent.frames['sales-salesOrder' + id].location.reload();
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
                var srcMoneyId1 = $(this).data('srcmoneyid1');
                var rowData = $('#grid').jqGrid('getRowData', id);
                if (checkId == 0) {
                    $.dialog.alert('单据未审核，请立即审核！');
                } else {
                    if (srcMoneyId1 > 0) {
                        parent.tab.addTabItem({
                            tabid: 'money-receipt' + srcMoneyId1,
                            text: '<i class="iconfont icon-danye f14 mr5"></i>销售收款单',
                            url: '/Uri/receipt_manage?id=' + srcMoneyId1
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
                } else if (rowData.srcMoneyId1 == 0) {
                    $.dialog.alert('未收过款，无法退款！');
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
            var checkId = $(this).data('checkid');
            var srcInvId1 = $(this).data('srcinvid1');
            var goodsStatus1 = $(this).data('goodsstatus1');
            if (checkId == 0) {
                $.dialog.alert('单据未审核，请立即审核！');
            } else {
                if (srcInvId1 > 0) {
                    parent.tab.addTabItem({
                        tabid: 'inventory-sales' + srcInvId1,
                        text: '<i class="iconfont icon-danye f14 mr5"></i>销售发货单',
                        url: '/Uri/sales_manage?id=' + srcInvId1 + '&transType=102201'
                    });
                } else {
                    if (goodsStatus1 == 3) {
                        $.dialog.tips('直发订单默认已减少当前库存，无需再发货！', 2);
                    } else {
                        if (id) {
                            var newTabId = (new Date).format("yyyyMMddhhmmss");
                            parent.tab.addTabItem({
                                tabid: 'inventory-sales' + newTabId,
                                text: '<i class="iconfont icon-danye f14 mr5"></i>销售发货单（新建）',
                                url: '/Uri/sales_manage?turnBySalesOrder&tabId=inventory-sales' + newTabId + '&transType=102201',
                                data: {
                                    id: id,
                                    transType: 102201
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
            var checkId = $(this).data('checkid');
            var srcInvId1 = $(this).data('srcinvid1');
            var srcInvId2 = $(this).data('srcinvid2');
            var goodsStatus1 = $(this).data('goodsstatus1');
            if (checkId == 0) {
                $.dialog.alert('单据未审核，请立即审核！');
            } else {
                if (srcInvId2 > 0) {
                    parent.tab.addTabItem({
                        tabid: 'inventory-salesBack' + srcInvId2,
                        text: '<i class="iconfont icon-danye f14 mr5"></i>销售退货单',
                        url: '/Uri/sales_manage?id=' + srcInvId2 + '&transType=102202'
                    });
                } else {
                    if (srcInvId1 == 0 || goodsStatus1 == 0) {
                        $.dialog.tips('未发过货，不能退货！', 2);
                    } else {
                        if (id) {
                            var newTabId = (new Date).format("yyyyMMddhhmmss");
                            parent.tab.addTabItem({
                                tabid: 'inventory-salesBack' + newTabId,
                                text: '<i class="iconfont icon-danye f14 mr5"></i>销售退货单（新建）',
                                url: '/Uri/sales_manage?turnBySalesOrder&tabId=inventory-salesBack' + newTabId + '&transType=102202',
                                data: {
                                    id: id,
                                    transType: 102202
                                }
                            });
                        }
                    }
                }
            }
        });
        $('.grid-wrap').on('click', '.invoice-btn', function(a) {
            a.preventDefault();
            if (Business.verifyRight('INVOICE_QUERY')) {
                var id = $(this).data('id');
                var rowData = $('#grid').jqGrid('getRowData', id);
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
        $('.grid-wrap').on('click', '.redoanother-bill-btn', function(a) {
            a.preventDefault();
            var srcOcId = $(this).data('srcocid');
            if (srcOcId && Business.verifyRight('SC_QUERY')) {
                parent.tab.addTabItem({
                    tabid: 'sales-salesContract' + srcOcId,
                    text: '<i class="iconfont icon-danye f14 mr5"></i>特殊销售单',
                    url: '/Uri/salesContract_manage?id=' + srcOcId
                });
            }
        });
        $('.grid-wrap').on('click', '.redoanother-btn', function(a) {
            a.preventDefault();
            var id = $(this).data('id');
            var rowData = $('#grid').jqGrid('getRowData', id);
            if (rowData) {
                $.dialog.confirm('<p class="f14 fb">特殊另发后，将自动删除原发货单与退货单（如果存在）！确定继续？</p><p class="tl">说明1：适用于另外发货包含非内部自有产品，或者包含产品种类太多，一项项点选产品太费时的情况。</p><p class="tl">说明2：如果另外发货全是内部自有产品，且种类较少，点选产品不费时，可直接编辑原发货单即可。</p>', function() {
                    var newTabId = (new Date).format("yyyyMMddhhmmss");
                    parent.tab.addTabItem({
                        tabid: 'sales-salesContract' + newTabId,
                        text: '<i class="iconfont icon-danye f14 mr5"></i>特殊销售单（新建）',
                        url: '/Uri/salesContract_manage?turnBySalesOrder&tabId=sales-salesContract' + newTabId,
                        data: {
                            id: id,
                            billNo: rowData.billNo,
                            billDate: rowData.billDate,
                            contactId: rowData.contactId,
                            contactName: rowData.contactName,
                            sId: rowData.sId,
                            sName: rowData.sName,
                            amount: rowData.amount,
                            saler: rowData.uName,
                            type: 1
                        }
                    });
                });
            };
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
        $('.goods-showall').on('click', function() {
            if (goodsShowAll) {
                goodsShowAll = !1;
                $('.grid-wrap .goods-showall').html('<i class="iconfont icon-shuangxiajiantou f12 mr5"></i>展开全部');
                $('.grid-wrap .goods-showinfo').removeClass('icon-shangjiantou').addClass('icon-xiajiantou');
                $('.grid-wrap .goods-info').hide();
                $('.ui-jqgrid-bdiv td').removeClass('ui-break').addClass('ui-ellipsis');
            } else {
                goodsShowAll = !0;
                $('.grid-wrap .goods-showall').html('<i class="iconfont icon-shuangshangjiantou f12 mr5"></i>收缩全部');
                $('.grid-wrap .goods-showinfo').removeClass('icon-xiajiantou').addClass('icon-shangjiantou');
                $('.grid-wrap .goods-info').show();
                $('.ui-jqgrid-bdiv td').removeClass('ui-ellipsis').addClass('ui-break');
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
