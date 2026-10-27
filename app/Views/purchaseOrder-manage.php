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
                    <dd id="classes" class="pct100">
                        <label class="radio"><input type="radio" name="classes" value="101101">采购</label>
                        <label class="radio"><input type="radio" name="classes" value="101102">直发</label>
                        <span id="srcOcNoCon" class="dn">
                            <label>关联销售单号</label>
                            <input type="text" id="srcOcNo" class="billNo ui-input" value="">
                        </span>
                    </dd>
                    <dd class="mr10">
                        <label>单据编号</label>
                        <input type="text" name="billNo" id="billNo" class="ui-input ui-input-dis" value="<?php echo str_no('CD') ?>" disabled />
                    </dd>
                    <dd class="mr10">
                        <label>单据日期</label>
                        <input type="text" value="" id="billDate" class="ui-input ui-datepicker-input ui-input-dis" disabled />
                    </dd>
                    <dd class="mr10">
                        <label>交货限期</label>
                        <input type="text" value="" id="deliveryDate" class="ui-input ui-datepicker-input" autocomplete="off" readonly />
                    </dd>
                    <dd class="mr10">
                        <label>商家</label>
                        <span class="ui-combo-wrap" id="contact" data-type="supplier">
                            <input type="text" class="input-txt" autocomplete="off" value="" data-ref="date" />
                            <i class="ui-icon-ellipsis iconfont icon-shenglvehao"></i>
                        </span>
                    </dd>
                    <dd class="mr10">
                        <label>采购方</label>
                        <span class="ui-combo-wrap" id="ourself">
                            <input type="text" class="input-txt" autocomplete="off" value="" data-ref="date" />
                        </span>
                    </dd>
                    <dd class="mr10">
                        <label>整单优惠率</label>
                        <input type="text" id="disRate" class="ui-input" data-ref="disAmount" />
                        <div class="unit">%</div>
                    </dd>
                    <dd class="mr10">
                        <label>整单优惠额</label>
                        <input type="text" id="disAmount" class="ui-input" data-ref="rpAmount" />
                        <div class="unit">元</div>
                    </dd>
                    <dd class="mr10">
                        <label>整单总运费</label>
                        <input type="text" id="deliverAmount" class="ui-input" data-ref="amount" />
                        <div class="unit">元</div>
                    </dd>
                    <dd class="mr10">
                        <label>应付总金额</label>
                        <input type="text" id="amount" class="ui-input ui-input-dis" data-ref="disRate" disabled />
                        <div class="unit">元</div>
                    </dd>
                </dl>
            </div>
            <div class="page-line"></div>
            <div class="rel h30 lh30 pb10 ovh">
                <div class="rel fr ui-btn-group">
                    <a id="gotoInventoryHistory" class="ui-btn bg-teal"><i class="iconfont icon-chaxun "></i><span>查指定产品近一年消耗量</span><b>注意：当某些模糊产品采购时（如JS-56-273套件图纸），实际消耗量需查询对应套件JS-56-273的近一年消耗量。</b></a>
                </div>
            </div>
            <div class="grid-wrap bde b-r-3">
                <table id="grid"></table>
            </div>
            <div class="mt10">
                <div class="description">
                    <textarea id="description" class="ui-input" spellcheck="false" placeholder="备注信息：输入任何有用的信息，例如：快递物流单号，微信小程序收货时，可直接扫描快递单。"></textarea>
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
            <input type="text" class="textbox goodsAuto" name="goods" autocomplete="off" readonly />
            <input type="text" class="textbox storageAuto" name="storage" autocomplete="off" />
            <input type="text" class="textbox batchAuto" name="batch" autocomplete="off" />
            <input type="text" class="textbox priceAuto" name="price" autocomplete="off" />
            <input type="text" class="textbox qtyAuto" name="qty" autocomplete="off" />
        </div>
        <div id="storageBox" class="shadow target_box dn"></div>
    </div>
<<<<<<< HEAD
    <script src="/statics/js/plugins/uploader/jquery.uploader.js?v=2026102701"></script>
    <script type="text/javascript" src="/statics/js/dist/purchaseOrder-manage.js?v=2026102701"></script>
=======
    <script src="/statics/js/plugins/uploader/jquery.uploader.js?v=2026101403"></script>
    <script type="text/javascript" src="/statics/js/dist/purchaseOrder-manage.js?v=2026101403"></script>
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
</body>

</html>
