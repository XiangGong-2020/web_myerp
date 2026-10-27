var defaultPage = Public.getDefaultPage();
var valMethodsCombo;
var currencyCombo;
var requiredCheckStoreCombo;
var THISPAGE = {
    init: function() {
        THISPAGE.initDom();
        THISPAGE.initValidator();
        THISPAGE.initEvent();
    },
    initDom: function() {
        $('#beginDate').val(defaultPage.SYSTEM.beginDate || '');
        $('#qtyPlaces').val(defaultPage.SYSTEM.qtyPlaces || '');
        $('#pricePlaces').val(defaultPage.SYSTEM.pricePlaces || '');
        $('#amountPlaces').val(defaultPage.SYSTEM.amountPlaces || '');
        $('#fillClockinTimes').val(defaultPage.SYSTEM.fillClockinTimes || '');
        $('#bonusFullAttendance').val(defaultPage.SYSTEM.bonusFullAttendance || '');
        $('#bonusDuty').val(defaultPage.SYSTEM.bonusDuty || '');
        $('#bonusBid1').val(defaultPage.SYSTEM.bonusBid1 || '');
        $('#bonusBid2').val(defaultPage.SYSTEM.bonusBid2 || '');
        $('#bonusBidLeap').val(defaultPage.SYSTEM.bonusBidLeap || '');
        $('#priceWH1').val(defaultPage.SYSTEM.priceWH1 || '');
        $('#priceWH2').val(defaultPage.SYSTEM.priceWH2 || '');
        $('#priceWR').val(defaultPage.SYSTEM.priceWR || '');
        $('#deductionSocialSecurity').val(defaultPage.SYSTEM.deductionSocialSecurity || '');
        defaultPage.SYSTEM.requiredCheckStore && ($('#requiredCheckStore')[0].checked = !0);

        currencyCombo = $('#currency').combo({
            data: [{value: 'rmb', text: '人民币'}, {value: 'dollor', text: '美元'}],
            text: 'text',
            value: 'value',
            width: 420,
            disabled: !1,
            defaultSelected: ['value', defaultPage.SYSTEM.currency || 'rmb']
        }).getCombo();
        valMethodsCombo = $('#valMethods').combo({
            data: [{value: 'movingAverage', text: '移动平均法'}, {value: 'firstInFirstOut', text: '先进先出法'}],
            text: 'text',
            value: 'value',
            width: 420,
            disabled: !1,
            defaultSelected: ['value', defaultPage.SYSTEM.valMethods || 'movingAverage']
        }).getCombo();
        requiredCheckStoreCombo = $('#requiredCheckStore').combo({
            data: [{value: '0', text: '库存不足允许出货'}, {value: '1', text: '库存不足禁止出货'}],
            text: 'text',
            value: 'value',
            width: 420,
            disabled: !1,
            defaultSelected: ['value', defaultPage.SYSTEM.requiredCheckStore || '0']
        }).getCombo();
        $('#currency').find('input').attr('name', 'currencyInput');
        $('#valMethods').find('input').attr('name', 'valMethodsInput');
        $('#requiredCheckStore').find('input').attr('name', 'requiredCheckStoreInput');

        Public.ajaxGet('/Base/Category/list', {typeNumber: 'departmenttype'}, function(res) {
            if (res.status === 200) {
                var department = '';
                $.each(res.data.rows, function(i, v) {
                    department += '<option value="' + v.id + '">' + v.name + '</option>';
                });
                /* 异步获取数据成功之后再填充选项并加载选择事件 */
                $('#priceWH1Department').html(department);
                $('#priceWH2Department').html(department);
                $('#priceWH1DepartmentCombo').fSelect({
                    id: 'priceWH1DepartmentFselect',
                    showSearch: !1,
                    listHeight: 300
                });
                $('#priceWH2DepartmentCombo').fSelect({
                    id: 'priceWH2DepartmentFselect',
                    showSearch: !1,
                    listHeight: 300
                });
                if (defaultPage.SYSTEM.priceWH1Department) {
                    $('#priceWH1DepartmentFselect .fs-input').val(defaultPage.SYSTEM.priceWH1Department);
                    var names = defaultPage.SYSTEM.priceWH1Department.toString().split(',');
                    $("#priceWH1DepartmentFselect .fs-option").each(function() {
                        if(names.indexOf($(this).children('.fs-option-label').html()) != -1) {
                            $(this).addClass("selected");
                        }
                    });
                }
                if (defaultPage.SYSTEM.priceWH2Department) {
                    $('#priceWH2DepartmentFselect .fs-input').val(defaultPage.SYSTEM.priceWH2Department);
                    var names = defaultPage.SYSTEM.priceWH2Department.toString().split(',');
                    $("#priceWH2DepartmentFselect .fs-option").each(function() {
                        if(names.indexOf($(this).children('.fs-option-label').html()) != -1) {
                            $(this).addClass("selected");
                        }
                    });
                }
            } else {
                $.dialog.tips(data1.msg, 2);
            }
        });
    },
    initValidator: function() {
        $('#manage-form').validate({
            rules: {
                beginDate: {required: !0},
                currencyInput: {required: !0},
                valMethodsInput: {required: !0},
                requiredCheckStoreInput: {required: !0},
                qtyPlaces: {required: !0},
                pricePlaces: {required: !0},
                amountPlaces: {required: !0},
                fillClockinTimes: {required: !0},
                bonusFullAttendance: {required: !0},
                bonusDuty: {required: !0},
                bonusBid1: {required: !0},
                bonusBid2: {required: !0},
                bonusBidLeap: {required: !0},
                priceWH1: {required: !0},
                priceWH2: {required: !0},
                priceWR: {required: !0},
                deductionSocialSecurity: {required: !0},
                fsInput: {required: !0}
            }
        });
    },
    initEvent: function() {
        laydate.render({
            elem: "#beginDate",
        });
        $('#refresh').on('click', function() {
            window.location.reload();
        });
        $('#save').on('click', function() {
            if (Business.verifyRight('SYS_PAR_UPDATE')) {
                if (!$('#manage-form').validate().form()) {
                    return void $('#manage-form').find('input.valid-error').eq(0).focus();
                }
                var beginDate = $.trim($('#beginDate').val());
                var currency = currencyCombo.getValue();
                var valMethods = valMethodsCombo.getValue();
                var requiredCheckStore = requiredCheckStoreCombo.getValue();
                var qtyPlaces = $.trim($('#qtyPlaces').val());
                var pricePlaces = $.trim($('#pricePlaces').val());
                var amountPlaces = $.trim($('#amountPlaces').val());
                var fillClockinTimes = $.trim($('#fillClockinTimes').val());
                var bonusFullAttendance = $.trim($('#bonusFullAttendance').val());
                var bonusDuty = $.trim($('#bonusDuty').val());
                var bonusBid1 = $.trim($('#bonusBid1').val());
                var bonusBid2 = $.trim($('#bonusBid2').val());
                var bonusBidLeap = $.trim($('#bonusBidLeap').val());
                var priceWH1 = $.trim($('#priceWH1').val());
                var priceWH1Department = $.trim($('#priceWH1DepartmentFselect .fs-input').val());
                var priceWH2 = $.trim($('#priceWH2').val());
                var priceWH2Department = $.trim($('#priceWH2DepartmentFselect .fs-input').val());
                var priceWR = $.trim($('#priceWR').val());
                var deductionSocialSecurity = $.trim($('#deductionSocialSecurity').val());
                var data = {
                    beginDate: beginDate,
                    currency: currency,
                    valMethods: valMethods,
                    requiredCheckStore: requiredCheckStore,
                    qtyPlaces: qtyPlaces,
                    pricePlaces: pricePlaces,
                    amountPlaces: amountPlaces,
                    fillClockinTimes: fillClockinTimes,
                    bonusFullAttendance: bonusFullAttendance,
                    bonusDuty: bonusDuty,
                    bonusBid1: bonusBid1,
                    bonusBid2: bonusBid2,
                    bonusBidLeap: bonusBidLeap,
                    priceWH1: priceWH1,
                    priceWH1Department: priceWH1Department,
                    priceWH2: priceWH2,
                    priceWH2Department: priceWH2Department,
                    priceWR: priceWR,
                    deductionSocialSecurity: deductionSocialSecurity
                };
                Public.ajaxPost('/Base/Option/updateSystem', data, function(res) {
                    if (res && 200 == res.status) {
                        $.dialog.tips('系统参数保存成功！');
                        $.extend(defaultPage.SYSTEM, data);
                    } else {
                        $.dialog.tips(res.msg, 2);
                    }
                }, function(err) {
                    $.dialog.tips(err.msg, 2);
                });
            }
        });
    }
};
$(function() {
    THISPAGE.init();
});
