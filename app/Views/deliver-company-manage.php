<?= view('header') ?>
<link rel="stylesheet" type="text/css" href="/statics/css/manage.css?v=2026102701" />
</head>

<body>
    <div class="manage-wrapper">
        <form id="manage-form" action="">
            <ul class="mod-form-rows">
                <li class="row-item item-md">
                    <div class="label-wrap"><label for="name">名称（简称）</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input" name="name" id="name" /></div>
                </li>
                <li class="row-item item-md">
                    <div class="label-wrap"><label for="code100">快递100查询编码</label></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input" name="code100" id="code100" /></div>
                </li>
                <li class="row-item item-md">
                    <div class="label-wrap"><label for="len">运单规则（长度）</label></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input" name="len" id="len" /></div>
                </li>
                <li class="row-item item-md">
                    <div class="label-wrap"><label for="str">运单规则（抬头）</label></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input" name="str" id="str" /></div>
                </li>
                <li class="row-item item-md">
                    <div class="label-wrap"><label for="contactId">商家ID</label></div>
                    <div class="ctn-wrap"><input type="number" value="" class="ui-input" name="contactId" id="contactId" /></div>
                </li>
                <li class="row-item">
                    <div class="w80 lh28 ti10 gray bg-white bde b-r-3-top mb-1">备注信息</div>
                    <div class="ctn-wrap">
                        <textarea id="description" class="ui-input" spellcheck="false" placeholder="添加备注信息"></textarea>
                    </div>
                </li>
            </ul>
        </form>
    </div>
    <script type="text/javascript" src="/statics/js/dist/deliver-company-manage.js?v=2026102701"></script>
</body>

</html>
