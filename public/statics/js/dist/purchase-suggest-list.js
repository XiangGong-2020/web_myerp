var defaultPage = Public.getDefaultPage();
var qtyPlaces = defaultPage.SYSTEM.qtyPlaces;
var pricePlaces = defaultPage.SYSTEM.pricePlaces;
var amountPlaces = defaultPage.SYSTEM.amountPlaces;
var queryConditions = {
    uId: $.inArray('PUR_MANAGER', defaultPage.SYSTEM.rights) !== -1 ? -1 : defaultPage.SYSTEM.uId,
    skey: ''
};
var combo1 = $('#combo1').combo({
    data: '/Base/User/queryAll?normal=yes',
    ajaxOptions: {
        formatData: function(res) {
            return res.data.rows.unshift({
                id: -1,
                name: '全部采购人'
            }), res.data.rows;
        }
    },
    value: 'id',
    text: 'name',
    defaultSelected: 0,
    width: 90,
    listHeight: 600,
    callback: {
        onListClick: function() {
            $('#search').trigger('click');
        }
    }
}).getCombo();
var THISPAGE = {
    init: function() {
        THISPAGE.initDom();
        THISPAGE.initGrid();
        THISPAGE.initEvent();
    },
    initDom: function() {
        if ($.inArray('PUR_MANAGER', defaultPage.SYSTEM.rights) !== -1) {
            $('#combo1').parent().removeClass('dn');
            $('#skey').parent().removeClass('dn');
            $('#search').parent().removeClass('dn');
            $('#notice').addClass('dn');
        }
    },
    initGrid: function() {
        var a = Public.setGrid();
        $('#grid').jqGrid({
            url: '/Base/PurSuggest/list',
            postData: queryConditions,
            datatype: 'json',
            colModel: [{
                name: 'billNo',
                label: '<span class="ml10">所在单号</span>',
                width: 150,
                fixed: !0,
                title: !0,
                editable: !1,
                classes: 'ti10 ui-ellipsis hover'
            }, {
                name: 'zidOrNo',
                label: '自编序号或产品编码',
                width: 100,
                title: !0,
                editable: !1,
                classes: 'ui-ellipsis',
                formatter: function(a, b, c) {
                    return c.zid || c.no;
                }
            }, {
                name: 'name',
                label: '产品名称',
                width: 100,
                title: !0,
                editable: !1,
                classes: 'ui-ellipsis'
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
                label: '产品品牌',
                width: 60,
                fixed: !0,
                title: !0,
                editable: !1,
                classes: 'ui-ellipsis'
            }, {
                name: 'unit',
                label: '单位',
                width: 34,
                fixed: !0,
                editable: !1,
                classes: 'ui-ellipsis'
            }, {
                name: 'qty',
                label: '采购数量',
                width: 54,
                fixed: !0,
                editable: !1,
                formatter: 'currency',
                formatoptions: {
                    showZero: !1,
                    decimalPlaces: qtyPlaces
                }
            }, {
                name: 'purName',
                label: '采购人',
                width: 50,
                fixed: !0,
                editable: !1,
                classes: 'gray'
            }, {
                name: 'purPrice',
                label: '采购单价',
                width: 70,
                fixed: !0,
                editable: !1,
                classes: 'blue',
                formatter: 'currency',
                formatoptions: {
                    showZero: !1,
                    decimalPlaces: pricePlaces
                }
            }, {
                name: 'purAmount',
                label: '采购总价',
                width: 70,
                fixed: !0,
                editable: !1,
                classes: 'yellow',
                formatter: 'currency',
                formatoptions: {
                    showZero: !1,
                    decimalPlaces: amountPlaces
                }
            }, {
                name: 'uName',
                label: '建议人',
                width: 50,
                fixed: !0,
                editable: !1,
                classes: 'gray'
            }, {
                name: 'price',
                label: '建议单价',
                width: 70,
                fixed: !0,
                editable: !1,
                classes: 'blue',
                formatter: 'currency',
                formatoptions: {
                    showZero: !1,
                    decimalPlaces: pricePlaces
                }
            }, {
                name: 'amount',
                label: '建议总价',
                width: 70,
                fixed: !0,
                editable: !1,
                classes: 'yellow',
                formatter: 'currency',
                formatoptions: {
                    showZero: !1,
                    decimalPlaces: amountPlaces
                }
            }, {
                name: 'url',
                label: '建议链接',
                width: 70,
                fixed: !0,
                title: !0,
                editable: !1,
                classes: 'ui-ellipsis',
                formatter: function(x, y, z) {
                    return (x && x.indexOf('http') === 0) ? '<a href="' + Public.fixUrl(x) + '" target="_blank">' + x + '</a>' : x;
                }
            }, {
                name: 'content',
                label: '建议补充',
                width: 70,
                fixed: !0,
                title: !0,
                editable: !1,
                classes: 'ui-ellipsis'
            }, {
                name: 'createTime',
                label: '建议时间',
                width: 124,
                fixed: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray',
                formatter: function(cellvalue, options, rowObject) {
                    return ('0000-00-00 00:00:00' == cellvalue || undefined == cellvalue) ? '' : cellvalue;
                }
            }, {
                name: 'modifyTime',
                label: '修改时间',
                width: 124,
                fixed: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray',
                formatter: function(cellvalue, options, rowObject) {
                    return ('0000-00-00 00:00:00' == cellvalue || undefined == cellvalue) ? '' : cellvalue;
                }
            }, {
                name: 'status',
                label: '采购人操作',
                width: 82,
                fixed: !0,
                sortable: !0,
                formatter: function(a, b, c) {
                    if (a == 0) {
                        return '<span class="agree-btn fl db pl5 pr5 h28 lh28 hover-teal b-r-3" title="点击采纳" data-id="' + c.id + '">采纳</span><span class="disagree-btn fl ml2 db pl5 pr5 h28 lh28 hover-red b-r-3" title="点击拒绝" data-id="' + c.id + '">拒绝</span>';
                    } else if (a == 1) {
                        return '<span class="disagree-btn fl db pl5 pr5 h28 lh28 hover-green b-r-3" title="点击转为拒绝" data-id="' + c.id + '">已采纳</span>';
                    } else {
                        return '<span class="agree-btn fl db pl5 pr5 h28 lh28 hover-night b-r-3" title="拒绝原因：' + c.disReason + '<br>点击转为采纳" data-id="' + c.id + '">已拒绝</span>';
                    }
                }
            }, {
                name: 'operate',
                label: '操作',
                width: 44,
                fixed: !0,
                formatter: function(val, opt, row) {
                    var html_con = (
                        '<div class="operating" data-id="' + row.id + '">' +
                        '<span class="oper-icon ui-icon-trash iconfont icon-shanchu" title="删除"></span>' +
                        '</div>'
                    );
                    return html_con;
                }
            }, {
                name: 'id',
                width: 0,
                hidden: !0
            }, {
                name: 'billId',
                width: 0,
                hidden: !0
            }, {
                name: 'transType',
                width: 0,
                hidden: !0
            }, {
                name: 'purId',
                width: 0,
                hidden: !0
            }, {
                name: 'uId',
                width: 0,
                hidden: !0
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
            sortname: 'createTime',
            sortorder: 'DESC',
            pager: '#page',
            page: 1,
            rowNum: 100,
            rowList: [100, 200, 500],
            viewrecords: !0,
            loadError: function(a, b) {
                $.dialog.tips('Type: ' + b + '; Response: ' + a.status + ' ' + a.statusText, 2);
            },
            onCellSelect: function(a, b) {
                var rowData = $('#grid').jqGrid('getRowData', a);
                if (b == 0) {
                    if (rowData.transType == 101111) {
                        parent.tab.addTabItem({
                            tabid: 'purchase-purchaseContract' + rowData.billId,
                            text: '<i class="iconfont icon-danye f14 mr5"></i>特殊采购单',
                            url: '/Uri/purchaseContract_manage?id=' + rowData.billId
                        });
                    } else {
                        parent.tab.addTabItem({
                            tabid: 'purchase-purchaseOrder' + rowData.billId,
                            text: '<i class="iconfont icon-danye f14 mr5"></i>采购订单',
                            url: '/Uri/purchaseOrder_manage?id=' + rowData.billId
                        });
                    }
                }
            }
        });
    },
    initEvent: function() {
        $('#search').click(function() {
            queryConditions.uId = combo1.getValue();
            queryConditions.skey = $.trim($('#skey').val()).split(/[ ,，]+/).toString();
            $('#grid').jqGrid('setGridParam', {
                postData: queryConditions,
                page: 1
            }).trigger('reloadGrid');
        });
        $('#refresh').on('click', function() {
            window.location.reload();
        });
        $('.grid-wrap').on('click', '.agree-btn', function(a) {
            a.preventDefault();
            var id = parseInt($(this).data('id'));
            var rowData = $('#grid').jqGrid('getRowData', id);
            if (rowData && rowData.purId == defaultPage.SYSTEM.uId) {
                Public.ajaxPost('/Base/PurSuggest/status', {
                    id: id,
                    status: 1
                }, function(res) {
                    if (res && 200 == res.status) {
                        $.dialog.tips('操作成功！');
                        $('#grid').jqGrid('setCell', id, 'status', 1);
                    } else {
                        $.dialog.tips('操作失败！' + res.msg, 2);
                    }
                });
            } else {
                $.dialog.tips('您不是对应的采购人，无法操作！', 2);
            }
        });
        $('.grid-wrap').on('click', '.disagree-btn', function(a) {
            a.preventDefault();
            var id = parseInt($(this).data('id'));
            var rowData = $('#grid').jqGrid('getRowData', id);
            if (rowData && rowData.purId == defaultPage.SYSTEM.uId) {
                $.dialog.prompt('<span class="f14">请输入拒绝本次建议的原因（100字以内）。', function(inputValue) {
                    Public.ajaxPost('/Base/PurSuggest/status', {
                        id: id,
                        status: 2,
                        disReason: inputValue
                    }, function(res) {
                        if (res && 200 == res.status) {
                            $.dialog.tips('操作成功！');
                            $('#grid').jqGrid('setRowData', id, res.data);
                        } else {
                            $.dialog.tips('操作失败！' + res.msg, 2);
                        }
                    });
                });
            } else {
                $.dialog.tips('您不是对应的采购人，无法操作！', 2);
            }
        });
        $('#grid').on('click', '.ui-icon-trash', function(a) {
            a.preventDefault();
            if (Business.verifyRight('PUR_MANAGER')) {
                var id = $(this).parent().data('id');
                id && $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                    Public.ajaxPost('/Base/PurSuggest/delete', {
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
        $(window).on('keydown', function(a) {
            var curKey = a.which;
            if (curKey == 13) {
                $('#search').trigger('click');
            }
        });
        $(window).on('resize', function() {
            Public.resizeGrid();
        });
    },
    callback: function(a, b, c, d) {
        $('#search').trigger('click'), c && c.api.close();
    }
};
$(function() {
    THISPAGE.init();
});
