var defaultPage = Public.getDefaultPage();
var queryConditions = {
    uId: -1,
    beginDate: (new Date).format('yyyy-MM') + '-01',
    endDate: (new Date).format('yyyy-MM-dd')
};
var combo1 = $('#combo1').combo({
    data: '/Base/User/queryAll?normal=yes&department=采购部',
    ajaxOptions: {
        formatData: function(res) {
            return res.data.rows.unshift({
                id: -1,
                name: '全部采购人'
            }), res.data.rows;
        }
    },
    value: 'id',
    text: 'name',
    defaultSelected: 0,
    width: 90,
    listHeight: 600,
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
        $('#beginDate').val((new Date).format('yyyy-MM') + '-01');
        $('#endDate').val((new Date).format('yyyy-MM-dd'));
    },
    initGrid: function() {
        var a = Public.setGrid();
        var rowData = {};
        var rank = 0;
        $('#grid').jqGrid({
            url: '/Bill/Report/purchaseInvoiceBack',
            postData: queryConditions,
            datatype: 'json',
            colModel: [{
                name: 'range',
                label: '<span class="ml20">排名</span>',
                width: 70,
                fixed: !0,
                align: 'center',
                formatter: function(a, b, c) {
                    if (b.rowId == 1) {
                        rank = 1;
                        rowData = c;
                    } else {
                        if (!(c.rate == rowData.rate && c.purInvoiceAmount == rowData.purInvoiceAmount && c.purPaidAmount == rowData.purPaidAmount)) {
                            rowData = c;
                            rank++;
                        }
                    }
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
                label: '采购人',
                width: 70,
                fixed: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'dateRange',
                label: '起止日期',
                width: 170,
                fixed: !0
            }, {
                name: 'purPaidAmount',
                label: '支出金额<small class="ml5">仅供参考</small>',
                width: 180,
                fixed: !0,
                sortable: !0,
                classes: 'ui-ellipsis',
                formatter: 'currency'
            }, {
                name: 'purInvoiceAmount',
                label: '回票金额<small class="ml5">以录入时间为准</small>',
                width: 180,
                fixed: !0,
                sortable: !0,
                classes: 'ui-ellipsis',
                formatter: 'currency'
            }, {
                name: 'rate',
                label: '回票率<small class="ml5">仅供参考</small>',
                width: 100,
                formatter: function(b, c, d) {
                    return parseFloat(Number(b).toFixed(2)) + ' ％';
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
                    dateRange: '<span class="fr mr20">合计（按财务维度）：</span>',
                    purPaidAmount: data.data.total_purPaidAmount,
                    purInvoiceAmount: data.data.total_purInvoiceAmount,
                    rate: data.data.total_rate
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
            queryConditions.uId = combo1.getValue();
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
