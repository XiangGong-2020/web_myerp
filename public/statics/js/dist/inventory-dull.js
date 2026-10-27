var defaultPage = Public.getDefaultPage();
var queryConditions = {
    categoryId: -1,
    monthNum: 3
};
var categoryTree = Public.categoryTree($('#category'), {
    rootTxt: '所有产品类别',
    treeSettings: {
        callback: {
            beforeClick: function(a, b) {
                $('#category').data('id', b.id).val(b.name);
                $('#search').trigger('click');
            }
        }
    }
});
var statusCombo1 = $('#combo1').combo({
    data: [
        {id: 3, name: '呆滞超过3个月的产品'},
        {id: 6, name: '呆滞超过6个月的产品'},
        {id: 12, name: '呆滞超过12个月的产品'}
    ],
    value: 'id',
    text: 'name',
    defaultSelected: 0,
    width: 154,
    callback: {
        onListClick: function() {
            $('#search').trigger('click');
        }
    }
}).getCombo();
var THISPAGE = {
    init: function() {
        this.initGrid();
        this.initEvent();
    },
    initGrid: function() {
        var a = Public.setGrid();
        $('#grid').jqGrid({
            url: '/Base/Goods/listInventoryDull',
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
                name: 'categoryName',
                label: '产品类别',
                width: 100,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'no',
                label: '产品编码',
                width: 150,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'name',
                label: '产品名称',
                width: 100,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'spec',
                label: '规格型号',
                width: 100,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'sku',
                label: '辅助属性',
                width: 100,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'brandName',
                label: '品牌',
                width: 60,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'unitName',
                label: '单位',
                width: 52,
                fixed: !0,
                sortable: !0
            }, {
                name: 'storageName',
                label: '仓库',
                width: 70,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'isDisable',
                label: '状态',
                width: 52,
                fixed: !0,
                sortable: !0,
                formatter: function(a, b, c) {
                    var d = (
                        0 == a ? '正常' : (
                            1 == a ? '将停产' : (
                                2 == a ? '已停产' : (
                                    9 == a ? '已禁用' : ''
                                )
                            )
                        )
                    );
                    var e = (
                        0 == a ? 'green' : (
                            1 == a ? 'red' : (
                                2 == a ? 'red' : (
                                    9 == a ? 'red' : ''
                                )
                            )
                        )
                    );
                    return '<span class="db h30 lh30 ' + e + '">' + d + '</span>';
                }
            }, {
                name: 'currentQty',
                label: '当前库存',
                width: 120,
                fixed: !0,
                sortable: !0,
                formatter: function(a, b, c) {
                    return Number(a);
                }
            }, {
                name: 'billDate',
                label: '最后活跃日期',
                width: 100,
                fixed: !0,
                sortable: !0,
                classes: 'red'
            }],
            cmTemplate: {sortable: !1, title: !1},
            jsonReader: {root: 'data.rows', records: 'data.records', total: 'data.total', repeatitems: !1, id: 'id'},
            width: a.w,
            height: a.h,
            altRows: !1,
            gridview: !0,
            shrinkToFit: !0,
            forceFit: !1,
            viewsortcols: [!0, 'vertical', !0],
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
        $('#search').on('click', function() {
            queryConditions.categoryId = categoryTree.getValue();
            queryConditions.monthNum = statusCombo1.getValue();
            $('#grid').jqGrid('setGridParam', {
                postData: queryConditions,
                page: 1
            }).trigger('reloadGrid');
        });
        $('#refresh').on('click', function() {
            window.location.reload();
        });
        $(window).on('keydown', function(e) {
            var curKey = e.which;
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
