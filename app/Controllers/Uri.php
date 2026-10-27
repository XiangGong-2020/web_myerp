<?php
namespace App\Controllers;

class Uri extends BaseController
{

    // 首页
    public function index(): string
    {
        return view('index');
    }
    // 主页
    public function main(): string
    {
        return view('main');
    }

    /* 通用 */
    // 类别 列表
    public function category_list(): string
    {
        return view('category-list');
    }
    // 类别 管理
    public function category_manage(): string
    {
        return view('category-manage');
    }
    // 客户+商家 选择
    public function select_contact(): string
    {
        return view('select-contact');
    }
    // 客户+商家 地址管理
    public function address_manage(): string
    {
        return view('address-manage');
    }


    /* 菜单 主页 */
    // 任务清单 列表
    public function task_list(): string
    {
        return view('task-list');
    }
    // 任务清单 管理
    public function task_manage(): string
    {
        return view('task-manage');
    }
    // 工作目标 列表
    public function plan_list(): string
    {
        return view('plan-list');
    }
    // 工作目标 管理
    public function plan_manage(): string
    {
        return view('plan-manage');
    }
    // 管理计划 列表
    public function manage_plan_list(): string
    {
        return view('manage-plan-list');
    }
    // 管理计划 管理
    public function manage_plan_manage(): string
    {
        return view('manage-plan-manage');
    }
    // 工作转交 列表
    public function duty_list(): string
    {
        return view('duty-list');
    }
    // 工作转交 管理
    public function duty_manage(): string
    {
        return view('duty-manage');
    }
    // 私人备忘 列表
    public function note_list(): string
    {
        return view('note-list');
    }
    // 私人备忘 管理
    public function note_manage(): string
    {
        return view('note-manage');
    }
    // 业务网站 列表
    public function work_site_list(): string
    {
        return view('work-site-list');
    }
    // 业务网站 管理
    public function work_site_manage(): string
    {
        return view('work-site-manage');
    }
    // 企业网盘
    public function netdisk(): string
    {
        return view('netdisk');
    }
    // 监控回放
    public function camera_rec(): string
    {
        return view('camera-rec');
    }
    // 监控直播
    public function camera_live(): string
    {
        return view('camera-live');
    }
    // 内部资讯 列表
    public function news_list(): string
    {
        return view('news-list');
    }
    // 内部资讯 管理
    public function news_manage(): string
    {
        return view('news-manage');
    }

    /* 菜单 研发 */
    // 产品信息 列表
    public function goods_list(): string
    {
        return view('goods-list');
    }
    // 产品信息 管理
    public function goods_manage(): string
    {
        return view('goods-manage');
    }
    // 产品信息 编辑历史
    public function goods_modify_his(): string
    {
        return view('goods-modify-his');
    }
    // 产品信息 图片管理
    public function goods_img_upload(): string
    {
        return view('goods-img-upload');
    }
    // 产品信息 选择
    public function goods_batch(): string
    {
        return view('goods-batch');
    }
    // 定制申请 列表
    public function goods_dev_req_list(): string
    {
        return view('goods-dev-req-list');
    }
    // 定制申请 管理
    public function goods_dev_req_manage(): string
    {
        return view('goods-dev-req-manage');
    }
    // 产品开发 列表
    public function goods_task_list(): string
    {
        return view('goods-task-list');
    }
    // 产品开发 管理
    public function goods_task_manage(): string
    {
        return view('goods-task-manage');
    }
    // 产品文件 列表
    public function goods_file_list(): string
    {
        return view('goods-file-list');
    }
    // 产品文件 管理
    public function goods_file_manage(): string
    {
        return view('goods-file-manage');
    }
    // 产品文件 下载申请 列表
    public function goods_file_req_list(): string
    {
        return view('goods-file-req-list');
    }
    // 产品文件 下载申请 管理
    public function goods_file_req_manage(): string
    {
        return view('goods-file-req-manage');
    }
    // 新品趋势
    public function goods_new_trend(): string
    {
        return view('goods-new-trend');
    }
    // 开发趋势
    public function goods_dev_trend(): string
    {
        return view('goods-dev-trend');
    }

