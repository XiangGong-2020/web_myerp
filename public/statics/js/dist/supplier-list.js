var defaultPage = Public.getDefaultPage();
var amountPlaces = Number(defaultPage.SYSTEM.amountPlaces);
var queryConditions = {
    categoryId: -1,
    disableType: -1,
    skey: '',
    blackList: 0
};
var categoryCombo = Business.categoryCombo($('#category'), {
    editable: !1,
    extraListHtml: '',
    addOptions: {
        value: -1,
        text: '所有商家类别'
    },
    defaultSelected: 0,
    width: 250,
    listHeight: 500,
    callback: {
        onListClick: function() {
            $('#search').trigger('click');
        }
    }
}, 'suppliertype');
var disableTypeCombo = $('#disabletype').combo({
    data: [
        {id: -1, name: '所有状态'},
        {id: 0, name: '已启用'},
        {id: 1, name: '已禁用'}
    ],
    value: 'id',
    text: 'name',
    defaultSelected: 0,
    width: 78,
    callback: {
        onListClick: function() {
            $('#search').trigger('click');
        }
    }
}).getCombo();
var THISPAGE = {
    init: function() {
        THISPAGE.initGrid();
        THISPAGE.initDom();
        THISPAGE.initEvent();
    },
    initDom: function() {
        if ($.inArray('SUPPLIER_EXPORT', defaultPage.SYSTEM.rights) !== -1) {
            $('#export').removeClass('dn');
        }
    },
    initGrid: function() {
        var a = Public.setGrid();
        $('#grid').jqGrid({
            url: '/Base/Contact/list?type=1',
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
                width: 100,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'product',
                label: '主营',
                width: 100,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'website',
                label: '网站',
                width: 72,
                fixed: !0,
                sortable: !0,
                classes: 'ui-ellipsis',
                formatter: function(c, d, e) {
                    return c ? '<a href="' + c + '" target="_blank">点击访问</a>' : '';
                }
            }, {
                name: 'contacter',
                label: '联系人',
                width: 54,
                fixed: !0,
                title: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'place',
                label: '职位',
                width: 0,
                hidden: !0
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
                fixed: !0,
                title: !0
            }, {
                name: 'mobile',
                label: '手机',
                width: 90,
                fixed: !0,
                title: !0
            }, {
                name: 'phone',
                label: '座机',
                width: 100,
                fixed: !0,
                title: !0
            }, {
                name: 'deliveryAddress',
                label: '联系地址',
                width: 100,
                title: !0,
                classes: 'ui-ellipsis',
                formatter: function(c, d, e) {
                    return (e.province || '') + (e.city || '') + (e.county || '') + (c || '');
                }
            }, {
                name: 'amount',
                label: '预付款余额',
                width: 80,
                fixed: !0,
                sortable: !0,
                formatter: 'currency',
                formatoptions: {
                    showZero: !1,
                    decimalPlaces: 2
                }
            }, {
                name: 'cLevel',
                label: '等级',
                width: 42,
                fixed: !0,
                sortable: !0,
                formatter: function(c, d, e) {
                    switch (c) {
                        case 0:
                            return '<span class="green">优秀</span>';
                        case 1:
                            return '<span class="blue">较好</span>';
                        case 2:
                            return '<span class="gray">一般</span>';
                        case 3:
                            return '<span class="yellow">较差</span>';
                        case 4:
                            return '<span class="red">恶劣</span>';
                        default:
                            return '';
                    }
                }
            }, {
                name: 'isDisable',
                label: '状态',
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
                if (data && 200 == data.status) {
                    for (var i = 0; i < data.data.rows.length; i++) {
                        if (data.data.rows[i].isDisable == 1) { //已禁用
                            $('#' + data.data.rows[i].id).addClass('ui-state-disabled');
                            $('#' + data.data.rows[i].id + ' input').attr({disabled: 'disabled'});
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
                $('#' + c).find('.ui-icon-pencil').trigger('click');
            }
        });
    },
    initEvent: function() {
        $('#search').on('click', function() {
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
            if (Business.verifyRight('SUPPLIER_ADD')) {
                var b = {
                    oper: 'add',
                    callback: THISPAGE.callback
                };
                $.dialog({
                    title: '新增商家',
                    content: 'url:/Uri/supplier_manage',
                    data: b,
                    width: 1250,
                    height: 520,
                    max: !1,
                    min: !1,
                    cache: !1,
                    lock: !0
                });
            }
        });
        $('#export').on('click', function(a) {
            if (Business.verifyRight('SUPPLIER_EXPORT')) {
                var c = $('#grid').jqGrid('getGridParam', 'selarrrow');
                var d = c.join();
                var e = d ? '&ids=' + d : '';
                queryConditions.categoryId = categoryCombo.getValue();
                queryConditions.disableType = disableTypeCombo.getValue();
                queryConditions.skey = $.trim($('#skey').val()).split(/[ ,，]+/).toString();
                for (var f in queryConditions) {
                    queryConditions[f] && (e += '&' + f + '=' + queryConditions[f]);
                }
                var g = '/Base/Contact/export?type=1' + e;
                $(this).attr('href', g);
            } else {
                return void a.preventDefault();
            }
        });
        $('.grid-wrap').on('click', '.ui-icon-pencil', function(a) {
            a.preventDefault();
            var b = $(this).parent().data('id');
            var c = {
                oper: 'edit',
                rowId: b,
                callback: THISPAGE.callback
            };
            $.dialog({
                title: '编辑商家',
                content: 'url:/Uri/supplier_manage',
                data: c,
                width: 1250,
                height: 520,
                max: !1,
                min: !1,
                cache: !1,
                lock: !0
            });
        });
        $('.grid-wrap').on('click', '.ui-icon-trash', function(a) {
            a.preventDefault();
            if (Business.verifyRight('SUPPLIER_DELETE')) {
                var id = $(this).parent().data('id');
                id && $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                    Public.ajaxPost('/Base/Contact/delete', {
                        type: 1,
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
        $('.grid-wrap').on('click', '.disable-btn', function(a) {
            a.stopPropagation();
            a.preventDefault();
            if (Business.verifyRight('SUPPLIER_DISABLE')) {
                var id = $(this).data('id');
                var isDisable = Number(!$(this).data('isdisable'));
                id && Public.ajaxPost('/Base/Contact/disable', {
                    id: id,
                    isDisable: isDisable,
                    type: 'supplier'
                }, function(res) {
                    if (res && 200 == res.status) {
                        $.dialog.tips((0 == isDisable ? '启用' : '禁用') + '成功！');
                        $('#grid').jqGrid('setCell', id, 'isDisable', isDisable);
                    } else {
                        $.dialog.tips((0 == isDisable ? '启用' : '禁用') + '失败！' + res.msg, 2);
                    }
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
