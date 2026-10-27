<?= view('header') ?>
<<<<<<< HEAD
<link rel="stylesheet" type="text/css" href="/statics/css/main.css?v=2026102701" />
=======
<link rel="stylesheet" type="text/css" href="/statics/css/main.css?v=2026101403" />
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
</head>

<body>
    <div id="bd">
        <div class="col-main-left">
            <div class="quick-links">
                <div id="evaluateChart" class="link-tab dn">
                    <div id="userChart1" class="link-large">
                        <div class="link-large-tit">
                            <span>考评趋势</span>
                            <i class="link-btn iconfont icon-shuaxin" id="userchart-refresh-btn-1" title="刷新"></i>
                            <div id="userchart-refresh-msg-1" class="link-msg dn"></div>
                        </div>
                        <div class="link-large-con">
                            <div id="evaluateChartCanvas" class="user-chart" style="height:200px;"></div>
                        </div>
                    </div>
                </div>
                <div id="achievementChart" class="link-tab dn">
                    <div id="userChart2" class="link-large">
                        <div class="link-large-tit">
                            <span>绩效趋势</span>
                            <i class="link-btn iconfont icon-shuaxin" id="userchart-refresh-btn-2" title="刷新"></i>
                            <div id="userchart-refresh-msg-2" class="link-msg dn"></div>
                        </div>
                        <div class="link-large-con">
                            <div id="achievementChartCanvas" class="user-chart" style="height:200px;"></div>
                        </div>
                    </div>
                </div>
                <div id="salesTargetChart" class="link-tab dn">
                    <div id="userChart3" class="link-large">
                        <div class="link-large-tit">
                            <span>销售目标</span>
                            <i class="link-btn iconfont icon-shuaxin" id="userchart-refresh-btn-3" title="刷新"></i>
                            <div id="userchart-refresh-msg-3" class="link-msg dn"></div>
                        </div>
                        <div class="link-large-con">
                            <div id="salesTargetChartCanvas" class="user-chart" style="height:200px;"></div>
                        </div>
                    </div>
                </div>
                <div id="invoiceBackChart" class="link-tab dn">
                    <div id="userChart4" class="link-large">
                        <div class="link-large-tit">
                            <span>采购回票</span>
                            <i class="link-btn iconfont icon-shuaxin" id="userchart-refresh-btn-4" title="刷新"></i>
                            <div id="userchart-refresh-msg-4" class="link-msg dn"></div>
                        </div>
                        <div class="link-large-con">
                            <div id="invoiceBackChartCanvas" class="user-chart" style="height:200px;"></div>
                        </div>
                    </div>
                </div>
                <div class="link-tab link-tab-100">
                    <div class="link-large">
                        <div class="link-large-tit">
                            <span>关注事项</span>
                            <i class="link-btn iconfont icon-shuaxin" id="notice-refresh-btn" title="刷新"></i>
                            <div id="notice-refresh-msg" class="link-msg dn"></div>
                        </div>
                        <div class="link-large-con pb20">
                            <div id="taskNotice" class="link-small dn">
                                <a tabid="index-task" data-right="TASK_QUERY" tabTxt="<i class='iconfont icon-renwu f12 mr5'></i>任务清单" parentOpen="true" rel="pageTab" href="/Uri/task_list">
                                    <span class="info"></span>
                                    <span class="iconfont icon-renwu icon"></span>
                                    <span class="menu">任务清单</span>
                                </a>
                            </div>
                            <div id="planNotice" class="link-small dn">
                                <a tabid="index-plan" data-right="PLAN_QUERY" tabTxt="<i class='iconfont icon-jihua f12 mr5'></i>工作目标" parentOpen="true" rel="pageTab" href="/Uri/plan_list">
                                    <span class="info"></span>
                                    <span class="iconfont icon-jihua icon"></span>
                                    <span class="menu">工作目标</span>
                                </a>
                            </div>
                            <div id="dutyNotice" class="link-small dn">
                                <a tabid="index-duty" data-right="DUTY_QUERY" tabTxt="<i class='iconfont icon-jiaojie f12 mr5'></i>工作转交" parentOpen="true" rel="pageTab" href="/Uri/duty_list">
                                    <span class="info"></span>
                                    <span class="iconfont icon-jiaojie icon"></span>
                                    <span class="menu">工作转交</span>
                                </a>
                            </div>
                            <div id="managePlanNotice" class="link-small dn">
                                <a tabid="index-managePlan" data-right="MANAGE_PLAN_QUERY" tabTxt="<i class='iconfont icon-jihua f12 mr5'></i>管理计划" parentOpen="true" rel="pageTab" href="/Uri/manage_plan_list">
                                    <span class="info"></span>
                                    <span class="iconfont icon-jihua icon"></span>
                                    <span class="menu">管理计划</span>
                                </a>
                            </div>
                            <div id="discountReqNotice" class="link-small dn">
                                <a tabid="sales-discountReq" data-right="SERVICE_QUERY" tabTxt="<i class='iconfont icon-shenqing f12 mr5'></i>优惠申请" parentOpen="true" rel="pageTab" href="/Uri/discountReq_list">
                                    <span class="info"></span>
                                    <span class="iconfont icon-shenqing icon"></span>
                                    <span class="menu">优惠申请</span>
                                </a>
                            </div>
                            <div id="quoteReqNotice" class="link-small dn">
                                <a tabid="sales-quoteReq" data-right="SERVICE_QUERY" tabTxt="<i class='iconfont icon-shenqing f12 mr5'></i>报价审批" parentOpen="true" rel="pageTab" href="/Uri/quoteReq_list">
                                    <span class="info"></span>
                                    <span class="iconfont icon-shenqing icon"></span>
                                    <span class="menu">报价审批</span>
                                </a>
                            </div>
                            <div id="purReqNotice" class="link-small dn">
                                <a tabid="purchase-purchaseReq" data-right="PURREQ_QUERY" tabTxt="<i class='iconfont icon-shenqing f12 mr5'></i>采购申请" parentOpen="true" rel="pageTab" href="/Uri/purchaseReq_list">
                                    <span class="info"></span>
                                    <span class="iconfont icon-shenqing icon"></span>
                                    <span class="menu">采购申请</span>
                                </a>
                            </div>
                            <div id="purContractReqNotice" class="link-small dn">
                                <a tabid="purchase-purchaseContractReq" data-right="PURREQ_QUERY" tabTxt="<i class='iconfont icon-shenqing f12 mr5'></i>特采申请" parentOpen="true" rel="pageTab" href="/Uri/purchaseContractReq_list">
                                    <span class="info"></span>
                                    <span class="iconfont icon-shenqing icon"></span>
                                    <span class="menu">特采申请</span>
                                </a>
                            </div>
                            <div id="produceKittingReqNotice" class="link-small dn">
                                <a tabid="produce-produceKittingReq" data-right="PROREQ_QUERY" tabTxt="<i class='iconfont icon-shenqing f12 mr5'></i>配货申请" parentOpen="true" rel="pageTab" href="/Uri/produceKittingReq_list">
                                    <span class="info"></span>
                                    <span class="iconfont icon-shenqing icon"></span>
                                    <span class="menu">配货申请</span>
                                </a>
                            </div>
                            <div id="payReqNotice" class="link-small dn">
                                <a tabid="money-payReq" data-right="PAYREQ_QUERY" tabTxt="<i class='iconfont icon-shenqing f12 mr5'></i>付款申请" parentOpen="true" rel="pageTab" href="/Uri/payReq_list">
                                    <span class="info"></span>
                                    <span class="iconfont icon-shenqing icon"></span>
                                    <span class="menu">付款申请</span>
                                </a>
                            </div>
                            <div id="expensesReqNotice" class="link-small dn">
                                <a tabid="money-expensesReq" data-right="EXPENSESREQ_QUERY" tabTxt="<i class='iconfont icon-shenqing f12 mr5'></i>报销申请" parentOpen="true" rel="pageTab" href="/Uri/expensesReq_list">
                                    <span class="info"></span>
                                    <span class="iconfont icon-shenqing icon"></span>
                                    <span class="menu">报销申请</span>
                                </a>
                            </div>
                            <div id="goodsDevReqNotice" class="link-small dn">
                                <a tabid="develop-goodsDevReq" data-right="GOODSDEVREQ_QUERY" tabTxt="<i class='iconfont icon-shenqing f12 mr5'></i>定制申请" parentOpen="true" rel="pageTab" href="/Uri/goods_dev_req_list">
                                    <span class="info"></span>
                                    <span class="iconfont icon-shenqing icon"></span>
                                    <span class="menu">定制申请</span>
                                </a>
                            </div>
                            <div id="goodsFileReqNotice" class="link-small dn">
                                <a tabid="develop-goodsFileReq" data-right="GOODSFILEREQ_QUERY" tabTxt="<i class='iconfont icon-shenqing f12 mr5'></i>下载申请" parentOpen="true" rel="pageTab" href="/Uri/goods_file_req_list">
                                    <span class="info"></span>
                                    <span class="iconfont icon-shenqing icon"></span>
                                    <span class="menu">下载申请</span>
                                </a>
                            </div>
                            <div id="poNotice" class="link-small dn">
                                <a tabid="purchase-purchaseOrder" data-right="PO_QUERY" tabTxt="<i class='iconfont icon-danju f12 mr5'></i>采购订单" parentOpen="true" rel="pageTab" href="/Uri/purchaseOrder_list">
                                    <span class="info"></span>
                                    <span class="iconfont icon-danju icon"></span>
                                    <span class="menu">采购订单</span>
                                </a>
                            </div>
                            <div id="pcNotice" class="link-small dn">
                                <a tabid="purchase-purchaseContract" data-right="PC_QUERY" tabTxt="<i class='iconfont icon-danju f12 mr5'></i>特殊采购" parentOpen="true" rel="pageTab" href="/Uri/purchaseContract_list">
                                    <span class="info"></span>
                                    <span class="iconfont icon-danju icon"></span>
                                    <span class="menu">特殊采购</span>
                                </a>
                            </div>
                            <div id="pcFillNotice" class="link-small dn">
                                <a tabid="purchase-purchaseContractFill" data-right="PC_QUERY" tabTxt="<i class='iconfont icon-danju f12 mr5'></i>特采补买" parentOpen="true" rel="pageTab" href="/Uri/purchaseContractFill_list">
                                    <span class="info"></span>
                                    <span class="iconfont icon-danju icon"></span>
                                    <span class="menu">特采补买</span>
                                </a>
                            </div>
                            <div id="pcLostNotice" class="link-small dn">
                                <a tabid="purchase-purchaseContractLost" data-right="PC_QUERY" tabTxt="<i class='iconfont icon-danju f12 mr5'></i>特采遗漏" parentOpen="true" rel="pageTab" href="/Uri/purchaseContractLost_list">
                                    <span class="info"></span>
                                    <span class="iconfont icon-danju icon"></span>
                                    <span class="menu">特采遗漏</span>
                                </a>
                            </div>
                            <div id="pcModifyPurAmountNotice" class="link-small dn">
                                <a tabid="purchase-purchaseContractModifyPurAmount" data-right="PC_QUERY" tabTxt="<i class='iconfont icon-danju f12 mr5'></i>改价通知" parentOpen="true" rel="pageTab" href="/Uri/purchaseContractModifyPurAmount_list">
                                    <span class="info"></span>
                                    <span class="iconfont icon-danju icon"></span>
                                    <span class="menu">改价通知</span>
                                </a>
                            </div>
                            <div id="purQCNotice" class="link-small dn">
                                <a tabid="purchase-purchaseQC" data-right="PURQC_QUERY" tabTxt="<i class='iconfont icon-zhijian f12 mr5'></i>采购质检" parentOpen="true" rel="pageTab" href="/Uri/purchase_qc">
                                    <span class="info"></span>
                                    <span class="iconfont icon-zhijian icon"></span>
                                    <span class="menu">采购质检</span>
                                </a>
                            </div>
                            <div id="purQCBackNotice" class="link-small dn">
                                <a tabid="purchase-purchaseQCBack" data-right="PURQCBACK_QUERY" tabTxt="<i class='iconfont icon-zhijian f12 mr5'></i>质检打回" parentOpen="true" rel="pageTab" href="/Uri/purchase_qc_back">
                                    <span class="info"></span>
                                    <span class="iconfont icon-zhijian icon"></span>
                                    <span class="menu">质检打回</span>
                                </a>
                            </div>
                            <div id="purSuggestNotice" class="link-small dn">
                                <a tabid="purchase-purchaseSuggest" data-right="PC_QUERY" tabTxt="<i class='iconfont icon-jianyi f12 mr5'></i>采购建议" parentOpen="true" rel="pageTab" href="/Uri/purchase_suggest_list">
                                    <span class="info"></span>
                                    <span class="iconfont icon-jianyi icon"></span>
                                    <span class="menu">采购建议</span>
                                </a>
                            </div>
                            <div id="soNotice" class="link-small dn">
                                <a tabid="sales-salesOrder" data-right="SO_QUERY" tabTxt="<i class='iconfont icon-danju f12 mr5'></i>销售订单" parentOpen="true" rel="pageTab" href="/Uri/salesOrder_list">
                                    <span class="info"></span>
                                    <span class="iconfont icon-danju icon"></span>
                                    <span class="menu">销售订单</span>
                                </a>
                            </div>
                            <div id="scNotice" class="link-small dn">
                                <a tabid="sales-salesContract" data-right="SC_QUERY" tabTxt="<i class='iconfont icon-danju f12 mr5'></i>特殊销售" parentOpen="true" rel="pageTab" href="/Uri/salesContract_list">
                                    <span class="info"></span>
                                    <span class="iconfont icon-danju icon"></span>
                                    <span class="menu">特殊销售</span>
                                </a>
                            </div>
                            <div id="serviceNotice" class="link-small dn">
                                <a tabid="sales-service" data-right="SERVICE_QUERY" tabTxt="<i class='iconfont icon-kefu f12 mr5'></i>客户之声" parentOpen="true" rel="pageTab" href="/Uri/service_list">
                                    <span class="info"></span>
                                    <span class="iconfont icon-kefu icon"></span>
                                    <span class="menu">客户之声</span>
                                </a>
                            </div>
                            <div id="reviewHoldNotice" class="link-small dn">
                                <a tabid="sales-reviewHold" data-right="SERVICE_QUERY" tabTxt="<i class='iconfont icon-pingjia f12 mr5'></i>差评删改" parentOpen="true" rel="pageTab" href="/Uri/reviewHold_list">
                                    <span class="info"></span>
                                    <span class="iconfont icon-pingjia icon"></span>
                                    <span class="menu">差评删改</span>
                                </a>
                            </div>
                            <div id="discountReqNotice" class="link-small dn">
                                <a tabid="sales-discountReq" data-right="SERVICE_QUERY" tabTxt="<i class='iconfont icon-kefu f12 mr5'></i>优惠申请" parentOpen="true" rel="pageTab" href="/Uri/discountReq_list">
                                    <span class="info"></span>
                                    <span class="iconfont icon-shenqing icon"></span>
                                    <span class="menu">优惠申请</span>
                                </a>
                            </div>
                            <div id="potentialCustomerNotice" class="link-small dn">
                                <a tabid="sales-potentialCustomer" data-right="SERVICE_QUERY" tabTxt="<i class='iconfont icon-kefu f12 mr5'></i>潜客登记" parentOpen="true" rel="pageTab" href="/Uri/potentialCustomer_list">
                                    <span class="info"></span>
                                    <span class="iconfont icon-kehu icon"></span>
                                    <span class="menu">潜客登记</span>
                                </a>
                            </div>
                            <div id="bidProgramNotice" class="link-small dn">
                                <a tabid="bid-bidProgram" data-right="BIDPROGRAM_QUERY" tabTxt="<i class='iconfont icon-zhaotoubiao f12 mr5'></i>投标项目" parentOpen="true" rel="pageTab" href="/Uri/bid_program_list">
                                    <span class="info"></span>
                                    <span class="iconfont icon-zhaotoubiao icon"></span>
                                    <span class="menu">投标项目</span>
                                </a>
                            </div>
                            <div id="deliverDullNotice" class="link-small dn">
                                <a tabid="deliver-deliverBagDull" data-right="DELIVERBAG_QUERY" tabTxt="<i class='iconfont icon-baoguo f12 mr5'></i>发包滞留" parentOpen="true" rel="pageTab" href="/Uri/deliver_bag_dull">
                                    <span class="info"></span>
                                    <span class="iconfont icon-baoguo icon"></span>
                                    <span class="menu">发包滞留</span>
                                </a>
                            </div>
                            <div id="goodsTaskNotice" class="link-small dn">
                                <a tabid="develop-goodsTask" data-right="GOODSTASK_QUERY" tabTxt="<i class='iconfont icon-renwu f12 mr5'></i>产品开发" parentOpen="true" rel="pageTab" href="/Uri/goods_task_list">
                                    <span class="info"></span>
                                    <span class="iconfont icon-renwu icon"></span>
                                    <span class="menu">产品开发</span>
                                </a>
                            </div>
                            <div id="proKittingTaskNotice" class="link-small dn">
                                <a tabid="produce-produceKittingTask" data-right="PROTASK_QUERY" tabTxt="<i class='iconfont icon-renwu f12 mr5'></i>配货任务" parentOpen="true" rel="pageTab" href="/Uri/produceKittingTask_list">
                                    <span class="info"></span>
                                    <span class="iconfont icon-renwu icon"></span>
                                    <span class="menu">配货任务</span>
                                </a>
                            </div>
                            <div id="proKittingTaskUpNotice" class="link-small dn">
                                <a tabid="produce-produceKittingTaskUp" data-right="PROTASK_QUERY" tabTxt="<i class='iconfont icon-jiaofu f12 mr5'></i>配货交付" parentOpen="true" rel="pageTab" href="/Uri/produceKittingTask_up_list">
                                    <span class="info"></span>
                                    <span class="iconfont icon-jiaofu icon"></span>
                                    <span class="menu">配货交付</span>
                                </a>
                            </div>
                            <div id="proProcessingTaskUpNotice" class="link-small dn">
                                <a tabid="produce-produceProcessingTaskUp" data-right="PROTASK_QUERY" tabTxt="<i class='iconfont icon-jiaofu f12 mr5'></i>加工交付" parentOpen="true" rel="pageTab" href="/Uri/produceProcessingTask_up_list">
                                    <span class="info"></span>
                                    <span class="iconfont icon-jiaofu icon"></span>
                                    <span class="menu">加工交付</span>
                                </a>
                            </div>
                            <div id="saleOrderSpiderErrGoodsNotice" class="link-small dn">
                                <a tabid="product-salesOrderSpiderErrGoods" data-right="SA_SPIDER_ERR_QUERY" tabTxt="<i class='iconfont icon-tongbu f12 mr5'></i>同步异常" parentOpen="true" rel="pageTab" href="/Uri/salesOrder_spiderErrGoods">
                                    <span class="info"></span>
                                    <span class="iconfont icon-tongbu icon"></span>
                                    <span class="menu">同步异常</span>
                                </a>
                            </div>
                            <div id="saleOrderSpiderErrPriceNotice" class="link-small dn">
                                <a tabid="product-salesOrderSpiderErrPrice" data-right="SA_SPIDER_ERR_QUERY" tabTxt="<i class='iconfont icon-tongbu f12 mr5'></i>售价异常" parentOpen="true" rel="pageTab" href="/Uri/salesOrder_spiderErrPrice">
                                    <span class="info"></span>
                                    <span class="iconfont icon-tongbu icon"></span>
                                    <span class="menu">售价异常</span>
                                </a>
                            </div>
                            <div id="competitiveMonitorNotice" class="link-small dn">
                                <a tabid="product-competitiveMonitor" data-right="COMPETITIVE_MONITOR_QUERY" tabTxt="<i class='iconfont icon-jiankong f12 mr5'></i>竞品监控" parentOpen="true" rel="pageTab" href="/Uri/competitive_monitor_list">
                                    <span class="info"></span>
                                    <span class="iconfont icon-jiankong icon"></span>
                                    <span class="menu">竞品监控</span>
                                </a>
                            </div>
                            <div id="receiptNotice" class="link-small dn">
                                <a tabid="money-receipt" data-right="RECEIPT_QUERY" tabTxt="<i class='iconfont icon-caiwudanju f12 mr5'></i>业务收款" parentOpen="true" rel="pageTab" href="/Uri/receipt_list">
                                    <span class="info"></span>
                                    <span class="iconfont icon-caiwudanju icon"></span>
                                    <span class="menu">业务收款</span>
                                </a>
                            </div>
                            <div id="paymentNotice" class="link-small dn">
                                <a tabid="money-payment" data-right="PAYMENT_QUERY" tabTxt="<i class='iconfont icon-caiwudanju f12 mr5'></i>业务付款" parentOpen="true" rel="pageTab" href="/Uri/payment_list">
                                    <span class="info"></span>
                                    <span class="iconfont icon-caiwudanju icon"></span>
                                    <span class="menu">业务付款</span>
                                </a>
                            </div>
                            <div id="incomeNotice" class="link-small dn">
                                <a tabid="money-income" data-right="INCOME_QUERY" tabTxt="<i class='iconfont icon-caiwudanju f12 mr5'></i>其他收入" parentOpen="true" rel="pageTab" href="/Uri/income_list">
                                    <span class="info"></span>
                                    <span class="iconfont icon-caiwudanju icon"></span>
                                    <span class="menu">其他收入</span>
                                </a>
                            </div>
                            <div id="expendNotice" class="link-small dn">
                                <a tabid="money-expend" data-right="EXPEND_QUERY" tabTxt="<i class='iconfont icon-caiwudanju f12 mr5'></i>其他支出" parentOpen="true" rel="pageTab" href="/Uri/expend_list">
                                    <span class="info"></span>
                                    <span class="iconfont icon-caiwudanju icon"></span>
                                    <span class="menu">其他支出</span>
                                </a>
                            </div>
                            <div id="deliverNotice" class="link-small dn">
                                <a tabid="deliver-deliver" data-right="DELIVER_QUERY" tabTxt="<i class='iconfont icon-danju f12 mr5'></i>快递月结" parentOpen="true" rel="pageTab" href="/Uri/deliver_list">
                                    <span class="info"></span>
                                    <span class="iconfont icon-danju icon"></span>
                                    <span class="menu">快递月结</span>
                                </a>
                            </div>
                            <div id="invoiceMissNotice" class="link-small dn">
                                <a tabid="money-invoiceMiss" data-right="INVOICE_QUERY" tabTxt="<i class='iconfont icon-danju f12 mr5'></i>收票遗漏" parentOpen="true" rel="pageTab" href="/Uri/invoice_miss_list">
                                    <span class="info"></span>
                                    <span class="iconfont icon-danju icon"></span>
                                    <span class="menu">收票遗漏</span>
                                </a>
                            </div>
                            <div id="invoiceErrNotice" class="link-small dn">
                                <a tabid="money-invoiceErr" data-right="FINANCE_MANAGER" tabTxt="<i class='iconfont icon-fapiao f12 mr5'></i>识票异常" parentOpen="true" rel="pageTab" href="/Uri/invoice_err_list">
                                    <span class="info"></span>
                                    <span class="iconfont icon-fapiao icon"></span>
                                    <span class="menu">识票异常</span>
                                </a>
                            </div>
                            <div id="clockinNotice" class="link-small dn">
                                <a tabid="personnel-clockin" data-right="CLOCKIN_QUERY" tabTxt="<i class='iconfont icon-kaoqin f12 mr5'></i>考勤打卡" parentOpen="true" rel="pageTab" href="/Uri/clockin_list">
                                    <span class="info"></span>
                                    <span class="iconfont icon-kaoqin icon"></span>
                                    <span class="menu">考勤打卡</span>
                                </a>
                            </div>
                            <div id="workFillNotice" class="link-small dn">
                                <a tabid="personnel-workFill" data-right="WORKHOUR_QUERY" tabTxt="<i class='iconfont icon-buban f12 mr5'></i>补班申报" parentOpen="true" rel="pageTab" href="/Uri/work_fill_list?type=0">
                                    <span class="info"></span>
                                    <span class="iconfont icon-buban icon"></span>
                                    <span class="menu">补班申报</span>
                                </a>
                            </div>
                            <div id="workOvertimeNotice" class="link-small dn">
                                <a tabid="personnel-workOvertime" data-right="WORKHOUR_QUERY" tabTxt="<i class='iconfont icon-jiaban f12 mr5'></i>加班申报" parentOpen="true" rel="pageTab" href="/Uri/work_overtime_list?type=1">
                                    <span class="info"></span>
                                    <span class="iconfont icon-jiaban icon"></span>
                                    <span class="menu">加班申报</span>
                                </a>
                            </div>
                            <div id="workHourNotice" class="link-small dn">
                                <a tabid="personnel-workHour" data-right="WORKHOUR_QUERY" tabTxt="<i class='iconfont icon-jishi f12 mr5'></i>计时申报" parentOpen="true" rel="pageTab" href="/Uri/work_hour_list?type=2">
                                    <span class="info"></span>
                                    <span class="iconfont icon-jishi icon"></span>
                                    <span class="menu">计时申报</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="link-tab link-tab-100 dn">
                    <div class="link-large ovh">
                        <div class="link-large-tit">
                            <span>供货限期计时</span>
                            <i class="link-btn iconfont icon-shuaxin" id="countdownRefreshBtn" title="刷新"></i>
                            <div id="countdownRefreshMsg" class="link-msg dn"></div>
                        </div>
                        <div class="fl ml10 mr5 mb10 bde b-r-3" style="width:calc(50% - 17px)">
                            <div class="lh30 gray bbe ovh">
                                <div class="fl pct15 pl10 es">特销单号</div>
                                <div class="fl pct20 pl10 es">购方名称</div>
                                <div class="fl pct15 pl10 es">销售人</div>
                                <div class="fl pct15 pl10 es">交货限期</div>
                                <div class="fr mr20 es">超期计时</div>
                            </div>
                            <div id="saleContactCountdownData1" class="link-large-con" style="max-height:204px;overflow-y:auto;"></div>
                        </div>
                        <div class="fl ml5 mr10 mb10 bde b-r-3" style="width:calc(50% - 17px)">
                            <div class="lh30 gray bbe ovh">
                                <div class="fl pct15 pl10 es">特销单号</div>
                                <div class="fl pct20 pl10 es">购方名称</div>
                                <div class="fl pct15 pl10 es">销售人</div>
                                <div class="fl pct15 pl10 es">交货限期</div>
                                <div class="fr mr20 es">临期计时</div>
                            </div>
                            <div id="saleContactCountdownData2" class="link-large-con" style="max-height:204px;overflow-y:auto;"></div>
                        </div>
                    </div>
                </div>
                <div id="goodsRecentNotice" class="link-tab link-tab-60">
                    <div class="tab-wrapper">
                        <div class="tab-menu">
                            <div class="tab-intro">产品动态</div>
                            <div class="tab-tit-con">
                                <div class="tab-tit active"><span class="es">缺货申报</span></div>
                                <div class="tab-tit"><span class="es">配货交付</span></div>
                                <div class="tab-tit"><span class="es">采购到货</span></div>
                                <div class="tab-tit"><span class="es">新品上架</span></div>
                                <div class="tab-search">
                                    <input type="text" id="skey" class="fl ui-input" placeholder="输入任意关键词" />
                                    <div class="fl ml5 ui-btn-group">
                                        <a id="search" class="db fl lh22 pl5 pr5 hover-white b-r-3"><i class="iconfont icon-chaxun f12 mr5"></i><span>查询</span></a>
                                    </div>
                                </div>
                            </div>
                            <i class="link-btn iconfont icon-shuaxin" id="grid-refresh-btn" title="刷新"></i>
                            <div id="grid-refresh-msg" class="link-msg dn"></div>
                        </div>
                        <div class="tab-content">
                            <div class="col-tab">
                                <div class="grid-wrap">
                                    <table id="grid1"></table>
                                    <div id="page1"></div>
                                </div>
                            </div>
                            <div class="col-tab">
                                <div class="grid-wrap">
                                    <table id="grid2"></table>
                                    <div id="page2"></div>
                                </div>
                            </div>
                            <div class="col-tab">
                                <div class="grid-wrap">
                                    <table id="grid3"></table>
                                    <div id="page3"></div>
                                </div>
                            </div>
                            <div class="col-tab">
                                <div class="grid-wrap">
                                    <table id="grid4"></table>
                                    <div id="page4"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="newsNotice" class="link-tab link-tab-40">
                    <div class="tab-wrapper">
                        <div class="tab-menu">
                            <div class="tab-intro">通知公告</div>
                            <div class="tab-tit-con">
                                <div class="tab-search">
                                    <input type="text" id="skey" class="fl ui-input" placeholder="输入任意关键词" />
                                    <div class="fl ml5 ui-btn-group">
                                        <a id="search" class="db fl lh22 pl5 pr5 hover-white b-r-3"><i class="iconfont icon-chaxun f12 mr5"></i><span>查询</span></a>
                                    </div>
                                </div>
                            </div>
                            <i class="link-btn iconfont icon-shuaxin" id="grid-refresh-btn" title="刷新"></i>
                            <div id="grid-refresh-msg" class="link-msg dn"></div>
                        </div>
                        <div class="tab-content">
                            <div class="col-tab">
                                <div class="grid-wrap">
                                    <table id="grid5"></table>
                                    <div id="page5"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-main-right">
            <div class="user-tab">
                <div class="user-tit">
                    <span>个人信息</span>
                    <i class="link-btn iconfont icon-shuaxin" id="user-info-refresh-btn" title="刷新"></i>
                    <div id="user-info-refresh-msg" class="link-msg dn"></div>
                </div>
                <div id="user-happybirthday" class="user-happybirthday dn"></div>
                <div class="user-con">
                    <div class="user-info">
                        <div id="user-stars" class="user-stars"></div>
                        <div class="user-info-con">
                            <span id="user-name" class="user-name"></span>
                            <span id="user-department" class="user-department"></span>
                            <span id="user-position" class="user-position"></span>
                            <span id="user-work-years" class="user-work-years"></span>
                            <span id="user-level" class="user-level"></span>
                            <span id="user-exp" class="user-exp"></span>
                        </div>
                    </div>
                    <div class="user-exp-percent">
                        <div class="user-exp-percent-bar progress-bar"></div>
                        <div class="user-exp-percent-text"></div>
                    </div>
                </div>
                <div id="lateTimes" class="mt10 ml20 mr20 lh30 tc white bg-red b-r-100 dn"></div>
                <div class="user-last-month-info">
                    <div class="user-last-month-tit">上月排名与奖章<div class="my-all-medals fn hover-silver">历史奖章</div></div>
                    <div class="medals"></div>
                </div>
                <div class="user-position-info">
                    <div class="user-position-info-con">
                        <div class="user-position-info-tit">岗位职责<div class="saler-duty-choose fn hover-green dn">选择今日值班内容</div></div>
                        <div id="duty" class="user-position-info-text"></div>
                    </div>
                    <div class="user-position-info-con dn">
                        <div class="user-position-info-tit">上级主管</div>
                        <div id="superiors" class="user-position-info-text"></div>
                    </div>
                    <div class="user-position-info-con dn">
                        <div class="user-position-info-tit">下属员工</div>
                        <div id="members" class="user-position-info-text"></div>
                    </div>
                    <div id="toUserStaffMap" class="user-position-staff-all fn hover-silver">更多同事</div>
                </div>
            </div>
            <div class="user-level-preview dn">
                <div class="user-level-preview-tit">成长等级<i class="user-level-preview-close iconfont icon-shanchu fn f12" title="关闭"></i></div>
                <div class="user-level-preview-con">
                    <div class="user-level-preview-item"><span>等级徽章</span><span>等级名称</span><span>最小经验</span><span>最大经验</span></div>
                    <div id="userlevel1" class="user-level-preview-item">
