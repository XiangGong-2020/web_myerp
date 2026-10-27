<?php

namespace App\Controllers\Bill;
use App\Controllers\BaseController;

class InvSo extends BaseController
{
    // 列表
    public function list()
    {
        if (!$this->myservice->verifyRight('SO_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvSo/list 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $status1 = $this->request->getGetPost('status1');
        $status2 = intval($this->request->getGetPost('status2'));
        $status3 = intval($this->request->getGetPost('status3'));
        $status4 = intval($this->request->getGetPost('status4'));
        $status5 = intval($this->request->getGetPost('status5'));
        $status6 = intval($this->request->getGetPost('status6'));
        $status7 = intval($this->request->getGetPost('status7'));
        $status8 = $this->request->getGetPost('status8');
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');
        $minAmount = (float)$this->request->getGetPost('minAmount');
        $maxAmount = (float)$this->request->getGetPost('maxAmount');
        $skey = $this->request->getGetPost('skey');
        $unClear = intval($this->request->getGetPost('unClear'));
        $builder = $this->db->table('bill_order');
        $builder->where('billType', 'SALE');
        if ($status1) {
            $builder->where('billFrom', $status1);
        }
        if (0 < $status2) {
            $builder->where('uId', $status2);
        }
        if (0 <= $status3) {
            if ($status3 == 0) {
                $builder->where('checkId', 0);
            } elseif ($status3 == 1) {
                $builder->where('checkId >', 0);
            } else {
                $builder->where('isCancel', 1);
            }
        }
        if (0 <= $status4) {
            $builder->where('moneyStatus1', $status4);
        }
        if (0 <= $status5) {
            $builder->where('moneyStatus2', $status5);
        }
        if (0 <= $status6) {
            if ($status6 == 0) {
                $builder->where('isCancel', 0)->where('isDraft', 0)->where('srcOcId', 0)->where('srcOcNo', '');
            }
            $builder->where('goodsStatus1', $status6);
        }
        if (0 <= $status7) {
            $builder->where('goodsStatus2', $status7);
        }
        if ($status8) {
            $builder->like('address', $this->db->escapeLikeString($status8));
        }
        if ($beginDate) {
            $builder->where('billDate >=', $beginDate);
        }
        if ($endDate) {
            $builder->where('billDate <=', $endDate);
        }
        if (0 < $minAmount) {
            $builder->where('amount >=', $minAmount);
        }
        if (0 < $maxAmount) {
            $builder->where('amount <=', $maxAmount);
        }
        if (1 == $unClear) {
            $builder->where('moneyStatus1', 0);
        } elseif (2 == $unClear) {
            $builder->where('moneyStatus1 <', 2);
        }
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
        $builder->where('isDelete', 0);
        $allowedSort = ['id','billFrom','billDate','deliveryDate','billNo','transType','address','expressCompanyName','expressNo','totalQty','amount','contactName','sName','moneyStatus1','moneyStatus2','goodsStatus1','goodsStatus2','srcOcNo','srcInvNo1','srcInvNo2','srcMoneyId1','srcMoneyNo1','srcMoneyId2','srcMoneyNo2','invoiceNum','description','uName','checkId','isDraft','isCancel'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $list = (clone $builder)->orderBy($orderField, $sord)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $total = (clone $builder)->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['billFrom'] = $row['billFrom'];
            $v[$arr]['billDate'] = $row['billDate'];
            $v[$arr]['deliveryDate'] = $row['deliveryDate'];
            $v[$arr]['billNo'] = $row['billNo'];
            $v[$arr]['transType'] = intval($row['transType']);
            $v[$arr]['address'] = $row['address'];
            $v[$arr]['expressCompanyName'] = $row['expressCompanyName'];
            $v[$arr]['expressNo'] = $row['expressNo'];
            $v[$arr]['totalQty'] = abs(intval($row['totalQty']));
            $v[$arr]['amount'] = abs((float)$row['amount']);
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
            $v[$arr]['srcOcId'] = intval($row['srcOcId']);
            $v[$arr]['srcOcNo'] = $row['srcOcNo'];
            $v[$arr]['invoiceNum'] = intval($row['invoiceNum']);
            $v[$arr]['invoiceAmount'] = $row['invoiceAmount'];
            $v[$arr]['files'] = $row['files'];
            $v[$arr]['description'] = $row['description'];
            $v[$arr]['goods'] = (array)json_decode($row['goods'], true);
            $v[$arr]['uId'] = intval($row['uId']);
            $v[$arr]['uName'] = $row['uName'];
            $v[$arr]['checkId'] = intval($row['checkId']);
            $v[$arr]['isDraft'] = intval($row['isDraft']);
            $v[$arr]['isCancel'] = intval($row['isCancel']);
        }
        // 未结款项汇总
        if (in_array($this->myerp['user']['department'], ['销售部', '财务部', '行政部'])) {
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
            $json['totalNotClearedAmount'] = $totalNotClearedAmount;
        }
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
        if (!$this->myservice->verifyRight('SO_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvSo/query 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('bill_order')->where(['id' =>$id, 'billType' => 'SALE', 'isDelete' => 0])->get()->getRowArray();
        if ($data) {
            $info['id'] = intval($data['id']);
            $info['billFrom'] = $data['billFrom'];
            $info['address'] = $data['address'];
            $info['billDate'] = $data['billDate'];
            $info['deliveryDate'] = $data['deliveryDate'];
            $info['billNo'] = $data['billNo'];
            $info['billType'] = $data['billType'];
            $info['transType'] = intval($data['transType']);
            $info['totalQty'] = abs(intval($data['totalQty']));
            $info['totalDiscount'] = abs((float)$data['totalDiscount']);
            $info['totalTax'] = abs((float)$data['totalTax']);
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
            $info['files'] = $data['files'];
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
            $info['srcOcId'] = intval($data['srcOcId']);
            $info['srcOcNo'] = $data['srcOcNo'];
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
                $v[$arr]['taxRate'] = abs((float)$row['taxRate']);
                $v[$arr]['tax'] = abs((float)$row['tax']);
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
        if (!$this->myservice->verifyRight('SO_ADD')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvSo/add 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getGetPost('postData');
        if (strlen($data) > 0) {
            $data = $this->validform((array)json_decode($data, true));
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            // 取消自动审核，由销售经理手工审核
            $data['checkId'] = 0;
            $data['checkName'] = '';
            $info = array_intersect_key($data, array_flip([
                'billFrom',  'billNo', 'billType', 'transType', 'billDate', 'deliveryDate',
                'totalQty', 'totalDiscount', 'totalTax', 'totalAmount', 'disRate', 'disAmount', 'deliverAmount', 'amount',
                'uId', 'uName', 'checkId', 'checkName', 'contactId', 'contactName', 'sId', 'sName', 'goods', 'files', 'description',
                'createTime', 'modifyTime', 'isDraft'
            ]));
            $this->db->transStart();
            $this->db->table('bill_order')->insert($info);
            $iid = $this->db->insertID();
            $this->invso_info(intval($iid), $data);
            $this->myservice->log('新增了销售订单' . ($data['isDraft'] == 1 ? '草稿' : '') . '，单号：' . $data['billNo']);
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
        if (!$this->myservice->verifyRight('SO_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvSo/update 权限不足！');
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
            if ($oldData['srcMoneyId1'] > 0 && $data['amount'] != $oldData['amount']) {
                return $this->response->setJSON(['status' => -1, 'msg' => '单据金额发生了改变，且已收款，请先删除收款单！']);
            }
            if ($oldData['srcMoneyId2'] > 0 && $data['amount'] != $oldData['amount']) {
                return $this->response->setJSON(['status' => -1, 'msg' => '单据金额发生了改变，且已退款，请先删除退款单！']);
            }
            if ($oldData['srcInvId1'] > 0 && $data['goods'] != $oldData['goods']) {
                return $this->response->setJSON(['status' => -1, 'msg' => '单据包含产品发生了改变，且已发货，请先删除发货单！']);
            }
            if ($oldData['srcInvId2'] > 0 && $data['goods'] != $oldData['goods']) {
                return $this->response->setJSON(['status' => -1, 'msg' => '单据包含产品发生了改变，且已退货，请先删除退货单！']);
            }
            if (!$this->myservice->verifyRight('SYS_ADMIN') && $this->myerp['uId'] != $oldData['uId']) {
                return $this->response->setJSON(['status' => -1, 'msg' => '不可编辑别人录入的销售订单，仅本人或管理员可修改！']);
            }
            if (!$this->myservice->verifyRight('SYS_ADMIN') && $oldData['billDate'] < date('Y-m-d', strtotime('-60 day'))) {
                return $this->response->setJSON(['status' => -1, 'msg' => '不可编辑已录入超过60天的销售订单，仅管理员可修改！']);
            }
            $info = array_intersect_key($data, array_flip([
                'deliveryDate',
                'totalQty', 'totalDiscount', 'totalTax', 'totalAmount','disRate', 'disAmount', 'deliverAmount', 'amount',
                'contactId', 'contactName', 'sId', 'sName', 'goods', 'files', 'description',
                'modifyTime', 'isDraft'
            ]));
            $this->db->transStart();
            $this->db->table('bill_order')->where('id', $data['id'])->update($info);
            if ($oldData['isDraft'] != $data['isDraft'] || //草稿转正式
                $data['goods'] != $oldData['goods'] //产品改变
            ) {
                $this->invso_info(intval($data['id']), $data);
            }
            $this->myservice->log('编辑了销售订单' . ($data['isDraft'] == 1 ? '草稿' : '') . '，' . (($oldData['isDraft'] == 1 && $data['isDraft'] == 0) ? '并将草稿转为了正式，' : '') . '单号：' . $data['billNo']);
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
        if (!$this->myservice->verifyRight('SO_DELETE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvSo/delete 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('bill_order')->where(['id' => $id, 'billType' => 'SALE', 'isDelete' => 0])->get()->getRowArray();
        if ($data) {
            if (!$this->myservice->verifyRight('SYS_ADMIN') && $data['uId'] != $this->myerp['uId']) {
                return $this->response->setJSON(['status' => -1, 'msg' => '不能删除别人的销售订单！仅本人或管理员可删除！']);
            }
            if ($data['billFrom'] != '手工录入') {
                return $this->response->setJSON(['status' => -1, 'msg' => '禁止删除网店同步订单！']);
            }
            if ($data['checkId'] > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '已审核的单据不可随意删除！']);
            }
            if ($data['srcMoneyId1'] > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '已存在收款单，请先删除收款单！']);
            }
            if ($data['srcMoneyId2'] > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '已存在退款单，请先删除退款单！']);
            }
            if ($data['srcInvId1'] > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '已存在发货单，请先删除发货单！']);
            }
            if ($data['srcInvId2'] > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '已存在退货单，请先删除退货单！']);
            }
            $this->db->transStart();
            $this->db->table('bill_order')->where('id', $id)->update(['isDelete' => 1]);
            $this->db->table('bill_order_info')->where('iid', $id)->update(['isDelete' => 1]);
            $this->myservice->log('删除了销售订单' . ($data['isDraft'] == 1 ? '草稿' : '').'，单号：' . $data['billNo']);
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
        if (!$this->myservice->verifyRight('SO_CHECK')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvSo/check 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $checkId = intval($this->request->getGetPost('checkId'));
        $checkName = $this->request->getGetPost('checkName');
        $billNo = $this->request->getGetPost('billNo');
        $data = $this->db->table('bill_order')->where('id', $id)->get()->getRowArray();
        if ($data) {
            $this->db->transStart();
            $this->db->table('bill_order')->where('id', $id)->update(['checkId ' => $checkId, 'checkName' => $checkName]);
            $this->db->table('bill_order_info')->where(['iid' => $id, 'billType' => 'SALE'])->update(['checkId ' => $checkId]);
            $this->myservice->log(($checkId > 0 ? '审核了' : '反审核了') . '销售订单，单号：' . $billNo);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '操作成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 导出产品清单
    public function exportInfoGrid()
    {
        if (!$this->myservice->verifyRight('SO_EXPORT')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvSo/exportInfoGrid 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = $this->request->getGetPost('id');
        $billNo = $this->request->getGetPost('billNo');
        $list = $this->db->table('bill_order_info')->where(['iid' => $id, 'isDelete' => 0])->select('id,iid,goodsId,orderQty,price,amount')->orderBy('id', 'ASC')->get()->getResultArray();
        $name = '销售订单产品清单_' . $billNo;

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
        $activeWorksheet->getStyle('A1:F' . (COUNT($list) + 2))->applyFromArray($styleArray, false);

        // 合并单元格
        $activeWorksheet->mergeCells('A1:F1');

        // 设置水平居中
        $activeWorksheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // 设置行高
        $activeWorksheet->getRowDimension('1')->setRowHeight(40);

        // 设置加粗
        $activeWorksheet->getStyle('A1')->getFont()->setBold(true);
        $activeWorksheet->getStyle('A2:F2')->getFont()->setBold(true);

        // 设置背景色
        $activeWorksheet->getStyle('A2:F2')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        $activeWorksheet->getStyle('A2:F2')->getFill()->getStartColor()->setRGB('F5F5F5');

        // 设置每列列宽
        $activeWorksheet->getColumnDimension('A')->setWidth(50);
        $activeWorksheet->getColumnDimension('B')->setWidth(50);
        $activeWorksheet->getColumnDimension('C')->setWidth(10);
        $activeWorksheet->getColumnDimension('D')->setWidth(10);
        $activeWorksheet->getColumnDimension('E')->setWidth(20);
        $activeWorksheet->getColumnDimension('F')->setWidth(20);

        //设置当前的sheet
        $activeWorksheet->setCellValue('A1', $name . '（导出时间 ' . date('Y-m-d H:i:s') . '）');
        $activeWorksheet->setCellValue('A2', '产品名称');
        $activeWorksheet->setCellValue('B2', '规格型号');
        $activeWorksheet->setCellValue('C2', '单位');
        $activeWorksheet->setCellValue('D2', '数量');
        $activeWorksheet->setCellValue('E2', '单价');
        $activeWorksheet->setCellValue('F2', '总价');

        $i = 3;
        $totalAmount = 0;
        foreach ($list as $k => $v) {
            $goods = $this->db->table('goods')->where('id', $v['goodsId'])->get()->getRowArray();
            $activeWorksheet->setCellValueExplicit('A' . $i, $goods['name'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $activeWorksheet->setCellValue('B' . $i, $goods['spec'] . ' ' . $goods['sku']);
            $activeWorksheet->setCellValue('C' . $i, $goods['unitName']);
            $activeWorksheet->setCellValue('D' . $i, $v['orderQty']);
            $activeWorksheet->setCellValue('E' . $i, $v['price']);
            $activeWorksheet->setCellValue('F' . $i, $v['amount']);
            $totalAmount += floatval($v['amount']);
            $i++;
        }
        $activeWorksheet->setCellValue('E' . $i, '合计');
        $activeWorksheet->setCellValue('F' . $i, $totalAmount);

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
    // 单独更新备注
    public function updatedes()
    {
        if (!$this->myservice->verifyRight('SO_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvSo/updatedes 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $newDes = $this->request->getGetPost('newDes');
        $data = $this->db->table('bill_order')->where('id', $id)->get()->getRowArray();
        if ($data) {
            $v['description'] = $newDes;
            $v['modifyTime'] = date("Y-m-d H:i:s");
            $this->db->transStart();
            $this->db->table('bill_order')->where('id', $id)->update($v);
            $this->myservice->log('单独更新了销售订单的备注，单号：' . $data['billNo']);
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
        if (!$this->myservice->verifyRight('SO_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvSo/uploadfile 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        set_time_limit(0);
        session_write_close();
        if (empty($_FILES)){
            return $this->response->setJSON(['status' => -1, 'msg' => '没有上传文件！']);
        };
        $uppath = 'data/upfile/invSo/';
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
        $this->myservice->log('上传了销售订单资料：' . $_FILES['file']['name']);
        $json['status'] = 200;
        $json['msg'] = '资料上传成功';
        $json['file'] = ['name' => $_FILES['file']['name'], 'url' => $target_file, 'upName' => $this->myerp['uName'], 'upTime' => date('Y-m-d')];
        return $this->response->setJSON($json);
    }
    // info数据
    private function invso_info(int $iid, array $data)
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
                'taxRate' => abs((float)$row['taxRate']),
                'tax' => abs((float)$row['tax']),
                'amount' => abs((float)$row['amount']),
                'uId' => $data['uId'] ?? $this->myerp['uId'],
                'contactId' => $data['contactId'],
                'checkId' => $data['checkId'] ?? 0,
                'description' => $row['description']
            ];
        }
        if (!empty($v)) {
            if (isset($data['id']) && $data['id'] > 0) {
                // 真删除
                $this->db->table('bill_order_info')->where('iid', $iid)->delete();
            }
            $this->db->table('bill_order_info')->insertBatch($v);
        }
    }
    // 生成关联单据
    public function queryDetails()
    {
        if (!$this->myservice->verifyRight('SO_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvSo/queryDetails 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $transType = intval($this->request->getGetPost('transType'));
        $data = $this->db->table('bill_order')->where('id', $id)->where('billType', 'SALE')->where('isDelete', 0)->get()->getRowArray();
        if ($data) {
            if ($transType == 102201 && $data['srcInvId1'] > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '已经生成过发货单，不能重复生成！']);
            }
            if ($transType == 102202 && $data['srcInvId2'] > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '已经生成过退货单，不能重复生成！']);
            }
            $json['status'] = 200;
            $json['msg'] = 'success';
            $json['data']['id'] = intval($data['id']);
            $json['data']['billNo'] = $data['billNo'];
            $json['data']['srcOrderId'] = intval($data['id']);
            $json['data']['srcOrderNo'] = $data['billNo'];
            $json['data']['billType'] = $data['billType'];
            $json['data']['transType'] = intval($data['transType']);
            $json['data']['contactId'] = intval($data['contactId']);
            $json['data']['contactName'] = $data['contactName'];
            $json['data']['totalOrderQty'] = abs(intval($data['totalQty']));
            $json['data']['totalQty'] = 0;
            $json['data']['totalNowQty'] = 0;
            $json['data']['deliverAmount'] = abs((float)$data['deliverAmount']);
            $json['data']['amount'] = abs((float)$data['amount']);
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
            $json['data']['entries'] = $v;
            return $this->response->setJSON($json);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 数据验证
    private function validform(array $data)
    {
        $data['id'] = isset($data['id']) ? intval($data['id']) : -1;
        $data['billFrom'] = '手工录入';
        $data['billType'] = 'SALE';
        $data['transType'] = intval($data['transType']);
        $data['totalQty'] = abs(intval($data['totalQty']));
        $data['totalDiscount'] = abs((float)$data['totalDiscount']);
        $data['totalTax'] = (isset($data['totalTax']) ? abs((float)$data['totalTax']) : 0);
        $data['totalAmount'] = abs((float) $data['totalAmount']);
        $data['disRate'] = abs((float)$data['disRate']);
        $data['disAmount'] = abs((float)$data['disAmount']);
        $data['deliverAmount'] = (isset($data['deliverAmount']) ? abs((float)$data['deliverAmount']) : 0);
        $data['amount'] = abs((float)$data['amount']);
        $data['files'] = strlen($data['files'] ?? '') < 10 ? '' : $data['files'];
        $data['uId'] = $data['uId'] ?? $this->myerp['uId'];
        $data['uName'] = $data['uName'] ?? $this->myerp['uName'];
        $data['contactId'] = intval($data['contactId']);
        $data['createTime'] = date('Y-m-d H:i:s');
        $data['modifyTime'] = date('Y-m-d H:i:s');
        $data['isDraft'] = isset($data['isDraft']) ? intval($data['isDraft']) : 0;
        if ($this->db->table('contact')->where('id', intval($data['contactId']))->countAllResults() < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '客户不存在！']);
        }
        if ($this->db->table('company')->where('id', intval($data['sId']))->countAllResults() < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '销售方主体不存在！']);
        }
        if ($data['disRate'] < 0) {
            return $this->response->setJSON(['status' => -1, 'msg' => '额外优惠率要为正数！']);
        }
        if (abs((float)$data['disAmount']) > abs((float)$data['totalAmount'])) {
            return $this->response->setJSON(['status' => -1, 'msg' => '额外优惠额不能大于合计金额！']);
        }
        if ((float)$data['amount'] < 0) {
            return $this->response->setJSON(['status' => -1, 'msg' => '应收金额要为正数！']);
        }
        if (is_array($data['entries'])) {
            if (count($data['entries']) < 1) {
                return $this->response->setJSON(['status' => -1, 'msg' => '没有包含产品！']);
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
        $storage = array_column($this->db->table('storage')->select('id')->where('isDisable', 0)->get()->getResultArray(), 'id');
        foreach ($data['entries'] as $arr => $row) {
            if (intval($row['goodsId']) < 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '请选择产品！']);
            }
            if (intval($row['orderQty']) < 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '数量要为正数！']);
            }
            if ((float)$row['price'] < 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '销售价要为正数！']);
            }
            if ((float)$row['discountRate'] < 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '折扣率要为正数！']);
            }
            if ((float)$row['taxRate'] < 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '税率要为正数！']);
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
            $data['goods'][$arr]['tax'] = abs((float)$row['tax']);
            $data['goods'][$arr]['amount'] = abs((float)$row['amount']);
        }
        $data['goods'] = json_encode($data['goods'], 320);
        return $data;
    }
}
