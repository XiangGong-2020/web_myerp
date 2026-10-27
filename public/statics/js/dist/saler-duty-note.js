var defaultPage = Public.getDefaultPage();
var queryConditions = {
    user: '',
    beginDate: '',
    endDate: '',
    skey: ''
};
var userCombo = $('#userCombo').combo({
    data: '/Base/User/queryAll?normal=yes&department=销售部',
    ajaxOptions: {
        formatData: function(a) {
            return a.data.rows.unshift({
                id: -1,
                name: '所有相关人员'
            }), a.data.rows.push({
                id: 2,
                name: '叶超'
            }, {
                id: 84,
                name: '杨颐'
            }), a.data.rows;
        }
    },
    value: 'id',
    text: 'name',
    defaultSelected: 0,
    width: 114,
    listHeight: 620,
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
        $('#beginDate').val(defaultPage.SYSTEM.beginDate + ' 00:00:00');
        $('#endDate').val(defaultPage.SYSTEM.endDate + ' 23:59:59');
    },
    initGrid: function() {
        var a = Public.setGrid();
        $('#grid').jqGrid({
            url: '/Base/SalerDuty/noteList',
            postData: queryConditions,
            datatype: 'json',
            colModel: [{
                name: 'dutyDate',
                label: '<span class="ml10">值班日期</span>',
                width: 100,
                fixed: !0,
                classes: 'ti10 gray'
            }, {
                name: 'name',
                label: '值班人',
                width: 60,
                fixed: !0
            }, {
                name: 'note',
                label: '值班记录',
                width: 100,
                title: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'createTime',
                label: '录入时间',
                width: 136,
                fixed: !0,
                classes: 'ti10 gray'
            }, {
                name: 'modifyTime',
                label: '修改时间',
                width: 136,
                fixed: !0,
                classes: 'ti10 gray'
            }],
            cmTemplate: {sortable: !1, title: !1},
            jsonReader: {root: 'data.rows', records: 'data.records', total: 'data.total', repeatitems: !1, id: 'id'},
            width: a.w,
            height: a.h,
            altRows: !1,
            gridview: !0,
            rownumbers: !1,
            shrinkToFit: !0,
            forceFit: !0,
            sortname: 'id',
            sortorder: 'DESC',
            pager: '#page',
            page: 1,
            rowNum: 100,
            rowList: [100, 500, 1000],
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
                }, 300)
            }
        });
        $('#search').on('click', function(a) {
            a.preventDefault();
            queryConditions.user = userCombo.getValue() == -1 ? '' : userCombo.getText();
            queryConditions.beginDate = $('#beginDate').val();
            queryConditions.endDate = $('#endDate').val();
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
                $('#beginDate, #endDate').blur();
                $('#search').trigger('click');
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
