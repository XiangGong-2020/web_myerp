var api = frameElement.api;
var oper = api.data.oper;
var purSuggestId = api.data.purSuggestId;
var rowData = api.data.rowData || {};
var newData = api.data.newData || {};
var callback = api.data.callback;
var THISPAGE = {
    init: function() {
        if (void 0 !== purSuggestId) {
            Public.ajaxPost('/Base/PurSuggest/query', {
                id: purSuggestId
            }, function(res) {
                if (200 == res.status) {
                    rowData = res.data;
                    THISPAGE.initBtns();
                    THISPAGE.initDom(rowData);
                    THISPAGE.initValidator();
                    THISPAGE.initEvent();
                } else {
                    $.dialog.tips('获取数据失败！' + res.msg, 2);
                }
            });
        } else {
            THISPAGE.initBtns();
            THISPAGE.initDom();
            THISPAGE.initValidator();
        }
    },
    initBtns: function() {
        if (api.config.disEdit) {
            api.button({
                id: 'cancel',
                name: '关闭'
            });
        } else {
            api.button({
                id: 'confirm',
                name: '保存',
                defClass: 'ui_state_highlight',
                callback: function() {
                    return THISPAGE.postData(1), !1;
                }
            }, {
                id: 'cancel',
                name: '关闭'
            });
        }
    },
    initDom: function(rowData) {
        if ('edit' == oper) {
            $('#price').val(rowData.price);
            $('#url').val(rowData.url);
            $('#content').val(rowData.content);
            $('#copyurl, #openurl').removeClass('dn');
            if (api.config.disEdit) {
                $('#price').attr('disabled', true);
                $('#url').attr('disabled', true);
                $('#content').attr('disabled', true);
            }
        } else {
            $('#price').val('');
            $('#url').val('');
            $('#content').val('');
        }
    },
    initValidator: function() {
        $('#manage-form').validate({
            rules: {
                price: {number: !0},
                url: {url: !0}
            }
        });
    },
    initEvent: function() {
        $('.manage-wrapper').on('click', '#copyurl', function(a) {
            a.preventDefault();
            if (window.clipboardData) {
                window.clipboardData.setData('Text', rowData.url);
                $.dialog.tips('复制成功！');
            } else {
                var clipboard = new ClipboardJS('#copyurl', {
                    text: function() {
                        return rowData.url;
                    }
                });
                clipboard.on('success', function() {
                    $.dialog.tips('复制成功！');
                    clipboard.destroy();
                });
                clipboard.on('error', function() {
                    $.dialog.tips('复制失败！', 2);
                    clipboard.destroy();
                });
            }
        }),
            $('.manage-wrapper').on('click', '#openurl', function(a) {
                a.preventDefault();
                window.open(rowData.url);
            });
    },
    postData: function(callbackType = 0) {
        if (Business.verifyRight('PUR_MANAGER')) {
            if (!$('#manage-form').validate().form()) {
                return void $('#manage-form').find('input.valid-error').eq(0).focus();
            }
            var title = 'add' == oper ? '新增采购建议' : '编辑采购建议';
            var data = THISPAGE.getData();
            if (data) {
                if (!data.price && !data.url && !data.content) {
                    $.dialog.tips('不能全部留空，请至少录入一项！', 2);
                } else {
                    // 先规范建议采购链接格式
                    if (data.url) {
                        if (data.url.indexOf('http') !== 0) {
                            $.dialog.tips('建议采购链接格式错误！请只填入真实链接或留空。', 2);
                            return !1;
                        } else if (data.url.indexOf('//cart.') !== -1 || data.url.indexOf('//trade.') !== -1 || data.url.indexOf('/offer/') !== -1) {
                            $.dialog.tips('建议采购链接错误！不能为别人看不见的地址！', 2);
                            return !1;
                        }
                    }
                    Public.ajaxPost('/Base/PurSuggest/' + ('add' == oper ? 'add' : 'update'), data, function(res) {
                        if (res && 200 == res.status) {
                            parent.parent.$.dialog.tips(title + '成功！');
                            parent.parent.frames['purchase-purchaseSuggest'] && parent.parent.frames['purchase-purchaseSuggest'].$('#search').trigger('click');
                            if (callback && 'function' == typeof callback) {
                                callback(res.data, oper, window, callbackType);
                            };
                        } else {
                            $.dialog.tips(title + '失败！' + res.msg, 2);
                        }
                    });
                }
            }
        }
    },
    getData: function() {
        var data = {
            id: oper == 'add' ? -1 : rowData.id,
            billId: rowData.billId,
            billNo: rowData.billNo,
            transType: parseInt(rowData.transType),
            infoId: parseInt(newData.infoId || rowData.infoId),
            zid: newData.zid || rowData.zid || '',
            no: newData.no || rowData.no || '',
            name: newData.name || rowData.name,
            spec: newData.spec || rowData.spec,
            sku: newData.sku || rowData.sku,
            brand: newData.brand || rowData.brand,
            unit: newData.unit || rowData.unit,
            qty: parseFloat(newData.qty || rowData.qty),
            purPrice: parseFloat(newData.purPrice || rowData.purPrice),
            purId: parseInt(newData.purId || rowData.purId),
            purName: newData.purName || rowData.purName,
            price: parseFloat($.trim($('#price').val())),
            url: $.trim($('#url').val()),
            content: $.trim($('#content').val())
        };
        return data;
    }
};
$(function() {
    THISPAGE.init();
});
