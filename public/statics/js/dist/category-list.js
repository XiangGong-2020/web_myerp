var urlParam = Public.urlParam();
var defaultPage = Public.getDefaultPage();
var typeNumber = urlParam.typeNumber;
var queryConditions = {
    typeNumber: typeNumber
};
var typeObj = {
    suppliertype: '商家类别',
    customertype: '客户类别',
    goodstype: '产品类别',
    brandtype: '产品品牌',
    unittype: '计量单位',
    incometype: '收入类别',
    expendtype: '支出类别',
    settlementtype: '结算方式',
    servicetype: '反馈类别',
    departmenttype: '职能部门',
    positiontype: '部门职位',
    videotype: '视频类别'
};
var THISPAGE = {
    init: function() {
        THISPAGE.initGrid();
        THISPAGE.initEvent();
    },
    initGrid: function() {
        var a = Public.setGrid();
        $('#grid').jqGrid({
            url: '/Base/Category/list',
            postData: queryConditions,
            datatype: 'json',
            colModel: [{
                name: 'id',
                label: '<span class="ml10">ID</span>',
                width: 60,
                fixed: !0,
                classes: 'ti10 gray'
            }, {
                name: 'namePlus',
                label: '名称',
                width: 100,
                formatter: function(a, b, c) {
                    if (c.typeNumber == 'goodstype') {
                        for (var d = parseInt(c.level) - 1, e = '', f = 0; d > f; f++) {
                            e += '<span style="color:#eee;">——&nbsp;&nbsp;&nbsp;&nbsp;</span>';
                        }
                        var g = [
                            '<span class="fb black">',
                            '<span class="dark">',
                            '<span class="night">',
                            '<span class="gray">',
                            '<span class="silver">',
                        ];
                        return e + g[c.level - 1] + c.name + '</span>' + (c.onlyposition != "" ? '<span class="ml30 red">仅如下职位可见（' + c.onlyposition + '）</span>' : '');
                    } else if (c.typeNumber == 'positiontype') {
                        return '<span class="gray">' + c.parentName + '</span><span class="ml30">' + c.name + '</span>';
                    } else {
                        return c.name;
                    }
                }
            }, {
                name: 'name',
                label: '名称',
                hidden: !0
            }, {
                name: 'parentId',
                label: '父级ID',
                hidden: !0
            }, {
                name: 'parentName',
                label: '父级名称',
                hidden: !0
            }, {
                name: 'operate',
                label: '操作',
                width: 72.8,
                fixed: !0,
                formatter: Public.operFmatter
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
            scroll: !0,
            pager: '#page',
            page: 1,
            rowNum: 10000,
            viewrecords: !0,
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
            if (Business.verifyRight(typeNumber.toUpperCase() + '_ADD')) {
                $.dialog({
                    title: '新增' + typeObj[typeNumber],
                    content: 'url:/Uri/category_manage',
                    data: {
                        oper: 'add',
                        typeNumber: typeNumber,
                        callback: THISPAGE.callback
                    },
                    width: 600,
                    height: 500,
                    max: !1,
                    min: !1,
                    cache: !1,
                    lock: !0
                });
            }
        });
        $('.grid-wrap').on('click', '.ui-icon-pencil', function(a) {
            a.preventDefault();
            if (Business.verifyRight(typeNumber.toUpperCase() + '_UPDATE')) {
                var id = $(this).parent().data('id');
                var rowData = $('#grid').jqGrid('getRowData', id);
                $.dialog({
                    title: '编辑' + typeObj[typeNumber],
                    content: 'url:/Uri/category_manage',
                    data: {
                        oper: 'edit',
                        rowId: id,
                        name: rowData.name,
                        typeNumber: typeNumber,
                        typeName: typeObj[typeNumber],
                        parentId: rowData.parentId,
                        parentName: rowData.parentName,
                        callback: THISPAGE.callback
                    },
                    width: 600,
                    height: 500,
                    max: !1,
                    min: !1,
                    cache: !1,
                    lock: !0
                });
            }
        });
        $('.grid-wrap').on('click', '.ui-icon-trash', function(a) {
            a.preventDefault();
            if (Business.verifyRight(typeNumber.toUpperCase() + '_DELETE')) {
                var id = $(this).parent().data('id');
                id && $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                    Public.ajaxPost('/Base/Category/delete', {
                        id: id,
                        typeNumber: typeNumber
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
        $(window).on('resize', function() {
            Public.resizeGrid();
        });
    },
    callback: function(a, b, c, d) {
        $('#grid').jqGrid('setGridParam', {
            postData: queryConditions
        }).trigger('reloadGrid');
        c && (d == 1 ? c.api.close() : c.THISPAGE.resetForm());
    }
};
$(function() {
    THISPAGE.init();
});
