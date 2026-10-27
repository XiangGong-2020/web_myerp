<?= view('header') ?>
<<<<<<< HEAD
<link rel="stylesheet" type="text/css" href="/statics/css/bills.css?v=2026102701" />
=======
<link rel="stylesheet" type="text/css" href="/statics/css/bills.css?v=2026101403" />
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
</head>

<body>
    <div class="wrapper">
        <div class="bill-tool-top">
            <div id="mark"></div>
            <div id="toolTop" class="fr ui-btn-group"></div>
        </div>
        <div class="bills">
            <div class="con-header">
                <dl class="cf">
                    <dd class="mr10">
                        <label>单据编号</label>
                        <input type="text" name="billNo" id="billNo" class="ui-input ui-input-dis" value="" disabled />
                    </dd>
                    <dd class="mr10">
                        <label>当月起始日</label>
                        <input type="text" value="" id="beginDate" class="ui-input ui-datepicker-input ui-input-dis" autocomplete="off" readonly disabled />
                    </dd>
                    <dd class="mr10">
                        <label>当月结束日</label>
                        <input type="text" value="" id="endDate" class="ui-input ui-datepicker-input ui-input-dis" autocomplete="off" readonly disabled />
                    </dd>
                    <dd class="mr10">
                        <label>出勤规划</label>
                        <input type="number" value="" id="mustDayNum" class="ui-input ui-input-dis" autocomplete="off" readonly disabled />
                        <div class="unit">天</div>
                    </dd>
                    <dd class="mr10">
                        <label>补卡限制</label>
                        <input type="number" value="" id="fillClockinTimes" class="ui-input ui-input-dis" autocomplete="off" readonly disabled />
                        <div class="unit">次</div>
                    </dd>
                </dl>
            </div>
            <div class="page-line"></div>
            <div class="grid-wrap bde b-r-3">
                <table id="grid"></table>
                <div id="page"></div>
            </div>
            <div class="mt10">
                <div class="description">
                    <textarea id="description" class="ui-input" spellcheck="false" placeholder="添加整单备注信息"></textarea>
                    <div id="desBtn" class="ui-btn bg-green dn"><i class="iconfont icon-baocun"></i><span>仅更新备注</span></div>
                </div>
            </div>
            <div class="con-footer">
                <ul class="cf">
                    <li><label>制单人</label><span id="uName"></span></li>
                    <li><label>制单时间</label><span id="createTime"></span></li>
                    <li><label>最后编辑</label><span id="modifyTime"></span></li>
                </ul>
            </div>
        </div>
    </div>
<<<<<<< HEAD
    <script type="text/javascript" src="/statics/js/dist/attendance-manage.js?v=2026102701"></script>
=======
    <script type="text/javascript" src="/statics/js/dist/attendance-manage.js?v=2026101403"></script>
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
</body>

</html>
