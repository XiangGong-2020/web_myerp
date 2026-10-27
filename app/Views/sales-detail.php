<?= view('header') ?>
</head>

<body>
    <div class="wrapper bg-silver">
        <div class="mod-search cf">
            <div class="fl">
                <ul class="ul-inline">
                    <li>
                        <span class="db fl mr-2 h28 lh28 pl5 pr5 gray bg-white bde b-r-3-left">客户</span>
                        <span class="rel fl db mt-1" id="filter-customer">
                            <input type="text" class="ui-input" id="customerAuto" />
                            <span class="ui-icon-ellipsis iconfont icon-shenglvehao"></span>
                        </span>
                    </li>
                    <li class="ml1">
                        <span class="db fl mr-2 h28 lh28 pl5 pr5 gray bg-white bde b-r-3-left">产品</span>
                        <span class="rel fl db mt-1" id="filter-goods">
                            <input type="text" class="ui-input" id="goodsAuto" />
                            <span class="ui-icon-ellipsis iconfont icon-shenglvehao"></span>
                        </span>
                    </li>
                    <li class="ml1">
                        <span class="db fl mr-2 h28 lh28 pl5 pr5 gray bg-white bde b-r-3-left">仓库</span>
                        <span class="rel fl db mt-1" id="filter-storage">
                            <input type="text" class="ui-input" id="storageAuto" />
                            <span class="ui-icon-ellipsis iconfont icon-shenglvehao"></span>
                        </span>
                    </li>
                    <li id="datePicker" class="ml1">
                        <span class="db fl mr-2 h28 lh28 pl5 pr5 gray bg-white bde b-r-3-left">销售时间</span>
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
                <a id="reset" class="ui-btn"><i class="iconfont icon-chongzhi"></i><span>重置</span></a>
                <a id="refresh" class="ui-btn"><i class="iconfont icon-shuaxin"></i><span>刷新</span></a>
            </div>
        </div>
        <div class="top-notice">
            <div class="fl" title="当前查询条件下销售订单销售汇总">
                <div class="fl pl10 pr10 h28 lh28 def dark bd-gray b-r-3-left">销售订单</div>
                <div id="totalOrder" class="fl pl10 pr10 h28 lh28 dark bd-gray bln b-r-3-right">待查询</div>
            </div>
            <div class="fl ml10" title="当前查询条件下特殊销售单销售汇总">
                <div class="fl pl10 pr10 h28 lh28 def dark bd-gray b-r-3-left">特殊销售</div>
                <div id="totalContract" class="fl pl10 pr10 h28 lh28 dark bd-gray bln b-r-3-right">待查询</div>
            </div>
            <div class="fl ml10" title="当前查询条件下总体汇总">
                <div class="fl pl10 pr10 h28 lh28 def dark bd-gray b-r-3-left">总计</div>
                <div id="totalAll" class="fl pl10 pr10 h28 lh28 dark bd-gray bln b-r-3-right">待查询</div>
            </div>
        </div>
        <div class="grid-wrap mt5 bte bbe">
            <table id="grid1"></table>
            <div id="page1"></div>
        </div>
        <div class="grid-wrap mt5 bte">
            <table id="grid2"></table>
            <div id="page2"></div>
        </div>
    </div>
<<<<<<< HEAD
    <script type="text/javascript" src="/statics/js/dist/sales-detail.js?v=2026102701"></script>
=======
    <script type="text/javascript" src="/statics/js/dist/sales-detail.js?v=2026101403"></script>
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
</body>

</html>
