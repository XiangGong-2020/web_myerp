var defaultPage = Public.getDefaultPage();
var queryConditions = {
    outStorageId: -1,
    inStorageId: -1,
    beginDate: '',
    endDate: '',
    skey: ''
};
var THISPAGE = {
    init: function(a) {
        THISPAGE.initDom();
        THISPAGE.initGrid();
        THISPAGE.initEvent();
    },
    initDom: function() {
        $('#beginDate').val(defaultPage.SYSTEM.beginDate);
        $('#endDate').val(defaultPage.SYSTEM.endDate);
    },
    initGrid: function() {
        function a(val, opt, row) {
            var jsonLength = 0;
            for (var item in val) {
                jsonLength++;
            }
            var returnData = jsonLength > 1 ? val.join('<p class="bbe">\r\n</p>') : val;
            return returnData;
        }
        var b = Public.setGrid();
        $('#grid').jqGrid({
            url: '/Bill/InvIo/listTf',
            datatype: 'json',
            postData: queryConditions,
            width: b.w,
            height: b.h,
            colModel: [{
                name: 'billNo',
                label: '<span class="ml10">单据编号</span>',
                width: 160,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ti10'
            }, {
                name: 'billDate',
                label: '单据日期',
                width: 70,
                fixed: !0,
                sortable: !0
            }, {
                name: 'goods',
                label: '产品',
                width: 500,
                fixed: !0,
                title: !0,
                classes: 'ui-ellipsis',
                formatter: a
            }, {
                name: 'qty',
                label: '数量',
                width: 80,
                fixed: !0,
                title: !0,
                classes: 'ui-ellipsis',
                formatter: a
            }, {
                name: 'unitName',
                label: '单位',
                width: 42,
                fixed: !0,
                title: !0,
                classes: 'ui-ellipsis',
                formatter: a
            }, {
                name: 'outStorageName',
                label: '调出仓库',
                width: 66,
                fixed: !0,
                title: !0,
                classes: 'ui-ellipsis',
                formatter: a
            }, {
                name: 'inStorageName',
                label: '调入仓库',
                width: 66,
                fixed: !0,
                title: !0,
                classes: 'ui-ellipsis',
                formatter: a
            }, {
                name: 'description',
                label: '备注',
                width: 400,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'uName',
                label: '制单人',
                width: 54,
                fixed: !0,
                sortable: !0
            }, {
                name: 'operate',
                label: '操作',
                width: 72.8,
                fixed: !0,
                formatter: Public.operFmatter
            }],
            cmTemplate: {sortable: !1, title: !1},
            jsonReader: {root: 'data.rows', records: 'data.records', repeatitems: !1, total: 'data.total', id: 'id'},
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
            ondblClickRow: function(a, b, c, d) {
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
        $('#add').on('click', function(a) {
            a.preventDefault();
            if (Business.verifyRight('TF_ADD')) {
                var newTabId = (new Date).format("yyyyMMddhhmmss");
                parent.tab.addTabItem({
                    tabid: 'inventory-transfers' + newTabId,
                    text: '<i class="iconfont icon-danye f14 mr5"></i>库存调拨单（新建）',
                    url: '/Uri/inout_manage_tf?tabId=inventory-transfers' + newTabId
                });
            }
        });
        $('.grid-wrap').on('click', '.ui-icon-pencil', function(a) {
            a.preventDefault();
            var id = $(this).parent().data('id');
            parent.tab.addTabItem({
                tabid: 'inventory-transfers' + id,
                text: '<i class="iconfont icon-danye f14 mr5"></i>库存调拨单',
                url: '/Uri/inout_manage_tf?id=' + id
            });
        });
        $('.grid-wrap').on('click', '.ui-icon-trash', function(a) {
            a.preventDefault();
            if (Business.verifyRight('TF_DELETE')) {
                var id = $(this).parent().data('id');
                id && $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                    Public.ajaxGet('/Bill/InvIo/deleteTf', {
                        id: id
                    }, function(res) {
                        if (res && 200 == res.status) {
                            $.dialog.tips('删除成功！');
                            $('#grid').jqGrid('delRowData', id);
                            parent.frames['inventory-transfers' + id] && parent.tab.removeTabItem('inventory-transfers' + id);
                        } else {
                            $.dialog.tips('删除失败！' + res.msg, 2);
                        }
                    });
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
