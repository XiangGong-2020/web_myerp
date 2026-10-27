var THISPAGE = {
    init: function() {
        THISPAGE.initGrid();
        THISPAGE.initEvent();
    },
    initGrid: function() {
        var a = Public.setGrid();
        $('#grid').jqGrid({
            url: '/Base/Storage/list',
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
                width: 150,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'address',
                label: '地址',
                width: 100,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'phone',
                label: '电话',
                width: 200,
                fixed: !0,
                title: !0,
                sortable: !0
            }, {
                name: 'empId',
                label: '应急联系人ID',
                width: 0,
                hidden: !0
            }, {
                name: 'empName',
                label: '应急联系人',
                width: 100,
                fixed: !0,
                title: !0,
                sortable: !0
            }, {
                name: 'type',
                label: '类型',
                width: 54,
                fixed: !0,
                sortable: !0,
                align: 'center',
                formatter: function(a, b, c) {
                    return 0 == a ? '实物仓' : '虚拟仓';
                }
            }, {
                name: 'allowNeg',
                label: '爆仓',
                width: 54,
                fixed: !0,
                sortable: !0,
                align: 'center',
                formatter: function(a, b, c) {
                    return 0 == a ? '不允许' : '允许';
                }
            }, {
                name: 'isDisable',
                label: '启用',
                width: 54,
                fixed: !0,
                sortable: !0,
                align: 'center',
                formatter: function(a, b, c) {
                    var d = a == 0 ? '已启用' : '已禁用';
                    var e = a == 0 ? 'hover-green' : 'hover-red';
                    var f = a == 0 ? '点击禁用' : '点击启用';
                    return '<span class="disable-btn db h28 lh28 b-r-3 ' + e + '" title="' + f + '" data-isdisable="' + a + '" data-id="' + c.id + '">' + d + '</span>';
                }
            }, {
                name: '',
                label: '',
                width: 86,
                fixed: !0,
                sortable: !1,
                align: 'center',
                formatter: function(a, b, c) {
                    return '<span class="create-partition-btn db h28 lh28 b-r-3 hover-white iconfont icon-zengjia f12" data-storageid="' + c.id + '"> 新增分区</span>';
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
            rownumbers: !1,
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
            subGrid: !0,
            subGridWidth: 30,
            subGridOptions: {
                expandOnLoad: !0
            },
            subGridRowExpanded: function(subgrid_id, row_id) {
                Public.ajaxPost('/Base/Storage/locationList', {
                    storageId: row_id,
                    order: 'DESC'
                }, function(res) {
                    var parent = {};
                    var child = {};
                    $.each(res.data, function(i, v) {
                        if (v.parentId == 0) {
                            if (!parent['div_' + v.id]) {
                                parent['div_' + v.id] = '';
                            }
                            parent['div_' + v.id] =
                                '<div id="div_' + v.id + '" class="m10 p10 bg-white b-r-3 ovh">' +
                                '<div class="ovh pt10 pb10">' +
                                '<span class="fl lh20">分区：' + v.name + '</span>' +
                                '<span class="create-location-btn fr ml-1 pl10 pr10 b-r-3-right hover-white f12 lh20" data-storageid="' + row_id + '" data-parentid="' + v.id + '"> 新增库位</span>' +
                                '<span class="delete-partition-btn fr ml-1 pl10 pr10 hover-white f12 lh20" data-id="' + v.id + '"> 删除分区</span>' +
                                '<span class="edit-partition-btn fr pl10 pr10 b-r-3-left hover-white f12 lh20" data-id="' + v.id + '"> 编辑分区</span>' +
                                '</div>' +
                                '<div id="div_' + v.id + '_location" style="min-height:40px;max-height:200px;padding-bottom:10px;overflow-y:auto;background:#f5f5f5;"></div>' +
                                '</div > ';
                        } else {
                            if (!child['div_' + v.parentId]) {
                                child['div_' + v.parentId] = '';
                            }
                            child['div_' + v.parentId] += '<div data-id="' + v.id + '" class="each"><span class="each-name" title="编辑库位">' + v.name + '</span><i class="each-trash fr iconfont icon-shanchu" title="删除库位"></i></div>';
                        }
                    });
                    if (Object.keys(parent).length > 0) {
                        $.each(parent, function(i, v) {
                            $('#' + subgrid_id).append(v);
                            $('#' + i + '_location').append(child[i] || '<div class="ml10 mt10 lh30 gray">暂无库位</div>');
                        });
                    } else {
                        if (Object.keys(child).length > 0) {
                            $.each(child, function(i, v) {
                                $('#' + subgrid_id).append(v);
                            });
                        }
                    }
                });
            },
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
        $('#refresh').on('click', function() {
            window.location.reload();
        });
        $('#add').on('click', function(a) {
            a.preventDefault();
            if (Business.verifyRight('STORAGE_ADD')) {
                var b = '新增仓库';
                var c = {
                    oper: 'add',
                    callback: THISPAGE.callback
                };
                $.dialog({
                    title: b,
                    content: 'url:/Uri/storage_manage',
                    data: c,
                    width: 600,
                    height: 250,
                    max: !1,
                    min: !1,
                    lock: !0,
                    cache: !1
                });
            }
        });
        $('.grid-wrap').on('click', '.ui-icon-pencil', function(a) {
            a.preventDefault();
            if (Business.verifyRight('STORAGE_UPDATE')) {
                var b = $(this).parent().data('id');
                var c = '编辑仓库';
                var d = {
                    oper: 'edit',
                    rowId: b,
                    callback: THISPAGE.callback
                };
                $.dialog({
                    title: c,
                    content: 'url:/Uri/storage_manage',
                    data: d,
                    width: 600,
                    height: 250,
                    max: !1,
                    min: !1,
                    lock: !0,
                    cache: !1
                });
            }
        });
        $('.grid-wrap').on('click', '.ui-icon-trash', function(a) {
            a.preventDefault();
            if (Business.verifyRight('STORAGE_DELETE')) {
                var id = $(this).parent().data('id');
                id && $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                    Public.ajaxPost('/Base/Storage/delete', {
                        storageId: id
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
        $('.grid-wrap').on('click', '.disable-btn', function(a) {
            a.preventDefault();
            if (Business.verifyRight('STORAGE_DISABLE')) {
                var id = $(this).data('id');
                var isDisable = Number(!$(this).data('isdisable'));
                id && Public.ajaxPost('/Base/Storage/disable', {
                    id: id,
                    isDisable: isDisable
                }, function(res) {
                    if (res && 200 == res.status) {
                        $.dialog.tips('仓库' + (0 == isDisable ? '启用' : '禁用') + '成功！');
                        $('#grid').jqGrid('setCell', id, 'isDisable', isDisable);
                    } else {
                        $.dialog.tips('仓库' + (0 == isDisable ? '启用' : '禁用') + '失败！' + res.msg, 2);
                    }
                });
            }
        });
        $('.grid-wrap').on('click', '.create-partition-btn', function(a) {
            a.preventDefault();
            if (Business.verifyRight('STORAGE_UPDATE')) {
                $.dialog({
                    title: '新增分区',
                    content: 'url:/Uri/location_manage',
                    data: {
                        oper: 'add',
                        storageId: $(this).data('storageid'),
                        parentId: 0,
                        callback: THISPAGE.callback
                    },
                    width: 600,
                    height: 60,
                    max: !1,
                    min: !1,
                    lock: !0,
                    cache: !1
                });
            }
        });
        $('.grid-wrap').on('click', '.edit-partition-btn', function(a) {
            a.preventDefault();
            if (Business.verifyRight('STORAGE_UPDATE')) {
                $.dialog({
                    title: '编辑分区',
                    content: 'url:/Uri/location_manage',
                    data: {
                        oper: 'edit',
                        rowId: $(this).data('id'),
                        callback: THISPAGE.callback
                    },
                    width: 600,
                    height: 60,
                    max: !1,
                    min: !1,
                    lock: !0,
                    cache: !1
                });
            }
        });
        $('.grid-wrap').on('click', '.delete-partition-btn', function(a) {
            a.preventDefault();
            if (Business.verifyRight('STORAGE_UPDATE')) {
                var id = $(this).data('id');
                id && $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                    Public.ajaxPost('/Base/Storage/locationDelete', {
                        id: id
                    }, function(res) {
                        if (res && 200 == res.status) {
                            $.dialog.tips('删除成功！');
                            $('#grid').jqGrid().trigger('reloadGrid');
                        } else {
                            $.dialog.tips('删除失败！' + res.msg, 2);
                        }
                    });
                });
            }
        });
        $('.grid-wrap').on('click', '.create-location-btn', function(a) {
            a.preventDefault();
            if (Business.verifyRight('STORAGE_ADD')) {
                $.dialog({
                    title: '新增库位',
                    content: 'url:/Uri/location_manage',
                    data: {
                        oper: 'add',
                        storageId: $(this).data('storageid'),
                        parentId: $(this).data('parentid'),
                        callback: THISPAGE.callback
                    },
                    width: 600,
                    height: 60,
                    max: !1,
                    min: !1,
                    lock: !0,
                    cache: !1
                });
            }
        });
        $('.grid-wrap').on('click', '.each-name', function(a) {
            a.preventDefault();
            if (Business.verifyRight('STORAGE_UPDATE')) {
                $.dialog({
                    title: '编辑库位',
                    content: 'url:/Uri/location_manage',
                    data: {
                        oper: 'edit',
                        rowId: $(this).parent().data('id'),
                        callback: THISPAGE.callback
                    },
                    width: 600,
                    height: 60,
                    max: !1,
                    min: !1,
                    lock: !0,
                    cache: !1
                });
            }
        });
        $('.grid-wrap').on('click', '.each-trash', function(a) {
            a.preventDefault();
            if (Business.verifyRight('STORAGE_UPDATE')) {
                var id = $(this).parent().data('id');
                id && $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                    Public.ajaxPost('/Base/Storage/locationDelete', {
                        id: id
                    }, function(res) {
                        if (res && 200 == res.status) {
                            $.dialog.tips('删除成功！');
                            $('#grid').jqGrid().trigger('reloadGrid');
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
    },
    callback: function(a, b, c, d) {
        $('#grid').jqGrid().trigger('reloadGrid');
        d == 1 ? c.api.close() : c.THISPAGE.resetForm();
    }
};
$(function() {
    THISPAGE.init();
});
