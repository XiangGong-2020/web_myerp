<?php

namespace App\Controllers\Base;
use App\Controllers\BaseController;

class Home extends BaseController
{
    // 首页 基础数据
    public function indexData()
    {
        $system = (array)json_decode($this->db->table('option')->select('optionValue')->where('optionName', 'system')->get()->getRowArray()['optionValue'], true);
        $system['endDate'] = date('Y-m-d');
        $expPar = (array)json_decode($this->db->table('option')->select('optionValue')->where('optionName', 'exp_par')->get()->getRowArray()['optionValue'], true);
        $user = $this->db->table('user')->where(['id' => $this->myerp['uId'], 'isDisable' => 0])->get()->getRowArray();
        $userLevel = array_column($this->db->table('user_level')->select('id,name,minExp,maxExp,hourlyWage')->where('isDelete', 0)->orderBy('minExp', 'ASC')->get()->getResultArray(), null, 'id');
        if ($user['roleId'] == 1) {
            $rights = array_column($this->db->table('right')->select('module')->where('isDelete', 0)->get()->getResultArray(), 'module');
        } else {
            $lever = strlen($user['lever']) > 0 ? $user['lever'] : '';
            if ($lever) {
                $rights = array_column($this->db->table('right')->select('module')->whereIn('id', explode(',', $user['lever']))->where(['isDelete' => 0, 'isDisable' => 0])->get()->getResultArray(), 'module');
            } else {
                $rights = [];
            }
        }
        $enableUserIds = array_column($this->db->table('user')->select('id')->where(['isDisable' => 0, 'isDelete' => 0])->get()->getResultArray(), 'id');
        $myerp = $this->myerp;
        $myerp['system'] = $system;
        $myerp['expPar'] = $expPar;
        $myerp['user'] = $user;
        $myerp['userLevel'] = $userLevel;
        $myerp['rights'] = $rights;
        $myerp['enableUserIds'] = $enableUserIds;
        if (!isset($myerp['noAlertCurLogin'])) {
            $myerp['noAlertCurLogin'] = [];
        }
        $myerp['transTypeName'] = [
            101101 => '采购订单',
            101102 => '采购直发订单',
            101111 => '特殊采购单',
            101201 => '销售订单',
            101211 => '特殊销售单',
            102101 => '采购收货单',
            102102 => '采购退货单',
            102201 => '销售发货单',
            102202 => '销售退货单',
            102401 => '其他入库单',
            102402 => '其他出库单',
            102403 => '库存调拨单',
            102404 => '成本调整单',
            103101 => '业务收款单',
            103102 => '业务付款单',
            103103 => '其他收入单',
            103104 => '其他支出单',
            109101 => '快递月结单'
        ];
        $this->session->set('myerp', $myerp);

        $data['clientIp'] = $this->request->getIPAddress();
        $data['locked'] = (strpos($data['clientIp'], '192.168.0.') === false && strpos($data['clientIp'], '127.0.0.1') === false) ? true : false;
        $data['uId'] = intval($user['id']);
        $data['uName'] = $user['name'];
        $data['user'] = $user;
        $data['userLevel'] = $userLevel;
        $data['enableUserIds'] = $enableUserIds;
        $data['rights'] = $rights;
        $data['expPar'] = $expPar;
        $data['system'] = $system;
        return $this->response->setJSON(['status' => 200, 'msg' => 'success', 'data' => $data]);
    }
    // 首页 左侧 导航通知数据 + 首页关注数据
    public function alertData()
    {
        // 库存预警
        $data['invWarningNum'] = $this->db->table('goods')
            ->whereNotIn('categoryId', [263,435])->where('isDisable <', 2)->groupStart()->where('currentQty > highQty', null, false)->orWhere('currentQty < lowQty', null, false)->groupEnd()->where('isDelete', 0)->countAllResults();
        // 任务清单未完成数
        if (in_array('TASK_QUERY', $this->myerp['rights'])) {
            $data['taskNotice'] = $this->db->table('task')->where('status', 0)->groupStart()->where('leader', $this->myerp['uName'])->orWhere('FIND_IN_SET(' . $this->db->escape($this->myerp['uName']) . ', other) >', 0, false)->groupEnd()->where('isDelete', 0)->countAllResults();
        }
        // 工作目标未完成数
        if (in_array('PLAN_QUERY', $this->myerp['rights'])) {
            $data['planNotice'] = $this->db->table('plan')->where(['isDisable' => 0, 'uId' => $this->myerp['uId'], 'isDelete' => 0])->countAllResults();
        }
        // 工作转交未完成数
        if (in_array('DUTY_QUERY', $this->myerp['rights'])) {
            $department = $this->myerp['user']['department'] ?? '行政部';
            $data['dutyNotice'] = $this->db->table('duty')->groupStart()->where('beginName', $this->myerp['uName'])->orWhere('FIND_IN_SET(' . $this->db->escape($this->myerp['uName']) . ', targetName) >', 0, false)->orWhere('FIND_IN_SET(' . $this->db->escape($department) . ', targetName) >', 0, false)->groupEnd()->where(['isDisable' => 0, 'isDelete' => 0])->countAllResults();
        }
        // 管理计划未完成数
        if (in_array('MANAGE_PLAN_QUERY', $this->myerp['rights'])) {
            $data['managePlanNotice'] = $this->db->table('manage_plan')->where(['isDisable' => 0, 'uId' => $this->myerp['uId'], 'isDelete' => 0])->countAllResults();
        }
        // 私人备忘提醒数
        if (in_array('SYS_DEFAULT', $this->myerp['rights'])) {
            $data['noteNotice'] = $this->db->table('note')->where(['uId' => $this->myerp['uId'], 'isDisable' => 0, 'isDelete' => 0])->countAllResults();
        }
        // 采购申请未完成数
        if (in_array('PURREQ_ADVANCE', $this->myerp['rights'])) {
            $data['purReqNotice'] = $this->db->table('pur_req')->orGroupStart()->where('checkId', 0)->orWhere('purchaseId', 0)->groupEnd()->where(['description' => '', 'isDelete' => 0])->countAllResults();
        }
        // 特采申请未完成数
        if (in_array('PURREQ_ADVANCE', $this->myerp['rights'])) {
            $data['purContractReqNotice'] = $this->db->table('bill_contract')->where(['isReq' => 1, 'isDelete' => 0])->countAllResults();
        }
        // 优惠申请未完成数
        if (in_array('SALE_MANAGER', $this->myerp['rights'])) {
            $data['discountReqNotice'] = $this->db->table('discount_req')->where(['checkId' => 0, 'description' => '', 'isDelete' => 0])->countAllResults();
        }
        // 报价审批未完成数
        if (in_array('SALE_MANAGER', $this->myerp['rights'])) {
            $data['quoteReqNotice'] = $this->db->table('quote_req')->where(['checkId' => 0, 'description' => '', 'isDelete' => 0])->countAllResults();
        }
        // 配货申请未完成数
        if (in_array('PROREQ_ADVANCE', $this->myerp['rights'])) {
            $data['produceKittingReqNotice'] = $this->db->table('pro_kitting_req')->groupStart()->where('checkId', 0)->orWhere('produceId', 0)->groupEnd()->where(['description' =>'', 'isDelete' => 0])->countAllResults();
        }
        // 付款申请未完成数
        if (in_array('PAYREQ_ADVANCE', $this->myerp['rights'])) {
            $data['payReqNotice'] = $this->db->table('pay_req')->groupStart()->where('checkId', 0)->orWhere('moneyStatus <', 2)->groupEnd()->where(['description' => '', 'type' => 1, 'isDelete' => 0])->countAllResults();
        }
        // 报销申请未完成数
        if (in_array('EXPENSESREQ_ADVANCE', $this->myerp['rights'])) {
            $data['expensesReqNotice'] = $this->db->table('pay_req')->groupStart()->where('checkId', 0)->orWhere('moneyStatus <', 2)->groupEnd()->where(['description' =>'', 'type' => 2, 'isDelete' => 0])->countAllResults();
        }
        // 定制申请未完成数
        if (in_array('GOODSDEVREQ_ADVANCE', $this->myerp['rights'])) {
            $data['goodsDevReqNotice'] = $this->db->table('goods_dev_req')->where('id >', 8076)->groupStart()->where('checkId', 0)->orWhere('srcTaskId', 0)->groupEnd()->where(['description' => '', 'isDelete' => 0])->countAllResults();
        }
        // 下载申请未完成数
        if (in_array('GOODSFILEONE_CHECK', $this->myerp['rights']) || in_array('GOODSFILETWO_CHECK', $this->myerp['rights']) || in_array('GOODSFILETHREE_CHECK', $this->myerp['rights']) || in_array('GOODSFILEFOUR_CHECK', $this->myerp['rights']) || in_array('GOODSFILEFIVE_CHECK', $this->myerp['rights'])) {
            $data['goodsFileReqNotice'] = $this->db->table('goods_file_req')->where(['checkId' => 0, 'description' => '', 'isDelete' => 0])->countAllResults();
        }
        // 采购订单未完成数
        if (in_array('PO_ADD', $this->myerp['rights'])) {
            $data['poNotice'] = $this->db->table('bill_order')->where('billType', 'PUR')->groupStart()->where('checkId', 0)->orWhere('moneyStatus1 <', 2)->orWhere('moneyStatus2', 1)->orWhere('goodsStatus1 <', 2)->orWhere('goodsStatus2', 1)->groupEnd()->where(['isCancel' => 0, 'isDelete' => 0])->countAllResults();
        }
        // 特殊采购未完成数
        if (in_array('PC_ADD', $this->myerp['rights'])) {
            $data['pcNotice'] = $this->db->table('bill_contract')->where(['billType' => 'PUR', 'isReq !=' => 1, 'isFill' => 0])->groupStart()->where('checkId', 0)->orWhere('moneyStatus1 <', 2)->orWhere('moneyStatus2', 1)->groupEnd()->where('isDelete', 0)->countAllResults();
        }
        // 特采补买未完成数
        if (in_array('PC_ADD', $this->myerp['rights'])) {
            $data['pcFillNotice'] = $this->db->table('bill_contract')->where(['billType' => 'PUR', 'isFill' => 1])->groupStart()->where('checkId', 0)->orWhere('moneyStatus1 <', 2)->orWhere('moneyStatus2', 1)->groupEnd()->where('isDelete', 0)->countAllResults();
        }
        // 特采遗漏未完成数
        if (in_array('PC_ADD', $this->myerp['rights'])) {
            $data['pcLostNotice'] = $this->db->table('bill_contract_info')->where(['transType' => 101111, 'isReq !=' => 1, 'isPurchased <' => 1, 'isDraft' => 0, 'isDelete' => 0, 'endDate <=' => date('Y-m-d', strtotime('+3 day'))])->where('paidAmount < amount', null, false)->countAllResults();
        }
        // 改价通知未完成数
        if (in_array('PC_ADD', $this->myerp['rights'])) {
            $data['pcModifyPurAmountNotice'] = $this->db->table('bill_contract_info_modify_pur_amount')->where(['isDisable' => 0, 'isDelete' => 0])->countAllResults();
        }
        // 采购建议未完成数
        if (in_array('PC_ADD', $this->myerp['rights'])) {
            $data['purSuggestNotice'] = $this->db->table('pur_suggest')->where(['purId' => $this->myerp['uId'], 'status' => 0, 'isDelete' => 0])->countAllResults();
        }
        // 采购质检未完成数
        if (in_array('PURQC_ADVANCE', $this->myerp['rights'])) {
            $data['purQCNotice'] = $this->db->table('bill_inventory_log')->where(['transType' => 102101, 'QCStatus' => 0, 'isDelete' => 0])->countAllResults();
        }
        // 质检打回未完成数
        if (in_array('PURQCBACK_ADVANCE', $this->myerp['rights'])) {
            $data['purQCBackNotice'] = $this->db->table('bill_inventory_log')->where(['transType' => 102101, 'QCStatus' => 1, 'backStatus' => 0, 'isDelete' => 0])->countAllResults();
        }
        // 销售订单未完成数
        if (in_array('SO_ADD', $this->myerp['rights'])) {
            $data['soNotice'] = $this->db->table('bill_order')->where(['billType' => 'SALE', 'srcOcId' => 0])->groupStart()->where('checkId', 0)->orWhere('moneyStatus1 <', 2)->orWhere('moneyStatus2', 1)->orWhere('goodsStatus1 <',  2)->orWhere('goodsStatus2', 1)->groupEnd()->where(['isCancel' => 0, 'isDelete' => 0])->countAllResults();
        }
        // 特殊销售未完成数
        if (in_array('SC_ADD', $this->myerp['rights'])) {
            $data['scNotice'] = $this->db->table('bill_contract')->where(['billType' => 'SALE', 'redoSoType' => 0])->groupStart()->where('checkId', 0)->orWhere('moneyStatus1 <', 2)->orWhere('moneyStatus2', 1)->orWhere('goodsStatus1 <', 1)->groupEnd()->where('isDelete', 0)->countAllResults();
        }
        // 业务收款未完成数
        if (in_array('RECEIPT_ADD', $this->myerp['rights'])) {
            $data['receiptNotice'] = $this->db->table('bill_money')->where('transType', 103101)->groupStart()->where('checkId', 0)->orWhere('moneyStatus <', 2)->groupEnd()->where('isDelete', 0)->countAllResults();
        }
        // 业务付款未完成数
        if (in_array('PAYMENT_ADD', $this->myerp['rights'])) {
            $data['paymentNotice'] = $this->db->table('bill_money')->where('transType', 103102)->groupStart()->where('checkId', 0)->orWhere('moneyStatus <', 2)->groupEnd()->where('isDelete', 0)->countAllResults();
        }
        // 其他收入未完成数
        if (in_array('INCOME_ADD', $this->myerp['rights'])) {
            $data['incomeNotice'] = $this->db->table('bill_money')->where(['transType' => 103103, 'checkId' => 0, 'isDelete' => 0])->countAllResults();
        }
        // 其他支出未完成数
        if (in_array('EXPEND_ADD', $this->myerp['rights'])) {
            $data['expendNotice'] = $this->db->table('bill_money')->where(['transType' => 103104, 'checkId' => 0, 'isDelete' => 0])->countAllResults();
        }
        // 快递月结单未完成数
        if (in_array('DELIVER_ADD', $this->myerp['rights'])) {
            $data['deliverNotice'] = $this->db->table('bill_deliver')->groupStart()->where('checkId', 0)->orWhere('moneyStatus1 <', 2)->orWhere('moneyStatus2', 1)->groupEnd()->where('isDelete', 0)->countAllResults();
        }
        // 收票遗漏未完成数
        $data['invoiceMissNotice'] = 0;
        if (in_array('PO_UPDATE', $this->myerp['rights'])) {
            $builder = $this->db->table('bill_order')->where('billType', 'PUR')->where('invoiceAmount < amount', null, false)->where(['isIgnoreInvoice <' => 2, 'isDraft' => 0, 'isCancel' => 0, 'isDelete' => 0]);
            if (!$this->myservice->verifyRight('SYS_ADMIN')) {
                $builder->where('uName', $this->myerp['uName']);
            }
            $data['invoiceMissNotice'] += $builder->countAllResults();
        }
        if (in_array('PC_UPDATE', $this->myerp['rights'])) {
            $builder = $this->db->table('bill_contract_info')->where(['billType' => 'PUR', 'paidAmount >' => 0, 'isRecInvoice' => 0, 'isIgnoreInvoice <' => 2, 'isDraft' => 0, 'isDelete' => 0]);
            if (!$this->myservice->verifyRight('SYS_ADMIN')) {
                $builder->where('uName', $this->myerp['uName']);
            }
            $data['invoiceMissNotice'] += $builder->countAllResults();
        }
        if (in_array('EXPEND_UPDATE', $this->myerp['rights'])) {
            $builder = $this->db->table('bill_money')->where('billType', 'EXP')->where('invoiceAmount < cleared', null, false)->where(['isIgnoreInvoice <' => 2, 'isDelete' => 0]);
            if (!$this->myservice->verifyRight('SYS_ADMIN')) {
                $builder->where('uName', $this->myerp['uName']);
            }
            $data['invoiceMissNotice'] += $builder->countAllResults();
        }
        if (in_array('DELIVER_UPDATE', $this->myerp['rights'])) {
            $builder = $this->db->table('bill_deliver')->where('invoiceAmount < amount', null, false)->where(['isIgnoreInvoice <' => 2, 'isDraft' => 0, 'isDelete' => 0]);
            if (!$this->myservice->verifyRight('SYS_ADMIN')) {
                $builder->where('uName', $this->myerp['uName']);
            }
            $data['invoiceMissNotice'] += $builder->countAllResults();
        }
        // 识票异常未完成数
        if (in_array('FINANCE_MANAGER', $this->myerp['rights'])) {
            $data['invoiceErrNotice'] = $this->db->table('invoice_err')->where(['status' => 0, 'isDelete' => 0])->countAllResults();
        }
        // 客户之声未完成数
        if (in_array('SERVICE_QUERY', $this->myerp['rights'])) {
            $data['serviceNotice'] = $this->db->table('service')->where(['isDisable' => 0, 'isDelete' => 0])->countAllResults();
        }
        // 差评删改未完成数
        if (in_array('SERVICE_QUERY', $this->myerp['rights'])) {
            $data['reviewHoldNotice'] = $this->db->table('review_hold')->where(['isDisable' => 0, 'isDelete' => 0])->countAllResults();
        }
        // 投标项目未开标数
        if (in_array('BIDPROGRAM_QUERY', $this->myerp['rights'])) {
            $data['bidProgramNotice'] = $this->db->table('bid_program')->where(['status' => 0, 'isDelete' => 0])->countAllResults();
        }
        // 发包滞留未完成数
        if (in_array('SO_ADD', $this->myerp['rights'])) {
            $data['deliverDullNotice'] = $this->db->table('deliver_bag')->where(['scanName' => '', 'scanTime' => '0000-00-00 00:00:00', 'printTime <=' => date('Y-m-d', strtotime('-1 day')) . ' 11:59:59', 'isDelete' => 0])->countAllResults();
        }
        // 产品开发未完成数
        if (in_array('GOODSTASK_UPDATE', $this->myerp['rights'])) {
<<<<<<< HEAD
            $data['goodsTaskNotice'] = $this->db->table('goods_task')->where('FIND_IN_SET("5", status) =', 0, false)->where('isDelete', 0)->countAllResults();
=======
            $data['goodsTaskNotice'] = $this->db->table('goods_task')->where('!FIND_IN_SET(5, status) >', 0, false)->where('isDelete', 0)->countAllResults();
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
        }
        // 配货任务未完成数
        if (in_array('PROTASK_ADVANCE', $this->myerp['rights'])) {
            $data['proKittingTaskNotice'] = $this->db->table('pro_kitting_task')->where(['taskStatus <' => 3, 'isDelete' => 0])->countAllResults();
        }
        // 配货交付未完成数
        if (in_array('PRO_CHECK_IN_STORAGE', $this->myerp['rights'])) {
            $data['proKittingTaskUpNotice'] = $this->db->table('pro_kitting_task_up')->where(['doneStatus <' => 2, 'isDelete' => 0])->countAllResults();
        }
        // 加工交付未完成数
        if (in_array('PRO_CHECK_IN_STORAGE', $this->myerp['rights'])) {
            $data['proProcessingTaskUpNotice'] = $this->db->table('pro_processing_task_up')->where(['doneStatus <' => 2, 'isDelete' => 0])->countAllResults();
        }
        // 同步异常未完成数
        if (in_array('SA_SPIDER_ERR_ADVANCE', $this->myerp['rights'])) {
            $data['saleOrderSpiderErrGoodsNotice'] = $this->db->table('spider_err_goods')->where(['status' => 0, 'isDelete' => 0])->countAllResults();
        }
        // 售价异常未完成数
        if (in_array('SA_SPIDER_ERR_ADVANCE', $this->myerp['rights'])) {
            $data['saleOrderSpiderErrPriceNotice'] = $this->db->table('spider_err_price')->where(['status' => 0, 'isDelete' => 0])->countAllResults();
        }
        // 竞品监控异常数
        if (in_array('COMPETITIVE_MONITOR_QUERY', $this->myerp['rights'])) {
            $data['competitiveMonitorNotice'] = $this->db->table('competitive_monitor')->where(['status >' =>  0, 'isDelete' => 0])->countAllResults();
        }
        // 考勤打卡未完成数
        if (in_array('CLOCKIN_CHECK', $this->myerp['rights'])) {
            $data['clockinNotice'] = $this->db->table('clockin')->groupStart()->where('FIND_IN_SET("请假", marks) >', 0, false)->orWhere('FIND_IN_SET("调休", marks) >', 0, false)->orWhere('FIND_IN_SET("补休", marks) >', 0, false)->orWhere('FIND_IN_SET("外勤", marks) >', 0, false)->groupEnd()->where(['isCheck' => 0, 'isDelete' => 0])->countAllResults();
        }
        // 补班申报未完成数
        if (in_array('WORKHOUR_ADVANCE', $this->myerp['rights'])) {
            $data['workFillNotice'] = $this->db->table('work_hour')->where(['type' => 0, 'isCheck' => 0, 'isDelete' => 0])->countAllResults();
        }
        // 加班申报未完成数
        if (in_array('WORKOVERTIME_ADVANCE', $this->myerp['rights'])) {
            $data['workOvertimeNotice'] = $this->db->table('work_hour')->where(['type' => 1, 'isCheck' => 0, 'isDelete' => 0])->countAllResults();
        }
        // 计时申报未完成数
        if (in_array('WORKHOUR_ADVANCE', $this->myerp['rights'])) {
            $data['workHourNotice'] = $this->db->table('work_hour')->where(['type' => 2, 'isCheck' => 0, 'isDelete' => 0])->countAllResults();
        }
        return $this->response->setJSON(['status' => 200, 'msg' => 'success', 'data' => $data]);
    }
    // 首页 右下角 紧急通知数据
    public function urgentData()
    {
        $data = [];
        if (is_countable($this->myerp['rights']) && count($this->myerp['rights']) > 0) {
            // 管理员相关
            if (in_array('SYS_ADMIN', $this->myerp['rights'])) {
                // 考勤审批
                $clockin = $this->db->table('clockin')->groupStart()->where('FIND_IN_SET("请假", marks) >', 0, false)->orWhere('FIND_IN_SET("调休", marks) >', 0, false)->orWhere('FIND_IN_SET("补休", marks) >', 0, false)->orWhere('FIND_IN_SET("外勤", marks) >', 0, false)->groupEnd()->where(['isCheck' => 0, 'isDelete' => 0])->countAllResults();
                if ($clockin > 0) {
                    $data['clockin'] = ['tabid' => 'clockin', 'uri' => '?isCheck=0', 'alert' => '您收到 ' . $clockin . ' 个请假/调休/补休/外勤申请'];
                }
                // 补班审批
                $workFill = $this->db->table('work_hour')->where(['type' => 0, 'isCheck' => 0, 'isDelete' => 0])->countAllResults();
                if ($workFill > 0) {
                    $data['workFill'] = ['tabid' => 'workFill', 'uri' => '&isCheck=0', 'alert' => '您收到 ' . $workFill . ' 个补班申报'];
                }
                // 加班审批
                $workOverTime = $this->db->table('work_hour')->where(['type' => 1, 'isCheck' => 0, 'isDelete' => 0])->countAllResults();
                if ($workOverTime > 0) {
                    $data['workOverTime'] = ['tabid' => 'workOvertime', 'uri' => '&isCheck=0', 'alert' => '您收到 ' . $workOverTime . ' 个加班申报'];
                }
                // 计时审批
                $workHour = $this->db->table('work_hour')->where(['type' => 2, 'isCheck' => 0, 'isDelete' => 0])->countAllResults();
                if ($workHour > 0) {
                    $data['workHour'] = ['tabid' => 'workHour', 'uri' => '&isCheck=0', 'alert' => '您收到 ' . $workHour . ' 个计时申报'];
                }
                // 白条入账申请
                $ignoreInvoiceReq = 0;
                $ignoreInvoiceReq += $this->db->table('bill_order')->where('billType', 'PUR')->where('invoiceAmount < amount', null, false)->where(['isIgnoreInvoice' => 0, 'ignoreInvoiceReason !=' => '', 'isDraft' => 0, 'isCancel' => 0, 'isDelete' => 0])->countAllResults();
                $ignoreInvoiceReq += $this->db->table('bill_contract_info')->where(['billType' => 'PUR', 'paidAmount >' => 0, 'isRecInvoice' => 0, 'isIgnoreInvoice' => 0, 'ignoreInvoiceReason !=' => '', 'isDraft' => 0, 'isDelete' => 0])->countAllResults();
                $ignoreInvoiceReq += $this->db->table('bill_money')->where('billType', 'EXP')->where('invoiceAmount < cleared', null, false)->where(['isIgnoreInvoice' => 0, 'ignoreInvoiceReason !=' => '', 'isDelete' => 0])->countAllResults();
                $ignoreInvoiceReq += $this->db->table('bill_deliver')->where('invoiceAmount < amount', null, false)->where(['isIgnoreInvoice' => 0, 'ignoreInvoiceReason !=' => '', 'isDraft' => 0, 'isDelete' => 0])->countAllResults();
                if ($ignoreInvoiceReq > 0) {
                    $data['ignoreInvoiceReq'] = ['tabid' => 'invoiceMiss', 'uri' => '?', 'alert' => '您收到 ' . $ignoreInvoiceReq . ' 个白条入账申请'];
                }
            }
            //销售订单30天仍未结清款项
            if(in_array($this->myerp['user']['position'], ['总裁', '副总裁', '市场总监'])) {
                $billOrderSaleUnclear30aAll = $this->db->table('bill_order')->where(['billDate <=' => date('Y-m-d', strtotime('-30 day')), 'billType' => 'SALE', 'moneyStatus1 <' => 2, 'isCancel' => 0, 'isDraft' => 0, 'isDelete' => 0])->countAllResults();
                if ($billOrderSaleUnclear30aAll > 0) {
                    $data['billOrderSaleUnclear30aAll'] = ['class' => 'hover-purple', 'tabid' => 'salesOrder', 'uri' => '?unClear=2&endDate=' . date('Y-m-d', strtotime('-30 day')), 'alert' => '您辖下有 ' . $billOrderSaleUnclear30aAll . ' 笔销售订单已超过 30 天仍未结清款项'];
                }
            }
            //特殊销售单60天仍未收取任何款项、或365天仍未结清款项
            if(in_array($this->myerp['user']['position'], ['总裁', '副总裁', '市场总监'])) {
                $billContractSaleUnclear60aAll = $this->db->table('bill_contract')->where(['billDate <=' => date('Y-m-d', strtotime('-60 day')), 'billType' => 'SALE', 'moneyStatus1' => 0, 'isDraft' => 0, 'isDelete' => 0])->countAllResults();
                if ($billContractSaleUnclear60aAll > 0) {
                    $data['billContractSaleUnclear60aAll'] = ['class' => 'hover-purple', 'tabid' => 'salesContract', 'uri' => '?unClear=1&endDate=' . date('Y-m-d', strtotime('-60 day')), 'alert' => '您辖下有 ' . $billContractSaleUnclear60aAll . ' 笔特殊销售单已超过 60 天仍未收取任何款项'];
                }
                $billContractSaleUnclear365aAll = $this->db->table('bill_contract')->where(['billDate <=' => date('Y-m-d', strtotime('-365 day')), 'billType' => 'SALE', 'moneyStatus1 <' => 2, 'isDraft' => 0, 'isDelete' => 0])->countAllResults();
                if ($billContractSaleUnclear365aAll > 0) {
                    $data['billContractSaleUnclear365aAll'] = ['class' => 'hover-purple', 'tabid' => 'salesContract', 'uri' => '?unClear=2&endDate=' . date('Y-m-d', strtotime('-365 day')), 'alert' => '您辖下有 ' . $billContractSaleUnclear365aAll . ' 笔特殊销售单已超过 365 天仍未结清款项'];
                }
            }
            //客户之声7天未解决
            if (in_array($this->myerp['user']['position'], ['总裁', '副总裁', '市场总监'])) {
                $serviceUndoneAll = $this->db->table('service')->where(['isDisable' => 0, 'beginDate <=' => date('Y-m-d', strtotime('-7 day')) . ' 23:59:59', 'isDelete' => 0])->countAllResults();
                if ($serviceUndoneAll > 0) {
                    $data['serviceUndoneAll'] = ['class' => 'hover-purple', 'tabid' => 'service', 'uri' => '?isDisable=0&endDate=' . date('Y-m-d', strtotime('-7 day')), 'alert' => '您辖下有 ' . $serviceUndoneAll . ' 个客户之声已超过 7 天仍未解决'];
                }
            }
            //采购订单20天仍未收齐货物
            if(in_array($this->myerp['user']['position'], ['总裁', '副总裁', '采购经理'])) {
                $billOrderPurUnrec20aAll = $this->db->table('bill_order')->where(['billDate <=' => date('Y-m-d', strtotime('-20 day')), 'billType' => 'PUR', 'goodsStatus1 <' => 2, 'isCancel' => 0, 'isDraft' => 0, 'isDelete' => 0])->countAllResults();
                if ($billOrderPurUnrec20aAll > 0) {
                    $data['billOrderPurUnrec20aAll'] = ['class' => 'hover-purple', 'tabid' => 'purchaseOrder', 'uri' => '?unDone=1&endDate=' . date('Y-m-d', strtotime('-20 day')), 'alert' => '您辖下有 ' . $billOrderPurUnrec20aAll . ' 笔采购订单已超过 20 天仍未收齐货物'];
                }
            }
            // 任务清单
            if (in_array('TASK_QUERY', $this->myerp['rights'])) {
                $task = $this->db->table('task')->groupStart()->where('leader', $this->myerp['uName'])->orWhere('FIND_IN_SET(' . $this->db->escape($this->myerp['uName']) . ', other) >', 0, false)->groupEnd()->where(['status' => 0, 'limitTime <' => date('Y-m-d H:i:s'), 'isDelete' => 0])->countAllResults();
                if ($task > 0) {
                    $data['task'] = ['class' => 'hover-purple', 'tabid' => 'task', 'uri' => '?unDone=1', 'alert' => '您有 ' . $task . ' 个任务清单已超时'];
                }
            }
            // 工作转交
            if (in_array('DUTY_QUERY', $this->myerp['rights'])) {
                $department = $this->myerp['user']['department'] ?? '';
                $duty = $this->db->table('duty')->groupStart()->where('FIND_IN_SET(' . $this->db->escape($this->myerp['uName']) . ', targetName) >', 0, false)->orWhere('FIND_IN_SET(' . $this->db->escape($department) . ', targetName) >', 0, false)->groupEnd()->where(['isDisable' => 0, 'isDelete' => 0])->countAllResults();
                if ($duty > 0) {
                    $data['duty'] = ['tabid' => 'duty', 'uri' => '?unDone=1', 'alert' => '您收到 ' . $duty . ' 个工作转交'];
                }
            }
            // 网店月绩
            if (in_array('ONLINESHOPMP_ADVANCE', $this->myerp['rights'])) {
                if (intval(date('d')) > 10) {
                    if ($this->db->table('bill_onlineshopmp')->where(['billNo' => 'WD' . date('Ym', strtotime(date('Y-m') . ' -1 month')), 'isDraft' => 0, 'isDelete' => 0])->countAllResults() == 0) {
                        $data['onlineshopmp'] = ['tabid' => 'onlineshopMp', 'uri' => '?', 'alert' => '请尽快出台上月正式网店月绩表'];
                    }
                }
            }
            // 出勤月表
            if (in_array('ATTENDANCE_ADVANCE', $this->myerp['rights'])) {
                if (intval(date('d')) > 5) {
                    if ($this->db->table('bill_attendance')->where(['billNo' => 'CQ' . date('Ym', strtotime(date('Y-m') . ' -1 month')), 'isDraft' => 0, 'isDelete' => 0])->countAllResults() == 0) {
                        $data['attendance'] = ['tabid' => 'attendance', 'uri' => '?', 'alert' => '请尽快出台上月正式出勤月表'];
                    }
                }
            }
            // 考评打分
            if (in_array($this->myerp['user']['position'], ['运营总监', '市场总监', '技术总监', '行政总监', '采购经理'])) {
                if ($this->db->table('bill_evaluate_info')->where(['billNo' => 'KP' . date('Ym', strtotime(date('Y-m') . ' -1 month')), 'lastName' => $this->myerp['uName'], 'isDelete' => 0])->countAllResults() == 0) {
                    $data['evaluate'] = ['tabid' => 'userStaff', 'uri' => '?', 'alert' => '请尽快为下属员工进行上月考评打分'];
                }
            }
            // 网店账号
            if (in_array('SHOPACCOUNT_UPDATE', $this->myerp['rights'])) {
                $shopAccountUsers = $this->db->table('shop_account')->select('uId, uName')->where('isDelete', 0)->get()->getResultArray();
                if ($shopAccountUsers) {
                    $shopAccountErrNum = 0;
                    foreach ($shopAccountUsers as $shopAccountUser) {
                        if (!in_array($shopAccountUser['uId'], $this->myerp['enableUserIds'])) {
                            $shopAccountErrNum += 1;
                        }
                    }
                    if ($shopAccountErrNum > 0) {
                        $data['shopAccount'] = ['tabid' => 'shopAccount', 'uri' => '?', 'alert' => '您收到 ' . $shopAccountErrNum . ' 个网店账号当前拥有人离职异常'];
                    }
                }
            }
            // 采购申请
            if (in_array('PURREQ_ADVANCE', $this->myerp['rights'])) {
                $purReq = $this->db->table('pur_req')->groupStart()->where('checkId', 0)->orWhere('purchaseId', 0)->groupEnd()->where(['description' => '', 'isDelete' => 0])->countAllResults();
                if ($purReq > 0) {
                    $data['purReq'] = ['tabid' => 'purchaseReq', 'uri' => '?unDone=1', 'alert' => '您收到 ' . $purReq . ' 个采购申请'];
                }
            }
            // 特采遗漏
            if (in_array('PC_ADD', $this->myerp['rights'])) {
                $purContractLostRemain3day = $this->db->table('bill_contract_info')->where(['transType' => 101111, 'isReq !=' => 1, 'isPurchased <' => 1])->where('paidAmount < amount', null, false)->where(['isDraft' => 0, 'isDelete' => 0, 'endDate >= ' => date('Y-m-d'), 'endDate <=' => date('Y-m-d', strtotime('+3 day')), 'uId' => $this->myerp['uId']])->countAllResults();
                if ($purContractLostRemain3day > 0) {
                    $data['purContractLostRemain3day'] = ['tabid' => 'purchaseContractLost', 'uri' => '?uId=' . $this->myerp['uId'], 'alert' => '您有 ' . $purContractLostRemain3day . ' 笔特采3天内将超期'];
                }
                $purContractLostRemain0day = $this->db->table('bill_contract_info')->where(['transType' => 101111, 'isReq !=' => 1, 'isPurchased <' => 1])->where('paidAmount < amount', null, false)->where(['isDraft' => 0, 'isDelete' => 0, 'endDate <=' => date('Y-m-d', strtotime('-1 day')), 'uId' => $this->myerp['uId']])->countAllResults();
                if ($purContractLostRemain0day > 0) {
                    $data['purContractLostRemain0day'] = ['class' => 'hover-purple', 'tabid' => 'purchaseContractLost', 'uri' => '?uId=' . $this->myerp['uId'], 'alert' => '您有 ' . $purContractLostRemain0day . ' 笔特采已超期'];
                }
                $purContractLostOver10day = $this->db->table('bill_contract_info')->where(['transType' => 101111, 'isReq !=' => 1, 'isPurchased <' => 1])->where('paidAmount < amount', null, false)->where(['isDraft' => 0, 'isDelete' => 0, 'endDate <=' => date('Y-m-d', strtotime('-10 day')), 'uId' => $this->myerp['uId']])->countAllResults();
                if ($purContractLostOver10day > 0) {
                    $data['purContractLostOver10day'] = ['class' => 'hover-purple', 'tabid' => 'purchaseContractLost', 'uri' => '?uId=' . $this->myerp['uId'], 'alert' => '您有 ' . $purContractLostRemain0day . ' 笔特采已超期10天以上'];
                }
            }
            // 改价通知
            if (in_array('PC_QUERY', $this->myerp['rights'])) {
                if ($this->myerp['user']['position'] == '财务会计') {
                    $purContractModifyPurAmount = $this->db->table('bill_contract_info_modify_pur_amount')->where(['isDisable' => 0, 'isDelete' => 0])->countAllResults();
                } elseif ($this->myerp['user']['position'] == '采购经理') {
                    $leavedUser = implode(',', array_column($this->db->table('user')->select('name')->where(['department' => '采购部', 'isDisable' => 1])->get()->getResultArray(), 'name'));
                    $purContractModifyPurAmount = $this->db->table('bill_contract_info_modify_pur_amount')->where('isDisable', 0)->groupStart()->where('FIND_IN_SET(uName, ' . $this->db->escape($leavedUser) . ') >', 0, false )->orWhere('uName', $this->myerp['uName'])->groupEnd()->where('isDelete', 0)->countAllResults();
                } else {
                    $purContractModifyPurAmount = $this->db->table('bill_contract_info_modify_pur_amount')->where(['isDisable' => 0, 'uName' => $this->myerp['uName'], 'isDelete' => 0])->countAllResults();
                }
                if ($purContractModifyPurAmount > 0) {
                    $data['purContractModifyPurAmount'] = ['class' => 'hover-purple', 'tabid' => 'purchaseContractModifyPurAmount', 'uri' => '?isDisable=0', 'alert' => $this->myerp['user']['position'] == '财务会计' ? ('您发出了 ' . $purContractModifyPurAmount . ' 个特采改价通知') : ('您收到 ' . $purContractModifyPurAmount . ' 个特采改价通知')];
                }
            }
            // 采购建议
            if (in_array('PC_QUERY', $this->myerp['rights'])) {
                $purSuggest = $this->db->table('pur_suggest')->where(['purId' => $this->myerp['uId'], 'status' => 0, 'isDelete' => 0])->countAllResults();
                if ($purSuggest > 0) {
                    $data['purSuggest'] = ['tabid' => 'purchaseSuggest', 'uri' => '?status=0', 'alert' => '您收到 ' . $purSuggest . ' 个采购建议'];
                }
            }
            // 采购订单20天仍未收齐货物
            if (in_array('PO_ADD', $this->myerp['rights'])) {
                $alertPO = $this->db->table('bill_order')->where(['billDate <=' => date('Y-m-d', strtotime('-20 day')), 'uId' => $this->myerp['uId'], 'billType' => 'PUR', 'goodsStatus1 <' => 2, 'isCancel' => 0, 'isDraft' => 0, 'isDelete' => 0])->countAllResults();
                if ($alertPO > 0) {
                    $data['alertPO'] = ['class' => 'hover-purple', 'tabid' => 'purchaseOrder', 'uri' => '?unDone=1&uId=' . $this->myerp['uId'] . '&endDate=' . date('Y-m-d', strtotime('-20 day')), 'alert' => '您有 ' . $alertPO . ' 笔采购订单已超过 20 天仍未收齐货物'];
                }
            }
            // 特采单距交货限期不足3天仍未采购
            if (in_array('PC_ADD', $this->myerp['rights'])) {
                $alertPC = $this->db->table('bill_contract_info')->where(['transType' => 101111, 'isReq !=' => 1, 'isPurchased <' => 2])->where('paidAmount < amount', null, false)->where(['isDraft' => 0, 'isDelete' => 0, 'endDate <=' => date('Y-m-d', strtotime('+3 day')), 'uId' => $this->myerp['uId']])->countAllResults();
                if ($alertPC > 0 ) {
                    $data['alertPC'] = ['class' => 'hover-purple', 'tabid' => 'purchaseContractLost', 'uri' => '?uId=' . $this->myerp['uId'], 'alert' => '您有 ' . $alertPC . ' 项特殊采购产品距交货限期已不足 3 天仍未采购'];
                }
            }
            // 值班遗漏
            if (in_array('SYS_ADMIN', $this->myerp['rights'])) {
                $dutyChoose = array_column($this->db->table('saler_duty')->select('dutyName')->where(['dutyDate' => date('Y-m-d'), 'isDisable' => 0, 'isDelete' => 0])->get()->getResultArray(), 'dutyName');
                $dutyChanel = array_column($this->db->table('billFrom')->select('name')->whereNotIn('name', ['官网', '手工录入'])->where(['isDisable' => 0, 'isDelete' => 0])->get()->getResultArray(), 'name');
                array_push($dutyChanel, '发货打单', 'QQ客服', '官网客服', '晚班客服');
                $dutyDiff = array_diff($dutyChanel, $dutyChoose);
                if (count($dutyDiff) > 0) {
                    $data['salerDutyEmpty'] = ['tabid' => 'salerDutyChoose', 'uri' => '?unDone=1', 'alert' => '您收到 ' . count($dutyDiff) . ' 个今日值班遗漏'];
                }
            } elseif ($this->myerp['user']['department'] == '销售部') {
                $dutyChoose = array_column($this->db->table('saler_duty')->select('dutyName')->where('dutyDate', date('Y-m-d'))->notLike('dutyName', 'LAZADA', 'after')->notLike('dutyName', 'SMT', 'after')->where(['isDisable' => 0, 'isDelete' => 0])->get()->getResultArray(), 'dutyName');
                $dutyChanel = array_column($this->db->table('billFrom')->select('name')->whereNotIn('name', ['官网', '手工录入'])->notLike('name', 'LAZADA', 'after')->notLike('name', 'SMT', 'after')->where(['isDisable' => 0, 'isDelete' => 0])->get()->getResultArray(), 'name');
                array_push($dutyChanel, '发货打单', 'QQ客服', '官网客服', '晚班客服');
                $dutyDiff = array_diff($dutyChanel, $dutyChoose);
                if (count($dutyDiff) > 0) {
                    $data['salerDutyEmpty'] = ['tabid' => 'salerDutyChoose', 'uri' => '?unDone=1', 'alert' => '您收到 ' . count($dutyDiff) . ' 个今日值班遗漏'];
                }
            } elseif (in_array($this->myerp['uName'], ['叶超', '杨颐'])) {
                $dutyChoose = array_column($this->db->table('saler_duty')->select('dutyName')->where('dutyDate', date('Y-m-d'))->groupStart()->like('dutyName','LAZADA', 'after')->orLike('dutyName', 'SMT', 'after')->groupEnd()->where(['isDisable' => 0, 'isDelete' => 0])->get()->getResultArray(), 'dutyName');
                $dutyChanel = array_column($this->db->table('billFrom')->select('name')->groupStart()->like('name', 'LAZADA', 'after')->orLike('name', 'SMT', 'after')->groupEnd()->where(['isDisable' => 0, 'isDelete' => 0])->get()->getResultArray(), 'name');
                $dutyDiff = array_diff($dutyChanel, $dutyChoose);
                if (count($dutyDiff) > 0) {
                    $data['salerDutyEmpty'] = ['tabid' => 'salerDutyChoose', 'uri' => '?unDone=1', 'alert' => '您收到 ' . count($dutyDiff) . ' 个今日值班遗漏'];
                }
            }
            // 投标项目
            if (in_array('BIDPROGRAM_CHECK', $this->myerp['rights']) && intval(date('d')) > 10) {
                if (!in_array('bidProgram', $this->myerp['noAlertCurLogin'] ?? [])) {
                    $bidProgram = $this->db->table('bid_program')->where(['status' => 0, 'createTime >=' => date('Y-m-01', strtotime(date('Y-m-01') . ' -1 month')) . ' 00:00:00', 'createTime <=' => date('Y-m-t', strtotime(date('Y-m-01') . ' -1 month')) . ' 23:59:59', 'isDelete' => 0])->countAllResults();
                    if ($bidProgram > 0) {
                        $data['bidProgram'] = ['tabid' => 'bidProgram', 'uri' => '?unDone=1&biginDate=' . date('Y-m-01', strtotime(date('Y-m-01') . ' -1 month')) . '&endDate=' . date('Y-m-t', strtotime(date('Y-m-01') . ' -1 month')), 'alert' => '请尽快标明上月 ' . $bidProgram . ' 个投标项目的开标结果<span id="noAlertCurLogin" data-alertname="bidProgram" class="db fr ml10 mr-10 mt2 h20 lh20 pl5 pr5 red bg-white b-r-3" title="在重新登录之前不再接收此类通知">拒收</span>'];
                    }
                }
            }
            // 特销交货已超期、即将到期
            if (in_array('SC_QUERY', $this->myerp['rights'])) {
                if (in_array('SALE_MANAGER', $this->myerp['rights'])) {
                    // 如果未拒收该提醒，才进行后续动作
                    if (!in_array('saleContractEndAll', $this->myerp['noAlertCurLogin'] ?? [])) {
                        $saleContractEndAll = $this->db->table('bill_contract')->where(['endDate <=' => date('Y-m-d'), 'billType' => 'SALE', 'goodsStatus1' => 0, 'redoSoType' => 0, 'isCancel' => 0, 'isDraft' => 0, 'isDelete' => 0])->countAllResults();
                        if ($saleContractEndAll > 0) {
                            $data['saleContractEndAll'] = ['class' => 'hover-purple', 'tabid' => 'salesContract', 'uri' => '?unDeliver=1&goodsStatus1=0', 'alert' => '您辖下有 ' . $saleContractEndAll . ' 个特殊销售单交货已超期<span id="noAlertCurLogin" data-alertname="saleContractEndAll" class="db fr ml10 mr-10 mt2 h20 lh20 pl5 pr5 red bg-white b-r-3" title="在重新登录之前不再接收此类通知">拒收</span>'];
                        }
                    }
                    if (!in_array('saleContractNearEndAll', $this->myerp['noAlertCurLogin'] ?? [])) {
                        $saleContractNearEndAll = $this->db->table('bill_contract')->where(['endDate >' => date('Y-m-d'), 'endDate <=' => date('Y-m-d', strtotime('+1 day')), 'billType' => 'SALE', 'goodsStatus1' => 0, 'redoSoType' => 0, 'isCancel' => 0, 'isDraft' => 0, 'isDelete' => 0])->countAllResults();
                        if ($saleContractNearEndAll > 0) {
                            $data['saleContractNearEndAll'] = ['class' => 'hover-yellow', 'tabid' => 'salesContract', 'uri' => '?unDeliver=2&goodsStatus1=0', 'alert' => '您辖下有 ' . $saleContractNearEndAll . ' 个特殊销售单交货即将到期<span id="noAlertCurLogin" data-alertname="saleContractNearEndAll" class="db fr ml10 mr-10 mt2 h20 lh20 pl5 pr5 red bg-white b-r-3" title="在重新登录之前不再接收此类通知">拒收</span>'];
                        }
                    }
                }
                if (!in_array('saleContractEnd', $this->myerp['noAlertCurLogin'] ?? [])) {
                    $saleContractEnd = $this->db->table('bill_contract')->where(['endDate <=' => date('Y-m-d'), 'billType' => 'SALE', 'goodsStatus1' => 0, 'redoSoType' => 0, 'isCancel' => 0, 'isDraft' => 0, 'isDelete' => 0])->where('FIND_IN_SET(' . $this->db->escape($this->myerp['uName']) . ', saler) >', 0, false)->countAllResults();
                    if ($saleContractEnd > 0) {
                        $data['saleContractEnd'] = ['class' => 'hover-purple', 'tabid' => 'salesContract', 'uri' => '?unDeliver=1&uName=' . $this->myerp['uName'] . '&goodsStatus1=0', 'alert' => '您有 ' . $saleContractEnd . ' 个特殊销售单交货已超期<span id="noAlertCurLogin" data-alertname="saleContractEnd" class="db fr ml10 mr-10 mt2 h20 lh20 pl5 pr5 red bg-white b-r-3" title="在重新登录之前不再接收此类通知">拒收</span>'];
                    }
                }
                if (!in_array('saleContractNearEnd', $this->myerp['noAlertCurLogin'] ?? [])) {
                    $saleContractNearEnd = $this->db->table('bill_contract')->where(['endDate >' => date('Y-m-d'), 'endDate <=' => date('Y-m-d', strtotime('+1 day')), 'billType' => 'SALE', 'goodsStatus1' => 0, 'redoSoType' => 0, 'isCancel' => 0, 'isDraft' => 0, 'isDelete' => 0])->where('FIND_IN_SET(' . $this->db->escape($this->myerp['uName']) . ', saler) >', 0, false)->countAllResults();
                    if ($saleContractNearEnd > 0) {
                        $data['saleContractNearEnd'] = ['class' => 'hover-yellow', 'tabid' => 'salesContract', 'uri' => '?unDeliver=2&uName=' . $this->myerp['uName'] . '&goodsStatus1=0', 'alert' => '您有 ' . $saleContractNearEnd . ' 个特殊销售单交货即将到期<span id="noAlertCurLogin" data-alertname="saleContractNearEnd" class="db fr ml10 mr-10 mt2 h20 lh20 pl5 pr5 red bg-white b-r-3" title="在重新登录之前不再接收此类通知">拒收</span>'];
                    }
                }
            }
            // 官网已付款未发货订单
            if (in_array('SO_ADD', $this->myerp['rights'])) {
                // 如果未拒收该提醒，才进行后续动作
                if (!in_array('unfilledOrder_56dz', $this->myerp['noAlertCurLogin'] ?? [])) {
                    $res = global_curl('https://www.56dz.com/erpapi/order/getnotdeliveredorderNum', ['timeout'=> 10]);
                    if ($res !== false) {
                        $res = (array)json_decode($res, true);
                        if (is_array($res) && isset($res['data']) && $res['data'] > 0) {
                            $data['unfilledOrder_56dz'] = ['url' => 'https://www.56dz.com/admin/order/list', 'alert' => '您收到 ' . $res['data'] . ' 个官网已付款未发货订单<span id="noAlertCurLogin" data-alertname="unfilledOrder_56dz" class="db fr ml10 mr-10 mt2 h20 lh20 pl5 pr5 red bg-white b-r-3" title="在重新登录之前不再接收此类通知">拒收</span>'];
                        }
                    }
                    // 【暂时不必要使用加载官网数据库模型进行官网数据库查询，杀鸡用牛刀了。后续如果将官网管理后台集成到本系统，才需要使用这种多数据库处理方式】
                    /* $this->load->model('Mysql_model_56dz'); // 按需加载操作外网56dz数据库model
                    $unfilledOrder_56dz = $this->Mysql_model_56dz->get_count('order', 'type = 0 AND pay_status = 1 AND delivery_status != 1 AND status NOT IN(4, 6) AND is_del = 0');
                    if ($unfilledOrder_56dz > 0) {
                        $data['unfilledOrder_56dz'] = ['url' => 'https://www.56dz.com/admin/order/list', 'alert' => '您收到 ' . $unfilledOrder_56dz . ' 个官网已付款未发货订单<span id="noAlertCurLogin" data-alertname="unfilledOrder_56dz" class="db fr ml10 mr-10 mt2 h20 lh20 pl5 pr5 red bg-white b-r-3" title="在重新登录之前不再接收此类通知">拒收</span>'];
                    } */
                }
            }
            // 销售订单30天仍未结清款项
            if (in_array('SO_ADD', $this->myerp['rights'])) {
                $dataO = $this->db->table('bill_order')->where(['billDate <=' => date('Y-m-d', strtotime('-30 day')), 'uId' => $this->myerp['uId'], 'billType' => 'SALE', 'moneyStatus1 <' => 2, 'isCancel' => 0, 'isDraft' => 0, 'isDelete' => 0])->countAllResults();
                if ($dataO > 0) {
                    $data['dataO'] = ['class' => 'hover-purple', 'tabid' => 'salesOrder', 'uri' => '?unClear=2&uId=' . $this->myerp['uId'] . '&endDate=' . date('Y-m-d', strtotime('-30 day')), 'alert' => '您有 ' . $dataO . ' 笔销售订单已超过 30 天仍未结清款项'];
                }
            }
            // 特殊销售单60天仍未收取任何款项、或365天仍未结清款项
            if (in_array('SC_ADD', $this->myerp['rights'])) {
                $dataC1 = $this->db->table('bill_contract')->where('billDate <=', date('Y-m-d', strtotime('-60 day')))->where('FIND_IN_SET(' . $this->db->escape($this->myerp['uName']) . ', saler) >', 0, false)->where(['billType' => 'SALE', 'moneyStatus1' => 0, 'isDraft' => 0, 'isDelete' => 0])->countAllResults();
                if ($dataC1 > 0) {
                    $data['dataC1'] = ['class' => 'hover-purple', 'tabid' => 'salesContract', 'uri' => '?unClear=1&uName=' . $this->myerp['uName'] . '&endDate=' . date('Y-m-d', strtotime('-60 day')), 'alert' => '您有 ' . $dataC1 . ' 笔特殊销售单已超过 60 天仍未收取任何款项'];
                }
                $dataC2 = $this->db->table('bill_contract')->where('billDate <=', date('Y-m-d', strtotime('-365 day')))->where('FIND_IN_SET(' . $this->db->escape($this->myerp['uName']) . ', saler) >', 0, false)->where(['billType' => 'SALE', 'moneyStatus1 <' => 2, 'isDraft' => 0, 'isDelete' => 0])->countAllResults();
                if ($dataC2 > 0) {
                    $data['dataC2'] = ['class' => 'hover-purple', 'tabid' => 'salesContract', 'uri' => '?unClear=2&uName=' . $this->myerp['uName'] . '&endDate=' . date('Y-m-d', strtotime('-365 day')), 'alert' => '您有 ' . $dataC2 . ' 笔特殊销售单已超过 365 天仍未结清款项'];
                }
            }
            // 客户之声7天未解决
            if (in_array('SERVICE_ADD', $this->myerp['rights'])) {
                $dataservice = $this->db->table('service')->where(['isDisable' => 0, 'beginDate <=' => date('Y-m-d', strtotime('-7 day')) . ' 23:59:59', 'beginName' => $this->myerp['uName'], 'isDelete' => 0])->countAllResults();
                if ($dataservice > 0 ) {
                    $data['dataservice'] = ['class' => 'hover-purple', 'tabid' => 'service', 'uri' => '?isDisable=0&beginName=' . $this->myerp['uName'] . '&endDate=' . date('Y-m-d', strtotime('-7 day')), 'alert' => '您有 ' . $dataservice . ' 个客户之声已超过 7 天仍未解决'];
                }
            }
            // 发包滞留3天未处理
            if (in_array('SO_ADD', $this->myerp['rights'])) {
                $alertDeliverBagDull = $this->db->table('deliver_bag')->where(['scanName' => '', 'scanTime' => '0000-00-00 00:00:00', 'printTime <=' => date('Y-m-d', strtotime('-3 day')) . ' 23:59:59', 'isDelete' => 0])->countAllResults();
                if ($alertDeliverBagDull > 0) {
                    $data['alertDeliverBagDull'] = ['class' => 'hover-purple', 'tabid' => 'deliverBagDull', 'uri' => '?unDone=1&endDate=' . date('Y-m-d', strtotime('-3 day')), 'alert' => '您辖下有 ' . $alertDeliverBagDull . ' 个发包滞留已超过 3 天仍未处理'];
                }
            }
            // 优惠申请
            if (in_array('SALE_MANAGER', $this->myerp['rights'])) {
                $discountReq = $this->db->table('discount_req')->where(['checkId' => 0, 'description' => '', 'isDelete' => 0])->countAllResults();
                if ($discountReq > 0) {
                    $data['discountReq'] = ['tabid' => 'discountReq', 'uri' => '?unDone=1', 'alert' => '您收到 ' . $discountReq . ' 个优惠申请'];
                }
            }
            // 报价审批
            if (in_array('SALE_MANAGER', $this->myerp['rights'])) {
                $quoteReq = $this->db->table('quote_req')->where(['checkId' => 0, 'description' => '', 'isDelete' => 0])->countAllResults();
                if ($quoteReq > 0) {
                    $data['quoteReq'] = ['tabid' => 'quoteReq', 'uri' => '?unDone=1', 'alert' => '您收到 ' . $quoteReq . ' 个报价审批'];
                }
            }
            // 年度销售目标
            if ($this->myerp['user']['position'] == '总裁') {
                $salesTargetYear = $this->db->table('sales_target_year')->where(['year' => date('Y'), 'isDelete' => 0])->countAllResults();
                if ($salesTargetYear == 0) {
                    $data['salesTargetYear'] = ['tabid' => 'salesTarget', 'uri' => '?', 'alert' => '请尽快制定本年销售目标'];
                }
            }
            // 月度销售目标
            if ($this->myerp['user']['position'] == '市场总监') {
                $salesTargetMonth = $this->db->table('sales_target_month')->where(['year' => date('Y'), 'month' => intval(date('m')), 'isDelete' => 0])->countAllResults();
                if ($salesTargetMonth == 0) {
                    $data['salesTargetMonth'] = ['tabid' => 'salesTarget', 'uri' => '?', 'alert' => '请尽快制定本月销售目标'];
                }
            }
            // 各主体销售额超限预警
            if (in_array($this->myerp['user']['department'], ['行政部', '销售部', '财务部'])) {
                if (!in_array('overSoldWarning', $this->myerp['noAlertCurLogin'] ?? [])) {
                    $companys = $this->db->table('company')->where(['isDisable' => 0, 'isDelete' => 0])->orderBy('ordnum ASC, id ASC')->get()->getResultArray();
                    foreach ($companys as $k => $v) {
                        $totalSaleAmount = 0;
                        // 先算本月含税销售额
                        $dateFrom = date('Y-m-01');
                        $dateTo = date('Y-m-t');
                        // 绑定网店销售金额（电商平台对接了税务局，只要挂了公司资质，销售额都是要报税的）
                        $curMonth_onlineOrderAmount = $this->db->table('bill_order')->select('SUM(amount) AS onlineOrderAmount')->where(['billType' => 'SALE', 'billDate >=' => $dateFrom, 'billDate <=' => $dateTo])->notLike('billNo', 'XD', 'after')->notLike('billNo', 'XXX')->where(['sId' => $v['id'], 'isCancel' => 0, 'isDraft' => 0, 'isDelete' => 0])->get()->getRowArray()['onlineOrderAmount'] ?? 0;
                        // 非绑定网店业务开出发票金额（主要是特销单、手工订单、官网订单，其他没挂公司资质的网店订单开出的票）
                        $curMonth_notOnlineOrderInvoiceOutAmount = $this->db->table('invoice')->select('SUM(amount) AS notOnlineOrderInvoiceOutAmount')->where(['date >=' => $dateFrom, 'date <=' => $dateTo, 'inoutType' => 0, 'us' => $v['id']])->groupStart()->like('billNo', 'XD')->orLike('billNo', 'XH')->orLike('billNo', 'XXX')->groupEnd()->notLike('status1', '已红冲', 'after')->where('isDelete', 0)->get()->getRowArray()['notOnlineOrderInvoiceOutAmount'] ?? 0;
                        $cur_month_saleAmount = intval((float)$curMonth_onlineOrderAmount + (float)$curMonth_notOnlineOrderInvoiceOutAmount);
                        // 再根据公司性质另外算小规模纳税人过去11个月固化的销售额记录
                        if ($v['type'] == 0 && $v['name'] != '虚拟主体') { // 个体工商户
                            $from_year = date('Y', strtotime('-11 month'));
                            $from_month = date('n', strtotime('-11 month'));
                            $to_year = date('Y');
                            $to_month = date('n');
                            $last_11_Month_saleAmount = $this->db->table('sales_amount_monthly')->select('SUM(saleAmount) AS totalSaleAmount')->where('cId', $v['id'])->groupStart()->groupStart()->where(['year' => $from_year, 'month >=' => $from_month])->groupEnd()->orGroupStart()->where(['year' => $to_year, 'month <' => $to_month])->groupEnd()->groupEnd()->where('isDelete', 0)->get()->getRowArray()['totalSaleAmount'] ?? 0;
                            $totalSaleAmount = intval((float)$cur_month_saleAmount + (float)$last_11_Month_saleAmount);
                            if ($cur_month_saleAmount > 90000) {
                                $data['overSoldWarning' . $k] = ['class' => $totalSaleAmount > 100000 ? 'hover-purple' : 'hover-yellow', 'tabid' => 'company', 'uri' => '?unDone=1', 'alert' => $v['name'] . ' 本月含税销售额已达 ' . round($totalSaleAmount / 10000, 2) . ' 万（限额10万），勿超限！<span id="noAlertCurLogin" data-alertname="overSoldWarning" class="db fr ml10 mr-10 mt2 h20 lh20 pl5 pr5 red bg-white b-r-3" title="在重新登录之前不再接收此类通知">拒收</span>'];
                            }
                            if ($totalSaleAmount > 1000000) {
                                $data['overSoldWarning' . $k] = ['class' => $totalSaleAmount > 1000000 ? 'hover-purple' : 'hover-yellow', 'tabid' => 'company', 'uri' => '?unDone=1', 'alert' => $v['name'] . ' 连续12个月含税销售额已达 ' . round($totalSaleAmount / 10000, 2) . ' 万（限额120万），勿超限！<span id="noAlertCurLogin" data-alertname="overSoldWarning" class="db fr ml10 mr-10 mt2 h20 lh20 pl5 pr5 red bg-white b-r-3" title="在重新登录之前不再接收此类通知">拒收</span>'];
                            }
                        } elseif ($v['type'] == 1 && $v['name'] != '虚拟主体') { // 小规模纳税人
                            $from_year = date('Y', strtotime('-11 month'));
                            $from_month = date('n', strtotime('-11 month'));
                            $to_year = date('Y');
                            $to_month = date('n');
                            $last_11_Month_saleAmount = $this->db->table('sales_amount_monthly')->select('SUM(saleAmount) AS totalSaleAmount')->where('cId', $v['id'])->groupStart()->groupStart()->where(['year' => $from_year, 'month >=' => $from_month])->groupEnd()->orGroupStart()->where(['year' => $to_year, 'month <' => $to_month])->groupEnd()->groupEnd()->where('isDelete', 0)->get()->getRowArray()['totalSaleAmount'] ?? 0;
                            $totalSaleAmount = intval((float)$cur_month_saleAmount + (float)$last_11_Month_saleAmount);
                            if ($totalSaleAmount > 4500000) {
                                $data['overSoldWarning' . $k] = ['class' => $totalSaleAmount > 5000000 ? 'hover-purple' : 'hover-yellow', 'tabid' => 'company', 'uri' => '?unDone=1', 'alert' => $v['name'] . ' 连续12个月含税销售额已达 ' . round($totalSaleAmount / 10000, 2) . ' 万（限额500万），勿超限！<span id="noAlertCurLogin" data-alertname="overSoldWarning" class="db fr ml10 mr-10 mt2 h20 lh20 pl5 pr5 red bg-white b-r-3" title="在重新登录之前不再接收此类通知">拒收</span>'];
                            }
                        }
                    }
                }
            }
            // 配货申请
            if (in_array('PROREQ_ADVANCE', $this->myerp['rights'])) {
                $produceKittingReq = $this->db->table('pro_kitting_req')->groupStart()->where('checkId', 0)->orWhere('produceId', 0)->groupEnd()->where(['description' => '', 'isDelete' => 0])->countAllResults();
                if ($produceKittingReq > 0) {
                    $data['produceKittingReq'] = ['tabid' => 'produceKittingReq', 'uri' => '?unDone=1', 'alert' => '您收到 ' . $produceKittingReq . ' 个配货申请'];
                }
            }
            // 加工申请
            if (in_array('PROREQ_ADVANCE', $this->myerp['rights'])) {
                $produceProcessingReq = $this->db->table('pro_processing_req')->groupStart()->where('checkId', 0)->orWhere('produceId', 0)->groupEnd()->where(['description' => '', 'isDelete' => 0])->countAllResults();
                if ($produceProcessingReq > 0) {
                    $data['produceProcessingReq'] = ['tabid' => 'produceProcessingReq', 'uri' => '?unDone=1', 'alert' => '您收到 ' . $produceProcessingReq . ' 个加工申请'];
                }
            }
            // 配货交付
            if (in_array('PRO_CHECK_IN_STORAGE', $this->myerp['rights'])) {
                if (!in_array('produceTaskUp', $this->myerp['noAlertCurLogin'] ?? [])) {
                    $produceKittingTaskUp = $this->db->table('pro_kitting_task_up')->where(['doneStatus <' => 2, 'isDelete' => 0])->countAllResults();
                    if ($produceKittingTaskUp > 0) {
                        $data['produceKittingTaskUp'] = ['tabid' => 'produceKittingTaskUp', 'uri' => '?unDone=1', 'alert' => '您收到 ' . $produceKittingTaskUp . ' 个配货交付<span id="noAlertCurLogin" data-alertname="produceKittingTaskUp" class="db fr ml10 mr-10 mt2 h20 lh20 pl5 pr5 red bg-white b-r-3" title="在重新登录之前不再接收此类通知">拒收</span>'];
                    }
                }
            }
            // 加工交付
            if (in_array('PRO_CHECK_IN_STORAGE', $this->myerp['rights'])) {
                if (!in_array('produceProcessingTaskUp', $this->myerp['noAlertCurLogin'] ?? [])) {
                    $produceProcessingTaskUp = $this->db->table('pro_processing_task_up')->where(['doneStatus <' => 2, 'isDelete' => 0])->countAllResults();
                    if ($produceProcessingTaskUp > 0) {
                        $data['produceProcessingTaskUp'] = ['tabid' => 'produceProcessingTaskUp', 'uri' => '?unDone=1', 'alert' => '您收到 ' . $produceProcessingTaskUp . ' 个加工交付<span id="noAlertCurLogin" data-alertname="produceProcessingTaskUp" class="db fr ml10 mr-10 mt2 h20 lh20 pl5 pr5 red bg-white b-r-3" title="在重新登录之前不再接收此类通知">拒收</span>'];
                    }
                }
            }
            // 配货超时
            if (in_array('PROTASK_ADVANCE', $this->myerp['rights'])) {
                $produceKittingTaskTimeout = $this->db->table('pro_kitting_task')->where(['taskStatus <' => 3, 'isDelete' => 0])->countAllResults();
                if ($produceKittingTaskTimeout > 0) {
                    $data['produceKittingTaskTimeout'] = ['class' => 'hover-purple', 'tabid' => 'produceKittingTask', 'uri' => '?unDone=1', 'alert' => '您辖下有 ' . $produceKittingTaskTimeout . ' 个配货任务已超时'];
                }
            }
            // 加工超时
            if (in_array('PROTASK_ADVANCE', $this->myerp['rights'])) {
                $produceProcessingTaskTimeout = $this->db->table('pro_processing_task')->where(['taskStatus <' => 3, 'isDelete' => 0])->countAllResults();
                if ($produceProcessingTaskTimeout > 0) {
                    $data['produceProcessingTaskTimeout'] = ['class' => 'hover-purple', 'tabid' => 'produceProcessingTask', 'uri' => '?unDone=1', 'alert' => '您辖下有 ' . $produceProcessingTaskTimeout . ' 个加工任务已超时'];
                }
            }
            // 同步异常
            if (in_array('SA_SPIDER_ERR_ADVANCE', $this->myerp['rights'])) {
                $salesOrderSpiderErrGoods = $this->db->table('spider_err_goods')->where(['status' => 0, 'isDelete' => 0])->countAllResults();
                if ($salesOrderSpiderErrGoods > 0) {
                    $data['salesOrderSpiderErrGoods'] = ['tabid' => 'salesOrderSpiderErrGoods', 'uri' => '?unDone=1', 'alert' => '您收到 ' . $salesOrderSpiderErrGoods . ' 个产品同步异常'];
                }
            }
            // 售价异常
            if (in_array('SA_SPIDER_ERR_ADVANCE', $this->myerp['rights'])) {
                $salesOrderSpiderErrPrice = $this->db->table('spider_err_price')->where(['status' => 0, 'isDelete' => 0])->countAllResults();
                if ($salesOrderSpiderErrPrice > 0) {
                    $data['salesOrderSpiderErrPrice'] = ['tabid' => 'salesOrderSpiderErrPrice', 'uri' => '?unDone=1', 'alert' => '您收到 ' . $salesOrderSpiderErrPrice . ' 个产品售价异常'];
                }
            }
            // 错误产品
            if (in_array('GOODS_UPDATE', $this->myerp['rights'])) {
                $errGoods = $this->db->table('goods')->where(['isError' => 1, 'isDelete' => 0])->countAllResults();
                if ($errGoods > 0) {
                    $data['errGoods'] = ['tabid' => 'goods', 'uri' => '?showErr=1', 'alert' => '您收到 ' . $errGoods . ' 个产品录入错误'];
                }
            }
            // 自产电子套件(需配货)缺失开发人或维护人
            if (in_array($this->myerp['user']['position'], ['总裁', '技术总监', '运营总监', '行政总监'])) {
                // 提取需配货套件类及子类
                $kitCids = array_column($this->db->table('category')->select('id')->where('FIND_IN_SET("26", path) >', 0, false)->where('isDelete', 0)->get()->getResultArray(), 'id');
                $noDevOrSerName = $this->db->table('goods')->whereIn('categoryId', $kitCids)->groupStart()->where('devName', '')->orWhere('serName', '')->groupEnd()->where(['isDisable' => 0, 'isDelete' => 0])->countAllResults();
                if ($noDevOrSerName > 0) {
                    $data['noDevOrSerName'] = ['tabid' => 'goods', 'uri' => '?noDevOrSerName=1&categoryId=26&showForbidden=0', 'alert' => '您辖下有 ' . $noDevOrSerName . ' 个自产电子套件(需配货)缺失开发人或维护人'];
                }
            }
            // 技术|资料|缺陷|返修客户之声
            if (in_array($this->myerp['user']['department'], ['行政部', '技术部', '运营部'])) {
                $service16 = $this->db->table('service')->where(['categoryId' => 16, 'isDisable' => 0, 'isDelete' => 0])->countAllResults();
                if ($service16 > 0) {
                    $data['service16'] = ['tabid' => 'service', 'uri' => '?unDone=1&categoryId=16&isDisable=0', 'alert' => '您收到 ' . $service16 . ' 个技术|资料|缺陷|返修客户之声'];
                }
            }
            // 配货|缺件|包装|配错客户之声
            if (in_array($this->myerp['user']['position'], ['总裁', '运营总监', '行政总监'])) {
                $service66 = $this->db->table('service')->where(['categoryId' => 66, 'isDisable' => 0, 'isDelete' => 0])->countAllResults();
                if ($service66 > 0) {
                    $data['service66'] = ['tabid' => 'service', 'uri' => '?unDone=1&categoryId=66&isDisable=0', 'alert' => '您收到 ' . $service66 . ' 个配货|缺件|包装|配错客户之声'];
                }
            }
            // 库房|发错|发漏|缺货客户之声
            if (in_array($this->myerp['user']['position'], ['总裁', '运营总监', '行政总监'])) {
                $service83 = $this->db->table('service')->where(['categoryId' => 83, 'isDisable' => 0, 'isDelete' => 0])->countAllResults();
                if ($service83 > 0) {
                    $data['service83'] = ['tabid' => 'service', 'uri' => '?unDone=1&categoryId=83&isDisable=0', 'alert' => '您收到 ' . $service83 . ' 个库房|发错|发漏|缺货客户之声'];
                }
            }
            // 建议|意见|信息|反馈客户之声
            if (in_array('SYS_ADVANCE', $this->myerp['rights'])) {
                $service331 = $this->db->table('service')->where(['categoryId' => 331, 'isDisable' => 0, 'isDelete' => 0])->countAllResults();
                if ($service331 > 0) {
                    $data['service331'] = ['tabid' => 'service', 'uri' => '?unDone=1&categoryId=331&isDisable=0', 'alert' => '您收到 ' . $service331 . ' 个建议|意见|信息|反馈客户之声'];
                }
            }
            // 其他售后问题客户之声
            if (in_array('SYS_ADVANCE', $this->myerp['rights'])) {
                $service354 = $this->db->table('service')->where(['categoryId' => 354, 'isDisable' => 0, 'isDelete' => 0])->countAllResults();
                if ($service354 > 0) {
                    $data['service354'] = ['tabid' => 'service', 'uri' => '?unDone=1&categoryId=354&isDisable=0', 'alert' => '您收到 ' . $service354 . ' 个其他售后问题'];
                }
            }
            // 官网异常数量
            if (in_array($this->myerp['user']['position'], ['行政部', '技术部', '运营部'])) {
                // 如果未拒收该提醒，才进行后续动作
                if (!in_array('abnormal_56dz', $this->myerp['noAlertCurLogin'] ?? [])) {
                    if ($res = global_curl('https://www.56dz.com/erpapi/abnormal/getAbnormal', ['timeout'=> 3])) {
                        $res = (array)json_decode($res, true);
                        if (is_array($res) && isset($res['data'])) {
                            $totalAbnormal = 0;
                            foreach ($res['data'] as $k => $v) {
                                $totalAbnormal += intval($v);
                            }
                            if ($totalAbnormal > 0) {
                                $data['abnormal_56dz'] = ['url' => 'https://www.56dz.com/admin/index', 'alert' => '您收到 ' . $totalAbnormal . ' 个官网商品或文件异常<span id="noAlertCurLogin" data-alertname="abnormal_56dz" class="db fr ml10 mr-10 mt2 h20 lh20 pl5 pr5 red bg-white b-r-3" title="在重新登录之前不再接收此类通知">拒收</span>'];
                            }
                        }
                    }
                }
            }
            // 定制申请
            if (in_array('GOODSDEVREQ_ADVANCE', $this->myerp['rights'])) {
                $goodsDevReq = $this->db->table('goods_dev_req')->where('id >', 423)->groupStart()->where('checkId', 0)->orWhere('srcTaskId', 0)->groupEnd()->where(['description' => '', 'isDelete' => 0])->countAllResults();
                if ($goodsDevReq > 0) {
                    $data['goodsDevReq'] = ['tabid' => 'goodsDevReq', 'uri' => '?unDone=1', 'alert' => '您收到 ' . $goodsDevReq . ' 个定制申请'];
                }
            }
            // 下载申请
            if (in_array('GOODSFILEONE_CHECK', $this->myerp['rights']) || in_array('GOODSFILETWO_CHECK', $this->myerp['rights']) || in_array('GOODSFILETHREE_CHECK', $this->myerp['rights']) || in_array('GOODSFILEFOUR_CHECK', $this->myerp['rights']) || in_array('GOODSFILEFIVE_CHECK', $this->myerp['rights'])) {
                $goodsFileReq = $this->db->table('goods_file_req')->where(['checkId' => 0, 'description' => '', 'isDelete' => 0])->countAllResults();
                if ($goodsFileReq > 0) {
                    $data['goodsFileReq'] = ['tabid' => 'goodsFileReq', 'uri' => '?unDone=1', 'alert' => '您收到 ' . $goodsFileReq . ' 个下载申请'];
                }
            }
            // 产品开发60天未完成
            if (in_array('GOODSTASK_UPDATE', $this->myerp['rights'])) {
<<<<<<< HEAD
                $builder = $this->db->table('goods_task')->where('FIND_IN_SET("5", status) =', 0, false)->where(['beginDate <=' => date('Y-m-d', strtotime('-60 day')), 'isDelete' => 0]);
=======
                $builder = $this->db->table('goods_task')->where('!FIND_IN_SET(5, status) >', 0, false)->where(['beginDate <=' => date('Y-m-d', strtotime('-60 day')), 'isDelete' => 0]);
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
                if (!in_array($this->myerp['user']['position'], ['总裁', '技术总监'])) {
                    $builder->groupStart()->where('leader', $this->myerp['uName'])->orWhere('FIND_IN_SET(' . $this->db->escape($this->myerp['uName']) . ', other) >', 0, false)->groupEnd();
                }
                $goodsTaskUndone = $builder->countAllResults();
                if($goodsTaskUndone > 0) {
                    $data['goodsTaskUndone'] = ['class' => 'hover-purple', 'tabid' => 'goodsTask', 'uri' => '?unDone=1&endDate=' . date('Y-m-d', strtotime('-60 day')), 'alert' => '您辖下有 ' . $goodsTaskUndone . ' 个产品开发已超过 60 天仍未完成'];
                }
            }
            // 付款申请
            if (in_array('FINANCE_MANAGER', $this->myerp['rights'])) {
                $payReq = $this->db->table('pay_req')->groupStart()->where('checkId', 0)->orWhere('moneyStatus <', 2)->groupEnd()->where(['description' => '', 'type' => 1, 'isDelete' => 0])->countAllResults();
                if ($payReq > 0) {
                    $data['payReq'] = ['tabid' => 'payReq', 'uri' => '?unDone=1', 'alert' => '您收到 ' . $payReq . ' 个付款申请'];
                }
            }
            // 报销申请
            if (in_array('FINANCE_MANAGER', $this->myerp['rights'])) {
                $expensesReq = $this->db->table('pay_req')->groupStart()->where('checkId', 0)->orWhere('moneyStatus <', 2)->groupEnd()->where(['description' => '', 'type' => 2, 'isDelete' => 0])->countAllResults();
                if ($expensesReq > 0) {
                    $data['expensesReq'] = ['tabid' => 'expensesReq', 'uri' => '?unDone=1', 'alert' => '您收到 ' . $expensesReq . ' 个报销申请'];
                }
            }
            // 改价通知 有异议
            if (in_array('FINANCE_MANAGER', $this->myerp['rights'])) {
                $purContractModifyPurAmountDis = $this->db->table('bill_contract_info_modify_pur_amount')->where(['isDisable' => 2, 'isDelete' => 0])->countAllResults();
                if ($purContractModifyPurAmountDis > 0) {
                    $data['purContractModifyPurAmountDis'] = ['tabid' => 'purchaseContractModifyPurAmount', 'uri' => '?isDisable=2', 'alert' => '您收到 ' . $purContractModifyPurAmountDis . ' 个特采改价异议'];
                }
            }
            // 预退款 未到账
            if (in_array('FINANCE_MANAGER', $this->myerp['rights'])) {
                // 如果未拒收该提醒，才进行后续动作
                if (!in_array('virtualRefund', $this->myerp['noAlertCurLogin'] ?? [])) {
                    $virtualRefund = $this->db->table('bill_money_info')->where(['moneyType' => 9, 'transType' => 103101, 'isDelete' => 0])->countAllResults();
                    if ($virtualRefund > 0) {
                        $data['virtualRefund'] = ['tabid' => 'purchaseContract', 'uri' => '?status3=-2', 'alert' => '您有 ' . $virtualRefund . ' 笔预退款未到账<span id="noAlertCurLogin" data-alertname="virtualRefund" class="db fr ml10 mr-10 mt2 h20 lh20 pl5 pr5 red bg-white b-r-3" title="在重新登录之前不再接收此类通知">拒收</span>'];
                    }
                }
            }
            // 识票异常
            if (in_array('FINANCE_MANAGER', $this->myerp['rights'])) {
                $invoiceErr = $this->db->table('invoice_err')->where(['status' => 0, 'isDelete' => 0])->countAllResults();
                if ($invoiceErr > 0) {
                    $data['invoiceErr'] = ['tabid' => 'invoiceErr', 'uri' => '?unDone=1', 'alert' => '您收到 ' . $invoiceErr . ' 个发票识别异常'];
                }
            }
        }
        return $this->response->setJSON(['status' => 200, 'msg' => 'success', 'data' => $data]);
    }
    // 首页 本次登录拒收紧急通知记录
    public function noAlertCurLogin()
    {
        $alertName = $this->request->getGetPost('alertName');
        if (strlen($alertName) > 0) {
            $myerp = $this->myerp;
            if (isset($myerp['noAlertCurLogin'])) {
                if (!in_array($alertName, $myerp['noAlertCurLogin'])) {
                    array_push($myerp['noAlertCurLogin'], $alertName);
                }
            } else {
                $myerp['noAlertCurLogin'] = [$alertName];
            }
            $this->session->set('myerp', $myerp);
            return $this->response->setJSON(['status' => 200, 'msg' => 'success']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '缺少要拒收的消息名称！']);
    }

