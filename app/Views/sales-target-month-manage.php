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
                <li class="row-item">
                    <div class="label-wrap"><label for="month">选择月份</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><span id="month"></span></div>
                </li>
                <li class="row-item">
                    <div class="label-wrap"><label for="targetAmount">目标金额</label><span class="must">●</span></div>
                    <div class="ctn-wrap">
                        <input type="number" value="" class="ui-input" name="targetAmount" id="targetAmount" autocomplete="off" title="全渠道的销售目标金额，包含线上+线下+投标等。" />
                        <span class="unit">元</span>
                    </div>
                </li>
                <li class="row-item">
                    <div class="mt10 lh30 ti10 night bg-silver b-r-3">销售目标任务分配<div class="fr pl10 pr10 ti0 white bg-red b-r-3">若不给某人分配任务则请留空或填入0</span></div>
                </li>
                <div id="userCon"></div>
            </ul>
        </form>
    </div>
<<<<<<< HEAD
    <script type="text/javascript" src="/statics/js/dist/sales-target-month-manage.js?v=2026102701"></script>
=======
    <script type="text/javascript" src="/statics/js/dist/sales-target-month-manage.js?v=2026101403"></script>
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
</body>

</html>
