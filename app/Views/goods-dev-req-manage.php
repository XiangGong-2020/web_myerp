<?= view('header') ?>
<link rel="stylesheet" type="text/css" href="/statics/css/manage.css?v=2026102701" />
</head>

<body>
    <div class="manage-wrapper">
        <form id="manage-form" action="">
            <ul class="mod-form-rows">
                <li class="row-item item-pct20">
                    <div class="label-wrap"><label for="lastDate">交付限期</label><span class="must">●</span></div>
                    <div class="ctn-wrap rel"><input type="text" value="" class="ui-input ui-datetimepicker-input" name="lastDate" id="lastDate" autocomplete="off" readonly /></div>
                </li>
                <li class="row-item item-pct80">
                    <div class="label-wrap"><label for="title">申请标题</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input" style="width:calc(100% - 92px);" name="title" id="title" placeholder="添加简要的定制申请描述作为标题" /></div>
                </li>
                <li class="row-item">
                    <div class="fl w80 lh28 ti10 gray bg-white bde b-r-3-top mb-1">附加说明</div>
                    <span id="createTime" class="fl ml10 lh28 gray"></span>
                    <div class="ctn-wrap">
                        <textarea id="content" class="ui-input" spellcheck="false" placeholder="添加申请的附加说明信息，越详细越好。"></textarea>
                    </div>
                </li>
            </ul>
        </form>
        <div class="mt10 ml10 mr10">
            <div class="upfile">
                <div class="upfile-con b-r-3-left">
                    <label class="upfile-title">参考图片</label>
                    <button type="button" id="upimg" class="choosefile iconfont icon-shangchuan"></button>
                </div>
                <div id="imgs" class="imgs b-r-3-right"></div>
            </div>
        </div>
        <div class="mt10 ml10 mr10">
            <div class="upfile">
                <div class="upfile-con b-r-3-left">
                    <label class="upfile-title">参考资料</label>
                    <button type="button" id="upfile" class="choosefile iconfont icon-shangchuan"></button>
                </div>
                <div id="files" class="files b-r-3-right"></div>
            </div>
        </div>
        <ul class="mod-form-rows">
            <li class="row-item">
                <div class="w80 lh28 ti10 gray bg-white bde b-r-3-top mb-1">回执信息</div>
                <div class="ctn-wrap">
                    <textarea id="backInfo" class="ui-input" spellcheck="false" placeholder="接手后给申请人的回执信息，例如：开发工期大约需要多久"></textarea>
                </div>
            </li>
        </ul>
        <ul class="mod-form-rows">
            <li class="row-item">
                <div class="w80 lh28 ti10 gray bg-white bde b-r-3-top mb-1">拒绝理由</div>
                <div class="ctn-wrap">
                    <textarea id="description" class="ui-input" spellcheck="false" placeholder="必须添加拒绝理由，才会被视为拒绝。"></textarea>
                </div>
            </li>
        </ul>
    </div>
    <script type="text/javascript" src="/statics/js/plugins/uploader/jquery.uploader.js?v=2026102701"></script>
    <script type="text/javascript" src="/statics/js/dist/goods-dev-req-manage.js?v=2026102701"></script>
</body>

</html>
