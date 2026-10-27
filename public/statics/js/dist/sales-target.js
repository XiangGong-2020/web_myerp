var urlParam = Public.urlParam();
var defaultPage = Public.getDefaultPage();
var queryConditions = {
    year: (new Date).format('yyyy')
};
var combo1 = $('#combo1').combo({
    data: [
        {id: 0, name: '2024年'},
        {id: 1, name: '2025年'},
        {id: 2, name: '2026年'},
        {id: 3, name: '2027年'},
        {id: 4, name: '2028年'},
        {id: 5, name: '2029年'},
        {id: 6, name: '2030年'},
        {id: 7, name: '2031年'},
        {id: 8, name: '2032年'},
        {id: 9, name: '2033年'},
        {id: 10, name: '2034年'},
        {id: 11, name: '2035年'},
        {id: 12, name: '2036年'},
        {id: 13, name: '2037年'},
        {id: 14, name: '2038年'},
        {id: 15, name: '2039年'},
        {id: 16, name: '2040年'},
        {id: 17, name: '2041年'},
        {id: 18, name: '2042年'},
        {id: 19, name: '2043年'},
        {id: 20, name: '2044年'},
        {id: 21, name: '2045年'},
        {id: 22, name: '2046年'},
        {id: 23, name: '2047年'},
        {id: 24, name: '2048年'},
        {id: 25, name: '2049年'},
        {id: 26, name: '2050年'},
        {id: 27, name: '2051年'},
        {id: 28, name: '2052年'},
        {id: 29, name: '2053年'},
        {id: 30, name: '2054年'},
        {id: 31, name: '2055年'},
        {id: 32, name: '2056年'},
        {id: 33, name: '2057年'},
        {id: 34, name: '2058年'},
        {id: 35, name: '2059年'},
        {id: 36, name: '2060年'}
    ],
    value: 'id',
    text: 'name',
    defaultSelected: ['name', (new Date).format('yyyy') + '年'],
    width: 78,
    listHeight: 500,
    callback: {
        onListClick: function() {
            $('#search').trigger('click');
        }
    }
}).getCombo();
var THISPAGE = {
    init: function() {
        THISPAGE.initDom();
        THISPAGE.initEvent();
    },
    initDom: function() {
        $('#yearTargetTit').html(queryConditions.year + '年 年度销售目标');
        $('#monthTargetTit').html(queryConditions.year + '年 月度销售目标');
        $('.wrapper').append('<div id="div-lock"><div class="loading">数据加载中，请稍候...</div></div>');
        Public.ajaxGet('/Base/SalesTarget/listYear', queryConditions, function(res) {
            if (res.status === 200) {
                if (res.data) {
                    $('#yearTargetDes').html('<div class="fl">' + res.data.uName + ' ' + res.data.date + ' 制定</div>').removeClass('dn');
                    var rate = Number(res.data.curAmount / res.data.targetAmount * 100).toFixed(1);
                    $('#yearTargetCon').html(
                        '<div class="fl mt10 lh30 white bg-green b-r-3">'
                        + '<div class="fl pl10 pr10 bg-rgba1">目标金额</div>'
                        + '<div class="fl pl10 pr10 f16 fb">' + Public.numToCurrency(res.data.targetAmount) + '</div>'
                        + '</div>'
                        + '<div class="fl ml10 mt10 lh30 white bg-green b-r-3">'
                        + '<div class="fl pl10 pr10 bg-rgba1">完成金额</div>'
                        + '<div class="fl pl10 pr10">'
                        + '<div class="fl pl10 pr10 f16 fb">' + Public.numToCurrency(res.data.curAmount) + '</div>'
                        + '<div class="fr ml5 mt5 lh20 white bg-rgba2 b-r-3">'
                        + '<div class="fl pl10 pr10 bg-rgba1">特销金额</div>'
                        + '<div class="fl pl10 pr10 f14">' + Public.numToCurrency(res.data.contractAmount) + '</div>'
                        + '</div>'
                        + '<div class="fr ml5 mt5 lh20 white bg-rgba2 b-r-3">'
                        + '<div class="fl pl10 pr10 bg-rgba1">订单金额</div>'
                        + '<div class="fl pl10 pr10 f14">' + Public.numToCurrency(res.data.orderAmount) + '</div>'
                        + '</div>'
                        + '</div>'
                        + '</div>'
                        + '<div class="fl ml10 mt10 lh30 white bg-green b-r-3">'
                        + '<div class="fl pl10 pr10 bg-rgba1">完成比例</div>'
                        + '<div class="fl pl10 pr10 f16 fb">' + rate + '%</div>'
                        + '</div>'
                    );
                } else {
                    $('#yearTargetDes').html('').addClass('dn');
                    $('#yearTargetCon').html('');
                }
            } else {
                $.dialog.tips(res.msg, 2);
            }
        });
        Public.ajaxGet('/Base/SalesTarget/listMonth', queryConditions, function(res) {
            $('.wrapper > #div-lock').remove();
            if (res.status === 200) {
                if (res.data) {
                    var html = '';
                    $.each(res.data, function(k, v) {
                        var rate = Number(v.curAmount / v.targetAmount * 100).toFixed(1);
                        html += (
                            '<div class="custom-item">'
                            + '<div class="custom-item-tit">'
                            + '<div class="fl w60 f16 fb tc bg-white b-r-3">' + v.month + '月</div>'
                            + '<div class="fl ml10 pl10 pr10 fn gray bg-white b-r-3">' + v.uName + ' ' + v.date + ' 制定</div>'
                            + '</div>'
                            + '<div class="custom-item-con">'
                            + '<div class="fl mt10 lh30 white bg-teal b-r-3">'
                            + '<div class="fl pl10 pr10 bg-rgba1">目标金额</div>'
                            + '<div class="fl pl10 pr10 f16 fb">' + Public.numToCurrency(v.targetAmount) + '</div>'
                            + '</div>'
                            + '<div class="fl ml10 mt10 lh30 white bg-teal b-r-3">'
                            + '<div class="fl pl10 pr10 bg-rgba1">完成金额</div>'
                            + '<div class="fl pl10 pr10">'
                            + '<div class="fl pl10 pr10 f16 fb">' + Public.numToCurrency(v.curAmount) + '</div>'
                            + '<div class="fr ml5 mt5 lh20 white bg-rgba2 b-r-3">'
                            + '<div class="fl pl10 pr10 bg-rgba1">特销金额</div>'
                            + '<div class="fl pl10 pr10 f14">' + Public.numToCurrency(v.contractAmount) + '</div>'
                            + '</div>'
                            + '<div class="fr ml5 mt5 lh20 white bg-rgba2 b-r-3">'
                            + '<div class="fl pl10 pr10 bg-rgba1">订单金额</div>'
                            + '<div class="fl pl10 pr10 f14">' + Public.numToCurrency(v.orderAmount) + '</div>'
                            + '</div>'
                            + '</div>'
                            + '</div>'
                            + '<div class="fl ml10 mt10 lh30 white bg-teal b-r-3">'
                            + '<div class="fl pl10 pr10 bg-rgba1">完成比例</div>'
                            + '<div class="fl pl10 pr10 f16 fb">' + rate + '%</div>'
                            + '</div>'
                        );
                        var html_info = '';
                        if (v.info.length > 0) {
                            html_info += '<div class="custom-item-staff-con">';
                            $.each(v.info, function(k1, v1) {
                                var rate = v1.targetAmount == 0 ? '100' : Number(v1.curAmount / v1.targetAmount * 100).toFixed(1);
                                html_info += (
                                    '<div class="custom-item-staff-info">'
                                    + '<div class="h30 fb lh30 tc bg-teal">' + v1.salerName + '</div>'
                                    + '<div class="pt10 tc">目标金额</div>'
                                    + '<div class="pb10 f14 fb tc">' + Public.numToCurrency(v1.targetAmount) + '</div>'
                                    + '<div class="tc">完成金额</div>'
                                    + '<div class="pb10 f14 fb tc">' + Public.numToCurrency(v1.curAmount) + '</div>'
                                    + '<div class="custom-rate pct90 ma bg-white b-r-3">'
                                    + '<div class="custom-rate-bar bg-aqua b-r-3" style="width:' + rate + '%"></div>'
                                    + '<div class="custom-rate-text tc" title="已完成 ' + rate + '%">' + rate + '%</div>'
                                    + '</div>'
                                    + '</div>'
                                );
                            });
                            html_info += '</div>';
                        }
                        html += (html_info + '</div></div>');
                    });
                    $('#monthTargetCon').html(html);
                } else {
                    $('#monthTargetCon').html('');
                }
            } else {
                $.dialog.tips(res.msg, 2);
            }
        });
    },
    initEvent: function() {
        $('#search').on('click', function() {
            queryConditions.year = combo1.getText().replace('年', '');
            THISPAGE.initDom();
        });
        $('#refresh').on('click', function() {
            window.location.reload();
        });
        $('.wrapper').on('click', '#makeYearTarget', function(a) {
            a.preventDefault();
            if (Business.verifyRight('SYS_ADMIN')) {
                $.dialog({
                    title: '制定' + queryConditions.year + '年年度销售目标',
                    content: 'url:/Uri/sales_target_year_manage',
                    data: {
                        oper: 'add',
                        year: queryConditions.year,
                        callback: THISPAGE.callback
                    },
                    width: 500,
                    height: 100,
                    max: !1,
                    min: !1,
                    cache: !1,
                    lock: !0
                });
            }
        });
        $('.wrapper').on('click', '#makeMonthTarget', function(a) {
            a.preventDefault();
            if (Business.verifyRight('SALE_MANAGER')) {
                $.dialog({
                    title: '制定' + queryConditions.year + '年月度销售目标',
                    content: 'url:/Uri/sales_target_month_manage',
                    data: {
                        oper: 'add',
                        year: queryConditions.year,
                        month: (new Date).format('M'),
                        callback: THISPAGE.callback
                    },
                    width: 500,
                    height: 600,
                    max: !1,
                    min: !1,
                    cache: !1,
                    lock: !0
                });
            }
        });
    },
    callback: function(a, b, c, d) {
        $('#search').trigger('click');
        c.api.close();
    }
};
$(function() {
    THISPAGE.init();
});
