<?= view('header') ?>
<style>
    .tabs {
        background: #fff;
    }

    .tab-menu {
        height: 40px;
        overflow: hidden;
        text-align: center;
        background: #f5f5f5;
    }

    .tab-tit {
        display: inline-block;
        width: 100px;
        height: 40px;
        line-height: 40px;
        text-align: center;
        color: #2f4050;
        cursor: pointer;
    }

    .tab-tit.active {
        background: #fff;
        transition: all .1s;
    }

    .tab-content {
        width: 100%;
        padding: 0;
        border: 0;
    }

    .col-tab {
        position: relative;
        display: none;
        width: 100%;
        min-height: calc(100vh - 91px);
        overflow: hidden;
        padding: 0;
        background: #fff;
        border: 0;
    }

    #excelBtns-1,
    #excelBtns-2 {
        width: 208px;
    }

    .print-tab {
        position: relative;
        margin: 0 auto;
        background: #fff;
        overflow: hidden;
    }

    .tab-tit-s {
        width: 100%;
        height: 40px;
        line-height: 40px;
        text-align: center;
    }

    .printer_img {
        position: relative;
        margin-top: 30px;
        height: auto;
        text-align: center;
        overflow: hidden;
    }

    .tab1 {
        width: 210mm;
        height: auto;
    }

    .ui-combo-wrap {
        border: 0 none;
        border-bottom: 1px solid #ddd;
    }

    #classes {
        height: 50px;
        line-height: 30px;
    }

    #classes label {
        display: inline-block;
        vertical-align: middle;
        width: 90px;
        height: 28px;
        margin-right: 5px;
        line-height: 28px;
        text-align: center;
        background-color: #fff;
        border: 1px solid #E1E2E3;
    }

    #classes .radio {
        width: 80px !important;
    }

    .tab2 {
        width: 40mm;
        height: 50mm;
    }

    .name {
        float: left;
        width: 10mm;
        height: 17px;
        margin-top: 10px;
        line-height: 16px;
        text-align: center;
    }

    .value {
        float: left;
        width: 28mm;
        height: 16px;
        margin-top: 10px;
        line-height: 16px;
        border-bottom: 1px solid #ddd;
    }

    .value input {
        width: 100%;
        height: 100%;
        line-height: 16px;
        text-align: left;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        border: 0 none;
        outline: 0 none;
    }

    .notice-info {
        position: absolute;
        bottom: 0;
        width: 100%;
        padding: 20px 0;
        line-height: 24px;
        color: #999;
        background: #f5f5f5;
    }

    .print-btn {
        width: 80px;
        margin: 0 auto 200px auto;
    }
</style>
</head>

