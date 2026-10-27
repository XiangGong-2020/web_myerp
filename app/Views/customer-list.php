<?= view('header') ?>
<style>
    .winRate {
        position: relative;
        width: 100%;
        height: 30px;
        background: #f0f0f0;
        border-radius: 3px;
        overflow: hidden;
    }

    .winRate-bar {
        width: 0px;
        height: 30px;
    }

    .winRate-text {
        position: absolute;
        z-index: 1;
        top: 0;
        left: 0;
        right: 0;
        height: 30px;
        font-size: 88%;
        line-height: 30px;
        color: #698096;
    }
</style>
</head>

<body>
    <div class="wrapper">
        <div class="mod-search cf">
            <div class="fl">
                <ul class="ul-inline">
                    <li><span id="category"></span></li>
                    <li class="ml1"><span id="disabletype"></span></li>
                    <li class="ml1">
                        <input type="text" id="skey" class="fl ui-input" placeholder="输入任意关键词" />
                    </li>
                </ul>
            </div>
            <div class="fl ml1 ui-btn-group">
                <a id="search" class="ui-btn current"><i class="iconfont icon-chaxun"></i><span>查询</span></a>
                <a id="searchBlack" class="ui-btn"><span>黑名单</span></a>
            </div>
            <div class="fr ui-btn-group">
                <a id="refresh" class="ui-btn"><i class="iconfont icon-shuaxin"></i><span>刷新</span></a>
                <a id="add" class="ui-btn"><i class="iconfont icon-zengjia"></i><span>新增</span></a>
                <a id="export" class="ui-btn dn"><i class="iconfont icon-daochu"></i><span>导出</span></a>
                <a id="addBlack" class="ui-btn"><i class="iconfont icon-heimingdan"></i><span>加入黑名单</span></a>
                <a id="removeBlack" class="ui-btn"><i class="iconfont icon-heimingdan"></i><span>移出黑名单</span></a>
            </div>
        </div>
        <div class="grid-wrap">
            <table id="grid"></table>
            <div id="page"></div>
        </div>
    </div>
    <script type="text/javascript" src="/statics/js/dist/customer-list.js?v=2026102701"></script>
</body>

</html>
