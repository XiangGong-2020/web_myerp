<?php

namespace App\Controllers\Base;
use App\Controllers\BaseController;

class CompanyAccount extends BaseController
{
    // 结算账户 列表
    public function list()
    {
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 1000);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $isDisable = intval($this->request->getGetPost('isDisable'));
        $skey = $this->request->getGetPost('skey');
        $builder = $this->db->table('company_account')->where('isDelete', 0);
        if ($isDisable >= 0) {
            $builder->where('isDisable', $isDisable);
        }
        if ($skey !== '' && $skey !== null) {
            $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
            foreach ($keywords as $kw) {
                $builder->groupStart()
                    ->like('name', $this->db->escapeLikeString($kw))
                    ->orLike('company', $this->db->escapeLikeString($kw))
                ->groupEnd();
            }
        }
        $allowedSort = ['id','name','company','date','type','description','isDisable'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $list = (clone $builder)->orderBy($orderField, $sord)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['name'] = $row['name'];
            $v[$arr]['amount'] = (float)$row['amount'];
            $v[$arr]['date'] = $row['date'];
            $v[$arr]['type'] = intval($row['type']);
            $v[$arr]['company'] = $row['company'];
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
    // 结算账户 查询
    public function query()
    {
        if (!$this->myservice->verifyRight('COMPANYACCOUNT_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/CompanyAccount/query 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('company_account')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
        if ($data) {
            $json['status'] = 200;
            $json['msg'] = 'success';
            $json['data']['date'] = $data['date'];
            $json['data']['amount'] = (float)$data['amount'];
            $json['data']['del'] = false;
            $json['data']['id'] = intval($data['id']);
            $json['data']['name'] = $data['name'];
            $json['data']['type'] = intval($data['type']);
            $json['data']['company'] = $data['company'];
            $json['data']['description'] = $data['description'];
            return $this->response->setJSON($json);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '数据不存在！']);
    }
    // 结算账户 根据关联主体名称查询（只返回id最新的一行的数据）
    public function queryByCompanyName()
    {
        if (!$this->myservice->verifyRight('COMPANYACCOUNT_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/CompanyAccount/queryByCompanyName 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $company = $this->request->getGetPost('company');
        $data = $this->db->table('company_account')->where(['company' => $company, 'isDelete' => 0])->get()->getRowArray();
        if ($data) {
            $json['status'] = 200;
            $json['msg'] = 'success';
            $json['data']['id'] = intval($data['id']);
            $json['data']['name'] = $data['name'];
            return $this->response->setJSON($json);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '数据不存在！']);
    }
    // 结算账户 当前余额
    public function findAmountOver()
    {
        if (!$this->myservice->verifyRight('COMPANYACCOUNT_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/CompanyAccount/findAmountOver 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $ids = $this->request->getGetPost('ids');
        $ids = array_filter(array_map('intval', explode(',', (string) $ids)));
        if (count($ids) > 0) {
            // 1. 子查询：按账户汇总流水金额（PAY/EXP 记为负数）
            // 注意：getCompiledSelect() 只编译不执行，会重置该 builder
            $subQuery = $this->db->table('bill_money_info')
                ->select('accId, SUM(CASE WHEN billType IN ("PAY","EXP") THEN -amount ELSE amount END) AS amount', false)
                ->where('isDelete', 0)
                ->groupBy('accId')
                ->getCompiledSelect();
            $list = $this->db->table('company_account AS a')
                // 普通字段仍走构造器转义
                ->select('a.id, a.date, a.type, a.name AS accountName, a.id AS accountNumber, b.amount')
                // 计算列必须关转义，否则 CASE/IFNULL 会被当字段名加反引号
                ->select('(a.amount + IFNULL(b.amount, 0)) AS amount', false)
                ->join("({$subQuery}) AS b", 'a.id = b.accId', 'left')
                ->where('a.isDelete', 0)
                ->whereIn('a.id', $ids)
                ->get()
                ->getResultArray();
            $v = [] ;
            foreach ($list as $arr => $row) {
                $v[$arr]['id'] = intval($row['id']);
                $v[$arr]['amountOver'] = (float)$row['amount'];
            }
            $json['status'] = 200;
            $json['msg'] = 'success';
            $json['data']['rows'] = $v;
            return $this->response->setJSON($json);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '数据不存在！']);
    }
    // 结算账户 新增
    public function add()
    {
        if (!$this->myservice->verifyRight('COMPANYACCOUNT_ADD')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/CompanyAccount/add 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $data = $this->validform($data);
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            $info = array_intersect_key($data, array_flip(['name', 'amount', 'date', 'type', 'company', 'description']));
            $this->db->transStart();
            $this->db->table('company_account')->insert($info);
            $data['id'] = $this->db->insertID();
            $data['isDisable'] = 0;
            $this->myservice->log('新增了结算账户：' . $data['name']);
            if ($data['company']) {
                $accountArr = $this->db->table('company_account')->select('name')->where('company', $data['company'])->get()->getResultArray();
                $account = count($accountArr) > 0 ? implode(',', array_column($accountArr, 'name')) : '';
                $this->db->table('company')->where('name', $data['company'])->update(['account' => $account]);
            }
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '新增成功！', 'data' => $data]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    // 结算账户 编辑
    public function update()
    {
        if (!$this->myservice->verifyRight('COMPANYACCOUNT_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/CompanyAccount/update 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $data = $this->validform($data);
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            $oldData = $this->db->table('company_account')->where('id', $data['id'])->get()->getRowArray();
            $info = array_intersect_key($data, array_flip(['name', 'amount', 'date', 'type', 'company', 'description']));
            $this->db->transStart();
            $this->db->table('company_account')->where('id', $data['id'])->update($info);
            if ($data['name'] != $oldData['name']) {
                $this->myservice->log('编辑了结算账户：' . $oldData['name'] . ' 为 ' . $data['name']);
            } else {
                $this->myservice->log('编辑了结算账户：' . $data['name']);
            }
            if ($data['company'] != $oldData['company']) {
                $oldAccountArr = $this->db->table('company_account')->select('name')->where('company', $oldData['company'])->get()->getResultArray();
                $oldAccount = count($oldAccountArr) > 0 ? implode(',', array_column($oldAccountArr, 'name')) : '';
                $this->db->table('company')->where('name', $oldData['company'])->update(['account' => $oldAccount]);
                $newAccountArr = $this->db->table('company_account')->select('name')->where('company', $data['company'])->get()->getResultArray();
                $newAccount = count($newAccountArr) > 0 ? implode(',', array_column($newAccountArr, 'name')) : '';
                $this->db->table('company')->where('name', $data['company'])->update(['account' => $newAccount]);
            } else {
                $accountArr = $this->db->table('company_account')->select('name')->where('company', $data['company'])->get()->getResultArray();
                $account = count($accountArr) > 0 ? implode(',', array_column($accountArr, 'name')) : '';
                $this->db->table('company')->where('name', $data['company'])->update(['account' => $account]);
            }
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '编辑成功！', 'data' => $data]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    // 结算账户 删除
    public function delete()
    {
        if (!$this->myservice->verifyRight('COMPANYACCOUNT_DELETE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/CompanyAccount/delete 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('company_account')->where('id', $id)->get()->getRowArray();
        if ($data) {
            if ($this->db->table('bill_money_info')->where(['accId' => $id, 'isDelete' => 0])->countAllResults() > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '结算账户已被使用，无法删除！']);
            }
            $this->db->transStart();
            $this->db->table('company_account')->where('id', $id)->update(['isDelete' => 1]);
            $this->myservice->log('删除了结算账户，ID：' . $id . ' 名称:' . $data['name']);
            $accountArr = $this->db->table('company_account')->select('name')->where('company', $data['company'])->get()->getResultArray();
            $account = count($accountArr) > 0 ? implode(',', array_column($accountArr, 'name')) : '';
            $this->db->table('company')->where('name', $data['company'])->update(['account' => $account]);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '删除成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 结算账户 状态
    public function disable()
    {
        if (!$this->myservice->verifyRight('COMPANYACCOUNT_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/CompanyAccount/disable 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $isDisable = intval($this->request->getGetPost('isDisable'));
        $data = $this->db->table('company_account')->where('id', $id)->get()->getRowArray();
        if ($data) {
            $this->db->transStart();
            $this->db->table('company_account')->where('id', $id)->update(['isDisable' => $isDisable]);
            $this->myservice->log('标记结算账户 ' . $data['name'] . ' 的状态为：' . ($isDisable > 0 ? '已注销' : '使用中'));
            $accountArr = $this->db->table('company_account')->select('name')->where('company', $data['company'])->get()->getResultArray();
            $account = count($accountArr) > 0 ? implode(',', array_column($accountArr, 'name')) : '';
            $this->db->table('company')->where('name', $data['company'])->update(['account' => $account]);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '操作成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 结算账户 数据验证
    private function validform(array $data)
    {
        if (strlen($data['name']) < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '名称不能为空！']);
        }
        $data['id'] = isset($data['id']) ? intval($data['id']) : -1;
        if ($this->db->table('company_account')->where('id !=', $data['id'])->where('name', $data['name'])->where('isDelete', 0)->countAllResults() > 0) {
            return $this->response->setJSON(['status' => -1, 'msg' => '名称重复！']);
        }
        $data['amount'] = (float)$data['amount'];
        $data['type'] = intval($data['type']);
        return $data;
    }
}
