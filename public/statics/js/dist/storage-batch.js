var api = frameElement.api;
var urlParam = Public.urlParam();
var defaultPage = Public.getDefaultPage();
var data = api.data || {};
var isSingle = data.isSingle || !1; // 单选模式
var addList = {};
var queryConditions = {
    skey: ''
};
var THISPAGE = {
    init: function() {
        THISPAGE.initGrid();
        THISPAGE.initEvent();
    },
    initGrid: function() {
        $('#grid').jqGrid({
            url: '/Base/Storage/list',
            postData: queryConditions,
            datatype: 'json',
            colModel: [{
                name: 'id',
                label: 'ID',
                width: 52,
                fixed: !0,
                sortable: !0
            }, {
                name: 'name',
                label: '名称',
                width: 200,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'address',
                label: '地址',
                width: 100,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'phone',
                label: '电话',
                width: 200,
                fixed: !0,
                title: !0,
                sortable: !0
            }, {
                name: 'empId',
                label: '应急联系人ID',
                width: 0,
                hidden: !0
            }, {
                name: 'empName',
                label: '应急联系人',
                width: 100,
                fixed: !0,
                title: !0,
                sortable: !0
            }, {
                name: 'type',
                label: '类型',
                width: 46,
                fixed: !0,
                sortable: !0,
                formatter: function(a, b, c) {
                    return 0 == a ? '实物仓' : '虚拟仓';
                },
                title: !1
            }, {
                name: 'allowNeg',
                label: '爆仓',
                width: 46,
                fixed: !0,
                sortable: !0,
                formatter: function(a, b, c) {
                    return 0 == a ? '不允许' : '允许';
                }
            }, {
                name: 'isDisable',
                label: '状态',
                width: 46,
                fixed: !0,
                sortable: !0,
                formatter: function(a, b, c) {
                    var d = a == 0 ? '正常' : '已禁用';
                    var e = a == 0 ? 'green' : 'red';
                    return '<span class="' + e + '" data-isdisable="' + a + '" data-id="' + c.id + '">' + d + '</span>';
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
            queryConditions.skey = $.trim($('#skey').val()).split(/[ ,，]+/).toString();
            $('#grid').jqGrid('setGridParam', {
                postData: queryConditions,
                page: 1
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
