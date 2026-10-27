<?php

namespace App\Controllers\Bill;
use App\Controllers\BaseController;

class MonIo extends BaseController
{
    // 列表
    public function list()
    {
        $transType = intval($this->request->getGetPost('transType'));
        switch ($transType) {
            case 103101:
                if (!$this->myservice->verifyRight('RECEIPT_QUERY')) {
                    log_message('error', $this->myerp['uName'] . ' 访问 Bill/MonIo/list 业务收款 权限不足！');
                    return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
                }
                break;
            case 103102:
                if (!$this->myservice->verifyRight('PAYMENT_QUERY')) {
                    log_message('error', $this->myerp['uName'] . ' 访问 Bill/MonIo/list 业务付款 权限不足！');
                    return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
                }
                break;
            case 103103:
                if (!$this->myservice->verifyRight('INCOME_QUERY')) {
                    log_message('error', $this->myerp['uName'] . ' 访问 Bill/MonIo/list 其他收入 权限不足！');
                    return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
                }
                break;
            case 103104:
                if (!$this->myservice->verifyRight('EXPEND_QUERY')) {
                    log_message('error', $this->myerp['uName'] . ' 访问 Bill/MonIo/list 其他支出 权限不足！');
                    return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
                }
                break;
            default:
                break;
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $unBack261 = intval($this->request->getGetPost('unBack261'));
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');
        $minAmount = (float)$this->request->getGetPost('minAmount');
        $maxAmount = (float)$this->request->getGetPost('maxAmount');
        $skey = $this->request->getGetPost('skey');
        $builder = $this->db->table('bill_money');
        $builder->where('transType', $transType);
        if ($unBack261 > 0) {
            $builder->where('isBack', 1);
        }
        if ($beginDate) {
            $builder->where('billDate >=', $beginDate);
        }
        if ($endDate) {
            $builder->where('billDate <=', $endDate);
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
                    ->like('billNo', $this->db->escapeLikeString($kw))
                    ->orLike('srcNo', $this->db->escapeLikeString($kw))
                    ->orLike('description', $this->db->escapeLikeString($kw))
                    ->orLike('uName', $this->db->escapeLikeString($kw))
                    ->orLike('checkName', $this->db->escapeLikeString($kw))
                    ->orLike('contactName', $this->db->escapeLikeString($kw))
                ->groupEnd();
            }
        }
        $builder->where('isDelete', 0);
        if ($sidx == 'miniTypeName') {
            $sidx = 'miniType';
        }
        $allowedSort = ['id', 'billDate','billNo', 'billType', 'transType', 'miniType', 'srcReqType', 'srcReqId', 'srcTransType', 'srcId', 'srcNo', 'contactName','moneyStatus','description','isBack','uName','checkId','invoiceNum','isIgnoreInvoice'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $list = (clone $builder)->orderBy($orderField, $sord)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['billDate'] = $row['billDate'];
            $v[$arr]['billNo'] = $row['billNo'];
            $v[$arr]['billType'] = $row['billType'];
            $v[$arr]['transType'] = intval($row['transType']);
            $v[$arr]['miniType'] = intval($row['miniType']);
            $v[$arr]['srcReqType'] = intval($row['srcReqType']);
            $v[$arr]['srcReqId'] = intval($row['srcReqId']);
            $v[$arr]['srcTransType'] = intval($row['srcTransType']);
            $v[$arr]['srcId'] = intval($row['srcId']);
            $v[$arr]['srcNo'] = $row['srcNo'];
            $v[$arr]['contactName'] = $row['contactName'];
            $v[$arr]['amount'] = (float)$row['amount'];
            $v[$arr]['cleared'] = (float)$row['cleared'];
            $v[$arr]['moneyStatus'] = intval($row['moneyStatus']);
            $v[$arr]['description'] = $row['description'];
            $v[$arr]['isBack'] = intval($row['isBack']);
            $v[$arr]['uName'] = $row['uName'];
            $v[$arr]['checkId'] = intval($row['checkId']);
            $v[$arr]['invoiceNum'] = intval($row['invoiceNum']);
            $v[$arr]['invoiceAmount'] = $row['invoiceAmount'];
            $v[$arr]['isIgnoreInvoice'] = intval($row['isIgnoreInvoice']);
            $v[$arr]['ignoreInvoiceReason'] = $row['ignoreInvoiceReason'];
            $v[$arr]['disagreeIgnoreReason'] = $row['disagreeIgnoreReason'];
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['page'] = $page;
        $json['data']['records'] = $total;
        $json['data']['total'] = ceil($json['data']['records'] / $rows);
        $json['data']['rows'] = $v;
        return $this->response->setJSON($json);
    }
    // 信息列表 (subGrid获取详情数据时调用)
    public function entries_list()
    {
        if (!$this->myservice->verifyRight('FINANCE_MANAGER')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/MonIo/entries_list 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 10000);
        $id = intval($this->request->getGetPost('id'));
        $money_info = $this->db->table('bill_money_info AS a')
            ->select([
                'a.*',
                'b.id AS contactId',
                'b.name AS contactName',
                'c.name AS typeName',
                'd.name AS accName',
                'e.name AS wayName',
                'f.name AS uName',
            ])
            ->join('contact AS b', 'a.contactId = b.id AND b.isDelete = 0', 'left')
            ->join('category AS c', 'a.typeId = c.id AND c.isDelete = 0', 'left')
            ->join('company_account AS d', 'a.accId = d.id AND d.isDelete = 0', 'left')
            ->join('category AS e', 'a.wayId = e.id AND e.isDelete = 0', 'left')
            ->join('user AS f', 'a.uId = f.id AND f.isDelete = 0', 'left')
            ->where('a.iid', $id)
            ->where('a.isDelete', 0)
            ->orderBy('a.id', 'ASC')
            ->get()
            ->getResultArray();
        $v = [];
        foreach ($money_info as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['amount'] = abs((float)$row['amount']);
            $v[$arr]['typeId'] = intval($row['typeId']);
            $v[$arr]['typeName'] = $row['typeName'];
            $v[$arr]['accId'] = intval($row['accId']);
            $v[$arr]['accName'] = $row['accName'];
            $v[$arr]['wayId'] = intval($row['wayId']);
            $v[$arr]['wayName'] = $row['wayName'];
            $v[$arr]['uId'] = intval($row['uId']);
            $v[$arr]['uName'] = $row['uName'];
            $v[$arr]['settlementTime'] = $row['settlementTime'];
            $v[$arr]['description'] = $row['description'];
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['page'] = $page;
        $json['data']['records'] = count($money_info);
        $json['data']['total'] = ceil($json['data']['records'] / $rows);
        $json['data']['rows'] = $v;
        return $this->response->setJSON($json);
    }
    // 查询
    public function query()
    {
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('bill_money')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
        if ($data) {
            switch (intval($data['transType'])) {
                case 103101:
                    if (!$this->myservice->verifyRight('RECEIPT_QUERY')) {
                        log_message('error', $this->myerp['uName'] . ' 访问 Bill/MonIo/query 业务收款 权限不足！');
                        return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
                    }
                    break;
                case 103102:
                    if (!$this->myservice->verifyRight('PAYMENT_QUERY')) {
                        log_message('error', $this->myerp['uName'] . ' 访问 Bill/MonIo/query 业务付款 权限不足！');
                        return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
                    }
                    break;
                case 103103:
                    if (!$this->myservice->verifyRight('INCOME_QUERY')) {
                        log_message('error', $this->myerp['uName'] . ' 访问 Bill/MonIo/query 其他收入 权限不足！');
                        return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
                    }
                    break;
                case 103104:
                    if (!$this->myservice->verifyRight('EXPEND_QUERY')) {
                        log_message('error', $this->myerp['uName'] . ' 访问 Bill/MonIo/query 其他支出 权限不足！');
                        return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
                    }
                    break;
                default:
                    break;
            }
            $info['id'] = intval($data['id']);
            $info['billDate'] = $data['billDate'];
            $info['billNo'] = $data['billNo'];
            $info['billType'] = $data['billType'];
            $info['transType'] = intval($data['transType']);
            $info['miniType'] = intval($data['miniType']);
            $info['srcReqType'] = intval($data['srcReqType']);
            $info['srcReqId'] = intval($data['srcReqId']);
            $info['srcTransType'] = intval($data['srcTransType']);
            $info['srcId'] = intval($data['srcId']);
            $info['srcNo'] = $data['srcNo'];
            // 业务付款单 获取源单中的买方（我方）
            if ($data['transType'] == 103102 && in_array($data['srcTransType'], [101101, 101102])) {
                $info['srcUs'] = $this->db->table('bill_order')->select('sName')->where('id', $data['srcId'])->get()->getRowArray()['sName'] ?? '';
            } elseif ($data['transType'] == 103102 && $data['srcTransType'] == 101111) {
                $info['srcUs'] = $this->db->table('bill_contract')->select('sName')->where('id', $data['srcId'])->get()->getRowArray()['sName'] ?? '';
            } else {
                $info['srcUs'] = '';
            }
            $info['amount'] = abs((float)$data['amount']);
            $info['cleared'] = abs((float)$data['cleared']);
            $info['uId'] = intval($data['uId']);
            $info['uName'] = $data['uName'];
            $info['contactId'] = intval($data['contactId']);
            $info['contactName'] = $data['contactName'];
            $info['checkId'] = intval($data['checkId']);
            $info['checkName'] = $data['checkName'] ?? '';
            $info['description'] = $data['description'];
            $info['createTime'] = $data['createTime'];
            $info['modifyTime'] = $data['modifyTime'];
            $info['files'] = $data['files'] == '' ? [] : json_decode($data['files'], true);
            $money_info = $this->db->table('bill_money_info AS a')
                ->select([
                    'a.*',
                    'b.id AS contactId',
                    'b.name AS contactName',
                    'c.name AS typeName',
                    'd.name AS accName',
                    'e.name AS wayName',
                    'f.name AS uName',
                ])
                ->join('contact AS b', 'a.contactId = b.id AND b.isDelete = 0', 'left')
                ->join('category AS c', 'a.typeId = c.id AND c.isDelete = 0', 'left')
                ->join('company_account AS d', 'a.accId = d.id AND d.isDelete = 0', 'left')
                ->join('category AS e', 'a.wayId = e.id AND e.isDelete = 0', 'left')
                ->join('user AS f', 'a.uId = f.id AND f.isDelete = 0', 'left')
                ->where('a.iid', $id)
                ->where('a.transType', $info['transType'])
                ->where('a.isDelete', 0)
                ->orderBy('a.id', 'ASC')
                ->get()
                ->getResultArray();
            $v = [];
            foreach ($money_info as $arr => $row) {
                $v[$arr]['id'] = intval($row['id']);
                $v[$arr]['amount'] = abs((float)$row['amount']);
                $v[$arr]['typeId'] = intval($row['typeId']);
                $v[$arr]['typeName'] = $row['typeName'];
                $v[$arr]['accId'] = intval($row['accId']);
                $v[$arr]['accName'] = $row['accName'];
                $v[$arr]['wayId'] = intval($row['wayId']);
                $v[$arr]['wayName'] = $row['wayName'];
                $v[$arr]['settlementTime'] = $row['settlementTime'];
                $v[$arr]['description'] = $row['description'];
                $v[$arr]['refundId'] = $row['refundId'];
                $v[$arr]['srcId'] = intval($row['srcId']);
                $v[$arr]['zid'] = $row['zid'];
                $v[$arr]['moneyType'] = intval($row['moneyType']);
                $v[$arr]['uId'] = intval($row['uId']);
                $v[$arr]['uName'] = $row['uName'];
            }
            $info['entries'] = $v;
            $json['status'] = 200;
            $json['msg'] = 'success';
            $json['data'] = $info;
            return $this->response->setJSON($json);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 新增
    public function add()
    {
        $data = $this->request->getGetPost('postData');
        if (strlen($data) > 0) {
            $data = $this->validform((array)json_decode($data, true));
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            switch (intval($data['transType'])) {
                case 103101:
                    if (!$this->myservice->verifyRight('RECEIPT_ADD')) {
                        log_message('error', $this->myerp['uName'] . ' 访问 Bill/MonIo/add 业务收款 权限不足！');
                        return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
                    }
                    break;
                case 103102:
                    if (!$this->myservice->verifyRight('PAYMENT_ADD')) {
                        log_message('error', $this->myerp['uName'] . ' 访问 Bill/MonIo/add 业务付款 权限不足！');
                        return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
                    }
                    break;
                case 103103:
                    if (!$this->myservice->verifyRight('INCOME_ADD')) {
                        log_message('error', $this->myerp['uName'] . ' 访问 Bill/MonIo/add 其他收入 权限不足！');
                        return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
                    }
                    break;
                case 103104:
                    if (!$this->myservice->verifyRight('EXPEND_ADD')) {
                        log_message('error', $this->myerp['uName'] . ' 访问 Bill/MonIo/add 其他支出 权限不足！');
                        return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
                    }
                    break;
                default:
                    break;
            }
            if (
                ($data['srcReqType'] > 0 && $data['srcReqId'] > 0 && $this->db->table('bill_money')->where(['srcReqType' => $data['srcReqType'], 'srcReqId' => $data['srcReqId'], 'transType' => $data['transType'], 'isDelete' => 0])->countAllResults() > 0) ||
                ($data['srcTransType'] > 0 && $data['srcId'] > 0 && $this->db->table('bill_money')->where(['srcTransType' => $data['srcTransType'], 'srcId' => $data['srcId'], 'transType' => $data['transType'], 'isDelete' => 0])->countAllResults() > 0)
            ) {
                return $this->response->setJSON(['status' => -1, 'msg' => '已新增过相同单据，无法重复新增！']);
            }
            $info = array_intersect_key($data, array_flip(['billDate','billNo','billType','transType','miniType','srcReqType','srcReqId','srcTransType','srcId','srcNo','amount','cleared','moneyStatus','isBack','uId','uName','checkId','checkName','contactId','contactName','files','description','createTime','modifyTime']));
            $this->db->transStart();
            $this->db->table('bill_money')->insert($info);
            $iid = $this->db->insertID();
            if ($data['srcReqType'] > 0 && $data['srcReqId'] > 0) {
                if ($data['transType'] == 103104) { // 其他支出单
                    $this->db->table('pay_req')->where('id', $data['srcReqId'])->update([
                        'payId' => $this->myerp['uId'],
                        'payName' => $this->myerp['uName'],
                        'payTime' => date('Y-m-d H:i:s'),
                        'srcMoneyId' => $iid,
                        'srcMoneyNo' => $data['billNo'],
                        'moneyStatus' => $data['moneyStatus']
                    ]);
                }
            }
            if ($data['srcTransType'] > 0 && $data['srcId'] > 0) {
                if ($data['transType'] == 103101) { // 业务收款单
                    switch ($data['srcTransType']) {
                        case 101101: // 采购订单 退款
                            $order = $this->db->table('bill_order')->where('id', $data['srcId'])->get()->getRowArray();
                            $paidAmount = $this->db->table('bill_money')->select('cleared')->where('id', $order['srcMoneyId1'])->get()->getRowArray()['cleared'] ?? 0;
                            if ((float)$data['cleared'] >= (float)$paidAmount) {
                                $this->db->table('bill_order')->where('id', $data['srcId'])->update([
                                    'srcMoneyId2' => $iid,
                                    'srcMoneyNo2' => $data['billNo'],
                                    'moneyStatus2' => $data['moneyStatus'],
                                    'isCancel' => 1
                                ]);
                            } else {
                                $this->db->table('bill_order')->where('id', $data['srcId'])->update([
                                    'srcMoneyId2' => $iid,
                                    'srcMoneyNo2' => $data['billNo'],
                                    'moneyStatus2' => $data['moneyStatus'],
                                    'isCancel' => 0
                                ]);
                            }
                            break;
                        case 101102: // 采购直发订单 退款
                            $order = $this->db->table('bill_order')->where('id', $data['srcId'])->get()->getRowArray();
                            $paidAmount = $this->db->table('bill_money')->select('cleared')->where('id', $order['srcMoneyId1'])->get()->getRowArray()['cleared'] ?? 0;
                            if ((float)$data['cleared'] >= (float)$paidAmount) {
                                $this->db->table('bill_order')->where('id', $data['srcId'])->update([
                                    'srcMoneyId2' => $iid,
                                    'srcMoneyNo2' => $data['billNo'],
                                    'moneyStatus2' => $data['moneyStatus'],
                                    'isCancel' => 1
                                ]);
                            } else {
                                $this->db->table('bill_order')->where('id', $data['srcId'])->update([
                                    'srcMoneyId2' => $iid,
                                    'srcMoneyNo2' => $data['billNo'],
                                    'moneyStatus2' => $data['moneyStatus'],
                                    'isCancel' => 0
                                ]);
                            }
                            break;
                        case 101111: // 特殊采购单 退款
                            $contract_pur = $this->db->table('bill_contract')->where(['id' => $data['srcId'], 'isDelete' => 0])->get()->getRowArray();
                            if ($contract_pur['srcMoneyId1'] > 0) { // 如果有特采付款单
                                // 已付金额要剔除已退款金额
                                $amount = floatval($this->db->table('bill_contract_info')->selectSum('amount', 'amount')->where(['iid' => $contract_pur['id'], 'isPurchased <' => 2, 'isDelete' => 0])->get()->getRowArray()['amount'] ?? 0); // 应付金额
                                $cleared = floatval($this->db->table('bill_money_info')->selectSum('amount', 'amount')->where(['iid' => $contract_pur['srcMoneyId1'], 'isDelete' => 0])->get()->getRowArray()['amount'] ?? 0); // 已付金额
                                $cleared -= (float)$data['cleared']; // 扣除当前退款单已清金额
                                $moneyStatus = ($amount > 0 && $cleared <= 0) ? 0 : (bccomp($cleared, $amount, 2) > -1 ? 2 : 1);
                                $this->db->table('bill_contract')->where('id', $contract_pur['id'])->update([
                                    'srcMoneyId2' => $iid,
                                    'srcMoneyNo2' => $data['billNo'],
                                    'moneyStatus1' => $moneyStatus,
                                    'moneyStatus2' => $data['moneyStatus']
                                ]);
                                $this->db->table('bill_money')->where('id', $contract_pur['srcMoneyId1'])->update(['amount' => $amount, 'cleared' => $cleared, 'moneyStatus' => $moneyStatus]);
                            }
                            break;
                        case 101201: // 销售订单 收款
                            $this->db->table('bill_order')->where('id', $data['srcId'])->update([
                                'srcMoneyId1' => $iid,
                                'srcMoneyNo1' => $data['billNo'],
                                'moneyStatus1' => $data['moneyStatus']
                            ]);
                            break;
                        case 101211: // 特殊销售单 收款
                            $this->db->table('bill_contract')->where('id', $data['srcId'])->update([
                                'srcMoneyId1' => $iid,
                                'srcMoneyNo1' => $data['billNo'],
                                'moneyStatus1' => $data['moneyStatus']
                            ]);
                            break;
                        case 109101: // 快递月结单 退款
                            $this->db->table('bill_deliver')->where('id', $data['srcId'])->update([
                                'srcMoneyId2' => $iid,
                                'srcMoneyNo2' => $data['billNo'],
                                'moneyStatus2' => $data['moneyStatus']
                            ]);
                            break;
                        default:
                            break;
                    }
                } elseif ($data['transType'] == 103102) { // 业务付款单
                    switch ($data['srcTransType']) {
                        case 101101: // 采购订单 付款
                            $order = $this->db->table('bill_order')->where('id', $data['srcId'])->get()->getRowArray();
                            $backAmount = $this->db->table('bill_money')->select('cleared')->where('id', $order['srcMoneyId2'])->get()->getRowArray()['cleared'] ?? 0;
                            if ((float)$backAmount >= (float)$data['cleared']) {
                                $this->db->table('bill_order')->where('id', $data['srcId'])->update([
                                    'srcMoneyId1' => $iid,
                                    'srcMoneyNo1' => $data['billNo'],
                                    'moneyStatus1' => $data['moneyStatus'],
                                    'isCancel' => 1
                                ]);
                            } else {
                                $this->db->table('bill_order')->where('id', $data['srcId'])->update([
                                    'srcMoneyId1' => $iid,
                                    'srcMoneyNo1' => $data['billNo'],
                                    'moneyStatus1' => $data['moneyStatus'],
                                    'isCancel' => 0
                                ]);
                            }
                            break;
                        case 101102: // 采购直发订单 付款
                            $order = $this->db->table('bill_order')->where('id', $data['srcId'])->get()->getRowArray();
                            $backAmount = $this->db->table('bill_money')->select('cleared')->where('id', $order['srcMoneyId2'])->get()->getRowArray()['cleared'] ?? 0;
                            if ((float)$backAmount >= (float)$data['cleared']) {
                                $this->db->table('bill_order')->where('id', $data['srcId'])->update([
                                    'srcMoneyId1' => $iid,
                                    'srcMoneyNo1' => $data['billNo'],
                                    'moneyStatus1' => $data['moneyStatus'],
                                    'isCancel' => 1
                                ]);
                            } else {
                                $this->db->table('bill_order')->where('id', $data['srcId'])->update([
                                    'srcMoneyId1' => $iid,
                                    'srcMoneyNo1' => $data['billNo'],
                                    'moneyStatus1' => $data['moneyStatus'],
                                    'isCancel' => 0
                                ]);
                            }
                            break;
                        case 101111: // 特殊采购单 付款
                            $this->db->table('bill_contract')->where('id', $data['srcId'])->update([
                                'srcMoneyId1' => $iid,
                                'srcMoneyNo1' => $data['billNo'],
                                'moneyStatus1' => $data['moneyStatus']
                            ]);
                            break;
                        case 101201: // 销售订单 退款
                            $order = $this->db->table('bill_order')->where('id', $data['srcId'])->get()->getRowArray();
                            if ($order['goodsStatus1'] == 0 && (float)$data['cleared'] >= (float)$order['amount']) {
                                $this->db->table('bill_order')->where('id', $data['srcId'])->update([
                                    'srcMoneyId2' => $iid,
                                    'srcMoneyNo2' => $data['billNo'],
                                    'moneyStatus2' => $data['moneyStatus'],
                                    'isCancel' => 1
                                ]);
                            } else {
                                $this->db->table('bill_order')->where('id', $data['srcId'])->update([
                                    'srcMoneyId2' => $iid,
                                    'srcMoneyNo2' => $data['billNo'],
                                    'moneyStatus2' => $data['moneyStatus'],
                                    'isCancel' => 0
                                ]);
                            }
                            break;
                        case 101211: // 特殊销售单 退款
                            $this->db->table('bill_contract')->where('id', $data['srcId'])->update([
                                'srcMoneyId2' => $iid,
                                'srcMoneyNo2' => $data['billNo'],
                                'moneyStatus2' => $data['moneyStatus']
                            ]);
                            break;
                        case 109101: // 快递月结单 付款
                            $this->db->table('bill_deliver')->where('id', $data['srcId'])->update([
                                'srcMoneyId1' => $iid,
                                'srcMoneyNo1' => $data['billNo'],
                                'moneyStatus1' => $data['moneyStatus']
                            ]);
                            break;
                        default:
                            break;
                    }
                }
            }
            $this->money_info(intval($iid), $data);
            $this->myservice->log('新增了' . ($data['miniTypeName'] ?? $this->myerp['transTypeName'][$data['transType']]) . '，单号：' . $data['billNo']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '新增成功！', 'data' => ['id' => intval($iid)]]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    // 编辑
    public function update()
    {
        $data = $this->request->getGetPost('postData');
        if (strlen($data) > 0) {
            $data = $this->validform((array)json_decode($data, true));
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            switch (intval($data['transType'])) {
                case 103101:
                    if (!$this->myservice->verifyRight('RECEIPT_UPDATE')) {
                        log_message('error', $this->myerp['uName'] . ' 访问 Bill/MonIo/update 业务收款 权限不足！');
                        return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
                    }
                    break;
                case 103102:
                    if (!$this->myservice->verifyRight('PAYMENT_UPDATE')) {
                        log_message('error', $this->myerp['uName'] . ' 访问 Bill/MonIo/update 业务付款 权限不足！');
                        return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
                    }
                    break;
                case 103103:
                    if (!$this->myservice->verifyRight('INCOME_UPDATE')) {
                        log_message('error', $this->myerp['uName'] . ' 访问 Bill/MonIo/update 其他收入 权限不足！');
                        return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
                    }
                    break;
                case 103104:
                    if (!$this->myservice->verifyRight('EXPEND_UPDATE')) {
                        log_message('error', $this->myerp['uName'] . ' 访问 Bill/MonIo/update 其他支出 权限不足！');
                        return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
                    }
                    break;
                default:
                    break;
            }
            $info = array_intersect_key($data, array_flip(['billDate', 'amount', 'cleared', 'moneyStatus', 'contactId', 'contactName', 'files', 'description', 'modifyTime']));
            $oldData = $this->db->table('bill_money')->where('id', $data['id'])->get()->getRowArray();
            $this->db->transStart();
            $this->db->table('bill_money')->where('id', $data['id'])->update($info);
            $this->money_info(intval($data['id']), $data);
            if ($data['srcReqType'] > 0 && $data['srcReqId'] > 0) {
                if ($data['transType'] == 103104) { // 其他支出单
                    if ($oldData['isBack'] == 2) {
                        return $this->response->setJSON(['status' => -1, 'msg' => '已一键退回单据无法编辑！']);
                    }
                    $this->db->table('pay_req')
                        ->where('id', $data['srcReqId'])
                        ->update(['moneyStatus' => $data['moneyStatus']]);
                }
            }
            if ($data['srcTransType'] > 0 && $data['srcId'] > 0) {
                if ($data['transType'] == 103101) { // 业务收款单
                    switch ($data['srcTransType']) {
                        case 101101: // 采购订单 退款
                            $order = $this->db->table('bill_order')->where('id', $data['srcId'])->get()->getRowArray();
                            $paidAmount = $this->db->table('bill_money')->where('id', $order['srcMoneyId1'])->get()->getRow('cleared') ?? 0;
                            if ($data['cleared'] >= $paidAmount) {
                                $this->db->table('bill_order')
                                    ->where('id', $data['srcId'])
                                    ->update(['moneyStatus2' => $data['moneyStatus'], 'isCancel' => 1]);
                            } else {
                                $this->db->table('bill_order')
                                    ->where('id', $data['srcId'])
                                    ->update(['moneyStatus2' => $data['moneyStatus'], 'isCancel' => 0]);
                            }
                            break;
                        case 101102: // 采购直发订单 退款
                            $order = $this->db->table('bill_order')->where('id', $data['srcId'])->get()->getRowArray();
                            $paidAmount = $this->db->table('bill_money')->where('id', $order['srcMoneyId1'])->get()->getRow('cleared') ?? 0;
                            if ($data['cleared'] >= $paidAmount) {
                                $this->db->table('bill_order')
                                    ->where('id', $data['srcId'])
                                    ->update(['moneyStatus2' => $data['moneyStatus'], 'isCancel' => 1]);
                            } else {
                                $this->db->table('bill_order')
                                    ->where('id', $data['srcId'])
                                    ->update(['moneyStatus2' => $data['moneyStatus'], 'isCancel' => 0]);
                            }
                            break;
                        case 101111: // 特殊采购单 退款
                            // 针对性处理相关信息
                            $contract_pur = $this->db->table('bill_contract')->where(['id' => $data['srcId'], 'isDelete' => 0])->get()->getRowArray();
                            if ($contract_pur['srcMoneyId1'] > 0) { // 如果有特采付款单
                                // 已付金额要剔除已退款金额
                                $amount = floatval($this->db->table('bill_contract_info')->selectSum('amount', 'amount')->where(['iid' => $contract_pur['id'], 'isPurchased <' => 2, 'isDelete' => 0])->get()->getRowArray()['amount'] ?? 0); // 应付金额
                                $cleared = floatval($this->db->table('bill_money_info')->selectSum('amount', 'amount')->where(['iid' => $contract_pur['srcMoneyId1'], 'isDelete' => 0])->get()->getRowArray()['amount'] ?? 0); // 已付金额
                                $cleared -= (float)$data['cleared']; // 扣除当前退款单已清金额
                                $moneyStatus = ($amount > 0 && $cleared <= 0) ? 0 : (bccomp($cleared, $amount, 2) > -1 ? 2 : 1);
                                $this->db->table('bill_contract')->where('id', $contract_pur['id'])->update(['moneyStatus1' => $moneyStatus, 'moneyStatus2' => $data['moneyStatus']]);
                                $this->db->table('bill_money')->where('id', $contract_pur['srcMoneyId1'])->update(['amount' => $amount, 'cleared' => $cleared, 'moneyStatus' => $moneyStatus]);
                            }
                            break;
                        case 101201: // 销售订单 收款
                            $this->db->table('bill_order')
                                ->where('id', $data['srcId'])
                                ->update(['moneyStatus1' => $data['moneyStatus']]);
                            break;
                        case 101211: // 特殊销售单 收款
                            $this->db->table('bill_contract')
                                ->where('id', $data['srcId'])
                                ->update(['moneyStatus1' => $data['moneyStatus']]);
                            break;
                        case 109101: // 快递月结单 退款
                            $this->db->table('bill_deliver')
                                ->where('id', $data['srcId'])
                                ->update(['moneyStatus2' => $data['moneyStatus']]);
                            break;
                        default:
                            break;
                    }
                } elseif ($data['transType'] == 103102) { // 业务付款单
                    switch ($data['srcTransType']) {
                        case 101101: // 采购订单 付款
                            $order = $this->db->table('bill_order')->where('id', $data['srcId'])->get()->getRowArray();
                            $backAmount = $this->db->table('bill_money')->where('id', $order['srcMoneyId2'])->get()->getRowArray()['cleared'] ?? 0;
                            if ($backAmount >= $data['cleared']) {
                                $this->db->table('bill_order')
                                    ->where('id', $data['srcId'])
                                    ->update(['moneyStatus1' => $data['moneyStatus'], 'isCancel' => 1]);
                            } else {
                                $this->db->table('bill_order')
                                    ->where('id', $data['srcId'])
                                    ->update(['moneyStatus1' => $data['moneyStatus'], 'isCancel' => 0]);
                            }
                            break;
                        case 101102: // 采购直发订单 付款
                            $order = $this->db->table('bill_order')->where('id', $data['srcId'])->get()->getRowArray();
                            $backAmount = $this->db->table('bill_money')->where('id', $order['srcMoneyId2'])->get()->getRowArray()['cleared'] ?? 0;
                            if ($backAmount >= $data['cleared']) {
                                $this->db->table('bill_order')
                                    ->where('id', $data['srcId'])
                                    ->update(['moneyStatus1' => $data['moneyStatus'], 'isCancel' => 1]);
                            } else {
                                $this->db->table('bill_order')
                                    ->where('id', $data['srcId'])
                                    ->update(['moneyStatus1' => $data['moneyStatus'], 'isCancel' => 0]);
                            }
                            break;
                        case 101111: // 特殊采购单 付款
                            $this->db->table('bill_contract')
                                ->where('id', $data['srcId'])
                                ->update(['moneyStatus1' => $data['moneyStatus']]);
                            break;
                        case 101201: // 销售订单 退款
                            $order = $this->db->table('bill_order')->where('id', $data['srcId'])->get()->getRowArray();
                            if ($order['goodsStatus1'] == 0 && $data['cleared'] >= $order['amount']) {
                                $this->db->table('bill_order')
                                    ->where('id', $data['srcId'])
                                    ->update(['moneyStatus2' => $data['moneyStatus'], 'isCancel' => 1]);
                            } else {
                                $this->db->table('bill_order')
                                    ->where('id', $data['srcId'])
                                    ->update(['moneyStatus2' => $data['moneyStatus'], 'isCancel' => 0]);
                            }
                            break;
                        case 101211: // 特殊销售单 退款
                            $this->db->table('bill_contract')
                                ->where('id', $data['srcId'])
                                ->update(['moneyStatus2' => $data['moneyStatus']]);
                            break;
                        case 109101: // 快递月结单 付款
                            $this->db->table('bill_deliver')
                                ->where('id', $data['srcId'])
                                ->update(['moneyStatus1' => $data['moneyStatus']]);
                            break;
                        default:
                            break;
                    }
                }
            }
            $this->myservice->log('编辑了' . ($data['miniTypeName'] ?? $this->myerp['transTypeName'][$data['transType']]) . '，单号：' . $data['billNo']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '编辑成功！', 'data' => ['id' => $data['id']]]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 删除
    public function delete()
    {
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('bill_money')->where('id', $id)->get()->getRowArray();
        if ($data) {
            switch (intval($data['transType'])) {
                case 103101:
                    if (!$this->myservice->verifyRight('RECEIPT_DELETE')) {
                        log_message('error', $this->myerp['uName'] . ' 访问 Bill/MonIo/delete 业务收款 权限不足！');
                        return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
                    }
                    break;
                case 103102:
                    if (!$this->myservice->verifyRight('PAYMENT_DELETE')) {
                        log_message('error', $this->myerp['uName'] . ' 访问 Bill/MonIo/delete 业务付款 权限不足！');
                        return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
                    }
                    break;
                case 103103:
                    if (!$this->myservice->verifyRight('INCOME_DELETE')) {
                        log_message('error', $this->myerp['uName'] . ' 访问 Bill/MonIo/delete 其他收入 权限不足！');
                        return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
                    }
                    break;
                case 103104:
                    if (!$this->myservice->verifyRight('EXPEND_DELETE')) {
                        log_message('error', $this->myerp['uName'] . ' 访问 Bill/MonIo/delete 其他支出 权限不足！');
                        return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
                    }
                    break;
                default:
                    break;
            }
            if ($data['transType'] == 103101) {
                if ($data['miniType'] == 1) {
                    $data['miniTypeName'] = '销售收款单';
                } elseif ($data['miniType'] == 2) {
                    $data['miniTypeName'] = '采购退款单';
                } elseif ($data['miniType'] == 3) {
                    $data['miniTypeName'] = '特采退款单';
                } elseif ($data['miniType'] == 4) {
                    $data['miniTypeName'] = '快递退款单';
                }
            } elseif ($data['transType'] == 103102) {
                if ($data['miniType'] == 1) {
                    $data['miniTypeName'] = '销售退款单';
                } elseif ($data['miniType'] == 2) {
                    $data['miniTypeName'] = '采购付款单';
                } elseif ($data['miniType'] == 3) {
                    $data['miniTypeName'] = '特采付款单';
                } elseif ($data['miniType'] == 4) {
                    $data['miniTypeName'] = '快递付款单';
                }
            }
            if (!$this->myservice->verifyRight('SYS_ADMIN') && $data['uId'] != $this->myerp['uId']) {
                return $this->response->setJSON(['status' => -1, 'msg' => '不能删除别人录入的' . ($data['miniTypeName'] ?? $this->myerp['transTypeName'][$data['transType']]) . '！仅本人或管理员可删除！']);
            }
            $this->db->transStart();
            // 其他收入单、其他支出单
            if ($data['transType'] == 103103 || $data['transType'] == 103104) {
                if ($data['isBack'] == 2) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '已一键退回单据无法删除！']);
                }
                // 其他支出单有关联的付款申请、报销申请
                if ($data['transType'] == 103104 && intval($data['srcReqType']) > 0 && intval($data['srcReqId']) > 0) {
                    $this->db->table('pay_req')->where('id', $data['srcReqId'])->update(['payId' => 0, 'payName' => '', 'payTime' => '0000-00-00 00:00:00', 'srcMoneyId' => 0, 'srcMoneyNo' => '', 'moneyStatus' => 0]);
                }
            }
            if (intval($data['srcTransType']) > 0 && intval($data['srcId']) > 0) {
                if ($data['transType'] == 103101) { // 业务收款单
                    switch ($data['srcTransType']) {
                        case 101101: // 采购订单 退款
                            $this->db->table('bill_order')->where('id', $data['srcId'])->update(['srcMoneyId2' => 0, 'srcMoneyNo2' => '', 'moneyStatus2' => 0, 'isCancel' => 0]);
                            break;
                        case 101102: // 采购直发订单 退款
                            $this->db->table('bill_order')->where('id', $data['srcId'])->update(['srcMoneyId2' => 0, 'srcMoneyNo2' => '', 'moneyStatus2' => 0, 'isCancel' => 0]);
                            break;
                        case 101111: // 特殊采购单 退款
                            $this->db->table('bill_contract')->where('id', $data['srcId'])->update(['srcMoneyId2' => 0, 'srcMoneyNo2' => '', 'moneyStatus2' => 0]);
                            // 更新 bill_contract_info 已付款信息
                            $contractPur = $this->db->table('bill_contract')->select('id, billNo, reqNo, srcMoneyId1, srcMoneyId2')->where(['id' => $data['srcId'], 'isDelete' => 0])->get()->getRowArray();
                            $contractPurInfo = $this->db->table('bill_contract_info')->select('id, zid, isPurchased, paidAmount, paidTime')->where(['iid' => $data['srcId'], 'isDelete' => 0])->get()->getResultArray();
                            if ($contractPurInfo) {
                                $contractSaleInfo = [];
                                foreach ($contractPurInfo as $k1 => $v1) {
                                    $contractSaleInfoId = 0;
                                    if ($contractPur['reqNo']) {
                                        $contractSaleInfoId = $this->db->table('bill_contract_info')->select('id')->where(['billNo' => $contractPur['reqNo'], 'zid' => $v1['zid'], 'isDelete' => 0])->get()->getRowArray()['id'] ?? 0;
                                    }
                                    $paidAmount = $this->db->table('bill_money_info')->selectSum('amount', 'paidAmount')->where(['iid' => $contractPur['srcMoneyId1'], 'zid' => $v1['zid'], 'isDelete' => 0])->get()->getRowArray()['paidAmount'] ?? 0;
                                    if (floatval($paidAmount) > 0) {
                                        $contractPurInfo[$k1]['paidAmount'] = floatval($paidAmount);
                                        $contractPurInfo[$k1]['paidTime'] = $v1['paidTime'] == '0000-00-00 00:00:00' ? date('Y-m-d H:i:s') : $v1['paidTime'];
                                        $contractPurInfo[$k1]['isPurchased'] = $v1['isPurchased'] == 2 ? 2 : 1;
                                        if ($contractSaleInfoId > 0) {
                                            array_push($contractSaleInfo, ['id' => $contractSaleInfoId, 'isPurchased' => $contractPurInfo[$k1]['isPurchased']]);
                                        }
                                    } else {
                                        $contractPurInfo[$k1]['paidAmount'] = 0;
                                        $contractPurInfo[$k1]['isPurchased'] = $v1['isPurchased'] == 2 ? 2 : 0;
                                        if ($contractSaleInfoId > 0) {
                                            array_push($contractSaleInfo, ['id' => $contractSaleInfoId, 'isPurchased' => $contractPurInfo[$k1]['isPurchased']]);
                                        }
                                    }
                                }
                                $this->db->table('bill_contract_info')->updateBatch($contractPurInfo, 'id');
                                if (count($contractSaleInfo) > 0) {
                                    $this->db->table('bill_contract_info')->updateBatch($contractSaleInfo, 'id');
                                }
                            }
                            break;
                        case 101201: // 销售订单 收款
                            $this->db->table('bill_order')->where(['id' => $data['srcId']])->update(['srcMoneyId1' => 0, 'srcMoneyNo1' => '', 'moneyStatus1' => 0]);
                            break;
                        case 101211: // 特殊销售单 收款
                            $this->db->table('bill_contract')->where(['id' => $data['srcId']])->update(['srcMoneyId1' => 0, 'srcMoneyNo1' => '', 'moneyStatus1' => 0]);
                            break;
                        case 109101: // 快递月结单 退款
                            $this->db->table('bill_deliver')->where(['id' => $data['srcId']])->update(['srcMoneyId2' => 0, 'srcMoneyNo2' => '', 'moneyStatus2' => 0]);
                            break;
                        default:
                            break;
                    }
                } elseif ($data['transType'] == 103102) { // 业务付款单
                    switch ($data['srcTransType']) {
                        case 101101: // 采购订单 付款
                            $this->db->table('bill_order')->where(['id' => $data['srcId']])->update(['srcMoneyId1' => 0, 'srcMoneyNo1' => '', 'moneyStatus1' => 0]);
                            break;
                        case 101102: // 采购直发订单 付款
                            $this->db->table('bill_order')->where(['id' => $data['srcId']])->update(['srcMoneyId1' => 0, 'srcMoneyNo1' => '', 'moneyStatus1' => 0]);
                            break;
                        case 101111: // 特殊采购单 付款
                            $this->db->table('bill_contract')->where(['id' => $data['srcId']])->update(['srcMoneyId1' => 0, 'srcMoneyNo1' => '', 'moneyStatus1' => 0]);
                            // 更新 bill_contract_info 已付款信息
                            $contractPur = $this->db->table('bill_contract')->select('id,billNo,reqNo,srcMoneyId1,srcMoneyId2')->where(['id' => $data['srcId'], 'isDelete' => 0])->get()->getRowArray();
                            $contractPurInfo = $this->db->table('bill_contract_info')->select('id, zid, isPurchased, paidAmount, paidTime')->where(['iid' => $data['srcId'], 'isDelete' => 0])->get()->getResultArray();
                            if (!empty($contractPurInfo)) {
                                $contractSaleInfo = [];
                                foreach ($contractPurInfo as $k1 => $v1) {
                                    $contractSaleInfoId = '';
                                    if ($contractPur['reqNo']) {
                                        $contractSaleInfoId = $this->db->table('bill_contract_info')->select('id')->where(['billNo' => $contractPur['reqNo'], 'zid' => $v1['zid'], 'isDelete' => 0])->get()->getRowArray()['id'] ?? 0;
                                    }
                                    $contractPurInfo[$k1]['paidAmount'] = 0;
                                    $contractPurInfo[$k1]['isPurchased'] = $v1['isPurchased'] == 2 ? 2 : 0;
                                    if ($contractSaleInfoId > 0) {
                                        array_push($contractSaleInfo, ['id' => $contractSaleInfoId, 'isPurchased' => $contractPurInfo[$k1]['isPurchased']]);
                                    }
                                }
                                $this->db->table('bill_contract_info')->updateBatch($contractPurInfo, 'id');
                                if (count($contractSaleInfo) > 0) {
                                    $this->db->table('bill_contract_info')->updateBatch($contractSaleInfo, 'id');
                                }
                            }
                            break;
                        case 101201: // 销售订单 退款
                            $this->db->table('bill_order')->where(['id' => $data['srcId']])->update(['srcMoneyId2' => 0, 'srcMoneyNo2' => '', 'moneyStatus2' => 0, 'isCancel' => 0]);
                            break;
                        case 101211: // 特殊销售单 退款
                            $this->db->table('bill_contract')->where(['id' => $data['srcId']])->update(['srcMoneyId2' => 0, 'srcMoneyNo2' => '', 'moneyStatus2' => 0]);
                            break;
                        case 109101: // 快递月结单 付款
                            $this->db->table('bill_deliver')->where(['id' => $data['srcId']])->update(['srcMoneyId1' => 0, 'srcMoneyNo1' => '', 'moneyStatus1' => 0]);
                            break;
                        default:
                            break;
                    }
                }
            }
            $this->db->table('bill_money')->where(['id' => $id])->update(['isDelete' => 1]);
            $this->db->table('bill_money_info')->where(['iid' => $id, 'transType' => $data['transType']])->update(['isDelete' => 1]);
            if ($data['isBack'] == 3 && $data['srcNo']) { //如果是对冲单被删除，重置源单退回状态：判断源单是否包含保证金，包含则重置为1（应退回），不包含则重置为0
                $Num261 = $this->db->table('bill_money_info')->where(['billNo' => $data['srcNo'], 'typeId' => 261, 'isDelete' => 0])->countAllResults();
                $this->db->table('bill_money')->where(['billNo' => $data['srcNo']])->update(['isBack' => ($Num261 > 0 ? 1 : 0)]);
            }
            $this->myservice->log('删除了' . ($data['miniTypeName'] ?? $this->myerp['transTypeName'][$data['transType']]) . '，单号：' . $data['billNo']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '删除成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 审核
    public function check()
    {
        $id = intval($this->request->getGetPost('id'));
        $checkId = intval($this->request->getGetPost('checkId'));
        $checkName = $this->request->getGetPost('checkName');
        $transType = intval($this->request->getGetPost('transType'));
        switch ($transType) {
            case 103101:
                if (!$this->myservice->verifyRight('RECEIPT_CHECK')) {
                    log_message('error', $this->myerp['uName'] . ' 访问 Bill/MonIo/check 业务收款 权限不足！');
                    return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
                }
                break;
            case 103102:
                if (!$this->myservice->verifyRight('PAYMENT_CHECK')) {
                    log_message('error', $this->myerp['uName'] . ' 访问 Bill/MonIo/check 业务付款 权限不足！');
                    return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
                }
                break;
            case 103103:
                if (!$this->myservice->verifyRight('INCOME_CHECK')) {
                    log_message('error', $this->myerp['uName'] . ' 访问 Bill/MonIo/check 其他收入 权限不足！');
                    return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
                }
                break;
            case 103104:
                if (!$this->myservice->verifyRight('EXPEND_CHECK')) {
                    log_message('error', $this->myerp['uName'] . ' 访问 Bill/MonIo/check 其他支出 权限不足！');
                    return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
                }
                break;
            default:
                break;
        }
        $data = $this->db->table('bill_money')->where(['id' => $id])->get()->getRowArray();
        if ($data) {
            $this->db->transStart();
            $this->db->table('bill_money')->where(['id' => $id])->update(['checkId ' => $checkId, 'checkName ' => $checkName]);
            $this->db->table('bill_money_info')->where(['iid' => $id, 'transType' => $transType])->update(['checkId ' => $checkId]);
            if ($data['transType'] == 103101) {
                if ($data['miniType'] == 1) {
                    $data['miniTypeName'] = '销售收款单';
                } elseif ($data['miniType'] == 2) {
                    $data['miniTypeName'] = '采购退款单';
                } elseif ($data['miniType'] == 3) {
                    $data['miniTypeName'] = '特采退款单';
                } elseif ($data['miniType'] == 4) {
                    $data['miniTypeName'] = '快递退款单';
                }
            } elseif ($data['transType'] == 103102) {
                if ($data['miniType'] == 1) {
                    $data['miniTypeName'] = '销售退款单';
                } elseif ($data['miniType'] == 2) {
                    $data['miniTypeName'] = '采购付款单';
                } elseif ($data['miniType'] == 3) {
                    $data['miniTypeName'] = '特采付款单';
                } elseif ($data['miniType'] == 4) {
                    $data['miniTypeName'] = '快递付款单';
                }
            }
            $this->myservice->log(($checkId > 0 ? '审核了' : '反审核了') . ($data['miniTypeName'] ?? $this->myerp['transTypeName'][$data['transType']]) . '，单号：' . $data['billNo']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '操作成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 更新备注
    public function updatedes()
    {
        if (!$this->myservice->verifyRight('FINANCE_MANAGER')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/MonIo/updatedes 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $newDes = $this->request->getGetPost('newDes');
        $data = $this->db->table('bill_money')->where('id', $id)->get()->getRowArray();
        if ($data) {
            $v['description'] = $newDes;
            $v['modifyTime'] = date("Y-m-d H:i:s");
            $this->db->transStart();
            $this->db->table('bill_money')->where('id', $id)->update($v);
            if ($data['transType'] == 103101) {
                if ($data['miniType'] == 1) {
                    $data['miniTypeName'] = '销售收款单';
                } elseif ($data['miniType'] == 2) {
                    $data['miniTypeName'] = '采购退款单';
                } elseif ($data['miniType'] == 3) {
                    $data['miniTypeName'] = '特采退款单';
                } elseif ($data['miniType'] == 4) {
                    $data['miniTypeName'] = '快递退款单';
                }
            } elseif ($data['transType'] == 103102) {
                if ($data['miniType'] == 1) {
                    $data['miniTypeName'] = '销售退款单';
                } elseif ($data['miniType'] == 2) {
                    $data['miniTypeName'] = '采购付款单';
                } elseif ($data['miniType'] == 3) {
                    $data['miniTypeName'] = '特采付款单';
                } elseif ($data['miniType'] == 4) {
                    $data['miniTypeName'] = '快递付款单';
                }
            }
            $this->myservice->log('单独更新了' . ($data['miniTypeName'] ?? $this->myerp['transTypeName'][$data['transType']]) . '的备注，单号：' . $data['billNo']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '更新成功！', 'data' => ['id' => $id]]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 上传资料
    public function uploadfile()
    {
        if (!$this->myservice->verifyRight('FINANCE_MANAGER')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/MonIo/uploadfile 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        set_time_limit(0);
        session_write_close();
        if (empty($_FILES)){
            return $this->response->setJSON(['status' => -1, 'msg' => '没有上传文件！']);
        };
        $uppath = 'data/upfile/monIo/';
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
        $this->myservice->log('上传了财务单据资料：' . $_FILES['file']['name']);
        $json['status'] = 200;
        $json['msg'] = '资料上传成功';
        $json['file'] = ['name' => $_FILES['file']['name'], 'url' => $target_file, 'upName' => $this->myerp['uName'], 'upTime' => date('Y-m-d')];
        return $this->response->setJSON($json);
    }
    // info数据
    private function money_info(int $iid, array $data)
    {
        $v = [];
        foreach ($data['entries'] as $arr => $row) {
            if (isset($row['moneyType']) && $row['moneyType'] == -1) { // 如果是取消的预退款，记录系统日志，并跳出本次循环。
                $this->myservice->log('对特殊采购单 ' . $data['srcNo'] . ' 中自编序号为 ' . $row['zid'] . ' 的产品进行了取消预退款，金额：' . $row['amount']);
                continue;
            }
            $x['iid'] = $iid;
            $x['billNo'] = $data['billNo'];
            $x['billDate'] = $data['billDate'];
            $x['billType'] = $data['billType'];
            $x['transType'] = $data['transType'];
            $x['miniType'] = intval($data['miniType'] ?? 0);
            $x['amount'] = $row['amount'];
            $x['contactId'] = intval($data['contactId']);
            $x['typeId'] = intval($row['typeId']);
            $x['accId'] = intval($row['accId']);
            $x['wayId'] = intval($row['wayId']);
            $x['uId'] = intval($row['uId'] ?? $data['uId']);
            $x['checkId'] = intval($data['checkId']);
            $x['createTime'] = $row['createTime'] ?? date('Y-m-d H:i:s');
            $x['settlementTime'] = $row['settlementTime'] ?? date('Y-m-d H:i:s');
            $x['description'] = $row['description'] ?? '';
            $x['refundId'] = $row['refundId'] ?? '';
            $x['srcId'] = intval($data['srcId']);
            $x['zid'] = $row['zid'] ?? '';
            $x['moneyType'] = $row['moneyType'] ?? 0;
            if ($data['transType'] == 103101 && $data['miniType'] == 3) { // 特采退款单
                if ($row['moneyType'] == 11) {
                    $this->myservice->log('对特殊采购单 ' . $data['srcNo'] . ' 中自编序号为 ' . $row['zid'] . ' 的产品进行了预退款转实退款，金额：' . $row['amount']);
                    $x['moneyType'] = 1;
                }
                if ($row['moneyType'] == 12) {
                    $this->myservice->log('对特殊采购单 ' . $data['srcNo'] . ' 中自编序号为 ' . $row['zid'] . ' 的产品进行了返现退款，金额：' . $row['amount']);
                    $x['moneyType'] = 2;
                }
                if ($row['moneyType'] == 19) {
                    $this->myservice->log('对特殊采购单 ' . $data['srcNo'] . ' 中自编序号为 ' . $row['zid'] . ' 的产品进行了预退款，金额：' . $row['amount']);
                    $x['moneyType'] = 9;
                }
            } elseif ($data['transType'] == 103102 && $data['miniType'] == 3) { // 特采付款单
                if ($row['moneyType'] == 11) {
                    $this->myservice->log('对特殊采购单 ' . $data['srcNo'] . ' 中自编序号为 ' . $row['zid'] . ' 的产品进行了额外付款，金额：' . $row['amount']);
                    $x['moneyType'] = 1;
                }
            }
            array_push($v, $x);
        }
        if ($data['id'] > 0) {
            // 真删除，只删自己的条目
            $this->db->table('bill_money_info')->where(['iid' => $iid, 'transType' => $data['transType'], 'uId' => $data['uId']])->delete();
        }
        if (count($v) > 0) {
            $this->db->table('bill_money_info')->insertBatch($v);
        }
        // 如果是特采付款单、特采退款单，更新 bill_contract_info 已付款信息，并自动更新特采单与对应特销单该产品采买状态
        if (($data['transType'] == 103101 || $data['transType'] == 103102) && $data['srcTransType'] == 101111 && $data['srcId'] > 0) {
            $contractPur = $this->db->table('bill_contract')->select('id, billNo, reqNo, srcMoneyId1, srcMoneyId2')->where(['id' => $data['srcId'], 'isDelete' => 0])->get()->getRowArray();
            $contractPurInfo = $this->db->table('bill_contract_info')->select('id, zid, isPurchased, paidAmount, paidTime')->where(['iid' => $data['srcId'], 'isDelete' => 0])->get()->getResultArray();
            // 更新特采单、特销单产品购买信息
            if ($contractPurInfo) {
                $contractSaleInfo = [];
                foreach ($contractPurInfo as $k1 => $v1) {
                    $contractSaleInfoId = '';
                    if ($contractPur['reqNo']) {
                        $contractSaleInfoId = $this->db->table('bill_contract_info')->select('id')->where(['billNo' => $contractPur['reqNo'], 'zid' => $v1['zid'], 'isDelete' => 0])->get()->getRowArray()['id'] ?? 0;
                    }
                    $paidAmount = $this->db->table('bill_money_info')->selectSum('amount', 'paidAmount')->where(['iid' => $contractPur['srcMoneyId1'], 'zid' => $v1['zid'], 'isDelete' => 0])->get()->getRowArray()['paidAmount'] ?? 0;
                    $refundAmount = $this->db->table('bill_money_info')->selectSum('amount', 'refundAmount')->where(['iid' => $contractPur['srcMoneyId2'], 'zid' => $v1['zid'], 'isDelete' => 0])->get()->getRowArray()['refundAmount'] ?? 0;
                    if ((floatval($paidAmount) - floatval($refundAmount)) > 0) {
                        $contractPurInfo[$k1]['paidAmount'] = floatval($paidAmount) - floatval($refundAmount);
                        $contractPurInfo[$k1]['paidTime'] = $v1['paidTime'] == '0000-00-00 00:00:00' ? date('Y-m-d H:i:s') : $v1['paidTime'];
                        $contractPurInfo[$k1]['isPurchased'] = $v1['isPurchased'] == 2 ? 2 : 1;
                        if ($contractSaleInfoId > 0) {
                            array_push($contractSaleInfo, ['id' => $contractSaleInfoId, 'isPurchased' => $contractPurInfo[$k1]['isPurchased']]);
                        }
                    } else {
                        $contractPurInfo[$k1]['paidAmount'] = 0;
                        $contractPurInfo[$k1]['isPurchased'] = $v1['isPurchased'] == 2 ? 2 : 0;
                        if ($contractSaleInfoId > 0) {
                            array_push($contractSaleInfo, ['id' => $contractSaleInfoId, 'isPurchased' => $contractPurInfo[$k1]['isPurchased']]);
                        }
                    }
                }
                $this->db->table('bill_contract_info')->updateBatch($contractPurInfo, 'id');
                if (count($contractSaleInfo) > 0) {
                    $this->db->table('bill_contract_info')->updateBatch($contractSaleInfo, 'id');
                }
            }
        }
    }
    // 数据验证
    private function validform(array $data)
    {
        $data['entries'] = $data['entries'] ?? [];
        if (count($data['entries']) < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
        }
        $data['srcReqType'] = isset($data['srcReqType']) ? intval($data['srcReqType']) : 0;
        $data['srcReqId'] = isset($data['srcReqId']) ? intval($data['srcReqId']) : 0;
        $data['srcTransType'] = isset($data['srcTransType']) ? intval($data['srcTransType']) : 0;
        $data['srcId'] = isset($data['srcId']) ? intval($data['srcId']) : 0;
        $data['srcNo'] = $data['srcNo'] ?? '';
        $data['id'] = isset($data['id']) ? intval($data['id']) : -1;
        $data['billDate'] = $data['billDate'] ?? date('Y-m-d');
        $data['transType'] = intval($data['transType']);
        $data['amount'] = abs((float)$data['amount']);
        $data['cleared'] = abs((float)$data['cleared']);
        $data['isBack'] = 0;
        $slef_cleared = 0;
        if ($data['id'] < 1) { // 是新增
            foreach ($data['entries'] as $arr => $row) {
                // 累加自己条目的金额
                if ($row['uId'] == $this->myerp['uId']) {
                    $slef_cleared += abs((float)$row['amount']);
                }
                // 包含保证金，设置一键退回状态为初始值1
                if ($row['typeId'] == 261) {
                    $data['isBack'] = 1;
                }
            }
            $data['cleared'] = $slef_cleared;
        } else { // 是修改
            foreach ($data['entries'] as $arr => $row) {
                // 累加自己条目的金额
                if ($row['uId'] == $this->myerp['uId']) {
                    $slef_cleared += abs((float)$row['amount']);
                } else {
                    unset($data['entries'][$arr]); // 剔除其他人的条目，只保留自己的条目用于后续更新
                }
            }
            $other_cleared = $this->db->table('bill_money_info')->selectSum('amount', 'otherCleared')->where(['iid' => $data['id'], 'uId !=' => $this->myerp['uId'], 'isDelete' => 0])->get()->getRowArray()['otherCleared'] ?? 0;
            $data['cleared'] = abs((float)$other_cleared) + $slef_cleared;
        }
        $data['miniType'] = 0;
        $data['miniTypeName'] = '';
        if ($data['transType'] == 103101) {
            if ($data['srcTransType'] == 101201 || $data['srcTransType'] == 101211) {
                $data['miniType'] = 1;
                $data['miniTypeName'] = '销售收款单';
            } elseif ($data['srcTransType'] == 101101 || $data['srcTransType'] == 101102) {
                $data['miniType'] = 2;
                $data['miniTypeName'] = '采购退款单';
            } elseif ($data['srcTransType'] == 101111) {
                $data['miniType'] = 3;
                $data['miniTypeName'] = '特采退款单';
            } elseif ($data['srcTransType'] == 109101) {
                $data['miniType'] = 4;
                $data['miniTypeName'] = '快递退款单';
            }
        } elseif ($data['transType'] == 103102) {
            if ($data['srcTransType'] == 101201 || $data['srcTransType'] == 101211) {
                $data['miniType'] = 1;
                $data['miniTypeName'] = '销售退款单';
            } elseif ($data['srcTransType'] == 101101 || $data['srcTransType'] == 101102) {
                $data['miniType'] = 2;
                $data['miniTypeName'] = '采购付款单';
            } elseif ($data['srcTransType'] == 101111) {
                $data['miniType'] = 3;
                $data['miniTypeName'] = '特采付款单';
            } elseif ($data['srcTransType'] == 109101) {
                $data['miniType'] = 4;
                $data['miniTypeName'] = '快递付款单';
            }
        }
        if ($data['miniTypeName'] == '特采付款单') {
            // 针对性处理相关信息
            if ($data['srcId'] > 0) {
                $contract_pur = $this->db->table('bill_contract')->where(['id' => $data['srcId'], 'isDelete' => 0])->get()->getRowArray();
                // 应付金额要剔除设为不买的
                $data['amount'] = floatval($this->db->table('bill_contract_info')->selectSum('amount', 'amount')->where(['iid' => $contract_pur['id'], 'isPurchased <' => 2, 'isDelete' => 0])->get()->getRowArray()['amount'] ?? 0);
                if ($contract_pur['srcMoneyId2'] > 0) { // 已付金额要剔除已退款金额
                    $refunded = $this->db->table('bill_money_info')->selectSum('amount', 'amount')->where(['iid' => $contract_pur['srcMoneyId2'], 'isDelete' => 0])->get()->getRowArray()['amount'] ?? 0;
                    $data['cleared'] -= (float)$refunded;
                }
            }
        }
        $data['moneyStatus'] = ($data['amount'] > 0 && $data['cleared'] <= 0) ? 0 : (bccomp($data['cleared'], $data['amount'], 2) > -1 ? 2 : 1);
        $data['uId'] = $this->myerp['uId'];
        $data['uName'] = $this->myerp['uName'];
        $data['checkId'] = $this->myerp['uId'];
        $data['checkName'] = $this->myerp['uName'];
        $data['contactId'] = intval($data['contactId']);
        $data['createTime'] = $data['modifyTime'] = date('Y-m-d H:i:s');
        $data['files'] = strlen($data['files'] ?? '') < 10 ? '' : $data['files'];
        // 编辑的时候
        if ($data['id'] > 0) {
            if ($this->db->table('bill_money')->where(['id' => $data['id'], 'isDelete' => 0])->countAllResults() < 1) {
                return $this->response->setJSON(['status' => -1, 'msg' => '单据不存在、或者已删除！']);
            }
        }
        return $data;
    }
    // 其他收入、其他支出 一键退回
    public function moneyOnekeyBack()
    {
        $id = intval($this->request->getGetPost('id'));
        $backAmount = (float)$this->request->getGetPost('backAmount');
        if ($id > 0 && $backAmount > 0) {
            $money = $this->db->table('bill_money')->where('id', $id)->get()->getRowArray();
            $moneyInfo = $this->db->table('bill_money_info')->where('iid', $id)->get()->getResultArray();
            if ($money) {
                switch (intval($money['transType'])) {
                    case 103103:
                        if (!$this->myservice->verifyRight('INCOME_UPDATE')) {
                            log_message('error', $this->myerp['uName'] . ' 访问 Bill/MonIo/moneyOnekeyBack 其他收入 权限不足！');
                            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
                        }
                        break;
                    case 103104:
                        if (!$this->myservice->verifyRight('EXPEND_UPDATE')) {
                            log_message('error', $this->myerp['uName'] . ' 访问 Bill/MonIo/moneyOnekeyBack 其他支出 权限不足！');
                            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
                        }
                        break;
                    default:
                        break;
                }
                if (intval($money['isBack']) == 2) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '本单已一键退回过！']);
                }
                if (intval($money['isBack']) == 3) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '本单属于一键退回生成的对冲单据，禁止再次逆向退回！']);
                }
                if (intval($money['isDelete']) == 1) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '本单已被删除！']);
                }
                $data['id'] = -1;
                $data['billDate'] = date('Y-m-d');
                $data['billNo'] = $money['billType'] == 'EXP' ? str_no('SR') : str_no('ZC');
                $data['billType'] = $money['billType'] == 'EXP' ? 'INC' : 'EXP';
                $data['transType'] = $money['billType'] == 'EXP' ? 103103 : 103104;
                $data['miniType'] = $money['miniType'];
                $data['srcTransType'] = $money['transType'];
                $data['srcId'] = $money['id'];
                $data['srcNo'] = $money['billNo'];
                $data['amount'] = $backAmount;
                $data['cleared'] = $backAmount;
                $data['moneyStatus'] = 2;
                $data['isBack'] = 3;
                $data['uId'] = $this->myerp['uId'];
                $data['uName'] = $this->myerp['uName'];
                $data['checkId'] = $this->myerp['uId'];
                $data['checkName'] = $this->myerp['uName'];
                $data['contactId'] = $money['contactId'];
                $data['contactName'] = $money['contactName'];
                $data['files'] = '';
                $data['description'] = '源单单号：' . $money['billNo'];
                $data['createTime'] = $data['modifyTime'] = date('Y-m-d H:i:s');
                $data['entries'][0] = [
                    'amount' => $backAmount,
                    'typeId' => $money['billType'] == 'EXP' ? 328 : 423,
                    'accId' => $moneyInfo[0]['accId'],
                    'wayId' => $moneyInfo[0]['wayId'],
                    'settlementTime' => date('Y-m-d H:i:s'),
                    'description' => ''
                ];
                $info = array_intersect_key($data, array_flip([
                    'billDate',
                    'billNo',
                    'billType',
                    'transType',
                    'miniType',
                    'srcTransType',
                    'srcId',
                    'srcNo',
                    'amount',
                    'cleared',
                    'moneyStatus',
                    'isBack',
                    'uId',
                    'uName',
                    'checkId',
                    'checkName',
                    'contactId',
                    'contactName',
                    'files',
                    'description',
                    'createTime',
                    'modifyTime'
                ]));
                $this->db->transStart();
                $this->db->table('bill_money')->insert($info);
                $iid = $this->db->insertID();
                $this->money_info(intval($iid), $data);
                $this->db->table('bill_money')->where('id', $id)->update(['isBack' => 2, 'srcId' => $iid, 'srcNo' => $data['billNo']]);
                $this->myservice->log('点击了' . $this->myerp['transTypeName'][$money['transType']] . $money['billNo'] . '一键退回，自动新增了' . $this->myerp['transTypeName'][$data['transType']] . '进行对冲，单号：' . $data['billNo']);
                $this->db->transComplete();
                if ($this->db->transStatus() === false) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
                }
                return $this->response->setJSON(['status' => 200, 'msg' => '退回成功！', 'data' => ['id' => $id, 'isBack' => 2, 'srcId' => $iid, 'srcNo' => $data['billNo']]]);
            }
            return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
}
