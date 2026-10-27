var urlParam = Public.urlParam();
var defaultPage = Public.getDefaultPage();
var THISPAGE = {
    init: function() {
        THISPAGE.initEvent();
    },
    initEvent: function() {
        $('.wrapper').on('click', '#clearUpload', function(a) {
            if (Business.verifyRight('SYS_ADMIN')) {
                $('.wrapper').append('<div id="div-lock"><div class="loading">自动清理中...</div></div>');
                $.dialog.confirm('本功能禁止频繁操作，确定执行清理任务？', function() {
                    Public.ajaxGet('/Base/Tool/clearWaste', {
                        type: 'clearUpload'
                    }, function(res) {
                        if (res && 200 == res.status) {
                            $.dialog.tips('清理成功！');
                            $('.wrapper > #div-lock').remove();
                        }
                    });
                }, function() {
                    $('.wrapper > #div-lock').remove();
                });
            }
        });
    }
};
$(function() {
    THISPAGE.init();
});
