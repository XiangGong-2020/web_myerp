<?= view('header') ?>
<link rel="stylesheet" type="text/css" href="/statics/css/manage.css?v=2026102701" />
</head>

<body>
    <div class="manage-wrapper">
        <form id="manage-form" action="">
            <ul class="mod-form-rows">
                <li class="row-item">
                    <div class="label-wrap"><label for="title">备忘标题</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input" name="title" id="title" /></div>
                </li>
                <li class="row-item">
                    <div class="w80 lh28 ti10 gray bg-white bde b-r-3-top mb-1">备忘内容</div>
                    <div class="ctn-wrap">
                        <textarea id="content" class="ui-input" style="height:190px" spellcheck="false" placeholder="添加备忘内容"></textarea>
                    </div>
                </li>
            </ul>
        </form>
        <div class="mt10 ml10 mr10">
            <div class="upfile">
                <div class="upfile-con b-r-3-left">
                    <label class="upfile-title">备忘文件</label>
                    <button type="button" id="upfile" class="choosefile iconfont icon-shangchuan"></button>
                </div>
                <div id="files" class="files b-r-3-right"></div>
            </div>
        </div>
    </div>
    <script type="text/javascript" src="/statics/js/plugins/uploader/jquery.uploader.js?v=2026102701"></script>
    <script type="text/javascript" src="/statics/js/dist/note-manage.js?v=2026102701"></script>
</body>

</html>
