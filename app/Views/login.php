<?= view('header') ?>
<<<<<<< HEAD
<link rel="stylesheet" type="text/css" href="/statics/css/login.css?v=2026102701" />
=======
<link rel="stylesheet" type="text/css" href="/statics/css/login.css?v=2026101403" />
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
</head>

<body id="body">
    <div class="con">
        <div class="company-rule diy-bg">
            <div class="company-rule-flag">使命</div>
            <div class="company-rule-text">做一流教学实训产品，赋能职业技术教育。</div>
            <div class="company-rule-flag">愿景</div>
            <div class="company-rule-text">让我们的产品和服务遍布全世界。</div>
            <div class="company-rule-flag">价值观</div>
            <div class="company-rule-text">做事精益求精，对人以诚相待。</div>
        </div>
        <div class="login-box">
            <div class="login-logo"><img src="/statics/img/logo-middle.png" /></div>
            <form action="" onSubmit="return login()">
                <div class="login-con">
                    <div class="login-input-con">
                        <span><i class="iconfont icon-yonghu f16 mr10"></i>账号</span>
                        <input type="text" class="login-input" value="<?php echo request()->getCookie('userName') ?>" id="userName" placeholder="请输入账号或手机号" />
                    </div>
                    <div class="login-input-con">
                        <span><i class="iconfont icon-mima f16 mr10"></i>密码</span>
                        <input type="password" class="login-input" id="userPwd" value="<?php echo request()->getCookie('userPwd') ?>" placeholder="请输入密码" autocomplete="off" />
                    </div>
                    <input type="hidden" class="dn" id="token" value="<?php echo token() ?>" />
                </div>
            </form>
            <div class="forgot-pwd">
                <span><i class="iconfont icon-mima f16 mr10"></i>忘记密码？</span>
                <div class="notice dn">请联系管理员重置密码</div>
            </div>
            <div class="login-btn hover-blue" onClick="login()">
                <span><i class="iconfont icon-jinru f16 mr10"></i>确定登录</span>
            </div>
            <div id="loginerror" class="login-error dn"></div>
        </div>
    </div>
    <div class="ver">© From 2018 重庆伍陆电子有限责任公司版权所有<br>Ver 1.3.21</div>
<<<<<<< HEAD
    <script type="text/javascript" src="/statics/js/dist/login.js?v=2026102701"></script>
=======
    <script type="text/javascript" src="/statics/js/dist/login.js?v=2026101403"></script>
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
</body>

</html>
