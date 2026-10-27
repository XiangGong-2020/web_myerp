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
                <li class="row-item item-pct33">
                    <div class="label-wrap"><label for="name">名称</label><span class="must">●</span></div>
                    <div class="ctn-wrap rel"><input type="text" value="" class="ui-input" name="name" id="name" /></div>
                </li>
                <li class="row-item item-pct33">
                    <div class="label-wrap"><label for="category">类别</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><span id="category"></span></div>
                </li>
                <li class="row-item item-pct33">
                    <div class="label-wrap"><label for="cLevel">等级</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><span id="cLevel"></span></div>
                </li>
                <li class="row-item item-pct33">
                    <div class="label-wrap"><label for="amount">预收款余额</label><span class="must">●</span></div>
                    <div class="ctn-wrap">
                        <input type="number" step="0.01" value="" class="ui-input" name="amount" id="amount" />
                        <span class="unit">元</span>
                    </div>
                </li>
                <li class="row-item item-pct33">
                    <div class="label-wrap"><label for="beginDate">建档日期</label><span class="must">●</span></div>
                    <div class="ctn-wrap rel"><input type="text" value="" class="ui-input ui-datepicker-input" name="beginDate" id="beginDate" autocomplete="off" readonly /></div>
                </li>
                <li class="row-item item-pct33">
                    <div class="label-wrap"><label for="comeFrom">来源渠道</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input ui-input-dis" name="comeFrom" id="comeFrom" autocomplete="off" readonly /></div>
                </li>
            </ul>
            <div class="mt10 ml10 mr10">
                <h3 class="dn">联系方式</h3>
                <div class="grid-wrap bde b-r-3">
                    <table id="grid"></table>
                    <div id="page"></div>
                </div>
            </div>
            <ul class="mod-form-rows">
                <li class="row-item">
                    <div class="w80 lh28 ti10 gray bg-white bde b-r-3-top mb-1">备注信息</div>
                    <div class="ctn-wrap">
                        <textarea id="description" class="ui-input" spellcheck="false" placeholder="添加备注信息"></textarea>
                    </div>
                </li>
            </ul>
            <div class="mt10 ml10 mr10">
                <div class="upfile">
                    <div class="upfile-con b-r-3-left">
                        <label class="upfile-title">上传资料</label>
                        <button type="button" id="upFile" class="choosefile iconfont icon-shangchuan"></button>
                    </div>
                    <div id="files" class="files b-r-3-right"></div>
                    <div id="queue"></div>
                </div>
            </div>
        </form>
        <div class="hideFile dn">
            <input type="text" class="textbox address" name="address" id="address" autocomplete="off" readonly />
        </div>
    </div>
<<<<<<< HEAD
    <script type="text/javascript" src="/statics/js/plugins/uploader/jquery.uploader.js?v=2026102701"></script>
    <script type="text/javascript" src="/statics/js/dist/customer-manage.js?v=2026102701"></script>
=======
    <script type="text/javascript" src="/statics/js/plugins/uploader/jquery.uploader.js?v=2026101403"></script>
    <script type="text/javascript" src="/statics/js/dist/customer-manage.js?v=2026101403"></script>
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
</body>

</html>
