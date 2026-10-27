var api = frameElement.api;
var defaultPage = Public.getDefaultPage();
var oper = api.data.oper;
var cRowId = api.data.rowId;
var callback = api.data.callback;
var rowData = {};
var combo1 = $('#combo1').combo({
    data: '/Base/User/queryAll?normal=yes',
    ajaxOptions: {
        formatData: function(a) {
            return a.data.rows;
        }
    },
    value: 'id',
    text: 'name',
    placeholder: '一键复制其他员工考评项作为本员工的考评项',
    defaultSelected: -1,
    width: 110,
    listHeight: 550,
    callback: {
        onListClick: function() {
            let uId = this.getValue();
            if (uId > -1) {
                Public.ajaxGet('/Base/User/queryUserById', {id: uId}, function(res) {
                    if (res.status === 200) {
                        $(".fs-option").each(function() {
                            $(this).removeClass("selected");
                        });
                        rowData = res.data;
                        THISPAGE.initDom();
                    }
                });
            }
        }
    }
}).getCombo();
var THISPAGE = {
    init: function() {
        if (void 0 !== cRowId) {
            if (cRowId.includes(',') == !1) {
                Public.ajaxPost('/Base/User/queryUserById', {
                    id: cRowId
                }, function(a) {
                    if (200 == a.status) {
                        rowData = a.data;
                        THISPAGE.initBtns();
                        THISPAGE.initDom();
                    } else {
                        $.dialog.tips('获取数据失败！', 2);
                    }
                });
            } else {
                THISPAGE.initBtns();
                THISPAGE.initDom();
            }
        }
    },
    initBtns: function() {
        api.button({
            id: 'confirm',
            name: '保存',
            defClass: 'ui_state_highlight',
            callback: function() {
                return THISPAGE.postData(), !1;
            }
        }, {
            id: 'cancel',
            name: '关闭'
        });
    },
    initDom: function() {
        Public.ajaxGet('/Bill/Evaluate/evaluationList', {type: '', skey: ''}, function(res) {
            if (res.status === 200) {
                var evaluation = res.data.rows;
                if (evaluation.length > 0) {
                    var optgroup = '';
                    var optgroupType = '';
                    var optgroupId = 0;
                    $.each(evaluation, function(k, v) {
                        if (k == 0) {
                            optgroupType = v.type;
                            optgroupId++;
                            optgroup += '<optgroup id="' + optgroupId + '" label="' + v.type + '"><option value="' + v.id + '">' + v.name + '</option>';
                        } else if (k == (evaluation.length - 1)) {
                            optgroup += '<option value="' + v.id + '">' + v.name + '</option></optgroup>';
                        } else {
                            if (optgroupType != v.type) {
                                optgroupType = v.type;
                                optgroupId++;
                                optgroup += '</optgroup><optgroup id="' + optgroupId + '" label="' + v.type + '"><option value="' + v.id + '">' + v.name + '</option>';
                            } else {
                                optgroup += '<option value="' + v.id + '">' + v.name + '</option>';
                            }
                        }
                    });
                    /* 异步获取数据成功之后再填充选项并加载选择事件 */
                    $('#evaCombo').html(optgroup);
                    $('#evaCombo').fSelect({
                        numDisplayed: 0,
                        showSearch: !0,
                        listHeight: 420
                    });
                    var evaluationIds = rowData.evaluationIds ? rowData.evaluationIds.toString().split(',') : [];
                    if (evaluationIds.length > 0) {
                        $(".fs-option").each(function() {
                            if(evaluationIds.includes($(this).data('value').toString())) {
                                $(this).addClass("selected");
                            }
                        });
                    }
                    $('#evaCombo').val(evaluationIds);
                    $("#evaCombo").fSelect('reloadDropdownLabel');
                    $('.fs-dropdown').removeClass('hidden');
                    $('#closeBtn').remove();
                }
            } else {
                $.dialog.tips(res.msg, 2);
            }
        });
    },
    postData: function() {
        if (Business.verifyRight('EVALUATION_ADVANCE')) {
            if ($('#evaCombo').val() && $('#evaCombo').val().length > 20) {
                parent.parent.$.dialog.tips('员工考评项目最多20项！', 2);
                return !1;
            }
            Public.ajaxPost('/Bill/Evaluate/evaluationOper', {
                ids: cRowId,
                evaluationIds: $('#evaCombo').val() ? $('#evaCombo').val().join(',') : ''
            }, function(res) {
                if (200 == res.status) {
                    parent.parent.$.dialog.tips('员工考评项目设定成功！');
                    if (callback && 'function' == typeof callback) {
                        callback(res.data, oper, window);
                    };
                } else {
                    parent.parent.$.dialog.tips('员工考评项目设定失败！' + res.msg, 2);
                }
            });
        }
    }
};
$(function() {
    THISPAGE.init();
});
