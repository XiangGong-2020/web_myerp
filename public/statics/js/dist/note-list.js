var defaultPage = Public.getDefaultPage();
var queryConditions = {
    beginDate: '',
    endDate: '',
    skey: ''
};
var THISPAGE = {
    init: function() {
        THISPAGE.initDom();
        THISPAGE.initGrid();
        THISPAGE.initEvent();
    },
    initDom: function() {
        $('#beginDate').val(defaultPage.SYSTEM.beginDate);
        $('#endDate').val(defaultPage.SYSTEM.endDate);
    },
    initGrid: function() {
        var a = Public.setGrid();
        $('#grid').jqGrid({
            url: '/Base/Note/list',
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
                name: 'uId',
                label: 'uId',
                hidden: !0
            }, {
                name: 'uName',
                label: '发布人',
                hidden: !0
            }, {
                name: 'title',
                label: '备忘标题',
                width: 100,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'createTime',
                label: '录入时间',
                width: 124,
                fixed: !0,
                sortable: !0,
                classes: 'gray'
            }, {
                name: 'isDisable',
                label: '状态',
                width: 72,
                fixed: !0,
                sortable: !0,
                align: 'center',
                formatter: function(a, b, c) {
                    var d = 1 == a ? '不再提醒' : '继续提醒';
                    var e = 1 == a ? '点击设置为 继续提醒' : '点击设置为 不再提醒';
                    var f = 1 == a ? 'hover-green' : 'hover-red';
                    return '<span class="disable-btn db h28 lh28 b-r-3 ' + f + '" title="' + e + '" data-isdisable="' + a + '" data-id="' + c.id + '">' + d + '</span>';
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
        laydate.render({
            elem: "#datePicker",
            range: ['#beginDate', '#endDate'],
            done: function() {
                setTimeout(function() {
                    $('#search').trigger('click');
                }, 300);
            }
        });
        $('#search').on('click', function(a) {
            a.preventDefault();
            queryConditions.skey = $.trim($('#skey').val()).split(/[ ,，]+/).toString();
            queryConditions.beginDate = $('#beginDate').val() ? $.trim($('#beginDate').val()) : '';
            queryConditions.endDate = $('#endDate').val() ? $.trim($('#endDate').val()) : '';
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
            $.dialog({
                title: '新增私人备忘',
                content: 'url:/Uri/note_manage',
                data: {
                    oper: 'add',
                    callback: THISPAGE.callback
                },
                width: 1250,
                height: 420,
                max: !1,
                min: !1,
                lock: !0,
                cache: !1
            });
        });
        $('.grid-wrap').on('click', '.disable-btn', function(a) {
            a.preventDefault();
            var id = $(this).data('id');
            var isDisable = Number(!$(this).data('isdisable'));
            var rowData = $('#grid').jqGrid('getRowData', id);
            if (rowData.uId == defaultPage.SYSTEM.uId) {
                id && Public.ajaxPost('/Base/Note/disable', {
                    id: id,
                    isDisable: isDisable,
                    title: rowData.title
                }, function(res) {
                    if (res && 200 == res.status) {
                        $.dialog.tips('标记成功！');
                        $('#grid').jqGrid('setCell', id, 'isDisable', isDisable);
                    } else {
                        $.dialog.tips('标记失败！' + res.msg, 2);
                    }
                });
            } else {
                $.dialog.tips('不能标记别人的私人备忘！', 2);
            }
        });
        $('.grid-wrap').on('click', '.ui-icon-pencil', function(a) {
            a.preventDefault();
            var id = $(this).parent().data('id');
            var rowData = $('#grid').jqGrid('getRowData', id);
            if (rowData.uId == defaultPage.SYSTEM.uId) {
                $.dialog({
                    title: '编辑私人备忘',
                    content: 'url:/Uri/note_manage',
                    data: {
                        oper: 'edit',
                        rowId: id,
                        callback: THISPAGE.callback
                    },
                    width: 1250,
                    height: 420,
                    max: !1,
                    min: !1,
                    lock: !0,
                    cache: !1
                });
            } else {
                $.dialog.tips('不能编辑别人的私人备忘！', 2);
            }
        });
        $('.grid-wrap').on('click', '.ui-icon-trash', function(a) {
            a.preventDefault();
            var id = $(this).parent().data('id');
            id && $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                Public.ajaxPost('/Base/Note/delete', {
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
        });
        $(window).on('keydown', function(e) {
            var curKey = e.which;
            if (curKey == 13) {
                $('#beginDate, #endDate').blur();
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
