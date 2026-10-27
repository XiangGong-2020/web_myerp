<?= view('header') ?>
<<<<<<< HEAD
<link rel="stylesheet" type="text/css" href="/statics/css/index.css?v=2026102701" />
=======
<link rel="stylesheet" type="text/css" href="/statics/css/index.css?v=2026101403" />
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
</head>

<body>
    <div style="position:fixed;top:-1px;z-index:9999;width:100vw;height:1px;box-shadow:0 2px 5px #00000088;"></div>
    <div id="container">
        <div id="col-side">
            <ul id="nav" class="cf bg-blue">
                <li class="item item-index current">
                    <a href="javascript:void(0);" class="main-nav"><i class="icon iconfont icon-zhuye"></i><span class="text">主页</span></a>
                    <div class="sub-nav-wrap">
                        <div class="nav-item">
                            <div class="title">日常</div>
                            <ul class="sub-nav" id="index-offen"></ul>
                        </div>
                        <div class="nav-item">
                            <div class="title">资讯</div>
                            <ul class="sub-nav" id="index-news"></ul>
                        </div>
                    </div>
                </li>
                <li class="item item-product">
                    <a href="javascript:void(0);" class="main-nav"><i class="icon iconfont icon-chanpin"></i><span class="text">产品</span></a>
                    <div class="sub-nav-wrap dn">
                        <div class="nav-item">
                            <div class="title">基础</div>
                            <ul class="sub-nav" id="product-base"></ul>
                        </div>
                        <div class="nav-item">
                            <div class="title">文件</div>
                            <ul class="sub-nav" id="product-file"></ul>
                        </div>
                        <div class="nav-item">
                            <div class="title">同步</div>
                            <ul class="sub-nav" id="product-platform"></ul>
                        </div>
                        <div class="nav-item">
                            <div class="title">竞品</div>
                            <ul class="sub-nav" id="product-competitive"></ul>
                        </div>
                    </div>
                </li>
                <li class="item item-develop">
                    <a href="javascript:void(0);" class="main-nav"><i class="icon iconfont icon-chanpin"></i><span class="text">研发</span></a>
                    <div class="sub-nav-wrap dn">
                        <div class="nav-item">
                            <div class="title">基础</div>
                            <ul class="sub-nav" id="develop-base"></ul>
                        </div>
                        <div class="nav-item">
                            <div class="title">开发</div>
                            <ul class="sub-nav" id="develop-develop"></ul>
                        </div>
                        <div class="nav-item">
                            <div class="title">文件</div>
                            <ul class="sub-nav" id="develop-file"></ul>
                        </div>
                        <div class="nav-item">
                            <div class="title">报表</div>
                            <ul class="sub-nav" id="develop-report"></ul>
                        </div>
                    </div>
                </li>
                <li class="item item-bid">
                    <a href="javascript:void(0);" class="main-nav"><i class="icon iconfont icon-zhaotoubiao"></i><span class="text">投标</span></a>
                    <div class="sub-nav-wrap dn">
                        <div class="nav-item">
                            <div class="title">信息</div>
                            <ul class="sub-nav" id="bid-info"></ul>
                        </div>
                        <div class="nav-item">
                            <div class="title">来源</div>
                            <ul class="sub-nav" id="bid-from"></ul>
                        </div>
                        <div class="nav-item">
                            <div class="title">报价</div>
                            <ul class="sub-nav" id="bid-quote"></ul>
                        </div>
                        <div class="nav-item">
                            <div class="title">项目</div>
                            <ul class="sub-nav" id="bid-program"></ul>
                        </div>
                        <div class="nav-item">
                            <div class="title">服务</div>
                            <ul class="sub-nav" id="bid-service"></ul>
                        </div>
                        <div class="nav-item">
                            <div class="title">报表</div>
                            <ul class="sub-nav" id="bid-report"></ul>
                        </div>
                    </div>
                </li>
                <li class="item item-sales">
                    <a href="javascript:void(0);" class="main-nav"><i class="icon iconfont icon-xiaoshou"></i><span class="text">销售</span></a>
                    <div class="sub-nav-wrap dn">
                        <div class="nav-item">
                            <div class="title">基础</div>
                            <ul class="sub-nav" id="sales-base"></ul>
                        </div>
                        <div class="nav-item">
                            <div class="title">单据</div>
                            <ul class="sub-nav" id="sales-bill"></ul>
                        </div>
                        <div class="nav-item">
                            <div class="title">客服</div>
                            <ul class="sub-nav" id="sales-service"></ul>
                        </div>
                        <div class="nav-item">
                            <div class="title">目标</div>
                            <ul class="sub-nav" id="sales-target"></ul>
                        </div>
                        <div class="nav-item">
                            <div class="title">汇总</div>
                            <ul class="sub-nav" id="sales-sum"></ul>
                        </div>
                        <div class="nav-item">
                            <div class="title">报表</div>
                            <ul class="sub-nav" id="sales-report"></ul>
                        </div>
                    </div>
                </li>
                <li class="item item-purchase">
                    <a href="javascript:void(0);" class="main-nav"><i class="icon iconfont icon-caigou"></i><span class="text">采购</span></a>
                    <div class="sub-nav-wrap dn">
                        <div class="nav-item">
                            <div class="title">基础</div>
                            <ul class="sub-nav" id="purchase-base"></ul>
                        </div>
                        <div class="nav-item">
                            <div class="title">申请</div>
                            <ul class="sub-nav" id="purchase-req"></ul>
                        </div>
                        <div class="nav-item">
                            <div class="title">单据</div>
                            <ul class="sub-nav" id="purchase-bill"></ul>
                        </div>
                        <div class="nav-item">
                            <div class="title">通知</div>
                            <ul class="sub-nav" id="purchase-alert"></ul>
                        </div>
                        <div class="nav-item">
                            <div class="title">质检</div>
                            <ul class="sub-nav" id="purchase-qc"></ul>
                        </div>
                        <div class="nav-item">
                            <div class="title">报表</div>
                            <ul class="sub-nav" id="purchase-report"></ul>
                        </div>
                    </div>
                </li>
                <li class="item item-produce">
                    <a href="javascript:void(0);" class="main-nav"><i class="icon iconfont icon-shengchan"></i><span class="text">生产</span></a>
                    <div class="sub-nav-wrap dn">
                        <div class="nav-item">
                            <div class="title">基础</div>
                            <ul class="sub-nav" id="produce-base"></ul>
                        </div>
                        <div class="nav-item">
                            <div class="title">配货</div>
                            <ul class="sub-nav" id="produce-kitting"></ul>
                        </div>
                        <div class="nav-item">
                            <div class="title">加工</div>
                            <ul class="sub-nav" id="produce-processing"></ul>
                        </div>
                    </div>
                </li>
                <li class="item item-inventory">
                    <a href="javascript:void(0);" class="main-nav"><i class="icon iconfont icon-cangku"></i><span class="text">仓储</span></a>
                    <div class="sub-nav-wrap dn">
                        <div class="nav-item">
                            <div class="title">基础</div>
                            <ul class="sub-nav" id="inventory-base"></ul>
                        </div>
                        <div class="nav-item">
                            <div class="title">单据</div>
                            <ul class="sub-nav" id="inventory-bill"></ul>
                        </div>
                        <div class="nav-item">
                            <div class="title">库存</div>
                            <ul class="sub-nav" id="inventory-other"></ul>
                        </div>
                        <div class="nav-item">
                            <div class="title">报表</div>
                            <ul class="sub-nav" id="inventory-report"></ul>
                        </div>
                    </div>
                </li>
                <li class="item item-deliver">
                    <a href="javascript:void(0);" class="main-nav"><i class="icon iconfont icon-kuaidi"></i><span class="text">物流</span></a>
                    <div class="sub-nav-wrap dn">
                        <div class="nav-item">
                            <div class="title">基础</div>
                            <ul class="sub-nav" id="deliver-base"></ul>
                        </div>
                        <div class="nav-item">
                            <div class="title">工具</div>
                            <ul class="sub-nav" id="deliver-tool"></ul>
                        </div>
                        <div class="nav-item">
                            <div class="title">单据</div>
                            <ul class="sub-nav" id="deliver-bill"></ul>
                        </div>
                        <div class="nav-item">
                            <div class="title">包裹</div>
                            <ul class="sub-nav" id="deliver-bag"></ul>
                        </div>
                        <div class="nav-item">
                            <div class="title">报表</div>
                            <ul class="sub-nav" id="deliver-report"></ul>
                        </div>
                    </div>
                </li>
                <li class="item item-personnel">
                    <a href="javascript:void(0);" class="main-nav"><i class="icon iconfont icon-renshi"></i><span class="text">人事</span></a>
                    <div class="sub-nav-wrap dn">
                        <div class="nav-item">
                            <div class="title">基础</div>
                            <ul class="sub-nav" id="personnel-base"></ul>
                        </div>
                        <div class="nav-item">
                            <div class="title">日常</div>
                            <ul class="sub-nav" id="personnel-ofen"></ul>
                        </div>
                        <div class="nav-item">
                            <div class="title">汇总</div>
                            <ul class="sub-nav" id="personnel-sum"></ul>
                        </div>
                        <div class="nav-item">
                            <div class="title">报表</div>
                            <ul class="sub-nav" id="personnel-report"></ul>
                        </div>
                    </div>
                </li>
                <li class="item item-money">
                    <a href="javascript:void(0);" class="main-nav"><i class="icon iconfont icon-caiwu"></i><span class="text">财务</span></a>
                    <div class="sub-nav-wrap dn">
                        <div class="nav-item">
                            <div class="title">基础</div>
                            <ul class="sub-nav" id="money-base"></ul>
                        </div>
                        <div class="nav-item">
                            <div class="title">申请</div>
                            <ul class="sub-nav" id="money-req"></ul>
                        </div>
                        <div class="nav-item">
                            <div class="title">单据</div>
                            <ul class="sub-nav" id="money-bill"></ul>
                        </div>
                        <div class="nav-item">
                            <div class="title">税务</div>
                            <ul class="sub-nav" id="money-tax"></ul>
                        </div>
                        <div class="nav-item">
                            <div class="title">报表</div>
                            <ul class="sub-nav" id="money-report"></ul>
                        </div>
                    </div>
                </li>
                <li class="item item-manage">
                    <a href="javascript:void(0);" class="main-nav"><i class="icon iconfont icon-yunying"></i><span class="text">数据</span></a>
                    <div class="sub-nav-wrap dn">
                        <div class="nav-item">
                            <div class="title">图表</div>
                            <ul class="sub-nav" id="manage-data"></ul>
                        </div>
                    </div>
                </li>
                <li class="item item-setting">
                    <a href="javascript:void(0);" class="main-nav"><i class="icon iconfont icon-shezhi"></i><span class="text">设置</span></a>
                    <div class="sub-nav-wrap dn">
                        <div class="nav-item">
                            <div class="title">系统</div>
                            <ul class="sub-nav" id="setting-system"></ul>
                        </div>
                        <div class="nav-item">
                            <div class="title">账号</div>
                            <ul class="sub-nav" id="setting-user"></ul>
                        </div>
                        <div class="nav-item">
                            <div class="title">清理</div>
                            <ul class="sub-nav" id="setting-clear"></ul>
                        </div>
                        <div class="nav-item">
                            <div class="title">日志</div>
                            <ul class="sub-nav" id="setting-log"></ul>
                        </div>
                    </div>
                </li>
            </ul>
            <div class="xcx">
                <div class="xcx-main"><i class="icon iconfont icon-xiaochengxu"></i></div>
            </div>
            <div class="xcx-wrap"></div>
        </div>
        <div id="col-main">
            <div id="main-bd">
                <div class="page-tab" id="page-tab"></div>
            </div>
        </div>
    </div>
    <div id="urgentCon" class="urgent-con">
        <div class="urgent-alert-con dn">
            <div class="urgent-alert-info">
            </div>
        </div>
        <div class="urgent-alert-hide dn">
            <div class="fl ml10 f14 fb">紧急通知</div>
            <div class="fr">
                <div id="pause10" class="fl ml10 mt5 h24 lh24 pl5 pr5 white hover-teal b-r-3">
                    <i class="fl iconfont icon-zanting f12" title="暂停10分钟"> 10分钟</i>
                </div>
                <div id="pause30" class="fl ml5 mt5 h24 lh24 pl5 pr5 white hover-teal b-r-3">
                    <i class="fl iconfont icon-zanting f12" title="暂停30分钟"> 30分钟</i>
                </div>
                <div id="pause60" class="fl ml5 mt5 mr5 h24 lh24 pl5 pr5 white hover-teal b-r-3">
                    <i class="fl iconfont icon-zanting f12" title="暂停1小时"> 1小时</i>
                </div>
            </div>
        </div>
        <div class="urgent-alert-tip dn" title="紧急通知暂停中（点击恢复）"><i class="iconfont icon-zanting urgent-alert-tip-icon"></i></div>
    </div>
<<<<<<< HEAD
    <script type="text/javascript" src="/statics/js/plugins/ligertab/jquery.ligertab-1.3.2.js?v=2026102701"></script>
    <script type="text/javascript" src="/statics/js/dist/index.js?v=2026102701"></script>
=======
    <script type="text/javascript" src="/statics/js/plugins/ligertab/jquery.ligertab-1.3.2.js?v=2026101403"></script>
    <script type="text/javascript" src="/statics/js/dist/index.js?v=2026101403"></script>
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
</body>

</html>
