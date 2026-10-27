<?php

namespace App\Controllers\Bill;
use App\Controllers\BaseController;
use CodeIgniter\Database\RawSql;

class InvPc extends BaseController
{
    // 列表
    public function list()
    {
        if (!$this->myservice->verifyRight('PC_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvPc/list 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $status1 = intval($this->request->getGetPost('status1'));
        $status2 = intval($this->request->getGetPost('status2'));
        $status3 = intval($this->request->getGetPost('status3'));
        $status4 = intval($this->request->getGetPost('status4'));
        $status5 = intval($this->request->getGetPost('status5'));
        $status6 = intval($this->request->getGetPost('status6'));
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');
        $minAmount = (float)$this->request->getGetPost('minAmount');
        $maxAmount = (float)$this->request->getGetPost('maxAmount');
        $skey = $this->request->getGetPost('skey');
        $builder = $this->db->table('bill_contract');
        $builder->where('billType', 'PUR')->where('isFill', 0);
        if ($status1 >= 0) {
            if ($status1 > 0) {
                $builder->where('checkId >', 0);
            } else {
                $builder->where('checkId', 0);
            }
        }
        if ($status2 >= 0) {
            $builder->where('moneyStatus1', $status2);
        }
        if ($status3 >= 0) {
            $builder->where('moneyStatus2', $status3);
        }
        if ($status3 == -2) {
            $srcIds = array_column($this->db->table('bill_money_info')->select('srcId')->where(['moneyType' => 9, 'transType' => 103101, 'miniType' => 3, 'isDelete' => 0])->groupBy('srcId')->get()->getResultArray(), 'srcId');
            if ($srcIds) {
                $builder->whereIn('id', $srcIds);
            } else {
                $builder->where('id', -1);
            }
        }
        if ($status4 >= 0) {
            if ($status4 == 0) {
                $builder->where('invoiceNum', 0);
            } else {
                $builder->where('invoiceNum >', 0);
            }
        }
        if ($status5 == 0) {
            $builder->where('isReq', 0);
        } elseif ($status5 == 1) {
            $builder->where('isReq', 2);
            if ($status6 == 0) {
                $builder->where('FIND_IN_SET(' . $this->db->escape($this->myerp['uName']) . ', quotedName) >', 0, false);
            }
        } else {
            $builder->whereIn('isReq', [0, 2]);
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
                    ->orLike('reqInfo', $this->db->escapeLikeString($kw))
                    ->orLike('contactName', $this->db->escapeLikeString($kw))
                    ->orLike('sName', $this->db->escapeLikeString($kw))
                    ->orLike('files', $this->db->escapeLikeString($kw))
                    ->orLike('goods', $this->db->escapeLikeString($kw))
                    ->orLike('description', $this->db->escapeLikeString($kw))
                ->groupEnd();
            }
        }
        $builder->where('isDelete', 0);
        $allowedSort = ['id','billDate','billNo','contactName','uName','description','checkId','moneyStatus1','moneyStatus2','invoiceNum','isReq'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $list = (clone $builder)->orderBy($orderField, $sord)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $total = (clone $builder)->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['billDate'] = $row['billDate'];
            $v[$arr]['billNo'] = $row['billNo'];
            $v[$arr]['transType'] = intval($row['transType']);
            $v[$arr]['isReq'] = intval($row['isReq']);
            $v[$arr]['totalQty'] = intval($row['totalQty']);
            $v[$arr]['contactId'] = intval($row['contactId']);
            $v[$arr]['contactName'] = $row['contactName'];
            $v[$arr]['sId'] = intval($row['sId']);
            $v[$arr]['sName'] = $row['sName'];
            $v[$arr]['endDate'] = $row['endDate'];
            $v[$arr]['amount'] = (float)$row['amount'];
            $v[$arr]['description'] = $row['description'];
            $v[$arr]['moneyStatus1'] = intval($row['moneyStatus1']);
            $v[$arr]['moneyStatus2'] = intval($row['moneyStatus2']);
            $v[$arr]['srcMoneyId1'] = intval($row['srcMoneyId1']);
            $v[$arr]['srcMoneyId2'] = intval($row['srcMoneyId2']);
            $v[$arr]['srcMoneyNo1'] = $row['srcMoneyNo1'];
            $v[$arr]['srcMoneyNo2'] = $row['srcMoneyNo2'];
            $v[$arr]['srcPurReqId'] = intval($row['srcPurReqId']);
            $v[$arr]['goodsStatus1'] = intval($row['goodsStatus1']);
            $v[$arr]['goodsStatus2'] = intval($row['goodsStatus2']);
            $v[$arr]['invoiceNum'] = intval($row['invoiceNum']);
            $v[$arr]['invoiceAmount'] = $row['invoiceAmount'];
            $v[$arr]['uId'] = $row['uId'];
            $v[$arr]['uName'] = $row['uName'];
            $v[$arr]['checkId'] = intval($row['checkId']);
            $v[$arr]['createTime'] = $row['createTime'];
            $v[$arr]['goods'] = (array)json_decode($row['goods'], true);
            $v[$arr]['isDraft'] = intval($row['isDraft']);
        }
        // 款项汇总
        $totalNotClearedAmount = (clone $builder)->where('moneyStatus1', 0)->selectSum('amount', 'totalNotClearedAmount')->get()->getRowArray()['totalNotClearedAmount'] ?? 0;
        $partIdsArr = [];
        $partIds = (clone $builder)->select('srcMoneyId1')->where('moneyStatus1', 1)->get()->getResultArray();
        if (count($partIds) > 0) {
            foreach ($partIds as $arr2 => $row2) {
                array_push($partIdsArr, $row2['srcMoneyId1']);
                if (count($partIdsArr) == 1000) { // 分片操作，每1000处理一次
                    $totalNotClearedAmount_part = $this->db->table('bill_money')->selectSum('amount', 'amount')->selectSum('cleared', 'cleared')->whereIn('id', $partIdsArr)->get()->getRowArray() ?? ['amount' => 0, 'cleared' => 0];
                    $totalNotClearedAmount = (float)$totalNotClearedAmount + (float)$totalNotClearedAmount_part['amount'] - (float)$totalNotClearedAmount_part['cleared'];
                    $partIdsArr = [];
                }
            }
            if (count($partIdsArr) > 0) { // 处理分片操作剩余部分
                $totalNotClearedAmount_part = $this->db->table('bill_money')->selectSum('amount', 'amount')->selectSum('cleared', 'cleared')->whereIn('id', $partIdsArr)->get()->getRowArray() ?? ['amount' => 0, 'cleared' => 0];
                $totalNotClearedAmount = (float)$totalNotClearedAmount + (float)$totalNotClearedAmount_part['amount'] - (float)$totalNotClearedAmount_part['cleared'];
                $partIdsArr = [];
            }
        }
        $json['totalNotClearedAmount'] = $totalNotClearedAmount;
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['page'] = $page;
        $json['data']['records'] = $total;
        $json['data']['total'] = ceil($json['data']['records'] / $rows);
        $json['data']['rows'] = $v;
        return $this->response->setJSON($json);
    }
    // 查询
    public function query()
    {
        if (!$this->myservice->verifyRight('PC_QUERY') && !$this->myservice->verifyRight('SC_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvPc/query 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $infoIds = $this->request->getGetPost('infoIds'); // 由特殊销售单勾选产品发起特采申请时的相应产品ids
        $reqType = $this->request->getGetPost('reqType'); // 由特殊销售单发起特采的类型，1：全部无编码产品，2：勾选的产品（允许有编码产品）
        $data = $this->db->table('bill_contract')->where('id', $id)->where('isDelete', 0)->get()->getRowArray();
        if ($data) {
            $info['id'] = $id;
            $info['transType'] = intval($data['transType']);
            $info['billDate'] = $data['billDate'];
            $info['billNo'] = $data['billNo'];
            $info['isReq'] = intval($data['isReq']);
            $info['reqInfo'] = $data['reqInfo'];
            $info['contactId'] = intval($data['contactId']);
            $info['contactName'] = $data['contactName'];
            $info['sId'] = intval($data['sId']);
            $info['sName'] = $data['sName'];
            $info['endDate'] = $data['endDate'];
            $info['amount'] = (float)$data['amount'];
            $info['files'] = $data['files'];
            $info['description'] = $data['description'];
            $info['files'] = $data['files'] == '' ? [] : json_decode($data['files'], true);
            $info['uId'] = intval($data['uId']);
            $info['uName'] = $data['uName'];
            $info['checkId'] = intval($data['checkId']);
            $info['checkName'] = $data['checkName'];
            $info['reqId'] = intval($data['reqId']);
            $info['reqName'] = $data['reqName'];
            $info['createTime'] = $data['createTime'];
            $info['modifyTime'] = $data['modifyTime'];
            $info['moneyStatus1'] = intval($data['moneyStatus1']);
            $info['moneyStatus2'] = intval($data['moneyStatus2']);
            $info['goodsStatus1'] = intval($data['goodsStatus1']);
            $info['goodsStatus2'] = intval($data['goodsStatus2']);
            $info['srcMoneyId1'] = intval($data['srcMoneyId1']);
            $info['srcMoneyNo1'] = $data['srcMoneyNo1'];
            $info['srcMoneyId2'] = intval($data['srcMoneyId2']);
            $info['srcMoneyNo2'] = $data['srcMoneyNo2'];
            $info['srcPurReqId'] = intval($data['srcPurReqId']);
            $info['isDraft'] = intval($data['isDraft']);
            $builder = $this->db->table('bill_contract_info')->where(['iid' => $id, 'isDelete' => 0]);
            if ($infoIds) {
                $builder->whereIn('id', explode(',', $infoIds));
            }
            $list = (clone $builder)->orderBy('CAST(zid AS UNSIGNED) asc, id asc')->get()->getResultArray();
            $total = $builder->countAllResults();
            $totalPaidAmount = array_column($this->db->table('bill_money_info')->select('zid, SUM(amount) AS amount')->where('iid', $data['srcMoneyId1'])->where('isDelete', 0)->groupBy('zid')->get()->getResultArray(), 'amount', 'zid');
            $refundingAmount = array_column($this->db->table('bill_money_info')->select('zid, SUM(amount) AS amount')->where('iid', $data['srcMoneyId2'])->where('moneyType', 9)->where('isDelete', 0)->groupBy('zid')->get()->getResultArray(), 'amount', 'zid');
            $refundedAmount = array_column($this->db->table('bill_money_info')->select('zid, SUM(amount) AS amount')->where('iid', $data['srcMoneyId2'])->whereIn('moneyType', [0,1])->where('isDelete', 0)->groupBy('zid')->get()->getResultArray(), 'amount', 'zid');
            $refundedCashbackAmount = array_column($this->db->table('bill_money_info')->select('zid, SUM(amount) AS amount')->where('iid', $data['srcMoneyId2'])->where('moneyType', 2)->where('isDelete', 0)->groupBy('zid')->get()->getResultArray(), 'amount', 'zid');
            $v = [];
            foreach ($list as $arr => $row) {
                // 如果是发起特采申请（全部无编码产品），则有编码的产品剔除。
                if ($reqType == 1 && $row['no'] != '') {
                    continue;
                }
                $v[$arr]['id'] = intval($row['id']);
                $v[$arr]['iid'] = intval($row['iid']);
                $v[$arr]['transType'] = intval($row['transType']);
                $v[$arr]['zid'] = $row['zid'];
                $v[$arr]['isReq'] = intval($row['isReq']);
                $v[$arr]['no'] = $row['no'];
                $v[$arr]['name'] = $row['name'];
                $v[$arr]['spec'] = $row['spec'];
                $v[$arr]['sku'] = $row['sku'];
                $v[$arr]['brand'] = $row['brand'];
                $v[$arr]['unit'] = $row['unit'];
                $v[$arr]['qty'] = (float)$row['qty'];
                $v[$arr]['hasRecQty'] = intval($row['hasRecQty']);
                $v[$arr]['limitPrice'] = (float)$row['limitPrice'];
                $v[$arr]['limitAmount'] = (float)$row['limitPrice'] * (float)$row['qty'];
                $v[$arr]['price'] = (float)$row['price'];
                $v[$arr]['amount'] = (float)$row['amount'];
                $v[$arr]['buyUrl'] = $row['buyUrl'];
                $v[$arr]['myUrl'] = $row['myUrl'];
                $v[$arr]['description1'] = $row['description1'];
                $v[$arr]['description2'] = $row['description2'];
                $v[$arr]['isPurchased'] = intval($row['isPurchased']);
                $v[$arr]['noPurchaseReason'] = $row['noPurchaseReason'];
                $v[$arr]['isRecInvoice'] = intval($row['isRecInvoice']);
                $v[$arr]['isIgnoreInvoice'] = intval($row['isIgnoreInvoice']);
                $v[$arr]['ignoreInvoiceReason'] = $row['ignoreInvoiceReason'];
                $v[$arr]['disagreeIgnoreReason'] = $row['disagreeIgnoreReason'];
                $v[$arr]['uId'] = intval($row['uId']);
                $v[$arr]['uName'] = $row['uName'];
                $v[$arr]['isHasExpress'] = intval($row['isHasExpress']);
                $v[$arr]['isHasInvoice'] = intval($row['isHasInvoice']);
                $v[$arr]['purFeedback'] = $row['purFeedback'];
                $v[$arr]['purSuggestId'] = intval($row['purSuggestId']);
                $v[$arr]['quotePrice'] = (float)$row['quotePrice'];
                $v[$arr]['paidAmount'] = (float)$row['paidAmount'];
                $v[$arr]['totalPaidAmount'] = $totalPaidAmount[$row['zid']] ?? '0.00';
                $v[$arr]['refundingAmount'] = $refundingAmount[$row['zid']] ?? '0.00';
                $v[$arr]['refundedAmount'] = $refundedAmount[$row['zid']] ?? '0.00';
                $v[$arr]['refundedCashbackAmount'] = $refundedCashbackAmount[$row['zid']] ?? '0.00';
            }
            $info['entries'] = $v;
            $json['status'] = 200;
            $json['msg'] = 'success';
            $json['data'] = $info;
            $json['totalsize'] = $total;
            return $this->response->setJSON($json);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 查询已付款金额
    public function getPaidAmount()
    {
        $id = intval($this->request->getGetPost('id'));
        if ($id > 0) {
            $paidAmount = $this->db->table('bill_contract_info')->select('paidAmount')->where('id', $id)->get()->getRowArray()['paidAmount'] ?? 0;
            $json['status'] = 200;
            $json['msg'] = 'success';
            $json['data']['paidAmount'] = (float)$paidAmount;
            return $this->response->setJSON($json);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 查询isPurchased值
    public function getIsPurchased()
    {
        $id = intval($this->request->getGetPost('id'));
        if ($id > 0) {
            $isPurchased = $this->db->table('bill_contract_info')->select('isPurchased')->where('id', $id)->get()->getRowArray()['isPurchased'] ?? 0;
            $json['status'] = 200;
            $json['msg'] = 'success';
            $json['data']['isPurchased'] = (int)$isPurchased;
            return $this->response->setJSON($json);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 单独查询某特采产品已付/退款记录
    public function singleSearchMoneyInfo()
    {
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 10000);
        $type = intval($this->request->getGetPost('type'));
        $srcId = intval($this->request->getGetPost('srcId'));
        $zid = $this->request->getGetPost('zid');
        if ($srcId > 0 && strlen($zid) > 0) {
            $builder = $this->db->table('bill_money_info AS a')
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
                ->where('a.srcId', $srcId)
                ->where('a.zid', $zid)
                ->where('a.isDelete', 0)
                ->orderBy('a.id', 'ASC');
            if ($type == 1) {
                $builder->where('a.transType', 103102);
            } else {
                $builder->where('a.transType', 103101);
            }
            $money_info = $builder->get()->getResultArray();
            $json['status'] = 200;
            $json['msg'] = 'success';
            $json['data']['page'] = $page;
            $json['data']['records'] = count($money_info);
            $json['data']['total'] = ceil($json['data']['records'] / $rows);
            $json['data']['rows'] = $money_info;
            return $this->response->setJSON($json);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 新增
    public function add()
    {
        $data = $this->request->getPost('postData');
        if (strlen($data) > 0) {
            $data = $this->validform((array)json_decode($data, TRUE));
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            if (($data['isReq'] == 1 && !$this->myservice->verifyRight('SC_ADD')) || ($data['isReq'] != 1 && !$this->myservice->verifyRight('PC_ADD'))) {
                log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvPc/add 权限不足！');
                return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
            }
            // 取消自动审核，由采购经理手工审核
            $data['checkId'] = 0;
            $data['checkName'] = '';
            $info = array_intersect_key($data, array_flip(['srcPurReqId','billDate','billNo','billType','transType','totalQty','contactId','contactName','sId','sName','endDate','amount','files','goods','description','uId','uName','checkId','checkName','createTime','modifyTime','isReq','reqNo','isDraft','isFill']));
            $this->db->transStart();
            $this->db->table('bill_contract')->insert($info);
            $iid = $this->db->insertID();
            if ($data['srcPurReqId'] > 0) { //如果是由采购申请转化
                $purReqData = $this->db->table('pur_req')->getWhere(['id' => $data['srcPurReqId']])->getRowArray();
                if ($purReqData) {
                    if ($purReqData['srcPcId'] == '') {
                        $this->db->table('pur_req')->where('id', $data['srcPurReqId'])->update(['purchaseId' => $data['uId'], 'purchaseName' => $data['uName'], 'purchaseTime' => $data['createTime'], 'srcPcId' => $iid, 'srcPcNo' => $data['billNo']]);
                    } else {
                        $srcPcId = explode(',', $purReqData['srcPcId']);
                        array_push($srcPcId, $iid);
                        $srcPcId = array_unique($srcPcId);
                        $srcPcId = implode(',', $srcPcId);

                        $srcPcNo = explode(',', $purReqData['srcPcNo']);
                        array_push($srcPcNo, $data['billNo']);
                        $srcPcNo = array_unique($srcPcNo);
                        $srcPcNo = implode(',', $srcPcNo);

                        $this->db->table('pur_req')->where('id', $data['srcPurReqId'])->update(['purchaseId' => $data['uId'], 'purchaseName' => $data['uName'], 'purchaseTime' => $data['createTime'], 'srcPcId' => $srcPcId, 'srcPcNo' => $srcPcNo]);
                    }
                }
            }
            if ($data['isReq'] == 1) {
                if ($data['reqNo']) {
                    $reqBill = $this->db->table('bill_contract')->select('billNo, contactName, sName, saler, description, uName')->where(['billNo' => $data['reqNo']])->get()->getRowArray();
                    $reqInfo = '原销售单信息（单号：' . $reqBill['billNo'] . '，买方：' . $reqBill['contactName'] . '，卖方：' . $reqBill['sName'] . '，销售人：' . $reqBill['saler'] . '，制单人：' . $reqBill['uName'] . ')';
                    $this->db->table('bill_contract')->where('id', $iid)->update(['reqInfo' => $reqInfo, 'reqId' => $data['uId'], 'reqName' => $data['uName']]);
                    $oldReqNo = $this->db->table('bill_contract')->select('reqNo')->where(['billNo' => $data['reqNo']])->get()->getRowArray()['reqNo'] ?? '';
                    $this->db->table('bill_contract')->where(['billNo' => $data['reqNo']])->update(['isReq' => 3, 'reqNo' => $oldReqNo ? ($oldReqNo . ',' . $data['billNo']) : $data['billNo']]);
                } else {
                    $this->db->table('bill_contract')->where('id', $iid)->update(['reqId' => $data['uId'], 'reqName' => $data['uName']]);
                }
            }
            $this->contract_info(intval($iid), $data);
            $data['id'] = $iid;
            // 更新关联的特殊销售单的产品条目的特采申请状态
            if ($data['isReq'] == 1 && $data['reqNo']) {
                $reqBills = array_column($this->db->table('bill_contract')->select('id')->where(['reqNo' => $data['reqNo'], 'billType' => 'PUR', 'isDelete' => 0])->get()->getResultArray(), 'id');
                if (count($reqBills) > 0) {
                    $reqGoodsZidsArr = array_column($this->db->table('bill_contract_info')->select('zid')->whereIn('iid', $reqBills)->where(['isDelete' => 0])->get()->getResultArray(), 'zid');
                    if (count($reqGoodsZidsArr) > 0) {
                        $reqGoodsZids = array_unique($reqGoodsZidsArr); // zid数组去重
                        $this->db->table('bill_contract_info')->where('billNo', $data['reqNo'])->where('isDelete', 0)->whereNotIn('zid', $reqGoodsZids)->update(['isAlreadyReqPur' => 0]);
                        $this->db->table('bill_contract_info')->where('billNo', $data['reqNo'])->where('isDelete', 0)->whereIn('zid', $reqGoodsZids)->update(['isAlreadyReqPur' => 1]);
                    } else {
                        $this->db->table('bill_contract_info')->where('billNo', $data['reqNo'])->where('isDelete', 0)->update(['isAlreadyReqPur' => 0]);
                    }
                }
            }
            $this->myservice->log('新增了' . ($data['isReq'] == 1 ? '特采申请单' : ($data['isFill'] == 1 ? '特采补买单' : '特殊采购单')) . ($data['isDraft'] == 1 ? '草稿' : '') . '，单号：' . $data['billNo']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '新增成功！', 'data' => $data]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    // 编辑
    public function update()
    {
        $data = $this->request->getPost('postData'); // FALSE 禁用XSS过滤
        if (strlen($data) > 0) {
            $data = $this->validform((array)json_decode($data, true));
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            if (($data['isReq'] == 1 && !$this->myservice->verifyRight('SC_UPDATE')) || ($data['isReq'] != 1 && !$this->myservice->verifyRight('PC_UPDATE'))) {
                log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvPc/update 权限不足！');
                return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
            }
            $oldData = $this->db->table('bill_contract')->where('id', $data['id'])->get()->getRowArray();
            $this->db->transStart();
            if ($data['isReq'] == 2) {
                // 特采转正单，只更部分info信息
                // 先处理info信息，提前判断异常
                $errZids = $this->contract_info_req_update(intval($data['id']), $data, $oldData);
                // 无异常再处理后续
                $goods = $this->db->table('bill_contract_info')->select('zid,name,spec,sku,brand,unit,qty,price,amount')->where(['iid' => $data['id'], 'isDelete' => 0])->get()->getResultArray();
                $data['goods'] = json_encode($goods, 320);
                $data['totalQty'] = $this->db->table('bill_contract_info')->selectSum('qty')->where(['iid' => $data['id'], 'isDelete' => 0])->get()->getRowArray()['qty'] ?? 0;
                $data['amount'] = $this->db->table('bill_contract_info')->selectSum('amount')->where(['iid' => $data['id'], 'isPurchased <' => 2, 'isDelete' => 0])->get()->getRowArray()['amount'] ?? 0;
                $info = array_intersect_key($data, array_flip(['contactId', 'contactName', 'sId', 'sName', 'endDate', 'goods', 'totalQty', 'amount', 'files', 'description', 'modifyTime']));
                $this->db->table('bill_contract')->where('id', $data['id'])->update($info);
            } else {
                // 先处理info信息，方便提前判断异常中断后续操作
                if ($data['goods'] != $oldData['goods'] || $data['isNewImportExcel']) {
                    // 产品信息改变了、或重新导入了表格，删除info信息重新插入
                    $this->contract_info(intval($data['id']), $data);
                } else {
                    // 否则只更新info信息
                    $this->contract_info_update(intval($data['id']), $data);
                }
                //无异常再处理后续
                $info = array_intersect_key($data, array_flip([
                    'billDate',
                    'billNo',
                    'billType',
                    'transType',
                    'totalQty',
                    'contactId',
                    'contactName',
                    'sId',
                    'sName',
                    'endDate',
                    'amount',
                    'files',
                    'goods',
                    'description',
                    'modifyTime',
                    'isDraft'
                ]));
                $this->db->table('bill_contract')->where('id', $data['id'])->update($info);
                if ($data['isReq'] == 1) {
                    $this->db->table('bill_contract')->set('onModify', 0)->set('modifyTimes', 'modifyTimes + 1', false)->where('id', $data['id'])->update();
                }
            }
            // 更新关联的特殊销售单的产品条目的特采申请状态
            if (($data['isReq'] == 1 || $data['isReq'] == 2) && $oldData['reqNo']) {
                $reqBills = array_column($this->db->table('bill_contract')->select('id')->where(['reqNo' => $oldData['reqNo'], 'billType' => 'PUR', 'isDelete' => 0])->get()->getResultArray(), 'id');
                if (count($reqBills) > 0) {
                    $reqGoodsZidsArr = array_column($this->db->table('bill_contract_info')->select('zid')->where('iid IN(' . implode(',', $reqBills) . ') AND isDelete = 0')->get()->getResultArray(), 'zid');
                    if (count($reqGoodsZidsArr) > 0) {
                        $reqGoodsZids = array_unique($reqGoodsZidsArr); // zid数组去重、打散成字符串，并加双引号
                        $this->db->table('bill_contract_info')->where('billNo', $oldData['reqNo'])->whereNotIn('zid', $reqGoodsZids)->where('isDelete', 0)->update(['isAlreadyReqPur' => 0]);
                        $this->db->table('bill_contract_info')->where('billNo', $oldData['reqNo'])->whereIn('zid', $reqGoodsZids)->where('isDelete', 0)->update(['isAlreadyReqPur' => 1]);
                    } else {
                        $this->db->table('bill_contract_info')->where('billNo', $oldData['reqNo'])->where('isDelete', 0)->update(['isAlreadyReqPur' => 0]);
                    }
                }
            }
            $this->myservice->log('编辑了' . ($data['isReq'] == 1 ? '特采申请单' : ($oldData['isFill'] == 1 ? '特采补买单' : '特殊采购单')) . ($data['isDraft'] == 1 ? '草稿' : '') . '，' . (($oldData['isDraft'] == 1 && $data['isDraft'] == 0) ? '并将草稿转为了正式，' : '') . '单号：' . $data['billNo']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '编辑成功！', 'data' => ['errZids' => ((isset($errZids) && count($errZids) > 0) ? implode(',', $errZids) : '')]]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    // 删除
    public function delete()
    {
        if (!$this->myservice->verifyRight('PC_DELETE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvPc/delete 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('bill_contract')->where('id', $id)->get()->getRowArray();
        if ($data) {
            if (!$this->myservice->verifyRight('SYS_ADMIN') && $data['uId'] != $this->myerp['uId']) {
                return $this->response->setJSON(['status' => -1, 'msg' => '不能删除别人的' . ($data['isFill'] == 1 ? '特采补买单' : '特殊采购单') . '！仅本人或管理员可删除！']);
            }
            if ($data['checkId'] > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '已审核的单据不可随意删除！']);
            }
            if ($data['srcMoneyId1'] > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '已发生过付款，请先删除付款单！']);
            }
            if ($data['srcMoneyId2'] > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '已发生过退款，请先删除退款单！']);
            }
            $this->db->transStart();
            $this->db->table('bill_contract')->where('id', $id)->update(['isDelete' => 1]);
            $this->db->table('bill_contract_info')->where('iid', $id)->update(['isDelete' => 1]);
            if ($data['srcPurReqId'] > 0) {
                $purReqData = $this->db->table('pur_req')->where('id', $data['srcPurReqId'])->get()->getRowArray();
                if ($purReqData) {
                    if ($purReqData['srcPcId'] == '' || $purReqData['srcPcId'] == $id) {
                        $this->db->table('pur_req')->where('id', $data['srcPurReqId'])->update(['purchaseId' => 0, 'purchaseName' => '', 'purchaseTime' => '', 'srcPcId' => '', 'srcPcNo' => '']);
                    } else {
                        $srcPcId = explode(',', $purReqData['srcPcId']);
                        $srcPcId = array_diff($srcPcId, [$id]);
                        $srcPcId = implode(',', $srcPcId);
                        $srcPcNo = explode(',', $purReqData['srcPcNo']);
                        $srcPcNo = array_diff($srcPcNo, [$data['billNo']]);
                        $srcPcNo = implode(',', $srcPcNo);
                        $this->db->table('pur_req')->where('id', $data['srcPurReqId'])->update(['srcPcId' => $srcPcId, 'srcPcNo' => $srcPcNo]);
                    }
                }
            }
            // 更新关联的特殊销售单的产品条目的特采申请状态
            if (($data['isReq'] == 1 || $data['isReq'] == 2) && $data['reqNo']) {
                $reqBills = array_column($this->db->table('bill_contract')->select('id')->where(['reqNo' => $data['reqNo'], 'billType' => 'PUR', 'isDelete' => 0])->get()->getResultArray(), 'id');
                if (count($reqBills) > 0) {
                    $reqGoodsZidsArr = array_column($this->db->table('bill_contract_info')->select('zid')->whereIn('iid', $reqBills)->where(['isDelete' => 0])->get()->getResultArray(), 'zid');
                    if (count($reqGoodsZidsArr) > 0) {
                        $reqGoodsZids = array_unique($reqGoodsZidsArr); // zid数组去重
                        $this->db->table('bill_contract_info')->where('billNo', $data['reqNo'])->where('isDelete', 0)->whereNotIn('zid', $reqGoodsZids)->update(['isAlreadyReqPur' => 0]);
                        $this->db->table('bill_contract_info')->where('billNo', $data['reqNo'])->where('isDelete', 0)->whereIn('zid', $reqGoodsZids)->update(['isAlreadyReqPur' => 1]);
                    } else {
                        $this->db->table('bill_contract_info')->where('billNo', $data['reqNo'])->where('isDelete', 0)->update(['isAlreadyReqPur' => 0]);
                    }
                }
            }
            $this->myservice->log('删除了' . ($data['isFill'] == 1 ? '特采补买单' : '特殊采购单') . ($data['isDraft'] == 1 ? '草稿' : '') . '，单号：' . $data['billNo']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '删除成功！', 'data' => ['id' => $data['id']]]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 审核
    public function check()
    {
        if (!$this->myservice->verifyRight('PC_CHECK')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvPc/check 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $checkId = intval($this->request->getGetPost('checkId'));
        $checkName = $this->request->getGetPost('checkName');
        $billNo = $this->request->getGetPost('billNo');
        $transType = intval($this->request->getGetPost('transType'));
        $data = $this->db->table('bill_contract')->where('id', $id)->get()->getRowArray();
        if ($data) {
            $this->db->transStart();
            $this->db->table('bill_contract')->where('id', $id)->update(['checkId ' => $checkId, 'checkName' => $checkName]);
            $this->myservice->log(($checkId > 0 ? '审核了' : '反审核了') . ($data['isFill'] == 1 ? '特采补买单' : '特殊采购单') . '，单号：' . $billNo);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '操作成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 更新收货状态
    public function upGoodsStatus1()
    {
        if (!$this->myservice->verifyRight('PC_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvPc/upGoodsStatus1 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $goodsStatus1 = intval($this->request->getGetPost('goodsStatus1'));
        $billNo = $this->request->getGetPost('billNo');
        $transType = intval($this->request->getGetPost('transType'));
        $data = $this->db->table('bill_contract')->where('id', $id)->get()->getRowArray();
        if ($data) {
            if ($goodsStatus1 > 0 && $data['deliverDoneTime'] == '0000-00-00 00:00:00') {
                $newDate = ['goodsStatus1 ' => $goodsStatus1, 'deliverDoneTime' => date('Y-m-d H:i:s')];
            } else {
                $newDate = ['goodsStatus1 ' => $goodsStatus1];
            }
            $this->db->transStart();
            $this->db->table('bill_contract')->where('id', $id)->update($newDate);
            $this->myservice->log('标记了' . ($data['isFill'] == 1 ? '特采补买单' : '特殊采购单') . ' ' . $billNo . ' 的收货状态为 ' . ($goodsStatus1 == 0 ? '未收完' : '已收完'));
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '操作成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 更新退货状态
    public function upGoodsStatus2()
    {
        if (!$this->myservice->verifyRight('PC_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvPc/upGoodsStatus2 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $goodsStatus2 = intval($this->request->getGetPost('goodsStatus2'));
        $billNo = $this->request->getGetPost('billNo');
        $transType = intval($this->request->getGetPost('transType'));
        $data = $this->db->table('bill_contract')->where('id', $id)->get()->getRowArray();
        if ($data) {
            $newDate = ['goodsStatus2 ' => $goodsStatus2];
            $this->db->transStart();
            $this->db->table('bill_contract')->where('id', $id)->update($newDate);
            $this->myservice->log('标记了' . ($data['isFill'] == 1 ? '特采补买单' : '特殊采购单') . ' ' . $billNo . ' 的退货状态为 ' . ($goodsStatus2 == 0 ? '无退货' : '有退货'));
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
        $id = intval($this->request->getGetPost('id'));
        $newDes = $this->request->getGetPost('newDes');
        $data = $this->db->table('bill_contract')->where('id', $id)->get()->getRowArray();
        if ($data) {
            if (($data['isReq'] == 1 && !$this->myservice->verifyRight('SC_UPDATE')) || ($data['isReq'] != 1 && !$this->myservice->verifyRight('PC_UPDATE'))) {
                log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvPc/updatedes 权限不足！');
                return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
            }
            $v['description'] = $newDes;
            $v['modifyTime'] = date("Y-m-d H:i:s");
            $this->db->transStart();
            $this->db->table('bill_contract')->where('id', $id)->update($v);
            $this->myservice->log('单独更新了' . ($data['isFill'] == 1 ? '特采补买单' : '特殊采购单') . ' ' . $data['billNo'] . ' 的备注');
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '操作成功！', 'data' => ['id' => $id]]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 更新采买状态[不买、要买]
    public function updateIsPurchased()
    {
        if (!$this->myservice->verifyRight('PC_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvPc/updateIsPurchased 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $isPurchased = intval($this->request->getGetPost('isPurchased'));
        $noPurchaseReason = $this->request->getGetPost('noPurchaseReason') ?? '';
        $contract_info_pur = $this->db->table('bill_contract_info')->where('id', $id)->get()->getRowArray();
        if ($contract_info_pur) {
            $contract_pur = $this->db->table('bill_contract')->where('id', $contract_info_pur['iid'])->get()->getRowArray();
            $contract_info_sale = $this->db->table('bill_contract_info')->where(['billNo' => $contract_pur['reqNo'], 'zid' => $contract_info_pur['zid'], 'isDelete' => 0])->get()->getRowArray();
            if ((float)$contract_info_pur['paidAmount'] > 0 && (float)$contract_info_pur['paidAmount'] >= (float)$contract_info_pur['amount']) {
                return $this->response->setJSON(['status' => -1, 'msg' => '已付清全款产品无法改变采买状态！']);
            } else {
                $this->db->transStart();
                // 如果原来isPurchased==1，本次是0，还是保持1
                $this->db->table('bill_contract_info')->where('id', $id)->update(['isPurchased' => ($isPurchased < 2 && $contract_info_pur['isPurchased'] == 1) ? 1 : $isPurchased, 'noPurchaseReason' => $noPurchaseReason]);
                $contract_info_sale && $this->db->table('bill_contract_info')->where('id', $contract_info_sale['id'])->update(['isPurchased' => ($isPurchased < 2 && $contract_info_pur['isPurchased'] == 1) ? 1 : $isPurchased, 'noPurchaseReason' => $noPurchaseReason]);
                // 针对性处理付款相关信息
                if ($contract_pur['srcMoneyId1'] > 0) {
                    $amount = floatval($this->db->table('bill_contract_info')->selectSum('amount')->where('iid', $contract_pur['id'])->where('isPurchased <', 2)->where('isDelete', 0)->get()->getRowArray()['amount'] ?? 0);
                    $cleared = floatval($this->db->table('bill_money_info')->selectSum('amount')->where('iid', $contract_pur['srcMoneyId1'])->where('isDelete', 0)->get()->getRowArray()['amount'] ?? 0);
                    if ($contract_pur['srcMoneyId2'] > 0) {
                        $refunded = floatval($this->db->table('bill_money_info')->selectSum('amount')->where('iid', $contract_pur['srcMoneyId2'])->where('isDelete', 0)->get()->getRowArray()['amount'] ?? 0);
                        $cleared -= $refunded;
                    }
                    $moneyStatus = ($amount > 0 && $cleared <= 0) ? 0 : (bccomp($cleared, $amount, 2) > -1 ? 2 : 1);
                    $this->db->table('bill_contract')->where('id', $contract_pur['id'])->update(['moneyStatus1' => $moneyStatus]);
                    $this->db->table('bill_money')->where('id', $contract_pur['srcMoneyId1'])->update(['amount' => $amount, 'cleared' => $cleared, 'moneyStatus' => $moneyStatus]);
                }
                $this->myservice->log('设置了特殊采购单 ' . $contract_info_pur['billNo'] . ' 内自编序号为 ' . $contract_info_pur['zid'] . ' 的产品的采买状态为 ' . ($isPurchased == 0 ? ($contract_info_pur['paidAmount'] > 0 ? '继续买' : '要买') : ($contract_info_pur['paidAmount'] > 0 ? '不再买' : '不买')));
                $this->db->transComplete();
                if ($this->db->transStatus() === false) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
                }
                return $this->response->setJSON(['status' => 200, 'msg' => '操作成功！', 'data' => ['id' => $id, 'isPurchased' => $isPurchased, 'noPurchaseReason' => $noPurchaseReason, 'amount' => (float)$contract_info_pur['amount'], 'paidAmount' => (float)$contract_info_pur['paidAmount'], 'uId' => intval($contract_info_pur['uId'])]]);
            }
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 更新发票收取状态
    public function updateIsRecInvoice()
    {
        if (!$this->myservice->verifyRight('PC_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvPc/updateIsRecInvoice 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $isRecInvoice = intval($this->request->getGetPost('isRecInvoice'));
        $contract_info_pur = $this->db->table('bill_contract_info')->where('id', $id)->get()->getRowArray();
        if ($contract_info_pur) {
            $this->db->transStart();
            $this->db->table('bill_contract_info')->where('id', $id)->update(['isRecInvoice' => $isRecInvoice]);
            $this->myservice->log('设置了特殊采购单 ' . $contract_info_pur['billNo'] . ' 内自编序号为 ' . $contract_info_pur['zid'] . ' 的产品的发票状态为 ' . ($isRecInvoice == 0 ? '未收' : '已收'));
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '操作成功！', 'data' => $isRecInvoice]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // info数据 产品有变化 全部删除 重新插入
    private function contract_info(int $iid, array $data)
    {
        foreach ($data['entries'] as $arr => $row) {
            $v[$arr]['iid'] = intval($iid);
            $v[$arr]['zid'] = trim($row['zid']);
            $v[$arr]['billDate'] = $data['billDate'];
            $v[$arr]['billNo'] = $data['billNo'];
            $v[$arr]['endDate'] = $data['endDate'];
            $v[$arr]['billType'] = $data['billType'];
            $v[$arr]['transType'] = intval($data['transType']);
            $v[$arr]['isReq'] = intval($data['isReq']);
            $v[$arr]['uId'] = $row['uId'] ?? $data['uId'];
            $v[$arr]['uName'] = $row['uName'] ?? $data['uName'];
            $v[$arr]['contactId'] = $data['contactId'];
            $v[$arr]['storageId'] = $row['storageId'];
            $v[$arr]['goodsId'] = $row['goodsId'];
            $v[$arr]['no'] = $row['no'];
            $v[$arr]['name'] = $row['name'];
            $v[$arr]['spec'] = $row['spec'];
            $v[$arr]['sku'] = $row['sku'];
            $v[$arr]['brand'] = $row['brand'];
            $v[$arr]['unit'] = $row['unit'];
            $v[$arr]['qty'] = abs((float)$row['qty']);
            $v[$arr]['hasRecQty'] = intval($row['hasRecQty']);
            $v[$arr]['limitPrice'] = abs((float)$row['limitPrice']);
            $v[$arr]['quotePrice'] = abs((float)($row['quotePrice'] ?? 0));
            $v[$arr]['price'] = abs((float)$row['price']);
            $v[$arr]['amount'] = abs((float)$row['amount']);
            $v[$arr]['paidAmount'] = $row['paidAmount'] ?? 0;
            $v[$arr]['paidTime'] = $row['paidTime'] ?? '0000-00-00 00:00:00';
            $v[$arr]['buyUrl'] = $row['buyUrl'];
            $v[$arr]['myUrl'] = $row['myUrl'];
            $v[$arr]['description1'] = $row['description1'];
            $v[$arr]['description2'] = $row['description2'];
            $v[$arr]['isHasExpress'] = $row['isHasExpress'] ?? 0;
            $v[$arr]['isHasInvoice'] = $row['isHasInvoice'] ?? 0;
            $v[$arr]['isPurchased'] = $row['isPurchased'] ?? 0;
            $v[$arr]['noPurchaseReason'] = $row['noPurchaseReason'] ?? '';
            $v[$arr]['isRecInvoice'] = $row['isRecInvoice'] ?? 0;
            $v[$arr]['purSuggestId'] = $row['purSuggestId'] ?? 0;
            $v[$arr]['purId'] = $row['purId'] ?? 0;
            $v[$arr]['purName'] = $row['purName'] ?? '';
            $v[$arr]['purFeedback'] = $row['purFeedback'] ?? '';
            $v[$arr]['purPriceAnormalStatus'] = $row['purPriceAnormalStatus'] ?? 0;
            $v[$arr]['isIgnoreInvoice'] = $row['isIgnoreInvoice'] ?? 0;
            $v[$arr]['ignoreInvoiceImgs'] = $row['ignoreInvoiceImgs'] ?? '';
            $v[$arr]['ignoreInvoiceReason'] = $row['ignoreInvoiceReason'] ?? '';
            $v[$arr]['ignoreInvoiceName'] = $row['ignoreInvoiceName'] ?? '';
            $v[$arr]['ignoreInvoiceTime'] = $row['ignoreInvoiceTime'] ?? '0000-00-00 00:00:00';
            $v[$arr]['disagreeIgnoreReason'] = $row['disagreeIgnoreReason'] ?? '';
            $v[$arr]['isDraft'] = $row['isDraft'] ?? 0;
        }
        if (isset($v)) {
            if ($data['id'] > 0) {
                // 真删除
                $this->db->table('bill_contract_info')->where('iid', $iid)->delete();
            }
            $this->db->table('bill_contract_info')->insertBatch($v);
        }
    }
    // info数据 产品无变化 全部更新
    private function contract_info_update(int $iid, array $data)
    {
        foreach ($data['entries'] as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['iid'] = intval($iid);
            $v[$arr]['zid'] = trim($row['zid']);
            $v[$arr]['billDate'] = $data['billDate'];
            $v[$arr]['billNo'] = $data['billNo'];
            $v[$arr]['endDate'] = $data['endDate'];
            $v[$arr]['billType'] = $data['billType'];
            $v[$arr]['transType'] = intval($data['transType']);
            $v[$arr]['isReq'] = intval($data['isReq']);
            $v[$arr]['contactId'] = intval($data['contactId']);
            $v[$arr]['storageId'] = $row['storageId'];
            $v[$arr]['goodsId'] = $row['goodsId'];
            $v[$arr]['no'] = $row['no'];
            $v[$arr]['name'] = $row['name'];
            $v[$arr]['spec'] = $row['spec'];
            $v[$arr]['sku'] = $row['sku'];
            $v[$arr]['brand'] = $row['brand'];
            $v[$arr]['unit'] = $row['unit'];
            $v[$arr]['qty'] = abs((float)$row['qty']);
            $v[$arr]['price'] = abs((float)$row['price']);
            $v[$arr]['amount'] = abs((float)$row['amount']);
            $v[$arr]['buyUrl'] = $row['buyUrl'] ?? '';
            $v[$arr]['myUrl'] = $row['myUrl'] ?? '';
            $v[$arr]['description1'] = $row['description1'];
            $v[$arr]['description2'] = $row['description2'];
            $v[$arr]['hasRecQty'] = intval($row['hasRecQty'] ?? 0);
            $v[$arr]['limitPrice'] = abs((float)$row['limitPrice']);
            $v[$arr]['uId'] = $row['uId'] ?? $data['uId'];
            $v[$arr]['uName'] = $row['uName'] ?? $data['uName'];
        }
        if (isset($v)) {
<<<<<<< HEAD
            $this->db->table('bill_contract_info')->updateBatch($v, ['id', 'iid']);
=======
            $this->db->table('bill_contract_info')->updateBatch($v, 'id');
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
        }
    }
    // info数据 转正的特采申请单 只更新部分字段
    private function contract_info_req_update(int $iid, array $data, array $oldData)
    {
        $oldAmountInfo = array_column($this->db->table('bill_contract_info')->select('zid, amount')->where('iid', $iid)->get()->getResultArray(), 'amount', 'zid');
        $paidInfo = $oldData['srcMoneyId1'] > 0 ? array_column($this->db->table('bill_money_info')->select('zid')->where('iid', $oldData['srcMoneyId1'])->where('isDelete', 0)->get()->getResultArray(), 'zid') : [];
        $errZids = [];
        $v = [];
        foreach ($data['entries'] as $arr => $row) {
            // 如果是本人的产品条目，或是已离职同事的产品条目，或是被管理员编辑的产品条目
            if ($this->myerp['uId'] == $row['uId'] || !in_array($row['uId'], $this->myerp['enableUserIds'])) {
                // 如果该产品条目已存在付款并且当前总价与以前总价不相等，记录错误产品zid，并跳出本次循环
                if (in_array($row['zid'], $paidInfo) && bccomp(abs((float)$oldAmountInfo[$row['zid']]), abs((float)$row['amount']), 2) != 0) {
                    array_push($errZids, $row['zid']);
                    continue;
                } else {
                    // 将购买标记、运单号同步到特殊销售单
                    $this->db->table('bill_contract_info')->where('billNo', $oldData['reqNo'])->where('zid', $row['zid'])->where('isDelete', 0)->update(['purId' => $row['uId'], 'purName' => $row['uName'], 'purFeedback' => ($row['purFeedback'] ?? '')]);
                    array_push($v, [
                        'id' => intval($row['id']),
<<<<<<< HEAD
                        'iid' => intval($iid),
=======
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
                        'price' => abs((float)$row['price']),
                        'amount' => abs((float)$row['amount']),
                        'myUrl' => $row['myUrl'] ?? '',
                        'description1' => $row['description1'],
                        'description2' => $row['description2'],
                        'hasRecQty' => intval($row['hasRecQty'] ?? 0),
                        'purFeedback' => $row['purFeedback'] ?? ''
                    ]);
                    if (bccomp($oldAmountInfo[$row['zid']], $row['amount'], 2) != 0) { // 新旧金额不相等
                        $this->myservice->log('将' . $this->myerp['transTypeName'][$data['transType']] . ' ' . $data['billNo'] . ' 中自编序号为 ' . $row['zid'] . ' 的产品的采购总价由 ' . $oldAmountInfo[$row['zid']] . ' 改为 ' . $row['amount']);
                    }
                }
            } else {
                continue;
            }
        }
        if (count($v) > 0) {
<<<<<<< HEAD
            $this->db->table('bill_contract_info')->updateBatch($v, ['id', 'iid']);
=======
            $this->db->table('bill_contract_info')->updateBatch($v, 'id');
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
        }
        return $errZids;
    }
    // 导出收货清单
    public function exportInfoGrid()
    {
        if (!$this->myservice->verifyRight('PC_EXPORT')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvPc/exportInfoGrid 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = $this->request->getGetPost('id');
        $uId = intval($this->request->getGetPost('uId'));
        $data = $this->db->table('bill_contract')->where('id', $id)->get()->getRowArray();
        $name = ($data['isFill'] == 1 ? '特采补买单' : '特殊采购单') . '_收货清单_' . $data['billNo'];
        $builder = $this->db->table('bill_contract_info')->where('iid', $id);
         if ($uId > 0) {
             $builder->where('uId', $uId);
         }
        $list = $builder->get()->getResultArray();

        // 调用spreadsheet处理表格
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $activeWorksheet = $spreadsheet->getActiveSheet();

        // A-Z列 设置字符串自动换行(会撑开单元格高度,并破坏设置的默认行高)，设置水平居左+垂直居中
        $activeWorksheet->getStyle("A:Z")->getAlignment()->setWrapText(TRUE);
        $activeWorksheet->getStyle('A:Z')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $activeWorksheet->getStyle('A:Z')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        // 设置边框
        $activeWorksheet->setShowGridlines(false);
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => '00000000'],
                ]
            ],
        ];
        $activeWorksheet->getStyle('A1:I' . (COUNT($list) + 3))->applyFromArray($styleArray, false);

        // 合并单元格
        $activeWorksheet->mergeCells('A1:I1');
        $activeWorksheet->mergeCells('A2:I2');

        // 设置水平居中
        $activeWorksheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $activeWorksheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // 设置行高
        $activeWorksheet->getRowDimension('1')->setRowHeight(40);
        $activeWorksheet->getRowDimension('2')->setRowHeight(40);

        // 设置加粗
        $activeWorksheet->getStyle('A1')->getFont()->setBold(true);
        $activeWorksheet->getStyle('A2')->getFont()->setBold(true);
        $activeWorksheet->getStyle('A3:I3')->getFont()->setBold(true);

        // 设置背景色
        $activeWorksheet->getStyle('A3:I3')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        $activeWorksheet->getStyle('A3:I3')->getFill()->getStartColor()->setRGB('F5F5F5');

        // 设置每列列宽
        $activeWorksheet->getColumnDimension('A')->setWidth(10);
        $activeWorksheet->getColumnDimension('B')->setWidth(20);
        $activeWorksheet->getColumnDimension('C')->setWidth(20);
        $activeWorksheet->getColumnDimension('D')->setWidth(20);
        $activeWorksheet->getColumnDimension('E')->setWidth(10);
        $activeWorksheet->getColumnDimension('F')->setWidth(10);
        $activeWorksheet->getColumnDimension('G')->setWidth(10);
        $activeWorksheet->getColumnDimension('H')->setWidth(10);
        $activeWorksheet->getColumnDimension('I')->setWidth(20);

        //设置当前的sheet
        $activeWorksheet->setCellValue('A1', $name . '（导出时间 ' . date('Y-m-d H:i:s') . '）');
        $activeWorksheet->setCellValue('A2', $data['reqInfo']);
        $activeWorksheet->setCellValue('A3', '自编序号');
        $activeWorksheet->setCellValue('B3', '产品名称');
        $activeWorksheet->setCellValue('C3', '规格型号');
        $activeWorksheet->setCellValue('D3', '辅助属性');
        $activeWorksheet->setCellValue('E3', '产品品牌');
        $activeWorksheet->setCellValue('F3', '单位');
        $activeWorksheet->setCellValue('G3', '数量');
        $activeWorksheet->setCellValue('H3', '采购人');
        $activeWorksheet->setCellValue('I3', '收货备注');

        $i = 4;
        foreach ($list as $k => $v) {
            $activeWorksheet->setCellValueExplicit('A' . $i, $v['zid'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $activeWorksheet->setCellValue('B' . $i, $v['name']);
            $activeWorksheet->setCellValue('C' . $i, $v['spec']);
            $activeWorksheet->setCellValue('D' . $i, $v['sku']);
            $activeWorksheet->setCellValue('E' . $i, $v['brand']);
            $activeWorksheet->setCellValue('F' . $i, $v['unit']);
            $activeWorksheet->setCellValue('G' . $i, $v['qty']);
            $activeWorksheet->setCellValue('H' . $i, $v['uName']);
            $activeWorksheet->setCellValue('I' . $i, '');
            $i++;
        }

        $outputFileName = $name . ".xlsx";
        $this->myservice->log('导出了 ' . $name);
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();
        return $this->response
            ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->setHeader('Content-Disposition', "attachment; filename=\"{$outputFileName}\"")
            ->setHeader('Cache-Control', 'max-age=0')
            ->setBody($content);
    }
    // 导出清单报价详情
    public function exportInfoGridAll()
    {
        if (!$this->myservice->verifyRight('SYS_ADMIN')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvPc/exportInfoGridAll 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = $this->request->getGetPost('id');
        $data = $this->db->table('bill_contract')->where('id', $id)->get()->getRowArray();
        $name = ($data['isFill'] == 1 ? '特采补买单' : '特殊采购单') . '_清单报价详情_' . $data['billNo'];
        $list = $this->db->table('bill_contract_info')->where('iid', $id)->get()->getResultArray();

        // 调用spreadsheet处理表格
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $activeWorksheet = $spreadsheet->getActiveSheet();

        // A-Z列 设置字符串自动换行(会撑开单元格高度,并破坏设置的默认行高)，设置水平居左+垂直居中
        $activeWorksheet->getStyle("A:Z")->getAlignment()->setWrapText(TRUE);
        $activeWorksheet->getStyle('A:Z')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $activeWorksheet->getStyle('A:Z')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        // 设置边框
        $activeWorksheet->setShowGridlines(false);
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => '00000000'],
                ]
            ],
        ];
        $activeWorksheet->getStyle('A1:O' . (COUNT($list) + 3))->applyFromArray($styleArray, false);

        // 合并单元格
        $activeWorksheet->mergeCells('A1:O1');
        $activeWorksheet->mergeCells('A2:O2');

        // 设置水平居中
        $activeWorksheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $activeWorksheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // 设置行高
        $activeWorksheet->getRowDimension('1')->setRowHeight(40);
        $activeWorksheet->getRowDimension('2')->setRowHeight(40);

        // 设置加粗
        $activeWorksheet->getStyle('A1')->getFont()->setBold(true);
        $activeWorksheet->getStyle('A2')->getFont()->setBold(true);
        $activeWorksheet->getStyle('A3:O3')->getFont()->setBold(true);

        // 设置背景色
        $activeWorksheet->getStyle('A3:O3')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        $activeWorksheet->getStyle('A3:O3')->getFill()->getStartColor()->setRGB('F5F5F5');

        // 设置每列列宽
        $activeWorksheet->getColumnDimension('A')->setWidth(10);
        $activeWorksheet->getColumnDimension('B')->setWidth(20);
        $activeWorksheet->getColumnDimension('C')->setWidth(20);
        $activeWorksheet->getColumnDimension('D')->setWidth(20);
        $activeWorksheet->getColumnDimension('E')->setWidth(10);
        $activeWorksheet->getColumnDimension('F')->setWidth(10);
        $activeWorksheet->getColumnDimension('G')->setWidth(10);
        $activeWorksheet->getColumnDimension('H')->setWidth(10);
        $activeWorksheet->getColumnDimension('I')->setWidth(20);
        $activeWorksheet->getColumnDimension('J')->setWidth(20);
        $activeWorksheet->getColumnDimension('K')->setWidth(20);
        $activeWorksheet->getColumnDimension('L')->setWidth(20);
        $activeWorksheet->getColumnDimension('M')->setWidth(20);
        $activeWorksheet->getColumnDimension('N')->setWidth(20);
        $activeWorksheet->getColumnDimension('O')->setWidth(20);
        //设置当前的sheet
        $activeWorksheet->setCellValue('A1', $name . '（导出时间 ' . date('Y-m-d H:i:s') . '）');
        $activeWorksheet->setCellValue('A2', $data['reqInfo']);
        $activeWorksheet->setCellValue('A3', '自编序号');
        $activeWorksheet->setCellValue('B3', '产品名称');
        $activeWorksheet->setCellValue('C3', '规格型号');
        $activeWorksheet->setCellValue('D3', '辅助属性');
        $activeWorksheet->setCellValue('E3', '产品品牌');
        $activeWorksheet->setCellValue('F3', '单位');
        $activeWorksheet->setCellValue('G3', '数量');
        $activeWorksheet->setCellValue('H3', '采购人');
        $activeWorksheet->setCellValue('I3', '采购单价');
        $activeWorksheet->setCellValue('J3', '采购总价');
        $activeWorksheet->setCellValue('K3', '销售总价');
        $activeWorksheet->setCellValue('L3', '参考链接');
        $activeWorksheet->setCellValue('M3', '采购链接');
        $activeWorksheet->setCellValue('N3', '备注1');
        $activeWorksheet->setCellValue('O3', '备注2');

        $i = 4;
        foreach ($list as $k => $v) {
            $activeWorksheet->setCellValueExplicit('A' . $i, $v['zid'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $activeWorksheet->setCellValue('B' . $i, $v['name']);
            $activeWorksheet->setCellValue('C' . $i, $v['spec']);
            $activeWorksheet->setCellValue('D' . $i, $v['sku']);
            $activeWorksheet->setCellValue('E' . $i, $v['brand']);
            $activeWorksheet->setCellValue('F' . $i, $v['unit']);
            $activeWorksheet->setCellValue('G' . $i, $v['qty']);
            $activeWorksheet->setCellValue('H' . $i, $v['uName']);
            $activeWorksheet->setCellValue('I' . $i, $v['price']);
            $activeWorksheet->setCellValue('J' . $i, $v['amount']);
            $activeWorksheet->setCellValue('K' . $i, (float)$v['limitPrice'] > 0 ? ((float)$v['limitPrice'] * 1.25 * (float)$v['qty']) : '');
            $activeWorksheet->setCellValue('L' . $i, $v['buyUrl']);
            $activeWorksheet->setCellValue('M' . $i, $v['myUrl']);
            $activeWorksheet->setCellValue('N' . $i, $v['description1']);
            $activeWorksheet->setCellValue('O' . $i, $v['description2']);
            $i++;
        }

        $outputFileName = $name . ".xlsx";
        $this->myservice->log('导出了 ' . $name);
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();
        return $this->response
            ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->setHeader('Content-Disposition', "attachment; filename=\"{$outputFileName}\"")
            ->setHeader('Cache-Control', 'max-age=0')
            ->setBody($content);
    }
    // 上传资料
    public function uploadfile()
    {
        set_time_limit(0);
        session_write_close();
        if (empty($_FILES)){
            return $this->response->setJSON(['status' => -1, 'msg' => '没有上传文件！']);
        };
        $uppath = 'data/upfile/contract/';
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
        $this->myservice->log('上传了特殊采购资料：' . $_FILES['file']['name']);
        $json['status'] = 200;
        $json['msg'] = '资料上传成功';
        $json['file'] = ['name' => $_FILES['file']['name'], 'url' => $target_file, 'upName' => $this->myerp['uName'], 'upTime' => date('Y-m-d')];
        return $this->response->setJSON($json);
    }
    // 数据验证
    private function validform(array $data)
    {
        $data['id'] = isset($data['id']) ? intval($data['id']) : -1;
        if ($data['id'] > 0) {
            if ($this->db->table('bill_contract')->where(['id' => $data['id'], 'isDelete' => 0])->countAllResults() < 1) {
                return $this->response->setJSON(['status' => -1, 'msg' => '单据不存在、或者已删除！']);
            }
            $oldData = $this->db->table('bill_contract')->where('id', $data['id'])->get()->getRowArray();
            $oldInfo = array_column($this->db->table('bill_contract_info')->where('iid', $data['id'])->get()->getResultArray(), null, 'zid');
        } else {
            if ($this->db->table('bill_contract')->where(['billNo' => $data['billNo']])->countAllResults() > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '单据编号重复！']);
            }
        }
        if ($data['billDate'] == '') {
            return $this->response->setJSON(['status' => -1, 'msg' => '单据日期不能为空！']);
        }
        if ($data['billNo'] == '') {
            return $this->response->setJSON(['status' => -1, 'msg' => '单据编号不能为空！']);
        }
        if ($data['contactName'] == '') {
            return $this->response->setJSON(['status' => -1, 'msg' => '买方不能为空！']);
        }
        if ($data['sName'] == '') {
            return $this->response->setJSON(['status' => -1, 'msg' => '卖方不能为空！']);
        }
        if ($data['endDate'] == '') {
            return $this->response->setJSON(['status' => -1, 'msg' => '交货期限不能为空！']);
        }
        if ($data['amount'] == '') {
            return $this->response->setJSON(['status' => -1, 'msg' => '最终金额不能为空！']);
        }
        $data['entries'] = $data['entries'] ?? [];
        if (count($data['entries']) < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
        }
        $data['srcPurReqId'] = isset($data['srcPurReqId']) ? intval($data['srcPurReqId']) : 0;
        $data['transType'] = intval($data['transType']);
        $data['amount'] = abs((float)$data['amount']);
        $data['totalQty'] = abs(intval($data['totalQty']));
        $data['uId'] = $data['uId'] ?? $this->myerp['uId'];
        $data['uName'] = $data['uName'] ?? $this->myerp['uName'];
        $data['createTime'] = date("Y-m-d H:i:s");
        $data['modifyTime'] = date("Y-m-d H:i:s");
        $data['isReq'] = $data['isReq'] ?? 0;
        $data['reqNo'] = $data['reqNo'] ?? '';
        $data['description'] = isset($data['description']) ? str_replace(['{', '}', '"', "'"], '”', $data['description']) : '';
        $data['files'] = strlen($data['files'] ?? '') < 10 ? '' : $data['files'];
        $data['isDraft'] = isset($data['isDraft']) ? intval($data['isDraft']) : 0;
        $data['isFill'] = isset($data['isFill']) ? intval($data['isFill']) : 0;
        foreach ($data['entries'] as $arr => $row) {
            // 处理容易出问题的特殊字符
            $data['entries'][$arr]['zid'] = str_replace(["\r", "\n", "\r\n", '{', '}', '"', "'"], '”', trim($row['zid']));
            $data['entries'][$arr]['name'] = str_replace(["\r", "\n", "\r\n", '{', '}', '"', "'"], '”', trim($row['name']));
            $data['entries'][$arr]['spec'] = str_replace(["\r", "\n", "\r\n", '{', '}', '"', "'"], '”', trim($row['spec']));
            $data['entries'][$arr]['sku'] = str_replace(["\r", "\n", "\r\n", '{', '}', '"', "'"], '”', trim($row['sku']));
            $data['entries'][$arr]['brand'] = str_replace(["\r", "\n", "\r\n", '{', '}', '"', "'"], '”', trim( $row['brand']));
            $data['entries'][$arr]['unit'] = str_replace(["\r", "\n", "\r\n", '{', '}', '"', "'"], '”', trim($row['unit']));
            $data['entries'][$arr]['buyUrl'] = $row['buyUrl'] ? shop_url_parse(trim($row['buyUrl'])) : '';
            $data['entries'][$arr]['myUrl'] = $row['myUrl'] ? shop_url_parse(trim($row['myUrl'])) : '';
            $data['entries'][$arr]['description1'] = str_replace(["\r", "\n", "\r\n", '{', '}', '"', "'"], '”', $row['description1']);
            $data['entries'][$arr]['description2'] = str_replace(["\r", "\n", "\r\n", '{', '}', '"', "'"], '”', $row['description2']);
            // 错误检查
            if (strlen($data['entries'][$arr]['name']) < 1) {
                return $this->response->setJSON(['status' => -1, 'msg' => '产品名称不能为空！']);
            }
            if ((float)$data['entries'][$arr]['qty'] < 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '数量要为正数！']);
            }
            if ((float)$data['entries'][$arr]['price'] < 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '价格要为正数！']);
            }
            // 补充数据
            $data['entries'][$arr]['storageId'] = 1;
            $data['entries'][$arr]['goodsId'] = $row['no'] ? ($this->db->table('goods')->select('id')->where(['no' => $row['no']])->get()->getRowArray()['id'] ?? 0) : 0;
            // 处理其他数据
            if ($data['id'] > 0 && isset($oldData) && isset($oldInfo)) { // 更新时才附加原来已存储的字段数据
                // 如果已被财务修改采购总价，则优先按此总价
                $newAmount = $this->db->table('bill_contract_info_modify_pur_amount')->select('newAmount')->where(['iid' => $oldData['id'], 'zid' => $row['zid'], 'isDelete' => 0])->get()->getRowArray()['newAmount'] ?? 0;
                if ($newAmount) {
                    $data['entries'][$arr]['amount'] = floatval($newAmount);
                    $data['entries'][$arr]['price'] = round(floatval($newAmount) / floatval($data['entries'][$arr]['qty']), 5);
                }
                // 附加旧数据
                if (isset($oldInfo[$data['entries'][$arr]['zid']])) {
                    $data['entries'][$arr]['quotePrice'] = $oldInfo[$data['entries'][$arr]['zid']]['quotePrice'];
                    $data['entries'][$arr]['paidAmount'] = $oldInfo[$data['entries'][$arr]['zid']]['paidAmount'];
                    $data['entries'][$arr]['paidTime'] = $oldInfo[$data['entries'][$arr]['zid']]['paidTime'];
                    $data['entries'][$arr]['isHasExpress'] = $oldInfo[$data['entries'][$arr]['zid']]['isHasExpress'];
                    $data['entries'][$arr]['isHasInvoice'] = $oldInfo[$data['entries'][$arr]['zid']]['isHasInvoice'];
                    $data['entries'][$arr]['isPurchased'] = $oldInfo[$data['entries'][$arr]['zid']]['isPurchased'];
                    $data['entries'][$arr]['noPurchaseReason'] = $oldInfo[$data['entries'][$arr]['zid']]['noPurchaseReason'];
                    $data['entries'][$arr]['isRecInvoice'] = $oldInfo[$data['entries'][$arr]['zid']]['isRecInvoice'];
                    $data['entries'][$arr]['purSuggestId'] = $oldInfo[$data['entries'][$arr]['zid']]['purSuggestId'];
                    $data['entries'][$arr]['purId'] = $oldInfo[$data['entries'][$arr]['zid']]['purId'];
                    $data['entries'][$arr]['purName'] = $oldInfo[$data['entries'][$arr]['zid']]['purName'];
                    $data['entries'][$arr]['purPriceAnormalStatus'] = $oldInfo[$data['entries'][$arr]['zid']]['purPriceAnormalStatus'];
                    $data['entries'][$arr]['isIgnoreInvoice'] = $oldInfo[$data['entries'][$arr]['zid']]['isIgnoreInvoice'];
                    $data['entries'][$arr]['ignoreInvoiceImgs'] = $oldInfo[$data['entries'][$arr]['zid']]['ignoreInvoiceImgs'];
                    $data['entries'][$arr]['ignoreInvoiceReason'] = $oldInfo[$data['entries'][$arr]['zid']]['ignoreInvoiceReason'];
                    $data['entries'][$arr]['ignoreInvoiceName'] = $oldInfo[$data['entries'][$arr]['zid']]['ignoreInvoiceName'];
                    $data['entries'][$arr]['ignoreInvoiceTime'] = $oldInfo[$data['entries'][$arr]['zid']]['ignoreInvoiceTime'];
                    $data['entries'][$arr]['disagreeIgnoreReason'] = $oldInfo[$data['entries'][$arr]['zid']]['disagreeIgnoreReason'];
                    $data['entries'][$arr]['isDraft'] = $oldInfo[$data['entries'][$arr]['zid']]['isDraft'];
                }
            }
            // 组装goods字段
            $data['goods'][$arr]['zid'] = $data['entries'][$arr]['zid'];
            $data['goods'][$arr]['name'] = $data['entries'][$arr]['name'];
            $data['goods'][$arr]['spec'] = $data['entries'][$arr]['spec'];
            $data['goods'][$arr]['sku'] = $data['entries'][$arr]['sku'];
            $data['goods'][$arr]['brand'] = $data['entries'][$arr]['brand'];
            $data['goods'][$arr]['unit'] = $data['entries'][$arr]['unit'];
            $data['goods'][$arr]['qty'] = abs((float)$data['entries'][$arr]['qty']);
            $data['goods'][$arr]['price'] = abs((float)$data['entries'][$arr]['price']);
            $data['goods'][$arr]['amount'] = abs((float)$data['entries'][$arr]['amount']);
        }
        $data['goods'] = json_encode($data['goods'], 320);
        return $data;
    }

    // 特采补买 列表
    public function listFill()
    {
        if (!$this->myservice->verifyRight('PC_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvPc/listFill 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $status1 = intval($this->request->getGetPost('status1'));
        $status2 = intval($this->request->getGetPost('status2'));
        $status3 = intval($this->request->getGetPost('status3'));
        $status4 = intval($this->request->getGetPost('status4'));
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');
        $minAmount = (float)$this->request->getGetPost('minAmount');
        $maxAmount = (float)$this->request->getGetPost('maxAmount');
        $skey = $this->request->getGetPost('skey');
        $builder = $this->db->table('bill_contract')->where('billType', 'PUR')->where('isFill', 1);
        if ($status1 >= 0) {
            if ($status1 > 0) {
                $builder->where('checkId >', 0);
            } else {
                $builder->where('checkId', 0);
            }
        }
        if ($status2 >= 0) {
            $builder->where('moneyStatus1', $status2);
        }
        if ($status3 >= 0) {
            $builder->where('moneyStatus2', $status3);
        }
        if ($status3 == -2) {
            $srcIds = array_column($this->db->table('bill_money_info')->select('srcId')->where(['moneyType' => 9, 'transType' => 103101, 'miniType' => 3, 'isDelete' => 0])->groupBy('srcId')->get()->getResultArray(), 'srcId');
            if ($srcIds) {
                $builder->whereIn('id', $srcIds);
            } else {
                $builder->where('id', -1);
            }
        }
        if ($status4 >= 0) {
            if ($status4 == 0) {
                $builder->where('invoiceNum', 0);
            } else {
                $builder->where('invoiceNum >', 0);
            }
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
                    ->orLike('contactName', $this->db->escapeLikeString($kw))
                    ->orLike('sName', $this->db->escapeLikeString($kw))
                    ->orLike('files', $this->db->escapeLikeString($kw))
                    ->orLike('goods', $this->db->escapeLikeString($kw))
                    ->orLike('description', $this->db->escapeLikeString($kw))
                ->groupEnd();
            }
        }
        $builder->where('isDelete', 0);
        $allowedSort = ['id','billDate','billNo','contactName','uName','description','checkId','moneyStatus1','moneyStatus2','invoiceNum','isReq'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $list = (clone $builder)->orderBy($orderField, $sord)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['billDate'] = $row['billDate'];
            $v[$arr]['billNo'] = $row['billNo'];
            $v[$arr]['transType'] = intval($row['transType']);
            $v[$arr]['isReq'] = intval($row['isReq']);
            $v[$arr]['totalQty'] = intval($row['totalQty']);
            $v[$arr]['contactId'] = intval($row['contactId']);
            $v[$arr]['contactName'] = $row['contactName'];
            $v[$arr]['sId'] = intval($row['sId']);
            $v[$arr]['sName'] = $row['sName'];
            $v[$arr]['endDate'] = $row['endDate'];
            $v[$arr]['amount'] = (float)$row['amount'];
            $v[$arr]['description'] = $row['description'];
            $v[$arr]['moneyStatus1'] = intval($row['moneyStatus1']);
            $v[$arr]['moneyStatus2'] = intval($row['moneyStatus2']);
            $v[$arr]['srcMoneyId1'] = intval($row['srcMoneyId1']);
            $v[$arr]['srcMoneyId2'] = intval($row['srcMoneyId2']);
            $v[$arr]['srcMoneyNo1'] = $row['srcMoneyNo1'];
            $v[$arr]['srcMoneyNo2'] = $row['srcMoneyNo2'];
            $v[$arr]['goodsStatus1'] = intval($row['goodsStatus1']);
            $v[$arr]['goodsStatus2'] = intval($row['goodsStatus2']);
            $v[$arr]['invoiceNum'] = intval($row['invoiceNum']);
            $v[$arr]['invoiceAmount'] = $row['invoiceAmount'];
            $v[$arr]['uId'] = $row['uId'];
            $v[$arr]['uName'] = $row['uName'];
            $v[$arr]['checkId'] = intval($row['checkId']);
            $v[$arr]['createTime'] = $row['createTime'];
            $v[$arr]['goods'] = (array)json_decode($row['goods'], true);
            $v[$arr]['isDraft'] = intval($row['isDraft']);
        }
        // 款项汇总
        $totalNotClearedAmount = (clone $builder)->where('moneyStatus1', 0)->selectSum('amount', 'totalNotClearedAmount')->get()->getRowArray()['totalNotClearedAmount'] ?? 0;
        $partIdsArr = [];
        $partIds = (clone $builder)->select('srcMoneyId1')->where('moneyStatus1', 1)->get()->getResultArray();
        if (count($partIds) > 0) {
            foreach ($partIds as $arr2 => $row2) {
                array_push($partIdsArr, $row2['srcMoneyId1']);
                if (count($partIdsArr) == 1000) { // 分片操作，每1000处理一次
                    $totalNotClearedAmount_part = $this->db->table('bill_money')->selectSum('amount')->selectSum('cleared')->whereIn('id', $partIdsArr)->get()->getRowArray() ?? ['amount' => 0, 'cleared' => 0];
                    $totalNotClearedAmount = (float)$totalNotClearedAmount + (float)$totalNotClearedAmount_part['amount'] - (float)$totalNotClearedAmount_part['cleared'];
                    $partIdsArr = [];
                }
            }
            if (count($partIdsArr) > 0) { // 处理分片操作剩余部分
                $totalNotClearedAmount_part = $this->db->table('bill_money')->selectSum('amount')->selectSum('cleared')->whereIn('id', $partIdsArr)->get()->getRowArray() ?? ['amount' => 0, 'cleared' => 0];
                $totalNotClearedAmount = (float)$totalNotClearedAmount + (float)$totalNotClearedAmount_part['amount'] - (float)$totalNotClearedAmount_part['cleared'];
                $partIdsArr = [];
            }
        }
        $json['totalNotClearedAmount'] = $totalNotClearedAmount;
        $total = $builder->countAllResults();
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['page'] = $page;
        $json['data']['records'] = $total;
        $json['data']['total'] = ceil($json['data']['records'] / $rows);
        $json['data']['rows'] = $v;
        return $this->response->setJSON($json);
    }

    // 特采遗漏 列表
    public function listLost()
    {
        if (!$this->myservice->verifyRight('PC_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvPc/listLost 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $uId = intval($this->request->getGetPost('uId'));
        $builder = $this->db->table('bill_contract_info');
        $builder->where('transType', 101111)
                ->where('isReq !=', 1)
                ->where('isPurchased <', 1)
                ->where('paidAmount < amount', null,false)
                ->where('isDraft', 0)
                ->where('isDelete', 0)
                ->where('endDate <=', date('Y-m-d', strtotime('+3 day')));
        if ($uId > 0) {
            $builder->where('uId', $uId);
        }
        $allowedSort = ['id','billNo','endDate'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $list = (clone $builder)->orderBy($orderField, $sord)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['iid'] = intval($row['iid']);
            $v[$arr]['zid'] = $row['zid'];
            $v[$arr]['billDate'] = $row['billDate'];
            $v[$arr]['billNo'] = $row['billNo'];
            $v[$arr]['endDate'] = $row['endDate'];
            $v[$arr]['transType'] = intval($row['transType']);
            $v[$arr]['isReq'] = intval($row['isReq']);
            $v[$arr]['uId'] = $row['uId'];
            $v[$arr]['uName'] = $row['uName'];
            $v[$arr]['no'] = $row['no'];
            $v[$arr]['name'] = $row['name'];
            $v[$arr]['spec'] = $row['spec'];
            $v[$arr]['sku'] = $row['sku'];
            $v[$arr]['brand'] = $row['brand'];
            $v[$arr]['unit'] = $row['unit'];
            $v[$arr]['qty'] = intval($row['qty']);
            $v[$arr]['quotePrice'] = (float)$row['quotePrice'];
            $v[$arr]['buyUrl'] = $row['buyUrl'];
            $v[$arr]['myUrl'] = $row['myUrl'];
            $v[$arr]['description1'] = $row['description1'];
            $v[$arr]['description2'] = $row['description2'];
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['page'] = $page;
        $json['data']['records'] = $total;
        $json['data']['total'] = ceil($json['data']['records'] / $rows);
        $json['data']['rows'] = $v;
        return $this->response->setJSON($json);
    }

    // 修改采购总价
    public function modifyPurAmount()
    {
        if (!$this->myservice->verifyRight('FINANCE_MANAGER')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvPc/modifyPurAmount 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $purAmount = floatval($this->request->getGetPost('purAmount'));
        $modifyReason = $this->request->getGetPost('modifyReason');
        $contract_info_pur = $this->db->table('bill_contract_info')->where(['id' => $id])->get()->getRowArray();
        if ($contract_info_pur) {
            $this->db->transStart();
            $this->db->table('bill_contract_info')->where(['id' => $id])->update(['amount' => round($purAmount, 2), 'price' => round($purAmount / floatval($contract_info_pur['qty']), 5)]);
            if ($this->db->table('bill_contract_info_modify_pur_amount')->where(['iid' => $contract_info_pur['iid'], 'zid' => $contract_info_pur['zid'], 'isDelete' => 0])->countAllResults() > 0) { // 有则更新
                $this->db->table('bill_contract_info_modify_pur_amount')->where(['iid' => $contract_info_pur['iid'], 'zid' => $contract_info_pur['zid']])->update(['newAmount' => round($purAmount, 2), 'modifyName' => $this->myerp['uName'], 'modifyTime' => date('Y-m-d H:i:s'), 'modifyReason' => $modifyReason, 'isDisable' => 0, 'disReason' => '']);
            } else { // 无则插入
                $this->db->table('bill_contract_info_modify_pur_amount')->insert(['billNo' => $contract_info_pur['billNo'], 'iid' => $contract_info_pur['iid'], 'zid' => $contract_info_pur['zid'], 'name' => $contract_info_pur['name'], 'spec' => $contract_info_pur['spec'], 'sku' => $contract_info_pur['sku'], 'brand' => $contract_info_pur['brand'], 'unit' => $contract_info_pur['unit'], 'qty' => $contract_info_pur['qty'], 'uName' => $contract_info_pur['uName'], 'oldAmount' => $contract_info_pur['amount'], 'newAmount' => round($purAmount, 2), 'modifyName' => $this->myerp['uName'], 'modifyTime' => date('Y-m-d H:i:s'), 'modifyReason' => $modifyReason]);
            }
            $this->myservice->log('修改了特殊采购单 ' . $contract_info_pur['billNo'] . ' 内自编序号为 ' . $contract_info_pur['zid'] . ' 采购人为 ' . $contract_info_pur['uName'] . ' 的产品的采购总价，从 ' . $contract_info_pur['amount'] . ' 修改为 ' . round($purAmount, 2));
            $contract = $this->db->table('bill_contract')->where(['id' => $contract_info_pur['iid']])->get()->getRowArray();
            $contract_info_pur['amount'] = round($purAmount, 2);
            $contract_info_pur['price'] = round($purAmount / floatval($contract_info_pur['qty']), 5);
            $contract_info_pur['limitAmount'] = (float)$contract_info_pur['limitPrice'] * (float)$contract_info_pur['qty'];
            $contract_info_pur['refundingAmount'] = $this->db->table('bill_money_info')->selectSum('amount', 'refundingAmount')->where(['iid' => $contract['srcMoneyId2'], 'zid' => $contract_info_pur['zid'], 'moneyType' => 9, 'isDelete' => 0])->get()->getRowArray()['refundingAmount'] ?? '0.00';
            $contract_info_pur['totalPaidAmount'] = $this->db->table('bill_money_info')->selectSum('amount', 'totalPaidAmount')->where(['iid' => $contract['srcMoneyId1'], 'zid' => $contract_info_pur['zid'], 'isDelete' => 0])->get()->getRowArray()['totalPaidAmount'] ?? '0.00';
            $contract_info_pur['refundedAmount'] = $this->db->table('bill_money_info')->selectSum('amount', 'refundedAmount')->where(['iid' => $contract['srcMoneyId2'], 'zid' => $contract_info_pur['zid'], 'isDelete' => 0])->whereIn('moneyType', [0, 1])->get()->getRowArray()['refundedAmount'] ?? '0.00';
            $contract_info_pur['refundedCashbackAmount'] = $this->db->table('bill_money_info')->selectSum('amount', 'refundedCashbackAmount')->where(['iid' => $contract['srcMoneyId2'], 'zid' => $contract_info_pur['zid'], 'moneyType' => 2, 'isDelete' => 0])->get()->getRowArray()['refundedCashbackAmount'] ?? '0.00';
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '操作成功！', 'data' => $contract_info_pur]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 改价通知 列表
    public function modifyPurAmountList()
    {
        if (!$this->myservice->verifyRight('PC_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvPc/modifyPurAmountList 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $uName = $this->request->getGetPost('uName');
        $isDisable = intval($this->request->getGetPost('isDisable'));
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');
        $skey = $this->request->getGetPost('skey');
        $builder = $this->db->table('bill_contract_info_modify_pur_amount');
        if ($uName) {
            if ($this->myerp['user']['position'] == '采购经理') {
                $leavedUser = implode(',', array_column($this->db->table('user')->select('name')->where(['department' => '采购部', 'isDisable' => 1])->get()->getResultArray(), 'name'));
                $builder->groupStart()
                    ->where('FIND_IN_SET(uName, ' . $this->db->escape($leavedUser) . ') >', 0, false)
                    ->orWhere('uName', $uName)
                ->groupEnd();
            } else {
                $builder->where('uName', $uName);
            }
        }
        if ($isDisable >= 0) {
            $builder->where('isDisable', $isDisable);
        }
        if ($beginDate) {
            $builder->where('modifyTime >=', $beginDate . ' 00:00:00');
        }
        if ($endDate) {
            $builder->where('modifyTime <=', $endDate . ' 23:59:59');
        }
        if ($skey !== '' && $skey !== null) {
            $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
            foreach ($keywords as $kw) {
                $builder->groupStart()
                    ->like('billNo', $this->db->escapeLikeString($kw))
                    ->orLike('zid', $this->db->escapeLikeString($kw))
                    ->orLike('name', $this->db->escapeLikeString($kw))
                    ->orLike('spec', $this->db->escapeLikeString($kw))
                    ->orLike('sku', $this->db->escapeLikeString($kw))
                    ->orLike('modifyName', $this->db->escapeLikeString($kw))
                    ->orLike('modifyReason', $this->db->escapeLikeString($kw))
                    ->orLike('disReason', $this->db->escapeLikeString($kw))
                ->groupEnd();
            }
        }
        $builder->where('isDelete', 0);
        $allowedSort = ['id','billNo','modifyTime'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $list = (clone $builder)->orderBy($orderField, $sord)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['billNo'] = $row['billNo'];
            $v[$arr]['iid'] = intval($row['iid']);
            $v[$arr]['zid'] = $row['zid'];
            $v[$arr]['name'] = $row['name'];
            $v[$arr]['spec'] = $row['spec'];
            $v[$arr]['sku'] = $row['sku'];
            $v[$arr]['brand'] = $row['brand'];
            $v[$arr]['unit'] = $row['unit'];
            $v[$arr]['qty'] = (float)$row['qty'];
            $v[$arr]['uName'] = $row['uName'];
            $v[$arr]['oldAmount'] = (float)$row['oldAmount'];
            $v[$arr]['newAmount'] = (float)$row['newAmount'];
            $v[$arr]['modifyName'] = $row['modifyName'];
            $v[$arr]['modifyTime'] = $row['modifyTime'];
            $v[$arr]['modifyReason'] = $row['modifyReason'];
            $v[$arr]['isDisable'] = intval($row['isDisable']);
            $v[$arr]['disReason'] = $row['disReason'];
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['page'] = $page;
        $json['data']['records'] = $total;
        $json['data']['total'] = ceil($json['data']['records'] / $rows);
        $json['data']['rows'] = $v;
        return $this->response->setJSON($json);
    }
    // 改价通知 操作
    public function modifyPurAmountOprate()
    {
        if (!$this->myservice->verifyRight('PC_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvPc/modifyPurAmountOprate 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $type = intval($this->request->getGetPost('type'));
        $isDisable = intval($this->request->getGetPost('isDisable'));
        $disReason = $this->request->getGetPost('disReason');
        $data = $this->db->table('bill_contract_info_modify_pur_amount')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
        if ($data) {
            $this->db->transStart();
            $this->db->table('bill_contract_info_modify_pur_amount')->where(['id' => $id])->update(['isDisable' => $isDisable, 'disReason' => $disReason]);
            $tit = '';
            if ($type == 0) {
                if ($isDisable == 0) {
                    if ($data['isDisable'] == 1) {
                        $tit = '取消了同意的';
                    } elseif ($data['isDisable'] == 2) {
                        $tit = '取消了拒绝的';
                    }
                } elseif ($isDisable == 1) {
                    if ($data['isDisable'] == 0) {
                        $tit = '同意了';
                    }
                } elseif ($isDisable == 2) {
                    if ($data['isDisable'] == 0) {
                        $tit = '拒绝了';
                    }
                }
                $this->myservice->log($tit . '特采改价，单号：' . $data['billNo'] . '，自编序号：' . $data['zid'] . '，修改人：' . $data['modifyName']);
            } else {
                $tit = '再次推送了';
                $this->myservice->log($tit . '特采改价，单号：' . $data['billNo'] . '，自编序号：' . $data['zid'] . '，采购人：' . $data['uName']);
            }
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '操作成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }

    // 价差异常 列表
    public function listPurPriceAnormaly()
    {
        if (!$this->myservice->verifyRight('SYS_ADMIN')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvPc/listPurPriceAnormaly 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $status1 = intval($this->request->getGetPost('status1'));
        $status2 = intval($this->request->getGetPost('status2'));
        $status3 = intval($this->request->getGetPost('status3'));
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');
        $skey = $this->request->getGetPost('skey');
        $unDone = intval($this->request->getGetPost('unDone'));
        $amount = $status2 == 0 ? 0 : ($status2 == 1 ? 1000 : 10000);
        $multiple = $status3 + 3;
        $builder = $this->db->table('bill_contract_info');
        $builder->where('transType', 101111)
                ->where('limitPrice >', 0)
                ->where('paidAmount >', 0)
                ->where('paidTime !=', '0000-00-00 00:00:00')
                ->groupStart()
                    ->where('(limitPrice * 1.25 * qty) > ' . $amount, null, false)
                    ->orWhere('paidAmount > ' . $amount, null, false)
                ->groupEnd()
                ->groupStart()
                    ->where('(limitPrice * 1.25 * qty) > (paidAmount * ' . $multiple . ')', null, false)
                    ->orWhere('paidAmount > (limitPrice * 1.25 * qty * ' . $multiple . ')', null, false)
                ->groupEnd()
                ->where('isDelete', 0);
        if ($status1 > -1) {
            $builder->where('uId', $status1);
        }
        if ($beginDate) {
            $builder->where('paidTime >=', $beginDate . ' 00:00:00');
        }
        if ($endDate) {
            $builder->where('paidTime <=', $endDate . ' 23:59:59');
        }
        if ($unDone > 0) {
            $builder->where('purPriceAnormalStatus', 0);
        }
        if ($skey !== '' && $skey !== null) {
            $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
            foreach ($keywords as $kw) {
                $builder->groupStart()
                    ->like('billNo', $this->db->escapeLikeString($kw))
                    ->orLike('no', $this->db->escapeLikeString($kw))
                    ->orLike('name', $this->db->escapeLikeString($kw))
                    ->orLike('spec', $this->db->escapeLikeString($kw))
                    ->orLike('sku', $this->db->escapeLikeString($kw))
                    ->orLike('brand', $this->db->escapeLikeString($kw))
                    ->orLike('description1', $this->db->escapeLikeString($kw))
                    ->orLike('description2', $this->db->escapeLikeString($kw))
                ->groupEnd();
            }
        }
        $list = (clone $builder)->orderBy('purPriceAnormalStatus ASC, id DESC')->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['iid'] = intval($row['iid']);
            $v[$arr]['billNo'] = $row['billNo'];
            $v[$arr]['zid'] = $row['zid'];
            $v[$arr]['no'] = $row['no'];
            $v[$arr]['name'] = $row['name'];
            $v[$arr]['spec'] = $row['spec'];
            $v[$arr]['sku'] = $row['sku'];
            $v[$arr]['brand'] = $row['brand'];
            $v[$arr]['unit'] = $row['unit'];
            $v[$arr]['qty'] = (float)$row['qty'];
            $v[$arr]['limitPrice'] = (float)$row['limitPrice'];
            $v[$arr]['saleAmount'] = round((float)$row['limitPrice'] * 1.25 * (float)$row['qty'], 2);
            $v[$arr]['amount'] = (float)$row['amount'];
            $v[$arr]['uId'] = intval($row['uId']);
            $v[$arr]['uName'] = $row['uName'];
            $v[$arr]['paidAmount'] = round((float)$row['paidAmount'], 2);
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['page'] = $page;
        $json['data']['records'] = $total;
        $json['data']['total'] = ceil($json['data']['records'] / $rows);
        $json['data']['rows'] = $v;
        return $this->response->setJSON($json);
    }

    // 特采申请 列表
    public function listReq()
    {
        if (!$this->myservice->verifyRight('PURREQ_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvPc/listReq 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $reqId = intval($this->request->getGetPost('reqId'));
        $skey = $this->request->getGetPost('skey');
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');
        $builder = $this->db->table('bill_contract');
        $builder->where('billType', 'PUR')->where('isFill', 0)->where('isReq', 1)->where('isDelete', 0);
        if ($reqId > 0) {
            $builder->where('reqId', $reqId);
        }
        if ($beginDate) {
            $builder->where('billDate >=', $beginDate);
        }
        if ($endDate) {
            $builder->where('billDate <=', $endDate);
        }
        if ($skey !== '' && $skey !== null) {
            $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
            foreach ($keywords as $kw) {
                $builder->groupStart()
                    ->like('billNo', $this->db->escapeLikeString($kw))
                    ->orLike('reqInfo', $this->db->escapeLikeString($kw))
                    ->orLike('contactName', $this->db->escapeLikeString($kw))
                    ->orLike('sName', $this->db->escapeLikeString($kw))
                    ->orLike('goods', $this->db->escapeLikeString($kw))
                    ->orLike('description', $this->db->escapeLikeString($kw))
                ->groupEnd();
            }
        }
        $allowedSort = ['id','billNo','billDate','endDate','uName','description','reqInfo','quoteNum','quoteName'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $list = (clone $builder)->orderBy($orderField, $sord)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['billDate'] = $row['billDate'];
            $v[$arr]['billNo'] = $row['billNo'];
            $v[$arr]['totalQty'] = intval($row['totalQty']);
            $v[$arr]['endDate'] = $row['endDate'];
            $v[$arr]['description'] = $row['description'];
            $v[$arr]['uId'] = $row['uId'];
            $v[$arr]['uName'] = $row['uName'];
            $v[$arr]['checkId'] = $row['checkId'];
            $v[$arr]['checkName'] = $row['checkName'];
            $v[$arr]['forceTransStatus'] = intval($row['forceTransStatus']);
            $v[$arr]['reqId'] = $row['reqId'];
            $v[$arr]['reqName'] = $row['reqName'];
            $v[$arr]['reqInfo'] = $row['reqInfo'];
            $v[$arr]['createTime'] = $row['createTime'];
            $v[$arr]['goods'] = (array)json_decode($row['goods'], true);
            $v[$arr]['isReq'] = intval($row['isReq']);
            $v[$arr]['quoteName'] = $row['quoteName'];
            $v[$arr]['quoteNum'] = intval($row['quoteNum']);
            $v[$arr]['onModify'] = intval($row['onModify']);
            $v[$arr]['modifyTimes'] = intval($row['modifyTimes']);
            $v[$arr]['isDraft'] = intval($row['isDraft']);
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['page'] = $page;
        $json['data']['records'] = $total;
        $json['data']['total'] = ceil($json['data']['records'] / $rows);
        $json['data']['rows'] = $v;
        return $this->response->setJSON($json);
    }
    // 上传表格并提取数据 特采申请
    public function uploadReqExcel()
    {
        if (!$this->myservice->verifyRight('PURREQ_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvPc/uploadReqExcel 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        set_time_limit(0);
        session_write_close();
        if (empty($_FILES)){
            return $this->response->setJSON(['status' => -1, 'msg' => '没有上传文件！']);
        };
        $uppath = 'data/upfile/contract/excel_temp/';
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
        $this->myservice->log('导入了特采申请单表格：' . $_FILES['file']['name']);

        // 调用spreadsheet处理表格
        $inputFileName = $uppath . $target_file; // 要读取的xls文件路径
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);
        $sheet = $spreadsheet->getActiveSheet();
        $sheetData = $sheet->toArray(null, true, true, true);
        // 组装数据
        $v = [];
        for ($i = 3; $i <= count($sheetData); $i++) {
            $v[$i - 3]['iid'] = 0;
            $v[$i - 3]['zid'] = $sheetData[$i]['A'] ? str_replace(["\r", "\n", "\r\n", '{', '}', '"', "'"], '”', trim($sheetData[$i]['A'])) : '';
            $v[$i - 3]['name'] = $sheetData[$i]['B'] ? str_replace(["\r", "\n", "\r\n", '{', '}', '"', "'"], '”', trim($sheetData[$i]['B'])) : '';
            $v[$i - 3]['spec'] = $sheetData[$i]['C'] ? str_replace(["\r", "\n", "\r\n", '{', '}', '"', "'"], '”', trim($sheetData[$i]['C'])) : '';
            $v[$i - 3]['sku'] = $sheetData[$i]['D'] ? str_replace(["\r", "\n", "\r\n", '{', '}', '"', "'"], '”', trim($sheetData[$i]['D'])) : '';
            $v[$i - 3]['brand'] = $sheetData[$i]['E'] ? str_replace(["\r", "\n", "\r\n", '{', '}', '"', "'"], '”', trim($sheetData[$i]['E'])) : '';
            $v[$i - 3]['unit'] = $sheetData[$i]['F'] ? str_replace(["\r", "\n", "\r\n", '{', '}', '"', "'"], '”', trim($sheetData[$i]['F'])) : '';
            $v[$i - 3]['qty'] = $sheetData[$i]['G'] ?? 0;
            // 判断是否有单元格超链接、公式超链接、或字符串url，最终提取出有效的参考链接
            $v[$i - 3]['buyUrl'] = '';
            $cell = $sheet->getCell('H' . $i); // 通过坐标直接获取单元格对象
            $url = '';
            if ($cell->hasHyperlink()) { // 判断【原生右键插入的超链接】
                $url = $cell->getHyperlink()->getUrl();
            } else { // 判断【=HYPERLINK公式生成的超链接】
                $rawValue = $cell->getValue();
                if (is_string($rawValue) && stripos($rawValue, '=HYPERLINK') === 0) {
                    if (preg_match('/^=HYPERLINK\(\s*["\']([^"\']+)["\']/i', $rawValue, $matches)) {
                        $url = $matches[1];
                    }
                }
            }
            if ($url) {
                $v[$i - 3]['buyUrl'] = shop_url_parse($url);
            } else {
                if ($sheetData[$i]['K']) {
                    preg_match('/https?:\/\/\S+/', $sheetData[$i]['K'], $matchUrl);
                    if (!empty($matchUrl[0])) {
                        $v[$i - 3]['buyUrl'] = shop_url_parse($matchUrl[0]);
                    }
                }
            }
            $v[$i - 3]['description1'] = $sheetData[$i]['I'] ? str_replace(["\r", "\n", "\r\n", '{', '}', '"', "'"], '”', $sheetData[$i]['I']) : '';
            $v[$i - 3]['description2'] = $sheetData[$i]['J'] ? str_replace(["\r", "\n", "\r\n", '{', '}', '"', "'"], '”', $sheetData[$i]['J']) : '';
            $v[$i - 3]['salePriceNewReq'] = $sheetData[$i]['K'] ?? 0;
            $v[$i - 3]['limitPrice'] = floatval($v[$i - 3]['salePriceNewReq']) * 0.8;
        }
        // 返回组装好的数据
        $info['entries'] = $v;
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data'] = $info;
        $json['totalsize'] = count($info['entries']);
        return $this->response->setJSON($json);
    }
    // 特采申请 详单导出
    public function exportReqInfo()
    {
        if (!$this->myservice->verifyRight('PURREQ_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvPc/exportReqInfo 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = $this->request->getGetPost('id');
        $data = $this->db->table('bill_contract')->where(['id' => $id])->get()->getRowArray();
        $name = '特采申请单_' . $data['billNo'];
        $list = $this->db->table('bill_contract_info')->where(['iid' => $id])->orderBy('CAST(zid AS UNSIGNED) asc, id asc')->get()->getResultArray();

        // 调用spreadsheet处理表格
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $activeWorksheet = $spreadsheet->getActiveSheet();

        // A-Z列 设置字符串自动换行(会撑开单元格高度,并破坏设置的默认行高)，设置水平居左+垂直居中
        $activeWorksheet->getStyle("A:Z")->getAlignment()->setWrapText(TRUE);
        $activeWorksheet->getStyle('A:Z')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $activeWorksheet->getStyle('A:Z')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        // 设置边框
        $activeWorksheet->setShowGridlines(false);
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => '00000000'],
                ]
            ]
        ];
        $activeWorksheet->getStyle('A1:M' . (COUNT($list) + 4))->applyFromArray($styleArray, false);

        // 合并单元格
        $activeWorksheet->mergeCells('A1:M1');
        $activeWorksheet->mergeCells('A3:M3');

        // 设置第水平居中
        $activeWorksheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // 设置行高
        $activeWorksheet->getRowDimension('1')->setRowHeight(40);
        $activeWorksheet->getRowDimension('3')->setRowHeight(80);

        // 设置加粗
        $activeWorksheet->getStyle('A1')->getFont()->setBold(true);
        $activeWorksheet->getStyle('A4:M4')->getFont()->setBold(true);

        // 设置背景色
        $activeWorksheet->getStyle('A4:M4')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        $activeWorksheet->getStyle('A4:M4')->getFill()->getStartColor()->setRGB('F5F5F5');

        // 设置每列列宽
        $activeWorksheet->getColumnDimension('A')->setWidth(15);
        $activeWorksheet->getColumnDimension('B')->setWidth(20);
        $activeWorksheet->getColumnDimension('C')->setWidth(20);
        $activeWorksheet->getColumnDimension('D')->setWidth(20);
        $activeWorksheet->getColumnDimension('E')->setWidth(10);
        $activeWorksheet->getColumnDimension('F')->setWidth(10);
        $activeWorksheet->getColumnDimension('G')->setWidth(10);
        $activeWorksheet->getColumnDimension('H')->setWidth(15);
        $activeWorksheet->getColumnDimension('I')->setWidth(15);
        $activeWorksheet->getColumnDimension('J')->setWidth(30);
        $activeWorksheet->getColumnDimension('K')->setWidth(30);
        $activeWorksheet->getColumnDimension('L')->setWidth(30);
        $activeWorksheet->getColumnDimension('M')->setWidth(30);

        //设置当前的sheet
        $activeWorksheet->setCellValue('A1', $name . '（导出时间 ' . date('Y-m-d H:i:s') . '）');
        $activeWorksheet->setCellValue('A2', '申请日期');
        $activeWorksheet->setCellValue('B2', $data['billDate']);
        $activeWorksheet->setCellValue('C2', '交货期限');
        $activeWorksheet->setCellValue('D2', $data['endDate']);
        $activeWorksheet->setCellValue('A3', $data['reqInfo'] . (($data['reqInfo'] && $data['description']) ? "\r\n" : '') . $data['description']);

        $activeWorksheet->setCellValue('A4', '自编序号');
        $activeWorksheet->setCellValue('B4', '产品名称');
        $activeWorksheet->setCellValue('C4', '规格型号');
        $activeWorksheet->setCellValue('D4', '辅助属性');
        $activeWorksheet->setCellValue('E4', '产品品牌');
        $activeWorksheet->setCellValue('F4', '单位');
        $activeWorksheet->setCellValue('G4', '数量');
        $activeWorksheet->setCellValue('H4', '采购单价');
        $activeWorksheet->setCellValue('I4', '采购总价');
        $activeWorksheet->setCellValue('J4', '参考链接');
        $activeWorksheet->setCellValue('K4', '采购链接');
        $activeWorksheet->setCellValue('L4', '备注1');
        $activeWorksheet->setCellValue('M4', '备注2');

        $i = 5;
        foreach ($list as $k => $v) {
            $activeWorksheet->setCellValueExplicit('A' . $i, $v['zid'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $activeWorksheet->setCellValue('B' . $i, $v['name']);
            $activeWorksheet->setCellValue('C' . $i, $v['spec']);
            $activeWorksheet->setCellValue('D' . $i, $v['sku']);
            $activeWorksheet->setCellValue('E' . $i, $v['brand']);
            $activeWorksheet->setCellValue('F' . $i, $v['unit']);
            $activeWorksheet->setCellValue('G' . $i, $v['qty']);
            $activeWorksheet->setCellValue('H' . $i, '');
            $activeWorksheet->setCellValue('I' . $i, '');
            $activeWorksheet->setCellValue('J' . $i, $v['buyUrl']);
            $activeWorksheet->setCellValue('K' . $i, $v['myUrl']);
            $activeWorksheet->setCellValue('L' . $i, $v['description1']);
            $activeWorksheet->setCellValue('M' . $i, $v['description2']);
            $i++;
        }

        $outputFileName = $name . ".xlsx";
        $this->myservice->log('导出了 ' . $name);
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();
        return $this->response
            ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->setHeader('Content-Disposition', "attachment; filename=\"{$outputFileName}\"")
            ->setHeader('Cache-Control', 'max-age=0')
            ->setBody($content);
    }
    // 特采申请 编辑前检查
    public function modifyCheck()
    {
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('bill_contract')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
        if ($data) {
            if ($data['reqId'] != $this->myerp['uId'] && !$this->myservice->verifyRight('SYS_ADMIN')) {
                return $this->response->setJSON(['status' => -1, 'msg' => '无法编辑别人的特采申请单！仅本人或管理员可编辑！']);
            } elseif ($data['isReq'] == 2) {
                return $this->response->setJSON(['status' => -1, 'msg' => '该申请已转正！请立即通知采购部打回转正的特采申请单并暂停采购，然后才可编辑！']);
            } else {
                $res = $this->db->table('bill_contract')->where(['id' => $id, 'isReq' => 1, 'isDelete' => 0])->update(['onModify' => 1]);
                if (!$res) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '更新编辑状态失败！']);
                }
                $data['onModify'] = 1;
                return $this->response->setJSON(['status' => 200, 'msg' => '更新编辑状态成功！', 'data' => $data]);
            }
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 特采申请 解除正在编辑状态
    public function confirmReqModify()
    {
        $id = intval($this->request->getGetPost('id'));
        $billNo = $this->request->getGetPost('billNo');
        $data = $this->db->table('bill_contract')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
        if ($data) {
            if ($data['reqId'] != $this->myerp['uId'] && !$this->myservice->verifyRight('SYS_ADMIN')) {
                return $this->response->setJSON(['status' => -1, 'msg' => '无法解除别人的特采申请单编辑状态！仅本人或管理员可解除！']);
            }
            $this->db->transStart();
            $this->db->table('bill_contract')->where(['id' => $id])->update(['onModify' => 0]);
            $this->myservice->log('解除了特采申请单的正在编辑状态，单号：' . $billNo);
            $this->db->transComplete();
            if ($this->db->transStatus() === FALSE) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '解除成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 特采申请 撤销
    public function cancelReq()
    {
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('bill_contract')->where(['id' => $id])->get()->getRowArray();
        if ($data) {
            if ($data['isDelete'] == 1) {
                return $this->response->setJSON(['status' => -1, 'msg' => '申请已被撤销过！']);
            } elseif ($data['isReq'] == 2) {
                return $this->response->setJSON(['status' => -1, 'msg' => '申请已被转正！请立即通知采购人员取消采购并删除转正的特殊采购单！']);
            } elseif (!$this->myservice->verifyRight('SYS_ADMIN') && $data['uId'] != $this->myerp['uId']) {
                return $this->response->setJSON(['status' => -1, 'msg' => '不能撤销别人的特采申请单！仅本人或管理员可撤销！']);
            }
            $this->db->transStart();
            $this->db->table('bill_contract')->where(['id' => $id])->update(['isDelete' => 1]);
            $this->db->table('bill_contract_info')->where(['iid' => $id])->update(['isDelete' => 1]);
            $oldReqNo = $this->db->table('bill_contract')->select('reqNo')->where('FIND_IN_SET(' . $this->db->escape($data['billNo']) . ', reqNo) >', 0, false)->get()->getRowArray()['reqNo'] ?? '';
            if ($oldReqNo) {
                $oldReqNoArr = explode(',', $oldReqNo);
                $newReqNoArr = array_values(array_diff($oldReqNoArr, [$data['billNo']]));
                $newReqNo = count($newReqNoArr) > 0 ? implode(',', $newReqNoArr) : '';
                $this->db->table('bill_contract')->where('FIND_IN_SET(' . $this->db->escape($data['billNo']) . ', reqNo) >', 0, false)->update(['isReq' => count($newReqNoArr) > 0 ? 3 : 0, 'reqNo' => $newReqNo]);
            }
            $this->myservice->log('撤销了特采申请单！单号：' . $data['billNo']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '撤销成功！', 'data' => ['id' => [$id]]]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 特采申请 更新正在报价状态（主要是将自己的名字加入或移出正在报价人）
    public function upQuoteName()
    {
        if (!$this->myservice->verifyRight('PURREQ_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvPc/upQuoteName 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $type = intval($this->request->getGetPost('type'));
        $uName = $this->myerp['uName'];
        $data = $this->db->table('bill_contract')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
        if ($data) {
            if ($data['isReq'] == 2) {
                return $this->response->setJSON(['status' => -1, 'msg' => '申请已被转正！']);
            }
            $quoteName = '';
            if ($type == 1) {
                if ($data['onModify'] == 1) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '申请正在被编辑中！']);
                }
                $quoteName = $data['quoteName'] == '' ? [] : explode(',', $data['quoteName']);
                $quoteName = implode(',', array_unique(array_merge($quoteName, [$uName])));
            } elseif ($type == 2) {
                $quoteName = $data['quoteName'] == '' ? [] : explode(',', $data['quoteName']);
                $quoteName = implode(',', array_values(array_diff($quoteName, [$uName])));
            }
            $this->db->transStart();
            $this->db->table('bill_contract')->where(['id' => $id])->update(['quoteName' => $quoteName]);
            $this->myservice->log(($type == 1 ? '开始了' : '结束了') . '特采报价！单号：' . $data['billNo']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '操作成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 特采申请 解除我的正在报价状态（主要是将自己移出正在报价人）
    public function confirmReqQuote()
    {
        if (!$this->myservice->verifyRight('PURREQ_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvPc/confirmReqQuote 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $billNo = $this->request->getGetPost('billNo');
        $reqName = $this->request->getGetPost('reqName');
        $uId = intval($this->request->getGetPost('uId'));
        $uName = $this->request->getGetPost('uName');
        $data = $this->db->table('bill_contract')->select('quoteName')->where(['id' => $id])->get()->getRowArray();
        if ($data) {
            if (in_array('SYS_ADMIN', $this->myerp['rights'])) { //管理员直接解除所有人的正在报价状态
                $quoteName = '';
            } else {
                $quoteName = $data['quoteName'] == '' ? [] : explode(',', $data['quoteName']);
                $quoteName = implode(',', array_values(array_diff($quoteName, [$uName])));
            }
            $this->db->transStart();
            $this->db->table('bill_contract')->where(['id' => $id])->update(['quoteName' => $quoteName]);
            $this->myservice->log('解除了 ' . $reqName . ' 发起的特采申请单号：' . $billNo . ' 的正在报价状态。');
            $this->db->transComplete();
            if ($this->db->transStatus() === FALSE) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '操作成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 特采申请 获取我的报价信息
    public function queryQuote()
    {
        if (!$this->myservice->verifyRight('PURREQ_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvPc/queryQuote 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('bill_contract')->where(['id' => $id])->get()->getRowArray();
        if ($data) {
            $data['files'] = $data['files'] == '' ? [] : json_decode($data['files'], true);
            $list = $this->db->table('bill_contract_info AS a')
                ->select(['a.id', 'a.iid', 'a.zid', 'a.name', 'a.spec', 'a.sku', 'a.brand', 'a.unit', 'a.qty', 'a.limitPrice', 'a.price', 'a.amount', 'a.buyUrl', 'a.myUrl', 'a.description1', 'a.description2', 'a.paidAmount', 'b.price AS price_quote', 'b.amount AS amount_quote', 'b.myUrl AS myUrl_quote', 'b.description1 AS description1_quote', 'b.description2 AS description2_quote', 'b.isHasExpress AS isHasExpress_quote', 'b.isHasInvoice AS isHasInvoice_quote', 'b.uId AS uId_quote', 'b.uName AS uName_quote', 'b.quoteLevel'])
                ->join('bill_contract_info_quote AS b', 'a.iid = b.iid AND a.zid = b.zid AND b.uId = ' . $this->myerp['uId'] . ' AND b.isDelete = 0', 'left')
                ->where('a.iid', $id)
                ->orderBy('CAST(a.zid AS UNSIGNED) ASC, a.id ASC')
                ->get()
                ->getResultArray();
            $v = [];
            foreach ($list as $arr => $row) {
                $v[$arr]['id'] = intval($row['id']);
                $v[$arr]['iid'] = intval($row['iid']);
                $v[$arr]['zid'] = $row['zid'];
                $v[$arr]['name'] = $row['name'];
                $v[$arr]['spec'] = $row['spec'];
                $v[$arr]['sku'] = $row['sku'];
                $v[$arr]['brand'] = $row['brand'];
                $v[$arr]['unit'] = $row['unit'];
                $v[$arr]['qty'] = (float)$row['qty'];
                $v[$arr]['limitPrice'] = (float)$row['limitPrice'];
                $v[$arr]['price'] = $row['price_quote'] ?? 0;
                $v[$arr]['amount'] = $row['amount_quote'] ?? 0;
                $v[$arr]['buyUrl'] = $row['buyUrl'];
                $v[$arr]['myUrl'] = $row['myUrl_quote'];
                $v[$arr]['description1'] = $row['description1_quote'] ?? $row['description1'];
                $v[$arr]['description2'] = $row['description2_quote'] ?? $row['description2'];
                $v[$arr]['isHasExpress'] = intval($row['isHasExpress_quote']);
                $v[$arr]['isHasInvoice'] = intval($row['isHasInvoice_quote']);
                $v[$arr]['quoteLevel'] = intval($row['quoteLevel']);
                $v[$arr]['paidAmount'] = $row['paidAmount'] ?? 0;
            }
            $zids = array_column($this->db->table('bill_contract_info')->select('zid')->where(['iid' => $id, 'isDelete' => 0])->get()->getResultArray(), 'zid');
            $quoteZids = array_column($this->db->table('bill_contract_info_quote')->select('zid')->where(['iid' => $id, 'isDelete' => 0])->get()->getResultArray(), 'zid');
            $nonQuoteZids = array_values(array_diff($zids, $quoteZids));

            $info['data'] = $data;
            $info['entries'] = $v;
            $info['zids'] = $zids;
            $info['quoteZids'] = $quoteZids;
            $info['nonQuoteZids'] = $nonQuoteZids;

            $json['status'] = 200;
            $json['msg'] = 'success';
            $json['data'] = $info;
            return $this->response->setJSON($json);

        }
        return $this->response->setJSON(['status' => -1, 'msg' => '申请不存在或已被转正！']);
    }
    // 特采申请 提交我的报价信息
    public function saveQuote()
    {
        if (!$this->myservice->verifyRight('PURREQ_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvPc/saveQuote 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost('postData');
        if (strlen($data) > 0) {
            $data = (array)json_decode($data, true);
            $reqData = $this->db->table('bill_contract')->where(['id' => $data['iid'], 'isReq' => 1, 'isDelete' => 0])->get()->getRowArray();
            if ($reqData) {
                if ($reqData['onModify'] == 1) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '申请正在被编辑！强行暂停报价！']);
                }
            } else {
                return $this->response->setJSON(['status' => -1, 'msg' => '申请已被撤销或转正！强行停止报价！']);
            }
            // 处理容易出问题的特殊字符
            $data['zid'] = str_replace(["\r", "\n", "\r\n", '{', '}', '"', "'"], '”', $data['zid']);
            $data['name'] = str_replace(["\r", "\n", "\r\n", '{', '}', '"', "'"], '”', $data['name']);
            $data['spec'] = str_replace(["\r", "\n", "\r\n", '{', '}', '"', "'"], '”', $data['spec']);
            $data['sku'] = str_replace(["\r", "\n", "\r\n", '{', '}', '"', "'"], '”', $data['sku']);
            $data['brand'] = str_replace(["\r", "\n", "\r\n", '{', '}', '"', "'"], '”', $data['brand']);
            $data['unit'] = str_replace(["\r", "\n", "\r\n", '{', '}', '"', "'"], '”', $data['unit']);
            $data['myUrl'] = shop_url_parse($data['myUrl']);
            $data['description1'] = str_replace(["\r", "\n", "\r\n", '{', '}', '"', "'"], '”', $data['description1']);
            $data['description2'] = str_replace(["\r", "\n", "\r\n", '{', '}', '"', "'"], '”', $data['description2']);
            $data['price'] = round($data['price'], 5);
            $data['amount'] = round($data['amount'], 2);

            $this->db->transStart();
            // 有则更新 无则插入
            if ($this->db->table('bill_contract_info_quote')->where(['iid' => $data['iid'], 'zid' => $data['zid'], 'uId' => $data['uId']])->countAllResults() > 0) {
                $data['lastTime'] = date('Y-m-d H:i:s');
                $this->db->table('bill_contract_info_quote')->where(['iid' => $data['iid'], 'zid' => $data['zid'], 'uId' => $data['uId']])->update($data);
            } else {
                $data['billNo'] = $reqData['billNo'];
                $data['firstTime'] = $data['lastTime'] = date('Y-m-d H:i:s');
                $data['transStatus'] = 0;
                $this->db->table('bill_contract_info_quote')->insert($data);
            }
            $quoteNum = count($this->db->table('bill_contract_info_quote')->select('uId')->where(['iid' => $data['iid'], 'isDelete' => 0])->groupBy('uId')->get()->getResultArray());
            $this->db->table('bill_contract')->where(['id' => $data['iid']])->update(['quoteNum' => $quoteNum]);
            $this->myservice->log('对特采申请单号为 ' . $reqData['billNo'] . ' 中自编序号为 ' . $data['zid'] . ' 的产品进行了报价，所报单价：' . $data['price'] . '，所报总价：' . $data['amount'] . '，所报链接：' . $data['myUrl']);

            $zids = array_column($this->db->table('bill_contract_info')->select('zid')->where(['iid' => $data['iid'], 'isDelete' => 0])->get()->getResultArray(), 'zid');
            $quoteZids = array_column($this->db->table('bill_contract_info_quote')->select('zid')->where(['iid' => $data['iid'], 'isDelete' => 0])->get()->getResultArray(), 'zid');
            $nonQuoteZids = array_values(array_diff($zids, $quoteZids));

            $limitPrice = $this->db->table('bill_contract_info')->select('limitPrice')->where(['iid' => $data['iid'], 'zid' => $data['zid'], 'isDelete' => 0])->get()->getRowArray()['limitPrice'] ?? 0;
            $minPrice = $this->db->table('bill_contract_info_quote')->selectMin('price', 'minPrice')->where(['iid' => $data['iid'], 'zid' => $data['zid'], 'uId !=' => $data['uId'], 'price >' => 0, 'isDelete' => 0])->get()->getRowArray()['minPrice'] ?? 0;

            $status = 200;
            $msg = 'success';
            $quoteLevel = 0;

            if ($limitPrice > 0 && $data['price'] < ($limitPrice * 0.1)) {
                // 限价大于0且我的报价小于限价的10%，优势等级1(低)并提醒
                $status = -2;
                $msg = '自编序号为 ' . $data['zid'] . ' 的产品报价可能过低，推荐与卖家核实！';
                $quoteLevel = 1;
            } elseif (
                ($limitPrice > 0 && $data['price'] > $limitPrice) ||
                ($limitPrice > 0 && $minPrice > 0 && $minPrice > ($limitPrice * 0.5) && $minPrice <= ($data['price'] / 1.5))
            ) {
                // 限价大于0且我的报价大于限价，或限价大于0且已有其他报价且所有报价人最低价大于限价的50%且所有报价人最低价小于等于我的报价的2/3，优势等级1(低)并提醒
                $status = -3;
                $msg = '自编序号为 ' . $data['zid'] . ' 的产品报价可能过高，推荐继续询价！';
                $quoteLevel = 1;
            } elseif (
                ($limitPrice > 0 && $minPrice == 0 && $data['price'] >= ($limitPrice * 0.1) && $data['price'] <= $limitPrice) ||
                ($limitPrice > 0 && $minPrice > 0 && $data['price'] < $minPrice)
            ) {
                // 限价大于0且无其他人报价且我的报价介于限价的10%~100%之间，或限价大于0且已有其他人报价且我的报价小于所有其他人报价，优势等级3(高)不提醒
                $quoteLevel = 3;
            } else {
                // 否则就是优势等级2(中)不提醒
                $quoteLevel = 2;
            }
            $this->db->table('bill_contract_info_quote')->where(['iid' => $data['iid'], 'zid' => $data['zid'], 'uId' => $data['uId']])->update(['quoteLevel' => $quoteLevel]);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚']);
            }
            return $this->response->setJSON(['status' => $status, 'msg' => $msg, 'data' => ['quoteLevel' => $quoteLevel, 'myUrl' => $data['myUrl'], 'zids' => $zids, 'quoteZids' => $quoteZids, 'nonQuoteZids' => $nonQuoteZids]]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    // 特采申请 提取最低价并转正
    public function transReq()
    {
        if (!$this->myservice->verifyRight('PC_TRANS')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvPc/transReq 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $type = intval($this->request->getGetPost('type'));
        $id = intval($this->request->getGetPost('id'));
        $billNo = $this->request->getGetPost('billNo');
        $uId = intval($this->request->getGetPost('uId'));
        $uName = $this->request->getGetPost('uName');
        $data = $this->db->table('bill_contract')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
        if ($data) {
            if ($data['isReq'] == 2) {
                return $this->response->setJSON(['status' => -1, 'msg' => '申请已被转正！']);
            }
            if ($data['quoteNum'] == 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '尚无人报价！']);
            }
            if ($data['quoteName'] != '') {
                return $this->response->setJSON(['status' => -1, 'msg' => '有其他人正在报价！']);
            }
            if ($data['onModify'] == 1) {
                return $this->response->setJSON(['status' => -1, 'msg' => '申请正在被编辑中！']);
            }
            if ($type == 0) {
                $err_msg = '';
                if ($this->db->table('bill_contract')->where(['id' => $id, 'isReq' => 1, 'quoteNum' => 1, 'isDelete' => 0])->countAllResults() > 0) {
                    $err_msg .= '仅有1份报价！';
                }
                $reqZids = array_column($this->db->table('bill_contract_info')->select('zid')->where(['iid' => $id, 'isDelete' => 0])->get()->getResultArray(), 'zid');
                $quoteZids = array_column($this->db->table('bill_contract_info_quote')->select('zid')->where(['iid' => $id, 'price >' => 0, 'isDelete' => 0])->get()->getResultArray(), 'zid');
                $noneZids = [];
                if (count($quoteZids) > 0) {
                    $noneZids = array_diff($reqZids, $quoteZids);
                }
                count($noneZids) > 0 && $err_msg .= '自编序号【 ' . implode('、', $noneZids) . ' 】产品尚未报价！';
                if (strlen($err_msg) > 0) {
                    return $this->response->setJSON(['status' => -2, 'msg' => $err_msg]);
                }
            }
            if ($type == 1) {
                $this->db->transStart();
                $this->db->table('bill_contract')->where(['id' => $id])->update(['forceTransStatus' => 1, 'forceTransName' => $uName]);
                $this->myservice->log('发起了特采申请单强制转正，特采单号：' . $billNo . '，特采申请人：' . $data['reqName'] . '。等待采购部负责人同意强制转正。');
                $this->db->transComplete();
                if ($this->db->transStatus() === false) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
                }
                return $this->response->setJSON(['status' => 200, 'msg' => 'success']);
            }
            // 子查询：按 zid 分组，价格升序 → 时间升序 → id 升序 编号
            $sub = $this->db->table('bill_contract_info_quote')
                ->select([
                    'id', 'iid', 'zid', 'price', 'amount', 'myUrl',
                    'description1', 'description2', 'isHasExpress',
                    'isHasInvoice', 'uId', 'uName',
                ])
                ->select(new RawSql(
                    'ROW_NUMBER() OVER (PARTITION BY zid ORDER BY price ASC, lastTime ASC, id ASC) AS rn'
                ))
                ->where('iid', $id)
                ->where('price >', 0);

            // 外层：只取每组第 1 条
            $quoteInfos = $this->db->newQuery()
                ->select([
                    't.id', 't.iid', 't.zid', 't.price', 't.amount', 't.myUrl',
                    't.description1', 't.description2', 't.isHasExpress',
                    't.isHasInvoice', 't.uId', 't.uName',
                ])
                ->fromSubquery($sub, 't')
                ->where('t.rn', 1)
                ->get()
                ->getResultArray();
            if (!empty($quoteInfos)) {
                $this->db->transStart();
                foreach ($quoteInfos as $k => $v) {
                    $oldInfo = $this->db->table('bill_contract_info')->where(['iid' => $v['iid'], 'zid' => $v['zid']])->get()->getRowArray();
                    if ($oldInfo) {
                        if ((float)$oldInfo['paidAmount'] == 0) { // 未付过款的，才改变信息
                            $this->db->table('bill_contract_info')->where([
                                'iid' => $v['iid'],
                                'zid' => $v['zid']
                            ])->update([
                                'isReq' => 2,
                                'price' => $v['price'],
                                'quotePrice' => $v['price'],
                                'amount' => $v['amount'],
                                'myUrl' => $v['myUrl'],
                                'description1' => $v['description1'],
                                'description2' => $v['description2'],
                                'isHasExpress' => $v['isHasExpress'],
                                'isHasInvoice' => $v['isHasInvoice'],
                                'uId' => $v['uId'],
                                'uName' => $v['uName']
                            ]);
                            // 将购买人信息同步到特殊销售单
                            $this->db->table('bill_contract_info')->where([
                                'billNo' => $data['reqNo'],
                                'zid' => $v['zid'],
                                'isDelete' => 0
                            ])->update([
                                'purId' => $v['uId'],
                                'purName' => $v['uName']
                            ]);
                        } else { // 已付过款
                            if ($oldInfo['uId'] == $v['uId']) { // 最低价仍是同一人，也只更新其他信息不更新价格
                                $this->db->table('bill_contract_info')->where([
                                    'iid' => $v['iid'],
                                    'zid' => $v['zid']
                                ])->update([
                                    'isReq' => 2,
                                    'quotePrice' => $v['price'],
                                    'myUrl' => $v['myUrl'],
                                    'description1' => $v['description1'],
                                    'description2' => $v['description2'],
                                    'isHasExpress' => $v['isHasExpress'],
                                    'isHasInvoice' => $v['isHasInvoice']
                                ]);
                            }
                        }
                    }
                }
                // 将未报价的产品条目的采购人置空（因为默认是申请人的uId和uName）
                $this->db->table('bill_contract_info')->where(['iid' => $id, 'quotePrice' => 0])->update(['isReq' => 2, 'uId' => 0, 'uName' => ""]);
                // 更新主单信息
                $amount = $this->db->table('bill_contract_info')->selectSum('amount')->where(['iid' => $id, 'isDelete' => 0])->get()->getRowArray()['amount'] ?? 0;
                $quotedNameArr = array_column($this->db->table('bill_contract_info')->select('uName')->where(['iid' => $id, 'isDelete' => 0])->get()->getResultArray(), 'uName');
                $quotedName = count($quotedNameArr) > 0 ? implode(',', array_unique($quotedNameArr)) : '';
                $this->db->table('bill_contract')->where(['id' => $id])->update(['amount' => $amount, 'quotedName' => $quotedName, 'checkId' => 0, 'checkName' => '', 'forceTransStatus' => 0, 'isReq' => 2, 'createTime' => date("Y-m-d H:i:s")]);
                // 更新报价信息转化状态
                $this->db->table('bill_contract_info_quote')->where(['iid' => $id])->update(['transStatus' => 1]);
                $this->myservice->log('转正了特采申请单，特采单号：' . $billNo . '，特采申请人：' . $data['reqName']);
                $this->db->transComplete();
                if ($this->db->transStatus() === false) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
                }
                return $this->response->setJSON(['status' => 200, 'msg' => 'success']);
            }
            return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 特采申请 同意强制转正
    public function allowTransReq()
    {
        if (!$this->myservice->verifyRight('PUR_MANAGER')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvPc/allowTransReq 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $billNo = $this->request->getGetPost('billNo');
        $uId = intval($this->request->getGetPost('uId'));
        $uName = $this->request->getGetPost('uName');
        $data = $this->db->table('bill_contract')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
        if ($data) {
            // 子查询：按 zid 分组，价格升序 → 时间升序 → id 升序 编号
            $sub = $this->db->table('bill_contract_info_quote')
                ->select([
                    'id', 'iid', 'zid', 'price', 'amount', 'myUrl',
                    'description1', 'description2', 'isHasExpress',
                    'isHasInvoice', 'uId', 'uName',
                ])
                ->select(new RawSql(
                    'ROW_NUMBER() OVER (PARTITION BY zid ORDER BY price ASC, lastTime ASC, id ASC) AS rn'
                ))
                ->where('iid', $id)
                ->where('price >', 0);

            // 外层：只取每组第 1 条
            $quoteInfos = $this->db->newQuery()
                ->select([
                    't.id', 't.iid', 't.zid', 't.price', 't.amount', 't.myUrl',
                    't.description1', 't.description2', 't.isHasExpress',
                    't.isHasInvoice', 't.uId', 't.uName',
                ])
                ->fromSubquery($sub, 't')
                ->where('t.rn', 1)
                ->get()
                ->getResultArray();
            if (!empty($quoteInfos)) {
                $this->db->transStart();
                foreach ($quoteInfos as $k => $v) {
                    $oldInfo = $this->db->table('bill_contract_info')->where(['iid' => $v['iid'], 'zid' => $v['zid']])->get()->getRowArray();
                    if ($oldInfo) {
                        if ((float)$oldInfo['paidAmount'] == 0) { // 未付过款的，直接更新信息
                            $this->db->table('bill_contract_info')->where([
                                'iid' => $v['iid'],
                                'zid' => $v['zid']
                            ])->update([
                                'isReq' => 2,
                                'price' => $v['price'],
                                'quotePrice' => $v['price'],
                                'amount' => $v['amount'],
                                'myUrl' => $v['myUrl'],
                                'description1' => $v['description1'],
                                'description2' => $v['description2'],
                                'isHasExpress' => $v['isHasExpress'],
                                'isHasInvoice' => $v['isHasInvoice'],
                                'uId' => $v['uId'],
                                'uName' => $v['uName']
                            ], [
                            ]);
                            // 将购买人信息同步到特殊销售单
                            $this->db->table('bill_contract_info')->where([
                                'billNo' => $data['reqNo'],
                                'zid' => $v['zid'],
                                'isDelete' => 0
                            ])->update([
                                'purId' => $v['uId'],
                                'purName' => $v['uName']
                            ]);
                        } else { // 已付过款的
                            if ($oldInfo['uId'] == $v['uId']) { // 最低价仍是同一人，也只更新其他信息不更新价格
                                $this->db->table('bill_contract_info')->where([
                                    'iid' => $v['iid'],
                                    'zid' => $v['zid']
                                ])->update([
                                    'isReq' => 2,
                                    'quotePrice' => $v['price'],
                                    'myUrl' => $v['myUrl'],
                                    'description1' => $v['description1'],
                                    'description2' => $v['description2'],
                                    'isHasExpress' => $v['isHasExpress'],
                                    'isHasInvoice' => $v['isHasInvoice']
                                ]);
                            }
                        }
                    }
                }
                // 将未报价的产品条目的采购人置空
                $this->db->table('bill_contract_info')->where(['iid' => $id, 'quotePrice' => 0])->update(['isReq' => 2, 'uId' => 0, 'uName' => ""]);
                // 更新主单信息
                $amount = $this->db->table('bill_contract_info')->select('SUM(amount) as amount')->where(['iid' => $id, 'isDelete' => 0])->get()->getRowArray()['amount'];
                $quotedNameArr = array_column($this->db->table('bill_contract_info')->select('uName')->where(['iid' => $id, 'isDelete' => 0])->get()->getResultArray(), 'uName');
                $quotedName = count($quotedNameArr) > 0 ? implode(',', array_unique($quotedNameArr)) : '';
                $this->db->table('bill_contract')->where(['id' => $id])->update(['amount' => $amount, 'quotedName' => $quotedName, 'checkId' => $uId, 'checkName' => $uName, 'forceTransStatus' => 0, 'isReq' => 2, 'createTime' => date("Y-m-d H:i:s")]);
                // 更新报价信息转化状态
                $this->db->table('bill_contract_info_quote')->where(['iid' => $id])->update(['transStatus' => 1]);
                $this->myservice->log('同意了 ' . $data['forceTransName'] . ' 发起的特采申请单强制转正，特采单号：' . $billNo . '，特采申请人：' . $data['reqName']);
                $this->db->transComplete();
                if ($this->db->transStatus() === false) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
                }
                return $this->response->setJSON(['status' => 200, 'msg' => 'success']);
            }
            return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 特采申请 报价记录 获取报价人
    public function getQuoteUser()
    {
        if (!$this->myservice->verifyRight('PURREQ_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvPc/getQuoteUser 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $builder = $this->db->table('bill_contract_info_quote');
        $builder->where('isDelete', 0);
        if ($id > 0) {
            $builder->where('iid', $id);
        }
        $data = $builder->select('uId id, uName name')->orderBy('id', 'ASC')->groupBy('uId')->get()->getResultArray();
        return $this->response->setJSON(['status' => 200, 'msg' => 'success', 'data' => $data]);
    }
    // 特采申请 报价记录 获取报价记录
    public function getQuoteInfo()
    {
        if (!$this->myservice->verifyRight('PURREQ_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvPc/getQuoteInfo 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $id = intval($this->request->getGetPost('id'));
        $uId = intval($this->request->getGetPost('uId'));
        $skey = $this->request->getGetPost('skey');
        $builder = $this->db->table('bill_contract_info_quote');
        $builder->where(['iid' => $id, 'price >' => 0, 'isDelete' => 0]);
        if ($uId > 0) {
            $builder->where('uId', $uId);
        }
        if ($skey !== '' && $skey !== null) {
            $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
            foreach ($keywords as $kw) {
                $builder->groupStart()
                    ->like('name', $this->db->escapeLikeString($kw))
                    ->orLike('spec', $this->db->escapeLikeString($kw))
                    ->orLike('sku', $this->db->escapeLikeString($kw))
                    ->orLike('description1', $this->db->escapeLikeString($kw))
                    ->orLike('description2', $this->db->escapeLikeString($kw))
                    ->orLike('billNo', $this->db->escapeLikeString($kw))
                ->groupEnd();
            }
        }
        $list = (clone $builder)->select(['id','iid','billNo','zid','CAST(REGEXP_REPLACE(zid, "[^0-9]", "") AS UNSIGNED) AS zid_num','name','spec','sku','brand','unit','qty','price','amount','myUrl','description1','description2','uId','uName','firstTime','lastTime'])
        ->orderBy('zid_num ASC, id ASC')
        ->limit($rows)
        ->offset($rows * ($page - 1))
        ->get()
        ->getResultArray();
        $total = $builder->countAllResults();
        $limitPriceList = array_column($this->db->table('bill_contract_info')->select('zid,limitPrice,buyUrl')->where(['iid' => $id, 'isDelete' => 0])->get()->getResultArray(), null, 'zid');
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['iid'] = intval($row['iid']);
            $v[$arr]['billNo'] = $row['billNo'];
            $v[$arr]['zid'] = $row['zid'];
            $v[$arr]['zid_num'] = $row['zid_num'];
            $v[$arr]['name'] = $row['name'];
            $v[$arr]['spec'] = $row['spec'];
            $v[$arr]['sku'] = $row['sku'];
            $v[$arr]['brand'] = $row['brand'];
            $v[$arr]['unit'] = $row['unit'];
            $v[$arr]['qty'] = (float)$row['qty'];
            $v[$arr]['limitPrice'] = $limitPriceList[$row['zid']]['limitPrice'] ?? 0;
            $v[$arr]['price'] = (float)$row['price'];
            $v[$arr]['amount'] =  (float)$row['amount'];
            $v[$arr]['buyUrl'] = $limitPriceList[$row['zid']]['buyUrl'] ?? '';
            $v[$arr]['myUrl'] = $row['myUrl'];
            $v[$arr]['description1'] = $row['description1'];
            $v[$arr]['description2'] = $row['description2'];
            $v[$arr]['uId'] = intval($row['uId']);
            $v[$arr]['uName'] = $row['uName'];
            $v[$arr]['firstTime'] = $row['firstTime'] == '0000-00-00 00:00:00' ? '' : $row['firstTime'];
            $v[$arr]['lastTime'] = $row['lastTime'] == '0000-00-00 00:00:00' ? '' : $row['lastTime'];
        }
        $zids = array_column($this->db->table('bill_contract_info')->select('zid')->where(['iid' => $id, 'isDelete' => 0])->get()->getResultArray(), 'zid');
        $quoteZids = array_column($this->db->table('bill_contract_info_quote')->select('zid')->where(['iid' => $id, 'isDelete' => 0])->groupBy('zid')->get()->getResultArray(), 'zid');
        $nonQuoteZids = array_values(array_diff($zids, $quoteZids));
        $info['zids'] = $zids;
        $info['quoteZids'] = $quoteZids;
        $info['nonQuoteZids'] = $nonQuoteZids;
        $info['page'] = $page;
        $info['records'] = $total;
        $info['total'] = ceil($info['records'] / $rows);
        $info['rows'] = $v;

        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data'] = $info;
        return $this->response->setJSON($json);
    }
    // 特采申请 转正单 获取被采纳报价人
    public function getAcceptedUser()
    {
        if (!$this->myservice->verifyRight('PURREQ_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvPc/getAcceptedUser 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('bill_contract_info')->select('uId AS id, uName AS name')->where(['iid' => $id, 'isDelete' => 0])->groupBy('uId')->orderBy('id', 'ASC')->get()->getResultArray();
        return $this->response->setJSON(['status' => 200, 'msg' => 'success', 'data' => $data]);
    }
    // 特采申请 转正单 获取被采纳报价人的产品条目
    public function getAcceptedInfo()
    {
        if (!$this->myservice->verifyRight('PURREQ_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvPc/getAcceptedInfo 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 10000);
        $id = intval($this->request->getGetPost('id'));
        $uId = intval($this->request->getGetPost('uId'));
        $builder = $this->db->table('bill_contract_info');
        $builder->where(['iid' => $id, 'isDelete' => 0]);
        if ($uId > 0) {
            $builder->where('uId', $uId);
        }
        $list = (clone $builder)->orderBy('CAST(zid AS UNSIGNED) ASC, id ASC')->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['iid'] = intval($row['iid']);
            $v[$arr]['zid'] = $row['zid'];
            $v[$arr]['isReq'] = intval($row['isReq']);
            $v[$arr]['no'] = $row['no'];
            $v[$arr]['name'] = $row['name'];
            $v[$arr]['spec'] = $row['spec'];
            $v[$arr]['sku'] = $row['sku'];
            $v[$arr]['brand'] = $row['brand'];
            $v[$arr]['unit'] = $row['unit'];
            $v[$arr]['qty'] = (float)$row['qty'];
            $v[$arr]['hasRecQty'] = intval($row['hasRecQty']);
            $v[$arr]['limitPrice'] = (float)$row['limitPrice'];
            $v[$arr]['price'] = (float)$row['price'];
            $v[$arr]['amount'] = (float)$row['amount'];
            $v[$arr]['buyUrl'] = $row['buyUrl'];
            $v[$arr]['myUrl'] = $row['myUrl'];
            $v[$arr]['description1'] = $row['description1'];
            $v[$arr]['description2'] = $row['description2'];
            $v[$arr]['isPurchased'] = intval($row['isPurchased']);
            $v[$arr]['noPurchaseReason'] = $row['noPurchaseReason'];
            $v[$arr]['isRecInvoice'] = intval($row['isRecInvoice']);
            $v[$arr]['isIgnoreInvoice'] = intval($row['isIgnoreInvoice']);
            $v[$arr]['ignoreInvoiceReason'] = $row['ignoreInvoiceReason'];
            $v[$arr]['uId'] = intval($row['uId']);
            $v[$arr]['uName'] = $row['uName'];
            $v[$arr]['isHasExpress'] = intval($row['isHasExpress']);
            $v[$arr]['isHasInvoice'] = intval($row['isHasInvoice']);
            $v[$arr]['purFeedback'] = $row['purFeedback'];
            $v[$arr]['purSuggestId'] = intval($row['purSuggestId']);
            $v[$arr]['quotePrice'] = (float)$row['quotePrice'];
            $v[$arr]['paidAmount'] = $row['paidAmount'];
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['page'] = $page;
        $json['data']['records'] = $total;
        $json['data']['total'] = ceil($json['data']['records'] / $rows);
        $json['data']['rows'] = $v;
        return $this->response->setJSON($json);
    }
    // 特采申请 转正单 打回
    public function reTrans()
    {
        if (!$this->myservice->verifyRight('PC_TRANS')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvPc/reTrans 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('bill_contract')->where(['id' => $id])->get()->getRowArray();
        if ($data) {
            $this->db->transStart();
            $this->db->table('bill_contract')->where(['id' => $id])->update(['isReq' => 1]);
            $this->db->table('bill_contract_info')->where(['iid' => $id])->update(['isReq' => 1]);
            $this->db->table('bill_contract_info_quote')->where(['iid' => $id])->update(['transStatus' => 0]);
            $this->myservice->log('打回了已转正的特采申请单，单号：' . $data['billNo']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '操作成功！', 'data' => ['id' => [$id]]]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
}
