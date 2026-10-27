var defaultPage = Public.getDefaultPage();
var queryConditions = {
    reqId: 0,
    beginDate: '',
    endDate: '',
    skey: ''
};
var goodsShowAll = !1;
var combo1 = $('#combo1').combo({
    data: '/Base/User/queryAll?normal=yes',
    ajaxOptions: {
        formatData: function(a) {
            return a.data.rows.unshift({
                id: -1,
                name: '所有申请人'
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
        $('#beginDate').val(defaultPage.SYSTEM.beginDate);
        $('#endDate').val(defaultPage.SYSTEM.endDate);
    },
    initGrid: function() {
        var a = Public.setGrid();
        $('#grid').jqGrid({
            url: '/Bill/InvPc/listReq',
            postData: queryConditions,
            datatype: 'json',
            colModel: [{
                name: 'id',
                label: '<span class="ml10">ID</span>',
                width: 50,
                fixed: !0,
                sortable: !0,
                classes: 'ti10 gray'
            }, {
                name: 'billNo',
                label: '申请单号',
                width: 150,
                fixed: !0,
                sortable: !0
            }, {
                name: 'billDate',
                label: '申请日期',
                width: 70,
                fixed: !0,
                sortable: !0
            }, {
                name: 'endDate',
                label: '交货限期',
                width: 70,
                fixed: !0,
                sortable: !0
            }, {
                name: 'reqName',
                label: '申请人',
                width: 52,
                fixed: !0,
                sortable: !0
            }, {
                name: 'goods',
                label: '包含产品',
                nameExt: '<span class="goods-showall"><i class="iconfont icon-shuangxiajiantou f12 mr5"></i>展开全部</span>',
                width: 100,
                formatter: function(b, c, d) {
                    if (b) {
                        var e = b.length;
                        var f = '';
                        var g = 0;
                        if (0 < e) {
                            $.each(b, function(i, v) {
                                var goodsText =
                                    (v['no'] ? v['no'] + ' ' : '') +
                                    v['name'] +
                                    (v['spec'] ? ' ' + v['spec'] : '') +
                                    (v['sku'] ? ' ' + v['sku'] : '') +
                                    (v['brand'] ? ' ' + v['brand'] : '') +
                                    (v['qty'] ? ' × ' + Number(v['qty']) : '') +
                                    (v['unit'] ? v['unit'] : '') +
                                    (v['price'] ? ' × ' + Number(v['price']) + '元' : '') +
                                    (v['amount'] ? ' = ' + Number(v['amount']) + '元' : '');
                                f += ('<div class="goods-line es" title="' + goodsText + '">' + goodsText + '</div>');
                                g += parseFloat(v['qty']);
                            });
                        }
                        return (
                            '<div class="goods-total"><span class="goods-num-1">' +
                            e +
                            ' 种</span><span class="goods-num-2">' +
                            parseInt(g) +
                            ' 件</span><span class="goods-num-3 es">??? 元</span><span class="goods-showinfo iconfont icon-xiajiantou"></span></div><div class="goods-info">' +
                            f +
                            '</div>'
                        );
                    } else {
                        return '&nbsp;';
                    }
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
                name: 'reqInfo',
                label: '源单信息',
                width: 100,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray'
            }, {
                name: 'modifyStatus',
                label: '已编辑',
                width: 44,
                fixed: !0,
                sortable: !1,
                title: !0,
                formatter: function(a, b, c) {
                    return c.onModify == 1 ? '<span class="red">编辑中</span>' : (c.modifyTimes > 0 ? '<span class="black">' + c.modifyTimes + ' 次</span>' : '<span class="gray">无编辑</span>');
                }
            }, {
                name: 'quoteNum',
                label: '已报价',
                width: 54,
                fixed: !0,
                sortable: !0,
                formatter: function(a, b, c) {
                    return a + ' 份';
                }
            }, {
                name: 'quoteName',
                label: '正在报价',
                width: 66,
                fixed: !0,
                sortable: !0,
                title: !0,
                classes: 'ui-ellipsis'
            }, {
                name: '',
                label: '申请人操作',
                width: 142,
                fixed: !0,
                sortable: !1,
                align: 'center',
                formatter: function(a, b, c) {
                    return '<span class="modify-btn db fl pl10 pr10 h28 lh28 b-r-3-left hover-white" data-id="' + c.id + '" data-reqid="' + c.reqId + '" title="编辑时会中断报价">编辑</span><span class="confirm-modify-btn db fl ml-1 pl10 pr10 h28 lh28 hover-white" data-id="' + c.id + '" data-reqid="' + c.reqId + '" title="解除编辑中状态">解除</span><span class="cancel-btn db fl ml-1 pl10 pr10 h28 lh28 b-r-3-right hover-white" data-id="' + c.id + '" data-reqid="' + c.reqId + '" title="撤销将强行删除本申请，请谨慎操作！">撤销</span>';
                }
            }, {
                name: '',
                label: '报价人操作',
                width: 142,
                fixed: !0,
                sortable: !1,
                align: 'center',
                formatter: function(a, b, c) {
                    if (c.isDraft == 1) {
                        return '<span class="db h28 lh28 b-r-3 hover-gray" title="目前为草稿 无法进行更多操作">草稿</span>';
                    }
                    return '<span class="quote-btn db fl pl10 pr10 h28 lh28 b-r-3-left hover-white" data-id="' + c.id + '" title="报价或查看我的报价">报价</span><span class="confirm-quote-btn db fl ml-1 pl10 pr10 h28 lh28 hover-white" data-id="' + c.id + '" title="解除' + (defaultPage.SYSTEM.user.roleId == 1 ? '所有人的' : '我的') + '正在报价状态">解除</span>' + (c.forceTransStatus == 0 ? '<span class="trans-btn db fl ml-1 pl10 pr10 h28 lh28 b-r-3-right hover-white" data-id="' + c.id + '" title="自动提取最低报价并转为正式特殊采购单">转正</span>' : '<span class="allow-trans-btn db fl ml-1 pl10 pr10 h28 lh28 b-r-3-right hover-red" data-id="' + c.id + '" title="同意强制转正">强转</span>');
                }
            }, {
                name: '',
                label: '管理员操作',
                width: 76,
                fixed: !0,
                sortable: !1,
                align: 'center',
                formatter: function(a, b, c) {
                    if (c.isDraft == 1) {
                        return '<span class="db h28 lh28 b-r-3 hover-gray" title="目前为草稿 无法进行更多操作">草稿</span>';
                    }
                    return '<a class="showQuoteHis-btn db pl10 pr10 h28 lh28 b-r-3 hover-white" data-id="' + c.id + '" title="查看所有人报价记录">查看报价</a>';
                }
            }, {
                name: '',
                label: '公共操作',
                width: 54,
                fixed: !0,
                sortable: !1,
                align: 'center',
                formatter: function(a, b, c) {
                    if (c.isDraft == 1) {
                        return '<span class="db fl pl10 pr10 h28 lh28 b-r-3 hover-gray" title="目前为草稿 无法进行更多操作">草稿</span>';
                    }
                    return '<a class="export-btn db fl pl10 pr10 h28 lh28 b-r-3 hover-white" data-id="' + c.id + '" title="导出为EXCEL表格">导出</a>';
                }
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
            }
        });
    },
    initEvent: function() {
        laydate.render({
            elem: "#datePicker",
            type: 'date',
            range: ['#beginDate', '#endDate'],
            done: function() {
                setTimeout(function() {
                    $('#search').trigger('click');
                }, 300);
            }
        });
        $('#search').on('click', function(a) {
            a.preventDefault();
            queryConditions.reqId = combo1.getValue();
            queryConditions.beginDate = $('#beginDate').val() ? $.trim($('#beginDate').val()) : '';
            queryConditions.endDate = $('#endDate').val() ? $.trim($('#endDate').val()) : '';
            queryConditions.skey = $.trim($('#skey').val()).split(/[ ,，]+/).toString();
            $('#grid').jqGrid('setGridParam', {
                postData: queryConditions,
                page: 1
            }).trigger('reloadGrid');
        });
        $('#refresh').on('click', function() {
            window.location.reload();
        });
        $('#addFromCtSale').on('click', function(a) {
            a.preventDefault();
            if (Business.verifyRight('SC_QUERY')) {
                parent.tab.addTabItem({
                    tabid: 'sales-salesContract',
                    text: '<i class="iconfont icon-danju f14 mr5"></i>特殊销售',
                    url: '/Uri/salesContract_list'
                });
            }
        });
        $('#add').on('click', function(a) {
            a.preventDefault();
            if (Business.verifyRight('SC_ADD')) {
                var newTabId = (new Date).format("yyyyMMddhhmmss");
                parent.tab.addTabItem({
                    tabid: 'purchase-purchaseContractReq' + newTabId,
                    text: '<i class="iconfont icon-danye f14 mr5"></i>特采申请单（新建）',
                    url: '/Uri/purchaseContract_manage?newReq&tabId=purchase-purchaseContractReq' + newTabId
                });
            }
        });
        $('#grid').on('click', '.modify-btn', function(a) {
            a.preventDefault();
            var reqId = $(this).data('reqid');
            if ($.inArray('SYS_ADMIN', defaultPage.SYSTEM.rights) !== -1 || Number(reqId) == Number(defaultPage.SYSTEM.uId)) {
                var id = $(this).data('id');
                Public.ajaxPost('/Bill/InvPc/modifyCheck', {
                    id: id
                }, function(res) {
                    $('#search').trigger('click');
                    if (res && 200 == res.status) {
                        if (res.data.quoteName != '' || parseInt(res.data.quoteNum) > 0) {
                            $.dialog.confirm('<p class="f14">采购已报价或正在报价，现在编辑将强行中断报价且不允许转正！确定进行编辑？</p><p class="mt5 pl10 pr10 tl lh30 white bg-rgba3 b-r-3">特别注意：编辑了某项内容必须详细备注，并通知采购部，否则不知道强行改了什么！</p>', function() {
                                parent.tab.addTabItem({
                                    tabid: 'purchase-purchaseContract' + id,
                                    text: '<i class="iconfont icon-danye f14 mr5"></i>特采申请单',
                                    url: '/Uri/purchaseContract_manage?id=' + id + '&modifyReq'
                                });
                            });
                        } else {
                            parent.tab.addTabItem({
                                tabid: 'purchase-purchaseContract' + id,
                                text: '<i class="iconfont icon-danye f14 mr5"></i>特采申请单',
                                url: '/Uri/purchaseContract_manage?id=' + id + '&modifyReq'
                            });
                        }
                    } else {
                        $.dialog.alert('不可编辑！' + res.msg);
                    }
                });
            } else {
                $.dialog.tips('不能编辑别人发起的特采申请！仅本人或管理员可编辑！', 2);
            }
        });
        $('#grid').on('click', '.confirm-modify-btn', function(a) {
            a.preventDefault();
            var reqId = $(this).data('reqid');
            if ($.inArray('SYS_ADMIN', defaultPage.SYSTEM.rights) !== -1 || Number(reqId) == Number(defaultPage.SYSTEM.uId)) {
                var id = $(this).data('id');
                var rowData = $('#grid').jqGrid('getRowData', id);
                Public.ajaxPost('/Bill/InvPc/confirmReqModify', {
                    id: id,
                    billNo: rowData.billNo
                }, function(res) {
                    if (res && 200 == res.status) {
                        $.dialog.tips('解除成功！');
                        $('#search').trigger('click');
                    } else {
                        $.dialog.tips('解除失败！' + res.msg, 2);
                    }
                });
            } else {
                $.dialog.tips('不能解除别人的编辑状态！仅本人或管理员可解除！', 2);
            }
        });
        $('#grid').on('click', '.cancel-btn', function(a) {
            a.preventDefault();
            var reqId = $(this).data('reqid');
            if ($.inArray('SYS_ADMIN', defaultPage.SYSTEM.rights) !== -1 || Number(reqId) == Number(defaultPage.SYSTEM.uId)) {
                var id = $(this).data('id');
                id && $.dialog.confirm('<p class="f14">撤销将强行中断报价并删除报价信息，撤销的申请不能恢复，确定强行撤销？</p><p class="mt5 pl10 pr10 lh30 white bg-rgba3 b-r-3">特别注意：禁止随意撤销被报价的申请！如必须要撤销，请立即且正式的通知采购部！</p>', function() {
                    Public.ajaxPost('/Bill/InvPc/cancelReq', {
                        id: id
                    }, function(res) {
                        if (res && 200 == res.status) {
                            $.dialog.tips('撤销成功！');
                            $('#grid').jqGrid('delRowData', id);
                        } else {
                            $.dialog.tips('撤销失败！' + res.msg, 2);
                        }
                    });
                });
            } else {
                $.dialog.tips('不能撤销别人发起的特采申请！仅本人或管理员可撤销！', 2);
            }
        });
        $('#grid').on('click', '.quote-btn', function(a) {
            a.preventDefault();
            if (Business.verifyRight('PURREQ_QUERY')) {
                var id = $(this).data('id');
                Public.ajaxPost('/Bill/InvPc/upQuoteName', {
                    id: id,
                    type: 1
                }, function(res) {
                    if (res && 200 == res.status) {
                        $('#search').trigger('click');
                        $.dialog({
                            title: '我的特采申请报价',
                            content: 'url:/Uri/purchaseContractReq_quote',
                            data: {
                                rowId: id,
                                callback: THISPAGE.callback
                            },
                            width: $(window).width() - 10,
                            height: $(window).height() - 100,
                            left: 5,
                            top: 5,
                            max: !0,
                            min: !0,
                            lock: !0,
                            cache: !1
                        });
                    } else {
                        $.dialog.tips(res.msg, 2);
                    }
                });
            }
        });
        $('#grid').on('click', '.confirm-quote-btn', function(a) {
            a.preventDefault();
            if (Business.verifyRight('PURREQ_QUERY')) {
                var id = $(this).data('id');
                var rowData = $('#grid').jqGrid('getRowData', id);
                if (defaultPage.SYSTEM.user.roleId == 1) {
                    $.dialog.confirm('管理员点击解除按钮，将解除所有人的正在报价状态！确定解除？', function() {
                        Public.ajaxPost('/Bill/InvPc/confirmReqQuote', {
                            id: id,
                            billNo: rowData.billNo,
                            reqName: rowData.uName,
                            uId: defaultPage.SYSTEM.uId,
                            uName: defaultPage.SYSTEM.uName
                        }, function(res) {
                            if (res && 200 == res.status) {
                                $.dialog.tips('解除成功！');
                                $('#search').trigger('click');
                            } else {
                                $.dialog.tips('解除失败！' + res.msg, 2);
                            }
                        });
                    });
                } else {
                    Public.ajaxPost('/Bill/InvPc/confirmReqQuote', {
                        id: id,
                        billNo: rowData.billNo,
                        reqName: rowData.uName,
                        uId: defaultPage.SYSTEM.uId,
                        uName: defaultPage.SYSTEM.uName
                    }, function(res) {
                        if (res && 200 == res.status) {
                            $.dialog.tips('解除成功！');
                            $('#search').trigger('click');
                        } else {
                            $.dialog.tips('解除失败！' + res.msg, 2);
                        }
                    });
                }
            }
        });
        $('#grid').on('click', '.trans-btn', function(a) {
            a.preventDefault();
            if (Business.verifyRight('PC_TRANS')) {
                $('.wrapper').append('<div id="div-lock"><div class="loading">转正计算中...</div></div>');
                var id = $(this).data('id');
                var rowData = $('#grid').jqGrid('getRowData', id);
                Public.ajaxPost('/Bill/InvPc/transReq', {
                    type: 0,
                    id: id,
                    billNo: rowData.billNo,
                    uId: defaultPage.SYSTEM.uId,
                    uName: defaultPage.SYSTEM.uName
                }, function(res) {
                    if (res && 200 == res.status) {
                        $('#div-lock').remove();
                        $('#grid').jqGrid('delRowData', id);
                        parent.frames['purchase-purchaseContract'] && parent.frames['purchase-purchaseContract'].$('#search').trigger('click');
                        parent.tab.addTabItem({
                            tabid: 'purchase-purchaseContract' + id,
                            text: '<i class="iconfont icon-danye f14 mr5"></i>特殊采购单',
                            url: '/Uri/purchaseContract_manage?id=' + id
                        });
                    } else if (res && -2 == res.status) {
                        $('#div-lock').remove();
                        $.dialog.confirm(res.msg + '无法正常转正！是否确定发起强制转正？发起强制转正后需通知采购经理同意强制转正！', function() {
                            Public.ajaxPost('/Bill/InvPc/transReq', {
                                type: 1,
                                id: id,
                                billNo: rowData.billNo,
                                uId: defaultPage.SYSTEM.uId,
                                uName: defaultPage.SYSTEM.uName
                            }, function(res) {
                                if (res && 200 == res.status) {
                                    $.dialog.tips('转正进入待同意状态！');
                                    $('#search').trigger('click');
                                } else {
                                    $.dialog.tips('转正失败！' + res.msg, 2);
                                }
                            });
                        });
                    } else if (res && -3 == res.status) {
                        $('#div-lock').remove();
                        $.dialog.tips('转正失败！' + res.msg, 2);
                    } else {
                        $('#div-lock').remove();
                        $.dialog.tips('转正失败！' + res.msg, 2);
                    }
                });
            }
        });
        $('#grid').on('click', '.allow-trans-btn', function(a) {
            a.preventDefault();
            if (Business.verifyRight('PUR_MANAGER')) {
                $('.wrapper').append('<div id="div-lock"><div class="loading">转正计算中...</div></div>');
                var id = $(this).data('id');
                var rowData = $('#grid').jqGrid('getRowData', id);
                Public.ajaxPost('/Bill/InvPc/allowTransReq', {
                    id: id,
                    billNo: rowData.billNo,
                    uId: defaultPage.SYSTEM.uId,
                    uName: defaultPage.SYSTEM.uName
                }, function(res) {
                    if (res && 200 == res.status) {
                        $('#div-lock').remove();
                        $('#grid').jqGrid('delRowData', id);
                        parent.frames['purchase-purchaseContract'] && parent.frames['purchase-purchaseContract'].$('#search').trigger('click');
                        parent.tab.addTabItem({
                            tabid: 'purchase-purchaseContract' + id,
                            text: '<i class="iconfont icon-danye f14 mr5"></i>特殊采购单',
                            url: '/Uri/purchaseContract_manage?id=' + id
                        });
                    } else {
                        $('#div-lock').remove();
                        $.dialog.tips('转正失败！' + res.msg, 2);
                    }
                });
            }
        });
        $('#grid').on('click', '.showQuoteHis-btn', function(a) {
            a.preventDefault();
            if (Business.verifyRight('SYS_ADVANCE')) {
                var id = $(this).data('id');
                id && $.dialog({
                    title: '特采申请报价记录',
                    content: 'url:/Uri/purchaseContractReq_quote_info',
                    data: {
                        id: id
                    },
                    width: $(window).width() - 10,
                    height: $(window).height() - 100,
                    left: 5,
                    top: 5,
                    max: !0,
                    min: !0,
                    lock: !0,
                    cache: !1
                });
            }
        });
        $('#grid').on('click', '.export-btn', function(a) {
            var id = $(this).data('id');
            var b = '/Bill/InvPc/exportReqInfo?id=' + id;
            $(this).attr('href', b);
        });
        $('#grid').on('click', '.goods-showinfo', function(a) {
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
    },
    callback: function(a, b, c) {
        $('#search').trigger('click');
        c && c.api.close();
    }
};
$(function() {
    THISPAGE.init();
});
