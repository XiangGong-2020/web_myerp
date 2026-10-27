var api = frameElement.api;
var defaultPage = Public.getDefaultPage();
var oper = api.data.oper;
var cRowId = api.data.rowId;
var rowData = api.data.rowData || {};
var callback = api.data.callback;
var companyCombo;
var addressCombo;
var packNameCombo;
var scanNameCombo;
var THISPAGE = {
    init: function() {
        if (void 0 !== cRowId) {
            Public.ajaxPost('/Base/DeliverBag/query', {
                id: cRowId
            }, function(res) {
                if (200 == res.status) {
                    rowData = res.data;
                    THISPAGE.initBtns();
                    THISPAGE.initDom();
                    THISPAGE.initEvent();
                    THISPAGE.initValidator();
                } else {
                    $.dialog.tips('获取数据失败！' + res.msg, 2);
                }
            });
        } else {
            THISPAGE.initBtns();
            THISPAGE.initDom();
            THISPAGE.initEvent();
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
                id: 'saveAndAdd',
                name: '保存并新增',
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
        if (rowData.id) {
            $('#no').val(rowData.no);
            $('#weight').val(rowData.weight);
            $('#amount').val(rowData.amount);
            $('#packTime').val(rowData.packTime);
            $('#scanTime').val(rowData.scanTime);
            $('#printTime').val(rowData.printTime);
            $('#orderNo').val(rowData.orderNo);
            $('#targetAmount').val(rowData.targetAmount);
            $('#description').val(rowData.description);
        } else {
            $('#printTime').val((new Date).format('yyyy-MM-dd hh:mm:ss'));
            $('#targetAmount').val(0);
        }
        if (api.config.disEdit) {
            $('#no').attr('disabled', true).addClass('ui-input-dis');
            $('#weight').attr('disabled', true).addClass('ui-input-dis');
            $('#amount').attr('disabled', true).addClass('ui-input-dis');
            $('#packTime').attr('disabled', true).addClass('ui-input-dis');
            $('#scanTime').attr('disabled', true).addClass('ui-input-dis');
            $('#printTime').attr('disabled', true).addClass('ui-input-dis');
            $('#orderNo').attr('disabled', true).addClass('ui-input-dis');
            $('#targetAmount').attr('disabled', true).addClass('ui-input-dis');
            $('#description').attr('disabled', true).addClass('ui-input-dis');
        }
        companyCombo = $('#company').combo({
            data: '/Base/DeliverCompany/queryAll',
            ajaxOptions: {
                formatData: function(a) {
                    return a.data.rows;
                }
            },
            value: 'id',
            text: 'name',
            listHeight: 400,
            defaultSelected: rowData.company ? ['name', rowData.company] : -1,
            disabled: api.config.disEdit ? !0 : !1
        }).getCombo();
        addressCombo = $('#address').combo({
            data: [
                {id: 0, name: ''},
                {id: 1, name: '北京'},
                {id: 2, name: '天津'},
                {id: 3, name: '上海'},
                {id: 4, name: '重庆'},
                {id: 5, name: '河北'},
                {id: 6, name: '山西'},
                {id: 7, name: '辽宁'},
                {id: 8, name: '吉林'},
                {id: 9, name: '黑龙'},
                {id: 10, name: '江苏'},
                {id: 11, name: '浙江'},
                {id: 12, name: '安徽'},
                {id: 13, name: '福建'},
                {id: 14, name: '江西'},
                {id: 15, name: '山东'},
                {id: 16, name: '河南'},
                {id: 17, name: '湖北'},
                {id: 18, name: '湖南'},
                {id: 19, name: '广东'},
                {id: 20, name: '广西'},
                {id: 21, name: '四川'},
                {id: 22, name: '贵州'},
                {id: 23, name: '云南'},
                {id: 24, name: '海南'},
                {id: 25, name: '陕西'},
                {id: 26, name: '甘肃'},
                {id: 27, name: '宁夏'},
                {id: 28, name: '青海'},
                {id: 29, name: '内蒙'},
                {id: 30, name: '西藏'},
                {id: 31, name: '新疆'},
                {id: 32, name: '香港'},
                {id: 33, name: '澳门'},
                {id: 34, name: '台湾'},
                {id: 35, name: '国外'}
            ],
            value: 'id',
            text: 'name',
            listHeight: 400,
            defaultSelected: rowData.address ? ['name', rowData.address] : -1,
            disabled: api.config.disEdit ? !0 : !1
        }).getCombo();
        packNameCombo = $('#packName').combo({
            data: '/Base/User/queryAll',
            ajaxOptions: {
                formatData: function(a) {
                    return a.data.rows;
                }
            },
            value: 'id',
            text: 'name',
            listHeight: 400,
            defaultSelected: rowData.packName ? ['name', rowData.packName] : -1,
            disabled: api.config.disEdit ? !0 : !1
        }).getCombo();
        scanNameCombo = $('#scanName').combo({
            data: '/Base/User/queryAll',
            ajaxOptions: {
                formatData: function(a) {
                    return a.data.rows;
                }
            },
            value: 'id',
            text: 'name',
            listHeight: 400,
            defaultSelected: rowData.scanName ? ['name', rowData.scanName] : -1,
            disabled: api.config.disEdit ? !0 : !1
        }).getCombo();
        $('#company').find('input').attr('name', 'companyInput');
        $('#address').find('input').attr('name', 'addressInput');
        $('#packName').find('input').attr('name', 'packNameInput');
        $('#scanName').find('input').attr('name', 'scanNameInput');
    },
    initEvent: function() {
        if (!api.config.disEdit) {
            laydate.render({
                elem: "#packTime, #scanTime",
                type: 'datetime'
            });
        }
    },
    initValidator: function() {
        $('#manage-form').validate({
            rules: {
                no: {required: !0},
                companyInput: {required: !0},
                addressInput: {required: !0},
                weight: {required: !0},
                packNameInput: {required: !0},
                scanNameInput: {required: !0},
                packTime: {required: !0},
                scanTime: {required: !0}
            }
        });
    },
    postData: function(callbackType = 0) {
        if (Business.verifyRight('DELIVERBAG_ADD')) {
            if (!$('#manage-form').validate().form()) {
                return void $('#manage-form').find('input.valid-error').eq(0).focus();
            }
            var title = 'add' == oper ? '新增' : '编辑';
            var data = THISPAGE.getData();
            data && Public.ajaxPost('/Base/DeliverBag/' + ('add' == oper ? 'add' : 'update'), data, function(res) {
                if (res && 200 == res.status) {
                    parent.parent.$.dialog.tips(title + '成功！');
                    if (callback && 'function' == typeof callback) {
                        callback(res.data, oper, window, callbackType);
                    }
                } else {
                    $.dialog.tips(title + '失败！' + res.msg, 2);
                }
            });
        }
    },
    getData: function() {
        var data = {
            id: cRowId || 0,
            no: $.trim($('#no').val()),
            company: companyCombo.getText(),
            address: addressCombo.getText(),
            weight: Number($('#weight').val()).toFixed(2),
            amount: Number($('#amount').val()).toFixed(2),
            packName: packNameCombo.getText(),
            packTime: $('#packTime').val(),
            scanName: scanNameCombo.getText(),
            scanTime: $('#scanTime').val(),
            printTime: $('#printTime').val(),
            orderNo: $('#orderNo').val(),
            targetAmount: Number($('#targetAmount').val()).toFixed(2),
            description: $.trim($('#description').val())
        };
        return data;
    },
    resetForm: function(a) {
        var uiTitle = $(".ui_title", parent.document).text().replace('◉ 视窗', '');
        if (uiTitle.includes('编辑')) {
            $(".ui_title", parent.document).text(uiTitle.replace('编辑','新增'));
        }
        oper = 'add';
        cRowId = void 0;
        $('#no').val('');
        companyCombo._setDefaultSelected(0);
        addressCombo._setDefaultSelected(0);
        $('#weight').val('');
        $('#amount').val('');
        packNameCombo._setDefaultSelected(0);
        scanNameCombo._setDefaultSelected(0);
        $('#packTime').val((new Date).format('yyyy-MM-dd hh:mm:ss'));
        $('#scanTime').val((new Date).format('yyyy-MM-dd hh:mm:ss'));
        $('#printTime').val((new Date).format('yyyy-MM-dd hh:mm:ss'));
        $('#targetAmount').val(0);
        $('#orderNo').val('');
        $('#description').val('');
    }
};
$(function() {
    THISPAGE.init();
});
