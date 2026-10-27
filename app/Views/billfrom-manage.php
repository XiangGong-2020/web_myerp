<?= view('header') ?>
<<<<<<< HEAD
<link rel="stylesheet" type="text/css" href="/statics/css/manage.css?v=2026102701" />
=======
<link rel="stylesheet" type="text/css" href="/statics/css/manage.css?v=2026101403" />
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
</head>

<body>
    <div class="manage-wrapper">
        <form id="manage-form" action="#">
            <ul class="mod-form-rows">
                <li class="row-item">
                    <div class="label-wrap"><label for="name">渠道名称</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input ui-input-dis" name="name" id="name" autocomplete="off" readonly /></div>
                </li>
                <li class="row-item">
                    <div class="label-wrap"><label for="company">关联主体</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><span id="company"></span></div>
                </li>
                <li class="row-item">
                    <div class="lh30 ti10 white bg-orange b-r-3">提醒：修改关联主体后，再产生的订单将指向新的关联主体，以前的订单关联主体信息不会改变。</div>
                </li>
                <li class="row-item">
                    <div class="w80 lh28 ti10 gray bg-white bde b-r-3-top mb-1">备注信息</div>
                    <div class="ctn-wrap">
                        <textarea id="description" class="ui-input" spellcheck="false" placeholder="添加备注信息"></textarea>
                    </div>
                </li>
            </ul>
        </form>
    </div>
<<<<<<< HEAD
    <script type="text/javascript" src="/statics/js/dist/billfrom-manage.js?v=2026102701"></script>
=======
    <script type="text/javascript" src="/statics/js/dist/billfrom-manage.js?v=2026101403"></script>
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
</body>

</html>
