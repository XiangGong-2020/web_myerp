var api = frameElement.api;
var oper = api.data.oper;
var cRowId = api.data.rowId;
var callback = api.data.callback;
var rowData = {};
var keywordTypeCombo;
var THISPAGE = {
    init: function() {
        if (void 0 !== cRowId) {
            Public.ajaxPost('/Base/Bid/getkeyword', {
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
        if ('editKeyword' == oper) {
            $('#name').val(rowData.name);
        } else {
            $('#name').val('');
        }
        keywordTypeCombo = $('#keywordType').combo({
            data: [{id: 0, name: '包含关键词'}, {id: 1, name: '排除关键词'}],
            value: 'id',
            text: 'name',
            defaultSelected: rowData.keywordType >= 0 ? ['id', rowData.keywordType] : -1
        }).getCombo();
        $('#keywordType').find('input').attr('name', 'keywordTypeInput');
    },
    initEvent: function() {
        Public.bindEnterSkip($('#base-form'), function() {
            $('#grid tr.jqgrow:eq(0) td:eq(0)').trigger('click');
        });
    },
    initValidator: function() {
        $('#manage-form').validate({
            rules: {
                keywordTypeInput: {required: !0},
                name: {required: !0}
            }
        });
    },
    postData: function(callbackType = 0) {
        if (Business.verifyRight('BIDKEYWORD_UPDATE')) {
            if (!$('#manage-form').validate().form()) {
                return void $('#manage-form').find('input.valid-error').eq(0).focus();
            }
            var title = 'addKeyword' == oper ? '新增' : '编辑';
            var data = THISPAGE.getData();
            Public.ajaxPost('/Base/Bid/' + ('addKeyword' == oper ? 'addKeyword' : 'updateKeyword'), data, function(res) {
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
            name: $.trim($('#name').val()),
            keywordType: keywordTypeCombo.getValue()
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
        keywordTypeCombo._setDefaultSelected(-1);
    }
};
$(function() {
    THISPAGE.init();
});
/* 关键词只允许输入指定字符 */
function isValidChar(evt) {
    var charCode = evt.which ? evt.which : evt.keyCode;
    // 判断是否为汉字
    if (/^[\u4e00-\u9fa5]$/.test(String.fromCharCode(charCode))) return true;
    // 判断是否为大小写字母
    if ((charCode >= 65 && charCode <= 90) || (charCode >= 97 && charCode <= 122)) return true;
    // 判断是否为数字
    if (charCode >= 48 && charCode <= 57) return true;
    // 判断是否为空格
    if (charCode == 32) return true;
    return false;
}
