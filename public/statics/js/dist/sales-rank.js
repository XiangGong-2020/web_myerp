var defaultPage = Public.getDefaultPage();
var queryConditions = {
    categoryId: -1,
    type: 1,
    datetype: 2,
    beginDate: '',
    endDate: '',
    limit: 20,
    export: 0
};
var categoryTree = Public.categoryTree($('#category'), {
    onlyposition: 1,
    rootTxt: '所有产品类别',
    treeSettings: {
        callback: {
            beforeClick: function(a, b) {
                $('#category').data('id', b.id).val(b.name);
                $('#search').trigger('click');
            }
        }
    }
});
var statusCombo1 = $('#combo1').combo({
    data: [
        {id: 1, name: '按销售额'},
        {id: 2, name: '按销售量'}
    ],
    value: 'id',
    text: 'name',
    defaultSelected: 0,
    width: 78,
    callback: {
        onListClick: function() {
            $('#search').trigger('click');
        }
    }
}).getCombo();
var statusCombo2 = $('#combo2').combo({
    data: [
        {id: 1, name: '自选时段'},
        {id: 2, name: '本月'},
        {id: 3, name: '上月'},
        {id: 4, name: '近12月'}
    ],
    value: 'id',
    text: 'name',
    defaultSelected: 1,
    width: 78,
    callback: {
        onListClick: function() {
            $('#search').trigger('click');
        }
    }
}).getCombo();
var THISPAGE = {
    init: function() {
        THISPAGE.initDom();
        THISPAGE.initEvent();
    },
    initDom: function() {
        $('#beginDate').val(defaultPage.SYSTEM.beginDate);
        $('#endDate').val(defaultPage.SYSTEM.endDate);
        THISPAGE.chartConfig = {
            title: {
                text: '销量排行图',
                subtext: '按产品类别、销售额/量、时间段展示数据',
                left: 'center'
            },
            toolbox: {
                feature: {
                    mark: { show: true },
                    restore: { show: true },
                    saveAsImage: { show: true },
                },
            },
            tooltip: {
                trigger: 'axis',
                axisPointer: {
                    type: 'shadow'
                },
                formatter: function(params) {
                    var res = params[0].axisValue + '<br/>' + params[0].name + '<br/>' + params[0].marker + params[0].seriesName + '：' + params[0].value;
                    return res;
                }
            },
            legend: {
                top: '80'
            },
            grid: {
                top: '120',
                left: '20',
                right: '20',
                bottom: '20',
                containLabel: true
            },
            xAxis: {
                type: 'value',
                boundaryGap: [0, 0.01]
            },
            yAxis: [{
                type: 'category',
                axisLine: {
                    onZero: false
                },
                position: 'left',
                data: ['TOP 20', 'TOP 19', 'TOP 18', 'TOP 17', 'TOP 16', 'TOP 15', 'TOP 14', 'TOP 13', 'TOP 12', 'TOP 11', 'TOP 10', 'TOP 9', 'TOP 8', 'TOP 7', 'TOP 6', 'TOP 5', 'TOP 4', 'TOP 3', 'TOP 2', 'TOP 1']
            }, {
                type: 'category',
                axisLine: {
                    onZero: false
                },
                position: 'right',
                data: []
            }],
            series: [
                {
                    name: '销量（元）',
                    type: 'bar',
                    label: {
                        show: true,
                        position: 'insideLeft'
                    },
                    data: []
                }
            ]
        };
        Public.ajaxPost('/Bill/Report/salesRank', queryConditions, function(res) {
            res.status == -1 && $.dialog.tips(res.msg, 2);
            THISPAGE.yAxisData = [];
            THISPAGE.seriesData = [];
            $.each(res.data.reverse(), function(k, v) {
                THISPAGE.yAxisData.push(v['a']);
                THISPAGE.seriesData.push({value: queryConditions.type == 2 ? parseInt(v['b']) : Number(v['b']).toFixed(2), name: v['a']});
            });
            THISPAGE.chartConfig.yAxis[1].data = THISPAGE.yAxisData;
            THISPAGE.chartConfig.series[0].data = THISPAGE.seriesData;
            THISPAGE.chart = echarts.init(document.getElementById('chart'));
            THISPAGE.chart.setOption(THISPAGE.chartConfig);
        });
    },
    initEvent: function() {
        laydate.render({
            elem: "#datePicker",
            range: ['#beginDate', '#endDate'],
            done: function() {
                setTimeout(function() {
                    $('#search').trigger('click');
                }, 300);
            }
        });
        $('#search').on('click', function() {
            queryConditions.categoryId = categoryTree.getValue();
            queryConditions.type = statusCombo1.getValue();
            queryConditions.datetype = statusCombo2.getValue();
            switch (queryConditions.datetype) {
                case 1:
                    break;
                case 2:
                    $('#beginDate').val((new Date).format('yyyy-MM') + '-01');
                    $('#endDate').val((new Date).format('yyyy-MM-dd'));
                    break;
                case 3:
                    $('#beginDate').val(Public.reduceMonths((new Date).format('yyyy-MM') + '-01', 1));
                    $('#endDate').val(Public.reduceDays((new Date).format('yyyy-MM') + '-01', 1));
                    break;
                case 4:
                    $('#beginDate').val(Public.reduceMonths((new Date).format('yyyy-MM-01'), 11));
                    $('#endDate').val((new Date).format('yyyy-MM-dd'));
                    break;
                default:
                    break;
            }
            queryConditions.beginDate = $('#beginDate').val();
            queryConditions.endDate = $('#endDate').val();
            queryConditions.limit = 20;
            queryConditions.export = 0;
            $('.wrapper').append('<div id="div-lock"><div class="loading">数据分析中，请稍候...</div></div>');
            Public.ajaxPost('/Bill/Report/salesRank', queryConditions, function(res) {
                $('.wrapper > #div-lock').remove();
                res.status == -1 && $.dialog.tips(res.msg, 2);
                THISPAGE.yAxisData = [];
                THISPAGE.seriesData = [];
                $.each(res.data.reverse(), function(k, v) {
                    THISPAGE.yAxisData.push(v['a']);
                    THISPAGE.seriesData.push({value: queryConditions.type == 2 ? parseInt(v['b']) : Number(v['b']).toFixed(2), name: v['a']});
                });
                THISPAGE.chartConfig.yAxis[1].data = THISPAGE.yAxisData;
                if (queryConditions.type == 2) {
                    THISPAGE.chartConfig.series[0].name = '销量（件）';
                } else {
                    THISPAGE.chartConfig.series[0].name = '销量（元）';
                }
                THISPAGE.chartConfig.series[0].data = THISPAGE.seriesData;
                THISPAGE.chart.dispose();
                THISPAGE.chart = echarts.init(document.getElementById('chart'));
                THISPAGE.chart.setOption(THISPAGE.chartConfig);
            });
        });
        $('#refresh').on('click', function() {
            $('#search').trigger('click');
        });
        $('#export100').on('click', function() {
            if (Business.verifyRight('SYS_ADVANCE')) {
                queryConditions.categoryId = categoryTree.getValue();
                queryConditions.type = statusCombo1.getValue();
                queryConditions.datetype = statusCombo2.getValue();
                queryConditions.beginDate = $('#beginDate').val();
                queryConditions.endDate = $('#endDate').val();
                queryConditions.limit = 100;
                queryConditions.export = 1;
                var para = '';
                for (var f in queryConditions) {
                    queryConditions[f] && (para += '&' + f + '=' + queryConditions[f]);
                }
                var href = '/Bill/Report/salesRank?export=1&limit=100' + para;
                $(this).attr('href', href);
            }
        });
        $('#export200').on('click', function() {
            if (Business.verifyRight('SYS_ADVANCE')) {
                queryConditions.categoryId = categoryTree.getValue();
                queryConditions.type = statusCombo1.getValue();
                queryConditions.datetype = statusCombo2.getValue();
                queryConditions.beginDate = $('#beginDate').val();
                queryConditions.endDate = $('#endDate').val();
                queryConditions.limit = 200;
                queryConditions.export = 1;
                var para = '';
                for (var f in queryConditions) {
                    queryConditions[f] && (para += '&' + f + '=' + queryConditions[f]);
                }
                var href = '/Bill/Report/salesRank?export=1&limit=200' + para;
                $(this).attr('href', href);
            }
        });
        $('#export500').on('click', function() {
            if (Business.verifyRight('SYS_ADVANCE')) {
                queryConditions.categoryId = categoryTree.getValue();
                queryConditions.type = statusCombo1.getValue();
                queryConditions.datetype = statusCombo2.getValue();
                queryConditions.beginDate = $('#beginDate').val();
                queryConditions.endDate = $('#endDate').val();
                queryConditions.limit = 500;
                queryConditions.export = 1;
                var para = '';
                for (var f in queryConditions) {
                    queryConditions[f] && (para += '&' + f + '=' + queryConditions[f]);
                }
                var href = '/Bill/Report/salesRank?export=1&limit=500' + para;
                $(this).attr('href', href);
            }
        });
        $('#export1000').on('click', function() {
            if (Business.verifyRight('SYS_ADVANCE')) {
                queryConditions.categoryId = categoryTree.getValue();
                queryConditions.type = statusCombo1.getValue();
                queryConditions.datetype = statusCombo2.getValue();
                queryConditions.beginDate = $('#beginDate').val();
                queryConditions.endDate = $('#endDate').val();
                queryConditions.limit = 1000;
                queryConditions.export = 1;
                var para = '';
                for (var f in queryConditions) {
                    queryConditions[f] && (para += '&' + f + '=' + queryConditions[f]);
                }
                var href = '/Bill/Report/salesRank?export=1&limit=1000' + para;
                $(this).attr('href', href);
            }
        });
        window.addEventListener('resize', function () {
            THISPAGE.chart.resize();
        });
    }
};
$(function() {
    THISPAGE.init();
});
