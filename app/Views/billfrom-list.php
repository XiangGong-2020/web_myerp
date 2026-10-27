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
            <i class="mr10 f16 fb red iconfont icon-jinggao"></i>
            <span class="gray es">
                销售渠道仅针对销售订单来源，由系统进行订单同步时自动匹配与创建。销售渠道必须关联经营主体，关联后，每笔销售订单的默认收付款账户才能与关联经营主体下的第一结算账户进行智能匹配。
            </span>
        </div>
        <div class="grid-wrap">
            <table id="grid"></table>
            <div id="page"></div>
        </div>
    </div>
<<<<<<< HEAD
    <script type="text/javascript" src="/statics/js/dist/billfrom-list.js?v=2026102701"></script>
=======
    <script type="text/javascript" src="/statics/js/dist/billfrom-list.js?v=2026101403"></script>
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
</body>

</html>
