<?php

namespace App\Controllers\Base;
use App\Controllers\BaseController;

class Invoice extends BaseController
{
    // 发票记录 列表
    public function list()
    {
        if (!$this->myservice->verifyRight('INVOICE_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Invoice/list 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = $this->request->getGetPost('sord');
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');
        $type = intval($this->request->getGetPost('type'));
        $us = intval($this->request->getGetPost('us'));
        $inout = intval($this->request->getGetPost('inout'));
        $transType = intval($this->request->getGetPost('transType'));
        $billFrom = $this->request->getGetPost('billFrom');
        $status1 = intval($this->request->getGetPost('status1'));
        $status2 = intval($this->request->getGetPost('status2'));
        $uName = $this->request->getGetPost('uName');
        $minAmount = (float)$this->request->getGetPost('minAmount');
        $maxAmount = (float)$this->request->getGetPost('maxAmount');
        $skey = $this->request->getGetPost('skey');

        $builder = $this->db->table('invoice');
        if ($beginDate) {
            $builder->where('date >=', $beginDate . ' 00:00:00');
        }
        if ($endDate) {
            $builder->where('date <=', $endDate . ' 23:59:59');
        }
        if ($type >= 0) {
            $builder->where('type', $type);
        }
        if ($us > 0) {
            $builder->where('us', $us);
        }
        if ($inout >= 0) {
            $builder->where('inoutType', $inout);
        }
        if ($transType >= 0) {
            $builder->where('transType', $transType);
        }
        if ($billFrom) {
            $builder->where('billFrom', $billFrom);
        }
        if ($status1 >= 0) {
            if ($status1 == 0) {
                $builder->where('status1', '');
            } elseif ($status1 == 1) {
                $builder->where('status1', '正常');
            } else {
                $builder->where('status1 !=', '')->where('status1 !=', '正常');
            }
        }
        if ($status2 >= 0) {
            if ($status2 == 0) {
                $builder->where('status2', '');
            } elseif ($status2 == 1) {
                $builder->where('status2', '正常');
            } else {
                $builder->where('status2 !=', '')->where('status2 !=', '正常');
            }
        }
        if ($uName) {
            $builder->where('uName', $uName);
        }
        if ($minAmount > 0) {
            $builder->where('amount >=', $minAmount);
        }
        if ($maxAmount > 0) {
            $builder->where('amount <=', $maxAmount);
        }
        if ($skey !== '' && $skey !== null) {
            $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
            foreach ($keywords as $kw) {
                $builder->groupStart()
                    ->like('code', $this->db->escapeLikeString($kw))
                    ->orLike('no', $this->db->escapeLikeString($kw))
                    ->orLike('otherSide', $this->db->escapeLikeString($kw))
                    ->orLike('billNo', $this->db->escapeLikeString((string)$kw))
                    ->orLike('billFrom', $this->db->escapeLikeString($kw))
                ->groupEnd();
            }
        }
        $builder->where('isDelete', 0);
        // 允许排序的字段白名单，杜绝 order by 注入
        $allowedSort = ['id','date','code','no','amount','type','us','otherSide','inoutType','billNo','billFrom','status1','status2','uName','createTime'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $list = (clone $builder)->orderBy($orderField, $sord)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $total = (clone $builder)->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['date'] = $row['date'];
            $v[$arr]['code'] = $row['code'];
            $v[$arr]['no'] = $row['no'];
            $v[$arr]['amount'] = (float)$row['amount'];
            $v[$arr]['type'] = intval($row['type']);
            $v[$arr]['inoutType'] = intval($row['inoutType']);
            $v[$arr]['otherSide'] = $row['otherSide'];
            $v[$arr]['us'] = intval($row['us']);
            $v[$arr]['label'] = intval($row['label']);
            $v[$arr]['transType'] = intval($row['transType']);
            $v[$arr]['transTypeName'] = $v[$arr]['transType'] > 0 ? $this->myerp['transTypeName'][$v[$arr]['transType']] : '未知';
            $v[$arr]['billNo'] = $row['billNo'];
            $v[$arr]['billFrom'] = $row['billFrom'];
            $v[$arr]['uName'] = $row['uName'];
            $v[$arr]['createTime'] = $row['createTime'];
            $v[$arr]['status1'] = $row['status1'];
            $v[$arr]['status2'] = $row['status2'];
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['page'] = $page;
        $json['data']['rows'] = $v;
        $json['data']['records'] = $total;
        $json['data']['total'] = ceil($json['data']['records'] / $rows);
        // 我方开出发票金额
        $json['data']['totalOutAmount'] = (clone $builder)->selectSum('amount', 'totalOutAmount')->where('inoutType', 0)->notLike('status1','已红冲', 'after', null, false)->get()->getRowArray()['totalOutAmount'] ?? 0;
        // 我方收取发票金额
        $json['data']['totalInAmount'] = (clone $builder)->selectSum('amount', 'totalInAmount')->where('inoutType', 1)->notLike('status1','已红冲', 'after', null, false)->get()->getRowArray()['totalInAmount'] ?? 0;
        // 纳税基数金额
        $json['data']['onlineOrderAmount'] = $json['data']['notOnlineOrderInvoiceOutAmount'] = $json['data']['totalTaxAmount'] = 0;
        if ($us > 0) {
            // 绑定网店销售金额（电商平台对接了税务局，只要挂了公司资质，销售额都是要报税的）
            $json['data']['onlineOrderAmount'] = $this->db->table('bill_order')->select('SUM(amount) AS onlineOrderAmount')->where(['billType' => 'SALE', 'billDate >=' => $beginDate, 'billDate <=' => $endDate])->notLike('billNo', 'XD', 'after')->notLike('billNo', 'XXX')->WHERE(['sId' => $us, 'isCancel' => 0, 'isDraft' => 0, 'isDelete' => 0])->get()->getRowArray()['onlineOrderAmount'] ?? 0;
            // 非绑定网店业务开出发票金额（主要是特销单、手工订单、官网订单）
            $json['data']['notOnlineOrderInvoiceOutAmount'] = $this->db->table('invoice')->select('SUM(amount) AS notOnlineOrderInvoiceOutAmount')->where(['date >=' => $beginDate, 'date <=' => $endDate, 'inoutType' => 0, 'us' => $us])->groupStart()->like('billNo','XD')->orLike('billNo','XH')->orLike('billNo', 'XXX')->groupEnd()->notLike('status1', '已红冲', 'after')->where('isDelete', 0)->get()->getRowArray()['notOnlineOrderInvoiceOutAmount'] ?? 0;
            $json['data']['totalTaxAmount'] = bcadd($json['data']['onlineOrderAmount'], $json['data']['notOnlineOrderInvoiceOutAmount'], 2);
        } else {
            $sids = array_column($this->db->table('company')->select('id')->where(['isDisable' => 0, 'isDelete' => 0])->get()->getResultArray(), 'id');
            // 绑定网店销售金额（电商平台对接了税务局，只要挂了公司资质，销售额都是要报税的）
            $json['data']['onlineOrderAmount'] = $this->db->table('bill_order')->select('SUM(amount) AS onlineOrderAmount')->where(['billType' => 'SALE', 'billDate >=' => $beginDate, 'billDate <=' => $endDate])->notLike('billNo', 'XD', 'after')->notLike('billNo', 'XXX')->whereIn('sId', $sids)->where(['isCancel' => 0, 'isDraft' => 0, 'isDelete' => 0])->get()->getRowArray()['onlineOrderAmount'] ?? 0;
            // 非绑定网店业务开出发票金额（主要是特销单、手工订单、官网订单）
            $json['data']['notOnlineOrderInvoiceOutAmount'] = $this->db->table('invoice')->select('SUM(amount) AS notOnlineOrderInvoiceOutAmount')->where(['date >=' => $beginDate , 'date <=' => $endDate, 'inoutType' => 0])->whereIn('us', $sids)->groupStart()->like('billNo', 'XD')->orLike('billNo', 'XH')->orLike('billNo','XXX')->groupEnd()->notLike('status1', '已红冲', 'after')->where('isDelete', 0)->get()->getRowArray()['notOnlineOrderInvoiceOutAmount'] ?? 0;
            $json['data']['totalTaxAmount'] = bcadd($json['data']['onlineOrderAmount'], $json['data']['notOnlineOrderInvoiceOutAmount'], 2);
        }
        return $this->response->setJSON($json);
    }
    // 发票记录 获取某单据所有发票
    public function queryAll()
    {
        if (!$this->myservice->verifyRight('INVOICE_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Invoice/queryAll 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $billNo = (string)$this->request->getGetPost('billNo');
        $builder = $this->db->table('invoice');
        $builder->where('isDelete', 0);
        if ($billNo) {
            $builder->like('billNo', $this->db->escapeLikeString($billNo));
        }
        $list = $builder->orderBy('id', 'DESC')->get()->getResultArray();
        if (strpos($billNo, 'CH') === 0) {
            $userAmountData = array_column($this->db->table('bill_contract_info')
                ->selectSum('paidAmount')
                ->select('0 AS invoiceAmount')
                ->select('uName')
                ->where('billNo', $billNo)
                ->where('isIgnoreInvoice <', 2)
                ->where('isDelete', 0)
                ->groupBy('uName')
                ->get()
                ->getResultArray(), null, 'uName');
        }
        $v = [];
        foreach ($list as $arr => $row) {
            if (isset($userAmountData)) {
                $invoiceBillNoArr = json_decode($row['billNo'], true);
                foreach ($invoiceBillNoArr as $invoiceBillNo => $invoiceBillNoAmount) {
                    if ($invoiceBillNo == $billNo) {
                        if (!isset($userAmountData[$row['uName']])) {
                            $userAmountData[$row['uName']] = ['paidAmount' => 0, 'invoiceAmount' => (float)$invoiceBillNoAmount];
                        } else {
                            $userAmountData[$row['uName']]['invoiceAmount'] += (float)$invoiceBillNoAmount;
                        }
                    }
                }
            }
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['date'] = $row['date'];
            $v[$arr]['code'] = $row['code'];
            $v[$arr]['no'] = $row['no'];
            $v[$arr]['amount'] = (float)$row['amount'];
            $v[$arr]['type'] = intval($row['type']);
            $v[$arr]['inoutType'] = intval($row['inoutType']);
            $v[$arr]['otherSide'] = $row['otherSide'];
            $v[$arr]['us'] = intval($row['us']);
            $v[$arr]['label'] = intval($row['label']);
            $v[$arr]['transType'] = intval($row['transType']);
            if ($row['billNo']) {
                $v[$arr]['billNo'] = json_decode($row['billNo'], true);
            } else {
                $v[$arr]['billNo'] = [];
            }
            $v[$arr]['uName'] = $row['uName'];
            $v[$arr]['createTime'] = $row['createTime'];
            $v[$arr]['status1'] = $row['status1'];
            $v[$arr]['status2'] = $row['status2'];
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['rows'] = $v;
        $json['data']['records'] = count($v);
        $json['data']['userAmountData'] = $userAmountData ?? null;
        return $this->response->setJSON($json);
    }
    // 发票记录 查询
    public function query()
    {
        if (!$this->myservice->verifyRight('INVOICE_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Invoice/query 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('invoice')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
        if ($data) {
            $json['status'] = 200;
            $json['msg'] = 'success';
            $json['data']['id'] = intval($data['id']);
            $json['data']['date'] = $data['date'];
            $json['data']['code'] = $data['code'];
            $json['data']['no'] = $data['no'];
            $json['data']['amount'] = (float)$data['amount'];
            $json['data']['type'] = intval($data['type']);
            $json['data']['inoutType'] = intval($data['inoutType']);
            $json['data']['otherSide'] = $data['otherSide'];
            $json['data']['us'] = intval($data['us']);
            $json['data']['label'] = intval($data['label']);
            $json['data']['transType'] = intval($data['transType']);
            $json['data']['billNo'] = $data['billNo'];
            if ($data['billNo']) {
                $json['data']['billNo'] = json_decode($data['billNo'], true);
            } else {
                $json['data']['billNo'] = [];
            }
            $json['data']['uName'] = $data['uName'];
            $json['data']['createTime'] = $data['createTime'];
            $json['data']['status1'] = $data['status1'];
            $json['data']['status2'] = $data['status2'];
            return $this->response->setJSON($json);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 发票记录 新增
    public function add()
    {
        if (!$this->myservice->verifyRight('INVOICE_ADD')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Invoice/add 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $data = $this->validform($data);
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            if ($data['inoutType'] == 0 && in_array($data['transType'], [101101, 101111, 103104, 109101])) {
                return $this->response->setJSON(['status' => -1, 'msg' => '往来是我方开出，业务类别只能选择（销售订单|特殊销售单|其他收入单）！']);
            } elseif ($data['inoutType'] == 1 && in_array($data['transType'], [101201, 101211, 103103])) {
                return $this->response->setJSON(['status' => -1, 'msg' => '往来是我方收取，业务类别只能选择（采购订单|特殊采购单|其他支出单|快递月结单）！']);
            }
            $billNoArr = (array)json_decode($data['billNo'], true);
            foreach ($billNoArr as $billNo => $amount) {
                $billNo = (string)$billNo;  // 订单号转为字符串，避免纯数字订单号被当成数字，确保后续处理正确
                if ($data['transType'] == 101101 && strpos($billNo, 'CD') !== 0 && strpos($billNo, 'CH') !== 0) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '业务类别是采购订单，不能关联其他类型单据！']);
                }
                if ($data['transType'] == 101111 && strpos($billNo, 'CD') !== 0 && strpos($billNo, 'CH') !== 0) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '业务类别是特殊采购单，不能关联其他类型单据！']);
                }
                if ($data['transType'] == 101211 && strpos($billNo, 'XH') !== 0) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '业务类别是特殊销售单，不能关联其他类型单据！']);
                }
                if ($data['transType'] == 103103 && strpos($billNo, 'SR') !== 0) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '业务类别是其他收入单，不能关联其他类型单据！']);
                }
                if ($data['transType'] == 103104 && strpos($billNo, 'ZC') !== 0) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '业务类别是其他支出单，不能关联其他类型单据！']);
                }
                if ($data['transType'] == 109101 && strpos($billNo, 'KD') !== 0) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '业务类别是快递月结单，不能关联其他类型单据！']);
                }
                if ($data['transType'] == 101201 && (strpos($billNo, 'CD') === 0 || strpos($billNo, 'CH') === 0 || strpos($billNo, 'SR') === 0 || strpos($billNo, 'ZC') === 0 || strpos($billNo, 'KD') === 0)) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '业务类别是销售订单，不能关联其他类型单据！']);
                }
            }
            $info = array_intersect_key($data, array_flip(['type', 'date', 'code', 'no', 'amount', 'inoutType', 'otherSide', 'us', 'label', 'transType', 'billNo','billFrom', 'uName', 'createTime']));
            $this->db->transStart();
            $this->db->table('invoice')->insert($info);
            $data['id'] = $this->db->insertID();
            if (!empty($billNoArr)) {
                foreach ($billNoArr as $billNo => $amount) {
                    $billNo = (string)$billNo; // 订单号转为字符串，避免纯数字订单号被当成数字，确保后续处理正确
                    $invoices = $this->db->table('invoice')
                        ->like('billNo', $this->db->escapeLikeString($billNo))
                        ->where('isDelete', 0)
                        ->get()
                        ->getResultArray();
                    $invoiceNum = count($invoices);
                    $invoiceAmount = 0;
                    foreach ($invoices as $index => $invoice) {
                        $invoiceBillNo = (array)json_decode($invoice['billNo'], true);
                        if (isset($invoiceBillNo[$billNo])) {
                            $invoiceAmount += floatval($invoiceBillNo[$billNo]);
                        }
                    }
                    if (strpos($billNo, 'KD') === 0) { // 快递月结
                        $this->db->table('bill_deliver')->where('billNo', $billNo)->update(['invoiceNum' => $invoiceNum, 'invoiceAmount' => bcadd($invoiceAmount, '0', 2)]);
                    } elseif (strpos($billNo, 'ZC') === 0 || strpos($billNo, 'SR') === 0) { // 其他收入、其他支出
                        $this->db->table('bill_money')->where('billNo', $billNo)->update(['invoiceNum' => $invoiceNum, 'invoiceAmount' => bcadd($invoiceAmount, '0', 2)]);
                    } elseif (strpos($billNo, 'CH') === 0 || strpos($billNo, 'XH') === 0) { // 特殊采购、特殊销售
                        $this->db->table('bill_contract')->where('billNo', $billNo)->update(['invoiceNum' => $invoiceNum, 'invoiceAmount' => bcadd($invoiceAmount, '0', 2)]);
                    } else { // 采购订单、采购直发订单、销售订单
                        $this->db->table('bill_order')->where('billNo', $billNo)->update(['invoiceNum' => $invoiceNum, 'invoiceAmount' => bcadd($invoiceAmount, '0', 2)]);
                    }
                }
            }
            $this->myservice->log('新增发票记录，发票代码：' . $data['code'] . '，发票号码：' . $data['no']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '新增成功！', 'data' => $data]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    // 发票记录 编辑
    public function update()
    {
        if (!$this->myservice->verifyRight('INVOICE_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Invoice/update 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $data = $this->validform($data);
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            if ($data['inoutType'] == 0 && in_array($data['transType'], [101101, 101111, 103104, 109101])) {
                return $this->response->setJSON(['status' => -1, 'msg' => '往来是我方开出，业务类别只能选择（销售订单|特殊销售单|其他收入单）！']);
            } elseif ($data['inoutType'] == 1 && in_array($data['transType'], [101201, 101211, 103103])) {
                return $this->response->setJSON(['status' => -1, 'msg' => '往来是我方收取，业务类别只能选择（采购订单|特殊采购单|其他支出单|快递月结单）！']);
            }
            $billNoArr = (array)json_decode($data['billNo'], true);
            foreach ($billNoArr as $billNo => $amount) {
                $billNo = (string)$billNo; // 订单号转为字符串，避免纯数字订单号被当成数字，确保后续处理正确
                if ($data['transType'] == 101101 && strpos($billNo, 'CD') !== 0 && strpos($billNo, 'CH') !== 0) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '业务类别是采购订单，不能关联其他类型单据！']);
                }
                if ($data['transType'] == 101111 && strpos($billNo, 'CD') !== 0 && strpos($billNo, 'CH') !== 0) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '业务类别是特殊采购单，不能关联其他类型单据！']);
                }
                if ($data['transType'] == 101211 && strpos($billNo, 'XH') !== 0) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '业务类别是特殊销售单，不能关联其他类型单据！']);
                }
                if ($data['transType'] == 103103 && strpos($billNo, 'SR') !== 0) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '业务类别是其他收入单，不能关联其他类型单据！']);
                }
                if ($data['transType'] == 103104 && strpos($billNo, 'ZC') !== 0) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '业务类别是其他支出单，不能关联其他类型单据！']);
                }
                if ($data['transType'] == 109101 && strpos($billNo, 'KD') !== 0) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '业务类别是快递月结单，不能关联其他类型单据！']);
                }
                if ($data['transType'] == 101201 && (strpos($billNo, 'CD') === 0 || strpos($billNo, 'CH') === 0 || strpos($billNo, 'SR') === 0 || strpos($billNo, 'ZC') === 0 || strpos($billNo, 'KD') === 0)) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '业务类别是销售订单，不能关联其他类型单据！']);
                }
            }
            $oldData = $this->db->table('invoice')->where(['id' => $data['id'], 'isDelete' => 0])->get()->getRowArray();
            if ($oldData['billNo']) {
                $oldBillNoArr = (array)json_decode($oldData['billNo'], true);
            } else {
                $oldBillNoArr = [];
            }
            // 特意采用 + 或 array_replace（php8.0新语法）合并数组，避免 array_merge 合并时对于纯数字键名超过64位PHP整型最大值而从0开始重置索引导致键名丢失（键名是订单号）, 键名如果是0、1，以['billNo' => 0]或['billNo' => 1]为where条件去更新时，会误更新大量行，所以更新语句采用明确的加双引号的形式，而不采用['billNo' => $billNo]作为where条件。
            $billNoArr = $billNoArr + $oldBillNoArr;
            $info = array_intersect_key($data, array_flip(['date', 'code', 'no', 'amount', 'type', 'inoutType', 'otherSide', 'us', 'label', 'transType', 'billNo', 'billFrom']));
            $this->db->transStart();
            $this->db->table('invoice')->where(['id' => $data['id']])->update($info);
            foreach ($billNoArr as $billNo => $amount) {
                $billNo = (string)$billNo; // 订单号转为字符串，避免纯数字订单号被当成数字，确保后续处理正确
                $invoices = $this->db->table('invoice')
                    ->like('billNo', $this->db->escapeLikeString($billNo))
                    ->where(['isDelete' => 0])
                    ->get()
                    ->getResultArray();
                $invoiceNum = count($invoices);
                $invoiceAmount = 0;
                foreach ($invoices as $index => $invoice) {
                    $invoiceBillNo = (array)json_decode($invoice['billNo'], true);
                    if (isset($invoiceBillNo[$billNo])) {
                        $invoiceAmount += floatval($invoiceBillNo[$billNo]);
                    }
                }
                if (strpos($billNo, 'KD') === 0) { // 快递月结
                    $this->db->table('bill_deliver')->where('billNo', $billNo)->update(['invoiceNum' => $invoiceNum, 'invoiceAmount' => bcadd($invoiceAmount, '0', 2)]);
                } elseif (strpos($billNo, 'ZC') === 0 || strpos($billNo, 'SR') === 0) { // 其他收入、其他支出
                    $this->db->table('bill_money')->where('billNo', $billNo)->update(['invoiceNum' => $invoiceNum, 'invoiceAmount' => bcadd($invoiceAmount, '0', 2)]);
                } elseif (strpos($billNo, 'CH') === 0 || strpos($billNo, 'XH') === 0) { // 特殊采购、特殊销售
                    $this->db->table('bill_contract')->where('billNo', $billNo)->update(['invoiceNum' => $invoiceNum, 'invoiceAmount' => bcadd($invoiceAmount, '0', 2)]);
                } else { // 采购订单、采购直发订单、销售订单
                    $this->db->table('bill_order')->where('billNo', $billNo)->update(['invoiceNum' => $invoiceNum, 'invoiceAmount' => bcadd($invoiceAmount, '0', 2)]);
                }
            }
            $this->myservice->log('编辑了发票记录，发票代码：' . $data['code'] . '，发票号码：' . $data['no']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '编辑成功！', 'data' => $data]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    // 发票记录 删除
    public function delete()
    {
        if (!$this->myservice->verifyRight('INVOICE_DELETE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Invoice/delete 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('invoice')->where(['id' => $id])->get()->getRowArray();
        if ($data) {
            if ($data['uName'] != $this->myerp['uName']) {
                return $this->response->setJSON(['status' => -1, 'msg' => '不能删除他人录入的发票记录！']);
            }
            // 真删除
            $this->db->table('invoice')->where(['id' => $id])->delete();
            $billNoArr = (array)json_decode($data['billNo'], true);
            foreach ($billNoArr as $billNo => $amount) {
                $billNo = (string)$billNo; // 订单号转为字符串，避免纯数字订单号被当成数字，确保后续处理正确
                $invoices = $this->db->table('invoice')
                    ->like('billNo', $this->db->escapeLikeString($billNo))
                    ->where(['isDelete' => 0])
                    ->get()
                    ->getResultArray();
                $invoiceNum = count($invoices);
                $invoiceAmount = 0;
                foreach ($invoices as $index => $invoice) {
                    $invoiceBillNo = (array)json_decode($invoice['billNo'], true);
                    if (isset($invoiceBillNo[$billNo])) {
                        $invoiceAmount += floatval($invoiceBillNo[$billNo]);
                    }
                }
                if (strpos($billNo, 'KD') === 0) { // 快递月结
                    $this->db->table('bill_deliver')->where(['billNo' => $billNo])->update(['invoiceNum' => $invoiceNum, 'invoiceAmount' => bcadd($invoiceAmount, '0', 2)]);
                } elseif (strpos($billNo, 'ZC') === 0 || strpos($billNo, 'SR') === 0) { // 其他收入、其他支出
                    $this->db->table('bill_money')->where(['billNo' => $billNo])->update(['invoiceNum' => $invoiceNum, 'invoiceAmount' => bcadd($invoiceAmount, '0', 2)]);
                } elseif (strpos($billNo, 'CH') === 0 || strpos($billNo, 'XH') === 0) { // 特殊采购、特殊销售
                    $this->db->table('bill_contract')->where(['billNo' => $billNo])->update(['invoiceNum' => $invoiceNum, 'invoiceAmount' => bcadd($invoiceAmount, '0', 2)]);
                } else { // 采购订单、采购直发订单、销售订单
                    $this->db->table('bill_order')->where(['billNo' => $billNo])->update(['invoiceNum' => $invoiceNum, 'invoiceAmount' => bcadd($invoiceAmount, '0', 2)]);
                }
            }
            $this->myservice->log('删除了发票记录，发票代码：' . $data['code'] . '，发票号码：' . $data['no']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '删除成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }

    // 发票记录 数据验证
    private function validform(array $data)
    {
        $data['id'] = isset($data['id']) ? intval($data['id']) : 0;
        if (strlen($data['date']) < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '开票日期不能为空！']);
        }
        if (strlen($data['no']) < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '发票号码不能为空！']);
        }
        if ((float)$data['amount'] < 0) {
            return $this->response->setJSON(['status' => -1, 'msg' => '价税合计需为正数！']);
        }
        if (intval($data['us']) < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '我方主体不能为空！']);
        }
        if (intval($data['label']) < 0) {
            return $this->response->setJSON(['status' => -1, 'msg' => '性质标签不能为空！']);
        }
        if (strlen($data['uName']) < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '录入人不能为空！']);
        }
        if (count($data['billNo']) < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '关联单据不能为空！']);
        }
        if (
            $this->db->table('invoice')
                ->groupStart()
                    ->where(['code' => $data['code'], 'no' => $data['no']])
                    ->orWhere('CONCAT(code,no)', $this->db->escape($data['code'] . $data['no']), false)
                ->groupEnd()
                ->where(['id !=' => $data['id'], 'isDelete' => 0])
                ->countAllResults() > 0
        ) {
            return $this->response->setJSON(['status' => -1, 'msg' => '该发票已录入过！']);
        }
        $data['billFrom'] = '';
        if ($data['inoutType'] == 0) { // 我方开出的发票，要记录对应销售单号的店铺名
            foreach ($data['billNo'] as $billNo => $amount) {
                $billNo = (string)$billNo; // 订单号转为字符串，避免纯数字订单号被当成数字，确保后续处理正确
                $orderInfo = $this->db->table('bill_order')->select('billFrom')->where(['billType' => 'SALE', 'billNo' => $billNo])->get()->getRowArray();
                if ($orderInfo) {
                    $data['billFrom'] = $orderInfo['billFrom'];
                    break;
                }
            }
        }
        $data['billNo'] = json_encode($data['billNo']);
        $data['type'] = intval($data['type']);
        $data['inoutType'] = intval($data['inoutType']);
        $data['transType'] = intval($data['transType']);
        $data['createTime'] = date('Y-m-d H:i:s');
        return $data;
    }


    // 导入税务局全量发票表格（我方收取）
    public function uploadExcel_1()
    {
        if (!$this->myservice->verifyRight('INVOICE_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Invoice/uploadExcel_1 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        set_time_limit(0); // 设置脚本运行时间无限制，直到脚本运行完毕
        session_write_close(); // 主动关闭session读写保护来解除锁定，避免其他访问无法读写session造成阻塞引起假死
        if (empty($_FILES)){
            return $this->response->setJSON(['status' => -1, 'msg' => '没有上传文件！']);
        };
        $uppath = 'data/upfile/invoice/excel_temp/';
        $temp_file = $_FILES['file']['tmp_name'];
        $target_file = date("YmdHis") . rand(1000, 9000) . '.' . strrev(explode('.', strrev($_FILES['file']['name']))[0]);
        if (!is_dir($uppath)) {
            @umask(0);
            $ret = @mkdir($uppath, 0777);
            if ($ret === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '没有权限创建用于保存的文件夹！']);
            }
        }
        move_uploaded_file($temp_file, $uppath . $target_file);
        $this->myservice->log('导入了税务局全量发票表格（我方收取）：' . $_FILES['file']['name']);
        // 调用spreadsheet处理表格
        $inputFileName = $uppath . $target_file; // 要读取的xls文件路径
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);
        $sheet = $spreadsheet->getSheet(1); // 当前工作表
        $highestRow = $sheet->getHighestRow(); // 取得总行数
        $highestColumn = $sheet->getHighestColumn(); // 取得总列数(格式：'AA')
        $columns = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn); //将数据总列数(格式：'AA')转换成数字列数(格式：27)表示
        $sheetData = $sheet->toArray(null, true, true, true); // 当前活动表格调用toArray方法，得到表格的二维数组
        for ($i = 2; $i <= $highestRow; $i++) {
            if ($sheetData[$i]['D']) {
                $us = $this->db->table('company')->select('id')->where('no', $sheetData[$i]['G'])->get()->getRowArray()['id'] ?? 0;
                if ($us > 0) {
                    if (strlen($sheetData[$i]['D']) < 20) {
                        $code = trim($sheetData[$i]['B']);
                        $no = trim($sheetData[$i]['C']);
                    } else {
                        $code = '';
                        $no = trim($sheetData[$i]['D']);
                    }
                    $invoice = $this->db->table('invoice')
                        ->groupStart()
                            ->where('CONCAT(code,no)', $code . $no, false)
                            ->orGroupStart()
                                ->like('no', $this->db->escapeLikeString(substr($no, -6, 6)))
                                ->where('amount', abs((float)$sheetData[$i]['L']))
                            ->groupEnd()
                            ->where('isDelete', 0)
                        ->groupEnd()
                        ->get()
                        ->getRowArray();
                    if ($invoice) {
                        $this->db->table('invoice')->where('id', $invoice['id'])->update([
                            'type' => strpos($sheetData[$i]['N'], '专用') === false ? 0 : 1,
                            'code' => $code,
                            'no' => $no,
                            'date' => date('Y-m-d', strtotime($sheetData[$i]['I'])),
                            'amount' => abs((float)$sheetData[$i]['L']),
                            'us' => $us,
                            'otherSide' => $sheetData[$i]['F'],
                            'inoutType' => 1,
                            'transType' => $invoice['transType'] > 0 ? $invoice['transType'] : 101101,
                            'status1' => (float)$sheetData[$i]['L'] < 0 ? '已红冲' : $sheetData[$i]['O'],
                            'status2' => $sheetData[$i]['Q']
                        ]);
                    } else {
                        if ((float)$sheetData[$i]['L'] > 0 && $sheetData[$i]['O'] == '正常') {
                            $this->db->table('invoice')->insert([
                                'type' => strpos($sheetData[$i]['N'], '专用') === false ? 0 : 1,
                                'code' => $code,
                                'no' => $no,
                                'date' => date('Y-m-d', strtotime($sheetData[$i]['I'])),
                                'amount' => abs((float)$sheetData[$i]['L']),
                                'us' => $us,
                                'otherSide' => $sheetData[$i]['F'],
                                'inoutType' => 1,
                                'transType' => 101101,
                                'status1' => (float)$sheetData[$i]['L'] < 0 ? '已红冲' : $sheetData[$i]['O'],
                                'status2' => $sheetData[$i]['Q'],
                                'uName' => '系统',
                                'createTime' => date('Y-m-d H:i:s')
                            ]);
                        }
                    }
                } else {
                    return $this->response->setJSON(['status' => -1, 'msg' => '购方非我方主体，请检查是否导入错误！']);
                }
            }
        }
        return $this->response->setJSON(['status' => 200, 'msg' => '导入并处理成功！']);
    }

    // 导入税务局全量发票表格（我方开出）
    public function uploadExcel_2()
    {
        if (!$this->myservice->verifyRight('INVOICE_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Invoice/uploadExcel_2 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        set_time_limit(0); // 设置脚本运行时间无限制，直到脚本运行完毕
        session_write_close(); // 主动关闭session读写保护来解除锁定，避免其他访问无法读写session造成阻塞引起假死
        if (empty($_FILES)){
            return $this->response->setJSON(['status' => -1, 'msg' => '没有上传文件！']);
        };
        $uppath = 'data/upfile/invoice/excel_temp/';
        $temp_file = $_FILES['file']['tmp_name'];
        $target_file = date("YmdHis") . rand(1000, 9000) . '.' . strrev(explode('.', strrev($_FILES['file']['name']))[0]);
        if (!is_dir($uppath)) {
            @umask(0);
            $ret = @mkdir($uppath, 0777);
            if ($ret === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '没有权限创建用于保存的文件夹！']);
            }
        }
        move_uploaded_file($temp_file, $uppath . $target_file);
        $this->myservice->log('导入了税务局全量发票表格（我方开出）：' . $_FILES['file']['name']);
        // 调用spreadsheet处理表格
        $inputFileName = $uppath . $target_file; // 要读取的xls文件路径
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);
        $sheet = $spreadsheet->getSheet(1); // 当前工作表
        $highestRow = $sheet->getHighestRow(); // 取得总行数
        $highestColumn = $sheet->getHighestColumn(); // 取得总列数(格式：'AA')
        $columns = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn); //将数据总列数(格式：'AA')转换成数字列数(格式：27)表示
        $sheetData = $sheet->toArray(null, true, true, true); // 当前活动表格调用toArray方法，得到表格的二维数组
        for ($i = 2; $i <= $highestRow; $i++) {
            if ($sheetData[$i]['D']) {
                $us = $this->db->table('company')->select('id')->where('no', $sheetData[$i]['E'])->get()->getRowArray()['id'] ?? 0;
                if ($us > 0) {
                    if (strlen($sheetData[$i]['D']) < 20) {
                        $code = trim($sheetData[$i]['B']);
                        $no = trim($sheetData[$i]['C']);
                    } else {
                        $code = '';
                        $no = trim($sheetData[$i]['D']);
                    }
                    $invoice = $this->db->table('invoice')
                        ->groupStart()
                            ->where('CONCAT(code,no)', $code . $no, false)
                            ->orGroupStart()
                                ->like('no', $this->db->escapeLikeString(substr($no, -6, 6)))
                                ->where('amount', abs((float)$sheetData[$i]['L']))
                            ->groupEnd()
                            ->where('isDelete', 0)
                        ->groupEnd()
                        ->get()
                        ->getRowArray();
                    if ($invoice) {
                        $this->db->table('invoice')->where('id', $invoice['id'])->update([
                            'type' => strpos($sheetData[$i]['N'], '专用') === false ? 0 : 1,
                            'code' => $code,
                            'no' => $no,
                            'date' => date('Y-m-d', strtotime($sheetData[$i]['I'])),
                            'amount' => abs((float)$sheetData[$i]['L']),
                            'us' => $us ?? $invoice['us'],
                            'otherSide' => $sheetData[$i]['H'],
                            'inoutType' => 0,
                            'transType' => $invoice['transType'] > 0 ? $invoice['transType'] : 101201,
                            'status1' => (float)$sheetData[$i]['L'] < 0 ? '已红冲' : $sheetData[$i]['O'],
                            'status2' => $sheetData[$i]['Q']
                        ]);
                    } else {
                        if ((float)$sheetData[$i]['L'] > 0 && $sheetData[$i]['O'] == '正常') {
                            $this->db->table('invoice')->insert([
                                'type' => strpos($sheetData[$i]['N'], '专用') === false ? 0 : 1,
                                'code' => $code,
                                'no' => $no,
                                'date' => date('Y-m-d', strtotime($sheetData[$i]['I'])),
                                'amount' => abs((float)$sheetData[$i]['L']),
                                'us' => $us ?? 1,
                                'otherSide' => $sheetData[$i]['H'],
                                'inoutType' => 0,
                                'transType' => 101201,
                                'status1' => (float)$sheetData[$i]['L'] < 0 ? '已红冲' : $sheetData[$i]['O'],
                                'status2' => $sheetData[$i]['Q'],
                                'uName' => '系统',
                                'createTime' => date('Y-m-d H:i:s')
                            ]);
                        }
                    }
                } else {
                    return $this->response->setJSON(['status' => -1, 'msg' => '销方非我方主体，请检查是否导入错误！']);
                }
            }
        }
        return $this->response->setJSON(['status' => 200, 'msg' => '导入并处理成功！']);
    }


    // 上传发票自动识别
    public function uploadInvoice()
    {
        set_time_limit(0);
        session_write_close();
        if (empty($_FILES)){
            return $this->response->setJSON(['status' => -1, 'msg' => '没有上传文件！']);
        };
        $uppath = 'data/upfile/invoice/';
        $temp_file = $_FILES['file']['tmp_name'];
        $fileType = strtolower(strrev(explode('.', strrev($_FILES['file']['name']))[0]));
        $target_file = date("YmdHis") . rand(1000, 9000) . '.' . $fileType;
        if (!is_dir($uppath)) {
            @umask(0);
            $ret = @mkdir($uppath, 0777);
            if ($ret === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '没有权限创建用于保存的文件夹']);
            }
        }
        move_uploaded_file($temp_file, $uppath . $target_file);
        $inoutType = $this->request->getGetPost('inoutType');
        $billNo = $this->request->getGetPost('billNo');
        // 开始识别
        if ($fileType == 'pdf' || $fileType == 'ofd') {
            // 如果是ofd，先转为pdf
            if ($fileType == 'ofd') {
                $output = [];
                exec(ROOTPATH . 'tool/ofd2pdf/ofd2pdf.exe ' . FCPATH . $uppath . $target_file, $output);
                if (is_countable($output) && count($output) == 2) {
                    $lastmsg = mb_convert_encoding($output[1], "UTF-8", "GBK"); //转换编码
                    if (strpos($lastmsg, '#完成') === 0) {
                        $target_file = $target_file . '.pdf';
                    } else {
                        return $this->response->setJSON(['status' => -1, 'msg' => 'ofd转pdf失败！']);
                    }
                } else {
                    return $this->response->setJSON(['status' => -1, 'msg' => 'ofd转pdf失败！']);
                }
            }
            // 引用PdfParser库
            $parser = new \Smalot\PdfParser\Parser();
            $pdf = $parser->parseFile($uppath . $target_file);
            // 为了兼容多页发票，统一取最后一页来提取文本
            $pages = $pdf->getPages();
            $pageLen = count($pages);
            $text = $pages[$pageLen - 1]->getText();
            // 先清除制表符、空格，再按换行符打散成数组
            $arr = preg_split("/[\r\n]+/", str_replace(["\x06", "\0", "	", " "], '', $text));
            // 开始识别
            if (is_array($arr)) {
                $type = 0;
                $code = '';
                $no = '';
                $date = '';
                $amount = '';
                $us = 0;
                $otherSide = '';
                // 先在本地识别所需信息
                foreach ($arr as $k => $v) {
                    // 识别发票类别
                    if (strpos($v, '专用发票') !== false) {
                        $type = 1;
                    }
                    // 识别发票号码
                    if (ctype_digit($v) && strlen($v) === 20 && $no == '') {
                        $no = $v;
                    } elseif (preg_match('/电子发票（普通发票）发票号码：[0-9]{20}$/', $v) && $no == '') {
                        $no = str_replace('电子发票（普通发票）发票号码：', '', $v);
                    } elseif (preg_match('/电子发票（普通发票）发票号码:[0-9]{20}$/', $v) && $no == '') {
                        $no = str_replace('电子发票（普通发票）发票号码:', '', $v);
                    } elseif (preg_match('/发票号码:[0-9]{20}$/', $v) && $no == '') {
                        $no = str_replace('发票号码:', '', $v);
                    }
                    // 识别开票日期
                    if (ctype_digit(str_replace(['开票日期', ':', '：', '年', '月', '日'], '', $v)) && strlen(str_replace(['开票日期', ':', '：', '年', '月', '日'], '', $v)) == 8 && $date == '' && $date == '') {
                        $date = date('Y-m-d', strtotime(str_replace(['开票日期', ':', '：', '年', '月', '日'], '', $v)));
                    }
                    // 识别价税合计
                    if (preg_match('/圆.*¥.*\./', $v) && $amount == '') {
                        $amountTemp = [];
                        if (preg_match('/¥(\d+(\.\d+)?)/', $v, $amountTemp)) {
                            $amount = $amountTemp[1];
                        }
                    } elseif (preg_match('/元.*￥.*\./', $v) && $amount == '') {
                        $amountTemp = [];
                        if (preg_match('/￥(\d+(\.\d+)?)/', $v, $amountTemp)) {
                            $amount = $amountTemp[1];
                        }
                    } elseif (preg_match('/写.*小.*¥.*\./', $v) && $amount == '') {
                        if ($amount == '') {
                            $amountTemp = [];
                            if (preg_match('/¥((\d+(\.\d+)?))/', $v, $amountTemp)) {
                                $amount = $amountTemp[1];
                            }
                        }
                    } elseif (substr_count($v, '¥') == 1 && $amount == '') {
                        if ($amount == '') {
                            $amountTemp = [];
                            if (preg_match('/¥((\d+(\.\d+)?))/', $v, $amountTemp)) {
                                $amount = $amountTemp[1];
                            }
                        }
                    } elseif (preg_match('/价税合计.*￥.*\./', $v) && $amount == '') {
                        $amountTemp = [];
                        if (preg_match('/￥(\d+(\.\d+)?)/', $v, $amountTemp)) {
                            $amount = $amountTemp[1];
                        }
                    } elseif (preg_match('/\(小写\)a.*\./', $v) && $amount == '') {
                        $amountTemp = [];
                        if (preg_match('/\(小写\)a(\d+(\.\d+)?)/', $v, $amountTemp)) {
                            $amount = $amountTemp[1];
                        }
                    } elseif ($type == 2 && $v == '￥' && $amount == '') {
                        $amount = $arr[$k + 1];
                    }
                }
                if (count($arr) >= 17 && ctype_digit($arr[2]) && strlen($arr[2]) === 20) {
                    if ($inoutType == '1') { // 我方收取
                        if (preg_match('/^[A-Z0-9]{18}$/', str_replace(['统一社会信用代码/纳税人识别号', ':', '：'], '', $arr[11])) && preg_match('/^[A-Z0-9]{18}$/', str_replace(['统一社会信用代码/纳税人识别号', ':', '：'], '', $arr[18]))) {
                            // 伍陆公司改名了，做临时紧急适配
                            if (strpos($arr[10], '伍陆电子（重庆）有限责任公司') !== false) {
                                $arr[10] = str_replace('伍陆电子（重庆）有限责任公司', '重庆伍陆电子有限责任公司', $arr[10]);
                            }
                            // 紧急适配完毕
                            $us = intval($this->db->table('company')->select('id')->where(['fullName' => str_replace(['名称', ':', '：'], '', $arr[10]), 'no' => str_replace(['统一社会信用代码/纳税人识别号', ':', '：'], '', $arr[11])])->get()->getRowArray()['id'] ?? 0);
                            $otherSide = str_replace(['名称', ':', '：'], '', $arr[17]);
                        } elseif (preg_match('/^[A-Z0-9]{36}$/', str_replace(['统一社会信用代码/纳税人识别号', ':', '：'], '', $arr[17]))) { // 合并读取了，要拆分出来
                            $companyNo = substr(str_replace(['统一社会信用代码/纳税人识别号', ':', '：'], '', $arr[10]), 0, 18); // 从前往后截取18位，代表采购方（我方）纳税人识别号
                            $companyInfo = $this->db->table('company')->where(['no' => $companyNo])->get()->getRowArray();
                            if ($companyInfo && strpos($arr[13], $companyInfo['fullName']) !== false) {
                                $us = intval($companyInfo['id']);
                                $otherSide = str_replace($companyInfo['fullName'], '', $arr[13]);
                            }
                        } else {
                            $us = intval($this->db->table('company')->select('id')->where(['fullName' => str_replace(['名称', ':', '：'], '', $arr[9]), 'no' => str_replace(['统一社会信用代码/纳税人识别号', ':', '：'], '', $arr[10])])->get()->getRowArray()['id'] ?? 0);
                            $otherSide = str_replace(['名称', ':', '：'], '', $arr[16]);
                        }
                    } else { // 我方开出
                        if (preg_match('/^[A-Z0-9]{18}$/', str_replace(['统一社会信用代码/纳税人识别号', ':', '：'], '', $arr[11])) && preg_match('/^[A-Z0-9]{18}$/', str_replace(['统一社会信用代码/纳税人识别号', ':', '：'], '', $arr[18]))) {
                            $us = intval($this->db->table('company')->select('id')->where(['fullName' => str_replace(['名称', ':', '：'], '', $arr[17]), 'no' => str_replace(['统一社会信用代码/纳税人识别号', ':', '：'], '', $arr[18])])->get()->getRowArray()['id'] ?? 0);
                            $otherSide = str_replace(['名称', ':', '：'], '', $arr[10]);
                        } elseif (preg_match('/^[A-Z0-9]{36}$/', str_replace(['统一社会信用代码/纳税人识别号', ':', '：'], '', $arr[17]))) { // 合并读取了，要拆分出来
                            $companyNo = substr(str_replace(['统一社会信用代码/纳税人识别号', ':', '：'], '', $arr[10]), -18); // 从后往前截取18位，代表销售方（我方）纳税人识别号
                            $companyInfo = $this->db->table('company')->where(['no' => $companyNo])->get()->getRowArray();
                            if ($companyInfo && strpos($arr[13], $companyInfo['fullName']) !== false) {
                                $us = intval($companyInfo['id']);
                                $otherSide = str_replace($companyInfo['fullName'], '', $arr[13]);
                            }
                        } else {
                            $us = intval($this->db->table('company')->select('id')->where(['fullName' => str_replace(['名称', ':', '：'], '', $arr[16]), 'no' => str_replace(['统一社会信用代码/纳税人识别号', ':', '：'], '', $arr[17])])->get()->getRowArray()['id'] ?? 0);
                            $otherSide = str_replace(['名称', ':', '：'], '', $arr[9]);
                        }
                    }
                } elseif (count($arr) >= 16 && ctype_digit($arr[11]) && strlen($arr[11]) === 20) {
                    if ($inoutType == '1') { // 我方收取
                        if (preg_match('/^[A-Z0-9]{36}$/', $arr[14])) { // 合并读取了，要拆分出来
                            $companyNo = substr($arr[14], 0, 18); // 从前往后截取18位，代表采购方（我方）纳税人识别号
                            $companyInfo = $this->db->table('company')->where(['no' => $companyNo])->get()->getRowArray();
                            if ($companyInfo && strpos($arr[13], $companyInfo['fullName']) !== false) {
                                $us = intval($companyInfo['id']);
                                $otherSide = str_replace($companyInfo['fullName'], '', $arr[13]);
                            }
                        } else {
                            $us = intval($this->db->table('company')->select('id')->where(['fullName' => $arr[13], 'no' => $arr[14]])->get()->getRowArray()['id'] ?? 0);
                            $otherSide = $arr[15];
                        }
                    } else { // 我方开出
                        if (preg_match('/^[A-Z0-9]{36}$/', $arr[14])) { // 合并读取了，要拆分出来
                            $companyNo = substr($arr[14], -18); // 从后往前截取18位，代表销售方（我方）纳税人识别号
                            $companyInfo = $this->db->table('company')->where(['no' => $companyNo])->get()->getRowArray();
                            if ($companyInfo && strpos($arr[13], $companyInfo['fullName']) !== false) {
                                $us = intval($companyInfo['id']);
                                $otherSide = str_replace($companyInfo['fullName'], '', $arr[13]);
                            }
                        } else {
                            $us = intval($this->db->table('company')->select('id')->where(['fullName' => $arr[15], 'no' => $arr[16]])->get()->getRowArray()['id'] ?? 0);
                            $otherSide = $arr[13];
                        }
                    }
                } elseif (count($arr) >= 21 && ctype_digit($arr[16]) && strlen($arr[16]) === 20) {
                    if ($inoutType == '1') { // 我方收取
                        if (preg_match('/^[A-Z0-9]{36}$/', $arr[19])) { // 合并读取了，要拆分出来
                            $companyNo = substr($arr[19], 0, 18); // 从前往后截取18位，代表采购方（我方）纳税人识别号
                            $companyInfo = $this->db->table('company')->where(['no' => $companyNo])->get()->getRowArray();
                            // 紧急适配完毕
                            if ($companyInfo && strpos($arr[18], $companyInfo['fullName']) !== false) {
                                $us = intval($companyInfo['id']);
                                $otherSide = str_replace($companyInfo['fullName'], '', $arr[18]);
                            }
                        } else {
                            $us = intval($this->db->table('company')->select('id')->where(['fullName' => $arr[18], 'no' => $arr[19]])->get()->getRowArray()['id'] ?? 0);
                            $otherSide = $arr[20];
                        }
                    } else { // 我方开出
                        if (preg_match('/^[A-Z0-9]{36}$/', $arr[19])) { // 合并读取了，要拆分出来
                            $companyNo = substr($arr[19], -18); // 从后往前截取18位，代表销售方（我方）纳税人识别号
                            $companyInfo = $this->db->table('company')->where(['no' => $companyNo])->get()->getRowArray();
                            if ($companyInfo && strpos($arr[18], $companyInfo['fullName']) !== false) {
                                $us = intval($companyInfo['id']);
                                $otherSide = str_replace($companyInfo['fullName'], '', $arr[18]);
                            }
                        } else {
                            $us = intval($this->db->table('company')->select('id')->where(['fullName' => $arr[20], 'no' => $arr[21]])->get()->getRowArray()['id'] ?? 0);
                            $otherSide = $arr[18];
                        }
                    }
                } elseif (count($arr) >= 21 && ctype_digit($arr[18]) && strlen($arr[18]) === 20) {
                    if ($inoutType == '1') { // 我方收取
                        if (preg_match('/^[A-Z0-9]{36}$/', $arr[21])) { // 合并读取了，要拆分出来
                            $companyNo = substr($arr[21], 0, 18); // 从前往后截取18位，代表采购方（我方）纳税人识别号
                            $companyInfo = $this->db->table('company')->where(['no' => $companyNo])->get()->getRowArray();
                            if ($companyInfo && strpos($arr[20], $companyInfo['fullName']) !== false) {
                                $us = intval($companyInfo['id']);
                                $otherSide = str_replace($companyInfo['fullName'], '', $arr[20]);
                            }
                        } else {
                            $us = intval($this->db->table('company')->select('id')->where(['fullName' => $arr[20], 'no' => $arr[21]])->get()->getRowArray()['id'] ?? 0);
                            $otherSide = $arr[22];
                        }
                    } else { // 我方开出
                        if (preg_match('/^[A-Z0-9]{36}$/', $arr[21])) { // 合并读取了，要拆分出来
                            $companyNo = substr($arr[21], -18); // 从后往前截取18位，代表销售方（我方）纳税人识别号
                            $companyInfo = $this->db->table('company')->where(['no' => $companyNo])->get()->getRowArray();
                            if ($companyInfo && strpos($arr[20], $companyInfo['fullName']) !== false) {
                                $us = intval($companyInfo['id']);
                                $otherSide = str_replace($companyInfo['fullName'], '', $arr[20]);
                            }
                        } else {
                            $us = intval($this->db->table('company')->select('id')->where(['fullName' => $arr[22], 'no' => $arr[23]])->get()->getRowArray()['id'] ?? 0);
                            $otherSide = $arr[20];
                        }
                    }
                } elseif (count($arr) >= 26 && ctype_digit($arr[21]) && strlen($arr[21]) === 20) {
                    if ($inoutType == '1') { // 我方收取
                        if (preg_match('/^[A-Z0-9]{18}$/', $arr[23]) && preg_match('/^[A-Z0-9]{18}$/', $arr[28])) {
                            $us = intval($this->db->table('company')->select('id')->where(['fullName' => $arr[22], 'no' => $arr[23]])->get()->getRowArray()['id'] ?? 0);
                            $otherSide = $arr[27];
                        } elseif (preg_match('/^[A-Z0-9]{36}$/', $arr[24])) { //合并读取了，要拆分出来
                            $companyNo = substr($arr[24], 0, 18); // 从前往后截取18位，代表采购方（我方）纳税人识别号
                            $companyInfo = $this->db->table('company')->where(['no' => $companyNo])->get()->getRowArray();
                            if ($companyInfo && strpos($arr[23], $companyInfo['fullName']) !== false) {
                                $us = intval($companyInfo['id']);
                                $otherSide = str_replace($companyInfo['fullName'], '', $arr[23]);
                            }
                        } else {
                            $us = intval($this->db->table('company')->select('id')->where(['fullName' => $arr[23], 'no' => $arr[24]])->get()->getRowArray()['id'] ?? 0);
                            $otherSide = $arr[25];
                        }
                    } else { // 我方开出
                        if (preg_match('/^[A-Z0-9]{18}$/', $arr[23]) && preg_match('/^[A-Z0-9]{18}$/', $arr[28])) {
                            $us = intval($this->db->table('company')->select('id')->where(['fullName' => $arr[27], 'no' => $arr[28]])->get()->getRowArray()['id'] ?? 0);
                            $otherSide = $arr[22];
                        } elseif (preg_match('/^[A-Z0-9]{36}$/', $arr[24])) { // 合并读取了，要拆分出来
                            $companyNo = substr($arr[24], -18); // 从后往前截取18位，代表销售方（我方）纳税人识别号
                            $companyInfo = $this->db->table('company')->where(['no' => $companyNo])->get()->getRowArray();
                            if ($companyInfo && strpos($arr[53], $companyInfo['fullName']) !== false) {
                                $us = intval($companyInfo['id']);
                                $otherSide = str_replace($companyInfo['fullName'], '', $arr[23]);
                            }
                        } elseif (preg_match('/^[A-Z0-9]{18}$/', $arr[24]) && !preg_match('/^[A-Z0-9]{18}$/', $arr[26])) { // 合并读取了，且购方识别号缺失
                            $companyInfo = $this->db->table('company')->where(['no' => $arr[24]])->get()->getRowArray();
                            if ($companyInfo && strpos($arr[23], $companyInfo['fullName']) !== false) {
                                $us = intval($companyInfo['id']);
                                $otherSide = str_replace($companyInfo['fullName'], '', $arr[23]);
                            }
                        } elseif (preg_match('/^[A-Z0-9]{18}$/', $arr[25]) && preg_match('/^[A-Z0-9]{18}$/', $arr[27])) { // 购方名称太长导致换行
                            $us = intval($this->db->table('company')->select('id')->where(['fullName' => $arr[26], 'no' => $arr[27]])->get()->getRowArray()['id'] ?? 0);
                            $otherSide = $arr[23] . $arr[24];
                        } else {
                            $us = intval($this->db->table('company')->select('id')->where(['fullName' => $arr[25], 'no' => $arr[26]])->get()->getRowArray()['id'] ?? 0);
                            $otherSide = $arr[23];
                        }
                    }
                } elseif (count($arr) >= 27 && ctype_digit($arr[22]) && strlen($arr[22]) === 20) {
                    if ($inoutType == '1') { // 我方收取
                        if (preg_match('/^[A-Z0-9]{36}$/', $arr[25])) { // 合并读取了，要拆分出来
                            $companyNo = substr($arr[25], 0, 18); // 从前往后截取18位，代表采购方（我方）纳税人识别号
                            $companyInfo = $this->db->table('company')->where(['no' => $companyNo])->get()->getRowArray();
                            if ($companyInfo && strpos($arr[24], $companyInfo['fullName']) !== false) {
                                $us = intval($companyInfo['id']);
                                $otherSide = str_replace($companyInfo['fullName'], '', $arr[24]);
                            }
                        } else {
                            $us = intval($this->db->table('company')->select('id')->where(['fullName' => $arr[24], 'no' => $arr[25]])->get()->getRowArray()['id'] ?? 0);
                            $otherSide = $arr[26];
                        }
                    } else { // 我方开出
                        if (preg_match('/^[A-Z0-9]{36}$/', $arr[25])) { // 合并读取了，要拆分出来
                            $companyNo = substr($arr[25], -18); // 从后往前截取18位，代表销售方（我方）纳税人识别号
                            $companyInfo = $this->db->table('company')->where(['no' => $companyNo])->get()->getRowArray();
                            if ($companyInfo && strpos($arr[24], $companyInfo['fullName']) !== false) {
                                $us = intval($companyInfo['id']);
                                $otherSide = str_replace($companyInfo['fullName'], '', $arr[24]);
                            }
                        } else {
                            $us = intval($this->db->table('company')->select('id')->where(['fullName' => $arr[26], 'no' => $arr[27]])->get()->getRowArray()['id'] ?? 0);
                            $otherSide = $arr[24];
                        }
                    }
                } elseif (count($arr) >= 34 && ctype_digit($arr[30]) && strlen($arr[30]) === 20) {
                    if ($inoutType == '1') { // 我方收取
                        if (preg_match('/^[A-Z0-9]{36}$/', $arr[27])) { // 合并读取了，要拆分出来
                            $companyNo = substr($arr[27], 0, 18); // 从前往后截取18位，代表采购方（我方）纳税人识别号
                            $companyInfo = $this->db->table('company')->where(['no' => $companyNo])->get()->getRowArray();
                            if ($companyInfo && strpos($arr[26], $companyInfo['fullName']) !== false) {
                                $us = intval($companyInfo['id']);
                                $otherSide = str_replace($companyInfo['fullName'], '', $arr[26]);
                            }
                        } else {
                            $us = intval($this->db->table('company')->select('id')->where(['fullName' => $arr[26], 'no' => $arr[27]])->get()->getRowArray()['id'] ?? 0);
                            $otherSide = $arr[28];
                        }
                    } else { // 我方开出
                        if (preg_match('/^[A-Z0-9]{36}$/', $arr[27])) { // 合并读取了，要拆分出来
                            $companyNo = substr($arr[27], -18); // 从后往前截取18位，代表销售方（我方）纳税人识别号
                            $companyInfo = $this->db->table('company')->where(['no' => $companyNo])->get()->getRowArray();
                            if ($companyInfo && strpos($arr[26], $companyInfo['fullName']) !== false) {
                                $us = intval($companyInfo['id']);
                                $otherSide = str_replace($companyInfo['fullName'], '', $arr[26]);
                            }
                        } else {
                            $us = intval($this->db->table('company')->select('id')->where(['fullName' => $arr[28], 'no' => $arr[29]])->get()->getRowArray()['id'] ?? 0);
                            $otherSide = $arr[26];
                        }
                    }
                } elseif (count($arr) >= 39 && ctype_digit($arr[34]) && strlen($arr[34]) === 20) {
                    $dateTemp = preg_split('/\D+/', $arr[35], -1, PREG_SPLIT_NO_EMPTY);
                    $date = implode('-', $dateTemp);
                    if ($inoutType == '1') { // 我方收取
                        if (preg_match('/^[A-Z0-9]{36}$/', $arr[37])) { // 合并读取了，要拆分出来
                            $companyNo = substr($arr[37], 0, 18); // 从前往后截取18位，代表采购方（我方）纳税人识别号
                            $companyInfo = $this->db->table('company')->where(['no' => $companyNo])->get()->getRowArray();
                            if ($companyInfo) {
                                $us = intval($companyInfo['id']);
                                $otherSide = substr($arr[37], -18);
                                $companyFullName = $this->getComoanyFullName($otherSide);
                                if ($companyFullName) {
                                    $otherSide = $companyFullName;
                                } else {
                                    log_message('error', '发票文件：' . $_FILES['file']['name'] . ' 未识别到对方公司名称，且系统通过统一社会信用代码反查对方公司名称失败！');
                                }
                            }
                        } elseif (preg_match('/^[A-Z0-9]{36}$/', $arr[38])) { // 合并读取了，要拆分出来
                            $companyNo = substr($arr[38], 0, 18); // 从前往后截取18位，代表采购方（我方）纳税人识别号
                            $companyInfo = $this->db->table('company')->where(['no' => $companyNo])->get()->getRowArray();
                            if ($companyInfo) {
                                $us = intval($companyInfo['id']);
                                $otherSide = substr($arr[38], -18);
                                $companyFullName = $this->getComoanyFullName($otherSide);
                                if ($companyFullName) {
                                    $otherSide = $companyFullName;
                                } else {
                                    log_message('error', '发票文件：' . $_FILES['file']['name'] . ' 未识别到对方公司名称，且系统通过统一社会信用代码反查对方公司名称失败！');
                                }
                            }
                        } else {
                            $us = intval($this->db->table('company')->select('id')->where(['no' => $arr[37]])->get()->getRowArray()['id'] ?? 0);
                            $otherSide = $arr[39];
                            $companyFullName = $this->getComoanyFullName($otherSide);
                            if ($companyFullName) {
                                $otherSide = $companyFullName;
                            } else {
                                log_message('error', '发票文件：' . $_FILES['file']['name'] . ' 未识别到对方公司名称，且系统通过统一社会信用代码反查对方公司名称失败！');
                            }
                        }
                    } else { // 我方开出
                        if (preg_match('/^[A-Z0-9]{36}$/', $arr[37])) { // 合并读取了，要拆分出来
                            $companyNo = substr($arr[37], -18); // 从后往前截取18位，代表销售方（我方）纳税人识别号
                            $companyInfo = $this->db->table('company')->where(['no' => $companyNo])->get()->getRowArray();
                            if ($companyInfo) {
                                $us = intval($companyInfo['id']);
                                $otherSide = substr($arr[37], 0, 18);
                                $companyFullName = $this->getComoanyFullName($otherSide);
                                if ($companyFullName) {
                                    $otherSide = $companyFullName;
                                } else {
                                    log_message('error', '发票文件：' . $_FILES['file']['name'] . ' 未识别到对方公司名称，且系统通过统一社会信用代码反查对方公司名称失败！');
                                }
                            }
                        } elseif (preg_match('/^[A-Z0-9]{36}$/', $arr[38])) { // 合并读取了，要拆分出来
                            $companyNo = substr($arr[38], 0, 18); // 从前往后截取18位，代表采购方（我方）纳税人识别号
                            $companyInfo = $this->db->table('company')->where(['no' => $companyNo])->get()->getRowArray();
                            if ($companyInfo) {
                                $us = intval($companyInfo['id']);
                                $otherSide = substr($arr[38], -18);
                                $companyFullName = $this->getComoanyFullName($otherSide);
                                if ($companyFullName) {
                                    $otherSide = $companyFullName;
                                } else {
                                    log_message('error', '发票文件：' . $_FILES['file']['name'] . ' 未识别到对方公司名称，且系统通过统一社会信用代码反查对方公司名称失败！');
                                }
                            }
                        } else {
                            $us = intval($this->db->table('company')->select('id')->where(['no' => $arr[39]])->get()->getRowArray()['id'] ?? 0);
                            $otherSide = $arr[37];
                            $companyFullName = $this->getComoanyFullName($otherSide);
                            if ($companyFullName) {
                                $otherSide = $companyFullName;
                            } else {
                                log_message('error', '发票文件：' . $_FILES['file']['name'] . ' 未识别到对方公司名称，且系统通过统一社会信用代码反查对方公司名称失败！');
                            }
                        }
                    }
                } elseif (count($arr) >= 42 && ctype_digit($arr[37]) && strlen($arr[37]) === 20) {
                    $dateTemp = preg_split('/\D+/', $arr[38], -1, PREG_SPLIT_NO_EMPTY);
                    $date = implode('-', $dateTemp);
                    if ($inoutType == '1') { // 我方收取
                        if (preg_match('/^[A-Z0-9]{36}$/', $arr[40])) { // 合并读取了，要拆分出来
                            $companyNo = substr($arr[40], 0, 18); // 从前往后截取18位，代表采购方（我方）纳税人识别号
                            $companyInfo = $this->db->table('company')->where(['no' => $companyNo])->get()->getRowArray();
                            if ($companyInfo) {
                                $us = intval($companyInfo['id']);
                                $otherSide = substr($arr[40], -18);
                                $companyFullName = $this->getComoanyFullName($otherSide);
                                if ($companyFullName) {
                                    $otherSide = $companyFullName;
                                } else {
                                    log_message('error', '发票文件：' . $_FILES['file']['name'] . ' 未识别到对方公司名称，且系统通过统一社会信用代码反查对方公司名称失败！');
                                }
                            }
                        } else {
                            $us = intval($this->db->table('company')->select('id')->where(['no' => $arr[40]])->get()->getRowArray()['id'] ?? 0);
                            $otherSide = $arr[42];
                            $companyFullName = $this->getComoanyFullName($otherSide);
                            if ($companyFullName) {
                                $otherSide = $companyFullName;
                            } else {
                                log_message('error', '发票文件：' . $_FILES['file']['name'] . ' 未识别到对方公司名称，且系统通过统一社会信用代码反查对方公司名称失败！');
                            }
                        }
                    } else { // 我方开出
                        if (preg_match('/^[A-Z0-9]{36}$/', $arr[40])) { // 合并读取了，要拆分出来
                            $companyNo = substr($arr[40], -18); // 从后往前截取18位，代表销售方（我方）纳税人识别号
                            $companyInfo = $this->db->table('company')->where(['no' => $companyNo])->get()->getRowArray();
                            if ($companyInfo) {
                                $us = intval($companyInfo['id']);
                                $otherSide = substr($arr[40], 0, 18);
                                $companyFullName = $this->getComoanyFullName($otherSide);
                                if ($companyFullName) {
                                    $otherSide = $companyFullName;
                                } else {
                                    log_message('error', '发票文件：' . $_FILES['file']['name'] . ' 未识别到对方公司名称，且系统通过统一社会信用代码反查对方公司名称失败！');
                                }
                            }
                        } else {
                            $us = intval($this->db->table('company')->select('id')->where(['no' => $arr[42]])->get()->getRowArray()['id'] ?? 0);
                            $otherSide = $arr[40];
                            $companyFullName = $this->getComoanyFullName($otherSide);
                            if ($companyFullName) {
                                $otherSide = $companyFullName;
                            } else {
                                log_message('error', '发票文件：' . $_FILES['file']['name'] . ' 未识别到对方公司名称，且系统通过统一社会信用代码反查对方公司名称失败！');
                            }
                        }
                    }
                } elseif (count($arr) >= 31 && $arr[0] == '合计' && preg_match('/^[A-Z0-9]{36}$/', $arr[24])) {
                    if ($inoutType == '1') { // 我方收取
                        $companyNo = substr($arr[24], 0, 18); // 从前往后截取18位，代表采购方（我方）纳税人识别号
                        $companyInfo = $this->db->table('company')->where(['no' => $companyNo])->get()->getRowArray();
                        if ($companyInfo && strpos($arr[23], $companyInfo['fullName']) !== false) {
                            $us = intval($companyInfo['id']);
                            $otherSide = str_replace($companyInfo['fullName'], '', $arr[23]);
                        }
                    } else { // 我方开出
                        $companyNo = substr($arr[40], -18); // 从后往前截取18位，代表销售方（我方）纳税人识别号
                        $companyInfo = $this->db->table('company')->where(['no' => $companyNo])->get()->getRowArray();
                        if ($companyInfo && strpos($arr[23], $companyInfo['fullName']) !== false) {
                            $us = intval($companyInfo['id']);
                            $otherSide = str_replace($companyInfo['fullName'], '', $arr[23]);
                        }
                    }
                } elseif (count($arr) >= 16 && preg_match('/电子发票（普通发票）发票号码[：|:][0-9]{20}$/', $arr[0])) {
                    if ($inoutType == '1') { // 我方收取
                        if (preg_match('/^[A-Z0-9]{36}$/', $arr[21]) && strpos($arr[22], '名称') === 0 && strpos($arr[24], '名称') === 0) {
                            $us = intval($this->db->table('company')->select('id')->where(['fullName' => str_replace(['名称', ':', '：'], '', $arr[24]), 'no' => substr($arr[21], -18)])->get()->getRowArray()['id'] ?? 0);
                            $otherSide = str_replace(['名称', ':', '：'], '', $arr[22]);
                        }
                    } else { // 我方开出
                        if (preg_match('/^[A-Z0-9]{36}$/', $arr[21]) && strpos($arr[22], '名称') === 0 && strpos($arr[24], '名称') === 0) {
                            $us = intval($this->db->table('company')->select('id')->where(['fullName' => str_replace(['名称', ':', '：'], '', $arr[22]), 'no' => substr($arr[21], 0, 18)])->get()->getRowArray()['id'] ?? 0);
                            $otherSide = str_replace(['名称', ':', '：'], '', $arr[24]);
                        }
                    }
                } elseif (count($arr) >= 16 && preg_match('/电子发票（普通发票）发票号码[：|:][0-9]{20}$/', $arr[1])) {
                    if ($inoutType == '1') { // 我方收取
                        if (strpos($arr[8], '名称') === 0) {
                            $us = intval($this->db->table('company')->select('id')->where(['fullName' => str_replace(['名称', ':', '：'], '', $arr[8]), 'no' => str_replace(['统一社会信用代码/纳税人识别号', ':', '：'], '', $arr[9])])->get()->getRowArray()['id'] ?? 0);
                            $otherSide = str_replace(['名称', ':', '：'], '', $arr[15]);
                        } elseif (strpos($arr[9], '名称') === 0) {
                            $us = intval($this->db->table('company')->select('id')->where(['fullName' => str_replace(['名称', ':', '：'], '', $arr[9]), 'no' => str_replace(['统一社会信用代码/纳税人识别号', ':', '：'], '', $arr[10])])->get()->getRowArray()['id'] ?? 0);
                            $otherSide = str_replace(['名称', ':', '：'], '', $arr[16]);
                        }
                    } else { // 我方开出
                        if (strpos($arr[8], '名称') === 0) {
                            $us = intval($this->db->table('company')->select('id')->where(['fullName' => str_replace(['名称', ':', '：'], '', $arr[15]), 'no' => str_replace(['统一社会信用代码/纳税人识别号', ':', '：'], '', $arr[16])])->get()->getRowArray()['id'] ?? 0);
                            $otherSide = str_replace(['名称', ':', '：'], '', $arr[8]);
                        } elseif (strpos($arr[9], '名称') === 0) {
                            $us = intval($this->db->table('company')->select('id')->where(['fullName' => str_replace(['名称', ':', '：'], '', $arr[16]), 'no' => str_replace(['统一社会信用代码/纳税人识别号', ':', '：'], '', $arr[17])])->get()->getRowArray()['id'] ?? 0);
                            $otherSide = str_replace(['名称', ':', '：'], '', $arr[9]);
                        }
                    }
                } elseif (strpos($arr[0], '发票代码:') === 0 && strpos($arr[1], '发票号码:') === 0 && strpos($arr[2], '开票日期:') === 0) {
                    // 深圳电子普通发票
                    $code = str_replace('发票代码:', '', $arr[0]);
                    $no = str_replace('发票号码:', '', $arr[1]);
                    $buyer = $saler = ['no' => '', 'fullName' => ''];
                    foreach ($arr as $k => $v) {
                        if ($k == 7) {
                            $buyer['fullName'] = str_replace('名称:', '', $v);
                        }
                        if ($k == 8) {
                            $buyer['no'] = str_replace('纳税人识别号:', '', $v);
                        }
                        if ($k > 8) {
                            if (strpos($v, '名称:') === 0) {
                                $saler['fullName'] = str_replace('名称:', '', $v);
                            }
                            if (strpos($v, '纳税人识别号:') === 0) {
                                $saler['no'] = str_replace('纳税人识别号:', '', $v);
                            }
                        }
                    }
                    if ($inoutType == '1') { // 我方收取
                        $us = intval($this->db->table('company')->select('id')->where(['fullName' => $buyer['fullName'], 'no' => $buyer['no']])->get()->getRowArray()['id'] ?? 0);
                        $otherSide = $saler['fullName'];
                    } else { // 我方开出
                        $us = intval($this->db->table('company')->select('id')->where(['fullName' => $saler['fullName'], 'no' => $saler['no']])->get()->getRowArray()['id'] ?? 0);
                        $otherSide = $buyer['fullName'];
                    }
                } elseif ($arr[0] == '深圳电子普通发票' && ctype_digit($arr[38]) && strlen($arr[38]) === 12 && ctype_digit($arr[39]) && strlen($arr[39]) === 8) {
                    // 深圳电子普通发票
                    $code = $arr[38];
                    $no = $arr[39];
                    $date = date('Y-m-d', strtotime(str_replace(['年', '月', '日'], '', $arr[40])));
                    $buyer = ['no' => $arr[46], 'fullName' => $arr[45]];
                    $saler = ['no' => $arr[48], 'fullName' => $arr[47]];
                    if ($inoutType == '1') { // 我方收取
                        $us = intval($this->db->table('company')->select('id')->where(['fullName' => $buyer['fullName'], 'no' => $buyer['no']])->get()->getRowArray()['id'] ?? 0);
                        $otherSide = $saler['fullName'];
                    } else { // 我方开出
                        $us = intval($this->db->table('company')->select('id')->where(['fullName' => $saler['fullName'], 'no' => $saler['no']])->get()->getRowArray()['id'] ?? 0);
                        $otherSide = $buyer['fullName'];
                    }
                } elseif (count($arr) >= 53 && $arr[9] == '深圳增值税电子普通发票') {
                    // 深圳增值税电子普通发票
                    $code = $arr[5];
                    $no = str_replace('发票号码:', '', $arr[6]);
                    if ($inoutType == '1') { // 我方收取
                        for ($i = 53; $i < count($arr); $i++) {
                            if (preg_match('/^[A-Z0-9]{18}$/', $arr[$i])) {
                                $usNo = $arr[$i];
                                $us = intval($this->db->table('company')->select('id')->where(['no' => $usNo])->get()->getRowArray()['id'] ?? 0);
                                if ($us > 0) {
                                    break;
                                }
                            }
                        }
                        $otherSide = $arr[42];
                    } else { // 我方开出
                        $usNo = $arr[43];
                        $us = intval($this->db->table('company')->select('id')->where(['no' => $usNo])->get()->getRowArray()['id'] ?? 0);
                        for ($i = 53; $i < count($arr); $i++) {
                            if (preg_match('/^[A-Z0-9]{18}$/', $arr[$i])) {
                                $otherSide = $arr[$i];
                                $companyFullName = $this->getComoanyFullName($otherSide);
                                if ($companyFullName) {
                                    $otherSide = $companyFullName;
                                } else {
                                    log_message('error', '发票文件：' . $_FILES['file']['name'] . ' 未识别到对方公司名称，且系统通过统一社会信用代码反查对方公司名称失败！');
                                }
                            }
                        }
                    }
                } elseif (count($arr) >= 78 && ctype_digit($arr[64]) && strlen($arr[64]) === 12 && ctype_digit($arr[65]) && strlen($arr[65]) === 8) {
                    // 天津增值税电子普通发票
                    $code = $arr[64];
                    $no = $arr[65];
                    $date = date('Y-m-d', strtotime($arr[66]));
                    if ($inoutType == '1') { // 我方收取
                        if (preg_match('/^[A-Z0-9]{36}$/', $arr[74])) { // 合并读取了，要拆分出来
                            $companyNo = substr($arr[74], 0, 18); // 从前往后截取18位，代表采购方（我方）纳税人识别号
                            $companyInfo = $this->db->table('company')->select('id')->where(['no' => $companyNo])->get()->getRowArray();
                            if ($companyInfo) {
                                $us = intval($companyInfo['id']);
                                $otherSide = substr($arr[74], -18);
                                $companyFullName = $this->getComoanyFullName($otherSide);
                                if ($companyFullName) {
                                    $otherSide = $companyFullName;
                                } else {
                                    log_message('error', '发票文件：' . $_FILES['file']['name'] . ' 未识别到对方公司名称，且系统通过统一社会信用代码反查对方公司名称失败！');
                                }
                            }
                        } else {
                            $us = intval($this->db->table('company')->select('id')->where(['no' => $arr[74]])->get()->getRowArray()['id'] ?? 0);
                            $otherSide = '';
                            for ($i = 75; $i < count($arr); $i++) {
                                if (preg_match('/^[A-Z0-9]{18}$/', $arr[$i])) {
                                    $otherSide = $arr[$i];
                                    $companyFullName = $this->getComoanyFullName($otherSide);
                                    if ($companyFullName) {
                                        $otherSide = $companyFullName;
                                    } else {
                                        log_message('error', '发票文件：' . $_FILES['file']['name'] . ' 未识别到对方公司名称，且系统通过统一社会信用代码反查对方公司名称失败！');
                                    }
                                    break;
                                }
                            }
                        }
                    } else { // 我方开出
                        if (preg_match('/^[A-Z0-9]{36}$/', $arr[74])) { // 合并读取了，要拆分出来
                            $companyNo = substr($arr[74], -18); // 从后往前截取18位，代表销售方（我方）纳税人识别号
                            $companyInfo = $this->db->table('company')->select('id')->where(['no' => $companyNo])->get()->getRowArray();
                            if ($companyInfo) {
                                $us = intval($companyInfo['id']);
                                $otherSide = substr($arr[74], 0, 18);
                                $companyFullName = $this->getComoanyFullName($otherSide);
                                if ($companyFullName) {
                                    $otherSide = $companyFullName;
                                } else {
                                    log_message('error', '发票文件：' . $_FILES['file']['name'] . ' 未识别到对方公司名称，且系统通过统一社会信用代码反查对方公司名称失败！');
                                }
                            }
                        } else {
                            $us = 0;
                            for ($i = 75; $i < count($arr); $i++) {
                                if (preg_match('/^[A-Z0-9]{18}$/', $arr[$i])) {
                                    $us = intval($this->db->table('company')->select('id')->where(['no' => $arr[$i]])->get()->getRowArray()['id'] ?? 0);
                                    break;
                                }
                            }
                            $otherSide = $arr[74];
                            $companyFullName = $this->getComoanyFullName($otherSide);
                            if ($companyFullName) {
                                $otherSide = $companyFullName;
                            } else {
                                log_message('error', '发票文件：' . $_FILES['file']['name'] . ' 未识别到对方公司名称，且系统通过统一社会信用代码反查对方公司名称失败！');
                            }
                        }
                    }
                } elseif (count($arr) >= 92 && ctype_digit($arr[91]) && strlen($arr[91]) === 12 && ctype_digit($arr[92]) && strlen($arr[92]) === 8) {
                    // 江苏增值税电子普通发票
                    $code = $arr[91];
                    $no = $arr[92];
                    $dateTemp = preg_split('/\D+/', $arr[93], -1, PREG_SPLIT_NO_EMPTY);
                    $date = implode('-', $dateTemp);
                    if ($inoutType == '1') { // 我方收取
                        if (preg_match('/^[A-Z0-9]{36}$/', $arr[86])) { // 合并读取了，要拆分出来
                            $companyNo = substr($arr[86], 0, 18); // 从前往后截取18位，代表采购方（我方）纳税人识别号
                            $companyInfo = $this->db->table('company')->select('id')->where(['no' => $companyNo])->get()->getRowArray();
                            if ($companyInfo) {
                                $us = intval($companyInfo['id']);
                                $otherSide = substr($arr[86], -18);
                                $companyFullName = $this->getComoanyFullName($otherSide);
                                if ($companyFullName) {
                                    $otherSide = $companyFullName;
                                } else {
                                    log_message('error', '发票文件：' . $_FILES['file']['name'] . ' 未识别到对方公司名称，且系统通过统一社会信用代码反查对方公司名称失败！');
                                }
                            }
                        } else {
                            $us = intval($this->db->table('company')->select('id')->where(['no' => $arr[86]])->get()->getRowArray()['id'] ?? 0);
                            $otherSide = '';
                            for ($i = 87; $i < count($arr); $i++) {
                                if (preg_match('/^[A-Z0-9]{18}$/', $arr[$i])) {
                                    $otherSide = $arr[$i];
                                    $companyFullName = $this->getComoanyFullName($otherSide);
                                    if ($companyFullName) {
                                        $otherSide = $companyFullName;
                                    } else {
                                        log_message('error', '发票文件：' . $_FILES['file']['name'] . ' 未识别到对方公司名称，且系统通过统一社会信用代码反查对方公司名称失败！');
                                    }
                                    break;
                                }
                            }
                        }
                    } else { // 我方开出
                        if (preg_match('/^[A-Z0-9]{36}$/', $arr[86])) { // 合并读取了，要拆分出来
                            $companyNo = substr($arr[86], -18); // 从后往前截取18位，代表销售方（我方）纳税人识别号
                            $companyInfo = $this->db->table('company')->select('id')->where(['no' => $companyNo])->get()->getRowArray();
                            if ($companyInfo) {
                                $us = intval($companyInfo['id']);
                                $otherSide = substr($arr[86], 0, 18);
                                $companyFullName = $this->getComoanyFullName($otherSide);
                                if ($companyFullName) {
                                    $otherSide = $companyFullName;
                                } else {
                                    log_message('error', '发票文件：' . $_FILES['file']['name'] . ' 未识别到对方公司名称，且系统通过统一社会信用代码反查对方公司名称失败！');
                                }
                            }
                        } else {
                            $us = 0;
                            for ($i = 87; $i < count($arr); $i++) {
                                if (preg_match('/^[A-Z0-9]{18}$/', $arr[$i])) {
                                    $us = intval($this->db->table('company')->select('id')->where(['no' => $arr[$i]])->get()->getRowArray()['id'] ?? 0);
                                    break;
                                }
                            }
                            $otherSide = $arr[86];
                            $companyFullName = $this->getComoanyFullName($otherSide);
                            if ($companyFullName) {
                                $otherSide = $companyFullName;
                            } else {
                                log_message('error', '发票文件：' . $_FILES['file']['name'] . ' 未识别到对方公司名称，且系统通过统一社会信用代码反查对方公司名称失败！');
                            }
                        }
                    }
                } elseif (count($arr) >= 44 && $arr[0] == '单位') {
                    // 广东增值税电子普通发票
                    $buyer = $saler = '';
                    foreach ($arr as $k => $v) {
                        if (ctype_digit($v) && strlen($v) === 12 && $code == '') {
                            $code = $v;
                        }
                        if (ctype_digit($v) && strlen($v) === 8) {
                            $no = $v;
                        }
                        if (preg_match('/¥.*\./', $v)) {
                            $amountTemp = [];
                            if (preg_match('/¥(\d+(\.\d+)?)/', $v, $amountTemp)) {
                                $amount = $amountTemp[1];
                            }
                        }
                        if (preg_match('/^[A-Z0-9]{18}$/', $v)) {
                            if ($buyer == '') {
                                $buyer = $v;
                            } else {
                                $saler = $v;
                            }
                        }
                    }
                    if ($inoutType == '1') { // 我方收取
                        $companyInfo = $this->db->table('company')->where(['no' => $buyer])->get()->getRowArray();
                        if ($companyInfo) {
                            $us = intval($companyInfo['id']);
                            $otherSide = $saler;
                            $companyFullName = $this->getComoanyFullName($otherSide);
                            if ($companyFullName) {
                                $otherSide = $companyFullName;
                            } else {
                                log_message('error', '发票文件：' . $_FILES['file']['name'] . ' 未识别到对方公司名称，且系统通过统一社会信用代码反查对方公司名称失败！');
                            }
                        }
                    } else { // 我方开出
                        $companyInfo = $this->db->table('company')->where(['no' => $saler])->get()->getRowArray();
                        if ($companyInfo) {
                            $us = intval($companyInfo['id']);
                            $otherSide = $buyer;
                            $companyFullName = $this->getComoanyFullName($otherSide);
                            if ($companyFullName) {
                                $otherSide = $companyFullName;
                            } else {
                                log_message('error', '发票文件：' . $_FILES['file']['name'] . ' 未识别到对方公司名称，且系统通过统一社会信用代码反查对方公司名称失败！');
                            }
                        }
                    }
                } elseif (count($arr) >= 45 && ctype_digit($arr[30]) && strlen($arr[30]) === 12 && ctype_digit($arr[31]) && strlen($arr[31]) === 8) {
                    $code = $arr[30];
                    $no = $arr[31];
                    if ($inoutType == '1') { // 我方收取
                        if (preg_match('/^[A-Z0-9]{36}$/', $arr[35])) { // 合并读取了，要拆分出来
                            $companyNo = substr($arr[35], 0, 18); // 从前往后截取18位，代表采购方（我方）纳税人识别号
                            $companyInfo = $this->db->table('company')->where(['no' => $companyNo])->get()->getRowArray();
                            if ($companyInfo) {
                                $us = intval($companyInfo['id']);
                                $otherSide = substr($arr[35], -18);
                                $companyFullName = $this->getComoanyFullName($otherSide);
                                if ($companyFullName) {
                                    $otherSide = $companyFullName;
                                } else {
                                    log_message('error', '发票文件：' . $_FILES['file']['name'] . ' 未识别到对方公司名称，且系统通过统一社会信用代码反查对方公司名称失败！');
                                }
                            }
                        } else {
                            $us = intval($this->db->table('company')->select('id')->where(['fullName' => $arr[34], 'no' => $arr[35]])->get()->getRowArray()['id'] ?? 0);
                            $otherSide = $arr[36];
                        }
                    } else { // 我方开出
                        if (preg_match('/^[A-Z0-9]{36}$/', $arr[35])) { // 合并读取了，要拆分出来
                            $companyNo = substr($arr[35], -18); // 从后往前截取18位，代表销售方（我方）纳税人识别号
                            $companyInfo = $this->db->table('company')->select('id')->where(['no' => $companyNo])->get()->getRowArray();
                            if ($companyInfo) {
                                $us = intval($companyInfo['id']);
                                $otherSide = substr($arr[35], 0, 18);
                                $companyFullName = $this->getComoanyFullName($otherSide);
                                if ($companyFullName) {
                                    $otherSide = $companyFullName;
                                } else {
                                    log_message('error', '发票文件：' . $_FILES['file']['name'] . ' 未识别到对方公司名称，且系统通过统一社会信用代码反查对方公司名称失败！');
                                }
                            }
                        } else {
                            $us = intval($this->db->table('company')->select('id')->where(['fullName' => $arr[36], 'no' => $arr[37]])->get()->getRowArray()['id'] ?? 0);
                            $otherSide = $arr[34];
                        }
                    }
                } elseif (count($arr) >= 26 && preg_match('/发票号码：[0-9]{20}$/', $arr[2]) && strpos($arr[9], '名称') == 0 && strpos($arr[10], '统一社会信用代码/纳税人识别号') == 0 && strpos($arr[16], '名称') == 0 && strpos($arr[17], '统一社会信用代码/纳税人识别号') == 0) {
                    $no = str_replace('发票号码：', '', $arr[2]);
                    if ($inoutType == '1') { // 我方收取
                        $us = intval($this->db->table('company')->select('id')->where(['fullName' => str_replace('名称:', '', $arr[9]), 'no' => str_replace('统一社会信用代码/纳税人识别号:', '', $arr[10])])->get()->getRowArray()['id'] ?? 0);
                        $otherSide = str_replace('名称:', '', $arr[16]);
                    } else { // 我方开出
                        $us = intval($this->db->table('company')->select('id')->where(['fullName' => str_replace('名称:', '', $arr[16]), 'no' => str_replace('统一社会信用代码/纳税人识别号:', '', $arr[17])])->get()->getRowArray()['id'] ?? 0);
                        $otherSide = str_replace('名称:', '', $arr[9]);
                    }
                } elseif (count($arr) == 46 && ($arr[43] == '电子发票（铁路电子客票）' || $arr[43] == 'µç×Ó·¢Æ±£¨ÌúÂ·µç×Ó¿ÍÆ±£©')) { // 铁路电子客票
                    $type = 2;
                    $code = '';
                    $no = explode(':', $arr[22])[1];
                    preg_match_all('/\d+/', $arr[21], $dateArr);
                    $date = $dateArr[0][0] . '-' . $dateArr[0][1] . '-' . $dateArr[0][2];
                    $amount = $arr[45];
                    $us = intval($this->db->table('company')->select('id')->where(['no' =>explode(':', $arr[40])[1]])->get()->getRowArray()['id'] ?? 0);
                    $otherSide = '中国铁路';
                } elseif (count($arr) == 32 && $arr[0] == 'Vý[¶zR¡`;\@') { // 铁路电子客票
                    $type = 2;
                    $code = '';
                    $no = explode(':', $arr[16])[1];
                    preg_match_all('/\d+/', $arr[27], $dateArr);
                    $date = $dateArr[0][0] . '-' . $dateArr[0][1] . '-' . $dateArr[0][2];
                    $amount = str_replace('yhN÷:ÿå', '', $arr[22]);
                    $us = intval($this->db->table('company')->select('id')->where(['no' =>explode(':', $arr[26])[1]])->get()->getRowArray()['id'] ?? 0);
                    $otherSide = '中国铁路';
                }
                // 如果本地未准确识别所需信息，则调用百度增值税发票识别api进行识别
                if ((strlen($no) < 20 && $code == '') || $no == '' || $date == '' || $amount == '' || $us == 0 || $otherSide == '') {
                    // 如果未录入过，才进行，避免浪费资源
                    if ($no != '' && $this->db->table('invoice')->where(['code' => $code, 'no' => $no, 'isDelete' => 0])->countAllResults() > 0) {
                        $invoiceInfo = $this->db->table('invoice')->where(['code' => $code, 'no' => $no, 'isDelete' => 0])->get()->getRowArray();
                        $type = intval($invoiceInfo['type']);
                        $code = $invoiceInfo['code'];
                        $no = $invoiceInfo['no'];
                        $date = $invoiceInfo['date'];
                        $amount = $invoiceInfo['amount'];
                        $us = intval($invoiceInfo['us']);
                        $otherSide = $invoiceInfo['otherSide'];
                    } else {
                        log_message('error', '发票文件：' . $_FILES['file']['name'] . ' 本地识别失败，转百度识别！');
                        $url = 'https://aip.baidubce.com/oauth/2.0/token';
                        $res = global_curl($url, [
                            'post' => 1,
                            'postfields' => http_build_query([
                                'grant_type' => 'client_credentials',
                                'client_id' => 'uJEzw76iyeJDNiiSL8v7crPP',
                                'client_secret' => 'wWgCl7kW6Ay4GN7VraLL36HeJTIEl5d6'
                            ])
                        ]);
                        if ($res !== false) {
                            $res = json_decode($res, true);
                            $access_token = $res['access_token'];
                            if ($access_token) {
                                $url = 'https://aip.baidubce.com/rest/2.0/ocr/v1/vat_invoice?access_token=' . $access_token;
                                $res = global_curl($url, [
                                    'post' => 1,
                                    'postfields' => http_build_query([
                                        'pdf_file' => base64_encode(file_get_contents($uppath . $target_file)),
                                        'pdf_file_num' => $pageLen,
                                        'seal_tag' => 'false'
                                    ]),
                                    'httpheader' => [
                                        'Content-Type: application/x-www-form-urlencoded',
                                        'Accept: application/json'
                                    ]
                                ]);
                                if ($res !== false) {
                                    $obj = json_decode($res, true);
                                    if (is_array($obj) && isset($obj['words_result'])) {
                                        $arr = $obj['words_result'];
                                        $type = strpos($arr['InvoiceType'], '专用发票') !== false ? 1 : 0;
                                        $code = $arr['InvoiceCode'];
                                        $no = $arr['InvoiceNum'];
                                        $date =  str_replace(['年','月','日'], ['-','-',''], $arr['InvoiceDate']);
                                        $amount = $arr['AmountInFiguers'];
                                        if ($inoutType == '1') { // 我方收取
                                            $us = intval($this->db->table('company')->select('id')->where(['fullName' => $arr['PurchaserName'], 'no' => $arr['PurchaserRegisterNum']])->get()->getRowArray()['id'] ?? 0);
                                            if ($arr['SellerName']) {
                                                $otherSide = $arr['SellerName'];
                                            } else {
                                                $companyFullName = $this->getComoanyFullName($arr['SellerRegisterNum']);
                                                if ($companyFullName) {
                                                    $otherSide = $companyFullName;
                                                } else {
                                                    log_message('error', '发票文件：' . $_FILES['file']['name'] . ' 未识别到对方公司名称，且系统通过统一社会信用代码反查对方公司名称失败！');
                                                }
                                            }
                                        } else { // 我方开出
                                            $us = intval($this->db->table('company')->select('id')->where(['fullName' => $arr['SellerName'], 'no' => $arr['SellerRegisterNum']])->get()->getRowArray()['id'] ?? 0);
                                            $otherSide = $arr['PurchaserName'];
                                        }
                                    } else {
                                        $this->db->table('invoice_err')->insert(['uName' => $this->myerp['uName'], 'billNo' => $billNo, 'fileName' => $_FILES['file']['name'], 'upPath' => $uppath . $target_file, 'upTime' => date('Y-m-d H:i:s'), 'upErr' => '自动识别结果：未识别到发票！请检查发票有效性！']);
                                        $this->myservice->log('上传了 '.$billNo. ' 的发票文件：' . $_FILES['file']['name'] . '，自动识别结果：未识别到发票！请检查发票有效性！');
                                        return $this->response->setJSON(['status' => -1, 'msg' => '未识别到发票！请检查发票有效性！']);
                                    }
                                } else {
                                    $this->db->table('invoice_err')->insert(['uName' => $this->myerp['uName'], 'billNo' => $billNo, 'fileName' => $_FILES['file']['name'], 'upPath' => $uppath . $target_file, 'upTime' => date('Y-m-d H:i:s'), 'upErr' => '自动识别结果：未识别到发票！请检查发票有效性！']);
                                    $this->myservice->log('上传了 ' . $billNo . ' 的发票文件：' . $_FILES['file']['name'] . '，自动识别结果：未识别到发票！请检查发票有效性！');
                                    return $this->response->setJSON(['status' => -1, 'msg' => '未识别到发票！请检查发票有效性！']);
                                }
                            } else {
                                $this->myservice->log('上传了 ' . $billNo . ' 的发票文件：' . $_FILES['file']['name'] . '，自动识别结果：未识别到发票！access_token获取失败！请重试一次！');
                                return $this->response->setJSON(['status' => -1, 'msg' => '未识别到发票！access_token获取失败！']);
                            }
                        } else {
                            $this->myservice->log('上传了 ' . $billNo . ' 的发票文件：' . $_FILES['file']['name'] . '，自动识别结果：未识别到发票！access_token获取失败！请重试一次！');
                            return $this->response->setJSON(['status' => -1, 'msg' => '未识别到发票！access_token获取失败！']);
                        }
                    }
                }
                if ($no != '' && strlen($no) < 20 && $code == '') {
                    $this->db->table('invoice_err')->insert(['uName' => $this->myerp['uName'], 'billNo' => $billNo, 'fileName' => $_FILES['file']['name'], 'upPath' => $uppath . $target_file, 'upTime' => date('Y-m-d H:i:s'), 'upErr' => '自动识别结果：未识别到发票代码！']);
                    $this->myservice->log('上传了 ' . $billNo . ' 的发票文件：' . $_FILES['file']['name'] . '，自动识别结果：未识别到发票代码！');
                    return $this->response->setJSON(['status' => -1, 'msg' => '未识别到发票代码！']);
                }
                if ($no == '') {
                    $this->db->table('invoice_err')->insert(['uName' => $this->myerp['uName'], 'billNo' => $billNo, 'fileName' => $_FILES['file']['name'], 'upPath' => $uppath . $target_file, 'upTime' => date('Y-m-d H:i:s'), 'upErr' => '自动识别结果：未识别到发票号码！']);
                    $this->myservice->log('上传了 ' . $billNo . ' 的发票文件：' . $_FILES['file']['name'] . '，自动识别结果：未识别到发票号码！');
                    return $this->response->setJSON(['status' => -1, 'msg' => '未识别到发票号码！']);
                }
                if ($date == '') {
                    $this->db->table('invoice_err')->insert(['uName' => $this->myerp['uName'], 'billNo' => $billNo, 'fileName' => $_FILES['file']['name'], 'upPath' => $uppath . $target_file, 'upTime' => date('Y-m-d H:i:s'), 'upErr' => '自动识别结果：未识别到开票日期！']);
                    $this->myservice->log('上传了 ' . $billNo . ' 的发票文件：' . $_FILES['file']['name'] . '，自动识别结果：未识别到开票日期！');
                    return $this->response->setJSON(['status' => -1, 'msg' => '未识别到开票日期！']);
                }
                if ($amount == '') {
                    $this->db->table('invoice_err')->insert(['uName' => $this->myerp['uName'], 'billNo' => $billNo, 'fileName' => $_FILES['file']['name'], 'upPath' => $uppath . $target_file, 'upTime' => date('Y-m-d H:i:s'), 'upErr' => '自动识别结果：未识别到价税合计金额！']);
                    $this->myservice->log('上传了 ' . $billNo . ' 的发票文件：' . $_FILES['file']['name'] . '，自动识别结果：未识别到价税合计金额！');
                    return $this->response->setJSON(['status' => -1, 'msg' => '未识别到价税合计金额！']);
                }
                if ($us == 0) {
                    $this->db->table('invoice_err')->insert(['uName' => $this->myerp['uName'], 'billNo' => $billNo, 'fileName' => $_FILES['file']['name'], 'upPath' => $uppath . $target_file, 'upTime' => date('Y-m-d H:i:s'), 'upErr' => '自动识别结果：未匹配到我方经营主体！公司名称或统一社会信用代码错误！']);
                    $this->myservice->log('上传了 ' . $billNo . ' 的发票文件：' . $_FILES['file']['name'] . '，自动识别结果：未匹配到我方经营主体！公司名称或统一社会信用代码错误！');
                    return $this->response->setJSON(['status' => -1, 'msg' => '未匹配到我方经营主体！公司名称或统一社会信用代码错误！']);
                }
                if ($otherSide == '') {
                    $this->db->table('invoice_err')->insert(['uName' => $this->myerp['uName'], 'billNo' => $billNo, 'fileName' => $_FILES['file']['name'], 'upPath' => $uppath . $target_file, 'upTime' => date('Y-m-d H:i:s'), 'upErr' => '自动识别结果：未匹配到对方经营主体']);
                    $this->myservice->log('上传了 ' . $billNo . ' 的发票文件：' . $_FILES['file']['name'] . '，自动识别结果：未匹配到对方经营主体！');
                    return $this->response->setJSON(['status' => -1, 'msg' => '未匹配到对方经营主体！']);
                }
                $data = [
                    'type' => $type,
                    'code' => $code,
                    'no' => $no,
                    'date' => $date,
                    'amount' => $amount,
                    'us' => $us,
                    'otherSide' => $otherSide
                ];
                $this->myservice->log('上传了 ' . $billNo . ' 的发票文件：' . $_FILES['file']['name'] . '，自动识别结果：' . json_encode($data, 320));
                $bakInvoiceStatus = $this->bakInvoice($uppath . $target_file, $data, intval($inoutType));
                if (!$bakInvoiceStatus) {
                    $this->myservice->log('发票文件：' . $_FILES['file']['name'] . ' 自动归档失败！');
                }
                return $this->response->setJSON(['status' => 200, 'msg' => '自动识别成功！', 'data' => $data]);
            } else {
                $this->db->table('invoice_err')->insert(['uName' => $this->myerp['uName'], 'billNo' => $billNo, 'fileName' => $_FILES['file']['name'], 'upPath' => $uppath . $target_file, 'upTime' => date('Y-m-d H:i:s'), 'upErr' => '自动识别结果：未识别出pdf信息！']);
                $this->myservice->log('上传了 ' . $billNo . ' 的发票文件：' . $_FILES['file']['name'] . '，自动识别结果：未识别出pdf信息！');
                return $this->response->setJSON(['status' => -1, 'msg' => '未识别出pdf信息！']);
            }
        } elseif ($fileType == 'png' || $fileType == 'jpg' || $fileType == 'jpeg' || $fileType == 'bmp') {
            // 如果是图片，则调用百度增值税发票识别api进行识别
            log_message('error', '发票文件：' . $_FILES['file']['name'] . ' 为图片格式，转百度识别！');
            $url = 'https://aip.baidubce.com/oauth/2.0/token';
            $res = global_curl($url, [
                'post' => 1,
                'postfields' => http_build_query([
                    'grant_type' => 'client_credentials',
                    'client_id' => 'uJEzw76iyeJDNiiSL8v7crPP',
                    'client_secret' => 'wWgCl7kW6Ay4GN7VraLL36HeJTIEl5d6'
                ])
            ]);
            if ($res !== false) {
                $res = json_decode($res, true);
                $access_token = $res['access_token'];
                if ($access_token) {
                    $url = 'https://aip.baidubce.com/rest/2.0/ocr/v1/vat_invoice?access_token=' . $access_token;
                    $res = global_curl($url, [
                        'post' => 1,
                        'postfields' => http_build_query([
                            'image' => base64_encode(file_get_contents($uppath . $target_file)),
                            'seal_tag' => 'false'
                        ]),
                        'httpheader' => [
                            'Content-Type: application/x-www-form-urlencoded',
                            'Accept: application/json'
                        ]
                    ]);
                    if ($res !== false) {
                        $obj = json_decode($res, true);
                        if (is_array($obj) && isset($obj['words_result'])) {
                            $arr = $obj['words_result'];
                            $type = strpos($arr['InvoiceType'], '专用发票') !== false ? 1 : 0;
                            $code = $arr['InvoiceCode'];
                            $no = $arr['InvoiceNum'];
                            $date = str_replace(['年','月','日'], ['-','-',''], $arr['InvoiceDate']);
                            $amount = $arr['AmountInFiguers'];
                            if ($inoutType == '1') { // 我方收取
                                $us = intval($this->db->table('company')->select('id')->where(['fullName' => $arr['PurchaserName'], 'no' => $arr['PurchaserRegisterNum']])->get()->getRowArray()['id'] ?? 0);
                                $otherSide = $arr['SellerName'];
                            } else { // 我方开出
                                $us = intval($this->db->table('company')->select('id')->where(['fullName' => $arr['SellerName'], 'no' => $arr['SellerRegisterNum']])->get()->getRowArray()['id'] ?? 0);
                                $otherSide = $arr['PurchaserName'];
                            }
                            if ($no != '' && strlen($no) < 20 && $code == '') {
                                $this->db->table('invoice_err')->insert(['uName' => $this->myerp['uName'], 'billNo' => $billNo, 'fileName' => $_FILES['file']['name'], 'upPath' => $uppath . $target_file, 'upTime' => date('Y-m-d H:i:s'), 'upErr' => '自动识别结果：未识别到发票代码！']);
                                $this->myservice->log('上传了 ' . $billNo . ' 的发票文件：' . $_FILES['file']['name'] . '，自动识别结果：未识别到发票代码！');
                                return $this->response->setJSON(['status' => -1, 'msg' => '未识别到发票代码！']);
                            }
                            if ($no == '') {
                                $this->db->table('invoice_err')->insert(['uName' => $this->myerp['uName'], 'billNo' => $billNo, 'fileName' => $_FILES['file']['name'], 'upPath' => $uppath . $target_file, 'upTime' => date('Y-m-d H:i:s'), 'upErr' => '自动识别结果：未识别到发票号码！']);
                                $this->myservice->log('上传了 ' . $billNo . ' 的发票文件：' . $_FILES['file']['name'] . '，自动识别结果：未识别到发票号码！');
                                return $this->response->setJSON(['status' => -1, 'msg' => '未识别到发票号码！']);
                            }
                            if ($date == '') {
                                $this->db->table('invoice_err')->insert(['uName' => $this->myerp['uName'], 'billNo' => $billNo, 'fileName' => $_FILES['file']['name'], 'upPath' => $uppath . $target_file, 'upTime' => date('Y-m-d H:i:s'), 'upErr' => '自动识别结果：未识别到开票日期！']);
                                $this->myservice->log('上传了 ' . $billNo . ' 的发票文件：' . $_FILES['file']['name'] . '，自动识别结果：未识别到开票日期！');
                                return $this->response->setJSON(['status' => -1, 'msg' => '未识别到开票日期！']);
                            }
                            if ($amount == '') {
                                $this->db->table('invoice_err')->insert(['uName' => $this->myerp['uName'], 'billNo' => $billNo, 'fileName' => $_FILES['file']['name'], 'upPath' => $uppath . $target_file, 'upTime' => date('Y-m-d H:i:s'), 'upErr' => '自动识别结果：未识别到价税合计金额！']);
                                $this->myservice->log('上传了 ' . $billNo . ' 的发票文件：' . $_FILES['file']['name'] . '，自动识别结果：未识别到价税合计金额！');
                                return $this->response->setJSON(['status' => -1, 'msg' => '未识别到价税合计金额！']);
                            }
                            if ($us == 0) {
                                $this->db->table('invoice_err')->insert(['uName' => $this->myerp['uName'], 'billNo' => $billNo, 'fileName' => $_FILES['file']['name'], 'upPath' => $uppath . $target_file, 'upTime' => date('Y-m-d H:i:s'), 'upErr' => '自动识别结果：未匹配到我方经营主体！请检查开票信息是否正确！']);
                                $this->myservice->log('上传了 ' . $billNo . ' 的发票文件：' . $_FILES['file']['name'] . '，自动识别结果：未匹配到我方经营主体！请检查开票信息是否正确！');
                                return $this->response->setJSON(['status' => -1, 'msg' => '未匹配到我方经营主体！公司名称或统一社会信用代码错误！']);
                            }
                            if ($otherSide == '') {
                                $this->db->table('invoice_err')->insert(['uName' => $this->myerp['uName'], 'billNo' => $billNo, 'fileName' => $_FILES['file']['name'], 'upPath' => $uppath . $target_file, 'upTime' => date('Y-m-d H:i:s'), 'upErr' => '自动识别结果：未识别到对方经营主体！']);
                                $this->myservice->log('上传了 ' . $billNo . ' 的发票文件：' . $_FILES['file']['name'] . '，自动识别结果：未识别到对方经营主体！');
                                return $this->response->setJSON(['status' => -1, 'msg' => '未识别到对方经营主体！']);
                            }
                            $data = [
                                'type' => $type,
                                'code' => $code,
                                'no' => $no,
                                'date' => $date,
                                'amount' => $amount,
                                'us' => $us,
                                'otherSide' => $otherSide
                            ];
                            $this->myservice->log('上传了 ' . $billNo . ' 的发票文件：' . $_FILES['file']['name'] . '，自动识别结果：' . json_encode($data, 320));
                            $bakInvoiceStatus = $this->bakInvoice($uppath . $target_file, $data, intval($inoutType));
                            if (!$bakInvoiceStatus) {
                                $this->myservice->log('发票文件：' . $_FILES['file']['name'] . ' 自动归档失败！');
                            }
                            return $this->response->setJSON(['status' => 200, 'msg' => '自动识别成功！', 'data' => $data]);
                        } else {
                            $this->db->table('invoice_err')->insert(['uName' => $this->myerp['uName'], 'billNo' => $billNo, 'fileName' => $_FILES['file']['name'], 'upPath' => $uppath . $target_file, 'upTime' => date('Y-m-d H:i:s'), 'upErr' => '自动识别结果：未识别到发票！请检查发票是否太模糊！']);
                            $this->myservice->log('上传了 ' . $billNo . ' 的发票文件：' . $_FILES['file']['name'] . '，自动识别结果：未识别到发票！请检查发票是否太模糊！');
                            return $this->response->setJSON(['status' => -1, 'msg' => '未识别到发票！请检查发票是否太模糊！']);
                        }
                    } else {
                        $this->db->table('invoice_err')->insert(['uName' => $this->myerp['uName'], 'billNo' => $billNo, 'fileName' => $_FILES['file']['name'], 'upPath' => $uppath . $target_file, 'upTime' => date('Y-m-d H:i:s'), 'upErr' => '自动识别结果：未识别到发票！请检查发票是否太模糊！']);
                        $this->myservice->log('上传了 ' . $billNo . ' 的发票文件：' . $_FILES['file']['name'] . '，自动识别结果：未识别到发票！请检查发票是否太模糊！');
                        return $this->response->setJSON(['status' => -1, 'msg' => '未识别到发票！请检查发票是否太模糊！']);
                    }
                } else {
                    $this->myservice->log('上传了 ' . $billNo . ' 的发票文件：' . $_FILES['file']['name'] . '，自动识别结果：未识别到发票！access_token获取失败！请重试一次！');
                    return $this->response->setJSON(['status' => -1, 'msg' => '未识别到发票！access_token获取失败！']);
                }
            } else {
                $this->myservice->log('上传了 ' . $billNo . ' 的发票文件：' . $_FILES['file']['name'] . '，自动识别结果：未识别到发票！access_token获取失败！请重试一次！');
                return $this->response->setJSON(['status' => -1, 'msg' => '未识别到发票！access_token获取失败！']);
            }
        } else {
            $this->db->table('invoice_err')->insert(['uName' => $this->myerp['uName'], 'billNo' => $billNo, 'fileName' => $_FILES['file']['name'], 'upPath' => $uppath . $target_file, 'upTime' => date('Y-m-d H:i:s'), 'upErr' => '自动识别结果：未识别到发票！不支持的文件类型！']);
            $this->myservice->log('上传了 ' . $billNo . ' 的发票文件：' . $_FILES['file']['name'] . '，自动识别结果：未识别到发票！不支持的文件类型！');
            return $this->response->setJSON(['status' => -1, 'msg' => '未识别到发票！不支持的文件类型！']);
        }
    }

    // 从天眼查获取公司名称
    private function getComoanyFullName(string $companyNo)
    {
        $companyName = '';
        $cookie = $this->db->table('bid_temp')->select('cookie')->where(['site' => '天眼查'])->get()->getRowArray()['cookie'] ?? '';
        if ($cookie) {
            $token = $tycid = [];
            preg_match('#auth_token=(.*);#isU', $cookie, $token);
            preg_match('#TYCID=(.*);#isU', $cookie, $tycid);
            if (is_countable($token) && count($token) > 0 && is_countable($tycid) && count($tycid) > 0) {
                $url = 'https://capi.tianyancha.com/cloud-tempest/search/suggest/company/main?_=' . intval(microtime(true) * 1000);
                $res = global_curl($url, [
                    'post' => 1,
                    'postfields' => '{"keyword":"' . $companyNo . '"}',
                    'httpheader' => [
                        'Accept: application/json, text/plain, */*',
                        'Accept-Encoding: gzip, deflate, br, zstd',
                        'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                        'Cache-Control: no-cache',
                        'Connection: keep-alive',
                        'Content-Length: 32',
                        'Content-Type: application/json',
                        'Host: capi.tianyancha.com',
                        'Origin: https://www.tianyancha.com',
                        'Pragma: no-cache',
                        'Referer: https://www.tianyancha.com/',
                        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0',
                        'X-AUTH-TOKEN: ' . $token[1],
                        'X-TYCID: ' . $tycid[1],
                        'version: TYC-Web'
                    ]
                ]);
                if ($res !== false) {
                    $obj = json_decode($res, true);
                    $companyName = $obj['data']['companySuggestList'][0]['name'] ?? '';
                }
            }
        }
        return $companyName;
    }
    // 发票识别成功后自动归档
    private function bakInvoice(string $dir, array $data, int $inoutType)
    {
        $fileType = strtolower(strrev(explode('.', strrev($dir))[0]));
        $bakFile = ($data['type'] == 0 ? '普票_' : '专票_') . ($data['code'] ? ($data['code'] . '_') : '') . $data['no'] . '_' . $data['amount'] . '.' . $fileType;
        $usName = $this->db->table('company')->select('name')->where(['id' => $data['us']])->get()->getRowArray()['name'] ?? '';
        if ($usName) {
            $bakPath1 = 'data/网盘/财务部/发票自动归档/';
            $bakPath2 = ($inoutType == 1 ? '收取' : '开出') . '/';
            $bakPath3 = $usName . '/';
            $bakPath4 = substr($data['date'], 0, 4) . '年/';
            $bakPath5 = substr($data['date'], 5, 2) . '月/';
            $bakPath6 = $this->myerp['uName'] . '/';
            $bakPath = $bakPath1 . $bakPath2 . $bakPath3 . $bakPath4 . $bakPath5 . $bakPath6;
            if (!is_dir($bakPath1)) {
                @umask(0);
                $ret = @mkdir($bakPath1, 0777);
                if ($ret === false) {
                    return false;
                }
            }
            if (!is_dir($bakPath1 . $bakPath2)) {
                @umask(0);
                $ret = @mkdir($bakPath1 . $bakPath2, 0777);
                if ($ret === false) {
                    return false;
                }
            }
            if (!is_dir($bakPath1 . $bakPath2 . $bakPath3)) {
                @umask(0);
                $ret = @mkdir($bakPath1 . $bakPath2 . $bakPath3, 0777);
                if ($ret === false) {
                    return false;
                }
            }
            if (!is_dir($bakPath1 . $bakPath2 . $bakPath3 . $bakPath4)) {
                @umask(0);
                $ret = @mkdir($bakPath1 . $bakPath2 . $bakPath3 . $bakPath4, 0777);
                if ($ret === false) {
                    return false;
                }
            }
            if (!is_dir($bakPath1 . $bakPath2 . $bakPath3 . $bakPath4 . $bakPath5)) {
                @umask(0);
                $ret = @mkdir($bakPath1 . $bakPath2 . $bakPath3 . $bakPath4 . $bakPath5, 0777);
                if ($ret === false) {
                    return false;
                }
            }
            if (!is_dir($bakPath1 . $bakPath2 . $bakPath3 . $bakPath4 . $bakPath5 . $bakPath6)) {
                @umask(0);
                $ret = @mkdir($bakPath1 . $bakPath2 . $bakPath3 . $bakPath4 . $bakPath5 . $bakPath6, 0777);
                if ($ret === false) {
                    return false;
                }
            }
            if (rename($dir, $bakPath . $bakFile)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }


    // 识票异常 列表
    public function listErr()
    {
        if (!$this->myservice->verifyRight('FINANCE_MANAGER')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Invoice/listErr 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');
        $unDone = intval($this->request->getGetPost('unDone'));
        $skey = $this->request->getGetPost('skey');
        $builder = $this->db->table('invoice_err');
        $builder->where('isDelete', 0);
        if ($beginDate) {
            $builder->where('upTime >=', $beginDate);
        }
        if ($endDate) {
            $builder->where('upTime <=', $endDate);
        }
        if ($unDone > 0) {
            $builder->where('status', 0);
        }
        if ($skey !== '' && $skey !== null) {
            $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
            foreach ($keywords as $kw) {
                $builder->groupStart()
                    ->where('uName', $kw)
                    ->orLike('billNo', $this->db->escapeLikeString((string)$kw))
                    ->orLike('fileName', $this->db->escapeLikeString($kw))
                    ->orLike('upPath', $this->db->escapeLikeString($kw))
                    ->orLike('upErr', $this->db->escapeLikeString($kw))
                ->groupEnd();
            }
        }
        $allowedSort = ['id','uName','billNo','fileName','upPath','upTime','upErr','status'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $list = (clone $builder)->orderBy($orderField, $sord)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['uName'] = $row['uName'];
            $v[$arr]['billNo'] = $row['billNo'];
            $v[$arr]['fileName'] = $row['fileName'];
            $v[$arr]['upPath'] = $row['upPath'];
            $v[$arr]['upTime'] = $row['upTime'];
            $v[$arr]['upErr'] = $row['upErr'];
            $v[$arr]['status'] = intval($row['status']);
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['page'] = $page;
        $json['data']['records'] = $total;
        $json['data']['total'] = ceil($json['data']['records'] / $rows);
        $json['data']['rows'] = $v;
        return $this->response->setJSON($json);
    }
    // 识票异常 财务抄收标记
    public function statusErr()
    {
        if (!$this->myservice->verifyRight('FINANCE_MANAGER')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Invoice/statusErr 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $status = intval($this->request->getGetPost('status'));
        $data = $this->db->table('invoice_err')->where(['id' => $id])->get()->getRowArray();
        if ($data) {
            $this->db->table('invoice_err')->where(['id' => $id])->update(['status' => $status]);
            $this->myservice->log('标记了识票异常: ID：' . $id . ' 的状态为 ' . ($status == 0 ? '提醒中' : '不再提醒'));
            $data['status'] = $status;
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '操作成功！', 'data' => $data]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }


    // 收票遗漏 列表
    public function listMiss()
    {
        if (!$this->myservice->verifyRight('INVOICE_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Invoice/listMiss 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $type = intval($this->request->getGetPost('type'));
        $uName = $this->request->getGetPost('uName');
        $billType = $this->request->getGetPost('billType');
        $builder = $this->db->table('bill_' . $billType);
        $builder->where('isDelete', 0);
        if ($type > 0) {
            $builder->where('isIgnoreInvoice >=', 2);
        } else {
            $builder->where('isIgnoreInvoice <', 2);
        }
        $builder->orderBy('ignoreInvoiceReason DESC, isIgnoreInvoice DESC, id DESC');

        $total = $total_order = $total_contract_info = $total_money = $total_deliver = 0;
        if ($uName && $billType) {
            if (!$this->myservice->verifyRight('SYS_ADMIN')) {
                $builder->where('uName', $uName);
            }
            $total_order = (clone $builder)->from('bill_order', true)->where('billType', 'PUR')->where('invoiceAmount < amount', null, false)->where('isDraft', 0)->where('isCancel', 0)->countAllResults();
            $total_contract_info = (clone $builder)->from('bill_contract_info', true)->where('billType', 'PUR')->where('paidAmount >', 0)->where('isRecInvoice', 0)->where('isDraft', 0)->countAllResults();
            $total_money = (clone $builder)->from('bill_money', true)->where('billType', 'EXP')->where('invoiceAmount < cleared', null, false)->countAllResults();
            $total_deliver = (clone $builder)->from('bill_deliver', true)->where('invoiceAmount < amount', null, false)->where('isDraft', 0)->countAllResults();
            switch ($billType) {
                case 'order':
                    $list = (clone $builder)->from('bill_order', true)->where('billType', 'PUR')->where('invoiceAmount < amount', null, false)->where('isDraft', 0)->where('isCancel', 0)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
                    $total = $total_order;
                    break;
                case 'contract_info':
                    $list = (clone $builder)->from('bill_contract_info', true)->where('billType', 'PUR')->where('paidAmount >', 0)->where('isRecInvoice', 0)->where('isDraft', 0)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
                    $total = $total_contract_info;
                    break;
                case 'money':
                    $list = (clone $builder)->from('bill_money', true)->where('billType', 'EXP')->where('invoiceAmount < cleared', null, false)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
                    $total = $total_money;
                    break;
                case 'deliver':
                    $list = (clone $builder)->from('bill_deliver', true)->where('invoiceAmount < amount', null, false)->where('isDraft', 0)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
                    $total = $total_deliver;
                    break;
                default:
                    $list = [];
                    break;
            }
        } else {
            $list = [];
        }
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['iid'] = intval($row['iid'] ?? 0);
            $v[$arr]['billNo'] = $row['billNo'];
            $v[$arr]['billDate'] = $row['billDate'];
            $v[$arr]['zid'] = $row['zid'] ?? '';
            $v[$arr]['uName'] = $row['uName'];
            $v[$arr]['amount'] = (float)$row['amount'];
            $v[$arr]['paidAmount'] = (float)($row['paidAmount'] ?? 0);
            $v[$arr]['isRecInvoice'] = intval($row['isRecInvoice'] ?? 0);
            $v[$arr]['invoiceAmount'] = (float)($row['invoiceAmount'] ?? 0);
            $v[$arr]['isIgnoreInvoice'] = intval($row['isIgnoreInvoice']);
            $v[$arr]['ignoreInvoiceImgs'] = $row['ignoreInvoiceImgs'] == '' ? [] : explode(',', $row['ignoreInvoiceImgs']);
            $v[$arr]['ignoreInvoiceReason'] = $row['ignoreInvoiceReason'];
            $v[$arr]['disagreeIgnoreReason'] = $row['disagreeIgnoreReason'];
            $v[$arr]['ignoreInvoiceTime'] = $row['ignoreInvoiceTime'];
            $v[$arr]['ignoreInvoiceName'] = $row['ignoreInvoiceName'];
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['page'] = $page;
        $json['data']['records'] = $total;
        $json['data']['total'] = ceil($json['data']['records'] / $rows);
        $json['data']['rows'] = $v;
        $json['data']['total_order'] = $total_order;
        $json['data']['total_contract_info'] = $total_contract_info;
        $json['data']['total_money'] = $total_money;
        $json['data']['total_deliver'] = $total_deliver;
        return $this->response->setJSON($json);
    }

    // 收票遗漏 白条操作
    public function ignoreMiss()
    {
        $type = $this->request->getGetPost('type');
        $billType = $this->request->getGetPost('billType');
        $id = intval($this->request->getGetPost('id'));
        $disagreeIgnoreReason = $this->request->getGetPost('disagreeIgnoreReason') ?? '';
        if ($type != 'ignoreReq') {
            if (!$this->myservice->verifyRight('SYS_ADMIN')) {
                log_message('error', $this->myerp['uName'] . ' 访问 /Base/Invoice/ignoreMiss 权限不足！');
                return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
            }
        } else {
            if ($billType == 'order' && !$this->myservice->verifyRight('PO_UPDATE')) {
                log_message('error', $this->myerp['uName'] . ' 访问 /Base/Invoice/ignoreMiss 权限不足！');
                return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
            } elseif ($billType == 'contract_info' && !$this->myservice->verifyRight('PC_UPDATE')) {
                log_message('error', $this->myerp['uName'] . ' 访问 /Base/Invoice/ignoreMiss 权限不足！');
                return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
            } elseif ($billType == 'money' && !$this->myservice->verifyRight('EXPEND_UPDATE')) {
                log_message('error', $this->myerp['uName'] . ' 访问 /Base/Invoice/ignoreMiss 权限不足！');
                return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
            } elseif ($billType == 'deliver' && !$this->myservice->verifyRight('DELIVER_UPDATE')) {
                log_message('error', $this->myerp['uName'] . ' 访问 /Base/Invoice/ignoreMiss 权限不足！');
                return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
            }
        }
        if ($billType && $id > 0) {
            $table = 'bill_' . $billType;
            $isIgnoreInvoice = ($type == 'ignoreReq' || $type == 'reIgnore' || $type == 'reAgreeIgnore' || $type == 'reDisagreeIgnore') ? 0 : ($type == 'disagreeIgnore' ? 1 : ($type == 'ignore' ? 3 : 2));
            switch ($type) {
                case 'ignoreReq':
                    $oper = $this->request->getGetPost('oper');
                    $ignoreInvoiceImgs = $this->request->getGetPost('ignoreInvoiceImgs');
                    $ignoreInvoiceReason = $this->request->getGetPost('ignoreInvoiceReason');
                    $this->db->transStart();
                    $this->db->table($table)->where(['id' => $id])->update(['isIgnoreInvoice' => $isIgnoreInvoice, 'ignoreInvoiceImgs' => $ignoreInvoiceImgs, 'ignoreInvoiceReason' => $ignoreInvoiceReason]);
                    $data = $this->db->table($table)->where(['id' => $id])->get()->getRowArray();
                    $this->myservice->log(($oper == 'add' ? '首次申请' : '重新编辑') . '了白条入账，单号：' . $data['billNo'] . ($billType == 'contract_info' ? '，产品自编序号：'.$data['zid'] : ''));
                    $this->db->transComplete();
                    if ($this->db->transStatus() === false) {
                        return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
                    }
                    return $this->response->setJSON(['status' => 200, 'msg' => '操作成功！', 'data' => $data]);
                break;
                case 'ignore':
                    $oper = $this->request->getGetPost('oper');
                    $ignoreInvoiceImgs = $this->request->getGetPost('ignoreInvoiceImgs');
                    $ignoreInvoiceReason = $this->request->getGetPost('ignoreInvoiceReason');
                    $this->db->transStart();
                    $this->db->table($table)->where(['id' => $id])->update(['isIgnoreInvoice' => $isIgnoreInvoice, 'ignoreInvoiceImgs' => $ignoreInvoiceImgs, 'ignoreInvoiceReason' => $ignoreInvoiceReason, 'ignoreInvoiceName' => $this->myerp['uName'], 'ignoreInvoiceTime' => date('Y-m-d H:i:s')]);
                    $data = $this->db->table($table)->where(['id' => $id])->get()->getRowArray();
                    $this->myservice->log(($oper == 'add' ? '直接进行' : '重新编辑') . '了白条入账，单号：' . $data['billNo'] . ($billType == 'contract_info' ? '，产品自编序号：'.$data['zid'] : ''));
                    $this->db->transComplete();
                    if ($this->db->transStatus() === false) {
                        return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
                    }
                    return $this->response->setJSON(['status' => 200, 'msg' => '操作成功！', 'data' => $data]);
                break;
                case 'reIgnore':
                    $this->db->transStart();
                    $this->db->table($table)->where(['id' => $id])->update(['isIgnoreInvoice' => $isIgnoreInvoice, 'ignoreInvoiceImgs' => '', 'ignoreInvoiceReason' => '', 'ignoreInvoiceName' => '', 'ignoreInvoiceTime' => '0000-00-00 00:00:00']);
                    $data = $this->db->table($table)->where(['id' => $id])->get()->getRowArray();
                    $this->myservice->log('取消了直接进行的白条入账，单号：' . $data['billNo'] . ($billType == 'contract_info' ? '，产品自编序号：'.$data['zid'] : ''));
                    $this->db->transComplete();
                    if ($this->db->transStatus() === false) {
                        return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
                    }
                    return $this->response->setJSON(['status' => 200, 'msg' => '操作成功！', 'data' => $data]);
                break;
                case 'agreeIgnore':
                    $this->db->transStart();
                    $this->db->table($table)->where(['id' => $id])->update(['isIgnoreInvoice' => $isIgnoreInvoice, 'ignoreInvoiceName' => $this->myerp['uName'], 'ignoreInvoiceTime' => date('Y-m-d H:i:s')]);
                    $data = $this->db->table($table)->where(['id' => $id])->get()->getRowArray();
                    $this->myservice->log('同意了'.$data['uName'] . '提交的白条入账申请，单号：' . $data['billNo'] . ($billType == 'contract_info' ? '，产品自编序号：'.$data['zid'] : '') . '，申请原因：'. $data['ignoreInvoiceReason']);
                    $this->db->transComplete();
                    if ($this->db->transStatus() === false) {
                        return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
                    }
                    return $this->response->setJSON(['status' => 200, 'msg' => '操作成功！', 'data' => $data]);
                break;
                case 'reAgreeIgnore':
                    $this->db->transStart();
                    $this->db->table($table)->where(['id' => $id])->update(['isIgnoreInvoice' => $isIgnoreInvoice, 'ignoreInvoiceName' => $this->myerp['uName'], 'ignoreInvoiceTime' => date('Y-m-d H:i:s')]);
                    $data = $this->db->table($table)->where(['id' => $id])->get()->getRowArray();
                    $this->myservice->log('取消了同意的'.$data['uName'] . '提交的白条入账申请，单号：' . $data['billNo'] . ($billType == 'contract_info' ? '，产品自编序号：'.$data['zid'] : '') . '，申请原因：'. $data['ignoreInvoiceReason']);
                    $this->db->transComplete();
                    if ($this->db->transStatus() === false) {
                        return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
                    }
                    return $this->response->setJSON(['status' => 200, 'msg' => '操作成功！', 'data' => $data]);
                break;
                case 'disagreeIgnore':
                    $this->db->transStart();
                    $this->db->table($table)->where(['id' => $id])->update(['isIgnoreInvoice' => $isIgnoreInvoice, 'disagreeIgnoreReason' => $disagreeIgnoreReason, 'ignoreInvoiceName' => $this->myerp['uName'], 'ignoreInvoiceTime' => date('Y-m-d H:i:s')]);
                    $data = $this->db->table($table)->where(['id' => $id])->get()->getRowArray();
                    $this->myservice->log('拒绝了'.$data['uName'] . '提交的白条入账申请，单号：' . $data['billNo'] . ($billType == 'contract_info' ? '，产品自编序号：'.$data['zid'] : '') . '，申请原因：'. $data['ignoreInvoiceReason']);
                    $this->db->transComplete();
                    if ($this->db->transStatus() === false) {
                        return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
                    }
                    return $this->response->setJSON(['status' => 200, 'msg' => '操作成功！', 'data' => $data]);
                break;
                case 'reDisagreeIgnore':
                    $this->db->transStart();
                    $this->db->table($table)->where(['id' => $id])->update(['isIgnoreInvoice' => $isIgnoreInvoice, 'disagreeIgnoreReason' => '', 'ignoreInvoiceName' => $this->myerp['uName'], 'ignoreInvoiceTime' => date('Y-m-d H:i:s')]);
                    $data = $this->db->table($table)->where(['id' => $id])->get()->getRowArray();
                    $this->myservice->log('取消了拒绝的'.$data['uName'] . '提交的白条入账申请，单号：' . $data['billNo'] . ($billType == 'contract_info' ? '，产品自编序号：'.$data['zid'] : '') . '，申请原因：'. $data['ignoreInvoiceReason']);
                    $this->db->transComplete();
                    if ($this->db->transStatus() === false) {
                        return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
                    }
                    return $this->response->setJSON(['status' => 200, 'msg' => '操作成功！', 'data' => $data]);
                break;
                default:
                    return $this->response->setJSON(['status' => -1, 'msg' => '操作类型错误！']);
                break;
            }
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }

    // 收票遗漏 白条图片上传
    public function ignoreMissUploadImg() {
        set_time_limit(0);
        session_write_close();
        if (empty($_FILES)){
            return $this->response->setJSON(['status' => -1, 'msg' => '没有上传文件！']);
        };
        $uppath = 'data/upfile/invoiceMiss/img/';
        $temp_file = $_FILES['file']['tmp_name'];
        $target_file = date("YmdHis") . rand(1000, 9000) . '.' . strrev(explode('.', strrev($_FILES['file']['name']))[0]);
        if (!is_dir($uppath)) {
            @umask(0);
            $ret = @mkdir($uppath, 0777);
            if ($ret === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '没有权限创建用于保存的文件夹！']);
            }
        }
        move_uploaded_file($temp_file, $uppath . $target_file);
        $this->myservice->log('上传了白条图片：' . $_FILES['file']['name']);
        return $this->response->setJSON(['status' => 200, 'msg' => '上传成功！', 'data' => $target_file]);
    }

    // 收票遗漏 白条查询
    public function ignoreMissQuery()
    {
        $billType = $this->request->getGetPost('billType');
        $id = intval($this->request->getGetPost('id'));
        if ($id > 0 && $billType) {
            if ($billType == 'order' && !$this->myservice->verifyRight('PO_UPDATE')) {
                log_message('error', $this->myerp['uName'] . ' 访问 /Base/Invoice/ignoreMiss 权限不足！');
                return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
            } elseif ($billType == 'contract_info' && !$this->myservice->verifyRight('PC_UPDATE')) {
                log_message('error', $this->myerp['uName'] . ' 访问 /Base/Invoice/ignoreMiss 权限不足！');
                return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
            } elseif ($billType == 'money' && !$this->myservice->verifyRight('EXPEND_UPDATE')) {
                log_message('error', $this->myerp['uName'] . ' 访问 /Base/Invoice/ignoreMiss 权限不足！');
                return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
            } elseif ($billType == 'deliver' && !$this->myservice->verifyRight('DELIVER_UPDATE')) {
                log_message('error', $this->myerp['uName'] . ' 访问 /Base/Invoice/ignoreMiss 权限不足！');
                return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
            }
            $table = 'bill_' . $billType;
            $data = $this->db->table($table)->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
            if ($data) {
                $data['ignoreInvoiceImgs'] = $data['ignoreInvoiceImgs'] == '' ? [] : explode(',', $data['ignoreInvoiceImgs']);
                return $this->response->setJSON(['status' => 200, 'msg' => 'success', 'data' => $data]);
            }
            return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }

}
