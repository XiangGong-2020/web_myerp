<?= view('header') ?>
</head>

<body>
    <div class="wrapper">
        <div class="fl bre" style="width:calc(100% - 401px)">
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
                    <a id="refresh" class="ui-btn"><i class="iconfont icon-shuaxin"></i><span>刷新</span></a>
                    <a id="add" class="ui-btn"><i class="iconfont icon-zengjia"></i><span>新增</span></a>
                </div>
            </div>
            <div class="grid-wrap">
                <table id="grid"></table>
            </div>
        </div>
        <div class="fr w400">
            <div class="mod-search cf">
                <div class="fl fb">员工考评项目设定</div>
                <div class="fr">
                    <ul class="ul-inline">
                        <li>
                            <div class="fl ui-btn-group">
                                <a id="batch-eva-oper" class="ui-btn"><i class="iconfont icon-shezhi"></i><span title="批量设定时，当勾选了多人，则所勾选的全部重新设定。">批量设定</span></a>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="grid-wrap">
                <table id="gridUser"></table>
            </div>
        </div>
    </div>
    <script type="text/javascript" src="/statics/js/dist/evaluation-list.js?v=2026102701"></script>
</body>

</html>
