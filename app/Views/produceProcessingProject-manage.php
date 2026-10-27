<?= view('header') ?>
<<<<<<< HEAD
<link rel="stylesheet" type="text/css" href="/statics/css/manage.css?v=2026102701" />
=======
<link rel="stylesheet" type="text/css" href="/statics/css/manage.css?v=2026101403" />
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
</head>

<body>
    <div class="manage-wrapper">
        <form id="manage-form" action="">
            <ul class="mod-form-rows">
                <li class="row-item item-pct20">
                    <div class="label-wrap"><label for="type">项目类型</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><span id="type"></span></div>
                </li>
                <li class="row-item item-pct80">
                    <div class="label-wrap"><label for="goodsText">项目名称</label><span class="must">●</span></div>
                    <div class="ctn-wrap" id="goods">
                        <input type="text" name="goodsText" id="goodsText" class="ui-input def" style="width:calc(100% - 92px);" autocomplete="off" value="" readonly="readonly" placeholder="点击直接选择产品" required />
                        <i class="ui-icon-ellipsis iconfont icon-shenglvehao"></i>
                    </div>
                </li>
                <li class="row-item item-pct20">
                    <div class="label-wrap"><label for="tPointNum">贴片焊点</label><span class="must">●</span></div>
                    <div class="ctn-wrap">
                        <input type="number" step="1" class="ui-input" name="tPointNum" id="tPointNum" placeholder="输入贴片焊点数" />
                        <span class="unit">个</span>
                    </div>
                </li>
                <li class="row-item item-pct20">
                    <div class="label-wrap"><label for="zPointNum">直插焊点</label><span class="must">●</span></div>
                    <div class="ctn-wrap">
                        <input type="number" step="1" class="ui-input" name="zPointNum" id="zPointNum" placeholder="输入直插焊点数" />
                        <span class="unit">个</span>
                    </div>
                </li>
                <li class="row-item item-pct80 dn">
                    <div class="label-wrap"><label for="name">项目名称</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input" name="name" id="name" placeholder="请直接填写项目名称" /></div>
                </li>
                <li class="row-item item-md item-pct40 dn">
                    <div class="label-wrap"><label for="donePerHourNum">每小时可完成数</label><span class="must">●</span></div>
                    <div class="ctn-wrap">
                        <input type="number" step="1" class="ui-input" name="donePerHourNum" id="donePerHourNum" placeholder="输入实测的每小时可完成数" />
                        <span class="unit">套</span>
                    </div>
                </li>
                <li class="row-item item-pct20">
                    <div class="label-wrap"><label for="needTest">测试工序</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><span id="needTest" title="暂不允许手动设置"></span></div>
                </li>
                <li class="row-item item-pct20">
                    <div class="label-wrap"><label for="producePrice">提成单价</label><span class="must">●</span></div>
                    <div class="ctn-wrap">
                        <input type="number" step="0.00001" value="" class="ui-input ui-input-dis money" name="producePrice" id="producePrice" title="保存时自动计算，暂不允许手动设置" placeholder="保存时自动计算" disabled />
                        <span class="unit">元</span>
                    </div>
                </li>
                <li class="row-item item-pct20">
                    <div class="label-wrap"><label for="isDisable">项目状态</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><span id="isDisable"></span></div>
                </li>
                <div class="db cf"></div>
                <li class="row-item item-pct25">
                    <div class="label-wrap"><label for="createTime">最初录入</label></div>
                    <div class="ctn-wrap">
                        <input type="text" class="ui-input ui-input-dis" name="createTime" id="createTime" disabled />
                    </div>
                </li>
                <li class="row-item item-pct30">
                    <div class="label-wrap"><label for="modifyTime">最后编辑</label></div>
                    <div class="ctn-wrap">
                        <input type="text" class="ui-input ui-input-dis" name="modifyTime" id="modifyTime" disabled />
                    </div>
                    <div id="modifyHis" class="pl5 pr5 h20 lh20 hover-teal b-r-3" style="position:absolute;z-index:1;top:4px;right:4px;">编辑历史</div>
                </li>
                <li class="row-item dn">
                    <div class="w200 lh28 ti10 dark bg-white bde b-r-3-top mb-1">每小时可完成数实测记录</div>
                    <div class="ctn-wrap">
                        <textarea id="donePerHourTestInfo" class="ui-input" spellcheck="false" placeholder="添加每小时可完成数实测记录"></textarea>
                    </div>
                </li>
                <li class="row-item">
                    <div class="w80 lh28 ti10 dark bg-white bde b-r-3-top mb-1">备注信息</div>
                    <div class="ctn-wrap">
                        <textarea id="description" class="ui-input" spellcheck="false" placeholder="请输入备注信息"></textarea>
                    </div>
                </li>
            </ul>
        </form>
    </div>
<<<<<<< HEAD
    <script type="text/javascript" src="/statics/js/plugins/uploader/jquery.uploader.js?v=2026102701"></script>
    <script type="text/javascript" src="/statics/js/dist/produceProcessingProject-manage.js?v=2026102701"></script>
=======
    <script type="text/javascript" src="/statics/js/plugins/uploader/jquery.uploader.js?v=2026101403"></script>
    <script type="text/javascript" src="/statics/js/dist/produceProcessingProject-manage.js?v=2026101403"></script>
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
</body>

</html>
