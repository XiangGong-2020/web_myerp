var api = frameElement.api;
var urlParam = Public.urlParam();
var defaultPage = Public.getDefaultPage();
var data = api.data || {};
var isSingle = data.isSingle || !1; // 单选模式
var queryConditions = {
    province: '',
    siteType: -1,
    skey: ''
};
var addList = {};
var provinceCombo = $('#provinceCombo').combo({
    data: [
        {id: -1, name: '所有省份'},
        {id: 0, name: '北京'},
        {id: 1, name: '天津'},
        {id: 2, name: '河北省'},
        {id: 3, name: '山西省'},
        {id: 4, name: '内蒙古自治区'},
        {id: 5, name: '辽宁省'},
        {id: 6, name: '吉林省'},
        {id: 7, name: '黑龙江省'},
        {id: 8, name: '上海'},
        {id: 9, name: '江苏省'},
        {id: 10, name: '浙江省'},
        {id: 11, name: '安徽省'},
        {id: 12, name: '福建省'},
        {id: 13, name: '江西省'},
        {id: 14, name: '山东省'},
        {id: 15, name: '河南省'},
        {id: 16, name: '湖北省'},
        {id: 17, name: '湖南省'},
        {id: 18, name: '广东省'},
        {id: 19, name: '广西壮族自治区'},
        {id: 20, name: '海南省'},
        {id: 21, name: '重庆'},
        {id: 22, name: '四川省'},
        {id: 23, name: '贵州省'},
        {id: 24, name: '云南省'},
        {id: 25, name: '西藏自治区'},
        {id: 26, name: '陕西省'},
        {id: 27, name: '甘肃省'},
        {id: 28, name: '青海省'},
        {id: 29, name: '宁夏回族自治区'},
        {id: 30, name: '新疆维吾尔自治区'},
        {id: 31, name: '台湾省'},
        {id: 32, name: '香港特别行政区'},
        {id: 33, name: '澳门特别行政区'},
        {id: 34, name: '海外'}
    ],
    value: 'id',
    text: 'name',
    defaultSelected: 0,
    width: 126,
    listHeight: 400,
    callback: {
        onListClick: function() {
            $('#search').trigger('click');
        }
    }
}).getCombo();
var siteTypeCombo = $('#siteTypeCombo').combo({
    data: [
        {id: -1, name: '所有类型'},
        {id: 0, name: '招标平台'},
        {id: 1, name: '招标代理'},
        {id: 2, name: '政府主办'},
        {id: 3, name: '招标企业'},
        {id: 4, name: '学校官网'},
        {id: 5, name: '公众号'}
    ],
    value: 'id',
    text: 'name',
    defaultSelected: 0,
    width: 78,
    listHeight: 400,
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
        $('#grid').jqGrid({
            url: '/Base/Bid/getSites',
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
                name: 'province',
                label: '省份',
                width: 124,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'name',
                label: '网站名称',
                width: 300,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'url',
                label: '网站链接',
                width: 100,
                title: !0,
                classes: 'ui-ellipsis'
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
                data.status == -1 && $.dialog.tips(data.msg, 2);
            },
            loadError: function(a, b) {
                $.dialog.tips('Type: ' + b + '; Response: ' + a.status + ' ' + a.statusText, 2);
            },
            gridComplete: function() {
                /* 翻页后，重新为选中的行设置选中状态，但不触发行选中事件 */
                for (item in addList) {
                    $('#grid').jqGrid('setSelection', item, !1);
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
            queryConditions.province = provinceCombo.getValue() == -1 ? '' : provinceCombo.getText();
            queryConditions.siteType = siteTypeCombo.getValue();
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
