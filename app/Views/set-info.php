<?= view('header') ?>
<link rel="stylesheet" type="text/css" href="/statics/css/manage.css?v=2026102701" />
</head>

<body>
    <div class="manage-wrapper">
        <form id="manage-form" action="#">
            <ul class="mod-form-rows">
                <li class="row-item item-pct50">
                    <div class="label-wrap"><label for="wechat">常用微信</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><input type="text" value="" name="wechat" id="wechat" class="ui-input" /></div>
                </li>
                <li class="row-item item-pct50">
                    <div class="label-wrap"><label for="mobile">常用手机</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><input type="text" value="" name="mobile" id="mobile" class="ui-input" /></div>
                </li>
                <li class="row-item item-pct50">
                    <div class="label-wrap"><label for="birthdaySolar">阳历生日</label></div>
                    <div class="ctn-wrap"><input type="text" value="" name="birthdaySolar" id="birthdaySolar" class="ui-input" placeholder="例如：8月8日" /></div>
                </li>
                <li class="row-item item-pct50">
                    <div class="label-wrap"><label for="birthdayLunar">农历生日</label></div>
                    <div class="ctn-wrap"><input type="text" value="" name="birthdayLunar" id="birthdayLunar" class="ui-input" placeholder="例如：腊月廿八" /></div>
                </li>
                <li class="row-item item-pct50">
                    <div class="label-wrap"><label for="animal">生肖</label></div>
                    <div class="ctn-wrap rel"><span id="animal"></span></div>
                </li>
                <li class="row-item item-pct50">
                    <div class="label-wrap"><label for="astro">星座</label></div>
                    <div class="ctn-wrap rel"><span id="astro"></span></div>
                </li>
            </ul>
        </form>
    </div>
    <script type="text/javascript" src="/statics/js/dist/set-info.js?v=2026102701"></script>
</body>

</html>
