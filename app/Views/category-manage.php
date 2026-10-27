<?= view('header') ?>
<link rel="stylesheet" type="text/css" href="/statics/css/manage.css?v=2026102701" />
<link rel="stylesheet" type="text/css" href="/statics/js/plugins/fSelect/fSelect.css?v=2026102701" />
</head>

<body>
    <div class="manage-wrapper">
        <form id="manage-form" action="">
            <ul class="mod-form-rows">
                <li class="row-item dn">
                    <div class="label-wrap"><label id="categoryName" for="ParentCategory">上级分类</label></div>
                    <div class="ctn-wrap"><span id="category"></span></div>
                </li>
                <li class="row-item">
                    <div class="label-wrap"><label for="name">名称</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input" name="name" id="name" /></div>
                </li>
                <li class="row-item dn">
                    <div class="label-wrap"><label for="onlyposition">可见职位</label></div>
                    <div class="ctn-wrap">
                        <select id="onlypositionCombo" class="hidden" multiple="multiple">
                            <optgroup id="onlyposition" label="部门职位"></optgroup>
                        </select>
                    </div>
                </li>
            </ul>
        </form>
        <div id="warning"></div>
    </div>
    <script type="text/javascript" src="/statics/js/plugins/fSelect/fSelect.js?v=2026102701"></script>
    <script type="text/javascript" src="/statics/js/dist/category-manage.js?v=2026102701"></script>
</body>

</html>
