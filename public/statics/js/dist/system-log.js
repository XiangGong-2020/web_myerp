var defaultPage = Public.getDefaultPage();
var is_user_log = window.location.pathname == '/Uri/user_log' ? !0 : !1;
var queryConditions = {
    user: is_user_log ? defaultPage.SYSTEM.uName : '',
    beginDate: '',
    endDate: '',
    skey: ''
};
var userCombo;
var THISPAGE = {
    init: function() {
        THISPAGE.initDom();
        THISPAGE.initGrid();
        THISPAGE.initEvent();
    },
    initDom: function() {
        $('#beginDate').val(defaultPage.SYSTEM.beginDate + ' 00:00:00');
        $('#endDate').val(defaultPage.SYSTEM.endDate + ' 23:59:59');
        if (!is_user_log) {
            $('#userCombo').parent().removeClass('dn');
            $('#delete-old').removeClass('dn');
            userCombo = $('#userCombo').combo({
                data: '/Base/User/queryAll?normal=yes',
                ajaxOptions: {
                    formatData: function(a) {
                        return a.data.rows.unshift({
                            id: -1,
                            name: '所有账号'
                        }), a.data.rows;
                    }
                },
                value: 'id',
                text: 'name',
                defaultSelected: 0,
                width: 78,
                listHeight: 620,
                callback: {
                    onListClick: function() {
                        $('#search').trigger('click');
                    }
                }
            }).getCombo();
        }
    },
    initGrid: function() {
        var a = Public.setGrid();
        $('#grid').jqGrid({
            url: '/Base/Log/list',
            postData: queryConditions,
            datatype: 'json',
            colModel: [{
                name: 'createTime',
                label: '<span class="ml10">时间</span>',
                width: 136,
                fixed: !0,
                classes: 'ti10 gray'
            }, {
                name: 'name',
                label: '姓名',
                width: 60,
                fixed: !0
            }, {
                name: 'log',
                label: '操作',
                width: 100,
                title: !0,
                classes: 'ui-ellipsis'
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
            if (is_user_log) {
                queryConditions.user = defaultPage.SYSTEM.uName;
            } else {
                queryConditions.user = userCombo.getValue() == -1 ? '' : userCombo.getText();
            }
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
        $('#delete-old').on('click', function(a) {
            a.preventDefault();
            if (Business.verifyRight('SYS_LOG_DELETE')) {
                $('.wrapper').append('<div id="div-lock"><div class="loading">回收中...</div></div>');
                Public.ajaxPost('/Base/Log/deleteOld', {}, function(res) {
                    $('#div-lock').remove();
                    if (res && 200 == res.status) {
                        $.dialog.tips(res.msg);
                        $('#div-lock').remove();
                        $('#search').trigger('click');
                    } else {
                        $.dialog.tips(res.msg, 2);
                    }
                }, function(err) {
                    $('#div-lock').remove();
                    $.dialog.tips(err.msg, 2);
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
            Public.resizeGrid();
        });
    }
};
$(function() {
    THISPAGE.init();
});
