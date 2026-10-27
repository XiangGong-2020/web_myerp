<?php

namespace App\Controllers\Base;
use App\Controllers\BaseController;

class DeliverCompany extends BaseController
{
    //快递公司列表
    public function list()
    {
        if (!$this->myservice->verifyRight('DELIVERCOM_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/DeliverCompany/list 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $builder = $this->db->table('deliver_company')->where('isDelete', 0);
        $allowedSort = ['id','contactId','name','len','str','code100','type','description','isDisable'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $list = (clone $builder)->orderBy($orderField, $sord)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['contactId'] = intval($row['contactId']);
            $v[$arr]['name'] = $row['name'];
            $v[$arr]['len'] = $row['len'];
            $v[$arr]['str'] = $row['str'];
            $v[$arr]['code100'] = $row['code100'];
            $v[$arr]['type'] = intval($row['type']);
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
    //获取所有快递公司
    public function queryAll()
    {
        if (!$this->myservice->verifyRight('DELIVERCOM_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/DeliverCompany/queryAll 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $isDisable = intval($this->request->getGetPost('isDisable') ?? -1);
        $builder = $this->db->table('deliver_company')->where('isDelete', 0);
        if ($isDisable > -1) {
            $builder->where('isDisable', $isDisable);
        }
        $list = (clone $builder)->orderBy('isDisable ASC, id ASC')->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['name'] = $row['name'];
            $v[$arr]['isDisable'] = intval($row['isDisable']);
            $v[$arr]['code100'] = $row['code100'];
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['records'] = $total;
        $json['data']['rows'] = $v;
        return $this->response->setJSON($json);
    }
    //获取所有快递公司与对应的商家ID信息
    public function queryAllContactId()
    {
        if (!$this->myservice->verifyRight('DELIVERCOM_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/DeliverCompany/queryAllContactId 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = array_column($this->db->table('deliver_company')->select('name, contactId')->where('isDelete', 0)->orderBy('id', 'ASC')->groupBy('name')->get()->getResultArray(), 'contactId', 'name');
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data'] = $data ?? [];
        return $this->response->setJSON($json);
    }
    //获取信息
    public function query()
    {
        if (!$this->myservice->verifyRight('DELIVERCOM_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/DeliverCompany/query 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('deliver_company')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
        if ($data) {
            $info['id'] = $id;
            $info['contactId'] = intval($data['contactId']);
            $info['name'] = $data['name'];
            $info['len'] = $data['len'];
            $info['str'] = $data['str'];
            $info['code100'] = $data['code100'];
            $info['type'] = intval($data['type']);
            $info['description'] = $data['description'];
            $info['isDisable'] = intval($data['isDisable']);

            $json['status'] = 200;
            $json['msg'] = 'success';
            $json['data'] = $info;
            return $this->response->setJSON($json);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    //新增
    public function add()
    {
        if (!$this->myservice->verifyRight('DELIVERCOM_ADD')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/DeliverCompany/add 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $data = $this->validform($data);
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            if ($this->db->table('deliver_company')->where(['name' => $data['name'], 'isDelete' => 0])->countAllResults() > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '名称（简称）重复']);
            }
            $this->db->transStart();
            $this->db->table('deliver_company')->insert(array_intersect_key($data, array_flip(['name', 'code100', 'len', 'str', 'contactId', 'description'])));
            $data['id'] = $this->db->insertID();
            $this->myservice->log('新增了快递公司：' . $data['name']);
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
        if (!$this->myservice->verifyRight('DELIVERCOM_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/DeliverCompany/update 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $data = $this->validform($data);
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            if ($this->db->table('deliver_company')->where(['name' => $data['name'], 'id !=' => $data['id'], 'isDelete' => 0])->countAllResults() > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '名称（简称）重复']);
            }
            $this->db->transStart();
            $this->db->table('deliver_company')->where('id', $data['id'])->update(array_intersect_key($data, array_flip(['name', 'code100', 'len', 'str', 'contactId', 'description'])));
            $this->myservice->log('编辑了快递公司：' . $data['name']);
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
        if (!$this->myservice->verifyRight('DELIVERCOM_DELETE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/DeliverCompany/delete 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('deliver_company')->where(['id' => $id])->get()->getRowArray();
        if ($data) {
            $this->db->transStart();
            $this->db->table('deliver_company')->where(['id' => $id])->update(['isDelete' => 1]);
            $this->myservice->log('删除了快递公司，名称：' . $data['name']);
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
        if (strlen($data['name']) < 2 || strlen($data['name']) > 50) {
            return $this->response->setJSON(['status' => -1, 'msg' => '名称（简称）长度不对！']);
        }
        return $data;
    }
}
