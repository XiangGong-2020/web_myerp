var urlParam = Public.urlParam();
var defaultPage = Public.getDefaultPage();
var qtyPlaces = Number(defaultPage.SYSTEM.qtyPlaces);
var pricePlaces = Number(defaultPage.SYSTEM.pricePlaces);
var amountPlaces = Number(defaultPage.SYSTEM.amountPlaces);
var originalData;
var curRow;
var curCol;
var btn_final = '<a id="final" class="ui-btn bg-green"><i class="iconfont icon-baocun"></i><span>保存为正式</span></a>';
var btn_edit = '<a id="edit" class="ui-btn bg-green"><i class="iconfont icon-baocun"></i><span>保存</span></a>';
var btn_export = '<a id="export" class="ui-btn bg-teal" ><i class="iconfont icon-daochu"></i><span>导出</span></a>';
var btn_check = '<a id="check" class="ui-btn bg-green"><i class="iconfont icon-shenhe"></i><span>审核</span></a>';
var btn_uncheck = '<a id="uncheck" class="ui-btn bg-red"><i class="iconfont icon-shenhe"></i><span>反审核</span></a>';
var btn_delete = '<a id="delete" class="ui-btn bg-red"><i class="iconfont icon-shanchu"></i><span>删除</span></a>';
var btn_del_srcmoney1 = '<a id="delsrcmoney1" class="ui-btn bg-red"><i class="iconfont icon-shanchu"></i><span>删除付款单</span></a>';
var btn_del_srcmoney2 = '<a id="delsrcmoney2" class="ui-btn bg-red"><i class="iconfont icon-shanchu"></i><span>删除退款单</span></a>';
var THISPAGE = {
    init: function(a) {
        THISPAGE.initDom(a);
        THISPAGE.initGrid(a);
        THISPAGE.initEvent();
    },
    initDom: function(a) {
        if (a.id > 0) {
            if (a.checkId > 0) {
                $('#mark').addClass('bill-has-checked');
                THISPAGE.disableEdit();
            } else {
                $('#mark').removeClass('bill-has-checked');
                THISPAGE.enableEdit();
            }
            $('#billFrom').val(a.company);
            $('#billNo').val(a.billNo);
            $('#deliverDate').val(a.deliverDate);
            $('#amount1').val(a.amount1);
            $('#amount2').val(a.amount2);
            $('#amount').val(a.amount);
            $('#uName').html(a.uName);
            $('#checkName').html(a.checkName);
            $('#modifyTime').html(a.modifyTime);
            $('#createTime').html(a.createTime);
            $('#description').val(a.description);
            $('#desBtn').show();
        }
    },
    initGrid: function(a) {
        $('#grid').jqGrid({
            data: a.entries,
            datatype: 'clientSide',
            colModel: [{
                name: 'company',
                label: '快递公司',
                width: 60,
                fixed: !0
            }, {
                name: 'no',
                label: '运单号',
                width: 130,
                fixed: !0
            }, {
                name: 'date',
                label: '入网日期',
                width: 80,
                fixed: !0
            }, {
                name: 'address1',
                label: '对方目的地',
                width: 70,
                fixed: !0,
                title: !0
            }, {
                name: 'weight1',
                label: '对方计重(kg)',
                width: 90,
                fixed: !0,
                classes: 'f14 yellow'
            }, {
                name: 'amount1',
                label: '对方计费(元)',
                width: 120,
                fixed: !0,
                classes: 'f14 red'
            }, {
                name: 'noCheck',
                label: '我方有无此单',
                width: 80,
                fixed: !0,
                formatter: function(a, b, c) {
                    return 0 == a ? '无' : (1 == a ? '有' : '&nbsp;');
                },
                editable: !0,
                edittype: 'select',
                editoptions: {
                    value: {
                        0: '无',
                        1: '有'
                    }
                }
            }, {
                name: 'address2',
                label: '我方目的地',
                width: 70,
                fixed: !0,
                title: !0,
                editable: !0
            }, {
                name: 'weight2',
                label: '我方计重(kg)',
                width: 90,
                fixed: !0,
                editable: !0,
                classes: 'f14 yellow'
            }, {
                name: 'amount2',
                label: '我方计费(元)',
                width: 120,
                fixed: !0,
                editable: !0,
                classes: 'f14 red'
            }, {
                name: 'printTime',
                label: '打印时间',
                width: 130,
                fixed: !0,
                editable: !0
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
            rowNum: 20000,
            cellEdit: !0,
            cellsubmit: 'clientArray',
            triggerAdd: !1,
            loadonce: !0,
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
            afterSaveCell: function(rowId, cellName, cellValue, iRow, iCol) {
                if (Business.verifyRight('DELIVER_UPDATE')) {
                    var rowData = $('#grid').jqGrid('getRowData', rowId);
                    if (rowData.no) {
                        Public.ajaxPost('/Bill/Deliver/upDeliverInfo', {
                            id: rowId,
                            cellName: cellName,
                            cellValue: cellValue
                        }, function(res) {
                            if (res && 200 == res.status) {
                                $.dialog.tips('运单信息更新成功！');
                                if ('noCheck' == cellName || 'amount2' == cellName) {
                                    Public.ajaxPost('/Bill/Deliver/count_amount2', {
                                        id: originalData.id
                                    }, function(res2) {
                                        if (res2 && 200 === res2.status) {
                                            $.dialog.tips('我方总额更新成功！');
                                            $('#amount2').val(res2.data);
                                            parent.frames['deliver-deliver'] && parent.frames['deliver-deliver'].$('#refresh').trigger('click');
                                        } else {
                                            $.dialog.tips(res2.msg, 2);
                                        }
                                    });
                                }
                            } else {
                                $.dialog.tips(res.msg, 2);
                            }
                        });
                    }
                }
            }
        });
        $('#grid2').jqGrid({
            url: '/Bill/Deliver/query',
            datatype: 'json',
            postData: {
                id: originalData.id,
                type: 'above25'
            },
            colModel: [{
                name: 'company',
                label: '快递公司',
                width: 60,
                fixed: !0
            }, {
                name: 'no',
                label: '运单号',
                width: 130,
                fixed: !0
            }, {
                name: 'date',
                label: '入网日期',
                width: 80,
                fixed: !0,
                sortable: !0
            }, {
                name: 'address1',
                label: '对方目的地',
                width: 70,
                fixed: !0
            }, {
                name: 'weight1',
                label: '对方计重(kg)',
                width: 90,
                fixed: !0,
                sortable: !0
            }, {
                name: 'amount1',
                label: '对方计费(元)',
                width: 120,
                fixed: !0,
                sortable: !0
            }, {
                name: 'noCheck',
                label: '我方有无此单',
                width: 80,
                fixed: !0,
                formatter: function(a, b, c) {
                    return 0 == a ? '无' : (1 == a ? '有' : '&nbsp;');
                },
                editable: !0,
                edittype: 'select',
                editoptions: {
                    value: {
                        0: '无',
                        1: '有'
                    }
                }
            }, {
                name: 'address2',
                label: '我方目的地',
                width: 70,
                fixed: !0,
                editable: !0
            }, {
                name: 'weight2',
                label: '我方计重(kg)',
                width: 90,
                fixed: !0,
                sortable: !0,
                editable: !0
            }, {
                name: 'amount2',
                label: '我方计费(元)',
                width: 120,
                fixed: !0,
                sortable: !0,
                editable: !0
            }, {
                name: 'printTime',
                label: '打印时间',
                width: 130,
                fixed: !0,
                editable: !0
            }, {
                name: 'description',
                label: '备注',
                width: 100,
                title: !0,
                editable: !0,
                classes: 'ui-ellipsis gray'
            }],
            cmTemplate: {sortable: !1, title: !1},
            jsonReader: {root: 'data.entries', repeatitems: !1, id: 'id'},
            autowidth: !0,
            height: '100%',
            gridview: !0,
            rownumbers: !0,
            onselectrow: !1,
            shrinkToFit: !0,
            forceFit: !1,
            viewsortcols: [true, 'vertical', true],
            sortname: 'date',
            sortorder: 'DESC',
            rowNum: 20000,
            cellEdit: !0,
            cellsubmit: 'clientArray',
            triggerAdd: !1,
            loadError: function(a, b) {
                $.dialog.tips('Type: ' + b + '; Response: ' + a.status + ' ' + a.statusText, 2);
            },
            gridComplete: function() {
                setTimeout(function() {
                    Public.autoGridWidth($('#grid2'));
                }, 10);
                $('#div-lock').remove();
            },
            beforeEditCell: function(rowId, cellName, cellValue, iRow, iCol) {
                curRow = iRow;
                curCol = iCol;
            },
            afterSaveCell: function(rowId, cellName, cellValue, iRow, iCol) {
                if (Business.verifyRight('DELIVER_UPDATE')) {
                    var rowData = $('#grid2').jqGrid('getRowData', rowId);
                    if (rowData.no) {
                        Public.ajaxPost('/Bill/Deliver/upDeliverInfo', {
                            id: rowId,
                            cellName: cellName,
                            cellValue: cellValue
                        }, function(res) {
                            if (res && 200 == res.status) {
                                $.dialog.tips('运单信息更新成功！');
                                if ('noCheck' == cellName || 'amount2' == cellName) {
                                    Public.ajaxPost('/Bill/Deliver/count_amount2', {
                                        id: originalData.id
                                    }, function(res2) {
                                        if (res2 && 200 === res2.status) {
                                            $.dialog.tips('我方总额更新成功！');
                                            $('#amount2').val(res2.data);
                                            parent.frames['money-deliver'] && parent.frames['money-deliver'].$('#refresh').trigger('click');
                                        } else {
                                            $.dialog.tips('我方总额更新失败！' + res2.msg, 2);
                                        }
                                    });
                                }
                            } else {
                                $.dialog.tips('运单信息更新失败！' + res.msg, 2);
                            }
                        });
                    }
                }
            }
        });
    },
    initEvent: function() {
        $('.wrapper').on('click', '#edit', function(a) {
            a.preventDefault();
            if (Business.verifyRight('DELIVER_UPDATE')) {
                $('#grid').jqGrid('saveCell', curRow, curCol);
                $('#grid2').jqGrid('saveCell', curRow, curCol);
                var data = THISPAGE.getPostData();
                if (data) {
                    $('.wrapper').append('<div id="div-lock"><div class="loading">保存中...</div></div>');
                    data.isDraft = originalData.isDraft;
                    Public.ajaxPost('/Bill/Deliver/update', {
                        postData: JSON.stringify(data)
                    }, function(res) {
                        if (res && 200 == res.status) {
                            parent.$.dialog.tips('保存成功！');
                            parent.frames['deliver-deliver'] && parent.frames['deliver-deliver'].$('#refresh').trigger('click');
                            parent.tab.removeSelectedTabItem();
                        } else {
                            $('.wrapper > #div-lock').remove();
                            $.dialog.tips('保存失败！' + res.msg, 2);
                        }
                    });
                }
            }
        });
        $('.wrapper').on('click', '#final', function(a) {
            a.preventDefault();
            if (Business.verifyRight('DELIVER_UPDATE')) {
                $('#grid').jqGrid('saveCell', curRow, curCol);
                $('#grid2').jqGrid('saveCell', curRow, curCol);
                var data = THISPAGE.getPostData();
                if (data) {
                    $('.wrapper').append('<div id="div-lock"><div class="loading">保存中...</div></div>');
                    data.isDraft = 0;
                    Public.ajaxPost('/Bill/Deliver/update', {
                        postData: JSON.stringify(data)
                    }, function(res) {
                        if (res && 200 == res.status) {
                            parent.$.dialog.tips('保存成功！');
                            parent.frames['deliver-deliver'] && parent.frames['deliver-deliver'].$('#refresh').trigger('click');
                            parent.tab.removeSelectedTabItem();
                        } else {
                            $('.wrapper > #div-lock').remove();
                            $.dialog.tips('保存失败！' + res.msg, 2);
                        }
                    });
                }
            }
        });
        $('.wrapper').on('click', '#export', function(a) {
            if (!Business.verifyRight('DELIVER_EXPORT')) {
                $('#grid').jqGrid('saveCell', curRow, curCol);
                $('#grid2').jqGrid('saveCell', curRow, curCol);
                return void a.preventDefault();
            }
            var href = '/Bill/Deliver/export?id=' + originalData.id;
            $(this).attr('href', href);
        });
        $('.wrapper').on('click', '#check', function(a) {
            a.stopPropagation();
            a.preventDefault();
            if (Business.verifyRight('DELIVER_CHECK')) {
                Public.ajaxPost('/Bill/Deliver/check', {
                    id: originalData.id,
                    checkId: defaultPage.SYSTEM.uId,
                    checkName: defaultPage.SYSTEM.uName,
                    billNo: originalData.billNo,
                    transType: 109101
                }, function(res) {
                    if (res && 200 == res.status) {
                        $.dialog.tips('审核成功！');
                        originalData.checkId = defaultPage.SYSTEM.uId;
                        originalData.checkName = defaultPage.SYSTEM.uName;
                        $('#mark').addClass('bill-has-checked');
                        THISPAGE.disableEdit();
                        $('#checkName').html(defaultPage.SYSTEM.uName);
                        parent.frames['deliver-deliver'] && parent.frames['deliver-deliver'].$('#refresh').trigger('click');
                    } else {
                        $.dialog.tips('审核失败！' + res.msg, 2);
                    }
                });
            }
        });
        $('.wrapper').on('click', '#uncheck', function(a) {
            a.stopPropagation();
            a.preventDefault();
            if (Business.verifyRight('DELIVER_UNCHECK')) {
                Public.ajaxPost('/Bill/Deliver/check', {
                    id: originalData.id,
                    checkId: 0,
                    checkName: '',
                    billNo: originalData.billNo,
                    transType: 109101
                }, function(res) {
                    if (res && 200 == res.status) {
                        $.dialog.tips('反审核成功！');
                        originalData.checkId = 0;
                        originalData.checkName = '';
                        $('#mark').removeClass('bill-has-checked');
                        THISPAGE.enableEdit();
                        $('#checkName').html('');
                        parent.frames['deliver-deliver'] && parent.frames['deliver-deliver'].$('#refresh').trigger('click');
                    } else {
                        $.dialog.tips('反审核失败！' + res.msg, 2);
                    }
                });
            }
        });
        $('.wrapper').on('click', '#delete', function(a) {
            a.preventDefault();
            if (Business.verifyRight('DELIVER_DELETE')) {
                originalData.id && $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                    Public.ajaxGet('/Bill/Deliver/delete', {
                        id: originalData.id
                    }, function(res) {
                        if (res && 200 == res.status) {
                            parent.$.dialog.tips('删除成功！');
                            parent.frames['deliver-deliver'] && parent.frames['deliver-deliver'].$('#refresh').trigger('click');
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
                            parent.frames['deliver-deliver'] && parent.frames['deliver-deliver'].$('#refresh').trigger('click');
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
                            parent.frames['deliver-deliver'] && parent.frames['deliver-deliver'].$('#refresh').trigger('click');
                            parent.frames['money-receipt'] && parent.frames['money-receipt'].$('#search').trigger('click');
                            parent.frames['money-receipt' + originalData.srcMoneyId2] && parent.tab.removeTabItem('money-receipt' + originalData.srcMoneyId2);
                        } else {
                            $.dialog.tips('删除失败！' + res.msg, 2);
                        }
                    });
                });
            }
        });
        $('#search').on('click', function() {
            $('#grid').jqGrid('saveCell', curRow, curCol);
            $('#grid2').jqGrid('saveCell', curRow, curCol);
            $('.wrapper').append('<div id="div-lock"><div class="loading">查询中...</div></div>');
            $('#grid2').jqGrid('setGridParam', {
                postData: {
                    id: originalData.id,
                    type: 'normal',
                    weight: $('#search-weight').val(),
                    amount: $('#search-amount').val(),
                    no: $('#search-no').val()
                }
            }).trigger('reloadGrid');
            $('.ui-jqgrid .loading').hide();
        });
        $('.wrapper').on('click', '#export-top', function(a) {
            if (!Business.verifyRight('DELIVER_EXPORT')) {
                $('#grid').jqGrid('saveCell', curRow, curCol);
                $('#grid2').jqGrid('saveCell', curRow, curCol);
                return void a.preventDefault();
            }
            var b = '/Bill/Deliver/export_top?id=' + originalData.id;
            $(this).attr('href', b);
        });
        $('#clear').on('click', function() {
            $('#grid').jqGrid('saveCell', curRow, curCol);
            $('#grid2').jqGrid('saveCell', curRow, curCol);
            $('#grid2').jqGrid('clearGridData');
        });
        $('#desBtn').on('click', function(a) {
            a.preventDefault();
            if (Business.verifyRight('DELIVER_UPDATE')) {
                Public.ajaxPost('/Bill/Deliver/updatedes', {
                    id: originalData.id,
                    newDes: $.trim($('#description').val())
                }, function(res) {
                    if (res && 200 == res.status) {
                        $.dialog.tips('备注保存成功！');
                        parent.frames['deliver-deliver'] && parent.frames['deliver-deliver'].$('#refresh').trigger('click');
                    } else {
                        $.dialog.tips('备注保存失败！' + res.msg, 2);
                    }
                });
            }
        });
        $(window).on('resize', function() {
            Public.autoGridWidth($('#grid'));
            Public.autoGridWidth($('#grid2'));
        });
    },
    getPostData: function(a) {
        curRow && curCol && ($('#grid').jqGrid('saveCell', curRow, curCol), $('#grid2').jqGrid('saveCell', curRow, curCol), curRow = curCol = null);
        var h = {
            id: originalData.id,
            billDate: originalData.billDate,
            billNo: originalData.billNo,
            amount: $.trim($('#amount').val()) || 0,
            description: $.trim($('#description').val())
        };
        return h;
    },
    disableEdit: function() {
        $('#toolTop').html(btn_export + (originalData.checkId > 0 ? btn_uncheck : btn_check) + btn_delete + (originalData.srcMoneyId1 > 0 ? btn_del_srcmoney1 : '') + (originalData.srcMoneyId2 > 0 ? btn_del_srcmoney2 : ''));
        $('#amount').attr('disabled', 'disabled').addClass('ui-input-dis');
        $('#grid').jqGrid('setGridParam', {
            cellEdit: !1
        });
        $('#grid2').jqGrid('setGridParam', {
            cellEdit: !1
        });
    },
    enableEdit: function() {
        $('#toolTop').html(btn_edit + (originalData.isDraft == 1 ? btn_final : '') + btn_export + (originalData.checkId > 0 ? btn_uncheck : btn_check) + btn_delete + (originalData.srcMoneyId1 > 0 ? btn_del_srcmoney1 : '') + (originalData.srcMoneyId2 > 0 ? btn_del_srcmoney2 : ''));
        originalData.isDraft == 1 && $('#edit span').after('<b>当前为草稿</b>');
        $('#amount').removeAttr('disabled').removeClass('ui-input-dis');
        $('#grid').jqGrid('setGridParam', {
            cellEdit: !0
        });
        $('#grid2').jqGrid('setGridParam', {
            cellEdit: !0
        });
    }
};
$(function() {
    if (urlParam.id) {
        $('.wrapper').append('<div id="div-lock"><div class="loading">单据准备中...</div></div>');
        Public.ajaxGet('/Bill/Deliver/query', {
            id: urlParam.id,
            type: 'warning'
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
            entries: [{id: '1'}],
            totalAmount: 0,
            amount: '0.00'
        };
        THISPAGE.init(originalData);
    }
});
