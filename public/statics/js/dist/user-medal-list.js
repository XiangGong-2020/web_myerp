var defaultPage = Public.getDefaultPage();
var queryConditions = {
    department: '',
    position: '',
    uName: defaultPage.SYSTEM.uName,
    beginDate: Public.reduceMonths((new Date).format('yyyy-MM') + '-01', 1),
    endDate: Public.reduceDays((new Date).format('yyyy-MM') + '-01', 1)
};
var departmentCombo;
var positionCombo;
var userCombo;
var THISPAGE = {
    init: function() {
        THISPAGE.initDom();
        THISPAGE.initCombo();
        THISPAGE.initEvent();
    },
    initDom: function() {
        $('.diy-con').html('');
        Public.ajaxPost('/Base/User/userMedalList', queryConditions, function(res) {
            if (res.status == 200) {
                $.each(res.data, function(k, v) {
                    var medal = '<div class="mt10 ml10 mr10 bg-white bde b-r-3 ovh"><div class="h20 lh20 p10 bbe">' + k + '</div><div class="pr10 pb10 ovh">';
                    $.each(v, function(i, a) {
<<<<<<< HEAD
                        medal += '<div class="fl mb10 ml10"><div class="medal" style="background-image: url(../statics/img/' + a.img + '?v=2026102701)"><div class="medal-tit"></div><div class="medal-text">' + a.name + '</div></div><div class="lh20 tc gold">' + a.date + '</div></div>';
=======
                        medal += '<div class="fl mb10 ml10"><div class="medal" style="background-image: url(../statics/img/' + a.img + '?v=2026101403)"><div class="medal-tit"></div><div class="medal-text">' + a.name + '</div></div><div class="lh20 tc gold">' + a.date + '</div></div>';
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
                    });
                    medal += '</div></div>';
                    $('.diy-con').append(medal);
                });
            } else {
                $.dialog.tips('员工奖章数据获取失败！' + res.msg, 2);
            }
        });
    },
    initCombo: function() {
        departmentCombo = $('#departmentCombo').combo({
            data: '/Base/Category/list?typeNumber=departmenttype',
            ajaxOptions: {
                formatData: function(a) {
                    return a.data.rows.unshift({
                        id: -1,
                        name: '所有部门'
                    }), a.data.rows;
                }
            },
            value: 'id',
            text: 'name',
            defaultSelected: 0,
            width: 80,
            listHeight: 500,
            trigger: $.inArray('USER_MEDAL_ADVANCE', defaultPage.SYSTEM.rights) !== -1 ? !0 : !1,
            callback: {
                onChange: function() {
                    if (this._selectedIndex > 0) { // 如果选择了具体部门，才动态加载对应部门的职位
                        Public.ajaxGet('/Base/Category/list?typeNumber=positiontype&parentId=' + this.formattedData[this._selectedIndex].value, {}, function(res) {
                            if (res && 200 == res.status) {
                                res.data.rows.unshift({
                                    id: -1,
                                    name: '所有职位'
                                });
                                positionCombo.loadData(res.data.rows, -1, false);
                                userCombo.loadData([], -1, false);
                            }
                        });
                    }
                },
                onListClick: function() {
                    $('.ui-btn-group').find('.current').trigger('click');
                }
            }
        }).getCombo();
        positionCombo = $('#positionCombo').combo({
            data: [],
            value: 'id',
            text: 'name',
            defaultSelected: -1,
            width: 92,
            listHeight: 500,
            trigger: $.inArray('USER_MEDAL_ADVANCE', defaultPage.SYSTEM.rights) !== -1 ? !0 : !1,
            callback: {
                onChange: function() {
                    if (this._selectedIndex > 0) { // 如果选择了具体职位，才动态加载对应职位的员工
                        Public.ajaxGet('/Base/User/queryAll?normal=yes&position=' + this.formattedData[this._selectedIndex].text, {}, function(res) {
                            if (res && 200 == res.status) {
                                res.data.rows.unshift({
                                    id: -1,
                                    name: '所有员工'
                                });
                                userCombo.loadData(res.data.rows, -1, false);
                            }
                        });
                    }
                },
                onListClick: function() {
                    $('.ui-btn-group').find('.current').trigger('click');
                }
            }
        }).getCombo();
        userCombo = $('#userCombo').combo({
            data: [],
            value: 'id',
            text: 'name',
            defaultSelected: -1,
            width: 90,
            listHeight: 500,
            trigger: $.inArray('USER_MEDAL_ADVANCE', defaultPage.SYSTEM.rights) !== -1 ? !0 : !1,
            callback: {
                onListClick: function() {
                    $('.ui-btn-group').find('.current').trigger('click');
                }
            }
        }).getCombo();
    },
    initEvent: function() {
        $('#lastMonth').on('click', function() {
            $(this).addClass('current').siblings().removeClass('current');
            queryConditions.department = departmentCombo.getValue() == -1 ? '' : departmentCombo.getText();
            queryConditions.position = positionCombo.getValue() == -1 ? '' : positionCombo.getText();
            queryConditions.uName = $.inArray('USER_MEDAL_ADVANCE', defaultPage.SYSTEM.rights) == -1 ? defaultPage.SYSTEM.uName : (userCombo.getValue() == -1 ? '' : userCombo.getText());
            queryConditions.beginDate = Public.reduceMonths((new Date).format('yyyy-MM') + '-01', 1);
            queryConditions.endDate = Public.reduceDays((new Date).format('yyyy-MM') + '-01', 1);
            THISPAGE.initDom();
        });
        $('#curYear').on('click', function() {
            $(this).addClass('current').siblings().removeClass('current');
            queryConditions.department = departmentCombo.getValue() == -1 ? '' : departmentCombo.getText();
            queryConditions.position = positionCombo.getValue() == -1 ? '' : positionCombo.getText();
            queryConditions.uName = $.inArray('USER_MEDAL_ADVANCE', defaultPage.SYSTEM.rights) == -1 ? defaultPage.SYSTEM.uName : (userCombo.getValue() == -1 ? '' : userCombo.getText());
            queryConditions.beginDate = (new Date).format('yyyy-01-01');
            queryConditions.endDate = (new Date).format('yyyy-MM-dd');
            THISPAGE.initDom();
        });
        $('#lastYear').on('click', function() {
            $(this).addClass('current').siblings().removeClass('current');
            queryConditions.department = departmentCombo.getValue() == -1 ? '' : departmentCombo.getText();
            queryConditions.position = positionCombo.getValue() == -1 ? '' : positionCombo.getText();
            queryConditions.uName = $.inArray('USER_MEDAL_ADVANCE', defaultPage.SYSTEM.rights) == -1 ? defaultPage.SYSTEM.uName : (userCombo.getValue() == -1 ? '' : userCombo.getText());
            queryConditions.beginDate = Public.reduceMonths((new Date).format('yyyy-01-01'), 12);
            queryConditions.endDate = Public.reduceMonths((new Date).format('yyyy-12-31'), 12);
            THISPAGE.initDom();
        });
    }
};
$(function() {
    THISPAGE.init();
});
