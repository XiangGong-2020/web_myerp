var api = frameElement.api;
var urlParam = Public.urlParam();
var userName = api.data.userName;
var name = api.data.name;
var user;
var curGroup;
var combo1 = $('#combo1').combo({
    data: '/Base/User/queryAll?normal=yes',
    ajaxOptions: {
        formatData: function(a) {
            return a.data.rows;
        }
    },
    value: 'id',
    text: 'name',
    placeholder: '选择已有账号一键复制权限',
    defaultSelected: -1,
    width: 110,
    listHeight: 550,
    callback: {
        onListClick: function() {
            let uId = this.getValue();
            let uName = this.getText();
            if (uId > -1) {
                $('#grid').jqGrid('setGridParam', {
                    url: '/Base/User/queryalluserright?name=' + uName
                }).trigger('reloadGrid');
                $(".fs-option").each(function() {
                    $(this).removeClass("selected");
                });
                Public.ajaxGet('/Base/User/queryUserById', {id: uId}, function(res) {
                    if (res.status === 200) {
                        user = res.data;
                        if(user.allowVideoTypeIds) {
                            var ids = user.allowVideoTypeIds.toString().split(',');
                            $('#videoTypeCombo').val(ids);
                            var names = [];
                            $(".fs-option").each(function() {
                                if(ids.indexOf($(this)[0]['dataset']['value']) != -1) {
                                    $(this).addClass("selected");
                                    names.push($(this).children('.fs-option-label').html());
                                }
                            });
                            $("#videoTypeCombo").fSelect('reloadDropdownLabel');
                            $('.fs-input').val(names.toString());
                        };
                    }
                });
            }
        }
    }
}).getCombo();
var THISPAGE = {
    init: function() {
        THISPAGE.initBtns();
        THISPAGE.initDom();
        THISPAGE.initGrid();
        THISPAGE.initEvent();
    },
    initBtns: function() {
        api.button({
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
        $('#name').text(name);
        Public.ajaxGet('/Base/User/queryUserByUserName', {userName: userName}, function(res) {
            if (res.status === 200) {
                user = res.data;
                Public.ajaxGet('/Base/Category/list', {typeNumber: "videotype"}, function(res2) {
                    if (res2.status === 200) {
                        var allVideoType = '';
                        $.each(res2.data.rows, function(i, v) {
                            allVideoType += '<option value="' + (v.id) + '">' + v.name + '</option>';
                        });
                        $('#allVideoType').html(allVideoType);
                        $('#videoTypeCombo').fSelect({
                            showSearch: !1,
                            listHeight: 500
                        });
                        if(user.allowVideoTypeIds) {
                            var ids = user.allowVideoTypeIds.toString().split(',');
                            $('#videoTypeCombo').val(ids);
                            var names = [];
                            $(".fs-option").each(function() {
                                if(ids.indexOf($(this)[0]['dataset']['value']) != -1) {
                                    $(this).addClass("selected");
                                    names.push($(this).children('.fs-option-label').html());
                                }
                            });
                            $("#videoTypeCombo").fSelect('reloadDropdownLabel');
                            $('.fs-input').val(names.toString());
                        };
                    } else {
                        $.dialog.tips(res2.msg, 2);
                    }
                });
            } else {
                $.dialog.tips(res.msg, 2);
            }
        });
    },
    initGrid: function() {
        $('#grid').jqGrid({
            url: '/Base/User/queryalluserright?userName=' + userName,
            datatype: 'json',
            colModel: [{
                name: 'fobjectid',
                label: '<input type="checkbox" id="all" class="vm">',
                width: 30,
                fixed: !0,
                formatter: function(val, opt, row) {
                    if (curGroup !== val) {
                        return '<input class="group" type="checkbox" id="' + val + '">';
                    } else {
                        return '';
                    };
                }
            }, {
                name: 'fobject',
                label: '权限组',
                width: 180,
                fixed: !0,
                classes: 'fb',
                formatter: function(val, opt, row) {
                    if (curGroup !== row.fobjectid) {
                        curGroup = row.fobjectid;
                        return val;
                    } else {
                        return '';
                    };
                }
            }, {
                name: 'faction',
                label: '权限名',
                width: 150,
                fixed: !0
            }, {
                name: 'frightid',
                label: '权限ID',
                width: 120,
                fixed: !0,
                classes: 'gray'
            }, {
                name: 'fright',
                label: '<label for="all">授权</label>',
                width: 100,
                formatter: function(val, opt, row) {
                    var html_str = '<input type="checkbox" class="ckbox" data-for="' + row.fobjectid + '" data-id="' + row.frightid + '"';
                    if (row.faction === '查询') {
                        html_str = html_str + 'data-view="true"';
                    };
                    if (val > 0) {
                        return html_str + ' checked="checked">';
                    } else {
                        return html_str + '>';
                    };
                }
            }],
            cmTemplate: {sortable: !1, title: !1},
            jsonReader: {root: 'data.rows', records: 'data.records', repeatitems: !1, id: -1},
            width: 1228,
            height: 446,
            altRows: !1,
            gridview: !0,
            multiselect: !1,
            shrinkToFit: !0,
            forceFit: !1,
            sortname: 'path',
            sortorder: 'ASC',
            scroll: !0,
            pager: '#page',
            page: 1,
            rowNum: 1000,
            viewrecords: !0,
            loadComplete: function(data) {
                data.status == -1 && $.dialog.tips(data.msg, 2);
                $('.group').each(function(index, element) {
                    var groupId = $(this).attr('id');
                    var $_ckbox = $('.ckbox[data-for=' + groupId + ']');
                    if ($_ckbox.length === $_ckbox.filter(':checked').length) {
                        this.checked = true;
                    };
                });
            },
            loadError: function(a, b) {
                $.dialog.tips('Type: ' + b + '; Response: ' + a.status + ' ' + a.statusText, 2);
            }
        });
    },
    initEvent: function() {
        $('#all').on('click', function(a) {
            a.stopPropagation();
            if (this.checked) {
                $('.ckbox').each(function() {
                    this.checked = true;
                });
                $('.group').each(function() {
                    this.checked = true;
                });
            } else {
                $('.ckbox').removeAttr('checked');
                $('.group').removeAttr('checked');
            }
        });
        $('.grid-wrap').on('click', '.group', function() {
            var groupId = $(this).attr('id');
            if (this.checked) {
                $('.ckbox[data-for=' + groupId + ']').each(function() {
                    this.checked = true;
                });
            } else {
                $('.ckbox[data-for=' + groupId + ']').removeAttr('checked');
            };
            $(this).closest('tr').find('input').filter('[data-view=true]').trigger('checkChange');
        });
        $('.grid-wrap').on('click', '.ckbox', function() {
            var groupId = $(this).data('for');
            var $_group = $('.ckbox[data-for=' + groupId + ']');
            var $_view = $_group.filter('[data-view=true]');
            var $_others = $_group.not('[data-view=true]');
            if (!$(this).data('view')) {
                if (this.checked && $_view.length > 0) {
                    $_view[0].checked = true;
                };
            } else {
                if ($_others.length > 0 && $_others.filter(':checked').length > 0) {
                    this.checked = true;
                };
            };
            $_view.trigger('checkChange');
            if ($_group.length === $_group.filter(':checked').length) {
                $('#' + groupId)[0].checked = true;
            } else {
                $('#' + groupId).removeAttr('checked');
            };
        });
    },
    postData: function(callbackType = 0) {
        if (Business.verifyRight('USER_UPDATE')) {
            var rightids = [];
            $('.ckbox').each(function(b) {
                if (this.checked) {
                    rightids.push($(this).data('id'));
                }
            });
            Public.ajaxPost('/Base/User/addRights2OutUser', {
                userName: userName,
                rightid: rightids.join(','),
                allowVideoTypeIds: $('#videoTypeCombo').val() ? $('#videoTypeCombo').val().join(',') : ''
            }, function(res) {
                if (res && 200 == res.status) {
                    parent.$.dialog.tips('保存成功！');
                    this.api.close();
                } else {
                    $.dialog.tips('保存失败！' + res.msg, 2);
                }
            });
        }
    }
};
$(function() {
    THISPAGE.init();
});
