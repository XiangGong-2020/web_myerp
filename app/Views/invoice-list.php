<?= view('header') ?>
</head>

<body>
    <div class="wrapper">
        <div class="mod-search cf">
            <div class="fl">
                <ul class="ul-inline">
                    <li>
                        <span class="db fl mr-2 h28 lh28 pl5 pr5 gray bg-white bde b-r-3-left">开票时段</span>
                        <span id="dateCombo"></span>
                    </li>
                    <li id="datePicker" class="ml1">
                        <span class="db fl mr-2 h28 lh28 pl5 pr5 gray bg-white bde b-r-3-left">细选日期</span>
                        <input type="text" id="beginDate" value="" class="fl mr-2 ui-input ui-datepicker-input" autocomplete="off" readonly />
                        <input type="text" id="endDate" value="" class="fl ui-input ui-datepicker-input" autocomplete="off" readonly />
                    </li>
                    <li class="ml1"><span id="typeCombo"></span></li>
                    <li class="ml1"><span id="usCombo"></span></li>
                    <li class="ml1"><span id="inoutCombo"></span></li>
                    <li class="ml1"><span id="transTypeCombo"></span></li>
                    <li class="ml1"><span id="billFromCombo"></span></li>
                    <li class="ml1"><span id="status1Combo"></span></li>
                    <li class="ml1"><span id="status2Combo"></span></li>
                    <li class="ml1"><span id="userCombo"></span></li>
                    <li class="ml1">
                        <input type="number" class="fl mr-2 ui-input w70" id="minAmount" value="" placeholder="最小金额(元)" />
                        <input type="number" class="fl ui-input w70" id="maxAmount" value="" placeholder="最大金额(元)" />
                    </li>
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
                <a id="add" class="ui-btn"><i class="iconfont icon-zengjia"></i><span>手动新增</span></a>
            </div>
        </div>
        <div class="top-notice">
            <div class="fl">
                <div class="fl pl10 pr10 h28 lh28 def dark bd-gray b-r-3-left" title="当前查询条件下开出发票总额">我方开出</div>
                <div id="totalOutAmount" class="fl pl10 pr10 h28 lh28 f14 dark bd-gray bln brn" title="当前查询条件下开出发票总额">待查询</div>
                <div class="fl pr10 h28 lh28 gray bd-gray bln b-r-3-right" title="当前查询条件下开出发票总额">元</div>
            </div>
            <div class="fl ml20">
                <div class="fl pl10 pr10 h28 lh28 def dark bd-gray b-r-3-left" title="当前查询条件下纳税基数总额">纳税基数</div>
                <div id="totalTaxAmount" class="fl pl10 pr10 h28 lh28 f14 dark bd-gray bln brn" title="当前查询条件下纳税基数总额">待查询</div>
                <div class="fl pr10 h28 lh28 gray bd-gray bln b-r-3-right" title="当前查询条件下纳税基数总额">元</div>
            </div>
            <div class="fl pl5 pr5 gray">–</div>
            <div class="fl">
                <div class="fl pl10 pr10 h28 lh28 def dark bd-gray b-r-3-left" title="当前查询条件下收到发票总额">我方收取</div>
                <div id="totalInAmount" class="fl pl10 pr10 h28 lh28 f14 dark bd-gray bln brn" title="当前查询条件下收到发票总额">待查询</div>
                <div class="fl pr10 h28 lh28 gray bd-gray bln b-r-3-right" title="当前查询条件下收到发票总额">元</div>
            </div>
            <div class="fl pl5 pr5 gray">=</div>
            <div class="fl">
                <div class="fl pl10 pr10 h28 lh28 def dark bd-gray b-r-3-left" title="当前查询条件下发票缺口">发票缺口</div>
                <div id="totalReAmount" class="fl pl10 pr10 h28 lh28 f14 dark bd-gray bln brn" title="当前查询条件下发票缺口">待查询</div>
                <div class="fl pr10 h28 lh28 gray bd-gray bln b-r-3-right" title="当前查询条件下发票缺口">元</div>
            </div>
            <div class="fr">
                <a id="tochinatax" href="https://etax.chongqing.chinatax.gov.cn/" target="_blank" class="ui-btn bg-blue dn">
                    <i class="iconfont icon-wangzhan"></i><span>打开税务局网站</span>
                </a>
                <a id="upfile_1" class="ui-btn ml2 bg-blue dn"><i class="iconfont icon-daoru"></i><span>导入税务局全量发票表格</span><b>我方收取</b></a>
                <a id="upfile_2" class="ui-btn ml2 bg-blue dn"><i class="iconfont icon-daoru"></i><span>导入税务局全量发票表格</span><b>我方开出</b></a>
            </div>
        </div>
        <div class="grid-wrap">
            <table id="grid"></table>
            <div id="page"></div>
        </div>
    </div>
<<<<<<< HEAD
    <script type="text/javascript" src="/statics/js/plugins/uploader/jquery.uploader.js?v=2026102701"></script>
    <script type="text/javascript" src="/statics/js/dist/invoice-list.js?v=2026102701"></script>
=======
    <script type="text/javascript" src="/statics/js/plugins/uploader/jquery.uploader.js?v=2026101403"></script>
    <script type="text/javascript" src="/statics/js/dist/invoice-list.js?v=2026101403"></script>
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
</body>

</html>