    /* 菜单 投标 */
    // 招标信息 列表
    public function bid_info_list(): string
    {
        return view('bid-info-list');
    }
    // 招标信息 管理
    public function bid_info_manage(): string
    {
        return view('bid-info-manage');
    }
    // 招标网站与关键词
    public function bid_site_and_keyword(): string
    {
        return view('bid-site-and-keyword');
    }
    // 招标网站 管理
    public function bid_site_manage(): string
    {
        return view('bid-site-manage');
    }
    // 招标网站 选择
    public function bid_site_batch(): string
    {
        return view('bid-site-batch');
    }
    // 招标关键词 管理
    public function bid_keyword_manage(): string
    {
        return view('bid-keyword-manage');
    }
    // 预算报价 列表
    public function bid_quote_list(): string
    {
        return view('bid-quote-list');
    }
    // 预算报价 管理
    public function bid_quote_manage(): string
    {
        return view('bid-quote-manage');
    }
    // 预算报价 报价
    public function bid_quote_quote(): string
    {
        return view('bid-quote-quote');
    }
    // 投标报价 参考
    public function bid_quote_reference(): string
    {
        return view('bid-quote-reference');
    }
    // 投标项目 列表
    public function bid_program_list(): string
    {
        return view('bid-program-list');
    }
    // 投标项目 管理
    public function bid_program_manage(): string
    {
        return view('bid-program-manage');
    }
    // 代办跑腿 列表
    public function bid_service_list(): string
    {
        return view('bid-service-list');
    }
    // 代办跑腿 管理
    public function bid_service_manage(): string
    {
        return view('bid-service-manage');
    }
    // 投标排名
    public function bidder_rank(): string
    {
        return view('bidder-rank');
    }
    // 中标分析
    public function bid_program_analyse(): string
    {
        return view('bid-program-analyse');
    }

    /* 菜单 销售 */
    // 客户 列表
    public function customer_list(): string
    {
        return view('customer-list');
    }
    // 客户 管理
    public function customer_manage(): string
    {
        return view('customer-manage');
    }
    // 客户 选择
    public function customer_batch(): string
    {
        return view('customer-batch');
    }
    // 销售渠道 列表
    public function billfrom_list(): string
    {
        return view('billfrom-list');
    }
    // 销售渠道 管理
    public function billfrom_manage(): string
    {
        return view('billfrom-manage');
    }
    // 客户之声 列表
    public function service_list(): string
    {
        return view('service-list');
    }
    // 客户之声 管理
    public function service_manage(): string
    {
        return view('service-manage');
    }
    // 优惠申请 列表
    public function discountReq_list(): string
    {
        return view('discountReq-list');
    }
    // 优惠申请 管理
    public function discountReq_manage(): string
    {
        return view('discountReq-manage');
    }
    // 报价审批 列表
    public function quoteReq_list(): string
    {
        return view('quoteReq-list');
    }
    // 报价审批 管理
    public function quoteReq_manage(): string
    {
        return view('quoteReq-manage');
    }
    // 潜客登记 列表
    public function potentialCustomer_list(): string
    {
        return view('potentialCustomer-list');
    }
    // 潜客登记 管理
    public function potentialCustomer_manage(): string
    {
        return view('potentialCustomer-manage');
    }
    // 差评删改 列表
    public function reviewHold_list(): string
    {
        return view('reviewHold-list');
    }
    // 差评删改 管理
    public function reviewHold_manage(): string
    {
        return view('reviewHold-manage');
    }
    // 值班选择
    public function saler_duty_choose(): string
    {
        return view('saler-duty-choose');
    }
    // 值班记录
    public function saler_duty_note(): string
    {
        return view('saler-duty-note');
    }
    // 销售目标
    public function sales_target(): string
    {
        return view('sales-target');
    }
    // 销售目标 年度目标管理
    public function sales_target_year_manage(): string
    {
        return view('sales-target-year-manage');
    }
    // 销售目标 月度目标管理
    public function sales_target_month_manage(): string
    {
        return view('sales-target-month-manage');
    }
    // 销售订单 列表
    public function salesOrder_list(): string
    {
        return view('salesOrder-list');
    }
    // 销售订单 管理
    public function salesOrder_manage(): string
    {
        return view('salesOrder-manage');
    }
    // 特殊销售 列表
    public function salesContract_list(): string
    {
        return view('salesContract-list');
    }
    // 特殊销售 管理
    public function salesContract_manage(): string
    {
        return view('salesContract-manage');
    }
    // 网店月绩 列表
    public function onlineshopMp_list(): string
    {
        return view('onlineshopMp-list');
    }
    // 网店月绩 管理
    public function onlineshopMp_manage(): string
    {
        return view('onlineshopMp-manage');
    }
    // 销售明细
    public function sales_detail(): string
    {
        return view('sales-detail');
    }
    // 销量排行
    public function sales_rank(): string
    {
        return view('sales-rank');
    }
    // 同步异常
    public function salesOrder_spiderErrGoods(): string
    {
        return view('salesOrder-spiderErrGoods');
    }
    // 售价异常
    public function salesOrder_spiderErrPrice(): string
    {
        return view('salesOrder-spiderErrPrice');
    }
    // 售价修改录入
    public function shopPrices_manage(): string
    {
        return view('shopPrices-manage');
    }
    // 销售排名
    public function saler_rank(): string
    {
        return view('saler-rank');
    }
    // 销量分布
    public function sales_distribution(): string
    {
        return view('sales-distribution');
    }
    // 销量趋势
    public function sales_trend(): string
    {
        return view('sales-trend');
    }

