var urlParam = Public.urlParam();
var defaultPage = Public.getDefaultPage();
var type = urlParam.type;
var queryConditions = {
    type: type,
    beginDate: '',
    endDate: '',
    skey: ''
};
var typeObj = {
    goodsFileOne: '中文图纸',
    goodsFileTwo: '中文教程',
    goodsFileOne_EN: '外文图纸',
    goodsFileTwo_EN: '外文教程',
    goodsFileThree: '程序文件',
    goodsFileFour: 'PCB文件',
    goodsFileFive: '激光文件',
    goodsFileSix: '设计文件'
};
var THISPAGE = {
    init: function() {
        THISPAGE.initDom();
        THISPAGE.initGrid();
        THISPAGE.initEvent();
    },
    initDom: function() {
        $('#beginDate').val(defaultPage.SYSTEM.beginDate + ' 00:00:00');
        $('#endDate').val(defaultPage.SYSTEM.endDate + ' 23:59:59');
    },
    initGrid: function() {
        var a = Public.setGrid();
        $('#grid').jqGrid({
            url: '/Base/Goods/fileList',
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
                name: 'no',
                label: '产品编码',
                width: 100,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'name',
                label: '产品文件',
                width: 100,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'printSize',
                label: type == 'goodsFileFive' ? '面积' : '打印尺寸',
                width: 100,
                fixed: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'authorName',
                label: '编写人',
                width: 66,
                fixed: !0,
                sortable: !0
            }, {
                name: 'proofName',
                label: '校对人',
                width: 66,
                fixed: !0,
                sortable: !0
            }, {
                name: 'uId',
                label: 'uId',
                hidden: !0
            }, {
                name: 'uName',
                label: '首次上传',
                width: 66,
                fixed: !0,
                sortable: !0,
                classes: 'gray'
            }, {
                name: 'createTime',
                label: '首次上传时间',
                width: 124,
                fixed: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray',
                formatter: function(a, b, c) {
                    return ('0000-00-00 00:00:00' == a || undefined == a) ? '' : a;
                }
            }, {
                name: 'modifyId',
                label: 'uId',
                hidden: !0
            }, {
                name: 'modifyName',
                label: '最后编辑',
                width: 66,
                fixed: !0,
                sortable: !0,
                classes: 'gray'
            }, {
                name: 'modifyTime',
                label: '最后编辑时间',
                width: 130,
                fixed: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray',
                formatter: function(a, b, c) {
                    return ('0000-00-00 00:00:00' == a || undefined == a) ? '' : a;
                }
            }, {
                name: 'checkId',
                label: '审核操作',
                width: 175,
                fixed: !0,
                sortable: !1,
                classes: 'ui-ellipsis gray',
                formatter: function(a, b, c) {
                    var d = a == 0 ? '未审核' : '已审核';
                    var e = a == 0 ? 'hover-red' : 'hover-green';
                    var f = a == 0 ? '点击审核' : '点击反审核';
                    return '<span class="check-btn fl db pl5 pr5 h28 lh28 b-r-3 ' + e + '" title="' + f + '" data-id="' + c.id + '" data-checkid="' + a + '">' + d + '</span>' +
                        (a > 0 ? ('<span class="ml5" title="' + c.checkName + ' ' + c.checkTime + ' 审核">' + c.checkName + ' ' + c.checkTime + '</span>') : '');
                }
            }, {
                name: 'dTimes',
                label: '被下载',
                width: 54,
                fixed: !0,
                sortable: !0,
                formatter: function(a, b, c) {
                    return a + ' 次';
                }
            }, {
                name: 'download',
                label: '下载操作',
                width: 144,
                fixed: !0,
                sortable: !1,
                classes: 'ui-ellipsis gray',
                formatter: function(a, b, c) {
                    return a == 0 ? '<span class="download-btn db fl pl5 pr5 h28 lh28 b-r-3 hover-green" data-id="' + c.id + '">下载</span>' : (
                        a == 1 ? '<span class="downloadReq-btn db fl pl5 pr5 h28 lh28 b-r-3 hover-red" data-id="' + c.id + '">申请下载</span>' : (
                            a == 2 ? '<span class="db fl pl5 pr5 h30 lh30 b-r-3 white bg-yellow def">已申请 待审批</span>' : (
                                a == 3 ? '<span class="db fl pl5 pr5 h30 lh30 b-r-3 white bg-yellow def" title="拒绝理由：' + c.refuseReason + '">被拒绝</span><span class="downloadReq-btn db fl ml2 pl5 pr5 h28 lh28 b-r-3 hover-red" data-id="' + c.id + '">再次申请下载</span>' : (
                                    a == 4 ? '<span class="db fl pl5 pr5 h30 lh30 b-r-3 white bg-rgba3 def">已过期</span><span class="downloadReq-btn db fl ml2 pl5 pr5 h28 lh28 b-r-3 hover-red" data-id="' + c.id + '">再次申请下载</span>' : '无权限'
                                )
                            )
                        )
                    );
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
            type: 'datetime',
            range: ['#beginDate', '#endDate'],
            done: function() {
                setTimeout(function() {
                    $('#search').trigger('click');
                }, 300);
            }
        });
        $('#search').on('click', function() {
            queryConditions.beginDate = $('#beginDate').val();
            queryConditions.endDate = $('#endDate').val();
            queryConditions.skey = ($('#skey').val() === $('#skey')[0].defaultValue ? '' : $.trim($('#skey').val()).split(/[ ,，]+/).toString());
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
            if (Business.verifyRight(type.split('_')[0].toUpperCase() + '_ADD')) {
                $.dialog({
                    title: '新增' + typeObj[type],
                    content: 'url:/Uri/goods_file_manage',
                    data: {
                        oper: 'add',
                        type: type,
                        callback: THISPAGE.callback
                    },
                    width: 600,
                    height: 325,
                    max: !1,
                    min: !1,
                    cache: !1,
                    lock: !0
                });
            }
        });
        $('.grid-wrap').on('click', '.ui-icon-pencil', function(a) {
            a.preventDefault();
            if (Business.verifyRight(type.split('_')[0].toUpperCase() + '_UPDATE')) {
                var id = $(this).parent().data('id');
                id && $.dialog({
                    title: '编辑' + typeObj[type],
                    content: 'url:/Uri/goods_file_manage',
                    data: {
                        oper: 'edit',
                        rowId: id,
                        type: type,
                        callback: THISPAGE.callback
                    },
                    width: 600,
                    height: 325,
                    max: !1,
                    min: !1,
                    cache: !1,
                    lock: !0
                });
            }
        });
        $('.grid-wrap').on('click', '.ui-icon-trash', function(a) {
            a.preventDefault();
            if (Business.verifyRight(type.toUpperCase() + '_DELETE')) {
                var id = $(this).parent().data('id');
                id && $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                    Public.ajaxPost('/Base/Goods/fileDelete', {
                        id: id,
                        type: type
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
            var that = $(this);
            if (Business.verifyRight(type.toUpperCase() + '_CHECK')) {
                var id = $(this).data('id');
                var checkId = Number($(this).data('checkid')) > 0 ? 0 : defaultPage.SYSTEM.uId;
                var checkName = Number($(this).data('checkid')) > 0 ? "" : defaultPage.SYSTEM.uName;
                var rowData = $('#grid').jqGrid('getRowData', id);
                id && rowData && Public.ajaxPost('/Base/Goods/fileCheck', {
                    id: id,
                    checkId: checkId,
                    checkName: checkName
                }, function(res) {
                    if (res && 200 == res.status) {
                        $.dialog.tips((checkId > 0 ? '审核' : '反审核') + '成功！');
                        $('#grid').jqGrid('setCell', id, 'checkId', checkId);
                        if (checkId > 0) {
                            rowData.checkId = checkId;
                            rowData.checkName = defaultPage.SYSTEM.uName;
                            rowData.checkTime = (new Date).format('yyyy-MM-dd hh:mm:ss');
                        } else {
                            rowData.checkId = 0;
                            rowData.checkName = '';
                            rowData.checkTime = '';
                        }
                        $('#grid').jqGrid('setRowData', id, rowData);
                    } else {
                        $.dialog.tips((checkId > 0 ? '审核' : '反审核') + '失败！' + res.msg, 2);
                    }
                });
            }
        });
        $('.grid-wrap').on('click', '.download-btn', function(a) {
            a.preventDefault();
            var id = $(this).data('id');
            id && Public.ajaxPost('/Base/Goods/fileDownload', {
                id: id,
                step: 1
            }, function(res) {
                if (res && 200 == res.status) {
                    window.open('/Base/Goods/fileDownload?id=' + id + '&step=2');
                } else {
                    $.dialog.tips('下载失败！' + res.msg, 2);
                }
            });
        });
        $('.grid-wrap').on('click', '.downloadReq-btn', function(a) {
            a.preventDefault();
            if (Business.verifyRight(type.split('_')[0].toUpperCase() + '_DOWNLOAD_REQ')) {
                var id = $(this).data('id');
                id && Public.ajaxPost('/Base/Goods/fileReq', {
                    id: id
                }, function(res) {
                    if (res && 200 == res.status) {
                        $.dialog.tips('申请成功，等待审批！');
                        $('#grid').jqGrid('setCell', id, 'download', 2);
                    } else {
                        $.dialog.tips('申请失败！' + res.msg, 2);
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
        $('#grid').jqGrid('setGridParam', {
            postData: queryConditions
        }).trigger('reloadGrid');
        c && (d == 1 ? c.api.close() : c.THISPAGE.resetForm());
    }
};
$(function() {
    THISPAGE.init();
});
