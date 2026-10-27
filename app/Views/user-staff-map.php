<?= view('header') ?>
<link rel="stylesheet" type="text/css" href="/statics/css/bills.css?v=2026102701" />
<style>
    .group-level-1,
    .group-level-2,
    .group-level-3 {
        position: relative;
        left: 50%;
        transform: translateX(-50%);
        width: 100%;
        text-align: center;
        background: #fff;
    }

    .group {
        float: left;
        position: relative;
        width: calc(14.2% - 50px);
        padding: 20px;
        margin: 10px 0 10px 10px;
        background-color: #00000011;
        border-radius: 10px;
    }

    .group-tit {
        margin: 0 0 20px;
        font-size: 16px;
        text-align: center;
        line-height: 30px;
        color: #999;
    }

    .staff-con {
        display: inline-block;
        width: 90px;
        padding: 10px;
        margin: 10px;
        color: #999;
        background-color: #fff;
        border-radius: 10px;
        overflow: hidden;
        cursor: pointer;
    }

    .staff-level-1 {
        box-shadow: 0 5px 10px #2c65ff55;
    }

    .staff-level-2 {
        box-shadow: 0 5px 10px #2c65ff33;
    }

    .staff-level-3 {
        box-shadow: 0 2px 5px #00000022;
    }

    .staff-con:hover {
        color: #fff;
        background-color: #2c65ff;
        transition: .3s;
    }

    .staff-avatar {
        float: left;
        width: 90px;
        height: 90px;
        margin: 0 0 10px;
        border-radius: 5px;
    }

    .staff-onduty {
        float: left;
        margin-top: -14px;
        margin-left: 50%;
        transform: translateX(-50%);
        width: 90px;
        height: 24px;
        font-size: 12px;
        text-align: center;
        line-height: 24px;
        overflow: hidden;
    }

    .staff-name {
        float: left;
        width: 90px;
        font-size: 20px;
        font-weight: bold;
        text-align: center;
        line-height: 30px;
    }

    .staff-position {
        float: left;
        width: 90px;
        font-size: 12px;
        text-align: center;
        line-height: 20px;
    }

    .staff-mobile {
        float: left;
        width: 90px;
        font-size: 12px;
        text-align: center;
        line-height: 30px;
    }

    .staff-side {
        position: fixed;
        top: 50px;
        right: -500px;
        width: 360px;
        padding: 30px;
        color: #000;
        background-color: #fff;
        border-radius: 10px 0 0 10px;
        box-shadow: 0 0 20px #00000033;
    }

    .staff-side-close {
        position: absolute;
        top: 10px;
        right: 10px;
        width: 30px;
        height: 30px;
        line-height: 30px;
        text-align: center;
        color: #f00;
        cursor: pointer;
    }

    .staff-side-avatar {
        width: 150px;
        height: 150px;
        margin: 0 0 20px 0;
        border-radius: 5px;
    }

    .staff-side-avatar-right {
        float: right;
        width: 180px;
        height: 150px;
        margin: 0 0 20px 0;
        border-radius: 5px;
    }

    .staff-side-tit {
        float: left;
        width: 70px;
        height: 24px;
        margin: 0 0 10px;
        font-size: 14px;
        line-height: 24px;
        font-weight: bold;
        color: #999;
    }

    .staff-side-text {
        float: left;
        width: 110px;
        height: 24px;
        margin: 0 0 10px;
        font-size: 12px;
        line-height: 24px;
        white-space: pre-wrap;
    }

    .staff-side-textarea {
        float: left;
        width: 100%;
        margin: 0 0 10px;
        font-size: 12px;
        line-height: 24px;
        white-space: pre-wrap;
    }
</style>
</head>

<body>
    <div class="wrapper">
        <div class="mod-search cf">
            <div class="fr ui-btn-group">
                <a id="refresh" class="ui-btn"><i class="iconfont icon-shuaxin"></i><span>刷新</span></a>
            </div>
        </div>
        <div class="diy-con rel bg-white">
            <div class="group-level-1 pt30"></div>
            <div class="group-level-2 pt30"></div>
            <div class="group-level-3 ovh">
                <div class="group">
                    <div class="group-tit">销售部</div>
                    <div id="saleGroup"></div>
                </div>
                <div class="group">
                    <div class="group-tit">采购部</div>
                    <div id="purGroup"></div>
                </div>
                <div class="group">
                    <div class="group-tit">生产部</div>
                    <div id="proGroup"></div>
                </div>
                <div class="group">
                    <div class="group-tit">仓储部</div>
                    <div id="stoGroup"></div>
                </div>
                <div class="group">
                    <div class="group-tit">技术部</div>
                    <div id="tecGroup"></div>
                </div>
                <div class="group">
                    <div class="group-tit">运营部</div>
                    <div id="opeGroup"></div>
                </div>
                <div class="group">
                    <div class="group-tit">其他部门</div>
                    <div id="otherGroup"></div>
                </div>
            </div>
        </div>
        <div class="staff-side"></div>
    </div>
    <script type="text/javascript" src="/statics/js/dist/user-staff-map.js?v=2026102701"></script>
</body>

</html>
