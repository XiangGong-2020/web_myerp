var api = frameElement.api;
var oper = api.data.oper;
var cRowId = api.data.rowId;
var callback = api.data.callback;
var rowData = {};
var combo1;
var THISPAGE = {
    init: function() {
        if (void 0 !== cRowId) {
            Public.ajaxPost('/Base/ShopAccount/query', {
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
            $('#his').val(rowData.his);
        } else {
            $('#name').val('');
            $('#his').val('');
        }
        combo1 = $('#uName').combo({
            data: '/Base/User/queryAll?department=销售部&isDisable=0',
            ajaxOptions: {
                formatData: function(a) {
                    return a.data.rows;
                }
            },
            value: 'id',
            text: 'name',
            defaultSelected: rowData.uName ? ['name', rowData.uName] : -1,
            width: 280
        }).getCombo();
        $('#uName').find('input').attr('name', 'uNameInput');
    },
    initEvent: function() {
        $('#manage-form').on('submit', function(a) {
            a.preventDefault();
            THISPAGE.postData();
        });
    },
    initValidator: function() {
        $('#manage-form').validate({
            rules: {
                name: {required: !0},
                uNameInput: {required: !0}
            }
        });
    },
    postData: function(callbackType = 0) {
        if (Business.verifyRight('SHOPACCOUNT_UPDATE')) {
            if (!$('#manage-form').validate().form()) {
                return void $('#manage-form').find('input.valid-error').eq(0).focus();
            }
            var title = 'add' == oper ? '新增网店账号' : '编辑网店账号';
            var data = THISPAGE.getData();
            Public.ajaxPost('/Base/ShopAccount/' + ('add' == oper ? 'add' : 'update'), data, function(res) {
                if (200 == res.status) {
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
        var his = $.trim($('#his').val());
        var cur = combo1.getText() || '';
        if (cur && !(his.includes(cur))) {
            his = his == '' ? cur : (his + ',' + cur);
        }
        var data = {
            id: cRowId || 0,
            name: $.trim($('#name').val()),
            uId: combo1.getValue() || 0,
            uName: cur,
            his: his
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
        combo1._setDefaultSelected(-1);
        $('#his').val('');
    }
};
$(function() {
    THISPAGE.init();
});
