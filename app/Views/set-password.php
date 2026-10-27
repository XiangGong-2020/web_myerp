<?= view('header') ?>
<link rel="stylesheet" type="text/css" href="/statics/css/manage.css?v=2026102701" />
</head>

<body>
    <div class="manage-wrapper">
        <form id="manage-form" action="#">
            <ul class="mod-form-rows">
                <li class="row-item">
                    <div class="label-wrap"><label for="name">姓名</label></div>
                    <div class="ctn-wrap"><input type="text" name="name" id="name" value="" class="ui-input ui-input-dis" disabled /></div>
                </li>
                <li class="row-item">
                    <div class="label-wrap"><label for="userName">账号名</label></div>
                    <div class="ctn-wrap"><input type="text" name="userName" id="userName" value="" class="ui-input ui-input-dis" disabled /></div>
                </li>
                <li class="row-item">
                    <div class="label-wrap"><label for="password">新密码</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><input type="password" name="password" id="password" class="ui-input" autocomplete="off" placeholder="6 ~ 20位数字与字母（区分大小写）" /></div>
                </li>
                <li class="row-item">
                    <div class="label-wrap"><label for="confirmPassword">确认密码</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><input type="password" name="confirmPassword" id="confirmPassword" class="ui-input" autocomplete="off" placeholder="再次输入新密码" /></div>
                </li>
            </ul>
        </form>
    </div>
    <script type="text/javascript" src="/statics/js/dist/set-password.js?v=2026102701"></script>
</body>

</html>
