var defaultPage = Public.getDefaultPage();
var queryConditions = {
    uId: 0,
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
                name: '所有发起人'
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
            url: '/Bill/InvBq/list',
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
                name: 'bidSiteName',
                label: '招标来源',
                width: 100,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis hover'
            }, {
                name: 'bidTitle',
                label: '招标标题',
                width: 100,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis hover'
            }, {
                name: 'billNo',
                label: '报价单号',
                width: 100,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'billDate',
                width: 0,
                hidden: !0
            }, {
                name: 'uName',
                label: '发起人',
                width: 54,
                fixed: !0,
                sortable: !0
            }, {
                name: 'offerEndTime',
                label: '报价截止',
                width: 124,
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
                                    (v['unit'] ? v['unit'] : '');
                                f += ('<div class="goods-line es" title="' + goodsText + '">' + goodsText + '</div>');
                                g += Number(v['qty']);
                            });
                        }
                        return (
                            '<div class="goods-total"><span class="goods-num-1">' +
                            e +
                            ' 种</span><span class="goods-num-2">' +
                            g +
                            ' 件</span><span class="goods-num-3 es"></span><span class="goods-showinfo iconfont icon-xiajiantou"></span></div><div class="goods-info">' +
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
                name: 'uId',
                width: 0,
                hidden: !0
            }, {
                name: 'contactId',
                width: 0,
                hidden: !0
            }, {
                name: 'contactName',
                width: 0,
                hidden: !0
            }, {
                name: 'sId',
                width: 0,
                hidden: !0
            }, {
                name: 'sName',
                width: 0,
                hidden: !0
            }, {
                name: 'amount',
                width: 0,
                hidden: !0
            }, {
                name: 'bidDate',
                width: 0,
                hidden: !0
            }, {
                name: 'bidSiteId',
                width: 0,
                hidden: !0
            }, {
                name: 'bidInfoId',
                width: 0,
                hidden: !0
            }, {
                name: 'bidUrl',
                width: 0,
                hidden: !0
            }, {
                name: 'contactInfo',
                width: 0,
                hidden: !0
            }, {
                name: 'quotedName',
                width: 0,
                hidden: !0
            }, {
                name: 'bidStatus',
                width: 0,
                hidden: !0
            }, {
                name: 'programStatus',
                width: 0,
                hidden: !0
            }, {
                name: 'contractStatus',
                width: 0,
                hidden: !0
            }, {
                name: 'programId',
                width: 0,
                hidden: !0
            }, {
                name: 'contractId',
                width: 0,
                hidden: !0
            }, {
                name: 'oprate',
                label: '操作',
                width: 430,
                fixed: !0,
                sortable: !1,
                align: 'center',
                formatter: function(a, b, c) {
                    var d = ('<span class="view-btn db fl pl10 pr10 h28 lh28 b-r-3-left hover-white" data-id="' + c.id + '" title="查看时不会中断报价">查看</span>')
                        + ('<span class="modify-btn db fl ml-1 pl10 pr10 h28 lh28 hover-white" data-id="' + c.id + '" title="编辑时会中断报价">编辑</span>')
                        + ('<span class="confirm-modify-btn db fl ml-1 pl10 pr10 h28 lh28 hover-white" data-id="' + c.id + '" title="解除编辑中状态（高级权限用户可同时解除正在报价状态）">解除</span>')
                        + ('<span class="cancel-btn db fl ml-1 pl10 pr10 h28 lh28 hover-white" data-id="' + c.id + '" title="建议仅删除未投标的无用报价单">删除</span>')
                        + (c.isDraft == 1 ? '<span class=" db fl ml-1 pl10 pr10 h28 lh28 b-r-3-right hover-gray" title="目前为草稿 无法进行更多操作">草稿</span>' : (
                            '<span class="quote-btn db fl ml-1 pl10 pr10 h28 lh28 b-r-3-right hover-white" data-id="' + c.id + '" title="报价或查看我的报价">报价</span>'
                            + (
                                c.bidStatus == 0 ? (
                                    '<span class="bidStatus-btn db fl ml5 pl5 pr5 h28 lh28 b-r-3 hover-gray" data-id="' + c.id + '" title="点击设为已投标">未投标</span>'
                                ) : (
                                    '<span class="bidStatus-btn db fl ml5 pl5 pr5 h28 lh28 b-r-3 hover-green" data-id="' + c.id + '" title="点击设为未投标">已投标</span>'
                                    + (
                                        c.programStatus == -1 ? (
                                            '<span class="programStatus-btn db fl ml5 pl5 pr5 h28 lh28 b-r-3 hover-red" data-id="' + c.id + '" title="点击转化为投标项目">未转投标项目</span>'
                                        ) : (
                                            c.programStatus == 0 ? (
                                                '<span class="programStatus-btn db fl ml5 pl5 pr5 h28 lh28 b-r-3 hover-red" data-id="' + c.id + '" title="点击查看投标项目">未开标</span>'
                                            ) : (
                                                c.programStatus == 1 ? (
                                                    '<span class="programStatus-btn db fl ml5 pl5 pr5 h28 lh28 b-r-3 hover-night" data-id="' + c.id + '" title="点击查看投标项目">未中标</span>'
                                                ) : (
                                                    c.programStatus == 2 ? (
                                                        '<span class="programStatus-btn db fl ml5 pl5 pr5 h28 lh28 b-r-3 hover-green" data-id="' + c.id + '" title="点击查看投标项目">已中标</span>'
                                                    ) : ''
                                                )
                                            )
                                        )
                                    )
                                )
                                + (
                                    c.programStatus == 2 ? (
                                        c.contractStatus == 0 ? (
                                            '<span class="contractStatus-btn db fl ml5 pl5 pr5 h28 lh28 b-r-3 hover-red" data-id="' + c.id + '" data-uid="' + c.uId + '" data-contractstatus="' + c.contractStatus + '" title="点击转化为特殊销售单">未转特殊销售单</span>'
                                        ) : (
                                            '<span class="contractStatus-btn db fl ml5 pl5 pr5 h28 lh28 b-r-3 hover-green" data-id="' + c.id + '" data-uid="' + c.uId + '" data-contractstatus="' + c.contractStatus + '" title="点击查看特殊销售单">已转特殊销售单</span>'
                                        )
                                    ) : ''
                                )
                            )
                        )
                        );
                    return d;
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
                data.ststus == -1 && $.dialog.tips(data.msg, 2);
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
            onCellSelect: function(a, b) {
                var c = $('#grid').jqGrid('getRowData', a);
                switch (b) {
                    case 1:
                        if (Business.verifyRight('BIDSITE_QUERY')) {
                            c.bidSiteId > 0 && $.dialog({
                                title: '查看招标网站',
                                content: 'url:/Uri/bid_site_manage',
                                data: {
                                    oper: 'editSite',
                                    rowId: c.bidSiteId
                                },
                                width: 800,
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
                        }
                        break;
                    case 2:
                        window.open(Public.fixUrl(c.bidUrl));
                        break;
                    default:
                        break;
                }
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
            queryConditions.uId = combo1.getValue();
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
        $('#toBidInfoList').on('click', function(a) {
            a.preventDefault();
            parent.tab.addTabItem({
                tabid: 'bid-bidInfo',
                text: '<i class="iconfont icon-xinxi f14 mr5"></i>招标信息',
                url: '/Uri/bid_info_list'
            });
        });
        $('#grid').on('click', '.view-btn', function(a) {
            a.preventDefault();
            var id = $(this).data('id');
            parent.tab.addTabItem({
                tabid: 'bid-bidQuote' + id,
                text: '<i class="iconfont icon-danye f14 mr5"></i>预算报价单（查看）',
                url: '/Uri/bid_quote_manage?view&id=' + id
            });
        });
        $('#grid').on('click', '.modify-btn', function(a) {
            a.preventDefault();
            var id = $(this).data('id');
            var rowData = $('#grid').jqGrid('getRowData', id);
            if ($.inArray('SYS_ADMIN', defaultPage.SYSTEM.rights) !== -1 || Number(rowData.uId) == Number(defaultPage.SYSTEM.uId)) {
                Public.ajaxPost('/Bill/InvBq/modifyCheck', {
                    id: id
                }, function(res) {
                    $('#search').trigger('click');
                    if (res && 200 == res.status) {
                        if (res.data.quoteName != '' || parseInt(res.data.quoteNum) > 0) {
                            $.dialog.confirm('<p class="f14">预算已报价或正在报价，现在进入编辑状态将强行中断报价！确定进入编辑？</p><p class="mt5 pl10 pr10 tl lh30 white bg-rgba3 b-r-3">特别注意：如果编辑了产品性质、数量等关键信息导致已报价不再准确，需自己纠正报价信息！</p>', function() {
                                parent.tab.addTabItem({
                                    tabid: 'bid-bidQuote' + id,
                                    text: '<i class="iconfont icon-danye f14 mr5"></i>预算报价单（编辑）',
                                    url: '/Uri/bid_quote_manage?id=' + id
                                });
                            });
                        } else {
                            parent.tab.addTabItem({
                                tabid: 'bid-bidQuote' + id,
                                text: '<i class="iconfont icon-danye f14 mr5"></i>预算报价单（编辑）',
                                url: '/Uri/bid_quote_manage?id=' + id
                            });
                        }
                    } else {
                        $.dialog.alert(res.msg);
                    }
                });
            } else {
                $.dialog.tips('不能编辑别人发起的预算报价单！仅本人或管理员可编辑！', 2);
            }
        });
        $('#grid').on('click', '.confirm-modify-btn', function(a) {
            a.preventDefault();
            var id = $(this).data('id');
            var rowData = $('#grid').jqGrid('getRowData', id);
            if ($.inArray('SYS_ADMIN', defaultPage.SYSTEM.rights) !== -1 || Number(rowData.uId) == Number(defaultPage.SYSTEM.uId)) {
                Public.ajaxPost('/Bill/InvBq/confirmModify', {
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
            }
        });
        $('#grid').on('click', '.cancel-btn', function(a) {
            a.preventDefault();
            var id = $(this).data('id');
            id && $.dialog.confirm('<p class="f14">删除将强行中断报价，删除的预算报价单不能恢复，确定强行删除？</p><p class="mt5 pl10 pr10 lh30 white bg-rgba3 b-r-3">特别注意：禁止随意删除已报价的预算报价单！如必须要删除，请立即且正式的通知相关报价人！</p>', function() {
                Public.ajaxPost('/Bill/InvBq/delete', {
                    id: id
                }, function(res) {
                    if (res && 200 == res.status) {
                        $.dialog.tips('删除成功！');
                        $('#grid').jqGrid('delRowData', id);
                        parent.frames['bid-bidInfo'] && parent.frames['bid-bidInfo'].$('#search').trigger('click');
                    } else {
                        $.dialog.tips('删除失败！' + res.msg, 2);
                    }
                });
            });
        });
        $('#grid').on('click', '.quote-btn', function(a) {
            a.preventDefault();
            var id = $(this).data('id');
            Public.ajaxPost('/Bill/InvBq/upQuoteName', {
                id: id,
                type: 1
            }, function(res) {
                if (res && 200 == res.status) {
                    $('#search').trigger('click');
                    $.dialog({
                        title: '我的预算报价',
                        content: 'url:/Uri/bid_quote_quote',
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
        });
        $('#grid').on('click', '.bidStatus-btn', function(a) {
            a.preventDefault();
            var id = $(this).data('id');
            var rowData = $('#grid').jqGrid('getRowData', id);
            if ($.inArray('SYS_ADMIN', defaultPage.SYSTEM.rights) !== -1 || Number(rowData.uId) == Number(defaultPage.SYSTEM.uId)) {
                if (id) {
                    Public.ajaxPost('/Bill/InvBq/upBidStatus', {
                        id: id,
                        bidStatus: Number(rowData.bidStatus) == 0 ? 1 : 0
                    }, function(res) {
                        if (res && 200 == res.status) {
                            res.data.oprate = ''; // 由于oprate列在返回值里不存在，必须补上，setRowData时才能更新操作列按钮状态
                            $('#grid').jqGrid('setRowData', id, res.data);
                        } else {
                            $.dialog.tips(res.msg, 2);
                        }
                    });
                }
            } else {
                $.dialog.tips('不能设置别人发起的预算报价单！仅本人或高级用户可设置！', 2);
            }
        });
        $('#grid').on('click', '.programStatus-btn', function(a) {
            a.preventDefault();
            var id = $(this).data('id');
            var rowData = $('#grid').jqGrid('getRowData', id);
            if (rowData.programStatus == -1 && Business.verifyRight('BIDPROGRAM_ADD')) {
                $.dialog({
                    title: '新增投标项目',
                    content: 'url:/Uri/bid_program_manage',
                    data: {
                        oper: 'add',
                        data: {
                            srcBqId: parseInt(id),
                            srcBqNo: rowData.billNo,
                            quotedName: rowData.quotedName,
                            amount: parseFloat(rowData.amount),
                            bidDate: rowData.bidDate,
                            contactId: parseInt(rowData.contactId),
                            contactName: rowData.contactName,
                            sId: parseInt(rowData.sId),
                            sName: rowData.sName,
                            bidSiteId: parseInt(rowData.bidSiteId),
                            bidSiteName: rowData.bidSiteName,
                            bidInfoId: parseInt(rowData.bidInfoId),
                            bidTitle: rowData.bidTitle,
                            bidUrl: Public.fixUrl(rowData.bidUrl),
                            contactInfo: rowData.contactInfo,
                            description: rowData.description
                        },
                        callback: THISPAGE.callback
                    },
                    width: 1250,
                    height: 600,
                    max: !1,
                    min: !1,
                    cache: !1,
                    lock: !0
                });
            } else {
                if (Business.verifyRight('BIDPROGRAM_QUERY')) {
                    $.dialog({
                        title: '查看投标项目',
                        content: 'url:/Uri/bid_program_manage',
                        data: {
                            oper: 'edit',
                            rowId: rowData.programId
                        },
                        width: 1250,
                        height: 600,
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
                }
            }
        });
        $('#grid').on('click', '.contractStatus-btn', function(a) {
            a.preventDefault();
            var id = $(this).data('id');
            var rowData = $('#grid').jqGrid('getRowData', id);
            if (rowData.programStatus == 2 && rowData.contractId == 0 && Business.verifyRight('SC_ADD')) {
                Public.ajaxPost('/Bill/InvBq/getEntries', {
                    id: id
                }, function(res) {
                    if (res && 200 == res.status) {
                        var newTabId = (new Date).format("yyyyMMddhhmmss");
                        parent.tab.addTabItem({
                            tabid: 'sales-salesContract' + newTabId,
                            text: '<i class="iconfont icon-danye f14 mr5"></i>特殊销售单（新建）',
                            url: '/Uri/salesContract_manage?turnByBidQuote&tabId=sales-salesContract' + newTabId,
                            data: {
                                id: id,
                                billNo: rowData.billNo,
                                contactId: rowData.contactId,
                                contactName: rowData.contactName,
                                sId: rowData.sId,
                                sName: rowData.sName,
                                quotedName: rowData.quotedName,
                                amount: rowData.amount,
                                entries: res.data,
                                description: rowData.description
                            }
                        });
                    } else {
                        $.dialog.tips('产品清单获取失败！' + res.msg, 2);
                    }
                });
            } else if (rowData.contractId > 0 && Business.verifyRight('SC_QUERY')) {
                parent.tab.addTabItem({
                    tabid: 'sales-salesContract' + rowData.contractId,
                    text: '<i class="iconfont icon-danye f14 mr5"></i>特殊销售单',
                    url: '/Uri/salesContract_manage?id=' + rowData.contractId
                });

            }
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
        parent.frames['bid-bidProgram'] && parent.frames['bid-bidProgram'].$('#search').trigger('click');
        c && c.api.close();
    }
};
$(function() {
    THISPAGE.init();
});
