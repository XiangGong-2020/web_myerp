<?= view('header') ?>
<<<<<<< HEAD
<link rel="stylesheet" type="text/css" href="/statics/css/manage.css?v=2026102701" />
=======
<link rel="stylesheet" type="text/css" href="/statics/css/manage.css?v=2026101403" />
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
<style>
    .invoice-wrapper {
        position: relative;
        z-index: 2;
        height: 120px;
        padding: 0 0 10px;
        background-color: #fff;
        box-shadow: 0 5px 10px #00000033;
    }

    .invoice-upload {
        position: absolute;
        top: 0px;
        right: 0;
        width: 140px;
        height: 110px;
    }

    /* 重写 */
    .choosefile {
        top: 0;
        left: 0;
        width: 100%;
        height: 30px;
        font-size: 12px;
        line-height: 30px;
        text-align: left;
        border-radius: 3px;
    }

    .upload-queue {
        bottom: 80px;
    }

    .des-con {
        position: fixed;
        z-index: 1;
        top: 130px;
        left: 0;
        width: 1250px;
        height: 30px;
        box-shadow: 0 2px 5px #00000022;
    }

    .invoices {
        height: 450px;
        padding: 15px 10px 0;
        overflow-y: auto;
        border-bottom: 1px solid #E1E2E3;
    }

    .invoice {
        position: relative;
        color: #666;
        background: #f5f6f7;
        margin-bottom: 10px;
        border: 4px dashed #ccc;
    }

    .my-invoice {
        background: #fff9ba;
    }

    .invoice-con {
        display: block;
        overflow: hidden;
        padding: 10px;
    }

    .invoice-info {
        float: left;
        width: 238px;
        line-height: 30px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .invoice-bottom {
        display: block;
        overflow: hidden;
        padding: 10px;
        border-top: 1px dashed #00000033;
    }

    .invoice-billno {
        float: left;
        width: 960px;
        line-height: 30px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .invoice-btns {
        position: absolute;
        right: 10px;
        bottom: 10px;
    }
    .invoices-amount {
        position: fixed;
        left:0;
        top:130px;
        border-radius: 0 0 3px 0;
    }
</style>
</head>

<body>
    <div class="manage-wrapper invoice-wrapper">
        <form id="manage-form" class="fl" style="width:calc(100% - 150px);" action="#">
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
                    <div class="ctn-wrap"><input type="number" value="" class="ui-input" name="amount" id="amount" autocomplete="off" /></div>
                    <span class="unit">元</span>
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
                    <div class="label-wrap"><label for="inoutType">往来区分</label></div>
                    <div class="ctn-wrap"><span id="inoutType"></span></div>
                </li>
                <li class="row-item item-pct20">
                    <div class="label-wrap"><label for="label">性质标签</label></div>
                    <div class="ctn-wrap"><span id="label"></span></div>
                </li>
                <li class="row-item item-pct20">
                    <div class="label-wrap"><label for="transTypeName">业务类别</label></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input ui-input-dis" name="transTypeName" id="transTypeName" readonly /></div>
                </li>
                <li class="row-item item-pct25">
                    <div class="label-wrap"><label for="billNo">关联单号</label></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input ui-input-dis" name="billNo" id="billNo" readonly /></div>
                </li>
                <li class="row-item item-pct15">
                    <div class="label-wrap"><label for="uName">录入人</label></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input ui-input-dis" name="uName" id="uName" readonly /></div>
                </li>
            </ul>
        </form>
        <div class="invoice-upload fr mt10 mr10 b-r-3 ovh">
            <a id="upfile" class="ui-btn pct100 bg-green"><i class="iconfont icon-shangchuan"></i><span>上传发票 自动识别</span></a>
            <div id="queue"></div>
            <a id="subInvoice" class="ui-btn pct100 mt10"><i class="fl iconfont icon-bianji"></i><span>财务录入 手动提交</span></a>
            <a class="des ui-btn pct100 mt10 bg-orange"><i class="fl iconfont icon-xianshi"></i><span>查看自动识别说明</span></a>
        </div>
    </div>
    <div class="des-con white bg-orange dn">
        <div class="fl ml10 lh30">请上传发票自动识别（仅财务可手录），以便及时发现开票错误、减少录错几率、提高录入效率、票根自动归档。目前已支持99%的正规发票识别，包含发票原始pdf/ofd文件识别，以及发票截图png/jpg/jpeg/bmp文件识别。</div>
        <div class="des-close fr mt5 mr10 poi"><i class="iconfont icon-guanbi f16" title="关闭说明"></i></div>
    </div>
    <div id="invoices" class="invoices"></div>
    <div id="invoicesAmount" class="invoices-amount h28 pl10 pr10 lh28 f14 white bg-gold bd-rgba1"></div>
<<<<<<< HEAD
    <script type="text/javascript" src="/statics/js/plugins/uploader/jquery.uploader.js?v=2026102701"></script>
    <script type="text/javascript" src="/statics/js/dist/invoice-bill-manage.js?v=2026102701"></script>
=======
    <script type="text/javascript" src="/statics/js/plugins/uploader/jquery.uploader.js?v=2026101403"></script>
    <script type="text/javascript" src="/statics/js/dist/invoice-bill-manage.js?v=2026101403"></script>
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
</body>

</html>
