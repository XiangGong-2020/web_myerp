<?php

namespace App\Controllers\Base;
use App\Controllers\BaseController;

class DeliverBagOff extends BaseController
{
    //列表
    public function list()
    {
        if (!$this->myservice->verifyRight('DELIVERBAG_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/DeliverBagOff/list 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $status1 = $this->request->getGetPost('status1');
        $status2 = $this->request->getGetPost('status2');
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');
        $skey = $this->request->getGetPost('skey');
        $builder = $this->db->table('deliver_bag_off')->where('isDelete', 0);
        if ($status1) {
            $builder->where('uName', $status1);
        }
        if ($status2) {
            $builder->where('company', $status2);
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
                $builder->like('no', $this->db->escapeLikeString($kw));
            }
        }
        $allowedSort = ['id','no','company','uId','uName','description','createTime','scanName','scanTime','offHits'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $list = (clone $builder)->orderBy($orderField, $sord)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['no'] = $row['no'];
            $v[$arr]['company'] = $row['company'];
            $v[$arr]['uId'] = intval($row['uId']);
            $v[$arr]['uName'] = $row['uName'];
            $v[$arr]['description'] = $row['description'];
            $v[$arr]['createTime'] = $row['createTime'] == '0000-00-00 00:00:00' ? '' : $row['createTime'];
            $v[$arr]['scanName'] = $row['scanName'];
            $v[$arr]['scanTime'] = $row['scanTime'] == '0000-00-00 00:00:00' ? '' : $row['scanTime'];
            $v[$arr]['offHits'] = intval($row['offHits']);
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
        if (!$this->myservice->verifyRight('DELIVERBAG_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/DeliverBagOff/query 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('deliver_bag_off')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
        if ($data) {
            $info['id'] = $id;
            $info['no'] = $data['no'];
            $info['company'] = $data['company'];
            $info['description'] = $data['description'];
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
        if (!$this->myservice->verifyRight('DELIVERBAG_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/DeliverBagOff/add 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $data = $this->validform($data);
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            if ($this->db->table('deliver_bag_off')->where('no', $data['no'])->where('isDelete', 0)->countAllResults() > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '包裹单号重复！']);
            }
            if ($this->db->table('deliver_bag')->where('no', $data['no'])->where('scanName !=', '')->where('isDelete', 0)->countAllResults() > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '包裹已被称重扫码！无法内部拦截！']);
            }
            $this->db->transStart();
            $this->db->table('deliver_bag_off')->insert(array_intersect_key($data, array_flip(['no', 'company', 'uId', 'uName', 'description', 'createTime'])));
            $data['id'] = $this->db->insertID();
            $this->myservice->log('新增发包拦截，单号：' . $data['no']);
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
        if (!$this->myservice->verifyRight('DELIVERBAG_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/DeliverBagOff/update 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $data = $this->validform($data);
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            if ($this->db->table('deliver_bag_off')->where('no', $data['no'])->where('id !=', $data['id'])->where('isDelete', 0)->countAllResults() > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '包裹单号重复！']);
            }
            if ($this->db->table('deliver_bag')->where('no', $data['no'])->where('scanName !=', '')->where('isDelete', 0)->countAllResults() > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '包裹已被称重扫码！无法内部拦截！']);
            }
            $info = array_intersect_key($data, array_flip(['no', 'company', 'description']));
            $this->db->transStart();
            $this->db->table('deliver_bag_off')->where('id', $data['id'])->update($info);
            $this->myservice->log('编辑发包拦截，单号：' . $data['no']);
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
        if (!$this->myservice->verifyRight('DELIVERBAG_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/DeliverBagOff/delete 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('deliver_bag_off')->where('id', $id)->get()->getRowArray();
        if ($data) {
            if ($data['uId'] != $this->myerp['uId']) {
                return $this->response->setJSON(['status' => -1, 'msg' => '不能删除别人的发包拦截！']);
            }
            $this->db->transStart();
            $this->db->table('deliver_bag_off')->where('id', $id)->update(['isDelete' => 1]);
            $this->myservice->log('删除了发包拦截，单号：' . $data['no']);
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
        if (strlen($data['no']) < 11 || strlen($data['no']) > 15) {
            return $this->response->setJSON(['status' => -1, 'msg' => '拦截单号格式不对！']);
        }
        if (strlen($data['company']) < 2) {
            return $this->response->setJSON(['status' => -1, 'msg' => '请选择所属公司！']);
        }
        $data['uId'] = $this->myerp['uId'];
        $data['uName'] = $this->myerp['uName'];
        $data['createTime'] = date('Y-m-d H:i:s');
        $data['description'] = $data['description'] ?? '';
        return $data;
    }
}
