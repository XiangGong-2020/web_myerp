<?= view('header') ?>
<link rel="stylesheet" type="text/css" href="/statics/css/manage.css?v=2026102701" />
</head>

<body>
    <div class="manage-wrapper">
        <form id="manage-form" action="">
            <ul class="mod-form-rows">
                <li class="row-item">
                    <div class="label-wrap"><label for="urlName">网站名称</label><span class="must">●</span></div>
                    <div class="ctn-wrap rel"><input type="text" value="" class="ui-input" name="urlName" id="urlName" /></div>
                </li>
                <li class="row-item">
                    <div class="label-wrap"><label for="urlDes">网站描述</label><span class="must">●</span></div>
                    <div class="ctn-wrap rel"><input type="text" value="" class="ui-input" name="urlDes" id="urlDes" /></div>
                </li>
                <li class="row-item">
                    <div class="label-wrap"><label for="urlLink">网站地址</label><span class="must">●</span></div>
                    <div class="ctn-wrap rel"><input type="url" value="" class="ui-input" name="urlLink" id="urlLink" /></div>
                </li>
            </ul>
        </form>
    </div>
    <script type="text/javascript" src="/statics/js/dist/work-site-manage.js?v=2026102701"></script>
</body>

</html>
