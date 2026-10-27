<?= view('header') ?>
<link rel="stylesheet" type="text/css" href="/statics/css/manage.css?v=2026102701" />
<link rel="stylesheet" type="text/css" href="/statics/js/plugins/fSelect/fSelect.css?v=2026102701" />
</head>

<body>
    <div class="manage-wrapper">
        <form id="manage-form" action="">
            <ul class="mod-form-rows">
                <li class="row-item">
                    <div class="label-wrap"><label for="isDisable">一键复制</label></div>
                    <div class="ctn-wrap"><span id="combo1"></span>
                        <div>
                </li>
                <li class="row-item">
                    <div class="label-wrap"><label for="isDisable">或手动选择</label></div>
                    <div class="ctn-wrap"> <select id="evaCombo" class="hidden" multiple="multiple"></select>
                        <div>
                </li>
            </ul>
        </form>
    </div>
    <script type="text/javascript" src="/statics/js/plugins/fSelect/fSelect.js?v=2026102701"></script>
    <script type="text/javascript" src="/statics/js/dist/evaluation-oper.js?v=2026102701"></script>
</body>

</html>
