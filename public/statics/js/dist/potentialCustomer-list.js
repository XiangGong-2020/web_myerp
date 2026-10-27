var defaultPage = Public.getDefaultPage();
var queryConditions = {
    categoryId: -1,
    disableType: -1,
    skey: ''
};
var categoryCombo = Business.categoryCombo($('#category'), {
    addOptions: {
        value: -1,
        text: '所有客户类别'
    },
    defaultSelected: 0,
    width: 126,
    listHeight: 470,
    callback: {
        onListClick: function() {
            $('#search').trigger('click');
        }
    }
}, 'customertype');
var disableTypeCombo = $('#disableTypeCombo').combo({
    data: [
        {id: 0, name: '未跟进'},
        {id: 1, name: '已放弃'},
        {id: 2, name: '已转化'}
    ],
    value: 'id',
    text: 'name',
    width: 102,
    listHeight: 500,
    defaultSelected: 0,
    cache: !1,
    editable: !1,
    trigger: !0,
    emptyOptions: !1,
    callback: {
        onListClick: function() {
            $('#search').trigger('click');
        }
    },
    queryDelay: 0
}).getCombo();
var curRow;
var THISPAGE = {
    init: function() {
        THISPAGE.initGrid();
        THISPAGE.initEvent();
    },
    initGrid: function() {
        var a = Public.setGrid();
        $('#grid').jqGrid({
            url: '/Base/PotentialCustomer/list',
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
                name: 'name',
                label: '名称',
                width: 70,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'contacter',
                label: '联系人',
                width: 54,
                fixed: !0,
                title: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'wangwang',
                label: '旺旺',
                width: 100,
                fixed: !0,
                title: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'weixin',
                label: '微信',
                width: 100,
                fixed: !0,
                title: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'im',
                label: 'QQ',
                width: 90,
                fixed: !0
            }, {
                name: 'mobile',
                label: '手机',
                width: 90,
                fixed: !0
            }, {
                name: 'phone',
                label: '座机',
                width: 100,
                fixed: !0
            }, {
                name: 'deliveryAddress',
                label: '联系地址',
                width: 100,
                fixed: !0,
                title: !0,
                classes: 'ui-ellipsis',
                formatter: function(c, d, e) {
                    return (e.province || '') + (e.city || '') + (e.county || '') + (c || '');
                }
            }, {
                name: 'uId',
                label: 'uId',
                hidden: !0
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
                width: 124,
                fixed: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray',
                formatter: function(a, b, c) {
                    return ('0000-00-00 00:00:00' == a || undefined == a) ? '' : a;
                }
            }, {
                name: 'description',
                label: '跟进备注',
                width: 100,
                title: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'isDisable',
                label: '跟进状态',
                width: 88,
                fixed: !0,
                sortable: !0,
                align: 'center',
                formatter: function(a, b, c) {
                    if (a == 0) {
                        return '<span class="disable-btn fl pl5 pr5 tc h28 lh28 b-r-3 hover-red" title="点击放弃跟进" data-isdisable="1" data-id="' + c.id + '">放弃</span>' + '<span class="disable-btn fl ml2 pl5 pr5 tc h28 lh28 b-r-3 hover-red" title="点击转化为正式客户" data-isdisable="2" data-id="' + c.id + '">转化</span>';
                    } else if (a == 1) {
                        return '<span class="disable-btn db tc h28 lh28 b-r-3 hover-gray" title="点击取消放弃" data-isdisable="0" data-id="' + c.id + '">已放弃跟进</span>';
                    } else if (a == 2) {
                        return '<span class="db tc h28 lh28 b-r-3 hover-green" data-isdisable="0" data-id="' + c.id + '">已转正式客户</span>';
                    }
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
            multiselect: !0,
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
        $('#search').on('click', function() {
            $(this).addClass('current').siblings().removeClass('current');
            queryConditions.categoryId = categoryCombo.getValue();
            queryConditions.disableType = disableTypeCombo.getValue();
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
            if (Business.verifyRight('SERVICE_ADD')) {
                $.dialog({
                    title: '新增潜在客户',
                    content: 'url:/Uri/potentialCustomer_manage',
                    data: {
                        oper: 'add',
                        callback: THISPAGE.callback
                    },
                    width: 1250,
                    height: 400,
                    max: !1,
                    min: !1,
                    cache: !1,
                    lock: !0
                });
            }
        });
        $('.grid-wrap').on('click', '.ui-icon-pencil', function(a) {
            a.preventDefault();
            if (Business.verifyRight('SERVICE_QUERY')) {
                var id = $(this).parent().data('id');
                $.dialog({
                    title: '编辑潜在客户',
                    content: 'url:/Uri/potentialCustomer_manage',
                    data: {
                        oper: 'edit',
                        rowId: id,
                        callback: THISPAGE.callback
                    },
                    width: 1250,
                    height: 400,
                    max: !1,
                    min: !1,
                    cache: !1,
                    lock: !0
                });
            }
        });
        $('.grid-wrap').on('click', '.ui-icon-trash', function(a) {
            a.preventDefault();
            if (Business.verifyRight('SERVICE_DELETE')) {
                var id = $(this).parent().data('id');
                id && $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                    Public.ajaxPost('/Base/PotentialCustomer/delete', {
                        id: id
                    }, function(res) {
                        if (res && 200 == res.status) {
                            $('#grid').jqGrid('delRowData', id);
                            $.dialog.tips('删除成功！');
                        } else {
                            $.dialog.tips('删除失败！' + res.msg, 2);
                        }
                    });
                });
            }
        });
        $('.grid-wrap').on('click', '.disable-btn', function(a) {
            a.stopPropagation();
            a.preventDefault();
            if (Business.verifyRight('SERVICE_UPDATE')) {
                var id = $(this).data('id');
                var isdisable = Number($(this).data('isdisable'));
                if (id) {
                    if (isdisable != 2) {
                        Public.ajaxPost('/Base/PotentialCustomer/disable', {
                            id: id,
                            isDisable: isdisable
                        }, function(res) {
                            if (res && 200 == res.status) {
                                parent.$.dialog.tips('操作成功！');
                                $('#grid').jqGrid('setCell', id, 'isDisable', isdisable);
                            } else {
                                $.dialog.tips('操作失败！' + res.msg, 2);
                            }
                        });
                    } else {
                        if (Business.verifyRight('CUSTOMER_ADD')) {
                            Public.ajaxPost('/Base/PotentialCustomer/query', {
                                id: id
                            }, function(res1) {
                                if (200 == res1.status) {
                                    curRow = id;
                                    $.dialog({
                                        title: '新增客户',
                                        content: 'url:/Uri/customer_manage',
                                        data: {
                                            oper: 'add',
                                            data: res1.data,
                                            callback: THISPAGE.callbackCustomer
                                        },
                                        onlyAdd: !0,
                                        width: 1250,
                                        height: 500,
                                        max: !1,
                                        min: !1,
                                        cache: !1,
                                        lock: !0
                                    });
                                }
                            });
                        }
                    }
                }
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
    },
    callbackCustomer: function(a, b, c, d) {
        Public.ajaxPost('/Base/PotentialCustomer/disable', {
            id: curRow,
            isDisable: 2
        }, function(res) {
            if (res && 200 == res.status) {
                $('#grid').jqGrid('setCell', curRow, 'isDisable', 2);
                d == 1 ? c.api.close() : c.THISPAGE.resetForm();
            } else {
                parent.parent.$.dialog.tips('操作失败！' + res.msg, 2);
                d == 1 ? c.api.close() : c.THISPAGE.resetForm();
            }
        });
    }
};
$(function() {
    THISPAGE.init();
});
