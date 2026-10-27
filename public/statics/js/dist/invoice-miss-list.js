var urlParam = Public.urlParam();
var defaultPage = Public.getDefaultPage();
var queryConditions = {
    type: 0,
    uName: defaultPage.SYSTEM.uName,
    billType: 'order'
};
var typeCombo = $('#typeCombo').combo({
    data: [
        {id: 0, name: '未白条入账'},
        {id: 1, name: '已白条入账'}
    ],
    value: 'id',
    text: 'name',
    width: 102,
    listHeight: 500,
    defaultSelected: 0,
    cache: !1,
    editable: !1,
    trigger: !0,
    emptyOptions: !1,
    callback: {
        onListClick: function() {
             queryConditions.type = this._selectedIndex;
            $('#grid').jqGrid('setGridParam', {postData: queryConditions, page: 1}).trigger('reloadGrid');
        }
    },
    queryDelay: 0
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
            url: '/Base/Invoice/listMiss',
            postData: queryConditions,
            datatype: 'json',
            colModel: [{
                name: 'billNo',
                label: '<span class="ml10">单据编号</span>',
                width: 180,
                fixed: !0,
                classes: 'ti10 hover'
            }, {
                name: 'billDate',
                label: '单据日期',
                width: 90,
                fixed: !0
            }, {
                name: 'zid',
                label: '商品自编序号',
                width: 120,
                fixed: !0,
                title: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'uName',
                label: '发票收取责任人',
                width: 120,
                fixed: !0,
                title: !0,
                classes: 'ui-ellipsis',
                formatter: function(a, b, c) {
                    return queryConditions.billType == 'contract' ? (c.quotedName || a) : a;
                }
            }, {
                name: 'invoicePercent',
                label: '发票收取情况',
                width: 300,
                fixed: !0,
                align: 'center',
                formatter: function(a, b, c) {
                    if (queryConditions.billType != 'contract_info') {
                        var d = Math.max(0, Number(parseFloat(c.invoiceAmount) / parseFloat(c.amount) * 100).toFixed(1));
                        var e = '<span class="rel db h30 lh30 white bg-rgba3 b-r-3"><span class="abs db h30 b-r-3 bg-green" style="left:0;top:0;width:' + Math.min(100, d) + '%"></span><span class="abs db" style="left:0;top:0;width:calc(50% - 25px)">已收 ' + c.invoiceAmount + '</span><span class="abs db" style="right:0;top:0;width:calc(50% - 25px)">应收 ' + c.amount + '</span><span class="abs db tc bg-rgba2 b-r-100" style="left:50%;margin-left:-25px;top:0;width:50px">' + d + '%</span></span>';
                        return e;
                    } else {
                        var d = '<span class="rel db h30 lh30 white bg-rgba3 b-r-3"><span class="abs db h28 lh28 b-r-3 bg-green" style="left:0;top:0;width:0%"></span><span class="abs db h30 lh30" style="left:0;top:0;width:calc(50% - 25px)">未收</span><span class="abs db h30 lh30" style="right:0;top:0;width:calc(50% - 25px)">应收 ' + c.paidAmount + '</span><span class="abs db h30 lh30 tc bg-rgba2 b-r-100" style="left:50%;margin-left:-25px;top:0;width:50px">0.0%</span></span>';
                        return d;
                    }
                }
            }, {
                name: 'ignoreInvoiceImgs',
                label: '责任人申请白条入账（白条）',
                width: 200,
                fixed: !0,
                title: !0,
                classes: 'ui-ellipsis',
                formatter: function(a, b, c) {
                    var imgs = '';
                    if (a) {
                        a.forEach(element => {
                            imgs += '<a target="_blank" href="/data/upfile/invoiceMiss/img/' + element + '"><span class="fl mr2 mb2 iconfont icon-tupian f20 gray poi" title="点击查看"></span></a>';
                        });
                    }
                    return imgs;
                }
            }, {
                name: 'ignoreInvoiceReason',
                label: '责任人申请白条入账（理由）',
                width: 100,
                title: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'operate',
                label: '管理员操作',
                width: 410,
                fixed: !0,
                formatter: function(a, b, c) {
                    if (c.ignoreInvoiceReason) {
                        if (c.isIgnoreInvoice == 0) {
                            var d = '<a class="agreeIgnore-btn db fl pl10 pr10 h28 lh28 b-r-3 hover-green" data-id="' + c.id + '">同意白条</a><a class="disagreeIgnore-btn db fl ml5 pl10 pr10 h28 lh28 b-r-3 hover-red" data-id="' + c.id + '">拒绝白条</a>';
                        } else if (c.isIgnoreInvoice == 1) {
                            var d = '<a class="reDisagreeIgnore-btn db fl pl10 pr10 h28 lh28 tc b-r-3 hover-black" data-id="' + c.id + '">取消拒绝</a><a class="agreeIgnore-btn db fl ml5 pl10 pr10 h28 lh28 b-r-3 hover-green" data-id="' + c.id + '">转为同意</a><span class="db fr mr10 black" title="拒绝原因：' + c.disagreeIgnoreReason + '">' + c.ignoreInvoiceName + ' ' + c.ignoreInvoiceTime + ' 拒绝白条入账</span>';
                        } else if (c.isIgnoreInvoice == 2) {
                            var d = '<a class="reAgreeIgnore-btn db fl pl10 pr10 h28 lh28 tc b-r-3 hover-black" data-id="' + c.id + '">取消同意</a><a class="disagreeIgnore-btn db fl ml5 pl10 pr10 h28 lh28 b-r-3 hover-red" data-id="' + c.id + '">转为拒绝</a><span class="db fr mr10 black">' + c.ignoreInvoiceName + ' ' + c.ignoreInvoiceTime + ' 同意白条入账</span>';
                        } else {
                            var d = '<a class="reIgnore-btn db fl pl10 pr10 h28 lh28 tc b-r-3 hover-black" data-id="' + c.id + '">取消直入</a><a class="ignoreEdit-btn db fl ml5 pl10 pr10 h28 lh28 tc b-r-3 hover-teal" data-id="' + c.id + '">重新编辑</a><span class="db fr mr10 black">' + c.ignoreInvoiceName + ' ' + c.ignoreInvoiceTime + ' 直接白条入账</span>';
                        }
                    } else {
                        if (c.isIgnoreInvoice == 0) {
                            var d = '<a class="ignore-btn db fl pl10 pr10 h28 lh28 tc b-r-3 hover-teal" data-id="' + c.id + '" title="管理员可直接进行白条入账">直接白条入账</a>';
                        } else {
                            var d = '<a class="reIgnore-btn db fl pl10 pr10 h28 lh28 tc b-r-3 hover-black" data-id="' + c.id + '">取消直入</a><a class="ignoreEdit-btn db fl ml5 pl10 pr10 h28 lh28 tc b-r-3 hover-teal" data-id="' + c.id + '">重新编辑</a><span class="db fr mr10 black">' + c.ignoreInvoiceName + ' ' + c.ignoreInvoiceTime + ' 直接白条入账</span>';
                        }
                    }
                    return d;
                }
            }, {
                name: 'id',
                width: 0,
                hidden: !0
            }, {
                name: 'iid',
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
            sortname: 'id',
            sortorder: 'DESC',
            pager: '#page',
            page: 1,
            rowNum: 100,
            rowList: [100, 200, 500],
            viewrecords: !0,
            loadComplete: function(data) {
                data.status == -1 && $.dialog.tips(data.msg, 2);
                if (queryConditions.billType != 'contract_info') {
                    $('#grid').hideCol('zid').trigger('reloadGrid');
                    Public.resizeGrid();
                } else {
                    $('#grid').showCol('zid').trigger('reloadGrid');
                    Public.resizeGrid();
                }
                $('.ui-btn').find('b').remove();
                $('#order').append('<b>' + data.data.total_order + ' 单</b>');
                $('#contract_info').append('<b>' + data.data.total_contract_info + ' 项</b>');
                $('#money').append('<b>' + data.data.total_money + ' 单</b>');
                $('#deliver').append('<b>' + data.data.total_deliver + ' 单</b>');
            },
            onCellSelect: function(c, d) {
                var rowData = $('#grid').jqGrid('getRowData', c);
                if (d == 0) {
                    switch (queryConditions.billType) {
                        case 'order':
                            if (Business.verifyRight('PO_QUERY')) {
                                var billNo = rowData['billNo'];
                                billNo && parent.tab.addTabItem({
                                    tabid: 'purchase-purchaseOrderList',
                                    text: '<i class="iconfont icon-danju f14 mr5"></i>采购订单',
                                    url: '/Uri/purchaseOrder_list?skey=' + billNo
                                });
                            }
                            break;
                        case 'contract_info':
                            if (Business.verifyRight('PC_QUERY')) {
                                var iid = rowData.iid;
                                iid && parent.tab.addTabItem({
                                    tabid: 'purchase-purchaseContract' + iid,
                                    text: '<i class="iconfont icon-danye f14 mr5"></i>特殊采购单',
                                    url: '/Uri/purchaseContract_manage?id=' + iid
                                });
                            }
                            break;
                        case 'money':
                            if (Business.verifyRight('EXPEND_QUERY')) {
                                var billNo = rowData['billNo'];
                                billNo && parent.tab.addTabItem({
                                    tabid: 'money-expendList',
                                    text: '<i class="iconfont icon-danju f14 mr5"></i>其他支出',
                                    url: '/Uri/expend_list?skey=' + billNo
                                });
                            }
                            break;
                        case 'deliver':
                            if (Business.verifyRight('DELIVER_QUERY')) {
                                var billNo = rowData['billNo'];
                                billNo && parent.tab.addTabItem({
                                    tabid: 'deliver-deliverList',
                                    text: '<i class="iconfont icon-danju f14 mr5"></i>快递月结',
                                    url: '/Uri/deliver_list?skey=' + billNo
                                });
                            }
                            break;
                        default:
                            break;
                    }
                }
            }
        });
    },
    initEvent: function() {
        $('#order').on('click', function() {
            $(this).addClass('current').siblings().removeClass('current');
            queryConditions.billType = 'order';
            $('#grid').jqGrid('setGridParam', {postData: queryConditions, page: 1}).trigger('reloadGrid');
        });
        $('#contract_info').on('click', function() {
            $(this).addClass('current').siblings().removeClass('current');
            queryConditions.billType = 'contract_info';
            $('#grid').jqGrid('setGridParam', {postData: queryConditions, page: 1}).trigger('reloadGrid');
        });
        $('#money').on('click', function() {
            $(this).addClass('current').siblings().removeClass('current');
            queryConditions.billType = 'money';
            $('#grid').jqGrid('setGridParam', {postData: queryConditions, page: 1}).trigger('reloadGrid');
        });
        $('#deliver').on('click', function() {
            $(this).addClass('current').siblings().removeClass('current');
            queryConditions.billType = 'deliver';
            $('#grid').jqGrid('setGridParam', {postData: queryConditions, page: 1}).trigger('reloadGrid');
        });
        $('#refresh').on('click', function() {
            window.location.reload();
        });
        $('.grid-wrap').on('click', '.ignore-btn', function(a) {
            a.preventDefault();
            if (Business.verifyRight('SYS_ADMIN')) {
                var id = $(this).data('id');
                id && $.dialog({
                    title: '直接白条入账',
                    content: 'url:/Uri/invoice_miss_ignore',
                    data: {
                        oper: 'add',
                        billType: queryConditions.billType,
                        type: 'ignore',
                        id: id,
                        callback: THISPAGE.callback
                    },
                    width: 400,
                    height: 260,
                    max: !1,
                    min: !1,
                    cache: !1,
                    lock: !0
                });
            }
        });
        $('.grid-wrap').on('click', '.ignoreEdit-btn', function(a) {
            a.preventDefault();
            if (Business.verifyRight('SYS_ADMIN')) {
                var id = $(this).data('id');
                id && $.dialog({
                    title: '编辑白条入账',
                    content: 'url:/Uri/invoice_miss_ignore',
                    data: {
                        oper: 'edit',
                        billType: queryConditions.billType,
                        type: 'ignore',
                        id: id,
                        callback: THISPAGE.callback
                    },
                    width: 400,
                    height: 260,
                    max: !1,
                    min: !1,
                    cache: !1,
                    lock: !0
                });
            }
        });
        $('.grid-wrap').on('click', '.reIgnore-btn', function(a) {
            a.preventDefault();
            if (Business.verifyRight('SYS_ADMIN')) {
                var id = $(this).data('id');
                id && $.dialog.confirm('确定要取消白条入账吗？', function() {
                    Public.ajaxPost('/Base/Invoice/ignoreMiss', {
                        billType: queryConditions.billType,
                        type: 'reIgnore',
                        id: id
                    }, function(res) {
                        if (res && 200 == res.status) {
                            $.dialog.tips('取消白条入账成功！');
                            $('#grid').jqGrid('setGridParam', {postData: queryConditions}).trigger('reloadGrid');
                        } else {
                            parent.$.dialog.tips('取消白条入账失败！' + res.msg, 2);
                        }
                    });
                });
            }
        });
        $('.grid-wrap').on('click', '.agreeIgnore-btn', function(a) {
            if (Business.verifyRight('SYS_ADMIN')) {
                var id = $(this).data('id');
                id && $.dialog.confirm('确定要同意白条入账吗？', function() {
                    Public.ajaxPost('/Base/Invoice/ignoreMiss', {
                        billType: queryConditions.billType,
                        type: 'agreeIgnore',
                        id: id
                    }, function(res) {
                        if (res && 200 == res.status) {
                            $.dialog.tips('同意白条入账成功！');
                            $('#grid').jqGrid('setGridParam', {postData: queryConditions}).trigger('reloadGrid');
                        } else {
                            parent.$.dialog.tips('同意白条入账失败！' + res.msg, 2);
                        }
                    });
                });
            }
        });
        $('.grid-wrap').on('click', '.disagreeIgnore-btn', function(a) {
            if (Business.verifyRight('SYS_ADMIN')) {
                var id = $(this).data('id');
                id && $.dialog.prompt('<span class="f14">请输入拒绝白条入账的原因，例如：不能以票额开完为由不开发票</span>', function(inputValue) {
                    Public.ajaxPost('/Base/Invoice/ignoreMiss', {
                        billType: queryConditions.billType,
                        type: 'disagreeIgnore',
                        id: id,
                        disagreeIgnoreReason: inputValue
                    }, function(res) {
                        if (res && 200 == res.status) {
                            $.dialog.tips('拒绝白条入账成功！');
                            $('#grid').jqGrid('setGridParam', {postData: queryConditions}).trigger('reloadGrid');
                        } else {
                            parent.$.dialog.tips('拒绝白条入账失败！' + res.msg, 2);
                        }
                    });
                });
            }
        });
        $('.grid-wrap').on('click', '.reAgreeIgnore-btn', function(a) {
            a.preventDefault();
            if (Business.verifyRight('SYS_ADMIN')) {
                var id = $(this).data('id');
                id && $.dialog.confirm('确定要取消同意吗？', function() {
                    Public.ajaxPost('/Base/Invoice/ignoreMiss', {
                        billType: queryConditions.billType,
                        type: 'reAgreeIgnore',
                        id: id
                    }, function(res) {
                        if (res && 200 == res.status) {
                            $.dialog.tips('取消同意成功！');
                            $('#grid').jqGrid('setGridParam', {postData: queryConditions}).trigger('reloadGrid');
                        } else {
                            parent.$.dialog.tips('取消同意失败！' + res.msg, 2);
                        }
                    });
                });
            }
        });
        $('.grid-wrap').on('click', '.reDisagreeIgnore-btn', function(a) {
            a.preventDefault();
            if (Business.verifyRight('SYS_ADMIN')) {
                var id = $(this).data('id');
                id && $.dialog.confirm('确定要取消拒绝吗？', function() {
                    Public.ajaxPost('/Base/Invoice/ignoreMiss', {
                        billType: queryConditions.billType,
                        type: 'reDisagreeIgnore',
                        id: id
                    }, function(res) {
                        if (res && 200 == res.status) {
                            $.dialog.tips('取消拒绝成功！');
                            $('#grid').jqGrid('setGridParam', {postData: queryConditions}).trigger('reloadGrid');
                        } else {
                            parent.$.dialog.tips('取消拒绝失败！' + res.msg, 2);
                        }
                    });
                });
            }
        });
        $(window).on('resize', function() {
            Public.resizeGrid();
        });
    },
    callback: function(a, b, c, d) {
        $('#grid').jqGrid('setGridParam', {postData: queryConditions}).trigger('reloadGrid');
        c && c.api.close();
    }
};
$(function() {
    THISPAGE.init();
});
