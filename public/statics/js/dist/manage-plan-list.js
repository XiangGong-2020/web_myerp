var defaultPage = Public.getDefaultPage();
var queryConditions = {
    uId: defaultPage.SYSTEM.uId,
    type: -1,
    status: -1,
    beginDate: '',
    endDate: '',
    skey: ''
};
var userCombo;
var typeCombo;
var statusCombo;
var THISPAGE = {
    init: function() {
        THISPAGE.initDom();
        THISPAGE.initCombo();
        THISPAGE.initGrid();
        THISPAGE.initEvent();
    },
    initDom: function() {
        $('#userCombo').data('defItem', ['id', defaultPage.SYSTEM.uId]);
        $('#beginDate').val(defaultPage.SYSTEM.beginDate);
        $('#endDate').val(defaultPage.SYSTEM.endDate);
    },
    initCombo: function() {
        userCombo = $('#userCombo').combo({
            data: '/Base/User/queryAll?normal=yes&member=yes',
            ajaxOptions: {
                formatData: function(a) {
                    return a.data.rows.unshift({
                        id: defaultPage.SYSTEM.uId,
                        name: defaultPage.SYSTEM.uName
                    }), a.data.rows;
                }
            },
            value: 'id',
            text: 'name',
            defaultSelected: ['id', defaultPage.SYSTEM.uId],
            width: 90,
            listHeight: 500,
            trigger: $.inArray('SYS_ADMIN', defaultPage.SYSTEM.rights) !== -1 ? !0 : !1,
            callback: {
                onListClick: function() {
                    $('#search').trigger('click');
                }
            }
        }).getCombo();
        typeCombo = $('#typeCombo').combo({
            data: [
                {id: -1, name: '所有类别'},
                {id: 0, name: '日计划'},
                {id: 1, name: '周计划'},
                {id: 2, name: '月计划'},
                {id: 3, name: '年计划'}
            ],
            value: 'id',
            text: 'name',
            defaultSelected: 0,
            width: 78,
            callback: {
                onListClick: function() {
                    $('#search').trigger('click');
                }
            }
        }).getCombo();
        statusCombo = $('#statusCombo').combo({
            data: [
                {id: -1, name: '所有状态'},
                {id: 0, name: '未完成'},
                {id: 1, name: '已完成'}
            ],
            value: 'id',
            text: 'name',
            defaultSelected: 0,
            width: 78,
            callback: {
                onListClick: function() {
                    $('#search').trigger('click');
                }
            }
        }).getCombo();
    },
    initGrid: function() {
        var a = Public.setGrid();
        $('#grid').jqGrid({
            url: '/Base/ManagePlan/list',
            datatype: 'json',
            postData: queryConditions,
            colModel: [{
                name: 'type',
                label: '<span class="ml10">类别</span>',
                width: 52,
                fixed: !0,
                sortable: !0,
                classes: 'ti10',
                formatter: function(a, b, c) {
                    var d = '';
                    switch (Number(a)) {
                        case 0:
                            d = '<span class="p5 white bg-green b-r-3">日</span>';
                            break;
                        case 1:
                            d = '<span class="p5 white bg-teal b-r-3">周</span>';
                            break;
                        case 2:
                            d = '<span class="p5 white bg-aqua b-r-3">月</span>';
                            break;
                        case 3:
                            d = '<span class="p5 white bg-gold b-r-3">年</span>';
                            break;
                        default:
                            d = '<span class="p5 white bg-dark b-r-3">?</span>';
                            break;
                    }
                    return d;
                }
            }, {
                name: 'id',
                label: 'ID',
                width: 50,
                fixed: !0,
                sortable: !0,
                classes: 'gray'
            }, {
                name: 'uId',
                label: 'uId',
                hidden: !0
            }, {
                name: 'uName',
                label: '发布人',
                width: 54,
                fixed: !0,
                sortable: !0
            }, {
                name: 'date',
                label: '发布日期',
                width: 80,
                fixed: !0,
                sortable: !0
            }, {
                name: 'target',
                label: '计划目标',
                width: 100,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: '',
                label: '<span class="f12 iconfont icon-shuangyoujiantou silver"></span>',
                width: 30,
                fixed: !0,
                formatter: function(a, b, c) {
                    return '<span class="f12 iconfont icon-shuangyoujiantou silver"></span>';
                }
            }, {
                name: 'summary',
                label: '计划总结',
                width: 100,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: '',
                label: '自动评估',
                width: 66,
                fixed: !0,
                sortable: !0,
                formatter: function(a, b, c) {
                    if (c.summaryTime != '0000-00-00 00:00:00' && c.summaryTime < '2023-12-22 00:00:00') {
                        return '<span class="silver">过往不评</span>';
                    } else if (c.target.length < 10) {
                        return '<span class="red">目标粗略</span>';
                    } else if (c.summary.length > 0 && c.summary.length < 10) {
                        return '<span class="red">总结粗略</span>';
                    } else if (
                        (c.type == 0 && c.summaryTime != '0000-00-00 00:00:00' && (new Date(c.summaryTime).getTime() - new Date(c.createTime).getTime()) > 43200000) ||
                        (c.type == 1 && c.summaryTime != '0000-00-00 00:00:00' && (new Date(c.summaryTime).getTime() - new Date(c.createTime).getTime()) > 604800000) ||
                        (c.type == 2 && c.summaryTime != '0000-00-00 00:00:00' && (new Date(c.summaryTime).getTime() - new Date(c.createTime).getTime()) > 2678400000) ||
                        (c.type == 3 && c.summaryTime != '0000-00-00 00:00:00' && (new Date(c.summaryTime).getTime() - new Date(c.createTime).getTime()) > 31536000000) ||
                        (c.type == 0 && c.summaryTime == '0000-00-00 00:00:00' && (new Date().getTime() - new Date(c.createTime).getTime()) > 43200000) ||
                        (c.type == 1 && c.summaryTime == '0000-00-00 00:00:00' && (new Date().getTime() - new Date(c.createTime).getTime()) > 604800000) ||
                        (c.type == 2 && c.summaryTime == '0000-00-00 00:00:00' && (new Date().getTime() - new Date(c.createTime).getTime()) > 2678400000) ||
                        (c.type == 3 && c.summaryTime == '0000-00-00 00:00:00' && (new Date().getTime() - new Date(c.createTime).getTime()) > 31536000000)
                    ) {
                        return '<span class="yellow">总结超时</span>';
                    } else {
                        return '<span class="silver">正常</span>';
                    }
                }
            }, {
                name: 'isDisable',
                label: '状态',
                width: 58,
                fixed: !0,
                sortable: !0,
                align: 'center',
                formatter: function(a, b, c) {
                    var d = 1 == a ? '已完成' : '未完成';
                    var e = 1 == a ? '点击设置为 未完成' : '点击设置为 已完成';
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
            queryConditions.uId = userCombo ? userCombo.getValue() : defaultPage.SYSTEM.uId;
            queryConditions.type = typeCombo ? typeCombo.getValue() : -1;
            queryConditions.status = statusCombo ? statusCombo.getValue() : -1;
            queryConditions.beginDate = $('#beginDate').val() ? $.trim($('#beginDate').val()) : '';
            queryConditions.endDate = $('#endDate').val() ? $.trim($('#endDate').val()) : '';
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
            if (Business.verifyRight('MANAGE_PLAN_ADVANCE')) {
                $.dialog({
                    title: '新增管理计划',
                    content: 'url:/Uri/manage_plan_manage',
                    data: {
                        oper: 'add',
                        callback: THISPAGE.callback
                    },
                    width: 1250,
                    height: 500,
                    max: !1,
                    min: !1,
                    lock: !0,
                    cache: !1
                });
            }
        });
        $('.grid-wrap').on('click', '.disable-btn', function(a) {
            a.preventDefault();
            if (Business.verifyRight('MANAGE_PLAN_ADVANCE')) {
                var id = $(this).data('id');
                var isDisable = Number(!$(this).data('isdisable'));
                var rowData = $('#grid').jqGrid('getRowData', id);
                if (rowData.uId == defaultPage.SYSTEM.uId) {
                    id && Public.ajaxPost('/Base/ManagePlan/disable', {
                        id: id,
                        isDisable: isDisable,
                        title: rowData.title
                    }, function(res) {
                        if (res && 200 == res.status) {
                            $.dialog.tips('完成状态标记成功！');
                            $('#grid').jqGrid('setCell', id, 'isDisable', isDisable);
                        } else {
                            $.dialog.tips('完成状态标记失败！' + res.msg, 2);
                        }
                    });
                } else {
                    $.dialog.tips('不能标记别人的管理计划！', 2);
                }
            }
        });
        $('.grid-wrap').on('click', '.ui-icon-pencil', function(a) {
            a.preventDefault();
            if (Business.verifyRight('MANAGE_PLAN_ADVANCE')) {
                var id = $(this).parent().data('id');
                var rowData = $('#grid').jqGrid('getRowData', id);
                if (rowData.uId == defaultPage.SYSTEM.uId) {
                    $.dialog({
                        title: '编辑管理计划',
                        content: 'url:/Uri/manage_plan_manage',
                        data: {
                            oper: 'edit',
                            rowId: id,
                            callback: THISPAGE.callback
                        },
                        width: 1250,
                        height: 500,
                        max: !1,
                        min: !1,
                        lock: !0,
                        cache: !1
                    });
                } else {
                    $.dialog.tips('不能编辑别人的管理计划！', 2);
                }
            }
        });
        $('.grid-wrap').on('click', '.ui-icon-trash', function(a) {
            a.preventDefault();
            if (Business.verifyRight('MANAGE_PLAN_ADVANCE')) {
                var id = $(this).parent().data('id');
                id && $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                    Public.ajaxPost('/Base/ManagePlan/delete', {
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
        'edit' == b ? ($('#grid').jqGrid('setRowData', a.id, a), c && (d == 1 ? c.api.close() : c.THISPAGE.resetForm())) : (a.isDisable = 0, $('#grid').jqGrid('addRowData', a.id, a, 'first'), c && (d == 1 ? c.api.close() : c.THISPAGE.resetForm()));
    }
};
$(function() {
    THISPAGE.init();
});
