<?php

namespace App\Controllers\Base;
use App\Controllers\BaseController;

class WorkSite extends BaseController
{
    // 获取所有业务网站信息
    public function list()
    {
        if (!$this->myservice->verifyRight('WORKSITE_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/WorkSite/list 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $list = $this->db->table('work_site')->where('isDelete', 0)->orderBy('id', 'ASC')->get()->getResultArray();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['urlName'] = $row['urlName'];
            $v[$arr]['urlDes'] = $row['urlDes'];
            $v[$arr]['urlLink'] = $row['urlLink'];
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data'] = $v;
        return $this->response->setJSON($json);
    }
    // 获取某个业务网站信息
    public function query()
    {
        if (!$this->myservice->verifyRight('WORKSITE_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/WorkSite/query 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('work_site')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
        if ($data) {
            $info['id'] = $id;
            $info['urlName'] = $data['urlName'];
            $info['urlDes'] = $data['urlDes'];
            $info['urlLink'] = $data['urlLink'];
            $json['status'] = 200;
            $json['msg'] = 'success';
            $json['data'] = $info;
            return $this->response->setJSON($json);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 新增业务网站
    public function add()
    {
        if (!$this->myservice->verifyRight('WORKSITE_ADD')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/WorkSite/add 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $data = $this->validform($data);
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            $info = array_intersect_key($data, array_flip(['urlName', 'urlDes', 'urlLink']));
            $this->db->transStart();
            $this->db->table('work_site')->insert($info);
            $data['id'] = $this->db->insertID();
            $this->myservice->log('新增了业务网站，名称：' . $data['urlName']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '新增成功！', 'data' => $data]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    // 编辑业务网站
    public function update()
    {
        if (!$this->myservice->verifyRight('WORKSITE_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/WorkSite/update 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $data = $this->validform($data);
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            $info = array_intersect_key($data, array_flip(['urlName', 'urlDes', 'urlLink']));
            $this->db->transStart();
            $this->db->table('work_site')->where(['id' => $data['id']])->update($info);
            $this->myservice->log('编辑了业务网站，名称：' . $data['urlName']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '编辑成功！', 'data' => $data]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    // 删除业务网站
    public function delete()
    {
        if (!$this->myservice->verifyRight('WORKSITE_DELETE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/WorkSite/delete 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('work_site')->where(['id' => $id])->get()->getRowArray();
        if ($data) {
            $this->db->table('work_site')->where(['id' => $id])->update(['isDelete' => 1]);
            $this->myservice->log('删除了业务网站，名称：' . $data['urlName']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '删除成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 数据验证
    private function validform(array $data)
    {
        $data['id'] = isset($data['id']) ? intval($data['id']) : -1;
        $where['urlLink'] = $data['urlLink'];
        $where['isDelete'] = 0;
        $where['id !='] = $data['id'];
        if ($this->db->table('work_site')->where(['urlLink' => $data['urlLink'], 'isDelete' => 0, 'id !=' => $data['id']])->countAllResults() > 0) {
            return $this->response->setJSON(['status' => -1, 'msg' => '网站链接重复！']);
        }
        return $data;
    }
}
