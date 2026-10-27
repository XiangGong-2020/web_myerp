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
            Public.ajaxPost('/Bill/InvPc/queryQuote', {
                id: cRowId
            }, function(res) {
                if (res && 200 == res.status) {
                    rowData = res.data;
                    THISPAGE.initBtns();
                    THISPAGE.initDom();
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
    initDom: function() {
        rowData.data.reqInfo && $('#notice').append('<p title="' + rowData.data.reqInfo + '">' + rowData.data.reqInfo + '</p>');
        rowData.data.description && $('#notice').append('<p title="' + rowData.data.description + '">' + rowData.data.description + '</p>');
        rowData.data.files && THISPAGE.addFiles(rowData.data.files);
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
                classes: 'night break'
            }, {
                name: 'limitPrice',
                label: '参考单价',
                width: 100,
                fixed: !0,
                editable: !1,
                hidden: $.inArray('SYS_ADVANCE', defaultPage.SYSTEM.rights) === -1 ? !0 : !1,
                classes: 'gray',
                formatter: 'currency',
                formatoptions: {
                    showZero: !1,
                    decimalPlaces: pricePlaces
                }
            }, {
                name: 'price',
                label: '我的采购单价',
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
                label: '我的采购总价',
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
                name: 'quoteLevel',
                label: '优势预估',
                width: 60,
                fixed: !0,
                hidden: !0,
                editable: !1,
                formatter: function(a, b, c) {
                    var d = a == 0 ? 'w0' : (a == 1 ? 'pct50' : (a == 2 ? 'pct75' : 'pct100'));
                    var e = a == 0 ? '' : (a == 1 ? '小' : (a == 2 ? '中' : '大'));
                    var f = a == 0 ? '' : (a == 1 ? '优势较小（报价过低或过高）' : (a == 2 ? '优势居中（报价合理）' : '优势较大（报价优秀）'));
                    return '<div class="pct100 bg-silver b-r-10 ovh"><span class="quoteLevel ' + d + '" title="' + f + '">' + e + '</span></div>';
                }
            }, {
                name: 'isHasExpress',
                label: '包邮？',
                width: 42,
                fixed: !0,
                formatter: function(a, b, c) {
                    return 1 == a ? '是' : '&nbsp;';
                },
                editable: !0,
                edittype: 'select',
                editoptions: {
                    value: {
                        1: '是',
                        0: '否'
                    }
                }
            }, {
                name: 'isHasInvoice',
                label: '带票？',
                width: 42,
                fixed: !0,
                formatter: function(a, b, c) {
                    return 1 == a ? '是' : '&nbsp;';
                },
                editable: !0,
                edittype: 'select',
                editoptions: {
                    value: {
                        1: '是',
                        0: '否'
                    }
                }
            }, {
                name: 'buyUrl',
                label: '参考采购链接',
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
                label: '我的采购链接',
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
            }],
            cmTemplate: {sortable: !1, title: !1},
            width: $(window).width() - 22,
            height: $(window).height() - 265,
            altRows: !1,
            gridview: !0,
            rownumbers: !0,
            rownumWidth: 40,
            onselectrow: !1,
            shrinkToFit: !0,
            forceFit: !1,
            pager: '#page',
            page: 1,
            rowNum: 10000,
            rowList: [10000, 20000, 50000],
            viewrecords: !0,
            cellEdit: !0,
            cellsubmit: 'clientArray',
            triggerAdd: !1,
            loadonce: !0,
            footerrow: !0,
            userDataOnFooter: !0,
            loadComplete: function(res) {
                var quoteInfo = '共 ' + a.zids.length + ' 种产品，已报价 ' + a.quoteZids.length + ' 种产品，还剩 ' + a.nonQuoteZids.length + ' 种产品未报价，未报价产品自编序号为：' + a.nonQuoteZids.toString();
                $('#totalInfo').html(quoteInfo).attr('title', quoteInfo);
                THISPAGE.calTotal();
                if (res && res.rows.length > 0) {
                    for (var i = 0; i < res.rows.length; i++) {
                        if (res.rows[i].paidAmount > 0) { //已付款条目
                            $('#grid tr#' + res.rows[i].id).addClass('ui-state-disabled');
                            $('#grid tr#' + res.rows[i].id + ' input').attr({disabled: 'disabled'});
                        }
                    }
                }
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
                            $.dialog.tips('自编序号 ' + rowData.zid + ' 的我的采购链接格式错误！请只填入真实链接或留空。无真实链接但有其他提醒信息，则请填入备注里。', 2);
                            return !1;
                        } else if ((rowData.myUrl.indexOf('//cart.') !== -1 || rowData.myUrl.indexOf('//trade.') !== -1) && rowData.uId == defaultPage.SYSTEM.uId) {
                            $.dialog.tips('自编序号 ' + rowData.zid + ' 的我的采购链接错误！不能为购物车或其他无法访问的地址！', 2);
                            return !1;
                        }
                    }
                    if (Number(rowData.price) > 0) {
                        var postData = {
                            iid: cRowId,
                            zid: rowData.zid || '',
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
                            isHasExpress: '是' == rowData.isHasExpress ? 1 : 0,
                            isHasInvoice: '是' == rowData.isHasInvoice ? 1 : 0,
                            uId: defaultPage.SYSTEM.uId,
                            uName: defaultPage.SYSTEM.uName
                        };
                        Public.ajaxPost('/Bill/InvPc/saveQuote', {
                            postData: JSON.stringify(postData)
                        }, function(res) {
                            if (res && 200 == res.status) {
                                $('#grid').jqGrid('setCell', rowId, 'quoteLevel', res.data.quoteLevel);
                                $('#grid').jqGrid('setCell', rowId, 'myUrl', res.data.myUrl);
                                var quoteInfo = '共 ' + res.data.zids.length + ' 种产品，已报价 ' + res.data.quoteZids.length + ' 种产品，还剩 ' + res.data.nonQuoteZids.length + ' 种产品未报价，未报价产品自编序号为：' + res.data.nonQuoteZids.toString();
                                $('#totalInfo').html(quoteInfo).attr('title', quoteInfo);
                            } else if (res && -2 == res.status) { //报价偏低
                                //$.dialog.tips(res.msg, 1, 2, !1);
                                $('#grid').jqGrid('setCell', rowId, 'quoteLevel', res.data.quoteLevel);
                                $('#grid').jqGrid('setCell', rowId, 'myUrl', res.data.myUrl);
                                var quoteInfo = '共 ' + res.data.zids.length + ' 种产品，已报价 ' + res.data.quoteZids.length + ' 种产品，还剩 ' + res.data.nonQuoteZids.length + ' 种产品未报价，未报价产品自编序号为：' + res.data.nonQuoteZids.toString();
                                $('#totalInfo').html(quoteInfo).attr('title', quoteInfo);
                            } else if (res && -3 == res.status) { //报价偏高
                                //$.dialog.tips(res.msg, 2, 2, !1);
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
                        $.dialog.tips('请先填入有效单价或总价！', 2, 2, !1);
                    }
                } else {
                    $.dialog.tips('信息获取失败！', 2, 2, !1);
                }
            }
        });
    },
    initEvent: function() {
        $('#toPurchaseContractHis').on('click', function(a) {
            a.preventDefault();
            if (Business.verifyRight('PC_ADD')) {
                parent.parent.tab.addTabItem({
                    tabid: 'purchase-purchaseContractHis',
                    text: '<i class="iconfont icon-rizhi f14 mr5"></i>特采历史',
                    url: '/Uri/purchaseContract_his'
                });
            }
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
                '<a href="/data/upfile/contract/' + v.url + '" target="_blank" title="' + v.name + '"><span>' + v.name + '</span><span>' + v.upName + '</span><span>' + v.upTime + '</span></a>' +
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
            rn: '合计',
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
        Public.ajaxPost('/Bill/InvPc/upQuoteName', {
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
