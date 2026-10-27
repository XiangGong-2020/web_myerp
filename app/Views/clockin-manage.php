<?= view('header') ?>
<link rel="stylesheet" type="text/css" href="/statics/css/manage.css?v=2026102701" />
<link rel="stylesheet" type="text/css" href="/statics/js/plugins/fSelect/fSelect.css?v=2026102701" />
</head>

<body>
    <div class="manage-wrapper">
        <form id="manage-form" action="">
            <ul class="mod-form-rows">
                <li class="row-item item-pct50">
                    <div class="label-wrap"><label for="uName">姓名</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><span id="uName"></span></div>
                </li>
                <li class="row-item item-pct50">
                    <div class="label-wrap"><label for="clockDate">日期</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input ui-datepicker-input" name="clockDate" id="clockDate" autocomplete="off" readonly /></div>
                </li>
                <li class="row-item item-pct50">
                    <div class="label-wrap"><label for="amClock">上班打卡</label></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input ui-timepicker-input" name="amClock" id="amClock" autocomplete="off" readonly /></div>
                </li>
                <li class="row-item item-pct50">
                    <div class="label-wrap"><label for="pmClock">下班打卡</label></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input ui-timepicker-input" name="pmClock" id="pmClock" autocomplete="off" readonly /></div>
                </li>
                <li class="row-item item-pct50">
                    <div class="label-wrap"><label for="amClock2">二次上班卡</label></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input ui-timepicker-input" name="amClock2" id="amClock2" autocomplete="off" readonly /></div>
                </li>
                <li class="row-item item-pct50">
                    <div class="label-wrap"><label for="pmClock2">二次下班卡</label></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input ui-timepicker-input" name="pmClock2" id="pmClock2" autocomplete="off" readonly /></div>
                </li>
                <li class="row-item item-pct50">
                    <div class="label-wrap"><label for="amClock3">三次上班卡</label></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input ui-timepicker-input" name="amClock3" id="amClock3" autocomplete="off" readonly /></div>
                </li>
                <li class="row-item item-pct50">
                    <div class="label-wrap"><label for="pmClock3">三次下班卡</label></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input ui-timepicker-input" name="pmClock3" id="pmClock3" autocomplete="off" readonly /></div>
                </li>
                <li class="row-item">
                    <div class="label-wrap"><label for="marks">标记与申请</label></div>
                    <div class="ctn-wrap">
                        <select id="marksCombo" class="hidden" multiple="multiple">
                            <option value="补卡">补卡</option>
                            <option value="请假">请假</option>
                            <option value="调休">调休</option>
                            <option value="补休">补休</option>
                            <option value="外勤">外勤</option>
                            <option value="值班">值班</option>
                            <option value="加班">加班</option>
                            <option value="调班">调班</option>
                            <option value="补班">补班</option>
                            <option value="计时">计时</option>
                            <option value="迟到">迟到</option>
                            <option value="早退">早退</option>
                            <option value="公事">公事</option>
                            <option value="其他">其他</option>
                        </select>
                    </div>
                </li>
                <li class="row-item">
                    <div class="w80 lh28 ti10 gray bg-white bde b-r-3-top mb-1">备注信息</div>
                    <div class="ctn-wrap">
                        <textarea id="description" class="ui-input" style="height:218px;" spellcheck="false" placeholder="添加备注信息"></textarea>
                    </div>
                </li>
            </ul>
        </form>
    </div>
    <script type="text/javascript" src="/statics/js/plugins/fSelect/fSelect.js?v=2026102701"></script>
    <script type="text/javascript" src="/statics/js/dist/clockin-manage.js?v=2026102701"></script>
</body>

</html>
