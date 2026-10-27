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
        {id: 1, name: '未开发'},
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
            url: '/Base/GoodsDevReq/list',
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
                name: 'lastDate',
                label: '交付限期',
                width: 130,
                fixed: !0,
                sortable: !0
            }, {
                name: 'title',
                label: '申请标题',
                width: 200,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'content',
                label: '附加说明',
                width: 200,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'imgs',
                label: '参考图片',
                width: 74,
                fixed: !0,
                sortable: !0,
                formatter: function(cellvalue, options, rowObject) {
                    imgs = '';
                    if (cellvalue) {
                        cellvalue.forEach(element => {
                            imgs += '<a target="_blank" href="/data/upfile/goodsTask/' + element + '"><span class="fl mr2 mb2 iconfont icon-tupian f20 gray poi" title="点击查看"></span></a>';
                        });
                    }
                    return imgs;
                }
            }, {
                name: 'files',
                label: '参考资料',
                width: 66,
                fixed: !0,
                sortable: !0,
                classes: 'ui-ellipsis',
                formatter: function(cellvalue, options, rowObject) {
                    return cellvalue.length > 0 ? (cellvalue.length + ' 份') : '';
                }
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
                width: 90,
                fixed: !0,
                title: !0,
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
                width: 90,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray',
                formatter: function(cellvalue, options, rowObject) {
                    return ('0000-00-00 00:00:00' == cellvalue || undefined == cellvalue) ? '' : cellvalue;
                }
            }, {
                name: 'backInfo',
                label: '回执信息',
                width: 100,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray'
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
                name: 'srcTaskId',
                label: '开发任务',
                width: 68,
                fixed: !0,
                title: !1,
                sortable: !0,
                align: 'center',
                formatter: function(a, b, c) {
                    var d = c.description ? '已拒绝' : (0 == a ? '立即转化' : '查看进度');
                    var e = c.description ? '删除拒绝理由可以取消拒绝' : (0 == a ? '点击转化为产品开发任务' : '点击查看产品开发任务');
                    var f = c.description ? 'hover-night' : (0 == a ? 'hover-red' : 'hover-green');
                    var g = c.description ? '' : (0 == a ? 'dev-btn' : 'src-btn');
                    return (c.id > 423 || (c.id <= 423 && a > 0)) ? ('<span class="' + g + ' db h28 lh28 tc b-r-3 ' + f + '" title="' + e + '" data-id="' + c.id + '" data-checkid="' + c.checkId + '" data-srctaskid="' + a + '">' + d + '</span>') : '<span class=" fl gray">过往数据</span>';
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
            queryConditions.uId = combo1.getValue();
            queryConditions.searchType = combo2.getValue();
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
            var b = '新增定制申请';
            var c = {
                oper: 'add',
                callback: THISPAGE.callback
            };
            $.dialog({
                title: b,
                content: 'url:/Uri/goods_dev_req_manage',
                data: c,
                width: 1250,
                height: 700,
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
                title: '编辑定制申请',
                content: 'url:/Uri/goods_dev_req_manage',
                data: {
                    oper: 'edit',
                    rowId: id,
                    callback: THISPAGE.callback
                },
                width: 1250,
                height: 700,
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
                Public.ajaxPost('/Base/GoodsDevReq/delete', {
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
            if (Business.verifyRight('GOODSDEVREQ_ADVANCE')) {
                var id = $(this).data('id');
                var checkId = Number($(this).data('checkid')) > 0 ? 0 : defaultPage.SYSTEM.uId;
                var checkName = Number($(this).data('checkid')) > 0 ? "" : defaultPage.SYSTEM.uName;
                var rowData = $('#grid').jqGrid('getRowData', id);
                id && Public.ajaxPost('/Base/GoodsDevReq/check', {
                    id: id,
                    checkId: checkId,
                    checkName: checkName,
                    title: rowData.title
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
                title: '编辑定制申请',
                content: 'url:/Uri/goods_dev_req_manage',
                data: {
                    oper: 'edit',
                    rowId: id,
                    callback: THISPAGE.callback
                },
                width: 1250,
                height: 700,
                max: !1,
                min: !1,
                cache: !1,
                lock: !0
            });
        });
        $('#grid').on('click', '.dev-btn', function(a) {
            a.stopPropagation();
            a.preventDefault();
            if (Business.verifyRight('GOODSDEVREQ_ADVANCE')) {
                var checkid = $(this).data('checkid');
                if (checkid == 0) {
                    $.dialog.alert('申请尚未审批！');
                } else {
                    var id = $(this).data('id');
                    var cRowData = {};
                    Public.ajaxPost('/Base/GoodsDevReq/query', {
                        id: id
                    }, function(res) {
                        if (200 == res.status) {
                            cRowData.id = id;
                            cRowData.lastDate = res.data.lastDate;
                            cRowData.title = res.data.title;
                            cRowData.content = res.data.content;
                            cRowData.imgs = res.data.imgs;
                            cRowData.files = res.data.files;
                            cRowData.uName = res.data.uName;
                            cRowData.createTime = res.data.createTime;
                            $.each(cRowData.files, function(k, v) {
                                cRowData.files[k].upName = res.data.uName;
                                cRowData.files[k].upTime = res.data.createTime.substring(0, 10);
                            })
                            $.dialog({
                                title: '新增产品开发',
                                content: 'url:/Uri/goods_task_manage',
                                data: {
                                    oper: 'add',
                                    cRowData: cRowData,
                                    callback: THISPAGE.callback
                                },
                                width: 1250,
                                height: 680,
                                max: !1,
                                min: !1,
                                cache: !1,
                                lock: !0
                            });
                        }
                    });
                }
            }
        });
        $('#grid').on('click', '.src-btn', function(a) {
            a.preventDefault();
            var srcTaskId = $(this).data('srctaskid');
            srcTaskId && $.dialog({
                title: '产品开发详情',
                content: 'url:/Uri/goods_task_manage',
                data: {
                    oper: 'edit',
                    rowId: srcTaskId
                },
                width: 1250,
                height: 680,
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
    callback: function(a, b, c, d) {
        $('#search').trigger('click');
        c && (d == 1 ? c.api.close() : c.THISPAGE.resetForm());
        parent.frames['develop-goodsTask'] && parent.frames['develop-goodsTask'].$('#search').trigger('click');
    }
};
$(function() {
    THISPAGE.init();
});
