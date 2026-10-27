<?= view('header') ?>
<<<<<<< HEAD
<link rel="stylesheet" type="text/css" href="/statics/css/manage.css?v=2026102701" />
<link rel="stylesheet" type="text/css" href="/statics/js/plugins/fSelect/fSelect.css?v=2026102701" />
=======
<link rel="stylesheet" type="text/css" href="/statics/css/manage.css?v=2026101403" />
<link rel="stylesheet" type="text/css" href="/statics/js/plugins/fSelect/fSelect.css?v=2026101403" />
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
</head>

<body>
    <div class="manage-wrapper">
        <form id="manage-form" action="">
            <ul class="mod-form-rows">
                <li class="row-item item-pct25">
                    <div class="label-wrap"><label for="type">记录类别</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><span id="type"></span></div>
                </li>
                <li class="row-item item-pct25">
                    <div class="label-wrap"><label for="targetDate">发生日期</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><input type="text" value="" name="targetDate" id="targetDate" class="ui-input ui-datepicker-input" autocomplete="off" readonly /></div>
                </li>
                <li class="row-item item-pct25">
                    <div class="label-wrap"><label for="targetName">目标员工</label><span class="must">●</span></div>
                    <div class="ctn-wrap">
                        <select id="targetNameCombo" class="hidden" multiple="multiple">
                            <optgroup id="normalUser" label="在职员工"></optgroup>
                            <optgroup id="disableUser" label="60天内离职员工"></optgroup>
                        </select>
                    </div>
                </li>
                <li class="row-item item-pct25 dn">
                    <div class="label-wrap"><label id="targetAmountName" for="targetAmount">损失金额</label></div>
                    <div class="ctn-wrap">
                        <input type="number" value="" class="ui-input" name="targetAmount" id="targetAmount">
                        <span class="unit">元</span>
                    </div>
                </li>
                <li class="row-item">
                    <div class="fl w80 lh28 ti10 gray bg-white bde b-r-3-top mb-1">详情信息<span class="must">●</span></div>
                    <div class="ctn-wrap">
                        <textarea name="content" id="content" class="ui-input" spellcheck="false" style="height:250px;" placeholder="添加详情信息"></textarea>
                    </div>
                </li>
            </ul>
        </form>
    </div>
<<<<<<< HEAD
    <script type="text/javascript" src="/statics/js/plugins/fSelect/fSelect.js?v=2026102701"></script>
    <script type="text/javascript" src="/statics/js/dist/work-record-manage.js?v=2026102701"></script>
=======
    <script type="text/javascript" src="/statics/js/plugins/fSelect/fSelect.js?v=2026101403"></script>
    <script type="text/javascript" src="/statics/js/dist/work-record-manage.js?v=2026101403"></script>
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
</body>

</html>
