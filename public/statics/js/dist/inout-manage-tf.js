var urlParam = Public.urlParam();
var defaultPage = Public.getDefaultPage();
var qtyPlaces = Number(defaultPage.SYSTEM.qtyPlaces);
var pricePlaces = Number(defaultPage.SYSTEM.pricePlaces);
var amountPlaces = Number(defaultPage.SYSTEM.amountPlaces);
var goodsCombo;
var outStorageCombo;
var inStorageCombo;
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
        goodsCombo = Business.goodsCombo($('.goodsAuto'), {
            userData: {
                beforeSet: function(a) {
                    a.outStorageId = a.storageId;
                    a.outStorageName = a.storageName;
                }
            }
        });
        outStorageCombo = $('.outStorageAuto').combo({
            data: '/Base/Storage/list',
            ajaxOptions: {
                formatData: function(data) {
                    return data.data.rows;
                }
            },
            value: 'id',
            text: 'name',
            defaultSelected: 0,
            cache: !1,
            trigger: !1,
            defaultFlag: !1,
            editable: !0,
            callback: {
                onChange: function(a) {
                    var b = this.input.parents('tr');
                    var c = b.data('outStorageInfo');
                    c || (c = {});
                    a && (c.id = a.id, c.name = a.name);
                    b.data('outStorageInfo', c);
                }
            }
        }).getCombo();
        inStorageCombo = $('.inStorageAuto').combo({
            data: '/Base/Storage/list',
            ajaxOptions: {
                formatData: function(data) {
                    return data.data.rows;
                }
            },
            value: 'id',
            text: 'name',
            defaultSelected: 0,
            cache: !1,
            editable: !0,
            trigger: !1,
            defaultFlag: !1,
            callback: {
                onChange: function(a) {
                    var b = this.input.parents('tr');
                    var c = b.data('inStorageInfo');
                    c || (c = {});
                    a && (c.id = a.id, c.name = a.name);
                    b.data('inStorageInfo', c);
                }
            }
        }).getCombo();
        if (a.id > 0) {
            a.checkId > 0 ? $('#mark').addClass('bill-has-checked') : $('#mark').removeClass('bill-has-checked');
            $('#toolTop').html(btn_edit + btn_delete);
            $('#billNo').val(a.billNo);
            $('#billDate').val(a.billDate);
            $('#description').val(a.description);
            $('#desBtn').show();
            $('#uName').html(a.uName);
            $('#checkName').html(a.checkName);
            $('#createTime').html(a.createTime);
            $('#modifyTime').html(a.modifyTime);
        } else {
            $('#toolTop').html(btn_save);
            $('#billDate').val(defaultPage.SYSTEM.endDate);
            $('#uName').html(defaultPage.SYSTEM.uName);
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
                width: 200,
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
                        var outStorageInfo = $('#' + b.rowId).data('outStorageInfo');
                        var rowData = $('#grid').jqGrid('getRowData', b.rowId);
                        if (goodsInfo && outStorageInfo && rowData) {
                            var data = {
                                brandName: goodsInfo.brandName,
                                unitName: goodsInfo.unitName,
                                outStorageId: outStorageInfo.id || goodsInfo.storageId,
                                outStorageName: outStorageInfo.name || goodsInfo.storageName,
                                qty: rowData.qty || goodsInfo.qty || 1
                            };
                            $('#grid').jqGrid('setRowData', b.rowId, data);
                            THISPAGE.calTotal();
                        }
                        return a;
                    } else { // 表格加载模式
                        return c.invNo ? (c.invNo + ' ' + c.invName + (c.invSpec ? ' ' + c.invSpec : '') + (c.invSku ? ' ' + c.invSku : '')) : '&nbsp;';
                    }
                }
            }, {
                name: 'brandName',
                label: '品牌',
                width: 60,
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
                name: 'outStorageName',
                label: '调出仓库',
                nameExt: '<small id="batch-storageA" class="ml5">批量</small>',
                sortable: !1,
                width: 100,
                fixed: !0,
                editable: !0,
                edittype: 'custom',
                editoptions: {
                    custom_element: function(a, b) {
                        var c = $('.outStorageAuto')[0];
                        return c;
                    },
                    custom_value: function(a, b, c) {
                        if ('get' === b) {
                            if ('' !== $('.outStorageAuto').getCombo().getValue()) {
                                return $(a).val();
                            }
                            var d = $(a).parents('tr');
                            return d.removeData('outStorageInfo'), '';
                        }
                        'set' === b && $('input', a).val(c);
                    },
                    handle: function() {
                        $('#initCombo').append($('.outStorageAuto').val(''));
                    },
                    trigger: 'ui-icon-triangle-1-s iconfont icon-xiajiantou'
                }
            }, {
                name: 'inStorageName',
                label: '调入仓库',
                nameExt: '<small id="batch-storageB" class="ml5">批量</small>',
                width: 100,
                fixed: !0,
                editable: !0,
                edittype: 'custom',
                editoptions: {
                    custom_element: function(a, b) {
                        var c = $('.inStorageAuto')[0];
                        return c;
                    },
                    custom_value: function(a, b, c) {
                        if ('get' === b) {
                            if ('' !== $('.inStorageAuto').getCombo().getValue()) {
                                return $(a).val();
                            }
                            var d = $(a).parents('tr');
                            return d.removeData('inStorageInfo'), '';
                        }
                        'set' === b && $('input', a).val(c);
                    },
                    handle: function() {
                        $('#initCombo').append($('.inStorageAuto').val(''));
                    },
                    trigger: 'ui-icon-triangle-1-s iconfont icon-xiajiantou'
                }
            }, {
                name: 'qty',
                label: '数量',
                width: 60,
                fixed: !0,
                formatter: 'number',
                formatoptions: {
                    decimalPlaces: qtyPlaces
                },
                editable: !0
            }, {
                name: 'description',
                label: '备注',
                width: 100,
                title: !0,
                editable: !0,
                classes: 'ui-ellipsis gray'
            }, {
                name: 'id',
                label: 'ID',
                width: 0,
                hidden: !0
            }, {
                name: 'outStorageId',
                label: '调出仓库ID',
                width: 0,
                hidden: !0
            }, {
                name: 'inStorageId',
                label: '调入仓库ID',
                width: 0,
                hidden: !0
            }],
            cmTemplate: {sortable: !1, title: !1},
            jsonReader: {root: 'data.entries', records: 'records', repeatitems: !1, id: 'id'},
            localReader: {root: 'rows', records: 'records', repeatitems: !1, id: 'id'},
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
                if (urlParam.id > 0) {
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
                        g.id = f.goodsId;
                        $('#' + e).data('goodsInfo', g).data('outStorageInfo', {
                            id: f.outStorageId,
                            name: f.outStorageName
                        }).data('inStorageInfo', {
                            id: f.inStorageId,
                            name: f.inStorageName
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
                if ('outStorageName' === cellName) {
                    $('#' + iRow + '_outStorageName', '#grid').val(cellValue);
                    outStorageCombo.selectByText(cellValue);
                }
                if ('inStorageName' === cellName) {
                    $('#' + iRow + '_inStorageName', '#grid').val(cellValue);
                    inStorageCombo.selectByText(cellValue);
                }
            },
            afterSaveCell: function(rowId, cellName, cellValue, iRow, iCol) {
                'qty' == cellName && THISPAGE.calTotal();
            }
        });
        $('#grid').jqGrid('setGridParam', {
            cellEdit: !0
        });
    },
    initEvent: function() {
        Business.billsEvent(this, 'inoutTF');
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
        $('#batch-storageA').powerFloat({
            eventType: 'click',
            hoverHold: !1,
            reverseSharp: !0,
            target: function() {
                return curRow && curCol && ($('#grid').jqGrid('saveCell', curRow, curCol), curRow = curCol = null), $('.wrapper').data('batch', 'storageA'), $('#storageBox');
            }
        });
        $('#batch-storageB').powerFloat({
            eventType: 'click',
            hoverHold: !1,
            reverseSharp: !0,
            target: function() {
                return curRow && curCol && ($('#grid').jqGrid('saveCell', curRow, curCol), curRow = curCol = null), $('.wrapper').data('batch', 'storageB'), $('#storageBox');
            }
        });
        $('.wrapper').on('click', '#storageBox li', function(a) {
            var b = $(this).data('id');
            var c = $(this).data('name');
            var d = $('#grid').jqGrid('getDataIDs');
            if ('storageA' === $('.wrapper').data('batch')) {
                var e = 'outStorageName';
                var f = 'outStorageInfo';
            } else {
                var e = 'inStorageName';
                var f = 'inStorageInfo';
            }
            for (var g = 0, h = d.length; h > g; g++) {
                var i = d[g];
                var j = $('#grid').jqGrid('getRowData', i);
                var k = $('#' + i);
                if ('' !== j.goods && void 0 !== k.data('goodsInfo')) {
                    var l = {};
                    l[e] = c;
                    $('#grid').jqGrid('setRowData', i, l);
                    $('#' + i).data(f, {
                        id: b,
                        name: c
                    });
                }
            }
            $.powerFloat.hide();
        });
        $('.wrapper').on('click', '#save', function(a) {
            a.preventDefault();
            if (Business.verifyRight('TF_ADD')) {
                var data = THISPAGE.getPostData();
                if (data) {
                    $('.wrapper').append('<div id="div-lock"><div class="loading">保存中...</div></div>');
                    Public.ajaxPost('/Bill/InvIo/addTf', {
                        postData: JSON.stringify(data)
                    }, function(res) {
                        if (res && 200 == res.status) {
                            parent.parent.$.dialog.tips('保存成功！');
                            parent.frames['inventory-transfers'] && parent.frames['inventory-transfers'].$('#search').trigger('click');
                            parent.tab.removeSelectedTabItem();
                        } else {
                            $('.wrapper > #div-lock').remove();
                            $.dialog.tips('保存失败！' + res.msg, 2);
                        }
                    });
                }
            }
        });
        $('.wrapper').on('click', '#edit', function(a) {
            a.preventDefault();
            if (Business.verifyRight('TF_UPDATE')) {
                var data = THISPAGE.getPostData();
                if (data) {
                    $('.wrapper').append('<div id="div-lock"><div class="loading">保存中...</div></div>');
                    Public.ajaxPost('/Bill/InvIo/updateTf', {
                        postData: JSON.stringify(data)
                    }, function(res) {
                        if (res && 200 == res.status) {
                            parent.parent.$.dialog.tips('保存成功！');
                            parent.frames['inventory-transfers'] && parent.frames['inventory-transfers'].$('#search').trigger('click');
                            parent.tab.removeSelectedTabItem();
                        } else {
                            $('.wrapper > #div-lock').remove();
                            $.dialog.tips('保存失败！' + res.msg, 2);
                        }
                    });
                }
            }
        });
        $('.wrapper').on('click', '#delete', function(a) {
            a.preventDefault();
            if (Business.verifyRight('TF_DELETE')) {
                originalData.id && $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                    Public.ajaxGet('/Bill/InvIo/deleteTf', {
                        id: originalData.id
                    }, function(res) {
                        if (res && 200 == res.status) {
                            parent.$.dialog.tips('删除成功！');
                            parent.frames['inventory-transfers'] && parent.frames['inventory-transfers'].$('#search').trigger('click');
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
            if (Business.verifyRight('TF_UPDATE')) {
                Public.ajaxPost('/Bill/InvIo/updatedes', {
                    id: originalData.id,
                    newDes: $.trim($('#description').val())
                }, function(res) {
                    if (res && 200 == res.status) {
                        $.dialog.tips('备注保存成功！');
                        parent.frames['inventory-transfers'] && parent.frames['inventory-transfers'].$('#search').trigger('click');
                    } else {
                        $.dialog.tips('备注保存失败！' + res.msg, 2);
                    }
                });
            }
        });
        $(window).on('resize', function(K) {
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
    calTotal: function() {
        for (var a = $('#grid').jqGrid('getDataIDs'), b = 0, c = 0, d = a.length; d > c; c++) {
            var e = a[c];
            var f = $('#grid').jqGrid('getRowData', e);
            f.qty && (b += parseFloat(f.qty));
        }
        $('#grid').jqGrid('footerData', 'set', {
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
            THISPAGE.calTotal();
            var data = {
                id: originalData.id,
                billDate: $.trim($('#billDate').val()),
                billNo: $.trim($('#billNo').val()),
                entries: entriesData,
                totalQty: $('#grid').jqGrid('footerData', 'get').qty.replace(/,/g, ''),
                description: $.trim($('#description').val())
            };
            return data;
        }
        return $.dialog.tips('产品信息不能为空！', 2), $('#grid').jqGrid('editCell', 1, 2, !0), !1;
    },
    getEntriesData: function() {
        for (var entriesData = [], ids = $('#grid').jqGrid('getDataIDs'), i = 0, len = ids.length; len > i; i++) {
            var id = ids[i];
            var rowData = $('#grid').jqGrid('getRowData', id);
            if ('' !== rowData.goods) {
                var goodsInfo = $('#' + id).data('goodsInfo');
                var outStorageInfo = $('#' + id).data('outStorageInfo');
                var inStorageInfo = $('#' + id).data('inStorageInfo');
                if (!outStorageInfo || !outStorageInfo.id) {
                    return $.dialog.tips('请选择相应的仓库！', 2), $('#grid').jqGrid('editCellByColName', id, 'outStorageName'), !1;
                }
                if (!inStorageInfo || !inStorageInfo.id) {
                    return $.dialog.tips('请选择相应的仓库！', 2), $('#grid').jqGrid('editCellByColName', id, 'inStorageName'), !1;
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
                    outStorageId: outStorageInfo.id || -1,
                    outStorageName: outStorageInfo.name || '',
                    inStorageId: inStorageInfo.id || -1,
                    inStorageName: inStorageInfo.name || '',
                    qty: rowData.qty || 0,
                    price: rowData.price || 0,
                    amount: rowData.amount || 0,
                    description: rowData.description
                };
                entriesData.push(data);
            }
        }
        return entriesData;
    }
};
$(function() {
    if (urlParam.id) {
        $('.wrapper').append('<div id="div-lock"><div class="loading">单据准备中...</div></div>');
        Public.ajaxGet('/Bill/InvIo/queryTf', {
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
            transType: 102403,
            id: -1,
            status: 'add',
            entries: [],
            totalQty: 0
        };
        THISPAGE.init(originalData);
    }
});
