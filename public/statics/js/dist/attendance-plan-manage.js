
var defaultPage = Public.getDefaultPage();
var api = frameElement.api;
var oper = api.data.oper;
var cRowId = api.data.rowId;
var rowData = api.data.rowData || {};
var callback = api.data.callback;
var yearcombo;
var THISPAGE = {
    init: function() {
        if (void 0 !== cRowId) {
            Public.ajaxPost('/Base/AttendancePlan/query', {
                id: cRowId
            }, function(a) {
                if (200 == a.status) {
                    rowData = a.data;
                    THISPAGE.initBtns();
                    THISPAGE.initDom();
                    THISPAGE.initEvent();
                    THISPAGE.initValidator();
                } else {
                    $.dialog.tips('获取数据失败！', 2);
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
            $('#m1').val(rowData.m1);
            $('#m2').val(rowData.m2);
            $('#m3').val(rowData.m3);
            $('#m4').val(rowData.m4);
            $('#m5').val(rowData.m5);
            $('#m6').val(rowData.m6);
            $('#m7').val(rowData.m7);
            $('#m8').val(rowData.m8);
            $('#m9').val(rowData.m9);
            $('#m10').val(rowData.m10);
            $('#m11').val(rowData.m11);
            $('#m12').val(rowData.m12);
            $('#description').val(rowData.description);
        }
        yearcombo = $('#year').combo({
            data: [
                {id: 0, name: '2024'},
                {id: 1, name: '2025'},
                {id: 2, name: '2026'},
                {id: 3, name: '2027'},
                {id: 4, name: '2028'},
                {id: 5, name: '2029'},
                {id: 6, name: '2030'},
                {id: 7, name: '2031'},
                {id: 8, name: '2032'},
                {id: 9, name: '2033'},
                {id: 10, name: '2034'},
                {id: 11, name: '2035'},
                {id: 12, name: '2036'},
                {id: 13, name: '2037'},
                {id: 14, name: '2038'},
                {id: 15, name: '2039'},
                {id: 16, name: '2040'},
                {id: 17, name: '2041'},
                {id: 18, name: '2042'},
                {id: 19, name: '2043'},
                {id: 20, name: '2044'},
                {id: 21, name: '2045'},
                {id: 22, name: '2046'},
                {id: 23, name: '2047'},
                {id: 24, name: '2048'},
                {id: 25, name: '2049'},
                {id: 26, name: '2050'},
                {id: 27, name: '2051'},
                {id: 28, name: '2052'},
                {id: 29, name: '2053'},
                {id: 30, name: '2054'},
                {id: 31, name: '2055'},
                {id: 32, name: '2056'},
                {id: 33, name: '2057'},
                {id: 34, name: '2058'},
                {id: 35, name: '2059'},
                {id: 36, name: '2060'}
            ],
            value: 'id',
            text: 'name',
            defaultSelected: rowData.year ? ['name', rowData.year] : -1
        }).getCombo();
        $('#year').find('input').attr('name', 'yearInput');
    },
    initEvent: function() {
    },
    initValidator: function() {
        $('#manage-form').validate({
            rules: {
                yearInput: {required: !0},
                m1: {required: !0},
                m2: {required: !0},
                m3: {required: !0},
                m4: {required: !0},
                m5: {required: !0},
                m6: {required: !0},
                m7: {required: !0},
                m8: {required: !0},
                m9: {required: !0},
                m10: {required: !0},
                m11: {required: !0},
                m12: {required: !0}
            }
        });
    },
    postData: function(callbackType = 0) {
        if (Business.verifyRight('SYS_ADMIN')) {
            if (!$('#manage-form').validate().form()) {
                return void $('#manage-form').find('input.valid-error').eq(0).focus();
            }
            var data = THISPAGE.getData();
            var title = 'add' == oper ? '新增' : '编辑';
            data && Public.ajaxPost('/Base/AttendancePlan/' + ('add' == oper ? 'add' : 'update'), data, function(res) {
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
            year: yearcombo.getText(),
            m1: parseInt($.trim($('#m1').val())),
            m2: parseInt($.trim($('#m2').val())),
            m3: parseInt($.trim($('#m3').val())),
            m4: parseInt($.trim($('#m4').val())),
            m5: parseInt($.trim($('#m5').val())),
            m6: parseInt($.trim($('#m6').val())),
            m7: parseInt($.trim($('#m7').val())),
            m8: parseInt($.trim($('#m8').val())),
            m9: parseInt($.trim($('#m9').val())),
            m10: parseInt($.trim($('#m10').val())),
            m11: parseInt($.trim($('#m11').val())),
            m12: parseInt($.trim($('#m12').val())),
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
        yearcombo._setDefaultSelected(-1);
        $('#m1').val('');
        $('#m2').val('');
        $('#m3').val('');
        $('#m4').val('');
        $('#m5').val('');
        $('#m6').val('');
        $('#m7').val('');
        $('#m8').val('');
        $('#m9').val('');
        $('#m10').val('');
        $('#m11').val('');
        $('#m12').val('');
        $('#description').val('');
    }
};
$(function() {
    THISPAGE.init();
});