    /* 菜单 采购 */
    // 商家 列表
    public function supplier_list(): string
    {
        return view('supplier-list');
    }
    // 商家 管理
    public function supplier_manage(): string
    {
        return view('supplier-manage');
    }
    // 商家 选择
    public function supplier_batch(): string
    {
        return view('supplier-batch');
    }
    // 采购申请 列表
    public function purchaseReq_list(): string
    {
        return view('purchaseReq-list');
    }
    // 采购申请 管理
    public function purchaseReq_manage(): string
    {
        return view('purchaseReq-manage');
    }
    // 特采申请 列表
    public function purchaseContractReq_list(): string
    {
        return view('purchaseContractReq-list');
    }
    // 特采申请 报价
    public function purchaseContractReq_quote(): string
    {
        return view('purchaseContractReq-quote');
    }
    // 特采申请 报价记录
    public function purchaseContractReq_quote_info(): string
    {
        return view('purchaseContractReq-quote-info');
    }
    // 采购订单 列表
    public function purchaseOrder_list(): string
    {
        return view('purchaseOrder-list');
    }
    // 采购订单 管理
    public function purchaseOrder_manage(): string
    {
        return view('purchaseOrder-manage');
    }
    // 特殊采购 列表
    public function purchaseContract_list(): string
    {
        return view('purchaseContract-list');
    }
    // 特殊采购 补买列表
    public function purchaseContractFill_list(): string
    {
        return view('purchaseContractFill-list');
    }
    // 特殊采购 管理
    public function purchaseContract_manage(): string
    {
        return view('purchaseContract-manage');
    }
    // 特殊采购 单独查看某特采产品付/退款记录
    public function purchaseContract_singleSearch_moneyInfo(): string
    {
        return view('purchaseContract-singleSearch-moneyInfo');
    }
    // 特殊采购 遗漏列表
    public function purchaseContractLost_list(): string
    {
        return view('purchaseContractLost-list');
    }
    // 特殊采购 改价通知列表
    public function purchaseContractModifyPurAmount_list(): string
    {
        return view('purchaseContractModifyPurAmount-list');
    }
    // 特殊采购 价差异常列表
    public function purchaseContractPriceAnormaly_list(): string
    {
        return view('purchaseContractPriceAnormaly-list');
    }
    // 采购质检
    public function purchase_qc(): string
    {
        return view('purchase-qc');
    }
    // 质检退货
    public function purchase_qc_back(): string
    {
        return view('purchase-qc-back');
    }
    // 采购明细
    public function purchase_detail(): string
    {
        return view('purchase-detail');
    }
    // 采购成本
    public function purchase_costSearch(): string
    {
        return view('purchase-costSearch');
    }
    // 采购排名
    public function purchaser_rank(): string
    {
        return view('purchaser-rank');
    }
    // 特采历史
    public function purchaseContract_his(): string
    {
        return view('purchaseContract-his');
    }
    // 采购建议 列表
    public function purchase_suggest_list(): string
    {
        return view('purchase-suggest-list');
    }
    // 采购建议 管理
    public function purchase_suggest_manage(): string
    {
        return view('purchase-suggest-manage');
    }
    // 回票排名
    public function purchase_invoice_back(): string
    {
        return view('purchase-invoice-back');
    }
    // 采购趋势
    public function purchase_trend(): string
    {
        return view('purchase-trend');
    }


