<?= view('header') ?>
<link rel="stylesheet" type="text/css" href="/statics/css/manage.css?v=2026102701" />
</head>

<body>
    <div class="manage-wrapper">
        <form id="manage-form" action="">
            <ul class="mod-form-rows mt20">
                <li class="row-item">
                    <div class="label-wrap"><label for="targetAmount">目标金额</label><span class="must">●</span></div>
                    <div class="ctn-wrap">
                        <input type="number" value="" class="ui-input" name="targetAmount" id="targetAmount" autocomplete="off" title="全渠道的销售目标金额，包含线上+线下+投标等。" />
                        <span class="unit">元</span>
                    </div>
                </li>
            </ul>
        </form>
    </div>
    <script type="text/javascript" src="/statics/js/dist/sales-target-year-manage.js?v=2026102701"></script>
</body>

</html>
