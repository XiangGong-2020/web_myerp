var defaultPage = Public.getDefaultPage();
var pricePlaces = defaultPage.SYSTEM.pricePlaces;
var queryConditions = {
    skey: ''
};
var THISPAGE = {
    init: function() {
        THISPAGE.initGrid();
        THISPAGE.initEvent();
    },
    initGrid: function() {
        var a = Public.setGrid();
        $('#grid').jqGrid({
            url: '/Bill/Report/bidQuoteReference',
            postData: queryConditions,
            datatype: 'json',
            colModel: [{
                name: 'id',
                label: 'ID',
                width: 0,
                fixed: !0,
                hidden: !0
            }, {
                name: 'name',
                label: '<span class="ml10">产品名称</span>',
                width: 100,
                title: !0,
                editable: !1,
                classes: 'ti10 ui-ellipsis'
            }, {
                name: 'spec',
                label: '规格型号',
                width: 100,
                title: !0,
                editable: !1,
                classes: 'ui-ellipsis'
            }, {
                name: 'sku',
                label: '辅助属性',
                width: 100,
                title: !0,
                editable: !1,
                classes: 'ui-ellipsis'
            }, {
                name: 'brand',
                label: '品牌',
                width: 100,
                fixed: !0,
                title: !0,
                editable: !1,
                classes: 'ui-ellipsis'
            }, {
                name: 'unit',
                label: '单位',
                width: 100,
                fixed: !0,
                editable: !1,
                classes: 'ui-ellipsis brd'
            }, {
                name: 'isHasExpress',
                label: '包邮',
                width: 50,
                fixed: !0,
                editable: !1,
                formatter: function(a, b, c) {
                    return a == 1 ? '包邮' : '';
                }
            }, {
                name: 'isHasInvoice',
                label: '带票',
                width: 50,
                fixed: !0,
                editable: !1,
                formatter: function(a, b, c) {
                    return a == 1 ? '带票' : '';
                }
            }, {
                name: 'buyUrl',
                label: '参考链接1',
                width: 100,
                fixed: !0,
                title: !0,
                editable: !1,
                classes: 'ui-ellipsis',
                formatter: function(x, y, z) {
                    return (x && x.indexOf('http') === 0) ? '<a href="' + x + '" target="_blank">' + x +'</a>' : x;
                }
            }, {
                name: 'myUrl',
                label: '参考链接2',
                width: 100,
                fixed: !0,
                title: !0,
                editable: !1,
                classes: 'ui-ellipsis',
                formatter: function(x, y, z) {
                    return (x && x.indexOf('http') === 0) ? '<a href="' + x + '" target="_blank">' + x +'</a>' : x;
                }
            }, {
                name: 'billDate',
                label: '参考日期',
                width: 100,
                fixed: !0,
                editable: !1
            }, {
                name: 'price',
                label: '参考单价',
                width: 100,
                fixed: !0,
                sortable: !0,
                editable: !1,
                classes: 'f14'
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
            }
        });
    },
    initEvent: function() {
        $('#search').click(function() {
            queryConditions.skey = $.trim($('#skey').val()).split(/[ ,，]+/).toString();
            $('#grid').jqGrid('setGridParam', {
                postData: queryConditions,
                page: 1
            }).trigger('reloadGrid');
        });
        $('#refresh').on('click', function() {
            window.location.reload();
        });
        $(window).on('keydown', function(a) {
            var curKey = a.which;
            if (curKey == 13) {
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
