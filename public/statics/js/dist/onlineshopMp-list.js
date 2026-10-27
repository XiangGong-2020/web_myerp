var defaultPage = Public.getDefaultPage();
var THISPAGE = {
    init: function() {
        this.initGrid();
        this.initEvent();
    },
    initGrid: function() {
        var a = Public.setGrid();
        $('#grid').jqGrid({
            url: '/Bill/OnlineshopMp/list',
            datatype: 'json',
            colModel: [{
                name: 'billNo',
                label: '<span class="ml10">单据编号</span>',
                classes: 'ti10',
                width: 90,
                fixed: !0
            }, {
                name: 'beginDate',
                label: '当月起始日',
                width: 90,
                fixed: !0
            }, {
                name: 'endDate',
                label: '当月结束日期',
                width: 100,
                fixed: !0
            }, {
                name: 'amount',
                label: '员工总业绩',
                width: 100,
                fixed: !0,
                formatter: 'currency',
                formatoptions: {
                    thousandsSeparator: '',
                    decimalPlaces: Number(defaultPage.SYSTEM.amountPlaces)
                }
            }, {
                name: 'description',
                label: '备注',
                width: 100,
                title: !0,
                classes: 'ui-ellipsis gray'
            }, {
                name: 'uName',
                label: '制单人',
                width: 54,
                fixed: !0,
                classes: 'ui-ellipsis gray'
            }, {
                name: 'createTime',
                label: '制单时间',
                width: 130,
                fixed: !0,
                align: 'center',
                classes: 'ui-ellipsis gray',
                formatter: function(a, b, c) {
                    if (c.isDraft == 1) {
                        return '<span class="db h28 lh28 b-r-3 hover-gray" title="目前为草稿 无法进行更多操作">草稿</span>';
                    }
                    return a;
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
            autowidth: !0,
            height: a.h,
            gridview: !0,
            multiselect: !1,
            multiboxonly: !0,
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
            loadonce: !0,
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
        $('#refresh').on('click', function() {
            $('#grid').jqGrid('setGridParam', {
                url: '/Bill/OnlineshopMp/list',
                datatype: 'json'
            }).trigger('reloadGrid');
        });
        $('#add').on('click', function(a) {
            a.preventDefault();
            if (Business.verifyRight('ONLINESHOPMP_ADVANCE')) {
                var newTabId = (new Date).format("yyyyMMddhhmmss");
                parent.tab.addTabItem({
                    tabid: 'sales-onlineshopMp' + newTabId,
                    text: '<i class="iconfont icon-danye f14 mr5"></i>网店月绩单（新建）',
                    url: '/Uri/onlineshopMp_manage?tabId=sales-onlineshopMp' + newTabId
                });
            }
        });
        $('.grid-wrap').on('click', '.ui-icon-pencil', function(a) {
            a.preventDefault();
            if (Business.verifyRight('ONLINESHOPMP_ADVANCE')) {
                var id = $(this).parent().data('id');
                id && parent.tab.addTabItem({
                    tabid: 'sales-onlineshopMp' + id,
                    text: '<i class="iconfont icon-danye f14 mr5"></i>网店月绩单',
                    url: '/Uri/onlineshopMp_manage?flag=list&id=' + id
                });
            }
        });
        $('.grid-wrap').on('click', '.ui-icon-trash', function(a) {
            a.preventDefault();
            if (Business.verifyRight('ONLINESHOPMP_ADVANCE')) {
                var id = $(this).parent().data('id');
                id && $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                    Public.ajaxGet('/Bill/OnlineshopMp/delete', {
                        id: id
                    }, function(res) {
                        if (res && 200 == res.status) {
                            $.dialog.tips('删除成功！');
                            $('#grid').jqGrid('delRowData', id);
                            parent.frames['sales-onlineshopMp' + id] && parent.tab.removeTabItem('sales-onlineshopMp' + id);
                        } else {
                            $.dialog.tips('删除失败！' + res.msg, 2);
                        }
                    });
                });
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
