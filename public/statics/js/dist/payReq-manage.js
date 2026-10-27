var api = frameElement.api;
var defaultPage = Public.getDefaultPage();
var oper = api.data.oper;
var cRowId = api.data.rowId;
var callback = api.data.callback;
var rowData = {};
var expendTypeCombo;
var levelCombo;
var THISPAGE = {
    init: function() {
        if (void 0 !== cRowId) {
            Public.ajaxPost('/Base/PayReq/query', {
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
            $('#amount').val(rowData.amount);
            $('#content').html(rowData.content);
            $('#description').html(rowData.description);
            $('#createTime').html('申请人：' + rowData.uName + '， 申请时间：' + rowData.createTime);
            if (rowData.imgs) {
                THISPAGE.addImgs(rowData.imgs);
            }
            if (api.config.disEdit) {
                $('#title').attr('disabled', true).addClass('ui-input-dis');
                $('#amount').attr('disabled', true).addClass('ui-input-dis');
                $('#content').attr('disabled', true).addClass('ui-input-dis');
                $('#description').attr('disabled', true).addClass('ui-input-dis');
            }
            if (rowData.uId != defaultPage.SYSTEM.uId || rowData.checkId > 0 || rowData.payId > 0) {
                $('#title').attr('disabled', true).addClass('ui-input-dis');
                $('#amount').attr('disabled', true).addClass('ui-input-dis');
                $('#description').attr('disabled', true).addClass('ui-input-dis');
            }
        } else {
            $('#description').attr({readonly: 'readonly'});
        }
        expendTypeCombo = Business.categoryCombo($('#expendType'), {
            async: false, // 异步 否
            listWidth: 324,
            listHeight: 300,
            defaultSelected: rowData.expendTypeId > 0 ? ['id', rowData.expendTypeId] : -1,
            disabled: (api.config.disEdit || ('edit' == oper && (rowData.uId != defaultPage.SYSTEM.uId || rowData.checkId > 0 || rowData.payId > 0))) ? !0 : !1
        }, 'expendtype');
        levelCombo = $('#level').combo({
            data: [
                {id: 0, name: '不急'},
                {id: 1, name: '急'},
                {id: 2, name: '很急'}
            ],
            value: 'id',
            text: 'name',
            defaultSelected: rowData.level >= 0 ? ['id', rowData.level] : -1,
            disabled: (api.config.disEdit || ('edit' == oper && (rowData.uId != defaultPage.SYSTEM.uId || rowData.checkId > 0 || rowData.payId > 0))) ? !0 : !1
        }).getCombo();
        $('#expendType').find('input').attr('name', 'expendTypeInput');
        $('#level').find('input').attr('name', 'levelInput');
    },
    initValidator: function() {
        $('#manage-form').validate({
            rules: {
                title: {required: !0},
                amount: {required: !0},
                expendTypeInput: {required: !0},
                levelInput: {required: !0}
            }
        });
    },
    initEvent: function() {
        if (!api.config.disEdit) {
            $('#upimg').uploader({
                action: '/Base/PayReq/uploadImg',
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
            $('.upload-el').on({
                click: function(a) {
                    !Business.verifyRight('PAYREQ_QUERY') && a.preventDefault();
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
        }
    },
    addImgs: function(imgs) {
        var imgDiv = '';
        $.each(imgs, function(k, v) {
            imgDiv += '<div class="imgDiv" data-pid="' + k + '"><p class="imgControl"><span class="controlBtn see iconfont icon-xianshi" title="查看"></span><span class="controlBtn del iconfont icon-shanchu" title="删除"></span></p><img src="/data/upfile/payReq/' + v + '"/></div>';
        });
        $('#imgs').html(imgDiv);
    },
    postData: function(callbackType = 0) {
        if ($.inArray('PAYREQ_ADVANCE', defaultPage.SYSTEM.rights) !== -1 || rowData.uId == defaultPage.SYSTEM.uId || (void 0 == cRowId && $.inArray('PAYREQ_QUERY', defaultPage.SYSTEM.rights) !== -1)) {
            if (!$('#manage-form').validate().form()) {
                return void $('#manage-form').find('input.valid-error').eq(0).focus();
            }
            var data = THISPAGE.getData();
            var title = 'add' == oper ? '新增' : '编辑';
            data && Public.ajaxPost('/Base/PayReq/' + ('add' == oper ? 'add' : 'update'), data, function(res) {
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
            $.dialog.tips('不能编辑别人的付款申请！仅本人或高级权限可编辑！', 2);
            return void 0;
        }
    },
    getData: function() {
        var data = {
            id: cRowId || 0,
            type: 1,
            title: $.trim($('#title').val()),
            amount: Number($.trim($('#amount').val())),
            expendTypeId: expendTypeCombo.getValue() || 0,
            expendTypeName: expendTypeCombo.getText() || '',
            level: levelCombo.getValue() || 0,
            content: $.trim($('#content').val()),
            imgs: rowData.imgs ? rowData.imgs.toString() : '',
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
        $('#title').val('');
        $('#amount').val('');
        expendTypeCombo._setDefaultSelected(-1);
        levelCombo._setDefaultSelected(-1);
        $('#content').val('');
        $('#imgs').html('');
        $('#description').val('');
    }
};
$(function() {
    THISPAGE.init();
});
