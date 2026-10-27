<?php

namespace App\Controllers\Bill;
use App\Controllers\BaseController;

class InvPu extends BaseController
{
    // 列表
    public function list()
    {
        if (!$this->myservice->verifyRight('PU_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvPu/list 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $transType = intval($this->request->getGetPost('transType'));
        $status1 = $this->request->getGetPost('status1');
        $status2 = intval($this->request->getGetPost('status2'));
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');
        $skey = $this->request->getGetPost('skey');
        $builder = $this->db->table('bill_inventory');
        $builder->where('billType', 'PUR');
        if ($transType) {
            $builder->where('transType', $transType);
        }
        if ($status1) {
            $builder->groupStart()
                ->where('FIND_IN_SET(' . $this->db->escape($status1) . ', pusaName) >', 0, false)
                ->orWhere('uName', $status1)
            ->groupEnd();
        }
        if ($status2 >= 0) {
            $builder->where('goodsStatus', $status2);
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
                    ->orLike('srcOrderNo', $this->db->escapeLikeString($kw))
                    ->orLike('description', $this->db->escapeLikeString($kw))
                    ->orLike('goods', $this->db->escapeLikeString($kw))
                    ->orWhere('uName', $kw)
                    ->orWhere('checkName', $kw)
                    ->orLike('contactName', $this->db->escapeLikeString($kw))
                    ->orWhere('FIND_IN_SET(' . $this->db->escape($kw) . ', pusaName) >', 0, false)
                ->groupEnd();
            }
        }
        $builder->where('isDelete', 0);
        $allowedSort = ['id','billNo','billDate','description','uName','contactName','srcOrderNo','goodsStatus'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $list = (clone $builder)->orderBy($orderField, $sord)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['billDate'] = $row['billDate'];
            $v[$arr]['billNo'] = $row['billNo'];
            $v[$arr]['transType'] = intval($row['transType']);
            $v[$arr]['contactId'] = intval($row['contactId']);
            $v[$arr]['contactName'] = $row['contactName'];
            $v[$arr]['totalOrderQty'] = intval($row['totalOrderQty']);
            $v[$arr]['totalQty'] = intval($row['totalQty']);
            $v[$arr]['amount'] = abs((float)$row['amount']);
            $v[$arr]['uName'] = $row['uName'];
            $v[$arr]['checkId'] = intval($row['checkId']);
            $v[$arr]['checkName'] = $row['checkName'];
            $v[$arr]['goodsStatus'] = intval($row['goodsStatus']);
            $v[$arr]['billStatusName'] = intval($row['goodsStatus']) == 0 ? (intval($row['transType']) == 102101 ? '未入库' : '未出库') : (intval($row['goodsStatus']) == 1 ? (intval($row['transType']) == 102101 ? '部分入库' : '部分出库') : (intval($row['goodsStatus']) == 2 ? (intval($row['transType']) == 102101 ? '全部入库' : '全部出库') : '直发'));
            $v[$arr]['description'] = $row['description'];
            $v[$arr]['srcOrderId'] = intval($row['srcOrderId']);
            $v[$arr]['srcOrderNo'] = $row['srcOrderNo'];
            $v[$arr]['goods'] = (array)json_decode($row['goods'], true);
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
        if (!$this->myservice->verifyRight('PU_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvPu/query 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('bill_inventory')->where(['id' => $id, 'billType' => 'PUR', 'isDelete' => 0])->get()->getRowArray();
        if ($data) {
            $info['id'] = intval($data['id']);
            $info['billDate'] = $data['billDate'];
            $info['billNo'] = $data['billNo'];
            $info['billType'] = $data['billType'];
            $info['transType'] = intval($data['transType']);
            $info['totalOrderQty'] = abs(intval($data['totalOrderQty']));
            $info['totalQty'] = abs(intval($data['totalQty']));
            $info['amount'] = abs((float)$data['amount']);
            $info['goodsStatus'] = intval($data['goodsStatus']);
            $info['uId'] = intval($data['uId']);
            $info['uName'] = $data['uName'];
            $info['contactId'] = intval($data['contactId']);
            $info['checkId'] = intval($data['checkId']);
            $info['checkName'] = $data['checkName'];
            $info['srcOrderId'] = intval($data['srcOrderId']);
            $info['srcOrderNo'] = $data['srcOrderNo'];
            $info['description'] = $data['description'];
            $info['createTime'] = $data['createTime'];
            $info['modifyTime'] = $data['modifyTime'];
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
                $v[$arr]['qty'] = abs(intval($row['qty']));
                $v[$arr]['price'] = abs((float)$row['price']);
                $v[$arr]['amount'] = abs((float)$row['amount']);
                $v[$arr]['nowQty'] = 0;
                $v[$arr]['description'] = $row['description'];
            }
            $logList = $this->db->table('bill_inventory_log AS a')
                ->select([
                    'a.*',
                    'b.id AS goodsId',
                    'b.no AS invNo',
                    'b.name AS invName',
                    'b.spec AS invSpec',
                    'b.sku AS invSku',
                    'b.brandName',
                    'b.unitName',
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
            $w = [];
            foreach ($logList as $arr1 => $row1) {
                $w[$arr1]['createTime'] = $row1['createTime'];
                $w[$arr1]['uName'] = $row1['uName'];
                $w[$arr1]['goodsId'] = intval($row1['goodsId']);
                $w[$arr1]['invNo'] = $row1['invNo'];
                $w[$arr1]['invName'] = $row1['invName'];
                $w[$arr1]['invSpec'] = $row1['invSpec'];
                $w[$arr1]['invSku'] = $row1['invSku'];
                $w[$arr1]['brandName'] = $row1['brandName'];
                $w[$arr1]['unitName'] = $row1['unitName'];
                $w[$arr1]['storageId'] = intval($row1['storageId']);
                $w[$arr1]['storageName'] = $row1['storageName'];
                $w[$arr1]['qty'] = intval($row1['qty']);
                $w[$arr1]['deliverCompany'] = $row1['deliverCompany'];
                $w[$arr1]['deliverNo'] = $row1['deliverNo'];
            }
            $info['entries'] = $v;
            $info['log'] = $w;
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
        if (!$this->myservice->verifyRight('PU_ADD')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvPu/add 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getGetPost('postData');
        if (strlen($data) > 0) {
            $data = $this->validform((array)json_decode($data, true));
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            if (0 < $data['totalQty'] && $data['totalQty'] < $data['totalOrderQty']) {
                $data['goodsStatus'] = 1;
            } else if (0 < $data['totalQty'] && $data['totalQty'] >= $data['totalOrderQty']) {
                $data['goodsStatus'] = 2;
            } else {
                $data['goodsStatus'] = 0;
            }
            $info = array_intersect_key($data, array_flip(['billNo', 'billDate', 'billType', 'transType', 'totalOrderQty', 'totalQty', 'amount', 'goodsStatus', 'uId', 'uName', 'checkId', 'checkName', 'contactId', 'contactName', 'srcOrderId', 'srcOrderNo', 'goods', 'description',  'createTime', 'modifyTime']));
            $this->db->transStart();
            $this->db->table('bill_inventory')->insert($info);
            $iid = $this->db->insertID();
            $this->inventory_info(intval($iid), $data);
            $this->inventory_log(intval($iid), $data);
            $data['id'] = $iid;
            if ($data['srcOrderId'] > 0) {
                if ($data['transType'] == 102101) {
                    $this->db->table('bill_order')->where(['id' => $data['srcOrderId']])->update(['goodsStatus1' => $data['goodsStatus'], 'srcInvId1' => $data['id'], 'srcInvNo1' => $data['billNo']]);
                } elseif ($data['transType'] == 102102) {
                    $this->db->table('bill_order')->where(['id' => $data['srcOrderId']])->update(['goodsStatus2' => $data['goodsStatus'], 'srcInvId2' => $data['id'], 'srcInvNo2' => $data['billNo']]);
                }
            }
            $this->myservice->log('新增了' . $this->myerp['transTypeName'][$data['transType']] . '，单号：' . $data['billNo']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '新增成功！', 'data' => ['id' => $data['id']]]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    // 编辑
    public function update()
    {
        if (!$this->myservice->verifyRight('PU_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvPu/update 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getGetPost('postData');
        if (strlen($data) > 0) {
            $data = $this->validform((array)json_decode($data, true));
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            if (0 < $data['totalQty'] && $data['totalQty'] < $data['totalOrderQty']) {
                $data['goodsStatus'] = 1;
            } else if (0 < $data['totalQty'] && $data['totalQty'] >= $data['totalOrderQty']) {
                $data['goodsStatus'] = 2;
            } else {
                $data['goodsStatus'] = 0;
            }
            $info = array_intersect_key($data, array_flip(['totalOrderQty', 'totalQty', 'goodsStatus', 'description', 'modifyTime']));
            $this->db->transStart();
            $this->db->table('bill_inventory')->where(['id' => $data['id']])->update($info);
            $this->inventory_info(intval($data['id']), $data);
            $this->inventory_log(intval($data['id']), $data);
            if ($data['transType'] == 102101) {
                $this->db->table('bill_order')->where(['id' => $data['srcOrderId']])->update(['goodsStatus1' => $data['goodsStatus']]);
            }
            if ($data['transType'] == 102102) {
                $this->db->table('bill_order')->where(['id' => $data['srcOrderId']])->update(['goodsStatus2' => $data['goodsStatus']]);
            }
            $this->myservice->log('编辑了' . $this->myerp['transTypeName'][$data['transType']] . '，单号：' . $data['billNo']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '新增成功！', 'data' => ['id' => $data['id']]]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    // 删除
    public function delete()
    {
        if (!$this->myservice->verifyRight('PU_DELETE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvPu/delete 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('bill_inventory')->where(['id' => $id, 'billType' => 'PUR', 'isDelete' => 0])->get()->getRowArray();
        if ($data) {
            if (!$this->myservice->verifyRight('SYS_ADMIN') && $data['uId'] != $this->myerp['uId']) {
                return $this->response->setJSON(['status' => -1, 'msg' => '不能删除别人录入的' . $this->myerp['transTypeName'][$data['transType']] . '！仅本人或管理员可删除！']);
            }
            $this->db->transStart();
            $inventory_info = $this->db->table('bill_inventory_info')->where(['iid' => $data['id'], 'billType' => 'PUR', 'isDelete' => 0])->get()->getResultArray();
            if (!empty($inventory_info)) {
                foreach ($inventory_info as $arr => $row) {
                    $this->db->table('goods')->set('currentQty', 'currentQty' . (102101 == $row['transType'] ? ' - ' : ' + ') . $row['qty'], false)->where(['id' => $row['goodsId']])->update();
                    $newGoods = $this->db->table('goods')->select('currentQty')->where(['id' => $row['goodsId']])->get()->getRowArray();
                    if ($newGoods) {
                        $invHisData['goodsId'] = $row['goodsId'];
                        $invHisData['billId'] = $row['iid'];
                        $invHisData['billNo'] = $row['billNo'];
                        $invHisData['billType'] = 'PU';
                        $invHisData['transType'] = $row['transType'];
                        $invHisData['changeType'] = '删除' . $this->myerp['transTypeName'][$row['transType']];
                        $invHisData['changeTime'] = date('Y-m-d H:i:s');
                        $invHisData['changeCurrentQty'] = 102101 == $row['transType'] ? -abs((float)$row['qty']) : abs((float)$row['qty']);
                        $invHisData['currentQty'] = (float)$newGoods['currentQty'];
                        $invHisInfo = array_intersect_key($invHisData, array_flip(['goodsId', 'billId', 'billNo', 'billType', 'transType', 'changeType', 'changeTime', 'changeCurrentQty', 'currentQty']));
                        $this->db->table('inv_history')->insert($invHisInfo);
                    }
                }
            }
            $this->db->table('bill_inventory')->where(['id' => $id])->update(['isDelete' => 1]);
            $this->db->table('bill_inventory_info')->where(['iid' => $id])->update(['isDelete' => 1]);
            $this->db->table('bill_inventory_log')->where(['iid' => $id])->update(['isDelete' => 1]);
            if ($data['transType'] == 102101) {
                $this->db->table('bill_order')->where(['srcInvId1' => $id])->update(['srcInvId1' => 0, 'srcInvNo1' => '', 'goodsStatus1' => 0]);
            } elseif ($data['transType'] == 102102) {
                $this->db->table('bill_order')->where(['srcInvId2' => $id])->update(['srcInvId2' => 0, 'srcInvNo2' => '', 'goodsStatus2' => 0]);
            }
            $this->myservice->log('删除了' . $this->myerp['transTypeName'][$data['transType']] . '，单号：' . $data['billNo']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '删除成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 单独更新备注
    public function updatedes()
    {
        if (!$this->myservice->verifyRight('PU_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvPu/updatedes 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $newDes = $this->request->getGetPost('newDes');
        $data = $this->db->table('bill_inventory')->where(['id' => $id])->get()->getRowArray();
        if ($data) {
            $v['description'] = $newDes;
            $v['modifyTime'] = date("Y-m-d H:i:s");
            $this->db->transStart();
            $this->db->table('bill_inventory')->where(['id' => $id])->update($v);
            $this->myservice->log('单独更新了' . $this->myerp['transTypeName'][$data['transType']] . '的备注，单号：' . $data['billNo']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '更新成功！', 'data' => ['id' => $id]]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // info数据
    private function inventory_info(int $iid, array $data)
    {
        $v = [];
        foreach ($data['entries'] as $arr => $row) {
            $v[] = [
                'iid' => $iid,
                'billDate' => $data['billDate'],
                'billNo' => $data['billNo'],
                'billType' => $data['billType'],
                'transType' => $data['transType'],
                'goodsId' => intval($row['goodsId']),
                'storageId' => intval($row['storageId']),
                'orderQty' => intval($row['orderQty']),
                'qty' => (intval($row['qty']) + intval($row['nowQty'])) > 0 ? (intval($row['qty']) + intval($row['nowQty'])) : 0,
                'price' => abs((float)$row['price']),
                'amount' => abs((float)$row['amount']),
                'srcOrderId' => intval($data['srcOrderId']),
                'description' => $row['description']
            ];
        }
        if (!empty($v)) {
            if (isset($data['id']) && $data['id'] > 0) {
                $inventory_info = $this->db->table('bill_inventory_info')->where(['iid' => $data['id'], 'billType' => 'PUR', 'isDelete' => 0])->get()->getResultArray();
                if (!empty($inventory_info)) {
                    foreach ($inventory_info as $arr1 => $row1) {
                        $this->db->table('goods')->set('currentQty', 'currentQty' . (102101 == $row1['transType'] ? ' - ' : ' + ') . $row1['qty'], false)->where('id', $row1['goodsId'])->update();
                        $newGoods = $this->db->table('goods')->select('currentQty')->where('id', $row1['goodsId'])->get()->getRowArray();
                        if ($newGoods) {
                            $invHisData['goodsId'] = $row1['goodsId'];
                            $invHisData['billId'] = $row1['iid'];
                            $invHisData['billNo'] = $row1['billNo'];
                            $invHisData['billType'] = 'PU';
                            $invHisData['transType'] = $row1['transType'];
                            $invHisData['changeType'] = '清空' . $this->myerp['transTypeName'][$row1['transType']];
                            $invHisData['changeTime'] = date('Y-m-d H:i:s');
                            $invHisData['changeCurrentQty'] = 102101 == $row1['transType'] ? -abs((float)$row1['qty']) : abs((float)$row1['qty']);
                            $invHisData['currentQty'] = (float)$newGoods['currentQty'];
                            $invHisInfo = array_intersect_key($invHisData, array_flip(['goodsId', 'billId', 'billNo', 'billType', 'transType', 'changeType', 'changeTime', 'changeCurrentQty', 'currentQty']));
                            $this->db->table('inv_history')->insert($invHisInfo);
                        }
                    }
                }
                // 真删除
                $this->db->table('bill_inventory_info')->where(['iid' => $data['id'], 'billType' => 'PUR'])->delete();
            }
            $this->db->table('bill_inventory_info')->insertBatch($v);
            foreach ($v as $arr2 => $row2) {
                $this->db->table('goods')->set('currentQty', 'currentQty' . (102101 == $row2['transType'] ? ' + ' : ' - ') . $row2['qty'], false)->where('id', $row2['goodsId'])->update();
                $newGoods = $this->db->table('goods')->select('currentQty')->where('id', $row2['goodsId'])->get()->getRowArray();
                if ($newGoods) {
                    $invHisData['goodsId'] = $row2['goodsId'];
                    $invHisData['billId'] = $row2['iid'];
                    $invHisData['billNo'] = $row2['billNo'];
                    $invHisData['billType'] = 'PU';
                    $invHisData['transType'] = $row2['transType'];
                    $invHisData['changeType'] = ((isset($data['id']) && $data['id'] > 0) ? '编辑' : '新增') . $this->myerp['transTypeName'][$row2['transType']];
                    $invHisData['changeTime'] = date('Y-m-d H:i:s');
                    $invHisData['changeCurrentQty'] = 102101 == $row2['transType'] ? abs((float)$row2['qty']) : -abs((float)$row2['qty']);
                    $invHisData['currentQty'] = (float)$newGoods['currentQty'];
                    $invHisInfo = array_intersect_key($invHisData, array_flip(['goodsId', 'billId', 'billNo', 'billType', 'transType', 'changeType', 'changeTime', 'changeCurrentQty', 'currentQty']));
                    $this->db->table('inv_history')->insert($invHisInfo);
                }
            }
        }
    }
    // 组装log数据
    private function inventory_log(int $iid, array $data)
    {
        $v = $x = [];
        foreach ($data['entries'] as $arr => $row) {
            if (intval($row['nowQty']) == 0) {
                continue;
            }
            $v[] = [
                'iid' => $iid,
                'srcOrderId' => intval($data['srcOrderId']),
                'billType' => $data['billType'],
                'transType' => $data['transType'],
                'createTime' => date('Y-m-d H:i:s'),
                'uName' => $this->myerp['uName'],
                'goodsId' => intval($row['goodsId']),
                'storageId' => intval($row['storageId']),
                'locationId' => isset($row['locationId']) ? intval($row['locationId']) : 0,
                'qty' => intval($row['nowQty']),
                'deliverCompany' => $row['deliverCompany'],
                'deliverNo' => $row['deliverNo']
            ];
            if (strlen($row['deliverNo']) > 0) {
                $x[] = $row['deliverCompany'].$row['deliverNo'];
            }
        }
        if (!empty($v)) {
            $this->db->table('bill_inventory_log')->insertBatch($v);
        }
        if (!empty($x)) {
            $x = array_unique($x);
            $des = implode('，', $x);
            $this->db->table('bill_order')->set('description', 'CONCAT(description, "\r\n物流单号：' . $des . '。")', false)->where('id', $data['srcOrderId'])->update();
            $this->db->table('bill_inventory')->set('description', 'CONCAT(description, "\r\n物流单号：' . $des . '。", description)', false)->where('id', $iid)->update();
        }
    }
    // 数据验证
    private function validform(array $data)
    {
        $data['id'] = isset($data['id']) ? intval($data['id']) : -1;
        $data['billDate'] = $data['billDate'];
        $data['billType'] = 'PUR';
        $data['transType'] = intval($data['transType']);
        $data['totalOrderQty'] = abs(intval($data['totalOrderQty']));
        $data['totalQty'] = intval($data['totalQty']) + intval($data['totalNowQty']);
        $data['amount'] = abs((float)$data['amount']);
        $data['entries'] = $data['entries'] ?? [];
        $data['uId'] = $data['checkId'] = $this->myerp['uId'];
        $data['uName'] = $data['checkName'] = $this->myerp['uName'];
        $data['createTime'] = date('Y-m-d H:i:s');
        $data['modifyTime'] = date('Y-m-d H:i:s');
        if (strlen($data['billNo']) < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '单据编号不能为空！']);
        }
        if (count($data['entries']) < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '产品信息不能为空！']);
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
        foreach ($data['entries'] as $arr => $row) {
            if (intval($row['goodsId']) < 1) {
                return $this->response->setJSON(['status' => -1, 'msg' => '请选择产品！']);
            }
            if ((float)$row['qty'] < 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '产品数量要为正数！']);
            }
            if ((float)$row['price'] < 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '产品单价要为正数！']);
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
            $data['goods'][$arr]['qty'] = abs(intval($row['orderQty']));
        }
        $data['goods'] = json_encode($data['goods'], 320);
        return $data;
    }
}
