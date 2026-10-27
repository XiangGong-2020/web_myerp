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
        var a = Public.setGrid(135, 0);
        var rank = 0;
        $('#grid').jqGrid({
            url: '/Bill/Report/salerRank',
            postData: queryConditions,
            datatype: 'json',
            colModel: [{
                name: 'range',
                label: '<span class="ml20">排名</span>',
                width: 70,
                fixed: !0,
                align: 'center',
                formatter: function(a, b, c) {
                    rank = b.rowId;
                    if (rank == 1) {
                        return '<i class="iconfont icon-diyiming gold f30"></i>';
                    } else if (rank == 2) {
                        return '<i class="iconfont icon-dierming gray f26"></i>';
                    } else if (rank == 3) {
                        return '<i class="iconfont icon-disanming copper f22"></i>';
                    } else {
                        return rank;
                    }
                }
            }, {
                name: 'uName',
                label: '销售人',
                width: 70,
                fixed: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'dateRange',
                label: '时间段',
                width: 170,
                fixed: !0
            }, {
                name: 'totalAmount',
                label: '总业绩<small class="ml5">元</small>',
                width: 120,
                fixed: !0
            }, {
                name: 'onlineshopAmount',
                label: '网店订单业绩<small class="ml5">元</small>',
                width: 120,
                fixed: !0
            }, {
                name: 'contractAmount',
                label: '特殊销售业绩<small class="ml5">元</small>',
                width: 120,
                fixed: !0
            }, {
                name: 'orderAmount',
                label: '手工订单业绩<small class="ml5">元</small>',
                width: 120
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
                $('#grid').jqGrid('footerData', 'set', {
                    offerDateRange: '<span class="fr mr20">合计：</span>',
                    totalAmount: data.data.totalAmountTotal,
                    onlineshopAmount: data.data.onlineshopAmountTotal,
                    contractAmount: data.data.contractAmountTotal,
                    orderAmount: data.data.orderAmountTotal
                });
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
                }, 300);
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
    }
};
$(function() {
    THISPAGE.init();
});
