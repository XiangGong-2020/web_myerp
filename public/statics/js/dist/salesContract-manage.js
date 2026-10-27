var urlParam = Public.urlParam();
var defaultPage = Public.getDefaultPage();
var qtyPlaces = Number(defaultPage.SYSTEM.qtyPlaces);
var pricePlaces = Number(defaultPage.SYSTEM.pricePlaces);
var amountPlaces = Number(defaultPage.SYSTEM.amountPlaces);
var isNewImportExcel = !1;
var contactCombo;
var ourselfCombo;
var company = [];
var zbjTypeCombo;
var goodsCombo;
var originalData;
var curRow;
var curCol;
var btn_draft = '<a id="draft" class="ui-btn bg-night"><i class="iconfont icon-baocun"></i><span>保存为草稿</span></a>';
var btn_final = '<a id="final" class="ui-btn bg-green"><i class="iconfont icon-baocun"></i><span>保存为正式</span></a>';
var btn_save = '<a id="save" class="ui-btn bg-green"><i class="iconfont icon-baocun"></i><span>保存</span></a>';
var btn_edit = '<a id="edit" class="ui-btn bg-green"><i class="iconfont icon-baocun"></i><span>保存</span></a>';
var btn_print = '<a id="print" class="ui-btn bg-yellow"><i class="iconfont icon-dayin"></i><span>打印整单</span></a><a id="printGrid" class="ui-btn bg-yellow"><i class="iconfont icon-dayin"></i><span>打印发货单</span></a>';
var btn_copy = '<a id="copy" class="ui-btn bg-teal"><i class="iconfont icon-fuzhi"></i><span>复制</span></a>';
var btn_check = '<a id="check" class="ui-btn bg-green"><i class="iconfont icon-shenhe"></i><span>审核</span></a>';
var btn_uncheck = '<a id="uncheck" class="ui-btn bg-red"><i class="iconfont icon-shenhe"></i><span>反审核</span></a>';
var btn_sign = '<a id="sign" class="ui-btn bg-green"><i class="iconfont icon-kuaidi"></i><span>交货</span><b>请真实交货后再标记</b></a>';
var btn_unsign = '<a id="unsign" class="ui-btn bg-red"><i class="iconfont icon-kuaidi"></i><span>取消交货</span></a>';
var btn_delete = '<a id="delete" class="ui-btn bg-red"><i class="iconfont icon-shanchu"></i><span>删除</span></a>';
var btn_del_srcmoney1 = '<a id="delsrcmoney1" class="ui-btn bg-red"><i class="iconfont icon-shanchu"></i><span>删除收款单</span></a>';
var btn_del_srcmoney2 = '<a id="delsrcmoney2" class="ui-btn bg-red"><i class="iconfont icon-shanchu"></i><span>删除退款单</span></a>';
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
        Public.ajaxGet('/Base/User/queryAll', {normal: 'yes'}, function(data) {
            if (data.status === 200) {
                var normalUser = disableUser = '';
                $.each(data.data.rows, function(i, v) {
                    if (v.isDisable == 0) {
                        normalUser += '<option value="' + v.id + '">' + v.name + '</option>';
                    } else {
                        disableUser += '<option value="' + v.id + '">' + v.name + '</option>';
                    }
                });
                $('#normalUser').html(normalUser);
                $('#disableUser').html(disableUser);
                $('#sysUser').html('<option value="1">系统</option>');
                $('#userCombo').fSelect({
                    showSearch: !0,
                    width: 200,
                    listHeight: 490
                });
                if (a.saler) {
                    $('.fs-input').val(a.saler);
                    if (a.redoSoId > 0) {
                        $('.fs-dropdown').addClass('dn').html('');
                        $('.fs-input').attr('disabled', true).addClass('ui-input-dis');
                    } else {
                        var names = a.saler.toString().split(',');
                        $(".fs-option").each(function() {
                            if (names.indexOf($(this).children('.fs-option-label').html()) != -1) {
                                $(this).addClass("selected");
                            }
                        });
                    }
                }
            }
        });
        zbjTypeCombo = $('#zbjType').combo({
            data: [
                {id: 0, name: '无保证金'},
                {id: 1, name: '另外支付保证金'},
                {id: 2, name: '从货款押保证金'}
            ],
            value: 'id',
            text: 'name',
            defaultSelected: a.zbjType >= 0 ? ['id', a.zbjType] : 0,
            width: 120
        }).getCombo();
        goodsCombo = Business.goodsCombo($('.goodsAuto'), {});
        if (a.id > 0) {
            a.checkId > 0 ? $('#mark').addClass('bill-has-checked') : $('#mark').removeClass('bill-has-checked');
            $('#toolTop').html(btn_edit + (a.isDraft == 1 ? btn_final : '') + btn_print + btn_copy + (a.checkId > 0 ? btn_uncheck : btn_check) + btn_delete + (a.srcMoneyId1 > 0 ? btn_del_srcmoney1 : '') + (a.srcMoneyId2 > 0 ? btn_del_srcmoney2 : ''));
            a.isDraft == 1 && $('#edit span').after('<b>当前为草稿</b>');
            $('#billNo').val(a.billNo);
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
            $('#amount').val(a.amount);
            $('#zbjAmount').val(a.zbjAmount);
            $('#billDate').val(a.billDate);
            $('#endDate').val(a.endDate);
            $('#description').val(a.description);
            $('#desBtn').show();
            $('#uName').html(a.uName);
            $('#createTime').html(a.createTime);
            $('#checkName').html(a.checkName);
            $('#modifyTime').html(a.modifyTime);
            if (Public.addDays(a.billDate, 25) < defaultPage.SYSTEM.endDate || a.moneyStatus1 > 0 || a.moneyStatus2 > 0) {
                $('#amount').attr('disabled', true).addClass('ui-input-dis');
            }
            if (a.isReq > 0 && originalData.reqNo) {
                $('#reqNo').html('已提交特采申请单：' + originalData.reqNo).removeClass('dn');
            }
            if (a.redoSoType == 0) {
                if (a.goodsStatus1 == 0) {
                    $('#toolTop').append(btn_sign);
                    $('#signmark').removeClass('bill-has-signed');
                } else {
                    $('#toolTop').append(btn_unsign);
                    $('#signmark').addClass('bill-has-signed');
                }
            }
            a.files && THISPAGE.addFiles(a.files);
        } else {
            $('#toolTop').html(btn_save + btn_draft);
            $('#billDate').val(defaultPage.SYSTEM.endDate);
            $('#endDate').val(Public.addDays(defaultPage.SYSTEM.endDate, 15));
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
            $('#amount').val(a.amount || '');
            $('#description').val(a.description || '');
            $('#uName').html(defaultPage.SYSTEM.uName || '');
        }
        if (a.redoSoId > 0) {
            $('#billDate').val(a.billDate);
            $('#endDate').val(Public.addDays(a.billDate, 7)).attr('disabled', true).addClass('ui-input-dis');
            contactCombo.disable();
            ourselfCombo.disable();
            zbjTypeCombo.disable();
            $('#amount').attr('disabled', true).addClass('ui-input-dis');
            $('#zbjAmount').attr('disabled', true).addClass('ui-input-dis');
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
                label: '选自有产品',
                width: 70,
                fixed: !0,
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
                    if (a || c.goodsId) {
                        var goodsInfo = $('#' + b.rowId).data('goodsInfo');
                        var rowData = $('#grid').jqGrid('getRowData', b.rowId);
                        if (goodsInfo && rowData) {
                            var data = {
                                zid: rowData.zid || b.rowId,
                                no: goodsInfo.no,
                                name: goodsInfo.name,
                                spec: goodsInfo.spec,
                                sku: goodsInfo.sku,
                                brand: goodsInfo.brandName,
                                unit: goodsInfo.unitName,
                                qty: rowData.qty || 1,
                                price: rowData.price || goodsInfo.salePrice
                            };
                            data.amount = data.qty * data.price;
                            $('#grid').jqGrid('setRowData', b.rowId, data);
                            THISPAGE.calTotal();
                        }
                    }
                    return '';
                }
            }, {
                name: 'zid',
                label: '自编序号',
                width: 56,
                fixed: !0,
                editable: !0,
                edittype: 'textarea',
                formatter: function(x, y, z) {
                    var zid = '';
                    if (!x) {
                        if (z.name) {
                            zid = y.rowId;
                        }
                    } else {
                        zid = x;
                    }
                    return zid;
                }
            }, {
                name: 'no',
                label: '产品编码',
                width: 60,
                fixed: !0,
                title: !0,
                editable: !0,
                edittype: 'textarea',
                classes: 'ui-ellipsis'
            }, {
                name: 'name',
                label: '产品名称',
                width: 120,
                title: !0,
                editable: !0,
                edittype: 'textarea',
                classes: 'ui-ellipsis'
            }, {
                name: 'spec',
                label: '规格型号',
                width: 100,
                title: !0,
                editable: !0,
                edittype: 'textarea',
                classes: 'ui-ellipsis'
            }, {
                name: 'sku',
                label: '辅助属性',
                width: 100,
                title: !0,
                editable: !0,
                edittype: 'textarea',
                classes: 'ui-ellipsis'
            }, {
                name: 'brand',
                label: '产品品牌',
                width: 70,
                fixed: !0,
                title: !0,
                editable: !0,
                edittype: 'textarea',
                classes: 'ui-ellipsis'
            }, {
                name: 'unit',
                label: '单位',
                width: 42,
                fixed: !0,
                editable: !0
            }, {
                name: 'qty',
                label: '数量',
                width: 80,
                fixed: !0,
                editable: !0,
                formatter: function(a, b, c) {
                    return Number(a) || '';
                }
            }, {
                name: 'limitPrice',
                label: '参考采购单价',
                hidden: !0,
                width: 100,
                fixed: !0,
                editable: !1,
                formatter: 'currency',
                formatoptions: {
                    showZero: !0,
                    decimalPlaces: pricePlaces
                }
            }, {
                name: 'price',
                label: '单价',
                width: 100,
                fixed: !0,
                editable: !0,
                formatter: 'currency',
                formatoptions: {
                    showZero: !0,
                    decimalPlaces: pricePlaces
                }
            }, {
                name: 'amount',
                label: '总价',
                width: 100,
                fixed: !0,
                editable: !0,
                formatter: 'currency',
                formatoptions: {
                    showZero: !0,
                    decimalPlaces: amountPlaces
                }
            }, {
                name: 'buyUrl',
                label: '参考采购链接',
                width: 100,
                title: !0,
                editable: !0,
                edittype: 'textarea',
                classes: 'ui-ellipsis'
            }, {
                name: 'description1',
                label: '备注1',
                width: 120,
                fixed: !0,
                title: !0,
                editable: !0,
                edittype: 'textarea',
                classes: 'ui-ellipsis'
            }, {
                name: 'description2',
                label: '备注2',
                width: 120,
                fixed: !0,
                title: !0,
                editable: !0,
                edittype: 'textarea',
                classes: 'ui-ellipsis'
            }, {
                name: 'isAlreadyReqPurStatus',
                label: '特采申请',
                width: 58,
                fixed: !0,
                hidden: originalData.id > 0 ? !1 : !0,
                align: 'center',
                formatter: function(a, b, c) {
                    if (c.isAlreadyReqPur > 0) {
                        return '<span class="green" title="' + c.alreadyReqPurInfo + '">已申请</span>';
                    } else {
                        return '<span class="gray">未申请</span>';
                    }
                }
            }, {
                name: 'purName',
                label: '采购人',
                width: 46,
                fixed: !0,
                classes: 'gray',
                hidden: originalData.id > 0 ? !1 : !0
            }, {
                name: 'isPurchased',
                label: '采买',
                width: 40,
                fixed: !0,
                hidden: originalData.id > 0 ? !1 : !0,
                align: 'center',
                formatter: function(a, b, c) {
                    if (c.purId > 0) {
                        var d = a == 1 ? '已买' : (a == 2 ? '不买' : '未买');
                        var e = a == 1 ? 'green' : (a == 2 ? 'black' : 'red');
                        return '<span class="' + e + '">' + d + '</span>';
                    } else {
                        return '';
                    }
                }
            }, {
                name: 'purFeedback',
                label: '采购反馈',
                width: 120,
                fixed: !0,
                hidden: originalData.id > 0 ? !1 : !0,
                title: !0,
                classes: 'gray ui-ellipsis'
            }, {
                name: 'id',
                label: 'ID',
                width: 0,
                hidden: !0
            }, {
                name: 'purId',
                label: 'purId',
                width: 0,
                hidden: !0
            }, {
                name: 'isAlreadyReqPur',
                label: 'isAlreadyReqPur',
                width: 46,
                hidden: !0
            }],
            cmTemplate: {sortable: !1, title: !1},
            localReader: {root: 'rows', records: 'records', repeatitems: !1, id: 'id'},
            jsonReader: {root: 'data.entries', records: 'records', repeatitems: !1, id: 'id'},
            width: Public.setGrid().w,
            height: '100%',
            altRows: !1,
            gridview: !0,
            rownumbers: !0,
            rownumWidth: 40,
            multiselect: !0,
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
            },
            afterSaveCell: function(rowId, cellName, cellValue, iRow, iCol) {
                switch (cellName) {
                    case 'name':
                        var rowData = $('#grid').jqGrid('getRowData', rowId);
                        if (cellValue && !rowData.zid) {
                            $('#grid').jqGrid('setRowData', rowId, {
                                zid: iRow
                            });
                        }
                        break;
                    case 'qty':
                        var rowData = $('#grid').jqGrid('getRowData', rowId);
                        if ($.isNumeric(cellValue) && $.isNumeric(rowData.price)) {
                            $('#grid').jqGrid('setRowData', rowId, {
                                amount: Number(parseFloat(cellValue) * parseFloat(rowData.price)).toFixed(2)
                            });
                            THISPAGE.calTotal();
                        }
                        break;
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
                                price: Number(parseFloat(cellValue) / parseFloat(rowData.qty)).toFixed(2)
                            });
                            THISPAGE.calTotal();
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
        Business.billsEvent(this, 'salesContract');
        laydate.render({
            elem: "#billDate, #endDate",
        });
        $('.wrapper').on('click', '#save', function(a) {
            a.preventDefault();
            if (Business.verifyRight('SC_ADD')) {
                var data = THISPAGE.getPostData();
                if (data) {
                    $('.wrapper').append('<div id="div-lock"><div class="loading">保存中...</div></div>');
                    data.isDraft = 0;
                    Public.ajaxPost('/Bill/InvSc/add', {
                        postData: JSON.stringify(data)
                    }, function(res) {
                        if (res && 200 == res.status) {
                            parent.parent.$.dialog.tips('新增成功！');
                            parent.frames['sales-salesOrder'] && parent.frames['sales-salesOrder'].$('#search').trigger('click');
                            parent.frames['bid-bidQuote'] && parent.frames['bid-bidQuote'].$('#search').trigger('click');
                            if (parent.frames['sales-salesContract']) {
                                parent.frames['sales-salesContract'].$('#search').trigger('click');
                            } else {
                                parent.tab.addTabItem({
                                    tabid: 'sales-salesContract',
                                    text: '<i class="iconfont icon-danju f14 mr5"></i>特殊销售',
                                    url: '/Uri/salesContract_list'
                                });
                                // 选择回原来的详情页，后面removeSelectedTabItem时才不会关闭刚打开的列表页
                                parent.tab.selectTabItem(urlParam.tabId);
                            }
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
            if (Business.verifyRight('SC_ADD')) {
                var data = THISPAGE.getPostData();
                if (data) {
                    $('.wrapper').append('<div id="div-lock"><div class="loading">保存中...</div></div>');
                    data.isDraft = 1;
                    Public.ajaxPost('/Bill/InvSc/add', {
                        postData: JSON.stringify(data)
                    }, function(res) {
                        if (res && 200 == res.status) {
                            parent.parent.$.dialog.tips('新增成功！');
                            parent.frames['sales-salesOrder'] && parent.frames['sales-salesOrder'].$('#search').trigger('click');
                            parent.frames['bid-bidQuote'] && parent.frames['bid-bidQuote'].$('#search').trigger('click');
                            if (parent.frames['sales-salesContract']) {
                                parent.frames['sales-salesContract'].$('#search').trigger('click');
                            } else {
                                parent.tab.addTabItem({
                                    tabid: 'sales-salesContract',
                                    text: '<i class="iconfont icon-danju f14 mr5"></i>特殊销售',
                                    url: '/Uri/salesContract_list'
                                });
                                // 选择回原来的详情页，后面removeSelectedTabItem时才不会关闭刚打开的列表页
                                parent.tab.selectTabItem(urlParam.tabId);
                            }
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
            if (Business.verifyRight('SC_UPDATE')) {
                if ($.inArray('SYS_ADMIN', defaultPage.SYSTEM.rights) !== -1 || (originalData.uId == defaultPage.SYSTEM.uId || originalData.uId == 1)) {
                    var data = THISPAGE.getPostData();
                    if (data) {
                        $('.wrapper').append('<div id="div-lock"><div class="loading">保存中...</div></div>');
                        data.isDraft = originalData.isDraft;
                        Public.ajaxPost('/Bill/InvSc/update', {
                            postData: JSON.stringify(data)
                        }, function(res) {
                            if (res && 200 == res.status) {
                                parent.parent.$.dialog.tips('更新成功！');
                                parent.frames['bid-bidQuote'] && parent.frames['bid-bidQuote'].$('#search').trigger('click');
                                if (parent.frames['sales-salesContract']) {
                                    parent.frames['sales-salesContract'].$('#search').trigger('click');
                                } else {
                                    parent.tab.addTabItem({
                                        tabid: 'sales-salesContract',
                                        text: '<i class="iconfont icon-danju f14 mr5"></i>特殊销售',
                                        url: '/Uri/salesContract_list'
                                    });
                                    // 选择回原来的详情页，后面removeSelectedTabItem时才不会关闭刚打开的列表页
                                    parent.tab.selectTabItem('sales-salesContract' + originalData.id);
                                }
                                parent.tab.removeSelectedTabItem();
                            } else {
                                $('.wrapper > #div-lock').remove();
                                $.dialog.tips('更新失败！' + res.msg, 2);
                            }
                        });
                    }
                } else {
                    $.dialog.alert('不可编辑别人录入的特殊销售单！仅本人或管理员可编辑！');
                }
            }
        });
        $('.wrapper').on('click', '#final', function(a) {
            a.preventDefault();
            if (Business.verifyRight('SC_UPDATE')) {
                if ($.inArray('SYS_ADMIN', defaultPage.SYSTEM.rights) !== -1 || (originalData.uId == defaultPage.SYSTEM.uId || originalData.uId == 1)) {
                    var data = THISPAGE.getPostData();
                    if (data) {
                        $('.wrapper').append('<div id="div-lock"><div class="loading">保存中...</div></div>');
                        data.isDraft = 0;
                        Public.ajaxPost('/Bill/InvSc/update', {
                            postData: JSON.stringify(data)
                        }, function(res) {
                            if (res && 200 == res.status) {
                                parent.parent.$.dialog.tips('更新成功！');
                                parent.frames['bid-bidQuote'] && parent.frames['bid-bidQuote'].$('#search').trigger('click');
                                if (parent.frames['sales-salesContract']) {
                                    parent.frames['sales-salesContract'].$('#search').trigger('click');
                                } else {
                                    parent.tab.addTabItem({
                                        tabid: 'sales-salesContract',
                                        text: '<i class="iconfont icon-danju f14 mr5"></i>特殊销售',
                                        url: '/Uri/salesContract_list'
                                    });
                                    // 选择回原来的详情页，后面removeSelectedTabItem时才不会关闭刚打开的列表页
                                    parent.tab.selectTabItem('sales-salesContract' + originalData.id);
                                }
                                parent.tab.removeSelectedTabItem();
                            } else {
                                $('.wrapper > #div-lock').remove();
                                $.dialog.tips('更新失败！' + res.msg, 2);
                            }
                        });
                    }
                } else {
                    $.dialog.alert('不可编辑别人录入的特殊销售单！仅本人或管理员可编辑！');
                }
            }
        });
        $('.wrapper').on('click', '#print', function(a) {
            a.preventDefault();
            if (Business.verifyRight('SC_PRINT')) {
                THISPAGE.preview(1);
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
        $('.wrapper').on('click', '#check', function(a) {
            a.stopPropagation();
            a.preventDefault();
            if (Business.verifyRight('SC_CHECK')) {
                originalData.id && Public.ajaxPost('/Bill/InvSc/check', {
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
                        parent.frames['sales-salesContract'] && parent.frames['sales-salesContract'].$('#search').trigger('click');
                    } else {
                        $.dialog.tips('审核失败！' + res.msg, 2);
                    }
                });
            }
        });
        $('.wrapper').on('click', '#uncheck', function(a) {
            a.stopPropagation();
            a.preventDefault();
            if (Business.verifyRight('SC_UNCHECK')) {
                originalData.id && Public.ajaxPost('/Bill/InvSc/check', {
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
                        parent.frames['sales-salesContract'] && parent.frames['sales-salesContract'].$('#search').trigger('click');
                    } else {
                        $.dialog.tips('反审核失败！' + res.msg, 2);
                    }
                });
            }
        });
        $('.wrapper').on('click', '#sign', function(a) {
            a.stopPropagation();
            a.preventDefault();
            if (Business.verifyRight('SC_UPDATE')) {
                originalData.id && Public.ajaxPost('/Bill/InvSc/upGoodsStatus1', {
                    id: originalData.id,
                    goodsStatus1: 1,
                    billNo: originalData.billNo,
                    transType: originalData.transType
                }, function(res) {
                    if (res && 200 == res.status) {
                        $.dialog.tips('标记已交货成功！');
                        originalData.goodsStatus1 = 1;
                        $('#signmark').addClass('bill-has-signed');
                        $('#sign').after(btn_unsign).remove();
                        parent.frames['sales-salesContract'] && parent.frames['sales-salesContract'].$('#search').trigger('click');
                        parent.frames['index'] && parent.frames['index'].$('#timelimited-refresh-btn').trigger('click');
                    } else {
                        $.dialog.tips('标记已交货失败！' + res.msg, 2);
                    }
                });
            }
        });
        $('.wrapper').on('click', '#unsign', function(a) {
            a.stopPropagation();
            a.preventDefault();
            if (Business.verifyRight('SC_UPDATE')) {
                originalData.id && Public.ajaxPost('/Bill/InvSc/upGoodsStatus1', {
                    id: originalData.id,
                    goodsStatus1: 0,
                    billNo: originalData.billNo,
                    transType: originalData.transType
                }, function(res) {
                    if (res && 200 == res.status) {
                        $.dialog.tips('标记未交货成功！');
                        originalData.goodsStatus1 = 0;
                        $('#signmark').removeClass('bill-has-signed');
                        $('#unsign').after(btn_sign).remove();
                        parent.frames['sales-salesContract'] && parent.frames['sales-salesContract'].$('#search').trigger('click');
                        parent.frames['index'] && parent.frames['index'].$('#timelimited-refresh-btn').trigger('click');
                    } else {
                        $.dialog.tips('标记未交货失败！' + res.msg, 2);
                    }
                });
            }
        });
        $('.wrapper').on('click', '#delete', function(a) {
            a.preventDefault();
            if (Business.verifyRight('SC_DELETE')) {
                originalData.id && $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                    Public.ajaxGet('/Bill/InvSc/delete', {
                        id: originalData.id
                    }, function(res) {
                        if (res && 200 == res.status) {
                            parent.$.dialog.tips('删除成功！');
                            parent.frames['sales-salesContract'] && parent.frames['sales-salesContract'].$('#search').trigger('click');
                            parent.frames['sales-salesOrder'] && parent.frames['sales-salesOrder'].$('#search').trigger('click');
                            parent.frames['bid-bidQuote'] && parent.frames['bid-bidQuote'].$('#search').trigger('click');
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
            if (Business.verifyRight('RECEIPT_DELETE')) {
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
                            parent.frames['sales-salesContract'] && parent.frames['sales-salesContract'].$('#search').trigger('click');
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
                            parent.frames['sales-salesContract'] && parent.frames['sales-salesContract'].$('#search').trigger('click');
                            parent.frames['money-payment'] && parent.frames['money-payment'].$('#search').trigger('click');
                            parent.frames['money-payment' + originalData.srcMoneyId2] && parent.tab.removeTabItem('money-payment' + originalData.srcMoneyId2);
                        } else {
                            $.dialog.tips('删除失败！' + res.msg, 2);
                        }
                    });
                });
            }
        });
        $('#exportGrid').on('click', function(a) {
            var billNo = $('#billNo').val();
            var href = '/Bill/InvSc/exportInfoGrid?id=' + originalData.id + '&billNo=' + billNo;
            $(this).attr('href', href);
        });
        $('#printGrid').on('click', function(a) {
            a.preventDefault();
            if (Business.verifyRight('SC_PRINT')) {
                THISPAGE.preview(2);
            }
        });
        $('#allToReq').on('click', function(a) {
            a.preventDefault();
            if (Business.verifyRight('SC_UPDATE')) {
                var newTabId = (new Date).format("yyyyMMddhhmmss");
                if (originalData.isReq > 0) {
                    $.dialog.confirm('已提交过特采申请单！确定需要再次发起特采申请？', function() {
                        var alreadyReqPurZids = [];
                        var ids = $('#grid').jqGrid('getDataIDs');
                        $.each(ids, function(k, v) {
                            var rowData = $('#grid').jqGrid('getRowData', v);
                            if (rowData.isAlreadyReqPur > 0) {
                                alreadyReqPurZids.push(rowData.zid);
                            }
                        });
                        if (alreadyReqPurZids.length > 0) {
                            parent.$.dialog.confirm('自编序号（' + alreadyReqPurZids.join() + '）共（' + alreadyReqPurZids.length + '个）产品已提交过特采申请！确定需要重复发起特采申请？', function() {
                                parent.tab.addTabItem({
                                    tabid: 'purchase-purchaseContractReq' + newTabId,
                                    text: '<i class="iconfont icon-danye f14 mr5"></i>特采申请单（新建）',
                                    url: '/Uri/purchaseContract_manage?copyFromContractSale&reqType=1&id=' + originalData.id + '&tabId=purchase-purchaseContractReq' + newTabId
                                });
                            });
                        } else {
                            parent.tab.addTabItem({
                                tabid: 'purchase-purchaseContractReq' + newTabId,
                                text: '<i class="iconfont icon-danye f14 mr5"></i>特采申请单（新建）',
                                url: '/Uri/purchaseContract_manage?copyFromContractSale&reqType=1&id=' + originalData.id + '&tabId=purchase-purchaseContractReq' + newTabId
                            });
                        }
                    });
                } else {
                    parent.tab.addTabItem({
                        tabid: 'purchase-purchaseContractReq' + newTabId,
                        text: '<i class="iconfont icon-danye f14 mr5"></i>特采申请单（新建）',
                        url: '/Uri/purchaseContract_manage?copyFromContractSale&reqType=1&id=' + originalData.id + '&tabId=purchase-purchaseContractReq' + newTabId
                    });
                }
            }
        });
        $('#selectedToReq').on('click', function(a) {
            a.preventDefault();
            if (Business.verifyRight('SC_UPDATE')) {
                var selectedIdArr = $('#grid').jqGrid('getGridParam', 'selarrrow');
                if (selectedIdArr.length > 0) {
                    var infoIds = selectedIdArr.join();
                    var newTabId = (new Date).format("yyyyMMddhhmmss");
                    if (originalData.isReq > 0) {
                        $.dialog.confirm('已提交过特采申请单！确定需要再次发起特采申请？', function() {
                            var alreadyReqPurZids = [];
                            $.each(selectedIdArr, function(k, v) {
                                var rowData = $('#grid').jqGrid('getRowData', v);
                                if (rowData.isAlreadyReqPur > 0) {
                                    alreadyReqPurZids.push(rowData.zid);
                                }
                            });
                            if (alreadyReqPurZids.length > 0) {
                                parent.$.dialog.confirm('自编序号（' + alreadyReqPurZids.join() + '）共（' + alreadyReqPurZids.length + '个）产品已提交过特采申请！确定需要重复发起特采申请？', function() {
                                    parent.tab.addTabItem({
                                        tabid: 'purchase-purchaseContractReq' + newTabId,
                                        text: '<i class="iconfont icon-danye f14 mr5"></i>特采申请单（新建）',
                                        url: '/Uri/purchaseContract_manage?copyFromContractSale&reqType=2&id=' + originalData.id + '&infoIds=' + infoIds + '&tabId=purchase-purchaseContractReq' + newTabId
                                    });
                                });
                            } else {
                                parent.tab.addTabItem({
                                    tabid: 'purchase-purchaseContractReq' + newTabId,
                                    text: '<i class="iconfont icon-danye f14 mr5"></i>特采申请单（新建）',
                                    url: '/Uri/purchaseContract_manage?copyFromContractSale&reqType=2&id=' + originalData.id + '&infoIds=' + infoIds + '&tabId=purchase-purchaseContractReq' + newTabId
                                });
                            }
                        });
                    } else {
                        parent.tab.addTabItem({
                            tabid: 'purchase-purchaseContractReq' + newTabId,
                            text: '<i class="iconfont icon-danye f14 mr5"></i>特采申请单（新建）',
                            url: '/Uri/purchaseContract_manage?copyFromContractSale&reqType=2&id=' + originalData.id + '&infoIds=' + infoIds + '&tabId=purchase-purchaseContractReq' + newTabId
                        });
                    }
                } else {
                    $.dialog.tips('请先勾选要申请采购的产品！', 2);
                }
            }
        });
        $('#copy').on('click', function(a) {
            a.preventDefault();
            if (Business.verifyRight('SC_ADD')) {
                var newTabId = (new Date).format("yyyyMMddhhmmss");
                parent.tab.addTabItem({
                    tabid: 'sales-salesContract' + newTabId,
                    text: '<i class="iconfont icon-danye f14 mr5"></i>特殊销售单（新建）',
                    url: '/Uri/salesContract_manage?copyFromContractSale&id=' + originalData.id + '&tabId=sales-salesContract' + newTabId
                });
            }
        });
        $('#desBtn').on('click', function(a) {
            a.preventDefault();
            if (Business.verifyRight('SC_UPDATE')) {
                if ($.inArray('SYS_ADMIN', defaultPage.SYSTEM.rights) !== -1 || originalData.uId == defaultPage.SYSTEM.uId) {
                    originalData.id && Public.ajaxPost('/Bill/InvSc/updatedes', {
                        id: originalData.id,
                        newDes: $.trim($('#description').val())
                    }, function(res) {
                        if (res && 200 == res.status) {
                            $.dialog.tips('备注保存成功！');
                            parent.frames['sales-salesContract'] && parent.frames['sales-salesContract'].$('#search').trigger('click');
                        } else {
                            $.dialog.tips('备注保存失败！' + res.msg, 2);
                        }
                    });
                } else {
                    $.dialog.alert('不可编辑别人录入的特殊销售单！仅本人或管理员可编辑！');
                }
            }
        });
        $('#add-excel').uploader({
            action: '/Bill/InvSc/uploadExcel',
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
            fileTypeExts: 'csv,xls,xlsx',
            onSuccess: function(e) {
                isNewImportExcel = !0;
                var data = JSON.parse(e.data);
                $('#grid').jqGrid('clearGridData').jqGrid('setGridParam', {
                    data: data.data.entries
                }).trigger('reloadGrid');
                THISPAGE.newId = data.data.entries.length + 1;
            }
        });
        $('#upFile').uploader({
            action: '/Bill/InvSc/uploadfile',
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
                !Business.verifyRight('SC_UPDATE') && a.preventDefault();
            },
            mouseover: function() {
                if ($(this).prev().hasClass('ui-btn')) {
                    $(this).prev().addClass('ui-btn-hover');
                } else {
                    $(this).prev().addClass('black');
                }
            },
            mouseleave: function() {
                if ($(this).prev().hasClass('ui-btn')) {
                    $(this).prev().removeClass('ui-btn-hover');
                } else {
                    $(this).prev().removeClass('black');
                }
            }
        });
        $('#files').on('click', '.fileRename', function() {
            if (Business.verifyRight('SC_UPDATE')) {
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
            if (Business.verifyRight('SC_UPDATE')) {
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
                '<a href="/data/upfile/contract/' + v.url + '" target="_blank" title="' + v.name + '"><span>' + v.name + '</span><span>' + v.upName + '</span><span>' + v.upTime + '</span></a>' +
                '<input type="text" class="renameInput dn" placeholder="输入新文件名">' +
                '<span class="renameSumbit dn">确定</span><span class="renameCancel dn">取消</span>' +
                '<i class="fileRename iconfont icon-bianji" title="改名"></i><i class="fileDel iconfont icon-shanchu" title="删除"></i>' +
                '</div>'
            );
        });
        $('#files').html(fileDiv);
    },
    calTotal: function() {
        for (var a = $('#grid').jqGrid('getDataIDs'), totalQty = 0, totalAmount = 0, d = 0, e = a.length; e > d; d++) {
            var f = a[d];
            var g = $('#grid').jqGrid('getRowData', f);
            g.qty && (totalQty += parseFloat(g.qty));
            g.amount && (totalAmount += parseFloat(g.amount));
        }
        $('#grid').jqGrid('footerData', 'set', {
            rn: '合计',
            qty: totalQty,
            amount: totalAmount
        });
    },
    getPostData: function() {
        curRow && curCol && (
            $('#grid').jqGrid('saveCell', curRow, curCol),
            curRow = curCol = null
        );
        var billDate = $.trim($('#billDate').val());
        var endDate = $.trim($('#endDate').val());
        if (endDate <= billDate) {
            $.dialog.tips('交货限期不合理！请录入合理的交货限期！', 2);
            return !1;
        }
        var amount = Number($.trim($('#amount').val()));
        if (amount == 0) {
            $.dialog.tips('货款金额不能为空或0！', 2);
            return !1;
        }
        var zbjType = zbjTypeCombo.getValue();
        var zbjAmount = Number($.trim($('#zbjAmount').val()));
        if (zbjType == 0 && zbjAmount > 0) {
            $.dialog.tips('无保证金时，请不要填写保证金金额！', 2);
            return !1;
        } else if (zbjType > 0 && zbjAmount == 0) {
            $.dialog.tips('有保证金时，请填写保证金金额！', 2);
            return !1;
        }
        var contactInput = $('#contact').find('input');
        if ('' === $.trim(contactInput.val())) {
            $('#contact').removeData('contactData');
            contactCombo.active = !0;
            contactCombo.doQuery();
            contactCombo.input.focus();
            $.dialog.tips('请选择买方(对方)！', 2);
            return !1;
        }
        var contactData = $('#contact').data('contactData');
        if (!contactData || contactData.id < 0) {
            setTimeout(function() {
                contactInput.focus().select();
            }, 10);
            $.dialog.tips('当前买方(对方)不存在！', 2);
            return !1;
        }
        var ourselfInput = $('#ourself').find('input');
        if ('' === $.trim(ourselfInput.val())) {
            $('#ourself').removeData('ourselfData');
            ourselfCombo.active = !0;
            ourselfCombo.doQuery();
            ourselfCombo.input.focus();
            $.dialog.tips('请选择卖方(我方)！', 2);
            return !1;
        }
        var ourselfData = $('#ourself').data('ourselfData');
        if (!ourselfData || ourselfData.id < 0) {
            setTimeout(function() {
                ourselfInput.focus().select();
            }, 10);
            $.dialog.tips('当前卖方(我方)不存在！', 2);
            return !1;
        }
        var entriesData = THISPAGE.getEntriesData();
        if (!entriesData) {
            return !1;
        }
        if (entriesData.length > 0) {
            var data = {
                isNewImportExcel: isNewImportExcel,
                srcBqId: originalData.srcBqId || 0,
                srcBqNo: originalData.srcBqNo || '',
                redoSoId: originalData.redoSoId || 0,
                redoSoNo: originalData.redoSoNo || '',
                redoSoType: originalData.redoSoType || 0,
                id: originalData.id,
                billType: 'SALE',
                transType: 101211,
                billNo: $.trim($('#billNo').val()),
                billDate: $.trim($('#billDate').val()),
                endDate: $.trim($('#endDate').val()),
                contactId: contactData.id,
                contactName: contactData.name,
                sId: ourselfData.id,
                sName: ourselfData.name,
                saler: $.trim($('.fs-input').val()),
                amount: amount,
                zbjAmount: zbjAmount,
                zbjType: zbjType,
                totalQty: $('#grid').jqGrid('footerData', 'get').qty.replace(/,/g, ''),
                entries: entriesData,
                description: $.trim($('#description').val()).replace(/"|'|{|}/g, '”'),
                files: originalData.files ? JSON.stringify(originalData.files) : ''
            };
            return data;
        } else {
            return $.dialog.tips('产品信息不能为空！', 2), $('#grid').jqGrid('editCell', 1, 2, !0), !1;
        }
    },
    getEntriesData() {
        for (var zids = [], entriesData = [], ids = $('#grid').jqGrid('getDataIDs'), i = 0, len = ids.length; len > i; i++) {
            var id = ids[i];
            var rowData = $('#grid').jqGrid('getRowData', id);

            //处理容易出问题的特殊字符
            rowData.zid = rowData.zid.replace(/"|'|{|}/g, '”');
            rowData.name = rowData.name.replace(/"|'|{|}/g, '”');
            rowData.spec = rowData.spec.replace(/"|'|{|}/g, '”');
            rowData.sku = rowData.sku.replace(/"|'|{|}/g, '”');
            rowData.brand = rowData.brand.replace(/"|'|{|}/g, '”');
            rowData.unit = rowData.unit.replace(/"|'|{|}/g, '”');
            rowData.buyUrl = rowData.buyUrl.replace(/"|'|{|}|%/g, '');
            rowData.description1 = rowData.description1.replace(/"|'|{|}/g, '”');
            rowData.description2 = rowData.description2.replace(/"|'|{|}/g, '”');

            if ('' == rowData.name) {
                if (rowData.qty != 0) {
                    $.dialog.tips('填入了产品数量时，产品名称不能为空！', 2);
                    return !1;
                }
            } else {
                if (rowData.zid == 0 || rowData.zid == '') {
                    $.dialog.tips('自编序号不允许为0或空！', 2);
                    return !1;
                }
                if (rowData.qty == 0 || rowData.qty == '') {
                    $.dialog.tips('产品数量不允许为0或空！', 2);
                    return !1;
                }
                if (rowData.buyUrl) {
                    if (rowData.buyUrl.indexOf('http') !== 0) {
                        $.dialog.tips('自编序号 ' + rowData.zid + ' 的参考采购链接格式错误！请只填入真实链接或留空。无真实链接但有其他提醒信息，则请填入备注里。', 2);
                        return !1;
                    } else if (rowData.buyUrl.indexOf('//cart.') !== -1 || rowData.buyUrl.indexOf('//trade.') !== -1) {
                        $.dialog.tips('自编序号 ' + rowData.zid + ' 的参考采购链接错误！不能为别人看不见的地址！', 2);
                        return !1;
                    }
                }
                var data = {
                    id: rowData.id || -1,
                    zid: rowData.zid || '',
                    no: rowData.no || '',
                    name: rowData.name,
                    spec: rowData.spec || '',
                    sku: rowData.sku || '',
                    brand: rowData.brand || '',
                    unit: rowData.unit || '',
                    qty: rowData.qty,
                    limitPrice: rowData.limitPrice || 0,
                    price: rowData.price,
                    amount: rowData.amount,
                    buyUrl: rowData.buyUrl || '',
                    description1: rowData.description1 || '',
                    description2: rowData.description2 || ''
                };
                entriesData.push(data);
                zids.push(rowData.zid);
            }
        }
        // 提取重复自编序号并处理
        let repeatZids = [];
        for (let k in zids) {
            if (zids.indexOf(zids[k]) !== zids.lastIndexOf(zids[k]) && repeatZids.indexOf(zids[k]) === -1) {
                repeatZids.push(zids[k]);
            }
        }
        if (repeatZids.length > 0) {
            $.dialog.tips('自编序号：' + repeatZids.toString() + ' 重复！', 2);
            return !1;
        }
        return entriesData;
    },
    preview: function(oper) {
        switch (oper) {
            case 1:
                $('#contractType').attr('value', '特殊销售单').removeClass('dn');
                $('#notice, .ui-combo-wrap .trigger, .ui-icon-ellipsis, .ui-icon-triangle-1-s, .fs-arrow, #desBtn, .upfile-title, .upfile-con, #excel-btns').hide();
                $('label, .ui-input, .ui-combo-wrap, .fs-wrap, .con-footer ul li span').css({'border': '0 none', 'border-bottom': '1px solid #ccc'});
                $('.wrapper').find('input').each(function() {
                    var value = $(this).val();
                    $(this).attr('value', value);
                });
                $('#files').css({'width': '100%', 'height': 'auto'});
                $('.file-line').css({'margin': '5px 10px'});
                $('#grid').hideCol('operate').hideCol('id').hideCol('goods').trigger('reloadGrid');
                $('#grid td').removeClass('ui-ellipsis').css({
                    'height': 'auto',
                    'line-height': '18px',
                    'word-wrap': 'break-word',
                    'word-break': 'break-all',
                    'white-space': 'normal'
                });
                /* 获取处理后的html代码 */
                var bdhtml = window.document.body.innerHTML;
                /* 设置打印开始区域 */
                var sprnstr = '<!--startprint' + oper + '-->';
                /* 设置打印结束区域 */
                var eprnstr = '<!--endprint' + oper + '-->';
                /* 从开始代码向后取html */
                var prnhtml = bdhtml.substring(bdhtml.indexOf(sprnstr) + 17 + oper.toString().length);
                /* 从结束代码向前取html */
                prnhtml = prnhtml.substring(0, prnhtml.indexOf(eprnstr));
                window.document.body.innerHTML = prnhtml;
                window.print();
                /* 将页面恢复成打印前状态 */
                window.location.reload();
                break;
            case 2:
                $.dialog.prompt('请输入自定义的整单发货备注（其他注意事项：浏览器分辨率1920，浏览器窗口最大化，打印预览界面选择横向打印，仔细核对发货种类和数量是否正确，不正确要先处理正确再打印）。', function(inputValue) {
                    var description = $.trim(inputValue);
                    $('#gridNo').html('<div class="lh50 f16 fb tc">发货单</div><div>' + ((originalData.redoSoNo && originalData.redoSoType == 1) ? ('原销售单 ' + originalData.redoSoNo + ' 另外发货') : '') + '特殊销售单号：' + originalData.billNo + '，客户名称：' + originalData.contactName + '，整单发货备注：' + description + '</div>').removeClass('dn');
                    $('#grid').hideCol('rn').hideCol('cb').hideCol('operate').hideCol('id').hideCol('goods').hideCol('limitPrice').hideCol('price').hideCol('amount').hideCol('buyUrl').hideCol('purName').hideCol('isPurchased').hideCol('purFeedback').trigger('reloadGrid');
                    $('#grid td').removeClass('ui-ellipsis').css({
                        'height': 'auto',
                        'line-height': '18px',
                        'word-wrap': 'break-word',
                        'word-break': 'break-all',
                        'white-space': 'normal'
                    });
                    $('.grid-wrap').after('<div class="h40 lh40 gray">发货地址：重庆市万州区百安坝街道523号5栋1楼；联系电话：023-58556299；官方网站：www.56dz.com</div>');
                    /* 获取处理后的html代码 */
                    var bdhtml = window.document.body.innerHTML;
                    /* 设置打印开始区域 */
                    var sprnstr = '<!--startprint' + oper + '-->';
                    /* 设置打印结束区域 */
                    var eprnstr = '<!--endprint' + oper + '-->';
                    /* 从开始代码向后取html */
                    var prnhtml = bdhtml.substring(bdhtml.indexOf(sprnstr) + 17 + oper.toString().length);
                    /* 从结束代码向前取html */
                    prnhtml = prnhtml.substring(0, prnhtml.indexOf(eprnstr));
                    window.document.body.innerHTML = prnhtml;
                    window.print();
                    /* 将页面恢复成打印前状态 */
                    window.location.reload();
                });
                break;
            default:
                break;
        }
    }
};
$(function() {
    if (urlParam.turnByBidQuote) {
        var openerData = window.parent.document.getElementById(urlParam.tabId).openerData;
        originalData = {
            id: -1,
            transType: 101211,
            srcBqId: parseInt(openerData.id),
            srcBqNo: openerData.billNo,
            contactId: parseInt(openerData.contactId),
            contactName: openerData.contactName,
            sId: parseInt(openerData.sId),
            sName: openerData.sName,
            saler: openerData.quotedName,
            amount: openerData.amount,
            entries: openerData.entries,
            description: openerData.description
        };
        THISPAGE.init(originalData);
    } else if (urlParam.turnBySalesOrder) {
        var openerData = window.parent.document.getElementById(urlParam.tabId).openerData;
        originalData = {
            redoSoId: parseInt(openerData.id),
            redoSoNo: openerData.billNo,
            redoSoType: parseInt(openerData.type),
            id: -1,
            transType: 101211,
            billDate: openerData.billDate,
            contactId: parseInt(openerData.contactId),
            contactName: openerData.contactName,
            sId: parseInt(openerData.sId),
            sName: openerData.sName,
            saler: openerData.saler,
            amount: openerData.amount,
            entries: [],
            description: '属于销售订单' + openerData.billNo + '另外发货'
        };
        THISPAGE.init(originalData);
    } else if (urlParam.id) {
        $('.wrapper').append('<div id="div-lock"><div class="loading">单据准备中...</div></div>');
        Public.ajaxGet('/Bill/InvSc/query', {
            id: urlParam.id
        }, function(res) {
            if (res && 200 == res.status) {
                originalData = res.data;
                if (urlParam.copyFromContractSale) {
                    originalData.id = -1;
                    originalData.transType = 101211;
                    $.dialog.alert('此为通过复制生成的新单，请按需编辑确认各项信息，检查无误后方可保存！');
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
            transType: 101211,
            entries: []
        };
        THISPAGE.init(originalData);
    }
});
