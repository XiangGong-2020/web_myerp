<?= view('header') ?>
</head>

<body>
    <div class="wrapper">
        <div class="mod-search cf">
            <div class="fl">
                <ul class="ul-inline">
                    <li><span id="combo1"></span></li>
                    <li class="ml1"><span id="combo2"></span></li>
                    <li class="ml1"><span id="combo3"></span></li>
                    <li id="datePicker" class="ml1">
                        <span class="db fl mr-2 h28 lh28 pl5 pr5 gray bg-white bde b-r-3-left">录入时间</span>
                        <input type="text" id="beginDate" value="" class="fl mr-2 ui-input ui-datepicker-input" autocomplete="off" readonly />
                        <input type="text" id="endDate" value="" class="fl ui-input ui-datepicker-input" autocomplete="off" readonly />
                    </li>
                    <li class="ml1">
                        <input type="text" id="skey" class="fl ui-input" placeholder="输入任意关键词" />
                    </li>
                </ul>
            </div>
            <div class="fl ml1 ui-btn-group">
                <a id="search" class="ui-btn"><i class="iconfont icon-chaxun"></i><span>查询</span></a>
                <a id="curMonth" class="ui-btn"><span>本月</span></a>
                <a id="lastMonth" class="ui-btn"><span>上月</span></a>
                <a id="curYear" class="ui-btn"><span>今年</span></a>
                <a id="lastYear" class="ui-btn"><span>去年</span></a>
                <a id="searchAll" class="ui-btn current"><span>全部</span></a>
            </div>
            <div class="fr ui-btn-group">
                <a id="refresh" class="ui-btn"><i class="iconfont icon-shuaxin"></i><span>刷新</span></a>
                <a id="add" class="ui-btn dn"><i class="iconfont icon-zengjia"></i><span>手动新增</span></a>
                <a id="toBidQuote" class="ui-btn dn"><i class="iconfont icon-zengjia"></i><span>从预算报价一键转化</span></a>
            </div>
        </div>
        <div class="grid-wrap">
            <table id="grid"></table>
            <div id="page"></div>
        </div>
    </div>
<<<<<<< HEAD
    <script type="text/javascript" src="/statics/js/plugins/clipboard/clipboard.min.js?v=2026102701"></script>
    <script type="text/javascript" src="/statics/js/dist/bid-program-list.js?v=2026102701"></script>
=======
    <script type="text/javascript" src="/statics/js/plugins/clipboard/clipboard.min.js?v=2026101403"></script>
    <script type="text/javascript" src="/statics/js/dist/bid-program-list.js?v=2026101403"></script>
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
</body>

</html>
