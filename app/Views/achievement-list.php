<?= view('header') ?>
</head>

<body>
    <div class="wrapper">
        <div class="mod-search cf">
            <div class="fl">
                <ul class="ul-inline">
                    <li><span id="yearCombo"></span></li>
                    <li class="ml1"><span id="monthCombo"></span></li>
                    <div class="fl ml1 ui-btn-group">
                        <a id="createMonthAchievement" class="ui-btn bg-green"><i class="iconfont icon-shengcheng"></i><span>生成该月工资月表</span></a>
                        <a id="redoMonthAchievement" class="ui-btn bg-red"><i class="iconfont icon-jisuan"></i><span>重算该月工资月表</span></a>
                    </div>
                </ul>
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
    <script type="text/javascript" src="/statics/js/dist/achievement-list.js?v=2026102701"></script>
</body>

</html>
