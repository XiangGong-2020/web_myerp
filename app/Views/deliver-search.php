<?= view('header') ?>
<style>
    .custom-con {
        min-height: calc(100% - 40px);
        margin: 20px;
        background-color: #fff;
        border-radius: 3px;
    }
</style>
</head>

<body>
    <div class="wrapper">
        <div class="mod-search cf">
            <div class="fl">
                <ul class="ul-inline">
                    <li>
                        <span class="db fl mr-2 h28 lh28 pl5 pr5 gray bg-white bde b-r-3-left">快递公司</span>
                        <span id="combo1"></span>
                    </li>
                    <li class="ml1">
                        <span class="db fl mr-2 h28 lh28 pl5 pr5 gray bg-white bde b-r-3-left">快递单号</span>
                        <input type="text" id="postid" class="fl ui-input w200" placeholder="输入快递单号" />
                    </li>
                    <li class="ml1">
                        <span class="db fl mr-2 h28 lh28 pl5 pr5 gray bg-white bde b-r-3-left">收件人或发件人电话后4位</span>
                        <input type="text" id="phone" class="fl ui-input w200" placeholder="顺丰查询时需要" title="我方发件人默认0156" />
                    </li>
                </ul>
            </div>
            <div class="fl ml1 ui-btn-group">
                <a id="search" class="ui-btn"><i class="iconfont icon-chaxun"></i><span>查询</span></a>
            </div>
        </div>
        <div class="diy-con">
            <div class="custom-con">
                <div class="w120 lh30 f14 fb tc black bd-black b-r-3">内部流转信息</div>
                <div id="inData"></div>
                <div class="mt20 w120 lh30 f14 fb tc black bd-black b-r-3">快递轨迹信息</div>
                <div id="outData"></div>
            </div>
        </div>
    </div>
    <script type="text/javascript" src="/statics/js/dist/deliver-search.js?v=2026102701"></script>
</body>

</html>
