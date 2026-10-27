<?= view('header') ?>
</head>

<body>
    <div class="wrapper">
        <div class="mod-search cf">
            <div class="fr ui-btn-group">
                <a id="refresh" class="ui-btn"><i class="iconfont icon-shuaxin"></i><span>刷新</span></a>
                <a id="editable" class="ui-btn"><i class="iconfont icon-bianji"></i><span>编辑</span></a>
                <a id="diseditable" class="ui-btn"><i class="iconfont icon-tuichu"></i><span>结束编辑</span></a>
            </div>
        </div>
        <div class="grid-wrap">
            <table id="grid"></table>
            <div id="page"></div>
        </div>
    </div>
    <script type="text/javascript" src="/statics/js/dist/system-right.js?v=2026102701"></script>
</body>

</html>
