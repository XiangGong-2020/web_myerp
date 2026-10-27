<?php

namespace App\Controllers\Base;
use App\Controllers\BaseController;

class ProKittingTask extends BaseController
{
    // 配货任务 列表
    public function list()
    {
        if (!$this->myservice->verifyRight('PROTASK_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/ProKittingTask/list 权限不足！');
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
        $status5 = intval($this->request->getGetPost('status5'));
        $status6 = intval($this->request->getGetPost('status6'));
        $skey = $this->request->getGetPost('skey');
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');
        $unCheck = intval($this->request->getGetPost('unCheck'));
        $uId = intval($this->request->getGetPost('uId'));
        $unDone = intval($this->request->getGetPost('unDone'));
        $builder = $this->db->table('pro_kitting_task');
        $builder->where('isDelete', 0);
        if ($status1) {
            $builder->where('reqName', $status1);
        }
        if ($status2) {
            $builder->where('uName', $status2);
        }
        if ($status3) {
            $builder->where('FIND_IN_SET(' . $this->db->escape($status3) . ', takeName) >', 0, false);
        }
        if ($status4 >= 0) {
            if ($status4 == 0) {
                $builder->where('checkId', 0);
            } else {
                $builder->where('checkId >', 0);
            }
        }
        if ($status5 >= 0) {
            $builder->where('takeStatus', $status5);
        }
        if ($status6 >= 0) {
            $builder->where('taskStatus', $status6);
        }
        if ($beginDate) {
            $builder->where('createTime >=', $beginDate);
        }
        if ($endDate) {
            $builder->where('createTime <=', $endDate);
        }
        if ($unCheck > 0) {
            $builder->where('checkId', 0);
        }
        if ($uId > 0) {
            $builder->where('uId', $uId);
        }
        if ($unDone > 0) {
            $builder->where('taskStatus <', 3);
        }
        if ($skey !== '' && $skey !== null) {
            $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
            foreach ($keywords as $kw) {
                $builder->groupStart()
                    ->like('goodsText', $this->db->escapeLikeString($kw))
                    ->orLike('description', $this->db->escapeLikeString($kw))
                ->groupEnd();
            }
        }
        $allowedSort = ['id','reqId','reqName','reqTime','lastDate','goodsId','goodsText','qty','uName','checkId','checkName','description','createTime','takeStatus','taskStatus','backInfo'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $list = (clone $builder)->orderBy($orderField, $sord)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $totalQty = (clone $builder)->selectSum('qty', 'totalQty')->get()->getRowArray()['totalQty'] ?? 0;
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['reqId'] = intval($row['reqId']);
            $v[$arr]['reqName'] = $row['reqName'];
            $v[$arr]['reqTime'] = $row['reqTime'] == '0000-00-00 00:00:00' ? '' : $row['reqTime'];
            $v[$arr]['lastDate'] = $row['lastDate'];
            $v[$arr]['goodsId'] = intval($row['goodsId']);
            $v[$arr]['goodsText'] = $row['goodsText'];
            $v[$arr]['qty'] = intval($row['qty']);
            $v[$arr]['uId'] = intval($row['uId']);
            $v[$arr]['uName'] = $row['uName'];
            $v[$arr]['checkId'] = intval($row['checkId']);
            $v[$arr]['checkName'] = $row['checkName'];
            $v[$arr]['description'] = $row['description'];
            $v[$arr]['createTime'] = $row['createTime'] == '0000-00-00 00:00:00' ? '' : $row['createTime'];
            $v[$arr]['takeStatus'] = intval($row['takeStatus']);
            $v[$arr]['taskStatus'] = intval($row['taskStatus']);
            $v[$arr]['backInfo'] = $row['backInfo'];
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['page'] = $page;
        $json['data']['records'] = $total;
        $json['data']['total'] = ceil($json['data']['records'] / $rows);
        $json['data']['rows'] = $v;
        $json['data']['totalQty'] = $totalQty;
        return $this->response->setJSON($json);
    }
    // 配货任务 获取信息
    public function query()
    {
        if (!$this->myservice->verifyRight('PROTASK_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/ProKittingTask/query 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('pro_kitting_task')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
        if ($data) {
            $info['id'] = $id;
            $info['lastDate'] = $data['lastDate'];
            $info['goodsId'] = intval($data['goodsId']);
            $info['goodsText'] = $data['goodsText'];
            $info['qty'] = intval($data['qty']);
            $info['uId'] = intval($data['uId']);
            $info['uName'] = $data['uName'];
            $info['checkId'] = intval($data['checkId']);
            $info['checkName'] = $data['checkName'];
            $info['reqId'] = intval($data['checkId']);
            $info['reqName'] = $data['reqName'];
            $info['reqTime'] = $data['reqTime'] == '0000-00-00 00:00:00' ? '' : $data['reqTime'];
            $info['description'] = $data['description'];
            $info['createTime'] = $data['createTime'] == '0000-00-00 00:00:00' ? '' : $data['createTime'];
            $info['takeStatus'] = intval($data['takeStatus']);
            $info['taskStatus'] = intval($data['taskStatus']);
            $list = $this->db->table('pro_kitting_task_get')->where(['iid' => $id, 'isDelete' => 0])->get()->getResultArray();
            $v = [];
            foreach ($list as $arr => $row) {
                $v[$arr]['id'] = intval($row['id']);
                $v[$arr]['iid'] = intval($row['iid']);
                $v[$arr]['lastDate'] = $row['lastDate'];
                $v[$arr]['goodsId'] = intval($row['goodsId']);
                $v[$arr]['goodsText'] = $row['goodsText'];
                $v[$arr]['description'] = $row['description'];
                $v[$arr]['taskQty'] = intval($row['taskQty']);
                $v[$arr]['getQty'] = intval($row['getQty']);
                $v[$arr]['upQty'] = intval($row['upQty']);
                $v[$arr]['doneStatus'] = intval($row['doneStatus']);
                $v[$arr]['uId'] = intval($row['uId']);
                $v[$arr]['uName'] = $row['uName'];
                $v[$arr]['createTime'] = $row['createTime'] == '0000-00-00 00:00:00' ? '' : $row['createTime'];
                $v[$arr]['backInfo'] = $row['backInfo'];
                $v[$arr]['transInfo'] = $row['transInfo'] ? (array)json_decode($row['transInfo'], TRUE) : '';
                $v[$arr]['transHis'] = $row['transHis'];
                $v[$arr]['isGroupTrans'] = intval($row['isGroupTrans']);
            }
            $info['entries'] = $v;
            $json['status'] = 200;
            $json['msg'] = 'success';
            $json['data'] = $info;
            return $this->response->setJSON($json);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 配货任务 新增
    public function add()
    {
        if (!$this->myservice->verifyRight('PROTASK_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/ProKittingTask/add 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost();
        $data = $this->validform($data);
        // 如果返回的是 Response 对象，直接返回它
        if ($data instanceof \CodeIgniter\HTTP\Response) {
            return $data;
        }
        if (is_array($data) && count($data) > 0) {
            if (strtotime($data['lastDate']) - strtotime($data['createTime']) < 14400) {
                return $this->response->setJSON(['status' => -1, 'msg' => '交付限期起码距发布时间4小时以上，请合理指定交付限期，切勿随意录入！']);
            }
            $data['checkId'] = $data['uId'];
            $data['checkName'] = $data['uName'];
            $data['takeStatus'] = $data['taskStatus'] = 0;
            $info = array_intersect_key($data, array_flip(['reqId', 'reqName', 'reqTime', 'lastDate', 'goodsId', 'goodsText', 'qty', 'description', 'uId', 'uName', 'checkId', 'checkName', 'takeStatus', 'taskStatus', 'createTime']));
            $this->db->transStart();
            $this->db->table('pro_kitting_task')->insert($info);
            $data['id'] = $this->db->insertID();
            if ($data['members']) {
                $membersArr = explode(',', $data['members']);
                $memberNum = count($membersArr);
                // 计算任务产量除以组员人数的商和余数，来平均分配产量，余数(剩余产量)加给随机组员
                $perMemberQty = intdiv(intval($data['qty']), $memberNum); // 取商为平均领取产量
                $remainQty = intval($data['qty']) % $memberNum; // 取余为剩余产量
                $createTime = date('Y-m-d H:i:s');
                $getDta = [];
                // 取合理随机数
                $randKey = rand(0, $memberNum - 1);
                foreach ($membersArr as $k => $v) {
                    $user = $this->db->table('user')->select('id,name')->where(['name' => $v])->get()->getRowArray();
                    $eachGetData = [
                        'iid' => $data['id'],
                        'lastDate' => $data['lastDate'],
                        'goodsId' => $data['goodsId'],
                        'goodsText' => $data['goodsText'],
                        'description' => $data['description'],
                        'taskQty' => $data['qty'],
                        'getQty' => $k == $randKey ? ($perMemberQty + $remainQty) : $perMemberQty, // 剩余产量加给随机一名组员
                        'uId' => $user['id'],
                        'uName' => $user['name'],
                        'createTime' => $createTime
                    ];
                    array_push($getDta, $eachGetData);
                }
                $this->db->table('pro_kitting_task_get')->insertBatch($getDta);
                $this->db->table('pro_kitting_task')->where(['id' => $data['id']])->update(['takeStatus' => 2, 'takeName' => $data['members'], 'taskStatus' => 1]);
            }
            if ($data['reqId'] > 0) {
                $this->db->table('pro_kitting_req')->where(['id' => $data['reqId']])->update(['produceId' => $data['uId'], 'produceName' => $data['uName'], 'produceTime' => date('Y-m-d H:i:s'), 'taskId' => $data['id']]);
            }
            $this->myservice->log('新增了配货任务，任务编号：' . $data['id'] . '，任务产品：' . $data['goodsText'] . '，任务产量：' . $data['qty'] . ($data['members'] ? ('，指定配货人：' . $data['members']) : ''));
            $data = $this->db->table('pro_kitting_task')->where(['id' => $data['id']])->get()->getRowArray();
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '新增成功！', 'data' => $data]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    // 配货任务 编辑
    public function update()
    {
        if (!$this->myservice->verifyRight('PROTASK_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/ProKittingTask/update 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost();
        $data = $this->validform($data);
        // 如果返回的是 Response 对象，直接返回它
        if ($data instanceof \CodeIgniter\HTTP\Response) {
            return $data;
        }
        $oldData = $this->db->table('pro_kitting_task')->where(['id' => $data['id']])->get()->getRowArray();
        if (strtotime($data['lastDate']) - strtotime($oldData['createTime']) < 14400) {
            return $this->response->setJSON(['status' => -1, 'msg' => '交付限期起码距发布时间4小时以上，请合理指定交付限期，切勿随意录入！']);
        }
        if (is_array($data) && count($data) > 0) {
            $info = array_intersect_key($data, array_flip(['reqName', 'reqTime', 'lastDate', 'goodsId', 'goodsText', 'qty', 'description']));
            if ($data['members'] && $this->db->table('pro_kitting_task_up')->where(['iid' => $data['id'], 'isDelete' => 0])->countAllResults() > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '已有交付，无法重新指定配货人！']);
            }
            $this->db->transStart();
            $this->db->table('pro_kitting_task')->where(['id' => $data['id']])->update($info);
            if ($data['members']) {
                $this->db->table('pro_kitting_task_get')->where(['iid' => $data['id']])->update(['isDelete' => 1]);
                $membersArr = explode(',', $data['members']);
                $memberNum = count($membersArr);
                // 计算任务产量除以组员人数的商和余数，来平均分配产量，余数(剩余产量)加给随机组员
                $perMemberQty = intdiv(intval($data['qty']), $memberNum); // 取商为平均领取产量
                $remainQty = intval($data['qty']) % $memberNum; // 取余为剩余产量
                $createTime = date('Y-m-d H:i:s');
                $getData = [];
                // 取合理随机数
                $randKey = rand(0, $memberNum - 1);
                foreach ($membersArr as $k => $v) {
                    $user = $this->db->table('user')->select('id,name')->where(['name' => $v])->get()->getRowArray();
                    $eachGetData = [
                        'iid' => $data['id'],
                        'lastDate' => $data['lastDate'],
                        'goodsId' => $data['goodsId'],
                        'goodsText' => $data['goodsText'],
                        'description' => $data['description'],
                        'taskQty' => $data['qty'],
                        'getQty' => $k == $randKey ? ($perMemberQty + $remainQty) : $perMemberQty, // 剩余产量加给随机一名组员
                        'uId' => $user['id'],
                        'uName' => $user['name'],
                        'createTime' => $createTime
                    ];
                    array_push($getData, $eachGetData);
                }
                $this->db->table('pro_kitting_task_get')->insertBatch($getData);
                $this->db->table('pro_kitting_task')->where(['id' => $data['id']])->update(['takeStatus' => 2, 'takeName' => $data['members'], 'taskStatus' => 1]);
            }
            $this->myservice->log('编辑了配货任务，任务编号：' . $data['id'] . '，任务产品：' . $data['goodsText'] . '，任务产量：' . $data['qty'] . ($data['members'] ? ('，指定配货人：' . $data['members']) : ''));
            $data = $this->db->table('pro_kitting_task')->where(['id' => $data['id']])->get()->getRowArray();
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '编辑成功！', 'data' => $data]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    // 配货任务 删除
    public function delete()
    {
        if (!$this->myservice->verifyRight('PROTASK_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/ProKittingTask/delete 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('pro_kitting_task')->where(['id' => $id])->get()->getRowArray();;
        if ($data) {
            if ($data['uId'] != $this->myerp['uId'] && !$this->myservice->verifyRight('SYS_ADMIN')) {
                return $this->response->setJSON(['status' => -1, 'msg' => '不能删除别人发布的配货任务，仅本人或管理员可删除！']);
            }
            $taskUp = $this->db->table('pro_kitting_task_up')->where(['iid' => $id, 'isDelete' => 0])->countAllResults();
            if ($taskUp > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '任务已有交付，不允许随意删除！']);
            }
            $this->db->transStart();
            $this->db->table('pro_kitting_task')->where(['id' => $id])->update(['isDelete' => 1]);
            $this->db->table('pro_kitting_task_get')->where(['iid' => $id])->update(['isDelete' => 1]);
            if ($data['reqId'] > 0) {
                $this->db->table('pro_kitting_req')->where(['id' => $data['reqId']])->update(['produceId' => 0, 'produceName' => '', 'produceTime' => '', 'taskId' => 0]);
            }
            $this->myservice->log('删除配货任务，任务编号：' . $data['id'] . '，任务产品：' . $data['goodsText'] . '，任务产量：' . $data['qty']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '删除成功！']);
        }
    }
    // 配货任务 发布与撤回
    public function check()
    {
        if (!$this->myservice->verifyRight('PROTASK_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/ProKittingTask/check 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $checkId = intval($this->request->getGetPost('checkId'));
        $checkName = $this->request->getGetPost('checkName');
        $title = $this->request->getGetPost('title');
        if ($id > 0) {
            $this->db->transBegin();
            $this->db->table('pro_kitting_task')->where(['id' => $id])->update(['checkId ' => $checkId, 'checkName' => $checkName]);
            $this->myservice->log(($checkId > 0 ? '发布了' : '撤回了') . $title . ' 的配货任务。');
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '操作成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 配货任务 数据验证
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

    // 配货任务 获取上次安排产量信息
    public function lastTaskInfo()
    {
        $id = intval($this->request->getGetPost('id'));
        $goodsId = intval($this->request->getGetPost('goodsId'));
        $taskInfo = $this->db->table('pro_kitting_task')->select('qty, createTime')->where(['goodsId' => $goodsId, 'id !=' => $id, 'isDelete' => 0])->get()->getRowArray();
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data'] = $taskInfo;
        return $this->response->setJSON($json);
    }

    // 配货任务 单任务领取列表
    public function get_list()
    {
        if (!$this->myservice->verifyRight('PROTASK_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/ProKittingTask/get_list 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $id = intval($this->request->getGetPost('id'));
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');

        $builder = $this->db->table('pro_kitting_task_get');
        $builder->where(['iid' => $id, 'isDelete' => 0]);
        if ($beginDate) {
            $builder->where('createTime >=', $beginDate);
        }
        if ($endDate) {
            $builder->where('createTime <=', $endDate);
        }
        $allowedSort = ['id','iid','lastDate','goodsText','description','taskQty','getQty','upQty','doneStatus','uId','uName','createTime','backInfo','transInfo','transHis','isGroupTrans'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $list = (clone $builder)->orderBy($orderField, $sord)->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['iid'] = intval($row['iid']);
            $v[$arr]['lastDate'] = $row['lastDate'];
            $v[$arr]['goodsId'] = intval($row['goodsId']);
            $v[$arr]['goodsText'] = $row['goodsText'];
            $v[$arr]['description'] = $row['description'];
            $v[$arr]['taskQty'] = intval($row['taskQty']);
            $v[$arr]['getQty'] = intval($row['getQty']);
            $v[$arr]['upQty'] = intval($row['upQty']);
            $v[$arr]['doneStatus'] = intval($row['doneStatus']);
            $v[$arr]['uId'] = intval($row['uId']);
            $v[$arr]['uName'] = $row['uName'];
            $v[$arr]['createTime'] = $row['createTime'] == '0000-00-00 00:00:00' ? '' : $row['createTime'];
            $v[$arr]['backInfo'] = $row['backInfo'];
            $v[$arr]['transInfo'] = $row['transInfo'] ? (array)json_decode($row['transInfo'], TRUE) : '';
            $v[$arr]['transHis'] = $row['transHis'];
            $v[$arr]['isGroupTrans'] = intval($row['isGroupTrans']);
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['page'] = 1;
        $json['data']['records'] = $total;
        $json['data']['total'] = 1;
        $json['data']['rows'] = $v;
        return $this->response->setJSON($json);
    }

    // 配货任务 单任务交付列表
    public function up_list()
    {
        if (!$this->myservice->verifyRight('PROTASK_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/ProKittingTask/up_list 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $id = intval($this->request->getGetPost('id'));
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');
        $builder = $this->db->table('pro_kitting_task_up');
        $builder->where('iid', $id)->where('isDelete', 0);
        if ($beginDate) {
            $builder->where('createTime >=', $beginDate);
        }
        if ($endDate) {
            $builder->where('createTime <=', $endDate);
        }
        $allowedSort = ['id','iid','lastDate','goodsId','goodsText','taskQty','getQty','upQty','price','amount','upDescription','upImgs','doneStatus','uId','uName','createTime'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $list = (clone $builder)->orderBy($orderField, $sord)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['iid'] = intval($row['iid']);
            $v[$arr]['lastDate'] = $row['lastDate'];
            $v[$arr]['goodsId'] = intval($row['goodsId']);
            $v[$arr]['goodsText'] = $row['goodsText'];
            $v[$arr]['taskQty'] = intval($row['taskQty']);
            $v[$arr]['getQty'] = intval($row['getQty']);
            $v[$arr]['upQty'] = intval($row['upQty']);
            $v[$arr]['price'] = (float)$row['price'];
            $v[$arr]['amount'] = (float)$row['amount'];
            $v[$arr]['upDescription'] = $row['upDescription'];
            $v[$arr]['upImgs'] = $row['upImgs'] == '' ? [] : explode(',', $row['upImgs']);
            $v[$arr]['doneStatus'] = intval($row['doneStatus']);
            $v[$arr]['uId'] = intval($row['uId']);
            $v[$arr]['uName'] = $row['uName'];
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

    // 配货任务 所有配货领取列表
    public function get_list_all()
    {
        if (!$this->myservice->verifyRight('PROTASK_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/ProKittingTask/get_list_all 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $status1 = $this->request->getGetPost('status1');
        $status2 = intval($this->request->getGetPost('status2'));
        $skey = $this->request->getGetPost('skey');
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');
        $builder = $this->db->table('pro_kitting_task_get');
        $builder->where('isDelete', 0);
        if ($status1) {
            $builder->where('uName', $status1);
        }
        if (0 <= $status2) {
            $builder->where('doneStatus', $status2);
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
                    ->like('goodsText', $this->db->escapeLikeString($kw))
                    ->orLike('description', $this->db->escapeLikeString($kw))
                    ->orWhere('uName', $kw)
                ->groupEnd();
            }
        }
        $allowedSort = ['id','iid','lastDate','goodsId','taskQty','getQty','upQty','doneStatus','uId','uName','createTime','backInfo','transInfo','transHis','isGroupTrans'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $list = (clone $builder)->orderBy($orderField, $sord)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $totalQty = (clone $builder)->selectSum('getQty', 'totalGetQty')->selectSum('upQty', 'totalUpQty')->get()->getRowArray() ?? ['totalGetQty' => 0, 'totalUpQty' => 0];
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['iid'] = intval($row['iid']);
            $v[$arr]['lastDate'] = $row['lastDate'];
            $v[$arr]['goodsId'] = intval($row['goodsId']);
            $v[$arr]['goodsText'] = $row['goodsText'];
            $v[$arr]['taskQty'] = intval($row['taskQty']);
            $v[$arr]['getQty'] = intval($row['getQty']);
            $v[$arr]['upQty'] = intval($row['upQty']);
            $v[$arr]['doneStatus'] = intval($row['doneStatus']);
            $v[$arr]['uId'] = intval($row['uId']);
            $v[$arr]['uName'] = $row['uName'];
            $v[$arr]['createTime'] = $row['createTime'] == '0000-00-00 00:00:00' ? '' : $row['createTime'];
            $v[$arr]['backInfo'] = $row['backInfo'];
            $v[$arr]['transInfo'] = $row['transInfo'] ? (array)json_decode($row['transInfo'], TRUE) : '';
            $v[$arr]['transHis'] = $row['transHis'];
            $v[$arr]['isGroupTrans'] = intval($row['isGroupTrans']);
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['page'] = $page;
        $json['data']['records'] = $total;
        $json['data']['total'] = ceil($json['data']['records'] / $rows);
        $json['data']['rows'] = $v;
        $json['data']['totalQty'] = $totalQty;
        return $this->response->setJSON($json);
    }

    // 配货任务 所有配货交付列表
    public function up_list_all()
    {
        if (!$this->myservice->verifyRight('PROTASK_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/ProKittingTask/up_list_all 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $status1 = $this->request->getGetPost('status1');
        $status2 = intval($this->request->getGetPost('status2'));
        $skey = $this->request->getGetPost('skey');
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');
        $builder = $this->db->table('pro_kitting_task_up');
        $builder->where('isDelete', 0);
        if ($status1) {
            $builder->where('uName', $status1);
        }
        if (0 < $status2) {
            $builder->where('doneStatus', $status2);
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
                    ->like('goodsText', $this->db->escapeLikeString($kw))
                    ->orLike('upDescription', $this->db->escapeLikeString($kw))
                    ->orWhere('uName', $kw)
                ->groupEnd();
            }
        }
        $allowedSort = ['id','iid','lastDate','goodsId','taskQty','getQty','upQty','weight','price','amount','upDescription','upImgs','doneStatus','checkId','checkName','checkTime','uName','createTime'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $list = (clone $builder)->orderBy($orderField, $sord)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $totalQty = (clone $builder)->selectSum('upQty', 'totalQty')->get()->getRowArray()['totalQty'] ?? 0;
        $totalWeight = (clone $builder)->selectSum('weight', 'totalWeight')->get()->getRowArray()['totalWeight'] ?? 0;
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['iid'] = intval($row['iid']);
            $v[$arr]['lastDate'] = $row['lastDate'];
            $v[$arr]['goodsId'] = intval($row['goodsId']);
            $v[$arr]['goodsText'] = $row['goodsText'];
            $v[$arr]['taskQty'] = intval($row['taskQty']);
            $v[$arr]['getQty'] = intval($row['getQty']);
            $v[$arr]['upQty'] = intval($row['upQty']);
            $v[$arr]['weight'] = (float)$row['weight'];
            $v[$arr]['price'] = (float)$row['price'];
            $v[$arr]['amount'] = (float)$row['amount'];
            $v[$arr]['upDescription'] = $row['upDescription'];
            $v[$arr]['upImgs'] = $row['upImgs'] == '' ? [] : explode(',', $row['upImgs']);
            $v[$arr]['doneStatus'] = intval($row['doneStatus']);
            $v[$arr]['checkId'] = intval($row['checkId']);
            $v[$arr]['checkName'] = $row['checkName'];
            $v[$arr]['checkTime'] = $row['checkTime'] == '0000-00-00 00:00:00' ? '' : $row['checkTime'];
            $v[$arr]['uId'] = intval($row['uId']);
            $v[$arr]['uName'] = $row['uName'];
            $v[$arr]['createTime'] = $row['createTime'] == '0000-00-00 00:00:00' ? '' : $row['createTime'];
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['page'] = $page;
        $json['data']['records'] = $total;
        $json['data']['total'] = ceil($json['data']['records'] / $rows);
        $json['data']['rows'] = $v;
        $json['data']['totalQty'] = $totalQty;
        $json['data']['totalWeight'] = $totalWeight;
        return $this->response->setJSON($json);
    }
}
