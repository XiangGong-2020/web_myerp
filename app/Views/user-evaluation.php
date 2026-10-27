<?= view('header') ?>
<link rel="stylesheet" type="text/css" href="/statics/css/bills.css?v=2026102701" />
<style>
    .rights-item {
        float: left;
        width: auto;
        margin: 10px 0 0 10px;
        padding-right: 10px;
        line-height: 40px;
        color: #fff;
        border-radius: 4px;
        overflow: hidden;
        cursor: default;
    }
</style>
</head>

<body>
    <div class="wrapper">
        <div class="mod-search cf">
            <div class="fl">
                <span id="evaluateScoreTit" class="db fl gray">上月考评得分（满分100）</span><span id="evaluateScore" class="ml10 db fl w50 f16 fb lh30 tc white bg-green b-r-3"></span>
            </div>
            <div class="fr ui-btn-group">
                <a id="lastMonth" class="ui-btn current"><i class="iconfont icon-chaxun"></i><span>上月</span></a>
                <a id="lastMonth2" class="ui-btn"><i class="iconfont icon-chaxun"></i><span>上上月</span></a>
            </div>
        </div>
        <div class="diy-con"></div>
    </div>
    <script type="text/javascript" src="/statics/js/dist/user-evaluation.js?v=2026102701"></script>
</body>

</html>
