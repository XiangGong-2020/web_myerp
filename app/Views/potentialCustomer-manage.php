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
                <li class="row-item item-pct50">
                    <div class="label-wrap"><label for="name">客户名称</label><span class="must">●</span></div>
                    <div class="ctn-wrap rel"><input type="text" value="" class="ui-input" name="name" id="name" /></div>
                </li>
                <li class="row-item item-pct50">
                    <div class="label-wrap"><label for="category">客户类别</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><span id="category"></span></div>
                </li>
            </ul>
            <div class="mt10 ml10 mr10">
                <h3 class="dn">联系方式</h3>
                <div class="grid-wrap bde b-r-3">
                    <table id="grid"></table>
                    <div id="page"></div>
                </div>
            </div>
            <ul class="mod-form-rows">
                <li class="row-item">
                    <div class="w80 lh28 ti10 gray bg-white bde b-r-3-top mb-1">跟进备注</div>
                    <div class="ctn-wrap">
                        <textarea id="description" class="ui-input" spellcheck="false" placeholder="添加潜在客户跟进情况备注信息"></textarea>
                    </div>
                </li>
            </ul>
        </form>
        <div class="hideFile dn">
            <input type="text" class="textbox address" name="address" id="address" autocomplete="off" readonly />
        </div>
    </div>
<<<<<<< HEAD
    <script type="text/javascript" src="/statics/js/dist/potentialCustomer-manage.js?v=2026102701"></script>
=======
    <script type="text/javascript" src="/statics/js/dist/potentialCustomer-manage.js?v=2026101403"></script>
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
</body>

</html>
