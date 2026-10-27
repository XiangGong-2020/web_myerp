var urlParam = Public.urlParam();
var defaultPage = Public.getDefaultPage();
var queryConditions = {
    uId: $.inArray('EXPEND_ADD', defaultPage.SYSTEM.rights) !== -1 ? -1 : defaultPage.SYSTEM.uId,
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
    defaultSelected: urlParam.uId ? ['id', urlParam.uId] : ($.inArray('EXPEND_ADD', defaultPage.SYSTEM.rights) === -1 ? ['id', defaultPage.SYSTEM.uId] : 0),
    disabled: $.inArray('EXPEND_ADD', defaultPage.SYSTEM.rights) === -1 ? !0 : !1,
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
        {id: 1, name: '未付款'},
        {id: 2, name: '已拒绝'}
    ],
    value: 'id',
    text: 'name',
    defaultSelected: urlParam.unDone ? 1 : 0,
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
        $('#beginDate').val(defaultPage.SYSTEM.beginDate + ' 00:00:00');
        $('#endDate').val((urlParam.endDate || defaultPage.SYSTEM.endDate) + ' 23:59:59');
    },
    initGrid: function() {
        var a = Public.setGrid();
        $('#grid').jqGrid({
            url: '/Base/PayReq/list?type=1',
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
                name: 'companyId',
                label: '支出经营主体ID',
                hidden: !0
            }, {
                name: 'companyName',
                label: '支出经营主体',
                hidden: !0
            }, {
                name: 'expendTypeId',
                label: '支出类别ID',
                hidden: !0
            }, {
                name: 'expendTypeName',
                label: '支出类别',
                width: 66,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'amount',
                label: '申请金额',
                width: 80,
                fixed: !0,
                sortable: !0,
                formatter: 'currency'
            }, {
                name: 'title',
                label: '申请标题',
                width: 100,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'content',
                label: '附加说明',
                width: 100,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'imgs',
                label: '参考图片',
                width: 66,
                fixed: !0,
                sortable: !0,
                classes: 'ui-ellipsis',
                formatter: function(cellvalue, options, rowObject) {
                    imgs = '';
                    if (cellvalue) {
                        cellvalue.forEach(element => {
                            imgs += '<a target="_blank" href="/data/upfile/payReq/' + element + '"><span class="fl mr2 mb2 iconfont icon-tupian f20 gray poi" title="点击查看"></span></a>';
                        });
                    }
                    return imgs;
                }
            }, {
                name: 'type',
                label: '类型',
                hidden: !0
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
                width: 124,
                fixed: !0,
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
                width: 124,
                fixed: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray',
                formatter: function(cellvalue, options, rowObject) {
                    return ('0000-00-00 00:00:00' == cellvalue || undefined == cellvalue) ? '' : cellvalue;
                }
            }, {
                name: 'payId',
                label: '付款人ID',
                hidden: !0
            }, {
                name: 'payName',
                label: '付款人',
                width: 54,
                fixed: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray'
            }, {
                name: 'payTime',
                label: '付款时间',
                width: 124,
                fixed: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray',
                formatter: function(cellvalue, options, rowObject) {
                    return ('0000-00-00 00:00:00' == cellvalue || undefined == cellvalue) ? '' : cellvalue;
                }
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
                width: 84,
                fixed: !0,
                sortable: !0,
                formatter: function(a, b, c) {
                    var d = c.description ? '已拒绝' : (a == 0 ? '批准' : '已批准');
                    var e = c.description ? '删除拒绝理由可以取消拒绝' : (a == 0 ? '点击批准' : '点击反批');
                    var f = c.description ? 'hover-night' : (a == 0 ? 'hover-teal' : 'hover-green');
                    return '<span class="' + (c.description ? '' : 'check-btn ') + 'fl db pl5 pr5 h28 lh28 b-r-3 ' + f + '" title="' + e + '" data-id="' + c.id + '" data-checkid="' + a + '">' + d + '</span>' + ((a == 0 && c.description == '') ? '<span class="refuse-btn fl ml2 db pl5 pr5 h28 lh28 b-r-3 hover-red" title="点击拒绝" data-id="' + c.id + '">拒绝</span>' : '');
                }
            }, {
                name: 'moneyStatus',
                label: '付款',
                width: 54,
                fixed: !0,
                sortable: !0,
                align: 'center',
                formatter: function(a, b, c) {
                    var e = c.description ? '已拒绝' : (a == 0 ? '未付' : (a == 1 ? '部分' : '付完'));
                    var f = c.description ? 'hover-night' : (a == 0 ? 'hover-red' : (a == 1 ? 'hover-yellow' : 'hover-green'));
                    var g = c.description ? '删除拒绝理由可以取消拒绝' : (c.srcMoneyId == 0 ? '点击付款' : ('点击打开其他支出单' + '\r\n' + c.srcMoneyNo));
                    return '<span class="' + (c.description ? '' : 'moneyStatus-btn ') + 'db h28 lh28 b-r-3 ' + f + '" title="' + g + '" data-id="' + c.id + '" data-checkid="' + c.checkId + '" data-srcmoneyid="' + c.srcMoneyId + '">' + e + '</span>';
                }
            }, {
                name: 'isInvoice',
                label: '收条',
                width: 54,
                fixed: !0,
                sortable: !0,
                align: 'center',
                formatter: function(a, b, c) {
                    var d = c.description ? '已拒绝' : (a == 0 ? '未收' : '已收');
                    var e = c.description ? '删除拒绝理由可以取消拒绝' : (a == 0 ? '点击标记为已收' : '点击标记为未收');
                    var f = c.description ? 'hover-night' : (a == 0 ? 'hover-red' : 'hover-green');
                    return '<span class="' + (c.description ? '' : 'invoice-btn ') + 'db pl5 pr5 h28 lh28 b-r-3 ' + f + '" title="' + e + '" data-id="' + c.id + '" data-isinvoice="' + a + '">' + d + '</span>';
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
            $.dialog({
                title: '新增付款申请',
                content: 'url:/Uri/payReq_manage',
                data: {
                    oper: 'add',
                    callback: THISPAGE.callback
                },
                width: 1250,
                height: 500,
                max: !1,
                min: !1,
                cache: !1,
                lock: !0
            });
        });
        $('#grid').on('click', '.ui-icon-pencil', function(a) {
            a.preventDefault();
            var id = $(this).parent().data('id');
            $.dialog({
                title: '编辑付款申请',
                content: 'url:/Uri/payReq_manage',
                data: {
                    oper: 'edit',
                    rowId: id,
                    callback: THISPAGE.callback
                },
                width: 1250,
                height: 500,
                max: !1,
                min: !1,
                cache: !1,
                lock: !0
            });
        });
        $('#grid').on('click', '.ui-icon-trash', function(a) {
            a.preventDefault();
            var id = $(this).parent().data('id');
            id && $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                Public.ajaxPost('/Base/PayReq/delete', {
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
        });
        $('#grid').on('click', '.check-btn', function(a) {
            if (Business.verifyRight('PAYREQ_ADVANCE')) {
                var id = $(this).data('id');
                var checkId = Number($(this).data('checkid')) > 0 ? 0 : defaultPage.SYSTEM.uId;
                var checkName = Number($(this).data('checkid')) > 0 ? "" : defaultPage.SYSTEM.uName;
                var rowData = $('#grid').jqGrid('getRowData', id);
                id && Public.ajaxPost('/Base/PayReq/check', {
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
                title: '编辑付款申请',
                content: 'url:/Uri/payReq_manage',
                data: {
                    oper: 'edit',
                    rowId: id,
                    callback: THISPAGE.callback
                },
                width: 1250,
                height: 500,
                max: !1,
                min: !1,
                cache: !1,
                lock: !0
            });
        });
        $('.grid-wrap').on('click', '.moneyStatus-btn', function(a) {
            a.preventDefault();
            if (Business.verifyRight('EXPEND_ADD')) {
                var id = $(this).data('id');
                var checkid = $(this).data('checkid');
                var srcmoneyid = $(this).data('srcmoneyid');
                var rowData = $('#grid').jqGrid('getRowData', id);
                if (checkid == 0) {
                    $.dialog.alert('申请尚未审批！');
                } else {
                    if (srcmoneyid > 0) {
                        parent.tab.addTabItem({
                            tabid: 'money-expend' + srcmoneyid,
                            text: '<i class="iconfont icon-danye f14 mr5"></i>其他支出单',
                            url: '/Uri/expend_manage?id=' + srcmoneyid
                        });
                    } else {
                        var newTabId = (new Date).format("yyyyMMddhhmmss");
                        id && rowData && parent.tab.addTabItem({
                            tabid: 'money-expend' + newTabId,
                            text: '<i class="iconfont icon-danye f14 mr5"></i>其他支出单（新建）',
                            url: '/Uri/expend_manage?turnByPayReq&tabId=money-expend' + newTabId,
                            data: {
                                type: 1,
                                id: id,
                                amount: rowData.amount,
                                typeId: rowData.expendTypeId,
                                typeName: rowData.expendTypeName,
                                title: '原付款申请ID：' + id + '，申请人：' + rowData.uName + '，标题：' + rowData.title
                            }
                        });
                    }
                }
            }
        });
        $('#grid').on('click', '.invoice-btn', function(a) {
            if (Business.verifyRight('PAYREQ_ADVANCE')) {
                var id = $(this).data('id');
                var isInvoice = $(this).data('isinvoice');
                id && Public.ajaxPost('/Base/PayReq/invoice', {
                    id: id,
                    isInvoice: isInvoice == 0 ? 1 : 0
                }, function(res) {
                    if (res && 200 == res.status) {
                        $.dialog.tips('标记成功！');
                        $('#grid').jqGrid('setRowData', id, res.data);
                    } else {
                        $.dialog.tips('标记失败！' + res.msg, 2);
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
            Public.autoGridWidth($('#grid'));
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
