<?= view('header') ?>
<link rel="stylesheet" type="text/css" href="/statics/js/plugins/fSelect/fSelect.css?v=2026102701" />
</head>

<body>
    <div class="wrapper">
        <div class="mod-search cf bg-white">
            <div class="fl">
                <ul class="ul-inline">
                    <li>
                        <div class="ctn-wrap">
                            <select id="deliverCompanyCombo" class="hidden" multiple="multiple">
                                <optgroup id="deliverCompany" label="选择快递公司筛选数据"></optgroup>
                            </select>
                        </div>
                    </li>
                    <li id="datePicker" class="ml1">
                        <span class="db fl mr-2 h28 lh28 pl5 pr5 gray bg-white bde b-r-3-left">起止日期</span>
                        <input type="text" id="beginDate" value="" class="fl mr-2 ui-input ui-datepicker-input" autocomplete="off" readonly />
                        <input type="text" id="endDate" value="" class="fl ui-input ui-datepicker-input" autocomplete="off" readonly />
                    </li>
                </ul>
            </div>
            <div class="fl ml1 ui-btn-group">
                <a id="search" class="ui-btn"><i class="iconfont icon-chaxun"></i><span>查询</span></a>
                <a id="curMonth" class="ui-btn current"><span>本月</span></a>
                <a id="lastMonth" class="ui-btn"><span>上月</span></a>
                <a id="curYear" class="ui-btn"><span>今年</span></a>
                <a id="lastYear" class="ui-btn"><span>去年</span></a>
                <a id="searchAll" class="ui-btn"><span>全部</span></a>
            </div>
            <div class="fr ui-btn-group">
                <a id="refresh" class="ui-btn"><i class="iconfont icon-shuaxin"></i><span>刷新</span></a>
            </div>
        </div>
        <div class="diy-con bg-rgba07">
            <div class="fl ml10" style="width:820px">
                <div class="mt10 ti20 lh40 bg-white bbe b-r-3-top">按快递公司</div>
                <div id="companyData" class="mb10 pt20 pl20 ovh bg-white b-r-3-bottom"></div>
                <div class="mt10 ti20 lh40 bg-white bbe b-r-3-top">按渠道类型</div>
                <div id="chTypeData" class="mb10 pt20 pl20 ovh bg-white b-r-3-bottom"></div>
                <div class="mt10 ti20 lh40 bg-white bbe b-r-3-top">按重量区间</div>
                <div id="weightData" class="mb10 pt20 pl20 ovh bg-white b-r-3-bottom"></div>
                <div class="mt10 ti20 lh40 bg-white bbe b-r-3-top">按打包人</div>
                <div id="packerData" class="mb10 pt20 pl20 ovh bg-white b-r-3-bottom"></div>
            </div>
            <div class="fr mr10" style="width:calc(100% - 850px)">
                <div class="mt10 ti20 lh40 bg-white bbe b-r-3-top">按目的地</div>
                <div id="addressData" class="mb10 pt20 pl20 ovh bg-white b-r-3-bottom"></div>
            </div>
        </div>
    </div>
    <script type="text/javascript" src="/statics/js/plugins/fSelect/fSelect.js?v=2026102701"></script>
    <script type="text/javascript" src="/statics/js/dist/deliver-analyse.js?v=2026102701"></script>
</body>

</html>
