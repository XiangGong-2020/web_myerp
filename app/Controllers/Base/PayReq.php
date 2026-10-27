<?php

namespace App\Controllers\Base;
use App\Controllers\BaseController;

class PayReq extends BaseController
{
    // 列表
    public function list()
    {
        $type = intval($this->request->getGetPost('type'));
        switch ($type) {
            case 1:
                if (!$this->myservice->verifyRight('PAYREQ_QUERY')) {
                    log_message('error', $this->myerp['uName'] . ' 访问 /Base/PayReq/list 付款申请 权限不足！');
                    return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
                }
                break;
            case 2:
                if (!$this->myservice->verifyRight('EXPENSESREQ_QUERY')) {
                    log_message('error', $this->myerp['uName'] . ' 访问 /Base/PayReq/list 报销申请 权限不足！');
                    return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
                }
                break;
            default:
                break;
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
        $builder = $this->db->table('pay_req');
        $builder->where('isDelete', 0);
        if ($type > 0) {
            $builder->where('type', $type);
        }
        if ($uId > 0) {
            $builder->where('uId', $uId);
        }
        if ($searchType == 1) {
            $builder->where('moneyStatus', 0);
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
                ->groupStart()
                    ->where('checkId', 0 )
                    ->orWhere('moneyStatus <', 2)
                ->groupEnd()
                ->where('description', '')
            ->groupEnd();
        }
        if ($skey !== '' && $skey !== null) {
            $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
            foreach ($keywords as $kw) {
                $builder->groupStart()
                    ->like('title', $this->db->escapeLikeString($kw))
                    ->orLike('content', $this->db->escapeLikeString($kw))
                    ->orWhere('uName', $kw)
                    ->orWhere('checkName', $kw)
                    ->orWhere('payName', $kw)
                ->groupEnd();
            }
        }
        $allowedSort = ['id','level','expendTypeName','amount','title','content','imgs','uName','createTime','checkName','checkTime','payName','payTime','description','checkId','moneyStatus','isInvoice'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $list = (clone $builder)->orderBy($orderField, $sord)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['type'] = intval($row['type']);
            $v[$arr]['expendTypeId'] = intval($row['expendTypeId']);
            $v[$arr]['expendTypeName'] = $row['expendTypeName'];
            $v[$arr]['level'] = intval($row['level']);
            $v[$arr]['amount'] = (float)$row['amount'];
            $v[$arr]['title'] = $row['title'];
            $v[$arr]['content'] = $row['content'];
            $v[$arr]['imgs'] = $row['imgs'] == '' ? [] : explode(',', $row['imgs']);
            $v[$arr]['uId'] = intval($row['uId']);
            $v[$arr]['uName'] = $row['uName'];
            $v[$arr]['createTime'] = $row['createTime'] == '0000-00-00 00:00:00' ? '' : $row['createTime'];
            $v[$arr]['checkId'] = intval($row['checkId']);
            $v[$arr]['checkName'] = $row['checkName'];
            $v[$arr]['checkTime'] = $row['checkTime'] == '0000-00-00 00:00:00' ? '' : $row['checkTime'];
            $v[$arr]['payId'] = intval($row['payId']);
            $v[$arr]['payName'] = $row['payName'];
            $v[$arr]['payTime'] = $row['payTime'] == '0000-00-00 00:00:00' ? '' : $row['payTime'];
            $v[$arr]['moneyStatus'] = intval($row['moneyStatus']);
            $v[$arr]['srcMoneyId'] = intval($row['srcMoneyId']);
            $v[$arr]['srcMoneyNo'] = $row['srcMoneyNo'];
            $v[$arr]['description'] = $row['description'];
            $v[$arr]['isInvoice'] = intval($row['isInvoice']);
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
    public function query() {
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('pay_req')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
        if ($data) {
            $info['type'] = intval($data['type']);
            switch ($info['type']) {
                case 1:
                    if (!$this->myservice->verifyRight('PAYREQ_QUERY')) {
                        log_message('error', $this->myerp['uName'] . ' 访问 /Base/PayReq/query 付款申请 权限不足！');
                        return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
                    }
                    break;
                case 2:
                    if (!$this->myservice->verifyRight('EXPENSESREQ_QUERY')) {
                        log_message('error', $this->myerp['uName'] . ' 访问 /Base/PayReq/list 报销申请 权限不足！');
                        return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
                    }
                    break;
                default:
                    break;
            }
            $info['id'] = $id;
            $info['expendTypeId'] = intval($data['expendTypeId']);
            $info['expendTypeName'] = $data['expendTypeName'];
            $info['level'] = intval($data['level']);
            $info['amount'] = (float)$data['amount'];
            $info['title'] = $data['title'];
            $info['content'] = $data['content'];
            $info['imgs'] =  $data['imgs'] == '' ? [] : explode(',', $data['imgs']);
            $info['uId'] = intval($data['uId']);
            $info['uName'] = $data['uName'];
            $info['checkId'] = intval($data['checkId']);
            $info['payId'] = intval($data['payId']);
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
    public function add() {
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $data = $this->validform($data);
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            switch (intval($data['type'])) {
                case 1:
                    if (!$this->myservice->verifyRight('PAYREQ_QUERY')) {
                        log_message('error', $this->myerp['uName'] . ' 访问 /Base/PayReq/add 付款申请 权限不足！');
                        return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
                    }
                    break;
                case 2:
                    if (!$this->myservice->verifyRight('EXPENSESREQ_QUERY')) {
                        log_message('error', $this->myerp['uName'] . ' 访问 /Base/PayReq/add 付款申请 权限不足！');
                        return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
                    }
                    break;
                default:
                    break;
            }
            $info = array_intersect_key($data, array_flip(['type', 'expendTypeId', 'expendTypeName', 'level', 'amount', 'title', 'content', 'imgs', 'uId', 'uName', 'createTime', 'description']));
            $this->db->transStart();
            $this->db->table('pay_req')->insert($info);
            $data['id'] = $this->db->insertID();
            $data['checkId'] = $data['payId'] = $data['moneyStatus'] = 0;
            $data['imgs'] = $data['imgs'] == '' ? [] : explode(',', $data['imgs']);
            $this->myservice->log('新增' . ($data['type'] == 1 ? '付款申请' : '报销申请') .'，ID：' . $data['id'] . '，标题：' . $data['title']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '新增成功！', 'data' => $data]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    // 编辑
    public function update() {
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $data = $this->validform($data);
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            switch (intval($data['type'])) {
                case 1:
                    if (!$this->myservice->verifyRight('PAYREQ_QUERY')) {
                        log_message('error', $this->myerp['uName'] . ' 访问 /Base/PayReq/update 付款申请 权限不足！');
                        return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
                    }
                    break;
                case 2:
                    if (!$this->myservice->verifyRight('EXPENSESREQ_QUERY')) {
                        log_message('error', $this->myerp['uName'] . ' 访问 /Base/PayReq/update 报销申请 权限不足！');
                        return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
                    }
                    break;
                default:
                    break;
            }
            $oldData = $this->db->table('pay_req')->where('id', $data['id'])->get()->getRowArray();
            if (
                ($oldData['checkId'] > 0 || $oldData['payId'] > 0) && (
                    $oldData['type'] != $data['type'] ||
                    $oldData['amount'] != $data['amount'] ||
                    $oldData['expendTypeId'] != $data['expendTypeId'] ||
                    $oldData['expendTypeName'] != $data['expendTypeName'] ||
                    $oldData['level'] != $data['level'] ||
                    $oldData['title'] != $data['title'] ||
                    $oldData['description'] != $data['description']
                )
            ) {
                return $this->response->setJSON(['status' => -1, 'msg' => '已审批或已付款的申请无法再编辑关键信息！']);
            }
            $this->db->transStart();
            if (!$oldData['description'] && $data['description']) {
                $this->db->table('pay_req')->where('id', $data['id'])->update(['checkId' => $this->myerp['uId'], 'checkName' => $this->myerp['uName'], 'checkTime' => date('Y-m-d H:i:s')]);
            }
            $info = array_intersect_key($data, array_flip(['expendTypeId', 'expendTypeName', 'level', 'amount', 'title', 'content', 'imgs', 'description']));
            $this->db->table('pay_req')->where('id', $data['id'])->update($info);
            $data = $this->db->table('pay_req')->where('id', $data['id'])->get()->getRowArray();
            $data['imgs'] = $data['imgs'] == '' ? [] : explode(',', $data['imgs']);
            $this->myservice->log('编辑了' . ($data['type'] == 1 ? '付款申请' : '报销申请') .'，ID：' . $data['id'] . '，标题：' . $data['title']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '编辑成功！', 'data' => $data]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    // 删除
    public function delete() {
        $id = $this->request->getGetPost('id');
        $data = $this->db->table('pay_req')->where('id', $id)->get()->getRowArray();
        if ($data) {
            switch (intval($data['type'])) {
                case 1:
                    if (!$this->myservice->verifyRight('PAYREQ_QUERY')) {
                        log_message('error', $this->myerp['uName'] . ' 访问 /Base/PayReq/delete 付款申请 权限不足！');
                        return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
                    }
                    break;
                case 2:
                    if (!$this->myservice->verifyRight('EXPENSESREQ_QUERY')) {
                        log_message('error', $this->myerp['uName'] . ' 访问 /Base/PayReq/delete 报销申请 权限不足！');
                        return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
                    }
                    break;
                default:
                    break;
            }
            if ($data['uId'] != $this->myerp['uId']) {
                return $this->response->setJSON(['status' => -1, 'msg' => '不能删除别人的' . ($data['type'] == 1 ? '付款申请' : '报销申请') . '！']);
            }
            if ($data['checkId'] > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '已审批申请无法删除！']);
            }
            if ($data['payId'] > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '已付款申请无法删除！']);
            }
            $this->db->transStart();
            $this->db->table('pay_req')->where('id', $id)->update(['isDelete' => 1]);
            $this->myservice->log('删除了' . ($data['type'] == 1 ? '付款申请' : '报销申请') .'，ID: ' . $id . '，标题：' . $data['title']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '删除成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 审批
    public function check() {
        $id = intval($this->request->getGetPost('id'));
        $checkId = intval($this->request->getGetPost('checkId'));
        $checkName = $this->request->getGetPost('checkName');
        $checkTime = $checkId > 0 ? date('Y-m-d H:i:s') : '0000-00-00 00:00:00';
        $data = $this->db->table('pay_req')->where('id', $id)->get()->getRowArray();
        if ($data) {
            switch (intval($data['type'])) {
                case 1:
                    if (!$this->myservice->verifyRight('PAYREQ_ADVANCE')) {
                        log_message('error', $this->myerp['uName'] . ' 访问 /Base/PayReq/check 付款申请 权限不足！');
                        return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
                    }
                    break;
                case 2:
                    if (!$this->myservice->verifyRight('EXPENSESREQ_ADVANCE')) {
                        log_message('error', $this->myerp['uName'] . ' 访问 /Base/PayReq/check 报销申请 权限不足！');
                        return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
                    }
                    break;
                default:
                    break;
            }
            $this->db->transStart();
            $this->db->table('pay_req')->where('id', $id)->update(['checkId ' => $checkId, 'checkName' => $checkName, 'checkTime' => $checkTime]);
            $this->myservice->log(($checkId > 0 ? '审批了' : '反审批了') . ($data['type'] == 1 ? '付款申请' : '报销申请') .'，ID: ' . $id . '，标题：' . $data['title']);
            $data = $this->db->table('pay_req')->where('id', $id)->get()->getRowArray();
            $data['imgs'] = $data['imgs'] == '' ? [] : explode(',', $data['imgs']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '操作成功！', 'data' => $data]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 收条
    public function invoice()
    {
        $id = intval($this->request->getGetPost('id'));
        $isInvoice = intval($this->request->getGetPost('isInvoice'));
        $data = $this->db->table('pay_req')->where('id', $id)->get()->getRowArray();
        if ($data) {
            switch (intval($data['type'])) {
                case 1:
                    if (!$this->myservice->verifyRight('PAYREQ_ADVANCE')) {
                        log_message('error', $this->myerp['uName'] . ' 访问 /Base/PayReq/invoice 付款申请 权限不足！');
                        return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
                    }
                    break;
                case 2:
                    if (!$this->myservice->verifyRight('EXPENSESREQ_ADVANCE')) {
                        log_message('error', $this->myerp['uName'] . ' 访问 /Base/PayReq/invoice 报销申请 权限不足！');
                        return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
                    }
                    break;
                default:
                    break;
            }
            $this->db->transStart();
            $this->db->table('pay_req')->where('id', $id)->update(['isInvoice ' => $isInvoice]);
            $this->myservice->log('标记了' . ($data['type'] == 1 ? '付款申请' : '报销申请') . '的收条状态为' . ($isInvoice == 1 ? '已收' : '未收') . '，ID: ' . $id . '，标题：' . $data['title']);
            $data = $this->db->table('pay_req')->where('id', $id)->get()->getRowArray();
            $data['imgs'] = $data['imgs'] == '' ? [] : explode(',', $data['imgs']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '操作成功！', 'data' => $data]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 上传参考图片、发票图片
    public function uploadImg() {
        set_time_limit(0);
        session_write_close();
        if (empty($_FILES)){
            return $this->response->setJSON(['status' => -1, 'msg' => '没有上传文件！']);
        };
        $uppath = 'data/upfile/payReq/';
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
        $this->myservice->log('上传了付款申请相关图片：' . $_FILES['file']['name']);
        return $this->response->setJSON(['status' => 200, 'msg' => '上传成功！', 'data' => $target_file]);
    }
    //数据验证
    private function validform(array $data) {
        $data['uId'] = $data['uId'] ?? $this->myerp['uId'];
        $data['uName'] = $data['uName'] ?? $this->myerp['uName'];
        $data['createTime'] = date('Y-m-d H:i:s');
        $data['description'] = $data['description'] ?? '';
        $data['imgs'] = strlen($data['imgs']) < 10 ? '' : $data['imgs'];
        return $data;
    }
}
