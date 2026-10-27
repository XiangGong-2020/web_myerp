<?= view('header') ?>
</head>

<body>
    <div class="wrapper">
        <div class="mod-search cf">
            <div class="fl">
                <ul class="ul-inline">
                    <li id="datePicker">
                        <span class="db fl mr-2 h28 lh28 pl5 pr5 gray bg-white bde b-r-3-left">单据日期</span>
                        <input type="text" id="beginDate" value="" class="fl mr-2 ui-input ui-datepicker-input" autocomplete="off" readonly />
                        <input type="text" id="endDate" value="" class="fl ui-input ui-datepicker-input" autocomplete="off" readonly />
                    </li>
                    <li class="ml1">
                        <input type="number" class="fl mr-2 ui-input w90" id="minAmount" value="" placeholder="最小金额(元)" />
                        <input type="number" class="fl ui-input w90" id="maxAmount" value="" placeholder="最大金额(元)" />
                    </li>
                    <li class="ml1">
                        <input type="text" id="skey" class="fl ui-input" placeholder="输入任意关键词" />
                    </li>
                </ul>
            </div>
            <div class="fl ml1 ui-btn-group">
                <a id="search" class="ui-btn current"><i class="iconfont icon-chaxun"></i><span>查询</span></a>
                <a id="searchUnBack261" class="ui-btn"><span>未退回保证金</span></a>
            </div>
            <div class="fr ui-btn-group">
                <a id="refresh" class="ui-btn"><i class="iconfont icon-shuaxin"></i><span>刷新</span></a>
                <a id="add" class="ui-btn"><i class="iconfont icon-zengjia"></i><span>新增</span></a>
            </div>
        </div>
        <div class="grid-wrap">
            <table id="grid"></table>
            <div id="page"></div>
        </div>
    </div>
    <script type="text/javascript" src="/statics/js/dist/expend-list.js?v=2026102701"></script>
</body>

</html>
