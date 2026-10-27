<?= view('header') ?>
<link rel="stylesheet" type="text/css" href="/statics/css/manage.css?v=2026102701" />
</head>

<body>
    <div class="manage-wrapper">
        <form id="manage-form" action="">
            <ul class="mod-form-rows">
                <li class="row-item">
                    <div class="label-wrap"><label for="keywordType">类型</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><span id="keywordType"></span></div>
                </li>
                <li class="row-item">
                    <div class="label-wrap"><label for="name">关键词</label><span class="must">●</span></div>
                    <div class="ctn-wrap rel"><input type="text" value="" class="ui-input" name="name" id="name" onkeypress="return isValidChar(event)" /></div>
                </li>
            </ul>
        </form>
        <div class="mt10 ml10 mr10 p10 lh20 night bg-silver b-r-3">
            除正常单词汇外，也允许同时包含<span class="red">多词汇</span>，中间用<span class="red">空格</span>间隔，来提高采集精确度。
            <br>
            例如：“技能 实训”作为包含关键词，同时包含这些词汇的招标信息将被提取出来。
            <br>
            例如：“医学 耗材”作为排除关键词，同时包含这些词汇的招标信息将被排除出去。
            <br>
            <br>
            <span class="red">特别说明</span>：政采云网页显示的招标标题与其原始数据的招标标题有时候是不一样的，网页展示给用户看时（比如招标信息详情页）会拼接招标类型名称到标题尾部，例如"原始标题xxx"+"竞价公告"作为展示标题，所以查询政采云某条招标信息我们有没有收录到，或设计关键词时，要忽略尾部拼接的招标类型名称，否则无法命中。
        </div>
    </div>
    <script type="text/javascript" src="/statics/js/dist/bid-keyword-manage.js?v=2026102701"></script>
</body>

</html>
