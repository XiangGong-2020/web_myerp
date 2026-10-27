var urlParam = Public.urlParam();
var defaultPage = Public.getDefaultPage();
var queryConditions = {
    status1: '',
    status2: '',
    status3: -1,
    status4: -1,
    beginDate: '',
    endDate: urlParam.endDate ? (urlParam.endDate + ' 23:59:59') : '',
    skey: '',
    unDone: urlParam.unDone || 0
};
var combo1 = $('#combo1').combo({
    data: '/Base/User/queryAll?normal=yes&department=技术部&isDisable=-2',
    ajaxOptions: {
        formatData: function(a) {
            return a.data.rows.unshift({
                id: -1,
                name: '所有负责人'
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
    data: '/Base/User/queryAll?normal=yes&department=技术部&isDisable=-2',
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
    callback: {
        onListClick: function() {
            $('#search').trigger('click');
        }
    }
}).getCombo();
var combo3 = $('#combo3').combo({
    data: [
        {id: -1, name: '所有接手状态'},
        {id: 0, name: '未接手'},
        {id: 1, name: '已接手'}
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
var combo4 = $('#combo4').combo({
    data: [
        {id: -1, name: '所有完成状态'},
        {id: 0, name: '未完成'},
        {id: 1, name: '已完成'}
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
    },
    initGrid: function() {
        var a = Public.setGrid();
        $('#grid').jqGrid({
            url: '/Base/GoodsTask/list',
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
                name: 'title',
                label: '项目目标',
                width: 100,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'publishName',
                label: '发起人',
                width: 54,
                fixed: !0,
                sortable: !0,
                classes: 'gray'
            }, {
                name: 'publishDate',
                label: '发起时间',
                width: 130,
                fixed: !0,
                sortable: !0,
                classes: 'gray'
            }, {
                name: 'lastDate',
                label: '交付限期',
                width: 130,
                fixed: !0,
                sortable: !0
            }, {
                name: 'leader',
                label: '项目负责人',
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
                name: 'beginDate',
                label: '接手时间',
                width: 130,
                fixed: !0,
                sortable: !0,
                classes: 'gray',
                formatter: function(val, opt, row) {
                    return ('0000-00-00 00:00:00' == val || undefined == val) ? '' : val;
                }
            }, {
                name: 'endDate',
                label: '完成时间',
                width: 130,
                fixed: !0,
                sortable: !0,
                classes: 'gray',
                formatter: function(val, opt, row) {
                    return ('0000-00-00 00:00:00' == val || undefined == val) ? '' : val;
                }
            }, {
                name: 'goodsNo',
                label: '成品编码',
                width: 100,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'timelineStatus',
                label: '时效评估',
                width: 60,
                fixed: !0,
                sortable: !1,
                formatter: function(b, c, d) {
                    var status = '';
                    if (d.endDate > '0000-00-00 00:00:00') {
                        if (d.lastDate != '0000-00-00 00:00:00' && d.endDate > d.lastDate) {
                            status = '<span class="fl db h30 lh30 pl5 pr5 white bg-red b-r-3">超期</span>';
                        } else {
                            status = '<span class="fl db h30 lh30 pl5 pr5 white bg-green b-r-3">正常</span>';
                        }
                    } else {
                        status = '<span class="fl db h30 lh30 pl5 pr5 white bg-red b-r-3">未完成</span>';
                    }
                    return status;
                }
            }, {
                name: 'checkId',
                label: '审核',
                width: 42,
                fixed: !0,
                sortable: !0,
                align: 'center',
                formatter: function(a, b, c) {
                    var d = a == 0 ? '未审' : '已审';
                    var e = a == 0 ? '点击审核' : '点击反审核';
                    var f = a == 0 ? 'hover-red' : 'hover-green';
                    return '<span class="check-btn db h28 lh28 b-r-3 ' + f + '" title="' + e + '" data-checkid="' + a + '" data-id="' + c.id + '">' + d + '</span>';
                }
            }, {
                name: 'status',
                label: '任务状态',
                width: 188,
                fixed: !0,
                sortable: !0,
                formatter: function(a, b, c) {
                    var d = a.toString().split(','), e, f, g, h, i, j;
                    $.inArray('1', d) < 0 ? e = '<div class="s">接</div>' : e = '<div class="s o">接</div>';
                    $.inArray('2', d) < 0 ? f = '<div class="s">设</div>' : f = '<div class="s o">设</div>';
                    $.inArray('3', d) < 0 ? g = '<div class="s">录</div>' : g = '<div class="s o">录</div>';
                    $.inArray('4', d) < 0 ? h = '<div class="s">传</div>' : h = '<div class="s o">传</div>';
                    $.inArray('5', d) < 0 ? i = '<div class="s">完</div>' : i = '<div class="s o">完</div>';
                    $.inArray('6', d) < 0 ? j = '<div class="s p">申</div>' : j = '<div class="s o">申</div>';
                    return '<div class="bill-status">' + e + f + g + h + i + j + '</div>';
                }
            }, {
                name: 'srcDevReqId',
                label: '原申请',
                width: 54,
                fixed: !0,
                sortable: !0,
                align: 'center',
                classes: 'silver',
                formatter: function(a, b, c) {
                    return a > 0 ? ('<span class="src-btn db h28 lh28 b-r-3 hover-white" title="点击查看原申请信息" data-type="' + c.type + '" data-srcdevreqid="' + a + '">查看</span>') : '无';
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
            queryConditions.status1 = combo1.getValue() == -1 ? '' : combo1.getText();
            queryConditions.status2 = combo2.getValue() == -1 ? '' : combo2.getText();
            queryConditions.status3 = combo3.getValue();
            queryConditions.status4 = combo4.getValue();
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
        $('#add').on('click', function(a) {
            a.preventDefault();
            if (Business.verifyRight('GOODSTASK_ADD')) {
                var b = '新增产品开发';
                var c = {
                    oper: 'add',
                    callback: THISPAGE.callback
                };
                $.dialog({
                    title: b,
                    content: 'url:/Uri/goods_task_manage',
                    data: c,
                    width: 1250,
                    height: 680,
                    max: !1,
                    min: !1,
                    lock: !0,
                    cache: !1
                });
            }
        });
        $('.grid-wrap').on('click', '.ui-icon-pencil', function(a) {
            a.preventDefault();
            var b = $(this).parent().data('id');
            var c = '编辑产品开发';
            var d = {
                oper: 'edit',
                rowId: b,
                callback: THISPAGE.callback
            };
            $.dialog({
                title: c,
                content: 'url:/Uri/goods_task_manage',
                data: d,
                width: 1250,
                height: 680,
                max: !1,
                min: !1,
                lock: !0,
                cache: !1
            });
        });
        $('.grid-wrap').on('click', '.ui-icon-trash', function(a) {
            a.preventDefault();
            if (Business.verifyRight('GOODSTASK_DELETE')) {
                var id = $(this).parent().data('id');
                id && $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                    Public.ajaxPost('/Base/GoodsTask/delete', {
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
            if (Business.verifyRight('GOODSTASK_CHECK')) {
                var id = $(this).data('id');
                var checkId = Number($(this).data('checkid')) > 0 ? 0 : defaultPage.SYSTEM.uId;
                var rowData = $('#grid').jqGrid('getRowData', id);
                id && Public.ajaxPost('/Base/GoodsTask/check', {
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
        $('.grid-wrap').on('click', '.src-btn', function(a) {
            a.preventDefault();
            var srcDevReqId = $(this).data('srcdevreqid');
            var type = $(this).data('type');
            if (srcDevReqId) {
                if (type == 1) { // 定制申请
                    $.dialog({
                        title: '原定制申请详情',
                        content: 'url:/Uri/goods_dev_req_manage',
                        data: {
                            oper: 'edit',
                            rowId: srcDevReqId
                        },
                        width: 1250,
                        height: 700,
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
                } else {
                    // 其他申请
                }
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
