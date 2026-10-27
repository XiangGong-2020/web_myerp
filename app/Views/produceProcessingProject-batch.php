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
                    <li><span id="typeCombo"></span></li>
                    <li class="ml1"><span id="isDisableCombo"></span></li>
                    <li class="ml1">
                        <input type="text" id="skey" class="fl ui-input" placeholder="输入任意关键词" />
                    </li>
                    <li class="ml1"><span id="searchTypeCombo"></span></li>
                </ul>
            </div>
            <div class="fl ml1 ui-btn-group">
                <a id="search" class="ui-btn"><i class="iconfont icon-chaxun"></i><span>查询</span></a>
            </div>
            <div class="fr ui-btn-group">
                <a id="refresh" class="ui-btn"><i class="iconfont icon-shuaxin"></i><span>刷新</span></a>
            </div>
        </div>
        <div id="zTree" class="grid-wrap rel bbe">
            <table id="grid"></table>
            <div id="page"></div>
        </div>
    </div>
    <script type="text/javascript" src="/statics/js/dist/produceProcessingProject-batch.js?v=2026102701"></script>
</body>

</html>
