<?= view('header') ?>
<link rel="stylesheet" type="text/css" href="/statics/css/manage.css?v=2026102701" />
<style>
    .custom-con {
        position: relative;
        float: left;
        width: 300px;
        height: 680px;
        padding: 10px;
        background-color: #fff;
        border-right: 1px solid #E1E2E3;
        border-bottom: 1px solid #E1E2E3;
    }

    .custom-tit {
        margin: 10px;
        font-size: 14px;
        font-weight: bold;
        text-align: center;
        color: #000;
    }

    .custom-info {
        height: 250px;
        margin: 10px;
        padding: 20px;
        line-height: 24px;
        color: #999;
        background-color: #f0f0f0;
        border-radius: 3px;
    }

    .custom-waring {
        margin: 10px;
        padding: 10px;
        border-radius: 5px;
    }

    .save-btn {
        position: absolute;
        right: 20px;
        bottom: 20px;
    }

    .save-and-redo-btn {
        position: absolute;
        left: 20px;
        bottom: 20px;
    }
</style>
</head>

<body>
    <div class="wrapper">
        <div class="mod-search cf">
            <div class="fr">
                <a id="refresh" class="ui-btn"><i class="iconfont icon-shuaxin"></i><span>刷新</span></a>
            </div>
        </div>
        <div class="diy-con bg-white">
            <div class="custom-con">
                <div class="custom-tit">配货业绩系数</div>
                <div class="custom-info">
                    <p class="fb night">配货业绩指数计算公式为：</p>
                    <p>(每套套件的配货元件种类 × 元件种类系数 + 每套套件的配货元件总量 × 元件总量系数) × 综合系数</p>
                    <p class="mt10 fb night">配货绩效工资计算公式为：</p>
                    <p>配货业绩指数 × 配货绩效工资转化系数</p>
                </div>
                <div class="custom-item">
                    <ul class="mod-form-rows">
                        <li class="row-item item-lg">
                            <div class="label-wrap"><label for="proKitting-par1">元件种类系数</label><span class="must">●</span></div>
                            <div class="ctn-wrap"><input type="number" name="proKitting-par1" class="ui-input" id="proKitting-par1" /></div>
                        </li>
                        <li class="row-item item-lg">
                            <div class="label-wrap"><label for="proKitting-par2">元件总量系数</label><span class="must">●</span></div>
                            <div class="ctn-wrap"><input type="number" name="proKitting-par2" class="ui-input" id="proKitting-par2" /></div>
                        </li>
                        <li class="row-item item-lg">
                            <div class="label-wrap"><label for="proKitting-par3">综合系数</label><span class="must">●</span></div>
                            <div class="ctn-wrap"><input type="number" name="proKitting-par3" class="ui-input" id="proKitting-par3" /></div>
                        </li>
                        <li class="row-item item-lg">
                            <div class="label-wrap"><label for="proKitting-achievementSalaryRate">配货绩效工资转化系数</label><span class="must">●</span></div>
                            <div class="ctn-wrap"><input type="number" name="proKitting-achievementSalaryRate" class="ui-input" id="proKitting-achievementSalaryRate" /></div>
                        </li>
                    </ul>
                </div>
                <a data-type="proKittingSaveAndRedo" class="save-and-redo-btn fr mt10 ui-btn bg-red"><i class="iconfont icon-baocun"></i><span>保存并重算全部配货提成单价</span></a>
                <a data-type="proKitting" class="save-btn fr mt10 ui-btn bg-green"><i class="iconfont icon-baocun"></i><span>保存</span></a>
            </div>
            <div class="custom-con">
                <div class="custom-tit">加工业绩系数</div>
                <div class="custom-info">
                    <p class="fb night">加工业绩指数计算公式为：</p>
                    <p>元件焊接类：焊接业绩 = (贴片焊点数 * 贴片焊点单价 + 直插焊点数 * 直插焊点单价) * 焊接提成百分比；测试业绩 = (贴片焊点数 * 贴片焊点单价 + 直插焊点数 * 直插焊点单价) * 测试提成百分比</p>
                    <p>其他加工类：每小时加工工价 / 每小时可完成数</p>
                    <p class="mt10 fb night">加工绩效工资计算公式为：</p>
                    <p>加工业绩指数 × 加工绩效工资转化系数</p>
                </div>
                <div class="custom-item">
                    <ul class="mod-form-rows">
                        <li class="row-item item-lg">
                            <div class="label-wrap"><label for="proProcessing-par1">贴片焊点单价</label><span class="must">●</span></div>
                            <div class="ctn-wrap"><input type="number" name="proProcessing-par1" class="ui-input" id="proProcessing-par1" /></div>
                        </li>
                        <li class="row-item item-lg">
                            <div class="label-wrap"><label for="proProcessing-par2">直插焊点单价</label><span class="must">●</span></div>
                            <div class="ctn-wrap"><input type="number" name="proProcessing-par2" class="ui-input" id="proProcessing-par2" /></div>
                        </li>
                        <li class="row-item item-lg">
                            <div class="label-wrap"><label for="proProcessing-par3">焊接提成百分比</label><span class="must">●</span></div>
                            <div class="ctn-wrap">
                                <input type="number" name="proProcessing-par3" class="ui-input" id="proProcessing-par3" />
                                <span class="unit">%</span>
                            </div>
                        </li>
                        <li class="row-item item-lg">
                            <div class="label-wrap"><label for="proProcessing-par4">测试提成百分比</label><span class="must">●</span></div>
                            <div class="ctn-wrap">
                                <input type="number" name="proProcessing-par4" class="ui-input" id="proProcessing-par4" />
                                <span class="unit">%</span>
                            </div>
                        </li>
                        <li class="row-item item-lg">
                            <div class="label-wrap"><label for="proProcessing-par5">每小时加工工价</label><span class="must">●</span></div>
                            <div class="ctn-wrap">
                                <input type="number" name="proProcessing-par5" class="ui-input" id="proProcessing-par5" />
                                <span class="unit">元</span>
                            </div>
                        </li>
                        <li class="row-item item-lg">
                            <div class="label-wrap"><label for="proProcessing-achievementSalaryRate">加工绩效工资转化系数</label><span class="must">●</span></div>
                            <div class="ctn-wrap"><input type="number" name="proProcessing-achievementSalaryRate" class="ui-input" id="proProcessing-achievementSalaryRate" /></div>
                        </li>
                    </ul>
                </div>
                <a data-type="proProcessingSaveAndRedo" class="save-and-redo-btn fr mt10 ui-btn bg-red"><i class="iconfont icon-baocun"></i><span>保存并重算全部加工提成单价</span></a>
                <a data-type="proProcessing" class="save-btn fr mt10 ui-btn bg-green"><i class="iconfont icon-baocun"></i><span>保存</span></a>
            </div>
            <div class="custom-con">
                <div class="custom-tit">打包业绩系数</div>
                <div class="custom-info">
                    <p class="fb night">打包业绩指数计算公式为：</p>
                    <p><span class="fb night">小件</span>（20KG以下）：打包总个数 × 小件个数系数 + 打包总重量 × 小件重量系数 + (主快递<span class="red">（目前为申通）</span>理论平均单价 × 主快递发包包裹数 - 主快递实际总运费) × 运费节约系数</p>
                    <p><span class="fb night">大件</span>（20KG及以上）：打包总个数 × 大件个数系数 + 打包总重量 × 大件重量系数</p>
                    <p class="mt10 fb night">打包绩效工资计算公式为：</p>
                    <p>打包业绩指数 × 打包绩效工资转化系数</p>
                </div>
                <div class="custom-item">
                    <ul class="mod-form-rows">
                        <li class="row-item item-lg">
                            <div class="label-wrap"><label for="pack-par1">小件个数系数</label><span class="must">●</span></div>
                            <div class="ctn-wrap"><input type="number" name="pack-par1" class="ui-input" id="pack-par1" /></div>
                        </li>
                        <li class="row-item item-lg">
                            <div class="label-wrap"><label for="pack-par2">小件重量系数</label><span class="must">●</span></div>
                            <div class="ctn-wrap"><input type="number" name="pack-par2" class="ui-input" id="pack-par2" /></div>
                        </li>
                        <li class="row-item item-lg">
                            <div class="label-wrap"><label for="pack-par3">主快递理论平均单价</label><span class="must">●</span></div>
                            <div class="ctn-wrap">
                                <input type="number" name="pack-par3" class="ui-input" id="pack-par3" />
                                <span class="unit">元</span>
                            </div>
                        </li>
                        <li class="row-item item-lg">
                            <div class="label-wrap"><label for="pack-par4">运费节约系数</label><span class="must">●</span></div>
                            <div class="ctn-wrap"><input type="number" name="pack-par4" class="ui-input" id="pack-par4" /></div>
                        </li>
                        <li class="row-item item-lg">
                            <div class="label-wrap"><label for="pack-par5">大件个数系数</label><span class="must">●</span></div>
                            <div class="ctn-wrap"><input type="number" name="pack-par5" class="ui-input" id="pack-par5" /></div>
                        </li>
                        <li class="row-item item-lg">
                            <div class="label-wrap"><label for="pack-par6">大件重量系数</label><span class="must">●</span></div>
                            <div class="ctn-wrap"><input type="number" name="pack-par6" class="ui-input" id="pack-par6" /></div>
                        </li>
                        <li class="row-item item-lg">
                            <div class="label-wrap"><label for="pack-achievementSalaryRate">打包绩效工资转化系数</label><span class="must">●</span></div>
                            <div class="ctn-wrap"><input type="number" name="pack-achievementSalaryRate" class="ui-input" id="pack-achievementSalaryRate" /></div>
                        </li>
                    </ul>
                </div>
                <a data-type="pack" class="save-btn fr mt10 ui-btn bg-green"><i class="iconfont icon-baocun"></i><span>保存</span></a>
            </div>
            <div class="custom-con">
                <div class="custom-tit">销售业绩系数</div>
                <div class="custom-info">
                    <p class="fb night">销售业绩指数计算公式为：</p>
                    <p>手工订单金额 × 手工订单系数 + 特殊销售金额 × 特殊销售系数 + 网店销售金额 × 网店销售系数</p>
                    <p class="mt10 fb night">销售绩效工资计算公式为：</p>
                    <p>销售业绩指数 × 销售绩效工资转化系数</p>
                </div>
                <div class="custom-item">
                    <ul class="mod-form-rows">
                        <li class="row-item item-lg">
                            <div class="label-wrap"><label for="sale-par1">手工订单系数</label><span class="must">●</span></div>
                            <div class="ctn-wrap"><input type="number" name="sale-par1" class="ui-input" id="sale-par1" /></div>
                        </li>
                        <li class="row-item item-lg">
                            <div class="label-wrap"><label for="sale-par2">特殊销售系数</label><span class="must">●</span></div>
                            <div class="ctn-wrap"><input type="number" name="sale-par2" class="ui-input" id="sale-par2" /></div>
                        </li>
                        <li class="row-item item-lg">
                            <div class="label-wrap"><label for="sale-par3">网店销售系数</label><span class="must">●</span></div>
                            <div class="ctn-wrap"><input type="number" name="sale-par3" class="ui-input" id="sale-par3" /></div>
                        </li>
                        <li class="row-item item-lg">
                            <div class="label-wrap"><label for="sale-achievementSalaryRate">销售绩效工资转化系数</label><span class="must">●</span></div>
                            <div class="ctn-wrap"><input type="number" name="sale-achievementSalaryRate" class="ui-input" id="sale-achievementSalaryRate" /></div>
                        </li>
                    </ul>
                </div>
                <a data-type="sale" class="save-btn fr mt10 ui-btn bg-green"><i class="iconfont icon-baocun"></i><span>保存</span></a>
            </div>
            <div class="custom-con">
                <div class="custom-tit">采购业绩系数</div>
                <div class="custom-info">
                    <p class="fb night">采购业绩指数计算公式为：</p>
                    <p>标准采购产品种类 × 标准采购系数 + 特殊采购产品种类 × 特殊采购系数 + 新增商家数量 × 商家系数 + 特殊采购竞价毛利（正毛利） × 竞价系数</p>
                    <p class="mt10 fb night">采购绩效工资计算公式为：</p>
                    <p>采购业绩指数 × 采购绩效工资转化系数</p>
                </div>
                <div class="custom-item">
                    <ul class="mod-form-rows">
                        <li class="row-item item-lg">
                            <div class="label-wrap"><label for="pur-par1">标准采购系数</label><span class="must">●</span></div>
                            <div class="ctn-wrap"><input type="number" name="pur-par1" class="ui-input" id="pur-par1" /></div>
                        </li>
                        <li class="row-item item-lg">
                            <div class="label-wrap"><label for="pur-par2">特殊采购系数</label><span class="must">●</span></div>
                            <div class="ctn-wrap"><input type="number" name="pur-par2" class="ui-input" id="pur-par2" /></div>
                        </li>
                        <li class="row-item item-lg">
                            <div class="label-wrap"><label for="pur-par3">商家系数</label><span class="must">●</span></div>
                            <div class="ctn-wrap"><input type="number" name="pur-par3" class="ui-input" id="pur-par3" /></div>
                        </li>
                        <li class="row-item item-lg">
                            <div class="label-wrap"><label for="pur-par4">竞价系数</label><span class="must">●</span></div>
                            <div class="ctn-wrap"><input type="number" name="pur-par4" class="ui-input" id="pur-par4" /></div>
                        </li>
                        <li class="row-item item-lg">
                            <div class="label-wrap"><label for="pur-achievementSalaryRate">采购绩效工资转化系数</label><span class="must">●</span></div>
                            <div class="ctn-wrap"><input type="number" name="pur-achievementSalaryRate" class="ui-input" id="pur-achievementSalaryRate" /></div>
                        </li>
                    </ul>
                </div>
                <a data-type="pur" class="save-btn fr mt10 ui-btn bg-green"><i class="iconfont icon-baocun"></i><span>保存</span></a>
            </div>
        </div>
    </div>
    <script type="text/javascript" src="/statics/js/dist/exp-par.js?v=2026102701"></script>
</body>

</html>
