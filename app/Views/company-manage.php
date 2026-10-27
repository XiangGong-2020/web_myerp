<?= view('header') ?>
<link rel="stylesheet" type="text/css" href="/statics/css/manage.css?v=2026102701" />
<link rel="stylesheet" type="text/css" href="/statics/js/plugins/fSelect/fSelect.css?v=2026102701" />
<style>
    .business-license-img {
        position: relative;
        width: 100%;
        height: 98px;
    }

    .business-license-img .img {
        width: 100%;
        height: 100%;
        text-align: center;
        line-height: 1;
    }

    .business-license-img .img img {
        width: 100%;
        height: 100%;
    }

    /* 重写 */
    .choosefile {
        top: 0;
        left: 90px;
        width: 150px;
        height: 26px;
        font-size: 12px;
        line-height: 26px;
    }
</style>
</head>

<body>
    <div class="manage-wrapper">
        <form id="manage-form" action="#">
            <ul class="mod-form-rows">
                <li class="row-item item-pct20">
                    <div class="label-wrap"><label for="name">主体简称</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input" name="name" id="name" /></div>
                </li>
                <li class="row-item item-pct80">
                    <div class="label-wrap"><label for="fullName">主体全称</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input" name="fullName" id="fullName" /></div>
                </li>
                <li class="row-item item-pct20">
                    <div class="label-wrap"><label for="no">信用代码</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input" name="no" id="no" /></div>
                </li>
                <li class="row-item item-pct80">
                    <div class="label-wrap"><label for="address">注册地址</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input" name="address" id="address" /></div>
                </li>
                <li class="row-item item-pct20">
                    <div class="label-wrap"><label for="managerName">法人姓名</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input" name="managerName" id="managerName" /></div>
                </li>
                <li class="row-item item-pct20">
                    <div class="label-wrap"><label for="date">注册日期</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input ui-datepicker-input" name="date" id="date" autocomplete="off" readonly /></div>
                </li>
                <li class="row-item item-pct20 row-category">
                    <div class="label-wrap"><label for="category">纳税资质</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><span id="category"></span></div>
                </li>
                <li class="row-item item-pct20">
                    <div class="label-wrap"><label for="invoiceway">征收方式</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><span id="invoiceway"></span></div>
                </li>
                <li class="row-item item-pct20">
                    <div class="label-wrap"><label for="invoicelimit">开票限额</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input" name="invoicelimit" id="invoicelimit" /><span class="unit">元</span></div>
                </li>
                <li class="row-item">
                    <div class="label-wrap"><label for="account">关联账户</label></div>
                    <div class="ctn-wrap">
                        <select id="accountCombo" class="hidden" multiple="multiple">
                            <optgroup id="accountNormal" label="使用中"></optgroup>
                            <optgroup id="accountDisabled" label="已注销"></optgroup>
                        </select>
                    </div>
                </li>
                <li class="row-item">
                    <div class="label-wrap"><label for="billFrom">销售渠道</label></div>
                    <div class="ctn-wrap">
                        <select id="billFromCombo" class="hidden" multiple="multiple">
                            <optgroup id="billFromNormal" label="开放中"></optgroup>
                            <optgroup id="billFromDisabled" label="已关闭"></optgroup>
                        </select>
                    </div>
                </li>
                <li class="row-item">
                    <div class="w80 lh28 ti10 gray bg-white bde b-r-3-top mb-1">经营范围</div>
                    <div class="ctn-wrap">
                        <textarea id="businessScope" class="ui-input" spellcheck="false" placeholder="填写经营范围"></textarea>
                    </div>
                </li>
                <li class="row-item item-pct20">
                    <div class="w80 lh28 ti10 gray bg-white bde b-r-3-top mb-1">营业执照</div>
                    <div id="upimg" class="choosefile"><i class="iconfont icon-shangchuan f14 mr5"></i><span>上传营业执照扫描图片</span></div>
                    <div class="ctn-wrap">
                        <div class="business-license-img bde">
                            <div id="img" class="img"></div>
                            <div id="queue"></div>
                        </div>
                    </div>
                </li>
                <li class="row-item item-pct80">
                    <div class="w80 lh28 ti10 gray bg-white bde b-r-3-top mb-1">备注信息</div>
                    <div class="ctn-wrap">
                        <textarea id="description" class="ui-input" spellcheck="false" placeholder="添加备注信息"></textarea>
                    </div>
                </li>
            </ul>
        </form>
    </div>
    <script type="text/javascript" src="/statics/js/plugins/uploader/jquery.uploader.js?v=2026102701"></script>
    <script type="text/javascript" src="/statics/js/plugins/fSelect/fSelect.js?v=2026102701"></script>
    <script type="text/javascript" src="/statics/js/dist/company-manage.js?v=2026102701"></script>
</body>

</html>
