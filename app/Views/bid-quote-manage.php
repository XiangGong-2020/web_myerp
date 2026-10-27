<?= view('header') ?>
<link rel="stylesheet" type="text/css" href="/statics/css/bills.css?v=2026102701" />
<link rel="stylesheet" type="text/css" href="/statics/js/plugins/fSelect/fSelect.css?v=2026102701" />
<style>
    .fs-wrap {
        width: calc(100% - 80px) !important;
    }
</style>
</head>

<body>
    <div class="wrapper">
        <div class="bill-tool-top">
            <div id="mark"></div>
            <div id="toolTop" class="fr ui-btn-group"></div>
        </div>
        <div class="bills">
            <!--startprint1-->
            <div class="con-header">
                <dl class="cf">
                    <dd class="mr10">
                        <label>招标单位</label>
                        <span class="ui-combo-wrap" id="contact" data-type="customer">
                            <input type="text" name="" class="input-txt" autocomplete="off" value="" data-ref="date" />
                            <i class="ui-icon-ellipsis iconfont icon-shenglvehao"></i>
                        </span>
                    </dd>
                    <dd class="mr10">
                        <label>投标单位</label>
                        <span class="ui-combo-wrap" id="ourself">
                            <input type="text" name="" class="input-txt" autocomplete="off" value="" data-ref="date" />
                        </span>
                    </dd>
                    <dd class="mr10">
                        <label>报价截止</label>
                        <input type="text" id="offerEndTime" class="ui-input ui-datepicker-input" value="" placeholder="请选择" autocomplete="off" readonly />
                    </dd>
                    <dd class="mr10">
                        <label>有效报价人</label>
                        <select id="userCombo" class="hidden" multiple="multiple">
                            <optgroup id="normalUser" label="在职员工"></optgroup>
                            <optgroup id="disableUser" label="60天内离职员工"></optgroup>
                            <optgroup id="sysUser" label="特殊"></optgroup>
                        </select>
                    </dd>
                    <dd class="mr10">
                        <label>报价单号</label>
                        <input type="text" name="billNo" id="billNo" class="ui-input ui-input-dis" value="<?php echo str_no('BJ') ?>" disabled />
                    </dd>
                    <dd class="mr10">
                        <label>单据日期</label>
                        <input type="text" id="billDate" class="ui-input ui-datepicker-input ui-input-dis" value="" disabled />
                    </dd>
                    <dd class="mr10">
                        <label>报价总额</label>
                        <input type="number" name="amount" id="amount" class="ui-input ui-input-dis" value="" disabled />
                        <span class="unit">元</span>
                    </dd>
                    <dd>
                        <label>招标来源</label>
                        <input type="text" name="bidSiteName" id="bidSiteName" class="ui-input ui-input-dis" style="width:calc(100vw - 125px)" value="" disabled />
                    </dd>
                    <dd>
                        <label>招标标题</label>
                        <input type="text" name="bidTitle" id="bidTitle" class="ui-input ui-input-dis" style="width:calc(100vw - 125px)" value="" disabled />
                    </dd>
                    <dd>
                        <label>招标链接</label>
                        <input type="url" name="bidUrl" id="bidUrl" class="ui-input ui-input-dis" style="width:calc(100vw - 125px)" value="" disabled />
                    </dd>
                    <dd>
                        <label>联系方式</label>
                        <input type="text" name="contactInfo" id="contactInfo" class="ui-input" style="width:calc(100vw - 125px)" value="" placeholder="请填写" />
                    </dd>
                </dl>
            </div>
            <div class="page-line"></div>
            <!--startprint2-->
            <div id="goodsbtns" class="rel h30 lh30 pb10 ovh">
                <div id="excel-btns" class="rel fr">
                    <a id="breakshow" class="ui-btn mr5 bg-teal"><i class="iconfont icon-biaoge"></i><span>显示详细表格</span></a>
                    <a id="download-excel" href="/data/template/预算报价清单模板.xlsx" target="_blank" class="ui-btn mr5 bg-teal"><i class="iconfont icon-xiazai"></i><span>下载预算报价清单模板</span></a>
                    <a id="add-excel" class="ui-btn mr5 bg-teal"><i class="iconfont icon-daoru"></i><span>导入预算报价清单</span></a>
                    <a id="exportInfoGrid" class="ui-btn mr5 bg-teal"><i class="iconfont icon-daochu"></i><span>导出最终报价清单</span><b>拟定标书时可用</b></a>
                </div>
            </div>
            <div class="grid-wrap bde b-r-3">
                <table id="grid"></table>
                <div id="page"></div>
            </div>
            <!--endprint2-->
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
                    <li><label>制单时间</label><span id="createTime"></span></li>
                    <li><label>最后编辑</label><span id="modifyTime"></span></li>
                </ul>
            </div>
            <!--endprint1-->
        </div>
        <div id="initCombo" class="dn">
            <input type="text" class="textbox goodsAuto" name="goods" autocomplete="off" />
        </div>
    </div>
    <script type="text/javascript" src="/statics/js/plugins/uploader/jquery.uploader.js?v=2026102701"></script>
    <script type="text/javascript" src="/statics/js/plugins/fSelect/fSelect.js?v=2026102701"></script>
    <script type="text/javascript" src="/statics/js/dist/bid-quote-manage.js?v=2026102701"></script>
</body>

</html>
