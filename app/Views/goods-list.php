<?= view('header') ?>
<style>
    .ztree {
        position: absolute;
        z-index: 1;
        right: 0;
        top: 0;
        width: 60px;
        height: 440px;
        overflow-x: hidden;
        box-shadow: -1px 0 1px 0 #e1e2e3;
    }

    .ztree:hover {
        width: 300px;
        box-shadow: -5px 0 10px 0 #2c65ff33;
        transition: all .2s;
    }

    .ztree>li {
        overflow: hidden;
    }
</style>
</head>

<body>
    <div class="wrapper">
        <div class="mod-search cf">
            <div class="fl">
                <ul class="ul-inline">
                    <li><span id="combo1"></span></li>
                    <li class="ml1">
                        <input type="text" id="skey" class="fl ui-input" placeholder="输入任意关键词" />
                    </li>
                    <li class="ml1"><span id="combo2"></span></li>
                </ul>
            </div>
            <div class="fl ml1 ui-btn-group">
                <a id="search" class="ui-btn current"><i class="iconfont icon-chaxun"></i><span>查询</span></a>
                <a id="searchErr" class="ui-btn"><i class="iconfont icon-chaxun"></i><span>查错误产品</span></a>
                <a id="searchKit2Level" class="ui-btn"><i class="iconfont icon-chaxun"></i><span>查包含子套件的套件</span></a>
            </div>
            <div class="fr ui-btn-group">
                <a id="refresh" class="ui-btn"><i class="iconfont icon-shuaxin"></i><span>刷新</span></a>
                <a id="add" class="ui-btn"><i class="iconfont icon-zengjia"></i><span>新增</span></a>
                <a id="export" class="ui-btn"><i class="iconfont icon-daochu"></i><span>导出</span></a>
                <a id="printTab" class="ui-btn"><i class="iconfont icon-dayin" title="标签打印说明（打印机选择：ZDesigner GK888t，份数：1份，布局：纵向，页面：所有，缩放：50%，边距：无，背景图形：勾选）"></i><span title="标签打印说明（打印机选择：ZDesigner GK888t，份数：1份，布局：纵向，页面：所有，缩放：50%，边距：无，背景图形：勾选）">打印标签</span></a>
            </div>
        </div>
        <div class="rel cf">
            <div id="zTree" class="grid-wrap rel">
                <table id="grid"></table>
                <div id="page"></div>
            </div>
        </div>
    </div>
</div>
<!--startprint1-->
<div class="print-area"></div>
<!--endprint1-->
<script type="text/javascript" src="/statics/js/plugins/qrcode/jquery.qrcode.min.js?v=2026102701"></script>
<script type="text/javascript" src="/statics/js/plugins/uploader/jquery.uploader.js?v=2026102701"></script>
<script type="text/javascript" src="/statics/js/dist/goods-list.js?v=2026102701"></script>
</body>

</html>
