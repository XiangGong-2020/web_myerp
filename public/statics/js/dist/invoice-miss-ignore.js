var api = frameElement.api;
var defaultPage = Public.getDefaultPage();
var oper = api.data.oper;
var type = api.data.type;
var id = api.data.id;
var billType = api.data.billType;
var callback = api.data.callback;
var rowData = {};
var THISPAGE = {
    init: function() {
        if (void 0 !== id && oper == 'edit') {
            Public.ajaxPost('/Base/Invoice/ignoreMissQuery', {
                id: id,
                billType: billType
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
        api.button({
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
            $('#ignoreInvoiceReason').val(rowData.ignoreInvoiceReason);
            if (rowData.ignoreInvoiceImgs) {
                THISPAGE.addImgs(rowData.ignoreInvoiceImgs);
            }
        }
    },
    initEvent: function() {
        $('#upimg').uploader({
            action: '/Base/Invoice/ignoreMissUploadImg',
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
            fileTypeExts: 'jpg,jpeg,png',
            onStart: function(e) {
                $('#manage-wrap').prepend('<div id="div-lock"><div class="loading">上传中...</div></div>');
            },
            onError: function(e) {
                $.dialog.tips('上传错误！', 2);
            },
            onSuccess: function(e) {
                var data = JSON.parse(e.data);
                if (data.data) {
                    if (!rowData.ignoreInvoiceImgs) {
                        rowData.ignoreInvoiceImgs = [];
                        rowData.ignoreInvoiceImgs[0] = data.data;
                    } else {
                        rowData.ignoreInvoiceImgs.push(data.data);
                    }
                    THISPAGE.addImgs(rowData.ignoreInvoiceImgs);
                }
            },
            onAllComplete: function() {
                $('#manage-wrap > #div-lock').remove();
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
        $('#ignoreInvoiceImgs').on('click', '.imgDiv', function() {
            var a = $(this).find('img').prop('src');
            window.open(a);
        });
        $('#ignoreInvoiceImgs').on('click', '.del', function(a) {
            a.stopPropagation();
            var b = $(this).closest('.imgDiv').hide().data('pid');
            rowData.ignoreInvoiceImgs.splice(b, 1) && THISPAGE.addImgs(rowData.ignoreInvoiceImgs);
        });
    },
    addImgs: function(imgs) {
        var imgDiv = '';
        $.each(imgs, function(k, v) {
            imgDiv += '<div class="imgDiv" data-pid="' + k + '"><p class="imgControl"><span class="controlBtn see iconfont icon-xianshi" title="查看"></span><span class="controlBtn del iconfont icon-shanchu" title="删除"></span></p><img src="/data/upfile/invoiceMiss/img/' + v + '"/></div>';
        });
        $('#ignoreInvoiceImgs').html(imgDiv);
    },
    initValidator: function() {
        $('#manage-form').validate({
            rules: {
                ignoreInvoiceReason: {required: !0}
            }
        });
    },
    postData: function() {
        if (!$('#manage-form').validate().form()) {
            return void $('#manage-form').find('input.valid-error').eq(0).focus();
        }
        var data = THISPAGE.getData();
        var title = 'add' == oper ? '新增' : '编辑';
        Public.ajaxPost('/Base/Invoice/ignoreMiss', data, function(res) {
            if (res && 200 == res.status) {
                parent.parent.$.dialog.tips(title + '成功！');
                if (callback && 'function' == typeof callback) {
                    callback(res.data, oper, window);
                };
            } else {
                $.dialog.tips(title + '失败！' + res.msg, 2);
            }
        });
    },
    getData: function() {
        var data = {
            oper: oper,
            billType: billType,
            type: type,
            id: id,
            ignoreInvoiceImgs: rowData.ignoreInvoiceImgs ? rowData.ignoreInvoiceImgs.toString() : '',
            ignoreInvoiceReason: $.trim($('#ignoreInvoiceReason').val())
        };
        return data;
    }
};
$(function() {
    THISPAGE.init();
});
