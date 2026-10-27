<?php

namespace App\Controllers\Base;
use App\Controllers\BaseController;

class PurQC extends BaseController
{
    // 待检列表
    public function list()
    {
        if (!$this->myservice->verifyRight('PURQC_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/PurQC/list 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $status1 = $this->request->getGetPost('status1');
        $status2 = $this->request->getGetPost('status2');
        $status3 = intval($this->request->getGetPost('status3'));
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');
        $skey = $this->request->getGetPost('skey');
        $builder = $this->db->table('bill_inventory_log AS a')
            ->select('a.*, b.no AS invNo, b.name AS invName, b.spec AS invSpec, b.sku AS invSku, b.brandName, b.unitName, c.id AS storageId, c.name AS storageName')
            ->join('goods AS b', 'a.goodsId = b.id AND b.isDelete = 0', 'left')
            ->join('storage AS c', 'a.storageId = c.id AND c.isDelete = 0', 'left');
        $builder->where('transType', 102101);
        if ($status1) {
            $builder->where('a.uName', $status1);
        }
        if ($status2) {
            $builder->where('a.QCName', $status2);
        }
        if (0 <= $status3) {
            $builder->where('QCStatus', $status3);
        }
        if ($beginDate) {
            $builder->where('a.createTime >=', $beginDate);
        }
        if ($endDate) {
            $builder->where('a.createTime <=', $endDate);
        }
        if ($skey !== '' && $skey !== null) {
            $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
            foreach ($keywords as $kw) {
                $builder->groupStart()
                    ->like('b.no', $this->db->escapeLikeString($kw))
                    ->orLike('b.name', $this->db->escapeLikeString($kw))
                    ->orLike('b.spec', $this->db->escapeLikeString($kw))
                    ->orLike('b.sku', $this->db->escapeLikeString($kw))
                    ->orLike('a.uName', $this->db->escapeLikeString($kw))
                    ->orLike('a.QCName', $this->db->escapeLikeString($kw))
                    ->orLike('a.deliverCompany', $this->db->escapeLikeString($kw))
                    ->orLike('a.deliverNo', $this->db->escapeLikeString($kw))
                ->groupEnd();
            }
        }
        $builder->where('a.isDelete', 0);
        $allowedSort = ['id','createTime','uName','qty','goodsId','deliverNo','srcOrderId','iid','QCName','QCTime'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $list = (clone $builder)->orderBy($orderField, $sord)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['iid'] = intval($row['iid']);
            $v[$arr]['srcOrderId'] = intval($row['srcOrderId']);
            $v[$arr]['uName'] = $row['uName'];
            $v[$arr]['goodsId'] = intval($row['goodsId']);
            $v[$arr]['invNo'] = $row['invNo'];
            $v[$arr]['invName'] = $row['invName'];
            $v[$arr]['invSpec'] = $row['invSpec'];
            $v[$arr]['invSku'] = $row['invSku'];
            $v[$arr]['brandName'] = $row['brandName'];
            $v[$arr]['unitName'] = $row['unitName'];
            $v[$arr]['storageName'] = $row['storageName'];
            $v[$arr]['qty'] = intval($row['qty']);
            $v[$arr]['deliverCompany'] = $row['deliverCompany'];
            $v[$arr]['deliverNo'] = $row['deliverNo'];
            $v[$arr]['QCName'] = $row['QCName'];
            $v[$arr]['QCTime'] = $row['QCTime'] == '0000-00-00 00:00:00' ? '' : $row['QCTime'];
            $v[$arr]['QCStatus'] = intval($row['QCStatus']);
            $v[$arr]['backStatus'] = intval($row['backStatus']);
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
    // 质检
    public function qc()
    {
        if (!$this->myservice->verifyRight('PURQC_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/PurQC/qc 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $goods = $this->request->getGetPost('goods');
        $QCStatus = intval($this->request->getGetPost('QCStatus'));
        $QCName = $this->request->getGetPost('QCName');
        $QCTime = $this->request->getGetPost('QCTime');
        $data = $this->db->table('bill_inventory_log')->where('id', $id)->get()->getRowArray();
        if ($data) {
            if ($QCStatus == 0 && $data['backStatus'] > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '打回已被采购人员处理，无法重置质检状态！']);
            }
            $this->db->transStart();
            $this->db->table('bill_inventory_log')->where('id', $id)->update(['QCStatus' => $QCStatus, 'QCName' => $QCName, 'QCTime' => $QCTime]);
            $this->myservice->log('对 '. $data['createTime'] . ' ' . $data['uName'] . ' 收货的 ' . $goods . ' 进行了 ' . ($QCStatus == 0 ? '重置' : ($QCStatus == 1 ? '不通过' : '通过')) . ' 质检操作');
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '操作成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }

    // 打回列表
    public function backList()
    {
        if (!$this->myservice->verifyRight('PURQCBACK_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/PurQC/backList 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $status1 = $this->request->getGetPost('status1');
        $status2 = $this->request->getGetPost('status2');
        $status3 = $this->request->getGetPost('status3');
        $status4 = intval($this->request->getGetPost('status4'));
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');
        $skey = $this->request->getGetPost('skey');
        $builder = $this->db->table('bill_inventory_log AS a')
            ->select('a.*, b.no AS invNo, b.name AS invName, b.spec AS invSpec, b.sku AS invSku, b.brandName, b.unitName, c.id AS storageId, c.name AS storageName')
            ->join('goods AS b', 'a.goodsId = b.id AND b.isDelete = 0', 'left')
            ->join('storage AS c', 'a.storageId = c.id AND c.isDelete = 0', 'left');
        $builder->where(['a.transType' => 102101, 'a.QCStatus' => 1]);
        if ($status1) {
            $builder->where('a.uName', $status1);
        }
        if ($status2) {
            $builder->where('a.QCName', $status2);
        }
        if ($status3) {
            $builder->where('a.backName', $status3);
        }
        if ($status4 >= 0) {
            $builder->where('a.backStatus', $status4);
        }
        if ($beginDate) {
            $builder->where('a.createTime >=', $beginDate);
        }
        if ($endDate) {
            $builder->where('a.createTime <=', $endDate);
        }
        if ($skey !== '' && $skey !== null) {
            $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
            foreach ($keywords as $kw) {
                $builder->groupStart()
                    ->like('b.no', $this->db->escapeLikeString($kw))
                    ->orLike('b.name', $this->db->escapeLikeString($kw))
                    ->orLike('b.spec', $this->db->escapeLikeString($kw))
                    ->orLike('b.sku', $this->db->escapeLikeString($kw))
                    ->orLike('a.uName', $this->db->escapeLikeString($kw))
                    ->orLike('a.QCName', $this->db->escapeLikeString($kw))
                    ->orLike('a.deliverCompany', $this->db->escapeLikeString($kw))
                    ->orLike('a.deliverNo', $this->db->escapeLikeString($kw))
                ->groupEnd();
            }
        }
        $builder->where('a.isDelete', 0);
        $allowedSort = ['id','createTime','uName','qty','goodsId','deliverNo','srcOrderId','iid','QCName','QCTime'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $list = (clone $builder)->orderBy($orderField, $sord)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['iid'] = intval($row['iid']);
            $v[$arr]['srcOrderId'] = intval($row['srcOrderId']);
            $v[$arr]['uName'] = $row['uName'];
            $v[$arr]['goodsId'] = intval($row['goodsId']);
            $v[$arr]['invNo'] = $row['invNo'];
            $v[$arr]['invName'] = $row['invName'];
            $v[$arr]['invSpec'] = $row['invSpec'];
            $v[$arr]['invSku'] = $row['invSku'];
            $v[$arr]['brandName'] = $row['brandName'];
            $v[$arr]['unitName'] = $row['unitName'];
            $v[$arr]['storageName'] = $row['storageName'];
            $v[$arr]['qty'] = intval($row['qty']);
            $v[$arr]['deliverCompany'] = $row['deliverCompany'];
            $v[$arr]['deliverNo'] = $row['deliverNo'];
            $v[$arr]['QCName'] = $row['QCName'];
            $v[$arr]['QCTime'] = $row['QCTime'] == '0000-00-00 00:00:00' ? '' : $row['QCTime'];
            $v[$arr]['QCStatus'] = intval($row['QCStatus']);
            $v[$arr]['backName'] = $row['backName'];
            $v[$arr]['backTime'] = $row['backTime'] == '0000-00-00 00:00:00' ? '' : $row['backTime'];
            $v[$arr]['backStatus'] = intval($row['backStatus']);
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
    // 处理
    public function back()
    {
        if (!$this->myservice->verifyRight('PURQCBACK_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/PurQC/back 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $goods = $this->request->getGetPost('goods');
        $backStatus = intval($this->request->getGetPost('backStatus'));
        $backName = $this->request->getGetPost('backName');
        $backTime = $this->request->getGetPost('backTime');
        $data = $this->db->table('bill_inventory_log')->where('id', $id)->get()->getRowArray();
        if ($data) {
            if ($backStatus > 0 && $data['QCStatus'] != 1) {
                return $this->response->setJSON(['status' => -1, 'msg' => '打回已被取消，无法处理！请刷新！']);
            }
            $this->db->transStart();
            $this->db->table('bill_inventory_log')->where('id', $id)->update(['backStatus' => $backStatus, 'backName' => $backName, 'backTime' => $backTime]);
            $this->myservice->log('对 '. $data['QCTime'] . ' ' . $data['QCName'] . ' 打回的 ' . $goods . ' 的质检进行了 ' . ($backStatus == 0 ? '重置' : ($backStatus == 1 ? '退货处理' : '无需退货 作其他处理')) . ' 标记操作');
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '操作成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
}
