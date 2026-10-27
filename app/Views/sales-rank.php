<?= view('header') ?>
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
        right: 0;
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
                        <div class="fl"><span id="category"></span></div>
                        <div class="fl ml1"><span id="combo1"></span></div>
                        <div class="fl ml1"><span id="combo2"></span></div>
                        <div id="datePicker" class="fl ml1">
                            <span class="db fl mr-2 h28 lh28 pl5 pr5 gray bg-white bde b-r-3-left">自定义时段</span>
                            <input type="text" id="beginDate" value="" class="fl mr-2 ui-input ui-datepicker-input" autocomplete="off" readonly />
                            <input type="text" id="endDate" value="" class="fl ui-input ui-datepicker-input" autocomplete="off" readonly />
                        </div>
                        <div class="fl ml1 ui-btn-group">
                            <a id="search" class="ui-btn"><i class="iconfont icon-chaxun"></i><span>查询</span></a>
                        </div>
                        <div class="fr ui-btn-group">
                            <span class="db fl mr2 h28 lh28 pl5 pr5 blue bg-white bd-blue b-r-3">导出排行前</span>
                            <a id="export100" class="ui-btn "></i><span>100</span></a>
                            <a id="export200" class="ui-btn"><span>200</span></a>
                            <a id="export500" class="ui-btn"><span>500</span></a>
                            <a id="export1000" class="ui-btn"><span>1000</span></a>
                        </div>
                    </div>
                    <div id="chart" class="chart-area"></div>
                </div>
            </div>
        </div>
    </div>
<<<<<<< HEAD
    <script type="text/javascript" src="/statics/js/plugins/echarts/echarts.min.js?v=2026102701"></script>
    <script type="text/javascript" src="/statics/js/dist/sales-rank.js?v=2026102701"></script>
=======
    <script type="text/javascript" src="/statics/js/plugins/echarts/echarts.min.js?v=2026101403"></script>
    <script type="text/javascript" src="/statics/js/dist/sales-rank.js?v=2026101403"></script>
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
</body>

</html>
