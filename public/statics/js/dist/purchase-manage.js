var urlParam = Public.urlParam();
var defaultPage = Public.getDefaultPage();
var qtyPlaces = Number(defaultPage.SYSTEM.qtyPlaces);
var pricePlaces = Number(defaultPage.SYSTEM.pricePlaces);
var amountPlaces = Number(defaultPage.SYSTEM.amountPlaces);
var goodsCombo;
var storageCombo;
var originalData;
var curRow;
var curCol;
var btn_save = '<a id="save" class="ui-btn bg-green"><i class="iconfont icon-baocun"></i><span>保存</span></a>';
var btn_edit = '<a id="edit" class="ui-btn bg-green"><i class="iconfont icon-baocun"></i><span>保存</span></a>';
var btn_delete = '<a id="delete" class="ui-btn bg-red"><i class="iconfont icon-shanchu"></i><span>删除</span></a>';
var THISPAGE = {
    init: function(a) {
        THISPAGE.initDom(a);
        THISPAGE.initGrid(a);
        THISPAGE.initEvent();
    },
    initDom: function(a) {
        if ('102101' == originalData.transType) {
            $('#restQtyToNowQty .inout, #logTit .inout').text('收');
        } else {
            $('#restQtyToNowQty .inout, #logTit .inout').text('退');
        }
        if (a.id > 0) {
            a.checkId > 0 ? $('#mark').addClass('bill-has-checked') : $('#mark').removeClass('bill-has-checked');
            $('#toolTop').html(btn_edit + btn_delete);
            $('#billNo').val(a.billNo);
            $('#srcOrderNo').val(a.srcOrderNo);
            $('#billDate').val(a.billDate);
            $('#description').val(a.description);
            $('#desBtn').show();
            $('#uName').html(a.uName);
            $('#checkName').html(a.checkName);
            $('#createTime').html(a.createTime);
            $('#modifyTime').html(a.modifyTime);
        } else {
            $('#toolTop').html(btn_save);
            $('#srcOrderNo').text(a.srcOrderNo);
            $('#billDate').val(defaultPage.SYSTEM.endDate);
            $('#uName').html(defaultPage.SYSTEM.uName);
        }
        goodsCombo = Business.goodsCombo($('.goodsAuto'), {});
        storageCombo = Business.storageCombo($('.storageAuto'));
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
                editable: !1,
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
                                orderQty: rowData.orderQty || goodsInfo.orderQty || 1,
                                qty: rowData.qty || 0,
                                nowQty: rowData.nowQty || 0,
                                price: rowData.price || goodsInfo.purPrice || 0,
                                amount: rowData.amount || 0
                            };
                            data.amount = data.amount || data.orderQty * data.price;
                            $('#grid').jqGrid('setRowData', b.rowId, data);
                            THISPAGE.calTotal();
                        }
                        return a;
                    } else { // 表格加载模式
                        return c.goodsId > 0 ? (c.invNo + ' ' + c.invName + (c.invSpec ? ' ' + c.invSpec : '') + (c.invSku ? ' ' + c.invSku : '')) : '&nbsp;';
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
                name: 'price',
                label: '采购价',
                width: 0,
                hidden: !0,
                formatter: 'currency',
                formatoptions: {
                    showZero: !0,
                    decimalPlaces: amountPlaces
                }
            }, {
                name: 'amount',
                label: '金额',
                width: 0,
                hidden: !0,
                formatter: 'currency',
                formatoptions: {
                    showZero: !0,
                    decimalPlaces: amountPlaces
                }
            }, {
                name: 'orderQty',
                label: originalData.transType == 102101 ? '订单数量' : '需退货数',
                width: 80,
                fixed: !0,
                editable: originalData.transType == 102101 ? !1 : !0,
                classes: 'fb',
                formatter: 'number',
                formatoptions: {
                    decimalPlaces: qtyPlaces
                }
            }, {
                name: 'qty',
                label: ('已' + ('102101' == originalData.transType ? '收' : '退') + '货数'),
                width: 80,
                fixed: !0,
                editable: !1,
                classes: 'fb green',
                formatter: 'number',
                formatoptions: {
                    decimalPlaces: qtyPlaces
                }
            }, {
                name: 'nowQty',
                label: ('本次' + ('102101' == originalData.transType ? '收' : '退') + '货数'),
                width: 80,
                fixed: !0,
                editable: !0,
                classes: 'fb red',
                formatter: 'number',
                formatoptions: {
                    decimalPlaces: qtyPlaces
                }
            }, {
                name: 'storageName',
                label: '仓库 ',
                nameExt: '<small id="batchStorage" class="ml5">批量</small>',
                width: 70,
                fixed: !0,
                editable: !0,
                classes: 'ui-ellipsis',
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
                name: 'deliverCompany',
                label: '快递公司',
                width: 80,
                fixed: !0,
                editable: !0
            }, {
                name: 'deliverNo',
                label: '快递单号',
                width: 130,
                fixed: !0,
                editable: !0
            }, {
                name: 'description',
                label: '备注',
                width: 100,
                fixed: !0,
                title: !0,
                editable: !0,
                classes: 'ui-ellipsis gray'
            }, {
                name: 'storageId',
                label: '仓库Id',
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
            triggerAdd: !1,
            loadonce: !0,
            footerrow: !0,
            userDataOnFooter: !0,
            loadComplete: function(a) {
                var b = a.rows;
                var c = b.length;
                THISPAGE.newId = c + 1;
                for (var d = 0; c > d; d++) {
                    var e = d + 1;
                    var f = b[d];
                    if ($.isEmptyObject(f)) {
                        break;
                    }
                    var g = $.extend(!0, {
                        id: f.goodsId,
                        no: f.invNo,
                        name: f.invName,
                        spec: f.invSpec,
                        sku: f.invSku
                    }, f);
                    $('#' + e).data('goodsInfo', g).data('storageInfo', {
                        id: f.storageId,
                        name: f.storageName
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
            },
            afterSaveCell: function(rowId, cellName, cellValue, iRow, iCol) {
                switch (cellName) {
                    case 'orderQty':
                        var rowData = $('#grid').jqGrid('getRowData', rowId);
                        if ($.isNumeric(cellValue) && $.isNumeric(rowData.price)) {
                            $('#grid').jqGrid('setRowData', rowId, {
                                amount: parseFloat(cellValue) * parseFloat(rowData.price)
                            });
                        }
                        THISPAGE.calTotal();
                        break;
                    case 'nowQty':
                        THISPAGE.calTotal();
                        break;
                    default:
                        break;
                }
            }
        });
        $('#grid').jqGrid('setGridParam', {
            cellEdit: !0
        });
        $('#logGrid').jqGrid({
            data: a.log,
            datatype: 'clientSide',
            colModel: [{
                name: 'createTime',
                label: '时间',
                width: 130,
                fixed: !0
            }, {
                name: 'uName',
                label: '操作人',
                width: 54,
                fixed: !0
            }, {
                name: 'goods',
                label: '产品',
                width: 100,
                title: !0,
                classes: 'ui-ellipsis',
                formatter: function(a, b, c) {
                    return c.goodsId ? (c.invNo + ' ' + c.invName + (c.invSpec ? ' ' + c.invSpec : '') + (c.invSku ? ' ' + c.invSku : '')) : '&nbsp;';
                }
            }, {
                name: 'brandName',
                label: '品牌',
                width: 80,
                fixed: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'unitName',
                label: '单位',
                width: 42,
                fixed: !0
            }, {
                name: 'storageName',
                label: '仓库',
                width: 80,
                fixed: !0
            }, {
                name: 'qty',
                label: '数量',
                width: 70,
                fixed: !0,
                aligin: 'right'
            }, {
                name: 'deliverCompany',
                label: '快递公司',
                width: 80,
                fixed: !0
            }, {
                name: 'deliverNo',
                label: '快递单号',
                width: 150,
                fixed: !0
            }, {
                name: 'storageId',
                label: '仓库ID',
                width: 0,
                hidden: !0
            }],
            cmTemplate: {sortable: !1, title: !1},
            jsonReader: {root: 'data.rows', records: 'data.records', total: 'data.total', repeatitems: !1, id: 'id'},
            autowidth: !0,
            height: '100%',
            rownumbers: !0,
            gridview: !0,
            onselectrow: !1,
            shrinkToFit: !0,
            forceFit: !1,
            rowNum: 10000,
            cellEdit: !1,
            cellsubmit: 'clientArray',
            footerrow: !0,
            userDataOnFooter: !0,
            loadComplete: function(a) {
                THISPAGE.calTotal();
            },
            loadError: function(a, b) {
                $.dialog.tips('Type: ' + b + '; Response: ' + a.status + ' ' + a.statusText, 2);
            },
            gridComplete: function() {
                setTimeout(function() {
                    Public.autoGridWidth($('#logGrid'));
                }, 10);
            }
        });
    },
    initEvent: function() {
        Business.billsEvent(this, 'purchase');
        laydate.render({
            elem: "#billDate",
        });
        $('.grid-wrap').on('click', '.ui-icon-triangle-1-s', function(a) {
            var b = $(this).siblings();
            var c = b.getCombo();
            setTimeout(function() {
                c.active = !0, c.doQuery();
            }, 10);
        });
        $('.wrapper').on('click', '#save', function(a) {
            a.preventDefault();
            if (Business.verifyRight('PU_ADD')) {
                var data = THISPAGE.getPostData();
                if (data) {
                    $('.wrapper').append('<div id="div-lock"><div class="loading">保存中...</div></div>');
                    Public.ajaxPost('/Bill/InvPu/add', {
                        postData: JSON.stringify(data)
                    }, function(res) {
                        if (res && 200 == res.status) {
                            parent.parent.$.dialog.tips('新增成功！');
                            parent.frames['purchase-purchaseOrder'] && parent.frames['purchase-purchaseOrder'].$('#search').trigger('click');
                            parent.frames['inventory-purchase'] && parent.frames['inventory-purchase'].$('#search').trigger('click');
                            parent.frames['inventory-purchaseBack'] && parent.frames['inventory-purchaseBack'].$('#search').trigger('click');
                            parent.tab.removeSelectedTabItem();
                        } else {
                            $.dialog.tips('新增失败！' + res.msg, 2);
                        }
                    });
                }
            }
        });
        $('.wrapper').on('click', '#edit', function(a) {
            a.preventDefault();
            if (Business.verifyRight('PU_UPDATE')) {
                var data = THISPAGE.getPostData();
                if (data) {
                    $('.wrapper').append('<div id="div-lock"><div class="loading">保存中...</div></div>');
                    Public.ajaxPost('/Bill/InvPu/update', {
                        postData: JSON.stringify(data)
                    }, function(res) {
                        if (res && 200 == res.status) {
                            parent.parent.$.dialog.tips('更新成功！');
                            parent.frames['purchase-purchaseOrder'] && parent.frames['purchase-purchaseOrder'].$('#search').trigger('click');
                            parent.frames['inventory-purchase'] && parent.frames['inventory-purchase'].$('#search').trigger('click');
                            parent.frames['inventory-purchaseBack'] && parent.frames['inventory-purchaseBack'].$('#search').trigger('click');
                            parent.tab.removeSelectedTabItem();
                        } else {
                            $('.wrapper > #div-lock').remove();
                            $.dialog.tips('更新失败！' + res.msg, 2);
                        }
                    });
                }
            }
        });
        $('.wrapper').on('click', '#delete', function(a) {
            a.preventDefault();
            if (Business.verifyRight('PU_DELETE')) {
                originalData.id && $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                    Public.ajaxGet('/Bill/InvPu/delete', {
                        id: originalData.id
                    }, function(res) {
                        if (res && 200 == res.status) {
                            parent.$.dialog.tips('删除成功！');
                            parent.frames['purchase-purchaseOrder'] && parent.frames['purchase-purchaseOrder'].$('#search').trigger('click');
                            parent.frames['inventory-purchase'] && parent.frames['inventory-purchase'].$('#search').trigger('click');
                            parent.frames['inventory-purchaseBack'] && parent.frames['inventory-purchaseBack'].$('#search').trigger('click');
                            parent.tab.removeSelectedTabItem();
                        } else {
                            $('.wrapper > #div-lock').remove();
                            $.dialog.tips('删除失败！' + res.msg, 2);
                        }
                    });
                });
            }
        });
        $('#restQtyToNowQty').on('click', function(a) {
            $("#grid").jqGrid("saveCell", curRow, curCol);
            curRow = null;
            curCol = null;
            var ids = $("#grid").jqGrid('getDataIDs');
            for (var i = 0, len = ids.length; i < len; i++) {
                var id = ids[i];
                var rowData = $("#grid").jqGrid('getRowData', id);
                if (Number(rowData.nowQty) > 0) {
                    continue; // 跳过已经填入了大于零的数字的行
                };
                var setData = {};
                setData['nowQty'] = parseInt(rowData.orderQty) - parseInt(rowData.qty);
                $("#grid").jqGrid('setRowData', id, setData);
            };
            THISPAGE.calTotal();
        });
        $('#desBtn').on('click', function(a) {
            a.preventDefault();
            if (Business.verifyRight('PU_UPDATE')) {
                Public.ajaxPost('/Bill/InvPu/updatedes', {
                    id: originalData.id,
                    newDes: $.trim($('#description').val())
                }, function(res) {
                    if (200 === res.status) {
                        $.dialog.tips('备注保存成功！');
                        parent.frames['purchase-purchaseOrder'] && parent.frames['purchase-purchaseOrder'].$('#search').trigger('click');
                        parent.frames['inventory-purchase'] && parent.frames['inventory-purchase'].$('#search').trigger('click');
                        parent.frames['inventory-purchaseBack'] && parent.frames['inventory-purchaseBack'].$('#search').trigger('click');
                    } else {
                        $.dialog.tips('备注保存失败！' + res.msg, 2);
                    }
                });
            }
        });
        $(window).on('resize', function() {
            Public.autoGridWidth($('#grid'));
            Public.autoGridWidth($('#logGrid'));
        });
    },
    calTotal: function() {
        for (var a = $('#grid').jqGrid('getDataIDs'), b = 0, c = 0, d = 0, e = 0, f = 0, g = a.length; g > f; f++) {
            var h = a[f];
            var i = $('#grid').jqGrid('getRowData', h);
            i.amount && (b += parseFloat(i.amount));
            i.orderQty && (c += parseFloat(i.orderQty));
            i.qty && (d += parseFloat(i.qty));
            i.nowQty && (e += parseFloat(i.nowQty));
        }
        $('#grid').jqGrid('footerData', 'set', {
            rn: '合计',
            amount: b,
            orderQty: c,
            qty: d,
            nowQty: e
        });
        for (var a = $('#logGrid').jqGrid('getDataIDs'), b = 0, c = 0, d = a.length; d > c; c++) {
            var e = a[c];
            var f = $('#logGrid').jqGrid('getRowData', e);
            f.qty && (b += parseFloat(f.qty));
        }
        $('#logGrid').jqGrid('footerData', 'set', {
            rn: '合计',
            qty: b
        });
    },
    getPostData: function() {
        curRow && curCol && ($('#grid').jqGrid('saveCell', curRow, curCol), curRow = curCol = null);
        var entriesData = THISPAGE.getEntriesData();
        if (!entriesData) {
            return !1;
        }
        if (entriesData.length > 0) {
            var data = {
                id: originalData.id,
                accId: originalData.accId,
                contactId: originalData.contactId,
                contactName: originalData.contactName,
                totalOrderQty: $('#grid').jqGrid('footerData', 'get').orderQty.replace(/,/g, ''),
                totalQty: $('#grid').jqGrid('footerData', 'get').qty.replace(/,/g, ''),
                totalNowQty: $('#grid').jqGrid('footerData', 'get').nowQty.replace(/,/g, ''),
                amount: $('#grid').jqGrid('footerData', 'get').amount.replace(/,/g, ''),
                billDate: $.trim($('#billDate').val()),
                billNo: $.trim($('#billNo').val()),
                transType: originalData.transType,
                description: $.trim($('#description').val()),
                entries: entriesData,
                srcOrderId: originalData.srcOrderId,
                srcOrderNo: originalData.srcOrderNo
            };
            return data;
        } else {
            return $.dialog.tips('产品信息不能为空！', 2), $('#grid').jqGrid('editCell', 1, 2, !0), !1;
        }
    },
    getEntriesData: function() {
        for (var entriesData = [], ids = $('#grid').jqGrid('getDataIDs'), i = 0, len = ids.length; len > i; i++) {
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
                        qty: rowData.qty,
                        nowQty: rowData.nowQty,
                        price: rowData.price,
                        amount: rowData.amount,
                        deliverCompany: rowData.deliverCompany,
                        deliverNo: rowData.deliverNo,
                        description: rowData.description
                    };
                    entriesData.push(data);
                }
            }
        }
        return entriesData;
    }
};
$(function() {
    if (urlParam.turnByPurchaseOrder) {
        $('.wrapper').append('<div id="div-lock"><div class="loading">单据准备中...</div></div>');
        var openerData = window.parent.document.getElementById(urlParam.tabId).openerData;
        Public.ajaxGet('/Bill/InvPo/queryDetails', {
            id: openerData.id,
            transType: openerData.transType
        }, function(res) {
            if (res && 200 == res.status) {
                originalData = res.data;
                originalData.status = 'add';
                originalData.id = -1;
                originalData.transType = openerData.transType;
                THISPAGE.init(originalData);
                $('.wrapper > #div-lock').remove();
            } else {
                $.dialog.tips(res.msg, 2);
            }
        });
    } else if (urlParam.id) {
        $('.wrapper').append('<div id="div-lock"><div class="loading">单据准备中...</div></div>');
        Public.ajaxGet('/Bill/InvPu/query', {
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
            id: -1,
            status: 'add',
            transType: 102101,
            entries: [],
            description: '',
            totalOrderQty: 0,
            totalQty: 0,
            totalNowQty: 0
        };
        '102102' === urlParam.transType ? originalData.transType = 102102 : originalData.transType = 102101;
        THISPAGE.init(originalData);
    }
});
