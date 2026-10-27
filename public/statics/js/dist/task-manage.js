var api = frameElement.api;
var defaultPage = Public.getDefaultPage();
var oper = api.data.oper;
var cRowId = api.data.rowId;
var callback = api.data.callback;
var rowData = {};
var typeCombo;
var departmentCombo;
var leaderCombo;
var statusCombo;
var disEdit = !1;
var THISPAGE = {
    init: function() {
        if (void 0 !== cRowId) {
            Public.ajaxPost('/Base/Task/query', {
                id: cRowId
            }, function(res) {
                if (200 == res.status) {
                    rowData = res.data;
                    if (rowData.uName != defaultPage.SYSTEM.uName && rowData.leader != defaultPage.SYSTEM.uName && $.inArray(defaultPage.SYSTEM.uName, rowData.other.split(",")) == -1) {
                        disEdit = !0;
                    }
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
        if (disEdit) {
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
            $('#limitTime').val(rowData.limitTime);
            $('#content').html(rowData.content);
            $('#execute').val(rowData.execute);
            if (rowData.imgs) {
                THISPAGE.addImgs(rowData.imgs);
            }
            if (rowData.files) {
                THISPAGE.addFiles(rowData.files);
            }
            // 如果不是发起人、或是发起人但已超过修改时效
            if (rowData.uName != defaultPage.SYSTEM.uName || (rowData.uName == defaultPage.SYSTEM.uName && rowData.goneHour == 1)) {
                $('#title').attr('disabled', true).addClass('ui-input-dis');
                $('#limitTime').attr('disabled', true).addClass('ui-input-dis');
                $('#content').attr('disabled', true).addClass('ui-input-dis');
            }
            if (disEdit) {
                $('#title').attr('disabled', true).addClass('ui-input-dis');
                $('#limitTime').attr('disabled', true).addClass('ui-input-dis');
                $('#content').attr('disabled', true).addClass('ui-input-dis');
                $('#addExecute').addClass('dn');
            }
        }
        typeCombo = $('#type').combo({
            data: [
                {id: 0, name: '自发'},
                {id: 1, name: '指派'}
            ],
            value: 'id',
            text: 'name',
            defaultSelected: rowData.type ? ['id', rowData.type] : (api.data.data ? ['id', api.data.data.type] : 0),
            disabled: !0,
            listHeight: 220
        }).getCombo();
        departmentCombo = $('#department').combo({
            data: '/Base/Category/list?typeNumber=departmenttype',
            ajaxOptions: {
                formatData: function(a) {
                    return a.data.rows.unshift({
                        id: -1,
                        name: ''
                    }), a.data.rows;
                }
            },
            value: 'id',
            text: 'name',
            width: 78,
            listHeight: 550,
            defaultSelected: 0,
            placeholder: '选择部门',
            disabled: (disEdit || (oper == 'add' && api.data.data.type == 0) || (oper == 'edit' && rowData.type == 0)) ? !0 : !1,
            callback: {
                onChange: function() {
                    if (this._selectedIndex > 0) { // 如果选择了具体部门，才动态加载对应部门的职位
                        Public.ajaxGet('/Base/User/queryAll?normal=yes' + '&department=' + this.formattedData[this._selectedIndex].text + (oper == 'add' ? '&isDisable=0' : ''), {}, function(res) {
                            if (res && 200 == res.status) {
                                leaderCombo.loadData(res.data.rows, -1, false);
                            }
                        });
                    }
                }
            }
        }).getCombo();
        leaderCombo = $('#leader').combo({
            data: rowData.leader ? [{id: 0, name: rowData.leader}] : (((oper == 'add' && api.data.data.type == 0) || (oper == 'edit' && rowData.type == 0))  ? [{id: 0, name: defaultPage.SYSTEM.uName}] : []),
            value: 'id',
            text: 'name',
            placeholder: '选择主导人',
            defaultSelected: rowData.leader ? ['name', rowData.leader] : ((oper == 'add' && api.data.data.type == 0) ? ['name', defaultPage.SYSTEM.uName] : -1),
            disabled: (disEdit || (oper == 'add' && api.data.data.type == 0) || (oper == 'edit' && rowData.type == 0)) ? !0 : !1,
            listHeight: 550
        }).getCombo();
        Public.ajaxPost('/Base/User/queryAll', {normal: 'yes', isDisable: -2}, function(data) {
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
                    listHeight: 500,
                    disabled: (disEdit || (oper == 'oper' && rowData.uName != defaultPage.SYSTEM.uName && rowData.leader != defaultPage.SYSTEM.uName)) ? !0 : !1

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
        statusCombo = $('#status').combo({
            data: [
                {id: 0, name: '未完成'},
                {id: 1, name: '已完成'}
            ],
            value: 'id',
            text: 'name',
            defaultSelected: rowData.status ? ['id', rowData.status] : 0,
            disabled: (oper == 'add' || disEdit || (rowData.uName != defaultPage.SYSTEM.uName && rowData.leader != defaultPage.SYSTEM.uName)) ? !0 : !1,
            listHeight: 220
        }).getCombo();
        $('#type').find('input').attr('name', 'typeInput');
        $('#leader').find('input').attr('name', 'leaderInput');
        $('#status').find('input').attr('name', 'statusInput');
    },
    initEvent: function() {
        if (!disEdit) {
            laydate.render({
                elem: "#limitTime",
                type: 'datetime'
            });
            $('#upimg').uploader({
                action: '/Base/Task/uploadImg',
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
                action: '/Base/Task/uploadfile',
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
                    !Business.verifyRight('TASK_UPDATE') && a.preventDefault();
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
                if (Business.verifyRight('TASK_UPDATE')) {
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
                if (Business.verifyRight('TASK_UPDATE')) {
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
            $('#addExecute').on('click', function(a) {
                $.dialog.prompt('<span class="db f14" style="width:500px;">请输入任务进展信息</span>', function(inputValue) {
                    if (oper == 'add') {
                        let execute = $('#execute').val();
                        execute += (new Date).format('yyyy-MM-dd hh:mm:ss') + ' ' + defaultPage.SYSTEM.uName + '：' + inputValue + "\r\n";
                        $('#execute').val(execute);
                    } else {
                        Public.ajaxPost('/Base/Task/addExecuteon', {
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
        }
    },
    addImgs: function(imgs) {
        var imgDiv = '';
        $.each(imgs, function(k, v) {
            imgDiv += '<div class="imgDiv" data-pid="' + k + '"><p class="imgControl"><span class="controlBtn see iconfont icon-xianshi" title="查看"></span><span class="controlBtn del iconfont icon-shanchu" title="删除"></span></p><img src="/data/upfile/task/' + v + '"/></div>';
        });
        $('#imgs').html(imgDiv);
    },
    addFiles: function(files) {
        var fileDiv = '';
        $.each(files, function(k, v) {
            fileDiv += (
                '<div class="file-line" data-key="' + k + '" data-upname="' + v.upName + '">' +
                '<a href="/data/upfile/task/' + v.url + '" target="_blank" title="' + v.name + '"><span>' + v.name + '</span><span>' + v.upName + '</span><span>' + v.upTime + '</span></a>' +
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
                limitTime: {required: !0},
                typeInput: {required: !0},
                leaderInput: {required: !0},
                statusInput: {required: !0}
            }
        });
    },
    postData: function(callbackType = 0) {
        if (Business.verifyRight('TASK_UPDATE')) {
            if (!$('#manage-form').validate().form()) {
                return void $('#manage-form').find('input.valid-error').eq(0).focus();
            }
            var title = 'add' == oper ? '新增任务清单' : '编辑任务清单';
            var data = THISPAGE.getData();
            data && Public.ajaxPost('/Base/Task/' + ('add' == oper ? 'add' : 'update'), data, function(res) {
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
            title: $.trim($('#title').val()),
            limitTime: $('#limitTime').val(),
            type: typeCombo.getValue(),
            content: $.trim($('#content').val()),
            imgs: rowData.imgs ? JSON.stringify(rowData.imgs) : '',
            files: rowData.files ? JSON.stringify(rowData.files) : '',
            leader: leaderCombo.getText(),
            other: $.trim($('#otherFselect .fs-input').val()),
            status: statusCombo.getValue(),
            execute: $.trim($('#execute').val())
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
        $('#limitTime').val('');
        typeCombo._setDefaultSelected(0);
        $('#content').val('');
        $('#imgs').html('');
        $('#files').html('');
        leaderCombo._setDefaultSelected(-1);
        $('#otherFselect #clearAllBtn').trigger('click');
        statusCombo._setDefaultSelected(0);
        $('#execute').val('');
    }
};
$(function() {
    THISPAGE.init();
});
