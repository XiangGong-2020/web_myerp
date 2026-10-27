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
                    <dd class="mr10">
                        <label>销售渠道</label>
                        <input type="text" name="billFrom" id="billFrom" class="ui-input ui-input-dis" value="" disabled />
                    </dd>
                    <dd class="mr10">
                        <label>单据编号</label>
                        <input type="text" name="billNo" id="billNo" class="ui-input ui-input-dis" value="<?php echo str_no('XD') ?>" disabled />
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
                        <label>客户</label>
                        <span class="ui-combo-wrap" id="contact" data-type="customer">
                            <input type="text" name="" class="input-txt" autocomplete="off" value="" data-ref="date" />
                            <i class="ui-icon-ellipsis iconfont icon-shenglvehao"></i>
                        </span>
                    </dd>
                    <dd class="mr10">
                        <label>销售方</label>
                        <span class="ui-combo-wrap" id="ourself">
                            <input type="text" name="" class="input-txt" autocomplete="off" value="" data-ref="date" />
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
                        <label>应收总金额</label>
                        <input type="text" id="amount" class="ui-input ui-input-dis" data-ref="disRate" disabled />
                        <div class="unit">元</div>
                    </dd>
                </dl>
            </div>
            <div class="page-line"></div>
            <div id="goodsbtns" class="rel h30 lh30 pb10 ovh">
                <div id="excel-btns" class="rel fr ui-btn-group">
                    <a id="breakshow" class="ui-btn bg-teal"><i class="iconfont icon-biaoge"></i><span>显示详细表格</span></a>
                    <a id="exportGrid" class="ui-btn bg-teal"><i class="iconfont icon-daochu"></i><span>导出清单</span><b>用于外部开票</b></a>
                </div>
            </div>
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
                    <li><label>制单人：</label><span id="uName"></span></li>
                    <li><label>审核人：</label><span id="checkName"></span></li>
                    <li><label>制单时间：</label><span id="createTime"></span></li>
                    <li><label>最后编辑：</label><span id="modifyTime"></span></li>
                </ul>
            </div>
        </div>
        <div id="initCombo" class="dn">
            <input type="text" class="textbox goodsAuto" name="goods" autocomplete="off" />
            <input type="text" class="textbox storageAuto" name="storage" autocomplete="off" />
            <input type="text" class="textbox batchAuto" name="batch" autocomplete="off" />
            <input type="text" class="textbox priceAuto" name="price" autocomplete="off" />
        </div>
        <div id="storageBox" class="shadow target_box dn"></div>
        <div id="taxRateBox" class="shadow target_box dn">
            <input id="taxRate" type="number" value=0 style="width:50px;" />
            <button id="subTaxRate" style="margin-left:5px;width:40px;">确定</button>
        </div>
    </div>
    <script type="text/javascript" src="/statics/js/plugins/uploader/jquery.uploader.js?v=2026102701"></script>
    <script type="text/javascript" src="/statics/js/dist/salesOrder-manage.js?v=2026102701"></script>
</body>

</html>
