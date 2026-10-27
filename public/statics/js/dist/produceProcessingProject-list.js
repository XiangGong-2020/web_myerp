var urlParam = Public.urlParam();
var defaultPage = Public.getDefaultPage();
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
    width: 160,
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
var THISPAGE = {
    init: function() {
        THISPAGE.initDom();
        THISPAGE.initGrid();
        THISPAGE.initEvent();
    },
    initDom: function() {
    },
    initGrid: function() {
        var a = Public.setGrid();
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
                width: 114,
                fixed: !0,
                sortable: !0,
                formatter: function(a, b, c) {
                    return a == 0 ? '开发板整体生产类' : (a == 1 ? '电路组件焊接类' : (a == 2 ? '包装组装类' : '其他加工类'));
                }
            }, {
                name: 'name',
                label: '项目名称',
                width: 200,
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
                name: 'producePrice',
                label: '加工提成',
                width: 78,
                fixed: !0,
                hidden: $.inArray('SYS_ADVANCE', defaultPage.SYSTEM.rights) === -1 ? !0 : !1,
                sortable: !0
            }, {
                name: 'description',
                label: '备注',
                width: 100,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'uName',
                label: '最初创建',
                width: 66,
                fixed: !0,
                sortable: !0
            }, {
                name: 'createTime',
                label: '创建时间',
                width: 130,
                fixed: !0,
                sortable: !0,
                classes: 'gray'
            }, {
                name: 'modName',
                label: '最后修改',
                width: 66,
                fixed: !0,
                sortable: !0
            }, {
                name: 'modifyTime',
                label: '修改时间',
                width: 130,
                fixed: !0,
                sortable: !0,
                classes: 'gray'
            }, {
                name: 'proName',
                label: '最近加工人',
                width: 78,
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
            }, {
                name: 'operate',
                label: '操作',
                width: 72.8,
                fixed: !0,
                formatter: Public.operFmatter
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
            },
            ondblClickRow: function(c) {
                $('#' + c).find('.ui-icon-pencil').trigger('click');
            }
        });
    },
    initEvent: function() {
        $('#search').on('click', function(a) {
            a.preventDefault();
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
        $('#add').on('click', function(a) {
            a.preventDefault();
            if (Business.verifyRight('PROPROJECT_ADVANCE')) {
                $.dialog({
                    title: '新增加工项目',
                    content: 'url:/Uri/produceProcessingProject_manage',
                    data: {
                        oper: 'add',
                        callback: THISPAGE.callback
                    },
                    width: 1250,
                    height: 600,
                    max: !1,
                    min: !1,
                    cache: !1,
                    lock: !0
                });
            }
        });
        $('#grid').on('click', '.ui-icon-pencil', function(a) {
            a.preventDefault();
            if (Business.verifyRight('PROPROJECT_ADVANCE')) {
                var id = $(this).parent().data('id');
                $.dialog({
                    title: '编辑加工项目',
                    content: 'url:/Uri/produceProcessingProject_manage',
                    data: {
                        oper: 'edit',
                        rowId: id,
                        callback: THISPAGE.callback
                    },
                    width: 1250,
                    height: 600,
                    max: !1,
                    min: !1,
                    cache: !1,
                    lock: !0
                });
            }
        });
        $('#grid').on('click', '.ui-icon-trash', function(a) {
            a.preventDefault();
            if (Business.verifyRight('PROPROJECT_ADVANCE')) {
                var id = $(this).parent().data('id');
                id && $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                    Public.ajaxPost('/Base/ProProcessingProject/delete', {
                        id: id
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
                $('#search').trigger('click');
            }
        });
        $(window).on('resize', function() {
            Public.autoGridWidth($('#grid'));
        });
    },
    callback: function(a, b, c) {
        $('#search').trigger('click');
        c.api.close();
    }
};
$(function() {
    THISPAGE.init();
});
