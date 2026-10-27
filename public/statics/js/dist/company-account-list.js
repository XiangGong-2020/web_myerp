var queryConditions = {
    isDisable: 0,
    skey: ''
};
var statusCombo = $('#combo1').combo({
    data: [
        {id: -1, name: '所有状态'},
        {id: 0, name: '使用中'},
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
            url: '/Base/CompanyAccount/list',
            postData: queryConditions,
            datatype: 'json',
            colModel: [{
                name: 'id',
                label: '<span class="ml10">ID</span>',
                width: 60,
                fixed: !0,
                sortable: !0,
                classes: 'ti10 gray'
            }, {
                name: 'name',
                label: '账户名称',
                width: 150,
                fixed: !0,
                sortable: !0
            }, {
                name: 'company',
                label: '关联经营主体',
                width: 200,
                fixed: !0,
                title: !0,
                sortable: !0,
                formatter: 'ui-ellipsis'
            }, {
                name: 'date',
                label: '建账日期',
                width: 100,
                fixed: !0,
                sortable: !0
            }, {
                name: 'type',
                label: '账户类别',
                width: 100,
                fixed: !0,
                sortable: !0,
                formatter: function(a) {
                    var b = '';
                    switch (a) {
                        case 1:
                            b = '人民币';
                            break;
                        case 2:
                            b = '美元';
                            break;
                        default:
                            break;
                    }
                    return b;
                }
            }, {
                name: 'amount',
                label: '期初余额',
                width: 100,
                fixed: !0,
                hidden: !0,
                formatter: 'currency',
                formatoptions: {
                    showZero: !0,
                    decimalPlaces: 2
                }
            }, {
                name: 'amountOver',
                label: '当前余额',
                width: 150,
                fixed: !0,
                hidden: !0,
                formatter: 'currency',
                formatoptions: {
                    showZero: !0,
                    decimalPlaces: 2
                }
            }, {
                name: 'description',
                label: '备注',
                width: 100,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray'
            }, {
                name: 'isDisable',
                label: '状态',
                width: 60,
                fixed: !0,
                sortable: !0,
                align: 'center',
                formatter: function(a, b, c) {
                    var d = a == 0 ? '使用中' : '已注销';
                    var e = a == 0 ? 'hover-green' : 'hover-red';
                    var f = a == 0 ? '点击设置为 已注销' : '点击设置为 使用中';
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
            multiselect: !1,
            shrinkToFit: !0,
            forceFit: !1,
            viewsortcols: [true, 'vertical', true],
            sortname: 'id',
            sortorder: 'ASC',
            pager: '#page',
            page: 1,
            rowNum: 100,
            rowList: [100, 200, 500],
            viewrecords: !0,
            loadComplete: function(data) {
                if (data && 200 == data.status) {
                    var ids = '';
                    for (var i = 0; i < data.data.rows.length; i++) {
                        var row = data.data.rows[i];
                        ids += ids ? ',' + row.id : row.id;
                    }
                    ids && Public.ajaxPost('/Base/CompanyAccount/findAmountOver', {
                        ids: ids
                    }, function(res) {
                        if (res && 200 == res.status) {
                            for (var j = 0; j < res.data.rows.length; j++) {
                                var rowData = res.data.rows[j];
                                $('#grid').jqGrid('setCell', rowData.id, 'amountOver', rowData.amountOver);
                            }
                        } else {
                            $.dialog.tips('获取当前余额失败！', 2);
                        }
                    });
                } else {
                    data.status == -1 && $.dialog.tips(data.msg, 2);
                }
            },
            loadError: function(a, b) {
                $.dialog.tips('Type: ' + b + '; Response: ' + a.status + ' ' + a.statusText, 2);
            },
            ondblClickRow: function(b) {
                $('#' + b).find('.ui-icon-pencil').trigger('click');
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
            if (Business.verifyRight('COMPANYACCOUNT_ADD')) {
                var b = {
                    oper: 'add',
                    callback: THISPAGE.callback
                };
                $.dialog({
                    title: '新增结算账户',
                    content: 'url:/Uri/company_account_manage',
                    data: b,
                    width: 600,
                    height: 410,
                    min: !1,
                    max: !1,
                    cache: !1,
                    lock: !0
                });
            }
        });
        $('.grid-wrap').on('click', '.ui-icon-pencil', function(a) {
            a.preventDefault();
            var id = $(this).parent().data('id');
            $.dialog({
                title: '编辑结算账户',
                content: 'url:/Uri/company_account_manage',
                data: {
                    oper: 'edit',
                    rowId: id,
                    callback: THISPAGE.callback
                },
                width: 600,
                height: 410,
                max: !1,
                min: !1,
                cache: !1,
                lock: !0
            });
        });
        $('.grid-wrap').on('click', '.ui-icon-trash', function(a) {
            a.preventDefault();
            if (Business.verifyRight('COMPANYACCOUNT_DELETE')) {
                var id = $(this).parent().data('id');
                id && $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                    Public.ajaxPost('/Base/CompanyAccount/delete', {
                        id: id
                    }, function(res) {
                        if (res && 200 == res.status) {
                            $.dialog.tips('删除成功！');
                            $('#grid').jqGrid('delRowData', id);
                            parent.frames['money-company'] && parent.frames['money-company'].$('#search').trigger('click');
                        } else {
                            $.dialog.tips('删除失败！' + res.msg, 2);
                        }
                    });
                });
            }
        });
        $('.grid-wrap').on('click', '.disable-btn', function(a) {
            a.stopPropagation();
            a.preventDefault();
            var id = $(this).data('id');
            var isDisable = Number(!$(this).data('isdisable'));
            if (Business.verifyRight('COMPANYACCOUNT_DISABLE')) {
                $.dialog.confirm('改变状态将影响部分已发生未完结业务，确定继续？', function() {
                    id && Public.ajaxPost('/Base/CompanyAccount/disable', {
                        id: id,
                        isDisable: isDisable
                    }, function(res) {
                        if (res && 200 == res.status) {
                            $.dialog.tips('状态设置成功！');
                            $('#grid').jqGrid('setCell', id, 'isDisable', isDisable);
                            parent.frames['money-company'] && parent.frames['money-company'].$('#search').trigger('click');
                        } else {
                            $.dialog.tips('状态设置失败！' + res.msg, 2);
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
        parent.frames['money-company'] && parent.frames['money-company'].$('#search').trigger('click');
    }
};
$(function() {
    THISPAGE.init();
});
