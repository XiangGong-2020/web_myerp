<?php

namespace App\Controllers\Base;
use App\Controllers\BaseController;

class DiscountReq extends BaseController
{
    //列表
    public function list()
    {
        if (!$this->myservice->verifyRight('SERVICE_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/DiscountReq/list 权限不足！');
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
        $builder = $this->db->table('discount_req')->where('isDelete', 0);
        if ($uId > 0) {
            $builder->where('uId', $uId);
        }
        if ($searchType == 1) {
            $builder->where('checkId', 0)->where('description', '');
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
            $builder->where('checkId', 0)->where('description', '');
        }
        if ($skey !== '' && $skey !== null) {
            $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
            foreach ($keywords as $kw) {
                $builder->groupStart()
                    ->like('content', $this->db->escapeLikeString($kw))
                    ->orLike('uName', $this->db->escapeLikeString($kw))
                    ->orLike('checkName', $this->db->escapeLikeString($kw))
                    ->orLike('description', $this->db->escapeLikeString($kw))
                ->groupEnd();
            }
        }
        $allowedSort = ['id','title','content','description','uId','uName','createTime','checkId','checkName','checkTime'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $list = (clone $builder)->orderBy($orderField, $sord)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['title'] = $row['title'];
            $v[$arr]['content'] = $row['content'];
            $v[$arr]['description'] = $row['description'];
            $v[$arr]['uId'] = intval($row['uId']);
            $v[$arr]['uName'] = $row['uName'];
            $v[$arr]['createTime'] = $row['createTime'] == '0000-00-00 00:00:00' ? '' : $row['createTime'];
            $v[$arr]['checkId'] = intval($row['checkId']);
            $v[$arr]['checkName'] = $row['checkName'];
            $v[$arr]['checkTime'] = $row['checkTime'] == '0000-00-00 00:00:00' ? '' : $row['checkTime'];
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['page'] = $page;
        $json['data']['records'] = $total;
        $json['data']['total'] = ceil($json['data']['records'] / $rows);
        $json['data']['rows'] = isset($v) ? array_values($v) : [];
        return $this->response->setJSON($json);
    }
    //获取信息
    public function query()
    {
        if (!$this->myservice->verifyRight('SERVICE_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/DiscountReq/query 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('discount_req')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
        if ($data) {
            $info['id'] = $id;
            $info['title'] = $data['title'];
            $info['content'] = $data['content'];
            $info['description'] = $data['description'];
            $info['uId'] = intval($data['uId']);
            $info['uName'] = $data['uName'];
            $info['createTime'] = $data['createTime'];
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
        if (!$this->myservice->verifyRight('SERVICE_ADD')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/DiscountReq/add 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $data = $this->validform($data);
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            $info = array_intersect_key($data, array_flip(['title', 'content', 'uId', 'uName', 'createTime']));
            $this->db->transStart();
            $this->db->table('discount_req')->insert($info);
            $data['id'] = $this->db->insertID();
            $data['checkId'] = 0;
            $this->myservice->log('新增优惠申请，ID：' . $data['id'] . '，标题：' . $data['title']);
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
        if (!$this->myservice->verifyRight('SERVICE_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/DiscountReq/update 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $data = $this->validform($data);
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            $oldData = $this->db->table('discount_req')->where(['id' => $data['id']])->get()->getRowArray();
            if ($oldData['checkId'] > 0 && !in_array('SALE_MANAGER', $this->myerp['rights'])) {
                return $this->response->setJSON(['status' => -1, 'msg' => '已审批的申请无法编辑！']);
            }
            $this->db->transStart();
            if (!$oldData['description'] && $data['description']) {
                $this->db->table('discount_req')->where('id', $data['id'])->update(['checkId' => $this->myerp['uId'], 'checkName' => $this->myerp['uName'], 'checkTime' => date('Y-m-d H:i:s')]);
            }
            $info = array_intersect_key($data, array_flip(['title', 'content', 'description']));
            $sql = $this->db->table('discount_req')->where('id', $data['id'])->update($info);
            $newData = $this->db->table('discount_req')->where(['id' => $data['id']])->get()->getRowArray();
            $this->myservice->log('编辑了优惠申请，ID：' . $data['id'] . '，标题：' . $data['title']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '编辑成功！', 'data' => $newData]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    //删除
    public function delete()
    {
        if (!$this->myservice->verifyRight('SERVICE_DELETE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/DiscountReq/delete 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('discount_req')->where(['id' => $id])->get()->getRowArray();
        if ($data) {
            if ($data['uId'] != $this->myerp['uId']) {
                return $this->response->setJSON(['status' => -1, 'msg' => '不能删除别人的优惠申请！']);
            }
            if ($data['checkId'] > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '已审批的申请无法删除！']);
            }
            $this->db->transStart();
            $this->db->table('discount_req')->where('id', $id)->update(['isDelete' => 1]);
            $this->myservice->log('删除了优惠申请，ID: ' . $id . '，标题：' . $data['title']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '删除成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    //审批
    public function check()
    {
        if (!$this->myservice->verifyRight('SALE_MANAGER')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/DiscountReq/check 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $checkId = intval($this->request->getGetPost('checkId'));
        $checkName = $this->request->getGetPost('checkName');
        $checkTime = $checkId > 0 ? date('Y-m-d H:i:s') : '0000-00-00 00:00:00';
        $title = $this->request->getGetPost('title');
        if ($id > 0) {
            $this->db->transStart();
            $this->db->table('discount_req')->where('id', $id)->update(['checkId ' => $checkId, 'checkName' => $checkName, 'checkTime' => $checkTime]);
            $this->myservice->log(($checkId > 0 ? '审批了' : '反审批了') . '优惠申请，ID：' . $id . '，标题：' . $title);
            $data = $this->db->table('discount_req')->where('id', $id)->get()->getRowArray();
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '操作成功！', 'data' => $data]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    //数据验证
    private function validform(array $data)
    {
        $data['uId'] = $data['uId'] ?? $this->myerp['uId'];
        $data['uName'] = $data['uName'] ?? $this->myerp['uName'];
        $data['createTime'] = date('Y-m-d H:i:s');
        $data['description'] = $data['description'] ?? '';
        return $data;
    }
}
