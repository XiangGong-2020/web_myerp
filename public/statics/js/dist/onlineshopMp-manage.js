var urlParam = Public.urlParam();
var defaultPage = Public.getDefaultPage();
var qtyPlaces = Number(defaultPage.SYSTEM.qtyPlaces);
var pricePlaces = Number(defaultPage.SYSTEM.pricePlaces);
var amountPlaces = Number(defaultPage.SYSTEM.amountPlaces);
var combo1 = Business.userCombo($('.userAuto'), {
    callback: {
        onChange: function(a) {
            var b = this.input.parents('tr');
            a && b.data('userInfo', a);
        }
    }
}, {normal: 'yes'});
var originalData;
var curRow;
var curCol;
var btn_draft = '<a id="draft" class="ui-btn bg-night"><i class="iconfont icon-baocun"></i><span>保存为草稿</span></a>';
var btn_final = '<a id="final" class="ui-btn bg-green"><i class="iconfont icon-baocun"></i><span>保存为正式</span></a>';
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
        if (a.id > 0) {
            a.checkId > 0 ? $('#mark').addClass('bill-has-checked') : $('#mark').removeClass('bill-has-checked');
            $('#toolTop').html(btn_edit + (a.isDraft == 1 ? btn_final : '') + btn_delete);
            a.isDraft == 1 && $('#edit span').after('<b>当前为草稿</b>');
            $('#billNo').val(a.billNo);
            $('#beginDate').val(a.beginDate);
            $('#endDate').val(a.endDate);
            $('#description').val(a.description);
            $('#desBtn').show();
            $('#uName').html(a.uName);
            $('#checkName').html(a.checkName);
            $('#createTime').html(a.createTime);
            $('#modifyTime').html(a.modifyTime);
        } else {
            $('#toolTop').html(btn_save + btn_draft);
            $('#billNo').val('WD' + Public.reduceMonths((new Date).format('yyyy-MM') + '-01', 1).replace(/-/g, '').substring(0, 6));
            $('#beginDate').val(Public.reduceMonths((new Date).format('yyyy-MM') + '-01', 1));
            $('#endDate').val(Public.reduceDays((new Date).format('yyyy-MM') + '-01', 1));
            $('#uName').html(defaultPage.SYSTEM.uName);
        }
    },
    initGrid: function(a) {
        if (a.id) {
            for (var b = 0; b < a.entries.length; b++) {
                a.entries[b].id = b + 1;
            }
            var c = 1 - a.entries.length;
            if (c > 0) {
                for (var d = 0; c > d; d++) {
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
                name: 'uName',
                label: '姓名',
                width: 76,
                fixed: !0,
                editable: !0,
                edittype: 'custom',
                editoptions: {
                    custom_element: function(a, b) {
                        var c = $('.userAuto')[0];
                        return c;
                    },
                    custom_value: function(a, b, c) {
                        if ('get' === b) {
                            if ('' !== $('.userAuto').getCombo().getValue()) {
                                return $(a).val();
                            }
                            var d = $(a).parents('tr');
                            return d.removeData('userInfo'), '';
                        }
                        'set' === b && $('input', a).val(c);
                    },
                    handle: function() {
                        $('#initCombo').append($('.userAuto').val(''));
                    },
                    trigger: 'ui-icon-triangle-1-s iconfont icon-xiajiantou'
                }
            }, {
                name: 'amount1',
                label: 'TB企业店业绩',
                width: 90,
                fixed: !0,
                editable: !0,
                formatter: 'currency',
                formatoptions: {
                    showZero: !0,
                    decimalPlaces: amountPlaces
                }
            }, {
                name: 'amount2',
                label: 'TB网店业绩',
                width: 90,
                fixed: !0,
                editable: !0,
                formatter: 'currency',
                formatoptions: {
                    showZero: !0,
                    decimalPlaces: amountPlaces
                }
            }, {
                name: 'amount3',
                label: 'TB学趣业绩',
                width: 90,
                fixed: !0,
                editable: !0,
                formatter: 'currency',
                formatoptions: {
                    showZero: !0,
                    decimalPlaces: amountPlaces
                }
            }, {
                name: 'amount4',
                label: 'TB和成聚业绩',
                width: 90,
                fixed: !0,
                editable: !0,
                formatter: 'currency',
                formatoptions: {
                    showZero: !0,
                    decimalPlaces: amountPlaces
                }
            }, {
                name: 'amount5',
                label: 'TB滕马业绩',
                width: 90,
                fixed: !0,
                editable: !0,
                formatter: 'currency',
                formatoptions: {
                    showZero: !0,
                    decimalPlaces: amountPlaces
                }
            }, {
                name: 'amount12',
                label: '天猫旗舰业绩',
                width: 90,
                fixed: !0,
                editable: !0,
                formatter: 'currency',
                formatoptions: {
                    showZero: !0,
                    decimalPlaces: amountPlaces
                }
            }, {
                name: 'amount6',
                label: 'PDD灿跃业绩',
                width: 90,
                fixed: !0,
                editable: !0,
                formatter: 'currency',
                formatoptions: {
                    showZero: !0,
                    decimalPlaces: amountPlaces
                }
            }, {
                name: 'amount7',
                label: 'PDD旗舰业绩',
                width: 90,
                fixed: !0,
                editable: !0,
                formatter: 'currency',
                formatoptions: {
                    showZero: !0,
                    decimalPlaces: amountPlaces
                }
            }, {
                name: 'amount8',
                label: 'PDD卓航业绩',
                width: 90,
                fixed: !0,
                editable: !0,
                formatter: 'currency',
                formatoptions: {
                    showZero: !0,
                    decimalPlaces: amountPlaces
                }
            }, {
                name: 'amount9',
                label: 'PDD恒熠业绩',
                width: 90,
                fixed: !0,
                editable: !0,
                formatter: 'currency',
                formatoptions: {
                    showZero: !0,
                    decimalPlaces: amountPlaces
                }
            }, {
                name: 'amount10',
                label: '1688业绩',
                width: 90,
                fixed: !0,
                editable: !0,
                formatter: 'currency',
                formatoptions: {
                    showZero: !0,
                    decimalPlaces: amountPlaces
                }
            }, {
                name: 'amount11',
                label: '抖店业绩',
                width: 90,
                fixed: !0,
                editable: !0,
                formatter: 'currency',
                formatoptions: {
                    showZero: !0,
                    decimalPlaces: amountPlaces
                }
            }, {
                name: 'amount',
                label: '总业绩',
                width: 90,
                fixed: !0,
                editable: !1,
                classes: 'green',
                formatter: 'currency',
                formatoptions: {
                    showZero: !0,
                    decimalPlaces: amountPlaces
                }
            }, {
                name: 'description',
                label: '备注',
                width: 100,
                title: !0,
                editable: !0,
                classes: 'ui-ellipsis gray'
            }],
            cmTemplate: {sortable: !1, title: !1},
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
                        $('#' + e).data('userInfo', {
                            id: f.uId,
                            name: f.uName
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
                if ('uName' === cellName) {
                    $('#' + rowId + '_uName', '#grid').val(cellValue);
                    combo1.selectByText(cellValue);
                    var rowData = $('#grid').jqGrid('getRowData', rowId);
                    if (!rowData.amount1 && !rowData.amount2 && !rowData.amount3 && !rowData.amount4 && !rowData.amount5 && !rowData.amount6 && !rowData.amount7 && !rowData.amount8 && !rowData.amount9 && !rowData.amount10 && !rowData.amount11 && !rowData.amount12 && !rowData.amount) {
                        $('#grid').jqGrid('setRowData', rowId, {
                            amount1: 0.00,
                            amount2: 0.00,
                            amount3: 0.00,
                            amount4: 0.00,
                            amount5: 0.00,
                            amount6: 0.00,
                            amount7: 0.00,
                            amount8: 0.00,
                            amount9: 0.00,
                            amount10: 0.00,
                            amount11: 0.00,
                            amount12: 0.00,
                            amount: 0.00
                        });
                    }
                    THISPAGE.calTotal();

                }
            },
            afterSaveCell: function(rowId, cellName, cellValue, iRow, iCol) {
                var array = ['amount1', 'amount2', 'amount3', 'amount4', 'amount5', 'amount6', 'amount7', 'amount8', 'amount9', 'amount10', 'amount11', 'amount12'];
                var index = $.inArray(cellName, array);
                if (index >= 0) {
                    var amount1 = parseFloat($('#grid').jqGrid('getCell', rowId, 'amount1'));
                    var amount2 = parseFloat($('#grid').jqGrid('getCell', rowId, 'amount2'));
                    var amount3 = parseFloat($('#grid').jqGrid('getCell', rowId, 'amount3'));
                    var amount4 = parseFloat($('#grid').jqGrid('getCell', rowId, 'amount4'));
                    var amount5 = parseFloat($('#grid').jqGrid('getCell', rowId, 'amount5'));
                    var amount6 = parseFloat($('#grid').jqGrid('getCell', rowId, 'amount6'));
                    var amount7 = parseFloat($('#grid').jqGrid('getCell', rowId, 'amount7'));
                    var amount8 = parseFloat($('#grid').jqGrid('getCell', rowId, 'amount8'));
                    var amount9 = parseFloat($('#grid').jqGrid('getCell', rowId, 'amount9'));
                    var amount10 = parseFloat($('#grid').jqGrid('getCell', rowId, 'amount10'));
                    var amount11 = parseFloat($('#grid').jqGrid('getCell', rowId, 'amount11'));
                    var amount12 = parseFloat($('#grid').jqGrid('getCell', rowId, 'amount12'));
                    var amount = 0;
                    $.isNumeric(amount1) && (amount += amount1);
                    $.isNumeric(amount2) && (amount += amount2);
                    $.isNumeric(amount3) && (amount += amount3);
                    $.isNumeric(amount4) && (amount += amount4);
                    $.isNumeric(amount5) && (amount += amount5);
                    $.isNumeric(amount6) && (amount += amount6);
                    $.isNumeric(amount7) && (amount += amount7);
                    $.isNumeric(amount8) && (amount += amount8);
                    $.isNumeric(amount9) && (amount += amount9);
                    $.isNumeric(amount10) && (amount += amount10);
                    $.isNumeric(amount11) && (amount += amount11);
                    $.isNumeric(amount12) && (amount += amount12);
                    $('#grid').jqGrid('setRowData', rowId, {
                        amount: amount
                    });
                    THISPAGE.calTotal();
                }
            }
        });
        $('#grid').jqGrid('setGridParam', {
            cellEdit: !0
        });
    },
    initEvent: function() {
        Business.billsEvent(this, 'onlineshopMp');
        laydate.render({
            elem: "#beginDate, #endDate",
            done: function() {
                setTimeout(function() {
                    $('#billNo').val('WD' + $.trim($('#beginDate').val()).replace(/-/g, '').substring(0, 6));
                }, 300);
            }
        });
        $('.wrapper').on('click', '#save', function(a) {
            a.preventDefault();
            if (Business.verifyRight('ONLINESHOPMP_ADVANCE')) {
                var data = THISPAGE.getPostData();
                if (data) {
                    $('.wrapper').append('<div id="div-lock"><div class="loading">保存中...</div></div>');
                    data.isDraft = 0;
                    Public.ajaxPost('/Bill/OnlineshopMp/add', {
                        postData: JSON.stringify(data)
                    }, function(res) {
                        if (res && 200 == res.status) {
                            parent.parent.$.dialog.tips('新增成功！');
                            parent.frames['sales-onlineshopMp'] && parent.frames['sales-onlineshopMp'].$('#refresh').trigger('click');
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
            if (Business.verifyRight('ONLINESHOPMP_ADVANCE')) {
                var data = THISPAGE.getPostData();
                if (data) {
                    $('.wrapper').append('<div id="div-lock"><div class="loading">保存中...</div></div>');
                    data.isDraft = 1;
                    Public.ajaxPost('/Bill/OnlineshopMp/add', {
                        postData: JSON.stringify(data)
                    }, function(res) {
                        if (res && 200 == res.status) {
                            parent.parent.$.dialog.tips('新增成功！');
                            parent.frames['sales-onlineshopMp'] && parent.frames['sales-onlineshopMp'].$('#refresh').trigger('click');
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
            if (Business.verifyRight('ONLINESHOPMP_ADVANCE')) {
                var data = THISPAGE.getPostData();
                if (data) {
                    $('.wrapper').append('<div id="div-lock"><div class="loading">保存中...</div></div>');
                    data.isDraft = originalData.isDraft;
                    Public.ajaxPost('/Bill/OnlineshopMp/update', {
                        postData: JSON.stringify(data)
                    }, function(res) {
                        if (res && 200 == res.status) {
                            parent.parent.$.dialog.tips('更新成功！');
                            parent.frames['sales-onlineshopMp'] && parent.frames['sales-onlineshopMp'].$('#refresh').trigger('click');
                            parent.tab.removeSelectedTabItem();
                        } else {
                            $('.wrapper > #div-lock').remove();
                            $.dialog.tips('更新失败！' + res.msg, 2);
                        }
                    });
                }
            }
        });
        $('.wrapper').on('click', '#final', function(a) {
            a.preventDefault();
            if (Business.verifyRight('ONLINESHOPMP_ADVANCE')) {
                var data = THISPAGE.getPostData();
                if (data) {
                    $('.wrapper').append('<div id="div-lock"><div class="loading">保存中...</div></div>');
                    data.isDraft = 0;
                    Public.ajaxPost('/Bill/OnlineshopMp/update', {
                        postData: JSON.stringify(data)
                    }, function(res) {
                        if (res && 200 == res.status) {
                            parent.parent.$.dialog.tips('更新成功！');
                            parent.frames['sales-onlineshopMp'] && parent.frames['sales-onlineshopMp'].$('#refresh').trigger('click');
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
            if (Business.verifyRight('ONLINESHOPMP_ADVANCE')) {
                originalData.id && $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                    Public.ajaxGet('/Bill/OnlineshopMp/delete', {
                        id: originalData.id
                    }, function(res) {
                        if (res && 200 == res.status) {
                            parent.$.dialog.tips('删除成功！');
                            parent.frames['sales-onlineshopMp'] && parent.frames['sales-onlineshopMp'].$('#refresh').trigger('click');
                            parent.tab.removeSelectedTabItem();
                        } else {
                            $.dialog.tips('删除失败！' + res.msg, 2);
                        }
                    });
                });
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
        $('#desBtn').on('click', function(a) {
            a.preventDefault();
            if (Business.verifyRight('ONLINESHOPMP_ADVANCE')) {
                Public.ajaxPost('/Bill/OnlineshopMp/updatedes', {
                    id: originalData.id,
                    newDes: $.trim($('#description').val())
                }, function(res) {
                    if (200 === res.status) {
                        $.dialog.tips('备注保存成功！');
                        parent.frames['sales-onlineshopMp'] && parent.frames['sales-onlineshopMp'].$('#refresh').trigger('click');
                    } else {
                        $.dialog.tips('备注保存失败！' + res.msg, 2);
                    }
                });
            }
        });
        $(window).on('resize', function() {
            Public.autoGridWidth($('#grid'));
        });
    },
    calTotal: function() {
        for (var a = $('#grid').jqGrid('getDataIDs'), b = 0, c = 0, d = a.length; d > c; c++) {
            var e = a[c];
            var f = $('#grid').jqGrid('getRowData', e);
            f.amount && (b += parseFloat(f.amount));
        }
        $('#grid').jqGrid('footerData', 'set', {
            rn: '合计',
            amount: b
        });
    },
    getEntriesData: function() {
        for (var entriesData = [], ids = $('#grid').jqGrid('getDataIDs'), i = 0, len = ids.length; len > i; i++) {
            var id = ids[i];
            var rowData = $('#grid').jqGrid('getRowData', id);
            var userInfo = $('#grid tr#' + id).data('userInfo');
            if (rowData && userInfo) {
                var data = {
                    uId: userInfo.id,
                    uName: userInfo.name,
                    amount1: rowData.amount1 || 0,
                    amount2: rowData.amount2 || 0,
                    amount3: rowData.amount3 || 0,
                    amount4: rowData.amount4 || 0,
                    amount5: rowData.amount5 || 0,
                    amount6: rowData.amount6 || 0,
                    amount7: rowData.amount7 || 0,
                    amount8: rowData.amount8 || 0,
                    amount9: rowData.amount9 || 0,
                    amount10: rowData.amount10 || 0,
                    amount11: rowData.amount11 || 0,
                    amount12: rowData.amount12 || 0,
                    amount: rowData.amount,
                    description: rowData.description
                };
                entriesData.push(data);
            }
        }
        return entriesData;
    },
    getPostData: function() {
        curRow && curCol && ($('#grid').jqGrid('saveCell', curRow, curCol), curRow = curCol = null);
        var entriesData = THISPAGE.getEntriesData();
        if (entriesData && entriesData.length > 0) {
            THISPAGE.calTotal();
            var data = {
                id: originalData.id,
                billNo: $.trim($('#billNo').val()),
                beginDate: $.trim($('#beginDate').val()),
                endDate: $.trim($('#endDate').val()),
                entries: entriesData,
                amount: $('#grid').jqGrid('footerData', 'get').amount.replace(/,/g, ''),
                description: $.trim($('#description').val())
            };
            if (data.billNo.length < 8) {
                return $.dialog.tips('单据编号长度不合规！', 2), !1;
            }
            if (data.beginDate.substring(0, 7) != data.endDate.substring(0, 7)) {
                return $.dialog.tips('起始日期和结束日期不在同月！', 2), !1;
            }
            return data;
        }
        return $.dialog.tips('表格不能为空！', 2), $('#grid').jqGrid('editCell', 1, 2, !0), !1;
    }
};
$(function() {
    if (urlParam.id) {
        $('.wrapper').append('<div id="div-lock"><div class="loading">单据准备中...</div></div>');
        Public.ajaxGet('/Bill/OnlineshopMp/query', {
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
            entries: [],
            amount: '0.00'
        };
        THISPAGE.init(originalData);
    }
});
