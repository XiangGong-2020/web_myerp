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
                <li class="row-item item-pct33">
                    <div class="label-wrap"><label for="name">联系人</label><span class="must">●</span></div>
                    <div class="ctn-wrap rel"><input type="text" value="" class="ui-input" name="name" id="name" /></div>
                </li>
                <li class="row-item item-pct33">
                    <div class="label-wrap"><label for="mobile">手机号</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input" name="mobile" id="mobile" /></div>
                </li>
                <li class="row-item item-pct33">
                    <div class="label-wrap"><label for="phone">座机号</label></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input" name="phone" id="phone" /></div>
                </li>
                <li class="row-item item-pct33">
                    <div class="label-wrap"><label for="qq">QQ号</label></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input" name="qq" id="qq" /></div>
                </li>
                <li class="row-item item-pct33">
                    <div class="label-wrap"><label for="wechat">微信号</label></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input" name="wechat" id="wechat" /></div>
                </li>
                <li class="row-item item-pct33">
                    <div class="label-wrap"><label for="beginDate">首次合作</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input ui-datepicker-input" name="beginDate" id="beginDate" autocomplete="off" readonly /></div>
                </li>
                <li class="row-item item-pct33">
                    <div class="label-wrap"><label for="province">所在省级</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><span id="province"></span></div>
                </li>
                <li class="row-item item-pct33">
                    <div class="label-wrap"><label for="city">所在地级</label></div>
                    <div class="ctn-wrap"><span id="city"></span></div>
                </li>
                <li class="row-item item-pct33">
                    <div class="label-wrap"><label for="area">所在县级</label></div>
                    <div class="ctn-wrap"><span id="area"></span></div>
                </li>
                <li class="row-item">
                    <div class="label-wrap"><label for="address">详细地址</label></div>
                    <div class="ctn-wrap rel"><input type="text" value="" class="ui-input" name="address" id="address" /></div>
                </li>
                <li class="row-item">
                    <div class="w80 lh28 ti10 gray bg-white bde b-r-3-top mb-1">备注信息</div>
                    <div class="ctn-wrap">
                        <textarea id="description" class="ui-input" spellcheck="false" placeholder="添加备注信息"></textarea>
                    </div>
                </li>
            </ul>
        </form>
        <div class="mt10 ml10 mr10">
            <div class="upfile ovh">
                <div class="upfile-con b-r-3-left">
                    <label class="upfile-title">上传资料</label>
                    <button type="button" id="upFile" class="choosefile iconfont icon-shangchuan"></button>
                </div>
                <div id="files" class="files b-r-3-right"></div>
                <div id="queue"></div>
            </div>
        </div>
    </div>
<<<<<<< HEAD
    <script type="text/javascript" src="/statics/js/plugins/uploader/jquery.uploader.js?v=2026102701"></script>
    <script type="text/javascript" src="/statics/js/dist/bid-service-manage.js?v=2026102701"></script>
=======
    <script type="text/javascript" src="/statics/js/plugins/uploader/jquery.uploader.js?v=2026101403"></script>
    <script type="text/javascript" src="/statics/js/dist/bid-service-manage.js?v=2026101403"></script>
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
</body>

</html>
