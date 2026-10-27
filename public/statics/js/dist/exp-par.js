var defaultPage = Public.getDefaultPage();
var THISPAGE = {
    init: function() {
        THISPAGE.initDom();
        THISPAGE.initEvent();
    },
    initDom: function() {
        $('#proKitting-par1').val(defaultPage.SYSTEM.expPar.proKitting ? defaultPage.SYSTEM.expPar.proKitting.par1 : '');
        $('#proKitting-par2').val(defaultPage.SYSTEM.expPar.proKitting ? defaultPage.SYSTEM.expPar.proKitting.par2 : '');
        $('#proKitting-par3').val(defaultPage.SYSTEM.expPar.proKitting ? defaultPage.SYSTEM.expPar.proKitting.par3 : '');
        $('#proKitting-achievementSalaryRate').val(defaultPage.SYSTEM.expPar.proKitting ? defaultPage.SYSTEM.expPar.proKitting.achievementSalaryRate : '');

        $('#proProcessing-par1').val(defaultPage.SYSTEM.expPar.proProcessing ? defaultPage.SYSTEM.expPar.proProcessing.par1 : '');
        $('#proProcessing-par2').val(defaultPage.SYSTEM.expPar.proProcessing ? defaultPage.SYSTEM.expPar.proProcessing.par2 : '');
        $('#proProcessing-par3').val(defaultPage.SYSTEM.expPar.proProcessing ? defaultPage.SYSTEM.expPar.proProcessing.par3 : '');
        $('#proProcessing-par4').val(defaultPage.SYSTEM.expPar.proProcessing ? defaultPage.SYSTEM.expPar.proProcessing.par4 : '');
        $('#proProcessing-par5').val(defaultPage.SYSTEM.expPar.proProcessing ? defaultPage.SYSTEM.expPar.proProcessing.par5 : '');
        $('#proProcessing-achievementSalaryRate').val(defaultPage.SYSTEM.expPar.proProcessing ? defaultPage.SYSTEM.expPar.proProcessing.achievementSalaryRate : '');

        $('#pack-par1').val(defaultPage.SYSTEM.expPar.pack ? defaultPage.SYSTEM.expPar.pack.par1 : '');
        $('#pack-par2').val(defaultPage.SYSTEM.expPar.pack ? defaultPage.SYSTEM.expPar.pack.par2 : '');
        $('#pack-par3').val(defaultPage.SYSTEM.expPar.pack ? defaultPage.SYSTEM.expPar.pack.par3 : '');
        $('#pack-par4').val(defaultPage.SYSTEM.expPar.pack ? defaultPage.SYSTEM.expPar.pack.par4 : '');
        $('#pack-par5').val(defaultPage.SYSTEM.expPar.pack ? defaultPage.SYSTEM.expPar.pack.par5 : '');
        $('#pack-par6').val(defaultPage.SYSTEM.expPar.pack ? defaultPage.SYSTEM.expPar.pack.par6 : '');
        $('#pack-achievementSalaryRate').val(defaultPage.SYSTEM.expPar.pack ? defaultPage.SYSTEM.expPar.pack.achievementSalaryRate : '');

        $('#sale-par1').val(defaultPage.SYSTEM.expPar.sale ? defaultPage.SYSTEM.expPar.sale.par1 : '');
        $('#sale-par2').val(defaultPage.SYSTEM.expPar.sale ? defaultPage.SYSTEM.expPar.sale.par2 : '');
        $('#sale-par3').val(defaultPage.SYSTEM.expPar.sale ? defaultPage.SYSTEM.expPar.sale.par3 : '');
        $('#sale-achievementSalaryRate').val(defaultPage.SYSTEM.expPar.sale ? defaultPage.SYSTEM.expPar.sale.achievementSalaryRate : '');

        $('#pur-par1').val(defaultPage.SYSTEM.expPar.pur ? defaultPage.SYSTEM.expPar.pur.par1 : '');
        $('#pur-par2').val(defaultPage.SYSTEM.expPar.pur ? defaultPage.SYSTEM.expPar.pur.par2 : '');
        $('#pur-par3').val(defaultPage.SYSTEM.expPar.pur ? defaultPage.SYSTEM.expPar.pur.par3 : '');
        $('#pur-par4').val(defaultPage.SYSTEM.expPar.pur ? defaultPage.SYSTEM.expPar.pur.par4 : '');
        $('#pur-achievementSalaryRate').val(defaultPage.SYSTEM.expPar.pur ? defaultPage.SYSTEM.expPar.pur.achievementSalaryRate : '');
    },
    initEvent: function() {
        $('#refresh').on('click', function() {
            window.location.reload();
        });
        $('.save-btn').on('click', function() {
            if (Business.verifyRight('EXP_PAR_UPDATE')) {
                var a1 = Number($.trim($('#proKitting-par1').val()));
                var a2 = Number($.trim($('#proKitting-par2').val()));
                var a3 = Number($.trim($('#proKitting-par3').val()));
                var a4 = Number($.trim($('#proKitting-achievementSalaryRate').val()));

                var b1 = Number($.trim($('#proProcessing-par1').val()));
                var b2 = Number($.trim($('#proProcessing-par2').val()));
                var b3 = Number($.trim($('#proProcessing-par3').val()));
                var b4 = Number($.trim($('#proProcessing-par4').val()));
                var b5 = Number($.trim($('#proProcessing-par5').val()));
                var b6 = Number($.trim($('#proProcessing-achievementSalaryRate').val()));

                var c1 = Number($.trim($('#pack-par1').val()));
                var c2 = Number($.trim($('#pack-par2').val()));
                var c3 = Number($.trim($('#pack-par3').val()));
                var c4 = Number($.trim($('#pack-par4').val()));
                var c5 = Number($.trim($('#pack-par5').val()));
                var c6 = Number($.trim($('#pack-par6').val()));
                var c7 = Number($.trim($('#pack-achievementSalaryRate').val()));

                var d1 = Number($.trim($('#sale-par1').val()));
                var d2 = Number($.trim($('#sale-par2').val()));
                var d3 = Number($.trim($('#sale-par3').val()));
                var d4 = Number($.trim($('#sale-achievementSalaryRate').val()));

                var e1 = Number($.trim($('#pur-par1').val()));
                var e2 = Number($.trim($('#pur-par2').val()));
                var e3 = Number($.trim($('#pur-par3').val()));
                var e4 = Number($.trim($('#pur-par4').val()));
                var e5 = Number($.trim($('#pur-achievementSalaryRate').val()));

                if (0 == a1 || 0 == a2 || 0 == a3 || 0 == a4 || 0 == b1 || 0 == b2 || 0 == b3 || 0 == b4 || 0 == b5  || 0 == b6 || 0 == c1 || 0 == c2 || 0 == c3 || 0 == c4 || 0 == c5 || 0 == c6 || 0 == c7 || 0 == d1 || 0 == d2 || 0 == d3 || 0 == d4 || 0 == e1 || 0 == e2 || 0 == e3 || 0 == e4 || 0 == e5) {
                    return $.dialog.tips('各系数不能为0或空！', 2), !1;
                }
                var data = {
                    proKitting: {
                        par1: a1,
                        par2: a2,
                        par3: a3,
                        achievementSalaryRate: a4
                    },
                    proProcessing: {
                        par1: b1,
                        par2: b2,
                        par3: b3,
                        par4: b4,
                        par5: b5,
                        achievementSalaryRate: b6
                    },
                    pack: {
                        par1: c1,
                        par2: c2,
                        par3: c3,
                        par4: c4,
                        par5: c5,
                        par6: c6,
                        achievementSalaryRate: c7
                    },
                    sale: {
                        par1: d1,
                        par2: d2,
                        par3: d3,
                        achievementSalaryRate: d4
                    },
                    pur: {
                        par1: e1,
                        par2: e2,
                        par3: e3,
                        par4: e4,
                        achievementSalaryRate: e5
                    }
                };
                Public.ajaxPost('/Base/Option/updateExpPar', data, function(res) {
                    if (200 === res.status) {
                        defaultPage.SYSTEM.expPar = data;
                        $.dialog.tips('业绩系数设置成功！');
                    } else {
                        $.dialog.tips('业绩系数设置失败！', 2);
                    }
                }, function(res) {
                    $.dialog.tips(res.msg, 2);
                });
            }
        });
        $('.save-and-redo-btn').on('click', function() {
            var type = $(this).data('type');
            if (Business.verifyRight('EXP_PAR_UPDATE')) {
                var a1 = Number($.trim($('#proKitting-par1').val()));
                var a2 = Number($.trim($('#proKitting-par2').val()));
                var a3 = Number($.trim($('#proKitting-par3').val()));
                var a4 = Number($.trim($('#proKitting-achievementSalaryRate').val()));

                var b1 = Number($.trim($('#proProcessing-par1').val()));
                var b2 = Number($.trim($('#proProcessing-par2').val()));
                var b3 = Number($.trim($('#proProcessing-par3').val()));
                var b4 = Number($.trim($('#proProcessing-par4').val()));
                var b5 = Number($.trim($('#proProcessing-par5').val()));
                var b6 = Number($.trim($('#proProcessing-achievementSalaryRate').val()));

                var c1 = Number($.trim($('#pack-par1').val()));
                var c2 = Number($.trim($('#pack-par2').val()));
                var c3 = Number($.trim($('#pack-par3').val()));
                var c4 = Number($.trim($('#pack-par4').val()));
                var c5 = Number($.trim($('#pack-par5').val()));
                var c6 = Number($.trim($('#pack-par6').val()));
                var c7 = Number($.trim($('#pack-achievementSalaryRate').val()));

                var d1 = Number($.trim($('#sale-par1').val()));
                var d2 = Number($.trim($('#sale-par2').val()));
                var d3 = Number($.trim($('#sale-par3').val()));
                var d4 = Number($.trim($('#sale-achievementSalaryRate').val()));

                var e1 = Number($.trim($('#pur-par1').val()));
                var e2 = Number($.trim($('#pur-par2').val()));
                var e3 = Number($.trim($('#pur-par3').val()));
                var e4 = Number($.trim($('#pur-par4').val()));
                var e5 = Number($.trim($('#pur-achievementSalaryRate').val()));

                if (0 == a1 || 0 == a2 || 0 == a3 || 0 == a4 || 0 == b1 || 0 == b2 || 0 == b3 || 0 == b4 || 0 == b5  || 0 == b6 || 0 == c1 || 0 == c2 || 0 == c3 || 0 == c4 || 0 == c5 || 0 == c6 || 0 == c7 || 0 == d1 || 0 == d2 || 0 == d3 || 0 == d4 || 0 == e1 || 0 == e2 || 0 == e3 || 0 == e4 || 0 == e5) {
                    return $.dialog.tips('各系数不能为0或空！', 2), !1;
                }
                var data = {
                    proKitting: {
                        par1: a1,
                        par2: a2,
                        par3: a3,
                        achievementSalaryRate: a4
                    },
                    proProcessing: {
                        par1: b1,
                        par2: b2,
                        par3: b3,
                        par4: b4,
                        par5: b5,
                        achievementSalaryRate: b6
                    },
                    pack: {
                        par1: c1,
                        par2: c2,
                        par3: c3,
                        par4: c4,
                        par5: c5,
                        par6: c6,
                        achievementSalaryRate: c7
                    },
                    sale: {
                        par1: d1,
                        par2: d2,
                        par3: d3,
                        achievementSalaryRate: d4
                    },
                    pur: {
                        par1: e1,
                        par2: e2,
                        par3: e3,
                        par4: e4,
                        achievementSalaryRate: e5
                    }
                };
                Public.ajaxPost('/Base/Option/updateExpPar', data, function(res) {
                    if (200 === res.status) {
                        defaultPage.SYSTEM.expPar = data;
                        if (type == 'proKittingSaveAndRedo') {
                            $.dialog.confirm('警告：即将重算全部含子产品的产品配货提成属性，是否确定继续？', function() {
                                $('.wrapper').append('<div id="div-lock"><div class="loading">重算全部含子产品的产品配货提成属性中...</div></div>');
                                Public.ajaxPost('/Base/Goods/upGoodsProduceInfo', {}, function(result) {
                                    if (200 == result.status) {
                                        $.dialog.tips('配货提成系数更新成功！全部含子产品的产品配货提成属性重算成功！');
                                    } else {
                                        $.dialog.alert('配货提成系数更新成功！但全部含子产品的产品配货提成属性重算失败！' + result.msg);
                                    }
                                    $('#div-lock').remove();
                                });
                            });
                        } else if (type == 'proProcessingSaveAndRedo') {
                            $.dialog.confirm('警告：即将重算全部加工项目的提成单价，是否确定继续？', function() {
                                $('.wrapper').append('<div id="div-lock"><div class="loading">重算全部加工项目的提成单价中...</div></div>');
                                Public.ajaxPost('/Base/ProProcessingProject/upProcessingProjectProducePriceInfo', {}, function(result) {
                                    if (200 == result.status) {
                                        $.dialog.tips('配货提成系数更新成功！全部加工项目的提成单价重算成功！');
                                    } else {
                                        $.dialog.alert('配货提成系数更新成功！但全部加工项目的提成单价重算失败！' + result.msg);
                                    }
                                    $('#div-lock').remove();
                                });
                            });
                        } else {
                            $.dialog.tips('业绩系数设置成功！');
                        }
                    } else {
                        $.dialog.tips('业绩系数设置失败！' + res.msg, 2);
                    }
                }, function(res) {
                    $.dialog.tips(res.msg, 2);
                });
            }
        });
    }
};
$(function() {
    THISPAGE.init();
});
