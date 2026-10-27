<?= view('header') ?>
<style>
    .ui-jqgrid tr.ui-row-ltr td {
        box-shadow: inset 1px 0 1px #00000011;
    }

    .ui-jqgrid tr.ui-row-ltr td:first-child {
        box-shadow: none;
    }
</style>
</head>

<body>
    <div class="manage-wrapper">
        <div id="notice" class="pl10 pb5 h20 lh20 gray es"></div>
        <div class="pl10 pr10 pb10">
            <div class="upfile">
                <div class="upfile-con b-r-3-left h50">
                    <label class="upfile-title bdn lh50">参考资料</label>
                </div>
                <div id="files" class="files b-r-3-right" style="height:40px;"></div>
            </div>
        </div>
        <div id="totalInfo" class="ml10 mr10 pr10 h30 lh30 night ovh es"></div>
        <div class="abs mt-30 fr ui-btn-group" style="z-index:2;right:10px;">
            <a id="toBidQuoteReference" class="ui-btn"><i class="iconfont icon-chaxun"></i><span>报价参考</span></a>
        </div>
        <div id="manage-wrap">
            <div class="grid-wrap ml10 mr10 bde b-r-3-bottom">
                <table id="grid"></table>
            </div>
        </div>
    </div>
<<<<<<< HEAD
    <script type="text/javascript" src="/statics/js/dist/bid-quote-quote.js?v=2026102701"></script>
=======
    <script type="text/javascript" src="/statics/js/dist/bid-quote-quote.js?v=2026101403"></script>
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
</body>

</html>
