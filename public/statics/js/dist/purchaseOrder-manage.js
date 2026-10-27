var urlParam = Public.urlParam();
var defaultPage = Public.getDefaultPage();
var qtyPlaces = Number(defaultPage.SYSTEM.qtyPlaces);
var pricePlaces = Number(defaultPage.SYSTEM.pricePlaces);
var amountPlaces = Number(defaultPage.SYSTEM.amountPlaces);
var contactCombo;
var ourselfCombo;
var company = [];
var classes;
var goodsCombo;
var storageCombo;
var qtyCombo;
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
var btn_del_srcmoney1 = '<a id="delsrcmoney1" class="ui-btn bg-red"><i class="iconfont icon-shanchu"></i><span>删除付款单</span></a>';
var btn_del_srcmoney2 = '<a id="delsrcmoney2" class="ui-btn bg-red"><i class="iconfont icon-shanchu"></i><span>删除退款单</span></a>';
var btn_del_srcinv1 = '<a id="delsrcinv1" class="ui-btn bg-red"><i class="iconfont icon-shanchu"></i><span>删除收货单</span></a>';
var btn_del_srcinv2 = '<a id="delsrcinv2" class="ui-btn bg-red"><i class="iconfont icon-shanchu"></i><span>删除退货单</span></a>';
var THISPAGE = {
    init: function(a) {
        THISPAGE.initDom(a);
        THISPAGE.initGrid(a);
        THISPAGE.initEvent();
    },
    initDom: function(a) {
        contactCombo = Business.supplierCombo($('#contact'), {
            width: 200
        });
        ourselfCombo = $('#ourself').combo({
            async: false, // 异步 否
            data: '/Base/Company/queryAll',
            ajaxOptions: {
                formatData: function(a) {
                    a.data.rows.forEach(element => {
                        if (element.id > 0 && element.isDisable == 0) {
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
        classes = $('#classes').cssRadio({
            callback: function(a) {
                if ('101101' == a.find('input').val()) {
                    $('#srcOcNoCon').hide().addClass('dn');
                } else {
                    $('#srcOcNoCon').show().removeClass('dn');
                }
            }
        });
        101101 == a.transType ? classes.setValue(0) : classes.setValue(1);
        goodsCombo = Business.goodsCombo($('.goodsAuto'), {});
        storageCombo = Business.storageCombo($('.storageAuto'));
        qtyCombo = $('.qtyAuto').combo({
            data: function() {
                if (!this.input) return [];
                var c = this.input.closest('tr');
                var d = c.data('goodsInfo');
                if (!d) {
                    return [];
                }
                function getLastYearUsedQty() {
                    var resultQty;
                    Public.ajaxPost('/Base/Goods/listQty', {
                        async: !1,
                        which: 'LastYear',
                        type: 'PO',
                        goodsId: d.id
                    }, function(data) {
                        if (200 === data.status) {
                            resultQty = parseInt(data.qty);
                        }
                    });
                    return resultQty;
                }
                var lastYearUsedQty = getLastYearUsedQty();
                var suggestPurQty1 = lastYearUsedQty;
                var suggestPurQty2 = lastYearUsedQty / 2;
                var suggestPurQty3 = lastYearUsedQty / 4;
                var h = [];
                return h.push(
                    {name: '最近一年消耗量：' + parseInt(lastYearUsedQty), id: 0},
                    {name: '建议一年采购量：' + parseInt(suggestPurQty1), id: 1},
                    {name: '建议半年采购量：' + parseInt(suggestPurQty2), id: 2},
                    {name: '建议季度采购量：' + parseInt(suggestPurQty3), id: 3}
                ), h;
            },
            value: 'id',
            text: 'name',
            editable: !0,
            trigger: !1,
            defaultFlag: !1,
            forceSelection: !1,
            listWidth: 190,
            callback: {
                onChange: function(data) {
                    if (data) {
                        var orderQty = data.name.toString().split('：')[1];
                        orderQty = orderQty == '无' ? '' : orderQty;
                        this.input.val(orderQty);
                        this.input.parents('tr').data('orderQty', orderQty);
                    }
                },
                onExpand: function() {
                    var orderQty = this.input.val() || this.input.parents('tr').data('orderQty') || '';
                    this.input.val(orderQty);
                }
            }
        }).getCombo();
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
                    type: 'PO',
                    goodsId: d.id
                }, function(res) {
                    if (res && 200 == res.status) {
                        listPrice = res.listPrice;
                    } else {
                        $.dialog.tips(res.msg, 2);
                    }
                });
                var priceArr = [
                    {id: 0, name: '最近一次采购价：' + listPrice.price1},
                    {id: 1, name: '上上一次采购价：' + listPrice.price2},
                    {id: 2, name: '上上上次采购价：' + listPrice.price3},
                    {id: 3, name: '历史最低采购价：' + listPrice.priceMin},
                    {id: 4, name: '历史最高采购价：' + listPrice.priceMax},
                    {id: 5, name: '历史平均采购价：' + listPrice.priceAvg}
                ];
                return priceArr;
            },
            value: 'id',
            text: 'name',
            editable: !0,
            trigger: !1,
            defaultFlag: !1,
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
            a.isDraft == 1 && $('#edit span').after('<b>当前为草稿</b>');
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
            $('#srcOcNo').val(a.srcOcNo);
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
            $('#billDate').val(defaultPage.SYSTEM.endDate);
            $('#deliveryDate').val(Public.addDays(defaultPage.SYSTEM.endDate, 3));
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
                                price: rowData.price || goodsInfo.purPrice || 0,
                                discountRate: rowData.discountRate || 0
                            };
                            data.discount = data.price * data.orderQty * data.discountRate / 100;
                            data.amount = data.price * data.orderQty * (100 - data.discountRate) / 100;
                            $('#grid').jqGrid('setRowData', b.rowId, data);
                            THISPAGE.calTotal();
                        }
                        return a;
                    } else { // 表格加载模式
                        return c.goodsId > 0 ? (c.invNo + ' ' + c.invName + (c.invSpec ? ' ' + c.invSpec : '') + (c.invSku ? ' ' + c.invSku : '')) : '&nbsp;';
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
                edittype: 'custom',
                editoptions: {
                    custom_element: function(a, b) {
                        var c = $('.qtyAuto')[0];
                        return c;
                    },
                    custom_value: function(a, b, c) {
                        if ('get' === b) {
                            return $(a).val() || '';
                        }
                        'set' === b && $('input', a).val(c);
                    },
                    handle: function() {
                        $('#initCombo').append($('.qtyAuto').val(''));
                    },
                    trigger: 'ui-icon-triangle-1-s iconfont icon-xiajiantou'
                },
                formatter: 'number',
                formatoptions: {
                    showZero: !0,
                    decimalPlaces: qtyPlaces
                }
            }, {
                name: 'price',
                label: '采购价',
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
                width: 80,
                fixed: !0,
                editable: !0,
                formatter: 'integer'
            }, {
                name: 'discount',
                label: '折扣额',
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
                width: 100,
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
            gridview: !0,
            rownumbers: !0,
            onselectrow: !1,
            shrinkToFit: !0,
            forceFit: !1,
            rowNum: 10000,
            cellEdit: !1,
            cellsubmit: 'clientArray',
            loadonce: !0,
            footerrow: !0,
            userDataOnFooter: !0,
            loadComplete: function(a) {
                if (urlParam.id > 0 || urlParam.turnBygoodList) {
                    var b = a.rows;
                    var c = b.length;
                    THISPAGE.newId = c + 1;
                    for (var d = 0; c > d; d++) {
                        var e = b[d].id,
                            f = b[d];
                        if ($.isEmptyObject(b[d])) {
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
                if ('orderQty' === cellName) {
                    $('#' + iRow + '_orderQty', '#grid').val(cellValue);
                    // 确保DOM完全渲染后再触发点击
                    setTimeout(function() {
                        // 定位当前行的触发箭头，模拟点击
                        $('#' + iRow + '_orderQty', '#grid')
                            .next('.icon-xiajiantou')
                            .click();
                    }, 0);
                }
                if ('price' === cellName) {
                    $('#' + iRow + '_price', '#grid').val(cellValue);
                    // 确保DOM完全渲染后再触发点击
                    setTimeout(function() {
                        // 定位当前行的触发箭头，模拟点击
                        $('#' + iRow + '_price', '#grid')
                            .next('.icon-xiajiantou')
                            .click();
                    }, 0);
                }
            },
            afterSaveCell: function(rowId, cellName, cellValue, iRow, iCol) {
                switch (cellName) {
                    case 'orderQty':
                        cellValue = parseFloat(cellValue);
                        var rowData = $('#grid').jqGrid('getRowData', rowId);
                        if (originalData.moneyStatus1 > 0) { //如果已付款，编辑数量时，改变单价，不改变折扣率、折扣额、金额，这样就不用动付款信息
                            var amount = parseFloat(rowData.amount);
                            var discount = parseFloat(rowData.discount);
                            if ($.isNumeric(cellValue) && $.isNumeric(amount) && $.isNumeric(discount)) {
                                var price = (amount + discount) / cellValue;
                                $('#grid').jqGrid('setRowData', rowId, {
                                    price: price
                                });
                                THISPAGE.calTotal();
                            } else {
                                $.dialog.alert('第 ' + iRow + ' 行数据有输入格式错误！请认真检查修正！');
                            }
                        } else { //如果未付款，编辑数量时，改变折扣额、金额，不改变单价、折扣率
                            var price = parseFloat(rowData.price);
                            var discountRate = parseFloat(rowData.discountRate);
                            if ($.isNumeric(cellValue) && $.isNumeric(price) && $.isNumeric(discountRate)) {
                                var amount = cellValue * price;
                                var discount = amount * discountRate / 100;
                                var amountD = amount - discount;
                                $('#grid').jqGrid('setRowData', rowId, {
                                    discount: discount,
                                    amount: amountD
                                });
                                THISPAGE.calTotal();
                            } else {
                                $.dialog.alert('第 ' + iRow + ' 行数据有输入格式错误！请认真检查修正！');
                            }
                        }
                        break;
                    case 'price':
                        cellValue = parseFloat(cellValue);
                        var rowData = $('#grid').jqGrid('getRowData', rowId);
                        var orderQty = parseFloat(rowData.orderQty);
                        var discountRate = parseFloat(rowData.discountRate);
                        if ($.isNumeric(cellValue) && $.isNumeric(orderQty) && $.isNumeric(discountRate)) {
                            var amount = cellValue * orderQty;
                            var discount = amount * discountRate / 100;
                            var amountD = amount - discount;
                            $('#grid').jqGrid('setRowData', rowId, {
                                discount: discount,
                                amount: amountD
                            });
                            THISPAGE.calTotal();
                        } else {
                            $.dialog.alert('第 ' + iRow + ' 行数据有输入格式错误！请认真检查修正！');
                        }
                        break;
                    case 'discountRate':
                        cellValue = parseFloat(cellValue);
                        var rowData = $('#grid').jqGrid('getRowData', rowId);
                        var orderQty = parseFloat(rowData.orderQty);
                        var price = parseFloat(rowData.price);
                        if ($.isNumeric(cellValue) && $.isNumeric(orderQty) && $.isNumeric(price)) {
                            var amount = orderQty * price;
                            var discount = amount * cellValue / 100;
                            var amountD = amount - discount;
                            $('#grid').jqGrid('setRowData', rowId, {
                                discount: discount,
                                amount: amountD
                            });
                            THISPAGE.calTotal();
                        } else {
                            $.dialog.alert('第 ' + iRow + ' 行数据有输入格式错误！请认真检查修正！');
                        }
                        break;
                    case 'discount':
                        cellValue = parseFloat(cellValue);
                        var rowData = $('#grid').jqGrid('getRowData', rowId);
                        var orderQty = parseFloat(rowData.orderQty);
                        var price = parseFloat(rowData.price);
                        if ($.isNumeric(cellValue) && $.isNumeric(orderQty) && $.isNumeric(price)) {
                            var amount = orderQty * price;
                            var discountRate = cellValue > 0 ? (100 * cellValue / amount).toFixed(2) : 0;
                            var amountD = amount - cellValue;
                            $('#grid').jqGrid('setRowData', rowId, {
                                discountRate: discountRate,
                                amount: amountD
                            });
                            THISPAGE.calTotal();
                        } else {
                            $.dialog.alert('第 ' + iRow + ' 行数据有输入格式错误！请认真检查修正！');
                        }
                        break;
                    case 'amount':
                        cellValue = parseFloat(cellValue);
                        var rowData = $('#grid').jqGrid('getRowData', rowId);
                        var discount = parseFloat(rowData.discount);
                        var orderQty = parseFloat(rowData.orderQty);
                        if ($.isNumeric(cellValue) && $.isNumeric(discount) && $.isNumeric(orderQty)) {
                            var amount = cellValue + discount;
                            var price = amount / orderQty;
                            var discountRate = discount > 0 ? (100 * discount / amount).toFixed(2) : 0;
                            $('#grid').jqGrid('setRowData', rowId, {
                                price: price,
                                discountRate: discountRate
                            });
                            THISPAGE.calTotal();
                        } else {
                            $.dialog.alert('第 ' + iRow + ' 行数据有输入格式错误！请认真检查修正！');
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
        Business.billsEvent(this, 'purchaseOrder');
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
                };
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
            blur: function(b) {
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
            if (Business.verifyRight('PO_ADD')) {
                var data = THISPAGE.getPostData();
                if (data) {
                    $('.wrapper').append('<div id="div-lock"><div class="loading">保存中...</div></div>');
                    data.isDraft = 0;
                    Public.ajaxPost('/Bill/InvPo/add', {
                        postData: JSON.stringify(data)
                    }, function(res) {
                        if (res && 200 == res.status) {
                            parent.parent.$.dialog.tips('新增成功！');
                            parent.frames['purchase-purchaseOrder'] && parent.frames['purchase-purchaseOrder'].$('#search').trigger('click');
                            parent.frames['purchase-purchaseReq'] && parent.frames['purchase-purchaseReq'].$('#search').trigger('click');
                            parent.tab.removeSelectedTabItem();
                        } else {
                            $('.wrapper > #div-lock').remove();
                            $.dialog.tips('新增失败！' + res.msg, 2);
                        }
                    });
                } else {
                    $.dialog.tips('数据提取失败！', 2);
                }
            }
        });
        $('.wrapper').on('click', '#draft', function(a) {
            a.preventDefault();
            if (Business.verifyRight('PO_ADD')) {
                var data = THISPAGE.getPostData();
                if (data) {
                    $('.wrapper').append('<div id="div-lock"><div class="loading">保存中...</div></div>');
                    data.isDraft = 1;
                    Public.ajaxPost('/Bill/InvPo/add', {
                        postData: JSON.stringify(data)
                    }, function(res) {
                        if (res && 200 == res.status) {
                            parent.parent.$.dialog.tips('新增成功！');
                            parent.frames['purchase-purchaseOrder'] && parent.frames['purchase-purchaseOrder'].$('#search').trigger('click');
                            parent.frames['purchase-purchaseReq'] && parent.frames['purchase-purchaseReq'].$('#search').trigger('click');
                            parent.tab.removeSelectedTabItem();
                        } else {
                            $('.wrapper > #div-lock').remove();
                            $.dialog.tips('新增失败！' + res.msg, 2);
                        }
                    });
                } else {
                    $.dialog.tips('数据提取失败！', 2);
                }
            }
        });
        $('.wrapper').on('click', '#edit', function(a) {
            a.preventDefault();
            if (Business.verifyRight('PO_UPDATE')) {
                // 已离职采购人员，可由具有相同权限的采购同事代为编辑其之前的录入的单据
                var data = THISPAGE.getPostData();
                if (data) {
                    $('.wrapper').append('<div id="div-lock"><div class="loading">保存中...</div></div>');
                    data.isDraft = originalData.isDraft;
                    Public.ajaxPost('/Bill/InvPo/update', {
                        postData: JSON.stringify(data)
                    }, function(res) {
                        if (res && 200 == res.status) {
                            parent.parent.$.dialog.tips('更新成功！');
                            parent.frames['purchase-purchaseOrder'] && parent.frames['purchase-purchaseOrder'].$('#search').trigger('click');
                            parent.frames['purchase-purchaseReq'] && parent.frames['purchase-purchaseReq'].$('#search').trigger('click');
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
            if (Business.verifyRight('PO_UPDATE')) {
                var data = THISPAGE.getPostData();
                if (data) {
                    $('.wrapper').append('<div id="div-lock"><div class="loading">保存中...</div></div>');
                    data.isDraft = 0;
                    Public.ajaxPost('/Bill/InvPo/update', {
                        postData: JSON.stringify(data)
                    }, function(res) {
                        if (res && 200 == res.status) {
                            parent.parent.$.dialog.tips('更新成功！');
                            parent.frames['purchase-purchaseOrder'] && parent.frames['purchase-purchaseOrder'].$('#search').trigger('click');
                            parent.frames['purchase-purchaseReq'] && parent.frames['purchase-purchaseReq'].$('#search').trigger('click');
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
            if (Business.verifyRight('PO_CHECK')) {
                Public.ajaxPost('/Bill/InvPo/check', {
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
                        parent.frames['purchase-purchaseOrder'] && parent.frames['purchase-purchaseOrder'].$('#search').trigger('click');
                    } else {
                        $.dialog.tips('审核失败！' + res.msg, 2);
                    }
                });
            }
        });
        $('.wrapper').on('click', '#uncheck', function(a) {
            a.stopPropagation();
            a.preventDefault();
            if (Business.verifyRight('PO_UNCHECK')) {
                Public.ajaxPost('/Bill/InvPo/check', {
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
                        parent.frames['purchase-purchaseOrder'] && parent.frames['purchase-purchaseOrder'].$('#search').trigger('click');
                    } else {
                        $.dialog.tips('反审核失败！' + res.msg, 2);
                    }
                });
            }
        });
        $('.wrapper').on('click', '#delete', function(a) {
            a.preventDefault();
            if (Business.verifyRight('PO_DELETE')) {
                originalData.id && $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                    Public.ajaxGet('/Bill/InvPo/delete', {
                        id: originalData.id
                    }, function(res) {
                        if (res && 200 == res.status) {
                            parent.$.dialog.tips('删除成功！');
                            parent.frames['purchase-purchaseOrder'] && parent.frames['purchase-purchaseOrder'].$('#search').trigger('click');
                            parent.frames['purchase-purchaseReq'] && parent.frames['purchase-purchaseReq'].$('#search').trigger('click');
                            parent.tab.removeSelectedTabItem();
                        } else {
                            $.dialog.tips('删除失败！' + res.msg, 2);
                        }
                    });
                });
            }
        });
        $('.wrapper').on('click', '#delsrcmoney1', function(a) {
            a.preventDefault();
            if (Business.verifyRight('PAYMENT_DELETE')) {
                originalData.id && $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                    Public.ajaxGet('/Bill/MonIo/delete', {
                        id: originalData.srcMoneyId1
                    }, function(res) {
                        if (res && 200 == res.status) {
                            $.dialog.tips('删除成功！');
                            originalData.moneyStatus1 = 0;
                            originalData.srcMoneyId1 = 0;
                            originalData.srcMoneyNo1 = '';
                            $('#delsrcmoney1').remove();
                            parent.frames['purchase-purchaseOrder'] && parent.frames['purchase-purchaseOrder'].$('#search').trigger('click');
                            parent.frames['money-payment'] && parent.frames['money-payment'].$('#search').trigger('click');
                            parent.frames['money-payment' + originalData.srcMoneyId1] && parent.tab.removeTabItem('money-payment' + originalData.srcMoneyId1);
                        } else {
                            $.dialog.tips('删除失败！' + res.msg, 2);
                        }
                    });
                });
            }
        });
        $('.wrapper').on('click', '#delsrcmoney2', function(a) {
            a.preventDefault();
            if (Business.verifyRight('RECEIPT_DELETE')) {
                originalData.id && $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                    Public.ajaxGet('/Bill/MonIo/delete', {
                        id: originalData.srcMoneyId2
                    }, function(res) {
                        if (res && 200 == res.status) {
                            $.dialog.tips('删除成功！');
                            originalData.moneyStatus2 = 0;
                            originalData.srcMoneyId2 = 0;
                            originalData.srcMoneyNo2 = '';
                            $('#delsrcmoney2').remove();
                            parent.frames['purchase-purchaseOrder'] && parent.frames['purchase-purchaseOrder'].$('#search').trigger('click');
                            parent.frames['money-receipt'] && parent.frames['money-receipt'].$('#search').trigger('click');
                            parent.frames['money-receipt' + originalData.srcMoneyId2] && parent.tab.removeTabItem('money-receipt' + originalData.srcMoneyId2);
                        } else {
                            $.dialog.tips('删除失败！' + res.msg, 2);
                        }
                    });
                });
            }
        });
        $('.wrapper').on('click', '#delsrcinv1', function(a) {
            a.preventDefault();
            if (Business.verifyRight('PU_DELETE')) {
                originalData.id && $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                    Public.ajaxGet('/Bill/InvPu/delete', {
                        id: originalData.srcInvId1
                    }, function(res) {
                        if (res && 200 == res.status) {
                            $.dialog.tips('删除成功！');
                            originalData.goodsStatus1 = 0;
                            originalData.srcInvId1 = 0;
                            originalData.srcInvNo1 = '';
                            $('#delsrcinv1').remove();
                            parent.frames['purchase-purchaseOrder'] && parent.frames['purchase-purchaseOrder'].$('#search').trigger('click');
                            parent.frames['purchase-purchase'] && parent.frames['purchase-purchase'].$('#search').trigger('click');
                            parent.frames['purchase-purchase' + originalData.srcInvId1] && parent.tab.removeTabItem('purchase-purchase' + originalData.srcInvId1);
                        } else {
                            $.dialog.tips('删除失败！' + res.msg, 2);
                        }
                    });
                });
            }
        });
        $('.wrapper').on('click', '#delsrcinv2', function(a) {
            a.preventDefault();
            if (Business.verifyRight('PU_DELETE')) {
                originalData.id && $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                    Public.ajaxGet('/Bill/InvPu/delete', {
                        id: originalData.srcInvId2
                    }, function(res) {
                        if (res && 200 == res.status) {
                            $.dialog.tips('删除成功！');
                            originalData.goodsStatus2 = 0;
                            originalData.srcInvId2 = 0;
                            originalData.srcInvNo2 = '';
                            $('#delsrcinv2').remove();
                            parent.frames['purchase-purchaseOrder'] && parent.frames['purchase-purchaseOrder'].$('#search').trigger('click');
                            parent.frames['purchase-purchaseBack'] && parent.frames['purchase-purchaseBack'].$('#search').trigger('click');
                            parent.frames['purchase-purchaseBack' + originalData.srcInvId2] && parent.tab.removeTabItem('purchase-purchaseBack' + originalData.srcInvId2);
                        } else {
                            $.dialog.tips('删除失败！' + res.msg, 2);
                        }
                    });
                });
            }
        });
        $('#copy').on('click', function(a) {
            a.preventDefault();
            if (Business.verifyRight('PO_ADD')) {
                var newTabId = (new Date).format("yyyyMMddhhmmss");
                parent.tab.addTabItem({
                    tabid: 'purchase-purchaseOrder' + newTabId,
                    text: '<i class="iconfont icon-danye f14 mr5"></i>采购订单',
                    url: '/Uri/purchaseOrder_manage?copyFromPurchaseOrder&id=' + originalData.id + '&tabId=purchase-purchaseOrder' + newTabId
                });
            }
        });
        $('#gotoInventoryHistory').on('click', function(a) {
            a.preventDefault();
            if (Business.verifyRight('INV_HISTORY')) {
                parent.tab.addTabItem({
                    tabid: 'inventory-invHistory',
                    text: '<i class="iconfont icon-kucunliushui f14 mr5"></i>库存流水',
                    url: '/Uri/inventory_history'
                });
            }
        });
        $('#desBtn').on('click', function(a) {
            a.preventDefault();
            if (Business.verifyRight('PO_UPDATE')) {
                if ($.inArray('SYS_ADMIN', defaultPage.SYSTEM.rights) !== -1 || originalData.uId == defaultPage.SYSTEM.uId) {
                    originalData.id && Public.ajaxPost('/Bill/InvPo/updatedes', {
                        id: originalData.id,
                        newDes: $.trim($('#description').val())
                    }, function(res) {
                        if (res && 200 == res.status) {
                            $.dialog.tips('备注保存成功！');
                            parent.frames['purchase-purchaseOrder'] && parent.frames['purchase-purchaseOrder'].$('#search').trigger('click');
                        } else {
                            $.dialog.tips('备注保存失败！' + res.msg, 2);
                        }
                    });
                } else {
                    $.dialog.alert('不可编辑别人录入的采购订单！仅本人或管理员可编辑！');
                }
            }
        });
        $('#upFile').uploader({
            action: '/Bill/InvPo/uploadfile',
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
                !Business.verifyRight('PO_UPDATE') && a.preventDefault();
            },
            mouseover: function() {
                $(this).prev().addClass('black');
            },
            mouseleave: function() {
                $(this).prev().removeClass('black');
            }
        });
        $('#files').on('click', '.fileRename', function() {
            if (Business.verifyRight('PO_UPDATE')) {
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
            if (Business.verifyRight('PO_UPDATE')) {
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
                '<a href="/data/upfile/invPo/' + v.url + '" target="_blank" title="' + v.name + '"><span>' + v.name + '</span><span>' + v.upName + '</span><span>' + v.upTime + '</span></a>' +
                '<input type="text" class="renameInput dn" placeholder="输入新文件名">' +
                '<span class="renameSumbit dn">确定</span><span class="renameCancel dn">取消</span>' +
                '<i class="fileRename iconfont icon-bianji" title="改名"></i><i class="fileDel iconfont icon-shanchu" title="删除"></i>' +
                '</div>'
            );
        });
        $('#files').html(fileDiv);
    },
    calTotal: function() {
        for (var a = $('#grid').jqGrid('getDataIDs'), b = 0, c = 0, d = 0, e = 0, f = 0, g = 0, h = a.length; h > g; g++) {
            var i = a[g];
            var j = $('#grid').jqGrid('getRowData', i);
            j.orderQty && (b += parseFloat(j.orderQty));
            j.discount && (c += parseFloat(j.discount));
            j.amount && (d += parseFloat(j.amount));
        };
        $('#grid').jqGrid('footerData', 'set', {
            rn: '合计',
            orderQty: b,
            discount: c,
            amount: d
        });
        var k = (d + Number($('#deliverAmount').val()) - Number($('#disAmount').val())).toFixed(2);
        $('#amount').val(k);
    },
    getPostData: function() {
        curRow && curCol && (
            $('#grid').jqGrid('saveCell', curRow, curCol),
            curRow = curCol = null
        );
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
            $.dialog.tips('请选择商家！', 2);
            $('#contact').removeData('contactData');
            contactInput.focus();
            return !1;
        }
        if (!contactData || !contactData.id) {
            $.dialog.tips('当前商家不存在！', 2);
            setTimeout(function() {
                contactInput.focus();
            }, 10);
            return !1;
        }
        if ('' === ourselfInput.val()) {
            $('#ourself').removeData('ourselfData');
            ourselfCombo.active = !0;
            ourselfCombo.doQuery();
            ourselfCombo.input.focus();
            $.dialog.tips('请选择采购方！', 2);
            return !1;
        }
        if (!ourselfData || ourselfData.id < 0) {
            setTimeout(function() {
                ourselfInput.focus().select();
            }, 10);
            $.dialog.tips('当前采购方不存在！', 2);
            return !1;
        }
        if (!entriesData) {
            return !1;
        }
        if (entriesData.length > 0) {
            var data = {
                srcPurReqId: originalData.srcPurReqId || 0,
                id: originalData.id,
                contactId: contactData.id,
                contactName: contactData.name,
                sId: ourselfData.id,
                sName: ourselfData.name,
                billDate: $.trim($('#billDate').val()),
                deliveryDate: $.trim($('#deliveryDate').val()),
                billNo: $.trim($('#billNo').val()),
                transType: classes.getValue(),
                srcOcNo: $.trim($('#srcOcNo').val()),
                disRate: $.trim($('#disRate').val() || 0),
                disAmount: $.trim($('#disAmount').val() || 0),
                deliverAmount: $.trim($('#deliverAmount').val() || 0),
                amount: $.trim($('#amount').val()),
                totalQty: $('#grid').jqGrid('footerData', 'get').orderQty.replace(/,/g, ''),
                totalDiscount: $('#grid').jqGrid('footerData', 'get').discount.replace(/,/g, ''),
                totalAmount: $('#grid').jqGrid('footerData', 'get').amount.replace(/,/g, ''),
                entries: entriesData,
                description: $.trim($('#description').val()),
                files: originalData.files ? JSON.stringify(originalData.files) : ''
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
                        rowId: id,
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
                        amount: rowData.amount,
                        description: rowData.description
                    };
                    entriesData.push(data);
                    invNos.push(goodsInfo.no);
                }
            }
        }
        // 提取重复产品并处理
        let repeatNos = [];
        for (let k in invNos) {
            if (invNos.indexOf(invNos[k]) !== invNos.lastIndexOf(invNos[k]) && repeatNos.indexOf(invNos[k]) === -1) {
                repeatNos.push(invNos[k]);
            }
        }
        if (repeatNos.length > 0) {
            $.dialog.tips('编码为：' + repeatNos.toString() + ' 的产品重复！', 2);
            return !1;
        }
        return entriesData;
    }
};
$(function() {
    if (urlParam.turnByPurReq) {
        var openerData = window.parent.document.getElementById(urlParam.tabId).openerData;
        originalData = {
            srcPurReqId: parseInt(openerData.id),
            id: -1,
            status: 'add',
            transType: 101101,
            contact: 0,
            entries: [],
            totalQty: 0,
            totalDiscount: 0,
            totalAmount: 0,
            disRate: 0,
            disAmount: '0.00',
            deliverAmount: '0.00',
            amount: '0.00',
            description: openerData.title
        };
        THISPAGE.init(originalData);
    } else if (urlParam.turnBygoodList) {
        var openerData = window.parent.document.getElementById(urlParam.tabId).openerData;
        originalData = {
            srcPurReqId: 0,
            id: -1,
            status: 'add',
            transType: 101101,
            contact: 0,
            entries: [],
            totalQty: 0,
            totalDiscount: 0,
            totalAmount: 0,
            disRate: 0,
            disAmount: '0.00',
            deliverAmount: '0.00',
            amount: '0.00',
            description: openerData.description
        };
        THISPAGE.init(originalData);
        curRow = THISPAGE.curID = 1;
        var newId = THISPAGE.newId;
        var nowId = 1;
        for (var b = JSON.parse(openerData.goods), c = 0; c < b.length; c++) {
            var goods = b[c];
            if (goods.length < 1) {
                continue;
            }
            goods.amount = goods.price * goods.orderQty;
            goods.goods = goods.no + ' ' + goods.name + (goods.spec ? ' ' + goods.spec : '') + (goods.sku ? ' ' + goods.sku : '');
            if (nowId) {
                $('#grid').jqGrid('setRowData', Number(nowId), {});
            } else {
                nowId = newId;
                $('#grid').jqGrid('addRowData', Number(nowId), {}, 'last');
                newId++;
            }
            $('#' + nowId).data('goodsInfo', goods).data('storageInfo', {
                id: goods.storageId,
                name: goods.storageName
            });
            $('#grid').jqGrid('setRowData', nowId, goods);
            curRow++;
            var d = $('#' + nowId).next();
            nowId = d.length > 0 ? $('#' + nowId).next().attr('id') : '';
        }
        '' === nowId && ($('#grid').jqGrid('addRowData', newId, {}, 'last'), THISPAGE.newId = newId + 1);
        setTimeout(function() {
            $('#grid').jqGrid('editCell', curRow, 2, !0);
        }, 10);
        THISPAGE.calTotal();
        $('.wrapper > #div-lock').remove();
    } else if (urlParam.id) {
        $('.wrapper').append('<div id="div-lock"><div class="loading">单据准备中...</div></div>');
        Public.ajaxGet('/Bill/InvPo/query', {
            id: urlParam.id
        }, function(res) {
            if (res && 200 == res.status) {
                originalData = res.data;
                if (urlParam.copyFromPurchaseOrder) {
                    originalData.srcPurReqId = 0;
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
            srcPurReqId: 0,
            id: -1,
            status: 'add',
            contact: 0,
            transType: 101101,
            entries: [],
            totalQty: 0,
            totalDiscount: 0,
            totalAmount: 0,
            disRate: 0,
            disAmount: '0.00',
            deliverAmount: '0.00',
            amount: '0.00'
        };
        '101102' === urlParam.transType ? (originalData.transType = 101102) : (originalData.transType = 101101);
        THISPAGE.init(originalData);
    }
});
