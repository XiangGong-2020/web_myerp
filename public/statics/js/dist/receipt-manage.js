var urlParam = Public.urlParam();
var defaultPage = Public.getDefaultPage();
var qtyPlaces = Number(defaultPage.SYSTEM.qtyPlaces);
var pricePlaces = Number(defaultPage.SYSTEM.pricePlaces);
var amountPlaces = Number(defaultPage.SYSTEM.amountPlaces);
var contactCombo = Business.supplierCombo($('#contact'), {
    width: 200
});
var incometypeCombo = Business.categoryCombo($('.incometypeAuto'), {
    listWidth: 324,
    listHeight: 300,
    trigger: !1,
    callback: {
        onChange: function(a) {
            var b = this.input.parents('tr');
            a && b.data('incometypeInfo', a);
        }
    }
}, 'incometype');
var companyAccountCombo = Business.companyAccountCombo($('.companyAccountAuto'), {
    listWidth: 200,
    listHeight: 527,
    trigger: !1,
    callback: {
        onChange: function(a) {
            var b = this.input.parents('tr');
            a && b.data('companyAccountInfo', a);
        }
    }
});
var settlementtypeCombo = Business.categoryCombo($('.settlementtypeAuto'), {
    trigger: !1,
    listWidth: 100,
    callback: {
        onChange: function(a) {
            var b = this.input.parents('tr');
            a && b.data('settlementtypeInfo', a);
        }
    }
}, 'settlementtype');
var originalData;
var curRow;
var curCol;
var btn_save = '<a id="save" class="ui-btn bg-green"><i class="iconfont icon-baocun"></i><span>保存</span></a>';
var btn_edit = '<a id="edit" class="ui-btn bg-green"><i class="iconfont icon-baocun"></i><span>保存</span></a>';
var btn_check = '<a id="check" class="ui-btn bg-green"><i class="iconfont icon-shenhe"></i><span>审核</span></a>';
var btn_uncheck = '<a id="uncheck" class="ui-btn bg-red"><i class="iconfont icon-shenhe"></i><span>反审核</span></a>';
var btn_delete = '<a id="delete" class="ui-btn bg-red"><i class="iconfont icon-shanchu"></i><span>删除</span></a>';
var THISPAGE = {
    init: function(a) {
        THISPAGE.initDom(a);
        THISPAGE.initGrid(a);
        THISPAGE.initEvent();
    },
    initDom: function(a) {
        if (a.id > 0) {
            a.checkId > 0 ? $('#mark').addClass('bill-has-checked') : $('#mark').removeClass('bill-has-checked');
            $('#toolTop').html(btn_edit + (a.checkId > 0 ? btn_uncheck : btn_check) + btn_delete);
            $('#contact').data('contactData', {
                id: a.contactId,
                name: a.contactName
            });
            contactCombo.input.val(a.contactName).attr('title', a.contactName);
            $('#billNo').val(a.billNo);
            $('#billDate').val(a.billDate);
            $('#srcNo').val(a.srcNo);
            $('#amount').val(a.amount);
            $('#cleared').val(a.cleared);
            $('#description').val(a.description);
            $('#uName').text(a.uName);
            $('#checkName').text(a.checkName);
            $('#createTime').text(a.createTime);
            $('#modifyTime').text(a.modifyTime);
            $('#desBtn').show();
            a.files && THISPAGE.addFiles(a.files);
        } else {
            $('#toolTop').html(btn_save);
            if (a.contactId >= 0 && a.contactName) {
                $('#contact').data('contactData', {
                    id: a.contactId,
                    name: a.contactName
                });
                contactCombo.input.val(a.contactName).attr('title', a.contactName);
            }
            $('#billDate').val(defaultPage.SYSTEM.endDate);
            a.srcNo && $('#srcNo').val(a.srcNo);
            a.amount && $('#amount').val(a.amount);
            a.cleared && $('#cleared').val(a.cleared);
            $('#uName').text(defaultPage.SYSTEM.uName || '');
        }
        if (originalData.srcTransType == 101111) {
            $('#amount').attr('disabled', true).addClass('ui-input-dis');
            $('#gridGoods').parent().parent().removeClass('dn').addClass('fl pct35');
            $('#grid').parent().removeClass('b-r-3').addClass('b-r-3-left');
            $('#grid').parent().parent().addClass('fl pct65');
            THISPAGE.initGridGoods();
        }
        if (originalData.srcTransType == 109101) {
            $('#amount').attr('disabled', true).addClass('ui-input-dis');
        }
    },
    initGridGoods: function() {
        $('#gridGoods').jqGrid({
            url: '/Bill/InvPc/query',
            postData: {id: originalData.srcId},
            datatype: 'json',
            colModel: [{
                name: 'zid',
                label: '自编序号',
                width: 60,
                fixed: !0,
                title: !0,
                classes: 'break'
            }, {
                name: 'goods',
                label: '产品信息',
                width: 100,
                title: !0,
                classes: 'break',
                formatter: function(a, b, c) {
                    return c.name + (c.spec ? ' ' + c.spec : '') + (c.sku ? ' ' + c.sku : '') + (c.brand ? ' ' + c.brand : '') + ' × ' + c.qty + (c.unit ? ' ' + c.unit : '');
                }
            }, {
                name: 'uName',
                label: '采购人',
                width: 44,
                fixed: !0,
                classes: 'gray'
            }, {
                name: 'limitAmount',
                label: '参考限价',
                width: 70,
                fixed: !0,
                classes: 'gray',
                formatter: 'currency',
                formatoptions: {
                    showZero: !0,
                    decimalPlaces: amountPlaces
                }
            }, {
                name: 'amount',
                label: '采购总价',
                width: 70,
                fixed: !0,
                formatter: 'currency',
                formatoptions: {
                    showZero: !0,
                    decimalPlaces: amountPlaces
                },
                cellattr: function(rowId, val, rawObject, cm, rdata) {
                    if (Number(rawObject.amount) != Number(rawObject.paidAmount)) {
                        return 'class="red" title="采购总价与有效付款不符"';
                    } else {
                        return 'class="green"';
                    }
                }
            }, {
                name: 'paidStatus',
                label: '款项状态与操作',
                width: 114,
                fixed: !0,
                classes: 'rel',
                formatter: function(a, b, c) {
                    var con = '<div class="">' +
                        '<p class="lh20 gray">有效付款<span class="ml5 green">' + c.paidAmount + '</span></p>' +
                        '<p class="lh20 gray">历史付款<span class="ml5 green">' + c.totalPaidAmount + '</span></p>' +
                        '<p class="lh20 gray">预退款<span class="ml5 yellow">' + c.refundingAmount + '</span></p>' +
                        '<p class="lh20 gray">实退款<span class="ml5 yellow">' + c.refundedAmount + '</span></p>' +
                        '<p class="mb5 lh20 gray">返现退款<span class="ml5 yellow">' + c.refundedCashbackAmount + '</span></p>';
                    if (c.totalPaidAmount == 0) {
                        con += '<span class="db lh20 tc white bg-rgba3 b-r-3 def">无付款信息</span>';
                    } else {
                        con += c.paidAmount > 0 ? (
                            '<span class="virtual-refund-btn db mb5 lh20 tc hover-gold b-r-3" data-id="' + c.id + '" title="发起本产品的预退款<p>功能说明：确定已发起退款但退款款项要延后才到账的情况下，可先进行预退款。预退款可随时取消，或退款到账后转为实退款。</p>">预退款</span>' +
                            '<span class="refund-btn db mb5 lh20 tc hover-gold b-r-3" data-id="' + c.id + '" title="发起本产品的实退款<p>功能说明：确定已退款且退款款项已到账，进行实退款。</p>">实退款</span>' +
                            '<span class="cashback-refund-btn db lh20 tc hover-gold b-r-3" data-id="' + c.id + '" title="发起本产品的返现退款<p>功能说明：确定返现已到账，进行返现退款（不允许采购报价时事先扣掉返现）。</p>">返现退款</span>'
                        ) : (
                            '<span class="db lh20 tc white bg-rgba3 b-r-3 def">无有效付款可退</span>'
                        );
                        con += ('<span class="single-search-pay-btn db fl mt5 pl2 pr2 lh20 hover-teal b-r-3" data-id="' + c.id + '" title="单独查看本产品的付款记录">付款记录</span>' +
                        '<span class="single-search-rec-btn db fr mt5 pl2 pr2 lh20 hover-teal b-r-3" data-id="' + c.id + '" title="单独查看本产品的退款记录">退款记录</span>');
                    }
                    con += '</div>';
                    return con;
                }
            }, {
                name: 'iid',
                label: '',
                width: 0,
                hidden: !0
            }, {
                name: 'paidAmount',
                label: '',
                width: 0,
                hidden: !0
            }, {
                name: 'totalPaidAmount',
                label: '',
                width: 0,
                hidden: !0
            }, {
                name: 'refundingAmount',
                label: '',
                width: 0,
                hidden: !0
            }, {
                name: 'refundedAmount',
                label: '',
                width: 0,
                hidden: !0
            }, {
                name: 'refundedCashbackAmount',
                label: '',
                width: 0,
                hidden: !0
            }],
            cmTemplate: {sortable: !1, title: !1},
            jsonReader: {root: 'data.entries', records: 'data.totalsize', repeatitems: !1, id: 'id'},
            autowidth: !0,
            height: 500,
            altRows: !1,
            gridview: !0,
            rownumbers: !0,
            rownumWidth: 40,
            shrinkToFit: !0,
            forceFit: !1,
            rowNum: 10000,
            cellsubmit: 'clientArray',
            loadonce: !0,
            footerrow: !0,
            userDataOnFooter: !0,
            loadComplete: function(a) {
                THISPAGE.calTotalGoods();
            },
            loadError: function(a, b) {
                $.dialog.tips('Type: ' + b + '; Response: ' + a.status + ' ' + a.statusText, 2);
            },
            gridComplete: function() {
                setTimeout(function() {
                    Public.autoGridWidth($('#gridGoods'));
                }, 10);
            }
        });
    },
    initGrid: function(a) {
        if (a.id) {
            var b = (originalData.srcTransType == 101111 ? 0 : 1) - a.entries.length;
            if (b > 0) {
                for (var c = 0; b > c; c++) {
                    a.entries.push({});
                }
            }
        }
        THISPAGE.newId = originalData.srcTransType == 101111 ? 1 : 2;
        $('#grid').jqGrid({
            data: a.entries,
            datatype: 'clientSide',
            colModel: [{
                name: 'operate',
                label: '操作',
                width: 72.8,
                fixed: !0,
                formatter: function(val, opt, row) {
                    var html_con = (
                        '<div class="operating" data-id="' + opt.rowId + '">' +
                        '<span class="oper-icon ui-icon-plus iconfont icon-zengjia" title="新增一行"></span>' +
                        ((row.uId == void 0 || row.uId == defaultPage.SYSTEM.uId) ? '<span class="ml2 oper-icon ui-icon-trash iconfont icon-shanchu" title="删除本行"></span>' : '') +
                        '</div>'
                    );
                    return html_con;
                }
            }, {
                name: 'zid',
                label: '自编序号',
                width: 60,
                fixed: !0,
                hidden: originalData.srcTransType == 101111 ? !1 : !0,
                title: !0,
                editable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'amount',
                label: '收款金额',
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
                label: '收款类别',
                width: 90,
                fixed: !0,
                title: !0,
                editable: !0,
                classes: 'ui-ellipsis',
                edittype: 'custom',
                editoptions: {
                    custom_element: function(a, b) {
                        var c = $('.incometypeAuto')[0];
                        return c;
                    },
                    custom_value: function(a, b, c) {
                        if ('get' === b) {
                            if ('' !== $('.incometypeAuto').getCombo().getValue()) {
                                return $(a).val();
                            }
                            var d = $(a).parents('tr');
                            return d.removeData('incometypeInfo'), '';
                        }
                        'set' === b && $('input', a).val(c);
                    },
                    handle: function() {
                        $('#initCombo').append($('.incometypeAuto').val('').unbind('focus.once'));
                    },
                    trigger: 'ui-icon-triangle-1-s iconfont icon-xiajiantou'
                }
            }, {
                name: 'accName',
                label: '收款账户',
                width: 90,
                fixed: !0,
                title: !0,
                editable: !0,
                classes: 'ui-ellipsis',
                edittype: 'custom',
                editoptions: {
                    custom_element: function(a, b) {
                        var c = $('.companyAccountAuto')[0];
                        return c;
                    },
                    custom_value: function(a, b, c) {
                        if ('get' === b) {
                            if ('' !== $('.companyAccountAuto').getCombo().getValue()) {
                                return $(a).val();
                            }
                            var d = $(a).parents('tr');
                            return d.removeData('companyAccountInfo'), '';
                        }
                        'set' === b && $('input', a).val(c);
                    },
                    handle: function() {
                        $('#initCombo').append($('.companyAccountAuto').val('').unbind('focus.once'));
                    },
                    trigger: 'ui-icon-triangle-1-s iconfont icon-xiajiantou'
                }
            }, {
                name: 'wayName',
                label: '收款方式',
                width: 60,
                fixed: !0,
                title: !0,
                editable: !0,
                classes: 'ui-ellipsis',
                edittype: 'custom',
                editoptions: {
                    custom_element: function(a, b) {
                        var c = $('.settlementtypeAuto')[0];
                        return c;
                    },
                    custom_value: function(a, b, c) {
                        if ('get' === b) {
                            if ('' !== $('.settlementtypeAuto').getCombo().getValue()) {
                                return $(a).val();
                            }
                            var d = $(a).parents('tr');
                            return d.removeData('settlementtypeInfo'), '';
                        }
                        'set' === b && $('input', a).val(c);
                    },
                    handle: function() {
                        $('#initCombo').append($('.settlementtypeAuto').val('').unbind('focus.once'));
                    },
                    trigger: 'ui-icon-triangle-1-s iconfont icon-xiajiantou'
                }
            }, {
                name: 'settlementTime',
                label: '收款时间',
                width: 124,
                fixed: !0,
                editable: !0,
                editoptions: {
                    dataInit: function(element) {
                        $(element).attr('readonly', true);
                        $(element).on("click", function() {
                            laydate.render({
                                elem: element,
                                type: 'datetime'
                            });
                        }).trigger('click');
                    }
                }
            }, {
                name: 'uName',
                label: '收款人',
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
                name: 'moneyOperate',
                label: '标记与操作',
                width: 100,
                fixed: !0,
                hidden: originalData.srcTransType == 101111 ? !1 : !0,
                formatter: function(a, b, c) {
                    var tag = '';
                    if (c.moneyType == 1 || c.moneyType == 11) {
                        tag = '<span class="db mt5 h20 lh20 tc white bg-rgba3 b-r-3 def">预转实退款</span>';
                    } else if (c.moneyType == 2 || c.moneyType == 12) {
                        tag = '<span class="db mt5 h20 lh20 tc white bg-rgba3 b-r-3 def">返现退款</span>';
                    } else if (c.moneyType == 9 || c.moneyType == 19) {
                        if ($.isNumeric(c.id)) {
                            if (c.uId == defaultPage.SYSTEM.uId) {
                                tag = '<span class="cancel-virtual-refund-btn db mt-5 h18 lh18 tc hover-yellow b-r-3" data-id="' + c.id + '" title="点击取消预退款">取消预退款</span>' +
                                    '<span class="enter-virtual-refund-btn db mt2 mb-5 h18 lh18 tc hover-red b-r-3" data-id="' + c.id + '" title="退款到账后点击转为实退款">转为实退款</span>';
                            } else {
                                tag = '<span class="db mt5 h20 lh20 tc white bg-rgba3 b-r-3 def">预退款中</span>';
                            }
                        } else {
                            tag = '<span class="db mt5 h20 lh20 tc white bg-rgba3 b-r-3 def" title="保存后，下次打开本单据可进行后续的取消预退款或转为实退款操作。">预退款</span>';
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
                label: '付款类别ID',
                hidden: !0
            }, {
                name: 'accId',
                label: '付款账户ID',
                hidden: !0
            }, {
                name: 'wayId',
                label: '付款方式ID',
                hidden: !0
            }, {
                name: 'uId',
                label: '付款人ID',
                hidden: !0
            }, {
                name: 'moneyType',
                label: '款项类别',
                hidden: !0
            }],
            cmTemplate: {sortable: !1, title: !1},
            localReader: {root: 'rows', records: 'records', repeatitems: !1, id: 'id'},
            jsonReader: {root: 'data.entries', records: 'records', repeatitems: !1, id: 'id'},
            autowidth: !0,
            height: originalData.srcTransType == 101111 ? 500 : '100%',
            gridview: !0,
            rownumbers: !0,
            onselectrow: !1,
            shrinkToFit: !0,
            forceFit: !1,
            rowNum: 10000,
            cellEdit: !0,
            cellsubmit: 'clientArray',
            loadonce: !0,
            footerrow: !0,
            userDataOnFooter: !0,
            loadComplete: function(a) {
                var b = a.rows;
                var c = b.length;
                THISPAGE.newMoneyId = c + 1;
                for (var d = 0; c > d; d++) {
                    var f = b[d];
                    if ($.isEmptyObject(b[d])) {
                        break;
                    }
                    $('#grid #' + f.id).data('incometypeInfo', {
                        id: f.typeId,
                        name: f.typeName
                    }).data('companyAccountInfo', {
                        id: f.accId,
                        name: f.accName
                    }).data('settlementtypeInfo', {
                        id: f.wayId,
                        name: f.wayName
                    });
                }
                THISPAGE.calTotal();
            },
            loadError: function(a, b) {
                $.dialog.tips('Type: ' + b + '; Response: ' + a.status + ' ' + a.statusText, 2);
            },
            gridComplete: function() {
                setTimeout(function() {
                    Public.autoGridWidth($('#grid'));
                }, 10);
            },
            onCellSelect: function(rowId, iCol, cellContent, event) {
                if (a.id > 0) {
                    var rowData = $('#grid').jqGrid('getRowData', rowId);
                    if (rowData.uId != void 0 && rowData.uId != '' && rowData.uId != defaultPage.SYSTEM.uId) {
                        // 不是新增行、自己行，设置不允许编辑
                        $('#grid').jqGrid('setCell', rowId, 'zid', '', 'not-editable-cell');
                        $('#grid').jqGrid('setCell', rowId, 'amount', '', 'not-editable-cell');
                        $('#grid').jqGrid('setCell', rowId, 'typeName', '', 'not-editable-cell');
                        $('#grid').jqGrid('setCell', rowId, 'accName', '', 'not-editable-cell');
                        $('#grid').jqGrid('setCell', rowId, 'wayName', '', 'not-editable-cell');
                        $('#grid').jqGrid('setCell', rowId, 'settlementTime', '', 'not-editable-cell');
                        $('#grid').jqGrid('setCell', rowId, 'description', '', 'not-editable-cell');
                    }
                }
            },
            beforeEditCell: function(rowId, cellName, cellValue, iRow, iCol) {
                curRow = iRow;
                curCol = iCol;
            },
            afterEditCell: function(rowId, cellName, cellValue, iRow, iCol) {
                switch (cellName) {
                    case 'typeName':
                        $('#' + iRow + '_typeName', '#grid').val(cellValue);
                        break;
                    case 'accName':
                        $('#' + iRow + '_accName', '#grid').val(cellValue);
                        break;
                    case 'wayName':
                        $('#' + iRow + '_wayName', '#grid').val(cellValue);
                        break;
                    default:
                        break;
                }
            },
            afterSaveCell: function(rowId, cellName, cellValue, iRow, iCol) {
                'amount' == cellName && THISPAGE.calTotal();
            }
        });
    },
    initEvent: function() {
        Business.billsEvent(this, 'receipt');
        contactCombo.input.enterKey();
        laydate.render({
            elem: "#billDate",
        });
        $('.wrapper').on('click', '#save', function(a) {
            a.preventDefault();
            if (Business.verifyRight('RECEIPT_ADD')) {
                var data = THISPAGE.getPostData();
                if (data) {
                    $('.wrapper').append('<div id="div-lock"><div class="loading">保存中...</div></div>');
                    Public.ajaxPost('/Bill/MonIo/add', {
                        postData: JSON.stringify(data)
                    }, function(res) {
                        if (res && 200 == res.status) {
                            parent.parent.$.dialog.tips('新增成功！');
                            if (originalData.srcTransType > 0 && originalData.srcId > 0) {
                                if (originalData.srcTransType == 101101 || originalData.srcTransType == 101102) {
                                    parent.frames['purchase-purchaseOrder'] && parent.frames['purchase-purchaseOrder'].$('#search').trigger('click');
                                    parent.frames['purchase-purchaseOrder' + originalData.srcId] && parent.frames['purchase-purchaseOrder' + originalData.srcId].location.reload();
                                } else if (originalData.srcTransType == 101111) {
                                    parent.frames['purchase-purchaseContract'] && parent.frames['purchase-purchaseContract'].$('#search').trigger('click');
                                    parent.frames['purchase-purchaseContractFill'] && parent.frames['purchase-purchaseContractFill'].$('#search').trigger('click');
                                    parent.frames['purchase-purchaseContract' + originalData.srcId] && parent.frames['purchase-purchaseContract' + originalData.srcId].location.reload();
                                } else if (originalData.srcTransType == 101201) {
                                    parent.frames['sales-salesOrder'] && parent.frames['sales-salesOrder'].$('#search').trigger('click');
                                    parent.frames['sales-salesOrder' + originalData.srcId] && parent.frames['sales-salesOrder' + originalData.srcId].location.reload();
                                } else if (originalData.srcTransType == 101211) {
                                    parent.frames['sales-salesContract'] && parent.frames['sales-salesContract'].$('#search').trigger('click');
                                    parent.frames['sales-salesContract' + originalData.srcId] && parent.frames['sales-salesContract' + originalData.srcId].location.reload();
                                } else if (originalData.srcTransType == 109101) {
                                    parent.frames['deliver-deliver'] && parent.frames['deliver-deliver'].$('#refresh').trigger('click');
                                    parent.frames['deliver-deliver' + originalData.srcId] && parent.frames['deliver-deliver' + originalData.srcId].location.reload();
                                }
                            }
                            parent.frames['money-receipt'] && parent.frames['money-receipt'].$('#search').trigger('click');
                            parent.tab.removeSelectedTabItem();
                        } else {
                            $('.wrapper > #div-lock').remove();
                            $.dialog.tips('新增失败！' + res.msg, 2);
                        }
                    });
                }
            }
        });
        $('.wrapper').on('click', '#edit', function(a) {
            a.preventDefault();
            if (Business.verifyRight('RECEIPT_UPDATE')) {
                var data = THISPAGE.getPostData();
                if (data) {
                    $('.wrapper').append('<div id="div-lock"><div class="loading">保存中...</div></div>');
                    Public.ajaxPost('/Bill/MonIo/update', {
                        postData: JSON.stringify(data)
                    }, function(res) {
                        if (res && 200 == res.status) {
                            parent.parent.$.dialog.tips('更新成功！');
                            if (originalData.srcTransType > 0 && originalData.srcId > 0) {
                                if (originalData.srcTransType == 101101 || originalData.srcTransType == 101102) {
                                    parent.frames['purchase-purchaseOrder'] && parent.frames['purchase-purchaseOrder'].$('#search').trigger('click');
                                    parent.frames['purchase-purchaseOrder' + originalData.srcId] && parent.frames['purchase-purchaseOrder' + originalData.srcId].location.reload();
                                } else if (originalData.srcTransType == 101111) {
                                    parent.frames['purchase-purchaseContract'] && parent.frames['purchase-purchaseContract'].$('#search').trigger('click');
                                    parent.frames['purchase-purchaseContractFill'] && parent.frames['purchase-purchaseContractFill'].$('#search').trigger('click');
                                    parent.frames['purchase-purchaseContract' + originalData.srcId] && parent.frames['purchase-purchaseContract' + originalData.srcId].location.reload();
                                } else if (originalData.srcTransType == 101201) {
                                    parent.frames['sales-salesOrder'] && parent.frames['sales-salesOrder'].$('#search').trigger('click');
                                    parent.frames['sales-salesOrder' + originalData.srcId] && parent.frames['sales-salesOrder' + originalData.srcId].location.reload();
                                } else if (originalData.srcTransType == 101211) {
                                    parent.frames['sales-salesContract'] && parent.frames['sales-salesContract'].$('#search').trigger('click');
                                    parent.frames['sales-salesContract' + originalData.srcId] && parent.frames['sales-salesContract' + originalData.srcId].location.reload();
                                } else if (originalData.srcTransType == 109101) {
                                    parent.frames['deliver-deliver'] && parent.frames['deliver-deliver'].$('#refresh').trigger('click');
                                    parent.frames['deliver-deliver' + originalData.srcId] && parent.frames['deliver-deliver' + originalData.srcId].location.reload();
                                }
                            }
                            parent.frames['money-receipt'] && parent.frames['money-receipt'].$('#search').trigger('click');
                            parent.tab.removeSelectedTabItem();
                        } else {
                            $('.wrapper > #div-lock').remove();
                            $.dialog.tips('更新失败！' + res.msg, 2);
                        }
                    });
                }
            }
        });
        $('.wrapper').on('click', '#check', function(a) {
            a.stopPropagation();
            a.preventDefault();
            if (Business.verifyRight('RECEIPT_CHECK')) {
                originalData.id && Public.ajaxPost('/Bill/MonIo/check', {
                    id: originalData.id,
                    checkId: defaultPage.SYSTEM.uId,
                    checkName: defaultPage.SYSTEM.uName,
                    billNo: originalData.billNo,
                    transType: originalData.transType
                }, function(res) {
                    if (res && 200 == res.status) {
                        $.dialog.tips('审核成功！');
                        originalData.checkId = defaultPage.SYSTEM.uId;
                        originalData.checkName = defaultPage.SYSTEM.uName;
                        $('#mark').addClass('bill-has-checked');
                        $('#check').after(btn_uncheck).remove();
                        $('#checkName').html(defaultPage.SYSTEM.uName);
                        parent.frames['money-receipt'] && parent.frames['money-receipt'].$('#search').trigger('click');
                    } else {
                        $.dialog.tips('审核失败！' + res.msg, 2);
                    }
                });
            }
        });
        $('.wrapper').on('click', '#uncheck', function(a) {
            a.stopPropagation();
            a.preventDefault();
            if (Business.verifyRight('RECEIPT_UNCHECK')) {
                originalData.id && Public.ajaxPost('/Bill/MonIo/check', {
                    id: originalData.id,
                    checkId: 0,
                    checkName: '',
                    billNo: originalData.billNo,
                    transType: originalData.transType
                }, function(res) {
                    if (res && 200 == res.status) {
                        $.dialog.tips('反审核成功！');
                        originalData.checkId = 0;
                        originalData.checkName = '';
                        $('#mark').removeClass('bill-has-checked');
                        $('#uncheck').after(btn_check).remove();
                        $('#checkName').html('');
                        parent.frames['money-receipt'] && parent.frames['money-receipt'].$('#search').trigger('click');
                    } else {
                        $.dialog.tips('反审核失败！' + res.msg, 2);
                    }
                });
            }
        });
        $('.wrapper').on('click', '#delete', function(a) {
            a.preventDefault();
            if (Business.verifyRight('RECEIPT_DELETE')) {
                originalData.id && $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                    Public.ajaxGet('/Bill/MonIo/delete', {
                        id: originalData.id
                    }, function(res) {
                        if (res && 200 == res.status) {
                            parent.$.dialog.tips('删除成功！');
                            if (originalData.srcTransType > 0 && originalData.srcId > 0) {
                                if (originalData.srcTransType == 101101 || originalData.srcTransType == 101102) {
                                    parent.frames['purchase-purchaseOrder'] && parent.frames['purchase-purchaseOrder'].$('#search').trigger('click');
                                    parent.frames['purchase-purchaseOrder' + originalData.srcId] && parent.frames['purchase-purchaseOrder' + originalData.srcId].location.reload();
                                } else if (originalData.srcTransType == 101111) {
                                    parent.frames['purchase-purchaseContract'] && parent.frames['purchase-purchaseContract'].$('#search').trigger('click');
                                    parent.frames['purchase-purchaseContractFill'] && parent.frames['purchase-purchaseContractFill'].$('#search').trigger('click');
                                    parent.frames['purchase-purchaseContract' + originalData.srcId] && parent.frames['purchase-purchaseContract' + originalData.srcId].location.reload();
                                } else if (originalData.srcTransType == 101201) {
                                    parent.frames['sales-salesOrder'] && parent.frames['sales-salesOrder'].$('#search').trigger('click');
                                    parent.frames['sales-salesOrder' + originalData.srcId] && parent.frames['sales-salesOrder' + originalData.srcId].location.reload();
                                } else if (originalData.srcTransType == 101211) {
                                    parent.frames['sales-salesContract'] && parent.frames['sales-salesContract'].$('#search').trigger('click');
                                    parent.frames['sales-salesContract' + originalData.srcId] && parent.frames['sales-salesContract' + originalData.srcId].location.reload();
                                } else if (originalData.srcTransType == 109101) {
                                    parent.frames['deliver-deliver'] && parent.frames['deliver-deliver'].$('#refresh').trigger('click');
                                    parent.frames['deliver-deliver' + originalData.srcId] && parent.frames['deliver-deliver' + originalData.srcId].location.reload();
                                }
                            }
                            parent.frames['money-receipt'] && parent.frames['money-receipt'].$('#search').trigger('click');
                            parent.tab.removeSelectedTabItem();
                        } else {
                            $.dialog.tips('删除失败！' + res.msg, 2);
                        }
                    });
                });
            }
        });
        $('#desBtn').on('click', function(a) {
            a.preventDefault();
            if (Business.verifyRight('RECEIPT_UPDATE')) {
                Public.ajaxPost('/Bill/MonIo/updatedes', {
                    id: originalData.id,
                    newDes: $.trim($('#description').val())
                }, function(res) {
                    if (res && 200 == res.status) {
                        $.dialog.tips('备注保存成功！');
                        parent.frames['money-receipt'] && parent.frames['money-receipt'].$('#search').trigger('click');
                        parent.frames['purchase-purchaseOrder'] && parent.frames['purchase-purchaseOrder'].$('#search').trigger('click');
                        parent.frames['sales-salesOrder'] && parent.frames['sales-salesOrder'].$('#search').trigger('click');
                        parent.frames['purchase-purchaseContract'] && parent.frames['purchase-purchaseContract'].$('#search').trigger('click');
                        parent.frames['purchase-purchaseContractFill'] && parent.frames['purchase-purchaseContractFill'].$('#search').trigger('click');
                        parent.frames['sales-salesContract'] && parent.frames['sales-salesContract'].$('#search').trigger('click');
                        parent.frames['deliver-deliver'] && parent.frames['deliver-deliver'].$('#refresh').trigger('click');
                    } else {
                        $.dialog.tips('备注保存失败！', 2);
                    }
                });
            }
        });
        $('#upFile').uploader({
            action: '/Bill/MonIo/uploadfile',
            mode: 'html5',
            name: 'file',
            formData: {},
            multiple: false,
            auto: true,
            debug: false,
            preview: false,
            showQueue: '#queue',
            fileSizeLimit: '2G',
            fileTypeDesc: '选择文件',
            fileTypeExts: 'jpg,gif,png,csv,xls,xlsx,doc,docx,xps,pdf,zip,rar,iso,7z',
            onSuccess: function(e) {
                var data = JSON.parse(e.data);
                if (data.file) {
                    if (!originalData.files) {
                        originalData.files = [];
                        originalData.files[0] = data.file;
                    } else {
                        originalData.files.unshift(data.file);
                    }
                    THISPAGE.addFiles(originalData.files);
                }
            },
            onError: function(e) {
                e.message && $.dialog.tips('上传失败！' + e.message, 2);
            },
            onCancel: function(e) {
                e.name && $.dialog.tips('已取消文件' + e.name + '上传！', 2);
            },
            onAllComplete: function() {
                $('.upload-queue .queue ul').html('上传成功！').addClass('tc');
            }
        });
        $('.upload-el').on({
            click: function(a) {
                !Business.verifyRight('RECEIPT_UPDATE') && a.preventDefault();
            },
            mouseover: function() {
                $(this).prev().addClass('black');
            },
            mouseleave: function() {
                $(this).prev().removeClass('black');
            }
        });
        $('#files').on('click', '.fileRename', function() {
            if (Business.verifyRight('RECEIPT_UPDATE')) {
                $('.manage-wrapper .renameInput').addClass('dn');
                $('.manage-wrapper .renameSumbit').addClass('dn');
                $('.manage-wrapper .renameCancel').addClass('dn');
                $(this).parent().children('.renameInput').removeClass('dn');
                $(this).parent().children('.renameSumbit').removeClass('dn');
                $(this).parent().children('.renameCancel').removeClass('dn');
            }
        });
        $('#files').on('click', '.renameSumbit', function() {
            var newName = $(this).parent().children('.renameInput').val();
            if (newName) {
                var key = $(this).closest('.file-line').data('key');
                originalData.files[key].name = newName;
                THISPAGE.addFiles(originalData.files);
            }
            $(this).addClass('dn');
            $(this).parent().children('.renameInput').addClass('dn');
            $(this).parent().children('.renameCancel').addClass('dn');
        });
        $('#files').on('click', '.renameCancel', function() {
            $(this).addClass('dn');
            $(this).parent().children('.renameInput').addClass('dn');
            $(this).parent().children('.renameSumbit').addClass('dn');
        });
        $('#files').on('click', '.fileDel', function(a) {
            a.stopPropagation();
            if (Business.verifyRight('RECEIPT_UPDATE')) {
                var upName = $(this).parent().data('upname');
                if (upName !== defaultPage.SYSTEM.uName) {
                    $.dialog.tips('禁止删除别人上传的文件！', 2);
                } else {
                    $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                        var key = $(this).closest('.file-line').data('key');
                        originalData.files.splice(key, 1) && THISPAGE.addFiles(originalData.files);
                    });
                }
            }
        });
        $('.grid-wrap').on('click', '.ui-icon-triangle-1-s', function(a) {
            var b = $(this).siblings();
            var c = b.getCombo();
            setTimeout(function() {
                c.active = !0;
                c.doQuery();
            }, 10);
        });
        $('.grid-wrap').on('click', '.virtual-refund-btn', function(a) {
            a.preventDefault();
            if (Business.verifyRight('RECEIPT_ADD')) {
                var id = $(this).data('id');
                var rowData = $('#gridGoods').jqGrid('getRowData', id);
                if (rowData) {
                    if ($(this).hasClass('hover-gold')) { // 退款
                        curRow && curCol && ($('#grid').jqGrid('saveCell', curRow, curCol), curRow = curCol = null);
                        //追加条目到退款表格
                        var entriesData = {
                            id: 'goods_virtual_refund_' + id,
                            amount: Number(rowData.paidAmount),
                            typeId: originalData.srcTransType == 101111 ? 6 : -1,
                            typeName: originalData.srcTransType == 101111 ? '采购款退款' : '',
                            accId: 1,
                            accName: '无主账户(户头)',
                            wayId: -1,
                            wayName: '',
                            settlementTime: (new Date).format('yyyy-MM-dd hh:mm:ss'),
                            zid: rowData.zid,
                            moneyType: 19, // 先设为19，保存时后台判断为首次预退款，并做系统日志记录后，才置为9并入库。
                            uId: defaultPage.SYSTEM.uId,
                            uName: defaultPage.SYSTEM.uName
                        };
                        $('#grid').jqGrid('addRowData', 'goods_virtual_refund_' + id, entriesData, 'last');
                        $('#grid #goods_virtual_refund_' + id).data('incometypeInfo', {
                            id: entriesData.typeId,
                            name: entriesData.typeName
                        }).data('companyAccountInfo', {
                            id: entriesData.accId,
                            name: entriesData.accName
                        }).data('settlementtypeInfo', {
                            id: entriesData.wayId,
                            name: entriesData.wayName
                        });
                        THISPAGE.calTotal();
                        $("#grid").closest(".ui-jqgrid-bdiv").scrollTop($("#grid").height()); // 自动滚动到表格底部
                        $('#grid tr').removeClass('ui-state-highlight');
                        $('#grid tr#goods_virtual_refund_' + id).addClass('ui-state-highlight');
                        $(this).removeClass('hover-gold').addClass('hover-yellow').attr('title', '撤回本产品刚刚发起的预退款').html('撤回预退款');
                    } else { // 撤回
                        curRow && curCol && ($('#grid').jqGrid('saveCell', curRow, curCol), curRow = curCol = null);
                        $('#grid').jqGrid('delRowData', 'goods_virtual_refund_' + id);
                        THISPAGE.calTotal();
                        $(this).removeClass('hover-yellow').addClass('hover-gold').attr('title', '发起本产品的预退款<p>功能说明：确定已发起退款但退款款项要延后才到账的情况下，可先进行预退款。预退款可随时取消，或退款到账后转为实退款。</p>').html('预退款');
                    }
                } else {
                    $.dialog.tips('行数据为空！', 2);
                }
            }
        });
        $('.grid-wrap').on('click', '.cancel-virtual-refund-btn', function(a) {
            a.preventDefault();
            if (Business.verifyRight('RECEIPT_ADD')) {
                var id = $(this).data('id');
                var rowData = $('#grid').jqGrid('getRowData', id);
                if (rowData) {
                    curRow && curCol && ($('#grid').jqGrid('saveCell', curRow, curCol), curRow = curCol = null);
                    $('#grid #' + id).addClass('dn');
                    $('#grid').jqGrid('setCell', id, 'moneyType', -1);
                    THISPAGE.calTotal();
                } else {
                    $.dialog.tips('行数据为空！', 2);
                }
            }
        });
        $('.grid-wrap').on('click', '.enter-virtual-refund-btn', function(a) {
            a.preventDefault();
            if (Business.verifyRight('RECEIPT_ADD')) {
                var id = $(this).data('id');
                var rowData = $('#grid').jqGrid('getRowData', id);
                if (rowData) {
                    curRow && curCol && ($('#grid').jqGrid('saveCell', curRow, curCol), curRow = curCol = null);
                    rowData.moneyType = 11; // 先设为11，保存时后台判断为首次预退款转实退款，并做系统日志记录后，才置为1并入库。
                    $('#grid').jqGrid('setRowData', id, rowData);
                    THISPAGE.calTotal();
                } else {
                    $.dialog.tips('行数据为空！', 2);
                }
            }
        });
        $('.grid-wrap').on('click', '.refund-btn', function(a) {
            a.preventDefault();
            if (Business.verifyRight('RECEIPT_ADD')) {
                var id = $(this).data('id');
                var rowData = $('#gridGoods').jqGrid('getRowData', id);
                if (rowData) {
                    if ($(this).hasClass('hover-gold')) { // 退款
                        curRow && curCol && ($('#grid').jqGrid('saveCell', curRow, curCol), curRow = curCol = null);
                        //追加条目到退款表格
                        var entriesData = {
                            id: 'goods_refund_' + id,
                            amount: Number(rowData.paidAmount),
                            typeId: originalData.srcTransType == 101111 ? 6 : -1,
                            typeName: originalData.srcTransType == 101111 ? '采购款退款' : '',
                            accId: 1,
                            accName: '无主账户(户头)',
                            wayId: -1,
                            wayName: '',
                            settlementTime: (new Date).format('yyyy-MM-dd hh:mm:ss'),
                            zid: rowData.zid,
                            moneyType: 0,
                            uId: defaultPage.SYSTEM.uId,
                            uName: defaultPage.SYSTEM.uName
                        };
                        $('#grid').jqGrid('addRowData', 'goods_refund_' + id, entriesData, 'last');
                        $('#grid #goods_refund_' + id).data('incometypeInfo', {
                            id: entriesData.typeId,
                            name: entriesData.typeName
                        }).data('companyAccountInfo', {
                            id: entriesData.accId,
                            name: entriesData.accName
                        }).data('settlementtypeInfo', {
                            id: entriesData.wayId,
                            name: entriesData.wayName
                        });
                        THISPAGE.calTotal();
                        $("#grid").closest(".ui-jqgrid-bdiv").scrollTop($("#grid").height()); // 自动滚动到表格底部
                        $('#grid tr').removeClass('ui-state-highlight');
                        $('#grid tr#goods_refund_' + id).addClass('ui-state-highlight');
                        $(this).removeClass('hover-gold').addClass('hover-yellow').attr('title', '撤回本产品刚刚发起的实退款').html('撤回实退款');
                    } else { // 撤回
                        curRow && curCol && ($('#grid').jqGrid('saveCell', curRow, curCol), curRow = curCol = null);
                        $('#grid').jqGrid('delRowData', 'goods_refund_' + id);
                        THISPAGE.calTotal();
                        $(this).removeClass('hover-yellow').addClass('hover-gold').attr('title', '发起本产品的实退款<p>功能说明：确定已退款且退款款项已到账，进行实退款。</p>').html('实退款');
                    }
                } else {
                    $.dialog.tips('行数据为空！', 2);
                }
            }
        });
        $('.grid-wrap').on('click', '.cashback-refund-btn', function(a) {
            a.preventDefault();
            if (Business.verifyRight('RECEIPT_ADD')) {
                var id = $(this).data('id');
                var rowData = $('#gridGoods').jqGrid('getRowData', id);
                if (rowData) {
                    if ($(this).hasClass('hover-gold')) { // 退款
                        curRow && curCol && ($('#grid').jqGrid('saveCell', curRow, curCol), curRow = curCol = null);
                        // 追加条目到退款表格
                        var entriesData = {
                            id: 'goods_cashback_refund_' + id,
                            amount: Number(rowData.paidAmount),
                            typeId: originalData.srcTransType == 101111 ? 6 : -1,
                            typeName: originalData.srcTransType == 101111 ? '采购款退款' : '',
                            accId: 1,
                            accName: '无主账户(户头)',
                            wayId: -1,
                            wayName: '',
                            settlementTime: (new Date).format('yyyy-MM-dd hh:mm:ss'),
                            zid: rowData.zid,
                            moneyType: 12, // 先设为12，保存时后台判断为首次返现退款，并做系统日志记录后，才置为2并入库。
                            uId: defaultPage.SYSTEM.uId,
                            uName: defaultPage.SYSTEM.uName
                        };
                        $('#grid').jqGrid('addRowData', 'goods_cashback_refund_' + id, entriesData, 'last');
                        $('#grid #goods_cashback_refund_' + id).data('incometypeInfo', {
                            id: entriesData.typeId,
                            name: entriesData.typeName
                        }).data('companyAccountInfo', {
                            id: entriesData.accId,
                            name: entriesData.accName
                        }).data('settlementtypeInfo', {
                            id: entriesData.wayId,
                            name: entriesData.wayName
                        });
                        THISPAGE.calTotal();
                        $("#grid").closest(".ui-jqgrid-bdiv").scrollTop($("#grid").height()); // 自动滚动到表格底部
                        $('#grid tr').removeClass('ui-state-highlight');
                        $('#grid tr#goods_cashback_refund_' + id).addClass('ui-state-highlight');
                        $(this).removeClass('hover-gold').addClass('hover-yellow').attr('title', '撤回本产品刚刚发起的返现退款').html('撤回返现退款');
                    } else { // 撤回
                        curRow && curCol && ($('#grid').jqGrid('saveCell', curRow, curCol), curRow = curCol = null);
                        $('#grid').jqGrid('delRowData', 'goods_cashback_refund_' + id);
                        THISPAGE.calTotal();
                        $(this).removeClass('hover-yellow').addClass('hover-gold').attr('title', '发起本产品的返现退款<p>功能说明：确定返现已到账，进行返现退款（不允许采购报价时事先扣掉返现）。</p>').html('返现退款');
                    }
                } else {
                    $.dialog.tips('行数据为空！', 2);
                }
            }
        });
        $('.grid-wrap').on('click', '.single-search-pay-btn', function(a) {
            a.preventDefault();
            if (Business.verifyRight('PAYMENT_UPDATE')) {
                var id = $(this).data('id');
                var rowData = $('#gridGoods').jqGrid('getRowData', id);
                $.dialog({
                    title: '自编序号 ' + rowData.zid + ' 付款记录',
                    content: 'url:/Uri/purchaseContract_singleSearch_moneyInfo',
                    data: {
                        oper: 'view',
                        type: 1,
                        srcId: rowData.iid,
                        zid: rowData.zid
                    },
                    width: 1250,
                    height: 500,
                    max: !1,
                    min: !1,
                    lock: !0,
                    cache: !1
                });
            }
        });
        $('.grid-wrap').on('click', '.single-search-rec-btn', function(a) {
            a.preventDefault();
            if (Business.verifyRight('PAYMENT_UPDATE')) {
                var id = $(this).data('id');
                var rowData = $('#gridGoods').jqGrid('getRowData', id);
                $.dialog({
                    title: '自编序号 ' + rowData.zid + ' 退款记录',
                    content: 'url:/Uri/purchaseContract_singleSearch_moneyInfo',
                    data: {
                        oper: 'view',
                        type: 2,
                        srcId: rowData.iid,
                        zid: rowData.zid
                    },
                    width: 1250,
                    height: 500,
                    max: !1,
                    min: !1,
                    lock: !0,
                    cache: !1
                });
            }
        });
        $(window).on('resize', function(a) {
            Public.autoGridWidth($('#grid'));
            Public.autoGridWidth($('#gridGoods'));
        });
    },
    addFiles: function(files) {
        var fileDiv = '';
        $.each(files, function(k, v) {
            fileDiv += (
                '<div class="file-line" data-key="' + k + '" data-upname="' + v.upName + '">' +
                '<a href="/data/upfile/monIo/' + v.url + '" target="_blank" title="' + v.name + '"><span>' + v.name + '</span><span>' + v.upName + '</span><span>' + v.upTime + '</span></a>' +
                '<input type="text" class="renameInput dn" placeholder="输入新文件名">' +
                '<span class="renameSumbit dn">确定</span><span class="renameCancel dn">取消</span>' +
                '<i class="fileRename iconfont icon-bianji" title="改名"></i><i class="fileDel iconfont icon-shanchu" title="删除"></i>' +
                '</div>'
            );
        });
        $('#files').html(fileDiv);
    },
    calTotalGoods: function() {
        for (var ids = $('#gridGoods').jqGrid('getDataIDs'), limitAmount = 0, amount = 0, i = 0, length = ids.length; length > i; i++) {
            var id = ids[i];
            var rowData = $('#gridGoods').jqGrid('getRowData', id);
            rowData.limitAmount && (limitAmount += parseFloat(rowData.limitAmount));
            rowData.amount && (amount += parseFloat(rowData.amount));
        }
        $('#gridGoods').jqGrid('footerData', 'set', {
            rn: '合计',
            limitAmount: limitAmount,
            amount: amount
        });
    },
    calTotal: function(a) {
        for (var b = $('#grid').jqGrid('getDataIDs'), c = 0, d = 0, e = b.length; e > d; d++) {
            var f = b[d];
            var g = $('#grid').jqGrid('getRowData', f);
            g.moneyType > -1 && g.amount && (c += parseFloat(g.amount));
        }
        $('#grid').jqGrid('footerData', 'set', {
            rn: '合计',
            amount: c
        });
    },
    getPostData: function() {
        curRow && curCol && ($('#grid').jqGrid('saveCell', curRow, curCol), curRow = curCol = null);
        var contactInput = $('#contact').find('input');
        if ('' === contactInput.val() || '(空)' === contactInput.val()) {
            var contactData = {};
            contactData.id = 0;
            contactData.name = '(空)';
            $('#contact').removeData('contactData');
        } else {
            var contactData = $('#contact').data('contactData');
            if (null === contactData) {
                setTimeout(function() {
                    contactInput.focus().select();
                }, 15);
                $.dialog.tips('当前客户不存在！', 2);
                return !1;
            }
        }
        var entriesData = THISPAGE.getEntriesData();
        if (!entriesData) {
            return !1;
        }
        if (entriesData.length > 0) {
            var data = {
                srcTransType: originalData.srcTransType,
                srcId: originalData.srcId,
                srcNo: originalData.srcNo,
                id: originalData.id,
                billDate: $.trim($('#billDate').val()),
                billNo: $.trim($('#billNo').val()),
                billType: 'REC',
                transType: 103101,
                contactId: contactData.id,
                contactName: contactData.name,
                entries: entriesData,
                amount: (originalData.srcTransType == 101101 || originalData.srcTransType == 101102 || originalData.srcTransType == 101111 || originalData.srcTransType == 109101) ? $('#grid').jqGrid('footerData', 'get').amount.replace(/,/g, '') : $.trim($('#amount').val()),
                cleared: $('#grid').jqGrid('footerData', 'get').amount.replace(/,/g, ''),
                description: $.trim($('#description').val()),
                files: originalData.files ? JSON.stringify(originalData.files) : ''
            };
            return data;
        } else {
            $.dialog.tips('结算信息不能为空！', 2);
            return !1;
        }
    },
    getEntriesData: function() {
        for (var entriesData = [], ids = $('#grid').jqGrid('getDataIDs'), i = 0, len = ids.length; len > i; i++) {
            var id = ids[i];
            var rowData = $('#grid').jqGrid('getRowData', id);
            if ('' != rowData.amount && parseFloat(rowData.amount) > 0) {
                if ('' != rowData.typeName && '' != rowData.accName && '' != rowData.wayName && '' != rowData.settlementTime) {
                    var incometypeInfo = $('#grid #' + id).data('incometypeInfo');
                    var companyAccountInfo = $('#grid #' + id).data('companyAccountInfo');
                    var settlementtypeInfo = $('#grid #' + id).data('settlementtypeInfo');
                    if (incometypeInfo.id <= 0) {
                        $.dialog.tips('收款类别识别错误，请再次选择收款类别！', 2);
                        return !1;
                    }
                    if (companyAccountInfo.id <= 0) {
                        $.dialog.tips('收款账户识别错误，请再次选择收款账户！', 2);
                        return !1;
                    }
                    if (settlementtypeInfo.id <= 0) {
                        $.dialog.tips('收款方式识别错误，请再次选择收款方式！', 2);
                        return !1;
                    }
                    if (originalData.srcTransType == 101111 && $.trim(rowData.zid) == '') {
                        $.dialog.tips('第 ' + (i + 1) + ' 行自编序号不能为空！', 2);
                        return !1;
                    }
                    var data = {
                        amount: $.trim(rowData.amount),
                        typeId: incometypeInfo.id,
                        accId: companyAccountInfo.id,
                        wayId: settlementtypeInfo.id,
                        uId: rowData.uId || defaultPage.SYSTEM.uId,
                        settlementTime: rowData.settlementTime,
                        description: $.trim(rowData.description),
                        refundId: rowData.refundId,
                        zid: $.trim(rowData.zid),
                        moneyType: rowData.moneyType
                    };
                    entriesData.push(data);
                } else {
                    $.dialog.tips('请准确选择每行的（收款类别、收款账户、收款方式、收款时间），不能为空！', 2);
                    return !1;
                }
            }
        }
        return entriesData;
    }
};
$(function() {
    if (urlParam.turn) {
        var openerData = window.parent.document.getElementById(urlParam.tabId).openerData;
        originalData = {
            srcTransType: Number(openerData.transType),
            srcId: Number(openerData.id),
            srcNo: openerData.no,
            id: -1,
            status: 'add',
            transType: 103101,
            contactId: Number(openerData.contactId),
            contactName: openerData.contactName,
            amount: Number(openerData.amount),
            cleared: 0,
            entries: 101111 == Number(openerData.transType) ? [] : [{
                id: 1,
                amount: openerData.amount,
                typeId: openerData.typeId || -1,
                typeName: openerData.typeName || '',
                accId: openerData.accId || -1,
                accName: openerData.accName || '',
                wayId: openerData.wayId || -1,
                wayName: openerData.wayName || '',
                uId: openerData.uId || -1,
                uName: openerData.uName || '',
                settlementTime: (new Date).format('yyyy-MM-dd hh:mm:ss')
            }]
        };
        THISPAGE.init(originalData);
        THISPAGE.calTotal();
        $('.wrapper > #div-lock').remove();
    } else if (urlParam.id) {
        $('.wrapper').append('<div id="div-lock"><div class="loading">单据准备中...</div></div>');
        Public.ajaxGet('/Bill/MonIo/query', {
            id: urlParam.id
        }, function(res) {
            if (res && 200 == res.status) {
                originalData = res.data;
                THISPAGE.init(originalData);
                $('.wrapper > #div-lock').remove();
            } else {
                $.dialog.tips(res.msg, 2);
            }
        });
    } else {
        originalData = {
            srcTransType: 0,
            srcId: 0,
            srcNo: '',
            id: -1,
            status: 'add',
            transType: 103101,
            contactId: -1,
            contactName: '',
            amount: 0,
            cleared: 0,
            entries: []
        };
        THISPAGE.init(originalData);
    }
});
