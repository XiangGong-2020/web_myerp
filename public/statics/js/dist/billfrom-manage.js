var api = frameElement.api;
var defaultPage = Public.getDefaultPage();
var oper = api.data.oper;
var cRowId = api.data.rowId;
var callback = api.data.callback;
var rowData = {};
var companyCombo;
var THISPAGE = {
    init: function() {
        if (void 0 !== cRowId) {
            Public.ajaxPost('/Base/Billfrom/query', {
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
        if (rowData.id) {
            $('#name').val(rowData.name);
            $('#company').data('defItem', ['name', rowData.company]);
            $('#description').val(rowData.description);
        } else {
            $('#name').val('');
            $('#company').data('defItem', ['name', '']);
        }
        companyCombo = $('#company').combo({
            data: '/Base/Company/queryAll',
            ajaxOptions: {
                formatData: function(a) {
                    return a.data.rows;
                }
            },
            value: 'id',
            text: 'name',
            defaultSelected: rowData.company ? ['name', rowData.company] : -1
        }).getCombo();
        $('#company').find('input').attr('name', 'companyInput');
    },
    initEvent: function() {
        laydate.render({
            elem: "#date",
        });
        Public.limitLength($('#name'), 30);
    },
    initValidator: function() {
        $('#manage-form').validate({
            rules: {
                name: {required: !0},
                companyInput: {required: !0}
            }
        });
    },
    postData: function(callbackType = 0) {
        if (Business.verifyRight('BILLFROM_ADVANCE')) {
            if (!$('#manage-form').validate().form()) {
                return void $('#manage-form').find('input.valid-error').eq(0).focus();
            }
            var title = 'add' == oper ? '新增销售渠道' : '编辑销售渠道';
            var data = THISPAGE.getData();
            data && Public.ajaxPost('/Base/Billfrom/' + ('add' == oper ? 'add' : 'update'), data, function(res) {
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
            name: $.trim($('#name').val()),
            company: companyCombo.getText(),
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
        $('#name').val('');
        companyCombo._setDefaultSelected(-1);
        $('#description').val('');
    }
};
$(function() {
    THISPAGE.init();
});
