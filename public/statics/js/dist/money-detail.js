var defaultPage = Public.getDefaultPage();
var queryConditions = {
    transType: -1,
    minAmount: '',
    maxAmount: '',
    beginDate: (new Date).format('yyyy-MM-dd') + ' 00:00:00',
    endDate: (new Date).format('yyyy-MM-dd') + ' 23:59:59',
    skey: ''
};
var combo1 = $('#combo1').combo({
    data: [
        {id: -1, name: '所有单据类别'},
        {id: 1, name: '所有收入类'},
        {id: 2, name: '所有支出类'},
        {id: 103101, name: '业务收款类'},
        {id: 103102, name: '业务付款类'},
        {id: 103103, name: '其他收入类'},
        {id: 103104, name: '其他支出类'}
    ],
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
Public.ajaxGet('/Base/Category/list?typeNumber=incometype,expendtype', {}, function(data) {
    if (data.status === 200) {
        var incometype = expendtype = '';
        $.each(data.data.rows, function(i, v) {
            if (v.typeNumber == 'incometype') {
                incometype += '<option value="' + v.id + '">' + v.name + '</option>';
            } else {
                expendtype += '<option value="' + v.id + '">' + v.name + '</option>';
            }
        });
        $('#incometype').html(incometype);
        $('#expendtype').html(expendtype);
        $('#combo2').fSelect({
            placeholder: '选择收支类别',
            showSearch: !0,
            listHeight: 490
        });
    } else {
        $.dialog.tips(data.msg, 2);
    }
});
var combo3 = Business.companyAccountCombo($('#combo3'), {
    ajaxOptions: {
        formatData: function(a) {
            return a.data.rows.unshift({
                id: -1,
                name: '所有收支账户'
            }), a.data.rows;
        }
    },
    defaultSelected: 0,
    width: 138,
    listHeight: 500,
    trigger: !0,
    callback: {
        onListClick: function() {
            $('#search').trigger('click');
        }
    }
});
var combo4 = Business.categoryCombo($('#combo4'), {
    ajaxOptions: {
        formatData: function(a) {
            return a.data.rows.unshift({
                id: -1,
                name: '所有收支方式'
            }), a.data.rows;
        }
    },
    defaultSelected: 0,
    width: 102,
    listHeight: 500,
    callback: {
        onListClick: function() {
            $('#search').trigger('click');
        }
    },
}, 'settlementtype');
var combo5 = $('#combo5').combo({
    data: '/Base/User/queryAll?normal=yes',
    ajaxOptions: {
        formatData: function(a) {
            return a.data.rows.unshift({
                id: -1,
                name: '所有收支人'
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
var THISPAGE = {
    init: function() {
        THISPAGE.initDom();
        THISPAGE.initGrid();
        THISPAGE.initEvent();
    },
    initDom: function() {
        $('#beginDate').val((new Date).format('yyyy-MM-dd') + ' 00:00:00');
        $('#endDate').val((new Date).format('yyyy-MM-dd') + ' 23:59:59');
    },
    initGrid: function() {
        var a = Public.setGrid();
        $('#grid').jqGrid({
            url: '/Bill/Report/moneyDetail',
            postData: queryConditions,
            datatype: 'json',
            colModel: [{
                name: 'billNo',
                label: '<span class="ml10">单据编号</span>',
                width: 160,
                fixed: !0,
                sortable: !0,
                classes: 'ti10'
            }, {
                name: 'billDate',
                label: '单据日期',
                width: 78,
                fixed: !0,
                sortable: !0
            }, {
                name: 'miniTypeName',
                label: '单据类别',
                width: 78,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis',
                formatter: function(a, b, c) {
                    var miniTypeName = '';
                    if (c.transType == 103101) {
                        if (c.miniType == 1) {
                            miniTypeName = '销售收款单';
                        } else if (c.miniType == 2) {
                            miniTypeName = '采购退款单';
                        } else if (c.miniType == 3) {
                            miniTypeName = '特采退款单';
                        } else if (c.miniType == 4) {
                            miniTypeName = '快递退款单';
                        }
                    } else if (c.transType == 103102) {
                        if (c.miniType == 1) {
                            miniTypeName = '销售退款单';
                        } else if (c.miniType == 2) {
                            miniTypeName = '采购付款单';
                        } else if (c.miniType == 3) {
                            miniTypeName = '特采付款单';
                        } else if (c.miniType == 4) {
                            miniTypeName = '快递付款单';
                        }
                    } else if (c.transType == 103103) {
                        miniTypeName = '其他收入单';
                    } else if (c.transType == 103104) {
                        miniTypeName = '其他支出单';
                    }
                    return miniTypeName;
                }
            }, {
                name: 'amount',
                label: '收支金额<small class="ml5">元</small>',
                width: 100,
                fixed: !0,
                sortable: !0,
                cellattr: function(rowId, val, rawObject, cm, rowdata) {
                    var type = '';
                    switch (rawObject.transType) {
                        case 103101:
                            type = '+';
                            break;
                        case 103102:
                            type = '-';
                            break;
                        case 103103:
                            type = '+';
                            break;
                        case 103104:
                            type = '-';
                            break;
                        default:
                            break;
                    }
                    if (type == '+') {
                        return 'class="f14 teal"';
                    } else {
                        return 'class="f14 yellow"';
                    }
                },
                formatter: function(val, opt, row) {
                    var type = '';
                    switch (row.transType) {
                        case 103101:
                            type = '+';
                            break;
                        case 103102:
                            type = '-';
                            break;
                        case 103103:
                            type = '+';
                            break;
                        case 103104:
                            type = '-';
                            break;
                        default:
                            break;
                    }
                    return type + ' ' + val;
                }
            }, {
                name: 'typeName',
                label: '收支类别',
                width: 120,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'accName',
                label: '收支账户',
                width: 120,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'wayName',
                label: '收支方式',
                width: 66,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'settlementTime',
                label: '收支时间',
                width: 130,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'uName',
                label: '收支人',
                width: 60,
                fixed: !0,
                sortable: !0
            }, {
                name: 'contactName',
                label: '收支对象',
                width: 200,
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
                name: 'moneyType',
                label: '标签',
                width: 100,
                fixed: !0,
                title: !0,
                sortable: !0,
                formatter: function(a, b, c) {
                    var tag = '';
                    if (c.transType == 103101) {
                        if (a == 1) {
                            tag = '<span class="db tc lh18 white bg-rgba3 b-r-3 def">预退款转实退款</span>';
                        } else if (a == 2) {
                            tag = '<span class="db tc lh18 white bg-aqua b-r-3 def">返现退款</span>';
                        } else if (a == 9) {
                            tag = '<span class="db tc lh18 white bg-red b-r-3 def">预退款</span>';
                        }
                    } else if (c.transType == 103102) {
                        if (a == 1) {
                            tag = '<span class="db tc lh18 white bg-gold b-r-3 def">额外付款</span>';
                        }
                    }
                    return tag;
                }
            }, {
                name: 'id',
                label: 'ID',
                hidden: !0
            }, {
                name: 'transType',
                label: '单据类别',
                hidden: !0
            }, {
                name: 'miniType',
                label: '细分类别',
                hidden: !0
            }],
            cmTemplate: {sortable: !1, title: !1},
            jsonReader: {root: 'data.rows', records: 'data.records', total: 'data.total', repeatitems: !1, id: 'id'},
            width: a.w,
            height: a.h,
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
            loadComplete: function(data) {
                data.status == -1 && $.dialog.tips(data.msg, 2);
                if (data && 200 == data.status) {
                    $('#totalInAmount').html(Public.numToCurrency((data.data.totalInAmount || 0), 2));
                    $('#totalOutAmount').html(Public.numToCurrency((data.data.totalOutAmount || 0), 2));
                    $('#totalReAmount').html(Public.numToCurrency((Number(data.data.totalInAmount || 0) - Number(data.data.totalOutAmount || 0)), 2));
                }
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
                }, 300);
            }
        });
        $('#search').on('click', function(a) {
            a.preventDefault();
            queryConditions.transType = combo1 ? combo1.getValue() : -1;
            queryConditions.typeId = $('#combo2').val() ? $('#combo2').val().join(',') : '';
            queryConditions.accId = combo3 ? combo3.getValue() : -1;
            queryConditions.wayId = combo4 ? combo4.getValue() : -1;
            queryConditions.uId = combo5 ? combo5.getValue() : -1;
            queryConditions.minAmount = $('#minAmount').val();
            queryConditions.maxAmount = $('#maxAmount').val();
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
            }, 300);
        });
        $('#today').on('click', function() {
            $(this).addClass('current').siblings().removeClass('current');
            $('#beginDate').val((new Date).format('yyyy-MM-dd') + ' 00:00:00');
            $('#endDate').val((new Date).format('yyyy-MM-dd') + ' 23:59:59');
            $('#search').trigger('click');
        });
        $('#yesterday').on('click', function() {
            $(this).addClass('current').siblings().removeClass('current');
            $('#beginDate').val(Public.reduceDays((new Date).format('yyyy-MM-dd'), 1) + ' 00:00:00');
            $('#endDate').val(Public.reduceDays((new Date).format('yyyy-MM-dd'), 1) + ' 23:59:59');
            $('#search').trigger('click');
        });
        $('#curMonth').on('click', function() {
            $(this).addClass('current').siblings().removeClass('current');
            $('#beginDate').val((new Date).format('yyyy-MM-01') + ' 00:00:00');
            $('#endDate').val(Public.reduceDays(Public.addMonths((new Date).format('yyyy-MM-01'), 1), 1) + ' 23:59:59');
            $('#search').trigger('click');
        });
        $('#lastMonth').on('click', function() {
            $(this).addClass('current').siblings().removeClass('current');
            $('#beginDate').val(Public.reduceMonths((new Date).format('yyyy-MM-01'), 1) + ' 00:00:00');
            $('#endDate').val(Public.reduceDays((new Date).format('yyyy-MM-01'), 1) + ' 23:59:59');
            $('#search').trigger('click');
        });
        $('#curYear').on('click', function() {
            $(this).addClass('current').siblings().removeClass('current');
            $('#beginDate').val((new Date).format('yyyy-01-01') + ' 00:00:00');
            $('#endDate').val((new Date).format('yyyy-MM-dd') + ' 23:59:59');
            $('#search').trigger('click');
        });
        $('#lastYear').on('click', function() {
            $(this).addClass('current').siblings().removeClass('current');
            $('#beginDate').val(Public.reduceMonths((new Date).format('yyyy-01-01'), 12) + ' 00:00:00');
            $('#endDate').val(Public.reduceMonths((new Date).format('yyyy-12-31'), 12) + ' 23:59:59');
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
        $(window).on('resize', function() {
            Public.resizeGrid();
        });
    }
};
$(function() {
    THISPAGE.init();
});