    /* 菜单 生产 */
    // 生产分组 列表
    public function produceGroup_list(): string
    {
        return view('produceGroup-list');
    }
    // 生产分组 管理
    public function produceGroup_manage(): string
    {
        return view('produceGroup-manage');
    }
    // 配货申请 列表
    public function produceKittingReq_list(): string
    {
        return view('produceKittingReq-list');
    }
    // 配货申请 管理
    public function produceKittingReq_manage(): string
    {
        return view('produceKittingReq-manage');
    }
    // 配货任务 列表
    public function produceKittingTask_list(): string
    {
        return view('produceKittingTask-list');
    }
    // 配货任务 管理
    public function produceKittingTask_manage(): string
    {
        return view('produceKittingTask-manage');
    }
    // 配货领取 列表
    public function produceKittingTask_get_list(): string
    {
        return view('produceKittingTask-get-list');
    }
    // 配货交付 列表
    public function produceKittingTask_up_list(): string
    {
        return view('produceKittingTask-up-list');
    }
    // 加工项目 列表
    public function produceProcessingProject_list(): string
    {
        return view('produceProcessingProject-list');
    }
    // 加工项目 管理
    public function produceProcessingProject_manage(): string
    {
        return view('produceProcessingProject-manage');
    }
    // 加工项目 选择
    public function produceProcessingProject_batch(): string
    {
        return view('produceProcessingProject-batch');
    }
    // 加工项目 编辑历史
    public function produceProcessingProject_modify_his(): string
    {
        return view('produceProcessingProject-modify-his');
    }
    // 加工申请 列表
    public function produceProcessingReq_list(): string
    {
        return view('produceProcessingReq-list');
    }
    // 加工申请 管理
    public function produceProcessingReq_manage(): string
    {
        return view('produceProcessingReq-manage');
    }
    // 加工任务 列表
    public function produceProcessingTask_list(): string
    {
        return view('produceProcessingTask-list');
    }
    //加工任务 管理
    public function produceProcessingTask_manage(): string
    {
        return view('produceProcessingTask-manage');
    }
    // 加工领取 列表
    public function produceProcessingTask_get_list(): string
    {
        return view('produceProcessingTask-get-list');
    }
    // 加工交付 列表
    public function produceProcessingTask_up_list(): string
    {
        return view('produceProcessingTask-up-list');
    }

