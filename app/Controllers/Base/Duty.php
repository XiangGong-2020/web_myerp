<?php

namespace App\Controllers\Base;
use App\Controllers\BaseController;

class Duty extends BaseController
{
    //列表
    public function list()
    {
        if (!$this->myservice->verifyRight('DUTY_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Duty/list 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $level = intval($this->request->getGetPost('level'));
        $isDisable = intval($this->request->getGetPost('isDisable'));
        $skey = $this->request->getGetPost('skey');
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');
        $unDone = intval($this->request->getGetPost('unDone'));
        $builder = $this->db->table('duty')->where('isDelete', 0);
        if (!in_array('SYS_ADMIN', $this->myerp['rights'])) {
            $builder->groupStart()
                ->where('beginName', $this->myerp['uName'])
                ->orWhere('FIND_IN_SET(' . $this->db->escape($this->myerp['uName']) . ', targetName) >', 0, false)
                ->orWhere('FIND_IN_SET(' . $this->db->escape($this->myerp['user']['department']) . ', targetName) >', 0, false)
            ->groupEnd();
        }
        if ($unDone > 0 && in_array('SYS_ADMIN', $this->myerp['rights'])) {
            $builder->groupStart()
                ->where('beginName', $this->myerp['uName'])
                ->orWhere('FIND_IN_SET(' . $this->db->escape($this->myerp['uName']) . ', targetName) >', 0, false)
                ->orWhere('FIND_IN_SET(' . $this->db->escape($this->myerp['user']['department']) . ', targetName) >', 0, false)
            ->groupEnd();
        }
        if ($level > -1) {
            $builder->where('level', $level);
        }
        if ($isDisable > -1) {
            $builder->where('isDisable', $isDisable);
        }
        if ($beginDate) {
            $builder->where('lastDate >=', $beginDate);
        }
        if ($endDate) {
            $builder->where('lastDate <=', $endDate);
        }
        if ($skey !== '' && $skey !== null) {
            $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
            foreach ($keywords as $kw) {
                $builder->groupStart()
                    ->like('content', $this->db->escapeLikeString($kw))
                    ->orLike('backinfo', $this->db->escapeLikeString($kw))
                    ->orWhere('beginName', $kw)
                    ->orWhere('endName', $kw)
                    ->orWhere('FIND_IN_SET(' . $this->db->escape($kw) . ', targetName) >', 0, false)
                ->groupEnd();
            }
        }
        $allowedSort = ['id','targetName','level','lastDate','content','backinfo','beginDate','beginName','endDate','endName','isDisable'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $list = (clone $builder)->orderBy($orderField, $sord)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['targetName'] = $row['targetName'];
            $v[$arr]['level'] = intval($row['level']);
            $v[$arr]['lastDate'] = $row['lastDate'];
            $v[$arr]['content'] = $row['content'];
            $v[$arr]['backinfo'] = $row['backinfo'];
            $v[$arr]['beginDate'] = $row['beginDate'];
            $v[$arr]['beginName'] = $row['beginName'];
            $v[$arr]['endDate'] = $row['endDate'];
            $v[$arr]['endName'] = $row['endName'];
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
    //获取信息
    public function query()
    {
        if (!$this->myservice->verifyRight('DUTY_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Duty/query 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('duty')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
        if ($data) {
            $info['id'] = $id;
            $info['targetName'] = $data['targetName'];
            $info['level'] = intval($data['level']);
            $info['lastDate'] = $data['lastDate'];
            $info['content'] = $data['content'];
            $info['backinfo'] = $data['backinfo'];
            $info['beginDate'] = $data['beginDate'];
            $info['beginName'] = $data['beginName'];
            $info['endDate'] = $data['endDate'];
            $info['endName'] = $data['endName'];
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
        if (!$this->myservice->verifyRight('DUTY_ADD')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Duty/add 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $data = $this->validform($data);
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            $data['beginDate'] = date('Y-m-d H:i:s');
            $info = array_intersect_key($data, array_flip(['targetName', 'level', 'lastDate', 'isDisable', 'content', 'beginName', 'beginDate']));
            $this->db->transStart();
            $sql = $this->db->table('duty')->insert($info);
            $data['id'] = $this->db->insertID();
            $data['isDisable'] = 0;
            $this->myservice->log('新增了工作转交，ID：' . $data['id']);
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
        if (!$this->myservice->verifyRight('DUTY_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Duty/update 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $data = $this->validform($data);
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            $info = array_intersect_key($data, array_flip(['targetName', 'level', 'lastDate', 'isDisable', 'content']));
            $this->db->transStart();
            $this->db->table('duty')->where('id', $data['id'])->update($info);
            $this->myservice->log('编辑了工作转交，ID：' . $data['id']);
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
        if (!$this->myservice->verifyRight('DUTY_DELETE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Duty/delete 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('duty')->where('id', $id)->get()->getRowArray();
        if ($data) {
            if ($data['beginName'] != $this->myerp['uName']) {
                return $this->response->setJSON(['status' => -1, 'msg' => '不能删除别人发起的工作转交！']);
            }
            $this->db->transStart();
            $this->db->table('duty')->where('id', $id)->update(['isDelete' => 1]);
            $this->myservice->log('删除了工作转交，ID: ' . $id);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '删除成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    //状态
    public function disable()
    {
        if (!$this->myservice->verifyRight('DUTY_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Duty/disable 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $isDisable = intval($this->request->getGetPost('isDisable'));
        $endDate = date('Y-m-d H:i:s');
        $endName = $this->myerp['uName'];
        if ($id > 0) {
            $this->db->transStart();
            $this->db->table('duty')->where('id', $id)->update(['isDisable' => $isDisable, 'endName' => $endName, 'endDate' => $endDate]);
            $this->myservice->log('将工作转交ID：' . $id . ' 的处理状态标记为' . ($isDisable > 0 ? '已处理' : '未处理'));
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '处理成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    //数据验证
    private function validform(array $data)
    {
        $data['beginName'] = $this->myerp['uName'];
        $data['endName'] = $this->myerp['uName'];
        $data['level'] = $data['level'] ?? 0;
        $data['isDisable'] = $data['isDisable'] ?? 0;
        return $data;
    }
}
