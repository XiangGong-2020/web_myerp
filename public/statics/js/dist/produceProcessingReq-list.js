var urlParam = Public.urlParam();
var defaultPage = Public.getDefaultPage();
var queryConditions = {
    uId: 0,
    searchType: urlParam.unDone ? 1 : 0,
    beginDate: '',
    endDate: urlParam.endDate ? (urlParam.endDate + ' 23:59:59') : '',
    skey: '',
    unDone: urlParam.unDone || 0
};
var combo1 = $('#combo1').combo({
    data: '/Base/User/queryAll?normal=yes',
    ajaxOptions: {
        formatData: function(a) {
            return a.data.rows.unshift({
                id: -1,
                name: '所有申请人'
            }), a.data.rows;
        }
    },
    value: 'id',
    text: 'name',
    defaultSelected: 0,
    width: 90,
    listHeight: 500,
    callback: {
        onListClick: function() {
            $('#search').trigger('click');
        }
    }
}).getCombo();
var combo2 = $('#combo2').combo({
    data: [
        {id: 0, name: '所有状态'},
        {id: 1, name: '未排产'},
        {id: 2, name: '已拒绝'}
    ],
    value: 'id',
    text: 'name',
    defaultSelected: urlParam.unDone ? 1 : 0,
    width: 78,
    listHeight: 500,
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
        $('#beginDate').val(defaultPage.SYSTEM.beginDate + ' 00:00:00');
        $('#endDate').val((urlParam.endDate || defaultPage.SYSTEM.endDate) + ' 23:59:59');
    },
    initGrid: function() {
        var a = Public.setGrid();
        $('#grid').jqGrid({
            url: '/Base/ProProcessingReq/list',
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
                name: 'projectId',
                label: '产品ID',
                width: 0,
                hidden: !0
            }, {
                name: 'projectType',
                label: '项目类型',
                width: 114,
                fixed: !0,
                sortable: !0,
                formatter: function(a, b, c) {
                    return a == 0 ? '开发板整体生产类' : (a == 1 ? '电路组件焊接类' : (a == 2 ? '包装组装类' : '其他加工类'));
                }
            }, {
                name: 'projectName',
                label: '项目名称',
                width: 200,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'qty',
                label: '申请产量',
                width: 66,
                fixed: !0,
                sortable: !0
            }, {
                name: 'lastDate',
                label: '交付限期',
                width: 130,
                fixed: !0,
                sortable: !0
            }, {
                name: 'content',
                label: '附加说明',
                width: 100,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'uId',
                label: 'uId',
                hidden: !0
            }, {
                name: 'uName',
                label: '申请人',
                width: 54,
                fixed: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray'
            }, {
                name: 'createTime',
                label: '申请时间',
                width: 124,
                fixed: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray',
                formatter: function(cellvalue, options, rowObject) {
                    return ('0000-00-00 00:00:00' == cellvalue || undefined == cellvalue) ? '' : cellvalue;
                }
            }, {
                name: 'checkName',
                label: '审批人',
                width: 54,
                fixed: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray'
            }, {
                name: 'checkTime',
                label: '审批时间',
                width: 124,
                fixed: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray',
                formatter: function(cellvalue, options, rowObject) {
                    return ('0000-00-00 00:00:00' == cellvalue || undefined == cellvalue) ? '' : cellvalue;
                }
            }, {
                name: 'produceName',
                label: '排产人',
                width: 54,
                fixed: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray'
            }, {
                name: 'produceTime',
                label: '排产时间',
                width: 124,
                fixed: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray',
                formatter: function(cellvalue, options, rowObject) {
                    return ('0000-00-00 00:00:00' == cellvalue || undefined == cellvalue) ? '' : cellvalue;
                }
            }, {
                name: 'description',
                label: '拒绝理由',
                width: 100,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray'
            }, {
                name: 'checkId',
                label: '审批',
                width: 82,
                fixed: !0,
                sortable: !0,
                formatter: function(a, b, c) {
                    var d = c.description ? '已拒绝' : (a == 0 ? '批准' : '已批准');
                    var e = c.description ? '删除拒绝理由可以取消拒绝' : (a == 0 ? '点击批准' : '点击反批');
                    var f = c.description ? 'hover-night' : (a == 0 ? 'hover-teal' : 'hover-green');
                    return '<span class="' + (c.description ? '' : 'check-btn ') + 'fl db pl5 pr5 h28 lh28 b-r-3 ' + f + '" title="' + e + '" data-id="' + c.id + '" data-checkid="' + a + '">' + d + '</span>' + ((a == 0 && c.description == '') ? '<span class="refuse-btn fl ml2 db pl5 pr5 h28 lh28 b-r-3 hover-red" title="点击拒绝" data-id="' + c.id + '">拒绝</span>' : '');
                }
            }, {
                name: 'produceId',
                label: '排产',
                width: 54,
                fixed: !0,
                sortable: !0,
                align: 'center',
                formatter: function(a, b, c) {
                    var d = c.description ? '已拒绝' : (0 == a ? '未排产' : '已排产');
                    var e = c.description ? '删除拒绝理由可以取消拒绝' : (0 == a ? '点击排产' : (c.taskId > 0 ? '点击查看加工任务' : ''));
                    var f = c.description ? 'hover-night' : (0 == a ? 'hover-red' : 'hover-green');
                    return '<span class="' + (c.description ? '' : (a > 0 ? 'src-btn ' : 'produce-btn ')) + 'db h28 lh28 b-r-3 ' + f + '" title="' + e + '" data-id="' + c.id + '" data-level="' + c.level + '" data-checkid="' + c.checkId + '" data-produceid="' + a + '" data-taskid="' + c.taskId + '">' + d + '</span>';
                }
            }, {
                name: 'needTest',
                label: 'needTest',
                width: 0,
                hidden: !0
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
            type: 'datetime',
            range: ['#beginDate', '#endDate'],
            done: function() {
                setTimeout(function() {
                    $('#search').trigger('click');
                }, 300);
            }
        });
        $('#search').on('click', function(a) {
            a.preventDefault();
            queryConditions.uId = combo1.getValue();
            queryConditions.searchType = combo2.getValue();
            queryConditions.beginDate = $('#beginDate').val() ? $.trim($('#beginDate').val()) : '';
            queryConditions.endDate = $('#endDate').val() ? $.trim($('#endDate').val()) : '';
            queryConditions.skey = $.trim($('#skey').val()).split(/[ ,，]+/).toString();
            $('#grid').jqGrid('setGridParam', {
                page: 1,
                postData: queryConditions
            }).trigger('reloadGrid');
        });
        $('#refresh').on('click', function() {
            window.location.reload();
        });
        $('#add').on('click', function(a) {
            a.preventDefault();
            $.dialog({
                title: '新增加工申请',
                content: 'url:/Uri/produceProcessingReq_manage',
                data: {
                    oper: 'add',
                    callback: THISPAGE.callback
                },
                width: 1250,
                height: 600,
                max: !1,
                min: !1,
                cache: !1,
                lock: !0
            });
        });
        $('#grid').on('click', '.ui-icon-pencil', function(a) {
            a.preventDefault();
            var id = $(this).parent().data('id');
            $.dialog({
                title: '编辑加工申请',
                content: 'url:/Uri/produceProcessingReq_manage',
                data: {
                    oper: 'edit',
                    rowId: id,
                    callback: THISPAGE.callback
                },
                width: 1250,
                height: 600,
                max: !1,
                min: !1,
                cache: !1,
                lock: !0
            });
        });
        $('#grid').on('click', '.ui-icon-trash', function(a) {
            a.preventDefault();
            var id = $(this).parent().data('id');
            id && $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                Public.ajaxPost('/Base/ProProcessingReq/delete', {
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
        $('#grid').on('click', '.check-btn', function(a) {
            if (Business.verifyRight('PROREQ_ADVANCE')) {
                var id = $(this).data('id');
                var checkId = Number($(this).data('checkid')) > 0 ? 0 : defaultPage.SYSTEM.uId;
                var checkName = Number($(this).data('checkid')) > 0 ? "" : defaultPage.SYSTEM.uName;
                id && Public.ajaxPost('/Base/ProProcessingReq/check', {
                    id: id,
                    checkId: Number(checkId),
                    checkName: checkName
                }, function(res) {
                    if (res && 200 == res.status) {
                        $.dialog.tips((checkId > 0 ? '审批' : '反审批') + '成功！');
                        $('#grid').jqGrid('setRowData', id, res.data);
                    } else {
                        $.dialog.tips((checkId > 0 ? '审批' : '反审批') + '失败！' + res.msg, 2);
                    }
                });
            }
        });
        $('#grid').on('click', '.refuse-btn', function(a) {
            a.preventDefault();
            var id = $(this).data('id');
            $.dialog({
                title: '编辑加工申请',
                content: 'url:/Uri/produceProcessingReq_manage',
                data: {
                    oper: 'edit',
                    rowId: id,
                    callback: THISPAGE.callback
                },
                width: 1250,
                height: 600,
                max: !1,
                min: !1,
                cache: !1,
                lock: !0
            });
        });
        $('#grid').on('click', '.produce-btn', function(a) {
            a.stopPropagation();
            a.preventDefault();
            if (Business.verifyRight('PROTASK_ADVANCE')) {
                var id = $(this).data('id');
                var level = Number($(this).data('level'));
                var checkid = $(this).data('checkid');
                var rowData = $('#grid').jqGrid('getRowData', id);
                if (checkid == 0) {
                    $.dialog.alert('申请尚未审批！');
                } else {
                    if (rowData.projectId > 0 && rowData.qty > 0) {
                        rowData.level = level;
                        $.dialog({
                            title: '新增加工任务',
                            content: 'url:/Uri/produceProcessingTask_manage',
                            data: {
                                oper: 'add',
                                cRowData: rowData,
                                callback: THISPAGE.callback
                            },
                            width: 1250,
                            height: 600,
                            max: !1,
                            min: !1,
                            cache: !1,
                            lock: !0
                        });
                    } else {
                        $.dialog.tips('排产失败！' + res.msg, 2);
                    }
                }
            }
        });
        $('#grid').on('click', '.src-btn', function(a) {
            a.preventDefault();
            var taskId = $(this).data('taskid');
            taskId && $.dialog({
                title: '加工任务详情',
                content: 'url:/Uri/produceProcessingTask_manage',
                data: {
                    oper: 'edit',
                    rowId: taskId
                },
                width: 1250,
                height: 600,
                max: !1,
                min: !1,
                cache: !1,
                lock: !0,
                disEdit: !0,
                button: [{
                    id: 'cancel',
                    name: '关闭'
                }]
            });
        });
        $(window).on('keydown', function(a) {
            var curKey = a.which;
            if (curKey == 13) {
                $('#beginDate, #endDate').blur();
                $('#search').trigger('click');
            }
        });
        $(window).on('resize', function() {
            Public.autoGridWidth($('#grid'));
        });
    },
    callback: function(a, b, c) {
        $('#search').trigger('click');
        c.api.close();
    }
};
$(function() {
    THISPAGE.init();
});
