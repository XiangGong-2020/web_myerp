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
                    <li class="ml1"><span id="combo1"></span></li>
                    <li class="ml1"><span id="combo2"></span></li>
                    <li class="ml1">
                        <input type="text" id="skey" class="fl ui-input" placeholder="输入任意关键词" />
                    </li>
                </ul>
            </div>
            <div class="fl ml1 ui-btn-group">
                <a id="search" class="ui-btn"><i class="iconfont icon-chaxun"></i><span>查询</span></a>
            </div>
            <div class="fr ui-btn-group">
                <a id="refresh" class="ui-btn"><i class="iconfont icon-shuaxin"></i><span>刷新</span></a>
            </div>
        </div>
        <div class="grid-wrap">
            <table id="grid"></table>
            <div id="page"></div>
        </div>
    </div>
    <script type="text/javascript" src="/statics/js/dist/sales-list.js?v=2026102701"></script>
</body>

</html>
