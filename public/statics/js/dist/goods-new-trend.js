var defaultPage = Public.getDefaultPage();
var THISPAGE = {
    init: function () {
        THISPAGE.initDom();
        THISPAGE.initChart();
        THISPAGE.initEvent();
    },
    initDom: function () {
        $('#beginDate').val(Public.reduceMonths(new Date().format('yyyy-MM-dd'), 24));
        $('#endDate').val(defaultPage.SYSTEM.endDate || new Date().format('yyyy-MM-dd'));
    },
    initChart: function () {
        THISPAGE.chartConfig = {
            title: {
                text: '新品趋势图',
                subtext: '按时段、分年/月/日展示数据',
                left: 'center',
            },
            toolbox: {
                feature: {
                    mark: { show: true },
                    restore: { show: true },
                    saveAsImage: { show: true },
                },
            },
            legend: {
                top: '80',
                data: [],
            },
            grid: {
                top: '120',
                left: '20',
                right: '50',
                bottom: '20',
                containLabel: true
            },
            tooltip: {
                trigger: 'axis',
                axisPointer: {
                    type: 'cross',
                    label: {
                        backgroundColor: '#6a7985',
                    },
                },
            },
            xAxis: [
                {
                    type: 'category',
                    boundaryGap: false,
                    data: [],
                },
            ],
            yAxis: [
                {
                    type: 'value',
                },
            ],
            series: [],
        };
        Public.ajaxPost('/Bill/Report/goodsNewTrend', { type: 'near30days' }, function (res) {
            res.status == -1 && $.dialog.tips(res.msg, 2);
            THISPAGE.xAxisData = [];
            THISPAGE.legendData = ['新增基础套件数'];
            THISPAGE.seriesData = [{
                name: '新增基础套件数',
                type: 'line',
                seriesLayoutBy: 'row',
                emphasis: {focus: 'series'},
                data: [],
            }];
            $.each(res.data, function(k, v) {
                THISPAGE.xAxisData.push(v['a']);
                THISPAGE.seriesData[0].data.push(v['b'] || 0);
            });
            THISPAGE.chartConfig.xAxis[0].data = THISPAGE.xAxisData;
            THISPAGE.chartConfig.legend.data = THISPAGE.legendData;
            THISPAGE.chartConfig.series = THISPAGE.seriesData;
            THISPAGE.chart = echarts.init(document.getElementById('chart'));
            THISPAGE.chart.setOption(THISPAGE.chartConfig);
        });
    },
    initEvent: function () {
        laydate.render({
            elem: '#datePicker',
            range: ['#beginDate', '#endDate'],
            done: function () {
                setTimeout(function () {
                    $('#custom').trigger('click');
                }, 300);
            },
        });
        $('.col-search-tab').on('click', function (a) {
            a.preventDefault();
            $(this).siblings().removeClass('active');
            $(this).addClass('active');
            var id = $(this).attr('id');
            var beginDate = $('#beginDate').val();
            var endDate = $('#endDate').val();
            $('.wrapper').append('<div id="div-lock"><div class="loading">数据分析中，请稍候...</div></div>');
            Public.ajaxPost('/Bill/Report/goodsNewTrend', { type: id, beginDate: beginDate, endDate: endDate }, function (res) {
                $('.wrapper > #div-lock').remove();
                res.status == -1 && $.dialog.tips(res.msg, 2);
                THISPAGE.xAxisData = [];
                THISPAGE.legendData = ['新增基础套件数'];
                THISPAGE.seriesData = [{
                    name: '新增基础套件数',
                    type: 'line',
                    seriesLayoutBy: 'row',
                    emphasis: {focus: 'series'},
                    data: [],
                }];
                $.each(res.data, function(k, v) {
                    THISPAGE.xAxisData.push(v['a']);
                    THISPAGE.seriesData[0].data.push(v['b'] || 0);
                });
                THISPAGE.chartConfig.xAxis[0].data = THISPAGE.xAxisData;
                THISPAGE.chartConfig.legend.data = THISPAGE.legendData;
                THISPAGE.chartConfig.series = THISPAGE.seriesData;
                THISPAGE.chart.dispose();
                THISPAGE.chart = echarts.init(document.getElementById('chart'));
                THISPAGE.chart.setOption(THISPAGE.chartConfig);
            });
        });
        window.addEventListener('resize', function () {
            THISPAGE.chart.resize();
        });
    },
};
$(function () {
    THISPAGE.init();
});
