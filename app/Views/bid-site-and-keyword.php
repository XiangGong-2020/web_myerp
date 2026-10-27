<?= view('header') ?>
<link rel="stylesheet" type="text/css" href="/statics/css/bills.css?v=2026102701" />
<style>
    .bid_keywords {
        height: auto;
    }

    .bid_keywords_con {
        overflow: hidden;
    }

    .bid_keywords_con h3 {
        position: relative;
        display: block;
        padding-left: 10px;
        font-size: 14px;
        line-height: 40px;
        background: #fff;
    }

    .bid_keywords_con h3 .btn {
        position: absolute;
        z-index: 1;
        font-size: 12px;
        font-weight: normal;
        top: 0;
        right: 15px;
    }

    .bid_keywords_con .keywords {
        margin: 0;
        padding: 10px 10px 0 0;
        background-color: #f5f5f5;
        border-radius: 10px 10px 0 0;
        box-shadow: inset 0 10px 5px -5px #00000022;
        overflow: hidden;
    }

    .bid_keywords_con .each {
        position: relative;
        float: left;
        width: auto;
        height: 24px;
        padding: 0 20px 0 20px;
        margin: 0 0 10px 10px;
        color: #000;
        background: #fff;
        border-radius: 3px;
        box-shadow: 0 0 0 1px #00000011;
        cursor: pointer;
        overflow: hidden;
    }

    .bid_keywords_con .each:hover {
        color: #fff;
        background: #666;
        padding: 0 30px 0 10px;
        transition: all .3s;
    }

    .bid_keywords_con span {
        display: block;
        width: auto;
        height: 24px;
        line-height: 24px;
    }

    .bid_keywords_con .each i {
        position: absolute;
        top: 3px;
        right: 3px;
        width: 18px;
        height: 18px;
        font-size: 12px;
        line-height: 18px;
        text-align: center;
        color: #fff;
        cursor: pointer;
        border-radius: 3px;
    }

    .bid_keywords_con .each:hover i {
        color: #fff;
        background: #999;
    }

    .bid_keywords_con .each:hover i:hover {
        color: #fff;
        background: #f00;
        transition: all .5s;
    }

    .bid_sites {
        height: auto;
    }

    .bid_sites ul {
        margin: 0;
        padding: 10px 10px 0 0;
        background-color: #f5f5f5;
        border-radius: 10px 10px 0 0;
        box-shadow: inset 0 10px 5px -5px #00000022;
        overflow: hidden;
    }

    .bid_sites li {
        position: relative;
        float: left;
        width: calc(20% - 10px);
        height: 100px;
        margin: 0 0 10px 10px;
        background: #fff;
        border-radius: 3px;
        box-shadow: 0 0 0 1px #00000011;
    }

    .bid_sites .name {
        width: calc(100% - 20px);
        height: 48px;
        padding: 0 10px;
        margin: 30px 0 21px;
        font-size: 12px;
        font-weight: bold;
        line-height: 48px;
        text-align: center;
        text-overflow: ellipsis;
        white-space: nowrap;
        color: #333;
        border-top: 1px solid #00000011;
        overflow: hidden;
    }

    .bid_sites .province {
        position: absolute;
        z-index: 1;
        top: 0;
        left: 0;
        height: 30px;
        padding: 0 10px;
        line-height: 30px;
        color: #999;
        transition: all .9s;
    }

    .bid_sites .hits {
        position: absolute;
        z-index: 1;
        top: 0;
        left: 50%;
        transform: translateX(-50%);
        height: 30px;
        padding: 0 10px;
        line-height: 30px;
        color: #999;
        opacity: 0;
        transition: all .9s;
    }

    .bid_sites .edit-site {
        position: absolute;
        z-index: 2;
        top: 0px;
        right: 0px;
        display: block;
        width: 40px;
        height: 30px;
        font-size: 16px;
        line-height: 30px;
        text-align: center;
        color: #999;
        cursor: pointer;
        opacity: 0;
        transition: all .9s;
    }

    .bid_sites .cant-bid {
        position: absolute;
        z-index: 1;
        top: 30px;
        width: 100%;
        height: 49px;
        font-size: 12px;
        line-height: 49px;
        text-align: center;
        color: #fff;
        background-color: #fd625ecc;
    }

    .bid_sites .winRate {
        position: absolute;
        bottom: 0;
        width: 100%;
        height: 30px;
        background: #00000006;
        border-top: 1px solid #00000011;
        border-radius: 0 0 3px 3px;
        overflow: hidden;
    }

    .bid_sites .winRate-bar {
        width: 0px;
        height: 30px;
    }

    .bid_sites .winRate-text {
        position: absolute;
        z-index: 1;
        top: 0;
        left: 10px;
        right: 10px;
        height: 30px;
        font-size: 88%;
        line-height: 30px;
        text-align: center;
        color: #698096;
    }

    .bid_sites li:hover {
        z-index: 1;
        box-shadow: 0 10px 20px 1px #2c65ffaa;
        transition: box-shadow .5s;
    }

    .bid_sites li:hover .hits,
    .bid_sites li:hover .edit-site {
        opacity: 1;
    }

    .bid_sites li:hover .edit-site:hover {
        color: #2c65ff;
    }

    .bid_sites li:hover .cant-bid {
        background-color: #ff0000cc;
    }
