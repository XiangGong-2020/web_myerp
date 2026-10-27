var defaultPage = Public.getDefaultPage();
//开始登录检查
function login() {
    var cookieEnabled = (navigator.cookieEnabled) ? true : false;
    if (!cookieEnabled) {
        alert("该浏览器Cookie设置不正确，无法正常登录！");
        return false;
    }
    var userName = $.trim($("#userName").val());
    var userPwd = $.trim($("#userPwd").val());
    var token = $('#token').val();
    if (checkNullOrEmpty(userName)) {
        $("#userName").focus();
        return false;
    }
    if (checkNullOrEmpty(userPwd)) {
        $("#userPwd").focus();
        return false;
    }
    Public.ajaxPost('/login', {userName: userName, userPwd: userPwd, token: token}, function(data) {
        if (data.status == 200) {
            location.href = "/";
        } else {
            $("#loginerror").text(data.msg).show(100).delay(2000).hide(100);
            setTimeout("location.href='/login'", 2000);
            return false;
        }
    });
}
//检查字符串是否为空
function checkNullOrEmpty(str) {
    var result = false;
    if (str == null) {
        result = true;
    }
    str = str.replace(/(^\s*)|(\s*$)/g, "");
    if (str.length == 0) {
        result = true;
    }
    return result;
}
$(function() {
    document.onkeydown = function(a) {
        var curKey = a.which;
        if (curKey == 13) {
            $(".login-btn").trigger("click");
        }
    };
    $('.forgot-pwd').on(
        {
            mouseover: function() {
                $('.notice').show(100);
            },
            mouseleave: function() {
                $('.notice').hide(100);
            }
        }
    );
});
