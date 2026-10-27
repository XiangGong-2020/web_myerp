var urlParam = Public.urlParam();
var defaultPage = Public.getDefaultPage();
var queryConditions = {
    uId: urlParam.uId || ($.inArray('SYS_ADVANCE', defaultPage.SYSTEM.rights) === -1 ? defaultPage.SYSTEM.uId : 0),
    searchType: urlParam.unDone ? 1 : 0,
    beginDate: '',
    endDate: urlParam.endDate ? (urlParam.endDate + ' 23:59:59') : '',
    skey: '',
    unDone: urlParam.unDone || 0
};
var combo1 = $('#combo1').combo({
    data: '/Base/User/queryAll?normal=yes',
    ajaxOptions: {
        formatData: function(a) {
            return a.data.rows.unshift({
                id: -1,
                name: '所有报审人'
            }), a.data.rows;
        }
    },
    value: 'id',
    text: 'name',
    width: 90,
    listHeight: 500,
    defaultSelected: urlParam.uId ? ['id', urlParam.uId] : ($.inArray('SYS_ADVANCE', defaultPage.SYSTEM.rights) === -1 ? ['id', defaultPage.SYSTEM.uId] : 0),
    disabled: $.inArray('SYS_ADVANCE', defaultPage.SYSTEM.rights) === -1 ? !0 : !1,
    callback: {
        onListClick: function() {
            $('#search').trigger('click');
        }
    }
}).getCombo();
var combo2 = $('#combo2').combo({
    data: [
        {id: 0, name: '所有状态'},
        {id: 1, name: '未审批'},
        {id: 2, name: '已拒绝'}
    ],
    value: 'id',
    text: 'name',
    defaultSelected: urlParam.unDone ? 1 : 0,
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
        THISPAGE.initDom();
        THISPAGE.initGrid();
        THISPAGE.initEvent();
    },
    initDom: function() {
        if ($.inArray('SYS_ADVANCE', defaultPage.SYSTEM.rights) === -1) {
            $('#beginDate').val(Public.reduceMonths((new Date).format('yyyy-MM') + '-01', 2) + ' 00:00:00').addClass('ui-input-dis');
            $('#endDate').val((urlParam.endDate || defaultPage.SYSTEM.endDate) + ' 23:59:59').addClass('ui-input-dis');
        } else {
            $('#beginDate').val(defaultPage.SYSTEM.beginDate + ' 00:00:00');
            $('#endDate').val((urlParam.endDate || defaultPage.SYSTEM.endDate) + ' 23:59:59');
        }
    },
    initGrid: function() {
        var a = Public.setGrid();
        $('#grid').jqGrid({
            url: '/Base/QuoteReq/list',
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
                name: 'title',
                label: '报审标题',
                width: 200,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'content',
                label: '报审详情',
                width: 200,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'uId',
                label: 'uId',
                hidden: !0
            }, {
                name: 'uName',
                label: '报审人',
                width: 54,
                fixed: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray'
            }, {
                name: 'createTime',
                label: '报审时间',
                width: 124,
                fixed: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray',
                formatter: function(cellvalue, options, rowObject) {
                    return ('0000-00-00 00:00:00' == cellvalue || undefined == cellvalue) ? '' : cellvalue;
                }
            }, {
                name: 'checkName',
                label: '审批人',
                width: 54,
                fixed: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray'
            }, {
                name: 'checkTime',
                label: '审批时间',
                width: 124,
                fixed: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray',
                formatter: function(cellvalue, options, rowObject) {
                    return ('0000-00-00 00:00:00' == cellvalue || undefined == cellvalue) ? '' : cellvalue;
                }
            }, {
                name: 'description',
                label: '拒绝理由',
                width: 100,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray'
            }, {
                name: 'checkId',
                label: '审批',
                width: 82,
                fixed: !0,
                sortable: !0,
                formatter: function(a, b, c) {
                    var d = c.description ? '已拒绝' : (a == 0 ? '批准' : '已批准');
                    var e = c.description ? '删除拒绝理由可以取消拒绝' : (a == 0 ? '点击批准' : '点击反批');
                    var f = c.description ? 'hover-night' : (a == 0 ? 'hover-teal' : 'hover-green');
                    return '<span class="' + (c.description ? '' : 'check-btn ') + 'fl db pl5 pr5 h28 lh28 b-r-3 ' + f + '" title="' + e + '" data-id="' + c.id + '" data-checkid="' + a + '">' + d + '</span>' + ((a == 0 && c.description == '') ? '<span class="refuse-btn fl ml2 db pl5 pr5 h28 lh28 b-r-3 hover-red" title="点击拒绝" data-id="' + c.id + '">拒绝</span>' : '');
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
        if ($.inArray('SYS_ADVANCE', defaultPage.SYSTEM.rights) !== -1) {
            laydate.render({
                elem: "#datePicker",
                type: 'datetime',
                range: ['#beginDate', '#endDate'],
                done: function() {
                    setTimeout(function() {
                        $('#search').trigger('click');
                    }, 300);
                }
            });
        }
        $('#search').on('click', function(a) {
            a.preventDefault();
            queryConditions.uId = combo1.getValue();
            queryConditions.searchType = combo2.getValue();
            queryConditions.beginDate = $('#beginDate').val() ? $.trim($('#beginDate').val()) : '';
            queryConditions.endDate = $('#endDate').val() ? $.trim($('#endDate').val()) : '';
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
            var b = '新增报价审批';
            var c = {
                oper: 'add',
                callback: THISPAGE.callback
            };
            $.dialog({
                title: b,
                content: 'url:/Uri/quoteReq_manage',
                data: c,
                width: 1250,
                height: 340,
                max: !1,
                min: !1,
                cache: !1,
                lock: !0
            });
        });
        $('#grid').on('click', '.ui-icon-pencil', function(a) {
            a.preventDefault();
            var id = $(this).parent().data('id');
            $.dialog({
                title: '编辑报价审批',
                content: 'url:/Uri/quoteReq_manage',
                data: {
                    oper: 'edit',
                    rowId: id,
                    callback: THISPAGE.callback
                },
                width: 1250,
                height: 340,
                max: !1,
                min: !1,
                cache: !1,
                lock: !0
            });
        });
        $('#grid').on('click', '.ui-icon-trash', function(a) {
            a.preventDefault();
            var id = $(this).parent().data('id');
            id && $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                Public.ajaxPost('/Base/QuoteReq/delete', {
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
        });
        $('#grid').on('click', '.check-btn', function(a) {
            if (Business.verifyRight('SALE_MANAGER')) {
                var id = $(this).data('id');
                var checkId = Number($(this).data('checkid')) > 0 ? 0 : defaultPage.SYSTEM.uId;
                var checkName = Number($(this).data('checkid')) > 0 ? "" : defaultPage.SYSTEM.uName;
                var rowData = $('#grid').jqGrid('getRowData', id);
                id && Public.ajaxPost('/Base/QuoteReq/check', {
                    id: id,
                    checkId: checkId,
                    checkName: checkName,
                    title: rowData.title
                }, function(res) {
                    if (res && 200 == res.status) {
                        $.dialog.tips((checkId > 0 ? '审批' : '反审批') + '成功！');
                        $('#grid').jqGrid('setRowData', id, res.data);
                    } else {
                        $.dialog.tips((checkId > 0 ? '审批' : '反审批') + '失败！' + res.msg, 2);
                    }
                });
            }
        });
        $('#grid').on('click', '.refuse-btn', function(a) {
            a.preventDefault();
            var id = $(this).data('id');
            $.dialog({
                title: '编辑报价审批',
                content: 'url:/Uri/quoteReq_manage',
                data: {
                    oper: 'edit',
                    rowId: id,
                    callback: THISPAGE.callback
                },
                width: 1250,
                height: 340,
                max: !1,
                min: !1,
                cache: !1,
                lock: !0
            });
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
    callback: function(a, b, c, d) {
        var e = $('#grid').data('gridData');
        e || (e = {}, $('#grid').data('gridData', e));
        e[a.id] = a;
        'edit' == b ? ($('#grid').jqGrid('setRowData', a.id, a), c && (d == 1 ? c.api.close() : c.THISPAGE.resetForm())) : ($('#grid').jqGrid('addRowData', a.id, a, 'first'), c && (d == 1 ? c.api.close() : c.THISPAGE.resetForm()));
    }
};
$(function() {
    THISPAGE.init();
});
