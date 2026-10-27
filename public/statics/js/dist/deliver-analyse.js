var defaultPage = Public.getDefaultPage();
var queryConditions = {
    deliverCompany: '',
    beginDate: (new Date).format('yyyy-MM') + '-01',
    endDate: (new Date).format('yyyy-MM-dd')
};
var THISPAGE = {
    init: function() {
        THISPAGE.initDom();
        THISPAGE.initData();
        THISPAGE.initEvent();
    },
    initDom: function() {
        $('#beginDate').val((new Date).format('yyyy-MM') + '-01');
        $('#endDate').val((new Date).format('yyyy-MM-dd'));
        Public.ajaxGet('/Base/DeliverCompany/queryAll', {}, function(data) {
            if (data.status === 200) {
                var deliverCompany = '';
                $.each(data.data.rows, function(i, v) {
                    if (v.isDisable == 0) {
                        deliverCompany += '<option value="' + v.id + '">' + v.name + '</option>';
                    }
                });
                /* 异步获取数据成功之后再填充选项并加载选择事件 */
                $('#deliverCompany').html(deliverCompany);
                $('#deliverCompanyCombo').fSelect({
                    placeholder: '快递公司筛选',
                    showSearch: !0,
                    width: 300,
                    listHeight: 200
                });
            } else {
                $.dialog.tips(data.msg, 2);
            }
        });
    },
    initData: function() {
        $('.diy-con').append('<div id="div-lock"><div class="loading">分析中...</div></div>');
        Public.ajaxPost('/Bill/Report/deliverAnalyse', queryConditions, function(res) {
            if (res.status == 200) {
                if (res.data.companyDataArr) {
                    var html_company = '';
                    var rank_company = 1;
                    $.each(res.data.companyDataArr, function(k, v) {
                        if (v.c > 0) {
                            html_company += '<div class="fl mr20 mb20 h30 lh30 white bg-orange ovh b-r-3"><span class="db fl w30 tc bg-rgba6" title="排名">' + rank_company + '</span><span class="db fl w70 tc bg-rgba2">' + k + '</span><span class="db fl w80 tc es">' + v.c + ' 个</span></div>';
                            rank_company += 1;
                        }
                    });
                    $('#companyData').html(html_company);
                }
                if (res.data.chTypeDataArr) {
                    var html_chType = '';
                    var rank_chType = 1;
                    $.each(res.data.chTypeDataArr, function(k, v) {
                        if (v.c > 0) {
                            html_chType += '<div class="fl mr20 mb20 h30 lh30 white bg-yellow ovh b-r-3"><span class="db fl w30 tc bg-rgba6" title="排名">' + rank_chType + '</span><span class="db fl w70 tc bg-rgba2">' + v.n + '</span><span class="db fl w80 tc es">' + v.c + ' 个</span></div>';
                            rank_chType += 1;
                        }
                    });
                    $('#chTypeData').html(html_chType);
                }
                if (res.data.weightDataArr) {
                    var html_weight = '';
                    var rank_weight = 1;
                    $.each(res.data.weightDataArr, function(k, v) {
                        if (v.c > 0) {
                            html_weight += '<div class="fl mr20 mb20 h30 lh30 white bg-gold ovh b-r-3"><span class="db fl w30 tc bg-rgba6" title="排名">' + rank_weight + '</span><span class="db fl w70 tc bg-rgba2">' + k + '</span><span class="db fl w80 tc es">' + v.c + ' 个</span></div>';
                            rank_weight += 1;
                        }
                    });
                    $('#weightData').html(html_weight);
                }
                if (res.data.packerDataArr) {
                    var html_packer = '';
                    var rank_packer = 1;
                    $.each(res.data.packerDataArr, function(k, v) {
                        if (v.totalCount > 0) {
                            html_packer += '<div class="fl mr20 mb20 lh30 white bg-green b-r-3"><span class="db fl w30 tc bg-rgba6" title="排名">' + rank_packer + '</span><span class="db fl w70 tc bg-rgba2">' + k + '</span><span class="db fl w80 tc es" title="总数量">' + v.totalCount + ' 个</span><span class="db fl w70 tc bg-rgba2" title="小于20kg">小件</span><span class="db fl w80 tc es" title="小件数量">' + v.totalCount1 + ' 个</span><span class="db fl w70 tc bg-rgba2" title="大于等于20kg">大件</span><span class="db fl w80 tc es" title="大件数量">' + v.totalCount2 + ' 个</span><span class="db fl w70 tc bg-rgba2" title="非百世快运">快递</span><span class="db fl w80 tc es" title="快递数量">' + v.totalCount3 + ' 个</span><span class="db fl w70 tc bg-rgba2" title="百世快运">物流</span><span class="db fl w80 tc es" title="物流数量">' + v.totalCount4 + ' 个</span></div>';
                            rank_packer += 1;
                        }
                    });
                    $('#packerData').html(html_packer);
                }
                if (res.data.addressDataArr) {
                    var html_address = '';
                    var rank_address = 1;
                    $.each(res.data.addressDataArr, function(k, v) {
                        if (v.c > 0) {
                            html_address += '<div class="fl mr20 mb20 h30 lh30 white bg-teal ovh b-r-3"><span class="db fl w30 tc bg-rgba6" title="排名">' + rank_address + '</span><span class="db fl w50 tc bg-rgba2">' + k + '</span><span class="db fl w80 tc es">' + v.c + ' 个</span><span class="db fl w50 tc bg-rgba2">占比</span><span class="db fl w80 tc">' + Number(v.p).toFixed(2) + ' %</span></div>';
                            rank_address += 1;
                        }
                    });
                    $('#addressData').html(html_address);
                }
            } else {
                $.dialog.tips(res.msg, 2);
                $('#companyData').html('');
                $('#chTypeData').html('');
                $('#weightData').html('');
                $('#packerData').html('');
                $('#addressData').html('');
            }
            $('.diy-con > #div-lock').remove();
        });
    },
    initEvent: function() {
        laydate.render({
            elem: "#datePicker",
            range: ['#beginDate', '#endDate'],
            done: function() {
                setTimeout(function() {
                    $('#search').trigger('click').addClass('current').siblings().removeClass('current');
                }, 300)
            }
        });
        $('.mod-search').on('click', '.fs-option, #selectAllBtn, #clearAllBtn', function() {
            setTimeout(function() {
                $('#search').trigger('click');
            }, 500);
        });
        $('#search').on('click', function() {
            queryConditions.deliverCompany = $.trim($('.fs-input').val());
            queryConditions.beginDate = $('#beginDate').val();
            queryConditions.endDate = $('#endDate').val();
            THISPAGE.initData();
        });
        $('#curMonth').on('click', function() {
            $(this).addClass('current').siblings().removeClass('current');
            $('#beginDate').val((new Date).format('yyyy-MM') + '-01');
            $('#endDate').val((new Date).format('yyyy-MM-dd'));
            $('#search').trigger('click');
        });
        $('#lastMonth').on('click', function() {
            $(this).addClass('current').siblings().removeClass('current');
            $('#beginDate').val(Public.reduceMonths((new Date).format('yyyy-MM') + '-01', 1));
            $('#endDate').val(Public.reduceDays((new Date).format('yyyy-MM') + '-01', 1));
            $('#search').trigger('click');
        });
        $('#curYear').on('click', function() {
            $(this).addClass('current').siblings().removeClass('current');
            $('#beginDate').val((new Date).format('yyyy-01-01'));
            $('#endDate').val((new Date).format('yyyy-MM-dd'));
            $('#search').trigger('click');
        });
        $('#lastYear').on('click', function() {
            $(this).addClass('current').siblings().removeClass('current');
            $('#beginDate').val(Public.reduceMonths((new Date).format('yyyy-01-01'), 12));
            $('#endDate').val(Public.reduceMonths((new Date).format('yyyy-12-31'), 12));
            $('#search').trigger('click');
        });
        $('#searchAll').on('click', function() {
            $(this).addClass('current').siblings().removeClass('current');
            $('#beginDate').val(defaultPage.SYSTEM.beginDate);
            $('#endDate').val(defaultPage.SYSTEM.endDate);
            $('#search').trigger('click');
        });
        $('#refresh').on('click', function() {
            window.location.reload();
        });
    }
};
$(function() {
    THISPAGE.init();
});
