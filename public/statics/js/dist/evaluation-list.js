var defaultPage = Public.getDefaultPage();
var evaluations = [];
var queryConditions = {
    type: '',
    skey: ''
};
var combo1 = $('#combo1').combo({
    data: '/Base/Category/list?typeNumber=positiontype',
    ajaxOptions: {
        formatData: function(a) {
            return a.data.rows.unshift({
                id: -1,
                name: '所有考评项目'
            }, {
                id: 0,
                name: '【通用】'
            }), a.data.rows;
        }
    },
    value: 'id',
    text: 'name',
    defaultSelected: 0,
    width: 114,
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
            url: '/Bill/Evaluate/evaluationList',
            postData: queryConditions,
            datatype: 'json',
            colModel: [{
                name: 'type',
                label: '对应职位',
                width: 80,
                fixed: !0,
                sortable: !0,
                classes: 'gray ui-ellipsis'
            }, {
                name: 'name',
                label: '考评内容',
                width: 100,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'operate',
                label: '操作',
                width: 72.8,
                fixed: !0,
                formatter: Public.operFmatter
            }],
            cmTemplate: {sortable: !1, title: !1},
            jsonReader: {root: 'data.rows', records: 'data.records', total: 'data.total', repeatitems: !1, id: 'id'},
            autoWidth: !0,
            height: a.h,
            altRows: !1,
            gridview: !0,
            rownumbers: !0,
            multiselect: !1,
            shrinkToFit: !0,
            forceFit: !1,
            viewsortcols: [true, 'vertical', true],
            footerrow: !0,
            userDataOnFooter: !0,
            loadComplete: function(data) {
                if (data && data.status == 200) {
                    $('#grid').jqGrid('footerData', 'set', {
                        rn: '合计',
                        name: data.data.records + ' 项'
                    });
                    if (queryConditions.type == '' && queryConditions.skey == '') {
                        evaluations = data.data.rows;
                        THISPAGE.initGridUser();
                    }
                } else {
                    $.dialog.tips(data.msg, 2);
                }
            },
            loadError: function(a, b) {
                $.dialog.tips('Type: ' + b + '; Response: ' + a.status + ' ' + a.statusText, 2);
            },
            gridComplete: function() {
                setTimeout(function() {
                    Public.autoGridWidth($('#grid'));
                }, 10);
            },
            ondblClickRow: function(c) {
                $('#grid #' + c).find('.ui-icon-pencil').trigger('click');
            }
        });
    },
    initGridUser: function() {
        var a = Public.setGrid();
        $('#gridUser').jqGrid({
            url: '/Base/User/queryAll',
            postData: {roleId: 0, isDisable: 0, normal: 'yes'},
            datatype: 'json',
            colModel: [{
                name: 'name',
                label: '姓名',
                fixed: !0,
                width: 70,
                sortable: !0
            }, {
                name: 'evaluationIds',
                label: '考评项目',
                width: 100,
                sortable: !0,
                classes: 'gray',
                formatter: function(a, b, c) {
                    if (a) {
                        var eva = a.split(',');
                        var evaLen = eva.length;
                        var evaTitle = '';
                        if (evaLen > 0) {
                            $.each(eva, function(k, v) {
                                var evaInfo = evaluations.filter(item => item.id == v);
                                evaTitle += (k + 1) + '、' + evaInfo[0]['name'] + "\r\n";
                            });
                        }
                        return '<i class="iconfont icon-liebiao f12" title="' + evaTitle + '"> 已设定 ' + evaLen + ' 项</i>';
                    } else {
                        return '';
                    }
                }
            }, {
                name: 'operate',
                label: '操作',
                width: 80,
                fixed: !0,
                formatter: function(a, b, c) {
                    return '<span class="eva-oper db fl pl5 pr5 h28 lh28 hover-white b-r-3" data-id="' + c.id + '">单独设定</span>';
                }
            }],
            cmTemplate: {sortable: !1, title: !1},
            jsonReader: {root: 'data.rows', records: 'data.records', total: 'data.total', repeatitems: !1, id: 'id'},
            autoWidth: !0,
            height: a.h,
            altRows: !1,
            gridview: !0,
            rownumbers: !0,
            multiselect: !0,
            shrinkToFit: !0,
            forceFit: !1,
            viewsortcols: [true, 'vertical', true],
            sortname: 'id',
            sortorder: 'ASC',
            footerrow: !0,
            userDataOnFooter: !0,
            loadComplete: function(data) {
                if (data && data.status == 200) {
                    $('#gridUser').jqGrid('footerData', 'set', {
                        rn: '合计',
                        name: data.data.records + ' 人'
                    });
                } else {
                    $.dialog.tips(data.msg, 2);
                }
            },
            loadError: function(a, b) {
                $.dialog.tips('Type: ' + b + '; Response: ' + a.status + ' ' + a.statusText, 2);
            },
            gridComplete: function() {
                setTimeout(function() {
                    Public.autoGridWidth($('#gridUser'));
                }, 10);
            },
            ondblClickRow: function(c) {
                $('#gridUser #' + c).find('.eva-oper').trigger('click');
            }
        });
    },
    initEvent: function() {
        $('#search').on('click', function() {
            queryConditions.type = combo1.getValue() == -1 ? '' : combo1.getText();
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
            if (Business.verifyRight('EVALUATION_ADVANCE')) {
                $.dialog({
                    title: '新增考评项目',
                    content: 'url:/Uri/evaluation_manage',
                    data: {
                        oper: 'add',
                        callback: THISPAGE.callback
                    },
                    width: 1000,
                    height: 140,
                    max: !1,
                    min: !1,
                    cache: !1,
                    lock: !0
                });
            }
        });
        $('#grid').on('click', '.ui-icon-pencil', function(a) {
            a.preventDefault();
            if (Business.verifyRight('EVALUATION_ADVANCE')) {
                var id = $(this).parent().data('id');
                $.dialog({
                    title: '编辑考评项目',
                    content: 'url:/Uri/evaluation_manage',
                    data: {
                        oper: 'edit',
                        rowId: id,
                        callback: THISPAGE.callback
                    },
                    width: 1000,
                    height: 140,
                    max: !1,
                    min: !1,
                    cache: !1,
                    lock: !0
                });
            }
        });
        $('#grid').on('click', '.ui-icon-trash', function(a) {
            a.preventDefault();
            if (Business.verifyRight('EVALUATION_ADVANCE')) {
                var id = $(this).parent().data('id');
                id && $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                    Public.ajaxPost('/Bill/Evaluate/evaluationDelete', {
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
        $('#batch-eva-oper').on('click', function(a) {
            a.preventDefault();
            if (Business.verifyRight('EVALUATION_ADVANCE')) {
                var ids = $('#gridUser').jqGrid('getGridParam', 'selarrrow');
                if (ids.length > 0) {
                    var names = [];
                    $.each(ids, function(k, v) {
                        var rowData = $('#gridUser').jqGrid('getRowData', v);
                        names.push(rowData.name);
                    });
                    $.dialog({
                        title: names.join('+') + ' 考评项目设定',
                        content: 'url:/Uri/evaluation_oper',
                        data: {
                            oper: 'edit',
                            rowId: ids.join(','),
                            callback: THISPAGE.callbackUser
                        },
                        width: 1000,
                        height: 600,
                        max: !1,
                        min: !1,
                        cache: !1,
                        lock: !0
                    });
                } else {
                    $.dialog.tips('请先批量勾选员工！', 2);
                }
            }
        });
        $('#gridUser').on('click', '.eva-oper', function(a) {
            a.preventDefault();
            if (Business.verifyRight('EVALUATION_ADVANCE')) {
                var id = $(this).data('id');
                var rowData = $('#gridUser').jqGrid('getRowData', id);
                $.dialog({
                    title: rowData.name + ' 考评项目设定',
                    content: 'url:/Uri/evaluation_oper',
                    data: {
                        oper: 'edit',
                        rowId: id.toString(),
                        callback: THISPAGE.callbackUser
                    },
                    width: 1000,
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
            Public.autoGridWidth($('#grid'));
            Public.autoGridWidth($('#gridUser'));
        });
    },
    callback: function(a, b, c, d) {
        combo1._setDefaultSelected(0);
        $('#skey').val('');
        queryConditions = {
            type: '',
            skey: ''
        };
        $('#grid').jqGrid('setGridParam', {
            postData: queryConditions,
            page: 1
        }).trigger('reloadGrid');
        c && (d == 1 ? c.api.close() : c.THISPAGE.resetForm());
    },
    callbackUser: function(a, b, c) {
        $('#gridUser').jqGrid().trigger('reloadGrid');
        c && c.api.close();
    }
};
$(function() {
    THISPAGE.init();
});
