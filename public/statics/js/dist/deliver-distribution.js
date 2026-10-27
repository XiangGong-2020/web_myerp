
var defaultPage = Public.getDefaultPage();
var THISPAGE = {
    init: function() {
        THISPAGE.initDom();
        THISPAGE.initChart();
        THISPAGE.initEvent();
    },
    initDom: function() {
        $('#beginDate').val(Public.reduceMonths((new Date).format('yyyy-MM-dd'), 24));
        $('#endDate').val(defaultPage.SYSTEM.endDate || (new Date).format('yyyy-MM-dd'));
    },
    initChart: function() {
        THISPAGE.chartConfig = {
            title: {
                text: '发包分配图',
                subtext: '按时段、分物流公司展示数据',
                left: 'center'
            },
            toolbox: {
                show: true,
                feature: {
                    mark: {show: true},
                    restore: {show: true},
                    saveAsImage: {show: true}
                }
            },
            legend: {
                type: 'scroll',
                orient: 'vertical',
                left: 0,
                top: 80,
                bottom: 20,
            },
            tooltip: {
                trigger: 'item',
                formatter: '{b}<br/>{a}：{c}<br/>占比：{d}%'
            },
            series: [
                {
                    name: '个数',
                    type: 'pie',
                    radius: '50%',
                    center: ['50%', '50%'],
                    itemStyle: {
                        borderRadius: 5
                    },
                    label: {
                        show: true
                    },
                    data: [],
                    emphasis: {
                        itemStyle: {
                            shadowBlur: 10,
                            shadowOffsetX: 0,
                            shadowColor: 'rgba(0, 0, 0, 0.5)'
                        }
                    }
                }
            ]
        };
        Public.ajaxPost('/Bill/Report/deliverDistribution', {type: 'near30days'}, function(f) {
            f.status == -1 && $.dialog.tips(f.msg, 2);
            THISPAGE.chartData = [];
            $.each(f.data, function(k, v) {
                THISPAGE.chartData[k] = {
                    name: v['a'],
                    value: v['b']
                };
            });
            THISPAGE.chartConfig.series[0].data = THISPAGE.chartData;
            THISPAGE.chart = echarts.init(document.getElementById('chart'));
            THISPAGE.chart.setOption(THISPAGE.chartConfig);
        });
    },
    initEvent: function() {
        laydate.render({
            elem: '#datePicker',
            range: ['#beginDate', '#endDate'],
            done: function() {
                setTimeout(function() {
                    $('#custom').trigger('click');
                }, 300);
            }
        });
        $('.col-search-tab').on('click', function(a) {
            a.preventDefault();
            $(this).siblings().removeClass('active');
            $(this).addClass('active');
            var id = $(this).attr('id');
            var beginDate = $('#beginDate').val();
            var endDate = $('#endDate').val();
            $('.wrapper').append('<div id="div-lock"><div class="loading">数据分析中，请稍候...</div></div>');
            Public.ajaxPost('/Bill/Report/deliverDistribution', {type: id, beginDate: beginDate, endDate: endDate}, function(f) {
                $('.wrapper > #div-lock').remove();
                f.status == -1 && $.dialog.tips(f.msg, 2);
                THISPAGE.chartData = [];
                $.each(f.data, function(k, v) {
                    THISPAGE.chartData[k] = {
                        name: v['a'],
                        value: v['b']
                    };
                });
                THISPAGE.chartConfig.series[0].data = THISPAGE.chartData;
                THISPAGE.chart.setOption(THISPAGE.chartConfig);
            });
        });
        window.addEventListener('resize', function() {
            THISPAGE.chart.resize();
        });
    }
};
$(function() {
    THISPAGE.init();
});
