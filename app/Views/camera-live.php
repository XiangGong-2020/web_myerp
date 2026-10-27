<?= view('header') ?>
<style>
    /* loading 取消继承父元素样式 */
    .loading {
        all: unset;
    }
    .ui-droplist-wrap {
        z-index: 1000 !important;
    }
</style>
</head>

<body>
    <div class="wrapper">
        <div class="mod-search cf">
            <div class="fl">
                <ul class="ul-inline">
                    <li><span class="db fl mr-2 h28 lh28 pl5 pr5 gray bg-white bde b-r-3-left">选择直播通道</span><span id="combo"></span></li>
                </ul>
            </div>
            <div class="fr ui-btn-group">
                <a class="ui-btn mb2" onclick="THISPAGE.initFloat()"><span>新建小窗口播放</span></a>
            </div>
        </div>
        <div id="playWind" class="bg-black"></div>
    </div>
<<<<<<< HEAD
    <script type="text/javascript" src="/statics/js/plugins/ezuikit/ezuikit.js?v=2026102701"></script>
    <script type="text/javascript" src="/statics/js/dist/camera-live.js?v=2026102701"></script>
=======
    <script type="text/javascript" src="/statics/js/plugins/ezuikit/ezuikit.js?v=2026101403"></script>
    <script type="text/javascript" src="/statics/js/dist/camera-live.js?v=2026101403"></script>
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
</body>

</html>
