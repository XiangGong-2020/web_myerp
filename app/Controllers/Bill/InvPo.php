<?php

namespace App\Controllers\Bill;
use App\Controllers\BaseController;

class InvPo extends BaseController
{
    // 列表
    public function list()
    {
        if (!$this->myservice->verifyRight('PO_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvPo/list 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max((int)$this->request->getGetPost('page'), 1);
        $rows = max((int)$this->request->getGetPost('rows'), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $status1 = intval($this->request->getGetPost('status1'));
        $status2 = intval($this->request->getGetPost('status2'));
        $status3 = intval($this->request->getGetPost('status3'));
        $status4 = intval($this->request->getGetPost('status4'));
        $status5 = intval($this->request->getGetPost('status5'));
        $status6 = intval($this->request->getGetPost('status6'));
        $status7 = intval($this->request->getGetPost('status7'));
        $status8 = intval($this->request->getGetPost('status8'));
        $status9 = intval($this->request->getGetPost('status9'));
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');
        $minAmount = (float)$this->request->getGetPost('minAmount');
        $maxAmount = (float)$this->request->getGetPost('maxAmount');
        $advanced = intval($this->request->getGetPost('advanced'));
        $skey = $this->request->getGetPost('skey');
        $unDone = intval($this->request->getGetPost('unDone'));
        $builder = $this->db->table('bill_order');
        $builder->where('billType', 'PUR');
        if ($status1 >= 0) {
            $builder->where('uId', $status1);
        }
        if ($status2 >= 0) {
            $builder->where('transType', $status2);
        }
        if ($status3 >= 0) {
            if ($status3 == 0) {
                $builder->where('checkId', 0);
            } elseif ($status3 == 1) {
                $builder->where('checkId >', 0);
            } else {
                $builder->where('isCancel', 1);
            }
        }
        if ($status4 >= 0) {
            $builder->where('moneyStatus1', $status4);
        }
        if ($status5 >= 0) {
            $builder->where('moneyStatus2', $status5);
        }
        if ($status6 >= 0) {
            $builder->where('goodsStatus1', $status6);
        }
        if ($status7 >= 0) {
            $builder->where('goodsStatus2', $status7);
        }
        if ($status8 >= 0) {
            if ($status8 == 0) {
                $builder->where('invoiceNum', 0);
            } else {
                $builder->where('invoiceNum >', 0);
            }
        }
        if ($status7 >= 0) {
            $builder->where('goodsStatus2', $status7);
        }
        if ($beginDate) {
            $builder->where('billDate >= ', $beginDate);
        }
        if ($endDate) {
            $builder->where('billDate <= ', $endDate);
        }
        if ($minAmount > 0) {
            $builder->where('amount >=', $minAmount);
        }
        if ($maxAmount > 0) {
            $builder->where('amount <=', $maxAmount);
        }
        if ($unDone > 0) {
            $builder->where(['goodsStatus1 <' => 2, 'isCancel' => 0, 'isDraft' => 0]);
        }
        if ($status9 < 0) { //匹配任意信息
            if ($skey !== '' && $skey !== null) {
                $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
                foreach ($keywords as $kw) {
                    $builder->groupStart()
                        ->where('billNo', $kw)
                        ->orWhere('srcInvNo1', $kw)
                        ->orWhere('srcInvNo2', $kw)
                        ->orLike('description', $this->db->escapeLikeString($kw))
                        ->orLike('goods', $this->db->escapeLikeString($kw))
                        ->orWhere('uName', $kw)
                        ->orWhere('checkName', $kw)
                        ->orLike('contactName', $this->db->escapeLikeString($kw))
                        ->orWhere('sName', $kw)
                    ->groupEnd();
                }
            }
        } elseif ($status9 == 0) { //匹配包含产品
            if ($skey !== '' && $skey !== null) {
                $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
                foreach ($keywords as $kw) {
                    $builder->like('goods', $this->db->escapeLikeString($kw));
                }
            }
        } elseif ($status9 == 1) { //匹配包含产品编码
            if ($skey !== '' && $skey !== null) {
                $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
                foreach ($keywords as $kw) {
                    $builder->like('goods', $this->db->escapeLikeString('"no":"'.$kw.'"'));
                }
            }
        }
        $builder->where('isDelete', 0);
        // 允许排序的字段白名单，杜绝 order by 注入
        $allowedSort = ['billNo','billDate','contactName','uName','checkId','moneyStatus1','moneyStatus2','goodsStatus1','goodsStatus2','invoiceNum','srcPurReqId'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $list = (clone $builder)->orderBy($orderField, $sord)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $total = (clone $builder)->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['billDate'] = $row['billDate'];
            $v[$arr]['deliveryDate'] = $row['deliveryDate'];
            $v[$arr]['billNo'] = $row['billNo'];
            $v[$arr]['transType'] = intval($row['transType']);
            $v[$arr]['totalQty'] = abs(intval($row['totalQty']));
            $v[$arr]['amount'] = abs((float)$row['amount']);
            $v[$arr]['uId'] = intval($row['uId']);
            $v[$arr]['uName'] = $row['uName'];
            $v[$arr]['checkId'] = intval($row['checkId']);
            $v[$arr]['checkName'] = $row['checkName'];
            $v[$arr]['contactId'] = intval($row['contactId']);
            $v[$arr]['contactName'] = $row['contactName'];
            $v[$arr]['sId'] = intval($row['sId']);
            $v[$arr]['sName'] = $row['sName'];
            $v[$arr]['moneyStatus1'] = intval($row['moneyStatus1']);
            $v[$arr]['moneyStatus2'] = intval($row['moneyStatus2']);
            $v[$arr]['goodsStatus1'] = intval($row['goodsStatus1']);
            $v[$arr]['goodsStatus2'] = intval($row['goodsStatus2']);
            $v[$arr]['srcMoneyId1'] = intval($row['srcMoneyId1']);
            $v[$arr]['srcMoneyNo1'] = $row['srcMoneyNo1'];
            $v[$arr]['srcMoneyId2'] = intval($row['srcMoneyId2']);
            $v[$arr]['srcMoneyNo2'] = $row['srcMoneyNo2'];
            $v[$arr]['srcInvId1'] = intval($row['srcInvId1']);
            $v[$arr]['srcInvNo1'] = $row['srcInvNo1'];
            $v[$arr]['srcInvId2'] = intval($row['srcInvId2']);
            $v[$arr]['srcInvNo2'] = $row['srcInvNo2'];
            $v[$arr]['srcPurReqId'] = intval($row['srcPurReqId']);
            $v[$arr]['invoiceNum'] = intval($row['invoiceNum']);
            $v[$arr]['invoiceAmount'] = $row['invoiceAmount'];
            $v[$arr]['isIgnoreInvoice'] = intval($row['isIgnoreInvoice']);
            $v[$arr]['ignoreInvoiceReason'] = $row['ignoreInvoiceReason'];
            $v[$arr]['disagreeIgnoreReason'] = $row['disagreeIgnoreReason'];
            $v[$arr]['files'] = $row['files'];
            $v[$arr]['description'] = $row['description'];
            $v[$arr]['goods'] = (array)json_decode($row['goods'], true);
            $v[$arr]['isOnlyPCB'] = intval($row['isOnlyPCB']);
            $v[$arr]['isDraft'] = intval($row['isDraft']);
            $v[$arr]['isCancel'] = intval($row['isCancel']);
        }
        // 款项汇总
        $totalNotClearedAmount = floatval((clone $builder)->selectSum('amount', 'totalNotClearedAmount')->where('moneyStatus1', 0)->get()->getRowArray()['totalNotClearedAmount'] ?? 0);
        $partIdsArr = [];
        $partIds = (clone $builder)->select('srcMoneyId1')->where('moneyStatus1', 1)->get()->getResultArray();
        if (count($partIds) > 0) {
            foreach($partIds as $arr2 => $row2) {
                array_push($partIdsArr, $row2['srcMoneyId1']);
                if (count($partIdsArr) == 1000) { // 分片操作，每1000处理一次
                    $totalNotClearedAmount_part = $this->db->table('bill_money')->selectSum('amount')->selectSum('cleared')->whereIn('id', $partIdsArr)->get()->getRowArray() ?? ['amount' => 0, 'cleared' => 0];
                    $totalNotClearedAmount = $totalNotClearedAmount + (float)$totalNotClearedAmount_part['amount'] - (float)$totalNotClearedAmount_part['cleared'];
                    $partIdsArr = [];
                }
            }
            if (count($partIdsArr) > 0) { // 处理分片操作剩余部分
                $totalNotClearedAmount_part = $this->db->table('bill_money')->selectSum('amount')->selectSum('cleared')->whereIn('id', $partIdsArr)->get()->getRowArray() ?? ['amount' => 0, 'cleared' => 0];
                $totalNotClearedAmount = $totalNotClearedAmount + (float)$totalNotClearedAmount_part['amount'] - (float)$totalNotClearedAmount_part['cleared'];
                $partIdsArr = [];
            }
        }
        // 发票缺口
        $advanced_info = [];
        if ($advanced > 0) {
            $advanced_info = (clone $builder)->selectCount('*', 'totalBillNum')->selectSum('amount', 'totalAmount')->selectSum('invoiceNum', 'totalInvoiceNum')->get()->getRowArray();
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['page'] = $page;
        $json['data']['records'] = $total;
        $json['data']['total'] = ceil($json['data']['records'] / $rows);
        $json['data']['rows'] = $v;
        $json['totalNotClearedAmount'] = $totalNotClearedAmount;
        $json['advanced_info'] = $advanced_info;
        return $this->response->setJSON($json);
    }
    // 查询
    public function query()
    {
        if (!$this->myservice->verifyRight('PO_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvPo/query 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('bill_order')->where(['id' => $id, 'billType' => 'PUR', 'isDelete' => 0])->get()->getRowArray();
        if ($data) {
            $info['id'] = intval($data['id']);
            $info['billDate'] = $data['billDate'];
            $info['deliveryDate'] = $data['deliveryDate'];
            $info['billNo'] = $data['billNo'];
            $info['billType'] = $data['billType'];
            $info['transType'] = intval($data['transType']);
            $info['totalQty'] = abs(intval($data['totalQty']));
            $info['totalDiscount'] = abs((float)$data['totalDiscount']);
            $info['totalAmount'] = abs((float)$data['totalAmount']);
            $info['disRate'] = abs((float)$data['disRate']);
            $info['disAmount'] = abs((float)$data['disAmount']);
            $info['deliverAmount'] = abs((float)$data['deliverAmount']);
            $info['amount'] = abs((float)$data['amount']);
            $info['uId'] = intval($data['uId']);
            $info['uName'] = $data['uName'];
            $info['checkId'] = intval($data['checkId']);
            $info['checkName'] = $data['checkName'];
            $info['contactId'] = intval($data['contactId']);
            $info['contactName'] = $data['contactName'];
            $info['sId'] = intval($data['sId']);
            $info['sName'] = $data['sName'];
            $info['srcOcNo'] = $data['srcOcNo'];
            $info['description'] = $data['description'];
            $info['files'] = $data['files'] == '' ? [] : json_decode($data['files'], true);
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
            $info['srcInvId1'] = intval($data['srcInvId1']);
            $info['srcInvNo1'] = $data['srcInvNo1'];
            $info['srcInvId2'] = intval($data['srcInvId2']);
            $info['srcInvNo2'] = $data['srcInvNo2'];
            $info['srcPurReqId'] = intval($data['srcPurReqId']);
            $info['isOnlyPCB'] = intval($data['isOnlyPCB']);
            $info['isDraft'] = intval($data['isDraft']);
            $info['isCancel'] = intval($data['isCancel']);
            $list = $this->db->table('bill_order_info AS a')
                ->select([
                    'a.*',
                    'b.id AS goodsId',
                    'b.no AS invNo',
                    'b.name AS invName',
                    'b.spec AS invSpec',
                    'b.sku AS invSku',
                    'b.brandName',
                    'b.unitName',
                    'b.purPrice',
                    'b.salePrice',
                    'b.imgs',
                    'b.shopImg',
                    'c.id AS contactId',
                    'c.name AS contactName',
                    'd.id AS storageId',
                    'd.name AS storageName'
                ])
                ->join('goods AS b', 'a.goodsId = b.id AND b.isDelete = 0', 'left')
                ->join('contact AS c', 'a.contactId = c.id AND c.isDelete = 0', 'left')
                ->join('storage AS d', 'a.storageId = d.id AND d.isDelete = 0', 'left')
                ->where('a.iid', $id)
                ->where('a.isDelete', 0)
                ->orderBy('a.id', 'ASC')
                ->get()
                ->getResultArray();
            $v = [];
            foreach ($list as $arr => $row) {
                $v[$arr]['id'] = intval($row['id']);
                $v[$arr]['goodsId'] = intval($row['goodsId']);
                $v[$arr]['invNo'] = $row['invNo'];
                $v[$arr]['invName'] = $row['invName'];
                $v[$arr]['invSpec'] = $row['invSpec'];
                $v[$arr]['invSku'] = $row['invSku'];
                $v[$arr]['brandName'] = $row['brandName'];
                $v[$arr]['unitName'] = $row['unitName'];
                $v[$arr]['storageId'] = intval($row['storageId']);
                $v[$arr]['storageName'] = $row['storageName'];
                $v[$arr]['orderQty'] = abs(intval($row['orderQty']));
                $v[$arr]['price'] = abs((float)$row['price']);
                $v[$arr]['discountRate'] = abs((float)$row['discountRate']);
                $v[$arr]['discount'] = abs((float)$row['discount']);
                $v[$arr]['amount'] = abs((float)$row['amount']);
                $v[$arr]['description'] = $row['description'];
                $imgs = [];
                if ($row['imgs']) {
                    $imgs = json_decode($row['imgs'], true);
                }
                if ($row['shopImg']) {
                    array_push($imgs, $row['shopImg']);
                }
                $v[$arr]['imgs'] = count($imgs) > 0 ? implode(',', $imgs) : '';
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
        if (!$this->myservice->verifyRight('PO_ADD')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvPo/add 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getGetPost('postData');
        if (strlen($data) > 0) {
            $data = $this->validform((array)json_decode($data, true));
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            $data['goodsStatus1'] = $data['goodsStatus2'] = ($data['transType'] == 101101 ? 0 : 3);
            // 取消自动审核，由采购经理手工审核
            $data['checkId'] = 0;
            $data['checkName'] = '';
            $info = array_intersect_key($data, array_flip([
                'srcOcNo', 'srcPurReqId', 'billNo', 'billType', 'transType', 'billDate', 'deliveryDate',
                'totalQty', 'totalDiscount', 'totalAmount', 'deliverAmount', 'disRate', 'disAmount', 'amount', 'goodsStatus1',
                'uId', 'uName', 'checkId', 'checkName', 'contactId', 'contactName', 'sId', 'sName', 'goods', 'files', 'description',
                'createTime', 'modifyTime', 'isDraft', 'isOnlyPCB'
            ]));
            $this->db->transStart();
            $this->db->table('bill_order')->insert($info);
            $iid = $this->db->insertID();
            $this->invpo_info(intval($iid), $data, []);
            if ($data['srcPurReqId'] > 0) { //如果是由采购申请转化
                $purReqData = $this->db->table('pur_req')->where(['id' => $data['srcPurReqId']])->get()->getRowArray();
                if ($purReqData) {
                    if ($purReqData['srcPoId'] == '') {
                        $this->db->table('pur_req')->where(['id' => $data['srcPurReqId']])->update(['purchaseId' => $data['uId'], 'purchaseName' => $data['uName'], 'purchaseTime' => $data['createTime'], 'srcPoId' => $iid, 'srcPoNo' => $data['billNo']]);
                    } else {
                        $srcPoId = explode(',', $purReqData['srcPoId']);
                        array_push($srcPoId, $iid);
                        $srcPoId = array_unique($srcPoId);
                        $srcPoId = implode(',', $srcPoId);

                        $srcPoNo = explode(',', $purReqData['srcPoNo']);
                        array_push($srcPoNo, $data['billNo']);
                        $srcPoNo = array_unique($srcPoNo);
                        $srcPoNo = implode(',', $srcPoNo);

                        $this->db->table('pur_req')->where(['id' => $data['srcPurReqId']])->update(['purchaseId' => $data['uId'], 'purchaseName' => $data['uName'], 'purchaseTime' => $data['createTime'], 'srcPoId' => $srcPoId, 'srcPoNo' => $srcPoNo]);
                    }
                }
            }
            $this->myservice->log('新增了' . $this->myerp['transTypeName'][$data['transType']] . ($data['isDraft'] == 1 ? '草稿' : '') . '，单号：' . $data['billNo']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => 'success', 'data' => ['id' => intval($iid)]]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    // 编辑
    public function update()
    {
        if (!$this->myservice->verifyRight('PO_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvPo/update 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getGetPost('postData');
        if (strlen($data) > 0) {
            $data = $this->validform((array)json_decode($data, true));
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            $oldData = $this->db->table('bill_order')->where(['id' => $data['id'], 'isDelete' => 0])->get()->getRowArray();
            if (!$this->myservice->verifyRight('SYS_ADMIN') && $this->myerp['uId'] != $oldData['uId'] && in_array($oldData['uId'], $this->myerp['enableUserIds'])) {
                 return $this->response->setJSON(['status' => -1, 'msg' => '不可编辑其他在职同事录入的采购订单，仅本人或管理员可修改！']);
            }
            if (!$this->myservice->verifyRight('SYS_ADMIN') && $oldData['billDate'] < date('Y-m-d', strtotime('-120 day'))) {
                return $this->response->setJSON(['status' => -1, 'msg' => '不可编辑已录入超过120天的采购订单，仅管理员可修改！']);
            }
            // 除仅PCB板子订单外，其他采购订单一旦存在收付款、收退货，都需要严谨判断风险，进行相应处理或阻止。
            if ($oldData['isOnlyPCB'] == 0 && $oldData['srcInvId1'] > 0 && $data['goods'] != $oldData['goods']) {
                return $this->response->setJSON(['status' => -1, 'msg' => '单据包含产品发生了改变，且已收货，请先删除收货单！']);
            }
            if ($oldData['isOnlyPCB'] == 0 && $oldData['srcInvId2'] > 0 && $data['goods'] != $oldData['goods']) {
                return $this->response->setJSON(['status' => -1, 'msg' => '单据包含产品发生了改变，且已退货，请先删除退货单！']);
            }

            $this->db->transBegin();
            // 修改已存在的付款单里的应付款金额更新到最新金额。
            if ($oldData['srcMoneyId1'] > 0 && $data['amount'] != $oldData['amount']) {
                $moneyData = $this->db->table('bill_money')->where(['id' => $oldData['srcMoneyId1'], 'isDelete' => 0])->get()->getRowArray();
                if ($data['amount'] < $moneyData['cleared']) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '单据金额不能小于已付款金额，修改失败，请先联系财务解决差额！']);
                } else {
                    $this->db->table('bill_money')->where('id', $oldData['srcMoneyId1'])->update(['amount' => $data['amount'], 'moneyStatus' => $data['amount'] == $moneyData['cleared'] ? 2 : 1]);
                    $this->db->table('bill_order')->where('id', $data['id'])->update(['moneyStatus1' => $data['amount'] == $moneyData['cleared'] ? 2 : 1]);
                }
            }
            if ($oldData['transType'] == 101101 && $data['transType'] == 101101) { //采购转采购
                $data['goodsStatus1'] = $oldData['goodsStatus1'];
                $data['goodsStatus2'] = $oldData['goodsStatus2'];
                $data['srcInvId1'] = $oldData['srcInvId1'];
                $data['srcInvId2'] = $oldData['srcInvId2'];
                $data['srcInvNo1'] = $oldData['srcInvNo1'];
                $data['srcInvNo2'] = $oldData['srcInvNo2'];
            } elseif ($oldData['transType'] == 101101 && $data['transType'] == 101102) {// 采购转直发
                $this->myservice->log('将采购订单转为了直发订单，单号：' . $data['billNo']);
                if ($oldData['srcInvId1'] > 0) { // 如果有采购收货单，重算库存，记录流水
                    $inventory_info = $this->db->table('bill_inventory_info')->where(['srcOrderId' => $oldData['id'], 'transType' => 102101, 'isDelete' => 0])->get()->getResultArray();
                    if (!empty($inventory_info)) {
                        foreach ($inventory_info as $arr1 => $row1) {
                            $this->db->table('goods')->set('currentQty', 'currentQty - '. $row1['qty'], false)->where('id', $row1['goodsId'])->update();
                            $newQty = $this->db->table('goods')->select('currentQty')->where('id', $row1['goodsId'])->get()->getRowArray()['currentQty'] ?? 0;
                            $invHisData = [];
                            $invHisData['goodsId'] = $row1['goodsId'];
                            $invHisData['billId'] = $row1['iid'];
                            $invHisData['billNo'] = $row1['billNo'];
                            $invHisData['billType'] = 'PU';
                            $invHisData['transType'] = $row1['transType'];
                            $invHisData['changeType'] = '删除' . $this->myerp['transTypeName'][$row1['transType']];
                            $invHisData['changeTime'] = date('Y-m-d H:i:s');
                            $invHisData['changeCurrentQty'] = -abs((float)$row1['qty']);
                            $invHisData['currentQty'] = (float)$newQty;
                            $this->db->table('inv_history')->insert($invHisData);
                            $this->db->table('bill_inventory')->where(['srcOrderId' => $data['id'], 'transType' => 102101])->update(['isDelete' => 1]);
                            $this->db->table('bill_inventory_info')->where(['srcOrderId' => $data['id'], 'transType' => 102101])->update(['isDelete' => 1]);
                            $this->db->table('bill_inventory_log')->where(['srcOrderId' => $data['id'], 'transType' => 102101])->update(['isDelete' => 1]);
                        }
                    }
                }
                $data['goodsStatus1'] = 3;
                $data['goodsStatus2'] = $oldData['goodsStatus2'];
                $data['srcInvId1'] = 0;
                $data['srcInvId2'] = $oldData['srcInvId2'];
                $data['srcInvNo1'] = '';
                $data['srcInvNo2'] = $oldData['srcInvNo2'];
            } elseif ($oldData['transType'] == 101102 && $data['transType'] == 101101) {// 直发转采购
                $this->myservice->log('将直发订单转为了采购订单，单号：' . $data['billNo']);
                $data['goodsStatus1'] = 0;
                $data['goodsStatus2'] = $oldData['goodsStatus2'];
                $data['srcInvId1'] = 0;
                $data['srcInvId2'] = $oldData['srcInvId2'];
                $data['srcInvNo1'] = '';
                $data['srcInvNo2'] = $oldData['srcInvNo2'];
            } else { //直发转直发
                $data['goodsStatus1'] = 3;
                $data['goodsStatus2'] = 3;
                $data['srcInvId1'] = 0;
                $data['srcInvId2'] = 0;
                $data['srcInvNo1'] = '';
                $data['srcInvNo2'] = '';
            }
            $info = array_intersect_key($data, array_flip([
                'srcOcNo', 'billType', 'transType', 'deliveryDate',
                'totalQty', 'totalDiscount', 'totalAmount', 'disRate', 'disAmount', 'deliverAmount', 'amount',
                'goodsStatus1', 'goodsStatus2', 'srcInvId1', 'srcInvId2', 'srcInvNo1', 'srcInvNo2',
                'contactId', 'contactName', 'sId', 'sName', 'goods', 'description', 'files',
                'modifyTime', 'isDraft', 'isOnlyPCB'
            ]));
            $this->db->table('bill_order')->where('id', $data['id'])->update($info);
            if (
                $oldData['transType'] != $data['transType'] || // 采购直发互转
                $oldData['isDraft'] != $data['isDraft'] || // 草稿转正式
                $oldData['goods'] != $data['goods'] // 产品改变
            ) {
                $this->invpo_info(intval($data['id']), $data, $oldData);
            }
            $this->myservice->log('编辑了' . $this->myerp['transTypeName'][$data['transType']] . ($data['isDraft'] == 1 ? '草稿' : '') . '，' . (($oldData['isDraft'] == 1 && $data['isDraft'] == 0) ? '并将草稿转为了正式，' : '') . '单号：' . $data['billNo']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '编辑成功！', 'data' => ['id' => $data['id']]]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    // 删除
    public function delete()
    {
        if (!$this->myservice->verifyRight('PO_DELETE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvPo/delete 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('bill_order')->getWhere(['id' => $id, 'isDelete' => 0])->getRowArray();
        if ($data) {
            if (!$this->myservice->verifyRight('SYS_ADMIN') && $data['uId'] != $this->myerp['uId']) {
                return $this->response->setJSON(['status' => -1, 'msg' => '不能删除别人的采购订单！仅本人或管理员可删除！']);
            }
            if ($data['checkId'] > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '已审核的单据不可随意删除！']);
            }
            if ($data['srcMoneyId1'] > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '已存在付款单，请先删除付款单！']);
            }
            if ($data['srcMoneyId2'] > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '已存在退款单，请先删除退款单！']);
            }
            if ($data['transType'] == 101101 && $data['srcInvId1'] > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '已存在收货单，请先删除收货单！']);
            }
            if ($data['srcInvId2'] > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '已存在退货单，请先删除退货单！']);
            }

            $this->db->transStart();
            if ($data['transType'] == 101102 && $data['isDraft'] == 0) {// 如果是直发正式订单，重算库存，记录流水
                $order_info = $this->db->table('bill_order_info')->where(['iid' => $data['id'], 'isDelete' => 0])->get()->getResultArray();
                if (!empty($order_info)) {
                    foreach ($order_info as $arr1 => $row1) {
                        $this->db->table('goods')->set('currentQty', 'currentQty - '. $row1['orderQty'], false)->where('id', $row1['goodsId'])->update();
                        $newQty = $this->db->table('goods')->select('currentQty')->where('id', $row1['goodsId'])->get()->getRowArray()['currentQty'] ?? 0;
                        $invHisData['goodsId'] = $row1['goodsId'];
                        $invHisData['billId'] = $row1['iid'];
                        $invHisData['billNo'] = $row1['billNo'];
                        $invHisData['billType'] = 'PO';
                        $invHisData['transType'] = $row1['transType'];
                        $invHisData['changeType'] = '删除' . $this->myerp['transTypeName'][$row1['transType']];
                        $invHisData['changeTime'] = date('Y-m-d H:i:s');
                        $invHisData['changeCurrentQty'] = -abs((float)$row1['orderQty']);
                        $invHisData['currentQty'] = (float)$newQty;
                        $invHisInfo = array_intersect_key($invHisData, array_flip(['goodsId', 'billId', 'billNo', 'billType', 'transType', 'changeType', 'changeTime', 'changeCurrentQty', 'currentQty']));
                        $this->db->table('inv_history')->insert($invHisInfo);
                    }
                }
            }
            $this->db->table('bill_order')->where('id', $id)->update(['isDelete' => 1]);
            $this->db->table('bill_order_info')->where('iid', $id)->update(['isDelete' => 1]);
            if ($data['srcPurReqId'] > 0) {
                $purReqData = $this->db->table('pur_req')->where(['id' => $data['srcPurReqId']])->get()->getRowArray();
                if ($purReqData) {
                    if ($purReqData['srcPoId'] == '' || $purReqData['srcPoId'] == $id) {
                        $this->db->table('pur_req')->where('id', $data['srcPurReqId'])->update(['purchaseId' => 0, 'purchaseName' => '', 'purchaseTime' => '0000-00-00 00:00:00', 'srcPoId' => '', 'srcPoNo' => '']);
                    } else {
                        $srcPoId = explode(',', $purReqData['srcPoId']);
                        $srcPoId = array_diff($srcPoId, [$id]);
                        $srcPoId = implode(',', $srcPoId);
                        $srcPoNo = explode(',', $purReqData['srcPoNo']);
                        $srcPoNo = array_diff($srcPoNo, [$data['billNo']]);
                        $srcPoNo = implode(',', $srcPoNo);
                        $this->db->table('pur_req')->where('id', $data['srcPurReqId'])->update(['srcPoId' => $srcPoId, 'srcPoNo' => $srcPoNo]);
                    }
                }
            }
            $this->myservice->log('删除了'.$this->myerp['transTypeName'][$data['transType']] . ($data['isDraft'] == 1 ? '草稿' : '').'，单号：' . $data['billNo']);
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
        if (!$this->myservice->verifyRight('PO_CHECK')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvPo/check 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $checkId = intval($this->request->getGetPost('checkId'));
        $checkName =$this->request->getGetPost('checkName');
        $data = $this->db->table('bill_order')->where(['id' => $id])->get()->getRowArray();
        if ($data) {
            $this->db->transStart();
            $this->db->table('bill_order')->where(['id' => $id])->update(['checkId ' => $checkId, 'checkName' => $checkName]);
            $this->db->table('bill_order_info')->where(['iid' => $id])->update(['checkId ' => $checkId]);
            $this->myservice->log(($checkId > 0 ? '审核了' : '反审核了') . $this->myerp['transTypeName'][$data['transType']] . '，单号：' . $data['billNo']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '操作成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 单独更新备注
    public function updatedes()
    {
        if (!$this->myservice->verifyRight('PO_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvPo/updatedes 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $newDes = $this->request->getGetPost('newDes');
        $data = $this->db->table('bill_order')->where(['id' => $id])->get()->getRowArray();
        if ($data) {
            $v['description'] = $newDes;
            $v['modifyTime'] = date("Y-m-d H:i:s");
            $this->db->transStart();
            $this->db->table('bill_order')->where(['id' => $id])->update($v);
            $this->myservice->log('单独更新了' . $this->myerp['transTypeName'][$data['transType']] . '的备注，单号：' . $data['billNo']);
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
        if (!$this->myservice->verifyRight('PO_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvPo/uploadfile 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        set_time_limit(0);
        session_write_close();
        if (empty($_FILES)){
            return $this->response->setJSON(['status' => -1, 'msg' => '没有上传文件！']);
        };
        $uppath = 'data/upfile/invPo/';
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
        $this->myservice->log('上传了采购订单资料：' . $_FILES['file']['name']);
        $json['status'] = 200;
        $json['msg'] = '资料上传成功';
        $json['file'] = ['name' => $_FILES['file']['name'], 'url' => $target_file, 'upName' => $this->myerp['uName'], 'upTime' => date('Y-m-d')];
        return $this->response->setJSON($json);
    }
    // 生成关联单据
    public function queryDetails()
    {
        if (!$this->myservice->verifyRight('PO_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvPo/queryDetails 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $transType = intval($this->request->getGetPost('transType'));
        $data = $this->db->table('bill_order')->where(['id' => $id, 'billType' => 'PUR', 'isDelete' => 0])->get()->getRowArray();
        if ($data) {
            if ($transType == 102101 && $data['srcInvId1'] > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '订单 ' . $data['billNo'] . ' 已经生成过采购收货单，不能重复生成！']);
            }
            if ($transType == 102102 && $data['srcInvId2'] > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '订单 ' . $data['billNo'] . ' 已经生成过采购退货单，不能重复生成！']);
            }
            if ($data['goodsStatus1'] == 3 && $transType == 102101) {
                return $this->response->setJSON(['status' => -1, 'msg' => '订单 ' . $data['billNo'] . ' 为直发订单，不能生成采购收货单！']);
            }
            $info['id'] = intval($data['id']);
            $info['billNo'] = $data['billNo'];
            $info['srcOrderId'] = intval($data['id']);
            $info['srcOrderNo'] = $data['billNo'];
            $info['billType'] = $data['billType'];
            $info['transType'] = intval($data['transType']);
            $info['contactId'] = intval($data['contactId']);
            $info['contactName'] = $data['contactName'];
            $info['totalOrderQty'] = abs(intval($data['totalQty']));
            $info['totalQty'] = 0;
            $info['totalNowQty'] = 0;
            $info['deliverAmount'] = abs((float)$data['deliverAmount']);
            $info['amount'] = abs((float)$data['amount']);
            $list = $this->db->table('bill_order_info AS a')
                ->select([
                    'a.*',
                    'b.id AS goodsId',
                    'b.no AS invNo',
                    'b.name AS invName',
                    'b.spec AS invSpec',
                    'b.sku AS invSku',
                    'b.brandName',
                    'b.unitName',
                    'b.purPrice',
                    'b.salePrice',
                    'b.imgs',
                    'b.shopImg',
                    'c.id AS contactId',
                    'c.name AS contactName',
                    'd.id AS storageId',
                    'd.name AS storageName'
                ])
                ->join('goods AS b', 'a.goodsId = b.id AND b.isDelete = 0', 'left')
                ->join('contact AS c', 'a.contactId = c.id AND c.isDelete = 0', 'left')
                ->join('storage AS d', 'a.storageId = d.id AND d.isDelete = 0', 'left')
                ->where('a.iid', $id)
                ->where('a.isDelete', 0)
                ->orderBy('a.id', 'ASC')
                ->get()
                ->getResultArray();
            $v = [];
            foreach ($list as $arr => $row) {
                $v[$arr]['goodsId'] = intval($row['goodsId']);
                $v[$arr]['invNo'] = $row['invNo'];
                $v[$arr]['invName'] = $row['invName'];
                $v[$arr]['invSpec'] = $row['invSpec'];
                $v[$arr]['invSku'] = $row['invSku'];
                $v[$arr]['brandName'] = $row['brandName'];
                $v[$arr]['unitName'] = $row['unitName'];
                $v[$arr]['storageId'] = intval($row['storageId']);
                $v[$arr]['storageName'] = $row['storageName'];
                $v[$arr]['orderQty'] = abs(intval($row['orderQty']));
                $v[$arr]['qty'] = 0;
                $v[$arr]['nowQty'] = 0;
                $v[$arr]['price'] = abs((float)$row['price']);
                $v[$arr]['amount'] = abs((float)$row['amount']);
                $v[$arr]['description'] = $row['description'];
            }
            $info['entries'] = $v;
            $json['status'] = 200;
            $json['msg'] = 'success';
            $json['data'] = $info;
            return $this->response->setJSON($json);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // info数据
    private function invpo_info(int $iid, array $data, array $oldData = [])
    {
        $v = [];
        foreach ($data['entries'] as $arr => $row) {
            $v[] = [
                'iid' => $iid,
                'billNo' => $data['billNo'],
                'billDate' => $data['billDate'],
                'billType' => $data['billType'],
                'transType' => $data['transType'],
                'goodsId' => intval($row['goodsId']),
                'storageId' => intval($row['storageId']),
                'orderQty' => abs(intval($row['orderQty'])),
                'price' => abs((float)$row['price']),
                'discountRate' => abs((float)$row['discountRate']),
                'discount' => abs((float)$row['discount']),
                'amount' => abs((float)$row['amount']),
                'uId' => $data['uId'] ?? $this->myerp['uId'],
                'contactId' => $data['contactId'],
                'checkId' => $data['checkId'] ?? 0,
                'description' => $row['description']
            ];
        }
        if (!empty($v)) {
            if ($data['id'] > 0) {
                // 如果是仅PCB订单，编辑时都要将已存在的收货单中的应收货数量更新到最新数量。
                if ($data['isOnlyPCB'] == 1 && count($oldData) > 0) {
                    if ($oldData['srcInvId1'] > 0) {
                        foreach ($v as $arr0 => $row0) {
                            $this->db->table('bill_inventory_info')->where(['iid' => $oldData['srcInvId1'], 'goodsId' => $row0['goodsId'], 'transType' => 102101, 'isDelete' => 0])->update(['orderQty' => $row0['orderQty']]);
                        }
                    }
                }
                // 编辑时，如果以前是直发正式订单，重算库存，记录流水
                if ($oldData && $oldData['transType'] == 101102 && $oldData['isDraft'] == 0) {
                    $order_info = $this->db->table('bill_order_info')->where(['iid' => $data['id'], 'isDelete' => 0])->get()->getResultArray();
                    if (!empty($order_info)) {
                        foreach ($order_info as $arr1 => $row1) {
                            $this->db->table('goods')->set('currentQty', 'currentQty - '. $row1['orderQty'], false)->where('id', $row1['goodsId'])->update();
                            $newQty = $this->db->table('goods')->select('currentQty')->where('id', $row1['goodsId'])->get()->getRowArray()['currentQty'] ?? 0;
                            $invHisData = [];
                            $invHisData['goodsId'] = $row1['goodsId'];
                            $invHisData['billId'] = $row1['iid'];
                            $invHisData['billNo'] = $row1['billNo'];
                            $invHisData['billType'] = 'PO';
                            $invHisData['transType'] = $row1['transType'];
                            $invHisData['changeType'] = '清空' . $this->myerp['transTypeName'][$row1['transType']] . ($oldData['isDraft'] == 1 ? '草稿' : '');
                            $invHisData['changeTime'] = date('Y-m-d H:i:s');
                            $invHisData['changeCurrentQty'] = -abs((float)$row1['orderQty']);
                            $invHisData['currentQty'] = (float)$newQty;
                            $this->db->table('inv_history')->insert($invHisData);
                        }
                    }
                }
                // 真删除
                $this->db->table('bill_order_info')->where('iid', $data['id'])->delete();
            }
            $this->db->table('bill_order_info')->insertBatch($v);
            if ($data['transType'] == 101102 && $data['isDraft'] == 0) { //如果现在是直发正式订单，计算库存，记录流水
                foreach ($v as $arr2 => $row2) {
                    $this->db->table('goods')->set('currentQty', 'currentQty + '. $row2['orderQty'], false)->where('id', $row2['goodsId'])->update();
                    $newQty = $this->db->table('goods')->select('currentQty')->where('id', $row2['goodsId'])->get()->getRowArray()['currentQty'] ?? 0;
                    $invHisData = [];
                    $invHisData['goodsId'] = $row2['goodsId'];
                    $invHisData['billId'] = $row2['iid'];
                    $invHisData['billNo'] = $row2['billNo'];
                    $invHisData['billType'] = 'PO';
                    $invHisData['transType'] = $row2['transType'];
                    $invHisData['changeType'] = ($data['id'] > 0 ? (($oldData && $oldData['transType'] > 0 && $data['transType'] != $oldData['transType']) ? '编辑并转为' : '编辑') : '新增') . $this->myerp['transTypeName'][$row2['transType']] . ($data['isDraft'] == 1 ? '草稿' : '');
                    $invHisData['changeTime'] = date('Y-m-d H:i:s');
                    $invHisData['changeCurrentQty'] = abs((float)$row2['orderQty']);
                    $invHisData['currentQty'] = (float)$newQty;
                    $this->db->table('inv_history')->insert($invHisData);
                }
            }
        }
    }
    // 数据验证
    private function validform(array $data)
    {
        $data['id'] = isset($data['id']) ? intval($data['id']) : -1;
        $data['srcPurReqId'] = isset($data['srcPurReqId']) ? intval($data['srcPurReqId']) : 0;
        $data['billType'] = 'PUR';
        $data['transType'] = intval($data['transType']);
        $data['totalQty'] = abs(intval($data['totalQty']));
        $data['totalDiscount'] = abs((float)$data['totalDiscount']);
        $data['totalAmount'] = abs((float) $data['totalAmount']);
        $data['disRate'] = abs((float)$data['disRate']);
        $data['disAmount'] = abs((float)$data['disAmount']);
        $data['deliverAmount'] = (isset($data['deliverAmount']) ? abs((float)$data['deliverAmount']) : 0);
        $data['amount'] = abs((float)$data['amount']);
        $data['files'] = strlen($data['files'] ?? '') < 10 ? '' : $data['files'];
        $data['uId'] = $data['uId'] ?? $this->myerp['uId'];
        $data['uName'] = $data['uName'] ?? $this->myerp['uName'];
        $data['contactId'] = intval($data['contactId']);
        $data['sId'] = intval($data['sId']);
        $data['createTime'] = date('Y-m-d H:i:s');
        $data['modifyTime'] = date('Y-m-d H:i:s');
        $data['isDraft'] = isset($data['isDraft']) ? intval($data['isDraft']) : 0;
        if ($this->db->table('contact')->where(['id' => intval($data['contactId'])])->countAllResults() < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '商家不存在！']);
        }
        if ($this->db->table('company')->where(['id' => intval($data['sId'])])->countAllResults() < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '采购方主体不存在！']);
        }
        if ($data['disRate'] < 0) {
            return $this->response->setJSON(['status' => -1, 'msg' => '额外优惠率要为正数！']);
        }
        if (abs((float)$data['disAmount']) > abs((float)$data['totalAmount'])) {
            return $this->response->setJSON(['status' => -1, 'msg' => '额外优惠额不能大于合计金额！']);
        }
        if (is_array($data['entries'])) {
            if (count($data['entries']) < 1) {
                return $this->response->setJSON(['status' => -1, 'msg' => '没有包含子产品！']);
            }
        } else {
            return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
        }
        if ($data['id'] > 0) {
            if ($this->db->table('bill_order')->where(['id' => $data['id'], 'isDelete' => 0])->countAllResults() < 1) {
                return $this->response->setJSON(['status' => -1, 'msg' => '单据不存在、或者已删除！']);
            }
        } else {
            if ($this->db->table('bill_order')->where(['billNo' => $data['billNo']])->countAllResults() > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '单据编号重复！']);
            }
        }
        $storage = array_column($this->db->table('storage')->select('id')->where(['isDisable' => 0, 'isDelete' => 0])->get()->getResultArray(), 'id');
        // 设置默认isOnlyPCB
        $data['isOnlyPCB'] = 1;
        foreach ($data['entries'] as $arr => $row) {
            if (intval($row['goodsId']) < 1) {
                return $this->response->setJSON(['status' => -1, 'msg' => '请选择产品！']);
            }
            if (intval($row['orderQty']) < 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '数量要为正数！']);
            }
            if ((float)$row['price'] < 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '采购价要为正数！']);
            }
            if ((float)$row['discountRate'] < 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '折扣率要为正数！']);
            }
            if (intval($row['storageId']) < 1) {
                return $this->response->setJSON(['status' => -1, 'msg' => '请选择相应的仓库！']);
            }
            if (!in_array($row['storageId'], $storage)) {
                return $this->response->setJSON(['status' => -1, 'msg' => $row['storageName'] . '不存在或不可用！']);
            }
            $data['goods'][$arr]['no'] = $row['invNo'];
            $data['goods'][$arr]['name'] = $row['invName'];
            $data['goods'][$arr]['spec'] = $row['invSpec'];
            $data['goods'][$arr]['sku'] = $row['invSku'];
            $data['goods'][$arr]['brand'] = $row['brandName'];
            $data['goods'][$arr]['qty'] = abs(intval($row['orderQty']));
            $data['goods'][$arr]['unit'] = $row['unitName'];
            $data['goods'][$arr]['price'] = abs((float)$row['price']);
            $data['goods'][$arr]['discount'] = abs((float)$row['discount']);
            $data['goods'][$arr]['amount'] = abs((float)$row['amount']);
            $lastPrice = $this->db->table('bill_order_info')->select('price')->where([
                'goodsId' => $row['goodsId'],
                'iid !=' => $data['id'],
                'billType' => 'PUR',
                'isDelete' => 0
            ])->get()->getRowArray()['price'] ?? 0;
            $data['goods'][$arr]['float'] = $lastPrice ? bcsub(abs((float)$row['price']), abs((float)$lastPrice), 5) : '0.00000';
            // 判断是否PCB板，如果任一产品不是，则isOnlyPCB = 0
            if ($data['isOnlyPCB'] == 1) {
                $goodsInfo = $this->db->table('goods')->where(['id' => $row['goodsId'], 'isDelete' => 0])->get()->getRowArray();
                if ($goodsInfo && !in_array($goodsInfo['categoryId'], ['34', '435'])) {
                    $data['isOnlyPCB'] = 0;
                }
            }
        }
        $data['goods'] = json_encode($data['goods'], 320);
        return $data;
    }
}
