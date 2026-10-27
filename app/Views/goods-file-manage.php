<?= view('header') ?>
<link rel="stylesheet" type="text/css" href="/statics/css/manage.css?v=2026102701" />
</head>

<body>
    <div class="manage-wrapper">
        <form id="manage-form" action="">
            <ul class="mod-form-rows">
                <li class="row-item">
                    <div class="label-wrap"><label for="no">产品编码</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input" name="no" id="no" placeholder="系统中真实存在的产品编码" /></div>
                </li>
                <li class="row-item">
                    <div class="label-wrap"><label for="name">产品文件</label><span class="must">●</span></div>
                    <div class="ctn-wrap">
                        <input type="text" value="" class="ui-input" name="name" id="name" placeholder="上传前请将源文件进行规范命名，上传后将直接沿用源文件名" readonly />
                        <button type="button" id="upFile" class="choosefile iconfont icon-shangchuan" style="left:551px"></button>
                    </div>
                    <div id="queue"></div>
                </li>
                <li class="row-item">
                    <div class="lh30 ti10 white bg-orange b-r-3">源文件命名规范：产品编码+空格+文件精确说明文字，例如：KS-56-127 三人表决器说明书.pdf</div>
                </li>
                <li class="row-item item-pct50">
                    <div class="label-wrap"><label for="authorName">编写人</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><span id="authorName"></span></div>
                </li>
                <li class="row-item item-pct50">
                    <div class="label-wrap"><label for="proofName">校对人</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><span id="proofName"></span></div>
                </li>
                <li class="row-item">
                    <div class="label-wrap"><label for="printSize">打印尺寸</label></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input" name="printSize" id="printSize" placeholder="例如：A4" /></div>
                </li>
            </ul>
        </form>
    </div>
    <script type="text/javascript" src="/statics/js/plugins/uploader/jquery.uploader.js?v=2026102701"></script>
    <script type="text/javascript" src="/statics/js/dist/goods-file-manage.js?v=2026102701"></script>
</body>

</html>
