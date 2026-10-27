<?php

namespace App\Controllers\Bill;
use App\Controllers\BaseController;

class OnlineshopMp extends BaseController
{
    //列表
    public function list()
    {
        if (!$this->myservice->verifyRight('ONLINESHOPMP_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/OnlineshopMp/list 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $builder = $this->db->table('bill_onlineshopmp');
        $builder->where('isDelete', 0);
        $allowedSort = ['id','billNo','beginDate','endDate','amount','description','createTime','isDraft'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $list = (clone $builder)->orderBy($orderField, $sord)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['billNo'] = $row['billNo'];
            $v[$arr]['beginDate'] = $row['beginDate'];
            $v[$arr]['endDate'] = $row['endDate'];
            $v[$arr]['amount'] = (float)$row['amount'];
            $v[$arr]['description'] = $row['description'];
            $v[$arr]['uId'] = intval($row['uId']);
            $v[$arr]['uName'] = $row['uName'];
            $v[$arr]['createTime'] = $row['createTime'];
            $v[$arr]['modifyTime'] = $row['modifyTime'];
            $v[$arr]['isDraft'] = intval($row['isDraft']);
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['page'] = $page;
        $json['data']['records'] = $total;
        $json['data']['total'] = ceil($json['data']['records'] / $rows);
        $json['data']['rows'] = $v;
        return $this->response->setJSON($json);
    }
    //查询
    public function query()
    {
        if (!$this->myservice->verifyRight('ONLINESHOPMP_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/OnlineshopMp/query 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('bill_onlineshopmp')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
        if ($data) {
            $info['id'] = intval($data['id']);
            $info['billNo'] = $data['billNo'];
            $info['beginDate'] = $data['beginDate'];
            $info['endDate'] = $data['endDate'];
            $info['amount'] = (float)$data['amount'];
            $info['description'] = $data['description'];
            $info['uId'] = intval($data['uId']);
            $info['uName'] = $data['uName'];
            $info['createTime'] = $data['createTime'];
            $info['modifyTime'] = $data['modifyTime'];
            $info['isDraft'] = intval($data['isDraft']);
            $list = $this->db->table('bill_onlineshopmp_info')->where(['iid' => $id, 'isDelete' => 0])->orderBy('id', 'ASC')->get()->getResultArray();
            $v = [];
            foreach ($list as $arr => $row) {
                $v[] = [
                    'id' => intval($row['id']),
                    'uId' => intval($row['uId']),
                    'uName' => $row['uName'],
                    'amount1' => (float)$row['amount1'],
                    'amount2' => (float)$row['amount2'],
                    'amount3' => (float)$row['amount3'],
                    'amount4' => (float)$row['amount4'],
                    'amount5' => (float)$row['amount5'],
                    'amount6' => (float)$row['amount6'],
                    'amount7' => (float)$row['amount7'],
                    'amount8' => (float)$row['amount8'],
                    'amount9' => (float)$row['amount9'],
                    'amount10' => (float)$row['amount10'],
                    'amount11' => (float)$row['amount11'],
                    'amount12' => (float)$row['amount12'],
                    'amount' => (float)$row['amount'],
                    'description' => $row['description']
                ];
            }
            $info['entries'] = $v;
            $json['status'] = 200;
            $json['msg'] = 'success';
            $json['data'] = $info;
            return $this->response->setJSON($json);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    //新增
    public function add()
    {
        if (!$this->myservice->verifyRight('ONLINESHOPMP_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/OnlineshopMp/add 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getGetPost('postData');
        if (strlen($data) > 0) {
            $data = $this->validform((array)json_decode($data, true));
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            $info = array_intersect_key($data, array_flip([
                'billNo',
                'beginDate',
                'endDate',
                'amount',
                'description',
                'uId',
                'uName',
                'createTime',
                'modifyTime',
                'isDraft'
            ]));
            $this->db->transStart();
            $this->db->table('bill_onlineshopmp')->insert($info);
            $iid = $this->db->insertID();
            $this->performance_info(intval($iid), $data);
            $data['id'] = $iid;
            $this->myservice->log('新增了网店月绩单' . ($data['isDraft'] == 1 ? '草稿' : '') . '，单号：' . $data['billNo']);
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
        if (!$this->myservice->verifyRight('ONLINESHOPMP_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/OnlineshopMp/update 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getGetPost('postData');
        if (strlen($data) > 0) {
            $data = $this->validform((array)json_decode($data, true));
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            $oldData = $this->db->table('bill_onlineshopmp')->where('id', $data['id'])->where('isDelete', 0)->get()->getRowArray();
            $info = array_intersect_key($data, array_flip([
                'billNo',
                'beginDate',
                'endDate',
                'amount',
                'description',
                'modifyTime',
                'isDraft'
            ]));
            $this->db->transStart();
            $this->db->table('bill_onlineshopmp')->where('id', $data['id'])->update($info);
            $this->performance_info(intval($data['id']), $data);
            $this->myservice->log('编辑了网店月绩单' . ($data['isDraft'] == 1 ? '草稿' : '') . (($oldData['isDraft'] == 1 && $data['isDraft'] == 0) ? '，并将草稿转为了正式' : '') . '，单号：' . $data['billNo']);
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
        if (!$this->myservice->verifyRight('ONLINESHOPMP_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/OnlineshopMp/delete 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('bill_onlineshopmp')->where('id', $id)->where('isDelete', 0)->get()->getRowArray();
        if ($data) {
            if (!$this->myservice->verifyRight('SYS_ADSYS_ADMINVANCE') && $data['uId'] != $this->myerp['uId']) {
                return $this->response->setJSON(['status' => -1, 'msg' => '不能删除别人录入的网店月绩单！仅本人或管理员可删除！']);
            }
            if ($data['checkId'] > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '已审核的单据不可随意删除！']);
            }
            $this->db->transStart();
            $this->db->table('bill_onlineshopmp')->where('id', $id)->update(['isDelete' => 1]);
            $this->db->table('bill_onlineshopmp_info')->where('iid', $id)->update(['isDelete' => 1]);
            $this->myservice->log('删除了网店月绩单' . ($data['isDraft'] == 1 ? '草稿' : '') . '，单号：' . $data['billNo']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '删除成功！', 'data' => $id]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 更新备注
    public function updatedes()
    {
        if (!$this->myservice->verifyRight('ONLINESHOPMP_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/OnlineshopMp/updatedes 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $newDes = $this->request->getGetPost('newDes');
        $data = $this->db->table('bill_onlineshopmp')->where('id', $id)->where('isDelete', 0)->get()->getRowArray();
        if ($data) {
            $v['description'] = $newDes;
            $v['modifyTime'] = date("Y-m-d H:i:s");
            $this->db->transStart();
            $this->db->table('bill_onlineshopmp')->where('id', $id)->update($v);
            $this->myservice->log('单独更新了网店月绩单备注，单号：' . $data['billNo']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '更新成功！', 'data' => ['id' => $id]]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // info数据
    private function performance_info(int $iid, array $data)
    {
        $v = [];
        foreach ($data['entries'] as $arr => $row) {
            $v[] = [
                'iid' => $iid,
                'beginDate' => $data['beginDate'],
                'endDate' => $data['endDate'],
                'uId' => intval($row['uId']),
                'uName' => $row['uName'],
                'amount1' => (float)$row['amount1'],
                'amount2' => (float)$row['amount2'],
                'amount3' => (float)$row['amount3'],
                'amount4' => (float)$row['amount4'],
                'amount5' => (float)$row['amount5'],
                'amount6' => (float)$row['amount6'],
                'amount7' => (float)$row['amount7'],
                'amount8' => (float)$row['amount8'],
                'amount9' => (float)$row['amount9'],
                'amount10' => (float)$row['amount10'],
                'amount11' => (float)$row['amount11'],
                'amount12' => (float)$row['amount12'],
                'amount' => (float)$row['amount'],
                'description' => $row['description'],
            ];
        }
        if ($v) {
            if (isset($data['id']) && $data['id'] > 0) {
                // 真删除
                $this->db->table('bill_onlineshopmp_info')->where('iid', $iid)->delete();
            }
            $this->db->table('bill_onlineshopmp_info')->insertBatch($v);
        }
    }
    //验证
    private function validform(array $data)
    {
        $data['id'] = isset($data['id']) ? intval($data['id']) : -1;
        if (strlen($data['billNo']) < 8) {
            return $this->response->setJSON(['status' => -1, 'msg' => '单据编号长度不合规！']);
        }
        if (substr($data['beginDate'], 0, 7) != substr($data['endDate'], 0, 7)) {
            return $this->response->setJSON(['status' => -1, 'msg' => '起始日期和结束日期不在同月！']);
        }
        if ($this->db->table('bill_onlineshopmp')->where('id !=', $data['id'])->where('billNo', $data['billNo'])->where('isDelete', 0)->countAllResults() > 0) {
            return $this->response->setJSON(['status' => -1, 'msg' => '已经有同月单据了！']);
        }
        $data['uId'] = $this->myerp['uId'];
        $data['uName'] = $this->myerp['uName'];
        $data['createTime'] = date('Y-m-d H:i:s');
        $data['modifyTime'] = date('Y-m-d H:i:s');
        $data['isDraft'] = isset($data['isDraft']) ? intval($data['isDraft']) : 0;
        return $data;
    }
}
