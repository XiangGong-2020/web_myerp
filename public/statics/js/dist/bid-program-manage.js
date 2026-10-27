var defaultPage = Public.getDefaultPage();
var api = frameElement.api;
var oper = api.data.oper;
var cRowId = api.data.rowId;
var callback = api.data.callback;
var rowData = api.data.data || {};
var ourselfCombo;
var statusCombo;
var company = [];
var THISPAGE = {
    init: function() {
        if (void 0 !== cRowId) {
            Public.ajaxPost('/Base/Bid/queryBidProgram', {
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
                id: 'confirm',
                name: '保存',
                defClass: 'ui_state_highlight',
                callback: function() {
                    return THISPAGE.postData(), !1;
                }
            }, {
                id: 'cancel',
                name: '关闭'
            });
        }
    },
    initDom: function() {
        if (rowData) {
            $('#bidSiteName').data('bidSiteId', rowData.bidSiteId).val(rowData.bidSiteName);
            $('#contactName').data('contactId', rowData.contactId).val(rowData.contactName);
            $('#bidTitle').val(rowData.bidTitle);
            $('#bidUrl').val(rowData.bidUrl);
            $('#budget').val(Number(rowData.budget || 0).toFixed(2));
            $('#bidDate').val(rowData.bidDate);
            $('#contactInfo').val(rowData.contactInfo);
            $('#amount').val(Number(rowData.amount || 0).toFixed(2));
            $('#srcBqNo').val(rowData.srcBqNo);
            $('#description').val(rowData.description);
            $('#lostReason').val(rowData.lostReason);
            if (rowData.files1) {
                THISPAGE.addFiles1(rowData.files1);
            }
            if (rowData.files2) {
                THISPAGE.addFiles2(rowData.files2);
            }
            if (rowData.files3) {
                THISPAGE.addFiles3(rowData.files3);
            }
        }
        Public.ajaxGet('/Base/User/queryAll', {normal: 'yes', department: '销售部'}, function(data) {
            if (data.status === 200) {
                var normalUser = disableUser = '';
                $.each(data.data.rows, function(i, v) {
                    if (v.isDisable == 0) {
                        normalUser += '<option value="' + v.id + '">' + v.name + '</option>';
                    } else {
                        disableUser += '<option value="' + v.id + '">' + v.name + '</option>';
                    }
                });
                normalUser += '<option value="2">叶超</option>';
                $('#normalUser').html(normalUser);
                $('#disableUser').html(disableUser);
                $('#sysUser').html('<option value="1">系统</option>');
                $('#userCombo').fSelect({
                    showSearch: !0,
                    listHeight: 270,
                    disabled: api.config.disEdit ? !0 : !1
                });
                if (rowData.quotedName) {
                    $('.fs-input').val(rowData.quotedName);
                    var names = rowData.quotedName.toString().split(',');
                    $(".fs-option").each(function() {
                        if(names.indexOf($(this).children('.fs-option-label').html()) != -1) {
                            $(this).addClass("selected");
                        }
                    });
                }
            } else {
                $.dialog.tips(data.msg, 2);
            }
        });
        statusCombo = $('#status').combo({
            data: [
                {id: 0, name: '未开标'},
                {id: 1, name: '未中标'},
                {id: 2, name: '已中标'}
            ],
            value: 'id',
            text: 'name',
            defaultSelected: rowData.status >= 0 ? ['id', rowData.status] : 0,
            disabled: api.config.disEdit ? !0 : !1
        }).getCombo();
        $('#status').find('input').attr('name', 'statusInput');
        ourselfCombo = $('#ourself').combo({
            async: false, // 异步 否
            data: '/Base/Company/queryAll',
            ajaxOptions: {
                formatData: function(a) {
                    a.data.rows.forEach(element => {
                        if (element.id > 0 && element.isDisable == 0) {
                            company.push(element);
                        }
                    });
                    return company;
                }
            },
            value: 'id',
            text: 'name',
            defaultSelected: rowData.sId > 0 ? ['id', rowData.sId] : -1,
            disabled: api.config.disEdit ? !0 : !1,
            width: 200,
            listHeight: 341,
            callback: {
                onChange: function(data) {
                    if (data) {
                        $('#ourself').data('ourselfData', data);
                    } else {
                        $('#ourself').removeData('ourselfData');
                    }
                }
            }
        }).getCombo();
        if (rowData.sId && rowData.sName) {
            $('#ourself').data('ourselfData', {
                id: rowData.sId,
                name: rowData.sName
            });
            ourselfCombo.input.val(rowData.sName);
        }
        $('#ourself').find('input').attr('name', 'ourselfInput');
        if (api.config.disEdit) {
            $('#bidSiteName').attr('disabled', true).addClass('ui-input-dis');
            $('#contactName').attr('disabled', true).addClass('ui-input-dis');
            $('#bidTitle').attr('disabled', true).addClass('ui-input-dis');
            $('#bidUrl').attr('disabled', true).addClass('ui-input-dis');
            $('#budget').attr('disabled', true).addClass('ui-input-dis');
            $('#bidDate').attr('disabled', true).addClass('ui-input-dis');
            $('#contactInfo').attr('disabled', true).addClass('ui-input-dis');
            $('#amount').attr('disabled', true).addClass('ui-input-dis');
            $('#srcBqNo').attr('disabled', true).addClass('ui-input-dis');
            $('#description').attr('disabled', true).addClass('ui-input-dis');
            $('#lostReason').attr('disabled', true).addClass('ui-input-dis');
        }
    },
    initEvent: function() {
        laydate.render({
            elem: "#bidDate",
            type: 'date'
        });
        $('#bidSite').on('click', function() {
            if (!api.config.disEdit) {
                $.dialog({
                    width: 1250,
                    height: 500,
                    title: '选择招标来源',
                    content: 'url:/Uri/bid_site_batch',
                    data: {
                        isSingle: !0
                    },
                    max: !1,
                    min: !1,
                    lock: !0,
                    ok: function() {
                        for (let rowid in this.content.addList) {
                            var row = this.content.addList[rowid];
                            $('#bidSiteName').data('bidSiteId', rowid).val(row.name);
                        }
                        $('#bidTitle').focus();
                    },
                    cancel: function() {
                        $('#bidTitle').focus();
                        return !0;
                    }
                });
            }
        });
        $('#contact').on('click', function() {
            if (!api.config.disEdit) {
                $.dialog({
                    width: 1250,
                    height: 500,
                    title: '选择招标单位（客户：招标单位分类）',
                    content: 'url:/Uri/customer_batch',
                    data: {
                        isSingle: !0
                    },
                    max: !1,
                    min: !1,
                    lock: !0,
                    ok: function() {
                        for (let rowid in this.content.addList) {
                            console.log(this.content.addList);
                            var row = this.content.addList[rowid];
                            $('#contactName').data('contactId', rowid).val(row.name);
                        }
                        $('#bidTitle').focus();
                    },
                    cancel: function() {
                        $('#bidTitle').focus();
                        return !0;
                    }
                });
            }
        });
        $('#upFile1').uploader({
            action: '/Base/Bid/uploadBidProgramFile1',
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
            fileTypeExts: 'jpg,jpeg,gif,png | csv,xls,xlsx,doc,docx,xps,pdf | zip,rar,iso,7z',
            onStart: function(e) {
                $('#manage-wrap').append('<div id="div-lock"><div class="loading">上传中...</div></div>');
            },
            onError: function(e) {
                $.dialog.tips('上传错误！', 2);
            },
            onSuccess: function(e) {
                var data = JSON.parse(e.data);
                if (data.file) {
                    if (!rowData.files1) {
                        rowData.files1 = [];
                        rowData.files1[0] = data.file;
                    } else {
                        rowData.files1.unshift(data.file);
                    }
                    THISPAGE.addFiles1(rowData.files1);
                }
            },
            onAllComplete: function() {
                $('#manage-wrap > #div-lock').remove();
            }
        });
        $('#upFile2').uploader({
            action: '/Base/Bid/uploadBidProgramFile2',
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
            fileTypeExts: 'jpg,jpeg,gif,png | csv,xls,xlsx,doc,docx,xps,pdf | zip,rar,iso,7z',
            onStart: function(e) {
                $('#manage-wrap').append('<div id="div-lock"><div class="loading">上传中...</div></div>');
            },
            onError: function(e) {
                $.dialog.tips('上传错误！', 2);
            },
            onSuccess: function(e) {
                var data = JSON.parse(e.data);
                if (data.file) {
                    if (!rowData.files2) {
                        rowData.files2 = [];
                        rowData.files2[0] = data.file;
                    } else {
                        rowData.files2.unshift(data.file);
                    }
                    THISPAGE.addFiles2(rowData.files2);
                }
            },
            onAllComplete: function() {
                $('#manage-wrap > #div-lock').remove();
            }
        });
        $('#upFile3').uploader({
            action: '/Base/Bid/uploadBidProgramFile3',
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
            fileTypeExts: 'jpg,jpeg,gif,png | csv,xls,xlsx,doc,docx,xps,pdf | zip,rar,iso,7z',
            onStart: function(e) {
                $('#manage-wrap').append('<div id="div-lock"><div class="loading">上传中...</div></div>');
            },
            onError: function(e) {
                $.dialog.tips('上传错误！', 2);
            },
            onSuccess: function(e) {
                var data = JSON.parse(e.data);
                if (data.file) {
                    if (!rowData.files3) {
                        rowData.files3 = [];
                        rowData.files3[0] = data.file;
                    } else {
                        rowData.files3.unshift(data.file);
                    }
                    THISPAGE.addFiles3(rowData.files3);
                }
            },
            onAllComplete: function() {
                $('#manage-wrap > #div-lock').remove();
            }
        });
        $('.upload-el').on({
            click: function(a) {
                if (!Business.verifyRight('BIDPROGRAM_UPDATE')) {
                    a.preventDefault();
                }
            },
            mouseover: function() {
                $(this).prev().addClass('black');
            },
            mouseleave: function() {
                $(this).prev().removeClass('black');
            }
        });
        $('#files1').on('click', '.fileRename', function() {
            if (Business.verifyRight('BIDPROGRAM_UPDATE')) {
                $('.manage-wrapper .renameInput').addClass('dn');
                $('.manage-wrapper .renameSumbit').addClass('dn');
                $('.manage-wrapper .renameCancel').addClass('dn');
                $(this).parent().children('.renameInput').removeClass('dn');
                $(this).parent().children('.renameSumbit').removeClass('dn');
                $(this).parent().children('.renameCancel').removeClass('dn');
            }
        });
        $('#files1').on('click', '.renameSumbit', function() {
            var newName = $(this).parent().children('.renameInput').val();
            if (newName) {
                var key = $(this).closest('.file-line').data('key');
                rowData.files1[key].name = newName;
                THISPAGE.addFiles1(rowData.files1);
            }
            $(this).addClass('dn');
            $(this).parent().children('.renameInput').addClass('dn');
            $(this).parent().children('.renameCancel').addClass('dn');
        });
        $('#files1').on('click', '.renameCancel', function() {
            $(this).addClass('dn');
            $(this).parent().children('.renameInput').addClass('dn');
            $(this).parent().children('.renameSumbit').addClass('dn');
        });
        $('#files1').on('click', '.fileDel', function(a) {
            a.stopPropagation();
            if (Business.verifyRight('BIDPROGRAM_UPDATE')) {
                $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                    var key = $(this).closest('.file-line').data('key');
                    rowData.files1.splice(key, 1) && THISPAGE.addFiles1(rowData.files1);
                });
            }
        });
        $('#files2').on('click', '.fileRename', function() {
            if (Business.verifyRight('BIDPROGRAM_UPDATE')) {
                $('.manage-wrapper .renameInput').addClass('dn');
                $('.manage-wrapper .renameSumbit').addClass('dn');
                $('.manage-wrapper .renameCancel').addClass('dn');
                $(this).parent().children('.renameInput').removeClass('dn');
                $(this).parent().children('.renameSumbit').removeClass('dn');
                $(this).parent().children('.renameCancel').removeClass('dn');
            }
        });
        $('#files2').on('click', '.renameSumbit', function() {
            var newName = $(this).parent().children('.renameInput').val();
            if (newName) {
                var key = $(this).closest('.file-line').data('key');
                rowData.files2[key].name = newName;
                THISPAGE.addFiles2(rowData.files2);
            }
            $(this).addClass('dn');
            $(this).parent().children('.renameInput').addClass('dn');
            $(this).parent().children('.renameCancel').addClass('dn');
        });
        $('#files2').on('click', '.renameCancel', function() {
            $(this).addClass('dn');
            $(this).parent().children('.renameInput').addClass('dn');
            $(this).parent().children('.renameSumbit').addClass('dn');
        });
        $('#files2').on('click', '.fileDel', function(a) {
            a.stopPropagation();
            if (Business.verifyRight('BIDPROGRAM_UPDATE')) {
                $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                    var key = $(this).closest('.file-line').data('key');
                    rowData.files2.splice(key, 1) && THISPAGE.addFiles2(rowData.files2);
                });
            }
        });
        $('#files3').on('click', '.fileRename', function() {
            if (Business.verifyRight('BIDPROGRAM_UPDATE')) {
                $('.manage-wrapper .renameInput').addClass('dn');
                $('.manage-wrapper .renameSumbit').addClass('dn');
                $('.manage-wrapper .renameCancel').addClass('dn');
                $(this).parent().children('.renameInput').removeClass('dn');
                $(this).parent().children('.renameSumbit').removeClass('dn');
                $(this).parent().children('.renameCancel').removeClass('dn');
            }
        });
        $('#files3').on('click', '.renameSumbit', function() {
            var newName = $(this).parent().children('.renameInput').val();
            if (newName) {
                var key = $(this).closest('.file-line').data('key');
                rowData.files3[key].name = newName;
                THISPAGE.addFiles3(rowData.files3);
            }
            $(this).addClass('dn');
            $(this).parent().children('.renameInput').addClass('dn');
            $(this).parent().children('.renameCancel').addClass('dn');
        });
        $('#files3').on('click', '.renameCancel', function() {
            $(this).addClass('dn');
            $(this).parent().children('.renameInput').addClass('dn');
            $(this).parent().children('.renameSumbit').addClass('dn');
        });
        $('#files3').on('click', '.fileDel', function(a) {
            a.stopPropagation();
            if (Business.verifyRight('BIDPROGRAM_UPDATE')) {
                $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                    var key = $(this).closest('.file-line').data('key');
                    rowData.files3.splice(key, 1) && THISPAGE.addFiles3(rowData.files3);
                });
            }
        });
    },
    addFiles1: function(files) {
        var fileDiv = '';
        $.each(files, function(k, v) {
            fileDiv += (
                '<div class="file-line" data-key="' + k + '">' +
                '<a href="/data/upfile/bidProgram/files1/' + v.url + '" target="_blank"><span title="' + v.name + '">' + v.name + '</span><span>' + v.upName + '</span><span>' + v.upTime + '</span></a>' +
                '<input type="text" class="renameInput dn" placeholder="输入新文件名">' +
                '<span class="renameSumbit dn">确定</span><span class="renameCancel dn">取消</span>' +
                '<i class="fileRename iconfont icon-bianji" title="改名"></i><i class="fileDel iconfont icon-shanchu" title="删除"></i>' +
                '</div>'
            );
        });
        $('#files1').html(fileDiv);
    },
    addFiles2: function(files) {
        var fileDiv = '';
        $.each(files, function(k, v) {
            fileDiv += (
                '<div class="file-line" data-key="' + k + '">' +
                '<a href="/data/upfile/bidProgram/files2/' + v.url + '" target="_blank"><span title="' + v.name + '">' + v.name + '</span><span>' + v.upName + '</span><span>' + v.upTime + '</span></a>' +
                '<input type="text" class="renameInput dn" placeholder="输入新文件名">' +
                '<span class="renameSumbit dn">确定</span><span class="renameCancel dn">取消</span>' +
                '<i class="fileRename iconfont icon-bianji" title="改名"></i><i class="fileDel iconfont icon-shanchu" title="删除"></i>' +
                '</div>'
            );
        });
        $('#files2').html(fileDiv);
    },
    addFiles3: function(files) {
        var fileDiv = '';
        $.each(files, function(k, v) {
            fileDiv += (
                '<div class="file-line" data-key="' + k + '">' +
                '<a href="/data/upfile/bidProgram/files3/' + v.url + '" target="_blank"><span title="' + v.name + '">' + v.name + '</span><span>' + v.upName + '</span><span>' + v.upTime + '</span></a>' +
                '<input type="text" class="renameInput dn" placeholder="输入新文件名">' +
                '<span class="renameSumbit dn">确定</span><span class="renameCancel dn">取消</span>' +
                '<i class="fileRename iconfont icon-bianji" title="改名"></i><i class="fileDel iconfont icon-shanchu" title="删除"></i>' +
                '</div>'
            );
        });
        $('#files3').html(fileDiv);
    },
    initValidator: function() {
        $('#manage-form').validate({
            rules: {
                fsInput: {required: !0},
                amount: {required: !0},
                bidDate: {required: !0},
                statusInput: {required: !0},
                bidSiteName: {required: !0},
                contactName: {required: !0},
                ourselfInput: {required: !0},
                bidTitle: {required: !0},
                bidUrl: {required: !0},
                contactInfo: {required: !0}
            }
        });
    },
    postData: function() {
        if (Business.verifyRight('BIDPROGRAM_UPDATE')) {
            if (!$('#manage-form').validate().form()) {
                return void $('#manage-form').find('input.valid-error').eq(0).focus();
            }
            var title = 'add' == oper ? '新增投标项目' : '编辑投标项目';
            var data = THISPAGE.getData();
            data && Public.ajaxPost('/Base/Bid/' + ('add' == oper ? 'addBidProgram' : 'updateBidProgram'), data, function(res) {
                if (res && 200 == res.status) {
                    parent.parent.$.dialog.tips(title + '成功！');
                    if (callback && 'function' == typeof callback) {
                        callback(res.data, oper, window);
                    };
                } else {
                    $.dialog.alert(title + '失败！' + res.msg);
                }
            });
        }
    },
    getData: function() {
        if (statusCombo.getValue() == 1 && $.trim($('#lostReason').val()) == '') {
            $.dialog.alert('开标结果为【未中标】时，必须录入【未中标原因】！');
            return !1;
        }
        var data = {
            id: void 0 !== cRowId ? cRowId : -1,
            srcBqId: rowData.srcBqId || 0,
            srcBqNo: $.trim($('#srcBqNo').val()),
            quotedName: $.trim($('.fs-input').val()),
            budget: Number($.trim($('#budget').val())).toFixed(2),
            amount: Number($.trim($('#amount').val())).toFixed(2),
            bidDate: $.trim($('#bidDate').val()),
            status: statusCombo.getValue(),
            bidSiteId: Number($('#bidSiteName').data('bidSiteId')),
            bidSiteName: $.trim($('#bidSiteName').val()),
            contactId: Number($('#contactName').data('contactId')),
            contactName: $.trim($('#contactName').val()),
            sId: Number($('#ourself').data('ourselfData').id),
            sName: $.trim($('#ourself').data('ourselfData').name),
            bidTitle: $.trim($('#bidTitle').val()),
            bidUrl: $.trim($('#bidUrl').val()),
            contactInfo: $.trim($('#contactInfo').val()),
            files1: rowData.files1 ? JSON.stringify(rowData.files1) : '',
            files2: rowData.files2 ? JSON.stringify(rowData.files2) : '',
            files3: rowData.files3 ? JSON.stringify(rowData.files3) : '',
            description: $.trim($('#description').val()),
            lostReason: $.trim($('#lostReason').val())
        };
        return data;
    }
};
$(function() {
    THISPAGE.init();
});
