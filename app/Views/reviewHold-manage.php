<?= view('header') ?>
<link rel="stylesheet" type="text/css" href="/statics/css/manage.css?v=2026102701" />
</head>

<body>
    <div class="manage-wrapper">
        <form id="manage-form" action="">
            <ul class="mod-form-rows">
                <li class="row-item item-pct25">
                    <div class="label-wrap"><label for="level">差评等级</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><span id="level"></span></div>
                </li>
                <li class="row-item item-pct25">
                    <div class="label-wrap"><label for="billfrom">渠道店铺</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><span id="billfrom"></span></div>
                </li>
                <li class="row-item item-pct50">
                    <div class="label-wrap"><label for="orderNo">订单号</label><span class="must">●</span></div>
                    <div class="ctn-wrap rel"><input type="text" value="" class="ui-input" name="orderNo" id="orderNo" /></div>
                </li>
                <li class="row-item">
                    <div class="ctn-wrap">
                        <div class="fl w80 mb-1 lh28 ti10 gray bg-white bde b-r-3-top">处理备注</div>
                        <textarea id="description" class="ui-input" spellcheck="false" placeholder="添加处理过程中的备注信息"></textarea>
                    </div>
                </li>
            </ul>
        </form>
    </div>
    <script type="text/javascript" src="/statics/js/dist/reviewHold-manage.js?v=2026102701"></script>
</body>

</html>
