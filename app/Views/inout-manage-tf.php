<?= view('header') ?>
<link rel="stylesheet" type="text/css" href="/statics/css/bills.css?v=2026102701" />
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
                    <dd id="identifier" class="mr10">
                        <label>单据编号</label>
                        <input type="text" name="billNo" id="billNo" class="ui-input ui-input-dis" value="<?php echo str_no('DB') ?>" disabled />
                    </dd>
                    <dd>
                        <label>单据日期</label>
                        <input type="text" id="billDate" class="ui-input ui-datepicker-input ui-input-dis" disabled />
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
                    <li><label>审核人</label><span id="checkName"></span></li>
                    <li><label>制单时间</label><span id="createTime"></span></li>
                    <li><label>最后编辑</label><span id="modifyTime"></span></li>
                </ul>
            </div>
        </div>
        <div id="initCombo" class="dn">
            <input type="text" class="textbox goodsAuto" name="goods" autocomplete="off" />
            <input type="text" class="textbox outStorageAuto" name="storage" autocomplete="off" />
            <input type="text" class="textbox inStorageAuto" name="storageB" autocomplete="off" />
            <input type="text" class="textbox batchAuto" name="batch" autocomplete="off" />
        </div>
        <div id="storageBox" class="shadow target_box dn"></div>
    </div>
    <script type="text/javascript" src="/statics/js/dist/inout-manage-tf.js?v=2026102701"></script>
</body>

</html>
