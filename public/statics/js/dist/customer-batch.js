var api = frameElement.api;
var urlParam = Public.urlParam();
var defaultPage = Public.getDefaultPage();
var data = api.data || {};
var isSingle = data.isSingle || !1; // 单选模式
var queryConditions = {
    categoryId: -1,
    disableType: 0,
    skey: '',
    blackList: 0
};
var categoryCombo = Business.categoryCombo($('#category'), {
    addOptions: {
        value: -1,
        text: '所有客户类别'
    },
    defaultSelected: 0,
    width: 250,
    listHeight: 470,
    callback: {
        onListClick: function() {
            $('#search').trigger('click');
        }
    }
}, 'customertype');
var disableTypeCombo = $('#disabletype').combo({
    data: [
        {id: -1, name: '所有状态'},
        {id: 0, name: '已启用'},
        {id: 1, name: '已禁用'}
    ],
    value: 'id',
    text: 'name',
    defaultSelected: 1,
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
        THISPAGE.initGrid();
        THISPAGE.initEvent();
    },
    initGrid: function() {
        $('#grid').jqGrid({
            url: '/Base/Contact/list?type=0',
            datatype: 'json',
            postData: queryConditions,
            colModel: [{
                name: 'id',
                label: 'ID',
                width: 50,
                fixed: !0,
                sortable: !0,
                classes: 'gray'
            }, {
                name: 'name',
                label: '名称',
                width: 200,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'contacter',
                label: '联系人',
                width: 60,
                fixed: !0,
                title: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'wangwang',
                label: '旺旺',
                width: 120,
                fixed: !0,
                title: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'weixin',
                label: '微信',
                width: 120,
                fixed: !0,
                title: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'im',
                label: 'QQ',
                width: 100,
                fixed: !0,
                title: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'mobile',
                label: '手机',
                width: 100,
                fixed: !0,
                title: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'phone',
                label: '座机',
                width: 100,
                fixed: !0,
                title: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'cLevel',
                label: 'cLevel',
                hidden: !0
            }, {
                name: 'linkMen',
                label: 'linkMen',
                hidden: !0
            }, {
                name: 'deliveryAddress',
                label: '联系地址',
                width: 200,
                title: !0,
                classes: 'ui-ellipsis',
                formatter: function(a, b, c) {
                    return (c.province || '') + (c.city || '') + (c.county || '') + (a || '');
                }
            }, {
                name: 'cLevel',
                label: '等级',
                width: 42,
                fixed: !0,
                sortable: !0,
                align: 'center',
                formatter: function(a, b, c) {
                    switch (a) {
                        case 0:
                            return '<span class="green">零售</span>';
                        case 1:
                            return '<span class="blue">批发</span>';
                        case 2:
                            return '<span class="gray">大宗</span>';
                        case 3:
                            return '<span class="yellow">分销</span>';
                        case 4:
                            return '<span class="red">战略</span>';
                        default:
                            break;
                    }
                }
            }, {
                name: 'winRate',
                label: '历史中标率',
                width: 140,
                fixed: !0,
                sortable: !0,
                formatter: function(a, b, c) {
                    var d = a == -1 ? '<span class="gray">没投过</span>' : ('<div class="winRate"><div class="winRate-bar bg-aqua" style="width:' + (a <= 0 ? 0 : a) + '%"></div><div class="winRate-text"><span class="db fl ml10 w30">' + a + '%</span><span class="fr mr10">' + c.winInfo + '</span></div></div>');
                    return d;
                }
            }, {
                name: 'isDisable',
                label: '状态',
                width: 0,
                hidden: !0
            }, {
                name: 'disableStatus',
                label: '状态',
                width: 50,
                fixed: !0,
                formatter: function(a, b, c) {
                    var d = '';
                    switch (c.isDisable) {
                        case 0:
                            d = '<span class="green">已启用</span>';
                            break;
                        case 1:
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
            width: 1248,
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
                        if (data.data.rows[i].isDisable == 1) { // 已禁用 添加禁止选择属性
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
                return !0;
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
                    for (var c = 0, d = a.length; d > c; c++) {
                        var e = a[c];
                        if (b) {
                            var f = $('#grid').jqGrid('getRowData', e);
                            addList[e] = f;
                        } else {
                            addList[e] && delete addList[e];
                        }
                    }
                }
                $('#page_left').html('<div class="fl ml10 db black">已选择<span class="ml5 mr5 pl5 pr5 white bg-green b-r-3">' + Object.keys(addList).length + '</span>行</siv>');
            }
        });
    },
    initEvent: function() {
        $('#search').on('click', function() {
            queryConditions.categoryId = categoryCombo.getValue();
            queryConditions.disableType = disableTypeCombo.getValue();
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
            if (Business.verifyRight('CUSTOMER_ADD')) {
                parent.$.dialog({
                    title: '新增客户',
                    content: 'url:/Uri/customer_manage',
                    data: {
                        oper: 'add',
                        callback: function(data, oper, dialogWin) {
                            var b = $('#grid').data('gridData');
                            b || (b = {}, $('#grid').data('gridData', b));
                            $('#grid').jqGrid('addRowData', data.id, data, 'first');
                            dialogWin && dialogWin.api.close();
                        }
                    },
                    width: 1250,
                    height: 500,
                    max: !1,
                    min: !1,
                    cache: !1,
                    lock: !1
                });
            };
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
