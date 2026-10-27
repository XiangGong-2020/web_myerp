var api = frameElement.api;
var defaultPage = Public.getDefaultPage();
var oper = api.data.oper;
var cRowId = api.data.rowId;
var type = api.data.type;
var callback = api.data.callback;
var rowData = {};
var authorNameCombo;
var proofNameCombo;
var typeObj = {
    goodsFileOne: '中文图纸',
    goodsFileTwo: '中文教程',
    goodsFileOne_EN: '外文图纸',
    goodsFileTwo_EN: '外文教程',
    goodsFileThree: '程序文件',
    goodsFileFour: 'PCB文件',
    goodsFileFive: '激光文件',
    goodsFileSix: '设计文件'
};
var THISPAGE = {
    init: function() {
        if (void 0 !== cRowId) {
            Public.ajaxPost('/Base/Goods/fileQuery', {
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
            $('#no').val(rowData.no);
            $('#name').val(rowData.name);
            $('#name').attr('title', rowData.name);
            $('#name').data('url', rowData.url);
            $('#authorName').val(rowData.authorName);
            $('#proofName').val(rowData.proofName);
            $('#printSize').val(rowData.printSize);
            if (api.config.disEdit) {
                $('#no').attr('disabled', true).addClass('ui-input-dis');
                $('#name').attr('disabled', true).addClass('ui-input-dis');
                $('#printSize').attr('disabled', true).addClass('ui-input-dis');
            }
        }
        if (type == 'goodsFileFive') {
            $('#printSize').attr('placeholder', '例如：50平方厘米');
            $('#printSize').parent().prev().children('label').html('面积');
        }
        authorNameCombo = $('#authorName').combo({
            data: '/Base/User/queryAll?normal=yes&department=技术部',
            ajaxOptions: {
                formatData: function(a) {
                    return a.data.rows.unshift({
                        id: 2,
                        name: '叶超'
                    }, {
                        id: 84,
                        name: '杨颐'
                    }, {
                        id: 96,
                        name: '陈雄浩'
                    }), a.data.rows;
                }
            },
            value: 'id',
            text: 'name',
            disabled: api.config.disEdit ? !0 : !1,
            defaultSelected: rowData.authorName ? ['name', rowData.authorName] : -1,
            listHeight: 155
        }).getCombo();
        proofNameCombo = $('#proofName').combo({
            data: '/Base/User/queryAll?normal=yes&department=技术部',
            ajaxOptions: {
                formatData: function(a) {
                    return a.data.rows.unshift({
                        id: 2,
                        name: '叶超'
                    }, {
                        id: 84,
                        name: '杨颐'
                    }, {
                        id: 96,
                        name: '陈雄浩'
                    }), a.data.rows;
                }
            },
            value: 'id',
            text: 'name',
            disabled: api.config.disEdit ? !0 : !1,
            defaultSelected: rowData.proofName ? ['name', rowData.proofName] : -1,
            listHeight: 155
        }).getCombo();
        $('#authorName').find('input').attr('name', 'authorNameInput');
        $('#proofName').find('input').attr('name', 'proofNameInput');
    },
    initEvent: function() {
        if (!api.config.disEdit) {
            $('#upFile').uploader({
                action: '/Base/Goods/uploadfile',
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
                fileTypeExts: (type == 'goodsFileOne' || type == 'goodsFileTwo' || type == 'goodsFileOne_EN' || type == 'goodsFileTwo_EN') ? 'pdf' : 'zip,rar,7z,iso',
                onSuccess: function(e) {
                    var data = JSON.parse(e.data);
                    $('#name').val(data.file.name);
                    $('#name').attr('title', data.file.name);
                    $('#name').data('url', data.file.url);
                },
                onError: function(e) {
                    e.message && $.dialog.tips('上传失败！' + e.message, 2);
                },
                onCancel: function(e) {
                    e.name && $.dialog.tips('已取消文件' + e.name + '上传！', 2);
                },
                onAllComplete: function() {
                    $('.upload-queue .queue ul').html('上传成功！').addClass('tc');
                }
            });
            $('.upload-el').on({
                click: function(a) {
                    !Business.verifyRight(type.split('_')[0].toUpperCase() + '_UPDATE') && a.preventDefault();
                },
                mouseover: function() {
                    $(this).prev().addClass('black');
                },
                mouseleave: function() {
                    $(this).prev().removeClass('black');
                }
            });
        }
    },
    initValidator: function() {
        var rules = {
            no: {required: !0},
            name: {required: !0},
            authorNameInput: {required: !0},
            proofNameInput: {required: !0}
        };
        $('#manage-form').validate({
            rules: rules
        });
    },
    postData: function(callbackType = 0) {
        if (Business.verifyRight(type.split('_')[0].toUpperCase() + '_UPDATE')) {
            if (!$('#manage-form').validate().form()) {
                return void $('#manage-form').find('input.valid-error').eq(0).focus();
            }
            var title = 'add' == oper ? '新增' : '编辑';
            var data = THISPAGE.getData();
            data && Public.ajaxPost('/Base/Goods/' + ('add' == oper ? 'fileAdd' : 'fileUpdate'), data, function(res) {
                if (res && 200 == res.status) {
                    parent.parent.$.dialog.tips(title + '成功！');
                    if (callback && 'function' == typeof callback) {
                        callback(res.data, oper, window, callbackType);
                    }
                } else {
                    $.dialog.tips(title + '失败！' + res.msg, 2);
                }
            });
        }
    },
    getData: function() {
        var data = {
            id: cRowId || 0,
            type: type,
            no: $.trim($('#no').val()),
            name: $.trim($('#name').val()),
            url: $.trim($('#name').data('url')),
            authorName: authorNameCombo.getText(),
            proofName: proofNameCombo.getText(),
            printSize: $.trim($('#printSize').val())
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
        $('#no').val('');
        $('#name').val('');
        $('#name').data('url','');
        authorNameCombo._setDefaultSelected(-1);
        proofNameCombo._setDefaultSelected(-1);
        $('#printSize').val('');
    }
};
$(function() {
    THISPAGE.init();
});
