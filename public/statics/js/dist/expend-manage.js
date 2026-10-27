var urlParam = Public.urlParam();
var defaultPage = Public.getDefaultPage();
var qtyPlaces = Number(defaultPage.SYSTEM.qtyPlaces);
var pricePlaces = Number(defaultPage.SYSTEM.pricePlaces);
var amountPlaces = Number(defaultPage.SYSTEM.amountPlaces);
var contactCombo = Business.supplierCombo($('#contact'), {
    width: 200
});
var expendtypeCombo = Business.categoryCombo($('.expendtypeAuto'), {
    trigger: !1,
    listWidth: 350,
    listHeight: 300,
    callback: {
        onChange: function(a) {
            var b = this.input.parents('tr');
            a && b.data('expendtypeInfo', a);
        }
    }
}, 'expendtype');
var companyAccountCombo = Business.companyAccountCombo($('.companyAccountAuto'), {
    trigger: !1,
    callback: {
        onChange: function(a) {
            var b = this.input.parents('tr');
            a && b.data('companyAccountInfo', a);
        }
    }
});
var settlementtypeCombo = Business.categoryCombo($('.settlementtypeAuto'), {
    trigger: !1,
    callback: {
        onChange: function(a) {
            var b = this.input.parents('tr');
            a && b.data('settlementtypeInfo', a);
        }
    }
}, 'settlementtype');
var originalData;
var curRow;
var curCol;
var btn_save = '<a id="save" class="ui-btn bg-green"><i class="iconfont icon-baocun"></i><span>保存</span></a>';
var btn_edit = '<a id="edit" class="ui-btn bg-green"><i class="iconfont icon-baocun"></i><span>保存</span></a>';
var btn_check = '<a id="check" class="ui-btn bg-green"><i class="iconfont icon-shenhe"></i><span>审核</span></a>';
var btn_uncheck = '<a id="uncheck" class="ui-btn bg-red"><i class="iconfont icon-shenhe"></i><span>反审核</span></a>';
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
            $('#toolTop').html(btn_edit + (a.checkId > 0 ? btn_uncheck : btn_check) + btn_delete);
            $('#contact').data('contactData', {
                id: a.contactId,
                name: a.contactName
            });
            contactCombo.input.val(a.contactName).attr('title', a.contactName);
            $('#billNo').val(a.billNo);
            $('#billDate').val(a.billDate);
            $('#description').val(a.description);
            $('#desBtn').show();
            $('#amount').val(a.amount);
            $('#uName').text(a.uName);
            $('#checkName').text(a.checkName);
            $('#createTime').text(a.createTime);
            $('#modifyTime').text(a.modifyTime);
            a.files && THISPAGE.addFiles(a.files);
        } else {
            $('#toolTop').html(btn_save);
            $('#billDate').val(defaultPage.SYSTEM.endDate);
            $('#amount').val(originalData.amount || '0.00');
            $('#description').val(originalData.description || '');
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
                hidden: originalData.srcReqId > 0 ? !0 : !1,
                formatter: function(val, opt, row) {
                    var html_con = (
                        '<div class="operating" data-id="' + opt.rowId + '">' +
                        '<span class="oper-icon ui-icon-plus iconfont icon-zengjia" title="新增一行"></span>' +
                        ((row.uId == void 0 || row.uId == defaultPage.SYSTEM.uId) ? '<span class="ml2 oper-icon ui-icon-trash iconfont icon-shanchu" title="删除本行"></span>' : '') +
                        '</div>'
                    );
                    return html_con;
                }
            }, {
                name: 'id',
                label: 'ID',
                hidden: !0
            }, {
                name: 'amount',
                label: '付款金额',
                width: 150,
                fixed: !0,
                editable: originalData.srcReqId > 0 ? !1 : !0,
                formatter: 'number',
                formatoptions: {
                    decimalPlaces: amountPlaces
                }
            }, {
                name: 'typeId',
                label: '付款类别ID',
                hidden: !0
            }, {
                name: 'typeName',
                label: '付款类别',
                width: 150,
                fixed: !0,
                title: !0,
                editable: !0,
                classes: 'ui-ellipsis',
                edittype: 'custom',
                editoptions: {
                    custom_element: function(a, b) {
                        var c = $('.expendtypeAuto')[0];
                        return c;
                    },
                    custom_value: function(a, b, c) {
                        if ('get' === b) {
                            if ('' !== $('.expendtypeAuto').getCombo().getValue()) {
                                return $(a).val();
                            }
                            var d = $(a).parents('tr');
                            return d.removeData('expendtypeInfo'), '';
                        }
                        'set' === b && $('input', a).val(c);
                    },
                    handle: function() {
                        $('#initCombo').append($('.expendtypeAuto').val('').unbind('focus.once'));
                    },
                    trigger: 'ui-icon-triangle-1-s expendtype-trigger iconfont icon-xiajiantou'
                }
            }, {
                name: 'accId',
                label: '付款账户ID',
                hidden: !0
            }, {
                name: 'accName',
                label: '付款账户',
                width: 150,
                fixed: !0,
                title: !0,
                editable: !0,
                classes: 'ui-ellipsis',
                edittype: 'custom',
                editoptions: {
                    custom_element: function(a, b) {
                        var c = $('.companyAccountAuto')[0];
                        return c;
                    },
                    custom_value: function(a, b, c) {
                        if ('get' === b) {
                            if ('' !== $('.companyAccountAuto').getCombo().getValue()) {
                                return $(a).val();
                            }
                            var d = $(a).parents('tr');
                            return d.removeData('companyAccountInfo'), '';
                        }
                        'set' === b && $('input', a).val(c);
                    },
                    handle: function() {
                        $('#initCombo').append($('.companyAccountAuto').val('').unbind('focus.once'));
                    },
                    trigger: 'ui-icon-triangle-1-s companyAccount-trigger iconfont icon-xiajiantou'
                }
            }, {
                name: 'wayId',
                label: '付款方式ID',
                hidden: !0
            }, {
                name: 'wayName',
                label: '付款方式',
                width: 150,
                fixed: !0,
                title: !0,
                editable: !0,
                classes: 'ui-ellipsis',
                edittype: 'custom',
                editoptions: {
                    custom_element: function(a, b) {
                        var c = $('.settlementtypeAuto')[0];
                        return c;
                    },
                    custom_value: function(a, b, c) {
                        if ('get' === b) {
                            if ('' !== $('.settlementtypeAuto').getCombo().getValue()) {
                                return $(a).val();
                            }
                            var d = $(a).parents('tr');
                            return d.removeData('settlementtypeInfo'), '';
                        }
                        'set' === b && $('input', a).val(c);
                    },
                    handle: function() {
                        $('#initCombo').append($('.settlementtypeAuto').val('').unbind('focus.once'));
                    },
                    trigger: 'ui-icon-triangle-1-s settlementtype-trigger iconfont icon-xiajiantou'
                }
            }, {
                name: 'settlementTime',
                label: '付款时间',
                width: 130,
                fixed: !0,
                editable: !0,
                editoptions: {
                    dataInit: function(element) {
                        $(element).attr('readonly', true);
                        $(element).on("click", function() {
                            laydate.render({
                                elem: element,
                                type: 'datetime'
                            });
                        }).trigger('click');
                    }
                }
            }, {
                name: 'uId',
                label: '付款人ID',
                hidden: !0
            }, {
                name: 'uName',
                label: '付款人',
                width: 44,
                fixed: !0,
                editable: !1,
                classes: 'gray'
            }, {
                name: 'description',
                label: '备注',
                width: 200,
                title: !0,
                editable: !0,
                classes: 'ui-ellipsis gray'
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
                var b = a.rows;
                var c = b.length;
                THISPAGE.newId = c + 1;
                for (var d = 0; c > d; d++) {
                    var f = b[d];
                    if ($.isEmptyObject(b[d])) {
                        break;
                    }
                    $('#' + f.id).data('expendtypeInfo', {
                        id: f.typeId,
                        name: f.typeName
                    }).data('companyAccountInfo', {
                        id: f.accId,
                        name: f.accName
                    }).data('settlementtypeInfo', {
                        id: f.wayId,
                        name: f.wayName
                    });
                }
                THISPAGE.calTotal();
            },
            loadError: function(a, b) {
                $.dialog.tips('Type: ' + b + '; Response: ' + a.status + ' ' + a.statusText, 2);
            },
            onCellSelect: function(rowId, iCol, cellContent, event) {
                if (a.id > 0) {
                    var rowData = $('#grid').jqGrid('getRowData', rowId);
                    if (rowData.uId != void 0 && rowData.uId != '' && rowData.uId != defaultPage.SYSTEM.uId) {
                        // 不是新增行、自己行，设置不允许编辑
                        $('#grid').jqGrid('setCell', rowId, 'amount', '', 'not-editable-cell');
                        $('#grid').jqGrid('setCell', rowId, 'typeName', '', 'not-editable-cell');
                        $('#grid').jqGrid('setCell', rowId, 'accName', '', 'not-editable-cell');
                        $('#grid').jqGrid('setCell', rowId, 'wayName', '', 'not-editable-cell');
                        $('#grid').jqGrid('setCell', rowId, 'settlementTime', '', 'not-editable-cell');
                        $('#grid').jqGrid('setCell', rowId, 'description', '', 'not-editable-cell');
                    }
                }
            },
            beforeEditCell: function(rowId, cellName, cellValue, iRow, iCol) {
                curRow = iRow;
                curCol = iCol;
            },
            afterEditCell: function(rowId, cellName, cellValue, iRow, iCol) {
                THISPAGE.curID = rowId;
                switch (cellName) {
                    case 'typeName':
                        $('#' + iRow + '_typeName', '#grid').val(cellValue);
                        break;
                    case 'accName':
                        $('#' + iRow + '_accName', '#grid').val(cellValue);
                        break;
                    case 'wayName':
                        $('#' + iRow + '_wayName', '#grid').val(cellValue);
                        break;
                    default:
                        break;
                }
            },
            afterSaveCell: function(rowId, cellName, cellValue, iRow, iCol) {
                'amount' == cellName && THISPAGE.calTotal();
            }
        });
        $('#grid').jqGrid('setGridParam', {
            cellEdit: !0
        });
    },
    initEvent: function() {
        Business.billsEvent(this, 'expend');
        contactCombo.input.enterKey();
        laydate.render({
            elem: '#billDate'
        });
        $('.wrapper').on('click', '#save', function(a) {
            a.preventDefault();
            if (Business.verifyRight('EXPEND_ADD')) {
                var data = THISPAGE.getPostData();
                if (data) {
                    $('.wrapper').append('<div id="div-lock"><div class="loading">保存中...</div></div>');
                    Public.ajaxPost('/Bill/MonIo/add', {
                        postData: JSON.stringify(data)
                    }, function(res) {
                        if (res && 200 == res.status) {
                            parent.$.dialog.tips('保存成功！');
                            parent.frames['money-expend'] && parent.frames['money-expend'].$('#search').trigger('click');
                            parent.frames['money-payReq'] && parent.frames['money-payReq'].$('#search').trigger('click');
                            parent.frames['money-expensesReq'] && parent.frames['money-expensesReq'].$('#search').trigger('click');
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
            if (Business.verifyRight('EXPEND_UPDATE')) {
                var data = THISPAGE.getPostData();
                if (data) {
                    $('.wrapper').append('<div id="div-lock"><div class="loading">保存中...</div></div>');
                    Public.ajaxPost('/Bill/MonIo/update', {
                        postData: JSON.stringify(data)
                    }, function(res) {
                        if (res && 200 == res.status) {
                            parent.$.dialog.tips('保存成功！');
                            parent.frames['money-expend'] && parent.frames['money-expend'].$('#search').trigger('click');
                            parent.frames['money-payReq'] && parent.frames['money-payReq'].$('#search').trigger('click');
                            parent.frames['money-expensesReq'] && parent.frames['money-expensesReq'].$('#search').trigger('click');
                            parent.tab.removeSelectedTabItem();
                        } else {
                            $('.wrapper > #div-lock').remove();
                            $.dialog.tips('保存失败！' + res.msg, 2);
                        }
                    });
                }
            }
        });
        $('.wrapper').on('click', '#check', function(a) {
            a.stopPropagation();
            a.preventDefault();
            if (Business.verifyRight('EXPEND_CHECK')) {
                originalData.id && Public.ajaxPost('/Bill/MonIo/check', {
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
                        parent.frames['money-expend'] && parent.frames['money-expend'].$('#search').trigger('click');
                    } else {
                        $.dialog.tips('审核失败！' + res.msg, 2);
                    }
                });
            }
        });
        $('.wrapper').on('click', '#uncheck', function(a) {
            a.stopPropagation();
            a.preventDefault();
            if (Business.verifyRight('EXPEND_UNCHECK')) {
                originalData.id && Public.ajaxPost('/Bill/MonIo/check', {
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
                        parent.frames['money-expend'] && parent.frames['money-expend'].$('#search').trigger('click');
                    } else {
                        $.dialog.tips('反审核失败！' + res.msg, 2);
                    }
                });
            }
        });
        $('.wrapper').on('click', '#delete', function(a) {
            a.preventDefault();
            if (Business.verifyRight('EXPEND_DELETE')) {
                originalData.id && $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                    Public.ajaxGet('/Bill/MonIo/delete', {
                        id: originalData.id
                    }, function(res) {
                        if (res && 200 == res.status) {
                            parent.$.dialog.tips('删除成功！');
                            parent.frames['money-expend'] && parent.frames['money-expend'].$('#search').trigger('click');
                            parent.frames['money-income'] && parent.frames['money-income'].$('#search').trigger('click');
                            parent.frames['money-payReq'] && parent.frames['money-payReq'].$('#search').trigger('click');
                            parent.frames['money-expensesReq'] && parent.frames['money-expensesReq'].$('#search').trigger('click');
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
            if (Business.verifyRight('EXPEND_UPDATE')) {
                Public.ajaxPost('/Bill/MonIo/updatedes', {
                    id: originalData.id,
                    newDes: $.trim($('#description').val())
                }, function(res) {
                    if (res && 200 == res.status) {
                        $.dialog.tips('备注保存成功！');
                        parent.frames['money-expend'] && parent.frames['money-expend'].$('#search').trigger('click');
                    } else {
                        $.dialog.tips('备注保存失败！' + res.msg, 2);
                    }
                });
            }
        });
        $('#upFile').uploader({
            action: '/Bill/MonIo/uploadfile',
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
                !Business.verifyRight('EXPEND_UPDATE') && a.preventDefault();
            },
            mouseover: function() {
                $(this).prev().addClass('black');
            },
            mouseleave: function() {
                $(this).prev().removeClass('black');
            }
        });
        $('#files').on('click', '.fileRename', function() {
            if (Business.verifyRight('EXPEND_UPDATE')) {
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
            if (Business.verifyRight('EXPEND_UPDATE')) {
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
        $('.grid-wrap').on('click', '.ui-icon-triangle-1-s', function(a) {
            var b = $(this).siblings();
            var c = b.getCombo();
            setTimeout(function() {
                c.active = !0;
                c.doQuery();
            }, 10);
        });
        $(window).on('resize', function() {
            Public.autoGridWidth($('#grid'));
        });
    },
    addFiles: function(files) {
        var fileDiv = '';
        $.each(files, function(k, v) {
            fileDiv += (
                '<div class="file-line" data-key="' + k + '" data-upname="' + v.upName + '">' +
                '<a href="/data/upfile/monIo/' + v.url + '" target="_blank" title="' + v.name + '"><span>' + v.name + '</span><span>' + v.upName + '</span><span>' + v.upTime + '</span></a>' +
                '<input type="text" class="renameInput dn" placeholder="输入新文件名">' +
                '<span class="renameSumbit dn">确定</span><span class="renameCancel dn">取消</span>' +
                '<i class="fileRename iconfont icon-bianji" title="改名"></i><i class="fileDel iconfont icon-shanchu" title="删除"></i>' +
                '</div>'
            );
        });
        $('#files').html(fileDiv);
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
        $('#amount').val(b);
    },
    getPostData: function() {
        curRow && curCol && ($('#grid').jqGrid('saveCell', curRow, curCol), curRow = curCol = null);
        var contactInput = $('#contact').find('input');
        if ('' === contactInput.val() || '(空)' === contactInput.val()) {
            var contactData = {};
            contactData.id = 0;
            contactData.name = '(空)';
            $('#contact').removeData('contactData');
        } else {
            var contactData = $('#contact').data('contactData');
            if (null === contactData) {
                return setTimeout(function() {
                    contactInput.focus().select();
                }, 15), $.dialog.tips('当前付款对象不存在！', 2), !1;
            }
        }
        var entriesData = THISPAGE.getEntriesData();
        if (!entriesData) {
            return !1;
        }
        if (entriesData.length > 0) {
            THISPAGE.calTotal();
            var data = {
                srcReqType: originalData.srcReqType || 0,
                srcReqId: originalData.srcReqId || 0,
                srcTransType: originalData.srcTransType || 0,
                srcId: originalData.srcId || 0,
                srcNo: originalData.srcNo || '',
                id: originalData.id,
                billDate: $.trim($('#billDate').val()),
                billNo: $.trim($('#billNo').val()),
                billType: 'EXP',
                transType: 103104,
                contactId: contactData.id,
                contactName: contactData.name,
                entries: entriesData,
                amount: $('#grid').jqGrid('footerData', 'get').amount.replace(/,/g, ''),
                cleared: $('#grid').jqGrid('footerData', 'get').amount.replace(/,/g, ''),
                description: $.trim($('#description').val()),
                files: originalData.files ? JSON.stringify(originalData.files) : ''
            };
            return data;
        } else {
            $.dialog.tips('结算信息不能为空！', 2);
            return !1;
        }
    },
    getEntriesData: function() {
        for (var entriesData = [], ids = $('#grid').jqGrid('getDataIDs'), i = 0, len = ids.length; len > i; i++) {
            var id = ids[i];
            var rowData = $('#grid').jqGrid('getRowData', id);
            if ('' != rowData.amount && parseFloat(rowData.amount) > 0) {
                if ('' != rowData.typeName && '' != rowData.accName && '' != rowData.wayName && '' != rowData.settlementTime) {
                    var expendtypeInfo = $('#grid #' + id).data('expendtypeInfo');
                    var companyAccountInfo = $('#grid #' + id).data('companyAccountInfo');
                    var settlementtypeInfo = $('#grid #' + id).data('settlementtypeInfo');
                    if (expendtypeInfo.id <= 0) {
                        $.dialog.tips('付款类别识别错误，请再次选择付款类别！', 2);
                        return !1;
                    }
                    if (companyAccountInfo.id <= 0) {
                        $.dialog.tips('付款账户识别错误，请再次选择付款账户！', 2);
                        return !1;
                    }
                    if (settlementtypeInfo.id <= 0) {
                        $.dialog.tips('付款方式识别错误，请再次选择付款方式！', 2);
                        return !1;
                    }
                    var data = {
                        amount: rowData.amount,
                        typeId: expendtypeInfo.id,
                        accId: companyAccountInfo.id,
                        wayId: settlementtypeInfo.id,
                        uId: rowData.uId || defaultPage.SYSTEM.uId,
                        settlementTime: rowData.settlementTime,
                        description: rowData.description
                    };
                    entriesData.push(data);
                } else {
                    $.dialog.tips('请准确选择每行的（付款类别、付款账户、付款方式、付款时间），不能为空！', 2);
                    return !1;
                }
            }
        }
        return entriesData;
    }
};
$(function() {
    if (urlParam.turnByPayReq || urlParam.turnByExpensesReq) {
        var openerData = window.parent.document.getElementById(urlParam.tabId).openerData;
        originalData = {
            srcReqType: Number(openerData.type),
            srcReqId: Number(openerData.id),
            id: -1,
            status: 'add',
            transType: 103104,
            entries: [{
                id: '1',
                amount: openerData.amount,
                typeId: openerData.typeId || -1,
                typeName: openerData.typeName || '',
                accId: openerData.accId || -1,
                accName: openerData.accName || '',
                wayId: openerData.wayId || -1,
                wayName: openerData.wayName || '',
                settlementTime: (new Date).format('yyyy-MM-dd hh:mm:ss')
            }],
            amount: openerData.amount,
            description: openerData.title
        };
        THISPAGE.init(originalData);
        THISPAGE.calTotal();
    } else if (urlParam.id) {
        $('.wrapper').append('<div id="div-lock"><div class="loading">单据准备中...</div></div>');
        Public.ajaxGet('/Bill/MonIo/query', {
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
            transType: 103104,
            contactId: -1,
            entries: [],
            amount: '0.00',
            description: ''
        };
        THISPAGE.init(originalData);
    }
});
