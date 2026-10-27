<?php

namespace App\Controllers\Base;
use App\Controllers\BaseController;

class Log extends BaseController
{
    // 日志列表
    public function list()
    {
        $user = $this->request->getGetPost('user');
        if (($user == '' || $user != $this->myerp['uName']) && !$this->myservice->verifyRight('SYS_LOG_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Log/list 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');
        $skey = $this->request->getGetPost('skey');

        $builder = $this->db->table('log');
        if ($user) {
            $builder->where('name', $user);
        }
        if ($beginDate) {
            $builder->where('createTime >=', $beginDate);
        }
        if ($endDate) {
            $builder->where('createTime <=', $endDate);
        }
        if ($skey !== '' && $skey !== null) {
            $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
            foreach ($keywords as $kw) {
                $builder->groupStart()
                    ->where('name', $kw)
                    ->orLike('log', $this->db->escapeLikeString($kw))
                ->groupEnd();
            }
        }
        $builder->where('isDelete', 0);
        $allowedSort = ['id','name','log','createTime'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $list = (clone $builder)->orderBy($orderField, $sord)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['name'] = $row['name'];
            $v[$arr]['log'] = $row['log'];
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
    // 回收一年前日志
    public function deleteOld()
    {
        if (!$this->myservice->verifyRight('SYS_LOG_DELETE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Log/deleteOld 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $this->db->transStart();
        $this->db->table('log')->where('createTime <', date('Y-m-d', strtotime('-1 year')) . ' 00:00:00"')->update(['isDelete' => 1]);
        $this->myservice->log('回收了一年前的系统日志');
        $this->db->transComplete();
        if ($this->db->transStatus() === false) {
            return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
        }
        return $this->response->setJSON(['status' => 200, 'msg' => '回收成功！']);
    }
}
