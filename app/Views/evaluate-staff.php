<?= view('header') ?>
<link rel="stylesheet" type="text/css" href="/statics/css/manage.css?v=2026102701" />
</head>

<body>
    <div class="manage-wrapper">
        <div class="ovh bte bbe">
            <div class="fl bg-silver bre" style="width:900px;">
                <div class="mod-search cf">
                    <div class="fl">
                        <div class="fl gray">员工姓名：</div>
                        <div id="name" class="fl w60 f14 fb"></div>
                        <div class="fl ui-btn-group">
                            <a id="lastMonth" class="ui-btn current"><i class="iconfont icon-chaxun"></i><span>上月</span></a>
                            <a id="lastMonth2" class="ui-btn"><i class="iconfont icon-chaxun"></i><span>上上月</span></a>
                        </div>
                        <div class="fl ml10 gray">考评人：</div>
                        <div id="lastName" class="w50 fl gray"></div>
                        <div class="fl ml10 gray">考评时间：</div>
                        <div id="lastTime" class="fl gray"></div>
                    </div>
                </div>
                <div class="grid-wrap">
                    <table id="grid"></table>
                </div>
            </div>
            <div class="fr ovh" style="width:calc(100% - 901px);height:calc(100vh - 54px);">
                <div class="mod-search cf">
                    <div class="fl fb">其他参考信息</div>
                    <div class="fr">
                        <ul class="fl ul-inline">
                            <li class="ui-btn-group">
                                <a id="refresh" class="ui-btn"><i class="iconfont icon-shuaxin"></i><span>刷新</span></a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="ovh" style="height:calc(100% - 44px);overflow-y:auto;">
                    <div>
                        <div class="p10 fb lh24 night bg-silver">近半年考评得分</div>
                        <div id="scoreHis" class="p10 lh24 ovh">查询中...</div>
                    </div>
                    <div>
                        <div class="p10 fb lh24 night bg-silver">出勤信息</div>
                        <div id="attendance" class="p10 lh24 ovh">查询中...</div>
                    </div>
                    <div>
                        <div class="p10 fb lh24 night bg-silver">配货信息</div>
                        <div id="performancePro" class="p10 lh24 ovh">查询中...</div>
                    </div>
                    <div>
                        <div class="p10 fb lh24 night bg-silver">打包信息</div>
                        <div id="performancePack" class="p10 lh24 ovh">查询中...</div>
                    </div>
                    <div>
                        <div class="p10 fb lh24 night bg-silver">发票录入</div>
                        <div id="invoice" class="p10 lh24 ovh">查询中...</div>
                    </div>
                    <div>
                        <div class="p10 fb lh24 night bg-silver">采购建议</div>
                        <div id="purSuggest" class="p10 lh24 ovh">查询中...</div>
                    </div>
                    <div>
                        <div class="p10 fb lh24 night bg-silver">反馈责任</div>
                        <div id="serviceTarget" class="p10 lh24 ovh">查询中...</div>
                    </div>
                    <div>
                        <div class="p10 fb lh24 night bg-silver">工作记录</div>
                        <div id="workRecord" class="p10 lh24 ovh">查询中...</div>
                    </div>
                    <div>
                        <div class="p10 fb lh24 night bg-silver">工作目标</div>
                        <div id="workPlan" class="p10 lh24 ovh">查询中...</div>
                    </div>
                </div>
            </div>
            <div class="fl pct100 bg-silver">
                <ul class="mod-form-rows mb10">
                    <li class="row-item" style="width:200px;">
                        <div class="label-wrap"><label for="score" class="f14 fb dark">主观评分</label></div>
                        <div class="ctn-wrap">
                            <input type="number" step="1" class="ui-input" name="score" id="score" placeholder="1 ~ 110" title="考评得分将影响下属员工的当月考评工资，请认真对待。打分标准：表现非常优异贡献突出的100~110分，表现优秀的90~99分，表现良好的80~89，表现差强人意的80分以下。" />
                            <span class="unit">分</span>
                        </div>
                    </li>
                    <li class="row-item" style="width:calc(100% - 230px);">
                        <div class="label-wrap"><label for="description" class="f14 fb dark">综合评语</label></div>
                        <div class="ctn-wrap">
                            <input type="text" class="ui-input" name="description" id="description" placeholder="视情况选择性填写" title="视情况选择性填写" />
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <script type="text/javascript" src="/statics/js/dist/evaluate-staff.js?v=2026102701"></script>
</body>

</html>
