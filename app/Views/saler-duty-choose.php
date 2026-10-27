<?= view('header') ?>
<style>
    .duty-list {
        float: left;
        padding: 0;
    }
    .duty-item {
        float: left;
        width: calc(20% - 12px);
        height: 100px;
        margin: 10px 0 0 10px;
        text-align: center;
        box-shadow: 0 0 0 1px #00000011;
        border-radius: 3px;
        cursor: pointer;
    }

    .duty-item:hover {
        z-index: 1;
        box-shadow: 0 0 0 1px #2c65ff;
        transition: box-shadow .2s;
    }
    .duty-item-header {
        height: 30px;
        line-height: 40px;
        text-align: center;
    }
    .duty-item-name {
        display: inline-block;
        height: 24px;
        margin: 5px 0 0 5px;
        padding: 0 8px;
        line-height: 24px;
        border-radius: 3px;
    }
    .duty-item-name-cancel {
        float: right;
        display: block;
        width: 24px;
        height: 24px;
        line-height: 24px;
        text-align: center;
        margin: 0 -8px 0 5px;
        font-size: 12px;
        background-color: #00000044;
        border-radius: 3px;
    }
    .duty-item-name-cancel:hover {
        background-color: #00000088;
    }
    .duty-note {
        position: relative;
        clear: both;
    }
</style>
</head>

<body>
    <div class="wrapper">
        <div class="mod-search cf">
            <div class="fl">
                <ul class="ul-inline">
                    <li id="datePicker">
                        <span class="db fl mr-2 h28 lh28 pl5 pr5 gray bg-white bde b-r-3-left">选择日期</span>
                        <input type="text" id="dutyDate" value="" class="fl mr-2 ui-input ui-datepicker-input" autocomplete="off" readonly />
                    </li>
                </ul>
            </div>
            <div class="fl ml10 ui-btn-group">
                <a id="search" class="ui-btn"><i class="iconfont icon-chaxun"></i><span>查询所选日期值班选择情况</span></a>
            </div>
            <div class="fr ui-btn-group">
                <a id="refresh" class="ui-btn"><i class="iconfont icon-shuaxin"></i><span>刷新</span></a>
            </div>
        </div>
        <div class="diy-con bg-rgba1">
            <div id="dutyList" class="duty-list"></div>
            <div class="fl ml10 mr10 mt20">
                <div class="mt10 cf">
                    <div class="fl ml10 lh30 gray">值班记录：</div>
                    <div id="uName" class="fl lh30 fb green"></div>
                    <div id="dutyNoteBtn" class="ui-btn bg-blue fr dn"><i class="iconfont icon-baocun"></i><span>保存我的值班记录</span></div>
                </div>
                <div class="duty-note">
                    <textarea id="dutyNote" class="ui-input" style="width:calc(100vw - 42px);height:100px;" spellcheck="false" placeholder="可在下班前填写今日值班情况记录"></textarea>
                </div>
            </div>
        </div>
    </div>
<<<<<<< HEAD
    <script type="text/javascript" src="/statics/js/dist/saler-duty-choose.js?v=2026102701"></script>
=======
    <script type="text/javascript" src="/statics/js/dist/saler-duty-choose.js?v=2026101403"></script>
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
</body>

</html>
