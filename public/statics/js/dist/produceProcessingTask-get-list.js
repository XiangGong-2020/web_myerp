var defaultPage = Public.getDefaultPage();
var queryConditions = {
    status1: '',
    status2: -1,
    beginDate: '',
    endDate: '',
    skey: ''
};
var statusCombo1 = $('#combo1').combo({
    data: '/Base/User/queryAll?department=生产部',
    ajaxOptions: {
        formatData: function(a) {
            return a.data.rows.unshift({
                id: -1,
                name: '所有领取人'
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
var statusCombo2 = $('#combo2').combo({
    data: [
        {id: -1, name: '所有完成状态'},
        {id: 0, name: '未完全交付'},
        {id: 1, name: '有交付待审核'},
        {id: 2, name: '已完全交付'}
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
        $('#beginDate').val(defaultPage.SYSTEM.beginDate + ' 00:00:00');
        $('#endDate').val(defaultPage.SYSTEM.endDate + ' 23:59:59');
    },
    initGrid: function() {
        var a = Public.setGrid();
        $('#grid').jqGrid({
            url: '/Base/ProProcessingTask/get_list_all',
            postData: queryConditions,
            datatype: 'json',
            colModel: [{
                name: 'iid',
                label: '<span class="ml10">任务编号</span>',
                width: 76,
                fixed: !0,
                sortable: !0,
                classes: 'ti10'
            }, {
                name: 'projectType',
                label: '项目类型',
                width: 114,
                fixed: !0,
                sortable: !0,
                formatter: function(a, b, c) {
                    return a == 0 ? '开发板整体生产类' : (a == 1 ? '电路组件焊接类' : (a == 2 ? '包装组装类' : '其他加工类'));
                }
            }, {
                name: 'projectName',
                label: '项目名称',
                width: 100,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'taskQty',
                label: '任务产量',
                width: 66,
                fixed: !0,
                sortable: !0,
                formatter: function(cellvalue, options, rowObject) {
                    return Number(cellvalue) == 0 ? '' : cellvalue;
                }
            }, {
                name: 'lastDate',
                label: '交付限期',
                width: 130,
                fixed: !0,
                sortable: !0
            }, {
                name: 'uName',
                label: '领取人',
                width: 54,
                fixed: !0,
                sortable: !0
            }, {
                name: 'createTime',
                label: '领取时间',
                width: 130,
                fixed: !0,
                title: !1,
                sortable: !0,
                classes: 'gray'
            }, {
                name: 'isTest',
                label: '领取工序',
                width: 70,
                fixed: !0,
                title: !1,
                sortable: !0,
                formatter: function(a, b, c) {
                    return a == 1 ? '<span class="green">测试工序</span>' : '加工工序';
                }
            }, {
                name: 'getQty',
                label: '领取产量',
                width: 66,
                fixed: !0,
                sortable: !0,
                formatter: function(cellvalue, options, rowObject) {
                    return Number(cellvalue) == 0 ? '' : cellvalue;
                }
            }, {
                name: 'upQty',
                label: '已交付产量',
                width: 80,
                fixed: !0,
                sortable: !0,
                formatter: function(cellvalue, options, rowObject) {
                    return Number(cellvalue) == 0 ? '' : cellvalue;
                }
            }, {
                name: 'doneStatus',
                label: '完成状态',
                width: 140,
                fixed: !0,
                title: !1,
                sortable: !0,
                formatter: function(a, b, c) {
                    var d = 0 == a ? '未完全交付' : (1 == a ? '有交付待审核' : '已完全交付');
                    var e = 0 == a ? 'yellow' : (1 == a ? 'blue' : 'green');
                    var f = (0 == a && (new Date).format('yyyy-MM-dd hh:mm:ss') > c.lastDate) ? '<span class="db fr h30 lh30 pl5 pr5 white bg-yellow b-r-3">已超时</span>' : '';
                    return '<span class="' + e + '">' + d + '</span>' + f;
                }
            }, {
                name: 'backInfo',
                label: '遇阻反馈',
                width: 100,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis red'
            }, {
                name: 'transInfo',
                label: '转让中',
                width: 100,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis',
                formatter: function(a, b, c) {
                    var d = '';
                    if (a) {
                        $.each(a, function(k, v) {
                            d += (k.includes(',') ? '整组' : '单人') + '转让产量' + v.qty + '给' + k + ' ' + v.time + "\r\n";
                        });
                    }
                    return d;
                }
            }, {
                name: 'transHis',
                label: '转让历史',
                width: 100,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray'
            }, {
                name: 'id',
                label: 'ID',
                width: 0,
                hidden: !0
            }],
            cmTemplate: {sortable: !1, title: !1},
            jsonReader: {root: 'data.rows', records: 'data.records', total: 'data.total', repeatitems: !1, id: 'id'},
            width: a.w,
            height: a.h - 45,
            altRows: !1,
            gridview: !0,
            rownumbers: !1,
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
            footerrow: !0,
            userDataOnFooter: !0,
            loadComplete: function(data) {
                THISPAGE.calTotal(data.data);
                data.status == -1 && $.dialog.tips(data.msg, 2);
            },
            loadError: function(a, b) {
                $.dialog.tips('Type: ' + b + '; Response: ' + a.status + ' ' + a.statusText, 2);
            }
        });
    },
    initEvent: function() {
        laydate.render({
            elem: '#datePicker',
            type: 'datetime',
            range: ['#beginDate', '#endDate'],
            done: function() {
                setTimeout(function() {
                    $('#search').trigger('click').addClass('current').siblings().removeClass('current');
                }, 300);
            }
        });
        $('#search').on('click', function() {
            queryConditions.status1 = statusCombo1.getValue() == -1 ? '' : statusCombo1.getText();
            queryConditions.status2 = statusCombo2.getValue();
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
        $('#searchAll').on('click', function() {
            $(this).addClass('current').siblings().removeClass('current');
            $('#beginDate').val(defaultPage.SYSTEM.beginDate + ' 00:00:00');
            $('#endDate').val(defaultPage.SYSTEM.endDate + ' 23:59:59');
            $('#search').trigger('click');
        });
        $('#refresh').on('click', function() {
            window.location.reload();
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
    calTotal: function(data) {
        $('#grid').jqGrid('footerData', 'set', {
            iid: '<span class="ml10">合计：</span>',
            getQty: data.totalQty.totalGetQty,
            upQty: data.totalQty.totalUpQty
        });
    }
};
$(function() {
    THISPAGE.init();
});
