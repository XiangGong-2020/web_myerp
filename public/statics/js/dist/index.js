
var CONFIG = {DEFAULT_PAGE: true};
var defaultPage = Public.getDefaultPage();
var SYSTEM = {unlock: !1};
var cacheList = {};
var tab = null;
var timer_alert;
var timer_timeout;
var timer_urgent;
var timer_urgent_pause;
var urgentPause = !1;
var urgentResult = !0;
var defaultList = {};
var list = {};
var menu = {
    init: function(a, b) {
        var c = {
            callback: {}
        };
        this.obj = a;
        this.opts = $.extend(!0, {}, c, b);
        this.sublist = this.opts.sublist;
        this.sublist || this._getMenuData();
        this._initDom();
    },
    _display: function(a, b) {
        for (var c = a.length - 1; c >= 0; c--) {
            this.sublist[a[c]] && (this.sublist[a[c]].disable = !b);
        }
        return this;
    },
    _show: function(a) {
        return this._display(a, !0);
    },
    _hide: function(a) {
        return this._display(a, !1);
    },
    _getMenuData: function() {
        this.sublist = list;
    },
    _getDom: function() {
        this.objCopy = this.obj.clone(!0);
        this.container = this.obj.closest('div');
    },
    _setDom: function() {
        this.obj.remove();
        this.container.append(this.objCopy);
    },
    _initDom: function() {
        if (this.sublist && this.obj) {
            this.obj.find('li:not(.item)').remove();
            this._getDom();
            var a = this.sublist, b = {};
            b.target = {};
            for (var c in a) {
                if (!a[c].disable) {
                    var d = a[c];
                    var e = b.target[d.target];
                    var f = d.id ? 'id=' + d.id : '';
                    var g = d.id ? '' : 'rel=pageTab';
                    var h = '<li class="' + d.target.toString().split('-')[0] + '-' + c + '"><a ' + f + ' tabTxt="' + (d.icon ? d.icon : '') + d.name + '" tabid="' + d.target.toString().split('-')[0] + '-' + c + '" ' + g + ' href="' + d.href + '" data-right="' + d.dataRight + '">' + (d.icon ? d.icon : '') + d.name + '<span class="point"></span></a></li>';
                    e ? e.append(h) : (
                        (b.target[d.target] = this.objCopy.find('#' + d.target)),
                        b.target[d.target] && b.target[d.target].append(h)
                    );
                }
            }
            this.objCopy.find('li.item').each(function() {
                var a = $(this);
                a.find('li').length || a.addClass('dn');
                a.find('.nav-item').each(function() {
                    var a = $(this);
                    a.find('li').length || (a.hasClass('last') && a.prev().addClass('last'), a.addClass('dn'));
                });
            });
            this._setDom();
        }
    }
};
var THISPAGE = {
    init: function() {
        THISPAGE.initDom();
        THISPAGE.initNav();
        THISPAGE.initTab();
        THISPAGE.initUser();
        THISPAGE.initEvent();
    },
    initDom: function() {
        Public.ajaxGet('/Base/Home/indexData', {
            async: false // 异步:否
        }, function(res) {
            if (res.status === 200) {
                var data = res.data;

                SYSTEM.clientIp = data.clientIp;
                SYSTEM.locked = data.locked;

                SYSTEM.uId = data.uId;
                SYSTEM.uName = data.uName;
                SYSTEM.user = data.user;
                SYSTEM.userLevel = data.userLevel;
                SYSTEM.enableUserIds = data.enableUserIds;

                SYSTEM.beginDate = data.system['beginDate'];
                SYSTEM.endDate = data.system['endDate'];
                SYSTEM.currency = data.system['currency'];
                SYSTEM.valMethods = data.system['valMethods'];
                SYSTEM.requiredCheckStore = Boolean(Number(data.system['requiredCheckStore']));
                SYSTEM.qtyPlaces = data.system['qtyPlaces'];
                SYSTEM.pricePlaces = data.system['pricePlaces'];
                SYSTEM.amountPlaces = data.system['amountPlaces'];
                SYSTEM.fillClockinTimes = data.system['fillClockinTimes'];
                SYSTEM.bonusFullAttendance = data.system['bonusFullAttendance'];
                SYSTEM.bonusDuty = data.system['bonusDuty'];
                SYSTEM.bonusBid1 = data.system['bonusBid1'];
                SYSTEM.bonusBid2 = data.system['bonusBid2'];
                SYSTEM.bonusBidLeap = data.system['bonusBidLeap'];
                SYSTEM.priceWH1 = data.system['priceWH1'];
                SYSTEM.priceWH1Department = data.system['priceWH1Department'];
                SYSTEM.priceWH2 = data.system['priceWH2'];
                SYSTEM.priceWH2Department = data.system['priceWH2Department'];
                SYSTEM.priceWR = data.system['priceWR'];
                SYSTEM.deductionSocialSecurity = data.system['deductionSocialSecurity'];

                SYSTEM.expPar = data.expPar;
                SYSTEM.rights = data.rights;
                defaultList = {
                    // 主页
                    task: {
                        icon: "<i class='iconfont icon-renwu f14 mr5'></i>",
                        name: '任务清单',
                        href: '/Uri/task_list',
                        dataRight: 'TASK_QUERY',
                        target: 'index-offen'
                    },
                    plan: {
                        icon: "<i class='iconfont icon-jihua f14 mr5'></i>",
                        name: '工作目标',
                        href: '/Uri/plan_list',
                        dataRight: 'PLAN_QUERY',
                        target: 'index-offen'
                    },
                    duty: {
                        icon: "<i class='iconfont icon-jiaojie f14 mr5'></i>",
                        name: '工作转交',
                        href: '/Uri/duty_list',
                        dataRight: 'DUTY_QUERY',
                        target: 'index-offen'
                    },
                    managePlan: {
                        icon: "<i class='iconfont icon-jihua f14 mr5'></i>",
                        name: '管理计划',
                        href: '/Uri/manage_plan_list',
                        dataRight: 'MANAGE_PLAN_QUERY',
                        target: 'index-offen'
                    },
                    note: {
                        icon: "<i class='iconfont icon-beiwang f14 mr5'></i>",
                        name: '私人备忘',
                        href: '/Uri/note_list',
                        dataRight: 'SYS_DEFAULT',
                        target: 'index-offen'
                    },
                    worksite: {
                        icon: "<i class='iconfont icon-wangzhan f14 mr5'></i>",
                        name: '业务网站',
                        href: '/Uri/work_site_list',
                        dataRight: 'WORKSITE_QUERY',
                        target: 'index-offen'
                    },
                    netdisk: {
                        icon: "<i class='iconfont icon-wangpan f14 mr5'></i>",
                        name: '企业网盘',
                        href: '/Uri/netdisk',
                        dataRight: 'SYS_DEFAULT',
                        target: 'index-offen'
                    },
                    cameraLive: {
                        icon: "<i class='iconfont icon-shexiangtou f14 mr5'></i>",
                        name: '监控直播',
                        href: '/Uri/camera_live',
                        dataRight: 'CAMERA_QUERY',
                        target: 'index-offen'
                    },
                    cameraRec: {
                        icon: "<i class='iconfont icon-shexiangtou f14 mr5'></i>",
                        name: '监控回放',
                        href: '/Uri/camera_rec',
                        dataRight: 'CAMERA_QUERY',
                        target: 'index-offen'
                    },
                    knowledges: {
                        icon: "<i class='iconfont icon-zhishi f14 mr5'></i>",
                        name: '业务知识',
                        href: '/Uri/news_list?type=1',
                        dataRight: 'NEWS_QUERY',
                        target: 'index-news'
                    },
                    videos: {
                        icon: "<i class='iconfont icon-shipin f14 mr5'></i>",
                        name: '学习视频',
                        href: '/Uri/news_list?type=2',
                        dataRight: 'NEWS_QUERY',
                        target: 'index-news'
                    },
                    videotype: {
                        icon: "<i class='iconfont icon-leibie f14 mr5'></i>",
                        name: '视频类别',
                        href: '/Uri/category_list?typeNumber=videotype',
                        dataRight: 'VIDEOTYPE_QUERY',
                        target: 'index-news'
                    },
                    files: {
                        icon: "<i class='iconfont icon-wenjian f14 mr5'></i>",
                        name: '通知公告',
                        href: '/Uri/news_list?type=3',
                        dataRight: 'NEWS_QUERY',
                        target: 'index-news'
                    },
                    rules: {
                        icon: "<i class='iconfont icon-guizhang f14 mr5'></i>",
                        name: '制度文件',
                        href: '/Uri/news_list?type=4',
                        dataRight: 'NEWS_QUERY',
                        target: 'index-news'
                    },

                    // 产品
                    goods: {
                        icon: "<i class='iconfont icon-chanpin f14 mr5'></i>",
                        name: '产品信息',
                        href: '/Uri/goods_list',
                        dataRight: 'GOODS_QUERY',
                        target: 'product-base'
                    },
                    goodstype: {
                        icon: "<i class='iconfont icon-leibie f14 mr5'></i>",
                        name: '产品类别',
                        href: '/Uri/category_list?typeNumber=goodstype',
                        dataRight: 'GOODSTYPE_QUERY',
                        target: 'product-base'
                    },
                    brand: {
                        icon: "<i class='iconfont icon-pinpai f14 mr5'></i>",
                        name: '产品品牌',
                        href: '/Uri/category_list?typeNumber=brandtype',
                        dataRight: 'BRANDTYPE_QUERY',
                        target: 'product-base'
                    },
                    unit: {
                        icon: "<i class='iconfont icon-jiliang f14 mr5'></i>",
                        name: '计量单位',
                        href: '/Uri/category_list?typeNumber=unittype',
                        dataRight: 'UNITTYPE_QUERY',
                        target: 'product-base'
                    },
                    goodsFileOne: {
                        icon: "<i class='iconfont icon-tuwen f14 mr5'></i>",
                        name: '中文图纸',
                        href: '/Uri/goods_file_list?type=goodsFileOne',
                        dataRight: 'GOODSFILEONE_QUERY',
                        target: 'product-file'
                    },
                    goodsFileTwo: {
                        icon: "<i class='iconfont icon-shuomingshu f14 mr5'></i>",
                        name: '中文教程',
                        href: '/Uri/goods_file_list?type=goodsFileTwo',
                        dataRight: 'GOODSFILETWO_QUERY',
                        target: 'product-file'
                    },
                    goodsFileOne_EN: {
                        icon: "<i class='iconfont icon-tuwen f14 mr5'></i>",
                        name: '外文图纸',
                        href: '/Uri/goods_file_list?type=goodsFileOne_EN',
                        dataRight: 'GOODSFILEONE_QUERY',
                        target: 'product-file'
                    },
                    goodsFileTwo_EN: {
                        icon: "<i class='iconfont icon-shuomingshu f14 mr5'></i>",
                        name: '外文教程',
                        href: '/Uri/goods_file_list?type=goodsFileTwo_EN',
                        dataRight: 'GOODSFILETWO_QUERY',
                        target: 'product-file'
                    },
                    salesOrderSpiderErrGoods: {
                        icon: "<i class='iconfont icon-tongbu f14 mr5'></i>",
                        name: '同步异常',
                        href: '/Uri/salesOrder_spiderErrGoods',
                        dataRight: 'SA_SPIDER_ERR_QUERY',
                        target: 'product-platform'
                    },
                    salesOrderSpiderErrPrice: {
                        icon: "<i class='iconfont icon-tongbu f14 mr5'></i>",
                        name: '售价异常',
                        href: '/Uri/salesOrder_spiderErrPrice',
                        dataRight: 'SA_SPIDER_ERR_QUERY',
                        target: 'product-platform'
                    },
                    competitiveMonitor: {
                        icon: "<i class='iconfont icon-jiankong f14 mr5'></i>",
                        name: '竞品监控',
                        href: '/Uri/competitive_monitor_list',
                        dataRight: 'COMPETITIVE_MONITOR_QUERY',
                        target: 'product-competitive'
                    },

                    // 研发
                    goodsDevReq: {
                        icon: "<i class='iconfont icon-shenqing f14 mr5'></i>",
                        name: '定制申请',
                        href: '/Uri/goods_dev_req_list',
                        dataRight: 'GOODSDEVREQ_QUERY',
                        target: 'develop-develop'
                    },
                    goodsTask: {
                        icon: "<i class='iconfont icon-chanpinkaifa f14 mr5'></i>",
                        name: '产品开发',
                        href: '/Uri/goods_task_list',
                        dataRight: 'GOODSTASK_QUERY',
                        target: 'develop-develop'
                    },
                    goodsFileThree: {
                        icon: "<i class='iconfont icon-c f14 mr5'></i>",
                        name: '程序文件',
                        href: '/Uri/goods_file_list?type=goodsFileThree',
                        dataRight: 'GOODSFILETHREE_QUERY',
                        target: 'develop-file'
                    },
                    goodsFileFour: {
                        icon: "<i class='iconfont icon-pcb f14 mr5'></i>",
                        name: 'PCB文件',
                        href: '/Uri/goods_file_list?type=goodsFileFour',
                        dataRight: 'GOODSFILEFOUR_QUERY',
                        target: 'develop-file'
                    },
                    goodsFileFive: {
                        icon: "<i class='iconfont icon-jiguangqiegeji f14 mr5'></i>",
                        name: '激光文件',
                        href: '/Uri/goods_file_list?type=goodsFileFive',
                        dataRight: 'GOODSFILEFIVE_QUERY',
                        target: 'develop-file'
                    },
                    goodsFileSix: {
                        icon: "<i class='iconfont icon-sheji f14 mr5'></i>",
                        name: '设计文件',
                        href: '/Uri/goods_file_list?type=goodsFileSix',
                        dataRight: 'GOODSFILESIX_QUERY',
                        target: 'develop-file'
                    },
                    goodsFileReq: {
                        icon: "<i class='iconfont icon-shenqing f14 mr5'></i>",
                        name: '下载申请',
                        href: '/Uri/goods_file_req_list',
                        dataRight: 'GOODSFILEREQ_QUERY',
                        target: 'develop-file'
                    },

                    // 投标
                    bidInfo: {
                        icon: "<i class='iconfont icon-xinxi f14 mr5'></i>",
                        name: '招标信息',
                        href: '/Uri/bid_info_list',
                        dataRight: 'BIDINFO_QUERY',
                        target: 'bid-info'
                    },
                    bidSiteAndKeyword: {
                        icon: "<i class='iconfont icon-wangzhan f14 mr5'></i>",
                        name: '招标网站',
                        href: '/Uri/bid_site_and_keyword',
                        dataRight: 'BIDSITE_QUERY',
                        target: 'bid-from'
                    },
                    bidQuote: {
                        icon: "<i class='iconfont icon-danju f14 mr5'></i>",
                        name: '预算报价',
                        href: '/Uri/bid_quote_list',
                        dataRight: 'BIDQUOTE_QUERY',
                        target: 'bid-quote'
                    },
                    bidQuoteReference: {
                        icon: "<i class='iconfont icon-cankao f14 mr5'></i>",
                        name: '报价参考',
                        href: '/Uri/bid_quote_reference',
                        dataRight: 'BIDINFO_QUERY',
                        target: 'bid-quote'
                    },
                    bidProgram: {
                        icon: "<i class='iconfont icon-zhaotoubiao f14 mr5'></i>",
                        name: '投标项目',
                        href: '/Uri/bid_program_list',
                        dataRight: 'BIDPROGRAM_QUERY',
                        target: 'bid-program'
                    },
                    bidService: {
                        icon: "<i class='iconfont icon-pao f14 mr5'></i>",
                        name: '代办跑腿',
                        href: '/Uri/bid_service_list',
                        dataRight: 'BIDSERVICE_QUERY',
                        target: 'bid-service'
                    },
                    bidderRank: {
                        icon: "<i class='iconfont icon-paihang f14 mr5'></i>",
                        name: '投标排名',
                        href: '/Uri/bidder_rank',
                        dataRight: 'BIDDER_RANK',
                        target: 'bid-report'
                    },
                    bidProgramAnalyse: {
                        icon: "<i class='iconfont icon-fenxi f14 mr5'></i>",
                        name: '中标分析',
                        href: '/Uri/bid_program_analyse',
                        dataRight: 'BIDPROGRAM_ANALYSE',
                        target: 'bid-report'
                    },

                    // 销售
                    customer: {
                        icon: "<i class='iconfont icon-kehu f14 mr5'></i>",
                        name: '客户信息',
                        href: '/Uri/customer_list',
                        dataRight: 'CUSTOMER_QUERY',
                        target: 'sales-base'
                    },
                    customertype: {
                        icon: "<i class='iconfont icon-leibie f14 mr5'></i>",
                        name: '客户类别',
                        href: '/Uri/category_list?typeNumber=customertype',
                        dataRight: 'CUSTOMERTYPE_QUERY',
                        target: 'sales-base'
                    },
                    billfrom: {
                        icon: "<i class='iconfont icon-qudao f14 mr5'></i>",
                        name: '销售渠道',
                        href: '/Uri/billfrom_list',
                        dataRight: 'BILLFROM_QUERY',
                        target: 'sales-base'
                    },
                    service: {
                        icon: "<i class='iconfont icon-kefu f14 mr5'></i>",
                        name: '客户之声',
                        href: '/Uri/service_list',
                        dataRight: 'SERVICE_QUERY',
                        target: 'sales-service'
                    },
                    servicetype: {
                        icon: "<i class='iconfont icon-leibie f14 mr5'></i>",
                        name: '反馈类别',
                        href: '/Uri/category_list?typeNumber=servicetype',
                        dataRight: 'SERVICETYPE_QUERY',
                        target: 'sales-service'
                    },
                    discountReq: {
                        icon: "<i class='iconfont icon-shenqing f14 mr5'></i>",
                        name: '优惠申请',
                        href: '/Uri/discountReq_list',
                        dataRight: 'SERVICE_QUERY',
                        target: 'sales-service'
                    },
                    quoteReq: {
                        icon: "<i class='iconfont icon-shenqing f14 mr5'></i>",
                        name: '报价审批',
                        href: '/Uri/quoteReq_list',
                        dataRight: 'SERVICE_QUERY',
                        target: 'sales-service'
                    },
                    potentialCustomer: {
                        icon: "<i class='iconfont icon-kehu f14 mr5'></i>",
                        name: '潜客登记',
                        href: '/Uri/potentialCustomer_list',
                        dataRight: 'SERVICE_QUERY',
                        target: 'sales-service'
                    },
                    reviewHold: {
                        icon: "<i class='iconfont icon-pingjia f14 mr5'></i>",
                        name: '差评删改',
                        href: '/Uri/reviewHold_list',
                        dataRight: 'SERVICE_QUERY',
                        target: 'sales-service'
                    },
                    salerDutyChoose: {
                        icon: "<i class='iconfont icon-pingjia f14 mr5'></i>",
                        name: '值班选择',
                        href: '/Uri/saler_duty_choose',
                        dataRight: 'SERVICE_QUERY',
                        target: 'sales-service'
                    },
                    salerDutyNote: {
                        icon: "<i class='iconfont icon-pingjia f14 mr5'></i>",
                        name: '值班记录',
                        href: '/Uri/saler_duty_note',
                        dataRight: 'SYS_ADVANCE',
                        target: 'sales-service'
                    },
                    salesTarget: {
                        icon: "<i class='iconfont icon-mubiao f14 mr5'></i>",
                        name: '销售目标',
                        href: '/Uri/sales_target',
                        dataRight: 'SYS_ADVANCE',
                        target: 'sales-target'
                    },
                    salesOrder: {
                        icon: "<i class='iconfont icon-danju f14 mr5'></i>",
                        name: '销售订单',
                        href: '/Uri/salesOrder_list',
                        dataRight: 'SO_QUERY',
                        target: 'sales-bill'
                    },
                    salesContract: {
                        icon: "<i class='iconfont icon-danju f14 mr5'></i>",
                        name: '特殊销售',
                        href: '/Uri/salesContract_list',
                        dataRight: 'SC_QUERY',
                        target: 'sales-bill'
                    },
                    onlineshopMp: {
                        icon: "<i class='iconfont icon-danju f14 mr5'></i>",
                        name: '网店月绩',
                        href: '/Uri/onlineshopMp_list',
                        dataRight: 'ONLINESHOPMP_QUERY',
                        target: 'sales-sum'
                    },
                    salesDetail: {
                        icon: "<i class='iconfont icon-liebiao f14 mr5'></i>",
                        name: '销售明细',
                        href: '/Uri/sales_detail',
                        dataRight: 'SALES_DETAIL',
                        target: 'sales-report'
                    },
                    salerRank: {
                        icon: "<i class='iconfont icon-paihang f14 mr5'></i>",
                        name: '销售排名',
                        href: '/Uri/saler_rank',
                        dataRight: 'SALER_RANK',
                        target: 'sales-report'
                    },

                    // 采购
                    supplier: {
                        icon: "<i class='iconfont icon-gongyingshang f14 mr5'></i>",
                        name: '商家信息',
                        href: '/Uri/supplier_list',
                        dataRight: 'SUPPLIER_QUERY',
                        target: 'purchase-base'
                    },
                    suppliertype: {
                        icon: "<i class='iconfont icon-leibie f14 mr5'></i>",
                        name: '商家类别',
                        href: '/Uri/category_list?typeNumber=suppliertype',
                        dataRight: 'SUPPLIERTYPE_QUERY',
                        target: 'purchase-base'
                    },
                    purchaseReq: {
                        icon: "<i class='iconfont icon-shenqing f14 mr5'></i>",
                        name: '采购申请',
                        href: '/Uri/purchaseReq_list',
                        dataRight: 'PURREQ_QUERY',
                        target: 'purchase-req'
                    },
                    purchaseContractReq: {
                        icon: "<i class='iconfont icon-shenqing f14 mr5'></i>",
                        name: '特采申请',
                        href: '/Uri/purchaseContractReq_list',
                        dataRight: 'PURREQ_QUERY',
                        target: 'purchase-req'
                    },
                    purchaseOrder: {
                        icon: "<i class='iconfont icon-danju f14 mr5'></i>",
                        name: '采购订单',
                        href: '/Uri/purchaseOrder_list',
                        dataRight: 'PO_QUERY',
                        target: 'purchase-bill'
                    },
                    purchaseContract: {
                        icon: "<i class='iconfont icon-danju f14 mr5'></i>",
                        name: '特殊采购',
                        href: '/Uri/purchaseContract_list',
                        dataRight: 'PC_QUERY',
                        target: 'purchase-bill'
                    },
                    purchaseContractFill: {
                        icon: "<i class='iconfont icon-danju f14 mr5'></i>",
                        name: '特采补买',
                        href: '/Uri/purchaseContractFill_list',
                        dataRight: 'PC_UPDATE',
                        target: 'purchase-bill'
                    },
                    purchaseContractLost: {
                        icon: "<i class='iconfont icon-danju f14 mr5'></i>",
                        name: '特采遗漏',
                        href: '/Uri/purchaseContractLost_list',
                        dataRight: 'PC_UPDATE',
                        target: 'purchase-alert'
                    },
                    purchaseSuggest: {
                        icon: "<i class='iconfont icon-jianyi f14 mr5'></i>",
                        name: '采购建议',
                        href: '/Uri/purchase_suggest_list',
                        dataRight: 'PC_UPDATE',
                        target: 'purchase-alert'
                    },
                    purchaseContractModifyPurAmount: {
                        icon: "<i class='iconfont icon-danju f14 mr5'></i>",
                        name: '改价通知',
                        href: '/Uri/purchaseContractModifyPurAmount_list',
                        dataRight: 'PC_UPDATE',
                        target: 'purchase-alert'
                    },
                    purchaseContractPriceAnormaly: {
                        icon: "<i class='iconfont icon-danju f14 mr5'></i>",
                        name: '价差异常',
                        href: '/Uri/purchaseContractPriceAnormaly_list',
                        dataRight: 'SYS_ADMIN',
                        target: 'purchase-alert'
                    },
                    purchaseQC: {
                        icon: "<i class='iconfont icon-zhijian f14 mr5'></i>",
                        name: '采购质检',
                        href: '/Uri/purchase_qc',
                        dataRight: 'PURQC_QUERY',
                        target: 'purchase-qc'
                    },
                    purchaseQCBack: {
                        icon: "<i class='iconfont icon-zhijian f14 mr5'></i>",
                        name: '质检打回',
                        href: '/Uri/purchase_qc_back',
                        dataRight: 'PURQCBACK_QUERY',
                        target: 'purchase-qc'
                    },
                    purchaseDetail: {
                        icon: "<i class='iconfont icon-liebiao f14 mr5'></i>",
                        name: '采购明细',
                        href: '/Uri/purchase_detail',
                        dataRight: 'PURCHASE_DETAIL',
                        target: 'purchase-report'
                    },
                    purchaseCostSearch: {
                        icon: "<i class='iconfont icon-chengben f14 mr5'></i>",
                        name: '采购成本',
                        href: '/Uri/purchase_costSearch',
                        dataRight: 'PURCHASE_COSTSEARCH',
                        target: 'purchase-report'
                    },
                    purchaserRank: {
                        icon: "<i class='iconfont icon-paihang f14 mr5'></i>",
                        name: '采购排名',
                        href: '/Uri/purchaser_rank',
                        dataRight: 'PURCHASER_RANK',
                        target: 'purchase-report'
                    },
                    purchaseInvoiceBack: {
                        icon: "<i class='iconfont icon-fapiao f14 mr5'></i>",
                        name: '回票排名',
                        href: '/Uri/purchase_invoice_back',
                        dataRight: 'PURCHASER_RANK',
                        target: 'purchase-report'
                    },
                    purchaseContractHis: {
                        icon: "<i class='iconfont icon-rizhi f14 mr5'></i>",
                        name: '特采历史',
                        href: '/Uri/purchaseContract_his',
                        dataRight: 'PC_UPDATE',
                        target: 'purchase-report'
                    },

                    // 生产
                    produceGroup: {
                        icon: "<i class='iconfont icon-yonghuzu f14 mr5'></i>",
                        name: '生产分组',
                        href: '/Uri/produceGroup_list',
                        dataRight: 'SYS_DEFAULT',
                        target: 'produce-base'
                    },
                    produceKittingReq: {
                        icon: "<i class='iconfont icon-shenqing f14 mr5'></i>",
                        name: '配货申请',
                        href: '/Uri/produceKittingReq_list',
                        dataRight: 'PROREQ_QUERY',
                        target: 'produce-kitting'
                    },
                    produceKittingTask: {
                        icon: "<i class='iconfont icon-renwu f14 mr5'></i>",
                        name: '配货任务',
                        href: '/Uri/produceKittingTask_list',
                        dataRight: 'PROTASK_QUERY',
                        target: 'produce-kitting'
                    },
                    produceKittingTaskGet: {
                        icon: "<i class='iconfont icon-lingqu f14 mr5'></i>",
                        name: '配货领取',
                        href: '/Uri/produceKittingTask_get_list',
                        dataRight: 'PROTASK_QUERY',
                        target: 'produce-kitting'
                    },
                    produceKittingTaskUp: {
                        icon: "<i class='iconfont icon-jiaofu f14 mr5'></i>",
                        name: '配货交付',
                        href: '/Uri/produceKittingTask_up_list',
                        dataRight: 'PROTASK_QUERY',
                        target: 'produce-kitting'
                    },
                    produceProcessingProject: {
                        icon: "<i class='iconfont icon-shengchan f14 mr5'></i>",
                        name: '加工项目',
                        href: '/Uri/produceProcessingProject_list',
                        dataRight: 'PROPROJECT_QUERY',
                        target: 'produce-processing'
                    },
                    produceProcessingReq: {
                        icon: "<i class='iconfont icon-shenqing f14 mr5'></i>",
                        name: '加工申请',
                        href: '/Uri/produceProcessingReq_list',
                        dataRight: 'PROREQ_QUERY',
                        target: 'produce-processing'
                    },
                    produceProcessingTask: {
                        icon: "<i class='iconfont icon-renwu f14 mr5'></i>",
                        name: '加工任务',
                        href: '/Uri/produceProcessingTask_list',
                        dataRight: 'PROTASK_QUERY',
                        target: 'produce-processing'
                    },
                    produceProcessingTaskGet: {
                        icon: "<i class='iconfont icon-lingqu f14 mr5'></i>",
                        name: '加工领取',
                        href: '/Uri/produceProcessingTask_get_list',
                        dataRight: 'PROTASK_QUERY',
                        target: 'produce-processing'
                    },
                    produceProcessingTaskUp: {
                        icon: "<i class='iconfont icon-jiaofu f14 mr5'></i>",
                        name: '加工交付',
                        href: '/Uri/produceProcessingTask_up_list',
                        dataRight: 'PROTASK_QUERY',
                        target: 'produce-processing'
                    },

                    // 仓储
                    storage: {
                        icon: "<i class='iconfont icon-cangku f14 mr5'></i>",
                        name: '仓库信息',
                        href: '/Uri/storage_list',
                        dataRight: 'STORAGE_QUERY',
                        target: 'inventory-base'
                    },
                    sales: {
                        icon: "<i class='iconfont icon-danju f14 mr5'></i>",
                        name: '销售发货',
                        href: '/Uri/sales_list?transType=102201',
                        dataRight: 'SA_QUERY',
                        target: 'inventory-bill'
                    },
                    salesBack: {
                        icon: "<i class='iconfont icon-danju f14 mr5'></i>",
                        name: '销售退货',
                        href: '/Uri/sales_list?transType=102202',
                        dataRight: 'SA_QUERY',
                        target: 'inventory-bill'
                    },
                    purchase: {
                        icon: "<i class='iconfont icon-danju f14 mr5'></i>",
                        name: '采购收货',
                        href: '/Uri/purchase_list?transType=102101',
                        dataRight: 'PU_QUERY',
                        target: 'inventory-bill'
                    },
                    purchaseBack: {
                        icon: "<i class='iconfont icon-danju f14 mr5'></i>",
                        name: '采购退货',
                        href: '/Uri/purchase_list?transType=102102',
                        dataRight: 'PU_QUERY',
                        target: 'inventory-bill'
                    },
                    otherOutbound: {
                        icon: "<i class='iconfont icon-danju f14 mr5'></i>",
                        name: '其他出库',
                        href: '/Uri/inout_list_out',
                        dataRight: 'OO_QUERY',
                        target: 'inventory-bill'
                    },
                    otherWarehouse: {
                        icon: "<i class='iconfont icon-danju f14 mr5'></i>",
                        name: '其他入库',
                        href: '/Uri/inout_list_in',
                        dataRight: 'OI_QUERY',
                        target: 'inventory-bill'
                    },
                    transfers: {
                        icon: "<i class='iconfont icon-danju f14 mr5'></i>",
                        name: '库存调拨',
                        href: '/Uri/inout_list_tf',
                        dataRight: 'TF_QUERY',
                        target: 'inventory-bill'
                    },
                    costAdjustment: {
                        icon: "<i class='iconfont icon-danju f14 mr5'></i>",
                        name: '成本调整',
                        href: '/Uri/inout_list_ca',
                        dataRight: 'CA_QUERY',
                        target: 'inventory-bill'
                    },
                    invSearch: {
                        icon: "<i class='iconfont icon-kucunchaxun f14 mr5'></i>",
                        name: '库存查询',
                        href: '/Uri/inventory_search',
                        dataRight: 'INV_SEARCH',
                        target: 'inventory-other'
                    },
                    invHistory: {
                        icon: "<i class='iconfont icon-kucunliushui f14 mr5'></i>",
                        name: '库存流水',
                        href: '/Uri/inventory_history',
                        dataRight: 'INV_HISTORY',
                        target: 'inventory-other'
                    },
                    invWarning: {
                        icon: "<i class='iconfont icon-tixing f14 mr5'></i>",
                        name: '库存预警',
                        href: '/Uri/inventory_warning',
                        dataRight: 'INV_WARNING',
                        target: 'inventory-other'
                    },
                    invCheck: {
                        icon: "<i class='iconfont icon-pandian f14 mr5'></i>",
                        name: '库存盘点',
                        href: '/Uri/inventory_check',
                        dataRight: 'INV_CHECK',
                        target: 'inventory-other'
                    },
                    invDull: {
                        icon: "<i class='iconfont icon-shalou f14 mr5'></i>",
                        name: '库存呆滞',
                        href: '/Uri/inventory_dull',
                        dataRight: 'INV_DULL',
                        target: 'inventory-other'
                    },
                    packerRank: {
                        icon: "<i class='iconfont icon-paihang f14 mr5'></i>",
                        name: '打包排名',
                        href: '/Uri/packer_rank',
                        dataRight: 'PACKER_RANK',
                        target: 'inventory-report'
                    },

                    // 物流
                    deliverCompany: {
                        icon: "<i class='iconfont icon-kuaidi f14 mr5'></i>",
                        name: '快递公司',
                        href: '/Uri/deliver_company_list',
                        dataRight: 'DELIVERCOM_QUERY',
                        target: 'deliver-base'
                    },
                    areas: {
                        icon: "<i class='iconfont icon-ditu f14 mr5'></i>",
                        name: '全国地区',
                        href: '/Uri/areas_list',
                        dataRight: 'AREAS_QUERY',
                        target: 'deliver-base'
                    },
                    deliver: {
                        icon: "<i class='iconfont icon-danju f14 mr5'></i>",
                        name: '快递月结',
                        href: '/Uri/deliver_list',
                        dataRight: 'DELIVER_QUERY',
                        target: 'deliver-bill'
                    },
                    deliverSearch: {
                        icon: "<i class='iconfont icon-kuaidichaxun f14 mr5'></i>",
                        name: '快递查询',
                        href: '/Uri/deliver_search',
                        dataRight: 'SYS_DEFAULT',
                        target: 'deliver-tool'
                    },
                    deliverPredict: {
                        icon: "<i class='iconfont icon-kuaidigusuan f14 mr5'></i>",
                        name: '快递估算',
                        href: '/Uri/deliver_predict',
                        dataRight: 'SYS_DEFAULT',
                        target: 'deliver-tool'
                    },
                    printTemplate: {
                        icon: "<i class='iconfont icon-dayin f14 mr5'></i>",
                        name: '打印模板',
                        href: '/Uri/print_template',
                        dataRight: 'PRINTTEMP_QUERY',
                        target: 'deliver-tool'
                    },
                    printLog: {
                        icon: "<i class='iconfont icon-liebiao f14 mr5'></i>",
                        name: '打印记录',
                        href: '/Uri/print_log',
                        dataRight: 'PRINTTEMP_LOG',
                        target: 'deliver-tool'
                    },
                    deliverBag: {
                        icon: "<i class='iconfont icon-baoguo f14 mr5'></i>",
                        name: '发包记录',
                        href: '/Uri/deliver_bag_list',
                        dataRight: 'DELIVERBAG_QUERY',
                        target: 'deliver-bag'
                    },
                    deliverBagDull: {
                        icon: "<i class='iconfont icon-baoguo f14 mr5'></i>",
                        name: '发包滞留',
                        href: '/Uri/deliver_bag_dull',
                        dataRight: 'DELIVERBAG_QUERY',
                        target: 'deliver-bag'
                    },
                    deliverBagOff: {
                        icon: "<i class='iconfont icon-baoguo f14 mr5'></i>",
                        name: '发包拦截',
                        href: '/Uri/deliver_bag_off_list',
                        dataRight: 'DELIVERBAG_QUERY',
                        target: 'deliver-bag'
                    },
                    deliverBagRec: {
                        icon: "<i class='iconfont icon-baoguo f14 mr5'></i>",
                        name: '收包记录',
                        href: '/Uri/deliver_bag_rec_list',
                        dataRight: 'DELIVERBAG_QUERY',
                        target: 'deliver-bag'
                    },
                    deliverAnalyse: {
                        icon: "<i class='iconfont icon-fenxi f14 mr5'></i>",
                        name: '发包分析',
                        href: '/Uri/deliver_analyse',
                        dataRight: 'DELIVER_ANALYSE',
                        target: 'deliver-report'
                    },

                    // 人事
                    applicant: {
                        icon: "<i class='iconfont icon-ziliao f14 mr5'></i>",
                        name: '应聘档案',
                        href: '/Uri/applicant_list',
                        dataRight: 'APPLICANT',
                        target: 'personnel-base'
                    },
                    userStaff: {
                        icon: "<i class='iconfont icon-renshi f14 mr5'></i>",
                        name: '员工档案',
                        href: '/Uri/user_staff_list',
                        dataRight: 'USER_STAFF_QUERY',
                        target: 'personnel-base'
                    },
                    userStaffMap: {
                        icon: "<i class='iconfont icon-yonghuzu f14 mr5'></i>",
                        name: '全员速览',
                        href: '/Uri/user_staff_map',
                        dataRight: 'SYS_DEFAULT',
                        target: 'personnel-base'
                    },
                    userLevel: {
                        icon: "<i class='iconfont icon-dengji f14 mr5'></i>",
                        name: '成长等级',
                        href: '/Uri/user_level_list',
                        dataRight: 'USER_LEVEL_QUERY',
                        target: 'personnel-base'
                    },
                    departmenttype: {
                        icon: "<i class='iconfont icon-bumen f14 mr5'></i>",
                        name: '职能部门',
                        href: '/Uri/category_list?typeNumber=departmenttype',
                        dataRight: 'DEPARTMENTTYPE_QUERY',
                        target: 'personnel-base'
                    },
                    positiontype: {
                        icon: "<i class='iconfont icon-zhiwei f14 mr5'></i>",
                        name: '部门职位',
                        href: '/Uri/category_list?typeNumber=positiontype',
                        dataRight: 'POSITIONTYPE_QUERY',
                        target: 'personnel-base'
                    },
                    evaluation: {
                        icon: "<i class='iconfont icon-kaoping f14 mr5'></i>",
                        name: '考评项目',
                        href: '/Uri/evaluation_list',
                        dataRight: 'EVALUATION_QUERY',
                        target: 'personnel-base'
                    },
                    attendancePlan: {
                        icon: "<i class='iconfont icon-guihua f14 mr5'></i>",
                        name: '出勤规划',
                        href: '/Uri/attendance_plan_list',
                        dataRight: 'SYS_ADMIN',
                        target: 'personnel-base'
                    },
                    clockin: {
                        icon: "<i class='iconfont icon-kaoqin f14 mr5'></i>",
                        name: '考勤打卡',
                        href: '/Uri/clockin_list',
                        dataRight: 'CLOCKIN_QUERY',
                        target: 'personnel-ofen'
                    },
                    workFill: {
                        icon: "<i class='iconfont icon-buban f14 mr5'></i>",
                        name: '补班申报',
                        href: '/Uri/work_fill_list?type=0',
                        dataRight: 'WORKOVERTIME_QUERY',
                        target: 'personnel-ofen'
                    },
                    workOvertime: {
                        icon: "<i class='iconfont icon-jiaban f14 mr5'></i>",
                        name: '加班申报',
                        href: '/Uri/work_overtime_list?type=1',
                        dataRight: 'WORKOVERTIME_QUERY',
                        target: 'personnel-ofen'
                    },
                    workHour: {
                        icon: "<i class='iconfont icon-jishi f14 mr5'></i>",
                        name: '计时申报',
                        href: '/Uri/work_hour_list?type=2',
                        dataRight: 'WORKHOUR_QUERY',
                        target: 'personnel-ofen'
                    },
                    workRecord: {
                        icon: "<i class='iconfont icon-jilu f14 mr5'></i>",
                        name: '工作记录',
                        href: '/Uri/work_record_list',
                        dataRight: 'WORKRECORD_QUERY',
                        target: 'personnel-ofen'
                    },
                    attendance: {
                        icon: "<i class='iconfont icon-danju f14 mr5'></i>",
                        name: '出勤月表',
                        href: '/Uri/attendance_list',
                        dataRight: 'ATTENDANCE_QUERY',
                        target: 'personnel-sum'
                    },
                    evaluate: {
                        icon: "<i class='iconfont icon-danju f14 mr5'></i>",
                        name: '考评月表',
                        href: '/Uri/evaluate_list',
                        dataRight: 'EVALUATE_QUERY',
                        target: 'personnel-sum'
                    },
                    achievement: {
                        icon: "<i class='iconfont icon-danju f14 mr5'></i>",
                        name: '工资月表',
                        href: '/Uri/achievement_list',
                        dataRight: 'ACHIEVEMENT_QUERY',
                        target: 'personnel-sum'
                    },
                    attendanceRank: {
                        icon: "<i class='iconfont icon-paihang f14 mr5'></i>",
                        name: '出勤排名',
                        href: '/Uri/attendance_rank',
                        dataRight: 'ATTENDANCE_RANK',
                        target: 'personnel-report'
                    },
                    userMedal: {
                        icon: "<i class='iconfont icon-jiangzhang f14 mr5'></i>",
                        name: '荣誉奖章',
                        href: '/Uri/user_medal_list',
                        dataRight: 'USER_MEDAL_QUERY',
                        target: 'personnel-report'
                    },

                    // 财务
                    company: {
                        icon: "<i class='iconfont icon-jituan f14 mr5'></i>",
                        name: '经营主体',
                        href: '/Uri/company_list',
                        dataRight: 'COMPANY_QUERY',
                        target: 'money-base'
                    },
                    companyAccount: {
                        icon: "<i class='iconfont icon-zhanghu f14 mr5'></i>",
                        name: '结算账户',
                        href: '/Uri/company_account_list',
                        dataRight: 'COMPANYACCOUNT_QUERY',
                        target: 'money-base'
                    },
                    settlementtype: {
                        icon: "<i class='iconfont icon-leibie f14 mr5'></i>",
                        name: '结算方式',
                        href: '/Uri/category_list?typeNumber=settlementtype',
                        dataRight: 'SETTLEMENTTYPE_QUERY',
                        target: 'money-base'
                    },
                    incometype: {
                        icon: "<i class='iconfont icon-leibie f14 mr5'></i>",
                        name: '收入类别',
                        href: '/Uri/category_list?typeNumber=incometype',
                        dataRight: 'INCOMETYPE_QUERY',
                        target: 'money-base'
                    },
                    expendtype: {
                        icon: "<i class='iconfont icon-leibie f14 mr5'></i>",
                        name: '支出类别',
                        href: '/Uri/category_list?typeNumber=expendtype',
                        dataRight: 'EXPENDTYPE_QUERY',
                        target: 'money-base'
                    },
                    payReq: {
                        icon: "<i class='iconfont icon-shenqing f14 mr5'></i>",
                        name: '付款申请',
                        href: '/Uri/payReq_list',
                        dataRight: 'PAYREQ_QUERY',
                        target: 'money-req'
                    },
                    expensesReq: {
                        icon: "<i class='iconfont icon-shenqing f14 mr5'></i>",
                        name: '报销申请',
                        href: '/Uri/expensesReq_list',
                        dataRight: 'EXPENSESREQ_QUERY',
                        target: 'money-req'
                    },
                    receipt: {
                        icon: "<i class='iconfont icon-caiwudanju f14 mr5'></i>",
                        name: '业务收款',
                        href: '/Uri/receipt_list',
                        dataRight: 'RECEIPT_QUERY',
                        target: 'money-bill'
                    },
                    payment: {
                        icon: "<i class='iconfont icon-caiwudanju f14 mr5'></i>",
                        name: '业务付款',
                        href: '/Uri/payment_list',
                        dataRight: 'PAYMENT_QUERY',
                        target: 'money-bill'
                    },
                    income: {
                        icon: "<i class='iconfont icon-caiwudanju f14 mr5'></i>",
                        name: '其他收入',
                        href: '/Uri/income_list',
                        dataRight: 'INCOME_QUERY',
                        target: 'money-bill'
                    },
                    expend: {
                        icon: "<i class='iconfont icon-caiwudanju f14 mr5'></i>",
                        name: '其他支出',
                        href: '/Uri/expend_list',
                        dataRight: 'EXPEND_QUERY',
                        target: 'money-bill'
                    },
                    invoice: {
                        icon: "<i class='iconfont icon-fapiao f14 mr5'></i>",
                        name: '发票记录',
                        href: '/Uri/invoice_list',
                        dataRight: 'INVOICE_QUERY',
                        target: 'money-tax'
                    },
                    invoiceMiss: {
                        icon: "<i class='iconfont icon-fapiao f14 mr5'></i>",
                        name: '收票遗漏',
                        href: '/Uri/invoice_miss_list',
                        dataRight: 'INVOICE_QUERY',
                        target: 'money-tax'
                    },
                    invoiceErr: {
                        icon: "<i class='iconfont icon-fapiao f14 mr5'></i>",
                        name: '识票异常',
                        href: '/Uri/invoice_err_list',
                        dataRight: 'FINANCE_MANAGER',
                        target: 'money-tax'
                    },
                    taxReturn: {
                        icon: "<i class='iconfont icon-nashui f14 mr5'></i>",
                        name: '纳税申报',
                        href: '/Uri/tax_return',
                        dataRight: 'TAX_RETURN_QUERY',
                        target: 'money-tax'
                    },
                    taxReport: {
                        icon: "<i class='iconfont icon-nashui f14 mr5'></i>",
                        name: '税务分析',
                        href: '/Uri/tax_report',
                        dataRight: 'TAX_REPORT_QUERY',
                        target: 'money-tax'
                    },
                    moneyDetail: {
                        icon: "<i class='iconfont icon-liebiao f14 mr5'></i>",
                        name: '收支明细',
                        href: '/Uri/money_detail',
                        dataRight: 'MONEY_DETAIL',
                        target: 'money-report'
                    },
                    moneyReport: {
                        icon: "<i class='iconfont icon-baobiao f14 mr5'></i>",
                        name: '财务报表',
                        href: '/Uri/money_report',
                        dataRight: 'MONEY_REPORT',
                        target: 'money-report'
                    },

                    // 数据
                    goodsNewTrend: {
                        icon: "<i class='iconfont icon-shuju f14 mr5'></i>",
                        name: '新品趋势',
                        href: '/Uri/goods_new_trend',
                        dataRight: 'SYS_ADVANCE',
                        target: 'manage-data'
                    },
                    goodsDevTrend: {
                        icon: "<i class='iconfont icon-shuju f14 mr5'></i>",
                        name: '开发趋势',
                        href: '/Uri/goods_dev_trend',
                        dataRight: 'SYS_ADVANCE',
                        target: 'manage-data'
                    },
                    salesRank: {
                        icon: "<i class='iconfont icon-paihang f14 mr5'></i>",
                        name: '销量排行',
                        href: '/Uri/sales_rank',
                        dataRight: 'SALES_RANK',
                        target: 'manage-data'
                    },
                    salesDistribution: {
                        icon: "<i class='iconfont icon-shuju f14 mr5'></i>",
                        name: '平台占比',
                        href: '/Uri/sales_distribution',
                        dataRight: 'SYS_ADVANCE',
                        target: 'manage-data'
                    },
                    salesTrend: {
                        icon: "<i class='iconfont icon-shuju f14 mr5'></i>",
                        name: '销量趋势',
                        href: '/Uri/sales_trend',
                        dataRight: 'SYS_ADVANCE',
                        target: 'manage-data'
                    },
                    purchaseTrend: {
                        icon: "<i class='iconfont icon-shuju f14 mr5'></i>",
                        name: '采购趋势',
                        href: '/Uri/purchase_trend',
                        dataRight: 'SYS_ADVANCE',
                        target: 'manage-data'
                    },
                    deliverTrend: {
                        icon: "<i class='iconfont icon-shuju f14 mr5'></i>",
                        name: '发包趋势',
                        href: '/Uri/deliver_trend',
                        dataRight: 'SYS_ADVANCE',
                        target: 'manage-data'
                    },
                    deliverDistribution: {
                        icon: "<i class='iconfont icon-shuju f14 mr5'></i>",
                        name: '发包分配',
                        href: '/Uri/deliver_distribution',
                        dataRight: 'SYS_ADVANCE',
                        target: 'manage-data'
                    },
                    deliverFeeTrend: {
                        icon: "<i class='iconfont icon-shuju f14 mr5'></i>",
                        name: '运费趋势',
                        href: '/Uri/deliver_fee_trend',
                        dataRight: 'SYS_ADVANCE',
                        target: 'manage-data'
                    },

                    // 设置
                    sysRight: {
                        icon: "<i class='iconfont icon-quanxian f14 mr5'></i>",
                        name: '系统权限',
                        href: '/Uri/system_right',
                        dataRight: 'SYS_RIGHT_QUERY',
                        target: 'setting-system'
                    },
                    sysPar: {
                        icon: "<i class='iconfont icon-canshu f14 mr5'></i>",
                        name: '系统参数',
                        href: '/Uri/system_parameter',
                        dataRight: 'SYS_PAR_QUERY',
                        target: 'setting-system'
                    },
                    sysTask: {
                        icon: "<i class='iconfont icon-renwu f14 mr5'></i>",
                        name: '系统任务',
                        href: '/Uri/system_task',
                        dataRight: 'SYS_TASK_QUERY',
                        target: 'setting-system'
                    },
                    expPar: {
                        icon: "<i class='iconfont icon-xishu f14 mr5'></i>",
                        name: '业绩系数',
                        href: '/Uri/exp_par',
                        dataRight: 'EXP_PAR_QUERY',
                        target: 'setting-system'
                    },
                    user: {
                        icon: "<i class='iconfont icon-yonghu f14 mr5'></i>",
                        name: '员工账号',
                        href: '/Uri/user_list',
                        dataRight: 'USER_QUERY',
                        target: 'setting-user'
                    },
                    shopAccount: {
                        icon: "<i class='iconfont icon-yonghu f14 mr5'></i>",
                        name: '网店账号',
                        href: '/Uri/shop_account_list',
                        dataRight: 'SHOPACCOUNT_QUERY',
                        target: 'setting-user'
                    },
                    clearWaste: {
                        icon: "<i class='iconfont icon-huishouzhan f14 mr5'></i>",
                        name: '垃圾清理',
                        href: '/Uri/clear_waste',
                        dataRight: 'SYS_ADMIN',
                        target: 'setting-clear'
                    },
                    sysLog: {
                        icon: "<i class='iconfont icon-rizhi f14 mr5'></i>",
                        name: '系统日志',
                        href: '/Uri/system_log',
                        dataRight: 'SYS_LOG_QUERY',
                        target: 'setting-log'
                    }
                };

                //只显示自己有权限的模块的菜单入口
                $.each(defaultList, function(i, v) {
                    if ($.inArray(v.dataRight, SYSTEM.rights) !== -1) {
                        list[i] = defaultList[i];
                    }
                });
            } else {
                $.dialog.tips(res.msg, 2, 99);
            }
        });
        if (SYSTEM.locked && !SYSTEM.unlock) {
            list = {
                plan: {
                    icon: "<i class='iconfont icon-jihua f14 mr5'></i>",
                    name: '工作目标',
                    href: '/Uri/plan_list',
                    dataRight: 'SYS_DEFAULT',
                    target: 'index-offen'
                },
                duty: {
                    icon: "<i class='iconfont icon-jiaojie f14 mr5'></i>",
                    name: '工作转交',
                    href: '/Uri/duty_list',
                    dataRight: 'SYS_DEFAULT',
                    target: 'index-offen'
                },
                note: {
                    icon: "<i class='iconfont icon-beiwang f14 mr5'></i>",
                    name: '私人备忘',
                    href: '/Uri/note_list',
                    dataRight: 'SYS_DEFAULT',
                    target: 'index-offen'
                },
                worksite: {
                    icon: "<i class='iconfont icon-wangzhan f14 mr5'></i>",
                    name: '业务网站',
                    href: '/Uri/work_site_list',
                    dataRight: 'WORKSITE_QUERY',
                    target: 'index-offen'
                },
                knowledges: {
                    icon: "<i class='iconfont icon-chanpin f14 mr5'></i>",
                    name: '业务知识',
                    href: '/Uri/news_list?type=1',
                    dataRight: 'NEWS_QUERY',
                    target: 'index-news'
                },
                videos: {
                    icon: "<i class='iconfont icon-shipin f14 mr5'></i>",
                    name: '学习视频',
                    href: '/Uri/news_list?type=2',
                    dataRight: 'NEWS_QUERY',
                    target: 'index-news'
                },
                files: {
                    icon: "<i class='iconfont icon-wenjian f14 mr5'></i>",
                    name: '通知公告',
                    href: '/Uri/news_list?type=3',
                    dataRight: 'NEWS_QUERY',
                    target: 'index-news'
                },
                rules: {
                    icon: "<i class='iconfont icon-guizhang f14 mr5'></i>",
                    name: '制度文件',
                    href: '/Uri/news_list?type=4',
                    dataRight: 'NEWS_QUERY',
                    target: 'index-news'
                },
                goods: {
                    icon: "<i class='iconfont icon-chanpin f14 mr5'></i>",
                    name: '产品信息',
                    href: '/Uri/goods_list',
                    dataRight: 'GOODS_QUERY',
                    target: 'product-base'
                },
                goodsDevReq: {
                    icon: "<i class='iconfont icon-shenqing f14 mr5'></i>",
                    name: '定制申请',
                    href: '/Uri/goods_dev_req_list',
                    dataRight: 'GOODSDEVREQ_QUERY',
                    target: 'develop-develop'
                },
                goodsFileOne: {
                    icon: "<i class='iconfont icon-shuomingshu f14 mr5'></i>",
                    name: '中文图纸',
                    href: '/Uri/goods_file_list?type=goodsFileOne',
                    dataRight: 'GOODSFILEONE_QUERY',
                    target: 'develop-file'
                },
                goodsFileTwo: {
                    icon: "<i class='iconfont icon-tuwen f14 mr5'></i>",
                    name: '中文教程',
                    href: '/Uri/goods_file_list?type=goodsFileTwo',
                    dataRight: 'GOODSFILETWO_QUERY',
                    target: 'develop-file'
                },
                goodsFileOne_EN: {
                    icon: "<i class='iconfont icon-shuomingshu f14 mr5'></i>",
                    name: '外文图纸',
                    href: '/Uri/goods_file_list?type=goodsFileOne_EN',
                    dataRight: 'GOODSFILEONE_QUERY',
                    target: 'develop-file'
                },
                goodsFileTwo_EN: {
                    icon: "<i class='iconfont icon-tuwen f14 mr5'></i>",
                    name: '外文教程',
                    href: '/Uri/goods_file_list?type=goodsFileTwo_EN',
                    dataRight: 'GOODSFILETWO_QUERY',
                    target: 'develop-file'
                },
                customer: {
                    icon: "<i class='iconfont icon-kehu f14 mr5'></i>",
                    name: '客户信息',
                    href: '/Uri/customer_list',
                    dataRight: 'CUSTOMER_QUERY',
                    target: 'sales-base'
                },
                service: {
                    icon: "<i class='iconfont icon-kefu f14 mr5'></i>",
                    name: '客户之声',
                    href: '/Uri/service_list',
                    dataRight: 'SERVICE_QUERY',
                    target: 'sales-service'
                },
                discountReq: {
                    icon: "<i class='iconfont icon-shenqing f14 mr5'></i>",
                    name: '优惠申请',
                    href: '/Uri/discountReq_list',
                    dataRight: 'SERVICE_QUERY',
                    target: 'sales-service'
                },
                potentialCustomer: {
                    icon: "<i class='iconfont icon-kehu f14 mr5'></i>",
                    name: '潜客登记',
                    href: '/Uri/potentialCustomer_list',
                    dataRight: 'SERVICE_QUERY',
                    target: 'sales-service'
                },
                reviewHold: {
                    icon: "<i class='iconfont icon-pingjia f14 mr5'></i>",
                    name: '差评删改',
                    href: '/Uri/reviewHold_list',
                    dataRight: 'SERVICE_QUERY',
                    target: 'sales-service'
                },
                salerDutyChoose: {
                    icon: "<i class='iconfont icon-pingjia f14 mr5'></i>",
                    name: '值班选择',
                    href: '/Uri/saler_duty_choose',
                    dataRight: 'SERVICE_QUERY',
                    target: 'sales-service'
                },
                deliverCompany: {
                    icon: "<i class='iconfont icon-kuaidi f14 mr5'></i>",
                    name: '快递公司',
                    href: '/Uri/deliver_company_list',
                    dataRight: 'DELIVERCOM_QUERY',
                    target: 'deliver-base'
                },
                areas: {
                    icon: "<i class='iconfont icon-ditu f14 mr5'></i>",
                    name: '全国地区',
                    href: '/Uri/areas_list',
                    dataRight: 'AREAS_QUERY',
                    target: 'deliver-base'
                },
                deliverSearch: {
                    icon: "<i class='iconfont icon-kuaidichaxun f14 mr5'></i>",
                    name: '快递查询',
                    href: '/Uri/deliver_search',
                    dataRight: 'SYS_DEFAULT',
                    target: 'deliver-tool'
                },
                deliverPredict: {
                    icon: "<i class='iconfont icon-kuaidigusuan f14 mr5'></i>",
                    name: '快递估算',
                    href: '/Uri/deliver_predict',
                    dataRight: 'SYS_DEFAULT',
                    target: 'deliver-tool'
                },
                deliverBag: {
                    icon: "<i class='iconfont icon-baoguo f14 mr5'></i>",
                    name: '发包记录',
                    href: '/Uri/deliver_bag_list',
                    dataRight: 'DELIVERBAG_QUERY',
                    target: 'deliver-bag'
                },
                deliverBagDull: {
                    icon: "<i class='iconfont icon-baoguo f14 mr5'></i>",
                    name: '发包滞留',
                    href: '/Uri/deliver_bag_dull',
                    dataRight: 'DELIVERBAG_QUERY',
                    target: 'deliver-bag'
                },
                deliverBagOff: {
                    icon: "<i class='iconfont icon-baoguo f14 mr5'></i>",
                    name: '发包拦截',
                    href: '/Uri/deliver_bag_off_list',
                    dataRight: 'DELIVERBAG_QUERY',
                    target: 'deliver-bag'
                },
                deliverBagRec: {
                    icon: "<i class='iconfont icon-baoguo f14 mr5'></i>",
                    name: '收包记录',
                    href: '/Uri/deliver_bag_rec_list',
                    dataRight: 'DELIVERBAG_QUERY',
                    target: 'deliver-bag'
                },
                userStaffMap: {
                    icon: "<i class='iconfont icon-yonghuzu f14 mr5'></i>",
                    name: '全员速览',
                    href: '/Uri/user_staff_map',
                    dataRight: 'SYS_DEFAULT',
                    target: 'personnel-base'
                }
            };
        }
    },
    initNav: function() {
        $('#nav li.item, .sub-nav-wrap .nav-item').removeClass('dn');
        menu.init($('#nav'));
    },
    initTab: function() {
        THISPAGE.setMainHeight();
        $('#page-tab').ligerTab({
            height: '100%',
            onAfterSelectTabItem: function(a) {
                if (a) {
                    //设置左侧菜单选中状态
                    var b = a.match(/([a-zA-Z]+)[-]?/)[1];
                    $('#nav > li.item-' + b).click();
                    if ($('#nav > li.item-' + b + ' .sub-nav > li.' + a).length > 0) {
                        $('#nav > li .sub-nav > li').removeClass('current');
                        $('#nav > li.item-' + b + ' .sub-nav > li.' + a).addClass('current');
                    } else if ($('#nav > li.item-' + b + ' .sub-nav > li.' + a.replace(/\d+/g, '')).length > 0) {
                        $('#nav > li .sub-nav > li').removeClass('current');
                        $('#nav > li.item-' + b + ' .sub-nav > li.' + a.replace(/\d+/g, '')).addClass('current');
                    }
                }
            }
        });
        tab = $('#page-tab').ligerGetTabManager();
        tab.addTabItem({tabid: 'index', text: '<i class="iconfont icon-zhuye f14 mr5"></i>主页', url: '/Uri/main', showClose: !1});
        Public.pageTab();
    },
    initUser: function() {
        $('.l-tab-links').append(
            '<div class="l-tab-user">' +
            '<div class="l-tab-user-avatar" title="个人中心"></div>' +
            '<div class="l-tab-user-name"><span title="个人中心">' + SYSTEM.uName + '</span><i class="iconfont icon-xiajiantou f12 ml5 f12" title="个人中心"></i><br><span class="gray" title="个人中心">' + SYSTEM.user.position + '</span></div>' +
            '<div class="l-tab-user-con dn">' +
            '<div id="myAchievement" class="user-nav"><i class="mr5 iconfont icon-yeji f14"></i>我的绩效</div>' +
            '<div id="myEvaluation" class="user-nav"><i class="mr5 iconfont icon-kaoping f14"></i>我的考评</div>' +
            '<div id="myLog" class="user-nav"><i class="mr5 iconfont icon-rizhi f14"></i>我的日志</div>' +
            '<div id="myRights" class="user-nav bbe pb10 mb10"><i class="mr5 iconfont icon-quanxian f14"></i>我的权限</div>' +
            '<div id="setInfo" class="user-nav"><i class="mr5 iconfont icon-ziliao f14"></i>修改资料</div>' +
            '<div id="setAvatar" class="user-nav"><i class="mr5 iconfont icon-touxiang f14"></i>修改头像</div>' +
            '<div id="setPassword" class="user-nav bbe pb10 mb10"><i class="mr5 iconfont icon-mima f14"></i>修改密码</div>' +
            '<div id="exit" class="user-nav"><i class="mr5 iconfont icon-tuichu f14"></i>安全退出</div>' +
            '</div>' +
            '</div>'
        );
        $('.l-tab-user-avatar').css({'background': 'url("/data/upfile/avatar/' + (SYSTEM.user.avatar || 'avatar.jpg') + '") center center / 100% 100% no-repeat'});
    },
    initEvent: function() {
        $('#col-side').on({
            mouseenter: function(e) {
                $(this).css({'width': '200px'});
                $('.sub-nav-wrap .title').css({'width': '150px'});
                $('.sub-nav-wrap li').css({'width': '150px'});
                $('.sub-nav-wrap i').css({'font-size': '18px'});
                $('.sub-nav-wrap .nav-alert').css({'right': '10px'});
            },
            mouseleave: function(e) {
                $(this).css({'width': '100px'});
                $('.sub-nav-wrap .title').css({'width': '50px'});
                $('.sub-nav-wrap li').css({'width': '50px'});
                $('.sub-nav-wrap i').css({'font-size': '22px'});
                $('.sub-nav-wrap .nav-alert').css({'right': '5px'});
            }
        });
        $('#nav > li').on('click', function() {
            $(this).siblings().removeClass('current');
            $(this).siblings().find('.sub-nav-wrap').addClass('dn');
            $(this).addClass('current');
            $(this).find('.sub-nav-wrap').removeClass('dn');
        });
        $('body').on('mouseleave', '.l-tab-switch', function(e) {
            $('.l-tab-windowsswitch').remove();
        });
        // 每隔10分钟自动执行一次导航通知
        timer_alert = setInterval(() => {
            Public.ajaxGet('/Base/Home/alertData', {}, function(res) {
                if (res.status == 200) {
                    THISPAGE.loadAlertData(res.data);
                }
            });
        }, 600000);
        $('.l-tab-user').on({
            click: function(e) {
                $('.l-tab-windowsswitch').remove();
                $('.l-tab-msg-con').addClass('dn');
                if ($('.l-tab-user-con').hasClass('dn')) {
                    $('.l-tab-user-con').removeClass('dn');
                } else {
                    $('.l-tab-user-con').addClass('dn');
                }
            },
            mouseleave: function(e) {
                $('.l-tab-user-con').addClass('dn');
            }
        });
        $('#myRights').on('click', function() {
            tab.addTabItem({tabid: 'index-myRights', text: '<i class="iconfont icon-quanxian f14 mr5"></i>我的权限', url: '/Uri/user_rights', showClose: !0});
            $('.l-tab-user-con').addClass('dn');
        });
        $('#myLog').on('click', function() {
            tab.addTabItem({tabid: 'index-myLog', text: '<i class="iconfont icon-rizhi f14 mr5"></i>我的日志', url: '/Uri/user_log', showClose: !0});
            $('.l-tab-user-con').addClass('dn');
        });
        $('#myAchievement').on('click', function() {
            tab.addTabItem({tabid: 'index-myAchievement', text: '<i class="iconfont icon-yeji f14 mr5"></i>我的绩效', url: '/Uri/achievement_search', showClose: !0});
            $('.l-tab-user-con').addClass('dn');
        });
        $('#myEvaluation').on('click', function() {
            tab.addTabItem({tabid: 'index-myEvaluation', text: '<i class="iconfont icon-kaoping f14 mr5"></i>我的考评', url: '/Uri/user_evaluation', showClose: !0});
            $('.l-tab-user-con').addClass('dn');
        });
        $('#setInfo').on('click', function() {
            $.dialog({
                title: '修改资料',
                content: 'url: /Uri/set_info',
                data: {},
                width: 600,
                height: 290,
                min: !1,
                max: !1,
                cache: !1,
                lock: !0
            });
            $('.l-tab-user-con').addClass('dn');
        });
        $('#setAvatar').on('click', function() {
            $.dialog({
                title: '修改头像',
                content: 'url: /Uri/set_avatar',
                data: {},
                width: 600,
                height: 200,
                min: !1,
                max: !1,
                cache: !1,
                lock: !0
            });
            $('.l-tab-user-con').addClass('dn');
        });
        $('#setPassword').on('click', function() {
            $.dialog({
                title: '修改密码',
                content: 'url: /Uri/set_password',
                data: {},
                width: 600,
                height: 170,
                min: !1,
                max: !1,
                cache: !1,
                lock: !0
            });
            $('.l-tab-user-con').addClass('dn');
        });
        $('#exit').on('click', function() {
            window.location = '/login/out';
        });
        $(window).on('resize', function() {
            THISPAGE.setMainHeight();
        });
        // 阻止浏览器的回退，弹消息让用户手动选择是否离开、刷新页面
        window.addEventListener('beforeunload', function(event) {
            event.preventDefault();
            event.returnValue = ''; // 兼容旧版本浏览器
        });
        // 阻止浏览器的回退（修改历史记录）
        window.history.pushState(null, null, window.location.href);
        window.onpopstate = function(event) {
            window.history.pushState(null, null, window.location.href);
        };
        // 阻止右键回退
        window.addEventListener('mousedown', function(event) {
            if (event.button === 2) { // 右键
                event.preventDefault();
                event.stopPropagation();
            }
        });
        // 阻止Alt+左方向键的回退
        window.addEventListener('keydown', function(event) {
            if (event.altKey && event.keyCode === 37) { // Alt+左方向键
                event.preventDefault();
                event.stopPropagation();
            }
        });
        // 如果是非锁定模式 初次加载 执行非锁定模式事件
        if (!SYSTEM.locked || SYSTEM.unlock) {
            THISPAGE.initNormalEvent();
        }
    },
    initNormalEvent: function() {
        // 初次加载延迟1秒后立即执行一次紧急通知
        urgentResult = !1;
        setTimeout(() => {
            Public.ajaxGet('/Base/Home/urgentData', {}, function(res) {
                if (res.status == 200 && res.data) {
                    urgentResult = !0;
                    THISPAGE.loadUrgentData(res.data);
                }
            });
        }, 1000);
        // 每隔1分钟钟自动执行一次紧急通知
        timer_urgent = setInterval(() => {
            if (!urgentPause && urgentResult) {
                urgentResult = !1;
                Public.ajaxGet('/Base/Home/urgentData', {}, function(res) {
                    if (res.status == 200 && res.data) {
                        urgentResult = !0;
                        THISPAGE.loadUrgentData(res.data);
                    }
                });
            }
        }, 60000);
        $('#urgentCon').on('click', '.urgent-p', function() {
            var tabid = $(this).data('tabid');
            var uri = $(this).data('uri');
            var url = $(this).data('url');
            if (tabid && uri && defaultList[tabid]) {
                 tab.addTabItem({
                    tabid: defaultList[tabid].target.toString().split('-')[0] + '-' + tabid,
                    text: defaultList[tabid].icon + defaultList[tabid].name,
                    url: defaultList[tabid].href + uri
                });
            } else if (url) {
                window.open(url);
            }
        });
        $('#urgentCon').on('click', '#noAlertCurLogin', function(a) {
            a.stopPropagation();
            a.preventDefault();
            var _this = $(this);
            var alertName = _this.data('alertname');
            Public.ajaxGet('/Base/Home/noAlertCurLogin', {alertName: alertName}, function(res) {
                if (res.status == 200) {
                    _this.parent().remove();
                }
            });
        });
        $('#urgentCon').on('click', '#pause10', function() {
            urgentPause = !0;
            clearInterval(timer_urgent);
            clearTimeout(timer_urgent_pause);
            $('.urgent-alert-con, .urgent-alert-hide').addClass('dn');
            $('.urgent-alert-tip').removeClass('dn');
            timer_urgent_pause = setTimeout(function() {
                urgentPause = !1;
                timer_urgent = setInterval(() => {
                    urgentResult = !0;
                    if (!urgentPause && urgentResult) {
                        urgentResult = !1;
                        Public.ajaxGet('/Base/Home/urgentData', {}, function(res) {
                            if (res.status == 200 && res.data) {
                                urgentResult = !0;
                                THISPAGE.loadUrgentData(res.data);
                            }
                        });
                    }
                }, 30000);
            }, 600000);
        });
        $('#urgentCon').on('click', '#pause30', function() {
            urgentPause = !0;
            clearInterval(timer_urgent);
            clearTimeout(timer_urgent_pause);
            $('.urgent-alert-con, .urgent-alert-hide').addClass('dn');
            $('.urgent-alert-tip').removeClass('dn');
            timer_urgent_pause = setTimeout(function() {
                urgentPause = !1;
                timer_urgent = setInterval(() => {
                    urgentResult = !0;
                    if (!urgentPause && urgentResult) {
                        urgentResult = !1;
                        Public.ajaxGet('/Base/Home/urgentData', {}, function(res) {
                            if (res.status == 200 && res.data) {
                                urgentResult = !0;
                                THISPAGE.loadUrgentData(res.data);
                            }
                        });
                    }
                }, 30000);
            }, 1800000);
        });
        $('#urgentCon').on('click', '#pause60', function() {
            urgentPause = !0;
            clearInterval(timer_urgent);
            clearTimeout(timer_urgent_pause);
            $('.urgent-alert-con, .urgent-alert-hide').addClass('dn');
            $('.urgent-alert-tip').removeClass('dn');
            timer_urgent_pause = setTimeout(function() {
                urgentPause = !1;
                timer_urgent = setInterval(() => {
                    urgentResult = !0;
                    if (!urgentPause && urgentResult) {
                        urgentResult = !1;
                        Public.ajaxGet('/Base/Home/urgentData', {}, function(res) {
                            if (res.status == 200 && res.data) {
                                urgentResult = !0;
                                THISPAGE.loadUrgentData(res.data);
                            }
                        });
                    }
                }, 30000);
            }, 3600000);
        });
        $('#urgentCon').on('click', '.urgent-alert-tip', function() {
            $(this).addClass('dn');
            clearInterval(timer_urgent);
            clearTimeout(timer_urgent_pause);
            urgentPause = !1;
            urgentResult = !1;
            Public.ajaxGet('/Base/Home/urgentData', {}, function(res) {
                if (res.status == 200 && res.data) {
                    urgentResult = !0;
                    THISPAGE.loadUrgentData(res.data);
                }
            });
            // 每隔1分钟钟自动执行一次紧急通知
            timer_urgent = setInterval(() => {
                urgentResult = !0;
                if (!urgentPause && urgentResult) {
                    urgentResult = !1;
                    Public.ajaxGet('/Base/Home/urgentData', {}, function(res) {
                        if (res.status == 200 && res.data) {
                            urgentResult = !0;
                            THISPAGE.loadUrgentData(res.data);
                        }
                    });
                }
            }, 60000);
        });
    },
    setMainHeight: function() {
        var a = $(window).height();
        var b = $('#main-bd');
        var c = a - b.offset().top;
        b.height(c);
    },
    loadAlertData: function(a) {
        $('.main-alert, .nav-alert').remove();
        //处理提醒信息
        if (a.taskNotice > 0) {
            $('.item-index .main-nav').append('<div class="main-alert"></div>');
            $('.index-task').append('<div class="nav-alert">' + a.taskNotice + '</div>');
        }
        if (a.planNotice > 0) {
            $('.item-index .main-nav').append('<div class="main-alert"></div>');
            $('.index-plan').append('<div class="nav-alert">' + a.planNotice + '</div>');
        }
        if (a.dutyNotice > 0) {
            $('.item-index .main-nav').append('<div class="main-alert"></div>');
            $('.index-duty').append('<div class="nav-alert">' + a.dutyNotice + '</div>');
        }
        if (a.managePlanNotice > 0) {
            $('.item-index .main-nav').append('<div class="main-alert"></div>');
            $('.index-managePlan').append('<div class="nav-alert">' + a.managePlanNotice + '</div>');
        }
        if (a.invWarningNum > 0) {
            $('.item-inventory .main-nav').append('<div class="main-alert"></div>');
            $('.inventory-invWarning').append('<div class="nav-alert">' + a.invWarningNum + '</div>');
        }
        if (a.purReqNotice > 0) {
            $('.item-purchase .main-nav').append('<div class="main-alert"></div>');
            $('.purchase-purchaseReq').append('<div class="nav-alert">' + a.purReqNotice + '</div>');
        }
        if (a.purContractReqNotice > 0) {
            $('.item-purchase .main-nav').append('<div class="main-alert"></div>');
            $('.purchase-purchaseContractReq').append('<div class="nav-alert">' + a.purContractReqNotice + '</div>');
        }
        if (a.discountReqNotice > 0) {
            $('.item-sales .main-nav').append('<div class="main-alert"></div>');
            $('.sales-discountReq').append('<div class="nav-alert">' + a.discountReqNotice + '</div>');
        }
        if (a.produceReqNotice > 0) {
            $('.item-produce .main-nav').append('<div class="main-alert"></div>');
            $('.produce-produceReq').append('<div class="nav-alert">' + a.produceReqNotice + '</div>');
        }
        if (a.payReqNotice > 0) {
            $('.item-money .main-nav').append('<div class="main-alert"></div>');
            $('.money-payReq').append('<div class="nav-alert">' + a.payReqNotice + '</div>');
        }
        if (a.expensesReqNotice > 0) {
            $('.item-money .main-nav').append('<div class="main-alert"></div>');
            $('.money-expensesReq').append('<div class="nav-alert">' + a.expensesReqNotice + '</div>');
        }
        if (a.invoiceMissNotice > 0) {
            $('.item-money .main-nav').append('<div class="main-alert"></div>');
            $('.money-invoiceMiss').append('<div class="nav-alert">' + a.invoiceMissNotice + '</div>');
        }
        if (a.invoiceErrNotice > 0) {
            $('.item-money .main-nav').append('<div class="main-alert"></div>');
            $('.money-invoiceErr').append('<div class="nav-alert">' + a.invoiceErrNotice + '</div>');
        }
        if (a.goodsDevReqNotice > 0) {
            $('.item-develop .main-nav').append('<div class="main-alert"></div>');
            $('.develop-goodsDevReq').append('<div class="nav-alert">' + a.goodsDevReqNotice + '</div>');
        }
        if (a.goodsFileReqNotice > 0) {
            $('.item-develop .main-nav').append('<div class="main-alert"></div>');
            $('.develop-goodsFileReq').append('<div class="nav-alert">' + a.goodsFileReqNotice + '</div>');
        }
        if (a.poNotice > 0) {
            $('.item-purchase .main-nav').append('<div class="main-alert"></div>');
            $('.purchase-purchaseOrder').append('<div class="nav-alert">' + a.poNotice + '</div>');
        }
        if (a.pcNotice > 0) {
            $('.item-purchase .main-nav').append('<div class="main-alert"></div>');
            $('.purchase-purchaseContract').append('<div class="nav-alert">' + a.pcNotice + '</div>');
        }
        if (a.pcFillNotice > 0) {
            $('.item-purchase .main-nav').append('<div class="main-alert"></div>');
            $('.purchase-purchaseContractFill').append('<div class="nav-alert">' + a.pcFillNotice + '</div>');
        }
        if (a.pcLostNotice > 0) {
            $('.item-purchase .main-nav').append('<div class="main-alert"></div>');
            $('.purchase-purchaseContractLost').append('<div class="nav-alert">' + a.pcLostNotice + '</div>');
        }
        if (a.pcModifyPurAmountNotice > 0) {
            $('.item-purchase .main-nav').append('<div class="main-alert"></div>');
            $('.purchase-purchaseContractModifyPurAmount').append('<div class="nav-alert">' + a.pcModifyPurAmountNotice + '</div>');
        }
        if (a.purSuggestNotice > 0) {
            $('.item-purchase .main-nav').append('<div class="main-alert"></div>');
            $('.purchase-purchaseSuggest').append('<div class="nav-alert">' + a.purSuggestNotice + '</div>');
        }
        if (a.purQCNotice > 0) {
            $('.item-purchase .main-nav').append('<div class="main-alert"></div>');
            $('.purchase-purchaseQC').append('<div class="nav-alert">' + a.purQCNotice + '</div>');
        }
        if (a.purQCBackNotice > 0) {
            $('.item-purchase .main-nav').append('<div class="main-alert"></div>');
            $('.purchase-purchaseQCBack').append('<div class="nav-alert">' + a.purQCBackNotice + '</div>');
        }
        if (a.soNotice > 0) {
            $('.item-sales .main-nav').append('<div class="main-alert"></div>');
            $('.sales-salesOrder').append('<div class="nav-alert">' + a.soNotice + '</div>');
        }
        if (a.scNotice > 0) {
            $('.item-sales .main-nav').append('<div class="main-alert"></div>');
            $('.sales-salesContract').append('<div class="nav-alert">' + a.scNotice + '</div>');
        }
        if (a.receiptNotice > 0) {
            $('.item-money .main-nav').append('<div class="main-alert"></div>');
            $('.money-receipt').append('<div class="nav-alert">' + a.receiptNotice + '</div>');
        }
        if (a.paymentNotice > 0) {
            $('.item-money .main-nav').append('<div class="main-alert"></div>');
            $('.money-payment').append('<div class="nav-alert">' + a.paymentNotice + '</div>');
        }
        if (a.incomeNotice > 0) {
            $('.item-money .main-nav').append('<div class="main-alert"></div>');
            $('.money-income').append('<div class="nav-alert">' + a.incomeNotice + '</div>');
        }
        if (a.expendNotice > 0) {
            $('.item-money .main-nav').append('<div class="main-alert"></div>');
            $('.money-expend').append('<div class="nav-alert">' + a.expendNotice + '</div>');
        }
        if (a.deliverNotice > 0) {
            $('.item-deliver .main-nav').append('<div class="main-alert"></div>');
            $('.deliver-deliver').append('<div class="nav-alert">' + a.deliverNotice + '</div>');
        }
        if (a.serviceNotice > 0) {
            $('.item-sales .main-nav').append('<div class="main-alert"></div>');
            $('.sales-service').append('<div class="nav-alert">' + a.serviceNotice + '</div>');
        }
        if (a.bidProgramNotice > 0) {
            $('.item-bid .main-nav').append('<div class="main-alert"></div>');
            $('.bid-bidProgram').append('<div class="nav-alert">' + a.bidProgramNotice + '</div>');
        }
        if (a.deliverDullNotice > 0) {
            $('.item-deliver .main-nav').append('<div class="main-alert"></div>');
            $('.deliver-deliverBagDull').append('<div class="nav-alert">' + a.deliverDullNotice + '</div>');
        }
        if (a.goodsTaskNotice > 0) {
            $('.item-develop .main-nav').append('<div class="main-alert"></div>');
            $('.develop-goodsTask').append('<div class="nav-alert">' + a.goodsTaskNotice + '</div>');
        }
        if (a.proKittingTaskNotice > 0) {
            $('.item-produce .main-nav').append('<div class="main-alert"></div>');
            $('.produce-produceKittingTask').append('<div class="nav-alert">' + a.proKittingTaskNotice + '</div>');
        }
        if (a.proKittingTaskUpNotice > 0) {
            $('.item-produce .main-nav').append('<div class="main-alert"></div>');
            $('.produce-produceKittingTaskUp').append('<div class="nav-alert">' + a.proKittingTaskUpNotice + '</div>');
        }
        if (a.proProcessingTaskUpNotice > 0) {
            $('.item-produce .main-nav').append('<div class="main-alert"></div>');
            $('.produce-produceProcessingTaskUp').append('<div class="nav-alert">' + a.proProcessingTaskUpNotice + '</div>');
        }
        if (a.saleOrderSpiderErrGoodsNotice > 0) {
            $('.item-product .main-nav').append('<div class="main-alert"></div>');
            $('.product-salesOrderSpiderErrGoods').append('<div class="nav-alert">' + a.saleOrderSpiderErrGoodsNotice + '</div>');
        }
        if (a.saleOrderSpiderErrPriceNotice > 0) {
            $('.item-product .main-nav').append('<div class="main-alert"></div>');
            $('.product-salesOrderSpiderErrPrice').append('<div class="nav-alert">' + a.saleOrderSpiderErrPriceNotice + '</div>');
        }
        if (a.competitiveMonitorNotice > 0) {
            $('.item-product .main-nav').append('<div class="main-alert"></div>');
            $('.product-competitiveMonitor').append('<div class="nav-alert">' + a.competitiveMonitorNotice + '</div>');
        }
        if (a.clockinNotice > 0) {
            $('.item-personnel .main-nav').append('<div class="main-alert"></div>');
            $('.personnel-clockin').append('<div class="nav-alert">' + a.clockinNotice + '</div>');
        }
        if (a.workFillNotice > 0) {
            $('.item-personnel .main-nav').append('<div class="main-alert"></div>');
            $('.personnel-workFill').append('<div class="nav-alert">' + a.workFillNotice + '</div>');
        }
        if (a.workOvertimeNotice > 0) {
            $('.item-personnel .main-nav').append('<div class="main-alert"></div>');
            $('.personnel-workOvertime').append('<div class="nav-alert">' + a.workOvertimeNotice + '</div>');
        }
        if (a.workHourNotice > 0) {
            $('.item-personnel .main-nav').append('<div class="main-alert"></div>');
            $('.personnel-workHour').append('<div class="nav-alert">' + a.workHourNotice + '</div>');
        }
        if (a.noteNotice > 0) {
            $('.item-index .main-nav').append('<div class="main-alert"></div>');
            $('.index-note').append('<div class="nav-alert">' + a.noteNotice + '</div>');
        }
    },
    loadUrgentData: function(a) {
        var urgentAlert = '';
        $.each(a, function(k, v) {
            if (v.tabid && v.uri) {
                urgentAlert += '<p data-tabid="' + v.tabid + '" data-uri="' + v.uri + '" class="urgent-p mb5 pl10 pr10 lh24 ' + (v.class || 'hover-red') + ' b-r-3-left"><i class="iconfont icon-tongzhi f12 mr10"></i>' + v.alert + '</p>';
            } else if (v.url) {
                urgentAlert += '<p data-url="' + v.url + '" class="urgent-p mb5 pl10 pr10 lh24 hover-red b-r-3-left"><i class="iconfont icon-tongzhi f12 mr10"></i>' + v.alert + '</p>';
            }
        });
        if (urgentAlert) {
            $('.urgent-alert-info').html(urgentAlert);
            $('.urgent-alert-con, .urgent-alert-hide').removeClass('dn');
        } else {
            $('.urgent-alert-info').html('');
        }
    }
};
$(function() {
    THISPAGE.init();
});
