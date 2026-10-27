<?= view('header') ?>
<<<<<<< HEAD
<link rel="stylesheet" type="text/css" href="/statics/css/manage.css?v=2026102701" />
<link rel="stylesheet" type="text/css" href="/statics/js/plugins/fSelect/fSelect.css?v=2026102701" />
=======
<link rel="stylesheet" type="text/css" href="/statics/css/manage.css?v=2026101403" />
<link rel="stylesheet" type="text/css" href="/statics/js/plugins/fSelect/fSelect.css?v=2026101403" />
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
</head>

<body>
    <div class="manage-wrapper">
        <form id="manage-form" action="">
            <ul class="mod-form-rows">
                <li class="row-item item-pct20">
                    <div class="label-wrap"><label for="reqName">申请人</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><span id="reqName"></span></div>
                </li>
                <li class="row-item item-pct20">
                    <div class="label-wrap"><label for="reqTime">申请时间</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input ui-datetimepicker-input" name="reqTime" id="reqTime" autocomplete="off" readonly /></div>
                </li>
                <li class="row-item item-pct20">
                    <div class="label-wrap"><label for="lastDate">交付限期</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input ui-datepicker-input" name="lastDate" id="lastDate" autocomplete="off" readonly /></div>
                </li>
                <li class="row-item item-pct40 rel">
                    <div class="label-wrap"><label for="goodsText">任务产品</label><span class="must">●</span></div>
                    <div class="ctn-wrap" id="goods">
                        <input type="text" name="goodsText" id="goodsText" class="ui-input def" style="width:calc(100% - 92px);" autocomplete="off" value="" readonly="readonly" required />
                        <i class="ui-icon-ellipsis iconfont icon-shenglvehao"></i>
                    </div>
                </li>
                <li class="row-item item-pct20">
                    <div class="label-wrap"><label for="qty">任务产量</label><span class="must">●</span></div>
                    <div class="ctn-wrap">
                        <input type="number" name="qty" id="qty" class="ui-input" value="" required />
                    </div>
                </li>
                <li class="row-item item-pct80">
                    <div class="label-wrap"><label for="goodsText">产量参考</label></div>
                    <div class="ctn-wrap">
                        <span id="qtyNotice" class="fl pr10 h28 lh28 bg-white bde b-r-3-right" style="width:calc(100% - 92px);"><span class="pl10 pr10">请先选定任务产品</span></span>
                    </div>
                </li>
                <li class="row-item">
                    <div class="label-wrap"><label for="members">指定配货人</label></div>
                    <div class="ctn-wrap">
                        <select id="membersCombo" class="hidden" multiple="multiple"></select>
                    </div>
                </li>
                <li class="row-item">
                    <div class="w80 lh28 ti10 gray bg-white bde b-r-3-top mb-1">备注信息</div>
                    <div class="ctn-wrap">
                        <textarea id="description" class="ui-input" spellcheck="false" placeholder="添加备注信息"></textarea>
                    </div>
                </li>
            </ul>
        </form>
        <div class="grid-wrap mt10 ml10 mr10 bde b-r-3">
            <table id="grid"></table>
            <div id="page"></div>
        </div>
        <div id="initCombo" class="dn">
            <input type="text" class="textbox userAuto" name="user" autocomplete="off" />
        </div>
    </div>
<<<<<<< HEAD
    <script type="text/javascript" src="/statics/js/plugins/fSelect/fSelect.js?v=2026102701"></script>
    <script type="text/javascript" src="/statics/js/dist/produceKittingTask-manage.js?v=2026102701"></script>
=======
    <script type="text/javascript" src="/statics/js/plugins/fSelect/fSelect.js?v=2026101403"></script>
    <script type="text/javascript" src="/statics/js/dist/produceKittingTask-manage.js?v=2026101403"></script>
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
</body>

</html>
