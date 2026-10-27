var defaultPage = Public.getDefaultPage();
var queryConditions = {
    contactId: '',
    goodsId: '',
    storageId: '',
    beginDate: (new Date).format('yyyy-MM') + '-01',
    endDate: (new Date).format('yyyy-MM-dd')
};
var totalOrderQty = 0, totalOrderAmount = 0, totalContractQty = 0, totalContractAmount = 0;
var THISPAGE = {
    init: function() {
        THISPAGE.initDom();
        THISPAGE.initGrid();
        THISPAGE.initEvent();
    },
    initDom: function() {
        $('#beginDate').val((new Date).format('yyyy-MM') + '-01');
        $('#endDate').val((new Date).format('yyyy-MM-dd'));
        setTimeout(() => {
        }, 30000);
    },
    initGrid: function() {
        var a = Public.setGrid();
        $('#grid1').jqGrid({
            url: '/Bill/Report/salesDetail?type=order',
            postData: queryConditions,
            datatype: 'json',
            colModel: [{
                name: 'id',
                label: 'ID',
                hidden: !0
            }, {
                name: 'billDate',
                label: '<span class="ml10">日期</span>',
                width: 80,
                fixed: !0,
                classes: 'ti10'
            }, {
                name: 'billType',
                label: '类型',
                width: 70,
                fixed: !0
            }, {
                name: 'billNo',
                label: '单号',
                width: 160,
                fixed: !0,
                title: !0,
                classes: 'ui-ellipsis hover'
            }, {
                name: 'contact',
                label: '客户',
                width: 80,
                fixed: !0,
                title: !0,
                classes: 'ui-ellipsis hover'
            }, {
                name: 'goods',
                label: '产品',
                width: 100,
                title: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'brand',
                label: '品牌',
                width: 60,
                fixed: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'storage',
                label: '仓库',
                width: 60,
                fixed: !0,
                title: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'unit',
                label: '单位',
                width: 50,
                fixed: !0
            }, {
                name: 'qty',
                label: '销售数量',
                width: 140,
                fixed: !0
            }, {
                name: 'price',
                label: '销售单价',
                width: 140,
                fixed: !0
            }, {
                name: 'amount',
                label: '销售金额',
                width: 140,
                fixed: !0
            }, {
                name: 'billId',
                hidden: !0
            }, {
                name: 'contactId',
                hidden: !0
            }],
            cmTemplate: {sortable: !1, title: !1},
            jsonReader: {root: 'data.rows', records: 'data.records', total: 'data.total', repeatitems: !1, id: 'id'},
            width: a.w,
            height: ($(window).height() - 294) / 2,
            altRows: !1,
            gridview: !0,
            rownumbers: !1,
            multiselect: !1,
            shrinkToFit: !0,
            forceFit: !1,
            viewsortcols: [true, 'vertical', true],
            sortname: 'id',
            sortorder: 'DESC',
            pager: '#page1',
            page: 1,
            rowNum: 100,
            rowList: [100, 200, 500],
            viewrecords: !0,
            loadComplete: function(data) {
                data.status == -1 && $.dialog.tips(data.msg, 2);
                if (data && 200 == data.status) {
                    totalOrderQty = parseInt(data.data.totalQty || 0);
                    totalOrderAmount = Number(data.data.totalAmount || 0);
                }
                $('#totalOrder').html(parseInt(totalOrderQty) + '<span class="ml5 mr20 f12 fn gray">件</span>' + Number(totalOrderAmount).toFixed(2) + '<span class="ml5 f12 fn gray">元</span>');
                $('#totalContract').html(parseInt(totalContractQty) + '<span class="ml5 mr20 f12 fn gray">件</span>' + Number(totalContractAmount).toFixed(2) + '<span class="ml5 f12 fn gray">元</span>');
                $('#totalAll').html((totalOrderQty + totalContractQty) + '<span class="ml5 mr20 f12 fn gray">件</span>' + Number(totalOrderAmount + totalContractAmount).toFixed(2) + '<span class="ml5 f12 fn gray">元</span>');
            },
            loadError: function(a, b) {
                $.dialog.tips('Type: ' + b + '; Response: ' + a.status + ' ' + a.statusText, 2);
            },
            onCellSelect: function(a, b) {
                var rowData = $('#grid1').jqGrid('getRowData', a);
                if (b == 3) {
                    parent.tab.addTabItem({
                        tabid: 'sales-salesOrder' + rowData.billId,
                        text: '<i class="iconfont icon-danye f14 mr5"></i>销售订单',
                        url: '/Uri/salesOrder_manage?id=' + rowData.billId
                    });
                }
                if (b == 4) {
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
            }
        });
        $('#grid2').jqGrid({
            url: '/Bill/Report/salesDetail?type=contract',
            postData: queryConditions,
            datatype: 'json',
            colModel: [{
                name: 'id',
                label: 'ID',
                hidden: !0
            }, {
                name: 'billDate',
                label: '<span class="ml10">日期</span>',
                width: 80,
                fixed: !0,
                classes: 'ti10'
            }, {
                name: 'billType',
                label: '类型',
                width: 70,
                fixed: !0
            }, {
                name: 'billNo',
                label: '单号',
                width: 160,
                fixed: !0,
                title: !0,
                classes: 'ui-ellipsis hover'
            }, {
                name: 'contact',
                label: '客户',
                width: 80,
                fixed: !0,
                title: !0,
                classes: 'ui-ellipsis hover'
            }, {
                name: 'goods',
                label: '产品',
                width: 100,
                title: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'brand',
                label: '品牌',
                width: 60,
                fixed: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'storage',
                label: '仓库',
                width: 60,
                fixed: !0,
                title: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'unit',
                label: '单位',
                width: 50,
                fixed: !0
            }, {
                name: 'qty',
                label: '销售数量',
                width: 140,
                fixed: !0
            }, {
                name: 'price',
                label: '销售单价',
                width: 140,
                fixed: !0
            }, {
                name: 'amount',
                label: '销售金额',
                width: 140,
                fixed: !0
            }, {
                name: 'billId',
                hidden: !0
            }, {
                name: 'contactId',
                hidden: !0
            }],
            cmTemplate: {sortable: !1, title: !1},
            jsonReader: {root: 'data.rows', records: 'data.records', total: 'data.total', repeatitems: !1, id: 'id'},
            width: a.w,
            height: ($(window).height() - 294) / 2,
            altRows: !1,
            gridview: !0,
            rownumbers: !1,
            multiselect: !1,
            shrinkToFit: !0,
            forceFit: !1,
            viewsortcols: [true, 'vertical', true],
            sortname: 'id',
            sortorder: 'DESC',
            pager: '#page2',
            page: 1,
            rowNum: 100,
            rowList: [100, 200, 500],
            viewrecords: !0,
            loadComplete: function(data) {
                data.status == -1 && $.dialog.tips(data.msg, 2);
                if (data && 200 == data.status) {
                    totalContractQty = parseInt(data.data.totalQty || 0);
                    totalContractAmount = Number(data.data.totalAmount || 0);
                }
                $('#totalOrder').html(parseInt(totalOrderQty) + '<span class="ml5 mr20 f12 fn gray">件</span>' + Number(totalOrderAmount).toFixed(2) + '<span class="ml5 f12 fn gray">元</span>');
                $('#totalContract').html(parseInt(totalContractQty) + '<span class="ml5 mr20 f12 fn gray">件</span>' + Number(totalContractAmount).toFixed(2) + '<span class="ml5 f12 fn gray">元</span>');
                $('#totalAll').html((totalOrderQty + totalContractQty) + '<span class="ml5 mr20 f12 fn gray">件</span>' + Number(totalOrderAmount + totalContractAmount).toFixed(2) + '<span class="ml5 f12 fn gray">元</span>');
            },
            loadError: function(a, b) {
                $.dialog.tips('Type: ' + b + '; Response: ' + a.status + ' ' + a.statusText, 2);
            },
            onCellSelect: function(a, b) {
                var rowData = $('#grid2').jqGrid('getRowData', a);
                if (b == 3) {
                    parent.tab.addTabItem({
                        tabid: 'sales-salesContract' + rowData.billId,
                        text: '<i class="iconfont icon-danye f14 mr5"></i>特殊销售单',
                        url: '/Uri/salesContract_manage?id=' + rowData.billId
                    });
                }
                if (b == 4) {
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
            }
        });
    },
    initEvent: function() {
        Business.filterCustomer(!1);
        Business.filterGoods(!1);
        Business.filterStorage(!1);
        laydate.render({
            elem: "#datePicker",
            range: ['#beginDate', '#endDate'],
            done: function() {
                setTimeout(function() {
                    $('#search').trigger('click').addClass('current').siblings().removeClass('current');
                }, 300);
            }
        });
        $('#search').on('click', function(a) {
            a.preventDefault();
            queryConditions.contactId = $('#filter-customer input').val() || '';
            queryConditions.goodsId = $('#filter-goods input').val() || '';
            queryConditions.storageId = $('#filter-storage input').val() || '';
            queryConditions.beginDate = $('#beginDate').val();
            queryConditions.endDate = $('#endDate').val();
            $('#grid1').jqGrid('setGridParam', {
                url: '/Bill/Report/salesDetail?type=order',
                postData: queryConditions,
                page: 1
            }).trigger('reloadGrid');
            $('#grid2').jqGrid('setGridParam', {
                url: '/Bill/Report/salesDetail?type=contract',
                postData: queryConditions,
                page: 1
            }).trigger('reloadGrid');
        });
        $('#curMonth').on('click', function() {
            $(this).addClass('current').siblings().removeClass('current');
            $('#beginDate').val((new Date).format('yyyy-MM') + '-01');
            $('#endDate').val((new Date).format('yyyy-MM-dd'));
            $('#search').trigger('click');
        });
        $('#lastMonth').on('click', function() {
            $(this).addClass('current').siblings().removeClass('current');
            $('#beginDate').val(Public.reduceMonths((new Date).format('yyyy-MM') + '-01', 1));
            $('#endDate').val(Public.reduceDays((new Date).format('yyyy-MM') + '-01', 1));
            $('#search').trigger('click');
        });
        $('#curYear').on('click', function() {
            $(this).addClass('current').siblings().removeClass('current');
            $('#beginDate').val((new Date).format('yyyy-01-01'));
            $('#endDate').val((new Date).format('yyyy-MM-dd'));
            $('#search').trigger('click');
        });
        $('#lastYear').on('click', function() {
            $(this).addClass('current').siblings().removeClass('current');
            $('#beginDate').val(Public.reduceMonths((new Date).format('yyyy-01-01'), 12));
            $('#endDate').val(Public.reduceMonths((new Date).format('yyyy-12-31'), 12));
            $('#search').trigger('click');
        });
        $('#searchAll').on('click', function() {
            $(this).addClass('current').siblings().removeClass('current');
            $('#beginDate').val(defaultPage.SYSTEM.beginDate);
            $('#endDate').val(defaultPage.SYSTEM.endDate);
            $('#search').trigger('click');
        });
        $('#refresh').on('click', function() {
            window.location.reload();
        });
        $('#reset').on('click', function(a) {
            a.preventDefault();
            $('#filter-customer input').val('');
            $('#filter-goods input').val('');
            $('#filter-storage input').val('');
            queryConditions.contactId = '';
            queryConditions.goodsId = '';
            queryConditions.storageId = '';
            $('#grid1').jqGrid('setGridParam', {
                postData: queryConditions,
                page: 1
            }).trigger('reloadGrid');
            $('#grid2').jqGrid('setGridParam', {
                postData: queryConditions,
                page: 1
            }).trigger('reloadGrid');
        });
        $(window).on('resize', function() {
            Public.autoGridWidth($('#grid1'));
            Public.autoGridWidth($('#grid2'));
        });
    }
};
$(function() {
    THISPAGE.init();
});
