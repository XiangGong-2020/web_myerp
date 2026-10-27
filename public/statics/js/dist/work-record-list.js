var urlParam = Public.urlParam();
var defaultPage = Public.getDefaultPage();
var queryConditions = {
    type: -1,
    targetName: $.inArray('WORKRECORD_ADVANCE', defaultPage.SYSTEM.rights) !== -1 ? '' : defaultPage.SYSTEM.uName,
    beginName: '',
    beginDate: '',
    endDate: '',
    skey: ''
};
var combo1 = $('#combo1').combo({
    data: [
        {id: -1, name: '所有记录'},
        {id: 0, name: '常规记录'},
        {id: 1, name: '贡献记录'},
        {id: 2, name: '过失记录'}
    ],
    value: 'id',
    text: 'name',
    defaultSelected: 0,
    width: 78,
    callback: {
        onListClick: function() {
            $('#search').trigger('click');
        }
    },
}).getCombo();
var THISPAGE = {
    init: function() {
        THISPAGE.initDom();
        THISPAGE.initGrid();
        THISPAGE.initEvent();
    },
    initDom: function() {
        $('#beginDate').val(defaultPage.SYSTEM.beginDate);
        $('#endDate').val(defaultPage.SYSTEM.endDate);
        Public.ajaxGet('/Base/User/queryAll?normal=yes', {}, function(data) {
            if (data.status === 200) {
                var normalUser = disableUser = '';
                $.each(data.data.rows, function(i, v) {
                    if (v.isDisable == 0) {
                        normalUser += '<option value="' + v.id + '">' + v.name + '</option>';
                    } else {
                        disableUser += '<option value="' + v.id + '">' + v.name + '</option>';
                    }
                });
                $('#normalUser').html(normalUser);
                $('#disableUser').html(disableUser);
                $('#targetNameCombo').fSelect({
                    placeholder: '选择员工',
                    showSearch: !0,
                    listHeight: 490
                });
                if ($.inArray('WORKRECORD_ADVANCE', defaultPage.SYSTEM.rights) === -1) {
                    $('.fs-input').val(defaultPage.SYSTEM.uName);
                    $('.fs-dropdown').addClass('dn').html('');
                    $('.fs-input').attr('disabled', true).addClass('ui-input-dis');
                }
            } else {
                $.dialog.tips(data.msg, 2);
            }
        });
    },
    initGrid: function() {
        var a = Public.setGrid();
        $('#grid').jqGrid({
            url: '/Base/WorkRecord/list',
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
                label: '记录类别',
                width: 66,
                fixed: !0,
                sortable: !0,
                formatter: function(a, b, c) {
                    return a == 0 ? '常规记录' : (a == 1 ? '贡献记录' : '过失记录');
                }
            }, {
                name: 'targetDate',
                label: '发生日期',
                width: 80,
                fixed: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'targetName',
                label: '目标员工',
                width: 100,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'content',
                label: '详情信息',
                width: 200,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis',
                formatter: function(a, b, c) {
                    var d = (c.type == 2 && c.targetAmount > 0) ? ('损失金额：' + c.targetAmount + '元，') : '';
                    return d + a;
                }
            }, {
                name: 'uName',
                label: '录入人',
                width: 54,
                fixed: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray'
            }, {
                name: 'createTime',
                label: '录入时间',
                width: 130,
                fixed: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray'
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
            ondblClickRow: function(a) {
                $('#' + a).find('.ui-icon-pencil').trigger('click');
            }
        });
    },
    initEvent: function() {
        laydate.render({
            elem: "#datePicker",
            type: 'date',
            range: ['#beginDate', '#endDate'],
            done: function() {
                setTimeout(function() {
                    $('#search').trigger('click');
                }, 300);
            }
        });
        $('#search').on('click', function(a) {
            a.preventDefault();
            queryConditions.type = combo1 ? combo1.getValue() : -1;
            queryConditions.targetName = $.trim($('.fs-input').val());
            queryConditions.beginDate = $('#beginDate').val() ? $.trim($('#beginDate').val()) : '';
            queryConditions.endDate = $('#endDate').val() ? $.trim($('#endDate').val()) : '';
            queryConditions.skey = $.trim($('#skey').val()).split(/[ ,，]+/).toString();
            $('#grid').jqGrid('setGridParam', {
                postData: queryConditions,
                page: 1
            }).trigger('reloadGrid');
        });
        $('.mod-search').on('click', '.fs-option, #selectAllBtn, #clearAllBtn', function() {
            setTimeout(function() {
                $('#search').trigger('click');
            }, 500);
        });
        $('#refresh').on('click', function() {
            window.location.reload();
        });
        $('#add').on('click', function(a) {
            a.preventDefault();
            if (Business.verifyRight('WORKRECORD_ADVANCE')) {
                var b = '新增工作记录';
                var c = {
                    oper: 'add',
                    callback: THISPAGE.callback
                };
                $.dialog({
                    title: b,
                    content: 'url:/Uri/work_record_manage',
                    data: c,
                    width: 1250,
                    height: 370,
                    max: !1,
                    min: !1,
                    lock: !0,
                    cache: !1
                });
            }
        });
        $('.grid-wrap').on('click', '.ui-icon-pencil', function(a) {
            a.preventDefault();
            var id = $(this).parent().data('id');
            $.dialog({
                title: '编辑工作记录',
                content: 'url:/Uri/work_record_manage',
                data: {
                    oper: 'edit',
                    rowId: id,
                    callback: THISPAGE.callback
                },
                width: 1250,
                height: 370,
                max: !1,
                min: !1,
                lock: !0,
                cache: !1
            });
        });
        $('.grid-wrap').on('click', '.ui-icon-trash', function(a) {
            a.preventDefault();
            if (Business.verifyRight('WORKRECORD_ADVANCE')) {
                var id = $(this).parent().data('id');
                id && $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                    Public.ajaxPost('/Base/WorkRecord/delete', {
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
        $(window).on('keydown', function(e) {
            var curKey = e.which;
            if (curKey == 13) {
                $('#beginDate, #endDate').blur();
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
        'edit' == b ? ($('#grid').jqGrid('setRowData', a.id, a), c && (d == 1 ? c.api.close() : c.THISPAGE.resetForm())) : ($('#grid').jqGrid('addRowData', a.id, a, 'first'), c && (d == 1 ? c.api.close() : c.THISPAGE.resetForm()));
    }
};
$(function() {
    THISPAGE.init();
});
