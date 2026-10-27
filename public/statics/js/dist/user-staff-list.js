var defaultPage = Public.getDefaultPage();
var queryConditions = {
    company: '',
    department: '',
    position: '',
    isDisable: 0,
    normal: 'yes',
    skey: ''
};
var combo1 = $('#combo1').combo({
    data: '/Base/Company/queryAll',
    ajaxOptions: {
        formatData: function(a) {
            return a.data.rows.unshift({
                id: -1,
                name: '所有主体'
            }), a.data.rows;
        }
    },
    value: 'id',
    text: 'name',
    defaultSelected: 0,
    width: 102,
    listHeight: 500,
    callback: {
        onListClick: function() {
            $('#search').trigger('click');
        }
    }
}).getCombo();
var combo2 = $('#combo2').combo({
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
    width: 78,
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
                        combo3.loadData(res.data.rows, -1, false);
                    }
                });
            }
        },
        onListClick: function() {
            $('#search').trigger('click');
        }
    }
}).getCombo();
var combo3 = $('#combo3').combo({
    data: [],
    value: 'id',
    text: 'name',
    defaultSelected: -1,
    width: 90,
    listHeight: 500,
    callback: {
        onListClick: function() {
            $('#search').trigger('click');
        }
    }
}).getCombo();
var combo4 = $('#combo4').combo({
    data: [
        {id: -1, name: '所有员工'},
        {id: 0, name: '在职员工'},
        {id: 1, name: '离职员工'}
    ],
    value: 'id',
    text: 'name',
    defaultSelected: 1,
    width: 78,
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
            postData: queryConditions,
            datatype: 'json',
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
                name: 'avatar',
                label: '头像',
                width: 42,
                fixed: !0,
                sortable: !0,
                formatter: function(a, b, c) {
                    return a ? ('<img src="/data/upfile/avatar/' + a + '" width="22" height="22" class="fl b-r-100">') : '<img src="/data/upfile/avatar/avatar.jpg" width="22" height="22" class="fl b-r-100">';
                }
            }, {
                name: 'name',
                label: '姓名',
                width: 54,
                fixed: !0,
                sortable: !0
            }, {
                name: 'sex',
                label: '性别',
                width: 42,
                fixed: !0,
                sortable: !0
            }, {
                name: 'company',
                label: '主体',
                width: 90,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'department',
                label: '部门',
                width: 78,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'position',
                label: '职位',
                width: 90,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'level',
                label: '等级',
                width: 42,
                fixed: !0,
                sortable: !0
            }, {
                name: 'mobile',
                label: '常用手机',
                width: 90,
                fixed: !0,
                sortable: !0
            }, {
                name: 'joinDate',
                label: '入职日期',
                width: 78,
                fixed: !0,
                sortable: !0,
                formatter: function(val, opt, row) {
                    return '0000-00-00' == row.joinDate ? '' : row.joinDate;
                }
            }, {
                name: 'leaveDate',
                label: '离职日期',
                width: 78,
                fixed: !0,
                sortable: !0,
                formatter: function(val, opt, row) {
                    return '0000-00-00' == row.leaveDate ? '' : row.leaveDate;
                }
            }, {
                name: 'isDisable',
                label: '在职状态',
                width: 68,
                fixed: !0,
                sortable: !0,
                formatter: function(a, b, c) {
                    var d = a == 0 ? '在职' : '已离职';
                    var e = a == 0 ? 'green' : 'red';
                    return '<span class="db h28 lh28 b-r-3 ' + e + '">' + d + '</span>';
                }
            }, {
                name: 'isBuySocialSecurity',
                label: '社保状态',
                width: 120,
                fixed: !0,
                sortable: !0,
                formatter: function(a, b, c) {
                    var d = a == 0 ? '未参保' : (a == 1 ? (c.buySocialSecurityDate + ' 已参保') : (c.stopSocialSecurityDate + ' 已停保'));
                    var e = a == 0 ? 'gray' : (a == 1 ? 'green' : 'red');
                    return '<span class="db h28 lh28 b-r-3 ' + e + '">' + d + '</span>';
                }
            }, {
                name: 'operate-edit',
                label: '操作',
                width: 210,
                formatter: function(a, b, c) {
                    var d = c.isDisable == 0 ? 'hover-white' : 'hover-silver';
                    var e = (
                        '<div data-id="' +
                        c.id +
                        '"><span class="operate-edit db fl pl10 pr10 h28 lh28 ' + (c.roleId == 0 ? 'b-r-3-left ' : 'b-r-3 ') + d + '" title="查看与编辑详细档案">详细档案</span>' + (c.roleId == 0 ? ('<span class="achievement-search db fl ml-1 pl10 pr10 h28 lh28 ' + d + '">绩效查询</span><span class="evaluate-staff db fl ml-1 pl10 pr10 h28 lh28 b-r-3-right ' + d + '">考评打分</span>') : '') + '</div>'
                    );
                    return e;
                }
            }],
            cmTemplate: {sortable: !1, title: !1},
            jsonReader: {root: 'data.rows', records: 'data.records', total: 'data.total', repeatitems: !1, id: 'id'},
            width: a.w,
            height: a.h,
            altRows: !1,
            gridview: !0,
            rownumbers: !1,
            multiselect: !1,
            shrinkToFit: !0,
            forceFit: !1,
            viewsortcols: [true, 'vertical', true],
            pager: '#page',
            page: 1,
            rowNum: 10000,
            viewrecords: !0,
            loadComplete: function(data) {
                if (data && 200 == data.status) {
                    for (var i = 0; i < data.data.rows.length; i++) {
                        if (data.data.rows[i].isDisable == 1) { //已禁用账号
                            $('#' + data.data.rows[i].id).addClass('ui-state-disabled');
                        }
                    }
                } else {
                    data.status == -1 && $.dialog.tips(data.msg, 2);
                }
            },
            loadError: function(a, b) {
                $.dialog.tips('Type: ' + b + '; Response: ' + a.status + ' ' + a.statusText, 2);
            },
            ondblClickRow: function(c) {
                $('#' + c).find('.operate-edit').trigger('click');
            }
        });
    },
    initEvent: function() {
        $('#search').on('click', function() {
            queryConditions.company = combo1.getValue() == -1 ? '' : combo1.getText();
            queryConditions.department = combo2.getValue() == -1 ? '' : combo2.getText();
            queryConditions.position = combo3.getValue() == -1 ? '' : combo3.getText();
            queryConditions.isDisable = combo4.getValue();
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
            if (Business.verifyRight('USER_STAFF_ADD')) {
                $.dialog({
                    title: '新增员工档案',
                    content: 'url:/Uri/user_staff_manage',
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
        $('#addFromApplicant').on('click', function(a) {
            a.preventDefault();
            if (Business.verifyRight('APPLICANT')) {
                parent.tab.addTabItem({
                    tabid: 'personnel-applicant',
                    text: '<i class="iconfont icon-ziliao f14 mr5"></i>应聘档案',
                    url: '/Uri/applicant_list'
                });
            }
        });
        $('#toUserList').on('click', function(a) {
            a.preventDefault();
            if (Business.verifyRight('USER_QUERY')) {
                parent.tab.addTabItem({
                    tabid: 'setting-user',
                    text: '<i class="iconfont icon-yonghuzu f14 mr5"></i>员工账号',
                    url: '/Uri/user_list'
                });
            }
        });
        $('#toEvaluationList').on('click', function(a) {
            a.preventDefault();
            if (Business.verifyRight('EVALUATION_QUERY')) {
                parent.tab.addTabItem({
                    tabid: 'personnel-evaluation',
                    text: '<i class="iconfont icon-leibie f14 mr5"></i>考评项目',
                    url: '/Uri/evaluation_list'
                });
            }
        });
        $('.grid-wrap').on('click', '.operate-edit', function(a) {
            a.preventDefault();
            if (Business.verifyRight('USER_STAFF_UPDATE')) {
                var id = $(this).parent().data('id');
                $.dialog({
                    title: '编辑员工档案',
                    content: 'url:/Uri/user_staff_manage',
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
        $('.grid-wrap').on('click', '.achievement-search', function(a) {
            a.preventDefault();
            if (Business.verifyRight('ACHIEVEMENT_SEARCH')) {
                var id = $(this).parent().data('id');
                var rowData = $('#grid').jqGrid('getRowData', id);
                $.dialog({
                    title: '绩效查询',
                    content: 'url:/Uri/achievement_search',
                    data: {
                        uId: id,
                        uName: rowData.name
                    },
                    width: $(window).width() - 10,
                    height: $(window).height() - 100,
                    left: 5,
                    top: 5,
                    max: !0,
                    min: !0,
                    lock: !0,
                    cache: !1
                });
            }
        });
        $('.grid-wrap').on('click', '.evaluate-staff', function(a) {
            a.preventDefault();
            if (Business.verifyRight('EVALUATION_QUERY')) {
                var id = $(this).parent().data('id');
                var rowData = $('#grid').jqGrid('getRowData', id);
                $.dialog({
                    title: '考评打分',
                    content: 'url:/Uri/evaluate_staff',
                    data: {
                        uId: id,
                        uName: rowData.name
                    },
                    width: $(window).width() - 10,
                    height: $(window).height() - 100,
                    left: 5,
                    top: 5,
                    max: !0,
                    min: !0,
                    lock: !0,
                    cache: !1
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
    },
    callback: function(a, b, c, d) {
        var e = $('#grid').data('gridData');
        e || (e = {}, $('#grid').data('gridData', e));
        e[a.id] = a;
        'edit' == b ? ($('#grid').jqGrid('setRowData', a.id, a), c && (d == 1 ? c.api.close() : c.THISPAGE.resetForm())) : (a.isDisable = 0, $('#grid').jqGrid('addRowData', a.id, a, 'first'), c && (d == 1 ? c.api.close() : c.THISPAGE.resetForm()));
    }
};
$(function() {
    THISPAGE.init();
});
