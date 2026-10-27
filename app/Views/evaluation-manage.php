<?= view('header') ?>
<link rel="stylesheet" type="text/css" href="/statics/css/manage.css?v=2026102701" />
</head>

<body>
    <div class="manage-wrapper">
        <form id="manage-form" action="">
            <ul class="mod-form-rows">
                <li class="row-item">
                    <div class="label-wrap"><label for="type">对应职位</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><span id="type"></span></div>
                </li>
                <li class="row-item">
                    <div class="label-wrap"><label for="name">考评内容</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input" name="name" id="name" /></div>
                </li>
            </ul>
        </form>
    </div>
    <script type="text/javascript" src="/statics/js/dist/evaluation-manage.js?v=2026102701"></script>
</body>

</html>
