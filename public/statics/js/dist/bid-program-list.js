var urlParam = Public.urlParam();
var defaultPage = Public.getDefaultPage();
var queryConditions = {
    status1: '',
    status2: -1,
    status3: -1,
    beginDate: '',
    beginDate: urlParam.beginDate || '',
    endDate: urlParam.endDate || '',
    skey: '',
    unDone: urlParam.unDone || 0
};
var combo1 = $('#combo1').combo({
    data: '/Base/User/queryAll?normal=yes&department=销售部',
    ajaxOptions: {
        formatData: function(a) {
            return a.data.rows.unshift({
                id: -1,
                name: '所有报价人'
            }), a.data.rows.push({
                id: 2,
                name: '叶超'
            }, {
                id: 1,
                name: '系统'
            }), a.data.rows;
        }
    },
    value: 'id',
    text: 'name',
    width: 90,
    listHeight: 500,
    defaultSelected: 0,
    callback: {
        onListClick: function() {
            $('#search').parent().find('.current').trigger('click');
        }
    }
}).getCombo();
var combo2 = $('#combo2').combo({
    data: '/Base/Company/queryAll?isDisable=0',
    ajaxOptions: {
        formatData: function(a) {
            return a.data.rows.unshift({
                id: -1,
                name: '所有报价主体'
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
            $('#search').parent().find('.current').trigger('click');
        }
    }
}).getCombo();
var combo3 = $('#combo3').combo({
    data: [
        {id: -1, name: '所有开标结果'},
        {id: 0, name: '未开标'},
        {id: 1, name: '未中标'},
        {id: 2, name: '已中标'}
    ],
    value: 'id',
    text: 'name',
    defaultSelected: 0,
    width: 102,
    callback: {
        onListClick: function() {
            $('#search').parent().find('.current').trigger('click');
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
        $('#beginDate').val(urlParam.beginDate || defaultPage.SYSTEM.beginDate);
        $('#endDate').val(urlParam.endDate || defaultPage.SYSTEM.endDate);
        if ($.inArray('BIDPROGRAM_ADD', defaultPage.SYSTEM.rights) !== -1) {
            $('#add, #toBidQuote').removeClass('dn');
        }
    },
    initGrid: function() {
        var a = Public.setGrid();
        $('#grid').jqGrid({
            url: '/Base/Bid/bidProgramList',
            postData: queryConditions,
            datatype: 'json',
            colModel: [{
                name: 'id',
                label: '<span class="ml10">ID</span>',
                width: 60,
                fixed: !0,
                sortable: !0,
                classes: 'ti10 gray'
            }, {
                name: 'bidSiteName',
                label: '招标来源',
                width: 70,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis hover'
            }, {
                name: 'contactName',
                label: '招标单位',
                width: 70,
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
                name: 'copy_url',
                label: '招标链接',
                width: 60,
                fixed: !0,
                formatter: function(c, d, e) {
                    return '<span class="copy_url db fl pl10 pr10 h28 lh28 b-r-3 hover-white">复制</span>';
                }
            }, {
                name: 'budget',
                label: '预算金额',
                width: 80,
                fixed: !0,
                title: !0,
                sortable: !0,
                formatter: 'currency',
                formatoptions: {
                    showZero: !0,
                    decimalPlaces: 2
                }
            }, {
                name: 'amount',
                label: '报价金额',
                width: 80,
                fixed: !0,
                title: !0,
                sortable: !0,
                formatter: 'currency',
                formatoptions: {
                    showZero: !0,
                    decimalPlaces: 2
                }
            }, {
                name: 'quotedName',
                label: '报价人',
                width: 60,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'sName',
                label: '报价主体',
                width: 70,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'srcBqNo',
                label: '报价单号',
                width: 100,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis hover'
            }, {
                name: 'description',
                label: '备注',
                width: 50,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray'
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
                formatter: function(val, opt, row) {
                    return ('0000-00-00 00:00:00' == val || undefined == val) ? '' : val;
                }
            }, {
                name: 'bidDate',
                label: '开标日期',
                width: 80,
                fixed: !0,
                sortable: !0,
                formatter: function(val, opt, row) {
                    return ('0000-00-00' == val || undefined == val) ? '' : val;
                }
            }, {
                name: 'status',
                label: '开标结果',
                width: 158,
                fixed: !0,
                sortable: !0,
                formatter: function(a, b, c) {
                    var d = a == 0 ? '<span class="db fl pl5 pr5 h30 lh30 b-r-3 white bg-red def">未开标</span>' : '<span class="db fl pl5 pr5 h28 lh28 b-r-3 hover-silver status-btn" data-id="' + c.id + '" data-status="0" title="点击一键切换">未开标</span>';
                    var e = a == 1 ? '<span class="db fl ml1 pl5 pr5 h30 lh30 b-r-3 white bg-night def" title="未中标原因：' + c.lostReason + '">未中标</span>' : '<span class="db fl ml2 pl5 pr5 h28 lh28 b-r-3 hover-silver status-btn" data-id="' + c.id + '" data-status="1" title="点击一键切换">未中标</span>';
                    var f = a == 2 ? '<span class="db fl ml1 pl5 pr5 h30 lh30 b-r-3 white bg-green def">已中标</span>' : '<span class="db fl ml2 pl5 pr5 h28 lh28 b-r-3 hover-silver status-btn" data-id="' + c.id + '" data-status="2" title="点击一键切换">已中标</span>';
                    return d + e + f;
                }
            }, {
                name: 'filesStatus',
                label: '文件状态',
                width: 108,
                fixed: !0,
                sortable: !1,
                formatter: function(a, b, c) {
                    var d = c.files1.length > 0 ? '<span class="db fl w30 tc h30 lh30 b-r-3 white bg-green def" title="合同文件">' + c.files1.length + '</span>' : '<span class="db fl w30 tc h30 lh30 b-r-3 white bg-gray def" title="合同文件">0</span>';
                    var e = c.files2.length > 0 ? '<span class="db fl ml1 w30 tc h30 lh30 b-r-3 white bg-green def" title="签收凭证">' + c.files2.length + '</span>' : '<span class="db fl ml2 w30 tc h30 lh30 b-r-3 white bg-gray def" title="签收凭证">0</span>';
                    var f = c.files3.length > 0 ? '<span class="db fl ml1 w30 tc h30 lh30 b-r-3 white bg-green def" title="其他文件">' + c.files3.length + '</span>' : '<span class="db fl ml2 w30 tc h30 lh30 b-r-3 white bg-gray def" title="其他文件">0</span>';
                    return d + e + f;
                }
            }, {
                name: 'bidSiteId',
                label: '',
                hidden: !0
            }, {
                name: 'bidUrl',
                label: '',
                hidden: !0
            }, {
                name: 'contactId',
                label: '',
                hidden: !0
            }, {
                name: 'sId',
                label: '',
                hidden: !0
            }, {
                name: 'srcBqId',
                label: '',
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
            cellsubmit: 'clientArray',
            loadComplete: function(data) {
                data.status == -1 && $.dialog.tips(data.msg, 2);
            },
            loadError: function(a, b) {
                $.dialog.tips('Type: ' + b + '; Response: ' + a.status + ' ' + a.statusText, 2);
            },
            onCellSelect: function(a, b) {
                var c = $('#grid').jqGrid('getRowData', a);
                switch (b) {
                    case 1:
                        if (Business.verifyRight('BIDSITE_QUERY')) {
                            if (c.bidSiteId > 0) {
                                $.dialog({
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
                                    disEdit: !0
                                });
                            }
                        }
                        break;
                    case 2:
                        if (Business.verifyRight('CUSTOMER_QUERY')) {
                            if (c.contactId > 0) {
                                $.dialog({
                                    title: '查看客户',
                                    content: 'url:/Uri/customer_manage',
                                    data: {
                                        oper: 'edit',
                                        rowId: c.contactId
                                    },
                                    width: 1250,
                                    height: 500,
                                    max: !1,
                                    min: !1,
                                    cache: !1,
                                    lock: !0,
                                    disEdit: !0
                                });
                            } else {
                                $.dialog.alert('系统中没有对应名称的客户信息！');
                            }
                        }
                        break;
                    case 3:
                        window.open(Public.fixUrl(c.bidUrl));
                        break;
                    case 4:
                        if (window.clipboardData) {
                            window.clipboardData.setData('Text', Public.fixUrl(c.bidUrl));
                            $.dialog.tips('复制成功！');
                        } else {
                            var clipboard = new ClipboardJS('.copy_url', {
                                text: function() {
                                    return Public.fixUrl(c.bidUrl);
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
                    case 9:
                        if (c.srcBqId > 0) {
                            parent.tab.addTabItem({
                                tabid: 'bid-bidQuote' + c.srcBqId,
                                text: '<i class="iconfont icon-danye f14 mr5"></i>预算报价单（查看）',
                                url: '/Uri/bid_quote_manage?view&id=' + c.srcBqId
                            });
                        }
                        break;
                    default:
                        break;
                }
            },
            ondblClickRow: function(c) {
                $('#' + c).find('.ui-icon-pencil').trigger('click');
            }
        });
    },
    initEvent: function() {
        laydate.render({
            elem: "#datePicker",
            range: ['#beginDate', '#endDate'],
            done: function() {
                setTimeout(function() {
                    $('#search').trigger('click').addClass('current').siblings().removeClass('current');
                }, 300);
            }
        });
        $('#search').on('click', function(a) {
            queryConditions.status1 = combo1.getValue() == -1 ? '' : combo1.getText();
            queryConditions.status2 = combo2.getValue();
            queryConditions.status3 = combo3.getValue();
            queryConditions.beginDate = $('#beginDate').val();
            queryConditions.endDate = $('#endDate').val();
            queryConditions.skey = $.trim($('#skey').val()).split(/[ ,，]+/).toString();
            $('#grid').jqGrid('setGridParam', {
                postData: queryConditions,
                page: 1
            }).trigger('reloadGrid');
        });
        $('#curMonth').on('click', function() {
            $(this).addClass('current').siblings().removeClass('current');
            $('#beginDate').val((new Date).format('yyyy-MM') + '-01');
            $('#endDate').val((new Date).format('yyyy-MM-dd'));
            $('#search').trigger('click');
        });
        $('#lastMonth').on('click', function() {
            $(this).addClass('current').siblings().removeClass('current');
            $('#beginDate').val(Public.reduceMonths((new Date).format('yyyy-MM') + '-01', 1));
            $('#endDate').val(Public.reduceDays((new Date).format('yyyy-MM') + '-01', 1));
            $('#search').trigger('click');
        });
        $('#curYear').on('click', function() {
            $(this).addClass('current').siblings().removeClass('current');
            $('#beginDate').val((new Date).format('yyyy-01-01'));
            $('#endDate').val((new Date).format('yyyy-MM-dd'));
            $('#search').trigger('click');
        });
        $('#lastYear').on('click', function() {
            $(this).addClass('current').siblings().removeClass('current');
            $('#beginDate').val(Public.reduceMonths((new Date).format('yyyy-01-01'), 12));
            $('#endDate').val(Public.reduceMonths((new Date).format('yyyy-12-31'), 12));
            $('#search').trigger('click');
        });
        $('#searchAll').on('click', function() {
            $(this).addClass('current').siblings().removeClass('current');
            $('#beginDate').val(defaultPage.SYSTEM.beginDate);
            $('#endDate').val(defaultPage.SYSTEM.endDate);
            $('#search').trigger('click');
        });
        $('#refresh').on('click', function() {
            window.location.reload();
        });
        $('#add').on('click', function(a) {
            a.preventDefault();
            if (Business.verifyRight('BIDPROGRAM_ADD')) {
                $.dialog({
                    title: '新增投标项目',
                    content: 'url:/Uri/bid_program_manage',
                    data: {
                        oper: 'add',
                        callback: THISPAGE.callback
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
        $('#toBidQuote').on('click', function(a) {
            a.preventDefault();
            parent.tab.addTabItem({
                tabid: 'bid-bidQuote',
                text: '<i class="iconfont icon-danju f14 mr5"></i>预算报价',
                url: '/Uri/bid_quote_list'
            });
        });
        $('.grid-wrap').on('click', '.status-btn', function(a) {
            a.preventDefault();
            if (Business.verifyRight('BIDPROGRAM_CHECK')) {
                var id = $(this).data('id');
                var status = $(this).data('status');
                if (id) {
                    if (status == 1) {
                        $.dialog.prompt('<span class="f14">请输入未中标的原因（请尽量输入准确原因，100字以内，便于相关人员后期查询与分析）。', function(inputValue) {
                            Public.ajaxPost('/Base/Bid/bidProgramStatus', {
                                id: id,
                                status: status,
                                lostReason: inputValue
                            }, function(res) {
                                if (res && 200 == res.status) {
                                    $.dialog.tips('开标结果更新成功！');
                                    $('#grid').jqGrid('setRowData', id, res.data);
                                    parent.frames['bid-bidQuote'] && parent.frames['bid-bidQuote'].$('#search').trigger('click');
                                } else {
                                    $.dialog.tips('开标结果更新失败！' + res.msg, 2);
                                }
                            });
                        });
                    } else {
                        Public.ajaxPost('/Base/Bid/bidProgramStatus', {
                            id: id,
                            status: status,
                            lostReason: ''
                        }, function(res) {
                            if (res && 200 == res.status) {
                                $.dialog.tips('开标结果更新成功！');
                                $('#grid').jqGrid('setRowData', id, res.data);
                                parent.frames['bid-bidQuote'] && parent.frames['bid-bidQuote'].$('#search').trigger('click');
                            } else {
                                $.dialog.tips('开标结果更新失败！' + res.msg, 2);
                            }
                        });
                    }
                }
            }
        });
        $('.grid-wrap').on('click', '.ui-icon-pencil', function(a) {
            a.preventDefault();
            var id = $(this).parent().data('id');
            $.dialog({
                title: '编辑投标项目',
                content: 'url:/Uri/bid_program_manage',
                data: {
                    oper: 'edit',
                    rowId: id,
                    callback: THISPAGE.callback
                },
                width: 1250,
                height: 600,
                max: !1,
                min: !1,
                cache: !1,
                lock: !0
            });
        });
        $('.grid-wrap').on('click', '.ui-icon-trash', function(a) {
            a.preventDefault();
            if (Business.verifyRight('BIDPROGRAM_DELETE')) {
                var id = $(this).parent().data('id');
                id && $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                    Public.ajaxPost('/Base/Bid/deleteBidProgram', {
                        id: id
                    }, function(res) {
                        if (res && 200 == res.status) {
                            $.dialog.tips('删除成功！');
                            $('#grid').jqGrid('delRowData', id);
                            parent.frames['bid-bidQuote'] && parent.frames['bid-bidQuote'].$('#search').trigger('click');
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
                $('#search').parent().find('.current').trigger('click');
            }
        });
        $(window).on('resize', function() {
            Public.resizeGrid();
        });
    },
    callback: function(a, b, c) {
        var e = $('#grid').data('gridData');
        e || (e = {}, $('#grid').data('gridData', e));
        e[a.id] = a;
        'edit' == b ? ($('#grid').jqGrid('setRowData', a.id, a), c && c.api.close()) : (a.status = 0, $('#grid').jqGrid('addRowData', a.id, a, 'first'), c && c.api.close());
        parent.frames['bid-bidQuote'] && parent.frames['bid-bidQuote'].$('#search').trigger('click');
    }
};
$(function() {
    THISPAGE.init();
});
