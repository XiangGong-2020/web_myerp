<?= view('header') ?>
<<<<<<< HEAD
<link rel="stylesheet" type="text/css" href="/statics/css/manage.css?v=2026102701" />
<link rel="stylesheet" type="text/css" href="/statics/js/plugins/fSelect/fSelect.css?v=2026102701" />
=======
<link rel="stylesheet" type="text/css" href="/statics/css/manage.css?v=2026101403" />
<link rel="stylesheet" type="text/css" href="/statics/js/plugins/fSelect/fSelect.css?v=2026101403" />
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
</head>

<body>
    <div class="manage-wrapper">
        <form id="manage-form" action="">
            <ul class="mod-form-rows">
                <li class="row-item item-pct25">
                    <div class="label-wrap"><label for="category">反馈类别</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><span id="category"></span></div>
                </li>
                <li class="row-item item-pct25">
                    <div class="label-wrap"><label for="ww">客户旺旺</label></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input" name="ww" id="ww" /></div>
                </li>
                <li class="row-item item-pct25">
                    <div class="label-wrap"><label for="tel">客户电话</label></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input" name="tel" id="tel" /></div>
                </li>
                <li class="row-item item-pct25">
                    <div class="label-wrap"><label for="qq">客户QQ</label></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input" name="qq" id="qq" /></div>
                </li>
                <li class="row-item item-pct25">
                    <div class="label-wrap"><label for="orderNo">订单号</label></div>
                    <div class="ctn-wrap rel"><input type="text" value="" class="ui-input" name="orderNo" id="orderNo" /></div>
                </li>
                <li class="row-item item-pct25">
                    <div class="label-wrap"><label for="deliverNo">运单号</label></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input" name="deliverNo" id="deliverNo" /></div>
                </li>
                <li class="row-item item-pct25">
                    <div class="label-wrap"><label for="targetName">责任人</label></div>
                    <div class="ctn-wrap">
                        <select id="targetNameCombo" class="hidden" multiple="multiple">
                            <optgroup id="deliverCompany" label="合作快递"></optgroup>
                            <optgroup id="normalUser" label="在职员工"></optgroup>
                            <optgroup id="disableUser" label="60天内离职员工"></optgroup>
                        </select>
                    </div>
                </li>
                <li class="row-item item-pct25">
                    <div class="label-wrap"><label for="targetAmount">损失金额</label></div>
                    <div class="ctn-wrap">
                        <input type="number" value="" class="ui-input" name="targetAmount" id="targetAmount" placeholder="扣运费也应如实填写" title="责任人将赔付录入的损失金额" />
                        <span class="unit">元</span>
                    </div>
                </li>
            </ul>
        </form>
        <div class="mt10 ml10 mr10 rel cf">
            <div class="fl w80 mb-1 lh28 ti10 gray bg-white bde b-r-3-top">反馈内容</div>
            <a id="add-info" class="fl ml10 mb-1 pl10 pr10 lh28 hover-green b-r-3-top">添加内容</a>
            <a id="add-dis" class="fl ml10 mb-1 pl10 pr10 lh28 hover-red b-r-3-top dn">取消添加</a>
        </div>
        <div class="info" style="height:370px;"></div>
    </div>
    <script type="text/plain" id="editor" class="dn" style="position:absolute;top:119px;left:10px;width:1230px;height:320px;"></script>
    <script>
        window.UMEDITOR_HOME_URL = "/statics/js/plugins/ueditor/";
    </script>
<<<<<<< HEAD
    <script type="text/javascript" src="/statics/js/plugins/ueditor/ueditor.config.js?v=2026102701"></script>
    <script type="text/javascript" src="/statics/js/plugins/ueditor/ueditor.all.js?v=2026102701"></script>
=======
    <script type="text/javascript" src="/statics/js/plugins/ueditor/ueditor.config.js?v=2026101403"></script>
    <script type="text/javascript" src="/statics/js/plugins/ueditor/ueditor.all.js?v=2026101403"></script>
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
    <script>
        //实例化编辑器
        var ue = UE.getEditor('editor', {
            toolbars: [
                [
                    'undo', //撤销
                    'redo', //重做
                    '|',
                    'fontfamily', //字体
                    'fontsize', //字号
                    'bold', //加粗
                    'italic', //斜体
                    'underline', //下划线
                    'strikethrough', //删除线
                    'forecolor', //字体颜色
                    '|',
                    'subscript', //下标
                    'superscript', //上标
                    'horizontal', //分隔线
                    'spechars', //特殊字符
                    '|',
                    'simpleupload', //单图上传
                    'insertimage', //多图上传
                    '|',
                    'pasteplain', //纯文本粘贴模式
                    'selectall', //全选
                    'formatmatch', //格式刷
                    'removeformat', //清除格式
                    '|',
                    'justifyleft', //居左对齐
                    'justifyright', //居右对齐
                    'justifycenter', //居中对齐
                    'justifyjustify', //两端对齐
                    '|',
                    'source', //源代码
                ]
            ]
        });
    </script>
<<<<<<< HEAD
    <script type="text/javascript" src="/statics/js/plugins/fSelect/fSelect.js?v=2026102701"></script>
    <script type="text/javascript" src="/statics/js/dist/service-manage.js?v=2026102701"></script>
=======
    <script type="text/javascript" src="/statics/js/plugins/fSelect/fSelect.js?v=2026101403"></script>
    <script type="text/javascript" src="/statics/js/dist/service-manage.js?v=2026101403"></script>
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
</body>

</html>
