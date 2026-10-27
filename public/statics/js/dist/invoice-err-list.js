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
            url: '/Base/Invoice/listErr',
            postData: queryConditions,
            datatype: 'json',
            colModel: [{
                name: 'id',
                label: '<span class="ml10">ID</span>',
                width: 60,
                fixed: !0,
                classes: 'ti10 gray'
            }, {
                name: 'uName',
                label: '发票上传人',
                width: 70,
                fixed: !0
            }, {
                name: 'billNo',
                label: '发票业务单号',
                width: 160,
                fixed: !0
            }, {
                name: 'fileName',
                label: '发票原始文件名',
                width: 200,
                fixed: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'upPath',
                label: '发票文件',
                width: 70,
                fixed: !0,
                formatter: function(c, d, e) {
                    return '<span class="invoice-view db fl pl10 pr10 h28 lh28 b-r-3 hover-white" data-uppath=' + c + '>查看</span>';
                }
            }, {
                name: 'upTime',
                label: '发票上传时间',
                width: 130,
                fixed: !0,
                classes: 'gray'
            }, {
                name: 'upErr',
                label: '发票识别异常',
                width: 100,
                title: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'status',
                label: '财务抄收标记',
                width: 90,
                fixed: !0,
                align: 'center',
                formatter: function(a, b, c) {
                    var d = 1 == a ? '不再提醒' : '提醒中';
                    var e = 1 == a ? '点击标记为 提醒中' : '点击标记为 不再提醒';
                    var f = 1 == a ? 'hover-green' : 'hover-red';
                    return '<span class="status-btn db tc h28 lh28 b-r-3 ' + f + '" data-status="' + a + '" data-id="' + c.id + '" title="' + e + '">' + d + '</span>';
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
        $('.grid-wrap').on('click', '.invoice-view', function(a) {
            a.preventDefault();
            if (Business.verifyRight('FINANCE_MANAGER')) {
                var uppath = $(this).data('uppath');
                uppath && window.open('/' + uppath);
            }
        });
        $('.grid-wrap').on('click', '.status-btn', function(a) {
            a.preventDefault();
            if (Business.verifyRight('FINANCE_MANAGER')) {
                var id = $(this).data('id');
                var status = Number(!$(this).data('status'));
                id && Public.ajaxPost('/Base/Invoice/statusErr', {
                    id: id,
                    status: status
                }, function(res) {
                    if (res && 200 == res.status) {
                        $.dialog.tips('状态标记成功！');
                        $('#grid').jqGrid('setRowData', id, res.data);
                    } else {
                        $.dialog.tips('状态标记失败！' + res.msg, 2);
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
