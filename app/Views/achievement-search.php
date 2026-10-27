<?= view('header') ?>
<style>
    .ach-item {
        padding: 10px;
        background-color: #fff;
        box-shadow: 0 0 0 1px #00000015;
    }

    .ach-tit {
        height: 40px;
        padding: 0 10px;
        font-size: 14px;
        font-weight: 700;
        line-height: 40px;
        overflow: hidden;
    }

    .ach-con {
        padding: 0 10px 10px;
        line-height: 30px;
        overflow: hidden;
    }
</style>
</head>

<body>
    <div class="wrapper">
        <div class="mod-search cf">
            <div class="fl">
                <div id="name" class="db fl ml5 w80 f16 fb"></div>
            </div>
            <div class="fr">
                <ul class="fl ul-inline">
                    <li id="datePicker">
                        <span class="db fl mr-2 h28 lh28 pl5 pr5 gray bg-white bde b-r-3-left">起止日期</span>
                        <input type="text" id="beginDate" value="" class="fl mr-2 ui-input ui-datepicker-input" autocomplete="off" readonly />
                        <input type="text" id="endDate" value="" class="fl ui-input ui-datepicker-input" autocomplete="off" readonly />
                    </li>
                </ul>
                <div class="fl ml1 ui-btn-group">
                    <a id="search" class="ui-btn"><i class="iconfont icon-chaxun"></i><span>查询</span></a>
                    <a id="curMonth" class="ui-btn"><i class="iconfont icon-chaxun"></i><span>本月</span></a>
                    <a id="lastMonth" class="ui-btn"><i class="iconfont icon-chaxun"></i><span>上月</span></a>
                </div>
            </div>
        </div>
        <div class="diy-con">
            <div class="no-data"></div>
            <div class="ach-item dn">
                <div class="ach-tit">考勤信息</div>
                <div id="info-attendance" class="ach-con"></div>
            </div>
            <div class="ach-item dn">
                <div class="ach-tit">考评得分</div>
                <div id="info-evaluate" class="ach-con"></div>
            </div>
            <div class="ach-item dn">
                <div class="ach-tit">采购业绩</div>
                <div id="performance-pur" class="ach-con"></div>
            </div>
            <div class="ach-item dn">
                <div class="ach-tit">销售业绩</div>
                <div id="performance-sale" class="ach-con"></div>
            </div>
            <div class="ach-item dn">
                <div class="ach-tit">配货业绩</div>
                <div id="performance-produce" class="ach-con"></div>
            </div>
            <div class="ach-item dn">
                <div class="ach-tit">打包业绩</div>
                <div id="performance-pack" class="ach-con"></div>
            </div>
            <div class="ach-item dn">
                <div class="ach-tit">开发业绩</div>
                <div id="info-task" class="ach-con"></div>
            </div>
            <div class="ach-item dn">
                <div class="ach-tit">反馈责任</div>
                <div id="service-target" class="ach-con"></div>
            </div>
            <div class="ach-item dn">
                <div class="ach-tit">工作目标</div>
                <div id="info-plan" class="ach-con"></div>
            </div>
        </div>
    </div>
    </div>
    <script type="text/javascript" src="/statics/js/dist/achievement-search.js?v=2026102701"></script>
</body>

</html>
