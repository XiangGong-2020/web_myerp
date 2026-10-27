<?= view('header') ?>
<link rel="stylesheet" type="text/css" href="/statics/css/manage.css?v=2026102701" />
<link rel="stylesheet" type="text/css" href="/statics/js/plugins/fSelect/fSelect.css?v=2026102701" />
</head>

<body>
    <div class="manage-wrapper">
        <form id="manage-form" action="">
            <ul class="mod-form-rows">
                <li class="row-item item-pct60">
                    <div class="label-wrap"><label for="title">项目目标</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input" name="title" id="title" /></div>
                </li>
                <li class="row-item item-pct20">
                    <div class="label-wrap"><label for="lastDate">交付限期</label><span class="must">●</span></div>
                    <div class="ctn-wrap rel"><input type="text" value="" class="ui-input ui-datetimepicker-input" name="lastDate" id="lastDate" autocomplete="off" readonly /></div>
                </li>
                <li class="row-item item-pct20">
                    <div class="label-wrap"><label for="type">来源</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><span id="type"></span></div>
                </li>
                <li class="row-item">
                    <div class="fl w80 lh28 ti10 gray bg-white bde b-r-3-top mb-1">项目内容</div>
                    <span id="reqInfo" class="fl ml10 lh28 gray"></span>
                    <div class="ctn-wrap">
                        <textarea id="content" name="content" class="ui-input" style="height:120px;" spellcheck="false" placeholder="添加项目具体内容"></textarea>
                    </div>
                </li>
                <li class="row-item item-pct50">
                    <div class="upfile">
                        <div class="upfile-con b-r-3-left">
                            <label class="upfile-title">参考图片</label>
                            <button type="button" id="upimg" class="choosefile iconfont icon-shangchuan"></button>
                        </div>
                        <div id="imgs" class="imgs b-r-3-right"></div>
                    </div>
                </li>
                <li class="row-item item-pct50">
                    <div class="upfile">
                        <div class="upfile-con b-r-3-left">
                            <label class="upfile-title">参考资料</label>
                            <button type="button" id="upFile" class="choosefile iconfont icon-shangchuan"></button>
                        </div>
                        <div id="files" class="files b-r-3-right"></div>
                    </div>
                </li>
                <li class="row-item item-md item-pct20">
                    <div class="label-wrap"><label for="leader">项目负责人</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><span id="leader"></span></div>
                </li>
                <li class="row-item item-pct60">
                    <div class="label-wrap"><label for="other">其他参与人</label></div>
                    <div class="ctn-wrap">
                        <select id="otherCombo" class="hidden" multiple="multiple">
                            <optgroup id="otherNormal" label="在职技术部员工"></optgroup>
                            <optgroup id="otherDisable" label="离职技术部员工"></optgroup>
                        </select>
                    </div>
                </li>
                <li class="row-item item-pct20">
                    <div class="label-wrap"><label for="goodsNo">成品编码</label></div>
                    <div class="ctn-wrap"><input type="text" id="goodsNo" class=" ui-input" value="" placeholder="输入成品的产品编码" /></div>
                </li>
                <li class="row-item">
                    <div class="fl w80 lh28 ti10 gray bg-white bde b-r-3-top mb-1">项目进展</div>
                    <span id="addExecute" class="ml10 lh28 ui-btn bg-green"><i class="iconfont icon-zengjia"></i><span>点击添加进展</span></span>
                    <span class="ml10 lh28">要求：项目执行过程中，项目负责人每天记录项目进展，便于申请人与管理人及时了解项目执行情况。</span>
                    <div class="ctn-wrap">
                        <textarea id="execute" name="execute" class="ui-input break" style="height:120px;" spellcheck="false" placeholder="添加项目进展" readonly ></textarea>
                    </div>
                </li>
            </ul>
        </form>
        <div class="mt10 ml10 mr10 ovh">
            <div class="w80 lh28 ti10 gray bg-white bde b-r-3-top mb-1">完成节点</div>
            <div id="status" class="db ovh p10 bg-white bde b-r-3">
                <div class="chk fl pct33 mt5 mb5">
                    <input type="checkbox" name="status" value="1">
                    <div class="statusTit fl w120">1、已接手项目</div>
                    <div class="chooseName fl w40 tc ti0 green"></div>
                    <div class="chooseTime fl w120 tc ti0 green"></div>
                </div>
                <div class="chk fl pct33 mt5 mb5">
                    <input type="checkbox" name="status" value="2">
                    <div class="statusTit fl w120">2、已完成设计</div>
                    <div class="chooseName fl w40 tc ti0 green"></div>
                    <div class="chooseTime fl w120 tc ti0 green"></div>
                </div>
                <div class="chk fl pct33 mt5 mb5">
                    <input type="checkbox" name="status" value="3">
                    <div class="statusTit fl w120">3、已录入成品</div>
                    <div class="chooseName fl w40 tc ti0 green"></div>
                    <div class="chooseTime fl w120 tc ti0 green"></div>
                </div>
                <div class="chk fl pct33 mt5 mb5">
                    <input type="checkbox" name="status" value="4">
                    <div class="statusTit fl w120">4、已上传资料</div>
                    <div class="chooseName fl w40 tc ti0 green"></div>
                    <div class="chooseTime fl w120 tc ti0 green"></div>
                </div>
                <div class="chk fl pct33 mt5 mb5">
                    <input type="checkbox" name="status" value="5">
                    <div class="statusTit fl w120">5、已完成项目</div>
                    <div class="chooseName fl w40 tc ti0 green"></div>
                    <div class="chooseTime fl w120 tc ti0 green"></div>
                </div>
                <div class="chk patentDone fl pct33 mt5 mb5">
                    <input type="checkbox" name="status" value="6" disabled>
                    <div class="statusTit fl w120">6、已申请专利</div>
                    <div class="chooseName fl w40 tc ti0 green"></div>
                    <div class="chooseTime fl w120 tc ti0 green"></div>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript" src="/statics/js/plugins/uploader/jquery.uploader.js?v=2026102701"></script>
    <script type="text/javascript" src="/statics/js/plugins/fSelect/fSelect.js?v=2026102701"></script>
    <script type="text/javascript" src="/statics/js/dist/goods-task-manage.js?v=2026102701"></script>
</body>

</html>
