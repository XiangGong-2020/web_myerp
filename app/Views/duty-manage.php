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
                <li class="row-item item-pct50">
                    <div class="label-wrap"><label for="targetName">转交对象</label></div>
                    <div class="ctn-wrap">
                        <select id="targetNameCombo" class="hidden" multiple="multiple">
                            <optgroup id="department" label="转交相关部门"></optgroup>
                            <optgroup id="normalUser" label="转交在职员工"></optgroup>
                        </select>
                    </div>
                </li>
                <li class="row-item item-pct16">
                    <div class="label-wrap"><label for="level">紧急程度</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><span id="level"></span></div>
                </li>
                <li class="row-item item-pct16">
                    <div class="label-wrap"><label for="lastDate">处理限期</label></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input ui-datepicker-input" name="lastDate" id="lastDate" autocomplete="off" readonly /></div>
                </li>
                <li class="row-item item-pct16">
                    <div class="label-wrap"><label for="isDisable">处理状态</label></div>
                    <div class="ctn-wrap"><span id="isDisable"></span></div>
                </li>
                <li class="row-item">
                    <div class="w80 lh28 ti10 gray bg-white bde b-r-3-top mb-1">工作内容</div>
                    <div class="ctn-wrap">
                        <textarea id="content" name="content" class="ui-input" style="height:100px;" spellcheck="false" placeholder="添加工作内容"></textarea>
                    </div>
                </li>
                <li class="row-item">
                    <div class="w80 lh28 ti10 gray bg-white bde b-r-3-top mb-1">处理回执</div>
                    <div class="ctn-wrap">
                        <textarea id="backinfo" name="backinfo" class="ui-input" style="height:100px;" spellcheck="false" placeholder="添加处理回执"></textarea>
                    </div>
                </li>
            </ul>
        </form>
    </div>
<<<<<<< HEAD
    <script type="text/javascript" src="/statics/js/plugins/fSelect/fSelect.js?v=2026102701"></script>
    <script type="text/javascript" src="/statics/js/dist/duty-manage.js?v=2026102701"></script>
=======
    <script type="text/javascript" src="/statics/js/plugins/fSelect/fSelect.js?v=2026101403"></script>
    <script type="text/javascript" src="/statics/js/dist/duty-manage.js?v=2026101403"></script>
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
</body>

</html>
