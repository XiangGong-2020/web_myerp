<?php

namespace App\Controllers\Base;
use App\Controllers\BaseController;

class ShopAccount extends BaseController
{
    //网店账号列表
    public function list()
    {
        if (!$this->myservice->verifyRight('SHOPACCOUNT_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/ShopAccount/list 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 10000);
        $builder = $this->db->table('shop_account')->where('isDelete', 0);
        $list = (clone $builder)->orderBy('id', 'DESC')->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['name'] = $row['name'];
            $v[$arr]['uId'] = intval($row['uId']);
            $v[$arr]['uName'] = $row['uName'];
            $v[$arr]['his'] = $row['his'];
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['page'] = $page;
        $json['data']['records'] = $total;
        $json['data']['total'] = ceil($json['data']['records'] / $rows);
        $json['data']['rows'] = $v;
        return $this->response->setJSON($json);
    }
    //查询
    public function query()
    {
        if (!$this->myservice->verifyRight('SHOPACCOUNT_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/ShopAccount/query 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('shop_account')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
        if ($data) {
            $json['status'] = 200;
            $json['msg'] = 'success';
            $json['data']['id'] = intval($data['id']);
            $json['data']['name'] = $data['name'];
            $json['data']['uId'] = intval($data['uId']);
            $json['data']['uName'] = $data['uName'];
            $json['data']['his'] = $data['his'];
            return $this->response->setJSON($json);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    //新增
    public function add()
    {
        if (!$this->myservice->verifyRight('SHOPACCOUNT_ADD')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/ShopAccount/add 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $data = $this->validform($data);
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            $this->db->transStart();
            $this->db->table('shop_account')->insert(array_intersect_key($data, array_flip(['name', 'uId', 'uName', 'his'])));
            $data['id'] = $this->db->insertID();
            $this->myservice->log('新增了网店账号：' . $data['name']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '新增成功！', 'data' => $data]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    //编辑
    public function update()
    {
        if (!$this->myservice->verifyRight('SHOPACCOUNT_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/ShopAccount/update 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $data = $this->validform($data);
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            $this->db->transStart();
            $this->db->table('shop_account')->where('id', $data['id'])->update(array_intersect_key($data, array_flip(['name', 'uId', 'uName', 'his'])));
            $this->myservice->log('编辑了网店账号：' . $data['name']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '编辑成功！', 'data' => $data]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    //删除
    public function delete()
    {
        if (!$this->myservice->verifyRight('SHOPACCOUNT_DELETE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/ShopAccount/delete 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('shop_account')->where('id', $id)->get()->getRowArray();
        if ($data) {
            $this->db->transStart();
            $this->db->table('shop_account')->where('id', $id)->update(['isDelete' => 1]);
            $this->myservice->log('删除了网店账号：' . $data['name']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '删除成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    //数据验证
    private function validform(array $data)
    {
        if (strlen($data['name']) < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '网店账号不能为空！']);
        }
        $data['id'] = isset($data['id']) ? intval($data['id']) : -1;
        $where['isDelete'] = 0;
        $where['name'] = $data['name'];
        $where['id != '] = $data['id'];
        if ($this->db->table('shop_account')->where(['id != ' => $data['id'], 'name' => $data['name'], 'isDelete' => 0])->countAllResults() > 0) {
            return $this->response->setJSON(['status' => -1, 'msg' => '网店账号重复！']);
        }
        return $data;
    }
}
