var urlParam = Public.urlParam();
var defaultPage = Public.getDefaultPage();
var queryConditions = {
    status1: '',
    status2: '',
    status3: '',
    status4: -1,
    status5: -1,
    status6: -1,
    beginDate: '',
    endDate: urlParam.endDate ? (urlParam.endDate + ' 23:59:59') : '',
    skey: '',
    unCheck: urlParam.isCheck || 0,
    uId: urlParam.uId || 0,
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
    data: '/Base/User/queryAll?normal=yes',
    ajaxOptions: {
        formatData: function(a) {
            return a.data.rows.unshift({
                id: -1,
                name: '所有发布人'
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
    data: '/Base/User/queryAll?normal=yes&department=生产部',
    ajaxOptions: {
        formatData: function(a) {
            return a.data.rows.unshift({
                id: -1,
                name: '所有领取人'
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
var combo4 = $('#combo4').combo({
    data: [
        {id: -1, name: '所有发布状态'},
        {id: 0, name: '未发布'},
        {id: 1, name: '已发布'}
    ],
    value: 'id',
    text: 'name',
    defaultSelected: 0,
    width: 102,
    listHeight: 500,
    callback: {
        onListClick: function() {
            $('#search').trigger('click');
        }
    }
}).getCombo();
var combo5 = $('#combo5').combo({
    data: [
        {id: -1, name: '所有领取状态'},
        {id: 0, name: '无人领取'},
        {id: 1, name: '部分领取'},
        {id: 2, name: '全部领取'}
    ],
    value: 'id',
    text: 'name',
    defaultSelected: 0,
    width: 102,
    callback: {
        onListClick: function() {
            $('#search').trigger('click');
        }
    }
}).getCombo();
var combo6 = $('#combo6').combo({
    data: [
        {id: -1, name: '所有加工状态'},
        {id: 0, name: '未加工'},
        {id: 1, name: '加工中'},
        {id: 2, name: '部分完成'},
        {id: 3, name: '全部完成'}
    ],
    value: 'id',
    text: 'name',
    defaultSelected: 0,
    width: 102,
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
            url: '/Base/ProProcessingTask/list',
            postData: queryConditions,
            datatype: 'json',
            colModel: [{
                name: 'id',
                label: '任务编号',
                width: 66,
                fixed: !0,
                sortable: !0
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
                label: '任务产量',
                width: 80,
                fixed: !0,
                sortable: !0,
                classes: 'f14'
            }, {
                name: 'lastDate',
                label: '交付限期',
                width: 130,
                fixed: !0,
                sortable: !0
            }, {
                name: 'description',
                label: '备注',
                width: 100,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray'
            }, {
                name: 'reqName',
                label: '申请人',
                width: 54,
                fixed: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray'
            }, {
                name: 'reqTime',
                label: '申请时间',
                width: 124,
                fixed: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray',
                formatter: function(cellvalue, options, rowObject) {
                    return ('0000-00-00 00:00:00' == cellvalue || undefined == cellvalue) ? '' : cellvalue;
                }
            }, {
                name: 'uName',
                label: '发布人',
                width: 54,
                fixed: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray'
            }, {
                name: 'createTime',
                label: '发布时间',
                width: 124,
                fixed: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray',
                formatter: function(cellvalue, options, rowObject) {
                    return ('0000-00-00 00:00:00' == cellvalue || undefined == cellvalue) ? '' : cellvalue;
                }
            }, {
                name: 'checkId',
                label: '发布状态',
                width: 66,
                fixed: !0,
                sortable: !0,
                align: 'center',
                formatter: function(a, b, c) {
                    var d = 0 == a ? '未发布' : '已发布';
                    var e = 0 == a ? '点击发布' : '点击撤回';
                    var f = 0 == a ? 'hover-red' : 'hover-green';
                    return '<span class="check-btn db h28 lh28 b-r-3 ' + f + '" title="' + e + '" data-checkid="' + a + '" data-id="' + c.id + '">' + d + '</span>';
                }
            }, {
                name: 'takeStatus',
                label: '领取状态',
                width: 66,
                fixed: !0,
                sortable: !0,
                formatter: function(a, b, c) {
                    var d = 0 == a ? '无人领取' : (1 == a ? '部分领取' : '全部领取');
                    var e = 0 == a ? 'hover-red' : (1 == a ? 'hover-yellow' : 'hover-green');
                    return '<span class="db h28 lh28 tc ' + e + ' b-r-3" title="点击快速查看领取情况">' + d + '</span>';
                }
            }, {
                name: 'taskStatus',
                label: '加工状态',
                width: 66,
                fixed: !0,
                sortable: !0,
                formatter: function(a, b, c) {
                    var d = 0 == a ? '未加工' : (1 == a ? '加工中' : (2 == a ? '部分完成' : '全部完成'));
                    var e = 0 == a ? 'hover-red' : (1 == a ? 'hover-yellow' : (2 == a ? 'hover-yellow' : 'hover-green'));
                    return '<span class="db h28 lh28 tc ' + e + ' b-r-3" title="点击快速查看完成情况">' + d + '</span>';
                }
            }, {
                name: 'backInfo',
                label: '遇阻反馈',
                width: 100,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis red'
            }, {
                name: 'reqId',
                label: '原申请',
                width: 54,
                fixed: !0,
                sortable: !0,
                align: 'center',
                classes: 'silver',
                formatter: function(a, b, c) {
                    return c.reqId > 0 ? ('<span class="src-btn db h28 lh28 b-r-3 hover-white" title="点击查看原申请信息" data-reqid="' + c.reqId + '">查看</span>') : '无';
                }
            }, {
                name: 'projectId',
                label: 'projectId',
                hidden: !0
            }, {
                name: 'needTest',
                label: 'needTest',
                hidden: !0
            }, {
                name: 'uId',
                label: 'uId',
                hidden: !0
            }, {
                name: 'operate',
                label: '操作',
                width: 72.8,
                fixed: !0,
                sortable: !1,
                formatter: Public.operFmatter
            }],
            cmTemplate: {sortable: !1, title: !1},
            jsonReader: {root: 'data.rows', records: 'data.records', total: 'data.total', repeatitems: !1, id: 'id'},
            width: a.w,
            height: a.h - 45,
            altRows: !1,
            gridview: !0,
            multiselect: !1,
            shrinkToFit: !0,
            forceFit: !1,
            rownumbers: !1,
            viewsortcols: [true, 'vertical', true],
            sortname: 'id',
            sortorder: 'DESC',
            pager: '#page',
            page: 1,
            rowNum: 100,
            rowList: [100, 200, 500],
            viewrecords: !0,
            footerrow: !0,
            userDataOnFooter: !0,
            subGrid: !0,
            subGridWidth: 30,
            subGridOptions: {
                //iconHtml: '<span class="fl f12 lh22 black">查看领取情况</span>',
                expandOnLoad: !1
            },
            subGridRowExpanded: function(subgrid_id, row_id) {
                var subgrid_table_id = subgrid_id + '_t';
                $('#' + subgrid_id).html('<div class="grid-wrap"><table id="' + subgrid_table_id + '"></table></div>');
                $('#' + subgrid_table_id).jqGrid({
                    url: '/Base/ProProcessingTask/get_list',
                    postData: {id: row_id},
                    datatype: 'json',
                    colModel: [{
                        name: 'uName',
                        label: '<span class="ml10">领取人</span>',
                        width: 66,
                        fixed: !0,
                        title: !1,
                        sortable: !0,
                        classes: 'ti10'
                    }, {
                        name: 'isTest',
                        label: '领取工序',
                        width: 70,
                        fixed: !0,
                        title: !1,
                        sortable: !0,
                        formatter: function(a, b, c) {
                            return a == 1 ? '<span class="green">测试工序</span>' : '加工工序';
                        }
                    }, {
                        name: 'getQty',
                        label: '领取产量',
                        width: 70,
                        fixed: !0,
                        title: !1,
                        sortable: !0
                    }, {
                        name: 'createTime',
                        label: '领取时间',
                        width: 140,
                        fixed: !0,
                        title: !1,
                        sortable: !0,
                        classes: 'gray'
                    }, {
                        name: 'upQty',
                        label: '已交付产量',
                        width: 90,
                        fixed: !0,
                        title: !1,
                        sortable: !0
                    }, {
                        name: 'doneStatus',
                        label: '完成状态',
                        width: 140,
                        fixed: !0,
                        title: !1,
                        sortable: !0,
                        formatter: function(a, b, c) {
                            var d = 0 == a ? '未完全交付' : (1 == a ? '有交付待审核' : '已完全交付');
                            var e = 0 == a ? 'yellow' : (1 == a ? 'blue' : 'green');
                            var f = (0 == a && (new Date).format('yyyy-MM-dd hh:mm:ss') > c.lastDate) ? '<span class="db fr lh22 pl5 pr5 white bg-yellow b-r-3">已超时</span>' : '';
                            return '<span class="' + e + '">' + d + '</span>' + f;
                        }
                    }, {
                        name: 'backInfo',
                        label: '遇阻反馈',
                        width: 200,
                        fixed: !0,
                        title: !0,
                        sortable: !0,
                        classes: 'ui-ellipsis red'
                    }, {
                        name: 'transInfo',
                        label: '转让中',
                        width: 100,
                        title: !0,
                        sortable: !0,
                        classes: 'ui-ellipsis',
                        formatter: function(a, b, c) {
                            var d = '';
                            if (a) {
                                $.each(a, function(k, v) {
                                    d += (k.includes(',') ? '整组' : '单人') + '转让产量' + v.qty + '给' + k + ' ' + v.time + "\r\n";
                                });
                            }
                            return d;
                        }
                    }, {
                        name: 'transHis',
                        label: '转让历史',
                        width: 100,
                        title: !0,
                        sortable: !0,
                        classes: 'ui-ellipsis gray'
                    }, {
                        name: 'id',
                        label: 'ID',
                        width: 0,
                        hidden: !0
                    }],
                    cmTemplate: {sortable: !1, title: !1},
                    jsonReader: {root: 'data.rows', records: 'data.records', total: 'data.total', repeatitems: !1, id: 'id'},
                    width: a.w - 35,
                    height: '100%',
                    shrinkToFit: !0,
                    forceFit: !1,
                    rownumbers: !1,
                    viewsortcols: [true, 'vertical', true],
                    rowNum: 1000,
                    sortname: 'id',
                    sortorder: 'ASC',
                    gridComplete: function() {
                        setTimeout(function() {
                            Public.autoGridWidth($('#' + subgrid_table_id));
                        }, 10);
                    }
                });
            },
            loadComplete: function(data) {
                THISPAGE.calTotal(data.data);
                data.status == -1 && $.dialog.tips(data.msg, 2);
            },
            loadError: function(a, b) {
                $.dialog.tips('Type: ' + b + '; Response: ' + a.status + ' ' + a.statusText, 2);
            },
            onCellSelect: function(rowId, iCol, cellContent, event) {
                if (iCol <= 12) { // 点击领取状态单元格，自动展开|收缩subGrid
                    $('#grid tr#' + rowId + ' .ui-sgcollapsed').trigger('click');
                }
            },
            ondblClickRow: function(a) {
                $('#' + a).find('.ui-icon-pencil').trigger('click');
            }
        });
    },
    initEvent: function() {
        laydate.render({
            elem: '#datePicker',
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
            queryConditions.status3 = combo3.getValue() == -1 ? '' : combo3.getText();
            queryConditions.status4 = combo4.getValue();
            queryConditions.status5 = combo5.getValue();
            queryConditions.status6 = combo6.getValue();
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
            if (Business.verifyRight('PROTASK_ADVANCE')) {
                var b = '新增加工任务';
                var c = {
                    oper: 'add',
                    callback: THISPAGE.callback
                };
                $.dialog({
                    title: b,
                    content: 'url:/Uri/produceProcessingTask_manage',
                    data: c,
                    width: 1250,
                    height: 600,
                    max: !1,
                    min: !1,
                    cache: !1,
                    lock: !0
                });
            }
        });
        $('#grid').on('click', '.ui-icon-pencil', function(a) {
            a.preventDefault();
            if (Business.verifyRight('PROTASK_ADVANCE')) {
                var b = $(this).parent().data('id');
                var c = '编辑加工任务';
                var d = {
                    oper: 'edit',
                    rowId: b,
                    callback: THISPAGE.callback
                };
                $.dialog({
                    title: c,
                    content: 'url:/Uri/produceProcessingTask_manage',
                    data: d,
                    width: 1250,
                    height: 600,
                    max: !1,
                    min: !1,
                    cache: !1,
                    lock: !0
                });
            }
        });
        $('#grid').on('click', '.ui-icon-trash', function(a) {
            a.preventDefault();
            if (Business.verifyRight('PROTASK_ADVANCE')) {
                var id = $(this).parent().data('id');
                id && $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                    Public.ajaxPost('/Base/ProProcessingTask/delete', {
                        id: id
                    }, function(res) {
                        if (res && 200 == res.status) {
                            $.dialog.tips('删除成功！');
                            $('#grid').jqGrid('delRowData', id);
                            parent.frames['produce-produceReq'] && parent.frames['produce-produceReq'].$('#search').trigger('click');
                        } else {
                            $.dialog.tips('删除失败！' + res.msg, 2);
                        }
                    });
                });
            }
        });
        $('.grid-wrap').on('click', '.src-btn', function(a) {
            a.preventDefault();
            if (Business.verifyRight('PROREQ_QUERY')) {
                var reqId = $(this).data('reqid');
                reqId && $.dialog({
                    title: '原加工申请详情',
                    content: 'url:/Uri/produceProcessingReq_manage',
                    data: {
                        oper: 'edit',
                        rowId: reqId
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
            }
        });
        $('#grid').on('click', '.check-btn', function(a) {
            a.stopPropagation();
            a.preventDefault();
            if (Business.verifyRight('PROTASK_ADVANCE')) {
                var id = $(this).data('id');
                var checkId = Number($(this).data('checkid')) > 0 ? 0 : defaultPage.SYSTEM.uId;
                var checkName = Number($(this).data('checkid')) > 0 ? '' : defaultPage.SYSTEM.uName;
                var rowData = $('#grid').jqGrid('getRowData', id);
                id && Public.ajaxPost('/Base/ProProcessingTask/check', {
                    id: id,
                    checkId: checkId,
                    checkName: checkName,
                    title: rowData.qty + ' 套 ' + rowData.projectName
                }, function(res) {
                    if (res && 200 == res.status) {
                        $.dialog.tips((checkId > 0 ? '发布' : '撤回') + '成功！');
                        $('#grid').jqGrid('setCell', id, 'checkId', checkId),
                            $('#grid').jqGrid('setCell', id, 'checkName', checkName || '&nbsp;');
                    } else {
                        $.dialog.tips((checkId > 0 ? '发布' : '撤回') + '失败！' + res.msg, 2);
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
            var subGridWrap = $('.ui-subgrid .grid-wrap');
            if (subGridWrap && subGridWrap.length > 0) {
                $.each(subGridWrap, function(k, v) {
                    Public.autoGridWidth($('#' + v.parentNode.id + '_t'));
                });
            }
        });
    },
    calTotal: function(data) {
        $('#grid').jqGrid('footerData', 'set', {
            id: '合计：',
            qty: '<span class="fl f12 es">' + data.totalQty + '</span>'
        });
    },
    callback: function(a, b, c) {
        $('#grid').jqGrid().trigger('reloadGrid');
        c.api.close();
    }
};
$(function() {
    THISPAGE.init();
});
