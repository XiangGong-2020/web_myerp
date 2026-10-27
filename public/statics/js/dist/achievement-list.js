var defaultPage = Public.getDefaultPage();
var queryConditions = {
    year: defaultPage.SYSTEM.endDate.slice(0, 3),
    month: defaultPage.SYSTEM.endDate.slice(5, 6)
};
var yearCombo = $('#yearCombo').combo({
    data: [
        {id: -1, name: '选择年份'},
        {id: 0, name: '2025'},
        {id: 1, name: '2026'},
        {id: 2, name: '2027'},
        {id: 3, name: '2028'},
        {id: 4, name: '2029'},
        {id: 5, name: '2030'},
        {id: 6, name: '2031'},
        {id: 7, name: '2032'},
        {id: 8, name: '2033'},
        {id: 9, name: '2034'},
        {id: 10, name: '2035'},
        {id: 11, name: '2036'},
        {id: 12, name: '2037'},
        {id: 13, name: '2038'},
        {id: 14, name: '2039'},
        {id: 15, name: '2040'},
        {id: 16, name: '2041'},
        {id: 17, name: '2042'},
        {id: 18, name: '2043'},
        {id: 19, name: '2044'},
        {id: 20, name: '2045'},
        {id: 21, name: '2046'},
        {id: 22, name: '2047'},
        {id: 23, name: '2048'},
        {id: 24, name: '2049'},
        {id: 25, name: '2050'}
    ],
    value: 'id',
    text: 'name',
    defaultSelected: 0,
    width: 78,
    listHeight: 500
}).getCombo();
var monthCombo = $('#monthCombo').combo({
    data: [
        {id: -1, name: '选择月份'},
        {id: 0, name: '01'},
        {id: 1, name: '02'},
        {id: 2, name: '03'},
        {id: 3, name: '04'},
        {id: 4, name: '05'},
        {id: 5, name: '06'},
        {id: 6, name: '07'},
        {id: 7, name: '08'},
        {id: 8, name: '09'},
        {id: 9, name: '10'},
        {id: 10, name: '11'},
        {id: 11, name: '12'}
    ],
    value: 'id',
    text: 'name',
    defaultSelected: 0,
    width: 78,
    listHeight: 500
}).getCombo();
var THISPAGE = {
    init: function() {
        this.initGrid();
        this.initEvent();
    },
    initGrid: function() {
        var a = Public.setGrid();
        $('#grid').jqGrid({
            url: '/Bill/Achievement/list',
            postData: queryConditions,
            datatype: 'json',
            colModel: [{
                name: 'billNo',
                label: '<span class="ml10">单据编号</span>',
                classes: 'ti10',
                width: 90,
                fixed: !0
            }, {
                name: 'beginDate',
                label: '当月起始日',
                width: 90,
                fixed: !0
            }, {
                name: 'endDate',
                label: '当月结束日',
                width: 90,
                fixed: !0
            }, {
                name: 'description',
                label: '备注',
                width: 100,
                title: !0,
                classes: 'ui-ellipsis gray'
            }, {
                name: 'uName',
                label: '制单人',
                width: 54,
                fixed: !0,
                classes: 'ui-ellipsis gray'
            }, {
                name: 'createTime',
                label: '制单时间',
                width: 130,
                fixed: !0,
                classes: 'ui-ellipsis gray',
                formatter: function(a, b, c) {
                    if (c.isDraft == 1) {
                        return '<span class="db fl pl10 pr10 h28 lh28 hover-gray b-r-3" title="目前为草稿">草稿</span>';
                    }
                    return a;
                }
            }, {
                name: 'export',
                label: '导出',
                width: 54,
                fixed: !0,
                align: 'center',
                formatter: function(a, b, c) {
                    if (c.isDraft == 1) {
                        return '<span class="db h28 lh28 hover-gray b-r-3" title="目前为草稿">草稿</span>';
                    }
                    return '<a class="export-btn db h28 lh28 hover-white b-r-3" data-id="' + c.id + '">导出</a>';
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
            autowidth: !0,
            height: a.h,
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
            loadonce: !0,
            loadComplete: function(data) {
                data.status == -1 && $.dialog.tips(data.msg, 2);
            },
            loadError: function(a, b) {
                $.dialog.tips('Type: ' + b + '; Response: ' + a.status + ' ' + a.statusText, 2);
            },
            ondblClickRow: function(a) {
                $('#' + a).find('.ui-icon-pencil').trigger('click');
            }
        });
    },
    initEvent: function() {
        $('#refresh').on('click', function() {
            $('#grid').jqGrid('setGridParam', {
                url: '/Bill/Achievement/list',
                datatype: 'json'
            }).trigger('reloadGrid');
        });
        $('#createMonthAchievement').on('click', function(a) {
            a.preventDefault();
            if (Business.verifyRight('ACHIEVEMENT_SEARCH')) {
                $('.wrapper').append('<div id="div-lock"><div class="loading">自动生成中...</div></div>');
                queryConditions.year = yearCombo.getValue() == -1 ? '' : yearCombo.getText();
                queryConditions.month = monthCombo.getValue() == -1 ? '' : monthCombo.getText();
                queryConditions.year == '' && $.dialog.tips('请选择年份！', 2);
                queryConditions.month == '' && $.dialog.tips('请选择月份！', 2);
                Public.ajaxPost('/Bill/Achievement/createMonthAchievement', queryConditions, function(res) {
                    $('.wrapper > #div-lock').remove();
                    if (res && 200 == res.status) {
                        parent.$.dialog.tips('生成成功！');
                        $('#refresh').trigger('click');
                    } else {
                        $.dialog.tips('生成失败！' + res.msg, 2);
                    }
                });
            }
        });
        $('#redoMonthAchievement').on('click', function(a) {
            a.preventDefault();
            if (Business.verifyRight('ACHIEVEMENT_SEARCH')) {
                $('.wrapper').append('<div id="div-lock"><div class="loading">自动计算中...</div></div>');
                queryConditions.year = yearCombo.getValue() == -1 ? '' : yearCombo.getText();
                queryConditions.month = monthCombo.getValue() == -1 ? '' : monthCombo.getText();
                queryConditions.year == '' && $.dialog.tips('请选择年份！', 2);
                queryConditions.month == '' && $.dialog.tips('请选择月份！', 2);
                Public.ajaxPost('/Bill/Achievement/redoMonthAchievement', queryConditions, function(res) {
                    $('.wrapper > #div-lock').remove();
                    if (res && 200 == res.status) {
                        $.dialog.tips('重算成功！');
                    } else {
                        $.dialog.tips('重算失败！' + res.msg, 2);
                    }
                });
            }
        });
        $('.grid-wrap').on('click', '.export-btn', function(a) {
            if (Business.verifyRight('ACHIEVEMENT_SEARCH')) {
                var id = $(this).data('id');
                var href = '/Bill/Achievement/export?id=' + id;
                $(this).attr('href', href);
            }
        });
        $('.grid-wrap').on('click', '.ui-icon-pencil', function(a) {
            a.preventDefault();
            if (Business.verifyRight('ACHIEVEMENT_SEARCH')) {
                var id = $(this).parent().data('id');
                id && parent.tab.addTabItem({
                    tabid: 'personnel-achievement' + id,
                    text: '<i class="iconfont icon-danye f14 mr5"></i>工资月表单',
                    url: '/Uri/achievement_manage?flag=list&id=' + id
                });
            }
        });
        $('.grid-wrap').on('click', '.ui-icon-trash', function(a) {
            a.preventDefault();
            if (Business.verifyRight('ACHIEVEMENT_SEARCH')) {
                var id = $(this).parent().data('id');
                id && $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                    Public.ajaxGet('/Bill/Achievement/delete', {
                        id: id
                    }, function(res) {
                        if (res && 200 == res.status) {
                            $.dialog.tips('删除成功！');
                            $('#grid').jqGrid('delRowData', id);
                            parent.frames['personnel-achievement' + id] && parent.tab.removeTabItem('personnel-achievement' + id);
                        } else {
                            $.dialog.tips('删除失败！' + res.msg, 2);
                        }
                    });
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
