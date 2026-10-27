<?= view('header') ?>
<link rel="stylesheet" type="text/css" href="/statics/css/manage.css?v=2026102701" />
<link rel="stylesheet" type="text/css" href="/statics/js/plugins/fSelect/fSelect.css?v=2026102701" />
<style>
    .custom-con {
        padding: 10px;
        background-color: #fff;
        border-radius: 3px;
    }

    .custom-item .label-wrap {
        width: 120px;
    }

    .custom-item .ui-input {
        width: 408px;
    }
</style>
</head>

<body>
    <div class="wrapper">
        <div class="mod-search cf">
            <div class="fr">
                <a id="refresh" class="ui-btn"><i class="iconfont icon-shuaxin"></i><span>刷新</span></a>
            </div>
        </div>
        <div class="diy-con bg-white">
            <div class="custom-con">
                <div class="custom-item">
                    <form id="manage-form" action="#">
                        <ul class="mod-form-rows">
                            <li class="row-item item-md item-pct33">
                                <div class="label-wrap"><label for="beginDate">默认起始日期</label><span class="must">●</span></div>
                                <div class="ctn-wrap"><input type="text" id="beginDate" name="beginDate" class="ui-input ui-datepicker-input" autocomplete="off" readonly /></div>
                            </li>
                            <li class="row-item item-md item-pct33">
                                <div class="label-wrap"><label for="currency">本位币</label><span class="must">●</span></div>
                                <div class="ctn-wrap"><span id="currency"></span></div>
                            </li>
                            <li class="row-item item-md item-pct33">
                                <div class="label-wrap"><label for="valMethods">存货计价方法</label><span class="must">●</span></div>
                                <div class="ctn-wrap"><span id="valMethods"></span></div>
                            </li>
                            <li class="row-item item-md item-pct33">
                                <div class="label-wrap"><label for="requiredCheckStore">出货检查库存</label><span class="must">●</span></div>
                                <div class="ctn-wrap"><span id="requiredCheckStore"></span></div>
                            </li>
                            <li class="row-item item-md item-pct33">
                                <div class="label-wrap"><label>数量小数位</label><span class="must">●</span></div>
                                <div class="ctn-wrap"><input type="number" name="qtyPlaces" id="qtyPlaces" class="ui-input" /></div>
                                <div class="unit">位</div>
                            </li>
                            <li class="row-item item-md item-pct33">
                                <div class="label-wrap"><label>单价小数位</label><span class="must">●</span></div>
                                <div class="ctn-wrap"><input type="number" name="pricePlaces" id="pricePlaces" class="ui-input" /></div>
                                <div class="unit">位</div>
                            </li>
                            <li class="row-item item-md item-pct33">
                                <div class="label-wrap"><label>金额小数位</label><span class="must">●</span></div>
                                <div class="ctn-wrap"><input type="number" name="amountPlaces" id="amountPlaces" class="ui-input" /></div>
                                <div class="unit">位</div>
                            </li>
                            <li class="row-item item-md item-pct33">
                                <div class="label-wrap"><label>月无损补卡次数 / 人</label><span class="must">●</span></div>
                                <div class="ctn-wrap"><input type="number" name="fillClockinTimes" id="fillClockinTimes" class="ui-input" /></div>
                                <div class="unit">次</div>
                            </li>
                            <li class="row-item item-md item-pct33">
                                <div class="label-wrap"><label>加班一档单价 / 小时</label><span class="must">●</span></div>
                                <div class="ctn-wrap"><input type="number" name="priceWH1" id="priceWH1" class="ui-input" /></div>
                                <div class="unit">元</div>
                            </li>
                            <li class="row-item item-md item-pct33">
                                <div class="label-wrap"><label for="priceWH1Department">加班一档对应部门</label><span class="must">●</span></div>
                                <div class="ctn-wrap">
                                    <select id="priceWH1DepartmentCombo" class="hidden" multiple="multiple">
                                        <optgroup id="priceWH1Department" label="加班一档对应部门"></optgroup>
                                    </select>
                                </div>
                            </li>
                            <li class="row-item item-md item-pct33">
                                <div class="label-wrap"><label>加班二档单价 / 小时</label><span class="must">●</span></div>
                                <div class="ctn-wrap"><input type="number" name="priceWH2" id="priceWH2" class="ui-input" /></div>
                                <div class="unit">元</div>
                            </li>
                            <li class="row-item item-md item-pct33">
                                <div class="label-wrap"><label for="priceWH2Department">加班二档对应部门</label><span class="must">●</span></div>
                                <div class="ctn-wrap">
                                    <select id="priceWH2DepartmentCombo" class="hidden" multiple="multiple">
                                        <optgroup id="priceWH2Department" label="加班二档对应部门"></optgroup>
                                    </select>
                                </div>
                            </li>
                            <li class="row-item item-md item-pct33">
                                <div class="label-wrap"><label>计时单价 / 小时</label><span class="must">●</span></div>
                                <div class="ctn-wrap"><input type="number" name="priceWR" id="priceWR" class="ui-input" /></div>
                                <div class="unit">元</div>
                            </li>
                            <li class="row-item item-md item-pct33">
                                <div class="label-wrap"><label>销售值班工资 / 次</label><span class="must">●</span></div>
                                <div class="ctn-wrap"><input type="number" name="bonusDuty" id="bonusDuty" class="ui-input" /></div>
                                <div class="unit">元</div>
                            </li>
                            <li class="row-item item-md item-pct33">
                                <div class="label-wrap"><label>全勤奖励 / 月</label><span class="must">●</span></div>
                                <div class="ctn-wrap"><input type="number" name="bonusFullAttendance" id="bonusFullAttendance" class="ui-input" /></div>
                                <div class="unit">元</div>
                            </li>
                            <li class="row-item item-md item-pct33">
                                <div class="label-wrap"><label>投标一档奖励 / 月</label><span class="must">●</span></div>
                                <div class="ctn-wrap"><input type="number" name="bonusBid1" id="bonusBid1" class="ui-input" title="投标金额大于50万且中标金额大于5万" /></div>
                                <div class="unit">元</div>
                            </li>
                            <li class="row-item item-md item-pct33">
                                <div class="label-wrap"><label>投标二档奖励 / 月</label><span class="must">●</span></div>
                                <div class="ctn-wrap"><input type="number" name="bonusBid2" id="bonusBid2" class="ui-input" title="投标金额大于100万且中标金额大于12万" /></div>
                                <div class="unit">元</div>
                            </li>
                            <li class="row-item item-md item-pct33">
                                <div class="label-wrap"><label>中标飞跃奖励 / 月</label><span class="must">●</span></div>
                                <div class="ctn-wrap"><input type="number" name="bonusBidLeap" id="bonusBidLeap" class="ui-input" title="获得中标飞跃奖章：当月与上月中标金额超去年同期39%以上" /></div>
                                <div class="unit">元</div>
                            </li>
                            <li class="row-item item-md item-pct33">
                                <div class="label-wrap"><label>社保个人缴费 / 月</label><span class="must">●</span></div>
                                <div class="ctn-wrap"><input type="number" name="deductionSocialSecurity" id="deductionSocialSecurity" class="ui-input" /></div>
                                <div class="unit">元</div>
                            </li>
                        </ul>
                    </form>
                </div>
                <div class="mt20 mb20 ovh"><a id="save" class="ui-btn bg-green fr mr10"><i class="iconfont icon-baocun"></i><span>保存</span></a></div>
            </div>
        </div>
    </div>
    <script type="text/javascript" src="/statics/js/plugins/fSelect/fSelect.js?v=2026102701"></script>
    <script type="text/javascript" src="/statics/js/dist/system-parameter.js?v=2026102701"></script>
</body>

</html>
