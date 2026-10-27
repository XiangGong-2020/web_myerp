<?php

namespace App\Controllers\Base;
use App\Controllers\BaseController;

class ReviewHold extends BaseController
{
    // 列表
    public function list()
    {
        if (!$this->myservice->verifyRight('SERVICE_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/ReviewHold/list 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $billfromId = intval($this->request->getGetPost('billfromId'));
        $isDisable = intval($this->request->getGetPost('isDisable'));
        $uName = $this->request->getGetPost('uName');
        $skey = $this->request->getGetPost('skey');
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');
        $builder = $this->db->table('review_hold');
        $builder->where('isDelete', 0);
        if ($billfromId > 0) {
            $builder->where('billfromId', $billfromId);
        }
        if ($isDisable >= 0) {
            $builder->where('isDisable', $isDisable);
        }
        if ($uName) {
            $builder->where('uName', $uName);
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
                    ->like('orderNo', $this->db->escapeLikeString($kw))
                    ->orWhere('uName', $kw)
                    ->orLike('description', $this->db->escapeLikeString($kw))
                ->groupEnd();
            }
        }
        $allowedSort = ['id','billfromId','orderId','orderNo','level','description','uName','createTime','isDisable','disableTime'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $list = (clone $builder)->orderBy($orderField, $sord)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['billfromId'] = intval($row['billfromId']);
            $v[$arr]['orderId'] = intval($row['orderId']);
            $v[$arr]['orderNo'] = $row['orderNo'];
            $v[$arr]['level'] = intval($row['level']);
            $v[$arr]['description'] = $row['description'];
            $v[$arr]['uId'] = intval($row['uId']);
            $v[$arr]['uName'] = $row['uName'];
            $v[$arr]['createTime'] = $row['createTime'];
            $v[$arr]['isDisable'] = intval($row['isDisable']);
            $v[$arr]['disableTime'] = $row['disableTime'];
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['page'] = $page;
        $json['data']['records'] = $total;
        $json['data']['total'] = ceil($json['data']['records'] / $rows);
        $json['data']['rows'] = $v;
        return $this->response->setJSON($json);
    }
    // 获取信息
    public function query()
    {
        if (!$this->myservice->verifyRight('SERVICE_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/ReviewHold/query 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('review_hold')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
        if ($data) {
            $info['id'] = $id;
            $info['billfromId'] = intval($data['billfromId']);
            $info['orderId'] = intval($data['orderId']);
            $info['orderNo'] = $data['orderNo'];
            $info['level'] = intval($data['level']);
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
        if (!$this->myservice->verifyRight('SERVICE_ADD')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/ReviewHold/add 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $data = $this->validform($data);
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            $info = array_intersect_key($data, array_flip(['billfromId', 'orderId', 'orderNo', 'level', 'description', 'uId', 'uName', 'createTime']));
            $this->db->transStart();
            $this->db->table('review_hold')->insert($info);
            $data['id'] = $this->db->insertID();
            $data['isDisable'] = 0;
            $this->myservice->log('新增了差评删改，编号：' . $data['id'] . '，订单号：' . $data['orderNo']);
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
        if (!$this->myservice->verifyRight('SERVICE_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/ReviewHold/update 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $data = $this->validform($data);
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            $info = array_intersect_key($data, array_flip(['billfromId', 'orderId', 'orderNo', 'level', 'description']));
            $this->db->transStart();
            $this->db->table('review_hold')->where(['id' => $data['id']])->update($info);
            $this->myservice->log('编辑了差评删改， 编号：' . $data['id'] . '，订单号：' . $data['orderNo']);
            $data = $this->db->table('review_hold')->where('id', $data['id'])->get()->getRowArray();
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
        if (!$this->myservice->verifyRight('SERVICE_DELETE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/ReviewHold/delete 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('review_hold')->where(['id' => $id])->get()->getRowArray();
        if ($data) {
            if ($data['uId'] != $this->myerp['uId']) {
                return $this->response->setJSON(['status' => -1, 'msg' => '不能删除别人录入的差评删改！']);
            }
            $this->db->transStart();
            $this->db->table('review_hold')->where(['id' => $id])->update(['isDelete' => 1]);
            $this->myservice->log('删除了差评删改，编号：' . $id . '，订单号：' . $data['orderNo']);
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
        if (!$this->myservice->verifyRight('SERVICE_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/ReviewHold/disable 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $isDisable = intval($this->request->getGetPost('isDisable'));
        $disableTime = date('Y-m-d H:i:s');
        if ($id > 0) {
            $this->db->transStart();
            $this->db->table('review_hold')->where(['id' => $id])->update(['isDisable' => $isDisable, 'disableTime' => $disableTime]);
            $data = $this->db->table('review_hold')->where(['id' => $id])->get()->getRowArray();
            $this->myservice->log('将编号：' . $id . '，订单号：' . $data['orderNo'] . ' 的差评删改的处理状态标记为' . ($isDisable == 0 ? '未处理' : ($isDisable == 1 ? '已处理' : '已放弃')));
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '操作成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 数据验证
    private function validform(array $data)
    {
        if ($data['billfromId'] <= 0) {
            return $this->response->setJSON(['status' => -1, 'msg' => '渠道店铺不允许为空！']);
        }
        if (strlen($data['orderNo']) == 0) {
            return $this->response->setJSON(['status' => -1, 'msg' => '订单号不允许为空！']);
        }
        if ($data['level'] < 0) {
            return $this->response->setJSON(['status' => -1, 'msg' => '差评等级不允许为空！']);
        }
        if (strlen($data['orderNo']) > 0 && (strlen($data['orderNo']) < 10 || strlen($data['orderNo']) > 50) && !preg_match('/[^A-Za-z0-9-]/', $data['orderNo'])) {
            return $this->response->setJSON(['status' => -1, 'msg' => '订单号格式异常，请准确输入！']);
        }
        if (strlen($data['orderNo']) > 0) {
            $data['orderId'] = $this->db->table('bill_order')->select('id')->where(['billNo' => $data['orderNo'], 'isDelete' => 0])->get()->getRow('id') ?? 0;
        } else {
            $data['orderId'] = 0;
        }
        $data['uId'] = $this->myerp['uId'];
        $data['uName'] = $this->myerp['uName'];
        $data['createTime'] = date('Y-m-d H:i:s');
        return $data;
    }
}
