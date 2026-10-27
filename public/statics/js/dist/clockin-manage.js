
var defaultPage = Public.getDefaultPage();
var api = frameElement.api;
var oper = api.data.oper;
var cRowId = api.data.rowId;
var rowData = api.data.rowData || {};
var callback = api.data.callback;
var usercombo;
var THISPAGE = {
    init: function() {
        if (void 0 !== cRowId) {
            Public.ajaxPost('/Base/Clockin/query', {
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
            $('#clockDate').val(rowData.clockDate);
            $('#amClock').val(rowData.amClock == '00:00:00' ? '' : rowData.amClock);
            $('#pmClock').val(rowData.pmClock == '00:00:00' ? '' : rowData.pmClock);
            $('#amClock2').val(rowData.amClock2 == '00:00:00' ? '' : rowData.amClock2);
            $('#pmClock2').val(rowData.pmClock2 == '00:00:00' ? '' : rowData.pmClock2);
            $('#amClock3').val(rowData.amClock3 == '00:00:00' ? '' : rowData.amClock3);
            $('#pmClock3').val(rowData.pmClock3 == '00:00:00' ? '' : rowData.pmClock3);
            $('#marks').val(rowData.marks);
            $('#description').val(rowData.description);
        } else {
            $('#clockDate').val(defaultPage.SYSTEM.endDate);
        }
        usercombo = $('#uName').combo({
            data: '/Base/User/queryAll?normal=yes',
            ajaxOptions: {
                formatData: function(a) {
                    return a.data.rows;
                }
            },
            value: 'id',
            text: 'name',
            defaultSelected: rowData.uName ? ['name', rowData.uName] : -1,
            listHeight: 434
        }).getCombo();
        $('#uName').find('input').attr('name', 'uNameInput');
        $('#marksCombo').fSelect({
            showSearch: !1,
            listHeight: 217
        });
        if ('edit' == oper && rowData.marks) {
            $('.fs-input').val(rowData.marks);
            var names = rowData.marks.toString().split(',');
            $(".fs-option").each(function() {
                if (names.indexOf($(this).children('.fs-option-label').html()) != -1) {
                    $(this).addClass("selected");
                }
            });
        }
    },
    initEvent: function() {
        laydate.render({elem: "#amClock, #pmClock, #amClock2, #pmClock2, #amClock3, #pmClock3", type: 'time'});
        laydate.render({elem: "#clockDate", type: 'date'});
    },
    initValidator: function() {
        $('#manage-form').validate({
            rules: {
                uNameInput: {required: !0},
                clockDate: {required: !0}
            }
        });
    },
    postData: function(callbackType = 0) {
        if (Business.verifyRight('CLOCKIN_UPDATE')) {
            if (!$('#manage-form').validate().form()) {
                return void $('#manage-form').find('input.valid-error').eq(0).focus();
            }
            var data = THISPAGE.getData();
            var title = 'add' == oper ? '新增' : '编辑';
            data && Public.ajaxPost('/Base/Clockin/' + ('add' == oper ? 'add' : 'update'), data, function(res) {
                if (res && 200 == res.status) {
                    if (res.data.fillClockinInfo) {
                        parent.parent.$.dialog.tips(title + '成功！' + res.data.fillClockinInfo, 1);
                    } else {
                        parent.parent.$.dialog.tips(title + '成功！');
                    }
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
            uId: usercombo.getValue(),
            uName: usercombo.getText(),
            clockDate: $.trim($('#clockDate').val()),
            amClock: $.trim($('#amClock').val()) || '00:00:00',
            pmClock: $.trim($('#pmClock').val()) || '00:00:00',
            amClock2: $.trim($('#amClock2').val()) || '00:00:00',
            pmClock2: $.trim($('#pmClock2').val()) || '00:00:00',
            amClock3: $.trim($('#amClock3').val()) || '00:00:00',
            pmClock3: $.trim($('#pmClock3').val()) || '00:00:00',
            marks: $.trim($('.fs-input').val()),
            description: $.trim($('#description').val())
        };
        return data;
    },
    resetForm: function(a) {
        var uiTitle = $(".ui_title", parent.document).text().replace('◉ 视窗', '');
        if (uiTitle.includes('编辑')) {
            $(".ui_title", parent.document).text(uiTitle.replace('编辑', '新增'));
        }
        oper = 'add';
        cRowId = void 0;
        usercombo._setDefaultSelected(-1);
        $('#clockDate').val(defaultPage.SYSTEM.endDate);
        $('#amClock').val('');
        $('#pmClock').val('');
        $('#amClock2').val('');
        $('#pmClock2').val('');
        $('#amClock3').val('');
        $('#pmClock3').val('');
        $('#clearAllBtn').trigger('click');
        $('.info').html('');
        $('#description').val('');
    }
};
$(function() {
    THISPAGE.init();
});
