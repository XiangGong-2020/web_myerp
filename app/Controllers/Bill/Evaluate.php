<?php

namespace App\Controllers\Bill;
use App\Controllers\BaseController;

class Evaluate extends BaseController
{
    // 考评月表 列表
    public function list()
    {
        if (!$this->myservice->verifyRight('EVALUATE_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/Evaluate/list 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $builder = $this->db->table('bill_evaluate');
        $builder->where('isDelete', 0);
        $allowedSort = ['id','billNo','beginDate','endDate','uName','createTime','modifyTime','isDraft'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $list = (clone $builder)->orderBy($orderField, $sord)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[] = [
                'id' => intval($row['id']),
                'billNo' => $row['billNo'],
                'beginDate' => $row['beginDate'],
                'endDate' => $row['endDate'],
                'description' => $row['description'],
                'uId' => intval($row['uId']),
                'uName' => $row['uName'],
                'createTime' => $row['createTime'],
                'modifyTime' => $row['modifyTime'],
                'isDraft' => intval($row['isDraft'])
            ];
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['page'] = $page;
        $json['data']['records'] = $total;
        $json['data']['total'] = ceil($json['data']['records'] / $rows);
        $json['data']['rows'] = $v;
        return $this->response->setJSON($json);
    }
    // 考评月表 查询
    public function query()
    {
        if (!$this->myservice->verifyRight('EVALUATE_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/Evaluate/query 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('bill_evaluate')->where(['isDelete' => 0, 'id' => $id])->get()->getRowArray();
        if ($data) {
            $info['id'] = intval($data['id']);
            $info['billNo'] = $data['billNo'];
            $info['beginDate'] = $data['beginDate'];
            $info['endDate'] = $data['endDate'];
            $info['description'] = $data['description'];
            $info['uId'] = intval($data['uId']);
            $info['uName'] = $data['uName'];
            $info['createTime'] = $data['createTime'];
            $info['modifyTime'] = $data['modifyTime'];
            $info['isDraft'] = intval($data['isDraft']);
            $list = $this->db->table('bill_evaluate_info')->where(['isDelete' => 0, 'iid' => $id])->orderBy('department', 'DESC')->orderBy('position', 'ASC')->orderBy('uId', 'ASC')->get()->getResultArray();
            $v = [];
            foreach ($list as $arr => $row) {
                $v[$arr]['id'] = intval($row['id']);
                $v[$arr]['uId'] = intval($row['uId']);
                $v[$arr]['uName'] = $row['uName'];
                $v[$arr]['department'] = $row['department'];
                $v[$arr]['position'] = $row['position'];
                $v[$arr]['beginDate'] = $row['beginDate'];
                $v[$arr]['endDate'] = $row['endDate'];
                $v[$arr]['score'] = intval($row['score']);
                $v[$arr]['description'] = $row['description'];
                $v[$arr]['lastName'] = $row['lastName'];
                $v[$arr]['lastTime'] = $row['lastTime'] == '0000-00-00 00:00:00' ? '' : $row['lastTime'];
            }
            $info['entries'] = $v;

            $json['status'] = 200;
            $json['msg'] = 'success';
            $json['data'] = $info;
            return $this->response->setJSON($json);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 考评月表 编辑
    public function update()
    {
        if (!$this->myservice->verifyRight('EVALUATE_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/Evaluate/update 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getGetPost('postData');
        if (strlen($data) > 0) {
            $data = (array)json_decode($data, true);
            if ($data['id'] > 0) {
                $oldData = $this->db->table('bill_evaluate')->where(['id' => $data['id'], 'isDelete' => 0])->get()->getRowArray();
                $data['modifyTime'] = date('Y-m-d H:i:s');
                $info = array_intersect_key($data, array_flip(['description', 'modifyTime', 'isDraft']));
                $this->db->transStart();
                $this->db->table('bill_evaluate')->where(['id' => $data['id']])->update($info);
                $this->myservice->log('编辑了考评月表' . ($data['isDraft'] == 1 ? '草稿' : '') . (($oldData['isDraft'] == 1 && $data['isDraft'] == 0) ? '，并将草稿转为了正式' : '').'，单号：'.$oldData['billNo']);
                $this->db->transComplete();
                if ($this->db->transStatus() === false) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
                }
                return $this->response->setJSON(['status' => 200, 'msg' => '编辑成功！', 'data' => $data]);
            }
            return $this->response->setJSON(['status' => -1, 'msg' => '提交的数据错误！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    // 考评月表 删除
    public function delete()
    {
        if (!$this->myservice->verifyRight('EVALUATE_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/Evaluate/delete 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('bill_evaluate')->where(['id' => $id])->get()->getRowArray();
        if ($data) {
            if (!$this->myservice->verifyRight('SYS_ADMIN') && $data['uId'] != $this->myerp['uId']) {
                return $this->response->setJSON(['status' => -1, 'msg' => '不能删除别人生成的考评月表单！仅本人或管理员可删除！']);
            }
            $this->db->transStart();
            $this->db->table('bill_evaluate')->where(['id' => $id])->update(['isDelete' => 1]);
            $this->db->table('bill_evaluate_info')->where(['iid' => $id])->update(['isDelete' => 1]);
            $this->myservice->log('删除了考评月表单' . ($data['isDraft'] == 1 ? '草稿' : '').'，单号：'.$data['billNo']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '删除成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 考评月表 更新备注
    public function updatedes()
    {
        if (!$this->myservice->verifyRight('EVALUATE_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/Evaluate/updatedes 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $newDes = $this->request->getGetPost('newDes');
        $data = $this->db->table('bill_evaluate')->where(['id' => $id])->get()->getRowArray();
        if ($data) {
            $v['description'] = $newDes;
            $v['modifyTime'] = date("Y-m-d H:i:s");
            $this->db->transStart();
            $this->db->table('bill_evaluate')->where(['id' => $id])->update($v);
            $this->myservice->log('单独更新了考评月表单的备注，单号：'.$data['billNo']);
            $this->db->transComplete();
            if ($this->db->transStatus() === FALSE) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '更新成功！', 'data' => ['id' => $id]]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }


    // 考评项目 列表
    public function evaluationList()
    {
        if (!$this->myservice->verifyRight('EVALUATION_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/Evaluate/evaluationList 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 1000);
        $type = $this->request->getGetPost('type');
        $skey = $this->request->getGetPost('skey');
        $builder = $this->db->table('evaluation');
        $builder->where('isDelete', 0);
        if (strlen($type) > 0) {
            $builder->where('type', $type);
        }
        if ($skey !== '' && $skey !== null) {
            $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
            foreach ($keywords as $kw) {
                $builder->like('name', $this->db->escapeLikeString($kw));
            }
        }
        $list = (clone $builder)->orderBy('type ASC, id ASC')->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['type'] = $row['type'];
            $v[$arr]['name'] = $row['name'];
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['page'] = $page;
        $json['data']['records'] = $total;
        $json['data']['total'] = ceil($json['data']['records'] / $rows);
        $json['data']['rows'] = $v;
        return $this->response->setJSON($json);
    }
    // 考评项目 获取信息
    public function evaluationQuery()
    {
        if (!$this->myservice->verifyRight('EVALUATION_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/Evaluate/evaluationQuery 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('evaluation')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
        if ($data) {
            $info['id'] = $id;
            $info['type'] = $data['type'];
            $info['name'] = $data['name'];
            $json['status'] = 200;
            $json['msg'] = 'success';
            $json['data'] = $info;
            return $this->response->setJSON($json);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 考评项目 新增
    public function evaluationAdd()
    {
        if (!$this->myservice->verifyRight('EVALUATION_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/Evaluate/evaluationAdd 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $data = $this->evaluationValidform($data);
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            $info = array_intersect_key($data, array_flip(['type', 'name']));
            $this->db->transStart();
            $this->db->table('evaluation')->insert($info);
            $iid = $this->db->insertID();
            $data['id'] = $iid;
            $this->myservice->log('新增了考评项目，对应职位：' . $data['type'] . '，考评内容：' . $data['name']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '新增成功！', 'data' => $data]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    // 考评项目 编辑
    public function evaluationUpdate()
    {
        if (!$this->myservice->verifyRight('EVALUATION_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/Evaluate/evaluationUpdate 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $data = $this->evaluationValidform($data);
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            $oldData = $this->db->table('evaluation')->where(['id' => $data['id']])->get()->getRowArray();
            $info = array_intersect_key($data, array_flip(['type', 'name']));
            $this->db->table('evaluation')->where(['id' => $data['id']])->update($info);
            $this->myservice->log('编辑了考评项目，将（对应职位：' . $oldData['type'] . '，考评内容：' . $oldData['name'] . '）编辑为了（对应职位：' . $data['type'] . '，考评内容：' . $data['name'] . '）');
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '编辑成功！', 'data' => $data]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    // 考评项目 删除
    public function evaluationDelete()
    {
        if (!$this->myservice->verifyRight('EVALUATION_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/Evaluate/evaluationDelete 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('evaluation')->where(['id' => $id])->get()->getRowArray();
        if ($data) {
            $this->db->table('evaluation')->where(['id' => $id])->update(['isDelete' => 1]);
            $this->myservice->log('删除了考评项目，对应职位：' . $data['type'] . '，考评内容：' . $data['name']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '删除成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 考评项目 数据验证
    private function evaluationValidform(array $data)
    {
        $data['id'] = isset($data['id']) ? intval($data['id']) : -1;
        if (strlen($data['type']) < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '对应职位不能为空！']);
        }
        if (strlen($data['name']) < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '考评内容不能为空！']);
        }
        if ($this->db->table('evaluation')->where(['id !=' => $data['id'], 'type' => $data['type'], 'name' => $data['name'], 'isDelete' => 0])->countAllResults() > 0) {
            return $this->response->setJSON(['status' => -1, 'msg' => '考评项目重复！']);
        }
        return $data;
    }

    // 员工考评项目指定
    public function evaluationOper()
    {
        if (!$this->myservice->verifyRight('EVALUATION_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/Evaluate/evaluationOper 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $ids = $this->request->getGetPost('ids');
        $ids = array_filter(array_map('intval', explode(',', (string) $ids)));
        $evaluationIds = $this->request->getGetPost('evaluationIds');
        if (count($ids) > 0) {
            $this->db->transStart();
            $sql = $this->db->table('user')->whereIn('id', $ids)->update(['evaluationIds' => $evaluationIds]);
            $names = implode('+', array_column($this->db->table('user')->select('name')->whereIn('id', $ids)->get()->getResultArray(), 'name'));
            $this->myservice->log((count($ids) > 1 ? '批量' : '') . '设定了 ' . $names . ' 的考评项目');
            if ($sql) {
                $this->db->transComplete();
                if ($this->db->transStatus() === false) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '设定失败！']);
                }
                return $this->response->setJSON(['status' => 200, 'msg' => '设定成功！']);
            }
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }

    // 考评打分 获取考评信息
    public function queryEvaluateInfo()
    {
        if (!$this->myservice->verifyRight('EVALUATION_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/Evaluate/queryEvaluateInfo 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $uId = intval($this->request->getGetPost('uId'));
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');
        $evaluate = $this->db->table('bill_evaluate')->where(['beginDate' => $beginDate, 'endDate' => $endDate, 'isDelete' => 0])->get()->getRowArray();
        if (!$evaluate) { //如无对应考评月表，则先自动创建
            $evaluate = [];
            $evaluate['billNo'] = 'KP'.date('Ym', strtotime($beginDate));
            $evaluate['beginDate'] = $beginDate;
            $evaluate['endDate'] = $endDate;
            $evaluate['description'] = '';
            $evaluate['uId'] = $this->myerp['uId'];
            $evaluate['uName'] = $this->myerp['uName'];
            $evaluate['createTime'] = $evaluate['modifyTime'] = date('Y-m-d H:i:s');
            $evaluate['isDraft'] = 0;
            $this->db->transStart();
            $this->db->table('bill_evaluate')->insert($evaluate);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '自动创建相关考评月表失败！']);
            } else{
                $evaluate['id'] = $this->db->insertID();
            }
        }
        //统一将当月已存在考评记录与考评月表关联起来
        $this->db->table('bill_evaluate_info')->where(['beginDate' => $beginDate, 'endDate' => $endDate, 'isDelete' => 0])->update(['iid' => $evaluate['id'], 'billNo' => $evaluate['billNo']]);

        // 取出用户基础数据备用
        $user = $this->db->table('user')->select('id, name, department, position, evaluationIds')->where(['id' => $uId])->get()->getRowArray();
        $evaluations = array_column($this->db->table('evaluation')->select('id, name')->where(['isDelete' => 0])->get()->getResultArray(), 'name', 'id');

        //开始处理返回信息
        if ($user['evaluationIds']) {
            $evaluationIdsArr = explode(',', $user['evaluationIds']);
            $infoArr = [];
            foreach($evaluationIdsArr as $key => $value) {
                $infoArr[$key] = ['n' => $evaluations[$value]];
            }
            $data['rows'] = $infoArr;
        } else {
            $data['rows'] = [];
        }
        $evaluate_info = $this->db->table('bill_evaluate_info')->where(['uId' => $uId, 'beginDate' => $beginDate, 'endDate' => $endDate, 'isDelete' => 0])->get()->getRowArray();
        if ($evaluate_info) {
            $data['id'] = intval($evaluate_info['id']);
            $data['iid'] = intval($evaluate_info['iid']);
            $data['billNo'] = $evaluate_info['billNo'];
            $data['uId'] = intval($evaluate_info['uId']);
            $data['uName'] = $evaluate_info['uName'];
            $data['department'] = $evaluate_info['department'];
            $data['position'] = $evaluate_info['position'];
            $data['beginDate'] = $evaluate_info['beginDate'];
            $data['endDate'] = $evaluate_info['endDate'];
            $data['score'] = intval($evaluate_info['score']);
            $data['description'] = $evaluate_info['description'];
            $data['lastName'] = $evaluate_info['lastName'];
            $data['lastTime'] = $evaluate_info['lastTime'];
        } else {
            $data['id'] = -1;
            $data['iid'] = intval($evaluate['id']);
            $data['billNo'] = $evaluate['billNo'];
            $data['uId'] = intval($uId);
            $data['uName'] = $user['name'];
            $data['department'] = $user['department'];
            $data['position'] = $user['position'];
            $data['beginDate'] = $beginDate;
            $data['endDate'] = $endDate;
            $data['score'] = 0;
            $data['description'] = '';
            $data['lastName'] = '无';
            $data['lastTime'] = '无';
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data'] = $data;
        return $this->response->setJSON($json);
    }
    // 考评打分 获取考评参考信息
    public function queryEvaluateReferenceInfo()
    {
        if (!$this->myservice->verifyRight('EVALUATION_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/Evaluate/queryEvaluateReferenceInfo 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $uId = intval($this->request->getGetPost('uId'));
        $uName = $this->request->getGetPost('uName');
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');

        // 历史考评得分信息
        $info['scoreHis'] = $this->db->table('bill_evaluate_info')
            ->select('beginDate, score')
            ->where(['uId' => $uId, 'beginDate >=' => date('Y-m-01', strtotime($beginDate . ' -6 month')), 'isDelete' => 0])
            ->get()
            ->getResultArray();
        // 出勤信息
        $info['attendance'] = $this->db->table('bill_attendance_info')
            ->where('beginDate >=', $beginDate)
            ->where('endDate <=', $endDate)
            ->where('uName', $uName)
            ->where('isDelete', 0)
            ->get()
            ->getResultArray();
        // 生产信息
        $pro_kitting_task_up_all = intval($this->db->table('pro_kitting_task_up')
            ->selectSum('upQty')
            ->where('createTime >=', $beginDate . ' 00:00:00')
            ->where('createTime <=', $endDate . ' 23:59:59')
            ->where('uName', $uName)
            ->where('isDelete', 0)
            ->get()
            ->getRowArray()['upQty'] ?? 0);
        $pro_kitting_task_up_uncheck = intval($this->db->table('pro_kitting_task_up')
            ->selectSum('upQty')
            ->where('createTime >=', $beginDate . ' 00:00:00')
            ->where('createTime <=', $endDate . ' 23:59:59')
            ->where('doneStatus <', 2)
            ->where('uName', $uName)
            ->where('isDelete', 0)
            ->get()
            ->getRowArray()['upQty'] ?? 0);
        $pro_kitting_task_up_checked = intval($this->db->table('pro_kitting_task_up')
            ->selectSum('upQty')
            ->where('createTime >=', $beginDate . ' 00:00:00')
            ->where('createTime <=', $endDate . ' 23:59:59')
            ->where('doneStatus', 2)
            ->where('uName', $uName)
            ->where('isDelete', 0)
            ->get()
            ->getRowArray()['upQty'] ?? 0);
        $performancePro['proQtyAll'] = $pro_kitting_task_up_all;
        $performancePro['proQtyUncheck'] = $pro_kitting_task_up_uncheck;
        $performancePro['proQty'] = $pro_kitting_task_up_checked;
        $info['performancePro'] = $performancePro;
        // 打包信息
        $info['performancePack'] = $this->db->table('deliver_bag')
            ->select('COUNT(CASE WHEN weight < 20 THEN no ELSE null END) AS totalCount1, SUM(CASE WHEN weight < 20 THEN weight ELSE 0 END) AS totalWeight1, COUNT(CASE company WHEN "申通快递" THEN no ELSE null END) AS totalZYCount, SUM(CASE company WHEN "申通快递" THEN amount ELSE 0 END) AS totalZYAmount, COUNT(CASE WHEN weight >= 20 THEN no ELSE null END) AS totalCount2, SUM(CASE WHEN weight >= 20 THEN weight ELSE 0 END) AS totalWeight2', false)
            ->where('scanTime >=', $beginDate . ' 00:00:00')
            ->where('scanTime <=', $endDate . ' 23:59:59')
            ->where('scanName', $uName)
            ->where('isDelete', 0)
            ->get()
            ->getRowArray() ?? ['totalCount1' => 0, 'totalWeight1' => 0, 'totalZYCount' => 0, 'totalZYAmount' => 0, 'totalCount2' => 0, 'totalWeight2' => 0];
        // 发票录入
        $info['invoice'] = $this->db->table('invoice')
            ->selectCount('*', 'num')
            ->selectSum('amount')
            ->where('createTime >=', $beginDate . ' 00:00:00')
            ->where('createTime <=', $endDate . ' 23:59:59')
            ->where('uName', $uName)
            ->where('isDelete', 0)
            ->get()
            ->getRowArray() ?? ['num' => 0, 'amount' => 0];
        // 反馈责任
        $info['serviceTarget'] = $this->db->table('service')
            ->select('content,targetName,targetAmount,beginDate')
            ->where('beginDate >=', $beginDate . ' 00:00:00')
            ->where('beginDate <=', $endDate . ' 23:59:59')
            ->where('FIND_IN_SET("' . $uName . '", targetName) >', 0, false)
            ->where('targetAmount >', 0)
            ->where('isDelete', 0)
            ->orderBy('id asc')
            ->get()
            ->getResultArray();
        if (is_array($info['serviceTarget']) && count($info['serviceTarget']) > 0) {
            foreach ($info['serviceTarget'] as $arr => $row) {
                $info['serviceTarget'][$arr]['content'] =  strip_tags(str_replace('<i class="close iconfont icon-shanchu"></i></div>','&nbsp;&nbsp;&#10;',str_replace('<div class="info-time">', '）', str_replace('<div class="info-username">','（', $row['content']))));
            }
        }
        // 工作记录
        $info['workRecord'] = $this->db->table('work_record')
            ->where('targetDate >=', $beginDate)
            ->where('targetDate <=', $endDate)
            ->where('targetName', $uName)
            ->where('isDelete', 0)
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();
        // 工作目标
        $info['planTypeArr'] = ['日','周','月','年'];
        $info['workPlan'] = $this->db->table('plan')
            ->select('date,type,target,summary,isDisable')
            ->where('date >=', $beginDate)
            ->where('date <=', $endDate)
            ->where('uId', $uId)
            ->where('isDelete', 0)
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();
        // 采购建议
        $info['purSuggest'] = $this->db->table('pur_suggest')
            ->selectCount('id', 'num')
            ->selectSum('amount')
            ->selectSum('purAmount')
            ->where('createTime >=', $beginDate . ' 00:00:00')
            ->where('createTime <=', $endDate . ' 23:59:59')
            ->where('uName', $uName)
            ->where('isDelete', 0)
            ->get()
            ->getRowArray() ?? ['num' => 0, 'amount' => 0, 'purAmount' => 0];

        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data'] = $info;
        return $this->response->setJSON($json);
    }
    // 考评打分 保存考评信息
    public function updateEvaluateInfo()
    {
        if (!$this->myservice->verifyRight('EVALUATION_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/Evaluate/updateEvaluateInfo 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getGetPost('postData');
        if (strlen($data) > 0) {
            $data = (array)json_decode($data, true);
            if (intval($data['score']) < 1 || intval($data['score']) > 110) {
                return $this->response->setJSON(['status' => -1, 'msg' => '主观评分范围错误，请输入1 ~ 110的整数！']);
            }
            $evaluateInfo = $this->db->table('bill_evaluate_info')
                ->where(['uId' => $data['uId'], 'beginDate' => $data['beginDate'], 'isDelete' => 0])
                ->get()
                ->getRowArray();
            $this->db->transStart();
            if ($evaluateInfo) {
                $this->db->table('bill_evaluate_info')
                ->where(['id' => $evaluateInfo['id']])
                ->update([
                    'score' => intval($data['score']),
                    'description' => $data['description'],
                    'lastName' => $this->myerp['uName'],
                    'lastTime' => date('Y-m-d H:i:s')
                ]);
            } else {
                $info = array_intersect_key($data, array_flip(['iid', 'billNo', 'uId', 'uName', 'department', 'position', 'beginDate', 'endDate', 'score', 'description']));
                $info['lastName'] = $this->myerp['uName'];
                $info['lastTime'] = date('Y-m-d H:i:s');
                $this->db->table('bill_evaluate_info')->insert($info);
            }
            $this->myservice->log('对 ' . $data['uName'] . ' 进行了 ' . date('Y-m', strtotime($data['beginDate'])) . '月 的考评打分');
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '保存成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
}
