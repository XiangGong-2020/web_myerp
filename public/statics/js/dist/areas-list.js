var curRow;
var curCol;
var queryConditions = {
    province: 0,
    city: 0,
    area: 0,
    skey: ''
};
var THISPAGE = {
    init: function() {
        THISPAGE.initGrid();
        THISPAGE.initEvent();
        mod_AreasCombo.init($('#province'), $('#city'), $('#area'), {listHeight: 500}, function() {
            THISPAGE.provinceCombo = mod_AreasCombo.provinceCombo;
            THISPAGE.cityCombo = mod_AreasCombo.cityCombo;
            THISPAGE.areaCombo = mod_AreasCombo.areaCombo;
            THISPAGE.provinceCombo.selectByText('');
            THISPAGE.cityCombo.selectByText('');
            THISPAGE.areaCombo.selectByText('');
            $('#province').find('input').attr('name', 'provinceInput');
            $('#city').find('input').attr('name', 'cityInput');
            $('#area').find('input').attr('name', 'areaInput');
        });
    },
    initGrid: function() {
        var a = Public.setGrid();
        $('#grid').jqGrid({
            url: '/Base/Areas/list',
            postData: queryConditions,
            datatype: 'json',
            colModel: [{
                name: 'id',
                label: '<span class="ml10">ID</span>',
                width: 70,
                fixed: !0,
                sortable: !0,
                classes: 'ti10 gray'
            }, {
                name: 'name',
                label: '名称',
                width: 200,
                fixed: !0,
                sortable: !0
            }, {
                name: 'type',
                label: '类别',
                width: 60,
                fixed: !0,
                sortable: !0,
                classes: 'gray',
                formatter: function(a, b, c) {
                    return a == 1 ? '<span class="red">国家</span>' : (a == 2 ? '<span class="yellow">省级</span>' : (a == 3 ? '<span class="blue">地级</span>' : '<span class="green">县级</span>'));
                }
            }, {
                name: 'parentId',
                label: '父级ID',
                hidden: !0
            }, {
                name: 'parentName',
                label: '父级',
                width: 200,
                fixed: !0,
                title: !0,
                classes: 'ui-ellipsis gray'
            }, {
                name: 'deliverTimeSF',
                label: '顺丰速运时效',
                width: 100,
                fixed: !0,
                sortable: !0,
                editable: !0,
                formatter: function(a, b, c) {
                    return a == 0 ? '' : a;
                }
            }, {
                name: 'deliverTimeYZ',
                label: '邮政快递时效',
                width: 100,
                fixed: !0,
                sortable: !0,
                editable: !0,
                formatter: function(a, b, c) {
                    return a == 0 ? '' : a;
                }
            }, {
                name: 'deliverTimeJD',
                label: '京东快递时效',
                width: 100,
                fixed: !0,
                sortable: !0,
                editable: !0,
                formatter: function(a, b, c) {
                    return a == 0 ? '' : a;
                }
            }, {
                name: 'deliverTimeST',
                label: '申通快递时效',
                width: 100,
                fixed: !0,
                sortable: !0,
                editable: !0,
                formatter: function(a, b, c) {
                    return a == 0 ? '' : a;
                }
            }, {
                name: 'deliverTimeYT',
                label: '圆通速递时效',
                width: 100,
                fixed: !0,
                sortable: !0,
                editable: !0,
                formatter: function(a, b, c) {
                    return a == 0 ? '' : a;
                }
            }, {
                name: 'deliverTimeBS',
                label: '百世快运时效',
                width: 100,
                fixed: !0,
                sortable: !0,
                editable: !0,
                formatter: function(a, b, c) {
                    return a == 0 ? '' : a;
                }
            }, {
                name: 'deliverTimeJDZH',
                label: '京东重货时效',
                width: 100,
                sortable: !0,
                editable: !0,
                formatter: function(a, b, c) {
                    return a == 0 ? '' : a;
                }
            }, {
                name: 'operate',
                label: '操作',
                width: 80,
                fixed: !0,
                hidden: !0,
                align: 'center',
                formatter: function(a, b, c) {
                    var d = '<div data-id="' + c.id + '">' +
                        '<span class="saveBtn db fl pl10 pr10 h28 lh28 b-r-3 hover-white">保存</span>' +
                        '</div>';
                    return d;
                }
            }],
            cmTemplate: {sortable: !1, title: !1},
            jsonReader: {root: 'data.rows', records: 'data.records', total: 'data.total', repeatitems: !1, id: 'id'},
            width: a.w,
            height: a.h,
            altRows: !1,
            gridview: !0,
            shrinkToFit: !0,
            forceFit: !1,
            viewsortcols: [true, 'vertical', true],
            pager: '#page',
            page: 1,
            rowNum: 100,
            rowList: [100, 200, 500],
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
                if ('deliverTimeYZ' == cellName || 'deliverTimeSF' == cellName || 'deliverTimeST' == cellName || 'deliverTimeYT' == cellName || 'deliverTimeJD' == cellName || 'deliverTimeBS' == cellName || 'deliverTimeJDZH' == cellName) {
                    var rowData = $('#grid').jqGrid('getRowData', rowId);
                    if (rowData) {
                        Public.ajaxPost('/Base/Areas/update', {
                            id: rowId,
                            name: rowData.name,
                            deliverTimeYZ: rowData.deliverTimeYZ || 0,
                            deliverTimeSF: rowData.deliverTimeSF || 0,
                            deliverTimeJD: rowData.deliverTimeJD || 0,
                            deliverTimeST: rowData.deliverTimeST || 0,
                            deliverTimeYT: rowData.deliverTimeYT || 0,
                            deliverTimeBS: rowData.deliverTimeBS || 0,
                            deliverTimeJDZH: rowData.deliverTimeJDZH || 0
                        }, function(res) {
                            if (res && 200 == res.status) {
                                $.dialog.tips('更新成功！');
                            } else {
                                $.dialog.tips('更新失败！' + res.msg, 2);
                            }
                        });
                    } else {
                        $.dialog.tips('行数据获取失败！', 2);
                    }
                }
            }
        });
    },
    initEvent: function() {
        $('#search').on('click', function(a) {
            a.preventDefault();
            queryConditions.province = THISPAGE.provinceCombo.getValue();
            queryConditions.city = THISPAGE.cityCombo.getValue();
            queryConditions.area = THISPAGE.areaCombo.getValue();
            queryConditions.skey = $.trim($('#skey').val()).split(/[ ,，]+/).toString();
            $('#grid').jqGrid('setGridParam', {
                postData: queryConditions,
                page: 1
            }).trigger('reloadGrid');
        });
        $('#refresh').on('click', function() {
            window.location.reload();
        });
        $('#editable').on('click', function() {
            if (Business.verifyRight('AREAS_ADVANCE')) {
                $('#grid').showCol('operate').jqGrid('setGridParam', {
                    cellEdit: !0
                }).trigger('reloadGrid');
            }
        });
        $('#diseditable').on('click', function() {
            if (Business.verifyRight('AREAS_ADVANCE')) {
                $('#grid').hideCol('operate').jqGrid('setGridParam', {
                    cellEdit: !1
                }).trigger('reloadGrid');
            }
        });
        $('#fillTimeFromCity').on('click', function() {
            if (Business.verifyRight('AREAS_ADVANCE')) {
                $('.wrapper').append('<div id="div-lock"><div class="loading">自动填充中...</div></div>');
                Public.ajaxPost('/Base/Areas/fillTimeFromCity', {}, function(res) {
                    $('.wrapper > #div-lock').remove();
                    if (res && 200 == res.status) {
                        $.dialog.tips('填充成功！');
                        $('#search').trigger('click');
                    } else {
                        $.dialog.tips('填充失败！' + res.msg, 2);
                    }
                });
            }
        });
        $('.grid-wrap').on('keypress', '.textbox', Public.numerical).on('focus', '.textbox', function() {
            this.value = Public.currencyToNum(this.value || '');
            $(this).select();
        }).on('blur', '.textbox', function() {
            this.value = Public.currencyToNum(this.value || '');
            curRow && curCol && ($('#grid').jqGrid('saveCell', curRow, curCol), curRow = curCol = null);
        });
        $('.grid-wrap').on('click', '.saveBtn', function(a) {
            a.stopPropagation();
            a.preventDefault();
            if (Business.verifyRight('AREAS_ADVANCE')) {
                var id = Number($(this).parent().data('id'));
                var rowData = $('#grid').jqGrid('getRowData', id);
                if (rowData) {
                    Public.ajaxPost('/Base/Areas/update', {
                        id: id,
                        name: rowData.name,
                        deliverTimeYZ: rowData.deliverTimeYZ || 0,
                        deliverTimeSF: rowData.deliverTimeSF || 0,
                        deliverTimeJD: rowData.deliverTimeJD || 0,
                        deliverTimeST: rowData.deliverTimeST || 0,
                        deliverTimeYT: rowData.deliverTimeYT || 0,
                        deliverTimeBS: rowData.deliverTimeBS || 0,
                        deliverTimeJDZH: rowData.deliverTimeJDZH || 0
                    }, function(res) {
                        if (res && 200 == res.status) {
                            $.dialog.tips('更新成功！');
                        } else {
                            $.dialog.tips('更新失败！' + res.msg, 2);
                        }
                    });
                } else {
                    $.dialog.tips('行数据获取失败！', 2);
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
    }
};
$(function() {
    THISPAGE.init();
});
