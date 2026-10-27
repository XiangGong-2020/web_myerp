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
                    <div class="label-wrap"><label for="type">选择类别</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><span id="type"></span></div>
                </li>
                <li class="row-item item-pct80 h30 lh30 night bg-silver b-r-3">
                    <span class="ml10">
                        提交后：超过1小时禁止删除、类别禁止修改；目标与总结首次提交后：超过修改期限禁止修改（日 / 周 / 月 / 年 类别对应修改期限分别为 2小时 / 2天 / 20天 / 60天）。
                    </span>
                </li>
                <li class="row-item">
                    <div class="fl w80 lh28 ti10 gray bg-white bde b-r-3-top mb-1">计划目标<span class="must">●</span></div>
                    <div id="targetNotice" class="fl ml10 lh28 gray"></div>
                    <div class="ctn-wrap">
                        <textarea name="target" id="target" class="ui-input" style="height:150px;white-space: pre-wrap !important;" spellcheck="false" placeholder="填写管理计划目标内容"></textarea>
                    </div>
                </li>
                <li class="row-item">
                    <div class="fl w80 lh28 ti10 gray bg-white bde b-r-3-top mb-1">计划总结</div>
                    <div id="summaryNotice" class="fl ml10 lh28 gray"></div>
                    <div class="ctn-wrap">
                        <textarea name="summary" id="summary" class="ui-input" style="height:150px;white-space: pre-wrap !important;" spellcheck="false" placeholder="填写管理计划总结内容"></textarea>
                    </div>
                </li>
            </ul>
        </form>
    </div>
<<<<<<< HEAD
    <script type="text/javascript" src="/statics/js/dist/manage-plan-manage.js?v=2026102701"></script>
=======
    <script type="text/javascript" src="/statics/js/dist/manage-plan-manage.js?v=2026101403"></script>
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
</body>

</html>
