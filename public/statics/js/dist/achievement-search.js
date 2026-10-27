var defaultPage = Public.getDefaultPage();
var api = frameElement.api;
var uId = api ? api.data.uId : defaultPage.SYSTEM.uId; // 如果是弹窗模式则取携带数据，否则取自身登录账号数据
var uName = api ? api.data.uName : defaultPage.SYSTEM.uName; // 如果是弹窗模式则取携带数据，否则取自身登录账号数据
var queryConditions = {
    uId: uId,
    uName: uName,
    isSelf: (api ? 0 : 1)
};
var THISPAGE = {
    init: function() {
        if (api) { // 如果是弹窗模式
            THISPAGE.initBtns();
        }
        THISPAGE.initDom();
        THISPAGE.initEvent();
    },
    initBtns: function() {
        api.button({
            id: 'cancel',
            name: '关闭'
        });
    },
    initDom: function() {
        $('#beginDate').val((new Date).format('yyyy-MM-01'));
        $('#endDate').val((new Date).format('yyyy-MM-dd'));
        if (api) { // 如果是弹窗模式
            $('#name').html(uName);
            $('.wrapper').css({'margin': '0'});
        } else {
            $('#datePicker').addClass('dn');
            $('#search').remove();
        }
    },
    initEvent: function() {
        laydate.render({
            elem: "#datePicker",
            range: ['#beginDate', '#endDate'],
            done: function() {
                setTimeout(function() {
                    $('#search').trigger('click');
                }, 300)
            }
        });
        $('#search').on('click', function() {
            $(this).addClass('current').siblings().removeClass('current');
            queryConditions.beginDate = $('#beginDate').val();
            queryConditions.endDate = $('#endDate').val();
            THISPAGE.achievementSearch();
        });
        $('#curMonth').on('click', function() {
            $(this).addClass('current').siblings().removeClass('current');
            $('#beginDate').val((new Date).format('yyyy-MM-01'));
            $('#endDate').val((new Date).format('yyyy-MM-dd'));
            queryConditions.beginDate = $('#beginDate').val();
            queryConditions.endDate = $('#endDate').val();
            THISPAGE.achievementSearch();
        });
        $('#lastMonth').on('click', function() {
            $(this).addClass('current').siblings().removeClass('current');
            $('#beginDate').val(Public.reduceMonths((new Date).format('yyyy-MM') + '-01', 1));
            $('#endDate').val(Public.reduceDays((new Date).format('yyyy-MM') + '-01', 1));
            queryConditions.beginDate = $('#beginDate').val();
            queryConditions.endDate = $('#endDate').val();
            THISPAGE.achievementSearch();
        });
        $('#refresh').on('click', function() {
            window.location.reload();
        });
        $('#lastMonth').trigger('click');
    },
    achievementSearch: function() {
        $('.wrapper').append('<div id="div-lock"><div class="loading">查询中...</div></div>');
        Public.ajaxPost('/Bill/Achievement/achievementSearch', queryConditions, function(a) {
            $('.wrapper > #div-lock').remove();
            if (200 == a.status) {
                //出勤信息
                if (a.attendance.length > 0) {
                    var attendanceInfo = '';
                    for (var i = 0; i < a.attendance.length; i++) {
                        attendanceInfo += (
                            '<div class="ovh">' +
                            '<div class="lh20 gray">日期 ' +
                            a.attendance[i].beginDate + ' ~ ' + a.attendance[i].endDate +
                            '，备注 ' + (a.attendance[i].description || '无') + '</div>' +
                            '<div class="fl lh28 pl10 pr10 bde b-r-3-left">打卡 ' + Number(a.attendance[i].workDays) + ' 天</div>' +
                            '<div class="fl ml-1 lh28 pl10 pr10 bde">补班 ' + Number(a.attendance[i].fillHours) + ' 小时</div>' +
                            '<div class="fl ml-1 lh28 pl10 pr10 bde">加班 ' + Number(a.attendance[i].overtimeHours) + ' 小时</div>' +
                            '<div class="fl ml-1 lh28 pl10 pr10 bde">计时 ' + Number(a.attendance[i].recordHours) + ' 小时</div>' +
                            '<div class="fl ml-1 lh28 pl10 pr10 bde">值班 ' + Number(a.attendance[i].dutyTimes) + ' 次</div>' +
                            '<div class="fl ml-1 lh28 pl10 pr10 bde">迟到 ' + Number(a.attendance[i].lateTimes) + ' 次</div>' +
                            '<div class="fl ml-1 lh28 pl10 pr10 bde">早退 ' + Number(a.attendance[i].leaveearlyTimes) + ' 次</div>' +
                            '<div class="fl ml-1 lh28 pl10 pr10 bde">请假 ' + Number(a.attendance[i].clockoffDays) + ' 天</div>' +
                            '<div class="fl ml-1 lh28 pl10 pr10 bde">调休 ' + Number(a.attendance[i].clockadjRestDays) + ' 天</div>' +
                            '<div class="fl ml-1 lh28 pl10 pr10 bde">补休 ' + Number(a.attendance[i].fillRestDays) + ' 天</div>' +
                            '<div class="fl ml-1 lh28 pl10 pr10 bde">外勤 ' + Number(a.attendance[i].travelDays) + ' 天</div>' +
                            '<div class="fl ml-1 lh28 pl10 pr10 bde">调班 ' + Number(a.attendance[i].clockadjWorkDays) + ' 天</div>' +
                            '<div class="fl ml-1 lh28 pl10 pr10 bde">补班 ' + Number(a.attendance[i].fillDays) + ' 天</div>' +
                            '<div class="fl ml-1 lh28 pl10 pr10 bde b-r-3-right">全勤 ' + (Number(a.attendance[i].isFull) == 1 ? '<span class="fb green">是</span>' : '<span class="fb red">否</span>') + '</div>' +
                            '<div class="fl ml-1 lh28 pl10 pr10 bde">备注 ' + (a.attendance[i].description || '无') + '</div>' +
                            '</div>' +
                            '</div>'
                        );
                    }
                    $('.no-data').addClass('dn');
                    $('#info-attendance').html(attendanceInfo).parent().removeClass('dn');
                } else {
                    $('#info-attendance').parent().addClass('dn');
                }
                //考评信息
                if (Number(a.evaluateScore) > 0) {
                    $('.no-data').addClass('dn');
                    $('#info-evaluate').html('<div class="fl lh28 pl10 pr10 bde b-r-3">考评得分 ' + a.evaluateScore + '</div>').parent().removeClass('dn');
                } else {
                    $('#info-evaluate').parent().addClass('dn');
                }
                //采购业绩
                if (Number(a.performancePur.core) > 0) {
                    $('.no-data').addClass('dn');
                    $('#performance-pur').html(
                        '<div class="fl lh28 pl10 pr10 bde b-r-3-left">订单采购 ' + a.performancePur.orderinfo + ' 项</div>' +
                        '<div class="fl ml-1 lh30 pl10 pr10 bde">特殊采购 ' + a.performancePur.contractinfo + ' 项</div>' +
                        '<div class="fl ml-1 lh30 pl10 pr10 bde b-r-3-right">业绩指数 ' + a.performancePur.core + '</div>' +
                        (api ? (
                            '<div class="fl ml10 ovh lh30 night bg-silver b-r-3">' +
                            '<div class="fl pl10 pr10">管理层可见：</div>' +
                            '<div class="fl ml-1 pl10 pr10">新增商家 ' + a.performancePur.supplier + ' 个</div>' +
                            '<div class="fl ml-1 pl10 pr10">竞价毛利 ' + Number(a.performancePur.deAmount).toFixed(2) + ' 元</div>' +
                            '</div>'
                        ) : '')
                    ).parent().removeClass('dn');
                } else {
                    $('#performance-pur').parent().addClass('dn');
                }
                //销售业绩
                if (Number(a.performanceSale.core) > 0) {
                    $('.no-data').addClass('dn');
                    $('#performance-sale').html(
                        '<div class="fl lh28 pl10 pr10 bde b-r-3-left">投标金额 ' + Number(a.performanceSale.bidpro).toFixed(2) + ' 元</div>' +
                        '<div class="fl ml-1 lh28 pl10 pr10 bde">手工订单 ' + Number(a.performanceSale.order).toFixed(2) + ' 元</div>' +
                        '<div class="fl ml-1 lh28 pl10 pr10 bde">特殊销售 ' + Number(a.performanceSale.contract).toFixed(2) + ' 元</div>' +
                        '<div class="fl ml-1 lh28 pl10 pr10 bde">网店订单 ' + Number(a.performanceSale.onlineshop).toFixed(2) + ' 元</div>' +
                        '<div class="fl ml-1 lh28 pl10 pr10 bde">销售总额 ' +
                        (Number(a.performanceSale.order) + Number(a.performanceSale.contract) + Number(a.performanceSale.onlineshop)).toFixed(2) + ' 元</div>' +
                        '<div class="fl ml-1 lh28 pl10 pr10 bde b-r-3-right">业绩指数 ' + a.performanceSale.core + '</div>'
                    ).parent().removeClass('dn');
                } else {
                    $('#performance-sale').parent().addClass('dn');
                }
                //加工业绩
                if (Number(a.performancePro.core) > 0) {
                    $('.no-data').addClass('dn');
                    $('#performance-produce').html(
                        '<div class="fl lh28 pl10 pr10 bde b-r-3-left">配货已交付产量 ' + a.performancePro.proKittingQtyAll + ' 套</div>' +
                        '<div class="fl ml-1 lh28 pl10 pr10 bde">配货未审核产量 ' + a.performancePro.proKittingQtyUncheck + ' 套</div>' +
                        '<div class="fl ml-1 lh28 pl10 pr10 bde">配货入业绩产量 ' + a.performancePro.proKittingQty + ' 套</div>' +
                        '<div class="fl lh28 pl10 pr10 bde b-r-3-left">加工已交付产量 ' + a.performancePro.proProcessingQtyAll + ' 套</div>' +
                        '<div class="fl ml-1 lh28 pl10 pr10 bde">加工未审核产量 ' + a.performancePro.proProcessingQtyUncheck + ' 套</div>' +
                        '<div class="fl ml-1 lh28 pl10 pr10 bde">加工入业绩产量 ' + a.performancePro.proProcessingQty + ' 套</div>' +
                        '<div class="fl ml-1 lh28 pl10 pr10 bde b-r-3-right">业绩指数 ' + a.performancePro.core + '</div>'
                    ).parent().removeClass('dn');
                } else {
                    $('#performance-produce').parent().addClass('dn');
                }
                //打包业绩
                if (Number(a.performancePack.core) > 0) {
                    $('.no-data').addClass('dn');
                    $('#performance-pack').html(
                        '<div class="fl lh28 pl10 pr10 bde b-r-3-left">打包数量 ' + (parseInt(a.performancePack.totalCount1) + parseInt(a.performancePack.totalCount2)) + ' 个</div>' +
                        '<div class="fl ml-1 lh28 pl10 pr10 bde b-r-3-right">业绩指数 ' + a.performancePack.core + '</div>' +
                        (api ? (
                            '<div class="fl ml10 ovh lh30 night bg-silver b-r-3">' +
                            '<div class="fl pl10 pr10">管理层可见：</div>' +
                            '<div class="fl ml-1 pl10 pr10">打包重量 ' + (parseInt(a.performancePack.totalWeight1) + parseInt(a.performancePack.totalWeight2)) +
                            ' KG</div>' +
                            '<div class="fl ml-1 pl10 pr10">20KG以下数量 ' + parseInt(a.performancePack.totalCount1) +' 个</div>' +
                            '<div class="fl ml-1 pl10 pr10">20KG以下重量 ' + parseInt(a.performancePack.totalWeight1) +' KG</div>' +
                            '<div class="fl ml-1 pl10 pr10">20KG及以上数量 ' + parseInt(a.performancePack.totalCount2) +' 个</div>' +
                            '<div class="fl ml-1 pl10 pr10">20KG及以上重量 ' + parseInt(a.performancePack.totalWeight2) +' KG</div>' +
                            '</div>'
                        ) : '')
                    ).parent().removeClass('dn');
                } else {
                    $('#performance-pack').parent().addClass('dn');
                }
                //开发业绩
                if (Number(a.workTask) > 0) {
                    $('.no-data').addClass('dn');
                    $('#info-task').html('<div class="fl lh28 pl10 pr10 bde b-r-3">参与产品开发 ' + a.workTask + ' 个</div>').parent().removeClass('dn');
                } else {
                    $('#info-task').parent().addClass('dn');
                }
                //反馈责任
                if (a.serviceTarget.length > 0) {
                    var serviceTargetInfo = '';
                    for (var i = 0; i < a.serviceTarget.length; i++) {
                        serviceTargetInfo += (
                            '<div class="es">' +
                            '<span class="gray">责任人 </span>' + a.serviceTarget[i].targetName +
                            '&nbsp;&nbsp;' +
                            '<span class="gray">损失金额 </span>' + Number(a.serviceTarget[i].targetAmount) + ' 元' +
                            '&nbsp;&nbsp;' +
                            '<span class="gray">录入日期 </span>' + a.serviceTarget[i].beginDate +
                            '&nbsp;&nbsp;' +
                            '<span class="gray">反馈描述 </span><span title="' + a.serviceTarget[i].content + '">' + a.serviceTarget[i].content + '</span>' +
                            '</div>'
                        );
                    }
                    $('.no-data').addClass('dn');
                    $('#service-target').html(serviceTargetInfo).parent().removeClass('dn');
                } else {
                    $('#service-target').parent().addClass('dn');
                }
                // 工作目标
                if (a.workPlan.length > 0) {
                    var workPlanInfo = '';
                    for (var i = 0; i < a.workPlan.length; i++) {
                        workPlanInfo += (
                            '<div class="mb10 pt10 pb10 bde b-r-3"><div class="pl10 pr10">' +
                            '<span class="gray">日期 </span>' + a.workPlan[i].date +
                            '&nbsp;&nbsp&nbsp;&nbsp&nbsp;&nbsp' +
                            '<span class="gray">类型 </span>' + a.planTypeArr[a.workPlan[i].type] + '计划' +
                            '&nbsp;&nbsp;&nbsp;&nbsp&nbsp;&nbsp' +
                            '<span class="gray">状态 </span>' + (a.workPlan[i].isDisable == 1 ? '已完成' : '未完成') +
                            '</div><div class="pl10 es"><span class="gray">目标 </span><span title="' + a.workPlan[i].target + '">' + a.workPlan[i].target + '</span>' +
                            '</div><div class="pl10 es"><span class="gray">总结 </span><span title="' + a.workPlan[i].summary + '">' + a.workPlan[i].summary + '</span>' +
                            '</div></div>'
                        );
                    }
                    $('.no-data').addClass('dn');
                    $('#info-plan').html(workPlanInfo).parent().removeClass('dn');
                } else {
                    $('#info-plan').parent().addClass('dn');
                }
                if (a.attendance.length == 0 && Number(a.evaluateScore) == 0 && Number(a.performancePur.core) == 0 && Number(a.performanceSale.core) == 0 && Number(a.performancePro.core) == 0 && Number(a.performancePack.core) == 0 && Number(a.workTask) == 0 && a.serviceTarget.length == 0 && a.workPlan.length == 0) {
                    $('.no-data').removeClass('dn');
                }
            } else {
                $('.no-data').removeClass('dn');
                $.dialog.tips('获取绩效信息失败！' + a.msg, 2);
            }
        });

    }
};
$(function() {
    THISPAGE.init();
});
