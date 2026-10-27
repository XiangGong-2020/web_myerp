var urlParam = Public.urlParam();
var defaultPage = Public.getDefaultPage();
var qtyPlaces = Number(defaultPage.SYSTEM.qtyPlaces);
var pricePlaces = Number(defaultPage.SYSTEM.pricePlaces);
var amountPlaces = Number(defaultPage.SYSTEM.amountPlaces);
var disEditable = urlParam.disEditable;
var goodsCombo;
var storageCombo;
var originalData;
var curRow;
var curCol;
var curRowLog;
var curColLog;
var btn_save = '<a id="save" class="ui-btn bg-green"><i class="iconfont icon-baocun"></i><span>保存</span></a>';
var btn_edit = '<a id="edit" class="ui-btn bg-green"><i class="iconfont icon-baocun"></i><span>保存</span></a>';
var btn_printlog = '<a id="printlog" class="ui-btn bg-teal fr"><i class="iconfont icon-dayin"></i><span>打印发货单</span><b>请先按本次打印需求，隐藏无关发货记录行，只保留要打印的行，并修改数量、备注，确认处理好了再点击打印发货单。</b></a>';
var btn_delete = '<a id="delete" class="ui-btn bg-red"><i class="iconfont icon-shanchu"></i><span>删除</span></a>';
var THISPAGE = {
    init: function(a) {
        THISPAGE.initDom(a);
        THISPAGE.initGrid(a);
        THISPAGE.initEvent();
    },
    initDom: function(a) {
        if ('102201' == originalData.transType) {
            $('#restQtyToNowQty .inout, #logTit .inout').text('发');
        } else {
            $('#restQtyToNowQty .inout, #logTit .inout').text('退');
        }
        if (a.id > 0) {
            a.checkId > 0 ? $('#mark').addClass('bill-has-checked') : $('#mark').removeClass('bill-has-checked');
            $('#toolTop').html(btn_edit + btn_delete);
            if ('102201' == originalData.transType) { // 销售发货单
                $('#logTit').append(btn_printlog);
            }
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
            $('#srcOrderNo').val(a.srcOrderNo);
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
                                orderQty: rowData.orderQty || goodsInfo.orderQty || 1,
                                qty: rowData.qty || 0,
                                nowQty: rowData.nowQty || 0,
                                price: rowData.price || goodsInfo.salePrice || 0,
                                amount: rowData.amount || 0
                            };
                            data.amount = data.amount || data.orderQty * data.price;
                            $('#grid').jqGrid('setRowData', b.rowId, data);
                            THISPAGE.calTotal();
                        }
                        return a;
                    } else { // 表格加载模式
                        return c.goodsId > -1 ? (c.invNo + ' ' + c.invName + (c.invSpec ? ' ' + c.invSpec : '') + (c.invSku ? ' ' + c.invSku : '')) : '&nbsp;';
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
                label: '销售价',
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
                label: originalData.transType == 102201 ? '订单数量' : '需退货数',
                width: 80,
                fixed: !0,
                editable: !0,
                classes: 'fb',
                formatter: 'number',
                formatoptions: {
                    decimalPlaces: qtyPlaces
                }
            }, {
                name: 'qty',
                label: ('已' + ('102201' == originalData.transType ? '发' : '退') + '货数量'),
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
                label: ('本次' + ('102201' == originalData.transType ? '发' : '退') + '货数'),
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
                label: '仓库',
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
            }, {
                name: 'refundId',
                label: '退款Id',
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
                    $('#grid #' + e).data('goodsInfo', g).data('storageInfo', {
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
                width: 200,
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
                editable: !0
            }, {
                name: 'description',
                label: '备注',
                width: 150,
                title: !0,
                editable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'deliverCompany',
                label: '快递公司',
                width: 80,
                fixed: !0,
                title: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'deliverNo',
                label: '快递单号',
                width: 150,
                title: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'operate',
                label: '操作',
                width: 60,
                fixed: !0,
                align: 'center',
                formatter: function(a, b, c) {
                    return '<span class="log-hide db lh20 hover-red b-r-3" data-id="' + c.id + '">隐藏</span>';
                }
            }, {
                name: 'id',
                label: 'ID',
                width: 0,
                hidden: !0
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
            beforeEditCell: function(rowId, cellName, cellValue, iRow, iCol) {
                curRowLog = iRow;
                curColLog = iCol;
            }
        });
        $('#logGrid').jqGrid('setGridParam', {
            cellEdit: !0
        });
    },
    initEvent: function() {
        Business.billsEvent(this, 'sales');
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
            if (Business.verifyRight('SA_ADD')) {
                var data = THISPAGE.getPostData();
                if (data) {
                    $('.wrapper').append('<div id="div-lock"><div class="loading">保存中...</div></div>');
                    Public.ajaxPost('/Bill/InvSa/add', {
                        postData: JSON.stringify(data)
                    }, function(res) {
                        if (res && 200 == res.status) {
                            parent.parent.$.dialog.tips('新增成功！');
                            parent.frames['sales-salesOrder'] && parent.frames['sales-salesOrder'].$('#search').trigger('click');
                            parent.frames['inventory-sales'] && parent.frames['inventory-sales'].$('#search').trigger('click');
                            parent.frames['inventory-salesBack'] && parent.frames['inventory-salesBack'].$('#search').trigger('click');
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
            if (Business.verifyRight('SA_UPDATE')) {
                var data = THISPAGE.getPostData();
                if (data) {
                    $('.wrapper').append('<div id="div-lock"><div class="loading">保存中...</div></div>');
                    Public.ajaxPost('/Bill/InvSa/update', {
                        postData: JSON.stringify(data)
                    }, function(res) {
                        if (res && 200 == res.status) {
                            parent.parent.$.dialog.tips('更新成功！');
                            parent.frames['sales-salesOrder'] && parent.frames['sales-salesOrder'].$('#search').trigger('click');
                            parent.frames['inventory-sales'] && parent.frames['inventory-sales'].$('#search').trigger('click');
                            parent.frames['inventory-salesBack'] && parent.frames['inventory-salesBack'].$('#search').trigger('click');
                            parent.tab.removeSelectedTabItem();
                        } else {
                            $('.wrapper > #div-lock').remove();
                            $.dialog.tips('更新失败！' + res.msg, 2);
                        }
                    });
                }
            }
        });
        $('.wrapper').on('click', '#printlog', function(a) {
            a.preventDefault();
            curRowLog && curColLog && ($('#logGrid').jqGrid('saveCell', curRowLog, curColLog), curRowLog = curColLog = null);
            $.dialog.prompt('请输入自定义的整单发货备注（其他注意事项：浏览器分辨率1920，浏览器窗口最大化，打印预览界面选择横向打印，仔细核对发货种类和数量是否正确，不正确要先处理正确再打印）。', function(inputValue) {
                var description = $.trim(inputValue);
                $('#logTit').html('<div class="lh50 f16 fb tc">发货单</div><div>销售单号：' + originalData.srcOrderNo + '，客户名称：' + originalData.contactName + '，整单发货备注：' + description + '</div>');
                $('#gbox_logGrid, #gview_logGrid, .ui-jqgrid-bdiv').css({'width': '1045px'});
                $('#logGrid').hideCol('createTime').hideCol('uName').hideCol('storageName').hideCol('deliverCompany').hideCol('deliverNo').hideCol('operate').trigger('reloadGrid');
                $('#logGrid td').removeClass('ui-ellipsis').css({
                    'height': 'auto',
                    'line-height': '18px',
                    'word-wrap': 'break-word',
                    'word-break': 'break-all',
                    'white-space': 'normal'
                });
                $('#logGridWrap').after('<div class="h40 lh40 gray">发货地址：重庆市万州区百安坝街道523号5栋1楼；联系电话：023-58556299；官方网站：www.56dz.com</div>');
                var bdhtml = window.document.body.innerHTML;
                var sprnstr = '<!--startprint-->';
                var eprnstr = '<!--endprint-->';
                var prnhtml = bdhtml.substring(bdhtml.indexOf(sprnstr) + 17);
                prnhtml = prnhtml.substring(0, prnhtml.indexOf(eprnstr));
                window.document.body.innerHTML = prnhtml;
                window.print();
                window.location.reload();
            });
        });
        $('.wrapper').on('click', '#delete', function(a) {
            a.preventDefault();
            if (Business.verifyRight('SA_DELETE')) {
                originalData.id && $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                    Public.ajaxGet('/Bill/InvSa/delete', {
                        id: originalData.id
                    }, function(res) {
                        if (res && 200 == res.status) {
                            parent.$.dialog.tips('删除成功！');
                            parent.frames['sales-salesOrder'] && parent.frames['sales-salesOrder'].$('#search').trigger('click');
                            parent.frames['inventory-sales'] && parent.frames['inventory-sales'].$('#search').trigger('click');
                            parent.frames['inventory-salesBack'] && parent.frames['inventory-salesBack'].$('#search').trigger('click');
                            parent.tab.removeSelectedTabItem();
                        } else {
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
            if (Business.verifyRight('SA_UPDATE')) {
                Public.ajaxPost('/Bill/InvSa/updatedes', {
                    id: originalData.id,
                    newDes: $.trim($('#description').val())
                }, function(res) {
                    if (res && 200 == res.status) {
                        $.dialog.tips('备注保存成功！');
                        parent.frames['inventory-sales'] && parent.frames['inventory-sales'].$('#search').trigger('click');
                        parent.frames['inventory-salesBack'] && parent.frames['inventory-salesBack'].$('#search').trigger('click');
                    } else {
                        $.dialog.tips('备注保存失败！' + res.msg, 2);
                    }
                });
            }
        });
        $('#logGrid').on('click', '.log-hide', function(a) {
            var id = $(this).data('id');
            if (id) {
                $("#logGrid").jqGrid('delRowData', id);
            } else {
                $.dialog.tips('ID获取失败！', 2);
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
                var goodsInfo = $('#grid #' + id).data('goodsInfo');
                if (goodsInfo) {
                    var storageInfo = $('#grid #' + id).data('storageInfo');
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
                        refundId: rowData.refundId,
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
    if (urlParam.turnBySalesOrder) {
        $('.wrapper').append('<div id="div-lock"><div class="loading">单据准备中...</div></div>');
        var openerData = window.parent.document.getElementById(urlParam.tabId).openerData;
        Public.ajaxGet('/Bill/InvSo/queryDetails', {
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
        Public.ajaxGet('/Bill/InvSa/query', {
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
            transType: 102201,
            entries: [],
            description: '',
            totalOrderQty: 0,
            totalQty: 0,
            totalNowQty: 0
        };
        '102202' === urlParam.transType ? originalData.transType = 102202 : originalData.transType = 102201;
        THISPAGE.init(originalData);
    }
});
