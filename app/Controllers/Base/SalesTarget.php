<?php

namespace App\Controllers\Base;
use App\Controllers\BaseController;

class SalesTarget extends BaseController
{
    // 年销售目标 列表
    public function listYear()
    {
        if (!$this->myservice->verifyRight('SYS_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/SalesTarget/listYear 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $year = $this->request->getGetPost('year');
        $data = $this->db->table('sales_target_year')->where(['year' => $year, 'isDelete' => 0])->get()->getRowArray();
        if ($data) {
            $orderAmount = $this->db->table('bill_order')
                ->selectSum('amount')
                ->where('billDate >=', $year . '-01-01')
                ->where('billDate <=', $year . '-12-31')
                ->where('billType', 'SALE')
                ->where('isDelete', 0)
                ->get()
                ->getRowArray()['amount'] ?? 0;
            $contractAmount = $this->db->table('bill_contract')
                ->selectSum('amount')
                ->where('billDate >=', $year . '-01-01')
                ->where('billDate <=', $year . '-12-31')
                ->where('billType', 'SALE')
                ->where('redoSoType', 0)
                ->where('isDelete', 0)
                ->get()
                ->getRowArray()['amount'] ?? 0;
            $data['orderAmount'] = (float)$orderAmount;
            $data['contractAmount'] = (float)$contractAmount;
            $data['curAmount'] = (float)$orderAmount + (float)$contractAmount;
            return $this->response->setJSON(['status' => 200, 'msg' => 'success', 'data' => $data]);
        } else {
            return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
        }
    }
    // 年销售目标 获取信息
    public function queryYear()
    {
        if (!$this->myservice->verifyRight('SYS_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/SalesTarget/queryYear 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $year = $this->request->getGetPost('year');
        $data = $this->db->table('sales_target_year')->where(['year' => $year, 'isDelete' => 0])->get()->getRowArray();
        if ($data) {
            return $this->response->setJSON(['status' => 200, 'msg' => 'success', 'data' => $data]);
        } else {
            return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
        }
    }
    // 年销售目标 制定
    public function makeYear()
    {
        if (!$this->myservice->verifyRight('SYS_ADMIN')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/SalesTarget/makeYear 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $data = $this->validformYear($data);
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            $this->db->transStart();
            if ($data['id'] > 0) {
                $info = array_intersect_key($data, array_flip(['targetAmount']));
                $this->db->table('sales_target_year')->where(['id' => $data['id']])->update($info);
            } else {
                $data = array_intersect_key($data, array_flip(['year', 'targetAmount', 'uId', 'uName', 'date']));
                $this->db->table('sales_target_year')->insert($data);
                $data['id'] = $this->db->insertID();
            }
            $this->myservice->log('制定了' . $data['year'] . '年年度销售目标');
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '操作成功！', 'data' => $data]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    // 年销售目标 数据验证
    private function validformYear(array $data)
    {
        if ($data['targetAmount'] <= 0) {
            return $this->response->setJSON(['status' => -1, 'msg' => '目标金额必须大于零！']);
        }
        if ($data['id'] < 1 && $this->db->table('sales_target_year')->where(['year' => $data['year'], 'isDelete' => 0])->countAllResults() > 0) {
            return $this->response->setJSON(['status' => -1, 'msg' => '已制定过同年销售目标！']);
        }
        $data['uId'] = $this->myerp['uId'];
        $data['uName'] = $this->myerp['uName'];
        $data['date'] = date('Y-m-d');
        return $data;
    }
    // 月销售目标 列表
    public function listMonth()
    {
        if (!$this->myservice->verifyRight('SYS_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/SalesTarget/listMonth 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $year = $this->request->getGetPost('year');
        $data = $this->db->table('sales_target_month')
            ->where(['year' => $year, 'isDelete' => 0])
            ->orderBy('month', 'DESC')
            ->get()
            ->getResultArray();
        if ($data) {
            foreach($data as $k => $v) {
                $orderAmount = $this->db->table('bill_order')
                    ->selectSum('amount')
                    ->where('billDate >=', $year . '-' . $v['month'] . '-01')
                    ->where('billDate <=', $year . '-' . $v['month'] . '-31')
                    ->where(['billType' => 'SALE', 'isDelete' => 0])
                    ->get()
                    ->getRowArray()['amount'] ?? 0;
                $contractAmount = $this->db->table('bill_contract')
                    ->selectSum('amount')
                    ->where('billDate >=', $year . '-' . $v['month'] . '-01')
                    ->where('billDate <=', $year . '-' . $v['month'] . '-31')
                    ->where(['billType' => 'SALE', 'redoSoType' => 0, 'isDelete' => 0])
                    ->get()
                    ->getRowArray()['amount'] ?? 0;
                $data[$k]['orderAmount'] = (float)$orderAmount;
                $data[$k]['contractAmount'] = (float)$contractAmount;
                $data[$k]['curAmount'] = (float)$orderAmount + (float)$contractAmount;
                $data[$k]['info'] = $this->db->table('sales_target_month_info')->where(['year' => $year, 'isDelete' => 0, 'month' => $v['month'], 'targetAmount >' => 0])->get()->getResultArray();
                if (count($data[$k]['info']) > 0) {
                    foreach($data[$k]['info'] as $k1 => $v1) {
                        $userOrderAmount = $this->db->table('bill_order')
                            ->selectSum('amount')
                            ->where('billDate >=', $year . '-' . $v['month'] . '-01')
                            ->where('billDate <=', $year . '-' . $v['month'] . '-31')
                            ->where(['billType' => 'SALE', 'uId' => $v1['salerId'], 'isCancel' => 0, 'isDelete' => 0])
                            ->get()
                            ->getRowArray()['amount'] ?? 0;
                        $userContractAmount = $this->db->table('bid_program')
                            ->select('SUM(CASE WHEN LOCATE(",", quotedName) > 0 THEN (amount / ((CHAR_LENGTH(quotedName) - CHAR_LENGTH(REPLACE(quotedName,",",""))) + 1)) ELSE amount END) AS amount', false)
                            ->where('createTime >=', $year . '-' . $v['month'] . '-01 00:00:00')
                            ->where('createTime <=', $year . '-' . $v['month'] . '-31 23:59:59')
                            ->where('FIND_IN_SET(' . $this->db->escape($v1['salerName']) . ', quotedName) >', 0, false)
                            ->where(['status' => 2, 'isDelete' => 0])
                            ->get()
                            ->getRowArray()['amount'] ?? 0;
                        $userOnlineShopAmount = $this->db->table('bill_onlineshopMp_info')
                            ->selectSum('amount')
                            ->where('beginDate >=', $v['year'] . '-' . $v['month'] . '-01')
                            ->where('endDate <=', $v['year'] . '-' . $v['month'] . '-31')
                            ->where(['uId' => $v1['salerId'], 'isDelete' => 0])
                            ->get()
                            ->getRowArray()['amount'] ?? 0;
                        $data[$k]['info'][$k1]['orderAmount'] = $userOrderAmount;
                        $data[$k]['info'][$k1]['contractAmount'] = $userContractAmount;
                        $data[$k]['info'][$k1]['onlineShopAmount'] = $userOnlineShopAmount;
                        $data[$k]['info'][$k1]['curAmount'] = $userOrderAmount + $userContractAmount + $userOnlineShopAmount;
                    }
                }
            }
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data'] = $data;
        return $this->response->setJSON($json);
    }
    // 月销售目标 获取信息
    public function queryMonth()
    {
        if (!$this->myservice->verifyRight('SYS_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/SalesTarget/queryMonth 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $year = intval($this->request->getGetPost('year'));
        $month = intval($this->request->getGetPost('month'));
        $where = 'year = ' . $year . ' AND month = ' . $month . ' AND isDelete = 0';

        $data = $this->db->table('sales_target_month')
            ->where(['year' => $year, 'month' => $month, 'isDelete' => 0])
            ->get()
            ->getRowArray() ?? [
                'id' => 0,
                'year' => $year,
                'month' => $month,
                'targetAmout' => '',
                'uId' => $this->myerp['uId'],
                'uName' => $this->myerp['uName'],
                'date' => date('Y-m-d')
            ];
        $data['info'] = $this->db->table('sales_target_month_info')
            ->where(['year' => $year, 'month' => $month, 'isDelete' => 0])
            ->get()
            ->getResultArray();
        $salerNameArr = array_column($data['info'], 'salerName');
        $allSalerArr = $this->db->table('user')
            ->select('id, name')
            ->where([
                'department' => '销售部',
                'joinDate <=' => $year . '-' . $month .'-31',
                'isDisable' => 0,
                'isDelete' => 0
            ])
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();
        foreach ($allSalerArr as $k => $v) {
            if (!in_array($v['name'], $salerNameArr)) {
                array_push($data['info'], [
                    'id' => 0,
                    'year' => $data['year'],
                    'month' => $data['month'],
                    'salerId' => $v['id'],
                    'salerName' => $v['name'],
                    'targetAmount' => '',
                    'uId' => $data['uId'],
                    'uName' => $data['uName'],
                    'date' => $data['date']
                ]);
            }
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data'] = $data;
        return $this->response->setJSON($json);
    }
    // 月销售目标 制定（新增 + 编辑）
    public function makeMonth()
    {
        if (!$this->myservice->verifyRight('SALE_MANAGER')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/SalesTarget/makeMonth 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $data = $this->validformMonth($data);
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            // 事务开始
            $this->db->transStart();
            if ($data['id'] > 0) {
                $info = array_intersect_key($data, array_flip(['targetAmount']));
                $this->db->table('sales_target_month')->where('id', $data['id'])->update($info);
            } else {
                $info = array_intersect_key($data, array_flip(['year', 'month', 'targetAmount', 'uId', 'uName', 'date']));
                $this->db->table('sales_target_month')->insert($info);
            }
            foreach ($data['info'] as $k => $v) {
                if ($v['id'] > 0) {
                    $this->db->table('sales_target_month_info')->where('id', $v['id'])->update([
                        'targetAmount' => $v['targetAmount']
                    ]);
                } else {
                    $this->db->table('sales_target_month_info')->insert([
                        'year' => $data['year'],
                        'month' => $data['month'],
                        'salerId' => $v['salerId'],
                        'salerName' => $v['salerName'],
                        'targetAmount' => $v['targetAmount'],
                        'uId' => $data['uId'],
                        'uName' => $data['uName'],
                        'date' => $data['date']
                    ]);
                }
            }
            $this->myservice->log('制定了' . $data['year'] . '年' . $data['month'] . '月销售目标');
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '操作成功！', 'data' => $data]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    // 月销售目标 数据验证
    private function validformMonth(array $data)
    {
        if ($data['year'] <= 0) {
            return $this->response->setJSON(['status' => -1, 'msg' => '年份错误！']);
        }
        if ($data['month'] <= 0) {
            return $this->response->setJSON(['status' => -1, 'msg' => '月份错误！']);
        }
        if ($data['targetAmount'] <= 0) {
            return $this->response->setJSON(['status' => -1, 'msg' => '目标金额必须大于零！']);
        }
        if (count($data['info']) < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '任务分配错误！']);
        }
        if ($data['id'] < 1 && $this->db->table('sales_target_month')->where(['year' => $data['year'], 'month' => $data['month'], 'isDelete' => 0])->countAllResults() > 0) {
            return $this->response->setJSON(['status' => -1, 'msg' => '已制定过同年同月销售目标！']);
        }
        $data['uId'] = $this->myerp['uId'];
        $data['uName'] = $this->myerp['uName'];
        $data['date'] = date('Y-m-d');
        return $data;
    }
}
