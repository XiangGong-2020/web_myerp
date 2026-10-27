var defaultPage = Public.getDefaultPage();
var queryConditions = {
    type: 0,
    beginDate: '',
    endDate: '',
    skey: ''
};
var goodsShowAll = !1;
var logtypeCombo;
var THISPAGE = {
    init: function() {
        THISPAGE.initDom();
        THISPAGE.initGrid();
        THISPAGE.initEvent();
    },
    initDom: function() {
        $('#beginDate').val(defaultPage.SYSTEM.beginDate + ' 00:00:00');
        $('#endDate').val(defaultPage.SYSTEM.endDate + ' 23:59:59');
        logtypeCombo = $('#logtype').combo({
            data: [
                {id: 0, name: '所有类别'},
                {id: 1, name: '装箱清单'},
                {id: 2, name: '发货标签'}
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
    },
    initGrid: function() {
        var a = Public.setGrid();
        $('#grid').jqGrid({
            url: '/Base/PrintTemplate/list',
            postData: queryConditions,
            datatype: 'json',
            colModel: [{
                name: 'createTime',
                label: '<span class="ml10">打印时间</span>',
                width: 130,
                fixed: !0,
                classes: 'ti10'
            }, {
                name: 'name',
                label: '打印人',
                width: 54,
                fixed: !0
            }, {
                name: 'type',
                label: '打印类别',
                width: 66,
                fixed: !0,
                formatter: function(a, b, c) {
                    var d = 1 == a ? '装箱清单' : '发货标签';
                    return d;
                }
            }, {
                name: 'goods',
                label: '包含产品',
                nameExt: '<span class="goods-showall"><i class="iconfont icon-shuangxiajiantou f12 mr5"></i>展开全部</span>',
                width: 100,
                sortable: !1,
                formatter: function(a, b, c) {
                    a = JSON.parse(a);
                    var d = (0 < a.length ? a.length : 0);
                    var e = '';
                    var f = 0;
                    if (0 < d) {
                        $.each(a, function(i, v) {
                            var goodsText =
                                v['goods'] +
                                (v['brand'] ? ' ★ ' + v['brand'] : '') +
                                (v['qty'] ? ' × ' + v['qty'] : '') +
                                (v['unit'] ? v['unit'] : '') +
                                (v['des'] ? ' 备注：' + v['des'] : '');
                            e += ('<div class="goods-line es" title="' + goodsText + '">' + goodsText + '</div>');
                            f += parseInt(v['qty'].replace(/[^0-9]/ig, ""));
                        });
                    }
                    return (
                        '<div class="goods-total"><span class="goods-num-1">' +
                        d +
                        ' 种</span><span class="goods-num-2">' +
                        f +
                        ' 件</span><span class="goods-num-3"></span><span class="goods-showinfo iconfont icon-xiajiantou"></span></div><div class="goods-info">' +
                        e +
                        '</div>'
                    );
                }
            }, {
                name: 'billNo',
                label: '单据编号',
                width: 160,
                fixed: !0
            }, {
                name: 'contactName',
                label: '客户信息',
                width: 200,
                fixed: !0,
                classes: 'ui-ellipsis'
            }],
            cmTemplate: {sortable: !0, title: !0},
            jsonReader: {root: 'data.rows', records: 'data.records', total: 'data.total', repeatitems: !1, id: 'id'},
            autowidth: !0,
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
            loadComplete: function(b) {
                if (goodsShowAll) {
                    $('.grid-wrap .goods-showall').html('<i class="iconfont icon-shuangshangjiantou f12 mr5"></i>收缩全部');
                    $('.grid-wrap .goods-showinfo').removeClass('icon-xiajiantou').addClass('icon-shangjiantou');
                    $('.grid-wrap .goods-info').show();
                } else {
                    $('.grid-wrap .goods-showall').html('<i class="iconfont icon-shuangxiajiantou f12 mr5"></i>展开全部');
                    $('.grid-wrap .goods-showinfo').removeClass('icon-shangjiantou').addClass('icon-xiajiantou');
                    $('.grid-wrap .goods-info').hide();
                }
            },
            loadError: function(a, b) {
                $.dialog.tips('Type: ' + b + '; Response: ' + a.status + ' ' + a.statusText, 2);
            }
        });
    },
    initEvent: function() {
        laydate.render({
            elem: "#datePicker",
            type: 'datetime',
            range: ['#beginDate', '#endDate'],
            done: function() {
                setTimeout(function() {
                    $('#search').trigger('click');
                }, 300)
            }
        });
        $('#search').on('click', function(a) {
            a.preventDefault();
            queryConditions.type = logtypeCombo ? logtypeCombo.getValue() : 0;
            queryConditions.beginDate = $('#beginDate').val() ? $.trim($('#beginDate').val()) : '';
            queryConditions.endDate = $('#endDate').val() ? $.trim($('#endDate').val()) : '';
            queryConditions.skey = ($('#skey').val() === $('#skey')[0].defaultValue ? '' : $.trim($('#skey').val()).split(/[ ,，]+/).toString());
            $('#grid').jqGrid('setGridParam', {
                postData: queryConditions,
                page: 1
            }).trigger('reloadGrid');
        });
        $('#refresh').on('click', function() {
            window.location.reload();
        });
        $('.grid-wrap').on('click', '.goods-showinfo', function(a) {
            if ($(this).hasClass('icon-xiajiantou')) {
                $(this).parent().next().show();
                $(this).removeClass('icon-xiajiantou').addClass('icon-shangjiantou');
            } else {
                $(this).parent().next().hide();
                $(this).removeClass('icon-shangjiantou').addClass('icon-xiajiantou');
            }
        });
        $('.goods-showall').on('click', function() {
            if (goodsShowAll) {
                goodsShowAll = !1;
                $('.grid-wrap .goods-showall').html('<i class="iconfont icon-shuangxiajiantou f12 mr5"></i>展开全部');
                $('.grid-wrap .goods-showinfo').removeClass('icon-shangjiantou').addClass('icon-xiajiantou');
                $('.grid-wrap .goods-info').hide();
            } else {
                goodsShowAll = !0;
                $('.grid-wrap .goods-showall').html('<i class="iconfont icon-shuangshangjiantou f12 mr5"></i>收缩全部');
                $('.grid-wrap .goods-showinfo').removeClass('icon-xiajiantou').addClass('icon-shangjiantou');
                $('.grid-wrap .goods-info').show();
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
