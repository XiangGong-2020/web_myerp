var defaultPage = Public.getDefaultPage();
var queryConditions = {
    status1: '',
    status2: '',
    status3: 0,
    weight: 0,
    amount: 0,
    beginDate: Public.reduceMonths((new Date).format('yyyy-MM') + '-01', 1) + ' 00:00:00',
    endDate: defaultPage.SYSTEM.endDate + ' 23:59:59',
    skey: ''
};
var deliverCompanyCode100List = {};
var combo1 = $('#combo1').combo({
    data: '/Base/User/queryAll',
    ajaxOptions: {
        formatData: function(a) {
            return a.data.rows.unshift({
                id: -1,
                name: '所有扫码人'
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
            $('#search').parent().find('.current').trigger('click');
        }
    }
}).getCombo();
var combo2 = $('#combo2').combo({
    data: '/Base/DeliverCompany/queryAll',
    ajaxOptions: {
        formatData: function(a) {
            for (var i = 0; i < a.data.rows.length; i++) {
                deliverCompanyCode100List['' + a.data.rows[i].name] = a.data.rows[i].code100;
            }
            return a.data.rows.unshift({
                id: -1,
                name: '所有快递公司'
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
        {id: 0, name: '按打印时间'},
        {id: 1, name: '按打包扫码时间'},
        {id: 2, name: '按称重扫码时间'}
    ],
    value: 'id',
    text: 'name',
    width: 114,
    listHeight: 100,
    defaultSelected: $('#combo3').data('defItem') || void 0,
    cache: !1,
    editable: !1,
    trigger: !0,
    emptyOptions: !1,
    callback: {
        onListClick: function() {
            $('#search').parent().find('.current').trigger('click');
        }
    },
    queryDelay: 0
}).getCombo();
var THISPAGE = {
    init: function() {
        THISPAGE.initDom();
        THISPAGE.initGrid();
        THISPAGE.initEvent();
    },
    initDom: function() {
        $('#beginDate').val(Public.reduceMonths((new Date).format('yyyy-MM') + '-01', 1) + ' 00:00:00');
        $('#endDate').val(defaultPage.SYSTEM.endDate + ' 23:59:59');
    },
    initGrid: function() {
        var a = Public.setGrid();
        $('#grid').jqGrid({
            url: '/Base/DeliverBag/list',
            postData: queryConditions,
            datatype: 'json',
            colModel: [{
                name: 'id',
                label: 'ID',
                width: 60,
                fixed: !0,
                hidden: !0,
                sortable: !0
            }, {
                name: 'no',
                label: '<span class="ml10">运单号</span>',
                classes: 'ti10',
                width: 124,
                fixed: !0,
                sortable: !1
            }, {
                name: 'company',
                label: '所属快递',
                width: 56,
                fixed: !0,
                sortable: !1
            }, {
                name: 'address',
                label: '发往',
                width: 36,
                fixed: !0,
                sortable: !1
            }, {
                name: 'chType',
                label: '类型',
                width: 36,
                fixed: !0,
                sortable: !1
            }, {
                name: 'sellerNick',
                label: '店铺标识',
                width: 60,
                fixed: !0,
                sortable: !1,
                title: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'orderNo',
                label: '包含订单',
                width: 58,
                fixed: !0,
                title: !0,
                sortable: !1,
                classes: 'ui-ellipsis'
            }, {
                name: 'lastUpdator',
                label: '打印人',
                width: 46,
                fixed: !0,
                sortable: !1,
                title: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'printTime',
                label: '打印时间',
                width: 124,
                fixed: !0,
                sortable: !0
            }, {
                name: 'lastFourMobileNum',
                label: '联系方式后4位',
                width: 62,
                fixed: !0,
                hidden: !0,
                sortable: !1
            }, {
                name: 'weight',
                label: '计重',
                width: 50,
                fixed: !0,
                sortable: !0,
                formatter: 'currency'
            }, {
                name: 'amount',
                label: '计费',
                width: 60,
                fixed: !0,
                sortable: !0,
                formatter: 'currency'
            }, {
                name: 'packName',
                label: '打包扫码人',
                width: 70,
                fixed: !0,
                sortable: !1
            }, {
                name: 'packTime',
                label: '打包扫码时间',
                width: 124,
                fixed: !0,
                sortable: !0
            }, {
                name: 'scanName',
                label: '称重扫码人',
                width: 70,
                fixed: !0,
                sortable: !1
            }, {
                name: 'scanTime',
                label: '称重扫码时间',
                width: 124,
                fixed: !0,
                sortable: !0
            }, {
                name: 'scanWay',
                label: '称重扫码源',
                width: 70,
                fixed: !0,
                sortable: !1,
                formatter: function(a, b, c) {
                    return a == 0 ? '' : (a == 1 ? '手持设备' : '扫码台<a target="_blank" href="http://192.168.0.254:8081/shared/' + c.scanTime.slice(0, 10) + '/' + c.no + '/hkws.jpg"><span class="iconfont icon-tupian ml5 f16 gray poi" title="点击查看扫码台照片"></span></a>');
                }
            }, {
                name: 'targetAmount',
                label: '快递赔偿',
                width: 60,
                fixed: !0,
                sortable: !1,
                formatter: 'currency'
            }, {
                name: 'description',
                label: '备注',
                width: 46,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray'
            }, {
                name: 'dullOperHis',
                label: '滞留处理记录',
                width: 100,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray'
            }, {
                name: '',
                label: '轨迹',
                width: 60,
                fixed: !0,
                sortable: !1,
                align: 'center',
                formatter: function(cellvalue, options, rowObject) {
                    return '<div class="search-deliver db h28 lh28 b-r-3 hover-white" data-id="' + rowObject.id + '">查轨迹</div>';
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
            gridview: !0,
            multiselect: !1,
            shrinkToFit: !0,
            forceFit: !1,
            viewsortcols: [true, 'vertical', true],
            sortname: 'printTime',
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
                    $('#search').trigger('click').addClass('current').siblings().removeClass('current');
                }, 300);
            }
        });
        $('#search').on('click', function() {
            queryConditions.status1 = combo1.getValue() == -1 ? '' : combo1.getText();
            queryConditions.status2 = combo2.getValue() == -1 ? '' : combo2.getText();
            queryConditions.status3 = combo3.getValue();
            queryConditions.weight = $('#search-weight').val() || 0;
            queryConditions.amount = $('#search-amount').val() || 0;
            queryConditions.beginDate = $.trim($('#beginDate').val());
            queryConditions.endDate = $.trim($('#endDate').val());
            queryConditions.skey = $.trim($('#skey').val()).split(/[ ,，]+/).toString();
            $('#grid').jqGrid('setGridParam', {
                postData: queryConditions,
                page: 1
            }).trigger('reloadGrid');
        });
        $('#curMonth').on('click', function() {
            $(this).addClass('current').siblings().removeClass('current');
            $('#beginDate').val((new Date).format('yyyy-MM') + '-01' + ' 00:00:00');
            $('#endDate').val((new Date).format('yyyy-MM-dd') + ' 23:59:59');
            $('#search').trigger('click');
        });
        $('#lastMonth').on('click', function() {
            $(this).addClass('current').siblings().removeClass('current');
            $('#beginDate').val(Public.reduceMonths((new Date).format('yyyy-MM') + '-01', 1) + ' 00:00:00');
            $('#endDate').val(Public.reduceDays((new Date).format('yyyy-MM') + '-01', 1) + ' 23:59:59');
            $('#search').trigger('click');
        });
        $('#refresh').on('click', function() {
            window.location.reload();
        });
        $('#add').on('click', function(a) {
            a.preventDefault();
            if (Business.verifyRight('DELIVERBAG_ADD')) {
                $.dialog({
                    title: '新增发包记录',
                    content: 'url:/Uri/deliver_bag_manage',
                    data: {
                        oper: 'add',
                        callback: THISPAGE.callback
                    },
                    width: 1250,
                    height: 550,
                    max: !1,
                    min: !1,
                    cache: !1,
                    lock: !0
                });
            }
        });
        $('.grid-wrap').on('click', '.search-deliver', function(a) {
            a.preventDefault();
            var id = $(this).data('id');
            var rowData = $('#grid').jqGrid('getRowData', id);
            if (rowData.company == '京东快递') {
                window.open('https://www.jdl.com/orderSearch/?waybillCodes=' + rowData.no);
            } else {
                $.dialog({
                    title: '快递查询',
                    content: 'url:/Uri/deliver_search',
                    data: {
                        company: rowData.company,
                        type: deliverCompanyCode100List[rowData.company],
                        postid: rowData.no,
                        phone: rowData.lastFourMobileNum
                    },
                    width: 1250,
                    height: 500,
                    max: !1,
                    min: !1,
                    cache: !1,
                    lock: !0
                });
            }
        });
        $('.grid-wrap').on('click', '.ui-icon-pencil', function(a) {
            a.preventDefault();
            if (Business.verifyRight('DELIVERBAG_UPDATE')) {
                var id = $(this).parent().data('id');
                var rowData = $('#grid').jqGrid('getRowData', id);
                $.dialog({
                    title: '编辑发包记录',
                    content: 'url:/Uri/deliver_bag_manage',
                    data: {
                        oper: 'edit',
                        rowId: id,
                        callback: THISPAGE.callback
                    },
                    width: 1250,
                    height: 550,
                    max: !1,
                    min: !1,
                    cache: !1,
                    lock: !0
                });
            }
        });
        $('.grid-wrap').on('click', '.ui-icon-trash', function(a) {
            a.preventDefault();
            if (Business.verifyRight('DELIVERBAG_DELETE')) {
                var id = $(this).parent().data('id');
                var rowData = $('#grid').jqGrid('getRowData', id);
                id && rowData && $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                    Public.ajaxPost('/Base/DeliverBag/delete', {
                        id: id,
                        no: rowData.no
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
