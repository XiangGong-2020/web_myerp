var defaultPage = Public.getDefaultPage();
var api = frameElement.api;
var qtyPlaces = defaultPage.SYSTEM.qtyPlaces;
var pricePlaces = defaultPage.SYSTEM.pricePlaces;
var amountPlaces = defaultPage.SYSTEM.amountPlaces;
var queryConditions = {
    id: api.data.id,
    uId: 0,
    skey: ''
};
var combo1 = $('#combo1').combo({
    data: '/Bill/InvPc/getQuoteUser?id=' + queryConditions.id,
    ajaxOptions: {
        formatData: function(res) {
            return res.data.unshift({
                id: -1,
                name: '全部报价人'
            }), res.data;
        }
    },
    value: 'id',
    text: 'name',
    defaultSelected: 0,
    width: 90,
    callback: {
        onListClick: function() {
            $('#search').trigger('click');
        }
    }
}).getCombo();
var THISPAGE = {
    init: function() {
        THISPAGE.initBtns();
        THISPAGE.initGrid();
        THISPAGE.initEvent();
    },
    initBtns: function() {
        api.button({
            id: 'cancel',
            name: '关闭'
        });
    },
    initGrid: function(a) {
        $('#grid').jqGrid({
            url: '/Bill/InvPc/getQuoteInfo',
            postData: queryConditions,
            datatype: 'json',
            colModel: [{
                name: 'id',
                label: 'ID',
                width: 0,
                fixed: !0,
                hidden: !0
            }, {
                name: 'zid',
                label: '自编序号',
                width: 66,
                fixed: !0,
                title: !0,
                editable: !1,
                classes: 'gray ui-ellipsis'
            }, {
                name: 'name',
                label: '产品名称',
                width: 100,
                title: !0,
                editable: !1,
                classes: 'gray ui-ellipsis'
            }, {
                name: 'spec',
                label: '规格型号',
                width: 100,
                title: !0,
                editable: !1,
                classes: 'gray ui-ellipsis'
            }, {
                name: 'sku',
                label: '辅助属性',
                width: 100,
                title: !0,
                editable: !1,
                classes: 'gray ui-ellipsis'
            }, {
                name: 'brand',
                label: '产品品牌',
                width: 60,
                fixed: !0,
                title: !0,
                editable: !1,
                classes: 'gray ui-ellipsis'
            }, {
                name: 'unit',
                label: '单位',
                width: 34,
                fixed: !0,
                editable: !1,
                classes: 'gray ui-ellipsis'
            }, {
                name: 'qty',
                label: '数量',
                width: 60,
                fixed: !0,
                editable: !1,
                classes: 'gray',
                formatter: function(a, b, c) {
                    return Number(a);
                }
            }, {
                name: 'limitPrice',
                label: '参考单价',
                width: 100,
                fixed: !0,
                hidden: ($.inArray('SYS_ADMIN', defaultPage.SYSTEM.rights) !== -1) ? !1 : !0,
                sortable: !0,
                editable: !1,
                classes: 'gray',
                formatter: 'currency',
                formatoptions: {
                    showZero: !1,
                    decimalPlaces: pricePlaces
                }
            }, {
                name: 'price',
                label: '所报单价',
                width: 100,
                fixed: !0,
                sortable: !0,
                editable: !1,
                formatter: 'currency',
                formatoptions: {
                    showZero: !1,
                    decimalPlaces: pricePlaces
                }
            }, {
                name: 'amount',
                label: '所报总价',
                width: 70,
                fixed: !0,
                sortable: !0,
                editable: !1,
                formatter: 'currency',
                formatoptions: {
                    showZero: !1,
                    decimalPlaces: amountPlaces
                }
            }, {
                name: 'saleAmount',
                label: '销售总价',
                width: 70,
                fixed: !0,
                hidden: ($.inArray('SYS_ADMIN', defaultPage.SYSTEM.rights) !== -1) ? !1 : !0,
                editable: !1,
                classes: 'gray',
                formatter: function(a, b, c) {
                    return c.limitPrice ? Number(c.limitPrice * 1.25 * c.qty).toFixed(amountPlaces) : (a || '');
                }
            }, {
                name: 'buyUrl',
                label: '参考链接',
                width: 100,
                title: !0,
                editable: !1,
                classes: 'ui-ellipsis',
                formatter: function(x, y, z) {
                    return (x && x.indexOf('http') === 0) ? '<a href="' + x + '" target="_blank">' + x +'</a>' : x;
                }
            }, {
                name: 'myUrl',
                label: '购买链接',
                width: 100,
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
                title: !0,
                editable: !1,
                classes: 'ui-ellipsis'
            }, {
                name: 'description2',
                label: '备注2',
                width: 100,
                title: !0,
                editable: !1,
                classes: 'ui-ellipsis'
            }, {
                name: 'uName',
                label: '报价人',
                width: 50,
                fixed: !0,
                title: !0,
                editable: !1
            }, {
                name: 'firstTime',
                label: '最初报价时间',
                width: 124,
                fixed: !0,
                title: !0,
                sortable: !0,
                editable: !1
            }, {
                name: 'lastTime',
                label: '最后报价时间',
                width: 124,
                fixed: !0,
                title: !0,
                sortable: !0,
                editable: !1
            }],
            cmTemplate: {sortable: !1, title: !1},
            jsonReader: {root: 'data.rows', records: 'data.records', total: 'data.total', repeatitems: !1, id: 'id'},
            width: $(window).width(),
            height: $(window).height() - 186,
            altRows: !1,
            gridview: !0,
            rownumbers: !0,
            rownumWidth: 40,
            onselectrow: !1,
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
            footerrow: !0,
            userDataOnFooter: !0,
            loadComplete: function(data) {
                data.status == -1 && $.dialog.tips(data.msg, 2);
                THISPAGE.calTotal();
                if (queryConditions.id > 0) {
                    var quoteInfo = '共 ' + data.data.zids.length + ' 种产品，已报价 ' + data.data.quoteZids.length + ' 种产品，还剩 ' + data.data.nonQuoteZids.length + ' 种产品未报价，未报价产品自编序号为：' + data.data.nonQuoteZids.toString();
                } else {
                    var quoteInfo = '此处显示所有特采单的已公开（已转正）报价历史记录。';
                }
                $('#totalInfo').html(quoteInfo).attr('title', quoteInfo);
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
        $(window).on('resize', function() {
            setTimeout(function() {
                $("#grid").jqGrid('setGridWidth', $(window).width());
                $("#grid").jqGrid('setGridHeight', $(window).height() - 186);
            }, 100)
        });
    },
    calTotal: function() {
        for (var a = $('#grid').jqGrid('getDataIDs'), totalQty = 0, totalAmount = 0, totalSaleAmount = 0, d = 0, e = a.length; e > d; d++) {
            var f = a[d];
            var g = $('#grid').jqGrid('getRowData', f);
            g.qty && (totalQty += parseFloat(g.qty));
            g.amount && (totalAmount += parseFloat(g.amount));
            g.saleAmount && (totalSaleAmount += parseFloat(g.saleAmount));
        }
        $('#grid').jqGrid('footerData', 'set', {
            rn: '合计',
            qty: Number(totalQty).toFixed(0),
            amount: Number(totalAmount).toFixed(2),
            saleAmount: Number(totalSaleAmount).toFixed(2)
        });
    }
};
$(function() {
    THISPAGE.init();
});
