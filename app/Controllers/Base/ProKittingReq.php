<?php

namespace App\Controllers\Base;
use App\Controllers\BaseController;

class ProKittingReq extends BaseController
{
    // 列表
    public function list()
    {
        if (!$this->myservice->verifyRight('PROREQ_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/ProKittingReq/list 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $uId = intval($this->request->getGetPost('uId'));
        $searchType = intval($this->request->getGetPost('searchType'));
        $skey = $this->request->getGetPost('skey');
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');
        $unDone = intval($this->request->getGetPost('unDone'));
        $builder = $this->db->table('pro_kitting_req');
        $builder->where('isDelete', 0);
        if ($uId > 0) {
            $builder->where('uId', $uId);
        }
        if ($searchType == 1) {
            $builder->where('produceId', 0);
            $builder->where('description', '');
        } elseif ($searchType == 2) {
            $builder->where('description !=', '');
        }
        if ($beginDate) {
            $builder->where('createTime >=', $beginDate);
        }
        if ($endDate) {
            $builder->where('createTime <=', $endDate);
        }
        if ($unDone > 0) {
            $builder->groupStart()
                ->where('checkId', 0)
                ->orWhere('produceId', 0)
            ->groupEnd()
            ->where('description', '');
        }
        if ($skey !== '' && $skey !== null) {
            $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
            foreach ($keywords as $kw) {
                $builder->groupStart()
                    ->like('goodsText', $this->db->escapeLikeString($kw))
                    ->orLike('content', $this->db->escapeLikeString($kw))
                    ->orWhere('uName', $kw)
                    ->orWhere('checkName', $this->db->escapeLikeString($kw))
                    ->orWhere('produceName', $this->db->escapeLikeString($kw))
                ->groupEnd();
            }
        }
        if ($this->myerp['user']['department'] == '生产部') {// 生产部不让看配货申请，避免其可以判断剩余可领取任务，从而钻空子
            $total = 0;
            $v = [];
        } else {
            $allowedSort = ['id','lastDate','goodsText','qty','content','uName','createTime','checkName','produceName','produceTime','description','checkId','produceId'];
            $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
            $list = (clone $builder)->orderBy($orderField, $sord)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
            $total = $builder->countAllResults();
            $v = [];
            foreach ($list as $arr => $row) {
                $v[$arr]['id'] = intval($row['id']);
                $v[$arr]['lastDate'] = $row['lastDate'];
                $v[$arr]['goodsId'] = intval($row['goodsId']);
                $v[$arr]['goodsText'] = $row['goodsText'];
                $v[$arr]['qty'] = intval($row['qty']);
                $v[$arr]['content'] = $row['content'];
                $v[$arr]['uId'] = intval($row['uId']);
                $v[$arr]['uName'] = $row['uName'];
                $v[$arr]['createTime'] = $row['createTime'] == '0000-00-00 00:00:00' ? '' : $row['createTime'];
                $v[$arr]['checkId'] = intval($row['checkId']);
                $v[$arr]['checkName'] = $row['checkName'];
                $v[$arr]['checkTime'] = $row['checkTime'] == '0000-00-00 00:00:00' ? '' : $row['checkTime'];
                $v[$arr]['produceId'] = intval($row['produceId']);
                $v[$arr]['produceName'] = $row['produceName'];
                $v[$arr]['produceTime'] = $row['produceTime'] == '0000-00-00 00:00:00' ? '' : $row['produceTime'];
                $v[$arr]['taskId'] = intval($row['taskId']);
                $v[$arr]['description'] = $row['description'];
            }
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
        if (!$this->myservice->verifyRight('PROREQ_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/ProKittingReq/query 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('pro_kitting_req')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
        if ($data) {
            $info['id'] = $id;
            $info['lastDate'] = $data['lastDate'];
            $info['goodsId'] = intval($data['goodsId']);
            $info['goodsText'] = $data['goodsText'];
            $info['qty'] = intval($data['qty']);
            $info['content'] = $data['content'];
            $info['uId'] = intval($data['uId']);
            $info['uName'] = $data['uName'];
            $info['description'] = $data['description'];
            $info['createTime'] = $data['createTime'];
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
        if (!$this->myservice->verifyRight('PROREQ_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/ProKittingReq/add 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $data = $this->validform($data);
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            if (strtotime($data['lastDate']) - strtotime($data['createTime']) < 18000) {
                return $this->response->setJSON(['status' => -1, 'msg' => '交付限期起码距申请时间5小时以上，请合理指定交付限期，切勿随意录入！']);
            }
            $info = array_intersect_key($data, array_flip(['lastDate', 'goodsId', 'goodsText', 'qty', 'content', 'uId', 'uName', 'createTime', 'description']));
            $this->db->transStart();
            $this->db->table('pro_kitting_req')->insert($info);
            $data['id'] = $this->db->insertID();
            $data['checkId'] = $data['produceId'] = 0;
            $this->myservice->log('新增了配货申请，ID：' . $data['id'] . '，产品：' . $data['goodsText']);
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
        if (!$this->myservice->verifyRight('PROREQ_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/ProKittingReq/update 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $data = $this->validform($data);
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            $oldData = $this->db->table('pro_kitting_req')->where(['id' => $data['id']])->get()->getRowArray();
            if ($oldData['produceId'] > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '已排产申请无法编辑！']);
            }
            if (strtotime($data['lastDate']) - strtotime($oldData['createTime']) < 18000) {
                return $this->response->setJSON(['status' => -1, 'msg' => '交付限期起码距申请时间5小时以上，请合理指定交付限期，切勿随意录入！']);
            }
            $this->db->transStart();
            if (!$oldData['description'] && $data['description']) {
                $this->db->table('pro_kitting_req')->where(['id' => $data['id']])->update(['checkId' => $this->myerp['uId'], 'checkName' => $this->myerp['uName'], 'checkTime' => date('Y-m-d H:i:s')]);
            }
            if ($oldData['description'] && !$data['description']) {
                $this->db->table('pro_kitting_req')->where(['id' => $data['id']])->update(['checkId' => 0, 'checkName' => '', 'checkTime' => '0000-00-00 00:00:00']);
            }
            $info = array_intersect_key($data, array_flip(['lastDate', 'goodsId', 'goodsText', 'qty', 'content', 'description']));
            $this->db->table('pro_kitting_req')->where(['id' => $data['id']])->update($info);
            $this->myservice->log('编辑了配货申请，ID：' . $data['id'] . '，产品' . $data['goodsText']);
            $data = $this->db->table('pro_kitting_req')->where(['id' => $data['id']])->get()->getRowArray();
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
        if (!$this->myservice->verifyRight('PROREQ_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/ProKittingReq/delete 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('pro_kitting_req')->where(['id' => $id])->get()->getRowArray();
        if ($data) {
            if ($data['uId'] != $this->myerp['uId']) {
                return $this->response->setJSON(['status' => -1, 'msg' => '不能删除别人的配货申请！']);
            }
            if ($data['checkId'] > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '已审批申请无法删除！']);
            }
            if ($data['produceId'] > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '已排产申请无法删除！']);
            }
            $this->db->transStart();
            $this->db->table('pro_kitting_req')->where(['id' => $id])->update(['isDelete' => 1]);
            $this->myservice->log('删除了配货申请，ID: ' . $id . '，产品：' . $data['goodsText']);
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
        if (!$this->myservice->verifyRight('PROREQ_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/ProKittingReq/check 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $checkId = intval($this->request->getGetPost('checkId'));
        $checkName = $this->request->getGetPost('checkName');
        $checkTime = $checkId > 0 ? date('Y-m-d H:i:s') : '0000-00-00 00:00:00';
        if ($id > 0) {
            $this->db->transStart();
            $this->db->table('pro_kitting_req')->where(['id' => $id])->update(['checkId ' => $checkId, 'checkName' => $checkName, 'checkTime' => $checkTime]);
            $data = $this->db->table('pro_kitting_req')->where(['id' => $id])->get()->getRowArray();
            $this->myservice->log(($checkId > 0 ? '审批了' : '反审批了') . '配货申请，ID：' . $id . '，产品：' . $data['goodsText']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '操作成功！', 'data' => $data]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    // 数据验证
    private function validform(array $data)
    {
        $goods = $this->db->table('goods')->where(['id' => $data['goodsId']])->get()->getRowArray();
        if (!$goods || $goods['isDelete'] == 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '产品不存在！']);
        }
        if ($goods['isDisable'] == 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '将停产不再生产！']);
        }
        if ($goods['isDisable'] == 2) {
            return $this->response->setJSON(['status' => -1, 'msg' => '已停产不再生产！']);
        }
        if ($goods['isDisable'] == 9) {
            return $this->response->setJSON(['status' => -1, 'msg' => '已禁用不再生产！']);
        }
        $kitCids_arr = array_column($this->db->table('category')->select('id')->where('FIND_IN_SET("245", path) >', 0, false)->where('isDelete', 0)->get()->getResultArray(), 'id');
        if (!in_array($goods['categoryId'], $kitCids_arr)) {
            return $this->response->setJSON(['status' => -1, 'msg' => '请申请基础套件！']);
        }
        $data['uId'] = $data['uId'] ?? $this->myerp['uId'];
        $data['uName'] = $data['uName'] ?? $this->myerp['uName'];
        $data['createTime'] = date('Y-m-d H:i:s');
        $data['description'] = $data['description'] ?? '';
        return $data;
    }
}
