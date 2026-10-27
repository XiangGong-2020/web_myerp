var urlParam = Public.urlParam();
var inout = '102101' == urlParam.transType ? '入' : '出';
var shoutui = '102101' == urlParam.transType ? '收' : '退';
var defaultPage = Public.getDefaultPage();
var queryConditions = {
    transType: '102101' === urlParam.transType ? 102101 : 102102,
    status1: '',
    status2: -1,
    beginDate: '',
    endDate: '',
    skey: ''
};
var statusCombo1 = $('#combo1').combo({
    data: '/Base/User/queryAll?normal=yes',
    ajaxOptions: {
        formatData: function(a) {
            return a.data.rows.unshift({
                id: -1,
                name: '所有制单人'
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
        {id: -1, name: '所有' + inout + '库状态'},
        {id: 0, name: '未' + inout + '库'},
        {id: 1, name: '部分' + inout + '库'},
        {id: 2, name: '全部' + inout + '库'}
    ],
    value: 'id',
    text: 'name',
    defaultSelected: 0,
    width: 102,
    callback: {
        onListClick: function() {
            $('#search').trigger('click');
        }
    }
}).getCombo();
var goodsShowAll = !1;
var THISPAGE = {
    init: function() {
        THISPAGE.initDom();
        THISPAGE.initGrid();
        THISPAGE.initEvent();
    },
    initDom: function() {
        $('#beginDate').val(defaultPage.SYSTEM.beginDate);
        $('#endDate').val(defaultPage.SYSTEM.endDate);
    },
    initGrid: function() {
        var a = Public.setGrid();
        $('#grid').jqGrid({
            url: '/Bill/InvPu/list',
            datatype: 'json',
            postData: queryConditions,
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
                label: '包含产品',
                nameExt: '<span class="goods-showall"><i class="iconfont icon-shuangxiajiantou f12 mr5"></i>展开全部</span>',
                width: 100,
                formatter: function(b, c, d) {
                    var e = (0 < b.length ? b.length : 0);
                    var f = '';
                    if (0 < e) {
                        $.each(b, function(i, v) {
                            var goodsText =
                                (v['no'] ? v['no'] + ' ' : '') +
                                v['name'] +
                                (v['spec'] ? ' ' + v['spec'] : '') +
                                (v['sku'] ? ' ' + v['sku'] : '') +
                                (v['brand'] ? ' ' + v['brand'] : '') +
                                (v['qty'] ? ' × ' + v['qty'] : '') +
                                (v['unit'] ? v['unit'] : '');
                            f += ('<div class="goods-line es" title="' + goodsText + '">' + goodsText + '</div>');
                        });
                    }
                    return (
                        '<div class="goods-total"><span class="goods-num-1">' +
                        e +
                        ' 种</span><span class="goods-num-2">' +
                        d.totalOrderQty +
                        ' 件</span><span class="goods-num-3">' +
                        d.totalQty +
                        ' 件已' + shoutui + '</span><span class="goods-showinfo iconfont icon-xiajiantou"></span></div><div class="goods-info">' +
                        f +
                        '</div>'
                    );
                }
            }, {
                name: 'description',
                label: '备注',
                width: 100,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray'
            }, {
                name: 'uName',
                label: '制单人',
                width: 54,
                fixed: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray'
            }, {
                name: 'contactName',
                label: '商家',
                width: 80,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis hover'
            }, {
                name: 'srcOrderNo',
                label: '源单',
                width: 160,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis hover',
                formatter: function(b, c, d) {
                    return b ? '<span class="srcOrderNo">' + b + '</span>' : '';
                }
            }, {
                name: 'goodsStatus',
                label: inout + '库状态',
                width: 66,
                fixed: !0,
                sortable: !0,
                formatter: function(b, c, d) {
                    var e = (0 == b ? '未' : (1 == b ? '部分' : '全部')) + inout + '库';
                    var f = 0 == b ? 'red' : (1 == b ? 'yellow' : 'green');
                    return '<span class="' + f + '">' + e + '</span>';
                }
            }, {
                name: 'srcOrderId',
                label: '关联单据ID',
                width: 0,
                hidden: !0
            }, {
                name: 'contactId',
                label: '商家Id',
                width: 0,
                hidden: !0
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
            altRows: !1,
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
            loadComplete: function(data) {
                data.status == -1 && $.dialog.tips(data.msg, 2);
                if (goodsShowAll) {
                    $('.grid-wrap .goods-showall').html('<i class="iconfont icon-shuangshangjiantou f12 mr5"></i>收缩全部');
                    $('.grid-wrap .goods-showinfo').removeClass('icon-xiajiantou').addClass('icon-shangjiantou');
                    $('.grid-wrap .goods-info').show();
                } else {
                    $('.grid-wrap .goods-showall').html('<i class="iconfont icon-shuangxiajiantou f12 mr5"></i>展开全部');
                    $('.grid-wrap .goods-showinfo').removeClass('icon-shangjiantou').addClass('icon-xiajiantou');
                    $('.grid-wrap .goods-info').hide();
                }
            },
            loadError: function(a, b) {
                $.dialog.tips('Type: ' + b + '; Response: ' + a.status + ' ' + a.statusText, 2);
            },
            onCellSelect: function(b, c) {
                var rowData = $('#grid').jqGrid('getRowData', b);
                if (c == 5) {
                    $.dialog({
                        title: '查看商家',
                        content: 'url:/Uri/supplier_manage',
                        data: {
                            oper: 'edit',
                            rowId: rowData.contactId
                        },
                        width: 1250,
                        height: 520,
                        max: !1,
                        min: !1,
                        cache: !1,
                        lock: !0,
                        disEdit: !0
                    });
                }
            },
            ondblClickRow: function(b) {
                $('#' + b).find('.ui-icon-pencil').trigger('click');
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
        $('#search').click(function() {
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
        $('.grid-wrap').on('click', '.srcOrderNo', function(a) {
            a.preventDefault();
            var id = $(this).closest('tr')[0].id;
            var rowData = $('#grid').jqGrid('getRowData', id);
            if (rowData.srcOrderId > 0) {
                parent.tab.addTabItem({
                    tabid: 'purchase-purchaseOrder' + rowData.srcOrderId,
                    text: '<i class="iconfont icon-danye f14 mr5"></i>采购订单',
                    url: '/Uri/purchaseOrder_manage?id=' + rowData.srcOrderId
                });
            }
        });
        $('.grid-wrap').on('click', '.ui-icon-pencil', function(a) {
            a.preventDefault();
            var id = $(this).parent().data('id');
            if ('102101' == queryConditions.transType) {
                var tabid = 'inventory-purchase' + id;
                var text = '<i class="iconfont icon-danye f14 mr5"></i>采购收货单';
            } else {
                var tabid = 'inventory-purchaseBack' + id;
                var text = '<i class="iconfont icon-danye f14 mr5"></i>采购退货单';
            }
            parent.tab.addTabItem({
                tabid: tabid,
                text: text,
                url: '/Uri/purchase_manage?id=' + id + '&flag=list&transType=' + queryConditions.transType
            });
        });
        $('.grid-wrap').on('click', '.ui-icon-trash', function(a) {
            a.preventDefault();
            if (Business.verifyRight('PU_DELETE')) {
                var id = $(this).parent().data('id');
                id && $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                    Public.ajaxGet('/Bill/InvPu/delete', {
                        id: id
                    }, function(res) {
                        if (res && 200 == res.status) {
                            $.dialog.tips('删除成功！');
                            $('#grid').jqGrid('delRowData', id);
                            parent.frames['inventory-purchase' + id] && parent.tab.removeTabItem('inventory-purchase' + id);
                            parent.frames['inventory-purchaseBack' + id] && parent.tab.removeTabItem('inventory-purchaseBack' + id);
                            parent.frames['purchase-purchaseOrder'] && parent.frames['purchase-purchaseOrder'].$('#search').trigger('click');
                        } else {
                            $.dialog.tips('删除失败！' + res.msg, 2);
                        }
                    });
                });
            }
        });
        $('.grid-wrap').on('click', '.goods-showinfo', function(a) {
            if ($(this).hasClass('icon-xiajiantou')) {
                $(this).parent().next().show();
                $(this).removeClass('icon-xiajiantou').addClass('icon-shangjiantou');
            } else {
                $(this).parent().next().hide();
                $(this).removeClass('icon-shangjiantou').addClass('icon-xiajiantou');
            }
        });
        $('.goods-showall').on('click', function() {
            if (goodsShowAll) {
                goodsShowAll = !1;
                $('.grid-wrap .goods-showall').html('<i class="iconfont icon-shuangxiajiantou f12 mr5"></i>展开全部');
                $('.grid-wrap .goods-showinfo').removeClass('icon-shangjiantou').addClass('icon-xiajiantou');
                $('.grid-wrap .goods-info').hide();
            } else {
                goodsShowAll = !0;
                $('.grid-wrap .goods-showall').html('<i class="iconfont icon-shuangshangjiantou f12 mr5"></i>收缩全部');
                $('.grid-wrap .goods-showinfo').removeClass('icon-xiajiantou').addClass('icon-shangjiantou');
                $('.grid-wrap .goods-info').show();
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
