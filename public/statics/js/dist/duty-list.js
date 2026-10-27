var urlParam = Public.urlParam();
var defaultPage = Public.getDefaultPage();
var queryConditions = {
    level: -1,
    isDisable: urlParam.unDone ? 0 : -1,
    beginDate: '',
    endDate: urlParam.endDate || '',
    skey: '',
    unDone: urlParam.unDone || 0
};
var statusCombo1 = $('#combo1').combo({
    data: [
        {id: -1, name: '所有紧急度'},
        {id: 0, name: '不急'},
        {id: 1, name: '急'},
        {id: 2, name: '很急'}
    ],
    value: 'id',
    text: 'name',
    defaultSelected: 0,
    width: 90,
    callback: {
        onListClick: function() {
            $('#search').trigger('click');
        }
    }
}).getCombo();
var statusCombo2 = $('#combo2').combo({
    data: [
        {id: -1, name: '所有状态'},
        {id: 0, name: '未处理'},
        {id: 1, name: '已处理'}
    ],
    value: 'id',
    text: 'name',
    defaultSelected: urlParam.unDone ? 1 : 0,
    width: 78,
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
            url: '/Base/Duty/list',
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
                name: 'targetName',
                label: '转交对象',
                width: 66,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'level',
                label: '紧急',
                width: 44,
                fixed: !0,
                sortable: !0,
                formatter: function(a, b, c) {
                    return a == 0 ? '不急' : a == 1 ? '<span class="blue">急</span>' : '<span class="red">很急</span>';
                }
            }, {
                name: 'lastDate',
                label: '处理限期',
                width: 80,
                fixed: !0,
                sortable: !0
            }, {
                name: 'content',
                label: '工作内容',
                width: 200,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'beginName',
                label: '发起人',
                width: 54,
                fixed: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray'
            }, {
                name: 'beginDate',
                label: '发起时间',
                width: 124,
                fixed: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray'
            }, {
                name: 'endName',
                label: '处理人',
                width: 54,
                fixed: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray'
            }, {
                name: 'endDate',
                label: '处理时间',
                width: 124,
                fixed: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray',
                formatter: function(a, b, c) {
                    return ('0000-00-00 00:00:00' == a || undefined == a) ? '' : a;
                }
            }, {
                name: 'isDisable',
                label: '状态',
                width: 58,
                fixed: !0,
                sortable: !0,
                align: 'center',
                formatter: function(a, b, c) {
                    var d = 0 == a ? '未处理' : '已处理';
                    var e = 0 == a ? '点击设置为 已处理' : '点击设置为 未处理';
                    var f = 0 == a ? 'hover-red' : 'hover-green';
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
            queryConditions.level = statusCombo1 ? statusCombo1.getValue() : -1;
            queryConditions.isDisable = statusCombo2 ? statusCombo2.getValue() : -1;
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
            if (Business.verifyRight('DUTY_ADD')) {
                $.dialog({
                    title: '新增工作转交',
                    content: 'url:/Uri/duty_manage',
                    data: {
                        oper: 'add',
                        callback: THISPAGE.callback
                    },
                    width: 1250,
                    height: 400,
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
                title: '编辑工作转交',
                content: 'url:/Uri/duty_manage',
                data: {
                    oper: 'edit',
                    rowId: id,
                    callback: THISPAGE.callback
                },
                width: 1250,
                height: 400,
                max: !1,
                min: !1,
                lock: !0,
                cache: !1
            });
        });
        $('.grid-wrap').on('click', '.ui-icon-trash', function(a) {
            a.preventDefault();
            if (Business.verifyRight('DUTY_DELETE')) {
                var id = $(this).parent().data('id');
                id && $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                    Public.ajaxPost('/Base/Duty/delete', {
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
        $('.grid-wrap').on('click', '.disable-btn', function(a) {
            a.stopPropagation();
            a.preventDefault();
            if (Business.verifyRight('DUTY_UPDATE')) {
                var id = $(this).data('id');
                var isDisable = Number(!$(this).data('isdisable'));
                var rowData = $('#grid').jqGrid('getRowData', id);
                id && rowData && Public.ajaxPost('/Base/Duty/disable', {
                    id: id,
                    isDisable: isDisable,
                    orderNo: rowData.orderNo,
                    endDate: (new Date).format('yyyy-MM-dd hh:mm:ss')
                }, function(res) {
                    if (res && 200 == res.status) {
                        $.dialog.tips('状态标记成功！');
                        $('#grid').jqGrid('setCell', id, 'isDisable', isDisable);
                        $('#grid').jqGrid('setCell', id, 'endName', defaultPage.SYSTEM.uName);
                        $('#grid').jqGrid('setCell', id, 'endDate', (new Date).format('yyyy-MM-dd hh:mm:ss'));
                    } else {
                        $.dialog.tips('状态标记失败！' + res.msg, 2);
                    }
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
