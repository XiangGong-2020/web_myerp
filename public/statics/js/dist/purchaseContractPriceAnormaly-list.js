var urlParam = Public.urlParam();
var defaultPage = Public.getDefaultPage();
var qtyPlaces = Number(defaultPage.SYSTEM.qtyPlaces);
var pricePlaces = Number(defaultPage.SYSTEM.pricePlaces);
var amountPlaces = Number(defaultPage.SYSTEM.amountPlaces);
var queryConditions = {
    status1: -1,
    status2: 0,
    status3: 0,
    beginDate: '',
    endDate: urlParam.endDate ? (urlParam.endDate + ' 23:59:59') : '',
    skey: '',
    unDone: urlParam.unDone || 0
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
var combo2 = $('#combo2').combo({
    data: [
        {id: 0, name: '总价0以上'},
        {id: 1, name: '总价1000以上'},
        {id: 2, name: '总价10000以上'}
    ],
    value: 'id',
    text: 'name',
    defaultSelected: 0,
    width: 120,
    listHeight: 500,
    callback: {
        onListClick: function() {
            $('#search').trigger('click');
        }
    }
}).getCombo();
var combo3 = $('#combo3').combo({
    data: [
        {id: 0, name: '价差3倍以上'},
        {id: 1, name: '价差4倍以上'},
        {id: 2, name: '价差5倍以上'},
        {id: 3, name: '价差6倍以上'},
        {id: 4, name: '价差7倍以上'},
        {id: 5, name: '价差8倍以上'},
        {id: 6, name: '价差9倍以上'},
        {id: 7, name: '价差10倍以上'}
    ],
    value: 'id',
    text: 'name',
    defaultSelected: 0,
    width: 120,
    listHeight: 500,
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
        $('#beginDate').val(defaultPage.SYSTEM.beginDate + ' 00:00:00');
        $('#endDate').val((urlParam.endDate || defaultPage.SYSTEM.endDate) + ' 23:59:59');
    },
    initGrid: function() {
        var a = Public.setGrid();
        $('#grid').jqGrid({
            url: '/Bill/InvPc/listPurPriceAnormaly',
            postData: queryConditions,
            datatype: 'json',
            colModel: [{
                name: 'billNo',
                label: '<span class="ml10">特采单号</span>',
                width: 160,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ti10 ui-ellipsis hover'
            }, {
                name: 'zid',
                label: '自编序号',
                width: 56,
                fixed: !0,
                title: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'name',
                label: '产品名称',
                width: 100,
                title: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'spec',
                label: '规格型号',
                width: 100,
                title: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'sku',
                label: '辅助属性',
                width: 100,
                title: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'brand',
                label: '品牌',
                width: 60,
                fixed: !0,
                title: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'unit',
                label: '单位',
                width: 40,
                fixed: !0,
                title: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'qty',
                label: '数量',
                width: 54,
                fixed: !0
            }, {
                name: 'limitPrice',
                label: '参考单价',
                width: 0,
                hidden: !0
            }, {
                name: 'price',
                label: '采购单价',
                width: 0,
                hidden: !0
            }, {
                name: 'uName',
                label: '采购人',
                width: 60,
                fixed: !0,
                classes: 'night'
            }, {
                name: 'saleAmount',
                label: '销售总价',
                width: 80,
                fixed: !0,
                classes: 'gray ble'
            }, {
                name: 'paidAmount',
                label: '已付采购总价',
                width: 100,
                fixed: !0,
                title: !0,
                classes: 'fb red ble',
                cellattr: function(rowId, val, rawObject, cm, rdata) {
                    if (val > rdata.saleAmount) {
                        return 'class="f14 fb red"';
                    } else {
                        return 'class="f14 fb teal"';
                    }
                }
            }, {
                name: '',
                label: '',
                width: 100,
                fixed: !0,
                title: !0,
                formatter: function(a, b, c) {
                    if (c.paidAmount > c.saleAmount) {
                        var x = '高';
                        var y = parseInt(c.paidAmount / c.saleAmount);
                        var z = 'red';
                    } else {
                        var x = '低';
                        var y = parseInt(c.saleAmount / c.paidAmount);
                        var z = 'teal';
                    }
                    return '<span class="db ' + z + '">' + x + ' <span class="f14 fb">' + y + '</span> 倍以上</span>'
                }
            }, {
                name: 'oprate',
                label: '操作',
                width: 200,
                fixed: !0,
                align: 'center',
                classes: 'ui-ellipsis ble'
            }, {
                name: 'id',
                label: 'ID',
                width: 0,
                hidden: !0,
                editable: !1
            }, {
                name: 'iid',
                label: 'ID',
                width: 0,
                hidden: !0,
                editable: !1
            }, {
                name: 'uId',
                label: 'uId',
                width: 0,
                hidden: !0,
                editable: !1
            }, {
                name: 'transType',
                label: 'transType',
                width: 0,
                hidden: !0,
                editable: !1
            }],
            cmTemplate: {sortable: !1, title: !1},
            jsonReader: {root: 'data.rows', records: 'data.records', total: 'data.total', repeatitems: !1, id: 'id'},
            width: a.w,
            height: a.h,
            altRows: !1,
            gridview: !0,
            multiselect: !1,
            multiboxonly: !0,
            shrinkToFit: !0,
            forceFit: !1,
            viewsortcols: [true, 'vertical', true],
            sortname: 'paidTime',
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
            },
            onCellSelect: function(c, d) {
                var rowData = $('#grid').jqGrid('getRowData', c);
                if (d == 0) {
                    if (Business.verifyRight('PC_QUERY')) {
                        var billNo = rowData['billNo'];
                        billNo && parent.tab.addTabItem({
                            tabid: 'purchase-purchaseContractList',
                            text: '<i class="iconfont icon-danju f14 mr5"></i>特殊采购',
                            url: '/Uri/purchaseContract_list?skey=' + billNo
                        });
                    }
                }
            }
        });
    },
    initEvent: function() {
        laydate.render({
            elem: "#datePicker",
            type: 'datetime',
            range: ['#beginDate', '#endDate'],
            done: function() {
                setTimeout(function() {
                    $('#search').trigger('click');
                }, 300);
            }
        });
        $('#search').on('click', function() {
            queryConditions.status1 = combo1.getValue();
            queryConditions.status2 = combo2.getValue();
            queryConditions.status3 = combo3.getValue();
            queryConditions.beginDate = $('#beginDate').val();
            queryConditions.endDate = $('#endDate').val();
            queryConditions.skey = $.trim($('#skey').val()).split(/[ ,，]+/).toString();
            $('#grid').jqGrid('setGridParam', {
                postData: queryConditions,
                page: 1
            }).trigger('reloadGrid');
        });
        $('#refresh').on('click', function() {
            window.location.reload();
        });
        $('.grid-wrap').on('click', '.status-btn', function(a) {
            a.preventDefault();
            if (Business.verifyRight('SA_SPIDER_ERR_ADVANCE')) {
                var id = $(this).data('id');
                var status = Number(!$(this).data('status'));
                id && Public.ajaxPost('/Base/SalesOrderSpiderErr/status', {
                    id: id,
                    status: status
                }, function(res) {
                    if (res && 200 == res.status) {
                        $.dialog.tips('处理状态标记成功！');
                        $('#grid').jqGrid('setRowData', id, res.data);
                    } else {
                        $.dialog.tips('处理状态标记失败！' + res.msg, 2);
                    }
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
