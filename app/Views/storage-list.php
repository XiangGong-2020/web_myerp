<?= view('header') ?>
<style>
    .each {
        position: relative;
        float: left;
        width: auto;
        height: 24px;
        padding: 0 20px 0 20px;
        margin: 10px 0 0 10px;
        color: #000;
        background: #fff;
        border-radius: 4px;
        cursor: pointer;
        overflow: hidden;
    }

    .each:hover {
        color: #fff;
        background: #666;
        padding: 0 30px 0 10px;
        transition: all .3s;
    }

    .each .each-name {
        display: block;
        width: auto;
        height: 24px;
        line-height: 24px;
    }

    .each .each-trash {
        position: absolute;
        top: 3px;
        right: 3px;
        width: 18px;
        height: 18px;
        font-size: 12px;
        line-height: 18px;
        text-align: center;
        color: #fff;
        cursor: pointer;
        border-radius: 4px;
    }

    .each:hover .each-trash {
        color: #fff;
        background: #999;
    }

    .each:hover .each-trash:hover {
        color: #fff;
        background: #f00;
        transition: all .5s;
    }
</style>
</head>

<body>
    <div class="wrapper">
        <div class="mod-search cf">
            <div class="fr rel ui-btn-group">
                <a id="refresh" class="ui-btn"><i class="iconfont icon-shuaxin"></i><span>刷新</span></a>
            </div>
        </div>
        <div class="grid-wrap">
            <table id="grid"></table>
            <div id="page"></div>
        </div>
    </div>
<<<<<<< HEAD
    <script type="text/javascript" src="/statics/js/dist/storage-list.js?v=2026102701"></script>
=======
    <script type="text/javascript" src="/statics/js/dist/storage-list.js?v=2026101403"></script>
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
</body>

</html>
