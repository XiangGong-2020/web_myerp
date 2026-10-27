<?= view('header') ?>
<link rel="stylesheet" type="text/css" href="/statics/css/manage.css?v=2026102701" />
<style>
    label.valid-error {
        right: 68px
    }
</style>
</head>

<body>
    <div class="manage-wrapper bg-silver">
        <form id="manage-form" action="">
            <ul class="mod-form-rows">
                <div class="ml10 mr10 pt10 f14 fb">修改店铺后台售价后，请立即将修改后的售价，同步录入到此处。就能不等该店铺该产品产生新订单，也能以最新的售价进行比价，避免产生误判。</div>
                <div id="shops" class="pb10 b-r-3 ovh" style="height:540px;overflow-y:auto;"></div>
            </ul>
        </form>
    </div>
    <script type="text/javascript" src="/statics/js/dist/shopPrices-manage.js?v=2026102701"></script>
</body>

</html>
