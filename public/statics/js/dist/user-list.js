var defaultPage = Public.getDefaultPage();
var queryConditions = {
    department: '',
    position: '',
    isDisable: 0,
    normal: 'yes',
    skey: ''
};
var combo1 = $('#combo1').combo({
    data: '/Base/Category/list?typeNumber=departmenttype',
    ajaxOptions: {
        formatData: function(a) {
            return a.data.rows.unshift({
                id: -1,
                name: '所有部门'
            }), a.data.rows;
        }
    },
    value: 'id',
    text: 'name',
    defaultSelected: 0,
    width: 80,
    listHeight: 500,
    callback: {
        onChange: function() {
            if (this._selectedIndex > 0) { // 如果选择了具体部门，才动态加载对应部门的职位
                Public.ajaxGet('/Base/Category/list?typeNumber=positiontype&parentId=' + this.formattedData[this._selectedIndex].value, {}, function(res) {
                    if (res && 200 == res.status) {
                        res.data.rows.unshift({
                            id: -1,
                            name: '所有职位'
                        });
                        combo2.loadData(res.data.rows, -1, false)
                    }
                })
            }
        },
        onListClick: function() {
            $('#search').trigger('click');
        }
    }
}).getCombo();
var combo2 = $('#combo2').combo({
    data: [],
    value: 'id',
    text: 'name',
    defaultSelected: 0,
    width: 92,
    listHeight: 500,
    callback: {
        onListClick: function() {
            $('#search').trigger('click');
        }
    }
}).getCombo();
var combo3 = $('#combo3').combo({
    data: [
        {id: -1, name: '所有状态'},
        {id: 0, name: '在职'},
        {id: 1, name: '已离职'}
    ],
    value: 'id',
    text: 'name',
    defaultSelected: 1,
    width: 80,
    listHeight: 500,
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
        var a = Public.setGrid();
        $('#grid').jqGrid({
            url: '/Base/User/list',
            datatype: 'json',
            postData: queryConditions,
            colModel: [{
                name: 'id',
                label: '<span class="ml10">工号</span>',
                width: 52,
                fixed: !0,
                sortable: !0,
                classes: 'ti10 gray',
                formatter: function(x, y, z) {
                    return x ? String(x).padStart(4, '0') : '';
                }
            }, {
                name: 'name',
                label: '姓名',
                width: 100,
                fixed: !0,
                sortable: !0
            }, {
                name: 'userName',
                label: '账号名',
                width: 140,
                fixed: !0,
                sortable: !0
            }, {
                name: 'roleId',
                label: '角色',
                width: 100,
                fixed: !0,
                sortable: !0,
                classes: 'gray',
                formatter: function(val, opt, row) {
                    return val == 1 ? '管理员' : '普通账号';
                }
            }, {
                name: 'department',
                label: '部门',
                width: 100,
                fixed: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'position',
                label: '职位',
                width: 100,
                fixed: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'oprate',
                label: '操作',
                width: 100,
                formatter: function(val, opt, row) {
                    var resetPwdBtn = '<span class="resetPwd db fl pl10 pr10 h28 lh28 b-r-3 mr10 '
                        + (row.isDisable == 0 ? 'hover-yellow' : 'hover-silver')
                        + '" data-id="' + row.id
                        + '"><i class="iconfont icon-mima f12 mr5"></i>重置密码</span>';
                    var setRightsBtn = '';
                    if (row.roleId == 0) {
                        setRightsBtn = '<span class="setRights db fl pl10 pr10 h28 lh28 b-r-3 '
                        + (row.isDisable == 0 ? 'hover-gold' : 'hover-silver')
                        + '" data-id="' + row.id
                        + '"><i class="iconfont icon-quanxian f12 mr5"></i>分配权限</span>';
                    }
                    return resetPwdBtn + setRightsBtn;
                }
            }],
            cmTemplate: {sortable: !1, title: !1},
            jsonReader: {root: 'data.rows', records: 'data.records', repeatitems: !1, id: 'id'},
            width: a.w,
            height: a.h,
            altRows: !1,
            gridview: !0,
            rownumbers: !1,
            shrinkToFit: !0,
            forceFit: !1,
            pager: '#page',
            page: 1,
            rowNum: 10000,
            viewrecords: !0,
            loadComplete: function(res) {
                if (res && 200 == res.status) {
                    for (var i = 0; i < res.data.rows.length; i++) {
                        if (res.data.rows[i].isDisable == 1) { //已禁用账号
                            $('#' + res.data.rows[i].id).addClass('ui-state-disabled');
                        }
                    }
                } else {
                    $.dialog.tips(res.msg, 2);
                }
            },
            loadError: function(a, b) {
                $.dialog.tips('Type: ' + b + '; Response: ' + a.status + ' ' + a.statusText, 2);
            }
        });
    },
    initEvent: function() {
        $('#search').on('click', function() {
            queryConditions.department = combo1.getValue() == -1 ? '' : combo1.getText();
            queryConditions.position = combo2.getValue() == -1 ? '' : combo2.getText();
            queryConditions.isDisable = combo3.getValue();
            queryConditions.skey = $.trim($('#skey').val()).split(/[ ,，]+/).toString();
            $('#grid').jqGrid('setGridParam', {
                postData: queryConditions,
                page: 1
            }).trigger('reloadGrid');
        });
        $('#refresh').on('click', function() {
            window.location.reload();
        });
        $('.grid-wrap').on('click', '.resetPwd', function(a) {
            a.preventDefault();
            if (Business.verifyRight('USER_RESETPWD')) {
                var id = $(this).data('id');
                var rowData = $('#grid').jqGrid('getRowData', id);
                var userName = rowData.userName;
                $.dialog.confirm('防止误点击，确定重置密码？', function() {
                    Public.ajaxPost('/Base/User/resetPwd', {
                        userName: userName
                    }, function(res) {
                        if (res && 200 == res.status) {
                            $.dialog.tips('密码已重置为【' + res.data.newpass + '】！请登录后设置自己的新密码！', 0, 999);
                        } else {
                            $.dialog.tips(res.msg, 2);
                        }
                    });
                });
            }
        });
        $('.grid-wrap').on('click', '.setRights', function(a) {
            a.preventDefault();
            if (Business.verifyRight('USER_UPDATE')) {
                var id = $(this).data('id');
                var rowData = $('#grid').jqGrid('getRowData', id);
                $.dialog({
                    title: '分配权限 <b>【员工：' + rowData.name + '】</b>',
                    content: 'url:/Uri/user_setting',
                    data: {
                        userName: rowData.userName,
                        name: rowData.name
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
        $(window).on('keydown', function(a) {
            var curKey = a.which;
            if (curKey == 13) {
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
