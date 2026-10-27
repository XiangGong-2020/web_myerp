<?php

namespace App\Controllers\Base;
use App\Controllers\BaseController;

class WorkHour extends BaseController
{
    // 列表
    public function list()
    {
        if (!$this->myservice->verifyRight('WORKHOUR_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/WorkHour/list 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $type = intval($this->request->getGetPost('type'));
        $uName = $this->request->getGetPost('uName');
        $isCheck = intval($this->request->getGetPost('isCheck'));
        $skey = $this->request->getGetPost('skey');
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');
        $builder = $this->db->table('work_hour');
        $builder->where('isDelete', 0);
        if ($type >= 0) {
            $builder->where('type', $type);
        }
        if ($beginDate) {
            $builder->where('workDate >=', $beginDate);
        }
        if ($endDate) {
            $builder->where('workDate <=', $endDate);
        }
        if ($uName) {
            $builder->where('uName', $uName);
        }
        if ($isCheck > -1) {
            $builder->where('isCheck', $isCheck);
        }
        if ($skey !== '' && $skey !== null) {
            $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
            foreach ($keywords as $kw) {
                $builder->like('description', $this->db->escapeLikeString($kw));
            }
        }
        $allowedSort = ['id','type','uName','workDate','isCheck'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $builder->orderBy('workDate', 'DESC');
        $builder->orderBy($orderField, $sord);
        $list = (clone $builder)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        $weekarray = ["日", "一", "二", "三", "四", "五", "六"];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['type'] = intval($row['type']);
            $v[$arr]['uName'] = $row['uName'];
            $v[$arr]['workDate'] = $row['workDate'];
            $v[$arr]['weekday'] = "周" . $weekarray[date("w", strtotime($row['workDate']))];
            $v[$arr]['workHours'] = (float)$row['workHours'];
            $v[$arr]['description'] = $row['description'];
            $v[$arr]['createTime'] = $row['createTime'];
            $v[$arr]['isCheck'] = intval($row['isCheck']);
            $v[$arr]['checkName'] = $row['checkName'];
            $v[$arr]['checkTime'] = $row['checkTime'] == '0000-00-00 00:00:00' ? '' : $row['checkTime'];
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
        if (!$this->myservice->verifyRight('WORKHOUR_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/WorkHour/query 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('work_hour')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
        if ($data) {
            $info['id'] = intval($data['id']);
            $info['type'] = intval($data['type']);
            $info['uName'] = $data['uName'];
            $info['workDate'] = $data['workDate'];
            $info['workHours'] = (float)$data['workHours'];
            $info['description'] = $data['description'];

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
        if (!$this->myservice->verifyRight('WORKHOUR_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/WorkHour/add 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $data = $this->validform($data);
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            if ($data['uName'] && $data['workDate']) {
                if ($this->db->table('work_hour')->where(['type' => $data['type'], 'uName' => $data['uName'], 'workDate' => $data['workDate'], 'isDelete' => 0])->countAllResults() > 0) {
                     return $this->response->setJSON(['status' => -1, 'msg' => '已存在该员工当天的申报！']);
                }
                $info = array_intersect_key($data, array_flip(['type', 'uName', 'workDate', 'workHours', 'description', 'createTime']));
                $this->db->transStart();
                $this->db->table('work_hour')->insert($info);
                $data['id'] = $this->db->insertID();
                $data['isCheck'] = 0;
                $data['checkName'] = '';
                $data['checkTime'] = '';
                $weekarray = ["日", "一", "二", "三", "四", "五", "六"];
                $data['weekday'] = "周" . $weekarray[date("w", strtotime($data['workDate']))];
                $this->myservice->log('新增了 ' . $data['uName'] . ' ' . $data['workDate'] . ' 的' . ($data['type'] == 0 ? '补班' : ($data['type'] == 1 ? '加班' : '计时')) . '申报');
                $this->db->transComplete();
                if ($this->db->transStatus() === false) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
                }
                return $this->response->setJSON(['status' => 200, 'msg' => '新增成功！', 'data' => $data]);
            }
            return $this->response->setJSON(['status' => -1, 'msg' => '提交的数据不完整！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    // 编辑
    public function update()
    {
        if (!$this->myservice->verifyRight('WORKHOUR_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/WorkHour/update 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $data = $this->validform($data);
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            if ($this->db->table('work_hour')->where(['id !=' => $data['id'], 'type' => $data['type'], 'uName' => $data['uName'], 'workDate' => $data['workDate'], 'isDelete' => 0])->countAllResults() > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '已存在该员工当天的申报！']);
            }
            $info = array_intersect_key($data, array_flip(['uName', 'workDate', 'workHours', 'description']));
            $this->db->transStart();
            $this->db->table('work_hour')->where(['id' => $data['id']])->update($info);
            $data = $this->db->table('work_hour')->getWhere(['id' => $data['id']])->getRowArray();
            $this->myservice->log('编辑了 ' . $data['uName'] . ' ' . $data['workDate'] . ' 的' . ($data['type'] == 0 ? '补班' : ($data['type'] == 1 ? '加班' : '计时')) . '申报');
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '编辑成功！', 'data' => ['id' => $data['id']]]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    // 删除
    public function delete()
    {
        if (!$this->myservice->verifyRight('WORKHOUR_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/WorkHour/delete 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('work_hour')->getWhere(['id' => $id])->getRowArray();
        if ($data) {
            $this->db->transStart();
            $this->db->table('work_hour')->where(['id' => $id])->update(['isDelete' => 1]);
            $this->myservice->log('删除了 ' . $data['uName'] . ' ' . $data['workDate'] . ' 的' . ($data['type'] == 0 ? '补班' : ($data['type'] == 1 ? '加班' : '计时')) . '申报');
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '删除成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 审批
    public function check()
    {
        if (!$this->myservice->verifyRight('WORKHOUR_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/WorkHour/check 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $isCheck = intval($this->request->getGetPost('isCheck'));
        $data = $this->db->table('work_hour')->getWhere(['id' => $id])->getRowArray();
        if ($data) {
            $this->db->transStart();
            if($isCheck == 0) {
                $info = ['isCheck' => $isCheck, 'checkName' => '', 'checkTime' => '0000-00-00 00:00:00'];
                $data['isCheck'] = $isCheck;
                $data['checkName'] = '';
                $data['checkTime'] = '0000-00-00 00:00:00';
            } else {
                $info = ['isCheck' => $isCheck, 'checkName' => $this->myerp['uName'], 'checkTime' => date('Y-m-d H:i:s')];
                $data['isCheck'] = $isCheck;
                $data['checkName'] = $this->myerp['uName'];
                $data['checkTime'] = date('Y-m-d H:i:s');
            }
            $this->db->table('work_hour')->where(['id' => $id])->update($info);
            $this->myservice->log(($isCheck == 0 ? '反批了' : ($isCheck == 1 ? '批准了' : '拒绝了')) . ' ' . $data['uName'] . ' 的' . $data['workDate'] . ' 的' . ($data['type'] == 0 ? '补班' : ($data['type'] == 1 ? '加班' : '计时')) . '申报');
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '操作成功！', 'data' => $data]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 数据验证
    private function validform(array $data)
    {
        $data['id'] = isset($data['id']) ? intval($data['id']) : -1;
        $data['type'] = intval($data['type']);
        $data['workHours'] = (float)$data['workHours'];
        $data['createTime'] = date('Y-m-d H:i:s');
        return $data;
    }
}
