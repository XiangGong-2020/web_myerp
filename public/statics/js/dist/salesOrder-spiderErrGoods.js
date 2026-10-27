var urlParam = Public.urlParam();
var defaultPage = Public.getDefaultPage();
var queryConditions = {
    beginDate: '',
    endDate: urlParam.endDate ? (urlParam.endDate + ' 23:59:59') : '',
    skey: '',
    unDone: urlParam.unDone || 0
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
            url: '/Base/SalesOrderSpiderErrGoods/list',
            postData: queryConditions,
            datatype: 'json',
            colModel: [{
                name: 'id',
                label: '<span class="ml10">ID</span>',
                width: 60,
                fixed: !0,
                classes: 'ti10 gray'
            }, {
                name: 'createTime',
                label: '发现时间',
                width: 124,
                fixed: !0,
                classes: 'gray'
            }, {
                name: 'info',
                label: '异常详情',
                width: 100,
                title: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'doneName',
                label: '上次处理人',
                width: 70,
                fixed: !0,
                classes: 'ble gray'
            }, {
                name: 'doneTime',
                label: '上次处理时间',
                width: 130,
                fixed: !0,
                classes: 'gray'
            }, {
                name: 'status',
                label: '状态',
                width: 70,
                fixed: !0,
                formatter: function(a, b, c) {
                    var d = 1 == a ? '已处理' : '未处理';
                    var e = 1 == a ? '点击设置为 未处理' : '点击设置为 已处理';
                    var f = 1 == a ? 'hover-green' : 'hover-red';
                    return '<span class="status-btn fl pl10 pr10 h28 lh28 b-r-3 ' + f + '" data-status="' + a + '" data-id="' + c.id + '" title="' + e + '">' + d + '</span>';
                }
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
                }, 300)
            }
        });
        $('#search').on('click', function() {
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
        $('.grid-wrap').on('click', '.status-btn', function(a) {
            a.preventDefault();
            if (Business.verifyRight('SA_SPIDER_ERR_ADVANCE')) {
                var id = Number($(this).data('id'));
                var status = Number(!$(this).data('status'));
                id && Public.ajaxPost('/Base/SalesOrderSpiderErrGoods/status', {
                    id: id,
                    status: status
                }, function(res) {
                    if (res && 200 == res.status) {
                        $('#grid').jqGrid('setRowData', id, res.data);
                        $.dialog.tips('处理状态标记成功！');
                    } else {
                        $.dialog.tips('处理状态标记失败！' + res.msg, 2);
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
    }
};
$(function() {
    THISPAGE.init();
});
