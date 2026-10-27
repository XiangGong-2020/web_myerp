
var defaultPage = Public.getDefaultPage();
var backgroundColorArr = ['#4dc9f6', '#ff61c5', '#acc236', '#00aa9c', '#00a950', '#ff8787', '#5773ff', '#f67019', '#6f56ff', '#7784bb'];
var THISPAGE = {
    init: function() {
        THISPAGE.initDom();
        THISPAGE.initEvent();
    },
    initDom: function() {
        Public.ajaxPost('/Base/User/myRights', {}, function(res) {
            if (res.status == 200) {
                var rightsDiv = '';
                var rightsText = '';
                $.each(res.data, function(i, v) {
                    if (v.level == 1 && i > 0) {
                        rightsDiv += '<div class="rights-item" style="background-color:' + backgroundColorArr[parseInt((i + "").charAt((i + "").length - 1))] + '">' + rightsText + '</div>';
                        rightsText = '';
                    }
                    if (v.level == 1) {
                        rightsText += ('<span class="fl db pl10 pr10 mr10 f12 fb lh30 b-r-3" style="background:#00000033;">' + v.name + '</span>' + ' 查看');
                    } else {
                        rightsText += (' ' + v.name);
                    }
                    if (i == (res.data.length - 1)) {
                        rightsDiv += '<div class="rights-item" style="background-color:' + backgroundColorArr[parseInt((i + "").charAt((i + "").length - 1))] + '">' + rightsText + '</div>';
                    }
                });
                $('.diy-con').html(rightsDiv);
            } else {
                $.dialog.tips(res.msg, 2);
            }
        });
    },
    initEvent: function() {
        $('#refresh').on('click', function() {
            window.location.reload();
        });
    }
};
$(function() {
    THISPAGE.init();
});
