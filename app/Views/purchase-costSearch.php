<?= view('header') ?>
</head>

<body>
    <div class="wrapper">
        <div class="mod-search cf">
            <div class="fl">
                <ul class="ul-inline">
                    <li class="mt-1">
                        <span class="db fl mr-2 h28 lh28 pl5 pr5 gray bg-white bde b-r-3-left">选择产品</span>
                        <span class="rel fl db mt-1" id="filter-goods">
                            <input type="text" id="goodsAuto" class="ui-input w200" />
                            <span class="ui-icon-ellipsis iconfont icon-shenglvehao"></span>
                        </span>
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
        <div class="top-notice">
            <div class="fl" title="当前查询条件下采购成本">
                <div class="fl pl10 pr10 h28 lh28 def dark bd-gray b-r-3-left">历史最低采购成本</div>
                <div id="minCost" class="fl pl10 pr10 h28 lh28 dark bd-gray bln b-r-3-right">待查询</div>
                <div class="fl ml10 pl10 pr10 h28 lh28 def dark bd-gray b-r-3-left">历史最高采购成本</div>
                <div id="maxCost" class="fl pl10 pr10 h28 lh28 dark bd-gray bln b-r-3-right">待查询</div>
                <div class="fl ml10 pl10 pr10 h28 lh28 def dark bd-gray b-r-3-left">历史平均采购成本</div>
                <div id="averageCost" class="fl pl10 pr10 h28 lh28 dark bd-gray bln b-r-3-right">待查询</div>
            </div>
        </div>
        <div class="grid-wrap">
            <table id="grid"></table>
            <div id="page"></div>
        </div>
    </div>
    <script type="text/javascript" src="/statics/js/dist/purchase-costSearch.js?v=2026102701"></script>
</body>

</html>
