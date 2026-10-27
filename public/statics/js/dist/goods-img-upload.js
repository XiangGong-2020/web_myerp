var api = frameElement.api;
var apiData = api.data || {};
var imgList = {};
var THISPAGE = {
    init: function() {
        THISPAGE.initBtns();
        THISPAGE.initDom();
        THISPAGE.initEvent();
    },
    initBtns: function() {
        api.button({
            id: 'uploadImg',
            name: '上传',
            defClass: 'ui_state_default uploadImg'
        }, {
            id: 'ok',
            name: '保存',
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
        apiData.id && Public.ajaxPost('/Base/Goods/getImgByInvId', {
            goodsId: apiData.id
        }, function(res) {
            if (200 == res.status) {
                THISPAGE.addImgDiv(res.imgs);
            } else {
                $.dialog.tips('获取产品图片失败！' + res.msg, 2);
            }
        });
    },
    initEvent: function() {
        parent.$('.uploadImg').uploader({
            action: '/Base/Goods/uploadImg',
            mode: 'html5',
            name: 'file',
            formData: {},
            multiple: true,
            auto: true,
            debug: false,
            preview: false,
            showQueue: '#queue',
            fileSizeLimit: '10M',
            fileTypeDesc: '选择图片文件',
            fileTypeExts: 'jpg,jpeg,gif,png',
            onSuccess: function(res) {
                var data = JSON.parse(res.data);
                imgList.unshift(data.img);
                THISPAGE.addImgDiv(imgList);
            }
        });
        $('.upload-el').on({
            click: function(a) {
                !Business.verifyRight('GOODS_UPDATE||PO_ADD') && a.preventDefault();
            },
            mouseover: function() {
                $(this).prev().addClass('black');
            },
            mouseleave: function() {
                $(this).prev().removeClass('black');
            }
        });
        $('#imgs').on('click', '.imgDiv', function() {
            var a = $(this).find('img').prop('src');
            window.open(a);
        });
        $('#imgs').on('click', '.del', function(a) {
            a.stopPropagation();
            var img = $(this).parent().parent().find('img').attr('src').replace('/data/upfile/goods/img/', '');
            imgList = imgList.filter(item => item != img); // 从数组去掉值为img的属性
            THISPAGE.addImgDiv(imgList);
        });
    },
    postData: function() {
        if (Business.verifyRight('GOODS_UPDATE||PO_ADD')) {
            var imgs = [];
            for (var b in imgList) {
                if (imgList[b]){
                    imgs.push(imgList[b]);
                }
            }
            if (apiData.id) {
                var data = {
                    id: apiData.id,
                    imgs: imgs
                };
                Public.ajaxPost('/Base/Goods/saveImg', {
                    postData: JSON.stringify(data)
                }, function(res) {
                    if (res && 200 == res.status) {
                        parent.$.dialog.tips('产品图片保存成功！');
                        if (apiData.callback && 'function' == typeof apiData.callback) {
                            apiData.callback(res.data, window);
                        }
                    } else {
                        $.dialog.tips('产品图片保存失败！' + res.msg, 2);
                    }
                });
            }
        }
    },
    addImgDiv: function(a) {
        var imgDiv = '';
        var liList = $('#imgs');
        imgList = a;
        $.each(a, function(b, c) {
            if (liList) {
                if (c.startsWith('http')) {
                    imgDiv += '<div class="imgDiv"><p class="imgControl"><span class="controlBtn see iconfont icon-xianshi" title="查看"></span><span class="controlBtn del iconfont icon-shanchu" title="删除"></span><span class="db fl mt5 ml5 w20 h20 lh20 tc white bg-purple b-r-10" title="灵通同步产品图">网</span></p><img src="' + c + '"/></div>';
                } else {
                    imgDiv += '<div class="imgDiv"><p class="imgControl"><span class="controlBtn see iconfont icon-xianshi" title="查看"></span><span class="controlBtn del iconfont icon-shanchu" title="删除"></span></p><img src="/data/upfile/goods/img/' + c + '"/></div>';
                }
            }
        });
        liList.find('.imgDiv').remove();
        liList.append(imgDiv);
    }
};
$(function() {
    THISPAGE.init();
});
