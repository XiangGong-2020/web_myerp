var urlParam = Public.urlParam();
var defaultPage = Public.getDefaultPage();
var qtyPlaces = Number(defaultPage.SYSTEM.qtyPlaces);
var pricePlaces = Number(defaultPage.SYSTEM.pricePlaces);
var amountPlaces = Number(defaultPage.SYSTEM.amountPlaces);
var contactCombo;
var ourselfCombo;
var company = [];
var goodsCombo;
var storageCombo;
var priceCombo;
var originalData;
var curRow;
var curCol;
var btn_draft = '<a id="draft" class="ui-btn bg-night"><i class="iconfont icon-baocun"></i><span>保存为草稿</span></a>';
var btn_final = '<a id="final" class="ui-btn bg-green"><i class="iconfont icon-baocun"></i><span>保存为正式</span></a>';
var btn_save = '<a id="save" class="ui-btn bg-green"><i class="iconfont icon-baocun"></i><span>保存</span></a>';
var btn_edit = '<a id="edit" class="ui-btn bg-green"><i class="iconfont icon-baocun"></i><span>保存</span></a>';
var btn_copy = '<a id="copy" class="ui-btn bg-teal"><i class="iconfont icon-fuzhi"></i><span>复制</span></a>';
var btn_check = '<a id="check" class="ui-btn bg-green"><i class="iconfont icon-shenhe"></i><span>审核</span></a>';
var btn_uncheck = '<a id="uncheck" class="ui-btn bg-red"><i class="iconfont icon-shenhe"></i><span>反审核</span></a>';
var btn_delete = '<a id="delete" class="ui-btn bg-red"><i class="iconfont icon-shanchu"></i><span>删除</span></a>';
var btn_del_srcmoney1 = '<a id="delsrcmoney1" class="ui-btn bg-red"><i class="iconfont icon-shanchu"></i><span>删除收款单</span></a>';
var btn_del_srcmoney2 = '<a id="delsrcmoney2" class="ui-btn bg-red"><i class="iconfont icon-shanchu"></i><span>删除退款单</span></a>';
var btn_del_srcinv1 = '<a id="delsrcinv1" class="ui-btn bg-red"><i class="iconfont icon-shanchu"></i><span>删除发货单</span></a>';
var btn_del_srcinv2 = '<a id="delsrcinv2" class="ui-btn bg-red"><i class="iconfont icon-shanchu"></i><span>删除退货单</span></a>';
var breakShow = !1;
var THISPAGE = {
    init: function(a) {
        THISPAGE.initDom(a);
        THISPAGE.initGrid(a);
        THISPAGE.initEvent();
    },
    initDom: function(a) {
        contactCombo = Business.customerCombo($('#contact'), {
            width: 200
        });
        ourselfCombo = $('#ourself').combo({
            async: false, // 异步 否
            data: '/Base/Company/queryAll',
            ajaxOptions: {
                formatData: function(a) {
                    a.data.rows.forEach(element => {
                        if (element.isDisable == 0) {
                            company.push(element);
                        }
                    });
                    return company;
                }
            },
            value: 'id',
            text: 'name',
            defaultSelected: a.sId > 0 ? ['id', a.sId] : -1,
            width: 200,
            callback: {
                onChange: function(data) {
                    if (data) {
                        $('#ourself').data('ourselfData', data);
                    } else {
                        $('#ourself').removeData('ourselfData');
                    }
                }
            }
        }).getCombo();
        goodsCombo = Business.goodsCombo($('.goodsAuto'), {});
        storageCombo = Business.storageCombo($('.storageAuto'));
        priceCombo = $('.priceAuto').combo({
            data: function() {
                if (!this.input) return [];
                var c = this.input.closest('tr');
                var d = c.data('goodsInfo');
                if (!d) {
                    return [];
                }
                var listPrice = {};
                Public.ajaxPost('/Base/Goods/listPrice', {
                    async: !1,
                    type: 'SO',
                    goodsId: d.id
                }, function(res) {
                    if (res && 200 == res.status) {
                        listPrice = res.listPrice;
                    } else {
                        $.dialog.tips(res.msg, 2);
                    }
                });
                var priceArr = [
                    {id: 0, name: '最近一次销售价：' + listPrice.price1},
                    {id: 1, name: '上上一次销售价：' + listPrice.price2},
                    {id: 2, name: '上上上次销售价：' + listPrice.price3},
                    {id: 3, name: '历史最低销售价：' + listPrice.priceMin},
                    {id: 4, name: '历史最高销售价：' + listPrice.priceMax},
                    {id: 5, name: '历史平均销售价：' + listPrice.priceAvg}
                ];
                return priceArr;
            },
            text: 'name',
            value: 'id',
            editable: !0,
            trigger: !1,
            forceSelection: !1,
            listWidth: 190,
            listHeight: 300,
            callback: {
                onChange: function(data) {
                    if (data) {
                        var price = data.name.toString().split('：')[1];
                        price = price == '无' ? '' : price;
                        this.input.val(price);
                        this.input.parents('tr').data('price', price);
                    }
                },
                onExpand: function() {
                    var price = this.input.val() || this.input.parents('tr').data('price') || '';
                    this.input.val(price);
                }
            }
        }).getCombo();
        if (a.id > 0) {
            a.checkId > 0 ? $('#mark').addClass('bill-has-checked') : $('#mark').removeClass('bill-has-checked');
            $('#toolTop').html(btn_edit + (a.isDraft == 1 ? btn_final : '') + btn_copy + (a.checkId > 0 ? btn_uncheck : btn_check) + btn_delete + (a.srcMoneyId1 > 0 ? btn_del_srcmoney1 : '') + (a.srcInvId1 > 0 ? btn_del_srcinv1 : '') + (a.srcMoneyId2 > 0 ? btn_del_srcmoney2 : '') + (a.srcInvId2 > 0 ? btn_del_srcinv2 : ''));
            $('#contact').data('contactData', {
                id: a.contactId,
                name: a.contactName
            });
            contactCombo.input.val(a.contactName);
            $('#ourself').data('ourselfData', {
                id: a.sId,
                name: a.sName
            });
            ourselfCombo.input.val(a.sName);
            $('#billFrom').val(a.billFrom);
            $('#billNo').val(a.billNo);
            $('#billDate').val(a.billDate);
            $('#deliveryDate').val(a.deliveryDate);
            $('#description').val(a.description);
            $('#desBtn').show();
            $('#disRate').val(a.disRate);
            $('#disAmount').val(a.disAmount);
            $('#deliverAmount').val(a.deliverAmount);
            $('#amount').val(a.amount);
            $('#uName').html(a.uName);
            $('#checkName').html(a.checkName);
            $('#createTime').html(a.createTime);
            $('#modifyTime').html(a.modifyTime);
            a.files && THISPAGE.addFiles(a.files);
        } else {
            $('#toolTop').html(btn_save + btn_draft);
            if (a.contactId && a.contactId > 0) {
                $('#contact').data('contactData', {
                    id: a.contactId,
                    name: a.contactName
                });
                contactCombo.input.val(a.contactName);
            }
            if (a.sId && a.sId > 0) {
                $('#ourself').data('ourselfData', {
                    id: a.sId,
                    name: a.sName
                });
                ourselfCombo.input.val(a.sName);
            }
            $('#billFrom').val('手工录入');
            $('#billDate').val(defaultPage.SYSTEM.endDate);
            $('#deliveryDate').val(Public.addDays(defaultPage.SYSTEM.endDate, 5));
            $('#description').val(a.description || '');
            $('#disRate').val(a.disRate || 0);
            $('#disAmount').val(a.disAmount || 0);
            $('#deliverAmount').val(a.deliverAmount || 0);
            $('#amount').val(a.amount || 0);
            $('#uName').html(defaultPage.SYSTEM.uName || '');
        }
    },
    initGrid: function(a) {
        if (a.id) {
            var b = 1 - a.entries.length;
            if (b > 0) {
                for (var c = 0; b > c; c++) {
                    a.entries.push({});
                }
            }
        }
        THISPAGE.newId = 2;
        $('#grid').jqGrid({
            data: a.entries,
            datatype: 'clientSide',
            colModel: [{
                name: 'operate',
                label: '操作',
                width: 72.8,
                fixed: !0,
                formatter: Public.billsOper
            }, {
                name: 'goods',
                label: '产品',
                width: 300,
                title: !0,
                editable: !0,
                edittype: 'custom',
                editoptions: {
                    custom_element: function(a, b) {
                        var c = $('.goodsAuto')[0];
                        return c;
                    },
                    custom_value: function(a, b, c) {
                        if ('get' === b) {
                            if ('' !== $('.goodsAuto').getCombo().getValue()) {
                                return $(a).val();
                            }
                            var d = $(a).parents('tr');
                            return d.removeData('goodsInfo'), '';
                        }
                        'set' === b && $('input', a).val(c);
                    },
                    handle: function() {
                        $('#initCombo').append($('.goodsAuto').val('').unbind('focus.once'));
                    },
                    trigger: 'ui-icon-ellipsis iconfont icon-shenglvehao'
                },
                classes: 'goods ui-ellipsis',
                formatter: function(a, b, c) {
                    if (a) { // 点选编辑模式
                        var goodsInfo = $('#' + b.rowId).data('goodsInfo');
                        var storageInfo = $('#' + b.rowId).data('storageInfo');
                        var rowData = $('#grid').jqGrid('getRowData', b.rowId);
                        if (goodsInfo && storageInfo && rowData) {
                            var data = {
                                brandName: goodsInfo.brandName,
                                unitName: goodsInfo.unitName,
                                storageId: storageInfo.id || goodsInfo.storageId,
                                storageName: storageInfo.name || goodsInfo.storageName,
                                orderQty: rowData.orderQty || 1,
                                price: rowData.price || goodsInfo.salePrice || 0,
                                discountRate: rowData.discountRate || 0,
                                taxRate: rowData.taxRate || 0
                            };
                            data.discount = data.price * data.orderQty * data.discountRate / 100;
                            data.tax = (data.price * data.orderQty - data.discount) * data.taxRate / 100;
                            data.amount = (data.price * data.orderQty - data.discount) + data.tax;
                            $('#grid').jqGrid('setRowData', b.rowId, data);
                            THISPAGE.calTotal();
                        }
                        return a;
                    } else { // 表格加载模式
                        return c.goodsId > -1 ? (c.invNo + ' ' + c.invName + (c.invSpec ? ' ' + c.invSpec : '') + (c.invSku ? ' ' + c.invSku : '')) : '&nbsp;';
                    }
                }
            }, {
                name: 'imgs',
                label: '图片',
                width: 42,
                fixed: !0,
                sortable: !0,
                align: 'center',
                formatter: function(a, b, c) {
                    if (c.goodsId > 0) {
                        if (a && a.length > 0) {
                            var d = '<span data-id="' + c.goodsId + '" data-imgs="' + a + '" class="ui-icon-pic rel iconfont icon-tupian f24 green poi goodsimgs"><span class="abs db f12 lh16 tc white bg-rgba5 b-r-3" style="min-width:25px;bottom:-2px;">' + a.split(',').length + '张</span></span>';
                        } else {
                            var d = '<span data-id="' + c.goodsId + '" class="ui-icon-pic fl iconfont icon-tupian f24 silver poi" title="添加图片"></span>';
                        }
                        return d;
                    } else if (c.id > 0) {
                        if (a && a.length > 0) {
                            var d = '<span data-id="' + c.id + '" data-imgs="' + a + '" class="ui-icon-pic rel iconfont icon-tupian f24 green poi goodsimgs"><span class="abs db f12 lh16 tc white bg-rgba5 b-r-3" style="min-width:25px;bottom:-2px;">' + a.split(',').length + '张</span></span>';
                        } else {
                            var d = '<span data-id="' + c.id + '" class="ui-icon-pic fl iconfont icon-tupian f24 silver poi" title="添加图片"></span>';
                        }
                        return d;
                    } else {
                        return '';
                    }
                }
            }, {
                name: 'brandName',
                label: '品牌',
                width: 80,
                fixed: !0,
                editable: !1,
                classes: 'ui-ellipsis'
            }, {
                name: 'unitName',
                label: '单位',
                width: 42,
                fixed: !0,
                editable: !1
            }, {
                name: 'storageId',
                label: '仓库Id',
                hidden: !0
            }, {
                name: 'storageName',
                label: '仓库',
                nameExt: '<small id="batchStorage" class="ml5">批量</small>',
                width: 100,
                fixed: !0,
                editable: !0,
                edittype: 'custom',
                editoptions: {
                    custom_element: function(a, b) {
                        var c = $('.storageAuto')[0];
                        return c;
                    },
                    custom_value: function(a, b, c) {
                        if ('get' === b) {
                            if ('' !== $('.storageAuto').getCombo().getValue()) {
                                return $(a).val();
                            }
                            var d = $(a).parents('tr');
                            return d.removeData('storageInfo'), '';
                        }
                        'set' === b && $('input', a).val(c);
                    },
                    handle: function() {
                        $('#initCombo').append($('.storageAuto').val(''));
                    },
                    trigger: 'ui-icon-triangle-1-s iconfont icon-xiajiantou'
                }
            }, {
                name: 'orderQty',
                label: '数量',
                width: 80,
                fixed: !0,
                editable: !0,
                formatter: 'number',
                formatoptions: {
                    showZero: !0,
                    decimalPlaces: qtyPlaces
                }
            }, {
                name: 'price',
                label: '销售价',
                width: 100,
                fixed: !0,
                editable: !0,
                edittype: 'custom',
                editoptions: {
                    custom_element: function(a, b) {
                        var c = $('.priceAuto')[0];
                        return c;
                    },
                    custom_value: function(a, b, c) {
                        if ('get' === b) {
                            return $(a).val() || '';
                        }
                        'set' === b && $('input', a).val(c);
                    },
                    handle: function() {
                        $('#initCombo').append($('.priceAuto').val(''));
                    },
                    trigger: 'ui-icon-triangle-1-s iconfont icon-xiajiantou'
                },
                formatter: 'currency',
                formatoptions: {
                    showZero: !0,
                    decimalPlaces: pricePlaces
                }
            }, {
                name: 'discountRate',
                label: '折扣率(%)',
                width: 70,
                fixed: !0,
                editable: !0,
                formatter: 'integer'
            }, {
                name: 'discount',
                label: '折扣额',
                width: 80,
                fixed: !0,
                editable: !0,
                formatter: 'currency',
                formatoptions: {
                    showZero: !0,
                    decimalPlaces: amountPlaces
                }
            }, {
                name: 'taxRate',
                label: '税率(%)',
                nameExt: '<small id="batchTaxRate" class="ml5">批量</small>',
                width: 85,
                fixed: !0,
                editable: !0,
                formatter: 'integer'
            }, {
                name: 'tax',
                label: '税额',
                width: 100,
                fixed: !0,
                editable: !0,
                formatter: 'currency',
                formatoptions: {
                    showZero: !0,
                    decimalPlaces: amountPlaces
                }
            }, {
                name: 'amount',
                label: '金额',
                width: 80,
                fixed: !0,
                editable: !0,
                formatter: 'currency',
                formatoptions: {
                    showZero: !0,
                    decimalPlaces: amountPlaces
                }
            }, {
                name: 'description',
                label: '备注',
                width: 100,
                fixed: !0,
                title: !0,
                editable: !0,
                classes: 'ui-ellipsis gray'
            }, {
                name: 'id',
                label: 'ID',
                width: 0,
                hidden: !0
            }],
            cmTemplate: {sortable: !1, title: !1},
            localReader: {root: 'rows', records: 'records', repeatitems: !1, id: 'id'},
            jsonReader: {root: 'data.entries', records: 'records', repeatitems: !1, id: 'id'},
            autowidth: !0,
            height: '100%',
            altRows: !1,
            gridview: !0,
            rownumbers: !0,
            rownumWidth: 40,
            onselectrow: !0,
            shrinkToFit: !0,
            forceFit: !1,
            rowNum: 10000,
            cellEdit: !1,
            cellsubmit: 'clientArray',
            loadonce: !0,
            footerrow: !0,
            userDataOnFooter: !0,
            loadComplete: function(a) {
                if (urlParam.id > 0) {
                    var b = a.rows;
                    var c = b.length;
                    THISPAGE.newId = c + 1;
                    for (var d = 0; c > d; d++) {
                        var e = b[d].id;
                        var f = b[d];
                        if ($.isEmptyObject(f)) {
                            break;
                        }
                        var g = $.extend(!0, {
                            no: f.invNo,
                            name: f.invName,
                            spec: f.invSpec,
                            sku: f.invSku
                        }, f);
                        g.id = f.goodsId;
                        $('#' + e).data('goodsInfo', g).data('storageInfo', {
                            id: f.storageId,
                            name: f.storageName
                        });
                    }
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
            beforeEditCell: function(rowId, cellName, cellValue, iRow, iCol) {
                curRow = iRow;
                curCol = iCol;
            },
            afterEditCell: function(rowId, cellName, cellValue, iRow, iCol) {
                THISPAGE.curID = rowId;
                if ('goods' === cellName) {
                    $('#' + iRow + '_goods', '#grid').val(cellValue);
                }
                if ('storageName' === cellName) {
                    $('#' + iRow + '_storageName', '#grid').val(cellValue);
                    storageCombo.selectByText(cellValue);
                }
                if ('price' === cellName) {
                    $('#' + iRow + '_price', '#grid').val(cellValue);
                }
            },
            afterSaveCell: function(rowId, cellName, cellValue, iRow, iCol) {
                switch (cellName) {
                    case 'orderQty':
                        cellValue = parseFloat(cellValue);
                        var rowData = $('#grid').jqGrid('getRowData', rowId);
                        var price = parseFloat(rowData.price);
                        var discountRate = parseFloat(rowData.discountRate);
                        var taxRate = parseFloat(rowData.taxRate);
                        if ($.isNumeric(cellValue) && $.isNumeric(price) && $.isNumeric(discountRate) && $.isNumeric(taxRate)) {
                            var amount = cellValue * price;
                            var discount = amount * discountRate / 100;
                            var amountD = amount - discount;
                            var tax = amountD * taxRate / 100;
                            var amountT = amountD + tax;
                            $('#grid').jqGrid('setRowData', rowId, {
                                discount: discount,
                                tax: tax,
                                amount: amountT
                            });
                            THISPAGE.calTotal();
                        } else {
                            $.dialog.alert('第 ' + iRow + ' 行有输入格式错误！请认真检查修正！');
                        }
                        break;
                    case 'price':
                        cellValue = parseFloat(cellValue);
                        var rowData = $('#grid').jqGrid('getRowData', rowId);
                        var orderQty = parseFloat(rowData.orderQty);
                        var discountRate = parseFloat(rowData.discountRate);
                        var taxRate = parseFloat(rowData.taxRate);
                        if ($.isNumeric(cellValue) && $.isNumeric(orderQty) && $.isNumeric(discountRate) && $.isNumeric(taxRate)) {
                            var amount = cellValue * orderQty;
                            var discount = amount * discountRate / 100;
                            var amountD = amount - discount;
                            var tax = amountD * taxRate / 100;
                            var amountT = amountD + tax;
                            $('#grid').jqGrid('setRowData', rowId, {
                                discount: discount,
                                tax: tax,
                                amount: amountT
                            });
                            THISPAGE.calTotal();
                        } else {
                            $.dialog.alert('第 ' + iRow + ' 行有输入格式错误！请认真检查修正！');
                        }
                        break;
                    case 'discountRate':
                        cellValue = parseFloat(cellValue);
                        var rowData = $('#grid').jqGrid('getRowData', rowId);
                        var orderQty = parseFloat(rowData.orderQty);
                        var price = parseFloat(rowData.price);
                        var taxRate = parseFloat(rowData.taxRate);
                        if ($.isNumeric(cellValue) && $.isNumeric(orderQty) && $.isNumeric(price) && $.isNumeric(taxRate)) {
                            var amount = orderQty * price;
                            var discount = amount * cellValue / 100;
                            var amountD = amount - discount;
                            var tax = amountD * taxRate / 100;
                            var amountT = amountD + tax;
                            $('#grid').jqGrid('setRowData', rowId, {
                                discount: discount,
                                tax: tax,
                                amount: amountT
                            });
                            THISPAGE.calTotal();
                        } else {
                            $.dialog.alert('第 ' + iRow + ' 行有输入格式错误！请认真检查修正！');
                        }
                        break;
                    case 'discount':
                        cellValue = parseFloat(cellValue);
                        var rowData = $('#grid').jqGrid('getRowData', rowId);
                        var orderQty = parseFloat(rowData.orderQty);
                        var price = parseFloat(rowData.price);
                        var taxRate = parseFloat(rowData.taxRate);
                        if ($.isNumeric(cellValue) && $.isNumeric(orderQty) && $.isNumeric(price) && $.isNumeric(taxRate)) {
                            var amount = orderQty * price;
                            var amountD = amount - cellValue;
                            var discountRate = cellValue > 0 ? (100 * cellValue / amount).toFixed(2) : 0;
                            var tax = amountD * taxRate / 100;
                            var amountT = amountD + tax;
                            $('#grid').jqGrid('setRowData', rowId, {
                                discountRate: discountRate,
                                tax: tax,
                                amount: amountT
                            });
                            THISPAGE.calTotal();
                        } else {
                            $.dialog.alert('第 ' + iRow + ' 行有输入格式错误！请认真检查修正！');
                        }
                        break;
                    case 'taxRate':
                        cellValue = parseFloat(cellValue);
                        var rowData = $('#grid').jqGrid('getRowData', rowId);
                        var orderQty = parseFloat(rowData.orderQty);
                        var price = parseFloat(rowData.price);
                        var discountRate = parseFloat(rowData.discountRate);
                        if ($.isNumeric(cellValue) && $.isNumeric(orderQty) && $.isNumeric(price) && $.isNumeric(discountRate)) {
                            var amount = orderQty * price;
                            var discount = amount * discountRate / 100;
                            var amountD = amount - discount;
                            var tax = amountD * cellValue / 100;
                            var amountT = amountD + tax;
                            $('#grid').jqGrid('setRowData', rowId, {
                                discount: discount,
                                tax: tax,
                                amount: amountT
                            });
                            THISPAGE.calTotal();
                        } else {
                            $.dialog.alert('第 ' + iRow + ' 行有输入格式错误！请认真检查修正！');
                        }
                        break;
                    case 'tax':
                        cellValue = parseFloat(cellValue);
                        var rowData = $('#grid').jqGrid('getRowData', rowId);
                        var orderQty = parseFloat(rowData.orderQty);
                        var price = parseFloat(rowData.price);
                        var discountRate = parseFloat(rowData.discountRate);
                        if ($.isNumeric(cellValue) && $.isNumeric(orderQty) && $.isNumeric(price) && $.isNumeric(discountRate) && $.isNumeric(taxRate)) {
                            var amount = orderQty * price;
                            var discount = amount * discountRate / 100;
                            var amountD = amount - discount;
                            var taxRate = cellValue > 0 ? (100 * cellValue / amountD).toFixed(2) : 0;
                            var amountT = amountD + cellValue;
                            $('#grid').jqGrid('setRowData', rowId, {
                                discount: discount,
                                taxRate: taxRate,
                                amount: amountT
                            });
                            THISPAGE.calTotal();
                        } else {
                            $.dialog.alert('第 ' + iRow + ' 行有输入格式错误！请认真检查修正！');
                        }
                        break;
                    case 'amount':
                        cellValue = parseFloat(cellValue);
                        var rowData = $('#grid').jqGrid('getRowData', rowId);
                        var orderQty = parseFloat(rowData.orderQty);
                        var discount = parseFloat(rowData.discount);
                        var tax = parseFloat(rowData.tax);
                        if ($.isNumeric(cellValue) && $.isNumeric(orderQty) && $.isNumeric(discount) && $.isNumeric(tax)) {
                            var amount = cellValue - tax + discount;
                            var price = amount / orderQty;
                            var discountRate = discount > 0 ? (100 * discount / amount).toFixed(2) : 0;
                            var taxRate = tax > 0 ? (100 * tax / (amount - discount)).toFixed(2) : 0;
                            $('#grid').jqGrid('setRowData', rowId, {
                                price: price,
                                discountRate: discountRate,
                                taxRate: taxRate
                            });
                            THISPAGE.calTotal();
                        } else {
                            $.dialog.alert('第 ' + iRow + ' 行有输入格式错误！请认真检查修正！');
                        }
                        break;
                    default:
                        break;
                }
            }
        });
        $('#grid').jqGrid('setGridParam', {
            cellEdit: !0
        });
    },
    initEvent: function() {
        Business.billsEvent(this, 'salesOrder');
        laydate.render({
            elem: "#billDate, #deliveryDate",
        });
        $('#disRate').on({
            keypress: function(a) {
                Public.numerical(a);
            },
            keyup: function() {
                var a = Number($(this).val());
                var b = Number($('#grid').jqGrid('footerData', 'get')['amount'].replace(/,/g, ''));
                var c = Number($.trim($('#deliverAmount').val()));
                var d = (b * a / 100).toFixed(amountPlaces);
                var e = (b + c - d).toFixed(amountPlaces);
                $('#disAmount').val(d);
                $('#amount').val(e);
            },
            focus: function() {
                this.select();
            },
            blur: function(a) {
                if ($(this).val() < 0 || $(this).val() > 100) {
                    $.dialog.tips('额外优惠率为 0% ~ 100% 之间！', 2);
                    $(this).focus();
                }
            }
        });
        $('#disAmount').on({
            keypress: function(a) {
                Public.numerical(a);
            },
            keyup: function() {
                var a = Number($(this).val());
                var b = Number($('#grid').jqGrid('footerData', 'get')['amount'].replace(/,/g, ''));
                var c = Number($.trim($('#deliverAmount').val()));
                var d = (b + c - a).toFixed(amountPlaces);
                if (b) {
                    var e = a / b * 100;
                    $('#disRate').val(e.toFixed(amountPlaces));
                    $('#amount').val(d);
                }
            },
            focus: function() {
                this.select();
            },
            blur: function(a) {
                if ($(this).val() < 0 || $(this).val() > Number($('#grid').jqGrid('footerData', 'get')['amount'].replace(/,/g, ''))) {
                    $.dialog.tips('额外优惠额只能为 0 ~ 合计金额之间！', 2);
                    $(this).focus();
                }
            }
        });
        $('#deliverAmount').on({
            keypress: function(a) {
                Public.numerical(a);
            },
            keyup: function() {
                var a = Number($(this).val());
                var b = Number($('#grid').jqGrid('footerData', 'get')['amount'].replace(/,/g, ''));
                var c = Number($.trim($('#disAmount').val()));
                var d = (a + b - c).toFixed(amountPlaces);
                if (b) {
                    $('#amount').val(d);
                }
            },
            focus: function() {
                this.select();
            },
            blur: function(a) {
                if ($(this).val() < 0) {
                    $.dialog.tips('运费不能为负数！', 2);
                    $(this).focus();
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
        $('.grid-wrap').on('mouseenter', '.goodsimgs', function(a) {
            a.preventDefault();
            if ($(this).has('.goodsimg').length == 0) {
                var imgs = $(this).data('imgs').split(',');
                if (imgs.length > 0) {
                    var imgsDiv = '<div class="goodsimg">';
                    imgs.forEach(element => {
                        if (element.startsWith('http')) {
                            imgsDiv += '<img src="' + element + '" width="auto" height="80" class="fl m5 b-r-3">';
                        } else {
                            imgsDiv += '<img src="/data/upfile/goods/img/' + element + '" width="auto" height="80" class="fl m5 b-r-3">';
                        }
                    });
                    imgsDiv += '</div>';
                    $(this).append(imgsDiv);
                }
            };
            var top = $(this).offset().top - 38;
            $(this).children('.goodsimg').css('top', top).show();
        });
        $('.grid-wrap').on('mouseleave', '.goodsimgs', function(a) {
            a.preventDefault();
            $(this).children('.goodsimg').hide();
        });
        $('.grid-wrap').on('click', '.ui-icon-pic', function(a) {
            a.preventDefault();
            var id = $(this).data('id');
            $.dialog({
                title: '产品图片',
                content: 'url:/Uri/goods_img_upload',
                data: {
                    title: '产品图片',
                    id: id,
                    callback: function(a, b) {
                        var entriesData = THISPAGE.getEntriesData(a);
                        if (entriesData) {
                            $.each(entriesData, function(k, v) {
                                if (v.goodsId == a.id) {
                                    $('#grid').jqGrid('setRowData', v.rowId, {imgs: a.imgs, goodsId: v.goodsId});
                                }
                            })
                        }
                        b && b.api.close();
                    }
                },
                width: 600,
                height: 370,
                max: !1,
                min: !1,
                lock: !0,
                cache: !1
            });
        });
        $('.wrapper').on('click', '#save', function(a) {
            a.preventDefault();
            if (Business.verifyRight('SO_ADD')) {
                var data = THISPAGE.getPostData();
                if (data) {
                    $('.wrapper').append('<div id="div-lock"><div class="loading">保存中...</div></div>');
                    data.isDraft = 0;
                    Public.ajaxPost('/Bill/InvSo/add', {
                        postData: JSON.stringify(data)
                    }, function(res) {
                        if (res && 200 == res.status) {
                            parent.parent.$.dialog.tips('新增成功！');
                            parent.frames['sales-salesOrder'] && parent.frames['sales-salesOrder'].$('#search').trigger('click');
                            parent.tab.removeSelectedTabItem();
                        } else {
                            $('.wrapper > #div-lock').remove();
                            $.dialog.tips('新增失败！' + res.msg, 2);
                        }
                    });
                }
            }
        });
        $('.wrapper').on('click', '#draft', function(a) {
            a.preventDefault();
            if (Business.verifyRight('SO_ADD')) {
                var data = THISPAGE.getPostData();
                if (data) {
                    $('.wrapper').append('<div id="div-lock"><div class="loading">保存中...</div></div>');
                    data.isDraft = 1;
                    Public.ajaxPost('/Bill/InvSo/add', {
                        postData: JSON.stringify(data)
                    }, function(res) {
                        if (res && 200 == res.status) {
                            parent.parent.$.dialog.tips('新增成功！');
                            parent.frames['sales-salesOrder'] && parent.frames['sales-salesOrder'].$('#search').trigger('click');
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
            if (Business.verifyRight('SO_UPDATE')) {
                var data = THISPAGE.getPostData();
                if (data) {
                    $('.wrapper').append('<div id="div-lock"><div class="loading">保存中...</div></div>');
                    data.isDraft = originalData.isDraft;
                    Public.ajaxPost('/Bill/InvSo/update', {
                        postData: JSON.stringify(data)
                    }, function(res) {
                        if (res && 200 == res.status) {
                            parent.parent.$.dialog.tips('更新成功！');
                            parent.frames['sales-salesOrder'] && parent.frames['sales-salesOrder'].$('#search').trigger('click');
                            parent.tab.removeSelectedTabItem();
                        } else {
                            $('.wrapper > #div-lock').remove();
                            $.dialog.tips('更新失败！' + res.msg, 2);
                        }
                    });
                } else {
                    $.dialog.tips('数据提取失败！', 2);
                }
            }
        });
        $('.wrapper').on('click', '#final', function(a) {
            a.preventDefault();
            if (Business.verifyRight('SO_UPDATE')) {
                var data = THISPAGE.getPostData();
                if (data) {
                    $('.wrapper').append('<div id="div-lock"><div class="loading">保存中...</div></div>');
                    data.isDraft = 0;
                    Public.ajaxPost('/Bill/InvSo/update', {
                        postData: JSON.stringify(data)
                    }, function(res) {
                        if (res && 200 == res.status) {
                            parent.parent.$.dialog.tips('更新成功！');
                            parent.frames['sales-salesOrder'] && parent.frames['sales-salesOrder'].$('#search').trigger('click');
                            parent.tab.removeSelectedTabItem();
                        } else {
                            $('.wrapper > #div-lock').remove();
                            $.dialog.tips('更新失败！' + res.msg, 2);
                        }
                    });
                } else {
                    $.dialog.tips('数据提取失败！', 2);
                }
            }
        });
        $('.wrapper').on('click', '#check', function(a) {
            a.stopPropagation();
            a.preventDefault();
            if (Business.verifyRight('SO_CHECK')) {
                Public.ajaxPost('/Bill/InvSo/check', {
                    id: originalData.id,
                    checkId: defaultPage.SYSTEM.uId,
                    checkName: defaultPage.SYSTEM.uName,
                    billNo: originalData.billNo
                }, function(res) {
                    if (res && 200 == res.status) {
                        $.dialog.tips('审核成功！');
                        originalData.checkId = defaultPage.SYSTEM.uId;
                        originalData.checkName = defaultPage.SYSTEM.uName;
                        $('#mark').addClass('bill-has-checked');
                        $('#check').after(btn_uncheck).remove();
                        $('#checkName').html(defaultPage.SYSTEM.uName);
                        parent.frames['sales-salesOrder'] && parent.frames['sales-salesOrder'].$('#search').trigger('click');
                    } else {
                        $.dialog.tips('审核失败！' + res.msg, 2);
                    }
                });
            }
        });
        $('.wrapper').on('click', '#uncheck', function(a) {
            a.stopPropagation();
            a.preventDefault();
            if (Business.verifyRight('SO_UNCHECK')) {
                Public.ajaxPost('/Bill/InvSo/check', {
                    id: originalData.id,
                    checkId: 0,
                    checkName: '',
                    billNo: originalData.billNo
                }, function(res) {
                    if (res && 200 == res.status) {
                        $.dialog.tips('反审核成功！');
                        originalData.checkId = 0;
                        originalData.checkName = '';
                        $('#mark').removeClass('bill-has-checked');
                        $('#uncheck').after(btn_check).remove();
                        $('#checkName').html('');
                        parent.frames['sales-salesOrder'] && parent.frames['sales-salesOrder'].$('#search').trigger('click');
                    } else {
                        $.dialog.tips('反审核失败！' + res.msg, 2);
                    }
                });
            }
        });
        $('.wrapper').on('click', '#delete', function(a) {
            a.preventDefault();
            if (Business.verifyRight('SO_DELETE')) {
                if (originalData.id) {
                    $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                        Public.ajaxGet('/Bill/InvSo/delete', {
                            id: originalData.id
                        }, function(res) {
                            if (res && 200 == res.status) {
                                parent.$.dialog.tips('删除成功！');
                                parent.frames['sales-salesOrder'] && parent.frames['sales-salesOrder'].$('#search').trigger('click');
                                parent.tab.removeSelectedTabItem();
                            } else {
                                $.dialog.tips('删除失败！' + res.msg, 2);
                            }
                        });
                    });
                }
            }
        });
        $('.wrapper').on('click', '#delsrcmoney1', function(a) {
            a.preventDefault();
            if (Business.verifyRight('RECEIPT_DELETE')) {
                originalData.srcMoneyId1 && $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                    Public.ajaxGet('/Bill/MonIo/delete', {
                        id: originalData.srcMoneyId1
                    }, function(res) {
                        if (res && 200 == res.status) {
                            $.dialog.tips('删除成功！');
                            originalData.moneyStatus1 = 0;
                            originalData.srcMoneyId1 = 0;
                            originalData.srcMoneyNo1 = '';
                            $('#delsrcmoney1').remove();
                            parent.frames['sales-salesOrder'] && parent.frames['sales-salesOrder'].$('#search').trigger('click');
                            parent.frames['money-receipt'] && parent.frames['money-receipt'].$('#search').trigger('click');
                            parent.frames['money-receipt' + originalData.srcMoneyId1] && parent.tab.removeTabItem('money-receipt' + originalData.srcMoneyId1);
                        } else {
                            $.dialog.tips('删除失败！' + res.msg, 2);
                        }
                    });
                });
            }
        });
        $('.wrapper').on('click', '#delsrcmoney2', function(a) {
            a.preventDefault();
            if (Business.verifyRight('PAYMENT_DELETE')) {
                originalData.srcMoneyId2 && $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                    Public.ajaxGet('/Bill/MonIo/delete', {
                        id: originalData.srcMoneyId2
                    }, function(res) {
                        if (res && 200 == res.status) {
                            $.dialog.tips('删除成功！');
                            originalData.moneyStatus2 = 0;
                            originalData.srcMoneyId2 = 0;
                            originalData.srcMoneyNo2 = '';
                            $('#delsrcmoney2').remove();
                            parent.frames['sales-salesOrder'] && parent.frames['sales-salesOrder'].$('#search').trigger('click');
                            parent.frames['money-payment'] && parent.frames['money-payment'].$('#search').trigger('click');
                            parent.frames['money-payment' + originalData.srcMoneyId2] && parent.tab.removeTabItem('money-payment' + originalData.srcMoneyId2);
                        } else {
                            $.dialog.tips('删除失败！' + res.msg, 2);
                        }
                    });
                });
            }
        });
        $('.wrapper').on('click', '#delsrcinv1', function(a) {
            a.preventDefault();
            if (Business.verifyRight('SA_DELETE')) {
                originalData.srcInvId1 && $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                    Public.ajaxGet('/Bill/InvSa/delete', {
                        id: originalData.srcInvId1
                    }, function(res) {
                        if (res && 200 == res.status) {
                            $.dialog.tips('删除成功！');
                            originalData.goodsStatus1 = 0;
                            originalData.srcInvId1 = 0;
                            originalData.srcInvNo1 = '';
                            $('#delsrcinv1').remove();
                            parent.frames['sales-salesOrder'] && parent.frames['sales-salesOrder'].$('#search').trigger('click');
                            parent.frames['sales-sales'] && parent.frames['sales-sales'].$('#search').trigger('click');
                            parent.frames['sales-sales' + originalData.srcInvId1] && parent.tab.removeTabItem('sales-sales' + originalData.srcInvId1);
                        } else {
                            $.dialog.tips('删除失败！' + res.msg, 2);
                        }
                    });
                });
            }
        });
        $('.wrapper').on('click', '#delsrcinv2', function(a) {
            a.preventDefault();
            if (Business.verifyRight('SA_DELETE')) {
                originalData.srcInvId2 && $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                    Public.ajaxGet('/Bill/InvSa/delete', {
                        id: originalData.srcInvId2
                    }, function(res) {
                        if (res && 200 == res.status) {
                            $.dialog.tips('删除成功！');
                            originalData.goodsStatus2 = 0;
                            originalData.srcInvId2 = 0;
                            originalData.srcInvNo2 = '';
                            $('#delsrcinv2').remove();
                            parent.frames['sales-salesOrder'] && parent.frames['sales-salesOrder'].$('#search').trigger('click');
                            parent.frames['sales-salesBack'] && parent.frames['sales-salesBack'].$('#search').trigger('click');
                            parent.frames['sales-salesBack' + originalData.srcInvId2] && parent.tab.removeTabItem('sales-salesBack' + originalData.srcInvId2);
                        } else {
                            $.dialog.tips('删除失败！' + res.msg, 2);
                        }
                    });
                });
            }
        });
        $('#copy').on('click', function(a) {
            a.preventDefault();
            if (Business.verifyRight('SO_ADD')) {
                var newTabId = (new Date).format("yyyyMMddhhmmss");
                parent.tab.addTabItem({
                    tabid: 'sales-salesOrder' + newTabId,
                    text: '<i class="iconfont icon-danye f14 mr5"></i>销售订单',
                    url: '/Uri/salesOrder_manage?copyFromSalesOrder&id=' + originalData.id + '&tabId=sales-salesOrder' + newTabId
                });
            }
        });
        $('.wrapper').on('click', '#breakshow', function(a) {
            a.preventDefault();
            if (breakShow) {
                breakShow = !1;
                $(this).children('span').text('显示详细表格');
                $('.ui-jqgrid-bdiv td').removeClass('ui-break').addClass('ui-ellipsis');
            } else {
                breakShow = !0;
                $(this).children('span').text('显示缩略表格');
                $('.ui-jqgrid-bdiv td').removeClass('ui-ellipsis').addClass('ui-break');
            }
        });
        $('#exportGrid').on('click', function(a) {
            var billNo = $('#billNo').val();
            var href = '/Bill/InvSo/exportInfoGrid?id=' + originalData.id + '&billNo=' + billNo;
            $(this).attr('href', href);
        });
        $('#desBtn').on('click', function(a) {
            a.preventDefault();
            if (Business.verifyRight('SO_UPDATE')) {
                if ($.inArray('SYS_ADMIN', defaultPage.SYSTEM.rights) !== -1 || originalData.uId == defaultPage.SYSTEM.uId) {
                    originalData.id && Public.ajaxPost('/Bill/InvSo/updatedes', {
                        id: originalData.id,
                        newDes: $.trim($('#description').val())
                    }, function(res) {
                        if (res && 200 == res.status) {
                            $.dialog.tips('备注保存成功！');
                            parent.frames['sales-salesOrder'] && parent.frames['sales-salesOrder'].$('#search').trigger('click');
                        } else {
                            $.dialog.tips('备注保存失败！' + res.msg, 2);
                        }
                    });
                } else {
                    $.dialog.alert('不可编辑别人录入的销售订单！仅本人或管理员可编辑！');
                }
            }
        });
        $('#upFile').uploader({
            action: '/Bill/InvSo/uploadfile',
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
                !Business.verifyRight('SO_UPDATE') && a.preventDefault();
            },
            mouseover: function() {
                $(this).prev().addClass('black');
            },
            mouseleave: function() {
                $(this).prev().removeClass('black');
            }
        });
        $('#files').on('click', '.fileRename', function() {
            if (Business.verifyRight('SO_UPDATE')) {
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
            if (Business.verifyRight('SO_UPDATE')) {
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
        $('#batchTaxRate').powerFloat({
            eventType: 'click',
            hoverHold: false,
            reverseSharp: true,
            target: function() {
                if (curRow !== null && curCol !== null) {
                    $('#grid').jqGrid('saveCell', curRow, curCol);
                    curRow = null;
                    curCol = null;
                };
                return $('#taxRateBox');
            }
        });
        $('#subTaxRate').on('click', function(a) {
            var taxRate = Number($('#taxRate').val());
            if (taxRate < 0) {
                $.dialog.tips('税率应为正数！', 2);
            } else {
                var ids = $('#grid').jqGrid('getDataIDs');
                for (var i = 0, len = ids.length; i < len; i++) {
                    var id = ids[i];
                    var row = $('#grid').jqGrid('getRowData', id);
                    var $_id = $('#' + id);
                    if (row.goods === '' || $_id.data('goodsInfo') === undefined) {
                        continue; // 跳过未选择产品的行
                    };
                    var setData = {};
                    var amount = row.orderQty * row.price - row.discount;
                    setData['taxRate'] = taxRate;
                    setData['tax'] = amount * taxRate / 100;
                    setData['amount'] = amount * (100 + taxRate) / 100;
                    $('#grid').jqGrid('setRowData', id, setData);
                };
                $.powerFloat.hide();
                THISPAGE.calTotal();
            }
        });
        $(window).on('resize', function() {
            Public.autoGridWidth($('#grid'));
        });
        $(window).on('scroll', function() {
            var nTop = $(".grid-wrap").offset().top - 100;
            var scrTop = $(this).scrollTop();
            if (scrTop >= nTop) {
                $(".ui-jqgrid .ui-jqgrid-hdiv").css({'position': 'fixed', 'z-index': 2, 'padding': '0 16px', 'top': '50px', 'left': 0, 'right': 0});
            } else {
                $(".ui-jqgrid .ui-jqgrid-hdiv").css({'position': 'relative', 'padding': 0, 'top': 'auto', 'left': 0, 'right': 0});
            }
        });
    },
    addFiles: function(files) {
        var fileDiv = '';
        $.each(files, function(k, v) {
            fileDiv += (
                '<div class="file-line" data-key="' + k + '" data-upname="' + v.upName + '">' +
                '<a href="/data/upfile/invSo/' + v.url + '" target="_blank" title="' + v.name + '"><span>' + v.name + '</span><span>' + v.upName + '</span><span>' + v.upTime + '</span></a>' +
                '<input type="text" class="renameInput dn" placeholder="输入新文件名">' +
                '<span class="renameSumbit dn">确定</span><span class="renameCancel dn">取消</span>' +
                '<i class="fileRename iconfont icon-bianji" title="改名"></i><i class="fileDel iconfont icon-shanchu" title="删除"></i>' +
                '</div>'
            );
        });
        $('#files').html(fileDiv);
    },
    calTotal: function() {
        for (var a = $('#grid').jqGrid('getDataIDs'), b = 0, c = 0, d = 0, e = 0, f = 0, g = a.length; g > f; f++) {
            var h = a[f];
            var i = $('#grid').jqGrid('getRowData', h);
            i.orderQty && (b += parseFloat(i.orderQty));
            i.discount && (c += parseFloat(i.discount));
            i.tax && (d += parseFloat(i.tax));
            i.amount && (e += parseFloat(i.amount));
        }
        $('#grid').jqGrid('footerData', 'set', {
            rn: '合计',
            orderQty: b,
            discount: c,
            tax: d,
            amount: e
        });
        var j = (e - Number($('#disAmount').val()) + Number($('#deliverAmount').val())).toFixed(2);
        $('#amount').val(j);
    },
    getPostData: function(a) {
        curRow && curCol && ($('#grid').jqGrid('saveCell', curRow, curCol), curRow = curCol = null);
        var billDate = $.trim($('#billDate').val());
        var deliveryDate = $.trim($('#deliveryDate').val());
        if (deliveryDate <= billDate) {
            $.dialog.tips('交货限期不合理！请录入合理的交货限期！', 2);
            return !1;
        }
        var entriesData = THISPAGE.getEntriesData();
        var contactInput = $('#contact').find('input');
        var contactData = $('#contact').data('contactData');
        var ourselfInput = $('#ourself').find('input');
        var ourselfData = $('#ourself').data('ourselfData');
        if ('' === contactInput.val()) {
            $.dialog.tips('请选择客户！', 2);
            $('#contact').removeData('contactData');
            contactInput.focus();
            return !1;
        }
        if (!contactData || !contactData.id) {
            $.dialog.tips('当前客户不存在！', 2);
            setTimeout(function() {
                contactInput.focus();
            }, 15);
            return !1;
        }
        if ('' === ourselfInput.val()) {
            $('#ourself').removeData('ourselfData');
            ourselfCombo.active = !0;
            ourselfCombo.doQuery();
            ourselfCombo.input.focus();
            $.dialog.tips('请选择销售方！', 2);
            return !1;
        }
        if (!ourselfData || ourselfData.id < 0) {
            $.dialog.tips('当前销售方不存在！', 2);
            setTimeout(function() {
                ourselfInput.focus().select();
            }, 10);
            return !1;
        }
        if (!entriesData) {
            return !1;
        }
        if (entriesData.length > 0) {
            var data = {
                id: originalData.id,
                contactId: contactData.id,
                contactName: contactData.name,
                sId: ourselfData.id,
                sName: ourselfData.name,
                billDate: $.trim($('#billDate').val()),
                deliveryDate: $.trim($('#deliveryDate').val()),
                billNo: $.trim($('#billNo').val()),
                transType: 101201,
                entries: entriesData,
                totalQty: $('#grid').jqGrid('footerData', 'get').orderQty.replace(/,/g, ''),
                totalDiscount: $('#grid').jqGrid('footerData', 'get').discount.replace(/,/g, ''),
                totalTax: $('#grid').jqGrid('footerData', 'get').tax.replace(/,/g, ''),
                totalAmount: $('#grid').jqGrid('footerData', 'get').amount.replace(/,/g, ''),
                description: $.trim($('#description').val()),
                files: originalData.files ? JSON.stringify(originalData.files) : '',
                disRate: $.trim($('#disRate').val() || 0),
                disAmount: $.trim($('#disAmount').val() || 0),
                deliverAmount: $.trim($('#deliverAmount').val() || 0),
                amount: $.trim($('#amount').val())
            };
            return data;
        } else {
            return $.dialog.tips('产品信息不能为空！', 2), $('#grid').jqGrid('editCell', 1, 2, !0), !1;
        }
    },
    getEntriesData: function() {
        for (var invNos = [], entriesData = [], ids = $('#grid').jqGrid('getDataIDs'), i = 0, len = ids.length; len > i; i++) {
            var id = ids[i];
            var rowData = $('#grid').jqGrid('getRowData', id);
            if ('' !== rowData.goods) {
                var goodsInfo = $('#' + id).data('goodsInfo');
                if (goodsInfo) {
                    var storageInfo = $('#' + id).data('storageInfo');
                    if (!storageInfo || !storageInfo.id) {
                        $.dialog.tips('请选择相应的仓库！', 2);
                        $('#grid').jqGrid('editCellByColName', id, 'storageName');
                        return !1;
                    }
                    var data = {
                        id: rowData.id || -1,
                        goodsId: goodsInfo.id,
                        invNo: goodsInfo.no,
                        invName: goodsInfo.name,
                        invSpec: goodsInfo.spec || '',
                        invSku: goodsInfo.sku || '',
                        brandName: goodsInfo.brandName || '',
                        unitName: goodsInfo.unitName || '',
                        storageId: storageInfo.id || -1,
                        storageName: storageInfo.name || '',
                        orderQty: rowData.orderQty,
                        price: rowData.price,
                        discountRate: rowData.discountRate,
                        discount: rowData.discount,
                        taxRate: rowData.taxRate,
                        tax: rowData.tax,
                        amount: rowData.amount,
                        description: rowData.description
                    };
                    entriesData.push(data);
                    invNos.push(goodsInfo.no);
                }
            }
        }
        // 提取重复产品并处理(暂不中断业务执行，因为网店订单有时候是会存在重复产品的情况)
        /* let repeatNos = [];
        for (let k in invNos) {
            if (invNos.indexOf(invNos[k]) !== invNos.lastIndexOf(invNos[k]) && repeatNos.indexOf(invNos[k]) === -1) {
                repeatNos.push(invNos[k]);
            }
        }
        if (repeatNos.length > 0) {
            $.dialog.tips('编码为：' + repeatNos.toString() + ' 的产品重复！', 2);
            return !1;
        } */
        return entriesData;
    }
};
$(function() {
    if (urlParam.id) {
        $('.wrapper').append('<div id="div-lock"><div class="loading">单据准备中...</div></div>');
        Public.ajaxGet('/Bill/InvSo/query', {
            id: urlParam.id
        }, function(res) {
            if (res && 200 == res.status) {
                originalData = res.data;
                if (urlParam.copyFromSalesOrder) {
                    originalData.id = -1;
                    $.dialog.alert('此为通过复制生成的新单，请按需增删、编辑各项信息，检查无误后方可保存！');
                }
                THISPAGE.init(originalData);
                $('.wrapper > #div-lock').remove();
            } else {
                $.dialog.tips(res.msg, 2);
            }
        });
    } else {
        originalData = {
            id: -1,
            status: 'add',
            contact: 0,
            transType: 101201,
            entries: [],
            totalQty: 0,
            totalDiscount: 0,
            totalAmount: 0,
            totalTax: 0,
            totalTaxAmount: 0,
            disRate: 0,
            disAmount: '0.00',
            deliverAmount: '0.00',
            amount: '0.00',
            arrears: '0.00',
            rpAmount: '0.00',
            accId: 0
        };
        THISPAGE.init(originalData);
    }
});
