var api = frameElement.api;
var oper = api.data.oper;
var cRowId = api.data.rowId;
var callback = api.data.callback;
var typeName = api.data.typeName;
var rowData = {typeNumber: api.data.typeNumber};
var categoryCombo;
var THISPAGE = {
    init: function() {
        if (void 0 !== cRowId) {
            Public.ajaxPost('/Base/Category/query', {
                id: cRowId
            }, function(a) {
                if (200 == a.status) {
                    rowData = a.data;
                    THISPAGE.initBtns();
                    THISPAGE.initDom();
                    THISPAGE.initValidator();
                } else {
                    $.dialog.tips('获取数据失败！', 2);
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
            $('#warning').addClass('p10 m10 white bg-red b-r-3').html('警告：改变名称将导致所有使用过本' + typeName + '的地方全部被改变！禁止随意改名，以免造成数据混乱！')
            $('#name').val(rowData.name);
        }
        if (rowData.typeNumber == 'goodstype') {
            $('#category').closest('li').removeClass('dn');
            $('#onlypositionCombo').closest('li').removeClass('dn');
        }
        if (rowData.typeNumber == 'positiontype') {
            $('#categoryName').html('所属部门');
            $('#category').closest('li').removeClass('dn');
            categoryCombo = $('#category').combo({
                data: '/Base/Category/list?typeNumber=departmenttype',
                ajaxOptions: {
                    formatData: function(a) {
                        return a.data.rows;
                    }
                },
                value: 'id',
                text: 'name',
                defaultSelected: rowData.parentId >= 0 ? ['id', rowData.parentId] : 0,
                listHeight: 310
            }).getCombo();
        } else {
            categoryCombo = Public.categoryTree($('#category'), {
                height: 420,
                defaultSelectValue: rowData.parentId >= 0 ? rowData.parentId : ''
            });
        }
        Public.ajaxGet('/Base/Category/list', {typeNumber: 'positiontype'}, function(res) {
            if (res.status === 200) {
                var onlyposition = '';
                $.each(res.data.rows, function(i, v) {
                    onlyposition += '<option value="' + v.id + '">' + v.name + '</option>';
                });
                /* 异步获取数据成功之后再填充选项并加载选择事件 */
                $('#onlyposition').html(onlyposition);
                $('#onlypositionCombo').fSelect({
                    id: 'onlypositionFselect',
                    showSearch: !1,
                    listHeight: 300,
                    placeholder: '请选择（留空则默认所有职位可见）'
                });
                if (rowData.onlyposition) {
                    $('#onlypositionFselect .fs-input').val(rowData.onlyposition);
                    var names = rowData.onlyposition.toString().split(',');
                    $("#onlypositionFselect .fs-option").each(function() {
                        if(names.indexOf($(this).children('.fs-option-label').html()) != -1) {
                            $(this).addClass("selected");
                        }
                    });
                }
            } else {
                $.dialog.tips(data1.msg, 2);
            }
        });
    },
    initValidator: function() {
        $('#manage-form').validate({
            rules: {
                name: {required: !0}
            }
        });
    },
    postData: function(callbackType = 0) {
        if (!$('#manage-form').validate().form()) {
            return void $('#manage-form').find('input.valid-error').eq(0).focus();
        }
        var data = THISPAGE.getData();
        if (data.id == data.parentId) {
            return void $.dialog.tips('不能和上级分类相同！', 2);
        }
        if ('add' == oper) {
            data && Public.ajaxPost('/Base/Category/add', data, function(res) {
                if (res && 200 == res.status) {
                    parent.parent.$.dialog.tips('新增成功！');
                    if (callback && 'function' == typeof callback) {
                        callback(res.data, oper, window, callbackType);
                    }
                } else {
                    $.dialog.tips('新增失败！' + res.msg, 2);
                }
            });
        } else {
            if (rowData.name != data.name) {
                $.dialog.confirm('注意：改变本' + typeName + '将导致所有使用过该' + typeName + '的地方也被改变！确定强行保存？', function() {
                    data && Public.ajaxPost('/Base/Category/update', data, function(res) {
                        if (res && 200 == res.status) {
                            parent.parent.$.dialog.tips('保存成功！');
                            if (callback && 'function' == typeof callback) {
                                callback(res.data, oper, window, callbackType);
                            }
                        } else {
                            $.dialog.tips('保存失败！' + res.msg, 2);
                        }
                    });
                });
            } else {
                data && Public.ajaxPost('/Base/Category/update', data, function(res) {
                    if (res && 200 == res.status) {
                        parent.parent.$.dialog.tips('保存成功！');
                        if (callback && 'function' == typeof callback) {
                            callback(res.data, oper, window, callbackType);
                        }
                    } else {
                        $.dialog.tips('保存失败！' + res.msg, 2);
                    }
                });
            }
        }
    },
    getData: function() {
        var data = {
            id: cRowId || -1,
            typeNumber: rowData.typeNumber,
            name: $.trim($('#name').val()),
            parentId: categoryCombo.getValue() || 0,
            onlyposition: $.trim($('#onlypositionFselect .fs-input').val())
        };
        return data;
    },
    resetForm: function(a) {
        var uiTitle = $(".ui_title", parent.document).text().replace('◉ 视窗', '');
        if (uiTitle.includes('编辑')) {
            $(".ui_title", parent.document).text(uiTitle.replace('编辑','新增'));
        }
        oper = 'add';
        cRowId = -1;
        categoryCombo.selectByValue('');
        $('#name').val('');
        $('#clearAllBtn').trigger('click');
    }
};
$(function() {
    THISPAGE.init();
});
