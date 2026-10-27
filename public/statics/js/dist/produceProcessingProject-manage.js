var api = frameElement.api;
var defaultPage = Public.getDefaultPage();
var oper = api.data.oper;
var cRowId = api.data.rowId;
var callback = api.data.callback;
var rowData = {};
var typeCombo;
var needTestCombo;
var isDisableCombo;
var THISPAGE = {
    init: function() {
        if (void 0 !== cRowId) {
            Public.ajaxPost('/Base/ProProcessingProject/query', {
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
            if (rowData.goodsId > 0) {
                $('#name').parent().parent().addClass('dn');
                $('#goodsText').data('goodsId', rowData.goodsId).val(rowData.name);
                $('#producePrice').val(rowData.producePrice);
                $('#goodsText').parent().parent().removeClass('dn');
                if (rowData.type == 2) {
                    $('#tPointNum').parent().parent().addClass('dn');
                    $('#zPointNum').parent().parent().addClass('dn');
                    $('#donePerHourNum').val(rowData.donePerHourNum);
                    $('#donePerHourTestInfo').val(rowData.donePerHourTestInfo);
                    $('#donePerHourNum').parent().parent().removeClass('dn');
                    $('#donePerHourTestInfo').parent().parent().removeClass('dn');
                } else {
                    $('#donePerHourNum').parent().parent().addClass('dn');
                    $('#donePerHourTestInfo').parent().parent().addClass('dn');
                    $('#tPointNum').val(rowData.tPointNum);
                    $('#zPointNum').val(rowData.zPointNum);
                    $('#tPointNum').parent().parent().removeClass('dn');
                    $('#zPointNum').parent().parent().removeClass('dn');
                }
            } else {
                $('#goodsText').parent().parent().addClass('dn');
                $('#tPointNum').parent().parent().addClass('dn');
                $('#zPointNum').parent().parent().addClass('dn');
                $('#name').val(rowData.name);
                $('#donePerHourNum').val(rowData.donePerHourNum);
                $('#donePerHourTestInfo').val(rowData.donePerHourTestInfo);
                $('#producePrice').val(rowData.producePrice);
                $('#name').parent().parent().removeClass('dn');
                $('#donePerHourNum').parent().parent().removeClass('dn');
                $('#donePerHourTestInfo').parent().parent().removeClass('dn');
            }
            $('#createTime').val(rowData.uName + ' ' + rowData.createTime);
            $('#modifyTime').val(rowData.modName + ' ' + rowData.modifyTime);
            $('#description').html(rowData.description);
        }
        typeCombo = $('#type').combo({
            data: [
                {id: 0, name: '开发板整体生产类'},
                {id: 1, name: '电路组件焊接类'},
                {id: 2, name: '包装组装类'},
                {id: 9, name: '临时加工类（无库存运算）'}
            ],
            value: 'id',
            text: 'name',
            defaultSelected: rowData.type >= 0 ? ['id', rowData.type] : 0,
            callback: {
                onListClick: function() {
                    var getValue = typeCombo.getValue();
                    if (getValue == 0 || getValue == 1) {
                        $('#name').parent().parent().addClass('dn');
                        $('#donePerHourNum').parent().parent().addClass('dn');
                        $('#donePerHourTestInfo').parent().parent().addClass('dn');
                        $('#goodsText').parent().parent().removeClass('dn');
                        $('#tPointNum').parent().parent().removeClass('dn');
                        $('#zPointNum').parent().parent().removeClass('dn');
                        needTestCombo._setDefaultSelected(['id', 1]);
                    } else if (getValue == 2) {
                        $('#name').parent().parent().addClass('dn');
                        $('#tPointNum').parent().parent().addClass('dn');
                        $('#zPointNum').parent().parent().addClass('dn');
                        $('#goodsText').parent().parent().removeClass('dn');
                        $('#donePerHourNum').parent().parent().removeClass('dn');
                        $('#donePerHourTestInfo').parent().parent().removeClass('dn');
                        needTestCombo._setDefaultSelected(['id', 0]);
                    } else {
                        $('#goodsText').parent().parent().addClass('dn');
                        $('#tPointNum').parent().parent().addClass('dn');
                        $('#zPointNum').parent().parent().addClass('dn');
                        $('#name').parent().parent().removeClass('dn');
                        $('#donePerHourNum').parent().parent().removeClass('dn');
                        $('#donePerHourTestInfo').parent().parent().removeClass('dn');
                        $('#name').removeAttr('readonly');
                        needTestCombo._setDefaultSelected(['id', 0]);
                    }
                    $('#producePrice').val('');
                }
            }
        }).getCombo();
        needTestCombo = $('#needTest').combo({
            data: [{id: 0, name: '无'}, {id: 1, name: '有'}],
            value: 'id',
            text: 'name',
            defaultSelected: rowData.needTest >= 0 ? ['id', rowData.needTest] : 0,
            disabled: !0
        }).getCombo();
        isDisableCombo = $('#isDisable').combo({
            data: [{id: 0, name: '正常'}, {id: 1, name: '将停产'}, {id: 2, name: '已停产'}, {id: 9, name: '已禁用'}],
            value: 'id',
            text: 'name',
            defaultSelected: rowData.isDisable >= 0 ? ['id', rowData.isDisable] : 0
        }).getCombo();
        $('#type').find('input').attr('name', 'typeInput');
        $('#needTest').find('input').attr('name', 'needTestInput');
        $('#isDisable').find('input').attr('name', 'isDisableInput');
    },
    initValidator: function() {
        $('#manage-form').validate({
            rules: {
                typeInput: {required: !0},
                needTestInput: {required: !0},
                isDisableInput: {required: !0}
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
                            $('#goodsText').data('goodsId', row.id).val(
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
            $('#modifyHis').on('click', function() {
                $.dialog({
                    title: '加工项目编辑历史',
                    content: 'url:/Uri/produceProcessingProject_modify_his',
                    data: {
                        id: rowData.id
                    },
                    width: $(window).width() - 100,
                    height: $(window).height() - 200,
                    max: !1,
                    min: !1,
                    lock: !0,
                    cache: !1
                });
            });
        }
    },
    postData: function() {
        if (Business.verifyRight('PROPROJECT_ADVANCE')) {
            if (!$('#manage-form').validate().form()) {
                return void $('#manage-form').find('input.valid-error').eq(0).focus();
            }
            var data = THISPAGE.getData();
            var title = 'add' == oper ? '新增' : '编辑';
            Public.ajaxPost('/Base/ProProcessingProject/' + ('add' == oper ? 'add' : 'update'), data, function(res) {
                if (200 == res.status) {
                    parent.parent.$.dialog.tips(title + '成功！');
                    parent.parent.frames['produce-produceProcessingProject'] && parent.parent.frames['produce-produceProcessingProject'].$('#search').trigger('click');
                    window.api.close();
                } else {
                    $.dialog.tips(title + '失败！' + res.msg, 2);
                }
            });
        }
    },
    getData: function() {
        var data = {
            id: cRowId || 0,
            type: typeCombo.getValue(),
            name: typeCombo.getValue() == 9 ? $.trim($('#name').val()) : $.trim($('#goodsText').val()),
            goodsId: typeCombo.getValue() == 9 ? 0 : Number($('#goodsText').data('goodsId')),
            tPointNum: parseInt($('#tPointNum').val() || 0),
            zPointNum: parseInt($('#zPointNum').val() || 0),
            donePerHourNum: parseInt($('#donePerHourNum').val() || 0),
            needTest: needTestCombo.getValue(),
            isDisable: isDisableCombo.getValue(),
            donePerHourTestInfo: $.trim($('#donePerHourTestInfo').val()),
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
        typeCombo._setDefaultSelected(0);
        $('#goodsText').val('');
        $('#tPointNum').val('');
        $('#zPointNum').val('');
        $('#name').val('');
        $('#donePerHourNum').val('');
        needTestCombo._setDefaultSelected(1);
        isDisableCombo._setDefaultSelected(0);
        $('#donePerHourTestInfo').val('');
        $('#description').val('');
    }
};
$(function() {
    THISPAGE.init();
});
