var urlParam = Public.urlParam();
var defaultPage = Public.getDefaultPage();
var queryConditions = {
    status1: '',
    beginDate: defaultPage.SYSTEM.beginDate + ' 00:00:00',
    endDate: urlParam.endDate ? (urlParam.endDate + ' 23:59:59') : (Public.reduceDays(defaultPage.SYSTEM.endDate, 1) + ' 11:59:59'),
    skey: '',
    unDone: urlParam.unDone || 0
};
var deliverCompanyCode100List = {};
var statusCombo1 = $('#combo1').combo({
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
            $('#search').trigger('click');
        }
    }
}).getCombo();
var THISPAGE = {
    init: function() {
        THISPAGE.initGrid();
        THISPAGE.initEvent();
    },
    initGrid: function() {
        var a = Public.setGrid();
        $('#grid').jqGrid({
            url: '/Base/DeliverBag/dull_list',
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
                sortable: !0
            }, {
                name: 'company',
                label: '所属快递',
                width: 66,
                fixed: !0,
                sortable: !0
            }, {
                name: 'address',
                label: '发往',
                width: 42,
                fixed: !0,
                sortable: !0
            }, {
                name: 'chType',
                label: '类型',
                width: 42,
                fixed: !0,
                sortable: !0
            }, {
                name: 'sellerNick',
                label: '店铺标识',
                width: 100,
                fixed: !0,
                sortable: !0,
                title: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'lastUpdator',
                label: '打印人',
                width: 54,
                fixed: !0,
                sortable: !0,
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
                label: '联系方式后四位',
                width: 100,
                fixed: !0,
                hidden: !0,
                sortable: !1
            }, {
                name: 'orderNo',
                label: '包含订单',
                width: 66,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'description',
                label: '备注',
                width: 100,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray'
            }, {
                name: 'dullOperHis',
                label: '滞留处理记录',
                width: 100,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: '',
                label: '操作',
                width: 270,
                fixed: !0,
                sortable: !1,
                align: 'center',
                formatter: function(cellvalue, options, rowObject) {
                    return '<div class="search-deliver db fl pl10 pr10 h28 lh28 b-r-3-left hover-white" data-id="' + rowObject.id + '">查轨迹</div>'
                        + '<div class="add-his db fl ml-1 pl10 pr10 h28 lh28 hover-white" data-id="' + rowObject.id + '">追加处理记录</div>'
                        + '<div class="dull-to-normal db fl ml-1 pl10 pr10 h28 lh28 b-r-3-right hover-white" data-id="' + rowObject.id + '" data-no="' + rowObject.no + '">转为非滞留包裹</div>';
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
            queryConditions.status1 = statusCombo1.getValue() == -1 ? '' : statusCombo1.getText();
            queryConditions.skey = $.trim($('#skey').val()).split(/[ ,，]+/).toString();
            $('#grid').jqGrid('setGridParam', {
                postData: queryConditions,
                page: 1
            }).trigger('reloadGrid');
        });
        $('#refresh').on('click', function() {
            window.location.reload();
        });
        $('.grid-wrap').on('click', '.add-his', function(a) {
            a.preventDefault();
            if (Business.verifyRight('SO_ADD')) { //销售部人员有权
                var id = $(this).data('id');
                var rowData = $('#grid').jqGrid('getRowData', id);
                $.dialog({
                    title: '追加滞留处理记录',
                    content: 'url:/Uri/deliver_bag_dull_addhis',
                    data: {
                        rowData: rowData,
                        callback: THISPAGE.callback
                    },
                    width: 1000,
                    height: 360,
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
        $('.grid-wrap').on('click', '.dull-to-normal', function(a) {
            a.preventDefault();
            if (Business.verifyRight('SO_ADD')) { //销售部人员有权
                var id = $(this).data('id');
                var no = $(this).data('no');
                id && $.dialog.confirm('确定转换为非滞留包裹？', function() {
                    Public.ajaxPost('/Base/DeliverBag/dull_to_normal', {
                        id: id,
                        no: no
                    }, function(res) {
                        if (res && 200 == res.status) {
                            $.dialog.tips('转换成功！');
                            $('#grid').jqGrid('delRowData', id);
                        } else {
                            $.dialog.tips('转换失败！' + res.msg, 2);
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
            Public.resizeGrid();
        });
    },
    callback: function(a, b) {
        var e = $('#grid').data('gridData');
        e || (e = {}, $('#grid').data('gridData', e));
        e[a.id] = a;
        $('#grid').jqGrid('setRowData', a.id, a);
    }
};
$(function() {
    THISPAGE.init();
});