    // 主页 考评趋势数据
    public function evaluateChartData()
    {
        $list = $this->db->table('bill_achievement_info')->select('billNo, evaluateScore')->where(['uId' => $this->myerp['uId'], 'beginDate >=' => $this->myerp['system']['beginDate'], 'beginDate <' => date('Y-m') . '-01', 'isDelete' => 0])->orderBy('billNo DESC')->limit(6)->offset(0)->get()->getResultArray();
        $data['evaluateData'] = array_reverse($list); //倒序
        return $this->response->setJSON(['status' => 200, 'msg' => 'success', 'data' => $data]);
    }
    // 主页 绩效趋势数据
    public function achievementChartData()
    {
        $list = $this->db->table('bill_achievement_info')->select('billNo, sumSalary')->where(['uId' => $this->myerp['uId'], 'beginDate >=' => $this->myerp['system']['beginDate'], 'beginDate <' => date('Y-m') . '-01', 'isDelete' => 0])->orderBy('billNo DESC')->limit(6)->offset(0)->get()->getResultArray();
        $data['achievementData'] = array_reverse($list); //倒序
        return $this->response->setJSON(['status' => 200, 'msg' => 'success', 'data' => $data]);
    }
    // 主页 销售目标数据
    public function salesTargetChartData()
    {
        if ($this->myservice->verifyRight('SYS_ADVANCE') || $this->myservice->verifyRight('FINANCE_MANAGER')) {
            $data = $this->db->table('sales_target_month')->where('isDelete', 0)->orderBy('year DESC, month DESC')->limit(6)->offset(0)->get()->getResultArray();
            foreach ($data as $k => $v) {
                $ym = $v['year'] . '-' . str_pad($v['month'], 2, '0', STR_PAD_LEFT);
                $start = $ym . '-01';
                $end = date('Y-m-t', strtotime($start));
                $orderAmount = $this->db->table('bill_order')->select('SUM(amount) AS orderAmount')->where(['billDate >=' => $start, 'billDate <=' => $end, 'billType' => 'SALE', 'isDelete' => 0])->get()->getRowArray()['orderAmount'] ?? 0;
                $contractAmount = $this->db->table('bill_contract')->select('SUM(amount) AS contractAmount')->where(['billDate >=' => $start, 'billDate <=' => $end, 'billType' => 'SALE', 'redoSoType' => 0, 'isDelete' => 0])->get()->getRowArray()['contractAmount'] ?? 0;
                $data[$k]['curAmount'] = intval((float)$orderAmount + (float)$contractAmount);
            }
        } else {
            $data = $this->db->table('sales_target_month_info')->where(['salerId' => $this->myerp['uId'], 'isDelete' => 0])->orderBy('year DESC, month DESC')->limit(6)->offset(0)->get()->getResultArray();
            foreach ($data as $k => $v) {
                $ym = $v['year'] . '-' . str_pad($v['month'], 2, '0', STR_PAD_LEFT);
                $start = $ym . '-01';
                $end = date('Y-m-t', strtotime($start));
                $userOrderAmount = $this->db->table('bill_order')->select('SUM(amount) AS userOrderAmount')->where('billDate >=', $start)->where('billDate <=', $end)->where(['billType' => 'SALE', 'uId' => $v['salerId'], 'isCancel' => 0, 'isDelete' => 0])->get()->getRowArray()['userOrderAmount'] ?? 0;
                $userContractAmount = $this->db->table('bid_program')->select('SUM(CASE WHEN LOCATE(",", quotedName) > 0 THEN amount / (CHAR_LENGTH(quotedName) - CHAR_LENGTH(REPLACE(quotedName, ",", "")) + 1) ELSE amount END) AS userContractAmount', false)->where('createTime >=', $start . ' 00:00:00')->where('createTime <=', $end . ' 23:59:59')->where('FIND_IN_SET(' . $this->db->escape($v['salerName']) . ', quotedName) >', 0, false)->where(['status' => 2, 'isDelete' => 0])->get()->getRowArray()['userContractAmount'] ?? 0;
                $userOnlineShopAmount = $this->db->table('bill_onlineshopMp_info')->select('SUM(amount) AS userOnlineShopAmount')->where(['beginDate >=' => $start, 'endDate <=' => $end, 'uId' => $v['salerId'], 'isDelete' => 0])->get()->getRowArray()['userOnlineShopAmount'] ?? 0;
                $data[$k]['curAmount'] = intval((float)$userOrderAmount + (float)$userContractAmount + (float)$userOnlineShopAmount);
            }
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['salesTargetData'] = array_reverse($data); //倒序
        return $this->response->setJSON($json);
    }
    // 主页 采购回票数据
    public function invoiceBackChartData()
    {
        // 预设只取最近6个月数据
        $data = [];
        for ($i = 0; $i < 6; $i++) {
            array_push($data, ['year' => date('Y', strtotime(date('Y-m-01') . ' -' . $i . ' month')), 'month' => date('m', strtotime(date('Y-m-01') . ' -' . $i . ' month'))]);
        }
        if ($this->myservice->verifyRight('SYS_ADVANCE') || $this->myservice->verifyRight('FINANCE_MANAGER') || $this->myerp['user']['department'] == '采购部') {
            // 高级权限用户、财务经理、采购部员工，查看总体回票情况
            foreach ($data as $k => $v) {
                $ym = $v['year'] . '-' . str_pad($v['month'], 2, '0', STR_PAD_LEFT);
                $start = $ym . '-01';
                $end = date('Y-m-t', strtotime($start));
                // 采购总体支出以当月收支明细里（采购款-采购款退款）为准
                $paidAmount = $this->db->table('bill_money_info')->select('SUM(amount) AS paidAmount')->where('typeId', 7)->whereIn('transType', [103102, 103104])->where(['settlementTime >=' => $start . ' 00:00:00', 'settlementTime <=' => $end . ' 23:59:59', 'isDelete' => 0])->get()->getRowArray()['paidAmount'] ?? 0;
                $refundAmount = $this->db->table('bill_money_info')->select('SUM(amount) AS refundAmount')->where('typeId', 6)->whereIn('transType', [103101, 103103])->where(['settlementTime >=' => $start . ' 00:00:00', 'settlementTime <=' => $end . ' 23:59:59', 'isDelete' => 0])->get()->getRowArray()['refundAmount'] ?? 0;
                $data[$k]['purPaidAmount'] = intval((float)$paidAmount - (float)$refundAmount);
                // 采购总体回票以当月收到的开票日期为准
                $data[$k]['purInvoiceAmount'] = $this->db->table('invoice')->select('SUM(amount) AS purInvoiceAmount')->where(['date >=' => $start, 'date <=' => $end, 'inoutType' => 1])->whereIn('transType', [101101, 101102, 101111])->where('isDelete', 0)->get()->getRowArray()['purInvoiceAmount'] ?? 0;
            }
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['invoiceBackData'] = array_reverse($data); //倒序
        return $this->response->setJSON($json);
    }

    // 主页 倒计时数据
    public function countdownData()
    {
        // 供货已超期
        $list1 = $this->db->table('bill_contract')->select('id, billNo, contactName, saler, endDate, goodsStatus1')->where(['endDate <=' => date('Y-m-d'), 'billType' => 'SALE', 'goodsStatus1' => 0, 'redoSoType' => 0, 'isCancel' => 0, 'isDraft' => 0, 'isDelete' => 0])->orderBy('endDate', 'ASC')->get()->getResultArray();
        foreach ($list1 as $arr1 => $row1) {
            $v[$arr1]['id'] = intval($row1['id']);
            $v[$arr1]['billNo'] = $row1['billNo'];
            $v[$arr1]['contactName'] = $row1['contactName'];
            $v[$arr1]['endDate'] = $row1['endDate'];
            $v[$arr1]['saler'] = $row1['saler'];
        }
        // 供货即将超期
        $list2 = $this->db->table('bill_contract')->select('id, billNo, contactName, saler, endDate, goodsStatus1')->where('endDate >', date('Y-m-d'))->where('endDate', date('Y-m-d', strtotime('+1 day')))->where(['billType' => 'SALE', 'goodsStatus1' => 0, 'redoSoType' => 0, 'isCancel' => 0, 'isDraft' => 0, 'isDelete' => 0])->orderBy('endDate', 'ASC')->get()->getResultArray();
        foreach ($list2 as $arr2 => $row2) {
            $w[$arr2]['id'] = intval($row2['id']);
            $w[$arr2]['billNo'] = $row2['billNo'];
            $w[$arr2]['contactName'] = $row2['contactName'];
            $w[$arr2]['endDate'] = $row2['endDate'];
            $w[$arr2]['saler'] = $row2['saler'];
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['saleContactCountdownData1'] = isset($v) ? array_values($v) : [];
        $json['data']['saleContactCountdownData2'] = isset($w) ? array_values($w) : [];
        return $this->response->setJSON($json);
    }
    // 主页 产品动态数据
    public function goodsRecentNotice()
    {
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 10);
        $order = 'id desc';
        $type = intval($this->request->getGetPost('type'));
        $skey = $this->request->getGetPost('skey');
        if ($type == 1) { // 最近缺货申报
            $builder1 = $this->db->table('pur_req')->select("id, 0 AS qty, CONCAT(title, ' ', content) AS title, uName, createTime", false)->where('isDelete', 0);
            $builder2 = $this->db->table('pro_kitting_req')->select("id, qty, CONCAT(goodsText, ' ', content) AS title, uName, createTime", false)->where('isDelete', 0);
            if ($skey !== '' && $skey !== null) {
                $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
                foreach ($keywords as $kw) {
                    $builder1->groupStart()->like('title', $this->db->escapeLikeString($kw))->orLike('content', $this->db->escapeLikeString($kw))->groupEnd();
                    $builder2->groupStart()->like('goodsText', $this->db->escapeLikeString($kw))->orLike('content', $this->db->escapeLikeString($kw))->groupEnd();
                }
            }
            $sql = "({$builder1->getCompiledSelect(false)})
                    UNION ALL
                    ({$builder2->getCompiledSelect(false)})
                    ORDER BY createTime DESC
                    LIMIT {$rows}
                    OFFSET " . ($rows * ($page - 1));
            $list = $this->db->query($sql)->getResultArray();
            $total = $builder1->countAllResults() + $builder2->countAllResults();
            $v = [];
            foreach ($list as $arr => $row) {
                $v[$arr]['id'] = intval($row['id']);
                $v[$arr]['qty'] = $row['qty'] > 0 ? ($row['qty'] . ' 套') : '';
                $v[$arr]['title'] = $row['title'];
                $v[$arr]['uName'] = $row['uName'];
                $v[$arr]['date'] = $row['createTime'] . ' 申报';
            }
            $json['status'] = 200;
            $json['msg'] = 'success';
            $json['data']['page'] = $page;
            $json['data']['records'] = $total;
            $json['data']['total'] = ceil($json['data']['records'] / $rows);
            $json['data']['rows'] = $v;
            return $this->response->setJSON($json);
        } elseif ($type == 2) { // 最近配货交付
            $builder = $this->db->table('pro_kitting_task_up')->where('isDelete', 0);
            if ($skey !== '' && $skey !== null) {
                $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
                $builderGoods = $this->db->table('goods')->select('id')->where('isDelete', 0);
                foreach ($keywords as $kw) {
                    $builderGoods->groupStart()
                        ->like('barCode', $this->db->escapeLikeString($kw))
                        ->orLike('no', $this->db->escapeLikeString($kw))
                        ->orLike('name', $this->db->escapeLikeString($kw))
                        ->orLike('spec', $this->db->escapeLikeString($kw))
                        ->orLike('sku', $this->db->escapeLikeString($kw))
                    ->groupEnd();
                }
                $goodsIds = array_column($builderGoods->get()->getResultArray(), 'id');
                $builder->whereIn('goodsId', $goodsIds);
            }
            $list = (clone $builder)->orderBy($order)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
            $total = $builder->countAllResults();
            $v = [];
            foreach ($list as $arr => $row) {
                $v[$arr]['id'] = intval($row['id']);
                $goods = $this->db->table('goods')->where('id', $row['goodsId'])->get()->getRowArray();
                $v[$arr]['qty'] = $row['upQty'] . ' ' . $goods['unitName'];
                $v[$arr]['title'] = $goods['no'] . ' ' . $goods['name'] . ' ' . $goods['spec'] . ' ' . $goods['sku'] . ' ' . $goods['brandName'];
                $v[$arr]['uName'] = $row['uName'];
                $v[$arr]['date'] = $row['createTime'] . ' 交付';
            }
            $json['status'] = 200;
            $json['msg'] = 'success';
            $json['data']['page'] = $page;
            $json['data']['records'] = $total;
            $json['data']['total'] = ceil($json['data']['records'] / $rows);
            $json['data']['rows'] = $v;
            return $this->response->setJSON($json);
        } elseif ($type == 3) { //最近采购到货
            $builder = $this->db->table('bill_inventory_log')->where(['transType' => 102101, 'isDelete' => 0]);
            if ($skey !== '' && $skey !== null) {
                $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
                $builderGoods = $this->db->table('goods')->select('id')->where('isDelete', 0);
                foreach ($keywords as $kw) {
                    $builderGoods->groupStart()
                        ->like('barCode', $this->db->escapeLikeString($kw))
                        ->orLike('no', $this->db->escapeLikeString($kw))
                        ->orLike('name', $this->db->escapeLikeString($kw))
                        ->orLike('spec', $this->db->escapeLikeString($kw))
                        ->orLike('sku', $this->db->escapeLikeString($kw))
                    ->groupEnd();
                }
                $goodsIds = array_column($builderGoods->get()->getResultArray(), 'id');
                $builder->whereIn('goodsId', $goodsIds);
            }
            $list = (clone $builder)->orderBy($order)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
            $total = $builder->countAllResults();
            $v = [];
            foreach ($list as $arr => $row) {
                $v[$arr]['id'] = intval($row['id']);
                $goods = $this->db->table('goods')->where('id', $row['goodsId'])->get()->getRowArray();
                $v[$arr]['qty'] = $row['qty'] . ' ' . $goods['unitName'];
                $v[$arr]['title'] = $goods['no'] . ' ' . $goods['name'] . ' ' . $goods['spec'] . ' ' . $goods['sku'] . ' ' . $goods['brandName'];
                $v[$arr]['uName'] = $row['uName'];
                $v[$arr]['date'] = $row['createTime'] . ' 收货';
            }
            $json['status'] = 200;
            $json['msg'] = 'success';
            $json['data']['page'] = $page;
            $json['data']['records'] = $total;
            $json['data']['total'] = ceil($json['data']['records'] / $rows);
            $json['data']['rows'] = $v;
            return $this->response->setJSON($json);
        } elseif ($type == 4) { //最近新品上架
            $builder = $this->db->table('goods')->where('isDelete', 0);
            if ($skey !== '' && $skey !== null) {
                $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
                $builderGoods = $this->db->table('goods')->select('id')->where('isDelete', 0);
                foreach ($keywords as $kw) {
                    $builderGoods->groupStart()
                        ->like('barCode', $this->db->escapeLikeString($kw))
                        ->orLike('no', $this->db->escapeLikeString($kw))
                        ->orLike('name', $this->db->escapeLikeString($kw))
                        ->orLike('spec', $this->db->escapeLikeString($kw))
                        ->orLike('sku', $this->db->escapeLikeString($kw))
                    ->groupEnd();
                }
                $goodsIds = array_column($builderGoods->get()->getResultArray(), 'id');
                $builder->whereIn('id', $goodsIds);
            }
            $list = (clone $builder)->orderBy($order)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
            $total = $builder->countAllResults();
            $v = [];
            foreach ($list as $arr => $row) {
                $v[$arr]['id'] = intval($row['id']);
                $v[$arr]['qty'] = '上架';
                $v[$arr]['title'] = $row['no'] . ' ' . $row['name'] . ' ' . $row['spec'] . ' ' . $row['sku'] . ' ' . $row['brandName'];
                $v[$arr]['uName'] = $row['uName'];
                $v[$arr]['date'] = $row['createTime'];
            }
            $json['status'] = 200;
            $json['msg'] = 'success';
            $json['data']['page'] = $page;
            $json['data']['records'] = $total;
            $json['data']['total'] = ceil($json['data']['records'] / $rows);
            $json['data']['rows'] = $v;
            return $this->response->setJSON($json);
        } else {
            return $this->response->setJSON(['status' => -1, 'msg' => '请求类型错误！']);
        }
    }

    // 主页 个人信息数据
    public function personalData()
    {
        $data['myInfo'] = $this->db->table('user')->where(['id' => $this->myerp['uId'], 'isDelete' => 0])->get()->getRowArray();
        $data['myInfo']['medal'] = $this->db->table('user_medal')->where(['date' => date('Ym', strtotime(date('Y-m-01') . ' -1 month')), 'uName' => $this->myerp['uName'], 'isDelete' => 0])->get()->getResultArray();
        $data['myInfo']['rank']['attRank'] = $this->db->table('user_rank')->select('rank')->where(['uName' => $this->myerp['uName'], 'name' => 'attRank', 'isDelete' => 0])->orderBy('id DESC')->limit(2)->offset(0)->get()->getResultArray();
        if ($data['myInfo']['department'] == '销售部' && !in_array($data['myInfo']['position'], ['电商文员', '投标文员'])) {
            $data['myInfo']['rank']['bidRank'] = $this->db->table('user_rank')->select('rank')->where(['uName' => $this->myerp['uName'], 'name' => 'bidRank', 'isDelete' => 0])->orderBy('id DESC')->limit(2)->offset(0)->get()->getResultArray();
            $data['myInfo']['rank']['saleRank'] = $this->db->table('user_rank')->select('rank')->where(['uName' => $this->myerp['uName'], 'name' => 'saleRank', 'isDelete' => 0])->orderBy('id DESC')->limit(2)->offset(0)->get()->getResultArray();
            $data['myInfo']['rank']['serviceRank'] = $this->db->table('user_rank')->select('rank')->where(['uName' => $this->myerp['uName'], 'name' => 'serviceRank', 'isDelete' => 0])->orderBy('id DESC')->limit(2)->offset(0)->get()->getResultArray();
        }
        if ($data['myInfo']['department'] == '采购部') {
            $data['myInfo']['rank']['purRank'] = $this->db->table('user_rank')->select('rank')->where(['uName' => $this->myerp['uName'], 'name' => 'purRank', 'isDelete' => 0])->orderBy('id DESC')->limit(2)->offset(0)->get()->getResultArray();
            $data['myInfo']['rank']['invoiceRank'] = $this->db->table('user_rank')->select('rank')->where(['uName' => $this->myerp['uName'], 'name' => 'invoiceRank', 'isDelete' => 0])->orderBy('id DESC')->limit(2)->offset(0)->get()->getResultArray();
        }
        if ($data['myInfo']['superiorsId'] > 0) {
            $superiorsInfo = $this->db->table('user')->where(['id' => $data['myInfo']['superiorsId'], 'isDelete' => 0])->get()->getRowArray();
            if ($superiorsInfo) {
                $data['myInfo']['superiorsInfo'] = $superiorsInfo;
                $data['myInfo']['superiorsInfo']['avatar'] = '/data/upfile/avatar/' . ($superiorsInfo['avatar'] ?: 'avatar.jpg');
                $clockinInfo = $this->db->table('clockin')->where(['uId' => $superiorsInfo['id'], 'clockDate ' => date('Y-m-d'), 'isDelete' => 0])->get()->getRowArray();
                if ($clockinInfo) {
                    if (($clockinInfo['amClock'] != '00:00:00' && $clockinInfo['pmClock'] == '00:00:00') || ($clockinInfo['amClock2'] !== '00:00:00' && $clockinInfo['pmClock2'] == '00:00:00')) {
                        $data['myInfo']['superiorsInfo']['onDuty'] = '在岗';
                    } elseif (($clockinInfo['amClock'] != '00:00:00' && $clockinInfo['pmClock'] != '00:00:00' && $clockinInfo['amClock2'] == '00:00:00') || ($clockinInfo['amClock2'] != '00:00:00' && $clockinInfo['pmClock2'] != '00:00:00')) {
                        $data['myInfo']['superiorsInfo']['onDuty'] = '离岗';
                    } else {
                        $data['myInfo']['superiorsInfo']['onDuty'] = trim(str_replace(['补卡,', ',补卡', '补卡'], '', $clockinInfo['marks']), ',');
                    }
                } else {
                    if ($superiorsInfo['id'] == 1 || $superiorsInfo['id'] == 11 || $superiorsInfo['id'] == 7 || $superiorsInfo['id'] == 8 || $superiorsInfo['id'] == 103 || $superiorsInfo['id'] == 172 || $superiorsInfo['id'] == 195) {
                        $data['myInfo']['superiorsInfo']['onDuty'] = '';
                    } else {
                        $data['myInfo']['superiorsInfo']['onDuty'] = '休息';
                    }
                }
            }
        }
        $data['myInfo']['lateTimes'] = $this->db->table('clockin')->where('clockDate >=', date('Y-m-01'))->where('clockDate <=', date('Y-m-t'))->where('FIND_IN_SET("迟到", marks) >', 0, false)->where(['uId' => $this->myerp['uId'], 'isDelete' => 0])->countAllResults();
        $membersInfo = $this->db->table('user')->where(['superiorsId' => $this->myerp['uId'], 'isDisable' => 0, 'isDelete' => 0])->get()->getResultArray();
        if (count($membersInfo) > 0) {
            $data['myInfo']['membersInfo'] = $membersInfo;
            foreach ($data['myInfo']['membersInfo'] as $k => $v) {
                $data['myInfo']['membersInfo'][$k]['avatar'] = '/data/upfile/avatar/' . ($v['avatar'] ?: 'avatar.jpg');
                $clockinInfo = $this->db->table('clockin')->where(['uId' => $v['id'], 'clockDate ' => date('Y-m-d'), 'isDelete' => 0])->get()->getRowArray();
                if ($clockinInfo) {
                    if (($clockinInfo['amClock'] != '00:00:00' && $clockinInfo['pmClock'] == '00:00:00') || ($clockinInfo['amClock2'] !== '00:00:00' && $clockinInfo['pmClock2'] == '00:00:00')) {
                        $data['myInfo']['membersInfo'][$k]['onDuty'] = '在岗';
                    } elseif (($clockinInfo['amClock'] != '00:00:00' && $clockinInfo['pmClock'] != '00:00:00' && $clockinInfo['amClock2'] == '00:00:00') || ($clockinInfo['amClock2'] != '00:00:00' && $clockinInfo['pmClock2'] != '00:00:00')) {
                        $data['myInfo']['membersInfo'][$k]['onDuty'] = '离岗';
                    } else {
                        $data['myInfo']['membersInfo'][$k]['onDuty'] = trim(str_replace(['补卡,', ',补卡', '补卡'], '', $clockinInfo['marks']), ',');
                    }
                } else {
                    if ($data['myInfo']['membersInfo'][$k]['id'] == 1 || $data['myInfo']['membersInfo'][$k]['id'] == 11 || $data['myInfo']['membersInfo'][$k]['id'] == 7 || $data['myInfo']['membersInfo'][$k]['id'] == 8 || $data['myInfo']['membersInfo'][$k]['id'] == 103 || $data['myInfo']['membersInfo'][$k]['id'] == 172 || $data['myInfo']['membersInfo'][$k]['id'] == 195) {
                        $data['myInfo']['membersInfo'][$k]['onDuty'] = '';
                    } else {
                        $data['myInfo']['membersInfo'][$k]['onDuty'] = '休息';
                    }
                }
            }
        }
        // 首次登陆后session中isFirstLogin设置的是true，传递过一次后将session中的isFirstLogin设为false。
        $data['isFirstLogin'] = $this->myerp['isFirstLogin'];
        if ($data['isFirstLogin']) {
            $session['myerp'] = $this->myerp;
            $session['myerp']['isFirstLogin'] = false;
            $this->session->set($session);
        }
        return $this->response->setJSON(['status' => 200, 'msg' => 'success', 'data' => $data]);
    }
    // 主页 私人备忘数据
    public function noteData()
    {
        // 私人备忘继续提醒数
        $data['noteNotice'] = $this->db->table('note')->where(['uId' => $this->myerp['uId'], 'isDisable' => 0 , 'isDelete' => 0])->countAllResults();
        // 私人备忘 取5条继续提醒展示
        $data['noteInfo'] = $this->db->table('note')->select('id,title')->where(['uId' => $this->myerp['uId'], 'isDelete' => 0])->orderBy('id', 'DESC')->limit(5)->get()->getResultArray();
        return $this->response->setJSON(['status' => 200, 'msg' => 'success', 'data' => $data]);
    }

    // 主页 升级提醒处理
    public function levelupNoticed()
    {
        $this->db->table('user')->where('id', $this->myerp['uId'])->set('levelNoticedId', 'levelId', false)->update();
        return $this->response->setJSON(['status' => 200, 'msg' => 'success']);
    }
}