    /* 菜单 仓储 */
    // 仓库信息 列表
    public function storage_list(): string
    {
        return view('storage-list');
    }
    // 仓库信息 管理
    public function storage_manage(): string
    {
        return view('storage-manage');
    }
    // 仓库 选择
    public function storage_batch(): string
    {
        return view('storage-batch');
    }
    // 仓库分区+库位 管理
    public function location_manage(): string
    {
        return view('location-manage');
    }
    // 销售发货/退货 列表
    public function sales_list(): string
    {
        return view('sales-list');
    }
    // 销售发货/退货 管理
    public function sales_manage(): string
    {
        return view('sales-manage');
    }
    // 采购收货/退货 列表
    public function purchase_list(): string
    {
        return view('purchase-list');
    }
    // 采购收货/退货 管理
    public function purchase_manage(): string
    {
        return view('purchase-manage');
    }
    // 其他入库 列表
    public function inout_list_in(): string
    {
        return view('inout-list-in');
    }
    // 其他入库 管理
    public function inout_manage_in(): string
    {
        return view('inout-manage-in');
    }
    // 其他出库 列表
    public function inout_list_out(): string
    {
        return view('inout-list-out');
    }
    // 其他出库 管理
    public function inout_manage_out(): string
    {
        return view('inout-manage-out');
    }
    // 库存调拨 列表
    public function inout_list_tf(): string
    {
        return view('inout-list-tf');
    }
    // 库存调拨 管理
    public function inout_manage_tf(): string
    {
        return view('inout-manage-tf');
    }
    // 成本调整 列表
    public function inout_list_ca(): string
    {
        return view('inout-list-ca');
    }
    // 成本调整 管理
    public function inout_manage_ca(): string
    {
        return view('inout-manage-ca');
    }
    // 库存查询
    public function inventory_search(): string
    {
        return view('inventory-search');
    }
    // 库存流水
    public function inventory_history(): string
    {
        return view('inventory-history');
    }
    // 库存预警
    public function inventory_warning(): string
    {
        return view('inventory-warning');
    }
    // 库存盘点
    public function inventory_check(): string
    {
        return view('inventory-check');
    }
    // 库存呆滞
    public function inventory_dull(): string
    {
        return view('inventory-dull');
    }
    // 打包排名
    public function packer_rank(): string
    {
        return view('packer-rank');
    }

    /* 菜单 物流 */
    // 快递公司 列表
    public function deliver_company_list(): string
    {
        return view('deliver-company-list');
    }
    // 快递公司 管理
    public function deliver_company_manage(): string
    {
        return view('deliver-company-manage');
    }
    // 全国地区 列表
    public function areas_list(): string
    {
        return view('areas-list');
    }
    // 快递查询
    public function deliver_search(): string
    {
        return view('deliver-search');
    }
    // 快递估算
    public function deliver_predict(): string
    {
        return view('deliver-predict');
    }
    // 打印模板
    public function print_template(): string
    {
        return view('print-template');
    }
    // 打印记录
    public function print_log(): string
    {
        return view('print-log');
    }
    // 快递月结 列表
    public function deliver_list(): string
    {
        return view('deliver-list');
    }
    // 快递月结 管理
    public function deliver_manage(): string
    {
        return view('deliver-manage');
    }
    // 发包记录 列表
    public function deliver_bag_list(): string
    {
        return view('deliver-bag-list');
    }
    // 发包记录 管理
    public function deliver_bag_manage(): string
    {
        return view('deliver-bag-manage');
    }
    // 发包拦截 列表
    public function deliver_bag_off_list(): string
    {
        return view('deliver-bag-off-list');
    }
    // 发包拦截 管理
    public function deliver_bag_off_manage(): string
    {
        return view('deliver-bag-off-manage');
    }
    // 发包滞留
    public function deliver_bag_dull(): string
    {
        return view('deliver-bag-dull');
    }
    // 发包滞留 追加处理历史
    public function deliver_bag_dull_addhis(): string
    {
        return view('deliver-bag-dull-addhis');
    }
    // 收包记录
    public function deliver_bag_rec_list(): string
    {
        return view('deliver-bag-rec-list');
    }
    // 发包分析
    public function deliver_analyse(): string
    {
        return view('deliver-analyse');
    }
    // 发包趋势
    public function deliver_trend(): string
    {
        return view('deliver-trend');
    }
    // 发包分布
    public function deliver_distribution(): string
    {
        return view('deliver-distribution');
    }
    // 运费趋势
    public function deliver_fee_trend(): string
    {
        return view('deliver-fee-trend');
    }

