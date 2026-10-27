var api = frameElement.api;
var cRowId = api.data.rowId;
var callback = api.data.callback;
var rowData = {};
var defaultPage = Public.getDefaultPage();
var qtyPlaces = defaultPage.SYSTEM.qtyPlaces;
var pricePlaces = defaultPage.SYSTEM.pricePlaces;
var amountPlaces = defaultPage.SYSTEM.amountPlaces;
var curRow;
var curCol;
var THISPAGE = {
    init: function() {
        if (void 0 !== cRowId) {
            Public.ajaxPost('/Bill/InvBq/queryQuote', {
                id: cRowId
            }, function(res) {
                if (res && 200 == res.status) {
                    rowData = res.data;
                    THISPAGE.initBtns();
                    THISPAGE.initDom(rowData);
                    THISPAGE.initGrid(rowData);
                    THISPAGE.initEvent();
                } else {
                    $.dialog.tips(res.msg, 2);
                }
            });
        } else {
            THISPAGE.initBtns();
            THISPAGE.initDom();
            THISPAGE.initGrid();
            THISPAGE.initEvent();
        }
    },
    initBtns: function() {
        api.button({
            id: 'confirm',
            name: '提交并关闭',
            defClass: 'ui_state_highlight',
            callback: function() {
                return THISPAGE.cancleGridEdit(), THISPAGE.postData(), !1;
            }
        }, {
            id: 'cancel',
            name: '关闭',
            callback: function() {
                return THISPAGE.upQuoteName(), !1;
            }
        });
    },
    initDom: function(a) {
        a.data.description && $('#notice').append('<p title="' + a.data.description + '">' + a.data.description + '</p>');
        a.data.files && THISPAGE.addFiles(a.data.files);
        var quoteInfo = '共 ' + a.zids.length + ' 种产品，已报价 ' + a.quoteZids.length + ' 种产品，还剩 ' + a.nonQuoteZids.length + ' 种产品未报价，未报价产品自编序号为：' + a.nonQuoteZids.toString();
        $('#totalInfo').html(quoteInfo).attr('title', quoteInfo);
    },
    initGrid: function(a) {
        $('#grid').jqGrid({
            data: a.entries,
            datatype: 'clientSide',
            colModel: [{
                name: 'zid',
                label: '自编序号',
                width: 56,
                fixed: !0,
                editable: !1,
                classes: 'break',
                formatter: function(a, b) {
                    return a ? a : b.rowId;
                }
            }, {
                name: 'no',
                label: '产品编码',
                width: 100,
                title: !0,
                editable: !1,
                classes: 'break'
            }, {
                name: 'name',
                label: '产品名称',
                width: 100,
                title: !0,
                editable: !1,
                classes: 'break'
            }, {
                name: 'spec',
                label: '规格型号',
                width: 100,
                title: !0,
                editable: !1,
                classes: 'break'
            }, {
                name: 'sku',
                label: '辅助属性',
                width: 100,
                title: !0,
                editable: !1,
                classes: 'break'
            }, {
                name: 'brand',
                label: '产品品牌',
                width: 80,
                fixed: !0,
                title: !0,
                editable: !1,
                classes: 'break'
            }, {
                name: 'qty',
                label: '<span class="fr">数量</span>',
                width: 60,
                fixed: !0,
                editable: !1,
                classes: 'f14 fb break',
                align: 'right',
                formatter: function(a, b, c) {
                    return Number(a);
                }
            }, {
                name: 'unit',
                label: '单位',
                width: 60,
                fixed: !0,
                editable: !1,
                classes: 'night break bre'
            }, {
                name: 'price',
                label: '我的报价单价',
                width: 100,
                fixed: !0,
                editable: !0,
                classes: 'f14 fb green break',
                formatter: 'currency',
                formatoptions: {
                    showZero: !1,
                    decimalPlaces: pricePlaces
                }
            }, {
                name: 'amount',
                label: '我的报价总价',
                width: 100,
                fixed: !0,
                editable: !0,
                classes: 'f14 fb yellow break',
                formatter: 'currency',
                formatoptions: {
                    showZero: !1,
                    decimalPlaces: amountPlaces
                }
            }, {
                name: 'buyUrl',
                label: '参考购买链接',
                width: 80,
                fixed: !0,
                title: !0,
                editable: !1,
                classes: 'ui-ellipsis',
                formatter: function(a, b, c) {
                    return (a && a.indexOf('http') === 0) ? '<a href="' + a + '" target="_blank">' + a + '</a>' : a;
                }
            }, {
                name: 'myUrl',
                label: '我的报价链接',
                width: 100,
                title: !0,
                editable: !0,
                edittype: 'textarea',
                classes: 'break'
            }, {
                name: 'description1',
                label: '备注1',
                width: 100,
                title: !0,
                editable: !0,
                edittype: 'textarea',
                classes: 'break'
            }, {
                name: 'description2',
                label: '备注2',
                width: 100,
                title: !0,
                editable: !0,
                edittype: 'textarea',
                classes: 'break'
            }, {
                name: 'id',
                label: '',
                hidden: !0
            }, {
                name: 'goodsId',
                label: '',
                hidden: !0
            }],
            cmTemplate: {sortable: !1, title: !1},
            width: $(window).width() - 22,
            height: $(window).height() - 220,
            altRows: !1,
            gridview: !0,
            rownumbers: !1,
            onselectrow: !1,
            shrinkToFit: !0,
            forceFit: !1,
            rowNum: 10000,
            cellEdit: !0,
            cellsubmit: 'clientArray',
            triggerAdd: !1,
            loadonce: !0,
            footerrow: !0,
            userDataOnFooter: !0,
            loadComplete: function() {
                THISPAGE.calTotal();
            },
            loadError: function(a, b) {
                $.dialog.tips('Type: ' + b + '; Response: ' + a.status + ' ' + a.statusText, 2);
            },
            beforeEditCell: function(rowId, cellName, cellValue, iRow, iCol) {
                curRow = iRow;
                curCol = iCol;
            },
            afterSaveCell: function(rowId, cellName, cellValue, iRow, iCol) {
                switch (cellName) {
                    case 'price':
                        var rowData = $('#grid').jqGrid('getRowData', rowId);
                        if ($.isNumeric(cellValue) && $.isNumeric(rowData.qty)) {
                            $('#grid').jqGrid('setRowData', rowId, {
                                amount: Number(parseFloat(cellValue) * parseFloat(rowData.qty)).toFixed(2)
                            });
                            THISPAGE.calTotal();
                        }
                        break;
                    case 'amount':
                        var rowData = $('#grid').jqGrid('getRowData', rowId);
                        if ($.isNumeric(cellValue) && $.isNumeric(rowData.qty)) {
                            $('#grid').jqGrid('setRowData', rowId, {
                                price: Number(parseFloat(cellValue) / parseFloat(rowData.qty)).toFixed(5)
                            });
                            THISPAGE.calTotal();
                        }
                        break;
                    default:
                        break;
                }
                var rowData = $('#grid').jqGrid('getRowData', rowId);
                if (rowData) {
                    // 先规范我的采购链接格式
                    if (rowData.myUrl) {
                        if (rowData.myUrl.indexOf('http') !== 0) {
                            $.dialog.tips('自编序号 ' + rowData.zid + ' 的我的报价链接格式错误！请只填入真实链接或留空。无真实链接但有其他提醒信息，则请填入备注里。', 2);
                            return !1;
                        } else if ((rowData.myUrl.indexOf('//cart.') !== -1 || rowData.myUrl.indexOf('//trade.') !== -1) && rowData.uId == defaultPage.SYSTEM.uId) {
                            $.dialog.tips('自编序号 ' + rowData.zid + ' 我的报价链接错误！不能为别人看不见的地址！', 2);
                            return !1;
                        }
                    }
                    if (Number(rowData.price) > 0) {
                        var postData = {
                            iid: cRowId,
                            zid: rowData.zid || '',
                            goodsId: rowData.goodsId || 0,
                            no: rowData.no || '',
                            name: rowData.name || '',
                            spec: rowData.spec || '',
                            sku: rowData.sku || '',
                            brand: rowData.brand || '',
                            unit: rowData.unit || '',
                            qty: rowData.qty,
                            price: rowData.price || 0,
                            amount: rowData.amount || 0,
                            myUrl: rowData.myUrl || '',
                            description1: rowData.description1 || '',
                            description2: rowData.description2 || '',
                            uId: defaultPage.SYSTEM.uId,
                            uName: defaultPage.SYSTEM.uName
                        };
                        Public.ajaxPost('/Bill/InvBq/saveQuote', {
                            postData: JSON.stringify(postData)
                        }, function(res) {
                            if (res && 200 == res.status) {
                                $('#grid').jqGrid('setCell', rowId, 'quoteLevel', res.data.quoteLevel);
                                $('#grid').jqGrid('setCell', rowId, 'myUrl', res.data.myUrl);
                                var quoteInfo = '共 ' + res.data.zids.length + ' 种产品，已报价 ' + res.data.quoteZids.length + ' 种产品，还剩 ' + res.data.nonQuoteZids.length + ' 种产品未报价，未报价产品自编序号为：' + res.data.nonQuoteZids.toString();
                                $('#totalInfo').html(quoteInfo).attr('title', quoteInfo);
                            } else if (res && -2 == res.status) { //报价偏低
                                $.dialog.tips(res.msg, 1, 2);
                                $('#grid').jqGrid('setCell', rowId, 'quoteLevel', res.data.quoteLevel);
                                $('#grid').jqGrid('setCell', rowId, 'myUrl', res.data.myUrl);
                                var quoteInfo = '共 ' + res.data.zids.length + ' 种产品，已报价 ' + res.data.quoteZids.length + ' 种产品，还剩 ' + res.data.nonQuoteZids.length + ' 种产品未报价，未报价产品自编序号为：' + res.data.nonQuoteZids.toString();
                                $('#totalInfo').html(quoteInfo).attr('title', quoteInfo);
                            } else if (res && -3 == res.status) { //报价偏高
                                $.dialog.tips(res.msg, 2, 2);
                                $('#grid').jqGrid('setCell', rowId, 'quoteLevel', res.data.quoteLevel);
                                $('#grid').jqGrid('setCell', rowId, 'myUrl', res.data.myUrl);
                                var quoteInfo = '共 ' + res.data.zids.length + ' 种产品，已报价 ' + res.data.quoteZids.length + ' 种产品，还剩 ' + res.data.nonQuoteZids.length + ' 种产品未报价，未报价产品自编序号为：' + res.data.nonQuoteZids.toString();
                                $('#totalInfo').html(quoteInfo).attr('title', quoteInfo);
                            } else {
                                parent.parent.$.dialog.alert('自动提交失败！' + res.msg, 2);
                                THISPAGE.upQuoteName();
                            }
                        });
                    } else {
                        $.dialog.tips('请先填入有效单价或总价！', 2);
                    }
                } else {
                    $.dialog.tips('信息获取失败！', 2);
                }
            }
        });
    },
    initEvent: function() {
        $('#toBidQuoteReference').on('click', function(a) {
            a.preventDefault();
            parent.parent.tab.addTabItem({
                tabid: 'bid-bidQuoteReference',
                text: '<i class="iconfont icon-chaxun f14 mr5"></i>报价参考',
                url: '/Uri/bid_quote_reference'
            });
        });
        $(window).on('resize', function() {
            setTimeout(function() {
                $("#grid").jqGrid('setGridWidth', $(window).width() - 22);
                $("#grid").jqGrid('setGridHeight', $(window).height() - 220);
            }, 100);
        });
    },
    addFiles: function(files) {
        var fileDiv = '';
        $.each(files, function(k, v) {
            fileDiv += (
                '<div class="file-line" data-key="' + k + '">' +
                '<a href="/data/upfile/bidQuote/' + v.url + '" target="_blank" title="' + v.name + '"><span>' + v.name + '</span><span>' + v.upName + '</span><span>' + v.upTime + '</span></a>' +
                '</div>'
            );
        });
        $('#files').html(fileDiv);
    },
    calTotal: function() {
        for (var a = $('#grid').jqGrid('getDataIDs'), b = 0, c = 0, d = 0, e = a.length; e > d; d++) {
            var f = a[d];
            var g = $('#grid').jqGrid('getRowData', f);
            g.qty && (b += parseFloat(g.qty));
            g.amount && (c += parseFloat(g.amount));
        }
        $('#grid').jqGrid('footerData', 'set', {
            zid: '合计',
            qty: b,
            amount: Number(c).toFixed(2)
        });
    },
    cancleGridEdit: function() {
        curRow && curCol && ($('#grid').jqGrid('saveCell', curRow, curCol), curRow = curCol = null);
    },
    postData: function() {
        if (curRow == null && curCol == null) {
            $('.manage-wrapper').append('<div id="div-lock"><div class="loading">即将关闭...</div></div>');
            setTimeout(() => {
                THISPAGE.upQuoteName();
            }, 2500);
        } else {
            $.dialog.tips('提交失败！', 2);
        }
    },
    upQuoteName: function() {
        Public.ajaxPost('/Bill/InvBq/upQuoteName', {
            id: cRowId,
            type: 2
        }, function(res) {
            if (callback && 'function' == typeof callback) {
                callback({}, 'save', window);
            }
        });
    }
};
$(function() {
    THISPAGE.init();
});
