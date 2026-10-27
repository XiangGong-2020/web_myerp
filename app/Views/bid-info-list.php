<?= view('header') ?>
<style>
    .winRate {
        position: relative;
        width: 100%;
        height: 30px;
        background: #00000006;
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
    <div class="wrapper bg-gray">
        <div class="mod-search cf">
            <div class="fl">
                <ul class="ul-inline">
                    <li><span id="combo1"></span></li>
                    <li class="ml1"><span id="combo2"></span></li>
                    <li id="datePicker" class="ml1">
                        <span class="db fl mr-2 h28 lh28 pl5 pr5 gray bg-white bde b-r-3-left">收录时间</span>
                        <input type="text" id="beginDate" value="" class="fl mr-2 ui-input ui-datetimepicker-input" autocomplete="off" readonly />
                        <input type="text" id="endDate" value="" class="fl ui-input ui-datetimepicker-input" autocomplete="off" readonly />
                    </li>
                    <li class="ml1">
                        <input type="text" id="skey" class="fl ui-input w200" placeholder="输入任意关键词" autocomplete="off" />
                        <div class="hotword-con dn"></div>
                    </li>
                    <li class="ml1"><span id="combo3"></span></li>
                </ul>
            </div>
            <div class="fl ml1 ui-btn-group">
                <a id="search" class="ui-btn"><i class="iconfont icon-chaxun"></i><span>查询</span></a>
            </div>
            <div class="fl ml10 lh28 bg-white bd-blue ovh b-r-3">
                <div class="fl pl10 pr10 blue">今日收录总数</div>
                <div id="todayTotal" class="fl pl10 pr10 white bg-blue b-r-3-left">？</div>
            </div>
            <div class="fr ui-btn-group">
                <a id="refresh" class="ui-btn"><i class="iconfont icon-shuaxin"></i><span>刷新</span></a>
                <a id="add" class="ui-btn"><i class="iconfont icon-zengjia"></i><span>新增</span></a>
                <a id="del1year" class="ui-btn"><i class="iconfont icon-huishouzhan"></i><span>删除1年前信息</span></a>
            </div>
        </div>
        <div class="grid-wrap">
            <table id="grid"></table>
            <div id="page"></div>
        </div>
    </div>
    <script type="text/javascript" src="/statics/js/plugins/clipboard/clipboard.min.js?v=2026102701"></script>
    <script type="text/javascript" src="/statics/js/dist/bid-info-list.js?v=2026102701"></script>
</body>

</html>
