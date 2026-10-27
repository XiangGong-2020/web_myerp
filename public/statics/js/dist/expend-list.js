var urlParam = Public.urlParam();
var defaultPage = Public.getDefaultPage();
var queryConditions = {
    transType: 103104,
    beginDate: '',
    endDate: '',
    minAmount: '',
    maxAmount: '',
    skey: urlParam.skey || '',
    unBack261: 0
};
var THISPAGE = {
    init: function() {
        THISPAGE.initDom();
        THISPAGE.initGrid();
        THISPAGE.initEvent();
    },
    initDom: function() {
        $('#beginDate').val(defaultPage.SYSTEM.beginDate);
        $('#endDate').val(defaultPage.SYSTEM.endDate);
        if (queryConditions.skey) {
            $('#skey').val(queryConditions.skey);
        }
    },
    initGrid: function() {
        var a = Public.setGrid();
        $('#grid').jqGrid({
            url: '/Bill/MonIo/list',
            postData: queryConditions,
            datatype: 'json',
            colModel: [{
                name: 'billNo',
                label: '<span class="ml10">单据编号</span>',
                width: 160,
                fixed: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'billDate',
                label: '单据日期',
                width: 80,
                fixed: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'amount',
                label: '付款总额',
                width: 80,
                fixed: !0,
                sortable: !0,
                classes: 'ui-ellipsis',
                formatter: 'currency'
            }, {
                name: 'contactName',
                label: '付款对象',
                width: 200,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'srcReqId',
                label: '原申请',
                width: 54,
                fixed: !0,
                sortable: !0,
                align: 'center',
                classes: 'silver',
                formatter: function(a, b, c) {
                    return (c.srcReqId > 0 && c.srcReqType > 0) ? ('<span class="srcreq-btn db h28 lh28 b-r-3 hover-white" title="点击查看原申请信息" data-srcreqid="' + c.srcReqId + '" data-srcreqtype="' + c.srcReqType + '">查看</span>') : '无';
                }
            }, {
                name: 'description',
                label: '备注',
                width: 100,
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
                name: 'checkId',
                label: '审核',
                width: 42,
                fixed: !0,
                sortable: !0,
                align: 'center',
                formatter: function(a, b, c) {
                    var d = 0 == a ? '未审' : '已审';
                    var e = 0 == a ? '点击审核' : '点击反审核';
                    var f = 0 == a ? 'hover-red' : 'hover-green';
                    return '<span class="check-btn db h28 lh28 b-r-3 ' + f + '" title="' + e + '" data-checkid="' + a + '" data-id="' + c.id + '">' + d + '</span>';
                }
            }, {
                name: 'isBack',
                label: '退回',
                width: 70,
                fixed: !0,
                sortable: !0,
                align: 'center',
                formatter: function(a, b, c) {
                    var d = a < 2 ? '一键退回' : (a == 2 ? '已退回' : '对冲单');
                    var e = a < 2 ? '点击一键退回本单款项' : '点击查看对应关联单据';
                    var f = a < 2 ? 'hover-white' : (a == 2 ? 'hover-green' : 'hover-silver');
                    return '<span class="moneyOnekeyBack-btn db h28 lh28 b-r-3 ' + f + '" title="' + e + '" data-id="' + c.id + '" data-checkid="' + c.checkId + '" data-isback="' + a + '" data-srcid="' + c.srcId + '" data-srcno="' + c.srcNo + '">' + d + '</span>';
                }
            }, {
                name: 'invoiceNum',
                label: '发票',
                width: 112,
                fixed: !0,
                sortable: !0,
                align: 'center',
                formatter: function(a, b, c) {
                    if (c.isDraft == 1) {
                        return '<span class="db h28 lh28 hover-gray b-r-3" title="目前为草稿 无法进行更多操作">草稿</span>';
                    } else {
                        var d = c.isIgnoreInvoice == 2 ? 'hover-green' : (c.isBack >= 2 ? 'hover-white' : (a > 0 ? (c.invoiceAmount > c.amount ? 'hover-purple' : (c.invoiceAmount == c.amount ? 'hover-green' : 'hover-yellow')) : 'hover-red'));
                        var e = a > 0 ? (c.invoiceAmount > c.amount ? '<br>已收：' + c.invoiceAmount + '元（超额）' : (c.invoiceAmount == c.amount ? '<br>已收：' + c.invoiceAmount + '元（足额）' : '<br>已收：' + c.invoiceAmount + '元（部分）')) : '';
                        var f = '<span class="ignoreReq-btn db fl ml1 w70 tc h28 lh28 hover-white b-r-3" title="点击申请白条入账" data-id="' + c.id + '">白条入账</span>';
                        if (c.isIgnoreInvoice == 2) {
                            f = '<span class="db fl ml1 w70 tc h28 lh28 hover-green b-r-3" title="管理员已同意白条入账">已同意白条</span>';
                        } else if (c.isIgnoreInvoice == 1) {
                            f = '<span class="ignoreReq-btn db fl ml1 w70 tc h28 lh28 hover-dark b-r-3" title="管理员已拒绝白条入账<br>拒绝原因：' + c.disagreeIgnoreReason + '<br>点击可再次申请白条入账" data-id="' + c.id + '">已拒绝白条</span>';
                        } else if (c.isIgnoreInvoice == 0 && c.ignoreInvoiceReason != '') {
                            f = '<span class="ignoreEdit-btn db fl ml1 w70 tc h28 lh28 hover-red b-r-3" title="您已申请过白条入账<br>申请原因：' + c.ignoreInvoiceReason + '<br>点击可重新编辑" data-id="' + c.id + '">白条待审批</span>';
                        } else if ((a > 0 && c.invoiceAmount >= c.amount) || c.isBack >= 2) {
                            f = '';
                        }
                        return '<span class="invoice-btn db fl w30 tc h28 lh28 ' + d + ' b-r-3" title="点击管理发票' + e + '" data-id="' + c.id + '">' + a + '</span>' + f;
                    }
                }
            }, {
                name: 'operate',
                label: '操作',
                width: 72.8,
                fixed: !0,
                formatter: Public.operFmatter
            }, {
                name: 'id',
                label: 'id',
                width: 0,
                hidden: !0
            }, {
                name: 'transType',
                label: '业务类型',
                width: 0,
                hidden: !0
            }, {
                name: 'srcTransType',
                hidden: !0
            }],
            cmTemplate: {sortable: !1, title: !1},
            jsonReader: {root: 'data.rows', records: 'data.records', repeatitems: !1, total: 'data.total', id: 'id'},
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
            subGrid: !0,
            subGridWidth: 30,
            subGridOptions: {
                expandOnLoad: !1
            },
            subGridRowExpanded: function(subgrid_id, row_id) {
                var subgrid_table_id = subgrid_id + '_t';
                $('#' + subgrid_id).html('<div class="grid-wrap"><table id="' + subgrid_table_id + '"></table></div>');
                $('#' + subgrid_table_id).jqGrid({
                    url: '/Bill/MonIo/entries_list',
                    postData: {id: row_id},
                    datatype: 'json',
                    colModel: [{
                        name: 'amount',
                        label: '<span class="ml10">付款金额</span>',
                        width: 120,
                        fixed: !0,
                        editable: !1,
                        classes: 'ti10',
                        formatter: 'currency'
                    }, {
                        name: 'typeId',
                        label: '付款类别ID',
                        hidden: !0
                    }, {
                        name: 'typeName',
                        label: '付款类别',
                        width: 120,
                        fixed: !0,
                        title: !0,
                        editable: !1,
                        classes: 'ui-ellipsis'
                    }, {
                        name: 'accId',
                        label: '付款账户ID',
                        hidden: !0
                    }, {
                        name: 'accName',
                        label: '付款账户',
                        width: 120,
                        fixed: !0,
                        title: !0,
                        editable: !1,
                        classes: 'ui-ellipsis'
                    }, {
                        name: 'wayId',
                        label: '付款方式ID',
                        hidden: !0
                    }, {
                        name: 'wayName',
                        label: '付款方式',
                        width: 120,
                        fixed: !0,
                        title: !0,
                        editable: !1,
                        classes: 'ui-ellipsis'
                    }, {
                        name: 'settlementTime',
                        label: '付款时间',
                        width: 150,
                        fixed: !0,
                        title: !0,
                        editable: !1,
                        classes: 'ui-ellipsis gray'
                    }, {
                        name: 'uName',
                        label: '付款人',
                        width: 60,
                        fixed: !0,
                        editable: !1,
                        classes: 'gray'
                    }, {
                        name: 'description',
                        label: '备注',
                        width: 100,
                        title: !0,
                        editable: !1,
                        classes: 'ui-ellipsis gray'
                    }, {
                        name: 'id',
                        label: 'ID',
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
                    if (queryConditions.unBack261 == 1) {
                        var rows = data.data.rows;
                        for (var i = 0; i < rows.length; i++) {
                            if (rows[i].isBack == 1) {
                                $('#grid tr#' + rows[i].id + ' .ui-sgcollapsed').trigger('click');
                            }
                        }
                    }
                } else {
                    $.dialog.tips(data.msg, 2);
                }
            },
            loadError: function(a, b) {
                $.dialog.tips('Type: ' + b + '; Response: ' + a.status + ' ' + a.statusText, 2);
            },
            ondblClickRow: function(a, b, c, d) {
                $('#' + a).find('.ui-icon-pencil').trigger('click');
            }
        });
    },
    initEvent: function() {
        laydate.render({
            elem: "#datePicker",
            range: ['#beginDate', '#endDate'],
            done: function() {
                setTimeout(function() {
                    $('#search').parent().find('.current').trigger('click');
                }, 300);
            }
        });
        $('#search').on('click', function() {
            $(this).addClass('current').siblings().removeClass('current');
            queryConditions.beginDate = $('#beginDate').val();
            queryConditions.endDate = $('#endDate').val();
            queryConditions.minAmount = $('#minAmount').val();
            queryConditions.maxAmount = $('#maxAmount').val();
            queryConditions.skey = $.trim($('#skey').val()).split(/[ ,，]+/).toString();
            queryConditions.unBack261 = 0;
            $('#grid').jqGrid('setGridParam', {
                postData: queryConditions,
                page: 1
            }).trigger('reloadGrid');
        });
        $('#searchUnBack261').on('click', function() {
            $(this).addClass('current').siblings().removeClass('current');
            queryConditions.beginDate = $('#beginDate').val();
            queryConditions.endDate = $('#endDate').val();
            queryConditions.minAmount = $('#minAmount').val();
            queryConditions.maxAmount = $('#maxAmount').val();
            queryConditions.skey = $.trim($('#skey').val()).split(/[ ,，]+/).toString();
            queryConditions.unBack261 = 1;
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
            if (Business.verifyRight('EXPEND_ADD')) {
                var newTabId = (new Date).format("yyyyMMddhhmmss");
                parent.tab.addTabItem({
                    tabid: 'money-expend' + newTabId,
                    text: '<i class="iconfont icon-danye f14 mr5"></i>其他支出单（新建）',
                    url: '/Uri/expend_manage?tabId=money-expend' + newTabId
                });
            }
        });
        $('.grid-wrap').on('click', '.ui-icon-pencil', function(a) {
            a.preventDefault();
            var id = $(this).parent().data('id');
            id && parent.tab.addTabItem({
                tabid: 'money-expend' + id,
                text: '<i class="iconfont icon-danye f14 mr5"></i>其他支出单',
                url: '/Uri/expend_manage?id=' + id
            });
        });
        $('.grid-wrap').on('click', '.ui-icon-trash', function(a) {
            a.preventDefault();
            if (Business.verifyRight('EXPEND_DELETE')) {
                var id = $(this).parent().data('id');
                id && $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                    Public.ajaxGet('/Bill/MonIo/delete', {
                        id: id
                    }, function(res) {
                        if (res && 200 == res.status) {
                            $.dialog.tips('删除成功！');
                            $('#grid').jqGrid('delRowData', id);
                            parent.frames['money-expend' + id] && parent.tab.removeTabItem('money-expend' + id);
                            parent.frames['money-income'] && parent.frames['money-income'].$('#search').trigger('click');
                            parent.frames['money-payReq'] && parent.frames['money-payReq'].$('#search').trigger('click');
                            parent.frames['money-expensesReq'] && parent.frames['money-expensesReq'].$('#search').trigger('click');
                        } else {
                            $.dialog.tips('删除失败！' + res.msg, 2);
                        }
                    });
                });
            }
        });
        $('.grid-wrap').on('click', '.srcreq-btn', function(a) {
            a.preventDefault();
            var srcReqId = $(this).data('srcreqid');
            var srcReqType = $(this).data('srcreqtype');
            if (srcReqType == 1) {
                var title = '原付款申请详情';
                var content = 'url:/Uri/payReq_manage';

            } else {
                var title = '原报销申请详情';
                var content = 'url:/Uri/expensesReq_manage';
            }
            srcReqId && $.dialog({
                title: title,
                content: content,
                data: {
                    oper: 'edit',
                    rowId: srcReqId
                },
                width: 1250,
                height: 500,
                max: !1,
                min: !1,
                cache: !1,
                lock: !0,
                disEdit: !0,
                button: [{
                    id: 'cancel',
                    name: '关闭'
                }]
            });
        });
        $('.grid-wrap').on('click', '.check-btn', function(a) {
            var that = $(this);
            if (Business.verifyRight('EXPEND_CHECK')) {
                var id = $(this).data('id');
                var checkId = Number($(this).data('checkid')) > 0 ? 0 : defaultPage.SYSTEM.uId;
                var checkName = Number($(this).data('checkid')) > 0 ? '' : defaultPage.SYSTEM.uName;
                var rowData = $('#grid').jqGrid('getRowData', id);
                id && Public.ajaxPost('/Bill/MonIo/check', {
                    id: id,
                    checkId: checkId,
                    checkName: checkName,
                    billNo: rowData.billNo,
                    transType: rowData.transType
                }, function(res) {
                    if (res && 200 == res.status) {
                        $.dialog.tips((checkId > 0 ? '审核' : '反审核') + '成功！');
                        that.parent().next().children('.moneyOnekeyBack-btn').data('checkid', checkId);
                        $('#grid').jqGrid('setCell', id, 'checkId', checkId);
                        parent.frames['money-expend' + id] && parent.frames['money-expend' + id].location.reload();
                    } else {
                        $.dialog.tips((checkId > 0 ? '审核' : '反审核') + '失败！' + res.msg, 2);
                    }
                });
            }
        });
        $('.grid-wrap').on('click', '.moneyOnekeyBack-btn', function(a) {
            a.stopPropagation();
            a.preventDefault();
            if (Business.verifyRight('EXPEND_UPDATE')) {
                var id = $(this).data('id');
                var checkid = $(this).data('checkid');
                var isback = $(this).data('isback');
                var srcId = $(this).data('srcid');
                var srcNo = $(this).data('srcno');
                if (checkid == 0) {
                    $.dialog.alert('单据未审核，请立即审核！');
                } else if (id && isback < 2) {
                    $.dialog.prompt('请准确输入实际退回金额', function(inputValue) {
                        var backAmount = $.trim(inputValue);
                        if ($.isNumeric(backAmount)) {
                            Public.ajaxPost('/Bill/MonIo/moneyOnekeyBack', {
                                id: id,
                                backAmount: backAmount
                            }, function(res) {
                                if (res && 200 == res.status) {
                                    $.dialog.tips('一键退回成功！系统已自动录入其他收入单进行对冲。');
                                    $('#grid').jqGrid('setRowData', id, res.data);
                                    parent.frames['money-income'] && parent.frames['money-income'].$('#search').trigger('click');
                                } else {
                                    $.dialog.tips('一键退回失败！' + res.msg, 2);
                                }
                            });
                        } else {
                            $.dialog.tips('金额格式必须为数字型字符！', 2);
                        }
                    });
                } else if (srcNo) {
                    if (srcId > 0) {
                        parent.tab.addTabItem({
                            tabid: 'money-income' + srcId,
                            text: '<i class="iconfont icon-danye f14 mr5"></i>其他收入单',
                            url: '/Uri/income_manage?id=' + srcId
                        });
                    } else {
                        $.dialog.tips((isback == 2 ? '对冲单号：' : '源单单号：') + srcNo, 1, 10);
                    }
                }
            }
        });
        $('.grid-wrap').on('click', '.invoice-btn', function(a) {
            a.preventDefault();
            var id = $(this).data('id');
            var rowData = $('#grid').jqGrid('getRowData', id);
            if (Business.verifyRight('INVOICE_ADD')) {
                var data = {
                    transType: rowData.transType,
                    billId: rowData.id,
                    billNo: rowData.billNo,
                    otherSide: rowData.contactName,
                    callback: THISPAGE.callback
                };
                $.dialog({
                    title: '发票管理',
                    content: 'url:/Uri/invoice_bill_manage',
                    data: data,
                    width: 1250,
                    height: 600,
                    min: !1,
                    max: !1,
                    cache: !1,
                    lock: !0
                });
            }
        });
        $('.grid-wrap').on('click', '.ignoreReq-btn', function(a) {
            a.preventDefault();
            if (Business.verifyRight('EXPEND_UPDATE')) {
                var id = $(this).data('id');
                var rowData = $('#grid').jqGrid('getRowData', id);
                if (id) {
                    if (rowData.uName == defaultPage.SYSTEM.uName) {
                        $.dialog({
                            title: '申请白条入账',
                            content: 'url:/Uri/invoice_miss_ignore',
                            data: {
                                oper: 'add',
                                billType: 'money',
                                type: 'ignoreReq',
                                id: id,
                                callback: THISPAGE.ignoreCallback
                            },
                            width: 400,
                            height: 260,
                            max: !1,
                            min: !1,
                            cache: !1,
                            lock: !0
                        });
                    } else {
                        $.dialog.tips('不能申请别人的单据白条入账！', 2);
                    }
                }
            }
        });
        $('.grid-wrap').on('click', '.ignoreEdit-btn', function(a) {
            a.preventDefault();
            if (Business.verifyRight('EXPEND_UPDATE')) {
                var id = $(this).data('id');
                var rowData = $('#grid').jqGrid('getRowData', id);
                if (id) {
                    if (rowData.uName == defaultPage.SYSTEM.uName) {
                        $.dialog({
                            title: '编辑白条入账',
                            content: 'url:/Uri/invoice_miss_ignore',
                            data: {
                                oper: 'edit',
                                billType: 'money',
                                type: 'ignoreReq',
                                id: id,
                                callback: THISPAGE.ignoreCallback
                            },
                            width: 400,
                            height: 260,
                            max: !1,
                            min: !1,
                            cache: !1,
                            lock: !0
                        });
                    } else {
                        $.dialog.tips('不能编辑别人的单据白条入账！', 2);
                    }
                }
            }
        });
        $(window).on('keydown', function(a) {
            var curKey = a.which;
            if (curKey == 13) {
                $('#beginDate, #endDate').blur();
                $('#search').parent().find('.current').trigger('click');
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
    },
    callback: function() {
        $('#search').parent().find('.current').trigger('click');
    },
    ignoreCallback: function(a, b, c, d) {
        $('#grid').jqGrid('setGridParam', {postData: queryConditions}).trigger('reloadGrid');
        c && c.api.close();
    }
};
$(function() {
    THISPAGE.init();
});
