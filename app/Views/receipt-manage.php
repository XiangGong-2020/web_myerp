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
                        <input type="text" name="billNo" id="billNo" class="ui-input ui-input-dis" value="<?php echo str_no('SK') ?>" disabled />
                    </dd>
                    <dd class="mr10">
                        <label>单据日期</label>
                        <input type="text" id="billDate" class="ui-input ui-datepicker-input ui-input-dis" disabled />
                    </dd>
                    <dd class="mr10">
                        <label>源单单号</label>
                        <input type="text" name="srcNo" id="srcNo" class="ui-input ui-input-dis" disabled />
                    </dd>
                    <dd class="mr10">
                        <label>收款对象</label>
                        <span class="ui-combo-wrap" id="contact" data-type="all">
                            <input type="text" name="" class="input-txt" autocomplete="off" value="" data-ref="date" />
                            <i class="ui-icon-ellipsis iconfont icon-shenglvehao"></i>
                        </span>
                    </dd>
                    <dd class="mr10">
                        <label>应收金额</label>
                        <input type="text" name="amount" id="amount" class="ui-input" />
                    </dd>
                    <dd class="mr10">
                        <label>已收金额</label>
                        <input type="text" name="cleared" id="cleared" class="ui-input ui-input-dis" disabled />
                    </dd>
                </dl>
            </div>
            <div class="page-line"></div>
            <div class="ovh">
                <div class="dn">
                    <div class="grid-wrap bde brn b-r-3-left">
                        <table id="gridGoods"></table>
                    </div>
                </div>
                <div>
                    <div class="grid-wrap bde b-r-3">
                        <table id="grid"></table>
                    </div>
                </div>
            </div>
            <div class="mt10">
                <div class="description">
                    <textarea id="description" class="ui-input" spellcheck="false" placeholder="添加整单备注信息"></textarea>
                    <div id="desBtn" class="ui-btn bg-green dn"><i class="iconfont icon-baocun"></i><span>仅更新备注</span></div>
                </div>
            </div>
            <div class="upfile mt10">
                <div class="upfile-con b-r-3-left">
                    <label class="upfile-title">上传资料</label>
                    <button type="button" id="upFile" class="choosefile iconfont icon-shangchuan"></button>
                </div>
                <div id="files" class="files b-r-3-right"></div>
                <div id="queue"></div>
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
            <input type="text" class="textbox incometypeAuto" name="incometype" autocomplete="off" />
            <input type="text" class="textbox companyAccountAuto" name="companyAccount" autocomplete="off" />
            <input type="text" class="textbox settlementtypeAuto" name="settlementtype" autocomplete="off" />
        </div>
    </div>
<<<<<<< HEAD
    <script type="text/javascript" src="/statics/js/plugins/uploader/jquery.uploader.js?v=2026102701"></script>
    <script type="text/javascript" src="/statics/js/dist/receipt-manage.js?v=2026102701"></script>
=======
    <script type="text/javascript" src="/statics/js/plugins/uploader/jquery.uploader.js?v=2026101403"></script>
    <script type="text/javascript" src="/statics/js/dist/receipt-manage.js?v=2026101403"></script>
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
</body>

</html>
