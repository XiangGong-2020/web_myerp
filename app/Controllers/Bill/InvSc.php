<?php

namespace App\Controllers\Bill;
use App\Controllers\BaseController;

class InvSc extends BaseController
{
    // 列表
    public function list()
    {
        if (!$this->myservice->verifyRight('SC_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvSc/list 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $order = $sidx ? $sidx . ' ' . $sord : 'id desc';
        $status0 = intval($this->request->getGetPost('status0'));
        $status1 = $this->request->getGetPost('status1');
        $status2 = intval($this->request->getGetPost('status2'));
        $status3 = intval($this->request->getGetPost('status3'));
        $status4 = intval($this->request->getGetPost('status4'));
        $status5 = intval($this->request->getGetPost('status5'));
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');
        $minAmount = (float)$this->request->getGetPost('minAmount');
        $maxAmount = (float)$this->request->getGetPost('maxAmount');
        $skey = $this->request->getGetPost('skey');
        $unClear = intval($this->request->getGetPost('unClear'));
        $unDeliver = intval($this->request->getGetPost('unDeliver'));
        $builder = $this->db->table('bill_contract');
        $builder->where('billType', 'SALE');
        if ($status0 > 0) {
            $builder->where('sId', $status0);
        }
        if ($status1) {
            $builder->where('FIND_IN_SET(' . $this->db->escape($status1) . ', saler) >', 0, false);
        }
        if ($status2 >= 0) {
            $builder->where('moneyStatus1', $status2);
        }
        if ($status3 >= 0) {
            $builder->where('goodsStatus1', $status3);
        }
        if ($status4 >= 0) {
            $builder->where('moneyStatus2', $status4);
        }
        if ($status5 >= 0) {
            $builder->where('goodsStatus2', $status5);
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
        if ($unClear == 1) {
            $builder->where('moneyStatus1', 0);
        } elseif ($unClear == 2) {
            $builder->where('moneyStatus1 <', 2);
        }
        if ($unDeliver == 1) {
            $builder->where('endDate <=', date('Y-m-d'))->where('redoSoType', 0)->where('isCancel', 0)->where('isDraft', 0);
        } elseif ($unDeliver == 2) {
            $builder->where('endDate >', date('Y-m-d'))->where('endDate <=', date('Y-m-d', strtotime('+1 day')))->where('redoSoType', 0)->where('isCancel', 0)->where('isDraft', 0);
        }
        if ($skey !== '' && $skey !== null) {
            $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
            foreach ($keywords as $kw) {
                $builder->groupStart()
                    ->like('billNo', $this->db->escapeLikeString($kw))
                    ->orLike('contactName', $this->db->escapeLikeString($kw))
                    ->orLike('sName', $this->db->escapeLikeString($kw))
                    ->orLike('saler', $this->db->escapeLikeString($kw))
                    ->orLike('files', $this->db->escapeLikeString($kw))
                    ->orLike('goods', $this->db->escapeLikeString($kw))
                    ->orLike('description', $this->db->escapeLikeString($kw))
                ->groupEnd();
            }
        }
        $builder->where('isDelete', 0);
        $allowedSort = ['id','billDate','billNo','contactName','saler','uName','description','checkId','moneyStatus1','moneyStatus2','invoiceNum','goodsStatus1','goodsStatus2','invoiceNum','isReq'];
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
            $v[$arr]['reqNo'] = $row['reqNo'];
            $v[$arr]['totalQty'] = intval($row['totalQty']);
            $v[$arr]['contactId'] = intval($row['contactId']);
            $v[$arr]['contactName'] = $row['contactName'];
            $v[$arr]['sId'] = intval($row['sId']);
            $v[$arr]['sName'] = $row['sName'];
            $v[$arr]['endDate'] = $row['endDate'];
            $v[$arr]['saler'] = $row['saler'];
            $v[$arr]['amount'] = (float)$row['amount'];
            $v[$arr]['zbjAmount'] = (float)$row['zbjAmount'];
            $v[$arr]['zbjType'] = intval($row['zbjType']);
            $v[$arr]['description'] = $row['description'];
            $v[$arr]['moneyStatus1'] = intval($row['moneyStatus1']);
            $v[$arr]['moneyStatus2'] = intval($row['moneyStatus2']);
            $v[$arr]['srcMoneyId1'] = intval($row['srcMoneyId1']);
            $v[$arr]['srcMoneyId2'] = intval($row['srcMoneyId2']);
            $v[$arr]['srcMoneyNo1'] = $row['srcMoneyNo1'];
            $v[$arr]['srcMoneyNo2'] = $row['srcMoneyNo2'];
            $v[$arr]['goodsStatus1'] = intval($row['goodsStatus1']);
            $v[$arr]['goodsStatus2'] = intval($row['goodsStatus2']);
            $v[$arr]['srcBqId'] = intval($row['srcBqId']);
            $v[$arr]['srcBqNo'] = $row['srcBqNo'];
            $v[$arr]['redoSoId'] = intval($row['redoSoId']);
            $v[$arr]['redoSoNo'] = $row['redoSoNo'];
            $v[$arr]['redoSoType'] = intval($row['redoSoType']);
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
        if (in_array($this->myerp['user']['department'], ['销售部', '财务部', '行政部'])) {
            $totalNotClearedAmount = floatval((clone $builder)->where('redoSoType', 0)->selectSum('amount', 'totalNotClearedAmount')->where('moneyStatus1', 0)->get()->getRowArray()['totalNotClearedAmount'] ?? 0);
            $partIdsArr = [];
            $partIds = (clone $builder)->select('srcMoneyId1')->where(['redoSoType' => 0, 'moneyStatus1' => 1])->get()->getResultArray();
            if (!empty($partIds)) {
                foreach ($partIds as $arr2 => $row2) {
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
        if (!$this->myservice->verifyRight('SC_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvSc/query 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('bill_contract')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
        if ($data) {
            $info['id'] = $id;
            $info['transType'] = intval($data['transType']);
            $info['billDate'] = $data['billDate'];
            $info['billNo'] = $data['billNo'];
            $info['isReq'] = intval($data['isReq']);
            $info['reqNo'] = $data['reqNo'];
            $info['contactId'] = intval($data['contactId']);
            $info['contactName'] = $data['contactName'];
            $info['sId'] = intval($data['sId']);
            $info['sName'] = $data['sName'];
            $info['endDate'] = $data['endDate'];
            $info['saler'] = $data['saler'];
            $info['amount'] = (float)$data['amount'];
            $info['zbjAmount'] = (float)$data['zbjAmount'];
            $info['zbjType'] = intval($data['zbjType']);
            $info['files'] = $data['files'];
            $info['description'] = $data['description'];
            $info['files'] = $data['files'] == '' ? [] : json_decode($data['files'], true);
            $info['uId'] = intval($data['uId']);
            $info['uName'] = $data['uName'];
            $info['checkId'] = intval($data['checkId']);
            $info['checkName'] = $data['checkName'];
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
            $info['srcBqId'] = intval($data['srcBqId']);
            $info['srcBqNo'] = $data['srcBqNo'];
            $info['redoSoId'] = intval($data['redoSoId']);
            $info['redoSoNo'] = $data['redoSoNo'];
            $info['redoSoType'] = intval($data['redoSoType']);
            $info['isDraft'] = intval($data['isDraft']);
            // 获取申请信息
            $reqInfo = [];
            if ($info['isReq'] == 3 && $info['reqNo']) {
                $reqBills = array_column($this->db->table('bill_contract')->select('id')->where(['reqNo' => $info['billNo'], 'billType' => 'PUR', 'isDelete' => 0])->get()->getResultArray(), 'id');
                if (!empty($reqBills)) {
                    $reqGoodsArr = $this->db->table('bill_contract_info')->select('zid, qty')->whereIn('iid', $reqBills)->where(['isDelete' => 0])->get()->getResultArray();
                    if (!empty($reqGoodsArr)) {
                        foreach ($reqGoodsArr as $kk => $goods) {
                            if (!isset($reqInfo[$goods['zid']])) {
                                $reqInfo[$goods['zid']]['qty'] = 0;
                            }
                            if ($goods['qty'] > 0) {
                                $reqInfo[$goods['zid']]['qty'] += $goods['qty'];
                            }
                        }
                    }
                }
            }
            // 获取产品信息
            $list = $this->db->table('bill_contract_info')->where(['iid' => $id, 'isDelete' => 0])->orderBy('CAST(zid AS UNSIGNED) ASC, id ASC')->get()->getResultArray();
            $v = [];
            foreach ($list as $arr => $row) {
                $v[$arr]['id'] = intval($row['id']);
                $v[$arr]['iid'] = intval($row['iid']);
                $v[$arr]['zid'] = $row['zid'];
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
                $v[$arr]['description1'] = $row['description1'];
                $v[$arr]['description2'] = $row['description2'];
                $v[$arr]['isAlreadyReqPur'] = intval($row['isAlreadyReqPur']);
                if ($v[$arr]['isAlreadyReqPur'] > 0) {
                    if ($reqInfo && isset($reqInfo[$row['zid']])) {
                        $v[$arr]['alreadyReqPurInfo'] = '已申请数量：' . $reqInfo[$row['zid']]['qty'];
                    } else {
                        $v[$arr]['isAlreadyReqPur'] = 0;
                        $v[$arr]['alreadyReqPurInfo'] = '';
                    }
                } else {
                    $v[$arr]['alreadyReqPurInfo'] = '';
                }
                $v[$arr]['isPurchased'] = intval($row['isPurchased']);
                $v[$arr]['isRecInvoice'] = intval($row['isRecInvoice']);
                $v[$arr]['uId'] = intval($row['uId']);
                $v[$arr]['uName'] = $row['uName'];
                $v[$arr]['purId'] = intval($row['purId']);
                $v[$arr]['purName'] = $row['purName'];
                $v[$arr]['purFeedback'] = $row['purFeedback'];
                $v[$arr]['isHasExpress'] = intval($row['isHasExpress']);
                $v[$arr]['isHasInvoice'] = intval($row['isHasInvoice']);
                $v[$arr]['quotePrice'] = (float)$row['quotePrice'];
            }
            $info['entries'] = $v;
            $json['status'] = 200;
            $json['msg'] = 'success';
            $json['data'] = $info;
            $json['totalsize'] = count($list);
            return $this->response->setJSON($json);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 新增
    public function add()
    {
        if (!$this->myservice->verifyRight('SC_ADD')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvSc/add 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost('postData');
        if (strlen($data) > 0) {
            $data = $this->validform((array)json_decode($data, TRUE));
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            // 取消自动审核，由销售理手工审核
            $data['checkId'] = 0;
            $data['checkName'] = '';
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
                'saler',
                'amount',
                'zbjAmount',
                'zbjType',
                'files',
                'goods',
                'description',
                'uId',
                'uName',
                'checkId',
                'checkName',
                'createTime',
                'modifyTime',
                'srcBqId',
                'srcBqNo',
                'redoSoId',
                'redoSoNo',
                'redoSoType',
                'isDraft',
                'isFill'
            ]));
            $this->db->transStart();
            $this->db->table('bill_contract')->insert($info);
            $iid = $this->db->insertID();
            if ($data['redoSoId'] > 0) { // 如果是销售订单特殊另发
                $this->db->table('bill_contract')->where(['id' => $iid])->update(['moneyStatus1' => 2, 'goodsStatus1' => 2]);
                $this->db->table('bill_order')->where(['id' => $data['redoSoId']])->update(['srcOcId' => $iid, 'srcOcNo' => $data['billNo']]);
                // 自动删除原发货单、退货单
                $order = $this->db->table('bill_order')->select('srcInvId1, srcInvId2')->where(['id' => $data['redoSoId']])->get()->getRowArray() ?? ['srcInvId1' => 0, 'srcInvId2' => 0];
                if ($order['srcInvId1'] > 0) {
                    $inventory = $this->db->table('bill_inventory')->where(['id' => $order['srcInvId1'], 'billType' => 'SALE', 'isDelete' => 0])->get()->getRowArray();
                    if ($inventory) {
                        $suitCids_arr = array_column($this->db->table('category')->select('id')->where('FIND_IN_SET("280", path) >', 0, false)->where(['isDelete' => 0])->get()->getResultArray(), 'id'); //取出套餐类id数组备用
                        $inventory_info = $this->db->table('bill_inventory_info')->where(['iid' => $inventory['id'], 'billType' => 'SALE', 'isDelete' => 0])->get()->getResultArray();
                        foreach ($inventory_info as $arr => $row) {
                            $goods = $this->db->table('goods')->where(['id' => $row['goodsId']])->get()->getRowArray();
                            if ($goods && in_array($goods['categoryId'], $suitCids_arr)) {
                                if ($goods['hasSon'] > 0 && strlen($goods['sonGoods']) > 0) {
                                    $sonGoods = (array)json_decode($goods['sonGoods'], true);
                                    foreach ($sonGoods as $arr1 => $row1) {
                                        $this->db->table('goods')->set('currentQty', 'currentQty' . ($row['transType'] == 102201 ? ' + ' : ' - ') . abs((float)$row1['qty1'] * (float)$row1['qty2'] * (float)$row['qty']), false)->where(['id' => $row1['gid']])->update();
                                        $newGoods = $this->db->table('goods')->select('currentQty')->where(['id' => $row1['gid']])->get()->getRowArray();
                                        if ($newGoods) {
                                            $invHisData['goodsId'] = $row1['gid'];
                                            $invHisData['billId'] = $row['iid'];
                                            $invHisData['billNo'] = $row['billNo'];
                                            $invHisData['billType'] = 'SA';
                                            $invHisData['transType'] = $row['transType'];
                                            $invHisData['changeType'] = '删除' . $this->myerp['transTypeName'][$row['transType']] . '(套餐)';
                                            $invHisData['changeTime'] = date('Y-m-d H:i:s');
                                            $invHisData['changeCurrentQty'] = $row['transType'] == 102201 ? abs((float)$row1['qty1'] * (float)$row1['qty2'] * (float)$row['qty']) : -abs((float)$row1['qty1'] * (float)$row1['qty2'] * (float)$row['qty']);
                                            $invHisData['currentQty'] = (float)$newGoods['currentQty'];
                                            $invHisInfo = array_intersect_key($invHisData, array_flip(['goodsId', 'billId', 'billNo', 'billType', 'transType', 'changeType', 'changeTime', 'changeCurrentQty', 'currentQty']));
                                            $this->db->table('inv_history')->insert($invHisInfo);
                                        }
                                    }
                                }
                            } else {
                                $this->db->table('goods')->set('currentQty', 'currentQty' . ($row['transType'] == 102201 ? ' + ' : ' - ') . $row['qty'], false)->where(['id' => $row['goodsId']])->update();
                                $newGoods = $this->db->table('goods')->select('currentQty')->where(['id' => $row['goodsId']])->get()->getRowArray();
                                if ($newGoods) {
                                    $invHisData['goodsId'] = $row['goodsId'];
                                    $invHisData['billId'] = $row['iid'];
                                    $invHisData['billNo'] = $row['billNo'];
                                    $invHisData['billType'] = 'SA';
                                    $invHisData['transType'] = $row['transType'];
                                    $invHisData['changeType'] = '删除' . $this->myerp['transTypeName'][$row['transType']];
                                    $invHisData['changeTime'] = date('Y-m-d H:i:s');
                                    $invHisData['changeCurrentQty'] = $row['transType'] == 102201 ? abs((float)$row['qty']) : -abs((float)$row['qty']);
                                    $invHisData['currentQty'] = (float)$newGoods['currentQty'];
                                    $invHisInfo = array_intersect_key($invHisData, array_flip(['goodsId', 'billId', 'billNo', 'billType', 'transType', 'changeType', 'changeTime', 'changeCurrentQty', 'currentQty']));
                                    $this->db->table('inv_history')->insert($invHisInfo);
                                }
                            }
                        }
                        $this->db->table('bill_inventory')->where(['id' => $inventory['id']])->update(['isDelete' => 1]);
                        $this->db->table('bill_inventory_info')->where(['iid' => $inventory['id']])->update(['isDelete' => 1]);
                        $this->db->table('bill_inventory_log')->where(['iid' => $inventory['id']])->update(['isDelete' => 1]);
                        if ($inventory['transType'] == 102201) {
                            $this->db->table('bill_order')->where(['srcInvId1' => $inventory['id']])->update(['srcInvId1' => 0, 'srcInvNo1' => '', 'goodsStatus1' => 0]);
                        } elseif ($inventory['transType'] == 102202) {
                            $this->db->table('bill_order')->where(['srcInvId2' => $inventory['id']])->update(['srcInvId2' => 0, 'srcInvNo2' => '', 'goodsStatus2' => 0]);
                        }
                    }
                }
                if ($order['srcInvId2'] > 0) {
                    $inventory = $this->db->table('bill_inventory')->where(['id' => $order['srcInvId2'], 'billType' => 'SALE', 'isDelete' => 0])->get()->getRowArray();
                    if ($inventory) {
                        $suitCids_arr = array_column($this->db->table('category')->select('id')->where('FIND_IN_SET("280", path) >', 0, false)->where(['isDelete' => 0])->get()->getResultArray(), 'id'); //取出套餐类id数组备用
                        $inventory_info = $this->db->table('bill_inventory_info')->where(['iid' => $inventory['id'], 'billType' => 'SALE', 'isDelete' => 0])->get()->getResultArray();
                        if (!empty($inventory_info)) {
                            foreach ($inventory_info as $arr => $row) {
                                $goods = $this->db->table('goods')->where(['id' => $row['goodsId']])->get()->getRowArray();
                                if ($goods && in_array($goods['categoryId'], $suitCids_arr)) {
                                    if ($goods['hasSon'] > 0 && strlen($goods['sonGoods']) > 0) {
                                        $sonGoods = (array)json_decode($goods['sonGoods'], true);
                                        foreach ($sonGoods as $arr1 => $row1) {
                                            $this->db->table('goods')->set('currentQty', 'currentQty' . ($row['transType'] == 102201 ? ' + ' : ' - ') . abs((float)$row1['qty1'] * (float)$row1['qty2'] * (float)$row['qty']), false)->where(['id' => $row1['gid']])->update();
                                            $newGoods = $this->db->table('goods')->select('currentQty')->where(['id' => $row1['gid']])->get()->getRowArray();
                                            if ($newGoods) {
                                                $invHisData['goodsId'] = $row1['gid'];
                                                $invHisData['billId'] = $row['iid'];
                                                $invHisData['billNo'] = $row['billNo'];
                                                $invHisData['billType'] = 'SA';
                                                $invHisData['transType'] = $row['transType'];
                                                $invHisData['changeType'] = '删除' . $this->myerp['transTypeName'][$row['transType']] . '(套餐)';
                                                $invHisData['changeTime'] = date('Y-m-d H:i:s');
                                                $invHisData['changeCurrentQty'] = $row['transType'] == 102201 ? abs((float)$row1['qty1'] * (float)$row1['qty2'] * (float)$row['qty']) : -abs((float)$row1['qty1'] * (float)$row1['qty2'] * (float)$row['qty']);
                                                $invHisData['currentQty'] = (float)$newGoods['currentQty'];
                                                $invHisInfo = array_intersect_key($invHisData, array_flip(['goodsId', 'billId', 'billNo', 'billType', 'transType', 'changeType', 'changeTime', 'changeCurrentQty', 'currentQty']));
                                                $this->db->table('inv_history')->insert($invHisInfo);
                                            }
                                        }
                                    }
                                } else {
                                    $this->db->table('goods')->set('currentQty', 'currentQty' . ($row['transType'] == 102201 ? ' + ' : ' - ') . $row['qty'], false)->where(['id' => $row['goodsId']])->update();
                                    $newGoods = $this->db->table('goods')->select('currentQty')->where(['id' => $row['goodsId']])->get()->getRowArray();
                                    if ($newGoods) {
                                        $invHisData['goodsId'] = $row['goodsId'];
                                        $invHisData['billId'] = $row['iid'];
                                        $invHisData['billNo'] = $row['billNo'];
                                        $invHisData['billType'] = 'SA';
                                        $invHisData['transType'] = $row['transType'];
                                        $invHisData['changeType'] = '删除' . $this->myerp['transTypeName'][$row['transType']];
                                        $invHisData['changeTime'] = date('Y-m-d H:i:s');
                                        $invHisData['changeCurrentQty'] = $row['transType'] == 102201 ? abs((float)$row['qty']) : -abs((float)$row['qty']);
                                        $invHisData['currentQty'] = (float)$newGoods['currentQty'];
                                        $invHisInfo = array_intersect_key($invHisData, array_flip(['goodsId', 'billId', 'billNo', 'billType', 'transType', 'changeType', 'changeTime', 'changeCurrentQty', 'currentQty']));
                                        $this->db->table('inv_history')->insert($invHisInfo);
                                    }
                                }
                            }
                        }
                        $this->db->table('bill_inventory')->where(['id' => $inventory['id']])->update(['isDelete' => 1]);
                        $this->db->table('bill_inventory_info')->where(['iid' => $inventory['id']])->update(['isDelete' => 1]);
                        $this->db->table('bill_inventory_log')->where(['iid' => $inventory['id']])->update(['isDelete' => 1]);
                        if ($inventory['transType'] == 102201) {
                            $this->db->table('bill_order')->where(['srcInvId1' => $inventory['id']])->update(['srcInvId1' => 0, 'srcInvNo1' => '', 'goodsStatus1' => 0]);
                        } elseif ($inventory['transType'] == 102202) {
                            $this->db->table('bill_order')->where(['srcInvId2' => $inventory['id']])->update(['srcInvId2' => 0, 'srcInvNo2' => '', 'goodsStatus2' => 0]);
                        }
                    }
                }
            }
            if ($data['srcBqId'] > 0) { // 如果是预算报价转化
                $this->db->table('bill_bidquote')->where(['id' => $data['srcBqId']])->update(['contractStatus' => 1, 'contractId' => $iid]);
            }
            $this->contract_info(intval($iid), $data, []);
            $data['id'] = $iid;
            if ($data['redoSoId'] > 0) {
                $this->myservice->log('对销售订单：' . $data['redoSoNo'] . ' 进行了特殊另发');
            }
            $this->myservice->log('新增了' . $this->myerp['transTypeName'][$data['transType']] . ($data['isDraft'] == 1 ? '草稿' : '') . '，单号：' . $data['billNo']);
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
        if (!$this->myservice->verifyRight('SC_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvSc/update 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost('postData'); // FALSE 禁用XSS过滤
        if (strlen($data) > 0) {
            $data = $this->validform((array)json_decode($data, true));
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            $oldData = $this->db->table('bill_contract')->where(['id' => $data['id']])->get()->getRowArray();
            if ($oldData['srcMoneyId1'] > 0 && $data['amount'] != $oldData['amount']) {
                return $this->response->setJSON(['status' => -1, 'msg' => '已收款，不允许再修改货款金额，保存失败！']);
            }
            if ($oldData['srcMoneyId2'] > 0 && $data['amount'] != $oldData['amount']) {
                return $this->response->setJSON(['status' => -1, 'msg' => '已退款，不允许再修改货款金额，保存失败！']);
            }
            if ($oldData['billDate'] < date('Y-m-d', strtotime('-25 day')) && $data['amount'] != $oldData['amount']) {
                return $this->response->setJSON(['status' => -1, 'msg' => '已超过25天，不允许再修改货款金额，保存失败！']);
            }
            if (!$this->myservice->verifyRight('SYS_ADMIN') && $this->myerp['uId'] != $oldData['uId']) {
                return $this->response->setJSON(['status' => -1, 'msg' => '不可编辑别人录入的特殊销售单，仅本人或管理员可修改！']);
            }

            $this->db->transStart();
            //先处理info信息，方便提前判断异常中断后续操作
            if ($data['goods'] != $oldData['goods'] || $data['isNewImportExcel']) {
                $this->contract_info(intval($data['id']), $data, $oldData);
            } else {
                $this->contract_info_update(intval($data['id']), $data, $oldData);
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
                'saler',
                'amount',
                'zbjAmount',
                'zbjType',
                'files',
                'goods',
                'description',
                'modifyTime',
                'isDraft'
            ]));
            $this->db->table('bill_contract')->where(['id' => $data['id']])->update($info);
            $this->myservice->log('编辑了' . $this->myerp['transTypeName'][$data['transType']] . ($data['isDraft'] == 1 ? '草稿' : '') . '，' . (($oldData['isDraft'] == 1 && $data['isDraft'] == 0) ? '并将草稿转为了正式，' : '') . '单号：' . $data['billNo']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => 'success', 'data' => ['errZids' => ((isset($errZids) && count($errZids) > 0) ? implode(',', $errZids) : '')]]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    // 删除
    public function delete()
    {
        if (!$this->myservice->verifyRight('SC_DELETE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvSc/delete 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('bill_contract')->where(['id' => $id])->get()->getRowArray();
        if ($data) {
            if (!$this->myservice->verifyRight('SYS_ADMIN') && $data['uId'] != $this->myerp['uId']) {
                return $this->response->setJSON(['status' => -1, 'msg' => '不能删除别人的特殊销售单！仅本人或管理员可删除！']);
            }
            if ($data['checkId'] > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '已审核的单据不可随意删除！']);
            }
            if ($data['srcMoneyId1'] > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '已发生过收款，请先删除收款单！']);
            }
            if ($data['srcMoneyId2'] > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '已发生过退款，请先删除退款单！']);
            }
            $this->db->transStart();
            if ($data['isDraft'] == 0) { // 如果是正式单据，对有编码的内部产品，重算库存，记录流水
                $contract_info = $this->db->table('bill_contract_info')->where(['iid' => $id, 'no !=' => '', 'isDelete' => 0])->get()->getResultArray();
                if (!empty($contract_info)) {
                    foreach ($contract_info as $arr => $row) {
                        if ($row['no']) {
                            $suitCids_arr = array_column($this->db->table('category')->select('id')->where('FIND_IN_SET("280", path) >', 0, false)->where('isDelete', 0)->get()->getResultArray(), 'id'); //取出套餐类id数组备用
                            $goods = $this->db->table('goods')->where(['no' => $row['no']])->get()->getRowArray();
                            if ($goods && in_array($goods['categoryId'], $suitCids_arr)) {
                                if ($goods['hasSon'] > 0 && strlen($goods['sonGoods']) > 0) {
                                    $sonGoods = (array)json_decode($goods['sonGoods'], true);
                                    foreach ($sonGoods as $arr1 => $row1) {
                                        $this->db->table('goods')->set('currentQty', 'currentQty + ' . abs((float)$row1['qty1'] * (float)$row1['qty2'] * (float)$row['qty']), false)->where('id', $row1['gid'])->update();
                                        $newGoods = $this->db->table('goods')->select('currentQty')->where('id', $row1['gid'])->get()->getRowArray();
                                        if ($newGoods) {
                                            $invHisData['goodsId'] = $row1['gid'];
                                            $invHisData['billId'] = $row['iid'];
                                            $invHisData['billNo'] = $row['billNo'];
                                            $invHisData['billType'] = 'SC';
                                            $invHisData['transType'] = $row['transType'];
                                            $invHisData['changeType'] = '删除' . $this->myerp['transTypeName'][$row['transType']] . '(套餐)';
                                            $invHisData['changeTime'] = date('Y-m-d H:i:s');
                                            $invHisData['changeCurrentQty'] = abs((float)$row1['qty1'] * (float)$row1['qty2'] * (float)$row['qty']);
                                            $invHisData['currentQty'] = (float)$newGoods['currentQty'];
                                            $invHisInfo = array_intersect_key($invHisData, array_flip(['goodsId', 'billId', 'billNo', 'billType', 'transType', 'changeType', 'changeTime', 'changeCurrentQty', 'currentQty']));
                                            $this->db->table('inv_history')->insert($invHisInfo);
                                        }
                                    }
                                }
                            } else {
                                $this->db->table('goods')->set('currentQty', 'currentQty + ' . $row['qty'], false)->where('no', $row['no'])->update();
                                $newGoods = $this->db->table('goods')->select('id, currentQty')->where('no', $row['no'])->get()->getRowArray();
                                if ($newGoods) {
                                    $invHisData['goodsId'] = $newGoods['id'];
                                    $invHisData['billId'] = $row['iid'];
                                    $invHisData['billNo'] = $row['billNo'];
                                    $invHisData['billType'] = 'SC';
                                    $invHisData['transType'] = $row['transType'];
                                    $invHisData['changeType'] = '删除' . $this->myerp['transTypeName'][$row['transType']];
                                    $invHisData['changeTime'] = date('Y-m-d H:i:s');
                                    $invHisData['changeCurrentQty'] = abs((float)$row['qty']);
                                    $invHisData['currentQty'] = (float)$newGoods['currentQty'];
                                    $invHisInfo = array_intersect_key($invHisData, array_flip(['goodsId', 'billId', 'billNo', 'billType', 'transType', 'changeType', 'changeTime', 'changeCurrentQty', 'currentQty']));
                                    $this->db->table('inv_history')->insert($invHisInfo);
                                }
                            }
                        }
                    }
                }
            }
            $this->db->table('bill_contract')->where('id', $id)->update(['isDelete' => 1]);
            $this->db->table('bill_contract_info')->where('iid', $id)->update(['isDelete' => 1]);
            if ($data['redoSoId'] > 0) {
                $this->db->table('bill_order')->where('id', $data['redoSoId'])->update(['srcOcId' => 0, 'srcOcNo' => '']);
            }
            if ($data['srcBqId'] > 0) { // 如果是预算报价转化
                $this->db->table('bill_bidquote')->where('id', $data['srcBqId'])->update(['contractStatus' => 0, 'contractId' => 0]);
            }
            $this->myservice->log('删除了' . $this->myerp['transTypeName'][$data['transType']] . ($data['isDraft'] == 1 ? '草稿' : '') . '，单号：' . $data['billNo']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '删除成功！', 'data' => ['id' => $id]]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 审核
    public function check()
    {
        if (!$this->myservice->verifyRight('SC_CHECK')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvSc/check 权限不足！');
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
            $this->myservice->log(($checkId > 0 ? '审核了' : '反审核了') . $this->myerp['transTypeName'][$transType] . '，单号：' . $billNo);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '操作成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 更新交货状态
    public function upGoodsStatus1()
    {
        if (!$this->myservice->verifyRight('SC_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvSc/upGoodsStatus1 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $goodsStatus1 = intval($this->request->getGetPost('goodsStatus1'));
        $billNo = $this->request->getGetPost('billNo');
        $transType = intval($this->request->getGetPost('transType'));
        $data = $this->db->table('bill_contract')->where('id', $id)->get()->getRowArray();
        if ($data) {
            if (!$this->myservice->verifyRight('SYS_ADMIN') && $this->myerp['uId'] != $data['uId']) {
                return $this->response->setJSON(['status' => -1, 'msg' => '不可标记别人录入的特殊销售单，仅本人或管理员可标记！']);
            }
            if ($goodsStatus1 > 0 && $data['deliverDoneTime'] == '0000-00-00 00:00:00') {
                $newDate = ['goodsStatus1 ' => $goodsStatus1, 'deliverDoneTime' => date('Y-m-d H:i:s')];
            } else {
                $newDate = ['goodsStatus1 ' => $goodsStatus1];
            }
            $this->db->transStart();
            $this->db->table('bill_contract')->where('id', $id)->update($newDate);
            $this->myservice->log('标记了' . $this->myerp['transTypeName'][$transType] . ' ' . $billNo . ' 的交货状态为 ' . ($goodsStatus1 == 0 ? '未交货' : '已交货'));
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
        if (!$this->myservice->verifyRight('SC_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvSc/upGoodsStatus2 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $goodsStatus2 = intval($this->request->getGetPost('goodsStatus2'));
        $billNo = $this->request->getGetPost('billNo');
        $transType = intval($this->request->getGetPost('transType'));
        $data = $this->db->table('bill_contract')->where('id', $id)->get()->getRowArray();
        if ($data) {
            if (!$this->myservice->verifyRight('SYS_ADMIN') && $this->myerp['uId'] != $data['uId']) {
                return $this->response->setJSON(['status' => -1, 'msg' => '不可标记别人录入的特殊销售单，仅本人或管理员可标记！']);
            }
            $newDate = ['goodsStatus2 ' => $goodsStatus2];
            $this->db->transStart();
            $this->db->table('bill_contract')->where('id', $id)->update($newDate);
            $this->myservice->log('标记了' . $this->myerp['transTypeName'][$transType] . ' ' . $billNo . ' 的退货状态为 ' . ($goodsStatus2 == 0 ? '无退货' : '有退货'));
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
        if (!$this->myservice->verifyRight('SC_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvSc/updatedes 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $newDes = $this->request->getGetPost('newDes');
        $data = $this->db->table('bill_contract')->where('id', $id)->get()->getRowArray();
        if ($data) {
            if (!$this->myservice->verifyRight('SYS_ADMIN') && $this->myerp['uId'] != $data['uId']) {
                return $this->response->setJSON(['status' => -1, 'msg' => '不可修改别人录入的特殊销售单，仅本人或管理员可修改！']);
            }
            $v['description'] = $newDes;
            $v['modifyTime'] = date("Y-m-d H:i:s");
            $this->db->transStart();
            $this->db->table('bill_contract')->where('id', $id)->update($v);
            $this->myservice->log('单独更新了' . $this->myerp['transTypeName'][$data['transType']] . '的备注，单号：' . $data['billNo']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '操作成功！', 'data' => ['id' => $id]]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // info数据 产品有变化 全部删除 重新插入
    private function contract_info(int $iid, array $data, array $oldData = [])
    {
        foreach ($data['entries'] as $arr => $row) {
            $v[$arr]['iid'] = intval($iid);
            $v[$arr]['zid'] = trim($row['zid']);
            $v[$arr]['billDate'] = $data['billDate'];
            $v[$arr]['billNo'] = $data['billNo'];
            $v[$arr]['endDate'] = $data['endDate'];
            $v[$arr]['billType'] = $data['billType'];
            $v[$arr]['transType'] = intval($data['transType']);
            $v[$arr]['contactId'] = $data['contactId'];
            $v[$arr]['storageId'] = 1;
            $v[$arr]['goodsId'] = $row['no'] ? ($this->db->table('goods')->select('id')->where('no', $row['no'])->get()->getRowArray()['id'] ?? 0) : 0;
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
            $v[$arr]['description1'] = $row['description1'];
            $v[$arr]['description2'] = $row['description2'];
            $v[$arr]['isRecInvoice'] = $row['isRecInvoice'] ?? 0;
            $v[$arr]['hasRecQty'] = intval($row['hasRecQty'] ?? 0);
            $v[$arr]['limitPrice'] = abs((float)$row['limitPrice']);
            $v[$arr]['uId'] = $row['uId'] ?? $data['uId'];
            $v[$arr]['uName'] = $row['uName'] ?? $data['uName'];
        }
        if (isset($v)) {
            $suitCids_arr = array_column($this->db->table('category')->select('id')->where('FIND_IN_SET("280", path) >', 0, false)->where('isDelete', 0)->get()->getResultArray(), 'id'); //取出套餐类id数组备用
            if ($data['id'] > 0) {
                if ($oldData && $oldData['isDraft'] == 0) { //编辑时，如果以前是正式单，重算库存，记录流水
                    $contract_info = $this->db->table('bill_contract_info')->where(['iid' => $data['id'], 'no !=' => '', 'isDelete' => 0])->get()->getResultArray();
                    if (!empty($contract_info)) {
                        foreach ($contract_info as $arr1 => $row1) {
                            if ($row1['qty'] > 0) {
                                $goods = $this->db->table('goods')->where(['no' => $row1['no']])->get()->getRowArray();
                                if ($goods && in_array($goods['categoryId'], $suitCids_arr)) {
                                    if ($goods['hasSon'] > 0 && strlen($goods['sonGoods']) > 0) {
                                        $sonGoods = (array)json_decode($goods['sonGoods'], true);
                                        foreach ($sonGoods as $arr2 => $row2) {
                                            $this->db->table('goods')->set('currentQty', 'currentQty + ' . abs((float)$row2['qty1'] * (float)$row2['qty2'] * (float)$row1['qty']), false)->where('id', $row2['gid'])->update();
                                            $newGoods = $this->db->table('goods')->select('currentQty')->where('id', $row2['gid'])->get()->getRowArray();
                                            if ($newGoods) {
                                                $invHisData = [];
                                                $invHisData['goodsId'] = $row2['gid'];
                                                $invHisData['billId'] = $row1['iid'];
                                                $invHisData['billNo'] = $row1['billNo'];
                                                $invHisData['billType'] = 'SC';
                                                $invHisData['transType'] = $row1['transType'];
                                                $invHisData['changeType'] = '清空' . $this->myerp['transTypeName'][$row1['transType']] . ($oldData['isDraft'] == 1 ? '草稿' : '') . '(套餐)';
                                                $invHisData['changeTime'] = date('Y-m-d H:i:s');
                                                $invHisData['changeCurrentQty'] = abs((float)$row2['qty1'] * (float)$row2['qty2'] * (float)$row1['qty']);
                                                $invHisData['currentQty'] = (float)$newGoods['currentQty'];
                                                $this->db->table('inv_history')->insert($invHisData);
                                            }
                                        }
                                    }
                                } elseif ($goods) {
                                    $this->db->table('goods')->set('currentQty', 'currentQty + ' . $row1['qty'], false)->where('no', $row1['no'])->update();
                                    $newGoods = $this->db->table('goods')->select('id, currentQty')->where('no', $row1['no'])->get()->getRowArray();
                                    if ($newGoods) {
                                        $invHisData['goodsId'] = $newGoods['id'];
                                        $invHisData['billId'] = $row1['iid'];
                                        $invHisData['billNo'] = $row1['billNo'];
                                        $invHisData['billType'] = 'SC';
                                        $invHisData['transType'] = $row1['transType'];
                                        $invHisData['changeType'] = '清空' . $this->myerp['transTypeName'][$row1['transType']] . ($oldData['isDraft'] == 1 ? '草稿' : '');
                                        $invHisData['changeTime'] = date('Y-m-d H:i:s');
                                        $invHisData['changeCurrentQty'] = abs((float)$row1['qty']);
                                        $invHisData['currentQty'] = (float)$newGoods['currentQty'];
                                        $invHisInfo = array_intersect_key($invHisData, array_flip(['goodsId', 'billId', 'billNo', 'billType', 'transType', 'changeType', 'changeTime', 'changeCurrentQty', 'currentQty']));
                                        $this->db->table('inv_history')->insert($invHisInfo);
                                    }
                                }
                            }
                        }
                    }
                }
                // 真删除
                $this->db->table('bill_contract_info')->where(['iid' => $iid])->delete();
            }
            $this->db->table('bill_contract_info')->insertBatch($v);
            if ($data['isDraft'] == 0) { //如果现在是正式单，重算库存，记录流水
                foreach ($v as $arr3 => $row3) {
                    if (strlen($row3['no']) > 0 && $row3['qty'] > 0) {
                        $goods = $this->db->table('goods')->where(['no' => $row3['no']])->get()->getRowArray();
                        if ($goods && in_array($goods['categoryId'], $suitCids_arr)) {
                            if ($goods['hasSon'] > 0 && strlen($goods['sonGoods']) > 0) {
                                $sonGoods = (array)json_decode($goods['sonGoods'], true);
                                foreach ($sonGoods as $arr4 => $row4) {
                                    $this->db->table('goods')->set('currentQty', 'currentQty - ' . abs((float)$row4['qty1'] * (float)$row4['qty2'] * (float)$row3['qty']), false)->where('id', $row4['gid'])->update();
                                    $newGoods = $this->db->table('goods')->select('currentQty')->where('id', $row4['gid'])->get()->getRowArray();
                                    if ($newGoods) {
                                        $invHisData = [];
                                        $invHisData['goodsId'] = $row4['gid'];
                                        $invHisData['billId'] = $row3['iid'];
                                        $invHisData['billNo'] = $row3['billNo'];
                                        $invHisData['billType'] = 'SC';
                                        $invHisData['transType'] = $row3['transType'];
                                        $invHisData['changeType'] = ((isset($data['id']) && $data['id'] > 0) ? '编辑' : '新增') . $this->myerp['transTypeName'][$row3['transType']] . ($data['isDraft'] == 1 ? '草稿' : '') . '(套餐)';
                                        $invHisData['changeTime'] = date('Y-m-d H:i:s');
                                        $invHisData['changeCurrentQty'] = -abs((float)$row4['qty1'] * (float)$row4['qty2'] * (float)$row3['qty']);
                                        $invHisData['currentQty'] = (float)$newGoods['currentQty'];
                                        $this->db->table('inv_history')->insert($invHisData);
                                    }
                                }
                            }
                        } elseif ($goods) {
                            $this->db->table('goods')->set('currentQty', 'currentQty - ' . $row3['qty'], false)->where('no', $row3['no'])->update();
                            $newGoods = $this->db->table('goods')->select('id, currentQty')->where('no', $row3['no'])->get()->getRowArray();
                            if ($newGoods) {
                                $invHisData = [];
                                $invHisData['goodsId'] = $newGoods['id'];
                                $invHisData['billId'] = $row3['iid'];
                                $invHisData['billNo'] = $row3['billNo'];
                                $invHisData['billType'] = 'SC';
                                $invHisData['transType'] = $row3['transType'];
                                $invHisData['changeType'] = ((isset($data['id']) && $data['id'] > 0) ? '编辑' : '新增') . $this->myerp['transTypeName'][$row3['transType']] . ($data['isDraft'] == 1 ? '草稿' : '');
                                $invHisData['changeTime'] = date('Y-m-d H:i:s');
                                $invHisData['changeCurrentQty'] = -abs((float)$row3['qty']);
                                $invHisData['currentQty'] = (float)$newGoods['currentQty'];
                                $this->db->table('inv_history')->insert($invHisData);
                            }
                        }
                    }
                }
            }
        }
    }
    // info数据 产品无变化 全部更新
    private function contract_info_update(int $iid, array $data, array $oldData)
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
            $v[$arr]['contactId'] = intval($data['contactId']);
            $v[$arr]['storageId'] = 1;
            $v[$arr]['goodsId'] = $row['no'] ? ($this->db->table('goods')->select('id')->where('no', $row['no'])->get()->getRowArray()['id'] ?? 0) : 0;
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
            $v[$arr]['description1'] = $row['description1'];
            $v[$arr]['description2'] = $row['description2'];
            $v[$arr]['isRecInvoice'] = $row['isRecInvoice'] ?? 0;
            $v[$arr]['hasRecQty'] = intval($row['hasRecQty'] ?? 0);
            $v[$arr]['limitPrice'] = abs((float)$row['limitPrice']);
            $v[$arr]['uId'] = $row['uId'] ?? $data['uId'];
            $v[$arr]['uName'] = $row['uName'] ?? $data['uName'];
        }
        if (isset($v)) {
            if ($oldData['isDraft'] == 1 && $data['isDraft'] == 0) { //编辑时，如果以前是草稿，现在是正式单，重算库存，记录流水
                $suitCids_arr = array_column($this->db->table('category')->select('id')->where('FIND_IN_SET("280", path) >', 0, false)->where('isDelete', 0)->get()->getResultArray(), 'id'); //取出套餐类id数组备用
                $contract_info = $this->db->table('bill_contract_info')->where(['iid' => $data['id'], 'no !=' => '', 'isDelete' => 0])->get()->getResultArray();
                foreach ($contract_info as $arr1 => $row1) {
                    if ($row1['qty'] > 0) {
                        $goods = $this->db->table('goods')->where(['no' => $row1['no']])->get()->getRowArray();
                        if ($goods && in_array($goods['categoryId'], $suitCids_arr)) {
                            if ($goods['hasSon'] > 0 && strlen($goods['sonGoods']) > 0) {
                                $sonGoods = (array)json_decode($goods['sonGoods'], true);
                                foreach ($sonGoods as $arr2 => $row2) {
                                    $this->db->table('goods')->set('currentQty', 'currentQty - ' . abs((float)$row2['qty1'] * (float)$row2['qty2'] * (float)$row1['qty']), false)->where('id', $row2['gid'])->update();
                                    $newGoods = $this->db->table('goods')->select('currentQty')->where('id', $row2['gid'])->get()->getRowArray();
                                    if ($newGoods) {
                                        $invHisData = [];
                                        $invHisData['goodsId'] = $row2['gid'];
                                        $invHisData['billId'] = $row1['iid'];
                                        $invHisData['billNo'] = $row1['billNo'];
                                        $invHisData['billType'] = 'SC';
                                        $invHisData['transType'] = $row1['transType'];
                                        $invHisData['changeType'] = $this->myerp['transTypeName'][$row1['transType']] . '草稿转正式(套餐)';
                                        $invHisData['changeTime'] = date('Y-m-d H:i:s');
                                        $invHisData['changeCurrentQty'] = -abs((float)$row2['qty1'] * (float)$row2['qty2'] * (float)$row1['qty']);
                                        $invHisData['currentQty'] = (float)$newGoods['currentQty'];
                                        $this->db->table('inv_history')->insert($invHisData);
                                    }
                                }
                            }
                        } elseif ($goods) {
                            $this->db->table('goods')->set('currentQty', 'currentQty - ' . $row1['qty'], false)->where('no', $row1['no'])->update();
                            $newGoods = $this->db->table('goods')->select('id, currentQty')->where('no', $row1['no'])->get()->getRowArray();
                            if ($newGoods) {
                                $invHisData['goodsId'] = $newGoods['id'];
                                $invHisData['billId'] = $row1['iid'];
                                $invHisData['billNo'] = $row1['billNo'];
                                $invHisData['billType'] = 'SC';
                                $invHisData['transType'] = $row1['transType'];
                                $invHisData['changeType'] = $this->myerp['transTypeName'][$row1['transType']] . '草稿转正式';
                                $invHisData['changeTime'] = date('Y-m-d H:i:s');
                                $invHisData['changeCurrentQty'] = -abs((float)$row1['qty']);
                                $invHisData['currentQty'] = (float)$newGoods['currentQty'];
                                $invHisInfo = array_intersect_key($invHisData, array_flip(['goodsId', 'billId', 'billNo', 'billType', 'transType', 'changeType', 'changeTime', 'changeCurrentQty', 'currentQty']));
                                $this->db->table('inv_history')->insert($invHisInfo);
                            }
                        }
                    }
                }
            }
<<<<<<< HEAD
            $this->db->table('bill_contract_info')->updateBatch($v, ['id', 'iid']);
=======
            $this->db->table('bill_contract_info')->where('id', $v['id'])->updateBatch($v, 'id');
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
        }
    }
    // 上传表格并提取数据
    public function uploadExcel()
    {
        if (!$this->myservice->verifyRight('SC_IMPORT')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvSc/uploadExcel 权限不足！');
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
        $this->myservice->log('导入了特殊销售表格：' . $_FILES['file']['name']);

        // 调用spreadsheet处理表格
        $inputFileName = $uppath . $target_file; // 要读取的xls文件路径
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);
        $sheet = $spreadsheet->getActiveSheet();
        $sheetData = $sheet->toArray(null, true, true, true);
        // 组装数据插入表
        for ($i = 3; $i <= count($sheetData); $i++) {
            $v[$i - 3]['iid'] = 0;
            $v[$i - 3]['zid'] = $sheetData[$i]['A'] ? str_replace(["\r", "\n", "\r\n", '{', '}', '"', "'"], '”', trim($sheetData[$i]['A'])) : '';
            $v[$i - 3]['no'] = $sheetData[$i]['B'] ? str_replace(["\r", "\n", "\r\n", '{', '}', '"', "'"], '', trim($sheetData[$i]['B'])) : '';
            $v[$i - 3]['name'] = $sheetData[$i]['C'] ? str_replace(["\r", "\n", "\r\n", '{', '}', '"', "'"], '”', trim($sheetData[$i]['C'])) : '';
            $v[$i - 3]['spec'] = $sheetData[$i]['D'] ? str_replace(["\r", "\n", "\r\n", '{', '}', '"', "'"], '”', trim($sheetData[$i]['D'])) : '';
            $v[$i - 3]['sku'] = $sheetData[$i]['E'] ? str_replace(["\r", "\n", "\r\n", '{', '}', '"', "'"], '”', trim($sheetData[$i]['E'])) : '';
            $v[$i - 3]['brand'] = $sheetData[$i]['F'] ? str_replace(["\r", "\n", "\r\n", '{', '}', '"', "'"], '”', trim($sheetData[$i]['F'])) : '';
            $v[$i - 3]['unit'] = $sheetData[$i]['G'] ? str_replace(["\r", "\n", "\r\n", '{', '}', '"', "'"], '”', trim($sheetData[$i]['G'])) : '';
            $v[$i - 3]['qty'] = $sheetData[$i]['H'] ?? 0;
            $v[$i - 3]['price'] = $sheetData[$i]['I'] ?? 0;
            $thistotal = $sheetData[$i]['J'] ?? 0;
            $v[$i - 3]['amount'] = (0 < (float)$thistotal ? (float)$thistotal : ((float)$v[$i - 3]['qty'] * (float)$v[$i - 3]['price']));
            // 判断是否有单元格超链接、公式超链接、或字符串url，最终提取出有效的参考链接
            $v[$i - 3]['buyUrl'] = '';
            $cell = $sheet->getCell('K' . $i); // 通过坐标直接获取单元格对象
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
            $v[$i - 3]['description1'] = $sheetData[$i]['L'] ? str_replace(["\r", "\n", "\r\n", '{', '}', '"', "'"], '”', $sheetData[$i]['L']) : '';
            $v[$i - 3]['description2'] = $sheetData[$i]['M'] ? str_replace(["\r", "\n", "\r\n", '{', '}', '"', "'"], '”', $sheetData[$i]['M']) : '';
        }
        // 返回组装好的数据
        $info['entries'] = isset($v) ? array_values($v) : [];
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data'] = $info;
        $json['totalsize'] = count($info['entries']);
        return $this->response->setJSON($json);
    }
    // 导出产品清单
    public function exportInfoGrid()
    {
        if (!$this->myservice->verifyRight('SC_EXPORT')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvSc/exportInfoGrid 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = $this->request->getGetPost('id');
        $billNo = $this->request->getGetPost('billNo');
        $list = $this->db->table('bill_contract_info')->where(['iid' => $id, 'isDelete' => 0])->get()->getResultArray();
        $name = '特殊销售产品清单_' . $billNo;

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
        $activeWorksheet->getStyle('A1:P' . (COUNT($list) + 2))->applyFromArray($styleArray, false);

        // 合并单元格
        $activeWorksheet->mergeCells('A1:P1');

        // 设置水平居中
        $activeWorksheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // 设置行高
        $activeWorksheet->getRowDimension('1')->setRowHeight(40);

        // 设置加粗
        $activeWorksheet->getStyle('A1')->getFont()->setBold(true);
        $activeWorksheet->getStyle('A2:P2')->getFont()->setBold(true);

        // 设置背景色
        $activeWorksheet->getStyle('A2:P2')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        $activeWorksheet->getStyle('A2:P2')->getFill()->getStartColor()->setRGB('F5F5F5');

        // 设置每列列宽
        $activeWorksheet->getColumnDimension('A')->setWidth(10);
        $activeWorksheet->getColumnDimension('B')->setWidth(20);
        $activeWorksheet->getColumnDimension('C')->setWidth(20);
        $activeWorksheet->getColumnDimension('D')->setWidth(20);
        $activeWorksheet->getColumnDimension('E')->setWidth(20);
        $activeWorksheet->getColumnDimension('F')->setWidth(10);
        $activeWorksheet->getColumnDimension('G')->setWidth(10);
        $activeWorksheet->getColumnDimension('H')->setWidth(10);
        $activeWorksheet->getColumnDimension('I')->setWidth(12);
        $activeWorksheet->getColumnDimension('J')->setWidth(12);
        $activeWorksheet->getColumnDimension('K')->setWidth(20);
        $activeWorksheet->getColumnDimension('L')->setWidth(20);
        $activeWorksheet->getColumnDimension('M')->setWidth(20);
        $activeWorksheet->getColumnDimension('N')->setWidth(10);
        $activeWorksheet->getColumnDimension('O')->setWidth(8);
        $activeWorksheet->getColumnDimension('P')->setWidth(20);

        //设置当前的sheet
        $activeWorksheet->setCellValue('A1', $name . '（导出时间 ' . date('Y-m-d H:i:s') . '）');
        $activeWorksheet->setCellValue('A2', '自编序号');
        $activeWorksheet->setCellValue('B2', '产品编码');
        $activeWorksheet->setCellValue('C2', '产品名称');
        $activeWorksheet->setCellValue('D2', '规格型号');
        $activeWorksheet->setCellValue('E2', '辅助属性');
        $activeWorksheet->setCellValue('F2', '产品品牌');
        $activeWorksheet->setCellValue('G2', '单位');
        $activeWorksheet->setCellValue('H2', '数量');
        $activeWorksheet->setCellValue('I2', '单价');
        $activeWorksheet->setCellValue('J2', '总价');
        $activeWorksheet->setCellValue('K2', '参考采购链接');
        $activeWorksheet->setCellValue('L2', '备注1');
        $activeWorksheet->setCellValue('M2', '备注2');
        $activeWorksheet->setCellValue('N2', '采购人');
        $activeWorksheet->setCellValue('O2', '采买');
        $activeWorksheet->setCellValue('P2', '采购反馈');

        $i = 3;
        foreach ($list as $k => $v) {
            $activeWorksheet->setCellValueExplicit('A' . $i, $v['zid'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $activeWorksheet->setCellValue('B' . $i, $v['no']);
            $activeWorksheet->setCellValue('C' . $i, $v['name']);
            $activeWorksheet->setCellValue('D' . $i, $v['spec']);
            $activeWorksheet->setCellValue('E' . $i, $v['sku']);
            $activeWorksheet->setCellValue('F' . $i, $v['brand']);
            $activeWorksheet->setCellValue('G' . $i, $v['unit']);
            $activeWorksheet->setCellValue('H' . $i, $v['qty']);
            $activeWorksheet->setCellValue('I' . $i, $v['price']);
            $activeWorksheet->setCellValue('J' . $i, $v['amount']);
            $activeWorksheet->setCellValue('K' . $i, $v['buyUrl']);
            $activeWorksheet->setCellValue('L' . $i, $v['description1']);
            $activeWorksheet->setCellValue('M' . $i, $v['description2']);
            $activeWorksheet->setCellValue('N' . $i, $v['purName']);
            $activeWorksheet->setCellValue('O' . $i, $v['isPurchased'] == 1 ? '已买' : ($v['isPurchased'] == 2 ? '不买' : '未买'));
            $activeWorksheet->setCellValue('P' . $i, $v['purFeedback']);
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
        if (!$this->myservice->verifyRight('SC_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvSc/uploadfile 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
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
        $this->myservice->log('上传了特殊销售资料：' . $_FILES['file']['name']);
        $json['status'] = 200;
        $json['msg'] = '资料上传成功';
        $json['file'] = ['name' => $_FILES['file']['name'], 'url' => $target_file, 'upName' => $this->myerp['uName'], 'upTime' => date('Y-m-d')];
        return $this->response->setJSON($json);
    }
    // 数据验证
    private function validform(array $data)
    {
        $data['id'] = isset($data['id']) ? intval($data['id']) : -1;
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
        $data['saler'] = $data['saler'] ?? '';
        $data['transType'] = intval($data['transType']);
        $data['amount'] = abs((float)$data['amount']);
        $data['zbjAmount'] = isset($data['zbjAmount']) ? abs((float)$data['zbjAmount']) : 0;
        $data['zbjType'] = isset($data['zbjType']) ? intval($data['zbjType']) : 0;
        $data['totalQty'] = abs(intval($data['totalQty']));
        $data['uId'] = $data['uId'] ?? $this->myerp['uId'];
        $data['uName'] = $data['uName'] ?? $this->myerp['uName'];
        $data['createTime'] = date("Y-m-d H:i:s");
        $data['modifyTime'] = date("Y-m-d H:i:s");
        $data['entries'] = $data['entries'] ?? [];
        $data['srcBqId'] = isset($data['srcBqId']) ? intval($data['srcBqId']) : 0;
        $data['srcBqNo'] = $data['srcBqNo'] ?? '';
        $data['redoSoId'] = isset($data['redoSoId']) ? intval($data['redoSoId']) : 0;
        $data['redoSoNo'] = $data['redoSoNo'] ?? '';
        $data['redoSoType'] = isset($data['redoSoType']) ? intval($data['redoSoType']) : 0;
        $data['description'] = isset($data['description']) ? str_replace(['{', '}', '"', "'"], '”', $data['description']) : '';
        $data['files'] = strlen($data['files'] ?? '') < 10 ? '' : $data['files'];
        $data['isDraft'] = isset($data['isDraft']) ? intval($data['isDraft']) : 0;
        $data['isFill'] = isset($data['isFill']) ? intval($data['isFill']) : 0;
        if ($data['id'] > 0) {
            if ($this->db->table('bill_contract')->where(['id' => $data['id'], 'isDelete' => 0])->countAllResults() < 1) {
                return $this->response->setJSON(['status' => -1, 'msg' => '单据不存在、或者已删除！']);
            }
        } else {
            if ($this->db->table('bill_contract')->where(['billNo' => $data['billNo']])->countAllResults() > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '单据编号重复！']);
            }
        }
        if (count($data['entries']) < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
        }
        foreach ($data['entries'] as $arr => $row) {
            //处理容易出问题的特殊字符
            $data['entries'][$arr]['zid'] = str_replace(["\r", "\n", "\r\n", '{', '}', '"', "'"], '”', trim($row['zid']));
            $data['entries'][$arr]['no'] = str_replace(["\r", "\n", "\r\n", '{', '}', '"', "'"], '”', trim($row['no']));
            $data['entries'][$arr]['name'] = str_replace(["\r", "\n", "\r\n", '{', '}', '"', "'"], '”', trim($row['name']));
            $data['entries'][$arr]['spec'] = str_replace(["\r", "\n", "\r\n", '{', '}', '"', "'"], '”', trim($row['spec']));
            $data['entries'][$arr]['sku'] = str_replace(["\r", "\n", "\r\n", '{', '}', '"', "'"], '”', trim($row['sku']));
            $data['entries'][$arr]['brand'] = str_replace(["\r", "\n", "\r\n", '{', '}', '"', "'"], '”', trim($row['brand']));
            $data['entries'][$arr]['unit'] = str_replace(["\r", "\n", "\r\n", '{', '}', '"', "'"], '”', trim($row['unit']));
            $data['entries'][$arr]['buyUrl'] = $row['buyUrl'] ? shop_url_parse(trim($row['buyUrl'])) : '';
            $data['entries'][$arr]['description1'] = str_replace(["\r", "\n", "\r\n", '{', '}', '"', "'"], '”', $row['description1']);
            $data['entries'][$arr]['description2'] = str_replace(["\r", "\n", "\r\n", '{', '}', '"', "'"], '”', $row['description2']);
            // 错误检查
            if ($data['entries'][$arr]['no']) {
                if ($this->db->table('goods')->where(['no' => $data['entries'][$arr]['no'], 'isDisable <' => 2, 'isDelete' => 0])->countAllResults() < 1) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '编码为【' . $data['entries'][$arr]['no'] . '】的产品在系统中不存在，或已停产与禁用！请认真检查！']);
                }
            }
            if (strlen($data['entries'][$arr]['name']) < 1) {
                return $this->response->setJSON(['status' => -1, 'msg' => '请选择产品！']);
            }
            if ((float)$data['entries'][$arr]['qty'] < 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '数量要为正数！']);
            }
            if ((float)$data['entries'][$arr]['price'] < 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '价格要为正数！']);
            }
            // 组装goods字段
            $data['goods'][$arr]['zid'] = $data['entries'][$arr]['zid'];
            $data['goods'][$arr]['no'] = $data['entries'][$arr]['no'];
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
}
