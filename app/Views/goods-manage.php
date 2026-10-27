<?= view('header') ?>
<link rel="stylesheet" type="text/css" href="/statics/css/manage.css?v=2026102701" />
<link rel="stylesheet" type="text/css" href="/statics/js/plugins/fSelect/fSelect.css?v=2026102701" />
<style>
    .notice-wrap {
        position: fixed;
        z-index: 2;
        top: 45px;
        left: 10px;
        right: 10px;
        padding: 20px;
        line-height: 26px;
        border-radius: 3px;
        box-shadow: 0 3px 10px #00000044;
    }

    .notice-close {
        position: absolute;
        top: 0;
        right: 0;
        width: 70px;
        height: 30px;
        font-size: 12px;
        line-height: 30px;
        text-align: center;
        cursor: pointer;
    }

    .sonrule-wrap {
        position: fixed;
        z-index: 2;
        top: 350px;
        left: 10px;
        right: 10px;
        padding: 20px;
        line-height: 26px;
        border-radius: 3px;
        box-shadow: 0 3px 10px #00000044;
    }

    .sonrule-close {
        position: absolute;
        top: 0;
        right: 0;
        width: 70px;
        height: 30px;
        font-size: 12px;
        line-height: 30px;
        text-align: center;
        cursor: pointer;
    }


    /* 重写表格主体 */
    .ui-jqgrid tr.ui-row-ltr td {
        box-shadow: inset 1px 0 1px #00000011;
    }

    .ui-jqgrid tr.ui-row-ltr td:first-child {
        box-shadow: none;
    }

    /**拖动样式 */
    .tDnD_whileDrag {
        background-color: #ffff00 !important;
    }

    .jqgrid-rownum {
        cursor: move
    }
</style>
</head>

