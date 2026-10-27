var urlParam = Public.urlParam();
var defaultPage = Public.getDefaultPage();
var queryConditions = {
    status1: '',
    status2: '',
    status3: urlParam.unDone ? 0 : -1,
    beginDate: '',
    endDate: urlParam.endDate ? (urlParam.endDate + ' 23:59:59') : '',
    skey: ''
};
var statusCombo1 = $('#combo1').combo({
    data: '/Base/User/queryAll',
    ajaxOptions: {
        formatData: function(a) {
            return a.data.rows.unshift({
                id: -1,
                name: '所有收货人'
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
    data: '/Base/User/queryAll?department=技术部',
    ajaxOptions: {
        formatData: function(a) {
            return a.data.rows.unshift({
                id: -1,
                name: '所有质检人'
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
var statusCombo3 = $('#combo3').combo({
    data: [
        {id: -1, name: '所有质检状态'},
        {id: 0, name: '未质检'},
        {id: 1, name: '未通过'},
        {id: 2, name: '已通过'}
    ],
    value: 'id',
    text: 'name',
    defaultSelected: urlParam.unDone ? 1 : 0,
    width: 102,
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
        $('#beginDate').val(defaultPage.SYSTEM.beginDate + ' 00:00:00');
        $('#endDate').val((urlParam.endDate || defaultPage.SYSTEM.endDate) + ' 23:59:59');
    },
    initGrid: function() {
        var a = Public.setGrid();
        $('#grid').jqGrid({
            url: '/Base/PurQC/list',
            postData: queryConditions,
            datatype: 'json',
            colModel: [{
                name: 'createTime',
                label: '<span class="ml10">收货时间</span>',
                width: 130,
                fixed: !0,
                sortable: !0,
                classes: 'ti10'
            }, {
                name: 'uName',
                label: '收货人',
                width: 54,
                fixed: !0,
                sortable: !0
            }, {
                name: 'qty',
                label: '收货数量',
                width: 100,
                fixed: !0,
                sortable: !0,
                formatter: function(a, b, c) {
                    return c.qty + ' ' + c.unitName;
                }
            }, {
                name: 'goodsId',
                label: '收货产品',
                width: 100,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis',
                formatter: function(a, b, c) {
                    return c.goodsId ? (c.invNo + ' ' + c.invName + (c.invSpec ? ' ' + c.invSpec : '') + (c.invSku ? ' ' + c.invSku : '') + (c.brandName ? ' ' + c.brandName : '')) : '';
                }
            }, {
                name: 'deliverNo',
                label: '快递信息',
                width: 150,
                fixed: !0,
                sortable: !0,
                formatter: function(a, b, c) {
                    return c.deliverCompany + ' ' + c.deliverNo;
                }
            }, {
                name: 'srcOrderId',
                label: '原订单',
                width: 66,
                fixed: !0,
                sortable: !0,
                align: 'center',
                classes: 'silver',
                formatter: function(a, b, c) {
                    return c.srcOrderId > 0 ? ('<span class="srcOrder-btn db h28 lh28 b-r-3 hover-white" title="点击查看原采购订单" data-srcorderid="' + c.srcOrderId + '">查看</span>') : '无';
                }
            }, {
                name: 'iid',
                label: '原收货单',
                width: 66,
                fixed: !0,
                sortable: !0,
                align: 'center',
                classes: 'silver',
                formatter: function(a, b, c) {
                    return c.srcOrderId > 0 ? ('<span class="srcInventory-btn db h28 lh28 b-r-3 hover-white" title="点击查看原采购收货单" data-srcinventoryid="' + c.iid + '">查看</span>') : '无';
                }
            }, {
                name: 'QCName',
                label: '质检人',
                width: 54,
                fixed: !0,
                sortable: !0
            }, {
                name: 'QCTime',
                label: '质检时间',
                width: 124,
                fixed: !0,
                sortable: !0
            }, {
                name: 'QCStatus',
                label: '质检状态与操作',
                width: 94,
                fixed: !0,
                sortable: !0,
                align: 'center',
                formatter: function(a, b, c) {
                    if (a == 0) {
                        return '<span class="qc-btn fl pl5 pr5 tc h28 lh28 b-r-3 hover-red" title="点击合格通过" data-qcstatus="2" data-id="' + c.id + '">合格</span>'
                            + '<span class="qc-btn fl ml2 pl5 pr5 tc h28 lh28 b-r-3 hover-red" title="点击不合格并打回" data-qcstatus="1" data-id="' + c.id + '">不合格</span>';
                    } else if (a == 1) {
                        if (c.backStatus > 0) {
                            return '<span class="db tc h28 lh28 b-r-3 hover-gray" title="打回的质检已被采购处理">打回已处理</span>';
                        } else {
                            return '<span class="qc-btn db tc h28 lh28 b-r-3 hover-yellow" title="点击重置质检状态以便重新质检" data-qcstatus="0" data-id="' + c.id + '">不合格已打回</span>';
                        }
                    } else if (a == 2) {
                        if (c.QCName == '') {
                            return '<span class="db tc h28 lh28 b-r-3 hover-silver">过往不检</span>';
                        } else {
                            return '<span class="qc-btn db tc h28 lh28 b-r-3 hover-green" title="点击重置质检状态以便重新质检" data-qcstatus="0" data-id="' + c.id + '">合格</span>';
                        }
                    }
                }
            }],
            cmTemplate: {sortable: !1, title: !1},
            jsonReader: {root: 'data.rows', records: 'data.records', total: 'data.total', repeatitems: !1, id: 'id'},
            width: a.w,
            height: a.h,
            altRows: !1,
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
            rowNum: 100,
            rowList: [100, 200, 500],
            viewrecords: !0,
            loadError: function(a, b) {
                $.dialog.tips('Type: ' + b + '; Response: ' + a.status + ' ' + a.statusText, 2);
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
                }, 300);
            }
        });
        $('#search').on('click', function() {
            queryConditions.status1 = statusCombo1.getValue() == -1 ? '' : statusCombo1.getText();
            queryConditions.status2 = statusCombo2.getValue() == -1 ? '' : statusCombo2.getText();
            queryConditions.status3 = statusCombo3.getValue();
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
        $('.grid-wrap').on('click', '.srcOrder-btn', function(a) {
            a.preventDefault();
            if (Business.verifyRight('PO_QUERY')) {
                var id = $(this).data('srcorderid');
                id && parent.tab.addTabItem({
                    tabid: 'purchase-purchaseOrder' + id,
                    text: '<i class="iconfont icon-danye f14 mr5"></i>采购订单',
                    url: '/Uri/purchaseOrder_manage?id=' + id
                });
            }
        });
        $('.grid-wrap').on('click', '.srcInventory-btn', function(a) {
            a.preventDefault();
            if (Business.verifyRight('PU_QUERY')) {
                var id = $(this).data('srcinventoryid');
                id && parent.tab.addTabItem({
                    tabid: 'inventory-purchase' + id,
                    text: '<i class="iconfont icon-danye f14 mr5"></i>采购收货单',
                    url: '/Uri/purchase_manage?id=' + id
                });
            }
        });
        $('#grid').on('click', '.qc-btn', function(a) {
            if (Business.verifyRight('PURQC_ADVANCE')) {
                var id = Number($(this).data('id'));
                var QCStatus = Number($(this).data('qcstatus'));
                var QCName = QCStatus > 0 ? defaultPage.SYSTEM.uName : '';
                var QCTime = QCStatus > 0 ? (new Date).format('yyyy-MM-dd hh:mm:ss') : '';
                var rowData = $('#grid').jqGrid('getRowData', id);
                id && Public.ajaxPost('/Base/PurQC/qc', {
                    id: id,
                    goods: '（' + rowData.qty + '）（' + rowData.goodsId + '）',
                    QCStatus: QCStatus,
                    QCName: QCName,
                    QCTime: QCTime
                }, function(res) {
                    if (res && 200 == res.status) {
                        $.dialog.tips('操作成功！');
                        $('#grid').jqGrid('setCell', id, 'QCStatus', QCStatus);
                        $('#grid').jqGrid('setCell', id, 'QCName', QCName || '&nbsp;'),
                            $('#grid').jqGrid('setCell', id, 'QCTime', QCTime || '&nbsp;');
                    } else {
                        $.dialog.tips('操作失败！' + res.msg, 2);
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
