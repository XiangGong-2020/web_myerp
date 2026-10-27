var api = frameElement.api;
var defaultPage = Public.getDefaultPage();
var THISPAGE = {
    init: function() {
        THISPAGE.initBtns();
        THISPAGE.initDom();
        THISPAGE.initEvent();
    },
    initBtns: function() {
        api.button({
            id: 'confirm',
            name: '确定',
            defClass: 'ui_state_highlight'
        }, {
            id: 'cancel',
            name: '关闭'
        });
    },
    initDom: function() {
        $('#avatar').html('<img src="/data/upfile/avatar/' + (defaultPage.SYSTEM.user.avatar == '' ? 'avatar.jpg' : defaultPage.SYSTEM.user.avatar) + '">');
    },
    initEvent: function() {
        $('#upimg').uploader({
            action: '/Base/User/setAvatar',
            mode: 'html5',
            name: 'file',
            formData: {},
            multiple: false,
            auto: true,
            debug: false,
            preview: false,
            showQueue: '#queue',
            fileSizeLimit: '10M',
            fileTypeDesc: '选择图片文件',
            fileTypeExts: 'jpg,jpeg,gif,png',
            onStart: function(e) {
                $('#manage-wrapper').prepend('<div id="div-lock"><div class="loading">上传中...</div></div>');
            },
            onError: function(e) {
                $.dialog.tips('上传错误！', 2);
            },
            onSuccess: function(e) {
                var data = JSON.parse(e.data);
                if (data.data) {
                    defaultPage.SYSTEM.user.avatar = data.data
                    $('#avatar').html('<img src="/data/upfile/avatar/' + data.data + '">');
                    parent.$('.l-tab-user-avatar').css({'background': 'url("/data/upfile/avatar/' + data.data + '") center center / 100% 100% no-repeat'});
                    parent.frames['index'] && parent.frames['index'].$('#user-avatar').css("background-image", "url('../data/upfile/avatar/" + data.data + "')");
                }
            },
            onAllComplete: function() {
                $('#manage-wrapper > #div-lock').remove();
            }
        });
        $('.upload-el').on({
            mouseover: function() {
                $(this).prev().addClass('black');
            },
            mouseleave: function() {
                $(this).prev().removeClass('black');
            }
        });
    }
};
THISPAGE.init();
