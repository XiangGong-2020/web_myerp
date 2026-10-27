<?= view('header') ?>
</head>

<body>
    <div class="manage-wrapper">
        <div class="mod-search cf">
            <div class="fl">
                <ul class="ul-inline">
                    <li><span id="combo1"></span></li>
                    <li class="ml1">
                        <input type="text" id="skey" class="fl ui-input" placeholder="输入任意关键词" />
                    </li>
                    <div class="fl ml1 ui-btn-group">
                        <a id="search" class="ui-btn"><i class="iconfont icon-chaxun"></i><span>查询</span></a>
                    </div>
                </ul>
            </div>
            <div id="totalInfo" class="fl ml10 h28 lh28 dark ovh es" style="width:calc(100vw - 450px)"></div>
            <div class="fr ui-btn-group">
                <a id="refresh" class="ui-btn"><i class="iconfont icon-shuaxin"></i><span>刷新</span></a>
            </div>
        </div>
        <div id="manage-wrap">
            <div class="grid-wrap bbe">
                <table id="grid"></table>
                <div id="page"></div>
            </div>
        </div>
    </div>
    <script type="text/javascript" src="/statics/js/dist/purchaseContractReq-quote-info.js?v=2026102701"></script>
</body>

</html>
