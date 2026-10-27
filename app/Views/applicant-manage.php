<?= view('header') ?>
<link rel="stylesheet" type="text/css" href="/statics/css/manage.css?v=2026102701" />
</head>

<body>
    <div class="manage-wrapper">
        <form id="manage-form" action="#">
            <ul class="mod-form-rows pb10">
                <li class="row-item item-pct25">
                    <div class="label-wrap"><label for="name">姓名</label><span class="must">●</span></div>
                    <div class="ctn-wrap rel"><input type="text" value="" class="ui-input" name="name" id="name" placeholder="真实姓名" /></div>
                </li>
                <li class="row-item item-pct25">
                    <div class="label-wrap"><label for="channel">了解渠道</label><span class="must">●</span></div>
                    <div class="ctn-wrap rel"><span id="channel"></span></div>
                </li>
                <li class="row-item item-pct25">
                    <div class="label-wrap"><label for="department">应聘部门</label><span class="must">●</span></div>
                    <div class="ctn-wrap rel"><span id="department"></span></div>
                </li>
                <li class="row-item item-pct25">
                    <div class="label-wrap"><label for="position">应聘职位</label><span class="must">●</span></div>
                    <div class="ctn-wrap rel"><span id="position"></span></div>
                </li>
                <li class="row-item item-pct25">
                    <div class="label-wrap"><label for="joinDate">应聘日期</label><span class="must">●</span></div>
                    <div class="ctn-wrap rel"><input type="text" value="" class="ui-input ui-datepicker-input" name="joinDate" id="joinDate" autocomplete="off" readonly /></div>
                </li>
                <li class="row-item item-pct25">
                    <div class="label-wrap"><label for="identity">社会身份</label><span class="must">●</span></div>
                    <div class="ctn-wrap rel"><span id="identity"></span></div>
                </li>
                <li class="row-item item-pct25">
                    <div class="label-wrap"><label for="sex">性别</label><span class="must">●</span></div>
                    <div class="ctn-wrap rel"><span id="sex"></span></div>
                </li>
                <li class="row-item item-pct25">
                    <div class="label-wrap"><label for="birthday">出生日期</label><span class="must">●</span></div>
                    <div class="ctn-wrap rel"><input type="text" value="" class="ui-input ui-datepicker-input" name="birthday" id="birthday" autocomplete="off" readonly /></div>
                </li>
                <li class="row-item item-pct25">
                    <div class="label-wrap"><label for="nativePlace">籍贯</label><span class="must">●</span></div>
                    <div class="ctn-wrap rel"><input type="text" value="" class="ui-input" name="nativePlace" id="nativePlace" placeholder="出生地" /></div>
                </li>
                <li class="row-item item-pct25">
                    <div class="label-wrap"><label for="nation">民族</label><span class="must">●</span></div>
                    <div class="ctn-wrap rel"><span id="nation"></span></div>
                </li>
                <li class="row-item item-pct25">
                    <div class="label-wrap"><label for="political">政治面貌</label><span class="must">●</span></div>
                    <div class="ctn-wrap rel"><span id="political"></span></div>
                </li>
                <li class="row-item item-pct25">
                    <div class="label-wrap"><label for="criminal">犯罪记录</label><span class="must">●</span></div>
                    <div class="ctn-wrap rel"><span id="criminal"></span></div>
                </li>
                <li class="row-item item-pct25">
                    <div class="label-wrap"><label for="healthy">健康状况</label><span class="must">●</span></div>
                    <div class="ctn-wrap rel"><span id="healthy"></span></div>
                </li>
                <li class="row-item item-pct25">
                    <div class="label-wrap"><label for="marriage">婚姻状况</label><span class="must">●</span></div>
                    <div class="ctn-wrap rel"><span id="marriage"></span></div>
                </li>
                <li class="row-item item-pct25">
                    <div class="label-wrap"><label for="education">最高学历</label><span class="must">●</span></div>
                    <div class="ctn-wrap rel"><span id="education"></span></div>
                </li>
                <li class="row-item item-pct25">
                    <div class="label-wrap"><label for="entrance">入学渠道</label><span class="must">●</span></div>
                    <div class="ctn-wrap rel"><span id="entrance"></span></div>
                </li>
                <li class="row-item item-pct25">
                    <div class="label-wrap"><label for="graduation">毕业证书</label><span class="must">●</span></div>
                    <div class="ctn-wrap rel"><span id="graduation"></span></div>
                </li>
                <li class="row-item item-pct25">
                    <div class="label-wrap"><label for="school">就读院校</label><span class="must">●</span></div>
                    <div class="ctn-wrap rel"><input type="text" value="" class="ui-input" name="school" id="school" /></div>
                </li>
                <li class="row-item item-pct25">
                    <div class="label-wrap"><label for="major">主修专业</label><span class="must">●</span></div>
                    <div class="ctn-wrap rel"><input type="text" value="" class="ui-input" name="major" id="major" /></div>
                </li>
                <li class="row-item item-pct25">
                    <div class="label-wrap"><label for="sfzNo">身份证号</label><span class="must">●</span></div>
                    <div class="ctn-wrap rel"><input type="text" value="" class="ui-input" name="sfzNo" id="sfzNo" /></div>
                </li>
                <li class="row-item item-pct25">
                    <div class="label-wrap"><label for="nowAddress">现居住地</label><span class="must">●</span></div>
                    <div class="ctn-wrap rel"><input type="text" value="" class="ui-input" name="nowAddress" id="nowAddress" /></div>
                </li>
                <li class="row-item item-pct25">
                    <div class="label-wrap"><label for="homeAddress">家庭住址</label><span class="must">●</span></div>
                    <div class="ctn-wrap rel"><input type="text" value="" class="ui-input" name="homeAddress" id="homeAddress" /></div>
                </li>
                <li class="row-item item-pct25">
                    <div class="label-wrap"><label for="wechat">微信号</label><span class="must">●</span></div>
                    <div class="ctn-wrap rel"><input type="text" value="" class="ui-input" name="wechat" id="wechat" /></div>
                </li>
                <li class="row-item item-pct25">
                    <div class="label-wrap"><label for="mobile">手机号</label><span class="must">●</span></div>
                    <div class="ctn-wrap rel"><input type="text" value="" class="ui-input" name="mobile" id="mobile" /></div>
                </li>
                <li class="row-item item-pct25">
                    <div class="label-wrap"><label for="salary">期望薪资</label><span class="must">●</span></div>
                    <div class="ctn-wrap rel"><input type="number" value="" class="ui-input" name="salary" id="salary" /><span class="unit">元</span></div>
                </li>
                <li class="row-item item-pct25 item-md">
                    <div class="label-wrap"><label for="urgentName">紧急联系人姓名</label><span class="must">●</span></div>
                    <div class="ctn-wrap rel"><input type="text" value="" class="ui-input" name="urgentName" id="urgentName" /></div>
                </li>
                <li class="row-item item-pct25 item-md">
                    <div class="label-wrap"><label for="urgentPhone">紧急联系人电话</label><span class="must">●</span></div>
                    <div class="ctn-wrap rel"><input type="text" value="" class="ui-input" name="urgentPhone" id="urgentPhone" /></div>
                </li>
                <li class="row-item item-pct25 item-md">
                    <div class="label-wrap"><label for="urgentRelationship">紧急联系人与您关系</label><span class="must">●</span></div>
                    <div class="ctn-wrap rel"><input type="text" value="" class="ui-input" name="urgentRelationship" id="urgentRelationship" /></div>
                </li>
                <li class="row-item item-pct50 item-md">
                    <div class="label-wrap"><label for="selfEvaluationOne">自我评价(优势)</label><span class="must">●</span></div>
                    <div class="ctn-wrap rel"><input type="text" value="" class="ui-input" name="selfEvaluationOne" id="selfEvaluationOne" /></div>
                </li>
                <li class="row-item item-pct50 item-md">
                    <div class="label-wrap"><label for="selfEvaluationTwo">自我评价(劣势)</label><span class="must">●</span></div>
                    <div class="ctn-wrap rel"><input type="text" value="" class="ui-input" name="selfEvaluationTwo" id="selfEvaluationTwo" /></div>
                </li>
                <div class="fl w100vw mt20 ti10 lh20 gray">以下为选填</div>
                <li class="row-item item-pct50 item-lg">
                    <div class="label-wrap"><label for="workOneTime">工作经历一 起止时间</label></div>
                    <div class="ctn-wrap rel"><input type="text" value="" class="ui-input" name="workOneTime" id="workOneTime" /></div>
                </li>
                <li class="row-item item-pct50 item-lg">
                    <div class="label-wrap"><label for="workOneCompany">工作经历一 单位</label></div>
                    <div class="ctn-wrap rel"><input type="text" value="" class="ui-input" name="workOneCompany" id="workOneCompany" /></div>
                </li>
                <li class="row-item item-pct50 item-lg">
                    <div class="label-wrap"><label for="workOnePosition">工作经历一 职位</label></div>
                    <div class="ctn-wrap rel"><input type="text" value="" class="ui-input" name="workOnePosition" id="workOnePosition" /></div>
                </li>
                <li class="row-item item-pct50 item-lg">
                    <div class="label-wrap"><label for="workOneEndReason">工作经历一 离职原因</label></div>
                    <div class="ctn-wrap rel"><input type="text" value="" class="ui-input" name="workOneEndReason" id="workOneEndReason" /></div>
                </li>
                <li class="row-item item-pct50 item-lg">
                    <div class="label-wrap"><label for="workOneWitness">工作经历一 证明人及联系方式</label></div>
                    <div class="ctn-wrap rel"><input type="text" value="" class="ui-input" name="workOneWitness" id="workOneWitness" /></div>
                </li>
                <li class="row-item item-pct50 item-lg">
                    <div class="label-wrap"><label for="workTwoTime">工作经历二 起止时间</label></div>
                    <div class="ctn-wrap rel"><input type="text" value="" class="ui-input" name="workTwoTime" id="workTwoTime" /></div>
                </li>
                <li class="row-item item-pct50 item-lg">
                    <div class="label-wrap"><label for="workTwoCompany">工作经历二 单位</label></div>
                    <div class="ctn-wrap rel"><input type="text" value="" class="ui-input" name="workTwoCompany" id="workTwoCompany" /></div>
                </li>
                <li class="row-item item-pct50 item-lg">
                    <div class="label-wrap"><label for="workTwoPosition">工作经历二 职位</label></div>
                    <div class="ctn-wrap rel"><input type="text" value="" class="ui-input" name="workTwoPosition" id="workTwoPosition" /></div>
                </li>
                <li class="row-item item-pct50 item-lg">
                    <div class="label-wrap"><label for="workTwoEndReason">工作经历二 离职原因</label></div>
                    <div class="ctn-wrap rel"><input type="text" value="" class="ui-input" name="workTwoEndReason" id="workTwoEndReason" /></div>
                </li>
                <li class="row-item item-pct50 item-lg">
                    <div class="label-wrap"><label for="workTwoWitness">工作经历二 证明人及联系方式</label></div>
                    <div class="ctn-wrap rel"><input type="text" value="" class="ui-input" name="workTwoWitness" id="workTwoWitness" /></div>
                </li>
                <li class="row-item item-pct50 item-lg">
                    <div class="label-wrap"><label for="familyOneName">家庭成员一 姓名</label></div>
                    <div class="ctn-wrap rel"><input type="text" value="" class="ui-input" name="familyOneName" id="familyOneName" /></div>
                </li>
                <li class="row-item item-pct50 item-lg">
                    <div class="label-wrap"><label for="familyOneRelationship">家庭成员一 与您的关系</label></div>
                    <div class="ctn-wrap rel"><input type="text" value="" class="ui-input" name="familyOneRelationship" id="familyOneRelationship" /></div>
                </li>
                <li class="row-item item-pct50 item-lg">
                    <div class="label-wrap"><label for="familyOneWorkPlace">家庭成员一 工作单位</label></div>
                    <div class="ctn-wrap rel"><input type="text" value="" class="ui-input" name="familyOneWorkPlace" id="familyOneWorkPlace" /></div>
                </li>
                <li class="row-item item-pct50 item-lg">
                    <div class="label-wrap"><label for="familyOnePosition">家庭成员一 工作职位</label></div>
                    <div class="ctn-wrap rel"><input type="text" value="" class="ui-input" name="familyOnePosition" id="familyOnePosition" /></div>
                </li>
                <li class="row-item item-pct50 item-lg">
                    <div class="label-wrap"><label for="familyOnePhone">家庭成员一 手机号</label></div>
                    <div class="ctn-wrap rel"><input type="text" value="" class="ui-input" name="familyOnePhone" id="familyOnePhone" /></div>
                </li>
                <li class="row-item item-pct50 item-lg">
                    <div class="label-wrap"><label for="familyTwoName">家庭成员二 姓名</label></div>
                    <div class="ctn-wrap rel"><input type="text" value="" class="ui-input" name="familyTwoName" id="familyTwoName" /></div>
                </li>
                <li class="row-item item-pct50 item-lg">
                    <div class="label-wrap"><label for="familyTwoRelationship">家庭成员二 与您的关系</label></div>
                    <div class="ctn-wrap rel"><input type="text" value="" class="ui-input" name="familyTwoRelationship" id="familyTwoRelationship" /></div>
                </li>
                <li class="row-item item-pct50 item-lg">
                    <div class="label-wrap"><label for="familyTwoWorkPlace">家庭成员二 工作单位</label></div>
                    <div class="ctn-wrap rel"><input type="text" value="" class="ui-input" name="familyTwoWorkPlace" id="familyTwoWorkPlace" /></div>
                </li>
                <li class="row-item item-pct50 item-lg">
                    <div class="label-wrap"><label for="familyTwoPosition">家庭成员二 工作职位</label></div>
                    <div class="ctn-wrap rel"><input type="text" value="" class="ui-input" name="familyTwoPosition" id="familyTwoPosition" /></div>
                </li>
                <li class="row-item item-pct50 item-lg">
                    <div class="label-wrap"><label for="familyTwoPhone">家庭成员二 手机号</label></div>
                    <div class="ctn-wrap rel"><input type="text" value="" class="ui-input" name="familyTwoPhone" id="familyTwoPhone" /></div>
                </li>
            </ul>
        </form>
    </div>
    <script type="text/javascript" src="/statics/js/dist/applicant-manage.js?v=2026102701"></script>
</body>

</html>
