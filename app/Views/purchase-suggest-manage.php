<?= view('header') ?>
<<<<<<< HEAD
<link rel="stylesheet" type="text/css" href="/statics/css/manage.css?v=2026102701" />
=======
<link rel="stylesheet" type="text/css" href="/statics/css/manage.css?v=2026101403" />
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
</head>

<body>
    <div class="manage-wrapper">
        <div id="div-lock" class="dn">
            <div class="loading">开发中...</div>
        </div>
        <form id="manage-form" action="">
            <ul class="mod-form-rows">
                <li class="row-item item-md item-pct20">
                    <div class="label-wrap"><label for="price">建议采购单价</label></div>
                    <div class="ctn-wrap rel">
                        <input type="number" value="" class="ui-input" name="price" id="price" />
                        <span class="unit">元</span>
                    </div>
                </li>
                <li class="row-item item-md item-pct80">
                    <div class="label-wrap"><label for="url">建议采购链接</label></div>
                    <div class="ctn-wrap rel">
                        <input type="url" value="" class="ui-input" name="url" id="url" />
                    </div>
                    <div id="copyurl" class="w40 h20 lh20 tc hover-yellow b-r-3-left dn" style="position:absolute;z-index:1;top:4px;right:45px;">复制</div>
                    <div id="openurl" class="w40 h20 lh20 tc hover-green b-r-3-right dn" style="position:absolute;z-index:1;top:4px;right:3px;">访问</div>
                </li>
                <li class="row-item">
                    <div class="fl w100 lh28 ti10 gray bg-white bde b-r-3-top mb-1">其他建议内容</div>
                    <div class="ctn-wrap">
                        <textarea id="content" class="ui-input" spellcheck="false" placeholder="视情况填写"></textarea>
                    </div>
                </li>
            </ul>
        </form>
    </div>
<<<<<<< HEAD
    <script type="text/javascript" src="/statics/js/plugins/clipboard/clipboard.min.js?v=2026102701"></script>
    <script type="text/javascript" src="/statics/js/dist/purchase-suggest-manage.js?v=2026102701"></script>
=======
    <script type="text/javascript" src="/statics/js/plugins/clipboard/clipboard.min.js?v=2026101403"></script>
    <script type="text/javascript" src="/statics/js/dist/purchase-suggest-manage.js?v=2026101403"></script>
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
</body>

</html>
