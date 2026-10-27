var api = frameElement.api;
var urlParam = Public.urlParam();
var defaultPage = Public.getDefaultPage();
var data = api.data || {};
var skey = data.skey || ''; // 缓存的关键词
var isSingle = data.isSingle || !1; // 单选模式
var queryConditions = {
    type: -1,
    isDisable: -1,
    skey: '',
    searchType: 0
};
var typeCombo = $('#typeCombo').combo({
    data: [
        {id: -1, name: '所有类型'},
        {id: 0, name: '开发板整体生产类'},
        {id: 1, name: '电路组件焊接类'},
        {id: 2, name: '包装组装类'},
        {id: 9, name: '临时加工类（无库存运算）'}
    ],
    value: 'id',
    text: 'name',
    width: 102,
    listHeight: 300,
    defaultSelected: 0,
    callback: {
        onListClick: function() {
            $('#search').trigger('click');
        }
    }
}).getCombo();
var isDisableCombo = $('#isDisableCombo').combo({
    data: [
        {id: -1, name: '所有状态'},
        {id: 0, name: '正常'},
        {id: 1, name: '将停产'},
        {id: 2, name: '已停产'},
        {id: 9, name: '已禁用'}
    ],
    value: 'id',
    text: 'name',
    width: 78,
    listHeight: 300,
    defaultSelected: 0,
    callback: {
        onListClick: function() {
            $('#search').trigger('click');
        }
    }
}).getCombo();
var searchTypeCombo = $('#searchTypeCombo').combo({
    data: [
        {id: 0, name: '模糊查询'},
        {id: 1, name: '精确查询'}
    ],
    value: 'id',
    text: 'name',
    defaultSelected: 0,
    width: 78,
    callback: {
        onListClick: function() {
            $('#search').trigger('click');
        }
    }
}).getCombo();
var addList = {};
var THISPAGE = {
    init: function() {
        THISPAGE.initDom();
        THISPAGE.initGrid();
        THISPAGE.initEvent();
    },
    initDom: function() {
        skey && $('#skey').val(skey);
    },
    initGrid: function() {
        $('#grid').jqGrid({
            url: '/Base/ProProcessingProject/list',
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
                name: 'type',
                label: '项目类型',
                width: 80,
                fixed: !0,
                sortable: !0,
                formatter: function(a, b, c) {
                    return a == 0 ? '开发板整体生产类' : (a == 1 ? '电路组件焊接类' : (a == 2 ? '包装组装类' : '其他加工类（无库存运算）'));
                }
            }, {
                name: 'name',
                label: '项目名称',
                width: 500,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'goodsId',
                label: '产品ID',
                width: 0,
                hidden: !0
            }, {
                name: 'tPointNum',
                label: '贴片焊点数',
                width: 78,
                fixed: !0,
                sortable: !0,
                formatter: function(a, b, c) {
                    return a > 0 ? a : '';
                }
            }, {
                name: 'zPointNum',
                label: '直插焊点数',
                width: 78,
                fixed: !0,
                sortable: !0,
                formatter: function(a, b, c) {
                    return a > 0 ? a : '';
                }
            }, {
                name: 'donePerHourNum',
                label: '<span class="fl mt10 lh14">每小时可</span><span class="fl lh14">完成数</spam>',
                width: 60,
                fixed: !0,
                sortable: !0,
                formatter: function(a, b, c) {
                    return a > 0 ? a : '';
                }
            }, {
                name: 'needTest',
                label: '测试工序',
                width: 66,
                fixed: !0,
                sortable: !0,
                formatter: function (a, b, c) {
                    return a == 0 ? '<span class="gray">无</span>' : '有';
                }
            }, {
                name: 'description',
                label: '备注',
                width: 175,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'isDisable',
                label: '状态',
                width: 54,
                fixed: !0,
                sortable: !0,
                formatter: function(a, b, c) {
                    var d = '';
                    switch (c.isDisable) {
                        case 0:
                            d = '<span class="gray">正常</span>';
                            break;
                        case 1:
                            d = '<span class="red">将停产</span>';
                            break;
                        case 2:
                            d = '<span class="red">已停产</span>';
                            break;
                        case 9:
                            d = '<span class="red">已禁用</span>';
                            break;
                        default:
                            break;
                    }
                    return d;
                }
            }],
            cmTemplate: {sortable: !1, title: !1},
            jsonReader: {root: 'data.rows', records: 'data.records', total: 'data.total', repeatitems: !1, id: 'id'},
            width: 1250,
            height: 358,
            altRows: !1,
            gridview: !0,
            multiselect: !0,
            multiboxonly: isSingle ? !0 : !1,
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
                if (data && 200 == data.status) {
                    for (var i = 0; i < data.data.rows.length; i++) {
                        if (data.data.rows[i].isDisable == 9) { // 已禁用 添加禁止选择属性
                            $('#' + data.data.rows[i].id).addClass('ui-state-disabled');
                            $('#' + data.data.rows[i].id + ' input').attr({disabled: 'disabled'});
                        }
                    }
                } else {
                    data.status == -1 && $.dialog.tips(data.msg, 2);
                }
            },
            loadError: function(a, b) {
                $.dialog.tips('Type: ' + b + '; Response: ' + a.status + ' ' + a.statusText, 2);
            },
            gridComplete: function() {
                /* 翻页后，重新为选中的行设置选中状态，但不触发行选中事件 */
                for (_item in addList) {
                    $('#grid').jqGrid('setSelection', _item, !1);
                }
            },
            beforeSelectRow: function(a) {
                if (isSingle) { // 单选模式
                    $("#grid").jqGrid('resetSelection');
                }
                // 增加禁止选择效果 或 异常提醒
                var curRowData = $('#grid').jqGrid('getRowData', a);
                if (curRowData.isDisable == 9) {
                    $.dialog.alert('该项目已禁用，禁止选择！');
                    return !1;
                } else if (curRowData.isDisable == 1) {
                    $.dialog.alert('该项目将停产，请慎重选择！');
                    return !0;
                } else if (curRowData.isDisable == 2) {
                    $.dialog.alert('该项目已停产，请慎重选择！');
                    return !0;
                } else {
                    return !0;
                }
            },
            onSelectRow: function(a, b) {
                if (b) {
                    var curRowData = $('#grid').jqGrid('getRowData', a);
                    if (isSingle) { // 单选模式
                        addList = {};
                        addList[a] = curRowData;
                    } else {
                        addList[a] = curRowData;
                    }
                } else {
                    if (isSingle) { // 单选模式
                        addList = {};
                        $("#grid").jqGrid('resetSelection');
                    } else {
                        addList[a] && delete addList[a];
                    }
                }
                $('#page_left').html('<div class="fl ml10 db black">已选择<span class="ml5 mr5 pl5 pr5 white bg-green b-r-3">' + Object.keys(addList).length + '</span>行</siv>');
            },
            onSelectAll: function(a, b) {
                if (isSingle) { // 单选模式
                    addList = {};
                    $("#grid").jqGrid('resetSelection');
                } else {
                    var notNormalNum = 0;
                    for (var c = 0, d = a.length; d > c; c++) {
                        var e = a[c];
                        if (b) {
                            var f = $('#grid').jqGrid('getRowData', e);
                            if (0 < f.isDisable) {
                                notNormalNum++;
                            }
                            addList[e] = f;
                        } else {
                            addList[e] && delete addList[e];
                        }
                    }
                    if (notNormalNum > 0) {
                        $.dialog.alert('全选当中有 ' + notNormalNum + ' 个（已/将）停产项目，请慎重选择！');
                    }
                }
                $('#page_left').html('<div class="fl ml10 db black">已选择<span class="ml5 mr5 pl5 pr5 white bg-green b-r-3">' + Object.keys(addList).length + '</span>行</siv>');
            }
        });
    },
    initEvent: function() {
        $('#search').on('click', function() {
            parent.THISPAGE.skey = $.trim($('#skey').val()); // 缓存的查询关键词
            queryConditions.type = typeCombo.getValue();
            queryConditions.isDisable = isDisableCombo.getValue();
            queryConditions.searchType = searchTypeCombo.getValue();
            queryConditions.skey = $.trim($('#skey').val()).split(/[ ,，]+/).toString();
            $('#grid').jqGrid('setGridParam', {
                page: 1,
                postData: queryConditions
            }).trigger('reloadGrid');
        });
        $('#refresh').on('click', function() {
            window.location.reload();
        });
        $(window).on('keydown', function(a) {
            var curKey = a.which;
            if (curKey == 13) {
                $('#search').trigger('click');
            }
        });
    }
};
$(function() {
    THISPAGE.init();
});
