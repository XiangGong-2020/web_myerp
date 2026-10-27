var api = frameElement.api;
var defaultPage = Public.getDefaultPage();
var oper = api.data.oper;
var cRowId = api.data.rowId;
var callback = api.data.callback;
var amountPlaces = Number(defaultPage.SYSTEM.amountPlaces);
var rowData = {};
var content = '';
var levelCombo;
var billfromCombo;
var THISPAGE = {
    init: function() {
        if (void 0 !== cRowId) {
            Public.ajaxPost('/Base/ReviewHold/query', {
                id: cRowId
            }, function(res) {
                if (200 == res.status) {
                    rowData = res.data;
                    content = rowData.content;
                    THISPAGE.initBtns();
                    THISPAGE.initDom();
                    THISPAGE.initValidator();
                } else {
                    $.dialog.tips('获取数据失败！' + res.msg, 2);
                }
            });
        } else {
            THISPAGE.initBtns();
            THISPAGE.initDom();
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
        if ('edit' == oper) {
            $('#orderNo').val(rowData.orderNo);
            $('#description').val(rowData.description);
        } else {
            $('#orderNo').val('');
            $('#description').val('');
        }
        levelCombo = $('#level').combo({
            data: [
                {id: 0, name: '中评'},
                {id: 1, name: '差评'}
            ],
            value: 'id',
            text: 'name',
            width: 90,
            listHeight: 150,
            defaultSelected: rowData.level >= 0 ? ['id', rowData.level] : -1
        }).getCombo();
        billfromCombo = $('#billfrom').combo({
            data: '/Base/Billfrom/queryAll?isDisable=0&exclude=手工录入,官网',
            ajaxOptions: {
                formatData: function(a) {
                    return a.data.rows;
                }
            },
            value: 'id',
            text: 'name',
            width: 300,
            listHeight: 150,
            defaultSelected: rowData.billfromId > 0 ? ['id', rowData.billfromId] : -1
        }).getCombo();
        $('#level').find('input').attr('name', 'levelInput');
        $('#billfrom').find('input').attr('name', 'billfromInput');
    },
    initValidator: function() {
        $('#manage-form').validate({
            rules: {
                levelInput: {required: !0},
                billfromInput: {required: !0},
                orderNo: {required: !0}
            }
        });
    },
    postData: function(callbackType = 0) {
        if (Business.verifyRight('SERVICE_UPDATE')) {
            if (!$('#manage-form').validate().form()) {
                return void $('#manage-form').find('input.valid-error').eq(0).focus();
            }
            var data = THISPAGE.getData();
            var title = 'add' == oper ? '新增差评删改' : '编辑差评删改';
            Public.ajaxPost('/Base/ReviewHold/' + ('add' == oper ? 'add' : 'update'), data, function(res) {
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
            level: levelCombo.getValue() || 0,
            billfromId: billfromCombo.getValue() || 0,
            orderNo: $.trim($('#orderNo').val()),
            description: $('#description').val()
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
        $('#orderNo').val('');
        $('#description').val('');
        levelCombo._setDefaultSelected(-1);
        billfromCombo._setDefaultSelected(-1);
    }
};
$(function() {
    THISPAGE.init();
});
