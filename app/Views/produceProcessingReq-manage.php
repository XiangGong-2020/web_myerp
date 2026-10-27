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
                <li class="row-item item-pct20">
                    <div class="label-wrap"><label for="lastDate">交付限期</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input ui-datepicker-input" name="lastDate" id="lastDate" autocomplete="off" readonly /></div>
                </li>
                <li class="row-item item-pct60">
                    <div class="label-wrap"><label for="projectName">申请项目</label><span class="must">●</span></div>
                    <div class="ctn-wrap" id="project">
                        <input type="text" name="projectName" id="projectName" class="ui-input def" style="width:calc(100% - 92px);" autocomplete="off" value="" readonly="readonly" required />
                        <i class="ui-icon-ellipsis iconfont icon-shenglvehao"></i>
                    </div>
                </li>
                <li class="row-item item-pct20">
                    <div class="label-wrap"><label for="qty">申请产量</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><input type="number" name="qty" id="qty" class="ui-input" autocomplete="off" value="" required /></div>
                </li>
                <li class="row-item">
                    <div class="fl w80 lh28 ti10 gray bg-white bde b-r-3-top mb-1">附加说明</div><span id="createTime" class="fl ml10 lh28 gray"></span>
                    <div class="ctn-wrap">
                        <textarea id="content" class="ui-input" spellcheck="false" placeholder="添加申请的附加说明信息，越详细越好。"></textarea>
                    </div>
                </li>
                <li class="row-item">
                    <div class="w80 lh28 ti10 gray bg-white bde b-r-3-top mb-1">拒绝理由</div>
                    <div class="ctn-wrap">
                        <textarea id="description" class="ui-input" spellcheck="false" placeholder="必须添加拒绝理由，才会被视为拒绝。"></textarea>
                    </div>
                </li>
            </ul>
        </form>
    </div>
<<<<<<< HEAD
    <script type="text/javascript" src="/statics/js/dist/produceProcessingReq-manage.js?v=2026102701"></script>
=======
    <script type="text/javascript" src="/statics/js/dist/produceProcessingReq-manage.js?v=2026101403"></script>
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
</body>

</html>
