var urlParam = Public.urlParam();
var defaultPage = Public.getDefaultPage();
var queryConditions = {
    uId: urlParam.uId || -1,
};
var userCombo = $('#userCombo').combo({
    data: '/Base/User/queryAll?normal=yes',
    ajaxOptions: {
        formatData: function(a) {
            return a.data.rows.unshift({
                id: -1,
                name: '所有采购人'
            }), a.data.rows;
        }
    },
    value: 'id',
    text: 'name',
    defaultSelected: urlParam.uId ? ['id', parseInt(urlParam.uId)] : 0,
    width: 90,
    listHeight: 620,
    callback: {
        onListClick: function() {
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
            url: '/Bill/InvPc/listLost',
            postData: queryConditions,
            datatype: 'json',
            colModel: [{
                name: 'billNo',
                label: '<span class="ml10">单据编号</span>',
                width: 160,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ti10 ui-ellipsis hover'
            }, {
                name: 'endDate',
                label: '交货限期',
                width: 70,
                fixed: !0,
                sortable: !0
            }, {
                name: 'zid',
                label: '自编序号',
                width: 56,
                fixed: !0,
                title: !0
            }, {
                name: 'name',
                label: '产品名称',
                width: 100,
                title: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'spec',
                label: '规格型号',
                width: 100,
                title: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'sku',
                label: '辅助属性',
                width: 100,
                title: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'brand',
                label: '品牌',
                width: 60,
                fixed: !0,
                title: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'unit',
                label: '单位',
                width: 40,
                fixed: !0,
                title: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'qty',
                label: '数量',
                width: 54,
                fixed: !0,
                formatter: function(a, b, c) {
                    return Number(a) || '';
                }
            }, {
                name: 'description1',
                label: '备注1',
                width: 60,
                title: !0,
                editable: !0,
                edittype: 'textarea',
                classes: 'ui-ellipsis'
            }, {
                name: 'description2',
                label: '备注2',
                width: 60,
                title: !0,
                editable: !0,
                edittype: 'textarea',
                classes: 'ui-ellipsis'
            }, {
                name: 'buyUrl',
                label: '参考链接',
                width: 56,
                fixed: !0,
                title: !0,
                classes: 'silver ui-ellipsis'
            }, {
                name: 'myUrl',
                label: '采购链接',
                width: 80,
                fixed: !0,
                title: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'uName',
                label: '采购人',
                width: 50,
                fixed: !0,
                classes: 'night'
            }, {
                name: '',
                label: '操作',
                width: 60,
                fixed: !0,
                align: 'center',
                formatter: function(a, b, c) {
                    return '<span class="topur-btn fl pl5 pr5 tc h28 lh28 b-r-3 hover-red" title="点击去处理" data-iid="' + c.iid + '">去处理</span>';
                }
            }, {
                name: 'id',
                label: 'ID',
                width: 0,
                hidden: !0,
                editable: !1
            }, {
                name: 'iid',
                label: 'ID',
                width: 0,
                hidden: !0,
                editable: !1
            }, {
                name: 'uId',
                label: 'uId',
                width: 0,
                hidden: !0,
                editable: !1
            }],
            cmTemplate: {sortable: !1, title: !1},
            jsonReader: {root: 'data.rows', records: 'data.records', total: 'data.total', repeatitems: !1, id: 'id'},
            width: a.w,
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
            },
            loadError: function(a, b) {
                $.dialog.tips('Type: ' + b + '; Response: ' + a.status + ' ' + a.statusText, 2);
            },
            onCellSelect: function(c, d) {
                var rowData = $('#grid').jqGrid('getRowData', c);
                if (d == 0) {
                    if (Business.verifyRight('PC_QUERY')) {
                        var billNo = rowData['billNo'];
                        billNo && parent.tab.addTabItem({
                            tabid: 'purchase-purchaseContractList',
                            text: '<i class="iconfont icon-danju f14 mr5"></i>特殊采购',
                            url: '/Uri/purchaseContract_list?skey=' + billNo
                        });
                    }
                }
                if (d == 2) {
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
            }
        });
    },
    initEvent: function() {
        $('#search').on('click', function() {
            queryConditions.uId = userCombo.getValue();
            $('#grid').jqGrid('setGridParam', {
                postData: queryConditions,
                page: 1
            }).trigger('reloadGrid');
        });
        $('#refresh').on('click', function() {
            window.location.reload();
        });
        $('.grid-wrap').on('click', '.topur-btn', function(a) {
            a.preventDefault();
            if (Business.verifyRight('PC_QUERY')) {
                var iid = $(this).data('iid');
                iid && parent.tab.addTabItem({
                    tabid: 'purchase-purchaseContract' + iid,
                    text: '<i class="iconfont icon-danye f14 mr5"></i>特殊采购单',
                    url: '/Uri/purchaseContract_manage?id=' + iid
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
    }
};
$(function() {
    THISPAGE.init();
});
