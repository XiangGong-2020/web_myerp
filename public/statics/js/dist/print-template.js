var defaultPage = Public.getDefaultPage();
var qtyPlaces = Number(defaultPage.SYSTEM.qtyPlaces);
var classes;
var contactCombo;
var goodsCombo;
var curRow;
var curCol;
var originalData = {
    editType: 0,
    id: -1,
    contactId: 1,
    contactName: '默认客户',
    entries: [{id: 1}]
};
var THISPAGE = {
    init: function(a) {
        THISPAGE.initTab();
        THISPAGE.initDom(a);
        THISPAGE.initEvent();
    },
    initTab: function() {
        var $wrapper = $('.tabs');
        var $tabMenu = $wrapper.find('.tab-menu div');
        var $allTabs = $wrapper.find('.tab-content > div');
        $tabMenu.each(function(i) {
            $(this).attr('data-tab', 'tab' + i);
        });
        $allTabs.each(function(i) {
            $(this).attr('data-tab', 'tab' + i);
            $tabMenu.filter('[data-tab=tab' + i + ']').hasClass('active') && $(this).show();
        });
        $tabMenu.on('click', function() {
            var dataTab = $(this).data('tab');
            var $getWrapper = $(this).closest($wrapper);
            $getWrapper.find($tabMenu).removeClass('active');
            $(this).addClass('active');
            $getWrapper.find($allTabs).hide();
            $getWrapper.find($allTabs).filter('[data-tab=' + dataTab + ']').show();
            if (dataTab == 'tab1') {
                $('#import-2').uploader({
                    action: '/Base/PrintTemplate/uploadExcel_2',
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
                    onStart: function(e) {
                        $('#manage-wrap').prepend('<div id="div-lock"><div class="loading">上传中...</div></div>');
                    },
                    onError: function(e) {
                        $.dialog.tips('上传错误！', 2);
                    },
                    onSuccess: function(a) {
                        var b = JSON.parse(a.data);
                        var c = b.data.entries;
                        var d = c.length;
                        if (d > 0) {
                            var tab = '';
                            for (var i = 0; i < d; i++) {
                                tab += (
                                    '<div class="print-tab tab2">' +
                                    '<div class="tab-tit-s">发货标签</div>' +
                                    '<div class="name">名称:</div>' +
                                    '<div class="value"><input type="text" id="tab2_name" value="' + c[i]['name'] + '" /></div>' +
                                    '<div class="name">型号:</div>' +
                                    '<div class="value"><input type="text" id="tab2_spec" value="' + c[i]['spec'] + '" /></div>' +
                                    '<div class="name">属性:</div>' +
                                    '<div class="value"><input type="text" id="tab2_sku" value="' + c[i]['sku'] + '" /></div>' +
                                    '<div class="name">数量:</div>' +
                                    '<div class="value"><input type="text" id="tab2_qty" value="' + c[i]['qty'] + '" /></div>' +
                                    '<div class="tab-bt-s">五六电子 023-58556299</div>' +
                                    '</div>'
                                );
                            }
                            $('#tab2group').html(tab);
                        }
                    },
                    onAllComplete: function() {
                        $('#manage-wrap > #div-lock').remove();
                    }
                });
                $('.upload-el').on({
                    click: function(a) {
                        !Business.verifyRight('PRINTTEMP_IMPORT') && a.preventDefault();
                    },
                    mouseover: function() {
                        $(this).prev().addClass('ui-btn-hover');
                    },
                    mouseleave: function() {
                        $(this).prev().removeClass('ui-btn-hover');
                    }
                });
            }
        });
    },
    initDom: function(a) {
        contactCombo = Business.customerCombo($('#contact'), {
            width: 300,
            defaultSelected: 0,
            emptyOptions: !0,
        });
        classes = $('#classes').cssRadio({
            callback: function(b) {
                a.editType = b.find('input').val();
                switch (a.editType) {
                    case '1':
                        $('#excelBtns-1').hide().addClass('dn');
                        $('#grid').jqGrid('GridUnload');
                        THISPAGE.initGrid_1(a);
                        $('#grid').jqGrid('setGridParam', {
                            cellEdit: !0
                        });
                        break;
                    case '2':
                        $('#excelBtns-1').show().removeClass('dn');
                        $('#import-1').uploader({
                            action: '/Base/PrintTemplate/uploadExcel_1',
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
                            onStart: function(e) {
                                $('#manage-wrap').prepend('<div id="div-lock"><div class="loading">上传中...</div></div>');
                            },
                            onError: function(e) {
                                $.dialog.tips('上传错误！', 2);
                            },
                            onSuccess: function(a) {
                                var data = JSON.parse(a.data);
                                $('#grid').jqGrid('clearGridData').jqGrid('setGridParam', {
                                    data: data.data.entries
                                }).trigger('reloadGrid');
                                THISPAGE.newId = data.data.entries.length + 1;
                            },
                            onAllComplete: function() {
                                $('#manage-wrap > #div-lock').remove();
                            }
                        });
                        $('.upload-el').on({
                            click: function(a) {
                                !Business.verifyRight('PRINTTEMP_IMPORT') && a.preventDefault();
                            },
                            mouseover: function() {
                                $(this).prev().addClass('ui-btn-hover');
                            },
                            mouseleave: function() {
                                $(this).prev().removeClass('ui-btn-hover');
                            }
                        });
                        $('#grid').jqGrid('GridUnload');
                        THISPAGE.initGrid_1(a);
                        $('#grid').jqGrid('setGridParam', {
                            cellEdit: !1
                        });
                        break;
                    case '3':
                        $('#excelBtns-1').hide().addClass('dn');
                        $('#grid').jqGrid('GridUnload');
                        THISPAGE.initGrid_2(a);
                        THISPAGE.initCombo();
                        $('#grid').jqGrid('setGridParam', {
                            cellEdit: !0
                        });
                        break;
                    default:
                        break;
                }
            }
        });
        1 == a.editType ? classes.setValue(0) : (
            2 == a.editType ? classes.setValue(1) : (
                3 == a.editType ? classes.setValue(2) : classes.setValue()
            )
        );
    },
    initGrid_1: function(a) {
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
                width: 334,
                fixed: !0,
                title: !0,
                editable: !0,
                classes: 'goods ui-ellipsis'
            }, {
                name: 'brandName',
                label: '品牌',
                width: 86,
                fixed: !0,
                editable: !0
            }, {
                name: 'unitName',
                label: '单位',
                width: 38,
                fixed: !0,
                editable: !0
            }, {
                name: 'qty',
                label: '数量',
                width: 60,
                fixed: !0,
                editable: !0,
                formatter: 'number',
                formatoptions: {
                    decimalPlaces: qtyPlaces
                }
            }, {
                name: 'description',
                label: '备注',
                width: 120,
                fixed: !0,
                title: !0,
                editable: !0,
                classes: 'ui-ellipsis gray'
            }],
            cmTemplate: {sortable: !1, title: !1},
            localReader: {root: 'rows', records: 'records', repeatitems: !1, id: 'id'},
            jsonReader: {root: 'data.entries', records: 'records', repeatitems: !1, id: 'id'},
            autowidth: !0,
            height: '100%',
            rownumbers: !0,
            gridview: !0,
            onselectrow: !1,
            shrinkToFit: !1,
            forceFit: !1,
            rowNum: 10000,
            cellEdit: !0,
            cellsubmit: 'clientArray',
            loadonce: !0,
            loadError: function(a, b) {
                $.dialog.tips('Type: ' + b + '; Response: ' + a.status + ' ' + a.statusText, 2);
            },
            gridComplete: function() {
                setTimeout(function() {
                    Public.autoGridWidth($('#grid'));
                }, 10);
            },
            afterEditCell: function(rowId, cellName, cellValue, iRow, iCol) {
                THISPAGE.curID = rowId;
            }
        });
        $('#grid').jqGrid('setGridParam', {
            cellEdit: !0
        });
    },
    initGrid_2: function(a) {
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
                width: 334,
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
                    if (a) { // 点选编辑模式
                        var goodsInfo = $('#' + b.rowId).data('goodsInfo');
                        var rowData = $('#grid').jqGrid('getRowData', b.rowId);
                        if (goodsInfo && rowData) {
                            var data = {
                                brandName: goodsInfo.brandName,
                                unitName: goodsInfo.unitName,
                                qty: rowData.qty || goodsInfo.qty || 1
                            };
                            $('#grid').jqGrid('setRowData', b.rowId, data);
                        }
                        return a;
                    } else { // 表格加载模式
                        return c.goodsId ? (c.invNo + ' ' + c.invName + (c.invSpec ? ' ' + c.invSpec : '') + (c.invSku ? ' ' + c.invSku : '')) : '&nbsp;';
                    }
                }
            }, {
                name: 'brandName',
                label: '品牌',
                width: 86,
                fixed: !0,
                editable: !1
            }, {
                name: 'unitName',
                label: '单位',
                width: 38,
                fixed: !0,
                editable: !1
            }, {
                name: 'qty',
                label: '数量',
                width: 60,
                fixed: !0,
                editable: !0,
                formatter: 'number',
                formatoptions: {
                    decimalPlaces: qtyPlaces
                }
            }, {
                name: 'description',
                label: '备注',
                width: 120,
                fixed: !0,
                title: !0,
                editable: !0,
                classes: 'ui-ellipsis gray'
            }],
            cmTemplate: {sortable: !1, title: !1},
            localReader: {root: 'rows', records: 'records', repeatitems: !1, id: 'id'},
            jsonReader: {root: 'data.entries', records: 'records', repeatitems: !1, id: 'id'},
            autowidth: !0,
            height: '100%',
            rownumbers: !0,
            gridview: !0,
            onselectrow: !1,
            shrinkToFit: !1,
            forceFit: !1,
            rowNum: 10000,
            cellEdit: !1,
            cellsubmit: 'clientArray',
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
            afterEditCell: function(rowId, cellName, cellValue, iRow, iCol) {
                THISPAGE.curID = rowId;
                if ('goods' === cellName) {
                    $('#' + iRow + '_goods', '#grid').val(cellValue);
                }
            }
        });
        $('#grid').jqGrid('setGridParam', {
            cellEdit: !0
        });
    },
    initCombo: function() {
        goodsCombo = Business.goodsCombo($('.goodsAuto'), {});
    },
    initEvent: function() {
        contactCombo.input.enterKey();
        Business.billsEvent(this, 'printTemplate');
        $('#refresh').on('click', function(a) {
            a.preventDefault();
            window.location.reload();
        });
    },
    getPostData: function(a) {
        curRow && curCol && ($('#grid').jqGrid('saveCell', curRow, curCol), curRow = curCol = null);
        var contactInput = $('#contact').find('input');
        if ('' === contactInput.val()) {
            $('#contact').removeData('contactData');
            $.dialog.tips('请选择客户！', 2);
            contactCombo.active = !0;
            contactCombo.doQuery();
            contactCombo.input.focus();
            return !1;
        }
        var contactData = $('#contact').data('contactData');
        if (!contactData || !contactData.id) {
            setTimeout(function() {
                contactInput.focus().select();
            }, 15);
            $.dialog.tips('当前客户不存在！', 2);
            return !1;
        }
        var entriesData = THISPAGE.getEntriesData(a);
        if (!entriesData) {
            return !1;
        }
        if (entriesData.length > 0) {
            var data = {
                type: 1,
                id: 1,
                billNo: $.trim($('#billNo').text()),
                contactId: contactData.id,
                contactName: contactData.name,
                entries: entriesData
            };
            return data;
        }
        return $.dialog.tips('产品信息不能为空！', 2), $('#grid').jqGrid('editCell', 1, 2, !0), !1;
    },
    getEntriesData: function(a) {
        for (var entriesData = [], ids = $('#grid').jqGrid('getDataIDs'), i = 0, len = ids.length; len > i; i++) {
            var id = ids[i];
            var rowData = $('#grid').jqGrid('getRowData', id);
            if ('' !== rowData.goods) {
                var data = {
                    goods: rowData.goods,
                    brand: rowData.brandName || '',
                    unit: rowData.unitName || '',
                    qty: rowData.qty || 0,
                    des: rowData.description || ''
                };
                entriesData.push(data);
            }
        }
        return entriesData;
    },
    preview: function(oper) {
        if (Business.verifyRight('PRINTTEMP_PRINT')) {
            /* 获取原始html存为变量备用 */
            var oldhtml = window.document.body.innerHTML;
            /* 将元素赋值 输入的值才能被获取到 */
            switch (oper) {
                case 1:
                    $('.tab1').find('.contact').each(function() {
                        var value = $(this).val();
                        $(this).attr('value', value);
                    });
                    var temp_data_1 = THISPAGE.getPostData();
                    temp_data_1 && Public.ajaxPost('/Base/PrintTemplate/add_log1', temp_data_1, function(res) {
                        if (res && 200 == res.status) {
                            $.dialog.tips('记录打印log成功！');
                        } else {
                            $.dialog.tips('记录打印log失败！');
                        }
                    });
                    /* 隐藏操作按钮列 */
                    $('#grid').setGridParam().hideCol('operate').trigger('reloadGrid');
                    break;
                case 2:
                    $('.tab2').find('input').each(function() {
                        var value = $(this).val();
                        $(this).attr('value', value);
                    });
                    var entries = [];
                    $('#tab2group').find('.tab2').each(function() {
                        entries.push({
                            goods: ($('#tab2_name').val() ? $('#tab2_name').val() : '') +
                                ($('#tab2_spec').val() ? ' ' + $('#tab2_spec').val() : '') +
                                ($('#tab2_sku').val() ? ' ' + $('#tab2_sku').val() : ''),
                            unit: '',
                            qty: $('#tab2_qty').val() || 0,
                            des: ''
                        });
                    });
                    var temp_data_2 = {
                        type: 2,
                        id: 1,
                        entries: entries
                    };
                    temp_data_2 && Public.ajaxPost('/Base/PrintTemplate/add_log2', temp_data_2, function(res) {
                        if (res && 200 == res.status) {
                            $.dialog.tips('记录打印log成功！');
                        } else {
                            $.dialog.tips('记录打印log失败！');
                        }
                    });
                    break;
            }
            /* 开始处理打印数据 */
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
        }
    }
};
$(function() {
    THISPAGE.init(originalData);
});
