<?= view('header') ?>
<style>
    .custom-con {
        min-height: calc(100% - 40px);
        padding: 20px;
        background-color: #fff;
        border-radius: 3px;
    }

    .ui-combo-wrap {
        width: 180px !important;
    }

    .ui-combo-wrap .input-txt {
        width: 151px !important;
    }
</style>
</head>

<body>
    <div class="wrapper">
        <div class="mod-search cf">
            <div class="fl">
                <span class="f14 fb">合作快递运费与时效估算</span>
            </div>
            <div class="fr ui-btn-group">
                <a id="refresh" class="ui-btn"><i class="iconfont icon-shuaxin"></i><span>刷新</span></a>
                <a id="showareas" class="ui-btn"><i class="iconfont icon-ditu"></i><span>全国地区快递时效管理</span></a>
            </div>
        </div>
        <div class="diy-con">
            <div class="custom-con">
                <ul class="ul-inline ovh cf">
                    <li><span class="fl pl10 pr10 lh28 bde b-r-3">万州发往 <i class="iconfont icon-youjiantou f12"></i></span></li>
                    <li class="ml1"><span class="db fl mr-2 h28 lh28 pl5 pr5 gray bg-white bde b-r-3-left">省级</span><span id="province"></span></li>
                    <li class="ml1"><span class="db fl mr-2 h28 lh28 pl5 pr5 gray bg-white bde b-r-3-left">地级</span><span id="city"></span></li>
                    <li class="ml1"><span class="db fl mr-2 h28 lh28 pl5 pr5 gray bg-white bde b-r-3-left">县级</span><span id="area"></span></li>
                    <li class="ml1">
                        <span class="db fl mr-2 h28 lh28 pl5 pr5 gray bg-white bde b-r-3-left">重量</span>
                        <input type="number" id="weight" class="fl ui-input w90" />
                        <span class="fl ml-2 pl10 pr10 lh28 bde b-r-3-right">KG</span>
                    </li>
                    <li class="ml1">
                        <a id="search" class="ui-btn"><i class="iconfont icon-kuaidigusuan"></i><span>估算</span></a>
                    </li>
                </ul>
                <div id="predict-info" class="mt10 bte"></div>
            </div>
        </div>
    </div>
<<<<<<< HEAD
    <script type="text/javascript" src="/statics/js/dist/deliver-predict.js?v=2026102701"></script>
=======
    <script type="text/javascript" src="/statics/js/dist/deliver-predict.js?v=2026101403"></script>
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
</body>

</html>
