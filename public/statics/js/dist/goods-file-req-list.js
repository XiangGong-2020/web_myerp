var urlParam = Public.urlParam();
var defaultPage = Public.getDefaultPage();
var queryConditions = {
    status1: '',
    status2: urlParam.unDone ? 1 : 0,
    beginDate: '',
    endDate: urlParam.endDate ? (urlParam.endDate + ' 23:59:59') : '',
    skey: ''
};
var statusCombo1 = $('#combo1').combo({
    data: '/Base/User/queryAll',
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
var statusCombo2 = $('#combo2').combo({
    data: [
        {id: 0, name: '所有状态'},
        {id: 1, name: '未审批'},
        {id: 2, name: '已拒绝'}
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
var typeObj = {
    goodsFileOne: '中文图纸',
    goodsFileTwo: '中文教程',
    goodsFileThree: '程序文件',
    goodsFileFour: 'PCB文件',
    goodsFileFive: '设计文件'
};
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
            url: '/Base/Goods/fileReqList',
            postData: queryConditions,
            datatype: 'json',
            colModel: [{
                name: 'id',
                hidden: !0
            }, {
                name: 'iid',
                label: '<span class="ml10">文件ID</span>',
                width: 66,
                fixed: !0,
                sortable: !0,
                classes: 'ti10'
            }, {
                name: 'type',
                width: 0,
                hidden: !0
            }, {
                name: 'typeName',
                label: '文件类型',
                width: 80,
                fixed: !0,
                formatter: function(a, b, c) {
                    return typeObj[c.type];
                }
            }, {
                name: 'no',
                label: '产品编码',
                width: 100,
                title: !0,
                fixed: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'name',
                label: '产品文件',
                width: 100,
                sortable: !0
            }, {
                name: 'uId',
                width: 0,
                hidden: !0
            }, {
                name: 'uName',
                label: '申请人',
                width: 54,
                fixed: !0,
                sortable: !0,
                classes: 'gray'
            }, {
                name: 'reqTime',
                label: '申请时间',
                width: 124,
                fixed: !0,
                sortable: !0,
                classes: 'gray'
            }, {
                name: 'checkName',
                label: '审核人',
                width: 54,
                fixed: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray'
            }, {
                name: 'checkTime',
                label: '审核时间',
                width: 124,
                fixed: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray',
                formatter: function(a, b, c) {
                    return ('0000-00-00 00:00:00' == a || undefined == a) ? '' : a;
                }
            }, {
                name: 'endTime',
                label: '下载有效期截止',
                width: 124,
                fixed: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray',
                formatter: function(a, b, c) {
                    return ('0000-00-00 00:00:00' == a || undefined == a) ? '' : a;
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
                    return '<span class="' + (c.description ? '' : 'check-btn ') + 'fl db pl5 pr5 h28 lh28 b-r-3 ' + f + '" title="' + e + '" data-type="' + c.type + '" data-id="' + c.id + '" data-checkid="' + a + '">' + d + '</span>' + ((a == 0 && c.description == '') ? '<span class="refuse-btn fl ml2 db pl5 pr5 h28 lh28 b-r-3 hover-red" title="点击拒绝" data-type="' + c.type + '" data-id="' + c.id + '">拒绝</span>' : '');
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
            sortname: 'reqTime',
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
        $('#search').on('click', function() {
            queryConditions.status1 = statusCombo1.getValue() == -1 ? '' : statusCombo1.getText();
            queryConditions.status2 = statusCombo2.getValue();
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
        $('#grid').on('click', '.ui-icon-pencil', function(a) {
            a.preventDefault();
            if (Business.verifyRight('GOODSFILEREQ_UPDATE')) {
                var id = $(this).parent().data('id');
                var rowData = $('#grid').jqGrid('getRowData', id);
                id && $.dialog({
                    title: '编辑下载申请',
                    content: 'url:/Uri/goods_file_req_manage',
                    data: {
                        oper: 'edit',
                        rowId: id,
                        type: rowData.type,
                        callback: THISPAGE.callback
                    },
                    width: 600,
                    height: 350,
                    max: !1,
                    min: !1,
                    cache: !1,
                    lock: !0
                });
            }
        });
        $('#grid').on('click', '.ui-icon-trash', function(a) {
            a.preventDefault();
            if (Business.verifyRight('GOODSFILEREQ_DELETE')) {
                var id = $(this).parent().data('id');
                id && $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                    Public.ajaxPost('/Base/Goods/fileReqDelete', {
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
        $('#grid').on('click', '.check-btn', function(a) {
            a.stopPropagation();
            a.preventDefault();
            var type = $(this).data('type');
            if (Business.verifyRight(type.toUpperCase() + '_CHECK')) {
                var id = Number($(this).data('id'));
                var checkId = Number($(this).data('checkid')) > 0 ? 0 : defaultPage.SYSTEM.uId;
                var checkName = Number($(this).data('checkid')) > 0 ? '' : defaultPage.SYSTEM.uName;
                id && Public.ajaxPost('/Base/Goods/fileReqCheck', {
                    id: id,
                    checkId: Number(checkId),
                    checkName: checkName
                }, function(res) {
                    if (res && 200 == res.status) {
                        $.dialog.tips((checkId == 0 ? '反审核' : '审核') + '成功！');
                        $('#grid').jqGrid('setRowData', id, res.data);
                    } else {
                        $.dialog.tips((checkId == 0 ? '反审核' : '审核') + '失败！' + res.msg, 2);
                    }
                });
            }
        });
        $('#grid').on('click', '.refuse-btn', function(a) {
            a.stopPropagation();
            a.preventDefault();
            var type = $(this).data('type');
            if (Business.verifyRight(type.toUpperCase() + '_CHECK')) {
                var id = $(this).data('id');
                id && $.dialog({
                    title: '拒绝' + typeObj[type] + '下载申请',
                    content: 'url:/Uri/goods_file_req_manage',
                    data: {
                        oper: 'edit',
                        rowId: id,
                        type: type,
                        callback: THISPAGE.callback
                    },
                    width: 600,
                    height: 350,
                    max: !1,
                    min: !1,
                    cache: !1,
                    lock: !0
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
        $('#grid').jqGrid('setGridParam', {
            postData: queryConditions
        }).trigger('reloadGrid');
        c && (d == 1 ? c.api.close() : c.THISPAGE.resetForm());
    }
};
$(function() {
    THISPAGE.init();
});
