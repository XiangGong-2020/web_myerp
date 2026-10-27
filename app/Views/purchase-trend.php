<?= view('header') ?>
<link rel="stylesheet" type="text/css" href="/statics/css/bills.css?v=2026102701" />
<style>
    .tab-wrapper-chart {
        float: left;
        width: calc(100% - 20px);
        height: calc(100vh - 20px);
        margin: 10px;
        background: #fff;
        border-radius: 3px;
        box-shadow: 0 0 0 1px #00000011;
        overflow: hidden;
    }

    .tab-wrapper-chart .tab-content {
        float: left;
        width: calc(100% - 40px);
        height: calc(100% - 90px);
        margin: 70px 20px 20px;
        padding: 0;
        border: 0;
    }

    .tab-wrapper-chart .col-tab {
        position: relative;
        margin: auto;
        width: 100%;
        height: 100%;
    }

    .tab-wrapper-chart .col-tab .col-search {
        position: absolute;
        top: -50px;
        left: 0;
        height: 30px;
        padding: 0;
        color: #333;
        background: #fff;
    }

    .tab-wrapper-chart .col-tab .col-search .col-search-tab {
        position: relative;
        float: left;
        margin-right: -1px;
        padding: 0 10px;
        height: 28px;
        line-height: 28px;
        border: 1px solid #E1E2E3;
        cursor: pointer;
    }

    .tab-wrapper-chart .col-tab .col-search .col-search-tab.active {
        z-index: 2;
        color: #2c65ff;
        background-color: #e6f7ff;
        border-color: #2c65ff;
    }

    .tab-wrapper-chart .col-tab .col-search .col-search-unit {
        float: left;
        padding: 0 10px;
        margin-left: 10px;
        height: 28px;
        line-height: 28px;
        border: 1px solid #E1E2E3;
    }

    .tab-wrapper-chart .col-tab .notice {
        position: absolute;
        right: 0;
        top: -45px;
    }
    .tab-wrapper-chart .chart-area {
        width: 100%;
        height: 100%;
    }
</style>
</head>

<body>
    <div class="wrapper bg-rgba1">
        <div class="tab-wrapper-chart">
            <div class="tab-content">
                <div class="col-tab rel">
                    <div class="col-search">
                        <div id="near30days" class="col-search-tab b-r-3-left active">近 30 天</div>
                        <div id="lastmonth" class="col-search-tab">上月</div>
                        <div id="near12months" class="col-search-tab">近 12 月</div>
                        <div id="all" class="col-search-tab b-r-3-right">全部</div>
                        <div id="datePicker" class="fl ml10 mr-2 bg-white">
                            <span class="db fl mr-2 h28 lh28 pl5 pr5 gray bg-white bde b-r-3-left">自定义时段</span>
                            <input type="text" id="beginDate" value="" class="fl mr-2 ui-input ui-datepicker-input" autocomplete="off" readonly />
                            <input type="text" id="endDate" value="" class="fl ui-input ui-datepicker-input" autocomplete="off" readonly />
                        </div>
                        <div id="custom" class="col-search-tab col-search-tab-1-2 bg-white b-r-3">查询</div>
                        <div class="col-search-unit b-r-3">单位：元</div>
                    </div>
                    <i class="notice iconfont icon-bangzhu gray" title="1、统计时间基准: 采购单据的单据日期。&#10;2、统计对象基准: 采购单据的采购金额。"></i>
                    <div id="chart" class="chart-area"></div>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript" src="/statics/js/plugins/echarts/echarts.min.js?v=2026102701"></script>
    <script type="text/javascript" src="/statics/js/dist/purchase-trend.js?v=2026102701"></script>
</body>

</html>
