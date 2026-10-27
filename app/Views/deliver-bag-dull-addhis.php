<?= view('header') ?>
<link rel="stylesheet" type="text/css" href="/statics/css/manage.css?v=2026102701" />
</head>

<body>
    <div class="manage-wrapper">
        <form id="manage-form" action="">
            <ul class="mod-form-rows">
                <li class="row-item item-pct33">
                    <div class="label-wrap"><label for="no">滞留运单号</label></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input ui-input-dis" name="no" id="no" readonly /></div>
                </li>
                <li class="row-item item-pct33">
                    <div class="label-wrap"><label for="company">所属快递</label></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input ui-input-dis" name="company" id="company" readonly /></div>
                </li>
                <li class="row-item item-pct33">
                    <div class="label-wrap"><label for="sellerNick">店铺标识</label></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input ui-input-dis" name="sellerNick" id="sellerNick" readonly /></div>
                </li>
                <li class="row-item">
                    <div class="w100 lh28 ti10 gray bg-white bde b-r-3-top mb-1">滞留处理记录</div>
                    <div class="ctn-wrap">
                        <textarea id="dullOperHis" class="ui-input ui-input-dis" style="height:200px" spellcheck="false" placeholder="暂无" readonly /></textarea>
                    </div>
                </li>
                <li class="row-item">
                    <div class="label-wrap"><label for="addHis">追加内容</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input" name="addHis" id="addHis"></div>
                </li>
            </ul>
        </form>
    </div>
    <script type="text/javascript" src="/statics/js/dist/deliver-bag-dull-addhis.js?v=2026102701"></script>
</body>

</html>
