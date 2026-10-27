<?= view('header') ?>
<link rel="stylesheet" type="text/css" href="/statics/css/bills.css?v=2026102701" />
<link rel="stylesheet" type="text/css" href="/statics/js/plugins/fSelect/fSelect.css?v=2026102701" />
</head>

<body>
    <div class="wrapper">
        <div class="bill-tool-top">
            <div id="mark"></div>
            <div class="mod-warning">
                <i class="iconfont icon-jinggao"></i>
                <span class="es">特殊采购单仅针对中标合同（外部产品）的采购，自有产品必须严格使用采购订单进行采购（否则无法处理库存变动）</span>
            </div>
            <div id="toolTop" class="fr ui-btn-group"></div>
        </div>
        <div class="bills">
            <!--startprint1-->
            <div class="con-header">
                <dl class="cf">
                    <dd class="mr10 come-from">
                        <label>单据来源</label>
                        <input type="text" name="billFrom" id="billFrom" class="ui-input ui-input-dis" value="" disabled />
                    </dd>
                    <div class="cf"></div>
                    <dd class="mr10">
                        <label>单据编号</label>
                        <input type="text" name="billNo" id="billNo" class="ui-input ui-input-dis" value="<?php echo str_no('CH') ?>" disabled />
                    </dd>
                    <dd class="mr10">
                        <label>单据日期</label>
                        <input type="text" id="billDate" class="ui-input ui-datepicker-input ui-input-dis" value="" disabled />
                    </dd>
                    <dd class="mr10">
                        <label>交货限期</label>
                        <input type="text" id="endDate" class="ui-input ui-datepicker-input" value="" autocomplete="off" readonly />
                    </dd>
                    <dd class="mr10">
                        <label>买方(我方)</label>
                        <span class="ui-combo-wrap" id="ourself">
                            <input type="text" name="" class="input-txt" autocomplete="off" value="" data-ref="date" />
                        </span>
                    </dd>
                    <dd class="mr10">
                        <label>卖方(对方)</label>
                        <span class="ui-combo-wrap" id="contact" data-type="supplier">
                            <input type="text" name="" class="input-txt" autocomplete="off" value="" data-ref="date" />
                            <i class="ui-icon-ellipsis iconfont icon-shenglvehao"></i>
                        </span>
                    </dd>
                    <dd class="mr10">
                        <label>应付总额</label>
                        <input type="number" name="amount" id="amount" class="ui-input" value="" />
                        <span class="unit">元</span>
                    </dd>
                </dl>
            </div>
            <div class="page-line"></div>
            <!--startprint2-->
            <div class="rel h30 lh30 pb10 ovh">
                <span id="reqNotice" class="dn"><i class="iconfont icon-jinggao f12 red lh30 mr10"></i><span class="gray">提交前应事先落实每产品的具体规格型号等要求，并检查各项关键数据，填写无误方可提交，尽可能从源头减少后续进程的麻烦，一旦采购开始报价，不允许再随意编辑申请单。</span></span>
                <div id="goodsbtns" class="rel fr h30 lh30">
                    <span id="quoteOper" class="fl dn">
                        <span class="db fl mr-2 h28 lh28 pl10 pr10 gray bde b-r-3-left def">按被采纳报价人筛选显示</span><span id="combo1"></span>
                    </span>
                    <div id="excel-btns" class="rel fr ml10 ui-btn-group">
                        <a id="breakshow" class="ui-btn bg-teal"><i class="iconfont icon-biaoge"></i><span>显示详细表格</span></a>
                        <a id="showQuoteHis" class="ui-btn bg-teal dn"><i class="iconfont icon-xianshi"></i><span>查看报价记录</span></a>
                        <a id="exportGrid" class="ui-btn bg-teal"><i class="iconfont icon-daochu"></i><span>导出收货清单</span></a>
                        <a id="exportGridAll" class="ui-btn bg-teal dn"><i class="iconfont icon-daochu"></i><span>导出清单报价详情</span><b>仅管理员</b></a>
                        <a id="printGrid" class="ui-btn bg-teal"><i class="iconfont icon-dayin"></i><span>打印本页清单</span></a>
                    </div>
                    <span id="gridNo" class="fl dn">特殊采购清单，单据编号：<span id="repeatNo"></span></span>
                </div>
                <div id="goodsbtns2" class="dn rel fr h30 lh30">
                    <div id="excel-btns" class="rel fr ui-btn-group">
                        <a href="/data/template/特采申请清单模板.xlsx" target="_blank" class="ui-btn bg-teal">
                            <i class="iconfont icon-xiazai f12"></i><span>下载特采申请清单模板</span>
                        </a>
                        <a id="add-excel" class="ui-btn bg-teal"><i class="iconfont icon-daoru"></i><span>导入特采申请清单</span></a>
                    </div>
                </div>
            </div>
            <div class="grid-wrap bde b-r-3">
                <table id="grid"></table>
                <div id="page"></div>
            </div>
            <!--endprint2-->
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
            <!--endprint1-->
        </div>
    </div>
    <script type="text/javascript" src="/statics/js/plugins/uploader/jquery.uploader.js?v=2026102701"></script>
    <script type="text/javascript" src="/statics/js/plugins/fSelect/fSelect.js?v=2026102701"></script>
    <script type="text/javascript" src="/statics/js/dist/purchaseContract-manage.js?v=2026102701"></script>
</body>

</html>
