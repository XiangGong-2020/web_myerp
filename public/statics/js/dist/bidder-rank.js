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
        var rank = 0;
        $('#grid').jqGrid({
            url: '/Bill/Report/bidderRank',
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
                name: 'quotedName',
                label: '报价人',
                width: 70,
                fixed: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'offerDateRange',
                label: '时间段',
                width: 170,
                fixed: !0
            }, {
                name: 'amount',
                label: '投标金额<small class="ml5">元</small>',
                width: 120,
                fixed: !0
            }, {
                name: 'winAmount',
                label: '中标金额<small class="ml5">元</small>',
                width: 120,
                fixed: !0
            }, {
                name: 'offerCount',
                label: '投标笔数',
                width: 120,
                fixed: !0,
                formatter: function(a, b, c) {
                    return Number(a);
                }
            }, {
                name: 'winCount',
                label: '中标笔数',
                width: 120,
                fixed: !0,
                formatter: function(a, b, c) {
                    return Number(a);
                }
            }, {
                name: 'winRate',
                label: '中标率<small class="ml5">中标笔数 / 投标笔数</small>',
                width: 120,
                formatter: function(a, b, c) {
                    return a + ' ％';
                }
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
            sortname: 'quotedName',
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
                    amount: data.data.amountTotal,
                    winAmount: data.data.winAmountTotal,
                    offerCount: data.data.offerCountTotal,
                    winCount: data.data.winCountTotal,
                    winRate: Number(data.data.winCountTotal / data.data.offerCountTotal * 100).toFixed(2)
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
        $('#search').on('click', function(a) {
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
