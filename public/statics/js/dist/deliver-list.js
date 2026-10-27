var urlParam = Public.urlParam();
var defaultPage = Public.getDefaultPage();
var queryConditions = {
    beginDate: '',
    endDate: '',
    skey: urlParam.skey || ''
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
            url: '/Bill/Deliver/list',
            postData: queryConditions,
            datatype: 'json',
            colModel: [{
                name: 'billNo',
                label: '<span class="ml10">单据编号</span>',
                width: 160,
                fixed: !0,
                sortable: !0,
                classes: 'ti10'
            }, {
                name: 'company',
                label: '快递公司',
                width: 66,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'deliverDate',
                label: '统计月份',
                width: 66,
                fixed: !0,
                sortable: !0
            }, {
                name: 'amount1',
                label: '对方总额',
                width: 100,
                fixed: !0,
                sortable: !0,
                formatter: 'currency'
            }, {
                name: 'amount2',
                label: '我方总额',
                width: 100,
                fixed: !0,
                sortable: !0,
                formatter: 'currency'
            }, {
                name: 'amount',
                label: '最终需付总额',
                width: 100,
                fixed: !0,
                sortable: !0,
                formatter: 'currency'
            }, {
                name: 'description',
                label: '备注',
                width: 100,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray'
            }, {
                name: 'billDate',
                label: '制单日期',
                width: 70,
                fixed: !0,
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
                    if (c.isDraft == 1) {
                        return '<span class="db h28 lh28 b-r-3 hover-gray" title="目前为草稿 无法进行更多操作">草稿</span>';
                    }
                    var d = a == 0 ? '未审' : '已审';
                    var e = a == 0 ? 'hover-red' : 'hover-green';
                    var f = a == 0 ? '点击审核' : '点击反审核';
                    return '<span class="check-btn db h28 lh28 b-r-3 ' + e + '" title="' + f + '" data-id="' + c.id + '" data-checkid="' + a + '">' + d + '</span>';
                }
            }, {
                name: 'moneyStatus1',
                label: '付款',
                width: 42,
                fixed: !0,
                sortable: !0,
                align: 'center',
                formatter: function(a, b, c) {
                    if (c.isDraft == 1) {
                        return '<span class="db h28 lh28 b-r-3 hover-gray" title="目前为草稿 无法进行更多操作">草稿</span>';
                    }
                    var d = a == 0 ? '未付' : (a == 1 ? '部分' : '付完');
                    var f = a == 0 ? 'hover-red' : (a == 1 ? 'hover-yellow' : 'hover-green');
                    var g = c.srcMoneyId1 == 0 ? '点击付款' : '点击打开快递付款单' + '\r\n' + c.srcMoneyNo1;
                    return '<span class="moneyStatus1-btn db h28 lh28 b-r-3 ' + f + '" title="' + g + '" data-id="' + c.id + '" data-checkid="' + c.checkId + '" data-srcmoneyid1="' + c.srcMoneyId1 + '">' + d + '</span>';
                }
            }, {
                name: 'moneyStatus2',
                label: '退款',
                width: 42,
                fixed: !0,
                sortable: !0,
                align: 'center',
                formatter: function(a, b, c) {
                    if (c.isDraft == 1) {
                        return '<span class="db h28 lh28 b-r-3 hover-gray" title="目前为草稿 无法进行更多操作">草稿</span>';
                    }
                    var d = a == 0 ? '暂无' : (a == 1 ? '部分' : '退完');
                    var f = a == 0 ? 'hover-white' : (a == 1 ? 'hover-yellow' : 'hover-green');
                    var g = c.srcMoneyId2 == 0 ? '点击退款' : '点击打开快递退款单' + '\r\n' + c.srcMoneyNo2;
                    return '<span class="moneyStatus2-btn db h28 lh28 b-r-3 ' + f + '" title="' + g + '" data-id="' + c.id + '" data-checkid="' + c.checkId + '" data-srcmoneyid2="' + c.srcMoneyId2 + '">' + d + '</span>';
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
                        var d = c.isIgnoreInvoice == 2 ? 'hover-green' : (a > 0 ? (c.invoiceAmount > c.amount ? 'hover-purple' : (c.invoiceAmount == c.amount ? 'hover-green' : 'hover-yellow')) : 'hover-red');
                        var e = a > 0 ? (c.invoiceAmount > c.amount ? '<br>已收：' + c.invoiceAmount + '元（超额）' : (c.invoiceAmount == c.amount ? '<br>已收：' + c.invoiceAmount + '元（足额）' : '<br>已收：' + c.invoiceAmount + '元（部分）')) : '';
                        var f = '<span class="ignoreReq-btn db fl ml1 w70 tc h28 lh28 hover-white b-r-3" title="点击申请白条入账" data-id="' + c.id + '">白条入账</span>';
                        if (c.isIgnoreInvoice == 2) {
                            f = '<span class="db fl ml1 w70 tc h28 lh28 hover-green b-r-3" title="管理员已同意白条入账">已同意白条</span>';
                        } else if (c.isIgnoreInvoice == 1) {
                            f = '<span class="ignoreReq-btn db fl ml1 w70 tc h28 lh28 hover-dark b-r-3" title="管理员已拒绝白条入账<br>拒绝原因：' + c.disagreeIgnoreReason + '<br>点击可再次申请白条入账" data-id="' + c.id + '">已拒绝白条</span>';
                        } else if (c.isIgnoreInvoice == 0 && c.ignoreInvoiceReason != '') {
                            f = '<span class="ignoreEdit-btn db fl ml1 w70 tc h28 lh28 hover-red b-r-3" title="您已申请过白条入账<br>申请原因：' + c.ignoreInvoiceReason + '<br>点击可重新编辑" data-id="' + c.id + '">白条待审批</span>';
                        } else if (a > 0 && c.invoiceAmount >= c.amount) {
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
        $('#search').on('click', function() {
            queryConditions.beginDate = $('#beginDate').val();
            queryConditions.endDate = $('#endDate').val();
            queryConditions.skey = $.trim($('#skey').val()).split(/[ ,，]+/).toString();
            $('#grid').jqGrid('setGridParam', {
                postData: queryConditions,
                page: 1
            }).trigger('reloadGrid');
        });
        $('#upfile_1').uploader({
            /* 服务端脚本 */
            action: '/Bill/Deliver/uploadExcel_1',
            /* 上传模式，html5/flash */
            mode: 'html5',
            /* 字段名 */
            name: 'file',
            formData: {},
            /* 超时时间 秒 */
            callee: 300,
            /* 是否多选 */
            multiple: false,
            /* 是否自动上传 */
            auto: true,
            debug: false,
            preview: false,
            /* 显示队列的位置（传递jQuery选择器自定义队列显示的元素，传递true自动生成队列） */
            showQueue: '#queue',
            /* 文件大小限制（'100kb' '5M' 等） */
            fileSizeLimit: '2G',
            /* 可选择的文件的描述，用中竖线分组。此字符串出现在浏览文件对话框的文件类型下拉中 */
            fileTypeDesc: '选择文件',
            /* 允许上传的文件类型类表，用逗号分隔多个扩展，用中竖线分组（eg: 'jpg,jpeg,gif,png | zip,rar'） */
            fileTypeExts: 'xls,xlsx,csv',
            onInit: function() {
            },
            /* 选择文件 */
            onSelected: function(filelist) {
            },
            /* 取消选择文件 */
            onCancel: function(file) {
            },
            /* 开始上传 */
            onStart: function(e) {
                $('.wrapper').append('<div id="div-lock"><div class="loading">自动处理中...</div></div>');
            },
            /* 上传进行中 */
            onProgress: function(e) {
            },
            /* 上传发生错误 */
            onError: function(e) {
                $('.wrapper > #div-lock').remove();
                var data = JSON.parse(e.data);
                if (200 == data.status) {
                    $.dialog.tips(data.msg);
                } else {
                    $.dialog.tips(data.msg, 2);
                }
            },
            /* 上传成功 */
            onSuccess: function(e) {
                $('.wrapper > #div-lock').remove();
            },
            /* 单个文件处理完成（error or success） */
            onComplete: function(e) {
                $('.wrapper > #div-lock').remove();
                var data = JSON.parse(e.data);
                if (200 == data.status) {
                    $.dialog.tips(data.msg);
                } else {
                    $.dialog.tips(data.msg, 2);
                }
            },
            /* 全部文件处理完成（error or success） */
            onAllComplete: function() {
                $('#grid').jqGrid('setGridParam', {
                    postData: queryConditions,
                    page: 1
                }).trigger('reloadGrid');
            },
            /* 清空队列 */
            onClearQueue: function() {
            }
        });
        $('.upload-el').on({
            click: function(a) {
                !Business.verifyRight('DELIVER_ADD') && a.preventDefault();
            },
            mouseover: function() {
                $(this).prev().addClass('ui-btn-hover');
            },
            mouseleave: function() {
                $(this).prev().removeClass('ui-btn-hover');
            }
        });
        $('#refresh').on('click', function() {
            window.location.reload();
        });
        $('.grid-wrap').on('click', '.ui-icon-pencil', function(a) {
            var id = $(this).parent().data('id');
            id && parent.tab.addTabItem({
                tabid: 'deliver-deliver' + id,
                text: '<i class="iconfont icon-danye f14 mr5"></i>快递月结单',
                url: '/Uri/deliver_manage?id=' + id
            });
        });
        $('.grid-wrap').on('click', '.ui-icon-trash', function(a) {
            if (Business.verifyRight('DELIVER_DELETE')) {
                var id = $(this).parent().data('id');
                id && $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                    Public.ajaxGet('/Bill/Deliver/delete', {
                        id: id
                    }, function(res) {
                        if (res && 200 == res.status) {
                            $.dialog.tips('删除成功！');
                            $('#grid').jqGrid('delRowData', id);
                            parent.frames['deliver-deliver' + id] && parent.tab.removeTabItem('deliver-deliver' + id);
                        } else {
                            $.dialog.tips('删除失败！' + res.msg, 2);
                        }
                    });
                });
            }
        });
        $('.grid-wrap').on('click', '.check-btn', function(a) {
            var that = $(this);
            if (Business.verifyRight('DELIVER_CHECK')) {
                var id = $(this).data('id');
                var checkId = Number($(this).data('checkid')) > 0 ? 0 : defaultPage.SYSTEM.uId;
                var checkName = Number($(this).data('checkid')) > 0 ? "" : defaultPage.SYSTEM.uName;
                var rowData = $('#grid').jqGrid('getRowData', id);
                id && rowData && Public.ajaxPost('/Bill/Deliver/check', {
                    id: id,
                    checkId: checkId,
                    checkName: checkName,
                    billNo: rowData.billNo,
                    transType: 109101
                }, function(res) {
                    if (res && 200 == res.status) {
                        $.dialog.tips((checkId > 0 ? '审核' : '反审核') + '成功！');
                        that.parent().next().children('.moneyStatus1-btn').data('checkid', checkId);
                        that.parent().next().next().children('.moneyStatus2-btn').data('checkid', checkId);
                        $('#grid').jqGrid('setCell', id, 'checkId', checkId);
                        parent.frames['deliver-deliver' + id] && parent.frames['deliver-deliver' + id].location.reload();
                    } else {
                        $.dialog.tips((checkId > 0 ? '审核' : '反审核') + '失败！' + res.msg, 2);
                    }
                });
            }
        });
        $('.grid-wrap').on('click', '.moneyStatus1-btn', function(a) {
            a.preventDefault();
            if (Business.verifyRight('PAYMENT_QUERY')) {
                var id = $(this).data('id');
                var checkid = $(this).data('checkid');
                var srcmoneyid1 = $(this).data('srcmoneyid1');
                var rowData = $('#grid').jqGrid('getRowData', id);
                if (checkid == 0) {
                    $.dialog.alert('单据未审核，请立即审核！');
                } else {
                    if (srcmoneyid1 > 0) {
                        parent.tab.addTabItem({
                            tabid: 'money-payment' + srcmoneyid1,
                            text: '<i class="iconfont icon-danye f14 mr5"></i>快递付款单',
                            url: '/Uri/payment_manage?id=' + srcmoneyid1
                        });
                    } else {
                        var deliverCompany = {};
                        Public.ajaxPost('/Base/DeliverCompany/queryAllContactId', {
                            async: !1
                        }, function(res) {
                            if (200 == res.status) {
                                deliverCompany = res.data;
                            }
                        });
                        if (id && rowData) {
                            var newTabId = (new Date).format("yyyyMMddhhmmss");
                            parent.tab.addTabItem({
                                tabid: 'money-payment' + newTabId,
                                text: '<i class="iconfont icon-danye f14 mr5"></i>快递付款单（新建）',
                                url: '/Uri/payment_manage?turn&tabId=money-payment' + newTabId,
                                data: {
                                    id: id,
                                    no: rowData.billNo,
                                    transType: 109101,
                                    amount: rowData.amount,
                                    contactId: deliverCompany[rowData.company],
                                    contactName: rowData.company,
                                    typeId: 277,
                                    typeName: '快递费',
                                    uId: defaultPage.SYSTEM.uId,
                                    uName: defaultPage.SYSTEM.uName
                                }
                            });
                        }
                    }
                }
            }
        });
        $('.grid-wrap').on('click', '.moneyStatus2-btn', function(a) {
            a.preventDefault();
            if (Business.verifyRight('RECEIPT_QUERY')) {
                var id = $(this).data('id');
                var checkid = $(this).data('checkid');
                var srcmoneyid2 = $(this).data('srcmoneyid2');
                var rowData = $('#grid').jqGrid('getRowData', id);
                if (checkid == 0) {
                    $.dialog.alert('单据未审核，请立即审核！');
                } else {
                    if (srcmoneyid2 > 0) {
                        parent.tab.addTabItem({
                            tabid: 'money-receipt' + srcmoneyid2,
                            text: '<i class="iconfont icon-danye f14 mr5"></i>快递退款单',
                            url: '/Uri/receipt_manage?id=' + srcmoneyid2
                        });
                    } else {
                        var deliverCompany = {};
                        Public.ajaxPost('/Base/DeliverCompany/queryAllContactId', {
                            async: !1
                        }, function(res) {
                            if (200 == res.status) {
                                deliverCompany = res.data;
                            }
                        });
                        if (id && rowData) {
                            var newTabId = (new Date).format("yyyyMMddhhmmss");
                            parent.tab.addTabItem({
                                tabid: 'money-receipt' + newTabId,
                                text: '<i class="iconfont icon-danye f14 mr5"></i>快递退款单（新建）',
                                url: '/Uri/receipt_manage?turn&tabId=money-receipt' + newTabId,
                                data: {
                                    id: id,
                                    no: rowData.billNo,
                                    transType: 109101,
                                    amount: rowData.amount,
                                    contactId: deliverCompany[rowData.company],
                                    contactName: rowData.company,
                                    typeId: 328,
                                    typeName: '其他支出款项退回',
                                    uId: defaultPage.SYSTEM.uId,
                                    uName: defaultPage.SYSTEM.uName
                                }
                            });
                        }
                    }
                }
            }
        });
        $('.grid-wrap').on('click', '.invoice-btn', function(a) {
            a.preventDefault();
            if (Business.verifyRight('INVOICE_ADD')) {
                var id = $(this).data('id');
                var rowData = $('#grid').jqGrid('getRowData', id);
                var data = {
                    transType: rowData.transType,
                    billId: rowData.id,
                    billNo: rowData.billNo,
                    otherSide: rowData.company,
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
            if (Business.verifyRight('DELIVER_UPDATE')) {
                var id = $(this).data('id');
                var rowData = $('#grid').jqGrid('getRowData', id);
                if (id) {
                    if (rowData.uName == defaultPage.SYSTEM.uName) {
                        $.dialog({
                            title: '申请白条入账',
                            content: 'url:/Uri/invoice_miss_ignore',
                            data: {
                                oper: 'add',
                                billType: 'deliver',
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
            if (Business.verifyRight('DELIVER_UPDATE')) {
                var id = $(this).data('id');
                var rowData = $('#grid').jqGrid('getRowData', id);
                if (id) {
                    if (rowData.uName == defaultPage.SYSTEM.uName) {
                        $.dialog({
                            title: '编辑白条入账',
                            content: 'url:/Uri/invoice_miss_ignore',
                            data: {
                                oper: 'edit',
                                billType: 'deliver',
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
        $(window).on('resize', function() {
            Public.resizeGrid();
        });
    },
    callback: function() {
        $('#grid').jqGrid('setGridParam', {
            postData: queryConditions,
            page: 1
        }).trigger('reloadGrid');
    },
    ignoreCallback: function(a, b, c, d) {
        $('#grid').jqGrid('setGridParam', {postData: queryConditions}).trigger('reloadGrid');
        c && c.api.close();
    }
};
$(function() {
    THISPAGE.init();
});
