var defaultPage = Public.getDefaultPage();
var pricePlaces = Number(defaultPage.SYSTEM.pricePlaces);
var queryConditions = {
    status1: -1,
    goodsId: -1,
    beginDate: '',
    endDate: '',
    type: 0
};
var statusCombo1 = $('#combo1').combo({
    data: [
        {id: -1, name: '所有变动量'},
        {id: 0, name: '正变动量'},
        {id: 1, name: '负变动量'}
    ],
    value: 'id',
    text: 'name',
    defaultSelected: 0,
    width: 90,
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
        $('#beginDate').val(Public.reduceMonths((new Date).format('yyyy-MM-dd'), 12));
        $('#endDate').val(defaultPage.SYSTEM.endDate);
    },
    initGrid: function() {
        var a = Public.setGrid();
        $('#grid').jqGrid({
            url: '/Base/Goods/listInventoryHistory',
            postData: queryConditions,
            datatype: 'json',
            colModel: [{
                name: 'goodsId',
                label: '<span class="ml10">产品ID</span>',
                width: 60,
                fixed: !0,
                classes: 'ti10 gray'
            }, {
                name: 'goodsCategoryName',
                label: '产品类别',
                width: 100,
                fixed: !0,
                title: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'goods',
                label: '产品信息',
                width: 100,
                title: !0,
                classes: 'ui-ellipsis',
                formatter: function(a, b, c) {
                    return c.goodsId ? c.goodsNo + ' ' + c.goodsName + (c.goodsSpec ? ' ' + c.goodsSpec : '') + (c.goodsSku ? ' ' + c.goodsSku : '') + (c.goodsBrandName ? ' ' + c.goodsBrandName : '') + ' ' + c.goodsUnitName : '&nbsp;';
                }
            }, {
                name: 'changeType',
                label: '发生业务',
                width: 150,
                fixed: !0,
                title: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'billNo',
                label: '业务单据号',
                width: 170,
                fixed: !0,
                classes: 'ui-ellipsis hover'
            }, {
                name: 'changeTime',
                label: '库存变动时间',
                width: 180,
                fixed: !0,
                sortable: !0
            }, {
                name: 'changeCurrentQty',
                label: '变动量',
                width: 120,
                fixed: !0,
                sortable: !0,
                cellattr: function(rowId, val, rawObject, cm, rowdata) {
                    var color = (0 < val ? 'green' : (0 == val ? 'black' : 'red'));
                    return 'class="f14 ' + color + '"';
                },
                formatter: function(val, opt, row) {
                    var type = (0 < val ? '+' : '');
                    return type + val;
                }
            }, {
                name: 'currentQty',
                label: '变动后',
                width: 120,
                fixed: !0,
                sortable: !0,
                classes: 'f14',
                formatter: function(val, opt, row) {
                    return Number(val);
                }
            }, {
                name: 'billId',
                label: '业务单据ID',
                width: 0,
                hidden: !0
            }, {
                name: 'billType',
                label: '业务类别',
                width: 0,
                hidden: !0
            }, {
                name: 'transType',
                label: '业务类型',
                width: 0,
                hidden: !0
            }],
            cmTemplate: {sortable: !1, title: !1},
            jsonReader: {root: 'data.rows', records: 'data.records', total: 'data.total', repeatitems: !1, id: 'id'},
            width: a.w,
            height: a.h - 45,
            altRows: !1,
            gridview: !0,
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
            footerrow: !0,
            userDataOnFooter: !0,
            loadComplete: function(data) {
                if (data && data.status == 200) {
                    $('#grid').jqGrid('footerData', 'set', {
                        changeTime: '<span class="fr mr20">合计：</span>',
                        changeCurrentQty: data.data.totalChangeCurrentQty
                    });
                } else {
                    data.status == -1 && $.dialog.tips(data.msg, 2);
                }
            },
            loadError: function(a, b) {
                $.dialog.tips('Type: ' + b + '; Response: ' + a.status + ' ' + a.statusText, 2);
            },
            onCellSelect: function(a, b) {
                var c = $('#grid').jqGrid('getRowData', a);
                if (b == 4) {
                    switch (c.billType) {
                        case 'PO':
                            parent.tab.addTabItem({
                                tabid: 'purchase-purchaseOrder' + c.billId,
                                text: '<i class="iconfont icon-danye f14 mr5"></i>采购订单',
                                url: '/Uri/purchaseOrder_manage?id=' + c.billId
                            });
                            break;
                        case 'PU':
                            if (c.transType == '102101') {
                                var d = 'inventory-purchase' + c.billId;
                                var e = '<i class="iconfont icon-danye f14 mr5"></i>采购收货单';
                            } else {
                                var d = 'inventory-purchaseBack' + c.billId;
                                var e = '<i class="iconfont icon-danye f14 mr5"></i>采购退货单';
                            }
                            parent.tab.addTabItem({
                                tabid: d,
                                text: e,
                                url: '/Uri/purchase_manage?id=' + c.billId + '&transType=' + c.transType
                            });
                            break;
                        case 'SA':
                            if (c.transType == '102201') {
                                var d = 'inventory-sales' + c.billId;
                                var e = '<i class="iconfont icon-danye f14 mr5"></i>销售发货单';
                            } else {
                                var d = 'inventory-salesBack' + c.billId;
                                var e = '<i class="iconfont icon-danye f14 mr5"></i>销售退货单';
                            }
                            parent.tab.addTabItem({
                                tabid: d,
                                text: e,
                                url: '/Uri/sales_manage?id=' + c.billId + '&transType=' + c.transType
                            });
                            break;
                        case 'SC':
                            parent.tab.addTabItem({
                                tabid: 'sales-salesContract' + c.billId,
                                text: '<i class="iconfont icon-danye f14 mr5"></i>特殊销售单',
                                url: '/Uri/salesContract_manage?id=' + c.billId
                            });
                            break;
                        case 'PK':
                            $.dialog({
                                title: '查看配货任务',
                                content: 'url:/Uri/produceKittingTask_manage',
                                data: {
                                    oper: 'edit',
                                    rowId: c.billId
                                },
                                width: 1250,
                                height: 600,
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
                            break;
                        case 'PP':
                            $.dialog({
                                title: '查看加工任务',
                                content: 'url:/Uri/produceProcessingTask_manage',
                                data: {
                                    oper: 'edit',
                                    rowId: c.billId
                                },
                                width: 1250,
                                height: 600,
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
                            break;
                        case 'OI':
                            parent.tab.addTabItem({
                                tabid: 'inventory-otherWarehouse' + c.billId,
                                text: '<i class="iconfont icon-danye f14 mr5"></i>其他入库单',
                                url: '/Uri/inout_manage_in?id=' + c.billId
                            });
                            break;
                        case 'OO':
                            parent.tab.addTabItem({
                                tabid: 'inventory-otherOutbound' + c.billId,
                                text: '<i class="iconfont icon-danye f14 mr5"></i>其他出库单',
                                url: '/Uri/inout_manage_out?id=' + c.billId
                            });
                            break;
                        case 'INVCHECK':
                            parent.tab.addTabItem({
                                tabid: 'inventory-invCheck',
                                text: '<i class="iconfont icon-pandian f14 mr5"></i>库存盘点',
                                url: '/Uri/inventory_check'
                            });
                            break;
                        default:
                            break;
                    }
                }
            }
        });
    },
    initEvent: function() {
        Business.filterGoods(!0);
        laydate.render({
            elem: "#datePicker",
            range: ['#beginDate', '#endDate'],
            done: function() {
                setTimeout(function() {
                    $('#search').trigger('click');
                }, 300)
            }
        });
        $('#search').on('click', function() {
            $(this).addClass('current').siblings().removeClass('current');
            queryConditions.status1 = statusCombo1.getValue();
            queryConditions.goodsId = $('#filter-goods input').val() || -1;
            queryConditions.beginDate = $('#beginDate').val();
            queryConditions.endDate = $('#endDate').val();
            queryConditions.type = 0;
            $('#grid').jqGrid('setGridParam', {
                postData: queryConditions,
                page: 1
            }).trigger('reloadGrid');
        });
        $('#lastYearUsedQty').on('click', function() {
            $('#beginDate').val(Public.reduceMonths((new Date).format('yyyy-MM-dd'), 12));
            $(this).addClass('current').siblings().removeClass('current');
            queryConditions.status1 = statusCombo1.getValue();
            queryConditions.goodsId = $('#filter-goods input').val() || -1;
            queryConditions.beginDate = $('#beginDate').val();
            queryConditions.endDate = $('#endDate').val();
            queryConditions.type = 1;
            $('#grid').jqGrid('setGridParam', {
                postData: queryConditions,
                page: 1
            }).trigger('reloadGrid');
        });
        $('#refresh').on('click', function() {
            window.location.reload();
        });
        $(window).on('resize', function() {
            Public.resizeGrid();
        });
    }
};
$(function() {
    THISPAGE.init();
});
