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
                    <li class="ml1"><span id="combo4"></span></li>
                    <li class="ml1"><span id="combo5"></span></li>
                    <li class="ml1"><span id="combo6"></span></li>
                    <li class="ml1"><span id="combo7"></span></li>
                    <li class="ml1"><span id="combo8"></span></li>
                    <li id="datePicker" class="ml1">
                        <span class="db fl mr-2 h28 lh28 pl5 pr5 gray bg-white bde b-r-3-left">单据日期</span>
                        <input type="text" id="beginDate" value="" class="fl mr-2 ui-input ui-datepicker-input" autocomplete="off" readonly />
                        <input type="text" id="endDate" value="" class="fl ui-input ui-datepicker-input" autocomplete="off" readonly />
                    </li>
                    <li class="ml1">
                        <input type="number" class="fl mr-2 ui-input w70" id="minAmount" value="" placeholder="最小金额(元)" />
                        <input type="number" class="fl ui-input w70" id="maxAmount" value="" placeholder="最大金额(元)" />
                    </li>
                    <li class="ml1">
                        <input type="text" id="skey" class="fl ui-input" placeholder="输入任意关键词" />
                    </li>
                    <li class="ml1"><span id="combo9"></span></li>
                </ul>
            </div>
            <div class="fl ml1 ui-btn-group">
                <a id="search" class="ui-btn"><i class="iconfont icon-chaxun"></i><span>查询</span></a>
                <a id="search2" class="ui-btn"><span>发票缺口</span></a>
            </div>
            <div class="fr ui-btn-group">
                <a id="refresh" class="ui-btn"><i class="iconfont icon-shuaxin"></i><span>刷新</span></a>
                <a id="add" class="ui-btn"><i class="iconfont icon-zengjia"></i><span>新增</span></a>
            </div>
        </div>
        <div id="totalAmountInfo" class="top-notice">
            <div class="fl h28 lh28 bd-red b-r-3">
                <div class="fl pl10 pr10 white bg-red bdn def" title="当前查询条件下未付款金额">未付款金额</div>
                <div id="totalNotClearedAmount" class="fl pl10 pr10 red" title="当前查询条件下未付款金额">待查询</div>
                <div class="fl pr10 red" title="当前查询条件下未付款金额">元</div>
            </div>
        </div>
        <div class="grid-wrap">
            <table id="grid"></table>
            <div id="page"></div>
        </div>
    </div>
    <script type="text/javascript" src="/statics/js/dist/purchaseOrder-list.js?v=2026102701"></script>
</body>

</html>
