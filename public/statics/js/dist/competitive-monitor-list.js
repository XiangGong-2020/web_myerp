var urlParam = Public.urlParam();
var defaultPage = Public.getDefaultPage();
var queryConditions = {
    status1: -1,
    status2: '',
    status3: '',
    skey: ''
};
var statusCombo1 = $('#combo1').combo({
    data: [
        {id: -1, name: '所有监控结果'},
        {id: 0, name: '竞品正常'},
        {id: 1, name: '竞品低价'},
        {id: 2, name: '链接无效'}
    ],
    value: 'id',
    text: 'name',
    defaultSelected: 0,
    width: 102,
    listHeight: 500,
    callback: {
        onListClick: function() {
            $('#search').trigger('click');
        }
    }
}).getCombo();
var statusCombo2 = $('#combo2').combo({
    data: '/Base/CompetitiveMonitor/queryAllSelfShopName',
    ajaxOptions: {
        formatData: function(a) {
            return a.data.rows.unshift({
                id: -1,
                name: '所有自家店铺'
            }), a.data.rows;
        }
    },
    value: 'id',
    text: 'name',
    defaultSelected: 0,
    width: 102,
    listHeight: 500,
    callback: {
        onListClick: function() {
            $('#search').trigger('click');
        }
    }
}).getCombo();
var statusCombo3 = $('#combo3').combo({
    data: '/Base/User/queryAll',
    ajaxOptions: {
        formatData: function(a) {
            return a.data.rows.unshift({
                id: -1,
                name: '所有录入人'
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
var THISPAGE = {
    init: function() {
        THISPAGE.initDom();
        THISPAGE.initGrid();
        THISPAGE.initEvent();
    },
    initDom: function() {
    },
    initGrid: function() {
        var a = Public.setGrid();
        $('#grid').jqGrid({
            url: '/Base/CompetitiveMonitor/list',
            postData: queryConditions,
            datatype: 'json',
            colModel: [{
                name: 'id',
                label: '<span class="ml10">ID</span>',
                width: 60,
                fixed: !0,
                sortable: !0,
                classes: 'ti10'
            }, {
                name: 'goodsText',
                label: '自家产品',
                width: 100,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'shopName',
                label: '自家店铺',
                width: 100,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'shopType',
                label: '店铺类型',
                width: 68,
                fixed: !0,
                sortable: !0
            }, {
                name: 'url',
                label: '自家链接',
                width: 80,
                fixed: !0,
                sortable: !0,
                formatter: function(a, b, c) {
                    d = '<a class="db fl w50 h28 lh28 tc hover-white b-r-3" target="_blank" href="' + a + '" title="自家链接">打开</a>';
                    return d;
                }
            }, {
                name: 'price1',
                label: '自家原价',
                width: 80,
                fixed: !0,
                sortable: !0
            }, {
                name: 'price2',
                label: '自家优惠价',
                width: 100,
                fixed: !0,
                sortable: !0
            }, {
                name: 'discountsInfo',
                label: '自家优惠信息',
                width: 200,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'status',
                label: '监控结果',
                width: 86,
                fixed: !0,
                sortable: !0,
                formatter: function(a, b, c) {
                    var d = a == 0 ? '竞品正常' : (a == 1 ? '竞品低价' : '链接无效');
                    var e = a == 0 ? 'hover-silver' : (a == 1 ? 'hover-yellow' : 'hover-red');
                    return '<span class="db tc ' + e + ' b-r-3">' + d + '<i class="iconfont icon-xiajiantou f12 ml5"></i></span>';
                }
            }, {
                name: 'uName',
                label: '录入人',
                width: 54,
                fixed: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray'
            }, {
                name: 'createTime',
                label: '录入时间',
                width: 124,
                fixed: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray',
                formatter: function(cellvalue, options, rowObject) {
                    return ('0000-00-00 00:00:00' == cellvalue || undefined == cellvalue) ? '' : cellvalue;
                }
            }, {
                name: 'lastSpiderTime',
                label: '上次成功监控时间',
                width: 124,
                fixed: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray',
                formatter: function(cellvalue, options, rowObject) {
                    return ('0000-00-00 00:00:00' == cellvalue || undefined == cellvalue) ? '' : cellvalue;
                }
            }, {
                name: 'goodsId',
                label: '',
                width: 0,
                hidden: !0
            }, {
                name: 'operate',
                label: '操作',
                width: 72.8,
                fixed: !0,
                sortable: !1,
                formatter: Public.operFmatter
            }],
            cmTemplate: {sortable: !1, title: !1},
            jsonReader: {root: 'data.rows', records: 'data.records', total: 'data.total', repeatitems: !1, id: 'id'},
            width: a.w,
            height: a.h,
            gridview: !0,
            rownumbers: !1,
            multiselect: !1,
            shrinkToFit: !0,
            forceFit: !1,
            viewsortcols: [true, 'vertical', true],
            sortname: 'id',
            sortorder: 'DESC',
            pager: '#page',
            page: 1,
            rowNum: 10,
            rowList: [10, 20, 50],
            viewrecords: !0,
            subGrid: !0,
            subGridWidth: 30,
            subGridOptions: {
                expandOnLoad: !1
            },
            subGridRowExpanded: function(subgrid_id, row_id) {
                var rowData = $('#grid').jqGrid('getRowData', row_id);
                var subgrid_table_id = subgrid_id + '_t';
                $('#' + subgrid_id).html('<div class="grid-wrap"><table id="' + subgrid_table_id + '"></table></div>');
                $('#' + subgrid_table_id).jqGrid({
                    url: '/Base/CompetitiveMonitor/get_list',
                    postData: {id: row_id},
                    datatype: 'json',
                    colModel: [{
                        name: 'id',
                        label: '<span class="ml10">ID</span>',
                        width: 60,
                        fixed: !0,
                        sortable: !0,
                        classes: 'ti10'
                    }, {
                        name: 'title',
                        label: '竞品标题',
                        width: 100,
                        title: !0,
                        sortable: !0,
                        classes: 'ui-ellipsis'
                    }, {
                        name: 'shopName',
                        label: '竞品店铺',
                        width: 100,
                        fixed: !0,
                        title: !0,
                        sortable: !0,
                        classes: 'ui-ellipsis'
                    }, {
                        name: 'shopType',
                        label: '店铺类型',
                        width: 68,
                        fixed: !0,
                        sortable: !0
                    }, {
                        name: 'url',
                        label: '竞品链接',
                        width: 80,
                        fixed: !0,
                        sortable: !0,
                        formatter: function(a, b, c) {
                            d = '<a class="db fl w50 h28 lh28 tc hover-white b-r-3" target="_blank" href="' + a + '" title="竞品链接">打开</a>';
                            return d;
                        }
                    }, {
                        name: 'price1',
                        label: '竞品原价',
                        width: 80,
                        fixed: !0,
                        sortable: !0
                    }, {
                        name: 'price2',
                        label: '竞品优惠价',
                        width: 100,
                        fixed: !0,
                        sortable: !0,
                        formatter: function(a, b, c) {
                            if (c.status == 2) {
                                return '<span class="f14 fb red">链接无效</span>';
                            } else if (a < rowData.price2) {
                                return '<span class="f14 fb yellow">' + a + '</span>';
                            } else {
                                return a;
                            }
                        }
                    }, {
                        name: 'discountsInfo',
                        label: '竞品优惠信息',
                        width: 290,
                        fixed: !0,
                        title: !0,
                        sortable: !0,
                        classes: 'ui-ellipsis',
                        formatter: function(a, b, c) {
                            if (c.status == 2) {
                                return '<span class="f14 fb red">链接无效</span>';
                            } else if (c.price2 < rowData.price2) {
                                return a ? ('<i class="iconfont icon-youhui yellow mr5"></i><span class="f14 fb yellow">' + a + '</span>') : '';
                            } else {
                                return a;
                            }
                        }
                    }, {
                        name: 'uName',
                        label: '录入人',
                        width: 54,
                        fixed: !0,
                        sortable: !0,
                        classes: 'ui-ellipsis gray'
                    }, {
                        name: 'createTime',
                        label: '录入时间',
                        width: 124,
                        fixed: !0,
                        sortable: !0,
                        classes: 'ui-ellipsis gray',
                        formatter: function(cellvalue, options, rowObject) {
                            return ('0000-00-00 00:00:00' == cellvalue || undefined == cellvalue) ? '' : cellvalue;
                        }
                    }, {
                        name: 'lastSpiderTime',
                        label: '上次成功监控时间',
                        width: 182,
                        fixed: !0,
                        sortable: !0,
                        classes: 'ui-ellipsis gray',
                        formatter: function(cellvalue, options, rowObject) {
                            return ('0000-00-00 00:00:00' == cellvalue || undefined == cellvalue) ? '' : cellvalue;
                        }
                    }, {
                        name: 'goodsId',
                        label: '',
                        width: 0,
                        hidden: !0
                    }],
                    cmTemplate: {sortable: !1, title: !1},
                    jsonReader: {root: 'data.rows', records: 'data.records', total: 'data.total', repeatitems: !1, id: 'id'},
                    width: a.w - 35,
                    height: '100%',
                    shrinkToFit: !0,
                    forceFit: !1,
                    rownumbers: !1,
                    viewsortcols: [true, 'vertical', true],
                    rowNum: 100,
                    sortname: 'id',
                    sortorder: 'ASC',
                    gridComplete: function() {
                        setTimeout(function() {
                            Public.autoGridWidth($('#' + subgrid_table_id));
                        }, 10);
                    }
                });
            },
            loadComplete: function(data) {
                if (data && 200 == data.status) {
                    var rows = data.data.rows;
                    //展开各行，每次展开一行间隔一定时间
                    for (let i = 0; i < rows.length; i++) {
                        setTimeout(function() {
                            $('#grid tr#' + rows[i].id + ' .ui-sgcollapsed').trigger('click');
                        }, 200 * i);
                    }
                } else {
                    data.status == -1 && $.dialog.tips(data.msg, 2);
                }
            },
            loadError: function(a, b) {
                $.dialog.tips('Type: ' + b + '; Response: ' + a.status + ' ' + a.statusText, 2);
            },
            onCellSelect: function(rowId, iCol, cellContent, event) {
                if (iCol == 9) { // 点击监控状态列，自动展开|收缩subGrid
                    $('#grid tr#' + rowId + ' .ui-sgcollapsed').trigger('click');
                }
            },
            ondblClickRow: function(c) {
                $('#' + c).find('.ui-icon-pencil').trigger('click');
            }
        });
    },
    initEvent: function() {
        $('#search').on('click', function(a) {
            a.preventDefault();
            queryConditions.status1 = statusCombo1.getValue();
            queryConditions.status2 = statusCombo2.getValue() == -1 ? '' : statusCombo2.getText();
            queryConditions.status3 = statusCombo3.getValue() == -1 ? '' : statusCombo3.getText();
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
            if ($.inArray('COMPETITIVE_MONITOR_ADVANCE', defaultPage.SYSTEM.rights) !== -1) {
                $.dialog({
                    title: '新增竞品监控',
                    content: 'url:/Uri/competitive_monitor_manage',
                    data: {
                        oper: 'add'
                    },
                    width: 1250,
                    height: 600,
                    max: !1,
                    min: !1,
                    cache: !1,
                    lock: !0
                });
            }
        });
        $('#grid').on('click', '.ui-icon-pencil', function(a) {
            a.preventDefault();
            if ($.inArray('COMPETITIVE_MONITOR_ADVANCE', defaultPage.SYSTEM.rights) !== -1) {
                var id = $(this).parent().data('id');
                $.dialog({
                    title: '编辑竞品监控',
                    content: 'url:/Uri/competitive_monitor_manage',
                    data: {
                        oper: 'edit',
                        rowId: id
                    },
                    width: 1250,
                    height: 600,
                    max: !1,
                    min: !1,
                    cache: !1,
                    lock: !0
                });
            }
        });
        $('#grid').on('click', '.ui-icon-trash', function(a) {
            a.preventDefault();
            if ($.inArray('COMPETITIVE_MONITOR_ADVANCE', defaultPage.SYSTEM.rights) !== -1) {
                var id = $(this).parent().data('id');
                var rowData = $('#grid').jqGrid('getRowData', id);
                if (id && rowData) {
                    $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                        Public.ajaxPost('/Base/CompetitiveMonitor/delete', {
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
            var subGridWrap = $('.ui-subgrid .grid-wrap');
            if (subGridWrap && subGridWrap.length > 0) {
                $.each(subGridWrap, function(k, v) {
                    Public.autoGridWidth($('#' + v.parentNode.id + '_t'));
                });
            }
        });
    }
};
$(function() {
    THISPAGE.init();
});
