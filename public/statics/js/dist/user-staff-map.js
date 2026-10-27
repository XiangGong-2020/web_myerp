
var defaultPage = Public.getDefaultPage();
var staffData = {};
var THISPAGE = {
    init: function() {
        THISPAGE.initDom();
        THISPAGE.initEvent();
    },
    initDom: function() {
        Public.ajaxPost('/Base/User/userMap', {}, function(res) {
            if (res.status == 200) {
                staffData = res.data;
                var group_level_1 = '';
                var group_level_2 = '';
                var saleGroup = '';
                var purGroup = '';
                var proGroup = '';
                var stoGroup = '';
                var tecGroup = '';
                var opeGroup = '';
                var otherGroup = '';
                $.each(staffData, function(i, v) {
                    $staff_con = '<div class="staff-con staff-level-' + v.groupLevel + '" data-id="' + v.name + '">'
<<<<<<< HEAD
                        + '<img class="staff-avatar" src="' + v.avatar + '?v=2026102701">'
=======
                        + '<img class="staff-avatar" src="' + v.avatar + '?v=2026101403">'
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
                        + '<div class="staff-onduty white b-r-3 ' + (v.onDuty == '在岗' ? 'bg-green' : (v.onDuty == '离岗' ? 'bg-aqua' : (v.onDuty == '' ? 'dn' : (v.onDuty.startsWith("申请") == true ? 'bg-purple' : 'bg-red')))) + '">' + v.onDuty + '</div>'
                        + '<div class="staff-name">' + v.name + '</div>'
                        + '<div class="staff-position">' + v.position + '</div>'
                        + '<div class="staff-mobile"><i class="iconfont icon-mobile f12 mr2"></i>' + v.mobile + '</div>'
                        + '</div>';
                    if (v.groupLevel == 1) {
                        group_level_1 += $staff_con;
                    } else if (v.groupLevel == 2) {
                        group_level_2 += $staff_con;
                    } else {
                        if (v.department == '销售部') {
                            saleGroup += $staff_con;
                        } else if (v.department == '采购部') {
                            purGroup += $staff_con;
                        } else if (v.department == '生产部') {
                            proGroup += $staff_con;
                        } else if (v.department == '仓储部') {
                            stoGroup += $staff_con;
                        } else if (v.department == '技术部') {
                            tecGroup += $staff_con;
                        } else if (v.department == '运营部') {
                            opeGroup += $staff_con;
                        } else {
                            otherGroup += $staff_con;
                        }
                    }
                });
                $('.group-level-1').html(group_level_1);
                $('.group-level-2').html(group_level_2);
                $('#saleGroup').html(saleGroup);
                $('#purGroup').html(purGroup);
                $('#proGroup').html(proGroup);
                $('#stoGroup').html(stoGroup);
                $('#tecGroup').html(tecGroup);
                $('#opeGroup').html(opeGroup);
                $('#otherGroup').html(otherGroup);
            } else {
                $.dialog.tips(res.msg, 2);
            }
        });
    },
    initEvent: function() {
        $('#refresh').on('click', function() {
            window.location.reload();
        });
        $('.wrapper').on('click', '.staff-con', function() {
            var id = $(this).data('id');
            var data = staffData[id];
            $('.staff-side ').html(
                '<i class="staff-side-close iconfont icon-shanchu" title="关闭"></i>'
<<<<<<< HEAD
                + '<img class="staff-side-avatar" src="' + data.avatar + '?v=2026102701">'
=======
                + '<img class="staff-side-avatar" src="' + data.avatar + '?v=2026101403">'
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
                + '<div class="staff-side-avatar-right">'
                + '<div class="staff-side-tit">姓名</div><div class="staff-side-text">' + data.name + '</div>'
                + '<div class="staff-side-tit">成长等级</div><div class="staff-side-text">' + data.level + '</div>'
                + '<div class="staff-side-tit">所在部门</div><div class="staff-side-text">' + data.department + '</div>'
                + '<div class="staff-side-tit">所处职位</div><div class="staff-side-text">' + data.position + '</div>'
                + '<div class="staff-side-tit">上级主管</div><div class="staff-side-text">' + (data.superiorsName || '无') + '</div>'
                + '</div>'
                + '<div class="staff-side-tit">常用手机</div><div class="staff-side-text">' + data.mobile + '</div>'
                + '<div class="staff-side-tit">常用微信</div><div class="staff-side-text">' + data.wechat + '</div>'
                + '<div class="staff-side-tit">岗位职责</div><div class="staff-side-textarea">' + data.duty + '</div>'
            ).css('right', '0');
        });
        $('.wrapper').on('click', '.staff-side-close', function() {
            $(this).parent().css('right', '-500px');
        });
    }
};
$(function() {
    THISPAGE.init();
});
