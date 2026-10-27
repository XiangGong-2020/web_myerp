var urlParam = Public.urlParam();
var defaultPage = Public.getDefaultPage();
var queryConditions = {
    billfromId: -1,
    isDisable: urlParam.isDisable || -1,
    beginDate: '',
    endDate: urlParam.endDate ? (urlParam.endDate + ' 23:59:59') : '',
    skey: ''
};
var typeAll = {};
var statusCombo1 = $('#combo1').combo({
    data: '/Base/Billfrom/queryAll?isDisable=0&exclude=手工录入,官网',
    ajaxOptions: {
        formatData: function(a) {
            a.data.rows.forEach(element => {
                if (element.id > 0) {
                    var id = '' + element.id;
                    typeAll[id] = element.name;
                }
            });
            THISPAGE.initGrid();
            return a.data.rows.unshift({
                id: -1,
                name: '所有渠道店铺'
            }), a.data.rows;
        }
    },
    value: 'id',
    text: 'name',
    defaultSelected: 0,
    width: 200,
    listHeight: 500,
    callback: {
        onListClick: function() {
            $('#search').trigger('click');
        }
    }
}).getCombo();
var statusCombo2 = $('#combo2').combo({
    data: [
        {id: -1, name: '所有状态'},
        {id: 0, name: '未处理'},
        {id: 1, name: '已处理'},
        {id: 2, name: '已放弃'}
    ],
    value: 'id',
    text: 'name',
    defaultSelected: urlParam.isDisable ? ['id', parseInt(urlParam.isDisable)] : 0,
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
    },
    initGrid: function() {
        var a = Public.setGrid();
        $('#grid').jqGrid({
            url: '/Base/ReviewHold/list',
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
                name: 'billfromId',
                label: '渠道店铺',
                width: 150,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis',
                formatter: function(cellvalue, options, rowObject) {
                    return typeAll[cellvalue];
                }
            }, {
                name: 'orderNo',
                label: '订单号',
                width: 150,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'hover ui-ellipsis'
            }, {
                name: 'level',
                label: '等级',
                width: 58,
                fixed: !0,
                sortable: !0,
                formatter: function(a, b, c) {
                    return a == 0 ? '中评' : '差评';
                }
            }, {
                name: 'description',
                label: '备注',
                width: 100,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
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
                classes: 'ui-ellipsis gray'
            }, {
                name: 'disableTime',
                label: '最终处理时间',
                width: 124,
                fixed: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray'
            }, {
                name: 'isDisable',
                label: '状态',
                width: 82,
                fixed: !0,
                sortable: !0,
                align: 'center',
                formatter: function(a, b, c) {
                    if (a == 0) {
                        return '<span class="disable-btn fl pl5 pr5 tc h28 lh28 b-r-3 hover-red" title="点击转为已处理" data-isdisable="1" data-id="' + c.id + '">处理</span>'
                            + '<span class="disable-btn fl ml2 pl5 pr5 tc h28 lh28 b-r-3 hover-red" title="点击放弃跟进" data-isdisable="2" data-id="' + c.id + '">放弃</span>';
                    } else if (a == 1) {
                        return '<span class="disable-btn db tc h28 lh28 b-r-3 hover-green" title="点击转为未处理" data-isdisable="0" data-id="' + c.id + '">已处理</span>';
                    } else if (a == 2) {
                        return '<span class="disable-btn db tc h28 lh28 b-r-3 hover-gray" title="点击转为未处理" data-isdisable="0" data-id="' + c.id + '">已放弃</span>';
                    }
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
                name: 'uId',
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
                if (b == 2 && rowData.orderId > 0 && Business.verifyRight('SO_QUERY')) {
                    parent.tab.addTabItem({
                        tabid: 'sales-salesOrder' + rowData.orderId,
                        text: '<i class="iconfont icon-danye f14 mr5"></i>销售订单',
                        url: '/Uri/salesOrder_manage?id=' + rowData.orderId
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
            queryConditions.billfromId = statusCombo1.getValue();
            queryConditions.isDisable = statusCombo2.getValue();
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
            if (Business.verifyRight('SERVICE_ADD')) {
                $.dialog({
                    title: '新增差评删改',
                    content: 'url:/Uri/reviewHold_manage',
                    data: {
                        oper: 'add',
                        callback: THISPAGE.callback
                    },
                    width: 1250,
                    height: 200,
                    max: !1,
                    min: !1,
                    lock: !0,
                    cache: !1
                });
            }
        });
        $('.grid-wrap').on('click', '.ui-icon-pencil', function(a) {
            a.preventDefault();
            if (Business.verifyRight('SERVICE_UPDATE')) {
                var id = $(this).parent().data('id');
                var rowData = $('#grid').jqGrid('getRowData', id);
                if (rowData.uName == defaultPage.SYSTEM.uName) {
                    $.dialog({
                        title: '编辑差评删改',
                        content: 'url:/Uri/reviewHold_manage',
                        data: {
                            oper: 'edit',
                            rowId: id,
                            callback: THISPAGE.callback
                        },
                        width: 1250,
                        height: 200,
                        max: !1,
                        min: !1,
                        lock: !0,
                        cache: !1
                    });
                } else {
                    $.dialog.tips('不能操作别人录入的差评删改！', 2);
                }
            }
        });
        $('.grid-wrap').on('click', '.ui-icon-trash', function(a) {
            a.preventDefault();
            if (Business.verifyRight('SERVICE_DELETE')) {
                var id = $(this).parent().data('id');
                id && $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                    Public.ajaxPost('/Base/ReviewHold/delete', {
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
                var rowData = $('#grid').jqGrid('getRowData', id);
                if (rowData.uName == defaultPage.SYSTEM.uName) {
                    var isDisable = Number($(this).data('isdisable'));
                    Public.ajaxPost('/Base/ReviewHold/disable', {
                        id: id,
                        isDisable: isDisable
                    }, function(res) {
                        if (res && 200 == res.status) {
                            $.dialog.tips('操作成功！');
                            $('#grid').jqGrid('setCell', id, 'isDisable', isDisable);
                            $('#grid').jqGrid('setCell', id, 'disableTime', (new Date).format('yyyy-MM-dd hh:mm:ss'));
                        } else {
                            $.dialog.tips('操作失败！' + res.msg, 2);
                        }
                    });
                } else {
                    $.dialog.tips('不能操作别人录入的差评删改！', 2);
                }
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
