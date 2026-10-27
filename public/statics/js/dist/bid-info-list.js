var urlParam = Public.urlParam();
var defaultPage = Public.getDefaultPage();
var queryConditions = {
    siteType: -1,
    status: -1,
    match: 0,
    beginDate: Public.reduceMonths((new Date).format('yyyy-MM') + '-01', 11) + ' 00:00:00',
    endDate: defaultPage.SYSTEM.endDate + ' 23:59:59',
    skey: ''
};
var siteTypeCombo = $('#combo1').combo({
    data: [
        {id: -1, name: '所有网站'},
        {id: 0, name: '招标平台'},
        {id: 1, name: '招标代理'},
        {id: 2, name: '政府主办'},
        {id: 3, name: '招标企业'},
        {id: 4, name: '学校官网'},
        {id: 5, name: '公众号'}
    ],
    value: 'id',
    text: 'name',
    defaultSelected: 0,
    width: 78,
    listHeight: 500,
    callback: {
        onListClick: function() {
            $('#search').trigger('click');
        }
    }
}).getCombo();
var statusCombo = $('#combo2').combo({
    data: [
        {id: -1, name: '所有状态'},
        {id: 0, name: '我有参与'},
        {id: 1, name: '已报价'}
    ],
    value: 'id',
    text: 'name',
    defaultSelected: 0,
    width: 78,
    callback: {
        onListClick: function() {
            $('#search').trigger('click');
        }
    }
}).getCombo();
var matchCombo = $('#combo3').combo({
    data: [
        {id: 0, name: '模糊查询'},
        {id: 1, name: '精确查询'}
    ],
    value: 'id',
    text: 'name',
    defaultSelected: 0,
    width: 78,
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
        $('#beginDate').val(Public.reduceMonths((new Date).format('yyyy-MM') + '-01', 11) + ' 00:00:00');
        $('#endDate').val(defaultPage.SYSTEM.endDate + ' 23:59:59');
    },
    initGrid: function() {
        var a = Public.setGrid();
        $('#grid').jqGrid({
            url: '/Base/Bid/list',
            postData: queryConditions,
            datatype: 'json',
            colModel: [{
                name: 'winRate',
                label: '历史中标率',
                width: 150,
                fixed: !0,
                sortable: !1,
                formatter: function(a, b, c) {
                    var d = a == -1 ? '<span class="gray">没投过</span>' : ('<div class="winRate"><div class="winRate-bar bg-aqua" style="width:' + (a <= 0 ? 0 : a) + '%"></div><div class="winRate-text"><span class="db fl ml10 w30">' + a + '%</span><span class="fr mr10">' + c.winInfo + '</span></div></div>');
                    return d;
                }
            }, {
                name: '',
                label: '<span class="f12 iconfont icon-youjiantou gray"></span>',
                width: 22,
                fixed: !0,
                formatter: function(a, b, c) {
                    return '<span class="f12 iconfont icon-youjiantou gray"></span>';
                }
            }, {
                name: 'bidSiteName',
                label: '招标来源',
                width: 150,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis hover'
            }, {
                name: '',
                label: '<span class="f12 iconfont icon-youjiantou gray"></span>',
                width: 22,
                fixed: !0,
                formatter: function(a, b, c) {
                    return '<span class="f12 iconfont icon-youjiantou gray"></span>';
                }
            }, {
                name: 'bidTitle',
                label: '招标标题<span class="ml5 f14 fb black">（直接点击被拦截打不开的话，点击复制按钮，手动粘贴到新窗口打开）</span>',
                width: 100,
                title: !1,
                sortable: !0,
                classes: 'ui-ellipsis hover',
                formatter: function(a, b, c) {
                    var d = '';
                    if (c.bidSiteId == 0) {
                        d += '<span class="db fl mr5 h30 lh30 pl5 pr5 ti0 white bg-red b-r-3 ovh" title="手动新增">增</span>';
                    }
                    if (c.inDate >= ((new Date).format('yyyy-MM-dd') + ' 00:00:00')) {
                        d += '<span class="db fl mr5 h30 lh30 pl5 pr5 ti0 white bg-teal b-r-3 ovh" title="今天收录">今</span>';
                    }
                    if (c.inDate >= (Public.reduceDays((new Date).format('yyyy-MM-dd'), 1) + ' 00:00:00') && c.inDate < ((new Date).format('yyyy-MM-dd') + ' 00:00:00')) {
                        d += '<span class="db fl mr5 h30 lh30 pl5 pr5 ti0 white bg-gray b-r-3 ovh" title="昨天收录">昨</span>';
                    }
                    d += '<span class="f14" title="' + a + '">' + a + '</span>';
                    return d;
                }
            }, {
                name: 'copy_url',
                label: '招标链接',
                width: 60,
                fixed: !0,
                formatter: function(c, d, e) {
                    return '<span class="copy_url db fl pl10 pr10 h28 lh28 b-r-3 hover-white">复制</span>';
                }
            }, {
                name: 'bidUrl',
                label: '链接',
                width: 0,
                hidden: !0
            }, {
                name: 'beginDate',
                label: '发布时间',
                width: 124,
                fixed: !0,
                sortable: !0,
                formatter: function(c, d, e) {
                    return ('0000-00-00 00:00:00' == c || undefined == c) ? '' : c;
                }
            }, {
                name: 'inDate',
                label: '收录时间',
                width: 130,
                fixed: !0,
                sortable: !0,
                classes: 'gray',
                formatter: function(c, d, e) {
                    return ('0000-00-00 00:00:00' == c || undefined == c) ? '' : c;
                }
            }, {
                name: 'joinHis',
                label: '历史参与人',
                width: 100,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray'
            }, {
                name: 'leaveReasonHis',
                label: '历史取消原因',
                width: 100,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray'
            }, {
                name: 'joinStatus',
                label: '参与信息<small class="ml5">可多人同时参与</small>',
                width: 150,
                fixed: !0,
                align: 'center',
                formatter: function(a, b, c) {
                    var d = 1 == a ? c.joinName : '暂无';
                    var e = 1 == a ? ('当前参与人：' + c.joinName + "\r\n" + (c.joinName.includes(defaultPage.SYSTEM.uName) ? '点击取消我的参与' : '点击参与')) : (c.joinHis ? ('历史参与人：' + c.joinHis + "\r\n" + '点击参与') : '点击参与');
                    var f = 1 == a ? 'hover-green' : 'hover-silver';
                    return '<span class="join-btn db h28 lh28 pl10 pr10 ' + f + ' b-r-3 es" data-joinstatus="' + a + '" data-id="' + c.id + '" title="' + e + '">' + d + '</span>';
                }
            }, {
                name: 'quoteId',
                label: '报价信息',
                width: 70,
                fixed: !0,
                sortable: !0,
                align: 'center',
                formatter: function(a, b, c) {
                    var d = a > 0 ? c.quoteNo : '暂无';
                    var e = a > 0 ? '点击查看预算报价单' : '点击创建预算报价单';
                    var f = a > 0 ? 'hover-green' : 'hover-silver';
                    return '<span class="quote-btn db h28 lh28 pl10 pr10 ' + f + ' b-r-3 es" data-quoteid="' + a + '" data-id="' + c.id + '" title="' + e + '">' + d + '</span>';
                }
            }, {
                name: 'id',
                fixed: !0,
                hidden: !0
            }, {
                name: 'bidSiteId',
                width: 0,
                hidden: !0
            }, {
                name: 'joinName',
                width: 0,
                hidden: !0
            }, {
                name: 'quoteNo',
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
            width: a.w,
            height: a.h,
            altRows: !1,
            gridview: !0,
            rownumbers: !0,
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
                $('#page_left').html('<div class="fl pl10 pr10 lh22 night"><i class="mr5 f12 iconfont icon-bangzhu"></i>上月投标排名越高，越快获取到最新招标信息。</div>');
                if (data.status == -1) {
                    $.dialog.tips(data.msg, 2);
                } else {
                    $('#todayTotal').html(data.data.todayTotal);
                    // 获取分页相关的所有信息
                    var gridParams = $("#grid").jqGrid('getGridParam');
                    // 获取当前页数
                    var currentPage = gridParams.page;
                    // 获取总页数
                    var totalPages = gridParams.lastpage;
                    if (currentPage > 1 && currentPage > totalPages) {
                        $.dialog.tips('当前页超出总页数，即将跳转到第一页！', 1, 1.5);
                        setTimeout(() => {
                            $("#grid").jqGrid('setGridParam', {page: 1}).trigger('reloadGrid');
                        }, 1500);
                    }
                }
            },
            loadError: function(a, b) {
                $.dialog.tips('Type: ' + b + '; Response: ' + a.status + ' ' + a.statusText, 2);
            },
            onCellSelect: function(c, d, x, y, z) {
                var e = $('#grid').jqGrid('getRowData', c);
                switch (d) {
                    case 3:
                        if (Business.verifyRight('BIDSITE_QUERY')) {
                            e.bidSiteId > 0 && $.dialog({
                                title: '查看招标网站',
                                content: 'url:/Uri/bid_site_manage',
                                data: {
                                    oper: 'editSite',
                                    rowId: e.bidSiteId
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
                    case 5:
                        Public.ajaxPost('/Base/Bid/viewLog', {
                            id: c,
                            bidTitle: $(e.bidTitle.replace('<span class="db fl mr5 h30 lh30 pl5 pr5 ti0 white bg-red b-r-3 ovh" title="手动新增">增</span>', '').replace('<span class="db fl mr5 h30 lh30 pl5 pr5 ti0 white bg-teal b-r-3 ovh" title="今天收录">今</span>', '').replace('<span class="db fl mr5 h30 lh30 pl5 pr5 ti0 white bg-gray b-r-3 ovh" title="昨天收录">昨</span>', '')).text()
                        }, function(res) {
                            $.dialog.tips('已记录浏览！');
                        });
                        window.open(Public.fixUrl(e.bidUrl));
                        break;
                    case 6:
                        if (window.clipboardData) {
                            window.clipboardData.setData('Text', Public.fixUrl(e.bidUrl));
                            $.dialog.tips('复制成功！');
                        } else {
                            var clipboard = new ClipboardJS('.copy_url', {
                                text: function() {
                                    return Public.fixUrl(e.bidUrl);
                                }
                            });
                            clipboard.on('success', function() {
                                $.dialog.tips('复制成功！');
                                clipboard.destroy();
                            });
                            clipboard.on('error', function() {
                                $.dialog.tips('复制失败！', 2);
                                clipboard.destroy();
                            });
                        }
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
            type: 'datetime',
            range: ['#beginDate', '#endDate'],
            done: function() {
                setTimeout(function() {
                    $('#search').trigger('click');
                }, 300);
            }
        });
        $('#skey').on({
            focusin: function() {
                // 当input获得焦点
                Public.ajaxGet('/Base/Bid/getHotWord', {
                    hotWord: $.trim($(this).val()).replace(/[ ,，]+/, '')
                }, function(res) {
                    if (res && 200 == res.status) {
                        if (res.data.length > 0) {
                            var html = '<div class="hotword-tit fb bg-silver">热搜关键词 TOP20<i class="iconfont icon-huore fr fn f20 red mr5"></i></div>';
                            $.each(res.data, function(k, v) {
                                html += '<div class="hotword-item es" data-hotword="' + v.name + '" title="' + v.name + '">' + v.name + '<span class="hotword-search-count">热度 ' + v.searchCount + '</span></div>';
                            });
                            $('.hotword-con').html(html).removeClass('dn');
                        } else {
                            $('.hotword-con').addClass('dn');
                        }
                    } else {
                        $('.hotword-con').addClass('dn');
                    }
                });
            },
            input: function() {
                // 当input输入内容时
                Public.ajaxGet('/Base/Bid/getHotWord', {
                    hotWord: $.trim($(this).val()).replace(/[ ,，]+/, '')
                }, function(res) {
                    if (res && 200 == res.status) {
                        if (res.data.length > 0) {
                            var html = '<div class="hotword-tit fb">热搜关键词<i class="iconfont icon-huore fr fn f20 red mr5"></i></div>';
                            $.each(res.data, function(k, v) {
                                html += '<div class="hotword-item es" data-hotword="' + v.name + '" title="' + v.name + '">' + v.name + '<span class="hotword-search-count">热度 ' + v.searchCount + '</span></div>';
                            });
                            $('.hotword-con').html(html).removeClass('dn');
                        } else {
                            $('.hotword-con').addClass('dn');
                        }
                    } else {
                        $('.hotword-con').addClass('dn');
                    }
                });
            },
            blur: function() {
                // 当input失去焦点时
                setTimeout(() => {
                    // 给关键词点击留出500ms处理时间，否则点击事件无法执行
                    $('.hotword-con').addClass('dn');
                }, 200);
            }
        });
        $('.hotword-con').on('click', '.hotword-item', function() {
            var hotword = $(this).data('hotword');
            $('#skey').val(hotword) && $('.hotword-con').addClass('dn') && $('#search').trigger('click');
        });
        $('#search').on('click', function() {
            queryConditions.siteType = siteTypeCombo.getValue();
            queryConditions.status = statusCombo.getValue();
            queryConditions.match = matchCombo.getValue();
            queryConditions.beginDate = $.trim($('#beginDate').val());
            queryConditions.endDate = $.trim($('#endDate').val());
            queryConditions.skey = $.trim($('#skey').val()).split(/[ ,，]+/).toString();
            $('#grid').jqGrid('setGridParam', {
                postData: queryConditions
            }).trigger('reloadGrid');
        });
        $('#refresh').on('click', function() {
            window.location.reload();
        });
        $('#add').on('click', function(a) {
            a.preventDefault();
            if (Business.verifyRight('BIDINFO_UPDATE')) {
                $.dialog({
                    title: '新增招标信息',
                    content: 'url:/Uri/bid_info_manage',
                    data: {
                        oper: 'add',
                        rowData: {},
                        callback: THISPAGE.callback
                    },
                    width: 1000,
                    height: 200,
                    max: !1,
                    min: !1,
                    cache: !1,
                    lock: !0
                });
            }
        });
        $('#del1year').on('click', function(a) {
            a.preventDefault();
            if (Business.verifyRight('BIDINFO_DELETE')) {
                $('.wrapper').append('<div id="div-lock"><div class="loading">删除中...</div></div>');
                Public.ajaxPost('/Base/Bid/deletebidinfo1year', {}, function(res) {
                    $('#div-lock').remove();
                    if (res && 200 == res.status) {
                        $.dialog.tips('删除成功！');
                        $('#search').trigger('click');
                    } else {
                        $.dialog.tips('删除失败！' + res.msg, 2);
                    }
                });
            }
        });
        $('.grid-wrap').on('click', '.join-btn', function(a) {
            a.preventDefault();
            if (Business.verifyRight('BIDINFO_QUERY')) {
                var id = $(this).data('id');
                var joinstatus = $(this).data('joinstatus');
                var joinNameArr = $(this).text().split(',');
                if (id) {
                    if (joinstatus == 1 && $.inArray(defaultPage.SYSTEM.uName, joinNameArr) !== -1) {
                        $.dialog.prompt('<span class="f14">请输入取消参与的原因</span><br>例如：已结束、不满足要求等等，来提醒所有人避免重复或无效参与。', function(inputValue) {
                            Public.ajaxPost('/Base/Bid/setJoinStatus', {
                                id: id,
                                leaveReason: inputValue
                            }, function(res) {
                                if (res && 200 == res.status) {
                                    parent.$.dialog.tips('参与状态标记成功！');
                                    $('#grid').jqGrid('setRowData', id, res.data);
                                } else {
                                    $.dialog.tips('参与状态标记失败！' + res.msg, 2);
                                }
                            });
                        });
                    } else {
                        Public.ajaxPost('/Base/Bid/setJoinStatus', {
                            id: id
                        }, function(res) {
                            if (res && 200 == res.status) {
                                parent.$.dialog.tips('参与状态标记成功！');
                                $('#grid').jqGrid('setRowData', id, res.data);
                            } else {
                                $.dialog.tips('参与状态标记失败！' + res.msg, 2);
                            }
                        });
                    }
                }
            }
        });
        $('.grid-wrap').on('click', '.quote-btn', function(a) {
            a.preventDefault();
            if (Business.verifyRight('BIDINFO_UPDATE')) {
                var id = $(this).data('id');
                var rowData = $('#grid').jqGrid('getRowData', id);
                var joinNameArr = rowData.joinName ? rowData.joinName.split(',') : [];
                if (rowData.quoteNo == '' && $.inArray(defaultPage.SYSTEM.uName, joinNameArr) === -1) {
                    $.dialog.tips('您未参与，无权创建！', 2);
                } else {
                    Public.ajaxPost('/Base/Bid/query', {
                        id: id
                    }, function(res) {
                        if (res && 200 == res.status) {
                            if (res.data.quoteId == 0) {
                                var newTabId = (new Date).format("yyyyMMddhhmmss");
                                parent.tab.addTabItem({
                                    tabid: 'bid-bidQuote' + newTabId,
                                    text: '<i class="iconfont icon-danye f14 mr5"></i>预算报价单（新建）',
                                    url: '/Uri/bid_quote_manage?turnByBidInfo&tabId=bid-bidQuote' + newTabId,
                                    data: {
                                        bidSiteId: rowData.bidSiteId,
                                        bidSiteName: rowData.bidSiteName,
                                        bidInfoId: id,
                                        bidTitle: rowData.bidTitle,
                                        bidUrl: Public.fixUrl(rowData.bidUrl)
                                    }
                                });
                            } else {
                                parent.tab.addTabItem({
                                    tabid: 'bid-bidQuote' + res.data.quoteId,
                                    text: '<i class="iconfont icon-danye f14 mr5"></i>预算报价单（查看）',
                                    url: '/Uri/bid_quote_manage?view&id=' + res.data.quoteId
                                });
                            }
                        }
                        $('#search').trigger('click');
                    });
                }
            }
        });
        $('.grid-wrap').on('click', '.ui-icon-pencil', function(a) {
            a.preventDefault();
            if (Business.verifyRight('BIDINFO_UPDATE')) {
                var id = $(this).parent().data('id');
                id && $.dialog({
                    title: '编辑招标信息',
                    content: 'url:/Uri/bid_info_manage',
                    data: {
                        oper: 'edit',
                        rowId: id,
                        rowData: {},
                        callback: THISPAGE.callback
                    },
                    width: 1000,
                    height: 200,
                    max: !1,
                    min: !1,
                    cache: !1,
                    lock: !0
                });
            }
        });
        $('.grid-wrap').on('click', '.ui-icon-trash', function(a) {
            a.preventDefault();
            if (Business.verifyRight('BIDINFO_DELETE')) {
                var id = $(this).parent().data('id');
                id && $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                    Public.ajaxGet('/Base/Bid/delete', {
                        id: id
                    }, function(res) {
                        if (res && 200 == res.status) {
                            $.dialog.tips('删除成功！');
                            $('#search').trigger('click');
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
                $('#beginDate, #endDate').blur();
                $('#search').trigger('click');
            }
        });
        $(window).on('resize', function() {
            Public.autoGridWidth($('#grid'));
        });
    },
    callback: function(a, b, c, d) {
        $('#search').trigger('click'), c && (d == 1 ? c.api.close() : c.THISPAGE.resetForm());
    }
};
$(function() {
    THISPAGE.init();
});