    /* 菜单 人事 */
    // 应聘档案 列表
    public function applicant_list(): string
    {
        return view('applicant-list');
    }
    // 应聘档案 管理
    public function applicant_manage(): string
    {
        return view('applicant-manage');
    }
    // 员工档案 列表
    public function user_staff_list(): string
    {
        return view('user-staff-list');
    }
    // 员工档案 管理
    public function user_staff_manage(): string
    {
        return view('user-staff-manage');
    }
    // 全员速览
    public function user_staff_map(): string
    {
        return view('user-staff-map');
    }
    // 成长等级 列表
    public function user_level_list(): string
    {
        return view('user-level-list');
    }
    // 成长等级 管理
    public function user_level_manage(): string
    {
        return view('user-level-manage');
    }
    // 员工考评
    public function evaluate_staff(): string
    {
        return view('evaluate-staff');
    }
    // 考评项目 列表
    public function evaluation_list(): string
    {
        return view('evaluation-list');
    }
    // 考评项目 管理
    public function evaluation_manage(): string
    {
        return view('evaluation-manage');
    }
    // 考评项目 设定
    public function evaluation_oper(): string
    {
        return view('evaluation-oper');
    }
    // 出勤规划 列表
    public function attendance_plan_list(): string
    {
        return view('attendance-plan-list');
    }
    // 出勤规划 管理
    public function attendance_plan_manage(): string
    {
        return view('attendance-plan-manage');
    }
    // 考勤打卡 列表
    public function clockin_list(): string
    {
        return view('clockin-list');
    }
    // 考勤打卡 管理
    public function clockin_manage(): string
    {
        return view('clockin-manage');
    }
    // 补班申报 列表
    public function work_fill_list(): string
    {
        return view('work-hour-list');
    }
    // 补班申报 管理
    public function work_fill_manage(): string
    {
        return view('work-hour-manage');
    }
    // 加班申报 列表
    public function work_overtime_list(): string
    {
        return view('work-hour-list');
    }
    // 加班申报 管理
    public function work_overtime_manage(): string
    {
        return view('work-hour-manage');
    }
    // 计时申报 列表
    public function work_hour_list(): string
    {
        return view('work-hour-list');
    }
    // 计时申报 管理
    public function work_hour_manage(): string
    {
        return view('work-hour-manage');
    }
    // 工作记录 列表
    public function work_record_list(): string
    {
        return view('work-record-list');
    }
    // 工作记录 管理
    public function work_record_manage(): string
    {
        return view('work-record-manage');
    }
    // 出勤月表 列表
    public function attendance_list(): string
    {
        return view('attendance-list');
    }
    // 出勤月表 管理
    public function attendance_manage(): string
    {
        return view('attendance-manage');
    }
    // 考评月表 列表
    public function evaluate_list(): string
    {
        return view('evaluate-list');
    }
    // 考评月表 管理
    public function evaluate_manage(): string
    {
        return view('evaluate-manage');
    }
    // 工资月表 列表
    public function achievement_list(): string
    {
        return view('achievement-list');
    }
    // 工资月表 管理
    public function achievement_manage(): string
    {
        return view('achievement-manage');
    }
    // 出勤排名
    public function attendance_rank(): string
    {
        return view('attendance-rank');
    }
    // 荣誉奖章 列表
    public function user_medal_list(): string
    {
        return view('user-medal-list');
    }
    // 荣誉奖章 管理
    public function user_medal_manage(): string
    {
        return view('user-medal-manage');
    }

