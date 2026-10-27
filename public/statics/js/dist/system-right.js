var defaultPage = Public.getDefaultPage();
var curRow;
var curCol;
var THISPAGE = {
    init: function() {
        THISPAGE.initDom();
        THISPAGE.initGrid();
        THISPAGE.initEvent();
    },
    initDom: function() {
    },
    initGrid: function() {
        var a = Public.setGrid();
        $('#grid').jqGrid({
            url: '/Base/Right/list',
            datatype: 'json',
            colModel: [{
                name: 'group',
                label: '权限组',
                width: 150,
                fixed: !0,
                editable: !0,
                classes: 'fb'
            }, {
                name: 'name',
                label: '权限名',
                width: 100,
                fixed: !0,
                editable: !0
            }, {
                name: 'module',
                label: '别名',
                width: 250,
                fixed: !0,
                editable: !0
            }, {
                name: 'parentId',
                label: '父级ID',
                width: 80,
                fixed: !0,
                editable: !0
            }, {
                name: 'id',
                label: '权限ID',
                width: 80,
                fixed: !0,
                editable: !0
            }, {
                name: 'path',
                label: '树型',
                width: 80,
                fixed: !0,
                editable: !0
            }, {
                name: 'level',
                label: '层级',
                width: 80,
                fixed: !0,
                editable: !0
            }, {
                name: 'ordnum',
                label: '排序值',
                width: 80,
                fixed: !0,
                editable: !0
            }, {
                name: 'isDisable',
                label: '状态',
                width: 100,
                formatter: function(a, b, c) {
                    var d = 1 == a ? '已禁用' : '已启用';
                    var e = 1 == a ? 'red' : 'green';
                    return '<span class="' + e + '">' + d + '</span>';
                }
            }, {
                name: 'operate',
                label: '操作',
                width: 145,
                fixed: !0,
                hidden: !0,
                formatter: function(a, b, c) {
                    var d = '<div data-id="'
                        + c.id
                        + '">'
                        + '<span class="saveBtn fl db h28 lh28 pl10 pr10 b-r-3-left hover-white">保存</span>'
                        + '<span class="enableBtn fl ml-1 db h28 lh28 pl10 pr10 hover-white">启用</span>'
                        + '<span class="disableBtn fl ml-1 db h28 lh28 pl10 pr10 b-r-3-right hover-white">禁用</span>'
                        + '</div>';
                    return d;
                }
            }],
            cmTemplate: {sortable: !1, title: !1},
            localReader: {root: 'rows', records: 'records', repeatitems: !1, id: 'id'},
            jsonReader: {root: 'data.rows', records: 'data.records', total: 'data.total', repeatitems: !1, id: 'id'},
            width: a.w,
            height: a.h,
            gridview: !0,
            rownumbers: !0,
            onselectrow: !1,
            shrinkToFit: !0,
            forceFit: !1,
            viewsortcols: [true, 'vertical', true],
            pager: '#page',
            page: 1,
            rowNum: 10000,
            viewrecords: !0,
            cellEdit: !1,
            cellsubmit: 'clientArray',
            triggerAdd: !1,
            loadComplete: function(data) {
                data.status == -1 && $.dialog.tips(data.msg, 2);
            },
            loadError: function(a, b) {
                $.dialog.tips('Type: ' + b + '; Response: ' + a.status + ' ' + a.statusText, 2);
            },
            gridComplete: function() {
                setTimeout(function() {
                    Public.autoGridWidth($('#grid'));
                }, 10);
            },
            beforeEditCell: function(rowId, cellName, cellValue, iRow, iCol) {
                curRow = iRow;
                curCol = iCol;
            },
            afterSaveCell: function(rowId, cellName, cellValue, iRow, iCol) {
                if (Business.verifyRight('SYS_RIGHT_UPDATE')) {
                    var oid = Number(rowId);
                    var rowData = $('#grid').jqGrid('getRowData', oid);
                    var name = 0 == rowData.parentId ? rowData.group : rowData.name;
                    var module = rowData.module;
                    var id = Number(rowData.id);
                    var parentId = Number(rowData.parentId);
                    var path = rowData.path;
                    var level = Number(rowData.level);
                    var ordnum = Number(rowData.ordnum);
                    if (id > 0 && name && module && id > 0 && parentId >= 0 && path && level > 0 && ordnum > 0) {
                        Public.ajaxPost('/Base/Right/update', {
                            oid: oid,
                            name: name,
                            module: module,
                            id: id,
                            parentId: parentId,
                            path: path,
                            level: level,
                            ordnum: ordnum
                        }, function(res) {
                            if (res && 200 == res.status) {
                                $.dialog.tips('权限更新成功！');
                                $(this).parent().data('id', id);
                                $(this).parents('tr').attr('id', id);
                            } else {
                                $.dialog.tips('权限更新失败！' + res.msg, 2);
                            }
                        });
                    } else {
                        $.dialog.tips('格式有误，请检查各列是否输入正确！', 2);
                    }
                }
            }
        });
    },
    initEvent: function() {
        $('#refresh').on('click', function() {
            $('#grid').jqGrid('setGridParam', {
                page: 1
            }).trigger('reloadGrid');
        });
        $('#editable').on('click', function() {
            if (Business.verifyRight('SYS_RIGHT_UPDATE')) {
                $('#grid').showCol('operate').jqGrid('setGridParam', {
                    cellEdit: !0
                }).trigger('reloadGrid');
            }
        });
        $('#diseditable').on('click', function() {
            if (Business.verifyRight('SYS_RIGHT_UPDATE')) {
                $('#grid').hideCol('operate').jqGrid('setGridParam', {
                    cellEdit: !1
                }).trigger('reloadGrid');
            }
        });
        $('.grid-wrap').on('click', '.saveBtn', function(a) {
            a.stopPropagation();
            a.preventDefault();
            if (Business.verifyRight('SYS_RIGHT_UPDATE')) {
                var oid = Number($(this).parent().data('id'));
                var rowData = $('#grid').jqGrid('getRowData', oid);
                var name = 0 == rowData.parentId ? rowData.group : rowData.name;
                var module = rowData.module;
                var id = Number(rowData.id);
                var parentId = Number(rowData.parentId);
                var path = rowData.path;
                var level = Number(rowData.level);
                var ordnum = Number(rowData.ordnum);
                if (id > 0 && name && module && id > 0 && parentId >= 0 && path && level > 0 && ordnum > 0) {
                    Public.ajaxPost('/Base/Right/update', {
                        oid: oid,
                        name: name,
                        module: module,
                        id: id,
                        parentId: parentId,
                        path: path,
                        level: level,
                        ordnum: ordnum
                    }, function(res) {
                        if (res && 200 == res.status) {
                            $.dialog.tips('权限更新成功！');
                            $(this).parent().data('id', id);
                            $(this).parents('tr').attr('id', id);
                        } else {
                            $.dialog.tips('权限更新失败！' + res.msg, 2);
                        }
                    });
                } else {
                    $.dialog.tips('格式有误，请检查各列是否输入正确！', 2);
                }
            }
        });
        $('.grid-wrap').on('click', '.enableBtn', function(a) {
            a.stopPropagation();
            a.preventDefault();
            if (Business.verifyRight('SYS_RIGHT_UPDATE')) {
                var b = $(this).parent().data('id');
                var c = $('#grid').jqGrid('getRowData', b);
                b && Public.ajaxPost('/Base/Right/disable', {
                    id: b,
                    isDisable: 0,
                    module: c.module
                }, function(res) {
                    if (res && 200 == res.status) {
                        $.dialog.tips('权限启用成功！');
                        $('#grid').jqGrid('setCell', b, 'isDisable', 0);
                    } else {
                        $.dialog.tips('权限启用失败！' + res.msg, 2);
                    }
                });
            }
        });
        $('.grid-wrap').on('click', '.disableBtn', function(a) {
            a.stopPropagation();
            a.preventDefault();
            if (Business.verifyRight('SYS_RIGHT_UPDATE')) {
                var b = $(this).parent().data('id');
                var c = $('#grid').jqGrid('getRowData', b);
                b && Public.ajaxPost('/Base/Right/disable', {
                    id: b,
                    isDisable: 1,
                    module: c.module
                }, function(res) {
                    if (res && 200 == res.status) {
                        $.dialog.tips('权限禁用成功！');
                        $('#grid').jqGrid('setCell', b, 'isDisable', 1);
                    } else {
                        $.dialog.tips('权限禁用失败！' + res.msg, 2);
                    }
                });
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
