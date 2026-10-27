var api = frameElement.api;
var defaultPage = Public.getDefaultPage();
var oper = api.data.oper;
var cRowId = api.data.rowId;
var goodsId = api.data.goodsId;
var apistatus = api.data.status;
var callback = api.data.callback;
var shopPrices = {};
var rules = {};
var THISPAGE = {
    init: function() {
        if (void 0 !== cRowId) {
            Public.ajaxPost('/Base/SalesOrderSpiderErrPrice/getShopPrices', {
                id: goodsId
            }, function(res) {
                if (200 == res.status) {
                    shopPrices = res.data;
                    THISPAGE.initBtns();
                    THISPAGE.initDom();
                    THISPAGE.initValidator();
                    THISPAGE.initEvent();
                } else {
                    $.dialog.tips('获取数据失败！' + res.msg, 2);
                }
            });
        }
    },
    initBtns: function() {
        api.button({
            id: 'confirm',
            name: '保存',
            defClass: 'ui_state_highlight',
            callback: function() {
                return THISPAGE.postData(), !1;
            }
        }, {
            id: 'cancel',
            name: '关闭'
        });
    },
    initDom: function() {
        if (shopPrices) {
            var shops = '';
            $.each(shopPrices, function(k, v) {
                rules['price_' + k] = {required: !0};
                shops += '<li class="row-item item-lg" data-key="' + k + '"><div class="label-wrap"><label>' + k + '</label><span class="must">●</span></div><div class="ctn-wrap"><input type="number" class="ui-input" name="price_' + k + '" style="width:calc(100% - 262px)" value="' + v + '" placeholder="输入目前该店铺的后台售价" /><span class="unit" style="right:50px">元</span></div><div id="delShop" class="pl10 pr10 h28 lh28 hover-white b-r-3" style="position:absolute;z-index:1;top:0;right:0;" title="如果本店铺已关店，可删除本行">删除</div></li>';
            })
            $('#shops').html(shops);
        }
    },
    initValidator: function() {
        $('#manage-form').validate({
            rules: rules
        })
    },
    initEvent: function() {
        $('.manage-wrapper').on('click', '#delShop', function() {
            $(this).parent().remove();
        })
    },
    postData: function() {
        if (Business.verifyRight('SA_SPIDER_ERR_ADVANCE')) {
            if (!$('#manage-form').validate().form()) {
                return void $('#manage-form').find('input.valid-error').eq(0).focus();
            }
            var data = THISPAGE.getData();
            data && Public.ajaxPost('/Base/SalesOrderSpiderErrPrice/status', data, function(res) {
                if (res && 200 == res.status) {
                    parent.$.dialog.tips('标记成功！');
                    callback(res.data, oper, window);
                } else {
                    $.dialog.tips('标记失败！' + res.msg, 2);
                }
            });
        }
    },
    getData: function() {
        var newShopPrices = {};
        var shops = $('#shops').children('li');
        for (var i = 0; i < shops.length; i++) {
            if ($.trim(shops.eq(i).find('input').val())) {
                newShopPrices[shops.eq(i).data('key')] = Number($.trim(shops.eq(i).find('input').val()));
            }
        };
        var data = {
            id: cRowId,
            status: apistatus,
            goodsId: goodsId,
            shopPrices: JSON.stringify(newShopPrices)
        };
        return data;
    }
};
$(function() {
    THISPAGE.init();
});
