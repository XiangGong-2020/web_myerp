<?= view('header') ?>
<link rel="stylesheet" type="text/css" href="/statics/css/manage.css?v=2026102701" />
</head>

<body>
    <div class="manage-wrapper">
        <form id="manage-form" action="">
            <ul class="mod-form-rows">
                <li class="row-item item-pct33">
                    <div class="label-wrap"><label for="province">省级</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><span id="province"></span></div>
                </li>
                <li class="row-item item-pct33">
                    <div class="label-wrap"><label for="city">地级</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><span id="city"></span></div>
                </li>
                <li class="row-item item-pct33">
                    <div class="label-wrap"><label for="area">县级</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><span id="area"></span></div>
                </li>
                <li class="row-item">
                    <div class="label-wrap"><label for="address">详细地址</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input" name="address" id="address" /></div>
                </li>
            </ul>
        </form>
    </div>
    <script type="text/javascript" src="/statics/js/dist/address-manage.js?v=2026102701"></script>
</body>

</html>
