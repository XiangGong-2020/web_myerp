var defaultPage = Public.getDefaultPage();
var api = frameElement.api;
var cRowId = api.data.rowId;
var rowData = {};
var hukouCombo;
var THISPAGE = {
    init: function() {
        if (void 0 !== cRowId) {
            Public.ajaxPost('/Base/User/queryUserById', {
                id: cRowId
            }, function(res) {
                if (200 == res.status) {
                    rowData = res.data;
                    THISPAGE.initBtns();
                    THISPAGE.initDom();
                    THISPAGE.initCombo();
                    THISPAGE.initValidator();
                } else {
                    $.dialog.tips('获取数据失败！' + res.msg, 2);
                }
            });
        } else {
            THISPAGE.initBtns();
            THISPAGE.initDom();
            THISPAGE.initCombo();
            THISPAGE.initValidator();
        }
    },
    initBtns: function() {
        api.button({
            id: 'confirm',
            name: '提交',
            defClass: 'ui_state_highlight',
            callback: function() {
                return THISPAGE.postData(), !1;
            }
        }, {
            id: 'cancel',
            name: '放弃'
        });
    },
    initDom: function() {
        rowData.id && (
            $('#name').val(rowData.name),
            $('#sex').val(rowData.sex),
            $('#sfzNo').val(rowData.sfzNo),
            $('#hukou').data('defItem', ['name', rowData.hukou]),
            $('#bankNo').val(rowData.bankNo),
            $('#zfbNo').val(rowData.zfbNo)
        );
    },
    initCombo: function() {
        hukouCombo = $('#hukou').combo({
            data: [{id: 0, name: '本市城镇'}, {id: 1, name: '本市农村'}, {id: 2, name: '外市城镇'}, {id: 3, name: '外市农村'}],
            value: 'id',
            text: 'name',
            defaultSelected: rowData.hukou >= 0 ? ['id', rowData.hukou] : -1,
            width: 220
        }).getCombo();
        $('#hukou').find('input').attr('name', 'hukouInput');
    },
    initValidator: function() {
        $('#manage-form').validate({
            rules: {
                hukouInput: {required: !0},
                bankNo: {required: !0},
                zfbNo: {required: !0}
            }
        });
    },
    postData: function() {
        if (!$('#manage-form').validate().form()) {
            return void $('#manage-form').find('input.valid-error').eq(0).focus();
        }
        var data = THISPAGE.getData();
        data && Public.ajaxPost('/Base/User/fill', data, function(res) {
            if (res && 200 == res.status) {
                parent.$.dialog.tips('补录成功！');
                api.close();
            } else {
                $.dialog.tips('补录失败！' + res.msg, 2);
            }
        });
    },
    getData: function() {
        var data = {
            hukou: hukouCombo.getText() || '',
            bankNo: $.trim($('#bankNo').val()),
            zfbNo: $.trim($('#zfbNo').val())
        };
        return data;
    }
};
$(function() {
    THISPAGE.init();
});
