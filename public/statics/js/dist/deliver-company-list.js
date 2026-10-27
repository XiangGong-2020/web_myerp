var defaultPage = Public.getDefaultPage();
var curRow;
var curCol;
var THISPAGE = {
    init: function() {
        THISPAGE.initDom();
        THISPAGE.initGrid();
        THISPAGE.initEvent();
    },
    initDom: function() {
    },
    initGrid: function() {
        var a = Public.setGrid();
        $('#grid').jqGrid({
            url: '/Base/DeliverCompany/list',
            datatype: 'json',
            colModel: [{
                name: 'id',
                label: '<span class="ml10">ID</span>',
                width: 60,
                fixed: !0,
                classes: 'ti10 gray'
            }, {
                name: 'name',
                label: '名称<small class="ml5">简称</small>',
                width: 100,
                fixed: !0
            }, {
                name: 'code100',
                label: '快递100查询编码',
                width: 150,
                fixed: !0
            }, {
                name: 'len',
                label: '运单规则<small class="ml5">长度</small>',
                width: 100,
                fixed: !0
            }, {
                name: 'str',
                label: '运单规则<small class="ml5">抬头</small>',
                width: 100,
                fixed: !0
            }, {
                name: 'contactId',
                label: '商家ID<small class="ml5">付款用</smal>',
                width: 100,
                fixed: !0,
                formatter: 'currency',
                formatoptions: {
                    showZero: !1,
                    decimalPlaces: 0,
                    thousandsSeparator: ""
                }
            }, {
                name: 'description',
                label: '备注',
                width: 100,
                title: !0,
                classes: 'ui-ellipsis gray'
            }, {
                name: 'operate',
                label: '操作',
                width: 72.8,
                fixed: !0,
                formatter: Public.operFmatter
            }],
            cmTemplate: {sortable: !1, title: !1},
            localReader: {root: 'rows', records: 'records', repeatitems: !1, id: 'id'},
            jsonReader: {root: 'data.rows', records: 'data.records', repeatitems: !1, id: 'id'},
            width: a.w,
            height: a.h,
            gridview: !0,
            rownumbers: !1,
            onselectrow: !1,
            shrinkToFit: !0,
            forceFit: !1,
            scroll: !0,
            viewsortcols: [true, 'vertical', true],
            sortname: 'id',
            sortorder: 'ASC',
            pager: '#page',
            page: 1,
            rowNum: 1000,
            viewrecords: !0,
            cellEdit: !1,
            cellsubmit: 'clientArray',
            triggerAdd: !1,
            loadComplete: function(data) {
                data.status == -1 && $.dialog.tips(data.msg, 2);
            },
            loadError: function(a, b) {
                $.dialog.tips('Type: ' + b + '; Response: ' + a.status + ' ' + a.statusText, 2);
            },
            gridComplete: function() {
                setTimeout(function() {
                    Public.autoGridWidth($('#grid'));
                }, 10);
            },
            ondblClickRow: function(b) {
                $('#' + b).find('.ui-icon-pencil').trigger('click');
            }
        });
    },
    initEvent: function() {
        $('#refresh').on('click', function() {
            $('#grid').jqGrid('setGridParam', {
                page: 1
            }).trigger('reloadGrid');
        });
        $('#add').on('click', function(a) {
            a.preventDefault();
            if (Business.verifyRight('DELIVERCOM_ADD')) {
                $.dialog({
                    title: '新增快递公司',
                    content: 'url:/Uri/deliver_company_manage',
                    data: {
                        oper: 'add',
                        callback: THISPAGE.callback
                    },
                    width: 600,
                    height: 350,
                    max: !1,
                    min: !1,
                    cache: !1,
                    lock: !0
                });
            }
        });
        $('.grid-wrap').on('click', '.ui-icon-pencil', function(a) {
            a.preventDefault();
            if (Business.verifyRight('DELIVERCOM_UPDATE')) {
                var id = $(this).parent().data('id');
                $.dialog({
                    title: '编辑快递公司',
                    content: 'url:/Uri/deliver_company_manage',
                    data: {
                        oper: 'edit',
                        rowId: id,
                        callback: THISPAGE.callback
                    },
                    width: 600,
                    height: 350,
                    max: !1,
                    min: !1,
                    cache: !1,
                    lock: !0
                });
            }
        });
        $('.grid-wrap').on('click', '.ui-icon-trash', function(a) {
            a.preventDefault();
            if (Business.verifyRight('DELIVERCOM_DELETE')) {
                var id = $(this).parent().data('id');
                var rowData = $('#grid').jqGrid('getRowData', id);
                id && rowData && $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                    Public.ajaxPost('/Base/DeliverCompany/delete', {
                        id: id,
                        name: rowData.name
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
