var defaultPage = Public.getDefaultPage();
var queryConditions = {
    beginDate: (new Date).format('yyyy-MM') + '-01',
    endDate: (new Date).format('yyyy-MM-dd')
};
var THISPAGE = {
    init: function() {
        THISPAGE.initDom();
        THISPAGE.initGrid();
        THISPAGE.initEvent();
    },
    initDom: function() {
        $('#beginDate').val((new Date).format('yyyy-MM') + '-01');
        $('#endDate').val((new Date).format('yyyy-MM-dd'));
    },
    initGrid: function() {
        var a = Public.setGrid();
        var rowData = {};
        var rank = 0;
        $('#grid').jqGrid({
            url: '/Bill/Report/purchaserRank',
            postData: queryConditions,
            datatype: 'json',
            colModel: [{
                name: 'range',
                label: '<span class="ml20">排名</span>',
                width: 70,
                fixed: !0,
                align: 'center',
                formatter: function(a,b,c) {
                    if (b.rowId == 1) {
                        rank = 1;
                        rowData = c;
                    } else {
                        if (!(c.core == rowData.core && c.orderNum == rowData.orderNum && c.contractNum == rowData.contractNum && c.supplierNum == rowData.supplierNum)) {
                            rowData = c;
                            rank++;
                        }
                    }
                    if (rank == 1) {
                        return '<i class="iconfont icon-diyiming gold f30"></i>'
                    } else if (rank == 2) {
                        return '<i class="iconfont icon-dierming gray f26"></i>'
                    } else if (rank == 3) {
                        return '<i class="iconfont icon-disanming copper f22"></i>'
                    } else {
                        return rank
                    }
                }
            }, {
                name: 'uName',
                label: '采购人',
                width: 70,
                fixed: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'dateRange',
                label: '时间段',
                width: 170,
                fixed: !0
            }, {
                name: 'orderNum',
                label: '订单采购项数',
                width: 120,
                fixed: !0
            }, {
                name: 'contractNum',
                label: '特采采购项数',
                width: 120,
                fixed: !0
            }, {
                name: 'supplierNum',
                label: '商家开发数',
                width: 120,
                fixed: !0
            }, {
                name: 'invoiceNum',
                label: '发票录入数',
                width: 120,
                fixed: !0
            }, {
                name: 'overPriceNum',
                label: '超报价采购笔数',
                width: 120,
                fixed: !0,
                classes: 'ble ti20 f14 fb red'
            }, {
                name: 'overPriceAmount',
                label: '超报价总金额<small title="单位：元">（元）</small>',
                width: 120,
                classes: 'f14 fb red'
            }],
            cmTemplate: {sortable: !1, title: !1},
            jsonReader: {root: 'data.rows', records: 'data.records', total: 'data.total', repeatitems: !1, id: 'id'},
            width: a.w,
            height: a.h - 45,
            altRows: !1,
            gridview: !0,
            rownumbers: !1,
            multiselect: !1,
            shrinkToFit: !0,
            forceFit: !1,
            viewsortcols: [true, 'vertical', true],
            sortname: 'uName',
            sortorder: 'ASC',
            pager: '#page',
            page: 1,
            rowNum: 1000,
            rowList: [1000, 2000, 5000],
            viewrecords: !0,
            cellsubmit: 'clientArray',
            footerrow: !0,
            userDataOnFooter: !0,
            loadComplete: function(data) {
                data.status == -1 && $.dialog.tips(data.msg, 2);
                THISPAGE.calTotal();
            },
            loadError: function(a, b) {
                $.dialog.tips('Type: ' + b + '; Response: ' + a.status + ' ' + a.statusText, 2);
            }
        });
    },
    initEvent: function() {
        laydate.render({
            elem: "#datePicker",
            range: ['#beginDate', '#endDate'],
            done: function() {
                setTimeout(function() {
                    $('#search').trigger('click').addClass('current').siblings().removeClass('current');
                }, 300)
            }
        });
        $('#search').on('click', function() {
            queryConditions.beginDate = $('#beginDate').val();
            queryConditions.endDate = $('#endDate').val();
            $('#grid').jqGrid('setGridParam', {
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
        $(window).on('resize', function() {
            Public.autoGridWidth($('#grid'));
        });
    },
    calTotal: function() {
        var coreTotal = orderNumTotal = contractNumTotal = supplierNumTotal = invoiceNumTotal = 0;
        for (var a = $('#grid').jqGrid('getDataIDs'), d = 0, e = a.length; e > d; d++) {
            var f = a[d];
            var g = $('#grid').jqGrid('getRowData', f);
            g.core && (coreTotal += parseFloat(g.core));
            g.orderNum && (orderNumTotal += parseFloat(g.orderNum));
            g.contractNum && (contractNumTotal += parseFloat(g.contractNum));
            g.supplierNum && (supplierNumTotal += parseFloat(g.supplierNum));
            g.invoiceNum && (invoiceNumTotal += parseFloat(g.invoiceNum));
        }
        $('#grid').jqGrid('footerData', 'set', {
            dateRange: '<span class="fr mr20">合计：</span>',
            core: coreTotal,
            orderNum: orderNumTotal,
            contractNum: contractNumTotal,
            supplierNum: supplierNumTotal,
            invoiceNum: invoiceNumTotal
        });
    }
};
$(function() {
    THISPAGE.init();
});
