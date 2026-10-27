var defaultPage = Public.getDefaultPage();
var queryConditions = {
    categoryId: -1,
    storageId: -1,
    warnType: 0,
    skey: ''
};
var categoryTree = Public.categoryTree($('#category'), {
    rootTxt: '所有产品类别',
    treeSettings: {
        callback: {
            beforeClick: function(a, b) {
                $('#category').data('id', b.id).val(b.name);
                $('#search').trigger('click');
            }
        }
    }
});
var storageCombo = $('#storage').combo({
    data: '/Base/Storage/list',
    ajaxOptions: {
        formatData: function(data) {
            return data.data.rows.unshift({
                id: -1,
                name: '所有仓库'
            }),data.data.rows;
        }
    },
    value: 'id',
    text: 'name',
    defaultSelected: 0,
    width: 78,
    callback: {
        onChange: function(a) {
            $('#search').trigger('click');
        }
    }
}).getCombo();
var warnTypeCombo = $('#warnType').combo({
    data: [
        {id: 0, name: '所有预警'},
        {id: 1, name: '低库存预警'},
        {id: 2, name: '高库存预警'}
    ],
    value: 'id',
    text: 'name',
    width: 90,
    defaultSelected: 0,
    cache: !1,
    trigger: !0,
    defaultFlag: !1,
    callback: {
        onChange: function(a) {
            $('#search').trigger('click');
        }
    }
}).getCombo();
var curRow;
var curCol;
var THISPAGE = {
    init: function() {
        this.initGrid();
        this.initEvent();
    },
    initGrid: function() {
        var a = Public.setGrid();
        $('#grid').jqGrid({
            url: '/Base/Goods/listInventoryQtyWarning',
            postData: queryConditions,
            datatype: 'json',
            colModel: [{
                name: 'id',
                label: '<span class="ml10">ID</span>',
                width: 60,
                fixed: !0,
                classes: 'ti10 gray'
            }, {
                name: 'categoryName',
                label: '产品类别',
                width: 100,
                fixed: !0,
                title: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'no',
                label: '产品编码',
                width: 150,
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
                name: 'brandName',
                label: '品牌',
                width: 60,
                fixed: !0,
                title: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'unitName',
                label: '单位',
                width: 52,
                fixed: !0
            }, {
                name: 'storageId',
                label: '仓库ID',
                hidden: !0
            }, {
                name: 'storageName',
                label: '仓库',
                width: 70,
                fixed: !0,
                title: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'isDisable',
                label: '状态',
                width: 52,
                fixed: !0,
                sortable: !0,
                formatter: function(a, b, c) {
                    var d = (
                        0 == a ? '正常' : (
                            1 == a ? '将停产' : (
                                2 == a ? '已停产' : (
                                    9 == a ? '已禁用' : ''
                                )
                            )
                        )
                    );
                    var e = (
                        0 == a ? 'green' : (
                            1 == a ? 'red' : (
                                2 == a ? 'red' : (
                                    9 == a ? 'red' : ''
                                )
                            )
                        )
                    );
                    return '<span class="db h30 lh30 ' + e + '">' + d + '</span>';
                }
            }, {
                name: 'purPrice',
                label: '预采购价',
                width: 0,
                hidden: !0
            }, {
                name: 'salePrice',
                label: '预销售价',
                width: 0,
                hidden: !0
            }, {
                name: 'currentQty',
                label: '当前库存',
                width: 100,
                fixed: !0
            }, {
                name: 'lowQty',
                label: '库存下限<small class="ml5">可编辑</small>',
                width: 114,
                fixed: !0,
                editable: !0,
                classes: 'ble bre'
            }, {
                name: 'highQty',
                label: '库存上限<small class="ml5">可编辑</small>',
                width: 114,
                fixed: !0,
                editable: !0,
                classes: 'bre'
            }, {
                name: 'warning',
                label: '已超限数量',
                width: 100,
                fixed: !0,
                cellattr: function(rowId, val, rawObject, cm, rdata) {
                    if (val <= 0) {
                        return 'class="f14 red"';
                    } else {
                        return 'class="f14 green"';
                    }
                }
            }
            ],
            cmTemplate: {sortable: !1, title: !1},
            jsonReader: {root: 'data.rows', records: 'data.records', total: 'data.total', repeatitems: !1, id: 'id'},
            width: a.w,
            height: a.h,
            gridview: !0,
            multiselect: !0,
            multiboxonly: !0,
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
            cellEdit: !1,
            cellsubmit: 'clientArray',
            triggerAdd: !1,
            loadComplete: function(data) {
                data.status == -1 && $.dialog.tips(data.msg, 2);
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
                    case 'lowQty':
                        var lowQty = parseFloat(cellValue);
                        var highQty = parseFloat($('#grid').jqGrid('getCell', rowId, 'highQty'));
                        var currentQty = parseFloat($('#grid').jqGrid('getCell', rowId, 'currentQty'));
                        var warning = parseFloat($('#grid').jqGrid('getCell', rowId, 'warning'));
                        var id = parseFloat($('#grid').jqGrid('getCell', rowId, 'id'));
                        if ($.isNumeric(lowQty)) {
                            if (lowQty < 0 || lowQty >= highQty) {
                                $.dialog.tips('库存下限应为 0 ~ 库存上限之间！请重新设置！', 2);
                            } else {
                                Public.ajaxPost('/Base/Goods/upLHQty', {
                                    async: !1,
                                    id: id,
                                    newQty: lowQty,
                                    type: cellName
                                }, function(res) {
                                    if (res && 200 == res.status) {
                                        $.dialog.tips('更新产品库存下限成功！');
                                    } else {
                                        $.dialog.tips(res.msg, 2);
                                    }
                                });
                                $('#grid').jqGrid('setRowData', rowId, {
                                    warning: warning > 0 ? warning : (currentQty < lowQty ? currentQty - lowQty : 0)
                                });
                            }
                        }
                        break;
                    case 'highQty':
                        var highQty = parseFloat(cellValue);
                        var lowQty = parseFloat($('#grid').jqGrid('getCell', rowId, 'lowQty'));
                        var currentQty = parseFloat($('#grid').jqGrid('getCell', rowId, 'currentQty'));
                        var warning = parseFloat($('#grid').jqGrid('getCell', rowId, 'warning'));
                        var id = parseFloat($('#grid').jqGrid('getCell', rowId, 'id'));
                        if ($.isNumeric(highQty)) {
                            if (highQty <= lowQty) {
                                $.dialog.tips('库存上限应大于库存下限！请重新设置！', 2);
                            } else {
                                Public.ajaxPost('/Base/Goods/upLHQty', {
                                    async: !1,
                                    id: id,
                                    newQty: highQty,
                                    type: cellName
                                }, function(res) {
                                    if (res && 200 == res.status) {
                                        $.dialog.tips('更新产品库存上限成功！');
                                    } else {
                                        $.dialog.tips(res.msg, 2);
                                    }
                                });
                                $('#grid').jqGrid('setRowData', rowId, {
                                    warning: warning < 0 ? warning : (currentQty > highQty ? currentQty - highQty : 0)
                                });
                            }
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
        $('#search').on('click', function() {
            queryConditions.categoryId = categoryTree.getValue();
            queryConditions.storageId = storageCombo.getValue();
            queryConditions.warnType = warnTypeCombo.getValue();
            queryConditions.skey = $.trim($('#skey').val()).split(/[ ,，]+/).toString();
            $('#grid').jqGrid('setGridParam', {
                postData: queryConditions,
                page: 1
            }).trigger('reloadGrid');
        });
        $('#refresh').on('click', function() {
            window.location.reload();
        });
        $('#export').on('click', function(a) {
            queryConditions.categoryId = categoryTree.getValue();
            queryConditions.storageId = storageCombo.getValue();
            queryConditions.warnType = warnTypeCombo.getValue();
            queryConditions.skey = $.trim($('#skey').val()).split(/[ ,，]+/).toString();
            var b = $('#grid').jqGrid('getGridParam', 'selarrrow');
            var c = b.join();
            var d = c ? '&id=' + c : '';
            for (var e in queryConditions) {
                if (queryConditions[e]) {
                    d += '&' + e + '=' + queryConditions[e];
                }
            }
            var f = '/Base/Goods/warningExport?action=warningExport' + d;
            $(this).attr('href', f);
        });
        $('#toPo').on('click', function(a) {
            a.preventDefault();
            if (Business.verifyRight('PO_ADD')) {
                var arr_ids = $('#grid').jqGrid('getGridParam', 'selarrrow');
                var rowList = [];
                for (var i = 0; i < arr_ids.length; i++) {
                    var rowid = arr_ids[i];
                    if (!rowid) {
                        continue;
                    }
                    var row = $('#grid').jqGrid('getRowData', rowid);
                    if (row.warning >= 0) {
                        $.dialog.tips('产品【' + row.name + '】未低于最低库存！', 2);
                        return;
                    }
                    if (row) {
                        var data = {
                            id: row.id,
                            no: row.no,
                            name: row.name,
                            spec: row.spec,
                            sku: row.sku,
                            brandName: row.brandName,
                            unitName: row.unitName,
                            storageId: row.storageId,
                            storageName: row.storageName,
                            price: row.purPrice,
                            orderQty: -row.warning
                        };
                    }
                    rowList.push(data);
                }
                if (rowList.length) {
                    if (rowList.length <= 20) {
                        var newTabId = (new Date).format("yyyyMMddhhmmss");
                        parent.tab.addTabItem({
                            tabid: 'purchase-purchaseOrder' + newTabId,
                            text: '<i class="iconfont icon-danye f14 mr5"></i>采购订单（新建）',
                            url: '/Uri/purchaseOrder_manage?turnBygoodList&tabId=purchase-purchaseOrder' + newTabId,
                            data: {
                                goods: JSON.stringify(rowList),
                                description: '来源于库存预警一键生成'
                            }
                        });
                    } else {
                        $.dialog.tips('一次不能选择超过20个产品！', 2);
                    }
                } else {
                    $.dialog.tips('请先选择产品！', 2);
                }
            };
        });
        $('.grid-wrap').on('blur', '.textbox', function() {
            curRow && curCol && ($('#grid').jqGrid('saveCell', curRow, curCol), curRow = curCol = null);
        });
        $(window).on('keydown', function(a) {
            var curKey = a.which;
            if (curKey == 13) {
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
