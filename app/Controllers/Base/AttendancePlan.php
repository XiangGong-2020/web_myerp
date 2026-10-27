<?php

namespace App\Controllers\Base;
use App\Controllers\BaseController;

class AttendancePlan extends BaseController
{
    // 列表
    public function list()
    {
        if (!$this->myservice->verifyRight('SYS_ADMIN')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/AttendancePlan/list 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max((int)$this->request->getGetPost('page'), 1);
        $rows = max((int)$this->request->getGetPost('rows'), 100);
        $builder = $this->db->table('attendance_plan')->where('isDelete', 0);
        $list = (clone $builder)->orderBy('year', 'DESC')->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['year'] = $row['year'];
            $v[$arr]['m1'] = intval($row['m1']);
            $v[$arr]['m2'] = intval($row['m2']);
            $v[$arr]['m3'] = intval($row['m3']);
            $v[$arr]['m4'] = intval($row['m4']);
            $v[$arr]['m5'] = intval($row['m5']);
            $v[$arr]['m6'] = intval($row['m6']);
            $v[$arr]['m7'] = intval($row['m7']);
            $v[$arr]['m8'] = intval($row['m8']);
            $v[$arr]['m9'] = intval($row['m9']);
            $v[$arr]['m10'] = intval($row['m10']);
            $v[$arr]['m11'] = intval($row['m11']);
            $v[$arr]['m12'] = intval($row['m12']);
            $v[$arr]['description'] = $row['description'];
            $v[$arr]['uId'] = intval($row['uId']);
            $v[$arr]['uName'] = $row['uName'];
            $v[$arr]['createTime'] = $row['createTime'] == '0000-00-00 00:00:00' ? '' : $row['createTime'];
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
        if (!$this->myservice->verifyRight('SYS_ADMIN')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/AttendancePlan/query 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = (int)$this->request->getGetPost('id');
        if($id > 0) {
            $data = $this->db->table('attendance_plan')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
            if ($data) {
                return $this->response->setJSON(['status' => 200, 'msg' => 'success', 'data' => $data]);
            }
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    //新增
    public function add()
    {
        if (!$this->myservice->verifyRight('SYS_ADMIN')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/AttendancePlan/add 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $data = $this->validform($data);
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            $info = array_intersect_key($data, array_flip(['year', 'm1', 'm2', 'm3', 'm4', 'm5', 'm6', 'm7', 'm8', 'm9', 'm10', 'm11', 'm12', 'description', 'uId', 'uName', 'createTime']));
            $this->db->transStart();
            $this->db->table('attendance_plan')->insert($info);
            $data['id'] = $this->db->insertID();
            $this->myservice->log('新增了' . $data['year'] . '年的出勤规划');
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
        if (!$this->myservice->verifyRight('SYS_ADMIN')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/AttendancePlan/update 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $data = $this->validform($data);
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            $info = array_intersect_key($data, array_flip(['year', 'm1', 'm2', 'm3', 'm4', 'm5', 'm6', 'm7', 'm8', 'm9', 'm10', 'm11', 'm12', 'description', 'uId', 'uName', 'createTime']));
            $this->db->transStart();
            $this->db->table('attendance_plan')->where('id', $data['id'])->update($info);
            $this->myservice->log('编辑了' . $data['year'] . '年的出勤规划');
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
        if (!$this->myservice->verifyRight('SYS_ADMIN')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/AttendancePlan/delete 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = (int)$this->request->getGetPost('id');
        $data = $this->db->table('attendance_plan')->where('id', $id)->get()->getRowArray();
        if ($data) {
            $this->db->transStart();
            $this->db->table('attendance_plan')->where('id', $id)->update(['isDelete' => 1]);
            $this->myservice->log('删除了' . $data['year'] . '年的出勤规划');
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
        if ($data['year'] < 2024) {
            return $this->response->setJSON(['status' => -1, 'msg' => '年份错误！']);
        }
        if ($this->db->table('attendance_plan')->where(['id !=' => $data['id'], 'year' => $data['year'], 'isDelete' => 0])->countAllResults() > 0) {
            return $this->response->setJSON(['status' => -1, 'msg' => '年份重复！']);
        }
        $data['uId'] = $this->myerp['uId'];
        $data['uName'] = $this->myerp['uName'];
        $data['createTime'] = date('Y-m-d H:i:s');
        return $data;
    }
}
