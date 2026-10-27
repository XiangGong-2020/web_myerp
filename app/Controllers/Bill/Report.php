<?php

namespace App\Controllers\Bill;
use App\Controllers\BaseController;

class Report extends BaseController
{
    /**开发相关 */
    // 新品趋势图
    public function goodsNewTrend()
    {
        if (!$this->myservice->verifyRight('SYS_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/Report/goodsNewTrend 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        set_time_limit(0); // 设置脚本运行时间无限制，直到脚本运行完毕
        session_write_close(); // 主动关闭session读写保护来解除锁定，避免其他访问无法读写session造成阻塞引起假死
        $type = $this->request->getGetPost('type');
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');
        switch ($type) {
            case 'near30days':
                $sql = 'SELECT date AS a, SUM(n) AS b FROM (SELECT DATE_FORMAT(createTime,"%Y-%m-%d") AS date, 1 AS n FROM goods WHERE createTime >= "' . date('Y-m-d', strtotime(date('Y-m-d') . " -30 day")) . ' 00:00:00" AND createTime <= "' . date('Y-m-d') . ' 23:59:59" AND categoryId IN(26,263) AND isDelete = 0) AS z GROUP BY date ORDER BY date ASC';
                break;
            case 'lastmonth':
                $sql = 'SELECT date AS a, SUM(n) AS b FROM (SELECT DATE_FORMAT(createTime,"%Y-%m-%d") AS date, 1 AS n FROM goods WHERE createTime >= "' . date('Y-m-01', strtotime(date('Y-m-01') . " -1 month")) . ' 00:00:00" AND createTime < "' . date('Y-m-01') . ' 00:00:00" AND categoryId IN(26,263) AND isDelete = 0) AS z GROUP BY date ORDER BY date ASC';
                break;
            case 'near12months':
                $sql = 'SELECT date AS a, SUM(n) AS b FROM (SELECT DATE_FORMAT(createTime,"%Y-%m") AS date, 1 AS n FROM goods WHERE createTime >= "' . date('Y-m-01', strtotime(date('Y-m-01') . " -11 month")) . ' 00:00:00" AND createTime <= "' . date('Y-m-d') . ' 23:59:59" AND categoryId IN(26,263) AND isDelete = 0) AS z GROUP BY date ORDER BY date ASC';
                break;
            case 'all':
                $sql = 'SELECT date AS a, SUM(n) AS b FROM (SELECT DATE_FORMAT(createTime,"%Y") AS date, 1 AS n FROM goods WHERE categoryId IN(26,263) AND isDelete = 0) AS z GROUP BY date ORDER BY date ASC';
                break;
            case 'custom':
                $sql = 'SELECT date AS a, SUM(n) AS b FROM (SELECT DATE_FORMAT(createTime,"%Y-%m") AS date, 1 AS n FROM goods WHERE createTime >= "' . $beginDate . ' 00:00:00" AND createTime <= "' . $endDate . ' 23:59:59" AND categoryId IN(26,263) AND isDelete = 0) AS z GROUP BY date ORDER BY date ASC';
                break;
            default:
                $sql = '';
                break;
        }
        $v =[];
        if ($sql) {
            $v = $this->db->query($sql)->getResultArray();
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data'] = $v;
        return $this->response->setJSON($json);
    }
    // 开发趋势图
    public function goodsDevTrend()
    {
        if (!$this->myservice->verifyRight('SYS_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/Report/goodsDevTrend 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        set_time_limit(0); // 设置脚本运行时间无限制，直到脚本运行完毕
        session_write_close(); // 主动关闭session读写保护来解除锁定，避免其他访问无法读写session造成阻塞引起假死
        $type = $this->request->getGetPost('type');
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');
        switch ($type) {
            case 'near30days':
                $sql = 'SELECT date AS a, SUM(n) AS b FROM (SELECT DATE_FORMAT(beginDate,"%Y-%m-%d") AS date, 1 AS n FROM goods_task WHERE beginDate >= "' . date('Y-m-d', strtotime(date('Y-m-d') . " -30 day")) . '" AND beginDate <= "' . date('Y-m-d') . '" AND isDelete = 0) AS z GROUP BY date ORDER BY date ASC';
                break;
            case 'lastmonth':
                $sql = 'SELECT date AS a, SUM(n) AS b FROM (SELECT DATE_FORMAT(beginDate,"%Y-%m-%d") AS date, 1 AS n FROM goods_task WHERE beginDate >= "' . date('Y-m-01', strtotime(date('Y-m-01') . " -1 month")) . '" AND beginDate < "' . date('Y-m-01') . '" AND isDelete = 0) AS z GROUP BY date ORDER BY date ASC';
                break;
            case 'near12months':
                $sql = 'SELECT date AS a, SUM(n) AS b FROM (SELECT DATE_FORMAT(beginDate,"%Y-%m") AS date, 1 AS n FROM goods_task WHERE beginDate >= "' . date('Y-m-01', strtotime(date('Y-m-01') . " -11 month")) . '" AND beginDate <= "' . date('Y-m-d') . '" AND isDelete = 0) AS z GROUP BY date ORDER BY date ASC';
                break;
            case 'all':
                $sql = 'SELECT date AS a, SUM(n) AS b FROM (SELECT DATE_FORMAT(beginDate,"%Y") AS date, 1 AS n FROM goods_task WHERE isDelete = 0) AS z GROUP BY date ORDER BY date ASC';
                break;
            case 'custom':
                $sql = 'SELECT date AS a, SUM(n) AS b FROM (SELECT DATE_FORMAT(beginDate,"%Y-%m") AS date, 1 AS n FROM goods_task WHERE beginDate >= "' . $beginDate . '" AND beginDate <= "' . $endDate . '" AND isDelete = 0) AS z GROUP BY date ORDER BY date ASC';
                break;
            default:
                $sql = '';
                break;
        }
        $v = [];
        if ($sql) {
            $v = $this->db->query($sql)->getResultArray();
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data'] = $v;
        return $this->response->setJSON($json);
    }

    /**销售相关 */
    // 销售明细
    public function salesDetail()
    {
        if (!$this->myservice->verifyRight('SALES_DETAIL')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/Report/salesDetail 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        set_time_limit(0); // 设置脚本运行时间无限制，直到脚本运行完毕
        session_write_close(); // 主动关闭session读写保护来解除锁定，避免其他访问无法读写session造成阻塞引起假死
        $type = $this->request->getGetPost('type');
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $storageId = $this->request->getGetPost('storageId');
        $contactId = $this->request->getGetPost('contactId');
        $goodsId = $this->request->getGetPost('goodsId');
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');
        if ($type == 'order') { // 采购订单
            $builder = $this->db->table('bill_order_info AS a');
            $builder->where('a.billType', 'SALE')->where('a.goodsId >', 0);
            if ($storageId) {
                $builder->whereIn('a.storageId', explode(',', $storageId));
            }
            if ($contactId) {
                $builder->whereIn('a.contactId', explode(',', $contactId));
            }
            if ($goodsId) {
                $builder->whereIn('a.goodsId', explode(',', $goodsId));
            }
            if ($beginDate) {
                $builder->where('a.billDate >=', $beginDate);
            }
            if ($endDate) {
                $builder->where('a.billDate <=', $endDate);
            }
            $builder->where('a.isDelete', 0);
            $allowedSort = ['id','billId','billNo','billDate','billType'];
            $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
            $list_order = (clone $builder)->select(['a.id', 'a.iid billId', 'a.billNo', 'a.billDate', '"销售订单" AS billType', 'a.contactId', 'a.storageId', 'a.goodsId', 'a.orderQty AS qty', 'a.price', 'a.amount', 'b.no', 'b.name', 'b.spec', 'b.sku', 'b.brandName AS brand', 'b.unitName AS unit', 'c.name AS contact', 'd.name AS storage'])
                ->join('goods AS b', 'a.goodsId = b.id AND b.isDelete = 0', 'left')
                ->join('contact AS c', 'a.contactId = c.id AND c.isDelete = 0', 'left')
                ->join('storage AS d', 'a.storageId = d.id AND d.isDelete = 0', 'left')->orderBy($orderField, $sord)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
            $total = (clone $builder)->countAllResults();
            $v = [];
            // 最终返回数据
            foreach ($list_order as $arr => $row) {
                $v[$arr]['billId'] = intval($row['billId']);
                $v[$arr]['billNo'] = $row['billNo'];
                $v[$arr]['billDate'] = $row['billDate'];
                $v[$arr]['billType'] = $row['billType'];
                $v[$arr]['contactId'] = intval($row['contactId']);
                $v[$arr]['contact'] = $row['contact'];
                $v[$arr]['no'] = $row['no'];
                $v[$arr]['name'] = $row['name'];
                $v[$arr]['spec'] = $row['spec'];
                $v[$arr]['sku'] = $row['sku'];
                $v[$arr]['goods'] = $row['no'] . ' ' . $row['name'] . ($row['spec'] ? ' ' . $row['spec'] : '') . ($row['sku'] ? ' ' . $row['sku'] : '');
                $v[$arr]['brand'] = $row['brand'];
                $v[$arr]['unit'] = $row['unit'];
                $v[$arr]['storage'] = $row['storage'];
                $v[$arr]['qty'] = intval($row['qty']);
                $v[$arr]['price'] = (float)$row['price'];
                $v[$arr]['amount'] = (float)$row['amount'];
            }
            $json['status'] = 200;
            $json['msg'] = 'success';
            $json['data']['page'] = $page;
            $json['data']['records'] = $total;
            $json['data']['total'] = ceil($json['data']['records'] / $rows);
            $json['data']['rows'] = $v;
            $json['data']['totalQty'] = (clone $builder)->select('SUM(orderQty) AS totalQty')->get()->getRowArray()['totalQty'] ?? 0;
            $json['data']['totalAmount'] = (clone $builder)->select('SUM(amount) AS totalAmount')->get()->getRowArray()['totalAmount'] ?? 0;
            return $this->response->setJSON($json);
        } else { // 特殊采购
            $builder = $this->db->table('bill_contract_info AS a');
            $builder->where('a.billType', 'SALE')->where('a.goodsId >', 0);
            if ($storageId) {
                $builder->whereIn('a.storageId', explode(',', $storageId));
            }
            if ($contactId) {
                $builder->whereIn('a.contactId', explode(',', $contactId));
            }
            if ($goodsId) {
                $builder->whereIn('a.goodsId', explode(',', $goodsId));
            }
            if ($beginDate) {
                $builder->where('a.billDate >=', $beginDate);
            }
            if ($endDate) {
                $builder->where('a.billDate <=', $endDate);
            }
            $builder->where('a.isDelete', 0);

            $allowedSort = ['id','billId','billNo','billDate','billType'];
            $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
            $list_contract = (clone $builder)->select(['a.id', 'a.iid AS billId', 'a.billNo', 'a.billDate', '"特殊销售单" AS billType', 'a.contactId', 'a.storageId', 'a.goodsId', 'a.qty', 'a.price', 'a.amount', 'b.no', 'b.name', 'b.spec', 'b.sku', 'b.brandName AS brand', 'b.unitName AS unit', 'c.name AS contact', 'd.name AS storage'])
            ->join('goods AS b', 'a.goodsId = b.id', 'left')
            ->join('contact AS c', 'a.contactId = c.id', 'left')
            ->join('storage AS d', 'a.storageId = d.id', 'left')->orderBy($orderField, $sord)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
            $total = (clone $builder)->countAllResults();
            $v = [];
            // 最终返回数据
            foreach ($list_contract as $arr => $row) {
                $v[$arr]['billId'] = intval($row['billId']);
                $v[$arr]['billNo'] = $row['billNo'];
                $v[$arr]['billDate'] = $row['billDate'];
                $v[$arr]['billType'] = $row['billType'];
                $v[$arr]['contactId'] = intval($row['contactId']);
                $v[$arr]['contact'] = $row['contact'];
                $v[$arr]['no'] = $row['no'];
                $v[$arr]['name'] = $row['name'];
                $v[$arr]['spec'] = $row['spec'];
                $v[$arr]['sku'] = $row['sku'];
                $v[$arr]['goods'] = $row['no'] . ' ' . $row['name'] . ($row['spec'] ? ' ' . $row['spec'] : '') . ($row['sku'] ? ' ' . $row['sku'] : '');
                $v[$arr]['brand'] = $row['brand'];
                $v[$arr]['unit'] = $row['unit'];
                $v[$arr]['storage'] = $row['storage'];
                $v[$arr]['qty'] = intval($row['qty']);
                $v[$arr]['price'] = (float)$row['price'];
                $v[$arr]['amount'] = (float)$row['amount'];
            }
            $json['status'] = 200;
            $json['msg'] = 'success';
            $json['data']['page'] = $page;
            $json['data']['records'] = $total;
            $json['data']['total'] = ceil($json['data']['records'] / $rows);
            $json['data']['rows'] = $v;
            $json['data']['totalQty'] = (clone $builder)->select('SUM(qty) as totalQty')->get()->getRowArray()['totalQty'] ?? 0;
            $json['data']['totalAmount'] = (clone $builder)->select('SUM(amount) as totalAmount')->get()->getRowArray()['totalAmount'] ?? 0;
            return $this->response->setJSON($json);
        }
    }
    // 销售排名
    public function salerRank()
    {
        if (!$this->myservice->verifyRight('SALER_RANK')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/Report/salerRank 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        set_time_limit(0); // 设置脚本运行时间无限制，直到脚本运行完毕
        session_write_close(); // 主动关闭session读写保护来解除锁定，避免其他访问无法读写session造成阻塞引起假死
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 1000);
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');

        $orderNameArr = $this->db->table('bill_order')
            ->select('uName')
            ->where('uId >', 0)
            ->where('uName !=', '')
            ->where('uName !=', '系统')
            ->where('createTime >=', $beginDate . ' 00:00:00')
            ->where('createTime <=', $endDate . ' 23:59:59')
            ->where('billType', 'SALE')
            ->where('checkId >', 0)
            ->where('moneyStatus1', 2)
            ->where('isCancel', 0)
            ->where('isDelete', 0)
            ->groupBy('uName')
            ->get()
            ->getResultArray();
        $contractNameArr = $this->db->table('bill_contract')
            ->select('saler')
            ->where(['deliverDoneTime >=' => $beginDate . ' 00:00:00', 'deliverDoneTime <=' => $endDate . ' 23:59:59', 'billType' => 'SALE', 'checkId >' => 0, 'goodsStatus1 >' => 0, 'redoSoType' => 0])
            ->where('saler !=', '')
            ->where('saler !=', '系统')
            ->where('isDelete', 0)
            ->groupBy('saler')
            ->get()
            ->getResultArray();
        $nameStr = '';
        foreach ($orderNameArr as $k => $v) {
            $nameStr .= ',' . $v['uName'];
        }
        foreach ($contractNameArr as $k1 => $v1) {
            $nameStr .= ',' . $v1['saler'];
        }
        $nameStr = trim($nameStr, ',');
        $nameArr = explode(',', $nameStr);
        $nameArr = array_unique($nameArr);
        $v = [];
        $totalAmountTotal = $onlineshopAmountTotal = $contractAmountTotal = $orderAmountTotal = 0;
        if (count($nameArr) > 0) {
            foreach ($nameArr as $arr => $row) {
                if ($row) {
                    // 订单金额
                    $orderAmount = $this->db->table('bill_order')->select('SUM(amount) AS orderAmount')->where(['createTime >=' => $beginDate . ' 00:00:00', 'createTime <=' => $endDate . ' 23:59:59', 'billType' => 'SALE', 'uName' => $row, 'checkId >' => 0, 'moneyStatus1' => 2, 'isCancel' => 0, 'isDelete' => 0])->get()->getRowArray()['orderAmount'] ?? 0;
                    // 特销金额
                    $contractAmount = $this->db->table('bill_contract')->select('SUM(CASE WHEN LOCATE(",", saler) > 0 THEN (amount / ((CHAR_LENGTH(saler) - CHAR_LENGTH(REPLACE(saler,",",""))) + 1) ) ELSE amount END) AS contractAmount', false)->where(['deliverDoneTime >=' => $beginDate . ' 00:00:00', 'deliverDoneTime <=' => $endDate . ' 23:59:59', 'billType' => 'SALE', 'checkId >' => 0, 'goodsStatus1 >' => 0, 'redoSoType' => 0, 'isDelete' => 0])->where('FIND_IN_SET(' . $this->db->escape($row) . ', saler) >', 0, false)->get()->getRowArray()['contractAmount'] ?? 0;

                    // 扣掉相应有退款单的退款金额
                    $orderRefundBills = $this->db->table('bill_order')->select('srcMoneyId2')->where(['createTime >=' => $beginDate . ' 00:00:00', 'createTime <=' => $endDate . ' 23:59:59', 'billType' => 'SALE', 'uName' => $row, 'checkId >' => 0, 'moneyStatus1' => 2, 'srcMoneyId2 >' => 0, 'isCancel' => 0, 'isDelete' => 0])->get()->getResultArray();
                    if (count($orderRefundBills) > 0) {
                        $orderRefundBillIds = array_column($orderRefundBills, 'srcMoneyId2');
                        $refundAmount = $this->db->table('bill_money_info')->select('SUM(amount) AS refundAmount')->whereIn('iid', $orderRefundBillIds)->where(['isDelete' => 0])->get()->getRowArray()['refundAmount'] ?? 0;
                        $orderAmount -= (float)$refundAmount;
                    }
                    $contractRefundBills = $this->db->table('bill_contract')->select('srcMoneyId2, saler')->where(['deliverDoneTime >=' => $beginDate . ' 00:00:00', 'deliverDoneTime <=' => $endDate . ' 23:59:59', 'billType' => 'SALE', 'checkId >' => 0, 'goodsStatus1 >' => 0, 'redoSoType' => 0, 'srcMoneyId2 >' => 0, 'isDelete' => 0])->where('FIND_IN_SET(' . $this->db->escape($row) . ', saler) >', 0, false)->get()->getResultArray();
                    if (count($contractRefundBills) > 0) {
                        foreach ($contractRefundBills as $bill) {
                            $refundAmount = $this->db->table('bill_money_info')->select('SUM(amount) AS refundAmount')->where(['iid' => $bill['srcMoneyId2'], 'isDelete' => 0])->get()->getRowArray()['refundAmount'] ?? 0;
                            if (strpos($bill['saler'], ',') !== false) {
                                $salerArray = explode(',', $bill['saler']);
                                $salerCount = count($salerArray);
                                if ($salerCount > 0) {
                                    $contractAmount -= (float)$refundAmount / $salerCount;
                                }
                            } else {
                                $contractAmount -= (float)$refundAmount;
                            }
                        }
                    }
                    // 网店金额
                    $onlineshopAmount = $this->db->table('bill_onlineshopMp_info')->select('SUM(amount) AS onlineshopAmount')->where(['beginDate >=' => $beginDate, 'endDate <=' => $endDate, 'uName' => $row, 'isDelete' => 0])->get()->getRowArray()['onlineshopAmount'] ?? 0;
                    $v[] = [
                        'uName' => $row,
                        'dateRange' => $beginDate . ' ~ ' . $endDate,
                        'orderAmount' => round((float)$orderAmount, 2),
                        'contractAmount' => round((float)$contractAmount, 2),
                        'onlineshopAmount' => round((float)$onlineshopAmount, 2),
                        'totalAmount' => round(((float)$orderAmount + (float)$contractAmount + (float)$onlineshopAmount), 2)
                    ];
                }
            }
        }
        // （二维数组）依次按总额、网店金额、特殊销售金额、手工订单金额进行多维降序排序（当前面排序字段值相等时，依次以后面另一个字段继续排序，类似数据库多字段排序）
        if (count($v) > 0) {
            $sort1 = array_column($v, 'totalAmount');
            $sort2 = array_column($v, 'onlineshopAmount');
            $sort3 = array_column($v, 'contractAmount');
            $sort4 = array_column($v, 'orderAmount');
            array_multisort($sort1, SORT_DESC, $sort2, SORT_DESC, $sort3, SORT_DESC, $sort4, SORT_DESC, $v);
        }
        foreach ($v as $aa => $bb) {
            $totalAmountTotal += $bb['totalAmount'];
            $onlineshopAmountTotal += $bb['onlineshopAmount'];
            $contractAmountTotal += $bb['contractAmount'];
            $orderAmountTotal += $bb['orderAmount'];
            if (!$this->myservice->verifyRight('SYS_ADVANCE') && $bb['uName'] != $this->myerp['uName']) {
                $v[$aa]['orderAmount'] = '???';
                $v[$aa]['contractAmount'] = '???';
                $v[$aa]['onlineshopAmount'] = '???';
                $v[$aa]['totalAmount'] = '???';
            }
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['page'] = $page;
        $json['data']['records'] = count($v);
        $json['data']['total'] = ceil($json['data']['records'] / $rows);
        $json['data']['rows'] = $v;
        $json['data']['totalAmountTotal'] = round($totalAmountTotal, 2);
        $json['data']['onlineshopAmountTotal'] = round($onlineshopAmountTotal, 2);
        $json['data']['contractAmountTotal'] = round($contractAmountTotal, 2);
        $json['data']['orderAmountTotal'] = round($orderAmountTotal, 2);
        return $this->response->setJSON($json);
    }
    // 销量排行图
    public function salesRank()
    {
        if (!$this->myservice->verifyRight('SALES_RANK')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/Report/salesRank 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        set_time_limit(0); // 设置脚本运行时间无限制，直到脚本运行完毕
        session_write_close(); // 主动关闭session读写保护来解除锁定，避免其他访问无法读写session造成阻塞引起假死
        $categoryId = intval($this->request->getGetPost('categoryId'));
        $type = intval($this->request->getGetPost('type'));
        $datetype = intval($this->request->getGetPost('datetype'));
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');
        $limit = intval($this->request->getGetPost('limit'));
        $export = intval($this->request->getGetPost('export'));
        $where_1 = '';
        $where_2 = '';
        $where_3 = '';
        if ($categoryId > 0) {
            $cid = array_column($this->db->table('category')->select('id')->where('FIND_IN_SET(' . $categoryId . ', path) >', 0, false)->where('isDelete', 0)->get()->getResultArray(), 'id');
            if (is_array($cid) && count($cid) > 0) {
                $cid = join(',', $cid);
                $where_1 .= ' AND t2.categoryId IN(' . $cid . ')';
            }
        }
        if ($datetype == 1) {
            if ($beginDate && $endDate) {
                $where_2 .= ' AND t1.billDate >= "' . $this->db->escapeString($beginDate) . '" AND t1.billDate <= "' . $this->db->escapeString($endDate) . '"';
                $where_3 .= ' AND t1.billDate >= "' . $this->db->escapeString($beginDate) . '" AND t1.billDate <= "' . $this->db->escapeString($endDate) . '"';
            }
        } elseif ($datetype == 2) {
            $where_2 .= ' AND t1.billDate >= "' . date('Y-m-01') . '" AND t1.billDate <= "' . date('Y-m-d') . '"';
            $where_3 .= ' AND t1.billDate >= "' . date('Y-m-01') . '" AND t1.billDate <= "' . date('Y-m-d') . '"';
        } elseif ($datetype == 3) {
            $where_2 .= ' AND t1.billDate >= "' . date('Y-m-01', strtotime(date('Y-m-01') . " -1 month")) . '" AND t1.billDate < "' . date('Y-m-01') . '"';
            $where_3 .= ' AND t1.billDate >= "' . date('Y-m-01', strtotime(date('Y-m-01') . " -1 month")) . '" AND t1.billDate < "' . date('Y-m-01') . '"';
        } elseif ($datetype == 4) {
            $where_2 .= ' AND t1.billDate >= "' . date('Y-m-01', strtotime(date('Y-m-01') . " -11 month")) . '" AND t1.billDate <= "' . date('Y-m-d') . '"';
            $where_3 .= ' AND t1.billDate >= "' . date('Y-m-01', strtotime(date('Y-m-01') . " -11 month")) . '" AND t1.billDate <= "' . date('Y-m-d') . '"';
        }
        $sql = 'SELECT goods AS a, SUM(num) AS b FROM ('
            . '(SELECT CONCAT_WS(" ", t2.no, t2.name, t2.spec, t2.sku) AS goods, SUM(t1.' . ($type == 2 ? 'orderQty' : 'amount') . ') AS num FROM bill_order_info AS t1 LEFT JOIN goods AS t2 ON t1.goodsId = t2.id WHERE t1.billType = "SALE" ' . $where_2 . ' AND t1.checkId > 0 AND t1.goodsId > 0 AND t1.isDelete = 0 AND t2.no != "" ' . $where_1 . ' GROUP BY goods ORDER BY num DESC)'
            . ' UNION ALL '
            . '(SELECT CONCAT_WS(" ", t2.no, t2.name, t2.spec, t2.sku) AS goods, SUM(t1.' . ($type == 2 ? 'qty' : 'amount') . ') AS num FROM bill_contract_info AS t1 LEFT JOIN goods AS t2 ON t1.no = t2.no WHERE t1.no != "" AND t1.billType = "SALE" ' . $where_3 . ' AND t1.isDelete = 0 ' . $where_1 . ' AND t2.no != "" GROUP BY goods ORDER BY num DESC)'
            . ') AS c GROUP BY a ORDER BY b DESC LIMIT ' . $limit;
        $sum_list = $this->db->query($sql)->getResultArray();
        if ($export == 1) {
            // 调用spreadsheet处理表格
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $activeWorksheet = $spreadsheet->getActiveSheet();

            // A-Z列 设置字符串自动换行(会撑开单元格高度,并破坏设置的默认行高)，设置水平居左+垂直居中
            $activeWorksheet->getStyle("A:Z")->getAlignment()->setWrapText(TRUE);
            $activeWorksheet->getStyle('A:Z')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
            $activeWorksheet->getStyle('A:Z')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

            $name = '销量排行表格' . date('Y-m-d H:i:s');

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
            $activeWorksheet->getStyle('A1:C' . (COUNT($sum_list) + 3))->applyFromArray($styleArray, false);

            // 设置加粗
            $activeWorksheet->getStyle('A1')->getFont()->setBold(true);
            $activeWorksheet->getStyle('A3:C3')->getFont()->setBold(true);

            // 设置背景色
            $activeWorksheet->getStyle('A3:C3')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
            $activeWorksheet->getStyle('A3:C3')->getFill()->getStartColor()->setRGB('F5F5F5');

            // 合并单元格
            $activeWorksheet->mergeCells('A1:C1');
            $activeWorksheet->mergeCells('A2:C2');

            // 设置行高
            $activeWorksheet->getRowDimension('1')->setRowHeight(40);

            // 设置水平居中
            $activeWorksheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            // 设置每列列宽
            $activeWorksheet->getColumnDimension('A')->setWidth(10);
            $activeWorksheet->getColumnDimension('B')->setWidth(100);
            $activeWorksheet->getColumnDimension('C')->setWidth(20);

            //设置当前的sheet
            $activeWorksheet->setCellValue('A1', $name);
            $categoryName = $this->db->table('category')->select('name')->where('id', $categoryId)->get()->getRowArray()['name'] ?? '所有产品类别';
            $activeWorksheet->setCellValue('A2', '产品类别：' . $categoryName . '，排行基准：' . ($type == 2 ? '按销售量' : '销售额') . '，排行时段：' . $beginDate . ' - ' . $endDate . '，导出时间：' . date('Y-m-d H:i:s'));
            $activeWorksheet->setCellValue('A3', '排名');
            $activeWorksheet->setCellValue('B3', '产品');
            $activeWorksheet->setCellValue('C3', ($type == 2 ? '销售量' : '销售额'));

            //设置某些列为文本格式
            $activeWorksheet->getStyle('A1:C' . (count($sum_list) + 3))->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);

            $i = 4;
            foreach ($sum_list as $k => $v) {
                $activeWorksheet->setCellValue('A' . $i, $k + 1);
                $activeWorksheet->setCellValue('B' . $i, $v['a']);
                $activeWorksheet->setCellValue('C' . $i, (string)$v['b']);
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
        } else {
            $json['status'] = 200;
            $json['msg'] = 'success';
            $json['data'] = $sum_list;
            return $this->response->setJSON($json);
        }
    }
    // 平台占比图
    public function salesDistribution()
    {
        if (!$this->myservice->verifyRight('SYS_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/Report/salesDistribution 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        set_time_limit(0); // 设置脚本运行时间无限制，直到脚本运行完毕
        session_write_close(); // 主动关闭session读写保护来解除锁定，避免其他访问无法读写session造成阻塞引起假死
        $type = $this->request->getGetPost('type');
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');
        switch ($type) {
            case 'near30days':
                $sql = 'SELECT billFrom AS a, (CASE WHEN SUM(amount) <= 0 THEN 0.01 ELSE SUM(amount) END) AS b FROM (SELECT billFrom, amount FROM bill_order WHERE billDate >= "' . date('Y-m-d', strtotime(date('Y-m-d') . " -30 day")) . '" AND billDate <= "' . date('Y-m-d') . '" AND billType = "SALE" AND isDelete = 0 UNION ALL SELECT "特殊销售" AS billFrom, amount FROM bill_contract WHERE billDate >= "' . date('Y-m-d', strtotime(date('Y-m-d') . " -30 day")) . '" AND billDate <= "' . date('Y-m-d') . '" AND billType = "SALE" AND redoSoType = 0 AND isDelete = 0) c GROUP BY a ORDER BY a ASC';
                break;
            case 'lastmonth':
                $sql = 'SELECT billFrom AS a, (CASE WHEN SUM(amount) <= 0 THEN 0.01 ELSE SUM(amount) END) AS b FROM (SELECT billFrom, amount FROM bill_order WHERE billDate >= "' . date('Y-m-01', strtotime(date('Y-m-01') . " -1 month")) . '" AND billdate < "' . date('Y-m-01') . '" AND billType = "SALE" AND isDelete = 0 UNION ALL SELECT "特殊销售" billFrom, amount amount FROM bill_contract WHERE billDate >= "' . date('Y-m-01', strtotime(date('Y-m-01') . " -1 month")) . '" AND billDate < "' . date('Y-m-01') . '" AND billType = "SALE" AND redoSoType = 0 AND isDelete = 0) c GROUP BY a ORDER BY a ASC';
                break;
            case 'near12months':
                $sql = 'SELECT billFrom AS a, (CASE WHEN SUM(amount) <= 0 THEN 0.01 ELSE SUM(amount) END) AS b FROM (SELECT billFrom, amount FROM bill_order WHERE billDate >= "' . date('Y-m-01', strtotime(date('Y-m-01') . " -11 month")) . '" AND billdate <= "' . date('Y-m-d') . '" AND billType = "SALE" AND isDelete = 0 UNION ALL SELECT "特殊销售" AS billFrom, amount FROM bill_contract WHERE billDate >= "' . date('Y-m-01', strtotime(date('Y-m-01') . " -11 month")) . '" AND billDate <= "' . date('Y-m-d') . '" AND billType = "SALE" AND redoSoType = 0 AND isDelete = 0) c GROUP BY a ORDER BY a ASC';
                break;
            case 'all':
                $sql = 'SELECT billFrom AS a, (CASE WHEN SUM(amount) <= 0 THEN 0.01 ELSE SUM(amount) END) AS b FROM (SELECT billFrom, amount FROM bill_order WHERE billType = "SALE" AND isDelete = 0 UNION ALL SELECT "特殊销售" AS billFrom, amount FROM bill_contract WHERE billType = "SALE" AND redoSoType = 0 AND isDelete = 0) c GROUP BY a ORDER BY a ASC';
                break;
            case 'custom':
                $sql = 'SELECT billFrom AS a, (CASE WHEN SUM(amount) <= 0 THEN 0.01 ELSE SUM(amount) END) AS b FROM (SELECT billFrom, amount FROM bill_order WHERE billDate >= "' . $this->db->escapeString($beginDate) . '" AND billdate <= "' . $this->db->escapeString($endDate) . '" AND billType = "SALE" AND isDelete = 0 UNION ALL SELECT "特殊销售" AS billFrom, amount FROM bill_contract WHERE billDate >= "' . $this->db->escapeString($beginDate) . '" AND billDate <= "' . $this->db->escapeString($endDate) . '" AND billType = "SALE" AND redoSoType = 0 AND isDelete = 0) c GROUP BY a ORDER BY a ASC';
                break;
            default:
                $sql = '';
                break;
        }
        $v = [];
        if ($sql) {
            $v = $this->db->query($sql)->getResultArray();
        }
        foreach ($v as $arr => $row) {
            $v[$arr]['b'] = round((float)$v[$arr]['b'], 2);
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data'] = $v;
        return $this->response->setJSON($json);
    }
    // 销量趋势图
    public function salesTrend()
    {
        if (!$this->myservice->verifyRight('SYS_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/Report/salesTrend 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        set_time_limit(0); // 设置脚本运行时间无限制，直到脚本运行完毕
        session_write_close(); // 主动关闭session读写保护来解除锁定，避免其他访问无法读写session造成阻塞引起假死
        $type = $this->request->getGetPost('type');
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');
        switch ($type) {
            case 'near30days':
                $sql = 'SELECT date AS a, SUM(amount) AS b FROM (SELECT billDate AS date, amount FROM bill_order WHERE billDate >= "' . date('Y-m-d', strtotime(date('Y-m-d') . " -30 day")) . '" AND billDate <= "' . date('Y-m-d') . '" AND billType = "SALE" AND isDelete = 0 UNION ALL SELECT billDate AS date, amount FROM bill_contract WHERE billDate >= "' . date('Y-m-d', strtotime(date('Y-m-d') . " -30 day")) . '" AND billDate <= "' . date('Y-m-d') . '" AND billType = "SALE" AND redoSoType = 0 AND isDelete = 0) c GROUP BY a ORDER BY a ASC';
                break;
            case 'lastmonth':
                $sql = 'SELECT date AS a, SUM(amount) AS b FROM (SELECT CONCAT(DATE_FORMAT(billDate,"%d"),"日") AS date, amount FROM bill_order WHERE billDate >= "' . date('Y-m-01', strtotime(date('Y-m-01') . " -1 month")) . '" AND billDate < "' . date('Y-m-01') . '" AND billType = "SALE" AND isDelete = 0 UNION ALL SELECT CONCAT(DATE_FORMAT(billDate,"%d"),"日") AS date, amount FROM bill_contract WHERE billDate >= "' . date('Y-m-01', strtotime(date('Y-m-01') . " -1 month")) . '" AND billDate < "' . date('Y-m-01') . '" AND billType = "SALE" AND redoSoType = 0 AND isDelete = 0) c GROUP BY a ORDER BY a ASC';
                break;
            case 'near12months':
                $sql = 'SELECT date AS a, SUM(amount1) AS b, SUM(amount2) AS b2 FROM (SELECT DATE_FORMAT(billDate,"%Y-%m") AS date, amount AS amount1, 0 AS amount2 FROM bill_order WHERE billDate >= "' . date('Y-m-01', strtotime(date('Y-m-01') . " -11 month")) . '" AND billDate <= "' . date('Y-m-d') . '" AND billType = "SALE" AND isDelete = 0 UNION ALL SELECT DATE_FORMAT(billDate,"%Y-%m") AS date, amount AS amount1, 0 AS amount2 FROM bill_contract WHERE billDate >= "' . date('Y-m-01', strtotime(date('Y-m-01') . " -11 month")) . '" AND billDate <= "' . date('Y-m-d') . '" AND billType = "SALE" AND redoSoType = 0 AND isDelete = 0 UNION ALL SELECT DATE_FORMAT(DATE_ADD(billDate, INTERVAL 1 YEAR),"%Y-%m") AS date, 0 AS amount1, amount AS amount2 FROM bill_order WHERE billDate >= "' . date('Y-m-01', strtotime(date('Y-m-01') . " -23 month")) . '" AND billDate < "' . date('Y-m-01', strtotime(date('Y-m-01') . " -11 month")) . '" AND billType = "SALE" AND isDelete = 0 UNION ALL SELECT DATE_FORMAT(DATE_ADD(billDate, INTERVAL 1 YEAR),"%Y-%m") AS date, 0 AS amount1, amount AS amount2 FROM bill_contract WHERE billDate >= "' . date('Y-m-01', strtotime(date('Y-m-01') . " -23 month")) . '" AND billDate < "' . date('Y-m-01', strtotime(date('Y-m-01') . " -11 month")) . '" AND billType = "SALE" AND redoSoType = 0 AND isDelete = 0) c GROUP BY a ORDER BY a ASC';
                break;
            case 'all':
                $sql = 'SELECT date AS a, SUM(amount) AS b FROM (SELECT DATE_FORMAT(billDate,"%Y") AS date, amount FROM bill_order WHERE billType = "SALE" AND isDelete = 0 UNION ALL SELECT DATE_FORMAT(billDate,"%Y") AS date, amount FROM bill_contract WHERE billType = "SALE" AND redoSoType = 0 AND isDelete = 0) c GROUP BY a ORDER BY a ASC';
                break;
            case 'custom':
                if (((strtotime(date($endDate)) - strtotime(date($beginDate))) / 86400) > 100) {
                    $dateFomat = '"%Y-%m"';
                } else {
                    $dateFomat = '"%Y-%m-%d"';
                }
                $sql = 'SELECT date AS a, SUM(amount) AS b FROM (SELECT DATE_FORMAT(billDate,' . $dateFomat . ') AS date, amount FROM bill_order WHERE billDate >= "' . $beginDate . '" AND billDate <= "' . $endDate . '" AND billType = "SALE" AND isDelete = 0 UNION ALL SELECT DATE_FORMAT(billDate,' . $dateFomat . ') AS date, amount FROM bill_contract WHERE billDate >= "' . $beginDate . '" AND billDate <= "' . $endDate . '" AND billType = "SALE" AND redoSoType = 0 AND isDelete = 0) c GROUP BY a ORDER BY a ASC';
                break;
            default:
                $sql = '';
                break;
        }
        $v = [];
        if ($sql) {
            $v = $this->db->query($sql)->getResultArray();
        }
        foreach ($v as $arr => $row) {
            $v[$arr]['b'] = round((float)$v[$arr]['b'], 2);
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data'] = $v;
        return $this->response->setJSON($json);
    }
    // 报价参考
    public function bidQuoteReference()
    {
        if (!$this->myservice->verifyRight('BIDINFO_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/Report/bidQuoteReference 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        set_time_limit(0); // 设置脚本运行时间无限制，直到脚本运行完毕
        session_write_close(); // 主动关闭session读写保护来解除锁定，避免其他访问无法读写session造成阻塞引起假死
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $skey = $this->request->getGetPost('skey');
        $builder = $this->db->table('bill_contract_info');
        $builder->where('billType', 'PUR')->where('paidAmount >', 0)->where('uId >', 0)->whereIn('isReq', [0, 2])->where('isDelete', 0);
        if ($skey !== '' && $skey !== null) {
            $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
            foreach ($keywords as $kw) {
                $builder->groupStart()
                    ->like('name', $this->db->escapeLikeString($kw))
                    ->orLike('spec', $this->db->escapeLikeString($kw))
                    ->orLike('sku', $this->db->escapeLikeString($kw))
                    ->orLike('description1', $this->db->escapeLikeString($kw))
                    ->orLike('description2', $this->db->escapeLikeString($kw))
                ->groupEnd();
            }
        }
        $allowedSort = ['id','price'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $list = (clone $builder)->orderBy($orderField, $sord)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['billDate'] = $row['billDate'];
            $v[$arr]['name'] = $row['name'];
            $v[$arr]['spec'] = $row['spec'];
            $v[$arr]['sku'] = $row['sku'];
            $v[$arr]['brand'] = $row['brand'];
            $v[$arr]['unit'] = $row['unit'];
            $v[$arr]['price'] = round((float)$row['price'] * 1.25, 2);
            $v[$arr]['buyUrl'] = $row['buyUrl'];
            $v[$arr]['myUrl'] = $row['myUrl'];
            $v[$arr]['description1'] = $row['description1'];
            $v[$arr]['description2'] = $row['description2'];
            $v[$arr]['isHasExpress'] = intval($row['isHasExpress']);
            $v[$arr]['isHasInvoice'] = intval($row['isHasInvoice']);
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['page'] = $page;
        $json['data']['records'] = $total;
        $json['data']['total'] = ceil($json['data']['records'] / $rows);
        $json['data']['rows'] = $v;
        return $this->response->setJSON($json);
    }
    // 投标排名
    public function bidderRank()
    {
        if (!$this->myservice->verifyRight('BIDDER_RANK')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/Report/bidderRank 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        set_time_limit(0); // 设置脚本运行时间无限制，直到脚本运行完毕
        session_write_close(); // 主动关闭session读写保护来解除锁定，避免其他访问无法读写session造成阻塞引起假死
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 10000);
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');
        $builder = $this->db->table('bid_program');
        $builder->where('isDelete', 0);
        if ($beginDate) {
            $builder->where('createTime >=', $beginDate . ' 00:00:00');
        }
        if ($endDate) {
            $builder->where('createTime <=', $endDate . ' 23:59:59');
        }
        $quotedNamelist = (clone $builder)->select('quotedName')->where('quotedName !=', '')->orderBy('CONVERT(quotedName USING gbk)', 'ASC', false)->groupby('quotedName')->get()->getResultArray();
        $quotedNameStr = '';
        foreach ($quotedNamelist as $key => $val) {
            $quotedNameStr .= ',' . $val['quotedName'];
        }
        $quotedNameStr = trim($quotedNameStr, ',');
        $quotedNameArr = explode(',', $quotedNameStr);
        $quotedNameArr = array_unique($quotedNameArr);
        $offerCountTotal = $winCountTotal = $amountTotal = $winAmountTotal = 0;
        $v = [];
        foreach ($quotedNameArr as $arr => $row) {
            if ($row) {
                $offerInfo = (clone $builder)->select('SUM(CASE WHEN LOCATE(",", quotedName) > 0 THEN (amount / ((CHAR_LENGTH(quotedName) - CHAR_LENGTH(REPLACE(quotedName,",",""))) + 1)) ELSE amount END) AS amount, SUM(CASE WHEN LOCATE(",", quotedName) > 0 THEN (1 / ((CHAR_LENGTH(quotedName) - CHAR_LENGTH(REPLACE(quotedName,",",""))) + 1)) ELSE 1 END) AS offerCount', false)->where('FIND_IN_SET(' . $this->db->escape($row) . ', quotedName) >', 0, false)->get()->getRowArray();
                $v[$arr]['quotedName'] = $row;
                $v[$arr]['offerDateRange'] = $beginDate . ' ~ ' . $endDate;
                $v[$arr]['offerCount'] = round(($offerInfo['offerCount'] ?? 0), 2);
                $v[$arr]['amount'] = (float)($offerInfo['amount'] ?? 0);
                $winInfo = (clone $builder)->select('SUM(CASE WHEN LOCATE(",", quotedName) > 0 THEN (amount / ((CHAR_LENGTH(quotedName) - CHAR_LENGTH(REPLACE(quotedName,",",""))) + 1)) ELSE amount END) AS winAmount, SUM(CASE WHEN LOCATE(",", quotedName) > 0 THEN (1 / ((CHAR_LENGTH(quotedName) - CHAR_LENGTH(REPLACE(quotedName,",",""))) + 1)) ELSE 1 END) AS winCount', false)->where('FIND_IN_SET(' . $this->db->escape($row) . ', quotedName) >', 0, false)->where('status', 2)->get()->getRowArray();
                $v[$arr]['winCount'] = round(($winInfo['winCount'] ?? 0), 2);
                $v[$arr]['winAmount'] = (float)($winInfo['winAmount'] ?? 0);
                $v[$arr]['winRate'] = round((float)$v[$arr]['winCount'] / (float)$v[$arr]['offerCount'] * 100, 2);
            }
        }
        // 投标汇总信息（缺省值处理）
        $v = isset($v) ? array_values($v) : [];
        // 投标汇总信息（二维数组）依次按中标金额、投标金额、中标笔数、投标笔数进行多维降序排序（当前面排序字段值相等时，依次以后面另一个字段继续排序，类似数据库多字段排序）
        if (count($v) > 0) {
            $sort1 = array_column($v, 'winAmount');
            $sort2 = array_column($v, 'amount');
            $sort3 = array_column($v, 'winCount');
            $sort4 = array_column($v, 'offerCount');
            array_multisort($sort1, SORT_DESC, $sort2, SORT_DESC, $sort3, SORT_DESC, $sort4, SORT_DESC, $v);
        }
        foreach ($v as $aa => $bb) {
            $offerCountTotal += $bb['offerCount'];
            $winCountTotal += $bb['winCount'];
            $amountTotal += $bb['amount'];
            $winAmountTotal += $bb['winAmount'];
            if (!$this->myservice->verifyRight('SYS_ADVANCE') && $bb['quotedName'] != $this->myerp['uName']) {
                $v[$aa]['winAmount'] = '';
                $v[$aa]['amount'] = '';
            } else {
                $v[$aa]['winAmount'] = round($v[$aa]['winAmount'], 2);
                $v[$aa]['amount'] = round($v[$aa]['amount'], 2);
            }
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['page'] = $page;
        $json['data']['rows'] = $v;
        $json['data']['records'] = count($v);
        $json['data']['total'] = ceil($json['data']['records'] / $rows);
        $json['data']['offerCountTotal'] = ceil($offerCountTotal);
        $json['data']['winCountTotal'] = ceil($winCountTotal);
        $json['data']['amountTotal'] = round($amountTotal, 2);
        $json['data']['winAmountTotal'] = round($winAmountTotal, 2);
        return $this->response->setJSON($json);
    }
    // 中标分析
    public function bidProgramAnalyse()
    {
        if (!$this->myservice->verifyRight('BIDPROGRAM_ANALYSE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/Report/bidProgramAnalyse 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        set_time_limit(0); // 设置脚本运行时间无限制，直到脚本运行完毕
        session_write_close(); // 主动关闭session读写保护来解除锁定，避免其他访问无法读写session造成阻塞引起假死
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 1000);
        $type = intval($this->request->getGetPost('type'));
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');
        $builder = $this->db->table('bid_program');
        $builder->where('isDelete', 0);
        if ($beginDate) {
            $builder->where('createTime >=', $beginDate . ' 00:00:00');
        }
        if ($endDate) {
            $builder->where('createTime <=', $endDate . ' 23:59:59');
        }
        $target = '';
        if ($type == 1) {
            $target = 'contactName';
            $targetlist = array_column((clone $builder)->select('contactName')->where('contactName !=', '')->orderBy('id', 'ASC')->groupBy('contactName')->get()->getResultArray(), 'contactName');
        } elseif ($type == 2) {
            $target = 'bidSiteName';
            $targetlist = array_column((clone $builder)->select('bidSiteName')->where('bidSiteName !=', '')->orderBy('id', 'ASC')->groupBy('bidSiteName')->get()->getResultArray(), 'bidSiteName');
        } else {
            $target = 'sName';
            $targetlist = array_column((clone $builder)->select('sName')->where('sName !=', '')->orderBy('id', 'ASC')->groupBy('sName')->get()->getResultArray(), 'sName');
        }
        foreach ($targetlist as $arr => $row) {
            if ($row) {
                $res = (clone $builder)->select('SUM(amount) AS amount, SUM(1) AS offerCount')->like($target, $row)->get()->getRowArray();
                if ($res && isset($res['amount']) && $res['amount'] > 0) {
                    $v[$arr]['target'] = $row;
                    $v[$arr]['offerDateRange'] = $beginDate . ' ~ ' . $endDate;
                    $v[$arr]['offerCount'] = intval($res['offerCount']);
                    $v[$arr]['amount'] = (float)$res['amount'];
                    $res2 = (clone $builder)->select('SUM(amount) AS winAmount, SUM(1) AS winCount')->like($target, $row)->where('status', 2)->get()->getRowArray();
                    $v[$arr]['winCount'] = ($res2 && isset($res2['winCount'])) ? intval($res2['winCount']) : 0;
                    $v[$arr]['winAmount'] = ($res2 && isset($res2['winAmount'])) ? (float)$res2['winAmount'] : 0;
                    $v[$arr]['winRate'] = round($v[$arr]['winCount'] / $v[$arr]['offerCount'] * 100, 2);
                }
            }
        }
        // 投标汇总信息（缺省值处理）
        $v = isset($v) ? array_values($v) : [];
        // 投标汇总信息（二维数组）依次按中标金额、中标率、投标金额、中标笔数、投标笔数进行多维降序排序（当前面排序字段值相等时，依次以后面另一个字段继续排序，类似数据库多字段排序）
        if (count($v) > 0) {
            $sort0 = array_column($v, 'winAmount');
            $sort1 = array_column($v, 'winRate');
            $sort2 = array_column($v, 'amount');
            $sort3 = array_column($v, 'winCount');
            $sort4 = array_column($v, 'offerCount');
            array_multisort($sort0, SORT_DESC, $sort1, SORT_DESC, $sort2, SORT_DESC, $sort3, SORT_DESC, $sort4, SORT_DESC, $v);
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['page'] = $page;
        $json['data']['rows'] = $v;
        $json['data']['records'] = count($v);
        $json['data']['total'] = ceil($json['data']['records'] / $rows);
        $json['data']['offerCountTotal'] = (clone $builder)->where('quotedName !=', '')->countAllResults();
        $json['data']['winCountTotal'] = (clone $builder)->where('quotedName !=', '')->where('status', 2)->countAllResults();
        $json['data']['amountTotal'] = (clone $builder)->select('SUM(amount) AS amountTotal')->where('quotedName !=', '')->get()->getRowArray()['amountTotal'] ?? 0;
        $json['data']['winAmountTotal'] = (clone $builder)->select('SUM(amount) AS winAmountTotal')->where('quotedName !=', '')->where('status', 2)->get()->getRowArray()['winAmountTotal'] ?? 0;
        return $this->response->setJSON($json);
    }

    /**采购相关 */
    // 采购明细
    public function purchaseDetail()
    {
        if (!$this->myservice->verifyRight('PURCHASE_DETAIL')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/Report/purchaseDetail 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        set_time_limit(0); // 设置脚本运行时间无限制，直到脚本运行完毕
        session_write_close(); // 主动关闭session读写保护来解除锁定，避免其他访问无法读写session造成阻塞引起假死
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $storageId = $this->request->getGetPost('storageId');
        $contactId = $this->request->getGetPost('contactId');
        $goodsId = $this->request->getGetPost('goodsId');
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');
        $builder = $this->db->table('bill_order_info AS a');
        $builder->where('billType', 'PUR');
        if ($storageId) {
            $builder->whereIn('a.storageId', explode(',', $storageId));
        }
        if ($contactId) {
            $builder->whereIn('a.contactId', explode(',', $contactId));
        }
        if ($goodsId) {
            $builder->whereIn('a.goodsId', explode(',', $goodsId));
        }
        if ($beginDate) {
            $builder->where('a.billDate >=', $beginDate);
        }
        if ($endDate) {
            $builder->where('a.billDate <=', $endDate);
        }
        $builder->where('a.isDelete', 0);
        $allowedSort = ['id','billId','billNo','billDate','billType'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $list = (clone $builder)->select(['a.id', 'a.iid AS billId', 'a.billNo', 'a.billDate', "'采购订单' AS billType", 'a.contactId', 'a.storageId', 'a.goodsId', 'a.orderQty AS qty', 'a.price', 'a.amount', 'a.description AS des', 'a.uId', 'b.no', 'b.name', 'b.spec', 'b.sku', 'b.brandName AS brand', 'b.unitName AS unit', 'c.name AS contact', 'd.name AS storage', 'e.name AS uName'])
            ->join('goods AS b', 'a.goodsId = b.id AND b.isDelete = 0', 'left')
            ->join('contact AS c', 'a.contactId = c.id AND c.isDelete = 0', 'left')
            ->join('storage AS d', 'a.storageId = d.id AND d.isDelete = 0', 'left')
            ->join('user AS e', 'a.uId = e.id AND e.isDelete = 0', 'left')
            ->orderBy($orderField, $sord)
            ->limit($rows)
            ->offset($rows * ($page - 1))
            ->get()
            ->getResultArray();
        $total = (clone $builder)->countAllResults();
        $v = [];
        // 最终返回数据
        foreach ($list as $arr => $row) {
            $v[$arr]['billId'] = intval($row['billId']);
            $v[$arr]['billNo'] = $row['billNo'];
            $v[$arr]['billDate'] = $row['billDate'];
            $v[$arr]['billType'] = $row['billType'];
            $v[$arr]['contactId'] = intval($row['contactId']);
            $v[$arr]['contact'] = $row['contact'];
            $v[$arr]['no'] = $row['no'];
            $v[$arr]['name'] = $row['name'];
            $v[$arr]['spec'] = $row['spec'];
            $v[$arr]['sku'] = $row['sku'];
            $v[$arr]['goods'] = $row['no'] . ' ' . $row['name'] . ($row['spec'] ? ' ' . $row['spec'] : '') . ($row['sku'] ? ' ' . $row['sku'] : '');
            $v[$arr]['brand'] = $row['brand'];
            $v[$arr]['unit'] = $row['unit'];
            $v[$arr]['storage'] = $row['storage'];
            $v[$arr]['des'] = $row['des'];
            $v[$arr]['uName'] = $row['uName'];
            $v[$arr]['qty'] = intval($row['qty']);
            $v[$arr]['price'] = (float)$row['price'];
            $v[$arr]['amount'] = (float)$row['amount'];
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['page'] = $page;
        $json['data']['records'] = $total;
        $json['data']['total'] = ceil($json['data']['records'] / $rows);
        $json['data']['rows'] = $v;
        $json['data']['totalQty'] = (clone $builder)->selectSum('orderQty', 'totalQty')->get()->getRowArray()['totalQty'] ?? 0;
        $json['data']['totalAmount'] = (clone $builder)->selectSum('amount', 'totalAmount')->get()->getRowArray()['totalAmount'] ?? 0;
        return $this->response->setJSON($json);
    }
    // 采购成本
    public function purchaseCostSearch()
    {
        if (!$this->myservice->verifyRight('PURCHASE_COSTSEARCH')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/Report/purchaseCostSearch 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        set_time_limit(0); // 设置脚本运行时间无限制，直到脚本运行完毕
        session_write_close(); // 主动关闭session读写保护来解除锁定，避免其他访问无法读写session造成阻塞引起假死
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $goodsId = $this->request->getGetPost('goodsId');
        $builder = $this->db->table('bill_order_info AS a');
        $builder->whereIn('a.goodsId', $goodsId ? explode(',', $goodsId) : [-1])->where('a.billType', 'PUR')->where('a.isDelete', 0);
        $listBuilder = (clone $builder)->select(['a.id', 'a.iid', 'a.billNo', 'a.billDate', 'a.billType', 'a.transType', 'a.goodsId', 'a.contactId', 'a.price', 'b.no AS goodsNo', 'b.name AS goodsName', 'b.spec AS goodsSpec', 'b.sku AS goodsSku', 'b.brandName AS goodsBrandName', 'b.unitName AS goodsUnitName', 'b.categoryName AS goodsCategoryName', 'c.name AS contactName'])
        ->join('goods AS b', 'a.goodsId = b.id', 'left')
        ->join('contact AS c', 'a.contactId = c.id', 'left');
        $allowedSort = ['id','contactName','billNo','billDate','price'];
        if ($sidx && in_array($sidx, $allowedSort, true)) {
            $listBuilder->orderBy($sidx, $sord);
        } else {
            $listBuilder->orderBy('goodsId', 'ASC')->orderBy('billDate', 'DESC');
        }
        $list = (clone $listBuilder)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $total = (clone $listBuilder)->countAllResults();
        $costInfo = (clone $builder)->selectMin('a.price', 'minPrice')->selectMax('a.price', 'maxPrice')->selectSum('a.orderQty', 'totalQty')->selectSum('a.amount', 'tatalAmount')->get()->getRowArray();
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['page'] = $page;
        $json['data']['rows'] = $list ?? [];
        $json['data']['records'] = $total;
        $json['data']['total'] = ceil($json['data']['records'] / $rows);
        $json['data']['minCost'] = $costInfo['minPrice'] ?? 0;
        $json['data']['maxCost'] = $costInfo['maxPrice'] ?? 0;
        $json['data']['averageCost'] = $costInfo['totalQty'] ? ((float)$costInfo['tatalAmount'] / (float)$costInfo['totalQty']) : 0;
        return $this->response->setJSON($json);
    }
    // 采购排名
    public function purchaserRank()
    {
        if (!$this->myservice->verifyRight('PURCHASER_RANK')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/Report/purchaserRank 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        set_time_limit(0); // 设置脚本运行时间无限制，直到脚本运行完毕
        session_write_close(); // 主动关闭session读写保护来解除锁定，避免其他访问无法读写session造成阻塞引起假死
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 1000);
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');
        $orderuIdArr = $this->db->table('bill_order_info')->select('uId')->where(['billDate >=' => $beginDate, 'billDate <=' => $endDate, 'billType' => 'PUR', 'checkId >' => 0, 'uId >' => 1, 'isDelete' => 0])->groupBy('uId')->get()->getResultArray();
        $contractuIdArr = $this->db->table('bill_contract_info')->select('uId')->where(['billDate >=' => $beginDate, 'billDate <=' => $endDate, 'billType' => 'PUR'])->whereIn('isReq', [0, 2])->where(['uId >' => 1, 'isDelete' => 0])->groupBy('uId')->get()->getResultArray();
        $uIdArr = array_unique(array_column(array_merge($orderuIdArr, $contractuIdArr), 'uId'));
        $v = [];
        if (count($uIdArr) > 0) {
            $expPar = (array)json_decode($this->db->table('option')->select('optionValue')->where('optionName', 'exp_par')->get()->getRowArray()['optionValue'] ?? '', true);
            if (!empty($expPar)) {
                foreach ($uIdArr as $arr => $uId) {
                    if ($uId) {
                        $uName = $this->db->table('user')->select('name')->where('id', $uId)->get()->getRowArray()['name'] ?? '';
                        // 采购订单货物种类数
                        $orderNum = $this->db->table('bill_order_info')->where(['billDate >=' => $beginDate, 'billDate <=' => $endDate, 'billType' => 'PUR', 'uId' => $uId, 'checkId >' => 0, 'isDelete' => 0])->countAllResults();
                        // 特殊采购货物种类数
                        $contractNum = $this->db->table('bill_contract_info')->where(['billDate >=' => $beginDate, 'billDate <=' => $endDate, 'billType' => 'PUR', 'isReq !=' => 1, 'uId' => $uId, 'price >' => 0, 'paidAmount >' => 0, 'isDelete' => 0])->countAllResults();
                        // 新增商家数
                        $supplierNum = $this->db->table('contact')->where(['createTime >=' => $beginDate . ' 00:00:00', 'createTime <=' => $endDate . ' 23:59:59', 'uName' => $uName, 'type' => 1, 'isDisable' => 0, 'isDelete' => 0])->countAllResults();
                        // 发票录入数
                        $invoiceNum = $this->db->table('invoice')->where(['createTime >=' => $beginDate . ' 00:00:00', 'createTime <=' => $endDate . ' 23:59:59', 'uName' => $uName, 'isDelete' => 0])->countAllResults();
                        // 特殊采购毛利
                        $deAmount = $this->db->table('bill_contract_info')->select('SUM(CASE WHEN (limitPrice * 1.25 * qty) <= paidAmount THEN 0 ELSE ((limitPrice * 1.25 * qty) - paidAmount) END) AS deAmount', false)->where(['paidTime >=' => $beginDate . ' 00:00:00', 'paidTime <=' => $endDate . ' 23:59:59', 'isReq' => 2, 'uId' => $uId, 'billType' => 'PUR', 'limitPrice >' => 0, 'price >' => 0, 'paidAmount >' => 0, 'isDelete' => 0])->where('paidAmount >= amount', null, false)->get()->getRowArray()['deAmount'] ?? 0;
                        // 超报价的总笔数、总金额
                        $overPriceData = $this->db->table('bill_contract_info')->select('COUNT(*) AS overPriceNum, SUM(paidAmount - (limitPrice * 1.25 * qty)) AS overPriceAmount', false)->where(['paidTime >=' => $beginDate . ' 00:00:00', 'paidTime <=' => $endDate . ' 23:59:59', 'isReq' => 2, 'uId' => $uId, 'billType' => 'PUR', 'limitPrice >' => 0, 'price >' => 0, 'paidAmount >' => 0, 'isDelete' => 0])->where('paidAmount > (limitPrice * 1.25 * qty)', null, false)->get()->getRowArray() ?? ['overPriceNum' => 0, 'overPriceAmount' => 0];
                        // 采购业绩
                        $core = intval((float)$orderNum * (float)$expPar['pur']['par1'] + (float)$contractNum * (float)$expPar['pur']['par2'] + (float)$supplierNum * (float)$expPar['pur']['par3'] + (float)$deAmount * (float)$expPar['pur']['par4']);
                        $v[] = [
                            'uName' => $uName,
                            'dateRange' => $beginDate . ' ~ ' . $endDate,
                            'orderNum' => $orderNum,
                            'contractNum' => $contractNum,
                            'supplierNum' => $supplierNum,
                            'invoiceNum' => $invoiceNum,
                            'overPriceNum' => (string)round($overPriceData['overPriceNum'], 2),
                            'overPriceAmount' => (string)round($overPriceData['overPriceAmount'], 2),
                            'core' => $core,
                        ];
                    }
                }
            }
        }
        // （二维数组）依次按业绩指数、采购订单货物种类数、特殊采购货物种类数、新增商家数进行多维降序排序（当前面排序字段值相等时，依次以后面另一个字段继续排序，类似数据库多字段排序）
        if (count($v) > 0) {
            $sort1 = array_column($v, 'core');
            $sort2 = array_column($v, 'orderNum');
            $sort3 = array_column($v, 'contractNum');
            $sort4 = array_column($v, 'supplierNum');
            $sort5 = array_column($v, 'invoiceNum');
            array_multisort($sort1, SORT_DESC, $sort2, SORT_DESC, $sort3, SORT_DESC, $sort4, SORT_DESC, $sort5, SORT_DESC, $v);
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['rows'] = $v;
        $json['data']['page'] = $page;
        $json['data']['records'] = count($v);
        $json['data']['total'] = ceil($json['data']['records'] / $rows);
        return $this->response->setJSON($json);
    }
    // 回票排名
    public function purchaseInvoiceBack()
    {
        if (!$this->myservice->verifyRight('PURCHASER_RANK')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/Report/purchaseInvoiceBack 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        set_time_limit(0); // 设置脚本运行时间无限制，直到脚本运行完毕
        session_write_close(); // 主动关闭session读写保护来解除锁定，避免其他访问无法读写session造成阻塞引起假死
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 1000);
        $uId = intval($this->request->getGetPost('uId'));
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');
        $data = [];
        if ($uId == -1) {
            $staffs = $this->db->table('user')->select('id, name')->where('department', '采购部')->groupStart()->where('leaveDate', '0000-00-00')->orWhere('leaveDate >', date('Y-m-d', strtotime('-60 day')))->groupEnd()->whereNotIn('name', ['系统', '审查员'])->where('isDelete', 0)->get()->getResultArray();
            foreach ($staffs as $k => $v) {
                $data[$k]['uName'] = $v['name'];
                $data[$k]['dateRange'] = $beginDate . ' ~ ' . $endDate;
                $data[$k]['purPaidAmount'] = $data[$k]['purInvoiceAmount'] = 0;
                // 采购订单支出
                $paid_order = $this->db->table('bill_money_info AS a')
                    ->select('a.amount, a.srcId, b.id, b.uId')
                    ->join('bill_order AS b', 'a.srcId = b.id AND b.uId = ' . $v['id'] . ' AND b.isDelete = 0', 'left')
                    ->where('a.transType', 103102)
                    ->where('a.miniType', 2)
                    ->where('a.settlementTime >=', $beginDate . ' 00:00:00')
                    ->where('a.settlementTime <=', $endDate . ' 23:59:59')
                    ->where('a.isDelete', 0)
                    ->get()
                    ->getResultArray();
                if (count($paid_order) > 0) {
                    foreach ($paid_order as $k1 => $v1) {
                        if ($v1['uId']) {
                            $data[$k]['purPaidAmount'] += (float)$v1['amount'];
                        }
                    }
                }
                // 特殊采购支出
                $paid_contract = $this->db->table('bill_contract_info')
                    ->selectSum('paidAmount', 'totalPaidAmount')
                    ->where('transType', 101111)
                    ->where('uId', $v['id'])
                    ->where('paidTime >=', $beginDate . ' 00:00:00')
                    ->where('paidTime <=', $endDate . ' 23:59:59')
                    ->where('isDelete', 0)
                    ->get()
                    ->getRowArray()['totalPaidAmount'] ?? 0;
                $data[$k]['purPaidAmount'] += (float)$paid_contract;
                // 采购总体回票以当月收到的开票日期为准
                $data[$k]['purInvoiceAmount'] = $this->db->table('invoice')
                    ->selectSum('amount', 'totalAmount')
                    ->where('date >=', $beginDate)
                    ->where('date <=', $endDate)
                    ->where('uName', $v['name'])
                    ->where('inoutType', 1)
                    ->whereIn('transType', [101101, 101102, 101111])
                    ->where('isDelete', 0)
                    ->get()
                    ->getRowArray()['totalAmount'] ?? 0;
                $data[$k]['rate'] = (float)$data[$k]['purPaidAmount'] > 0 ? (float)$data[$k]['purInvoiceAmount'] / (float)$data[$k]['purPaidAmount'] * 100 : 0;
            }
        } else {
            $data[0]['uName'] = $this->db->table('user')->select('name')->where('id', $uId)->get()->getRowArray()['name'] ?? '';
            $data[0]['dateRange'] = $beginDate . ' ~ ' . $endDate;
            $data[0]['purPaidAmount'] = $data[0]['purInvoiceAmount'] = 0;
            // 采购订单支出
            $paid_order = $this->db->table('bill_money_info AS a')
                ->select('a.amount, a.srcId, b.id, b.uId')
                ->join('bill_order AS b', 'a.srcId = b.id AND b.uId = ' . $uId . ' AND b.isDelete = 0', 'left')
                ->where('a.transType', 103102)
                ->where('a.miniType', 2)
                ->where('a.settlementTime >=', $beginDate . ' 00:00:00')
                ->where('a.settlementTime <=', $endDate . ' 23:59:59')
                ->where('a.isDelete', 0)
                ->get()
                ->getResultArray();
            if (count($paid_order) > 0) {
                foreach ($paid_order as $k1 => $v1) {
                    if ($v1['uId']) {
                        $data[0]['purPaidAmount'] += (float)$v1['amount'];
                    }
                }
            }
            // 特殊采购支出
            $paid_contract = $this->db->table('bill_contract_info')
                ->selectSum('paidAmount', 'paidAmount')
                ->where('transType', 101111)
                ->where('uId', $uId)
                ->where('paidTime >=', $beginDate . ' 00:00:00')
                ->where('paidTime <=', $endDate . ' 23:59:59')
                ->where('isDelete', 0)
                ->get()
                ->getRowArray()['paidAmount'] ?? 0;
            $data[0]['purPaidAmount'] += floatval($paid_contract);
            // 采购总体回票以当月收到的开票日期为准
            $data[0]['purInvoiceAmount'] = $this->db->table('invoice')
                ->selectSum('amount', 'purInvoiceAmount')
                ->where('date >=', $beginDate)
                ->where('date <=', $endDate)
                ->where('uName', $data[0]['uName'])
                ->where('inoutType', 1)
                ->whereIn('transType', [101101, 101102, 101111])
                ->where('isDelete', 0)
                ->get()
                ->getRowArray()['purInvoiceAmount'] ?? 0;
            $data[0]['rate'] = (float)$data[0]['purPaidAmount'] > 0 ? (float)$data[0]['purInvoiceAmount'] / (float)$data[0]['purPaidAmount'] * 100 : 0;
        }
        // 单独计算总体数据
        $paidAmount = $this->db->table('bill_money_info')
            ->selectSum('amount', 'paidAmount')
            ->where('typeId', 7)
            ->whereIn('transType', [103102, 103104])
            ->where('settlementTime >=', $beginDate . ' 00:00:00')
            ->where('settlementTime <=', $endDate . ' 23:59:59')
            ->where('isDelete', 0)
            ->get()
            ->getRowArray()['paidAmount'] ?? 0;
        $refundAmount = $this->db->table('bill_money_info')
            ->selectSum('amount', 'refundAmount')
            ->where('typeId', 6)
            ->whereIn('transType', [103101, 103103])
            ->where('settlementTime >=', $beginDate . ' 00:00:00')
            ->where('settlementTime <=', $endDate . ' 23:59:59')
            ->where('isDelete', 0)
            ->get()
            ->getRowArray()['refundAmount'] ?? 0;
        $total_purPaidAmount = floatval($paidAmount) - floatval($refundAmount);
        $total_purInvoiceAmount = $this->db->table('invoice')
            ->selectSum('amount', 'total_purInvoiceAmount')
            ->where('date >=', $beginDate)
            ->where('date <=', $endDate)
            ->where('inoutType', 1)
            ->whereIn('transType', [101101, 101102, 101111])
            ->where('isDelete', 0)
            ->get()
            ->getRowArray()['total_purInvoiceAmount'] ?? 0;
        $total_rate = (float)$total_purPaidAmount > 0 ? (float)$total_purInvoiceAmount / (float)$total_purPaidAmount * 100 : 0;
        // （二维数组）依次按回票率、回票金额、支出金额进行多维降序排序（当前面排序字段值相等时，依次以后面另一个字段继续排序，类似数据库多字段排序）
        if (count($data) > 0) {
            $sort1 = array_column($data, 'rate');
            $sort2 = array_column($data, 'purInvoiceAmount');
            $sort3 = array_column($data, 'purPaidAmount');
            array_multisort($sort1, SORT_DESC, $sort2, SORT_DESC, $sort3, SORT_DESC, $data);
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['page'] = $page;
        $json['data']['records'] = count($data);
        $json['data']['total'] = ceil($json['data']['records'] / $rows);
        $json['data']['rows'] = $data;
        $json['data']['total_purPaidAmount'] = round($total_purPaidAmount, 2);
        $json['data']['total_purInvoiceAmount'] = round($total_purInvoiceAmount, 2);
        $json['data']['total_rate'] = round($total_rate, 2);
        return $this->response->setJSON($json);
    }
    // 特采历史
    public function purchaseContractHis()
    {
        if (!$this->myservice->verifyRight('PC_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/Report/purchaseContractHis 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        set_time_limit(0); // 设置脚本运行时间无限制，直到脚本运行完毕
        session_write_close(); // 主动关闭session读写保护来解除锁定，避免其他访问无法读写session造成阻塞引起假死
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $uId = intval($this->request->getGetPost('uId'));
        $skey = $this->request->getGetPost('skey');
        $builder = $this->db->table('bill_contract_info');
        $builder->where('billType', 'PUR')->where('paidAmount >', 0)->where('uId >', 0)->whereIn('isReq', [0, 2]);
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
                ->groupEnd();
            }
        }
        $builder->where('isDelete', 0);
        $allowedSort = ['id','price'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $list = (clone $builder)->orderBy($orderField, $sord)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['billDate'] = $row['billDate'];
            $v[$arr]['name'] = $row['name'];
            $v[$arr]['spec'] = $row['spec'];
            $v[$arr]['sku'] = $row['sku'];
            $v[$arr]['brand'] = $row['brand'];
            $v[$arr]['unit'] = $row['unit'];
            $v[$arr]['price'] = (float)$row['price'];
            $v[$arr]['buyUrl'] = $row['buyUrl'];
            $v[$arr]['myUrl'] = $row['myUrl'];
            $v[$arr]['description1'] = $row['description1'];
            $v[$arr]['description2'] = $row['description2'];
            $v[$arr]['isHasExpress'] = intval($row['isHasExpress']);
            $v[$arr]['isHasInvoice'] = intval($row['isHasInvoice']);
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['page'] = $page;
        $json['data']['records'] = $total;
        $json['data']['total'] = ceil($json['data']['records'] / $rows);
        $json['data']['rows'] = $v;
        return $this->response->setJSON($json);
    }
    // 导出特采历史用于危化品筛查(2020-01-01至今)
    public function exportHisForCheck() {
        set_time_limit(0); // 设置脚本运行时间无限制，直到脚本运行完毕
        session_write_close(); // 主动关闭session读写保护来解除锁定，避免其他访问无法读写session造成阻塞引起假死
        $list = $this->db->table('bill_contract_info')
            ->select('name, spec, sku')
            ->where('billType', 'PUR')
            ->where('paidAmount >', 0)
            ->where('uId >', 0)
            ->whereIn('isReq', [0,2])
            ->where('billDate >=', '2020-01-01')
            ->where('isDelete', 0)
            ->orderBy('id', 'DESC')
            ->get()
            ->getResultArray();

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
        $activeWorksheet->getStyle('A1:C' . (COUNT($list) + 2))->applyFromArray($styleArray, false);

        // 合并单元格
        $activeWorksheet->mergeCells('A1:C1');

        // 设置水平居中
        $activeWorksheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // 设置行高
        $activeWorksheet->getRowDimension('1')->setRowHeight(40);

        // 设置加粗
        $activeWorksheet->getStyle('A1')->getFont()->setBold(true);
        $activeWorksheet->getStyle('A2:C2')->getFont()->setBold(true);

        // 设置背景色
        $activeWorksheet->getStyle('A2:C2')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        $activeWorksheet->getStyle('A2:C2')->getFill()->getStartColor()->setRGB('F5F5F5');

        // 设置每列列宽
        $activeWorksheet->getColumnDimension('A')->setWidth(40);
        $activeWorksheet->getColumnDimension('B')->setWidth(40);
        $activeWorksheet->getColumnDimension('C')->setWidth(40);
        //设置当前的sheet
        $activeWorksheet->setCellValue('A1', '2025-01-01至今特采历史数据');
        $activeWorksheet->setCellValue('A2', '产品名称');
        $activeWorksheet->setCellValue('B2', '规格型号');
        $activeWorksheet->setCellValue('C2', '辅助属性');

        $i = 3;
        foreach ($list as $k => $v) {
            $activeWorksheet->setCellValue('A' . $i, $v['name']);
            $activeWorksheet->setCellValue('B' . $i, $v['spec']);
            $activeWorksheet->setCellValue('C' . $i, $v['sku']);
            $i++;
        }

        $outputFileName = '2020-01-01至今特采历史数据.xlsx';
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
    // 采购趋势图
    public function purchaseTrend()
    {
        if (!$this->myservice->verifyRight('SYS_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/Report/purchaseTrend 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        set_time_limit(0); // 设置脚本运行时间无限制，直到脚本运行完毕
        session_write_close(); // 主动关闭session读写保护来解除锁定，避免其他访问无法读写session造成阻塞引起假死
        $type = $this->request->getGetPost('type');
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');
        switch ($type) {
            case 'near30days':
                $sql = 'SELECT date AS a, SUM(amount) AS b FROM (SELECT billDate AS date, amount FROM bill_order WHERE billDate >= "' . date('Y-m-d', strtotime(date('Y-m-d') . " -30 day")) . '" AND billDate <= "' . date('Y-m-d') . '" AND billType = "PUR" AND isCancel = 0 AND isDelete = 0 UNION ALL SELECT billDate AS date, amount FROM bill_contract WHERE billDate >= "' . date('Y-m-d', strtotime(date('Y-m-d') . " -30 day")) . '" AND billDate <= "' . date('Y-m-d') . '" AND billType = "PUR" AND isReq IN(0,2) AND isDelete = 0) AS c GROUP BY a ORDER BY a ASC';
                break;
            case 'lastmonth':
                $sql = 'SELECT date AS a, SUM(amount) AS b FROM (SELECT CONCAT(DATE_FORMAT(billDate,"%d"),"日") AS date, amount FROM bill_order WHERE billDate >= "' . date('Y-m-01', strtotime(date('Y-m-01') . " -1 month")) . '" AND billDate < "' . date('Y-m-01') . '" AND billType = "PUR" AND isCancel = 0 AND isDelete = 0 UNION ALL SELECT CONCAT(DATE_FORMAT(billDate,"%d"),"日") AS date, amount FROM bill_contract WHERE billDate >= "' . date('Y-m-01', strtotime(date('Y-m-01') . " -1 month")) . '" AND billDate < "' . date('Y-m-01') . '" AND billType = "PUR" AND isReq IN(0,2) AND isDelete = 0) AS c GROUP BY a ORDER BY a ASC';
                break;
            case 'near12months':
                $sql = 'SELECT date AS a, SUM(amount1) AS b, SUM(amount2) AS b2 FROM (SELECT DATE_FORMAT(billDate,"%Y-%m") AS date, amount AS amount1, 0 AS amount2 FROM bill_order WHERE billDate >= "' . date('Y-m-01', strtotime(date('Y-m-01') . " -11 month")) . '" AND billDate <= "' . date('Y-m-d') . '" AND billType = "PUR" AND isCancel = 0 AND isDelete = 0 UNION ALL SELECT DATE_FORMAT(billDate,"%Y-%m") AS date, amount AS amount1, 0 AS amount2 FROM bill_contract WHERE billDate >= "' . date('Y-m-01', strtotime(date('Y-m-01') . " -11 month")) . '" AND billDate <= "' . date('Y-m-d') . '" AND billType = "PUR" AND isReq IN(0,2) AND isDelete = 0 UNION ALL SELECT DATE_FORMAT(DATE_ADD(billDate, INTERVAL 1 YEAR),"%Y-%m") AS date, 0 AS amount1, amount AS amount2 FROM bill_order WHERE billDate >= "' . date('Y-m-01', strtotime(date('Y-m-01') . " -23 month")) . '" AND billDate < "' . date('Y-m-01', strtotime(date('Y-m-01') . " -11 month")) . '" AND billType = "PUR" AND isCancel = 0 AND isDelete = 0 UNION ALL SELECT DATE_FORMAT(DATE_ADD(billDate, INTERVAL 1 YEAR),"%Y-%m") AS date, 0 AS amount1, amount AS amount2 FROM bill_contract WHERE billDate >= "' . date('Y-m-01', strtotime(date('Y-m-01') . " -23 month")) . '" AND billDate < "' . date('Y-m-01', strtotime(date('Y-m-01') . " -11 month")) . '" AND billType = "PUR" AND isReq IN(0,2) AND isDelete = 0) AS c GROUP BY a ORDER BY a ASC';
                break;
            case 'all':
                $sql = 'SELECT date AS a, SUM(amount) AS b FROM (SELECT DATE_FORMAT(billDate,"%Y") AS date, amount FROM bill_order WHERE billType = "PUR" AND isCancel = 0 AND isDelete = 0 UNION ALL SELECT DATE_FORMAT(billDate,"%Y") AS date, amount FROM bill_contract WHERE billType = "PUR" AND isReq IN(0,2) AND isDelete = 0) AS c GROUP BY a ORDER BY a ASC';
                break;
            case 'custom':
                if (((strtotime(date($endDate)) - strtotime(date($beginDate))) / 86400) > 100) {
                    $dateFomat = '"%Y-%m"';
                } else {
                    $dateFomat = '"%Y-%m-%d"';
                }
                $sql = 'SELECT date AS a, SUM(amount) AS b FROM (SELECT DATE_FORMAT(billDate,' . $dateFomat . ') AS date, amount FROM bill_order WHERE billDate >= "' . $beginDate . '" AND billDate <= "' . $endDate . '" AND billType = "PUR" AND isCancel = 0 AND isDelete = 0 UNION ALL SELECT DATE_FORMAT(billDate,' . $dateFomat . ') AS date, amount FROM bill_contract WHERE billDate >= "' . $beginDate . '" AND billDate <= "' . $endDate . '" AND billType = "PUR" AND isReq IN(0,2) AND isDelete = 0) AS c GROUP BY a ORDER BY a ASC';
                break;
            default:
                $sql = '';
                break;
        }
        $v = $this->db->query($sql)->getResultArray();
        foreach ($v as $arr => $row) {
            $v[$arr]['b'] = round((float)$v[$arr]['b'], 2);
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data'] = $v;
        return $this->response->setJSON($json);
    }

    /**仓储相关 */
    // 打包排名
    public function packerRank()
    {
        if (!$this->myservice->verifyRight('PACKER_RANK')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/Report/packerRank 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        set_time_limit(0); // 设置脚本运行时间无限制，直到脚本运行完毕
        session_write_close(); // 主动关闭session读写保护来解除锁定，避免其他访问无法读写session造成阻塞引起假死
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 1000);
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');

        $userArr = $this->db->table('user')->select('id, name')->where('department', '仓储部')->where('joinDate <=', $endDate)->groupStart()->where('leaveDate', '00-00-00')->orWhere('leaveDate >=', $beginDate)->groupEnd()->get()->getResultArray();
        $v = [];
        if (count($userArr) > 0) {
            $expPar = (array)json_decode($this->db->table('option')->select('optionValue')->where('optionName', 'exp_par')->get()->getRowArray()['optionValue'] ?? '', true);
            if (!empty($expPar)) {
                foreach ($userArr as $arr => $user) {
                    $performancePack = $this->db->table('deliver_bag')->select('COUNT(CASE WHEN weight < 20 THEN no ELSE null END) totalCount1, SUM(CASE WHEN weight < 20 THEN weight ELSE 0 END) totalWeight1, COUNT(CASE company WHEN "申通快递" THEN no ELSE null END) totalZYCount, SUM(CASE company WHEN "申通快递" THEN amount ELSE 0 END) totalZYAmount, COUNT(CASE WHEN weight >= 20 THEN no ELSE null END) totalCount2, SUM(CASE WHEN weight >= 20 THEN weight ELSE 0 END) totalWeight2', false)->where('scanTime >=', $beginDate . ' 00:00:00')->where('scanTime <=', $endDate . ' 23:59:59')->where('scanName', $user['name'])->where('isDelete', 0)->get()->getRowArray() ?? ['tatalCount1' => 0, 'totalWeight1' => 0, 'totalZYCount' => 0, 'totalZYAmount' => 0, 'tatalCount2' => 0, 'totalWeight2' => 0];
                    $performancePack['core'] = intval((float)$performancePack['totalCount1'] * (float)$expPar['pack']['par1'] + (float)$performancePack['totalWeight1'] * (float)$expPar['pack']['par2'] + ((float)$expPar['pack']['par3'] * (float)$performancePack['totalZYCount'] - (float)$performancePack['totalZYAmount']) * (float)$expPar['pack']['par4'] + (float)$performancePack['totalCount2'] * (float)$expPar['pack']['par5'] + (float)$performancePack['totalWeight2'] * (float)$expPar['pack']['par6']);
                    $v[] = [
                        'uName' => $user['name'],
                        'dateRange' => $beginDate . ' ~ ' . $endDate,
                        'totalCount' => intval($performancePack['totalCount1']) + intval($performancePack['totalCount2']),
                        'totalWeight' => intval($performancePack['totalWeight1']) + intval($performancePack['totalWeight2']),
                        'totalCount1' => intval($performancePack['totalCount1']),
                        'totalWeight1' => intval($performancePack['totalWeight1']),
                        'totalCount2' => intval($performancePack['totalCount2']),
                        'totalWeight2' => intval($performancePack['totalWeight2']),
                        'core' => $performancePack['core'],
                    ];
                }
            }
        }
        // （二维数组）依次按业绩指数、采购订单货物种类数、特殊采购货物种类数、新增商家数进行多维降序排序（当前面排序字段值相等时，依次以后面另一个字段继续排序，类似数据库多字段排序）
        if (count($v) > 0) {
            $sort1 = array_column($v, 'core');
            $sort2 = array_column($v, 'totalCount');
            $sort3 = array_column($v, 'totalWeight');
            $sort4 = array_column($v, 'totalCount1');
            $sort5 = array_column($v, 'totalWeight1');
            $sort6 = array_column($v, 'totalCount2');
            $sort7 = array_column($v, 'totalWeight2');
            array_multisort($sort1, SORT_DESC, $sort2, SORT_DESC, $sort3, SORT_DESC, $sort4, SORT_DESC, $sort5, SORT_DESC, $sort6, SORT_DESC, $sort7, SORT_DESC, $v);
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['page'] = $page;
        $json['data']['records'] = count($v);
        $json['data']['total'] = ceil($json['data']['records'] / $rows);
        $json['data']['rows'] = $v;
        return $this->response->setJSON($json);
    }
    // 发包分析
    public function deliverAnalyse()
    {
        if (!$this->myservice->verifyRight('DELIVER_ANALYSE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/Report/deliverAnalyse 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        set_time_limit(0); // 设置脚本运行时间无限制，直到脚本运行完毕
        session_write_close(); // 主动关闭session读写保护来解除锁定，避免其他访问无法读写session造成阻塞引起假死
        $deliverCompany = $this->request->getGetPost('deliverCompany');
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');
        $builder = $this->db->table('deliver_bag');
        $builder->where('scanTime >=', $beginDate)->where('scanTime <=', $endDate)->where('isDelete', 0);
        if (strlen($deliverCompany) > 0) {
            $builder->whereIn('company', explode(',', $deliverCompany));
        }
        $list = (clone $builder)->select('company,address,chType,weight,scanName')->get()->getResultArray();
        $totalCount = (clone $builder)->countAllResults();
        if ($totalCount > 0) {
            $addressDataArr = [
                '北京' => ['c' => 0, 'p' => 0],
                '天津' => ['c' => 0, 'p' => 0],
                '河北' => ['c' => 0, 'p' => 0],
                '山西' => ['c' => 0, 'p' => 0],
                '内蒙' => ['c' => 0, 'p' => 0],
                '辽宁' => ['c' => 0, 'p' => 0],
                '吉林' => ['c' => 0, 'p' => 0],
                '黑龙' => ['c' => 0, 'p' => 0],
                '上海' => ['c' => 0, 'p' => 0],
                '江苏' => ['c' => 0, 'p' => 0],
                '浙江' => ['c' => 0, 'p' => 0],
                '安徽' => ['c' => 0, 'p' => 0],
                '福建' => ['c' => 0, 'p' => 0],
                '江西' => ['c' => 0, 'p' => 0],
                '山东' => ['c' => 0, 'p' => 0],
                '河南' => ['c' => 0, 'p' => 0],
                '湖北' => ['c' => 0, 'p' => 0],
                '湖南' => ['c' => 0, 'p' => 0],
                '广东' => ['c' => 0, 'p' => 0],
                '广西' => ['c' => 0, 'p' => 0],
                '海南' => ['c' => 0, 'p' => 0],
                '重庆' => ['c' => 0, 'p' => 0],
                '四川' => ['c' => 0, 'p' => 0],
                '贵州' => ['c' => 0, 'p' => 0],
                '云南' => ['c' => 0, 'p' => 0],
                '西藏' => ['c' => 0, 'p' => 0],
                '陕西' => ['c' => 0, 'p' => 0],
                '甘肃' => ['c' => 0, 'p' => 0],
                '青海' => ['c' => 0, 'p' => 0],
                '宁夏' => ['c' => 0, 'p' => 0],
                '新疆' => ['c' => 0, 'p' => 0],
                '台湾' => ['c' => 0, 'p' => 0],
                '香港' => ['c' => 0, 'p' => 0],
                '澳门' => ['c' => 0, 'p' => 0],
                '海外' => ['c' => 0, 'p' => 0]
            ];
            $companyDataArr = [
                '邮政快递' => ['c' => 0],
                '申通快递' => ['c' => 0],
                '顺丰速运' => ['c' => 0],
                '百世快运' => ['c' => 0]
            ];
            $chTypeArr = array_column($this->db->table('deliver_bag')->select('chType')->where('chType !=', '')->where('isDelete', 0)->orderBy('chType', 'DESC')->groupBy('chType')->get()->getResultArray(), 'chType');
            $chTypeDataArr = [];
            foreach ($chTypeArr as $arr => $chType) {
                $chTypeDataArr[$chType]['n'] = $chType;
                $chTypeDataArr[$chType]['c'] = 0;
            }
            $weightDataArr = [
                '0~5 kg' => ['c' => 0],
                '5~20 kg' => ['c' => 0],
                '20~50 kg' => ['c' => 0],
                '50+ kg' => ['c' => 0]
            ];
            $packerArr = array_column($this->db->table('user')->select('name')->where('department', '仓储部')->where('joinDate <=', $endDate)->groupStart()->where('leaveDate', '00-00-00')->orWhere('leaveDate >=', $beginDate)->groupEnd()->get()->getResultArray(), 'name');
            $packerDataArr = [];
            foreach ($packerArr as $arr => $packer) {
                $packerDataArr[$packer]['totalCount'] = 0; // 总个数
                $packerDataArr[$packer]['totalWeight'] = 0; // 总重量
                $packerDataArr[$packer]['totalCount1'] = 0; // 小件个数
                $packerDataArr[$packer]['totalWeight1'] = 0; // 小件重量
                $packerDataArr[$packer]['totalCount2'] = 0; // 大件个数
                $packerDataArr[$packer]['totalWeight2'] = 0; // 大件重量
                $packerDataArr[$packer]['totalCount3'] = 0; // 快递个数
                $packerDataArr[$packer]['totalWeight3'] = 0; // 快递重量
                $packerDataArr[$packer]['totalCount4'] = 0; // 物流个数
                $packerDataArr[$packer]['totalWeight4'] = 0; // 物流重量
            }
            foreach ($list as $i => $v) {
                // 处理按快递公司统计数据
                if (isset($companyDataArr[$v['company']])) {
                    $companyDataArr[$v['company']]['c'] += 1;
                }
                // 处理按来源渠道统计数据
                if ($v['chType'] != "" && isset($chTypeDataArr[$v['chType']])) {
                    $chTypeDataArr[$v['chType']]['c'] += 1;
                }
                // 处理按重量区间统计数据
                if ($v['weight'] < 5) {
                    $weightDataArr['0~5 kg']['c'] += 1;
                } elseif ($v['weight'] >= 5 && $v['weight'] < 20) {
                    $weightDataArr['5~20 kg']['c'] += 1;
                } elseif ($v['weight'] >= 20 && $v['weight'] < 50) {
                    $weightDataArr['20~50 kg']['c'] += 1;
                } elseif ($v['weight'] >= 50) {
                    $weightDataArr['50+ kg']['c'] += 1;
                }
                // 处理按打包人统计数据
                if (isset($packerDataArr[$v['scanName']])) {
                    $packerDataArr[$v['scanName']]['totalCount'] += 1;
                    if ($v['weight'] < 20) {
                        $packerDataArr[$v['scanName']]['totalCount1'] += 1;
                        $packerDataArr[$v['scanName']]['totalWeight1'] += (float)$v['weight'];
                    } else {
                        $packerDataArr[$v['scanName']]['totalCount2'] += 1;
                        $packerDataArr[$v['scanName']]['totalWeight2'] += (float)$v['weight'];
                    }
                    if ($v['company'] == '百世快运') {
                        $packerDataArr[$v['scanName']]['totalCount4'] += 1;
                        $packerDataArr[$v['scanName']]['totalWeight4'] += (float)$v['weight'];
                    } else {
                        $packerDataArr[$v['scanName']]['totalCount3'] += 1;
                        $packerDataArr[$v['scanName']]['totalWeight3'] += (float)$v['weight'];
                    }
                }
                // 处理按省份统计数据
                if (isset($addressDataArr[$v['address']])) {
                    $addressDataArr[$v['address']]['c'] += 1;
                }
            }
            // 处理按来源渠道统计数据中的纯数字键名，以免array_multisort之后排序错乱。
            $addressDataArr_new = [];
            foreach ($chTypeDataArr as $i1 => $v1) {
                array_push($addressDataArr_new, $v1);
            }
            // 处理按省份统计数据中的占比值
            foreach ($addressDataArr as $i2 => $v2) {
                $addressDataArr[$i2]['p'] = $v2['c'] / $totalCount * 100;
            }

            // 二维数组排序（以纯数字作为键名在array_multisort排序后，键名将重新从0开始赋值）。
            // 例如：chTypeDateArr中的['1688' => ['n' => '1688', 'c' => 0]]在排序后变成[0 => ['n' => '1688', 'c' => 0]]，所以在该键值数组中事先存入'n' => '1688'供后续能引用。
            $addressDataArr_sort1 = array_column($addressDataArr, 'c');
            array_multisort($addressDataArr_sort1, SORT_DESC, $addressDataArr);
            $companyDataArr_sort1 = array_column($companyDataArr, 'c');
            array_multisort($companyDataArr_sort1, SORT_DESC, $companyDataArr);
            $chTypeDataArr_sort1 = array_column($addressDataArr_new, 'c');
            array_multisort($chTypeDataArr_sort1, SORT_DESC, $addressDataArr_new);
            $weightDataArr_sort1 = array_column($weightDataArr, 'c');
            array_multisort($weightDataArr_sort1, SORT_DESC, $weightDataArr);
            $packerDataArr_sort1 = array_column($packerDataArr, 'totalCount');
            array_multisort($packerDataArr_sort1, SORT_DESC, $packerDataArr);

            $json['status'] = 200;
            $json['msg'] = 'success';
            $json['data']['addressDataArr'] = $addressDataArr;
            $json['data']['companyDataArr'] = $companyDataArr;
            $json['data']['chTypeDataArr'] = $addressDataArr_new;
            $json['data']['weightDataArr'] = $weightDataArr;
            $json['data']['packerDataArr'] = $packerDataArr;
            return $this->response->setJSON($json);
        } else {
            $json['status'] = -1;
            $json['msg'] = '无包裹数据！';
            return $this->response->setJSON($json);
        }
    }
    // 发包趋势图
    public function deliverTrend()
    {
        if (!$this->myservice->verifyRight('SYS_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/Report/deliverTrend 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        set_time_limit(0); // 设置脚本运行时间无限制，直到脚本运行完毕
        session_write_close(); // 主动关闭session读写保护来解除锁定，避免其他访问无法读写session造成阻塞引起假死
        $type = $this->request->getGetPost('type');
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');
        switch ($type) {
            case 'near30days':
                $sql = 'SELECT date AS a, SUM(num) AS b FROM (SELECT DATE_FORMAT(scanTime, "%Y-%m-%d") AS date, 1 AS num FROM deliver_bag WHERE company != "" AND scanTime >= "' . date('Y-m-d', strtotime(date('Y-m-d') . " -30 day")) . ' 00:00:00" AND scanTime <= "' . date('Y-m-d') . ' 23:59:59" AND isDelete = 0) AS c GROUP BY a ORDER BY a ASC';
                break;
            case 'lastmonth':
                $sql = 'SELECT date AS a, SUM(num) AS b FROM (SELECT DATE_FORMAT(scanTime, "%Y-%m-%d") AS date, 1 AS num FROM deliver_bag WHERE company != "" AND scanTime >= "' . date('Y-m-01', strtotime(date('Y-m-01') . " -1 month")) . ' 00:00:00" AND scanTime < "' . date('Y-m-01') . ' 00:00:00" AND isDelete = 0) AS c GROUP BY a ORDER BY a ASC';
                break;
            case 'near12months':
                $sql = 'SELECT date AS a, SUM(num1) AS b, SUM(num2) AS b2 FROM (SELECT DATE_FORMAT(scanTime, "%Y-%m") AS date, 1 AS num1, 0 AS num2 FROM deliver_bag WHERE company != "" AND scanTime >= "' . date('Y-m-01', strtotime(date('Y-m-01') . " -11 month")) . ' 00:00:00" AND scanTime <= "' . date('Y-m-d') . ' 23:59:59" AND isDelete = 0 UNION ALL SELECT DATE_FORMAT(DATE_ADD(scanTime, INTERVAL 1 YEAR), "%Y-%m") AS date, 0 AS num1, 1 AS num2 FROM deliver_bag WHERE company != "" AND scanTime >= "' . date('Y-m-01', strtotime(date('Y-m-01') . " -23 month")) . ' 00:00:00" AND scanTime < "' . date('Y-m-01', strtotime(date('Y-m-01') . " -11 month")) . ' 00:00:00" AND isDelete = 0) AS c GROUP BY a ORDER BY a ASC';
                break;
            case 'all':
                $sql = 'SELECT date AS a, SUM(num) AS b FROM (SELECT DATE_FORMAT(scanTime, "%Y") AS date, 1 AS num FROM deliver_bag WHERE company != "" AND scanTime > "0000-00-00 00:00:00" AND isDelete = 0) AS c GROUP BY a ORDER BY a ASC';
                break;
            case 'custom':
                if (((strtotime(date($endDate)) - strtotime(date($beginDate))) / 86400) > 100) {
                    $dateFomat = '"%Y-%m"';
                } else {
                    $dateFomat = '"%Y-%m-%d"';
                }
                $sql = 'SELECT date AS a, SUM(num) AS b FROM (SELECT DATE_FORMAT(scanTime,' . $dateFomat . ') AS date, 1 AS num FROM deliver_bag WHERE company != "" AND scanTime >= "' . $beginDate . ' 00:00:00" AND scanTime <= "' . $endDate . ' 23:59:59" AND isDelete = 0) AS c GROUP BY a ORDER BY a ASC';
                break;
            default:
                $sql = '';
                break;
        }
        $v = $this->db->query($sql)->getResultArray();
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data'] = $v;
        return $this->response->setJSON($json);
    }
    // 发包分配图
    public function deliverDistribution()
    {
        if (!$this->myservice->verifyRight('SYS_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/Report/deliverDistribution 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        set_time_limit(0); // 设置脚本运行时间无限制，直到脚本运行完毕
        session_write_close(); // 主动关闭session读写保护来解除锁定，避免其他访问无法读写session造成阻塞引起假死
        $type = $this->request->getGetPost('type');
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');
        switch ($type) {
            case 'near30days':
                $sql = 'SELECT company AS a, SUM(num) AS b FROM (SELECT company, 1 AS num FROM deliver_bag WHERE company != "" AND scanTime >= "' . date('Y-m-d', strtotime(date('Y-m-d') . " -30 day")) . ' 00:00:00" AND scanTime <= "' . date('Y-m-d') . ' 23:59:59" AND isDelete = 0) AS c GROUP BY a ORDER BY a ASC';
                break;
            case 'lastmonth':
                $sql = 'SELECT company AS a, SUM(num) AS b FROM (SELECT company, 1 AS num FROM deliver_bag WHERE company != "" AND scanTime >= "' . date('Y-m-01', strtotime(date('Y-m-01') . " -1 month")) . ' 00:00:00" AND scanTime < "' . date('Y-m-01') . ' 00:00:00" AND isDelete = 0) AS c GROUP BY a ORDER BY a ASC';
                break;
            case 'near12months':
                $sql = 'SELECT company AS a, SUM(num) AS b FROM (SELECT company, 1 AS num FROM deliver_bag WHERE company != "" AND scanTime >= "' . date('Y-m-01', strtotime(date('Y-m-01') . " -11 month")) . ' 00:00:00" AND scanTime < "' . date('Y-m-d') . ' 23:59:59" AND isDelete = 0) AS c GROUP BY a ORDER BY a ASC';
                break;
            case 'all':
                $sql = 'SELECT company AS a, SUM(num) AS b FROM (SELECT company, 1 AS num FROM deliver_bag WHERE company != "" AND scanTime > "0000-00-00 00:00:00" AND isDelete = 0) AS c GROUP BY a ORDER BY a ASC';
                break;
            case 'custom':
                $sql = 'SELECT company AS a, SUM(num) AS b FROM (SELECT company, 1 AS num FROM deliver_bag WHERE company != "" AND scanTime >= "' . $beginDate . ' 00:00:00" AND scanTime <= "' . $endDate . ' 23:59:59" AND isDelete = 0) AS c GROUP BY a ORDER BY a ASC';
                break;
            default:
                $sql = '';
                break;
        }
        $v = $this->db->query($sql)->getResultArray();
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data'] = $v;
        return $this->response->setJSON($json);
    }
    // 运费趋势图
    public function deliverFeeTrend()
    {
        if (!$this->myservice->verifyRight('SYS_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/Report/deliverTrend 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        set_time_limit(0); // 设置脚本运行时间无限制，直到脚本运行完毕
        session_write_close(); // 主动关闭session读写保护来解除锁定，避免其他访问无法读写session造成阻塞引起假死
        $type = $this->request->getGetPost('type');
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');
        $company = [];
        $sql = '';
        $v = [];
        switch ($type) {
            case 'near12months':
                $company = $this->db->table('bill_deliver')->select('company, SUM(amount) AS amount')->where(['company !=' => '', 'deliverDate >=' => date('Y-m', strtotime(date('Y-m-01') . ' -11 month')), 'deliverDate <=' => date('Y-m')])->groupBy('company')->orderBy('amount', 'DESC')->get()->getResultArray();
                $sqlStrA = $sqlStrB = $sqlStrC = $sqlStrD = '';
                for ($i = 0; $i < count($company); $i++) {
                    $sqlStrA .= ', SUM(n' . $i . ') AS ' . $company[$i]['company'];
                    $sqlStrB .= ', 0 AS n' . $i;
                    $sqlStrC = '';
                    for ($j = 0; $j < count($company); $j++) {
                        if ($i == $j) {
                            $sqlStrC .= ', amount AS n' . $j;
                        } else {
                            $sqlStrC .= ', 0 AS n' . $j;
                        }
                    }
                    $sqlStrD .= ' UNION ALL SELECT deliverDate AS date, 0 AS n' . $sqlStrC . ' FROM bill_deliver WHERE company = "' . $company[$i]['company'] . '" AND deliverDate >= "' . date('Y-m', strtotime(date('Y-m-01') . " -11 month")) . '" AND deliverDate <= "' . date('Y-m') . '" AND isDelete = 0';
                }
                $sql = 'SELECT date, SUM(n) AS "总计"' . $sqlStrA . ' FROM ('
                    . 'SELECT deliverDate AS date, amount AS n' . $sqlStrB . ' FROM bill_deliver WHERE deliverDate >= "' . date('Y-m', strtotime(date('Y-m-01') . " -11 month")) . '" AND deliverDate <= "' . date('Y-m') . '" AND isDelete = 0'
                    . $sqlStrD
                    . ') AS z GROUP BY date ORDER BY date ASC';
                break;
            case 'all':
                $company = $this->db->table('bill_deliver')->select('company, SUM(amount) AS amount')->where(['company !=' => '', 'deliverDate !=' => ''])->groupBy('company')->orderBy('amount', 'DESC')->get()->getResultArray();
                $sqlStrA = $sqlStrB = $sqlStrC = $sqlStrD = '';
                for ($i = 0; $i < count($company); $i++) {
                    $sqlStrA .= ', SUM(n' . $i . ') AS ' . $company[$i]['company'];
                    $sqlStrB .= ', 0 AS n' . $i;
                    $sqlStrC = '';
                    for ($j = 0; $j < count($company); $j++) {
                        if ($i == $j) {
                            $sqlStrC .= ', amount AS n' . $j;
                        } else {
                            $sqlStrC .= ', 0 AS n' . $j;
                        }
                    }
                    $sqlStrD .= ' UNION ALL SELECT DATE_FORMAT(CONCAT(deliverDate,"-01"),"%Y") AS date, 0 AS n' . $sqlStrC . ' FROM bill_deliver WHERE company = "' . $company[$i]['company'] . '" AND deliverDate != "" AND isDelete = 0';
                }
                $sql = 'SELECT date, SUM(n) AS "总计"' . $sqlStrA . ' FROM ('
                    . 'SELECT DATE_FORMAT(CONCAT(deliverDate,"-01"),"%Y") AS date, amount AS n' . $sqlStrB . ' FROM bill_deliver WHERE deliverDate != "" AND isDelete = 0'
                    . $sqlStrD
                    . ') AS z GROUP BY date ORDER BY date asc';
                break;
            case 'custom':
                $company = $this->db->table('bill_deliver')->select('company, SUM(amount) AS amount')->where(['company !=' => '', 'deliverDate >=' => date("Y-m", strtotime($beginDate)), 'deliverDate <=' => date("Y-m", strtotime($endDate))])->groupBy('company')->orderBy('amount', 'DESC')->get()->getResultArray();
                $sqlStrA = $sqlStrB = $sqlStrC = $sqlStrD = '';
                for ($i = 0; $i < count($company); $i++) {
                    $sqlStrA .= ', SUM(n' . $i . ') AS ' . $company[$i]['company'];
                    $sqlStrB .= ', 0 AS n' . $i;
                    $sqlStrC = '';
                    for ($j = 0; $j < count($company); $j++) {
                        if ($i == $j) {
                            $sqlStrC .= ', amount AS n' . $j;
                        } else {
                            $sqlStrC .= ', 0 AS n' . $j;
                        }
                    }
                    $sqlStrD .= ' UNION ALL SELECT deliverDate AS date, 0 AS n' . $sqlStrC . ' FROM bill_deliver WHERE company = "' . $company[$i]['company'] . '" AND deliverDate >= "' . date("Y-m", strtotime($beginDate)) . '" AND deliverDate <= "' . date("Y-m", strtotime($endDate)) . '" AND isDelete = 0';
                }
                $sql = 'SELECT date, SUM(n) AS "总计"' . $sqlStrA . ' FROM ('
                    . 'SELECT deliverDate AS date, amount AS n' . $sqlStrB . ' FROM bill_deliver WHERE deliverDate >= "' . date("Y-m", strtotime($beginDate)) . '" AND deliverDate <= "' . date("Y-m", strtotime($endDate)) . '" AND isDelete = 0'
                    . $sqlStrD
                    . ') AS z GROUP BY date ORDER BY date asc';
                break;
            default:
                break;
        }
        if ($sql) {
            $v = $this->db->query($sql)->getResultArray();
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['company'] = $company;
        $json['data'] = $v;
        return $this->response->setJSON($json);
    }

    /**人事相关 */
    // 出勤排名
    public function attendanceRank()
    {
        if (!$this->myservice->verifyRight('ATTENDANCE_RANK')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/Report/attendanceRank 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        set_time_limit(0); // 设置脚本运行时间无限制，直到脚本运行完毕
        session_write_close(); // 主动关闭session读写保护来解除锁定，避免其他访问无法读写session造成阻塞引起假死
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 1000);
        $company = $this->request->getGetPost('company');
        $department = $this->request->getGetPost('department');
        $position = $this->request->getGetPost('position');
        $isDisable = $this->request->getGetPost('isDisable') ?? -1;
        $builderUser = $this->db->table('user');
        $builderUser->where('isDelete', 0)->whereNotIn('name', ['系统', '审查员']);
        if ($company) {
            $builderUser->where('company', $company);
        }
        if ($department) {
            $builderUser->where('department', $department);
        }
        if ($position) {
            $builderUser->where('position', $position);
        }
        if (intval($isDisable) >= 0) {
            $builderUser->where('isDisable', intval($isDisable));
        };
        $userList = array_column($builderUser->select('name')->get()->getResultArray(), 'name');
        $list = [];
        if (count($userList) > 0) {
            $beginDate = $this->request->getGetPost('beginDate');
            $endDate = $this->request->getGetPost('endDate');
            $builder = $this->db->table('bill_attendance_info');
            $builder->where('isDelete', 0)->whereIn('uName', $userList);
            if ($beginDate) {
                $builder->where('beginDate >=', $beginDate);
            }
            if ($endDate) {
                $builder->where('endDate <=', $endDate);
            }
            $list = $builder->select(['uName', 'SUM(mustDayNum) AS mustDayNum', 'SUM(workHours) AS workHours', 'SUM(workDays) AS workDays', 'SUM(lateTimes + leaveearlyTimes) AS llTimes', 'SUM(dutyTimes) AS dutyTimes', 'SUM(isFull) AS isFull', 'SUM(overtimeHours) AS overtimeHours'])
                ->groupBy('uName')
                ->orderBy('CONVERT(uName USING gbk)', 'ASC', false)
                ->limit($rows)
                ->offset($rows * ($page - 1))
                ->get()
                ->getResultArray();
            // 考勤汇总信息（二维数组）进行多维排序（当前面排序字段值相等时，依次以后面另一个字段继续排序，类似数据库多字段排序）
            if (count($list) > 0) {
                foreach ($list as $k => $v) {
                    $userInfo = $this->db->table('user')->select('joinDate, leaveDate')->where('name', $v['uName'])->get()->getRowArray();
                    if (!$userInfo) {
                        continue;
                    }
                    // 如果入职日期大于统计起始日期，入职当月应出勤天数要减去多算的
                    if ($userInfo['joinDate'] > $beginDate) {
                        $joinDateCurDay = date('j', strtotime($userInfo['joinDate'])); // 获取入职日期的“日”部分（如8号返回8）
                        $joinMonthGoneSunday = 0; // 入职当月已过去几个星期天
                        for ($i = 1; $i < intval($joinDateCurDay); $i++) {
                            if (date('w', strtotime(date('Y-m', strtotime($userInfo['joinDate'])) . '-' . ($i < 10 ? ('0' . $i) : $i))) == 0) {
                                $joinMonthGoneSunday += 1;
                            }
                        }
                        $list[$k]['mustDayNum'] = intval($list[$k]['mustDayNum']) - (intval($joinDateCurDay) - 1 - $joinMonthGoneSunday);
                    }
                    // 如果已离职且离职职日期小于统计结束日期，离职当月应出勤天数要减去多算的
                    if ($userInfo['leaveDate'] != '0000-00-00' && $userInfo['leaveDate'] < $endDate) {
                        $leaveDateCurDay = date('j', strtotime($userInfo['leaveDate'])); // 获取离职日期的“日”部分（如8号返回8）
                        $leaveMonthTotalDays = date('t', strtotime($userInfo['leaveDate'])); // 获取离职月份的总天数
                        $leaveMonthLeftSunday = 0; // 离职当月剩余几个星期天
                        for ($i = intval($leaveDateCurDay); $i < intval($leaveMonthTotalDays); $i++) {
                            if (date('w', strtotime(date('Y-m', strtotime($userInfo['leaveDate'])) . '-' . ($i < 10 ? ('0' . $i) : $i))) == 0) {
                                $leaveMonthLeftSunday += 1;
                            }
                        }
                        $list[$k]['mustDayNum'] = intval($list[$k]['mustDayNum']) - (intval($leaveMonthTotalDays) - intval($leaveDateCurDay) - $leaveMonthLeftSunday);
                    }
                    $list[$k]['mustDayNum'] = max($list[$k]['mustDayNum'], 1);
                    $list[$k]['workRate'] = round(min(floatval($list[$k]['workDays']) / intval($list[$k]['mustDayNum']) * 100, 100), 1);
                    $list[$k]['missDays'] = round(intval($list[$k]['mustDayNum']) - floatval($list[$k]['workDays']), 1);
                }
                $sort1 = array_column($list, 'workRate');
                $sort2 = array_column($list, 'workDays');
                $sort3 = array_column($list, 'workHours');
                $sort4 = array_column($list, 'isFull');
                $sort5 = array_column($list, 'dutyTimes');
                $sort6 = array_column($list, 'overtimeHours');
                $sort7 = array_column($list, 'llTimes');
                $sort8 = array_column($list, 'missDays');
                array_multisort($sort1, SORT_DESC, $sort2, SORT_DESC, $sort3, SORT_DESC, $sort4, SORT_DESC, $sort5, SORT_DESC, $sort6, SORT_DESC, $sort7, SORT_ASC, $sort8, SORT_ASC, $list);
            }
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['page'] = $page;
        $json['data']['records'] = count($list);
        $json['data']['total'] = ceil($json['data']['records'] / $rows);
        $json['data']['rows'] = $list;
        return $this->response->setJSON($json);
    }

    /**财务相关 */
    // 纳税申报
    public function taxReturn()
    {
        if (!$this->myservice->verifyRight('TAX_RETURN_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/Report/taxReturn 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        set_time_limit(0); // 设置脚本运行时间无限制，直到脚本运行完毕
        session_write_close(); // 主动关闭session读写保护来解除锁定，避免其他访问无法读写session造成阻塞引起假死
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 1000);
        $year = (int)$this->request->getGetPost('year');
        $date = (int)$this->request->getGetPost('date');
        $companys  = [];
        if ($year > 0 && $date > 0) {
            switch ($date) {
                case 1: // 所选年1月
                    $dateFrom = $year . '-01-01';
                    $dateTo = $year . '-01-31';
                    break;
                case 2: // 所选年2月
                    $dateFrom = $year . '-02-01';
                    $dateTo = $year . '-02-29';
                    break;
                case 3: // 所选年3月
                    $dateFrom = $year . '-03-01';
                    $dateTo = $year . '-03-31';
                    break;
                case 4: // 所选年4月
                    $dateFrom = $year . '-04-01';
                    $dateTo = $year . '-04-30';
                    break;
                case 5: // 所选年5月
                    $dateFrom = $year . '-05-01';
                    $dateTo = $year . '-05-31';
                    break;
                case 6: // 所选年6月
                    $dateFrom = $year . '-06-01';
                    $dateTo = $year . '-06-30';
                    break;
                case 7: // 所选年7月
                    $dateFrom = $year . '-07-01';
                    $dateTo = $year . '-07-31';
                    break;
                case 8: // 所选年8月
                    $dateFrom = $year . '-08-01';
                    $dateTo = $year . '-08-31';
                    break;
                case 9: // 所选年9月
                    $dateFrom = $year . '-09-01';
                    $dateTo = $year . '-09-30';
                    break;
                case 10: // 所选年10月
                    $dateFrom = $year . '-10-01';
                    $dateTo = $year . '-10-31';
                    break;
                case 11: // 所选年11月
                    $dateFrom = $year . '-11-01';
                    $dateTo = $year . '-11-30';
                    break;
                case 12: // 所选年12月
                    $dateFrom = $year . '-12-01';
                    $dateTo = $year . '-12-31';
                    break;
                case 13: // 所选年一季度
                    $dateFrom = $year . '-01-01';
                    $dateTo = $year . '-03-31';
                    break;
                case 14: // 所选年二季度
                    $dateFrom = $year . '-04-01';
                    $dateTo = $year . '-06-30';
                    break;
                case 15: // 所选年三季度
                    $dateFrom = $year . '-07-01';
                    $dateTo = $year . '-09-30';
                    break;
                case 16: // 所选年四季度
                    $dateFrom = $year . '-10-01';
                    $dateTo = $year . '-12-31';
                    break;
                case 17: // 所选年全年
                    $dateFrom = $year . '-01-01';
                    $dateTo = $year . '-12-31';
                    break;
                default: // 默认全部
                    $dateFrom = '2018-01-01';
                    $dateTo = date('Y-m-t');
                    break;
            }
            $companys = $this->db->table('company')->where(['isDisable' => 0, 'isDelete' => 0])->orderBy('ordnum ASC, id ASC')->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
            foreach ($companys as $k => $v) {
                $companys[$k]['dateFrom'] = $dateFrom;
                $companys[$k]['dateTo'] = $dateTo;
                $companys[$k]['billFrom'] = $this->db->table('billFrom')->select('GROUP_CONCAT(name) AS billFrom', false)->where(['company' => $v['name'], 'isDelete' => 0])->get()->getRowArray()['billFrom'] ?? '';
                // 网店开票金额
                $companys[$k]['onlineOrderInvoiceAmount'] = (float)$this->db->table('invoice')->selectSum('amount', 'onlineOrderInvoiceAmount')->where(['date >=' => $dateFrom, 'date <=' => $dateTo, 'inoutType' => 0 , 'us' => $v['id'], 'transType' => 101201])->notLike('status1', '已红冲', 'after')->where(['isDelete' => 0])->get()->getRowArray()['onlineOrderInvoiceAmount'] ?? 0;
                // 绑定网店销售金额（电商平台对接了税务局，只要挂了公司资质，销售额都是要报税的）
                $companys[$k]['onlineOrderAmount'] = (float)$this->db->table('bill_order')->selectSum('amount', 'onlineOrderAmount')->where([
                    'billType' => 'SALE',
                    'billDate >=' => $dateFrom,
                    'billDate <=' => $dateTo,
                    'sId' => $v['id'],
                    'isCancel' => 0,
                    'isDraft' => 0,
                    'isDelete' => 0
                ])->notLike('billNo', 'XD', 'after')->notLike('billNo', 'XXX', 'both')->get()->getRowArray()['onlineOrderAmount'] ?? 0;
                // 其他开票金额（主要是特销单、手工订单、官网订单，其他没挂公司资质的网店订单开出的票）
                $companys[$k]['notOnlineOrderInvoiceAmount'] = (float)$this->db->table('invoice')
                    ->selectSum('amount', 'notOnlineOrderInvoiceAmount')
                    ->where(['date >=' => $dateFrom, 'date <=' => $dateTo, 'inoutType' => 0, 'us' => $v['id'], 'isDelete' => 0])
                    ->groupStart()
                        ->like('billNo', 'XH')
                        ->orLike('billNo', 'XD')
                        ->orLike('billNo', 'XXX')
                    ->groupEnd()
                    ->notLike('status1', '已红冲', 'after')
                    ->get()
                    ->getRowArray()['notOnlineOrderInvoiceAmount'] ?? 0;
                $companys[$k]['totalAmount'] = bcadd($companys[$k]['onlineOrderAmount'], $companys[$k]['notOnlineOrderInvoiceAmount'], 2);
            }
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['page'] = $page;
        $json['data']['records'] = count($companys);
        $json['data']['total'] = ceil($json['data']['records'] / $rows);
        $json['data']['rows'] = $companys;
        return $this->response->setJSON($json);
    }
    // 税务分析
    public function taxReport()
    {

    }
    // 收支明细
    public function moneyDetail()
    {
        if (!$this->myservice->verifyRight('MONEY_DETAIL')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/Report/moneyDetail 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        set_time_limit(0); // 设置脚本运行时间无限制，直到脚本运行完毕
        session_write_close(); // 主动关闭session读写保护来解除锁定，避免其他访问无法读写session造成阻塞引起假死
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $transType = intval($this->request->getGetPost('transType'));
        $typeId = $this->request->getGetPost('typeId');
        $accId = intval($this->request->getGetPost('accId'));
        $wayId = intval($this->request->getGetPost('wayId'));
        $uId = intval($this->request->getGetPost('uId'));
        $minAmount = (float)$this->request->getGetPost('minAmount');
        $maxAmount = (float)$this->request->getGetPost('maxAmount');
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');
        $skey = $this->request->getGetPost('skey');
        $builder = $this->db->table('bill_money_info AS a')->where('a.isDelete', 0);
        if ($transType > 0) {
            if ($transType == 1) {
                $builder->whereIn('a.transType', [103101, 103103]);
            } elseif ($transType == 2) {
                $builder->whereIn('a.transType', [103102, 103104]);
            } else {
                $builder->where('a.transType', $transType);
            }
        }
        if ($typeId) {
            $builder->whereIn('a.typeId', explode(',', $typeId));
        }
        if ($accId > 0) {
            $builder->where('a.accId', $accId);
        }
        if ($wayId > 0) {
            $builder->where('a.wayId', $wayId);
        }
        if ($uId > 0) {
            $builder->where('a.uId', $uId);
        }
        if ($minAmount > 0) {
            $builder->where('a.amount >=', $minAmount);
        }
        if ($maxAmount > 0) {
            $builder->where('a.amount <=', $maxAmount);
        }
        if ($beginDate) {
            $builder->where('a.settlementTime >=', $beginDate);
        }
        if ($endDate) {
            $builder->where('a.settlementTime <=', $endDate);
        }
        if ($skey !== '' && $skey !== null) {
            $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
            foreach ($keywords as $kw) {
                $contactIds = array_column($this->db->table('contact')->select('id')->like('name', $this->db->escapeLikeString($kw))->get()->getResultArray(), 'id');
                if (count($contactIds) > 0) {
                    $builder->groupStart()
                        ->like('a.billNo', $this->db->escapeLikeString($kw))
                        ->orLike('a.description', $this->db->escapeLikeString($kw))
                        ->orWhereIn('a.contactId', $contactIds)
                    ->groupEnd();
                } else {
                    $builder->groupStart()
                        ->like('a.billNo', $this->db->escapeLikeString($kw))
                        ->orLike('a.description', $this->db->escapeLikeString($kw))
                    ->groupEnd();
                }
            }
        }
        $allowedSort = ['id','billNo','billDate','billType','amount','typeName','accName','wayName','settlementTime','uName','contactName','settlementTime','description','moneyType'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $listBuilder = (clone $builder)->select([
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
            ->join('user AS f', 'a.uId = f.id AND f.isDelete = 0', 'left');
        $list = $listBuilder->orderBy($orderField, $sord)
            ->limit($rows)
            ->offset($rows * ($page - 1))
            ->get()
            ->getResultArray();
        $total = $listBuilder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['iid'] = intval($row['iid']);
            $v[$arr]['billDate'] = $row['billDate'];
            $v[$arr]['billNo'] = $row['billNo'];
            $v[$arr]['billType'] = $row['billType'];
            $v[$arr]['transType'] = intval($row['transType']);
            $v[$arr]['miniType'] = intval($row['miniType']);
            $v[$arr]['moneyType'] = intval($row['moneyType']);
            $v[$arr]['typeId'] = intval($row['typeId']);
            $v[$arr]['typeName'] = $row['typeName'];
            $v[$arr]['accId'] = intval($row['accId']);
            $v[$arr]['accName'] = $row['accName'];
            $v[$arr]['wayId'] = intval($row['wayId']);
            $v[$arr]['wayName'] = $row['wayName'];
            $v[$arr]['amount'] = (float)$row['amount'];
            $v[$arr]['settlementTime'] = $row['settlementTime'];
            $v[$arr]['description'] = $row['description'];
            $v[$arr]['contactId'] = intval($row['contactId']);
            $v[$arr]['contactName'] = $row['contactName'];
            $v[$arr]['uId'] = intval($row['uId']);
            $v[$arr]['uName'] = $row['uName'];
        }
        $json['data']['totalInAmount'] = (clone $builder)->selectSum('a.amount', 'totalInAmount')->whereIn('a.transType', [103101, 103103])->get()->getRowArray()['totalInAmount'] ?? 0;
        $json['data']['totalOutAmount'] = (clone $builder)->selectSum('a.amount', 'totalOutAmount')->whereIn('a.transType', [103102, 103104])->get()->getRowArray()['totalOutAmount'] ?? 0;
        $total = (clone $builder)->countAllResults();
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['page'] = $page;
        $json['data']['records'] = $total;
        $json['data']['total'] = ceil($json['data']['records'] / $rows);
        $json['data']['rows'] = isset($v) ? array_values($v) : [];
        return $this->response->setJSON($json);
    }
    // 财务报表
    public function moneyReport()
    {
    }
}
