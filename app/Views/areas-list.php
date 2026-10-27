<?= view('header') ?>
</head>

<body>
    <div class="wrapper">
        <div class="mod-search cf">
            <div class="fl">
                <ul class="ul-inline">
                    <li><span class="db fl mr-2 h28 lh28 pl5 pr5 gray bg-white bde b-r-3-left">省级</span><span id="province"></span></li>
                    <li class="ml1"><span class="db fl mr-2 h28 lh28 pl5 pr5 gray bg-white bde b-r-3-left">地级</span><span id="city"></span></li>
                    <li class="ml1"><span class="db fl mr-2 h28 lh28 pl5 pr5 gray bg-white bde b-r-3-left">县级</span><span id="area"></span></li>
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
                <a id="editable" class="ui-btn"><i class="iconfont icon-bianji"></i><span>编辑</span></a>
                <a id="diseditable" class="ui-btn"><i class="iconfont icon-tuichu"></i><span>结束编辑</span></a>
                <a class="ui-btn"><i class="iconfont icon-yijian"></i><span id="fillTimeFromCity">一键填充省级 / 县级时效</span><b>以地级时效为参考</b></a>
            </div>
        </div>
        <div class="top-notice">
            <p>
                <i class="mr10 f16 fb red iconfont icon-jinggao"></i>
                <span class="gray es">
                    快递时效说明：从万州发出，揽收至签收所需的时间，单位：天（ 初始数据批量采集自 kuaidi.com 和 kuaidi100.com，非100%准确，可按需手动编辑录入更准确的时效 ）。
                </span>
            </p>
        </div>
        <div class="grid-wrap">
            <table id="grid"></table>
            <div id="page"></div>
        </div>
    </div>
<<<<<<< HEAD
    <script type="text/javascript" src="/statics/js/dist/areas-list.js?v=2026102701"></script>
=======
    <script type="text/javascript" src="/statics/js/dist/areas-list.js?v=2026101403"></script>
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
</body>

</html>
