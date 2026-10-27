<?= view('header') ?>
<style>
    .custom {
        background-color: #fff;
        overflow: hidden;
    }

    .custom-tit {
        height: 30px;
        padding: 10px;
        line-height: 30px;
        text-align: left;
    }

    .custom-con {
        min-height: 40px;
        overflow: hidden;
    }

    .custom-item {
        width: 100%;
        margin: 0 0 10px;
        background-color: #f5f5f5;
        border-radius: 3px;
        overflow: hidden;
    }

    .custom-item:hover {
        background-color: #ccc;
        transition: all .5s;
    }

    .custom-item-tit {
        height: 30px;
        padding: 10px 10px 0;
        font-weight: bold;
        line-height: 30px;
    }

    .custom-item-con {
        min-height: 70px;
        padding: 10px;
        overflow: hidden;
    }

    .custom-item-staff-con {
        float: left;
        width: 100%;
        margin: 10px 0 0;
        overflow: hidden;
    }

    .custom-item-staff-info {
        float: left;
        width: 120px;
        height: 150px;
        margin: 10px 10px 0 0;
        color: #fff;
        background-color: #01ad9c99;
        border-radius: 3px;
        overflow: hidden;
    }

    .custom-rate {
        position: relative;
        height: 14px;
        background: #f5f5f5;
        border-radius: 3px;
        box-shadow: inset 0 0 0 1px #00000011;
        overflow: hidden;
    }

    .custom-rate-bar {
        width: 1px;
        height: 14px;
        border-radius: 3px;
    }

    .custom-rate-text {
        position: absolute;
        z-index: 1;
        top: 0;
        left: 0;
        right: 0;
        font-size: 80%;
        height: 14px;
        line-height: 14px;
        color: #01ad9c;
    }
</style>
</head>

<body>
    <div class="wrapper">
        <div class="mod-search cf">
            <div class="fl">
                <ul class="ul-inline">
                    <li>
                        <span class="db fl mr-2 h28 lh28 pl10 pr10 teal bg-white bde b-r-3-left">选择年份</span>
                        <span id="combo1"></span>
                    </li>
                </ul>
            </div>
            <div class="fl ml1 ui-btn-group">
                <a id="search" class="ui-btn dn"><i class="iconfont icon-chaxun"></i><span>查询</span></a>
            </div>
            <div class="fr ui-btn-group">
                <a id="refresh" class="ui-btn"><i class="iconfont icon-shuaxin"></i><span>刷新</span></a>
            </div>
        </div>
        <div class="diy-con">
            <div class="custom">
                <div class="custom-tit">
                    <div id="yearTargetTit" class="fl f16 fb"></div>
                    <div id="yearTargetBtn" class="fl ml20">
                        <div id="makeYearTarget" class="fl ui-btn bg-green"><i class="iconfont icon-mubiao"></i><span>制定</span></div>
                    </div>
                    <div id="yearTargetDes" class="fl ml10 pl10 pr10 gray bg-silver b-r-3"></div>
                </div>
                <div id="yearTargetCon" class="custom-con pl10 pr10 pb10"></div>
            </div>
            <div class="pl10 pr10 pb10 lh20 teal bg-white b-r-3">
                <p><i class="iconfont icon-bangzhu f12 mr5"></i>基准说明：总体销售目标完成量以有效销售订单与特殊销售单的单据日期为基准；销售人员的销售目标完成量以有效署名销售订单的单据日期、有效署名已中标投标项目的录入时间、以及后期录入的网店业绩的对应月份为基准。</p>
                <p><i class="iconfont icon-bangzhu f12 mr5"></i>特别说明：销售目标完成情况不等于业绩，两者统计意图、统计对象、统计方法、及时性、时间宽容度等均无法相同。</p>
            </div>
            <div class="custom bte">
                <div class="custom-tit">
                    <div id="monthTargetTit" class="fl f16 fb"></div>
                    <div id="monthTargetBtn" class="fl ml20">
                        <div id="makeMonthTarget" class="fl ui-btn bg-teal"><i class="iconfont icon-mubiao"></i><span>制定</span></div>
                    </div>
                </div>
                <div id="monthTargetCon" class="custom-con pl10 pr10"></div>
            </div>
        </div>
    </div>
    <script type="text/javascript" src="/statics/js/dist/sales-target.js?v=2026102701"></script>
</body>

</html>
