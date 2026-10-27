var api = frameElement.api;
var defaultPage = Public.getDefaultPage();
var rowData = {};
var animalCombo, astroCombo;
var THISPAGE = {
    init: function() {
        Public.ajaxPost('/Base/User/query', {
            id: defaultPage.SYSTEM.uId
        }, function(res) {
            if (200 == res.status) {
                rowData = res.data;
                THISPAGE.initBtns();
                THISPAGE.initDom();
                THISPAGE.initCombo();
                THISPAGE.initEvent();
                THISPAGE.initValidator();
            } else {
                $.dialog.tips('获取数据失败！' + res.msg, 2);
            }
        });
    },
    initBtns: function() {
        api.button({
            id: 'confirm',
            name: '确定',
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
        rowData.id && (
            $('#wechat').val(rowData.wechat),
            $('#mobile').val(rowData.mobile),
            $('#birthdaySolar').val(rowData.birthdaySolar),
            $('#birthdayLunar').val(rowData.birthdayLunar)
        );
    },
    initCombo: function() {
        animalCombo = $('#animal').combo({
            data: [{id: 0, name: '鼠'}, {id: 1, name: '牛'}, {id: 2, name: '虎'}, {id: 3, name: '兔'}, {id: 4, name: '龙'}, {id: 5, name: '蛇'}, {id: 6, name: '马'}, {id: 7, name: '羊'}, {id: 8, name: '猴'}, {id: 9, name: '鸡'}, {id: 10, name: '狗'}, {id: 11, name: '猪'}],
            value: 'id',
            text: 'name',
            defaultSelected: rowData.animal ? ['name', rowData.animal] : -1
        }).getCombo();
        astroCombo = $('#astro').combo({
            data: [{id: 0, name: '水瓶座'}, {id: 1, name: '双鱼座'}, {id: 2, name: '白羊座'}, {id: 3, name: '金牛座'}, {id: 4, name: '双子座'}, {id: 5, name: '巨蟹座'}, {id: 6, name: '狮子座'}, {id: 7, name: '处女座'}, {id: 8, name: '天秤座'}, {id: 9, name: '天蝎座'}, {id: 10, name: '射手座'}, {id: 11, name: '摩羯座'}],
            value: 'id',
            text: 'name',
            defaultSelected: rowData.astro ? ['name', rowData.astro] : -1
        }).getCombo();
    },
    initEvent: function() {
    },
    initValidator: function() {
        $('#manage-form').validate({
            rules: {
                wechat: {required: !0},
                mobile: {required: !0}
            }
        });
    },
    postData: function(a, b) {
        if (!$('#manage-form').validate().form()) {
            return void $('#manage-form').find('input.valid-error').eq(0).focus();
        }
        Public.ajaxPost('/Base/User/setInfo', {
            wechat: $.trim($('#wechat').val()),
            mobile: $.trim($('#mobile').val()),
            birthdaySolar: $.trim($('#birthdaySolar').val()),
            birthdayLunar: $.trim($('#birthdayLunar').val()),
            astro: astroCombo.getText() || '',
            animal: animalCombo.getText() || ''
        }, function(res) {
            if (res && 200 == res.status) {
                parent.parent.$.dialog.tips('资料修改成功！');
                this.api.close();
            } else {
                $.dialog.tips(res.msg, 2);
            }
        });
    }
};
THISPAGE.init();
