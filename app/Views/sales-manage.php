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
                        <label>单据编号</label>
                        <input type="text" name="billNo" id="billNo" class="ui-input ui-input-dis" value="<?php echo str_no('XS') ?>" disabled />
                    </dd>
                    <dd class="mr10">
                        <label>源单单号</label>
                        <input type="text" name="srcOrderNo" id="srcOrderNo" class="ui-input ui-input-dis" disabled />
                    </dd>
                    <dd>
                        <label>单据日期</label>
                        <input type="text" id="billDate" class="ui-input ui-datepicker-input ui-input-dis" disabled />
                    </dd>
                </dl>
            </div>
            <div class="page-line"></div>
            <div class="mb10 lh30">
                <span><i class="iconfont icon-jinggao f12 red lh30 mr10"></i><span class="gray">如需退货，必须完成发货单，才能在退货单进行按需退货。</span></span>
                <a id="restQtyToNowQty" class="fr ui-btn bg-teal"><i class="iconfont icon-yuanxiajiantou"></i><span>一键将剩余数量填入本次<c class="inout"></c>货数</span></a>
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
            <div class="con-footer">
                <ul class="cf">
                    <li><label>制单人</label><span id="uName"></span></li>
                    <li><label>审核人</label><span id="checkName"></span></li>
                    <li><label>制单时间</label><span id="createTime"></span></li>
                    <li><label>最后编辑</label><span id="modifyTime"></span></li>
                </ul>
            </div>
            <div class="page-line"></div>
            <!--startprint-->
            <div class="pt10 pb20 gray cf" id="logTit"><span>销售<c class="inout"></c>货记录</span></div>
            <div class="grid-wrap mb30 bde b-r-3" id="logGridWrap">
                <table id="logGrid"></table>
                <div id="logPage"></div>
            </div>
            <!--endprint-->
        </div>
        <div id="initCombo" class="dn">
            <input type="text" class="textbox goodsAuto" name="goods" autocomplete="off" />
            <input type="text" class="textbox storageAuto" name="storage" autocomplete="off" />
            <input type="text" class="textbox batchAuto" name="batch" autocomplete="off" />
            <input type="text" class="textbox priceAuto" name="price" autocomplete="off" />
        </div>
        <div id="storageBox" class="shadow target_box dn"></div>
    </div>
    <script type="text/javascript" src="/statics/js/dist/sales-manage.js?v=2026102701"></script>
</body>

</html>
