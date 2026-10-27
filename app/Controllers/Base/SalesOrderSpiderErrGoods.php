<?php

namespace App\Controllers\Base;
use App\Controllers\BaseController;

class SalesOrderSpiderErrGoods extends BaseController
{
    // 同步异常 列表
    public function list()
    {
        if (!$this->myservice->verifyRight('SA_SPIDER_ERR_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/SalesOrderSpiderErrGoods/list 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $skey = $this->request->getGetPost('skey');
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');
        $unDone = intval($this->request->getGetPost('unDone'));
        $builder = $this->db->table('spider_err_goods');
        $builder->where('isDelete', 0);
        if ($beginDate) {
            $builder->where('createTime >=', $beginDate . ' 00:00:00');
        }
        if ($endDate) {
            $builder->where('createTime <=', $endDate . ' 23:59:59');
        }
        if ($unDone > 0) {
            $builder->where('status', 0);
        }
        if ($skey !== '' && $skey !== null) {
            $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
            foreach ($keywords as $kw) {
                $builder->like('info', $this->db->escapeLikeString($kw));
            }
        }
        $list = (clone $builder)->orderBy('status', 'ASC')->orderBy('id', 'DESC')->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['info'] = $row['info'];
            $v[$arr]['createTime'] = $row['createTime'];
            $v[$arr]['doneName'] = $row['doneName'];
            $v[$arr]['doneTime'] = $row['doneTime'] == '0000-00-00 00:00:00' ? '' : $row['doneTime'];
            $v[$arr]['status'] = intval($row['status']);
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['page'] = $page;
        $json['data']['records'] = $total;
        $json['data']['total'] = ceil($json['data']['records'] / $rows);
        $json['data']['rows'] = $v;
        return $this->response->setJSON($json);
    }
    // 同步异常 处理
    public function status()
    {
        if (!$this->myservice->verifyRight('SA_SPIDER_ERR_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/SalesOrderSpiderErrGoods/status 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $status = intval($this->request->getGetPost('status'));
        $data = $this->db->table('spider_err_goods')->where('id', $id)->get()->getRowArray();
        if ($data) {
            $this->db->transStart();
            $this->db->table('spider_err_goods')->where('id', $id)->update(['status' => $status, 'doneName' => $this->myerp['uName'], 'doneTime' => date('Y-m-d H:i:s')]);
            $data['status'] = $status;
            $data['doneName'] = $this->myerp['uName'];
            $data['doneTime'] = date('Y-m-d H:i:s');
            $this->myservice->log('标记了产品同步异常: ID：' . $id . ' 的状态为 ' . ($status == 0 ? '未处理' : '已处理'));
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '更新失败！']);
            } else {
                return $this->response->setJSON(['status' => 200, 'msg' => '操作成功！', 'data' => $data]);
            }
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
}
