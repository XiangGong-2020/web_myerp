var api = frameElement.api;
var urlParam = Public.urlParam();
var defaultPage = Public.getDefaultPage();
var data = api.data || {};
var isSonGoods = data.isSonGoods || !1; // 选择子产品模式
var noAdd = data.noAdd || !1; // 无新增功能模式
var skey = data.skey || ''; // 缓存的关键词
var categoryId = data.categoryId || 0; // 缓存的类别ID
var isSingle = data.isSingle || !1; // 单选模式
var queryConditions = {
    showForbidden: 0,
    searchType: 0,
    showErr: 0,
    skey: skey ? skey.split(/[ ,，]+/).toString() : '',
    categoryId: categoryId || 0
};
var addList = {};
var combo1 = $('#combo1').combo({
    data: [
        {id: -1, name: '所有状态'},
        {id: 0, name: '正常状态'},
        {id: 1, name: '未禁用'},
        {id: 2, name: '已禁用'},
        {id: 3, name: '将停产'},
        {id: 4, name: '已停产'}
    ],
    value: 'id',
    text: 'name',
    defaultSelected: 1,
    width: 78,
    listHeight: 300,
    callback: {
        onListClick: function() {
            $('#search').trigger('click');
        }
    }
}).getCombo();
var combo2 = $('#combo2').combo({
    data: [
        {id: 0, name: '模糊查询'},
        {id: 1, name: '精确查询'}
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
        THISPAGE.initDom();
        THISPAGE.initGrid();
        THISPAGE.initZtree();
        THISPAGE.initEvent();
    },
    initDom: function() {
        skey && $('#skey').val(skey);
        noAdd && $('#quickAdd').remove();
    },
    initGrid: function() {
        $('#grid').jqGrid({
            url: '/Base/Goods/list',
            postData: queryConditions,
            datatype: 'json',
            colModel: [{
                name: 'id',
                label: 'ID',
                width: 60,
                fixed: !0,
                sortable: !0,
                classes: 'gray'
            }, {
                name: 'imgsCon',
                label: '图片',
                width: 40,
                fixed: !0,
                sortable: !1,
                formatter: function(a, b, c) {
                    if (c.imgs.length > 0) {
                        var d = '<span data-id="' + c.id + '" data-imgs="' + c.imgs + '" class="ui-icon-pic rel iconfont icon-tupian f24 green poi goodsimgs" title="查看或管理图片"><span class="abs db f12 lh16 tc white bg-rgba5 b-r-3" style="min-width:25px;bottom:-2px;">' + c.imgs.split(',').length + '张</span></span>';
                    } else {
                        var d = '<span data-id="' + c.id + '" class="ui-icon-pic fl iconfont icon-tupian f24 silver poi" title="添加图片"></span>';
                    }
                    return d;
                }
            }, {
                name: 'no',
                label: '产品编码',
                width: 100,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'name',
                label: '产品名称',
                width: 100,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'spec',
                label: '规格型号',
                width: 100,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'sku',
                label: '辅助属性',
                width: 100,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'brandName',
                label: '品牌',
                width: 42,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis'
            }, {
                name: 'unitName',
                label: '单位',
                width: 42,
                fixed: !0,
                sortable: !0
            }, {
                name: 'storageName',
                label: '仓库',
                hidden: !0
            }, {
                name: 'purPrice',
                label: '预采购价',
                hidden: !0,
                formatter: function(a, b, c) {
                    return parseFloat(a);
                }
            }, {
                name: 'salePrice',
                label: '预销售价',
                hidden: !0,
                formatter: function(a, b, c) {
                    return parseFloat(a);
                }
            }, {
                name: 'currentQty',
                label: '当前库存',
                width: 66,
                fixed: !0,
                sortable: !0,
                formatter: function(a, b, c) {
                    return parseFloat(a);
                }
            }, {
                name: 'produceClass1',
                label: '',
                hidden: !0
            }, {
                name: 'produceClass2',
                label: '',
                hidden: !0
            }, {
                name: 'produceQty1',
                label: '',
                hidden: !0
            }, {
                name: 'produceQty2',
                label: '',
                hidden: !0
            }, {
                name: 'producePrice1',
                label: '',
                hidden: !0
            }, {
                name: 'producePrice2',
                label: '',
                hidden: !0
            }, {
                name: 'producePrice3',
                label: '',
                hidden: !0
            }, {
                name: 'categoryName',
                label: '类别',
                width: 60,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray'
            }, {
                name: 'storageId',
                label: '仓库ID',
                hidden: !0
            }, {
                name: 'isProofread',
                label: '校对值',
                hidden: !0
            }, {
                name: 'isDisable',
                label: '状态',
                hidden: !0
            }, {
                name: 'imgs',
                label: '状态',
                hidden: !0
            }, {
                name: 'disableStatus',
                label: '状态',
                width: 42,
                fixed: !0,
                formatter: function(a, b, c) {
                    var d = '';
                    switch (c.isDisable) {
                        case 0:
                            d = '<span class="gray">正常</span>';
                            break;
                        case 1:
                            d = '<span class="red">将停产</span>';
                            break;
                        case 2:
                            d = '<span class="red">已停产</span>';
                            break;
                        case 9:
                            d = '<span class="red">已禁用</span>';
                            break;
                        default:
                            break;
                    }
                    return d;
                }
            }, {
                name: 'proofreadStatus',
                label: '校对',
                width: 48,
                fixed: !0,
                formatter: function(a, b, c) {
                    return c.isProofread > 0 ? '<span class="gray">已校对</span>' : '<span class="red">未校对</span>';
                }
            }],
            cmTemplate: {sortable: !1, title: !1},
            jsonReader: {root: 'data.rows', records: 'data.records', total: 'data.total', repeatitems: !1, id: 'id'},
            width: 1169,
            height: 358,
            altRows: !1,
            gridview: !0,
            multiselect: !0,
            multiboxonly: isSingle ? !0 : !1,
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
                        if (data.data.rows[i].isDisable == 9) { // 已禁用 添加禁止选择属性
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
            gridComplete: function() {
                /* 翻页后，重新为选中的行设置选中状态，但不触发行选中事件 */
                for (_item in addList) {
                    $('#grid').jqGrid('setSelection', _item, !1);
                }
            },
            beforeSelectRow: function(a) {
                if (isSingle) { // 单选模式
                    $("#grid").jqGrid('resetSelection');
                }
                // 增加禁止选择效果 或 异常提醒
                var curRowData = $('#grid').jqGrid('getRowData', a);
                if (curRowData.isDisable == 9) {
                    $.dialog.alert('该产品已禁用，禁止选择！');
                    return !1;
                } else if (curRowData.isDisable == 1) {
                    $.dialog.alert('该产品将停产，请慎重选择！');
                    return !0;
                } else if (curRowData.isDisable == 2) {
                    $.dialog.alert('该产品已停产，请慎重选择！');
                    return !0;
                } else {
                    return !0;
                }
            },
            onSelectRow: function(a, b) {
                if (b) {
                    var curRowData = $('#grid').jqGrid('getRowData', a);
                    if (isSingle) { // 单选模式
                        addList = {};
                        addList[a] = curRowData;
                    } else {
                        addList[a] = curRowData;
                    }
                } else {
                    if (isSingle) { // 单选模式
                        addList = {};
                        $("#grid").jqGrid('resetSelection');
                    } else {
                        addList[a] && delete addList[a];
                    }
                }
                $('#page_left').html('<div class="fl ml10 db black">已选择<span class="ml5 mr5 pl5 pr5 white bg-green b-r-3">' + Object.keys(addList).length + '</span>行</siv>');
            },
            onSelectAll: function(a, b) {
                if (isSingle) { // 单选模式
                    addList = {};
                    $("#grid").jqGrid('resetSelection');
                } else {
                    var notNormalNum = 0;
                    for (var c = 0, d = a.length; d > c; c++) {
                        var e = a[c];
                        if (b) {
                            var f = $('#grid').jqGrid('getRowData', e);
                            if (0 < f.isDisable) {
                                notNormalNum++;
                            }
                            addList[e] = f;
                        } else {
                            addList[e] && delete addList[e];
                        }
                    }
                    if (notNormalNum > 0) {
                        $.dialog.alert('全选当中有 ' + notNormalNum + ' 个（已/将）停产产品，请慎重选择！');
                    }
                }
                $('#page_left').html('<div class="fl ml10 db black">已选择<span class="ml5 mr5 pl5 pr5 white bg-green b-r-3">' + Object.keys(addList).length + '</span>行</siv>');
            }
        });
    },
    initZtree: function() {
        Public.zTree.init($('#zTree'), {onlyposition: 1}, {
            callback: {
                onNodeCreated: function(event, treeId, treeNode) {
                    // 节点创建时检查是否需要选中
                    if (treeNode.id == categoryId) {
                        var zTree = $.fn.zTree.getZTreeObj(treeId);
                        zTree.selectNode(treeNode, false, true);
                    }
                },
                beforeClick: function(a, b) {
                    queryConditions.categoryId = b.id;
                    $('#search').trigger('click');
                }
            }
        });
    },
    initEvent: function() {
        $('#search').on('click', function() {
            parent.THISPAGE.skey = $.trim($('#skey').val()); // 缓存子产品查询关键词
            parent.THISPAGE.categoryId = queryConditions.categoryId || 0; // 缓存子产品查询类别选择
            queryConditions.showForbidden = combo1.getValue();
            queryConditions.searchType = combo2.getValue();
            queryConditions.skey = $.trim($('#skey').val()).split(/[ ,，]+/).toString();
            $('#grid').jqGrid('setGridParam', {
                postData: queryConditions,
                page: 1
            }).trigger('reloadGrid');
        });
        $('#refresh').on('click', function() {
            window.location.reload();
        });
        $('#quickAdd').on('click', function(a) {
            a.preventDefault();
            if (!Business.verifyRight('GOODS_ADD')) {
                return;
            };
            parent.$.dialog({
                title: '新增产品',
                content: 'url:/Uri/goods_manage',
                data: {
                    oper: 'add',
                    callback: function(data, oper, dialogWin) {
                        var b = $('#grid').data('gridData');
                        b || (b = {}, $('#grid').data('gridData', b));
                        data.currentQty = data.isProofread = data.isDisable = 0;
                        $('#grid').jqGrid('addRowData', data.id, data, 'first');
                        dialogWin && dialogWin.api.close();
                    }
                },
                width: $(parent.window).width() - 10,
                height: $(parent.window).height() - 100,
                left: 5,
                top: 5,
                max: !0,
                min: !0,
                lock: !1,
                cache: !1
            });
        });
        $('.grid-wrap').on('mouseenter', '.goodsimgs', function(a) {
            a.preventDefault();
            if ($(this).has('.goodsimg').length == 0) {
                var imgs = $(this).data('imgs').split(',');
                if (imgs.length > 0) {
                    var imgsDiv = '<div class="goodsimg">';
                    imgs.forEach(element => {
                        if (element.startsWith('http')) {
                            imgsDiv += '<img src="' + element + '" width="auto" height="80" class="fl m5 b-r-3">';
                        } else {
                            imgsDiv += '<img src="/data/upfile/goods/img/' + element + '" width="auto" height="80" class="fl m5 b-r-3">';
                        }
                    });
                    imgsDiv += '</div>';
                    $(this).append(imgsDiv);
                }
            };
            var top = $(this).offset().top - 38;
            $(this).children('.goodsimg').css('top', top).show();
        });
        $('.grid-wrap').on('mouseleave', '.goodsimgs', function(a) {
            a.preventDefault();
            $(this).children('.goodsimg').hide();
        });
        $('.grid-wrap').on('click', '.ui-icon-pic', function(a) {
            a.preventDefault();
            var id = $(this).data('id');
            $.dialog({
                content: 'url:/Uri/goods_img_upload',
                data: {
                    title: '产品图片',
                    id: id,
                    callback: function(a, b) {
                        $('#grid').jqGrid().trigger('reloadGrid');
                        addList = {};
                        $("#grid").jqGrid('resetSelection');
                        $('#page_left').html('<div class="fl ml10 db black">已选择<span class="ml5 mr5 pl5 pr5 white bg-green b-r-3">' + Object.keys(addList).length + '</span>行</siv>');
                        b && b.api.close();
                    }
                },
                title: '产品图片',
                width: 600,
                height: 370,
                max: !1,
                min: !1,
                lock: !0,
                cache: !1
            });
        });
        $(window).on('keydown', function(a) {
            var curKey = a.which;
            if (curKey == 13) {
                $('#search').trigger('click');
            }
        });
    },
    callback: function() {
        var a = parent.THISPAGE || api.data.page;
        var b = a.curID;
        var c = a.newId;
        var d = api.data.callback;
        var e = $('#grid').jqGrid('getGridParam', 'selarrrow');
        var f = e.length;
        var g = oldRow = parent.curRow;
        var h = parent.curCol;
        if (isSingle) { // 单选模式
            parent.$('#grid').jqGrid('restoreCell', g, h);
            var i = $('#grid').jqGrid('getRowData', $('#grid').jqGrid('getGridParam', 'selrow'));
            if (i.id = i.id.toString().split('_')[0], delete i.amount) {
                var j = i.no + ' ' + i.name + ' ' + i.spec + ' ' + i.sku;
            }
            if (g > 8 && g > oldRow) {
                var k = g;
            } else {
                var k = b;
            }
            var l = parent.$('#grid').jqGrid('getRowData', Number(b));
            l = $.extend({}, l, {
                id: i.id,
                goods: j
            });
            parent.$('#' + k).data('goodsInfo', l).data('storageInfo', {
                id: i.storageId,
                name: i.storageName
            });
            d(k, l);
        } else { // 默认多选模式
            if (f > 0) {
                parent.$('#grid').jqGrid('restoreCell', g, h);
                for (rowid in addList) {
                    var i = addList[rowid];
                    if (i.id = i.id.toString().split('_')[0], delete i.amount) {
                        var j = i.no + ' ' + i.name + ' ' + i.spec + ' ' + i.sku;
                    }
                    if (b) {
                        var k = b;
                    } else {
                        var k = c;
                    }
                    if (a.pageType) {
                        switch (a.pageType) {
                            case 'produceManageSuit':
                                i.qty = Math.abs(Number(i.currentQty));
                                break;
                            default:
                                break;
                        }
                    }
                    var l = $.extend(!0, {
                        id: k,
                        goods: j
                    }, i);
                    if (isSonGoods) {
                        l.gid = i.id;
                        l.brand = i.brandName || '';
                        l.qty1 = i.qty || 1;
                        l.unit1 = i.unitName;
                        l.qty2 = 1;
                        l.unit2 = '份';
                        l.produceClass1 = i.produceClass1;
                        l.produceClass2 = i.produceClass2;
                        l.produceQty1 = i.produceQty1;
                        l.produceQty2 = i.produceQty2;
                        l.producePrice1 = i.producePrice1;
                        l.producePrice2 = i.producePrice2;
                        l.producePrice3 = i.producePrice3;
                    }
                    if (b) {
                        var m = parent.$('#grid').jqGrid('setRowData', Number(b), {});
                    } else {
                        var m = parent.$('#grid').jqGrid('addRowData', Number(c), {}, 'last');
                        c++;
                    }
                    m && parent.$('#' + k).data('goodsInfo', l).data('storageInfo', {
                        id: i.storageId,
                        name: i.storageName
                    });
                    parent.$('#grid').jqGrid('setRowData', k, l);
                    g++;
                    var p = parent.$('#' + b).next();
                    b = p.length > 0 ? parent.$('#' + b).next().attr('id') : '';
                }
                d(c, b, g);
                $('#grid').jqGrid('resetSelection');
                addList = {};
            }
        }
        return e;
    }
};
$(function() {
    THISPAGE.init();
});