    /* 菜单 财务 */
    // 经营主体 列表
    public function company_list(): string
    {
        return view('company-list');
    }
    // 经营主体 管理
    public function company_manage(): string
    {
        return view('company-manage');
    }
    // 结算账户 列表
    public function company_account_list(): string
    {
        return view('company-account-list');
    }
    // 结算账户 管理
    public function company_account_manage(): string
    {
        return view('company-account-manage');
    }
    // 付款申请 列表
    public function payReq_list(): string
    {
        return view('payReq-list');
    }
    // 付款申请 管理
    public function payReq_manage(): string
    {
        return view('payReq-manage');
    }
    // 报销申请 列表
    public function expensesReq_list(): string
    {
        return view('expensesReq-list');
    }
    // 报销申请 管理
    public function expensesReq_manage(): string
    {
        return view('expensesReq-manage');
    }
    // 业务收款 列表
    public function receipt_list(): string
    {
        return view('receipt-list');
    }
    // 业务收款 管理
    public function receipt_manage(): string
    {
        return view('receipt-manage');
    }
    // 业务付款 列表
    public function payment_list(): string
    {
        return view('payment-list');
    }
    // 业务付款 管理
    public function payment_manage(): string
    {
        return view('payment-manage');
    }
    // 其他收入 列表
    public function income_list(): string
    {
        return view('income-list');
    }
    // 其他收入 管理
    public function income_manage(): string
    {
        return view('income-manage');
    }
    // 其他支出 列表
    public function expend_list(): string
    {
        return view('expend-list');
    }
    // 其他支出 管理
    public function expend_manage(): string
    {
        return view('expend-manage');
    }
    // 发票记录 列表
    public function invoice_list(): string
    {
        return view('invoice-list');
    }
    // 发票记录 管理
    public function invoice_manage(): string
    {
        return view('invoice-manage');
    }
    // 单据页 发票记录 管理
    public function invoice_bill_manage(): string
    {
        return view('invoice-bill-manage');
    }
    // 收票遗漏 列表
    public function invoice_miss_list(): string
    {
        return view('invoice-miss-list');
    }
    // 收票遗漏 白条入账
    public function invoice_miss_ignore(): string
    {
        return view('invoice-miss-ignore');
    }
    // 识票异常 列表
    public function invoice_err_list(): string
    {
        return view('invoice-err-list');
    }
    // 收支明细
    public function money_detail(): string
    {
        return view('money-detail');
    }
    // 纳税申报
    public function tax_return(): string
    {
        return view('tax-return');
    }
    // 税务分析
    public function tax_report(): string
    {
        return view('tax-report');
    }
    // 财务报表
    public function money_report(): string
    {
        return view('money-report');
    }

    /* 菜单 运营 */
    // 竞品监控 列表
    public function competitive_monitor_list(): string
    {
        return view('competitive-monitor-list');
    }
    // 竞品监控 管理
    public function competitive_monitor_manage(): string
    {
        return view('competitive-monitor-manage');
    }

    /* 菜单 设置 */
    // 系统参数
    public function system_parameter(): string
    {
        return view('system-parameter');
    }
    // 系统权限
    public function system_right(): string
    {
        return view('system-right');
    }
    // 系统任务
    public function system_task(): string
    {
        return view('system-task');
    }
    // 业绩系数
    public function exp_par(): string
    {
        return view('exp-par');
    }
    // 员工账号 列表
    public function user_list(): string
    {
        return view('user-list');
    }
    // 员工账号 分配权限
    public function user_setting(): string
    {
        return view('user-setting');
    }
    // 用户信息补录
    public function user_fill(): string
    {
        return view('user-fill');
    }
    // 用户权限
    public function user_rights(): string
    {
        return view('user-rights');
    }
    // 用户日志
    public function user_log(): string
    {
        return view('system-log');
    }
    // 绩效查询
    public function achievement_search(): string
    {
        return view('achievement-search');
    }
    // 用户考评
    public function user_evaluation(): string
    {
        return view('user-evaluation');
    }
    // 修改资料
    public function set_info(): string
    {
        return view('set-info');
    }
    // 修改头像
    public function set_avatar(): string
    {
        return view('set-avatar');
    }
    // 修改密码
    public function set_password(): string
    {
        return view('set-password');
    }
    // 网店账号
    public function shop_account_list(): string
    {
        return view('shop-account-list');
    }
    // 网店账号 管理
    public function shop_account_manage(): string
    {
        return view('shop-account-manage');
    }
    // 系统日志
    public function system_log(): string
    {
        return view('system-log');
    }
    // 系统日志
    public function clear_waste(): string
    {
        return view('clear-waste');
    }
}
