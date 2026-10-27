<?= view('header') ?>
<link rel="stylesheet" type="text/css" href="/statics/css/manage.css?v=2026102701" />
</head>

<body>
    <div class="manage-wrapper">
        <form id="manage-form" action="">
            <ul class="mod-form-rows">
                <div class="ml10 mr10 pb10 bg-silver b-r-3 ovh">
                    <li class="row-item">
                        <div class="label-wrap"><label for="goodsText">自家产品</label><span class="must">●</span></div>
                        <div class="ctn-wrap" id="goods">
                            <input type="text" name="goodsText" id="goodsText" class="ui-input def" style="width:calc(100% - 92px);" autocomplete="off" value="" readonly="readonly" required />
                            <i class="ui-icon-ellipsis iconfont icon-shenglvehao"></i>
                        </div>
                    </li>
                    <li class="row-item">
                        <div class="label-wrap"><label for="url">自家链接</label><span class="must">●</span></div>
                        <div class="ctn-wrap">
                            <input type="text" name="url" id="url" class="ui-input" value="" placeholder="需是淘宝自家主店中的商品链接" required />
                        </div>
                    </li>
                </div>
                <div class="mt10">
                    <a id="addurl" class="db w110 ml10 h28 lh28 ma tc hover-teal b-r-3"><i class="iconfont icon-zengjia f12 mr5"></i><span>增加竞品链接</span></a>
                </div>
                <div class="h10 ml10 mt10 mr10 btc" style="position:relative;z-index:1;box-shadow: inset 0 5px 5px #00000022;"></div>
                <div id="urls" class="ml10 mt-10 mr10 pb10 bg-silver b-r-3 ovh" style="height:440px;overflow-y:auto;"></div>
            </ul>
        </form>
    </div>
    <script type="text/javascript" src="/statics/js/dist/competitive-monitor-manage.js?v=2026102701"></script>
</body>

</html>
