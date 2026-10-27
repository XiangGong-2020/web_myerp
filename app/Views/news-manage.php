<?= view('header') ?>
<<<<<<< HEAD
<link rel="stylesheet" type="text/css" href="/statics/css/bills.css?v=2026102701" />
<link rel="stylesheet" type="text/css" href="/statics/js/plugins/fSelect/fSelect.css?v=2026102701" />
=======
<link rel="stylesheet" type="text/css" href="/statics/css/bills.css?v=2026101403" />
<link rel="stylesheet" type="text/css" href="/statics/js/plugins/fSelect/fSelect.css?v=2026101403" />
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
<style>
    #titlecon {
        width: calc(100vw - 25px);
    }

    #title {
        width: calc(100% - 98px);
    }
</style>
</head>

<body>
    <div class="wrapper">
        <div class="bill-tool-top">
            <div id="mark"></div>
            <div id="toolTop" class="fr ui-btn-group"></div>
        </div>
        <div class="bills">
            <div class="con-header">
                <dl class="cf">
                    <dd id="titlecon">
                        <label>标题</label>
                        <input type="text" id="title" class="ui-input" value="" placeholder="请输入标题" />
                    </dd>
                    <dd id="classes">
                        <label class="radio"><input type="radio" name="classes" value="1">业务知识</label>
                        <label class="radio"><input type="radio" name="classes" value="2">学习视频</label>
                        <label class="radio"><input type="radio" name="classes" value="3">通知公告</label>
                        <label class="radio"><input type="radio" name="classes" value="4">制度文件</label>
                    </dd>
                    <dd id="departmentCon" class="ml20">
                        <label title="如果不选择目标部门，所有人都可以查看。">目标部门</label>
                        <select id="departmentCombo" class="hidden" multiple="multiple">
                            <optgroup id="department" label="部门列表"></optgroup>
                        </select>
                    </dd>
                    <dd id="videotypeCon" class="dn ml20">
                        <label title="如果不选择视频类别，所有人都可以观看。">视频类别</label>
                        <span id="videotypeCombo"></span>
                    </dd>
                    <div class="fr">
                        <dd class="ml20">
                            <label>发布人</label>
                            <input type="text" id="beginName" class="ui-input ui-input-dis" disabled />
                        </dd>
                        <dd class="ml20">
                            <label>发布时间</label>
                            <input type="text" id="beginDate" class="ui-input ui-input-dis" disabled />
                        </dd>
                    </div>
                </dl>
            </div>
            <div class="page-line"></div>
            <div class="pl10 pr10 ovh">
                <div id="content" class="lh24"></div>
                <div id="watch" class="dn fl pl20 lh24" style="width:calc(50% - 20px)">
                    <div><span class="f16 fb">观看记录</span><span>（每10秒计入累计时间）</span></div>
                    <div id="watchinfo"></div>
                </div>
            </div>
            <script type="text/plain" id="editor" style="display:none;width:100%;height:500px;margin:20px auto 0;"></script>
        </div>
    </div>
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
                    'attachment', //附件
                    'insertvideo', //视频
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
    <script type="text/javascript" src="/statics/js/dist/news-manage.js?v=2026102701"></script>
=======
    <script type="text/javascript" src="/statics/js/plugins/fSelect/fSelect.js?v=2026101403"></script>
    <script type="text/javascript" src="/statics/js/dist/news-manage.js?v=2026101403"></script>
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
</body>

</html>
