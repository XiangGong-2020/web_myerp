var defaultPage = Public.getDefaultPage();
var pricePlaces = Number(defaultPage.SYSTEM.pricePlaces);
var queryConditions = {
    goodsId: ''
};
var THISPAGE = {
    init: function() {
        THISPAGE.initGrid();
        THISPAGE.initEvent();
    },
    initGrid: function() {
        var a = Public.setGrid();
        $('#grid').jqGrid({
            url: '#',
            datatype: 'clientSide',
            postData: queryConditions,
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
                name: 'contactName',
                label: '商家',
                width: 200,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis hover'
            }, {
                name: 'billNo',
                label: '采购订单号',
                width: 160,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis hover'
            }, {
                name: 'billDate',
                label: '采购时间',
                width: 80,
                fixed: !0,
                sortable: !0,
                classes: 'gray'
            }, {
                name: 'price',
                label: '采购成本',
                width: 120,
                fixed: !0,
                sortable: !0
            }, {
                name: 'contactId',
                label: '商家ID',
                width: 0,
                hidden: !0
            }, {
                name: 'iid',
                label: '采购订单ID',
                width: 0,
                hidden: !0
            }],
            cmTemplate: {sortable: !1, title: !1},
            jsonReader: {root: 'data.rows', records: 'data.records', total: 'data.total', repeatitems: !1, id: 'id'},
            width: a.w,
            height: a.h,
            altRows: !1,
            gridview: !0,
            shrinkToFit: !0,
            forceFit: !1,
            viewsortcols: [true, 'vertical', true],
            sortname: 'goodsId',
            sortorder: 'DESC',
            pager: '#page',
            page: 1,
            rowNum: 100,
            rowList: [100, 200, 500],
            viewrecords: !0,
            loadComplete: function(data) {
                data.status == -1 && $.dialog.tips(data.msg, 2);
                if (data && 200 == data.status) {
                    $('#minCost').html(Number(data.data.minCost).toFixed(5) + ' 元 / 件');
                    $('#maxCost').html(Number(data.data.maxCost).toFixed(5) + ' 元 / 件');
                    $('#averageCost').html(Number(data.data.averageCost).toFixed(5) + ' 元 / 件');
                }
            },
            loadError: function(a, b) {
                $.dialog.tips('Type: ' + b + '; Response: ' + a.status + ' ' + a.statusText, 2);
            },
            onCellSelect: function(a, b) {
                var rowData = $('#grid').jqGrid('getRowData', a);
                if (b == 3 && rowData.contactId > 0) {
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
                if (b == 4 && rowData.iid > 0) {
                    parent.tab.addTabItem({
                        tabid: 'purchase-purchaseOrder',
                        text: '<i class="iconfont icon-danye f14 mr5"></i>采购订单',
                        url: '/Uri/purchaseOrder_manage?id=' + rowData.iid
                    });
                }
            }
        });
    },
    initEvent: function() {
        Business.filterGoods(!0);
        $('#search').on('click', function() {
            if (Business.verifyRight('PURCHASE_COSTSEARCH')) {
                queryConditions.goodsId = $('#filter-goods input').val() || '';
                $('#grid').jqGrid('setGridParam', {
                    url: '/Bill/Report/purchaseCostSearch',
                    postData: queryConditions,
                    datatype: 'json',
                    page: 1
                }).trigger('reloadGrid');
            }
        });
        $(window).on('keydown', function(a) {
            var curKey = a.which;
            if (curKey == 13) {
                $('#search').trigger('click');
            }
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
