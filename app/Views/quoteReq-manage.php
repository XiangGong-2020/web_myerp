<?= view('header') ?>
<link rel="stylesheet" type="text/css" href="/statics/css/manage.css?v=2026102701" />
</head>

<body>
    <div class="manage-wrapper">
        <form id="manage-form" action="">
            <ul class="mod-form-rows">
                <li class="row-item">
                    <div class="label-wrap"><label for="title">报审标题</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input" style="width:calc(100% - 92px);" name="title" id="title" placeholder="添加简要的报审描述作为标题" /></div>
                </li>
                <li class="row-item">
                    <div class="fl w80 lh28 ti10 gray bg-white bde b-r-3-top mb-1">报审详情</div><span id="createTime" class="fl ml10 lh28 gray"></span>
                    <div class="ctn-wrap">
                        <textarea id="content" class="ui-input" spellcheck="false" placeholder="添加报审的说明信息，越详细越好（一般定制产品的报价需要提交报价审批）。"></textarea>
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
    <script type="text/javascript" src="/statics/js/dist/quoteReq-manage.js?v=2026102701"></script>
</body>

</html>
