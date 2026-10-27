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
                        <label>快递公司</label>
                        <input type="text" name="billFrom" id="billFrom" class="ui-input ui-input-dis" value="" disabled />
                    </dd>
                    <dd id="identifier" class="mr10">
                        <label>单据编号</label>
                        <input type="text" name="billNo" id="billNo" class="ui-input ui-input-dis" value="<?php echo str_no('KD') ?>" readonly />
                    </dd>
                    <dd class="mr10">
                        <label>统计月份</label>
                        <input type="text" id="deliverDate" class="ui-input ui-input-dis" value="" readonly />
                    </dd>
                    <dd class="mr10">
                        <label>对方总额</label>
                        <input type="text" id="amount1" class="ui-input" value="" />
                        <span class="unit">元</span>
                    </dd>
                    <dd class="mr10">
                        <label>我方总额</label>
                        <input type="text" id="amount2" class="ui-input ui-input-dis" value="" readonly />
                        <span class="unit">元</span>
                    </dd>
                    <dd>
                        <label>需付总额</label>
                        <input type="text" id="amount" class="ui-input" value="" />
                        <span class="unit">元</span>
                    </dd>
                </dl>
            </div>
            <div class="mt10">
                <div class="description">
                    <textarea id="description" class="ui-input" spellcheck="false" placeholder="添加整单备注信息"></textarea>
                    <div id="desBtn" class="ui-btn bg-green dn"><i class="iconfont icon-baocun"></i><span>仅更新备注</span></div>
                </div>
            </div>
            <div class="page-line"></div>
            <div id="top1" class="ovh p10 h30 lh30 bde bbn b-r-3-top">
                <span class="fl ml10 fb">可疑运单</span>
                <span class="fl ml10 gray">默认显示所有可疑运单</span>
                <a id="export-top" class="ui-btn bg-teal fr"><i class="iconfont icon-daochu"></i><span>导出</span></a>
            </div>
            <div class="grid-wrap bde b-r-3-bottom">
                <table id="grid"></table>
                <div id="page"></div>
            </div>
            <div id="top2" class="ovh mt20 p10 h30 lh30 bde bbn b-r-3-top">
                <span class="fl ml10 fb">可信运单</span>
                <span class="fl ml10 gray">默认仅显示25元以上可信运单，可自定义条件显示。</span>
                <div class="fr">
                    <input type="number" id="search-weight" class="fl ui-input" placeholder="超过多少重量(kg) ...">
                    <input type="number" id="search-amount" class="fl ml5 ui-input" placeholder="超过多少运费(元) ...">
                    <input type="text" id="search-no" class="fl ml5 ui-input" placeholder="运单号 ...">
                    <div class="fl ml5 ui-btn-group">
                        <a id="search" class="ui-btn bg-teal ml5"><i class="iconfont icon-chaxun"></i><span>查询并显示</span></a>
                        <a id="clear" class="ui-btn bg-teal ml5"><i class="iconfont icon-yincang"></i><span>隐藏</span></a>
                    </div>
                </div>
            </div>
            <div class="grid-wrap bde b-r-3-bottom">
                <table id="grid2"></table>
                <div id="page2"></div>
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
        <div id="initCombo" class="dn"><input type="text" class="textbox nocheckedAuto" name="nochecked" autocomplete="off" /></div>
    </div>
    <script type="text/javascript" src="/statics/js/dist/deliver-manage.js?v=2026102701"></script>
</body>

</html>
