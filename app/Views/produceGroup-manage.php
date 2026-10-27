<?= view('header') ?>
<link rel="stylesheet" type="text/css" href="/statics/css/manage.css?v=2026102701" />
<link rel="stylesheet" type="text/css" href="/statics/js/plugins/fSelect/fSelect.css?v=2026102701" />
</head>

<body>
    <div class="manage-wrapper">
        <form id="manage-form" action="">
            <ul class="mod-form-rows">
                <li class="row-item">
                    <div class="label-wrap"><label for="name">组名</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input" name="name" id="name" /></div>
                </li>
                <li class="row-item">
                    <div class="label-wrap"><label for="members">组员</label></div>
                    <div class="ctn-wrap">
                        <select id="membersCombo" class="hidden" multiple="multiple">
                            <optgroup id="normalUser" label="生产部在职员工"></optgroup>
                            <optgroup id="disableUser" label="生产部60天内离职员工"></optgroup>
                        </select>
                    </div>
                </li>
                <li class="row-item">
                    <div class="label-wrap"><label for="type">在职状态</label><span class="must">●</span></div>
                    <div class="ctn-wrap rel"><span id="type"></span></div>
                </li>
                <li class="row-item">
                    <div class="label-wrap"><label for="CoCNo">合格证编号</label></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input" name="CoCNo" id="CoCNo" /></div>
                </li>
                <li class="row-item">
                    <div class="w80 lh28 ti10 gray bg-white bde b-r-3-top mb-1">备注信息</div>
                    <div class="ctn-wrap">
                        <textarea id="description" class="ui-input" style="height:178px;" spellcheck="false" placeholder="添加备注信息"></textarea>
                    </div>
                </li>
            </ul>
        </form>
    </div>
    <script type="text/javascript" src="/statics/js/plugins/fSelect/fSelect.js?v=2026102701"></script>
    <script type="text/javascript" src="/statics/js/dist/produceGroup-manage.js?v=2026102701"></script>
</body>

</html>
