var urlParam = Public.urlParam();
var defaultPage = Public.getDefaultPage();
var queryConditions = {
    rows: 9999,
    province: '',
    siteType: -1,
    skey: ''
};
var provinceCombo = $('#provinceCombo').combo({
    data: [
        {id: -1, name: '所有省份'},
        {id: 0, name: '北京'},
        {id: 1, name: '天津'},
        {id: 2, name: '河北'},
        {id: 3, name: '山西'},
        {id: 4, name: '内蒙'},
        {id: 5, name: '辽宁'},
        {id: 6, name: '吉林'},
        {id: 7, name: '黑龙江'},
        {id: 8, name: '上海'},
        {id: 9, name: '江苏'},
        {id: 10, name: '浙江'},
        {id: 11, name: '安徽'},
        {id: 12, name: '福建'},
        {id: 13, name: '江西'},
        {id: 14, name: '山东'},
        {id: 15, name: '河南'},
        {id: 16, name: '湖北'},
        {id: 17, name: '湖南'},
        {id: 18, name: '广东'},
        {id: 19, name: '广西'},
        {id: 20, name: '海南'},
        {id: 21, name: '重庆'},
        {id: 22, name: '四川'},
        {id: 23, name: '贵州'},
        {id: 24, name: '云南'},
        {id: 25, name: '西藏'},
        {id: 26, name: '陕西'},
        {id: 27, name: '甘肃'},
        {id: 28, name: '青海'},
        {id: 29, name: '宁夏'},
        {id: 30, name: '新疆'},
        {id: 31, name: '台湾'},
        {id: 32, name: '香港'},
        {id: 33, name: '澳门'},
        {id: 34, name: '海外'}
    ],
    value: 'id',
    text: 'name',
    defaultSelected: 0,
    width: 78,
    listHeight: 500,
    callback: {
        onListClick: function() {
            $('#searchSite').trigger('click');
        }
    }
}).getCombo();
var siteTypeCombo = $('#siteTypeCombo').combo({
    data: [
        {id: -1, name: '所有类型'},
        {id: 0, name: '招标平台'},
        {id: 1, name: '招标代理'},
        {id: 2, name: '政府主办'},
        {id: 3, name: '招标企业'},
        {id: 4, name: '学校官网'},
        {id: 5, name: '公众号'}
    ],
    value: 'id',
    text: 'name',
    defaultSelected: 0,
    width: 78,
    listHeight: 500,
    callback: {
        onListClick: function() {
            $('#searchSite').trigger('click');
        }
    }
}).getCombo();
var THISPAGE = {
    init: function() {
        THISPAGE.initKeyword();
        THISPAGE.initSite();
        THISPAGE.initEvent();
    },
    initKeyword: function() {
        Public.ajaxPost('/Base/Bid/getkeywords', {}, function(res) {
            if (res.status == 200) {
                var keywords = res.data;
                var div_key_in = '';
                var div_key_out = '';
                for (var i = 0; i < keywords.length; i++) {
                    let div_key = '<div class="each" data-id='
                        + keywords[i]['id']
                        + '><span title="编辑">'
                        + keywords[i]['name']
                        + '</span><i class="fr ui-icon-trash iconfont icon-shanchu" title="删除"></i></div>';
                    if (keywords[i]['keywordType'] == 0) {
                        div_key_in += div_key;
                    } else {
                        div_key_out += div_key;
                    }
                }
                $('#in_keywords').html(div_key_in);
                $('#out_keywords').html(div_key_out);
            } else {
                $.dialog.tips('未正确接收招标关键词数据！' + res.msg, 2);
            }
        });
    },
    initSite: function() {
        $('.bid_sites ul').html('').addClass('dn');
        Public.ajaxPost('/Base/Bid/getsites', queryConditions, function(res) {
            if (res.status == 200) {
                var sites = res.data.rows;
                $('#total').html(res.data.total);
                $('#usedTotal').html(parseInt(res.data.total) - parseInt(res.data.neverTotal));
                $('#neverTotal').html(res.data.neverTotal);
                if (sites.length > 0) {
                    var div_site_0 = '';
                    var div_site_1 = '';
                    var div_site_2 = '';
                    var div_site_3 = '';
                    var div_site_4 = '';
                    var div_site_5 = '';
                    for (var i = 0; i < sites.length; i++) {
                        let div_site = '<li data-id=' + sites[i]['id'] + '>'
                            + '<a' + (sites[i]['url'] ? ' target="_blank" href="' + sites[i]['url'] + '"' : '') + '>'
                            + (sites[i]['status'] > 0 ? '<div class="cant-bid">收录异常</div>' : '')
                            + '<div class="name">' + sites[i]['name'] + '</div>'
                            + '<div class="winRate"><div class="winRate-bar bg-aqua" style="width:' + (sites[i]['winRate'] <= 0 ? 0 : sites[i]['winRate']) +'%"></div><div class="winRate-text">' + (sites[i]['winRate'] >= 0 ? ('历史中标率' + sites[i]['winRate'] + '% ' + sites[i]['winInfo']) : '没投过') + '</div></div>'
                            + '<div class="province">' + sites[i]['province'] + '</div>'
                            + '<div class="hits">点击量 ' + sites[i]['hits'] + '</div>'
                            + '<i class="edit-site ui-icon-pencil iconfont icon-bianji" title="编辑"></i>'
                            + '</a></li>';
                        if (sites[i]['siteType'] == 0) {
                            div_site_0 += div_site;
                        } else if (sites[i]['siteType'] == 1) {
                            div_site_1 += div_site;
                        } else if (sites[i]['siteType'] == 2) {
                            div_site_2 += div_site;
                        } else if (sites[i]['siteType'] == 3) {
                            div_site_3 += div_site;
                        } else if (sites[i]['siteType'] == 4) {
                            div_site_4 += div_site;
                        } else if (sites[i]['siteType'] == 5) {
                            div_site_5 += div_site;
                        }
                    }
                    div_site_0 && $('#bid_sites_0').html(div_site_0).removeClass('dn');
                    div_site_1 && $('#bid_sites_1').html(div_site_1).removeClass('dn');
                    div_site_2 && $('#bid_sites_2').html(div_site_2).removeClass('dn');
                    div_site_3 && $('#bid_sites_3').html(div_site_3).removeClass('dn');
                    div_site_4 && $('#bid_sites_4').html(div_site_4).removeClass('dn');
                    div_site_5 && $('#bid_sites_5').html(div_site_5).removeClass('dn');
                }
            } else {
                $.dialog.tips('未正确接收招标网站列表数据！' + res.msg, 2);
            }
        });
    },
    initEvent: function() {
        $('#searchSite').on('click', function(a) {
            a.preventDefault();
            queryConditions.province = provinceCombo.getValue() == -1 ? '' : provinceCombo.getText();
            queryConditions.siteType = siteTypeCombo.getValue();
            queryConditions.skey = $.trim($('#skey').val()).split(/[ ,，]+/).toString();
            THISPAGE.initSite();
        });
        $('#refresh').on('click', function() {
            window.location.reload();
        });
        $('#addSite').on('click', function(a) {
            a.preventDefault();
            if (Business.verifyRight('BIDSITE_ADD')) {
                $.dialog({
                    title: '新增招标网站',
                    content: 'url:/Uri/bid_site_manage',
                    data: {
                        oper: 'addSite',
                        callback: THISPAGE.callback
                    },
                    width: 800,
                    height: 500,
                    max: !1,
                    min: !1,
                    cache: !1,
                    lock: !0
                });
            }
        });
        $('#addKeyword').on('click', function(a) {
            a.preventDefault();
            if (Business.verifyRight('BIDKEYWORD_ADD')) {
                $.dialog({
                    title: '新增关键词',
                    content: 'url:/Uri/bid_keyword_manage',
                    data: {
                        oper: 'addKeyword',
                        callback: THISPAGE.callback
                    },
                    width: 600,
                    height: 300,
                    max: !1,
                    min: !1,
                    cache: !1,
                    lock: !0
                });
            }
        });
        $('#in_btn').on('click', function() {
            if ($('#in_keywords').hasClass('dn')) {
                $('#in_keywords').show(200).removeClass('dn');
                $('#in_btn').html('收起');
            } else {
                $('#in_keywords').hide(200).addClass('dn');
                $('#in_btn').html('展开');
            }
        });
        $('#out_btn').on('click', function() {
            if ($('#out_keywords').hasClass('dn')) {
                $('#out_keywords').show(200).removeClass('dn');
                $('#out_btn').html('收起');
            } else {
                $('#out_keywords').hide(200).addClass('dn');
                $('#out_btn').html('展开');
            }
        });
        $('.bid_keywords').on('click', 'span', function(a) {
            a.preventDefault();
            if (Business.verifyRight('BIDKEYWORD_UPDATE')) {
                var id = $(this).parent().data('id');
                $.dialog({
                    title: '编辑关键词',
                    content: 'url:/Uri/bid_keyword_manage',
                    data: {
                        oper: 'editKeyword',
                        rowId: id,
                        callback: THISPAGE.callback
                    },
                    width: 600,
                    height: 300,
                    max: !1,
                    min: !1,
                    cache: !1,
                    lock: !0
                });
            }
        });
        $('.bid_keywords').on('click', '.ui-icon-trash', function(a) {
            a.preventDefault();
            if (Business.verifyRight('BIDKEYWORD_DELETE')) {
                var id = $(this).parent().data('id');
                $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                    Public.ajaxPost('/Base/Bid/deletekeyword', {
                        id: id
                    }, function(res) {
                        if (res && 200 == res.status) {
                            $.dialog.tips('删除成功！');
                        } else {
                            $.dialog.tips('刪除失败！' + res.msg, 2);
                        }
                        THISPAGE.initKeyword();
                    });
                });
            }
        });
        $('.bid_sites').on('click', '.ui-icon-pencil', function(a) {
            a.preventDefault();
            if (Business.verifyRight('BIDSITE_QUERY')) {
                var id = $(this).parent().parent().data('id');
                $.dialog({
                    title: '编辑招标网站',
                    content: 'url:/Uri/bid_site_manage',
                    data: {
                        oper: 'editSite',
                        rowId: id,
                        callback: THISPAGE.callback
                    },
                    width: 800,
                    height: 500,
                    max: !1,
                    min: !1,
                    cache: !1,
                    lock: !0
                });
            }
        });
        $('.bid_sites').on('click', 'li', function(a) {
            var b = $(this).data('id');
            Public.ajaxPost('/Base/Bid/addsitehits', {
                id: b
            }, function(c) {
                if (200 == c.status) {
                    var d = Number($(this).find('.hitNum').html());
                    $(this).find('.hitNum').html(d + 1);
                }
            });
        });
    },
    callback: function(a, b, c, d) {
        THISPAGE.initKeyword(), THISPAGE.initSite(), c && (d == 1 ? c.api.close() : c.THISPAGE.resetForm());
    }
};
$(function() {
    THISPAGE.init();
});
