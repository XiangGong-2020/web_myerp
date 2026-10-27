<?php

namespace App\Controllers\Base;
use App\Controllers\BaseController;

class WorkRecord extends BaseController
{
    // 列表
    public function list()
    {
        if (!$this->myservice->verifyRight('WORKRECORD_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/WorkRecord/list 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $type = intval($this->request->getGetPost('type'));
        $targetName = $this->request->getGetPost('targetName');
        $skey = $this->request->getGetPost('skey');
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');
        $builder = $this->db->table('work_record');
        $builder->where('isDelete', 0);
        if ($type >= 0) {
            $builder->where('type', $type);
        }
        if ($beginDate) {
            $builder->where('targetDate >=', $beginDate);
        }
        if ($endDate) {
            $builder->where('targetDate <=', $endDate);
        }
        if ($targetName !== '' && $targetName !== null) {
            $keywords = array_values(array_filter(explode(',', $targetName), 'strlen'));
            foreach ($keywords as $kw) {
                $builder->like('targetName', $this->db->escapeLikeString($kw));
            }
        }
        if ($skey !== '' && $skey !== null) {
            $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
            foreach ($keywords as $kw) {
                $builder->groupStart()
                    ->like('content', $this->db->escapeLikeString($kw))
                    ->orLike('targetName', $this->db->escapeLikeString($kw))
                    ->orLike('uName', $this->db->escapeLikeString($kw))
                ->groupEnd();
            }
        }
        $allowedSort = ['id','type','targetDate','targetName','targetAmount','content','uName','createTime'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $list = (clone $builder)->orderBy($orderField, $sord)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['type'] = intval($row['type']);
            $v[$arr]['targetDate'] = $row['targetDate'];
            $v[$arr]['targetName'] = $row['targetName'];
            $v[$arr]['targetAmount'] = (float)abs($row['targetAmount']);
            $v[$arr]['content'] = $row['content'];
            $v[$arr]['uName'] = $row['uName'];
            $v[$arr]['createTime'] = $row['createTime'];
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
        if (!$this->myservice->verifyRight('WORKRECORD_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/WorkRecord/query 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('work_record')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
        if ($data) {
            $info['id'] = $id;
            $info['type'] = intval($data['type']);
            $info['targetDate'] = $data['targetDate'];
            $info['targetName'] = $data['targetName'];
            $info['targetAmount'] = abs((float)$data['targetAmount']);
            $info['content'] = $data['content'];

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
        if (!$this->myservice->verifyRight('WORKRECORD_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/WorkRecord/add 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $data = $this->validform($data);
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            $info = array_intersect_key($data, array_flip(['type', 'targetName', 'targetDate', 'targetAmount', 'content', 'uName', 'createTime']));
            $this->db->transStart();
            $this->db->table('work_record')->insert($info);
            $data['id'] = $this->db->insertID();
            $typeName = $data['type'] == 0 ? '常规记录' : ($data['type'] == 1 ? '贡献记录' : '过失记录');
            $amountText = $data['type'] == 2 ? ('，损失金额：'.$data['targetAmount']) : '';
            $this->myservice->log('新增了工作记录，编号：' . $data['id'] . '，记录类别：' . $typeName . '，发生日期：' . $data['targetDate'] . '，目标员工：' . $data['targetName'] . $amountText);
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
        if (!$this->myservice->verifyRight('WORKRECORD_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/WorkRecord/update 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $data = $this->validform($data);
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            $info = array_intersect_key($data, array_flip(['type', 'targetName', 'targetDate', 'targetAmount', 'content']));
            $this->db->transStart();
            $this->db->table('work_record')->where('id', $data['id'])->update($info);
            $typeName = $data['type'] == 0 ? '常规记录' : ($data['type'] == 1 ? '贡献记录' : '过失记录');
            $amountText = $data['type'] == 2 ? ('，损失金额：'.$data['targetAmount']) : '';
            $this->myservice->log('编辑了工作记录，编号：' . $data['id'] . '，记录类别：' . $typeName . '，发生日期：' . $data['targetDate'] . '，目标员工：' . $data['targetName'] . $amountText);
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
        if (!$this->myservice->verifyRight('WORKRECORD_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/WorkRecord/delete 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('work_record')->where('id', $id)->get()->getRowArray();
        if ($data) {
            if ($data['uName'] != $this->myerp['uName']) {
                return $this->response->setJSON(['status' => -1, 'msg' => '不能删除别人录入的工作记录！']);
            }
            $this->db->transStart();
            $this->db->table('work_record')->where('id', $id)->update(['isDelete' => 1]);
            $typeName = $data['type'] == 0 ? '常规记录' : ($data['type'] == 1 ? '贡献记录' : '过失记录');
            $amountText = $data['type'] == 2 ? ('，损失金额：'.$data['targetAmount']) : '';
            $this->myservice->log('删除了工作记录，编号：' . $id . '，记录类别：' . $typeName . '，发生日期：' . $data['targetDate'] . '，目标员工：' . $data['targetName'] . $amountText);
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
        $data['uName'] = $this->myerp['uName'];
        $data['createTime'] = date('Y-m-d H:i:s');
        return $data;
    }
}
