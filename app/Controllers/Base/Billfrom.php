<?php

namespace App\Controllers\Base;
use App\Controllers\BaseController;

class Billfrom extends BaseController
{
    // 列表
    public function list()
    {
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $isDisable = intval($this->request->getGetPost('isDisable'));
        $skey = $this->request->getGetPost('skey');
        $builder = $this->db->table('billfrom');
        $builder->where('isDelete', 0);
        if ($isDisable >= 0) {
            $builder->where('isDisable', $isDisable);
        }
        if ($skey !== '' && $skey !== null) {
            $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
            foreach ($keywords as $kw) {
                $builder->groupStart()
                    ->like('name', $this->db->escapeLikeString($kw))
                    ->orLike('company', $this->db->escapeLikeString($kw))
                    ->orLike('description', $this->db->escapeLikeString($kw))
                ->groupEnd();
            }
        }
        $allowedSort = ['id','name','company','description','isDisable'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $list = (clone $builder)->orderBy($orderField, $sord)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['name'] = $row['name'];
            $v[$arr]['company'] = $row['company'];
            $v[$arr]['description'] = $row['description'];
            $v[$arr]['isDisable'] = intval($row['isDisable']);
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['page'] = $page;
        $json['data']['records'] = $total;
        $json['data']['total'] = ceil($json['data']['records'] / $rows);
        $json['data']['rows'] = $v;
        return $this->response->setJSON($json);
    }
    // 获取所有
    public function queryAll()
    {
        $isDisable = intval($this->request->getGetPost('isDisable'));
        $exclude = $this->request->getGetPost('exclude');
        $builder = $this->db->table('billfrom')->select('id, name, isDisable');
        $builder->where('isDelete', 0);
        $builder->where('isDisable', $isDisable);
        if ($exclude !== '' && $exclude !== null) {
            $keywords = array_values(array_filter(explode(',', $exclude), 'strlen'));
            foreach ($keywords as $kw) {
                $builder->where('name !=', $kw);
            }
        }
        $list = (clone $builder)->orderBy('name', 'DESC')->get()->getResultArray();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['name'] = $row['name'];
            $v[$arr]['isDisable'] = intval($row['isDisable']);
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['rows'] = $v;
        return $this->response->setJSON($json);
    }
    // 查询
    public function query()
    {
        if (!$this->myservice->verifyRight('BILLFROM_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Billfrom/query 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('billfrom')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
        if ($data) {
            $json['status'] = 200;
            $json['msg'] = 'success';
            $json['data']['id'] = intval($data['id']);
            $json['data']['name'] = $data['name'];
            $json['data']['company'] = $data['company'];
            $json['data']['description'] = $data['description'];
            return $this->response->setJSON($json);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 新增
    public function add()
    {
        if (!$this->myservice->verifyRight('BILLFROM_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Billfrom/add 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $data = $this->validform($data);
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            $info = array_intersect_key($data, array_flip(['name', 'company', 'description']));
            $this->db->transStart();
            $this->db->table('billfrom')->insert($info);
            $data['id'] = $this->db->insertID();
            if ($data['company']) {
                $billFrom = $this->db->table('billfrom')->select('name')->where('company', $data['company'])->get()->getResultArray();
                $billFrom = count($billFrom) > 0 ? implode(',', array_column($billFrom, 'name')) : '';
                $this->db->table('company')->where('name', $data['company'])->update(['billFrom' => $billFrom]);
            }
            $data['isDisable'] = 0;
            $this->myservice->log('新增了销售渠道：' . $data['name']);
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
        if (!$this->myservice->verifyRight('BILLFROM_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Billfrom/update 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $data = $this->validform($data);
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            $oldData = $this->db->table('billfrom')->where('id', $data['id'])->get()->getRowArray();
            $info = array_intersect_key($data, array_flip(['name', 'company', 'description']));
            $this->db->transStart();
            $this->db->table('billfrom')->where('id', $data['id'])->update($info);
            if ($data['name'] != $oldData['name']) {
                $this->myservice->log('编辑了销售渠道：' . $oldData['name'] . ' 为 ' . $data['name']);
            } else {
                $this->myservice->log('编辑了销售渠道：' . $data['name']);
            }
            if ($data['company'] != $oldData['company']) {
                $oldBillFrom = $this->db->table('billfrom')->select('name')->where('company', $oldData['company'])->get()->getRowArray();
                $oldBillFrom = count($oldBillFrom) > 0 ? implode(',', array_column($oldBillFrom, 'name')) : '';
                $this->db->table('company')->where('name', $oldData['company'])->update(['billFrom' => $oldBillFrom]);
                $newBillFrom = $this->db->table('billfrom')->select('name')->where('company', $data['company'])->get()->getRowArray();
                $newBillFrom = count($newBillFrom) > 0 ? implode(',', array_column($newBillFrom, 'name')) : '';
                $this->db->table('company')->where('name', $data['company'])->update(['billFrom' => $newBillFrom]);
            } else {
                $billFrom = $this->db->table('billfrom')->select('name')->where('company', $data['company'])->get()->getRowArray();
                $billFrom = count($billFrom) > 0 ? implode(',', array_column($billFrom, 'name')) : '';
                $this->db->table('company')->where('name', $data['company'])->update(['billFrom' => $billFrom]);
            }
            $data = $this->db->table('billfrom')->where('id', $data['id'])->get()->getRowArray();
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '编辑成功！', 'data' => $data]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    // 删除
    public function delete()
    {
        if (!$this->myservice->verifyRight('BILLFROM_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Billfrom/delete 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('billfrom')->where('id', $id)->get()->getRowArray();
        if ($data) {
            if ($this->db->table('bill_order')->where(['billFrom' => $data['name'], 'isDelete' => 0])->countAllResults() > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '销售渠道已发生业务，无法删除！']);
            }
            $this->db->transStart();
            $this->db->table('billfrom')->where('id', $id)->update(['isDelete' => 1]);
            $this->myservice->log('删除了销售渠道，ID：' . $id . ' 名称:' . $data['name']);
            $billFrom = $this->db->table('billfrom')->select('name')->where('company', $data['company'])->get()->getRowArray();
            $billFrom = count($billFrom) > 0 ? implode(',', array_column($billFrom, 'name')) : '';
            $this->db->table('company')->where('name', $data['company'])->update(['billFrom' => $billFrom]);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '删除成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 状态
    public function disable()
    {
        if (!$this->myservice->verifyRight('BILLFROM_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Billfrom/disable 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = (int)$this->request->getGetPost('id');
        $isDisable = intval($this->request->getGetPost('isDisable'));
        $data = $this->db->table('billfrom')->where('id', $id)->get()->getRowArray();
        if ($data) {
            $this->db->transStart();
            $this->db->table('billfrom')->where('id', $id)->update(['isDisable' => $isDisable]);
            $this->myservice->log('标记销售渠道 ' . $data['name'] . ' 的状态为：' . ($isDisable > 0 ? '已关闭' : '开放中'));
            $billFrom = $this->db->table('billfrom')->select('name')->where('company', $data['company'])->get()->getRowArray();
            $billFrom = count($billFrom) > 0 ? implode(',', array_column($billFrom, 'name')) : '';
            $this->db->table('company')->where('name', $data['company'])->update(['billFrom' => $billFrom]);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '操作成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    //数据验证
    private function validform(array $data)
    {
        $data['id'] = isset($data['id']) ? intval($data['id']) : 0;
        if (strlen($data['name']) < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '名称不能为空！']);
        }
        $builder = $this->db->table('billfrom')->where(['name' => $data['name'], 'isDelete' => 0]);
        if ($data['id'] > 0) {
            $builder->where('id !=', $data['id']);
        }
        if ($builder->countAllResults() > 0) {
            return $this->response->setJSON(['status' => -1, 'msg' => '名称重复！']);
        }
        return $data;
    }
}
