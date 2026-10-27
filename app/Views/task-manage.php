<?= view('header') ?>
<link rel="stylesheet" type="text/css" href="/statics/css/manage.css?v=2026102701" />
<link rel="stylesheet" type="text/css" href="/statics/js/plugins/fSelect/fSelect.css?v=2026102701" />
<style>
#department .ui-combo-wrap,  #leader .ui-combo-wrap{
    width: calc(100% - 2px) !important;
}
</style>
</head>

<body>
    <div class="manage-wrapper">
        <form id="manage-form" action="">
            <ul class="mod-form-rows">
                <li class="row-item item-pct60">
                    <div class="label-wrap"><label for="title">任务目标</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input" name="title" id="title" /></div>
                </li>
                <li class="row-item item-pct20">
                    <div class="label-wrap"><label for="limitTime">完成限期</label><span class="must">●</span></div>
                    <div class="ctn-wrap rel"><input type="text" value="" class="ui-input ui-datetimepicker-input" name="limitTime" id="limitTime" autocomplete="off" readonly /></div>
                </li>
                <li class="row-item item-pct20">
                    <div class="label-wrap"><label for="type">发起类型</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><span id="type"></span></div>
                </li>
                <li class="row-item item-md item-pct40">
                    <div class="label-wrap"><label for="leader">任务主导人</label><span class="must">●</span></div>
                    <div class="ctn-wrap ctn-wrap-muti">
                        <span id="department" style="display:block;float:left;width:170px;"></span>
                        <span id="leader" style="display:block;float:left;width:170px;"></span>
                    </div>
                </li>
                <li class="row-item item-pct40">
                    <div class="label-wrap"><label for="other">其他参与人</label></div>
                    <div class="ctn-wrap">
                        <select id="otherCombo" class="hidden" multiple="multiple">
                            <optgroup id="otherNormal" label="在职员工"></optgroup>
                            <optgroup id="otherDisable" label="离职员工"></optgroup>
                        </select>
                    </div>
                </li>
                <li class="row-item item-pct20">
                    <div class="label-wrap"><label for="status">完成状态</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><span id="status"></span></div>
                </li>
                <li class="row-item">
                    <div class="fl w80 lh28 ti10 dark bg-white bde b-r-3-top mb-1">任务内容</div>
                    <span class="fl ml10 lh28 gray">录入超过1小时后，禁止再修改任务目标、完成限期、任务内容。</span>
                    <div class="ctn-wrap">
                        <textarea id="content" name="content" class="ui-input" style="height:120px;" spellcheck="false" placeholder="添加任务具体内容"></textarea>
                    </div>
                </li>
                <li class="row-item item-pct50">
                    <div class="upfile">
                        <div class="upfile-con b-r-3-left">
                            <label class="upfile-title">任务图片</label>
                            <button type="button" id="upimg" class="choosefile iconfont icon-shangchuan"></button>
                        </div>
                        <div id="imgs" class="imgs b-r-3-right"></div>
                    </div>
                </li>
                <li class="row-item item-pct50">
                    <div class="upfile">
                        <div class="upfile-con b-r-3-left">
                            <label class="upfile-title">任务资料</label>
                            <button type="button" id="upFile" class="choosefile iconfont icon-shangchuan"></button>
                        </div>
                        <div id="files" class="files b-r-3-right"></div>
                    </div>
                </li>
                <li class="row-item">
                    <div class="fl w80 lh28 ti10 dark bg-white bde b-r-3-top mb-1">任务进展</div>
                    <span id="addExecute" class="ml10 lh28 ui-btn bg-green"><i class="iconfont icon-zengjia"></i><span>点击添加进展</span></span>
                    <span class="ml10 lh28">要求：任务执行过程中，主导人、参与人及时记录任务进展，便于该任务相关人员及时了解任务执行情况。</span>
                    <div class="ctn-wrap">
                        <textarea id="execute" name="execute" class="ui-input break" style="height:200px;" spellcheck="false" placeholder="添加任务进展" readonly ></textarea>
                    </div>
                </li>
            </ul>
        </form>
    </div>
    <script type="text/javascript" src="/statics/js/plugins/uploader/jquery.uploader.js?v=2026102701"></script>
    <script type="text/javascript" src="/statics/js/plugins/fSelect/fSelect.js?v=2026102701"></script>
    <script type="text/javascript" src="/statics/js/dist/task-manage.js?v=2026102701"></script>
</body>

</html>
