var urlParam = Public.urlParam();
var defaultPage = Public.getDefaultPage();
var qtyPlaces = Number(defaultPage.SYSTEM.qtyPlaces);
var pricePlaces = Number(defaultPage.SYSTEM.pricePlaces);
var amountPlaces = Number(defaultPage.SYSTEM.amountPlaces);
var isNewImportExcel = !1;
var contactCombo;
var ourselfCombo;
var goodsCombo;
var originalData;
var curRow;
var curCol;
var btn_draft = '<a id="draft" class="ui-btn bg-night"><i class="iconfont icon-baocun"></i><span>保存为草稿</span></a>';
var btn_final = '<a id="final" class="ui-btn bg-green"><i class="iconfont icon-baocun"></i><span>保存为正式</span></a>';
var btn_save = '<a id="save" class="ui-btn bg-green"><i class="iconfont icon-baocun"></i><span>保存</span></a>';
var btn_edit = '<a id="edit" class="ui-btn bg-green"><i class="iconfont icon-baocun"></i><span>保存</span></a>';
var breakShow = !1;
var THISPAGE = {
    init: function(a) {
        THISPAGE.initDom(a);
        THISPAGE.initGrid(a);
        THISPAGE.initEvent();
    },
    initDom: function(a) {
        $('#bidSiteName').val(a.bidSiteName);
        $('#bidTitle').val(a.bidTitle);
        $('#bidUrl').val(a.bidUrl);
        $('#contactInfo').val(a.contactInfo);
        $('#amount').val(a.amount || '0.00');
        $('#description').val(a.description || '');
        contactCombo = Business.supplierCombo($('#contact'), {
            width: 200,
            defaultSelected: a.contactId > 0 ? ['id', a.contactId] : -1,
            disabled: urlParam.view ? !0 : !1
        });
        ourselfCombo = $('#ourself').combo({
            async: false, // 异步 否
            data: '/Base/Company/queryAll',
            ajaxOptions: {
                formatData: function(a) {
                    var company = [];
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
            width: 200,
            defaultSelected: a.sId > 0 ? ['id', a.sId] : -1,
            disabled: urlParam.view ? !0 : !1,
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
                    listHeight: 490,
                    disabled: urlParam.view ? !0 : !1
                });
                if (a.quotedName) {
                    $('.fs-input').val(a.quotedName);
                    if (a.redoSoId > 0) {
                        $('.fs-dropdown').addClass('dn').html('');
                        $('.fs-input').attr('disabled', true).addClass('ui-input-dis');
                    } else {
                        var names = a.quotedName.toString().split(',');
                        $(".fs-option").each(function() {
                            if (names.indexOf($(this).children('.fs-option-label').html()) != -1) {
                                $(this).addClass("selected");
                            }
                        });
                    }
                }
            }
        });
        if (a.id > 0) {
            a.checkId > 0 ? $('#mark').addClass('bill-has-checked') : $('#mark').removeClass('bill-has-checked');
            $('#toolTop').html(btn_edit + (a.isDraft == 1 ? btn_final : ''));
            a.isDraft == 1 && $('#edit span').after('<b>当前为草稿</b>');
            $('#billNo').val(a.billNo);
            $('#billDate').val(a.billDate);
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
            $('#offerEndTime').val(a.offerEndTime);
            $('#desBtn').show();
            $('#uName').html(a.uName);
            $('#createTime').html(a.createTime);
            $('#checkName').html(a.checkName);
            $('#modifyTime').html(a.modifyTime);
            $('#quoteOper').removeClass('dn');
            a.files && THISPAGE.addFiles(a.files);
        } else {
            $('#toolTop').html(btn_save + btn_draft);
            $('#billDate').val(defaultPage.SYSTEM.endDate);
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
            $('#offerEndTime').val(defaultPage.SYSTEM.endDate + ' 18:00:00');
            $('#uName').html(defaultPage.SYSTEM.uName || '');
        }
        if (urlParam.view) {
            $('#toolTop').html('<span class="db fl mr10 pl10 pr10 h30 lh30 white bg-gray b-r-3 def">查看模式</span>');
            $('#offerEndTime').attr('disabled', true).addClass('ui-input-dis');
            $('#contactInfo').attr('disabled', true).addClass('ui-input-dis');
            $('#download-excel').remove();
            $('#add-excel').remove();
            $('#description').attr('disabled', true).addClass('ui-input-dis');
            $('#desBtn').hide();
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
                width: 74,
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
                width: 100,
                title: !0,
                editable: !0,
                edittype: 'textarea',
                classes: 'ui-ellipsis'
            }, {
                name: 'name',
                label: '产品名称',
                width: 100,
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
                label: '最终报价单价',
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
                label: '最终报价总价',
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
                label: '最终参考链接',
                width: 100,
                title: !0,
                editable: !0,
                edittype: 'textarea',
                classes: 'ui-ellipsis'
            }, {
                name: 'description1',
                label: '最终备注1',
                width: 100,
                title: !0,
                editable: !0,
                edittype: 'textarea',
                classes: 'ui-ellipsis'
            }, {
                name: 'description2',
                label: '最终备注2',
                width: 100,
                title: !0,
                editable: !0,
                edittype: 'textarea',
                classes: 'ui-ellipsis'
            }, {
                name: 'id',
                width: 0,
                hidden: !0
            }, {
                name: 'iid',
                width: 0,
                hidden: !0
            }],
            cmTemplate: {sortable: !1, title: !1},
            localReader: {root: 'rows', records: 'records', repeatitems: !1, id: 'id'},
            jsonReader: {root: 'data.entries', records: 'records', repeatitems: !1, id: 'id'},
            width: Public.setGrid().w,
            height: '100%',
            altRows: !1,
            gridview: !0,
            rownumbers: a.id > 0 ? !1 : !0,
            rownumWidth: 40,
            onselectrow: !1,
            shrinkToFit: !0,
            forceFit: !1,
            rowNum: 10000,
            cellEdit: !1,
            cellsubmit: 'clientArray',
            loadonce: !0,
            footerrow: !0,
            userDataOnFooter: !0,
            subGrid: a.id > 0 ? !0 : !1,
            subGridWidth: 30,
            subGridOptions: {
                expandOnLoad: !1
            },
            subGridRowExpanded: function(subgrid_id, row_id) {
                var rowData = $('#grid').jqGrid('getRowData', row_id);
                var subgrid_table_id = subgrid_id + '_t';
                $('#' + subgrid_id).html('<div class="grid-wrap"><table id="' + subgrid_table_id + '"></table></div>');
                $('#' + subgrid_table_id).jqGrid({
                    url: '/Bill/InvBq/quoteInfoList',
                    postData: {iid: rowData.iid, zid: rowData.zid},
                    datatype: 'json',
                    colModel: [{
                        name: 'uName',
                        label: '报价人',
                        width: 56,
                        fixed: !0,
                        editable: !1
                    }, {
                        name: 'price',
                        label: '报价单价',
                        width: 100,
                        fixed: !0,
                        editable: !1,
                        formatter: 'currency',
                        formatoptions: {
                            showZero: !1,
                            decimalPlaces: pricePlaces
                        }
                    }, {
                        name: 'amount',
                        label: '报价总价',
                        width: 100,
                        fixed: !0,
                        editable: !1,
                        formatter: 'currency',
                        formatoptions: {
                            showZero: !1,
                            decimalPlaces: amountPlaces
                        }
                    }, {
                        name: 'myUrl',
                        label: '报价链接',
                        width: 100,
                        title: !0,
                        editable: !1,
                        classes: 'ui-ellipsis hover'
                    }, {
                        name: 'description1',
                        label: '备注1',
                        width: 100,
                        title: !0,
                        editable: !1,
                        classes: 'break'
                    }, {
                        name: 'description2',
                        label: '备注2',
                        width: 100,
                        title: !0,
                        editable: !1,
                        classes: 'break'
                    }, {
                        name: 'firstTime',
                        label: '首次报价时间',
                        width: 124,
                        fixed: !0,
                        editable: !1
                    }, {
                        name: 'lastTime',
                        label: '最后报价时间',
                        width: 124,
                        fixed: !0,
                        editable: !1
                    }, {
                        name: 'status',
                        label: '被选状态',
                        width: 100,
                        fixed: !0,
                        editable: !1,
                        formatter: function(a, b, c) {
                            var d = a == 0 ? '点击选择' : '已被选择';
                            var e = a == 0 ? 'hover-gray' : 'hover-green';
                            var f = a == 0 ? '点击选择本条报价作为最终报价' : '点击取消选择，以便重新选择，或自己手动输入各项最终信息';
                            return '<span class="status-btn db fl pl5 pr5 h28 lh28 b-r-3 ' + e + '" title="' + f + '" data-parentid="' + row_id + '" data-subgrid="' + subgrid_table_id + '" data-id="' + c.id + '" data-status="' + a + '">' + d + '</span>';
                        }
                    }, {
                        name: 'id',
                        label: '',
                        hidden: !0
                    }, {
                        name: 'iid',
                        label: '',
                        hidden: !0
                    }, {
                        name: 'zid',
                        label: '',
                        hidden: !0
                    }, {
                        name: 'goodsId',
                        label: '',
                        hidden: !0
                    }],
                    cmTemplate: {sortable: !1, title: !1},
                    jsonReader: {root: 'data.rows', records: 'data.records', total: 'data.total', repeatitems: !1, id: 'id'},
                    width: a.w - 35,
                    height: '100%',
                    shrinkToFit: !0,
                    forceFit: !1,
                    rownumbers: !1,
                    viewsortcols: [true, 'vertical', true],
                    rowNum: 100,
                    sortname: 'id',
                    sortorder: 'ASC',
                    gridComplete: function() {
                        setTimeout(function() {
                            Public.autoGridWidth($('#' + subgrid_table_id));
                        }, 10);
                    },
                    onCellSelect: function(a, b) {
                        var rowData = $('#' + subgrid_table_id).jqGrid('getRowData', a);
                        switch (b) {
                            case 3:
                                rowData.myUrl && window.open(Public.fixUrl(rowData.myUrl));
                                break;
                            default:
                                break;
                        }
                    }
                });
            },
            loadComplete: function(a) {
                if (urlParam.id > 0) {
                    var b = a.rows;
                    var c = b.length;
                    THISPAGE.newId = c + 1;
                    //展开各行，每次展开一行间隔一定时间
                    for (let i = 0; i < b.length; i++) {
                        setTimeout(function() {
                            $('#grid tr#' + b[i].id + ' .ui-sgcollapsed').trigger('click');
                        }, 200 * i);
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
        if (!urlParam.view) {
            Business.billsEvent(this, 'bidQuote');
            laydate.render({elem: '#billDate'});
            laydate.render({elem: '#offerEndTime', type: 'datetime'});
            $('.wrapper').on('click', '#save', function(a) {
                a.preventDefault();
                if (Business.verifyRight('BIDQUOTE_ADD')) {
                    var data = THISPAGE.getPostData();
                    if (data) {
                        $('.wrapper').append('<div id="div-lock"><div class="loading">保存中...</div></div>');
                        data.isDraft = 0;
                        Public.ajaxPost('/Bill/InvBq/add', {
                            postData: JSON.stringify(data)
                        }, function(res) {
                            $('.wrapper > #div-lock').remove();
                            if (res && 200 == res.status) {
                                parent.parent.$.dialog.tips('保存成功！');
                                parent.frames['bid-bidInfo'] && parent.frames['bid-bidInfo'].$('#search').trigger('click');
                                if (parent.frames['bid-bidQuote']) {
                                    parent.frames['bid-bidQuote'].$('#search').trigger('click');
                                } else {
                                    parent.tab.addTabItem({
                                        tabid: 'bid-bidQuote',
                                        text: '<i class="iconfont icon-danju f14 mr5"></i>预算报价',
                                        url: '/Uri/bid_quote_list'
                                    });
                                    // 选择回原来的详情页，后面removeSelectedTabItem时才不会关闭刚打开的列表页
                                    parent.tab.selectTabItem(urlParam.tabId || ('bid-bidQuote' + originalData.id));
                                }
                                parent.tab.removeSelectedTabItem();
                            } else {
                                $.dialog.tips('保存失败！' + res.msg, 2);
                            }
                        });
                    }
                }
            });
            $('.wrapper').on('click', '#draft', function(a) {
                a.preventDefault();
                if (Business.verifyRight('BIDQUOTE_ADD')) {
                    var data = THISPAGE.getPostData();
                    if (data) {
                        $('.wrapper').append('<div id="div-lock"><div class="loading">保存中...</div></div>');
                        data.isDraft = 1;
                        Public.ajaxPost('/Bill/InvBq/add', {
                            postData: JSON.stringify(data)
                        }, function(res) {
                            $('.wrapper > #div-lock').remove();
                            if (res && 200 == res.status) {
                                parent.parent.$.dialog.tips('保存成功！');
                                parent.frames['bid-bidInfo'] && parent.frames['bid-bidInfo'].$('#search').trigger('click');
                                if (parent.frames['bid-bidQuote']) {
                                    parent.frames['bid-bidQuote'].$('#search').trigger('click');
                                } else {
                                    parent.tab.addTabItem({
                                        tabid: 'bid-bidQuote',
                                        text: '<i class="iconfont icon-danju f14 mr5"></i>预算报价',
                                        url: '/Uri/bid_quote_list'
                                    });
                                    // 选择回原来的详情页，后面removeSelectedTabItem时才不会关闭刚打开的列表页
                                    parent.tab.selectTabItem(urlParam.tabId || ('bid-bidQuote' + originalData.id));
                                }
                                parent.tab.removeSelectedTabItem();
                            } else {
                                $.dialog.tips('保存失败！' + res.msg, 2);
                            }
                        });
                    }
                }
            });
            $('.wrapper').on('click', '#edit', function(a) {
                a.preventDefault();
                if (Business.verifyRight('BIDQUOTE_UPDATE')) {
                    if ($.inArray('SYS_ADMIN', defaultPage.SYSTEM.rights) !== -1 || ((originalData.uId == defaultPage.SYSTEM.uId || originalData.uId == 1) && Public.addDays(originalData.billDate, 30) >= defaultPage.SYSTEM.endDate)) {
                        var data = THISPAGE.getPostData();
                        if (data) {
                            $('.wrapper').append('<div id="div-lock"><div class="loading">保存中...</div></div>');
                            data.isDraft = originalData.isDraft;
                            Public.ajaxPost('/Bill/InvBq/update', {
                                postData: JSON.stringify(data)
                            }, function(res) {
                                $('.wrapper > #div-lock').remove();
                                if (res && 200 == res.status) {
                                    parent.parent.$.dialog.tips('保存成功！');
                                    parent.frames['bid-bidInfo'] && parent.frames['bid-bidInfo'].$('#search').trigger('click');
                                    if (parent.frames['bid-bidQuote']) {
                                        parent.frames['bid-bidQuote'].$('#search').trigger('click');
                                    } else {
                                        parent.tab.addTabItem({
                                            tabid: 'bid-bidQuote',
                                            text: '<i class="iconfont icon-danju f14 mr5"></i>预算报价',
                                            url: '/Uri/bid_quote_list'
                                        });
                                        // 选择回原来的详情页，后面removeSelectedTabItem时才不会关闭刚打开的列表页
                                        parent.tab.selectTabItem('bid-bidQuote' + originalData.id);
                                    }
                                    parent.tab.removeSelectedTabItem();
                                } else {
                                    $.dialog.tips('保存失败！' + res.msg, 2);
                                }
                            });
                        }
                    } else {
                        $.dialog.tips('不可编辑已超过30天或别人录入的预算报价单！仅本人或管理员可编辑！' + res.msg, 999);
                    }
                }
            });
            $('.wrapper').on('click', '#final', function(a) {
                a.preventDefault();
                if (Business.verifyRight('BIDQUOTE_UPDATE')) {
                    if ($.inArray('SYS_ADMIN', defaultPage.SYSTEM.rights) !== -1 || ((originalData.uId == defaultPage.SYSTEM.uId || originalData.uId == 1) && Public.addDays(originalData.billDate, 30) >= defaultPage.SYSTEM.endDate)) {
                        var data = THISPAGE.getPostData();
                        if (data) {
                            $('.wrapper').append('<div id="div-lock"><div class="loading">保存中...</div></div>');
                            data.isDraft = 0;
                            Public.ajaxPost('/Bill/InvBq/update', {
                                postData: JSON.stringify(data)
                            }, function(res) {
                                $('.wrapper > #div-lock').remove();
                                if (res && 200 == res.status) {
                                    parent.parent.$.dialog.tips('保存成功！');
                                    parent.frames['bid-bidInfo'] && parent.frames['bid-bidInfo'].$('#search').trigger('click');
                                    if (parent.frames['bid-bidQuote']) {
                                        parent.frames['bid-bidQuote'].$('#search').trigger('click');
                                    } else {
                                        parent.tab.addTabItem({
                                            tabid: 'bid-bidQuote',
                                            text: '<i class="iconfont icon-danju f14 mr5"></i>预算报价',
                                            url: '/Uri/bid_quote_list'
                                        });
                                        // 选择回原来的详情页，后面removeSelectedTabItem时才不会关闭刚打开的列表页
                                        parent.tab.selectTabItem('bid-bidQuote' + originalData.id);
                                    }
                                    parent.tab.removeSelectedTabItem();
                                } else {
                                    $.dialog.tips('保存失败！' + res.msg, 2);
                                }
                            });
                        }
                    } else {
                        $.dialog.tips('不可编辑已超过30天或别人录入的预算报价单！仅本人或管理员可编辑！' + res.msg, 999);
                    }
                }
            });
            $('#add-excel').uploader({
                action: '/Bill/InvBq/uploadExcel',
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
                    var gridData = data.data.entries;
                    if (originalData.id > 0) {
                        $.each(gridData, function(k, v) {
                            gridData[k].iid = originalData.id;
                        });
                    }
                    $('#grid').jqGrid('clearGridData').jqGrid('setGridParam', {
                        data: gridData
                    }).trigger('reloadGrid');
                    THISPAGE.newId = gridData.length + 1;
                    // 如果单据已存在
                    if (originalData.id > 0) {
                        // 先重置报价信息的被选状态，再展开各行，每次展开一行间隔一定时间
                        Public.ajaxPost('/Bill/InvBq/resetQuoteInfoStatus', {
                            id: originalData.id
                        }, function(res) {
                            if (res && 200 == res.status) {
                                $.dialog.tips('因重新导入了清单表格！请重新选择报价信息作为最终报价！', 1, 99);
                                setTimeout(function() {
                                    for (let i = 0; i < gridData.length; i++) {
                                        setTimeout(function() {
                                            $('#grid tr#' + (i + 1) + ' .ui-sgcollapsed').trigger('click');
                                        }, 200 * i);
                                    }
                                }, 500);
                            } else {
                                $.dialog.tips('操作失败！' + res.msg, 2);
                            }
                        });
                    }
                }
            });
            $('#grid').on('click', '.status-btn', function(a) {
                a.preventDefault();
                var parentid = $(this).data('parentid');
                var parentRowData = $('#grid').jqGrid('getRowData', parentid);
                var subgrid = $(this).data('subgrid');
                var id = $(this).data('id');
                var status = $(this).data('status');
                var newStatus = parseInt(status) == 0 ? 1 : 0;
                var rowData = $('#' + subgrid).jqGrid('getRowData', id);
                Public.ajaxPost('/Bill/InvBq/updateQuoteInfoStatus', {
                    id: id,
                    status: newStatus
                }, function(res) {
                    if (res && 200 == res.status) {
                        $.dialog.tips('操作成功！');
                        $('#' + subgrid).jqGrid().trigger('reloadGrid');
                        if (newStatus == 1) {
                            $('#grid').jqGrid('setRowData', parentid, {'price': rowData.price || parentRowData.price, 'amount': rowData.amount || parentRowData.amount, 'buyUrl': rowData.myUrl || parentRowData.buyUrl, 'description1': rowData.description1 || parentRowData.description1, 'description2': rowData.description2 || parentRowData.description2});
                            THISPAGE.calTotal();
                        }
                    } else {
                        $.dialog.tips('操作失败！' + res.msg, 2);
                    }
                });
            });
            $('#desBtn').on('click', function(a) {
                a.preventDefault();
                if (Business.verifyRight('BIDQUOTE_UPDATE')) {
                    if ($.inArray('SYS_ADMIN', defaultPage.SYSTEM.rights) !== -1 || originalData.uId == defaultPage.SYSTEM.uId) {
                        originalData.id && Public.ajaxPost('/Bill/InvBq/updatedes', {
                            id: originalData.id,
                            newDes: $.trim($('#description').val())
                        }, function(res) {
                            if (res && 200 == res.status) {
                                $.dialog.tips('备注保存成功！');
                                parent.frames['bid-bidQuote'] && parent.frames['bid-bidQuote'].$('#search').trigger('click');
                            } else {
                                $.dialog.tips('备注保存失败！' + res.msg, 2);
                            }
                        });
                    } else {
                        $.dialog.alert('不可编辑别人录入的预算报价单！仅本人或管理员可编辑！');
                    }
                }
            });
            $('#upFile').uploader({
                action: '/Bill/InvBq/uploadfile',
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
                    !Business.verifyRight('BIDQUOTE_UPDATE') && a.preventDefault();
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
                if (Business.verifyRight('BIDQUOTE_UPDATE')) {
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
                if (Business.verifyRight('BIDQUOTE_UPDATE')) {
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
        }
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
        $('#exportInfoGrid').on('click', function(a) {
            var billNo = $('#billNo').val();
            var href = '/Bill/InvBq/exportInfoGrid?id=' + originalData.id + '&billNo=' + billNo;
            $(this).attr('href', href);
        });
        $(window).on('resize', function() {
            Public.autoGridWidth($('#grid'));
            var subGridWrap = $('.ui-subgrid .grid-wrap');
            if (subGridWrap && subGridWrap.length > 0) {
                $.each(subGridWrap, function(k, v) {
                    Public.autoGridWidth($('#' + v.parentNode.id + '_t'));
                });
            }
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
                '<a href="/data/upfile/bidQuote/' + v.url + '" target="_blank" title="' + v.name + '"><span>' + v.name + '</span><span>' + v.upName + '</span><span>' + v.upTime + '</span></a>' +
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
        $('#amount').val(totalAmount);
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
    getPostData: function() {
        curRow && curCol && (
            $('#grid').jqGrid('saveCell', curRow, curCol),
            curRow = curCol = null
        );
        var contactInput = $('#contact').find('input');
        var ourselfInput = $('#ourself').find('input');
        var contactData = $('#contact').data('contactData');
        var ourselfData = $('#ourself').data('ourselfData');
        if ('' === contactInput.val()) {
            $('#contact').removeData('contactData');
            contactCombo.active = !0;
            contactCombo.doQuery();
            contactCombo.input.focus();
            $.dialog.tips('请选择招标单位！', 2);
            return !1;
        }
        if (!contactData || contactData.id < 0) {
            setTimeout(function() {
                contactInput.focus().select();
            }, 10);
            $.dialog.tips('当前招标单位不存在！', 2);
            return !1;
        }
        if ('' === ourselfInput.val()) {
            $('#ourself').removeData('ourselfData');
            ourselfCombo.active = !0;
            ourselfCombo.doQuery();
            ourselfCombo.input.focus();
            $.dialog.tips('请选择投标单位！', 2);
            return !1;
        }
        if (!ourselfData || ourselfData.id < 0) {
            setTimeout(function() {
                ourselfInput.focus().select();
            }, 10);
            $.dialog.tips('当前投标单位不存在！', 2);
            return !1;
        }
        var billDate = $.trim($('#billDate').val());
        var offerEndTime = $.trim($('#offerEndTime').val());
        if (offerEndTime < billDate) {
            $.dialog.tips('报价截止时间不合理！请录入合理的报价截止时间！', 2);
            return !1;
        }
        var contactInfo = $.trim($('#contactInfo').val());
        if (contactInfo == '') {
            $.dialog.tips('联系方式不能为空！', 2);
            return !1;
        }
        var quotedName = $.trim($('.fs-input').val());
        if (quotedName == '') {
            $.dialog.tips('有效报价人不能为空！', 2);
            return !1;
        }
        var entriesData = THISPAGE.getEntriesData();
        if (!entriesData) {
            return !1;
        }
        if (entriesData.length > 0) {
            var data = {
                isNewImportExcel: isNewImportExcel,
                id: originalData.id,
                billNo: $.trim($('#billNo').val()),
                billDate: $.trim($('#billDate').val()),
                offerEndTime: $.trim($('#offerEndTime').val()),
                contactId: contactData.id,
                contactName: contactData.name,
                sId: ourselfData.id,
                sName: ourselfData.name,
                bidSiteId: originalData.bidSiteId,
                bidSiteName: originalData.bidSiteName,
                bidInfoId: originalData.bidInfoId,
                bidTitle: originalData.bidTitle,
                bidUrl: originalData.bidUrl,
                contactInfo: contactInfo,
                quotedName: quotedName,
                totalQty: $('#grid').jqGrid('footerData', 'get').qty.replace(/,/g, ''),
                amount: $('#grid').jqGrid('footerData', 'get').amount.replace(/,/g, ''),
                entries: entriesData,
                description: $.trim($('#description').val()).replace(/"|'|{|}/g, '”'),
                files: originalData.files ? JSON.stringify(originalData.files) : ''
            };
            return data;
        } else {
            return $.dialog.tips('产品信息不能为空！', 2), $('#grid').jqGrid('editCell', 1, 2, !0), !1;
        }
    }
};
$(function() {
    if (urlParam.turnByBidInfo) {
        var openerData = window.parent.document.getElementById(urlParam.tabId).openerData;
        originalData = {
            bidSiteId: parseInt(openerData.bidSiteId),
            bidSiteName: openerData.bidSiteName,
            bidInfoId: parseInt(openerData.bidInfoId),
            bidTitle: openerData.bidTitle,
            bidUrl: openerData.bidUrl,
            id: -1,
            entries: []
        };
        THISPAGE.init(originalData);
    } else if (urlParam.id) {
        $('.wrapper').append('<div id="div-lock"><div class="loading">单据准备中...</div></div>');
        Public.ajaxGet('/Bill/InvBq/query', {
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
            entries: []
        };
        THISPAGE.init(originalData);
    }
});
