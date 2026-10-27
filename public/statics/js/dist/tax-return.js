var urlParam = Public.urlParam();
var defaultPage = Public.getDefaultPage();
var amountPlaces = Number(defaultPage.SYSTEM.amountPlaces);
var queryConditions = {
    year: (new Date).format('yyyy'),
    date: (new Date).format('M')
};
var yearCombo = $('#yearCombo').combo({
    data: [
        {id: 0, name: '2024年'},
        {id: 1, name: '2025年'},
        {id: 2, name: '2026年'},
        {id: 3, name: '2027年'},
        {id: 4, name: '2028年'},
        {id: 5, name: '2029年'},
        {id: 6, name: '2030年'},
        {id: 7, name: '2031年'},
        {id: 8, name: '2032年'},
        {id: 9, name: '2033年'},
        {id: 10, name: '2034年'},
        {id: 11, name: '2035年'},
        {id: 12, name: '2036年'},
        {id: 13, name: '2037年'},
        {id: 14, name: '2038年'},
        {id: 15, name: '2039年'},
        {id: 16, name: '2040年'},
        {id: 17, name: '2041年'},
        {id: 18, name: '2042年'},
        {id: 19, name: '2043年'},
        {id: 20, name: '2044年'},
        {id: 21, name: '2045年'},
        {id: 22, name: '2046年'},
        {id: 23, name: '2047年'},
        {id: 24, name: '2048年'},
        {id: 25, name: '2049年'},
        {id: 26, name: '2050年'},
        {id: 27, name: '2051年'},
        {id: 28, name: '2052年'},
        {id: 29, name: '2053年'},
        {id: 30, name: '2054年'},
        {id: 31, name: '2055年'},
        {id: 32, name: '2056年'},
        {id: 33, name: '2057年'},
        {id: 34, name: '2058年'},
        {id: 35, name: '2059年'},
        {id: 36, name: '2060年'}
    ],
    value: 'id',
    text: 'name',
    defaultSelected: ['name', (new Date).format('yyyy') + '年'],
    width: 78,
    listHeight: 500,
    callback: {
        onListClick: function() {
            $('#search').trigger('click');
        }
    }
}).getCombo();
var dateCombo = $('#dateCombo').combo({
    data: [
        {id: 1, name: '1月'},
        {id: 2, name: '2月'},
        {id: 3, name: '3月'},
        {id: 4, name: '4月'},
        {id: 5, name: '5月'},
        {id: 6, name: '6月'},
        {id: 7, name: '7月'},
        {id: 8, name: '8月'},
        {id: 9, name: '9月'},
        {id: 10, name: '10月'},
        {id: 11, name: '11月'},
        {id: 12, name: '12月'},
        {id: 13, name: '一季度'},
        {id: 14, name: '二季度'},
        {id: 15, name: '三季度'},
        {id: 16, name: '四季度'},
        {id: 17, name: '全年'}
    ],
    value: 'id',
    text: 'name',
    width: 78,
    listHeight: 550,
    defaultSelected: ['name', (new Date).format('M') + '月'],
    cache: !1,
    editable: !1,
    trigger: !0,
    emptyOptions: !1,
    callback: {
        onListClick: function() {
            $('#search').trigger('click');
        }
    },
    queryDelay: 0
}).getCombo();
var THISPAGE = {
    init: function() {
        THISPAGE.initGrid();
        THISPAGE.initEvent();
    },
    initGrid: function() {
        var a = Public.setGrid(135, 0);
        $('#grid').jqGrid({
            url: '/Bill/Report/taxReturn',
            postData: queryConditions,
            datatype: 'json',
            colModel: [{
                name: 'name',
                label: '<span class="ml10">主体简称</span>',
                width: 100,
                fixed: !0,
                sortable: !1,
                classes: 'ti10 ui-ellipsis'
            }, {
                name: 'no',
                label: '纳税人识别号',
                width: 180,
                fixed: !0,
                sortable: !1
            }, {
                name: 'dateFrom',
                label: '起始日期',
                width: 80,
                fixed: !0,
                sortable: !1,
                classes: 'gray'
            }, {
                name: 'dateTo',
                label: '截至日期',
                width: 80,
                fixed: !0,
                sortable: !1,
                classes: 'gray'
            }, {
                name: 'billFrom',
                label: '销售渠道',
                width: 80,
                title: !0,
                sortable: !1,
                classes: 'ui-ellipsis'
            }, {
                name: '',
                label: '',
                width: 10,
                fixed: !0,
                sortable: !1
            }, {
                name: '',
                label: '',
                width: 10,
                fixed: !0,
                sortable: !1,
                classes: 'ble'
            }, {
                name: 'onlineOrderInvoiceAmount',
                label: '网店开票金额',
                width: 100,
                fixed: !0,
                sortable: !1,
                formatter: 'currency',
                formatoptions: {
                    showZero: !1,
                    decimalPlaces: amountPlaces
                }
            }, {
                name: 'onlineOrderAmount',
                label: '网店总收入',
                width: 100,
                fixed: !0,
                sortable: !1,
                formatter: 'currency',
                formatoptions: {
                    showZero: !1,
                    decimalPlaces: amountPlaces
                }
            }, {
                name: 'notOnlineOrderInvoiceAmount',
                label: '其他开票收入',
                width: 100,
                fixed: !0,
                sortable: !1,
                formatter: 'currency',
                formatoptions: {
                    showZero: !1,
                    decimalPlaces: amountPlaces
                }
            }, {
                name: 'notOnlineOrderNoInvoiceAmount',
                label: '其他未开票收入',
                width: 100,
                fixed: !0,
                sortable: !1,
                formatter: 'currency',
                formatoptions: {
                    showZero: !1,
                    decimalPlaces: amountPlaces
                }
            }, {
                name: 'notOnlineOrderAmount',
                label: '其他总收入',
                width: 100,
                fixed: !0,
                sortable: !1,
                formatter: 'currency',
                formatoptions: {
                    showZero: !1,
                    decimalPlaces: amountPlaces
                }
            }, {
                name: '',
                label: '',
                width: 10,
                fixed: !0,
                sortable: !1,
                classes: 'ble'
            }, {
                name: 'totalAmount',
                label: '含税销售收入',
                width: 140,
                fixed: !0,
                sortable: !1,
                classes: 'f14 fb',
                formatter: 'currency',
                formatoptions: {
                    showZero: !1,
                    decimalPlaces: amountPlaces
                }
            }, {
                name: '',
                label: '',
                width: 10,
                fixed: !0,
                sortable: !1,
                classes: 'ble'
            }, {
                name: 'returnAmount',
                label: '申报收入',
                width: 140,
                fixed: !0,
                sortable: !1,
                classes: 'f14 fb',
                formatter: 'currency',
                formatoptions: {
                    showZero: !1,
                    decimalPlaces: amountPlaces
                }
            }],
            cmTemplate: {sortable: !1, title: !1},
            jsonReader: {root: 'data.rows', records: 'data.records', total: 'data.total', repeatitems: !1, id: 'id'},
            width: a.w,
            height: a.h,
            altRows: !1,
            gridview: !0,
            rownumbers: !1,
            multiselect: !1,
            shrinkToFit: !0,
            forceFit: !1,
            viewsortcols: [true, 'vertical', true],
            pager: '#page',
            page: 1,
            rowNum: 1000,
            viewrecords: !0,
            footerrow: !0,
            userDataOnFooter: !0,
            loadComplete: function(data) {
                THISPAGE.calTotal();
            },
            loadError: function(a, b) {
                $.dialog.tips('Type: ' + b + '; Response: ' + a.status + ' ' + a.statusText, 2);
            }
        });
    },
    initEvent: function() {
        $('#search').on('click', function() {
            if (Business.verifyRight('TAX_RETURN_QUERY')) {
                queryConditions.year = yearCombo.getText().replace('年', '');
                queryConditions.date = dateCombo.getValue();
                $('#grid').jqGrid('setGridParam', {
                    postData: queryConditions,
                    page: 1
                }).trigger('reloadGrid');
            }
        });
        $('#refresh').on('click', function() {
            window.location.reload();
        });
        $(window).on('resize', function() {
            Public.resizeGrid(135, 0);
        });
    },
    calTotal: function() {
        for (var a = $('#grid').jqGrid('getDataIDs'), b = 0, c = 0, d = 0, e = 0, f = 0, g = a.length; g > f; f++) {
            var h = a[f];
            var rowData = $('#grid').jqGrid('getRowData', h);
            rowData.onlineOrderAmount && (b += parseFloat(rowData.onlineOrderAmount));
            rowData.notOnlineOrderInvoiceOutAmount && (c += parseFloat(rowData.notOnlineOrderInvoiceOutAmount));
            rowData.totalAmount && (d += parseFloat(rowData.totalAmount));
        }
        $('#grid').jqGrid('footerData', 'set', {
            billFrom: '<span class="fr">合计：</span>',
            onlineOrderAmount: b,
            notOnlineOrderInvoiceOutAmount: c,
            totalAmount: d
        });
        var j = (e - Number($('#disAmount').val()) + Number($('#deliverAmount').val())).toFixed(2);
        $('#amount').val(j);
    }
};
$(function() {
    THISPAGE.init();
});
