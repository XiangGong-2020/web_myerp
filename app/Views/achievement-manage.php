<?= view('header') ?>
<<<<<<< HEAD
<link rel="stylesheet" type="text/css" href="/statics/css/bills.css?v=2026102701" />
=======
<link rel="stylesheet" type="text/css" href="/statics/css/bills.css?v=2026101403" />
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
</head>

<body>
    <div class="wrapper">
        <div class="bill-tool-top">
            <div id="mark"></div>
            <div id="exportByCompany" class="dn">
                <ul class="fl ul-inline">
                    <span class="db fl mr-2 h28 lh28 pl5 pr5 gray bg-white bde b-r-3-left">经营主体</span>
                    <li><span id="companyCombo"></span></li>
                </ul>
                <div class="fl ml1 ui-btn-group">
                    <a id="exportCompanyAchievementExcel" class="ui-btn bg-teal"><i class="iconfont icon-daochu"></i><span>导出该经营主体工资月表</span></a>
                </div>
            </div>
            <div id="toolTop" class="fr ui-btn-group"></div>
        </div>
        <div class="bills">
            <div class="con-header">
                <dl class="cf">
                    <dd class="mr10">
                        <label>单据编号</label>
                        <input type="text" name="billNo" id="billNo" class="ui-input ui-input-dis" value="" disabled />
                    </dd>
                    <dd class="mr10">
                        <label>当月起始日</label>
                        <input type="text" value="" id="beginDate" class="ui-input ui-datepicker-input ui-input-dis" autocomplete="off" disabled />
                    </dd>
                    <dd class="mr10">
                        <label>当月结束日</label>
                        <input type="text" value="" id="endDate" class="ui-input ui-datepicker-input ui-input-dis" autocomplete="off" disabled />
                    </dd>
                </dl>
            </div>
            <div class="page-line"></div>
            <div class="mt20 fb gray b-r-3"><span class="yellow">汇总工资</span> = 出勤工资（日薪 * 出勤天数）+ 考评工资（各员工相应考评工资基数 * 出勤率百分比，目前采购部再*0.7、生产部再*考评分/100）+ 绩效工资（对应业绩指数 * 对应绩效工资转化系数（采购部是0.3））+ 值班工资 + 加班工资 + 计时工资 + 全勤奖金 + 投标奖金 +（岗位津贴 * 出勤率百分比）- 社保扣除 - 其他扣除（汇总工资为系统根据各项数据自动计算）。<br><br><span class="green">实发工资</span> = 汇总工资 + 淡季津贴 ± 其他主观调整金额（最终实发工资以管理层手动录入的金额为准）。</div>
            <div class="page-line"></div>
            <div class="grid-wrap bde b-r-3">
                <table id="grid"></table>
                <div id="page"></div>
            </div>
            <div class="mt10">
                <div class="description">
                    <textarea id="description" class="ui-input" spellcheck="false" placeholder="添加整单备注信息"></textarea>
                    <div id="desBtn" class="ui-btn bg-green dn"><i class="iconfont icon-baocun"></i><span>仅更新备注</span></div>
                </div>
            </div>
            <div class="con-footer">
                <ul class="cf">
                    <li><label>制单人</label><span id="uName"></span></li>
                    <li><label>制单时间</label><span id="createTime"></span></li>
                    <li><label>最后编辑</label><span id="modifyTime"></span></li>
                </ul>
            </div>
        </div>
    </div>
<<<<<<< HEAD
    <script type="text/javascript" src="/statics/js/dist/achievement-manage.js?v=2026102701"></script>
=======
    <script type="text/javascript" src="/statics/js/dist/achievement-manage.js?v=2026101403"></script>
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
</body>

</html>
