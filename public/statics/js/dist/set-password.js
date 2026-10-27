var api = frameElement.api;
var defaultPage = Public.getDefaultPage();
var THISPAGE = {
    init: function() {
        THISPAGE.initBtns();
        THISPAGE.initDom();
        THISPAGE.initValidator();
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
        $('#name').val(defaultPage.SYSTEM.user.name);
        $('#userName').val(defaultPage.SYSTEM.user.userName);
    },
    initValidator: function() {
        $.validator.addMethod('checkPwd', function(a) {
            // 6 ~ 20位数字与字母(区分大小写)
            return a ? /[0-9a-zA-Z]{6,20}/.test(a) : !1;
        });
        $('#manage-form').validate({
            rules: {
                password: {required: !0, checkPwd: !0},
                confirmPassword: {required: !0, equalTo: '#password'}
            }
        });
    },
    postData: function(a, b) {
        if (!$('#manage-form').validate().form()) {
            return void $('#manage-form').find('input.valid-error').eq(0).focus();
        }
        Public.ajaxPost('/Base/User/setPassword', {
            userPwd: $.trim($('#password').val())
        }, function(res) {
            if (res && 200 == res.status) {
                parent.parent.$.dialog.tips('密码修改成功！');
                this.api.close();
            } else {
                $.dialog.tips(res.msg, 2);
            }
        });
    }
};
THISPAGE.init();
