<?= view('header') ?>
</head>

<body>
    <div class="wrapper">
        <div class="mod-search cf">
            <div class="fl">
                <ul class="ul-inline">
                    <li><span id="combo1"></span></li>
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
        <div class="top-notice">
            <p>
                <i class="mr10 f16 fb red iconfont icon-jinggao"></i>
                <span class="gray es">
                    昨天12点前打印的面单，但库房至今未扫码发走，将判定为滞留包裹（ 特别注意：打印了但未使用的废面单，必须及时在灵通打单里进行单号回收），滞留包裹由库房扫码发走、或单号被回收，将自动从本列表消失。
                </span>
            </p>
        </div>
        <div class="grid-wrap">
            <table id="grid"></table>
            <div id="page"></div>
        </div>
    </div>
<<<<<<< HEAD
    <script type="text/javascript" src="/statics/js/dist/deliver-bag-dull.js?v=2026102701"></script>
=======
    <script type="text/javascript" src="/statics/js/dist/deliver-bag-dull.js?v=2026101403"></script>
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
</body>

</html>
