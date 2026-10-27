
var defaultPage = Public.getDefaultPage();
var queryConditions = {
    beginDate: Public.reduceMonths((new Date).format('yyyy-MM') + '-01', 1),
    endDate: Public.reduceDays((new Date).format('yyyy-MM') + '-01', 1)
};
var THISPAGE = {
    init: function() {
        THISPAGE.initDom();
        THISPAGE.initEvent();
    },
    initDom: function() {
        Public.ajaxPost('/Base/User/myEvaluation', queryConditions, function(res) {
            if (res.status == 200) {
                $('#evaluateScore').html(res.data.evaluateScore);
                if (res.data.evaluations.length > 0) {
                    var evaluationText = '';
                    $.each(res.data.evaluations, function(i, v) {
                        evaluationText += '<div class="pl20 pr20 lh50 bg-white bbe"><div class="fl mt10 mr20 w30 tc lh30 gray bdd b-r-3">' + (i + 1) + '</div>' + v + '</div>';
                    });
                    $('.diy-con').html(evaluationText);
                } else {
                    $('.diy-con').html('<div class="pl20 pr20 lh50 bg-white">暂无考评项</div>');
                }
            }
        })
    },
    initEvent: function() {
        $('#lastMonth').on('click', function() {
            $(this).addClass('current').siblings().removeClass('current');
            $('#evaluateScoreTit').html('上月考评得分（满分100）');
            queryConditions.beginDate = Public.reduceMonths((new Date).format('yyyy-MM') + '-01', 1);
            queryConditions.endDate = Public.reduceDays((new Date).format('yyyy-MM') + '-01', 1);
            THISPAGE.initDom();
        });
        $('#lastMonth2').on('click', function() {
            $(this).addClass('current').siblings().removeClass('current');
            $('#evaluateScoreTit').html('上上月考评得分（满分100）');
            queryConditions.beginDate = Public.reduceMonths((new Date).format('yyyy-MM') + '-01', 2);
            queryConditions.endDate = Public.reduceDays(Public.reduceMonths((new Date).format('yyyy-MM') + '-01', 1), 1);
            THISPAGE.initDom();
        });
    }
};
$(function() {
    THISPAGE.init();
});
