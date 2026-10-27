var api = frameElement.api;
var oper = api.data.oper;
var cRowId = api.data.rowId;
var rowData = api.data.rowData || {};
var callback = api.data.callback;
var companyCombo;
var THISPAGE = {
    init: function() {
        if (void 0 !== cRowId) {
            Public.ajaxPost('/Base/DeliverBagOff/query', {
                id: cRowId
            }, function(res) {
                if (200 == res.status) {
                    rowData = res.data;
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
        if (rowData.id) {
            $('#no').val(rowData.no);
            $('#company').data('defItem', ['name', rowData.company]);
            $('#description').val(rowData.description);
        } else {
            $('#company').data('defItem', ['id', 0]);
        }
        companyCombo = $('#company').combo({
            data: '/Base/DeliverCompany/queryAll',
            ajaxOptions: {
                formatData: function(a) {
                    return a.data.rows;
                }
            },
            value: 'id',
            text: 'name',
            defaultSelected: rowData.company ? ['name', rowData.company] : -1,
            listHeight: 90
        }).getCombo();
        $('#company').find('input').attr('name', 'companyInput');
    },
    initValidator: function() {
        $('#manage-form').validate({
            rules: {
                no: {required: !0},
                companyInput: {required: !0}
            }
        });
    },
    postData: function(callbackType = 0) {
        if (!$('#manage-form').validate().form()) {
            return void $('#manage-form').find('input.valid-error').eq(0).focus();
        }
        var title = 'add' == oper ? '新增' : '编辑';
        var data = THISPAGE.getData();
        data && Public.ajaxPost('/Base/DeliverBagOff/' + ('add' == oper ? 'add' : 'update'), data, function(res) {
            if (res && 200 == res.status) {
                parent.parent.$.dialog.tips('发包拦截' + title + '成功！');
                if (callback && 'function' == typeof callback) {
                    callback(res.data, oper, window, callbackType);
                }
            } else {
                $.dialog.tips(title + '发包拦截' + title + '失败！' + res.msg, 2, 20);
            }
        });
    },
    getData: function() {
        var data = {
            id: cRowId || 0,
            company: companyCombo.getText(),
            no: $.trim($('#no').val()),
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
        companyCombo._setDefaultSelected(-1);
        $('#no').val('');
        $('#description').val('');
    }
};
$(function() {
    THISPAGE.init();
});
