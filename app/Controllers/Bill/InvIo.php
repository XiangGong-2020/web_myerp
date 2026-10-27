<?php

namespace App\Controllers\Bill;
use App\Controllers\BaseController;

class InvIo extends BaseController
{
    // 其他入库单 列表
    public function listOi()
    {
        if (!$this->myservice->verifyRight('OI_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvIo/listOi 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $skey = $this->request->getGetPost('skey');
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');
        $storageId = intval($this->request->getGetPost('storageId'));
        $builder = $this->db->table('bill_inventory');
        $builder->where('billType', 'OI');
        if ($beginDate) {
            $builder->where('billDate >=', $beginDate);
        }
        if ($endDate) {
            $builder->where('billDate <=', $endDate);
        }
        if ($storageId > 0) {
            $builder->where('FIND_IN_SET(' . $storageId . ', instorageId) >', 0, false);
        }
        if ($skey !== '' && $skey !== null) {
            $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
            foreach ($keywords as $kw) {
                $builder->groupStart()
                    ->like('billNo', $this->db->escapeLikeString($kw))
                    ->orLike('description', $this->db->escapeLikeString($kw))
                    ->orLike('goods', $this->db->escapeLikeString($kw))
                ->groupEnd();
            }
        }
        $builder->where('isDelete', 0);
        $allowedSort = ['id','billNo','billDate','uName'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $list = (clone $builder)->orderBy($orderField, $sord)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[] = [
                'id' => intval($row['id']),
                'billDate' => $row['billDate'],
                'billNo' => $row['billNo'],
                'billType' => $row['billType'],
                'transType' => intval($row['transType']),
                'totalQty' => abs(intval($row['totalQty'])),
                'amount' => abs((float)$row['amount']),
                'uName' => $row['uName'],
                'description' => $row['description'],
                'goods' => (array)json_decode($row['goods'], true),
            ];
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['page'] = $page;
        $json['data']['records'] = $total;
        $json['data']['total'] = ceil($json['data']['records'] / $rows);
        $json['data']['rows'] = $v;
        return $this->response->setJSON($json);
    }
    // 其他入库单 查询
    public function queryOi($id = 0)
    {
        if (!$this->myservice->verifyRight('OI_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvIo/queryOi 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = (0 < $id ? $id : intval($this->request->getGetPost('id')));
        $data = $this->db->table('bill_inventory')->where('id', $id)->where('isDelete', 0)->get()->getRowArray();
        if ($data) {
            $info['id'] = intval($data['id']);
            $info['billDate'] = $data['billDate'];
            $info['billNo'] = $data['billNo'];
            $info['billType'] = $data['billType'];
            $info['transType'] = intval($data['transType']);
            $info['totalQty'] = abs(intval($data['totalQty']));
            $info['amount'] = abs((float)$data['amount']);
            $info['uId'] = intval($data['uId']);
            $info['uName'] = $data['uName'];
            $info['checkId'] = intval($data['checkId']);
            $info['checkName'] = $data['checkName'];
            $info['modifyTime'] = $data['modifyTime'];
            $info['createTime'] = $data['createTime'];
            $info['description'] = $data['description'];
            $list = $this->db->table('bill_inventory_info AS a')
                ->select([
                    'a.*',
                    'b.id AS goodsId',
                    'b.no AS invNo',
                    'b.name AS invName',
                    'b.spec AS invSpec',
                    'b.sku AS invSku',
                    'b.brandName',
                    'b.unitName',
                    'b.producePrice3',
                    'b.isProofread',
                    'c.id AS storageId',
                    'c.name AS storageName'
                ])
                ->join('goods AS b', 'a.goodsId = b.id AND b.isDelete = 0', 'left')
                ->join('storage AS c', 'a.storageId = c.id AND c.isDelete = 0', 'left')
                ->where('a.iid', $id)
                ->where('a.isDelete', 0)
                ->orderBy('a.id', 'ASC')
                ->get()
                ->getResultArray();
            $v = [];
            foreach ($list as $arr => $row) {
                $v[] = [
                    'id' => intval($row['id']),
                    'goodsId' => intval($row['goodsId']),
                    'invNo' => $row['invNo'],
                    'invName' => $row['invName'],
                    'invSpec' => $row['invSpec'],
                    'invSku' => $row['invSku'],
                    'brandName' => $row['brandName'],
                    'unitName' => $row['unitName'],
                    'storageId' => intval($row['storageId']),
                    'storageName' => $row['storageName'],
                    'qty' => abs(intval($row['qty'])),
                    'price' => abs((float)$row['price']),
                    'amount' => abs((float)$row['amount']),
                    'description' => $row['description']
                ];
            }
            $info['entries'] = $v;
            $json['status'] = 200;
            $json['msg'] = 'success';
            $json['data'] = $info;
            return $this->response->setJSON($json);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 其他入库单 新增
    public function addOi()
    {
        if (!$this->myservice->verifyRight('OI_ADD')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvIo/addOi 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getGetPost('postData');
        if (strlen($data) > 0) {
            $data = $this->validform_Oi((array)json_decode($data, true));
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            $info = array_intersect_key($data, array_flip(['billNo', 'billDate', 'billType', 'transType', 'totalQty', 'amount', 'inStorageId', 'description', 'goods',
                'uId', 'uName', 'checkId', 'checkName', 'createTime', 'modifyTime']));
            $this->db->transStart();
            $this->db->table('bill_inventory')->insert($info);
            $iid = $this->db->insertID();
            $this->inventory_info_Oi(intval($iid), $data);
            $this->myservice->log('新增了其他入库单，单号：' . $data['billNo']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '新增成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    // 其他入库单 编辑
    public function updateOi()
    {
        if (!$this->myservice->verifyRight('OI_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvIo/updateOi 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getGetPost('postData');
        if (strlen($data) > 0) {
            $data = $this->validform_Oi((array)json_decode($data, true));
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            $oldData = $this->db->table('bill_inventory')->getWhere(['id' => $data['id']])->getRowArray();
            $info = array_intersect_key($data, array_flip([
                'billDate', 'transType', 'totalQty', 'amount', 'inStorageId', 'description', 'goods', 'modifyTime'
            ]));
            $this->db->transStart();
            $this->db->table('bill_inventory')->where(['id' => $data['id']])->update($info);
            if ($data['goods'] != $oldData['goods']) {
                $this->inventory_info_Oi(intval($data['id']), $data);
            } else {
                $this->inventory_info_Oi_update(intval($data['id']), $data);
            }
            $this->myservice->log('编辑了其他入库单，单号：' . $data['billNo']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '编辑成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    // 其他入库单 删除
    public function deleteOi()
    {
        if (!$this->myservice->verifyRight('OI_DELETE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvIo/deleteOi 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('bill_inventory')->where(['id' => $id, 'billType' => 'OI'])->get()->getRowArray();
        if ($data) {
            if (!$this->myservice->verifyRight('SYS_ADMIN') && $data['uId'] != $this->myerp['uId']) {
                return $this->response->setJSON(['status' => -1, 'msg' => '不能删除别人录入的其他入库单！仅本人或管理员可删除！']);
            }
            $this->db->transStart();
            $inventory_info = $this->db->table('bill_inventory_info')->where(['iid' => $data['id'], 'billType' => 'OI', 'isDelete' => 0])->get()->getResultArray();
            foreach ($inventory_info as $arr => $row) {
                $this->db->table('goods')->set('currentQty', 'currentQty - ' . $row['qty'], false)->where(['id' => $row['goodsId']])->update();
                $currentQty = $this->db->table('goods')->select('currentQty')->where(['id' => $row['goodsId']])->get()->getRowArray()['currentQty'];
                $invHisData['goodsId'] = $row['goodsId'];
                $invHisData['billId'] = $row['iid'];
                $invHisData['billNo'] = $row['billNo'];
                $invHisData['billType'] = 'OI';
                $invHisData['transType'] = $row['transType'];
                $invHisData['changeType'] = '删除其他入库单';
                $invHisData['changeTime'] = date('Y-m-d H:i:s');
                $invHisData['changeCurrentQty'] = -abs((float)$row['qty']);
                $invHisData['currentQty'] = (float)$currentQty;
                $invHisInfo = array_intersect_key($invHisData, array_flip(['goodsId', 'billId', 'billNo', 'billType', 'transType', 'changeType', 'changeTime', 'changeCurrentQty', 'currentQty']));
                $this->db->table('inv_history')->insert($invHisInfo);
            }
            $this->db->table('bill_inventory')->set('isDelete', 1)->where(['id' => $id, 'billType' => 'OI'])->update();
            $this->db->table('bill_inventory_info')->set('isDelete', 1)->where(['iid' => $id, 'billType' => 'OI'])->update();
            $this->myservice->log('删除了其他入库单，单号：' . $data['billNo']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '删除成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 其他入库单 info数据
    private function inventory_info_Oi(int $iid, array $data)
    {
        $v = [];
        foreach ($data['entries'] as $arr => $row) {
            $v[] = [
                'iid' => $iid,
                'billNo' => $data['billNo'],
                'transType' => $data['transType'],
                'billDate' => $data['billDate'],
                'billType' => $data['billType'],
                'goodsId' => intval($row['goodsId']),
                'storageId' => intval($row['storageId']),
                'orderQty' => abs(intval($row['qty'])),
                'qty' => abs(intval($row['qty'])),
                'price' => abs((float)$row['price']),
                'amount' => abs((float)$row['amount']),
                'description' => $row['description']
            ];
        }
        if (!empty($v)) {
            if (isset($data['id']) && $data['id'] > 0) {
                $inventory_info = $this->db->table('bill_inventory_info')->where('iid', $data['id'])->where('billType', 'OI')->where('isDelete', 0)->get()->getResultArray();
                foreach ($inventory_info as $arr1 => $row1) {
                    $this->db->table('goods')->set('currentQty', 'currentQty - ' . $row1['qty'], false)->where('id', $row1['goodsId'])->update();
                    $currentQty = $this->db->table('goods')->select('currentQty')->where('id', $row1['goodsId'])->get()->getRowArray()['currentQty'] ?? 0;
                    $invHisData['goodsId'] = $row1['goodsId'];
                    $invHisData['billId'] = $row1['iid'];
                    $invHisData['billNo'] = $row1['billNo'];
                    $invHisData['billType'] = 'OI';
                    $invHisData['transType'] = $row1['transType'];
                    $invHisData['changeType'] = '清空其他入库单';
                    $invHisData['changeTime'] = date('Y-m-d H:i:s');
                    $invHisData['changeCurrentQty'] = -abs((float)$row1['qty']);
                    $invHisData['currentQty'] = (float)$currentQty;
                    $invHisInfo = array_intersect_key($invHisData, array_flip(['goodsId', 'billId', 'billNo', 'billType', 'transType', 'changeType', 'changeTime', 'changeCurrentQty', 'currentQty']));
                    $this->db->table('inv_history')->insert($invHisInfo);
                }
                // 真删除
                $this->db->table('bill_inventory_info')->where('iid', $data['id'])->where('billType', 'OI')->delete();
            }
            $this->db->table('bill_inventory_info')->insertBatch($v);
            foreach ($v as $arr2 => $row2) {
                $this->db->table('goods')->set('currentQty', 'currentQty + ' . $row2['qty'], false)->where('id', $row2['goodsId'])->update();
                $currentQty = $this->db->table('goods')->select('currentQty')->where('id', $row2['goodsId'])->get()->getRowArray()['currentQty'] ?? 0;
                $invHisData['goodsId'] = $row2['goodsId'];
                $invHisData['billId'] = $row2['iid'];
                $invHisData['billNo'] = $row2['billNo'];
                $invHisData['billType'] = 'OI';
                $invHisData['transType'] = $row2['transType'];
                $invHisData['changeType'] = ((isset($data['id']) && $data['id'] > 0) ? '编辑' : '新增') . '其他入库单';
                $invHisData['changeTime'] = date('Y-m-d H:i:s');
                $invHisData['changeCurrentQty'] = abs((float)$row2['qty']);
                $invHisData['currentQty'] = (float)$currentQty;
                $invHisInfo = array_intersect_key($invHisData, array_flip(['goodsId', 'billId', 'billNo', 'billType', 'transType', 'changeType', 'changeTime', 'changeCurrentQty', 'currentQty']));
                $this->db->table('inv_history')->insert($invHisInfo);
            }
        }
    }
    // 其他入库单 子条目 仅更新
    private function inventory_info_Oi_update(int $iid, array $data)
    {
        if (!$this->myservice->verifyRight('OI_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvIo/inventory_info_Oi_update 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $v = [];
        foreach ($data['entries'] as $arr => $row) {
            $v[] = [
<<<<<<< HEAD
                'id' => intval($row['id']),
                'iid' => intval($iid),
=======
                'id' => $row['id'],
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
                'storageId' => intval($row['storageId']),
                'description' => $row['description']
            ];
        }
        if (!empty($v)) {
<<<<<<< HEAD
            $this->db->table('bill_inventory_info')->updateBatch($v, ['id', 'iid']);
=======
            $this->db->table('bill_inventory_info')->updateBatch($v, 'id');
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
        }
    }
    // 其他入库单 验证
    private function validform_Oi(array $data)
    {
        $data['id'] = isset($data['id']) ? intval($data['id']) : -1;
        $data['billDate'] = $data['billDate'];
        $data['billType'] = 'OI';
        $data['transType'] = 102401;
        $data['totalQty'] = abs(intval($data['totalQty']));
        $data['uId'] = $data['checkId'] = $this->myerp['uId'];
        $data['uName'] = $data['checkName'] = $this->myerp['uName'];
        $data['modifyTime'] = date('Y-m-d H:i:s');
        $data['createTime'] = $data['modifyTime'];
        $data['entries'] = $data['entries'] ?? [];
        if (count($data['entries']) < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
        }
        if ($data['id'] > 0) {
            if ($this->db->table('bill_inventory')->where(['id' => $data['id'], 'isDelete' => 0])->countAllResults() < 1) {
                return $this->response->setJSON(['status' => -1, 'msg' => '单据不存在、或者已删除']);
            }
        } else {
            if ($this->db->table('bill_inventory')->where(['billNo' => $data['billNo']])->countAllResults() > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '单据编号重复！']);
            }
        }
        $storage = array_column($this->db->table('storage')->select('id')->where(['isDisable' => 0, 'isDelete' => 0])->get()->getResultArray(), 'id');
        $inStorageId = [];
        foreach ($data['entries'] as $arr => $row) {
            if (intval($row['goodsId']) < 1) {
                return $this->response->setJSON(['status' => -1, 'msg' => '请选择产品']);
            }
            if (intval($row['qty']) < 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '数量要为正数！']);
            }
            if ((float)$row['price'] < 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '采购价要为正数！']);
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
            $data['goods'][$arr]['qty'] = abs(intval($row['qty']));
            $data['goods'][$arr]['unit'] = $row['unitName'];
            $data['goods'][$arr]['price'] = $row['price'];
            $data['goods'][$arr]['amount'] = $row['amount'];
            $inStorageId[] = $row['storageId'];
        }
        $data['goods'] = json_encode($data['goods'], 320);
        $data['inStorageId'] = join(',', array_unique($inStorageId));
        return $data;
    }

    // 其他出库单 列表
    public function listOo()
    {
        if (!$this->myservice->verifyRight('OO_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvIo/listOo 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $skey = $this->request->getGetPost('skey');
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');
        $storageId = intval($this->request->getGetPost('storageId'));
        $builder = $this->db->table('bill_inventory');
        $builder->where('billType', 'OO');
        if ($beginDate) {
            $builder->where('billDate >=', $beginDate);
        }
        if ($endDate) {
            $builder->where('billDate <=', $endDate);
        }
        if ($storageId > 0) {
            $builder->where('FIND_IN_SET(' . $storageId . ', outStorageId) >', 0, false);
        }
        if ($skey !== '' && $skey !== null) {
            $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
            foreach ($keywords as $kw) {
                $builder->groupStart()
                    ->like('billNo', $this->db->escapeLikeString($kw))
                    ->orLike('description', $this->db->escapeLikeString($kw))
                    ->orLike('goods', $this->db->escapeLikeString($kw))
                ->groupEnd();
            }
        }
        $builder->where('isDelete', 0);
        $allowedSort = ['id','billNo','billDate','uName'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $list = (clone $builder)->orderBy($orderField, $sord)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[] = [
                'id' => intval($row['id']),
                'billDate' => $row['billDate'],
                'billNo' => $row['billNo'],
                'billType' => $row['billType'],
                'transType' => intval($row['transType']),
                'totalQty' => abs(intval($row['totalQty'])),
                'amount' => abs((float)$row['amount']),
                'uName' => $row['uName'],
                'description' => $row['description'],
                'goods' => (array)json_decode($row['goods'], true)
            ];
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['page'] = $page;
        $json['data']['records'] = $total;
        $json['data']['total'] = ceil($json['data']['records'] / $rows);
        $json['data']['rows'] = $v;
        return $this->response->setJSON($json);
    }
    // 其他出库单 查询
    public function queryOo($id = 0)
    {
        if (!$this->myservice->verifyRight('OO_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvIo/queryOo 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = (0 < $id ? $id : intval($this->request->getGetPost('id')));
        $data = $this->db->table('bill_inventory')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
        if ($data) {
            $info['id'] = intval($data['id']);
            $info['billDate'] = $data['billDate'];
            $info['billNo'] = $data['billNo'];
            $info['billType'] = $data['billType'];
            $info['transType'] = intval($data['transType']);
            $info['totalQty'] = abs(intval($data['totalQty']));
            $info['amount'] = abs((float)$data['amount']);
            $info['uId'] = intval($data['uId']);
            $info['uName'] = $data['uName'];
            $info['checkId'] = intval($data['checkId']);
            $info['checkName'] = $data['checkName'];
            $info['modifyTime'] = $data['modifyTime'];
            $info['createTime'] = $data['createTime'];
            $info['description'] = $data['description'];
            $list = $this->db->table('bill_inventory_info AS a')
                ->select([
                    'a.*',
                    'b.id AS goodsId',
                    'b.no AS invNo',
                    'b.name AS invName',
                    'b.spec AS invSpec',
                    'b.sku AS invSku',
                    'b.brandName',
                    'b.unitName',
                    'b.producePrice3',
                    'b.isProofread',
                    'c.id AS storageId',
                    'c.name AS storageName'
                ])
                ->join('goods AS b', 'a.goodsId = b.id AND b.isDelete = 0', 'left')
                ->join('storage AS c', 'a.storageId = c.id AND c.isDelete = 0', 'left')
                ->where('a.iid', $id)
                ->where('a.isDelete', 0)
                ->orderBy('a.id', 'ASC')
                ->get()
                ->getResultArray();
            $v = [];
            foreach ($list as $arr => $row) {
                $v[] = [
                    'id' => intval($row['id']),
                    'goodsId' => intval($row['goodsId']),
                    'invNo' => $row['invNo'],
                    'invName' => $row['invName'],
                    'invSpec' => $row['invSpec'],
                    'invSku' => $row['invSku'],
                    'brandName' => $row['brandName'],
                    'unitName' => $row['unitName'],
                    'storageId' => intval($row['storageId']),
                    'storageName' => $row['storageName'],
                    'qty' => abs(intval($row['qty'])),
                    'price' => abs((float)$row['price']),
                    'amount' => abs((float)$row['amount']),
                    'description' => $row['description']
                ];
            }
            $info['entries'] = $v;
            $json['status'] = 200;
            $json['msg'] = 'success';
            $json['data'] = $info;
            return $this->response->setJSON($json);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 其他出库单 新增
    public function addOo()
    {
        if (!$this->myservice->verifyRight('OO_ADD')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvIo/addOo 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getGetPost('postData');
        if (strlen($data) > 0) {
            $data = $this->validform_Oo((array)json_decode($data, true));
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            $info = array_intersect_key($data, array_flip([
                'billNo', 'billDate', 'billType', 'transType', 'totalQty', 'amount', 'outStorageId', 'description', 'goods',
                'uId', 'uName', 'checkId', 'checkName', 'createTime', 'modifyTime'
            ]));
            $this->db->transStart();
            $this->db->table('bill_inventory')->insert($info);
            $iid = $this->db->insertID();
            $this->inventory_info_Oo(intval($iid), $data);
            $this->myservice->log('新增了其他出库单，单号：' . $data['billNo']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '新增成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    // 其他出库单 编辑
    public function updateOo()
    {
        if (!$this->myservice->verifyRight('OO_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvIo/updateOo 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getGetPost('postData');
        if (strlen($data) > 0) {
            $data = $this->validform_Oo((array)json_decode($data, true));
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            $oldData = $this->db->table('bill_inventory')->getWhere(['id' => $data['id']])->getRowArray();
            $info = array_intersect_key($data, array_flip(['billDate', 'transType', 'totalQty', 'amount', 'outStorageId', 'description', 'goods', 'modifyTime']));
            $this->db->transStart();
            $this->db->table('bill_inventory')->where(['id' => $data['id']])->update($info);
            if ($data['goods'] != $oldData['goods']) {
                $this->inventory_info_Oo(intval($data['id']), $data);
            } else {
                $this->inventory_info_Oo_update(intval($data['id']), $data);
            }
            $this->myservice->log('编辑了其他出库单，单号：' . $data['billNo']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '编辑成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    // 其他出库单 删除
    public function deleteOo()
    {
        if (!$this->myservice->verifyRight('OO_DELETE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvIo/deleteOo 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('bill_inventory')->where(['id' => $id, 'billType' => 'OO', 'isDelete' => 0])->get()->getRowArray();
        if ($data) {
            if (!$this->myservice->verifyRight('SYS_ADMIN') && $data['uId'] != $this->myerp['uId']) {
                return $this->response->setJSON(['status' => -1, 'msg' => '不能删除别人录入的其他出库单！仅本人或管理员可删除！']);
            }
            $this->db->transStart();
            //提取旧数据更新库存后删除旧数据
            $inventory_info = $this->db->table('bill_inventory_info')->where(['iid' => $data['id'], 'billType' => 'OO', 'isDelete' => 0])->get()->getResultArray();
            foreach ($inventory_info as $arr => $row) {
                $this->db->table('goods')->set('currentQty', 'currentQty + ' . $row['qty'], false)->where(['id' => $row['goodsId']])->update();
                $newQty = $this->db->table('goods')->select('currentQty')->where(['id' => $row['goodsId']])->get()->getRowArray()['currentQty'] ?? 0;
                $invHisData['goodsId'] = $row['goodsId'];
                $invHisData['billId'] = $row['iid'];
                $invHisData['billNo'] = $row['billNo'];
                $invHisData['billType'] = 'OO';
                $invHisData['transType'] = $row['transType'];
                $invHisData['changeType'] = '删除其他出库单';
                $invHisData['changeTime'] = date('Y-m-d H:i:s');
                $invHisData['changeCurrentQty'] = abs((float)$row['qty']);
                $invHisData['currentQty'] = (float)$newQty;
                $invHisInfo = array_intersect_key($invHisData, array_flip(['goodsId', 'billId', 'billNo', 'billType', 'transType', 'changeType', 'changeTime', 'changeCurrentQty', 'currentQty']));
                $this->db->table('inv_history')->insert($invHisInfo);
            }
            $this->db->table('bill_inventory')->set('isDelete', 1)->where(['id' => $id, 'billType' => 'OO'])->update();
            $this->db->table('bill_inventory_info')->set('isDelete', 1)->where(['iid' => $id, 'billType' => 'OO'])->update();
            $this->myservice->log('删除了其他出库单，单号：' . $data['billNo']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '删除成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 其他出库单 info数据
    private function inventory_info_Oo(int $iid, array $data)
    {
        $v = [];
        foreach ($data['entries'] as $arr => $row) {
            $v[] = [
                'iid' => $iid,
                'billNo' => $data['billNo'],
                'transType' => $data['transType'],
                'billDate' => $data['billDate'],
                'billType' => $data['billType'],
                'goodsId' => intval($row['goodsId']),
                'storageId' => intval($row['storageId']),
                'qty' => abs(intval($row['qty'])),
                'price' => abs((float)$row['price']),
                'amount' => abs((float)$row['amount']),
                'description' => $row['description']
            ];
        }
        if (!empty($v)) {
            if (isset($data['id']) && $data['id'] > 0) {
                $inventory_info = $this->db->table('bill_inventory_info')->where(['iid' => $data['id'], 'billType' => 'OO', 'isDelete' => 0])->get()->getResultArray();
                foreach ($inventory_info as $arr1 => $row1) {
                    $this->db->table('goods')->set('currentQty', 'currentQty + ' . $row1['qty'], false)->where('id', $row1['goodsId'])->update();
                    $currentQty = $this->db->table('goods')->select('currentQty')->where('id', $row1['goodsId'])->get()->getRowArray()['currentQty'] ?? 0;
                    $invHisData['goodsId'] = $row1['goodsId'];
                    $invHisData['billId'] = $row1['iid'];
                    $invHisData['billNo'] = $row1['billNo'];
                    $invHisData['billType'] = 'OO';
                    $invHisData['transType'] = $row1['transType'];
                    $invHisData['changeType'] = '清空其他出库单';
                    $invHisData['changeTime'] = date('Y-m-d H:i:s');
                    $invHisData['changeCurrentQty'] = abs((float)$row1['qty']);
                    $invHisData['currentQty'] = (float)$currentQty;
                    $invHisInfo = array_intersect_key($invHisData, array_flip(['goodsId', 'billId', 'billNo', 'billType', 'transType', 'changeType', 'changeTime', 'changeCurrentQty', 'currentQty']));
                    $this->db->table('inv_history')->insert($invHisInfo);
                }
                // 真删除
                $this->db->table('bill_inventory_info')->where(['iid' => $iid, 'billType' => 'OO'])->delete();
            }
            $this->db->table('bill_inventory_info')->insertBatch($v);
            foreach ($v as $arr2 => $row2) {
                $this->db->table('goods')->set('currentQty', 'currentQty - ' . $row2['qty'], false)->where('id', $row2['goodsId'])->update();
                $currentQty = $this->db->table('goods')->select('currentQty')->where('id', $row2['goodsId'])->get()->getRowArray()['currentQty'] ?? 0;
                $invHisData['goodsId'] = $row2['goodsId'];
                $invHisData['billId'] = $row2['iid'];
                $invHisData['billNo'] = $row2['billNo'];
                $invHisData['billType'] = 'OO';
                $invHisData['transType'] = $row2['transType'];
                $invHisData['changeType'] = ((isset($data['id']) && $data['id'] > 0) ? '编辑' : '新增') . '其他出库单';
                $invHisData['changeTime'] = date('Y-m-d H:i:s');
                $invHisData['changeCurrentQty'] = -abs((float)$row2['qty']);
                $invHisData['currentQty'] = (float)$currentQty;
                $invHisInfo = array_intersect_key($invHisData, array_flip(['goodsId', 'billId', 'billNo', 'billType', 'transType', 'changeType', 'changeTime', 'changeCurrentQty','currentQty']));
                $this->db->table('inv_history')->insert($invHisInfo);
            }
        }
    }
    // 其他出库单 子条目 仅更新
    private function inventory_info_Oo_update(int $iid, array $data)
    {
        $v = [];
        foreach ($data['entries'] as $arr => $row) {
            $v[] = [
<<<<<<< HEAD
                'id' => intval($row['id']),
                'iid' => intval($iid),
=======
                'id' => $row['id'],
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
                'storageId' => intval($row['storageId']),
                'description' => $row['description']
            ];
        }
        if (!empty($v)) {
<<<<<<< HEAD
            $this->db->table('bill_inventory_info')->updateBatch($v, ['id', 'iid']);
=======
            $this->db->table('bill_inventory_info')->updateBatch($v, 'id');
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
        }
    }
    // 其他出库单 验证
    private function validform_Oo(array $data)
    {
        $data['id'] = isset($data['id']) ? intval($data['id']) : -1;
        $data['billDate'] = $data['billDate'];
        $data['billType'] = 'OO';
        $data['transType'] = 102402;
        $data['totalQty'] = abs(intval($data['totalQty']));
        $data['uId'] = $data['checkId'] = $this->myerp['uId'];
        $data['uName'] = $data['checkName'] = $this->myerp['uName'];
        $data['modifyTime'] = date('Y-m-d H:i:s');
        $data['createTime'] = $data['modifyTime'];
        $data['entries'] = $data['entries'] ?? [];
        if (count($data['entries']) < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
        }
        if ($data['id'] > 0) {
            if ($this->db->table('bill_inventory')->where(['id' => $data['id'], 'isDelete' => 0])->countAllResults() < 1) {
                return $this->response->setJSON(['status' => -1, 'msg' => '单据不存在、或者已删除']);
            }
        } else {
            if ($this->db->table('bill_inventory')->where(['billNo' => $data['billNo']])->countAllResults() > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '单据编号重复！']);
            }
        }
        $storage = array_column($this->db->table('storage')->select('id')->where(['isDisable' => 0, 'isDelete' => 0])->get()->getResultArray(), 'id');
        $outStorageId = [];
        foreach ($data['entries'] as $arr => $row) {
            if (intval($row['goodsId']) < 1) {
                return $this->response->setJSON(['status' => -1, 'msg' => '请选择产品']);
            }
            if (intval($row['qty']) < 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '数量要为正数！']);
            }
            if ((float)$row['price'] < 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '销售单价要为正数！']);
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
            $data['goods'][$arr]['qty'] = abs(intval($row['qty']));
            $data['goods'][$arr]['unit'] = $row['unitName'];
            $data['goods'][$arr]['price'] = $row['price'];
            $data['goods'][$arr]['amount'] = $row['amount'];
            $outStorageId[] = $row['storageId'];
        }
        $data['goods'] = json_encode($data['goods'], 320);
        $data['outStorageId'] = join(',', array_unique($outStorageId));
        return $data;
    }

    // 库存调拨单 列表
    public function listTf()
    {
        if (!$this->myservice->verifyRight('TF_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvIo/listTf 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $skey = $this->request->getGetPost('skey');
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');
        $inStorageId = intval($this->request->getGetPost('inStorageId'));
        $outStorageId = intval($this->request->getGetPost('outStorageId'));
        $builder = $this->db->table('bill_inventory');
        $builder->where('billType', 'TF');
        if ($beginDate) {
            $builder->where('billDate >=', $beginDate);
        }
        if ($endDate) {
            $builder->where('billDate <=', $endDate);
        }
        if ($inStorageId > 0) {
            $builder->where('FIND_IN_SET(' . $inStorageId . ', inStorageId) >', 0, false);
        }
        if ($outStorageId > 0) {
            $builder->where('FIND_IN_SET(' . $outStorageId . ', outStorageId) >', 0, false);
        }
        if ($skey !== '' && $skey !== null) {
            $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
            foreach ($keywords as $kw) {
                $builder->groupStart()
                    ->like('billNo', $this->db->escapeLikeString($kw))
                    ->orLike('description', $this->db->escapeLikeString($kw))
                    ->orLike('goods', $this->db->escapeLikeString($kw))
                ->groupEnd();
            }
        }
        $builder->where('isDelete', 0);
        $allowedSort = ['id','billNo','billDate','uName'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $list = (clone $builder)->orderBy($orderField, $sord)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            //包含产品
            $goods = (array)json_decode($row['goods'], true);
            $unitName = $outStorageName = $inStorageName = $qty = [];
            foreach ($goods as $arr1 => $row1) {
                $goods[$row['id']][] = $row1['no'] . ' ' . $row1['name'] . ($row1['spec'] ? ' ' . $row1['spec'] : '') . ($row1['sku'] ? ' ' . $row1['sku'] : '') . ($row1['brandName'] ? ' ' . $row1['brandName'] : '');
                $unitName[$row['id']][] = $row1['unitName'];
                $outStorageName[$row['id']][] = $row1['outStorageName'];
                $inStorageName[$row['id']][] = $row1['inStorageName'];
                $qty[$row['id']][] = abs($row1['qty']);
            }
            $v[$arr]['goods'] = $goods[$row['id']];
            $v[$arr]['unitName'] = $unitName[$row['id']];
            $v[$arr]['outStorageName'] = $outStorageName[$row['id']];
            $v[$arr]['inStorageName'] = $inStorageName[$row['id']];
            $v[$arr]['qty'] = $qty[$row['id']];
            //包含产品结束
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['billDate'] = $row['billDate'];
            $v[$arr]['description'] = $row['description'];
            $v[$arr]['billNo'] = $row['billNo'];
            $v[$arr]['uName'] = $row['uName'];
            $v[$arr]['checkId'] = intval($row['checkId']);
            $v[$arr]['checkName'] = $row['checkName'];
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['page'] = $page;
        $json['data']['records'] = $total;
        $json['data']['total'] = ceil($json['data']['records'] / $rows);
        $json['data']['rows'] = $v;
        return $this->response->setJSON($json);
    }
    // 库存调拨单 查询
    public function queryTf($id = 0)
    {
        if (!$this->myservice->verifyRight('TF_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvIo/queryTf 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = (0 < $id ? $id : intval($this->request->getGetPost('id')));
        $data = $this->db->table('bill_inventory')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
        if ($data) {
            $info['id'] = intval($data['id']);
            $info['billDate'] = $data['billDate'];
            $info['billNo'] = $data['billNo'];
            $info['totalQty'] = (float)$data['totalQty'];
            $info['uName'] = $data['uName'];
            $info['checkId'] = intval($data['checkId']);
            $info['checkName'] = $data['checkName'];
            $info['createTime'] = $data['createTime'];
            $info['modifyTime'] = $data['modifyTime'];
            $info['description'] = $data['description'];
            $goods = (array)json_decode($data['goods'], true);
<<<<<<< HEAD
            $v = [];
=======
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
            foreach ($goods as $arr => $row) {
                $v[$arr]['goodsId'] = $row['id'];
                $v[$arr]['invNo'] = $row['no'];
                $v[$arr]['invName'] = $row['name'];
                $v[$arr]['invSpec'] = $row['spec'] ?? '';
                $v[$arr]['invSku'] = $row['sku'] ?? '';
                $v[$arr]['brandName'] = $row['brandName'] ?? '';
                $v[$arr]['unitName'] = $row['unitName'];
                $v[$arr]['inStorageId'] = intval($row['inStorageId']);
                $v[$arr]['inStorageName'] = $row['inStorageName'];
                $v[$arr]['outStorageId'] = intval($row['outStorageId']);
                $v[$arr]['outStorageName'] = $row['outStorageName'];
                $v[$arr]['qty'] = abs(intval($row['qty']));
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
    // 库存调拨单 新增
    public function addTf()
    {
        if (!$this->myservice->verifyRight('TF_ADD')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvIo/addTf 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getGetPost('postData');
        if (strlen($data) > 0) {
            $data = $this->validform_Tf((array)json_decode($data, true));
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            $info = array_intersect_key($data, array_flip([
                'billDate', 'billNo', 'billType', 'transType', 'inStorageId', 'outStorageId', 'totalQty', 'uId', 'uName', 'checkId', 'checkName', 'goods', 'description', 'createTime', 'modifyTime'
            ]));
            $this->db->transStart();
            $this->db->table('bill_inventory')->insert($info);
            $iid = $this->db->insertID();
            $this->inventory_info_Tf(intval($iid), $data);
            $this->myservice->log('新增了库存调拨单，单号：' . $info['billNo']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '新增成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    // 库存调拨单 编辑
    public function updateTf()
    {
        if (!$this->myservice->verifyRight('TF_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvIo/updateTf 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getGetPost('postData');
        if (strlen($data) > 0) {
            $data = $this->validform_Tf((array)json_decode($data, true));
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            $oldData = $this->db->table('bill_inventory')->getWhere(['id' => $data['id']])->getRowArray();
            $info = array_intersect_key($data, array_flip(['billDate', 'goods', 'inStorageId', 'outStorageId', 'description', 'totalQty', 'modifyTime']));
            $this->db->transStart();
            $this->db->table('bill_inventory')->where('id', $data['id'])->update($info);
            if ($data['goods'] != $oldData['goods']) {
                $this->inventory_info_Tf(intval($data['id']), $data);
            }
            $this->myservice->log('编辑了库存调拨单，单号：' . $data['billNo']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '编辑成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 库存调拨单 删除
    public function deleteTf()
    {
        if (!$this->myservice->verifyRight('TF_DELETE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvIo/deleteTf 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('bill_inventory')->where(['id' => $id, 'billType' => 'TRANSFER'])->get()->getRowArray();
        if ($data) {
            if (!$this->myservice->verifyRight('SYS_ADMIN') && $data['uId'] != $this->myerp['uId']) {
                return $this->response->setJSON(['status' => -1, 'msg' => '不能删除别人录入的库存调拨单！仅本人或管理员可删除！']);
            }
            $this->db->transBegin();
            $this->db->table('bill_inventory')->where('id', $id)->update(['isDelete' => 1]);
            $this->db->table('bill_inventory_info')->where(['iid' => $id])->update(['isDelete' => 1]);
            $this->myservice->log('删除了库存调拨单，单号：' . $data['billNo']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '删除成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 库存调拨单 info数据
    private function inventory_info_Tf(int $iid, array $data)
    {
        $v = $s = [];
        foreach ($data['entries'] as $arr => $row) {
            $v[] = [
                'iid' => $iid,
                'billDate' => $data['billDate'],
                'billNo' => $data['billNo'],
                'billType' => $data['billType'],
                'transType' => $data['transType'],
                'goodsId' => intval($row['goodsId']),
                'qty' => abs($row['qty']),
                'storageId' => intval($row['inStorageId']),
                'entryId' => 1,
                'description' => $row['description']
            ];
            $s[] = [
                'iid' => $iid,
                'billDate' => $data['billDate'],
                'billNo' => $data['billNo'],
                'billType' => $data['billType'],
                'transType' => $data['transType'],
                'goodsId' => intval($row['goodsId']),
                'qty' => abs($row['qty']),
                'storageId' => intval($row['outStorageId']),
                'entryId' => 2,
                'description' => $row['description']
            ];
        }
        if (!empty($s) && !empty($v)) {
            if (isset($data['id']) && $data['id'] > 0) {
                // 真删除
                $this->db->table('bill_inventory_info')->where(['iid' => $iid])->delete();
            }
            $this->db->table('bill_inventory_info')->insertBatch($v);
            $this->db->table('bill_inventory_info')->insertBatch($s);
        }
    }
    // 库存调拨单 验证
    private function validform_Tf(array $data)
    {
        $data['id'] = isset($data['id']) ? intval($data['id']) : -1;
        $data['billDate'] = $data['billDate'];
        $data['billType'] = 'TF';
        $data['transType'] = 102403;
        $data['totalQty'] = (float)$data['totalQty'];
        $data['description'] = $data['description'];
        $data['uId'] = $data['checkId'] = $this->myerp['uId'];
        $data['uName'] = $data['checkName'] = $this->myerp['uName'];
        $data['modifyTime'] = date('Y-m-d H:i:s');
        $data['createTime'] = $data['modifyTime'];
        $data['entries'] = $data['entries'] ?? [];
        if (count($data['entries']) < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
        }
        if ($data['id'] > 0) {
            if ($this->db->table('bill_inventory')->where(['id' => $data['id'], 'isDelete' => 0])->countAllResults() < 1) {
                return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
            }
        } else {
            if ($this->db->table('bill_inventory')->where(['billNo' => $data['billNo']])->countAllResults() > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '单据编号重复！']);
            }
        }
        //产品录入验证
        $storage = array_column($this->db->table('storage')->select('id')->where(['isDisable' => 0, 'isDelete' => 0])->get()->getResultArray(), 'id');
        $inStorageId = $outStorageId = [];
        foreach ($data['entries'] as $arr => $row) {
            if ((float)$row['qty'] < 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '数量要为正数！']);
            }
            if (intval($row['outStorageId']) < 1) {
                return $this->response->setJSON(['status' => -1, 'msg' => '请选择调出仓库仓库！']);
            }
            if (intval($row['inStorageId']) < 1) {
                return $this->response->setJSON(['status' => -1, 'msg' => '请选择调入仓库仓库！']);
            }
            if (intval($row['outStorageId']) == intval($row['inStorageId'])) {
                return $this->response->setJSON(['status' => -1, 'msg' => '调出仓库不能与调入仓库相同！']);
            }
            if (!in_array($row['outStorageId'], $storage)) {
                return $this->response->setJSON(['status' => -1, 'msg' => $row['outStorageName'] . '不存在或不可用！']);
            }
            if (!in_array($row['inStorageId'], $storage)) {
                return $this->response->setJSON(['status' => -1, 'msg' => $row['inStorageName'] . '不存在或不可用！']);
            }
            $data['goods'][$arr]['id'] = $row['goodsId'];
            $data['goods'][$arr]['no'] = $row['invNo'];
            $data['goods'][$arr]['name'] = $row['invName'];
            $data['goods'][$arr]['spec'] = $row['invSpec'];
            $data['goods'][$arr]['sku'] = $row['invSku'];
            $data['goods'][$arr]['brandName'] = $row['brandName'];
            $data['goods'][$arr]['unitName'] = $row['unitName'];
            $data['goods'][$arr]['inStorageId'] = intval($row['inStorageId']);
            $data['goods'][$arr]['inStorageName'] = $row['inStorageName'];
            $data['goods'][$arr]['outStorageId'] = intval($row['outStorageId']);
            $data['goods'][$arr]['outStorageName'] = $row['outStorageName'];
            $data['goods'][$arr]['qty'] = intval($row['qty']);
            $data['goods'][$arr]['description'] = $row['description'];
            $inStorageId[] = $row['inStorageId'];
            $outStorageId[] = $row['outStorageId'];
        }
        $data['goods'] = json_encode($data['goods'], 320);
        $data['inStorageId'] = join(',', array_unique($inStorageId));
        $data['outStorageId'] = join(',', array_unique($outStorageId));
        return $data;
    }

    // 成本调整单 列表
    public function listCa()
    {
        if (!$this->myservice->verifyRight('CA_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvIo/listCa 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $skey = $this->request->getGetPost('skey');
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');
        $storageId = intval($this->request->getGetPost('storageId'));
        $builder = $this->db->table('bill_inventory');
        $builder->where('billType', 'CA');
        if ($beginDate) {
            $builder->where('billDate >=', $beginDate);
        }
        if ($endDate) {
            $builder->where('billDate <=', $endDate);
        }
        if ($storageId > 0) {
            $builder->where('FIND_IN_SET(' . $storageId . ', storageId) >', 0, false);
        }
        if ($skey !== '' && $skey !== null) {
            $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
            foreach ($keywords as $kw) {
                $builder->groupStart()
                    ->like('billNo', $this->db->escapeLikeString($kw))
                    ->orLike('description', $this->db->escapeLikeString($kw))
                    ->orLike('goods', $this->db->escapeLikeString($kw))
                ->groupEnd();
            }
        }
        $builder->where('isDelete', 0);
        $allowedSort = ['id','billNo','billDate','uName'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $list = (clone $builder)->orderBy($orderField, $sord)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[] = [
                'id' => intval($row['id']),
                'billDate' => $row['billDate'],
                'billNo' => $row['billNo'],
                'billType' => $row['billType'],
                'transType' => intval($row['transType']),
                'amount' => abs((float)$row['amount']),
                'uName' => $row['uName'],
                'description' => $row['description'],
                'goods' => (array)json_decode($row['goods'], true)
            ];
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['page'] = $page;
        $json['data']['records'] = $total;
        $json['data']['total'] = ceil($json['data']['records'] / $rows);
        $json['data']['rows'] = $v;
        return $this->response->setJSON($json);
    }
    // 成本调整单 查询
    public function queryCa()
    {
        if (!$this->myservice->verifyRight('CA_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvIo/queryCa 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('bill_inventory')->where('id', $id)->where('isDelete', 0)->get()->getRowArray();
        if (is_array($data) && count($data) > 0) {
            $info['id'] = intval($data['id']);
            $info['billDate'] = $data['billDate'];
            $info['billNo'] = $data['billNo'];
            $info['billType'] = $data['billType'];
            $info['modifyTime'] = $data['modifyTime'];
            $info['createTime'] = $data['createTime'];
            $info['transType'] = intval($data['transType']);
            $info['totalQty'] = intval($data['totalQty']);
            $info['amount'] = (float)$data['amount'];
            $info['uName'] = $data['uName'];
            $info['description'] = $data['description'];
            $list = $this->db->table('bill_inventory_info AS a')
                ->select([
                    'a.*',
                    'b.id AS goodsId',
                    'b.no AS invNo',
                    'b.name AS invName',
                    'b.spec AS invSpec',
                    'b.sku AS invSku',
                    'b.brandName',
                    'b.unitName',
                    'b.producePrice3',
                    'b.isProofread',
                    'c.id AS storageId',
                    'c.name AS storageName'
                ])
                ->join('goods AS b', 'a.goodsId = b.id AND b.isDelete = 0', 'left')
                ->join('storage AS c', 'a.storageId = c.id AND c.isDelete = 0', 'left')
                ->where('a.iid', $id)
                ->where('a.isDelete', 0)
                ->orderBy('a.id', 'ASC')
                ->get()
                ->getResultArray();
            $v = [];
            foreach ($list as $arr => $row) {
                $v[$arr] = [
                    'id' => intval($row['id']),
                    'goodsId' => intval($row['goodsId']),
                    'invNo' => $row['invNo'],
                    'invName' => $row['invName'],
                    'invSpec' => $row['invSpec'],
                    'invSku' => $row['invSku'],
                    'brandName' => $row['brandName'],
                    'unitName' => $row['unitName'],
                    'storageId' => intval($row['storageId']),
                    'storageName' => $row['storageName'],
                    'amount' => abs((float)$row['amount']),
                    'description' => $row['description']
                ];
            }
            $info['entries'] = $v;
            $json['status'] = 200;
            $json['msg'] = 'success';
            $json['data'] = $info;
            return $this->response->setJSON($json);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 成本调整单 新增
    public function addCa()
    {
        if (!$this->myservice->verifyRight('CA_ADD')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvIo/addCa 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getGetPost('postData');
        if (strlen($data) > 0) {
            $data = $this->validform_Ca((array)json_decode($data, true));
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            $info = array_intersect_key($data, array_flip([
                'billNo', 'billDate', 'billType', 'transType', 'amount', 'storageId', 'description', 'goods',
                'uId', 'uName', 'checkId', 'checkName', 'createTime', 'modifyTime'
            ]));
            $this->db->transStart();
            $this->db->table('bill_inventory')->insert($info);
            $iid = $this->db->insertID();
            $this->inventory_info_Ca(intval($iid), $data);
            $this->myservice->log('新增了成本调整单，单号：' . $data['billNo']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '新增成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    // 成本调整单 编辑
    public function updateCa()
    {
        if (!$this->myservice->verifyRight('CA_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvIo/updateCa 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getGetPost('postData');
        if (strlen($data) > 0) {
            $data = $this->validform_Ca((array)json_decode($data, true));
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            $oldData = $this->db->table('bill_inventory')->getWhere(['id' => $data['id']])->getRowArray();
            $info = array_intersect_key($data, array_flip(['billDate', 'totalQty', 'amount', 'storageId', 'description', 'goods', 'modifyTime']));
            $this->db->transStart();
            $this->db->table('bill_inventory')->where('id', $data['id'])->update($info);
            $this->myservice->log('编辑了成本调整单，单号：' . $data['billNo']);
            if ($data['goods'] != $oldData['goods']) {
                $this->inventory_info_Ca(intval($data['id']), $data);
            } else {
                $this->inventory_info_Ca_update(intval($data['id']), $data);
            }
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '编辑成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 成本调整单 删除
    public function deleteCa()
    {
        if (!$this->myservice->verifyRight('CA_DELETE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvIo/deleteCa 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('bill_inventory')->where(['id' => $id])->get()->getRowArray();
        if ($data) {
            if (!$this->myservice->verifyRight('SYS_ADMIN') && $data['uId'] != $this->myerp['uId']) {
                return $this->response->setJSON(['status' => -1, 'msg' => '不能删除别人录入的成本调整单！仅本人或管理员可删除！']);
            }
            $this->db->transStart();
            $this->db->table('bill_inventory')->where('id', $id)->update(['isDelete' => 1]);
            $this->db->table('bill_inventory_info')->where('iid', $id)->update(['isDelete' => 1]);
            $this->myservice->log('删除了成本调整单，单号：' . $data['billNo']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '删除成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 成本调整单 info数据
    private function inventory_info_Ca(int $iid, array $data)
    {
        $v = [];
        foreach ($data['entries'] as $arr => $row) {
            $v[] = [
                'iid' => $iid,
                'billNo' => $data['billNo'],
                'transType' => $data['transType'],
                'billDate' => $data['billDate'],
                'billType' => $data['billType'],
                'goodsId' => intval($row['goodsId']),
                'storageId' => intval($row['storageId']),
                'amount' => abs((float)$row['amount']),
                'description' => $row['description']
            ];
        }
        if (!empty($v)) {
            if ($data['id'] > 0) {
                // 真删除
                $this->db->table('bill_inventory_info')->where(['iid' => $iid])->delete();
            }
            $this->db->table('bill_inventory_info')->insertBatch($v);
        }
    }
    // 成本调整单 子条目 仅更新
    private function inventory_info_Ca_update(int $iid, array $data)
    {
        $v = [];
        foreach ($data['entries'] as $arr => $row) {
            $v[] = [
<<<<<<< HEAD
                'id' => intval($row['id']),
                'iid' => intval($iid),
=======
                'id' => $row['id'],
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
                'storageId' => intval($row['storageId']),
                'description' => $row['description']
            ];
        }
        if (!empty($v)) {
<<<<<<< HEAD
            $this->db->table('bill_inventory_info')->updateBatch($v, ['id', 'iid']);
=======
            $this->db->table('bill_inventory_info')->updateBatch($v, 'id');
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
        }
    }
    // 成本调整单 验证
    private function validform_Ca(array $data)
    {
        $data['id'] = isset($data['id']) ? intval($data['id']) : -1;
        $data['billDate'] = $data['billDate'];
        $data['billType'] = 'CA';
        $data['transType'] = 102404;
        $data['amount'] = (float)$data['amount'];
        $data['uId'] = $data['checkId'] = $this->myerp['uId'];
        $data['uName'] = $data['checkName'] = $this->myerp['uName'];
        $data['modifyTime'] = date('Y-m-d H:i:s');
        $data['createTime'] = $data['modifyTime'];
        $data['entries'] = $data['entries'] ?? [];
        if (count($data['entries']) < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
        }
        if ($data['id'] > 0) {
            if ($this->db->table('bill_inventory')->where(['id' => $data['id'], 'isDelete' => 0])->countAllResults() < 1) {
                return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
            }
        } else {
            if ($this->db->table('bill_inventory')->where(['billNo' => $data['billNo']])->countAllResults() > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '单据编号重复！']);
            }
        }
        $storage = array_column($this->db->table('storage')->select('id')->where(['isDisable' => 0, 'isDelete' => 0])->get()->getResultArray(), 'id');
        $storageId = [];
        foreach ($data['entries'] as $arr => $row) {
            if (intval($row['goodsId']) < 1) {
                return $this->response->setJSON(['status' => -1, 'msg' => '选择产品']);
            }
            if ((float)$row['amount'] < 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '调整金额要为正数！']);
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
            $data['goods'][$arr]['unit'] = $row['unitName'];
            $data['goods'][$arr]['amount'] = abs((float)$row['amount']);
            $storageId[] = $row['storageId'];
        }
        $data['goods'] = json_encode($data['goods'], 320);
        $data['storageId'] = join(',', array_unique($storageId));
        return $data;
    }

    // 单独更新备注 统一方法
    public function updatedes()
    {
        $id = intval($this->request->getGetPost('id'));
        $newDes = $this->request->getGetPost('newDes');
        $data = $this->db->table('bill_inventory')->where(['id' => $id])->get()->getRowArray();
        if ($data) {
            switch ($data['billType']) {
                case 'OI':
                    if (!$this->myservice->verifyRight('OI_UPDATE')) {
                        log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvIo/updatedes 权限不足！');
                        return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
                    }
                    break;
                case 'OO':
                    if (!$this->myservice->verifyRight('OO_UPDATE')) {
                        log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvIo/updatedes 权限不足！');
                        return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
                    }
                    break;
                case 'TF':
                    if (!$this->myservice->verifyRight('TF_UPDATE')) {
                        log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvIo/updatedes 权限不足！');
                        return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
                    }
                    break;
                case 'CA':
                    if (!$this->myservice->verifyRight('CA_UPDATE')) {
                        log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvIo/updatedes 权限不足！');
                        return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
                    }
                    break;
            }
            $v['description'] = $newDes;
            $v['modifyTime'] = date("Y-m-d H:i:s");
            $this->db->transStart();
            $this->db->table('bill_inventory')->where('id', $id)->update($v);
            $this->myservice->log('单独更新了' . $this->myerp['transTypeName'][$data['transType']] . '的备注，单号：' . $data['billNo']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '更新成功！', 'data' => ['id' => $id]]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
}
