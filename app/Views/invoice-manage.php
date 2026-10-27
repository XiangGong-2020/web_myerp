<?= view('header') ?>
<<<<<<< HEAD
<link rel="stylesheet" type="text/css" href="/statics/css/manage.css?v=2026102701" />
=======
<link rel="stylesheet" type="text/css" href="/statics/css/manage.css?v=2026101403" />
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
<style>
    .billno-con {
        margin-top: 30px;
        padding: 0 10px 10px 0;
        width: calc(100vw - 32px);
        height: 300px;
        border: 1px solid #E1E2E3;
        border-radius: 3px;
        background-color: #f5f5f5;
        overflow-y: auto;
    }

    .billno-item {
        position: relative;
        float: left;
        margin: 10px 0 0 10px;
        background-color: #fff;
        border: 1px solid #ccc;
    }

    .billno-no {
        padding: 5px;
        width: 180px;
        height: 18px;
        line-height: 18px;
        vertical-align: middle;
        background-color: #fff;
        border: 0 none;
        border-right: 1px solid #ccc;
        outline: 0;
        transition: all .1s;
    }

    .billno-amount {
        padding: 5px;
        width: 110px;
        height: 18px;
        line-height: 18px;
        vertical-align: middle;
        background-color: #fff;
        border: 0 none;
        border-right: 1px solid #ccc;
        outline: 0;
        transition: all .1s;
    }

    .billno-unit {
        position: absolute;
        z-index: 1;
        top: 1px;
        right: 35px;
        height: 28px;
        padding: 0;
        font-size: 12px;
        line-height: 28px;
        color: #999;
        cursor: default;
    }

    .billno-delbtn {
        display:block;
        float: right;
        width: 28px;
        font-size: 12px;
        text-align: center;
        line-height: 28px;
        color: #999;
        cursor: pointer;
    }
    .billno-delbtn:hover {
        color: #f00;
        transition: all .1s;
    }
</style>

</head>

<body>
    <div class="manage-wrapper">
        <form id="manage-form" action="#">
            <ul class="mod-form-rows">
                <li class="row-item item-pct20">
                    <div class="label-wrap"><label for="type">发票类别</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><span id="type"></span></div>
                </li>
                <li class="row-item item-pct20">
                    <div class="label-wrap"><label for="code">发票代码</label></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input" name="code" id="code" placeholder="无代码请留空" /></div>
                </li>
                <li class="row-item" style="width:236px">
                    <div class="label-wrap"><label for="no">发票号码</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input" name="no" id="no" placeholder="原样录入无需人为拆分" /></div>
                </li>
                <li class="row-item" style="width:180px">
                    <div class="label-wrap"><label for="date">开票日期</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input ui-datepicker-input" name="date" id="date" autocomplete="off" readonly /></div>
                </li>
                <li class="row-item item-pct20">
                    <div class="label-wrap"><label for="amount">价税合计</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><input type="number" value="" class="ui-input" name="amount" id="amount" /><span class="unit">元</span></div>
                </li>
                <li class="row-item item-pct20">
                    <div class="label-wrap"><label for="us">我方主体</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><span id="us"></span></div>
                </li>
                <li class="row-item item-pct80">
                    <div class="label-wrap"><label for="otherSide">对方主体</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input" name="otherSide" id="otherSide" /></div>
                </li>
                <li class="row-item item-pct20">
                    <div class="label-wrap"><label for="inoutType">往来区分</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><span id="inoutType"></span></div>
                </li>
                <li class="row-item item-pct20">
                    <div class="label-wrap"><label for="label">性质标签</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><span id="label"></span></div>
                </li>
                <li class="row-item item-pct20">
                    <div class="label-wrap"><label for="transType">业务类别</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><span id="transType"></span></div>
                </li>
                <li class="row-item item-pct20">
                    <div class="label-wrap"><label for="uName">录入人</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input ui-input-dis" name="uName" id="uName" readonly /></div>
                </li>
                <li class="row-item item-pct20">
                    <div class="label-wrap"><label for="createTime">录入时间</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input ui-input-dis" name="createTime" id="createTime" readonly /></div>
                </li>
                <div class="rel fl ml10 mr10 mt30">
                    <div class="fl pl5 pr5 lh28 ti10 night bg-silver bdd b-r-3-left">关联单据列表（可修改）</div>
                    <div id="totalAmount" class="fl ml-1 pl10 pr10 lh28 night bg-white bdd brn"></div>
                    <div id="reAmount" class="fl pr10 lh28 night bg-white bdd bln b-r-3-right"></div>
                    <div class="fr ml10">
                        <div class="fl h28 lh28 bdd brn b-r-3-left">
                            <div class="fl pl10 pr10 brd">增加关联单据：</div>
                            <input type="text" id="curno" class="billno-no" value="" placeholder="输入要关联的单据编号"/>
                            <input type="number" id="curamount" class="billno-amount" value="" placeholder="输入要关联的金额"/>
                        </div>
                        <div id="addBillNo" class="fl ml-1 ui-btn bg-green b-r-3-right"><i class="iconfont icon-xiajiang"></i><span>确定加入到列表</span></div>
                    </div>
                    <div id="billNoCon" class="billno-con"></div>
                </div>
            </ul>
        </form>
    </div>
<<<<<<< HEAD
    <script type="text/javascript" src="/statics/js/dist/invoice-manage.js?v=2026102701"></script>
=======
    <script type="text/javascript" src="/statics/js/dist/invoice-manage.js?v=2026101403"></script>
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
</body>

</html>