<<<<<<< HEAD
                        <img src="/statics/img/user-star-1.png?v=2026102701"><span>萌新</span><span>0</span><span>75</span>
                    </div>
                    <div id="userlevel2" class="user-level-preview-item">
                        <img src="/statics/img/user-star-2.png?v=2026102701"><span>青铜</span><span>76</span><span>299</span>
                    </div>
                    <div id="userlevel3" class="user-level-preview-item">
                        <img src="/statics/img/user-star-3.png?v=2026102701"><span>白银</span><span>300</span><span>699</span>
                    </div>
                    <div id="userlevel4" class="user-level-preview-item">
                        <img src="/statics/img/user-star-4.png?v=2026102701"><span>黄金</span><span>700</span><span>1299</span>
                    </div>
                    <div id="userlevel5" class="user-level-preview-item">
                        <img src="/statics/img/user-star-5.png?v=2026102701"><span>铂金</span><span>1300</span><span>2199</span>
                    </div>
                    <div id="userlevel6" class="user-level-preview-item">
                        <img src="/statics/img/user-star-6.png?v=2026102701"><span>水晶</span><span>2200</span><span>3499</span>
                    </div>
                    <div id="userlevel7" class="user-level-preview-item">
                        <img src="/statics/img/user-star-7.png?v=2026102701"><span>钻石</span><span>3500</span><span>5299</span>
                    </div>
                    <div id="userlevel8" class="user-level-preview-item">
                        <img src="/statics/img/user-star-8.png?v=2026102701"><span>星辰</span><span>5300</span><span>7699</span>
                    </div>
                    <div id="userlevel9" class="user-level-preview-item">
                        <img src="/statics/img/user-star-9.png?v=2026102701"><span>星云</span><span>7700</span><span>10799</span>
                    </div>
                    <div id="userlevel10" class="user-level-preview-item">
                        <img src="/statics/img/user-star-10.png?v=2026102701"><span>星河</span><span>10800</span><span>MAX</span>
