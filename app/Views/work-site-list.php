<?= view('header') ?>
<style>
    #urls {
        padding: 0;
    }

    .url {
        position: relative;
        display: block;
        float: left;
        width: 20%;
        text-align: center;
        background: #fff;
        box-shadow: 0 0 0 1px #00000022;
    }

    .url:hover {
        z-index: 1;
        box-shadow: 0 10px 20px 1px #2c65ff;
        transition: box-shadow .5s;
    }

    .url-name {
        position: relative;
        height: 50px;
        padding: 0 30px;
        font-size: 14px;
        line-height: 50px;
        color: #333;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .edit-url {
        position: absolute;
        z-index: 1;
        top: 0;
        right: 10px;
        font-size: 16px;
        font-weight: normal;
        color: #999;
        cursor: pointer;
        opacity: 0;
        transition: all .9s;
    }

    .url:hover .edit-url {
        opacity: 1;
    }

    .url:hover .edit-url:hover {
        color: #2c65ff;
    }

    .url-des {
        height: 50px;
        padding: 0 30px;
        font-size: 12px;
        line-height: 50px;
        color: #999;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .url-link {
        height: 50px;
        padding: 0 30px;
        font-size: 12px;
        line-height: 20px;
        color: #999;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
</style>
</head>

<body>
    <div class="wrapper">
        <div class="mod-search cf">
            <div class="fr ui-btn-group">
                <a id="refresh" class="ui-btn"><i class="iconfont icon-shuaxin"></i><span>刷新</span></a>
                <a id="add" class="ui-btn"><i class="iconfont icon-zengjia"></i><span>新增</span></a>
            </div>
        </div>
        <div class="diy-con">
            <div id="urls" class="ovh"></div>
        </div>
    </div>
    <script type="text/javascript" src="/statics/js/dist/work-site-list.js?v=2026102701"></script>
</body>

</html>
