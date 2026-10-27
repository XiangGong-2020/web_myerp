<?php

namespace App\Controllers\Base;
use App\Controllers\BaseController;

class DeliverBag extends BaseController
{
    // 发包记录 列表
    public function list()
    {
        if (!$this->myservice->verifyRight('DELIVERBAG_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/DeliverBag/list 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $status1 = $this->request->getGetPost('status1');
        $status2 = $this->request->getGetPost('status2');
        $status3 = intval($this->request->getGetPost('status3'));
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');
        $weight = (float)$this->request->getGetPost('weight');
        $amount = (float)$this->request->getGetPost('amount');
        $skey = $this->request->getGetPost('skey');
        $builder = $this->db->table('deliver_bag')->where('isDelete', 0);
        if ($status1) {
            $builder->groupStart()
                ->where('packName', $status1)
                ->orWhere('scanName', $status1)
            ->groupEnd();
        }
        if ($status2) {
            $builder->where('company', $status2);
        }
        if ($beginDate) {
            $builder->where(($status3 == 0 ? 'printTime' : ($status3 == 1 ? 'packTime' : 'scanTime')) . ' >=', $beginDate);
        }
        if ($endDate) {
            $builder->where(($status3 == 0 ? 'printTime' : ($status3 == 1 ? 'packTime' : 'scanTime')) . ' <=', $endDate);
        }
        if ($weight > 0) {
            $builder->where('weight >', $weight);
        }
        if ($amount > 0) {
            $builder->where('amount >', $amount);
        }
        if ($skey !== '' && $skey !== null) {
            $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
            foreach ($keywords as $kw) {
                $builder->groupStart()
                    ->like('no', $this->db->escapeLikeString($kw))
                    ->orLike('orderNo', $this->db->escapeLikeString($kw))
                    ->orLike('lastUpdator', $this->db->escapeLikeString($kw))
                    ->orLike('address', $this->db->escapeLikeString($kw))
                ->groupEnd();
            }
        }
        $allowedSort = ['id','printTime','weight','amount','packTime','scanTime','description','dullOperHis'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $list = (clone $builder)->orderBy($orderField, $sord)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['no'] = $row['no'];
            $v[$arr]['company'] = $row['company'];
            $v[$arr]['address'] = $row['address'];
            $v[$arr]['printTime'] = '0000-00-00 00:00:00' == $row['printTime'] ? '' : $row['printTime'];
            $v[$arr]['lastUpdator'] = $row['lastUpdator'];
            $v[$arr]['lastFourMobileNum'] = $row['lastFourMobileNum'];
            $v[$arr]['chType'] = $row['chType'];
            $v[$arr]['sellerNick'] = $row['sellerNick'];
            $v[$arr]['orderNo'] = $row['orderNo'];
            $v[$arr]['weight'] = (float)$row['weight'];
            $v[$arr]['amount'] = (float)$row['amount'];
            $v[$arr]['packName'] = $row['packName'];
            $v[$arr]['scanName'] = $row['scanName'];
            $v[$arr]['packTime'] = '0000-00-00 00:00:00' == $row['packTime'] ? '' : $row['packTime'];
            $v[$arr]['scanTime'] = '0000-00-00 00:00:00' == $row['scanTime'] ? '' : $row['scanTime'];
            $v[$arr]['scanWay'] = intval($row['scanWay']);
            $v[$arr]['targetAmount'] = (float)$row['targetAmount'];
            $v[$arr]['description'] = $row['description'];
            $v[$arr]['dullOperHis'] = $row['dullOperHis'];
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['page'] = $page;
        $json['data']['records'] = $total;
        $json['data']['total'] = ceil($json['data']['records'] / $rows);
        $json['data']['rows'] = $v;
        return $this->response->setJSON($json);
    }
    // 发包记录 获取信息
    public function query()
    {
        if (!$this->myservice->verifyRight('DELIVERBAG_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/DeliverBag/query 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('deliver_bag')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
        if ($data) {
            $info['id'] = $id;
            $info['no'] = $data['no'];
            $info['company'] = $data['company'];
            $info['weight'] = (float)$data['weight'];
            $info['packName'] = $data['packName'];
            $info['scanName'] = $data['scanName'];
            $info['packTime'] = '0000-00-00 00:00:00' == $data['packTime'] ? '' : $data['packTime'];
            $info['scanTime'] = '0000-00-00 00:00:00' == $data['scanTime'] ? '' : $data['scanTime'];
            $info['printTime'] = '0000-00-00 00:00:00' == $data['printTime'] ? '' : $data['printTime'];
            $info['address'] = $data['address'];
            $info['amount'] = (float)$data['amount'];
            $info['orderNo'] = $data['orderNo'];
            $info['targetAmount'] = (float)$data['targetAmount'];
            $info['description'] = $data['description'];
            $json['status'] = 200;
            $json['msg'] = 'success';
            $json['data'] = $info;
            return $this->response->setJSON($json);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 发包记录 新增
    public function add()
    {
        if (!$this->myservice->verifyRight('DELIVERBAG_ADD')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/DeliverBag/add 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $data = $this->validform($data);
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            if ($this->db->table('deliver_bag')->where(['no' => $data['no'], 'isDelete' => 0])->countAllResults() > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '发包记录重复']);
            }
            $info = array_intersect_key($data, array_flip(['no', 'company', 'address', 'weight', 'amount', 'chType', 'lastUpdator', 'packName', 'packTime', 'scanName', 'scanTime', 'printTime', 'orderNo', 'targetAmount', 'description']));
            $this->db->transStart();
            $this->db->table('deliver_bag')->insert($info);
            $data['id'] = $this->db->insertID();
            $this->myservice->log('新增发包记录，运单号：' . $data['no']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '新增成功！', 'data' => $data]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    // 发包记录 编辑
    public function update()
    {
        if (!$this->myservice->verifyRight('DELIVERBAG_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/DeliverBag/update 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $data = $this->validform($data);
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            $info = array_intersect_key($data, array_flip(['no', 'company', 'address', 'weight', 'amount', 'packName', 'packTime', 'scanName', 'scanTime', 'printTime', 'orderNo', 'targetAmount', 'description']));
            $this->db->transStart();
            $this->db->table('deliver_bag')->where('id', $data['id'])->update($info);
            $this->myservice->log('编辑了发包记录，运单号：' . $data['no']);
            $newData = $this->db->table('deliver_bag')->where('id', $data['id'])->get()->getRowArray();
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '编辑成功！', 'data' => $newData]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    // 发包记录 删除
    public function delete()
    {
        if (!$this->myservice->verifyRight('DELIVERBAG_DELETE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/DeliverBag/delete 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('deliver_bag')->where('id', $id)->get()->getRowArray();
        if ($data) {
            $this->db->transStart();
            $this->db->table('deliver_bag')->where('id', $id)->update(['isDelete' => 1]);
            $this->myservice->log('删除了发包记录，运单号：' . $data['no']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '删除成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 发包记录 数据验证
    private function validform(array $data)
    {
        if (strlen($data['no']) < 8 || strlen($data['no']) > 18) {
            return $this->response->setJSON(['status' => -1, 'msg' => '运单号长度应为8~18位之间！']);
        }
        if (strlen($data['company']) < 2) {
            return $this->response->setJSON(['status' => -1, 'msg' => '请选择所属公司！']);
        }
        if (strlen($data['address']) < 2) {
            return $this->response->setJSON(['status' => -1, 'msg' => '请选择目的地！']);
        }
        if ($data['weight'] <= 0) {
            return $this->response->setJSON(['status' => -1, 'msg' => '包裹重量需大于0！']);
        }
        if ($data['packName'] == '') {
            return $this->response->setJSON(['status' => -1, 'msg' => '请选择打包人！']);
        }
        if ($data['scanName'] == '') {
            return $this->response->setJSON(['status' => -1, 'msg' => '请选择称重人！']);
        }
        if ($data['packTime'] == '' || $data['packTime'] == '0000-00-00 00:00:00') {
            return $this->response->setJSON(['status' => -1, 'msg' => '请选择打包扫码时间！']);
        }
        if ($data['scanTime'] == '' || $data['scanTime'] == '0000-00-00 00:00:00') {
            return $this->response->setJSON(['status' => -1, 'msg' => '请选择称重扫码时间！']);
        }
        $data['lastUpdator'] = $this->myerp['uName'];
        $data['chType'] = '手录';
        return $data;
    }


    // 发包滞留 列表
    public function dull_list()
    {
        if (!$this->myservice->verifyRight('DELIVERBAG_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/DeliverBag/dull_list 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $status1 = $this->request->getGetPost('status1');
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');
        $unDone = intval($this->request->getGetPost('unDone'));
        $skey = $this->request->getGetPost('skey');
        $builder = $this->db->table('deliver_bag')->where('scanName', '')->where('scanTime', '0000-00-00 00:00:00')->where('isDelete', 0);
        if ($status1) {
            $builder->where('company', $status1);
        }
        if ($beginDate) {
            $builder->where('printTime >=', $beginDate);
        }
        if ($endDate) {
            $builder->where('printTime <=', $endDate);
        }
        if ($skey !== '' && $skey !== null) {
            $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
            foreach ($keywords as $kw) {
                $builder->groupStart()
                    ->like('no', $this->db->escapeLikeString($kw))
                    ->orLike('orderNo', $this->db->escapeLikeString($kw))
                    ->orLike('lastUpdator', $this->db->escapeLikeString($kw))
                ->groupEnd();
            }
        }
        $allowedSort = ['id','no','company','address','printTime','lastUpdator','lastFourMobileNum','chType','sellerNick','orderNo','description','dullOperHis'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $list = (clone $builder)->orderBy($orderField, $sord)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['no'] = $row['no'];
            $v[$arr]['company'] = $row['company'];
            $v[$arr]['address'] = $row['address'];
            $v[$arr]['printTime'] = '0000-00-00 00:00:00' == $row['printTime'] ? '' : $row['printTime'];
            $v[$arr]['lastUpdator'] = $row['lastUpdator'];
            $v[$arr]['lastFourMobileNum'] = $row['lastFourMobileNum'];
            $v[$arr]['chType'] = $row['chType'];
            $v[$arr]['sellerNick'] = $row['sellerNick'];
            $v[$arr]['orderNo'] = $row['orderNo'];
            $v[$arr]['description'] = $row['description'];
            $v[$arr]['dullOperHis'] = $row['dullOperHis'];
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['page'] = $page;
        $json['data']['records'] = $total;
        $json['data']['total'] = ceil($json['data']['records'] / $rows);
        $json['data']['rows'] = $v;
        return $this->response->setJSON($json);
    }
    // 发包滞留 追加滞留处理记录
    public function dull_update_dullOperHis()
    {
        if (!$this->myservice->verifyRight('SO_ADD')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/DeliverBag/dull_update_dullOperHis 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $dullOperHis = $this->request->getGetPost('dullOperHis');
        $data = $this->db->table('deliver_bag')->where('id', $id)->get()->getRowArray();
        if ($data) {
            $this->db->transStart();
            $this->db->table('deliver_bag')->where('id', $id)->update(['dullOperHis' => $dullOperHis]);
            $this->myservice->log('追加了运单号 ' . $data['no'] . ' 的滞留处理记录');
            $newData = $this->db->table('deliver_bag')->where('id', $id)->get()->getRowArray();
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '操作成功！', 'data' => $newData]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 发包滞留 转换为正常
    public function dull_to_normal()
    {
        if (!$this->myservice->verifyRight('SO_ADD')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/DeliverBag/dull_to_normal 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('deliver_bag')->where('id', $id)->get()->getRowArray();
        if ($data) {
            $this->db->table('deliver_bag')->where('id', $id)->update(['scanName' => $data['packName'] ?: '系统', 'scanTime' => $data['packTime']]);
            $this->myservice->log('将滞留包裹转换为正常包裹，运单号：' . $data['no']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '操作成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
}