=======
                        <img src="/statics/img/user-star-1.png?v=2026101403"><span>萌新</span><span>0</span><span>75</span>
                    </div>
                    <div id="userlevel2" class="user-level-preview-item">
                        <img src="/statics/img/user-star-2.png?v=2026101403"><span>青铜</span><span>76</span><span>299</span>
                    </div>
                    <div id="userlevel3" class="user-level-preview-item">
                        <img src="/statics/img/user-star-3.png?v=2026101403"><span>白银</span><span>300</span><span>699</span>
                    </div>
                    <div id="userlevel4" class="user-level-preview-item">
                        <img src="/statics/img/user-star-4.png?v=2026101403"><span>黄金</span><span>700</span><span>1299</span>
                    </div>
                    <div id="userlevel5" class="user-level-preview-item">
                        <img src="/statics/img/user-star-5.png?v=2026101403"><span>铂金</span><span>1300</span><span>2199</span>
                    </div>
                    <div id="userlevel6" class="user-level-preview-item">
                        <img src="/statics/img/user-star-6.png?v=2026101403"><span>水晶</span><span>2200</span><span>3499</span>
                    </div>
                    <div id="userlevel7" class="user-level-preview-item">
                        <img src="/statics/img/user-star-7.png?v=2026101403"><span>钻石</span><span>3500</span><span>5299</span>
                    </div>
                    <div id="userlevel8" class="user-level-preview-item">
                        <img src="/statics/img/user-star-8.png?v=2026101403"><span>星辰</span><span>5300</span><span>7699</span>
                    </div>
                    <div id="userlevel9" class="user-level-preview-item">
                        <img src="/statics/img/user-star-9.png?v=2026101403"><span>星云</span><span>7700</span><span>10799</span>
                    </div>
                    <div id="userlevel10" class="user-level-preview-item">
                        <img src="/statics/img/user-star-10.png?v=2026101403"><span>星河</span><span>10800</span><span>MAX</span>
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
                    </div>
                </div>
            </div>
            <div id="user-note" class="user-note">
                <div class="user-note-nav">
                    <span class="user-note-tit">私人备忘</span>
                    <span class="rel fr mr20">
                        <i class="add fl iconfont icon-zengjia f12 poi" title="新增"></i>
                        <i class="more fl ml20 iconfont icon-shenglvehao f12 poi" title="更多"></i>
                        <i class="refresh fl ml20 iconfont icon-shuaxin f12 poi" title="刷新"></i>
                        <span class="more fl ml20 mt10 poi">
                            <span class="fl pr10 h24 lh24 dark">提醒</span>
                            <span id="noteNotice" class="fl w30 h20 lh20 tc white bg-silver b-r-3">0</span>
                        </span>
                    </span>
                    <div id="note-refresh-msg" class="link-msg dn"></div>
                </div>
                <div class="user-note-con"></div>
            </div>
        </div>
    </div>
    <div class="staff-side"></div>
    <div class="festival dn">
        <div class="festival-tit"></div>
        <div class="festival-close">
            <div class="white bg-red b-r-3"><i class="iconfont icon-guanbi f12 mr5"></i><span>关闭节日遮罩</span></div>
        </div>
    </div>
    <div class="levelup dn">
        <div class="levelup-con border-image-clip-path">
            <div class="levelup-img"></div>
            <div class="levelup-tit"></div>
            <div class="levelup-close"><i class="iconfont icon-guanbi f14" title="关闭"></i></div>
        </div>
    </div>
<<<<<<< HEAD
    <script type="text/javascript" src="/statics/js/plugins/echarts/echarts.min.js?v=2026102701"></script>
    <script type="text/javascript" src="/statics/js/plugins/calendar/calendar.js?v=2026102701"></script>
    <script type="text/javascript" src="/statics/js/dist/main.js?v=2026102701"></script>
=======
    <script type="text/javascript" src="/statics/js/plugins/echarts/echarts.min.js?v=2026101403"></script>
    <script type="text/javascript" src="/statics/js/plugins/calendar/calendar.js?v=2026101403"></script>
    <script type="text/javascript" src="/statics/js/dist/main.js?v=2026101403"></script>
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
</body>

</html>
