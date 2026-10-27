<?= view('header') ?>
</head>

<body>
    <div class="wrapper">
        <div class="mod-search cf">
            <div class="fl">
                <ul class="fl ul-inline">
                    <li class="ml1"><span id="typeCombo"></span></li>
                </ul>
                <div class="fl ml10 ui-btn-group">
                    <a id="order" class="ui-btn bg-gray current"><span>采购订单缺票</span></a>
                    <a id="contract_info" class="ui-btn bg-gray"><span>特殊采购单缺票</span></a>
                    <a id="money" class="ui-btn bg-gray"><span>其他支出单缺票</span></a>
                    <a id="deliver" class="ui-btn bg-gray"><span>快递月结单缺票</span></a>
                        <li class="ml1"><span id="typeCombo"></span></li>
                </div>
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
<<<<<<< HEAD
    <script type="text/javascript" src="/statics/js/dist/invoice-miss-list.js?v=2026102701"></script>
=======
    <script type="text/javascript" src="/statics/js/dist/invoice-miss-list.js?v=2026101403"></script>
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
</body>

</html>
