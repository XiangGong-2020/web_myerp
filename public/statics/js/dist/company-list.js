var defaultPage = Public.getDefaultPage();
var queryConditions = {
    isDisable: 0,
    skey: ''
};
var statusCombo = $('#combo1').combo({
    data: [
        {id: -1, name: '所有状态'},
        {id: 0, name: '存续中'},
        {id: 1, name: '已注销'}
    ],
    value: 'id',
    text: 'name',
    defaultSelected: 1,
    width: 78,
    callback: {
        onListClick: function() {
            $('#search').trigger('click');
        }
    }
}).getCombo();
var THISPAGE = {
    init: function() {
        THISPAGE.initGrid();
        THISPAGE.initEvent();
    },
    initGrid: function() {
        var a = Public.setGrid();
        $('#grid').jqGrid({
            url: '/Base/Company/list',
            postData: queryConditions,
            datatype: 'json',
            colModel: [{
                name: 'id',
                label: '<span class="ml10">ID</span>',
                width: 40,
                fixed: !0,
                sortable: !0,
                classes: 'ti10 gray'
            }, {
                name: 'name',
                label: '主体简称',
                width: 80,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'fullName',
                label: '主体全称',
                width: 200,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'no',
                label: '信用代码',
                width: 160,
                fixed: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'managerName',
                label: '法人姓名',
                width: 64,
                fixed: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'address',
                label: '注册地址',
                width: 100,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'date',
                label: '注册日期',
                width: 74,
                fixed: !0,
                sortable: !0
            }, {
                name: 'businessScope',
                label: '经营范围',
                width: 100,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'businessLicense',
                label: '营业执照',
                width: 70,
                fixed: !0,
                sortable: !0,
                formatter: function(cellvalue, options, rowObject) {
                    img = '';
                    if (cellvalue) {
                        img += '<a target="_blank" href="/data/upfile/company/' + cellvalue + '"><img width="40" height="32" class="fl b-r-3" src="/data/upfile/company/' + cellvalue + '"></a>';
                    }
                    return img;
                }
            }, {
                name: 'type',
                label: '纳税资质',
                width: 80,
                fixed: !0,
                sortable: !0,
                formatter: function(a) {
                    var b = '';
                    switch (a) {
                        case 0:
                            b = '个体工商户';
                            break;
                        case 1:
                            b = '小规模纳税人';
                            break;
                        case 2:
                            b = '一般纳税人';
                            break;
                        default:
                            break;
                    }
                    return b;
                }
            }, {
                name: 'invoiceway',
                label: '征收方式',
                width: 66,
                fixed: !0,
                sortable: !0,
                formatter: function(a) {
                    var b = '';
                    switch (a) {
                        case 0:
                            b = '查账征收';
                            break;
                        case 1:
                            b = '核定征收';
                            break;
                        case 2:
                            b = '定额征收';
                            break;
                        default:
                            break;
                    }
                    return b;
                }
            }, {
                name: 'invoicelimit',
                label: '开票限额',
                width: 76,
                fixed: !0,
                sortable: !0
            }, {
                name: 'account',
                label: '结算账户',
                width: 100,
                title: !1,
                sortable: !1,
                classes: 'ui-ellipsis',
                formatter: function(a) {
                    var b = a ? ('<span title="' + a.replace(/,/g, '<br>') + '">' + a + '</span>') : '';
                    return b;
                }
            }, {
                name: 'billFrom',
                label: '销售渠道',
                width: 100,
                title: !1,
                sortable: !1,
                classes: 'ui-ellipsis',
                formatter: function(a) {
                    var b = a ? ('<span title="' + a.replace(/,/g, '<br>') + '">' + a + '</span>') : '';
                    return b;
                }
            }, {
                name: 'description',
                label: '备注',
                width: 100,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray'
            }, {
                name: 'ordnum',
                label: '排序',
                width: 34,
                fixed: !0,
                editable: !0,
                sortable: !0
            }, {
                name: 'isDisable',
                label: '状态',
                width: 60,
                fixed: !0,
                sortable: !0,
                align: 'center',
                formatter: function(a, b, c) {
                    var d = a == 0 ? '存续中' : '已注销';
                    var e = a == 0 ? 'hover-green' : 'hover-red';
                    var f = a == 0 ? '点击设置为 已注销' : '点击设置为 存续中';
                    return '<span class="disable-btn db h28 lh28 b-r-3 ' + e + '" title="' + f + '" data-isdisable="' + a + '" data-id="' + c.id + '">' + d + '</span>';
                }
            }, {
                name: 'operate',
                label: '操作',
                width: 72.8,
                fixed: !0,
                formatter: Public.operFmatter
            }],
            cmTemplate: {sortable: !1, title: !1},
            jsonReader: {root: 'data.rows', records: 'data.records', total: 'data.total', repeatitems: !1, id: 'id'},
            width: a.w,
            height: a.h,
            altRows: !1,
            gridview: !0,
            rownumbers: !1,
            shrinkToFit: !0,
            forceFit: !1,
            viewsortcols: [true, 'vertical', true],
            sortname: 'ordnum',
            sortorder: 'ASC',
            pager: '#page',
            page: 1,
            rowNum: 100,
            rowList: [100, 200, 500],
            viewrecords: !0,
            cellEdit: !0,
            cellsubmit: 'clientArray',
            triggerAdd: !1,
            loadComplete: function(data) {
                data.status == -1 && $.dialog.tips(data.msg, 2);
            },
            loadError: function(a, b) {
                $.dialog.tips('Type: ' + b + '; Response: ' + a.status + ' ' + a.statusText, 2);
            },
            ondblClickRow: function(b) {
                $('#' + b).find('.ui-icon-pencil').trigger('click');
            },
            afterSaveCell: function(rowId, cellName, cellValue, iRow, iCol) {
                if ('ordnum' == cellName) {
                    var id = parseInt($('#grid').jqGrid('getCell', rowId, 'id'));
                    var ordnum = parseInt(cellValue);
                    if (id && $.isNumeric(cellValue)) {
                        Public.ajaxPost('/Base/Company/ordnumChange', {
                            id: id,
                            ordnum: ordnum
                        }, function(res) {
                            if (res && 200 == res.status) {
                                $.dialog.tips('保存成功！');
                            } else {
                                $.dialog.tips('保存失败！' + res.msg, 2);
                            }
                        });
                    }
                }
            }
        });
    },
    initEvent: function() {
        $('#search').on('click', function() {
            queryConditions.isDisable = statusCombo.getValue();
            queryConditions.skey = $.trim($('#skey').val()).split(/[ ,，]+/).toString();
            $('#grid').jqGrid('setGridParam', {
                postData: queryConditions,
                page: 1
            }).trigger('reloadGrid');
        });
        $('#refresh').on('click', function() {
            window.location.reload();
        });
        $('#add').on('click', function(a) {
            a.preventDefault();
            if (Business.verifyRight('COMPANY_ADD')) {
                $.dialog({
                    title: '新增经营主体',
                    content: 'url:/Uri/company_manage',
                    data: {
                        oper: 'add',
                        callback: THISPAGE.callback
                    },
                    width: 1250,
                    height: 500,
                    min: !1,
                    max: !1,
                    cache: !1,
                    lock: !0
                });
            }
        });
        $('.grid-wrap').on('click', '.disable-btn', function(a) {
            a.stopPropagation();
            a.preventDefault();
            if (Business.verifyRight('COMPANY_UPDATE')) {
                var id = $(this).data('id');
                var isDisable = Number(!$(this).data('isdisable'));
                if (isDisable == 1) {
                    var comfirm = '注销经营主体将使该经营主体无法进行新业务，并将自动注销关联的结算账户；自动删除所有相关发票往来，删除的数据无法恢复，确定继续？';
                } else {
                    var comfirm = '启用经营主体并不能恢复该经营主体注销时被删除的所有相关发票往来，确定继续？';
                }
                $.dialog.confirm(comfirm, function() {
                    id && Public.ajaxPost('/Base/Company/disable', {
                        id: id,
                        isDisable: isDisable
                    }, function(res) {
                        if (res && 200 == res.status) {
                            $.dialog.tips('设置成功！');
                            $('#grid').jqGrid('setCell', id, 'isDisable', isDisable);
                            parent.frames['money-companyAccount'] && parent.frames['money-companyAccount'].$('#search').trigger('click');
                            parent.frames['money-invoice'] && parent.frames['money-invoice'].$('#search').trigger('click');
                        } else {
                            $.dialog.tips('设置失败！' + res.msg, 2);
                        }
                    });
                });
            }
        });
        $('.grid-wrap').on('click', '.ui-icon-pencil', function(a) {
            a.preventDefault();
            var id = $(this).parent().data('id');
            $.dialog({
                title: '编辑经营主体',
                content: 'url:/Uri/company_manage',
                data: {
                    oper: 'edit',
                    rowId: id,
                    callback: THISPAGE.callback
                },
                width: 1250,
                height: 500,
                min: !1,
                max: !1,
                cache: !1,
                lock: !0
            });
        });
        $('.grid-wrap').on('click', '.ui-icon-trash', function(a) {
            a.preventDefault();
            if (Business.verifyRight('COMPANY_DELETE')) {
                var id = $(this).parent().data('id');
                id && $.dialog.confirm('删除经营主体将同步删除对应结算账户、发票，并自动将关联的销售渠道、职员等信息关联到“虚拟主体”上，删除后数据不可恢复，确定尝试删除？', function() {
                    Public.ajaxPost('/Base/Company/delete', {
                        id: id
                    }, function(res) {
                        if (res && 200 == res.status) {
                            $.dialog.tips('删除成功！');
                            $('#grid').jqGrid('delRowData', id);
                        } else {
                            $.dialog.tips('删除失败！' + res.msg, 2);
                        }
                    });
                });
            }
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
    },
    callback: function(a, b, c, d) {
        var e = $('#grid').data('gridData');
        e || (e = {}, $('#grid').data('gridData', e));
        e[a.id] = a;
        'edit' == b ? ($('#grid').jqGrid('setRowData', a.id, a), c && (d == 1 ? c.api.close() : c.THISPAGE.resetForm())) : ($('#grid').jqGrid('addRowData', a.id, a, 'first'), c && (d == 1 ? c.api.close() : c.THISPAGE.resetForm()));
    }
};
$(function() {
    THISPAGE.init();
});
