var api = frameElement.api;
var defaultPage = Public.getDefaultPage();
var data = api.data || {};
var oper = data.oper;
var inout = data.type == 1 ? '付' : '收';
var amountPlaces = Number(defaultPage.SYSTEM.amountPlaces);
var THISPAGE = {
    init: function() {
        THISPAGE.initBtns();
        THISPAGE.initGrid();
        THISPAGE.initEvent();
    },
    initBtns: function() {
        if ('view' == oper) {
            api.button({
                id: 'cancel',
                name: '关闭'
            });
        }
    },
    initGrid: function() {
        $('#grid').jqGrid({
            url: '/Bill/InvPc/singleSearchMoneyInfo',
            postData: {type: data.type, srcId: data.srcId, zid: data.zid},
            datatype: 'json',
            colModel: [{
                name: 'zid',
                label: '自编序号',
                width: 60,
                fixed: !0,
                title: !0,
                editable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'amount',
                label: inout + '款金额',
                width: 70,
                fixed: !0,
                editable: !0,
                formatter: 'currency',
                formatoptions: {
                    showZero: !0,
                    decimalPlaces: amountPlaces
                }
            }, {
                name: 'typeName',
                label: inout + '款类别',
                width: 120,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'accName',
                label: inout + '款账户',
                width: 120,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'wayName',
                label: inout + '款方式',
                width: 66,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'settlementTime',
                label: inout + '款时间',
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'uName',
                label: inout + '款人',
                width: 44,
                fixed: !0,
                editable: !1,
                classes: 'gray'
            }, {
                name: 'description',
                label: '备注',
                width: 50,
                title: !0,
                editable: !0,
                classes: 'ui-ellipsis gray'
            }, {
                name: 'moneyType',
                label: '标签',
                width: 100,
                fixed: !0,
                title: !0,
                sortable: !0,
                formatter: function(a, b, c) {
                    var tag = '';
                    if (c.transType == 103101) {
                        if (a == 1) {
                            tag = '<span class="db tc lh18 white bg-rgba3 b-r-3 def">预退款转实退款</span>';
                        } else if (a == 2) {
                            tag = '<span class="db tc lh18 white bg-aqua b-r-3 def">返现退款</span>';
                        } else if (a == 9) {
                            tag = '<span class="db tc lh18 white bg-red b-r-3 def">预退款</span>';
                        }
                    } else if (c.transType == 103102) {
                        if (a == 1) {
                            tag = '<span class="db tc lh18 white bg-gold b-r-3 def">额外付款</span>';
                        }
                    }
                    return tag;
                }
            }, {
                name: 'id',
                label: 'ID',
                hidden: !0
            }, {
                name: 'refundId',
                label: '退款编号',
                hidden: !0
            }, {
                name: 'typeId',
                label: '收款类别ID',
                hidden: !0
            }, {
                name: 'accId',
                label: '收款账户ID',
                hidden: !0
            }, {
                name: 'wayId',
                label: '收款方式ID',
                hidden: !0
            }, {
                name: 'uId',
                label: '收款人ID',
                hidden: !0
            }],
            cmTemplate: {sortable: !1, title: !1},
            jsonReader: {root: 'data.rows', records: 'data.records', total: 'data.total', repeatitems: !1, id: 'id'},
            width: 1248,
            height: 358,
            rownumbers: !0,
            gridview: !0,
            onselectrow: !1,
            shrinkToFit: !0,
            forceFit: !1,
            rowNum: 1000,
            cellEdit: !1,
            footerrow: !0,
            userDataOnFooter: !0,
            userData: {
                rn: '合计',
                amount: 0
            },
            loadComplete: function(data) {
                data.status == -1 && $.dialog.tips(data.msg, 2);
            },
            loadError: function(a, b) {
                $.dialog.tips('Type: ' + b + '; Response: ' + a.status + ' ' + a.statusText, 2);
            },
            gridComplete: function() {
                THISPAGE.calTotal();
            }
        });
    },
    initEvent: function() {
        $('#refresh').on('click', function() {
            window.location.reload();
        });
    },
    calTotal: function() {
        for (var b = $('#grid').jqGrid('getDataIDs'), c = 0, d = 0, e = b.length; e > d; d++) {
            var f = b[d];
            var g = $('#grid').jqGrid('getRowData', f);
            g.amount && (c += parseFloat(g.amount));
        }
        $('#grid').jqGrid('footerData', 'set', {
            rn: '合计',
            amount: c
        });
    }
};
$(function() {
    THISPAGE.init();
});
