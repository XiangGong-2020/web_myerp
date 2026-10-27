var defaultPage = Public.getDefaultPage();
var api = frameElement.api;
var uId = api.data.uId;
var uName = api.data.uName;
var queryConditions = {
    uId: uId,
    uName: uName,
    beginDate: Public.reduceMonths((new Date).format('yyyy-MM') + '-01', 1),
    endDate: Public.reduceDays((new Date).format('yyyy-MM') + '-01', 1)
};
var originalData;
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
                return THISPAGE.postData(), !1;
            }
        }, {
            id: 'cancel',
            name: '关闭'
        });
    },
    initDom: function() {
        Public.ajaxPost('/Bill/Evaluate/queryEvaluateReferenceInfo', queryConditions, function(res) {
            if (res && 200 == res.status) {
                //历史考评得分信息
                if (res.data.scoreHis.length > 0) {
                    var scoreHis = res.data.scoreHis;
                    var scoreHisInfo = '';
                    for (var i = 0; i < scoreHis.length; i++) {
                        scoreHisInfo += (
                            '<div class="fl ml-1 mb-1 pl10 pr10 lh28 bdc">' + scoreHis[i].beginDate.slice(0, 4) + '年' + scoreHis[i].beginDate.slice(5, 7) + '月：' + scoreHis[i].score + ' 分</div>'
                        );
                    }
                    $('#scoreHis').html(scoreHisInfo).parent().removeClass('dn');
                } else {
                    $('#scoreHis').html('<span class="gray">暂无</span>').parent().addClass('dn');
                }
                //考勤信息
                if (res.data.attendance.length > 0) {
                    var attendance = res.data.attendance;
                    var attendanceInfo = '';
                    for (var i = 0; i < attendance.length; i++) {
                        attendanceInfo += (
                            '<div class="ovh">' +
                            '<div class="lh20 gray">日期 ' + attendance[i].beginDate + ' ~ ' + attendance[i].endDate + '，备注 ' + (attendance[i].description || '无') + '</div>' +
                            '<div class="fl mt5 mr20 lh28">打卡 ' + Number(attendance[i].workDays) + ' 天</div>' +
                            '<div class="fl mt5 mr20 lh28">补班 ' + Number(attendance[i].fillHours) + ' 小时</div>' +
                            '<div class="fl mt5 mr20 lh28">加班 ' + Number(attendance[i].overtimeHours) + ' 小时</div>' +
                            '<div class="fl mt5 mr20 lh28">计时 ' + Number(attendance[i].recordHours) + ' 小时</div>' +
                            '<div class="fl mt5 mr20 lh28">值班 ' + Number(attendance[i].dutyTimes) + ' 次</div>' +
                            '<div class="fl mt5 mr20 lh28">迟到 ' + Number(attendance[i].lateTimes) + ' 次</div>' +
                            '<div class="fl mt5 mr20 lh28">早退 ' + Number(attendance[i].leaveearlyTimes) + ' 次</div>' +
                            '<div class="fl mt5 mr20 lh28">请假 ' + Number(attendance[i].clockoffDays) + ' 天</div>' +
                            '<div class="fl mt5 mr20 lh28">调休 ' + Number(attendance[i].clockadjRestDays) + ' 天</div>' +
                            '<div class="fl mt5 mr20 lh28">补休 ' + Number(attendance[i].fillRestDays) + ' 天</div>' +
                            '<div class="fl mt5 mr20 lh28">外勤 ' + Number(attendance[i].travelDays) + ' 天</div>' +
                            '<div class="fl mt5 mr20 lh28">调班 ' + Number(attendance[i].clockadjWorkDays) + ' 天</div>' +
                            '<div class="fl mt5 mr20 lh28">补班 ' + Number(attendance[i].fillDays) + ' 天</div>' +
                            '<div class="fl mt5 mr20 lh28">全勤 ' + (Number(attendance[i].isFull) == 1 ? '<span class="fb green">是</span>' : '<span class="fb red">否</span>') + '</div>' +
                            '</div>' +
                            '</div>'
                        );
                    }
                    $('#attendance').html(attendanceInfo).parent().removeClass('dn');
                } else {
                    $('#attendance').html('<span class="gray">暂无</span>').parent().addClass('dn');
                }
                //配货信息
                if (res.data.performancePro && res.data.performancePro.proQtyAll > 0) {
                    $('#performancePro').html(
                        '<div class="fl ml-1 mb-1 lh28 pl10 pr10 bdc">已交付产量 ' + res.data.performancePro.proQtyAll + ' 套</div>' +
                        '<div class="fl ml-1 mb-1 lh28 pl10 pr10 bdc">未审核产量 ' + res.data.performancePro.proQtyUncheck + ' 套</div>' +
                        '<div class="fl ml-1 mb-1 lh28 pl10 pr10 bdc">入业绩产量 ' + res.data.performancePro.proQty + ' 套</div>'
                    ).parent().removeClass('dn');
                } else {
                    $('#performancePro').html('<span class="gray">暂无</span>').parent().addClass('dn');
                }
                //打包信息
                if (res.data.performancePack && res.data.performancePack.totalCount1 > 0) {
                    $('#performancePack').html(
                        '<div class="fl ml-1 mb-1 lh28 pl10 pr10 bdc">打包数量 ' + (parseInt(res.data.performancePack.totalCount1) + parseInt(res.data.performancePack.totalCount2)) + ' 个</div>' +
                        '<div class="fl ml-1 mb-1 lh28 pl10 pr10 bdc">打包重量 ' + (parseInt(res.data.performancePack.totalWeight1) + parseInt(res.data.performancePack.totalWeight2)) +
                        ' KG</div>' +
                        '<div class="fl ml-1 mb-1 lh28 pl10 pr10 bdc">20KG以下数量 ' + parseInt(res.data.performancePack.totalCount1) +' 个</div>' +
                        '<div class="fl ml-1 mb-1 lh28 pl10 pr10 bdc">20KG以下重量 ' + parseInt(res.data.performancePack.totalWeight1) +' KG</div>' +
                        '<div class="fl ml-1 mb-1 lh28 pl10 pr10 bdc">20KG及以上数量 ' + parseInt(res.data.performancePack.totalCount2) +' 个</div>' +
                        '<div class="fl ml-1 mb-1 lh28 pl10 pr10 bdc">20KG及以上重量 ' + parseInt(res.data.performancePack.totalWeight2) +' KG</div>' +
                        '</div>'
                    ).parent().removeClass('dn');
                } else {
                    $('#performancePack').html('<span class="gray">暂无</span>').parent().addClass('dn');
                }
                //发票录入
                if (res.data.invoice && res.data.invoice.num > 0) {
                    $('#invoice').html('<span class="">共录入 ' + res.data.invoice.num + ' 票，票面总额 ' + res.data.invoice.amount + ' 元。</span>').parent().removeClass('dn');
                } else {
                    $('#invoice').html('<span class="gray">暂无</span>').parent().addClass('dn');
                }
                //反馈责任
                if (res.data.serviceTarget.length > 0) {
                    var serviceTarget = res.data.serviceTarget;
                    var serviceTargetInfo = '';
                    for (var i = 0; i < serviceTarget.length; i++) {
                        serviceTargetInfo += (
                            '<div class="es">' +
                            '<span class="gray">责任人 </span>' + serviceTarget[i].targetName +
                            '&nbsp;&nbsp;' +
                            '<span class="gray">损失金额 </span>' + Number(serviceTarget[i].targetAmount) + ' 元' +
                            '&nbsp;&nbsp;' +
                            '<span class="gray">录入日期 </span>' + serviceTarget[i].beginDate +
                            '&nbsp;&nbsp;' +
                            '<span class="gray">反馈描述 </span><span title="' + serviceTarget[i].content + '">' + serviceTarget[i].content + '</span>' +
                            '</div>'
                        );
                    }
                    $('#serviceTarget').html(serviceTargetInfo).parent().removeClass('dn');
                } else {
                    $('#serviceTarget').html('<span class="gray">暂无</span>').parent().addClass('dn');
                }
                //工作记录
                if (res.data.workRecord.length > 0) {
                    var workRecord = res.data.workRecord;
                    var workRecordInfo = '';
                    for (var i = 0; i < workRecord.length; i++) {
                        workRecordInfo += (
                            '<div class="mb10 pt10 pb10"><div class="pl10 pr10">' +
                            '<span class="gray">记录类型 </span>' + (workRecord[i].type == 0 ? '常规记录' : workRecord[i].type == 1 ? '贡献记录' : '过失记录') +
                            '&nbsp;&nbsp;&nbsp;&nbsp&nbsp;&nbsp' +
                            '<span class="gray">发生日期 </span>' + workRecord[i].targetDate +
                            '&nbsp;&nbsp&nbsp;&nbsp&nbsp;&nbsp' +
                            '<span class="gray">目标员工 </span>' + workRecord[i].targetName +
                            '&nbsp;&nbsp&nbsp;&nbsp&nbsp;&nbsp' +
                            '<span class="gray">损失金额 </span>' + workRecord[i].targetAmount +
                            '</div><div class="pl10 es"><span class="gray">记录详情 </span><span title="' + workRecord[i].content + '">' + workRecord[i].content + '</span>' +
                            '</div></div>'
                        );
                    }
                    $('#workRecord').html(workRecordInfo).parent().removeClass('dn');
                } else {
                    $('#workRecord').html('<span class="gray">暂无</span>').parent().addClass('dn');
                }
                // 工作目标
                if (res.data.workPlan.length > 0) {
                    var workPlan = res.data.workPlan;
                    var planTypeArr = res.data.planTypeArr;
                    var workPlanInfo = '';
                    for (var i = 0; i < workPlan.length; i++) {
                        workPlanInfo += (
                            '<div class="mb10 pt10 pb10"><div class="pl10 pr10">' +
                            '<span class="gray">日期 </span>' + workPlan[i].date +
                            '&nbsp;&nbsp&nbsp;&nbsp&nbsp;&nbsp' +
                            '<span class="gray">类型 </span>' + planTypeArr[workPlan[i].type] + '计划' +
                            '&nbsp;&nbsp;&nbsp;&nbsp&nbsp;&nbsp' +
                            '<span class="gray">状态 </span>' + (workPlan[i].isDisable == 1 ? '已完成' : '未完成') +
                            '</div><div class="pl10 es"><span class="gray">目标 </span><span title="' + workPlan[i].target + '">' + workPlan[i].target + '</span>' +
                            '</div><div class="pl10 es"><span class="gray">总结 </span><span title="' + workPlan[i].summary + '">' + workPlan[i].summary + '</span>' +
                            '</div></div>'
                        );
                    }
                    $('#workPlan').html(workPlanInfo).parent().removeClass('dn');
                } else {
                    $('#workPlan').html('<span class="gray">暂无</span>').parent().addClass('dn');
                }
                //采购建议
                if (res.data.purSuggest && res.data.purSuggest.num > 0) {
                    $('#purSuggest').html('共提供采购建议 ' + res.data.purSuggest.num + ' 条，预计节约采购成本 ' + Number(Number(res.data.purSuggest.purAmount) - Number(res.data.purSuggest.amount)).toFixed(2) + ' 元。').parent().removeClass('dn');
                } else {
                    $('#purSuggest').html('<span class="gray">暂无</span>').parent().addClass('dn');
                }
            } else {
                $.dialog.tips(res.msg, 2);
            }
        });
    },
    initGrid: function() {
        $('#grid').jqGrid({
            url: '/Bill/Evaluate/queryEvaluateInfo',
            datatype: 'json',
            postData: queryConditions,
            colModel: [{
                name: 'n',
                label: '考评项目',
                width: 100,
                title: !0,
                editable: !1,
                classes: 'ui-ellipsis'
            }],
            cmTemplate: {sortable: !1, title: !1},
            jsonReader: {root: 'data.rows', records: 'data.records', repeatitems: !1, id: 'id'},
            width: 900,
            height: 600,
            altRows: !1,
            gridview: !0,
            rownumbers: !0,
            shrinkToFit: !0,
            forceFit: !1,
            cellEdit: !1,
            loadComplete: function(a) {
                if (a.status == 200 && a.data) {
                    originalData = a.data;
                    $('#name').html(a.data.uName);
                    $('#lastName').html(a.data.lastName);
                    $('#lastTime').html(a.data.lastTime);
                    $('#score').val(a.data.score > 0 ? a.data.score : '');
                    $('#description').val(a.data.description);
                } else {
                    $.dialog.tips(res.msg, 2);
                }
            },
            gridComplete: function() {
                setTimeout(function() {
                    $('#grid').jqGrid('setGridHeight', $(window).height() - 149);
                }, 100);
            }
        });
    },
    initEvent: function() {
        $('#lastMonth').on('click', function() {
            $(this).addClass('current').siblings().removeClass('current');
            queryConditions.beginDate = Public.reduceMonths((new Date).format('yyyy-MM') + '-01', 1);
            queryConditions.endDate = Public.reduceDays((new Date).format('yyyy-MM') + '-01', 1);
            THISPAGE.initDom();
            $('#grid').jqGrid('setGridParam', {
                postData: queryConditions
            }).trigger('reloadGrid');
        });
        $('#lastMonth2').on('click', function() {
            $(this).addClass('current').siblings().removeClass('current');
            queryConditions.beginDate = Public.reduceMonths((new Date).format('yyyy-MM') + '-01', 2);
            queryConditions.endDate = Public.reduceDays(Public.reduceMonths((new Date).format('yyyy-MM') + '-01', 1), 1);
            THISPAGE.initDom();
            $('#grid').jqGrid('setGridParam', {
                postData: queryConditions
            }).trigger('reloadGrid');
        });
        $('#refresh').on('click', function() {
            THISPAGE.initDom();
        });
    },
    postData: function() {
        if (Business.verifyRight('EVALUATION_QUERY')) {
            originalData.score = parseInt($.trim($('#score').val())) || 0;
            originalData.description = $.trim($('#description').val());
            if (originalData.score > 0) {
                Public.ajaxPost('/Bill/Evaluate/updateEvaluateInfo', {
                    postData: JSON.stringify(originalData)
                }, function(res) {
                    if (res && 200 == res.status) {
                        parent.parent.$.dialog.tips('员工考评表保存成功！');
                        window.api.close();
                    } else {
                        $.dialog.tips('员工考评表保存失败！' + res.msg, 2);
                    }
                });
            } else {
                $.dialog.tips('主观评分需填入 1 ~ 110 的整数！', 2);
            }
        }
    }
};
$(function() {
    THISPAGE.init();
});
