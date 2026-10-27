<?= view('header') ?>
</head>

<body>
    <div class="wrapper">
        <div class="mod-search cf">
            <div class="fl">
                <ul class="ul-inline">
                    <li><span id="category"></span></li>
                    <li class="ml1"><span id="storage"></span></li>
                    <li class="ml1">
                        <input type="text" id="skey" class="fl ui-input" placeholder="输入任意关键词" />
                    </li>
                </ul>
            </div>
            <div class="fl ml1 ui-btn-group">
                <a id="search" class="ui-btn"><i class="iconfont icon-chaxun"></i><span>查询</span></a>
            </div>
            <div class="fl ml10">
                <div class="fl black bd-black bg-white b-r-3" title="当前查询条件下当前库存总量">
                    <span class="fl pl10 h28 lh28 def">当前总库存</span>
                    <span class="fl pl10 pr10 h28 f16 fb lh28" id="currentQty_total"></span>
                </div>
            </div>
            <div class="fr ui-byn-group">
                <a id="refresh" class="ui-btn"><i class="iconfont icon-shuaxin"></i><span>刷新</span></a>
            </div>
        </div>
        <div class="grid-wrap">
            <table id="grid"></table>
            <div id="page"></div>
        </div>
    </div>
    <script type="text/javascript" src="/statics/js/dist/inventory-search.js?v=2026102701"></script>
</body>

</html>
