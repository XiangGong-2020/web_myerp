var defaultPage = Public.getDefaultPage();
var queryConditions = {
    transType: 103101,
    beginDate: '',
    endDate: '',
    minAmount: '',
    maxAmount: '',
    skey: ''
};
var THISPAGE = {
    init: function() {
        this.initDom();
        this.initGrid();
        this.initEvent();
    },
    initDom: function() {
        $('#beginDate').val(defaultPage.SYSTEM.beginDate);
        $('#endDate').val(defaultPage.SYSTEM.endDate);
    },
    initGrid: function() {
        var a = Public.setGrid();
        $('#grid').jqGrid({
            url: '/Bill/MonIo/list',
            postData: queryConditions,
            datatype: 'json',
            colModel: [{
                name: 'billNo',
                label: '<span class="ml10">单据编号</span>',
                width: 180,
                fixed: !0,
                sortable: !0,
                classes: 'ti10 ui-ellipsis'
            }, {
                name: 'miniTypeName',
                label: '细分类型',
                width: 100,
                fixed: !0,
                sortable: !0,
                classes: 'ui-ellipsis',
                formatter: function(a, b, c) {
                    return c.miniType == 1 ? '销售收款单' : (c.miniType == 2 ? '采购退款单' : (c.miniType == 3 ? '特采退款单' : (c.miniType == 4 ? '快递退款单' : '')));
                }
            }, {
                name: 'billDate',
                label: '单据日期',
                width: 100,
                fixed: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'amount',
                label: '应收金额',
                width: 100,
                fixed: !0,
                sortable: !0,
                classes: 'ui-ellipsis',
                formatter: 'currency'
            }, {
                name: 'cleared',
                label: '已收金额',
                width: 100,
                fixed: !0,
                sortable: !0,
                classes: 'ui-ellipsis',
                formatter: 'currency'
            }, {
                name: 'contactName',
                label: '收款对象',
                width: 300,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'description',
                label: '备注',
                width: 100,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray'
            }, {
                name: 'srcNo',
                label: '源单',
                width: 160,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis hover',
                formatter: function(a, b, c) {
                    return (c.srcTransType && c.srcId && c.srcNo) ? '<span class="srcNo" data-id="' + c.id + '">' + c.srcNo + '</span>' : '';
                }
            }, {
                name: 'uName',
                label: '制单人',
                width: 54,
                fixed: !0,
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
                    var d = 0 == a ? '未审' : '已审';
                    var e = 0 == a ? '点击审核' : '点击反审核';
                    var f = 0 == a ? 'hover-red' : 'hover-green';
                    return '<span class="check-btn db h28 lh28 b-r-3 ' + f + '" title="' + e + '" data-checkid="' + a + '" data-id="' + c.id + '">' + d + '</span>';
                }
            }, {
                name: 'moneyStatus',
                label: '结算',
                width: 42,
                fixed: !0,
                sortable: !0,
                align: 'center',
                formatter: function(a, b, c) {
                    var d = 0 == a ? '未结' : (1 == a ? '部分' : '结清');
                    var e = 0 == a ? 'hover-red' : (1 == a ? 'hover-yellow' : 'hover-green');
                    return '<span class="moneyStatus-btn db h28 lh28 b-r-3 ' + e + '" data-id="' + c.id + '">' + d + '</span>';
                }
            }, {
                name: 'operate',
                label: '操作',
                width: 72.8,
                fixed: !0,
                formatter: Public.operFmatter
            }, {
                name: 'id',
                label: 'id',
                width: 0,
                hidden: !0
            }, {
                name: 'transType',
                label: '业务类型',
                width: 0,
                hidden: !0
            }, {
                name: 'miniType',
                label: '细分类型',
                width: 0,
                hidden: !0
            }, {
                name: 'srcTransType',
                label: '关联单据业务类型',
                width: 0,
                hidden: !0
            }, {
                name: 'srcId',
                label: '关联单据ID',
                width: 0,
                hidden: !0
            }],
            cmTemplate: {sortable: !1, title: !1},
            jsonReader: {root: 'data.rows', records: 'data.records', total: 'data.total', repeatitems: !1, id: 'id'},
            width: a.w,
            height: a.h,
            altRows: !1,
            gridview: !0,
            multiselect: !1,
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
            loadError: function(a, b) {
                $.dialog.tips('Type: ' + b + '; Response: ' + a.status + ' ' + a.statusText, 2);
            },
            ondblClickRow: function(b, c, d, e) {
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
        $('.grid-wrap').on('click', '.srcNo', function(a) {
            a.preventDefault();
            var id = $(this).data('id');
            var rowData = $('#grid').jqGrid('getRowData', id);
            if (rowData.srcTransType && rowData.srcId) {
                switch (rowData.srcTransType) {
                    case '101101':
                        parent.tab.addTabItem({
                            tabid: 'purchase-purchaseOrder' + rowData.srcId,
                            text: '<i class="iconfont icon-danye f14 mr5"></i>采购订单',
                            url: '/Uri/purchaseOrder_manage?id=' + rowData.srcId
                        });
                        break;
                    case '101102':
                        parent.tab.addTabItem({
                            tabid: 'purchase-purchaseOrder' + rowData.srcId,
                            text: '<i class="iconfont icon-danye f14 mr5"></i>采购订单',
                            url: '/Uri/purchaseOrder_manage?id=' + rowData.srcId
                        });
                        break;
                    case '101111':
                        parent.tab.addTabItem({
                            tabid: 'purchase-purchaseContract' + rowData.srcId,
                            text: '<i class="iconfont icon-danye f14 mr5"></i>特殊采购单',
                            url: '/Uri/purchaseContract_manage?id=' + rowData.srcId
                        });
                        break;
                    case '101201':
                        parent.tab.addTabItem({
                            tabid: 'sales-salesOrder' + rowData.srcId,
                            text: '<i class="iconfont icon-danye f14 mr5"></i>销售订单',
                            url: '/Uri/salesOrder_manage?id=' + rowData.srcId
                        });
                        break;
                    case '101211':
                        parent.tab.addTabItem({
                            tabid: 'sales-salesContract' + rowData.srcId,
                            text: '<i class="iconfont icon-danye f14 mr5"></i>特殊销售单',
                            url: '/Uri/salesContract_manage?id=' + rowData.srcId
                        });
                        break;
                    case '109101':
                        parent.tab.addTabItem({
                            tabid: 'deliver-deliver' + rowData.srcId,
                            text: '<i class="iconfont icon-danye f14 mr5"></i>快递月结单',
                            url: '/Uri/deliver_manage?id=' + rowData.srcId
                        });
                        break;
                    default:
                        break;
                }
            }
        });
        $('.grid-wrap').on('click', '.check-btn', function(a) {
            a.preventDefault();
            if (Business.verifyRight('RECEIPT_CHECK')) {
                var id = $(this).data('id');
                var checkId = Number($(this).data('checkid')) > 0 ? 0 : defaultPage.SYSTEM.uId;
                var checkName = Number($(this).data('checkid')) > 0 ? '' : defaultPage.SYSTEM.uName;
                var rowData = $('#grid').jqGrid('getRowData', id);
                id && Public.ajaxPost('/Bill/MonIo/check', {
                    id: id,
                    checkId: checkId,
                    checkName: checkName,
                    billNo: rowData.billNo,
                    transType: rowData.transType
                }, function(res) {
                    if (res && 200 == res.status) {
                        $.dialog.tips((checkId > 0 ? '审核' : '反审核') + '成功！');
                        $('#grid').jqGrid('setCell', id, 'checkId', checkId);
                        parent.frames['money-receipt' + id] && parent.frames['money-receipt' + id].location.reload();
                    } else {
                        $.dialog.tips((checkId > 0 ? '审核' : '反审核') + '失败！' + res.msg, 2);
                    }
                });
            }
        });
        $('.grid-wrap').on('click', '.moneyStatus-btn', function(a) {
            a.preventDefault();
            var id = $(this).data('id');
            $('#' + id).find('.ui-icon-pencil').trigger('click');
        });
        $('.grid-wrap').on('click', '.ui-icon-pencil', function(a) {
            a.preventDefault();
            var id = $(this).parent().data('id');
            var rowData = $('#grid').jqGrid('getRowData', id);
            id && rowData && parent.tab.addTabItem({
                tabid: 'money-receipt' + id,
                text: '<i class="iconfont icon-danye f14 mr5"></i>' + (rowData.miniType == 1 ? '销售收款单' : (rowData.miniType == 2 ? '采购退款单' : (rowData.miniType == 3 ? '特采退款单' : (rowData.miniType == 4 ? '快递退款单' : '业务收款单')))),
                url: '/Uri/receipt_manage?id=' + id
            });
        });
        $('.grid-wrap').on('click', '.ui-icon-trash', function(a) {
            a.preventDefault();
            if (Business.verifyRight('RECEIPT_DELETE')) {
                var id = $(this).parent().data('id');
                var rowData = $('#grid').jqGrid('getRowData', id);
                id && $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                    Public.ajaxGet('/Bill/MonIo/delete', {
                        id: id
                    }, function(res) {
                        if (res && 200 == res.status) {
                            $.dialog.tips('删除成功！');
                            $('#grid').jqGrid('delRowData', id);
                            if (rowData.srcTransType > 0 && rowData.srcId > 0) {
                                if (rowData.srcTransType == 101101 || rowData.srcTransType == 101102) {
                                    parent.frames['purchase-purchaseOrder'] && parent.frames['purchase-purchaseOrder'].$('#search').trigger('click');
                                    parent.frames['purchase-purchaseOrder' + rowData.srcId] && parent.frames['purchase-purchaseOrder' + rowData.srcId].location.reload();
                                } else if (rowData.srcTransType == 101111) {
                                    parent.frames['purchase-purchaseContract'] && parent.frames['purchase-purchaseContract'].$('#search').trigger('click');
                                    parent.frames['purchase-purchaseContract' + rowData.srcId] && parent.frames['purchase-purchaseContract' + rowData.srcId].location.reload();
                                } else if (rowData.srcTransType == 101201) {
                                    parent.frames['sales-salesOrder'] && parent.frames['sales-salesOrder'].$('#search').trigger('click');
                                    parent.frames['sales-salesOrder' + rowData.srcId] && parent.frames['sales-salesOrder' + rowData.srcId].location.reload();
                                } else if (rowData.srcTransType == 101211) {
                                    parent.frames['sales-salesContract'] && parent.frames['sales-salesContract'].$('#search').trigger('click');
                                    parent.frames['sales-salesContract' + rowData.srcId] && parent.frames['sales-salesContract' + rowData.srcId].location.reload();
                                } else if (rowData.srcTransType == 109101) {
                                    parent.frames['deliver-deliver'] && parent.frames['deliver-deliver'].$('#refresh').trigger('click');
                                    parent.frames['deliver-deliver' + rowData.srcId] && parent.frames['deliver-deliver' + rowData.srcId].location.reload();
                                }
                            }
                            parent.frames['money-receipt' + id] && parent.tab.removeTabItem('money-receipt' + id);
                        } else {
                            $.dialog.tips('删除失败！' + res.msg, 2);
                        }
                    });
                });
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
    }
};
$(function() {
    THISPAGE.init();
});