<body>
    <div class="wrapper">
        <div class="mod-search cf">
            <div class="fr ui-btn-group">
                <a id="refresh" class="ui-btn"><i class="iconfont icon-chongzhi"></i><span>重置</span></a>
            </div>
        </div>
        <div class="tabs">
            <div class="tab-menu">
                <div class="tab-tit active">装箱清单</div>
                <div class="tab-tit">发货标签</div>
            </div>
            <div class="tab-content">
                <div class="col-tab">
                    <div class="printer_img mb30"><img width=160 src="/statics/img/printer_scx4521.jpg"></div>
                    <div id="classes" class="mb30 tc">
                        <p>请选择输入模式</p>
                        <label class="radio b-r-3"><input type="radio" name="classes" value="1">自由输入</label>
                        <label class="radio b-r-3"><input type="radio" name="classes" value="2">导入表格</label>
                        <label class="radio b-r-3"><input type="radio" name="classes" value="3">系统点选</label>
                    </div>
                    <div id="excelBtns-1" class="auto mb10 cf tc dn">
                        <a id="download-1" class="ui-btn mr5" target="_blank" href="/data/template/装箱清单模板.xlsx"><i class="iconfont icon-xiazai"></i><span>下载模板</span></a>
                        <a id="import-1" class="ui-btn"><i class="iconfont icon-daoru"></i><span>导入表格</span></a>
                    </div>
                    <!--startprint1-->
                    <div class="print-tab tab1">
                        <div class="tab-tit-s">装箱清单</div>
                        <div class="fl mt20 ml10 h30 lh30">
                            <label>清单单号：</label>
                            <span id="billNo"><?php echo str_no('ZX'); ?></span>
                        </div>
                        <div class="fr mt20 mr10">
                            <label class="fl h30 lh30">客户：</label>
                            <span class="ui-combo-wrap" id="contact" data-type="customer">
                                <input type="text" name="contact" class="contact input-txt" autocomplete="off" value="">
                                <i class="ui-icon-ellipsis iconfont icon-shenglvehao"></i>
                            </span>
                        </div>
                        <div class="cf"></div>
                        <div class="grid-wrap mt10 mr10 ml10 mb30 bde">
                            <table id="grid"></table>
                        </div>
                    </div>
                    <!--endprint1-->
                    <div class="print-btn">
                        <a class="ui-btn" onclick="THISPAGE.preview(1)"><i class="iconfont icon-dayin"></i><span>打印</span></a>
                    </div>
                    <div class="notice-info">
                        <p class="ml20">此清单用于放在快递包装箱内，来表明该包装箱内含有的产品明细，方便客户一看便知。</p>
                        <p class="ml20">请使用主流Chromium内核浏览器如：Microsoft Edge 浏览器、Google Chrome 浏览器、QQ浏览器，请不要使用IE或其他落后浏览器。</p>
                        <p class="ml20">纸张尺寸：A4（210mm * 297mm），使用激光打印机，输入相应内容后即可点击打印。</p>
                        <p class="ml20">或者下载模板编辑好表格后导入即可开始打印。</p>
                    </div>
                </div>
                <div class="col-tab">
                    <div class="printer_img mb30"><img width=100 src="/statics/img/printer_jpw580.jpg"></div>
                    <div id="excelBtns-2" class="auto mb10 cf tc">
                        <a id="download-2" class="ui-btn mr5" target="_blank" href="/data/template/发货标签模板.xlsx"><i class="iconfont icon-xiazai"></i><span>下载模板</span></a>
                        <a id="import-2" class="ui-btn"><i class="iconfont icon-daoru"></i><span>导入表格</span></a>
                    </div>
                    <!--startprint2-->
                    <div id="tab2group">
                        <div class="print-tab tab2">
                            <div class="tab-tit-s">发货标签</div>
                            <div class="name">名称:</div>
                            <div class="value"><input type="text" id="tab2_name" value="" /></div>
                            <div class="name">型号:</div>
                            <div class="value"><input type="text" id="tab2_spec" value="" /></div>
                            <div class="name">属性:</div>
                            <div class="value"><input type="text" id="tab2_sku" value="" /></div>
                            <div class="name">数量:</div>
                            <div class="value"><input type="text" id="tab2_qty" value="" /></div>
                        </div>
                    </div>
                    <!--endprint2-->
                    <div class="print-btn">
                        <a class="ui-btn" onclick="THISPAGE.preview(2)"><i class="iconfont icon-dayin"></i><span>打印</span></a>
                    </div>
                    <div class="notice-info">
                        <p class="ml20">此标签用于贴在客户不好辨认种类或数量的产品打包之后的包装上，来表明该包装内含有多少个（套）某规格型号某辅助属性的某产品。</p>
                        <p class="ml20">请使用主流Chromium内核浏览器如：Microsoft Edge 浏览器、Google Chrome 浏览器、QQ浏览器，请不要使用IE或其他落后浏览器。</p>
                        <p class="ml20">纸张尺寸：40mm * 50mm，使用热敏打印机，输入相应内容后即可点击打印。</p>
                        <p class="ml20">如果需要一次打印多张，下载模板编辑好表格后导入可以批量打印。</p>
                    </div>
                </div>
            </div>
        </div>
        <div id="initCombo" class="dn">
            <input type="text" class="textbox goodsAuto" name="goods" autocomplete="off">
        </div>
        <div id="storageBox" class="shadow target_box dn"></div>
    </div>
    <script type="text/javascript" src="/statics/js/plugins/uploader/jquery.uploader.js?v=2026102701"></script>
    <script type="text/javascript" src="/statics/js/dist/print-template.js?v=2026102701"></script>
</body>

</html>
