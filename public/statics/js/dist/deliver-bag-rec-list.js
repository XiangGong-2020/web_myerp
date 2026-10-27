var defaultPage = Public.getDefaultPage();
var queryConditions = {
    company1: '',
    beginDate: '',
    endDate: '',
    skey: ''
};
var deliverCompanyCode100List = {};
var combo1 = $('#combo1').combo({
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
            url: '/Base/DeliverBagRec/list',
            postData: queryConditions,
            datatype: 'json',
            colModel: [{
                name: 'id',
                label: 'ID',
                hidden: !0
            }, {
                name: 'no',
                label: '<span class="ml10">快递单号</span>',
                width: 150,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ti10'
            }, {
                name: 'company1',
                label: '常见快递公司',
                width: 100,
                fixed: !0,
                title: !0,
                sortable: !0
            }, {
                name: 'company2',
                label: '其他快递公司',
                width: 100,
                fixed: !0,
                title: !0,
                sortable: !0
            }, {
                name: 'boxNum',
                label: '总箱数',
                width: 100,
                fixed: !0,
                title: !0,
                sortable: !0
            }, {
                name: 'boxWeight',
                label: '总重量',
                width: 100,
                fixed: !0,
                title: !0,
                sortable: !0
            }, {
                name: 'destroy',
                label: '破损情况',
                width: 100,
                fixed: !0,
                title: !0,
                sortable: !0
            }, {
                name: 'description',
                label: '备注',
                width: 100,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray'
            }, {
                name: 'uId',
                label: 'uId',
                hidden: !0
            }, {
                name: 'uName',
                label: '接收人',
                width: 54,
                fixed: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray'
            }, {
                name: 'createTime',
                label: '接收时间',
                width: 130,
                fixed: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray',
                formatter: function(cellvalue, options, rowObject) {
                    return ('0000-00-00 00:00:00' == cellvalue || undefined == cellvalue) ? '' : cellvalue;
                }
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
                }, 300)
            }
        });
        $('#search').on('click', function(a) {
            a.preventDefault();
            queryConditions.company1 = combo1.getValue() == -1 ? '' : combo1.getText();
            queryConditions.beginDate = $('#beginDate').val() ? $.trim($('#beginDate').val()) : '';
            queryConditions.endDate = $('#endDate').val() ? $.trim($('#endDate').val()) : '';
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
        $(window).on('keydown', function(a) {
            var curKey = a.which;
            if (curKey == 13) {
                $('#beginDate, #endDate').blur();
                $('#search').parent().find('.current').trigger('click');
            }
        });
        $('.grid-wrap').on('click', '.search-deliver', function(a) {
            a.preventDefault();
            var id = $(this).data('id');
            var rowData = $('#grid').jqGrid('getRowData', id);
            if (rowData.company1 == '京东快递') {
                window.open('https://www.jdl.com/orderSearch/?waybillCodes=' + rowData.no);
            } else {
                $.dialog({
                    title: '快递查询',
                    content: 'url:/Uri/deliver_search',
                    data: {
                        company: rowData.company1,
                        type: deliverCompanyCode100List[rowData.company1],
                        postid: rowData.no,
                        phone: ''
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
        $(window).on('resize', function() {
            Public.resizeGrid();
        });
    }
};
$(function() {
    THISPAGE.init();
});
