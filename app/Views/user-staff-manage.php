<?= view('header') ?>
<<<<<<< HEAD
<link rel="stylesheet" type="text/css" href="/statics/css/manage.css?v=2026102701" />
=======
<link rel="stylesheet" type="text/css" href="/statics/css/manage.css?v=2026101403" />
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
</head>

<body>
    <div class="manage-wrapper">
        <form id="manage-form" action="#">
            <ul class="mod-form-rows">
                <li class="row-item item-pct16">
                    <div class="label-wrap"><label for="name">姓名</label><span class="must">●</span></div>
                    <div class="ctn-wrap rel"><input type="text" value="" class="ui-input" name="name" id="name" placeholder="真实身份证上姓名" /></div>
                </li>
                <li class="row-item item-pct16">
                    <div class="label-wrap"><label for="sex">性别</label><span class="must">●</span></div>
                    <div class="ctn-wrap rel"><span id="sex"></span></div>
                </li>
                <li class="row-item item-pct16">
                    <div class="label-wrap"><label for="joinDate">入职日期</label><span class="must">●</span></div>
                    <div class="ctn-wrap rel"><input type="text" value="" class="ui-input ui-datepicker-input" name="joinDate" id="joinDate" autocomplete="off" readonly /></div>
                </li>
                <li class="row-item item-pct16">
                    <div class="label-wrap"><label for="company">所属主体</label><span class="must">●</span></div>
                    <div class="ctn-wrap rel"><span id="company"></span></div>
                </li>
                <li class="row-item item-pct16">
                    <div class="label-wrap"><label for="department">所属部门</label><span class="must">●</span></div>
                    <div class="ctn-wrap rel"><span id="department"></span></div>
                </li>
                <li class="row-item item-pct16">
                    <div class="label-wrap"><label for="position">所在职位</label><span class="must">●</span></div>
                    <div class="ctn-wrap rel"><span id="position"></span></div>
                </li>
                <li class="row-item item-pct16">
                    <div class="label-wrap"><label for="superiors">上级主管</label><span class="must">●</span></div>
                    <div class="ctn-wrap rel"><span id="superiors"></span></div>
                </li>
                <li class="row-item item-pct16">
                    <div class="label-wrap"><label for="nation">民族</label></div>
                    <div class="ctn-wrap rel"><span id="nation"></span></div>
                </li>
                <li class="row-item item-pct16">
                    <div class="label-wrap"><label for="hukou">户口性质</label></div>
                    <div class="ctn-wrap rel"><span id="hukou"></span></div>
                </li>
                <li class="row-item item-pct20">
                    <div class="label-wrap"><label for="sfzNo">身份证号</label><span class="must">●</span></div>
                    <div class="ctn-wrap rel"><input type="text" value="" class="ui-input" name="sfzNo" id="sfzNo" placeholder="真实身份证号" /></div>
                </li>
                <li class="row-item item-pct30">
                    <div class="label-wrap"><label for="bankNo">银行卡</label></div>
                    <div class="ctn-wrap rel"><input type="text" value="" class="ui-input" name="bankNo" id="bankNo" placeholder="例如：建行6222XXXXXXXXXX" /></div>
                </li>
                <li class="row-item item-pct20">
                    <div class="label-wrap"><label for="zfbNo">支付宝</label></div>
                    <div class="ctn-wrap rel"><input type="text" value="" class="ui-input" name="zfbNo" id="zfbNo" placeholder="例如：123456@qq.com" /></div>
                </li>
                <li class="row-item item-pct20">
                    <div class="label-wrap"><label for="mobile">常用手机</label><span class="must">●</span></div>
                    <div class="ctn-wrap rel"><input type="text" value="" class="ui-input" name="mobile" id="mobile" placeholder="真实手机号" /></div>
                </li>
                <li class="row-item item-pct20">
                    <div class="label-wrap"><label for="wechat">常用微信</label></div>
                    <div class="ctn-wrap rel"><input type="text" value="" class="ui-input" name="wechat" id="wechat" placeholder="真实微信号" /></div>
                </li>
                <li class="row-item item-pct20">
                    <div class="label-wrap"><label for="animal">生肖</label></div>
                    <div class="ctn-wrap rel"><span id="animal"></span></div>
                </li>
                <li class="row-item item-pct20">
                    <div class="label-wrap"><label for="astro">星座</label></div>
                    <div class="ctn-wrap rel"><span id="astro"></span></div>
                </li>
                <li class="row-item item-pct20">
                    <div class="label-wrap"><label for="birthdaySolar">阳历生日</label></div>
                    <div class="ctn-wrap rel"><input type="text" value="" class="ui-input" name="birthdaySolar" id="birthdaySolar" placeholder="例如：8月8日" /></div>
                </li>
                <li class="row-item item-pct20">
                    <div class="label-wrap"><label for="birthdayLunar">农历生日</label></div>
                    <div class="ctn-wrap rel"><input type="text" value="" class="ui-input" name="birthdayLunar" id="birthdayLunar" placeholder="例如：腊月廿八" /></div>
                </li>
                <li class="row-item item-pct60">
                    <div class="label-wrap"><label for="homeAddress">家庭住址</label></div>
                    <div class="ctn-wrap rel"><input type="text" value="" class="ui-input" name="homeAddress" id="homeAddress" placeholder="真实家庭住址" /></div>
                </li>
                <li class="row-item item-pct20">
                    <div class="label-wrap"><label for="education">最高学历</label></div>
                    <div class="ctn-wrap rel"><span id="education"></span></div>
                </li>
                <li class="row-item item-pct20">
                    <div class="label-wrap"><label for="school">毕业院校</label></div>
                    <div class="ctn-wrap rel"><input type="text" value="" class="ui-input" name="school" id="school" /></div>
                </li>
                <li class="row-item item-pct20">
                    <div class="label-wrap"><label for="major">毕业专业</label></div>
                    <div class="ctn-wrap rel"><input type="text" value="" class="ui-input" name="major" id="major" /></div>
                </li>
                <li class="row-item item-md item-pct20">
                    <div class="label-wrap"><label for="fixedSalary">月考评工资基数</label></div>
                    <div class="ctn-wrap rel"><input type="number" value="" class="ui-input" name="fixedSalary" id="fixedSalary" title="如果填写本基数，该员工当月考评工资=本基数*当月出勤率百分比*当月考评分区间百分比" /></div>
                    <div class="unit">元</div>
                </li>
                <li class="row-item item-md item-pct20">
                    <div class="label-wrap"><label for="bonusPerson">月岗位津贴基数</label></div>
                    <div class="ctn-wrap rel"><input type="number" value="" class="ui-input" name="bonusPerson" id="bonusPerson" title="如果填写本基数，该员工当月岗位津贴=本基数*当月出勤率百分比" /></div>
                    <div class="unit">元</div>
                </li>
                <li class="row-item item-pct20">
                    <div class="label-wrap"><label for="isDisable">在职状态</label><span class="must">●</span></div>
                    <div class="ctn-wrap rel"><span id="isDisable"></span></div>
                </li>
                <li class="row-item item-pct20">
                    <div class="label-wrap"><label for="leaveDate">离职日期</label></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input ui-datepicker-input" name="leaveDate" id="leaveDate" autocomplete="off" readonly /></div>
                </li>
                <li class="row-item item-pct20">
                    <div class="label-wrap"><label for="isBuySocialSecurity">社保状态</label><span class="must">●</span></div>
                    <div class="ctn-wrap rel" title="设置为已买将在下次统计工资时带出系统参数中设置的社保个人缴费金额"><span id="isBuySocialSecurity"></span></div>
                </li>
                <li class="row-item item-pct20">
                    <div class="label-wrap"><label for="buySocialSecurityDate">参保日期</label></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input ui-datepicker-input" name="buySocialSecurityDate" id="buySocialSecurityDate" autocomplete="off" readonly /></div>
                </li>
                <li class="row-item item-pct20">
                    <div class="label-wrap"><label for="stopSocialSecurityDate">停保日期</label></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input ui-datepicker-input" name="stopSocialSecurityDate" id="stopSocialSecurityDate" autocomplete="off" readonly /></div>
                </li>
                <li class="row-item">
                    <div class="w80 lh28 ti10 gray bg-white bde b-r-3-top mb-1">岗位职责<span class="must">●</span></div>
                    <div class="ctn-wrap">
                        <textarea name="duty" id="duty" class="ui-input" style="height:160px" spellcheck="false" placeholder="添加岗位职责"></textarea>
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
<<<<<<< HEAD
    <script type="text/javascript" src="/statics/js/plugins/calendar/calendar.js?v=2026102701"></script>
    <script type="text/javascript" src="/statics/js/plugins/fSelect/fSelect.js?v=2026102701"></script>
    <script type="text/javascript" src="/statics/js/dist/user-staff-manage.js?v=2026102701"></script>
=======
    <script type="text/javascript" src="/statics/js/plugins/calendar/calendar.js?v=2026101403"></script>
    <script type="text/javascript" src="/statics/js/plugins/fSelect/fSelect.js?v=2026101403"></script>
    <script type="text/javascript" src="/statics/js/dist/user-staff-manage.js?v=2026101403"></script>
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
</body>

</html>
