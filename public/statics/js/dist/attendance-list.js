var defaultPage = Public.getDefaultPage();
var THISPAGE = {
    init: function() {
        this.initGrid();
        this.initEvent();
    },
    initGrid: function() {
        var a = Public.setGrid();
        $('#grid').jqGrid({
            url: '/Bill/Attendance/list',
            datatype: 'json',
            colModel: [{
                name: 'billNo',
                label: '<span class="ml10">单据编号</span>',
                classes: 'ti10',
                width: 90,
                fixed: !0
            }, {
                name: 'beginDate',
                label: '当月起始日',
                width: 90,
                fixed: !0
            }, {
                name: 'endDate',
                label: '当月结束日',
                width: 90,
                fixed: !0
            }, {
                name: 'description',
                label: '备注',
                width: 100,
                title: !0,
                classes: 'ui-ellipsis gray'
            }, {
                name: 'uName',
                label: '制单人',
                width: 54,
                fixed: !0,
                classes: 'ui-ellipsis gray'
            }, {
                name: 'createTime',
                label: '制单时间',
                width: 130,
                fixed: !0,
                classes: 'ui-ellipsis gray',
                formatter: function(a, b, c) {
                    if (c.isDraft == 1) {
                        return '<span class="db fl pl10 pr10 h28 lh28 b-r-3 hover-gray" title="目前为草稿">草稿</span>';
                    }
                    return a;
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
                url: '/Bill/Attendance/list',
                datatype: 'json'
            }).trigger('reloadGrid');
        });
        $('#createLastMonthAttendance').on('click', function(a) {
            a.preventDefault();
            if (Business.verifyRight('ATTENDANCE_ADVANCE')) {
                $('.wrapper').append('<div id="div-lock"><div class="loading">自动生成中...</div></div>');
                var endDate = Public.reduceDays((new Date).format('yyyy-MM') + '-01', 1);
                var endDatelunar = calendar.solar2lunar(endDate.substring(0, 4), endDate.substring(5, 7), endDate.substring(8, 10));
                Public.ajaxPost('/Bill/Attendance/createLastMonthAttendance', {
                    endDateWeek: endDatelunar.nWeek
                }, function(res) {
                    $('.wrapper > #div-lock').remove();
                    if (res && 200 == res.status) {
                        if (res.data.Warning) {
                            parent.$.dialog.tips('生成成功！' + (res.data.Warning || ''), 0, 10);
                        } else {
                            $.dialog.tips('生成成功！');
                        }
                        $('#refresh').trigger('click');
                    } else {
                        $.dialog.tips('生成失败！' + res.msg, 2);
                    }
                });
            }
        });
        $('#redoLastMonthAttendance').on('click', function(a) {
            a.preventDefault();
            if (Business.verifyRight('ATTENDANCE_ADVANCE')) {
                var endDate = Public.reduceDays((new Date).format('yyyy-MM') + '-01', 1);
                var endDatelunar = calendar.solar2lunar(endDate.substring(0, 4), endDate.substring(5, 7), endDate.substring(8, 10));
                $.dialog.confirm('重算将重新汇总数据并自动计算全勤，这将使手动修改的全勤失效，确定进行重算？', function() {
                    $('.wrapper').append('<div id="div-lock"><div class="loading">重新计算中...</div></div>');
                    Public.ajaxPost('/Bill/Attendance/redoLastMonthAttendance', {
                        endDateWeek: endDatelunar.nWeek
                    }, function(res) {
                        $('.wrapper > #div-lock').remove();
                        if (res && 200 == res.status) {
                            if (res.data.Warning) {
                                $.dialog.tips('重算成功！' + (res.data.Warning || ''), 0, 10);
                            } else {
                                $.dialog.tips('重算成功！');
                            }
                        } else {
                            $.dialog.tips('重算失败！' + res.msg, 2);
                        }
                    });
                });
            }
        });
        $('.grid-wrap').on('click', '.ui-icon-pencil', function(a) {
            a.preventDefault();
            if (Business.verifyRight('ATTENDANCE_ADVANCE')) {
                var id = $(this).parent().data('id');
                id && parent.tab.addTabItem({
                    tabid: 'personnel-attendance' + id,
                    text: '<i class="iconfont icon-danye f14 mr5"></i>出勤月表单',
                    url: '/Uri/attendance_manage?flag=list&id=' + id
                });
            }
        });
        $('.grid-wrap').on('click', '.ui-icon-trash', function(a) {
            a.preventDefault();
            if (Business.verifyRight('ATTENDANCE_ADVANCE')) {
                var id = $(this).parent().data('id');
                id && $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                    Public.ajaxGet('/Bill/Attendance/delete', {
                        id: id
                    }, function(res) {
                        if (res && 200 == res.status) {
                            $.dialog.tips('删除成功！');
                            $('#grid').jqGrid('delRowData', id);
                            parent.frames['personnel-attendance' + id] && parent.tab.removeTabItem('personnel-attendance' + id);
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
    }
};
$(function() {
    THISPAGE.init();
});