<body>
    <div class="manage-wrapper">
        <form id="manage-form" action="">
            <ul class="mod-form-rows">
                <li class="row-item item-pct25">
                    <div class="label-wrap"><label for="category">产品类别</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><span id="category"></span></div>
                </li>
                <li class="row-item item-pct25">
                    <div class="label-wrap"><label for="no">产品编码</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input" name="no" id="no" maxlength="30" /></div>
                    <div id="notice" class="pl5 pr5 h20 lh20 hover-orange b-r-3" style="position:absolute;z-index:1;top:4px;right:4px;">编码规则</div>
                    <div class="notice-wrap f14 dark bg-white dn">
                        <div>1、产品编码作为本企业每种产品在全网店铺及管理系统中的唯一身份标识，由相关人员按规则命名后，统一更新到所有网店类似于【商家编码】属性里去。</div>
                        <div>2、编码目的：① 规范每种产品的唯一身份，实现多渠道严格对应的清晰关系，避免混乱；② 为实现产品全网联动、订单同步、精准库存与预警、数据分析等功能提供基础数据支持。</div>
                        <div>3、命名规则：必须为以大小写字母或数字开头，中间接0-24位大小写字母或数字或连接符，并以大小写字母或数字结尾，例如：TJ-56-123（运营部负责编码规则的编写与维护）。</div>
                        <i class="notice-close iconfont icon-guanbi"> 关闭</i>
                    </div>
                </li>
                <li class="row-item item-pct25">
                    <div class="label-wrap"><label for="barCode">产品条码</label></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input" name="barCode" id="barCode" maxlength="30" /></div>
                </li>
                <li class="row-item item-pct25">
                    <div class="label-wrap"><label for="brand">产品品牌</label></div>
                    <div class="ctn-wrap"><span id="brand"></span></div>
                </li>
                <li class="row-item item-pct33">
                    <div class="label-wrap"><label for="name">产品名称</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input" name="name" id="name" /></div>
                </li>
                <li class="row-item item-pct33">
                    <div class="label-wrap"><label for="spec">规格型号</label></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input" name="spec" id="spec" /></div>
                </li>
                <li class="row-item item-pct33">
                    <div class="label-wrap"><label for="sku">辅助属性</label></div>
                    <div class="ctn-wrap"><input type="text" value="" class="ui-input" name="sku" id="sku" /></div>
                </li>
                <li class="row-item item-pct20">
                    <div class="label-wrap"><label for="unit">最小单位</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><span id="unit"></span></div>
                </li>
                <li class="row-item item-pct20">
                    <div class="label-wrap"><label for="weight">单位重量</label></div>
                    <div class="ctn-wrap">
                        <input type="number" step="0.00001" value="" class="ui-input money" name="weight" id="weight" placeholder="可到小数点后5位" />
                        <span class="unit">kg</span>
                    </div>
                </li>
                <li class="row-item item-pct20">
                    <div class="label-wrap"><label for="storage">首选仓库</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><span id="storage"></span></div>
                </li>
                <li class="row-item item-pct20">
                    <div class="label-wrap"><label for="location">指定库位</label></div>
                    <div class="ctn-wrap"><span id="location"></span></div>
                </li>
                <li class="row-item item-pct20">
                    <div class="label-wrap"><label for="minInventory">库存下限</label><span class="must">●</span></div>
                    <div class="ctn-wrap">
                        <input type="number" step="1" class="ui-input qty" name="minInventory" id="minInventory" placeholder="低于下限会预警" />
                    </div>
                </li>
                <li class="row-item item-pct20">
                    <div class="label-wrap"><label for="maxInventory">库存上限</label><span class="must">●</span></div>
                    <div class="ctn-wrap">
                        <input type="number" step="1" class="ui-input qty" name="maxInventory" id="maxInventory" placeholder="高于上限会预警" />
                    </div>
                </li>
                <li class="row-item item-pct20">
                    <div class="label-wrap"><label for="purchasePrice">预采购价</label><span class="must">●</span></div>
                    <div class="ctn-wrap">
                        <input type="number" step="0.00001" value="" class="ui-input money" name="purchasePrice" id="purchasePrice" placeholder="随每次采购变化" />
                        <span class="unit">元</span>
                    </div>
                </li>
                <li class="row-item item-pct20">
                    <div class="label-wrap"><label for="salePrice">预销售价</label><span class="must">●</span></div>
                    <div class="ctn-wrap">
                        <input type="number" step="0.00001" value="" class="ui-input money" name="salePrice" id="salePrice" placeholder="随每次销售变化" />
                        <span class="unit">元</span>
                    </div>
                </li>
                <li class="row-item item-pct20">
                    <div class="label-wrap"><label for="isDisable">产品状态</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><span id="isDisable"></span></div>
                </li>
                <li class="row-item item-pct20">
                    <div class="label-wrap"><label for="isProofread">校对状态</label><span class="must">●</span></div>
                    <div class="ctn-wrap"><span id="isProofread"></span></div>
                </li>
                <li class="row-item item-pct25">
                    <div class="label-wrap"><label for="devName">开发人</label></div>
                    <div class="ctn-wrap">
                        <select id="devNameCombo" class="hidden" multiple="multiple">
                            <optgroup id="devNameNormal" label="在职技术部员工"></optgroup>
                            <optgroup id="devNameDisable" label="离职技术部员工"></optgroup>
                        </select>
                    </div>
                </li>
                <li class="row-item item-pct25">
                    <div class="label-wrap"><label for="serName">维护人</label></div>
                    <div class="ctn-wrap"><span id="serName"></span></div>
                </li>
                <li class="row-item item-pct25">
                    <div class="label-wrap"><label for="createTime">最初录入</label></div>
                    <div class="ctn-wrap">
                        <input type="text" class="ui-input ui-input-dis" name="createTime" id="createTime" disabled />
                    </div>
                </li>
                <li class="row-item item-pct25">
                    <div class="label-wrap"><label for="modifyTime">最后编辑</label></div>
                    <div class="ctn-wrap">
                        <input type="text" class="ui-input ui-input-dis" name="modifyTime" id="modifyTime" disabled />
                    </div>
                    <div id="modifyHis" class="pl5 pr5 h20 lh20 hover-teal b-r-3" style="position:absolute;z-index:1;top:4px;right:4px;">编辑历史</div>
                </li>
                <li class="row-item item-pct33">
                    <div class="upfile">
                        <div class="upfile-con b-r-3-left">
                            <label class="upfile-title">产品图片</label>
                            <button type="button" id="upimg" class="choosefile iconfont icon-shangchuan"></button>
                        </div>
                        <div id="imgs" class="imgs b-r-3-right"></div>
                    </div>
                </li>
                <li class="row-item item-pct33">
                    <div class="upfile">
                        <div class="upfile-con b-r-3-left">
                            <label class="upfile-title">参考文件</label>
                            <button type="button" id="upFile" class="choosefile iconfont icon-shangchuan"></button>
                        </div>
                        <div id="files" class="files b-r-3-right"></div>
                        <div class="fr mt-30 pl10 pr10 lh30 gray def es">仅上传外部产品采购参考文件！自家产品各种文件不要传到这里！</div>
                    </div>
                </li>
                <li class="row-item item-pct33">
                    <div class="ctn-wrap">
                        <textarea id="description" class="ui-input" spellcheck="false" placeholder="添加备注信息"></textarea>
                    </div>
                </li>
            </ul>
        </form>
        <div class="mt10 ml10 mr10 rel cf">
            <div class="fl mb-1 pl10 pr10 lh28 bde b-r-3-top" id="showSonGoods">子产品</div>
            <a id="export" class="fl ml10 mb-1 pl10 pr10 lh28 hover-teal b-r-3-top">导出清单</a>
            <a id="sonrule" class="fl ml10 mb-1 pl10 pr10 lh28 hover-orange b-r-3-top">子产品规则</a>
        </div>
        <div class="sonrule-wrap f14 dark bg-white dn">
            <div>1、销售搭配套餐、需配货套件必须录入子产品，其他类型产品视情况选择是否录入子产品。</div>
            <div>2、自产元件包比较特殊，如不确定具体元件型号，可以不录具体元件，但其他附属配件（如包装袋）应据实录入，且准确录入默认配货元件种类（调整值）、默认配货元件总量（调整值），智能计算出最终配货提成单价，否则生产部配货时，配货提成将与实际严重不符。</div>
            <div>3、允许需配货套件包含其他需配货套件作为子产品（子套件），但禁止三层及以上套娃（即套件A子产品中有套件B，套件B子产品中又有套件C，……）。</div>
            <div>4、当需配货套件又包含其他需配货套件（子套件），在新增与编辑时，将智能附加该子套件的默认配货元件种类、默认配货元件总量，智能计算出实际的最终配货提成单价，以便配货时提成准确无歧义。</div>
            <div>5、当需配货套件又包含其他需配货套件（子套件），在导出子产品清单时，将智能提取该子套件的附属子产品清单，随主清单一起导出到excel表，以便配货时清单准确无歧义。</div>
            <div>6、按住子产品前面的排序序号上下拖动，可自由调整子产品的排序。</div>
            <i class="sonrule-close iconfont icon-guanbi"> 关闭</i>
        </div>
        <div class="grid-wrap ml10 mr10 bde b-r-3-bottom">
            <table id="grid"></table>
        </div>
        <div id="produceInfo" class="dn">
            <ul class="mod-form-rows">
                <li class="row-item item-pct20 item-md">
                    <div class="label-wrap"><label for="produceClass">默认配货元件种类</label></div>
                    <div class="ctn-wrap">
                        <input type="text" class="ui-input ui-input-dis" style="width:calc(100% - 192px)" name="produceClass1" id="produceClass1" placeholder="0" title="根据录入的子产品自动计算的结果，可在右侧输入调整值。" readonly />
                        <div class="abs" style="top:0;right:0;width:56px;">
                            <span class="f14 lh28">+</span>
                            <input type="number" step="1" class="ui-input fr" style="width:30px" name="produceClass2" id="produceClass2" placeholder="0" title="调整值" />
                        </div>
                    </div>
                </li>
                <li class="row-item item-pct20 item-md">
                    <div class="label-wrap"><label for="produceQty">默认配货元件总量</label></div>
                    <div class="ctn-wrap">
                        <input type="text" class="ui-input ui-input-dis" style="width:calc(100% - 192px)" name="produceQty1" id="produceQty1" placeholder="0" title="根据录入的子产品自动计算的结果，可在右侧输入调整值。" readonly />
                        <div class="abs" style="top:0;right:0;width:56px;">
                            <span class="f14 lh28">+</span>
                            <input type="number" step="1" class="ui-input fr" style="width:30px" name="produceQty2" id="produceQty2" placeholder="0" title="调整值" />
                        </div>
                    </div>
                </li>
                <li class="row-item item-pct20 item-md">
                    <div class="label-wrap">
                        <label for="producePrice1">默认配货提成单价</label>
                    </div>
                    <div class="ctn-wrap">
                        <input type="text" class="ui-input ui-input-2 ui-input-dis" name="producePrice1" id="producePrice1" placeholder="0.00000" title="根据最终的元件种类数与元件总量数、配货提成系数与公式，自动计算的结果，不可随意编辑。" readonly />
                    </div>
                </li>
                <li class="row-item item-pct20 item-md">
                    <div class="label-wrap">
                        <label for="producePrice2">调整配货提成单价</label>
                    </div>
                    <div class="ctn-wrap">
                        <input type="number" step="0.00001" class="ui-input ui-input-2" name="producePrice2" id="producePrice2" placeholder="± 0.00000" title="可手动输入正负数值，来调整最终配货提成单价。" />
                    </div>
                </li>
                <li class="row-item item-pct20 item-md">
                    <div class="label-wrap">
                        <label for="producePrice3">最终配货提成单价</label>
                    </div>
                    <div class="ctn-wrap">
                        <input type="text" class="ui-input ui-input-2 ui-input-dis" name="producePrice3" id="producePrice3" placeholder="0.00000" title="默认配货提成单价 + 调整配货提成单价 = 最终配货提成单价" readonly />
                    </div>
                </li>
            </ul>
        </div>
        <div id="produceInfoHide" class="mt10 ml10 mr10 h28 lh28 tc gray bg-white bde b-r-3">配货提成属性 —— 当前状态：权限不足，无法查看！</div>
        <div id="initCombo" class="dn">
            <input type="text" class="textbox goodsAuto" name="goods" placeholder="请选择" autocomplete="off" readonly />
        </div>
        <div id="storageBox" class="shadow target_box dn"></div>
    </div>
    <script type="text/javascript" src="/statics/js/plugins/fSelect/fSelect.js?v=2026102701"></script>
    <script type="text/javascript" src="/statics/js/plugins/tablednd/jquery.tablednd.js?v=2026102701"></script>
    <script type="text/javascript" src="/statics/js/plugins/uploader/jquery.uploader.js?v=2026102701"></script>
    <script type="text/javascript" src="/statics/js/dist/goods-manage.js?v=2026102701"></script>
</body>

</html>
