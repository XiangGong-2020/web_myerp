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
                <li class="row-item">
                    <div class="label-wrap"><label for="title">申请标题</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input" style="width:calc(100% - 92px);" name="title" id="title" placeholder="添加简要的申请描述作为标题" /></div>
                </li>
                <li class="row-item">
                    <div class="fl w80 lh28 ti10 gray bg-white bde b-r-3-top mb-1">申请详情</div><span id="createTime" class="fl ml10 lh28 gray"></span>
                    <div class="ctn-wrap">
                        <textarea id="content" class="ui-input" spellcheck="false" placeholder="添加申请的说明信息，越详细越好（例如：是什么客户，需要什么产品，购买量多少，原价多少，申请优惠价多少，等等……）。"></textarea>
                    </div>
                </li>
            </ul>
        </form>
        <ul class="mod-form-rows">
            <li class="row-item">
                <div class="w80 lh28 ti10 gray bg-white bde b-r-3-top mb-1">拒绝理由</div>
                <div class="ctn-wrap">
                    <textarea id="description" class="ui-input" spellcheck="false" placeholder="必须添加拒绝理由，才会被视为拒绝。"></textarea>
                </div>
            </li>
        </ul>
    </div>
<<<<<<< HEAD
    <script type="text/javascript" src="/statics/js/dist/discountReq-manage.js?v=2026102701"></script>
=======
    <script type="text/javascript" src="/statics/js/dist/discountReq-manage.js?v=2026101403"></script>
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
</body>

</html>
