var api = frameElement.api;
var defaultPage = Public.getDefaultPage();
var oper = api.data.oper;
var cRowId = api.data.rowId;
var rowData = {};
var THISPAGE = {
    init: function() {
        if (void 0 !== cRowId) {
            Public.ajaxPost('/Base/CompetitiveMonitor/query', {
                id: cRowId
            }, function(res) {
                if (200 == res.status) {
                    rowData = res.data;
                    THISPAGE.initBtns();
                    THISPAGE.initDom();
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
            THISPAGE.initEvent();
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
                id: 'saveAndMonitor',
                name: '保存并立即插队监控',
                defClass: 'ui_state_highlight',
                callback: function() {
                    return THISPAGE.postData(0), !1;
                }
            }, {
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
    initDom: function() {
        if ('edit' == oper) {
            $('#goodsText').data('goodsId', rowData.goodsId).val(rowData.goodsText);
            $('#url').val(rowData.url);
            if (rowData.competitiveInfo.length > 0) {
                let urls = '';
                $.each(rowData.competitiveInfo, function(k, v) {
                    urls += '<li class="row-item" id="' + v.id + '" data-id="' + v.id + '"><div class="label-wrap"><label for="ourl">竞品链接</label></div><div class="ctn-wrap"><input type="text" name="ourl" id="ourl" class="ui-input es" style="width:calc(100% - 192px)" value="' + v.url + '" placeholder="需是淘宝竞店中的商品链接" /></div><div id="clearurl" class="pl10 pr10 h28 lh28 hover-yellow b-r-3" style="position:absolute;z-index:1;top:0;right:50px;" title="清空本行竞品链接，以便重新填写。">清空</div><div id="delurl" class="pl10 pr10 h28 lh28 hover-red b-r-3" style="position:absolute;z-index:1;top:0;right:0;" title="删除本行竞品链接，谨慎操作。">删除</div></li>';
                })
                $('#urls').html(urls);
            }
            if (api.config.disEdit) {
                $('#goodsText').attr('disabled', true).addClass('ui-input-dis');
                $('#url').attr('disabled', true).addClass('ui-input-dis');
                $('#description').attr('disabled', true).addClass('ui-input-dis');
            }
        }
    },
    initValidator: function() {
        $('#manage-form').validate({
            rules: {
                goodsText: {required: !0},
                url: {required: !0}
            }
        });
    },
    initEvent: function() {
        if (!api.config.disEdit) {
            $('#goods').on('click', function() {
                $.dialog({
                    width: 1250,
                    height: 500,
                    title: '选择产品',
                    content: 'url:/Uri/goods_batch',
                    data: {
                        isNoAdd: !0,
                        isSingle: !0
                    },
                    max: !1,
                    min: !1,
                    lock: !0,
                    ok: function() {
                        for (let rowid in this.content.addList) {
                            var row = this.content.addList[rowid];
                            $('#goodsText').data('goodsId', rowid).val(
                                row.no + ' ' + row.name + (row.spec ? ' ' + row.spec : '') + (row.sku ? ' ' + row.sku : '') + (row.brandName ? ' ' + row.brandName : '')
                            );
                        }
                    },
                    cancel: function() {
                        return true;
                    }
                });
            });
            $('.manage-wrapper').on('click', '#addurl', function() {
                $('#urls').append('<li class="row-item" id="-1" data-id="-1"><div class="label-wrap"><label for="ourl">竞品链接</label></div><div class="ctn-wrap"><input type="text" name="ourl" id="ourl" class="ui-input es" style="width:calc(100% - 192px)" value="" placeholder="需是淘宝竞店中的商品链接" /></div><div id="clearurl" class="pl10 pr10 h28 lh28 hover-yellow b-r-3" style="position:absolute;z-index:1;top:0;right:50px;" title="清空本行竞品链接，以便重新填写。">清空</div><div id="delurl" class="pl10 pr10 h28 lh28 hover-red b-r-3" style="position:absolute;z-index:1;top:0;right:0;" title="删除本行竞品链接，谨慎操作。">删除</div></li>');
                $('#urls').scrollTop($('#urls')[0].scrollHeight); // 自动滚动到底部
            });
            $('.manage-wrapper').on('click', '#clearurl', function() {
                $(this).prev().find('input').val('');
            });
            $('.manage-wrapper').on('click', '#delurl', function() {
                $(this).parent().remove();
            });
        }
    },
    postData: function(monitorType = 0) {
        if ($.inArray('COMPETITIVE_MONITOR_ADVANCE', defaultPage.SYSTEM.rights) !== -1) {
            if (!$('#manage-form').validate().form()) {
                return void $('#manage-form').find('input.valid-error').eq(0).focus();
            }
            var data = THISPAGE.getData();
            data.monitorType = monitorType;
            var title = 'add' == oper ? '新增' : '编辑';
            data && Public.ajaxPost('/Base/CompetitiveMonitor/' + ('add' == oper ? 'add' : 'update'), data, function(res) {
                if (res && 200 == res.status) {
                    parent.parent.$.dialog.tips(title + '成功！');
                    parent.parent.frames['product-competitiveMonitor'] && parent.parent.frames['product-competitiveMonitor'].$('#search').trigger('click');
                    window.api.close();
                } else {
                    $.dialog.tips(title + '失败！' + res.msg, 2);
                }
            });
        }
    },
    getData: function() {
        var competitiveInfo = [];
        var urls = $('#urls').children('li');
        var url = {};
        for (var i = 0; i < urls.length; i++) {
            if ($.trim(urls.eq(i).find('input').val())) {
                url = {
                    id: urls.eq(i).data('id'),
                    url: $.trim(urls.eq(i).find('input').val())
                };
                competitiveInfo.push(url);
            }
        }
        var data = {
            id: cRowId || 0,
            goodsId: Number($('#goodsText').data('goodsId')),
            goodsText: $.trim($('#goodsText').val()),
            url: $.trim($('#url').val()),
            competitiveInfo: competitiveInfo
        };
        return data;
    }
};
$(function() {
    THISPAGE.init();
});
