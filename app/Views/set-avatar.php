<?= view('header') ?>
<<<<<<< HEAD
<link rel="stylesheet" type="text/css" href="/statics/css/manage.css?v=2026102701" />
=======
<link rel="stylesheet" type="text/css" href="/statics/css/manage.css?v=2026101403" />
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
<style>
    .avatar-con {
        position: relative;
        width: 100px;
        height: 160px;
        margin: 30px auto 0;
    }

    .avatar-con .avatar {
        width: 100px;
        height: 100px;
        margin: 0 auto;
        text-align: center;
        line-height: 100px;
        border-radius: 50%;
        overflow: hidden;
    }

    .avatar-con .avatar img {
        width: 100%;
        height: 100%;
    }

    /* 重写 */
    .choosefile {
        top: 130px;
        left: 0px;
        width: 100px;
        height: 30px;
        font-size: 12px;
        line-height: 1;
        border: 1px solid #00000022;
        border-radius: 4px;
    }
</style>
</head>

<body>
    <div class="manage-wrapper">
        <div class="avatar-con">
            <div id="avatar" class="avatar">
            </div>
            <button id="upimg" class="choosefile b-r-3-bottom"><i class="iconfont icon-shangchuan f14 mr5"></i><span>上传新头像</span></button>
        </div>
    </div>
<<<<<<< HEAD
    <script type="text/javascript" src="/statics/js/plugins/uploader/jquery.uploader.js?v=2026102701"></script>
    <script type="text/javascript" src="/statics/js/dist/set-avatar.js?v=2026102701"></script>
=======
    <script type="text/javascript" src="/statics/js/plugins/uploader/jquery.uploader.js?v=2026101403"></script>
    <script type="text/javascript" src="/statics/js/dist/set-avatar.js?v=2026101403"></script>
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
</body>

</html>
