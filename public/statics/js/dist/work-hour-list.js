var urlParam = Public.urlParam();
var defaultPage = Public.getDefaultPage();
var type = urlParam.type;
var queryConditions = {
    type: type,
    uName: '',
    isCheck: urlParam.isCheck || -1,
    beginDate: '',
    endDate: '',
    skey: ''
};
var combo1 = $('#combo1').combo({
    data: '/Base/User/queryAll?normal=yes',
    ajaxOptions: {
        formatData: function(a) {
            return a.data.rows.unshift({
                id: -1,
                name: '所有员工'
            }), a.data.rows;
        }
    },
    value: 'id',
    text: 'name',
    defaultSelected: 0,
    width: 78,
    listHeight: 500,
    callback: {
        onListClick: function() {
            $('#search').parent().find('.current').trigger('click');
        }
    }
}).getCombo();
var combo2 = $('#combo2').combo({
    data: [
        {id: -1, name: '所有审批'},
        {id: 0, name: '未审批'},
        {id: 1, name: '已批准'},
        {id: 2, name: '已拒绝'}
    ],
    value: 'id',
    text: 'name',
    defaultSelected: urlParam.isCheck ? ['id', parseInt(urlParam.isCheck)] : 0,
    width: 78,
    callback: {
        onListClick: function() {
            $('#search').parent().find('.current').trigger('click');
        }
    }
}).getCombo();
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
            url: '/Base/WorkHour/list',
            postData: queryConditions,
            datatype: 'json',
            colModel: [{
                name: 'id',
                label: '<span class="ml10">ID</span>',
                width: 60,
                fixed: !0,
                classes: 'ti10 gray'
            }, {
                name: 'uName',
                label: '姓名',
                width: 54,
                fixed: !0
            }, {
                name: 'workDate',
                label: '日期',
                width: 80,
                fixed: !0
            }, {
                name: 'weekday',
                label: '星期',
                width: 42,
                fixed: !0
            }, {
                name: 'workHours',
                label: type == 0 ? '补班小时' : (type == 1 ? '加班小时' : '计时小时'),
                width: 66,
                fixed: !0,
                formatter: 'number',
                formatoptions: {
                    showZero: !0,
                    decimalPlaces: 1
                }
            }, {
                name: 'description',
                label: type == 0 ? '补班工作内容' : (type == 1 ? '加班工作内容' : '计时工作内容'),
                width: 90,
                title: !0,
                classes: 'ui-ellipsis gray'
            }, {
                name: 'createTime',
                label: '申报时间',
                width: 124,
                fixed: !0,
                classes: 'ui-ellipsis gray'
            }, {
                name: 'checkName',
                label: '审批人',
                width: 54,
                fixed: !0,
                classes: 'ui-ellipsis gray'
            }, {
                name: 'checkTime',
                label: '审批时间',
                width: 124,
                fixed: !0,
                classes: 'ui-ellipsis gray',
                formatter: function(a, b, c) {
                    return a == '0000-00-00 00:00:00' ? '' : a;
                }
            }, {
                name: 'isCheck',
                label: '审批',
                width: 82,
                fixed: !0,
                sortable: !0,
                align: 'center',
                formatter: function(a, b, c) {
                    if (a == 0) {
                        return '<span class="check-btn fl db pl5 pr5 tc h28 lh28 b-r-3 hover-teal" data-ischeck="'
                            + a
                            + '" data-id="'
                            + c.id
                            + '">批准</span>'
                            + '<span class="uncheck-btn fl db ml2 pl5 pr5 tc h28 lh28 b-r-3 hover-red" data-ischeck="'
                            + a
                            + '" data-id="'
                            + c.id
                            + '">拒绝</span>';
                    } else if (a == 1) {
                        return '<span class="recheck-btn db pl5 pr5 tc h28 lh28 b-r-3 hover-green" title="点击取消批准" data-ischeck="' + a + '" data-id="' + c.id + '">已批准</span>';
                    } else if (a == 2) {
                        return '<span class="recheck-btn db pl5 pr5 tc h28 lh28 b-r-3 hover-night" title="点击取消拒绝" data-ischeck="' + a + '" data-id="' + c.id + '">已拒绝</span>';
                    }
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
                    $('#search').trigger('click').addClass('current').siblings().removeClass('current');
                }, 300);
            }
        });
        $('#search').on('click', function() {
            queryConditions.uName = combo1.getValue() == -1 ? '' : combo1.getText();
            queryConditions.isCheck = combo2.getValue();
            queryConditions.beginDate = $('#beginDate').val();
            queryConditions.endDate = $('#endDate').val();
            queryConditions.skey = $.trim($('#skey').val()).split(/[ ,，]+/).toString();
            $('#grid').jqGrid('setGridParam', {
                postData: queryConditions,
                page: 1
            }).trigger('reloadGrid');
        });
        $('#today').on('click', function() {
            $(this).addClass('current').siblings().removeClass('current');
            $('#beginDate').val((new Date).format('yyyy-MM-dd'));
            $('#endDate').val((new Date).format('yyyy-MM-dd'));
            $('#search').trigger('click');
        });
        $('#yesterday').on('click', function() {
            $(this).addClass('current').siblings().removeClass('current');
            $('#beginDate').val(Public.reduceDays((new Date).format('yyyy-MM-dd'), 1));
            $('#endDate').val(Public.reduceDays((new Date).format('yyyy-MM-dd'), 1));
            $('#search').trigger('click');
        });
        $('#curMonth').on('click', function() {
            $(this).addClass('current').siblings().removeClass('current');
            $('#beginDate').val((new Date).format('yyyy-MM-01'));
            $('#endDate').val(Public.reduceDays(Public.addMonths((new Date).format('yyyy-MM-01'), 1), 1));
            $('#search').trigger('click');
        });
        $('#lastMonth').on('click', function() {
            $(this).addClass('current').siblings().removeClass('current');
            $('#beginDate').val(Public.reduceMonths((new Date).format('yyyy-MM-01'), 1));
            $('#endDate').val(Public.reduceDays((new Date).format('yyyy-MM-01'), 1));
            $('#search').trigger('click');
        });
        $('#add').on('click', function(a) {
            a.preventDefault();
            if (Business.verifyRight('WORKHOUR_ADVANCE')) {
                $.dialog({
                    title: '新增' + (type == 0 ? '补班' : (type == 1 ? '加班' : '计时')) + '申报',
                    content: 'url:/Uri/work_hour_manage',
                    data: {
                        type: type,
                        oper: 'add',
                        callback: THISPAGE.callback
                    },
                    width: 600,
                    height: 410,
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
        $('#grid').on('click', '.check-btn', function(a) {
            a.stopPropagation();
            a.preventDefault();
            if (Business.verifyRight('WORKHOUR_ADVANCE')) {
                var id = $(this).data('id');
                id && Public.ajaxPost('/Base/WorkHour/check', {
                    id: id,
                    isCheck: 1
                }, function(res) {
                    if (res && 200 == res.status) {
                        $.dialog.tips('批准成功！');
                        $('#grid').jqGrid('setRowData', id, res.data);
                    } else {
                        $.dialog.tips('批准失败！' + res.msg, 2);
                    }
                });
            }
        });
        $('#grid').on('click', '.uncheck-btn', function(a) {
            a.stopPropagation();
            a.preventDefault();
            if (Business.verifyRight('WORKHOUR_ADVANCE')) {
                var id = $(this).data('id');
                id && Public.ajaxPost('/Base/WorkHour/check', {
                    id: id,
                    isCheck: 2
                }, function(res) {
                    if (res && 200 == res.status) {
                        $.dialog.tips('拒绝成功！');
                        $('#grid').jqGrid('setRowData', id, res.data);
                    } else {
                        $.dialog.tips('拒绝失败！' + res.msg, 2);
                    }
                });
            }
        });
        $('#grid').on('click', '.recheck-btn', function(a) {
            a.stopPropagation();
            a.preventDefault();
            if (Business.verifyRight('WORKHOUR_ADVANCE')) {
                var id = $(this).data('id');
                var isCheck = Number($(this).data('ischeck'));
                id && Public.ajaxPost('/Base/WorkHour/check', {
                    id: id,
                    isCheck: 0
                }, function(res) {
                    if (res && 200 == res.status) {
                        $.dialog.tips((isCheck == 1 ? '取消批准' : '取消拒绝') + '成功！');
                        $('#grid').jqGrid('setRowData', id, res.data);
                    } else {
                        $.dialog.tips((isCheck == 1 ? '取消批准' : '取消拒绝') + '失败！' + res.msg, 2);
                    }
                });
            }
        });
        $('#grid').on('click', '.ui-icon-pencil', function(a) {
            a.preventDefault();
            if (Business.verifyRight('WORKHOUR_ADVANCE')) {
                var id = $(this).parent().data('id');
                $.dialog({
                    title: '编辑' + (type == 0 ? '补班' : (type == 1 ? '加班' : '计时')) + '申报',
                    content: 'url:/Uri/work_hour_manage',
                    data: {
                        type: type,
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
            }
        });
        $('#grid').on('click', '.ui-icon-trash', function(a) {
            a.preventDefault();
            if (Business.verifyRight('WORKHOUR_ADVANCE')) {
                var id = $(this).parent().data('id');
                id && $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                    Public.ajaxPost('/Base/WorkHour/delete', {
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
                $('#beginDate, #endDate').blur();
                $('#search').parent().find('.current').trigger('click');
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
