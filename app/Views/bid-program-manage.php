<?= view('header') ?>
<<<<<<< HEAD
<link rel="stylesheet" type="text/css" href="/statics/css/manage.css?v=2026102701" />
<link rel="stylesheet" type="text/css" href="/statics/js/plugins/fSelect/fSelect.css?v=2026102701" />
=======
<link rel="stylesheet" type="text/css" href="/statics/css/manage.css?v=2026101403" />
<link rel="stylesheet" type="text/css" href="/statics/js/plugins/fSelect/fSelect.css?v=2026101403" />
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
<style>
    #files1, #files2, #files3 {
    float: left;
    width: calc(100% - 61px);
    height: 88px;
    padding: 10px 0 0;
    overflow-y: auto;
    background: #fff;
    border: 1px solid #E1E2E3;
    border-left: 0 none;
}
</style>
</head>

<body>
    <div class="manage-wrapper">
        <form id="manage-form" action="">
            <ul class="mod-form-rows">
                <li class="row-item item-pct50">
                    <div class="label-wrap"><label for="bidSiteName">招标来源</label><span class="must">●</span></div>
                    <div class="ctn-wrap" id="bidSite">
                        <input type="text" name="bidSiteName" id="bidSiteName" class="ui-input def" style="width:calc(100% - 92px);" autocomplete="off" value="" readonly required />
                        <i class="ui-icon-ellipsis iconfont icon-shenglvehao"></i>
                    </div>
                </li>
                <li class="row-item item-pct50">
                    <div class="label-wrap"><label for="contactName">招标主体</label><span class="must">●</span></div>
                    <div class="ctn-wrap" id="contact">
                        <input type="text" name="contactName" id="contactName" class="ui-input def" style="width:calc(100% - 92px);" autocomplete="off" value="" readonly required />
                        <i class="ui-icon-ellipsis iconfont icon-shenglvehao"></i>
                    </div>
                </li>
                <li class="row-item">
                    <div class="label-wrap"><label for="bidTitle">招标标题</label><span class="must">●</span></div>
                    <div class="ctn-wrap rel"><input type="text" value="" class="ui-input" name="bidTitle" id="bidTitle" autocomplete="off" /></div>
                </li>
                <li class="row-item">
                    <div class="label-wrap"><label for="bidUrl">招标链接</label><span class="must">●</span></div>
                    <div class="ctn-wrap rel"><input type="url" value="" class="ui-input" name="bidUrl" id="bidUrl" autocomplete="off" /></div>
                </li>
                <li class="row-item item-pct16">
                    <div class="label-wrap"><label for="budget">预算金额</label></div>
                    <div class="ctn-wrap rel"><input type="number" value="" class="ui-input" name="budget" id="budget" autocomplete="off" /><span class="unit">元</span></div>
                </li>
                <li class="row-item item-pct16">
                    <div class="label-wrap"><label for="bidDate">开标日期</label><span class="must">●</span></div>
                    <div class="ctn-wrap rel"><input type="text" value="" class="ui-input ui-datepicker-input" name="bidDate" id="bidDate" autocomplete="off" readonly /></div>
                </li>
                <li class="row-item item-pct66">
                    <div class="label-wrap"><label for="contactInfo">联系方式</label><span class="must">●</span></div>
                    <div class="ctn-wrap rel"><input type="text" value="" class="ui-input" name="contactInfo" id="contactInfo" autocomplete="off" /></div>
                </li>
                <li class="row-item ti10 gray">报价信息 ▼</li>
                <li class="row-item item-pct16">
                    <div class="label-wrap"><label for="amount">报价金额</label><span class="must">●</span></div>
                    <div class="ctn-wrap rel"><input type="number" value="" class="ui-input" name="amount" id="amount" autocomplete="off" /><span class="unit">元</span></div>
                </li>
                <li class="row-item item-pct25">
                    <div class="label-wrap"><label for="quotedName">报价人</label><span class="must">●</span></div>
                    <div class="ctn-wrap rel">
                        <select id="userCombo" class="hidden" multiple="multiple">
                            <optgroup id="normalUser" label="在职员工"></optgroup>
                            <optgroup id="disableUser" label="60天内离职员工"></optgroup>
                            <optgroup id="sysUser" label="特殊"></optgroup>
                        </select>
                    </div>
                </li>
                <li class="row-item item-pct16">
                    <div class="label-wrap"><label for="sName">报价主体</label><span class="must">●</span></div>
                    <div class="ctn-wrap rel"><span id="ourself"></span></div>
                </li>
                <li class="row-item item-pct25">
                    <div class="label-wrap"><label for="srcBqNo">报价单号</label></div>
                    <div class="ctn-wrap rel"><input type="text" value="" class="ui-input ui-input-dis" name="srcBqNo" id="srcBqNo" placeholder="转化模式时自动获取，无需手动录入" disabled /></div>
                </li>
                <li class="row-item item-pct16">
                    <div class="label-wrap"><label for="status">开标结果</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><span id="status"></span></div>
                </li>
                <li class="row-item">
                    <div class="label-wrap"><label for="lostReason">未中标原因</label></div>
                    <div class="ctn-wrap rel"><input type="text" value="" class="ui-input" name="lostReason" id="lostReason" autocomplete="off" placeholder="未中标时才填写（100字以内）" /></div>
                </li>
                <li class="row-item ti10 gray">文件信息 ▼</li>
                <li class="row-item item-pct33">
                    <div class="upfile">
                        <div class="upfile-con b-r-3-left">
                            <label class="upfile-title">合同文件</label>
                            <button type="button" id="upFile1" class="choosefile iconfont icon-shangchuan"></button>
                        </div>
                        <div id="files1" class="files b-r-3-right"></div>
                    </div>
                </li>
                <li class="row-item item-pct33">
                    <div class="upfile">
                        <div class="upfile-con b-r-3-left">
                            <label class="upfile-title">签收凭证</label>
                            <button type="button" id="upFile2" class="choosefile iconfont icon-shangchuan"></button>
                        </div>
                        <div id="files2" class="files b-r-3-right"></div>
                    </div>
                </li>
                <li class="row-item item-pct33">
                    <div class="upfile">
                        <div class="upfile-con b-r-3-left">
                            <label class="upfile-title">其他文件</label>
                            <button type="button" id="upFile3" class="choosefile iconfont icon-shangchuan"></button>
                        </div>
                        <div id="files3" class="files b-r-3-right"></div>
                    </div>
                </li>
                <li class="row-item">
                    <div class="w80 lh28 ti10 gray bg-white bde b-r-3-top mb-1">备注信息 ▼</div>
                    <div class="ctn-wrap">
                        <textarea id="description" class="ui-input" spellcheck="false" placeholder="添加备注信息"></textarea>
                    </div>
                </li>
            </ul>
        </form>
    </div>
<<<<<<< HEAD
    <script type="text/javascript" src="/statics/js/plugins/fSelect/fSelect.js?v=2026102701"></script>
    <script type="text/javascript" src="/statics/js/plugins/uploader/jquery.uploader.js?v=2026102701"></script>
    <script type="text/javascript" src="/statics/js/dist/bid-program-manage.js?v=2026102701"></script>
=======
    <script type="text/javascript" src="/statics/js/plugins/fSelect/fSelect.js?v=2026101403"></script>
    <script type="text/javascript" src="/statics/js/plugins/uploader/jquery.uploader.js?v=2026101403"></script>
    <script type="text/javascript" src="/statics/js/dist/bid-program-manage.js?v=2026101403"></script>
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
</body>

</html>
