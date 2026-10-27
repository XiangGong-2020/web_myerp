var api = frameElement.api;
var defaultPage = Public.getDefaultPage();
var oper = api.data.oper;
var cRowId = api.data.rowId;
var callback = api.data.callback;
var rowData = {};
var THISPAGE = {
    init: function() {
        if (void 0 !== cRowId) {
            Public.ajaxPost('/Base/Note/query', {
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
    },
    initDom: function() {
        if ('edit' == oper) {
            $('#title').val(rowData.title);
            $('#content').val(rowData.content);
            if (rowData.files) {
                THISPAGE.addFiles(rowData.files);
            }
        }
    },
    initEvent: function() {
        $('#upfile').uploader({
            action: '/Base/Note/uploadFile',
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
        $('#files').on('click', '.del', function(a) {
            a.stopPropagation();
            if (rowData.uName !== defaultPage.SYSTEM.uName) {
                $.dialog.tips('禁止删除别人上传的文件！', 2);
            } else {
                var b = $(this).closest('.fileDiv').hide().data('pid');
                rowData.files.splice(b, 1) && THISPAGE.addFiles(rowData.files);
            }
        });
    },
    addFiles: function(files) {
        var fileDiv = '';
        $.each(files, function(k, v) {
            fileDiv += '<div class="fileDiv" data-pid="' + k + '"><p class="fileControl"><span class="controlBtn del iconfont icon-shanchu" title="删除"></span></p><a target="_blank" href="/data/upfile/note/file/' + v.url + '" title="点击查看或下载"><span>' + v.name + '</span></a></div>';
        });
        $('#files').html(fileDiv);
    },
    initValidator: function() {
        $('#manage-form').validate({
            rules: {
                title: {required: !0}
            }
        });
    },
    postData: function(callbackType = 0) {
        if (!$('#manage-form').validate().form()) {
            return void $('#manage-form').find('input.valid-error').eq(0).focus();
        }
        var data = THISPAGE.getData();
        var title = 'add' == oper ? '新增私人备忘' : '编辑私人备忘';
        data && Public.ajaxPost('/Base/Note/' + ('add' == oper ? 'add' : 'update'), data, function(res) {
            if (res && 200 == res.status) {
                parent.parent.$.dialog.tips(title + '成功！');
                if (callback && 'function' == typeof callback) {
                    callback(res.data, oper, window, callbackType);
                };
            } else {
                $.dialog.tips(title + '失败！' + res.msg, 2);
            }
        });
    },
    getData: function() {
        var data = {
            id: cRowId || 0,
            title: $.trim($('#title').val()),
            content: $.trim($('#content').val()),
            files: rowData.files ? JSON.stringify(rowData.files) : ''
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
        $('#title').val('');
        $('#content').val('');
        $('#files').html('');
        $(rowData).removeProp("files");
    }
};
$(function() {
    THISPAGE.init();
});
