var defaultPage = Public.getDefaultPage();
var qtyPlaces = defaultPage.SYSTEM.qtyPlaces;
var pricePlaces = defaultPage.SYSTEM.pricePlaces;
var amountPlaces = defaultPage.SYSTEM.amountPlaces;
var queryConditions = {
    uId: -1,
    skey: ''
};
var combo1 = $('#combo1').combo({
    data: '/Base/User/queryAll?normal=yes',
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
        THISPAGE.initGrid();
        THISPAGE.initEvent();
    },
    initGrid: function() {
        var a = Public.setGrid();
        $('#grid').jqGrid({
            url: '/Bill/Report/purchaseContractHis',
            postData: queryConditions,
            datatype: 'json',
            colModel: [{
                name: 'billDate',
                label: '<span class="ml10">采购日期</span>',
                width: 90,
                fixed: !0,
                editable: !1,
                classes: 'ti10 gray'
            }, {
                name: 'name',
                label: '<span class="ml10">产品名称</span>',
                width: 100,
                title: !0,
                editable: !1,
                classes: 'ti10 ui-ellipsis'
            }, {
                name: 'spec',
                label: '规格型号',
                width: 100,
                title: !0,
                editable: !1,
                classes: 'ui-ellipsis'
            }, {
                name: 'sku',
                label: '辅助属性',
                width: 100,
                title: !0,
                editable: !1,
                classes: 'ui-ellipsis'
            }, {
                name: 'brand',
                label: '产品品牌',
                width: 100,
                fixed: !0,
                title: !0,
                editable: !1,
                classes: 'ui-ellipsis'
            }, {
                name: 'unit',
                label: '单位',
                width: 60,
                fixed: !0,
                editable: !1,
                classes: 'ui-ellipsis brd'
            }, {
                name: 'isHasExpress',
                label: '包邮',
                width: 34,
                fixed: !0,
                sortable: !0,
                editable: !1,
                formatter: function(a, b, c) {
                    return a == 1 ? '包邮' : '';
                }
            }, {
                name: 'isHasInvoice',
                label: '带票',
                width: 34,
                fixed: !0,
                sortable: !0,
                editable: !1,
                formatter: function(a, b, c) {
                    return a == 1 ? '带票' : '';
                }
            }, {
                name: 'buyUrl',
                label: '参考链接',
                width: 100,
                fixed: !0,
                title: !0,
                editable: !1,
                classes: 'ui-ellipsis',
                formatter: function(x, y, z) {
                    return (x && x.indexOf('http') === 0) ? '<a href="' + x + '" target="_blank">' + x +'</a>' : x;
                }
            }, {
                name: 'myUrl',
                label: '采购链接',
                width: 100,
                fixed: !0,
                title: !0,
                editable: !1,
                classes: 'ui-ellipsis',
                formatter: function(x, y, z) {
                    return (x && x.indexOf('http') === 0) ? '<a href="' + x + '" target="_blank">' + x +'</a>' : x;
                }
            }, {
                name: 'description1',
                label: '备注1',
                width: 100,
                fixed: !0,
                title: !0,
                editable: !1,
                classes: 'ui-ellipsis'
            }, {
                name: 'description2',
                label: '备注2',
                width: 100,
                fixed: !0,
                title: !0,
                editable: !1,
                classes: 'ui-ellipsis'
            }, {
                name: 'price',
                label: '采购单价',
                width: 100,
                fixed: !0,
                sortable: !0,
                editable: !1,
                classes: 'f14',
                formatter: 'currency',
                formatoptions: {
                    showZero: !1,
                    decimalPlaces: pricePlaces
                }
            }, {
                name: 'id',
                label: 'ID',
                width: 0,
                fixed: !0,
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
            loadComplete: function(data) {
                data.status == -1 && $.dialog.tips(data.msg, 2);
            },
            loadError: function(a, b) {
                $.dialog.tips('Type: ' + b + '; Response: ' + a.status + ' ' + a.statusText, 2);
            }
        });
    },
    initEvent: function() {
        $('#search').click(function() {
            queryConditions.uId = combo1.getValue();
            queryConditions.skey = $.trim($('#skey').val()).split(/[ ,，]+/).toString();
            $('#grid').jqGrid('setGridParam', {
                postData: queryConditions,
                page: 1
            }).trigger('reloadGrid');
        });
        $('#refresh').on('click', function() {
            window.location.reload();
        });
        $(window).on('keydown', function(a) {
            var curKey = a.which;
            if (curKey == 13) {
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
