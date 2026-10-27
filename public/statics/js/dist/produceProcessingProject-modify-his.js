var api = frameElement.api;
var apiData = api.data || {};
var defaultPage = Public.getDefaultPage();
var queryConditions = {
    id: api.data.id,
    beginDate: '',
    endDate: '',
    skey: ''
};
var THISPAGE = {
    init: function() {
        THISPAGE.initBtns();
        THISPAGE.initDom();
        THISPAGE.initGrid();
        THISPAGE.initEvent();
    },
    initBtns: function() {
        api.button({
            id: 'cancel',
            name: '关闭'
        });
    },
    initDom: function() {
        $('#beginDate').val(defaultPage.SYSTEM.beginDate + ' 00:00:00');
        $('#endDate').val(defaultPage.SYSTEM.endDate + ' 23:59:59');
    },
    initGrid: function() {
        $('#grid').jqGrid({
            url: '/Base/ProProcessingProject/getModifyHis',
            datatype: 'json',
            postData: queryConditions,
            colModel: [{
                name: 'changeTime',
                label: '<span class="ml10">编辑时间</span>',
                width: 134,
                fixed: !0,
                sortable: !0,
                classes: 'ti10 gray'
            }, {
                name: 'uName',
                label: '编辑人',
                width: 56,
                fixed: !0,
                sortable: !0
            }, {
                name: 'change',
                label: '编辑内容',
                width: 100,
                classes: 'break'
            }, {
                name: 'id',
                label: '',
                width: 0,
                hidden: !0
            }, {
                name: 'proProjectId',
                label: '',
                width: 0,
                hidden: !0
            }, {
                name: 'uId',
                label: '',
                width: 0,
                hidden: !0
            }],
            cmTemplate: {sortable: !1, title: !1},
            jsonReader: {root: 'data.rows', records: 'data.records', total: 'data.total', repeatitems: !1, id: 'id'},
            width: $(window).width(),
            height: $(window).height() - 142,
            altRows: !1,
            gridview: !0,
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
                setTimeout(function() {
                    $("#grid").jqGrid('setGridWidth', $(window).width());
                    $("#grid").jqGrid('setGridHeight', $(window).height() - 142);
                }, 100)
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
        $('#search').on('click', function() {
            queryConditions.beginDate = $.trim($('#beginDate').val());
            queryConditions.endDate = $.trim($('#endDate').val());
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

    }
};
$(function() {
    THISPAGE.init();
});
