var api = frameElement.api;
var defaultPage = Public.getDefaultPage();
var oper = api.data.oper;
var cRowId = api.data.rowId;
var callback = api.data.callback;
var rowData = {};
var THISPAGE = {
    init: function() {
        if (void 0 !== cRowId) {
            Public.ajaxPost('/Base/Bid/bidServiceQuery', {
                id: cRowId
            }, function(res) {
                if (200 == res.status) {
                    rowData = res.data;
                    THISPAGE.initBtns();
                    THISPAGE.initDom(rowData);
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
    initDom: function(rowData) {
        if ('edit' == oper) {
            $('#name').val(rowData.name);
            $('#mobile').val(rowData.mobile);
            $('#phone').val(rowData.phone);
            $('#qq').val(rowData.qq);
            $('#wechat').val(rowData.wechat);
            $('#beginDate').val(rowData.beginDate);
            mod_AreasCombo.init($('#province'), $('#city'), $('#area'), {listHeight: 282}, function() {
                THISPAGE.provinceCombo = mod_AreasCombo.provinceCombo;
                THISPAGE.cityCombo = mod_AreasCombo.cityCombo;
                THISPAGE.areaCombo = mod_AreasCombo.areaCombo;
                THISPAGE.provinceCombo.selectByText(rowData.province);
                THISPAGE.cityCombo.selectByText(rowData.city);
                THISPAGE.areaCombo.selectByText(rowData.area);
                $('#province').find('input').attr('name', 'provinceInput');
                $('#city').find('input').attr('name', 'cityInput');
                $('#area').find('input').attr('name', 'areaInput');
            });
            $('#address').val(rowData.address);
            $('#description').val(rowData.description);
            if (rowData.files) {
                THISPAGE.addFiles(rowData.files);
            }
        } else {
            mod_AreasCombo.init($('#province'), $('#city'), $('#area'), {listHeight: 282}, function() {
                THISPAGE.provinceCombo = mod_AreasCombo.provinceCombo;
                THISPAGE.cityCombo = mod_AreasCombo.cityCombo;
                THISPAGE.areaCombo = mod_AreasCombo.areaCombo;
                THISPAGE.provinceCombo.selectByText('');
                THISPAGE.cityCombo.selectByText('');
                THISPAGE.areaCombo.selectByText('');
                $('#province').find('input').attr('name', 'provinceInput');
                $('#city').find('input').attr('name', 'cityInput');
                $('#area').find('input').attr('name', 'areaInput');
            });
        }
    },
    initEvent: function() {
        laydate.render({elem: "#beginDate"});
        $('#upFile').uploader({
            action: '/Base/Bid/bidServiceUploadFile',
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
            fileTypeExts: 'jpg,gif,png,csv,xls,xlsx,doc,docx,xps,pdf,zip,rar,iso,7z',
            onSuccess: function(e) {
                var data = JSON.parse(e.data);
                if (data.file) {
                    if (!rowData.files) {
                        rowData.files = [];
                        rowData.files[0] = data.file;
                    } else {
                        rowData.files.unshift(data.file);
                    }
                    THISPAGE.addFiles(rowData.files);
                }
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
                !Business.verifyRight('BIDSERVICE_UPDATE') && a.preventDefault();
            },
            mouseover: function() {
                $(this).prev().addClass('black');
            },
            mouseleave: function() {
                $(this).prev().removeClass('black');
            }
        });
        $('#files').on('click', '.fileRename', function() {
            if (Business.verifyRight('BIDSERVICE_UPDATE')) {
                $('.manage-wrapper .renameInput').addClass('dn');
                $('.manage-wrapper .renameSumbit').addClass('dn');
                $('.manage-wrapper .renameCancel').addClass('dn');
                $(this).parent().children('.renameInput').removeClass('dn');
                $(this).parent().children('.renameSumbit').removeClass('dn');
                $(this).parent().children('.renameCancel').removeClass('dn');
            }
        });
        $('#files').on('click', '.renameSumbit', function() {
            var newName = $(this).parent().children('.renameInput').val();
            if (newName) {
                var key = $(this).closest('.file-line').data('key');
                rowData.files[key].name = newName;
                THISPAGE.addFiles(rowData.files);
            }
            $(this).addClass('dn');
            $(this).parent().children('.renameInput').addClass('dn');
            $(this).parent().children('.renameCancel').addClass('dn');
        });
        $('#files').on('click', '.renameCancel', function() {
            $(this).addClass('dn');
            $(this).parent().children('.renameInput').addClass('dn');
            $(this).parent().children('.renameSumbit').addClass('dn');
        });
        $('#files').on('click', '.fileDel', function(a) {
            a.stopPropagation();
            if (Business.verifyRight('BIDSERVICE_UPDATE')) {
                var upName = $(this).parent().data('upname');
                if (upName !== defaultPage.SYSTEM.uName) {
                    $.dialog.tips('禁止删除别人上传的文件！', 2);
                } else {
                    $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                        var key = $(this).closest('.file-line').data('key');
                        rowData.files.splice(key, 1) && THISPAGE.addFiles(rowData.files);
                    });
                }
            }
        });
    },
    addFiles: function(files) {
        var fileDiv = '';
        $.each(files, function(k, v) {
            fileDiv += (
                '<div class="file-line" data-key="' + k + '" data-upname="' + v.upName + '">' +
                '<a href="/data/upfile/bidService/' + v.url + '" target="_blank" title="' + v.name + '"><span>' + v.name + '</span><span>' + v.upName + '</span><span>' + v.upTime + '</span></a>' +
                '<input type="text" class="renameInput dn" placeholder="输入新文件名">' +
                '<span class="renameSumbit dn">确定</span><span class="renameCancel dn">取消</span>' +
                '<i class="fileRename iconfont icon-bianji" title="改名"></i><i class="fileDel iconfont icon-shanchu" title="删除"></i>' +
                '</div>'
            );
        });
        $('#files').html(fileDiv);
    },
    initValidator: function() {
        $('#manage-form').validate({
            rules: {
                name: {required: !0},
                mobile: {required: !0},
                beginDate: {required: !0},
                province: {required: !0}
            }
        });
    },
    postData: function(callbackType = 0) {
        if (Business.verifyRight('BIDSERVICE_UPDATE')) {
            if (!$('#manage-form').validate().form()) {
                return void $('#manage-form').find('input.valid-error').eq(0).focus();
            }
            var title = 'add' == oper ? '新增代办跑腿' : '编辑代办跑腿';
            var data = THISPAGE.getData();
            data && Public.ajaxPost('/Base/Bid/' + ('add' == oper ? 'bidServiceAdd' : 'bidServiceUpdate'), data, function(res) {
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
            id: void 0 !== cRowId ? cRowId : -1,
            name: $.trim($('#name').val()),
            mobile: $.trim($('#mobile').val()),
            phone: $.trim($('#phone').val()),
            qq: $.trim($('#qq').val()),
            wechat: $.trim($('#wechat').val()),
            province: THISPAGE.provinceCombo.getText(),
            city: THISPAGE.cityCombo.getText(),
            area: THISPAGE.areaCombo.getText(),
            address: $.trim($('#address').val()),
            beginDate: $('#beginDate').val(),
            description: $.trim($('#description').val()),
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
        rowData = {};
        $('#name').val('');
        $('#mobile').val('');
        $('#phone').val('');
        $('#qq').val('');
        $('#wechat').val('');
        THISPAGE.provinceCombo.selectByText('');
        THISPAGE.cityCombo.selectByText('');
        THISPAGE.areaCombo.selectByText('');
        $('#address').val('');
        $('#beginDate').val(defaultPage.SYSTEM.endDate);
        $('#description').val('');
        $('#files').html('');
    }
};
$(function() {
    THISPAGE.init();
});
