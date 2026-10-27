var api = frameElement.api;
var defaultPage = Public.getDefaultPage();
var oper = api.data.oper;
var cRowId = api.data.rowId;
var rowData = api.data.data || {};
var callback = api.data.callback;
var categoryData = {};
var categoryCombo;
var levelCombo;
var curRow;
var curCol;
var THISPAGE = {
    init: function() {
        if (void 0 !== cRowId) {
            Public.ajaxPost('/Base/Contact/query?type=0', {
                id: cRowId
            }, function(res) {
                if (200 == res.status) {
                    rowData = res.data;
                    THISPAGE.initBtns();
                    THISPAGE.initDom(rowData);
                    THISPAGE.initGrid(rowData.links);
                    THISPAGE.initEvent();
                    THISPAGE.initValidator();
                } else {
                    $.dialog.tips('获取数据失败！' + res.msg, 2);
                }
            });
        } else {
            THISPAGE.initBtns();
            THISPAGE.initDom(rowData);
            THISPAGE.initGrid(rowData.links || [{}]);
            THISPAGE.initEvent();
            THISPAGE.initValidator();
        }
    },
    initBtns: function() {
        if (api.config.disEdit) {
            api.button({
                id: 'enEdit',
                name: '转到编辑模式',
                defClass: 'ui_state_highlight',
                callback: function() {
                    if (Business.verifyRight('CUSTOMER_UPDATE')) {
                        parent.$('.ui_buttons .dialog-btn.ui_state_highlight').remove();
                        api.config.disEdit = !1;
                        api.config.onlyAdd = !0;
                        var uiTitle = $(".ui_title", parent.document).text().replace('◉ 视窗', '');
                        if (uiTitle.includes('查看')) {
                            $(".ui_title", parent.document).text(uiTitle.replace('查看', '编辑'));
                        }
                        window.location.reload();
                        return !1;
                    }
                }
            }, {
                id: 'cancel',
                name: '关闭'
            });
        } else {
            if (api.config.onlyAdd) {
                api.button({
                    id: 'confirm',
                    name: '保存',
                    defClass: 'ui_state_highlight',
                    callback: function() {
                        return THISPAGE.cancleGridEdit(), THISPAGE.postData(1), !1;
                    }
                }, {
                    id: 'cancel',
                    name: '关闭'
                });
            } else {
                api.button({
                    id: 'saveAndAdd',
                    name: '保存并新增',
                    defClass: 'ui_state_highlight',
                    callback: function() {
                        return THISPAGE.cancleGridEdit(), THISPAGE.postData(0), !1;
                    }
                }, {
                    id: 'confirm',
                    name: '保存',
                    defClass: 'ui_state_highlight',
                    callback: function() {
                        return THISPAGE.cancleGridEdit(), THISPAGE.postData(1), !1;
                    }
                }, {
                    id: 'cancel',
                    name: '关闭'
                });
            }
        }
    },
    initDom: function() {
        if ('edit' == oper) {
            $('#category').data('defItem', ['id', rowData.categoryId]);
            $('#name').val(rowData.name);
            $('#cLevel').data('defItem', ['id', rowData.cLevel]);
            $('#beginDate').val(rowData.beginDate);
            $('#amount').val(rowData.amount);
            $('#comeFrom').val(rowData.comeFrom);
            $('#description').val(rowData.description);
            if (rowData.files) {
                THISPAGE.addFiles(rowData.files);
            }
            if (api.config.disEdit) {
                $('#name').attr('disabled', true).addClass('ui-input-dis');
                $('#beginDate').attr('disabled', true).addClass('ui-input-dis');
                $('#amount').attr('disabled', true).addClass('ui-input-dis');
                $('#comeFrom').attr('disabled', true).addClass('ui-input-dis');
                $('#description').attr('disabled', true).addClass('ui-input-dis');
            }
        } else {
            $('#name').val(rowData.name || '');
            $('#beginDate').val(defaultPage.SYSTEM.endDate);
            $('#amount').val(0);
            $('#comeFrom').val('手工录入');
            $('#description').val(rowData.description || '');
        }
        categoryCombo = Business.categoryCombo($('#category'), {
            listHeight: 470,
            defaultSelected: rowData.categoryId > 0 ? ['id', rowData.categoryId] : -1,
            disabled: api.config.disEdit ? !0 : !1
        }, 'customertype');
        levelCombo = $('#cLevel').combo({
            data: [
                {id: 0, name: '零售'},
                {id: 1, name: '批发'},
                {id: 2, name: '大宗'},
                {id: 3, name: '分销'},
                {id: 4, name: '战略'}
            ],
            value: 'id',
            text: 'name',
            width: 292,
            editable: !1,
            trigger: !0,
            defaultSelected: $('#cLevel').data('defItem') || 0,
            disabled: api.config.disEdit ? !0 : !1
        }).getCombo();
        $('#category').find('input').attr('name', 'categoryInput');
        $('#cLevel').find('input').attr('name', 'cLevelInput');
    },
    initGrid: function(a) {
        if (a || (a = []), a.length < 1) {
            a.push({});
        }
        THISPAGE.newId = 2;
        $('#grid').jqGrid({
            data: a,
            datatype: 'local',
            colModel: [{
                name: 'operate',
                label: '操作',
                width: 72.8,
                fixed: !0,
                formatter: Public.billsOper
            }, {
                name: 'contacter',
                label: '联系人',
                width: 52,
                fixed: !0,
                editable: api.config.disEdit ? !1 : !0
            }, {
                name: 'place',
                label: '职位',
                width: 60,
                fixed: !0,
                editable: api.config.disEdit ? !1 : !0
            }, {
                name: 'wangwang',
                label: '旺旺',
                width: 150,
                fixed: !0,
                editable: api.config.disEdit ? !1 : !0
            }, {
                name: 'weixin',
                label: '微信',
                width: 150,
                fixed: !0,
                editable: api.config.disEdit ? !1 : !0
            }, {
                name: 'im',
                label: 'QQ',
                width: 90,
                fixed: !0,
                editable: api.config.disEdit ? !1 : !0
            }, {
                name: 'mobile',
                label: '手机',
                width: 90,
                fixed: !0,
                editable: api.config.disEdit ? !1 : !0
            }, {
                name: 'phone',
                label: '座机',
                width: 100,
                fixed: !0,
                editable: api.config.disEdit ? !1 : !0
            }, {
                name: 'addressStr',
                label: '联系地址',
                width: 150,
                classes: 'ui-ellipsis',
                formatter: function(a, b, c) {
                    if (a) {
                        return a;
                    }
                    var d = {};
                    d.province = c.province || '';
                    d.city = c.city || '';
                    d.county = c.county || '';
                    d.address = c.address || '';
                    $('#' + c.id).data('addressInfo', d);
                    return d.province + d.city + d.county + d.address || '&nbsp;';
                },
                editable: api.config.disEdit ? !1 : !0,
                edittype: 'custom',
                editoptions: {
                    custom_element: function(a, b) {
                        var c = $('.address')[0];
                        return c;
                    },
                    custom_value: function(a, b, c) {
                        if ('get' === b) {
                            var d = $.trim($('.address').val());
                            return '' !== d ? d : '';
                        }
                        'set' === b && $('input', a).val(c);
                    },
                    handle: function() {
                        $('.hideFile').append($('.address').val('').unbind('focus.once'));
                    },
                    trigger: 'ui-icon-ellipsis iconfont icon-shenglvehao'
                }
            }, {
                name: 'first',
                label: '主联系人',
                width: 60,
                fixed: !0,
                title: !1,
                formatter: function(a, b, c) {
                    return a = ('boolean' == typeof a ? (a ? '1' : '0') : a), '1' === a ? '是' : '&nbsp;';
                },
                editable: api.config.disEdit ? !1 : !0,
                edittype: 'select',
                editoptions: {
                    value: {
                        0: '否',
                        1: '是'
                    }
                }
            }],
            cmTemplate: {sortable: !1, title: !0},
            localReader: {root: 'rows', records: 'records', repeatitems: !0},
            width: 1229,
            height: 90,
            gridview: !0,
            onselectrow: !1,
            rownumbers: !0,
            shrinkToFit: !0,
            forceFit: !1,
            cellEdit: !0,
            cellsubmit: 'clientArray',
            loadComplete: function(a) {
                var b = a.rows;
                var c = b.length;
                THISPAGE.newId = c + 1;
                for (var d = 0; d < c; d++) {
                    var e = b[d];
                    if (e.id) {
                        var f = {
                            province: e.province,
                            city: e.city,
                            county: e.county,
                            address: e.address
                        };
                        $('#' + e.id).data('addressInfo', f);
                    }
                }
            },
            loadError: function(a, b) {
                $.dialog.tips('Type: ' + b + '; Response: ' + a.status + ' ' + a.statusText, 2);
            },
            beforeEditCell: function(rowId, cellName, cellValue, iRow, iCol) {
                curRow = iRow;
                curCol = iCol;
            },
            afterEditCell: function(rowId, cellName, cellValue, iRow, iCol) {
                THISPAGE.curID = rowId;
                $('#' + rowId).find('input').val(cellValue);
            },
            afterSaveCell: function(rowId, cellName, cellValue, iRow, iCol) {
                if ('first' == cellName && (cellValue = 'boolean' == typeof cellValue ? cellValue ? '1' : '0' : cellValue, '1' === cellValue)) {
                    for (var ids = $('#grid').jqGrid('getDataIDs'), i = 0; i < ids.length; i++) {
                        var id = ids[i];
                        id != rowId && $('#grid').jqGrid('setCell', id, 'first', '0');
                    }
                }
            }
        });
    },
    initEvent: function() {
        if (!api.config.disEdit) {
            laydate.render({
                elem: "#beginDate",
            });
            $('#amount').on({
                keypress: Public.numerical,
                focus: function() {
                    $(this).select();
                },
                blur: function() {
                    this.value = Number(this.value).toFixed(2);
                }
            });
            // 新增一行
            $('.grid-wrap').on('click', '.ui-icon-plus', function(a) {
                a.preventDefault();
                var rowId = $(this).parent().data('id');
                var datarow = {id: THISPAGE.newId};
                var su = $("#grid").jqGrid('addRowData', THISPAGE.newId, datarow, 'last', rowId);
                if (su) {
                    $(this).parents('td').removeAttr('class');
                    $(this).parents('tr').removeClass('selected-row ui-state-hover');
                    $("#grid").jqGrid('resetSelection');
                    THISPAGE.newId++;
                }
            });
            // 删除本行
            $('.grid-wrap').on('click', '.ui-icon-trash', function(a) {
                a.preventDefault();
                if ($('#grid tbody tr').length === 2) {
                    $.dialog.tips('至少保留一行！', 2);
                    return false;
                }
                var rowId = $(this).parent().data('id');
                var su = $("#grid").jqGrid('delRowData', rowId);
                if (su) {
                    THISPAGE.calTotal && THISPAGE.calTotal();
                };
            });
            // 编辑地址
            $('.grid-wrap').on('click', '.ui-icon-ellipsis', function(a) {
                a.preventDefault();
                var b = $(this).siblings();
                var c = $(this).closest('tr');
                var d = c.data('addressInfo');
                $.dialog({
                    title: '联系地址',
                    content: 'url:/Uri/address_manage',
                    data: {
                        rowData: d,
                        callback: function(e, f) {
                            if (e) {
                                var g = {};
                                g.province = e.province || '';
                                g.city = e.city || '';
                                g.county = e.area || '';
                                g.address = e.address || '';
                                b.val(g.province + g.city + g.county + g.address);
                                c.data('addressInfo', g);
                            }
                            f.close();
                        }
                    },
                    width: 900,
                    height: 210,
                    min: !1,
                    max: !1,
                    cache: !1,
                    lock: !0
                });
            });
            $('#upFile').uploader({
                action: '/Base/Contact/uploadfile',
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
                        if (!rowData.files) {
                            rowData.files = [];
                            rowData.files[0] = data.file;
                        } else {
                            rowData.files.unshift(data.file);
                        }
                        THISPAGE.addFiles(rowData.files);
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
                    !Business.verifyRight('CUSTOMER_UPDATE') && a.preventDefault();
                },
                mouseover: function() {
                    $(this).prev().addClass('black');
                },
                mouseleave: function() {
                    $(this).prev().removeClass('black');
                }
            });
            $('#files').on('click', '.fileRename', function() {
                if (Business.verifyRight('CUSTOMER_UPDATE')) {
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
                    rowData.files[key].name = newName;
                    THISPAGE.addFiles(rowData.files);
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
                if (Business.verifyRight('CUSTOMER_UPDATE')) {
                    var upName = $(this).parent().data('upname');
                    if (upName !== defaultPage.SYSTEM.uName) {
                        $.dialog.tips('禁止删除别人上传的文件！', 2);
                    } else {
                        $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                            var key = $(this).closest('.file-line').data('key');
                            rowData.files.splice(key, 1) && THISPAGE.addFiles(rowData.files);
                        });
                    }
                }
            });
            $(document).on('click.cancle', function(a) {
                var b = a.target || a.srcElement;
                !$(b).closest('#grid').length > 0 && THISPAGE.cancleGridEdit();
            });
        }
    },
    addFiles: function(files) {
        var fileDiv = '';
        $.each(files, function(k, v) {
            fileDiv += (
                '<div class="file-line" data-key="' + k + '" data-upname="' + v.upName + '">' +
                '<a href="/data/upfile/contact/' + v.url + '" target="_blank" title="' + v.name + '"><span>' + v.name + '</span><span>' + v.upName + '</span><span>' + v.upTime + '</span></a>' +
                '<input type="text" class="renameInput dn" placeholder="输入新文件名">' +
                '<span class="renameSumbit dn">确定</span><span class="renameCancel dn">取消</span>' +
                '<i class="fileRename iconfont icon-bianji" title="改名"></i><i class="fileDel iconfont icon-shanchu" title="删除"></i>' +
                '</div>'
            );
        });
        $('#files').html(fileDiv);
    },
    initValidator: function() {
        $('#manage-form').validate({
            rules: {
                name: {required: !0},
                categoryInput: {required: !0},
                cLevelInput: {required: !0},
                beginDate: {required: !0},
                comeFrom: {required: !0}
            }
        });
    },
    postData: function(callbackType = 0) {
        if (Business.verifyRight('CUSTOMER_UPDATE')) {
            if (!$('#manage-form').validate().form()) {
                return void $('#manage-form').find('input.valid-error').eq(0).focus();
            }
            var entriesData = THISPAGE.getEntriesData();
            Public.ajaxPost('/Base/Contact/checkrepeat', {
                type: 0,
                id: void 0 !== cRowId ? cRowId : -1,
                linkMobile: entriesData.firstLink ? entriesData.firstLink['linkMobile'] : ''
            }, function(res) {
                if (-1 == res.status) {
                    $.dialog.tips('已有相同信息的客户存在！', 2);
                    return !1;
                } else {
                    var title = 'add' == oper ? '新增客户' : '编辑客户';
                    var contactData = THISPAGE.getContactData();
                    var firstLink = contactData.firstLink || {};
                    delete contactData.firstLink;
                    Public.ajaxPost('/Base/Contact/' + ('add' == oper ? 'add' : 'update') + '?type=0', contactData, function(res_1) {
                        if (200 == res_1.status) {
                            parent.parent.$.dialog.tips(title + '成功！');
                            if (callback && 'function' == typeof callback) {
                                contactData.id = res_1.data.id;
                                contactData.contacter = firstLink.linkName || '';
                                contactData.place = firstLink.linkPlace || '';
                                contactData.wangwang = firstLink.linkWangWang || '';
                                contactData.weixin = firstLink.linkWeixin || '';
                                contactData.im = firstLink.linkIm || '';
                                contactData.mobile = firstLink.linkMobile || '';
                                contactData.phone = firstLink.linkPhone || '';
                                contactData.deliveryAddress = firstLink.province + firstLink.city + firstLink.county + firstLink.address;
                                callback(contactData, oper, window, callbackType);
                            } else {
                                this.api.close();
                            }
                        } else {
                            $.dialog.tips(title + '失败！' + res_1.msg, 2);
                        }
                    });
                }
            });
        }
    },
    getContactData: function() {
        var entriesData = THISPAGE.getEntriesData();
        var linkMans = entriesData.linkMans;
        var data = {
            id: void 0 !== cRowId ? cRowId : -1,
            name: $.trim($('#name').val()),
            categoryId: categoryCombo.getValue(),
            cLevel: levelCombo.getValue(),
            beginDate: $('#beginDate').val(),
            amount: $('#amount').val() || 0,
            comeFrom: $('#comeFrom').val(),
            linkMans: JSON.stringify(linkMans),
            firstLink: entriesData.firstLink,
            description: $.trim($('#description').val()),
            files: rowData.files ? JSON.stringify(rowData.files) : ''
        };
        return data;
    },
    getEntriesData: function() {
        for (var entriesData = {}, entries = [], ids = $('#grid').jqGrid('getDataIDs'), isFirst = !1, i = 0, len = ids.length; len > i; i++) {
            var id = ids[i];
            var rowData = $('#grid').jqGrid('getRowData', id);
            if (rowData.wangwang !== '' || rowData.weixin !== '' || rowData.im !== '' || rowData.mobile !== '' || rowData.phone !== '' || rowData.addressStr !== '') {
                var data = {
                    linkId: id,
                    linkName: rowData.contacter,
                    linkPlace: rowData.place,
                    linkWangWang: rowData.wangwang,
                    linkWeixin: rowData.weixin,
                    linkIm: rowData.im,
                    linkMobile: rowData.mobile,
                    linkPhone: rowData.phone,
                    linkAddress: rowData.address,
                    linkFirst: rowData.first == '是' ? 1 : 0
                };
                var addressInfo = $('#' + id).data('addressInfo') || {};
                data.province = addressInfo.province;
                data.city = addressInfo.city;
                data.county = addressInfo.county;
                data.address = addressInfo.address;
                rowData.first == '是' && (isFirst = !0, entriesData.firstLink = data);
                entries.push(data);
            }
        }
        // 如未设置首要联系人，则设置最靠前的一条为首要联系人
        if (!isFirst && entries.length > 0) {
            entries[0].linkFirst = 1;
            entriesData.firstLink = entries[0];
        }
        entriesData.linkMans = entries;
        return entriesData;
    },
    cancleGridEdit: function() {
        curRow && curCol && ($('#grid').jqGrid('saveCell', curRow, curCol), curRow = curCol = null);
    },
    resetForm: function(a) {
        var uiTitle = $(".ui_title", parent.document).text().replace('◉ 视窗', '');
        if (uiTitle.includes('编辑')) {
            $(".ui_title", parent.document).text(uiTitle.replace('编辑', '新增'));
        }
        oper = 'add';
        cRowId = void 0;
        rowData = {};
        var data = [{}];
        $('#name').val('');
        categoryCombo._setDefaultSelected(0);
        levelCombo._setDefaultSelected(0);
        $('#beginDate').val(defaultPage.SYSTEM.endDate);
        $('amount').val('');
        $('#comeFrom').val('手工录入');
        $('#grid').jqGrid('clearGridData').jqGrid('setGridParam', {
            data: data
        }).trigger('reloadGrid');
        $('#description').val('');
        $('#files').html('');
    }
};
$(function() {
    THISPAGE.init();
});
