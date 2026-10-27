var urlParam = Public.urlParam();
var defaultPage = Public.getDefaultPage();
var queryConditions = {};
var THISPAGE = {
    init: function() {
        THISPAGE.initGrid();
        THISPAGE.initEvent();
    },
    initGrid: function() {
        var a = Public.setGrid();
        $('#grid').jqGrid({
            url: '/Base/ProduceGroup/list',
            postData: queryConditions,
            datatype: 'json',
            colModel: [{
                name: 'id',
                label: '<span class="ml10">ID</span>',
                width: 60,
                fixed: !0,
                classes: 'ti10 gray'
            }, {
                name: 'name',
                label: '组名',
                width: 80,
                fixed: !0
            }, {
                name: 'type',
                label: '组别',
                width: 80,
                fixed: !0
            }, {
                name: 'members',
                label: '组员',
                width: 200,
                fixed: !0,
                title: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'CoCNo',
                label: '合格证编号',
                width: 80,
                fixed: !0
            }, {
                name: 'description',
                label: '备注',
                width: 100,
                title: !0,
                classes: 'ui-ellipsis'
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
            sortorder: 'DESC',
            pager: '#page',
            page: 1,
            rowNum: 100,
            rowList: [100, 200, 500],
            viewrecords: !0,
            loadComplete: function(data) {
                data.status == -1 && $.dialog.tips(data.msg, 2);
            },
            loadError: function(a, b) {
                $.dialog.tips('Type: ' + b + '; Response: ' + a.status + ' ' + a.statusText, 2);
            },
            ondblClickRow: function(a) {
                $('#' + a).find('.ui-icon-pencil').trigger('click');
            }
        });
    },
    initEvent: function() {
        $('#add').on('click', function(a) {
            a.preventDefault();
            if (Business.verifyRight('SYS_ADVANCE')) {
                $.dialog({
                    title: '新增生产分组',
                    content: 'url:/Uri/produceGroup_manage',
                    data: {
                        oper: 'add',
                        callback: THISPAGE.callback
                    },
                    width: 600,
                    height: 400,
                    max: !1,
                    min: !1,
                    cache: !1,
                    lock: !0
                });
            }
        });
        $('#refresh').on('click', function() {
            window.location.reload();
        });
        $('#grid').on('click', '.ui-icon-pencil', function(a) {
            a.preventDefault();
            if (Business.verifyRight('SYS_ADVANCE')) {
                var id = $(this).parent().data('id');
                $.dialog({
                    title: '编辑生产分组',
                    content: 'url:/Uri/produceGroup_manage',
                    data: {
                        oper: 'edit',
                        rowId: id,
                        callback: THISPAGE.callback
                    },
                    width: 600,
                    height: 400,
                    max: !1,
                    min: !1,
                    cache: !1,
                    lock: !0
                });
            }
        });
        $('#grid').on('click', '.ui-icon-trash', function(a) {
            a.preventDefault();
            if (Business.verifyRight('SYS_ADVANCE')) {
                var id = $(this).parent().data('id');
                id && $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                    Public.ajaxPost('/Base/ProduceGroup/delete', {
                        id: id
                    }, function(res) {
                        if (res && 200 == res.status) {
                            $('#grid').jqGrid('delRowData', id);
                            $.dialog.tips('删除成功！');
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
        $('#grid').jqGrid('setGridParam', {page: 1, postData: queryConditions}).trigger('reloadGrid');
        c && (d == 1 ? c.api.close() : c.THISPAGE.resetForm())
    }
};
$(function() {
    THISPAGE.init();
});
