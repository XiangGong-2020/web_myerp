var api = frameElement.api;
var defaultPage = Public.getDefaultPage();
var oper = api.data.oper;
var cRowId = api.data.rowId;
var callback = api.data.callback;
var rowData = {};
var THISPAGE = {
    init: function() {
        if (void 0 !== cRowId) {
            Public.ajaxPost('/Base/ProKittingReq/query', {
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
        }
    },
    initDom: function() {
        if ('edit' == oper) {
            $('#lastDate').val(rowData.lastDate);
            $('#goodsText').data('goodsId', rowData.goodsId).val(rowData.goodsText);
            $('#qty').val(rowData.qty);
            $('#content').html(rowData.content);
            $('#description').html(rowData.description);
            $('#createTime').html('申请人：' + rowData.uName + '， 申请时间：' + rowData.createTime);
            if (api.config.disEdit) {
                $('#lastDate').attr('disabled', true).addClass('ui-input-dis');
                $('#goodsText').attr('disabled', true).addClass('ui-input-dis');
                $('#qty').attr('disabled', true).addClass('ui-input-dis');
                $('#content').attr('disabled', true).addClass('ui-input-dis');
                $('#description').attr('disabled', true).addClass('ui-input-dis');
            }
        } else {
            $('#lastDate').val(parseInt((new Date).format('h')) < 12 ? ((new Date).format('yyyy-MM-dd') + ' 17:00:00') : (Public.addDays((new Date).format('yyyy-MM-dd'), 1) + ' 12:00:00'));
            $('#description').attr({readonly: 'readonly'});
        }
    },
    initValidator: function() {
        $('#manage-form').validate({
            rules: {
                lastDate: {required: !0},
                goodsText: {required: !0},
                qty: {required: !0}
            }
        });
    },
    initEvent: function() {
        if (!api.config.disEdit) {
            laydate.render({
                elem: "#lastDate",
                type: 'datetime'
            });
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
                            $('#qty').focus();
                        }
                    },
                    cancel: function() {
                        $('#qty').focus();
                        return !0;
                    }
                });
            });
        }
    },
    postData: function() {
        if ($.inArray('PROREQ_ADVANCE', defaultPage.SYSTEM.rights) !== -1 || rowData.uId == defaultPage.SYSTEM.uId || (void 0 == cRowId && $.inArray('PROREQ_QUERY', defaultPage.SYSTEM.rights) !== -1)) {
            if (!$('#manage-form').validate().form()) {
                return void $('#manage-form').find('input.valid-error').eq(0).focus();
            }
            var data = THISPAGE.getData();
            var title = 'add' == oper ? '新增' : '编辑';
            Public.ajaxPost('/Base/ProKittingReq/' + ('add' == oper ? 'add' : 'update'), data, function(res) {
                if (200 == res.status) {
                    parent.parent.$.dialog.tips(title + '成功！');
                    parent.parent.frames['produce-produceKittingTask'] && parent.parent.frames['produce-produceKittingTask'].$('#search').trigger('click');
                    parent.parent.frames['produce-produceKittingReq'] && parent.parent.frames['produce-produceKittingReq'].$('#search').trigger('click');
                    window.api.close();
                } else {
                    $.dialog.tips(title + '失败！' + res.msg, 2);
                }
            });
        } else {
            $.dialog.tips('不能编辑别人的配货申请！仅本人或高级权限可编辑！', 2);
            return void 0;
        }
    },
    getData: function() {
        var data = {
            id: cRowId || 0,
            lastDate: $.trim($('#lastDate').val()),
            goodsId: Number($('#goodsText').data('goodsId')),
            goodsText: $.trim($('#goodsText').val()),
            qty: parseInt($('#qty').val()),
            content: $.trim($('#content').val()),
            description: $.trim($('#description').val())
        };
        return data;
    },
    resetForm: function(a) {
        var uiTitle = $(".ui_title", parent.document).text().replace('◉ 视窗', '');
        if (uiTitle.includes('编辑')) {
            $(".ui_title", parent.document).text(uiTitle.replace('编辑', '新增'));
        }
        oper = 'add';
        cRowId = void 0;
        $('#lastDate').val('');
        $('#goodsText').val('');
        $('#qty').val('');
        $('#content').val('');
        $('#description').val('');
    }
};
$(function() {
    THISPAGE.init();
});