</style>
</head>

<body>
    <div class="wrapper">
        <div class="mod-search cf">
            <div class="fl">
                <ul class="ul-inline">
                    <li><span id="provinceCombo"></span></li>
                    <li class="ml1"><span id="siteTypeCombo"></span></li>
                    <li class="ml1">
                        <input type="text" id="skey" class="fl ui-input" placeholder="输入任意关键词" autocomplete="off" />
                    </li>
                </ul>
            </div>
            <div class="fl ml1 ui-btn-group">
                <a id="searchSite" class="ui-btn"><i class="iconfont icon-chaxun"></i><span>查询招标网站</span></a>
            </div>
            <div class="fl ml10 lh28 bg-white bd-blue ovh b-r-3">
                <div class="fl pl10 pr10 blue">抓取网站总数</div>
                <div id="total" class="fl pl10 pr10 white bg-blue b-r-3-left">？</div>
            </div>
            <div class="fl ml2 lh28 bg-white bd-blue ovh b-r-3">
                <div class="fl pl10 pr10 blue">投过网站总数</div>
                <div id="usedTotal" class="fl pl10 pr10 white bg-blue b-r-3-left">？</div>
            </div>
            <div class="fl ml2 lh28 bg-white bd-blue ovh b-r-3">
                <div class="fl pl10 pr10 blue">没投过网站总数</div>
                <div id="neverTotal" class="fl pl10 pr10 white bg-blue b-r-3-left">？</div>
            </div>
            <div class="fr ui-btn-group">
                <a id="refresh" class="ui-btn"><i class="iconfont icon-shuaxin"></i><span>刷新</span></a>
                <a id="addKeyword" class="ui-btn"><i class="iconfont icon-zengjia"></i><span>新增关键词</span></a>
                <a id="addSite" class="ui-btn"><i class="iconfont icon-zengjia"></i><span>新增招标网站</span></a>
            </div>
        </div>
        <div class="diy-con">
            <div class="bid_keywords">
                <div class="bid_keywords_con bbe">
                    <h3><i class="mr5 iconfont icon-xiajiantou f12"></i>包含关键词<div id="in_btn" class="btn poi">展开</div>
                    </h3>
                    <div id="in_keywords" class="keywords dn"></div>
                </div>
                <div class="bid_keywords_con bbe">
                    <h3><i class="mr5 iconfont icon-xiajiantou f12"></i>排除关键词<div id="out_btn" class="btn poi">展开</div>
                    </h3>
                    <div id="out_keywords" class="keywords dn"></div>
                </div>
            </div>
            <div class="bid_sites bbe">
                <div class="h40 lh40 pl10 f14 fb bg-white cf"><i class="mr5 iconfont icon-xiajiantou f12"></i><span>招标平台</span></div>
                <ul id="bid_sites_0" class="dn"></ul>
            </div>
            <div class="bid_sites bbe">
                <div class="h40 lh40 pl10 f14 fb bg-white cf"><i class="mr5 iconfont icon-xiajiantou f12"></i><span>招标代理</span></div>
                <ul id="bid_sites_1" class="dn"></ul>
            </div>
            <div class="bid_sites bbe">
                <div class="h40 lh40 pl10 f14 fb bg-white cf"><i class="mr5 iconfont icon-xiajiantou f12"></i><span>政府主办</span></div>
                <ul id="bid_sites_2" class="dn"></ul>
            </div>
            <div class="bid_sites bbe">
                <div class="h40 lh40 pl10 f14 fb bg-white cf"><i class="mr5 iconfont icon-xiajiantou f12"></i><span>招标企业</span></div>
                <ul id="bid_sites_3" class="dn"></ul>
            </div>
            <div class="bid_sites bbe">
                <div class="h40 lh40 pl10 f14 fb bg-white cf"><i class="mr5 iconfont icon-xiajiantou f12"></i><span>学校官网</span></div>
                <ul id="bid_sites_4" class="dn"></ul>
            </div>
            <div class="bid_sites bbe">
                <div class="h40 lh40 pl10 f14 fb bg-white cf"><i class="mr5 iconfont icon-xiajiantou f12"></i><span>公众号</span></div>
                <ul id="bid_sites_5" class="dn"></ul>
            </div>
        </div>
    </div>
    <script type="text/javascript" src="/statics/js/dist/bid-site-and-keyword.js?v=2026102701"></script>
</body>

</html>
