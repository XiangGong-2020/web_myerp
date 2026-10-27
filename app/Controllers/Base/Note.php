<?php

namespace App\Controllers\Base;
use App\Controllers\BaseController;

class Note extends BaseController
{
    // 列表
    public function list()
    {
        if (!$this->myservice->verifyRight('SYS_DEFAULT')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Note/list 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $skey = $this->request->getGetPost('skey');
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');
        $builder = $this->db->table('note');
        $builder->where('uId', $this->myerp['uId']);
        if ($beginDate) {
            $builder->where('createTime >=', $beginDate . ' 00:00:00');
        }
        if ($endDate) {
            $builder->where('createTime <=', $endDate . ' 23:59:59');
        }
        if ($skey !== '' && $skey !== null) {
            $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
            foreach ($keywords as $kw) {
                $builder->groupStart()
                    ->like('title', $this->db->escapeLikeString($kw))
                    ->orLike('content', $this->db->escapeLikeString($kw))
                ->groupEnd();
            }
        }
        $builder->where('isDelete', 0);
        $allowedSort = ['id','title','uName','createTime','isDisable'];
        $builder->orderBy('isDisable', 'ASC');
        if (in_array($sidx, $allowedSort, true)) {
            $builder->orderBy($sidx, $sord);
        } else {
            $builder->orderBy('id', 'DESC');
        }
        $list = (clone $builder)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['title'] = $row['title'];
            $v[$arr]['uId'] = intval($row['uId']);
            $v[$arr]['uName'] = $row['uName'];
            $v[$arr]['createTime'] = $row['createTime'];
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
    // 查询
    public function query()
    {
        if (!$this->myservice->verifyRight('SYS_DEFAULT')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Note/query 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('note')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
        if ($data) {
            $info['id'] = $id;
            $info['title'] = $data['title'];
            $info['content'] = $data['content'];
            $info['files'] = $data['files'] == '' ? [] : json_decode($data['files'], true);

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
        if (!$this->myservice->verifyRight('SYS_DEFAULT')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Note/add 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $data = $this->validform($data);
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            $info = array_intersect_key($data, array_flip(['title', 'content', 'files', 'uId', 'uName', 'createTime']));
            $this->db->transStart();
            $this->db->table('note')->insert($info);
            $data['id'] = $this->db->insertID();
            $data['isDisable'] = 0;
            $this->myservice->log('新增了私人备忘，标题：' . $data['title']);
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
        if (!$this->myservice->verifyRight('SYS_DEFAULT')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Note/update 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $data = $this->validform($data);
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            $info = array_intersect_key($data, array_flip(['title', 'content', 'files']));
            $this->db->transStart();
            $this->db->table('note')->where(['id' => $data['id']])->update($info);
            $this->myservice->log('编辑了私人备忘，标题：' . $data['title']);
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
        if (!$this->myservice->verifyRight('SYS_DEFAULT')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Note/delete 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('note')->where(['id' => $id])->get()->getRowArray();
        if ($data) {
            if ($data['uId'] != $this->myerp['uId']) {
                return $this->response->setJSON(['status' => -1, 'msg' => '不能删除别人的私人备忘！']);
            }
            $this->db->transStart();
            $this->db->table('note')->where(['id' => $id])->update(['isDelete' => 1]);
            $this->myservice->log('删除了私人备忘，标题：' . $data['title']);
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
        if (!$this->myservice->verifyRight('SYS_DEFAULT')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Note/disable 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $isDisable = intval($this->request->getGetPost('isDisable'));
        $data = $this->db->table('note')->where(['id' => $id])->get()->getRowArray();
        if ($data) {
            $this->db->transStart();
            $this->db->table('note')->where(['id' => $id])->update(['isDisable' => $isDisable]);
            $this->myservice->log('将私人备忘（标题：'.$data['title'].'）的状态标记为' . ($isDisable > 0 ? '不再提醒' : '继续提醒'));
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '操作成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    //上传私人备忘文件
    public function uploadFile()
    {
        if (!$this->myservice->verifyRight('SYS_DEFAULT')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Note/uploadFile 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        set_time_limit(0);
        session_write_close();
        if (empty($_FILES)){
            return $this->response->setJSON(['status' => -1, 'msg' => '没有上传文件！']);
        };
        $uppath = 'data/upfile/note/file/';
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
        $this->myservice->log('上传了私人备忘文件：' . $_FILES['file']['name']);
        return $this->response->setJSON(['status' => 200, 'msg' => '提交成功！', 'data' => ['name' => $_FILES['file']['name'], 'url' => $target_file]]);
    }
    //数据验证
    private function validform(array $data)
    {
        $data['uId'] = $this->myerp['uId'];
        $data['uName'] = $this->myerp['uName'];
        $data['createTime'] = date('Y-m-d H:i:s');
        $data['files'] = strlen($data['files']) < 10 ? '' : $data['files'];
        return $data;
    }
}
