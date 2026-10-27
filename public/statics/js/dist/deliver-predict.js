var THISPAGE = {
    init: function() {
        THISPAGE.initDom();
        THISPAGE.initEvent();
    },
    initDom: function() {
        mod_AreasCombo.init($('#province'), $('#city'), $('#area'), {listHeight: 440}, function() {
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
    initEvent: function() {
        $('#search').on('click', function(a) {
            a.preventDefault();
            var province = THISPAGE.provinceCombo.getValue();
            var city = THISPAGE.cityCombo.getValue();
            var area = THISPAGE.areaCombo.getValue();
            var address = THISPAGE.provinceCombo.getText();
            var weight = Number($('#weight').val());
            if (province == '' || weight < 0) {
                $.dialog.tips('请选择目的地，输入大于零的重量！', 2);
            } else {
                Public.ajaxPost('/Base/Tool/deliverPredict', {
                    province: province,
                    city: city,
                    area: area,
                    address: address,
                    weight: weight
                }, function(res) {
                    if (res && 200 == res.status) {
                        var predict_info = '';
                        $.each(res.data, function(k, v) {
                            predict_info += (
                                '<div class="lh40 bbe ovh"><div class="fl pct50">' +
                                '<span class="fl w100 fb">' + k + '</span>' +
                                '<span class="fl w120">运费：' + v.amount + ' 元</span>' +
                                (k == '顺丰速运' ? '<span class="fl gray">非100%准确，参考价，有各种增值费用等不确定因素。</span>' : '') +
                                (k == '邮政快递' ? '<span class="fl gray">非100%准确，参考价，有各种标准箱号等不确定因素。</span>' : '') +
                                (k == '申通快递' ? '<span class="fl gray">准确</span>' : '') +
                                (k == '百世快运' ? '<span class="fl gray">准确，但应考虑上楼费、仓储费等额外费用。</span>' : '') +
                                (k == '圆通速递' ? '<span class="fl gray">准确</span>' : '') +
                                (k == '京东快递' ? '<span class="fl gray">非100%准确，8折参考价，有特快与偏远地区等不确定因素。</span>' : '') +
                                (k == '京东重货' ? '<span class="fl gray">非100%准确，8折参考价，有偏远地区等不确定因素。</span>' : '') +
                                '</div><div class="fr pct50">' +
                                '<span class="fl w120">时效：' + (v.deliverTime > 0 ? (v.deliverTime + ' 天') : '<span class="gray">无</span>') + '</span>' +
                                '<span class="fl gray">非100%准确，' +
                                (v.deliverTime > 0 ? ((v.isLate ? '明' : '今') + '天18点前发件，预计 ' + v.recDate + ' 签收。') : '') +
                                '</span>' +
                                '</div></div>');
                        });
                        $('#predict-info').html(predict_info);
                    } else {
                        $.dialog.tips(res.msg, 2);
                    }
                });
            }
        });
        $('#showareas').on('click', function(a) {
            a.preventDefault();
            if (Business.verifyRight('AREAS_QUERY')) {
                parent.tab.addTabItem({
                    tabid: 'deliver-areas',
                    text: '<i class="iconfont icon-ditu f14 mr5"></i>全国地区',
                    url: '/Uri/areas_list'
                });
            }
        });
    }
};
$(function() {
    THISPAGE.init();
});
