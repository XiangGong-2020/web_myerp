var api = frameElement.api;
var defaultPage = Public.getDefaultPage();
var oper = api.data.oper;
var cRowId = api.data.rowId;
var callback = api.data.callback;
var rowData = {};
var provinceCombo;;
var siteTypeCombo;
var statusCombo;
var THISPAGE = {
    init: function() {
        if (void 0 !== cRowId) {
            Public.ajaxPost('/Base/Bid/getsite', {
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
                id: 'enEdit',
                name: '转到编辑模式',
                defClass: 'ui_state_highlight',
                callback: function() {
                    if (Business.verifyRight('BIDSITE_UPDATE')) {
                        parent.$('.ui_buttons .dialog-btn.ui_state_highlight').remove();
                        api.config.disEdit = !1;
                        api.config.onlyAdd = !0;
                        var uiTitle = $(".ui_title", parent.document).text().replace('◉ 视窗', '');
                        if (uiTitle.includes('查看')) {
                            $(".ui_title", parent.document).text(uiTitle.replace('查看', '编辑'));
                        }
                        window.location.reload();
                        return !1;
                    }
                }
            }, {
                id: 'cancel',
                name: '关闭'
            });
        } else {
            if (api.config.onlyAdd) {
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
        }
    },
    initDom: function() {
        if ('editSite' == oper) {
            $('#name').val(rowData.name);
            $('#url').val(rowData.url);
            $('#note').val(rowData.note);
            if (api.config.disEdit) {
                $('#name').attr('disabled', true).addClass('ui-input-dis');
                $('#url').attr('disabled', true).addClass('ui-input-dis');
                $('#note').attr('disabled', true).addClass('ui-input-dis');
            }
        } else {
            $('#name').val('');
            $('#url').val('');
            $('#note').val('');
        }
        provinceCombo = $('#province').combo({
            data: [
                {id: 0, name: '北京'},
                {id: 1, name: '天津'},
                {id: 2, name: '河北'},
                {id: 3, name: '山西'},
                {id: 4, name: '内蒙'},
                {id: 5, name: '辽宁'},
                {id: 6, name: '吉林'},
                {id: 7, name: '黑龙江'},
                {id: 8, name: '上海'},
                {id: 9, name: '江苏'},
                {id: 10, name: '浙江'},
                {id: 11, name: '安徽'},
                {id: 12, name: '福建'},
                {id: 13, name: '江西'},
                {id: 14, name: '山东'},
                {id: 15, name: '河南'},
                {id: 16, name: '湖北'},
                {id: 17, name: '湖南'},
                {id: 18, name: '广东'},
                {id: 19, name: '广西'},
                {id: 20, name: '海南'},
                {id: 21, name: '重庆'},
                {id: 22, name: '四川'},
                {id: 23, name: '贵州'},
                {id: 24, name: '云南'},
                {id: 25, name: '西藏'},
                {id: 26, name: '陕西'},
                {id: 27, name: '甘肃'},
                {id: 28, name: '青海'},
                {id: 29, name: '宁夏'},
                {id: 30, name: '新疆'},
                {id: 31, name: '台湾'},
                {id: 32, name: '香港区'},
                {id: 33, name: '澳门'},
                {id: 34, name: '海外'}
            ],
            value: 'id',
            text: 'name',
            defaultSelected: rowData.province ? ['name', rowData.province] : -1,
            listHeight: 440,
            disabled: api.config.disEdit ? !0 : !1
        }).getCombo();
        siteTypeCombo = $('#siteType').combo({
            data: [
                {id: 0, name: '招标平台'},
                {id: 1, name: '招标代理'},
                {id: 2, name: '政府主办'},
                {id: 3, name: '招标企业'},
                {id: 4, name: '学校官网'},
                {id: 5, name: '公众号'}
            ],
            value: 'id',
            text: 'name',
            listHeight: 186,
            defaultSelected: rowData.siteType >= 0 ? ['id', rowData.siteType] : -1,
            disabled: api.config.disEdit ? !0 : !1
        }).getCombo();
        statusCombo = $('#status').combo({
            data: [
                {id: 0, name: '收录正常'},
                {id: 1, name: '收录异常'}
            ],
            value: 'id',
            text: 'name',
            defaultSelected: rowData.status >= 0 ? ['id', rowData.status] : -1,
            disabled: api.config.disEdit ? !0 : !1
        }).getCombo();
        $('#province').find('input').attr('name', 'provinceInput');
        $('#siteType').find('input').attr('name', 'siteTypeInput');
        $('#status').find('input').attr('name', 'statusInput');
    },
    initEvent: function() {
    },
    initValidator: function() {
        $('#manage-form').validate({
            rules: {
                provinceInput: {required: !0},
                name: {required: !0},
                siteTypeInput: {required: !0},
                statusInput: {required: !0}
            }
        });
    },
    postData: function(callbackType = 0) {
        if (Business.verifyRight('BIDSITE_UPDATE')) {
            if (!$('#manage-form').validate().form()) {
                return void $('#manage-form').find('input.valid-error').eq(0).focus();
            }
            var title = 'addSite' == oper ? '新增' : '编辑';
            var data = THISPAGE.getData();
            data && Public.ajaxPost('/Base/Bid/' + ('addSite' == oper ? 'addSite' : 'updateSite'), data, function(res) {
                if (res && 200 == res.status) {
                    parent.parent.$.dialog.tips(title + '成功！');
                    if (callback && 'function' == typeof callback) {
                        callback(res.data, oper, window, callbackType);
                    } else {
                        this.api.close();
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
            province: provinceCombo.getText(),
            name: $.trim($('#name').val()),
            url: $.trim($('#url').val()),
            siteType: siteTypeCombo.getValue(),
            status: statusCombo.getValue(),
            note: $.trim($('#note').val())
        };
        if (data.siteType != 5 && data.url == '') {
            $.dialog.tips('除公众号外，其他类型招标网站必须录入网站地址！', 2);
            return !1;
        }
        return data;
    },
    resetForm: function(a) {
        var uiTitle = $(".ui_title", parent.document).text().replace('◉ 视窗', '');
        if (uiTitle.includes('编辑')) {
            $(".ui_title", parent.document).text(uiTitle.replace('编辑', '新增'));
        }
        oper = 'add';
        cRowId = void 0;
        provinceCombo.selectByText('');
        $('#name').val('');
        $('#url').val('');
        siteTypeCombo._setDefaultSelected(-1);
        statusCombo._setDefaultSelected(-1);
        $('#note').val('');
    }
};
$(function() {
    THISPAGE.init();
});
