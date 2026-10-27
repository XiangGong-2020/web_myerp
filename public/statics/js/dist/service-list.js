var urlParam = Public.urlParam();
var defaultPage = Public.getDefaultPage();
var queryConditions = {
    categoryId: urlParam.categoryId || -1,
    isDisable: urlParam.isDisable || 0,
    targetName: '',
    beginName: urlParam.beginName || '',
    beginDate: '',
    endDate: urlParam.endDate ? (urlParam.endDate + ' 23:59:59') : '',
    skey: ''
};
var typeAll = {};
var statusCombo1 = Business.categoryCombo($('#combo1'), {
    ajaxOptions: {
        formatData: function(a) {
            a.data.rows.forEach(element => {
                if (element.id > 0) {
                    var id = '' + element.id;
                    typeAll[id] = element.name;
                }
            });
            Object.keys(typeAll).length > 0 && THISPAGE.initGrid();
            return a.data.rows.unshift({
                id: -1,
                name: '所有反馈类别'
            }), a.data.rows;
        }
    },
    defaultSelected: 0,
    width: 140,
    listHeight: 400,
    callback: {
        onListClick: function() {
            $('#search').trigger('click');
        }
    },
}, 'servicetype');
var statusCombo2 = $('#combo2').combo({
    data: [
        {id: -1, name: '所有状态'},
        {id: 0, name: '未解决'},
        {id: 1, name: '已解决'}
    ],
    value: 'id',
    text: 'name',
    defaultSelected: urlParam.isDisable ? ['id', parseInt(urlParam.isDisable)] : 1,
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
        THISPAGE.initEvent();
    },
    initDom: function() {
        $('#beginDate').val(defaultPage.SYSTEM.beginDate + ' 00:00:00');
        $('#endDate').val(urlParam.endDate ? (urlParam.endDate + ' 23:59:59') : defaultPage.SYSTEM.endDate + ' 23:59:59');
        Public.ajaxGet('/Base/User/queryAll?normal=yes', {}, function(data) {
            if (data.status === 200) {
                var normalUser = disableUser = '';
                $.each(data.data.rows, function(i, v) {
                    if (v.isDisable == 0) {
                        normalUser += '<option value="' + v.id + '">' + v.name + '</option>';
                    } else {
                        disableUser += '<option value="' + v.id + '">' + v.name + '</option>';
                    }
                });
                Public.ajaxGet('/Base/DeliverCompany/queryAll', {}, function(data1) {
                    if (data1.status === 200) {
                        var deliverCompany = '';
                        $.each(data1.data.rows, function(i1, v1) {
                            deliverCompany += '<option value="' + v1.id + '">' + v1.name + '</option>';
                        });
                        /* 异步获取数据成功之后再填充选项并加载选择事件 */
                        $('#normalUser').html(normalUser);
                        $('#disableUser').html(disableUser);
                        $('#deliverCompany').html(deliverCompany);
                        $('#targetNameCombo').fSelect({
                            placeholder: '选择责任人',
                            showSearch: !0,
                            listHeight: 490
                        });
                    } else {
                        $.dialog.tips(data1.msg, 2);
                    }
                });
            } else {
                $.dialog.tips(data.msg, 2);
            }
        });
    },
    initGrid: function() {
        var a = Public.setGrid();
        $('#grid').jqGrid({
            url: '/Base/Service/list',
            postData: queryConditions,
            datatype: 'json',
            colModel: [{
                name: 'id',
                label: '<span class="ml10">ID</span>',
                width: 6,
                fixed: !0,
                sortable: !0,
                classes: 'ti10 gray'
            }, {
                name: 'categoryId',
                label: '反馈类别',
                width: 66,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis',
                formatter: function(cellvalue, options, rowObject) {
                    return typeAll[cellvalue];
                }
            }, {
                name: 'content',
                label: '反馈内容',
                width: 100,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'orderNo',
                label: '订单号',
                width: 68,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'hover ui-ellipsis'
            }, {
                name: 'deliverNo',
                label: '运单号',
                width: 68,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'hover ui-ellipsis'
            }, {
                name: 'targetAmount',
                label: '损失金额',
                width: 70,
                fixed: !0,
                sortable: !0,
                formatter: 'currency'
            }, {
                name: 'targetName',
                label: '责任人',
                width: 80,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'disStatus',
                label: '异议状态',
                width: 150,
                fixed: !0,
                title: !1,
                sortable: !0,
                formatter: function(a, b, c) {
                    var d = '';
                    if (a == 0 && c.targetName.length != 0 && c.targetName.length != 4 && c.targetAmount > 0) {
                        d = '<span class="red">待责任人确认或提交异议</span>';
                    } else if (a == 1) {
                        d = '<span class="fl red">收到异议</span><span id="reSend" data-id="' + c.id + '" class="ml10 db fl pl10 pr10 lh22 hover-red b-r-3" title="查看责任人提交的异议做出核实与判断，视情况进行相应修改或追加反馈内容后，如果仍存在内部责任人与损失金额，可以点击重新推送给相关责任人再次确认。">重新推送</span>';
                    } else if (a == 2) {
                        d = '<span class="red">已重新推送给责任人</span>';
                    } else if (a == 3) {
                        d = '<span class="green">责任人已确认属实</span>';
                    }
                    return d;
                }
            }, {
                name: 'disReason',
                label: '异议内容',
                width: 100,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis red'
            }, {
                name: 'beginName',
                label: '录入人',
                width: 54,
                fixed: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray'
            }, {
                name: 'beginDate',
                label: '录入时间',
                width: 84,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray'
            }, {
                name: 'endName',
                label: '跟进人',
                width: 54,
                fixed: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray'
            }, {
                name: 'endDate',
                label: '跟进时间',
                width: 84,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray',
                formatter: function(a, b, c) {
                    return ('0000-00-00 00:00:00' == a || undefined == a) ? '' : a;
                }
            }, {
                name: 'isDisable',
                label: '状态',
                width: 58,
                fixed: !0,
                sortable: !0,
                align: 'center',
                formatter: function(a, b, c) {
                    var d = 0 == a ? '未解决' : '已解决';
                    var e = 0 == a ? '点击设置为 已解决' : '点击设置为 未解决';
                    var f = 0 == a ? 'hover-red' : 'hover-green';
                    return '<span class="disable-btn db h28 lh28 b-r-3 ' + f + '" title="' + e + '" data-isdisable="' + a + '" data-id="' + c.id + '">' + d + '</span>';
                }
            }, {
                name: 'operate',
                label: '操作',
                width: 72.8,
                fixed: !0,
                formatter: Public.operFmatter
            }, {
                name: 'orderId',
                label: '订单ID',
                hidden: !0
            }, {
                name: 'deliverId',
                label: '运单ID',
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
            },
            loadError: function(a, b) {
                $.dialog.tips('Type: ' + b + '; Response: ' + a.status + ' ' + a.statusText, 2);
            },
            onCellSelect: function(a, b) {
                var rowData = $('#grid').jqGrid('getRowData', a);
                if (b == 3 && rowData.orderId > 0 && Business.verifyRight('SO_QUERY')) {
                    parent.tab.addTabItem({
                        tabid: 'sales-salesOrder' + rowData.orderId,
                        text: '<i class="iconfont icon-danye f14 mr5"></i>销售订单',
                        url: '/Uri/salesOrder_manage?id=' + rowData.orderId
                    });
                } else if (b == 4 && rowData.deliverId > 0 && Business.verifyRight('DELIVERBAG_QUERY')) {
                    $.dialog({
                        title: '查看发包记录',
                        content: 'url:/Uri/deliver_bag_manage',
                        data: {
                            oper: 'edit',
                            rowId: rowData.deliverId
                        },
                        width: 600,
                        height: 550,
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
            },
            ondblClickRow: function(a) {
                $('#' + a).find('.ui-icon-pencil').trigger('click');
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
            queryConditions.categoryId = statusCombo1 ? statusCombo1.getValue() : -1;
            queryConditions.isDisable = statusCombo2 ? statusCombo2.getValue() : -1;
            queryConditions.targetName = $.trim($('.fs-input').val());
            queryConditions.beginDate = $('#beginDate').val() ? $.trim($('#beginDate').val()) : '';
            queryConditions.endDate = $('#endDate').val() ? $.trim($('#endDate').val()) : '';
            queryConditions.skey = $.trim($('#skey').val()).split(/[ ,，]+/).toString();
            $('#grid').jqGrid('setGridParam', {
                postData: queryConditions,
                page: 1
            }).trigger('reloadGrid');
        });
        $('.mod-search').on('click', '.fs-option, #selectAllBtn, #clearAllBtn', function() {
            setTimeout(function() {
                $('#search').trigger('click');
            }, 500);
        });
        $('#refresh').on('click', function() {
            window.location.reload();
        });
        $('#add').on('click', function(a) {
            a.preventDefault();
            if (Business.verifyRight('SERVICE_ADD')) {
                var b = '新增客户之声';
                var c = {
                    oper: 'add',
                    callback: THISPAGE.callback
                };
                $.dialog({
                    title: b,
                    content: 'url:/Uri/service_manage',
                    data: c,
                    width: 1250,
                    height: 500,
                    max: !1,
                    min: !1,
                    lock: !0,
                    cache: !1
                });
            }
        });
        $('.grid-wrap').on('click', '#reSend', function(a) {
            a.preventDefault();
            var id = $(this).data('id');
            id && Public.ajaxPost('/Base/Service/reSend', {
                id: id
            }, function(res) {
                if (res && 200 == res.status) {
                    $.dialog.tips('推送成功！');
                    $('#grid').jqGrid('setRowData', id, res.data);
                } else {
                    $.dialog.tips('推送失败！' + res.msg, 2);
                }
            });

        });
        $('.grid-wrap').on('click', '.ui-icon-pencil', function(a) {
            a.preventDefault();
            var b = $(this).parent().data('id');
            var c = '编辑客户之声';
            var d = {
                oper: 'edit',
                rowId: b,
                callback: THISPAGE.callback
            };
            $.dialog({
                title: c,
                content: 'url:/Uri/service_manage',
                data: d,
                width: 1250,
                height: 500,
                max: !1,
                min: !1,
                lock: !0,
                cache: !1
            });
        });
        $('.grid-wrap').on('click', '.ui-icon-trash', function(a) {
            a.preventDefault();
            if (Business.verifyRight('SERVICE_DELETE')) {
                var id = $(this).parent().data('id');
                id && $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                    Public.ajaxPost('/Base/Service/delete', {
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
        $('.grid-wrap').on('click', '.disable-btn', function(a) {
            a.stopPropagation();
            a.preventDefault();
            if (Business.verifyRight('SERVICE_UPDATE')) {
                var id = $(this).data('id');
                var isDisable = Number(!$(this).data('isdisable'));
                var rowData = $('#grid').jqGrid('getRowData', id);
                id && Public.ajaxPost('/Base/Service/disable', {
                    id: id,
                    isDisable: isDisable,
                    orderNo: rowData.orderNo
                }, function(res) {
                    if (res && 200 == res.status) {
                        $.dialog.tips('解决状态标记成功！');
                        $('#grid').jqGrid('setCell', id, 'isDisable', isDisable);
                        $('#grid').jqGrid('setCell', id, 'endName', defaultPage.SYSTEM.uName);
                        $('#grid').jqGrid('setCell', id, 'endDate', (new Date).format('yyyy-MM-dd hh:mm:ss'));
                    } else {
                        $.dialog.tips('解决状态标记失败！' + res.msg, 2);
                    }
                });
            }
        });
        $(window).on('keydown', function(e) {
            var curKey = e.which;
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
        'edit' == b ? ($('#grid').jqGrid('setRowData', a.id, a), c && (d == 1 ? c.api.close() : c.THISPAGE.resetForm())) : (a.isDisable = 0, $('#grid').jqGrid('addRowData', a.id, a, 'first'), c && (d == 1 ? c.api.close() : c.THISPAGE.resetForm()));
    }
};
$(function() {
    THISPAGE.init();
});
