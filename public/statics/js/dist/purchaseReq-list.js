var urlParam = Public.urlParam();
var defaultPage = Public.getDefaultPage();
var queryConditions = {
    uId: 0,
    searchType: urlParam.unDone ? 1 : 0,
    beginDate: '',
    endDate: urlParam.endDate ? (urlParam.endDate + ' 23:59:59') : '',
    skey: '',
    unDone: urlParam.unDone || 0
};
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
var combo2 = $('#combo2').combo({
    data: [
        {id: 0, name: '所有状态'},
        {id: 1, name: '未采购'},
        {id: 2, name: '已拒绝'}
    ],
    value: 'id',
    text: 'name',
    defaultSelected: urlParam.unDone ? 1 : 0,
    width: 78,
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
        $('#beginDate').val(defaultPage.SYSTEM.beginDate + ' 00:00:00');
        $('#endDate').val((urlParam.endDate || defaultPage.SYSTEM.endDate) + ' 23:59:59');
    },
    initGrid: function() {
        var a = Public.setGrid();
        $('#grid').jqGrid({
            url: '/Base/PurReq/list',
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
                name: 'level',
                label: '紧急',
                width: 44,
                fixed: !0,
                sortable: !0,
                align: 'center',
                formatter: function(a, b, c) {
                    var d = 0 == a ? '不急' : (1 == a ? '急' : '很急');
                    var f = 0 == a ? 'green bd-green' : (1 == a ? 'blue bd-blue' : 'red bd-red');
                    return '<span class="db h28 lh28 b-r-3 def ' + f + '">' + d + '</span>';
                }
            }, {
                name: 'title',
                label: '申请标题',
                width: 200,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'content',
                label: '附加说明',
                width: 200,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'imgs',
                label: '参考图片',
                width: 74,
                fixed: !0,
                sortable: !0,
                classes: 'ui-ellipsis',
                formatter: function(cellvalue, options, rowObject) {
                    var imgs = '';
                    if (cellvalue) {
                        cellvalue.forEach(element => {
                            imgs += '<a target="_blank" href="/data/upfile/purReq/img/' + element + '"><span class="fl mr2 mb2 iconfont icon-tupian f20 gray poi" title="点击查看"></span></a>';
                        });
                    }
                    return imgs;
                }
            }, {
                name: 'files',
                label: '参考资料',
                width: 66,
                fixed: !0,
                sortable: !0,
                classes: 'ui-ellipsis',
                formatter: function(cellvalue, options, rowObject) {
                    return cellvalue.length > 0 ? (cellvalue.length + ' 份') : '';
                }
            }, {
                name: 'uId',
                label: 'uId',
                hidden: !0
            }, {
                name: 'uName',
                label: '申请人',
                width: 54,
                fixed: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray'
            }, {
                name: 'createTime',
                label: '申请时间',
                width: 80,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray',
                formatter: function(cellvalue, options, rowObject) {
                    return ('0000-00-00 00:00:00' == cellvalue || undefined == cellvalue) ? '' : cellvalue;
                }
            }, {
                name: 'checkName',
                label: '审批人',
                width: 54,
                fixed: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray'
            }, {
                name: 'checkTime',
                label: '审批时间',
                width: 80,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray',
                formatter: function(cellvalue, options, rowObject) {
                    return ('0000-00-00 00:00:00' == cellvalue || undefined == cellvalue) ? '' : cellvalue;
                }
            }, {
                name: 'purchaseName',
                label: '采购人',
                width: 54,
                fixed: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray'
            }, {
                name: 'purchaseTime',
                label: '最后采购时间',
                width: 80,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray',
                formatter: function(cellvalue, options, rowObject) {
                    return ('0000-00-00 00:00:00' == cellvalue || undefined == cellvalue) ? '' : cellvalue;
                }
            }, {
                name: 'backInfo',
                label: '回执信息',
                width: 100,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray'
            }, {
                name: 'description',
                label: '拒绝理由',
                width: 100,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray'
            }, {
                name: 'checkId',
                label: '审批',
                width: 82,
                fixed: !0,
                sortable: !0,
                formatter: function(a, b, c) {
                    var d = c.description ? '已拒绝' : (a == 0 ? '批准' : '已批准');
                    var e = c.description ? '删除拒绝理由可以取消拒绝' : (a == 0 ? '点击批准' : '点击反批');
                    var f = c.description ? 'hover-night' : (a == 0 ? 'hover-teal' : 'hover-green');
                    return '<span class="' + (c.description ? '' : 'check-btn ') + 'fl db pl5 pr5 h28 lh28 b-r-3 ' + f + '" title="' + e + '" data-id="' + c.id + '" data-checkid="' + a + '">' + d + '</span>' + ((a == 0 && c.description == '') ? '<span class="refuse-btn fl ml2 db pl5 pr5 h28 lh28 b-r-3 hover-red" title="点击拒绝" data-id="' + c.id + '">拒绝</span>' : '');
                }
            }, {
                name: 'purchaseId',
                label: '采购',
                width: 164,
                fixed: !0,
                title: !1,
                sortable: !0,
                formatter: function(a, b, c) {
                    if (c.description) {
                        return '<span class="db fl pl5 pr5 h28 lh28 b-r-3 hover-night" title="删除拒绝理由可以取消拒绝">已拒绝</span>';
                    } else {
                        if (a == 0) {
                            return '<span class="pur-order-btn db fl pl5 pr5 h28 lh28 b-r-3 hover-red" data-id="' + c.id + '" data-checkid="' + c.checkId + '" title="点击以采购订单模式采购（可分多单采购）">订单采购</span><span class="pur-contract-btn db fl ml2 pl5 pr5 h28 lh28 b-r-3 hover-red" data-id="' + c.id + '" data-checkid="' + c.checkId + '" title="点击以特殊采购单模式采购（可分多单采购）">特殊采购</span>';
                        } else {
                            return '<span class="pur-order-btn db fl pl5 pr5 h28 lh28 b-r-3 hover-green" data-id="' + c.id + '" data-checkid="' + c.checkId + '" title="点击继续以采购订单模式采购">订单采购</span><span class="pur-contract-btn db fl ml2 pl5 pr5 h28 lh28 b-r-3 hover-green" data-id="' + c.id + '" data-checkid="' + c.checkId + '" title="点击继续以特殊采购单模式采购">特殊采购</span><span class="open-pur-btn db fl ml2 pl5 pr5 h28 lh28 b-r-3 hover-green" data-id="' + c.id + '" data-checkid="' + c.checkId + '" data-srcpoid="' + c.srcPoId + '" data-srcpcid="' + c.srcPcId + '" title="点击打开已采购单据' + (c.srcPoNo ? ('\r\n' + c.srcPoNo) : '') + (c.srcPcNo ? ('\r\n' + c.srcPcNo) : '') + '">单据</span>';
                        }
                    }
                }
            }, {
                name: 'operate',
                label: '操作',
                width: 72.8,
                fixed: !0,
                formatter: Public.operFmatter
            }],
            cmTemplate: {sortable: !1, title: !1},
            jsonReader: {root: 'data.rows', records: 'data.records', total: 'data.total', repeatitems: !1, id: 'id'},
            autowidth: !0,
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
            },
            loadError: function(a, b) {
                $.dialog.tips('Type: ' + b + '; Response: ' + a.status + ' ' + a.statusText, 2);
            },
            ondblClickRow: function(c) {
                $('#' + c).find('.ui-icon-pencil').trigger('click');
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
        $('#search').on('click', function(a) {
            a.preventDefault();
            queryConditions.uId = combo1.getValue();
            queryConditions.searchType = combo2.getValue();
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
        $('#add').on('click', function(a) {
            a.preventDefault();
            if (Business.verifyRight('PURREQ_QUERY')) {
                var b = '新增采购申请';
                var c = {
                    oper: 'add',
                    callback: THISPAGE.callback
                };
                $.dialog({
                    title: b,
                    content: 'url:/Uri/purchaseReq_manage',
                    data: c,
                    width: 1250,
                    height: 700,
                    max: !1,
                    min: !1,
                    cache: !1,
                    lock: !0
                });
            }
        });
        $('#grid').on('click', '.ui-icon-pencil', function(a) {
            a.preventDefault();
            if (Business.verifyRight('PURREQ_QUERY')) {
                var id = $(this).parent().data('id');
                $.dialog({
                    title: '编辑采购申请',
                    content: 'url:/Uri/purchaseReq_manage',
                    data: {
                        oper: 'edit',
                        rowId: id,
                        callback: THISPAGE.callback
                    },
                    width: 1250,
                    height: 700,
                    max: !1,
                    min: !1,
                    cache: !1,
                    lock: !0
                });
            }
        });
        $('#grid').on('click', '.ui-icon-trash', function(a) {
            a.preventDefault();
            if (Business.verifyRight('PURREQ_QUERY')) {
                var id = $(this).parent().data('id');
                id && $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                    Public.ajaxPost('/Base/PurReq/delete', {
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
        });
        $('#grid').on('click', '.check-btn', function(a) {
            if (Business.verifyRight('PURREQ_ADVANCE')) {
                var id = $(this).data('id');
                var checkId = Number($(this).data('checkid')) > 0 ? 0 : defaultPage.SYSTEM.uId;
                var checkName = Number($(this).data('checkid')) > 0 ? "" : defaultPage.SYSTEM.uName;
                var rowData = $('#grid').jqGrid('getRowData', id);
                id && Public.ajaxPost('/Base/PurReq/check', {
                    id: id,
                    checkId: checkId,
                    checkName: checkName,
                    title: rowData.title
                }, function(res) {
                    if (res && 200 == res.status) {
                        $.dialog.tips((checkId > 0 ? '审批' : '反审批') + '成功！');
                        $('#grid').jqGrid('setRowData', id, res.data);
                    } else {
                        $.dialog.tips((checkId > 0 ? '审批' : '反审批') + '失败！' + res.msg, 2);
                    }
                });
            }
        });
        $('#grid').on('click', '.refuse-btn', function(a) {
            a.preventDefault();
            var id = $(this).data('id');
            $.dialog({
                title: '编辑采购申请',
                content: 'url:/Uri/purchaseReq_manage',
                data: {
                    oper: 'edit',
                    rowId: id,
                    callback: THISPAGE.callback
                },
                width: 1250,
                height: 700,
                max: !1,
                min: !1,
                cache: !1,
                lock: !0
            });
        });
        $('#grid').on('click', '.pur-order-btn', function(a) {
            a.preventDefault();
            if (Business.verifyRight('PO_ADD')) {
                var id = $(this).data('id');
                var checkid = $(this).data('checkid');
                var rowData = $('#grid').jqGrid('getRowData', id);
                if (checkid == 0) {
                    $.dialog.alert('申请尚未审批！');
                } else {
                    if (id && rowData) {
                        var newTabId = (new Date).format("yyyyMMddhhmmss");
                        parent.tab.addTabItem({
                            tabid: 'purchase-purchaseOrder' + newTabId,
                            text: '<i class="iconfont icon-danye f14 mr5"></i>采购订单（新建）',
                            url: '/Uri/purchaseOrder_manage?turnByPurReq&tabId=purchase-purchaseOrder' + newTabId,
                            data: {
                                id: id,
                                title: '原采购申请ID：' + id + '，标题：' + rowData.title + '，说明：' + rowData.content
                            }
                        });
                    }
                }
            }
        });
        $('#grid').on('click', '.pur-contract-btn', function(a) {
            a.preventDefault();
            if (Business.verifyRight('PC_ADD')) {
                var id = $(this).data('id');
                var checkid = $(this).data('checkid');
                var rowData = $('#grid').jqGrid('getRowData', id);
                if (checkid == 0) {
                    $.dialog.alert('申请尚未审批！');
                } else {
                    if (id && rowData) {
                        var newTabId = (new Date).format("yyyyMMddhhmmss");
                        parent.tab.addTabItem({
                            tabid: 'purchase-purchaseContract' + newTabId,
                            text: '<i class="iconfont icon-danye f14 mr5"></i>特殊采购单（新建）',
                            url: '/Uri/purchaseContract_manage?turnByPurReq&tabId=purchase-purchaseContract' + newTabId,
                            data: {
                                id: id,
                                title: '原采购申请ID：' + id + '，标题：' + rowData.title + '，说明：' + rowData.content
                            }
                        });
                    }
                }
            }
        });
        $('#grid').on('click', '.open-pur-btn', function(a) {
            a.preventDefault();
            var id = $(this).data('id');
            var checkid = $(this).data('checkid');
            var srcpoid = $(this).data('srcpoid');
            var srcpcid = $(this).data('srcpcid');
            var rowData = $('#grid').jqGrid('getRowData', id);
            if (checkid == 0) {
                $.dialog.alert('申请尚未审批！');
            } else {
                if (srcpoid || srcpcid) {
                    if (Business.verifyRight('PO_QUERY') && srcpoid) {
                        srcPoIdArr = srcpoid.toString().split(',');
                        for (var i = 0; i < srcPoIdArr.length; i++) {
                            parent.tab.addTabItem({
                                tabid: 'purchase-purchaseOrder' + srcPoIdArr[i],
                                text: '<i class="iconfont icon-danye f14 mr5"></i>采购订单',
                                url: '/Uri/purchaseOrder_manage?id=' + srcPoIdArr[i]
                            });
                        }
                    }
                    if (Business.verifyRight('PC_QUERY') && srcpcid) {
                        srcPcIdArr = srcpcid.toString().split(',');
                        for (var i = 0; i < srcPcIdArr.length; i++) {
                            parent.tab.addTabItem({
                                tabid: 'purchase-purchaseContract' + srcPcIdArr[i],
                                text: '<i class="iconfont icon-danye f14 mr5"></i>特殊采购单',
                                url: '/Uri/purchaseContract_manage?id=' + srcPcIdArr[i]
                            });
                        }
                    }
                } else {
                    $.dialog.alert('暂无已采购单据！');
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
        });
    },
    callback: function(a, b, c, d) {
        var e = $('#grid').data('gridData');
        e || (e = {}, $('#grid').data('gridData', e));
        e[a.id] = a;
        'edit' == b ? ($('#grid').jqGrid('setRowData', a.id, a), c && (d == 1 ? c.api.close() : c.THISPAGE.resetForm())) : ($('#grid').jqGrid('addRowData', a.id, a, 'first'), c && (d == 1 ? c.api.close() : c.THISPAGE.resetForm()));
    }
};
$(function() {
    THISPAGE.init();
});
