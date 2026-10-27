<?= view('header') ?>
<style>
    .task-line {
        float: left;
        width: 530px;
        height: 30px;
        line-height: 30px;
        padding: 10px;
        background-color: #fff;
        border-right: 1px solid #e1e2e3;
        border-bottom: 1px solid #e1e2e3;
    }

    .task-name {
        float: left;
        width: 300px;
        padding: 0 0 0 10px;
        color: #666;
    }

    .task-status {
        float: left;
        width: 60px;
    }
</style>
</head>

<body>
    <div class="wrapper">
        <div class="mod-search cf">
            <div class="fr ui-btn-group">
                <a id="refresh" class="ui-btn"><i class="iconfont icon-shuaxin"></i><span>刷新</span></a>
            </div>
        </div>
        <div class="diy-con bg-white">
            <div id="task-wrapper"></div>
        </div>
    </div>
<<<<<<< HEAD
    <script type="text/javascript" src="/statics/js/dist/system-task.js?v=2026102701"></script>
=======
    <script type="text/javascript" src="/statics/js/dist/system-task.js?v=2026101403"></script>
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
</body>

</html>
