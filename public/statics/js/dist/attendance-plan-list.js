var defaultPage = Public.getDefaultPage();
var THISPAGE = {
    init: function() {
        this.initGrid();
        this.initEvent();
    },
    initGrid: function() {
        var a = Public.setGrid();
        $('#grid').jqGrid({
            url: '/Base/AttendancePlan/list',
            datatype: 'json',
            colModel: [{
                name: 'year',
                label: '<span class="ml10">年份</span>',
                classes: 'ti10',
                width: 80,
                fixed: !0,
                formatter: function(b, c, d) {
                    return b + '年';
                }
            }, {
                name: 'm1',
                label: '1月应出勤',
                width: 62,
                fixed: !0,
                formatter: function(b, c, d) {
                    return b + '天';
                }
            }, {
                name: 'm2',
                label: '2月应出勤',
                width: 62,
                fixed: !0,
                formatter: function(b, c, d) {
                    return b + '天';
                }
            }, {
                name: 'm3',
                label: '3月应出勤',
                width: 62,
                fixed: !0,
                formatter: function(b, c, d) {
                    return b + '天';
                }
            }, {
                name: 'm4',
                label: '4月应出勤',
                width: 62,
                fixed: !0,
                formatter: function(b, c, d) {
                    return b + '天';
                }
            }, {
                name: 'm5',
                label: '5月应出勤',
                width: 62,
                fixed: !0,
                formatter: function(b, c, d) {
                    return b + '天';
                }
            }, {
                name: 'm6',
                label: '6月应出勤',
                width: 62,
                fixed: !0,
                formatter: function(b, c, d) {
                    return b + '天';
                }
            }, {
                name: 'm7',
                label: '7月应出勤',
                width: 62,
                fixed: !0,
                formatter: function(b, c, d) {
                    return b + '天';
                }
            }, {
                name: 'm8',
                label: '8月应出勤',
                width: 62,
                fixed: !0,
                formatter: function(b, c, d) {
                    return b + '天';
                }
            }, {
                name: 'm9',
                label: '9月应出勤',
                width: 62,
                fixed: !0,
                formatter: function(b, c, d) {
                    return b + '天';
                }
            }, {
                name: 'm10',
                label: '10月应出勤',
                width: 68,
                fixed: !0,
                formatter: function(b, c, d) {
                    return b + '天';
                }
            }, {
                name: 'm11',
                label: '11月应出勤',
                width: 68,
                fixed: !0,
                formatter: function(b, c, d) {
                    return b + '天';
                }
            }, {
                name: 'm12',
                label: '12月应出勤',
                width: 68,
                fixed: !0,
                formatter: function(b, c, d) {
                    return b + '天';
                }
            }, {
                name: 'all',
                label: '全年应出勤',
                width: 68,
                fixed: !0,
                formatter: function(b, c, d) {
                    return (d.m1 + d.m2 + d.m3 + d.m4 + d.m5 + d.m6 + d.m7 + d.m8 + d.m9 + d.m10 + d.m11 + d.m12) + '天';
                }
            }, {
                name: 'uId',
                label: 'uId',
                hidden: !0
            }, {
                name: 'uName',
                label: '最终制定人',
                width: 76,
                fixed: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray'
            }, {
                name: 'createTime',
                label: '最终制定时间',
                width: 124,
                fixed: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray',
                formatter: function(cellvalue, options, rowObject) {
                    return ('0000-00-00 00:00:00' == cellvalue || undefined == cellvalue) ? '' : cellvalue;
                }
            }, {
                name: 'description',
                label: '备注',
                width: 100,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray'
            }, {
                name: 'operate',
                label: '操作',
                width: 72.8,
                fixed: !0,
                formatter: Public.operFmatter
            }],
            cmTemplate: {sortable: !1, title: !1},
            jsonReader: {root: 'data.rows', records: 'data.records', total: 'data.total', repeatitems: !1, id: 'id'},
            autowidth: !0,
            height: a.h,
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
            loadonce: !0,
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
        $('#refresh').on('click', function() {
            $('#grid').jqGrid('setGridParam', {
                url: '/Base/AttendancePlan/list',
                datatype: 'json'
            }).trigger('reloadGrid');
        });
        $('#add').on('click', function(a) {
            a.preventDefault();
            if (Business.verifyRight('CLOCKIN_ADD')) {
                $.dialog({
                    title: '新增出勤规划',
                    content: 'url:/Uri/attendance_plan_manage',
                    data: {
                        oper: 'add',
                        callback: THISPAGE.callback
                    },
                    width: 600,
                    height: 430,
                    max: !1,
                    min: !1,
                    cache: !1,
                    lock: !0
                });
            }
        });
        $('.grid-wrap').on('click', '.ui-icon-pencil', function(a) {
            a.preventDefault();
            if (Business.verifyRight('CLOCKIN_UPDATE')) {
                var id = $(this).parent().data('id');
                $.dialog({
                    title: '编辑出勤规划',
                    content: 'url:/Uri/attendance_plan_manage',
                    data: {
                        oper: 'edit',
                        rowId: id,
                        callback: THISPAGE.callback
                    },
                    width: 600,
                    height: 430,
                    max: !1,
                    min: !1,
                    cache: !1,
                    lock: !0
                });
            }
        });
        $('.grid-wrap').on('click', '.ui-icon-trash', function(a) {
            a.preventDefault();
            if (Business.verifyRight('SYS_ADMIN')) {
                var id = $(this).parent().data('id');
                id && $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                    Public.ajaxPost('/Base/AttendancePlan/delete', {
                        id: id
                    }, function(res) {
                        if (res && 200 == res.status) {
                            $.dialog.tips('删除成功！');
                            $('#grid').jqGrid('delRowData', id);
                            parent.frames['personnel-attendancePlan' + id] && parent.tab.removeTabItem('personnel-attendancePlan' + id);
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
