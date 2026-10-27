<?= view('header') ?>
</head>

<body>
    <div class="wrapper">
        <div class="mod-search cf">
            <div class="fr ui-btn-group">
                <a id="refresh" class="ui-btn"><i class="iconfont icon-shuaxin"></i><span>刷新</span></a>
                <a id="toEvaluationList" class="ui-btn"><i class="iconfont icon-kaoping"></i><span>考评项目管理</span></a>
                <a id="toUserStaffList" class="ui-btn"><i class="iconfont icon-renshi"></i><span>员工考评打分</span></a>
            </div>
        </div>
        <div class="grid-wrap">
            <table id="grid"></table>
            <div id="page"></div>
        </div>
    </div>
    <script type="text/javascript" src="/statics/js/dist/evaluate-list.js?v=2026102701"></script>
</body>

</html>
