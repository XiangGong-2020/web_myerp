<?= view('header') ?>
<link rel="stylesheet" type="text/css" href="/statics/css/manage.css?v=2026102701" />
</head>

<body>
    <div class="manage-wrapper">
        <form id="manage-form" action="">
            <ul class="mod-form-rows">
                <li class="row-item">
                    <div class="label-wrap"><label for="name">网店账号</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input" name="name" id="name" autocomplete="off" /></div>
                </li>
                <li class="row-item">
                    <div class="label-wrap"><label for="uName">当前拥有人</label></div>
                    <div class="ctn-wrap"><span id="uName"></span></div>
                </li>
                <li class="row-item">
                    <div class="label-wrap"><label for="his">历史拥有人</label></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input ui-input-dis" name="his" id="his" disabled /></div>
                </li>
            </ul>
        </form>
    </div>
    <script type="text/javascript" src="/statics/js/dist/shop-account-manage.js?v=2026102701"></script>
</body>

</html>
