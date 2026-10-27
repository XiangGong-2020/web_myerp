<?php

namespace App\Controllers\Base;
use App\Controllers\BaseController;

class SalesOrderSpiderErrPrice extends BaseController
{
    // 售价异常 列表
    public function list()
    {
        if (!$this->myservice->verifyRight('SA_SPIDER_ERR_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/SalesOrderSpiderErrPrice/list 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $skey = $this->request->getGetPost('skey');
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');
        $unDone = intval($this->request->getGetPost('unDone'));
        $builder = $this->db->table('spider_err_price');
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
                $builder->groupStart()
                    ->like('goodsText', $this->db->escapeLikeString($kw))
                    ->orLike('info', $this->db->escapeLikeString($kw))
                ->groupEnd();
            }
        }
        $list = (clone $builder)->orderBy('status', 'ASC')->orderBy('id', 'DESC')->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['goodsId'] = intval($row['goodsId']);
            $v[$arr]['goodsText'] = $row['goodsText'];
            $shopPrices = [];
            if ($row['shopPrices']) {
                $shopPrices = json_decode($row['shopPrices'], true);
            }
            $v[$arr]['shopPrices'] = $shopPrices;
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
    // 获取shopPrices
    public function getShopPrices()
    {
        $id = intval($this->request->getGetPost('id'));
        if ($id > 0) {
            $shopPrices = $this->db->table('goods')->select('shopPrices')->where('id', $id)->get()->getRowArray()['shopPrices'] ?? '';
            if ($shopPrices) {
                $shopPrices = json_decode($shopPrices, true);
                return $this->response->setJSON(['status' => 200, 'msg' => 'success', 'data' => $shopPrices]);
            }
            return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 售价异常 处理
    public function status()
    {
        if (!$this->myservice->verifyRight('SA_SPIDER_ERR_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/SalesOrderSpiderErrPrice/status 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $status = intval($this->request->getGetPost('status'));
        $goodsId = intval($this->request->getPost('goodsId'));
        $shopPrices = $this->request->getGetPost('shopPrices');
        $data = $this->db->table('spider_err_price')->where('id', $id)->get()->getRowArray();
        if ($data) {
            $this->db->transStart();
            $this->db->table('spider_err_price')->where('id', $id)->update(['status' => $status, 'doneName' => $this->myerp['uName'], 'doneTime' => date('Y-m-d H:i:s')]);
            $this->db->table('goods')->where('id', $goodsId)->update(['shopPrices' => $shopPrices]);
            $data['status'] = $status;
            $data['doneName'] = $this->myerp['uName'];
            $data['doneTime'] = date('Y-m-d H:i:s');
            if ($data['shopPrices']) {
                $shopPrices = json_decode($data['shopPrices'], true);
            }
            $data['shopPrices'] = $shopPrices;
            $this->myservice->log('标记了产品售价异常: ID：' . $id . ' 的状态为 ' . ($status == 0 ? '未处理' : '已处理'));
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '操作成功！', 'data' => $data]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
}
