var defaultPage = Public.getDefaultPage();
var queryConditions = {
    categoryId: -1,
    storageId: -1,
    skey: ''
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
var storageCombo = $('#storage').combo({
    data: '/Base/Storage/list',
    ajaxOptions: {
        formatData: function(data) {
            return data.data.rows.unshift({
                id: -1,
                name: '所有仓库'
            }), data.data.rows;
        }
    },
    value: 'id',
    text: 'name',
    defaultSelected: 0,
    width: 78,
    callback: {
        onChange: function(a) {
            $('#search').trigger('click');
        }
    }
}).getCombo();
var THISPAGE = {
    init: function() {
        THISPAGE.initGrid();
        THISPAGE.initEvent();
    },
    initGrid: function() {
        var a = Public.setGrid();
        $('#grid').jqGrid({
            url: '/Base/Goods/listInventoryQty',
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
                cellattr: function(rowId, val, rawObject, cm, rdata) {
                    if (val <= 0) {
                        return 'class="f14 red"';
                    } else {
                        return 'class="f14 green"';
                    }
                }
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
                if (data && data.status == 200) {
                    $('#currentQty_total').html(Number(data.data.currentQty_total));
                } else {
                    data.status == -1 && $.dialog.tips(data.msg, 2);
                }
            },
            loadError: function(a, b) {
                $.dialog.tips('Type: ' + b + '; Response: ' + a.status + ' ' + a.statusText, 2);
            }
        });
    },
    initEvent: function() {
        $('#search').on('click', function() {
            queryConditions.categoryId = categoryTree.getValue();
            queryConditions.storageId = storageCombo.getValue();
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
