<?= view('header') ?>
<style>
    .ztree {
        position: absolute;
        z-index: 1;
        right: 0;
        top: 0;
        width: 60px;
        height: 430px;
        overflow-x: hidden;
        box-shadow: -1px 0 1px 0 #e1e2e3;
    }

    .ztree:hover {
        width: 300px;
        box-shadow: -5px 0 10px 0 #2c65ff33;
        transition: all .2s;
    }

    .ztree>li {
        overflow: hidden;
    }
</style>
</head>

<body class="ovh">
    <div class="container">
        <div class="mod-search cf">
            <div class="fl">
                <ul class="ul-inline">
                    <li><span id="combo1"></span></li>
                    <li class="ml1">
                        <input type="text" id="skey" class="fl ui-input" placeholder="输入任意关键词" />
                    </li>
                    <li class="ml1"><span id="combo2"></span></li>
                </ul>
            </div>
            <div class="fl ml1 ui-btn-group">
                <a id="search" class="ui-btn"><i class="iconfont icon-chaxun"></i><span>查询</span></a>
            </div>
            <div class="fr ui-btn-group">
                <a id="refresh" class="ui-btn"><i class="iconfont icon-shuaxin"></i><span>刷新</span></a>
                <a id="quickAdd" class="ui-btn"><i class="iconfont icon-zengjia"></i><span>新增</span></a>
            </div>
        </div>
        <div id="zTree" class="grid-wrap rel bbe">
            <table id="grid"></table>
            <div id="page"></div>
        </div>
    </div>
<<<<<<< HEAD
    <script type="text/javascript" src="/statics/js/plugins/uploader/jquery.uploader.js?v=2026102701"></script>
    <script type="text/javascript" src="/statics/js/dist/goods-batch.js?v=2026102701"></script>
=======
    <script type="text/javascript" src="/statics/js/plugins/uploader/jquery.uploader.js?v=2026101403"></script>
    <script type="text/javascript" src="/statics/js/dist/goods-batch.js?v=2026101403"></script>
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
</body>

</html>
