<?= view('header') ?>
<link rel="stylesheet" type="text/css" href="/statics/css/manage.css?v=2026102701" />
</head>

<body>
    <div class="manage-wrapper">
        <form id="manage-form" action="">
            <ul class="mod-form-rows">
                <li class="row-item">
                    <div class="label-wrap"><label for="company">所属快递</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><span id="company"></span></div>
                </li>
                <li class="row-item">
                    <div class="label-wrap"><label for="no">拦截单号</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input" name="no" id="no" /></div>
                </li>
                <li class="row-item">
                    <div class="label-wrap"><label for="description">拦截原因</label></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input" name="description" id="description" /></div>
                </li>
            </ul>
        </form>
    </div>
    <script type="text/javascript" src="/statics/js/dist/deliver-bag-off-manage.js?v=2026102701"></script>
</body>

</html>
