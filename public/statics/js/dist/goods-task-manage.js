var api = frameElement.api;
var defaultPage = Public.getDefaultPage();
var oper = api.data.oper;
var cRowId = api.data.rowId;
var callback = api.data.callback;
var cRowData = api.data.cRowData;
var rowData = {};
var content = '';
var typeCombo;
var leaderCombo;
var statusBox;
var THISPAGE = {
    init: function() {
        if (void 0 !== cRowId) {
            Public.ajaxPost('/Base/GoodsTask/query', {
                id: cRowId
            }, function(res) {
                if (200 == res.status) {
                    rowData = res.data;
                    content = rowData.content;
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
        } else if (cRowData) {
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
            $('#content').html(rowData.content);
            $('#goodsNo').val(rowData.goodsNo);
            $('#execute').val(rowData.execute);
            if (rowData.reqInfo) {
                $('#reqInfo').html('<span class="ml10">定制申请人：' + rowData.reqInfo.uName + '</span><span class="ml10">申请时间：' + rowData.reqInfo.createTime + '</span>');
            $('#title').attr({disabled: true}).addClass('ui-input-dis');
            $('#lastDate').attr({disabled: true}).addClass('ui-input-dis');
            }
            if (rowData.statusInfo) {
                $.each(rowData.statusInfo, function(k, v) {
                    k == 5 && $.inArray('GOODSTASK_CHECK', defaultPage.SYSTEM.rights) !== -1 && $('#status .chk').eq(k).find('input').eq(0).removeAttr('disabled');
                    v.c == 1 && $('#status .chk').eq(k).addClass('checked');
                    $('#status .chk').eq(k).find('input')[0].checked = Boolean(Number(v.c));
                    $('#status .chk').eq(k).find('.chooseName').eq(0).html(v.n);
                    $('#status .chk').eq(k).find('.chooseTime').eq(0).html(v.t);
                });
            }
            if (rowData.publishName != defaultPage.SYSTEM.uName) {
                $('#title').attr('disabled', true).addClass('ui-input-dis');
                $('#content').attr('disabled', true).addClass('ui-input-dis');
            }
            if (rowData.imgs) {
                THISPAGE.addImgs(rowData.imgs);
            }
            if (rowData.files) {
                THISPAGE.addFiles(rowData.files);
            }
            if (api.config.disEdit) {
                $('#title').attr('disabled', true).addClass('ui-input-dis');
                $('#content').attr('disabled', true).addClass('ui-input-dis');
                $('#goodsNo').attr('disabled', true).addClass('ui-input-dis');
                $('#addExecute').addClass('dn');
            }
        } else {
            if (cRowData) {
                $('#title').val(cRowData.title).attr({disabled: true}).addClass('ui-input-dis');
                $('#lastDate').val(cRowData.lastDate).attr({disabled: true}).addClass('ui-input-dis');
                $('#content').html(cRowData.content);
                $('#reqInfo').html('<span class="ml10">定制申请人：' + cRowData.uName + '</span><span class="ml10">申请时间：' + cRowData.createTime + '</span>');
                if (cRowData.imgs) {
                    rowData.imgs = cRowData.imgs;
                    THISPAGE.addImgs(rowData.imgs);
                }
                if (cRowData.files) {
                    rowData.files = cRowData.files;
                    THISPAGE.addFiles(rowData.files);
                }
            }
            $.inArray('GOODSTASK_CHECK', defaultPage.SYSTEM.rights) !== -1 && $('#status .chk').eq(5).find('input').eq(0).removeAttr('disabled');
        }
        typeCombo = $('#type').combo({
            data: [
                {id: 0, name: '正常发起'},
                {id: 1, name: '定制申请'}
            ],
            value: 'id',
            text: 'name',
            defaultSelected: cRowData ? 1 : (rowData.type ? ['id', rowData.type] : 0),
            disabled: !0,
            listHeight: 220
        }).getCombo();
        leaderCombo = $('#leader').combo({
            data: '/Base/User/queryAll?normal=yes&department=技术部',
            ajaxOptions: {
                formatData: function(a) {
                    if (rowData.leader && !a.data.rows.find(item => item.name === rowData.leader)) {
                        a.data.rows.unshift({
                            id: -1,
                            name: rowData.leader
                        })
                    }
                    return a.data.rows;
                }
            },
            value: 'id',
            text: 'name',
            defaultSelected: rowData.leader ? ['name', rowData.leader] : -1,
            disabled: api.config.disEdit ? !0 : !1,
            listHeight: 220
        }).getCombo();
        Public.ajaxGet('/Base/User/queryAll', {department: '技术部', isDisable: -2}, function(data) {
            if (data.status === 200) {
                var normalUser = disableUser = '';
                $.each(data.data.rows, function(i, v) {
                    if (v.isDisable == 0) {
                        normalUser += '<option value="' + v.id + '">' + v.name + '</option>';
                    } else {
                        disableUser += '<option value="' + v.id + '">' + v.name + '</option>';
                    }
                });
                $('#otherNormal').html(normalUser);
                $('#otherDisable').html(disableUser);
                /* 异步获取数据成功之后再填充选项并加载选择事件 */
                $('#otherCombo').fSelect({
                    id: 'otherFselect',
                    showSearch: !1,
                    listHeight: 176,
                    disabled: api.config.disEdit ?!0 : !1

                });
                if (rowData.other) {
                    $('#otherFselect .fs-input').val(rowData.other);
                    var names = rowData.other.toString().split(',');
                    $("#otherFselect .fs-option").each(function() {
                        if (names.indexOf($(this).children('.fs-option-label').html()) != -1) {
                            $(this).addClass("selected");
                        }
                    })
                }
            } else {
                $.dialog.tips(data.msg, 2);
            }
        });
        $('#type').find('input').attr('name', 'typeInput');
        $('#leader').find('input').attr('name', 'leaderInput');
    },
    initEvent: function() {
        statusBox = $('#status').cssCheckbox();
        if (!api.config.disEdit) {
            laydate.render({
                elem: "#lastDate",
                type: 'datetime'
            });
            $('#addExecute').on('click', function(a) {
                $.dialog.prompt('<span class="db f14" style="width:500px;">请输入项目进展信息</span>', function(inputValue) {
                    if (oper == 'add') {
                        let execute = $('#execute').val();
                        execute += (new Date).format('yyyy-MM-dd hh:mm:ss') + ' ' + inputValue + "\r\n";
                        $('#execute').val(execute);
                    } else {
                        Public.ajaxPost('/Base/GoodsTask/addExecuteon', {
                            id: cRowId,
                            execute: inputValue
                        }, function(res) {
                            if (res && 200 == res.status) {
                                $('#execute').val(res.data.execute);
                                $.dialog.tips('添加成功！');
                            } else {
                                $.dialog.tips('添加失败！' + res.msg, 2);
                            }
                        });
                    }
                });
            });
            $('.chk').on('click', function(a) {
                a.preventDefault();
                if (Business.verifyRight('GOODSTASK_UPDATE')) {
                    if ($(this).hasClass('patentDone')) {
                        if (Business.verifyRight('GOODSTASK_CHECK')) {
                            if ($(this).hasClass('checked')) {
                                /* 如果是勾选 */
                                $(this).find('.chooseName').html(defaultPage.SYSTEM.uName);
                                $(this).find('.chooseTime').html((new Date).format('yyyy-MM-dd hh:mm:ss'));
                            } else {
                                /* 如果是取消勾选 */
                                $(this).find('.chooseName').html('');
                                $(this).find('.chooseTime').html('');
                            }
                        }
                    } else {
                        if ($(this).hasClass('checked')) {
                            /* 如果是勾选 */
                            $(this).find('.chooseName').html(defaultPage.SYSTEM.uName);
                            $(this).find('.chooseTime').html((new Date).format('yyyy-MM-dd hh:mm:ss'));
                        } else {
                            /* 如果是取消勾选 */
                            $(this).find('.chooseName').html('');
                            $(this).find('.chooseTime').html('');
                        }
                    }
                }
            });
            $('#upimg').uploader({
                action: '/Base/GoodsTask/uploadImg',
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
            $('#upFile').uploader({
                action: '/Base/GoodsTask/uploadfile',
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
                fileTypeExts: 'jpg,jpeg,gif,png | csv,xls,xlsx,doc,docx,xps,pdf,txt | zip,rar,7z,iso',
                onStart: function(e) {
                    $('#manage-wrap').prepend('<div id="div-lock"><div class="loading">上传中...</div></div>');
                },
                onError: function(e) {
                    $.dialog.tips('上传错误！', 2);
                },
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
                onAllComplete: function() {
                    $('#manage-wrap > #div-lock').remove();
                }
            });
            $('.upload-el').on({
                click: function(a) {
                    !Business.verifyRight('GOODSTASK_UPDATE') && a.preventDefault();
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
            $('#files').on('click', '.fileRename', function() {
                if (Business.verifyRight('GOODSTASK_UPDATE')) {
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
                if (Business.verifyRight('GOODSTASK_UPDATE')) {
                    var upName = $(this).parent().data('upname');
                    if (upName !== defaultPage.SYSTEM.uName) {
                        $.dialog.tips('禁止删除别人上传的文件！', 2);
                    } else {
                        $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                            var key = $(this).parent().data('key');
                            rowData.files.splice(key, 1) && THISPAGE.addFiles(rowData.files);
                        });
                    }
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
            fileDiv += (
                '<div class="file-line" data-key="' + k + '" data-upname="' + v.upName + '">' +
                '<a href="/data/upfile/goodsTask/' + v.url + '" target="_blank" title="' + v.name + '"><span>' + v.name + '</span><span>' + v.upName + '</span><span>' + v.upTime + '</span></a>' +
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
                title: {required: !0},
                lastDate: {required: !0},
                typeInput: {required: !0},
                leaderInput: {required: !0}
            }
        });
    },
    postData: function(callbackType = 0) {
        if (Business.verifyRight('GOODSTASK_UPDATE')) {
            if (!$('#manage-form').validate().form()) {
                return void $('#manage-form').find('input.valid-error').eq(0).focus();
            }
            var title = 'add' == oper ? '新增产品开发' : '编辑产品开发';
            var data = THISPAGE.getData();
            data && Public.ajaxPost('/Base/GoodsTask/' + ('add' == oper ? 'add' : 'update'), data, function(res) {
                if (200 == res.status) {
                    parent.parent.$.dialog.tips(title + '成功！');
                    if (callback && 'function' == typeof callback) {
                        callback(res.data, oper, window, callbackType);
                    };
                } else {
                    $.dialog.tips(title + '失败！' + res.msg, 2);
                }
            });
        }
    },
    getData: function() {
        var data = {
            id: cRowId || 0,
            srcDevReqId: cRowData ? cRowData.id : 0,
            title: $.trim($('#title').val()),
            lastDate: $('#lastDate').val(),
            type: typeCombo.getValue(),
            content: $.trim($('#content').val()),
            leader: leaderCombo.getText(),
            other: $.trim($('#otherFselect .fs-input').val()),
            goodsNo: $.trim($('#goodsNo').val()),
            execute: $.trim($('#execute').val()),
            status: statusBox.chkVal().length > 0 ? statusBox.chkVal().join(',') : '0',
            statusInfo: THISPAGE.getStatusInfo(),
            imgs: rowData.imgs ? JSON.stringify(rowData.imgs) : '',
            files: rowData.files ? JSON.stringify(rowData.files) : ''
        };
        return data;
    },
    getStatusInfo() {
        for (var a = [], b = 0; b < 6; b++) {
            var c = {
                c: Number($('#status .chk').eq(b).find('input')[0].checked),
                n: $('#status .chk').eq(b).find('.chooseName').eq(0).html(),
                t: $('#status .chk').eq(b).find('.chooseTime').eq(0).html()
            };
            a.push(c);
        }
        return a;
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
        $('#lastDate').val('');
        typeCombo._setDefaultSelected(0);
        $('#content').val('');
        leaderCombo._setDefaultSelected(-1);
        $('#otherFselect #clearAllBtn').trigger('click');
        $('#goodsNo').val('');
        $('#execute').val(''),
        $.inArray('GOODSTASK_CHECK', defaultPage.SYSTEM.rights) !== -1 && $('#status .chk').eq(5).find('input').eq(0).removeAttr('disabled');
        for (var i = 0; i < 6; i++){
            $('#status .chk').eq(i).removeClass('checked');
            $('#status .chk').eq(i).find('input')[0].checked = false;
            $('#status .chk').eq(i).find('.chooseName').eq(0).html('');
            $('#status .chk').eq(i).find('.chooseTime').eq(0).html('');
        }
        $('#imgs').html('');
        $('#files').html('');
    }
};
$(function() {
    THISPAGE.init();
});
