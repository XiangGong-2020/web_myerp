<?= view('header') ?>
<link rel="stylesheet" type="text/css" href="/statics/css/manage.css?v=2026102701" />
</head>

<body>
    <div class="manage-wrapper">
        <form id="manage-form" action="">
            <ul class="mod-form-rows">
                <li class="row-item item-md">
                    <div class="label-wrap"><label for="year">对应年份</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><span id="year"></span></div>
                </li>
                <li class="row-item item-md item-pct50">
                    <div class="label-wrap"><label for="m1">1月应出勤</label><span class="must">●</span></div>
                    <div class="ctn-wrap">
                        <input type="number" value="" class="ui-input" name="m1" id="m1" autocomplete="off" />
                        <span class="unit">天</span>
                    </div>
                </li>
                <li class="row-item item-md item-pct50">
                    <div class="label-wrap"><label for="m2">2月应出勤</label><span class="must">●</span></div>
                    <div class="ctn-wrap">
                        <input type="number" value="" class="ui-input" name="m2" id="m2" autocomplete="off" />
                        <span class="unit">天</span>
                    </div>
                </li>
                <li class="row-item item-md item-pct50">
                    <div class="label-wrap"><label for="m3">3月应出勤</label><span class="must">●</span></div>
                    <div class="ctn-wrap">
                        <input type="number" value="" class="ui-input" name="m3" id="m3" autocomplete="off" />
                        <span class="unit">天</span>
                    </div>
                </li>
                <li class="row-item item-md item-pct50">
                    <div class="label-wrap"><label for="m4">4月应出勤</label><span class="must">●</span></div>
                    <div class="ctn-wrap">
                        <input type="number" value="" class="ui-input" name="m4" id="m4" autocomplete="off" />
                        <span class="unit">天</span>
                    </div>
                </li>
                <li class="row-item item-md item-pct50">
                    <div class="label-wrap"><label for="m5">5月应出勤</label><span class="must">●</span></div>
                    <div class="ctn-wrap">
                        <input type="number" value="" class="ui-input" name="m5" id="m5" autocomplete="off" />
                        <span class="unit">天</span>
                    </div>
                </li>
                <li class="row-item item-md item-pct50">
                    <div class="label-wrap"><label for="m6">6月应出勤</label><span class="must">●</span></div>
                    <div class="ctn-wrap">
                        <input type="number" value="" class="ui-input" name="m6" id="m6" autocomplete="off" />
                        <span class="unit">天</span>
                    </div>
                </li>
                <li class="row-item item-md item-pct50">
                    <div class="label-wrap"><label for="m7">7月应出勤</label><span class="must">●</span></div>
                    <div class="ctn-wrap">
                        <input type="number" value="" class="ui-input" name="m7" id="m7" autocomplete="off" />
                        <span class="unit">天</span>
                    </div>
                </li>
                <li class="row-item item-md item-pct50">
                    <div class="label-wrap"><label for="m8">8月应出勤</label><span class="must">●</span></div>
                    <div class="ctn-wrap">
                        <input type="number" value="" class="ui-input" name="m8" id="m8" autocomplete="off" />
                        <span class="unit">天</span>
                    </div>
                </li>
                <li class="row-item item-md item-pct50">
                    <div class="label-wrap"><label for="m9">9月应出勤</label><span class="must">●</span></div>
                    <div class="ctn-wrap">
                        <input type="number" value="" class="ui-input" name="m9" id="m9" autocomplete="off" />
                        <span class="unit">天</span>
                    </div>
                </li>
                <li class="row-item item-md item-pct50">
                    <div class="label-wrap"><label for="m10">10月应出勤</label><span class="must">●</span></div>
                    <div class="ctn-wrap">
                        <input type="number" value="" class="ui-input" name="m10" id="m10" autocomplete="off" />
                        <span class="unit">天</span>
                    </div>
                </li>
                <li class="row-item item-md item-pct50">
                    <div class="label-wrap"><label for="m11">11月应出勤</label><span class="must">●</span></div>
                    <div class="ctn-wrap">
                        <input type="number" value="" class="ui-input" name="m11" id="m11" autocomplete="off" />
                        <span class="unit">天</span>
                    </div>
                </li>
                <li class="row-item item-md item-pct50">
                    <div class="label-wrap"><label for="m12">12月应出勤</label><span class="must">●</span></div>
                    <div class="ctn-wrap">
                        <input type="number" value="" class="ui-input" name="m12" id="m12" autocomplete="off" />
                        <span class="unit">天</span>
                    </div>
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
    <script type="text/javascript" src="/statics/js/dist/attendance-plan-manage.js?v=2026102701"></script>
</body>

</html>
