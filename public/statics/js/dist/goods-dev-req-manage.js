var api = frameElement.api;
var defaultPage = Public.getDefaultPage();
var oper = api.data.oper;
var cRowId = api.data.rowId;
var callback = api.data.callback;
var rowData = {};
var THISPAGE = {
    init: function() {
        if (void 0 !== cRowId) {
            Public.ajaxPost('/Base/GoodsDevReq/query', {
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
        if ('edit' == oper) {
            $('#title').val(rowData.title);
            $('#lastDate').val(rowData.lastDate);
            $('#content').val(rowData.content);
            $('#backInfo').val(rowData.backInfo);
            $('#description').val(rowData.description);
            $('#createTime').html('申请人：' + rowData.uName + '， 申请时间：' + rowData.createTime);
            if (rowData.imgs) {
                THISPAGE.addImgs(rowData.imgs);
            }
            if (rowData.files) {
                THISPAGE.addFiles(rowData.files);
            }
            if (api.config.disEdit) {
                $('#title').attr('disabled', true).addClass('ui-input-dis');
                $('#content').attr('disabled', true).addClass('ui-input-dis');
                $('#backInfo').attr('disabled', true).addClass('ui-input-dis');
                $('#description').attr('disabled', true).addClass('ui-input-dis');
            }
        } else {
            $('#lastDate').val(Public.addDays((new Date).format('yyyy-MM-dd'), 3) + ' 17:00:00');
            $('#backInfo').attr({readonly: 'readonly'});
            $('#description').attr({readonly: 'readonly'});
        }
    },
    initEvent: function() {
        if (!api.config.disEdit) {
            laydate.render({
                elem: "#lastDate",
                type: 'datetime'
            });
            $('#upimg').uploader({
                action: '/Base/GoodsDevReq/uploadImg',
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
                    $('#manage-wrap').prepend('<div id="div-lock"><div class="loading">上传中...</div></div>');
                },
                onError: function(e) {
                    $.dialog.tips('上传错误！', 2);
                },
                onSuccess: function(e) {
                    var data = JSON.parse(e.data);
                    if (data.data) {
                        if (!rowData.imgs) {
                            rowData.imgs = [];
                            rowData.imgs[0] = data.data;
                        } else {
                            rowData.imgs.push(data.data);
                        }
                        THISPAGE.addImgs(rowData.imgs);
                    }
                },
                onAllComplete: function() {
                    $('#manage-wrap > #div-lock').remove();
                }
            });
            $('#upfile').uploader({
                action: '/Base/GoodsDevReq/uploadFile',
                mode: 'html5',
                name: 'file',
                formData: {},
                multiple: false,
                auto: true,
                debug: false,
                preview: false,
                showQueue: '#queue',
                fileSizeLimit: '2G',
                fileTypeDesc: '选择文件',
                fileTypeExts: 'csv,xls,xlsx,doc,docx,xps,pdf,txt | zip,rar,7z,iso',
                onStart: function(e) {
                    $('#manage-wrap').prepend('<div id="div-lock"><div class="loading">上传中...</div></div>');
                },
                onError: function(e) {
                    $.dialog.tips('上传错误！', 2);
                },
                onSuccess: function(e) {
                    var data = JSON.parse(e.data);
                    if (data.data) {
                        if (!rowData.files) {
                            rowData.files = [];
                            rowData.files[0] = data.data;
                        } else {
                            rowData.files.push(data.data);
                        }
                        THISPAGE.addFiles(rowData.files);
                    }
                },
                onAllComplete: function() {
                    $('#manage-wrap > #div-lock').remove();
                }
            });
            $('.upload-el').on({
                click: function(a) {
                    !Business.verifyRight('GOODSDEVREQ_QUERY') && a.preventDefault();
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
                if (rowData.uName !== defaultPage.SYSTEM.uName) {
                    $.dialog.tips('禁止删除别人上传的文件！', 2);
                } else {
                    var b = $(this).closest('.imgDiv').hide().data('pid');
                    rowData.imgs.splice(b, 1) && THISPAGE.addImgs(rowData.imgs);
                }
            });
            $('#files').on('click', '.del', function(a) {
                a.stopPropagation();
                if (rowData.uName !== defaultPage.SYSTEM.uName) {
                    $.dialog.tips('禁止删除别人上传的文件！', 2);
                } else {
                    var b = $(this).closest('.fileDiv').hide().data('pid');
                    rowData.files.splice(b, 1) && THISPAGE.addFiles(rowData.files);
                }
            });
        }
    },
    addImgs: function(imgs) {
        var imgDiv = '';
        $.each(imgs, function(k, v) {
            imgDiv += '<div class="imgDiv" data-pid="' + k + '"><p class="imgControl"><span class="controlBtn see iconfont icon-xianshi" title="查看"></span><span class="controlBtn del iconfont icon-shanchu" title="删除"></span></p><img src="/data/upfile/goodsTask/' + v + '"/></div>';
        });
        $('#imgs').html(imgDiv);
    },
    addFiles: function(files) {
        var fileDiv = '';
        $.each(files, function(k, v) {
            fileDiv += '<div class="fileDiv" data-pid="' + k + '"><p class="fileControl"><span class="controlBtn del iconfont icon-shanchu" title="删除"></span></p><a target="_blank" href="/data/upfile/goodsTask/' + v.url + '" title="点击查看或下载"><span>' + v.name + '</span></a></div>';
        });
        $('#files').html(fileDiv);
    },
    initValidator: function() {
        $('#manage-form').validate({
            rules: {
                title: {required: !0},
                lastDate: {required: !0}
            }
        });
    },
    postData: function(callbackType = 0) {
        if ($.inArray('GOODSDEVREQ_ADVANCE', defaultPage.SYSTEM.rights) !== -1 || rowData.uId == defaultPage.SYSTEM.uId || (void 0 == cRowId && $.inArray('GOODSDEVREQ_QUERY', defaultPage.SYSTEM.rights) !== -1)) {
            if (!$('#manage-form').validate().form()) {
                return void $('#manage-form').find('input.valid-error').eq(0).focus();
            }
            var data = THISPAGE.getData();
            var title = 'add' == oper ? '新增' : '编辑';
            Public.ajaxPost('/Base/GoodsDevReq/' + ('add' == oper ? 'add' : 'update'), data, function(res) {
                if (res && 200 == res.status) {
                    parent.parent.$.dialog.tips(title + '成功！');
                    if (callback && 'function' == typeof callback) {
                        callback(res.data, oper, window, callbackType);
                    };
                } else {
                    $.dialog.tips(title + '失败！' + res.msg, 2);
                }
            });
        } else {
            $.dialog.tips('不能编辑别人的申请！仅本人或高级权限可编辑！', 2);
            return void 0;
        }
    },
    getData: function() {
        var data = {
            id: cRowId || 0,
            lastDate: $.trim($('#lastDate').val()),
            title: $.trim($('#title').val()),
            content: $.trim($('#content').val()),
            imgs: rowData.imgs ? rowData.imgs.toString() : '',
            files: rowData.files ? JSON.stringify(rowData.files) : '',
            backInfo: $.trim($('#backInfo').val()),
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
        rowData = {};
        levelCombo._setDefaultSelected(-1);
        $('#title').val('');
        $('#content').val('');
        $('#imgs').html('');
        $('#files').html('');
        $(rowData).removeProp("imgs");
        $(rowData).removeProp("files");
        $('#backInfo').val('');
        $('#description').val('');
    }
};
$(function() {
    THISPAGE.init();
});
