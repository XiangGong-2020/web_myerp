<?= view('header') ?>
<link rel="stylesheet" type="text/css" href="/statics/css/manage.css?v=2026102701" />
</head>

<body>
    <div class="manage-wrapper">
        <form id="manage-form" action="">
            <ul class="mod-form-rows">
                <li class="row-item item-pct20">
                    <div class="label-wrap"><label for="type">选择类别</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><span id="type"></span></div>
                </li>
                <li class="row-item item-pct80 h30 lh30 night bg-silver b-r-3">
                    <span class="ml10">
                        提交后：超过1小时禁止删除、类别禁止修改；规划与总结首次提交后：超过修改期限禁止修改（日 / 周 / 月 / 年 类别对应修改期限分别为 2小时 / 2天 / 20天 / 60天）。
                    </span>
                </li>
                <li class="row-item">
                    <div class="fl w80 lh28 ti10 gray bg-white bde b-r-3-top mb-1">目标规划<span class="must">●</span></div>
                    <div id="targetNotice" class="fl ml10 lh28 gray"></div>
                    <div class="ctn-wrap">
                        <textarea name="target" id="target" class="ui-input" style="height:150px;white-space: pre-wrap !important;" spellcheck="false" placeholder="填写目标规划内容，请按编号1、2、3……分行清晰录入目标规划条目。 &#13 例如： &#13 1、今天要干某某事情。 &#13 2、今天要干某某事情。 &#13 3、今天要干某某事情。 &#13 4、今天要干某某事情。 &#13 以此类推……"></textarea>
                    </div>
                </li>
                <li class="row-item">
                    <div class="fl w80 lh28 ti10 gray bg-white bde b-r-3-top mb-1">工作总结</div>
                    <div id="summaryNotice" class="fl ml10 lh28 gray"></div>
                    <div class="ctn-wrap">
                        <textarea name="summary" id="summary" class="ui-input" style="height:150px;white-space: pre-wrap !important;" spellcheck="false" placeholder="填写工作总结内容，请按编号1、2、3……对应目标规划分行清晰录入工作总结条目（每条应包含目标执行情况、遇到了什么问题、问题如何解决的）。 &#13 例如： &#13 1、目标1已顺利完成，未遇到问题。 &#13 2、目标2已顺利完成，遇到了XX问题，通过XX解决了。 &#13 3、目标3仅部分完成，没遇到问题，时间不够了。 &#13 4、目标4未完成，遇到了XX问题，暂未找到解决办法。 &#13 以此类推……"></textarea>
                    </div>
                </li>
            </ul>
        </form>
    </div>
    <script type="text/javascript" src="/statics/js/dist/plan-manage.js?v=2026102701"></script>
</body>

</html>
