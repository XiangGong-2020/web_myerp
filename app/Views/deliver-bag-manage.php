<?= view('header') ?>
<<<<<<< HEAD
<link rel="stylesheet" type="text/css" href="/statics/css/manage.css?v=2026102701" />
=======
<link rel="stylesheet" type="text/css" href="/statics/css/manage.css?v=2026101403" />
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
</head>

<body>
    <div class="manage-wrapper">
        <form id="manage-form" action="">
            <ul class="mod-form-rows">
                <li class="row-item item-md item-pct33">
                    <div class="label-wrap"><label for="no">运单号</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input" name="no" id="no" /></div>
                </li>
                <li class="row-item item-md item-pct33 row-category">
                    <div class="label-wrap"><label for="company">所属快递</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><span id="company"></span></div>
                </li>
                <li class="row-item item-md item-pct33 row-category">
                    <div class="label-wrap"><label for="address">目的地</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><span id="address"></span></div>
                </li>
                <li class="row-item item-md item-pct33 row-category">
                    <div class="label-wrap"><label for="packName">打包扫码人</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><span id="packName"></span></div>
                </li>
                <li class="row-item item-md item-pct33">
                    <div class="label-wrap"><label for="packTime">打包扫码时间</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input ui-datepicker-input" name="packTime" id="packTime" autocomplete="off" readonly /></div>
                </li>
                <li class="row-item item-md item-pct33 row-category">
                    <div class="label-wrap"><label for="scanName">称重扫码人</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><span id="scanName"></span></div>
                </li>
                <li class="row-item item-md item-pct33">
                    <div class="label-wrap"><label for="scanTime">称重扫码时间</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input ui-datepicker-input" name="scanTime" id="scanTime" autocomplete="off" readonly /></div>
                </li>
                <li class="row-item item-md item-pct33 rel">
                    <div class="label-wrap"><label for="weight">包裹重量</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><input type="number" value="" class="ui-input" name="weight" id="weight" /></div>
                    <span class="unit">KG</span>
                </li>
                <li class="row-item item-md item-pct33 rel">
                    <div class="label-wrap"><label for="amount">计费总价</label></div>
                    <div class="ctn-wrap"><input type="number" value="" class="ui-input" name="amount" id="amount" placeholder="包含所有附加费用" /></div>
                    <span class="unit">元</span>
                </li>
                <li class="row-item item-md item-pct33">
                    <div class="label-wrap"><label for="printTime">打印时间</label></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input ui-input-dis" name="printTime" id="printTime" autocomplete="off" readonly /></div>
                </li>
                <li class="row-item item-md item-pct33">
                    <div class="label-wrap"><label for="orderNo">包含订单</label></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input" name="orderNo" id="orderNo" /></div>
                </li>
                <li class="row-item item-md item-pct33 rel">
                    <div class="label-wrap"><label for="targetAmount">快递赔偿</label></div>
                    <div class="ctn-wrap"><input type="number" value="" class="ui-input" name="targetAmount" id="targetAmount" /></div>
                    <span class="unit">元</span>
                </li>
                <li class="row-item">
                    <div class="w80 lh28 ti10 gray bg-white bde b-r-3-top mb-1">备注信息</div>
                    <div class="ctn-wrap">
                        <textarea id="description" class="ui-input" style="height:300px" spellcheck="false" placeholder="添加备注信息"></textarea>
                    </div>
                </li>
            </ul>
        </form>
    </div>
<<<<<<< HEAD
    <script type="text/javascript" src="/statics/js/dist/deliver-bag-manage.js?v=2026102701"></script>
=======
    <script type="text/javascript" src="/statics/js/dist/deliver-bag-manage.js?v=2026101403"></script>
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
</body>

</html>
