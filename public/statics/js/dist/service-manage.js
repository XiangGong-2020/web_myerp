var api = frameElement.api;
var defaultPage = Public.getDefaultPage();
var oper = api.data.oper;
var cRowId = api.data.rowId;
var callback = api.data.callback;
var amountPlaces = Number(defaultPage.SYSTEM.amountPlaces);
var rowData = {};
var content = '';
var categoryCombo;
var THISPAGE = {
    init: function() {
        if (void 0 !== cRowId) {
            Public.ajaxPost('/Base/Service/query', {
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
        Public.ajaxGet('/Base/User/queryAll?normal=yes', {}, function(data) {
            if (data.status === 200) {
                var normalUser = disableUser = '';
                $.each(data.data.rows, function(i, v) {
                    if (v.isDisable == 0) {
                        normalUser += '<option value="' + v.id + '">' + v.name + '</option>';
                    } else {
                        disableUser += '<option value="' + v.id + '">' + v.name + '</option>';
                    }
                });
                Public.ajaxGet('/Base/DeliverCompany/queryAll', {}, function(data1) {
                    if (data1.status === 200) {
                        var deliverCompany = '';
                        $.each(data1.data.rows, function(i1, v1) {
                            deliverCompany += '<option value="' + v1.id + '">' + v1.name + '</option>';
                        });
                        /* 异步获取数据成功之后再填充选项并加载选择事件 */
                        $('#normalUser').html(normalUser);
                        $('#disableUser').html(disableUser);
                        $('#deliverCompany').html(deliverCompany);
                        $('#targetNameCombo').fSelect({
                            showSearch: !0,
                            listHeight: 317
                        });
                        if ('edit' == oper && rowData.targetName) {
                            $('.fs-input').val(rowData.targetName);
                            var names = rowData.targetName.toString().split(',');
                            $(".fs-option").each(function() {
                                if(names.indexOf($(this).children('.fs-option-label').html()) != -1) {
                                    $(this).addClass("selected");
                                }
                            });
                        }
                    } else {
                        $.dialog.tips(data1.msg, 2);
                    }
                });
            } else {
                $.dialog.tips(data.msg, 2);
            }
        });
        if ('edit' == oper) {
            $('#orderNo').val(rowData.orderNo);
            $('#deliverNo').val(rowData.deliverNo);
            $('#qq').val(rowData.qq);
            $('#ww').val(rowData.ww);
            $('#tel').val(rowData.tel);
            $('#targetAmount').val(Public.numToCurrency(rowData.targetAmount, amountPlaces));
            $('.info').html(rowData.content);
        } else {
            $('#info').html('');
        }
        categoryCombo = Business.categoryCombo($('#category'), {
            listHeight: 400,
            defaultSelected: rowData.categoryId > 0 ? ['id', rowData.categoryId] : -1
        }, 'servicetype');
        $('#category').find('input').attr('name', 'categoryInput');
    },
    initEvent: function() {
        $('#add-info').on('click', function() {
            if (Business.verifyRight('SERVICE_UPDATE')) {
                if ($('#add-info').hasClass('add-now')) {
                    var ueContent = ue.getContent();
                    if (ueContent != '') {
                        $('.info').prepend(
                            '<div class="info-line">' +
                            '<div class="info-icon"></div>' +
                            '<div class="info-content">' + ueContent + '</div>' +
                            '<div class="info-username">' + defaultPage.SYSTEM.uName + '</div>' +
                            '<div class="info-time">' + (new Date).format("yyyy-MM-dd hh:mm:ss") + '</div>' +
                            '<i class="close iconfont icon-shanchu"></i>' +
                            '</div>'
                        );
                    }
                    ue.setContent('');
                    $('#editor').addClass('dn');
                    $('#add-info').removeClass('add-now');
                    $('#add-info').html('添加内容');
                    $('#add-dis').addClass('dn');
                } else {
                    ue.setContent('');
                    $('#editor').removeClass('dn');
                    $('#add-info').addClass('add-now');
                    $('#add-info').html('确认添加');
                    $('#add-dis').removeClass('dn');
                }
            }
        });
        $('#add-dis').on('click', function() {
            if (Business.verifyRight('SERVICE_UPDATE')) {
                ue.setContent('');
                $('#editor').addClass('dn');
                $(this).addClass('dn');
                $('#add-info').html('添加内容');
                $('#add-info').removeClass('add-now');
            }
        });
        $('.manage-wrapper').on('click', '.info-line .close', function() {
            if (Business.verifyRight('SERVICE_UPDATE')) {
                var that = $(this).parent();
                if (defaultPage.SYSTEM.uName == that.children('.info-username').html()) {
                    $.dialog.confirm('删除数据需谨慎，删除后数据不可恢复，确定尝试删除？', function() {
                        that.remove();
                    });
                } else {
                    $.dialog.tips('不能删除别人添加的反馈内容！', 2);
                }
            }
        });
    },
    initValidator: function() {
        $('#manage-form').validate({
            rules: {
                categoryInput: {required: !0}
            }
        });
    },
    postData: function(callbackType = 0) {
        if (Business.verifyRight('SERVICE_UPDATE')) {
            if (!$('#manage-form').validate().form()) {
                return void $('#manage-form').find('input.valid-error').eq(0).focus();
            }
            var data = THISPAGE.getData();
            if (data.content == '') {
                return void $.dialog.tips('反馈内容不能为空！', 2);
            }
            var title = 'add' == oper ? '新增客户之声' : '编辑客户之声';
            Public.ajaxPost('/Base/Service/' + ('add' == oper ? 'add' : 'update'), data, function(res) {
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
        if (!$('#editor').hasClass('dn')) {
            var ueContent = ue.getContent();
            if (ueContent != '') {
                $('.info').prepend(
                    '<div class="info-line">' +
                    '<div class="info-icon"></div>' +
                    '<div class="info-content">' + ueContent + '</div>' +
                    '<div class="info-username">' + defaultPage.SYSTEM.uName + '</div>' +
                    '<div class="info-time">' + (new Date).format("yyyy-MM-dd hh:mm:ss") + '</div>' +
                    '<i class="close iconfont icon-shanchu"></i>' +
                    '</div>'
                );
            }
            ue.setContent('');
            $('#editor').addClass('dn');
            $('#add-info').removeClass('add-now');
            $('#add-info').html('添加内容');
            $('#add-dis').addClass('dn');
        }
        var data = {
            id: cRowId || 0,
            categoryId: categoryCombo.getValue() || 0,
            orderNo: $.trim($('#orderNo').val()),
            deliverNo: $.trim($('#deliverNo').val()),
            ww: $.trim($('#ww').val()),
            tel: $.trim($('#tel').val()),
            qq: $.trim($('#qq').val()),
            targetName: $.trim($('.fs-input').val()),
            targetAmount: Public.currencyToNum($('#targetAmount').val(), amountPlaces),
            content: $('.info').html()
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
        categoryCombo._setDefaultSelected(-1);
        $('#orderNo').val('');
        $('#deliverNo').val('');
        $('#qq').val('');
        $('#ww').val('');
        $('#tel').val('');
        $('#clearAllBtn').trigger('click');
        $('#targetAmount').val('');
        ue.setContent('');
        $('.info').html('');
    }
};
$(function() {
    THISPAGE.init();
});
