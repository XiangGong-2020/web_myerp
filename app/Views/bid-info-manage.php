<?= view('header') ?>
<link rel="stylesheet" type="text/css" href="/statics/css/manage.css?v=2026102701" />
</head>

<body>
    <div class="manage-wrapper">
        <form id="manage-form" action="">
            <ul class="mod-form-rows">
                <li class="row-item">
                    <div class="label-wrap"><label for="bidSiteName">招标来源</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input" name="bidSiteName" id="bidSiteName" /></div>
                </li>
                <li class="row-item">
                    <div class="label-wrap"><label for="bidTitle">招标标题</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input" name="bidTitle" id="bidTitle" /></div>
                </li>
                <li class="row-item">
                    <div class="label-wrap"><label for="bidUrl">招标链接</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><input type="url" value="" class="ui-input" name="bidUrl" id="bidUrl" /></div>
                </li>
            </ul>
        </form>
    </div>
    <script type="text/javascript" src="/statics/js/dist/bid-info-manage.js?v=2026102701"></script>
</body>

</html>
