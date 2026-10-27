<?= view('header') ?>
</head>

<body>
    <div class="wrapper">
        <div class="mod-search cf">
            <div class="fl">
                <ul class="ul-inline">
                    <li><span id="usercombo"></span></li>
                    <li class="ml1"><span id="combo2"></span></li>
                    <li id="datePicker" class="ml1">
                        <span class="db fl mr-2 h28 lh28 pl5 pr5 gray bg-white bde b-r-3-left">起止日期</span>
                        <input type="text" id="beginDate" value="" class="fl mr-2 ui-input ui-datepicker-input" autocomplete="off" readonly />
                        <input type="text" id="endDate" value="" class="fl ui-input ui-datepicker-input" autocomplete="off" readonly />
                    </li>
                    <li class="ml1">
                        <input type="text" id="skey" class="fl ui-input" placeholder="输入任意关键词" />
                    </li>
                </ul>
            </div>
            <div class="fl ml1 ui-btn-group">
                <a id="search" class="ui-btn current"><i class="iconfont icon-chaxun"></i><span>查询</span></a>
                <a id="today" class="ui-btn"><span>今天</span></a>
                <a id="yesterday" class="ui-btn"><span>昨天</span></a>
                <a id="curMonth" class="ui-btn"><span>本月</span></a>
                <a id="lastMonth" class="ui-btn"><span>上月</span></a>
            </div>
            <div class="fl ml1 rel ui-btn-group">
                <a id="omit" class="ui-btn"><i class="iconfont icon-xianshi"></i><span>漏打卡</span></a>
            </div>
            <div class="fr ui-btn-group">
                <a id="refresh" class="ui-btn"><i class="iconfont icon-shuaxin"></i><span>刷新</span></a>
                <a id="add" class="ui-btn"><i class="iconfont icon-zengjia"></i><span>新增</span></a>
            </div>
        </div>
        <div id="uncheckinfos" class="mb-1 bbd dn">未审核的考勤申请信息</div>
        <div class="grid-wrap">
            <table id="grid"></table>
            <div id="page"></div>
        </div>
    </div>
    <script type="text/javascript" src="/statics/js/dist/clockin-list.js?v=2026102701"></script>
</body>

</html>
