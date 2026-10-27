var urlParam = Public.urlParam();
var defaultPage = Public.getDefaultPage();
var queryConditions = {
    status1: -1,
    status2: '',
    status3: '',
    status4: '',
    status5: -1,
    status6: -1,
    beginDate: '',
    endDate: urlParam.endDate || '',
    skey: '',
    unDone: urlParam.unDone || 0
};
var combo1 = $('#combo1').combo({
    data: [
        {id: -1, name: '所有类型'},
        {id: 0, name: '自发型'},
        {id: 1, name: '指派型'}
    ],
    value: 'id',
    text: 'name',
    width: 78,
    listHeight: 300,
    defaultSelected: 0,
    callback: {
        onListClick: function() {
            $('#search').trigger('click');
        }
    }
}).getCombo();
var combo2 = $('#combo2').combo({
    data: '/Base/User/queryAll?normal=yes&isDisable=0',
    ajaxOptions: {
        formatData: function(a) {
            return a.data.rows.unshift({
                id: -1,
                name: '所有发起人'
            }), a.data.rows;
        }
    },
    value: 'id',
    text: 'name',
    defaultSelected: 0,
    width: 90,
    listHeight: 500,
    disabled: $.inArray('SYS_ADVANCE', defaultPage.SYSTEM.rights) == -1 ? !0 : !1,
    callback: {
        onListClick: function() {
            $('#search').trigger('click');
        }
    }
}).getCombo();
var combo3 = $('#combo3').combo({
    data: '/Base/User/queryAll?normal=yes&isDisable=0',
    ajaxOptions: {
        formatData: function(a) {
            return a.data.rows.unshift({
                id: -1,
                name: '所有主导人'
            }), a.data.rows;
        }
    },
    value: 'id',
    text: 'name',
    defaultSelected: 0,
    width: 90,
    listHeight: 500,
    disabled: $.inArray('SYS_ADVANCE', defaultPage.SYSTEM.rights) == -1 ? !0 : !1,
    callback: {
        onListClick: function() {
            $('#search').trigger('click');
        }
    }
}).getCombo();
var combo4 = $('#combo4').combo({
    data: '/Base/User/queryAll?normal=yes&isDisable=0',
    ajaxOptions: {
        formatData: function(a) {
            return a.data.rows.unshift({
                id: -1,
                name: '所有参与人'
            }), a.data.rows;
        }
    },
    value: 'id',
    text: 'name',
    defaultSelected: 0,
    width: 90,
    listHeight: 500,
    disabled: $.inArray('SYS_ADVANCE', defaultPage.SYSTEM.rights) == -1 ? !0 : !1,
    callback: {
        onListClick: function() {
            $('#search').trigger('click');
        }
    }
}).getCombo();
var combo5 = $('#combo5').combo({
    data: [
        {id: -1, name: '所有完成状态'},
        {id: 0, name: '未完成'},
        {id: 1, name: '已完成'}
    ],
    value: 'id',
    text: 'name',
    width: 102,
    listHeight: 300,
    defaultSelected: urlParam.unDone ? 1 : 0,
    callback: {
        onListClick: function() {
            $('#search').trigger('click');
        }
    }
}).getCombo();
var combo6 = $('#combo6').combo({
    data: [
        {id: -1, name: '所有超时状态'},
        {id: 0, name: '未超时'},
        {id: 1, name: '已超时'}
    ],
    value: 'id',
    text: 'name',
    width: 102,
    listHeight: 300,
    defaultSelected: 0,
    callback: {
        onListClick: function() {
            $('#search').trigger('click');
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
        $('#endDate').val(urlParam.endDate || defaultPage.SYSTEM.endDate);
        if ($.inArray('SYS_ADVANCE', defaultPage.SYSTEM.rights) !== -1) {
            $('#combo2').parent().removeClass('dn');
            $('#combo3').parent().removeClass('dn');
            $('#combo4').parent().removeClass('dn');
            $('#addAssign').removeClass('dn');
        }
    },
    initGrid: function() {
        var a = Public.setGrid();
        $('#grid').jqGrid({
            url: '/Base/Task/list',
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
                name: 'type',
                label: '任务类型',
                width: 66,
                fixed: !0,
                sortable: !0,
                classes: 'gray',
                formatter: function(val, opt, row) {
                    return 0 == val ? '自发' : '<span class="black">指派</span>';
                }
            }, {
                name: 'title',
                label: '任务目标',
                width: 100,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'uName',
                label: '发起人',
                width: 54,
                fixed: !0,
                sortable: !0,
                classes: 'gray'
            }, {
                name: 'createTime',
                label: '发起时间',
                width: 130,
                fixed: !0,
                sortable: !0,
                classes: 'gray'
            }, {
                name: 'limitTime',
                label: '完成限期',
                width: 130,
                fixed: !0,
                sortable: !0,
                formatter: function(val, opt, row) {
                    return ('0000-00-00 00:00:00' == val || undefined == val) ? '' : val;
                }
            }, {
                name: 'leader',
                label: '任务主导人',
                width: 76,
                fixed: !0,
                title: !0,
                sortable: !0
            }, {
                name: 'other',
                label: '其他参与人',
                width: 120,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'doneTime',
                label: '完成时间',
                width: 130,
                fixed: !0,
                sortable: !0,
                classes: 'gray',
                formatter: function(val, opt, row) {
                    return ('0000-00-00 00:00:00' == val || undefined == val) ? '' : val;
                }
            }, {
                name: 'status',
                label: '完成状态',
                width: 66,
                fixed: !0,
                sortable: !0,
                align: 'center',
                formatter: function(a, b, c) {
                    return 0 == a ? '<span class="db h30 lh30 tc white bg-red b-r-3">未完成</span>' : '<span class="db h30 lh30 tc white bg-green b-r-3">已完成</span>';
                }
            }, {
                name: 'timelineStatus',
                label: '超时状态',
                width: 60,
                fixed: !0,
                sortable: !1,
                align: 'center'
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
            ondblClickRow: function(c) {
                $('#' + c).find('.ui-icon-pencil').trigger('click');
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
            queryConditions.status1 = combo1.getValue();
            queryConditions.status2 = combo2.getValue() == -1 ? '' : combo2.getText();
            queryConditions.status3 = combo3.getValue() == -1 ? '' : combo3.getText();
            queryConditions.status4 = combo4.getValue() == -1 ? '' : combo4.getText();
            queryConditions.status5 = combo5.getValue();
            queryConditions.status6 = combo6.getValue();
            queryConditions.beginDate = $('#beginDate').val();
            queryConditions.endDate = $('#endDate').val();
            queryConditions.skey = $.trim($('#skey').val()).split(/[ ,，]+/).toString();
            $('#grid').jqGrid('setGridParam', {
                postData: queryConditions,
                page: 1
            }).trigger('reloadGrid');
        });
        $('#refresh').on('click', function() {
            window.location.reload();
        });
        $('#addSelf').on('click', function(a) {
            a.preventDefault();
            if (Business.verifyRight('TASK_ADD')) {
                $.dialog({
                    title: '新增(自发)任务清单',
                    content: 'url:/Uri/task_manage',
                    data: {
                        oper: 'add',
                        data: {
                            type: 0
                        },
                        callback: THISPAGE.callback
                    },
                    width: 1250,
                    height: 640,
                    max: !1,
                    min: !1,
                    lock: !0,
                    cache: !1
                });
            }
        });
        $('#addAssign').on('click', function(a) {
            a.preventDefault();
            if (Business.verifyRight('SYS_ADVANCE')) {
                $.dialog({
                    title: '新增(指派)任务清单',
                    content: 'url:/Uri/task_manage',
                    data: {
                        oper: 'add',
                        data: {
                            type: 1
                        },
                        callback: THISPAGE.callback
                    },
                    width: 1250,
                    height: 640,
                    max: !1,
                    min: !1,
                    lock: !0,
                    cache: !1
                });
            }
        });
        $('.grid-wrap').on('click', '.ui-icon-pencil', function(a) {
            a.preventDefault();
            var id = $(this).parent().data('id');
            $.dialog({
                title: '编辑任务清单',
                content: 'url:/Uri/task_manage',
                data: {
                    oper: 'edit',
                    rowId: id,
                    callback: THISPAGE.callback
                },
                width: 1250,
                height: 640,
                max: !1,
                min: !1,
                lock: !0,
                cache: !1
            });
        });
        $('.grid-wrap').on('click', '.ui-icon-trash', function(a) {
            a.preventDefault();
            if (Business.verifyRight('TASK_DELETE')) {
                var id = $(this).parent().data('id');
                id && $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                    Public.ajaxPost('/Base/Task/delete', {
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
        $('.grid-wrap').on('click', '.check-btn', function(a) {
            a.stopPropagation();
            a.preventDefault();
            if (Business.verifyRight('TASK_CHECK')) {
                var id = $(this).data('id');
                var checkId = Number($(this).data('checkid')) > 0 ? 0 : defaultPage.SYSTEM.uId;
                var rowData = $('#grid').jqGrid('getRowData', id);
                id && Public.ajaxPost('/Base/Task/check', {
                    id: id,
                    checkId: checkId,
                    title: rowData.title
                }, function(res) {
                    if (res && 200 == res.status) {
                        $.dialog.tips((checkId > 0 ? '审核' : '反审核') + '成功！');
                        $('#grid').jqGrid('setCell', id, 'checkId', checkId);
                    } else {
                        $.dialog.tips((checkId > 0 ? '审核' : '反审核') + '失败！' + res.msg, 2);
                    }
                });
            }
        });
        $(window).on('keydown', function(a) {
            var curKey = a.which;
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
        'edit' == b ? ($('#grid').jqGrid('setRowData', a.id, a), c && (d == 1 ? c.api.close() : c.THISPAGE.resetForm())) : (a.checked = 0, $('#grid').jqGrid('addRowData', a.id, a, 'first'), c && (d == 1 ? c.api.close() : c.THISPAGE.resetForm()));
    }
};
$(function() {
    THISPAGE.init();
});
