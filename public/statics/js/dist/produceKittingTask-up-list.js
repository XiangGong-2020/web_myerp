var urlParam = Public.urlParam();
var defaultPage = Public.getDefaultPage();
var queryConditions = {
    status1: '',
    status2: urlParam.unDone ? 1 : 1,
    beginDate: '',
    endDate: urlParam.endDate ? (urlParam.endDate + ' 23:59:59') : '',
    skey: ''
};
var statusCombo1 = $('#combo1').combo({
    data: '/Base/User/queryAll?department=生产部',
    ajaxOptions: {
        formatData: function(a) {
            return a.data.rows.unshift({
                id: -1,
                name: '所有交付人'
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
        {id: 0, name: '所有入库状态'},
        {id: 1, name: '未点货入库'},
        {id: 2, name: '已点货入库'}
    ],
    value: 'id',
    text: 'name',
    defaultSelected: urlParam.unDone ? 1 : 1,
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
        $('#endDate').val((urlParam.endDate || defaultPage.SYSTEM.endDate) + ' 23:59:59');
    },
    initGrid: function() {
        var a = Public.setGrid();
        $('#grid').jqGrid({
            url: '/Base/ProKittingTask/up_list_all',
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
                name: 'lastDate',
                label: '交付限期',
                width: 130,
                fixed: !0,
                sortable: !0
            }, {
                name: 'goodsText',
                label: '任务产品',
                width: 100,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'uName',
                label: '交付人',
                width: 54,
                fixed: !0,
                sortable: !0
            }, {
                name: 'upQty',
                label: '交付产量',
                width: 66,
                fixed: !0,
                sortable: !0,
                formatter: function(cellvalue, options, rowObject) {
                    return Number(cellvalue) == 0 ? '' : cellvalue;
                }
            }, {
                name: 'upImgs',
                label: '交付照片',
                width: 66,
                fixed: !0,
                sortable: !0,
                classes: 'ui-ellipsis',
                formatter: function(cellvalue, options, rowObject) {
                    imgs = '';
                    if (cellvalue) {
                        cellvalue.forEach(element => {
                            imgs += '<a target="_blank" href="/data/upfile/proKittingTask/' + element + '"><span class="fl mr2 mb2 iconfont icon-tupian f20 gray poi" title="点击查看"></span></a>';
                        });
                    }
                    return imgs;
                }
            }, {
                name: 'createTime',
                label: '交付时间',
                width: 190,
                fixed: !0,
                title: !1,
                sortable: !0,
                formatter: function(a, b, c) {
                    var d = a > c.lastDate ? 'yellow' : '';
                    var e = a > c.lastDate ? '交付超时' : '';
                    var f = a > c.lastDate ? '<span class="db fr h30 lh30 pl5 pr5 white bg-yellow b-r-3">交付超时</span>' : '';
                    return '<span class="' + d + '" title="' + e + '">' + a + '</span>' + f;
                }
            }, {
                name: 'upDescription',
                label: '交付备注',
                width: 100,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray'
            }, {
                name: 'weight',
                label: '入库重量<small>kg</small>',
                width: 80,
                fixed: !0,
                sortable: !0,
                formatter: function(cellvalue, options, rowObject) {
                    return Number(cellvalue) == 0 ? '' : cellvalue;
                }
            }, {
                name: 'checkName',
                label: '入库人',
                width: 54,
                fixed: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray'
            }, {
                name: 'checkTime',
                label: '入库时间',
                width: 130,
                fixed: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray',
                formatter: function(cellvalue, options, rowObject) {
                    return ('0000-00-00 00:00:00' == cellvalue || undefined == cellvalue) ? '' : cellvalue;
                }
            }, {
                name: 'id',
                hidden: !0
            }, {
                name: 'checkId',
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
            upQty: data.totalQty,
            weight: data.totalWeight
        });
    }
};
$(function() {
    THISPAGE.init();
});
