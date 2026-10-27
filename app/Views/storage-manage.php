<?= view('header') ?>
<link rel="stylesheet" type="text/css" href="/statics/css/manage.css?v=2026102701" />
</head>

<body>
    <div class="manage-wrapper">
        <form id="manage-form" action="#">
            <ul class="mod-form-rows">
                <li class="row-item">
                    <div class="label-wrap"><label for="name">仓库名称</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input" name="name" id="name" /></div>
                </li>
                <li class="row-item">
                    <div class="label-wrap"><label for="empName">应急联系人</label></div>
                    <div class="ctn-wrap"><span id="empName"></span></div>
                </li>
                <li class="row-item">
                    <div class="label-wrap"><label for="type">仓库类型</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><span id="type"></span></div>
                </li>
                <li class="row-item">
                    <div class="label-wrap"><label for="allowNeg">允许爆仓</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><span id="allowNeg"></span></div>
                </li>
                <li class="row-item">
                    <div class="label-wrap"><label for="address">仓库地址</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input" name="address" id="address" /></div>
                </li>
                <li class="row-item">
                    <div class="label-wrap"><label for="phone">仓库电话</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input" name="phone" id="phone" /></div>
                </li>
            </ul>
        </form>
    </div>
<<<<<<< HEAD
    <script type="text/javascript" src="/statics/js/dist/storage-manage.js?v=2026102701"></script>
=======
    <script type="text/javascript" src="/statics/js/dist/storage-manage.js?v=2026102701"></script>
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
</body>

</html>
