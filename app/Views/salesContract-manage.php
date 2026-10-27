<?= view('header') ?>
<<<<<<< HEAD
<link rel="stylesheet" type="text/css" href="/statics/css/bills.css?v=2026102701" />
<link rel="stylesheet" type="text/css" href="/statics/js/plugins/fSelect/fSelect.css?v=2026102701" />
=======
<link rel="stylesheet" type="text/css" href="/statics/css/bills.css?v=2026101403" />
<link rel="stylesheet" type="text/css" href="/statics/js/plugins/fSelect/fSelect.css?v=2026101403" />
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
</head>

<body>
    <div class="wrapper">
        <div class="bill-tool-top">
            <div id="mark"></div>
            <div id="signmark"></div>
            <div class="mod-warning">
                <i class="iconfont icon-jinggao"></i>
                <span class="es">特殊销售单原则上仅针对中标合同，如含自有产品请点选或准确填写产品编码（否则无法处理库存变动）</span>
            </div>
            <div id="toolTop" class="fr ui-btn-group"></div>
        </div>
        <div class="bills">
            <!--startprint1-->
            <div class="con-header">
                <dl class="cf">
                    <dd class="mr10">
                        <label>单据编号</label>
                        <input type="text" name="billNo" id="billNo" class="ui-input ui-input-dis" value="<?php echo str_no('XH') ?>" disabled />
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
                        <label>买方(对方)</label>
                        <span class="ui-combo-wrap" id="contact" data-type="customer">
                            <input type="text" name="" class="input-txt" autocomplete="off" value="" data-ref="date" />
                            <i class="ui-icon-ellipsis iconfont icon-shenglvehao"></i>
                        </span>
                    </dd>
                    <dd class="mr10">
                        <label>卖方(我方)</label>
                        <span class="ui-combo-wrap" id="ourself">
                            <input type="text" name="" class="input-txt" autocomplete="off" value="" data-ref="date" />
                        </span>
                    </dd>
                    <dd class="mr10">
                        <label>销售人</label>
                        <select id="userCombo" class="hidden" multiple="multiple">
                            <optgroup id="normalUser" label="在职员工"></optgroup>
                            <optgroup id="disableUser" label="60天内离职员工"></optgroup>
                            <optgroup id="sysUser" label="特殊"></optgroup>
                        </select>
                    </dd>
                    <dd class="mr10">
                        <label>货款金额</label>
                        <input type="number" name="amount" id="amount" class="ui-input" value="" />
                        <span class="unit">元</span>
                    </dd>
                    <dd class="mr10">
                        <label>保证金</label>
                        <span class="ui-combo-wrap" id="zbjType">
                            <input type="text" name="" class="input-txt" autocomplete="off" value="" data-ref="date" />
                        </span>
                        <input type="number" name="zbjAmount" id="zbjAmount" class="ui-input" style="width:70px" value="" />
                        <span class="unit">元</span>
                    </dd>
                </dl>
            </div>
            <div class="page-line"></div>
            <div id="goodsbtns" class="rel h30 lh30 pb10 ovh">
                <div id="excel-btns" class="rel fr ui-btn-group">
                    <a id="breakshow" class="ui-btn bg-teal"><i class="iconfont icon-biaoge"></i><span>显示详细表格</span></a>
                    <a href="/data/template/特殊销售清单模板.xlsx" target="_blank" class="ui-btn bg-teal"><i class="iconfont icon-xiazai"></i><span>下载特殊销售清单模板</span></a>
                    <a id="add-excel" class="ui-btn bg-teal"><i class="iconfont icon-daoru"></i><span>导入特殊销售清单</span></a>
                    <a id="exportGrid" class="ui-btn bg-teal"><i class="iconfont icon-daochu"></i><span>导出特殊销售清单</span></a>
                    <a id="allToReq" class="ui-btn bg-teal"><i class="iconfont icon-shengcheng"></i><span>发起特采申请</span><b>全部无编码产品</b></a>
                    <a id="selectedToReq" class="ui-btn bg-teal"><i class="iconfont icon-shengcheng"></i><span>发起特采申请</span><b>勾选的产品</b></a>
                </div>
            </div>
            <div id="reqNo" class="dn mb10 p10 bg-rgba1 bde b-r-3"></div>
            <!--startprint2-->
            <div id="gridNo" class="pt10 pb20 gray cf dn"></div>
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
                    <li><label>制单人：</label><span id="uName"></span></li>
                    <li><label>审核人：</label><span id="checkName"></span></li>
                    <li><label>制单时间：</label><span id="createTime"></span></li>
                    <li><label>最后编辑：</label><span id="modifyTime"></span></li>
                </ul>
            </div>
            <!--endprint1-->
        </div>
        <div id="initCombo" class="dn">
            <input type="text" class="textbox goodsAuto" name="goods" autocomplete="off" />
        </div>
    </div>
<<<<<<< HEAD
    <script type="text/javascript" src="/statics/js/plugins/uploader/jquery.uploader.js?v=2026102701"></script>
    <script type="text/javascript" src="/statics/js/plugins/fSelect/fSelect.js?v=2026102701"></script>
    <script type="text/javascript" src="/statics/js/dist/salesContract-manage.js?v=2026102701"></script>
=======
    <script type="text/javascript" src="/statics/js/plugins/uploader/jquery.uploader.js?v=2026101403"></script>
    <script type="text/javascript" src="/statics/js/plugins/fSelect/fSelect.js?v=2026101403"></script>
    <script type="text/javascript" src="/statics/js/dist/salesContract-manage.js?v=2026101403"></script>
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
</body>

</html>
