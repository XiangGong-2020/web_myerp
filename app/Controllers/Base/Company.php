<?php

namespace App\Controllers\Base;
use App\Controllers\BaseController;

class Company extends BaseController
{
    // 列表
    public function list()
    {
        if (!$this->myservice->verifyRight('COMPANY_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Company/list 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $isDisable = intval($this->request->getGetPost('isDisable'));
        $skey = $this->request->getGetPost('skey');
        $builder = $this->db->table('company')->where('isDelete', 0);
        if ($isDisable >= 0) {
            $builder->where('isDisable', $isDisable);
        }
        if ($skey !== '' && $skey !== null) {
            $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
            foreach ($keywords as $kw) {
                $builder->groupStart()
                    ->like('name', $this->db->escapeLikeString($kw))
                    ->orLike('no', $this->db->escapeLikeString($kw))
                ->groupEnd();
            }
        }
        $allowedSort = ['id','name','fullName','no','managerName','address','date','type','invoiceway','invoicelimit','businessScope','businessLicense','description','ordnum','isDisable'];
        $orderField  = in_array($sidx, $allowedSort, true) ? $sidx : '';
        $list = (clone $builder)->orderBy($orderField ? $orderField. ' ' . $sord : 'ordnum ASC, id ASC')->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['ordnum'] = intval($row['ordnum']);
            $v[$arr]['name'] = $row['name'];
            $v[$arr]['fullName'] = $row['fullName'];
            $v[$arr]['managerName'] = $row['managerName'];
            $v[$arr]['no'] = $row['no'];
            $v[$arr]['address'] = $row['address'];
            $v[$arr]['date'] = $row['date'];
            $v[$arr]['type'] = intval($row['type']);
            $v[$arr]['invoiceway'] = intval($row['invoiceway']);
            $v[$arr]['invoicelimit'] = $row['invoicelimit'];
            $v[$arr]['account'] = $row['account'];
            $v[$arr]['billFrom'] = $row['billFrom'];
            $v[$arr]['businessScope'] = $row['businessScope'];
            $v[$arr]['businessLicense'] = $row['businessLicense'];
            $v[$arr]['description'] = $row['description'];
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
    // 查询所有
    public function queryAll()
    {
        if (!$this->myservice->verifyRight('COMPANY_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Company/queryAll 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $isDisable = intval($this->request->getGetPost('isDisable') ?? -1);
        $builder = $this->db->table('company')->where('isDelete', 0);
        if ($isDisable >= 0) {
            $builder->where('isDisable', $isDisable);
        }
        $list = (clone $builder)->orderBy('ordnum ASC, id ASC')->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['name'] = $row['name'];
            $v[$arr]['isDisable'] = intval($row['isDisable']);
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['records'] = $total;
        $json['data']['rows'] = $v;
        return $this->response->setJSON($json);
    }
    // 查询
    public function query()
    {
        if (!$this->myservice->verifyRight('COMPANY_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Company/query 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('company')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
        if ($data) {
            $json['status'] = 200;
            $json['msg'] = 'success';
            $json['data']['id'] = intval($data['id']);
            $json['data']['name'] = $data['name'];
            $json['data']['fullName'] = $data['fullName'];
            $json['data']['managerName'] = $data['managerName'];
            $json['data']['no'] = $data['no'];
            $json['data']['address'] = $data['address'];
            $json['data']['date'] = $data['date'];
            $json['data']['type'] = intval($data['type']);
            $json['data']['invoiceway'] = intval($data['invoiceway']);
            $json['data']['invoicelimit'] = $data['invoicelimit'];
            $json['data']['account'] = $data['account'];
            $json['data']['billFrom'] = $data['billFrom'];
            $json['data']['businessScope'] = $data['businessScope'];
            $json['data']['businessLicense'] = $data['businessLicense'];
            $json['data']['description'] = $data['description'];
            return $this->response->setJSON($json);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 新增
    public function add()
    {
        if (!$this->myservice->verifyRight('COMPANY_ADD')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Company/add 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $data = $this->validform($data);
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            $info = array_intersect_key($data, array_flip(['name', 'fullName', 'managerName', 'no', 'address', 'date', 'type', 'invoiceway', 'invoicelimit', 'account', 'billFrom', 'businessScope', 'businessLicense', 'description']));
            $this->db->transStart();
            $this->db->table('company')->insert($info);
            $data['id'] = $this->db->insertID();
            $data['isDisable'] = 0;
            if ($data['account']) {
                $accountArr = explode(',', $data['account']);
                foreach ($accountArr as $account) {
                    $this->db->table('company_account')->where('name', $account)->update(['company' => $data['name']]);
                }
            }
            if ($data['billFrom'] != '') {
                $billFromArr = explode(',', $data['billFrom']);
                foreach ($billFromArr as $billFrom) {
                    $this->db->table('billfrom')->where('name', $billFrom)->update(['company' => $data['name']]);
                }
            }
            $this->myservice->log('新增了经营主体：' . $data['name']);
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
        if (!$this->myservice->verifyRight('COMPANY_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Company/update 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $data = $this->validform($data);
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            $oldData = $this->db->table('company')->where('id', $data['id'])->get()->getRowArray();
            $info = array_intersect_key($data, array_flip(['name', 'fullName', 'managerName', 'no', 'address', 'date', 'type', 'invoiceway', 'invoicelimit', 'account', 'billFrom', 'businessScope', 'businessLicense', 'description']));
            $this->db->transStart();
            $this->db->table('company')->where('id', $data['id'])->update($info);
            if ($data['name'] != $oldData['name']) {
                $this->db->table('bill_bidquote')->where('sId', $data['id'])->update(['sName' => $data['name']]);
                $this->db->table('bill_contract')->where('sId', $data['id'])->update(['sName' => $data['name']]);
                $this->db->table('bill_order')->where('sId', $data['id'])->update(['sName' => $data['name']]);
                $this->db->table('billfrom')->where('company', $oldData['name'])->update(['company' => $data['name']]);
                $this->db->table('company_account')->where('company', $oldData['name'])->update(['company' => $data['name']]);
                $this->db->table('sales_amount_monthly')->where('cId', $data['id'])->update(['cName' => $data['name']]);
                $this->db->table('user')->where('company', $oldData['name'])->update(['company' => $data['name']]);
                $this->myservice->log('编辑了经营主体：' . $oldData['name'] . ' 为 ' . $data['name']);
            } else {
                $this->myservice->log('编辑了经营主体：' . $data['name']);
            }
            if ($data['account']) {
                $accountArr = explode(',', $data['account']);
                foreach ($accountArr as $account) {
                    $this->db->table('company_account')->where('name', $account)->update(['company' => $data['name']]);
                }
            }
            if ($data['billFrom'] != '') {
                $billFromArr = explode(',', $data['billFrom']);
                foreach ($billFromArr as $billFrom) {
                    $this->db->table('billfrom')->where('name', $billFrom)->update(['company' => $data['name']]);
                }
            }
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
        if (!$this->myservice->verifyRight('COMPANY_DELETE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Company/delete 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('company')->where('id', $id)->get()->getRowArray();
        if ($data) {
            $this->db->transStart();
            $this->db->table('company')->where('id', $id)->update(['isDelete' => 1]);
            $this->db->table('company_account')->where('company', $data['name'])->update(['isDelete' => 1]);
            $this->db->table('billfrom')->where('company', $data['name'])->update(['company' => '虚拟主体']);
            $this->db->table('user')->where('company', $data['name'])->update(['company' => '虚拟主体']);
            $this->myservice->log('删除了经营主体，ID：' . $id . ' 名称：' . $data['name']);
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
        if (!$this->myservice->verifyRight('COMPANY_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Company/disable 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $isDisable = intval($this->request->getGetPost('isDisable'));
        $data = $this->db->table('company')->where('id', $id)->get()->getRowArray();
        if ($data) {
            $this->db->transStart();
            $this->db->table('company')->where('id', $id)->update(['isDisable' => $isDisable]);
            if ($isDisable == 1) {
                $this->db->table('company_account')->where('company', $data['name'])->update(['isDisable' => 1]);
            }
            $this->myservice->log('将经营主体 ' . $data['name'] . ' 设置为：' . ($isDisable == 1 ? '已注销' : '存续中'));
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '操作成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 排序编辑
    public function ordnumchange()
    {
        if (!$this->myservice->verifyRight('COMPANY_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Company/ordnumchange 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $ordnum = intval($this->request->getGetPost('ordnum'));
        if ($id > 0 && $ordnum > -1) {
            $this->db->transStart();
            $sql = $this->db->table('company')->where('id', $id)->update(['ordnum' => $ordnum]);
            $this->myservice->log('编辑了经营主体自定义排序值');
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '操作成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    // 上传营业执照图片
    public function uploadImg()
    {
        if (!$this->myservice->verifyRight('COMPANY_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Company/uploadImg 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        set_time_limit(0);
        session_write_close();
        if (empty($_FILES)){
            return $this->response->setJSON(['status' => -1, 'msg' => '没有上传文件！']);
        };
        $uppath = 'data/upfile/company/';
        $temp_file = $_FILES['file']['tmp_name'];
        $target_file = date("YmdHis") . rand(1000, 9000) . '.' . strrev(explode('.', strrev($_FILES['file']['name']))[0]);
        if (!is_dir($uppath)) {
            @umask(0);
            $ret = @mkdir($uppath, 0777);
            if ($ret === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '没有权限创建用于保存的文件夹！']);
            }
        }
        move_uploaded_file($temp_file, $uppath . $target_file);
        $this->myservice->log('上传了营业执照图片：' . $_FILES['file']['name']);
        return $this->response->setJSON(['status' => 200, 'msg' => '提交成功', 'date' => $target_file]);
    }
    // 数据验证
    private function validform(array $data)
    {
        $data['id'] = isset($data['id']) ? intval($data['id']) : 0;
        if (strlen($data['name']) < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '主体简称不能为空！']);
        }
        if (strlen($data['fullName']) < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '主体全称不能为空！']);
        }
        if (strlen($data['managerName']) < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '法人姓名不能为空！']);
        }
        if (strlen($data['no']) < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '信用代码不能为空！']);
        }
        if (strlen($data['address']) < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '注册地址不能为空！']);
        }
        if (strlen($data['date']) < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '注册日期不能为空！']);
        }
        if (strlen($data['businessScope']) < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '经营范围不能为空！']);
        }
        $where = $data['id'] > 0 ? (' AND id<>' . $data['id']) : '';
        $builder = $this->db->table('company');
        if ($data['id'] > 0) {
            $builder->where('id !=', $data['id']);
        }
        if ((clone $builder)->where('isDelete', 0)->where('name', $data['name'])->countAllResults() > 0) {
            return $this->response->setJSON(['status' => -1, 'msg' => '主体简称重复！']);
        }
        if ((clone $builder)->where('isDelete', 0)->where('fullName', $data['fullName'])->countAllResults() > 0) {
            return $this->response->setJSON(['status' => -1, 'msg' => '主体全称重复！']);
        }
        if ((clone $builder)->where('isDelete', 0)->where('no', $data['no'])->countAllResults() > 0) {
            return $this->response->setJSON(['status' => -1, 'msg' => '信用代码重复！']);
        }
        $data['type'] = intval($data['type']);
        $data['invoiceway'] = intval($data['invoiceway']);
        return $data;
    }
}
