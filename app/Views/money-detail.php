<?= view('header') ?>
<<<<<<< HEAD
<link rel="stylesheet" type="text/css" href="/statics/js/plugins/fSelect/fSelect.css?v=2026102701" />
=======
<link rel="stylesheet" type="text/css" href="/statics/js/plugins/fSelect/fSelect.css?v=2026101403" />
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
</head>

<body>
    <div class="wrapper">
        <div class="mod-search cf">
            <div class="fl">
                <ul class="ul-inline">
                    <li><span id="combo1"></span></li>
                    <li class="ml1">
                        <div class="ctn-wrap">
                            <select id="combo2" class="hidden" multiple="multiple">
                                <optgroup id="incometype" label="收入类别"></optgroup>
                                <optgroup id="expendtype" label="支出类别"></optgroup>
                            </select>
                        </div>
                    </li>
                    <li class="ml1"><span id="combo3"></span></li>
                    <li class="ml1"><span id="combo4"></span></li>
                    <li class="ml1"><span id="combo5"></span></li>
                    <li class="ml1">
                        <input type="number" class="fl mr-2 ui-input w70" id="minAmount" value="" placeholder="最小金额(元)" />
                        <input type="number" class="fl ui-input w70" id="maxAmount" value="" placeholder="最大金额(元)" />
                    </li>
                    <li id="datePicker" class="ml1">
                        <span class="db fl mr-2 h28 lh28 pl5 pr5 gray bg-white bde b-r-3-left">收支时间</span>
                        <input type="text" id="beginDate" value="" class="fl mr-2 ui-input ui-datetimepicker-input" autocomplete="off" readonly />
                        <input type="text" id="endDate" value="" class="fl ui-input ui-datetimepicker-input" autocomplete="off" readonly />
                    </li>
                    <li class="ml1">
                        <input type="text" id="skey" class="fl ui-input" style="width: 220px;" placeholder="输入任意(单号|收支对象名|备注)关键词" />
                    </li>
                </ul>
            </div>
            <div class="fl ml1 ui-btn-group">
                <a id="search" class="ui-btn"><i class="iconfont icon-chaxun"></i><span>查询</span></a>
                <a id="today" class="ui-btn current"><span>今天</span></a>
                <a id="yesterday" class="ui-btn"><span>昨天</span></a>
                <a id="curMonth" class="ui-btn"><span>本月</span></a>
                <a id="lastMonth" class="ui-btn"><span>上月</span></a>
                <a id="curYear" class="ui-btn"><span>今年</span></a>
                <a id="lastYear" class="ui-btn"><span>去年</span></a>
                <a id="searchAll" class="ui-btn"><span>全部</span></a>
            </div>
            <div class="fr ui-btn-group">
                <a id="refresh" class="ui-btn"><i class="iconfont icon-shuaxin"></i><span>刷新</span></a>
            </div>
        </div>
        <div class="top-notice">
            <div class="fl">
                <div class="fl pl10 pr10 h28 lh28 def dark bd-gray b-r-3-left" title="当前查询条件下收入总额">收入总额</div>
                <div id="totalInAmount" class="fl pl10 pr10 h28 lh28 dark bd-gray bln brn" title="当前查询条件下收入总额">待查询</div>
                <div class="fl pr10 h28 lh28 gray bd-gray bln b-r-3-right" title="当前查询条件下收入总额">元</div>
            </div>
            <div class="fl pl10 pr10 gray">–</div>
            <div class="fl">
                <div class="fl pl10 pr10 h28 lh28 def dark bd-gray b-r-3-left" title="当前查询条件下支出总额">支出总额</div>
                <div id="totalOutAmount" class="fl pl10 pr10 h28 lh28 dark bd-gray bln brn" title="当前查询条件下支出总额">待查询</div>
                <div class="fl pr10 h28 lh28 gray bd-gray bln b-r-3-right" title="当前查询条件下支出总额">元</div>
            </div>
            <div class="fl pl10 pr10 gray">=</div>
            <div class="fl">
                <div class="fl pl10 pr10 h28 lh28 def dark bd-gray b-r-3-left" title="当前查询条件下收支盈余总额">收支盈余</div>
                <div id="totalReAmount" class="fl pl10 pr10 h28 lh28 dark bd-gray bln brn" title="当前查询条件下收支盈余总额">待查询</div>
                <div class="fl pr10 h28 lh28 gray bd-gray bln b-r-3-right" title="当前查询条件下收支盈余总额">元</div>
            </div>
        </div>
        <div class="grid-wrap">
            <table id="grid"></table>
            <div id="page"></div>
        </div>
    </div>
<<<<<<< HEAD
    <script type="text/javascript" src="/statics/js/plugins/fSelect/fSelect.js?v=2026102701"></script>
    <script type="text/javascript" src="/statics/js/dist/money-detail.js?v=2026102701"></script>
=======
    <script type="text/javascript" src="/statics/js/plugins/fSelect/fSelect.js?v=2026101403"></script>
    <script type="text/javascript" src="/statics/js/dist/money-detail.js?v=2026101403"></script>
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
</body>

</html>
