var urlParam = Public.urlParam();
var defaultPage = Public.getDefaultPage();
var queryConditions = {
    noDevOrSerName: urlParam.noDevOrSerName || 0,
    categoryId: urlParam.categoryId || 0,
    showForbidden: urlParam.showForbidden || 0,
    showErr: urlParam.showErr || 0,
    searchType: 0,
    showKitHasKit: 0,
    skey: ''
};
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
    width: 78,
    listHeight: 300,
    defaultSelected: urlParam.showForbidden ? ['id', parseInt(urlParam.showForbidden)] : 1,
    callback: {
        onListClick: function() {
            $('#search').parent().find('.current').trigger('click');
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
            $('#search').parent().find('.current').trigger('click');
        }
    }
}).getCombo();
var gh = 90;
var gw = 81;
var THISPAGE = {
    init: function() {
        THISPAGE.initGrid();
        THISPAGE.initTree();
        THISPAGE.initEvent();
    },
    initGrid: function() {
        var a = Public.setGrid(gh, gw);
        $('#grid').jqGrid({
            url: '/Base/Goods/list',
            datatype: 'json',
            postData: queryConditions,
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
                sortable: !0,
            }, {
                name: 'weight',
                label: '重量(kg)',
                width: 66,
                fixed: !0,
                sortable: !0
            }, {
                name: 'currentQty',
                label: '当前库存',
                width: 66,
                fixed: !0,
                sortable: !0
            }, {
                name: 'purPrice',
                label: '预采购价',
                width: 66,
                fixed: !0,
                hidden: $.inArray('PO_ADD', defaultPage.SYSTEM.rights) !== -1 ? !1 : !0,
                sortable: !0
            }, {
                name: 'salePrice',
                label: '预销售价',
                width: 66,
                fixed: !0,
                hidden: $.inArray('SO_ADD', defaultPage.SYSTEM.rights) !== -1 ? !1 : !0,
                sortable: !0
            }, {
                name: 'description',
                label: '备注',
                width: 70,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray'
            }, {
                name: 'categoryName',
                label: '类别',
                width: 70,
                fixed: !0,
                title: !0,
                sortable: !0,
                classes: 'ui-ellipsis gray'
            }, {
                name: 'isDisable',
                label: '状态',
                width: 54,
                fixed: !0,
                sortable: !0,
                align: 'center',
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
                name: 'isProofread',
                label: '校对',
                width: 54,
                fixed: !0,
                sortable: !0,
                align: 'center',
                formatter: function(a, b, c) {
                    return c.isProofread > 0 ? '<span class="gray">已校对</span>' : '<span class="red">未校对</span>';
                }
            }, {
                name: 'devName',
                label: '开发人',
                width: 54,
                fixed: !0,
                hidden: $.inArray('SO_ADD', defaultPage.SYSTEM.rights) !== -1 ? !1 : !0,
                sortable: !0
            }, {
                name: 'serName',
                label: '维护人',
                width: 54,
                fixed: !0,
                hidden: $.inArray('SO_ADD', defaultPage.SYSTEM.rights) !== -1 ? !1 : !0,
                sortable: !0
            }, {
                name: 'proName',
                label: '最近配货人',
                width: 78,
                fixed: !0,
                hidden: $.inArray('SO_ADD', defaultPage.SYSTEM.rights) !== -1 ? !1 : !0,
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
            width: a.w,
            height: a.h,
            altRows: !1,
            gridview: !0,
            multiselect: !0,
            multiboxonly: !1,
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
                $('.wrapper > #div-lock').remove();
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
    initTree: function() {
        Public.zTree.init($('#zTree'), {onlyposition: 1}, {
            callback: {
                beforeClick: function(a, b) {
                    queryConditions.categoryId = b.id;
                    $('#search').parent().find('.current').trigger('click');
                }
            }
        });
    },
    initEvent: function() {
        $('#search').on('click', function() {
            $(this).addClass('current').siblings().removeClass('current');
            queryConditions.showForbidden = combo1.getValue();
            queryConditions.searchType = combo2.getValue();
            queryConditions.showErr = 0;
            queryConditions.showKitHasKit = 0;
            queryConditions.skey = $.trim($('#skey').val()).split(/[ ,，]+/).toString();
            $('#grid').jqGrid('setGridParam', {
                postData: queryConditions,
                page: 1
            }).trigger('reloadGrid');
        });
        $('#searchErr').on('click', function() {
            $(this).addClass('current').siblings().removeClass('current');
            queryConditions.showForbidden = combo1.getValue();
            queryConditions.searchType = combo2.getValue();
            queryConditions.showErr = 1;
            queryConditions.showKitHasKit = 0;
            queryConditions.skey = $.trim($('#skey').val()).split(/[ ,，]+/).toString();
            $('#grid').jqGrid('setGridParam', {
                postData: queryConditions,
                page: 1
            }).trigger('reloadGrid');
        });
        $('#searchKit2Level').on('click', function() {
            $(this).addClass('current').siblings().removeClass('current');
            queryConditions.showForbidden = combo1.getValue();
            queryConditions.searchType = combo2.getValue();
            queryConditions.showErr = 0;
            queryConditions.showKitHasKit = 2;
            queryConditions.skey = $.trim($('#skey').val()).split(/[ ,，]+/).toString();
            $('#grid').jqGrid('setGridParam', {
                postData: queryConditions,
                page: 1
            }).trigger('reloadGrid');
            $('.wrapper').append('<div id="div-lock"><div class="loading">包含子套件的套件查询中...</div></div>');
            $('.ui-jqgrid .loading').hide();
        });
        $('#refresh').on('click', function() {
            window.location.reload();
        });
        $('#add').on('click', function(a) {
            a.preventDefault();
            if (Business.verifyRight('GOODS_ADD')) {
                $.dialog({
                    title: '新增产品',
                    content: 'url:/Uri/goods_manage',
                    data: {
                        oper: 'add',
                        callback: THISPAGE.callback
                    },
                    width: $(window).width() - 10,
                    height: $(window).height() - 100,
                    left: 5,
                    top: 5,
                    max: !0,
                    min: !0,
                    lock: !0,
                    cache: !1
                });
            }
        });
        $('#export').on('click', function(a) {
            if (Business.verifyRight('GOODS_EXPORT')) {
                queryConditions.showForbidden = combo1.getValue();
                queryConditions.searchType = combo2.getValue();
                queryConditions.showErr = $('.ui-btn.current').text() == '查询' ? 0 : 1;
                queryConditions.skey = $.trim($('#skey').val()).split(/[ ,，]+/).toString();
                var c = $('#grid').jqGrid('getGridParam', 'selarrrow');
                var d = c.join();
                var e = d ? ('&id=' + d) : '';
                for (var f in queryConditions) {
                    queryConditions[f] && (e += '&' + f + '=' + queryConditions[f]);
                }
                var g = '/Base/Goods/export?action=export' + e;
                $(this).attr('href', g);
            } else {
                return void a.preventDefault();
            }
        });
        $('#printTab').on('click', function(a) {
            a.preventDefault();
            if (Business.verifyRight('GOODS_PRINT')) {
                var b = $('#grid').jqGrid('getGridParam', 'selarrrow');
                if (b.length > 0) {
                    for (var i = 0; i < b.length; i++) {
                        var c = $('#grid').jqGrid('getRowData', b[i]);
                        $('.print-area').append(
                            '<div class="print-con">' +
                            '<div class="no">' + c.no + '</div>' +
                            '<div class="name">' + c.name + (c.spec ? ' . ' + c.spec : '') + (c.sku ? ' . ' + c.sku : '') + (c.brandName ? ' . ' + c.brandName : '') + '</div>' +
                            '<div id="qrcode' + i + '" class="qrcode"></div>' +
                            '</div>'
                        );
                        $('#qrcode' + i).qrcode({
                            render: "table",
                            width: 220,
                            height: 220,
                            text: c.no
                        });
                    };
                    /* 开始处理打印数据 */
                    /* 获取处理后的html代码 */
                    var bdhtml = window.document.body.innerHTML;
                    /* 设置打印开始区域 */
                    var sprnstr = '<!--startprint1-->';
                    /* 设置打印结束区域 */
                    var eprnstr = '<!--endprint1-->';
                    /* 从开始代码向后取html */
                    var prnhtml = bdhtml.substring(bdhtml.indexOf(sprnstr) + 18);
                    /* 从结束代码向前取html */
                    prnhtml = prnhtml.substring(0, prnhtml.indexOf(eprnstr));
                    window.document.body.innerHTML = prnhtml;
                    window.print();
                    /* 将页面恢复成打印前状态 */
                    window.location.reload();
                } else {
                    $.dialog.tips('请先勾选需要打印标签的产品（可多选）！', 2);
                };
            }
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
                title: '产品图片',
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
                width: 600,
                height: 370,
                max: !1,
                min: !1,
                lock: !0,
                cache: !1
            });
        });
        $('.grid-wrap').on('click', '.ui-icon-pencil', function(a) {
            a.preventDefault();
            var id = $(this).parent().data('id');
            $.dialog({
                title: '编辑产品',
                content: 'url:/Uri/goods_manage',
                data: {
                    oper: 'edit',
                    rowId: id,
                    callback: THISPAGE.callback
                },
                width: $(window).width() - 10,
                height: $(window).height() - 100,
                left: 5,
                top: 5,
                max: !0,
                min: !0,
                lock: !0,
                cache: !1
            });
        });
        $('.grid-wrap').on('click', '.ui-icon-trash', function(a) {
            a.preventDefault();
            if (Business.verifyRight('GOODS_DELETE')) {
                var id = $(this).parent().data('id');
                id && $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                    Public.ajaxPost('/Base/Goods/delete', {
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
        $(window).on('keydown', function(a) {
            var curKey = a.which;
            if (curKey == 13) {
                $('#search').parent().find('.current').trigger('click');
            }
        });
        $(window).on('resize', function() {
            Public.resizeGrid(gh, gw);
            $('.innerTree').height($('.ztree').height() - 4);
        });
        Public.setAutoHeight($('.ztree'));
        $('.innerTree').height($('.ztree').height() - 4);
    },
    callback: function(a, b, c, d) {
        $('#search').parent().find('.current').trigger('click'), c && (d == 1 ? c.api.close() : c.THISPAGE.resetForm());
    }
};
$(function() {
    THISPAGE.init();
});
