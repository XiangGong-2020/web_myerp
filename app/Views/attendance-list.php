<?= view('header') ?>
</head>

<body>
    <div class="wrapper">
        <div class="mod-search cf">
            <div class="fr ui-btn-group">
                <a id="refresh" class="ui-btn"><i class="iconfont icon-shuaxin"></i><span>刷新</span></a>
                <a id="createLastMonthAttendance" class="ui-btn bg-green"><i class="iconfont icon-shengcheng"></i><span>生成上月出勤月表</span></a>
                <a id="redoLastMonthAttendance" class="ui-btn bg-red"><i class="iconfont icon-jisuan"></i><span>重算上月出勤月表</span></a>
            </div>
        </div>
        <div class="grid-wrap">
            <table id="grid"></table>
            <div id="page"></div>
        </div>
    </div>
    <script type="text/javascript" src="/statics/js/plugins/calendar/calendar.js?v=2026102701"></script>
    <script type="text/javascript" src="/statics/js/dist/attendance-list.js?v=2026102701"></script>
</body>

</html>
