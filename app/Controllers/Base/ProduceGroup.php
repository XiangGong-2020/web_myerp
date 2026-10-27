<?php

namespace App\Controllers\Base;
use App\Controllers\BaseController;

class ProduceGroup extends BaseController
{
    //列表
    public function list()
    {
        if (!$this->myservice->verifyRight('SYS_DEFAULT')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/ProduceGroup/list 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $builder = $this->db->table('pro_group');
        $builder->where('isDelete', 0);
        $list = (clone $builder)->orderBy('id', 'ASC')->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['name'] = $row['name'];
            $v[$arr]['members'] = $row['members'];
            $v[$arr]['type'] = $row['type'];
            $v[$arr]['CoCNo'] = $row['CoCNo'];
            $v[$arr]['description'] = $row['description'];
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['page'] = $page;
        $json['data']['records'] = $total;
        $json['data']['total'] = ceil($json['data']['records'] / $rows);
        $json['data']['rows'] = $v;
        return $this->response->setJSON($json);
    }
    //获取信息
    public function query()
    {
        if (!$this->myservice->verifyRight('SYS_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/ProduceGroup/query 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('pro_group')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
        if ($data) {
            $info['id'] = intval($data['id']);
            $info['name'] = $data['name'];
            $info['members'] = trim(preg_replace('/,,/', ',', $data['members']), ',');
            $info['type'] = $data['type'];
            $info['CoCNo'] = $data['CoCNo'];
            $info['description'] = $data['description'];

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
        if (!$this->myservice->verifyRight('SYS_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/ProduceGroup/add 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $name = $this->request->getGetPost('name');
        $members = trim(str_replace(',,', ',', $this->request->getGetPost('members')), ',');
        $type = $this->request->getGetPost('type');
        $CoCNo = $this->request->getGetPost('CoCNo');
        $description = $this->request->getGetPost('description');
        if ($name && $members) {
            if ($this->db->table('pro_group')->where(['name' => $name, 'isDelete' => 0])->countAllResults() > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '已存在相同名称的组！']);
            }
            $this->db->transStart();
            $this->db->table('pro_group')->insert(['name' => $name, 'members' => $members, 'type' => $type, 'CoCNo' => $CoCNo, 'description' => $description]);
            $iid = $this->db->insertID();
            $this->myservice->log('新增了生产分组，组名：' . $name . '，组员：' . $members);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '新增成功！', 'data' => ['id' => $iid]]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    //编辑
    public function update()
    {
        if (!$this->myservice->verifyRight('SYS_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/ProduceGroup/update 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $name = $this->request->getGetPost('name');
        $members = trim(preg_replace('/,,/', ',', $this->request->getGetPost('members')), ',');
        $type = $this->request->getGetPost('type');
        $CoCNo = $this->request->getGetPost('CoCNo');
        $description = $this->request->getGetPost('description');
        $data = $this->db->table('pro_group')->where(['id' => $id])->get()->getRowArray();
        if ($data) {
            if ($this->db->table('pro_group')->where(['id !=' => $id, 'name' => $name, 'isDelete' => 0])->countAllResults() > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '已存在相同名称的组！']);
            }
            $this->db->transStart();
            $this->db->table('pro_group')->where('id', $id)->update(['name' => $name, 'members' => $members, 'type' => $type, 'CoCNo' => $CoCNo, 'description' => $description]);
            $this->myservice->log('编辑了生产分组，组名：' . $name . '，组员：' . $members);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '编辑成功！', 'data' => ['id' => $id]]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    //删除
    public function delete()
    {
        if (!$this->myservice->verifyRight('SYS_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/ProduceGroup/delete 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('pro_group')->where('id', $id)->get()->getRowArray();
        if ($data) {
            $this->db->transStart();
            $this->db->table('pro_group')->where('id', $id)->update(['isDelete' => 1]);
            $this->myservice->log('删除了生产分组，组名：' . $data['name'] . '，组员：' . $data['members']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '删除成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
}
